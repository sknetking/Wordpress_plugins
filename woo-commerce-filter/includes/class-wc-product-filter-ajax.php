<?php
/**
 * WooCommerce Product Filter AJAX Handler
 *
 * @package WooCommerceProductFilter
 */

defined( 'ABSPATH' ) || exit;

class WC_Product_Filter_AJAX {

    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX actions for both logged-in and non-logged-in users
        add_action( 'wp_ajax_wc_filter_products', array( $this, 'filter_products' ) );
        add_action( 'wp_ajax_nopriv_wc_filter_products', array( $this, 'filter_products' ) );
    }

    /**
     * Handle AJAX product filtering
     */
    public function filter_products() {
        // Security check
        if ( ! wp_verify_nonce( $_POST['filter_nonce'] ?? '', 'wc_filter_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'woo-product-filter' ) ) );
        }

        // Parse and validate inputs
        $min_price = isset( $_POST['min_price'] ) ? floatval( $_POST['min_price'] ) : 0;
        $max_price = isset( $_POST['max_price'] ) ? floatval( $_POST['max_price'] ) : 0;
        $sort = isset( $_POST['sort'] ) ? sanitize_text_field( $_POST['sort'] ) : 'default';
        $page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
        $posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 12;

        // Build query arguments
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'meta_query' => array(),
            'tax_query' => array( 'relation' => 'AND' ),
        );

        // Handle price filtering
        if ( $min_price > 0 || $max_price > 0 ) {
            $price_query = array( 'relation' => 'AND' );
            
            if ( $min_price > 0 ) {
                $price_query[] = array(
                    'key' => '_price',
                    'value' => $min_price,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
            
            if ( $max_price > 0 ) {
                $price_query[] = array(
                    'key' => '_price',
                    'value' => $max_price,
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }
            
            $args['meta_query'][] = $price_query;
        }

        // Handle category filtering
        if ( isset( $_POST['product_cat'] ) && is_array( $_POST['product_cat'] ) ) {
            $categories = array_map( 'sanitize_text_field', $_POST['product_cat'] );
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $categories,
                'operator' => 'IN'
            );
        }

        // Handle tag filtering
        if ( isset( $_POST['product_tag'] ) && is_array( $_POST['product_tag'] ) ) {
            $tags = array_map( 'sanitize_text_field', $_POST['product_tag'] );
            $args['tax_query'][] = array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => $tags,
                'operator' => 'IN'
            );
        }

        // Handle attribute filtering
        $attributes = wc_get_attribute_taxonomies();
        foreach ( $attributes as $attribute ) {
            $taxonomy = 'pa_' . $attribute->attribute_name;
            
            if ( isset( $_POST[$taxonomy] ) && is_array( $_POST[$taxonomy] ) ) {
                $terms = array_map( 'sanitize_text_field', $_POST[$taxonomy] );
                $args['tax_query'][] = array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $terms,
                    'operator' => 'IN'
                );
            }
        }

        // Handle sorting
        switch ( $sort ) {
            case 'price-low':
                $args['meta_key'] = '_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'price-high':
                $args['meta_key'] = '_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'name':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'rating':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }

        $query = new WP_Query( $args );
        
        ob_start();
        
        if ( $query->have_posts() ) :
            woocommerce_product_loop_start();
            
            while ( $query->have_posts() ) : $query->the_post();
                wc_get_template_part( 'content', 'product' );
            endwhile;
            
            woocommerce_product_loop_end();
            
            // Generate pagination
            $this->generate_pagination( $query, $page, $posts_per_page );
            
        else :
            echo '<div class="wc-no-products">';
            echo '<p>' . esc_html__( 'No products found matching your criteria.', 'woo-product-filter' ) . '</p>';
            echo '</div>';
        endif;
        
        $products_html = ob_get_clean();
        
        // Prepare response
        $response = array(
            'success' => true,
            'products' => $products_html,
            'count' => $query->found_posts,
            'max_pages' => $query->max_num_pages,
            'current_page' => $page
        );
        
        wp_reset_postdata();
        wp_send_json_success( $response );
    }

    /**
     * Generate pagination HTML
     */
    private function generate_pagination( $query, $current_page, $posts_per_page ) {
        $max_pages = $query->max_num_pages;
        
        if ( $max_pages <= 1 ) {
            return;
        }
        
        echo '<div class="wc-pagination">';
        echo '<div class="wc-pagination-info">';
        printf(
            esc_html__( 'Showing %d-%d of %d products', 'woo-product-filter' ),
            ( $current_page - 1 ) * $posts_per_page + 1,
            min( $current_page * $posts_per_page, $query->found_posts ),
            $query->found_posts
        );
        echo '</div>';
        
        echo '<div class="wc-pagination-links">';
        
        // Previous button
        if ( $current_page > 1 ) :
            echo '<button class="wc-pagination-btn wc-prev" data-page="' . ( $current_page - 1 ) . '">';
            echo esc_html__( 'Previous', 'woo-product-filter' );
            echo '</button>';
        endif;
        
        // Page numbers
        $show_pages = 3;
        $start_page = max( 1, $current_page - $show_pages );
        $end_page = min( $max_pages, $current_page + $show_pages );
        
        for ( $i = $start_page; $i <= $end_page; $i++ ) :
            $class = $i === $current_page ? 'wc-active' : '';
            echo '<button class="wc-pagination-btn wc-page ' . $class . '" data-page="' . $i . '">';
            echo esc_html( $i );
            echo '</button>';
        endfor;
        
        // Next button
        if ( $current_page < $max_pages ) :
            echo '<button class="wc-pagination-btn wc-next" data-page="' . ( $current_page + 1 ) . '">';
            echo esc_html__( 'Next', 'woo-product-filter' );
            echo '</button>';
        endif;
        
        echo '</div>';
        echo '</div>';
    }
}