<?php
class SB_Term {
    public static function get($taxonomy, $args = array()) {
        $args['hide_empty'] = 0;
        return get_terms($taxonomy, $args);
    }

    public static function get_permalink($term) {
        return get_term_link($term);
    }

    public static function get_links($taxonomy, $args = array()) {
        $separator = ', ';
        $terms = self::get($taxonomy, $args);
        $result = '';
        extract($args, EXTR_OVERWRITE);
        foreach($terms as $term) {
            $result .= sprintf('<a href="%1$s" title="%2$s">%3$s</a>', get_term_link($term), $term->name, $term->name) . $separator;
        }
        $result = trim($result, $separator);
        return $result;
    }

    public static function get_menus($args = array()) {
        return get_terms('nav_menu', $args);
    }

    public static function delete($term_id, $taxonomy, $args = array()) {
        wp_delete_term($term_id, $taxonomy, $args);
    }

    public static function delete_menu($menu_id, $args = array()) {
        self::delete($menu_id, 'nav_menu', $args);
    }

    public static function delete_all_menu($args = array()) {
        $menus = self::get_menus(array('hide_empty' => false));
        foreach($menus as $menu) {
            self::delete_menu($menu->term_id, $args);
        }
    }

    public static function get_all_metas($term_id, $taxonomy) {
        $metas = SB_Option::get_term_metas();
        $result = array();
        $meta_info = isset($metas[$term_id]) ? $metas[$term_id] : array();
        $tax = isset($meta_info['taxonomy']) ? $meta_info['taxonomy'] : '';
        if($tax == $taxonomy) {
            $result = $meta_info;
        }
        return $result;
    }

    public static function change_meta_url($old_url, $new_url) {
        $metas = SB_Option::get_term_metas();
        if(!is_array($metas)) {
            return;
        }
        $new_metas = array();
        foreach($metas as $key => $meta) {
            if(isset($meta['thumbnail'])) {
                $meta['thumbnail'] = str_replace($old_url, $new_url, $meta['thumbnail']);
            }
            $new_metas[$key] = $meta;
        }
        SB_Option::update_term_metas($new_metas);
    }

    public static function get_only_top_parents($taxonomy, $args = array()) {
        $args['parent'] = 0;
        return self::get($taxonomy, $args);
    }

    public static function get_top_parent($term_id, $taxonomy) {
        $term = get_term($term_id, $taxonomy);
        while($term->parent > 0) {
            $term = get_term($term->parent, $taxonomy);
        }
        return $term;
    }

    public static function get_single() {
        return get_queried_object();
    }

    public static function get_level($term_id, $taxonomy) {
        $level = 0;
        $term = self::get_by('id', $term_id, $taxonomy);
        while($term && !is_wp_error($term) && $term->parent > 0) {
            $term = self::get_by('id', $term->parent, $taxonomy);
            $level++;
        }
        return $level;
    }

    public static function get_category_level($term_id) {
        return self::get_level($term_id, 'category');
    }

    public static function get_category_by_id($term_id) {
        return get_category($term_id);
    }

    public static function get_single_id() {
        return get_queried_object()->term_id;
    }

    public static function get_same_level($term, $args = array()) {
        $defaults = array('parent' => $term->parent, 'depth' => 1);
        $args = wp_parse_args($args, $defaults);
        return self::get($term->taxonomy, $args);
    }

    public static function get_first_level_child($term_id, $taxonomy, $args = array()) {
        $args['parent'] = $term_id;
        return self::get($taxonomy, $args);
    }

    public static function get_meta($term_id, $taxonomy, $meta_key) {
        $meta_info = self::get_all_metas($term_id, $taxonomy);
        $result = isset($meta_info[$meta_key]) ? $meta_info[$meta_key] : '';
        return $result;
    }

    public static function get_thumbnail_url($term_id, $taxonomy) {
        return self::get_meta($term_id, $taxonomy, 'thumbnail');
    }

    public static function get_category_thumbnail_url($term_id) {
        return self::get_thumbnail_url($term_id, 'category');
    }

    public static function get_tags($args = array()) {
        return self::get('post_tag', $args);
    }

    public static function get_tag_links($args = array()) {
        return self::get_links('post_tag', $args);
    }

    public static function get_by_meta($taxonomy, $meta_key, $meta_value, $args = array()) {
        $terms = self::get($taxonomy, $args);
        $result = array();
        foreach($terms as $term) {
            $meta = self::get_meta($term->term_id, $taxonomy, $meta_key);
            if($meta == $meta_value) {
                array_push($result, $term);
            }
        }
        return $result;
    }

    public static function get_no_childrens($taxonomy, $args = array()) {
        $args['parent'] = 0;
        $terms = self::get($taxonomy, $args);
        return $terms;
    }

    public static function get_childrens($parent_id, $taxonomy, $args = array()) {
        $args['child_of'] = $parent_id;
        $terms = self::get($taxonomy, $args);
        return $terms;
    }

    public static function get_category_no_childrens($args = array()) {
        return self::get_no_childrens('category', $args);
    }

    public static function get_categories($args = array()) {
        $args['hide_empty'] = false;
        return get_categories($args);
    }

    public static function get_by($field, $value, $taxonomy, $output = OBJECT, $filter = 'raw') {
        return get_term_by($field, $value, $taxonomy, $output = OBJECT, $filter = 'raw');
    }

}