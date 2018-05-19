<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_ext_ads_display( $args ) {
	$ads      = $args;
	$html     = '';
	$position = '';
	if ( ! is_object( $args ) ) {
		if ( ! is_array( $args ) ) {
			$args = array(
				'position' => $args
			);
		}
		$position = isset( $args['position'] ) ? $args['position'] : '';
		if ( ! empty( $position ) ) {
			$random           = (bool) HT()->get_value_in_array( $args, 'random' );
			$current_datetime = current_time( 'timestamp' );
			$query_args       = array(
				'post_type'      => 'hocwp_ads',
				'posts_per_page' => 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'relation' => 'OR',
						array(
							'key'     => 'expire',
							'compare' => 'NOT EXISTS'
						),
						array(
							'key'     => 'expire',
							'value'   => '',
							'compare' => '='
						),
						array(
							'key'   => 'expire',
							'value' => 0,
							'type'  => 'numeric'
						),
						array(
							'key'     => 'expire',
							'value'   => $current_datetime,
							'type'    => 'numeric',
							'compare' => '>='
						)
					),
					array(
						'key'   => 'active',
						'value' => 1,
						'type'  => 'numeric'
					)
				)
			);
			if ( $random ) {
				$query_args['orderby'] = 'rand';
			}
			$ads = HT_Query()->posts_by_meta( 'position', $position, $query_args );
			if ( $ads->have_posts() ) {
				$posts = $ads->posts;
				$ads   = array_shift( $posts );
			}
		}
	}
	if ( $ads instanceof WP_Post && 'hocwp_ads' == $ads->post_type ) {
		$code = get_post_meta( $ads->ID, 'code', true );
		if ( empty( $code ) ) {
			$image = get_post_meta( $ads->ID, 'image', true );
			if ( ! empty( $image ) ) {
				$image = wp_get_attachment_url( $image );
				$img   = new HOCWP_Theme_HTML_Tag( 'img' );
				$img->add_attribute( 'src', $image );
				$url = get_post_meta( $ads->ID, 'url', true );
				if ( ! empty( $url ) ) {
					$url = esc_url( $url );
					$a   = new HOCWP_Theme_HTML_Tag( 'a' );
					$a->add_attribute( 'href', $url );
					$a->set_text( $img );
					$code = $a->build();
				} else {
					$code = $img->build();
				}
			}
		}
		if ( ! empty( $code ) ) {
			$class = HT()->get_value_in_array( $args, 'class' );
			$class .= ' hocwp-ads text-center ads';
			if ( ! empty( $position ) ) {
				$class .= ' position-' . $position;
				$class .= ' ' . $position;
			}
			$class .= ' ' . $ads->post_name;
			$div   = new HOCWP_Theme_HTML_Tag( 'div' );
			$div->add_attribute( 'class', $class );
			$div->set_text( $code );
			$html = $div->build();
		}
	}
	$html = apply_filters( 'hocwp_ads_html', $html, $ads_or_args = $args );
	echo $html;
}