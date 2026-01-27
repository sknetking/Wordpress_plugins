<?php 
/* Add all code related about show on font end for attributes*/
// version 2
function custom_woocommerce_template_paths() {
    global $product;

    $attributes = $product->get_variation_attributes();
    $available_variations = $product->get_available_variations();
    $attribute_keys = array_keys( $attributes );

    foreach ( $attributes as $attribute_name => $options ) {
        $attribute_taxonomy = wc_attribute_taxonomy_name( $attribute_name );
        $attribute_type = get_term_meta( $attribute_taxonomy, 'attribute_type', true );

        if ( 'pa_color' === $attribute_name ) {
            // Color swatches
			 echo "<h4>Select Color :</h4>";
            
            echo '<div class="color-swatch">';
            foreach ( $options as $option ) {
                echo '<div class="swatch" data-value="' . esc_attr( $option ) . '" style="background-color: ' . esc_attr( $option ) . ';" title="' . esc_attr( $option ) . '" ></div>';
            }
            echo '</div>';
        } elseif ( 'pa_image' === $attribute_name && $attribute_type === 'image' ) {
            // Image swatches
			 echo "<h4>Select Images :</h4>";
            
            echo '<div class="image-swatch">';
            foreach ( $options as $option ) {
               			
                echo '<div class="img-swatch" data-fabric="' . esc_attr( $option ) . '" title="' . esc_attr( $option ) . '" ><img src="/wp-content/uploads/woocommerce-placeholder.png" alt="' . esc_attr( $option ) . '"></div>';
            }
            echo '</div>';
        } else {
            // Handle other attributes if needed
            echo "<h4>Select Images :</h4>";
             echo '<div class="image-swatch">';			
            foreach ( $options as $option ) {    
           echo '<div class="img-swatch" data-fabric="' . esc_attr( $option ) . '" title="' . esc_attr( $option ) . '"><img src="/wp-content/uploads/woocommerce-placeholder.png" alt="' . esc_attr( $option ) . '"></div>';

            }
            echo '</div>';
        }
		?>
<a class="reset_variations" href="#" style="visibility: visible;">Clear</a>
<?php 
    }
}

add_filter( 'woocommerce_before_variations_form', 'custom_woocommerce_template_paths', 10, 3 );