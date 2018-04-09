<?php
/*
Plugin Name: Extensions by HocWP Team
Plugin URI: http://hocwp.net/project/
Description: Extensions for using in theme which is created by HocWP Team. This plugin will not work if you use it on theme not written by HocWP Team.
Author: HocWP Team
Version: 2.0.8
Author URI: http://hocwp.net/
Donate link: http://hocwp.net/donate/
Text Domain: hocwp-ext
Domain Path: /languages/
*/
function hocwp_ext_check_theme() {
	$theme = wp_get_theme();

	if ( 'hocwp-theme' != $theme->get_stylesheet() ) {
		return false;
	}

	return true;
}

function hocwp_ext_check_theme_notices() {
	$msg = __( '<strong>Plugin Extensions by HocWP Team:</strong> You must use the theme written by the HocWP Team or the directory of the theme must be named as <code>hocwp-theme</code>.', 'hocwp-ext' );
	?>
    <div class="alert alert-error updated error is-dismissible alert-danger">
		<?php echo wpautop( $msg ); ?>
    </div>
	<?php
}

if ( ! hocwp_ext_check_theme() ) {
	add_action( 'admin_notices', 'hocwp_ext_check_theme_notices' );

	return;
}

define( 'HOCWP_EXT_FILE', __FILE__ );
define( 'HOCWP_EXT_PATH', dirname( HOCWP_EXT_FILE ) );
define( 'HOCWP_EXT_URL', plugins_url( '', HOCWP_EXT_FILE ) );
define( 'HOCWP_EXT_REQUIRE_THEME_CORE_VERSION', '6.2.2' );

function hocwp_ext_check_theme_core_notices() {
	$msg = sprintf( __( '<strong>Plugin Extensions by HocWP Team:</strong> You must using theme core version at least %s. Please upgrade your theme or contact theme author for more details.', 'hocwp-ext' ), '<strong>' . HOCWP_EXT_REQUIRE_THEME_CORE_VERSION . '</strong>' );
	?>
    <div class="alert alert-error updated error is-dismissible alert-danger">
		<?php echo wpautop( $msg ); ?>
    </div>
	<?php
}

function hocwp_ext_load() {
	if ( ! defined( 'HOCWP_THEME_CORE_VERSION' ) ) {
		add_action( 'admin_notices', 'hocwp_ext_check_theme_notices' );

		return;
	}

	if ( version_compare( HOCWP_THEME_CORE_VERSION, HOCWP_EXT_REQUIRE_THEME_CORE_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'hocwp_ext_check_theme_core_notices' );

		return;
	}

	global $hocwp_theme;

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

	if ( is_admin() ) {
		require HOCWP_EXT_PATH . '/inc/admin.php';
	} else {
		require HOCWP_EXT_PATH . '/inc/frontend.php';
	}
}

add_action( 'hocwp_theme_setup_after', 'hocwp_ext_load' );

function hocwp_ext_plugin_action_links_filter( $links ) {
	$links[] = '<a href="' . esc_url( admin_url( 'themes.php?page=hocwp_theme&tab=extension' ) ) . '">' . __( 'Settings', 'hocwp-ext' ) . '</a>';

	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'hocwp_ext_plugin_action_links_filter' );