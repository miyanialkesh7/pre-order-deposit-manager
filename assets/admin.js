(function($) {
    'use strict';

    $(document).ready(function() {
        $('.wcpd-btn-ready').on('click', function(e) {
            e.preventDefault();
            if (!confirm(wcpd_admin.strings.confirm_ready)) {
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).addClass('wcpd-btn-loading').html('<span class="wcpd-spinner"></span> ' + wcpd_admin.strings.confirm_ready);

            $.post(wcpd_admin.ajax_url, {
                action: 'wcpd_mark_ready_manual',
                order_id: btn.data('order'),
                nonce: wcpd_admin.nonce
            }, function(res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert('Error: ' + res.data);
                    btn.prop('disabled', false).removeClass('wcpd-btn-loading').html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Mark Ready & Notify');
                }
            });
        });

        $('.wcpd-btn-charge').on('click', function(e) {
            e.preventDefault();
            if (!confirm(wcpd_admin.strings.confirm_charge)) {
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).addClass('wcpd-btn-loading').html('<span class="wcpd-spinner"></span> Processing...');

            $.post(wcpd_admin.ajax_url, {
                action: 'wcpd_charge_remaining',
                order_id: btn.data('order'),
                nonce: wcpd_admin.nonce
            }, function(res) {
                if (res.success) {
                    alert('Success: ' + res.data);
                    location.reload();
                } else {
                    alert('Charge error: ' + res.data);
                    btn.prop('disabled', false).removeClass('wcpd-btn-loading').html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg> Auto-Charge Remainder');
                }
            });
        });
    });
})(jQuery);
