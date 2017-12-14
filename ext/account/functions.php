<?php
function hocwp_ext_account_connect_social_avatar_html( $url ) {
	return ( ! empty( $url ) ) ? '<img src="' . $url . '" style="float:left;margin-right:10px;width:50px;height:50px;">' : '';
}

function hocwp_ext_account_connect_social_name_html( $name ) {
	return ( ! empty( $name ) ) ? '<span style="display:block;font-weight:700;margin-bottom:6px;font-size:12px;">' . $name . '</span>' : '';
}

function hocwp_ext_account_connect_google_avatar_name_html( $data ) {
	$html = '';
	if ( is_array( $data ) ) {
		$photos = isset( $data['photos'] ) ? $data['photos'] : '';
		if ( is_array( $photos ) ) {
			$photo = array_shift( $photos );
			$thumb = isset( $photo['url'] ) ? $photo['url'] : '';
			if ( ! empty( $thumb ) ) {
				$html .= hocwp_ext_account_connect_social_avatar_html( $thumb );
			}
		}
		$names = isset( $data['names'] ) ? $data['names'] : '';
		if ( is_array( $names ) ) {
			$name = array_shift( $names );
			$name = isset( $name['displayName'] ) ? $name['displayName'] : '';
			if ( ! empty( $name ) ) {
				$html .= hocwp_ext_account_connect_social_name_html( $name );
			}
		}
	}

	return $html;
}

function hocwp_ext_account_connect_facebook_avatar_name_html( $data ) {
	$html = '';
	if ( is_array( $data ) ) {
		$thumb = isset( $data['picture']['data']['url'] ) ? $data['picture']['data']['url'] : '';
		if ( ! empty( $thumb ) ) {
			$html .= hocwp_ext_account_connect_social_avatar_html( $thumb );
		}
		$name = isset( $data['name'] ) ? $data['name'] : '';
		if ( ! empty( $name ) ) {
			$html .= hocwp_ext_account_connect_social_name_html( $name );
		}
	}

	return $html;
}