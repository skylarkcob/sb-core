<?php
class SB_List_Plugin {
    private $plugins;

    public function __construct() {
        $this->init();
    }

    private function init() {
        $this->plugins = array();
        $this->add(new SB_Plugin('sb-banner-widget'));
        $this->add(new SB_Plugin('sb-clean'));
        $this->add(new SB_Plugin('sb-comment'));
        $this->add(new SB_Plugin('sb-core'));
        $this->add(new SB_Plugin('sb-login-page'));
        $this->add(new SB_Plugin('sb-paginate'));
        $this->add(new SB_Plugin('sb-post-widget'));
        $this->add(new SB_Plugin('sb-tab-widget'));
        $this->add(new SB_Plugin('sb-tbfa'));
    }

    public function add($plugin) {
        array_push($this->plugins, $plugin);
    }

    public function get() {
        return $this->plugins;
    }
}