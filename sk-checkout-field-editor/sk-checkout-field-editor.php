<?php
/**
 * Plugin Name: SK Checkout Field Editor
 * Plugin URI: https://example.com/sk-checkout-field-editor
 * Description: A comprehensive WooCommerce checkout field editor that allows admin to manage default fields and create custom fields with various options.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sk-checkout-field-editor
 * Domain Path: /languages
 * WC requires at least: 8.0
 * WC tested up to: 9.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Elementor requires at least: 3.0.0
 * Elementor tested up to: 3.20.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SK_CFE_VERSION', '1.0.0' );
define( 'SK_CFE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SK_CFE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

class SK_Checkout_Field_Editor {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ) );
        
        // HPOS compatibility declaration
        add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
        
        // Checkout field modifications
        add_filter( 'woocommerce_checkout_fields', array( $this, 'modify_checkout_fields' ), 15 );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_custom_fields' ), 10, 2 );
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_custom_fields_in_admin' ) );
        add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'add_custom_fields_to_email' ), 10, 3 );
    }

    public function init() {
        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }

        // Load text domain
        load_plugin_textdomain( 'sk-checkout-field-editor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        
        // Include required files
        require_once SK_CFE_PLUGIN_DIR . 'includes/class-sk-cfe-admin.php';
        require_once SK_CFE_PLUGIN_DIR . 'includes/class-sk-cfe-field-manager.php';
        require_once SK_CFE_PLUGIN_DIR . 'includes/class-sk-cfe-checkout-handler.php';
    }

    /**
     * Declare HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Checkout Field Editor', 'sk-checkout-field-editor' ),
            __( 'Checkout Fields', 'sk-checkout-field-editor' ),
            'manage_woocommerce',
            'sk-cfe-settings',
            array( $this, 'admin_page' ),
            'dashicons-edit',
            30
        );
    }

    public function admin_page() {
        require_once SK_CFE_PLUGIN_DIR . 'admin/admin-page.php';
    }

    public function admin_enqueue_scripts( $hook ) {
        if ( 'toplevel_page_sk-cfe-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'sk-cfe-admin', SK_CFE_PLUGIN_URL . 'assets/css/admin.css', array(), SK_CFE_VERSION );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'sk-cfe-admin', SK_CFE_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), SK_CFE_VERSION, true );
        
        wp_localize_script( 'sk-cfe-admin', 'sk_cfe_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'sk_cfe_nonce' ),
            'confirm_delete' => __( 'Are you sure you want to delete this field?', 'sk-checkout-field-editor' ),
        ) );
    }

    public function frontend_enqueue_scripts() {
        if ( is_checkout() ) {
            wp_enqueue_style( 'sk-cfe-frontend', SK_CFE_PLUGIN_URL . 'assets/css/frontend.css', array(), SK_CFE_VERSION );
            wp_enqueue_script( 'sk-cfe-frontend', SK_CFE_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery' ), SK_CFE_VERSION, true );
            
            wp_localize_script( 'sk-cfe-frontend', 'sk_cfe_frontend_vars', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'sk_cfe_nonce' ),
            ) );
        }
    }

    public function modify_checkout_fields( $fields ) {
        $field_manager = new SK_CFE_Field_Manager();
        return $field_manager->modify_checkout_fields( $fields );
    }

    public function save_custom_fields( $order_id, $data ) {
        $checkout_handler = new SK_CFE_Checkout_Handler();
        $checkout_handler->save_custom_fields( $order_id, $data );
    }

    public function display_custom_fields_in_admin( $order ) {
        $checkout_handler = new SK_CFE_Checkout_Handler();
        $checkout_handler->display_custom_fields_in_admin( $order );
    }

    public function add_custom_fields_to_email( $fields, $sent_to_admin, $order ) {
        $checkout_handler = new SK_CFE_Checkout_Handler();
        return $checkout_handler->add_custom_fields_to_email( $fields, $sent_to_admin, $order );
    }

    public function woocommerce_missing_notice() {
        ?>
<div class="error notice">
    <p><?php _e( 'SK Checkout Field Editor requires WooCommerce to be installed and active.', 'sk-checkout-field-editor' ); ?>
    </p>
</div>
<?php
    }
}

// Initialize the plugin
function sk_cfe_init() {
    return SK_Checkout_Field_Editor::get_instance();
}

// Get the plugin instance
$GLOBALS['sk_cfe'] = sk_cfe_init();