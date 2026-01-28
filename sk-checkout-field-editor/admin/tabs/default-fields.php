<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$admin = new SK_CFE_Admin();
$default_fields = $admin->get_default_woocommerce_fields();
$saved_settings = get_option( 'sk_cfe_default_fields_settings', array() );

// Debug: Log the current settings
error_log('SK CFE: Current saved settings: ' . print_r($saved_settings, true));

// Process form submission
if ( isset( $_POST['sk_cfe_save_default_fields'] ) && check_admin_referer( 'sk_cfe_save_default_fields' ) ) {
    $settings = array();
    
    foreach ( $default_fields as $section => $fields ) {
        foreach ( $fields as $field_key => $field_data ) {
            $field_key_safe = str_replace( array( '[', ']' ), array( '_', '_' ), $field_key );
            
            $settings[$section][$field_key] = array(
                'enabled' => isset( $_POST['sk_cfe_field_' . $field_key_safe . '_enabled'] ) ? 'yes' : 'no',
                'label' => sanitize_text_field( $_POST['sk_cfe_field_' . $field_key_safe . '_label'] ),
                'required' => isset( $_POST['sk_cfe_field_' . $field_key_safe . '_required'] ) ? 'yes' : 'no',
                'order' => intval( $_POST['sk_cfe_field_' . $field_key_safe . '_order'] ),
            );
        }
    }
    
    update_option( 'sk_cfe_default_fields_settings', $settings );
    echo '<div class="notice notice-success"><p>' . __( 'Settings saved successfully!', 'sk-checkout-field-editor' ) . '</p></div>';
}
?>

<div class="sk-cfe-default-fields">
    <form method="post" action="">
        <?php wp_nonce_field( 'sk_cfe_save_default_fields' ); ?>
        
        <div class="sk-cfe-sections">
            <?php foreach ( $default_fields as $section => $fields ) : ?>
                <div class="sk-cfe-section">
                    <h3><?php echo esc_html( ucfirst( $section ) ); ?> <?php _e( 'Fields', 'sk-checkout-field-editor' ); ?></h3>
                    
                    <div class="sk-cfe-fields-list" data-section="<?php echo esc_attr( $section ); ?>">
                        <?php 
                        $section_settings = isset( $saved_settings[$section] ) ? $saved_settings[$section] : array();
                        
                        // Sort fields by order if settings exist
                        $sorted_fields = $fields;
                        if ( ! empty( $section_settings ) ) {
                            // Create array of field keys with their order values
                            $field_orders = array();
                            foreach ( $fields as $field_key => $field_data ) {
                                $order = isset( $section_settings[$field_key]['order'] ) ? intval( $section_settings[$field_key]['order'] ) : 999;
                                $field_orders[$field_key] = $order;
                            }
                            
                            // Sort by order value
                            asort( $field_orders );
                            
                            // Reorder the fields array based on sorted keys
                            $sorted_fields = array();
                            foreach ( $field_orders as $field_key => $order ) {
                                $sorted_fields[$field_key] = $fields[$field_key];
                            }
                        }
                        
                        foreach ( $sorted_fields as $field_key => $field_data ) :
                            $field_key_safe = str_replace( array( '[', ']' ), array( '_', '_' ), $field_key );
                            $field_settings = isset( $section_settings[$field_key] ) ? $section_settings[$field_key] : array();
                            $enabled = isset( $field_settings['enabled'] ) ? $field_settings['enabled'] : 'yes';
                            $label = isset( $field_settings['label'] ) ? $field_settings['label'] : $field_data['label'];
                            $required = isset( $field_settings['required'] ) ? $field_settings['required'] : ( isset( $field_data['required'] ) ? 'yes' : 'no' );
                            $order = isset( $field_settings['order'] ) ? $field_settings['order'] : 10;
                        ?>
                            <div class="sk-cfe-field-item" data-field="<?php echo esc_attr( $field_key ); ?>">
                                <div class="sk-cfe-field-header">
                                    <span class="sk-cfe-field-handle dashicons dashicons-menu"></span>
                                    <span class="sk-cfe-field-name"><?php echo esc_html( $field_key ); ?></span>
                                    <span class="sk-cfe-field-order-display">#<span class="order-number"><?php echo esc_html( $order ); ?></span></span>
                                    <div class="sk-cfe-field-actions">
                                        <label class="sk-cfe-toggle">
                                            <input type="checkbox" name="sk_cfe_field_<?php echo $field_key_safe; ?>_enabled" <?php checked( $enabled, 'yes' ); ?>>
                                            <span class="sk-cfe-toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="sk-cfe-field-details">
                                    <div class="sk-cfe-field-row">
                                        <label>
                                            <?php _e( 'Label:', 'sk-checkout-field-editor' ); ?>
                                            <input type="text" name="sk_cfe_field_<?php echo $field_key_safe; ?>_label" value="<?php echo esc_attr( $label ); ?>" class="regular-text">
                                        </label>
                                        
                                        <label>
                                            <?php _e( 'Required:', 'sk-checkout-field-editor' ); ?>
                                            <input type="checkbox" name="sk_cfe_field_<?php echo $field_key_safe; ?>_required" <?php checked( $required, 'yes' ); ?>>
                                        </label>
                                        
                                        <label class="sk-cfe-order-input">
                                            <?php _e( 'Order:', 'sk-checkout-field-editor' ); ?>
                                            <input type="number" name="sk_cfe_field_<?php echo $field_key_safe; ?>_order" value="<?php echo esc_attr( $order ); ?>" class="small-text sk-cfe-order-field" min="0">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="sk-cfe-actions">
            <button type="submit" name="sk_cfe_save_default_fields" class="button button-primary">
                <?php _e( 'Save Changes', 'sk-checkout-field-editor' ); ?>
            </button>
        </div>
    </form>
</div>
