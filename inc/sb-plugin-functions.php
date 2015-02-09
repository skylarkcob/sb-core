<?php
function sb_core_get_default_theme() {
	SB_Core::get_default_theme();
}

function sb_testing() {
	return apply_filters('sb_testing', false);
}

function sb_core_testing() {
    return apply_filters('sb_core_testing', sb_testing());
}

function sb_build_meta_name($meta_name) {
    return SB_Core::build_meta_box_field_name($meta_name);
}

function sb_meta_box_nonce() {
    wp_nonce_field('sb_meta_box', 'sb_meta_box_nonce');
}

function sb_term_meta_nonce() {
    wp_nonce_field('sb_term_meta', 'sb_term_meta_nonce');
}

function sb_core_owner() {
    return apply_filters('sb_core_owner', false);
}

function sb_core_get_image_url($name) {
    return SB_CORE_URL . '/images/' . $name;
}

function sb_core_ajax_loader() {
    echo '<div class="sb-ajax-loader center"><img src="' . sb_core_get_image_url('icon-ajax-loader.gif') . '"></div>';
}