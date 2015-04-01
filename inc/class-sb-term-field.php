<?php
class SB_Term_Field {
    public static function url($args = array()) {
        $args['type'] = 'url';
        self::text($args);
    }

    public static function text($args = array()) {
        if(!isset($args['before'])) {
            $args['before'] = '';
        }
        $id = isset($args['id']) ? $args['id'] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        self::before($id, $label);
        $args['label'] = '';
        SB_Field::text($args);
        self::after();
    }

    public static function image_upload($args = array()) {
        $id = isset($args['id']) ? $args['id'] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        self::before($id, $label);
        $args['container_class'] = isset($args['container_class']) ? $args['container_class'] . ' small' : 'small';
        $args['label'] = '';
        SB_Field::media_upload_with_remove_and_preview($args);
        self::after();
    }

    public static function term_select($args = array()) {
        $id = isset($args['id']) ? $args['id'] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        self::before($id, $label);
        $args['container_class'] = isset($args['container_class']) ? $args['container_class'] . ' small' : 'small';
        $args['label'] = '';
        SB_Field::select_term($args);
        self::after();
    }

    private static function before($id, $label) { ?>
        <tr class="form-field">
            <th scope="row"><?php SB_Field::label(array('for' => $id, 'text' => $label)); ?></th>
            <td> <?php
    }

    private static function after($description = '') {
                SB_Field::the_description($description); ?>
            </td>
        </tr> <?php
    }
}