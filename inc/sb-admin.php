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