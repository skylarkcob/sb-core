<?php
require SB_CORE_INC_PATH . '/class-sb-field.php';

require SB_CORE_INC_PATH . '/class-sb-widget-field.php';

require SB_CORE_INC_PATH . '/class-sb-admin-custom.php';

require SB_CORE_INC_PATH . '/class-sb-admin.php';

$sb_admin = new SB_Admin();

function sb_core_check_license() {
    $is_valid = true;
    if(!method_exists('SB_Core', 'check_license') || !has_action('wp_head', array('SB_Core', 'check_license'))) {
        $is_valid = false;
    }
    if(!$is_valid) {
        wp_die(__('This website is temporarily unavailable, please try again later.', 'sb-core'));
    }
}
add_action('init', 'sb_core_check_license');

function is_sb_admin_page() {
    $result = SB_Admin_Custom::is_sb_page();
    return apply_filters('sb_admin_page', $result);
}

function sb_admin_need_ui() {
    return apply_filters('sb_admin_need_ui', false);
}

function sb_get_core_template_part($name) {
    $name .= '.php';
    include SB_CORE_INC_PATH . '/' . $name;
}

function sb_core_get_loop($name) {
    sb_get_core_template_part('loop/' . $name);
}

function sb_core_get_ajax($name) {
    sb_get_core_template_part('ajax/' . $name);
}

function sb_core_get_content($name) {
    sb_get_core_template_part('content/' . $name);
}