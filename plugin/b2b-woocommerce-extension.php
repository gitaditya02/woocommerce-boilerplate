<?php
/**
 * Plugin Name: B2B WooCommerce Extension
 * Description: Production-ready B2B wholesale extension for WooCommerce with customer tiers, approval workflows, and custom pricing.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: b2b-wc-extension
 * Domain Path: /languages
 * Requires: WordPress 5.0
 * Requires PHP: 7.2
 * Requires Plugins: woocommerce
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('B2B_WC_VERSION')) {
    define('B2B_WC_VERSION', '1.0.0');
    define('B2B_WC_PATH', plugin_dir_path(__FILE__));
    define('B2B_WC_URL', plugin_dir_url(__FILE__));
}

// Check WooCommerce is active
if (!is_woocommerce_active()) {
    add_action('admin_notices', 'b2b_wc_admin_notice');
    return;
}

function is_woocommerce_active() {
    return class_exists('WooCommerce') || class_exists('WC_Load');
}

function b2b_wc_admin_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php esc_html_e('B2B WooCommerce Extension requires WooCommerce to be installed and activated.', 'b2b-wc-extension'); ?></p>
    </div>
    <?php
}

// Load all B2B classes
add_action('plugins_loaded', 'b2b_wc_load_classes', 11);
function b2b_wc_load_classes() {
    if (!is_woocommerce_active()) {
        return;
    }

    require_once B2B_WC_PATH . 'includes/class-b2b-woocommerce-extension.php';
    B2B_WooCommerce_Extension::init();
}

// Activation hook
register_activation_hook(__FILE__, 'b2b_wc_activate');
function b2b_wc_activate() {
    if (!is_woocommerce_active()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('B2B WooCommerce Extension requires WooCommerce 5.0 or higher to be installed and activated.', 'b2b-wc-extension'));
    }
}
