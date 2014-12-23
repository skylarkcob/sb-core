<?php
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
        return admin_url('admin-ajax.php');
    }

    public static function get_all_shortcodes() {
        global $shortcode_tags;
        return $shortcode_tags;
    }

    public static function get_all_sb_shortcodes() {
        $shortcodes = self::get_all_shortcodes();
        $result = array();
        foreach($shortcodes as $key => $function) {
            if('sb' == substr($key, 0, 2) && 'sb' == substr($function, 0, 2)) {
                $result[$key] = $function;
            }
        }
        return $result;
    }

    public static function get_default_theme() {
        $themes = wp_get_themes();
        $wp_theme = '';
        foreach($themes as $theme) {
            $author_uri = $theme->get('AuthorURI');
            if(strrpos($author_uri, 'wordpress.org') !== false) {
                $wp_theme = $theme;
                break;
            }
        }
        if(empty($wp_theme)) {
            foreach($themes as $theme) {
                $text_domain = $theme->get('TextDomain');
                if(strrpos($text_domain, 'sb-theme') === false) {
                    $wp_theme = $theme;
                    break;
                }
            }
        }
        return $wp_theme;
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

    public static function is_error($thing) {
        if($thing && !is_wp_error($thing)) {
            return false;
        }
        return true;
    }

    public static function format_price($args = array()) {
        $suffix = '₫';
        $prefix = '';
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

    public static function verify_nonce($nonce_key, $post_key) {
        $nonce = isset($_POST[$post_key]) ? $_POST[$post_key] : '';
        if(!wp_verify_nonce($nonce, $nonce_key)) {
            return false;
        }
        return true;
    }

    public static function enqueue_script($handle, $url) {
        wp_enqueue_script($handle, $url, array('jquery'), false, true);
    }

    public static function nonce() {
        wp_nonce_field('sb_nonce', 'sb_nonce_field');
    }

    public static function verify_sb_nonce() {
        return self::verify_nonce('sb_nonce', 'sb_nonce_field');
    }

    public static function get_human_time_diff_info($from, $to = '') {
        if(empty($to)) {
            $to = self::current_time_stamp();
        }
        $diff = (int)abs($to - $from);
        if($diff < MINUTE_IN_SECONDS) {
            $seconds = round($diff);
            if($seconds < 1) {
                $seconds = 1;
            }
            $since["type"] = "second";
            $since["value"] = $seconds;
        } elseif($diff < HOUR_IN_SECONDS) {
            $mins = round($diff / MINUTE_IN_SECONDS);
            if ( $mins <= 1 ) {
                $mins = 1;
            }
            $since['type'] = 'minute';
            $since['value'] = $mins;
        } elseif ( $diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS ) {
            $hours = round( $diff / HOUR_IN_SECONDS );
            if ( $hours <= 1 ) {
                $hours = 1;
            }
            $since['type'] = 'hour';
            $since['value'] = $hours;
        } elseif ( $diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS ) {
            $days = round( $diff / DAY_IN_SECONDS );
            if ( $days <= 1 ) {
                $days = 1;
            }
            $since['type'] = 'day';
            $since['value'] = $days;
        } elseif ( $diff < 30 * DAY_IN_SECONDS && $diff >= WEEK_IN_SECONDS ) {
            $weeks = round( $diff / WEEK_IN_SECONDS );
            if ( $weeks <= 1 ) {
                $weeks = 1;
            }
            $since['type'] = 'week';
            $since['value'] = $weeks;
        } elseif ( $diff < YEAR_IN_SECONDS && $diff >= 30 * DAY_IN_SECONDS ) {
            $months = round( $diff / ( 30 * DAY_IN_SECONDS ) );
            if ( $months <= 1 ) {
                $months = 1;
            }
            $since['type'] = 'month';
            $since['value'] = $months;
        } elseif ( $diff >= YEAR_IN_SECONDS ) {
            $years = round( $diff / YEAR_IN_SECONDS );
            if ( $years <= 1 ) {
                $years = 1;
            }
            $since['type'] = 'year';
            $since['value'] = $years;
        }
        return $since;
    }

    public static function date_time_ago($date) {
        $chunks = array(
            array( 60 * 60 * 24 * 365 , __( 'năm', 'sb-core' ), __( 'năm', 'sb-core' ) ),
            array( 60 * 60 * 24 * 30 , __( 'tháng', 'sb-core' ), __( 'tháng', 'sb-core' ) ),
            array( 60 * 60 * 24 * 7, __( 'tuần', 'sb-core' ), __( 'tuần', 'sb-core' ) ),
            array( 60 * 60 * 24 , __( 'ngày', 'sb-core' ), __( 'ngày', 'sb-core' ) ),
            array( 60 * 60 , __( 'giờ', 'sb-core' ), __( 'giờ', 'sb-core' ) ),
            array( 60 , __( 'phút', 'sb-core' ), __( 'phút', 'sb-core' ) ),
            array( 1, __( 'giây', 'sb-core' ), __( 'giây', 'sb-core' ) )
        );
        if ( !is_numeric( $date ) ) {
            $time_chunks = explode( ':', str_replace( ' ', ':', $date ) );
            $date_chunks = explode( '-', str_replace( ' ', '-', $date ) );
            $date = gmmktime( (int)$time_chunks[1], (int)$time_chunks[2], (int)$time_chunks[3], (int)$date_chunks[1], (int)$date_chunks[2], (int)$date_chunks[0] );
        }
        $current_time = current_time( 'mysql', $gmt = 0 );
        $newer_date = strtotime( $current_time );
        $since = $newer_date - $date;
        if ( 0 > $since )
            return __( 'Vài giây trước', 'sb-core' );
        for ( $i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];
            if ( ( $count = floor($since / $seconds) ) != 0 )
                break;
        }
        $first_chunk = isset($chunks[$i][1]) ? $chunks[$i][1] : '';
        $second_chunk = isset($chunks[$i][2]) ? $chunks[$i][2] : '';
        $output = ( 1 == $count ) ? '1 '. $first_chunk : $count . ' ' . $second_chunk;
        if ( !(int)trim($output) ){
            $output = __( 'Vài giây', 'sb-core' );
        }
        $output .= ' ' . __('trước', 'sb-core');
        return $output;
    }

    public static function get_human_time_diff( $from, $to = '' ) {
        $time_diff = self::get_human_time_diff_info($from, $to);
        $type = $time_diff['type'];
        $value = $time_diff['value'];
        switch($type) {
            case 'second':
                $phrase = sprintf(__('%d giây trước', 'sb-core'), $value);
                $phrase_many = sprintf(__('%d giây trước', 'sb-core'), $value);
                break;
            case 'minute':
                $phrase = sprintf(__('%d phút trước', 'sb-core'), $value);
                $phrase_many = sprintf(__('%d phút trước', 'sb-core'), $value);
                break;
            case 'hour':
                $phrase = sprintf(__('%d giờ trước', 'sb-core'), $value);
                $phrase_many = sprintf(__('%d giờ trước', 'sb-core'), $value);
                break;
            case 'day':
                $phrase = sprintf(__('%d ngày trước', 'sb-core'), $value);
                $phrase_many = sprintf(__('%d ngày trước', 'sb-core'), $value);
                break;
            case 'week':
                $phrase = sprintf(__('%d tuần trước', 'sb-core'), $value);
                $phrase_many = sprintf(__('%d tuần trước', 'sb-core'), $value);
                break;
            case 'month':
                $phrase = sprintf(__('%d tháng trước', 'sb-core'), $value);
                $phrase_many = sprintf(__('%d tháng trước', 'sb-core'), $value);
                break;
            case 'year':
                $phrase = sprintf(__('%d năm trước', 'sb-core'), $value);
                $phrase_many = sprintf(__('%d năm trước', 'sb-core'), $value);
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
        $type = $diff['type'];
        $value = $diff['value'];
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
        $id = 'message';
        $message = '';
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

    public static function get_menu_location() {
        return get_nav_menu_locations();
    }

    public static function get_menu($args = array()) {
        return wp_get_nav_menus($args);
    }

    public static function change_url($args = array()) {
        $home_url = '';
        $site_url = '';
        extract($args, EXTR_OVERWRITE);
        if(empty($home_url)) {
            $home_url = $site_url;
        }
        if(empty($site_url)) {
            $site_url = $home_url;
        }
        if(empty($site_url) && empty($home_url)) {
            return;
        }
        $url = esc_url(untrailingslashit(SB_Option::get_site_url()));
        $site_url = esc_url(untrailingslashit($site_url));
        if($url != $site_url) {
            update_option('siteurl', $site_url);
            $args = array('url' => $url, 'site_url' => $site_url);
            SB_Post::change_custom_menu_url($args);
            SB_Option::change_option_url($args);
            SB_Option::change_widget_text_url($args);
            SB_Term::change_meta_url($url, $site_url);
            add_action('wp_head', array('SB_Core', 'regenerate_htaccess_file'));
            SB_Post::change_all_url(array('url' => $url, 'site_url' => $site_url));
        } else {
            remove_action('wp_head', array('SB_Core', 'regenerate_htaccess_file'));
        }
        $url = esc_url(untrailingslashit(SB_Option::get_home_url()));
        $home_url = esc_url(untrailingslashit($home_url));
        if($url != $home_url) {
            update_option('home', $home_url);
        }
    }

    public static function get_menu_by_location($location) {
        $locations = self::get_menu_location();
        $menu = null;
        if(isset($locations[$location])) {
            $menu = wp_get_nav_menu_object($locations[$location]);
        }
        return $menu;
    }

    public static function get_current_url() {
        global $wp;
        $current_url = trailingslashit(home_url($wp->request));
        return $current_url;
    }

    public static function get_menu_items($menu, $args = array()) {
        return wp_get_nav_menu_items($menu, $args);
    }

    public static function get_menu_items_by_location($location, $args = array()) {
        $menu = self::get_menu_by_location($location);
        $items = array();
        if($menu) {
            $items = self::get_menu_items($menu->term_id, $args);
        }
        return $items;
    }

    public static function regenerate_htaccess_file() {
        if(!function_exists('save_mod_rewrite_rules')) {
            if(!function_exists('mysql2date')) {
                require ABSPATH . '/wp-includes/functions.php';
            }
            if(!function_exists('get_home_path')) {
                require ABSPATH . '/wp-admin/includes/file.php';
            }
            require ABSPATH . '/wp-admin/includes/misc.php';
        }
        global $is_nginx, $wp_rewrite;
        $home_path = get_home_path();
        $htaccess_file = $home_path.'.htaccess';
        if(file_exists($htaccess_file)) {
            unlink($htaccess_file);
        }
        $home_path = get_home_path();
        $iis7_permalinks = iis7_supports_permalinks();

        $prefix = $blog_prefix = '';
        if ( ! got_url_rewrite() )
            $prefix = '/index.php';
        if ( is_multisite() && !is_subdomain_install() && is_main_site() )
            $blog_prefix = '/blog';
        $permalink_structure = get_option( 'permalink_structure' );
        $category_base       = get_option( 'category_base' );
        $tag_base            = get_option( 'tag_base' );
        $update_required     = false;

        if ( $iis7_permalinks ) {
            if ( ( ! file_exists($home_path . 'web.config') && win_is_writable($home_path) ) || win_is_writable($home_path . 'web.config') )
                $writable = true;
            else
                $writable = false;
        } elseif ( $is_nginx ) {
            $writable = false;
        } else {
            if ( ( ! file_exists( $home_path . '.htaccess' ) && is_writable( $home_path ) ) || is_writable( $home_path . '.htaccess' ) ) {
                $writable = true;
            } else {
                $writable = false;
                $existing_rules  = array_filter( extract_from_markers( $home_path . '.htaccess', 'WordPress' ) );
                $new_rules       = array_filter( explode( "\n", $wp_rewrite->mod_rewrite_rules() ) );
                $update_required = ( $new_rules !== $existing_rules );
            }
        }

        if ( $wp_rewrite->using_index_permalinks() )
            $usingpi = true;
        else
            $usingpi = false;

        flush_rewrite_rules();
        save_mod_rewrite_rules();
    }

    public static function get_current_date_time($format = SB_DATE_TIME_FORMAT) {
        return SB_PHP::get_current_date_time(SB_DATE_TIME_FORMAT, SB_Option::get_timezone_string());
    }

    public static function get_request() {
        $request = remove_query_arg( 'paged' );
        $home_root = parse_url(home_url());
        $home_root = ( isset($home_root['path']) ) ? $home_root['path'] : '';
        $home_root = preg_quote( $home_root, '|' );
        $request = preg_replace('|^'. $home_root . '|i', '', $request);
        $request = preg_replace('|^/+|', '', $request);
        return $request;
    }

    public static function get_pagenum_link( $args = array() ) {
        $pagenum = 1;
        $escape = true;
        $request = self::get_request();
        extract($args, EXTR_OVERWRITE);
        if (!is_admin()) {
            return get_pagenum_link($pagenum, $escape);
        } else {
            global $wp_rewrite;
            $pagenum = (int) $pagenum;
            if ( !$wp_rewrite->using_permalinks() ) {
                $base = trailingslashit( get_bloginfo( 'url' ) );

                if ( $pagenum > 1 ) {
                    $result = add_query_arg( 'paged', $pagenum, $base . $request );
                } else {
                    $result = $base . $request;
                }
            } else {
                $qs_regex = '|\?.*?$|';
                preg_match( $qs_regex, $request, $qs_match );

                if ( !empty( $qs_match[0] ) ) {
                    $query_string = $qs_match[0];
                    $request = preg_replace( $qs_regex, '', $request );
                } else {
                    $query_string = '';
                }

                $request = preg_replace( "|$wp_rewrite->pagination_base/\d+/?$|", '', $request);
                $request = preg_replace( '|^' . preg_quote( $wp_rewrite->index, '|' ) . '|i', '', $request);
                $request = ltrim($request, '/');

                $base = trailingslashit( get_bloginfo( 'url' ) );

                if ( $wp_rewrite->using_index_permalinks() && ( $pagenum > 1 || '' != $request ) )
                    $base .= $wp_rewrite->index . '/';

                if ( $pagenum > 1 ) {
                    $request = ( ( !empty( $request ) ) ? trailingslashit( $request ) : $request ) . user_trailingslashit( $wp_rewrite->pagination_base . "/" . $pagenum, 'paged' );
                }

                $result = $base . $request . $query_string;
            }

            $result = apply_filters( 'get_pagenum_link', $result );

            if ( $escape )
                return esc_url( $result );
            else
                return esc_url_raw( $result );
        }
    }

    public static function set_default_timezone() {
        date_default_timezone_set(SB_Option::get_timezone_string());
    }

    public static function get_current_datetime() {
        return self::get_current_date_time();
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

    public static function insert_attachment($attachment, $file_path, $parent_post_id = 0) {
        if(!file_exists($file_path)) {
            return 0;
        }
        $file_type = wp_check_filetype(basename($file_path), null);
        $attachment['post_mime_type'] = $file_type['type'];
        if(!isset($attachment['guid'])) {
            return 0;
        }
        $attachment['post_status'] = isset($attachment['post_status']) ? $attachment['post_status'] : 'inherit';
        if(!isset($attachment['post_title'])) {
            $attachment['post_title'] = preg_replace('/\.[^.]+$/', '', basename($file_path));
        }
        $attach_id = wp_insert_attachment($attachment, $file_path, $parent_post_id);
        if($attach_id > 0) {
            self::update_attachment_meta($attach_id, $file_path);
            if($parent_post_id > 0) {
                SB_Post::set_thumbnail($parent_post_id, $attach_id);
            }
        }
        return $attach_id;
    }

    public static function update_attachment_meta($attach_id, $file_path) {
        if(!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);
    }

    public static function fetch_media($image_url) {
        $attach_id = 0;
        if(empty($image_url)) {
            return $attach_id;
        }
        $wp_upload_dir = wp_upload_dir();
        $base_dir = trailingslashit($wp_upload_dir['basedir']) . 'sb-media';
        $base_url = trailingslashit($wp_upload_dir['url']) . 'sb-media';
        SB_PHP::create_folder($base_dir);
        $parts = pathinfo($image_url);
        $random = rand();
        $random = md5($random);
        $file_name = 'sb-media-' . $parts['filename'] . '-' . $random . '.' . $parts['extension'];
        $file_path = trailingslashit($base_dir) . $file_name;
        $file_url = trailingslashit($base_url) . $file_name;
        if(SB_PHP::copy($image_url, $file_path)) {
            $attachment = array(
                'guid' => $file_url
            );
            $attach_id = self::insert_attachment($attachment, $file_path);
        }
        return $attach_id;
    }

    public static function switch_theme($name) {
        switch_theme($name);
    }

    public static function sanitize($data, $type) {
        switch($type) {
            case 'url':
                $data = trim($data);
                $data = esc_url_raw($data);
                if(!SB_PHP::is_valid_url($data)) {
                    $data = '';
                }
                return $data;
            case 'image_url':
                $data = trim($data);
                if(!SB_PHP::is_valid_image($data)) {
                    $data = '';
                }
                return $data;
            case 'text':
                $data = trim($data);
                $data = sanitize_text_field($data);
                return $data;
            case 'checkbox':
                if(is_numeric($data) && $data != 0) {
                    $data = 1;
                } else {
                    $data = 0;
                }
                return $data;
            default:
                return $data;
        }
    }

    public static function get_visitor_ip() {
        $result = '';
        if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $result = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $result = $_SERVER['REMOTE_ADDR'];
        }
        $result = apply_filters('wpb_get_ip', $result);
        return $result;
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
        $options = SB_Option::get();
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

    public static function get_language_text($en_text, $vi_text) {
        $lang = SB_PHP::get_session('current_language');
        $result = '';
        switch($lang) {
            case 'vi':
                $result = $vi_text;
                break;
            case 'en':
                $result = $en_text;
                break;
        }
        return $result;
    }

    public static function the_language_text($en_text, $vi_text) {
        echo self::get_language_text($en_text, $vi_text);
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
        $cats = get_categories(array('hide_empty' => 0, 'parent' => $cat_id));
        if($cats) {
            return true;
        }
        return false;
    }

    public static function widget_area($args = array()) {
        $class = '';
        $id = '';
        $location = '';
        extract($args, EXTR_OVERWRITE);
        $class = trim('sb-widget-area ' . $class);
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
        if(class_exists('WP_Widget_PostViews')) {
            return true;
        }
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = %s", 'views');
        $views = SB_Query::get_results($query);
        if(count($views) > 0) {
            return true;
        }
        return false;
    }

    public static function is_support_post_favorites() {
        $users = SB_User::get_by_meta('favorites');
        if(!is_array($users) || count($users) < 1) {
            return false;
        }
        return true;
    }

    public static function is_support_post_likes() {
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = %s", 'likes');
        $likes = SB_Query::get_results($query);
        if(count($likes) > 0) {
            return true;
        }
        return false;
    }

    public static function build_widget_class($widget_id) {
        $widget_class = explode('-', $widget_id);
        array_pop($widget_class);
        if(is_array($widget_class)) {
            $widget_class = implode('-', $widget_class);
        } else {
            $widget_class = (string) $widget_class;
        }
        $widget_class = trim(trim(trim($widget_class, '_'), '-'));
        $widget_class = 'widget_' . $widget_class;
        return $widget_class;
    }

    public static function get_sidebar() {
        global $wp_registered_sidebars;
        return $wp_registered_sidebars;
    }

    public static function get_sidebar_by($key, $value) {
        $sidebars = self::get_sidebar();
        foreach ($sidebars as $id => $sidebar) {
            switch ($key) {
                default:
                    if ($id == $value) return $sidebar;
            }
        }
        return array();
    }

    public static function build_meta_box_field_name($name) {
        $name = str_replace('sbmb_', '', $name);
        return 'sbmb_' . $name;
    }

    public static function register_sidebar($sidebar_id, $sidebar_name, $sidebar_description) {
        if(!self::is_sidebar_exists($sidebar_id)) {
            register_sidebar( array(
                'name'          => $sidebar_name,
                'id'            => $sidebar_id,
                'description'   => __($sidebar_description, 'sb-theme'),
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h4 class="widget-title">',
                'after_title'   => '</h4>',
            ));
        }
    }

    public static function is_sidebar_exists($sidebar_id) {
        global $wp_registered_sidebars;
        return array_key_exists($sidebar_id, $wp_registered_sidebars);
    }

    public static function set_dashboard_language($lang) {
        global $sb_dashboard_language;
        $sb_dashboard_language = apply_filters('sb_dashboard_language', $lang);
    }

    public static function get_dashboard_language() {
        global $sb_dashboard_language;
        if(empty($sb_dashboard_language)) {
            $sb_dashboard_language = 'en';
        }
        return $sb_dashboard_language;
    }

    public static function english_dashboard($locale) {
        if(is_admin()) {
            $locale = 'en';
        }
        return $locale;
    }
    
    public static function register_post_type($args = array()) {
        $name = '';
        $singular_name = '';
        $supports = array();
        $hierarchical = false;
        $public = true;
        $show_ui = true;
        $show_in_menu = true;
        $show_in_nav_menus = false;
        $show_in_admin_bar = true;
        $menu_position = 6;
        $can_export = true;
        $has_archive = true;
        $exclude_from_search = false;
        $publicly_queryable = true;
        $capability_type = 'post';
        $taxonomies = array();
        $menu_icon = 'dashicons-admin-post';
        $slug = '';
        $with_front = true;
        $pages = true;
        $feeds = true;
        $query_var = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        if(empty($singular_name)) {
            $singular_name = $name;
        }
        if(empty($name) || !is_array($supports) || empty($slug) || post_type_exists($slug)) {
            return;
        }
        if(!in_array('title', $supports)) {
            array_push($supports, 'title');
        }
        $labels = array(
            'name'                => $name,
            'singular_name'       => $singular_name,
            'menu_name'           => $name,
            'parent_item' => sprintf(__( 'Parent %s', 'sb-core' ), $singular_name),
            'parent_item_colon'   => sprintf(__( 'Parent %s:', 'sb-core' ), $singular_name),
            'all_items'           => sprintf(__( 'All %s', 'sb-core' ), $name),
            'view_item'           => sprintf(__( 'View %s', 'sb-core' ), $singular_name),
            'add_new_item'        => sprintf(__( 'Add New %s', 'sb-core' ), $singular_name),
            'add_new'             => __( 'Add New', 'sb-core' ),
            'edit_item'           => sprintf(__( 'Edit %s', 'sb-core' ), $singular_name),
            'update_item'         => sprintf(__( 'Update %s', 'sb-core' ), $singular_name),
            'search_items'        => sprintf(__( 'Search %s', 'sb-core' ), $singular_name) ,
            'not_found'           => __( 'Not found', 'sb-core' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'sb-core' )
        );
        $rewrite = array(
            'slug'                => $slug,
            'with_front'          => $with_front,
            'pages'               => $pages,
            'feeds'               => $feeds
        );
        unset($args);
        $args = array(
            'labels'              => $labels,
            'supports'            => $supports,
            'taxonomies' => $taxonomies,
            'hierarchical'        => $hierarchical,
            'public'              => $public,
            'show_ui'             => $show_ui,
            'show_in_menu'        => $show_in_menu,
            'show_in_nav_menus'   => $show_in_nav_menus,
            'show_in_admin_bar'   => $show_in_admin_bar,
            'menu_position'       => $menu_position,
            'menu_icon' => $menu_icon,
            'can_export'          => $can_export,
            'has_archive'         => $has_archive,
            'exclude_from_search' => $exclude_from_search,
            'publicly_queryable'  => $publicly_queryable,
            'query_var' => $query_var,
            'rewrite' => $rewrite,
            'capability_type'     => $capability_type
        );
        register_post_type($slug, $args);
    }

    public static function wp_postviews_activated() {
        if(function_exists('process_postviews') || function_exists('postviews_menu') || function_exists('the_views')) {
            return true;
        }
        return false;
    }

    public static function get_social_share_url($args = array()) {
        $result = '';
        $title = get_the_title();
        $permalink = get_the_permalink();
        $url = $permalink;
        $social_name = '';
        $thumbnail = '';
        $excerpt = get_the_excerpt();
        $language = SB_Option::get_default_language();
        $twitter_account = 'skylarkcob';
        extract($args, EXTR_OVERWRITE);
        $permalink = urlencode($permalink);
        if(empty($twitter_account)) {
            $twitter_account = SB_Option::get_theme_social('twitter');
            $twitter_account = basename($twitter_account);
        }
        switch($social_name) {
            case 'email':
                $result = 'mailto:email@hocwp.net?subject=' . $title . '&amp;body=' . $permalink;
                break;
            case 'facebook':
                $url = 'https://www.facebook.com/sharer/sharer.php';
                $url = add_query_arg('u', $permalink, $url);
                if(!empty($title)) {
                    $url = add_query_arg('t', $title, $url);
                }
                $result = $url;
                break;
            case 'googleplus':
                $url = 'http://plusone.google.com/_/+1/confirm';
                $url = add_query_arg('hl', $language, $url);
                $url = add_query_arg('url', $permalink, $url);
                $result = $url;
                break;
            case 'twitter':
                $url = 'http://twitter.com/share';
                $url = add_query_arg('url', $permalink, $url);
                if(!empty($title)) {
                    $url = add_query_arg('text', $title, $url);
                }
                $url = add_query_arg('via', $twitter_account, $url);
                $result = $url;
                break;
            case 'pinterest':
                $url = 'http://www.pinterest.com/pin/create/button';
                if(!empty($thumbnail)) {
                    $url = add_query_arg('media', $thumbnail, $url);
                }
                $url = add_query_arg('url', $permalink, $url);
                if(!empty($title)) {
                    $url = add_query_arg('description', $title . ' ' . $permalink, $url);
                }
                $result = $url;
                break;
            case 'zingme':
                $url = 'http://link.apps.zing.vn/share';
                if(!empty($title)) {
                    $url = add_query_arg('t', $title, $url);
                }
                $url = add_query_arg('u', $permalink, $url);
                if(!empty($excerpt)) {
                    $url = add_query_arg('desc', $excerpt, $url);
                }
                $result = $url;
                break;
        }
        return $result;
    }

    public static function register_taxonomy($args = array()) {
        $name = '';
        $singular_name = '';
        $hierarchical = true;
        $public = true;
        $show_ui = true;
        $show_admin_column = true;
        $show_in_nav_menus = false;
        $show_tagcloud = true;
        $post_types = array();
        $slug = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        if(empty($singular_name)) {
            $singular_name = $name;
        }
        if(empty($name) || empty($slug) || taxonomy_exists($slug)) {
            return;
        }
        $labels = array(
            'name'                       => $name,
            'singular_name'              => $singular_name,
            'menu_name'                  => $name,
            'all_items'                  => sprintf(__( 'All %s', 'sb-core' ), $name),
            'parent_item'                => sprintf(__( 'Parent %s', 'sb-core' ), $singular_name),
            'parent_item_colon'          => sprintf(__( 'Parent %s:', 'sb-core' ), $singular_name),
            'new_item_name'              => sprintf(__( 'New %s Name', 'sb-core' ), $singular_name),
            'add_new_item'               => sprintf(__( 'Add New %s', 'sb-core' ), $singular_name),
            'edit_item'                  => sprintf(__( 'Edit %s', 'sb-core' ), $singular_name),
            'update_item'                => sprintf(__( 'Update %s', 'sb-core' ), $singular_name),
            'separate_items_with_commas' => sprintf(__( 'Separate %s with commas', 'sb-core' ), SB_PHP::lowercase($name)),
            'search_items'               => sprintf(__( 'Search %s', 'sb-core' ), $name),
            'add_or_remove_items'        => sprintf(__( 'Add or remove %s', 'sb-core' ), $name),
            'choose_from_most_used'      => sprintf(__( 'Choose from the most used %s', 'sb-core' ), $name),
            'not_found'                  => __( 'Not Found', 'sb-core' ),
        );
        unset($args);
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => $hierarchical,
            'public'                     => $public,
            'show_ui'                    => $show_ui,
            'show_admin_column'          => $show_admin_column,
            'show_in_nav_menus'          => $show_in_nav_menus,
            'show_tagcloud'              => $show_tagcloud,
            'query_var' => true,
            'rewrite' => array('slug' => $slug)
        );
        register_taxonomy($slug, $post_types, $args);
    }
}