<?php
/**
 * B2B Admin Dashboard
 * Provides admin interface for managing wholesale customers and tiers
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Admin_Dashboard {

    /**
     * Register hooks
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_init', [__CLASS__, 'handle_actions']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    /**
     * Register admin menu
     */
    public static function register_menu() {
        add_submenu_page(
            'woocommerce',
            __('B2B Settings', 'b2b-wc-extension'),
            __('B2B Settings', 'b2b-wc-extension'),
            'manage_woocommerce',
            'b2b-wc-settings',
            [__CLASS__, 'render_settings_page']
        );

        add_submenu_page(
            'woocommerce',
            __('B2B Customers', 'b2b-wc-extension'),
            __('B2B Customers', 'b2b-wc-extension'),
            'manage_woocommerce',
            'b2b-wc-customers',
            [__CLASS__, 'render_customers_page']
        );
    }

    /**
     * Register settings
     */
    public static function register_settings() {
        register_setting('b2b_wc_settings_group', 'b2b_wc_minimum_order', 'floatval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_catalog_restricted', 'intval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_require_approval', 'intval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_wholesale_discount', 'floatval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_silver_discount', 'floatval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_gold_discount', 'floatval');
        register_setting('b2b_wc_settings_group', 'b2b_wc_platinum_discount', 'floatval');
    }

    /**
     * Handle bulk actions
     */
    public static function handle_actions() {
        if (!isset($_REQUEST['action']) || !isset($_REQUEST['_wpnonce'])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'b2b_nonce')) {
            return;
        }

        $action = sanitize_text_field(wp_unslash($_REQUEST['action']));
        $user_id = isset($_REQUEST['user_id']) ? intval(wp_unslash($_REQUEST['user_id'])) : 0;

        if ($action === 'approve_b2b' && $user_id) {
            update_user_meta($user_id, 'b2b_approval_status', 'approved');
            wp_safe_redirect(admin_url('admin.php?page=b2b-wc-customers&approved=1'));
            exit;
        } elseif ($action === 'reject_b2b' && $user_id) {
            update_user_meta($user_id, 'b2b_approval_status', 'rejected');
            wp_safe_redirect(admin_url('admin.php?page=b2b-wc-customers&rejected=1'));
            exit;
        }
    }

    /**
     * Render settings page
     */
    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('B2B WooCommerce Settings', 'b2b-wc-extension'); ?></h1>

            <?php if (isset($_GET['approved'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Account approved successfully.', 'b2b-wc-extension'); ?></p></div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('b2b_wc_settings_group'); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="b2b_wc_minimum_order"><?php esc_html_e('Minimum Order Value', 'b2b-wc-extension'); ?></label>
                        </th>
                        <td>
                            <input type="number" step="0.01" min="0" name="b2b_wc_minimum_order" id="b2b_wc_minimum_order" value="<?php echo esc_attr(get_option('b2b_wc_minimum_order', 250)); ?>" />
                            <p class="description"><?php esc_html_e('Minimum subtotal before checkout is allowed.', 'b2b-wc-extension'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Restrict Catalog', 'b2b-wc-extension'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="b2b_wc_catalog_restricted" value="1" <?php checked(get_option('b2b_wc_catalog_restricted', 1), 1); ?> />
                                <?php esc_html_e('Only show products to wholesale customers.', 'b2b-wc-extension'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Require Approval', 'b2b-wc-extension'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="b2b_wc_require_approval" value="1" <?php checked(get_option('b2b_wc_require_approval', 1), 1); ?> />
                                <?php esc_html_e('New accounts must be approved before shopping.', 'b2b-wc-extension'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Pricing Tiers', 'b2b-wc-extension'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="b2b_wc_wholesale_discount"><?php esc_html_e('Standard Tier Discount (%)', 'b2b-wc-extension'); ?></label>
                        </th>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" name="b2b_wc_wholesale_discount" value="<?php echo esc_attr(get_option('b2b_wc_wholesale_discount', 10)); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="b2b_wc_silver_discount"><?php esc_html_e('Silver Tier Discount (%)', 'b2b-wc-extension'); ?></label>
                        </th>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" name="b2b_wc_silver_discount" value="<?php echo esc_attr(get_option('b2b_wc_silver_discount', 15)); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="b2b_wc_gold_discount"><?php esc_html_e('Gold Tier Discount (%)', 'b2b-wc-extension'); ?></label>
                        </th>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" name="b2b_wc_gold_discount" value="<?php echo esc_attr(get_option('b2b_wc_gold_discount', 20)); ?>" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="b2b_wc_platinum_discount"><?php esc_html_e('Platinum Tier Discount (%)', 'b2b-wc-extension'); ?></label>
                        </th>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" name="b2b_wc_platinum_discount" value="<?php echo esc_attr(get_option('b2b_wc_platinum_discount', 25)); ?>" />
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render customers page
     */
    public static function render_customers_page() {
        $users = get_users(['role' => 'b2b_wholesale_customer']);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('B2B Customers', 'b2b-wc-extension'); ?></h1>

            <?php if (isset($_GET['approved'])) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Account approved.', 'b2b-wc-extension'); ?></p></div>
            <?php endif; ?>
            <?php if (isset($_GET['rejected'])) : ?>
                <div class="notice notice-warning is-dismissible"><p><?php esc_html_e('Account rejected.', 'b2b-wc-extension'); ?></p></div>
            <?php endif; ?>

            <table class="widefat" role="presentation">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'b2b-wc-extension'); ?></th>
                        <th><?php esc_html_e('Company', 'b2b-wc-extension'); ?></th>
                        <th><?php esc_html_e('Email', 'b2b-wc-extension'); ?></th>
                        <th><?php esc_html_e('Tier', 'b2b-wc-extension'); ?></th>
                        <th><?php esc_html_e('Status', 'b2b-wc-extension'); ?></th>
                        <th><?php esc_html_e('Actions', 'b2b-wc-extension'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <?php
                        $company = get_user_meta($user->ID, 'company_name', true);
                        $tier = get_user_meta($user->ID, 'b2b_customer_tier', true);
                        $status = get_user_meta($user->ID, 'b2b_approval_status', true);
                        ?>
                        <tr>
                            <td><?php echo esc_html($user->display_name); ?></td>
                            <td><?php echo esc_html($company ?: '-'); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><?php echo esc_html(ucfirst($tier ?: 'standard')); ?></td>
                            <td>
                                <span class="b2b-status <?php echo esc_attr($status); ?>">
                                    <?php echo esc_html(ucfirst($status ?: 'pending')); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($status !== 'approved') : ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=b2b-wc-customers&action=approve_b2b&user_id=' . $user->ID), 'b2b_nonce')); ?>" class="button button-small button-primary"><?php esc_html_e('Approve', 'b2b-wc-extension'); ?></a>
                                <?php endif; ?>
                                <a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>" class="button button-small"><?php esc_html_e('Edit', 'b2b-wc-extension'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets() {
        wp_enqueue_style('b2b-wc-admin-style', B2B_WC_URL . 'assets/css/admin-style.css', [], B2B_WC_VERSION);
    }
}
