<?php
function hocwp_theme_settings_page_account_tab( $tabs ) {
	$tabs['account'] = __( 'Account', 'hocwp-ext' );

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_account_tab' );

function hocwp_theme_settings_page_account_field() {
	$options = HT_Util()->get_theme_options( 'account' );

	$fields = array();

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Allow users can login and register via their social accounts.', 'hocwp-ext' )
	);

	$field    = hocwp_theme_create_setting_field( 'connect_social', __( 'Connect Social', 'hocwp-ext' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Using captcha for account form?', 'hocwp-ext' )
	);

	$field    = hocwp_theme_create_setting_field( 'captcha', __( 'Captcha', 'hocwp-ext' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Change style of default WordPress login page for displaying nicely?', 'hocwp-ext' )
	);

	$field    = hocwp_theme_create_setting_field( 'custom_style', __( 'Custom Style', 'hocwp-ext' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args = array(
		'class' => 'regular-text'
	);

	$field    = hocwp_theme_create_setting_field( 'login_page', __( 'Login Page', 'hocwp-ext' ), 'select_page', $args, 'numeric', 'account', 'account_tools_page' );
	$fields[] = $field;

	$args = array(
		'class' => 'regular-text'
	);

	$field    = hocwp_theme_create_setting_field( 'register_page', __( 'Register Page', 'hocwp-ext' ), 'select_page', $args, 'numeric', 'account', 'account_tools_page' );
	$fields[] = $field;

	$args = array(
		'class' => 'regular-text'
	);

	$field    = hocwp_theme_create_setting_field( 'lostpassword_page', __( 'Lost Password Page', 'hocwp-ext' ), 'select_page', $args, 'numeric', 'account', 'account_tools_page' );
	$fields[] = $field;

	$args = array(
		'class' => 'regular-text'
	);

	$field    = hocwp_theme_create_setting_field( 'profile_page', __( 'Profile Page', 'hocwp-ext' ), 'select_page', $args, 'numeric', 'account', 'account_tools_page' );
	$fields[] = $field;

	$cs = isset( $options['custom_style'] ) ? $options['custom_style'] : '';

	if ( 1 == $cs ) {
		$field    = hocwp_theme_create_setting_field( 'login_logo', __( 'Login Logo', 'hocwp-ext' ), 'media_upload', array(), 'positive_integer', 'account', 'custom_default_login_page' );
		$fields[] = $field;
	}

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_account_settings_field', 'hocwp_theme_settings_page_account_field' );

function hocwp_theme_settings_page_account_settings_section( $sections ) {
	$section = array(
		'id'    => 'account_tools_page',
		'title' => __( 'Account Tools Page', 'hocwp-ext' ),
		'tab'   => 'account'
	);

	$sections[] = $section;

	$options = HT_Util()->get_theme_options( 'account' );
	$cs      = isset( $options['custom_style'] ) ? $options['custom_style'] : '';

	if ( 1 == $cs ) {
		$section = array(
			'id'    => 'custom_default_login_page',
			'title' => __( 'Customize Default Login Page', 'hocwp-ext' ),
			'tab'   => 'account'
		);

		$sections[] = $section;
	}

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_account_settings_section', 'hocwp_theme_settings_page_account_settings_section' );

function hocwp_theme_sanitize_option_account( $input ) {
	if ( ! is_array( $input ) ) {
		$input = array();
	}

	return $input;
}

add_filter( 'hocwp_theme_sanitize_option_account', 'hocwp_theme_sanitize_option_account' );

function hocwp_theme_admin_setting_page_account_scripts() {
	HT_Util()->enqueue_media();
}

add_action( 'hocwp_theme_admin_setting_page_account_scripts', 'hocwp_theme_admin_setting_page_account_scripts' );