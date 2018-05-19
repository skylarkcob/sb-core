<?php
/*
 * Name: WooCommerce
 * Description: Add more functionality for your shop site which runs base on WooCommerce Plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_woocommerce_activated() {
	return class_exists( 'WC_Product' );
}

function hocwp_theme_load_extension_woocommerce() {
	$load = apply_filters( 'hocwp_theme_load_extension_woocommerce', hocwp_theme_is_extension_active( __FILE__ ) );

	return $load;
}

$load = hocwp_theme_load_extension_woocommerce();

function hocwp_ext_wc_require_plugins( $plugins ) {
	if ( ! in_array( 'woocommerce', $plugins ) ) {
		$plugins[] = 'woocommerce';
	}

	return $plugins;
}

add_filter( 'hocwp_theme_required_plugins', 'hocwp_ext_wc_require_plugins' );

if ( ! $load || ! hocwp_theme_woocommerce_activated() ) {
	return;
}

require dirname( __FILE__ ) . '/woocommerce/woocommerce.php';