<?php
if ( HOCWP_THEME_DEVELOPING ) {
	require HOCWP_EXT_PATH . '/inc/functions-development.php';
}

require HOCWP_EXT_PATH . '/inc/functions.php';

hocwp_load_all_extensions( HOCWP_EXT_PATH );