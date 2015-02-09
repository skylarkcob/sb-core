<?php
class SB_Field {

    private static function image_thumbnail($args = array()) {
        self::uploaded_image_preview($args);
    }

    private static function uploaded_image_preview($args = array()) {
        $value = '';
        $preview = true;
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        $image_preview = '';
        $image_preview_class = 'image-preview';
        if(!empty($value)) {
            $image_preview = sprintf('<img src="%s">', $value);
            $image_preview_class .= ' has-image';
        }
        if($preview) : ?>
            <div class="<?php echo $image_preview_class; ?>"><?php echo $image_preview; ?></div>
        <?php endif;
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
        echo '<div class="margin-top">';
        $args['name'] = $name . '[url]';
        $args['value'] = $value;
        $args['description'] = $description;
        self::text_field($args);
        echo '</div>';
    }

    public static function select_gender($args = array()) {
        $field_class = isset($args['field_class']) ? $args['field_class'] : '';
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'sb-select-gender');
        $name = isset($args['name']) ? $args['name'] : '';
        $value = isset($args['value']) ? intval($args['value']) : 0;
        ?>
        <select class="<?php echo esc_attr($field_class); ?>" name="<?php echo esc_attr($name); ?>" autocomplete="off">
            <option value="0" <?php selected(0, $value); ?>><?php _e('Male', 'sb-login-page'); ?></option>
            <option value="1" <?php selected(1, $value); ?>><?php _e('Female', 'sb-login-page'); ?></option>
        </select>
        <?php
    }

    public static function select_birthday($args = array()) {
        $lang = isset($args['language']) ? $args['language'] : 'en';
        $birthday = isset($args['value']) ? $args['value'] : strtotime(SB_Core::get_current_datetime());
        $birth_day = intval(date('d', $birthday));
        $birth_month = intval(date('m', $birthday));
        $birth_year = intval(date('Y', $birthday));
        $year_max = intval(date('Y')) - 13;
        $year_min = $year_max - 150;
        ?>
        <?php if('vi' == $lang) : ?>
            <select class="user-birth-day" name="user_birth_day" autocomplete="off">
                <?php for($i = 1; $i <= 31; $i++) : ?>
                    <option value="<?php echo $i; ?>" <?php selected($birth_day, $i); ?>><?php printf('%02s', $i);; ?></option>
                <?php endfor; ?>
            </select>
            <span class="sep">/</span>
            <select class="user-birth-month" name="user_birth_month" autocomplete="off">
                <?php for($i = 1; $i <= 12; $i++) : ?>
                    <option value="<?php echo $i; ?>" <?php selected($birth_month, $i); ?>><?php printf('%02s', $i);; ?></option>
                <?php endfor; ?>
            </select>
            <span class="sep">/</span>
            <select class="user-birth-year" name="user_birth_year" autocomplete="off">
                <?php for($i = $year_max; $i >= $year_min; $i--) : ?>
                    <option value="<?php echo $i; ?>" <?php selected($birth_year, $i); ?>><?php echo $i; ?></option>
                <?php endfor; ?>
                <?php if($birth_year < $year_min || $birth_year > $year_max) : ?>
                    <option value="<?php echo $birth_year; ?>" selected><?php echo $birth_year; ?></option>
                <?php endif; ?>
            </select>
        <?php else : ?>
            <select class="user-birth-year" name="user_birth_year" autocomplete="off">
                <?php for($i = $year_max; $i >= $year_min; $i--) : ?>
                    <option value="<?php echo $i; ?>" <?php selected($birth_year, $i); ?>><?php echo $i; ?></option>
                <?php endfor; ?>
                <?php if($birth_year < $year_min || $birth_year > $year_max) : ?>
                    <option value="<?php echo $birth_year; ?>" selected><?php echo $birth_year; ?></option>
                <?php endif; ?>
            </select>
            <span class="sep">/</span>
            <select class="user-birth-month" name="user_birth_month" autocomplete="off">
                <?php for($i = 1; $i <= 12; $i++) : ?>
                    <option value="<?php echo $i; ?>" <?php selected($birth_month, $i); ?>><?php printf('%02s', $i); ?></option>
                <?php endfor; ?>
            </select>
            <span class="sep">/</span>
            <select class="user-birth-day" name="user_birth_day" autocomplete="off">
                <?php for($i = 1; $i <= 31; $i++) : ?>
                    <option value="<?php echo $i; ?>" <?php selected($birth_day, $i); ?>><?php printf('%02s', $i); ?></option>
                <?php endfor; ?>
            </select>
        <?php endif; ?>
        <?php
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
        ?>
        <input type="url" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>" autocomplete="off" class="<?php echo $field_class; ?>">
        <a href="javascript:;" class="<?php echo $upload_button_class; ?>" title="<?php _e('Insert image', 'sb-core'); ?>"><?php _e('Upload', 'sb-core'); ?></a>
        <a href="javascript:;" class="<?php echo $remove_button_class; ?>" title="<?php _e('Remove image', 'sb-core'); ?>"><?php _e('Remove', 'sb-core'); ?></a>
        <?php
    }

    public static function media_upload_with_remove_and_preview($args = array()) {
        $name = '';
        $value = '';
        $container_class = '';
        $preview = true;
        $id = '';
        $label = '';
        $upload_button_class = '';
        $remove_button_class = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        if(empty($id) || empty($name)) {
            return;
        }
        $image_preview = '';
        $image_preview_class = 'image-preview';
        if(!empty($value)) {
            $image_preview = sprintf('<img src="%s">', $value);
            $image_preview_class .= ' has-image';
        }
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'sb-media-upload');
        $upload_button_class = SB_PHP::add_string_with_space_before($upload_button_class, 'sb-button button sb-insert-media sb-add_media');
        $remove_button_class = SB_PHP::add_string_with_space_before($remove_button_class, 'sb-button button sb-remove-media sb-remove-image');
        ?>
        <div class="<?php echo $container_class; ?>">
            <?php if($preview) : ?>
                <div class="<?php echo $image_preview_class; ?>"><?php echo $image_preview; ?></div>
            <?php endif; ?>
            <div class="image-upload-container">
                <label for="<?php echo esc_attr($id); ?>"><?php echo $label; ?></label>
                <input type="url" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>" autocomplete="off" class="image-url">
                <a href="javascript:;" class="<?php echo $upload_button_class; ?>" title="<?php _e('Insert image', 'sb-core'); ?>"><?php _e('Upload', 'sb-core'); ?></a>
                <a href="javascript:;" class="<?php echo $remove_button_class; ?>" title="<?php _e('Remove image', 'sb-core'); ?>"><?php _e('Remove', 'sb-core'); ?></a>
            </div>
        </div>
        <?php
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
    
    public static function sortble_term($args = array()) {
        $option_name = '';
        $sortable_class = '';
        $sortable_active_class = '';
        $term_args = array();
        $taxonomy = '';
        if(is_array($args)) {
            extract($args);
        }
        if(empty($option_name) || empty($taxonomy)) {
            return;
        }
        $sortable_class = SB_PHP::add_string_with_space_before($sortable_class, 'connected-sortable sb-sortable-list left min-height sortable-source');
        $sortable_active_class = SB_PHP::add_string_with_space_before($sortable_active_class, 'connected-sortable active-sortable sb-sortable-list min-height right');
        $active_terms = SB_Option::get_theme_option_single_key($option_name);
        $term_args['exclude'] = $active_terms;
        $terms = SB_Term::get($taxonomy, $term_args);
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
            <input type="hidden" class="active-sortalbe-value" name="sb_options[theme][<?php echo $option_name; ?>]" value="<?php echo $terms; ?>">
        </div>
        <p class="description" style="clear: both"><?php _e('Drag and drop the widget into right box to active it.', 'sb-theme'); ?></p>
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
                        <?php sb_core_get_loop('loop-rss-feed'); ?>
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
                            <?php sb_core_get_loop('loop-rss-feed'); ?>
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

    public static function text_field($args = array()) {
        self::text($args);
    }

    public static function text($args = array()) {
        $id = '';
        $name = '';
        $value = '';
        $description = '';
        $field_class = '';
        $container_class = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        $value = trim($value);
        $container_class = SB_PHP::add_string_with_space_before($container_class, 'sb-text-field');
        $field_class = SB_PHP::add_string_with_space_before($field_class, 'widefat'); ?>
        <div class="<?php echo $container_class; ?>">
            <?php printf('<input type="text" id="%1$s" name="%2$s" value="%3$s" class="' . $field_class . '" autocomplete="off"><p class="description">%4$s</p>', esc_attr($id), esc_attr($name), $value, $description); ?>
        </div> <?php
    }

    public static function number_field($args = array()){
        self::number($args);
    }

    public static function number($args = array()) {
        $id = '';
        $name = '';
        $value = 0;
        $description = '';
        $field_class = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        printf('<input type="number" id="%1$s" name="%2$s" value="%3$s" class="' . $field_class . '" autocomplete="off"><p class="description">%4$s</p>', esc_attr($id), esc_attr($name), $value, $description);
    }

    public static function checkbox($args = array()) {
        $id = '';
        $name = '';
        $value = 0;
        $description = '';
        $label = '';
        $text = '';
        $field_class = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        if(!is_numeric($value)) {
            $value = 0;
        }
        if(empty($label)) {
            $label = $text;
        }
        printf('<input type="checkbox" id="%1$s" name="%2$s" value="%3$s" class="' . $field_class . '" %4$s autocomplete="off"> %5$s<p class="description">%6$s</p>', esc_attr($id), esc_attr($name), $value, checked($value, 1, false), $label, $value, $description);
    }

    public static function switch_button($args = array()) {
        $id = '';
        $name = '';
        $value = 0;
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
                <input type="hidden" value="<?php echo $value; ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo $id; ?>" class="checkbox checkbox-input" autocomplete="off">
                <p class="description"><?php echo $description; ?></p>
            </div>
        </fieldset>
        <?php
    }

    public static function button($args = array()) {
        $text = isset($args['text']) ? $args['text'] : '';
        if(empty($text)) {
            return;
        }
        $class = isset($args['field_class']) ? $args['field_class'] : '';
        $class = SB_PHP::add_string_with_space_before($class, 'sb-button');
        $description = isset($args['description']) ? $args['description'] : '';
        echo '<button class="' . $class . '">' . $text . '</button>';
        if(!empty($description)) {
            echo '<p class="description">' . $description . '</p>';
        }
    }

    public static function select($args = array()) {
        $id = '';
        $name = '';
        $list_options = array();
        $options = array();
        $description = '';
        $container_class = '';
        $value = '';
        $field_class = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        if(!is_array($options) || count($options) < 1) {
            $options = $list_options;
        }
        ?>
        <div class="<?php echo $container_class; ?>">
            <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" class="<?php echo $field_class; ?>" autocomplete="off">
                <?php foreach($options as $key => $text) : ?>
                    <option value="<?php echo $key; ?>" <?php selected($value, $key); ?>><?php echo $text; ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php echo $description; ?></p>
        </div>
        <?php
    }

    public static function select_page($args = array()) {
        $pages = SB_Post::get_all('page');
        $id = '';
        $name = '';
        $description = '';
        $container_class = '';
        $value = '';
        $field_class = '';
        if(is_array($args)) {
            extract($args, EXTR_OVERWRITE);
        }
        ?>
        <div class="<?php echo $container_class; ?>">
            <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" class="<?php echo $field_class; ?>" autocomplete="off">
                <option value="0"><?php _e('Select page:', 'sb-core'); ?></option>
                <?php if($pages->have_posts()) : ?>
                    <?php while($pages->have_posts()) : $pages->the_post(); ?>
                        <?php $post_id = get_the_ID(); ?>
                        <option value="<?php the_ID(); ?>" <?php selected($value, $post_id); ?>><?php the_title(); ?></option>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php endif; ?>
            </select>
            <p class="description"><?php echo $description; ?></p>
        </div>
    <?php
    }

    public static function select_term_field($args = array()) {
        self::select_term($args);
    }

    public static function select_term($args = array()) {
        $container_class = '';
        $id = '';
        $name = '';
        $field_class = '';
        $label = '';
        $options = array();
        $value = '';
        $description = '';
        $taxonomy = '';
        $taxonomy_id = '';
        $taxonomy_name = '';
        extract($args, EXTR_OVERWRITE);
        ?>
        <p class="<?php echo $container_class; ?>">
            <label for="<?php echo esc_attr($id); ?>"><?php echo $label; ?></label>
            <select id="<?php echo esc_attr($id); ?>" class="<?php echo $field_class; ?>" name="<?php echo esc_attr($name); ?>" autocomplete="off">
                <option value="0">--- <?php _e('Choose the term', 'sb-core'); ?> ---</option>
                <?php foreach($options as $tax) : ?>
                    <?php $terms = get_terms($tax->name); ?>
                    <?php if(count($terms) > 0) : ?>
                        <optgroup label="<?php echo $tax->labels->name; ?>">
                            <?php foreach ($terms as $cat) : ?>
                                <option value="<?php echo $cat->term_id; ?>" data-taxonomy="<?php echo $tax->name; ?>" <?php selected($value, $cat->term_id); ?>><?php echo $cat->name; ?> (<?php echo $cat->count; ?>)</option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <?php if(!empty($description)) : ?>
                <em><?php echo $description; ?></em>
            <?php endif; ?>
            <input id="<?php echo esc_attr($taxonomy_id); ?>" class="widefat taxonomy" name="<?php echo esc_attr($taxonomy_name); ?>" type="hidden" value="<?php echo esc_attr($taxonomy); ?>">
        </p>
        <?php
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
            echo '<div class="margin-bottom">';
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
        self::rich_editor($args);
    }

    public static function rich_editor($args = array()) {
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
        ); ?>
        <div id="<?php echo $id . '_editor'; ?>" class="sb-rich-editor">
            <?php wp_editor($value, $id, $args); ?>
            <?php if(!empty($description)) : ?>
                <p class="description"><?php echo $description; ?></p>
            <?php endif; ?>
        </div> <?php
    }
}