<?php
if(class_exists('SB_Captcha')) {
    return;
}

class SB_Captcha {
    public static function get($args = array()) {
        global $sb_captcha;
        if(empty($sb_captcha) && self::use_captcha()) {
            if(!is_array($args)) {
                $len = $args;
                $args = (array) $args;
                $args['len'] = $len;
            }
            $sb_captcha = new ReallySimpleCaptcha();
            $len = isset($args['len']) ? $args['len'] : 4;
            $bg = isset($args['bg']) ? (array) $args['bg'] : array();
            if(count($bg) > 0) {
                $sb_captcha->bg = $bg;
            }
            $sb_captcha->char_length = $len;
            $sb_captcha->cleanup(5);
        }
        return $sb_captcha;
    }

    public static function use_captcha() {
        $use_captcha = apply_filters('sb_use_captcha', true);
        if($use_captcha && !class_exists('ReallySimpleCaptcha')) {
            $use_captcha = false;
        }
        return $use_captcha;
    }

    public static function build_key($args = array()) {
        $key = isset($args['key']) ? $args['key'] : 'default';
        $key = 'sb_captcha_' . $key . '_prefix';
        return $key;
    }

    public static function cleanup($minute = 5) {
        $sb_captcha = self::get($args);
        $sb_captcha->cleanup($minute);
    }

    public static function delete_expired_transient() {
        global $wpdb;
        $current_timestamp = time();
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->options WHERE option_name like %s AND option_value < %d", '_transient_timeout_sb_captcha_%', $current_timestamp ) );
        foreach($results as $row) {
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_id = %d", $row->option_id ) );
        }
    }

    public static function delete_all_transient() {
        global $wpdb;
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name like %s", '_transient_sb_captcha_%' ) );
        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name like %s", '_transient_timeout_sb_captcha_%' ) );
    }

    public static function remove($file_name) {
        if(empty($file_name)) {
            return;
        }
        $sb_captcha = self::get();
        $file_name_only = SB_PHP::get_file_name_without_extension($file_name);
        delete_transient('sb_captcha_' . $file_name_only . '_code');
        delete_transient('sb_captcha_' . $file_name_only . '_prefix');
        SB_PHP::set_session('sb_captcha_image', '');
        self::delete_expired_transient();
        $sb_captcha->remove($file_name_only);
    }

    public static function get_captcha_session() {
        $file_name = SB_PHP::get_session('sb_captcha_image');
        return $file_name;
    }

    public static function set_captcha_session($file_name) {
        SB_PHP::set_session('sb_captcha_image', $file_name);
    }

    public static function build_transient_name($file_name) {
        $file_name_only = SB_PHP::get_file_name_without_extension($file_name);
        $result = array(
            'prefix' => 'sb_captcha_' . $file_name_only . '_prefix',
            'code' => 'sb_captcha_' . $file_name_only . '_code'
        );
        return $result;
    }

    public static function set_transient($name, $value) {
        set_transient($name, $value, 30 * MINUTE_IN_SECONDS);
    }

    public static function generate_image($args = array()) {
        if(!self::use_captcha()) {
            return;
        }
        $sb_captcha = self::get($args);
        $file_name = self::get_captcha_session();
        $transient_keys = self::build_transient_name($file_name);
        $force = isset($args['force']) ? (bool) $args['force'] : false;
        if($force || empty($file_name) || false === get_transient($transient_keys['prefix']) || !file_exists(trailingslashit($sb_captcha->tmp_dir) . $file_name)) {
            $old_file_name = $file_name;
            $word = $sb_captcha->generate_random_word();
            $prefix = mt_rand();
            $file_name = $sb_captcha->generate_image( $prefix, $word );
            $transient_keys = self::build_transient_name($file_name);
            self::set_captcha_session($file_name);
            self::set_transient($transient_keys['prefix'], $prefix);
            self::set_transient($transient_keys['code'], SB_Core::hash_password($word));
            self::remove($old_file_name);
        }
        $file_name = trailingslashit(plugins_url()) . 'really-simple-captcha/tmp/' . $file_name;
        return $file_name;
    }

    public static function check($code) {
        if(!self::use_captcha()) {
            return true;
        }
        $result = false;
        $file_name = SB_PHP::get_session('sb_captcha_image');
        if(!empty($file_name)) {
            $file_name_only = SB_PHP::get_file_name_without_extension($file_name);
            $transient_key = 'sb_captcha_' . $file_name_only . '_code';
            if(false !== ($captcha_code = get_transient($transient_key))) {
                if(SB_Core::password_compare($code, $captcha_code)) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    public static function the_image($args = array()) {
        $url = self::generate_image($args);
        $file_name = SB_PHP::get_file_name_without_extension($url);
        if(!empty($url)) {
            $len = isset($args['len']) ? $args['len'] : 4;
            echo '<img class="sb-captcha-image captcha-code" data-file="' . $file_name . '" src="' . $url . '" data-len="' . $len . '">';
        }
    }
}