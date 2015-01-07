<?php
function sb_core_get_default_theme() {
	SB_Core::get_default_theme();
}

function sb_testing() {
	return apply_filters('sb_testing', false);
}

function sb_core_testing() {
    return sb_testing();
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