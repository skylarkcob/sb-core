<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

error_reporting( 0 );
header( 'Content-Type: application/json' );
$load = hocwp_theme_load_extension_jwplayer();
$rs   = array();
if ( ! $load ) {
	$rs['status'] = 0;
	$rs['why']    = __( 'JW Player extension not loaded!', 'sb-core' );
	echo wp_json_encode( $rs );

	return;
}
$url = isset( $_REQUEST['url'] ) ? $_REQUEST['url'] : '';
if ( empty( $url ) ) {
	$rs['status'] = 0;
	$rs['why']    = __( 'Link not valid', 'sb-core' );
	echo wp_json_encode( $rs );

	return;
}
$domain   = HT()->get_domain_name( $url, true );
$username = isset( $_REQUEST['username'] ) ? $_REQUEST['username'] : '';
$password = isset( $_REQUEST['password'] ) ? $_REQUEST['password'] : '';
switch ( $domain ) {
	case 'streamcherry.com':
		$streaming = new HOCWP_Theme_Streaming_Streamcherry( $url, $username, $password );
		echo $streaming->get_data();
		break;
	case 'streamango.com':
		$streaming = new HOCWP_Theme_Streaming_Streamango( $url, $username, $password );
		echo $streaming->get_data();
		break;
}