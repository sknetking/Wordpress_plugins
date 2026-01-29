<?php
/**
 * WooCommerce Product Filter Admin Settings
 *
 * @package WooCommerceProductFilter
 */

defined( 'ABSPATH' ) || exit;

class WC_Product_Filter_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',  // Changed from 'woocommerce' to 'tools.php'
            __( 'Product Filter Settings', 'woo-product-filter' ),
            __( 'Filter Settings', 'woo-product-filter' ),  // Changed from 'Product Filter'
            'manage_options',
            'wc-product-filter-settings',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'wc_product_filter_settings', 'wc_filter_options' );

        // General Settings
        add_settings_section(
            'wc_filter_general',
            __( 'General Settings', 'woo-product-filter' ),
            array( $this, 'general_section_callback' ),
            'wc_product_filter_settings'
        );

        add_settings_field(
            'enable_categories',
            __( 'Enable Category Filter', 'woo-product-filter' ),
            array( $this, 'checkbox_field' ),
            'wc_product_filter_settings',
            'wc_filter_general',
            array( 'name' => 'enable_categories', 'label' => __( 'Show product category filter', 'woo-product-filter' ) )
        );

        add_settings_field(
            'enable_tags',
            __( 'Enable Tag Filter', 'woo-product-filter' ),
            array( $this, 'checkbox_field' ),
            'wc_product_filter_settings',
            'wc_filter_general',
            array( 'name' => 'enable_tags', 'label' => __( 'Show product tag filter', 'woo-product-filter' ) )
        );

        add_settings_field(
            'enable_attributes',
            __( 'Enable Attribute Filters', 'woo-product-filter' ),
            array( $this, 'checkbox_field' ),
            'wc_product_filter_settings',
            'wc_filter_general',
            array( 'name' => 'enable_attributes', 'label' => __( 'Show product attribute filters', 'woo-product-filter' ) )
        );

        add_settings_field(
            'enable_price',
            __( 'Enable Price Filter', 'woo-product-filter' ),
            array( $this, 'checkbox_field' ),
            'wc_product_filter_settings',
            'wc_filter_general',
            array( 'name' => 'enable_price', 'label' => __( 'Show price range filter', 'woo-product-filter' ) )
        );

        // Layout Settings
        add_settings_section(
            'wc_filter_layout',
            __( 'Layout Settings', 'woo-product-filter' ),
            array( $this, 'layout_section_callback' ),
            'wc_product_filter_settings'
        );

        add_settings_field(
            'desktop_columns',
            __( 'Desktop Columns', 'woo-product-filter' ),
            array( $this, 'number_field' ),
            'wc_product_filter_settings',
            'wc_filter_layout',
            array( 'name' => 'desktop_columns', 'min' => 1, 'max' => 6, 'default' => 4 )
        );

        add_settings_field(
            'tablet_columns',
            __( 'Tablet Columns', 'woo-product-filter' ),
            array( $this, 'number_field' ),
            'wc_product_filter_settings',
            'wc_filter_layout',
            array( 'name' => 'tablet_columns', 'min' => 1, 'max' => 4, 'default' => 3 )
        );

        add_settings_field(
            'mobile_columns',
            __( 'Mobile Columns', 'woo-product-filter' ),
            array( $this, 'number_field' ),
            'wc_product_filter_settings',
            'wc_filter_layout',
            array( 'name' => 'mobile_columns', 'min' => 1, 'max' => 2, 'default' => 2 )
        );

        // Text Settings
        add_settings_section(
            'wc_filter_text',
            __( 'Text Settings', 'woo-product-filter' ),
            array( $this, 'text_section_callback' ),
            'wc_product_filter_settings'
        );

        add_settings_field(
            'filter_title',
            __( 'Filter Title', 'woo-product-filter' ),
            array( $this, 'text_field' ),
            'wc_product_filter_settings',
            'wc_filter_text',
            array( 'name' => 'filter_title', 'default' => __( 'Filter Products', 'woo-product-filter' ) )
        );

        add_settings_field(
            'price_range_text',
            __( 'Price Range Text', 'woo-product-filter' ),
            array( $this, 'text_field' ),
            'wc_product_filter_settings',
            'wc_filter_text',
            array( 'name' => 'price_range_text', 'default' => __( 'Price Range', 'woo-product-filter' ) )
        );

        add_settings_field(
            'categories_text',
            __( 'Categories Text', 'woo-product-filter' ),
            array( $this, 'text_field' ),
            'wc_product_filter_settings',
            'wc_filter_text',
            array( 'name' => 'categories_text', 'default' => __( 'Categories', 'woo-product-filter' ) )
        );

        add_settings_field(
            'tags_text',
            __( 'Tags Text', 'woo-product-filter' ),
            array( $this, 'text_field' ),
            'wc_product_filter_settings',
            'wc_filter_text',
            array( 'name' => 'tags_text', 'default' => __( 'Tags', 'woo-product-filter' ) )
        );

        add_settings_field(
            'no_products_text',
            __( 'No Products Text', 'woo-product-filter' ),
            array( $this, 'text_field' ),
            'wc_product_filter_settings',
            'wc_filter_text',
            array( 'name' => 'no_products_text', 'default' => __( 'No products found matching your criteria.', 'woo-product-filter' ) )
        );

        add_settings_field(
            'loading_text',
            __( 'Loading Text', 'woo-product-filter' ),
            array( $this, 'text_field' ),
            'wc_product_filter_settings',
            'wc_filter_text',
            array( 'name' => 'loading_text', 'default' => __( 'Loading products...', 'woo-product-filter' ) )
        );
    }

    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>' . __( 'Configure which filters to display on the frontend.', 'woo-product-filter' ) . '</p>';
    }

    /**
     * Layout section callback
     */
    public function layout_section_callback() {
        echo '<p>' . __( 'Configure the layout and responsive column settings.', 'woo-product-filter' ) . '</p>';
    }

    /**
     * Text section callback
     */
    public function text_section_callback() {
        echo '<p>' . __( 'Customize the text displayed in the filter interface.', 'woo-product-filter' ) . '</p>';
    }

    /**
     * Checkbox field
     */
    public function checkbox_field( $args ) {
        $options = get_option( 'wc_filter_options' );
        $name = $args['name'];
        $checked = isset( $options[$name] ) && $options[$name] === 'enabled' ? 'checked' : '';
        ?>
<label>
    <input type="checkbox" name="wc_filter_options[<?php echo $name; ?>]" value="enabled" <?php echo $checked; ?>>
    <?php echo $args['label']; ?>
</label>
<?php
    }

    /**
     * Number field
     */
    public function number_field( $args ) {
        $options = get_option( 'wc_filter_options' );
        $name = $args['name'];
        $value = isset( $options[$name] ) ? $options[$name] : $args['default'];
        ?>
<input type="number" name="wc_filter_options[<?php echo $name; ?>]" value="<?php echo $value; ?>"
    min="<?php echo $args['min']; ?>" max="<?php echo $args['max']; ?>" class="small-text">
<?php
    }

    /**
     * Text field
     */
    public function text_field( $args ) {
        $options = get_option( 'wc_filter_options' );
        $name = $args['name'];
        $value = isset( $options[$name] ) ? $options[$name] : $args['default'];
        ?>
<input type="text" name="wc_filter_options[<?php echo $name; ?>]" value="<?php echo esc_attr( $value ); ?>"
    class="regular-text">
<?php
    }

    /**
     * Settings page
     */
    public function settings_page() {
        ?>
<div class="wrap wc-filter-info-page">
    <div class="wc-filter-header">
        <h1>
            <span class="dashicons dashicons-filter"></span>
            <?php _e( 'WooCommerce Product Filter', 'woo-product-filter' ); ?>
        </h1>
        <p class="subtitle">
            <?php _e( 'Advanced product filtering for WooCommerce with dynamic attributes and responsive design', 'woo-product-filter' ); ?>
        </p>
    </div>

    <div class="notice notice-info">
        <p><strong><?php _e( 'Important:', 'woo-product-filter' ); ?></strong>
            <?php _e( 'This plugin is design and created by SK NetKing(Shyam Sahani)', 'woo-product-filter' ); ?>
        </p>
    </div>

    <div class="wc-filter-content">
        <!-- Plugin Overview -->
        <div class="wc-filter-card">
            <h2><span class="dashicons dashicons-info"></span> <?php _e( 'Plugin Features', 'woo-product-filter' ); ?>
            </h2>
            <div class="wc-filter-grid">
                <div class="wc-filter-feature">
                    <span class="dashicons dashicons-filter"></span>
                    <h3><?php _e( 'Dynamic Filtering', 'woo-product-filter' ); ?></h3>
                    <p><?php _e( 'Filter by price, categories, tags, and all WooCommerce attributes including colors, sizes, and brands.', 'woo-product-filter' ); ?>
                    </p>
                </div>
                <div class="wc-filter-feature">
                    <span class="dashicons dashicons-palette"></span>
                    <h3><?php _e( 'Visual Attributes', 'woo-product-filter' ); ?></h3>
                    <p><?php _e( 'Color swatches, size badges, and image thumbnails for intuitive product filtering.', 'woo-product-filter' ); ?>
                    </p>
                </div>
                <div class="wc-filter-feature">
                    <span class="dashicons dashicons-smartphone"></span>
                    <h3><?php _e( 'Responsive Design', 'woo-product-filter' ); ?></h3>
                    <p><?php _e( 'Mobile-friendly interface with configurable columns for desktop, tablet, and mobile devices.', 'woo-product-filter' ); ?>
                    </p>
                </div>
                <div class="wc-filter-feature">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <h3><?php _e( 'Shortcode Control', 'woo-product-filter' ); ?></h3>
                    <p><?php _e( 'Complete control via shortcode attributes to configure which filters to show and customize layout.', 'woo-product-filter' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Shortcode Guide -->
        <div class="wc-filter-card">
            <h2><span class="dashicons dashicons-shortcode"></span>
                <?php _e( 'Shortcode Usage Guide', 'woo-product-filter' ); ?></h2>

            <div class="wc-filter-shortcode-basic">
                <h3><?php _e( 'Basic Usage', 'woo-product-filter' ); ?></h3>
                <div class="wc-shortcode-example">
                    <code>[product_filter]</code>
                    <button class="button wc-copy-btn"
                        data-text="[product_filter]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>
                <p><?php _e( 'Display the product filter with all filters enabled by default.', 'woo-product-filter' ); ?>
                </p>
            </div>

            <div class="wc-filter-shortcode-advanced">
                <h3><?php _e( 'Advanced Options', 'woo-product-filter' ); ?></h3>

                <div class="wc-shortcode-example">
                    <code>[product_filter columns="3" posts_per_page="9"]</code>
                    <button class="button wc-copy-btn"
                        data-text="[product_filter columns=&quot;3&quot; posts_per_page=&quot;9&quot;]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>
                <p><?php _e( 'Customize layout with specific columns and products per page.', 'woo-product-filter' ); ?>
                </p>

                <div class="wc-shortcode-example">
                    <code>[product_filter show_price="yes" show_categories="yes" show_tags="no" show_attributes="no"]</code>
                    <button class="button wc-copy-btn"
                        data-text="[product_filter show_price=&quot;yes&quot; show_categories=&quot;yes&quot; show_tags=&quot;no&quot; show_attributes=&quot;no&quot;]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>
                <p><?php _e( 'Control which filter sections to display.', 'woo-product-filter' ); ?></p>
            </div>

            <h3><?php _e( 'Available Attributes', 'woo-product-filter' ); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'Attribute', 'woo-product-filter' ); ?></th>
                        <th><?php _e( 'Values', 'woo-product-filter' ); ?></th>
                        <th><?php _e( 'Description', 'woo-product-filter' ); ?></th>
                        <th><?php _e( 'Default', 'woo-product-filter' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>show_price</code></td>
                        <td><code>yes</code>, <code>no</code>, <code>enabled</code>, <code>disabled</code></td>
                        <td><?php _e( 'Show/hide price filter', 'woo-product-filter' ); ?></td>
                        <td><code>yes</code></td>
                    </tr>
                    <tr>
                        <td><code>show_categories</code></td>
                        <td><code>yes</code>, <code>no</code>, <code>enabled</code>, <code>disabled</code></td>
                        <td><?php _e( 'Show/hide category filter', 'woo-product-filter' ); ?></td>
                        <td><code>yes</code></td>
                    </tr>
                    <tr>
                        <td><code>show_tags</code></td>
                        <td><code>yes</code>, <code>no</code>, <code>enabled</code>, <code>disabled</code></td>
                        <td><?php _e( 'Show/hide tag filter', 'woo-product-filter' ); ?></td>
                        <td><code>yes</code></td>
                    </tr>
                    <tr>
                        <td><code>show_attributes</code></td>
                        <td><code>yes</code>, <code>no</code>, <code>enabled</code>, <code>disabled</code></td>
                        <td><?php _e( 'Show/hide attribute filters', 'woo-product-filter' ); ?></td>
                        <td><code>yes</code></td>
                    </tr>
                    <tr>
                        <td><code>columns</code></td>
                        <td><code>1-6</code></td>
                        <td><?php _e( 'Number of product columns', 'woo-product-filter' ); ?></td>
                        <td><code>4</code></td>
                    </tr>
                    <tr>
                        <td><code>posts_per_page</code></td>
                        <td><code>number</code></td>
                        <td><?php _e( 'Products per page', 'woo-product-filter' ); ?></td>
                        <td><code>12</code></td>
                    </tr>
                </tbody>
            </table>

            <h3><?php _e( 'Examples', 'woo-product-filter' ); ?></h3>
            <div class="shortcode-examples">
                <p><strong><?php _e( 'Show only price and category filters:', 'woo-product-filter' ); ?></strong></p>
                <div class="wc-shortcode-example">
                    <code>[product_filter show_price="yes" show_categories="yes" show_tags="no" show_attributes="no"]</code>
                    <button class="button wc-copy-btn"
                        data-text="[product_filter show_price=&quot;yes&quot; show_categories=&quot;yes&quot; show_tags=&quot;no&quot; show_attributes=&quot;no&quot;]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>

                <p><strong><?php _e( 'Show all filters with 3 columns:', 'woo-product-filter' ); ?></strong></p>
                <div class="wc-shortcode-example">
                    <code>[product_filter columns="3"]</code>
                    <button class="button wc-copy-btn"
                        data-text="[product_filter columns=&quot;3&quot;]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>

                <p><strong><?php _e( 'Show only price filter:', 'woo-product-filter' ); ?></strong></p>
                <div class="wc-shortcode-example">
                    <code>[product_filter show_price="enabled" show_categories="disabled" show_tags="disabled" show_attributes="disabled"]</code>
                    <button class="button wc-copy-btn"
                        data-text="[product_filter show_price=&quot;enabled&quot; show_categories=&quot;disabled&quot; show_tags=&quot;disabled&quot; show_attributes=&quot;disabled&quot;]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.wc-copy-btn').on('click', function() {
        var text = $(this).data('text');
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();

        $(this).text('<?php _e( 'Copied!', 'woo-product-filter' ); ?>');
        var $btn = $(this);
        setTimeout(function() {
            $btn.text('<?php _e( 'Copy', 'woo-product-filter' ); ?>');
        }, 2000);
    });
});
</script>

<style>
.wc-filter-info-page .wc-filter-header {
    margin-bottom: 20px;
    padding: 20px 0;
    border-bottom: 1px solid #e1e1e1;
}

.wc-filter-info-page .wc-filter-header h1 {
    font-size: 28px;
    margin: 0 0 10px 0;
}

.wc-filter-info-page .wc-filter-header h1 .dashicons {
    font-size: 32px;
    height: 32px;
    width: 32px;
    margin-right: 10px;
    vertical-align: middle;
}

.wc-filter-info-page .wc-filter-header .subtitle {
    font-size: 16px;
    color: #666;
    margin: 0;
}

.wc-filter-content {
    margin-top: 20px;
}

.wc-filter-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
}

.wc-filter-card h2 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 20px;
    color: #23282d;
}

.wc-filter-card h2 .dashicons {
    font-size: 24px;
    height: 24px;
    width: 24px;
    margin-right: 8px;
    vertical-align: middle;
}

.wc-filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.wc-filter-feature {
    text-align: center;
    padding: 20px;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    background: #f9f9f9;
}

.wc-filter-feature .dashicons {
    font-size: 48px;
    height: 48px;
    width: 48px;
    margin-bottom: 15px;
    color: #0073aa;
}

.wc-filter-feature h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #23282d;
}

.wc-filter-feature p {
    margin: 0;
    font-size: 14px;
    color: #666;
    line-height: 1.5;
}

.wc-filter-shortcode-basic,
.wc-filter-shortcode-advanced {
    margin-bottom: 30px;
}

.wc-filter-shortcode-basic h3,
.wc-filter-shortcode-advanced h3 {
    margin-bottom: 15px;
    color: #23282d;
}

.wc-shortcode-example {
    background: #f8f9f9;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.wc-shortcode-example code {
    flex: 1;
    background: #f1f1f1;
    padding: 8px 12px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 13px;
    border: 1px solid #ddd;
}

.wc-copy-btn {
    flex-shrink: 0;
}

.shortcode-examples {
    margin-top: 20px;
}

.shortcode-examples p {
    margin-bottom: 10px;
}

.shortcode-examples .wc-shortcode-example {
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .wc-filter-grid {
        grid-template-columns: 1fr;
    }

    .wc-shortcode-example {
        flex-direction: column;
        align-items: stretch;
    }

    .wc-shortcode-example code {
        margin-bottom: 10px;
    }
}
</style>
<?php
    }

    /**
     * Admin scripts
     */
    public function admin_scripts( $hook ) {
        if ( 'tools_page_wc-product-filter-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wc-filter-admin', plugin_dir_url( __FILE__ ) . '../assets/css/admin.css', array(), '1.0.0' );
    }

    /**
     * Get default options
     */
    public static function get_default_options() {
        return array(
            'enable_categories' => 'enabled',
            'enable_tags' => 'enabled',
            'enable_attributes' => 'enabled',
            'enable_price' => 'enabled',
            'desktop_columns' => 4,
            'tablet_columns' => 3,
            'mobile_columns' => 2,
            'filter_title' => __( 'Filter Products', 'woo-product-filter' ),
            'price_range_text' => __( 'Price Range', 'woo-product-filter' ),
            'categories_text' => __( 'Categories', 'woo-product-filter' ),
            'tags_text' => __( 'Tags', 'woo-product-filter' ),
            'no_products_text' => __( 'No products found matching your criteria.', 'woo-product-filter' ),
            'loading_text' => __( 'Loading products...', 'woo-product-filter' )
        );
    }

    /**
     * Get option value
     */
    public static function get_option( $key, $default = null ) {
        $options = get_option( 'wc_filter_options' );
        $defaults = self::get_default_options();
        
        if ( $default === null && isset( $defaults[$key] ) ) {
            $default = $defaults[$key];
        }
        
        return isset( $options[$key] ) ? $options[$key] : $default;
    }
}

// Initialize admin class only if in admin area
if ( is_admin() ) {
    new WC_Product_Filter_Admin();
}