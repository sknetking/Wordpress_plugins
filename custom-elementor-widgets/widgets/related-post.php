<?php 
class Elementor_related_posts extends \Elementor\Widget_Base {

public function get_name() {
    return 'Elementor_related_posts';
}

public function get_title() {
    return esc_html__( 'Related Articles', 'elementor' );
}

public function get_icon() {
    return 'eicon-posts-grid';
}

public function get_categories() {
    return [ 'basic' ];
}


protected function _register_controls() {
    // Content Tab
    $this->start_controls_section(
        'content_section',
        [
            'label' => esc_html__( 'Content', 'elementor' ),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
    );

    // Number of Posts
    $this->add_control(
        'posts_per_page',
        [
            'label' => esc_html__( 'Number of Posts', 'elementor' ),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 5,
        ]
    );

   
    // Sorting Options
    $this->add_control(
        'orderby',
        [
            'label' => esc_html__( 'Order By', 'elementor' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'date' => esc_html__( 'Date', 'elementor' ),
                'title' => esc_html__( 'Title', 'elementor' ),
                'comment_count' => esc_html__( 'Comment Count', 'elementor' ),
                'rand' => esc_html__( 'Random', 'elementor' ),
              
            ],
            'default' => 'date',
        ]
    );

    $this->add_control(
        'order',
        [
            'label' => esc_html__( 'Order', 'elementor' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'ASC' => esc_html__( 'Ascending', 'elementor' ),
                'DESC' => esc_html__( 'Descending', 'elementor' ),
            ],
            'default' => 'DESC',
        ]
    );

    $this->end_controls_section();
}

// Helper function to get categories

protected function render() {
    $settings = $this->get_settings_for_display();
    $element_id = $this->get_id();

     // Query Arguments
     function get_related_posts($post_id, $limit = 3) {
        $post_tags = wp_get_post_tags($post_id);
        $post_categories = get_the_category($post_id);
    
        $tag_ids = $post_tags ? wp_list_pluck($post_tags, 'term_id') : array();
        $category_ids = $post_categories ? wp_list_pluck($post_categories, 'term_id') : array();
    
        $args = array(
            'post_type' => 'post', // Change if needed
            'post__not_in' => array($post_id),
            'posts_per_page' => $limit,
            'orderby' => 'rand',
            'ignore_sticky_posts' => 1,
        );
    
        // Combine tag and category queries using a tax_query
        $tax_query = array('relation' => 'OR'); // OR relation means either tag OR category match
    
        if (!empty($tag_ids)) {
            $tax_query[] = array(
                'taxonomy' => 'post_tag',
                'field' => 'term_id',
                'terms' => $tag_ids,
            );
        }
    
        if (!empty($category_ids)) {
            $tax_query[] = array(
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $category_ids,
            );
        }
    
        $args['tax_query'] = $tax_query;
    
    
        $related_posts_query = new WP_Query($args);
    
        if ($related_posts_query->have_posts()) {
             echo '<div class="related-posts">';
                echo '<h3>Related Posts</h3>';
                echo '<ul>';
                while ($related_posts_query->have_posts()) {
                    $related_posts_query->the_post();
                    echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
                }
                echo '</ul>';
                echo '</div>';
        }
    
        wp_reset_postdata();
    }
    
    
    // In your single.php:
    if (is_singular('post')) {
        get_related_posts(get_the_ID());
    }
  
    }
}