<?php
class SB_Term_Meta {
    public $taxonomies = array();
    private $callback;
    public $fields = array();
    private $create_callback;
    private $translate = false;
    public $column = false;

    public function __construct($args = array()) {
        $this->extract($args);
        $this->hook();
    }

    private function extract($args = array()) {
        if(!is_array($args)) {
            return;
        }
        $taxonomies = isset($args['taxonomies']) ? $args['taxonomies'] : array();
        $callback = isset($args['callback']) ? $args['callback'] : '';
        $create_callback = isset($args['create_callback']) ? $args['create_callback'] : '';
        $fields = isset($args['fields']) ? $args['fields']: array();
        $translate = isset($args['translate']) ? $args['translate'] : false;
        $this->taxonomies = $taxonomies;
        $this->callback = $callback;
        $this->fields = $fields;
        $this->create_callback = $create_callback;
        $this->translate = $translate;
        $this->column = isset($args['column']) ? $args['column'] : false;
    }

    public function qtranslate_plus_field() {
        foreach($this->taxonomies as $tax_name) {
            add_action($tax_name . '_add_form_fields', 'ppqtrans_modifyTermFormFor');
            add_action($tax_name . '_edit_form_fields', 'ppqtrans_modifyTermFormFor');
        }
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
            if($this->translate) {
                add_action($tax_name . '_add_form_fields', 'ppqtrans_modifyTermFormFor');
                add_action($tax_name . '_edit_form_fields', 'ppqtrans_modifyTermFormFor');
            }
            add_action('edited_' . $tax_name, array($this, 'save'));
            add_action('created_' . $tax_name, array($this, 'save'));
            if($this->column) {
                add_filter( 'manage_edit-' . $tax_name . '_columns', array($this, 'term_meta_column_header'), 10);
                add_action( 'manage_' . $tax_name . '_custom_column', array($this, 'term_meta_column_content'), 10, 3);
            }
        }
    }

    public function term_meta_column_header( $columns ){
        $columns = apply_filters('sb_term_meta_column_header', $columns);
        return $columns;
    }

    public function term_meta_column_content( $value, $column_name, $tax_id ){
        do_action('sb_term_meta_column_content', $value, $column_name, $tax_id);
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