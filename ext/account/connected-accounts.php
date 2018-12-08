<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $hocwp_theme;
$user_id   = get_current_user_id();
$social    = HT_Util()->get_theme_options( 'social' );
$api_key   = HT()->get_value_in_array( $social, 'google_api_key' );
$client_id = HT()->get_value_in_array( $social, 'google_client_id' );
$google    = ( ! empty( $api_key ) && ! empty( $client_id ) );
$fb_appid  = HT()->get_value_in_array( $social, 'facebook_app_id' );
$fb_jssdk  = HT()->get_value_in_array( $social, 'facebook_sdk_javascript' );
$facebook  = ( ! empty( $fb_appid ) || ! empty( $fb_jssdk ) );

if ( ! $facebook && ! $google ) {
	return;
}
?>
<h2><?php _e( 'Connected Accounts', 'sb-core' ); ?></h2>
<p><?php _e( 'If you would like to use a 3rd party account such as Facebook or Google to Sign In to this site, you can add it below. You can disconnect these accounts at any time.', 'sb-core' ); ?></p>
<table class="form-table">
	<tbody>
	<?php
	if ( $facebook ) {
		?>
		<tr id="connect_facebook" class="connect-facebook-wrap">
			<th>
				<label
					for="connect_with_facebook"><?php _ex( 'Facebook', 'connect social account', 'sb-core' ); ?></label>
			</th>
			<td>
				<?php
				$facebook_profile = get_user_meta( $user_id, 'facebook_profile', true );

				$data_connect = 0;

				$data_text = _x( 'Connect', 'connect social account', 'sb-core' );

				$data_disconnect_text = _x( 'Disconnect', 'connect social account', 'sb-core' );

				$text = $data_text;

				if ( $facebook_profile ) {
					$data_connect = 1;

					$text = $data_disconnect_text;
					$html = hocwp_ext_account_connect_facebook_avatar_name_html( $facebook_profile );
					echo $html;
				}
				?>
				<button type="button" data-connect="<?php echo esc_attr( $data_connect ); ?>"
				        data-loading-text="<?php echo esc_attr( __( 'Fetching data...', 'sb-core' ) ); ?>"
				        data-text="<?php echo esc_attr( $data_text ); ?>"
				        data-disconnect-text="<?php echo esc_attr( $data_disconnect_text ); ?>"
				        class="button connect-facebook hide-if-no-js"><?php echo esc_html( $text ); ?></button>
			</td>
		</tr>
		<?php
	}
	if ( $google ) {
		?>
		<tr id="connect_google" class="connect-google-wrap">
			<th>
				<label for="connect_with_google"><?php _ex( 'Google', 'connect social account', 'sb-core' ); ?></label>
			</th>
			<td>
				<?php
				$google_profile = get_user_meta( $user_id, 'google_profile', true );

				$data_connect = 0;

				$data_text = _x( 'Connect', 'connect social account', 'sb-core' );

				$data_disconnect_text = _x( 'Disconnect', 'connect social account', 'sb-core' );

				$text = $data_text;

				if ( $google_profile ) {
					$data_connect = 1;

					$text = $data_disconnect_text;
					$html = hocwp_ext_account_connect_google_avatar_name_html( $google_profile );
					echo $html;
				}
				?>
				<button id="connect-google" type="button" data-connect="<?php echo esc_attr( $data_connect ); ?>"
				        data-loading-text="<?php echo esc_attr( __( 'Fetching data...', 'sb-core' ) ); ?>"
				        data-text="<?php echo esc_attr( $data_text ); ?>"
				        data-disconnect-text="<?php echo esc_attr( $data_disconnect_text ); ?>"
				        class="button connect-google hide-if-no-js"><?php echo esc_html( $text ); ?></button>
				<?php
				$social = isset( $hocwp_theme->options['social'] ) ? $hocwp_theme->options['social'] : '';

				if ( ! is_array( $social ) ) {
					$social = array();
				}

				$api_key   = HT()->get_value_in_array( $social, 'google_api_key' );
				$client_id = HT()->get_value_in_array( $social, 'google_client_id' );

				if ( ! empty( $api_key ) && ! empty( $client_id ) ) {
					$args = array(
						'load'     => true,
						'callback' => 'hocwp_theme_connect_google'
					);

					HT_Util()->load_google_javascript_sdk( $args );
					?>
					<script>
						var apiKey = "<?php echo $api_key; ?>";
						var discoveryDocs = ["https://people.googleapis.com/$discovery/rest?version=v1"];
						var clientId = "<?php echo $client_id; ?>";
						var scopes = "profile";
						var authorizeButton = document.getElementById("connect-google");
						var signInParams = {scope: "https://www.googleapis.com/auth/plus.me https://www.googleapis.com/auth/user.addresses.read https://www.googleapis.com/auth/user.birthday.read https://www.googleapis.com/auth/user.emails.read https://www.googleapis.com/auth/user.phonenumbers.read https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile"};

						function updateSigninStatus(isSignedIn) {
							if (isSignedIn) {
								makeApiCall();
							}
						}

						function handleAuthClick() {
							var connect = parseInt(authorizeButton.getAttribute("data-connect"));
							authorizeButton.className += " disabled";
							authorizeButton.setAttribute("disabled", "disabled");
							if (0 === connect) {
								authorizeButton.innerHTML = authorizeButton.getAttribute("data-loading-text");
								gapi.auth2.getAuthInstance().signIn(signInParams);
							} else {
								(function ($) {
									var element = $(authorizeButton),
										container = element.parent(),
										clone = element.clone();
									$.ajax({
										type: "POST",
										dataType: "json",
										url: hocwpTheme.ajaxUrl,
										data: {
											action: "hocwp_theme_connect_social",
											type: "google",
											social_data: '',
											disconnect: 1,
											id: ''
										},
										success: function (response) {
											if (response.success) {
												element = $(clone);
												element.text(element.attr("data-text"));
												element.attr("data-connect", 0);
												container.html(element);
												authorizeButton = document.getElementById("connect-google");
												element.on("click", function () {
													connect = parseInt(element.attr("data-connect"));
													if (0 === connect) {
														container = element.parent();
														clone = element.clone();
														element.addClass("disabled");
														element.text(element.attr("data-loading-text"));
														gapi.auth2.getAuthInstance().signIn(signInParams).then(function () {
															updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
														});
													} else {
														handleAuthClick();
													}
												});
											}
										},
										complete: function () {
											container.find(".connect-google").removeClass("disabled");
											container.find(".connect-google").prop("disabled", false);
										}
									});
								})(jQuery);
							}
						}

						function initClient() {
							gapi.client.init({
								apiKey: apiKey,
								discoveryDocs: discoveryDocs,
								clientId: clientId,
								scope: scopes
							}).then(function () {
								gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

								updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());

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
										var element = $(authorizeButton),
											container = element.parent();
										$.ajax({
											type: "POST",
											dataType: "json",
											url: hocwpTheme.ajaxUrl,
											data: {
												action: "hocwp_theme_connect_social",
												type: "google",
												social_data: response.result,
												disconnect: 0,
												id: userID
											},
											success: function (response) {
												if (response.success) {
													element.text(element.attr("data-disconnect-text"));
													element.attr("data-connect", 1);
													if (response.data.html) {
														element = element.detach();
														container.html(response.data.html);
														container.append(element);
													}
												} else {
													element.text(element.attr("data-text"));
												}
											},
											complete: function () {
												element.removeClass("disabled");
												element.prop("disabled", false);
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
				?>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>