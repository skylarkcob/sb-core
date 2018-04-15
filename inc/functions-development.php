<?php
if ( ! defined( 'HOCWP_THEME_DEVELOPING' ) || 1 != HOCWP_THEME_DEVELOPING ) {
	return;
}

global $pagenow;

if ( 'plugins.php' == $pagenow ) {
	$action   = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	$activate = isset( $_REQUEST['activate'] ) ? $_REQUEST['activate'] : '';

	if ( 'activate' == $action || 'true' == $activate ) {
		return;
	}
}

global $hocwp_theme;

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
	$msg .= '<script>function StartTime(t){var e=new Date;e=e.toTimeString();e=e.replace(/.*(\d{2}:\d{2}:\d{2}).*/,"$1");document.getElementById(t).innerHTML=e.toString();setTimeout(function(){StartTime(t)},500)}setTimeout(function(){StartTime("currentClock")},500);</script>';
}

function hocwp_theme_dev_end_of_working_hours() {
	if ( defined( 'HOCWP_THEME_OVERTIME' ) && HOCWP_THEME_OVERTIME ) {
		return;
	}

	$time      = HOCWP_Theme_Utility::timestamp_to_string( time(), 'H:i:s', 'Asia/Ho_Chi_Minh' );
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
		exit;
	}
}

add_action( 'init', 'hocwp_theme_dev_end_of_working_hours', 10 );

function hocwp_theme_zip_folder( $source, $destination ) {
	if ( ! extension_loaded( 'zip' ) || ! file_exists( $source ) ) {
		return false;
	}

	$zip = new ZipArchive();

	if ( ! $zip->open( $destination, ZIPARCHIVE::CREATE ) ) {
		return false;
	}

	$source     = str_replace( '\\', '/', realpath( $source ) );
	$filesystem = HOCWP_Theme_Utility::filesystem();

	if ( is_dir( $source ) === true ) {
		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source ), RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $files as $file ) {
			$file = str_replace( '\\', '/', $file );

			if ( in_array( substr( $file, strrpos( $file, '/' ) + 1 ), array( '.', '..' ) ) ) {
				continue;
			}

			$file = realpath( $file );

			if ( is_dir( $file ) === true ) {
				$zip->addEmptyDir( str_replace( $source . '/', '', $file . '/' ) );
			} else if ( is_file( $file ) === true ) {
				$zip->addFromString( str_replace( $source . '/', '', $file ), $filesystem->get_contents( $file ) );
			}
		}
	} else if ( is_file( $source ) === true ) {
		$zip->addFromString( basename( $source ), $filesystem->get_contents( $source ) );
	}

	return @$zip->close();
}

function hocwp_theme_zip_current_theme() {
	$time    = strtotime( date( 'Y-m-d H:i:s' ) );
	$theme   = wp_get_theme();
	$sheet   = $theme->get_stylesheet();
	$version = $theme->get( 'Version' );
	$source  = untrailingslashit( get_template_directory() );
	$dest    = dirname( $source ) . '/' . $sheet;
	$dest .= '_v' . $version;
	$dest .= '_' . $time;
	$dest .= '.zip';

	return hocwp_theme_zip_folder( $source, $dest );
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
	if ( false !== strpos( $src, 'ver=' ) ) {
		$src = add_query_arg( array( 'ver' => time() ), $src );
	}

	return $src;
}

add_filter( 'style_loader_src', 'hocwp_theme_update_ver_css_js_realtime', 9999 );
add_filter( 'script_loader_src', 'hocwp_theme_update_ver_css_js_realtime', 9999 );

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
			_hocwp_theme_compress_all_css_and_js( $css );
			$js = $path . '/js';
			_hocwp_theme_compress_all_css_and_js( $js );
		} elseif ( is_readable( $path ) ) {
			_hocwp_theme_compress_all_css_and_js( $path );
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
function hocwp_theme_dev_export_database() {
	global $wpdb;
	$name      = DB_NAME;
	$user      = DB_USER;
	$pass      = DB_PASSWORD;
	$file_name = $name . '_' . date( 'Y-m-d' ) . '_' . date( 'H-i-s' ) . '.sql';
	$dirs      = wp_upload_dir();
	$dir       = dirname( $dirs['basedir'] );
	$dir       = trailingslashit( $dir ) . 'backups/databases/';

	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0777, true );
	}

	$dir .= $file_name;
	$root = dirname( $_SERVER['DOCUMENT_ROOT'] );
	$cmd  = trailingslashit( $root ) . "mysql/bin/mysqldump -u$user -p$pass $name > $dir";
	exec( $cmd );
}

/**
 * Auto backup fiels in wp-content folder.
 *
 * @param string $folder The sub-folder in wp-content directory.
 */
function hocwp_theme_dev_backup_wp_content_folder( $folder = '' ) {
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

	$driver_letters = array( 'D', 'Y', 'Z' );

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

function _hocwp_theme_compress_all_css_and_js( $dir ) {
	if ( is_dir( $dir ) ) {
		hocwp_theme_debug( '---------------------------------------------------------------------------------------------' );
		hocwp_theme_debug( sprintf( 'Scanning directory % s', $dir ) );
		hocwp_theme_debug( '---------------------------------------------------------------------------------------------' );
		$files = scandir( $dir );

		if ( ! class_exists( 'HOCWP_Theme_Minify' ) ) {
			require HOCWP_THEME_CORE_PATH . ' / inc /class-hocwp-theme-minify.php';
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
	$compress_css_and_js = isset( $_POST['compress_css_and_js'] ) ? $_POST['compress_css_and_js'] : '';

	if ( 'true' == $compress_css_and_js || ( 'false' != $compress_css_and_js && ! empty( $compress_css_and_js ) ) ) {
		if ( 'true' != $compress_css_and_js ) {
			$compress_css_and_js = HOCWP_Theme::json_string_to_array( $compress_css_and_js );
			$tmp                 = array();

			foreach ( $compress_css_and_js as $path ) {
				$tmp[] = $path;
			}

			$compress_css_and_js = $tmp;
		}

		hocwp_theme_debug( 'Sarting to generate minified files...' );
		hocwp_theme_compress_all_css_and_js( $compress_css_and_js );
		hocwp_theme_debug( 'All files compressed...' );
	}

	hocwp_theme_debug( 'Exporting database...' );
	hocwp_theme_dev_export_database();
	$publish_release = isset( $_POST['publish_release'] ) ? $_POST['publish_release'] : '';

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
	$domain = HOCWP_Theme::get_domain_name( home_url(), true );

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
		$current   = time();

		if ( false === $timestamp ) {
			delete_transient( $tb );
			set_transient( $tr_name, $current );
		} else {
			$diff = absint( $current - $timestamp );

			$result['diff'] = $diff;

			$diff /= MINUTE_IN_SECONDS;
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
				$diff = time();
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
				$script = 'setInterval(function(){window.location.reload();},5e3);';
				$message .= HT()->wrap_text( $script, '<script>', '</script>' );
				$script = 'var countDownDate=new Date(document.getElementById("timeUntil").innerHTML).getTime(),x=setInterval(function(){var e=(new Date).getTime(),t=countDownDate-e,n=Math.floor(t%36e5/6e4);0>n&&(window.location.reload());var o=Math.floor(t%6e4/1e3);document.getElementById("timeLeft").innerHTML=n+"m "+o+"s",0>t&&(clearInterval(x),window.location.reload())},1e3);';
				$message .= HT()->wrap_text( $script, '<script>', '</script>' );
				$title = 'Taking Short Beaks';

				if ( 15 <= $minute ) {
					$title = 'Taking Long Breaks';
				}

				wp_die( $message, $title );
				exit;
			}
		}
	}
}

add_action( 'init', 'hocwp_theme_dev_init_action' );

function hocwp_team_dev_wp_schedule_event() {
	$domain = HT()->get_domain_name( home_url() );

	if ( 'localhost' == $domain || HT()->is_IP( $domain ) ) {
		if ( ! wp_next_scheduled( 'hocwp_team_backup_wp_content' ) ) {
			wp_schedule_event( time(), 'daily', 'hocwp_team_backup_wp_content' );
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

function hocwp_team_dev_backup_files() {
	if ( current_user_can( 'manage_options' ) ) {
		$tr_name = 'hocwp_team_backup_wp_content';

		if ( false !== get_transient( $tr_name ) ) {
			$img = '<img src="' . admin_url( 'images/loading.gif' ) . '" style="display: inline-block;vertical-align: middle;margin-left: 5px;max-width: 16px;height: auto;">';
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

add_action( 'admin_notices', 'hocwp_team_dev_backup_files' );

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
					var folders = <?php echo $folders; ?>;
					if (!folders) {
						folders = [];
					}
					folders = JSON.stringify(folders);
					folders = JSON.parse(folders);
					var tmp = [];
					$.each(folders, function (i, val) {
						if ($.trim(val)) {
							tmp.push(val);
						}
					});
					if (tmp.length) {
						var i, current = 0, backupNotice = $("#backupFolderNotice");
						var interval = setInterval(function () {
							if (current >= (tmp.length - 1)) {
								clearInterval(interval);
								$.ajax({
									type: "POST",
									dataType: "JSON",
									url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
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
								url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
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

	$clear_transient = isset( $_POST['clear_transient'] ) ? $_POST['clear_transient'] : '';

	if ( 1 == $clear_transient ) {
		delete_transient( 'hocwp_team_backup_wp_content' );
	} else {
		$folders = isset( $_POST['folders'] ) ? $_POST['folders'] : '';
		$folders = (array) $folders;
		$folders = array_filter( $folders );
		hocwp_theme_dev_backup_wp_content_folder( $folders );

		$index = isset( $_POST['index'] ) ? $_POST['index'] : '';

		$data['index'] = $index;
	}

	wp_send_json_success( $data );
}

add_action( 'wp_ajax_hocwp_dev_backup_wp_content_folder', 'hocwp_dev_backup_wp_content_folder_ajax_callback' );

/**
 * Remove version from styles and scripts url.
 *
 * @param $src
 *
 * @return string
 */
function hocwp_theme_dev_remove_url_ver( $src ) {
	if ( strpos( $src, 'ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}

add_filter( 'style_loader_src', 'hocwp_theme_dev_remove_url_ver', 9999 );
add_filter( 'script_loader_src', 'hocwp_theme_dev_remove_url_ver', 9999 );

function hocwp_team_dev_backup_wp_content_folders_plugin( $folders ) {
	if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) {
		$folders   = (array) $folders;
		$plugins   = array();
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
		shuffle( $plugins );
		$plugins = array_slice( $plugins, 0, 3 );
		$folders = array_merge( $folders, $plugins );
	}

	return $folders;
}

add_filter( 'hocwp_theme_backup_wp_content_folders', 'hocwp_team_dev_backup_wp_content_folders_plugin' );

function hocwp_theme_delete_duplicate_min_file( $path, $extension = 'css' ) {
	$path = trailingslashit( $path ) . $extension;

	if ( HT()->is_dir( $path ) ) {
		$files = scandir( $path );

		if ( HT()->array_has_value( $files ) ) {
			foreach ( $files as $file ) {
				$info = pathinfo( $file );

				if ( isset( $info['extension'] ) && $info['extension'] == $extension ) {
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

function hocwp_teme_dev_load_themes_action() {
	global $hocwp_theme;
	$paths = $hocwp_theme->defaults['compress_css_and_js_paths'];

	if ( HT()->array_has_value( $paths ) ) {
		foreach ( $paths as $path ) {
			if ( HT()->is_dir( $path ) ) {
				hocwp_theme_delete_duplicate_min_file( $path );
				hocwp_theme_delete_duplicate_min_file( $path, 'js' );
			}
		}
	}
}

add_action( 'load-themes.php', 'hocwp_teme_dev_load_themes_action' );

if ( function_exists( 'qtranxf_postsFilter' ) && has_filter( 'the_posts', 'qtranxf_postsFilter' ) ) {
	remove_filter( 'the_posts', 'qtranxf_postsFilter', 5 );

	function hocwp_theme_dev_the_posts_filter( $posts, $query ) {
		qtranxf_postsFilter( $posts, $query );

		return $posts;
	}

	add_filter( 'the_posts', 'hocwp_theme_dev_the_posts_filter', 5, 2 );
}

function hocwp_theme_development_admin_notices() {

}

add_action( 'admin_notices', 'hocwp_theme_development_admin_notices' );