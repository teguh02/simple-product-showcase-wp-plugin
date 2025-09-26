<?php
/**
 * Test Padding Layout
 * Access via: wp.test/wp-content/plugins/simple-product-showcase/test-padding-layout.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<h1>Test Padding Layout</h1>';

// Check if plugin is active
$active_plugins = get_option('active_plugins');
$plugin_file = 'simple-product-showcase/simple-product-showcase.php';

if (in_array($plugin_file, $active_plugins)) {
    echo '<p>✅ Plugin is active</p>';
    
    // Test the shortcode
    echo '<h2>Testing Padding Layout:</h2>';
    echo do_shortcode('[sps_products]');
    
} else {
    echo '<p>❌ Plugin is not active</p>';
}
?>
