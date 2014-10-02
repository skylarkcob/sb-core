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
}