<?php
/*
 * Name: RSS Feed
 * Description: Customize your website RSS Feed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_RSS' ) ) {
	class HOCWP_EXT_RSS extends HOCWP_Theme_Extension {
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

			add_filter( 'hocwp_theme_settings_page_reading_settings_section', array(
				$this,
				'setting_reading_sections'
			) );

			add_filter( 'hocwp_theme_settings_page_reading_settings_field', array(
				$this,
				'setting_reading_fields'
			) );

			add_filter( 'request', array( $this, 'request_filter' ) );
		}

		public function request_filter( $query_vars ) {
			if ( isset( $query_vars['feed'] ) && ( ! isset( $query_vars['post_type'] ) || empty( $query_vars['post_type'] ) ) ) {
				$post_types = $this->get_post_types( 'names' );

				foreach ( $post_types as $key => $post_type ) {
					if ( ! $this->is_post_type_feed_enabled( $post_type ) ) {
						unset( $post_types[ $key ] );
					}
				}

				if ( HT()->array_has_value( $post_types ) ) {
					$query_vars['post_type'] = $post_types;
				}
			}

			return $query_vars;
		}

		public function is_post_type_feed_enabled( $post_type ) {
			$options = HOCWP_Theme()->object->options['reading'];
			$id      = 'rss_type_' . $post_type;
			$value   = isset( $options[ $id ] ) ? absint( $options[ $id ] ) : 0;

			return ( 1 === $value );
		}

		public function setting_reading_sections( $sections ) {
			$sections['rss_feed'] = array(
				'tab'         => 'reading',
				'id'          => 'rss_feed',
				'title'       => __( 'RSS Feed', 'sb-core' ),
				'description' => __( 'Customize your website RSS Feed. You can choose what post type appears on the feed.', 'sb-core' )
			);

			return $sections;
		}

		public function get_post_types( $output = OBJECT ) {
			$post_types = get_post_types( array( '_builtin' => false, 'public' => true ) );

			array_unshift( $post_types, 'page' );
			array_unshift( $post_types, 'post' );

			if ( OBJECT == $output ) {
				$post_types = array_map( 'get_post_type_object', $post_types );
			}

			return $post_types;
		}

		public function setting_reading_fields( $fields ) {
			$options = HOCWP_Theme()->object->options['reading'];

			$post_types = $this->get_post_types();

			foreach ( $post_types as $post_type ) {
				if ( $post_type instanceof WP_Post_Type ) {
					$id = 'rss_type_' . $post_type->name;

					$value = isset( $options[ $id ] ) ? absint( $options[ $id ] ) : 0;

					$fields[] = array(
						'tab'     => 'reading',
						'section' => 'rss_feed',
						'id'      => $id,
						'title'   => sprintf( __( '%s Feed', 'sb-core' ), $post_type->labels->singular_name ),
						'args'    => array(
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
							'callback_args' => array(
								'value' => $value,
								'type'  => 'checkbox',
								'text'  => sprintf( __( 'Allow %s (%s) appears on site feed.', 'sb-core' ), $post_type->labels->singular_name, $post_type->name )
							)
						)
					);
				}
			}

			return $fields;
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_RSS()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_RSS() {
	return HOCWP_EXT_RSS::get_instance();
}