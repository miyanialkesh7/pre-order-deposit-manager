<?php
/**
 * Shared helpers for identifying and validating pre-order orders.
 *
 * @package WC_Preorder_Deposit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the pre-order statuses as payable and exposes WCPD_Order::is_preorder().
 */
class WCPD_Order {

	/**
	 * Allows WooCommerce to accept payment while an order is in a pre-order status.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'woocommerce_valid_order_statuses_for_payment', array( __CLASS__, 'allow_payment' ) );
	}

	/**
	 * Adds the pre-order statuses to the list of statuses that accept payment.
	 *
	 * @param array $statuses Order status slugs that currently accept payment.
	 * @return array Order status slugs including the pre-order statuses.
	 */
	public static function allow_payment( $statuses ) {
		$statuses[] = 'preorder-deposit';
		$statuses[] = 'preorder-ready';
		return $statuses;
	}

	/**
	 * Checks whether an order is a pre-order (has taken a deposit payment).
	 *
	 * @param int|WC_Order $order Order ID or order object.
	 * @return bool True if the order is a pre-order.
	 */
	public static function is_preorder( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}
		if ( ! $order ) {
			return false;
		}
		return $order->get_meta( '_wcpd_is_preorder' ) === 'yes';
	}
}
