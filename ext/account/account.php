<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'HOCWP_EXT_ACCOUNT_PATH', dirname( __FILE__ ) );
const HOCWP_EXT_ACCOUNT_URL = HOCWP_EXT_URL . '/ext/account';

require HOCWP_EXT_ACCOUNT_PATH . '/functions.php';

global $pagenow;

/*
 * Register all styles and scripts for admin, login and front-end page.
 */
function hocwp_ext_account_global_scripts() {
	wp_register_script( 'hocwp-ext-connected-accounts', HOCWP_EXT_URL . '/js/connected-accounts' . HOCWP_THEME_JS_SUFFIX, array(
		'jquery',
		'hocwp-theme'
	), false, true );

	wp_register_script( 'hocwp-ext-facebook-account-kit', HOCWP_EXT_URL . '/js/facebook-account-kit' . HOCWP_THEME_JS_SUFFIX, array(
		'jquery',
		'hocwp-theme',
		'hocwp-theme-ajax-button'
	), false, true );

	$options = HT_Util()->get_theme_options( 'account' );

	$country_code = $options['fac_country_code'] ?? '';
	$country_code = str_replace( '+', '', $country_code );

	$btn_connect_phone = hocwp_ext_account_connect_facebook_account_kit_button( 'connect-phone', __( 'Connect', 'sb-core' ), __( 'Connect phone number with Facebook account kit', 'sb-core' ) );
	$btn_connect_email = hocwp_ext_account_connect_facebook_account_kit_button( 'connect-email', __( 'Connect', 'sb-core' ), __( 'Connect email address with Facebook account kit', 'sb-core' ) );

	if ( is_user_logged_in() ) {
		$user = HTE_Account()->user;

		$fac = HTE_Account()->get_user_facebook_account_kit();

		if ( is_array( $fac ) && isset( $fac['email'] ) && $user->user_email == $fac['email'] ) {
			$btn_connect_email = hocwp_ext_account_connect_facebook_account_kit_button( 'disconnect-email', __( 'Disconnect', 'sb-core' ), __( 'Disconnect email address from Facebook account kit', 'sb-core' ) );
		}

		$phone = get_user_meta( $user->ID, 'phone', true );

		if ( is_array( $fac ) && isset( $fac['phone'] ) && HTE_Account()->compare_phone_numbers( $phone, $fac['phone'] ) ) {
			$btn_connect_phone = hocwp_ext_account_connect_facebook_account_kit_button( 'disconnect-phone', __( 'Disconnect', 'sb-core' ), __( 'Disconnect phone number from Facebook account kit', 'sb-core' ) );
		}
	}

	$l10n = array(
		'app_id'                   => HT_Options()->get_tab( 'facebook_app_id', '', 'social' ),
		'nonce'                    => wp_create_nonce( 'hte_facebook_account_kit' ),
		'api_version'              => $options['fac_api_version'] ?? '',
		'display'                  => $options['fac_display'] ?? 'popup',
		'debug'                    => HOCWP_THEME_DEVELOPING,
		'country_code'             => $country_code,
		'status_NOT_AUTHENTICATED' => __( 'Authentication failure', 'sb-core' ),
		'status_BAD_PARAMS'        => __( 'Bad parameters', 'sb-core' ),
		'app_secret'               => $options['fac_app_secret'] ?? '',
		'connect_phone_button'     => $btn_connect_phone,
		'connect_email_button'     => $btn_connect_email,
		'confirm_disconnect_email' => __( 'Are you sure you want to disconnect your email address?', 'sb-core' ),
		'confirm_disconnect_phone' => __( 'Are you sure you want to disconnect your phone?', 'sb-core' )
	);

	wp_localize_script( 'hocwp-ext-facebook-account-kit', 'hteFacebookAccountKit', $l10n );
}

add_action( 'wp_enqueue_scripts', 'hocwp_ext_account_global_scripts' );
add_action( 'admin_enqueue_scripts', 'hocwp_ext_account_global_scripts' );
add_action( 'login_enqueue_scripts', 'hocwp_ext_account_global_scripts' );


if ( is_admin() ) {
	require HOCWP_EXT_ACCOUNT_PATH . '/admin.php';
} else {
	require HOCWP_EXT_ACCOUNT_PATH . '/front-end.php';
}

if ( 'wp-login.php' == $pagenow ) {
	require HOCWP_EXT_ACCOUNT_PATH . '/default-login-page.php';
}

function hocwp_ext_account_connect_social_buttons( $options = '', $args = array() ) {
	if ( empty( $options ) ) {
		$options = HT_Util()->get_theme_options( 'account' );
	}

	$cs = $options['connect_social'] ?? '';

	if ( 1 == $cs ) {
		$social    = HT_Util()->get_theme_options( 'social' );
		$api_key   = HT()->get_value_in_array( $social, 'google_api_key' );
		$client_id = HT()->get_value_in_array( $social, 'google_client_id' );
		$google    = ( ! empty( $api_key ) && ! empty( $client_id ) );
		$fb_appid  = HT()->get_value_in_array( $social, 'facebook_app_id' );
		$fb_jssdk  = HT()->get_value_in_array( $social, 'facebook_sdk_javascript' );
		$facebook  = ( ! empty( $fb_appid ) || ! empty( $fb_jssdk ) );

		ob_start();

		if ( $google ) {
			$params = array(
				'load'     => true,
				'callback' => 'hocwp_theme_connect_google'
			);

			HT_Util()->load_google_javascript_sdk( $params );
		}
		?>
        <p class="connected-accounts">
			<?php
			if ( $google ) {
				$google_button = $args['google_button'] ?? '';

				if ( empty( $google_button ) ) {
					?>
                    <button id="connect-google" title="<?php _e( 'Sign up with your Google account', 'sb-core' ); ?>"
                            class="btn btn-danger connect-google w-full mb-10" data-login="1" type="button">
                        <i class="fa fab fa-google mr-5" aria-hidden="true"></i><span
                                class="dashicons dashicons-googleplus mr-5"></span><?php _e( 'Continue with Google', 'sb-core' ); ?>
                    </button>
					<?php
				} else {
					echo $google_button;
				}
			}

			if ( $facebook ) {
				$facebook_button = $args['facebook_button'] ?? '';

				if ( empty( $facebook_button ) ) {
					?>
                    <button id="connect-facebook"
                            title="<?php _e( 'Sign up with your Facebook account', 'sb-core' ); ?>"
                            class="btn btn-primary connect-facebook w-full" data-login="1" type="button">
                        <i class="fa fab fa-facebook-f mr-5"
                           aria-hidden="true"></i><span
                                class="dashicons dashicons-facebook mr-5"></span><?php _e( 'Continue with Facebook', 'sb-core' ); ?>
                    </button>
					<?php
				} else {
					echo $facebook_button;
				}
			}
			?>
        </p>
		<?php
		if ( $google ) {
			?>
            <script>
                let apiKey = "<?php echo $api_key; ?>";
                let discoveryDocs = ["https://people.googleapis.com/$discovery/rest?version=v1"];
                let clientId = "<?php echo $client_id; ?>";
                let scopes = "profile";
                let authorizeButton = document.getElementById("connect-google");
                let signInParams = {scope: "https://www.googleapis.com/auth/plus.me https://www.googleapis.com/auth/user.addresses.read https://www.googleapis.com/auth/user.birthday.read https://www.googleapis.com/auth/user.emails.read https://www.googleapis.com/auth/user.phonenumbers.read https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile"};
                let callAPI = false;

                function updateSigninStatus(isSignedIn) {
                    if (isSignedIn) {
                        if (!callAPI) {
                            makeApiCall();
                            callAPI = true;
                        }
                    }
                }

                function handleAuthClick() {
                    authorizeButton.className += " disabled";
                    authorizeButton.setAttribute("disabled", "disabled");
                    gapi.auth2.getAuthInstance().signIn(signInParams).then(function () {
                        updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
                    });
                }

                function initClient() {
                    let settings = {
                        apiKey: apiKey,
                        discoveryDocs: discoveryDocs,
                        clientId: clientId,
                        scope: scopes
                    };

                    gapi.client.init(settings).then(function () {
                        gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);
                        authorizeButton.onclick = handleAuthClick;
                    });
                }

                function makeApiCall() {
                    gapi.client.people.people.get({
                        resourceName: "people/me",
                        personFields: "names,birthdays,genders,addresses,emailAddresses,phoneNumbers,photos"
                    }).then(function (response) {
                        if (response.status === 200) {
                            let body = JSON.parse(response.body),
                                userID = body.resourceName.replace("people/", "");

                            (function ($) {
                                let element = $(authorizeButton);

                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    url: hocwpTheme.ajaxUrl,
                                    data: {
                                        action: "hocwp_theme_connect_social",
                                        type: "google",
                                        social_data: response.result,
                                        disconnect: 0,
                                        id: userID,
                                        login: 1
                                    },
                                    success: function (response) {
                                        if (response.success) {
                                            if ($.trim(response.data.redirect_to)) {
                                                window.location.href = response.data.redirect_to;
                                            } else {
                                                let inputRedirectTo = $("input[name='redirect_to']");

                                                if (inputRedirectTo.length && $.trim(inputRedirectTo.val())) {
                                                    window.location.href = inputRedirectTo.val();
                                                } else {
                                                    window.location.reload();
                                                }
                                            }
                                        } else {
                                            if ($.trim(response.data.message)) {
                                                alert(response.data.message);
                                            }

                                            authorizeButton.onclick = handleAuthClick;
                                        }
                                    },
                                    complete: function () {
                                        element.removeClass("disabled");
                                        element.prop("disabled", false);
                                        callAPI = false;
                                    }
                                });
                            })(jQuery);
                        }
                    });
                }

                function hocwp_theme_connect_google() {
                    gapi.load("client:auth2", initClient);
                }
            </script>
			<?php
		}

		return ob_get_clean();
	}

	return '';
}

// Account Kit services no longer available
function hocwp_ext_account_facebook_account_kit_sdk() {
	$locale = apply_filters( 'hocwp_theme_extension_facebook_account_kit_language', get_locale() );

	if ( 'vi' == $locale ) {
		$locale = 'vi_VN';
	}

	echo PHP_EOL;
	?>
    <!-- HTTPS required. HTTP will give a 403 forbidden response -->
    <script src="https://sdk.accountkit.com/<?php echo $locale; ?>/sdk.js"></script>
	<?php
	echo PHP_EOL;
}

// Account Kit services no longer available
function hocwp_ext_account_facebook_account_kit_button() {
	ob_start();
	?>
    <p class="facebook-account-kit">
        <button id="sms-or-email" title="<?php echo esc_attr( __( 'Connect SMS/Email', 'sb-core' ) ); ?>"
                class="btn btn-primary sms-or-email w-full"
                data-login="1" type="button">
            <i class="fa fa-link mr-5" aria-hidden="true"></i>
            <span class="dashicons dashicons-admin-links mr-5"></span>
            <span><?php _e( 'Connect SMS/Email', 'sb-core' ); ?></span>
        </button>
    </p>
    <div class="fac-popup verify-box">
        <div class="inner">
            <button class="close-btn" type="button">&times;</button>
            <div class="buttons">
                <button id="verify-phone" title="<?php echo esc_attr( __( 'Verify your phone number', 'sb-core' ) ); ?>"
                        class="btn btn-primary verify-phone w-full"
                        data-login="1" type="button">
                    <i class="fa fa-mobile mr-5" aria-hidden="true"></i>
                    <span class="dashicons dashicons-smartphone mr-5"></span>
                    <span><?php _e( 'Verify your phone number', 'sb-core' ); ?></span>
                </button>
                <button id="verify-email"
                        title="<?php echo esc_attr( __( 'Verify your email address', 'sb-core' ) ); ?>"
                        class="btn btn-primary verify-email w-full"
                        data-login="1" type="button">
                    <i class="fa fa-envelope mr-5" aria-hidden="true"></i>
                    <span class="dashicons dashicons-email-alt mr-5"></span>
                    <span><?php _e( 'Verify your email address', 'sb-core' ); ?></span>
                </button>
            </div>
			<?php hocwp_ext_account_facebook_account_kit_sdk(); ?>
        </div>
    </div>
	<?php
	return ob_get_clean();
}

function hocwp_ext_account_add_recaptcha( $html, $args ) {
	$options = HT_Util()->get_theme_options( 'account' );
	$captcha = $options['captcha'] ?? '';

	if ( 1 == $captcha ) {
		ob_start();
		HT_CAPTCHA()->display_html();
		$html .= ob_get_clean();
	}

	return $html;
}

add_filter( 'login_form_middle', 'hocwp_ext_account_add_recaptcha', 10, 2 );

function hocwp_ext_account_login_form_recaptcha() {
	$options = HT_Util()->get_theme_options( 'account' );
	$captcha = $options['captcha'] ?? '';

	if ( 1 == $captcha && HT_CAPTCHA()->check_config_valid() ) {
		HT_CAPTCHA()->display_html();
	}
}

add_action( 'login_form', 'hocwp_ext_account_login_form_recaptcha', 99 );
add_action( 'register_form', 'hocwp_ext_account_login_form_recaptcha', 99 );
add_action( 'lostpassword_form', 'hocwp_ext_account_login_form_recaptcha', 99 );

function hte_account_login_footer_action() {
	$options = HT_Util()->get_theme_options( 'account' );
	$captcha = $options['captcha'] ?? '';

	if ( 1 == $captcha && HT_CAPTCHA()->check_config_valid() ) {
		echo HT_CAPTCHA()->add_recaptcha_script( HT_CAPTCHA() );
	}
}

add_action( 'login_footer', 'hte_account_login_footer_action', 99 );

function hte_account_login_form_bottom_filter( $html ) {
	$options = HT_Util()->get_theme_options( 'account' );
	$captcha = $options['captcha'] ?? '';

	if ( 1 == $captcha && HT_CAPTCHA()->check_config_valid() ) {
		$html = HT_CAPTCHA()->add_recaptcha_script( HT_CAPTCHA(), $html );
	}

	return $html;
}

add_filter( 'login_form_bottom', 'hte_account_login_form_bottom_filter', 99 );

function hocwp_ext_connected_socials_horizontal_bar() {
	return '<div class="social-wrapper-title ng-scope text-center mt-10 mb-10"><span>' . _x( 'Or', 'connected socials', 'sb-core' ) . '</span></div>';
}

function hocwp_ext_account_add_connect_social_buttons() {
	$options = HT_Util()->get_theme_options( 'account' );

	$cs = $options['connect_social'] ?? '';

	if ( 1 == $cs ) {
		echo hocwp_ext_connected_socials_horizontal_bar();
		echo hocwp_ext_account_connect_social_buttons( $options );
	}

	// Account Kit services no longer available
	$account_kit = $options['account_kit'] ?? '';

	if ( 1 == $account_kit && false ) {
		$has_bar = ( 1 == $cs );

		if ( ! $has_bar ) {
			echo hocwp_ext_connected_socials_horizontal_bar();
		}

		echo hocwp_ext_account_facebook_account_kit_button();
	}
}

add_action( 'login_form', 'hocwp_ext_account_add_connect_social_buttons', 99 );
add_action( 'register_form', 'hocwp_ext_account_add_connect_social_buttons', 99 );

function hocwp_ext_account_login_form_top( $html, $args ) {
	$options = HT_Util()->get_theme_options( 'account' );

	$cs = $options['connect_social'] ?? '';

	if ( 1 == $cs ) {
		$html .= hocwp_ext_account_connect_social_buttons( $options );
		$html .= hocwp_ext_connected_socials_horizontal_bar();
	}

	$account_kit = $options['account_kit'] ?? '';

	if ( 1 == $account_kit ) {
		$html .= hocwp_ext_account_facebook_account_kit_button();

		$has_bar = ( 1 == $cs );

		if ( ! $has_bar ) {
			$html .= hocwp_ext_connected_socials_horizontal_bar();
		}
	}

	return $html;
}

add_filter( 'login_form_top', 'hocwp_ext_account_login_form_top', 10, 2 );

function hocwp_ext_account_wp_authenticate_user( $user ) {
	if ( ! is_wp_error( $user ) ) {
		$options = HT_Util()->get_theme_options( 'account' );
		$captcha = $options['captcha'] ?? '';

		if ( 1 == $captcha && HT_CAPTCHA()->check_config_valid() ) {
			$response = HT_CAPTCHA()->check_valid();

			$user = HT_CAPTCHA()->control_captcha_errors( $response, $user );
		}
	}

	return $user;
}

add_filter( 'wp_authenticate_user', 'hocwp_ext_account_wp_authenticate_user' );

function hocwp_ext_account_lostpassword_post( $errors ) {

}

add_action( 'lostpassword_post', 'hocwp_ext_account_lostpassword_post' );

function hte_account_lostpassword_errors_filter( $errors ) {
	$options = HT_Util()->get_theme_options( 'account' );
	$captcha = $options['captcha'] ?? '';

	if ( 1 == $captcha && HT_CAPTCHA()->check_config_valid() ) {
		$response = HT_CAPTCHA()->check_valid();

		$errors = HT_CAPTCHA()->control_captcha_errors( $response, $errors );
	}

	return $errors;
}

add_filter( 'lostpassword_errors', 'hte_account_lostpassword_errors_filter' );

function hocwp_ext_account_after_setup_theme_action() {
	register_nav_menu( 'login', __( 'Login footer', 'sb-core' ) );
}

add_action( 'after_setup_theme', 'hocwp_ext_account_after_setup_theme_action' );

function hocwp_ext_account_set_logged_in_cookie_action( $logged_in_cookie, $expire, $expiration, $user_id ) {
	update_user_meta( $user_id, 'last_login', current_time( 'timestamp' ) );
	do_action( 'hocwp_theme_extension_account_user_logged_in', $user_id );
}

add_action( 'set_logged_in_cookie', 'hocwp_ext_account_set_logged_in_cookie_action', 10, 4 );

function hocwp_ext_account_body_classes_filter( $classes ) {
	$options = HT_Util()->get_theme_options( 'account' );

	$cs = $options['custom_style'] ?? '';

	if ( 1 == $cs ) {
		$classes[] = 'custom-login-style';
	}

	return $classes;
}

add_filter( 'login_body_class', 'hocwp_ext_account_body_classes_filter' );
add_filter( 'body_class', 'hocwp_ext_account_body_classes_filter' );