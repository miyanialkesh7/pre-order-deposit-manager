<?php
if (!defined('ABSPATH')) {
    exit;
}

class WCPD_Payment {

    public static function init() {
        add_action('woocommerce_payment_complete', array(__CLASS__, 'capture_token'));
        add_action('wp_ajax_wcpd_charge_remaining', array(__CLASS__, 'ajax_charge'));
        add_action('wp_ajax_wcpd_mark_ready_manual', array(__CLASS__, 'ajax_ready'));
    }

    public static function capture_token($order_id) {
        $order = wc_get_order($order_id);
        if (!$order || !WCPD_Order::is_preorder($order)) {
            return;
        }

        $user_id = $order->get_customer_id();
        if (!$user_id) {
            return;
        }

        $tokens = WC_Payment_Tokens::get_customer_tokens($user_id);
        if (empty($tokens)) {
            return;
        }

        $latest = null;
        foreach ($tokens as $token) {
            if (!$latest || $token->get_id() > $latest->get_id()) {
                $latest = $token;
            }
        }

        if ($latest) {
            $order->update_meta_data('_wcpd_token_id', $latest->get_id());
            $order->update_meta_data('_wcpd_gateway_id', $latest->get_gateway_id());
            $order->save_meta_data();
        }
    }

    public static function ajax_charge() {
        check_ajax_referer('wcpd_admin_action', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Access denied.', 'pre-order-deposit-manager'));
        }

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $order    = wc_get_order($order_id);

        if (!$order || !WCPD_Order::is_preorder($order)) {
            wp_send_json_error(__('Invalid order.', 'pre-order-deposit-manager'));
        }

        $remaining = (float) $order->get_meta('_wcpd_remaining_total');
        if ($remaining <= 0) {
            wp_send_json_error(__('No remaining payment.', 'pre-order-deposit-manager'));
        }

        $token_id   = (int) $order->get_meta('_wcpd_token_id');
        $gateway_id = $order->get_meta('_wcpd_gateway_id');

        if (!$token_id || !$gateway_id) {
            wp_send_json_error(__('Customer has no saved card. Use manual method.', 'pre-order-deposit-manager'));
        }

        $token   = WC_Payment_Tokens::get($token_id);
        $gateways = WC()->payment_gateways()->payment_gateways();
        $gateway  = isset($gateways[$gateway_id]) ? $gateways[$gateway_id] : null;

        if (!$token || !$gateway) {
            wp_send_json_error(__('Invalid gateway.', 'pre-order-deposit-manager'));
        }

        $order->update_meta_data('_payment_token_id', $token_id);
        $order->save_meta_data();

        $result = apply_filters('wcpd_process_auto_charge', null, $order, $token, $remaining, $gateway);

        if ($result === null && method_exists($gateway, 'process_payment')) {
            WC()->session->set('chosen_payment_method', $gateway_id);
            $result = $gateway->process_payment($order_id);
        }

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        if (is_array($result) && isset($result['result']) && $result['result'] === 'success') {
            $order->update_meta_data('_wcpd_remaining_paid', $remaining);
            $order->update_meta_data('_wcpd_remaining_paid_date', current_time('mysql'));
            $order->update_status('wc-preorder-completed', __('Final payment processed. Order completed.', 'pre-order-deposit-manager'));
            $order->save_meta_data();

            wp_send_json_success(__('Final payment processed. Order completed.', 'pre-order-deposit-manager'));
        }

        wp_send_json_error(__('Automatic charging failed. Use manual method or gateway directly.', 'pre-order-deposit-manager'));
    }

    public static function ajax_ready() {
        check_ajax_referer('wcpd_admin_action', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Access denied.', 'pre-order-deposit-manager'));
        }

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $order    = wc_get_order($order_id);

        if (!$order || !WCPD_Order::is_preorder($order)) {
            wp_send_json_error(__('Invalid order.', 'pre-order-deposit-manager'));
        }

        $order->update_status('wc-preorder-ready', __('Product is ready for delivery.', 'pre-order-deposit-manager'));

        do_action('wcpd_preorder_ready_notification', $order_id);

        wp_send_json_success(__('Status changed. Customer notified.', 'pre-order-deposit-manager'));
    }
}
