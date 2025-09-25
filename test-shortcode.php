<?php
/**
 * Test Shortcode
 * Access via: wp.test/wp-content/plugins/simple-product-showcase/test-shortcode.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<h1>Test Shortcode</h1>';

// Check if plugin is active
$active_plugins = get_option('active_plugins');
$plugin_file = 'simple-product-showcase/simple-product-showcase.php';

if (in_array($plugin_file, $active_plugins)) {
    echo '<p>✅ Plugin is active</p>';
    
    // Check if SPS_Shortcodes class exists
    if (class_exists('SPS_Shortcodes')) {
        echo '<p>✅ SPS_Shortcodes class exists</p>';
        
        // Check if shortcode is registered
        global $shortcode_tags;
        if (isset($shortcode_tags['sps_products'])) {
            echo '<p>✅ sps_products shortcode is registered</p>';
        } else {
            echo '<p>❌ sps_products shortcode is NOT registered</p>';
        }
        
        // Check if we have any products
        $products = get_posts(array(
            'post_type' => 'sps_product',
            'post_status' => 'publish',
            'numberposts' => 5
        ));
        
        echo '<p>Products found: ' . count($products) . '</p>';
        
        if (count($products) > 0) {
            echo '<h2>Found Products:</h2>';
            foreach ($products as $product) {
                echo '<p>- ' . $product->post_title . ' (ID: ' . $product->ID . ')</p>';
            }
            
            // Test shortcode execution
            echo '<h2>Testing Shortcode:</h2>';
            echo do_shortcode('[sps_products]');
            
        } else {
            echo '<p>❌ No products found. Please create some products first.</p>';
            echo '<p><a href="' . admin_url('post-new.php?post_type=sps_product') . '" target="_blank">Create New Product</a></p>';
        }
        
    } else {
        echo '<p>❌ SPS_Shortcodes class does not exist</p>';
    }
    
} else {
    echo '<p>❌ Plugin is not active</p>';
}

echo '<h2>Debug Info:</h2>';
echo '<p>WordPress Version: ' . get_bloginfo('version') . '</p>';
echo '<p>PHP Version: ' . phpversion() . '</p>';
echo '<p>Plugin Directory: ' . __DIR__ . '</p>';

// Check if CPT is registered
$post_types = get_post_types(array('public' => true), 'names');
if (in_array('sps_product', $post_types)) {
    echo '<p>✅ sps_product post type is registered</p>';
} else {
    echo '<p>❌ sps_product post type is NOT registered</p>';
}

// Check if taxonomy is registered
$taxonomies = get_taxonomies(array('public' => true), 'names');
if (in_array('sps_product_category', $taxonomies)) {
    echo '<p>✅ sps_product_category taxonomy is registered</p>';
} else {
    echo '<p>❌ sps_product_category taxonomy is NOT registered</p>';
}
?>
