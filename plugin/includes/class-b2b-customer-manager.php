<?php
/**
 * B2B Customer Manager
 * Handles wholesale customer registration, approval, and tier management
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Customer_Manager {

    /**
     * Register hooks
     */
    public static function init() {
        add_action('woocommerce_register_form_start', [__CLASS__, 'render_registration_fields']);
        add_action('woocommerce_created_customer', [__CLASS__, 'process_registration'], 10, 3);
        add_action('woocommerce_account_menu_items', [__CLASS__, 'add_account_menu_items']);
        add_action('woocommerce_account_b2b-dashboard_endpoint', [__CLASS__, 'render_b2b_dashboard']);
    }

    /**
     * Render B2B registration fields
     */
    public static function render_registration_fields() {
        ?>
        <div class="b2b-registration-section">
            <h3><?php esc_html_e('B2B Account Information', 'b2b-wc-extension'); ?></h3>

            <p class="form-row form-row-wide">
                <label for="b2b_company_name"><?php esc_html_e('Company Name', 'b2b-wc-extension'); ?> <span class="required">*</span></label>
                <input type="text" class="input-text" name="b2b_company_name" id="b2b_company_name" required value="<?php echo esc_attr($_POST['b2b_company_name'] ?? ''); ?>" />
            </p>

            <p class="form-row form-row-wide">
                <label for="b2b_vat_number"><?php esc_html_e('VAT/Tax ID Number', 'b2b-wc-extension'); ?></label>
                <input type="text" class="input-text" name="b2b_vat_number" id="b2b_vat_number" value="<?php echo esc_attr($_POST['b2b_vat_number'] ?? ''); ?>" />
            </p>

            <p class="form-row form-row-wide">
                <label for="b2b_company_address"><?php esc_html_e('Company Address', 'b2b-wc-extension'); ?></label>
                <input type="text" class="input-text" name="b2b_company_address" id="b2b_company_address" value="<?php echo esc_attr($_POST['b2b_company_address'] ?? ''); ?>" />
            </p>

            <p class="form-row form-row-wide">
                <label for="b2b_business_type"><?php esc_html_e('Business Type', 'b2b-wc-extension'); ?></label>
                <select class="input-select" name="b2b_business_type" id="b2b_business_type">
                    <option value=""><?php esc_html_e('Select...', 'b2b-wc-extension'); ?></option>
                    <option value="retail" <?php selected($_POST['b2b_business_type'] ?? '', 'retail'); ?>><?php esc_html_e('Retail', 'b2b-wc-extension'); ?></option>
                    <option value="wholesale" <?php selected($_POST['b2b_business_type'] ?? '', 'wholesale'); ?>><?php esc_html_e('Wholesale', 'b2b-wc-extension'); ?></option>
                    <option value="distributor" <?php selected($_POST['b2b_business_type'] ?? '', 'distributor'); ?>><?php esc_html_e('Distributor', 'b2b-wc-extension'); ?></option>
                    <option value="manufacturer" <?php selected($_POST['b2b_business_type'] ?? '', 'manufacturer'); ?>><?php esc_html_e('Manufacturer', 'b2b-wc-extension'); ?></option>
                </select>
            </p>
        </div>
        <?php
    }

    /**
     * Process customer registration
     */
    public static function process_registration($customer_id, $new_customer_data, $password_generated) {
        // Save company information
        if (!empty($_POST['b2b_company_name'])) {
            update_user_meta($customer_id, 'billing_company', sanitize_text_field(wp_unslash($_POST['b2b_company_name'])));
            update_user_meta($customer_id, 'company_name', sanitize_text_field(wp_unslash($_POST['b2b_company_name'])));
        }

        if (!empty($_POST['b2b_vat_number'])) {
            update_user_meta($customer_id, 'billing_vat_number', sanitize_text_field(wp_unslash($_POST['b2b_vat_number'])));
        }

        if (!empty($_POST['b2b_company_address'])) {
            update_user_meta($customer_id, 'company_address', sanitize_text_field(wp_unslash($_POST['b2b_company_address'])));
        }

        if (!empty($_POST['b2b_business_type'])) {
            update_user_meta($customer_id, 'business_type', sanitize_text_field(wp_unslash($_POST['b2b_business_type'])));
        }

        // Assign B2B role and set approval status
        $user = get_user_by('id', $customer_id);
        if ($user) {
            $user->set_role('b2b_wholesale_customer');
            $require_approval = (int) get_option('b2b_wc_require_approval', 1);
            if ($require_approval) {
                update_user_meta($customer_id, 'b2b_approval_status', 'pending');
            } else {
                update_user_meta($customer_id, 'b2b_approval_status', 'approved');
            }
        }

        // Set default customer tier
        update_user_meta($customer_id, 'b2b_customer_tier', 'standard');
    }

    /**
     * Add B2B dashboard menu item
     */
    public static function add_account_menu_items($items) {
        if (self::is_b2b_customer()) {
            $items['b2b-dashboard'] = __('B2B Dashboard', 'b2b-wc-extension');
        }
        return $items;
    }

    /**
     * Render B2B dashboard
     */
    public static function render_b2b_dashboard() {
        $user_id = get_current_user_id();
        ?>
        <div class="b2b-dashboard">
            <h2><?php esc_html_e('B2B Dashboard', 'b2b-wc-extension'); ?></h2>

            <div class="b2b-dashboard-sections">
                <?php self::render_account_status($user_id); ?>
                <?php self::render_company_info($user_id); ?>
                <?php self::render_tier_info($user_id); ?>
                <?php self::render_pricing_info($user_id); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render account status section
     */
    private static function render_account_status($user_id) {
        $approval_status = get_user_meta($user_id, 'b2b_approval_status', true);
        $status_class = $approval_status === 'approved' ? 'approved' : 'pending';
        $status_label = $approval_status === 'approved' ? __('Approved', 'b2b-wc-extension') : __('Pending Approval', 'b2b-wc-extension');
        ?>
        <div class="b2b-dashboard-section">
            <h3><?php esc_html_e('Account Status', 'b2b-wc-extension'); ?></h3>
            <p><span class="b2b-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></p>
        </div>
        <?php
    }

    /**
     * Render company info section
     */
    private static function render_company_info($user_id) {
        $company_name = get_user_meta($user_id, 'company_name', true);
        $vat_number = get_user_meta($user_id, 'billing_vat_number', true);
        $company_address = get_user_meta($user_id, 'company_address', true);
        $business_type = get_user_meta($user_id, 'business_type', true);
        ?>
        <div class="b2b-dashboard-section">
            <h3><?php esc_html_e('Company Information', 'b2b-wc-extension'); ?></h3>
            <ul>
                <li><strong><?php esc_html_e('Company:', 'b2b-wc-extension'); ?></strong> <?php echo esc_html($company_name); ?></li>
                <li><strong><?php esc_html_e('VAT ID:', 'b2b-wc-extension'); ?></strong> <?php echo esc_html($vat_number); ?></li>
                <li><strong><?php esc_html_e('Address:', 'b2b-wc-extension'); ?></strong> <?php echo esc_html($company_address); ?></li>
                <li><strong><?php esc_html_e('Business Type:', 'b2b-wc-extension'); ?></strong> <?php echo esc_html(ucfirst($business_type)); ?></li>
            </ul>
        </div>
        <?php
    }

    /**
     * Render tier info section
     */
    private static function render_tier_info($user_id) {
        $tier = get_user_meta($user_id, 'b2b_customer_tier', true);
        $tier_label = self::get_tier_label($tier);
        ?>
        <div class="b2b-dashboard-section">
            <h3><?php esc_html_e('Customer Tier', 'b2b-wc-extension'); ?></h3>
            <p><?php echo esc_html($tier_label); ?></p>
        </div>
        <?php
    }

    /**
     * Render pricing info section
     */
    private static function render_pricing_info($user_id) {
        $tier = get_user_meta($user_id, 'b2b_customer_tier', true);
        $discount = self::get_tier_discount($tier);
        ?>
        <div class="b2b-dashboard-section">
            <h3><?php esc_html_e('Your Pricing', 'b2b-wc-extension'); ?></h3>
            <p><?php printf(esc_html__('Discount: %d%%', 'b2b-wc-extension'), intval($discount)); ?></p>
        </div>
        <?php
    }

    /**
     * Check if user is B2B customer
     */
    public static function is_b2b_customer($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) {
            return false;
        }
        $user = get_user_by('id', $user_id);
        return $user && in_array('b2b_wholesale_customer', (array) $user->roles, true);
    }

    /**
     * Get tier label
     */
    public static function get_tier_label($tier) {
        $tiers = [
            'standard' => __('Standard', 'b2b-wc-extension'),
            'silver' => __('Silver', 'b2b-wc-extension'),
            'gold' => __('Gold', 'b2b-wc-extension'),
            'platinum' => __('Platinum', 'b2b-wc-extension'),
        ];
        return isset($tiers[$tier]) ? $tiers[$tier] : __('Unknown', 'b2b-wc-extension');
    }

    /**
     * Get tier discount
     */
    public static function get_tier_discount($tier) {
        $tiers = [
            'standard' => (float) get_option('b2b_wc_wholesale_discount', 10),
            'silver' => (float) get_option('b2b_wc_silver_discount', 15),
            'gold' => (float) get_option('b2b_wc_gold_discount', 20),
            'platinum' => (float) get_option('b2b_wc_platinum_discount', 25),
        ];
        return isset($tiers[$tier]) ? $tiers[$tier] : (float) get_option('b2b_wc_wholesale_discount', 10);
    }
}
