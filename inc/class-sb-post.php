<?php
class SB_Post {
    public static function get_images($post_id) {
        $result = array();
        $files = get_posts(array('post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image'));
        foreach($files as $file) {
            $image_file = get_attached_file($file->ID);
            if(file_exists($image_file)) {
                array_push($result, $file);
            }
        }
        return $result;
    }

    public static function get_comment_number($post_id = '') {
        $comment_number = get_comments_number($post_id);
        if($comment_number == 1) {
            $comments = SB_Post::get_comments($post_id);
            $comment_number = count($comments);
        }
        return $comment_number;
    }

    public static function the_comment_number($post_id = '') {
        echo self::get_comment_number($post_id);
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

    public static function clean_all_revision() {
        SB_Core::delete_revision();
    }

    public static function set_post_term($post_id, $terms, $taxonomy) {
        return wp_set_post_terms($post_id, $terms, $taxonomy);
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

    public static function check_duplicate_comment($commentdata) {
        if(!isset($commentdata['comment_post_ID']) || !isset($commentdata['comment_content']) || !isset($commentdata['comment_author'])) {
            return false;
        }
        if(!isset($commentdata['comment_parent'])) {
            $commentdata['comment_parent'] = 0;
        }
        global $wpdb;
        $dupe = $wpdb->prepare(
            "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_parent = %s AND comment_approved != 'trash' AND ( comment_author = %s ",
            wp_unslash($commentdata['comment_post_ID']),
            wp_unslash($commentdata['comment_parent']),
            wp_unslash($commentdata['comment_author'])
        );
        if(isset($commentdata['comment_author_email'])) {
            $dupe .= $wpdb->prepare(
                "OR comment_author_email = %s ",
                wp_unslash($commentdata['comment_author_email'])
            );
        }
        $dupe .= $wpdb->prepare(
            ") AND comment_content = %s LIMIT 1",
            wp_unslash($commentdata['comment_content'])
        );
        if($wpdb->get_var($dupe)) {
            return true;
        }
        return false;
    }

    public static function update_product_price($post_id, $price) {
        if(!is_numeric($price) || !is_numeric($post_id) || $post_id < 1) {
            return;
        }
        $sale_price = floatval(self::get_meta($post_id, '_sale_price'));
        $regular_price = floatval(self::get_meta($post_id, '_regular_price'));
        if($sale_price > 0 && $price < $regular_price) {
            self::update_meta($post_id, '_sale_price', $price);
        } else {
            self::update_meta($post_id, '_regular_price', $price);
        }
        self::update_meta($post_id, '_price', $price);
    }

    public static function plus_product_price($post_id, $price_plus) {
        if(!is_numeric($price_plus) || !is_numeric($post_id) || $post_id < 1) {
            return;
        }
        $price = floatval(self::get_meta($post_id, '_price'));
        $price += $price_plus;
        self::update_product_price($post_id, $price);
    }

    public static function minus_product_price($post_id, $price_plus) {
        if(!is_numeric($price_plus) || !is_numeric($post_id) || $post_id < 1) {
            return;
        }
        $price = floatval(self::get_meta($post_id, '_price'));
        $price -= $price_plus;
        self::update_product_price($post_id, $price);
    }

    public static function remove_duplicate_comment($post_id = 0) {
        global $wpdb;
        $query = "SELECT * FROM $wpdb->comments";
        if($post_id > 0) {
            $query .= " WHERE comment_post_ID = " . $post_id;
        }
        $comments = $wpdb->get_results($query);
        $compare_comments = $comments;
        foreach($comments as $comment) {
            $comment_id = $comment->comment_ID;
            foreach($compare_comments as $compare) {
                $compare_comment_id = $compare->comment_ID;
                if($comment_id == $compare_comment_id) {
                    continue;
                }
                if($comment->comment_content == $compare->comment_content) {
                    $query = "DELETE FROM $wpdb->comments WHERE comment_ID = " . $compare_comment_id;
                    $wpdb->query($query);
                }
            }
        }
    }

    public static function change_all_url($args = array()) {
        $query = self::get_all();
        if($query->have_posts()) {
            $my_posts = $query->posts;
            global $post;
            foreach($my_posts as $post) {
                if(SB_Core::is_error($post)) {
                    continue;
                }
                setup_postdata($post);
                $args['post_content'] = get_the_content();
                $args['post_id'] = get_the_ID();
                self::change_url_in_content($args);
            }
            wp_reset_postdata();
        }
    }

    public static function change_url_in_content($args = array()) {
        $post_id = isset($args['post_id']) ? $args['post_id'] : 0;
        $content = isset($args['post_content']) ? $args['post_content'] : '';
        if(empty($content)) {
            $post = isset($args['post']) ? $args['post'] : null;
            if(!SB_Core::is_error($post)) {
                $content = $post->post_content;
                if($post_id < 1) {
                    $post_id = $post->ID;
                }
            }
        }
        $url = isset($args['url']) ? $args['url'] : '';
        $site_url = isset($args['site_url']) ? $args['site_url'] : '';
        $content = str_replace($url, $site_url, $content);
        $post_data = array(
            'ID' => $post_id,
            'post_content' => $content
        );
        self::update($post_data);
    }

    public static function update($post_data) {
        $post_id = isset($post_data['ID']) ? intval($post_data['ID']) : 0;
        if($post_id > 0) {
            wp_update_post($post_data);
        }
    }

    public static function get_all($post_type = 'post') {
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => -1
        );
        return new WP_Query($args);
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
            if($post && !is_wp_error($post)) {
                $result = SB_PHP::get_first_image($post->post_content);
            }
        }
        if(empty($result)) {
            $result = SB_Option::get_theme_thumbnail_url();
        }
        return apply_filters('sb_thumbnail_url', $result);
    }

    public static function get_thumbnail_full_url($post_id = '') {
        if(empty($post_id)) {
            $post_id = get_the_ID();
        }
        $result = '';
        if(has_post_thumbnail($post_id)) {
            $image_path = get_attached_file(get_post_thumbnail_id($post_id));
            if(file_exists($image_path)) {
                $image_attributes = wp_get_attachment_image_src(get_post_thumbnail_id($post_id));
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
            if($post && !is_wp_error($post)) {
                $result = SB_PHP::get_first_image($post->post_content);
            }
        }
        if(empty($result)) {
            $result = SB_Option::get_theme_thumbnail_url();
        }
        return apply_filters('sb_thumbnail_full_url', $result);
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
            $result = '<img class="wp-post-image sb-post-image img-responsive thumbnail-image" alt="' . get_the_title($post_id) . '" width="' . $width . '" height="' . $height . '" src="' . $result . '"' . $style . '>';
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
            $thumbnail_url = sprintf('<img class="wp-post-image sb-post-image img-responsive thumbnail-image" src="%1$s" alt="%2$s">', $thumbnail_url, get_the_title($post_id));
        }
        ?>
        <div class="post-thumbnail">
            <a href="<?php echo get_permalink($post_id); ?>"><?php echo $thumbnail_url; ?></a>
        </div>
        <?php
    }

    public static function the_thumbnail_only_link_image_html($args = array()) {
        $post_id = get_the_ID();
        $thumbnail_url = '';
        extract($args, EXTR_OVERWRITE);
        if(empty($thumbnail_url)) {
            $thumbnail_url = self::get_thumbnail_html($args);
        } else {
            $thumbnail_url = sprintf('<img class="wp-post-image sb-post-image img-responsive thumbnail-image" src="%1$s" alt="%2$s">', $thumbnail_url, get_the_title($post_id));
        }
        ?>
        <a href="<?php echo get_permalink($post_id); ?>"><?php echo $thumbnail_url; ?></a>
        <?php
    }

    public static function the_thumbnail_only_image_html($args = array()) {
        $post_id = get_the_ID();
        $thumbnail_url = '';
        extract($args, EXTR_OVERWRITE);
        if(empty($thumbnail_url)) {
            $thumbnail_url = self::get_thumbnail_html($args);
        } else {
            $thumbnail_url = sprintf('<img class="wp-post-image sb-post-image img-responsive thumbnail-image" src="%1$s" alt="%2$s">', $thumbnail_url, get_the_title($post_id));
        }
        echo $thumbnail_url;
    }

    public static function the_thumbnail_crop_html_by_id($post_id, $width, $height) {
        $args = array(
            'width' => $width,
            'height' => $height,
            'crop' => true,
            'post_id' => $post_id
        );
        self::the_thumbnail_html($args);
    }

    public static function the_thumbnail_crop_html($width, $height) {
        $args = array(
            'width' => $width,
            'height' => $height,
            'crop' => true
        );
        self::the_thumbnail_html($args);
    }

    public static function the_thumbnail_crop_only_link_image_html($width, $height) {
        $args = array(
            'width' => $width,
            'height' => $height,
            'crop' => true
        );
        self::the_thumbnail_only_link_image_html($args);
    }

    public static function the_thumbnail_crop_only_image_html($width, $height) {
        $args = array(
            'width' => $width,
            'height' => $height,
            'crop' => true
        );
        self::the_thumbnail_only_image_html($args);
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

    public static function get_first_term($post_id, $taxonomy) {
        $terms = self::get_terms($post_id, $taxonomy);
        $first_term = array_shift($terms);
        return $first_term;
    }

    public static function get_types($args = array(), $output = 'names', $operator = 'and') {
        $args['public'] = true;
        return get_post_types($args, $output, $operator);
    }

    public static function update_custom_menu_url($post_id, $meta_value) {
        self::update_meta($post_id, '_menu_item_url', $meta_value);
    }

    public static function the_comment_link($post_id = 0) {
        if($post_id == 0) {
            $post_id = get_the_ID();
        }
        $comment_number = self::get_comment_number($post_id);
        $comment_link = get_comments_link($post_id);
        if(!post_password_required() && (comments_open() || get_comments_number())) : ?>
            <span class="comments-link post-comment">
                <i class="fa fa-comments icon-left"></i>
                <a href="<?php echo $comment_link; ?>">
                    <?php echo '<span class="count">' . $comment_number . '</span> <span class="text">' . __('bình  luận', 'sb-core') . '</span>'; ?>
                </a>
            </span>
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
            if(!SB_Core::is_error($term)) {
                $result = $term->name;
            }
        }
        echo $result;
    }

    public static function get_next_post_url() {
        $result = '';
        $post = get_adjacent_post(false, '', true);
        if(!SB_Core::is_error($post)) {
            $result = get_permalink($post);
        }
        return $result;
    }

    public static function get_previous_post_url() {
        $result = '';
        $post = get_adjacent_post(false, '', false);
        if(!SB_Core::is_error($post)) {
            $result = get_permalink($post);
        }
        return $result;
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

    public static function update_sb_meta($post_id, $meta_key, $meta_value) {
        $meta_key = sb_build_meta_name($meta_key);
        self::update_meta($post_id, $meta_key, $meta_value);
    }

    public static function comment_closed($post_id) {
        $result = false;
        if(!comments_open($post_id)) {
            $result = true;
        }
        return $result;
    }

    public static function get_views($post_id) {
        $views = self::get_meta($post_id, 'views');
        if(empty($views)) {
            $views = 0;
        }
        return $views;
    }

    public static function insert_comment($post_id, $comment_data) {
        $comment_data['comment_post_ID'] = $post_id;
        $comment_id = 0;
        if(!self::check_duplicate_comment($comment_data)) {
            $comment_id = wp_insert_comment($comment_data);
            $comment_id = intval($comment_id);
        }
        return $comment_id;
    }

    public static function the_temperature($post_id) {
        $temp = self::get_temperature($post_id);
        echo '<span class="temperature"><i class="fa fa-fire icon-left"></i><span class="count">' . $temp . '°</span></span>';
    }

    public static function the_temperature_html($post_id) {
        $class = 'item-temp';
        $temp = SB_Post::get_temperature($post_id);
        if($temp < 50) {
            $class = SB_PHP::add_string_with_space_before($class, 'temp-50');
        } elseif($temp >= 50 && $temp < 250) {
            $class = SB_PHP::add_string_with_space_before($class, 'temp-100');
        } elseif($temp >= 250 && $temp < 500) {
            $class = SB_PHP::add_string_with_space_before($class, 'temp-250');
        } elseif($temp >= 500 && $temp < 700) {
            $class = SB_PHP::add_string_with_space_before($class, 'temp-500');
        } else {
            $class = SB_PHP::add_string_with_space_before($class, 'temp-1000');
        }
        ?>
        <div class="<?php echo $class; ?>"><?php SB_Post::the_temperature($post_id); ?></div>
        <?php
    }

    public static function get_temperature($post_id) {
        $result = 0;
        $views = self::get_views($post_id);
        if(0 < $views) {
            $result = sqrt($views);
            $result *= 5;
            $result = ceil($result);
        }
        $comment_count = self::get_comment_number($post_id);
        $result += $comment_count;
        if(1 > $result) {
            $result = 1;
        } elseif(1000000 < $result) {
            $result = 1000000;
        }
        return $result;
    }

    public static function update_views($post_id) {
        $views = self::get_views($post_id);
        $views++;
        self::update_meta($post_id, 'views', $views);
        $views_week = intval(self::get_meta($post_id, 'views_week'));
        $new_week = intval(get_option('sb_new_week'));
        if(SB_PHP::is_monday() && $new_week != 1) {
            $views_week = 0;
            update_option('sb_new_week', 1);
        } else {
            if(!SB_PHP::is_monday() && $new_week != 0) {
                update_option('sb_new_week', 0);
            }
        }
        $views_week++;
        self::update_meta($post_id, 'views_week', $views_week);
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
        if(!is_array($menus)) {
            return;
        }
        foreach($menus as $menu) {
            $menu_items = wp_get_nav_menu_items($menu->term_id);
            if(!is_array($menu_items)) {
                continue;
            }
            foreach($menu_items as $item) {
                if('custom' == $item->type) {
                    array_push($result, $item);
                }
            }
        }
        return $result;
    }

    public static function get_menu_items_by_location($location) {
        $locations = SB_Core::get_menu_location();
        $menu_id = isset($locations[$location]) ? $locations[$location] : 0;
        return wp_get_nav_menu_items($menu_id);
    }

    public static function get_sticky_post_ids() {
        $ids = get_option('sticky_posts');
        return (array)$ids;
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
        if(!is_array($menu_items)) {
            return;
        }
        foreach($menu_items as $item) {
            if('trang-chu' == $item->post_name || 'home' == $item->post_name) {
                $item_url = $item->url;
                $item_url = mb_ereg_replace($url, $site_url, $item_url);
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

    public static function get_all_image_from_content($content) {
        return SB_PHP::get_all_image_html_from_string($content);
    }

    public static function get_all_image_html_from_content($content) {
        return SB_PHP::get_all_image_html_from_string($content);
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

    public static function get_comments($post_id, $args = array()) {
        $args['post_id'] = $post_id;
        return get_comments($args);
    }

    public static function the_term($post_id, $taxonomy, $before = '', $sep = ', ', $after = '') {
        the_terms($post_id, $taxonomy, $before, $sep, $after);
    }

    public static function get_rate_average($post_id, $precision = 2) {
        $value = floatval(self::get_meta($post_id, 'rate'));
        $value = round($value, $precision);
        return $value;
    }

    public static function update_rate_average($post_id, $score) {
        $old_score = self::get_rate_average($post_id);
        $value = $old_score + $score;
        if($old_score > 0) {
            $value /= 2;
        }
        update_post_meta($post_id, 'rate', $value);
        return $value;
    }

    public static function get_rate_count($post_id) {
        return intval(self::get_meta($post_id, 'rates'));
    }

    public static function update_rate_count($post_id) {
        $count = self::get_rate_count($post_id);
        $count++;
        update_post_meta($post_id, 'rates', $count);
        return $count;
    }

    public static function the_term_html($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);
        if($terms && ! is_wp_error($terms)) : ?>
        <span class="cat-links">
		        <span class="entry-utility-prep"><?php _e('Chuyên mục:', 'sb-core'); ?> </span>
            <?php the_terms($post_id, $taxonomy); ?>
            </span>
    <?php endif;
    }
}