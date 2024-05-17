<?php
/*
 * Name: Base Slug
 * Description: Change or remove custom post type and custom taxonomy base slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$load = apply_filters( 'hocwp_theme_load_extension_base_slug', HT_extension()->is_active( __FILE__ ) );

if ( ! $load ) {
	return;
}

global $wp_rewrite;

if ( ! $wp_rewrite->using_permalinks() ) {
	return;
}

require HOCWP_EXT_PATH . '/ext/base-slug/class-hocwp-remove-base-slug.php';
require HOCWP_EXT_PATH . '/ext/base-slug/class-hocwp-remove-base-slug-taxonomy.php';
require HOCWP_EXT_PATH . '/ext/base-slug/class-hocwp-remove-base-slug-post-type.php';
require HOCWP_EXT_PATH . '/ext/base-slug/class-hocwp-update-base-slug.php';

if ( is_admin() ) {
	require HOCWP_EXT_PATH . '/ext/base-slug/admin.php';
}

function hocwp_ext_change_base_slug() {
	global $hocwp_theme, $wp_rewrite;

	if ( isset( $hocwp_theme->base_slug_updated ) && $hocwp_theme->base_slug_updated ) {
		return;
	}

	if ( ! is_object( $hocwp_theme ) ) {
		$hocwp_theme = new stdClass();
	}

	if ( ! isset( $hocwp_theme->options ) ) {
		$hocwp_theme->options = (array) get_option( 'hocwp_theme' );
	}

	if ( $wp_rewrite->using_permalinks() && isset( $hocwp_theme->options['permalinks'] ) ) {
		$options = $hocwp_theme->options['permalinks'];

		$types = hocwp_theme_get_custom_post_types( 'objects' );

		foreach ( $types as $type ) {
			$key = 'remove_' . $type->name . '_base';

			if ( isset( $options[ $key ] ) && 1 == $options[ $key ] ) {
				$obj = new HOCWP_Remove_Base_Slug_Post_Type( $type->name );
				$obj->init();
			} else {
				$key = $type->name . '_base';

				if ( isset( $options[ $key ] ) ) {
					$slug = $options[ $key ];

					if ( ! empty( $slug ) ) {
						$args = (array) $type;

						$args['capabilities'] = isset( $args['cap'] ) ? (array) $args['cap'] : array();
						unset( $args['cap'] );
						$args['rewrite']['slug'] = $slug;
						register_post_type( $type->name, $args );
					}
				}
			}
		}

		$taxs   = hocwp_theme_get_custom_taxonomies( 'objects' );
		$taxs[] = get_taxonomy( 'category' );
		$taxs[] = get_taxonomy( 'post_tag' );

		foreach ( $taxs as $tax ) {
			$key = 'remove_' . $tax->name . '_base';

			if ( isset( $options[ $key ] ) && 1 == $options[ $key ] ) {
				$obj = new HOCWP_Remove_Base_Slug_Taxonomy( $tax->name );
				$obj->init();
			} else {
				$key = $tax->name . '_base';

				if ( isset( $options[ $key ] ) ) {
					$slug = $options[ $key ];

					if ( ! empty( $slug ) ) {
						$current = $tax->rewrite['slug'] ?? '';

						if ( $slug != $current ) {
							$args = (array) $tax;

							$args['capabilities'] = isset( $args['cap'] ) ? (array) $args['cap'] : array();
							unset( $args['cap'] );
							$args['rewrite']['slug'] = $slug;
							register_taxonomy( $tax->name, $tax->object_type, $args );
						}
					}
				}
			}
		}

		$hocwp_theme->base_slug_updated = true;
		set_transient( 'hocwp_theme_flush_rewrite_rules', 1 );
	}
}



