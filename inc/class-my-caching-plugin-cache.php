<?php

class My_Caching_Plugin_Cache
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize the cache functionality
        $this->init();
    }

    /**
     * Initialize the cache functionality
     */
    public function init()
    {
        // Check if caching is enabled
        if (!$this->is_cache_enabled() || is_user_logged_in()) {
            return;
        }

        // Add hooks to cache the page
        add_action('wp_head', array($this, 'cache_page_start'), 0);
        add_action('wp_footer', array($this, 'cache_page_end'), PHP_INT_MAX);
    }

    /**
     * Check if caching is enabled
     */
    public function is_cache_enabled()
    {
        // Check if the caching option is enabled in the plugin settings
        return get_option('my_caching_plugin_enable_cache');
    }

    /**
     * Start caching the page
     */
    public function cache_page_start()
    {
        // Check if the page is already cached
        if ($this->is_page_cached()) {
            $cache_file = $this->get_cache_file();
            readfile($cache_file);
            exit;
        }

        // Start output buffering
        ob_start();
        echo '<!-- This page has been cached by our Awesome Caching Plugin -->' . PHP_EOL;
    }

    /**
     * End caching the page
     */
    public function cache_page_end()
    {
        // Check if output buffering is active
        if (!ob_get_length()) {
            return;
        }

        // Get the buffered output
        $output = ob_get_contents();
        ob_end_clean();

        // Save the output to a file
        $cache_file = $this->get_cache_file();
        file_put_contents($cache_file, $output);

        // Output the buffered output
        echo $output;
    }

    /**
     * Check if the page is already cached
     */
    public function is_page_cached()
    {
        // Get the cache file path for the current page
        $cache_file = $this->get_cache_file();

        // Check if the cache file exists and is not empty
        return file_exists($cache_file) && filesize($cache_file) > 0;
    }

    /**
     * Get the cache file path for the current page
     */
    public function get_cache_file()
    {
        // Get the current page name
        $page_name = get_query_var('pagename') ?: 'index';

        // Use the page name to generate a unique filename
        $filename = $page_name . '.html';

        // Get the cache directory path
        $cache_dir = WP_CONTENT_DIR . '/my-caching-plugin/cache/';

        // Create the cache directory if it doesn't exist
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }

        // Return the full cache file path
        return $cache_dir . $filename;
    }
}
