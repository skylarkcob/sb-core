<?php
$count = isset($_POST['data_count']) ? $_POST['data_count'] : 1;
$count++;
$name = isset($_POST['data_name']) ? $_POST['data_name'] : '';
$id = isset($_POST['data_id']) ? $_POST['data_id'] : $count;
?>
<?php SB_Admin_Custom::set_current_rss_feed_item(array('name' => $name, 'count' => $count, 'id' => $id)); ?>
<?php sb_core_get_loop('loop-rss-feed'); ?>
