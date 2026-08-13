<?php
if (!defined('ABSPATH')) {
    exit;
}

class WCPD_Product {

    public static function init() {
        add_filter('product_type_options', array(__CLASS__, 'add_option'));
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'save_option'));
        add_action('woocommerce_before_single_product_summary', array(__CLASS__, 'preorder_badge'), 5);
    }

    public static function add_option($opts) {
        $opts['preorder_deposit'] = array(
            'id'            => '_wcpd_enabled',
            'wrapper_class' => 'show_if_simple show_if_variable',
            'label'         => __('Pre-Order Deposit', 'pre-order-deposit-manager'),
            'description'   => __('Customer pays 30% now, the rest on delivery.', 'pre-order-deposit-manager'),
            'default'       => 'no',
        );
        return $opts;
    }

    public static function save_option($post_id) {
        $enabled = isset($_POST['_wcpd_enabled']) ? 'yes' : 'no';
        update_post_meta($post_id, '_wcpd_enabled', $enabled);
    }

    public static function is_preorder($product_id) {
        return get_post_meta($product_id, '_wcpd_enabled', true) === 'yes';
    }

    public static function preorder_badge() {
        global $product;
        if (!$product || !self::is_preorder($product->get_id())) {
            return;
        }
        echo '<div class="wcpd-badge-ribbon"><span>' . esc_html__('PRE-ORDER', 'pre-order-deposit-manager') . '</span></div>';
    }
}
