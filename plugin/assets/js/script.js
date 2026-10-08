/**
 * WooCommerce Custom Plugin - Frontend Script
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize custom functionality
        initCustomFunctionality();
    });

    /**
     * Initialize custom functionality
     */
    function initCustomFunctionality() {
        // Add your custom JavaScript here
        console.log('WooCommerce Custom Plugin loaded');

        // Example: Listen to add to cart button
        $(document.body).on('added_to_cart', function() {
            console.log('Product added to cart');
        });
    }

    /**
     * AJAX example function
     */
    function customAjaxCall(data) {
        $.ajax({
            url: wcCustom.ajaxurl,
            type: 'POST',
            data: {
                action: 'custom_action',
                nonce: wcCustom.nonce,
                data: data
            },
            success: function(response) {
                console.log('Success:', response);
            },
            error: function(error) {
                console.error('Error:', error);
            }
        });
    }

})(jQuery);
