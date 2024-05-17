<?php
/*
 * Name: Account
 * Description: Custom login page and user management.
 * Requires core: 6.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_extension_account' ) ) {
	function hocwp_theme_load_extension_account() {
		return apply_filters( 'hocwp_theme_load_extension_account', HT_extension()->is_active( __FILE__ ) );
	}
}

$load = hocwp_theme_load_extension_account();

if ( ! $load ) {
	return;
}

if ( ! class_exists( 'HOCWP_EXT_Account' ) ) {
	class HOCWP_EXT_Account extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public $user;
		public $facebook_account_kit = 'facebook_account_kit';
		public $activation_code_expire = DAY_IN_SECONDS;
		public $activation_code_sent_limit = 3;

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			add_action( 'init', array( $this, 'init_action' ) );

			$this->user = wp_get_current_user();

			add_shortcode( 'hte_account_saved_posts', array( $this, 'shortcode_saved_posts' ) );
			add_shortcode( 'hte_account_profile_settings', array( $this, 'shortcode_profile_settings' ) );
			add_shortcode( 'hte_account_register', array( $this, 'shortcode_register' ) );
			add_shortcode( 'hte_account_login', array( $this, 'shortcode_login' ) );
			add_shortcode( 'hte_account_lostpassword', array( $this, 'shortcode_lostpassword' ) );
			add_shortcode( 'hte_account', array( $this, 'shortcode_account' ) );

			require dirname( __FILE__ ) . '/account/account.php';

			if ( ! is_admin() ) {
				add_filter( 'edit_profile_url', array( $this, 'edit_profile_url_filter' ) );
				add_action( 'wp', array( $this, 'wp_action' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
				add_action( 'wp_footer', array( $this, 'wp_footer_action' ) );
				add_action( 'login_footer', array( $this, 'load_facebook_sdk' ) );
				add_filter( 'body_class', array( $this, 'body_classes' ) );
				add_filter( 'registration_errors', array( $this, 'registration_errors_filter' ) );
			}

			add_action( 'hocwp_theme_extension_account_user_added', array( $this, 'user_added_action' ) );

			$activity_logs = $this->get_option( 'activity_logs' );

			if ( $activity_logs ) {
				require_once $this->folder_path . '/activity-logs.php';
			}
		}

		public function user_added_action( $user_id ) {
			do_action( 'register_new_user', $user_id );
		}

		public function registration_errors_filter( $errors ) {
			$options = HT_Util()->get_theme_options( 'account' );
			$captcha = $options['captcha'] ?? '';

			if ( 1 == $captcha && HT_CAPTCHA()->check_config_valid() ) {
				$response = HT_CAPTCHA()->check_valid();

				$errors = HT_CAPTCHA()->control_captcha_errors( $response, $errors );
			}

			return $errors;
		}

		public function body_classes( $classes ) {
			$ac_page = $this->check_option_page_valid( 'login_page', true );

			if ( ! $ac_page ) {
				$ac_page = $this->check_option_page_valid( 'register_page', true );
			}

			if ( ! $ac_page ) {
				$ac_page = $this->check_option_page_valid( 'lostpassword_page', true );
			}

			if ( ! $ac_page ) {
				$ac_page = $this->check_option_page_valid( 'profile_page', true );
			}

			if ( $ac_page ) {
				$classes[] = 'account-page';
			}

			return $classes;
		}

		public function get_user_facebook_account_kit( $user_id = '' ) {
			if ( ! HT()->is_positive_number( $user_id ) && $this->user instanceof WP_User ) {
				$user_id = $this->user->ID;
			}

			return get_user_meta( $user_id, $this->facebook_account_kit, true );
		}

		public function update_user_facebook_account_kit( $value, $user_id = '' ) {
			if ( ! HT()->is_positive_number( $user_id ) && $this->user instanceof WP_User ) {
				$user_id = $this->user->ID;
			}

			return update_user_meta( $user_id, HTE_Account()->facebook_account_kit, $value );
		}

		public function compare_phone_numbers( $phone1, $phone2 ) {
			if ( $phone1 == $phone2 ) {
				return true;
			}

			$phone1 = str_replace( ' ', '', $phone1 );
			$phone2 = str_replace( ' ', '', $phone2 );

			$phone1 = trim( $phone1 );
			$phone2 = trim( $phone2 );

			$len1 = strlen( $phone1 );
			$len2 = strlen( $phone2 );

			if ( $len1 != $len2 ) {
				$max = max( $len1, $len2 );

				if ( $len1 == $max ) {
					$phone1 = ltrim( $phone1, '0' );
				} else {
					$phone2 = ltrim( $phone2, '0' );
				}
			}

			if ( $phone1 == $phone2 ) {
				return true;
			}

			return false;
		}

		public function wp_action() {
			global $pagenow;

			if ( ! $this->is_register_or_login_page() && 'wp-login.php' != $pagenow ) {
				if ( ! wp_doing_ajax() && ! wp_doing_cron() ) {
					$curl     = HT_Util()->get_current_url( true );
					$basename = basename( $curl );

					$info = pathinfo( $basename );

					if ( ! empty( $curl ) && ( ! isset( $info['extension'] ) || empty( $info['extension'] ) ) ) {
						$_SESSION['hocwp_theme_current_url'] = $curl;
					}
				}
			}
		}

		public function wp_footer_action() {
			if ( $this->is_register_or_login_page() ) {
				$this->load_facebook_sdk();
			}
		}

		public function get_redirect_to() {
			$redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

			if ( empty( $redirect_to ) ) {
				$redirect_to = isset( $_SESSION['hocwp_theme_current_url'] ) ? $_SESSION['hocwp_theme_current_url'] : '';
			}

			return apply_filters( 'hocwp_theme_extension_account_redirect_to', $redirect_to );
		}

		public function init_action() {
			global $pagenow, $wpdb;

			if ( 'wp-login.php' == $pagenow ) {
				$action = isset( $_GET['action'] ) ? $_GET['action'] : '';

				if ( 'logout' != $action && 'rp' != $action || ( 'rp' == $action && ( ! isset( $_GET['key'] ) || empty( $_GET['key'] ) ) ) ) {
					if ( 'lostpassword' == $action ) {
						$page = HT_Util()->get_theme_option_page( 'lostpassword_page', 'account' );
					} elseif ( 'register' == $action ) {
						$page = HT_Util()->get_theme_option_page( 'register_page', 'account' );
					} else {
						$page = HT_Util()->get_theme_option_page( 'login_page', 'account' );
					}

					if ( HTE_Account()->check_option_page_valid( $page ) && 'rp' != $action && 'resetpass' != $action && 'logout' != $action ) {
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

			if ( ! is_admin() && isset( $_POST['action'] ) && 'user_login' == $_POST['action'] ) {
				$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';

				if ( wp_verify_nonce( $nonce ) ) {
					$login = isset( $_POST['user_login'] ) ? $_POST['user_login'] : '';
					$pass  = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';
					$rem   = isset( $_POST['rememberme'] ) ? $_POST['rememberme'] : '';

					$credentials = array(
						'user_login'    => $login,
						'user_password' => $pass,
						'remember'      => $rem
					);

					$result = wp_signon( $credentials );

					if ( $result instanceof WP_User ) {
						$url = get_edit_profile_url();

						$redirect_to = HTE_Account()->get_redirect_to();

						if ( ! empty( $redirect_to ) ) {
							$url = $redirect_to;
						}

						wp_redirect( $url );
						exit;
					} else {
						$_POST['error'] = $result->get_error_messages();
					}
				}
			}

			$action = isset( $_GET['action'] ) ? $_GET['action'] : '';

			if ( is_user_logged_in() ) {
				// Check email address vierified
				$must_verify_email = HT_Options()->get_tab( 'must_verify_email', '', 'account' );

				$red = true;

				if ( 1 == $must_verify_email && ! HOCWP_THEME_DOING_AJAX && ! HOCWP_THEME_DOING_CRON ) {
					if ( $this->user instanceof WP_User ) {
						// Skip all administrator users
						if ( ! in_array( 'administrator', $this->user->roles ) ) {
							$is_activated = get_user_meta( $this->user->ID, 'is_activated', true );

							if ( 1 != $is_activated ) {
								$red = false;

								if ( 'verify_email' == $action ) {
									$user_id = HT()->get_method_value( 'user_id', 'get' );
									$key     = HT()->get_method_value( 'key', 'get' );

									$check = $this->check_user_activation_key( $key, $user_id );

									// Check activation code valid
									if ( ! is_wp_error( $check ) && HT()->is_positive_number( $check ) && $check == $user_id ) {
										update_user_meta( $user_id, 'is_activated', 1 );
										$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'ID' => $user_id ) );
										delete_user_meta( $user_id, 'activation_code_sent_count' );
										$msg = sprintf( __( 'Your email address has been verified successfully. To change profile, update it <a href="%s">here</a> or <a href="%s">visit our homepage</a> to see posts.', 'sb-core' ), get_edit_profile_url(), home_url() );
										wp_die( $msg, __( 'Email Address Verified', 'sb-core' ) );
									} else {
										$msg = __( 'Invalid key.', 'sb-core' );

										if ( $check instanceof WP_Error ) {
											$msg = $check->get_error_message();
										}

										$msg .= ' ' . sprintf( __( 'If you have not received email yet, try to resend <a href="%s">here</a>.', 'sb-core' ), home_url( '/?action=resend_activation_code' ) );

										wp_die( $msg, __( 'Verify Email Error', 'sb-core' ) );
									}
								} else {
									$tr_name = 'hocwp_theme_verify_user_notification_sent_' . $this->user->user_email;

									if ( false === get_transient( $tr_name ) || 'resend_activation_code' == $action ) {
										$count = 0;

										if ( 'resend_activation_code' == $action ) {
											$count = get_user_meta( $this->user->ID, 'activation_code_sent_count', true );
										}

										$count = absint( $count );

										if ( $count >= $this->activation_code_sent_limit ) {
											wp_die( __( 'Your account has reached the confirmation email limit, please try again later. You can also contact administrator for supports.', 'sb-core' ), __( 'Email Sent Limit', 'sb-core' ) );
										}

										$key = wp_generate_password( 20, false );
										HT_Util()->get_user_activation_key( $key, $this->user );
										$sent = hocwp_theme_verify_user_notification( $key, $this->user );

										if ( $sent ) {
											set_transient( $tr_name, $sent, $this->activation_code_expire );
											$count ++;
											update_user_meta( $this->user->ID, 'activation_code_sent_count', $count );

											$msg = sprintf( __( 'Email verification has been sent, please check your inbox, if you cannot see email, try to check spam box too. Your confirmation email limit <strong>%d</strong>.', 'sb-core' ), ( $this->activation_code_sent_limit - $count ) );

											wp_die( $msg, __( 'Email Sent Limit', 'sb-core' ) );
										}
									}

									if ( 'profile.php' != $pagenow ) {
										$cur_url = HT_Util()->get_current_url();
										$pro_url = get_edit_profile_url();

										if ( $cur_url != $pro_url ) {
											$msg = sprintf( __( 'You need to verify your email address. Please check your inbox for verify email, also see your spam box too. To change your email address, update it <a href="%s">here</a>.', 'sb-core' ), $pro_url );
											wp_die( $msg, __( 'Verify Email Address', 'sb-core' ) );
										}
									}

									add_action( 'admin_notices', array( $this, 'admin_notice_verify_email_address' ) );
								}
							}
						}
					}
				}

				// Check phone number verified
				$must_verify_phone = HT_Options()->get_tab( 'must_verify_phone', '', 'account' );

				if ( $red && 1 == $must_verify_phone && ! HOCWP_THEME_DOING_AJAX && ! HOCWP_THEME_DOING_CRON ) {
					if ( $this->user instanceof WP_User && ! in_array( 'administrator', $this->user->roles ) ) {
						$fac   = HTE_Account()->get_user_facebook_account_kit();
						$phone = get_user_meta( $this->user->ID, 'phone', true );

						if ( ! is_array( $fac ) || ! isset( $fac['phone'] ) || ! HTE_Account()->compare_phone_numbers( $phone, $fac['phone'] ) ) {
							if ( 'profile.php' != $pagenow ) {
								$cur_url = HT_Util()->get_current_url();
								$pro_url = get_edit_profile_url();

								if ( $cur_url != $pro_url ) {
									wp_redirect( $pro_url );
									exit;
								}
							}

							add_action( 'admin_notices', array( $this, 'admin_notice_verify_phone_number' ) );
						}
					}
				}
			} else {
				if ( 'verify_email' == $action ) {
					$user_id = HT()->get_method_value( 'user_id', 'get' );
					$key     = HT()->get_method_value( 'key', 'get' );

					if ( ! empty( $key ) ) {
						$data = get_userdata( $user_id );

						if ( $data instanceof WP_User ) {
							wp_set_auth_cookie( $user_id, true );
							wp_redirect( HT_Util()->get_current_url( true ) );
							exit;
						}
					}
				}
			}
		}

		public function admin_notice_verify_email_address() {
			if ( function_exists( 'HT_Admin' ) && method_exists( HT_Admin(), 'skip_admin_notices' ) && HT_Admin()->skip_admin_notices() ) {
				return;
			}

			$args = array(
				'message' => sprintf( __( '<strong>Verify email address:</strong> You must provide and verify your email address before viewing site. <a href="%s">Click here</a> to update it.', 'sb-core' ), get_edit_profile_url() ),
				'type'    => 'warning'
			);

			HT_Util()->admin_notice( $args );
		}

		public function admin_notice_verify_phone_number() {
			if ( function_exists( 'HT_Admin' ) && method_exists( HT_Admin(), 'skip_admin_notices' ) && HT_Admin()->skip_admin_notices() ) {
				return;
			}

			$args = array(
				'message' => sprintf( __( '<strong>Verify phone number:</strong> You must provide and verify your phone number before viewing site. <a href="%s">Click here</a> to update it.', 'sb-core' ), get_edit_profile_url() ),
				'type'    => 'warning'
			);

			HT_Util()->admin_notice( $args );
		}

		public function check_user_activation_key( $key, $user_id, $activation_key = '' ) {
			global $wpdb, $wp_hasher;

			$key = preg_replace( '/[^a-z0-9]/i', '', $key );

			if ( empty( $key ) || ! is_string( $key ) ) {
				return new WP_Error( 'invalid_key', __( 'Invalid key.', 'sb-core' ) );
			}

			if ( ! HT()->is_positive_number( $user_id ) ) {
				return new WP_Error( 'invalid_key', __( 'Invalid key.', 'sb-core' ) );
			}


			if ( empty( $activation_key ) ) {
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT ID, user_activation_key FROM $wpdb->users WHERE ID = %s", $user_id ) );

				if ( ! $row ) {
					return new WP_Error( 'invalid_key', __( 'Invalid key.', 'sb-core' ) );
				}

				$activation_key = $row->user_activation_key;
			}

			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . WPINC . '/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}

			$expiration_duration = apply_filters( 'hocwp_theme_extension_account_activation_key_expiration', $this->activation_code_expire );

			if ( false !== strpos( $activation_key, ':' ) ) {
				list( $code_request_time, $code_key ) = explode( ':', $activation_key, 2 );
				$expiration_time = $code_request_time + $expiration_duration;
			} else {
				$code_key = $activation_key;

				$expiration_time = false;
			}

			if ( ! $code_key ) {
				return new WP_Error( 'invalid_key', __( 'Invalid key.', 'sb-core' ) );
			}

			$hash_is_correct = $wp_hasher->CheckPassword( $key, $code_key );

			if ( $hash_is_correct && $expiration_time && time() < $expiration_time ) {
				return $user_id;
			} elseif ( $hash_is_correct && $expiration_time ) {
				// Key has an expiration time that's passed
				return new WP_Error( 'expired_key', __( 'Invalid key.', 'sb-core' ) );
			}

			if ( hash_equals( $code_key, $key ) || ( $hash_is_correct && ! $expiration_time ) ) {
				$return = new WP_Error( 'expired_key', __( 'Invalid key.', 'sb-core' ) );

				return apply_filters( 'hocwp_theme_extension_account_activation_key_expired', $return, $user_id );
			}

			return new WP_Error( 'invalid_key', __( 'Invalid key.', 'sb-core' ) );
		}

		public function shortcode_lostpassword( $atts = array(), $content = null ) {
			$atts = shortcode_atts( array(), $atts );

			ob_start();
			include $this->folder_path . '/page-lostpassword.php';

			return ob_get_clean();
		}

		public function shortcode_account( $atts = array() ) {
			$atts = shortcode_atts( array(), $atts );

			ob_start();
			include $this->folder_path . '/page-account.php';

			return ob_get_clean();
		}

		public function shortcode_login( $atts = array(), $content = null ) {
			$atts = shortcode_atts( array(), $atts );

			ob_start();
			include $this->folder_path . '/page-login.php';

			return ob_get_clean();
		}

		public function is_register_or_login_page() {
			$page  = HT_Util()->get_theme_option_page( 'register_page', 'account' );
			$login = HT_Util()->get_theme_option_page( 'login_page', 'account' );

			if ( HTE_Account()->check_option_page_valid( $page, true ) || HTE_Account()->check_option_page_valid( $login, true ) ) {
				return true;
			}

			return false;
		}

		/*
		 * Load styles and scripts on front-end.
		 */
		public function enqueue_scripts() {
			if ( $this->is_register_or_login_page() ) {
				$this->load_connected_socials_script();
				$this->load_facebook_account_kit_script();

				if ( ! wp_style_is( 'font-awesome-style' ) && ! wp_style_is( 'fontawesome-style' ) ) {
					wp_enqueue_style( 'dashicons' );
				}
			}

			if ( $this->check_option_page_valid( 'profile_page', true ) ) {
				wp_enqueue_style( 'hte-account-style', SB_Core()->url . '/css/account.css' );
				wp_enqueue_script( 'hte-account', SB_Core()->url . '/js/account.js', array( 'jquery' ), false, true );
				$this->load_facebook_account_kit_script();
			}
		}

		public function connected_socials_enabled() {
			$options = HT_Util()->get_theme_options( 'account' );

			$cs = isset( $options['connect_social'] ) ? $options['connect_social'] : '';

			return ( 1 == $cs );
		}

		public function facebook_account_kit_enabled() {
			$options = HT_Util()->get_theme_options( 'account' );

			$value = isset( $options['account_kit'] ) ? $options['account_kit'] : '';

			return ( 1 == $value );
		}

		public function load_facebook_sdk() {
			if ( $this->connected_socials_enabled() ) {
				HT_Util()->load_facebook_javascript_sdk( array( 'load' => true ) );
			}
		}

		public function load_connected_socials_script() {
			if ( $this->connected_socials_enabled() ) {
				wp_enqueue_script( 'hocwp-theme' );

				wp_enqueue_script( 'hocwp-ext-connected-accounts' );
			}
		}

		public function load_facebook_account_kit_script() {
			if ( $this->facebook_account_kit_enabled() ) {
				wp_enqueue_script( 'hocwp-theme' );
				HT_Enqueue()->ajax_overlay();

				wp_enqueue_script( 'hocwp-ext-facebook-account-kit' );
			}
		}

		public function shortcode_register( $atts = array(), $content = null ) {
			$atts = shortcode_atts( array(), $atts );

			ob_start();
			include $this->folder_path . '/page-register.php';

			return ob_get_clean();
		}

		public function shortcode_profile_settings( $atts = array(), $content = null ) {
			$atts = shortcode_atts( array(), $atts );

			ob_start();
			include $this->folder_path . '/page-edit-profile.php';

			return ob_get_clean();
		}

		public function edit_profile_url_filter( $url ) {
			$page = HT_Util()->get_theme_option_page( 'profile_page', 'account' );

			if ( $this->check_option_page_valid( $page ) ) {
				$url = get_permalink( $page );
			}

			return $url;
		}

		/**
		 * Shortcode for displaying user's favorite posts.
		 *
		 * @param array $atts
		 * @param null $content
		 *
		 * @return mixed|string|void
		 */
		public function shortcode_saved_posts( $atts = array(), $content = null ) {
			$atts = shortcode_atts( array(
				'posts_per_page' => HT_Util()->get_posts_per_page()
			), $atts );

			if ( ! is_user_logged_in() ) {
				return '<a class="btn btn-success" href="' . wp_login_url( get_permalink() ) . '">' . __( 'Login', 'sb-core' ) . '</a>';
			}

			$html = apply_filters( 'hocwp_theme_extension_account_saved_posts_html', '', $atts, $this );

			if ( ! empty( $html ) ) {
				return $html;
			}

			$posts = $this->get_saved_posts();

			$no_posts_text = wpautop( __( 'No posts found.', 'sb-core' ) );

			if ( empty( $posts ) ) {
				return $no_posts_text;
			}

			$ppp = $atts['posts_per_page'];

			$args = array(
				'posts_per_page' => $ppp,
				'post__in'       => $posts,
				'paged'          => HT_Frontend()->get_paged()
			);

			$query = new WP_Query( $args );

			if ( ! $query->have_posts() ) {
				return $no_posts_text;
			}

			$html = apply_filters( 'hocwp_theme_extension_account_saved_posts_loop', '', $query, $atts, $this );

			if ( ! empty( $html ) ) {
				return $html;
			}

			ob_start();
			do_action( 'hocwp_theme_loop', $query );

			return ob_get_clean();
		}

		public function get_saved_posts( $user_id = null ) {
			$posts = $this->get_user_meta_posts( 'saved_posts', $user_id );

			if ( empty( $posts ) ) {
				$posts = $this->get_favorite_posts( $user_id );
			}

			return $posts;
		}

		public function get_favorite_posts( $user_id = null ) {
			return $this->get_user_meta_posts( 'favorite_posts', $user_id );
		}

		public function get_user_meta_posts( $meta_key, $user_id = null ) {
			$user_id = HT_Util()->return_user( $user_id, 'id' );

			$posts = get_user_meta( $user_id, $meta_key, true );

			return $posts;
		}

		public function check_option_page_valid( $page, $check_current_page = false ) {
			if ( is_string( $page ) ) {
				$page = HT_Util()->get_theme_option_page( $page, 'account' );
			}

			return HT_Options()->check_page_valid( $page, $check_current_page );
		}

		public function check_recaptcha_config_valid() {
			return HT_CAPTCHA()->check_recaptcha_config_valid();
		}

		public function check_captcha_config_valid() {
			return HT_CAPTCHA()->check_config_valid();
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Account()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Account() {
	return HOCWP_EXT_Account::get_instance();
}