<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $hocwp_theme, $pagenow;

if ( ! is_object( $hocwp_theme ) ) {
	$hocwp_theme = new stdClass();
}

$path = get_template_directory() . '/hocwp/inc/functions-extensions.php';

if ( file_exists( $path ) ) {
	require_once $path;
}

if ( ! function_exists( 'hocwp_theme_is_extension_active' ) ) {
	return;
}

require HOCWP_EXT_PATH . '/inc/global.php';

/*
 * Check theme load all extensions function.
 *
 * Since theme core version 6.3.4, we use function hocwp_load_all_extensions to load all extensions automatically.
 * If theme version order than 6.3.4, extensions will be loaded manually.
 */
if ( ! function_exists( 'hocwp_load_all_extensions' ) ) {
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
	require HOCWP_EXT_PATH . '/inc/admin.php';
} else {
	require HOCWP_EXT_PATH . '/inc/frontend.php';
}