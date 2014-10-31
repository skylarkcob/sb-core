<?php
class SB_Field {

    private static function image_thumbnail($class = '', $src = '') {
        $class = str_replace('[', '_', $class);
        $class = str_replace(']', '', $class);
        $class .= ' media image thumbnail sbtheme';
        $class = trim($class);
        if(!empty($src)) {
            $class .= ' uploaded';
        }
        echo '<div class="'.$class.'">';
        if(!empty($src)) {
            echo '<img src="'.$src.'">';
        }
        echo '</div>';
    }

    public static function media_image($args = array()) {
        $name = '';
        $value = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        echo '<div class="sbtheme-media-image">';
        self::image_thumbnail($name, $value);
        self::media_upload($args);
        echo '</div>';
    }

    public static function widget_area($args = array()) {
        $id = '';
        $name = '';
        $list_sidebars = array();
        $description = '';
        $default_sidebars = array();
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        ?>
        <div id="<?php echo $id; ?>" class="sb-theme-sidebar">
            <div class="sb-sidebar-group">
                <ul id="sb-sortable-sidebar" class="sb-sortable-list" data-icon-drag="<?php echo SB_CORE_URL . '/images/icon-drag-16.png'; ?>" data-icon-delete="<?php echo SB_CORE_URL . '/images/icon-delete-16.png'; ?>" data-sidebar="<?php echo count($list_sidebars); ?>" data-message-confirm="<?php _e('Are you sure you want to delete?', 'sb-core'); ?>" data-name="<?php echo $name; ?>">
                    <li class="ui-state-disabled sb-default-sidebar">
                        <div class="sb-sidebar-line">
                            <input type="text" name="sidebar_default_0_name" value="<?php _e('Sidebar name', 'sb-core'); ?>" autocomplete="off" disabled>
                            <input type="text" name="sidebar_default_0_description" value="<?php _e('Sidebar description', 'sb-core'); ?>" autocomplete="off" disabled>
                            <input type="text" name="sidebar_default_0_id" value="<?php _e('Sidebar id', 'sb-core'); ?>" autocomplete="off" disabled>
                        </div>
                        <img class="sb-icon-drag" src="<?php echo SB_CORE_URL . '/images/icon-drag-16.png'; ?>">
                        <img class="sb-icon-delete" src="<?php echo SB_CORE_URL . '/images/icon-delete-16.png'; ?>">
                    </li>
                    <?php $count = 1; foreach($default_sidebars as $value) : ?>
                        <li class="ui-state-disabled sb-default-sidebar">
                            <div class="sb-sidebar-line">
                                <input type="text" name="sidebar_default_<?php echo $count; ?>_name" value="<?php echo $value['name']; ?>" autocomplete="off" disabled>
                                <input type="text" name="sidebar_default_<?php echo $count; ?>_description" value="<?php echo $value['description']; ?>" autocomplete="off" disabled>
                                <input type="text" name="sidebar_default_<?php echo $count; ?>_id" value="<?php echo $value['id']; ?>" autocomplete="off" disabled>
                            </div>
                            <img class="sb-icon-drag" src="<?php echo SB_CORE_URL . '/images/icon-drag-16.png'; ?>">
                            <img class="sb-icon-delete" src="<?php echo SB_CORE_URL . '/images/icon-delete-16.png'; ?>">
                        </li>
                    <?php $count++; endforeach; ?>
                    <?php $count = 1; foreach($list_sidebars as $sidebar) : ?>
                        <li class="ui-state-default sb-user-sidebar" data-sidebar="<?php echo $count; ?>">
                            <div class="sb-sidebar-line">
                                <input type="text" name="<?php echo $name . '[' . $count . '][name]'; ?>" value="<?php echo $sidebar['name']; ?>" autocomplete="off">
                                <input type="text" name="<?php echo $name . '[' . $count . '][description]'; ?>" value="<?php echo $sidebar['description']; ?>" autocomplete="off">
                                <input type="text" name="<?php echo $name . '[' . $count . '][id]'; ?>" value="<?php echo $sidebar['id']; ?>" autocomplete="off">
                            </div>
                            <img class="sb-icon-drag" src="<?php echo SB_CORE_URL . '/images/icon-drag-16.png'; ?>">
                            <img class="sb-icon-delete" src="<?php echo SB_CORE_URL . '/images/icon-delete-16.png'; ?>">
                        </li>
                    <?php $count++; endforeach; ?>
                </ul>
                <input type="hidden" name="<?php echo $name; ?>[count]" value="<?php echo count($list_sidebars); ?>" class="sb-sidebar-count">
            </div>
            <button class="button sb-add-sidebar"><?php _e('Add new sidebar', 'sb-core'); ?></button>
        </div>
        <?php
    }

    public static function rss_feed($args = array()) {
        $id = '';
        $name = '';
        $list_feeds = array();
        $description = '';
        $order = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        $count = SB_Option::get_theme_option(array('keys' => array('rss_feed', 'count')));
        if($count > count($list_feeds)) {
            $count = count($list_feeds);
        }
        $real_count = $count;
        $next_id = 1;

        ?>
        <div id="<?php echo $id; ?>" class="sb-addable rss-feed min-height relative gray-bg border padding-10 sb-ui-panel">
            <div class="item-group">
                <ul class="sb-sortable-list" data-message-confirm="<?php _e('Are you sure you want to delete?', 'sb-core'); ?>">
                    <?php if($count == 0) : ?>
                        <?php $count++; ?>
                        <?php SB_Admin_Custom::set_current_rss_feed_item(array('name' => $name, 'count' => $count)); ?>
                        <?php sb_get_core_template_part('loop-rss-feed'); ?>
                        <?php $real_count = $count; ?>
                        <?php $order = $count; ?>
                        <?php $next_id++; ?>
                    <?php endif; ?>
                    <?php if($count > 0) : ?>
                        <?php $new_count = 1; ?>
                        <?php foreach($list_feeds as $feed) : ?>
                            <?php
                            $feed_id = isset($feed['id']) ? $feed['id'] : 0;
                            if($feed_id >= $next_id) {
                                $next_id = $feed_id + 1;
                            }
                            ?>
                            <?php SB_Admin_Custom::set_current_rss_feed_item(array('feed' => $feed, 'count' => $new_count, 'name' => $name)); ?>
                            <?php sb_get_core_template_part('loop-rss-feed'); ?>
                            <?php $new_count++; ?>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <input type="hidden" name="<?php echo $name; ?>[order]" value="<?php echo $order; ?>" class="ui-item-order item-order" autocomplete="off">
                <input type="hidden" name="<?php echo $name; ?>[count]" value="<?php echo $real_count; ?>" class="ui-item-count item-count" autocomplete="off">
            </div>
            <button class="button add-item ui-add-item absolute" data-type="rss_feed" data-name="<?php echo $name; ?>" data-count="<?php echo $count; ?>" data-next-id="<?php echo $next_id; ?>"><?php _e('Add new', 'sb-core'); ?></button>
            <button class="button reset-item ui-reset-item absolute reset" data-type="rss_feed"><?php _e('Reset', 'sb-core'); ?> <img src="<?php echo SB_CORE_URL; ?>/images/ajax-loader.gif"></button>
        </div>
        <?php if(!empty($description)) : ?>
            <p class="description"><?php _e($description, 'sb-core'); ?></p>
        <?php endif; ?>
        <?php
    }

    private static function media_upload($args = array()) {
        $id = '';
        $name = '';
        $description = '';
        $value = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        $button_title = __('Insert image', 'sb-core');
        $value = trim($value);
        ?>
        <div class="sbtheme-upload media">
            <input type="text" id="<?php echo $id; ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>">
            <a title="<?php echo $button_title; ?>" data-editor="sb-content" class="sb-button button sb-insert-media sb-add_media" href="javascript:void(0);"><?php _e('Upload', 'sb-core'); ?></a>
        </div>
        <p class="description"><?php echo $description; ?></p>
        <?php
    }

    public static function media_image_with_url($args = array()) {
        $name = '';
        $value = '';
        echo '<div class="sbtheme-media-image with-url">';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        self::image_thumbnail($name, $value);
        self::media_upload_with_url($args);
        echo '</div>';
    }

    private static function media_upload_with_url($args = array()) {
        $new_args = $args;
        $new_args['id'] = (isset($args['id']) ? $args['id'] : '') . '_image';
        $new_args['name'] = (isset($args['name']) ? $args['name'] : '') . '[image]';
        self::media_upload($new_args);
        $args['id'] = (isset($args['id']) ? $args['id'] : '') . '_url';
        $args['name'] = (isset($args['name']) ? $args['name'] : '') . '[url]';
        $options = SB_Option::get();
        $keys = explode(']', $args['name']);
        $first = str_replace('sb_options[', '', $keys[0]);
        $second = str_replace('[', '', $keys[1]);
        $third = str_replace('[', '', $keys[2]);
        $value = isset($options[$first][$second][$third]) ? $options[$first][$second][$third] : '';
        $description = __('Enter url for the image above.', 'sb-core');
        echo '<div style="margin-top: 20px; ">';
        self::text_field($args);
        echo '</div>';
    }

    public static function text_field($args = array()) {
        $id = '';
        $name = '';
        $value = '';
        $description = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        $value = trim($value);
        $class = 'widefat';
        printf('<input type="text" id="%1$s" name="%2$s" value="%3$s" class="'.$class.'"><p class="description">%4$s</p>', esc_attr($id), esc_attr($name), $value, __($description, 'sb-core'));
    }

    public static function number_field($args = array()){
        $id = '';
        $name = '';
        $value = '';
        $description = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        $value = trim($value);
        $class = '';
        printf('<input type="number" id="%1$s" name="%2$s" value="%3$s" class="'.$class.'"><p class="description">%4$s</p>', esc_attr($id), esc_attr($name), $value, __($description, 'sb-core'));
    }

    public static function switch_button($args = array()) {
        $id = '';
        $name = '';
        $value = '';
        $description = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        $enable = (bool) $value;
        $class = 'switch-button';
        $class_on = $class . ' on';
        $class_off = $class . ' off';
        if($enable) {
            $class_on .= ' active';
        } else {
            $class_off .= ' active';
        }
        ?>
        <fieldset class="sbtheme-switch">
            <div class="switch-options">
                <label data-switch="on" class="<?php echo $class_on; ?> left"><span><?php _e('On', 'sb-core'); ?></span></label>
                <label data-switch="off" class="<?php echo $class_off; ?> right"><span><?php _e('Off', 'sb-core'); ?></span></label>
                <input type="hidden" value="<?php echo $value; ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo $id; ?>" class="checkbox checkbox-input">
                <p class="description"><?php echo $description; ?></p>
            </div>
        </fieldset>
        <?php
    }

    public static function select_term_field($args = array()) {
        $paragraph_class = '';
        $id = '';
        $name = '';
        $field_class = '';
        $label_text = '';
        $list_options = array();
        $value = '';
        $description = '';
        $taxonomy = '';
        $taxonomy_id = '';
        $taxonomy_name = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
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
            $new_args = array(
                'id' => $id,
                'name' => $name,
                'value' => $value,
                'description' => $description
            );
            self::text_field($new_args);
            echo '</div>';
        }
    }

    public static function rich_editor_field($args = array()) {
        $id = '';
        $name = '';
        $value = '';
        $description = '';
        $textarea_row = 5;
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        $args = array(
            'textarea_name' => $name,
            'textarea_rows' => $textarea_row
        );
        ?>
        <div id="<?php echo $id . '_editor'; ?>" class="sb-rich-editor">
            <?php wp_editor($value, $id, $args); ?>
            <?php if(!empty($description)) : ?>
            <p class="description"><?php echo $description; ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}