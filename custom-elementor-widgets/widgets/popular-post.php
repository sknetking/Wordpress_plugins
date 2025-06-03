
<?php 

class Elementor_popular_post extends \Elementor\Widget_Base {

public function get_name() {
    return 'Elementor_popular_post';
}

public function get_title() {
    return esc_html__( 'Popular Articles', 'elementor-custom-recent-articles' );
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
            'label' => esc_html__( 'Content', 'elementor-custom-recent-articles' ),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
    );

    $this->end_controls_section();
}

// Helper function to get categories

    protected function render() {
        $settings = $this->get_settings_for_display();

   $args = array(
        'post_type'      => 'post', // Change to your post type if needed
        'meta_key'      => 'post_views_count', // Meta key for view count
        'orderby'       => 'meta_value_num', // Order by numeric meta value
        'order'         => 'DESC', // Most viewed first
        'posts_per_page' => 10, // Number of posts to display
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            // Display post title, excerpt, etc.
            the_title('<h2>', '</h2>');
            the_excerpt();
            echo "view count: ".get_post_meta(get_the_ID(),'post_views_count',true);
        }
        // Reset post data
    } else {
        echo 'No posts found.';
    }
    wp_reset_postdata();

    }
}