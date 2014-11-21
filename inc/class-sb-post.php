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

    public static function get_attachments($post_id) {
        return self::get_images($post_id);
    }

    public static function get_first_attachment($post_id) {
        return self::get_first_image($post_id);
    }

    public static function get_first_image($post_id) {
        $images = self::get_images($post_id);
        if(is_array($images)) {
            $image = array_shift($images);
            return $image;
        }
        return '';
    }

    public static function auto_set_thumbnail($post_id) {
        $first_image = self::get_first_image($post_id);
        if(empty($first_image)) {
            $post = get_post($post_id);
            if($post) {
                $first_image = SB_PHP::get_first_image($post->post_content);
                self::set_thumbnail_from_url($post_id, $first_image);
            }
        } else {
            self::set_thumbnail($post_id, $first_image->ID);
        }
    }

    public static function get_first_image_url($post_id) {
        $image = self::get_first_image($post_id);
        if($image && !is_wp_error($image)) {
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
            $post = get_post($post_id);
            $result = SB_PHP::get_first_image($post->post_content);
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
        $crop = false;
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
            $tmp = bfi_thumb($result, array('width' => $width, 'height' => $height, 'crop' => $crop));
            if(!empty($tmp)) {
                $result = $tmp;
            }
            $result = '<img class="wp-post-image sb-post-image img-responsive" alt="' . get_the_title($post_id) . '" width="' . $width . '" height="' . $height . '" src="' . $result . '"' . $style . '>';
        }
        return apply_filters('sb_thumbnail_html', $result);
    }

    public static function the_thumbnail_html($args = array()) {
        $post_id = get_the_ID();
        $thumbnail_url = '';
        extract($args, EXTR_OVERWRITE);
        if(empty($thumbnail_url)) {
            $thumbnail_url = self::get_thumbnail_html($args);
        } else {
            $thumbnail_url = sprintf('<img class="wp-post-image sb-post-image img-responsive" src="%1$s" alt="%2$s">', $thumbnail_url, get_the_title($post_id));
        }
        ?>
        <div class="post-thumbnail">
            <a href="<?php echo get_permalink($post_id); ?>"><?php echo $thumbnail_url; ?></a>
        </div>
        <?php
    }

    public static function set_thumbnail($post_id, $attach_id) {
        return set_post_thumbnail($post_id, $attach_id);
    }

    public static function set_thumbnail_from_url($post_id, $image_url) {
        if(!current_theme_supports('post-thumbnails') || has_post_thumbnail($post_id) || empty($image_url)) {
            return false;
        }
        $attach_id = SB_Core::fetch_media($image_url);
        return self::set_thumbnail($post_id, $attach_id);
    }

    public static function get_author_url() {
        return get_author_posts_url(get_the_author_meta('ID'));
    }

    public static function the_author() {
        printf('<span class="post-author"><i class="fa fa-user icon-left"></i> <span class="author vcard"><a class="url fn n" href="%1$s" rel="author">%2$s</a></span></span>',
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

    public static function get_the_date() {
        $post_date = get_the_date();
        if(empty($post_date)) {
            $post_id = get_the_ID();
            $post = get_post($post_id);
            $post_date = $post->post_date_gmt;
            $post_date = date(SB_Option::get_date_format(), strtotime($post_date));
        }
        return $post_date;
    }

    public static function the_date() {
        $post_date = self::get_the_date();
        printf('<span class="date"><i class="fa fa-clock-o icon-left"></i><span>%1$s</span></span>',
            esc_html($post_date)
        );
    }

    public static function the_date_time() {
        printf('<span class="date"><i class="fa fa-clock-o"></i><span class="post-date">%1$s</span>&nbsp;<span class="post-time">%2$s</span></span>',
            esc_html(get_the_date()),
            esc_html(get_the_time())
        );
    }

    public static function get_types($args = array(), $output = 'names', $operator = 'and') {
        $args['public'] = true;
        return get_post_types($args, $output, $operator);
    }

    public static function update_custom_menu_url($post_id, $meta_value) {
        self::update_meta($post_id, '_menu_item_url', $meta_value);
    }

    public static function the_comment_link() {
        if(!post_password_required() && (comments_open() || get_comments_number())) : ?>
            <span class="comments-link post-comment"><i class="fa fa-comments icon-left"></i> <?php comments_popup_link( '<span class="count">0</span> <span class="text">' . __('comment', 'sb-core') . '</span>', '<span class="count">1</span> <span class="text">' . __('comment', 'sb-core') . '</span>', '<span class="count">%</span> <span class="text">' . __('comments', 'sb-core') . '</span>'); ?></span>
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

    public static function the_term_link($post_id, $taxonomy, $args = array()) {
        $separator = ', ';
        $number = -1;
        $link = true;
        $top_level = false;
        extract($args, EXTR_OVERWRITE);
        $terms = self::get_terms($post_id, $taxonomy);
        $result = '';
        $count = 0;
        foreach($terms as $term) {
            if($top_level && $term->parent > 0) {
                continue;
            }
            if($link) {
                $result .= sprintf('<a href="%1$s">%2$s</a>', get_term_link($term), $term->name);
            } else {
                $result .= $term->name;
            }
            $result .= $separator;
            $count++;
            if($number > 0 && $count >= $number) {
                break;
            }
        }
        $result = trim($result, $separator);
        if(empty($result)) {
            $term = array_shift($terms);
            $result = $term->name;
        }
        echo $result;
    }

    public static function the_term_name($post_id, $taxonomy, $args = array()) {
        $args['link'] = false;
        self::the_term_link($post_id, $taxonomy, $args);
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

    public static function get_menu_custom_items() {
        $result = array();
        $menus = wp_get_nav_menus();
        foreach($menus as $menu) {
            $menu_items = wp_get_nav_menu_items($menu->term_id);
            foreach($menu_items as $item) {
                if('custom' == $item->type) {
                    array_push($result, $item);
                }
            }
        }
        return $result;
    }

    public static function change_custom_menu_url($args = array()) {
        $site_url = '';
        $url = '';
        extract($args, EXTR_OVERWRITE);
        if(empty($url)) {
            $url = SB_Option::get_site_url();
        }
        if(empty($site_url) || $url == $site_url) {
            return;
        }
        $menu_items = self::get_menu_custom_items();
        foreach($menu_items as $item) {
            if('trang-chu' == $item->post_name || 'home' == $item->post_name) {
                $item_url = $item->url;
                $item_url = str_replace($url, $site_url, $item_url);
                SB_Post::update_custom_menu_url($item->ID, $item_url);
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

    public static function get_author_link() {
        return get_the_author_link();
    }

    public static function the_author_link() {
        echo self::get_author_link();
    }

    public static function the_category() {
        the_category(', ', '');
    }

    public static function the_term($post_id, $taxonomy) {
        the_terms($post_id, $taxonomy);
    }

    public static function the_term_html($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);
        if($terms && ! is_wp_error($terms)) : ?>
        <span class="cat-links">
		        <span class="entry-utility-prep"><?php _e('Posted in:', 'sb-core'); ?> </span>
            <?php the_terms($post_id, $taxonomy); ?>
            </span>
    <?php endif;
    }
}