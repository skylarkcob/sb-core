<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_add_post_frontend_template() {
	include dirname( __FILE__ ) . '/template.php';
}

function hocwp_add_post_frontend_post_type() {
	return apply_filters( 'hocwp_add_post_frontend_post_type', 'post' );
}

function hocwp_add_post_frontend_post_meta() {
	global $hocwp_theme;
	$options    = $hocwp_theme->options;
	$post_price = isset( $options['vip']['post_price'] ) ? $options['vip']['post_price'] : '';
	$post_price = absint( $post_price );
	if ( 0 < $post_price ) {
		$meta       = new HOCWP_Theme_Meta_Post();
		$post_types = hocwp_add_post_frontend_post_type();
		$meta->set_post_types( $post_types );

		$meta->set_title( __( 'VIP Content Information', 'sb-core' ) );
		$meta->set_id( 'vip-content-information' );

		$args  = array( 'disabled' => 'disabled', 'autocomlete' => 'off' );
		$field = hocwp_theme_create_meta_field( 'vip_expired', __( 'Expiry day:', 'sb-core' ), 'input', $args, 'timestamp' );
		$meta->add_field( $field );

		$args  = array(
			'type'        => 'number',
			'description' => sprintf( __( 'Add more day for your VIP content. %s coins per day.', 'sb-core' ), $post_price ),
			'value'       => ''
		);
		$field = hocwp_theme_create_meta_field( 'add_vip_day', __( 'Add more day:', 'sb-core' ), 'input', $args, 'positive_integer' );
		$meta->add_field( $field );
	}
}

add_action( 'load-post.php', 'hocwp_add_post_frontend_post_meta' );
add_action( 'load-post-new.php', 'hocwp_add_post_frontend_post_meta' );

function hocwp_add_post_frontend_save_post( $post_id ) {
	if ( ! HT_Util()->can_save_post( $post_id, 'vip-content-information', 'vip-content-information_nonce' ) ) {
		return;
	}

	if ( isset( $_POST['add_vip_day'] ) && ! empty( $_POST['add_vip_day'] ) ) {
		global $hocwp_theme;
		$options    = $hocwp_theme->options;
		$post_price = isset( $options['vip']['post_price'] ) ? $options['vip']['post_price'] : '';
		$post_price = absint( $post_price );
		$obj        = get_post( $post_id );
		$author     = $obj->post_author;
		$coin       = get_user_meta( $author, 'coin', true );
		$coin       = absint( $coin );
		$day        = absint( $_POST['add_vip_day'] );
		$cost       = $day * $post_price;

		if ( $coin < $post_price || $coin < $cost ) {
			$params = array(
				'message' => __( 'You do not have enough coin to add more day for VIP content.', 'sb-core' ),
				'type'    => 'error',
				'echo'    => false
			);
			$msg    = HT_Util()->admin_notice( $params );
			set_transient( 'hocwp_add_vip_post_day_message', $msg );
		} else {
			$now = time();

			$vip_expired = get_post_meta( $post_id, 'vip_expired', true );
			if ( ! is_numeric( $vip_expired ) || $vip_expired < $now ) {
				$vip_expired = $now;
			}
			$vip_expired = strtotime( '+' . $day . ' day', $vip_expired );
			$res         = update_post_meta( $post_id, 'vip_expired', $vip_expired );
			if ( $res ) {
				$coin -= $cost;
				update_user_meta( $author, 'coin', $coin );
				$params = array(
					'message' => sprintf( __( 'You have added %s more VIP days for this post.', 'sb-core' ), $day ),
					'type'    => 'success',
					'echo'    => false
				);
				$msg    = HT_Util()->admin_notice( $params );
				set_transient( 'hocwp_add_vip_post_day_message', $msg );
			}
		}
	}
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

