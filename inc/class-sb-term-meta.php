<?php
class SB_Term_Meta {
    public $taxonomies = array();
    private $callback;
    public $fields = array();
    private $create_callback;

    public function __construct($args = array()) {
        $this->extract($args);
        $this->hook();
    }

    private function extract($args = array()) {
        if(!is_array($args)) {
            return;
        }
        $taxonomies = array();
        $callback = '';
        $create_callback = '';
        $fields = array();
        extract($args, EXTR_OVERWRITE);
        $this->taxonomies = $taxonomies;
        $this->callback = $callback;
        $this->fields = $fields;
        $this->create_callback = $create_callback;
    }

    public function hook() {
        if(empty($this->callback)) {
            return;
        }
        foreach($this->taxonomies as $tax_name) {
            if(!empty($this->create_callback) || function_exists($this->create_callback)) {
                add_action($tax_name . '_add_form_fields', $this->create_callback);
            }
            if(!empty($this->callback) || function_exists($this->callback)) {
                add_action($tax_name . '_edit_form_fields', $this->callback);
            }
            add_action('edited_' . $tax_name, array($this, 'save'));
            add_action('created_' . $tax_name, array($this, 'save'));
        }
    }

    public function save($term_id) {
        if(!SB_Core::verify_nonce('sb_term_meta', 'sb_term_meta_nonce')) {
            return $term_id;
        }
        $taxonomy = isset($_POST['taxonomy']) ? $_POST['taxonomy'] : '';
        $sb_term_metas = SB_Option::get_term_metas();
        foreach($this->fields as $field) {
            $name = isset($field['name']) ? $field['name'] : '';
            $value = isset($_POST[$name]) ? $_POST[$name] : '';
            $sb_term_metas[$term_id][$name] = $value;
            $sb_term_metas[$term_id]['taxonomy'] = $taxonomy;
        }
        SB_Option::update_term_metas($sb_term_metas);
        return $term_id;
    }
}