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
        $container_class = '';
        $id = '';
        $name = '';
        $field_class = '';
        $label = '';
        $options = SB_Post::get_types(array(), 'objects');
        $value = '';
        $description = '';
        extract($args, EXTR_OVERWRITE);
        ?>
        <p class="<?php echo $container_class; ?>">
            <label for="<?php echo esc_attr($id); ?>"><?php echo $label; ?></label>
            <select id="<?php echo esc_attr($id); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr($name); ?>" autocomplete="off">
                <?php foreach ($options as $key => $option) : ?>
                    <option value="<?php echo esc_attr($key); ?>"<?php selected($value, $key); ?>><?php echo $option->labels->name; ?></option>
                <?php endforeach; ?>
            </select>
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
    <?php
    }

    public static function number($args = array()) {
        $field_class = '';
        $container_class = '';
        $id = '';
        $description = '';
        $label = '';
        $name = '';
        $value = '';
        extract($args, EXTR_OVERWRITE);
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'sb-number');
        ?>
        <p class="<?php echo $container_class; ?>">
            <label for="<?php echo esc_attr($id); ?>"><?php echo $label; ?></label>
            <input id="<?php echo esc_attr($id); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr($name); ?>" type="number" value="<?php echo esc_attr($value); ?>" autocomplete="off">
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function select($args = array()) {
        $container_class = '';
        $id = '';
        $name = '';
        $field_class = '';
        $label = '';
        $options = array();
        $value = '';
        $description = '';
        extract($args, EXTR_OVERWRITE);
        ?>
        <p class="<?php echo $container_class; ?>">
            <label for="<?php echo esc_attr($id); ?>"><?php echo $label; ?></label>
            <select id="<?php echo esc_attr($id); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr($name); ?>" autocomplete="off">
                <?php foreach ($options as $key => $option) : ?>
                    <option value="<?php echo esc_attr($key); ?>"<?php selected($value, $key); ?>><?php echo $option; ?></option>
                <?php endforeach; ?>
            </select>
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function select_sidebar($args = array()) {
        $paragraph_class = '';
        $id = '';
        $name = '';
        $field_class = '';
        $label_text = '';
        $list_options = array();
        $value = '';
        $description = '';
        extract($args, EXTR_OVERWRITE);
        ?>
        <p class="<?php echo $paragraph_class; ?>">
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo $label_text; ?></label>
            <select id="<?php echo esc_attr( $id ); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr( $name ); ?>">
                <?php foreach ( $list_options as $sidebar_id => $sidebar ) : ?>
                    <?php if('wp_inactive_widgets' == $sidebar_id) continue; ?>
                    <option value="<?php echo esc_attr( $sidebar_id ); ?>"<?php selected( $value, $sidebar_id ); ?>><?php echo $sidebar['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function radio($args = array()) {
        $name = '';
        $options = array();
        $value = '';
        if(is_array($args)) {
            extract($args);
        }
        if(empty($name) || !is_array($options) || count($options) < 1) {
            return;
        }
        foreach($options as $key => $text) : ?>
            <input type="radio" name="<?php echo $name; ?>" value="<?php echo $key; ?>" autocomplete="off" <?php checked($value, $key); ?>><?php echo $text; ?><br>
        <?php endforeach;
    }

    public static function checkbox($args = array()) {
        $field_class = '';
        $container_class = '';
        $id = '';
        $label = '';
        $description = '';
        $name = '';
        $value = '';
        extract($args, EXTR_OVERWRITE);
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'sb-checkbox');
        ?>
        <p class="<?php echo $container_class; ?>">
            <input id="<?php echo esc_attr($id); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr($name ); ?>" type="checkbox" value="<?php echo esc_attr($value); ?>" autocomplete="off" <?php checked($value, 1); ?>>
            <label for="<?php echo esc_attr($id); ?>"><?php echo $label; ?></label>
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function size($args = array()) {
        $field_class = '';
        $container_class = '';
        $description = '';
        $id = '';
        $label = '';
        $id_width = '';
        $id_height = '';
        $name_width = '';
        $name_height = '';
        $value = array();
        extract($args, EXTR_OVERWRITE);
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'sb-number image-size');
        ?>
        <p class="<?php echo $container_class; ?>">
            <label for="<?php echo esc_attr($id ); ?>"><?php echo $label; ?></label>
            <label for="<?php echo esc_attr($id_width); ?>"></label>
            <input id="<?php echo esc_attr($id_width); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr($name_width); ?>" type="number" autocomplete="off" value="<?php echo esc_attr($value[0]); ?>">
            <span>x</span>
            <label for="<?php echo esc_attr($id_height); ?>"></label>
            <input id="<?php echo esc_attr($id_height); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr($name_height); ?>" type="number" autocomplete="off" value="<?php echo esc_attr($value[1]); ?>">
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function text($args = array()) {
        $container_class = '';
        $field_class = 'widefat';
        $id = '';
        $name = '';
        $value = '';
        $description = '';
        $label = '';
        extract($args, EXTR_OVERWRITE);
        ?>
        <p class="<?php echo $container_class; ?>">
            <label for="<?php echo esc_attr($id); ?>"><?php echo $label; ?></label>
            <input id="<?php echo esc_attr($id); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr($name); ?>" type="text" value="<?php echo esc_attr($value); ?>" autocomplete="off">
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function textarea($args = array()) {
        $paragraph_class = '';
        $input_class = 'widefat';
        $id = '';
        $name = '';
        $value = '';
        $description = '';
        $label_text = '';
        $textarea_rows = 3;
        extract($args, EXTR_OVERWRITE);
        ?>
        <p class="<?php echo $paragraph_class; ?>">
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo $label_text; ?></label>
            <textarea id="<?php echo esc_attr( $id ); ?>" class="<?php echo $input_class; ?>" name="<?php echo esc_attr( $name ); ?>" rows="<?php echo $textarea_rows; ?>"><?php echo esc_attr( $value ); ?></textarea>
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function fieldset($args = array()) {
        $label = '';
        $callback = '';
        $container_class = '';
        extract($args, EXTR_OVERWRITE);
        ?>
        <fieldset class="<?php echo $container_class; ?>">
            <legend><?php echo $label; ?></legend>
            <?php call_user_func($callback); ?>
        </fieldset>
        <?php
    }

    public static function select_term($args = array()) {
        SB_Field::select_term($args);
    }

    public static function media_upload($args = array()) {
        $container_class = '';
        extract($args, EXTR_OVERWRITE);
        SB_PHP::add_string_with_space_before($container_class, 'sb-media-upload');
        $upload_button_class = isset($args['upload_button_class']) ? $args['upload_button_class'] : '';
        $upload_button_class = SB_PHP::add_string_with_space_before($upload_button_class, 'sb-widget-button delegate');
        $args['upload_button_class'] = $upload_button_class;
        $remove_button_class = isset($args['remove_button_class']) ? $args['remove_button_class'] : '';
        $remove_button_class = SB_PHP::add_string_with_space_before($remove_button_class, 'sb-widget-button delegate');
        $args['remove_button_class'] = $remove_button_class;
        ?>
        <div class="<?php echo $container_class; ?>">
            <?php SB_Field::media_upload_no_preview($args); ?>
        </div>
        <?php
    }
}