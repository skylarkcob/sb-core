<?php
/*
 * Name: Language
 * Description: Supports using multiple languages on website.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Language' ) ) {
	class HOCWP_EXT_Language extends HOCWP_Theme_Extension {
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

			if ( is_admin() ) {
				add_filter( 'hocwp_theme_settings_page_tabs', array( $this, 'settings_tab_filter' ) );
				add_filter( 'hocwp_theme_settings_page_' . $this->option_name . '_settings_field', array(
					$this,
					'settings_fields_filter'
				) );
				add_filter( 'hocwp_theme_required_plugins', array( $this, 'require_plugins_filter' ) );

				add_action( 'admin_init', array( $this, 'admin_init_action' ), 999 );
			}
		}

		public function admin_init_action() {
		}

		public function require_plugins_filter( $plugins ) {
			if ( ! in_array( 'polylang', $plugins ) ) {
				$plugins[] = 'polylang';
			}

			return $plugins;
		}

		public function settings_tab_filter( $tabs ) {
			$tabs[ $this->option_name ] = array(
				'text' => __( 'Language', 'sb-core' ),
				'icon' => '<span class="dashicons dashicons-translation"></span>'
			);

			return $tabs;
		}

		public function settings_fields_filter() {
			$fields = array();

			$args = array(
				'type' => 'checkbox'
			);

			$args['label'] = __( 'Auto add new setting fields for each language?', 'sb-core' );

			$field    = new HOCWP_Theme_Admin_Setting_Field( 'multiple_option', __( 'Multiple Options', 'sb-core' ), 'input', $args, 'boolean', $this->option_name );
			$fields[] = $field;

			return $fields;
		}

		public function generate_setting_with_language( $field, &$fields ) {
			if ( function_exists( 'HOCWP_EXT_Language' ) ) {
				$multiple_option = HOCWP_EXT_Language()->get_option( 'multiple_option' );

				if ( $multiple_option && function_exists( 'pll_languages_list' ) ) {
					$langs   = pll_languages_list();
					$default = pll_default_language();
					unset( $langs[ array_search( $default, $langs ) ] );

					if ( ht()->array_has_value( $langs ) ) {
						if ( $field instanceof HOCWP_Theme_Admin_Setting_Field ) {
							$data = $field->generate();
						} else {
							$data = $field;
						}

						$id    = isset( $data['id'] ) ? $data['id'] : '';
						$title = isset( $data['title'] ) ? $data['title'] : '';

						foreach ( $langs as $lang ) {
							if ( ! empty( $id ) && ! empty( $title ) ) {
								$new_id    = $id . '_' . $lang;
								$new_title = sprintf( '%s (%s)', $title, $lang );

								$data['id']    = $new_id;
								$data['title'] = $new_title;

								$fields[] = $data;
							}
						}
					}
				}
			}
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = hocwp_ext_language()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function hocwp_ext_language() {
	return HOCWP_EXT_Language::get_instance();
}