<?php
class SB_Query {
    public static function get_posts_per_page() {
        return get_option('posts_per_page');
    }

    public static function count_product() {
        $products = new WP_Query(array('post_type' => 'product', 'posts_per_page' => -1));
        return $products->post_count;
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
        return new WP_Query($args);
    }

    public static function get_related_post($args = array()) {
        $related_posts = array();
        $post_id = '';
        $posts_per_page = 5;
        $post_type = 'post';
        extract($args, EXTR_OVERWRITE);
        if(empty($post_id) && (is_single() || is_page())) {
            $post_id = get_the_ID();
        }
        $tags = SB_Post::get_tag_ids($post_id);
        $posts = new WP_Query(array('post_type' => $post_type, 'tag__in' => $tags, 'posts_per_page' => -1));
        $tag_posts = $posts->posts;
        $cats = SB_Post::get_category_ids($post_id);
        $posts = new WP_Query(array('post_type' => $post_type, 'category__in' => $cats, 'posts_per_page' => -1));
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
}