<?php 
/**
 * Dynamic Attribute Display for WooCommerce Variation Gallery
 * Handles all attribute types dynamically on the frontend
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wcvg_dynamic_attribute_display() {
    global $product;

    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        return; // Only run for variable products
    }

    // Get variation attributes
    $attributes = $product->get_variation_attributes();
    $available_variations = $product->get_available_variations();
    
    echo '<div class="wcvg-variation-attributes">';
    
    foreach ( $attributes as $attribute_name => $options ) {
        // Clean attribute name (remove 'pa_' prefix if present)
        $clean_attribute_name = str_replace( 'pa_', '', $attribute_name );
        $attribute_taxonomy = wc_attribute_taxonomy_name( $clean_attribute_name );
        
        // Get attribute ID and type
        $attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
        $attribute_type = get_option( "wc_attribute_my_field-$attribute_id", 'default' );
        
        // Display attribute label
        echo '<div class="wcvg-attribute-group" data-attribute="' . esc_attr( $attribute_name ) . '" data-type="' . esc_attr( $attribute_type ) . '">';
        echo '<h3 class="wcvg-attribute-label">' . wc_attribute_label( $attribute_taxonomy ) . '</h3>';
        
        // Render attribute options based on type
        wcvg_render_attribute_options( $attribute_name, $options, $attribute_type );
        
        echo '</div>';
    }
    
    echo '</div>';
    
    // Add reset variations link
    echo '<a class="reset_variations wcvg-reset" href="#" style="visibility: visible;">' . __( 'Clear selection', 'woocommerce' ) . '</a>';
    
    // Add hidden variation data for JavaScript
    echo '<script type="text/javascript">';
    echo 'var wcvg_variation_data = ' . json_encode( $available_variations ) . ';';
    echo 'var wcvg_attributes = ' . json_encode( $attributes ) . ';';
    echo '</script>';
    
    // Add JavaScript for dynamic handling
    wcvg_add_dynamic_script();
}

function wcvg_render_attribute_options( $attribute_name, $options, $attribute_type ) {
    $attribute_taxonomy = wc_attribute_taxonomy_name( str_replace( 'pa_', '', $attribute_name ) );
    
    echo '<div class="wcvg-swatch-container wcvg-' . esc_attr( $attribute_type ) . '-container">';
    
    foreach ( $options as $option ) {
        if ( empty( $option ) ) continue;
        
        // Get the term by slug
        $term = get_term_by( 'slug', $option, $attribute_name );
        
        if ( ! $term ) continue;
        
        $term_id = $term->term_id;
        $term_name = $term->name;
        $term_slug = $term->slug;
        
        // Render based on attribute type
        switch ( $attribute_type ) {
            case 'color':
                wcvg_render_color_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy );
                break;
                
            case 'image':
                wcvg_render_image_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy );
                break;
                
            case 'button':
                wcvg_render_button_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy );
                break;
                
            case 'radio':
                wcvg_render_radio_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy );
                break;
                
            default:
                wcvg_render_default_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy );
                break;
        }
    }
    
    echo '</div>';
}

function wcvg_render_color_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy ) {
    $term_color = get_term_meta( $term_id, 'term_color', true );
    
    // Try to get matching variation image first
    $variation_image_url = wcvg_get_color_variation_image( $term_name, $term_slug, $attribute_taxonomy );
    
    if ( $variation_image_url ) {
        // Show variation image instead of color swatch
        echo '<div class="wcvg-swatch wcvg-color-swatch wcvg-image-swatch" 
                 data-value="' . esc_attr( $term_slug ) . '" 
                 title="' . esc_attr( $term_name ) . '" 
                 onclick="wcvg_select_swatch(this, \'' . esc_attr( $attribute_taxonomy ) . '\')">
                 <img src="' . esc_url( $variation_image_url ) . '" alt="' . esc_attr( $term_name ) . '" />
              </div>';
    } elseif ( $term_color ) {
        // Fallback to color swatch if no image found
        echo '<div class="wcvg-swatch wcvg-color-swatch" 
                 data-value="' . esc_attr( $term_slug ) . '" 
                 title="' . esc_attr( $term_name ) . '" 
                 style="background-color: ' . esc_attr( $term_color ) . '"
                 onclick="wcvg_select_swatch(this, \'' . esc_attr( $attribute_taxonomy ) . '\')">
              </div>';
    }
}

function wcvg_render_image_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy ) {
    $term_image_id = get_term_meta( $term_id, 'term_image', true );
    $term_image_url = $term_image_id ? wp_get_attachment_url( $term_image_id ) : '';
    
    if ( $term_image_url ) {
        echo '<div class="wcvg-swatch wcvg-image-swatch" 
                 data-value="' . esc_attr( $term_slug ) . '" 
                 title="' . esc_attr( $term_name ) . '"
                 onclick="wcvg_select_swatch(this, \'' . esc_attr( $attribute_taxonomy ) . '\')">
                 <img src="' . esc_url( $term_image_url ) . '" alt="' . esc_attr( $term_name ) . '" />
              </div>';
    }
}

function wcvg_render_button_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy ) {
    $button_text = get_term_meta( $term_id, 'term_button_text', true );
    $display_text = $button_text ? $button_text : $term_name;
    
    echo '<button type="button" 
            class="wcvg-swatch wcvg-button-swatch" 
            data-value="' . esc_attr( $term_slug ) . '" 
            title="' . esc_attr( $term_name ) . '"
            onclick="wcvg_select_swatch(this, \'' . esc_attr( $attribute_taxonomy ) . '\')">
            ' . esc_html( $display_text ) . '
          </button>';
}

function wcvg_render_radio_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy ) {
    $radio_icon = get_term_meta( $term_id, 'term_radio_icon', true );
    $icon_html = '';
    
    if ( $radio_icon && strpos( $radio_icon, 'dashicons' ) !== false ) {
        $icon_html = '<span class="dashicons ' . esc_attr( $radio_icon ) . '"></span>';
    }
    
    echo '<label class="wcvg-swatch wcvg-radio-swatch">
            <input type="radio" 
                   name="wcvg_radio_' . esc_attr( $attribute_taxonomy ) . '" 
                   value="' . esc_attr( $term_slug ) . '" 
                   onchange="wcvg_select_radio(this, \'' . esc_attr( $attribute_taxonomy ) . '\')" />
            <span class="wcvg-radio-label">
                ' . $icon_html . '
                <span class="wcvg-radio-text">' . esc_html( $term_name ) . '</span>
            </span>
          </label>';
}

function wcvg_render_default_swatch( $term_id, $term_name, $term_slug, $attribute_taxonomy ) {
    // Check if this is a size attribute and show abbreviation
    $display_text = wcvg_get_size_abbreviation( $term_name, $attribute_taxonomy );
    
    echo '<div class="wcvg-swatch wcvg-default-swatch" 
             data-value="' . esc_attr( $term_slug ) . '" 
             title="' . esc_attr( $term_name ) . '"
             onclick="wcvg_select_swatch(this, \'' . esc_attr( $attribute_taxonomy ) . '\')">
            ' . esc_html( $display_text ) . '
          </div>';
}

function wcvg_add_dynamic_script() {
    ?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize WC Variation Gallery
    window.wcvg = {
        select_swatch: function(element, attributeSelector) {
            var $element = $(element);
            var value = $element.data('value');
            var $container = $element.closest('.wcvg-swatch-container');

            // Remove active class from siblings
            $container.find('.wcvg-swatch').removeClass('wcvg-active');
            $container.find('.wcvg-radio-swatch input').prop('checked', false);

            // Add active class to selected element
            $element.addClass('wcvg-active');

            // Trigger change on the hidden select
            $(attributeSelector).val(value).trigger('change');

            // Trigger custom event
            $(document).trigger('wcvg_swatch_selected', [attributeSelector, value, element]);
        },

        select_radio: function(radio, attributeSelector) {
            var $radio = $(radio);
            var value = $radio.val();
            var $container = $radio.closest('.wcvg-swatch-container');

            // Remove active class from all swatches
            $container.find('.wcvg-swatch').removeClass('wcvg-active');

            // Add active class to parent label
            $radio.closest('.wcvg-radio-swatch').addClass('wcvg-active');

            // Trigger change on the hidden select
            $(attributeSelector).val(value).trigger('change');

            // Trigger custom event
            $(document).trigger('wcvg_radio_selected', [attributeSelector, value, radio]);
        }
    };

    // Reset variations handler
    $('.wcvg-reset').on('click', function(e) {
        e.preventDefault();
        $('.wcvg-swatch').removeClass('wcvg-active');
        $('.wcvg-radio-swatch input').prop('checked', false);
        $('form.variations_form').trigger('reset_data');
    });

    // Handle variation found event
    $('form.variations_form').on('found_variation', function(event, variation) {
        $(document).trigger('wcvg_variation_found', [variation]);
    });

    // Handle variation not found
    $('form.variations_form').on('variation_not_found', function() {
        $(document).trigger('wcvg_variation_not_found');
    });
});

// Global functions for onclick handlers
function wcvg_select_swatch(element, attributeSelector) {
    window.wcvg.select_swatch(element, attributeSelector);
}

function wcvg_select_radio(radio, attributeSelector) {
    window.wcvg.select_radio(radio, attributeSelector);
}
</script>
<?php
}

// Hook into WooCommerce
add_action( 'woocommerce_before_variations_form', 'wcvg_dynamic_attribute_display', 15 );

/**
 * Get variation image that matches color
 */
function wcvg_get_color_variation_image( $color_name, $color_slug, $attribute_taxonomy ) {
    global $product;
    
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        return false;
    }
    
    $available_variations = $product->get_available_variations();
    
    foreach ( $available_variations as $variation ) {
        // Check multiple possible attribute name formats
        $attribute_key = 'attribute_' . $attribute_taxonomy;
        $attribute_key_pa = 'attribute_pa_' . $attribute_taxonomy;
        
        // Try both attribute key formats
        $variation_value = '';
        if ( isset( $variation['attributes'][ $attribute_key ] ) ) {
            $variation_value = $variation['attributes'][ $attribute_key ];
        } elseif ( isset( $variation['attributes'][ $attribute_key_pa ] ) ) {
            $variation_value = $variation['attributes'][ $attribute_key_pa ];
        }
        
        if ( $variation_value === $color_slug ) {
            // Get variation image
            if ( isset( $variation['image']['url'] ) && ! empty( $variation['image']['url'] ) ) {
                return $variation['image']['url'];
            }
            
            // Fallback to variation object
            $variation_obj = wc_get_product( $variation['variation_id'] );
            if ( $variation_obj && $variation_obj->get_image_id() ) {
                return wp_get_attachment_url( $variation_obj->get_image_id() );
            }
        }
    }
    
    return false;
}

/**
 * Get size abbreviation for display
 */
function wcvg_get_size_abbreviation( $term_name, $attribute_taxonomy ) {
    // Check if attribute is likely a size attribute
    $size_keywords = array('size', 'dimension', 'length', 'width', 'height');
    $attribute_label = wc_attribute_label( $attribute_taxonomy );
    $is_size_attribute = false;
    
    foreach ( $size_keywords as $keyword ) {
        if ( stripos( $attribute_label, $keyword ) !== false ) {
            $is_size_attribute = true;
            break;
        }
    }
    
    // If it's a size attribute, return abbreviation
    if ( $is_size_attribute ) {
        $term_name_lower = strtolower( $term_name );
        
        // Common size abbreviations
        $abbreviations = array(
            'extra small' => 'XS',
            'x-small' => 'XS',
            'small' => 'S',
            'medium' => 'M',
            'large' => 'L',
            'extra large' => 'XL',
            'x-large' => 'XL',
            'xx-large' => 'XXL',
            'xxx-large' => 'XXXL',
            '2xl' => 'XXL',
            '3xl' => 'XXXL',
            '4xl' => 'XXXXL',
            '5xl' => 'XXXXXL'
        );
        
        // Check for exact matches first
        if ( isset( $abbreviations[ $term_name_lower ] ) ) {
            return $abbreviations[ $term_name_lower ];
        }
        
        // Check for partial matches
        foreach ( $abbreviations as $size => $abbr ) {
            if ( stripos( $term_name_lower, $size ) !== false ) {
                return $abbr;
            }
        }
        
        // If no match found, return first letter in uppercase
        return strtoupper( substr( $term_name, 0, 1 ) );
    }
    
    // If not a size attribute, return original name
    return $term_name;
}