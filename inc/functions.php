<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_custom_post_types_registration() {
	return apply_filters( 'hocwp_theme_custom_post_types', array() );
}

function hocwp_ext_custom_taxonomies_registration() {
	return apply_filters( 'hocwp_theme_custom_taxonomies', array() );
}