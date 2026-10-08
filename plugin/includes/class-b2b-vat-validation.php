<?php
/**
 * B2B Company Pricing Rules
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Company_Pricing {
    public static function init() {
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'apply_company_rules'], 30);
        add_filter('woocommerce_get_price_html', [__CLASS__, 'append_company_tag'], 10, 2);
    }

    public static function apply_company_rules($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (!is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();
        if (!in_array('b2b_wholesale_customer', (array) $user->roles, true)) {
            return;
        }

        $company_name = get_user_meta($user->ID, 'company_name', true);
        if (empty($company_name)) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if (!$product || !$product->exists()) {
                continue;
            }

            $rule = self::get_company_rule($company_name, $product->get_id());
            if (!$rule) {
                continue;
            }

            $price = (float) $product->get_price();
            $product->set_price($price * (1 - ($rule['discount'] / 100)));
        }
    }

    public static function append_company_tag($price_html, $product) {
        if (!is_user_logged_in()) {
            return $price_html;
        }

        $user = wp_get_current_user();
        if (!in_array('b2b_wholesale_customer', (array) $user->roles, true)) {
            return $price_html;
        }

        $company_name = get_user_meta($user->ID, 'company_name', true);
        if (empty($company_name)) {
            return $price_html;
        }

        $rule = self::get_company_rule($company_name, $product->get_id());
        if (!$rule) {
            return $price_html;
        }

        return $price_html . ' <small>(' . esc_html__('Company pricing applied', 'b2b-wc-extension') . ')</small>';
    }

    public static function get_company_rule($company_name, $product_id) {
        $rules = get_option('b2b_company_pricing_rules', []);
        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $rule) {
            if (($rule['company_name'] ?? '') === $company_name && (int) ($rule['product_id'] ?? 0) === (int) $product_id) {
                return $rule;
            }
        }

        return false;
    }
}
