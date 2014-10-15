<?php
class SB_Option {
    public static function get_date_format() {
        return get_option('date_format');
    }

    public static function get_time_fortmat() {
        return get_option('time_format');
    }

    public static function get_timezone_string() {
        return get_option('timezone_string');
    }

    public static function update_permalink($struct) {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure( $struct );
    }

    public static function get_permalink_struct() {
        return get_option('permalink_structure');
    }

    public static function get() {
        global $sb_options;
        if(empty($sb_options)) {
            $sb_options = get_option('sb_options');
        }
        return $sb_options;
    }

    public static function get_favicon_url() {
        $options = self::get();
        return isset($options['theme']['favicon']) ? $options['theme']['favicon'] : '';
    }

    public static function get_logo_url() {
        $options = self::get();
        return isset($options['theme']['logo']) ? $options['theme']['logo'] : '';
    }

    public static function the_footer_text_html() {
        $options = self::get();
        echo isset($options['theme']['footer_text']) ? $options['theme']['footer_text'] : '';
    }

    public static function get_login_logo_url() {
        $options = self::get();
        $logo_url = isset($options['login_page']['logo']) ? $options['login_page']['logo'] : '';
        if(empty($logo_url) && defined('SB_THEME_VERSION')) {
            $logo_url = isset($options['theme']['logo']) ? $options['theme']['logo'] : '';
        }
        return $logo_url;
    }

    public static function get_theme_thumbnail_url() {
        $options = self::get();
        $url = isset($options['theme']['thumbnail']) ? $options['theme']['thumbnail'] : '';
        if(empty($url)) {
            $url = isset($options['post_widget']['no_thumbnail']) ? $options['post_widget']['no_thumbnail'] : '';
        }
        if(empty($url)) {
            $url = SB_Post::get_default_thumbnail_url();
        }
        return $url;
    }

    public static function get_widget_thumbnail_url() {
        $options = self::get();
        $url = isset($options['post_widget']['no_thumbnail']) ? $options['post_widget']['no_thumbnail'] : '';
        if(empty($url)) {
            $url = isset($options['theme']['thumbnail']) ? $options['theme']['thumbnail'] : '';
        }
        if(empty($url)) {
            $url = SB_Post::get_default_thumbnail_url();
        }
        return $url;
    }

    public static function get_by_key($args = array()) {
        $default = isset($args['default']) ? $args['default'] : '';
        $options = self::get();
        $keys = isset($args['keys']) ? $args['keys'] : array();
        $value = $default;
        $tmp = $options;
        foreach($keys as $key) {
            $tmp = isset($tmp[$key]) ? $tmp[$key] : '';
            if(empty($tmp)) {
                break;
            }
        }
        if(!empty($tmp)) {
            $value = $tmp;
        }
        return $value;
    }

    public static function get_bool_value_by_key($args = array()) {
        $value = self::get_by_key($args);
        return (bool)$value;
    }

    public static function get_int_value_by_key($args = array()) {
        $value = self::get_by_key($args);
        return intval($value);
    }
}