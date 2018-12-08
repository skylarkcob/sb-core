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
		}

		public function registration_errors_filter( $errors ) {
			$options = HT_Util()->get_theme_options( 'account' );
			$captcha = isset( $options['captcha'] ) ? $options['captcha'] : '';

			if ( 1 == $captcha && HTE_Account()->check_recaptcha_config_valid() ) {
				$response = HT_Util()->recaptcha_valid();

				if ( ! $response ) {
					if ( ! is_wp_error( $errors ) || ! ( $errors instanceof WP_Error ) ) {
						$errors = new WP_Error( 'invalid_captcha', __( '<strong>Error:</strong> Please correct the captcha.', 'sb-core' ) );
					} else {
						$errors->add( 'invalid_captcha', __( '<strong>Error:</strong> Please correct the captcha.', 'sb-core' ) );
					}
				}
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
			global $pagenow;

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
		}

		public function shortcode_lostpassword( $atts = array(), $content = null ) {
			$atts = shortcode_atts( array(), $atts );

			ob_start();
			include $this->folder_path . '/page-lostpassword.php';

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

		public function enqueue_scripts() {
			if ( $this->is_register_or_login_page() ) {
				$this->load_connected_socials_script();

				if ( ! wp_style_is( 'font-awesome-style' ) && ! wp_style_is( 'fontawesome-style' ) ) {
					wp_enqueue_style( 'dashicons' );
				}
			}

			if ( $this->check_option_page_valid( 'profile_page', true ) ) {
				wp_enqueue_style( 'hte-account-style', SB_Core()->url . '/css/account.css' );
				wp_enqueue_script( 'hte-account', SB_Core()->url . '/js/account.js', array( 'jquery' ), false, true );
			}
		}

		public function connected_socials_enabled() {
			$options = HT_Util()->get_theme_options( 'account' );

			$cs = isset( $options['connect_social'] ) ? $options['connect_social'] : '';

			return ( 1 == $cs );
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
			$options    = HT_Util()->get_theme_options( 'social' );
			$site_key   = isset( $options['recaptcha_site_key'] ) ? $options['recaptcha_site_key'] : '';
			$secret_key = isset( $options['recaptcha_secret_key'] ) ? $options['recaptcha_secret_key'] : '';

			return ( ! empty( $site_key ) && ! empty( $secret_key ) );
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