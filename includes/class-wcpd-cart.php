<?php
if (!defined('ABSPATH')) {
    exit;
}

class WCPD_Cart {

    private static $percent = 30;

    public static function init() {
        add_filter('woocommerce_get_price_html', array(__CLASS__, 'price_html'), 10, 2);
        add_action('woocommerce_before_calculate_totals', array(__CLASS__, 'adjust_totals'), 99);
        add_filter('woocommerce_cart_item_price', array(__CLASS__, 'cart_item_price'), 10, 3);
        add_filter('woocommerce_cart_item_subtotal', array(__CLASS__, 'cart_item_subtotal'), 10, 3);
        add_action('woocommerce_checkout_create_order_line_item', array(__CLASS__, 'line_meta'), 10, 4);
        add_action('woocommerce_checkout_update_order_meta', array(__CLASS__, 'save_meta'));
        add_action('woocommerce_before_cart_table', array(__CLASS__, 'cart_notice'));
    }

    public static function price_html($html, $product) {
        if (!WCPD_Product::is_preorder($product->get_id())) {
            return $html;
        }

        $full = (float) $product->get_price();
        $deposit = round($full * (self::$percent / 100), wc_get_price_decimals());

        $out = '<div class="wcpd-price-wrap">';
        $out .= '<div class="wcpd-price-current">' . $html . '</div>';
        $out .= '<div class="wcpd-deposit-card">';
        $out .= '<div class="wcpd-deposit-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>';
        $out .= '<div class="wcpd-deposit-body">';
        $out .= '<div class="wcpd-deposit-label">' . sprintf(
            /* translators: %s: deposit percentage, e.g. "30". */
            __('Pay %s%% now', 'pre-order-deposit-manager'),
            self::$percent
        ) . '</div>';
        $out .= '<div class="wcpd-deposit-amount">' . wc_price($deposit) . '</div>';
        $out .= '<div class="wcpd-deposit-note">' . sprintf(
            /* translators: %s: full product price formatted as currency. */
            __('Total price: %s', 'pre-order-deposit-manager'),
            wc_price($full)
        ) . '</div>';
        $out .= '</div></div>';
        $out .= '</div>';

        return $out;
    }

    public static function cart_notice() {
        $has_preorder = false;
        foreach (WC()->cart->get_cart() as $item) {
            if (WCPD_Product::is_preorder($item['product_id'])) {
                $has_preorder = true;
                break;
            }
        }
        if (!$has_preorder) {
            return;
        }
        echo '<div class="wcpd-cart-notice">';
        echo '<div class="wcpd-notice-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></div>';
        echo '<div class="wcpd-notice-body">';
        echo '<strong>' . __('Pre-Order Items in Cart', 'pre-order-deposit-manager') . '</strong>';
        echo '<span>' . __('You will be charged a 30% deposit now. The remaining balance will be due when your order is ready for delivery.', 'pre-order-deposit-manager') . '</span>';
        echo '</div></div>';
    }

    public static function adjust_totals($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return;
        }

        foreach ($cart->get_cart() as $key => $item) {
            $product = $item['data'];

            if (!WCPD_Product::is_preorder($product->get_id())) {
                continue;
            }

            $original = (float) $product->get_price();
            $deposit  = round($original * (self::$percent / 100), wc_get_price_decimals());

            $cart->cart_contents[$key]['_wcpd_original'] = $original;
            $cart->cart_contents[$key]['_wcpd_deposit']  = $deposit;
            $cart->cart_contents[$key]['_wcpd_is_preorder'] = true;

            $product->set_price($deposit);
            $product->set_regular_price($original);
        }
    }

    public static function cart_item_price($price, $item, $key) {
        if (empty($item['_wcpd_is_preorder'])) {
            return $price;
        }

        $orig = isset($item['_wcpd_original']) ? (float) $item['_wcpd_original'] : 0;
        $out = '<div class="wcpd-cart-price">';
        $out .= '<span class="wcpd-cart-strike">' . wc_price($orig) . '</span>';
        $out .= '<span class="wcpd-cart-deposit">' . $price . '</span>';
        $out .= '<span class="wcpd-cart-tag">' . esc_html__('30% deposit', 'pre-order-deposit-manager') . '</span>';
        $out .= '</div>';
        return $out;
    }

    public static function cart_item_subtotal($subtotal, $item, $key) {
        if (empty($item['_wcpd_is_preorder'])) {
            return $subtotal;
        }

        $orig = isset($item['_wcpd_original']) ? (float) $item['_wcpd_original'] * $item['quantity'] : 0;
        $out = '<div class="wcpd-cart-sub">';
        $out .= '<div class="wcpd-cart-sub-deposit">' . $subtotal . '</div>';
        $out .= '<div class="wcpd-cart-sub-full">' . sprintf(
            /* translators: %s: full line item value formatted as currency. */
            __('Total value: %s', 'pre-order-deposit-manager'),
            wc_price($orig)
        ) . '</div>';
        $out .= '</div>';
        return $out;
    }

    public static function line_meta($item, $key, $values, $order) {
        if (!empty($values['_wcpd_is_preorder'])) {
            $item->add_meta_data('_wcpd_original_price', $values['_wcpd_original'], true);
            $item->add_meta_data('_wcpd_is_preorder', 'yes', true);
        }
    }

    public static function save_meta($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $has_preorder = false;
        $full_total   = 0;
        $deposit_total = 0;

        foreach ($order->get_items() as $item) {
            if ($item->get_meta('_wcpd_is_preorder') === 'yes') {
                $has_preorder = true;
                $qty  = $item->get_quantity();
                $orig = (float) $item->get_meta('_wcpd_original_price');
                $line_full = $orig * $qty;
                $line_dep  = $item->get_total();

                $full_total    += $line_full;
                $deposit_total += $line_dep;
            } else {
                $line = $item->get_total() + $item->get_total_tax();
                $full_total    += $line;
                $deposit_total += $line;
            }
        }

        if (!$has_preorder) {
            return;
        }

        $remaining = round($full_total - $deposit_total, wc_get_price_decimals());

        $order->update_meta_data('_wcpd_full_total', $full_total);
        $order->update_meta_data('_wcpd_deposit_total', $deposit_total);
        $order->update_meta_data('_wcpd_remaining_total', $remaining);
        $order->update_meta_data('_wcpd_is_preorder', 'yes');
        $order->save_meta_data();

        if ($order->get_status() === 'processing' || $order->get_status() === 'completed') {
            $order->update_status('wc-preorder-deposit', __('Deposit paid. Waiting for delivery.', 'pre-order-deposit-manager'));
        }
    }
}
