<?php
/*
 * Name: Ads
 * Description: Create and display ads on your site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_extension_ads' ) ) {
	function hocwp_theme_load_extension_ads() {
		return apply_filters( 'hocwp_theme_load_extension_ads', HT_extension()->is_active( __FILE__ ) );
	}
}

$load = hocwp_theme_load_extension_ads();

if ( ! $load ) {
	return;
}

if ( ! class_exists( 'HOCWP_Ext_Ads' ) ) {
	final class HOCWP_Ext_Ads extends HOCWP_Theme_Extension {
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

			$this->folder_url = HOCWP_EXT_URL . '/ext';
			parent::__construct( __FILE__ );

			require $this->folder_path . '/ads.php';
			add_shortcode( 'hte_ads_display', array( $this, 'shortcode_display_ads' ) );
		}

		public function shortcode_display_ads( $atts = array(), $content = null ) {
			$atts = shortcode_atts( array(
				'position' => ''
			), $atts );

			ob_start();

			HT_Util()->display_ads( $atts );

			return ob_get_clean();
		}

		public function get_positions() {
			global $hocwp_theme;

			if ( ! isset( $hocwp_theme->ads_positions ) || ! is_array( $hocwp_theme->ads_positions ) ) {
				$hocwp_theme->ads_positions = array();
			}

			$hocwp_theme->ads_positions['leaderboard'] = __( 'Leaderboard', 'sb-core' );

			return apply_filters( 'hocwp_theme_ads_positions', $hocwp_theme->ads_positions );
		}

		public function query( $args ) {
			$query = new WP_Query();

			$position = '';

			if ( ! is_object( $args ) ) {
				if ( ! is_array( $args ) ) {
					$args = array(
						'position' => $args
					);
				}

				$position = isset( $args['position'] ) ? $args['position'] : '';

				if ( ! empty( $position ) ) {
					$random = (bool) HT()->get_value_in_array( $args, 'random' );

					$current_datetime = current_time( 'timestamp' );

					$defaults = array(
						'post_type'      => 'hocwp_ads',
						'posts_per_page' => 1,
						'meta_query'     => array(
							'relation' => 'AND',
							array(
								'relation' => 'OR',
								array(
									'key'     => 'expire',
									'compare' => 'NOT EXISTS'
								),
								array(
									'key'     => 'expire',
									'value'   => '',
									'compare' => '='
								),
								array(
									'key'   => 'expire',
									'value' => 0,
									'type'  => 'numeric'
								),
								array(
									'key'     => 'expire',
									'value'   => $current_datetime,
									'type'    => 'numeric',
									'compare' => '>='
								)
							),
							array(
								'key'   => 'active',
								'value' => 1,
								'type'  => 'numeric'
							)
						)
					);


					if ( $random ) {
						$defaults['orderby'] = 'rand';
					}

					$args = wp_parse_args( $args, $defaults );

					$query = HT_Query()->posts_by_meta( 'position', $position, $args );
				}
			}

			return $query;
		}

		public function display( $args ) {
			$ads      = $args;
			$html     = '';
			$position = '';

			if ( ! is_object( $args ) ) {
				$ads = $this->query( $args );

				if ( $ads->have_posts() ) {
					$posts = $ads->posts;
					$ads   = array_shift( $posts );
				}
			}

			if ( $ads instanceof WP_Post && 'hocwp_ads' == $ads->post_type ) {
				$code = get_post_meta( $ads->ID, 'code', true );

				if ( empty( $code ) ) {
					$image = get_post_meta( $ads->ID, 'image', true );

					if ( ! empty( $image ) ) {
						$image = wp_get_attachment_url( $image );
						$img   = new HOCWP_Theme_HTML_Tag( 'img' );
						$img->add_attribute( 'src', $image );
						$url = get_post_meta( $ads->ID, 'url', true );

						if ( ! empty( $url ) ) {
							$url = esc_url( $url );
							$a   = new HOCWP_Theme_HTML_Tag( 'a' );
							$a->add_attribute( 'href', $url );
							$a->set_text( $img );
							$code = $a->build();
						} else {
							$code = $img->build();
						}
					}
				}

				if ( ! empty( $code ) ) {
					$class = HT()->get_value_in_array( $args, 'class' );
					$class .= ' hocwp-ads text-center ads';

					if ( ! empty( $position ) ) {
						$class .= ' position-' . $position;
						$class .= ' ' . $position;
					}

					$class .= ' ' . $ads->post_name;
					$div = new HOCWP_Theme_HTML_Tag( 'div' );
					$div->add_attribute( 'class', $class );
					$div->set_text( $code );
					$html = $div->build();
				}
			}

			$html = apply_filters( 'hocwp_ads_html', $html, $ads_or_args = $args );

			echo $html;
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Ads()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Ads() {
	return HOCWP_Ext_Ads::get_instance();
}