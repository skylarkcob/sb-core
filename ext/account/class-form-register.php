<?php
defined( 'ABSPATH' ) || exit;

class HTE_Account_Form_Register {
	public $layout;

	public function __construct( $layout = '' ) {
		$this->layout = $layout;
		$this->html();
	}

	public function fields_layout_2( $user_email, $phone, $first_name, $last_name ) {
		?>
        <div class="row form-group mb-15">
            <div class="col-md-3">
                <label for="last-name"><?php _e( 'Last Name', 'sb-core' ); ?> <span class="required">*</span></label>
            </div>
            <div class="col-md-9">
                <input id="last-name" class="required input form-control" type="text"
                       name="last_name" required placeholder="<?php esc_attr_e( 'Enter last name', 'sb-core' ); ?>"
                       value="<?php echo $last_name; ?>">
            </div>
        </div>
        <div class="row form-group mb-15">
            <div class="col-md-3">
                <label for="first-name"><?php _e( 'First Name', 'sb-core' ); ?> <span class="required">*</span></label>
            </div>
            <div class="col-md-9">
                <input id="first-name" class="required input form-control" type="text"
                       name="first_name" required placeholder="<?php esc_attr_e( 'Enter first name', 'sb-core' ); ?>"
                       value="<?php echo $first_name; ?>">
            </div>
        </div>
        <div class="row form-group mb-15">
            <div class="col-md-3">
                <label for="phone"><?php _e( 'Phone', 'sb-core' ); ?> <span class="required">*</span></label>
            </div>
            <div class="col-md-9">
                <input id="phone" class="required input form-control" type="text"
                       name="phone" required placeholder="<?php esc_attr_e( 'Enter phone number', 'sb-core' ); ?>"
                       value="<?php echo $phone; ?>">
            </div>
        </div>
        <div class="row form-group mb-15">
            <div class="col-md-3">
                <label for="user-email"><?php _e( 'Email', 'sb-core' ); ?> <span class="required">*</span></label>
            </div>
            <div class="col-md-9">
                <input id="user-email" class="required input form-control" type="email"
                       name="user_email" required placeholder="<?php esc_attr_e( 'Enter email address', 'sb-core' ); ?>"
                       value="<?php echo $user_email; ?>">
            </div>
        </div>
        <div class="row form-group mb-15">
            <div class="col-md-3">
                <label for="user-pass"><?php _e( 'Password', 'sb-core' ); ?> <span class="required">*</span></label>
            </div>
            <div class="col-md-9">
                <input id="user-pass" class="password required input form-control" type="password"
                       name="user_pass" autocomplete="new-password" required
                       placeholder="<?php esc_attr_e( 'Enter password', 'sb-core' ); ?>">
            </div>
        </div>
		<?php
	}

	public function fields_default( $user_login, $user_email ) {
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
                <input id="user-email" class="required input form-control" type="email"
                       name="user_email"
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
		<?php
	}

	public function fields_submit() {
		switch ( $this->layout ) {
			case 2:
				$btn_text = __( 'Create account', 'sb-core' );
				break;
			default:
				$btn_text = __( 'Register', 'sb-core' );
		}
		?>
        <div class="row form-group mb-15">
            <div class="col-md-9 col-md-offset-3 offset-md-3">
                <div class="mb-20">
					<?php do_action( 'register_form' ); ?>
                </div>
                <input type="hidden" name="action" value="user_register">
				<?php $redirect_to = $_REQUEST['redirect_to'] ?? ''; ?>
                <input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>">
                <button class="submit btn btn-success w-full" name="register_submit" type="submit">
					<?php echo $btn_text; ?>
                </button>
				<?php
				if ( empty( $this->layout ) ) {
					?>
                    <div class="register-form-info mt-15">
						<?php
						$page_id = get_option( 'wp_page_for_privacy_policy' );

						if ( ht()->is_positive_number( $page_id ) ) {
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

	public function html() {
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
				<?php
				switch ( $this->layout ) {
					case 2:
						?>
                        <div class="function">
                            <a class=""
                               href="<?php echo esc_attr( wp_login_url() ); ?>"><?php _e( 'Login', 'sb-core' ); ?></a>
                            <a class="active"
                               href="<?php echo esc_attr( wp_registration_url() ); ?>"><?php _e( 'Register', 'sb-core' ); ?></a>
                        </div>
						<?php
						break;
					default:
						?>
                        <h2 class="text-left"><?php _e( 'Sign up', 'sb-core' ); ?></h2>
					<?php
				}
				?>
                <form id="registerForm" class="register-form mt-20 clearfix info" action="" method="post">
					<?php
					wp_nonce_field();

					$user_login = '';
					$user_email = '';
					$phone      = '';
					$first_name = '';
					$last_name  = '';

					$added = false;

					if ( isset( $_POST['action'] ) && 'user_register' == $_POST['action'] ) {
						$nonce = $_POST['_wpnonce'] ?? '';

						if ( wp_verify_nonce( $nonce ) ) {
							$errors = new WP_Error();

							$user_login = $_POST['user_login'] ?? '';

							$user_email = $_POST['user_email'] ?? '';

							$phone = $_POST['phone'] ?? '';

							$first_name = $_POST['first_name'] ?? '';
							$last_name  = $_POST['last_name'] ?? '';

							$user_pass = $_POST['user_pass'] ?? '';

							$pass2 = $_POST['user_pass2'] ?? '';

							if ( empty( $user_login ) ) {
								$user_login = $user_email;
							}

							if ( ( empty( $this->layout ) && empty( $user_login ) ) || empty( $user_email ) || empty( $user_pass ) ) {
								$errors->add( 'missing_field', __( 'Please enter your information.', 'sb-core' ) );
							} elseif ( ! is_email( $user_email ) ) {
								$errors->add( 'invalid_email', __( 'Invalid email.', 'sb-core' ) );
							} elseif ( empty( $this->layout ) && $user_pass != $pass2 ) {
								$errors->add( 'invalid_password', __( 'Passwords do not match!', 'sb-core' ) );
							} elseif ( username_exists( $user_login ) || email_exists( $user_email ) ) {
								$errors->add( 'exists', __( 'Username or email exists!', 'sb-core' ) );
							} else {
								$errors = register_new_user( $user_login, $user_email );

								if ( ( ! ( $errors instanceof WP_Error ) || empty( $errors->get_error_code() ) ) && ht()->is_positive_number( $errors ) ) {
									$user_id = $errors;

									$added = true;

									do_action( 'hocwp_theme_extension_account_user_added', $user_id );

									$data = array(
										'user_login' => $user_login,
										'user_email' => $user_email,
										'user_pass'  => $user_pass,
										'ID'         => $user_id
									);

									if ( ! empty( $first_name ) ) {
										$data['first_name'] = $first_name;
									}

									if ( ! empty( $last_name ) ) {
										$data['last_name'] = $last_name;
									}

									if ( ! empty( $first_name ) && ! empty( $last_name ) ) {
										$data['display_name'] = $last_name . ' ' . $first_name;
									}

									$errors = wp_update_user( $data );

									update_user_meta( $user_id, 'phone', $phone );
									wp_set_password( $user_pass, $user_id );

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
						switch ( $this->layout ) {
							case 2:
								$this->fields_layout_2( $user_email, $phone, $first_name, $last_name );
								break;
							default:
								$this->fields_default( $user_login, $user_email );
						}

						$this->fields_submit();
					}
					?>
                </form>
            </div>
        </div>
		<?php
	}
}