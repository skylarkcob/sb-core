<?php
class SB_Post {
    public static function get_images($post_id) {
        $result = array();
        $files = get_children(array('post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image'));
        foreach($files as $file) {
            $image_file = get_attached_file($file->ID);
            if(file_exists($image_file)) {
                array_push($result, $file);
            }
        }
        return $result;
    }

    public static function get_first_image($post_id) {
        $images = self::get_images($post_id);
        foreach($images as $image) {
            return $image;
        }
        return '';
    }

    public static function get_first_image_url($post_id) {
        $image = self::get_first_image($post_id);
        if($image) {
            return wp_get_attachment_url($image->id);
        } else {
            $post = get_post($post_id);
            return SB_PHP::get_first_image($post->post_content);
        }
    }

    public static function get_thumbnail_url($args = array()) {
        $post_id = get_the_ID();
        $result = '';
        $size = '';
        extract($args, EXTR_OVERWRITE);
        if(has_post_thumbnail($post_id)) {
            $image_path = get_attached_file(get_post_thumbnail_id($post_id));
            if(file_exists($image_path)) {
                $image_attributes = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size);
                if($image_attributes) {
                    $result = $image_attributes[0];
                }
            }
        }
        if(empty($result)) {
            $result = apply_filters('hocwp_post_image_url', '');
        }
        if(empty($result)) {
            $result = self::get_first_image_url($post_id);
        }
        if(empty($result)) {
            $result = SB_Option::get_theme_thumbnail_url();
        }
        return apply_filters('sb_thumbnail_url', $result);
    }

    public static function get_default_thumbnail_url() {
        return SB_CORE_URL . '/images/no-thumbnail-grey-100.png';
    }

    public static function get_thumbnail_html($args = array()) {
        $size = '';
        $post_id = get_the_ID();
        $width = '';
        $height = '';
        $style = '';
        extract($args, EXTR_OVERWRITE);
        if(is_array($size) && count($size) == 1) {
            $size = array($size, $size);
        }
        if(count($size) == 2) {
            $width = $size[0];
            $height = $size[1];
            $style = ' style="width:' . $width . 'px; height:' . $height . 'px;"';
        }
        $args['size'] = $size;
        $result = self::get_thumbnail_url($args);
        if(!empty($result)) {
            $result = '<img class="wp-post-image" alt="' . get_the_title($post_id) . '" width="' . $width . '" height="' . $height . '" src="' . $result . '"' . $style . '>';
        }
        return apply_filters('sb_thumbnail_html', $result);
    }

    public static function the_thumbnail_html($args = array()) {
        $post_id = get_the_ID();
        extract($args, EXTR_OVERWRITE);
        ?>
        <div class="post-thumbnail">
            <a href="<?php echo get_permalink($post_id); ?>"><?php echo self::get_thumbnail_html($args); ?></a>
        </div>
        <?php
    }

    public static function get_author_url() {
        return get_author_posts_url(get_the_author_meta('ID'));
    }

    public static function the_author() {
        printf('<span class="post-author"><i class="fa fa-user"></i> <span class="author vcard"><a class="url fn n" href="%1$s" rel="author">%2$s</a></span></span>',
            esc_url( self::get_author_url()),
            get_the_author_meta('user_nicename')
        );
    }

    public static function get_time_compare($post) {
        return get_post_time('G', false, $post);
    }

    public static function get_human_minute_diff($post) {
        return SB_Core::get_human_minute_diff(self::get_time_compare($post));
    }

    public static function get_human_time_diff($post) {
        return SB_Core::get_human_time_diff(self::get_time_compare($post));
    }

    public static function the_date() {
        printf('<span class="date"><span>%1$s</span></span>',
            esc_html( get_the_date())
        );
    }

    public static function update_custom_menu_url($post_id, $meta_value) {
        self::update_meta($post_id, '_menu_item_url', $meta_value);
    }

    public static function the_comment_link() {
        if(!post_password_required() && (comments_open() || get_comments_number())) : ?>
            <span class="comments-link post-comment"><i class="fa fa-comments"></i> <?php comments_popup_link( '<span class="count">0</span> <span class="text">' . __('comment', 'sb-core') . '</span>', '<span class="count">1</span> <span class="text">' . __('comment', 'sb-core') . '</span>', '<span class="count">%</span> <span class="text">' . __('comments', 'sb-core') . '</span>'); ?></span>
        <?php endif;
    }

    public static function get_tag_ids($post_id) {
        $tags = (array)wp_get_post_tags($post_id, array('fields' => 'ids'));
        return $tags;
    }

    public static function get_category_ids($post_id) {
        return wp_get_post_categories($post_id, array('fields' => 'ids'));
    }

    public static function get_term_ids($post_id, $taxonomy) {
        return wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));
    }

    public static function get_terms($post_id, $taxonomy) {
        return wp_get_post_terms($post_id, $taxonomy);
    }

    public static function get_meta($post_id, $meta_key) {
        return get_post_meta($post_id, $meta_key, true);
    }

    public static function get_sb_meta($post_id, $meta_key) {
        $meta_key = sb_build_meta_name($meta_key);
        return self::get_meta($post_id, $meta_key);
    }

    public static function update_meta($post_id, $meta_key, $meta_value) {
        update_post_meta($post_id, $meta_key, $meta_value);
    }

    public static function update_metas($post_id, $metas = array()) {
        foreach($metas as $meta) {
            $meta_key = isset($meta['key']) ? $meta['key'] : '';
            $meta_value = isset($meta['value']) ? $meta['value'] : '';
            if(empty($meta_key)) {
                continue;
            }
            self::update_meta($post_id, $meta_key, $meta_value);
        }
    }

    public static function change_custom_menu_url($args = array()) {
        $site_url = '';
        $url = '';
        extract($args, EXTR_OVERWRITE);
        if(empty($site_url) || empty($url) || $url == $site_url) {
            return;
        }
        $menus = wp_get_nav_menus();
        foreach($menus as $menu) {
            $menu_items = wp_get_nav_menu_items($menu->term_id);
            foreach($menu_items as $item) {
                if('custom' == $item->type || 'trang-chu' == $item->post_name || 'home' == $item->post_name) {
                    $item_url = str_replace($url, $site_url, $item->url);
                    SB_Post::update_custom_menu_url($item->term_id, $item_url);
                }
            }
        }
    }

    public static function get_by_slug($slug, $post_type = 'post') {
        return get_page_by_path($slug, OBJECT, $post_type);
    }

    public static function insert($args = array()) {
        $post_title = '';
        $post_content = '';
        $post_status = 'pending';
        $post_type = 'post';
        $post_author = 1;
        $first_admin = SB_User::get_first_admin();
        if($first_admin) {
            $post_author = $first_admin->ID;
        }
        $defaults = array(
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status'           => $post_status,
            'post_type'             => $post_type,
            'post_author'           => $post_author,
            'ping_status'           => get_option('default_ping_status'),
            'post_parent'           => 0,
            'menu_order'            => 0,
            'to_ping'               =>  '',
            'pinged'                => '',
            'post_password'         => '',
            'guid'                  => '',
            'post_content_filtered' => '',
            'post_excerpt'          => '',
            'import_id'             => 0
        );
        $args = wp_parse_args($args, $defaults);
        $args['post_title'] = wp_strip_all_tags($args['post_title']);
        $post_id = wp_insert_post($args);
        return $post_id;
    }
}