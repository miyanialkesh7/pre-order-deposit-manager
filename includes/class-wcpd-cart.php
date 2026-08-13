<?php
/**
 * Calculates and displays the 30% deposit price throughout the cart/checkout.
 *
 * @package WC_Preorder_Deposit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adjusts pre-order product prices to a deposit amount across the cart and checkout.
 */
class WCPD_Cart {

	/**
	 * Percentage of the full price charged as a deposit at checkout.
	 *
	 * @var int
	 */
	private static $percent = 30;

	/**
	 * Registers all cart/checkout hooks used to apply the deposit price.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'price_html' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'adjust_totals' ), 99 );
		add_filter( 'woocommerce_cart_item_price', array( __CLASS__, 'cart_item_price' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_subtotal', array( __CLASS__, 'cart_item_subtotal' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'line_meta' ), 10, 3 );
		add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'save_meta' ) );
		add_action( 'woocommerce_before_cart_table', array( __CLASS__, 'cart_notice' ) );
	}

	/**
	 * Appends the deposit amount card to a pre-order product's price display.
	 *
	 * @param string     $html    The default price HTML.
	 * @param WC_Product $product The product being displayed.
	 * @return string Price HTML including the deposit card, unchanged if not a pre-order.
	 */
	public static function price_html( $html, $product ) {
		if ( ! WCPD_Product::is_preorder( $product->get_id() ) ) {
			return $html;
		}

		$full    = (float) $product->get_price();
		$deposit = round( $full * ( self::$percent / 100 ), wc_get_price_decimals() );

		$out  = '<div class="wcpd-price-wrap">';
		$out .= '<div class="wcpd-price-current">' . $html . '</div>';
		$out .= '<div class="wcpd-deposit-card">';
		$out .= '<div class="wcpd-deposit-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>';
		$out .= '<div class="wcpd-deposit-body">';
		$out .= '<div class="wcpd-deposit-label">' . sprintf( __( 'Pay %s%% now', 'wc-preorder-deposit' ), self::$percent ) . '</div>';
		$out .= '<div class="wcpd-deposit-amount">' . wc_price( $deposit ) . '</div>';
		$out .= '<div class="wcpd-deposit-note">' . sprintf( __( 'Total price: %s', 'wc-preorder-deposit' ), wc_price( $full ) ) . '</div>';
		$out .= '</div></div>';
		$out .= '</div>';

		return $out;
	}

	/**
	 * Prints a notice above the cart table when it contains pre-order items.
	 *
	 * @return void
	 */
	public static function cart_notice() {
		$has_preorder = false;
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( WCPD_Product::is_preorder( $item['product_id'] ) ) {
				$has_preorder = true;
				break;
			}
		}
		if ( ! $has_preorder ) {
			return;
		}
		echo '<div class="wcpd-cart-notice">';
		echo '<div class="wcpd-notice-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></div>';
		echo '<div class="wcpd-notice-body">';
		echo '<strong>' . __( 'Pre-Order Items in Cart', 'wc-preorder-deposit' ) . '</strong>';
		echo '<span>' . __( 'You will be charged a 30% deposit now. The remaining balance will be due when your order is ready for delivery.', 'wc-preorder-deposit' ) . '</span>';
		echo '</div></div>';
	}

	/**
	 * Replaces each pre-order cart item's price with its deposit amount.
	 *
	 * @param WC_Cart $cart The cart being totaled.
	 * @return void
	 */
	public static function adjust_totals( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		foreach ( $cart->get_cart() as $key => $item ) {
			$product = $item['data'];

			if ( ! WCPD_Product::is_preorder( $product->get_id() ) ) {
				continue;
			}

			$original = (float) $product->get_price();
			$deposit  = round( $original * ( self::$percent / 100 ), wc_get_price_decimals() );

			$cart->cart_contents[ $key ]['_wcpd_original']    = $original;
			$cart->cart_contents[ $key ]['_wcpd_deposit']     = $deposit;
			$cart->cart_contents[ $key ]['_wcpd_is_preorder'] = true;

			$product->set_price( $deposit );
			$product->set_regular_price( $original );
		}
	}

	/**
	 * Shows the original price struck through above the deposit price in the cart.
	 *
	 * @param string $price The deposit price HTML.
	 * @param array  $item  The cart item data.
	 * @return string Price HTML including the original price, unchanged if not a pre-order.
	 */
	public static function cart_item_price( $price, $item ) {
		if ( empty( $item['_wcpd_is_preorder'] ) ) {
			return $price;
		}

		$orig = isset( $item['_wcpd_original'] ) ? (float) $item['_wcpd_original'] : 0;
		$out  = '<div class="wcpd-cart-price">';
		$out .= '<span class="wcpd-cart-strike">' . wc_price( $orig ) . '</span>';
		$out .= '<span class="wcpd-cart-deposit">' . $price . '</span>';
		$out .= '<span class="wcpd-cart-tag">30% deposit</span>';
		$out .= '</div>';
		return $out;
	}

	/**
	 * Shows the full line total alongside the deposit subtotal in the cart.
	 *
	 * @param string $subtotal The deposit subtotal HTML.
	 * @param array  $item     The cart item data.
	 * @return string Subtotal HTML including the full line value, unchanged if not a pre-order.
	 */
	public static function cart_item_subtotal( $subtotal, $item ) {
		if ( empty( $item['_wcpd_is_preorder'] ) ) {
			return $subtotal;
		}

		$orig = isset( $item['_wcpd_original'] ) ? (float) $item['_wcpd_original'] * $item['quantity'] : 0;
		$out  = '<div class="wcpd-cart-sub">';
		$out .= '<div class="wcpd-cart-sub-deposit">' . $subtotal . '</div>';
		$out .= '<div class="wcpd-cart-sub-full">' . sprintf( __( 'Total value: %s', 'wc-preorder-deposit' ), wc_price( $orig ) ) . '</div>';
		$out .= '</div>';
		return $out;
	}

	/**
	 * Copies the pre-order original price onto the order line item's meta data.
	 *
	 * @param WC_Order_Item_Product $item   The order line item being created.
	 * @param string                $key    The cart item key.
	 * @param array                 $values The cart item data.
	 * @return void
	 */
	public static function line_meta( $item, $key, $values ) {
		if ( ! empty( $values['_wcpd_is_preorder'] ) ) {
			$item->add_meta_data( '_wcpd_original_price', $values['_wcpd_original'], true );
			$item->add_meta_data( '_wcpd_is_preorder', 'yes', true );
		}
	}

	/**
	 * Calculates and stores the full/deposit/remaining totals on a new order.
	 *
	 * @param int $order_id The order ID being saved from checkout.
	 * @return void
	 */
	public static function save_meta( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$has_preorder  = false;
		$full_total    = 0;
		$deposit_total = 0;

		foreach ( $order->get_items() as $item ) {
			if ( $item->get_meta( '_wcpd_is_preorder' ) === 'yes' ) {
				$has_preorder = true;
				$qty          = $item->get_quantity();
				$orig         = (float) $item->get_meta( '_wcpd_original_price' );
				$line_full    = $orig * $qty;
				$line_dep     = $item->get_total();

				$full_total    += $line_full;
				$deposit_total += $line_dep;
			} else {
				$line           = $item->get_total() + $item->get_total_tax();
				$full_total    += $line;
				$deposit_total += $line;
			}
		}

		if ( ! $has_preorder ) {
			return;
		}

		$remaining = round( $full_total - $deposit_total, wc_get_price_decimals() );

		$order->update_meta_data( '_wcpd_full_total', $full_total );
		$order->update_meta_data( '_wcpd_deposit_total', $deposit_total );
		$order->update_meta_data( '_wcpd_remaining_total', $remaining );
		$order->update_meta_data( '_wcpd_is_preorder', 'yes' );
		$order->save_meta_data();

		if ( $order->get_status() === 'processing' || $order->get_status() === 'completed' ) {
			$order->update_status( 'wc-preorder-deposit', __( 'Deposit paid. Waiting for delivery.', 'wc-preorder-deposit' ) );
		}
	}
}
