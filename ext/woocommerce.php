<?php
/*
 * Name: WooCommerce
 * Description: Add more functionality for your shop site which runs base on WooCommerce Plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_woocommerce_activated' ) ) {
	function hocwp_theme_woocommerce_activated() {
		return class_exists( 'WC_Product' );
	}
}

if ( ! function_exists( 'hocwp_theme_load_extension_woocommerce' ) ) {
	function hocwp_theme_load_extension_woocommerce() {
		$load = apply_filters( 'hocwp_theme_load_extension_woocommerce', HT_extension()->is_active( __FILE__ ) );

		return $load;
	}
}

$load = hocwp_theme_load_extension_woocommerce();

if ( ! function_exists( 'hocwp_ext_wc_require_plugins' ) ) {
	function hocwp_ext_wc_require_plugins( $plugins ) {
		if ( ! in_array( 'woocommerce', $plugins ) ) {
			$plugins[] = 'woocommerce';
		}

		return $plugins;
	}
}

add_filter( 'hocwp_theme_required_plugins', 'hocwp_ext_wc_require_plugins' );

if ( ! $load || ! hocwp_theme_woocommerce_activated() ) {
	return;
}

if ( ! class_exists( 'HOCWP_EXT_WooCommerce' ) ) {
	class HOCWP_EXT_WooCommerce extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			add_action( 'after_setup_theme', array( $this, 'after_setup_theme_action' ), 999 );

			require dirname( __FILE__ ) . '/woocommerce/woocommerce.php';

			if ( ! is_admin() || HOCWP_THEME_DOING_AJAX ) {
				$custom_comment = $this->get_option( 'custom_comment' );

				if ( 1 == $custom_comment ) {
					add_filter( 'woocommerce_product_tabs', array( $this, 'woocommerce_product_tabs_filter' ), 99 );
					$replace_review = $this->get_option( 'replace_review' );

					if ( 1 == $replace_review ) {
						add_action( 'woocommerce_after_single_product_summary', array(
							$this,
							'woocommerce_after_single_product_summary_action'
						), 9 );
					}
				}
			}
		}

		public function after_setup_theme_action() {
			add_theme_support( 'woocommerce' );

			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
		}

		public function woocommerce_after_single_product_summary_action() {
			echo '<div class="clearfix"></div><div class="custom-comments-box clearfix">';
			$this->comment_form_callback();
			echo '</div>';
		}

		public function woocommerce_product_tabs_filter( $tabs ) {
			$replace_review = $this->get_option( 'replace_review' );

			if ( 1 != $replace_review ) {
				$comment = HT_Options()->get_tab( 'comment_system', '', 'discussion' );

				if ( 'facebook' == $comment ) {
					$tabs['facebook_comment'] = array(
						'title'    => __( 'Facebook Comments', 'sb-core' ),
						'callback' => array( $this, 'comment_form_callback' )
					);
				}
			} else {
				unset( $tabs['reviews'] );
			}

			return $tabs;
		}

		public function comment_form_callback() {
			hocwp_theme_comments_template();
		}
	}
}

function HTE_WooCommerce() {
	return HOCWP_EXT_WooCommerce::get_instance();
}

HTE_WooCommerce();