<?php
/**
 * Handles saved-card capture and the admin AJAX actions for pre-order orders.
 *
 * @package WC_Preorder_Deposit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Captures payment tokens and processes the "ready" / "charge remainder" admin actions.
 */
class WCPD_Payment {

	/**
	 * Registers the payment-capture hook and the admin AJAX action handlers.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'capture_token' ) );
		add_action( 'wp_ajax_wcpd_charge_remaining', array( __CLASS__, 'ajax_charge' ) );
		add_action( 'wp_ajax_wcpd_mark_ready_manual', array( __CLASS__, 'ajax_ready' ) );
	}

	/**
	 * Stores the customer's most recent saved payment token on the order.
	 *
	 * @param int $order_id The order that just completed payment.
	 * @return void
	 */
	public static function capture_token( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || ! WCPD_Order::is_preorder( $order ) ) {
			return;
		}

		$user_id = $order->get_customer_id();
		if ( ! $user_id ) {
			return;
		}

		$tokens = WC_Payment_Tokens::get_customer_tokens( $user_id );
		if ( empty( $tokens ) ) {
			return;
		}

		$latest = null;
		foreach ( $tokens as $token ) {
			if ( ! $latest || $token->get_id() > $latest->get_id() ) {
				$latest = $token;
			}
		}

		if ( $latest ) {
			$order->update_meta_data( '_wcpd_token_id', $latest->get_id() );
			$order->update_meta_data( '_wcpd_gateway_id', $latest->get_gateway_id() );
			$order->save_meta_data();
		}
	}

	/**
	 * AJAX handler: attempts to auto-charge the remaining balance via the
	 * customer's saved payment token.
	 *
	 * @return void
	 */
	public static function ajax_charge() {
		check_ajax_referer( 'wcpd_admin_action', 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'Access denied.', 'wc-preorder-deposit' ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$order    = wc_get_order( $order_id );

		if ( ! $order || ! WCPD_Order::is_preorder( $order ) ) {
			wp_send_json_error( __( 'Invalid order.', 'wc-preorder-deposit' ) );
		}

		$remaining = (float) $order->get_meta( '_wcpd_remaining_total' );
		if ( $remaining <= 0 ) {
			wp_send_json_error( __( 'No remaining payment.', 'wc-preorder-deposit' ) );
		}

		$token_id   = (int) $order->get_meta( '_wcpd_token_id' );
		$gateway_id = $order->get_meta( '_wcpd_gateway_id' );

		if ( ! $token_id || ! $gateway_id ) {
			wp_send_json_error( __( 'Customer has no saved card. Use manual method.', 'wc-preorder-deposit' ) );
		}

		$token    = WC_Payment_Tokens::get( $token_id );
		$gateways = WC()->payment_gateways()->payment_gateways();
		$gateway  = isset( $gateways[ $gateway_id ] ) ? $gateways[ $gateway_id ] : null;

		if ( ! $token || ! $gateway ) {
			wp_send_json_error( __( 'Invalid gateway.', 'wc-preorder-deposit' ) );
		}

		$order->update_meta_data( '_payment_token_id', $token_id );
		$order->save_meta_data();

		$result = apply_filters( 'wcpd_process_auto_charge', null, $order, $token, $remaining, $gateway );

		if ( null === $result && method_exists( $gateway, 'process_payment' ) ) {
			WC()->session->set( 'chosen_payment_method', $gateway_id );
			$result = $gateway->process_payment( $order_id );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		if ( is_array( $result ) && isset( $result['result'] ) && 'success' === $result['result'] ) {
			$order->update_meta_data( '_wcpd_remaining_paid', $remaining );
			$order->update_meta_data( '_wcpd_remaining_paid_date', current_time( 'mysql' ) );
			$order->update_status( 'wc-preorder-completed', __( 'Final payment processed. Order completed.', 'wc-preorder-deposit' ) );
			$order->save_meta_data();

			wp_send_json_success( __( 'Final payment processed. Order completed.', 'wc-preorder-deposit' ) );
		}

		wp_send_json_error( __( 'Automatic charging failed. Use manual method or gateway directly.', 'wc-preorder-deposit' ) );
	}

	/**
	 * AJAX handler: marks the order ready for delivery and notifies the customer.
	 *
	 * @return void
	 */
	public static function ajax_ready() {
		check_ajax_referer( 'wcpd_admin_action', 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'Access denied.', 'wc-preorder-deposit' ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$order    = wc_get_order( $order_id );

		if ( ! $order || ! WCPD_Order::is_preorder( $order ) ) {
			wp_send_json_error( __( 'Invalid order.', 'wc-preorder-deposit' ) );
		}

		$order->update_status( 'wc-preorder-ready', __( 'Product is ready for delivery.', 'wc-preorder-deposit' ) );

		do_action( 'wcpd_preorder_ready_notification', $order_id );

		wp_send_json_success( __( 'Status changed. Customer notified.', 'wc-preorder-deposit' ) );
	}
}
