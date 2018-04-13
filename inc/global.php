<?php
if ( HOCWP_THEME_DEVELOPING ) {
	require HOCWP_EXT_PATH . '/inc/functions-development.php';
}

require HOCWP_EXT_PATH . '/inc/functions.php';

if ( function_exists( 'hocwp_load_all_extensions' ) ) {
	hocwp_load_all_extensions( HOCWP_EXT_PATH );
}