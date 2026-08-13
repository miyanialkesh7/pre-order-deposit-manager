<?php
if (!defined('ABSPATH')) {
    exit;
}
$order = $args['order'];
$remaining = (float) $order->get_meta('_wcpd_remaining_total');
$full = (float) $order->get_meta('_wcpd_full_total');
$deposit = (float) $order->get_meta('_wcpd_deposit_total');

echo esc_html($email_heading) . "\n\n";
echo "==========================================\n";
printf(
    /* translators: %s: order number. */
    esc_html__('Your pre-order #%s is ready for delivery.', 'wc-preorder-deposit'),
    esc_html($order->get_order_number())
);
echo "\n==========================================\n\n";

esc_html_e('Payment Summary', 'wc-preorder-deposit');
echo "\n";
echo "- " . esc_html__('Total Value:', 'wc-preorder-deposit') . " " . wp_strip_all_tags(wc_price($full)) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- value derived from wc_price() on a float, tags stripped for the plain-text email.
echo "- " . esc_html__('Deposit Paid:', 'wc-preorder-deposit') . " " . wp_strip_all_tags(wc_price($deposit)) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- value derived from wc_price() on a float, tags stripped for the plain-text email.
echo "- " . esc_html__('Remaining Amount:', 'wc-preorder-deposit') . " " . wp_strip_all_tags(wc_price($remaining)) . "\n\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- value derived from wc_price() on a float, tags stripped for the plain-text email.

esc_html_e('Please complete the payment as soon as possible so we can deliver your order.', 'wc-preorder-deposit');
echo "\n";
esc_html_e('If you have any questions, our team is happy to help.', 'wc-preorder-deposit');
echo "\n\n";
echo "----------------------------------------\n";
echo esc_html(get_bloginfo('name')) . "\n";
