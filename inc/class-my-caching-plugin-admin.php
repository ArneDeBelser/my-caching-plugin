<?php

class My_Caching_Plugin_Admin
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Add the plugin settings page
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Register the plugin settings
        add_action('admin_init', array($this, 'register_settings'));

        // Add a link to clear the cache in the WordPress admin top bar menu
        add_action('admin_bar_menu', array(
            $this, 'add_clear_cache_link'
        ), 999);

        // Handle requests to clear the cache
        add_action('admin_post_my_caching_plugin_clear_cache', array($this, 'handle_clear_cache_request'));
    }

    /**
     * Add the plugin settings page
     */
    public function add_settings_page()
    {
        add_options_page(
            'My Caching Plugin Settings',
            'My Caching Plugin',
            'manage_options',
            'my-caching-plugin',
            array($this, 'settings_page')
        );
    }

    /**
     * Render the plugin settings page
     */
    public function settings_page()
    {
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php settings_fields('my_caching_plugin_settings_group'); ?>
                <?php do_settings_sections('my_caching_plugin_settings'); ?>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
<?php
    }

    /**
     * Register the plugin settings
     */
    public function register_settings()
    {
        register_setting(
            'my_caching_plugin_settings_group',
            'my_caching_plugin_enable_cache'
        );

        register_setting(
            'my_caching_plugin_settings_group',
            'my_caching_plugin_enable_preload'
        );

        add_settings_section(
            'my_caching_plugin_settings_section',
            'Cache Settings',
            array($this, 'settings_section'),
            'my_caching_plugin_settings'
        );

        add_settings_field(
            'my_caching_plugin_enable_cache',
            'Enable Cache',
            array($this, 'enable_cache_field'),
            'my_caching_plugin_settings',
            'my_caching_plugin_settings_section'
        );

        add_settings_field(
            'my_caching_plugin_enable_preload',
            'Enable Preload',
            array($this, 'enable_cache_preload_field'),
            'my_caching_plugin_settings',
            'my_caching_plugin_settings_section'
        );
    }

    /**
     * Render the settings section
     */
    public function settings_section()
    {
        echo '<p>Configure the caching options for your site.</p>';
    }

    /**
     * Render the enable cache field
     */
    public function enable_cache_field()
    {
        $value = get_option('my_caching_plugin_enable_cache');
        echo '<input type="checkbox" name="my_caching_plugin_enable_cache" value="1" ' . checked($value, 1, false) . ' />';
    }

    public function enable_cache_preload_field()
    {
        $value = get_option('my_caching_plugin_enable_preload');
        echo '<input type="checkbox" name="my_caching_plugin_enable_preload" value="1" ' . checked($value, 1, false) . ' />';
    }

    /**
     * Add a link to clear thecache in the WordPress admin top bar menu
     */
    public function add_clear_cache_link($admin_bar)
    {
        // Check if the user has permission to clear the cache
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add a link to clear the cache in the WordPress admin top bar menu
        $admin_bar->add_menu(array(
            'id' => 'my-caching-plugin-clear-cache',
            'title' => __('Clear Cache', 'my-caching-plugin'),
            'href' => wp_nonce_url(admin_url('admin-post.php?action=my_caching_plugin_clear_cache'), 'my_caching_plugin_clear_cache')
        ));
    }

    /**
     * Handle requests to clear the cache
     */
    public function handle_clear_cache_request()
    {
        // Verify that the user has permission to clear the cache
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to clear the cache.', 'my-caching-plugin'));
        }

        // Clear the cache
        $this->clear_cache();

        // Redirect the user back to the previous page
        wp_redirect(wp_get_referer());
        exit;
    }

    /**
     * Clear the cache
     */
    public function clear_cache()
    {
        // Verify that the user has permission to clear the cache
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to clear the cache.', 'my-caching-plugin'));
        }

        // Clear the cache directory
        $cache_dir = WP_CONTENT_DIR . '/cache/my-caching-plugin/';
        $this->delete_directory($cache_dir);

        // Redirect the user back to the previous page
        wp_redirect(wp_get_referer());
        exit;
    }

    /**
     * Delete a directory and its contents
     *
     * @param string $directory The path to the directory to delete
     * @return bool True on success, false on failure
     */
    private function delete_directory($directory)
    {
        if (!is_dir($directory)) {
            return true;
        }

        $files = array_diff(scandir($directory), array('.', '..'));

        foreach ($files as $file) {
            $path = $directory . '/' . $file;

            if (is_dir($path)) {
                $this->delete_directory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($directory);
    }
}
