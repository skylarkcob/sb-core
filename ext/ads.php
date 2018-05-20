<?php
/*
 * Name: Ads
 * Description: Create and display ads on your site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_load_extension_ads() {
	return apply_filters( 'hocwp_theme_load_extension_ads', HT_extension()->is_active( __FILE__ ) );
}

$load = hocwp_theme_load_extension_ads();

if ( ! $load ) {
	return;
}

require dirname( __FILE__ ) . '/ads/ads.php';