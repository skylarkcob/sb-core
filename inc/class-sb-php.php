<?php
class SB_PHP {
    public static function url_exists($url) {

    }

    public static function clean_url($url) {
        $url = self::lowercase($url);
        $url = str_replace(' ', '-', $url);
        $url = self::remove_vietnamese($url);
        $url = preg_replace('/[^A-Za-z0-9\-]/', '', $url);
        return preg_replace('/-+/', '-', $url);
    }

    public static function mysql_time_format() {
        return 'Y-m-d H:i:s';
    }

    public static function format_date($date, $format) {
        return date($format, strtotime($date));
    }

    public static function move_item_to_beginning_by_key($key, $arr) {
        $tmp = isset($arr[$key]) ? $arr[$key] : '';
        if($tmp) {
            unset($arr[$key]);
            array_unshift($arr, $tmp);
        }
        return $arr;
    }

    public static function delete_file($file_path) {
        $file_path = realpath($file_path);
        if(is_readable($file_path)) {
            unlink($file_path);
        }
    }

    public static function date_plus_minute($date, $minute) {
        $kq = new DateTime($date);
        $time_modify = '+' . $minute;
        if($minute > 1) {
            $time_modify .= ' minutes';
        } else {
            $time_modify .= ' minute';
        }
        $kq->modify($time_modify);
        return $kq->format(self::mysql_time_format());
    }

    public static function date_minus_minute($date1, $date2) {
        $date1 = new DateTime($date1);
        $date2 = new DateTime($date2);
        $diff = $date1->diff($date2);
        return round(date_create('@0')->add($diff)->getTimestamp()/60, 0);
    }

    public static function create_folder($file_path) {
        if(!file_exists($file_path)) {
            mkdir($file_path);
        }
    }

    public static function copy($source, $destination) {
        if(@fclose(@fopen($source, 'r'))) {
            copy($source, $destination);
            return true;
        }
        return false;
    }

    public static function add_string_with_space_before($old_string, $new_string) {
        $old_string .= ' ';
        $old_string = self::add_string_unique($old_string, $new_string);
        return $old_string;
    }

    public static function substr($str, $len, $more = '...', $charset = 'UTF-8'){
        $str = html_entity_decode($str, ENT_QUOTES, $charset);
        if(mb_strlen($str, $charset) > $len) {
            $arr = explode(' ', $str);
            $str = mb_substr($str, 0, $len, $charset);
            $arrRes = explode(' ', $str);
            $last = $arr[count($arrRes)-1];
            unset($arr);
            if(strcasecmp($arrRes[count($arrRes)-1], $last)) {
                unset($arrRes[count($arrRes)-1]);
            }
            return implode(' ', $arrRes).$more;
        }
        return $str;
    }

    public static function array_shift(&$array, $number = 1) {
        $result = array();
        $number = absint($number);
        if(!is_array($array) || !is_numeric($number)) {
            return $result;
        }
        if(1 == $number) {
            return array_shift($array);
        }
        if($number >= count($array)) {
            $result = $array;
            $array = array();
            return $result;
        }
        for($i = 0; $i < $number; $i++) {
            $item = array_shift($array);
            array_push($result, $item);
        }
        return $result;
    }

    public static function timezone_hcm() {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
    }

    public static function set_default_timezone($timezone_string) {
        date_default_timezone_set($timezone_string);
    }

    public static function get_input_number($value) {
        $result = 0;
        if(is_numeric($value)) {
            $result = absint(intval($value));
        }
        return $result;
    }

    public static function remove_punctuation($str) {
        $last_char = self::get_last_char($str);
        if(')' == $last_char) {
            return $str;
        }
        return preg_replace('/^\PL+|\PL\z/', '', $str);
    }

    public static function is_punctuation($char) {
        $punctuations = array('.', '!', ':', ',', ';', '?');
        if(in_array($char, $punctuations)) {
            return true;
        }
        return false;
    }

    public static function get_last_char($str) {
        return mb_substr($str, -1);
    }

    public static function get_punctuation($str) {
        $punctuation = SB_PHP::get_last_char($str);
        if(!SB_PHP::is_punctuation($punctuation)) {
            $punctuation = '';
        }
        return $punctuation;
    }

    public static function is_number($number) {
        return is_numeric($number);
    }

    public static function lowercase($str, $charset = 'UTF-8') {
        return mb_strtolower($str, $charset);
    }

    public static function get_first_char($string, $encoding = 'utf-8') {
        $result = '';
        if(!empty($string)) {
            $result = mb_substr($string, 0, 1, $encoding);
        }
        return $result;
    }

    public static function uppercase_first_char($string, $encoding = 'utf-8') {
        $first_char = self::get_first_char($string, $encoding);
        $len = mb_strlen($string, $encoding);
        $then = mb_substr($string, 1, $len - 1, $encoding);
        $first_char = mb_strtoupper($first_char, $encoding);
        return $first_char . $then;
    }

    public static function uppercase_only_first_char($string, $encoding = 'utf-8') {
        $string = self::strtolower($string, $encoding);
        $string = self::uppercase_first_char($string, $encoding);
        return $string;
    }

    public static function strtolower($string, $encoding = 'utf-8') {
        return self::lowercase($string, $encoding);
    }

    public static function strtoupper($string, $encoding = 'utf-8') {
        return self::uppercase($string, $encoding);
    }

    public static function uppercase($str, $charset = 'UTF-8') {
        return mb_strtoupper($str, $charset);
    }

    public static function get_current_date($format = 'd-m-Y') {
        self::timezone_hcm();
        return date($format);
    }

    public static function get_current_url() {
        return $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public static function get_current_date_time($format = 'd-m-Y H:i:s', $timezone_string = '') {
        if(empty($timezone_string)) {
            self::timezone_hcm();
        } else {
            self::set_default_timezone($timezone_string);
        }
        return date($format);
    }

    public static function generate_token() {
        return md5(uniqid(mt_rand(), true));
    }

    public static function percentage($val1, $val2, $precision) {
        $res = 100 - round( ($val1 / $val2) * 100, $precision );
        return $res;
    }

    public static function str_unique($str, $split = ' ') {
        $str = explode($split, $str);
        $str = array_unique($str);
        $str = implode($split, $str);
        return $str;
    }

    public static function is_image_url($url) {
        $img_formats = array('png', 'jpg', 'jpeg', 'gif', 'tiff', 'bmp');
        $path_info = pathinfo($url);
        $extension = isset($path_info['extension']) ? $path_info['extension'] : "";
        if (in_array(strtolower($extension), $img_formats)) {
            return true;
        }
        return false;
    }

    public static function is_image_valid($image_url) {
        return self::is_image_url($image_url);
    }

    public static function is_valid_image($img_url) {
        return self::is_image_url($img_url);
    }

    public static function strip_shortcode($string) {
        $pattern = '|[[\/\!]*?[^\[\]]*?]|si';
        $replace = '';
        return preg_replace($pattern, $replace, $string);
    }

    public static function get_value_by_key($array_value, $key) {
        return isset($array_value[$key]) ? $array_value[$key] : '';
    }

    public static function strlen($string, $encoding = 'utf-8') {
        return mb_strlen($string, $encoding);
    }

    public static function get_first_image($content) {
        $doc = new DOMDocument();
        @$doc->loadHTML($content);
        $xpath = new DOMXPath($doc);
        $src = $xpath->evaluate('string(//img/@src)');
        return $src;
    }

    public static function count_image($content) {
        return self::count_html_tag($content, 'img');
    }

    public static function count_html_tag($content, $tag_name) {
        $doc = new DOMDocument();
        @$doc->loadHTML($content);
        $tags = $doc->getElementsByTagName($tag_name);
        return $tags->length;
    }

    public static function remove_vietnamese($string) {
        $characters = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D'=>'Đ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach($characters as $key => $value) {
            $string = preg_replace("/($value)/i", $key, $string);
        }
        return $string;
    }

    public static function strip_bbcode($string) {
        return self::strip_shortcode($string);
    }

    public static function paragraph_to_array($list_paragraph) {
        $list_paragraph = str_replace('</p>', '', $list_paragraph);
        $list_paragraph = explode('<p>', $list_paragraph);
        return array_filter($list_paragraph);
    }

    public static function is_favicon_url($url) {
        $favicon_formats = array('png', 'ico');
        $path_info = pathinfo($url);
        $extension = isset($path_info['extension']) ? $path_info['extension'] : '';
        if (in_array(strtolower($extension), $favicon_formats)) {
            return true;
        }
        return false;
    }

    public static function implode_all($arr, $split = '~') {
        if(!is_array($arr)) return $arr;
        $result = '';
        foreach($arr as $value) {
            if(empty($value)) continue;
            if(is_array($value)) {
                $result .= self::implode_all($value, $split).$split;
            }
            else {
                $result .= $value.$split;
            }
        }
        $result = trim($result, $split);
        return $result;
    }

    public static function add_exclamation_mark($text) {
        if(empty($text)) {
            return $text;
        }
        return self::add_punctuation($text, '!');
    }

    public static function add_commas($text) {
        if(empty($text)) {
            return $text;
        }
        return self::add_punctuation($text, ',');
    }

    public static function count_character($string) {
        $new_string = strip_tags($string);
        return str_word_count($new_string);
    }

    public static function ip_details($ip) {
        if(!self::is_ip_valid($ip)) {
            return array();
        }
        $json = file_get_contents("http://ipinfo.io/{$ip}");
        $details = json_decode($json);
        return (array)$details;
    }

    public static function get_ip_detail($ip) {
        return self::ip_details($ip);
    }

    public static function get_domain_name($url) {
        $parse = parse_url($url);
        return isset($parse['host']) ? $parse['host'] : '';
    }

    public static function get_domain_name_with_http($url) {
        $url = self::strtolower($url);
        $domain_name = self::get_domain_name($url);
        return self::add_http_to_url($domain_name);
    }

    public static function add_http_to_url($url) {
        $url = self::strtolower($url);
        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            $url = 'http://' . $url;
        }
        return $url;
    }

    public static function ping_domain($domain_name) {
        $start_time = microtime(true);
        $file = @fsockopen ($domain_name, 80, $errno, $errstr, 10);
        $stop_time = microtime(true);
        $status = 0;
        if (!$file) {
            $status = -1;
        }
        else {
            fclose($file);
            $status = ($stop_time - $start_time) * 1000;
            $status = floor($status);
        }
        return $status;
    }

    public static function is_domain_alive($domain_name) {
        $status = self::ping_domain($domain_name);
        if(-1 != $status) {
            return true;
        }
        return false;
    }

    public static function is_url_alive($url) {
        $domain = self::get_domain_name($url);
        return self::is_domain_alive($domain);
    }

    public static function get_one_in_many_if_empty($current_value, $array_value) {
        if(empty($current_value)) {
            $current_value = self::get_one_in_many($array_value);
        }
        return $current_value;
    }

    public static function get_file_extension($file_name) {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    public static function remove_file_extension($file_name) {
        return self::get_file_name_without_extension($file_name);
    }

    public static function remove_min_css_extension($file_name) {
        return self::remove_file_extension($file_name);
    }

    public static function add_file_extension($file_name, $extension) {
        return $file_name.'.'.$extension;
    }

    public static function change_file_extension($file_name, $extension) {
        $file_name = self::remove_file_extension($file_name);
        return $file_name.'.'.$extension;
    }

    public static function get_file_name_without_extension($file_name) {
        $extension = self::get_file_extension($file_name);
        $file_name = basename($file_name);
        $file_name = basename($file_name, '.'.$extension);
        return $file_name;
    }

    public static function get_one_in_many($array_value) {
        $result = '';
        if(is_array($array_value)) {
            foreach($array_value as $value) {
                $result = $value;
                if(!empty($result)) {
                    break;
                }
            }
        }
        return $result;
    }

    public static function is_ip_valid($ip) {
        if(filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }
        return false;
    }

    public static function bool_to_int($bool_value) {
        $bool_value = (bool)$bool_value;
        if($bool_value) {
            return 1;
        }
        return 0;
    }

    public static function int_to_bool($int_value) {
        $result = (bool)$int_value;
        return $result;
    }

    public static function get_part_of($part, $total) {
        return round($part * $total);
    }

    public static function is_ip_vietnam($ip) {
        $details = self::ip_details($ip);
        if(isset($details['country'])) {
            $country = $details['country'];
            if('VN' == $country) {
                return true;
            }
        }
        return false;
    }

    public static function is_email_valid($email) {
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }

    public static function get_domain_from_email($email) {
        $result = '';
        if(self::is_email_valid($email)) {
            $result = array_pop(explode('@', $email));
        }
        return $result;
    }

    public static function array_sort($array, $on, $order = SORT_ASC) {
        $new_array = array();
        $sortable_array = array();
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        } else {
            $new_array = $array;
        }

        return $new_array;
    }

    public static function get_path($file) {
        return dirname($file);
    }

    public static function base64_to_jpeg($base64_string, $output_file) {
        $folder = self::get_path($output_file);
        if(!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        $ifp = fopen($output_file, 'wb');
        $data = explode(',', $base64_string);
        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);
        return $output_file;
    }

    public static function add_punctuation_mark($text, $punc) {
        if(empty($text)) {
            return $text;
        }
        $char = substr($text, -1);
        if($punc != $char) {
            $text .= $punc;
        }
        return $text;
    }

    public static function add_dotted($text) {
        $char = substr($text, -1);
        if('.' != $char) {
            $text .= '.';
        }
        return $text;
    }

    public static function add_punctuation($text, $punc) {
        if(empty($text)) {
            return $text;
        }
        $char = substr($text, -1);
        if($punc != $char) {
            $text .= $punc;
        }
        return $text;
    }

    public static function add_colon($text) {
        $char = substr($text, -1);
        if(':' != $char) {
            $text .= ':';
        }
        return $text;
    }

    function current_weekday($format = 'd/m/Y H:i:s') {
        self::timezone_hcm();
        $weekday = date('l');
        $weekday = strtolower($weekday);
        switch($weekday) {
            case 'monday':
                $weekday = 'Thứ hai';
                break;
            case 'tuesday':
                $weekday = 'Thứ ba';
                break;
            case 'wednesday':
                $weekday = 'Thứ tư';
                break;
            case 'thursday':
                $weekday = 'Thứ năm';
                break;
            case 'friday':
                $weekday = 'Thứ sáu';
                break;
            case 'saturday':
                $weekday = 'Thứ bảy';
                break;
            default:
                $weekday = 'Chủ nhật';
                break;
        }
        return $weekday.', '.date($format);
    }

    public static function remove_http($url) {
        $disallowed = array('http://', 'https://');
        foreach($disallowed as $d) {
            if(strpos($url, $d) !== false) {
                return str_replace($d, '', $url);
            }
        }
        return $url;
    }

    public static function get_session($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : '';
    }

    public static function set_session($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function set_session_array($key, $value) {
        $old = (array)self::get_session($key);
        if(!in_array($value, $old)) {
            array_push($old, $value);
        }
        self::set_session($key, $old);
    }

    public static function sort_array_by_key_array($array = array(), $order = array()) {
        $ordered = array();
        foreach($order as $key) {
            if(is_array($key)) {
                continue;
            }
            if(array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return $ordered + $array;
    }

    public static function is_valid_url($url) {
        return self::is_url_valid($url);
    }

    public static function is_url($url) {
        return self::is_valid_url($url);
    }

    public static function is_url_valid($url) {
        if(filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        }
        return false;
    }

    public static function get_checkbox_value($value) {
        if(isset($value) && (bool)$value) {
            return 1;
        }
        return 0;
    }

    public static function get_single_line_value($value) {
        return strip_tags(stripslashes($value));
    }

    public static function is_string_contain($string, $key) {
        if (strpos($string, $key) !== false) {
            return true;
        }
        return false;
    }

    public static function str_contains($string, $key) {
        return self::is_string_contain($string, $key);
    }

    public  static function add_string_unique($old_string, $text) {
        if(!self::is_string_contain($old_string, $text)) {
            $old_string .= $text;
        }
        $old_string = trim($old_string);
        return $old_string;
    }

    public static function count_next_day($from, $to) {
        $sec_from = strtotime ( date(SB_DATE_TIME_FORMAT, strtotime($from)) );
        $sec_to = strtotime ( date(SB_DATE_TIME_FORMAT, strtotime($to)) );
        $seconds =  $sec_to - $sec_from;
        $days = $seconds / 86400;
        $days = ceil($days);
        return abs($days);
    }

    public static function is_today($date) {
        if(date('Ymd') == date('Ymd', strtotime($date))) {
            return true;
        }
        return false;
    }

    public static function get_next_time_diff($args = array()) {
        $from = '';
        $to = '';
        $text_before = '';
        extract($args, EXTR_OVERWRITE);
        $days = self::count_next_day($from, $to);
        $result = '';
        if($days == 1 && self::is_today($to)) {
            return 'Today';
        } elseif($days < 7) {
            $result = self::_n($days, '1 day', '%d days');
        } elseif($days >= 7 && $days < 30) {
            $week = round($days/7, 0);
            $result = self::_n($week, '1 week', '%d weeks');
        } elseif($days >= 30 && $days < 365) {
            $value = round($days/30, 0);
            $result = self::_n($value, '1 month', '%d months');
        } else {
            $value = round($days/365, 0);
            $result = self::_n($value, '1 year', '%d years');
        }
        $result = $text_before . ' ' . $result;
        $result = trim($result);
        return $result;
    }

    public static function _n($number, $text_one, $text_many) {
        if($number < 2) {
            return sprintf(__($text_one, 'sb-core'), $number);
        }
        return sprintf(__($text_many, 'sb-core'), $number);
    }

}