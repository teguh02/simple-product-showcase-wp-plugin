<?php
/**
 * Test Data Persistence
 * 
 * File ini untuk testing apakah data produk tetap ada setelah plugin dinonaktifkan
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/test-data-persistence.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin privileges
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to access this page.');
}

echo '<h1>Test Data Persistence</h1>';

// Check plugin status
if (is_plugin_active('simple-product-showcase/simple-product-showcase.php')) {
    echo '<p style="color: green;">✅ Plugin is ACTIVE</p>';
} else {
    echo '<p style="color: orange;">⚠️ Plugin is DEACTIVATED</p>';
}

// Check if post type is registered
$post_types = get_post_types(array('public' => true), 'objects');
if (isset($post_types['sps_product'])) {
    echo '<p style="color: green;">✅ sps_product post type is REGISTERED</p>';
} else {
    echo '<p style="color: red;">❌ sps_product post type is NOT registered</p>';
}

// Check if taxonomy is registered
$taxonomies = get_taxonomies(array('public' => true), 'objects');
if (isset($taxonomies['sps_product_category'])) {
    echo '<p style="color: green;">✅ sps_product_category taxonomy is REGISTERED</p>';
} else {
    echo '<p style="color: red;">❌ sps_product_category taxonomy is NOT registered</p>';
}

// Check products in database
$products = get_posts(array(
    'post_type' => 'sps_product',
    'posts_per_page' => -1,
    'post_status' => 'any'
));

echo '<h2>Products in Database:</h2>';
if ($products) {
    echo '<p style="color: green;">✅ Found ' . count($products) . ' products in database</p>';
    echo '<ul>';
    foreach ($products as $product) {
        echo '<li>' . $product->post_title . ' (ID: ' . $product->ID . ', Status: ' . $product->post_status . ')</li>';
    }
    echo '</ul>';
} else {
    echo '<p style="color: red;">❌ No products found in database</p>';
}

// Check categories in database
$categories = get_terms(array(
    'taxonomy' => 'sps_product_category',
    'hide_empty' => false,
));

echo '<h2>Categories in Database:</h2>';
if ($categories && !is_wp_error($categories)) {
    echo '<p style="color: green;">✅ Found ' . count($categories) . ' categories in database</p>';
    echo '<ul>';
    foreach ($categories as $category) {
        echo '<li>' . $category->name . ' (ID: ' . $category->term_id . ', Count: ' . $category->count . ')</li>';
    }
    echo '</ul>';
} else {
    echo '<p style="color: red;">❌ No categories found in database</p>';
}

// Check if SPS_Persistent class exists
if (class_exists('SPS_Persistent')) {
    echo '<p style="color: green;">✅ SPS_Persistent class is loaded</p>';
} else {
    echo '<p style="color: red;">❌ SPS_Persistent class is NOT loaded</p>';
}

echo '<h2>Test Instructions:</h2>';
echo '<ol>';
echo '<li>Go to <a href="' . admin_url('edit.php?post_type=sps_product') . '">Products → All Products</a> to see if products are visible</li>';
echo '<li>Go to <a href="' . admin_url('edit-tags.php?taxonomy=sps_product_category&post_type=sps_product') . '">Products → Categories</a> to see if categories are visible</li>';
echo '<li>Deactivate the plugin and refresh this page to test persistence</li>';
echo '<li>Reactivate the plugin and check if everything works normally</li>';
echo '</ol>';

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';
?>
