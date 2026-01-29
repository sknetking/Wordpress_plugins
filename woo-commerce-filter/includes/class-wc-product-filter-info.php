<?php
/**
 * WooCommerce Product Filter - Info Page
 *
 * @package WooCommerceProductFilter
 */

defined( 'ABSPATH' ) || exit;

class WC_Product_Filter_Info {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            __( 'Product Filter Info', 'woo-product-filter' ),
            __( 'Product Filter', 'woo-product-filter' ),
            'manage_options',
            'wc-product-filter-info',
            array( $this, 'info_page' )
        );
    }

    /**
     * Admin scripts
     */
    public function admin_scripts( $hook ) {
        if ( 'tools_page_wc-product-filter-info' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wc-filter-info', plugin_dir_url( __FILE__ ) . '../assets/css/info.css', array(), '1.0.0' );
        wp_enqueue_script( 'wc-filter-info', plugin_dir_url( __FILE__ ) . '../assets/js/info.js', array( 'jquery' ), '1.0.0', true );
    }

    /**
     * Info page content
     */
    public function info_page() {
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

    <div class="wc-filter-content">
        <!-- Plugin Overview -->
        <div class="wc-filter-card">
            <h2><span class="dashicons dashicons-info"></span> <?php _e( 'Plugin Overview', 'woo-product-filter' ); ?>
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
                    <h3><?php _e( 'Admin Control', 'woo-product-filter' ); ?></h3>
                    <p><?php _e( 'Complete admin settings to configure which filters to show and customize all text labels.', 'woo-product-filter' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Shortcode Guide -->
        <div class="wc-filter-card">
            <h2><span class="dashicons dashicons-shortcode"></span>
                <?php _e( 'Shortcode Guide', 'woo-product-filter' ); ?></h2>

            <div class="wc-filter-shortcode-basic">
                <h3><?php _e( 'Basic Usage', 'woo-product-filter' ); ?></h3>
                <div class="wc-shortcode-example">
                    <code>[product_filter]</code>
                    <button class="wc-copy-btn"
                        data-text="[product_filter]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>
                <p><?php _e( 'Display the product filter with default settings from admin panel.', 'woo-product-filter' ); ?>
                </p>
            </div>

            <div class="wc-filter-shortcode-advanced">
                <h3><?php _e( 'Advanced Options', 'woo-product-filter' ); ?></h3>

                <div class="wc-shortcode-example">
                    <code>[product_filter columns="3" posts_per_page="9"]</code>
                    <button class="wc-copy-btn" data-text="[product_filter columns=" 3" posts_per_page="9"
                        ]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>
                <p><?php _e( 'Customize layout with specific columns and products per page.', 'woo-product-filter' ); ?>
                </p>

                <div class="wc-shortcode-example">
                    <code>[product_filter show_price="yes" show_categories="yes" show_tags="no"]</code>
                    <button class="wc-copy-btn" data-text="[product_filter show_price=" yes" show_categories="yes"
                        show_tags="no" ]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>
                <p><?php _e( 'Control which filter sections to display.', 'woo-product-filter' ); ?></p>

                <div class="wc-shortcode-example">
                    <code>[product_filter columns="4" posts_per_page="12" show_price="yes" show_categories="yes" show_tags="yes" show_attributes="yes"]</code>
                    <button class="wc-copy-btn" data-text="[product_filter columns=" 4" posts_per_page="12"
                        show_price="yes" show_categories="yes" show_tags="yes" show_attributes="yes"
                        ]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                </div>
                <p><?php _e( 'Complete shortcode with all available options.', 'woo-product-filter' ); ?></p>
            </div>

            <div class="wc-filter-attributes-table">
                <h3><?php _e( 'Available Attributes', 'woo-product-filter' ); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'Attribute', 'woo-product-filter' ); ?></th>
                            <th><?php _e( 'Description', 'woo-product-filter' ); ?></th>
                            <th><?php _e( 'Default', 'woo-product-filter' ); ?></th>
                            <th><?php _e( 'Example', 'woo-product-filter' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>columns</code></td>
                            <td><?php _e( 'Number of product columns', 'woo-product-filter' ); ?></td>
                            <td><code>4</code></td>
                            <td><code>columns="3"</code></td>
                        </tr>
                        <tr>
                            <td><code>posts_per_page</code></td>
                            <td><?php _e( 'Products per page', 'woo-product-filter' ); ?></td>
                            <td><code>12</code></td>
                            <td><code>posts_per_page="9"</code></td>
                        </tr>
                        <tr>
                            <td><code>show_price</code></td>
                            <td><?php _e( 'Show price filter', 'woo-product-filter' ); ?></td>
                            <td><code>yes</code></td>
                            <td><code>show_price="no"</code></td>
                        </tr>
                        <tr>
                            <td><code>show_categories</code></td>
                            <td><?php _e( 'Show category filter', 'woo-product-filter' ); ?></td>
                            <td><code>yes</code></td>
                            <td><code>show_categories="no"</code></td>
                        </tr>
                        <tr>
                            <td><code>show_tags</code></td>
                            <td><?php _e( 'Show tag filter', 'woo-product-filter' ); ?></td>
                            <td><code>yes</code></td>
                            <td><code>show_tags="no"</code></td>
                        </tr>
                        <tr>
                            <td><code>show_attributes</code></td>
                            <td><?php _e( 'Show attribute filters', 'woo-product-filter' ); ?></td>
                            <td><code>yes</code></td>
                            <td><code>show_attributes="no"</code></td>
                        </tr>
                        <tr>
                            <td><code>show_clear</code></td>
                            <td><?php _e( 'Show clear filters button', 'woo-product-filter' ); ?></td>
                            <td><code>yes</code></td>
                            <td><code>show_clear="no"</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Implementation Guide -->
        <div class="wc-filter-card">
            <h2><span class="dashicons dashicons-clipboard"></span>
                <?php _e( 'Implementation Guide', 'woo-product-filter' ); ?></h2>

            <div class="wc-filter-steps">
                <div class="wc-step">
                    <div class="wc-step-number">1</div>
                    <div class="wc-step-content">
                        <h3><?php _e( 'Configure Settings', 'woo-product-filter' ); ?></h3>
                        <p><?php _e( 'Go to <strong>WooCommerce → Product Filter Settings</strong> to configure which filters to display and customize text labels.', 'woo-product-filter' ); ?>
                        </p>
                        <a href="<?php echo admin_url( 'admin.php?page=wc-product-filter-settings' ); ?>"
                            class="button button-primary">
                            <?php _e( 'Open Settings', 'woo-product-filter' ); ?>
                        </a>
                    </div>
                </div>

                <div class="wc-step">
                    <div class="wc-step-number">2</div>
                    <div class="wc-step-content">
                        <h3><?php _e( 'Add Shortcode', 'woo-product-filter' ); ?></h3>
                        <p><?php _e( 'Add the shortcode to any page, post, or widget where you want the product filter to appear.', 'woo-product-filter' ); ?>
                        </p>
                        <div class="wc-shortcode-example">
                            <code>[product_filter]</code>
                            <button class="wc-copy-btn"
                                data-text="[product_filter]"><?php _e( 'Copy', 'woo-product-filter' ); ?></button>
                        </div>
                    </div>
                </div>

                <div class="wc-step">
                    <div class="wc-step-number">3</div>
                    <div class="wc-step-content">
                        <h3><?php _e( 'Customize Attributes', 'woo-product-filter' ); ?></h3>
                        <p><?php _e( 'For color and size attributes, go to <strong>Products → Attributes</strong> and edit individual terms to add colors or images.', 'woo-product-filter' ); ?>
                        </p>
                        <a href="<?php echo admin_url( 'edit.php?post_type=product&page=product_attributes' ); ?>"
                            class="button">
                            <?php _e( 'Manage Attributes', 'woo-product-filter' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="wc-filter-card">
            <h2><span class="dashicons dashicons-admin-links"></span>
                <?php _e( 'Quick Links', 'woo-product-filter' ); ?></h2>
            <div class="wc-filter-links">
                <a href="<?php echo admin_url( 'admin.php?page=wc-product-filter-settings' ); ?>" class="wc-link-card">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <div>
                        <strong><?php _e( 'Filter Settings', 'woo-product-filter' ); ?></strong>
                        <p><?php _e( 'Configure filter options and layout', 'woo-product-filter' ); ?></p>
                    </div>
                </a>
                <a href="<?php echo admin_url( 'edit.php?post_type=product&page=product_attributes' ); ?>"
                    class="wc-link-card">
                    <span class="dashicons dashicons-tag"></span>
                    <div>
                        <strong><?php _e( 'Product Attributes', 'woo-product-filter' ); ?></strong>
                        <p><?php _e( 'Manage colors, sizes, and other attributes', 'woo-product-filter' ); ?></p>
                    </div>
                </a>
                <a href="<?php echo admin_url( 'edit.php?post_type=product' ); ?>" class="wc-link-card">
                    <span class="dashicons dashicons-products"></span>
                    <div>
                        <strong><?php _e( 'Products', 'woo-product-filter' ); ?></strong>
                        <p><?php _e( 'View and manage your products', 'woo-product-filter' ); ?></p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
    }
}

// Initialize info class only if in admin area
// if ( is_admin() ) {
//     new WC_Product_Filter_Info();
// }