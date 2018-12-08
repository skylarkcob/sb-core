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
		}
	}
}

function HTE_WooCommerce() {
	return HOCWP_EXT_WooCommerce::get_instance();
}

require dirname( __FILE__ ) . '/woocommerce/woocommerce.php';