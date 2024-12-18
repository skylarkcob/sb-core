<?php
/*
 * Name: Google Maps
 * Description: Generate Google Maps for your posts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Google_Maps' ) ) {
	class HOCWP_EXT_Google_Maps extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			if ( ! defined( 'HOCWP_THEME_CORE_VERSION' ) || version_compare( HOCWP_THEME_CORE_VERSION, '6.5.4', '<' ) ) {
				add_action( 'admin_notices', array( $this, 'upgrade_theme_core_notice' ) );

				return;
			}

			if ( is_admin() ) {
				$tab = new HOCWP_Theme_Admin_Setting_Tab( 'google_maps', __( 'Google Maps', 'sb-core' ), '<span class="dashicons dashicons-location"></span>' );

				$args = array(
					'type' => 'checkbox',
					'text' => __( 'Only load Google Maps script on Single page.', 'sb-core' )
				);

				$tab->add_field_array( array(
					'id'    => 'load_only_single',
					'title' => __( 'Only Single', 'sb-core' ),
					'args'  => array(
						'type'          => 'boolean',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args
					)
				) );

				$args = array(
					'class' => 'regular-text'
				);

				$tab->add_field_array( array(
					'id'    => 'default_lat',
					'title' => __( 'Default Latitude', 'sb-core' ),
					'args'  => array(
						'type'          => 'string',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args
					)
				) );

				$tab->add_field_array( array(
					'id'    => 'default_lng',
					'title' => __( 'Default Longitude', 'sb-core' ),
					'args'  => array(
						'type'          => 'string',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args
					)
				) );

				add_action( 'load-post.php', array( $this, 'meta_post' ) );
				add_action( 'load-post-new.php', array( $this, 'meta_post' ) );
			} else {
				add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_action' ) );
			}

			add_shortcode( 'hte_google_maps', array( $this, 'shortcode_google_maps' ) );
		}

		public function wp_enqueue_scripts_action() {
			$only_single = ht_options()->get_tab( 'load_only_single', 1, 'google_maps' );

			if ( 1 != $only_single || is_singular() ) {
				ht_enqueue()->google_maps();
			}
		}

		public function shortcode_google_maps( $atts = array(), $content = null ) {
			$atts = shortcode_atts( array(
				'latitude'    => '',
				'longitude'   => '',
				'scrollwheel' => false,
				'zoom'        => 18
			), $atts );

			$latitude = $atts['latitude'];

			$longitude = $atts['longitude'];

			if ( is_singular() ) {
				$post_id     = get_the_ID();
				$google_maps = get_post_meta( $post_id, 'google_maps', true );
				$google_maps = ht()->json_string_to_array( $google_maps );

				if ( ht()->array_has_value( $google_maps ) ) {
					$latitude  = ht()->get_value_in_array( $google_maps, 'lat' );
					$longitude = ht()->get_value_in_array( $google_maps, 'lng' );
				}
			}

			$atts['latitude']  = $latitude;
			$atts['longitude'] = $longitude;

			if ( empty( $latitude ) && empty( $longitude ) ) {
				return '';
			}

			ob_start();
			ht_html_field()->google_maps( $atts );

			return ob_get_clean();
		}

		public function meta_post() {
			$args = array(
				'draggable' => true
			);

			$lng = ht_options()->get_tab( 'default_lng', '', 'google_maps' );
			$lat = ht_options()->get_tab( 'default_lat', '', 'google_maps' );

			if ( ! empty( $lat ) ) {
				$args['latitude'] = $lat;
			}

			if ( ! empty( $lng ) ) {
				$args['longitude'] = $lng;
			}

			hocwp_theme_meta_box_google_maps( 'google_maps', 'post', $args );
		}

		public function upgrade_theme_core_notice() {
			if ( function_exists( 'ht_admin' ) && method_exists( ht_admin(), 'skip_admin_notices' ) && ht_admin()->skip_admin_notices() ) {
				return;
			}

			$args = array(
				'type'    => 'warning',
				'message' => sprintf( __( '<strong>Warning:</strong> Extension <code>%s</code> requires theme core version as least %s.', 'sb-core' ), $this->name, '6.5.4' )
			);

			ht_admin()->admin_notice( $args );
		}

		public function enqueue_google_maps( $api_key = '' ) {
			if ( function_exists( 'ht_enqueue' ) ) {
				ht_enqueue()->google_maps( $api_key );
			} else {
				hocwp_theme_load_google_maps_script( $api_key );
			}
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = hte_google_maps()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function hte_google_maps() {
	return HOCWP_EXT_Google_Maps::get_instance();
}