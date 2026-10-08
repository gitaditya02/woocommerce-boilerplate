<?php
/**
 * Main WooCommerce Custom Plugin Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Custom_WooCommerce {

    /**
     * Initialize the plugin
     */
    public static function init() {
        add_action('woocommerce_init', [__CLASS__, 'setup']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        add_action('init', [__CLASS__, 'load_textdomain']);
    }

    /**
     * Setup WooCommerce hooks
     */
    public static function setup() {
        // Add your custom hooks and filters here
        add_action('woocommerce_before_main_content', [__CLASS__, 'display_custom_message']);
        
        // Example: Modify product loop columns
        add_filter('woocommerce_product_loop_columns', [__CLASS__, 'products_per_row']);
        
        // Example: Custom product actions
        add_action('woocommerce_single_product_summary', [__CLASS__, 'custom_product_info'], 25);
    }

    /**
     * Enqueue frontend assets
     */
    public static function enqueue_assets() {
        if (is_woocommerce() || is_cart() || is_checkout()) {
            wp_enqueue_style(
                'wc-custom-style',
                WC_CUSTOM_PLUGIN_URL . 'assets/css/style.css',
                [],
                WC_CUSTOM_PLUGIN_VERSION
            );

            wp_enqueue_script(
                'wc-custom-script',
                WC_CUSTOM_PLUGIN_URL . 'assets/js/script.js',
                ['jquery', 'wc-add-to-cart'],
                WC_CUSTOM_PLUGIN_VERSION,
                true
            );

            wp_localize_script('wc-custom-script', 'wcCustom', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc-custom-nonce'),
            ]);
        }
    }

    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets() {
        wp_enqueue_style(
            'wc-custom-admin-style',
            WC_CUSTOM_PLUGIN_URL . 'assets/css/admin-style.css',
            [],
            WC_CUSTOM_PLUGIN_VERSION
        );
    }

    /**
     * Load text domain for translations
     */
    public static function load_textdomain() {
        load_plugin_textdomain(
            'wc-custom-plugin',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Display custom message before main content
     */
    public static function display_custom_message() {
        echo '<div class="wc-custom-notice notice-info">';
        echo '<p>' . esc_html__('Welcome to WooCommerce Custom Plugin!', 'wc-custom-plugin') . '</p>';
        echo '</div>';
    }

    /**
     * Set number of products per row
     *
     * @param int $cols Number of columns
     * @return int
     */
    public static function products_per_row($cols) {
        return 3;
    }

    /**
     * Add custom product information
     */
    public static function custom_product_info() {
        global $product;
        echo '<div class="custom-product-info">';
        echo '<p>' . esc_html__('This is a custom product section.', 'wc-custom-plugin') . '</p>';
        echo '</div>';
    }
}
