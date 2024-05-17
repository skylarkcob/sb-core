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
		'type' => 'checkbox'
	);

	$args['label'] = __( 'Users must verify their email address for viewing site?', 'sb-core' );

	$field    = hocwp_theme_create_setting_field( 'must_verify_email', __( 'Must Verify Email', 'sb-core' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Allow users can login and register via their social accounts.', 'sb-core' )
	);

	$field    = hocwp_theme_create_setting_field( 'connect_social', __( 'Connect Social', 'sb-core' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Using CAPTCHA for account form?', 'sb-core' )
	);

	$field    = hocwp_theme_create_setting_field( 'captcha', __( 'CAPTCHA', 'sb-core' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args = array(
		'type'  => 'checkbox',
		'label' => __( 'Change style of default WordPress login page for displaying nicely?', 'sb-core' )
	);

	$field    = hocwp_theme_create_setting_field( 'custom_style', __( 'Custom Style', 'sb-core' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args['label'] = __( 'Allow user login with phone number or email via Facebook Account Kit?', 'sb-core' );

	$field    = hocwp_theme_create_setting_field( 'account_kit', __( 'Account Kit', 'sb-core' ), 'input', $args, 'boolean', 'account' );
	$fields[] = $field;

	$args['label'] = __( 'Track users activity?', 'sb-core' );

	$field    = hocwp_theme_create_setting_field( 'activity_logs', __( 'Activity Logs', 'sb-core' ), 'input', $args, 'boolean', 'account' );
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

	$account_kit = isset( $options['account_kit'] ) ? $options['account_kit'] : '';

	if ( 1 == $account_kit ) {
		$field    = hocwp_theme_create_setting_field( 'fac_app_secret', __( 'App Secret', 'sb-core' ), 'input', array( 'class' => 'regular-text' ), 'string', 'account', HTE_Account()->facebook_account_kit );
		$fields[] = $field;

		$field    = hocwp_theme_create_setting_field( 'fac_client_token', __( 'Client Token', 'sb-core' ), 'input', array( 'class' => 'regular-text' ), 'string', 'account', HTE_Account()->facebook_account_kit );
		$fields[] = $field;

		$field    = hocwp_theme_create_setting_field( 'fac_api_version', __( 'API Version', 'sb-core' ), 'input', array( 'class' => 'regular-text' ), 'string', 'account', HTE_Account()->facebook_account_kit );
		$fields[] = $field;

		$args = array(
			'options' => array(
				'popup' => __( 'Popup', 'sb-core' ),
				'modal' => __( 'Modal', 'sb-core' )
			),
			'class'   => 'regular-text'
		);

		$field    = hocwp_theme_create_setting_field( 'fac_display', __( 'Display', 'sb-core' ), 'select', $args, 'string', 'account', HTE_Account()->facebook_account_kit );
		$fields[] = $field;

		$field    = hocwp_theme_create_setting_field( 'fac_country_code', __( 'Default Country Code', 'sb-core' ), 'input', array( 'class' => 'regular-text' ), 'positive_number', 'account', HTE_Account()->facebook_account_kit );
		$fields[] = $field;

		$args = array(
			'type' => 'checkbox'
		);

		$args['label'] = __( 'Users must verify their phone number for viewing site?', 'sb-core' );

		$field    = hocwp_theme_create_setting_field( 'must_verify_phone', __( 'Must Verify Phone Number', 'sb-core' ), 'input', $args, 'boolean', 'account', HTE_Account()->facebook_account_kit );
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

	$account_kit = isset( $options['account_kit'] ) ? $options['account_kit'] : '';

	if ( 1 == $account_kit ) {
		$section = array(
			'id'          => HTE_Account()->facebook_account_kit,
			'title'       => __( 'Facebook Account Kit', 'sb-core' ),
			'tab'         => 'account',
			'description' => __( 'Account Kit helps people quickly and easily register and log into your app using their phone number or email address as passwordless credentials. Account Kit is powered by Facebook\'s email, SMS and WhatsApp sending infrastructure for reliable scalable performance with global reach. Because it uses email and phone number authentication, Account Kit does not require a Facebook account and is the ideal alternative to a social login.', 'sb-core' )
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