<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( HOCWP_THEME_DEVELOPING ) {
	require HOCWP_EXT_PATH . '/inc/admin-setting-page-development.php';
}