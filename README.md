# WooCommerce Boilerplate

A comprehensive boilerplate for building custom WooCommerce plugins and themes. This starter kit includes best practices, hooks, filters, and a solid foundation for extending WooCommerce functionality.

## Features

- ✅ Plugin boilerplate with OOP structure
- ✅ Theme boilerplate with WooCommerce support
- ✅ Custom product templates
- ✅ Asset management (CSS/JS)
- ✅ WordPress coding standards compliant
- ✅ Ready for production development

## Directory Structure

```
woocommerce-boilerplate/
├── plugin/
│   ├── woocommerce-custom-plugin.php
│   ├── includes/
│   │   └── class-custom-woocommerce.php
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   └── js/
│   │       └── script.js
│   └── languages/
│       └── wc-custom-plugin.pot
├── theme/
│   ├── style.css
│   ├── functions.php
│   ├── index.php
│   ├── header.php
│   ├── footer.php
│   ├── woocommerce/
│   │   ├── archive-product.php
│   │   └── single-product.php
│   └── assets/
│       ├── css/
│       └── js/
└── README.md
```

## Getting Started

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/gitaditya02/woocommerce-boilerplate.git
   ```

2. **For Plugin Development:**
   - Copy the `plugin/` folder to `wp-content/plugins/`
   - Activate the plugin from WordPress admin

3. **For Theme Development:**
   - Copy the `theme/` folder to `wp-content/themes/`
   - Activate the theme from WordPress admin

## Usage

### Plugin Development

Edit `plugin/includes/class-custom-woocommerce.php` to add your custom functionality:

```php
public static function setup() {
    // Add your hooks and filters here
    add_action('woocommerce_before_main_content', [__CLASS__, 'your_custom_function']);
}
```

### Theme Development

Customize `theme/functions.php` and override WooCommerce templates in `theme/woocommerce/`.

## WooCommerce Hooks & Filters

### Common Actions
- `woocommerce_init` - WooCommerce initialized
- `woocommerce_before_main_content` - Before shop page content
- `woocommerce_after_main_content` - After shop page content
- `woocommerce_before_single_product` - Before single product page
- `woocommerce_single_product_summary` - Product summary section

### Common Filters
- `woocommerce_product_loop_columns` - Modify columns in product loop
- `woocommerce_default_catalog_orderby` - Change product sorting
- `woocommerce_add_to_cart_redirect` - Redirect after add to cart

## Best Practices

- ✅ Use hooks and filters instead of editing core files
- ✅ Follow [WordPress Coding Standards](https://developer.wordpress.org/plugins/wordpress-org/planning-your-plugin/)
- ✅ Always check for WooCommerce class existence before using
- ✅ Use proper text domains for translations
- ✅ Keep custom code separate from WooCommerce updates
- ✅ Enqueue scripts and styles properly
- ✅ Use appropriate capabilities and nonces for security

## Requirements

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.2+

## Documentation

- [WooCommerce Developer Documentation](https://developer.woocommerce.com/)
- [WordPress Plugin Development](https://developer.wordpress.org/plugins/)
- [WordPress Theme Development](https://developer.wordpress.org/themes/)

## Contributing

Contributions are welcome! Please follow the WordPress Coding Standards.

## License

GPL v2 or later - See LICENSE file

## Support

For issues and questions, please open a GitHub issue.
