# B2B WooCommerce Extension - Commercial Edition

A production-ready B2B wholesale extension for WooCommerce with enterprise-grade features for wholesale customer management, tiered pricing, and approval workflows.

## Features

### Customer Management
- **B2B Registration Form**: Enhanced registration with company details, VAT/Tax ID, and business type
- **Approval Workflow**: Admin approval for new wholesale accounts
- **Customer Tiers**: Standard, Silver, Gold, and Platinum tier support
- **B2B Dashboard**: Dedicated customer portal for account and pricing information

### Pricing & Discounts
- **Tier-Based Pricing**: Different discount percentages per customer tier
- **Standard Tier**: Configurable base discount
- **Silver Tier**: Enhanced discount for growing businesses
- **Gold Tier**: Significant savings for established partners
- **Platinum Tier**: Premium discount for highest-volume customers

### Order Management
- **Minimum Order Enforcement**: Prevent orders below specified threshold
- **PO Number Tracking**: Capture purchase order numbers on checkout
- **Order Meta Storage**: PO numbers and B2B info stored with orders

### Admin Features
- **B2B Settings Page**: Configure all business rules centrally
- **Customer Directory**: View and manage all B2B customers
- **Bulk Approval**: Approve/reject wholesale account requests
- **Tier Management**: Assign customer tiers to users
- **Discount Configuration**: Set discount rates per tier

### Catalog Control
- **Restricted Catalog**: Show products only to approved wholesalers
- **Authentication-Based Access**: Non-wholesale users redirected to login
- **Purchasable Product Filtering**: Control which customers can buy

## Installation

1. Copy the `plugin/` folder to `wp-content/plugins/`
2. Activate in WordPress admin
3. Go to **WooCommerce > B2B Settings** to configure
4. Go to **WooCommerce > B2B Customers** to manage accounts

## Default Configuration

```
Minimum Order: $250
Standard Discount: 10%
Silver Discount: 15%
Gold Discount: 20%
Platinum Discount: 25%
Catalog Restricted: Yes
Approval Required: Yes
```

## Architecture

### Core Classes

- **B2B_WooCommerce_Extension**: Main plugin class and hooks registry
- **B2B_Customer_Manager**: Registration, profiles, and customer dashboard
- **B2B_Pricing_Engine**: Discount calculation and price filtering
- **B2B_Admin_Dashboard**: Admin interface and customer management
- **B2B_Checkout**: Custom checkout flow with PO number support

### Hooks & Filters

Extend functionality via:
- `b2b_wc_apply_discount` - Filter wholesale discount
- `b2b_wc_customer_tier` - Customize tier logic
- `b2b_wc_minimum_order` - Override minimum order
- `b2b_wc_is_wholesale_customer` - Custom wholesale detection

## Usage

### For Store Administrators

1. **Configure Settings**:
   - Set minimum order value
   - Define tier discount percentages
   - Enable/disable catalog restrictions
   - Toggle approval requirement

2. **Manage Customers**:
   - Review pending wholesale applications
   - Approve or reject accounts
   - Assign customer tiers
   - Edit company information

### For Wholesale Customers

1. **Register**: Fill in company details at checkout
2. **Wait for Approval**: Admin reviews your application
3. **Access Dashboard**: View account status, tier, and pricing
4. **Place Orders**: Benefit from tier-based discounts

## Customization

### Adding Custom Tiers

Edit `plugin/includes/class-b2b-customer-manager.php` and add to `get_tier_label()` and `get_tier_discount()` methods.

### Custom Checkout Fields

Hook into `woocommerce_checkout_init` or use WooCommerce's `woocommerce_form_field()` in `class-b2b-checkout.php`.

### Approval Workflow

Send custom emails or notifications by hooking into `save_customer_meta()` in `class-b2b-customer-manager.php`.

## Requirements

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.2+

## Security

- All user input sanitized with `sanitize_text_field()` and `wp_unslash()`
- Admin actions protected with nonces
- Role-based capability checks (`manage_woocommerce`)
- Secure user meta storage

## Performance

- Minimal database queries
- Efficient tier and discount lookups
- Caching compatible design
- No heavy page loads

## Support & Development

For issues or feature requests, check the GitHub repository:
https://github.com/gitaditya02/woocommerce-boilerplate

## License

GPL v2 or later
