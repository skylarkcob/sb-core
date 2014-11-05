<?php
function sb_do_settings_sections( $page ) {
    global $wp_settings_sections, $wp_settings_fields;
    if ( ! isset( $wp_settings_sections[$page] ) ) {
        return;
    }
    $count = 0;
    foreach ( (array) $wp_settings_sections[$page] as $section ) {
        $section_id = $section['id'];
        $class = 'sbtheme-option-section';
        echo '<div id="'.$section_id.'" class="'.$class.'">';
        if ( $section['title'] ) {
            echo "<h3 class=\"setting-title\">{$section['title']}</h3>\n";
        }
        if ( $section['callback'] ) {
            call_user_func( $section['callback'], $section );
        }
        $has_field = true;
        if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section_id] ) ) {
            $has_field = false;
        }
        echo '<table class="form-table">';
        if($has_field && 'sb_options_section' != $section_id) {
            do_settings_fields( $page, $section_id );
        } elseif('sb_options_section' == $section_id) {
            sb_core_get_content('sb-about');
        }
        echo '</table>';
        echo '</div>';
        $count++;
    }
}
?>
<div class="wrap sb-option sb-options" data-message-confirm="<?php _e('Are you sure?', 'sb-core'); ?>">
    <noscript><div class="no-js"><?php _e('Options page will not work if your browser doesn\'t support javascript!', 'sb-core'); ?></div></noscript>
    <h2></h2>
    <?php if (isset($_REQUEST['submit']) || isset($_REQUEST['settings-updated'])) : ?>
        <div id="message" class="updated">
            <p><strong><?php _e('All your changes have been saved successfully.', 'sb-core'); ?></strong></p>
        </div>
    <?php endif; ?>
    <div class="sbtheme-container">
        <div class="sbtheme-header">
            <?php $theme = wp_get_theme(); ?>
            <?php $name = $theme->get('Name'); ?>
            <?php
            if(empty($name)) {
                $name = __('No name theme', 'sb-core');
            }
            ?>
            <h2><?php echo $name; ?></h2>
            <?php
            $version = $theme->get('Version');
            $version = str_replace('-wpcom', '', $version);
            ?>
            <?php if(!empty($version)) : ?>
                <span><?php _e('Version:', 'sb-core'); ?> <?php echo $version; ?></span>
            <?php endif; ?>
        </div>
        <div class="sbtheme-content">
            <div class="sidebar">
                <ul class="sbtheme-list-section sb-tabs">
                    <?php $count = 0; $tabs = apply_filters('sb_admin_tabs', array()); $current_tab = isset($_REQUEST['page']) ? $_REQUEST['page'] : ''; ?>
                    <?php
                    $key = 'sb_options';
                    $about_tab = isset($tabs[$key]) ? $tabs[$key] : '';
                    if($about_tab) {
                        unset($tabs[$key]);
                        $class = 'tab-item section-item tab-'.$key; if($key == $current_tab) $class .= ' active';
                        $type = isset($value['type']) ? $value['type'] : 'normal';
                        $class .= ' '.$type;
                        $value = $about_tab;
                        ?>
                        <li class="<?php echo $class; ?>">
                            <a class="sbtheme-group-tab" href="<?php echo admin_url('admin.php?page='.$key); ?>" data-section="<?php echo $value['section_id']; ?>"><i class="tab-icon <?php echo $key; ?>"></i> <span class="group-title"><?php _e($value['title'], 'sb-core'); ?></span></a>
                        </li>
                        <?php
                    }
                    $tabs['sb_plugins'] = array('title' => 'SB Plugins', 'section_id' => 'sb_plugins_section', 'type' => 'plugin');
                    ?>
                    <?php foreach($tabs as $key => $value) : ?>
                        <?php $class = 'tab-item section-item tab-'.$key; if($key == $current_tab) $class .= ' active'; ?>
                        <?php
                        $type = isset($value['type']) ? $value['type'] : 'normal';
                        $class .= ' '.$type;
                        ?>
                        <li class="<?php echo $class; ?>">
                            <a class="sbtheme-group-tab" href="<?php echo admin_url('admin.php?page='.$key); ?>" data-section="<?php echo $value['section_id']; ?>"><i class="tab-icon <?php echo $key; ?>"></i> <span class="group-title"><?php _e($value['title'], 'sb-core'); ?></span></a>
                        </li>
                        <?php $count++; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="main">
                <form id="sb-options-form" method="post" action="options.php" data-page="<?php echo $current_tab; ?>">
                    <?php settings_fields( 'sb-setting' ); ?>
                    <?php sb_do_settings_sections( $current_tab ); ?>
                    <?php if(!SB_Admin_Custom::is_about_page()) : ?>
                    <?php submit_button(__('Save changes', 'sb-core')); ?>
                    <div class="top-save-button"><?php submit_button(__('Save changes', 'sb-core')); ?></div>
                    <div class="sb-button reset-button"><span class="button"><?php _e('Reset', 'sb-core'); ?><img src="<?php echo SB_CORE_URL . '/images/ajax-loader.gif'; ?>"></span></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div class="sbtheme-footer">
            <div class="left"><p><?php printf(__('Created by %s.', 'sb-core'), 'SB Team'); ?> <?php printf(__('If you have any question, please send us an email via: %s', 'sb-core'), ' <em>laidinhcuongvn@gmail.com</em>'); ?></p></div>
            <div class="right">
                <ul class="sb-social-list">
                    <li class="github"><a target="_blank" href="https://github.com/skylarkcob/sb-core"></a></li>
                    <li class="facebook"><a target="_blank" href="https://www.facebook.com/Sauhicom"></a></li>
                    <li class="twitter"><a target="_blank" href="https://twitter.com/skylarkcob"></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="sbtheme-copyright">
        <p>&copy; 2008 - <?php echo date('Y'); ?> <a href="http://hocwp.net">SB Team</a>. All Rights Reserved.</p>
    </div>
</div>