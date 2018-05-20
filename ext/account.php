<?php
/*
 * Name: Account
 * Description: Custom login page and user management.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_load_extension_account() {
	return apply_filters( 'hocwp_theme_load_extension_account', HT_extension()->is_active( __FILE__ ) );
}

$load = hocwp_theme_load_extension_account();

if ( ! $load ) {
	return;
}

require dirname( __FILE__ ) . '/account/account.php';