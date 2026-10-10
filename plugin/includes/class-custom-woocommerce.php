<?php
/**
 * B2B WooCommerce Extension - Main Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_WooCommerce_Extension {
    public static function init() {
        self::register_wholesale_role();
        self::set_default_options();

        add_action('init', [__CLASS__, 'load_textdomain']);
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        // Core B2B features
        add_action('woocommerce_register_form_start', [__CLASS__, 'render_registration_fields']);
        add_action('woocommerce_created_customer', [__CLASS__, 'save_customer_meta'], 10, 3);
        add_action('woocommerce_before_checkout_form', [__CLASS__, 'enforce_minimum_order']);
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'apply_wholesale_discount'], 20);
        add_action('woocommerce_before_single_product', [__CLASS__, 'restrict_catalog_access']);
        add_filter('woocommerce_is_purchasable', [__CLASS__, 'restrict_purchasable_products'], 10, 2);

        B2B_Main::init();
    }

    public static function register_wholesale_role() {
        if (!get_role('b2b_wholesale_customer')) {
            add_role(
                'b2b_wholesale_customer',
                __('B2B Wholesale Customer', 'b2b-wc-extension'),
                [
                    'read' => true,
                    'edit_posts' => false,
                    'delete_posts' => false,
                    'publish_posts' => false,
                    'upload_files' => false,
                ]
            );
        }
    }

    public static function set_default_options() {
        add_option('b2b_wc_minimum_order', 250);
        add_option('b2b_wc_catalog_restricted', 1);
        add_option('b2b_wc_require_approval', 1);
        add_option('b2b_wc_wholesale_discount', 10);
        add_option('b2b_wc_silver_discount', 15);
        add_option('b2b_wc_gold_discount', 20);
        add_option('b2b_wc_platinum_discount', 25);
    }

    public static function load_textdomain() {
        load_plugin_textdomain('b2b-wc-extension', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public static function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('B2B Settings', 'b2b-wc-extension'),
            __('B2B Settings', 'b2b-wc-extension'),
            'manage_woocommerce',
            'b2b-wc-settings',
            [__CLASS__, 'render_admin_page']
        );
    }

    public static function register_settings() {
        register_setting('b2b_wc_settings_group', 'b2b_wc_minimum_order', 'floatval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_catalog_restricted', 'intval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_require_approval', 'intval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_wholesale_discount', 'floatval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_silver_discount', 'floatval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_gold_discount', 'floatval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_platinum_discount', 'floatval');
    }

    public static function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('B2B WooCommerce Settings', 'b2b-wc-extension'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('b2b_wc_settings_group'); ?>
                <?php do_settings_sections('b2b_wc_settings_group'); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="b2b_wc_minimum_order"><?php esc_html_e('Minimum order value', 'b2b-wc-extension'); ?></label></th>
                        <td>
                            <input type="number" step="0.01" min="0" name="b2b_wc_minimum_order" id="b2b_wc_minimum_order" value="<?php echo esc_attr(get_option('b2b_wc_minimum_order', 250)); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Restrict catalog', 'b2b-wc-extension'); ?></th>
                        <td>
                            <label><input type="checkbox" name="b2b_wc_catalog_restricted" value="1" <?php checked(get_option('b2b_wc_catalog_restricted', 1), 1); ?> /> <?php esc_html_e('Only wholesale customers may access the catalog.', 'b2b-wc-extension'); ?></label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Require approval', 'b2b-wc-extension'); ?></th>
                        <td>
                            <label><input type="checkbox" name="b2b_wc_require_approval" value="1" <?php checked(get_option('b2b_wc_require_approval', 1), 1); ?> /> <?php esc_html_e('Approve new B2B accounts manually.', 'b2b-wc-extension'); ?></label>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Wholesale Pricing Tiers', 'b2b-wc-extension'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="b2b_wc_wholesale_discount"><?php esc_html_e('Standard discount (%)', 'b2b-wc-extension'); ?></label></th>
                        <td><input type="number" step="0.01" min="0" max="100" name="b2b_wc_wholesale_discount" value="<?php echo esc_attr(get_option('b2b_wc_wholesale_discount', 10)); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="b2b_wc_silver_discount"><?php esc_html_e('Silver discount (%)', 'b2b-wc-extension'); ?></label></th>
                        <td><input type="number" step="0.01" min="0" max="100" name="b2b_wc_silver_discount" value="<?php echo esc_attr(get_option('b2b_wc_silver_discount', 15)); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="b2b_wc_gold_discount"><?php esc_html_e('Gold discount (%)', 'b2b-wc-extension'); ?></label></th>
                        <td><input type="number" step="0.01" min="0" max="100" name="b2b_wc_gold_discount" value="<?php echo esc_attr(get_option('b2b_wc_gold_discount', 20)); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="b2b_wc_platinum_discount"><?php esc_html_e('Platinum discount (%)', 'b2b-wc-extension'); ?></label></th>
                        <td><input type="number" step="0.01" min="0" max="100" name="b2b_wc_platinum_discount" value="<?php echo esc_attr(get_option('b2b_wc_platinum_discount', 25)); ?>" /></td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function render_registration_fields() {
        ?>
        <p class="form-row form-row-wide">
            <label for="b2b_company_name"><?php esc_html_e('Company Name', 'b2b-wc-extension'); ?> <span class="required">*</span></label>
            <input type="text" class="input-text" name="b2b_company_name" id="b2b_company_name" value="<?php echo esc_attr($_POST['b2b_company_name'] ?? ''); ?>" required />
        </p>

        <p class="form-row form-row-wide">
            <label for="b2b_company_address"><?php esc_html_e('Company Address', 'b2b-wc-extension'); ?></label>
            <input type="text" class="input-text" name="b2b_company_address" id="b2b_company_address" value="<?php echo esc_attr($_POST['b2b_company_address'] ?? ''); ?>" />
        </p>

        <p class="form-row form-row-wide">
            <label for="b2b_vat_number"><?php esc_html_e('VAT Number', 'b2b-wc-extension'); ?></label>
            <input type="text" class="input-text" name="b2b_vat_number" id="b2b_vat_number" value="<?php echo esc_attr($_POST['b2b_vat_number'] ?? ''); ?>" />
        </p>
        <?php
    }

    public static function save_customer_meta($customer_id, $new_customer_data, $password_generated) {
        if (!empty($_POST['b2b_company_name'])) {
            update_user_meta($customer_id, 'billing_company', sanitize_text_field(wp_unslash($_POST['b2b_company_name'])));
            update_user_meta($customer_id, 'company_name', sanitize_text_field(wp_unslash($_POST['b2b_company_name'])));
        }

        if (!empty($_POST['b2b_company_address'])) {
            update_user_meta($customer_id, 'company_address', sanitize_text_field(wp_unslash($_POST['b2b_company_address'])));
        }

        if (!empty($_POST['b2b_vat_number'])) {
            update_user_meta($customer_id, 'billing_vat_number', sanitize_text_field(wp_unslash($_POST['b2b_vat_number'])));
            update_user_meta($customer_id, 'vat_number', sanitize_text_field(wp_unslash($_POST['b2b_vat_number'])));
        }

        $user = get_user_by('id', $customer_id);
        if ($user && !in_array('b2b_wholesale_customer', (array) $user->roles, true)) {
            $user->set_role('b2b_wholesale_customer');
        }

        $require_approval = (int) get_option('b2b_wc_require_approval', 1);
        update_user_meta($customer_id, 'b2b_approval_status', $require_approval ? 'pending' : 'approved');
        update_user_meta($customer_id, 'b2b_customer_tier', 'standard');
    }

    public static function enforce_minimum_order() {
        if (!is_user_logged_in()) {
            return;
        }

        if (!is_admin() && !self::is_wholesale_customer()) {
            return;
        }

        if (!WC()->cart) {
            return;
        }

        $minimum_order = (float) get_option('b2b_wc_minimum_order', 250);
        if ($minimum_order <= 0) {
            return;
        }

        $subtotal = WC()->cart->get_subtotal();
        if ($subtotal < $minimum_order) {
            wc_add_notice(sprintf(__('Your wholesale subtotal must be at least %s before checkout.', 'b2b-wc-extension'), wc_price($minimum_order)), 'error');
        }
    }

    public static function apply_wholesale_discount($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (!is_user_logged_in() || !self::is_wholesale_customer()) {
            return;
        }

        if (!class_exists('B2B_Customer_Manager')) {
            return;
        }

        $tier = get_user_meta(get_current_user_id(), 'b2b_customer_tier', true);
        $discount = B2B_Customer_Manager::get_tier_discount($tier);

        if ($discount <= 0) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if ($product && $product->exists()) {
                $price = (float) $product->get_price();
                $product->set_price($price * (1 - ($discount / 100)));
            }
        }
    }

    public static function restrict_catalog_access() {
        if (is_admin()) {
            return;
        }

        $restricted = (int) get_option('b2b_wc_catalog_restricted', 1);
        if (!$restricted) {
            return;
        }

        if (!is_user_logged_in() || !self::is_wholesale_customer()) {
            wp_safe_redirect(wp_login_url(get_permalink()));
            exit;
        }
    }

    public static function restrict_purchasable_products($is_purchasable, $product) {
        $restricted = (int) get_option('b2b_wc_catalog_restricted', 1);
        if (!$restricted) {
            return $is_purchasable;
        }

        if (!is_user_logged_in() || !self::is_wholesale_customer()) {
            return false;
        }

        return $is_purchasable;
    }

    public static function enqueue_assets() {
        wp_enqueue_style('b2b-wc-extension-style', B2B_WC_URL . 'assets/css/style.css', [], B2B_WC_VERSION);
        wp_enqueue_style('b2b-wc-extension-admin-style', B2B_WC_URL . 'assets/css/admin-style.css', [], B2B_WC_VERSION);
        wp_enqueue_script('b2b-wc-extension-script', B2B_WC_URL . 'assets/js/script.js', ['jquery'], B2B_WC_VERSION, true);
    }

    public static function is_wholesale_customer() {
        if (!is_user_logged_in()) {
            return false;
        }
        $user = wp_get_current_user();
        return in_array('b2b_wholesale_customer', (array) $user->roles, true);
    }
}

class Custom_WooCommerce extends B2B_WooCommerce_Extension {}
