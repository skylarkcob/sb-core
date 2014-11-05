<?php
$list = new SB_List_Plugin();
$plugins = '';
foreach($list->get() as $plugin) {
    $plugins .= $plugin->get_slug() . ',';
}
$plugins = trim($plugins, ',');
?>
<div class="sb-plugins" data-plugin="<?php echo $plugins; ?>">
    <p><?php printf(__('List all plugins are written by %s.', 'sb-core'), 'SB Team'); ?></p>
    <div class="wp-list-table widefat plugin-install sb-plugin-list">
        <img class="sb-ajax-load" src="<?php echo SB_CORE_URL . '/images/loading.gif'; ?>">
    </div>
</div>