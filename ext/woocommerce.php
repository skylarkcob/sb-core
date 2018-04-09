<?php
/*
 * Name: WooCommerce
 * Description: Add more functionality for your shop site which runs base on WooCommerce Plugin.
 */
function hocwp_theme_woocommerce_activated() {
	return class_exists( 'WC_Product' );
}

function hocwp_theme_load_extension_woocommerce() {
	$load = apply_filters( 'hocwp_theme_load_extension_woocommerce', hocwp_theme_is_extension_active( __FILE__ ) );

	return $load;
}

$load = hocwp_theme_load_extension_woocommerce();

if ( ! $load || ! hocwp_theme_woocommerce_activated() ) {
	return;
}

function hocwp_theme_woocommerce_support() {
	add_theme_support( 'woocommerce' );
}

add_action( 'after_setup_theme', 'hocwp_theme_woocommerce_support' );

function hocwp_theme_wc_pre_get_posts( WP_Query $query ) {
	if ( $query instanceof WP_Query ) {
		if ( $query->is_main_query() ) {
			if ( is_post_type_archive( 'product' ) || is_tax() || is_search() ) {
				if ( is_woocommerce() ) {
					$ppp = hocwp_theme_get_option( 'products_per_page', $GLOBALS['hocwp_theme']->defaults['posts_per_page'], 'reading' );
					$query->set( 'posts_per_page', $ppp );
					$query->set( 'post_type', 'product' );
				}
			}
		}
	}
}

if ( ! is_admin() ) {
	add_action( 'pre_get_posts', 'hocwp_theme_wc_pre_get_posts' );
}

function hocwp_theme_wc_enqueue_scripts() {
	wp_enqueue_style( 'hocwp-theme-woocommerce-style', HOCWP_EXT_URL . '/css/woocommerce' . HOCWP_THEME_CSS_SUFFIX );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_wc_enqueue_scripts' );

function hocwp_theme_wc_product_data_filter( $value, $data ) {
	if ( empty( $value ) ) {
		$value = 0;
	}

	return $value;
}

add_filter( 'woocommerce_product_get_price', 'hocwp_theme_wc_product_data_filter', 10, 2 );

$locale = get_locale();
if ( 'en_US' != $locale && 'en' != $locale ) {
	load_template( HOCWP_EXT_PATH . '/ext/woocommerce/woocommerce-translation.php' );
}