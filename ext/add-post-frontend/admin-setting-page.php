<?php
function hocwp_theme_settings_page_vip_tab( $tabs ) {
	$tabs['vip'] = __( 'VIP', 'hocwp-ext' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_vip_tab' );

global $hocwp_theme;
if ( 'vip' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_vip_section() {
	$fields = array();

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_vip_settings_section', 'hocwp_theme_settings_page_vip_section' );

function hocwp_theme_settings_page_vip_field( $fields ) {
	$args     = array(
		'type'        => 'number',
		'class'       => 'small-text',
		'description' => __( 'VIP post costs by day.', 'hocwp-ext' )
	);
	$field    = hocwp_theme_create_setting_field( 'post_price', __( 'Post Price', 'hocwp-ext' ), 'input', $args, 'positive_integer', 'vip' );
	$fields[] = $field;

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_vip_settings_field', 'hocwp_theme_settings_page_vip_field' );