<?php
/**
 * Simple Meta Box Test
 * 
 * File ini untuk testing meta box dengan cara yang lebih sederhana
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/test-meta-box-simple.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin privileges
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to access this page.');
}

echo '<h1>Simple Meta Box Test</h1>';

// Check plugin status
if (is_plugin_active('simple-product-showcase/simple-product-showcase.php')) {
    echo '<p style="color: green;">✅ Plugin is ACTIVE</p>';
} else {
    echo '<p style="color: red;">❌ Plugin is NOT active</p>';
    exit;
}

// Check class
if (class_exists('SPS_CPT')) {
    echo '<p style="color: green;">✅ SPS_CPT class is loaded</p>';
} else {
    echo '<p style="color: red;">❌ SPS_CPT class is NOT loaded</p>';
    exit;
}

// Check post type
$post_type_object = get_post_type_object('sps_product');
if ($post_type_object) {
    echo '<p style="color: green;">✅ sps_product post type exists</p>';
} else {
    echo '<p style="color: red;">❌ sps_product post type does NOT exist</p>';
    exit;
}

// Check if block editor is disabled
if (function_exists('use_block_editor_for_post_type')) {
    $use_block_editor = use_block_editor_for_post_type('sps_product');
    echo '<p>Block Editor for sps_product: ' . ($use_block_editor ? 'YES (This might cause issues)' : 'NO (Good!)') . '</p>';
}

// Get products
$products = get_posts(array(
    'post_type' => 'sps_product',
    'posts_per_page' => 5,
    'post_status' => 'any'
));

echo '<h2>Products Found:</h2>';
if ($products) {
    echo '<ul>';
    foreach ($products as $product) {
        echo '<li><a href="' . admin_url('post.php?post=' . $product->ID . '&action=edit') . '" target="_blank">' . $product->post_title . ' (ID: ' . $product->ID . ')</a></li>';
    }
    echo '</ul>';
} else {
    echo '<p style="color: orange;">⚠️ No products found</p>';
}

echo '<h2>Test Steps:</h2>';
echo '<ol>';
echo '<li>Click on any product link above to edit it</li>';
echo '<li>Look in the <strong>right sidebar</strong> for meta boxes</li>';
echo '<li>You should see: "Product Price", "WhatsApp Settings", and "Product Gallery"</li>';
echo '<li>If you see all three, the meta boxes are working correctly</li>';
echo '</ol>';

echo '<h2>If Meta Boxes Don\'t Appear:</h2>';
echo '<ol>';
echo '<li>Try refreshing the page</li>';
echo '<li>Check if you\'re using a custom admin theme</li>';
echo '<li>Try deactivating and reactivating the plugin</li>';
echo '<li>Check browser console for JavaScript errors</li>';
echo '</ol>';

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';
?>
