<?php
class SB_Page_Template {
    protected $plugin_slug;
    protected $plugin_path;
    protected $plugin_folder_path;
    private static $instance;
    protected $templates;
    public $theme_template_path;

    public static function get_instance() {
        if(null == self::$instance) {
            self::$instance = new SB_Page_Template();
        }
        return self::$instance;
    }

    public function set_plugin_path($plugin_path) {
        $plugin_path = untrailingslashit($plugin_path);
        $this->plugin_path = $plugin_path;
    }

    public function set_folder_path($folder_path) {
        $this->plugin_folder_path = $folder_path;
    }

    public function __construct() {
        $this->templates = array();
        $theme_path = untrailingslashit(get_template_directory());
        $theme_path .= '/page-templates';
        $this->theme_template_path = $theme_path;
        SB_PHP::create_folder($theme_path);
    }

    public function hook() {
        add_filter('page_attributes_dropdown_pages_args', array($this, 'cache_page_template'));
        add_filter('wp_insert_post_data', array($this, 'cache_page_template'));
        add_filter('template_include', array($this, 'load_page_template'));
    }

    public function copy_to_theme() {
        foreach($this->templates as $key => $template) {
            $file_path = $this->plugin_path . '/' . $this->plugin_folder_path . '/' . $key;
            $destination = trailingslashit($this->theme_template_path) . '/' . $key;
            $result = SB_PHP::copy($file_path, $destination);
        }
    }

    public function delete_from_theme() {
        foreach($this->templates as $key => $template) {
            $destination = trailingslashit($this->theme_template_path) . '/' . $key;
            SB_PHP::delete_file($destination);
        }
    }

    public function add($file_name, $title) {
        $this->templates[$file_name] = $title;
    }

    public function add_array_templates($templates) {
        foreach($templates as $template) {
            $this->add(untrailingslashit($template['file_name']), $template['title']);
        }
    }

    public function cache_page_template($atts) {
        $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());
        $templates = wp_get_theme()->get_page_templates();
        if(!is_array($templates) || empty($templates)) {
            $templates = array();
        }
        wp_cache_delete($cache_key , 'themes');
        $templates = array_merge($templates, $this->templates);
        wp_cache_add($cache_key, $templates, 'themes', 1800);
        return $atts;
    }

    public function load_page_template($template) {
        global $post;
        if(SB_Core::is_error($post)) {
            return $template;
        }
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        if(!isset($this->templates[$page_template])) {
            return $template;
        }
        $file = $this->plugin_path . '/';
        if(!empty($this->plugin_folder_path)) {
            $file .= untrailingslashit($this->plugin_folder_path) . '/';
        }
        $file .= $page_template;
        if(file_exists($file)) {
            $template = $file;
        }
        return $template;
    }
}