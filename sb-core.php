<?php
/*
Plugin Name: Extensions by HocWP Team
Plugin URI: https://hocwp.net/project/
Description: Extensions for using in theme which is created by HocWP Team. This plugin will not work if you use it on theme not written by HocWP Team.
Author: HocWP Team
Version: 2.7.0
Requires at least: 6.5
Tested up to: 6.7
Last Updated: 16/12/2024
Requires PHP: 8.0
Author URI: https://hocwp.net/
Donate link: https://hocwp.net/donate/
Text Domain: sb-core
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'SB_Core' ) ) {
	final class SB_Core {
		/**
		 * The plugin version number.
		 *
		 * @var string
		 */
		public $version;

		/**
		 * The plugin settings array.
		 *
		 * @var array
		 */
		public $settings = array();

		/**
		 * The plugin data array.
		 *
		 * @var array
		 */
		public $data = array();

		/**
		 * Storage for class instances.
		 *
		 * @var array
		 */
		public $instances = array();

		public $file;
		public $path;
		public $url;

		public $require_theme_core_version;

		public $plugin_basename;

		public function __construct() {
			define( 'HOCWP_EXT_REQUIRE_THEME_CORE_VERSION', '7.0.6' );

			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}

			$data = get_plugin_data( __FILE__ );

			$require_version = $data['RequiresPHP'] ?? '8.0';

			define( 'HOCWP_EXT_VERSION', $data['Version'] );
			$this->version = HOCWP_EXT_VERSION;

			define( 'HOCWP_EXT_FILE', __FILE__ );

			define( 'HOCWP_EXT_PATH', dirname( HOCWP_EXT_FILE ) );
			define( 'HOCWP_EXT_URL', plugins_url( '', HOCWP_EXT_FILE ) );

			$this->file = HOCWP_EXT_FILE;
			$this->path = HOCWP_EXT_PATH;
			$this->url  = HOCWP_EXT_URL;

			$this->require_theme_core_version = HOCWP_EXT_REQUIRE_THEME_CORE_VERSION;

			/*
			 * Check current PHP version.
			 */
			$php_version = phpversion();

			if ( version_compare( $php_version, $require_version, '<' ) ) {
				if ( ! function_exists( 'deactivate_plugins' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				deactivate_plugins( plugin_basename( __FILE__ ) );

				$msg   = sprintf( __( '<strong>Error:</strong> You are using PHP version %s, please upgrade PHP version to at least %s.', 'sb-core' ), $php_version, $require_version );
				$title = __( 'Invalid PHP Version', 'sb-core' );

				$args = array(
					'back_link' => admin_url( 'plugins.php' )
				);

				wp_die( $msg, $title, $args );
			}

			unset( $php_version, $require_version );

			add_action( 'init', array( $this, 'load_textdomain' ), 999 );

			add_action( 'hocwp_theme_setup_after', function () {
				require_once( __DIR__ . '/updates.php' );
			} );
		}

		public function load_textdomain() {
			$domain = 'sb-core';
			$path   = basename( dirname( __FILE__ ) ) . '/languages';

			load_plugin_textdomain( $domain, false, $path );

			unset( $domain, $path );
		}

		public function define( $name, $value = true ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		public function has_setting( $name ) {
			return isset( $this->settings[ $name ] );
		}

		public function get_setting( $name ) {
			return $this->settings[ $name ] ?? null;
		}

		public function update_setting( $name, $value ) {
			$this->settings[ $name ] = $value;

			return true;
		}

		public function get_data( $name ) {
			return $this->data[ $name ] ?? null;
		}

		public function set_data( $name, $value ) {
			$this->data[ $name ] = $value;
		}

		public function get_instance( $class ) {
			$name = strtolower( $class );

			return $this->instances[ $name ] ?? null;
		}

		public function new_instance( $class ) {
			$instance                 = new $class();
			$name                     = strtolower( $class );
			$this->instances[ $name ] = $instance;

			return $instance;
		}

		public function initialize() {
			$this->plugin_basename = plugin_basename( $this->file );

			if ( ! $this->check_requirements() ) {
				return;
			}

			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'global_scripts' ) );
				add_filter( 'all_plugins', array( $this, 'all_plugins_filter' ) );
				add_action( 'admin_notices', array( $this, 'admin_notices_action' ) );
			} else {
				add_action( 'wp_enqueue_scripts', array( $this, 'global_scripts' ) );
				add_action( 'login_enqueue_scripts', array( $this, 'global_scripts' ) );
			}

			add_action( 'hocwp_theme_setup_after', array( $this, 'load' ), 99 );

			add_filter( 'plugin_action_links_' . $this->plugin_basename, array(
				$this,
				'plugin_action_links_filter'
			), 10, 3 );
		}

		private function check_requirements() {
			if ( ! $this->check_theme() ) {
				add_action( 'admin_notices', array( $this, 'check_theme_notices' ) );

				return false;
			}

			if ( ! defined( 'HOCWP_THEME_CORE_VERSION' ) ) {
				add_action( 'admin_notices', array( $this, 'check_theme_core_notices' ) );

				return false;
			}

			if ( version_compare( HOCWP_THEME_CORE_VERSION, $this->require_theme_core_version, '<' ) || ! function_exists( 'ht_extension' ) ) {
				global $pagenow;

				if ( ! is_admin() && 'wp-login.php' != $pagenow ) {
					$title = __( 'Invalid Theme Core Version', 'sb-core' );
					$name  = get_file_data( __FILE__, array( 'Name' => 'Plugin Name' ) );
					$name  = $name['Name'] ?? $this->plugin_basename;
					$msg   = sprintf( __( '<strong>Error:</strong> Plugin <code>%s</code> requires theme core version <code>%s</code> or higher. Please upgrade your theme or downgrade this plugin to older version.', 'sb-core' ), $name, $this->require_theme_core_version );
					wp_die( $msg, $title, array( 'back_link' => admin_url( 'plugins.php' ) ) );
				}

				add_action( 'admin_notices', array( $this, 'check_theme_core_notices' ) );
				require( $this->path . '/inc/back-compat.php' );

				return false;
			}

			return true;
		}

		public function admin_notices_action() {

		}

		public function all_plugins_filter( $plugins ) {
			if ( ht()->array_has_value( $plugins ) && isset( $plugins[ $this->plugin_basename ] ) ) {
				if ( ht()->array_has_value( $plugins[ $this->plugin_basename ] ) ) {
					$plugins[ $this->plugin_basename ]['Version'] = HOCWP_EXT_VERSION;
				}
			}

			return $plugins;
		}

		public function get_ajax_url() {
			return apply_filters( 'hocwp_theme_ajax_url', admin_url( 'admin-ajax.php' ) );
		}

		public function global_scripts() {
			wp_register_style( 'toastr-style', HOCWP_EXT_URL . '/css/toastr.min.css' );
			wp_register_script( 'toastr', HOCWP_EXT_URL . '/js/toastr.min.js', array( 'jquery' ), false, true );
		}

		public function plugin_action_links_filter( $links, $file, $data ) {
			$links[] = '<a href="' . esc_url( admin_url( 'themes.php?page=hocwp_theme&tab=extension' ) ) . '" title="' . esc_attr__( 'Go to setting page', 'sb-core' ) . '">' . __( 'Settings', 'sb-core' ) . '</a>';

			if ( current_user_can( 'manage_options' ) && function_exists( 'hocwp_team_dev_is_localhost' ) && hocwp_team_dev_is_localhost() ) {
				$ver = $data['Version'] ?? '';

				if ( empty( $ver ) ) {
					$ver = $data['new_version'] ?? '';
				}

				$url = admin_url( 'update-core.php' );

				$url = add_query_arg( array(
					'action'      => 'do-plugin-upgrade',
					'do_action'   => 'reinstall_plugin',
					'plugin_file' => $file,
					'version'     => $ver,
					'checked'     => array( $file ),
					'plugins'     => array( $file )
				), $url );

				$url = wp_nonce_url( $url );

				$links[] = '<a href="' . esc_url( $url ) . '"  title="' . esc_attr__( 'Reinstall plugin', 'sb-core' ) . '">' . __( 'Reinstall', 'sb-core' ) . '</a>';
			}

			return $links;
		}

		public function load() {
			if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) {
				require( $this->path . '/inc/functions-development.php' );
			}

			add_filter( 'hocwp_theme_extension_paths', array( $this, 'extension_paths_filter' ) );

			if ( function_exists( 'hocwp_theme' ) ) {
				add_action( 'after_setup_theme', function () {
					hocwp_theme()->load_extensions( HOCWP_EXT_PATH );
				}, 99 );
			}

			add_action( 'init', array( $this, 'register_custom_post_types_and_taxonomies' ) );

			if ( function_exists( 'hocwp_theme_upload_mimes_filter' ) ) {
				add_filter( 'upload_mimes', 'hocwp_theme_upload_mimes_filter' );
			}
		}

		public function register_custom_post_types_and_taxonomies() {
			$post_types = $this->get_custom_post_types_registration();

			if ( ht()->array_has_value( $post_types ) ) {
				foreach ( $post_types as $post_type => $args ) {
					$args = ht_util()->post_type_args( $args );
					register_post_type( $post_type, $args );
				}
			}

			$taxonomies = $this->get_custom_taxonomies_registration();

			if ( ht()->array_has_value( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy => $data ) {
					$post_type = $data['post_type'] ?? '';

					if ( ! empty( $post_type ) ) {
						$args = $data['args'] ?? $data;

						$args = ht_util()->taxonomy_args( $args );

						register_taxonomy( $taxonomy, $post_type, $args );
					}
				}
			}

			// Dynamic add shortcode
			$shortcodes = apply_filters( 'hocwp_theme_shortcodes', array() );

			if ( is_array( $shortcodes ) ) {
				foreach ( $shortcodes as $name => $shortcode ) {
					if ( is_callable( $shortcode ) ) {
						add_shortcode( $name, $shortcode );
					}
				}
			}
		}

		public function get_custom_post_types_registration() {
			return apply_filters( 'hocwp_theme_custom_post_types', array() );
		}

		public function get_custom_taxonomies_registration() {
			return apply_filters( 'hocwp_theme_custom_taxonomies', array() );
		}

		public function extension_paths_filter( $paths ) {
			$paths[] = $this->path . '/ext';

			return $paths;
		}

		public function check_theme_core_notices() {
			if ( function_exists( 'ht_admin' ) && method_exists( ht_admin(), 'skip_admin_notices' ) && ht_admin()->skip_admin_notices() ) {
				return;
			}

			$msg = sprintf( __( '<strong>Plugin Extensions by HocWP Team:</strong> You must be using theme core version at least %s. Please upgrade your theme or contact theme author for more details. You may also downgrade this plugin to older version, but it is not recommended.', 'sb-core' ), '<strong>' . $this->require_theme_core_version . '</strong>' );
			?>
            <div class="alert alert-info is-dismissible notice notice-info">
				<?php echo wpautop( $msg ); ?>
            </div>
			<?php
		}

		private function check_theme() {
			$theme = wp_get_theme();

			if ( 'hocwp-theme' != $theme->get_stylesheet() && 'hocwp-theme' != $theme->get( 'Template' ) ) {
				return false;
			}

			return true;
		}

		public function check_theme_notices() {
			if ( function_exists( 'ht_admin' ) && method_exists( ht_admin(), 'skip_admin_notices' ) && ht_admin()->skip_admin_notices() ) {
				return;
			}

			$msg = __( '<strong>Plugin Extensions by HocWP Team:</strong> You must use the theme written by the HocWP Team or the directory of the theme must be named as <code>hocwp-theme</code>, you can change your theme <a href="%s" title="%s">here</a>.', 'sb-core' );
			$msg = sprintf( $msg, esc_attr__( 'View list themes', 'sb-core' ), admin_url( 'themes.php' ) );
			?>
            <div class="alert alert-error updated error is-dismissible alert-danger">
				<?php echo wpautop( $msg ); ?>
            </div>
			<?php
		}
	}

	function SB_Core() {
		global $sb_core;

		// Instantiate only once.
		if ( ! isset( $sb_core ) ) {
			$sb_core = new SB_Core();
			$sb_core->initialize();
		}

		return $sb_core;
	}

	$stylesheet = get_option( 'template' );

	if ( 'hocwp-theme' !== $stylesheet ) {
		add_action( 'init', 'SB_Core' );
	} else {
		add_action( 'hocwp_theme_setup', 'SB_Core' );
	}
}