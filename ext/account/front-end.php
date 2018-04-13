<?php
function hocwp_ext_account_login_enqueue_scripts() {
	global $pagenow;

	$is_page = false;

	if ( is_page() ) {
		$page = HT_Util()->get_theme_option_page( 'login_page', 'account' );

		if ( $page instanceof WP_Post ) {
			$is_page = true;
		} else {
			$page = HT_Util()->get_theme_option_page( 'register_page', 'account' );

			if ( $page instanceof WP_Post ) {
				$is_page = true;
			} else {
				$page = HT_Util()->get_theme_option_page( 'lostpassword_page', 'account' );

				if ( $page instanceof WP_Post ) {
					$is_page = true;
				}
			}
		}
	}

	if ( 'wp-login.php' == $pagenow || $is_page ) {
		wp_enqueue_style( 'hocwp-ext-account-login-style', HOCWP_EXT_URL . '/css/login' . HOCWP_THEME_CSS_SUFFIX );
		$options = HT_Util()->get_theme_options( 'account' );
		$cs      = isset( $options['connect_social'] ) ? $options['connect_social'] : '';

		if ( 1 == $cs ) {
			wp_enqueue_script( 'hocwp-theme' );

			$args = array(
				'load' => true
			);

			HT_Util()->load_facebook_javascript_sdk( $args );
			wp_enqueue_script( 'hocwp-ext-connected-accounts' );
		}
	}
}

add_action( 'login_enqueue_scripts', 'hocwp_ext_account_login_enqueue_scripts' );
add_action( 'wp_enqueue_scripts', 'hocwp_ext_account_login_enqueue_scripts' );

function hocwp_ext_account_default_login_page_scripts() {
	global $pagenow;

	if ( 'wp-login.php' == $pagenow ) {
		$options = HT_Util()->get_theme_options( 'account' );
		$cs      = isset( $options['custom_style'] ) ? $options['custom_style'] : '';

		if ( 1 == $cs ) {
			wp_enqueue_style( 'hocwp-ext-account-login-default-style', HOCWP_EXT_URL . '/css/login-default' . HOCWP_THEME_CSS_SUFFIX );
		}
	}
}

add_action( 'login_enqueue_scripts', 'hocwp_ext_account_default_login_page_scripts' );

function hocwp_ext_account_login_init() {
	$action = HT()->get_method_value( 'action', 'get' );
	if ( 'register' == $action && is_user_logged_in() ) {
		wp_redirect( get_edit_profile_url() );
		exit;
	}
}

add_action( 'login_init', 'hocwp_ext_account_login_init' );

function hocwp_ext_account_edit_profile_url( $url ) {
	$page = HT_Util()->get_theme_option_page( 'profile_page', 'account' );

	if ( $page instanceof WP_Post ) {
		$url = get_permalink( $page );
	}

	return $url;
}

add_filter( 'edit_profile_url', 'hocwp_ext_account_edit_profile_url' );

function hocwp_ext_account_login_url( $login_url, $redirect, $force_reauth ) {
	$page = HT_Util()->get_theme_option_page( 'login_page', 'account' );

	if ( $page instanceof WP_Post ) {
		$url    = get_permalink( $page );
		$params = array();

		if ( ! empty( $redirect ) ) {
			$params['redirect_to'] = $redirect;
		}

		if ( $force_reauth ) {
			$params['reauth'] = 1;
		}

		$login_url = add_query_arg( $params, $url );
	}

	return $login_url;
}

add_filter( 'login_url', 'hocwp_ext_account_login_url', 10, 3 );

function hocwp_ext_account_lostpassword_url( $lostpassword_url, $redirect ) {
	$page = HT_Util()->get_theme_option_page( 'lostpassword_page', 'account' );

	if ( $page instanceof WP_Post ) {
		$url    = get_permalink( $page );
		$params = array();

		if ( ! empty( $redirect ) ) {
			$params['redirect_to'] = $redirect;
		}

		$lostpassword_url = add_query_arg( $params, $url );
	}

	return $lostpassword_url;
}

add_filter( 'lostpassword_url', 'hocwp_ext_account_lostpassword_url', 10, 2 );

function hocwp_ext_account_register_url( $url ) {
	$page = HT_Util()->get_theme_option_page( 'register_page', 'account' );

	if ( $page instanceof WP_Post ) {
		$url = get_permalink( $page );
	}

	return $url;
}

add_filter( 'register_url', 'hocwp_ext_account_register_url' );

function hocwp_ext_account_redirect_to_login() {
	if ( ! is_user_logged_in() ) {
		global $pagenow;

		if ( 'wp-login.php' == $pagenow && ! isset( $_POST['log'] ) && ! isset( $_POST['pwd'] ) ) {
			$page = HT_Util()->get_theme_option_page( 'login_page', 'account' );

			if ( $page instanceof WP_Post ) {
				$action = HT()->get_method_value( 'action', 'get' );

				if ( empty( $action ) ) {
					$url = get_permalink( $page );
					wp_redirect( $url );
					exit;
				}
			}
		}
	}
}

add_action( 'init', 'hocwp_ext_account_redirect_to_login' );