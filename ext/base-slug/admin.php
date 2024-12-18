<?php
defined( 'ABSPATH' ) || exit;

function hocwp_ext_settings_page_permalinks_section( $sections ) {
	$taxs  = hocwp_theme_get_custom_taxonomies();
	$types = hocwp_theme_get_custom_post_types();

	if ( ht()->array_has_value( $taxs ) || ht()->array_has_value( $types ) ) {
		$desc = __( 'If you like, you may enter custom structures for your custom taxonomy term and custom post type URLs here. If you leave these blank the defaults will be used. Note: This function may affect the performance of the site.', 'sb-core' );

		$sections['custom_base'] = array(
			'tab'         => 'permalinks',
			'id'          => 'custom_base',
			'title'       => __( 'Custom Base Slug', 'sb-core' ),
			'description' => $desc
		);
	}

	$desc = __( 'We do not encourage you to do this but if you like you can still remove the base slug from permalink.', 'sb-core' );

	$sections['remove_base'] = array(
		'tab'         => 'permalinks',
		'id'          => 'remove_base',
		'title'       => __( 'Remove Base Slug', 'sb-core' ),
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
			'title'   => sprintf( __( '%s base', 'sb-core' ), ucwords( $type->labels->singular_name ) ),
			'args'    => array(
				'label_for'   => true,
				'description' => sprintf( __( 'Base slug for custom post type %s.', 'sb-core' ), '<code>' . $type->name . '</code>' )
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
					'label' => sprintf( __( 'Remove base slug for post type %s', 'sb-core' ), '<code>' . $type->name . '</code>' )
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
			'title'   => sprintf( __( '%s base', 'sb-core' ), ucwords( $tax->labels->singular_name ) ),
			'args'    => array(
				'label_for'   => true,
				'description' => sprintf( __( 'Base slug for custom taxonomy %s', 'sb-core' ), '<code>' . $tax->name . '</code>' )
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
					'label' => sprintf( __( 'Remove base slug for taxonomy %s', 'sb-core' ), '<code>' . $tax->name . '</code>' )
				)
			)
		);
	}

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_permalinks_settings_field', 'hocwp_ext_settings_page_permalinks_field' );