<?php
/**
 * Force fix permalink - aggressive approach
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/force-fix-permalink.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>🚀 Force Fix Permalink</h1>";

// 1. Force permalink structure
echo "<h2>1. Force Set Permalink Structure</h2>";
update_option('permalink_structure', '/%postname%/');
echo "<p>✅ Permalink structure forced to '/%postname%/'</p>";

// 2. Force register custom post type dengan parameter yang lebih eksplisit
echo "<h2>2. Force Register Custom Post Type</h2>";

// Unregister dulu jika sudah ada
if (post_type_exists('sps_product')) {
    unregister_post_type('sps_product');
    echo "<p>🔄 Unregistered existing 'sps_product' post type</p>";
}

// Register dengan parameter yang sangat eksplisit
$labels = array(
    'name' => 'Products',
    'singular_name' => 'Product',
    'menu_name' => 'Products',
    'add_new' => 'Add New',
    'add_new_item' => 'Add New Product',
    'edit_item' => 'Edit Product',
    'new_item' => 'New Product',
    'view_item' => 'View Product',
    'search_items' => 'Search Products',
    'not_found' => 'No products found',
    'not_found_in_trash' => 'No products found in trash',
);

$args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'rewrite' => array(
        'slug' => 'product',
        'with_front' => false,
        'pages' => true,
        'feeds' => true,
    ),
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => 5,
    'menu_icon' => 'dashicons-products',
    'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
    'show_in_rest' => true,
    'rest_base' => 'products',
    'rest_controller_class' => 'WP_REST_Posts_Controller',
);

register_post_type('sps_product', $args);
echo "<p>✅ Custom post type 'sps_product' force registered</p>";

// 3. Force flush rewrite rules
echo "<h2>3. Force Flush Rewrite Rules</h2>";
global $wp_rewrite;
$wp_rewrite->init();
$wp_rewrite->flush_rules(true);
echo "<p>✅ Rewrite rules force flushed</p>";

// 4. Test immediately
echo "<h2>4. Immediate Test</h2>";
$products = get_posts(array(
    'post_type' => 'sps_product',
    'numberposts' => 3,
    'post_status' => 'publish'
));

if ($products) {
    foreach ($products as $product) {
        echo "<h3>📦 Testing: {$product->post_title}</h3>";
        
        // Clear any caches
        clean_post_cache($product->ID);
        
        // Get fresh permalink
        $permalink = get_permalink($product->ID);
        echo "<p><strong>Fresh permalink:</strong> <a href='{$permalink}' target='_blank'>{$permalink}</a></p>";
        
        // Check if it contains the slug
        if (strpos($permalink, $product->post_name) !== false) {
            echo "<p>🎉 <strong>SUCCESS:</strong> URL menggunakan slug '{$product->post_name}'</p>";
        } else {
            echo "<p>❌ <strong>STILL FAILING:</strong> URL: {$permalink}</p>";
            echo "<p><strong>Expected to contain:</strong> {$product->post_name}</p>";
            
            // Try alternative approach
            $alt_url = home_url('/product/' . $product->post_name . '/');
            echo "<p><strong>Alternative URL:</strong> <a href='{$alt_url}' target='_blank'>{$alt_url}</a></p>";
        }
        
        // Test SPS function
        if (class_exists('SPS_Settings')) {
            $sps_url = SPS_Settings::get_product_detail_url($product->ID);
            echo "<p><strong>SPS URL:</strong> <a href='{$sps_url}' target='_blank'>{$sps_url}</a></p>";
        }
        
        echo "<hr>";
    }
} else {
    echo "<p>❌ No products found!</p>";
}

// 5. Check rewrite rules
echo "<h2>5. Rewrite Rules Status</h2>";
$rules = $wp_rewrite->wp_rewrite_rules();
$product_rules = array_filter($rules, function($rule, $pattern) {
    return strpos($pattern, 'product') !== false;
}, ARRAY_FILTER_USE_BOTH);

if ($product_rules) {
    echo "<p>✅ Found " . count($product_rules) . " rewrite rules for 'product':</p>";
    foreach ($product_rules as $pattern => $rule) {
        echo "<p><code>{$pattern}</code> => <code>{$rule}</code></p>";
    }
} else {
    echo "<p>❌ No rewrite rules found for 'product'!</p>";
}

// 6. Final verification
echo "<h2>6. Final Verification</h2>";
$permalink_structure = get_option('permalink_structure');
echo "<p><strong>Final permalink structure:</strong> '{$permalink_structure}'</p>";

$post_type_obj = get_post_type_object('sps_product');
if ($post_type_obj) {
    echo "<p>✅ Custom post type exists and is public</p>";
    echo "<p><strong>Rewrite slug:</strong> " . ($post_type_obj->rewrite['slug'] ?? 'Not set') . "</p>";
} else {
    echo "<p>❌ Custom post type not found!</p>";
}

echo "<h2>7. Manual Test Instructions</h2>";
echo "<ol>";
echo "<li>Go to your product page</li>";
echo "<li>Click the 'Detail' button</li>";
echo "<li>Check if URL now uses slug instead of product_id</li>";
echo "<li>Expected: wp.test/product/product-slug/</li>";
echo "<li>Not: wp.test/show-product/?product_id=28</li>";
echo "</ol>";

echo "<p><strong>If still not working, try:</strong></li>";
echo "<ol>";
echo "<li>Deactivate and reactivate the plugin</li>";
echo "<li>Go to Settings → Permalinks and click 'Save Changes'</li>";
echo "<li>Clear any caching plugins</li>";
echo "</ol>";
?>
