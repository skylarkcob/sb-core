<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * Load all styles and scripts for login and front-end page.
 */
function hocwp_ext_account_login_enqueue_scripts() {
	global $pagenow;

	$is_page = false;

	if ( is_page() ) {
		$page = HT_Util()->get_theme_option_page( 'login_page', 'account' );

		if ( HTE_Account()->check_option_page_valid( $page ) ) {
			$is_page = true;
		} else {
			$page = HT_Util()->get_theme_option_page( 'register_page', 'account' );

			if ( HTE_Account()->check_option_page_valid( $page ) ) {
				$is_page = true;
			} else {
				$page = HT_Util()->get_theme_option_page( 'lostpassword_page', 'account' );

				if ( HTE_Account()->check_option_page_valid( $page ) ) {
					$is_page = true;
				}
			}
		}
	}

	if ( 'wp-login.php' == $pagenow || $is_page ) {

	}
}

add_action( 'wp_enqueue_scripts', 'hocwp_ext_account_login_enqueue_scripts' );
add_action( 'login_enqueue_scripts', 'hocwp_ext_account_login_enqueue_scripts' );

function hocwp_ext_account_login_init() {
	$action = HT()->get_method_value( 'action', 'get' );

	// Check if logged in user goes to register page.
	if ( 'register' == $action && is_user_logged_in() ) {
		// If logged in user goes to register page, just redirect to profile page.
		wp_redirect( get_edit_profile_url() );
		exit;
	}
}

add_action( 'login_init', 'hocwp_ext_account_login_init' );

function hocwp_ext_account_edit_profile_url( $url ) {
	$page = HT_Util()->get_theme_option_page( 'profile_page', 'account' );

	if ( HTE_Account()->check_option_page_valid( $page ) ) {
		$url = get_permalink( $page );
		$url = add_query_arg('action', 'edit_profile', $url);
	}

	return $url;
}

add_filter( 'edit_profile_url', 'hocwp_ext_account_edit_profile_url' );

function hocwp_ext_account_login_url( $login_url, $redirect, $force_reauth ) {
	$page = HT_Util()->get_theme_option_page( 'login_page', 'account' );

	if ( HTE_Account()->check_option_page_valid( $page ) ) {
		$url    = get_permalink( $page );
		$params = array();

		if ( ! empty( $redirect ) ) {
			$params['redirect_to'] = $redirect;
		}

		if ( $force_reauth ) {
			$params['reauth'] = 1;
		}

		$login_url = add_query_arg( $params, $url );
		$login_url = add_query_arg( 'action', 'login', $login_url );
	}

	return $login_url;
}

add_filter( 'login_url', 'hocwp_ext_account_login_url', 10, 3 );

function hocwp_ext_account_lostpassword_url( $lostpassword_url, $redirect ) {
	$page = HT_Util()->get_theme_option_page( 'lostpassword_page', 'account' );

	if ( HTE_Account()->check_option_page_valid( $page ) ) {
		$url    = get_permalink( $page );
		$params = array();

		if ( ! empty( $redirect ) ) {
			$params['redirect_to'] = $redirect;
		}

		$lostpassword_url = add_query_arg( $params, $url );
		$lostpassword_url = add_query_arg( 'action', 'lostpassword', $lostpassword_url );
	}

	return $lostpassword_url;
}

add_filter( 'lostpassword_url', 'hocwp_ext_account_lostpassword_url', 10, 2 );

function hocwp_ext_account_register_url( $url ) {
	$page = HT_Util()->get_theme_option_page( 'register_page', 'account' );

	if ( HTE_Account()->check_option_page_valid( $page ) ) {
		$url = get_permalink( $page );
		$url = add_query_arg( 'action', 'register', $url );
	}

	return $url;
}

add_filter( 'register_url', 'hocwp_ext_account_register_url' );

function hocwp_ext_account_redirect_to_login() {
	if ( ! is_user_logged_in() ) {
		global $pagenow;

		if ( 'wp-login.php' == $pagenow && ! isset( $_POST['log'] ) && ! isset( $_POST['pwd'] ) ) {
			$page = HT_Util()->get_theme_option_page( 'login_page', 'account' );

			if ( HTE_Account()->check_option_page_valid( $page ) ) {
				$action = HT()->get_method_value( 'action', 'get' );

				if ( empty( $action ) ) {
					$url = get_permalink( $page );

					$redirect_to = HTE_Account()->get_redirect_to();

					if ( ! empty( $redirect_to ) ) {
						$url = add_query_arg( 'redirect_to', $redirect_to, $url );
					}

					wp_redirect( $url );
					exit;
				}
			}
		}
	}
}

add_action( 'init', 'hocwp_ext_account_redirect_to_login' );