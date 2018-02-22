<?php
function hocwp_ext_ads_sub_menu() {
	$title = __( 'Ads Management', 'hocwp-ext' );
	add_theme_page( $title, $title, 'manage_options', 'edit.php?post_type=hocwp_ads' );
}

add_action( 'admin_menu', 'hocwp_ext_ads_sub_menu', 99 );

function hocwp_ext_ads_meta_box() {
	$meta = new HOCWP_Theme_Meta_Post();
	$meta->add_post_type( 'hocwp_ads' );
	$meta->set_title( __( 'Ads Information', 'hocwp-ext' ) );

	$options = hocwp_ext_get_ads_positions();

	$args  = array( 'options' => $options, 'option_all' => __( '-- Choose position --', 'hocwp-ext' ) );
	$field = hocwp_theme_create_meta_field( 'position', __( 'Position:', 'hocwp-ext' ), 'select', $args );
	$meta->add_field( $field );

	$args  = array(
		'class' => 'datepicker widefat'
	);
	$field = hocwp_theme_create_meta_field( 'expire', __( 'Expiry date:', 'hocwp-ext' ), 'input', $args, 'timestamp' );
	$meta->add_field( $field );

	$field = hocwp_theme_create_meta_field( 'image', __( 'Image:', 'hocwp-ext' ), 'media_upload' );
	$meta->add_field( $field );

	$field = hocwp_theme_create_meta_field( 'url', __( 'Url:', 'hocwp-ext' ), 'input_url', '', 'url' );
	$meta->add_field( $field );

	$args  = array( 'row' => 10 );
	$field = hocwp_theme_create_meta_field( 'code', __( 'Code:', 'hocwp-ext' ), 'textarea', $args, 'html' );
	$meta->add_field( $field );

	$args  = array( 'type' => 'checkbox' );
	$field = hocwp_theme_create_meta_field( 'active', __( 'Make this ads as active status?', 'hocwp-ext' ), 'input', $args, 'boolean' );
	$meta->add_field( $field );
}

add_action( 'load-post.php', 'hocwp_ext_ads_meta_box' );
add_action( 'load-post-new.php', 'hocwp_ext_ads_meta_box' );

function hocwp_ext_ads_default_hidden_meta_boxes( $hidden, $screen ) {
	if ( $screen instanceof WP_Screen && 'post' == $screen->base && 'hocwp_ads' == $screen->id ) {
		$defaults = array(
			'slugdiv',
			'trackbacksdiv',
			'postcustom',
			'postexcerpt',
			'commentstatusdiv',
			'commentsdiv',
			'authordiv',
			'revisionsdiv'
		);

		$hidden = wp_parse_args( $hidden, $defaults );
		$hidden = array_unique( $hidden );
	}

	return $hidden;
}

add_filter( 'default_hidden_meta_boxes', 'hocwp_ext_ads_default_hidden_meta_boxes', 99, 2 );

function hocwp_ext_ads_add_meta_box_action() {
	$screen = get_current_screen();
	if ( $screen instanceof WP_Screen ) {
		remove_meta_box( 'slugdiv', $screen, 'normal' );
	}
}

add_action( 'add_meta_boxes', 'hocwp_ext_ads_add_meta_box_action', 0 );

function hocwp_ext_ads_admin_enqueue_scripts() {
	global $post_type;

	if ( 'hocwp_ads' == $post_type ) {
		HT_Util()->enqueue_datepicker();
	}
}

add_action( 'admin_enqueue_scripts', 'hocwp_ext_ads_admin_enqueue_scripts' );