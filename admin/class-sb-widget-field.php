<?php
if(!defined('ABSPATH')) exit;

if(!class_exists('SB_Widget_Field')) {
    class SB_Widget_Field {
        public static function number($args = array()) {
            $input_class = '';
            $paragraph_class = '';
            $display = '';
            $id = '';
            $description = '';
            $label_text = '';
            $name = '';
            $value = '';
            $defaults = array(
                'id'				=> '',
                'name'				=> '',
                'value'				=> '',
                'description'		=> '',
                'paragraph_id'		=> '',
                'display'			=> true,
                'input_class'		=> '',
                'paragraph_class'	=> ''
            );
            $args = wp_parse_args($args, $defaults);
            extract($args, EXTR_OVERWRITE);
            $input_class = trim($input_class." sb-number");
            ?>
            <p class="<?php echo $paragraph_class; ?>"<?php if(!$display) echo ' style="display:none"'; ?>>
                <label for="<?php echo esc_attr( $id ); ?>"><?php echo $label_text; ?></label>
                <input id="<?php echo esc_attr( $id ); ?>" class="<?php echo $input_class; ?>" name="<?php echo esc_attr( $name ); ?>" type="number" value="<?php echo esc_attr( $value ); ?>">
                <?php if(!empty($description)) : ?>
                    <em><?php echo $description; ?></em>
                <?php endif; ?>
            </p>
            <?php
        }

        public static function select($args = array()) {
            $paragraph_class = "";
            $id = "";
            $name = "";
            $field_class = "";
            $label_text = "";
            $list_options = array();
            $value = "";
            $description = "";

            $defaults = array(
                "id"                => "",
                "name"              => "",
                "label_text"        => "",
                "value"             => "",
                "paragraph_class"   => "",
                "field_class"       => "",
                "list_options"      => array()
            );

            $args = wp_parse_args($args, $defaults);

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

        public static function checkbox($args = array()) {
            $input_class = '';
            $paragraph_class = '';
            $id = '';
            $label_text = '';
            $description = '';
            $name = '';
            $value = '';
            $defaults = array(
                'id'				=> '',
                'name'				=> '',
                'value'				=> '',
                'description'		=> '',
                'paragraph_id'		=> '',
                'display'			=> true,
                'input_class'		=> '',
                'paragraph_class'	=> ''
            );
            $args = wp_parse_args($args, $defaults);
            extract($args, EXTR_OVERWRITE);
            $input_class = trim($input_class." sb-checkbox");
            ?>
            <p class="<?php echo $paragraph_class; ?>">
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
            $defaults = array(
                'id_width'			=> '',
                'name_width'		=> '',
                'id_height'			=> '',
                'name_height'		=> '',
                'value'				=> array(),
                'description'		=> '',
                'paragraph_id'		=> '',
                'display'			=> true,
                'input_class'		=> '',
                'paragraph_class'	=> ''
            );
            $args = wp_parse_args($args, $defaults);
            extract($args, EXTR_OVERWRITE);
            $input_class = trim($input_class." sb-number image-size");
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
            $paragraph_class = "";
            $input_class = "";
            $id = "";
            $name = "";
            $value = "";
            $description = "";
            $label_text = "";

            $defaults = array(
                "input_class"   => "widefat"
            );

            $args = wp_parse_args($args, $defaults);

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
            $paragraph_class = "";
            $input_class = "";
            $id = "";
            $name = "";
            $value = "";
            $description = "";
            $label_text = "";
            $textarea_rows = 3;

            $defaults = array(
                "input_class"   => "widefat",
                "textarea_rows" => 3
            );

            $args = wp_parse_args($args, $defaults);

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
            extract($args, EXTR_OVERWRITE);
            ?>
            <fieldset>
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

            $defaults = array(
                "id"                => "",
                "name"              => "",
                "label_text"        => "",
                "value"             => "",
                "paragraph_class"   => "",
                "field_class"       => "",
                "list_options"      => array()
            );

            $args = wp_parse_args($args, $defaults);

            extract($args, EXTR_OVERWRITE);
            ?>
            <p class="<?php echo $paragraph_class; ?>">
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
    }
}