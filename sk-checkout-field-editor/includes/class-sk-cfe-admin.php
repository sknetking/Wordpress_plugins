<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SK_CFE_Admin {

    public function __construct() {
        add_action( 'wp_ajax_sk_cfe_reorder_fields', array( $this, 'ajax_reorder_fields' ) );
        add_action( 'wp_ajax_sk_cfe_get_custom_field', array( $this, 'ajax_get_custom_field' ) );
        add_action( 'wp_ajax_sk_cfe_update_custom_field', array( $this, 'ajax_update_custom_field' ) );
        
        // Clear WooCommerce checkout fields cache when settings are updated
        add_action( 'update_option_sk_cfe_default_fields_settings', array( $this, 'clear_wc_cache' ), 10, 2 );
        add_action( 'update_option_sk_cfe_custom_fields', array( $this, 'clear_wc_cache' ), 10, 2 );
    }

    public function get_default_woocommerce_fields() {
        return array(
            'billing' => array(
                'billing_first_name' => array(
                    'label' => __( 'First Name', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-first' ),
                ),
                'billing_last_name' => array(
                    'label' => __( 'Last Name', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-last' ),
                ),
                'billing_company' => array(
                    'label' => __( 'Company Name', 'woocommerce' ),
                    'required' => false,
                    'class' => array( 'form-row-wide' ),
                ),
                'billing_country' => array(
                    'label' => __( 'Country', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
                ),
                'billing_address_1' => array(
                    'label' => __( 'Street Address', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
                'billing_address_2' => array(
                    'label' => __( 'Apartment, suite, unit etc. (optional)', 'woocommerce' ),
                    'required' => false,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
                'billing_city' => array(
                    'label' => __( 'Town / City', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
                'billing_state' => array(
                    'label' => __( 'State / County', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
                'billing_postcode' => array(
                    'label' => __( 'Postcode / ZIP', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
                'billing_phone' => array(
                    'label' => __( 'Phone', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide' ),
                ),
                'billing_email' => array(
                    'label' => __( 'Email Address', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide' ),
                ),
            ),
            'shipping' => array(
                'shipping_first_name' => array(
                    'label' => __( 'First Name', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-first' ),
                ),
                'shipping_last_name' => array(
                    'label' => __( 'Last Name', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-last' ),
                ),
                'shipping_company' => array(
                    'label' => __( 'Company Name', 'woocommerce' ),
                    'required' => false,
                    'class' => array( 'form-row-wide' ),
                ),
                'shipping_country' => array(
                    'label' => __( 'Country', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
                ),
                'shipping_address_1' => array(
                    'label' => __( 'Street Address', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
                'shipping_address_2' => array(
                    'label' => __( 'Apartment, suite, unit etc. (optional)', 'woocommerce' ),
                    'required' => false,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
                'shipping_city' => array(
                    'label' => __( 'Town / City', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
                'shipping_state' => array(
                    'label' => __( 'State / County', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
                'shipping_postcode' => array(
                    'label' => __( 'Postcode / ZIP', 'woocommerce' ),
                    'required' => true,
                    'class' => array( 'form-row-wide', 'address-field' ),
                ),
            ),
            'order' => array(
                'order_comments' => array(
                    'label' => __( 'Order Notes', 'woocommerce' ),
                    'required' => false,
                    'class' => array( 'notes' ),
                ),
            ),
        );
    }

    public function ajax_reorder_fields() {
        check_ajax_referer( 'sk_cfe_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        $section = sanitize_text_field( $_POST['section'] );
        $field_order = array_map( 'sanitize_text_field', $_POST['field_order'] );

        $settings = get_option( 'sk_cfe_default_fields_settings', array() );
        
        if ( ! isset( $settings[$section] ) ) {
            $settings[$section] = array();
        }

        // Update order for each field based on new position
        foreach ( $field_order as $index => $field_key ) {
            if ( ! isset( $settings[$section][$field_key] ) ) {
                $settings[$section][$field_key] = array();
            }
            $settings[$section][$field_key]['order'] = $index + 1; // Start from 1
        }

        // Debug: Log what we're saving
        error_log('SK CFE: Saving settings for section ' . $section . ': ' . print_r($settings[$section], true));

        update_option( 'sk_cfe_default_fields_settings', $settings );

        // Debug: Verify the save
        $saved_settings = get_option( 'sk_cfe_default_fields_settings', array() );
        error_log('SK CFE: Retrieved settings for section ' . $section . ': ' . print_r($saved_settings[$section], true));

        wp_send_json_success( array( 'message' => __( 'Field order updated successfully!', 'sk-checkout-field-editor' ) ) );
    }

    public function ajax_get_custom_field() {
        check_ajax_referer( 'sk_cfe_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        $field_id = sanitize_text_field( $_POST['field_id'] );
        $custom_fields = get_option( 'sk_cfe_custom_fields', array() );

        if ( ! isset( $custom_fields[$field_id] ) ) {
            wp_send_json_error( array( 'message' => __( 'Field not found.', 'sk-checkout-field-editor' ) ) );
        }

        wp_send_json_success( array(
            'field_id' => $field_id,
            'field_data' => $custom_fields[$field_id]
        ) );
    }

    public function ajax_update_custom_field() {
        check_ajax_referer( 'sk_cfe_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        $field_id = sanitize_text_field( $_POST['field_id'] );
        $custom_fields = get_option( 'sk_cfe_custom_fields', array() );

        if ( ! isset( $custom_fields[$field_id] ) ) {
            wp_send_json_error( array( 'message' => __( 'Field not found.', 'sk-checkout-field-editor' ) ) );
        }

        $field_data = array(
            'name' => sanitize_text_field( $_POST['field_name'] ),
            'label' => sanitize_text_field( $_POST['field_label'] ),
            'type' => sanitize_text_field( $_POST['field_type'] ),
            'position' => sanitize_text_field( $_POST['field_position'] ),
            'required' => isset( $_POST['field_required'] ) ? 'yes' : 'no',
            'placeholder' => sanitize_text_field( $_POST['field_placeholder'] ),
            'options' => isset( $_POST['field_options'] ) ? array_map( 'sanitize_text_field', $_POST['field_options'] ) : array(),
            'default' => sanitize_text_field( $_POST['field_default'] ),
            'class' => sanitize_text_field( $_POST['field_class'] ),
            'show_in_email' => isset( $_POST['show_in_email'] ) ? 'yes' : 'no',
            'show_in_order_details' => isset( $_POST['show_in_order_details'] ) ? 'yes' : 'no',
            'order' => intval( $_POST['field_order'] ),
        );

        $custom_fields[$field_id] = $field_data;
        update_option( 'sk_cfe_custom_fields', $custom_fields );

        wp_send_json_success( array( 'message' => __( 'Field updated successfully!', 'sk-checkout-field-editor' ) ) );
    }
    
    /**
     * Clear WooCommerce checkout fields cache
     */
    public function clear_wc_cache( $old_value, $new_value ) {
        // Clear WooCommerce checkout fields cache
        if ( function_exists( 'wc_delete_shop_order_transients' ) ) {
            wc_delete_shop_order_transients();
        }
        
        // Clear any field-related transients
        if ( function_exists( 'wc_delete_transient' ) ) {
            wc_delete_transient( 'wc_checkout_fields' );
        }
        
        // Clear object cache for checkout fields
        wp_cache_delete( 'checkout_fields', 'woocommerce' );
        
        error_log('SK CFE: Cleared WooCommerce cache after settings update');
    }
}
