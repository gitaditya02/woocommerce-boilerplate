<?php
/**
 * Plugin Name: B2B WooCommerce Extension
 * Description: Production-ready B2B wholesale extension for WooCommerce.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: b2b-wc-extension
 * Domain Path: /languages
 * Requires: WooCommerce
 * Requires at least: 5.0
 * Tested up to: 6.5
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('B2B_WC_PATH', plugin_dir_path(__FILE__));
define('B2B_WC_URL', plugin_dir_url(__FILE__));
define('B2B_WC_VERSION', '1.0.0');

require_once B2B_WC_PATH . 'includes/class-custom-woocommerce.php';

function b2b_wc_init() {
    if (class_exists('WooCommerce')) {
        B2B_WooCommerce_Extension::init();
    } else {
        add_action('admin_notices', 'b2b_wc_missing_notice');
    }
}
add_action('plugins_loaded', 'b2b_wc_init');

function b2b_wc_missing_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php esc_html_e('B2B WooCommerce Extension requires WooCommerce to be installed and activated.', 'b2b-wc-extension'); ?></p>
    </div>
    <?php
}

register_activation_hook(__FILE__, 'b2b_wc_activate');
function b2b_wc_activate() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('B2B WooCommerce Extension requires WooCommerce to be installed and activated.', 'b2b-wc-extension'));
    }

    B2B_WooCommerce_Extension::register_wholesale_role();
    B2B_WooCommerce_Extension::set_default_options();
}
