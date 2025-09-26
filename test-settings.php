<?php
/**
 * Test Settings
 * Access via: wp.test/wp-content/plugins/simple-product-showcase/test-settings.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<h1>Test Settings</h1>';

// Check if plugin is active
$active_plugins = get_option('active_plugins');
$plugin_file = 'simple-product-showcase/simple-product-showcase.php';

if (in_array($plugin_file, $active_plugins)) {
    echo '<p>✅ Plugin is active</p>';
    
    // Check if settings exist
    echo '<h2>Settings Check:</h2>';
    
    $detail_mode = get_option('sps_detail_page_mode', 'NOT_SET');
    echo '<p><strong>Detail Page Mode:</strong> ' . esc_html($detail_mode) . '</p>';
    
    $custom_page = get_option('sps_custom_detail_page', 'NOT_SET');
    echo '<p><strong>Custom Detail Page:</strong> ' . esc_html($custom_page) . '</p>';
    
    $whatsapp_number = get_option('sps_whatsapp_number', 'NOT_SET');
    echo '<p><strong>WhatsApp Number:</strong> ' . esc_html($whatsapp_number) . '</p>';
    
    // Check if settings are registered
    global $wp_registered_settings;
    echo '<h2>Registered Settings:</h2>';
    echo '<pre>';
    if (isset($wp_registered_settings['sps_detail_page_mode'])) {
        echo '✅ sps_detail_page_mode is registered' . PHP_EOL;
    } else {
        echo '❌ sps_detail_page_mode is NOT registered' . PHP_EOL;
    }
    
    if (isset($wp_registered_settings['sps_custom_detail_page'])) {
        echo '✅ sps_custom_detail_page is registered' . PHP_EOL;
    } else {
        echo '❌ sps_custom_detail_page is NOT registered' . PHP_EOL;
    }
    echo '</pre>';
    
    // Test settings URL
    echo '<h2>Settings URL:</h2>';
    $settings_url = admin_url('edit.php?post_type=sps_product&page=sps-settings');
    echo '<p><a href="' . esc_url($settings_url) . '" target="_blank">Go to Settings Page</a></p>';
    
} else {
    echo '<p>❌ Plugin is not active</p>';
}
?>
