<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_ads_display( $args, $random = false ) {
	if ( ! is_object( $args ) ) {
		if ( ! is_array( $args ) ) {
			$args = array(
				'position' => $args
			);
		}

		if ( $random ) {
			$args['random'] = $random;
		}
	}

	HTE_Ads()->display( $args );
}