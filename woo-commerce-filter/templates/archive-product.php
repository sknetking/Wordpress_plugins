<?php
/**
 * WooCommerce Product Filter Template
 *
 * @package WooCommerceProductFilter
 */

defined( 'ABSPATH' ) || exit;

// WooCommerce hooks and opening content wrappers
do_action( 'woocommerce_before_main_content' );

// Display the product filter
echo do_shortcode( '[product_filter]' );

// WooCommerce closing content wrappers
do_action( 'woocommerce_after_main_content' );