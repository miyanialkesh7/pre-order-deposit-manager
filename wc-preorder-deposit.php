<?php
/**
 * Plugin Name: WC Pre-Order Deposit
 * Description: Customers pay a 30% deposit on pre-order. The remaining 70% is charged automatically or manually when the product is marked ready for delivery.
 * Version: 1.0.0
 * Author: BYOT
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * License: GPLv2
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WCPD_VERSION', '1.0.0');
define('WCPD_DIR', plugin_dir_path(__FILE__));
define('WCPD_URL', plugin_dir_url(__FILE__));

add_action('init', 'wcpd_register_statuses', 9);

function wcpd_register_statuses() {
    $statuses = array(
        'wc-preorder-deposit' => array(
            'label'                     => _x('Pre-Order: Deposit Paid', 'Order status', 'wc-preorder-deposit'),
            'public'                    => false,
            'exclude_from_search'       => false,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'label_count'               => _n_noop('Pre-Order: Deposit Paid <span class="count">(%s)</span>', 'Pre-Order: Deposit Paid <span class="count">(%s)</span>', 'wc-preorder-deposit'),
        ),
        'wc-preorder-ready' => array(
            'label'                     => _x('Pre-Order: Ready for Delivery', 'Order status', 'wc-preorder-deposit'),
            'public'                    => false,
            'exclude_from_search'       => false,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'label_count'               => _n_noop('Pre-Order: Ready for Delivery <span class="count">(%s)</span>', 'Pre-Order: Ready for Delivery <span class="count">(%s)</span>', 'wc-preorder-deposit'),
        ),
        'wc-preorder-completed' => array(
            'label'                     => _x('Pre-Order: Completed', 'Order status', 'wc-preorder-deposit'),
            'public'                    => false,
            'exclude_from_search'       => false,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'label_count'               => _n_noop('Pre-Order: Completed <span class="count">(%s)</span>', 'Pre-Order: Completed <span class="count">(%s)</span>', 'wc-preorder-deposit'),
        ),
    );

    foreach ($statuses as $slug => $args) {
        register_post_status($slug, $args);
    }
}

add_filter('wc_order_statuses', 'wcpd_add_to_order_statuses');

function wcpd_add_to_order_statuses($statuses) {
    $new = array();
    foreach ($statuses as $key => $label) {
        $new[$key] = $label;
        if ($key === 'wc-processing') {
            $new['wc-preorder-deposit']   = _x('Pre-Order: Deposit Paid', 'Order status', 'wc-preorder-deposit');
            $new['wc-preorder-ready']     = _x('Pre-Order: Ready for Delivery', 'Order status', 'wc-preorder-deposit');
            $new['wc-preorder-completed'] = _x('Pre-Order: Completed', 'Order status', 'wc-preorder-deposit');
        }
    }
    return $new;
}

require_once WCPD_DIR . 'includes/class-wcpd-product.php';
require_once WCPD_DIR . 'includes/class-wcpd-cart.php';
require_once WCPD_DIR . 'includes/class-wcpd-order.php';
require_once WCPD_DIR . 'includes/class-wcpd-admin.php';
require_once WCPD_DIR . 'includes/class-wcpd-payment.php';

add_action('plugins_loaded', 'wcpd_init_plugin');

function wcpd_init_plugin() {
    if (!class_exists('WooCommerce')) {
        return;
    }

    WCPD_Product::init();
    WCPD_Cart::init();
    WCPD_Order::init();
    WCPD_Admin::init();
    WCPD_Payment::init();

    add_action('wp_enqueue_scripts', 'wcpd_enqueue_assets');
    add_action('admin_enqueue_scripts', 'wcpd_admin_assets');
    add_filter('woocommerce_email_classes', 'wcpd_register_email_class');

    // Force WooCommerce's mailer to initialize now, in the same request. This
    // guarantees our woocommerce_email_classes callback (and therefore the
    // wcpd_preorder_ready_notification listener it registers) runs on every
    // request, rather than only when something else happens to trigger the
    // mailer first.
    WC()->mailer();
}

function wcpd_enqueue_assets() {
    if (!function_exists('is_woocommerce')) {
        return;
    }
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_product()) {
        return;
    }
    wp_enqueue_style('wcpd-public', WCPD_URL . 'assets/public.css', array(), WCPD_VERSION);
    wp_enqueue_script('wcpd-public', WCPD_URL . 'assets/public.js', array('jquery'), WCPD_VERSION, true);
}

function wcpd_admin_assets($hook) {
    global $post_type, $post;
    if (($hook === 'edit.php' && $post_type === 'shop_order') || ($hook === 'post.php' && isset($post) && $post->post_type === 'shop_order')) {
        wp_enqueue_style('wcpd-admin', WCPD_URL . 'assets/admin.css', array(), WCPD_VERSION);
    }
}

function wcpd_register_email_class($emails) {
    // Loaded here rather than at plugins_loaded because WC_Email itself is
    // only available once WooCommerce reaches this point in WC_Emails::init().
    require_once WCPD_DIR . 'includes/class-wcpd-emails.php';

    $emails['WCPD_Email_Preorder_Ready'] = new WCPD_Email_Preorder_Ready();

    // trigger() is an instance method, so the listener must reference this
    // specific object rather than the class name (a non-static method can't
    // be invoked as a static callback).
    add_action('wcpd_preorder_ready_notification', array($emails['WCPD_Email_Preorder_Ready'], 'trigger'), 10, 1);

    return $emails;
}

register_activation_hook(__FILE__, 'wcpd_activate');

function wcpd_activate() {
    wcpd_register_statuses();
    flush_rewrite_rules();
}
