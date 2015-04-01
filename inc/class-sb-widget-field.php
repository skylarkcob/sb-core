<?php
class SB_Widget_Field {
    public static function before($class = '') {
        $class = SB_PHP::add_string_with_space_before($class, 'sb-widget');
        echo '<div class="' . $class . '">';
    }

    public static function after() {
        echo '</div>';
    }

    public static function title($id, $name, $value) {
        $args = array(
            'id' => $id,
            'name' => $name,
            'value' => $value,
            'label' => __('Title:', 'sb-core'),
        );
        self::text($args);
    }

    public static function select_post_type($args = array()) {
        $options = SB_Post::get_types(array(), 'objects');
        $all_option = '';
        $value = isset($args['value']) ? $args['value'] : '';
        foreach ($options as $key => $option) {
            $tmp_args = array(
                'value' => $key,
                'selected' => $value,
                'text' => $option->labels->name
            );
            $all_option .= SB_Field::get_option($tmp_args);
        }
        $args['all_option'] = $all_option;
        SB_Field::select($args);
    }

    public static function number($args = array()) {
        $field_class = isset($args['field_class']) ? $args['field_class'] : '';
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'sb-number');
        $args['field_class'] = $field_class;
        $args['type'] = 'number';
        SB_Field::text($args);
    }

    public static function select($args = array()) {
        SB_Field::select($args);
    }

    public static function select_sidebar($args = array()) {
        $list_options = isset($args['list_options']) ? $args['list_options'] : array();
        $value = isset($args['value']) ? $args['value'] : '';
        $all_option = '';
        foreach ( $list_options as $sidebar_id => $sidebar ) {
            if('wp_inactive_widgets' == $sidebar_id) {
                continue;
            }
            $all_option .= SB_Field::get_option(array('value' => $sidebar_id, 'selected' => $value, 'text' => $sidebar['name']));
        }
        $args['all_option'] = $all_option;
        SB_Field::select($args);
    }

    public static function radio($args = array()) {
        $options = isset($args['options']) ? $args['options'] : array();
        if(count($options) > 0) {
            foreach($options as $key => $label) {
                $tmp_args = array(
                    'label' => $label,
                    'value' => $key
                );
                self::radio($tmp_args);
            }
        } else {
            $args['type'] = 'radio';
            SB_Field::text($args);
        }
    }

    public static function checkbox($args = array()) {
        $field_class = isset($args['field_class']) ? $args['field_class'] : '';
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'sb-checkbox');
        $args['field_class'] = $field_class;
        $args['type'] = 'checkbox';
        SB_Field::text($args);
    }

    public static function size($args = array()) {
        SB_Field::size($args);
    }

    public static function text($args = array()) {
        SB_Field::text($args);
    }

    public static function textarea($args = array()) {
        SB_Field::textarea($args);
    }

    public static function fieldset($args = array()) {
        SB_Field::fieldset($args);
    }

    public static function select_term($args = array()) {
        SB_Field::select_term($args);
    }

    public static function media_upload($args = array()) {
        SB_Field::media_upload_with_remove_and_preview($args);
    }
}