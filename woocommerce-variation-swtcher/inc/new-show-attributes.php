<?php 

function custom_woocommerce_template_paths() {
    global $product;

    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        return; // Ensure we only run this for variable products
    }else{

    // Get variation attributes
    $attributes = $product->get_variation_attributes();
    $available_variations = $product->get_available_variations();
   

    foreach ( $attributes as $attribute_name => $options ) {
        // Attribute taxonomy name (removing 'pa_' prefix if present)
        $attribute_taxonomy = wc_attribute_taxonomy_name( str_replace( 'pa_', '', $attribute_name ) );
		$term_id = wc_attribute_taxonomy_id_by_name($attribute_taxonomy);
    	$custom_field_value = get_option( "wc_attribute_my_field-$term_id" );
        // Display the attribute name
        echo '<strong>Choose: ' . wc_attribute_label( $attribute_taxonomy ) . '</strong> <br>';
		
		switch ($custom_field_value) {
			case 'color':
		echo "<div class='pa_color wrap' id='cus_pa_color'>";
		foreach ( $attributes as $attribute_name => $options ) {
				// Attribute taxonomy name (removing 'pa_' prefix if present)
				$attribute_taxonomy = wc_attribute_taxonomy_name( str_replace( 'pa_', '', $attribute_name ) );

				foreach ( $options as $option ) {
					// Get the term by slug
					$term = get_term_by( 'slug', $option, $attribute_name );

					if ( $term ) {
						// Get the term ID
						$term_id = $term->term_id;
			   		   $custom_field_value = get_option( "wc_attribute_my_field-$term_id" );
						// Get the term image
						$term_value = get_term_meta( $term_id, 'term_color', true );
				     	if ( $term_value ) {
							echo '<div data-val="'.esc_attr( $term->slug ).'" title ="' . esc_attr( $term->name ) . '" class="swatch color-swatch" style="width:50px; height:50px; background-color:'.$term_value.'"  onclick="dynamicSwatchSelector(this, \'#' . esc_attr( $attribute_taxonomy ) . '\', \'slected\')" > </div>';
						}
					}
				}
			}
		echo "</div>";
		
		break;
					
		case 'image':
		echo "<div class='pa_image wrap' id='cus_pa_image'>";
			foreach ( $attributes as $attribute_name => $options ) {
				// Attribute taxonomy name (removing 'pa_' prefix if present)
				$attribute_taxonomy = wc_attribute_taxonomy_name( str_replace( 'pa_', '', $attribute_name ) );

				foreach ( $options as $option ) {
					// Get the term by slug
					$term = get_term_by( 'slug', $option, $attribute_name );

					if ( $term ) {
						// Get the term ID
						$term_id = $term->term_id;
						$custom_field_value = get_option( "wc_attribute_my_field-$term_id" );
						// Get the term image
						$term_image_id = get_term_meta( $term_id, 'term_image', true );
						$term_image_url = $term_image_id ? wp_get_attachment_url( $term_image_id ) : '';
					// Display the term image if it exists
						if ( $term_image_url ) {
							echo '<img src="' . esc_url( $term_image_url ) . '" data-val="'.esc_attr( $term->slug ).'"  title="' . esc_attr( $term->name ) . '" class="swatch img-swatch" alt="' . esc_attr( $term->name ) . '"  onclick="dynamicSwatchSelector(this, \'#' . esc_attr( $attribute_taxonomy ) . '\', \'slected\')" />';
						}
					}
				}
			
			}
		echo "</div>";
		
		  break;
  		default:
				
		echo "<div class='{$attribute_taxonomy} swatch-wrap' id='cu-{$attribute_taxonomy}'>";
			foreach ( $attributes as $attribute_name => $options ) {
				// Attribute taxonomy name (removing 'pa_' prefix if present)
				$attribute_taxonomy = wc_attribute_taxonomy_name( str_replace( 'pa_', '', $attribute_name ) );
					
				foreach ( $options as $option ) {
					$term = get_term_by( 'slug', $option, $attribute_name );
					$term_id = $term->term_id;
					$term_image_id = get_term_meta( $term_id, 'term_image', true );
					$term_value = get_term_meta( $term_id, 'term_color', true );
					
					// Get the term by slug
					if ( !empty($option) && !$term_image_id && !$term_value ) {
						echo '<div data-val="' . esc_attr( $term->slug ) . '" title="' . esc_attr( $term->name ) . '" class="swatch cu-' . esc_attr( $attribute_taxonomy ) . '" 
        onclick="dynamicSwatchSelector(this, \'#' . esc_attr( $attribute_taxonomy ) . '\', \'slected\')">' 
        . esc_attr( $term->name ) . 
    '</div>';

						}
					
				}
			
			}
		echo "</div>";
		
		}
        // Display the custom attribute (my_field)	
		
		
		
    }
	?>
<script>
function dynamicSwatchSelector(clickElement, triggerSelector, activeClass) {
    var currentValue = jQuery(clickElement).attr('data-val');
    jQuery(clickElement).siblings().removeClass(activeClass);
    jQuery(triggerSelector).val(currentValue).trigger('change');
    jQuery(clickElement).addClass(activeClass);
    console.log(currentValue);
}
</script>
<?php 
	}
}
add_action( 'woocommerce_before_variations_form', 'custom_woocommerce_template_paths', 10, 3 );