<?php
/**
 * Simple Meta Box Test
 * 
 * File ini untuk testing meta box tanpa registrasi langsung
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/force-meta-box.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin privileges
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to access this page.');
}

echo '<h1>Simple Meta Box Test</h1>';

// Check if plugin is active
if (is_plugin_active('simple-product-showcase/simple-product-showcase.php')) {
    echo '<p style="color: green;">✅ Plugin is ACTIVE</p>';
} else {
    echo '<p style="color: red;">❌ Plugin is NOT active</p>';
    exit;
}

// Check if SPS_CPT class exists
if (class_exists('SPS_CPT')) {
    echo '<p style="color: green;">✅ SPS_CPT class is loaded</p>';
} else {
    echo '<p style="color: red;">❌ SPS_CPT class is NOT loaded</p>';
    exit;
}

// Check if add_meta_box function is available
if (function_exists('add_meta_box')) {
    echo '<p style="color: green;">✅ add_meta_box() function is available</p>';
} else {
    echo '<p style="color: red;">❌ add_meta_box() function is NOT available</p>';
    echo '<p><strong>Note:</strong> This function is only available in WordPress admin context.</p>';
}

// Check WordPress admin functions
if (function_exists('is_admin')) {
    echo '<p>is_admin(): ' . (is_admin() ? 'TRUE' : 'FALSE') . '</p>';
}

// Check if we're in admin context
if (is_admin()) {
    echo '<p style="color: green;">✅ We are in admin context</p>';
} else {
    echo '<p style="color: orange;">⚠️ We are NOT in admin context</p>';
}

// Check post type
$post_type_object = get_post_type_object('sps_product');
if ($post_type_object) {
    echo '<p style="color: green;">✅ sps_product post type exists</p>';
} else {
    echo '<p style="color: red;">❌ sps_product post type does NOT exist</p>';
}

// Check if we have products
$products = get_posts(array(
    'post_type' => 'sps_product',
    'posts_per_page' => 1,
    'post_status' => 'any'
));

if ($products) {
    $product = $products[0];
    echo '<p style="color: green;">✅ Found product: ' . $product->post_title . ' (ID: ' . $product->ID . ')</p>';
} else {
    echo '<p style="color: orange;">⚠️ No products found</p>';
}

echo '<h2>Test Instructions:</h2>';
echo '<ol>';
echo '<li>Go to <a href="' . admin_url('post-new.php?post_type=sps_product') . '" target="_blank">Add New Product</a></li>';
echo '<li>Look for meta boxes in the <strong>right sidebar</strong></li>';
echo '<li>You should see "Product Price", "WhatsApp Settings", and "Product Gallery"</li>';
echo '<li>If you don\'t see "Product Gallery", there might be an issue with the meta box registration</li>';
echo '</ol>';

if ($products) {
    echo '<h2>Edit Existing Product:</h2>';
    echo '<p><a href="' . admin_url('post.php?post=' . $products[0]->ID . '&action=edit') . '" target="_blank">Edit: ' . $products[0]->post_title . '</a></p>';
}

echo '<h2>Debug Information:</h2>';
echo '<p>WordPress Version: ' . get_bloginfo('version') . '</p>';
echo '<p>Current Screen: ' . (function_exists('get_current_screen') ? (get_current_screen() ? get_current_screen()->id : 'Not available') : 'Not available') . '</p>';

// Check if block editor is being used
if (function_exists('use_block_editor_for_post_type')) {
    $use_block_editor = use_block_editor_for_post_type('sps_product');
    echo '<p>Block Editor for sps_product: ' . ($use_block_editor ? 'YES' : 'NO') . '</p>';
}

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';
?>
