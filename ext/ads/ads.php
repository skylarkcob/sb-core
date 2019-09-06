<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_ext_ads_register_object' ) ) {
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

		register_post_type( HTE_Ads()->post_type, $args );
	}
}

add_action( 'init', 'hocwp_ext_ads_register_object', 0 );

if ( ! function_exists( 'hocwp_ext_get_ads_positions' ) ) {
	function hocwp_ext_get_ads_positions() {
		return HTE_Ads()->get_positions();
	}
}

require_once dirname( __FILE__ ) . '/class-hocwp-ads-widget.php';

if ( is_admin() ) {
	load_template( dirname( __FILE__ ) . '/admin.php' );
} else {
	require dirname( __FILE__ ) . '/front-end.php';
}

if ( ! function_exists( 'hocwp_ext_ads_widgets_init_action' ) ) {
	function hocwp_ext_ads_widgets_init_action() {
		register_widget( 'HOCWP_Ads_Widget' );
	}
}

add_action( 'widgets_init', 'hocwp_ext_ads_widgets_init_action' );