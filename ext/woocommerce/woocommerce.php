<?php
define( 'HOCWP_EXT_WC_PATH', dirname( __FILE__ ) );
define( 'HOCWP_EXT_WC_URL', HOCWP_EXT_URL . '/ext/woocommerce' );

require HOCWP_EXT_WC_PATH . '/functions.php';

function hocwp_theme_woocommerce_support() {
	add_theme_support( 'woocommerce' );
}

add_action( 'after_setup_theme', 'hocwp_theme_woocommerce_support' );

function hocwp_theme_wc_product_data_filter( $value, $data ) {
	if ( empty( $value ) ) {
		$value = 0;
	}

	return $value;
}

add_filter( 'woocommerce_product_get_price', 'hocwp_theme_wc_product_data_filter', 10, 2 );

function hocwp_ext_wc_woocommerce_paypal_supported_currencies_filter( $currencies ) {
	$currencies[] = 'VND';

	return $currencies;
}

add_filter( 'woocommerce_paypal_supported_currencies', 'hocwp_ext_wc_woocommerce_paypal_supported_currencies_filter' );

if ( version_compare( wc()->version, '3.3.3', '<' ) ) {
	$locale = get_locale();

	if ( 'en_US' != $locale && 'en' != $locale && 'vi' == $locale ) {
		load_template( HOCWP_EXT_PATH . '/ext/woocommerce/woocommerce-translation.php' );
	}
}

if ( is_admin() ) {
	require HOCWP_EXT_WC_PATH . '/admin.php';
} else {
	require HOCWP_EXT_WC_PATH . '/front-end.php';
}

function vnd_to_usd( $paypal_args ) {
	if ( isset( $paypal_args['currency_code'] ) && 'VND' == $paypal_args['currency_code'] ) {
		$convert_rate = hocwp_ext_wc_usd_to_vnd_rate();

		$paypal_args['currency_code'] = 'USD';

		$i = 1;

		while ( isset( $paypal_args[ 'amount_' . $i ] ) ) {
			$paypal_args[ 'amount_' . $i ] = round( $paypal_args[ 'amount_' . $i ] / $convert_rate, 2 );
			++ $i;
		}

	}

	return $paypal_args;
}

add_filter( 'woocommerce_paypal_args', 'vnd_to_usd' );