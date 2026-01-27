<?php
/**
 * Plugin Name: WooCommerce Variation Switcher
 * Description: A WooCommerce plugin to add a variation product gallery with Bootstrap and jQuery.
 * Version: 1.0.0
 * Author: SK NetKing
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function enqueue_media_picker_scripts() {
    wp_enqueue_media(); // Enqueue the WordPress media library scripts
   // wp_enqueue_script('media-picker-script', get_template_directory_uri() . '/js/media-picker.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'enqueue_media_picker_scripts');



function wcvg_enqueue_scripts() {
//     wp_enqueue_style( 'bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' );
    wp_enqueue_style( 'wcvg-gallery-css', plugin_dir_url( __FILE__ ) . 'gallery.css', array(), '1.0.0' );
    wp_enqueue_script( 'wcvg-gallery-js', plugin_dir_url( __FILE__ ) . 'gallery.js', array('jquery'), '1.0.0', true );

    // Localize script to pass PHP values to JavaScript
    wp_localize_script( 'wcvg-gallery-js', 'wcvg_vars', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
    ));
}
add_action( 'wp_enqueue_scripts', 'wcvg_enqueue_scripts' );

// Hook into WooCommerce to override template paths

/****
 * Register custom term fields 
 * ****/
include_once("inc/add-attribute-trems.php");
include_once("inc/dynamic-attribute-display.php");