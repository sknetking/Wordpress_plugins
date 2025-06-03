<?php
class Elementor_Custom_Recent_Articles_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'custom_recent_articles';
    }

    public function get_title(): string {
        return esc_html__( 'Recent posts', 'elementor' );
    }
    
    public function get_icon(): string {
        return 'eicon-code';
    }
    
    public function get_categories(): array {
        return [ 'basic' ];
    }
    
    public function get_keywords(): array {
        return [ "loop" ];
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

        $this->add_control(
            'posts_per_page',
            [
                'label' => esc_html__( 'Number of Posts', 'elementor-custom-recent-articles' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $args = [
            'post_type' => 'post',
            'posts_per_page' => $settings['posts_per_page'],
        ];

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            echo '<div class="custom-recent-articles">';
            while ( $query->have_posts() ) {
                $query->the_post();
                echo '<div class="article-item">';
                echo '<h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
                echo '<p>' . get_the_excerpt() . '</p>';
                echo '</div>';
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p>No articles found.</p>';
        }
    }
}