<?php
$args = SB_Admin_Custom::get_current_rss_feed_item();
$name = isset($args['name']) ? $args['name'] : '';
$count = isset($args['count']) ? $args['count'] : 1;
if(isset($args['feed'])) {
    $feed = $args['feed'];
    $number = isset($feed['number']) ? $feed['number'] : SB_Admin_Custom::get_default_rss_feed_number();
    $title = $feed['title'];
    $url = $feed['url'];
    $id = $feed['id'];
} else {
    $number = SB_Admin_Custom::get_default_rss_feed_number();
    $title = '';
    $url = '';
    $id = isset($args['id']) ? $args['id'] : $count;
}
?>
<li class="ui-state-default feed-item sortable-item" data-id="<?php echo $id; ?>">
    <input type="text" name="<?php echo $name . '[' . $id . '][title]'; ?>" value="<?php echo $title; ?>" placeholder="<?php _e('Title', 'sb-core'); ?>" class="width-50" autocomplete="off">
    <input type="number" name="<?php echo $name . '[' . $id . '][number]'; ?>" value="<?php echo $number; ?>" placeholder="<?php _e('Number of post', 'sb-core'); ?>" class="width-50 right" autocomplete="off">
    <input type="text" name="<?php echo $name . '[' . $id . '][url]'; ?>" value="<?php echo $url; ?>" placeholder="<?php _e('Put your feed url here', 'sb-core'); ?>" class="widefat" autocomplete="off">
    <input class="ui-item-id item-id" type="hidden" name="<?php echo $name . '[' . $id . '][id]'; ?>" value="<?php echo $id; ?>" autocomplete="off">
</li>