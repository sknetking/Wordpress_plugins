<?php

class Elementor_Dynamic_Tag_post_meta extends \Elementor\Core\DynamicTags\Tag {


public function get_name(): string {
    return 'post-meta';
}

/**
 * Get dynamic tag title.
 *
 * Returns the title of the random number tag.
 *
 * @since 1.0.0
 * @access public
 * @return string Dynamic tag title.
 */
public function get_title(): string {
    return esc_html__( 'Post count Number', 'elementor-random-number-dynamic-tag' );
}

/**
 * Get dynamic tag groups.
 *
 * Retrieve the list of groups the random number tag belongs to.
 *
 * @since 1.0.0
 * @access public
 * @return array Dynamic tag groups.
 */
public function get_group(): array {
    return [ 'actions' ];
}

/**
 * Get dynamic tag categories.
 *
 * Retrieve the list of categories the random number tag belongs to.
 *
 * @since 1.0.0
 * @access public
 * @return array Dynamic tag categories.
 */
public function get_categories(): array {
    return [ \Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY ];
}

/**
 * Render tag output on the frontend.
 *
 * Written in PHP and used to generate the final HTML.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
public function render(): void {
    echo rand();
}

}

