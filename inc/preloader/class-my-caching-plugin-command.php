<?php
class My_Caching_Plugin_Command
{
    private $page_url;

    /**
     * Preload Cache
     *
     * ## OPTIONS
     *
     * [--max_pages=<max_pages>]
     * : Maximum number of pages to preload
     * ---
     * default: 10
     * ---
     *
     * ## EXAMPLES
     *
     * wp my-caching-plugin preload_cache --max_pages=20
     *
     * @synopsis [--max_pages=<max_pages>]
     */
    public function preload_cache($args, $assoc_args)
    {
        $max_pages = $assoc_args['max_pages'] ?? 100;

        WP_CLI::debug("Max pages to preload: $max_pages");

        $query = new WP_Query(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'order' => 'ASC',
            'posts_per_page' => -1,
        ));

        $pages = $query->get_posts();
        $cache = new My_Caching_Plugin_Cache();

        $count = 0;
        foreach ($pages as $page) {
            WP_CLI::line("-----------------");
            $count++;
            if ($count > $max_pages) {
                break;
            }

            $this->page_url = get_permalink($page->ID);
            $cached_html_file = $this->get_cache_file();

            WP_CLI::line("Processing page: {$this->page_url}");

            if (!file_exists($cached_html_file)) {
                // Use wp_remote_get to fetch the page content
                $response = wp_remote_get($this->page_url);
                WP_CLI::line("cached_html_file: $cached_html_file");

                // Get the HTML content from the response
                $html = wp_remote_retrieve_body($response);

                WP_CLI::line("Caching page: {$this->page_url}");

                $this->cache_page_start();
                echo $html;
                $this->cache_page_end();
            } else {
                WP_CLI::line("Page already cached: {$this->page_url}");
            }
        }

        WP_CLI::success("Cache preloaded for $count pages.");
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
        echo '<!-- This page has been cached by our Awesome Caching Plugin via the Preloader -->' . PHP_EOL;
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

    public function get_cache_file()
    {
        // Get the current page name
        $page_path = parse_url($this->page_url, PHP_URL_PATH);
        $page_name = trim($page_path, '/');
        if (empty($page_name)) {
            $page_name = 'index';
        }

        WP_CLI::line(
            'page_name ' . $page_name
        );

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
