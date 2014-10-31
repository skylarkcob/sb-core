<?php
class SB_Feed {
    private $url;
    private $items;

    public function __construct($feed_url) {
        $this->url = $feed_url;
    }

    public function fetch($args = array()) {
        $number = 5;
        $start = 0;
        if(!function_exists('fetch_feed')) {
            include_once(ABSPATH . WPINC . '/feed.php');
        }
        $rss = fetch_feed($this->url);
        if(!is_wp_error($rss)) {
            extract($args, EXTR_OVERWRITE);
            $max = $rss->get_item_quantity($number);
            $items = $rss->get_items($start, $max);
            $this->items = $items;
        }
    }

    public function get() {
        return $this->items;
    }

    public function get_items($args = array()) {
        $result = array();
        $this->fetch($args);
        if(is_array($this->items)) {
            $items = $this->items;
            foreach($items as $item) {
                $description = $item->get_description();
                $thumbnail = SB_PHP::get_first_image($description);
                $description = strip_tags($description);
                $description = wp_strip_all_tags($description);
                $content = $item->get_content();
                if(empty($thumbnail)) {
                    $thumbnail = SB_PHP::get_first_image($content);
                }
                $value = array(
                    'permalink' => $item->get_permalink(),
                    'title' => $item->get_title(),
                    'date' => $item->get_date(SB_Option::get_date_time_format()),
                    'image_url' => $thumbnail,
                    'description' => $description,
                    'content' => $content
                );
                array_push($result, $value);
            }
        }
        return $result;
    }
}