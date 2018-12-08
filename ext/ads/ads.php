<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_ads_register_object() {
	$args = array(
		'labels'       => array(
			'name' => __( 'Ads', 'sb-core' )
		),
		'private'      => true,
		'show_in_menu' => false,
		'supports'     => array( 'title' )
	);

	$args = HT_Util()->post_type_args( $args );
	register_post_type( 'hocwp_ads', $args );
}

add_action( 'init', 'hocwp_ext_ads_register_object', 0 );

function hocwp_ext_get_ads_positions() {
	return HTE_Ads()->get_positions();
}

if ( is_admin() ) {
	load_template( dirname( __FILE__ ) . '/admin.php' );
} else {
	require dirname( __FILE__ ) . '/front-end.php';
}