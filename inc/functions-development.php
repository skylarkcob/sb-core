<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! defined( 'HOCWP_THEME_DEVELOPING' ) || 1 != HOCWP_THEME_DEVELOPING ) {
	return;
}

$allow_domains = apply_filters( 'hocwp_theme_dev_allow_domains', array(
	'localhost',
	'hocwp.net',
	'ldcuong.com'
) );

$url = get_bloginfo( 'url' );

$skip = true;

foreach ( $allow_domains as $domain ) {
	if ( str_contains( $url, $domain ) ) {
		$skip = false;
		break;
	}
}

if ( $skip ) {
	return;
}

global $pagenow;

if ( 'plugins.php' == $pagenow ) {
	$action   = $_REQUEST['action'] ?? '';
	$activate = $_REQUEST['activate'] ?? '';

	if ( 'activate' == $action || 'true' == $activate ) {
		return;
	}
}

global $hocwp_theme;

if ( ! is_object( $hocwp_theme ) ) {
	$hocwp_theme = new stdClass();
}

$hocwp_theme->defaults['compress_css_and_js_paths'] = $paths = array(
	HOCWP_THEME_PATH,
	HOCWP_THEME_CORE_PATH,
	HOCWP_THEME_CUSTOM_PATH,
	HOCWP_EXT_PATH
);

$hocwp_theme->defaults['compress_css_and_js_paths'] = apply_filters( 'hocwp_theme_compress_css_and_js_paths', $hocwp_theme->defaults['compress_css_and_js_paths'] );

function hocwp_theme_debug( $value ) {
	if ( is_array( $value ) || is_object( $value ) ) {
		error_log( print_r( $value, true ) );
	} else {
		error_log( $value );
	}
}

function hocwp_theme_dev_add_clock_to_message( &$msg ) {
	$msg .= '<br>';
	date_default_timezone_set( 'Asia/Ho_Chi_Minh' );
	$msg .= '<span id="currentClock" style="font-size: 30px;display: block;text-align: center;line-height: 27px;border-bottom: 3px solid #333;border-top: 3px solid #333;margin-top: 15px;padding: 10px 0;">' . date( 'H:i:s' ) . '</span>';
	$msg .= '<script>function StartTime(t){let e=new Date;e=e.toTimeString();e=e.replace(/.*(\d{2}:\d{2}:\d{2}).*/,"$1");document.getElementById(t).innerHTML=e.toString();setTimeout(function(){StartTime(t)},500)}setTimeout(function(){StartTime("currentClock")},500);</script>';

	// Hide all elements
	$msg .= '<style>body>*:not(.wp-die-message){display:none!important;opacity:0!important;visibility:hidden!important}</style>';
}

function hocwp_theme_dev_end_of_working_hours() {
	if ( defined( 'HOCWP_THEME_OVERTIME' ) && HOCWP_THEME_OVERTIME ) {
		return;
	}

	if ( isset( $_REQUEST['submit'] ) || isset( $_REQUEST['action'] ) || isset( $_REQUEST['do_action'] ) ) {
		return;
	}

	$time      = HT_Util()->timestamp_to_string( current_time( 'timestamp' ), 'H:i:s', 'Asia/Ho_Chi_Minh' );
	$time      = strtotime( $time );
	$morning   = ( $time < strtotime( '07:00:00' ) );
	$noon      = ( $time >= strtotime( '11:00:00' ) && $time <= strtotime( '13:00:00' ) );
	$afternoon = ( $time >= strtotime( '17:00:00' ) && $time <= strtotime( '19:00:00' ) );
	$evening   = $time >= strtotime( '22:00:00' );

	if ( $morning || $noon || $afternoon || $evening ) {
		delete_transient( 'hocwp_theme_dev_taking_breaks' );
		delete_transient( 'hocwp_theme_dev_taking_breaks_timestamp' );
		delete_transient( 'hocwp_theme_dev_taking_breaks_count' );
		$msg = 'Working hours: between <strong>07:00:00</strong> and <strong>11:00:00</strong>, <strong>13:00:00</strong> and <strong>17:00:00</strong>, <strong>19:00:00</strong> and <strong>22:00:00</strong>.';
		hocwp_theme_dev_add_clock_to_message( $msg );
		$msg .= '<script>setInterval(function(){window.location.reload()},3e4);</script>';
		wp_die( $msg, 'Outside working hours' );
	}
}

add_action( 'init', 'hocwp_theme_dev_end_of_working_hours' );

function hocwp_dev_option_hocwp_theme_filter( $option ) {
	if ( ! hocwp_team_dev_is_localhost() ) {
		$option = hocwp_dev_replace_localhost( $option );
	}

	return $option;
}

add_filter( 'option_hocwp_theme', 'hocwp_dev_option_hocwp_theme_filter', 99 );
add_filter( 'hocwp_theme_option', 'hocwp_dev_option_hocwp_theme_filter', 99 );

/**
 * Replace localhost URL with current domain URL.
 *
 * @param mixed $value String or array value.
 *
 * @param string $domain Current domain name.
 *
 * @return array|mixed
 */
function hocwp_dev_replace_localhost( $value, $domain = '' ) {
	if ( empty( $domain ) ) {
		$domain = get_bloginfo( 'url' );
		$domain = HT()->get_domain_name( $domain );
	}

	$find = 'localhost';

	if ( $domain != $find ) {
		if ( is_array( $value ) ) {
			$tmp = maybe_serialize( $value );

			if ( str_contains( $tmp, $find ) ) {
				foreach ( $value as $key => $option ) {
					$value[ $key ] = hocwp_dev_replace_localhost( $option, $domain );
				}
			}
		} else {
			if ( ! is_object( $value ) && str_contains( $value, $find ) ) {
				$value = str_replace( $find, $domain, $value );
			}
		}
	}

	return $value;
}

function hocwp_dev_widget_display_callback_filter( $instance ) {
	if ( ! hocwp_team_dev_is_localhost() ) {
		$instance = hocwp_dev_replace_localhost( $instance );
	}

	return $instance;
}

add_filter( 'widget_display_callback', 'hocwp_dev_widget_display_callback_filter', 99 );

function hocwp_dev_replace_meta_localhost( $meta_type, $id, $key, $single ) {
	$value = null;

	if ( ! is_admin() ) {
		switch ( $meta_type ) {
			case 'post':
				$value = get_post_meta( $id, $key, $single );
				break;
			case 'user':
				$value = get_user_meta( $id, $key, $single );
				break;
			case 'comment':
				$value = get_comment_meta( $id, $key, $single );
				break;
			case 'term':
				$value = get_term_meta( $id, $key, $single );
				break;
		}

		if ( null !== $value ) {
			$tmp = maybe_serialize( $value );

			if ( str_contains( $tmp, 'localhost' ) ) {
				$value = hocwp_dev_replace_localhost( $value );
			} else {
				$value = null;
			}
		}

		if ( $single && is_array( $value ) ) {
			return current( $value );
		}
	}

	return $value;
}

function hocwp_dev_post_metadata_filter( $value, $id, $key, $single ) {
	if ( ! hocwp_team_dev_is_localhost() ) {
		$type = 'post';
		remove_filter( 'get_' . $type . '_metadata', 'hocwp_dev_' . $type . '_metadata_filter', 99 );
		$value = hocwp_dev_replace_meta_localhost( $type, $id, $key, $single );
		add_filter( 'get_' . $type . '_metadata', 'hocwp_dev_' . $type . '_metadata_filter', 99, 4 );
	}

	return $value;
}

add_filter( 'get_post_metadata', 'hocwp_dev_post_metadata_filter', 99, 4 );

function hocwp_dev_user_metadata_filter( $value, $id, $key, $single ) {
	if ( ! hocwp_team_dev_is_localhost() ) {
		$type = 'user';
		remove_filter( 'get_' . $type . '_metadata', 'hocwp_dev_' . $type . '_metadata_filter', 99 );
		$value = hocwp_dev_replace_meta_localhost( $type, $id, $key, $single );
		add_filter( 'get_' . $type . '_metadata', 'hocwp_dev_' . $type . '_metadata_filter', 99, 4 );
	}

	return $value;
}

add_filter( 'get_user_metadata', 'hocwp_dev_user_metadata_filter', 99, 4 );

function hocwp_dev_comment_metadata_filter( $value, $id, $key, $single ) {
	if ( ! hocwp_team_dev_is_localhost() ) {
		$type = 'comment';
		remove_filter( 'get_' . $type . '_metadata', 'hocwp_dev_' . $type . '_metadata_filter', 99 );
		$value = hocwp_dev_replace_meta_localhost( $type, $id, $key, $single );
		add_filter( 'get_' . $type . '_metadata', 'hocwp_dev_' . $type . '_metadata_filter', 99, 4 );
	}

	return $value;
}

add_filter( 'get_comment_metadata', 'hocwp_dev_comment_metadata_filter', 99, 4 );

function hocwp_dev_term_metadata_filter( $value, $id, $key, $single ) {
	if ( ! hocwp_team_dev_is_localhost() ) {
		$type = 'term';
		remove_filter( 'get_' . $type . '_metadata', 'hocwp_dev_' . $type . '_metadata_filter', 99 );
		$value = hocwp_dev_replace_meta_localhost( $type, $id, $key, $single );
		add_filter( 'get_' . $type . '_metadata', 'hocwp_dev_' . $type . '_metadata_filter', 99, 4 );
	}

	return $value;
}

add_filter( 'get_term_metadata', 'hocwp_dev_term_metadata_filter', 99, 4 );

function hocwp_dev_the_content_filter( $content ) {
	if ( ! hocwp_team_dev_is_localhost() ) {
		$content = hocwp_dev_replace_localhost( $content );
	}

	return $content;
}

add_filter( 'the_content', 'hocwp_dev_the_content_filter', 99 );
add_filter( 'woocommerce_data_get_description', 'hocwp_dev_the_content_filter', 99 );
add_filter( 'woocommerce_product_get_description', 'hocwp_dev_the_content_filter', 99 );

function hocwp_theme_zip_folder( $source, $destination ) {
	if ( method_exists( HT_Util(), 'zip_folder' ) ) {
		return HT_Util()->zip_folder( $source, $destination );
	}

	if ( ! extension_loaded( 'zip' ) || ! file_exists( $source ) ) {
		return false;
	}

	if ( ! class_exists( 'ZipArchive' ) ) {
		return false;
	}

	$zip = new ZipArchive();

	if ( $zip->open( $destination, ZipArchive::CREATE ) === true ) {
		$source = wp_normalize_path( $source );

		if ( is_dir( $source ) ) {
			$replace = trailingslashit( dirname( $source ) );

			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source ), RecursiveIteratorIterator::SELF_FIRST );

			foreach ( $files as $file ) {
				$file = wp_normalize_path( $file );

				$file_name = basename( $file );

				if ( is_dir( $file ) && ( $file_name === '.git' || $file_name === '.svn' ) ) {
					continue;
				}

				if ( '.git' == $file_name || str_contains( $file, '.git/' ) || str_contains( $file, '.git\\' ) ) {
					continue;
				}

				if ( '.svn' == $file_name || str_contains( $file, '.svn/' ) || str_contains( $file, '.svn\\' ) ) {
					continue;
				}

				if ( '.' == $file_name || '..' == $file_name ) {
					continue;
				}

				if ( in_array( substr( $file, strrpos( $file, '/' ) + 1 ), array( '.', '..' ) ) ) {
					continue;
				}

				$relative = str_replace( $replace, '', $file );

				if ( is_dir( $file ) ) {
					$zip->addEmptyDir( $relative );
				} elseif ( is_file( $file ) ) {
					$zip->addFile( $file, $relative );
				}
			}
		} else if ( is_file( $source ) ) {
			$zip->addFile( $source, basename( $source ) );
		}

		return $zip->close();
	}

	return false;
}

function hocwp_theme_zip_theme( $source = '' ) {
	$theme   = wp_get_theme();
	$sheet   = $theme->get_stylesheet();
	$version = $theme->get( 'Version' );

	if ( empty( $source ) ) {
		$source = untrailingslashit( get_template_directory() );
	}

	$style = $source . '/style.css';

	if ( ! function_exists( 'get_file_data' ) ) {
		require ABSPATH . 'wp-includes/functions.php';
	}

	$name = get_file_data( $style, array( 'text_domain' => 'Text Domain' ) );
	$name = ( is_array( $name ) && isset( $name['text_domain'] ) ) ? $name['text_domain'] : '';

	if ( empty( $name ) ) {
		$name = get_file_data( $style, array( 'real_name' => 'Real Theme Name' ) );
		$name = ( is_array( $name ) && isset( $name['real_name'] ) ) ? $name['real_name'] : '';

		if ( empty( $name ) ) {
			$name = $sheet;
		}
	}

	$dest = trailingslashit( dirname( $source ) );
	$name .= '_v' . $version;

	$ts = current_time( 'timestamp' );

	$name .= sprintf( '_%s_%s_%s.zip', date( 'Ymd', $ts ), date( 'Hi', $ts ), date( 's', $ts ) );

	$name = sanitize_file_name( $name );
	$dest .= $name;

	return hocwp_theme_zip_folder( $source, $dest );
}

function hocwp_theme_zip_current_theme() {
	// Zip both child and parent theme
	if ( is_child_theme() ) {
		hocwp_theme_zip_theme( untrailingslashit( get_stylesheet_directory() ) );
	}

	return hocwp_theme_zip_theme();
}

/**
 * Compress the current theme as zip file.
 */
function hocwp_theme_auto_create_backup_current_theme() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$tr_name = 'hocwp_theme_backup_current_developing_theme';

	if ( false === get_transient( $tr_name ) ) {
		$result = hocwp_theme_zip_current_theme();

		if ( $result ) {
			set_transient( $tr_name, 1, 6 * HOUR_IN_SECONDS );
		}
	}
}

add_action( 'wp_loaded', 'hocwp_theme_auto_create_backup_current_theme' );

function hocwp_theme_dev_on_upgrade_new_version() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	hocwp_theme_dev_export_database();
	hocwp_theme_zip_current_theme();
}

add_action( 'hocwp_theme_upgrade_new_version', 'hocwp_theme_dev_on_upgrade_new_version', 99 );

function hocwp_theme_admin_development_scripts() {
	global $hocwp_theme, $pagenow, $plugin_page;

	if ( 'themes.php' == $pagenow && 'themecheck' == $plugin_page ) {
		wp_enqueue_style( 'hocwp-theme-admin-themecheck-style', HOCWP_EXT_URL . '/css/admin-themecheck' . HOCWP_THEME_CSS_SUFFIX );
	}

	if ( 'themes.php' == $pagenow && 'hocwp_theme' == $plugin_page && 'development' == $hocwp_theme->option->tab ) {
		wp_enqueue_style( 'hocwp-theme-ajax-overlay-style' );
		wp_enqueue_script( 'admin-settings-page-development', HOCWP_EXT_URL . '/js/admin-settings-page-development' . HOCWP_THEME_JS_SUFFIX, array(
			'hocwp-theme-admin',
			'hocwp-theme-ajax-button'
		), false, true );
	}
}

add_action( 'admin_enqueue_scripts', 'hocwp_theme_admin_development_scripts' );

function hocwp_theme_update_ver_css_js_realtime( $src ) {
	if ( str_contains( $src, 'ver=' ) ) {
		$src = add_query_arg( array( 'ver' => current_time( 'timestamp' ) ), $src );
	}

	return $src;
}

function hocwp_theme_dev_remove_source_version() {
	return apply_filters( 'hocwp_theme_dev_remove_source_version', false );
}

/**
 * Remove version from styles and scripts url.
 *
 * @param $src
 *
 * @return string
 */
function hocwp_theme_dev_remove_url_ver( $src ) {
	if ( str_contains( $src, 'ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}

if ( ! hocwp_theme_dev_remove_source_version() ) {
	add_filter( 'style_loader_src', 'hocwp_theme_update_ver_css_js_realtime', 9999 );
	add_filter( 'script_loader_src', 'hocwp_theme_update_ver_css_js_realtime', 9999 );
} else {
	add_filter( 'style_loader_src', 'hocwp_theme_dev_remove_url_ver', 9999 );
	add_filter( 'script_loader_src', 'hocwp_theme_dev_remove_url_ver', 9999 );
}

function hocwp_theme_compress_all_css_and_js( $paths = null ) {
	if ( ! is_array( $paths ) ) {
		global $hocwp_theme;
		$paths = $hocwp_theme->defaults['compress_css_and_js_paths'];
	}

	if ( ! class_exists( 'HOCWP_Theme_Minify' ) ) {
		require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-minify.php';
	}

	foreach ( $paths as $path ) {
		if ( is_dir( $path ) ) {
			$css = $path . '/css';
			hocwp_theme_compress_all_css_and_js_helper( $css );
			$js = $path . '/js';
			hocwp_theme_compress_all_css_and_js_helper( $js );
		} elseif ( is_readable( $path ) ) {
			hocwp_theme_compress_all_css_and_js_helper( $path );
		}
	}
}

function hocwp_theme_dev_admin_bar_menu_action( WP_Admin_Bar $wp_admin_bar ) {
	if ( current_user_can( 'manage_options' ) ) {
		$args = array(
			'id'    => 'theme-development',
			'title' => 'Development',
			'href'  => admin_url( 'themes.php?page=hocwp_theme&tab=development' )
		);

		$wp_admin_bar->add_node( $args );
	}
}

add_action( 'admin_bar_menu', 'hocwp_theme_dev_admin_bar_menu_action', 90 );

/**
 * Backup one or all tables in current database.
 */
function hocwp_theme_dev_export_database( $db_name = '' ) {
	if ( method_exists( HT_Util(), 'export_database' ) ) {
		HT_Util()->export_database( $db_name );

		return;
	}

	if ( ! function_exists( 'exec' ) ) {
		return;
	}

	if ( empty( $db_name ) ) {
		$db_name = DB_NAME;
	}

	$name = $db_name;
	$user = DB_USER;
	$pass = DB_PASSWORD;

	global $wp_db_version;

	$info = HT_Util()->generate_file_path( WP_CONTENT_DIR, WP_CONTENT_URL, 'databases', $db_name, $wp_db_version, 'sql' );

	$dir = $info['path'];

	if ( stripos( PHP_OS, 'WIN' ) !== false ) {
		$root = dirname( $_SERVER['DOCUMENT_ROOT'] );
		$root = trailingslashit( $root ) . 'mysql/bin/mysqldump';
	} else {
		$root = 'mysqldump';
	}

	$cmd = $root . " -u$user -p$pass $name > $dir";

	exec( $cmd );
}

/**
 * Auto backup files in wp-content folder.
 *
 * @param string $folder The sub-folder in wp-content directory.
 */
function hocwp_theme_dev_backup_wp_content_folder( $folder = '' ) {
	if ( ! function_exists( 'exec' ) ) {
		return;
	}

	if ( is_array( $folder ) ) {
		$folder = array_filter( $folder );
		$folder = array_unique( $folder );

		foreach ( $folder as $fn ) {
			$tr_name = 'hocwp_dev_backup_folder_' . md5( $fn );

			if ( false === get_transient( $tr_name ) ) {
				hocwp_theme_dev_backup_wp_content_folder( $fn );
				set_transient( $tr_name, 1, 20 * MINUTE_IN_SECONDS );
			}
		}

		return;
	}

	$source = WP_CONTENT_DIR;
	$source = trailingslashit( $source );

	if ( ! empty( $folder ) ) {
		$source .= $folder;
		$source = trailingslashit( $source );
	}

	$source = HT_Util()->normalize_path( $source );

	if ( ! is_dir( $source ) ) {
		return;
	}

	$pc = getenv( 'COMPUTERNAME' );

	if ( ! function_exists( 'get_home_path' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	$home_path = get_home_path();
	$home_path = dirname( $home_path );
	$home_path = HT_Util()->normalize_path( $home_path );

	$driver_letters = array( 'D', 'E', 'G', 'H', 'Y', 'Z' );

	$k = array_rand( $driver_letters );

	$driver_letter = $driver_letters[ $k ];
	$driver_letter .= ':\\' . $pc;

	$dest   = str_replace( $home_path, $driver_letter, $source );
	$dest   = HT_Util()->normalize_path( $dest, true );
	$source = HT_Util()->normalize_path( $source, true );

	$tr_name   = 'hocwp_dev_notify_backup';
	$transient = get_transient( $tr_name );

	if ( false === $transient ) {
		HT()->debug( '---------------------------------------------------------------------------------------------' );
		HT()->debug( 'Doing backup from: ' . $source . ' to ' . $dest );
	}

	$cmd = "robocopy $source $dest /S /E";

	exec( $cmd );

	if ( false === $transient ) {
		HT()->debug( '---------------------------------------------------------------------------------------------' );
		set_transient( $tr_name, 1, 12 * HOUR_IN_SECONDS );
	}
}

function hocwp_theme_compress_all_css_and_js_helper( $dir ) {
	if ( is_dir( $dir ) ) {
		hocwp_theme_debug( '---------------------------------------------------------------------------------------------' );
		hocwp_theme_debug( sprintf( 'Scanning directory % s', $dir ) );
		hocwp_theme_debug( '---------------------------------------------------------------------------------------------' );
		$files = scandir( $dir );

		if ( ! class_exists( 'HOCWP_Theme_Minify' ) ) {
			require HOCWP_THEME_CORE_PATH . '/inc/class-hocwp-theme-minify.php';
		}

		foreach ( $files as $file ) {
			if ( ! _hocwp_theme_is_css_or_js_file( $file ) ) {
				continue;
			}

			$file = trailingslashit( $dir ) . $file;
			hocwp_theme_debug( sprintf( 'Minifying file % s', $file ) );
			HOCWP_Theme_Minify::generate( $file );
		}
	} elseif ( is_readable( $dir ) ) {
		hocwp_theme_debug( sprintf( 'Minifying file % s', $dir ) );
		HOCWP_Theme_Minify::generate( $dir );
	}
}

function _hocwp_theme_is_css_or_js_file( $file ) {
	$info = pathinfo( $file );

	return ! ( ! isset( $info['extension'] ) || ( 'js' != $info['extension'] && 'css' != $info['extension'] ) );
}

function hocwp_theme_execute_development_ajax_callback() {
	$result = array();

	hocwp_theme_debug( '/* ============================================= ' . date( 'Y-m-d H:i:s' ) . ' ============================================= */' );
	hocwp_theme_debug( 'Building theme environment...' );
	$compress_css_and_js = $_POST['compress_css_and_js'] ?? '';

	if ( 'true' == $compress_css_and_js || ( 'false' != $compress_css_and_js && ! empty( $compress_css_and_js ) ) ) {
		if ( 'true' != $compress_css_and_js ) {
			$compress_css_and_js = HT()->json_string_to_array( $compress_css_and_js );

			$tmp = array();

			foreach ( $compress_css_and_js as $path ) {
				$tmp[] = $path;
			}

			$compress_css_and_js = $tmp;
		}

		hocwp_theme_debug( 'Starting to generate minified files...' );
		hocwp_theme_compress_all_css_and_js( $compress_css_and_js );
		hocwp_theme_debug( 'All files compressed...' );
	}

	hocwp_theme_debug( 'Exporting database...' );
	hocwp_theme_dev_export_database();
	$publish_release = $_POST['publish_release'] ?? '';

	if ( 'true' == $publish_release ) {
		hocwp_theme_debug( 'Creating zip file...' );
		$ziped = hocwp_theme_zip_current_theme();

		if ( $ziped ) {
			hocwp_theme_debug( 'Theme has been compressed successfully . ' );
		} else {
			hocwp_theme_debug( 'The theme can not be compressed . ' );
		}
	}

	hocwp_theme_debug( 'Tasks Finished' );
	hocwp_theme_debug( '/* ============================================= ' . date( 'Y-m-d H:i:s' ) . ' ============================================= */' );
	wp_send_json( $result );
}

if ( is_admin() ) {
	add_action( 'wp_ajax_hocwp_theme_execute_development', 'hocwp_theme_execute_development_ajax_callback' );
}

function hocwp_theme_debug_save_queries() {
	if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES && current_user_can( 'administrator' ) ) {
		global $wpdb;

		if ( is_admin() ) {
			hocwp_theme_debug( '/* ============================= BACK-END QUERIES ============================= */' );
		} else {
			hocwp_theme_debug( '/* ============================= FRONT-END QUERIES ============================= */' );
		}

		hocwp_theme_debug( $wpdb->queries );
	}
}

if ( is_admin() ) {
	add_action( 'admin_footer', 'hocwp_theme_debug_save_queries', 9999 );
}

add_action( 'wp_footer', 'hocwp_theme_debug_save_queries', 9999 );

function hocwp_theme_dev_global_scripts() {
	$domain = HT()->get_domain_name( home_url(), true );

	if ( 'localhost' == $domain ) {
		wp_enqueue_script( 'taking-breaks', HOCWP_EXT_URL . '/js/taking-breaks' . HOCWP_THEME_JS_SUFFIX, array(
			'jquery',
			'hocwp-theme'
		), false, true );
	}
}

add_action( 'hocwp_theme_global_enqueue_scripts', 'hocwp_theme_dev_global_scripts' );

function hocwp_theme_dev_break_minutes() {
	return ( defined( 'HOCWP_THEME_BREAK_MINUTES' ) ) ? absint( HOCWP_THEME_BREAK_MINUTES ) : 45;
}

function hocwp_theme_dev_taking_breaks_ajax_callback() {
	$result = array(
		'taking_break' => false,
		'message'      => ''
	);

	$tb = 'hocwp_theme_dev_taking_breaks';

	if ( false === get_transient( $tb ) ) {
		$tr_name   = 'hocwp_theme_dev_taking_breaks_timestamp';
		$timestamp = get_transient( $tr_name );
		$current   = current_time( 'timestamp' );

		if ( false === $timestamp ) {
			delete_transient( $tb );
			set_transient( $tr_name, $current );
		} else {
			$diff = absint( $current - $timestamp );

			$result['diff'] = $diff;

			$diff     /= MINUTE_IN_SECONDS;
			$interval = hocwp_theme_dev_break_minutes();

			if ( $interval < 1 ) {
				wp_send_json( $result );
			}

			if ( $interval <= $diff ) {
				$result['taking_break'] = true;

				$count = absint( get_transient( 'hocwp_theme_dev_taking_breaks_count' ) );
				$count ++;
				$minute = 5;

				if ( 4 == $count ) {
					$minute = 15;
					$count  = 0;
				}

				set_transient( 'hocwp_theme_dev_taking_breaks_count', $count );
				set_transient( 'hocwp_theme_dev_taking_breaks_until', strtotime( ' + ' . $minute . ' minutes', $current ) );
				set_transient( $tb, $minute, $minute * MINUTE_IN_SECONDS );
			} elseif ( ( $interval - 5 ) <= $diff ) {
				$result['message'] = 'You will take a break for the next 5 minutes.';
			}
		}
	} else {
		$result['taking_break'] = true;
	}

	wp_send_json( $result );
}

add_action( 'wp_ajax_hocwp_theme_dev_taking_breaks', 'hocwp_theme_dev_taking_breaks_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_theme_dev_taking_breaks', 'hocwp_theme_dev_taking_breaks_ajax_callback' );

function hocwp_theme_dev_init_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_REQUEST['submit'] ) || isset( $_REQUEST['action'] ) || isset( $_REQUEST['do_action'] ) ) {
		return;
	}

	$tr_name = 'hocwp_theme_dev_export_database';

	if ( false === get_transient( $tr_name ) ) {
		hocwp_theme_dev_export_database();
		set_transient( $tr_name, 1, DAY_IN_SECONDS );
	}

	if ( ! is_admin() ) {
		$interval = hocwp_theme_dev_break_minutes();

		if ( $interval < 1 ) {
			return;
		}

		$tr_name = 'hocwp_theme_dev_taking_breaks';

		if ( false !== ( $minute = get_transient( $tr_name ) ) ) {
			if ( ! wp_doing_ajax() && ! wp_doing_cron() ) {
				delete_transient( 'hocwp_theme_dev_taking_breaks_timestamp' );
				$minute    = absint( $minute );
				$time_left = get_transient( 'hocwp_theme_dev_taking_breaks_until' );
				date_default_timezone_set( 'Asia/Ho_Chi_Minh' );
				$diff = current_time( 'timestamp' );
				$left = '';

				if ( is_numeric( $time_left ) ) {
					$diff = abs( $time_left - $diff );
					$min  = $diff / MINUTE_IN_SECONDS;
					$min  = (int) $min;
					$sec  = $diff % MINUTE_IN_SECONDS;
					$sec  = round( $sec, 0, PHP_ROUND_HALF_DOWN );
					$sec --;
					$left = $min . 'm ' . $sec . 's';

					$time_left = date( 'F j, Y H:i:s', $time_left );
				}

				$message = sprintf( 'You should take a break and relax for %d minutes . Waiting until <span id="timeUntil">%s</span>, time left <span id="timeLeft">' . $left . '</span>.', $minute, $time_left );
				hocwp_theme_dev_add_clock_to_message( $message );
				$script  = 'setInterval(function(){window.location.reload();},5e3);';
				$message .= HT()->wrap_text( $script, '<script>', '</script>' );
				$script  = 'var countDownDate=new Date(document.getElementById("timeUntil").innerHTML).getTime(),x=setInterval(function(){var e=(new Date).getTime(),t=countDownDate-e,n=Math.floor(t%36e5/6e4);0>n&&(window.location.reload());var o=Math.floor(t%6e4/1e3);document.getElementById("timeLeft").innerHTML=n+"m "+o+"s",0>t&&(clearInterval(x),window.location.reload())},1e3);';
				$message .= HT()->wrap_text( $script, '<script>', '</script>' );
				$title   = 'Taking Short Beaks';

				if ( 15 <= $minute ) {
					$title = 'Taking Long Breaks';
				}

				wp_die( $message, $title );
			}
		}
	}
}

add_action( 'init', 'hocwp_theme_dev_init_action' );

function hocwp_team_dev_wp_schedule_event() {
	$domain = HT()->get_domain_name( home_url() );

	if ( 'localhost' == $domain || HT()->is_IP( $domain ) ) {
		if ( ! wp_next_scheduled( 'hocwp_team_backup_wp_content' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'hocwp_team_backup_wp_content' );
		}
	}
}

add_action( 'init', 'hocwp_team_dev_wp_schedule_event' );

function hocwp_theme_backup_wp_content_folders() {
	return apply_filters( 'hocwp_theme_backup_wp_content_folders', '' );
}

function hocwp_team_backup_wp_content_event_callback() {
	$folder = hocwp_theme_backup_wp_content_folders();

	if ( ! empty( $folder ) ) {
		set_transient( 'hocwp_team_backup_wp_content', 1 );
	}
}

add_action( 'hocwp_team_backup_wp_content', 'hocwp_team_backup_wp_content_event_callback' );

function hocwp_team_dev_is_localhost() {
	if ( function_exists( 'HT_Util' ) && is_callable( array( HT_Util(), 'is_localhost' ) ) ) {
		return HT_Util()->is_localhost();
	}

	$domain = home_url();
	$domain = HT()->get_domain_name( $domain, true );

	return ( 'localhost' == $domain );
}

function hocwp_team_dev_backup_files() {
	if ( function_exists( 'HT_Admin' ) && method_exists( HT_Admin(), 'skip_admin_notices' ) && HT_Admin()->skip_admin_notices() ) {
		return;
	}

	if ( current_user_can( 'manage_options' ) ) {
		$tr_name = 'hocwp_team_backup_wp_content';

		if ( false !== get_transient( $tr_name ) ) {
			if ( hocwp_team_dev_is_localhost() ) {
				$img = '<img alt="" src="' . admin_url( 'images/loading.gif' ) . '" style="display: inline-block;vertical-align: middle;margin-left: 5px;max-width: 16px;height: auto;">';
				$msg = '<strong>Notices:</strong> Copying themes and plugins to new directory. Please wait and do not close the browser... ' . $img;

				$args = array(
					'type'    => 'warning',
					'message' => $msg,
					'id'      => 'backupFolderNotice'
				);

				HT_Util()->admin_notice( $args );
			}
		}
	}
}

add_action( 'admin_notices', 'hocwp_team_dev_backup_files' );

function hocwp_dev_check_theme_core_new_version() {
	if ( function_exists( 'HT_Admin' ) && method_exists( HT_Admin(), 'skip_admin_notices' ) && HT_Admin()->skip_admin_notices() ) {
		return;
	}

	$tr_name = 'hocwp_theme_release_data';

	if ( false === ( $data = get_transient( $tr_name ) ) && hocwp_team_dev_is_localhost() ) {
		$data = wp_remote_get( 'https://api.github.com/repos/skylarkcob/hocwp-theme/releases/latest' );
		$data = wp_remote_retrieve_body( $data );
		$data = json_decode( $data );

		if ( is_object( $data ) && isset( $data->tag_name ) ) {
			set_transient( $tr_name, $data, HOUR_IN_SECONDS );
		}
	}

	if ( is_object( $data ) && isset( $data->tag_name ) && hocwp_team_dev_is_localhost() ) {
		$version = $data->tag_name;
		$version = str_replace( 'v', '', $version );

		if ( version_compare( $version, HOCWP_THEME_CORE_VERSION, '>' ) && hocwp_team_dev_is_localhost() ) {
			$args = array(
				'message' => sprintf( __( '<strong>Note:</strong> New theme core version has been released. Please take time to <a href="%s" target="_blank">update it</a>. If you are not theme author, just leave this message.', 'sb-core' ), 'https://github.com/skylarkcob/hocwp-theme/releases' ),
				'type'    => 'info'
			);

			HT_Admin()->admin_notice( $args );
		}
	}
}

add_action( 'admin_notices', 'hocwp_dev_check_theme_core_new_version' );

function hocwp_team_dev_backup_files_in_footer() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$folders = hocwp_theme_backup_wp_content_folders();

	if ( ! empty( $folders ) ) {
		$folders = (array) $folders;
		$folders = array_filter( $folders );
		$folders = json_encode( $folders );
		?>
        <script>
            jQuery(document).ready(function ($) {
                (function () {
                    let folders = <?php echo $folders; ?>;

                    if (!folders) {
                        folders = [];
                    }

                    folders = JSON.stringify(folders);
                    folders = JSON.parse(folders);

                    let tmp = [];

                    $.each(folders, function (i, val) {
                        if ($.trim(val)) {
                            tmp.push(val);
                        }
                    });

                    if (tmp.length) {
                        let i, current = 0, backupNotice = $("#backupFolderNotice");

                        let interval = setInterval(function () {
                            if (current >= (tmp.length - 1)) {
                                clearInterval(interval);

                                $.ajax({
                                    type: "POST",
                                    dataType: "JSON",
                                    url: "<?php echo SB_Core()->get_ajax_url(); ?>",
                                    cache: true,
                                    data: {
                                        action: "hocwp_dev_backup_wp_content_folder",
                                        clear_transient: 1
                                    },
                                    success: function (response) {
                                        if (response.success) {
                                            backupNotice.find("button").trigger("click");
                                        }
                                    }
                                });
                            }
                        }, 100);

                        for (i = 0; i < tmp.length; i++) {
                            $.ajax({
                                type: "POST",
                                dataType: "JSON",
                                url: "<?php echo SB_Core()->get_ajax_url(); ?>",
                                cache: true,
                                data: {
                                    action: "hocwp_dev_backup_wp_content_folder",
                                    folders: tmp[i],
                                    index: i
                                },
                                success: function (response) {
                                    if (response.success) {
                                        current++;
                                    }
                                }
                            });
                        }
                    }
                })();
            });
        </script>
		<?php
	}
}

add_action( 'admin_footer', 'hocwp_team_dev_backup_files_in_footer' );

function hocwp_dev_backup_wp_content_folder_ajax_callback() {
	$data = array();

	$clear_transient = $_POST['clear_transient'] ?? '';

	if ( 1 == $clear_transient ) {
		delete_transient( 'hocwp_team_backup_wp_content' );
	} else {
		$folders = $_POST['folders'] ?? '';
		$folders = (array) $folders;
		$folders = array_filter( $folders );
		hocwp_theme_dev_backup_wp_content_folder( $folders );

		$index = $_POST['index'] ?? '';

		$data['index'] = $index;
	}

	wp_send_json_success( $data );
}

add_action( 'wp_ajax_hocwp_dev_backup_wp_content_folder', 'hocwp_dev_backup_wp_content_folder_ajax_callback' );

function hocwp_team_dev_backup_wp_content_folders_plugin( $folders ) {
	if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) {
		$folders = (array) $folders;
		$plugins = array();

		$plugins[] = 'plugins\sau-contact';
		$plugins[] = 'plugins\sau-syntax';
		$plugins[] = 'plugins\sb-banner-widget';
		$plugins[] = 'plugins\sb-clean';
		$plugins[] = 'plugins\sb-comment';
		$plugins[] = 'plugins\sb-core';
		$plugins[] = 'plugins\sb-login-page';
		$plugins[] = 'plugins\sb-paginate';
		$plugins[] = 'plugins\sb-post-widget';
		$plugins[] = 'plugins\sb-tab-widget';
		$plugins[] = 'plugins\sb-tbfa';
		$plugins[] = 'plugins\wp-attachment-filter';

		$plugins = apply_filters( 'hocwp_team_backup_these_plugin_folders', $plugins );

		shuffle( $plugins );

		$plugins = array_slice( $plugins, 0, 3 );
		$folders = array_merge( $folders, $plugins );
	}

	return $folders;
}

add_filter( 'hocwp_theme_backup_wp_content_folders', 'hocwp_team_dev_backup_wp_content_folders_plugin' );

function hocwp_theme_delete_duplicate_min_file( $path, $extension = 'css', $root = false, $recursive = false ) {
	if ( HT()->is_file( $path ) ) {
		$info = pathinfo( $path );

		if ( isset( $info['extension'] ) && $extension == $info['extension'] ) {
			wp_delete_file( $path );

			return;
		}
	}

	if ( ! $root ) {
		$path = trailingslashit( $path ) . $extension;
	}

	if ( HT()->is_dir( $path ) ) {
		$files = scandir( $path );

		if ( HT()->array_has_value( $files ) ) {
			foreach ( $files as $file ) {
				$info = pathinfo( $file );

				if ( '.' == $file || '..' == $file ) {
					continue;
				}

				if ( empty( $info['extension'] ) ) {
					if ( $recursive ) {
						$tmp = trailingslashit( $path ) . $file;

						if ( HT()->is_dir( $tmp ) ) {
							hocwp_theme_delete_duplicate_min_file( $tmp, $extension, $root, $recursive );
						}
					}

					continue;
				}

				if ( $info['extension'] == $extension ) {
					if ( isset( $info['filename'] ) ) {
						$info = pathinfo( $info['filename'] );

						if ( isset( $info['extension'] ) && $info['extension'] == 'min' ) {
							if ( isset( $info['filename'] ) ) {
								$info = pathinfo( $info['filename'] );

								if ( isset( $info['extension'] ) && $info['extension'] == 'min' ) {
									$file = trailingslashit( $path ) . $file;
									wp_delete_file( $file );
								}
							}
						}
					}
				}
			}
		}

		unset( $files, $file, $info );
	}
}

function _hocwp_theme_clean_theme_dir( $td ) {
	hocwp_theme_delete_duplicate_min_file( $td, 'css', true );
	hocwp_theme_delete_duplicate_min_file( $td, 'js', true );
	hocwp_theme_delete_duplicate_min_file( $td . '/style.min.css' );

	hocwp_theme_delete_duplicate_min_file( $td . '/layouts', 'css', true );
	hocwp_theme_delete_duplicate_min_file( $td, 'js' );
	hocwp_theme_delete_duplicate_min_file( HOCWP_THEME_CORE_PATH . '/lib', 'css', true, true );
	hocwp_theme_delete_duplicate_min_file( HOCWP_THEME_CORE_PATH . '/lib', 'js', true, true );
	hocwp_theme_delete_duplicate_min_file( $td . '/custom/lib', 'css', true, true );
	hocwp_theme_delete_duplicate_min_file( $td . '/custom/lib', 'js', true, true );
}

function hocwp_theme_dev_load_themes_action() {
	global $hocwp_theme;

	$paths = $hocwp_theme->defaults['compress_css_and_js_paths'] ?? '';

	if ( HT()->array_has_value( $paths ) ) {
		foreach ( $paths as $path ) {
			if ( HT()->is_dir( $path ) ) {
				hocwp_theme_delete_duplicate_min_file( $path );
				hocwp_theme_delete_duplicate_min_file( $path, 'js' );
			}
		}
	}

	$td = get_template_directory();
	_hocwp_theme_clean_theme_dir( $td );

	if ( get_stylesheet_directory() != $td ) {
		_hocwp_theme_clean_theme_dir( get_stylesheet_directory() );
	}

	unset( $td, $paths, $path );
}

add_action( 'load-themes.php', 'hocwp_theme_dev_load_themes_action' );
add_action( 'load-plugins.php', 'hocwp_theme_dev_load_themes_action' );

if ( function_exists( 'qtranxf_postsFilter' ) && has_filter( 'the_posts', 'qtranxf_postsFilter' ) ) {
	remove_filter( 'the_posts', 'qtranxf_postsFilter', 5 );

	function hocwp_theme_dev_the_posts_filter( $posts, $query ) {
		qtranxf_postsFilter( $posts, $query );

		return $posts;
	}

	add_filter( 'the_posts', 'hocwp_theme_dev_the_posts_filter', 5, 2 );
}

function hocwp_theme_development_admin_notices() {
	/*if ( function_exists( 'HT_Admin' ) && method_exists( HT_Admin(), 'skip_admin_notices' ) && HT_Admin()->skip_admin_notices() ) {
	}*/
}

add_action( 'admin_notices', 'hocwp_theme_development_admin_notices' );

function hocwp_theme_dev_init_action_check() {

}

add_action( 'init', 'hocwp_theme_dev_init_action_check' );