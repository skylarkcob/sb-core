<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( is_user_logged_in() ) {
	$url = get_edit_profile_url();
	?>
	<p class="alert alert-warning"><?php _e( 'You are logged in.', 'sb-core' ); ?></p>
	<script>
		window.location.href = "<?php echo $url; ?>";
	</script>
	<?php
	return;
}

$permalink = get_the_permalink();
?>
<div class="register-account">
	<div class="register-form-box maxw-600 mx-auto border rounded pd-20">
		<h2 class="text-left"><?php _e( 'Sign up', 'sb-core' ); ?></h2>

		<form id="registerForm" class="register-form mt-20 clearfix" action="" method="post">
			<?php
			wp_nonce_field();

			$user_login = '';
			$user_email = '';

			$added = false;

			if ( isset( $_POST['action'] ) && 'user_register' == $_POST['action'] ) {
				$nonce = $_POST['_wpnonce'] ?? '';

				if ( wp_verify_nonce( $nonce ) ) {
					$errors = new WP_Error();

					$user_login = $_POST['user_login'] ?? '';

					$user_email = $_POST['user_email'] ?? '';

					$user_pass = $_POST['user_pass'] ?? '';

					$pass2 = $_POST['user_pass2'] ?? '';

					if ( empty( $user_login ) || empty( $user_email ) || empty( $user_pass ) ) {
						$errors->add( 'missing_field', __( 'Please enter your information.', 'sb-core' ) );
					} elseif ( ! is_email( $user_email ) ) {
						$errors->add( 'invalid_email', __( 'Invalid email.', 'sb-core' ) );
					} elseif ( $user_pass != $pass2 ) {
						$errors->add( 'invalid_password', __( 'Passwords do not match!', 'sb-core' ) );
					} elseif ( username_exists( $user_login ) || email_exists( $user_email ) ) {
						$errors->add( 'exists', __( 'Username or email exists!', 'sb-core' ) );
					} else {
						$errors = register_new_user( $user_login, $user_email );

						if ( ( ! ( $errors instanceof WP_Error ) || empty( $errors->get_error_code() ) ) && HT()->is_positive_number( $errors ) ) {
							$user_id = $errors;

							$added = true;

							do_action( 'hocwp_theme_extension_account_user_added', $user_id );

							$data = array(
								'user_login' => $user_login,
								'user_email' => $user_email,
								'user_pass'  => $user_pass,
								'ID'         => $user_id
							);

							$errors = wp_update_user( $data );

							if ( $errors && ! ( $errors instanceof WP_Error ) ) {
								do_action( 'hocwp_theme_extension_account_user_updated', $user_id );
							}
						}
					}

					if ( $errors instanceof WP_Error ) {
						$error = $errors->get_error_messages();

						if ( is_array( $error ) ) {
							foreach ( $error as $err ) {
								?>
								<p class="alert alert-error alert-danger"><?php echo $err; ?></p>
								<?php
							}
						} else {
							?>
							<p class="alert alert-error alert-danger"><?php echo $error; ?></p>
							<?php
						}
					}
				}
			}

			if ( $added ) {
				?>
				<p class="alert alert-success"><?php _e( 'Your account has been created successfully.', 'sb-core' ); ?></p>
				<script>
					setTimeout(function () {
						window.location.href = "<?php echo wp_login_url(); ?>";
					}, 2000);
				</script>
				<?php
			}

			if ( ! $added ) {
				?>
				<div class="row form-group mb-15">
					<div class="col-md-3">
						<label for="user-login"><?php _e( 'Username', 'sb-core' ); ?></label>
					</div>
					<div class="col-md-9">
						<input id="user-login" class="required input form-control" type="text" name="user_login"
						       value="<?php echo $user_login; ?>" autocomplete="username">
					</div>
				</div>
				<div class="row form-group mb-15">
					<div class="col-md-3">
						<label for="user-email"><?php _e( 'Email', 'sb-core' ); ?></label>
					</div>
					<div class="col-md-9">
						<input id="user-email" class="required input form-control" type="email" name="user_email"
						       value="<?php echo $user_email; ?>">
					</div>
				</div>
				<div class="row form-group mb-15">
					<div class="col-md-3">
						<label for="user-pass"><?php _e( 'Password', 'sb-core' ); ?></label>
					</div>
					<div class="col-md-9">
						<input id="user-pass" class="password required input form-control" type="password"
						       name="user_pass" autocomplete="new-password">
					</div>
				</div>
				<div class="row form-group mb-15">
					<div class="col-md-3">
						<label for="user-pass2"><?php _e( 'Confirm Password', 'sb-core' ); ?></label>
					</div>
					<div class="col-md-9">
						<input id="user-pass2" class="password required input form-control" type="password"
						       name="user_pass2" autocomplete="new-password">
					</div>
				</div>
				<div class="row form-group mb-15">
					<div class="col-md-9 col-md-offset-3 offset-md-3">
						<div class="mb-20">
							<?php do_action( 'register_form' ); ?>
						</div>
						<input type="hidden" name="action" value="user_register">
						<?php $redirect_to = $_REQUEST['redirect_to'] ?? ''; ?>
						<input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>">
						<button class="submit btn btn-success w-full" name="register_submit" type="submit">
							<?php _e( 'Register', 'sb-core' ); ?>
						</button>
						<?php
						if ( ! $added ) {
							?>
							<div class="register-form-info mt-15">
								<?php
								$page_id = get_option( 'wp_page_for_privacy_policy' );

								if ( HT()->is_positive_number( $page_id ) ) {
									$page = get_post( $page_id );

									if ( $page instanceof WP_Post ) {
										?>
										<p>
											<?php printf( __( 'By creating an account, you agree to <a href="%s">terms and privacy policy</a>.', 'sb-core' ), get_permalink( $page ) ); ?>
										</p>
										<?php
									}
								}
								?>
								<p>
									<?php printf( __( 'Already have an account? <a href="%s">Sign in</a>', 'sb-core' ), wp_login_url() ); ?>
								</p>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
			?>
		</form>
	</div>
</div>