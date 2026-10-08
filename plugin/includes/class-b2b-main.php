(function ($) {
    'use strict';

    $(document).ready(function () {
        console.log('B2B WooCommerce Extension loaded');

        $('#b2b_select_all').on('click', function () {
            $('.b2b-user-check').prop('checked', this.checked);
        });
    });
})(jQuery);
