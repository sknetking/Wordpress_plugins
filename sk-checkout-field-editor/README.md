# SK Checkout Field Editor

A comprehensive WooCommerce checkout field editor plugin that allows administrators to manage default WooCommerce fields and create custom fields with various options.

## Features

### Default Fields Management
- **Show/Hide Fields**: Enable or disable any default WooCommerce checkout field
- **Edit Labels**: Customize field labels to match your store's terminology
- **Reorder Fields**: Drag and drop interface to reorder fields within each section
- **Required Settings**: Control which fields are required
- **Field Priority**: Set custom order priority for each field

### Custom Field Builder
- **Multiple Field Types**:
  - Text, Email, Phone, Number, Password
  - Textarea for longer text
  - Select Dropdown and Radio Buttons
  - Checkbox for yes/no options
  - Date and Time pickers
  - File Upload with validation
  - Hidden fields for internal data

- **Field Positioning**:
  - Billing Section
  - Shipping Section  
  - After Additional Notes section

- **Field Options**:
  - Custom labels and placeholders
  - Default values
  - CSS classes for styling
  - Required field validation
  - Custom field order

### Display Options
- **Email Integration**: Choose which custom fields appear in customer and admin emails
- **Order Details**: Display custom field values in the WordPress admin order details
- **Frontend Validation**: Real-time validation for email, phone, and number fields
- **File Upload**: Secure file handling with size and type validation

## Installation

1. Download the plugin ZIP file
2. Upload to your WordPress plugins directory (`/wp-content/plugins/`)
3. Activate the plugin through the WordPress admin dashboard
4. Navigate to **WooCommerce → Checkout Fields** to configure

## Usage

### Managing Default Fields

1. Go to **WooCommerce → Checkout Fields**
2. Click on the **Default Fields** tab
3. Use the toggle switches to show/hide fields
4. Edit field labels directly in the text inputs
5. Drag and drop fields to reorder them
6. Set required status and field order
7. Click **Save Changes**

### Adding Custom Fields

1. Go to **WooCommerce → Checkout Fields**
2. Click on the **Custom Fields** tab
3. Fill in the field details:
   - **Field Name**: Unique identifier (lowercase, underscores only)
   - **Field Label**: Display name for customers
   - **Field Type**: Choose from available field types
   - **Position**: Where the field should appear
   - **Additional Options**: Placeholder, default value, CSS classes
4. Configure display options (email, order details)
5. Click **Add Field**

### Field Types Reference

| Field Type | Description | Use Cases |
|------------|-------------|-----------|
| Text | Single line text input | Names, short answers |
| Email | Email address with validation | Contact emails |
| Phone | Phone number with formatting | Contact numbers |
| Number | Numeric input only | Quantities, ages |
| Textarea | Multi-line text input | Comments, descriptions |
| Select | Dropdown selection | Country, product choices |
| Radio | Radio button group | Gender, yes/no |
| Checkbox | Single checkbox | Terms acceptance |
| Date | Date picker | Delivery dates |
| Time | Time picker | Appointment times |
| File | File upload | Documents, images |
| Hidden | Hidden field | Internal tracking |

## File Upload Security

- **File Size Limit**: 5MB maximum
- **Allowed File Types**: JPG, PNG, GIF, PDF, DOC, TXT
- **Secure Storage**: Files are stored as WordPress attachments
- **Automatic Cleanup**: Files are linked to specific orders

## Customization

### CSS Classes

Add custom CSS classes to fields for advanced styling:
1. Edit a custom field
2. Enter your CSS class in the "CSS Class" field
3. Add custom CSS to your theme's stylesheet

### Hooks and Filters

The plugin provides several hooks for developers:

```php
// Filter to modify field configuration
add_filter('sk_cfe_field_config', 'my_custom_field_config', 10, 2);

// Action before field display
add_action('sk_cfe_before_field', 'my_before_field_function', 10, 2);

// Action after field display
add_action('sk_cfe_after_field', 'my_after_field_function', 10, 2);
```

### Template Overrides

Override the field display templates:
- Create a `sk-checkout-field-editor` folder in your theme
- Copy template files from `plugins/sk-checkout-field-editor/templates/`
- Modify as needed

## Compatibility

- **WordPress**: 5.0+
- **WooCommerce**: 3.0+
- **PHP**: 7.0+

## Support

For support and feature requests:
1. Check the documentation
2. Search existing issues
3. Create a new support ticket

## Changelog

### Version 1.0.0
- Initial release
- Default field management
- Custom field builder
- File upload support
- Email and order details integration

## License

This plugin is licensed under the GPL v2 or later.

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Submit a pull request
4. Follow WordPress coding standards

## Security

The plugin follows WordPress security best practices:
- All input is sanitized and validated
- File uploads are restricted and secure
- Nonce verification for all AJAX requests
- Capability checks for admin functions
