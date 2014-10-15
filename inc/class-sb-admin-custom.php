<?php
class SB_Admin_Custom {
    public static function add_submenu_page($title, $slug, $callback) {
        if(!self::submenu_page_exists($slug)) {
            add_submenu_page('sb_options', $title, $title, 'manage_options', $slug, $callback);
        }
    }

    public static function submenu_page_exists($handle) {
        return self::menu_page_exists($handle, true);
    }

    public static function menu_page_exists($handle, $sub = false) {
        if(!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
            return false;
        }
        global $menu, $submenu;
        $check_menu = $sub ? $submenu : $menu;
        if(empty($check_menu)) {
            return false;
        }
        foreach($check_menu as $k => $item) {
            if($sub) {
                foreach($item as $sm) {
                    if($handle == $sm[2]) {
                        return true;
                    }
                }
            } else {
                if($handle == $item[2]) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function get_current_page() {
        return isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
    }

    public static function is_about_page() {
        $page = self::get_current_page();
        if('sb_options' == $page) {
            return true;
        }
        return false;
    }

    public static function add_section($section_id, $section_title, $page_slug) {
        add_settings_section($section_id, $section_title, array('SB_Admin_Custom', 'section_description_callback'), $page_slug);
    }

    public static function section_description_callback($args) {
        if($args['id'] == 'sb_options_section') {
            _e('Short description about SB Options.', 'sb-core');
        } else {
            _e('Change your settings below:', 'sb-core');
        }
    }

    public static function add_setting_field($field_id, $field_title, $section_id, $callback, $page_slug) {
        add_settings_field($field_id, $field_title, $callback, $page_slug, $section_id);
    }

    public static function setting_page_callback() {
        include SB_CORE_INC_PATH . '/sb-setting-page.php';
    }
}