<?php
class SB_Plugin {
    private $slug;
    private $folder_name;
    private $information;

    public function __construct($slug) {
        $this->slug = $slug;
        $this->folder_name = $slug;
    }

    public function get_slug() {
        return $this->slug;
    }

    public function set_folder($name) {
        $this->folder_name = $name;
    }

    public function get() {
        if(empty($this->information)) {
            return $this->get_information();
        }
        return $this->information;
    }

    public function get_name() {
        $info = $this->get();
        $name = '';
        if(!is_wp_error($info)) {
            $name = $info->name;
        }
        return $name;
    }

    public function get_status() {
        if(!function_exists('install_plugin_install_status')) {
            require ABSPATH . 'wp-admin/includes/plugin-install.php';
        }
        return install_plugin_install_status($this->get());
    }

    public function the_installation_button() {
        if(current_user_can('install_plugins') || current_user_can('update_plugins')) {
            $status = $this->get_status();
            $name = $this->get_name();
            switch($status['status']) {
                case 'install':
                    if($status['url']) {
                        echo '<a class="install-now button" href="' . $status['url'] . '" title="' . esc_attr__(sprintf(__( 'Install plugin %s now', 'sb-core' ), $name)) . '">' . __('Install Now', 'sb-core') . '</a>';
                    }
                    break;
                case 'update_available':
                    if($status['url']) {
                        echo '<a class="button update-now" href="' . admin_url('update-core.php') . '" title="' . esc_attr__(sprintf(__('Update plugin %s now', 'sb-core'), $name)) . '">' . __('Update Now', 'sb-core') . '</a>';
                    }
                    break;
                case 'latest_installed':
                    echo '<span class="button button-disabled" title="' . esc_attr__('This plugin is already installed and is up to date', 'sb-core') . ' ">' . __('Installed', 'sb-core') . '</span>';
                    break;
                case 'newer_installed':
                    echo '<span class="button button-disabled" title="' . esc_attr__('This plugin is already installed and is up to date', 'sb-core') . ' ">' . __('Installed', 'sb-core') . '</span>';
                    break;
            }
        }
    }

    public function get_information() {
        if(!function_exists('plugins_api')) {
            require ABSPATH . 'wp-admin/includes/plugin-install.php';
        }
        $fields = array(
            'short_description' => true,
            'screenshots' => false,
            'changelog' => false,
            'installation' => false,
            'description' => false
        );
        $args = array(
            'slug' => $this->slug,
            'fields' => $fields);
        $this->information = plugins_api('plugin_information', $args);
        return $this->information;
    }

    public function is_activated() {
        if(!function_exists('is_plugin_active')) {
            require ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active($this->folder_name . '/' . $this->slug . '.php');
    }

    public function is_installed() {
        $path = dirname(dirname(plugin_dir_path(__FILE__)));
        $path .= '/' . $this->folder_name;
        return file_exists($path);
    }
}