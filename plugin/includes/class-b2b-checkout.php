<?php
/**
 * B2B Checkout Handler
 * Custom checkout flow for wholesale customers
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Checkout {

    /**
     * Register hooks
     */
    public static function init() {
        add_action('woocommerce_before_checkout_form', [__CLASS__, 'enforce_minimum_order']);
        add_action('woocommerce_checkout_process', [__CLASS__, 'validate_checkout']);
        add_action('woocommerce_before_order_notes', [__CLASS__, 'add_po_number_field']);
        add_action('woocommerce_checkout_update_order_meta', [__CLASS__, 'save_po_number']);
    }

    /**
     * Enforce minimum order before checkout
     */
    public static function enforce_minimum_order() {
        if (!is_user_logged_in()) {
            return;
        }

        if (!self::is_b2b_customer()) {
            return;
        }

        if (!WC()->cart) {
            return;
        }

        $minimum_order = (float) get_option('b2b_wc_minimum_order', 250);
        if ($minimum_order <= 0) {
            return;
        }

        $cart_total = WC()->cart->get_subtotal();
        if ($cart_total < $minimum_order) {
            wc_add_notice(
                sprintf(
                    __('Your wholesale order must meet a minimum subtotal of %s. Current subtotal: %s', 'b2b-wc-extension'),
                    wc_price($minimum_order),
                    wc_price($cart_total)
                ),
                'error'
            );
        }
    }

    /**
     * Validate checkout
     */
    public static function validate_checkout() {
        self::enforce_minimum_order();
    }

    /**
     * Add PO number field
     */
    public static function add_po_number_field() {
        if (!self::is_b2b_customer()) {
            return;
        }
        ?>
        <div class="b2b-checkout-fields">
            <h3><?php esc_html_e('B2B Order Information', 'b2b-wc-extension'); ?></h3>
            <?php
            woocommerce_form_field('b2b_po_number', [
                'type' => 'text',
                'label' => __('Purchase Order Number (Optional)', 'b2b-wc-extension'),
                'placeholder' => __('e.g., PO-2024-001', 'b2b-wc-extension'),
            ]);
            ?>
        </div>
        <?php
    }

    /**
     * Save PO number to order meta
     */
    public static function save_po_number($order_id) {
        if (!isset($_POST['b2b_po_number'])) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $po_number = sanitize_text_field(wp_unslash($_POST['b2b_po_number']));
        if ($po_number) {
            $order->update_meta_data('b2b_po_number', $po_number);
            $order->save();
        }
    }

    /**
     * Check if user is B2B customer
     */
    private static function is_b2b_customer() {
        $user = wp_get_current_user();
        return in_array('b2b_wholesale_customer', (array) $user->roles, true);
    }
}
