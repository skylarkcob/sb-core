<?php
function sb_core_session() {
    if(version_compare(phpversion(), '5.4.0', '>=')) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    } else {
        if(session_id() == '') {
            session_start();
        }
    }
}
add_action('init', 'sb_core_session');

function sb_core_textdomain() {
    load_plugin_textdomain('sb-core', false, SB_CORE_DIRNAME . '/languages/');
}
add_action('plugins_loaded', 'sb_core_textdomain');

function sb_core_about_page_link($links) {
    $sb_link = '<a href="admin.php?page=sb_options">' . __('About', 'sb-core') . '</a>';
    array_unshift($links, $sb_link);
    return $links;
}
add_filter('plugin_action_links_' . SB_CORE_BASENAME, 'sb_core_about_page_link');

function sb_core_admin_style_and_script() {
    wp_register_style('sb-core-style', SB_CORE_URL . '/css/sb-core-admin-style.css');
    wp_enqueue_style('sb-core-style');

    wp_register_script('sb-core-admin', SB_CORE_URL . '/js/sb-core-admin-script.js', array('jquery'), false, true);
    wp_localize_script('sb-core-admin', 'sb_core_admin_ajax', array('url' => SB_Core::get_admin_ajax_url()));
    wp_enqueue_script('sb-core-admin');
}
add_action('admin_enqueue_scripts', 'sb_core_admin_style_and_script');

function sb_core_style_and_script() {
    wp_register_script('sb-core', SB_CORE_URL . '/js/sb-core-script.js', array('jquery'), false, true);
    wp_localize_script('sb-core', 'sb_core_ajax', array('url' => SB_Core::get_admin_ajax_url()));
    wp_enqueue_script('sb-core');
}
add_action('wp_enqueue_scripts', 'sb_core_style_and_script');

function sb_core_deactivate_ajax_callback() {
    _e('All plugins and themes that are created by SB Team will be deactivated! Are you sure?', 'sb-core');
    die();
}
add_action('wp_ajax_sb_core_deactivate', 'sb_core_deactivate_ajax_callback');
add_action('wp_ajax_nopriv_sb_core_deactivate', 'sb_core_deactivate_ajax_callback');

function sb_plugins_ajax_callback() {
    include SB_CORE_INC_PATH . '/sb-plugins-ajax.php';
    die();
}
add_action('wp_ajax_sb_plugins', 'sb_plugins_ajax_callback');
add_action('wp_ajax_nopriv_sb_plugins', 'sb_plugins_ajax_callback');

function sb_option_reset_ajax_callback() {
	$sb_page = isset($_POST['sb_option_page']) ? $_POST['sb_option_page'] : '';
	switch($sb_page) {
		case 'sb_paginate':
			echo json_encode(SB_Default_Setting::sb_paginate());
			break;
		default:
			break;
	}
	die();
}
add_action('wp_ajax_sb_option_reset', 'sb_option_reset_ajax_callback');
add_action('wp_ajax_nopriv_sb_option_reset', 'sb_option_reset_ajax_callback');

add_action('wp_head', array('SB_Core', 'check_license'));

function sb_core_admin_bar( $wp_admin_bar ) {
    if(current_user_can('manage_options')) {
        $args = array(
            'id'        => 'sb-options',
            'title'     => __('SB Options', 'sb-core'),
            'href'      => admin_url('admin.php?page=sb_options'),
            'meta'      => array('class' => 'sb-options'),
            'parent'    => 'site-name',
            'tabindex'  => '10'
        );
        $wp_admin_bar->add_node( $args );
    }
}
add_action('admin_bar_menu', 'sb_core_admin_bar');

function sb_core_get_default_theme() {
	$themes = wp_get_themes();
	$wp_theme = '';
	foreach($themes as $theme) {
		$author_uri = $theme->get('AuthorURI');
		if(strrpos($author_uri, 'wordpress.org') !== false) {
			$wp_theme = $theme;
			break;
		}
	}
	if(empty($wp_theme)) {
		foreach($themes as $theme) {
			$text_domain = $theme->get('TextDomain');
			if(strrpos($text_domain, 'sb-theme') === false) {
				$wp_theme = $theme;
				break;
			}
		}
	}
	return $wp_theme;
}

function sb_core_deactivate() {
	$theme = wp_get_theme();
	$text_domain = $theme->get('TextDomain');
	if('sb-theme' == $text_domain) {
		$theme = sb_core_get_default_theme();
		if(!empty($theme)) {
			switch_theme($theme->get('TextDomain'));
		}
	}
}
register_deactivation_hook(SB_CORE_FILE, 'sb_core_deactivate');

function sb_testing() {
	return apply_filters('sb_testing', false);
}

require SB_CORE_INC_PATH . '/sb-plugin-load.php';