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

function sb_core_mail_from_name($name) {
    if('wordpress' == strtolower($name)) {
        $name = get_bloginfo('name');
    }
    return $name;
}
add_filter('wp_mail_from_name', 'sb_core_mail_from_name');

function sb_core_admin_style_and_script() {
    global $pagenow;
    if(sb_core_testing()) {
        wp_register_style('sb-core-style', SB_CORE_URL . '/css/sb-core-admin-style.css');
        wp_register_script('sb-core-admin', SB_CORE_URL . '/js/sb-core-admin-script.js', array('jquery'), false, true);
    } else {
        wp_register_style('sb-core-style', SB_CORE_URL . '/css/sb-core-admin-style.min.css');
        wp_register_script('sb-core-admin', SB_CORE_URL . '/js/sb-core-admin-script.min.js', array('jquery'), false, true);
    }
    wp_enqueue_style('sb-core-style');
    if('nav_menus.php' != $pagenow) {
        wp_localize_script('sb-core-admin', 'sb_core_admin_ajax', array('url' => SB_Core::get_admin_ajax_url()));
        wp_enqueue_script('sb-core-admin');
        if(is_sb_admin_page() && sb_admin_need_ui()) {
            wp_enqueue_script('jquery-ui-core');
        }
    }
}
add_action('admin_enqueue_scripts', 'sb_core_admin_style_and_script');

function sb_core_style_and_script() {
    if(sb_core_testing()) {
        wp_register_script('sb-core', SB_CORE_URL . '/js/sb-core-script.js', array('jquery'), false, true);
    } else {
        wp_register_script('sb-core', SB_CORE_URL . '/js/sb-core-script.min.js', array('jquery'), false, true);
    }
    wp_localize_script('sb-core', 'sb_core_ajax', array('url' => SB_Core::get_admin_ajax_url()));
    wp_enqueue_script('sb-core');
}
add_action('wp_enqueue_scripts', 'sb_core_style_and_script');

function sb_custom_post_type_and_taxonomy() {
    do_action('sb_post_type_and_taxonomy');
}
add_action('init', 'sb_custom_post_type_and_taxonomy', 0);

add_action('wp_head', array('SB_Core', 'check_license'));