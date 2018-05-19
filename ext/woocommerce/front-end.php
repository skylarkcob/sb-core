<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_wc_pre_get_posts( WP_Query $query ) {
	if ( $query instanceof WP_Query ) {
		if ( $query->is_main_query() ) {
			if ( is_post_type_archive( 'product' ) || is_tax() || is_search() ) {
				if ( is_woocommerce() ) {
					$ppp = hocwp_theme_get_option( 'products_per_page', $GLOBALS['hocwp_theme']->defaults['posts_per_page'], 'reading' );
					$query->set( 'posts_per_page', $ppp );
					$query->set( 'post_type', 'product' );
				}
			}
		}
	}
}

if ( ! is_admin() ) {
	add_action( 'pre_get_posts', 'hocwp_theme_wc_pre_get_posts' );
}

function hocwp_theme_wc_enqueue_scripts() {
	wp_enqueue_style( 'hocwp-theme-woocommerce-style', HOCWP_EXT_URL . '/css/woocommerce' . HOCWP_THEME_CSS_SUFFIX );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_wc_enqueue_scripts' );