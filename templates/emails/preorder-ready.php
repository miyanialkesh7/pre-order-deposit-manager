<?php
if (!defined('ABSPATH')) {
    exit;
}
$order = $args['order'];
$remaining = (float) $order->get_meta('_wcpd_remaining_total');
$full = (float) $order->get_meta('_wcpd_full_total');
$deposit = (float) $order->get_meta('_wcpd_deposit_total');
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html(get_bloginfo('name')); ?></title>
<style>
@media only screen and (max-width: 600px) {
    .wcpd-wrap { width: 100% !important; padding: 20px 16px !important; }
    .wcpd-card { padding: 24px 20px !important; }
    .wcpd-row { display: block !important; width: 100% !important; }
    .wcpd-row td { display: block !important; width: 100% !important; padding-left: 0 !important; padding-right: 0 !important; }
}
</style>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#f1f5f9">
<tr>
<td align="center" style="padding:40px 16px;">
    <table role="presentation" class="wcpd-wrap" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:600px;">
        <tr>
            <td align="center" style="padding-bottom:28px;">
                <h1 style="margin:0;font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;"><?php echo esc_html(get_bloginfo('name')); ?></h1>
            </td>
        </tr>

        <tr>
            <td class="wcpd-card" style="background:#ffffff;border-radius:12px;padding:40px 32px;text-align:center;box-shadow:0 4px 6px -1px rgba(0,0,0,0.04);">
                <div style="display:inline-block;padding:12px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:50%;margin-bottom:20px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h2 style="margin:0 0 8px 0;font-size:20px;font-weight:700;color:#0f172a;"><?php esc_html_e('Your product is ready', 'wc-preorder-deposit'); ?></h2>
                <p style="margin:0;font-size:15px;color:#64748b;line-height:1.5;">
                    <?php
                    printf(
                        /* translators: %s: order number wrapped in a styled <strong> tag. */
                        esc_html__('Hi! Your pre-order %s is now ready for delivery.', 'wc-preorder-deposit'),
                        '<strong style="color:#4f46e5;">#' . esc_html($order->get_order_number()) . '</strong>'
                    );
                    ?>
                </p>
            </td>
        </tr>

        <tr><td style="height:16px;"></td></tr>

        <tr>
            <td class="wcpd-card" style="background:#ffffff;border-radius:12px;padding:32px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.04);">
                <h3 style="margin:0 0 20px 0;font-size:14px;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;font-weight:700;"><?php esc_html_e('Payment Summary', 'wc-preorder-deposit'); ?></h3>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding:12px 0;border-bottom:1px solid #f1f5f9;">
                            <span style="font-size:14px;color:#64748b;"><?php esc_html_e('Total Value', 'wc-preorder-deposit'); ?></span>
                        </td>
                        <td align="right" style="padding:12px 0;border-bottom:1px solid #f1f5f9;">
                            <span style="font-size:14px;font-weight:700;color:#0f172a;"><?php echo wc_price($full); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wc_price() escapes its own output. ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 0;border-bottom:1px solid #f1f5f9;">
                            <span style="font-size:14px;color:#64748b;"><?php esc_html_e('Deposit Paid', 'wc-preorder-deposit'); ?></span>
                        </td>
                        <td align="right" style="padding:12px 0;border-bottom:1px solid #f1f5f9;">
                            <span style="font-size:14px;font-weight:700;color:#4f46e5;"><?php echo wc_price($deposit); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wc_price() escapes its own output. ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 0 0 0;">
                            <span style="font-size:14px;color:#0f172a;font-weight:600;"><?php esc_html_e('Remaining Amount', 'wc-preorder-deposit'); ?></span>
                        </td>
                        <td align="right" style="padding:16px 0 0 0;">
                            <span style="font-size:18px;font-weight:800;color:#d97706;"><?php echo wc_price($remaining); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wc_price() escapes its own output. ?></span>
                        </td>
                    </tr>
                </table>

                <div style="margin-top:28px;padding:16px;background:#f5f3ff;border-radius:8px;border-left:4px solid #4f46e5;">
                    <p style="margin:0;font-size:13px;color:#3730a3;line-height:1.5;">
                        <?php esc_html_e('Please complete the payment as soon as possible so we can deliver your order. If you have any questions, our team is happy to help.', 'wc-preorder-deposit'); ?>
                    </p>
                </div>
            </td>
        </tr>

        <tr><td style="height:16px;"></td></tr>

        <tr>
            <td align="center" style="padding:0 8px 24px 8px;">
                <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
                    <?php echo esc_html(get_bloginfo('name')); ?> &bull; <?php echo esc_html(get_bloginfo('admin_email')); ?>
                </p>
            </td>
        </tr>
    </table>
</td>
</tr>
</table>
</body>
</html>
