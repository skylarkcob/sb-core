<?php
/*
 * Name: Allow WEBP
 * Description: Allow upload .webp image into your site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Allow_Webp' ) ) {
	class HOCWP_EXT_Allow_Webp extends HOCWP_Theme_Extension {
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

			if ( function_exists( 'hocwp_theme_upload_mimes_filter' ) ) {
				add_filter( 'mime_types', 'hocwp_theme_upload_mimes_filter' );
			}
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HOCWP_EXT_Allow_Webp();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HOCWP_EXT_Allow_Webp() {
	return HOCWP_EXT_Allow_Webp::get_instance();
}