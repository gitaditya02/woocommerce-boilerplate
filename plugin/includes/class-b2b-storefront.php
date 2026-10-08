<?php
/**
 * B2B Tax Validation
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Tax_Validation {
    public static function init() {
        add_action('woocommerce_checkout_process', [__CLASS__, 'validate_tax_id']);
        add_action('woocommerce_after_checkout_billing_form', [__CLASS__, 'render_tax_field']);
    }

    public static function render_tax_field() {
        if (!is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();
        if (!in_array('b2b_wholesale_customer', (array) $user->roles, true)) {
            return;
        }

        woocommerce_form_field('b2b_tax_id', [
            'type' => 'text',
            'label' => __('Tax ID / VAT Number', 'b2b-wc-extension'),
            'placeholder' => __('Enter your tax ID', 'b2b-wc-extension'),
            'required' => true,
        ]);
    }

    public static function validate_tax_id() {
        if (!is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();
        if (!in_array('b2b_wholesale_customer', (array) $user->roles, true)) {
            return;
        }

        $tax_id = isset($_POST['b2b_tax_id']) ? sanitize_text_field(wp_unslash($_POST['b2b_tax_id'])) : '';
        if (empty($tax_id)) {
            wc_add_notice(__('A valid tax ID is required for wholesale checkout.', 'b2b-wc-extension'), 'error');
        }
    }
}
