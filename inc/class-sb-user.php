<?php
defined('ABSPATH') OR exit;

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


    public static function set_password($user_id, $new_password) {
        wp_set_password($new_password, $user_id);
    }

    public static function change_assword($username, $new_password) {
        $user = get_user_by('login', $username);
        if($user) {
            self::set_password($user->ID, $new_password);
        }
    }

}