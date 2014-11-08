<?php
class SB_Term_Field {
    public static function url($args = array()) {
        $id = '';
        $label = '';
        $description = '';
        $name = '';
        $value = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        if(empty($id) || empty($label) || empty($name)) {
            return;
        }
        self::before($id, $label);
        ?>
        <input type="url" size="40" value="<?php echo $value; ?>" id="<?php echo $id; ?>" name="<?php echo $name; ?>">
        <?php
        self::after($description);
    }

    public static function image_upload($args = array()) {
        $id = '';
        $label = '';
        $description = '';
        $name = '';
        $value = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        if(empty($id) || empty($label) || empty($name)) {
            return;
        }
        $image_preview = '';
        $image_preview_class = 'image-preview';
        if(!empty($value)) {
            $image_preview = sprintf('<img src="%s">', $value);
            $image_preview_class .= ' has-image';
        }
        self::before($id, $label);
        $args['container_class'] = isset($args['container_class']) ? $args['container_class'] . ' small' : 'small';
        SB_Field::media_upload_with_remove_and_preview($args);
        self::after($description);
    }

    private static function before($id, $label) {
        ?>
        <tr class="form-field">
            <th scope="row"><label for="<?php echo $id; ?>"><?php echo $label; ?></label></th>
            <td>
        <?php
    }

    private static function after($description) {
        ?>
                <p class="description"><?php echo $description; ?></p>
            </td>
        </tr>
        <?php
    }
}