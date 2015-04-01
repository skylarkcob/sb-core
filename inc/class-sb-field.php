<?php
class SB_Field {

    private static function image_thumbnail($args = array()) {
        self::uploaded_image_preview($args);
    }

    private static function uploaded_image_preview($args = array()) {
        $value = isset($args['value']) ? $args['value'] : '';
        $preview = isset($args['preview']) ? $args['preview'] : true;
        $image_preview = '';
        $image_preview_class = 'image-preview';
        if(!empty($value)) {
            $image_preview = sprintf('<img src="%s">', $value);
            $image_preview_class .= ' has-image';
        }
        if($preview) {
            echo '<div class="' . $image_preview_class . '">' . $image_preview . '</div>';
        }
    }

    public static function media_image($args = array()) {
        self::media_upload_with_remove_and_preview($args);
    }

    public static function media_upload_no_preview($args = array()) {
        $args['preview'] = false;
        self::media_upload_with_remove_and_preview($args);
    }

    private static function media_upload($args = array()) {
        self::media_image($args);
    }

    public static function media_image_with_url($args = array()) {
        self::media_upload_with_url($args);
    }

    private static function media_upload_with_url($args = array()) {
        $new_args = $args;
        $id = isset($args['id']) ? $args['id'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $names = explode(']', $name);
        $keys = array();
        foreach($names as $name_item) {
            $item = str_replace('sb_options[', '', $name_item);
            $item = str_replace('[', '', $item);
            if(empty($item)) {
                continue;
            }
            array_push($keys, $item);
        }
        $image_keys = $keys;
        array_push($image_keys, 'image');
        if(!empty($id)) {
            $new_args['id'] = $id . '_image';
        }
        $new_args['name'] = $name . '[image]';
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'margin-bottom');
        $value = SB_Option::get_by_key(array('keys' => $image_keys));
        $new_args['container_class'] = $container_class;
        $new_args['value'] = $value;
        self::media_upload($new_args);
        if(!empty($id)) {
            $args['id'] = $id . '_url';
        }
        array_push($keys, 'url');
        $value = SB_Option::get_by_key(array('keys' => $keys));
        $description = __('Enter url for the image above.', 'sb-core');
        $args['before'] = '<div class="margin-top">';
        $args['name'] = $name . '[url]';
        $args['value'] = $value;
        $args['description'] = $description;
        self::text_field($args);
    }

    public static function select_gender($args = array()) {
        $value = isset($args['value']) ? intval($args['value']) : 0;
        $all_option = '<option value="0" ' . selected(0, $value, false) . '>' . __('Male', 'sb-login-page') . '</option>';
        $all_option .= '<option value="0" ' . selected(1, $value, false) . '>' . __('Female', 'sb-login-page') . '</option>';
        $args['all_option'] = $all_option;
        self::select($args);
    }

    public static function get_option($args = array()) {
        $value = isset($args['value']) ? $args['value'] : '';
        $text = isset($args['text']) ? $args['text'] : '';
        $selected = isset($args['selected']) ? $args['selected'] : '';
        return '<option value="' . esc_attr($value) . '" ' . selected($value, $selected, false) . '>' . $text . '</option>';
    }

    public static function option($args = array()) {
        echo self::get_option($args);
    }

    public static function select_birthday($args = array()) {
        $lang = isset($args['language']) ? $args['language'] : 'en';
        $birthday = isset($args['value']) ? $args['value'] : strtotime(SB_Core::get_current_datetime());
        $birth_day = intval(date('d', $birthday));
        $birth_month = intval(date('m', $birthday));
        $birth_year = intval(date('Y', $birthday));
        $sep = isset($args['sep']) ? $args['sep'] : '<span class="sep">/</span>';
        $year_max = intval(date('Y')) - 13;
        $year_min = $year_max - 150;
        $all_option_day = '<option value="0">' . __('Choose day', 'sb-core') . '</option>';
        for($i = 1; $i <= 31; $i++) {
            $all_option_day .= self::get_option(array('text' => sprintf('%02d', $i), 'value' => $i, 'selected' => $birth_day));
        }
        $all_option_month = '<option value="0">' . __('Choose month', 'sb-core') . '</option>';
        for($i = 1; $i <= 12; $i++) {
            $all_option_month .= self::get_option(array('text' => sprintf('%02d', $i), 'value' => $i, 'selected' => $birth_month));
        }
        $all_option_year = '<option value="0">' . __('Choose year', 'sb-core') . '</option>';
        for($i = $year_max; $i >= $year_min; $i--) {
            $all_option_year .= self::get_option(array('text' => sprintf('%02d', $i), 'value' => $i, 'selected' => $birth_year));
        }
        if($birth_year < $year_min || $birth_year > $year_max) {
            $all_option_year .= self::get_option(array('text' => $birth_year, 'value' => $birth_year, 'selected' => $birth_year));
        }
        $args['before'] = '';
        if('vi' == $lang) {
            $args['all_option'] = $all_option_day;
            self::select($args);
            echo $sep;
            $args['all_option'] = $all_option_month;
            self::select($args);
            echo $sep;
            $args['all_option'] = $all_option_year;
            self::select($args);
        } else {
            $args['all_option'] = $all_option_year;
            self::select($args);
            echo $sep;
            $args['all_option'] = $all_option_month;
            self::select($args);
            echo $sep;
            $args['all_option'] = $all_option_day;
            self::select($args);
        }
    }

    public static function media_upload_group($args = array()) {
        $name = isset($args['name']) ? trim($args['name']) : '';
        if(empty($name)) {
            return;
        }
        $name = sb_build_meta_name($name);
        $value = isset($args['value']) ? trim($args['value']) : '';
        $field_class = isset($args['field_class']) ? trim($args['field_class']) : '';
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'image-url image-upload-url');
        $upload_button_class = isset($args['upload_button_class']) ? trim($args['upload_button_class']) : '';
        $remove_button_class = isset($args['remove_button_class']) ? trim($args['remove_button_class']) : '';
        $upload_button_class = SB_PHP::add_string_with_space_before($upload_button_class, 'sb-button button sb-insert-media sb-add_media');
        $remove_button_class = SB_PHP::add_string_with_space_before($remove_button_class, 'sb-button button sb-remove-media sb-remove-image');
        if(!isset($args['before'])) {
            $args['before'] = '';
        }
        self::media_upload_no_preview($args);
    }

    public static function fieldset($args = array()) {
        $label = isset($args['label']) ? $args['label'] : '';
        $callback = isset($args['callback']) ? $args['callback'] : '';
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        ?>
        <fieldset class="<?php echo $container_class; ?>">
            <legend><?php echo $label; ?></legend>
            <?php call_user_func($callback); ?>
        </fieldset>
    <?php
    }

    public static function size($args = array()) {
        $field_class = isset($args['field_class']) ? $args['field_class'] : '';
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $id = isset($args['id']) ? $args['id'] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        $id_width = isset($args['id_width']) ? $args['id_width'] : '';
        $id_height = isset($args['id_height']) ? $args['id_height'] : '';
        $name_width = isset($args['name_width']) ? $args['name_width'] : '';
        $name_height = isset($args['name_height']) ? $args['name_height'] : '';
        $value = isset($args['value']) ? $args['value'] : array();
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'sb-number image-size');
        $before = isset($args['before']) ? $args['before'] : '<p class="' . esc_attr($container_class) . '">';
        $after = isset($args['after']) ? $args['after'] : '';
        $sep = isset($args['sep']) ? $args['sep'] : '<span>x</span>';
        echo $before;
        self::label(array('for' => $id, 'text' => $label));
        $input_args = array(
            'type' => 'number',
            'id' => $id_width,
            'field_class' => $field_class,
            'name' => $name_width,
            'autocomplete' => false,
            'value' => $value[0],
            'only' => true,
        );
        self::text($input_args);
        echo $sep;
        $input_args['id'] = $id_height;
        $input_args['name'] = $name_height;
        $input_args['value'] = $value[1];
        self::text($input_args);
        self::the_description($description);
        echo $after;
    }

    public static function media_upload_with_remove_and_preview($args = array()) {
        $name = isset($args['name']) ? $args['name'] : '';
        $value = isset($args['value']) ? $args['value'] : '';
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        $preview = isset($args['preview']) ? $args['preview'] : true;
        $id = isset($args['id']) ? $args['id'] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        $upload_button_class = isset($args['upload_button_class']) ? $args['upload_button_class'] : '';
        $remove_button_class = isset($args['remove_button_class']) ? $args['remove_button_class'] : '';
        if(empty($id) || empty($name)) {
            return;
        }
        $image_preview = '';
        $media_detail = SB_Option::get_media_detail($value);
        $value_id = $media_detail['id'];
        $value_url = $media_detail['url'];
        $image_preview_class = 'image-preview';
        if(!empty($value_url)) {
            $image_preview = sprintf('<img src="%s">', $value_url);
            $image_preview_class .= ' has-image';
        }
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'sb-media-upload');
        $upload_button_class = SB_PHP::add_string_with_space_before($upload_button_class, 'sb-button button sb-insert-media sb-add_media');
        $remove_button_class = SB_PHP::add_string_with_space_before($remove_button_class, 'sb-button button sb-remove-media sb-remove-image');
        $image_preview_div = '<div class="' . $image_preview_class . '">' . $image_preview . '</div>';
        $before = isset($args['before']) ? $args['before'] : '<div class="' . $container_class . '"><div class="image-upload-container">';
        $after = isset($args['after']) ? $args['after'] : '</div></div>';
        echo $before;
        if($preview) {
            echo $image_preview_div;
        }
        $args['name'] = $name . '[url]';
        $args['before'] = '';
        $args['description'] = '';
        $args['value'] = $value_url;
        $args['field_class'] = 'image-url';
        $args['autocomplete'] = false;
        self::text($args);

        $args['label'] = '';
        $args['name'] = $name . '[id]';
        $args['field_class'] = 'media-id';
        $args['type'] = 'hidden';
        $args['value'] = $value_id;
        self::text($args);
        $html = new SB_HTML('a');
        $atts = array(
            'href' => 'javascript:;',
            'class' => $upload_button_class,
            'title' => __('Insert image', 'sb-core'),
            'text' => __('Upload', 'sb-core')
        );
        $html->set_attribute_array($atts);
        echo $html->build();
        $atts = array(
            'href' => 'javascript:;',
            'class' => $remove_button_class,
            'title' => __('Remove image', 'sb-core'),
            'text' => __('Remove', 'sb-core')
        );
        $html->set_attribute_array($atts);
        echo $html->build();
        self::the_after($before, $after);
    }

    public static function widget_area($args = array()) {
        $id = isset($args['id']) ? $args['id'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $list_sidebars = isset($args['list_sidebars']) ? $args['list_sidebars'] : array();
        $description = isset($args['description']) ? $args['description'] : '';
        $default_sidebars = isset($args['default_sidebars']) ? $args['default_sidebars'] : array();
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
    
    public static function sortble_term($args = array()) {
        $option_name = isset($args['option_name']) ? $args['option_name'] : '';
        if(empty($option_name)) {
            $option_name = isset($args['name']) ? $args['name'] : '';
        }
        $sortable_class = isset($args['sortable_class']) ? $args['sortable_class'] : '';
        $sortable_active_class = isset($args['sortable_active_class']) ? $args['sortable_active_class'] : '';
        $term_args = isset($args['term_args']) ? $args['term_args'] : array();
        $taxonomy = isset($args['taxonomy']) ? $args['taxonomy'] : '';
        if(empty($option_name) || empty($taxonomy)) {
            return;
        }
        $sortable_class = SB_PHP::add_string_with_space_before($sortable_class, 'connected-sortable sb-sortable-list left min-height sortable-source');
        $sortable_active_class = SB_PHP::add_string_with_space_before($sortable_active_class, 'connected-sortable active-sortable sb-sortable-list min-height right');
        $active_terms = SB_Option::get_theme_option_single_key($option_name);
        $term_args['exclude'] = $active_terms;
        $terms = SB_Term::get($taxonomy, $term_args);
        $base_key = isset($args['base_key']) ? $args['base_key'] : 'theme';
        ?>
        <div class="sb-sortable">
            <div class="sb-sortable-container">
                <ul class="<?php echo $sortable_class; ?>">
                    <?php foreach($terms as $term) : ?>
                        <li data-term="<?php echo $term->term_id; ?>" class="ui-state-default"><?php echo $term->name; ?></li>
                    <?php endforeach; ?>
                </ul>
                <ul class="<?php echo $sortable_active_class; ?>">
                    <?php $terms = $active_terms; $active_terms = explode(',', $active_terms); ?>
                    <?php foreach($active_terms as $term_id) : if($term_id < 1) continue; $term = get_term($term_id, $taxonomy); ?>
                        <?php if(!$term) continue; ?>
                        <li data-term="<?php echo $term->term_id; ?>" class="ui-state-default"><?php echo $term->name; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <input type="hidden" class="active-sortalbe-value" name="sb_options[<?php echo $base_key; ?>][<?php echo $option_name; ?>]" value="<?php echo $terms; ?>">
        </div>
        <div style="clear: both"></div>
        <?php
        self::the_description(__('Drag and drop the widget into right box to active it.', 'sb-theme'));
    }

    public static function rss_feed($args = array()) {
        $id = isset($args['id']) ? $args['id'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $list_feeds = isset($args['list_feeds']) ? $args['list_feeds'] : array();
        $description = isset($args['description']) ? $args['description'] : '';
        $order = isset($args['order']) ? $args['order'] : '';
        $count = SB_Option::get_theme_option(array('keys' => array('rss_feed', 'count')));
        if($count > count($list_feeds)) {
            $count = count($list_feeds);
        }
        $real_count = $count;
        $next_id = 1;
        ?>
        <div id="<?php echo esc_attr($id); ?>" class="sb-addable rss-feed min-height relative gray-bg border padding-10 sb-ui-panel">
            <div class="item-group">
                <ul class="sb-sortable-list" data-message-confirm="<?php echo SB_Message::get_confirm_delete_text(); ?>">
                    <?php
                    if(0 == $count) {
                        $count++;
                        SB_Admin_Custom::set_current_rss_feed_item(array('name' => $name, 'count' => $count));
                        sb_core_get_loop('loop-rss-feed');
                        $real_count = $count;
                        $order = $count;
                        $next_id++;
                    }
                    if(0 < $count) {
                        $new_count = 1;
                        foreach($list_feeds as $feed) {
                            $feed_id = isset($feed['id']) ? $feed['id'] : 0;
                            if($feed_id >= $next_id) {
                                $next_id = $feed_id + 1;
                                SB_Admin_Custom::set_current_rss_feed_item(array('feed' => $feed, 'count' => $new_count, 'name' => $name));
                                sb_core_get_loop('loop-rss-feed');
                                $new_count++;
                            }
                        }
                    }
                    ?>
                </ul>
                <input type="hidden" name="<?php echo $name; ?>[order]" value="<?php echo $order; ?>" class="ui-item-order item-order" autocomplete="off">
                <input type="hidden" name="<?php echo $name; ?>[count]" value="<?php echo $real_count; ?>" class="ui-item-count item-count" autocomplete="off">
            </div>
            <button class="button add-item ui-add-item absolute" data-type="rss_feed" data-name="<?php echo $name; ?>" data-count="<?php echo $count; ?>" data-next-id="<?php echo $next_id; ?>"><?php _e('Add new', 'sb-core'); ?></button>
            <button class="button reset-item ui-reset-item absolute reset" data-type="rss_feed"><?php _e('Reset', 'sb-core'); ?> <img src="<?php echo SB_CORE_URL; ?>/images/ajax-loader.gif"></button>
        </div>
        <?php
        self::the_description($description);
    }

    public static function text_field($args = array()) {
        self::text($args);
    }

    public static function set_attributes($html, $attributes) {
        foreach($attributes as $key => $att) {
            $html->set_attribute($key, $att);
        }
        return $html;
    }

    public static function text($args = array()) {
        $type = isset($args['type']) ? $args['type'] : 'text';
        $id = isset($args['id']) ? $args['id'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $value = isset($args['value']) ? $args['value'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $field_class = isset($args['field_class']) ? $args['field_class'] : '';
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        $value = trim($value);
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'sb-' . $type . '-field');
        $widefat = isset($args['widefat']) ? $args['widefat'] : true;
        if($widefat) {
            $field_class = SB_PHP::add_string_with_space_before($field_class, 'widefat');
        }
        $label = isset($args['label']) ? $args['label'] : '';
        $after = isset($args['after']) ? $args['after'] : '</div>';

        $autocomplete = isset($args['autocomplete']) ? $args['autocomplete'] : true;
        $before = isset($args['before']) ? $args['before'] : '<div class="' . esc_attr($container_class) . '">';
        $only = isset($args['only']) ? $args['only'] : false;
        $html = new SB_HTML('input');
        $atts = array(
            'type' => esc_attr($type),
            'id' => esc_attr($id),
            'name' => esc_attr($name),
            'value' => esc_attr($value),
            'autocomplete' => (bool)$autocomplete ? '' : 'off',
            'class' => esc_attr($field_class)
        );
        $html->set_attribute_array($atts);
        $attributes = isset($args['attributes']) ? $args['attributes'] : array();
        $html = self::set_attributes($html, $attributes);
        if($only) {
            echo $html->build();
        } else {
            echo $before;
            if(!empty($label) && 'checkbox' != $type && 'radio' != $type) {
                self::label(array('text' => $label, 'for' => 'id'));
            }
            echo $html->build();
            if('checkbox' == $type || 'radio' == $type) {
                self::label(array('text' => $label, 'for' => 'id', 'attributes' => array('class' => $type . '-label')));
            }
            self::the_description($description);
            self::the_after($before, $after);
        }
    }

    public static function textarea($args = array()) {
        $id = isset($args['id']) ? $args['id'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $value = isset($args['value']) ? $args['value'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $field_class = isset($args['field_class']) ? $args['field_class'] : '';
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        $value = trim($value);
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'sb-textarea-field');
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'widefat');
        $after = isset($args['after']) ? $args['after'] : '</div>';
        $type = isset($args['type']) ? $args['type'] : 'text';
        $autocomplete = isset($args['autocomplete']) ? $args['autocomplete'] : true;
        $before = isset($args['before']) ? $args['before'] : '<div class="' . esc_attr($container_class) . '">';
        $row = isset($args['row']) ? $args['row'] : 4;
        echo $before;
        $html = new SB_HTML('textarea');
        $atts = array(
            'type' => esc_attr($type),
            'id' => esc_attr($id),
            'name' => esc_attr($name),
            'text' => esc_attr($value),
            'autocomplete' => (bool)$autocomplete ? 'off' : '',
            'class' => esc_attr($field_class),
            'rows' => esc_attr($row)
        );
        $html->set_attribute_array($atts);
        echo $html->build();
        self::the_description($description);
        self::the_after($before, $after);
    }

    public static function number_field($args = array()){
        self::number($args);
    }

    public static function number($args = array()) {
        $args['type'] = 'number';
        self::text($args);
    }

    public static function checkbox($args = array()) {
        $args['type'] = 'checkbox';
        self::text($args);
    }

    public static function switch_button($args = array()) {
        $id = isset($args['id']) ? $args['id'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $value = isset($args['value']) ? $args['value'] : 0;
        $description = isset($args['description']) ? $args['description'] : '';

        $enable = (bool) $value;
        $class = 'switch-button';
        $class_on = $class . ' on';
        $class_off = $class . ' off';
        if($enable) {
            $class_on .= ' active';
        } else {
            $class_off .= ' active';
        }
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        $container_class .= ' sbtheme-switch';
        $before = isset($args['before']) ? $args['before'] : '<fieldset class="' . esc_attr($container_class) . '"><div class="switch-options">';
        $after = isset($args['after']) ? $args['after'] : '</div></fieldset>';
        echo $before;
        $attributes = array(
            'data-switch' => 'on',
            'class' => $class_on . ' left'
        );
        self::label(array('text' => '<span>' . __('On', 'sb-core') . '</span>', 'attributes' => $attributes));
        $attributes = array(
            'data-switch' => 'off',
            'class' => $class_off . ' right'
        );
        self::label(array('text' => '<span>' . __('Off', 'sb-core') . '</span>', 'attributes' => $attributes));
        $args['type'] = 'hidden';
        $args['only'] = true;
        $args['field_class'] = 'checkbox checkbox-input';
        $args['value'] = $value;
        $args['autocomplete'] = false;
        self::text($args);
        self::the_description($description);
        echo $after;
    }

    public static function button($args = array()) {
        $text = isset($args['text']) ? $args['text'] : '';
        if(empty($text)) {
            return;
        }
        $class = isset($args['field_class']) ? $args['field_class'] : '';
        $class = SB_PHP::add_string_with_space_before($class, 'sb-button');
        $description = isset($args['description']) ? $args['description'] : '';
        echo '<button class="' . esc_attr($class) . '">' . $text . '</button>';
        self::the_description($description);
    }

    public static function the_description($text) {
        if(!empty($text)) {
            echo '<p class="description">' . $text . '</p>';
        }
    }

    public static function select($args = array()) {
        $id = isset($args['id']) ? $args['id'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $list_options = isset($args['list_options']) ? $args['list_options'] : array();
        $options = isset($args['options']) ? $args['options'] : array();
        $description = isset($args['description']) ? $args['description'] : '';
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        $value = isset($args['value']) ? $args['value'] : '';
        $field_class = isset($args['field_class']) ? $args['field_class'] : '';
        if(!is_array($options) || count($options) < 1) {
            $options = $list_options;
        }
        $all_option = isset($args['all_option']) ? $args['all_option'] : '';
        $before = isset($args['before']) ? $args['before'] : '<div class="' . $container_class . '">';
        $after = isset($args['after']) ? $args['after'] : '</div>';
        echo $before;
        $autocomplete = isset($args['autocomplete']) ? '' : 'off';
        $select_option = isset($args['default_option']) ? $args['default_option'] : '';
        if(empty($all_option)) {
            foreach($options as $key => $text) {
                $select_option .= self::get_option(array('value' => $key, 'text' => $text, 'selected' => $value));
            }
        } else {
            $select_option .= $all_option;
        }
        $html = new SB_HTML('select');
        $atts = array(
            'id' => esc_attr($id),
            'name' => $name,
            'class' => $field_class,
            'autocomplete' => $autocomplete,
            'text' => $select_option
        );
        $html->set_attribute_array($atts);
        echo $html->build();
        self::the_description($description);
        self::the_after($before, $after);
    }

    public static function select_page($args = array()) {
        $pages = SB_Post::get_all('page');
        $all_option = '<option value="0">' . __('Choose page', 'sb-core') . '</option>';
        $value = isset($args['value']) ? $args['value'] : '';
        while($pages->have_posts()) {
            $pages->the_post();
            $post_id = get_the_ID();
            $all_option .= '<option value="' . esc_attr($post_id) . '" ' . selected($value, $post_id, false) . '>' . get_the_title() . '</option>';
        }
        wp_reset_postdata();
        $args['all_option'] = $all_option;
        self::select($args);
    }

    public static function select_term_field($args = array()) {
        self::select_term($args);
    }

    public static function label($args = array()) {
        $text = isset($args['text']) ? $args['text'] : '';
        if(empty($text)) {
            return;
        }
        $html = new SB_HTML('label');
        $atts = array(
            'for' => isset($args['for']) ? $args['for'] : '',
            'text' => isset($args['text']) ? $args['text'] : ''
        );
        $html->set_attribute_array($atts);
        $attributes = isset($args['attributes']) ? $args['attributes'] : array();
        $html = self::set_attributes($html, $attributes);
        echo $html->build();
    }

    public static function select_term($args = array()) {
        $container_class = isset($args['container_class']) ? $args['container_class'] : '';
        $id = isset($args['id']) ? $args['id'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $field_class = isset($args['field_class']) ? $args['field_class'] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        $options = isset($args['options']) ? $args['options'] : array();
        $value = isset($args['value']) ? $args['value'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $taxonomy = isset($args['taxonomy']) ? $args['taxonomy'] : '';
        $taxonomy_id = isset($args['taxonomy_id']) ? $args['taxonomy_id'] : '';
        $taxonomy_name = isset($args['taxonomy_name']) ? $args['taxonomy_name'] : '';
        $show_count = isset($args['show_count']) ? $args['show_count'] : true;
        $before = isset($args['before']) ? $args['before'] : '<p class="' . esc_attr($container_class) . '">';
        $after = isset($args['after']) ? $args['after'] : '</p>';
        echo $before;
        self::label(array('for' => $id, 'text' => $label));
        $all_option = '<option value="0">' . __('Choose term', 'sb-core') . '</option>';
        $args['before'] = '';
        if(count($options) > 0) {
            foreach($options as $tax) {
                $terms = get_terms($tax->name);
                if(!SB_Core::is_error($terms) && count($terms) > 0) {
                    $tmp = '<optgroup label="' . $tax->labels->name . '">';
                    foreach($terms as $cat) {
                        $option_text = $cat->name . ($show_count) ? ' (' . $cat->count . ')' : '';
                        $tmp .= self::get_option(array('value' => $cat->term_id, 'attributes' => array('data-taxonomy' => $tax->name), 'selected' => $value, 'text' => $option_text));
                    }
                    $tmp .= '</optgroup>';
                    $all_option .= $tmp;
                }
            }
        } else {
            $terms = SB_Term::get($taxonomy);
            if(!SB_Core::is_error($terms) && count($terms) > 0) {
                foreach($terms as $cat) {
                    $all_option .= self::get_option(array('value' => $cat->term_id, 'attributes' => array('data-taxonomy' => $tax->name), 'selected' => $value, 'text' => $option_text));
                }
            }
        }
        $args['all_option'] = $all_option;
        self::select($args);
        if(!empty($taxonomy_name)) {
            $args['id'] = $taxonomy_id;
            $args['name'] = $taxonomy_name;
            $args['value'] = $taxonomy;
            $args['field_class'] = 'widefat taxonomy';
            $args['type'] = 'hidden';
            self::text($args);
        }
        echo $after;
    }

    public static function social_field($args = array()) {
        self::social($args);
    }

    public static function social($args = array()) {
        foreach($args as $field) {
            $id = isset($field['id']) ? $field['id'] : '';
            $name = isset($field['name']) ? $field['name'] : '';
            $value = isset($field['value']) ? $field['value'] : '';
            if(empty($name)) {
                continue;
            }
            $description = isset($field['description']) ? $field['description'] : '';
            $new_args = array(
                'before' => '<div class="margin-bottom">',
                'id' => $id,
                'name' => $name,
                'value' => $value,
                'description' => $description
            );
            self::text_field($new_args);
        }
    }

    public static function rich_editor_field($args = array()) {
        self::rich_editor($args);
    }

    public static function rich_editor($args = array()) {
        $id = isset($args['id']) ? $args['id'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $value = isset($args['value']) ? $args['value'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $textarea_row = isset($args['textarea_row']) ? $args['textarea_row'] : 0;
        if(empty($textarea_row) || $textarea_row < 1) {
            $textarea_row = isset($args['row']) ? $args['row'] : 5;
        }
        $before = isset($args['before']) ? $args['before'] : '<div id="' . esc_attr($id) . '_editor" class="sb-rich-editor">';
        $after = isset($args['after']) ? $args['after'] : '</div>';
        $args = array(
            'textarea_name' => $name,
            'textarea_rows' => $textarea_row
        );
        echo $before;
        wp_editor($value, $id, $args);
        self::the_description($description);
        self::the_after($before, $after);
    }

    public static function color_picker($args) {
        $id = isset($args['id']) ? $args['id'] : '';
        $default = isset($args['default']) ? $args['default'] : '';
        $value = isset($args['value']) ? $args['value'] : '';
        $field_class = isset($args['field_class']) ? $args['field_class'] : '';
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'sb-color-picker');
        $description = isset($args['description']) ? $args['description'] : '';
        $colors = isset($args['colors']) ? (array)$args['colors'] : array();
        $colors = array_filter($colors);
        $before = isset($args['before']) ? $args['before'] : '<div id="' . esc_attr($id) . '" class="sb-color-options">';
        $after = isset($args['after']) ? $args['after'] : '</div>';
        $name = isset($args['name']) ? $args['name'] : '';
        echo $before;
        if(count($colors) > 0) {
            foreach($colors as $color) {
                $color_name = isset($color['name']) ? $name . '[' . $color['name'] . ']' : '';
                $color_value = isset($color['color']) ? $color['color'] : '';
                $color_default = isset($color['default']) ? $color['default'] : '';
                $color_description = isset($color['description']) ? $color['description'] : '';
                $args = array(
                    'before' => '<div class="color-area">',
                    'name' => $color_name,
                    'value' => $color_value,
                    'default' => $color_default,
                    'description' => $color_description
                );
                self::color_picker($args);
            }

        } else {
            $atts = array(
                'data-default-color' => $default
            );
            $args['attributes'] = $atts;
            self::text($args);
        }
        self::the_after($before, $after);
    }

    public static function the_after($before, $after) {
        if(!empty($before)) {
            echo $after;
        }
    }
}