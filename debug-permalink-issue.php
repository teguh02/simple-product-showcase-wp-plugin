<?php
/**
 * Debug permalink issue - langsung perbaiki masalah
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/debug-permalink-issue.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>🔍 Debug Permalink Issue</h1>";

// 1. Check permalink structure
echo "<h2>1. Permalink Structure Check</h2>";
$permalink_structure = get_option('permalink_structure');
echo "<p><strong>Current permalink_structure:</strong> '{$permalink_structure}'</p>";

if (empty($permalink_structure)) {
    echo "<p>❌ <strong>MASALAH DITEMUKAN:</strong> Permalink structure KOSONG!</p>";
    echo "<p>🔧 <strong>MEMPERBAIKI...</strong></p>";
    update_option('permalink_structure', '/%postname%/');
    echo "<p>✅ <strong>FIXED:</strong> Permalink structure diubah ke '/%postname%/'</p>";
    $permalink_structure = get_option('permalink_structure');
    echo "<p><strong>New permalink_structure:</strong> '{$permalink_structure}'</p>";
} else {
    echo "<p>✅ Permalink structure sudah diatur: '{$permalink_structure}'</p>";
}

// 2. Check custom post type registration
echo "<h2>2. Custom Post Type Check</h2>";
$post_type_obj = get_post_type_object('sps_product');
if ($post_type_obj) {
    echo "<p>✅ Custom post type 'sps_product' terdaftar</p>";
    echo "<p><strong>Public:</strong> " . ($post_type_obj->public ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Publicly Queryable:</strong> " . ($post_type_obj->publicly_queryable ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Rewrite Slug:</strong> " . ($post_type_obj->rewrite['slug'] ?? 'Not set') . "</p>";
    
    // Re-register dengan force
    echo "<p>🔄 <strong>Re-registering custom post type...</strong></p>";
    if (class_exists('SPS_CPT')) {
        SPS_CPT::register();
        echo "<p>✅ Custom post type re-registered</p>";
    }
} else {
    echo "<p>❌ Custom post type 'sps_product' TIDAK terdaftar!</p>";
}

// 3. Flush rewrite rules
echo "<h2>3. Flush Rewrite Rules</h2>";
flush_rewrite_rules(true);
echo "<p>✅ Rewrite rules flushed</p>";

// 4. Test produk
echo "<h2>4. Test Product URLs</h2>";
$products = get_posts(array(
    'post_type' => 'sps_product',
    'numberposts' => 3,
    'post_status' => 'publish'
));

if ($products) {
    foreach ($products as $product) {
        echo "<h3>📦 Product: {$product->post_title} (ID: {$product->ID})</h3>";
        echo "<p><strong>Slug:</strong> {$product->post_name}</p>";
        
        // Test get_permalink
        $permalink = get_permalink($product->ID);
        echo "<p><strong>get_permalink():</strong> <a href='{$permalink}' target='_blank'>{$permalink}</a></p>";
        
        // Check if using slug
        if (strpos($permalink, $product->post_name) !== false) {
            echo "<p>✅ <strong>BERHASIL:</strong> URL menggunakan slug '{$product->post_name}'</p>";
        } else {
            echo "<p>❌ <strong>MASIH GAGAL:</strong> URL TIDAK menggunakan slug!</p>";
            
            // Try manual URL construction
            $manual_url = home_url('/product/' . $product->post_name . '/');
            echo "<p><strong>Manual URL:</strong> <a href='{$manual_url}' target='_blank'>{$manual_url}</a></p>";
            
            // Test if manual URL works
            $response = wp_remote_get($manual_url);
            if (!is_wp_error($response)) {
                $status = wp_remote_retrieve_response_code($response);
                echo "<p><strong>Manual URL Status:</strong> {$status}</p>";
                if ($status === 200) {
                    echo "<p>✅ Manual URL bisa diakses!</p>";
                }
            }
        }
        
        // Test SPS_Settings function
        if (class_exists('SPS_Settings')) {
            $detail_url = SPS_Settings::get_product_detail_url($product->ID);
            echo "<p><strong>SPS Detail URL:</strong> <a href='{$detail_url}' target='_blank'>{$detail_url}</a></p>";
        }
        
        echo "<hr>";
    }
} else {
    echo "<p>❌ Tidak ada produk ditemukan!</p>";
}

// 5. Check rewrite rules
echo "<h2>5. Rewrite Rules Check</h2>";
global $wp_rewrite;
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
    echo "<p>❌ Tidak ada rewrite rules untuk 'product'!</p>";
    
    // Try to force register
    echo "<p>🔄 <strong>Attempting to force register custom post type...</strong></p>";
    
    $args = array(
        'labels' => array(
            'name' => 'Products',
            'singular_name' => 'Product',
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'product'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => true,
    );
    
    register_post_type('sps_product', $args);
    flush_rewrite_rules(true);
    echo "<p>✅ Custom post type force registered and rules flushed</p>";
}

// 6. Final test
echo "<h2>6. Final Test</h2>";
echo "<p>🔄 <strong>Testing one more time...</strong></p>";
if ($products) {
    $test_product = $products[0];
    $final_url = get_permalink($test_product->ID);
    echo "<p><strong>Final test URL:</strong> <a href='{$final_url}' target='_blank'>{$final_url}</a></p>";
    
    if (strpos($final_url, $test_product->post_name) !== false) {
        echo "<p>🎉 <strong>SUCCESS:</strong> URL sekarang menggunakan slug!</p>";
        echo "<p><strong>Expected format:</strong> wp.test/product/product-slug/</p>";
    } else {
        echo "<p>😞 <strong>STILL FAILING:</strong> URL masih tidak menggunakan slug</p>";
        echo "<p><strong>Current format:</strong> {$final_url}</p>";
    }
}

echo "<h2>7. Next Steps</h2>";
echo "<ol>";
echo "<li>Refresh halaman produk Anda</li>";
echo "<li>Klik tombol 'Detail' pada produk</li>";
echo "<li>Periksa apakah URL sekarang menggunakan slug</li>";
echo "<li>Jika masih bermasalah, coba deaktifkan dan aktifkan kembali plugin</li>";
echo "</ol>";
?>
