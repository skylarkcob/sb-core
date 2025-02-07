<?php
/*
 * Name: Recent Activity Posts
 * Description: Tracking recent activity posts on your site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$load = apply_filters( 'hocwp_theme_load_extension_recent_activity_post', ht_extension()->is_active( __FILE__ ) );

if ( ! $load ) {
	return;
}

function hocwp_recent_activity_save_post_action( $post_id, $post ) {
	$date   = strtotime( $post->post_date_gmt );
	$update = strtotime( $post->post_modified_gmt );
	$lad    = get_post_meta( $post_id, 'last_activity', true );

	if ( $update > $date ) {
		$date = $update;
	}

	if ( ! is_numeric( $lad ) || $date > $lad ) {
		if ( ! is_numeric( $date ) ) {
			$date = strtotime( date( 'Y-m-d H:i:s' ) );
		}

		update_post_meta( $post_id, 'last_activity', $date );
	}
}

add_action( 'save_post', 'hocwp_recent_activity_save_post_action', 10, 2 );