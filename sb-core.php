<?php
/*
Plugin Name: SB Core
Plugin URI: http://hocwp.net/
Description: SB Core is not only a plugin, it contains core function for all plugins and themes that are created by SB Team.
Author: SB Team
Version: 1.2.4
Author URI: http://hocwp.net/
Text Domain: sb-core
Domain Path: /languages/
*/

define('SB_CORE_VERSION', '1.2.4');

add_filter('sb_testing', '__return_true');

define('SB_CORE_FILE', __FILE__);

define('SB_CORE_PATH', untrailingslashit(plugin_dir_path(SB_CORE_FILE)));

define('SB_CORE_URL', plugins_url('', SB_CORE_FILE));

define('SB_CORE_INC_PATH', SB_CORE_PATH . '/inc');

define('SB_CORE_BASENAME', plugin_basename(SB_CORE_FILE));

define('SB_CORE_DIRNAME', dirname(SB_CORE_BASENAME));

require SB_CORE_INC_PATH . '/sb-plugin-functions.php';