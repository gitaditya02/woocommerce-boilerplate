<?php
/**
 * B2B Approval Queue UI
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Approval_Queue {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_approval_queue']);
        add_action('admin_init', [__CLASS__, 'handle_bulk_actions']);
    }

    public static function register_approval_queue() {
        add_submenu_page(
            'woocommerce',
            __('B2B Approvals', 'b2b-wc-extension'),
            __('B2B Approvals', 'b2b-wc-extension'),
            'manage_woocommerce',
            'b2b-wc-approvals',
            [__CLASS__, 'render_approval_queue']
        );
    }

    public static function handle_bulk_actions() {
        if (!isset($_POST['b2b_bulk_action']) || !current_user_can('manage_woocommerce')) {
            return;
        }

        if (!isset($_POST['b2b_user_ids'])) {
            return;
        }

        $ids = array_map('intval', $_POST['b2b_user_ids']);
        $action = sanitize_text_field(wp_unslash($_POST['b2b_bulk_action']));

        foreach ($ids as $id) {
            $user = get_user_by('id', $id);
            if (!$user) {
                continue;
            }

            if ($action === 'approve') {
                update_user_meta($id, 'b2b_approval_status', 'approved');
                do_action('b2b_customer_approved', $id);
            } elseif ($action === 'reject') {
                update_user_meta($id, 'b2b_approval_status', 'rejected');
                do_action('b2b_customer_rejected', $id);
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=b2b-wc-approvals'));
        exit;
    }

    public static function render_approval_queue() {
        $users = get_users([
            'meta_key' => 'b2b_approval_status',
            'meta_value' => 'pending',
            'role' => 'b2b_wholesale_customer',
        ]);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('B2B Approval Queue', 'b2b-wc-extension'); ?></h1>

            <form method="post">
                <table class="widefat" role="presentation">
                    <thead>
                        <tr>
                            <th><input type="checkbox" name="b2b_select_all" id="b2b_select_all" /></th>
                            <th><?php esc_html_e('Customer', 'b2b-wc-extension'); ?></th>
                            <th><?php esc_html_e('Company', 'b2b-wc-extension'); ?></th>
                            <th><?php esc_html_e('Submitted', 'b2b-wc-extension'); ?></th>
                            <th><?php esc_html_e('Actions', 'b2b-wc-extension'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)) : ?>
                            <tr><td colspan="5"><?php esc_html_e('No pending B2B applications.', 'b2b-wc-extension'); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td><input type="checkbox" name="b2b_user_ids[]" value="<?php echo esc_attr($user->ID); ?>" class="b2b-user-check" /></td>
                                    <td><?php echo esc_html($user->display_name); ?><br><small><?php echo esc_html($user->user_email); ?></small></td>
                                    <td><?php echo esc_html(get_user_meta($user->ID, 'company_name', true) ?: '-'); ?></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($user->user_registered))); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=b2b-wc-approvals&approve=' . $user->ID), 'b2b_approve_' . $user->ID)); ?>" class="button button-primary"><?php esc_html_e('Approve', 'b2b-wc-extension'); ?></a>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=b2b-wc-approvals&reject=' . $user->ID), 'b2b_reject_' . $user->ID)); ?>" class="button button-secondary"><?php esc_html_e('Reject', 'b2b-wc-extension'); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div style="margin-top: 15px;">
                    <select name="b2b_bulk_action">
                        <option value="approve"><?php esc_html_e('Approve selected', 'b2b-wc-extension'); ?></option>
                        <option value="reject"><?php esc_html_e('Reject selected', 'b2b-wc-extension'); ?></option>
                    </select>
                    <?php submit_button(__('Apply', 'b2b-wc-extension')); ?>
                </div>
            </form>
        </div>
        <?php
    }
}
