(function($) {
    'use strict';

    $(document).ready(function() {
        $('.wcpd-cart-notice').hide().slideDown(350);

        $('body').on('updated_checkout', function() {
            $('.cart_item').each(function() {
                var $item = $(this);
                if ($item.find('.product-name').text().toLowerCase().indexOf('pre-order') === -1) {
                    if ($item.find('.product-total .amount').length) {
                        // deposit row styling hook
                    }
                }
            });
        });
    });
})(jQuery);
