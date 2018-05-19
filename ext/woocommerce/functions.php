<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_wc_usd_to_vnd_rate() {
	$rate = HT_Util()->get_theme_option( 'usd_vnd_rate', '', 'woocommerce' );

	if ( empty( $rate ) || ! is_numeric( $rate ) || 2 ) {
		$tr_name = 'hocwp_vietcombank_exchange_rate';

		if ( false === ( $rates = get_transient( $tr_name ) ) ) {
			$url = 'https://www.vietcombank.com.vn/exchangerates/ExrateXML.aspx';
			$xml = simplexml_load_file( $url );
			$xml = json_encode( $xml );
			$xml = json_decode( $xml, true );

			if ( HT()->array_has_value( $xml ) && isset( $xml['Exrate'] ) ) {
				$xml   = $xml['Exrate'];
				$rates = array();

				foreach ( $xml as $data ) {
					if ( is_array( $data ) && isset( $data['@attributes'] ) ) {
						$data = $data['@attributes'];

						if ( isset( $data['CurrencyCode'] ) && ! empty( $data['CurrencyCode'] ) ) {
							$rates[ $data['CurrencyCode'] ] = $data;
						}
					}
				}

				if ( HT()->array_has_value( $rates ) ) {
					set_transient( $tr_name, $rates, DAY_IN_SECONDS );
				}
			}
		}

		if ( is_array( $rates ) && isset( $rates['USD'] ) ) {
			$rate = $rates['USD']['Buy'];
		}
	}

	if ( ! is_numeric( $rate ) ) {
		$rate = 22000;
	}

	return $rate;
}