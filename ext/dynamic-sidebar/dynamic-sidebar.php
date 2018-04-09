<?php
function hocwp_ext_dynamic_sidebar_init_action() {
	$args = array(
		'name'          => __( 'Sidebars', 'hocwp-theme' ),
		'singular_name' => __( 'Sidebar', 'hocwp-theme' ),
		'supports'      => array( 'title', 'excerpt' ),
		'public'        => false,
		'show_in_menu'  => false,
		'show_ui'       => true
	);

	$args = HT_Util()->post_type_args( $args );

	register_post_type( 'hocwp_sidebar', $args );
}

add_action( 'init', 'hocwp_ext_dynamic_sidebar_init_action', 0 );

if ( is_admin() ) {
	load_template( dirname( __FILE__ ) . '/admin.php' );
}