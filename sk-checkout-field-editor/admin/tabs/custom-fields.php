<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$admin = new SK_CFE_Admin();
$custom_fields = get_option( 'sk_cfe_custom_fields', array() );

// Handle form submissions
if ( isset( $_POST['sk_cfe_add_custom_field'] ) && check_admin_referer( 'sk_cfe_add_custom_field' ) ) {
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
    
    $field_id = sanitize_title( $_POST['field_name'] );
    $custom_fields[$field_id] = $field_data;
    
    update_option( 'sk_cfe_custom_fields', $custom_fields );
    echo '<div class="notice notice-success"><p>' . __( 'Custom field added successfully!', 'sk-checkout-field-editor' ) . '</p></div>';
}

if ( isset( $_POST['sk_cfe_update_custom_field'] ) && check_admin_referer( 'sk_cfe_update_custom_field' ) ) {
    $field_id = sanitize_text_field( $_POST['field_id'] );
    
    if ( isset( $custom_fields[$field_id] ) ) {
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
        echo '<div class="notice notice-success"><p>' . __( 'Custom field updated successfully!', 'sk-checkout-field-editor' ) . '</p></div>';
        
        // Redirect to clear edit mode
        echo '<script>window.location.href = "?page=sk-cfe-settings&tab=custom-fields";</script>';
    }
}

if ( isset( $_POST['sk_cfe_delete_field'] ) && check_admin_referer( 'sk_cfe_delete_field' ) ) {
    $field_id = sanitize_text_field( $_POST['field_id'] );
    if ( isset( $custom_fields[$field_id] ) ) {
        unset( $custom_fields[$field_id] );
        update_option( 'sk_cfe_custom_fields', $custom_fields );
        echo '<div class="notice notice-success"><p>' . __( 'Field deleted successfully!', 'sk-checkout-field-editor' ) . '</p></div>';
    }
}

// Handle edit mode
$editing_field = null;
if ( isset( $_GET['edit_field'] ) ) {
    $field_id = sanitize_text_field( $_GET['edit_field'] );
    if ( isset( $custom_fields[$field_id] ) ) {
        $editing_field = array(
            'id' => $field_id,
            'data' => $custom_fields[$field_id]
        );
    }
}
?>

<div class="sk-cfe-custom-fields">
    <div class="sk-cfe-add-field-section">
        <h3><?php echo $editing_field ? __( 'Edit Custom Field', 'sk-checkout-field-editor' ) : __( 'Add New Custom Field', 'sk-checkout-field-editor' ); ?></h3>
        
        <?php if ( $editing_field ) : ?>
            <p><a href="?page=sk-cfe-settings&tab=custom-fields" class="button"><?php _e( '← Back to Add New Field', 'sk-checkout-field-editor' ); ?></a></p>
        <?php endif; ?>
        
        <form method="post" action="" class="sk-cfe-add-field-form">
            <?php 
            if ( $editing_field ) {
                wp_nonce_field( 'sk_cfe_update_custom_field' );
                echo '<input type="hidden" name="field_id" value="' . esc_attr( $editing_field['id'] ) . '">';
            } else {
                wp_nonce_field( 'sk_cfe_add_custom_field' );
            }
            ?>
            
            <div class="sk-cfe-form-grid">
                <div class="sk-cfe-form-group">
                    <label for="field_name"><?php _e( 'Field Name (ID):', 'sk-checkout-field-editor' ); ?> <span class="required">*</span></label>
                    <input type="text" id="field_name" name="field_name" class="regular-text" required <?php echo $editing_field ? 'readonly' : ''; ?> value="<?php echo $editing_field ? esc_attr( $editing_field['data']['name'] ) : ''; ?>">
                    <p class="description"><?php _e( 'Unique identifier for the field (no spaces, use lowercase and underscores).', 'sk-checkout-field-editor' ); ?></p>
                </div>
                
                <div class="sk-cfe-form-group">
                    <label for="field_label"><?php _e( 'Field Label:', 'sk-checkout-field-editor' ); ?> <span class="required">*</span></label>
                    <input type="text" id="field_label" name="field_label" class="regular-text" required value="<?php echo $editing_field ? esc_attr( $editing_field['data']['label'] ) : ''; ?>">
                </div>
                
                <div class="sk-cfe-form-group">
                    <label for="field_type"><?php _e( 'Field Type:', 'sk-checkout-field-editor' ); ?> <span class="required">*</span></label>
                    <select id="field_type" name="field_type" class="regular-text" required>
                        <option value="text" <?php echo $editing_field && $editing_field['data']['type'] === 'text' ? 'selected' : ''; ?>><?php _e( 'Text', 'sk-checkout-field-editor' ); ?></option>
                        <option value="email" <?php echo $editing_field && $editing_field['data']['type'] === 'email' ? 'selected' : ''; ?>><?php _e( 'Email', 'sk-checkout-field-editor' ); ?></option>
                        <option value="tel" <?php echo $editing_field && $editing_field['data']['type'] === 'tel' ? 'selected' : ''; ?>><?php _e( 'Phone', 'sk-checkout-field-editor' ); ?></option>
                        <option value="number" <?php echo $editing_field && $editing_field['data']['type'] === 'number' ? 'selected' : ''; ?>><?php _e( 'Number', 'sk-checkout-field-editor' ); ?></option>
                        <option value="password" <?php echo $editing_field && $editing_field['data']['type'] === 'password' ? 'selected' : ''; ?>><?php _e( 'Password', 'sk-checkout-field-editor' ); ?></option>
                        <option value="postcode" <?php echo $editing_field && $editing_field['data']['type'] === 'postcode' ? 'selected' : ''; ?>><?php _e( 'Postcode', 'sk-checkout-field-editor' ); ?></option>
                        <option value="textarea" <?php echo $editing_field && $editing_field['data']['type'] === 'textarea' ? 'selected' : ''; ?>><?php _e( 'Textarea', 'sk-checkout-field-editor' ); ?></option>
                        <option value="select" <?php echo $editing_field && $editing_field['data']['type'] === 'select' ? 'selected' : ''; ?>><?php _e( 'Select Dropdown', 'sk-checkout-field-editor' ); ?></option>
                        <option value="radio" <?php echo $editing_field && $editing_field['data']['type'] === 'radio' ? 'selected' : ''; ?>><?php _e( 'Radio Buttons', 'sk-checkout-field-editor' ); ?></option>
                        <option value="checkbox" <?php echo $editing_field && $editing_field['data']['type'] === 'checkbox' ? 'selected' : ''; ?>><?php _e( 'Checkbox', 'sk-checkout-field-editor' ); ?></option>
                        <option value="date" <?php echo $editing_field && $editing_field['data']['type'] === 'date' ? 'selected' : ''; ?>><?php _e( 'Date', 'sk-checkout-field-editor' ); ?></option>
                        <option value="time" <?php echo $editing_field && $editing_field['data']['type'] === 'time' ? 'selected' : ''; ?>><?php _e( 'Time', 'sk-checkout-field-editor' ); ?></option>
                        <option value="file" <?php echo $editing_field && $editing_field['data']['type'] === 'file' ? 'selected' : ''; ?>><?php _e( 'File Upload', 'sk-checkout-field-editor' ); ?></option>
                        <option value="hidden" <?php echo $editing_field && $editing_field['data']['type'] === 'hidden' ? 'selected' : ''; ?>><?php _e( 'Hidden', 'sk-checkout-field-editor' ); ?></option>
                    </select>
                </div>
                
                <div class="sk-cfe-form-group">
                    <label for="field_position"><?php _e( 'Position:', 'sk-checkout-field-editor' ); ?> <span class="required">*</span></label>
                    <select id="field_position" name="field_position" class="regular-text" required>
                        <option value="billing" <?php echo $editing_field && $editing_field['data']['position'] === 'billing' ? 'selected' : ''; ?>><?php _e( 'Billing Section', 'sk-checkout-field-editor' ); ?></option>
                        <option value="shipping" <?php echo $editing_field && $editing_field['data']['position'] === 'shipping' ? 'selected' : ''; ?>><?php _e( 'Shipping Section', 'sk-checkout-field-editor' ); ?></option>
                        <option value="additional" <?php echo $editing_field && $editing_field['data']['position'] === 'additional' ? 'selected' : ''; ?>><?php _e( 'After Additional Notes', 'sk-checkout-field-editor' ); ?></option>
                    </select>
                </div>
                
                <div class="sk-cfe-form-group">
                    <label for="field_placeholder"><?php _e( 'Placeholder:', 'sk-checkout-field-editor' ); ?></label>
                    <input type="text" id="field_placeholder" name="field_placeholder" class="regular-text" value="<?php echo $editing_field ? esc_attr( $editing_field['data']['placeholder'] ) : ''; ?>">
                </div>
                
                <div class="sk-cfe-form-group">
                    <label for="field_default"><?php _e( 'Default Value:', 'sk-checkout-field-editor' ); ?></label>
                    <input type="text" id="field_default" name="field_default" class="regular-text" value="<?php echo $editing_field ? esc_attr( $editing_field['data']['default'] ) : ''; ?>">
                </div>
                
                <div class="sk-cfe-form-group">
                    <label for="field_class"><?php _e( 'CSS Class:', 'sk-checkout-field-editor' ); ?></label>
                    <input type="text" id="field_class" name="field_class" class="regular-text" value="<?php echo $editing_field ? esc_attr( $editing_field['data']['class'] ) : ''; ?>">
                </div>
                
                <div class="sk-cfe-form-group">
                    <label for="field_order"><?php _e( 'Order:', 'sk-checkout-field-editor' ); ?></label>
                    <input type="number" id="field_order" name="field_order" class="small-text" value="<?php echo $editing_field ? esc_attr( $editing_field['data']['order'] ) : '10'; ?>" min="0">
                </div>
                
                <div class="sk-cfe-form-group sk-cfe-options-group" <?php echo ( $editing_field && in_array( $editing_field['data']['type'], array( 'select', 'radio' ) ) ) ? '' : 'style="display: none;"'; ?>>
                    <label><?php _e( 'Options (for select/radio):', 'sk-checkout-field-editor' ); ?></label>
                    <div id="field-options-container">
                        <?php 
                        $options = $editing_field ? $editing_field['data']['options'] : array();
                        if ( empty( $options ) ) {
                            $options = array( '' );
                        }
                        foreach ( $options as $index => $option ) :
                        ?>
                            <div class="sk-cfe-option-row">
                                <input type="text" name="field_options[]" placeholder="<?php printf( __( 'Option %d', 'sk-checkout-field-editor' ), $index + 1 ); ?>" class="regular-text" value="<?php echo esc_attr( $option ); ?>">
                                <button type="button" class="button sk-cfe-remove-option">-</button>
                            </div>
                        <?php endforeach; ?>
                        <div class="sk-cfe-option-row">
                            <button type="button" class="button sk-cfe-add-option">+ <?php _e( 'Add Option', 'sk-checkout-field-editor' ); ?></button>
                        </div>
                    </div>
                </div>
                
                <div class="sk-cfe-form-group">
                    <label>
                        <input type="checkbox" name="field_required" <?php echo $editing_field && $editing_field['data']['required'] === 'yes' ? 'checked' : ''; ?>>
                        <?php _e( 'Required Field', 'sk-checkout-field-editor' ); ?>
                    </label>
                </div>
                
                <div class="sk-cfe-form-group">
                    <label>
                        <input type="checkbox" name="show_in_email" <?php echo $editing_field && $editing_field['data']['show_in_email'] === 'yes' ? 'checked' : ''; ?>>
                        <?php _e( 'Show in Email', 'sk-checkout-field-editor' ); ?>
                    </label>
                </div>
                
                <div class="sk-cfe-form-group">
                    <label>
                        <input type="checkbox" name="show_in_order_details" <?php echo $editing_field && $editing_field['data']['show_in_order_details'] === 'yes' ? 'checked' : ''; ?>>
                        <?php _e( 'Show in Order Details', 'sk-checkout-field-editor' ); ?>
                    </label>
                </div>
            </div>
            
            <div class="sk-cfe-actions">
                <button type="submit" name="<?php echo $editing_field ? 'sk_cfe_update_custom_field' : 'sk_cfe_add_custom_field'; ?>" class="button button-primary">
                    <?php echo $editing_field ? __( 'Update Field', 'sk-checkout-field-editor' ) : __( 'Add Field', 'sk-checkout-field-editor' ); ?>
                </button>
                <?php if ( $editing_field ) : ?>
                    <a href="?page=sk-cfe-settings&tab=custom-fields" class="button"><?php _e( 'Cancel', 'sk-checkout-field-editor' ); ?></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="sk-cfe-existing-fields">
        <h3><?php _e( 'Existing Custom Fields', 'sk-checkout-field-editor' ); ?></h3>
        
        <?php if ( empty( $custom_fields ) ) : ?>
            <p><?php _e( 'No custom fields created yet.', 'sk-checkout-field-editor' ); ?></p>
        <?php else : ?>
            <div class="sk-cfe-fields-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'Field Name', 'sk-checkout-field-editor' ); ?></th>
                            <th><?php _e( 'Label', 'sk-checkout-field-editor' ); ?></th>
                            <th><?php _e( 'Type', 'sk-checkout-field-editor' ); ?></th>
                            <th><?php _e( 'Position', 'sk-checkout-field-editor' ); ?></th>
                            <th><?php _e( 'Required', 'sk-checkout-field-editor' ); ?></th>
                            <th><?php _e( 'Actions', 'sk-checkout-field-editor' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $custom_fields as $field_id => $field ) : ?>
                            <tr>
                                <td><?php echo esc_html( $field['name'] ); ?></td>
                                <td><?php echo esc_html( $field['label'] ); ?></td>
                                <td><?php echo esc_html( $field['type'] ); ?></td>
                                <td><?php echo esc_html( ucfirst( $field['position'] ) ); ?></td>
                                <td><?php echo $field['required'] === 'yes' ? __( 'Yes', 'sk-checkout-field-editor' ) : __( 'No', 'sk-checkout-field-editor' ); ?></td>
                                <td>
                                    <a href="?page=sk-cfe-settings&tab=custom-fields&edit_field=<?php echo esc_attr( $field_id ); ?>" class="button button-small">
                                        <?php _e( 'Edit', 'sk-checkout-field-editor' ); ?>
                                    </a>
                                    <form method="post" action="" style="display: inline;">
                                        <?php wp_nonce_field( 'sk_cfe_delete_field' ); ?>
                                        <input type="hidden" name="field_id" value="<?php echo esc_attr( $field_id ); ?>">
                                        <button type="submit" name="sk_cfe_delete_field" class="button button-small" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this field?', 'sk-checkout-field-editor' ) ); ?>')">
                                            <?php _e( 'Delete', 'sk-checkout-field-editor' ); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
