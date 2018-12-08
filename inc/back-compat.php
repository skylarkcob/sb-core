<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$path = get_template_directory() . '/hocwp/inc/functions-extensions.php';

if ( file_exists( $path ) ) {
	require_once $path;
}

if ( ! function_exists( 'HT_extension' ) ) {
	return;
}

if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) {
	require HOCWP_EXT_PATH . '/inc/functions-development.php';
}

function hocwp_ext_files_path_filter( $paths ) {
	$paths[] = HOCWP_EXT_PATH . '/ext';

	return $paths;
}

add_filter( 'hocwp_theme_extension_paths', 'hocwp_ext_files_path_filter' );

function hocwp_ext_custom_post_types_registration() {
	return apply_filters( 'hocwp_theme_custom_post_types', array() );
}

function hocwp_ext_custom_taxonomies_registration() {
	return apply_filters( 'hocwp_theme_custom_taxonomies', array() );
}

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

/*
 * Check theme load all extensions function.
 *
 * Since theme core version 6.3.4, we use function hocwp_load_all_extensions to load all extensions automatically.
 * If theme version order than 6.3.4, extensions will be loaded manually.
 */
if ( ! function_exists( 'hocwp_load_all_extensions' ) && ! function_exists( 'HOCWP_Theme' ) ) {
	require HOCWP_EXT_PATH . '/ext/base-slug.php';
	require HOCWP_EXT_PATH . '/ext/google-code-prettify.php';
	require HOCWP_EXT_PATH . '/ext/optimize.php';
	require HOCWP_EXT_PATH . '/ext/jwplayer.php';
	require HOCWP_EXT_PATH . '/ext/recent-activity-post.php';
	require HOCWP_EXT_PATH . '/ext/woocommerce.php';
	require HOCWP_EXT_PATH . '/ext/add-post-frontend.php';
	require HOCWP_EXT_PATH . '/ext/account.php';
	require HOCWP_EXT_PATH . '/ext/ads.php';
}

if ( is_admin() ) {
	require HOCWP_EXT_PATH . '/inc/back-compat/admin.php';
} else {
	require HOCWP_EXT_PATH . '/inc/back-compat/frontend.php';
}