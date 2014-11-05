<?php
class SB_Message {
    public static function get_confirm_text() {
        $text = __('Are you sure?', 'sb-core');
        return apply_filters('sb_confirm_text', $text);
    }

    public static function get_redirecting_text() {
        $text = __('Redirecting', 'sb-core');
        return apply_filters('sb_redirecting_text', $text);
    }

    public static function get_redirecting_to_text() {
        $text = __('Redirecting to %s', 'sb-core');
        return apply_filters('sb_redirecting_to_text', $text);
    }

    public static function get_deactivate_sb_core_confirm_text() {
        $text = __('All plugins and themes that are created by SB Team will be deactivated! Are you sure?', 'sb-core');
        return apply_filters('sb_deactivate_sb_core_confirm_text', $text);
    }
}