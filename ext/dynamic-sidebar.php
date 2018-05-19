<?php
/*
 * Name: Dynamic Sidebar
 * Description: Create and display sidebar dynamically.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_load_extension_dynamic_sidebar() {
	return apply_filters( 'hocwp_theme_load_extension_dynamic_sidebar', hocwp_theme_is_extension_active( __FILE__ ) );
}

$load = hocwp_theme_load_extension_dynamic_sidebar();

if ( ! $load ) {
	return;
}

require dirname( __FILE__ ) . '/dynamic-sidebar/dynamic-sidebar.php';