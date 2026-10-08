<?php
/**
 * B2B Bulk Assignment
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Bulk_Assignment {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_bulk_assignment_page']);
        add_action('admin_post_b2b_bulk_assign', [__CLASS__, 'handle_assignment']);
    }

    public static function register_bulk_assignment_page() {
        add_submenu_page(
            'woocommerce',
            __('B2B Bulk Assign', 'b2b-wc-extension'),
            __('B2B Bulk Assign', 'b2b-wc-extension'),
            'manage_woocommerce',
            'b2b-wc-bulk-assign',
            [__CLASS__, 'render_page']
        );
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Bulk Assign B2B Tiers', 'b2b-wc-extension'); ?></h1>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="b2b_bulk_assign" />
                <?php wp_nonce_field('b2b_bulk_assign', 'b2b_bulk_nonce'); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="b2b_bulk_role"><?php esc_html_e('Assign tier to all selected wholesale users', 'b2b-wc-extension'); ?></label></th>
                        <td>
                            <select name="b2b_bulk_role" id="b2b_bulk_role">
                                <option value="standard"><?php esc_html_e('Standard', 'b2b-wc-extension'); ?></option>
                                <option value="silver"><?php esc_html_e('Silver', 'b2b-wc-extension'); ?></option>
                                <option value="gold"><?php esc_html_e('Gold', 'b2b-wc-extension'); ?></option>
                                <option value="platinum"><?php esc_html_e('Platinum', 'b2b-wc-extension'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <input type="submit" class="button button-primary" value="<?php esc_html_e('Assign Tier', 'b2b-wc-extension'); ?>" />
            </form>
        </div>
        <?php
    }

    public static function handle_assignment() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You are not allowed to assign B2B tiers.', 'b2b-wc-extension'));
        }

        if (!isset($_POST['b2b_bulk_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['b2b_bulk_nonce'])), 'b2b_bulk_assign')) {
            wp_die(__('Security check failed.', 'b2b-wc-extension'));
        }

        $tier = sanitize_text_field(wp_unslash($_POST['b2b_bulk_role'] ?? 'standard'));
        $users = get_users(['role' => 'b2b_wholesale_customer']);

        foreach ($users as $user) {
            update_user_meta($user->ID, 'b2b_customer_tier', $tier);
        }

        wp_safe_redirect(admin_url('admin.php?page=b2b-wc-bulk-assign&updated=1'));
        exit;
    }
}
