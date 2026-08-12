<?php
if (!defined('ABSPATH')) {
    exit;
}

class WCPD_Order {

    public static function init() {
        add_filter('woocommerce_valid_order_statuses_for_payment', array(__CLASS__, 'allow_payment'));
    }

    public static function allow_payment($statuses) {
        $statuses[] = 'preorder-deposit';
        $statuses[] = 'preorder-ready';
        return $statuses;
    }

    public static function is_preorder($order) {
        if (is_numeric($order)) {
            $order = wc_get_order($order);
        }
        if (!$order) {
            return false;
        }
        return $order->get_meta('_wcpd_is_preorder') === 'yes';
    }
}
