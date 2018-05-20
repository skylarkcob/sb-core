<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) {
	require HOCWP_EXT_PATH . '/inc/functions-development.php';
}

function hocwp_ext_files_path_filter( $paths ) {
	$paths[] = HOCWP_EXT_PATH . '/ext';

	return $paths;
}

add_filter( 'hocwp_theme_extension_paths', 'hocwp_ext_files_path_filter' );

require HOCWP_EXT_PATH . '/inc/functions.php';

if ( function_exists( 'hocwp_load_all_extensions' ) ) {
	hocwp_load_all_extensions( HOCWP_EXT_PATH );
}

function hocwp_ext_register_custom_post_types_and_taxonomies() {
	$post_types = hocwp_ext_custom_post_types_registration();

	if ( HT()->array_has_value( $post_types ) ) {
		foreach ( $post_types as $post_type => $args ) {
			$args = HT_Util()->post_type_args( $args );
			register_post_type( $post_type, $args );
		}
	}

	$taxonomies = hocwp_ext_custom_taxonomies_registration();

	if ( HT()->array_has_value( $taxonomies ) ) {
		foreach ( $taxonomies as $taxonomy => $data ) {
			$post_type = isset( $data['post_type'] ) ? $data['post_type'] : '';

			if ( ! empty( $post_type ) ) {
				$args = isset( $data['args'] ) ? $data['args'] : '';

				$args = HT_Util()->taxonomy_args( $args );

				register_taxonomy( $taxonomy, $post_type, $args );
			}
		}
	}
}

add_action( 'init', 'hocwp_ext_register_custom_post_types_and_taxonomies' );