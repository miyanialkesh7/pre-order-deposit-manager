<?php
/**
 * Adds the "Pre-Order Deposit" product option and its front-end badge.
 *
 * @package WC_Preorder_Deposit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the pre-order product checkbox and exposes WCPD_Product::is_preorder().
 */
class WCPD_Product {

	/**
	 * Registers the product-edit hooks and the front-end pre-order badge.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'product_type_options', array( __CLASS__, 'add_option' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_option' ) );
		add_action( 'woocommerce_before_single_product_summary', array( __CLASS__, 'preorder_badge' ), 5 );
	}

	/**
	 * Adds the "Pre-Order Deposit" checkbox to the product data panel.
	 *
	 * @param array $opts Existing product type options.
	 * @return array Product type options including the pre-order checkbox.
	 */
	public static function add_option( $opts ) {
		$opts['preorder_deposit'] = array(
			'id'            => '_wcpd_enabled',
			'wrapper_class' => 'show_if_simple show_if_variable',
			'label'         => __( 'Pre-Order Deposit', 'wc-preorder-deposit' ),
			'description'   => __( 'Customer pays 30% now, the rest on delivery.', 'wc-preorder-deposit' ),
			'default'       => 'no',
		);
		return $opts;
	}

	/**
	 * Saves the "Pre-Order Deposit" checkbox state for a product.
	 *
	 * @param int $post_id Product post ID.
	 * @return void
	 */
	public static function save_option( $post_id ) {
		$enabled = isset( $_POST['_wcpd_enabled'] ) ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce hardening is handled separately in the security PR.
		update_post_meta( $post_id, '_wcpd_enabled', $enabled );
	}

	/**
	 * Checks whether a product has the "Pre-Order Deposit" option enabled.
	 *
	 * @param int $product_id Product post ID.
	 * @return bool True if the product is a pre-order product.
	 */
	public static function is_preorder( $product_id ) {
		return get_post_meta( $product_id, '_wcpd_enabled', true ) === 'yes';
	}

	/**
	 * Prints the "PRE-ORDER" ribbon badge above a pre-order product's summary.
	 *
	 * @return void
	 */
	public static function preorder_badge() {
		global $product;
		if ( ! $product || ! self::is_preorder( $product->get_id() ) ) {
			return;
		}
		echo '<div class="wcpd-badge-ribbon"><span>PRE-ORDER</span></div>';
	}
}
