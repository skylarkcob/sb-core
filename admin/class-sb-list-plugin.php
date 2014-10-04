<?php
defined('ABSPATH') OR exit;

if(!class_exists('SB_List_Plugin')) {
    class SB_List_Plugin {
        private $plugins;

        public function __construct() {
            $this->init();
        }

        private function init() {
            $this->plugins = array();
            $this->add(new SB_Plugin('sb-core'));
            $this->add(new SB_Plugin('sb-paginate'));
            $this->add(new SB_Plugin('sb-clean'));
            $this->add(new SB_Plugin('sb-tbfa'));
            $this->add(new SB_Plugin('sb-comment'));
        }

        public function add($plugin) {
            array_push($this->plugins, $plugin);
        }

        public function get() {
            return $this->plugins;
        }
    }
}