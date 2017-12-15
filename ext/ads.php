<?php
/*
 * Name: Ads
 * Description: Create and display ads on your site.
 */
function hocwp_theme_load_extension_ads() {
	return apply_filters( 'hocwp_theme_load_extension_ads', hocwp_theme_is_extension_active( __FILE__ ) );
}

$load = hocwp_theme_load_extension_ads();
if ( ! $load ) {
	return;
}

require dirname( __FILE__ ) . '/ads/ads.php';