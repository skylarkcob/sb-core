<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_add_post_frontend_template() {
	include dirname( __FILE__ ) . '/template.php';
}

function hocwp_add_post_frontend_post_type() {
	$post_type = HT_Options()->get_tab( 'post_type', 'post', 'add_post_frontend' );

	return apply_filters( 'hocwp_add_post_frontend_post_type', $post_type );
}

function hocwp_add_post_frontend_post_meta() {

}

add_action( 'load-post.php', 'hocwp_add_post_frontend_post_meta' );
add_action( 'load-post-new.php', 'hocwp_add_post_frontend_post_meta' );

function hocwp_add_post_frontend_save_post( $post_id ) {

}

add_action( 'save_post', 'hocwp_add_post_frontend_save_post' );

function hocwp_add_post_frontend_admin_notices() {
	if ( false !== ( $msg = get_transient( 'hocwp_add_vip_post_day_message' ) ) ) {
		echo $msg;
		delete_transient( 'hocwp_add_vip_post_day_message' );
	}
}

add_action( 'admin_notices', 'hocwp_add_post_frontend_admin_notices' );

if ( is_admin() ) {
	require dirname( __FILE__ ) . '/admin-setting-page.php';
	require dirname( __FILE__ ) . '/user-profile.php';
}

function hocwp_add_post_frontend_wp_handle_upload_prefilter( $file ) {
	if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
		global $hocwp_theme;

		$options = isset( $hocwp_theme->options['media'] ) ? $hocwp_theme->options['media'] : '';

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$user    = wp_get_current_user();
		$user_id = $user->ID;
		$info    = getdate();
		$counts  = get_user_meta( $user_id, 'media_upload_counts', true );

		if ( ! is_array( $counts ) ) {
			$counts = array();
		}

		$year  = $info['year'];
		$yday  = $info['yday'];
		$count = isset( $counts[ $year ][ $yday ] ) ? absint( $counts[ $year ][ $yday ] ) : 0;
		$count ++;
		$limit = isset( $options['upload_per_day'] ) ? absint( $options['upload_per_day'] ) : 10;
		$limit = apply_filters( 'hocwp_theme_limit_media_upload_per_day', $limit );

		if ( ! is_numeric( $limit ) ) {
			$limit = 10;
		}

		$limit = absint( $limit );

		if ( $count > $limit ) {
			$file['error'] = sprintf( __( 'You can only upload %s media file per day.', 'sb-core' ), $limit );
		} else {
			$counts[ $year ][ $yday ] = $count;
			update_user_meta( $user_id, 'media_upload_counts', $counts );
		}
	}

	return $file;
}

add_filter( 'wp_handle_upload_prefilter', 'hocwp_add_post_frontend_wp_handle_upload_prefilter' );

function hocwp_add_post_frontend_allow_contributor_upload_media() {
	if ( current_user_can( 'edit_posts' ) ) {
		$role = get_role( 'contributor' );

		if ( ! $role->has_cap( 'upload_files' ) ) {
			$role->add_cap( 'upload_files' );
		}

		if ( ! is_admin() ) {
			if ( ! $role->has_cap( 'edit_published_pages' ) ) {
				$role->add_cap( 'edit_published_pages' );
				$role->add_cap( 'edit_published_posts' );
				$role->add_cap( 'edit_others_pages' );
				$role->add_cap( 'edit_others_posts' );
			}
		} else {
			if ( $role->has_cap( 'edit_others_posts' ) ) {
				$role->remove_cap( 'edit_others_posts' );
				$role->remove_cap( 'edit_others_pages' );
				$role->remove_cap( 'edit_published_posts' );
				$role->remove_cap( 'edit_published_pages' );
			}
		}
	}
}

add_action( 'init', 'hocwp_add_post_frontend_allow_contributor_upload_media' );

