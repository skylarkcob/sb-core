<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_woocommerce_tab( $tabs ) {
	$tabs['woocommerce'] = array(
		'text' => __( 'WooCommerce', 'sb-core' ),
		'icon' => '<span class="dashicons dashicons-products"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_woocommerce_tab' );

function hocwp_theme_settings_page_woocommerce_field() {
	$options = HT_Util()->get_theme_options( 'woocommerce' );

	$fields = array();

	$args = array(
		'class' => 'medium-text',
		'type'  => 'number'
	);

	$field    = hocwp_theme_create_setting_field( 'usd_vnd_rate', __( 'USD to Vietnam Dong Rate', 'sb-core' ), 'input', $args, 'numeric', 'woocommerce' );
	$fields[] = $field;

	$comment = HT_Options()->get_tab( 'comment_system', '', 'discussion' );

	if ( 'default' != $comment ) {
		$args = array(
			'type' => 'checkbox',
			'text' => __( 'Use custom comment system for product?', 'sb-core' )
		);

		$field    = hocwp_theme_create_setting_field( 'custom_comment', __( 'Custom Comment', 'sb-core' ), 'input', $args, 'boolean', 'woocommerce' );
		$fields[] = $field;

		$args['text'] = __( 'Replace review form with custom comment form.', 'sb-core' );

		$field    = hocwp_theme_create_setting_field( 'replace_review', __( 'Replace Review', 'sb-core' ), 'input', $args, 'boolean', 'woocommerce' );
		$fields[] = $field;
	}

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_woocommerce_settings_field', 'hocwp_theme_settings_page_woocommerce_field' );