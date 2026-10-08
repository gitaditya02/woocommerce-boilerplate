<?php
/**
 * B2B Email Templates
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Email_Templates {
    public static function init() {
        add_filter('wp_mail_from', [__CLASS__, 'set_from_email']);
        add_filter('wp_mail_from_name', [__CLASS__, 'set_from_name']);
    }

    public static function set_from_email() {
        return get_option('admin_email');
    }

    public static function set_from_name() {
        return get_bloginfo('name');
    }
}
