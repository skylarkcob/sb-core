<?php
class SB_Meta_Field {
    public static function text($args = array()) {
        $name = isset($args['name']) ? trim($args['name']) : '';
        if(empty($name)) {
            return;
        }
        $name = sb_build_meta_name($name);
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

    public static function image_upload($args = array()) {
        $name = isset($args['name']) ? trim($args['name']) : '';
        if(empty($name)) {
            return;
        }
        $name = sb_build_meta_name($name);
        $value = isset($args['value']) ? trim($args['value']) : '';
        $container_class = isset($args['container_class']) ? trim($args['container_class']) : '';
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'sb-media-upload');
        $field_class = isset($args['field_class']) ? trim($args['field_class']) : '';
        $upload_button_class = isset($args['upload_button_class']) ? trim($args['upload_button_class']) : '';
        $remove_button_class = isset($args['remove_button_class']) ? trim($args['remove_button_class']) : '';
        $upload_button_class = SB_PHP::add_string_with_space_before($upload_button_class, 'sb-button button sb-insert-media sb-add_media');
        $remove_button_class = SB_PHP::add_string_with_space_before($remove_button_class, 'sb-button button sb-remove-media sb-remove-image');
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'image-url image-upload-url');
        $label = isset($args['label']) ? $args['label'] : ''; ?>
        <p class="<?php echo $container_class; ?>">
            <label for="<?php echo esc_attr($name); ?>" class="display-block"><?php echo $label; ?>:</label>
            <input type="url" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>" autocomplete="off" class="<?php echo $field_class; ?>">
            <a href="javascript:;" class="<?php echo $upload_button_class; ?>" title="<?php _e('Insert image', 'sb-core'); ?>"><?php _e('Upload', 'sb-core'); ?></a>
            <a href="javascript:;" class="<?php echo $remove_button_class; ?>" title="<?php _e('Remove image', 'sb-core'); ?>"><?php _e('Remove', 'sb-core'); ?></a>
        </p>
        <?php
    }
}