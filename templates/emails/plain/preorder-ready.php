<?php
/**
 * Plain-text "your pre-order is ready for delivery" email template.
 *
 * @package WC_Preorder_Deposit
 *
 * @var WC_Order $order         The pre-order that is now ready for delivery.
 * @var string   $email_heading The heading configured for this email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$order     = $args['order']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- local template variable, not the WP $order global.
$remaining = (float) $order->get_meta( '_wcpd_remaining_total' );
$full      = (float) $order->get_meta( '_wcpd_full_total' );
$deposit   = (float) $order->get_meta( '_wcpd_deposit_total' );

echo esc_html( $email_heading ) . "\n\n";
echo "==========================================\n";
printf( __( 'Your pre-order #%s is ready for delivery.', 'wc-preorder-deposit' ), $order->get_order_number() ) . "\n";
echo "==========================================\n\n";

_e( 'Payment Summary', 'wc-preorder-deposit' ) . "\n";
echo '- ' . __( 'Total Value:', 'wc-preorder-deposit' ) . ' ' . strip_tags( wc_price( $full ) ) . "\n";
echo '- ' . __( 'Deposit Paid:', 'wc-preorder-deposit' ) . ' ' . strip_tags( wc_price( $deposit ) ) . "\n";
echo '- ' . __( 'Remaining Amount:', 'wc-preorder-deposit' ) . ' ' . strip_tags( wc_price( $remaining ) ) . "\n\n";

_e( 'Please complete the payment as soon as possible so we can deliver your order.', 'wc-preorder-deposit' ) . "\n";
_e( 'If you have any questions, our team is happy to help.', 'wc-preorder-deposit' ) . "\n\n";
echo "----------------------------------------\n";
echo esc_html( get_bloginfo( 'name' ) ) . "\n";
