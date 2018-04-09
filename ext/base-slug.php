<?php
/*
 * Name: Base Slug
 * Description: Change or remove custom post type and custom taxonomy base slug.
 */

$load = apply_filters( 'hocwp_theme_load_extension_base_slug', hocwp_theme_is_extension_active( __FILE__ ) );

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
		$types   = hocwp_theme_get_custom_post_types( 'objects' );

		foreach ( $types as $type ) {
			$key = 'remove_' . $type->name . '_base';

			if ( isset( $options[ $key ] ) && 1 == $options[ $key ] ) {
				$obj = new HOCWP_Remove_Base_Slug_Post_Type( $type->name );
				$obj->init();
			} else {
				$key = $type->name . '_base';

				if ( isset( $options[ $key ] ) && ! empty( $options[ $key ] ) ) {
					$slug = $options[ $key ];

					if ( ! empty( $slug ) ) {
						$args                 = (array) $type;
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

				if ( isset( $options[ $key ] ) && ! empty( $options[ $key ] ) ) {
					$slug = $options[ $key ];

					if ( ! empty( $slug ) ) {
						$current = isset( $tax->rewrite['slug'] ) ? $tax->rewrite['slug'] : '';

						if ( $slug != $current ) {
							$args                 = (array) $tax;
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

function hocwp_ext_settings_page_permalinks_section( $sections ) {
	$types = hocwp_theme_get_custom_post_types();
	$taxs  = hocwp_theme_get_custom_taxonomies();

	if ( HOCWP_Theme::array_has_value( $types ) || HOCWP_Theme::array_has_value( $taxs ) ) {
		$desc = __( 'If you like, you may enter custom structures for your custom taxonomy term and custom post type URLs here. If you leave these blank the defaults will be used. Note: This function may affect the performance of the site.', 'hocwp-ext' );

		$sections['custom_base'] = array(
			'tab'         => 'permalinks',
			'id'          => 'custom_base',
			'title'       => __( 'Custom Base Slug', 'hocwp-ext' ),
			'description' => $desc
		);
	}

	$desc = __( 'We do not encourage you to do this but if you like you can still remove the base slug from permalink.', 'hocwp-ext' );

	$sections['remove_base'] = array(
		'tab'         => 'permalinks',
		'id'          => 'remove_base',
		'title'       => __( 'Remove Base Slug', 'hocwp-ext' ),
		'description' => $desc
	);

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_permalinks_settings_section', 'hocwp_ext_settings_page_permalinks_section' );

function hocwp_ext_settings_page_permalinks_field( $fields ) {
	$types = hocwp_theme_get_custom_post_types( 'objects' );

	foreach ( $types as $type ) {
		$fields[] = array(
			'tab'     => 'permalinks',
			'section' => 'custom_base',
			'id'      => $type->name . '_base',
			'title'   => sprintf( __( '%s base', 'hocwp-ext' ), ucwords( $type->labels->singular_name ) ),
			'args'    => array(
				'label_for'   => true,
				'description' => sprintf( __( 'Base slug for custom post type %s', 'hocwp-ext' ), '<code>' . $type->name . '</code>' )
			)
		);
	}

	foreach ( $types as $type ) {
		$fields[] = array(
			'tab'     => 'permalinks',
			'section' => 'remove_base',
			'id'      => 'remove_' . $type->name . '_base',
			'title'   => ucwords( $type->labels->singular_name ),
			'args'    => array(
				'label_for'     => true,
				'callback_args' => array(
					'type'  => 'checkbox',
					'label' => sprintf( __( 'Remove base slug for post type %s', 'hocwp-ext' ), '<code>' . $type->name . '</code>' )
				)
			)
		);
	}

	$taxs = hocwp_theme_get_custom_taxonomies( 'objects' );

	foreach ( $taxs as $tax ) {
		$fields[] = array(
			'tab'     => 'permalinks',
			'section' => 'custom_base',
			'id'      => $tax->name . '_base',
			'title'   => sprintf( __( '%s base', 'hocwp-ext' ), ucwords( $tax->labels->singular_name ) ),
			'args'    => array(
				'label_for'   => true,
				'description' => sprintf( __( 'Base slug for custom taxonomy %s', 'hocwp-ext' ), '<code>' . $tax->name . '</code>' )
			)
		);
	}

	$taxs[] = get_taxonomy( 'category' );
	$taxs[] = get_taxonomy( 'post_tag' );

	foreach ( $taxs as $tax ) {
		$fields[] = array(
			'tab'     => 'permalinks',
			'section' => 'remove_base',
			'id'      => 'remove_' . $tax->name . '_base',
			'title'   => ucwords( $tax->labels->singular_name ),
			'args'    => array(
				'label_for'     => true,
				'callback_args' => array(
					'type'  => 'checkbox',
					'label' => sprintf( __( 'Remove base slug for taxonomy %s', 'hocwp-ext' ), '<code>' . $tax->name . '</code>' )
				)
			)
		);
	}

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_permalinks_settings_field', 'hocwp_ext_settings_page_permalinks_field' );