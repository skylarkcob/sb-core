<?php
/*
 * Name: Add New Post Frontend
 * Description: Allow registered users add new post from frontend.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_load_extension_add_post_frontend() {
	return apply_filters( 'hocwp_theme_load_extension_add_post_frontend', HT_extension()->is_active( __FILE__ ) );
}

$load = hocwp_theme_load_extension_add_post_frontend();

if ( ! $load ) {
	return;
}

require dirname( __FILE__ ) . '/add-post-frontend/add-post-frontend.php';