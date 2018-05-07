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

require dirname( __FILE__ ) . '/woocommerce/woocommerce.php';