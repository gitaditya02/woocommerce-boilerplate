<?php
/**
 * B2B Pricing Engine
 * Handles role-based and tier-based pricing logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Pricing_Engine {

    /**
     * Register hooks
     */
    public static function init() {
        add_filter('woocommerce_product_get_price', [__CLASS__, 'apply_wholesale_price'], 10, 2);
        add_filter('woocommerce_product_variation_get_price', [__CLASS__, 'apply_wholesale_price'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'apply_cart_discount'], 20);
    }

    /**
     * Apply wholesale price to product
     */
    public static function apply_wholesale_price($price, $product) {
        if (!is_user_logged_in() || !self::is_wholesale_customer()) {
            return $price;
        }

        if (is_admin() && !defined('DOING_AJAX')) {
            return $price;
        }

        $user_id = get_current_user_id();
        $tier = get_user_meta($user_id, 'b2b_customer_tier', true);
        $discount = self::get_tier_discount($tier);

        if ($discount > 0) {
            $price = $price * (1 - ($discount / 100));
        }

        return $price;
    }

    /**
     * Apply cart discount on checkout
     */
    public static function apply_cart_discount($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (!is_user_logged_in() || !self::is_wholesale_customer()) {
            return;
        }

        $user_id = get_current_user_id();
        $tier = get_user_meta($user_id, 'b2b_customer_tier', true);
        $discount = self::get_tier_discount($tier);

        if ($discount <= 0) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if ($product && $product->exists()) {
                $price = (float) $product->get_price();
                $new_price = $price * (1 - ($discount / 100));
                $product->set_price($new_price);
            }
        }
    }

    /**
     * Get tier discount
     */
    public static function get_tier_discount($tier) {
        $tier_discounts = [
            'standard' => (float) get_option('b2b_wc_wholesale_discount', 10),
            'silver' => (float) get_option('b2b_wc_silver_discount', 15),
            'gold' => (float) get_option('b2b_wc_gold_discount', 20),
            'platinum' => (float) get_option('b2b_wc_platinum_discount', 25),
        ];
        return isset($tier_discounts[$tier]) ? $tier_discounts[$tier] : 0;
    }

    /**
     * Check if current user is wholesale customer
     */
    public static function is_wholesale_customer() {
        if (!is_user_logged_in()) {
            return false;
        }
        $user = wp_get_current_user();
        return in_array('b2b_wholesale_customer', (array) $user->roles, true);
    }
}
