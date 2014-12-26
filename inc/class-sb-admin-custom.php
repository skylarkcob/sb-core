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

    public static function add_recaptcha_setting_field_callback() {
        $options = SB_Option::get();
        $value = isset($options['global']['recaptcha']['site_key']) ? $options['global']['recaptcha']['site_key'] : '';
        $args = array(
            'id' => 'sb_core_global_recaptcha_site_key',
            'name' => SB_Option::build_sb_option_name(array('global', 'recaptcha', 'site_key')),
            'value' => $value,
            'description' => __('The Google reCaptcha Site key, you can use this in the HTML code your site serves to users.', 'sb-core'),
            'container_class' => 'margin-bottom'
        );
        SB_Field::text($args);
        $value = isset($options['global']['recaptcha']['secret_key']) ? $options['global']['recaptcha']['secret_key'] : '';
        $args = array(
            'id' => 'sb_core_global_recaptcha_secret_key',
            'name' => SB_Option::build_sb_option_name(array('global', 'recaptcha', 'secret_key')),
            'value' => $value,
            'description' => __('The reCaptcha Secret key, you can use this for communication between your site and Google.', 'sb-core'),
        );
        SB_Field::text($args);
    }

    public static function add_recaptcha_setting_field($section_id, $page_id) {
        self::add_setting_field('sb_core_global_recaptcha_key', 'reCAPTCHA', $section_id, array('SB_Admin_Custom', 'add_recaptcha_setting_field_callback'), $page_id);
    }

    public static function add_media_upload_to_post_page() {
        $pagenow = SB_Admin_Custom::get_page_now();
        if('post.php' == $pagenow || 'post-new.php' == $pagenow) {
            wp_enqueue_media();
        }
    }

    public static function get_page_now() {
        global $pagenow;
        return $pagenow;
    }

    public static function is_edit_term_page() {
        $page = self::get_page_now();
        if(strrpos($page, 'edit-tags') !== false) {
            return true;
        }
        return false;
    }

    public static function is_sb_page() {
        $page = self::get_current_page();
        if(SB_PHP::is_string_contain($page, 'sb')) {
            return true;
        }
        return false;
    }

    public static function is_about_page() {
        $page = self::get_current_page();
        if('sb_options' == $page) {
            return true;
        }
        return false;
    }

    public static function get_default_rss_feed_number() {
        return SB_RSS_FEED_NUMBER;
    }

    public static function set_current_rss_feed_item($args) {
        global $sb_current_rss_feed;
        $sb_current_rss_feed = $args;
    }

    public static function get_current_rss_feed_item() {
        global $sb_current_rss_feed;
        if(!is_array($sb_current_rss_feed)) {
            $sb_current_rss_feed = array();
        }
        return $sb_current_rss_feed;
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
        sb_core_get_content('sb-setting-page');
    }
}