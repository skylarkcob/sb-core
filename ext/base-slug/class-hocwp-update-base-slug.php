<?php
defined( 'ABSPATH' ) || exit;

class HOCWP_Update_Base_Slug {
	public $priority = 99999;
	public $taxonomies = array( 'category', 'post_tag' );
	public $permalink_structure = '';
	public $base_slugs = array();

	public function __construct() {
		add_action( 'init', array( $this, 'init_action' ), $this->priority );
	}

	public function init_action() {
		$this->permalink_structure = get_option( 'permalink_structure' );

		if ( ! empty( $this->permalink_structure ) ) {
			$tmp = explode( '/%', $this->permalink_structure );

			foreach ( $tmp as $base ) {
				if ( ! str_contains( $base, '%' ) ) {
					$base = untrailingslashit( $base );
					$base = rtrim( $base, '/' );
					$base = ltrim( $base, '/' );

					$this->base_slugs[] = $base;
				}
			}

			if ( ht()->array_has_value( $this->base_slugs ) ) {
				$custom = hocwp_theme_get_custom_taxonomies();

				if ( ht()->array_has_value( $custom ) ) {
					$this->taxonomies = array_merge( $this->taxonomies, $custom );
				}

				foreach ( $this->taxonomies as $tax ) {
					add_filter( $tax . '_rewrite_rules', array( $this, 'update_rewrite_rules' ), $this->priority );
					add_filter( $tax . '_link', array( $this, 'update_permalink' ), $this->priority );
					add_filter( 'term_link', array( $this, 'update_permalink' ), $this->priority );

					if ( 'post_tag' == $tax ) {
						add_filter( 'tag_link', array( $this, 'update_permalink' ), $this->priority );
					}
				}

				$pts = hocwp_theme_get_custom_post_types();

				if ( ht()->array_has_value( $pts ) ) {
					foreach ( $pts as $pt ) {
						add_filter( $pt . '_rewrite_rules', array( $this, 'update_rewrite_rules' ), $this->priority );
						add_filter( 'post_type_link', array( $this, 'update_permalink' ), $this->priority );
					}
				}

				add_action( 'wp', array( $this, 'wp_action' ) );
			}
		}
	}

	public function wp_action() {
		if ( ! is_single() && ( is_singular() || is_tax() || is_category() || is_tag() ) ) {
			$link   = ht_util()->get_current_url();
			$change = false;

			foreach ( $this->base_slugs as $slug ) {
				$slug = trailingslashit( $slug );

				if ( str_contains( $link, $slug ) ) {
					$link   = str_replace( $slug, '', $link );
					$change = true;
				}
			}

			if ( $change ) {
				wp_redirect( $link );
				exit;
			}
		}
	}

	public function update_permalink( $link ) {
		foreach ( $this->base_slugs as $slug ) {
			$slug = trailingslashit( $slug );

			if ( str_contains( $link, $slug ) ) {
				$link = str_replace( $slug, '', $link );
			}
		}

		return $link;
	}

	public function update_rewrite_rules( $rules ) {
		foreach ( $this->base_slugs as $slug ) {
			$slug = trailingslashit( $slug );

			foreach ( $rules as $key => $rule ) {
				if ( str_contains( $key, $slug ) ) {
					$key = str_replace( $slug, '', $key );

					$rules[ $key ] = $rule;
				}
			}
		}

		return $rules;
	}
}

new HOCWP_Update_Base_Slug();