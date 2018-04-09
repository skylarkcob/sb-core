<?php
/*
 * Name: Account
 * Description: Custom login page and user management.
 */
function hocwp_theme_load_extension_account() {
	return apply_filters( 'hocwp_theme_load_extension_account', hocwp_theme_is_extension_active( __FILE__ ) );
}

$load = hocwp_theme_load_extension_account();

if ( ! $load ) {
	return;
}

require dirname( __FILE__ ) . '/account/account.php';