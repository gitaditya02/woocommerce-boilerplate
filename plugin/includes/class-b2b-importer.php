<?php
/**
 * B2B Quantity Pricing
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Quantity_Pricing {
    public static function init() {
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'adjust_quantity_pricing'], 25);
        add_filter('woocommerce_get_price_html', [__CLASS__, 'show_quantity_pricing_note'], 10, 2);
    }

    public static function adjust_quantity_pricing($cart) {
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

        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if (!$product || !$product->exists()) {
                continue;
            }

            $quantity = $cart_item['quantity'];
            $base_price = (float) $product->get_price();
            $discount = self::get_quantity_discount($quantity);

            if ($discount > 0) {
                $product->set_price($base_price * (1 - ($discount / 100)));
            }
        }
    }

    public static function get_quantity_discount($quantity) {
        if ($quantity >= 50) {
            return 20;
        }
        if ($quantity >= 25) {
            return 15;
        }
        if ($quantity >= 10) {
            return 10;
        }
        if ($quantity >= 5) {
            return 5;
        }

        return 0;
    }

    public static function show_quantity_pricing_note($price_html, $product) {
        if (!is_user_logged_in()) {
            return $price_html;
        }

        $user = wp_get_current_user();
        if (!in_array('b2b_wholesale_customer', (array) $user->roles, true)) {
            return $price_html;
        }

        return $price_html . ' <small>' . esc_html__('Volume pricing available on larger orders.', 'b2b-wc-extension') . '</small>';
    }
}
