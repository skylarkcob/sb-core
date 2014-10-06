<?php
if(!defined('ABSPATH')) exit;

class SB_Field {

    private static function image_thumbnail($class = "", $src = "") {
        $class = str_replace('[', '_', $class);
        $class = str_replace(']', '', $class);
        $class .= " media image thumbnail sbtheme";
        $class = trim($class);
        if(!empty($src)) {
            $class .= " uploaded";
        }
        echo '<div class="'.$class.'">';
        if(!empty($src)) {
            echo '<img src="'.$src.'">';
        }
        echo '</div>';
    }

    public static function media_image($id, $name, $value, $description) {
        echo '<div class="sbtheme-media-image">';
        self::image_thumbnail($name, $value);
        self::media_upload($id, $name, $value, $description);
        echo '</div>';
    }

    private static function media_upload($id, $name, $value, $description) {
        $button_title = __("Insert image", "sb-core");
        $value = trim($value);
        ?>
        <div class="sbtheme-upload media">
            <input type="text" id="<?php echo $id; ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>">
            <a title="<?php echo $button_title; ?>" data-editor="sb-content" class="sb-button button sb-insert-media sb-add_media" href="javascript:void(0);"><?php _e("Upload", "sb-core"); ?></a>
        </div>
        <p class="description"><?php echo $description; ?></p>
    <?php
    }

    public static function media_image_with_url($id, $name, $value, $description) {
        echo '<div class="sbtheme-media-image with-url">';
        self::image_thumbnail($name, $value);
        self::media_upload_with_url($id, $name, $value, $description);
        echo '</div>';
    }

    private static function media_upload_with_url($id, $name, $value, $description) {
        self::media_upload($id."_image", $name."[image]", $value, $description);
        $name = $name."[url]";
        $url_name = $name;
        $options = get_option("sb_options");
        $keys = explode(']', $name);
        $first = str_replace('sb_options[', '', $keys[0]);
        $second = str_replace('[', '', $keys[1]);
        $third = str_replace('[', '', $keys[2]);

        $value = isset($options[$first][$second][$third]) ? $options[$first][$second][$third] : '';
        $description = __("Enter url for the image above.", "sb-core");
        echo '<div style="margin-top: 20px; ">';
        self::text_field($id."_url", $url_name, $value, $description);
        echo '</div>';
    }

    public static function text_field($id, $name, $value, $description) {
        $value = trim($value);
        $class = 'widefat';
        printf('<input type="text" id="%1$s" name="%2$s" value="%3$s" class="'.$class.'"><p class="description">%4$s</p>', esc_attr($id), esc_attr($name), $value, $description);
    }

    public static function switch_button($id, $name, $value, $description) {
        $enable = (bool) $value;
        $class = "switch-button";
        $class_on = $class . ' on';
        $class_off = $class . ' off';
        if($enable) {
            $class_on .= " active";
        } else {
            $class_off .= " active";
        }
        ?>
        <fieldset class="sbtheme-switch">
            <div class="switch-options">
                <label data-switch="on" class="<?php echo $class_on; ?> left"><span><?php _e("On", "sb-core"); ?></span></label>
                <label data-switch="off" class="<?php echo $class_off; ?> right"><span><?php _e("Off", "sb-core"); ?></span></label>
                <input type="hidden" value="<?php echo $value; ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo $id; ?>" class="checkbox checkbox-input">
                <p class="description"><?php echo $description; ?></p>
            </div>
        </fieldset>
    <?php
    }

    public static function select_term_field($args = array()) {
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
            <label for="<?php echo esc_attr( $id ); ?>"></label>
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

    public static function social_field($args = array()) {
        foreach($args as $field) {
            $id = $field['id'];
            $name = $field['name'];
            $value = $field['value'];
            $description = $field['description'];
            echo '<div style="margin-bottom: 20px; ">';
            self::text_field($id, $name, $value, $description);
            echo '</div>';
        }
    }

    public static function rich_editor_field($id, $name, $value, $description, $args = array()) {
        $defaults = array(
            'textarea_name' => $name,
            'textarea_rows' => 5
        );
        $args = wp_parse_args($args, $defaults);
        ?>
        <div id="<?php echo $id . '_editor'; ?>" class="sb-rich-editor">
            <?php wp_editor($value, $id, $args); ?>
            <p class="description"><?php echo $description; ?></p>
        </div>
    <?php
    }
}