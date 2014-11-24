<?php
class SB_Option {
    public static function update($sb_options) {
        self::update_option('sb_options', $sb_options);
    }
    public static function get_date_format() {
        return get_option('date_format');
    }

    public static function get_default_language() {
        global $sb_default_language;
        if(empty($sb_default_language)) {
            $sb_default_language = self::get_theme_option_single_key('default_language');
        }
        if(empty($sb_default_language)) {
            $sb_default_language = SB_THEME_DEFAULT_LANGUAGE;
        }
        return apply_filters('sb_default_language', $sb_default_language);
    }

    public static function update_breadcrumb_sep($value) {
        $options = get_option('wpseo_internallinks');
        $options['breadcrumbs-sep'] = $value;
        update_option('wpseo_internallinks', $options);
    }

    public static function edit_breadcrumb_sep() {
        $options = get_option('wpseo_internallinks');
        $sep = isset($options['breadcrumbs-sep']) ? $options['breadcrumbs-sep'] : '/';
        $options['breadcrumbs-sep'] = '<span class="sep">' . trim($sep) . '</span>';
        update_option('wpseo_internallinks', $options);
    }

    public static function get_time_fortmat() {
        return get_option('time_format');
    }

    public static function get_date_time_format() {
        return self::get_date_format() . ' ' . self::get_time_fortmat();
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
        return self::get_theme_option(array('keys' => array('favicon')));
    }

    public static function get_logo_url() {
        return self::get_theme_option(array('keys' => array('logo')));
    }

    public static function get_logo_type() {
        $type = self::get_theme_option(array('keys' => array('logo_type')));
        if(empty($type)) {
            $type = 'background';
        }
        return apply_filters('sb_logo_type', $type);
    }

    public static function get_term_metas() {
        global $sb_term_metas;
        if(!is_array($sb_term_metas) || count($sb_term_metas) < 1) {
            $sb_term_metas = get_option('sb_term_metas');
        }
        return $sb_term_metas;
    }

    public static function update_term_metas($new_value) {
        update_option('sb_term_metas', $new_value);
    }

    public static function get_logo_text() {
        $result = self::get_theme_option(array('keys' => array('logo_text')));
        if(empty($result)) {
            $result = get_bloginfo('name');
        }
        return $result;
    }

    public static function the_footer_text_html() {
        $footer_text = self::get_theme_footer_text();
        $footer_text = wpautop($footer_text);
        echo $footer_text;
    }

    public static function get_login_logo_url() {
        $options = self::get();
        $logo_url = isset($options['login_page']['logo']) ? $options['login_page']['logo'] : '';
        if(empty($logo_url) && defined('SB_THEME_VERSION')) {
            $logo_url = self::get_logo_url();
        }
        return $logo_url;
    }

    public static function get_theme_thumbnail_url() {
        $options = self::get();
        $url = self::get_theme_option(array('keys' => array('thumbnail')));
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
            $url = self::get_theme_option(array('keys' => array('thumbnail')));
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
        if(!is_array($keys)) {
            return $value;
        }
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

    public static function get_theme_social($social_key) {
        return self::get_by_key(array('keys' => array('theme', 'social', $social_key)));
    }

    public static function get_theme_option($args = array()) {
        if(isset($args['keys']) && is_array($args['keys'])) {
            array_unshift($args['keys'], 'theme');
        }
        return self::get_by_key($args);
    }

    public static function get_utility_option($args = array()) {
        if(isset($args['keys']) && is_array($args['keys'])) {
            array_unshift($args['keys'], 'utilities');
        }
        return self::get_by_key($args);
    }

    public static function get_utility($name) {
        $value = self::get_utility_option(array('keys' => array($name)));
        return intval($value);
    }

    public static function utility_enabled($name) {
        $value = self::get_utility($name);
        return (bool)$value;
    }

    public static function get_theme_option_single_key($key_name) {
        $args = array('keys' => array($key_name));
        return self::get_theme_option($args);
    }

    public static function get_theme_footer_text() {
        $args = array(
            'keys' => array('footer_text')
        );
        return self::get_theme_option($args);
    }

    public static function get_scroll_top() {
        $result = self::get_theme_option(array('keys' => array('scroll_top')));
        return (bool)$result;
    }

    public static function get_bool_value_by_key($args = array()) {
        $value = self::get_by_key($args);
        return (bool)$value;
    }

    public static function get_int_value_by_key($args = array()) {
        $value = self::get_by_key($args);
        return intval($value);
    }

    public static function get_home_url() {
        return get_option('home');
    }

    public static function get_site_url() {
        return get_option('siteurl');
    }

    public static function change_option_array_url(&$options, $args = array()) {
        if(!is_array($options)) {
            return;
        }
        $site_url = '';
        $url = '';
        extract($args, EXTR_OVERWRITE);
        if(empty($site_url) || empty($url) || $url == $site_url) {
            return;
        }
        foreach($options as $key => &$value) {
            if(is_array($value)) {
                self::change_option_array_url($value, $args);
            } elseif(!empty($value) && !is_numeric($value)) {
                $value = str_replace($url, $site_url, $value);
            }
        }
    }

    public static function change_option_url($args = array()) {
        $site_url = '';
        $url = '';
        extract($args, EXTR_OVERWRITE);
        if(empty($site_url) || empty($url) || $url == $site_url) {
            return;
        }
        $options = self::get();
        self::change_option_array_url($options, $args);
        self::update($options);
    }

    public static function change_widget_text_url($args = array()) {
        $site_url = '';
        $url = '';
        extract($args, EXTR_OVERWRITE);
        if(empty($site_url) || empty($url) || $url == $site_url) {
            return;
        }
        $text_widgets = get_option('widget_text');
        foreach($text_widgets as $key => $widget) {
            if(isset($widget['text'])) {
                $text_widgets[$key]['text'] = str_replace($url, $site_url, $widget['text']);
            }
        }
        update_option('widget_text', $text_widgets);
    }

    public static function update_option($option_name, $option_value) {
        update_option($option_name, $option_value);
    }

    public static function get_theme_rss_feed() {
        $result = array();
        $count = self::get_theme_option(array('keys' => array('rss_feed', 'count')));
        if(!is_numeric($count)) {
            $count = 0;
        }
        $order = self::get_theme_option(array('keys' => array('rss_feed', 'order')));
        $order = explode(',', $order);
        for($i = 1; $i <= $count; $i++) {
            $title = self::get_theme_option(array('keys' => array('rss_feed', $i, 'title')));
            $number = self::get_theme_option(array('keys' => array('rss_feed', $i, 'number')));
            $url = self::get_theme_option(array('keys' => array('rss_feed', $i, 'url')));
            $id = self::get_theme_option(array('keys' => array('rss_feed', $i, 'id')));
            if((empty($number) || empty($url)) || (empty($title) && empty($id))) {
                continue;
            }
            $feed = array('title' => $title, 'number' => $number, 'url' => $url, 'id' => $id);
            array_push($result, $feed);
        }
        $ordered = array();
        foreach($order as $id) {
            $count = 0;
            foreach($result as $item) {
                if($item['id'] == $id) {
                    array_push($ordered, $item);
                    unset($result[$count]);
                    break;
                }
                $count++;
            }
        }
        return $ordered + $result;
    }
}