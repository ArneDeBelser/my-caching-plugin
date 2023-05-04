<?php
/*
Plugin Name: My Caching Plugin
Plugin URI: https://www.arnedebelser.be
Description: A caching plugin that improves the performance of your WordPress site
Version: 1.0
Author: De Belser Arne
Author URI: https://www.arnedebelser.be
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Define plugin constants
define('MY_CACHING_PLUGIN_VERSION', '1.0');
define('MY_CACHING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MY_CACHING_PLUGIN_URL', plugin_dir_url(__FILE__));

if (!function_exists('dd')) {
    function dd($message)
    {
        echo '<pre>';
        var_dump($message);
        echo '</pre>';
        die();
    }
}

// Load plugin classes and files
require_once(MY_CACHING_PLUGIN_DIR . 'inc/class-my-caching-plugin.php');

// Initialize the plugin
function my_caching_plugin_init()
{
    $my_caching_plugin = new My_Caching_Plugin();
}
add_action('init', 'my_caching_plugin_init');
