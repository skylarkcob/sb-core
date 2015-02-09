<?php
class SB_Meta_Field {
    public static function get_name($args) {
        $name = isset($args['name']) ? trim($args['name']) : '';
        if(empty($name)) {
            return $name;
        }
        $sbmb = substr($name, 0, 4);
        if('sbmb' != $sbmb) {
            $name = sb_build_meta_name($name);
        }
        return $name;
    }

    public static function text($args = array()) {
        $name = self::get_name($args);
        $value = isset($args['value']) ? trim($args['value']) : '';
        $field_class = isset($args['field_class']) ? trim($args['field_class']) : '';
        $label = isset($args['label']) ? $args['label'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($name); ?>"><?php echo $label; ?>:</label>
            <input type="text" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>" class="<?php echo $field_class; ?>">
        </p>
        <?php
    }

    public static function checkbox($args = array()) {
        $name = self::get_name($args);
        $value = isset($args['value']) ? intval($args['value']) : 0;
        $field_class = isset($args['field_class']) ? trim($args['field_class']) : '';
        $label = isset($args['label']) ? $args['label'] : '';
        ?>
        <p>
            <input type="checkbox" id="<?php echo esc_attr($name); ?>" autocomplete="off" name="<?php echo esc_attr($name); ?>" class="<?php echo $field_class; ?>" <?php checked(1, $value); ?>>&nbsp;<?php echo $label; ?>
        </p>
    <?php
    }

    public static function image_upload($args = array()) {
        $name = self::get_name($args);
        $container_class = isset($args['container_class']) ? trim($args['container_class']) : '';
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'sb-media-upload');
        $preview = isset($args['preview']) ? (bool)$args['preview'] : false;
        $label = isset($args['label']) ? $args['label'] : '';
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'sb-post-meta-row');
        $tag = 'p';
        if($preview) {
            $tag = 'div';
        }
        $value = isset($args['value']) ? $args['value'] : '';
        $image_preview_class = 'image-preview';
        if(!empty($value)) {
            $image_preview_class = SB_PHP::add_string_with_space_before($image_preview_class, 'has-image');
        }
        ?>
        <<?php echo $tag; ?> class="<?php echo $container_class; ?>">
            <label for="<?php echo esc_attr($name); ?>" class="display-block"><?php echo $label; ?>:</label>
            <?php SB_Field::media_upload_group($args); ?>
            <?php if($preview) : ?>
                <div class="<?php echo $image_preview_class; ?>">
                    <?php if(!empty($value)) : ?>
                        <img src="<?php echo $value; ?>" alt="">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </<?php echo $tag; ?>>
    <?php
    }

    public static function editor($args = array()) {
        $name = self::get_name($args);
        $value = isset($args['value']) ? $args['value'] : '';
        $id = isset($args['id']) ? $args['id'] : '';
        if(empty($id)) {
            $id = $name;
        }
        $label = isset($args['label']) ? $args['label'] : '';
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'sb-post-meta-editor');
        echo '<div class="' . $container_class . '">';
        echo '<label for="' . $id . '">' . $label . ':</label>';
        $args['textarea_name'] = $name;
        $row = isset($args['row']) ? $args['row'] : 5;
        $args['textarea_rows'] = $row;
        wp_editor($value, $id, $args);
        echo '</div>';
    }

    public static function number($args = array()) {
        $name = self::get_name($args);
        $value = isset($args['value']) ? $args['value'] : '';
        $id = isset($args['id']) ? $args['id'] : '';
        if(empty($id)) {
            $id = $name;
        }
        $label = isset($args['label']) ? $args['label'] : '';
        $field_class = isset($args['field_class']) ? trim($args['field_class']) : '';
        echo '<p>';
        echo '<label for="' . $id . '">' . $label . ':</label>';
        $input = new SB_HTML('input');
        $input->set_attribute('type', 'number');
        $input->set_attribute('class', $field_class);
        $input->set_attribute('value', $value);
        $input->set_attribute('name', $name);
        $input->set_attribute('id', $id);
        $input->output();
        echo '</p>';
    }
}