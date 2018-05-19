<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_dynamic_sidebar_admin_menu_action() {
	$title = __( 'Sidebar Manager', 'sb-core' );
	add_theme_page( $title, $title, 'manage_options', 'edit.php?post_type=hocwp_sidebar' );
}

add_action( 'admin_menu', 'hocwp_ext_dynamic_sidebar_admin_menu_action', 99 );