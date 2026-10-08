<?php
/**
 * B2B Audit Log
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Audit_Log {
    public static function init() {
        add_action('b2b_customer_approved', [__CLASS__, 'log_event'], 10, 1);
        add_action('b2b_customer_rejected', [__CLASS__, 'log_event'], 10, 1);
        add_action('b2b_customer_tier_changed', [__CLASS__, 'log_event'], 10, 1);
    }

    public static function log_event($user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }

        $log = get_option('b2b_audit_log', []);
        $log[] = [
            'timestamp' => current_time('mysql'),
            'user_id' => $user_id,
            'user_email' => $user->user_email,
            'event' => current_filter(),
            'company' => get_user_meta($user_id, 'company_name', true),
        ];

        update_option('b2b_audit_log', array_slice($log, -200));
    }
}
