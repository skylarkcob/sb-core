<?php
if(!defined('ABSPATH')) exit;

function sb_core_textdomain() {
    load_plugin_textdomain( 'sb-core', false, SB_CORE_DIRNAME . '/languages/' );
}
add_action('plugins_loaded', 'sb_core_textdomain');

function sb_core_about_page_link($links) {
    $sb_link = '<a href="admin.php?page=sb_options">'.__('About', 'sb-core').'</a>';
    array_unshift($links, $sb_link);
    return $links;
}
add_filter('plugin_action_links_' . SB_CORE_BASENAME, 'sb_core_about_page_link' );

function sb_core_admin_style_and_script() {
    wp_register_script('sb-core-admin', SB_CORE_URL . '/js/sb-core-admin-script.js', array('jquery'), false, true);
    wp_localize_script('sb-core-admin', 'sb_core_admin_ajax', array('url' => admin_url('admin-ajax.php')));
    wp_enqueue_script('sb-core-admin');
}
add_action('admin_enqueue_scripts', 'sb_core_admin_style_and_script');

function sb_core_deactivate_ajax_callback() {
    _e('All plugins and themes that are created by SB Team will be deactivated! Are you sure?', 'sb-core');
    exit;
}
add_action('wp_ajax_sb_core_deactivate', 'sb_core_deactivate_ajax_callback');
add_action('wp_ajax_nopriv_sb_core_deactivate', 'sb_core_deactivate_ajax_callback');

require SB_CORE_INC_PATH . "/sb-plugin-load.php";