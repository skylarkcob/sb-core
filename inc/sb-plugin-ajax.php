<?php
function sb_core_deactivate_ajax_callback() {
    echo SB_Message::get_deactivate_sb_core_confirm_text();
    die();
}
add_action('wp_ajax_sb_core_deactivate_message', 'sb_core_deactivate_ajax_callback');

function sb_core_get_admin_url_callback() {
	$name = isset($_POST['name']) ? $_POST['name'] : '';
	echo admin_url($name);
	die();
}
add_action('wp_ajax_sb_core_get_admin_url', 'sb_core_get_admin_url_callback');

function sb_plugins_ajax_callback() {
    sb_core_get_ajax('sb-plugins-ajax');
    die();
}
add_action('wp_ajax_sb_plugins', 'sb_plugins_ajax_callback');

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

function sb_add_ui_item_ajax_callback() {
    $type = isset($_POST['data_type']) ? $_POST['data_type'] : '';
    switch($type) {
        case 'rss_feed':
            sb_core_get_ajax('ajax-add-rss-feed');
            break;
    }
    die();
}
add_action('wp_ajax_sb_add_ui_item', 'sb_add_ui_item_ajax_callback');

function sb_ui_reset_ajax_callback() {
    $type = isset($_POST['data_type']) ? $_POST['data_type'] : '';
    switch($type) {
        case 'rss_feed':
            $options = SB_Option::get();
            unset($options['theme']['rss_feed']);
            SB_Option::update($options);
            break;
    }
    die();
}
add_action('wp_ajax_sb_ui_reset', 'sb_ui_reset_ajax_callback');

function sb_deactivate_all_sb_product_ajax_callback() {
    update_option('sb_core_activated', 0);
    update_option('sb_core_deactivated_caller', 'user');
    sb_switch_to_default_theme();
    sb_deactivate_all_sb_plugin();
    die();
}
add_action('wp_ajax_sb_deactivate_all_sb_product', 'sb_deactivate_all_sb_product_ajax_callback');

function sb_deactivate_all_sb_plugin() {
    $activated_plugins = get_option('active_plugins');
    $sb_plugins = array(
        'sb-banner-widget/sb-banner-widget.php',
        'sb-clean/sb-clean.php',
        'sb-comment/sb-comment.php',
        'sb-core/sb-core.php',
        'sb-login-page/sb-login-page.php',
        'sb-paginate/sb-paginate.php',
        'sb-post-widget/sb-post-widget.php',
        'sb-tab-widget/sb-tab-widget.php',
        'sb-tbfa/sb-tbfa.php'
    );
    $new_plugins = $activated_plugins;
    foreach($activated_plugins as $plugin) {
        if(in_array($plugin, $sb_plugins)) {
            $item = array($plugin);
            $new_plugins = array_diff($new_plugins, $item);
        }
    }
    update_option('active_plugins', $new_plugins);
}

function sb_switch_to_default_theme() {
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
    $theme = $wp_theme;
    if(!empty($theme)) {
        switch_theme($theme->get('TextDomain'));
    }
}