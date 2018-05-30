<?php
/*
Plugin Name: Extensions by HocWP Team
Plugin URI: http://hocwp.net/project/
Description: Extensions for using in theme which is created by HocWP Team. This plugin will not work if you use it on theme not written by HocWP Team.
Author: HocWP Team
Version: 2.1.4
Author URI: http://hocwp.net/
Donate link: http://hocwp.net/donate/
Text Domain: sb-core
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'HOCWP_EXT_FILE', __FILE__ );
define( 'HOCWP_EXT_PATH', dirname( HOCWP_EXT_FILE ) );
define( 'HOCWP_EXT_URL', plugins_url( '', HOCWP_EXT_FILE ) );
define( 'HOCWP_EXT_REQUIRE_THEME_CORE_VERSION', '6.4.3' );

final class SB_Core {
	protected static $instance;

	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public $file = HOCWP_EXT_FILE;
	public $path = HOCWP_EXT_PATH;
	public $url = HOCWP_EXT_URL;

	public $require_theme_core_version = HOCWP_EXT_REQUIRE_THEME_CORE_VERSION;

	public $plugin_basename;

	public function __construct() {
		if ( self::$instance instanceof self ) {
			return;
		}

		if ( ! $this->check_theme() ) {
			add_action( 'admin_notices', array( $this, 'check_theme_notices' ) );

			return;
		}

		$this->plugin_basename = plugin_basename( $this->file );

		add_action( 'hocwp_theme_setup_after', array( $this, 'load' ) );

		add_filter( 'plugin_action_links_' . $this->plugin_basename, array(
			$this,
			'plugin_action_links_filter'
		) );

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'sb-core', false, basename( $this->path ) . '/languages' );
	}

	public function plugin_action_links_filter( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'themes.php?page=hocwp_theme&tab=extension' ) ) . '">' . __( 'Settings', 'sb-core' ) . '</a>';

		return $links;
	}

	public function load() {
		if ( ! defined( 'HOCWP_THEME_CORE_VERSION' ) ) {
			add_action( 'admin_notices', array( $this, 'check_theme_core_notices' ) );

			return;
		}

		if ( version_compare( HOCWP_THEME_CORE_VERSION, $this->require_theme_core_version, '<' ) || ! function_exists( 'HT_extension' ) ) {
			add_action( 'admin_notices', array( $this, 'check_theme_core_notices' ) );
			require $this->path . '/inc/back-compat.php';

			return;
		}

		if ( defined( 'HOCWP_THEME_DEVELOPING' ) && HOCWP_THEME_DEVELOPING ) {
			require $this->path . '/inc/functions-development.php';
		}

		add_filter( 'hocwp_theme_extension_paths', array( $this, 'extension_paths_filter' ) );

		if ( function_exists( 'HOCWP_Theme' ) ) {
			HOCWP_Theme()->load_extensions( HOCWP_EXT_PATH );
		}

		add_action( 'init', array( $this, 'register_custom_post_types_and_taxonomies' ) );
	}

	public function register_custom_post_types_and_taxonomies() {
		$post_types = $this->get_custom_post_types_registration();

		if ( HT()->array_has_value( $post_types ) ) {
			foreach ( $post_types as $post_type => $args ) {
				$args = HT_Util()->post_type_args( $args );
				register_post_type( $post_type, $args );
			}
		}

		$taxonomies = $this->get_custom_taxonomies_registration();

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
		$msg = sprintf( __( '<strong>Plugin Extensions by HocWP Team:</strong> You must using theme core version at least %s. Please upgrade your theme or contact theme author for more details. You may also downgrade this plugin to older version but it is not recommended.', 'sb-core' ), '<strong>' . $this->require_theme_core_version . '</strong>' );
		?>
		<div class="alert alert-info is-dismissible notice notice-info">
			<?php echo wpautop( $msg ); ?>
		</div>
		<?php
	}

	private function check_theme() {
		$theme = wp_get_theme();

		if ( 'hocwp-theme' != $theme->get_stylesheet() ) {
			return false;
		}

		return true;
	}

	public function check_theme_notices() {
		$msg = __( '<strong>Plugin Extensions by HocWP Team:</strong> You must use the theme written by the HocWP Team or the directory of the theme must be named as <code>hocwp-theme</code>.', 'sb-core' );
		?>
		<div class="alert alert-error updated error is-dismissible alert-danger">
			<?php echo wpautop( $msg ); ?>
		</div>
		<?php
	}
}

function SB_Core() {
	return SB_Core::get_instance();
}

add_action( 'plugins_loaded', function () {
	SB_Core();
} );