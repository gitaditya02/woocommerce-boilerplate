<?php
/**
 * VAT/GSTIN Validation
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_VAT_Validation {
    public static function init() {
        add_action('woocommerce_checkout_process', [__CLASS__, 'validate_vat_or_gstin']);
        add_action('woocommerce_after_checkout_billing_form', [__CLASS__, 'render_tax_id_field']);
    }

    public static function render_tax_id_field() {
        if (!is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();
        if (!in_array('b2b_wholesale_customer', (array) $user->roles, true)) {
            return;
        }

        $stored_value = get_user_meta(get_current_user_id(), 'billing_vat_number', true);

        woocommerce_form_field('b2b_tax_id', [
            'type' => 'text',
            'label' => __('VAT / GSTIN Number', 'b2b-wc-extension'),
            'placeholder' => __('e.g., GB123456789 or 27ABCDE1234F1Z5', 'b2b-wc-extension'),
            'required' => true,
            'default' => $stored_value,
        ]);
    }

    public static function validate_vat_or_gstin() {
        if (!is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();
        if (!in_array('b2b_wholesale_customer', (array) $user->roles, true)) {
            return;
        }

        $tax_id = isset($_POST['b2b_tax_id']) ? strtoupper(sanitize_text_field(wp_unslash($_POST['b2b_tax_id']))) : '';
        if (empty($tax_id)) {
            wc_add_notice(__('A valid VAT/GSTIN number is required for wholesale checkout.', 'b2b-wc-extension'), 'error');
            return;
        }

        if (!self::is_valid_tax_id($tax_id)) {
            wc_add_notice(__('The entered VAT/GSTIN number is invalid. Please confirm it is correct.', 'b2b-wc-extension'), 'error');
        }

        update_user_meta(get_current_user_id(), 'billing_vat_number', $tax_id);
    }

    public static function is_valid_tax_id($tax_id) {
        if (empty($tax_id)) {
            return false;
        }

        if (preg_match('/^[A-Z0-9]{8,15}$/', $tax_id)) {
            return true;
        }

        if (preg_match('/^[A-Z]{2}[A-Z0-9]{5,12}$/', $tax_id)) {
            return true;
        }

        return false;
    }
}
