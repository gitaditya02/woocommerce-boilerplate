<?php
/**
 * B2B WooCommerce Extension - Main Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_WooCommerce_Extension {
    private static $initialized = false;

    public static function init() {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;
        self::load_classes();
        self::register_wholesale_role();
        self::set_default_options();
        self::register_hooks();
    }

    private static function load_classes() {
        $includes = [
            'class-b2b-customer-manager.php',
            'class-b2b-pricing-engine.php',
            'class-b2b-admin-dashboard.php',
            'class-b2b-checkout.php',
            'class-b2b-email-service.php',
            'class-b2b-quantity-pricing.php',
            'class-b2b-importer.php',
            'class-b2b-tax-validation.php',
            'class-b2b-storefront.php',
            'class-b2b-company-pricing.php',
            'class-b2b-vat-validation.php',
            'class-b2b-approval-queue.php',
            'class-b2b-bulk-assignment.php',
            'class-b2b-audit-log.php',
            'class-b2b-email-templates.php',
        ];

        foreach ($includes as $file) {
            $path = B2B_WC_PATH . 'includes/' . $file;
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }

    private static function register_hooks() {
        add_action('init', [__CLASS__, 'load_textdomain']);
        add_action('woocommerce_register_form_start', [__CLASS__, 'render_registration_fields']);
        add_action('woocommerce_created_customer', [__CLASS__, 'save_customer_meta'], 10, 3);
        add_action('woocommerce_before_checkout_form', [__CLASS__, 'enforce_minimum_order']);
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'apply_wholesale_discount'], 20);
        add_action('woocommerce_before_single_product', [__CLASS__, 'restrict_catalog_access']);
        add_filter('woocommerce_is_purchasable', [__CLASS__, 'restrict_purchasable_products'], 10, 2);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);

        if (class_exists('B2B_Customer_Manager')) {
            B2B_Customer_Manager::init();
        }
        if (class_exists('B2B_Pricing_Engine')) {
            B2B_Pricing_Engine::init();
        }
        if (class_exists('B2B_Admin_Dashboard')) {
            B2B_Admin_Dashboard::init();
        }
        if (class_exists('B2B_Checkout')) {
            B2B_Checkout::init();
        }
        if (class_exists('B2B_Email_Service')) {
            B2B_Email_Service::init();
        }
        if (class_exists('B2B_Quantity_Pricing')) {
            B2B_Quantity_Pricing::init();
        }
        if (class_exists('B2B_Importer')) {
            B2B_Importer::init();
        }
        if (class_exists('B2B_VAT_Validation')) {
            B2B_VAT_Validation::init();
        }
        if (class_exists('B2B_Storefront')) {
            B2B_Storefront::init();
        }
        if (class_exists('B2B_Company_Pricing')) {
            B2B_Company_Pricing::init();
        }
        if (class_exists('B2B_Approval_Queue')) {
            B2B_Approval_Queue::init();
        }
        if (class_exists('B2B_Bulk_Assignment')) {
            B2B_Bulk_Assignment::init();
        }
        if (class_exists('B2B_Audit_Log')) {
            B2B_Audit_Log::init();
        }
        if (class_exists('B2B_Email_Templates')) {
            B2B_Email_Templates::init();
        }
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

    public static function render_registration_fields() {
        if (class_exists('B2B_Customer_Manager')) {
            B2B_Customer_Manager::render_registration_fields();
        }
    }

    public static function save_customer_meta($customer_id, $new_customer_data, $password_generated) {
        if (class_exists('B2B_Customer_Manager')) {
            B2B_Customer_Manager::process_registration($customer_id, $new_customer_data, $password_generated);
        }
    }

    public static function enforce_minimum_order() {
        if (class_exists('B2B_Checkout')) {
            B2B_Checkout::enforce_minimum_order();
        }
    }

    public static function apply_wholesale_discount($cart) {
        if (class_exists('B2B_Pricing_Engine')) {
            B2B_Pricing_Engine::apply_cart_discount($cart);
        }
    }

    public static function restrict_catalog_access() {
        if (!is_admin()) {
            $restricted = (int) get_option('b2b_wc_catalog_restricted', 1);
            if ($restricted && (!is_user_logged_in() || !self::is_wholesale_customer())) {
                wp_safe_redirect(wp_login_url(get_permalink()));
                exit;
            }
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
        if (is_woocommerce() || is_cart() || is_checkout()) {
            wp_enqueue_style('b2b-wc-extension-style', B2B_WC_URL . 'assets/css/style.css', [], B2B_WC_VERSION);
            wp_enqueue_script('b2b-wc-extension-script', B2B_WC_URL . 'assets/js/script.js', ['jquery'], B2B_WC_VERSION, true);
        }
    }

    public static function enqueue_admin_assets() {
        wp_enqueue_style('b2b-wc-admin-style', B2B_WC_URL . 'assets/css/admin-style.css', [], B2B_WC_VERSION);
    }

    public static function is_wholesale_customer() {
        if (!is_user_logged_in()) {
            return false;
        }
        $user = wp_get_current_user();
        return in_array('b2b_wholesale_customer', (array) $user->roles, true);
    }
}
