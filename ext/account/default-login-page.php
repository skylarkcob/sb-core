<?php
function hocwp_ext_account_default_login_page_scripts() {
	global $pagenow;

	wp_enqueue_style( 'hocwp-ext-account-login-style', HOCWP_EXT_URL . '/css/login' . HOCWP_THEME_CSS_SUFFIX );

	$options = HT_Util()->get_theme_options( 'account' );

	$cs = isset( $options['connect_social'] ) ? $options['connect_social'] : '';

	if ( 1 == $cs ) {
		wp_enqueue_script( 'hocwp-theme' );

		$args = array(
			'load' => true
		);

		HT_Util()->load_facebook_javascript_sdk( $args );
		wp_enqueue_script( 'hocwp-ext-connected-accounts' );
	}

	$cs = isset( $options['custom_style'] ) ? $options['custom_style'] : '';

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