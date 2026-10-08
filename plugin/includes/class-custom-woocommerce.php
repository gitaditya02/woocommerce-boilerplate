<?php
/**
 * Main B2B plugin orchestrator
 */

if (!defined('ABSPATH')) {
    exit;
}

class B2B_Main {
    public static function init() {
        require_once B2B_WC_PATH . 'includes/class-b2b-customer-manager.php';
        require_once B2B_WC_PATH . 'includes/class-b2b-pricing-engine.php';
        require_once B2B_WC_PATH . 'includes/class-b2b-admin-dashboard.php';
        require_once B2B_WC_PATH . 'includes/class-b2b-checkout.php';
        require_once B2B_WC_PATH . 'includes/class-b2b-email-service.php';
        require_once B2B_WC_PATH . 'includes/class-b2b-quantity-pricing.php';
        require_once B2B_WC_PATH . 'includes/class-b2b-importer.php';
        require_once B2B_WC_PATH . 'includes/class-b2b-tax-validation.php';
        require_once B2B_WC_PATH . 'includes/class-b2b-storefront.php';

        B2B_Customer_Manager::init();
        B2B_Pricing_Engine::init();
        B2B_Admin_Dashboard::init();
        B2B_Checkout::init();
        B2B_Email_Service::init();
        B2B_Quantity_Pricing::init();
        B2B_Importer::init();
        B2B_Tax_Validation::init();
        B2B_Storefront::init();
    }
}
