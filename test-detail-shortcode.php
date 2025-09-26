<?php
/**
 * Test Detail Shortcode
 * Access via: wp.test/wp-content/plugins/simple-product-showcase/test-detail-shortcode.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<h1>Test Detail Shortcode</h1>';

// Check if plugin is active
$active_plugins = get_option('active_plugins');
$plugin_file = 'simple-product-showcase/simple-product-showcase.php';

if (in_array($plugin_file, $active_plugins)) {
    echo '<p>✅ Plugin is active</p>';
    
    // Test different sections
    echo '<h2>Testing Detail Shortcode Sections:</h2>';
    
    echo '<h3>Title:</h3>';
    echo do_shortcode('[sps_detail_products section="title"]');
    
    echo '<h3>Image:</h3>';
    echo do_shortcode('[sps_detail_products section="image"]');
    
    echo '<h3>Price:</h3>';
    echo do_shortcode('[sps_detail_products section="price"]');
    
    echo '<h3>Description:</h3>';
    echo do_shortcode('[sps_detail_products section="description"]');
    
    echo '<h3>Gallery (Grid):</h3>';
    echo do_shortcode('[sps_detail_products section="gallery" style="grid"]');
    
    echo '<h3>Gallery (Slider):</h3>';
    echo do_shortcode('[sps_detail_products section="gallery" style="slider"]');
    
    echo '<h3>WhatsApp:</h3>';
    echo do_shortcode('[sps_detail_products section="whatsapp"]');
    
} else {
    echo '<p>❌ Plugin is not active</p>';
}
?>
