<?php
/*
 * Name: Add New Post Frontend
 * Description: Allow registered users add new post from frontend.
 */
function hocwp_theme_load_extension_add_post_frontend() {
	return apply_filters( 'hocwp_theme_load_extension_add_post_frontend', hocwp_theme_is_extension_active( __FILE__ ) );
}

$load = hocwp_theme_load_extension_add_post_frontend();

if ( ! $load ) {
	return;
}

require dirname( __FILE__ ) . '/add-post-frontend/add-post-frontend.php';