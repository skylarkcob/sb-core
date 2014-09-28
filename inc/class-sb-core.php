<?php
if(!defined('ABSPATH')) exit;

if(!class_exists('SB_Core')) {
    class SB_Core {
        public static function get_all_post_image($post_id) {
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

        public static function get_first_post_image($post_id) {
            $result = '';
            $images = self::get_all_post_image($post_id);

            if($images) {
                foreach($images as $key => $value) {
                    $result = wp_get_attachment_url($key);
                    break;
                }
            }
            return $result;
        }

        public static function get_post_thumbnail($args = array()) {
            $size_name = 'thumbnail';
            $size = array();
            $post_id = get_the_ID();
            $width = '';
            $height = '';
            $style = '';
            $defaults = array(
                'size'		=> array(),
                'size_name'	=> 'thumbnail'
            );
            $args = wp_parse_args($args, $defaults);
            extract($args, EXTR_OVERWRITE);
            if($size && !is_array($size)) {
                $size = array($size, $size);
            }

            if(count($size) == 2) {
                $width = $size[0];
                $height = $size[1];
                $style = ' style="width:'.$width.'px; height:'.$height.'px;"';
            }

            if(has_post_thumbnail()) {
                $image_path = get_attached_file(get_post_thumbnail_id($post_id));
                if(file_exists($image_path)) {
                    $result = self::get_post_thumbnail_url($post_id);
                }
            }
            if(empty($result)) {
                $result = apply_filters('sb_hocwp_blog_post_image', '');
            }
            if(empty($result)) {
                $result = self::get_first_post_image($post_id);
                echo $result;
            }
            if(empty($result)) {
                $options = get_option('sb_options');
                $result = isset($options['post_widget']['no_thumbnail']) ? $options['post_widget']['no_thumbnail'] : '';
            }

            if(empty($result)) {
                $result = SB_CORE_ADMIN_URL . '/images/no-thumbnail-grey-100.png';
            }

            if(!empty($result)) {
                $result = '<img class="no-thumbnail wp-post-image" alt="'.get_the_title($post_id).'" width="'.$width.'" height="'.$height.'" src="'.$result.'"'.$style.'>';
            }

            return apply_filters('sb_post_thumbnail', $result);
        }

        public static function get_post_thumbnail_url($post_id) {
            return wp_get_attachment_url( get_post_thumbnail_id($post_id) );
        }

        public static function the_post_thumbnail($args = array()) {
            ?>
            <div class="post-thumbnail">
                <a href="<?php echo get_permalink(get_the_ID()); ?>"><?php echo self::get_post_thumbnail($args); ?></a>
            </div>
            <?php
        }

        public static function deactivate_all_sb_plugin() {
            $sb_plugins = array(
                'sb-clean/sb-clean.php'
            );
            $activated_plugins = get_option('active_plugins');
            $activated_plugins = array_diff($activated_plugins, $sb_plugins);
            update_option('active_plugins', $activated_plugins);
            deactivate_plugins($sb_plugins);
        }

        public static function get_author_post_url() {
            return get_author_posts_url( get_the_author_meta( 'ID' ) );
        }

        public static function the_post_author() {
            printf( '<span class="post-author"><i class="fa fa-user"></i> <span class="author vcard"><a class="url fn n" href="%1$s" rel="author">%2$s</a></span></span>',
                esc_url( sb_get_author_post_url() ),
                get_the_author_meta('user_nicename')
            );
        }

        public static function the_post_date() {
            printf( '<span class="date"><span>%1$s</span></span>',
                esc_html( get_the_date() )
            );
        }

        public static function the_post_comment_link() {
            if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) : ?>
                <span class="comments-link post-comment"><i class="fa fa-comments"></i> <?php comments_popup_link( '<span class="count">0</span> <span class="text">'.__('comment', 'sb-core').'</span>', '<span class="count">1</span> <span class="text">'.__('comment', 'sb-core')."</span>", '<span class="count">%</span> <span class="text">'.__('comments', 'sb-core')."</span>" ); ?></span>
            <?php endif;
        }

        public static function get_all_taxonomy() {
            return get_taxonomies('', 'objects');
        }

        public static function get_all_taxonomy_hierarchical() {
            $taxs = self::get_all_taxonomy();
            $kq = array();
            foreach($taxs as $tax) {
                if(empty($tax->hierarchical) || !$tax->hierarchical) continue;
                array_push($kq, $tax);
            }
            return $kq;
        }

        public static function get_parent_terms($taxonomy) {
            $args = array(
                'parent' => 0
            );
            $terms = get_terms($taxonomy, $args);
            return $terms;
        }

        public static function get_parent_categories() {
            return self::get_parent_terms("category");
        }

        public static function get_post_terms($post_id, $taxonomy) {
            return wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));
        }

        public static function get_post_categories($post_id) {
            return self::get_post_terms($post_id, 'category');
        }

        public static function redirect_home() {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . home_url('/'));
            exit();
        }

        public static function the_archive_title() {
            if ( is_category() ) :
                single_cat_title();

            elseif ( is_tag() ) :
                single_tag_title();

            elseif ( is_author() ) :
                printf( __( 'Author: %s', 'sb-core' ), '<span class="vcard">' . get_the_author() . '</span>' );

            elseif ( is_day() ) :
                printf( __( 'Day: %s', 'sb-core' ), '<span>' . get_the_date() . '</span>' );

            elseif ( is_month() ) :
                printf( __( 'Month: %s', 'sb-core' ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'sb-core' ) ) . '</span>' );

            elseif ( is_year() ) :
                printf( __( 'Year: %s', 'sb-core' ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'sb-core' ) ) . '</span>' );

            elseif ( is_tax( 'post_format', 'post-format-aside' ) ) :
                _e( 'Asides', 'sb-core' );

            elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) :
                _e( 'Galleries', 'sb-core' );

            elseif ( is_tax( 'post_format', 'post-format-image' ) ) :
                _e( 'Images', 'sb-core' );

            elseif ( is_tax( 'post_format', 'post-format-video' ) ) :
                _e( 'Videos', 'sb-core' );

            elseif ( is_tax( 'post_format', 'post-format-quote' ) ) :
                _e( 'Quotes', 'sb-core' );

            elseif ( is_tax( 'post_format', 'post-format-link' ) ) :
                _e( 'Links', 'sb-core' );

            elseif ( is_tax( 'post_format', 'post-format-status' ) ) :
                _e( 'Statuses', 'sb-core' );

            elseif ( is_tax( 'post_format', 'post-format-audio' ) ) :
                _e( 'Audios', 'sb-core' );

            elseif ( is_tax( 'post_format', 'post-format-chat' ) ) :
                _e( 'Chats', 'sb-core' );

            else :
                _e( 'Archives', 'sb-core' );

            endif;
        }

        public static function sanitize($data, $type) {
            switch($type) {
                case "url":
                    $data = esc_url_raw($data);
                    if(!SB_PHP::is_valid_url($data) || !SB_PHP::is_valid_image($data)) {
                        $data = '';
                    }
                    return $data;
                default:
                    return $data;
            }
        }
    }
}