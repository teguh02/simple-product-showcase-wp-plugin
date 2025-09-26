<?php
/**
 * Test permalink untuk produk
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/test-permalink.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Test Permalink untuk Produk</h1>";

// Ambil beberapa produk untuk test
$products = get_posts(array(
    'post_type' => 'sps_product',
    'numberposts' => 3,
    'post_status' => 'publish'
));

if ($products) {
    echo "<h2>Produk yang ditemukan:</h2>";
    foreach ($products as $product) {
        echo "<h3>ID: {$product->ID} - {$product->post_title}</h3>";
        echo "<p><strong>Slug:</strong> {$product->post_name}</p>";
        
        // Test get_permalink
        $permalink = get_permalink($product->ID);
        echo "<p><strong>get_permalink():</strong> <a href='{$permalink}' target='_blank'>{$permalink}</a></p>";
        
        // Test SPS_Settings::get_product_detail_url
        if (class_exists('SPS_Settings')) {
            $detail_url = SPS_Settings::get_product_detail_url($product->ID);
            echo "<p><strong>SPS_Settings::get_product_detail_url():</strong> <a href='{$detail_url}' target='_blank'>{$detail_url}</a></p>";
        }
        
        // Test permalink structure
        $custom_permalink = home_url('/product/' . $product->post_name . '/');
        echo "<p><strong>Expected URL:</strong> <a href='{$custom_permalink}' target='_blank'>{$custom_permalink}</a></p>";
        
        echo "<hr>";
    }
} else {
    echo "<p>Tidak ada produk yang ditemukan.</p>";
}

// Test permalink settings
echo "<h2>WordPress Permalink Settings:</h2>";
$permalink_structure = get_option('permalink_structure');
echo "<p><strong>Permalink Structure:</strong> {$permalink_structure}</p>";

// Test rewrite rules
global $wp_rewrite;
echo "<h2>Rewrite Rules untuk 'product':</h2>";
$rules = $wp_rewrite->wp_rewrite_rules();
$product_rules = array_filter($rules, function($rule, $pattern) {
    return strpos($pattern, 'product') !== false;
}, ARRAY_FILTER_USE_BOTH);

if ($product_rules) {
    foreach ($product_rules as $pattern => $rule) {
        echo "<p><strong>{$pattern}</strong> => {$rule}</p>";
    }
} else {
    echo "<p>Tidak ada rewrite rules untuk 'product' ditemukan.</p>";
}

// Test apakah post type terdaftar dengan benar
echo "<h2>Custom Post Type Info:</h2>";
$post_type_obj = get_post_type_object('sps_product');
if ($post_type_obj) {
    echo "<p><strong>Public:</strong> " . ($post_type_obj->public ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Publicly Queryable:</strong> " . ($post_type_obj->publicly_queryable ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Query Var:</strong> " . ($post_type_obj->query_var ? $post_type_obj->query_var : 'No') . "</p>";
    echo "<p><strong>Rewrite Slug:</strong> " . ($post_type_obj->rewrite['slug'] ?? 'Not set') . "</p>";
} else {
    echo "<p>Custom post type 'sps_product' tidak ditemukan.</p>";
}
?>
