<?php
defined('ABSPATH') OR exit;

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
        return wp_get_attachment_url($image->id);
    }

    public static function get_thumbnail_url($post_id) {
        if(has_post_thumbnail($post_id)) {
            $image_path = get_attached_file(get_post_thumbnail_id($post_id));
            if(file_exists($image_path)) {
                $result = wp_get_attachment_url( get_post_thumbnail_id($post_id) );
            }
        }
        if(empty($result)) {
            $result = apply_filters('hocwp_post_image_url', '');
        }
        if(empty($result)) {
            $result = self::get_first_image_url($post_id);
        }
        if(empty($result)) {
            $options = get_option('sb_options');
            $result = isset($options['post_widget']['no_thumbnail']) ? $options['post_widget']['no_thumbnail'] : '';
            if(empty($result)) {
                $result = isset($options['theme']['no_thumbnail']) ? $options['theme']['no_thumbnail'] : '';
            }
        }
        if(empty($result)) {
            $result = SB_CORE_ADMIN_URL . '/images/no-thumbnail-grey-100.png';
        }
        return apply_filters('sb_thumbnail_url', $result);
    }

    public static function get_thumbnail_html($args = array()) {
        $size = array();
        $post_id = get_the_ID();
        $width = '';
        $height = '';
        $style = '';
        extract($args, EXTR_OVERWRITE);
        if($size && !is_array($size)) {
            $size = array($size, $size);
        }
        if(count($size) == 2) {
            $width = $size[0];
            $height = $size[1];
            $style = ' style="width:'.$width.'px; height:'.$height.'px;"';
        }
        $result = self::get_thumbnail_url($post_id);
        if(!empty($result)) {
            $result = '<img class="wp-post-image" alt="'.get_the_title($post_id).'" width="'.$width.'" height="'.$height.'" src="'.$result.'"'.$style.'>';
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
        return get_author_posts_url( get_the_author_meta( 'ID' ) );
    }

    public static function the_author() {
        printf( '<span class="post-author"><i class="fa fa-user"></i> <span class="author vcard"><a class="url fn n" href="%1$s" rel="author">%2$s</a></span></span>',
            esc_url( self::get_author_url() ),
            get_the_author_meta('user_nicename')
        );
    }

    public static function get_time_compare($post) {
        return get_post_time("G", false, $post);
    }

    public static function get_human_minute_diff($post) {
        return SB_Core::get_human_minute_diff(self::get_time_compare($post));
    }

    public static function the_date() {
        printf( '<span class="date"><span>%1$s</span></span>',
            esc_html( get_the_date() )
        );
    }

    public static function the_comment_link() {
        if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) : ?>
            <span class="comments-link post-comment"><i class="fa fa-comments"></i> <?php comments_popup_link( '<span class="count">0</span> <span class="text">'.__('comment', 'sb-core').'</span>', '<span class="count">1</span> <span class="text">'.__('comment', 'sb-core')."</span>", '<span class="count">%</span> <span class="text">'.__('comments', 'sb-core')."</span>" ); ?></span>
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

    public static function get_meta($post_id, $key) {
        return get_post_meta($post_id, $key, true);
    }

}