<?php
class SB_Meta_Box {
    private $id;
    private $post_type;
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
        $post_type = 'post';
        $fields = array();
        $title = 'SB Meta Box';
        $callback = '';
        $context = 'advanced';
        $priority = 'default';
        $callback_args = null;
        extract($args, EXTR_OVERWRITE);
        $this->id = 'sb_meta_box_' . $id;
        $this->post_type = $post_type;
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
        add_meta_box($this->id, $this->title, $this->callback, $this->post_type, $this->context, $this->priority, $this->callback_args);
    }

    public function save($post_id) {
        if ( ! isset( $_POST['sb_meta_box_nonce'] ) ) {
            return $post_id;
        }
        $nonce = $_POST['sb_meta_box_nonce'];
        if ( ! wp_verify_nonce( $nonce, 'sb_meta_box' ) ) {
            return $post_id;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        if(!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
        foreach($this->fields as $field) {
            $value = isset($_POST[$field['name']]) ? $_POST[$field['name']] : '';
            $meta_value = SB_Core::sanitize($value, $field['type']);
            SB_Post::update_meta($post_id, $field['name'], $meta_value);
        }
        return $post_id;
    }
}