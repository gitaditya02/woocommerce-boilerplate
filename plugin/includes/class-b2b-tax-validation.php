<?php
/**
 * B2B Customer Importer
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Importer {
    public static function init() {
        add_action('admin_post_b2b_import_customers', [__CLASS__, 'handle_import']);
    }

    public static function render_import_form() {
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Import B2B Customers', 'b2b-wc-extension'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="b2b_import_customers" />
                <?php wp_nonce_field('b2b_import_customers', 'b2b_import_nonce'); ?>
                <p>
                    <input type="file" name="b2b_csv" accept=".csv" required />
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Import Customers', 'b2b-wc-extension'); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    public static function handle_import() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You are not allowed to import customers.', 'b2b-wc-extension'));
        }

        if (!isset($_POST['b2b_import_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['b2b_import_nonce'])), 'b2b_import_customers')) {
            wp_die(__('Security check failed.', 'b2b-wc-extension'));
        }

        if (!isset($_FILES['b2b_csv']) || $_FILES['b2b_csv']['error'] !== UPLOAD_ERR_OK) {
            wp_die(__('Please upload a valid CSV file.', 'b2b-wc-extension'));
        }

        $file = $_FILES['b2b_csv']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            wp_die(__('Unable to read CSV file.', 'b2b-wc-extension'));
        }

        $header = fgetcsv($handle);
        $rows = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if (!$data || empty($data['email'])) {
                continue;
            }

            $user_email = sanitize_email($data['email']);
            $user = get_user_by('email', $user_email);

            if (!$user) {
                $user_id = wp_create_user($data['first_name'] ?? $user_email, wp_generate_password(), $user_email);
                if (is_wp_error($user_id)) {
                    continue;
                }
                $user = get_user_by('id', $user_id);
            }

            if ($user) {
                $user->set_role('b2b_wholesale_customer');
                update_user_meta($user->ID, 'company_name', sanitize_text_field($data['company_name'] ?? ''));
                update_user_meta($user->ID, 'b2b_customer_tier', sanitize_text_field($data['tier'] ?? 'standard'));
                update_user_meta($user->ID, 'b2b_approval_status', 'approved');
                $rows++;
            }
        }

        fclose($handle);

        wp_safe_redirect(add_query_arg(['imported' => $rows], admin_url('admin.php?page=b2b-wc-customers')));
        exit;
    }
}
