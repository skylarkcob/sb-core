<?php
define( 'HOCWP_EXT_ACCOUNT_PATH', dirname( __FILE__ ) );
define( 'HOCWP_EXT_ACCOUNT_URL', HOCWP_EXT_URL . '/ext/account' );

require HOCWP_EXT_ACCOUNT_PATH . '/functions.php';

function hocwp_ext_account_global_scripts() {
	wp_register_script( 'hocwp-ext-connected-accounts', HOCWP_EXT_URL . '/js/connected-accounts' . HOCWP_THEME_JS_SUFFIX, array(
		'jquery',
		'hocwp-theme'
	), false, true );
}

add_action( 'wp_enqueue_scripts', 'hocwp_ext_account_global_scripts' );
add_action( 'admin_enqueue_scripts', 'hocwp_ext_account_global_scripts' );
add_action( 'login_enqueue_scripts', 'hocwp_ext_account_global_scripts' );

if ( is_admin() ) {
	require HOCWP_EXT_ACCOUNT_PATH . '/admin.php';
} else {
	require HOCWP_EXT_ACCOUNT_PATH . '/front-end.php';
}

function hocwp_ext_account_connect_social_buttons() {
	$options = HT_Util()->get_theme_options( 'account' );
	$cs      = isset( $options['connect_social'] ) ? $options['connect_social'] : '';
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
			$args = array(
				'load'     => true,
				'callback' => 'hocwp_theme_connect_google'
			);
			HT_Util()->load_google_javascript_sdk( $args );
		}
		?>
        <p class="connected-accounts">
			<?php
			if ( $google ) {
				?>
                <button id="connect-google" title="<?php _e( 'Sign up with your Google account', 'hocwp-theme' ); ?>"
                        class="btn btn-danger connect-google" data-login="1" type="button">
                    <i class="fa fa-google" aria-hidden="true"></i><?php _e( 'Continue with Google', 'hocwp-theme' ); ?>
                </button>
				<?php
			}
			if ( $facebook ) {
				?>
                <button id="connect-facebook"
                        title="<?php _e( 'Sign up with your Facebook account', 'hocwp-theme' ); ?>"
                        class="btn btn-primary connect-facebook" data-login="1" type="button">
                    <i class="fa fa-facebook"
                       aria-hidden="true"></i><?php _e( 'Continue with Facebook', 'hocwp-theme' ); ?>
                </button>
				<?php
			}
			?>
        </p>
		<?php
		if ( $google ) {
			?>
            <script>
                var apiKey = "<?php echo $api_key; ?>";
                var discoveryDocs = ["https://people.googleapis.com/$discovery/rest?version=v1"];
                var clientId = "<?php echo $client_id; ?>";
                var scopes = "profile";
                var authorizeButton = document.getElementById("connect-google");
                var signInParams = {scope: "https://www.googleapis.com/auth/plus.me https://www.googleapis.com/auth/user.addresses.read https://www.googleapis.com/auth/user.birthday.read https://www.googleapis.com/auth/user.emails.read https://www.googleapis.com/auth/user.phonenumbers.read https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile"};
                var callAPI = false;

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
                    gapi.client.init({
                        apiKey: apiKey,
                        discoveryDocs: discoveryDocs,
                        clientId: clientId,
                        scope: scopes
                    }).then(function () {
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
                            var body = JSON.parse(response.body),
                                userID = body.resourceName.replace("people/", "");
                            (function ($) {
                                var element = $(authorizeButton);
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
                                                var inputRedirectTo = $("input[name='redirect_to']");
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

function hocwp_ext_account_login_form_top( $html, $args ) {
	$html .= hocwp_ext_account_connect_social_buttons();

	return $html;
}

add_filter( 'login_form_top', 'hocwp_ext_account_login_form_top', 10, 2 );

function hocwp_ext_account_add_recaptcha( $html, $args ) {
	$options = HT_Util()->get_theme_options( 'account' );
	$captcha = isset( $options['captcha'] ) ? $options['captcha'] : '';
	if ( 1 == $captcha ) {
		ob_start();
		HT_Util()->recaptcha();
		$html .= ob_get_clean();
	}

	return $html;
}

add_filter( 'login_form_middle', 'hocwp_ext_account_add_recaptcha', 10, 2 );

function hocwp_ext_account_login_form_recaptcha() {
	$options = HT_Util()->get_theme_options( 'account' );
	$captcha = isset( $options['captcha'] ) ? $options['captcha'] : '';
	if ( 1 == $captcha ) {
		HT_Util()->recaptcha();
	}
}

add_action( 'login_form', 'hocwp_ext_account_login_form_recaptcha', 99 );
add_action( 'register_form', 'hocwp_ext_account_login_form_recaptcha', 99 );
add_action( 'lostpassword_form', 'hocwp_ext_account_login_form_recaptcha', 99 );

function hocwp_ext_account_add_connect_social_buttons() {
	echo hocwp_ext_account_connect_social_buttons();
}

add_action( 'login_form', 'hocwp_ext_account_add_connect_social_buttons', 99 );
add_action( 'register_form', 'hocwp_ext_account_add_connect_social_buttons', 99 );

function hocwp_ext_account_wp_authenticate_user( $user ) {
	if ( ! is_wp_error( $user ) ) {
		$options = HT_Util()->get_theme_options( 'account' );
		$captcha = isset( $options['captcha'] ) ? $options['captcha'] : '';
		if ( 1 == $captcha ) {
			$response = HT_Util()->recaptcha_valid();
			if ( ! $response ) {
				$user = new WP_Error( 'invalid_captcha', __( '<strong>Error:</strong> Please correct the captcha.', 'hocwp-ext' ) );
			}
		}
	}

	return $user;
}

add_filter( 'wp_authenticate_user', 'hocwp_ext_account_wp_authenticate_user' );

function hocwp_ext_account_registration_errors( $errors ) {
	$options = HT_Util()->get_theme_options( 'account' );
	$captcha = isset( $options['captcha'] ) ? $options['captcha'] : '';
	if ( 1 == $captcha ) {
		$response = HT_Util()->recaptcha_valid();
		if ( ! $response ) {
			if ( ! is_wp_error( $errors ) || ! ( $errors instanceof WP_Error ) ) {
				$errors = new WP_Error( 'invalid_captcha', __( '<strong>Error:</strong> Please correct the captcha.', 'hocwp-ext' ) );
			} else {
				$errors->add( 'invalid_captcha', __( '<strong>Error:</strong> Please correct the captcha.', 'hocwp-ext' ) );
			}
		}
	}

	return $errors;
}

add_filter( 'registration_errors', 'hocwp_ext_account_registration_errors' );

function hocwp_ext_account_lostpassword_post( $errors ) {
	$options = HT_Util()->get_theme_options( 'account' );
	$captcha = isset( $options['captcha'] ) ? $options['captcha'] : '';
	if ( 1 == $captcha ) {
		$response = HT_Util()->recaptcha_valid();
		if ( ! $response ) {
			if ( ! is_wp_error( $errors ) || ! ( $errors instanceof WP_Error ) ) {
				$errors = new WP_Error( 'invalid_captcha', __( '<strong>Error:</strong> Please correct the captcha.', 'hocwp-ext' ) );
			} else {
				$errors->add( 'invalid_captcha', __( '<strong>Error:</strong> Please correct the captcha.', 'hocwp-ext' ) );
			}
		}
	}
}

add_action( 'lostpassword_post', 'hocwp_ext_account_lostpassword_post' );