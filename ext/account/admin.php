<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require HOCWP_EXT_ACCOUNT_PATH . '/admin-setting-page.php';

function hocwp_ext_account_profile_fields( $user ) {
	global $pagenow, $hocwp_theme;
	$options = isset( $hocwp_theme->options['account'] ) ? $hocwp_theme->options['account'] : '';

	if ( ! is_array( $options ) ) {
		$options = array();
	}

	if ( 'profile.php' == $pagenow ) {
		$cs = isset( $options['connect_social'] ) ? $options['connect_social'] : '';

		if ( 1 == $cs ) {
			require HOCWP_EXT_ACCOUNT_PATH . '/connected-accounts.php';
		}
	}
}

add_action( 'show_user_profile', 'hocwp_ext_account_profile_fields' );
add_action( 'edit_user_profile', 'hocwp_ext_account_profile_fields' );

/*
 * Load styles and scripts on back-end.
 */
function hocwp_ext_account_admin_scripts() {
	global $pagenow;

	if ( 'profile.php' == $pagenow ) {
		HTE_Account()->load_connected_socials_script();
		HTE_Account()->load_facebook_account_kit_script();
	}
}

add_action( 'admin_enqueue_scripts', 'hocwp_ext_account_admin_scripts' );

function hocwp_ext_account_admin_footer() {
	global $pagenow;

	if ( 'profile.php' == $pagenow ) {
		HTE_Account()->load_facebook_sdk();
	}
}

add_action( 'admin_footer', 'hocwp_ext_account_admin_footer' );

function hocwp_ext_account_connect_social_ajax_callback() {
	global $hocwp_theme;

	$type = HT()->get_method_value( 'type' );

	$social_data = HT()->get_method_value( 'social_data' );
	$disconnect  = HT()->get_method_value( 'disconnect' );

	$user_id = get_current_user_id();
	$data    = array();

	if ( ! empty( $type ) && ( $social_data || 1 == $disconnect ) ) {
		$login = HT()->get_method_value( 'login' );
		$type  = strtolower( $type );

		$profile_key = $type . '_profile';

		$id_key = $type . '_id';

		$id = HT()->get_method_value( 'id' );

		if ( 1 == $login ) {
			$users = get_users( array( 'meta_key' => $id_key, 'meta_value' => $id ) );

			if ( HT()->array_has_value( $users ) ) {
				$user = current( $users );

				if ( $user instanceof WP_User ) {
					$user_id = $user->ID;
				}
			} else {
				$email     = '';
				$user_data = array();

				switch ( $type ) {
					case 'facebook':
						$email = isset( $social_data['email'] ) ? $social_data['email'] : '';

						$user_data['display_name'] = isset( $social_data['name'] ) ? $social_data['name'] : '';
						$user_data['first_name']   = isset( $social_data['first_name'] ) ? $social_data['first_name'] : '';
						$user_data['last_name']    = isset( $social_data['last_name'] ) ? $social_data['last_name'] : '';

						break;
					case 'google':
						$emails = isset( $social_data['emailAddresses'] ) ? $social_data['emailAddresses'] : '';
						$emails = (array) $emails;

						if ( HT()->array_has_value( $emails ) ) {
							$emails = current( $emails );
							$email  = isset( $emails['value'] ) ? $emails['value'] : '';
						}

						$names = isset( $social_data['names'] ) ? $social_data['names'] : '';
						$names = (array) $names;

						if ( HT()->array_has_value( $names ) ) {
							$names = current( $names );

							$user_data['display_name'] = isset( $names['displayName'] ) ? $names['displayName'] : '';
							$user_data['first_name']   = isset( $names['givenName'] ) ? $names['givenName'] : '';
							$user_data['last_name']    = isset( $names['familyName'] ) ? $names['familyName'] : '';
						}

						break;
				}

				if ( is_email( $email ) ) {
					if ( $hocwp_theme->users_can_register ) {
						$user_id = wp_create_user( $email, wp_generate_password(), $email );

						if ( HT()->is_positive_number( $user_id ) ) {
							do_action( 'hocwp_theme_extension_account_user_added', $user_id );
						}
					} else {
						$user_id = new WP_Error( 'registration_not_allowed', __( 'This site does not allow users to register.', 'sb-core' ) );
					}
				}
			}

			if ( HT()->is_positive_number( $user_id ) ) {
				wp_set_auth_cookie( $user_id, true );
			}
		}

		if ( is_wp_error( $user_id ) ) {
			$data['message'] = $user_id->get_error_message();
			wp_send_json_error( $data );
		}

		$deleted = delete_user_meta( $user_id, $profile_key );
		delete_user_meta( $user_id, $id_key );

		if ( 0 == $disconnect || ( 1 == $login && HT()->is_positive_number( $user_id ) ) ) {
			if ( ! is_array( $social_data ) ) {
				$social_data = array();
			}

			$updated = update_user_meta( $user_id, $profile_key, $social_data );

			if ( $updated ) {
				$html  = '';
				$thumb = '';

				if ( ! empty( $id ) ) {
					update_user_meta( $user_id, $id_key, $id );
				}

				if ( 1 != $login ) {
					switch ( $type ) {
						case 'facebook':
							$html = hocwp_ext_account_connect_facebook_avatar_name_html( $social_data );
							break;
						case 'google':
							$html = hocwp_ext_account_connect_google_avatar_name_html( $social_data );
							break;
					}
				}

				$data['html'] = $html;
				wp_send_json_success( $data );
			}
		} elseif ( 1 == $disconnect && $deleted ) {
			wp_send_json_success();
		}
	}

	wp_send_json_error();
}

add_action( 'wp_ajax_hocwp_theme_connect_social', 'hocwp_ext_account_connect_social_ajax_callback' );
add_action( 'wp_ajax_nopriv_hocwp_theme_connect_social', 'hocwp_ext_account_connect_social_ajax_callback' );

function hocwp_ext_account_admin_notices_action() {
	if ( function_exists( 'HT_Admin' ) && method_exists( HT_Admin(), 'skip_admin_notices' ) && HT_Admin()->skip_admin_notices() ) {
		return;
	}

	$options = HT_Util()->get_theme_options( 'account' );

	$account_kit = isset( $options['account_kit'] ) ? $options['account_kit'] : '';

	if ( 1 == $account_kit ) {
		$msg = __( 'Account Kit will no longer be available for developers and partners.', 'sb-core' );

		$args = array(
			'type'    => 'error',
			'message' => sprintf( '<strong>%s</strong> %s', __( 'Account Extension:', 'sb-core' ), $msg )
		);

		HT_Util()->admin_notice( $args );
	}

	$cs      = $options['connect_social'] ?? '';
	$captcha = $options['captcha'] ?? '';

	if ( 1 == $cs || 1 == $captcha ) {
		$options = HT_Util()->get_theme_options( 'social' );

		$fai = $options['facebook_app_id'] ?? '';
		$gak = $options['google_api_key'] ?? '';
		$gci = $options['google_client_id'] ?? '';

		if ( empty( $fai ) || empty( $gak ) || empty( $gci ) || ! HT_CAPTCHA()->check_config_valid() ) {
			$msg = sprintf( __( 'You must fully input settings in <a href="%s">Social tab</a> for account extension works normally.', 'sb-core' ), admin_url( 'themes.php?page=hocwp_theme&tab=social' ) );

			$args = array(
				'type'    => 'info',
				'message' => sprintf( '<strong>%s</strong> %s', __( 'Account Extension:', 'sb-core' ), $msg )
			);

			HT_Util()->admin_notice( $args );
		}
	}
}

add_action( 'admin_notices', 'hocwp_ext_account_admin_notices_action' );

/*
 * Login or register with Facebook account kit AJAX callback.
 * Connect or disconnect phone number and email address AJAX callback.
 */
function hocwp_extension_account_connect_facebook_account_kit_ajax_callback() {
	$data = array(
		'message' => __( 'There was an error occurred, please try again.', 'sb-core' )
	);

	$do_action = HT()->get_method_value( 'do_action' );

	if ( 'disconnect-email' == $do_action ) {
		$email = HT()->get_method_value( 'email' );

		$user = get_user_by( 'email', $email );

		if ( $user instanceof WP_User ) {
			$fac = HTE_Account()->get_user_facebook_account_kit();

			unset( $fac['email'] );
			HTE_Account()->update_user_facebook_account_kit( $fac, $user->ID );

			$data['message'] = __( 'Your email address has been disconnected successfully.', 'sb-core' );
			wp_send_json_success( $data );
		}

		wp_send_json_error( $data );
	} elseif ( 'disconnect-phone' == $do_action ) {
		$user_id = HT()->get_method_value( 'user_id' );

		$user = get_user_by( 'ID', $user_id );

		if ( $user instanceof WP_User ) {
			$fac = HTE_Account()->get_user_facebook_account_kit();

			unset( $fac['phone'] );
			HTE_Account()->update_user_facebook_account_kit( $fac, $user->ID );

			$data['message'] = __( 'Your phone number has been disconnected successfully.', 'sb-core' );
			wp_send_json_success( $data );
		}

		wp_send_json_error( $data );
	}

	$csrf = HT()->get_method_value( 'csrf' );

	if ( ! wp_verify_nonce( $csrf, 'hte_facebook_account_kit' ) ) {
		$data['message'] = __( 'Invalid nonce', 'sb-core' );
		wp_send_json_error( $data );
	}

	$app_id = HT()->get_method_value( 'app_id' );

	$app_secret = HT()->get_method_value( 'app_secret' );

	$token = 'AA|' . $app_id . '|' . $app_secret;

	$api_version = HT()->get_method_value( 'api_version' );

	$code = HT()->get_method_value( 'code' );

	$url = 'https://graph.accountkit.com/' . $api_version . '/access_token';

	$params = array(
		'grant_type'   => 'authorization_code',
		'code'         => $code,
		'access_token' => $token
	);

	$url = add_query_arg( $params, $url );

	$remote = wp_remote_get( $url );
	$body   = wp_remote_retrieve_body( $remote );
	$result = json_decode( $body );

	if ( is_object( $result ) && isset( $result->access_token ) ) {
		$appsecret_proof = hash_hmac( 'sha256', $result->access_token, $app_secret );

		$url = 'https://graph.accountkit.com/' . $api_version . '/me';

		$params = array(
			'access_token'    => $result->access_token,
			'appsecret_proof' => $appsecret_proof
		);

		$url = add_query_arg( $params, $url );

		$remote = wp_remote_get( $url );
		$body   = wp_remote_retrieve_body( $remote );
		$result = json_decode( $body );

		$requested_redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

		if ( is_object( $result ) && isset( $result->id ) ) {
			if ( 'login' == $do_action ) {
				$user = '';

				if ( isset( $result->phone ) ) {
					$args = array(
						'meta_query' => array(
							'relation' => 'OR',
							array(
								'key'   => 'phone',
								'value' => $result->phone->national_number
							),
							array(
								'key'   => 'phone',
								'value' => '0' . $result->phone->national_number
							)
						)
					);

					$query = new WP_User_Query( $args );
					$users = $query->get_results();
					$user  = current( $users );
				} elseif ( isset( $result->email ) ) {
					$user = get_user_by( 'email', $result->email->address );
				}

				// Find user with email address or phone number
				if ( $user instanceof WP_User ) {
					$facebook_account_kit = HTE_Account()->get_user_facebook_account_kit();

					if ( is_array( $facebook_account_kit ) ) {
						if ( ( isset( $facebook_account_kit['phone'] ) && isset( $result->phone ) && $result->phone->national_number == $facebook_account_kit['phone'] ) || ( isset( $facebook_account_kit['email'] ) && isset( $result->email ) && $result->email->address == $facebook_account_kit['email'] ) ) {
							wp_set_auth_cookie( $user->ID, true );
							$data['message'] = __( 'You have been logged in successfully.', 'sb-core' );

							$redirect_to = apply_filters( 'login_redirect', get_edit_profile_url( $user->ID ), $requested_redirect_to, $user );

							$data['redirect_to'] = $redirect_to;

							wp_send_json_success( $data );
						}
					}

					if ( isset( $result->email ) ) {
						$data['message'] = __( 'Your email address is not connected.', 'sb-core' );
					} elseif ( isset( $result->phone ) ) {
						$data['message'] = __( 'Your phone number is not connected.', 'sb-core' );
					} else {
						$data['message'] = __( 'Your phone number or email address is not connected.', 'sb-core' );
					}
				} else {
					global $hocwp_theme;

					if ( $hocwp_theme->users_can_register ) {
						if ( isset( $result->phone ) ) {
							$data['message'] = __( 'Your phone number is not connected.', 'sb-core' );
						} elseif ( isset( $result->email ) ) {
							$user_id = wp_create_user( $result->email->address, wp_generate_password(), $result->email->address );

							if ( HT()->is_positive_number( $user_id ) ) {
								$fac = array(
									'id'    => $result->id,
									'email' => $result->email->address
								);

								HTE_Account()->update_user_facebook_account_kit( $fac, $user_id );

								do_action( 'hocwp_theme_extension_account_user_added', $user_id );
								wp_set_auth_cookie( $user_id, true );

								$data['message'] = __( 'Your account has been created successfully.', 'sb-core' );

								$redirect_to = apply_filters( 'login_redirect', get_edit_profile_url( $user_id ), $requested_redirect_to, get_user_by( 'ID', $user_id ) );

								$data['redirect_to'] = $redirect_to;

								wp_send_json_success( $data );
							}

							$data['message'] = __( 'Cannot create user.', 'sb-core' );
						}
					} else {
						$data['message'] = __( 'This site does not allow users to register.', 'sb-core' );
					}
				}
			} elseif ( 'connect-email' == $do_action ) {
				$user_id = HT()->get_method_value( 'user_id' );

				if ( HT()->is_positive_number( $user_id ) && isset( $result->email ) && isset( $result->email->address ) && is_email( $result->email->address ) ) {
					$user = get_user_by( 'ID', $user_id );

					if ( $user instanceof WP_User ) {
						if ( $result->email->address == $user->user_email ) {
							$fac = HTE_Account()->get_user_facebook_account_kit();

							if ( ! is_array( $fac ) ) {
								$fac = array(
									'id' => $result->id
								);
							}

							$fac['email'] = $user->user_email;

							HTE_Account()->update_user_facebook_account_kit( $fac, $user_id );

							$data['message'] = __( 'Your email address has been connected successfully.', 'sb-core' );
							wp_send_json_success( $data );
						}
					}
				}
			} elseif ( 'connect-phone' == $do_action ) {
				$user_id = HT()->get_method_value( 'user_id' );

				if ( HT()->is_positive_number( $user_id ) && isset( $result->phone ) && isset( $result->phone->national_number ) && ! empty( $result->phone->national_number ) ) {
					$user = get_user_by( 'ID', $user_id );

					if ( $user instanceof WP_User ) {
						$fac = HTE_Account()->get_user_facebook_account_kit();

						if ( ! is_array( $fac ) ) {
							$fac = array(
								'id' => $result->id
							);
						}

						$fac['phone']          = $result->phone->national_number;
						$fac['country_prefix'] = $result->phone->country_prefix;

						HTE_Account()->update_user_facebook_account_kit( $fac, $user_id );

						$phone = get_user_meta( $user_id, 'phone', true );

						if ( empty( $phone ) ) {
							update_user_meta( $user_id, 'phone', $fac['phone'] );
						}

						$data['message'] = __( 'Your phone number has been connected successfully.', 'sb-core' );
						wp_send_json_success( $data );
					}
				}
			}
		}
	}

	wp_send_json_error( $data );
}

function hocwp_ext_account_admin_head_action() {
	global $pagenow;

	if ( 'profile.php' == $pagenow ) {
		if ( HTE_Account()->facebook_account_kit_enabled() ) {
			hocwp_ext_account_facebook_account_kit_sdk();
		}
	}
}

add_action( 'admin_head', 'hocwp_ext_account_admin_head_action' );