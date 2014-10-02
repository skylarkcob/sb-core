<?php
defined('ABSPATH') OR exit;

class SB_Term {
    public static function get($taxonomy, $args = array()) {
        $args['hide_empty'] = 0;
        return get_terms($taxonomy, $args);
    }

    public static function get_links($taxonomy, $args = array()) {
        $separator = ', ';
        $terms = self::get($taxonomy, $args);
        $result = '';
        extract($args, EXTR_OVERWRITE);
        foreach($terms as $term) {
            $result .= sprintf('<a href="%1$s" title="%2$s">%3$s</a>', get_term_link($term), $term->name, $term->name).$separator;
        }
        $result = trim($result, $separator);
        return $result;
    }

    public static function get_tags($args = array()) {
        return self::get('post_tag', $args);
    }

    public static function get_tag_links($args = array()) {
        return self::get_links('post_tag', $args);
    }

    public static function get_no_childrens($taxonomy, $args = array()) {
        $args['parent'] = 0;
        $terms = self::get($taxonomy, $args);
        return $terms;
    }

    public static function get_category_no_childrens($args = array()) {
        return self::get_no_childrens('category', $args);
    }

}