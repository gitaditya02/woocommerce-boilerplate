<?php
/**
 * Plugin Name: WooCommerce Custom Plugin
 * Description: Boilerplate for WooCommerce extension development
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: wc-custom-plugin
 * Domain Path: /languages
 * Requires: WooCommerce
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Define plugin constants
 */
define('WC_CUSTOM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WC_CUSTOM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_CUSTOM_PLUGIN_VERSION', '1.0.0');

/**
 * Include the main plugin class
 */
require_once WC_CUSTOM_PLUGIN_PATH . 'includes/class-custom-woocommerce.php';

/**
 * Initialize the plugin
 */
function wc_custom_plugin_init() {
    if (class_exists('WooCommerce')) {
        Custom_WooCommerce::init();
    } else {
        add_action('admin_notices', 'wc_custom_plugin_missing_notice');
    }
}
add_action('plugins_loaded', 'wc_custom_plugin_init');

/**
 * Display admin notice if WooCommerce is not active
 */
function wc_custom_plugin_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e('WooCommerce Custom Plugin requires WooCommerce to be installed and activated.', 'wc-custom-plugin'); ?></p>
    </div>
    <?php
}

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('WooCommerce Custom Plugin requires WooCommerce to be installed and activated.', 'wc-custom-plugin'));
    }
});
