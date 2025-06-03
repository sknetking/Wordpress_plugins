<?php
class Elementor_custom_post_loop extends \Elementor\Widget_Base {

    public function get_name() {
        return 'custom_post_loop';
    }

    public function get_title() {
        return esc_html__( 'Custom Posts Grid', 'elementor-custom-recent-articles' );
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
    public function get_post_types() {
        $post_types = get_post_types([
            'public' => true, // Only include public post types
            'show_in_nav_menus' => true, // Only include post types that are shown in nav menus
        ], 'objects'); // Return as objects for better handling
    
        $options = [];
    
        foreach ($post_types as $post_type) {
            $options[$post_type->name] = $post_type->label; // Use post type slug as key and label as value
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

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'text-domain'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
                'tablet_default' => 2,
                'mobile_default' => 1,
                'min' => 1,
                'max' => 6,
                'selectors' => [
                    '{{WRAPPER}} .responsive-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_control(
            'post_type',
            [
                'label' => __('Select Post Type', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_post_types(), // Function to fetch all post types
                'default' => 'post',
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

        $this->add_control(
            'exclude_category',
            [
                'label' => __('Exclude Category', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' =>  $this->get_all_categories(), // Function to fetch categories
                'multiple' => true,
            ]
        );
        $this->add_control(
            'load_more',
            [
                'label' => __('Show Load More Button', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'text-domain'),
                'label_off' => __('No', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'show_author',
            [
                'label' => __('Show Author', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'text-domain'),
                'label_off' => __('Hide', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_date',
            [
                'label' => __('Show Publish Date', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'text-domain'),
                'label_off' => __('Hide', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_category',
            [
                'label' => __('Show Category', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'text-domain'),
                'label_off' => __('Hide', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_tags',
            [
                'label' => __('Show Tags', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'text-domain'),
                'label_off' => __('Hide', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'show_featured_image',
            [
                'label' => __('Show Featured Image', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'text-domain'),
                'label_off' => __('Hide', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'featured_image_link',
            [
                'label' => __('Link Featured Image', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'text-domain'),
                'label_off' => __('No', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        $this->add_control(
            'title_link',
            [
                'label' => __('Link Title', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'text-domain'),
                'label_off' => __('No', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

                $this->add_control(
                    'show_excerpt',
                    [
                        'label' => __('Show Excerpt', 'text-domain'),
                        'type' => \Elementor\Controls_Manager::SWITCHER,
                        'label_on' => __('Show', 'text-domain'),
                        'label_off' => __('Hide', 'text-domain'),
                        'return_value' => 'yes',
                        'default' => 'yes',
                    ]
                );

                $this->add_control(
                    'excerpt_length',
                    [
                        'label' => __('Excerpt Length (Words)', 'text-domain'),
                        'type' => \Elementor\Controls_Manager::NUMBER,
                        'default' => 30,
                        'condition' => [
                            'show_excerpt' => 'yes',
                        ],
                    ]
                );


                $this->add_control(
                    'show_read_more',
                    [
                        'label' => __('Show Read More', 'text-domain'),
                        'type' => \Elementor\Controls_Manager::SWITCHER,
                        'label_on' => __('Show', 'text-domain'),
                        'label_off' => __('Hide', 'text-domain'),
                        'return_value' => 'yes',
                        'default' => 'yes',
                    ]
                );
                
                $this->add_control(
                    'read_more_text',
                    [
                        'label' => __('Read More Text', 'text-domain'),
                        'type' => \Elementor\Controls_Manager::TEXT,
                        'default' => __('Read More', 'text-domain'),
                        'condition' => [
                            'show_read_more' => 'yes',
                        ],
                    ]
                );
           
        $this->end_controls_section();
    }

    // Helper function to get categories

    protected function render() {
    $settings = $this->get_settings_for_display();

    $paged = isset($_GET['page']) ? intval($_GET['page']) : 1;
    // Build query arguments with optimizations
    $args = [
        'post_type'      => $settings['post_type'] ?? 'post',
        'posts_per_page' => $settings['posts_per_page'] ?? 10,
        'paged' => $paged,
    ];

    if (!empty($settings['category'])) {
        $args['category__in'] = $settings['category'];
    }
    if (!empty($settings['exclude_category'])) {
        $args['category__not_in'] = $settings['exclude_category'];
    }

    // Try caching results if applicable
    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        echo '<p>' . esc_html__('No posts found.', 'text-domain') . '</p>';
        return;
    }
?>
    <style>
    .responsive-grid {
        display: grid;
        grid-template-columns: 1fr; /* Default: 1 column */
        gap: 20px; /* Adjust spacing between items */
    }

/* Medium screens: 2 columns */
    @media (min-width: 768px) {
        .responsive-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Large screens: 3 columns */
    @media (min-width: 1024px) {
        .responsive-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }


    </style>

   <div class="custom-post-wrap" id="ajax-post-container" data-max-pages="<?= $query->max_num_pages; ?>" >
     <div class="responsive-grid">
<?php
    $posts_html = [];

    while ($query->have_posts()) {
        $query->the_post();

        // Featured Image
        $thumbnail_html = '';
        if ($settings['show_featured_image'] === 'yes' && has_post_thumbnail()) {
            $thumbnail_html .= '<div class="post-thumbnail">';
            if ($settings['featured_image_link'] === 'yes') {
                $thumbnail_html .= '<a href="' . get_the_permalink() . '">';
            }
            $thumbnail_html .= get_the_post_thumbnail();
            if ($settings['featured_image_link'] === 'yes') {
                $thumbnail_html .= '</a>';
            }
            $thumbnail_html .= '</div>';
        }

        // Post Meta
        $meta_html = [];
        if ($settings['show_author'] === 'yes') {
            $meta_html[] = '<span class="post-author">' . get_the_author() . '</span>';
        }
        if ($settings['show_date'] === 'yes') {
            $meta_html[] = '<span class="post-date">' . get_the_date() . '</span>';
        }
        if ($settings['show_category'] === 'yes') {
            $meta_html[] = '<span class="post-categories">' . get_the_category_list(', ') . '</span>';
        }
        if ($settings['show_tags'] === 'yes') {
            $meta_html[] = '<span class="post-tags">' . get_the_tag_list('', ', ') . '</span>';
        }
        $meta_output = !empty($meta_html) ? '<div class="post-meta">' . implode(' | ', $meta_html) . '</div>' : '';

        // Post Title
        $title_html = '<h2 class="post-title">';
        if ($settings['title_link'] === 'yes') {
            $title_html .= '<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>';
        } else {
            $title_html .= get_the_title();
        }
        $title_html .= '</h2>';

        // Excerpt
        $excerpt_html = '';
        if ($settings['show_excerpt'] === 'yes') {
            $excerpt_html = '<div class="post-excerpt">' . wp_trim_words(get_the_excerpt(), $settings['excerpt_length']) . '</div>';
        }

        // Read More Button
        $read_more_html = '';
        if ($settings['show_read_more'] === 'yes') {
            $read_more_html = '<div class="post-read-more"> <a href="' . get_the_permalink() . '">' . esc_html($settings['read_more_text']) . '</a></div>';
        }

        // Assemble post block
        $posts_html[] = '<div class="article">' . $thumbnail_html . $title_html . $meta_output . $excerpt_html . $read_more_html . '</div>';
    }

    // Reset post data
    wp_reset_postdata();

    // Print posts
    echo implode('', $posts_html);
    
    // Close wrappers
    echo '</div>';

    echo '</div>';


}

}