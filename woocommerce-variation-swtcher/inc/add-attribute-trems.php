<?php 
/*add here all code related term and attributes */

function my_edit_wc_attribute_my_field() {
$id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
$value = $id ? get_option( "wc_attribute_my_field-$id" ) : '';
?>
<tr class="form-field">
<th scope="row" valign="top">
<label for="my-field">My Field</label>
</th>
<td>
<select name="my_field" id="my-field">
<option value='<?php echo esc_attr( $value ); ?>'>Selected : <?php echo esc_attr( $value ); ?> </option>
<option value='color'>Color</option>
<option value='image'>Image</option>
</select>
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



/**
 * Function for `woocommerce_product_attribute_term_name` filter-hook.
 */

// Add custom fields when adding a new term for any attribute
 $taxonomy_type = $_GET["taxonomy"];
// Add custom fields when adding a new term for any attribute
function my_wc_add_custom_fields_to_terms( $taxonomy ) {
    $attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
    $attribute_type = get_option( "wc_attribute_my_field-$attribute_id" );
    
    if ( 'color' === $attribute_type ) {
        ?>
        <div class="form-field">
            <label for="term_color"><?php _e( 'Select Color', 'woocommerce' ); ?></label>
            <input type="color" id="term_color" name="term_color" value="" />
        </div>
        <?php
    } elseif ( 'image' === $attribute_type ) {
        ?>
        <div class="form-field">
            <label for="term_image"><?php _e( 'Select Image', 'woocommerce' ); ?></label>
            <input type="hidden" id="term_image" name="term_image" />
            <img id="term_image_preview" src="" style="max-width: 100px; display: none;" />
            <button class="button upload_image_button"><?php _e( 'Upload Image', 'woocommerce' ); ?></button>
            <button class="button remove_image_button" style="display: none;"><?php _e( 'Remove Image', 'woocommerce' ); ?></button>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                var frame;
                $('.upload_image_button').on('click', function(e) {
                    e.preventDefault();
                    if (frame) {
                        frame.open();
                        return;
                    }
                    frame = wp.media({
                        title: 'Select Image',
                        button: { text: 'Use Image' },
                        multiple: false
                    }).on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#term_image').val(attachment.id);
                        $('#term_image_preview').attr('src', attachment.url).show();
                        $('.remove_image_button').show();
                    }).open();
                });

                $('.remove_image_button').on('click', function(e) {
                    e.preventDefault();
                    $('#term_image').val('');
                    $('#term_image_preview').attr('src', '').hide();
                    $(this).hide();
                });
            });
        </script>
        <?php
    }
}

// Hook to add custom fields when adding a new term
add_action( 'pa_color_add_form_fields', 'my_wc_add_custom_fields_to_terms', 10, 1 );
add_action($taxonomy_type.'_add_form_fields', 'my_wc_add_custom_fields_to_terms', 10, 1 );

// Hook to add custom fields when editing an existing term
function my_wc_edit_custom_fields_in_terms( $term, $taxonomy ) {
    $attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
    $attribute_type = get_option( "wc_attribute_my_field-$attribute_id" );
    $term_id = $term->term_id;

    if ( 'color' === $attribute_type ) {
        $term_color = get_term_meta( $term_id, 'term_color', true );
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_color"><?php _e( 'Select Color', 'woocommerce' ); ?></label></th>
            <td>
                <input type="color" id="term_color" name="term_color" value="<?php echo esc_attr( $term_color ); ?>" />
            </td>
        </tr>
        <?php
    } elseif ( 'image' === $attribute_type ) {
        $term_image_id = get_term_meta( $term_id, 'term_image', true );
        $term_image_url = $term_image_id ? wp_get_attachment_url( $term_image_id ) : '';
		echo $term_image_url;
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_image"><?php _e( 'Select Image', 'woocommerce' ); ?></label></th>
            <td>
                <button id="media-picker-button" class="button">Select Image</button>
				<input type="hidden" id="media_id" name="term_image" value="" />
				<div id="media-preview"> <?php if(!empty( $term_image_url)){ echo "<img src='". $term_image_url."' style='max-width: 100px' >"; } ?> </div>
				
            </td>
	</tr>
        <script type="text/javascript">
           jQuery(document).ready(function($) {
   			 var mediaFrame;

			$('#media-picker-button').on('click', function(e) {
				e.preventDefault();

				// Create or reuse the media frame
				if (mediaFrame) {
					mediaFrame.open();
					return;
				}

				mediaFrame = wp.media({
					title: 'Select or Upload Media',
					button: {
						text: 'Use this image'
					},
					multiple: false // Set to true to allow multiple selections
				});

				// When an image is selected, run a callback function
				mediaFrame.on('select', function() {
					var attachment = mediaFrame.state().get('selection').first().toJSON();
					$('#media_id').val(attachment.id);
					$('#media-preview').html('<img src="' + attachment.url + '" style="max-width: 100px;"/>');
				});

				// Open the media library frame
				mediaFrame.open();
			});
		});


        </script>
        <?php
    }
}
// add_action( 'pa_color_edit_form_fields', 'my_wc_edit_custom_fields_in_terms', 10, 2 );
add_action( $taxonomy_type.'_edit_form_fields', 'my_wc_edit_custom_fields_in_terms', 10, 2 );

// Save custom fields when the term is created or edited
function my_wc_save_custom_fields_in_terms( $term_id, $tt_id, $taxonomy ) {
    $attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
    $attribute_type = get_option( "wc_attribute_my_field-$attribute_id" );

    if ( 'color' === $attribute_type && isset( $_POST['term_color'] ) ) {
        update_term_meta( $term_id, 'term_color', sanitize_hex_color( $_POST['term_color'] ) );
    } elseif ( 'image' === $attribute_type && isset( $_POST['term_image'] ) ) {
        update_term_meta( $term_id, 'term_image', absint( $_POST['term_image'] ) );
    }
}
add_action( 'created_term', 'my_wc_save_custom_fields_in_terms', 10, 3 );
add_action( 'edited_term', 'my_wc_save_custom_fields_in_terms', 10, 3 );

// Delete the custom fields when a term is deleted
function my_wc_delete_custom_fields_in_terms( $term_id, $tt_id, $taxonomy, $deleted_term ) {
    delete_term_meta( $term_id, 'term_color' );
    delete_term_meta( $term_id, 'term_image' );
}
add_action( 'delete_term', 'my_wc_delete_custom_fields_in_terms', 10, 4 );
