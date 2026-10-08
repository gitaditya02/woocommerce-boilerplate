<?php
/**
 * WooCommerce Boilerplate Theme Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Set up theme support
 */
function wc_boilerplate_setup() {
    // Add theme support for WooCommerce
    add_theme_support('woocommerce');
    
    // Add support for WooCommerce product gallery features
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('wc-product-gallery-zoom');
    
    // Add theme support for post thumbnails
    add_theme_support('post-thumbnails');
    
    // Add menu support
    add_theme_support('menus');
    register_nav_menu('primary', esc_html__('Primary Menu', 'wc-boilerplate-theme'));
    
    // Add text domain
    load_theme_textdomain('wc-boilerplate-theme', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'wc_boilerplate_setup');

/**
 * Enqueue theme styles and scripts
 */
function wc_boilerplate_enqueue_assets() {
    // Enqueue main stylesheet
    wp_enqueue_style(
        'wc-boilerplate-style',
        get_stylesheet_uri(),
        [],
        filemtime(get_template_directory() . '/style.css')
    );
    
    // Enqueue main script
    wp_enqueue_script(
        'wc-boilerplate-script',
        get_template_directory_uri() . '/assets/js/theme.js',
        ['jquery'],
        filemtime(get_template_directory() . '/assets/js/theme.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'wc_boilerplate_enqueue_assets');

/**
 * Customize WooCommerce product columns
 */
function wc_boilerplate_product_columns($columns) {
    return 3; // Display 3 products per row
}
add_filter('woocommerce_product_loop_columns', 'wc_boilerplate_product_columns');

/**
 * Customize number of related products shown
 */
function wc_boilerplate_related_products_args($args) {
    $args['posts_per_page'] = 4; // Show 4 related products
    $args['columns'] = 2;
    return $args;
}
add_filter('woocommerce_output_related_products_args', 'wc_boilerplate_related_products_args');

/**
 * Remove default WooCommerce styles if desired
 * Uncomment to disable default WooCommerce styling
 */
/*
function wc_boilerplate_dequeue_styles() {
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
}
add_action('wp_enqueue_scripts', 'wc_boilerplate_dequeue_styles', 99);
*/
