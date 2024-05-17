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

		public $post_type = 'hocwp_ads';

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			$this->set_option_name( 'ads_management' );
			$this->folder_url = HOCWP_EXT_URL . '/ext';
			parent::__construct( __FILE__ );

			require $this->folder_path . '/ads.php';
			add_shortcode( 'hte_ads_display', array( $this, 'shortcode_display_ads' ) );

			$detect_adsblock = $this->get_option( 'detect_adsblock' );

			if ( is_admin() ) {
				$tab = new HOCWP_Theme_Admin_Setting_Tab( 'ads_management', __( 'Ads Management', 'sb-core' ), '<span class="dashicons dashicons-cloud"></span>' );

				$args = array(
					'type' => 'checkbox',
					'text' => __( 'Auto detect ads block extension on browser.', 'sb-core' )
				);

				$tab->add_field_array( array(
					'id'    => 'detect_adsblock',
					'title' => __( 'Detect Ads Block', 'sb-core' ),
					'args'  => array(
						'type'          => 'boolean',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args
					)
				) );

				if ( 1 == $detect_adsblock ) {
					$tab->add_field( 'adsblock_message', __( 'Ads Block Message', 'sb-core' ) );
				}

				$tab->add_field_array( array(
					'id'    => 'video_ad_tag_url',
					'title' => __( 'Video Ad Tag URL', 'sb-core' ),
					'args'  => array(
						'type'          => 'url',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => array( 'class' => 'widefat' )
					)
				) );
			} else {
				if ( 1 == $detect_adsblock ) {
					add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_action' ), 999 );
				}
			}
		}

		public function wp_footer_action() {

		}

		public function wp_enqueue_scripts_action() {
			wp_enqueue_style( 'toastr-style' );
			wp_enqueue_script( 'ads-management-adsense', HOCWP_EXT_URL . '/js/adsense' . HOCWP_THEME_JS_SUFFIX, array( 'toastr' ), false, true );

			$text = $this->get_option( 'adsblock_message' );

			if ( empty( $text ) ) {
				$text = __( 'Our website is made possible by displaying online advertisements to our visitors. Please consider supporting us by disabling your ad blocker.', 'sb-core' );
			}

			wp_add_inline_script( 'ads-management-adsense', '"undefined"==typeof flagADS&&("object"==typeof toastr?toastr.warning("' . $text . '"):alert("' . $text . '"));' );
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

			$hocwp_theme->ads_positions['random']      = __( 'Random', 'sb-core' );
			$hocwp_theme->ads_positions['leaderboard'] = __( 'Leaderboard', 'sb-core' );

			return apply_filters( 'hocwp_theme_ads_positions', $hocwp_theme->ads_positions );
		}

		public function query_vast_vpaid( $args = array() ) {
			$args['vast_ads'] = true;

			return $this->query( $args );
		}

		public function query( $args ) {
			$query = new WP_Query();

			if ( ! is_object( $args ) ) {
				if ( ! is_array( $args ) ) {
					$args = array(
						'position' => $args
					);
				}

				$args = apply_filters( 'hocwp_theme_extension_ads_display_args', $args );

				$position = isset( $args['position'] ) ? $args['position'] : '';
				$vast_ads = isset( $args['vast_ads'] ) ? $args['vast_ads'] : false;

				if ( ! empty( $position ) || $vast_ads ) {
					$random = (bool) HT()->get_value_in_array( $args, 'random' );

					if ( 'random' == $position ) {
						$random = true;
					}

					$current_datetime = current_time( 'timestamp' );

					$defaults = array(
						'post_type'      => $this->post_type,
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

					if ( $vast_ads ) {
						$defaults['meta_query'][] = array(
							'relation' => 'AND',
							array(
								'key'     => 'vast_vpaid_url',
								'compare' => 'EXISTS'
							),
							array(
								'key'     => 'vast_vpaid_url',
								'value'   => '',
								'compare' => '!='
							)
						);
					} else {
						$defaults['meta_query'][] = array(
							'relation' => 'OR',
							array(
								'key'     => 'vast_vpaid_url',
								'compare' => 'NOT EXISTS'
							),
							array(
								'key'   => 'vast_vpaid_url',
								'value' => ''
							)
						);
					}

					if ( $random ) {
						$defaults['orderby'] = 'rand';
					}

					if ( wp_is_mobile() ) {
						$key = 'only_desktop';
					} else {
						$key = 'only_mobile';
					}

					$meta_query = array(
						'relation' => 'AND',
						array(
							'relation' => 'OR',
							array(
								'key'     => $key,
								'type'    => 'numeric',
								'value'   => 1,
								'compare' => '!='
							),
							array(
								'key'     => $key,
								'value'   => '',
								'compare' => '='
							),
							array(
								'key'     => $key,
								'type'    => 'numeric',
								'value'   => 0,
								'compare' => '='
							),
							array(
								'key'     => $key,
								'compare' => 'NOT EXISTS'
							)
						)
					);

					if ( function_exists( 'HT_Util' ) && HT_Util()->is_amp() ) {
						$key = 'only_amp';

						$meta_query[] = array(
							'key'     => $key,
							'type'    => 'numeric',
							'value'   => 1,
							'compare' => '='
						);
					}

					$defaults['meta_query'][] = $meta_query;

					$args = wp_parse_args( $args, $defaults );

					$args = apply_filters( 'hocwp_theme_extension_ads_query_args', $args, $position );

					$query = HT_Query()->posts_by_meta( 'position', $position, $args );
				}
			}

			return $query;
		}

		public function display( $args ) {
			$ads  = $args;
			$html = '';

			$position = '';

			if ( ! is_object( $args ) ) {
				if ( is_string( $args ) ) {
					$position = $args;
				} elseif ( is_array( $args ) ) {
					$position = HT()->get_value_in_array( $args, 'position' );
				}

				$ads = $this->query( $args );

				if ( $ads->have_posts() ) {
					$ads = array_shift( $ads->posts );
				}
			}

			if ( $ads instanceof WP_Post && $this->post_type == $ads->post_type ) {
				$only_desktop = get_post_meta( $ads->ID, 'only_desktop', true );
				$only_mobile  = get_post_meta( $ads->ID, 'only_mobile', true );

				if ( $only_desktop && wp_is_mobile() ) {
					return;
				}

				if ( $only_mobile && ! wp_is_mobile() ) {
					return;
				}

				$code = get_post_meta( $ads->ID, 'code', true );

				if ( empty( $code ) ) {
					$image = get_post_meta( $ads->ID, 'image', true );

					if ( ! empty( $image ) && HT_Media()->exists( $image ) ) {
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

					$expire = get_post_meta( $ads->ID, 'expire', true );

					$class .= ' ' . $ads->post_name;
					$div = new HOCWP_Theme_HTML_Tag( 'div' );

					$class = explode( ' ', $class );
					$class = array_unique( $class );
					$class = array_filter( $class );

					$div->add_attribute( 'class', join( ' ', $class ) );

					if ( ! empty( $expire ) ) {
						$div->add_attribute( 'data-expire', $expire );
						$div->add_attribute( 'data-expire-data', date( 'Y-m-d H:i:s', $expire ) );
					}

					$div->set_text( $code );
					$html = $div->build();
				}
			}

			$html = apply_filters( 'hocwp_ads_html', $html, $args );

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