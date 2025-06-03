<?php
/**
 * Plugin Name: Elementor Custom Widgets
 * Description: Adds custom widgets to Elementor.
 * Version: 1.0
 * Author: Shyam
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load the widget class
function register_custom_elementor_widgets( $widgets_manager ) {
    require_once plugin_dir_path( __FILE__ ) . '/widgets/countdown-timer-widget.php';
    require_once plugin_dir_path( __FILE__ ) . '/widgets/recent-posts.php';
    require_once plugin_dir_path( __FILE__ ) . '/widgets/popular-post.php';
    require_once plugin_dir_path( __FILE__ ) . '/widgets/related-post.php';
    require_once plugin_dir_path( __FILE__ ) . '/widgets/template-widget.php';
    require_once plugin_dir_path( __FILE__ ) . '/widgets/voice-search.php';
    require_once plugin_dir_path( __FILE__ ) . '/widgets/custom-posts-loop.php';
  

    $widgets_manager->register( new \Countdown_Timer_Widget() );
    $widgets_manager->register( new \Elementor_Custom_Recent_Articles_Widget() );
    $widgets_manager->register( new \Elementor_popular_post() );
    $widgets_manager->register( new \Elementor_related_posts() );
    
    $widgets_manager->register( new \Elementor_custom_post_loop());
	$widgets_manager->register( new \Custom_Voice_Search());
	
	$widgets_manager->register( new \Custom_Button_Widget());
	

    

}
add_action( 'elementor/widgets/widgets_registered', 'register_custom_elementor_widgets' );

// Enqueue scripts and styles


function countdown_timer_widget_scripts() {
    // Frontend script
    wp_enqueue_script(
        'countdown-timer',
        plugin_dir_url( __FILE__ ) . 'assets/countdown-timer.js',
        [],
        '1.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'countdown_timer_widget_scripts' );



function custom_recent_articles_scripts() {
    wp_enqueue_script( 'custom-recent-articles', plugins_url( 'custom-recent-articles.js', __FILE__ ), [ 'jquery' ], '1.0', true );
    wp_localize_script( 'custom-recent-articles', 'ajax_object', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );

    wp_localize_script('countdown-timer', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action( 'wp_enqueue_scripts', 'custom_recent_articles_scripts' );


function filter_recent_articles() {
    $category = isset($_POST['category']) ? $_POST['category'] : [];
    $orderby = isset($_POST['orderby']) ? $_POST['orderby'] : 'date';
    $order = isset($_POST['order']) ? $_POST['order'] : 'ASC';
    
 

    $args = [
        'post_type' => 'post',
        'posts_per_page' => 5,
        'category__in' => $category,
        'orderby' => $orderby,
        'order' => $order,
    ];
    
    if( $orderby == 'meta_value_num'){
        $args['meta_key']='post_views_count';
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="article-item">';
            echo '<h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
            echo '<p>' . get_the_excerpt() . '</p>';
            echo '</div>';
        }
        wp_reset_postdata();
    } else {
        echo '<p>No articles found.</p>';
    }

    wp_die();
}
add_action('wp_ajax_filter_recent_articles', 'filter_recent_articles');
add_action('wp_ajax_nopriv_filter_recent_articles', 'filter_recent_articles');


function customSetPostViews() {
    // Check if we're on a single post page
    if (!is_single()) {
        return; // Exit if not a single post
    }

    // Get the current post ID
    $postID = get_the_ID();

    // Check if the current user is an admin
    if (current_user_can('administrator')) {
        return; // Skip counting for admin
    }

    $countKey = 'post_views_count';
    $cookieKey = 'post_viewed_' . $postID;

    // Check if the user has already viewed the post today
    if (isset($_COOKIE[$cookieKey])) {
        return; // User has already viewed the post today
    }

    // Get the current view count
    $count = get_post_meta($postID, $countKey, true);
    if ($count == '') {
        $count = 0;
    }

    // Increment the view count
    $count++;
    update_post_meta($postID, $countKey, $count);

    // Set a cookie to track the user's view
    setcookie($cookieKey, '1', time() + (12 * 60 * 60), '/'); // Expires in 12 hours
}

// Hook the function to the 'wp' action to count views when a post is loaded
add_action('wp', 'customSetPostViews');

// Add the view count column to the posts list table
function add_view_count_column($columns) {
    $columns['view_count'] = 'View Count';
    return $columns;
}
add_filter('manage_posts_columns', 'add_view_count_column');

// Display the view count in the custom column
function display_view_count_column($column_name, $post_id) {
    if ($column_name === 'view_count') {
        $count = get_post_meta($post_id, 'post_views_count', true);
        echo esc_html($count ? $count : 0);
    }
}
add_action('manage_posts_custom_column', 'display_view_count_column', 10, 2);
// Hide the view count column by default
function hide_view_count_column_by_default($hidden, $screen) {
    if ($screen->id === 'edit-post') {
        $hidden[] = 'view_count';
    }
    return $hidden;
}
add_filter('default_hidden_columns', 'hide_view_count_column_by_default', 10, 2);
