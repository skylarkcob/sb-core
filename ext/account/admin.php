<?php
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

function hocwp_ext_account_admin_scripts() {
	global $pagenow;

	if ( 'profile.php' == $pagenow ) {
		$args = array(
			'load' => true
		);

		HT_Util()->load_facebook_javascript_sdk( $args );
		wp_enqueue_script( 'hocwp-ext-connected-accounts' );
	}
}

add_action( 'admin_enqueue_scripts', 'hocwp_ext_account_admin_scripts' );

function hocwp_ext_account_connect_social_ajax_callback() {
	global $hocwp_theme;
	$type        = HT()->get_method_value( 'type' );
	$social_data = HT()->get_method_value( 'social_data' );
	$disconnect  = HT()->get_method_value( 'disconnect' );

	$user_id = get_current_user_id();
	$data    = array();

	if ( ! empty( $type ) && ( $social_data || 1 == $disconnect ) ) {
		$login       = HT()->get_method_value( 'login' );
		$type        = strtolower( $type );
		$profile_key = $type . '_profile';
		$id_key      = $type . '_id';

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
						$user_data['user_pass']  = wp_generate_password();
						$user_data['user_login'] = $email;
						$user_data['user_email'] = $email;

						$user_id = wp_insert_user( $user_data );
					} else {
						$user_id = new WP_Error( 'registration_not_allowed', __( 'This site does not allow users to register.', 'hocwp-ext' ) );
					}
				}
			}

			if ( HT()->is_positive_number( $user_id ) ) {
				wp_set_auth_cookie( $user_id, true );
				update_user_meta( $user_id, 'last_login', time() );
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
	$options = HT_Util()->get_theme_options( 'account' );
	$cs      = isset( $options['connect_social'] ) ? $options['connect_social'] : '';
	$captcha = isset( $options['captcha'] ) ? $options['captcha'] : '';

	if ( 1 == $cs || 1 == $captcha ) {
		$options = HT_Util()->get_theme_options( 'social' );

		$fai = isset( $options['facebook_app_id'] ) ? $options['facebook_app_id'] : '';
		$gak = isset( $options['google_api_key'] ) ? $options['google_api_key'] : '';
		$gci = isset( $options['google_client_id'] ) ? $options['google_client_id'] : '';
		$rsk = isset( $options['recaptcha_site_key'] ) ? $options['recaptcha_site_key'] : '';
		$rse = isset( $options['recaptcha_secret_key'] ) ? $options['recaptcha_secret_key'] : '';

		if ( empty( $fai ) || empty( $gak ) || empty( $gci ) || empty( $rsk ) || empty( $rse ) ) {
			$msg = sprintf( __( 'You must fully input settings in <a href="%s">Social tab</a> for account extension works normally.', 'hocwp-ext' ), admin_url( 'themes.php?page=hocwp_theme&tab=social' ) );

			$args = array(
				'type'    => 'info',
				'message' => sprintf( '<strong>%s</strong> %s', __( 'Account Extension:', 'hocwp-ext' ), $msg )
			);

			HT_Util()->admin_notice( $args );
		}
	}
}

add_action( 'admin_notices', 'hocwp_ext_account_admin_notices_action' );