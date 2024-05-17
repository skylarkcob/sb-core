<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * Load all styles and scripts on default wp-login.php page.
 */
function hocwp_ext_account_default_login_page_scripts() {
	wp_enqueue_style( 'hocwp-ext-account-login-style', HOCWP_EXT_URL . '/css/login' . HOCWP_THEME_CSS_SUFFIX );

	$options = HT_Util()->get_theme_options( 'account' );

	HTE_Account()->load_connected_socials_script();
	HTE_Account()->load_facebook_account_kit_script();

	$cs = $options['custom_style'] ?? '';

	if ( 1 == $cs ) {
		wp_enqueue_style( 'hocwp-ext-account-login-default-style', HOCWP_EXT_URL . '/css/login-default' . HOCWP_THEME_CSS_SUFFIX );
	}

	$src = HOCWP_EXT_URL . '/js/login-default' . HOCWP_THEME_JS_SUFFIX;
	wp_register_script( 'hocwp-ext-account-login-default', $src, array(), false, true );

	$l10n = array(
		'logo' => ''
	);

	$logo = HT_Util()->get_theme_option( 'login_logo', '', 'account' );

	if ( HT()->is_positive_number( $logo ) ) {
		$tag = new HOCWP_Theme_HTML_Tag( 'img' );
		$tag->add_attribute( 'src', wp_get_attachment_url( $logo ) );
		$tag->add_attribute( 'alt', get_bloginfo( 'name', 'display' ) );
		$l10n['logo'] = $tag->build();
	}

	wp_localize_script( 'hocwp-ext-account-login-default', 'hocwpLogin', $l10n );

	wp_enqueue_script( 'hocwp-ext-account-login-default' );

	unset( $options, $cs );
}

add_action( 'login_enqueue_scripts', 'hocwp_ext_account_default_login_page_scripts' );

function hocwp_ext_account_the_privacy_policy_link_filter( $link, $url ) {
	$options = HT_Util()->get_theme_options( 'account' );

	$cs = $options['custom_style'] ?? '';

	if ( 1 != $cs ) {
		return $link;
	}

	$params = $_SERVER['QUERY_STRING'] ?? '';
	parse_str( $params, $params );

	$locale  = $_GET['locale'] ?? get_locale();
	$locales = get_available_languages();

	ob_start();
	?>
    <div class="clearfix">
        <div class="footer-left">
			<?php
			$locations = get_nav_menu_locations();

			if ( isset( $locations['login'] ) ) {
				$items = wp_get_nav_menu_items( $locations['login'] );

				if ( HT()->array_has_value( $items ) ) {
					foreach ( $items as $item ) {
						if ( ! empty( $item->title ) ) {
							printf( '<a class="menu-link" href="%s">%s</a>', esc_url( $item->url ), $item->title );
						}
					}
				}
			}

			if ( ! empty( $url ) ) {
				echo sprintf( '<a class="privacy-policy-link menu-link" href="%s">%s</a>', esc_url( $url ), __( 'Privacy Policy', 'sb-core' ) );
			}
			?>
        </div>
        <div class="footer-right">
            <div class="clearfix">
                <div class="language-switcher">
                    <form id="lang-switcher" action="" method="GET">
						<?php
						$params = (array) $params;

						if ( HT()->array_has_value( $params ) ) {
							foreach ( $params as $name => $value ) {
								if ( ! empty( $value ) && ! empty( $name ) && 'locale' != $name ) {
									?>
                                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>"
                                           value="<?php echo esc_attr( $value ); ?>">
									<?php
								}
							}
						}
						?>
                        <label for="language-switcher-locales">
                            <span aria-hidden="true" class="dashicons dashicons-translation"></span>
                            <span class="screen-reader-text"><?php _e( 'Select the language:', 'sb-core' ); ?></span>
                        </label>
						<?php
						$args = array(
							'selected'                    => $locale,
							'show_available_translations' => false,
							'languages'                   => $locales
						);

						wp_dropdown_languages( $args );
						?>
                    </form>
                    <script>
                        let languageForm = document.getElementById("language-switcher"),
                            locale = document.getElementById("locale");

                        locale.onchange = function () {
                            languageForm.submit();
                        };
                    </script>
                </div>
            </div>
        </div>
    </div>
	<?php
	return ob_get_clean();
}

add_filter( 'the_privacy_policy_link', 'hocwp_ext_account_the_privacy_policy_link_filter', 10, 2 );

function hocwp_ext_account_login_init_action() {
	$locale = $_GET['locale'] ?? get_locale();

	if ( empty( $locale ) ) {
		$locale = 'en_US';
	}

	switch_to_locale( $locale );
}

add_action( 'login_init', 'hocwp_ext_account_login_init_action' );

function hocwp_ext_account_locale_filter( $locale ) {
	if ( 'en' == $locale ) {
		$locale = 'en_US';
	}

	return $locale;
}

add_filter( 'locale', 'hocwp_ext_account_locale_filter' );