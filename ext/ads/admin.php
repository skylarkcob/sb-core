<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_ads_sub_menu() {
	$title = __( 'Ads Management', 'sb-core' );
	add_theme_page( $title, $title, 'manage_options', 'edit.php?post_type=' . HTE_Ads()->post_type );
}

add_action( 'admin_menu', 'hocwp_ext_ads_sub_menu', 99 );

if ( ! method_exists( 'HOCWP_Theme_Utility', 'is_post_new_update_page' ) || ( function_exists( 'HT_Admin' ) && HT_Admin()->is_post_new_update_page() ) || ( ! function_exists( 'HT_Admin' ) && HT_Util()->is_post_new_update_page() ) ) {
	function hocwp_ext_ads_meta_box() {
		$meta = new HOCWP_Theme_Meta_Post();
		$meta->add_post_type( HTE_Ads()->post_type );
		$meta->set_title( __( 'Ads Information', 'sb-core' ) );
		$meta->form_table = true;

		$options = hocwp_ext_get_ads_positions();

		$args  = array( 'options' => $options, 'option_all' => __( '-- Choose position --', 'sb-core' ) );
		$field = hocwp_theme_create_meta_field( 'position', __( 'Position:', 'sb-core' ), 'select', $args );
		$meta->add_field( $field );

		$args = array(
			'class' => 'datepicker widefat'
		);

		$field = hocwp_theme_create_meta_field( 'expire', __( 'Expiry date:', 'sb-core' ), 'input', $args, 'timestamp' );
		$meta->add_field( $field );

		$field = hocwp_theme_create_meta_field( 'image', __( 'Image:', 'sb-core' ), 'media_upload' );
		$meta->add_field( $field );

		$field = hocwp_theme_create_meta_field( 'url', __( 'Url:', 'sb-core' ), 'input_url', '', 'url' );
		$meta->add_field( $field );

		$field = hocwp_theme_create_meta_field( 'vast_vpaid_url', __( 'VAST/VPAID Ads URL:', 'sb-core' ), 'input_url', '', 'url' );
		$meta->add_field( $field );

		$args  = array( 'row' => 10 );
		$field = hocwp_theme_create_meta_field( 'code', __( 'Code:', 'sb-core' ), 'textarea', $args, 'html' );
		$meta->add_field( $field );

		$args  = array( 'type' => 'checkbox', 'text' => __( 'Only display on mobile?', 'sb-core' ) );
		$field = hocwp_theme_create_meta_field( 'only_mobile', __( 'Mobile', 'sb-core' ), 'input', $args, 'boolean' );
		$meta->add_field( $field );

		$args  = array( 'type' => 'checkbox', 'text' => __( 'Only display on desktop?', 'sb-core' ) );
		$field = hocwp_theme_create_meta_field( 'only_desktop', __( 'Desktop', 'sb-core' ), 'input', $args, 'boolean' );
		$meta->add_field( $field );

		$args  = array( 'type' => 'checkbox', 'text' => __( 'Only display on AMP page?', 'sb-core' ) );
		$field = hocwp_theme_create_meta_field( 'only_amp', __( 'AMP', 'sb-core' ), 'input', $args, 'boolean' );
		$meta->add_field( $field );

		$args  = array( 'type' => 'checkbox', 'text' => __( 'Make this ads as active status?', 'sb-core' ) );
		$field = hocwp_theme_create_meta_field( 'active', __( 'Active', 'sb-core' ), 'input', $args, 'boolean' );
		$meta->add_field( $field );
	}

	add_action( 'load-post.php', 'hocwp_ext_ads_meta_box' );
	add_action( 'load-post-new.php', 'hocwp_ext_ads_meta_box' );

	function hocwp_ext_ads_default_hidden_meta_boxes( $hidden, $screen ) {
		if ( $screen instanceof WP_Screen && 'post' == $screen->base && HTE_Ads()->post_type == $screen->id ) {
			$defaults = array(
				'slugdiv',
				'trackbacksdiv',
				'postcustom',
				'postexcerpt',
				'commentstatusdiv',
				'commentsdiv',
				'authordiv',
				'revisionsdiv'
			);

			$hidden = wp_parse_args( $hidden, $defaults );
			$hidden = array_unique( $hidden );
		}

		return $hidden;
	}

	add_filter( 'default_hidden_meta_boxes', 'hocwp_ext_ads_default_hidden_meta_boxes', 99, 2 );

	function hocwp_ext_ads_add_meta_box_action() {
		$screen = get_current_screen();
		if ( $screen instanceof WP_Screen ) {
			remove_meta_box( 'slugdiv', $screen, 'normal' );
		}
	}

	add_action( 'add_meta_boxes', 'hocwp_ext_ads_add_meta_box_action', 0 );
}

if ( ! method_exists( 'HOCWP_Theme_Utility', 'is_edit_post_new_update_page' ) || ( function_exists( 'HT_Admin' ) && HT_Admin()->is_edit_post_new_update_page() ) || ( ! function_exists( 'HT_Admin' ) && HT_Util()->is_edit_post_new_update_page() ) ) {
	function hocwp_ext_ads_admin_enqueue_scripts() {
		global $post_type, $pagenow;

		if ( HTE_Ads()->post_type == $post_type ) {
			if ( ( function_exists( 'HT_Admin' ) && HT_Admin()->is_post_new_update_page() ) || ( ! function_exists( 'HT_Admin' ) && HT_Util()->is_post_new_update_page() ) ) {
				if ( function_exists( 'HT_Enqueue' ) ) {
					HT_Enqueue()->datepicker();
				} else {
					HT_Util()->enqueue_datepicker();
				}
			}

			if ( 'edit.php' == $pagenow ) {
				wp_enqueue_style( 'hocwp-theme-admin-manage-column-style' );
				wp_enqueue_script( 'hocwp-theme-boolean-meta' );
			}
		}
	}

	add_action( 'admin_enqueue_scripts', 'hocwp_ext_ads_admin_enqueue_scripts', 99 );
}

if ( ! method_exists( 'HOCWP_Theme_Utility', 'is_admin_page' ) || ( function_exists( 'HT_Admin' ) && HT_Admin()->is_admin_page( 'edit.php' ) ) || ( ! function_exists( 'HT_Admin' ) && HT_Util()->is_admin_page( 'edit.php' ) ) ) {
	function hocwp_ext_ads_posts_columns( $columns ) {
		$columns = HT()->insert_to_array( $columns, __( 'Position', 'sb-core' ), 'before_tail', 'position' );
		$columns = HT()->insert_to_array( $columns, __( 'Expiry date', 'sb-core' ), 'before_tail', 'expire_date' );
		$columns = HT()->insert_to_array( $columns, __( 'Desktop/Mobile', 'sb-core' ), 'before_tail', 'desktop_mobile' );
		$columns = HT()->insert_to_array( $columns, __( 'Active', 'sb-core' ), 'before_tail', 'active' );

		return $columns;
	}

	add_filter( 'manage_' . HTE_Ads()->post_type . '_posts_columns', 'hocwp_ext_ads_posts_columns' );

	function hocwp_ext_ads_posts_custom_column_action( $column, $post_id ) {
		if ( 'position' == $column ) {
			$position = get_post_meta( $post_id, 'position', true );
			$options  = hocwp_ext_get_ads_positions();
			$position = isset( $options[ $position ] ) ? $options[ $position ] : '';
			echo $position;
		} elseif ( 'expire_date' == $column ) {
			$expire = get_post_meta( $post_id, 'expire', true );

			if ( is_numeric( $expire ) && 0 != $expire ) {
				$expire = date_i18n( get_option( 'date_format' ), $expire );
			}

			if ( ! empty( $expire ) ) {
				echo $expire;
			}
		} elseif ( 'active' == $column ) {
			$value = get_post_meta( $post_id, 'active', true );
			$value = absint( $value );
			$class = 'hocwp-theme-boolean-meta';

			if ( 1 == $value ) {
				$class .= ' active';
			}

			echo '<span class="' . $class . '" data-meta-key="' . $column . '" data-meta-type="post" data-meta-value="' . $value . '" data-boolean-meta="1" data-id="' . $post_id . '" data-ajax-button="1"></span>';
		} elseif ( 'desktop_mobile' == $column ) {
			$only_mobile  = get_post_meta( $post_id, 'only_mobile', true );
			$only_desktop = get_post_meta( $post_id, 'only_desktop', true );

			if ( $only_desktop && $only_mobile || ( ! $only_mobile && ! $only_desktop ) ) {
				echo '<strong>' . _ex( 'Both', 'desktop mobile', 'sb-core' ) . '</strong>';
			} elseif ( $only_desktop ) {
				echo '<strong>' . _ex( 'Only Desktop', 'desktop mobile', 'sb-core' ) . '</strong>';
			} elseif ( $only_mobile ) {
				echo '<strong>' . _ex( 'Only Mobile', 'desktop mobile', 'sb-core' ) . '</strong>';
			}
		}
	}

	add_action( 'manage_' . HTE_Ads()->post_type . '_posts_custom_column', 'hocwp_ext_ads_posts_custom_column_action', 10, 2 );
}