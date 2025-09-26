<?php
/**
 * Test Settings Debug
 * Access via: wp.test/wp-content/plugins/simple-product-showcase/test-settings-debug.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<h1>Test Settings Debug</h1>';

// Check if plugin is active
$active_plugins = get_option('active_plugins');
$plugin_file = 'simple-product-showcase/simple-product-showcase.php';

if (in_array($plugin_file, $active_plugins)) {
    echo '<p>✅ Plugin is active</p>';
    
    // Check if class exists
    if (class_exists('SPS_Settings')) {
        echo '<p>✅ SPS_Settings class exists</p>';
        
        // Try to get instance
        $settings = SPS_Settings::get_instance();
        if ($settings) {
            echo '<p>✅ SPS_Settings instance created</p>';
        } else {
            echo '<p>❌ SPS_Settings instance failed</p>';
        }
    } else {
        echo '<p>❌ SPS_Settings class does not exist</p>';
    }
    
    // Check hooks
    global $wp_filter;
    if (isset($wp_filter['admin_init'])) {
        echo '<p>✅ admin_init hook exists</p>';
        echo '<p>Number of callbacks: ' . count($wp_filter['admin_init']->callbacks) . '</p>';
    } else {
        echo '<p>❌ admin_init hook does not exist</p>';
    }
    
    // Check if settings are in database
    global $wpdb;
    $settings_results = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'sps_%'");
    echo '<h2>Database Settings:</h2>';
    echo '<pre>';
    foreach ($settings_results as $setting) {
        echo $setting->option_name . ' = ' . $setting->option_value . PHP_EOL;
    }
    echo '</pre>';
    
    // Force register settings
    echo '<h2>Force Register Settings:</h2>';
    register_setting('sps_settings_group', 'sps_detail_page_mode', array(
        'type' => 'string',
        'default' => 'default'
    ));
    register_setting('sps_settings_group', 'sps_custom_detail_page', array(
        'type' => 'integer',
        'default' => 0
    ));
    echo '<p>✅ Settings force registered</p>';
    
    // Check again
    global $wp_registered_settings;
    if (isset($wp_registered_settings['sps_detail_page_mode'])) {
        echo '<p>✅ sps_detail_page_mode is now registered</p>';
    } else {
        echo '<p>❌ sps_detail_page_mode still not registered</p>';
    }
    
} else {
    echo '<p>❌ Plugin is not active</p>';
}
?>
