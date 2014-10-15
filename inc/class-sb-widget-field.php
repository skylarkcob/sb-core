<?php
class SB_Widget_Field {
    public static function number($args = array()) {
        $input_class = '';
        $paragraph_class = '';
        $display = true;
        $id = '';
        $description = '';
        $label_text = '';
        $name = '';
        $value = '';
        extract($args, EXTR_OVERWRITE);
        $style = '';
        if(!$display) {
            $style = 'display: none;';
        }
        $input_class = trim($input_class . ' sb-number');
        ?>
        <p class="<?php echo $paragraph_class; ?>" style="<?php echo $style; ?>">
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo $label_text; ?></label>
            <input id="<?php echo esc_attr( $id ); ?>" class="<?php echo $input_class; ?>" name="<?php echo esc_attr( $name ); ?>" type="number" value="<?php echo esc_attr( $value ); ?>">
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function select($args = array()) {
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
                <?php foreach ( $list_options as $key => $option ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>"<?php selected( $value, $key ); ?>><?php echo $option; ?></option>
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

    public static function checkbox($args = array()) {
        $input_class = '';
        $paragraph_class = '';
        $id = '';
        $label_text = '';
        $description = '';
        $name = '';
        $value = '';
        $display = true;
        $paragraph_id = '';
        extract($args, EXTR_OVERWRITE);
        $style = '';
        if(!$display) {
            $style = 'display: none;';
        }
        $input_class = trim($input_class . ' sb-checkbox');
        ?>
        <p id="<?php echo $paragraph_id; ?>" class="<?php echo $paragraph_class; ?>" style="<?php echo $style; ?>">
            <input id="<?php echo esc_attr( $id ); ?>" class="<?php echo $input_class; ?>" name="<?php echo esc_attr( $name ); ?>" type="checkbox" value="<?php echo esc_attr( $value ); ?>" <?php checked( $value, 1, true ); ?>>
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo $label_text; ?></label>
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function size($args = array()) {
        $input_class = '';
        $paragraph_class = '';
        $display = '';
        $description = '';
        $id = '';
        $label_text = '';
        $id_width = '';
        $id_height = '';
        $name_width = '';
        $name_height = '';
        $value = array();
        extract($args, EXTR_OVERWRITE);
        $input_class = trim($input_class . ' sb-number image-size');
        ?>
        <p class="<?php echo $paragraph_class; ?>"<?php if(!$display) echo ' style="display:none"'; ?>>
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo $label_text; ?></label>
            <label for="<?php echo esc_attr( $id_width ); ?>"></label>
            <input id="<?php echo esc_attr( $id_width ); ?>" class="<?php echo $input_class; ?>" name="<?php echo esc_attr( $name_width ); ?>" type="number" value="<?php echo esc_attr( $value[0] ); ?>">
            <span>x</span>
            <label for="<?php echo esc_attr( $id_height ); ?>"></label>
            <input id="<?php echo esc_attr( $id_height ); ?>" class="<?php echo $input_class; ?>" name="<?php echo esc_attr( $name_height ); ?>" type="number" value="<?php echo esc_attr( $value[1] ); ?>">
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
        </p>
        <?php
    }

    public static function text($args = array()) {
        $paragraph_class = '';
        $input_class = 'widefat';
        $id = '';
        $name = '';
        $value = '';
        $description = '';
        $label_text = '';
        extract($args, EXTR_OVERWRITE);
        ?>
        <p class="<?php echo $paragraph_class; ?>">
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo $label_text; ?></label>
            <input id="<?php echo esc_attr( $id ); ?>" class="<?php echo $input_class; ?>" name="<?php echo esc_attr( $name ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>">
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
        $title = '';
        $callback = '';
        $class = '';
        $display = true;
        extract($args, EXTR_OVERWRITE);
        $style = '';
        if(!$display) {
            $style = 'display: none;';
        }
        ?>
        <fieldset class="<?php echo $class; ?>" style="<?php echo $style; ?>">
            <legend><?php echo $title; ?></legend>
            <?php call_user_func($callback); ?>
        </fieldset>
        <?php
    }

    public static function select_term($args = array()) {
        $paragraph_class = "";
        $id = "";
        $name = "";
        $field_class = "";
        $label_text = "";
        $list_options = array();
        $value = "";
        $description = "";
        $taxonomy = '';
        $taxonomy_id = '';
        $taxonomy_name = '';
        $display = true;
        extract($args, EXTR_OVERWRITE);
        $style = '';
        if(!$display) {
            $style = 'display: none;';
        }
        ?>
        <p class="<?php echo $paragraph_class; ?>" style="<?php echo $style; ?>">
            <label for="<?php echo esc_attr( $id ); ?>"><?php echo $label_text; ?></label>
            <select id="<?php echo esc_attr( $id ); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr( $name ); ?>">
                <?php foreach ( $list_options as $tax ) : ?>
                    <?php $terms = get_terms($tax->name); ?>
                    <?php if(count($terms) > 0) : ?>
                        <optgroup label="<?php echo $tax->labels->name; ?>">
                            <?php foreach ( $terms as $cat ) : ?>
                                <option value="<?php echo $cat->term_id; ?>"<?php selected( $value, $cat->term_id ); ?> data-taxonomy="<?php echo $tax->name; ?>"><?php echo $cat->name; ?> (<?php echo $cat->count; ?>)</option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
            <input id="<?php echo esc_attr( $taxonomy_id ); ?>" class="widefat taxonomy" name="<?php echo esc_attr( $taxonomy_name ); ?>" type="hidden" value="<?php echo esc_attr( $taxonomy ); ?>">
        </p>
        <?php
    }

    public static function media_upload($args = array()) {
        $id = '';
        $name = '';
        $label_text = '';
        $value = '';
        $paragraph_id = '';
        extract($args, EXTR_OVERWRITE);
        ?>
        <p id="<?php echo $paragraph_id; ?>" class="sb-media-upload">
            <label for="<?php echo esc_attr($id); ?>"><?php _e($label_text, 'sb-banner-widget' ); ?></label>
            <input id="<?php echo esc_attr($id); ?>" class="widefat" name="<?php echo esc_attr($name); ?>" type="text" value="<?php echo esc_attr($value); ?>">
            <a title="<?php _e('Insert image', 'sb-core'); ?>" data-editor="sb-content" class="sb-button button sb-insert-media sb-add-media" href="javascript:;"><?php _e('Upload', 'sb-core'); ?></a>
        </p>
        <?php
    }
}