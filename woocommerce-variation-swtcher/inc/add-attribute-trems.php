<?php 
/*add here all code related term and attributes */

function my_edit_wc_attribute_my_field() {
    $id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
    $value = $id ? get_option( "wc_attribute_my_field-$id" ) : 'default';
    
    // Dynamic attribute types
    $attribute_types = array(
        'default' => __( 'Default (Text)', 'woocommerce' ),
        'color'   => __( 'Color Swatch', 'woocommerce' ),
        'image'   => __( 'Image Swatch', 'woocommerce' ),
        'button'  => __( 'Button', 'woocommerce' ),
        'radio'   => __( 'Radio Button', 'woocommerce' )
    );
    ?>
<tr class="form-field">
    <th scope="row" valign="top">
        <label for="my-field"><?php _e( 'Display Type', 'woocommerce' ); ?></label>
    </th>
    <td>
        <select name="my_field" id="my-field">
            <?php foreach ( $attribute_types as $type_value => $type_label ) : ?>
            <option value='<?php echo esc_attr( $type_value ); ?>' <?php selected( $value, $type_value ); ?>>
                <?php echo esc_html( $type_label ); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e( 'Select how this attribute should be displayed on the frontend.', 'woocommerce' ); ?>
        </p>
    </td>
</tr>
<?php
}
add_action( 'woocommerce_after_add_attribute_fields', 'my_edit_wc_attribute_my_field' );
add_action( 'woocommerce_after_edit_attribute_fields', 'my_edit_wc_attribute_my_field' );


function my_save_wc_attribute_my_field( $id ) {
if ( is_admin() && isset( $_POST['my_field'] ) ) {
$option = "wc_attribute_my_field-$id";
update_option( $option, sanitize_text_field( $_POST['my_field'] ) );
}
}
add_action( 'woocommerce_attribute_added', 'my_save_wc_attribute_my_field' );
add_action( 'woocommerce_attribute_updated', 'my_save_wc_attribute_my_field' );

add_action( 'woocommerce_attribute_deleted', function ( $id ) {
delete_option( "wc_attribute_my_field-$id" );
} );



// Get current taxonomy dynamically
$taxonomy_type = isset( $_GET["taxonomy"] ) ? sanitize_text_field( $_GET["taxonomy"] ) : '';

// Add custom fields when adding a new term for any attribute
function my_wc_add_custom_fields_to_terms( $taxonomy ) {
    $attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
    $attribute_type = get_option( "wc_attribute_my_field-$attribute_id", 'default' );
    
    // Dynamic field rendering based on attribute type
    switch ( $attribute_type ) {
        case 'color':
            ?>
<div class="form-field">
    <label for="term_color"><?php _e( 'Select Color', 'woocommerce' ); ?></label>
    <input type="color" id="term_color" name="term_color" value="#000000" />
    <p class="description"><?php _e( 'Choose a color for this attribute option.', 'woocommerce' ); ?></p>
</div>
<?php
            break;
            
        case 'image':
            ?>
<div class="form-field">
    <label for="term_image"><?php _e( 'Select Image', 'woocommerce' ); ?></label>
    <input type="hidden" id="term_image" name="term_image" />
    <div id="term_image_preview"
        style="max-width: 100px; height: 100px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
        <span style="color: #999;"><?php _e( 'No image selected', 'woocommerce' ); ?></span>
    </div>
    <button type="button" class="button upload_image_button" data-target="term_image" data-preview="term_image_preview">
        <?php _e( 'Upload Image', 'woocommerce' ); ?>
    </button>
    <button type="button" class="button remove_image_button" data-target="term_image" data-preview="term_image_preview"
        style="display: none; margin-left: 10px;">
        <?php _e( 'Remove Image', 'woocommerce' ); ?>
    </button>
</div>
<?php
            break;
            
        case 'button':
            ?>
<div class="form-field">
    <label for="term_button_text"><?php _e( 'Button Text', 'woocommerce' ); ?></label>
    <input type="text" id="term_button_text" name="term_button_text" value="" />
    <p class="description"><?php _e( 'Custom text for the button display.', 'woocommerce' ); ?></p>
</div>
<?php
            break;
            
        case 'radio':
            ?>
<div class="form-field">
    <label for="term_radio_icon"><?php _e( 'Icon Class (optional)', 'woocommerce' ); ?></label>
    <input type="text" id="term_radio_icon" name="term_radio_icon" value="" placeholder="e.g., dashicons-star-filled" />
    <p class="description"><?php _e( 'Dashicon class for radio button icon.', 'woocommerce' ); ?></p>
</div>
<?php
            break;
            
        default:
            // Default text field - no additional fields needed
            break;
    }
}

// Hook to add custom fields when adding a new term - make it dynamic for all taxonomies
add_action( 'pa_color_add_form_fields', 'my_wc_add_custom_fields_to_terms', 10, 1 );
if ( ! empty( $taxonomy_type ) ) {
    add_action( $taxonomy_type . '_add_form_fields', 'my_wc_add_custom_fields_to_terms', 10, 1 );
}

// Hook to add custom fields when editing an existing term
function my_wc_edit_custom_fields_in_terms( $term, $taxonomy ) {
    $attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
    $attribute_type = get_option( "wc_attribute_my_field-$attribute_id", 'default' );
    $term_id = $term->term_id;

    // Dynamic field rendering based on attribute type for editing
    switch ( $attribute_type ) {
        case 'color':
            $term_color = get_term_meta( $term_id, 'term_color', true );
            ?>
<tr class="form-field">
    <th scope="row" valign="top">
        <label for="term_color"><?php _e( 'Select Color', 'woocommerce' ); ?></label>
    </th>
    <td>
        <input type="color" id="term_color" name="term_color"
            value="<?php echo esc_attr( $term_color ?: '#000000' ); ?>" />
        <p class="description"><?php _e( 'Choose a color for this attribute option.', 'woocommerce' ); ?></p>
    </td>
</tr>
<?php
            break;
            
        case 'image':
            $term_image_id = get_term_meta( $term_id, 'term_image', true );
            $term_image_url = $term_image_id ? wp_get_attachment_url( $term_image_id ) : '';
            ?>
<tr class="form-field">
    <th scope="row" valign="top">
        <label for="term_image"><?php _e( 'Select Image', 'woocommerce' ); ?></label>
    </th>
    <td>
        <input type="hidden" id="term_image" name="term_image" value="<?php echo esc_attr( $term_image_id ); ?>" />
        <div id="term_image_preview"
            style="max-width: 100px; height: 100px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
            <?php if ( $term_image_url ) : ?>
            <img src="<?php echo esc_url( $term_image_url ); ?>" style="max-width: 100%; max-height: 100%;" />
            <?php else : ?>
            <span style="color: #999;"><?php _e( 'No image selected', 'woocommerce' ); ?></span>
            <?php endif; ?>
        </div>
        <button type="button" class="button upload_image_button" data-target="term_image"
            data-preview="term_image_preview">
            <?php _e( 'Upload Image', 'woocommerce' ); ?>
        </button>
        <button type="button" class="button remove_image_button" data-target="term_image"
            data-preview="term_image_preview"
            <?php echo $term_image_id ? '' : 'style="display: none; margin-left: 10px;"'; ?>>
            <?php _e( 'Remove Image', 'woocommerce' ); ?>
        </button>
    </td>
</tr>
<?php
            break;
            
        case 'button':
            $term_button_text = get_term_meta( $term_id, 'term_button_text', true );
            ?>
<tr class="form-field">
    <th scope="row" valign="top">
        <label for="term_button_text"><?php _e( 'Button Text', 'woocommerce' ); ?></label>
    </th>
    <td>
        <input type="text" id="term_button_text" name="term_button_text"
            value="<?php echo esc_attr( $term_button_text ); ?>" />
        <p class="description"><?php _e( 'Custom text for the button display.', 'woocommerce' ); ?></p>
    </td>
</tr>
<?php
            break;
            
        case 'radio':
            $term_radio_icon = get_term_meta( $term_id, 'term_radio_icon', true );
            ?>
<tr class="form-field">
    <th scope="row" valign="top">
        <label for="term_radio_icon"><?php _e( 'Icon Class (optional)', 'woocommerce' ); ?></label>
    </th>
    <td>
        <input type="text" id="term_radio_icon" name="term_radio_icon"
            value="<?php echo esc_attr( $term_radio_icon ); ?>" placeholder="e.g., dashicons-star-filled" />
        <p class="description"><?php _e( 'Dashicon class for radio button icon.', 'woocommerce' ); ?></p>
    </td>
</tr>
<?php
            break;
            
        default:
            // Default text field - no additional fields needed
            break;
    }
}
// Hook to add custom fields when editing a term - make it dynamic for all taxonomies
if ( ! empty( $taxonomy_type ) ) {
    add_action( $taxonomy_type . '_edit_form_fields', 'my_wc_edit_custom_fields_in_terms', 10, 2 );
}

// Save custom fields when the term is created or edited
function my_wc_save_custom_fields_in_terms( $term_id, $tt_id, $taxonomy ) {
    $attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
    $attribute_type = get_option( "wc_attribute_my_field-$attribute_id", 'default' );

    // Dynamic saving based on attribute type
    switch ( $attribute_type ) {
        case 'color':
            if ( isset( $_POST['term_color'] ) ) {
                update_term_meta( $term_id, 'term_color', sanitize_hex_color( $_POST['term_color'] ) );
            }
            break;
            
        case 'image':
            if ( isset( $_POST['term_image'] ) ) {
                update_term_meta( $term_id, 'term_image', absint( $_POST['term_image'] ) );
            }
            break;
            
        case 'button':
            if ( isset( $_POST['term_button_text'] ) ) {
                update_term_meta( $term_id, 'term_button_text', sanitize_text_field( $_POST['term_button_text'] ) );
            }
            break;
            
        case 'radio':
            if ( isset( $_POST['term_radio_icon'] ) ) {
                update_term_meta( $term_id, 'term_radio_icon', sanitize_text_field( $_POST['term_radio_icon'] ) );
            }
            break;
            
        default:
            // Default text field - no additional meta to save
            break;
    }
}
add_action( 'created_term', 'my_wc_save_custom_fields_in_terms', 10, 3 );
add_action( 'edited_term', 'my_wc_save_custom_fields_in_terms', 10, 3 );

// Delete the custom fields when a term is deleted
function my_wc_delete_custom_fields_in_terms( $term_id, $tt_id, $taxonomy, $deleted_term ) {
    delete_term_meta( $term_id, 'term_color' );
    delete_term_meta( $term_id, 'term_image' );
    delete_term_meta( $term_id, 'term_button_text' );
    delete_term_meta( $term_id, 'term_radio_icon' );
}
add_action( 'delete_term', 'my_wc_delete_custom_fields_in_terms', 10, 4 );

// Add JavaScript for media uploader
function my_wc_media_uploader_script() {
    $screen = get_current_screen();
    if ( $screen && 'edit-tags' === $screen->base ) {
        ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    var mediaFrame;

    $('.upload_image_button').on('click', function(e) {
        e.preventDefault();

        var target = $(this).data('target');
        var preview = $(this).data('preview');

        if (mediaFrame) {
            mediaFrame.open();
            return;
        }

        mediaFrame = wp.media({
            title: '<?php _e( 'Select Image', 'woocommerce' ); ?>',
            button: {
                text: '<?php _e( 'Use Image', 'woocommerce' ); ?>'
            },
            multiple: false
        }).on('select', function() {
            var attachment = mediaFrame.state().get('selection').first().toJSON();
            $('#' + target).val(attachment.id);
            $('#' + preview).html('<img src="' + attachment.url +
                '" style="max-width: 100%; max-height: 100%;" />');
            $('.remove_image_button[data-target="' + target + '"]').show();
        }).open();
    });

    $('.remove_image_button').on('click', function(e) {
        e.preventDefault();

        var target = $(this).data('target');
        var preview = $(this).data('preview');

        $('#' + target).val('');
        $('#' + preview).html(
            '<span style="color: #999;"><?php _e( 'No image selected', 'woocommerce' ); ?></span>');
        $(this).hide();
    });
});
</script>
<?php
    }
}
add_action( 'admin_footer', 'my_wc_media_uploader_script' );