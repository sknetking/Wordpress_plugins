<?php
/**
 * WooCommerce Product Filter Shortcode Class
 *
 * @package WooCommerceProductFilter
 */

defined( 'ABSPATH' ) || exit;

class WC_Product_Filter_Shortcode {

    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode( 'product_filter', array( $this, 'render_filter_shortcode' ) );
    }

    /**
     * Render the product filter shortcode
     */
    public function render_filter_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'columns' => 4,  // Default value
            'posts_per_page' => '12',
            'show_price' => 'yes',  // Default: show all filters
            'show_categories' => 'yes',
            'show_tags' => 'yes',
            'show_attributes' => 'yes',
            'show_clear' => 'yes'
        ), $atts, 'product_filter' );

        // Allow shortcode attributes to override admin settings
        if ( isset( $atts['show_price'] ) && in_array( $atts['show_price'], array( 'yes', 'no', 'enabled', 'disabled' ) ) ) {
            $atts['show_price'] = in_array( $atts['show_price'], array( 'yes', 'enabled' ) ) ? 'yes' : 'no';
        }
        if ( isset( $atts['show_categories'] ) && in_array( $atts['show_categories'], array( 'yes', 'no', 'enabled', 'disabled' ) ) ) {
            $atts['show_categories'] = in_array( $atts['show_categories'], array( 'yes', 'enabled' ) ) ? 'yes' : 'no';
        }
        if ( isset( $atts['show_tags'] ) && in_array( $atts['show_tags'], array( 'yes', 'no', 'enabled', 'disabled' ) ) ) {
            $atts['show_tags'] = in_array( $atts['show_tags'], array( 'yes', 'enabled' ) ) ? 'yes' : 'no';
        }
        if ( isset( $atts['show_attributes'] ) && in_array( $atts['show_attributes'], array( 'yes', 'no', 'enabled', 'disabled' ) ) ) {
            $atts['show_attributes'] = in_array( $atts['show_attributes'], array( 'yes', 'enabled' ) ) ? 'yes' : 'no';
        }

        ob_start();
        $this->render_filter_html( $atts );
        return ob_get_clean();
    }

    /**
     * Render filter HTML
     */
    private function render_filter_html( $atts ) {
        ?>
<div class="wc-product-filter-wrapper">
    <div class="wc-filter-sidebar">
        <div class="wc-filter-header">
            <h3><?php echo esc_html( __( 'Filter Products', 'woo-product-filter' ) ); ?></h3>
            <button type="button" class="wc-filter-toggle"
                aria-label="<?php esc_attr_e( 'Toggle filters', 'woo-product-filter' ); ?>">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <form id="wc-product-filter-form" class="wc-filter-form">
            <?php wp_nonce_field( 'wc_filter_nonce', 'filter_nonce' ); ?>

            <?php if ( $atts['show_price'] === 'yes' ) : ?>
            <div class="wc-filter-section wc-filter-price">
                <h4><?php echo esc_html( __( 'Price Range', 'woo-product-filter' ) ); ?></h4>
                <div class="wc-price-inputs">
                    <div class="wc-price-input-group">
                        <label for="min_price"><?php esc_html_e( 'Min', 'woo-product-filter' ); ?></label>
                        <input type="number" id="min_price" name="min_price" placeholder="0" min="0" step="1">
                    </div>
                    <span class="wc-price-separator">-</span>
                    <div class="wc-price-input-group">
                        <label for="max_price"><?php esc_html_e( 'Max', 'woo-product-filter' ); ?></label>
                        <input type="number" id="max_price" name="max_price" placeholder="1000" min="0" step="1">
                    </div>
                </div>
                <div class="wc-price-slider">
                    <input type="range" id="price_range" min="0" max="1000" value="500" class="wc-slider">
                </div>
            </div>
            <?php endif; ?>

            <?php if ( $atts['show_categories'] === 'yes' ) : ?>
            <?php $this->render_category_filters(); ?>
            <?php endif; ?>

            <?php if ( $atts['show_tags'] === 'yes' ) : ?>
            <?php $this->render_tag_filters(); ?>
            <?php endif; ?>

            <?php if ( $atts['show_attributes'] === 'yes' ) : ?>
            <?php $this->render_attribute_filters(); ?>
            <?php endif; ?>

            <?php if ( $atts['show_clear'] === 'yes' ) : ?>
            <div class="wc-filter-actions">
                <button type="button" id="wc-clear-filters" class="wc-btn wc-btn-secondary" style="display: none;">
                    <?php esc_html_e( 'Clear All', 'woo-product-filter' ); ?>
                </button>
                <button type="button" id="wc-apply-filters" class="wc-btn wc-btn-primary">
                    <?php esc_html_e( 'Apply Filters', 'woo-product-filter' ); ?>
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="wc-filter-content">
        <div class="wc-filter-results-header">
            <div class="wc-results-info">
                <span class="wc-results-count"></span>
            </div>
            <div class="wc-filter-sort">
                <select id="wc-sort-products" class="wc-sort-select">
                    <option value="default"><?php esc_html_e( 'Default sorting', 'woo-product-filter' ); ?></option>
                    <option value="price-low"><?php esc_html_e( 'Sort by price: low to high', 'woo-product-filter' ); ?>
                    </option>
                    <option value="price-high">
                        <?php esc_html_e( 'Sort by price: high to low', 'woo-product-filter' ); ?></option>
                    <option value="name"><?php esc_html_e( 'Sort by name: A to Z', 'woo-product-filter' ); ?></option>
                    <option value="rating"><?php esc_html_e( 'Sort by rating', 'woo-product-filter' ); ?></option>
                </select>
            </div>
        </div>

        <div id="wc-filtered-products" class="wc-products-grid">
            <div class="wc-loading">
                <div class="wc-spinner"></div>
                <p><?php esc_html_e( 'Loading products...', 'woo-product-filter' ); ?></p>
            </div>
        </div>

        <div class="wc-filter-pagination"></div>
    </div>
</div>
<?php
    }

    /**
     * Render category filters
     */
    private function render_category_filters() {
        $categories = get_terms( array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ) );

        if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
            ?>
<div class="wc-filter-section wc-filter-categories">
    <h4><?php echo esc_html( __( 'Categories', 'woo-product-filter' ) ); ?></h4>
    <div class="wc-attribute-options">
        <?php foreach ( $categories as $category ) : ?>
        <label class="wc-attribute-label">
            <input type="checkbox" class="wc-filter-checkbox" name="product_cat[]"
                value="<?php echo esc_attr( $category->slug ); ?>">
            <span class="wc-checkbox-custom"></span>
            <span class="wc-term-name"><?php echo esc_html( $category->name ); ?></span>
            <span class="wc-term-count">(<?php echo esc_html( $category->count ); ?>)</span>
        </label>
        <?php endforeach; ?>
    </div>
</div>
<?php
        }
    }

    /**
     * Render tag filters
     */
    private function render_tag_filters() {
        $tags = get_terms( array(
            'taxonomy' => 'product_tag',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ) );

        if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
            ?>
<div class="wc-filter-section wc-filter-tags">
    <h4><?php echo esc_html( __( 'Tags', 'woo-product-filter' ) ); ?></h4>
    <div class="wc-attribute-options">
        <?php foreach ( $tags as $tag ) : ?>
        <label class="wc-attribute-label">
            <input type="checkbox" class="wc-filter-checkbox" name="product_tag[]"
                value="<?php echo esc_attr( $tag->slug ); ?>">
            <span class="wc-checkbox-custom"></span>
            <span class="wc-term-name"><?php echo esc_html( $tag->name ); ?></span>
            <span class="wc-term-count">(<?php echo esc_html( $tag->count ); ?>)</span>
        </label>
        <?php endforeach; ?>
    </div>
</div>
<?php
        }
    }

    /**
     * Render attribute filters
     */
    private function render_attribute_filters() {
        $attributes = wc_get_attribute_taxonomies();
        
        if ( empty( $attributes ) ) {
            return;
        }

        foreach ( $attributes as $attribute ) {
            $taxonomy = 'pa_' . $attribute->attribute_name;
            $terms = get_terms( array(
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
            ) );

            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                $attribute_type = $attribute->attribute_type;
                ?>
<div class="wc-filter-section wc-filter-attribute" data-attribute="<?php echo esc_attr( $taxonomy ); ?>"
    data-attribute-type="<?php echo esc_attr( $attribute_type ); ?>">
    <h4><?php echo esc_html( $attribute->attribute_label ); ?></h4>
    <div class="wc-attribute-options wc-attribute-<?php echo esc_attr( $attribute_type ); ?>">
        <?php 
                        foreach ( $terms as $term ) {
                            $this->render_attribute_option( $term, $taxonomy, $attribute_type );
                        }
                        ?>
    </div>
</div>
<?php
            }
        }
    }

    /**
     * Render individual attribute option
     */
    private function render_attribute_option( $term, $taxonomy, $attribute_type ) {
        $term_name = esc_html( $term->name );
        $term_slug = esc_attr( $term->slug );
        $term_count = esc_html( $term->count );

        switch ( $attribute_type ) {
            case 'color':
                $this->render_color_option( $term, $taxonomy, $term_name, $term_slug, $term_count );
                break;
            case 'size':
                $this->render_size_option( $term, $taxonomy, $term_name, $term_slug, $term_count );
                break;
            case 'image':
                $this->render_image_option( $term, $taxonomy, $term_name, $term_slug, $term_count );
                break;
            default:
                $this->render_default_option( $term, $taxonomy, $term_name, $term_slug, $term_count );
                break;
        }
    }

    /**
     * Render color attribute option
     */
    private function render_color_option( $term, $taxonomy, $term_name, $term_slug, $term_count ) {
        $color_value = get_term_meta( $term->term_id, 'color', true );
        $color_style = '';
        
        if ( $color_value ) {
            // Handle hex colors
            if ( preg_match( '/^#[0-9A-F]{6}$/i', $color_value ) ) {
                $color_style = "background-color: {$color_value};";
            }
            // Handle color names
            else {
                $color_style = "background-color: {$this->get_color_from_name( $color_value )};";
            }
        } else {
            // Try to extract color from term name
            $color_style = "background-color: {$this->get_color_from_name( $term_name )};";
        }
        
        ?>
<label class="wc-attribute-label wc-color-option">
    <input type="checkbox" class="wc-filter-checkbox" name="<?php echo esc_attr( $taxonomy ); ?>[]"
        value="<?php echo $term_slug; ?>">
    <span class="wc-color-swatch" style="<?php echo $color_style; ?>" title="<?php echo $term_name; ?>"></span>
    <span class="wc-term-name"><?php echo $term_name; ?></span>
    <span class="wc-term-count">(<?php echo $term_count; ?>)</span>
</label>
<?php
    }

    /**
     * Render size attribute option
     */
    private function render_size_option( $term, $taxonomy, $term_name, $term_slug, $term_count ) {
        ?>
<label class="wc-attribute-label wc-size-option">
    <input type="checkbox" class="wc-filter-checkbox" name="<?php echo esc_attr( $taxonomy ); ?>[]"
        value="<?php echo $term_slug; ?>">
    <span class="wc-size-badge"><?php echo $term_name; ?></span>
    <span class="wc-term-count">(<?php echo $term_count; ?>)</span>
</label>
<?php
    }

    /**
     * Render image attribute option
     */
    private function render_image_option( $term, $taxonomy, $term_name, $term_slug, $term_count ) {
        $image_id = get_term_meta( $term->term_id, 'image', true );
        $image_url = '';
        
        if ( $image_id ) {
            $image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
        }
        
        ?>
<label class="wc-attribute-label wc-image-option">
    <input type="checkbox" class="wc-filter-checkbox" name="<?php echo esc_attr( $taxonomy ); ?>[]"
        value="<?php echo $term_slug; ?>">
    <?php if ( $image_url ) : ?>
    <span class="wc-image-swatch">
        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo $term_name; ?>">
    </span>
    <?php endif; ?>
    <span class="wc-term-name"><?php echo $term_name; ?></span>
    <span class="wc-term-count">(<?php echo $term_count; ?>)</span>
</label>
<?php
    }

    /**
     * Render default attribute option
     */
    private function render_default_option( $term, $taxonomy, $term_name, $term_slug, $term_count ) {
        ?>
<label class="wc-attribute-label">
    <input type="checkbox" class="wc-filter-checkbox" name="<?php echo esc_attr( $taxonomy ); ?>[]"
        value="<?php echo $term_slug; ?>">
    <span class="wc-checkbox-custom"></span>
    <span class="wc-term-name"><?php echo $term_name; ?></span>
    <span class="wc-term-count">(<?php echo $term_count; ?>)</span>
</label>
<?php
    }

    /**
     * Get color from color name
     */
    private function get_color_from_name( $color_name ) {
        $color_map = array(
            'red' => '#FF0000',
            'blue' => '#0000FF',
            'green' => '#008000',
            'yellow' => '#FFFF00',
            'orange' => '#FFA500',
            'purple' => '#800080',
            'pink' => '#FFC0CB',
            'brown' => '#A52A2A',
            'black' => '#000000',
            'white' => '#FFFFFF',
            'gray' => '#808080',
            'grey' => '#808080',
            'silver' => '#C0C0C0',
            'gold' => '#FFD700',
            'navy' => '#000080',
            'teal' => '#008080',
            'maroon' => '#800000',
            'lime' => '#00FF00',
            'aqua' => '#00FFFF',
            'fuchsia' => '#FF00FF',
            'olive' => '#808000',
        );

        $color_key = strtolower( trim( $color_name ) );
        return isset( $color_map[$color_key] ) ? $color_map[$color_key] : '#CCCCCC';
    }
}

// Initialize shortcode class only if not in admin area
if ( ! is_admin() ) {
    new WC_Product_Filter_Shortcode();
}