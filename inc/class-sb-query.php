<?php
class SB_Query {
    public static function get_posts_per_page() {
        return get_option('posts_per_page');
    }

    public static function get_pages($args = array()) {
        return get_pages($args);
    }

    public static function get($args = array()) {
        $transient_name = isset($args['transient_name']) ? $args['transient_name'] : '';
        if(!empty($transient_name) && false === ($query = get_transient($transient_name))) {
            $query = new WP_Query($args);
            set_transient($transient_name, $query, DAY_IN_SECONDS);
        } else {
            $query = new WP_Query($args);
        }
        return $query;
    }

    public static function count_product() {
        $products = self::get(array('post_type' => 'product', 'posts_per_page' => -1));
        return $products->post_count;
    }

    public static function get_product_by_price_range($price_min, $price_max, $args = array()) {
        $args['post_type'] = 'product';
        if(!isset($args['posts_per_page'])) {
            $args['posts_per_page'] = -1;
        }
        $meta_item = array(
            'key' => '_price',
            'value' => array($price_min, $price_max),
            'type' => 'numeric',
            'compare' => 'BETWEEN'
        );
        $args = self::build_meta_query($meta_item, $args);
        return self::get($args);
    }

    public static function get_post_by_term($term_id, $taxonomy, $args = array()) {
        $tax_item = array(
            'taxonomy' => $taxonomy,
            'field' => 'id',
            'terms' => $term_id
        );
        $args = SB_Query::build_tax_query($tax_item, $args);
        return self::get($args);
    }

    public static function get_full_post_by_meta($args = array()) {
        $posts_per_page = isset($args['posts_per_page']) ? $args['posts_per_page'] : self::get_posts_per_page();
        $orderby = isset($args['orderby']) ? $args['orderby'] : '';
        $meta_key = isset($args['meta_key']) ? $args['meta_key'] : '';
        $query = self::get($args);
        if($query->have_posts() && $query->post_count < $posts_per_page) {
            $tmp_query = $query;
            $post_ids = array();
            $my_posts = $query->posts;
            foreach($my_posts as $game) {
                array_push($post_ids, $game->ID);
            }
            $posts_per_page -= $query->post_count;
            unset($args['orderby']);
            unset($args['meta_key']);
            $args['posts_per_page'] = $posts_per_page;
            $args['post__not_in'] = $post_ids;
            $query = self::get($args);
            if($query->have_posts()) {
                $my_posts = $query->posts;
                foreach($my_posts as $game) {
                    array_push($tmp_query->posts, $game);
                }
                $tmp_query->post_count = $tmp_query->post_count + $query->post_count;
            }
            return $tmp_query;
        } else {
            if($query->post_count >= $posts_per_page) {
                return $query;
            }
            unset($args['orderby']);
            unset($args['meta_key']);
            return self::get($args);
        }
    }

    public static function get_oldest_post($args = array()) {
        $args['order'] = 'ASC';
        return self::get($args);
    }

    public static function get_most_view_of_week($args = array()) {
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = 'views_week';
        return self::get($args);
    }

    public static function get_paged() {
        return get_query_var('paged', 1);
    }

    public static function get_recent_post_by_view($args = array()) {
        $posts_per_page = isset($args['posts_per_page']) ? $args['posts_per_page'] : 8;
        $new_args = array(
            'posts_per_page' => $posts_per_page * 3,
            'post_type' => isset($args['post_type']) ? $args['post_type'] : 'post'
        );
        if(isset($args['post__not_in'])) {
            $new_args['post__not_in'] = $args['post__not_in'];
        }
        $query = self::get_recent_post($new_args);
        $post_ids = array();
        if($query->have_posts()) {
            $my_posts = $query->posts;
            $temp_posts = array();
            foreach($my_posts as $post) {
                $post_id = $post->ID;
                $item = array('id' => $post_id, 'views' => SB_Post::get_views($post_id));
                array_push($temp_posts, $item);
            }
            $temp_posts = SB_PHP::array_sort($temp_posts, 'views', 'DESC');
            $count = 0;
            foreach($temp_posts as $temp) {
                if($count >= $posts_per_page) {
                    break;
                }
                array_push($post_ids, $temp['id']);
                $count++;
            }
            if(count($post_ids) > 0) {
                $args['post__in'] = $post_ids;
            }
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'views';
            $query = self::get($args);
        }
        return $query;
    }

    public static function get_post_by_category($term_id, $args = array()) {
        return self::get_post_by_term($term_id, 'category', $args);
    }

    public static function get_sticky_posts($args = array()) {
        $args['post__in'] = SB_Post::get_sticky_post_ids();
        return self::get($args);
    }

    public static function get_post_by_recent_comment($args = array()) {
        $posts_per_page = self::get_posts_per_page();
        extract($args, EXTR_OVERWRITE);
        $comments = SB_Comment::get();
        $posts = array();
        $count = 0;
        foreach($comments as $comment) {
            $post = get_post($comment->comment_post_ID);
            if(in_array($post, $posts)) {
                continue;
            }
            array_push($posts, $post);
            $count++;
            if($count >= $posts_per_page) {
                break;
            }
        }
        if(0 == count($posts)) {
            $args['posts_per_page'] = $posts_per_page;
            $posts = get_posts($args);
        }
        return $posts;
    }

    public static function get_recent_post($args = array()) {
        $defaults = array(
            'posts_per_page'    => self::get_posts_per_page(),
            'paged'             => 1
        );
        $args = wp_parse_args($args, $defaults);
        return self::get($args);
    }

    public static function get_related_post($args = array()) {
        $related_posts = array();
        $post_id = '';
        $posts_per_page = 5;
        $post_type = 'post';
        extract($args, EXTR_OVERWRITE);
        if(empty($post_id) && (is_single() || is_page() || is_singular())) {
            $post_id = get_the_ID();
        }
        $tags = SB_Post::get_tag_ids($post_id);
        $defaults = array('post_type' => $post_type, 'tag__in' => $tags, 'posts_per_page' => -1);
        $defaults = wp_parse_args($defaults, $args);
        $posts = self::get($defaults);
        $tag_posts = $posts->posts;
        $cats = SB_Post::get_category_ids($post_id);
        $defaults = array('post_type' => $post_type, 'category__in' => $cats, 'posts_per_page' => -1);
        $defaults = wp_parse_args($defaults, $args);
        $posts = self::get($defaults);
        $cat_posts = $posts->posts;
        $a_part = SB_PHP::get_part_of(2/3, $posts_per_page);
        foreach($tag_posts as $post) {
            if($post->ID == $post_id || in_array($post, $related_posts)) {
                continue;
            }
            array_push($related_posts, $post);
        }
        $related_posts = array_slice($related_posts, 0, $a_part);
        if(count($related_posts) < $a_part) {
            $a_part_new = $posts_per_page - count($related_posts);
        } else {
            $a_part_new = $posts_per_page - $a_part;
        }
        $count = 0;
        foreach($cat_posts as $post) {
            if($post->ID == $post_id || in_array($post, $related_posts)) {
                continue;
            }
            array_push($related_posts, $post);
            $count++;
            if($count >= $a_part_new) {
                break;
            }
        }
        return $related_posts;
    }

    public static function get_first_post_from_query($query) {
        if($query && $query->have_posts()) {
            return $query->posts[0];
        }
        return null;
    }

    public static function set_loop_count($count) {
        global $sb_loop_count;
        $sb_loop_count = $count;
    }

    public static function get_loop_count() {
        global $sb_loop_count;
        return $sb_loop_count;
    }

    public static function build_tax_query($tax_item, $args) {
        if(is_array($args)) {
            if(isset($args['tax_query'])) {
                array_push($args['tax_query'], $tax_item);
            } else {
                $args['tax_query'] = array($tax_item);
            }
        }
        return $args;
    }

    public static function build_meta_query($meta_item, $args) {
        if(is_array($args)) {
            if(isset($args['meta_query'])) {
                array_push($args['meta_query'], $meta_item);
            } else {
                $args['meta_query'] = array($meta_item);
            }
        }
        return $args;
    }

    public static function build_date_query($date_item, $args) {
        if(is_array($args)) {
            if(isset($args['date_query'])) {
                array_push($args['date_query'], $date_item);
            } else {
                $args['date_query'] = array($date_item);
            }
        }
        return $args;
    }

    public static function get_results($query) {
        global $wpdb;
        return $wpdb->get_results($query, OBJECT);
    }

    public static function build_daily_post_args($args = array()) {
        $today = getdate();
        $date_item = array(
            'year' => $today['year'],
            'month' => $today['mon'],
            'day' => $today['mday']
        );
        $args = self::build_date_query($date_item, $args);
        return $args;
    }

    public static function get_today_posts($args = array()) {
        $args = self::build_daily_post_args($args);
        return self::get($args);
    }

    public static function get_random_posts($args = array()) {
        $args['orderby'] = 'rand';
        return self::get($args);
    }

    public static function get_random_post($post_types = array()) {
        if(!is_array($post_types) || count($post_types) < 1) {
            $post_types = 'post';
        }
        $args = array(
            'post_type' => $post_types,
            'posts_per_page' => 1
        );
        return self::get_random_posts($args);
    }

    public static function build_weekly_post_args($args = array()) {
        $date_item = array(
            'year' => date('Y'),
            'week' => date('W')
        );
        $args = self::build_date_query($date_item, $args);
        return $args;
    }

    public static function get_this_week_posts($args = array()) {
        $args = self::build_weekly_post_args($args);
        return self::get($args);
    }

    public static function build_monthly_post_args($args = array()) {
        $today = getdate();
        $date_item = array(
            'year' => $today['year'],
            'month' => $today['mon']
        );
        $args = self::build_date_query($date_item, $args);
        return $args;
    }

    public static function get_this_month_posts($args = array()) {
        $args = self::build_monthly_post_args($args);
        return self::get($args);
    }

    public static function build_yearly_post_args($args = array()) {
        $today = getdate();
        $date_item = array(
            'year' => $today['year']
        );
        $args = self::build_date_query($date_item, $args);
        return $args;
    }

    public static function get_this_year_posts($args = array()) {
        $args = self::build_yearly_post_args($args);
        return self::get($args);
    }
}