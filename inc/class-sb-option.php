<?php
defined('ABSPATH') OR exit;

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
}