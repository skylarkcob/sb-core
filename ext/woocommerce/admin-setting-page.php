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

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_woocommerce_settings_field', 'hocwp_theme_settings_page_woocommerce_field' );