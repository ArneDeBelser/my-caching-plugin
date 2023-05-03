<?php

class My_Caching_Plugin
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Load the plugin classes
        $this->load_classes();

        // Initialize the plugin functionality
        $this->init();
    }

    /**
     * Load the plugin classes
     */
    public function load_classes()
    {
        require_once 'class-my-caching-plugin-admin.php';
        require_once 'class-my-caching-plugin-cache.php';
        // require_once 'class-my-caching-plugin-preload.php';
    }

    /**
     * Initialize the plugin functionality
     */
    public function init()
    {
        $admin = new My_Caching_Plugin_Admin();
        $cache = new My_Caching_Plugin_Cache();
        //  $preload = new My_Caching_Plugin_Preload();

        // $minify = new WP_Rocket_Minify();
        // $media = new WP_Rocket_Media();
        // $cdn = new WP_Rocket_CDN();
    }
}
