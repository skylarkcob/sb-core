<?php
function hocwp_ext_account_login_enqueue_scripts() {
	wp_enqueue_style( 'hocwp-ext-account-login-style', HOCWP_EXT_URL . '/css/login' . HOCWP_THEME_CSS_SUFFIX );
	$options = HT_Util()->get_theme_options( 'account' );
	$cs      = isset( $options['connect_social'] ) ? $options['connect_social'] : '';
	if ( 1 == $cs ) {
		$args = array(
			'load' => true
		);
		HT_Util()->load_facebook_javascript_sdk( $args );
		wp_enqueue_script( 'hocwp-ext-connected-accounts' );
	}
}

add_action( 'login_enqueue_scripts', 'hocwp_ext_account_login_enqueue_scripts' );

function hocwp_ext_account_login_init() {
	$action = HT()->get_method_value( 'action', 'get' );
	if ( 'register' == $action && is_user_logged_in() ) {
		wp_redirect( get_edit_profile_url() );
		exit;
	}
}

add_action( 'login_init', 'hocwp_ext_account_login_init' );