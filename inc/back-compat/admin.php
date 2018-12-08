<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( HOCWP_THEME_DEVELOPING && function_exists( 'hocwp_theme_settings_page_development_tab' ) ) {
	require HOCWP_EXT_PATH . '/inc/admin-setting-page-development.php';
}