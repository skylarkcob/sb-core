<?php
if(!defined('ABSPATH')) exit;

if(!class_exists("SB_Admin")) {
	class SB_Admin {
		
		private $sb_plugins = array();
		private $sb_admin_added = false;
        private $tabs = array();
		
		private function sb_plugin_init() {

		}
		
		private function has_sb_admin() {
			global $sb_admin;
			if($sb_admin && $sb_admin->sb_admin_added) {
				return true;
			}
			return false;
		}
	
		private function init() {
			$this->sb_admin_added = true;
			$this->sb_plugin_init();
            $this->sb_tab_init();
		}
	
		public function submenu_exists($name) {
			global $submenu;
			if(isset( $submenu[ $name ] )) {
				return true;
			}
			return false;
		}
	
		public function settings_page() {
            SB_Admin_Custom::setting_page_callback();
		}

        private function sb_admin_test() {
            return apply_filters("sb_admin_test", false);
        }

        public function admin_style_and_script() {
            $page = SB_Admin_Custom::get_current_page();
            if(SB_PHP::is_string_contain($page, "sb")) {
                wp_enqueue_media();
            }

            if($this->sb_admin_test()) {
                wp_register_style("sb-admin-style", plugins_url("sb-admin-style.css", __FILE__));
                wp_enqueue_style("sb-admin-style");

                wp_register_script("sb-admin", plugins_url("sb-admin-script.js", __FILE__), array("jquery"), false, true);
                wp_enqueue_script("sb-admin");
            } else {
                wp_register_style("sb-admin-style", plugins_url("sb-admin-style.min.css", __FILE__));
                wp_enqueue_style("sb-admin-style");

                wp_register_script("sb-admin", plugins_url("sb-admin-script.min.js", __FILE__), array("jquery"), false, true);
                wp_enqueue_script("sb-admin");
            }

        }
		
		public function action_admin_head() {
			do_action("sb_admin_head");
		}

        public function sanitize( $input ) {
            $data = $input;
            $options = get_option("sb_options");
            $data = wp_parse_args($data, $options);
            return apply_filters("sb_options_sanitize", $data);
        }

        private function register_sb_setting() {
            register_setting( 'sb-setting', 'sb_options', array( $this, 'sanitize' ) );
        }

        private function add_sb_options_section() {
            if(SB_Admin_Custom::is_about_page()) {
                add_settings_section("sb_options_section", __('About SB', 'sb-core'), array( $this, 'print_section_info' ), 'sb_options');
            }
        }

        public function action_admin_init() {
            $this->register_sb_setting();
            $this->add_sb_options_section();
            do_action("sb_admin_init");
        }

        public function sb_options_callback() {
            include(SB_ADMIN_PATH."/sb-about.php");
        }

        public function print_section_info($args) {
            if($args["id"] == "sb_options_section") {
                _e("Short description about SB Options.", "sb-core");
            } else {
                _e("Change your settings below:", "sb-core");
            }
        }

        private function sb_tab_init() {
            $this->add_tab("sb_options", __('About SB', 'sb-core'), 'sb_options_section');
        }

        private function add_tab($key, $title, $section_id) {
            $this->tabs[$key] = array('title' => $title, 'section_id' => $section_id);
        }

        public function option_tab($tabs) {
            $tabs = array_merge($tabs, $this->tabs);
            return $tabs;
        }

        private function filter() {
            add_filter("sb_admin_tabs", array($this, "option_tab"));
        }

		private function action() {
			add_action("admin_menu", array($this, "action_admin_menu"));
			add_action("admin_head", array($this, "action_admin_head"));
            add_action("admin_enqueue_scripts", array($this, "admin_style_and_script"));
            add_action("admin_init", array($this, "action_admin_init"));
		}
		
		public function action_admin_menu() {
			$this->add_menu_page();
			$this->add_submenu_page();
			do_action("sb_admin_menu");
		}
		
		public function __construct() {
			if($this->has_sb_admin()) {
				return;
			}
			$this->init();
			$this->action();
            $this->filter();
		}
		
		public function add_submenu_page() {
			if(!$this->submenu_exists("sb_options")) {
				add_submenu_page('sb_options', __('About SB', 'sb-core'), __('About SB', 'sb-core'), 'manage_options', 'sb_options', array($this, 'settings_page'));
			}
		}
		
		public function add_menu_page() {
			if ( empty ( $GLOBALS['admin_page_hooks']['sb_options'] ) ) {
				add_menu_page( __('SB Options', 'sb-core'), __('SB Options', 'sb-core'), 'manage_options', 'sb_options', '', plugins_url( "admin/images/px.png", __FILE__ ), 1006 );
			}
		}
	}
}
?>