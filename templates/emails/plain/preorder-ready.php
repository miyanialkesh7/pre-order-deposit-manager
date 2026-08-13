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
    __('Your pre-order #%s is ready for delivery.', 'pre-order-deposit-manager'),
    $order->get_order_number()
) . "\n";
echo "==========================================\n\n";

_e('Payment Summary', 'pre-order-deposit-manager') . "\n";
echo "- " . __('Total Value:', 'pre-order-deposit-manager') . " " . strip_tags(wc_price($full)) . "\n";
echo "- " . __('Deposit Paid:', 'pre-order-deposit-manager') . " " . strip_tags(wc_price($deposit)) . "\n";
echo "- " . __('Remaining Amount:', 'pre-order-deposit-manager') . " " . strip_tags(wc_price($remaining)) . "\n\n";

_e('Please complete the payment as soon as possible so we can deliver your order.', 'pre-order-deposit-manager') . "\n";
_e('If you have any questions, our team is happy to help.', 'pre-order-deposit-manager') . "\n\n";
echo "----------------------------------------\n";
echo esc_html(get_bloginfo('name')) . "\n";
