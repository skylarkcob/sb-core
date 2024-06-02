<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	if ( function_exists( 'hocwp_theme_register_plugin_update' ) ) {
		$data = get_plugin_data( SB_Core()->file );

		hocwp_theme_register_plugin_update( array(
			'basename' => SB_Core()->plugin_basename,
			'version'  => $data['Version'] ?? ''
		) );
	}
} );