<?php

class My_Caching_Plugin_Preload
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize the preloading functionality
        add_action('init', array($this, 'init'));
    }

    /**
     * Initialize the preloading functionality
     */
    public function init()
    {
        // Check if preloading is enabled
        if (!$this->is_preloading_enabled()) {
            return;
        }

        // Add hooks to preload all pages, posts, and custom post types on the site
        add_action('wp_loaded', array($this, 'preload_all'));
        add_action('wp_enqueue_scripts', array($this, 'preload_assets'));
    }

    /**
     * Check if preloading is enabled
     */
    public function is_preloading_enabled()
    {
        // Check if the preloading option is enabled in the plugin settings
        return get_option('my_caching_plugin_enable_preloading');
    }

    /**
     * Preload the homepage
     */
    public function preload_all()
    {
        // Get all published pages, posts, and custom post types
        $post_types = get_post_types(array('public' => true), 'names');
        $args = array(
            'post_type' => $post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        $all_posts = get_posts($args);

        // Preload each post using a separate request
        foreach ($all_posts as $post) {
            wp_remote_get(get_permalink($post->ID));
        }
    }
    /**
     * Preload assets
     */
    public function preload_assets()
    {
        // Get the list of assets to preload from the plugin settings
        $assets = get_option('my_caching_plugin_preload_assets');

        // Preload each asset using a separate request
        foreach ($assets as $asset) {
            wp_remote_get($asset);
        }
    }
}
