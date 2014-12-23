<?php
class SB_User {
    public static function is_admin($user_id) {
        $user = get_user_by('id', $user_id);
        if(is_a($user, 'WP_User')) {
            foreach($user->roles as $key => $value) {
                if('administrator' == $value) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function get($args = array()) {
        return get_users($args);
    }

    public static function can($action) {
        return current_user_can($action);
    }

    public static function get_administrators($args = array()) {
        $args['role'] = 'administrator';
        return self::get($args);
    }

    public static function get_first_admin($args = array()) {
        $users = self::get_administrators($args);
        $user = new WP_User();
        foreach($users as $value) {
            $user = $value;
            break;
        }
        return $user;
    }

    public static function only_view_own_media($query) {
        global $current_user;
        if($current_user && !self::is_admin($current_user->ID)) {
            $query->set('author', $current_user->ID );
        }
        return $query;
    }

    public static function add($args = array()) {
        $password = '';
        $role = '';
        extract($args, EXTR_OVERWRITE);
        if(!empty($password) && !empty($username) && !empty($email) && !username_exists($username) && !email_exists($email)) {
            $user_id = wp_create_user( $username, $password, $email );
            $user = get_user_by('id', $user_id);
            self::remove_all_role($user);
            $user->add_role( $role );
        }
    }

    public static function add_admin($args = array()) {
        $args['role'] = 'administrator';
        self::add($args);
    }

    public static function remove_all_role($user) {
        foreach($user->roles as $role) {
            $user->remove_role($role);
        }
    }

    public static function get_logout_url($redirect = '') {
        return wp_logout_url($redirect);
    }

    public static function set_password($user_id, $new_password) {
        wp_set_password($new_password, $user_id);
    }

    public static function change_password($username, $new_password) {
        $user = get_user_by('login', $username);
        if($user) {
            self::set_password($user->ID, $new_password);
        }
    }

    public static function update_password($username, $password) {
        self::change_password($username, $password);
    }

    public static function is_logged_in() {
        return is_user_logged_in();
    }

    public static function get_current() {
        return wp_get_current_user();
    }

    public static function get_profile_url() {
        return admin_url('profile.php');
    }

    public static function get_meta($user_id, $meta_key) {
        return get_user_meta($user_id, $meta_key, true);
    }

    public static function update_meta($user_id, $meta_key, $meta_value) {
        if($user_id > 0) {
            update_user_meta($user_id, $meta_key, $meta_value);
        }
    }

    public static function get_following_stores($user_id) {
        return self::get_meta($user_id, 'following-stores');
    }

    public static function get_following_stores_array($user_id) {
        global $sb_following_stores;
        if(empty($sb_following_stores) || !is_array($sb_following_stores)) {
            $stores = self::get_following_stores($user_id);
            $sb_following_stores = explode(',', $stores);
        }
        $sb_following_stores = array_filter($sb_following_stores);
        return $sb_following_stores;
    }

    public static function update_following_stores($user_id, $store_id, $remove = false) {
        if($store_id > 0) {
            $stores = self::get_following_stores_array($user_id);
            if($remove) {
                $key = array_search($store_id, $stores);
                unset($stores[$key]);
                $stores = implode(',', $stores);
            } else {
                if(!in_array($store_id, $stores)) {
                    $stores = implode(',', $stores);
                    $stores .= ',' . $store_id;
                    $stores = trim($stores, ',');
                }
            }
            self::update_meta($user_id, 'following-stores', $stores);
        }
    }

    public static function remove_following_store($user_id, $store_id) {
        self::update_following_stores($user_id, $store_id, true);
    }

    public static function unfollow_store($user_id, $store_id) {
        self::remove_following_store($user_id, $store_id);
    }

    public static function get_saving_coupons_array($user_id) {
        global $sb_saving_coupons;
        if(empty($sb_saving_coupons) || !is_array($sb_saving_coupons)) {
            $coupons = self::get_meta($user_id, 'saving-coupons');
            $sb_saving_coupons = explode(',', $coupons);
        }
        $sb_saving_coupons = array_filter($sb_saving_coupons);
        return $sb_saving_coupons;
    }

    public static function update_saving_coupons($user_id, $coupon_id, $remove = false) {
        if($coupon_id > 0) {
            $coupons = self::get_saving_coupons_array($user_id);
            if($remove) {
                $key = array_search($coupon_id, $coupons);
                unset($coupons[$key]);
                $coupons = implode(',', $coupons);
            } else {
                if(!in_array($coupon_id, $coupons)) {
                    $coupons = implode(',', $coupons);
                    $coupons .= ',' . $coupon_id;
                    $coupons = trim($coupons, ',');
                }
            }
            self::update_meta($user_id, 'saving-coupons', $coupons);
        }
    }

    public static function remove_saving_coupon($user_id, $coupon_id) {
        self::update_saving_coupons($user_id, $coupon_id, true);
    }

    public static function count_saving_coupon($user_id) {
        $coupons = self::get_saving_coupons_array($user_id);
        $count = count($coupons);
        return $count;
    }

    public static function count_following_store($user_id) {
        $stores = self::get_following_stores_array($user_id);
        $count = count($stores);
        return $count;
    }

    public static function get_login_url($redirect = '') {
        return wp_login_url($redirect);
    }

    public static function get_signup_url() {
        return wp_registration_url();
    }

    public static function go_to_login() {
        wp_redirect(self::get_login_url());
        die();
    }

    public static function is_following_store($user_id, $store_id) {
        if($user_id > 0 && $store_id > 0) {
            $stores = SB_User::get_following_stores_array($user_id);
            if(in_array($store_id, $stores)) {
                return true;
            }
        }
        return false;
    }

    public static function is_current_following_store($store_id) {
        if($store_id > 0) {
            if(SB_User::is_logged_in()) {
                $user = SB_User::get_current();
                return self::is_following_store($user->ID, $store_id);
            }
        }
        return false;
    }

    public static function must_login() {
        if(!self::is_logged_in()) {
            self::go_to_login();
        }
    }

    public static function get_by_meta($meta_key, $args = array()) {
        $args['meta_key'] = $meta_key;
        return self::get($args);
    }
}