<?php
/**
 * B2B Email Notification Service
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Email_Service {
    public static function init() {
        add_action('b2b_customer_approved', [__CLASS__, 'send_approval_email'], 10, 1);
        add_action('b2b_customer_rejected', [__CLASS__, 'send_rejection_email'], 10, 1);
    }

    public static function send_approval_email($user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }

        $to = $user->user_email;
        $subject = __('Your B2B account has been approved', 'b2b-wc-extension');
        $message = sprintf(
            "Hi %s,\n\nYour wholesale account has been approved. You can now access the B2B dashboard and shop with your trade pricing.\n\nRegards,\n%s",
            $user->display_name,
            get_bloginfo('name')
        );

        wp_mail($to, $subject, $message);
    }

    public static function send_rejection_email($user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }

        $to = $user->user_email;
        $subject = __('Your B2B account has been reviewed', 'b2b-wc-extension');
        $message = sprintf(
            "Hi %s,\n\nYour B2B account application has been reviewed. Additional verification may be required before your account can be approved.\n\nRegards,\n%s",
            $user->display_name,
            get_bloginfo('name')
        );

        wp_mail($to, $subject, $message);
    }
}
