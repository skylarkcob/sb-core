<?php

/*
 * Name: Anime
 * Description: Create anime site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_EXT_Anime extends HOCWP_Theme_Extension {
	public $taxonomies_args = array();

	public function __construct() {
		parent::__construct( __FILE__ );

		$this->get_taxonomies_args();

		add_action( 'init', array( $this, 'register_post_type_and_taxonomy' ) );
	}

	public function get_taxonomies_args() {
		$args = array(
			'labels'       => array(
				'name'          => _x( 'Release Years', 'taxonomy', 'sb-core' ),
				'singular_name' => _x( 'Release Year', 'taxonomy', 'sb-core' ),
				'menu_name'     => _x( 'Years', 'taxonomy', 'sb-core' )
			),
			'rewrite'      => array(
				'slug' => 'release-year'
			),
			'hierarchical' => true
		);

		$this->taxonomies_args['release_year'] = array(
			'post_type' => 'post',
			'args'      => $args
		);

		$args = array(
			'labels'  => array(
				'name' => _x( 'Status', 'taxonomy', 'sb-core' )
			),
			'rewrite' => array(
				'slug' => 'status'
			)
		);

		$this->taxonomies_args['status'] = array(
			'post_type' => 'post',
			'args'      => $args
		);

		$this->taxonomies_args = apply_filters( 'hocwp_theme_anime_taxonomies', $this->taxonomies_args );

		return $this->taxonomies_args;
	}

	public function register_post_type_and_taxonomy() {
		$args = array(
			'labels'              => array(
				'name'          => _x( 'Episodes', 'post type', 'sb-core' ),
				'singular_name' => _x( 'Episode', 'post type', 'sb-core' )
			),
			'public'              => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'show_ui'             => true
		);

		$args = HT_Util()->post_type_args( $args );

		register_post_type( 'episode', $args );

		foreach ( $this->taxonomies_args as $taxonomy => $data ) {
			$post_type = isset( $data['post_type'] ) ? $data['post_type'] : '';

			if ( ! empty( $post_type ) ) {
				$args = isset( $data['args'] ) ? $data['args'] : '';

				$args = HT_Util()->taxonomy_args( $args );

				register_taxonomy( $taxonomy, $post_type, $args );
			}
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = new HOCWP_EXT_Anime();

$hocwp_theme->extensions[ $extension->basename ] = $extension;