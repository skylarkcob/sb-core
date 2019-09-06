<?php
/*
 * Name: JW Player
 * Description: The easiest way to distribute, manage and monetize videos on the web and mobile apps. (This extension is deprecated, please use Media Player instead).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$extension = new HOCWP_Theme_Extension( __FILE__ );

HT_Extension()->deprecated( $extension->name, '6.4.2', 'Media Player' );

function hocwp_theme_load_extension_jwplayer() {
	return apply_filters( 'hocwp_theme_load_extension_jwplayer', HT_extension()->is_active( __FILE__ ) );
}

$load = hocwp_theme_load_extension_jwplayer();

if ( ! $load ) {
	return;
}

global $hocwp_theme;
$hocwp_theme->defaults['options']['jwplayer']['endpoint'] = 'player';

$hocwp_theme->options['jwplayer'] = isset( $hocwp_theme->options['jwplayer'] ) ? $hocwp_theme->options['jwplayer'] : array();

$hocwp_theme->options['jwplayer'] = wp_parse_args( $hocwp_theme->options['jwplayer'], $hocwp_theme->defaults['options']['jwplayer'] );

require( HOCWP_EXT_PATH . '/ext/jwplayer/class-hocwp-theme-streaming.php' );
require( HOCWP_EXT_PATH . '/ext/jwplayer/class-hocwp-theme-streaming-streamango.php' );
require( HOCWP_EXT_PATH . '/ext/jwplayer/class-hocwp-theme-streaming-streamcherry.php' );

function hocwp_theme_jwplayer_player_endpoint_init() {
	global $hocwp_theme;
	$options = $hocwp_theme->options;
	$name    = $options['jwplayer']['endpoint'];

	if ( empty( $name ) ) {
		$name = 'player';
	}

	add_rewrite_endpoint( $name, EP_PERMALINK | EP_ROOT );
}

add_action( 'init', 'hocwp_theme_jwplayer_player_endpoint_init' );

function hocwp_theme_jwplayer_player_redirect_control() {
	global $wp_query, $hocwp_theme;
	$options = $hocwp_theme->options;
	$name    = $options['jwplayer']['endpoint'];

	if ( empty( $name ) ) {
		$name = 'player';
	}

	if ( isset( $wp_query->query[ $name ] ) ) {
		load_template( HOCWP_EXT_PATH . '/ext/jwplayer/streaming.php' );
		exit;
	}
}

add_action( 'template_redirect', 'hocwp_theme_jwplayer_player_redirect_control' );

if ( is_admin() ) {
	require HOCWP_EXT_PATH . '/ext/jwplayer/admin-setting-page-jwplayer.php';
}