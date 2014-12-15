<?php
class SB_Geo {
    public static function is_geoplugin_valid($geo) {
        return SB_PHP::is_geoplugin_valid($geo);
    }

    public static function get_geoplugin_country_code($geo) {
        return self::get_geoplugin_data($geo, 'countryCode');
    }

    public static function get_geoplugin_city($geo) {
        return self::get_geoplugin_data($geo, 'city');
    }

    public static function get_geoplugin_country_name($geo) {
        return self::get_geoplugin_data($geo, 'countryName');
    }

    public static function get_geoplugin_data($geo, $key) {
        $key = 'geoplugin_' . $key;
        return isset($geo[$key]) ? $geo[$key] : '';
    }

    public static function get_geoplugin_info($ip_address) {
        return SB_PHP::get_geo_info($ip_address);
    }

    public static function set_country_cookie($ip_address, $month, $domain) {
        $sb_geo = isset($_COOKIE['sb_geo']) ? $_COOKIE['sb_geo'] : array();
        $country_code = isset($sb_geo['country_code']) ? $sb_geo['country_code'] : '';
        if(empty($country_code)) {
            $geo = self::get_geoplugin_info($ip_address);
            if(self::is_geoplugin_valid($geo)) {
                $country_code = self::get_geoplugin_country_code($geo);
                $country_name = self::get_geoplugin_country_name($geo);
                $city = self::get_geoplugin_city($geo);
                $value = array(
                    'country_code' => $country_code,
                    'country_name' => $country_name,
                    'city' => $city
                );
                $value = serialize($value);
                SB_PHP::set_cookie_month('sb_geo', $value, $month, $domain);
            }
        }
    }

    public static function get_country_cookie() {
        $sb_geo = SB_PHP::get_cookie_array('sb_geo');
        return $sb_geo;
    }
}