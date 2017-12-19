<?php
function hocwp_theme_settings_page_account_tab( $tabs ) {
	$tabs['account'] = __( 'Account', 'hocwp-ext' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_account_tab' );

function hocwp_theme_settings_page_account_field() {
	$fields = array();

	$args     = array(
		'type'  => 'checkbox',
		'label' => __( 'Allow users can login and register via their social accounts.', 'hocwp-ext' )
	);
	$field    = hocwp_theme_create_setting_field( 'connect_social', __( 'Connect Social', 'hocwp-ext' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args     = array(
		'type'  => 'checkbox',
		'label' => __( 'Using captcha for account form?', 'hocwp-ext' )
	);
	$field    = hocwp_theme_create_setting_field( 'captcha', __( 'Captcha', 'hocwp-ext' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args     = array(
		'class' => 'regular-text'
	);
	$field    = hocwp_theme_create_setting_field( 'login_page', __( 'Login Page', 'hocwp-ext' ), 'select_page', $args, 'numeric', 'account' );
	$fields[] = $field;

	$args     = array(
		'class' => 'regular-text'
	);
	$field    = hocwp_theme_create_setting_field( 'register_page', __( 'Register Page', 'hocwp-ext' ), 'select_page', $args, 'numeric', 'account' );
	$fields[] = $field;

	$args     = array(
		'class' => 'regular-text'
	);
	$field    = hocwp_theme_create_setting_field( 'lostpassword_page', __( 'Lost Password Page', 'hocwp-ext' ), 'select_page', $args, 'numeric', 'account' );
	$fields[] = $field;

	$args     = array(
		'class' => 'regular-text'
	);
	$field    = hocwp_theme_create_setting_field( 'profile_page', __( 'Profile Page', 'hocwp-ext' ), 'select_page', $args, 'numeric', 'account' );
	$fields[] = $field;

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_account_settings_field', 'hocwp_theme_settings_page_account_field' );

function hocwp_theme_sanitize_option_account( $input ) {
	if ( ! is_array( $input ) ) {
		$input = array();
	}

	return $input;
}

add_filter( 'hocwp_theme_sanitize_option_account', 'hocwp_theme_sanitize_option_account' );