<?php
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

function sb_core_admin_bar( $wp_admin_bar ) {
    if(current_user_can('manage_options')) {
        $args = array(
            'id'        => 'sb-options',
            'title'     => 'SB Options',
            'href'      => admin_url('admin.php?page=sb_options'),
            'meta'      => array('class' => 'sb-options'),
            'parent'    => 'site-name',
            'tabindex'  => '10'
        );
        $wp_admin_bar->add_node( $args );
    }
}
add_action('admin_bar_menu', 'sb_core_admin_bar');

function sb_core_deactivation() {
    if(!current_user_can('activate_plugins')) {
        return;
    }
    update_option('sb_core_activated', 0);
    update_option('sb_core_deactivated_caller', 'wp');
}
register_deactivation_hook(SB_CORE_FILE, 'sb_core_deactivation');

function sb_core_activation() {
    if(!current_user_can('activate_plugins')) {
        return;
    }
    update_option('sb_core_activated', 1);
}
register_activation_hook(SB_CORE_FILE, 'sb_core_activation');