<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$load = hocwp_theme_load_extension_jwplayer();

if ( ! $load ) {
	return;
}

function hocwp_theme_settings_page_jwplayer_tab( $tabs ) {
	$tabs['jwplayer'] = array(
		'text' => __( 'JW Player', 'sb-core' ),
		'icon' => '<span class="dashicons dashicons-controls-play"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_jwplayer_tab' );

global $hocwp_theme;

if ( 'jwplayer' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_jwplayer_section() {
	$fields = array(
		'streamango'   => array(
			'tab'         => 'jwplayer',
			'id'          => 'streamango',
			'title'       => __( 'Streamango', 'sb-core' ),
			'description' => __( 'Streamango streaming API settings.', 'sb-core' )
		),
		'streamcherry' => array(
			'tab'         => 'jwplayer',
			'id'          => 'streamcherry',
			'title'       => __( 'Streamcherry', 'sb-core' ),
			'description' => __( 'Streamcherry streaming API settings.', 'sb-core' )
		)
	);

	$fields = array();

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_jwplayer_settings_section', 'hocwp_theme_settings_page_jwplayer_section' );

function hocwp_theme_settings_page_jwplayer_field() {
	$fields    = array();

	$fields[]  = array(
		'id'    => 'key',
		'title' => __( 'Key', 'sb-core' ),
		'tab'   => 'jwplayer',
		'args'  => array(
			'type'      => 'string',
			'label_for' => true
		)
	);

	$fields[]  = array(
		'id'    => 'player_library_url',
		'title' => __( 'Player Library URL', 'sb-core' ),
		'tab'   => 'jwplayer',
		'args'  => array(
			'type'      => 'url',
			'label_for' => true
		)
	);

	$skins_dir = HOCWP_THEME_CUSTOM_PATH . '/lib/jwplayer/skins';

	if ( is_dir( $skins_dir ) ) {
		$files = scandir( $skins_dir );
		unset( $files[0], $files[1] );

		if ( HT()->array_has_value( $files ) ) {
			$opts = array(
				__( '-- Choose skin --', 'sb-core' )
			);

			foreach ( $files as $file ) {
				$info                      = pathinfo( $file );
				$opts[ $info['filename'] ] = ucfirst( $info['filename'] );
			}

			$fields[] = array(
				'id'    => 'skin',
				'title' => __( 'Skin', 'sb-core' ),
				'tab'   => 'jwplayer',
				'args'  => array(
					'type'          => 'string',
					'callback'      => array( 'HOCWP_Theme_HTML_Field', 'select' ),
					'callback_args' => array(
						'options' => $opts
					)
				)
			);
		}
	}

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_jwplayer_settings_field', 'hocwp_theme_settings_page_jwplayer_field' );

function hocwp_theme_settings_page_jwplayer_flush_rules() {
	set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
}

add_action( 'hocwp_theme_settings_saved', 'hocwp_theme_settings_page_jwplayer_flush_rules' );
add_action( 'init', 'hocwp_theme_settings_page_jwplayer_flush_rules' );