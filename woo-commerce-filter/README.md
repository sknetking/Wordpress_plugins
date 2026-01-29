# WooCommerce Product Filter

A powerful and flexible product filtering plugin for WooCommerce that provides advanced filtering capabilities with price ranges and product attributes.

## Features

- **Price Range Filtering**: Filter products by minimum and maximum price
- **Attribute Filtering**: Filter by any WooCommerce product attributes (color, size, brand, etc.)
- **AJAX Powered**: Instant filtering without page reload
- **Responsive Design**: Works perfectly on all devices
- **Easy Integration**: Simple shortcode implementation
- **Secure**: Built with WordPress security best practices

## Requirements

- WordPress 6.0+
- PHP 7.4+
- WooCommerce 5.0+

## Installation

1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the ZIP file
4. Activate the plugin

## Usage

### Shortcode

Use the following shortcode to display the product filter anywhere on your site:

```
[product_filter]
```

### Template Override

The plugin automatically overrides the WooCommerce shop archive template to display the filter. If you want to customize this, you can modify the `archive-product.php` file in the plugin directory.

### Customization

The filter automatically detects all your WooCommerce product attributes and creates filter options for them. No additional configuration needed!

## Styling

The plugin includes comprehensive styling that matches modern WordPress themes. You can override the styles by adding your custom CSS:

```css
/* Custom filter container */
#product-filters {
    background: your-color;
    border: your-border;
}

/* Custom button styling */
#clear-filters {
    background: your-button-color;
}
```

## Hook Reference

### Filters

- `woocommerce_locate_template`: Overrides the archive template location
- `wp_enqueue_scripts`: Enqueues plugin scripts and styles

### Actions

- `wp_ajax_filter_products`: Handles AJAX filtering requests
- `wp_ajax_nopriv_filter_products`: Handles AJAX filtering for non-logged-in users

## Security

This plugin implements WordPress security best practices:

- Nonce verification for all AJAX requests
- Input sanitization and validation
- Proper capability checks
- SQL injection prevention

## Support

For support and feature requests, please contact the plugin author.

## Changelog

### Version 1.0.0
- Initial release
- Price range filtering
- Product attribute filtering
- AJAX-powered filtering
- Responsive design
- Security enhancements
