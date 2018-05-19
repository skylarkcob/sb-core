<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_files_filter( $files ) {
	$path = HOCWP_EXT_PATH . '/ext';
	hocwp_theme_load_extension_files( $path, $files );

	return $files;
}

add_filter( 'hocwp_theme_extensions_files', 'hocwp_ext_files_filter' );