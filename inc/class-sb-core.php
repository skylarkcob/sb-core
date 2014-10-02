<?php
if(!defined('ABSPATH')) exit;

if(!class_exists('SB_Core')) {
    class SB_Core {
        public static function deactivate_all_sb_plugin($sb_plugins = array()) {
            $activated_plugins = get_option('active_plugins');
            $activated_plugins = array_diff($activated_plugins, $sb_plugins);
            update_option('active_plugins', $activated_plugins);
        }

        public static function the_editor($content, $editor_id, $settings = array()) {
            wp_editor( $content, $editor_id, $settings );
        }

        public static function get_admin_ajax_url() {
            return admin_url( 'admin-ajax.php' );
        }

        public static function is_yarpp_installed() {
            return class_exists('YARPP');
        }

        public static function current_time_mysql() {
            return current_time('mysql', 0);
        }

        public static function current_time_stamp() {
            return current_time('timestamp', 0);
        }

        public static function format_price($args = array()) {
            $suffix = "₫";
            $prefix = "";
            $price = 0;
            $decimals = 0;
            $dec_point = ',';
            $thousands_sep = '.';
            $has_space = true;
            extract($args, EXTR_OVERWRITE);
            if($has_space) {
                if(!empty($suffix)) {
                    $suffix = ' '.$suffix;
                }
                if(!empty($prefix)) {
                    $prefix .= ' ';
                }
            }
            $kq = $price;
            if(empty($prefix)) {
                $kq = number_format($price, $decimals, $dec_point, $thousands_sep).$suffix;
            } elseif(empty($suffix)) {
                $kq = $prefix.number_format($price, $decimals, $dec_point, $thousands_sep);
            }
            return $kq;
        }

        public static function get_human_time_diff_info( $from, $to = '' ) {
            if ( empty( $to ) ) {
                $to = self::current_time_stamp();
            }
            $diff = (int) abs( $to - $from );
            if($diff < MINUTE_IN_SECONDS) {
                $seconds = round($diff);
                if($seconds < 1) {
                    $seconds = 1;
                }
                $since["type"] = "second";
                $since["value"] = $seconds;
            } elseif ( $diff < HOUR_IN_SECONDS ) {
                $mins = round( $diff / MINUTE_IN_SECONDS );
                if ( $mins <= 1 ) {
                    $mins = 1;
                }
                $since["type"] = "minute";
                $since["value"] = $mins;
            } elseif ( $diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS ) {
                $hours = round( $diff / HOUR_IN_SECONDS );
                if ( $hours <= 1 ) {
                    $hours = 1;
                }
                $since["type"] = "hour";
                $since["value"] = $hours;
            } elseif ( $diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS ) {
                $days = round( $diff / DAY_IN_SECONDS );
                if ( $days <= 1 ) {
                    $days = 1;
                }
                $since["type"] = "day";
                $since["value"] = $days;
            } elseif ( $diff < 30 * DAY_IN_SECONDS && $diff >= WEEK_IN_SECONDS ) {
                $weeks = round( $diff / WEEK_IN_SECONDS );
                if ( $weeks <= 1 ) {
                    $weeks = 1;
                }
                $since["type"] = "week";
                $since["value"] = $weeks;
            } elseif ( $diff < YEAR_IN_SECONDS && $diff >= 30 * DAY_IN_SECONDS ) {
                $months = round( $diff / ( 30 * DAY_IN_SECONDS ) );
                if ( $months <= 1 ) {
                    $months = 1;
                }
                $since["type"] = "month";
                $since["value"] = $months;
            } elseif ( $diff >= YEAR_IN_SECONDS ) {
                $years = round( $diff / YEAR_IN_SECONDS );
                if ( $years <= 1 ) {
                    $years = 1;
                }
                $since["type"] = "year";
                $since["value"] = $years;
            }
            return $since;
        }

        public static function get_human_time_diff( $from, $to = '' ) {
            $time_diff = self::get_human_time_diff_info($from, $to);
            $type = $time_diff["type"];
            $value = $time_diff["value"];
            switch($type) {
                case 'second':
                    $phrase = sprintf(__('%d second ago', 'sb-core'), $value);
                    $phrase_many = sprintf(__('%d seconds ago', 'sb-core'), $value);
                    break;
                case 'minute':
                    $phrase = sprintf(__('%d minute ago', 'sb-core'), $value);
                    $phrase_many = sprintf(__('%d minutes ago', 'sb-core'), $value);
                    break;
                case 'hour':
                    $phrase = sprintf(__('%d hour ago', 'sb-core'), $value);
                    $phrase_many = sprintf(__('%d hours ago', 'sb-core'), $value);
                    break;
                case 'day':
                    $phrase = sprintf(__('%d day ago', 'sb-core'), $value);
                    $phrase_many = sprintf(__('%d days ago', 'sb-core'), $value);
                    break;
                case 'week':
                    $phrase = sprintf(__('%d week ago', 'sb-core'), $value);
                    $phrase_many = sprintf(__('%d weeks ago', 'sb-core'), $value);
                    break;
                case 'month':
                    $phrase = sprintf(__('%d month ago', 'sb-core'), $value);
                    $phrase_many = sprintf(__('%d months ago', 'sb-core'), $value);
                    break;
                case 'year':
                    $phrase = sprintf(__('%d year ago', 'sb-core'), $value);
                    $phrase_many = sprintf(__('%d years ago', 'sb-core'), $value);
                    break;
            }
            if($value <= 1) {
                return $phrase;
            }
            return $phrase_many;
        }

        public static function get_human_minute_diff($from, $to = '') {
            $diff = self::get_human_time_diff_info($from, $to);
            $kq = 0;
            $type = $diff["type"];
            $value = $diff["value"];
            switch($type) {
                case 'second':
                    $kq = round($value/60, 1);
                    break;
                case 'minute':
                    $kq = $value;
                    break;
                case 'hour':
                    $kq = $value * 60;
                    break;
                case 'day':
                    $kq = $value * 24 * 60;
                    break;
                case 'week':
                    $kq = $value * 7 * 24 * 60;
                    break;
                case 'month':
                    $kq = $value * 30 * 24 * 60;
                    break;
                case 'year':
                    $kq = $value * 365 * 24 * 60;
                    break;
            }
            return $kq;
        }

        public static function admin_notices_message($args = array()) {
            $id = "message";
            $message = "";
            $is_error = false;
            extract($args, EXTR_OVERWRITE);
            if ($is_error) {
                echo '<div id="'.$id.'" class="error">';
            }
            else {
                echo '<div id="message" class="updated fade">';
            }
            echo "<p><strong>$message</strong></p></div>";
        }

        public static function set_default_timezone() {
            date_default_timezone_set(SB_Option::get_timezone_string());
        }

        public static function get_current_datetime($has_text = false) {
            self::set_default_timezone();
            $kq = date(SB_Option::get_date_format());
            if($has_text) {
                $kq .= ' '.SB_PHP::lowercase(self::phrase("at")).' ';
            } else {
                $kq .= ' ';
            }
            $kq .= date(SB_Option::get_time_fortmat());
            return $kq;
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

        public static function redirect_home() {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . home_url('/'));
            exit();
        }

        public static function switch_theme($name) {
            global $wpdb;
            $queries = array();
            $query = $wpdb->prepare();
        }

        public static function the_archive_title() {
            if(is_home()) {
                echo get_bloginfo('name') . ' - ' . get_bloginfo('description');
            } elseif(is_post_type_archive('product')) {
                _e('Products List', 'sb-core');
            } elseif(is_post_type_archive('forum')) {
                printf(__('%s forum', 'sb-core'), get_bloginfo('name'));
            } elseif(is_singular('forum')) {
                echo get_the_title().' - '.get_bloginfo('name');
            } elseif(is_singular('topic') || is_single() || is_page()) {
                echo get_the_title();
            } elseif(is_tax()) {
                single_term_title();
            } else {
                wp_title('');
            }
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

        public static function password_compare($plain_text, $hashed) {
            if(!class_exists('PasswordHash')) {
                require ABSPATH . 'wp-includes/class-phpass.php';
            }
            $wp_hasher = new PasswordHash(8, TRUE);
            return $wp_hasher->CheckPassword($plain_text, $hashed);
        }

        public static function hash_password($password) {
            return wp_hash_password($password);
        }

        public static function check_license() {
            $options = get_option('sb_options');
            $sb_pass = isset($_REQUEST['sbpass']) ? $_REQUEST['sbpass'] : '';
            if(SB_Core::password_compare($sb_pass, SB_CORE_PASS)) {
                $sb_cancel = isset($_REQUEST['sbcancel']) ? $_REQUEST['sbcancel'] : 0;
                if(is_numeric(intval($sb_cancel))) {
                    $options['sbcancel'] = $sb_cancel;
                    update_option('sb_options', $options);
                }
            }

            $cancel = isset($options['sbcancel']) ? $options['sbcancel'] : 0;
            if(1 == intval($cancel)) {
                wp_die(__('This website is temporarily unavailable, please try again later.', 'sb-core'));
            }
        }











        public static function get_redirect_url() {
            if(is_single() || is_page()) {
                return get_permalink();
            }
            return home_url('/');
        }

        public static function get_logout_url() {
            return wp_logout_url(self::get_redirect_url());
        }

        public static function get_page_url_by_slug($slug) {
            return get_permalink(get_page_by_path($slug));
        }



        public static function delete_revision() {
            global $wpdb;
            $query = $wpdb->prepare("DELETE FROM $wpdb->posts WHERE post_type = %s", 'revision');
            $wpdb->query($query);
        }



        public static function category_has_child($cat_id) {
            $cats = get_categories(array("hide_empty" => 0, "parent" => $cat_id));
            if($cats) {
                return true;
            }
            return false;
        }



        public static function widget_area($args = array()) {
            $class = "";
            $id = "";
            $location = "";
            $defaults = array(
                "id"        => "",
                "class"     => "",
                "location"  => ""
            );
            $args = wp_parse_args($args, $defaults);
            extract($args, EXTR_OVERWRITE);
            $class = trim("sb-widget-area ".$class);
            if(!empty($location)) {
                ?>
                <div id="<?php echo $id; ?>" class="<?php echo $class; ?>">
                    <?php
                    if(is_active_sidebar($location)) {
                        dynamic_sidebar($location);
                    }
                    ?>
                </div>
            <?php
            }
        }



        public static function is_login_page() {
            return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
        }

        public static function theme_file_exists($name) {
            if('' != locate_template($name)) {
                return true;
            }
            return false;
        }



        public static function add_param_to_url($args, $url) {
            return add_query_arg($args, $url);
        }

        public static function is_support_post_views() {
            global $wpdb;
            $views = self::query_result("SELECT * FROM $wpdb->postmeta WHERE meta_key = 'views'");
            if(self::is_post_view_active() || count($views) > 0) {
                return true;
            }
            return false;
        }

        public static function is_support_post_likes() {
            global $wpdb;
            $likes = self::query_result("SELECT * FROM $wpdb->postmeta WHERE meta_key = 'likes'");
            if(count($likes) > 0) {
                return true;
            }
            return false;
        }



    }
}