<?php
/**
 * Plugin Name: WooCommerce Product Filter
 * Plugin URI: https://example.com/woocommerce-product-filter
 * Description: Advanced product filter for WooCommerce with price range and attribute filtering.
 * Version: 10.1.0
 * Author: Shyam Sahani
 * Author URI: https://example.com
 * Text Domain: woo-product-filter
 * Domain Path: /languages/
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 10.4
 * Requires Plugins: woocommerce
 * Woo: 10.1.0
 *
 * @package WooCommerceProductFilter
 * @version 10.1.0 */

// Declare WooCommerce HPOS compatibility
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', __FILE__, true );
    }
});

defined( 'ABSPATH' ) || exit;

class WC_Product_Filter {

    /**
     * Plugin version
     */
    const VERSION = '10.1.0';

    /**
     * Single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main WC_Product_Filter Instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
        
        // Initialize AJAX class immediately for both frontend and admin
        new WC_Product_Filter_AJAX();
    }

    /**
     * Include required core files
     */
    public function includes() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/attribute-enhancements.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-product-filter-admin.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-product-filter-info.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-product-filter-shortcode.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-product-filter-ajax.php';
    }

    /**
     * Initialize hooks
     */
    public function init_hooks() {
        // Check if WooCommerce is active
        if ( ! $this->is_woocommerce_active() ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_not_active_notice' ) );
            return;
        }

        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'woocommerce_locate_template', array( $this, 'override_woocommerce_template' ), 10, 3 );
        
        // Add plugin action links
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ) );
    }

    /**
     * Check if WooCommerce is active
     */
    public function is_woocommerce_active() {
        return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || 
               is_plugin_active_for_network( 'woocommerce/woocommerce.php' );
    }

    /**
     * Show notice if WooCommerce is not active
     */
    public function woocommerce_not_active_notice() {
        ?>
<div class="error">
    <p>
        <strong><?php _e( 'WooCommerce Product Filter', 'woo-product-filter' ); ?></strong>
        <?php _e( 'requires WooCommerce to be installed and active.', 'woo-product-filter' ); ?>
        <?php _e( 'Please install or activate WooCommerce to use this plugin.', 'woo-product-filter' ); ?>
    </p>
</div>
<?php
    }

    /**
     * Init plugin when WordPress loads
     */
    public function init() {
        // Declare WooCommerce support
        $this->declare_woocommerce_support();
        
        // Load text domain
        load_plugin_textdomain( 'woo-product-filter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Declare WooCommerce support
     */
    public function declare_woocommerce_support() {
        // Add WooCommerce support
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'wc-product-filter-style',
            plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
            array(),
            self::VERSION
        );

        wp_enqueue_script(
            'wc-product-filter-script',
            plugin_dir_url( __FILE__ ) . 'assets/js/filter.js',
            array( 'jquery' ),
            self::VERSION,
            true
        );

        wp_localize_script(
            'wc-product-filter-script',
            'wc_filter_params',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'wc_filter_nonce' ),
                'loading_text' => WC_Product_Filter_Admin::get_option( 'loading_text' ),
                'no_products_text' => WC_Product_Filter_Admin::get_option( 'no_products_text' ),
                'error_text' => __( 'Error loading products. Please try again.', 'woo-product-filter' ),
                'desktop_columns' => WC_Product_Filter_Admin::get_option( 'desktop_columns' ),
                'tablet_columns' => WC_Product_Filter_Admin::get_option( 'tablet_columns' ),
                'mobile_columns' => WC_Product_Filter_Admin::get_option( 'mobile_columns' )
            )
        );
    }

    /**
     * Override WooCommerce template
     */
    public function override_woocommerce_template( $template, $template_name, $template_path ) {
        if ( 'archive-product.php' === $template_name ) {
            $plugin_template = plugin_dir_path( __FILE__ ) . 'templates/archive-product.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
        return $template;
    }

    /**
     * Plugin activation
     */
    public static function activate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Add plugin action links
     */
    public function add_action_links( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=wc-product-filter-settings' ),
            __( 'Settings', 'woo-product-filter' )
        );
        
        $info_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'tools.php?page=wc-product-filter-info' ),
            __( 'Info', 'woo-product-filter' )
        );
        
        // Add settings link at the beginning
        array_unshift( $links, $settings_link );
        
        // Add info link after settings
        array_splice( $links, 1, 0, $info_link );
        
        return $links;
    }
}

// Register activation/deactivation hooks
register_activation_hook( __FILE__, array( 'WC_Product_Filter', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WC_Product_Filter', 'deactivate' ) );

// Initialize the plugin
function wc_product_filter() {
    return WC_Product_Filter::instance();
}

// Get the plugin running
wc_product_filter();