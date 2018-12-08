<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_account_tab( $tabs ) {
	$tabs['account'] = array(
		'text' => __( 'Account', 'sb-core' ),
		'icon' => '<span class="dashicons dashicons-businessman"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_account_tab' );

function hocwp_theme_settings_page_account_field() {
	$options = HT_Util()->get_theme_options( 'account' );

	$fields = array();

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Allow users can login and register via their social accounts.', 'sb-core' )
	);

	$field    = hocwp_theme_create_setting_field( 'connect_social', __( 'Connect Social', 'sb-core' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Using captcha for account form?', 'sb-core' )
	);

	$field    = hocwp_theme_create_setting_field( 'captcha', __( 'Captcha', 'sb-core' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Change style of default WordPress login page for displaying nicely?', 'sb-core' )
	);

	$field    = hocwp_theme_create_setting_field( 'custom_style', __( 'Custom Style', 'sb-core' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args = array(
		'class' => 'regular-text'
	);

	$field    = hocwp_theme_create_setting_field( 'login_page', __( 'Login Page', 'sb-core' ), 'select_page', $args, 'numeric', 'account', 'account_tools_page' );
	$fields[] = $field;

	$args = array(
		'class' => 'regular-text'
	);

	$field    = hocwp_theme_create_setting_field( 'register_page', __( 'Register Page', 'sb-core' ), 'select_page', $args, 'numeric', 'account', 'account_tools_page' );
	$fields[] = $field;

	$args = array(
		'class' => 'regular-text'
	);

	$field    = hocwp_theme_create_setting_field( 'lostpassword_page', __( 'Lost Password Page', 'sb-core' ), 'select_page', $args, 'numeric', 'account', 'account_tools_page' );
	$fields[] = $field;

	$args = array(
		'class' => 'regular-text'
	);

	$field    = hocwp_theme_create_setting_field( 'profile_page', __( 'Profile Page', 'sb-core' ), 'select_page', $args, 'numeric', 'account', 'account_tools_page' );
	$fields[] = $field;

	$field    = hocwp_theme_create_setting_field( 'saved_posts_page', __( 'Saved Posts Page', 'sb-core' ), 'select_page', $args, 'numeric', 'account', 'account_tools_page' );
	$fields[] = $field;

	$cs = isset( $options['custom_style'] ) ? $options['custom_style'] : '';

	if ( 1 == $cs ) {
		$field    = hocwp_theme_create_setting_field( 'login_logo', __( 'Login Logo', 'sb-core' ), 'media_upload', array(), 'positive_integer', 'account', 'custom_default_login_page' );
		$fields[] = $field;
	}

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_account_settings_field', 'hocwp_theme_settings_page_account_field' );

function hocwp_theme_settings_page_account_settings_section( $sections ) {
	$section = array(
		'id'          => 'account_tools_page',
		'title'       => __( 'Account Tools Page', 'sb-core' ),
		'tab'         => 'account',
		'description' => __( 'If you want to use custom page for user functions instead of using <code>wp-login.php</code>, you can choose the page for these settings below.', 'sb-core' )
	);

	$sections[] = $section;

	$options = HT_Util()->get_theme_options( 'account' );
	$cs      = isset( $options['custom_style'] ) ? $options['custom_style'] : '';

	if ( 1 == $cs ) {
		$section = array(
			'id'          => 'custom_default_login_page',
			'title'       => __( 'Customize Default Login Page', 'sb-core' ),
			'tab'         => 'account',
			'description' => __( 'You can customize some styles to make the default WordPress login page more pretty.', 'sb-core' )
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
	if ( function_exists( 'HT_Enqueue' ) ) {
		HT_Enqueue()->media_upload();
	} else {
		HT_Util()->enqueue_media();
	}
}

add_action( 'hocwp_theme_admin_setting_page_account_scripts', 'hocwp_theme_admin_setting_page_account_scripts' );