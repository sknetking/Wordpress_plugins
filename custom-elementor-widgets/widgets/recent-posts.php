<?php
class Elementor_Custom_Recent_Articles_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'custom_recent_articles';
    }

    public function get_title() {
        return esc_html__( 'Custom Recent Articles', 'elementor-custom-recent-articles' );
    }

    public function get_icon() {
        return 'eicon-posts-grid';
    }

    public function get_categories() {
        return [ 'basic' ];
    }
    public function get_all_categories() {
        $categories = get_categories();
        $options = [];
        foreach ( $categories as $category ) {
            $options[ $category->term_id ] = $category->name;
        }
        return $options;
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

        // Number of Posts
        $this->add_control(
            'posts_per_page',
            [
                'label' => esc_html__( 'Number of Posts', 'elementor-custom-recent-articles' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
            ]
        );

        // Category Filter
        $this->add_control(
            'category',
            [
                'label' => esc_html__( 'Category', 'elementor-custom-recent-articles' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_all_categories(),
                'multiple' => true,
                'label_block' => true,
            ]
        );

        // Sorting Options
        $this->add_control(
            'orderby',
            [
                'label' => esc_html__( 'Order By', 'elementor-custom-recent-articles' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'date' => esc_html__( 'Date', 'elementor-custom-recent-articles' ),
                    'title' => esc_html__( 'Title', 'elementor-custom-recent-articles' ),
                    'comment_count' => esc_html__( 'Comment Count', 'elementor-custom-recent-articles' ),
                    'rand' => esc_html__( 'Random', 'elementor-custom-recent-articles' ),
                    'meta_value_num'=>esc_html__( 'Most Viewed', 'elementor-custom-recent-articles' ),
                ],
                'default' => 'date',
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => esc_html__( 'Order', 'elementor-custom-recent-articles' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'ASC' => esc_html__( 'Ascending', 'elementor-custom-recent-articles' ),
                    'DESC' => esc_html__( 'Descending', 'elementor-custom-recent-articles' ),
                ],
                'default' => 'ASC',
            ]
        );

        $this->end_controls_section();
    }

    // Helper function to get categories

    protected function render() {
        $settings = $this->get_settings_for_display();
        $element_id = $this->get_id();

         // Query Arguments
        $args = [
            'post_type' => 'post',
            'posts_per_page' => $settings['posts_per_page'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
        ];
        if($settings['orderby']=='meta_value_num'){
            $args['meta_key']='post_views_count';
        }

        // Add Category Filter
        if ( ! empty( $settings['category'] ) ) {
            $args['category__in'] = $settings['category'];
        }

        ?>
    <style>
        form.filter-form {
            display: flex;
            width: fit-content;
            float: right;
            position: relative;
        }
		ul.filter-cat {
    display: inline-flex;
    list-style-type: none;
    column-gap: 16px;
    cursor: pointer;
}
    </style>
    
  <section id='<?= $element_id; ?>'>
	    <ul class='filter-cat'> <?php
        $categories = get_categories();
        foreach ($categories as $category) {
            echo '<li data-cat="' . $category->term_id. '">' .$category->name. '</li>';
        }
        ?>    
	  </ul>
		
    <form class="filter-form">
       <select id="category-filter">
        <option value=''>Category By</option>
        <?php
        $categories = get_categories();
        foreach ($categories as $category) {
            echo '<option value="' . $category->term_id. '">' .$category->name. '</option>';
        }
        ?>
     </select>
        <select id="orderby-filter">
        <option>Sort By</option>
        <option value="date">Date</option>
        <option value="title">Title</option>
        <option value="rand">Random</option>
        <option value="meta_value_num">Most Viewed</option>
        </select>
      </form>
      <div class="custom-recent-articles">
        <?php // Run the Query
        $posts = get_posts( $args );

       
           
            foreach ( $posts as $_post ) {
                echo get_the_post_thumbnail( $_post->ID, 'thumbnail' );
                echo '<div class="article-item">';
                echo '<h2><a href="' . get_permalink($_post->ID) . '">' . get_the_title( $_post->ID) . '</a></h2>';
                echo '<p>' . get_the_excerpt($_post->ID) . '</p>';
                echo '</div>';
            }
         
        ?>
        </div>
</section>
<script>
    jQuery(document).ready(function($) {
    function load_posts(category,orderby,order){
		
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_recent_articles',
                category: category,
                orderby: orderby,
                order: order,
            },
            success: function(response) {
                $('#<?= $element_id; ?> .custom-recent-articles').html(response);
            }
        });
		
	}
		$('#<?= $element_id; ?> .filter-cat li').on('click', function(e) {
        e.preventDefault();
		var category = $(e.target).attr('data-cat');
        var orderby = $('#<?= $element_id; ?> #orderby-filter').val();
        var order = $('#<?= $element_id; ?> #order-filter').val();
		
		load_posts(category,orderby,order);
		
    });
		
    $('#<?= $element_id; ?> .filter-form').on('change', function(e) {
        e.preventDefault();
		var category = $('#<?= $element_id; ?> #category-filter').val();
        var orderby = $('#<?= $element_id; ?> #orderby-filter').val();
        var order = $('#<?= $element_id; ?> #order-filter').val();
		
		load_posts(category,orderby,order);
		
    });
});

</script>
        <?php 
            wp_reset_postdata();
      
    }
}