<?php
class SB_Meta_Box {
    private $id;
    private $post_type;
    private $post_types;
    private $fields;
    private $title;
    private $callback;
    private $context;
    private $priority;
    private $callback_args;

    public function __construct($args = array()) {
        $this->extract_args($args);
        add_action('add_meta_boxes', array($this, 'add'));
        add_action('save_post', array($this, 'save'));
    }

    private function extract_args($args) {
        if(!is_array($args)) {
            return;
        }
        $id = '';
        $post_type = '';
        $post_types = array('post');
        $fields = array();
        $title = 'SB Meta Box';
        $callback = '';
        $context = 'advanced';
        $priority = 'default';
        $callback_args = null;
        extract($args, EXTR_OVERWRITE);
        $this->id = 'sb_meta_box_' . $id;
        $this->post_type = $post_type;
        $this->post_types = $post_types;
        $this->fields = array();
        $this->title = $title;
        $this->callback = $callback;
        $this->context = $context;
        $this->priority = $priority;
        $this->callback_args = $callback_args;
        foreach($fields as $field) {
            $field['name'] = SB_Core::build_meta_box_field_name($field['name']);
            array_push($this->fields, $field);
        }
    }

    public function add() {
        $post_type = $this->post_type;
        if(empty($post_type)) {
            $post_type = $this->post_types;
        }
        if(is_array($post_type)) {
            foreach($post_type as $screen) {
                add_meta_box($this->id, $this->title, $this->callback, $screen, $this->context, $this->priority, $this->callback_args);
            }
        } else {
            add_meta_box($this->id, $this->title, $this->callback, $post_type, $this->context, $this->priority, $this->callback_args);
        }
    }

    public function save($post_id) {
        if (!SB_Core::verify_nonce('sb_meta_box', 'sb_meta_box_nonce') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
        foreach($this->fields as $field) {
            $type = isset($field['type']) ? $field['type'] : '';
            $name = isset($field['name']) ? $field['name'] : '';
            if(empty($name)) {
                continue;
            }
            if('checkbox' == $type) {
                $value = isset($_POST[$name]) ? 1 : 0;
            } elseif('datetime' == $type) {
                $value = isset($_POST[$name]) ? SB_PHP::string_to_datetime($_POST[$name], 'Y-m-d') : '';
                if(!empty($value)) {
                    $value = strtotime($value);
                }
            } else {
                $value = isset($_POST[$name]) ? $_POST[$name] : '';
            }
            $meta_value = SB_Core::sanitize($value, $type);
            SB_Post::update_meta($post_id, $name, $meta_value);
        }
        return $post_id;
    }
}