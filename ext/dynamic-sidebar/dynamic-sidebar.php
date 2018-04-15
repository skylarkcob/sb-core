<?php
function hocwp_ext_dynamic_sidebar_init_action() {
	$args = array(
		'name'          => __( 'Sidebars', 'sb-core' ),
		'singular_name' => __( 'Sidebar', 'sb-core' ),
		'supports'      => array( 'title', 'excerpt' ),
		'public'        => false,
		'show_in_menu'  => false,
		'show_ui'       => true
	);

	$args = HT_Util()->post_type_args( $args );

	register_post_type( 'hocwp_sidebar', $args );
}

add_action( 'init', 'hocwp_ext_dynamic_sidebar_init_action', 0 );

function hocwp_ext_dynamic_sidebar_widgets_init() {
	$types = get_post_types( array( '_builtin' => false, 'public' => true ) );
	//$types[] = 'post';

	$types = array_map( 'get_post_type_object', $types );

	foreach ( $types as $post_type ) {
		$name = $post_type->labels->singular_name;

		$args = array(
			'id'          => $post_type->name,
			'name'        => sprintf( __( '%s Sidebar', 'sb-core' ), $name ),
			'description' => sprintf( __( 'Display widget on %s singular page and archive page.', 'sb-core' ), $name )
		);

		register_sidebar( $args );
	}
}

add_action( 'widgets_init', 'hocwp_ext_dynamic_sidebar_widgets_init', 99 );

if ( is_admin() ) {
	load_template( dirname( __FILE__ ) . '/admin.php' );
}