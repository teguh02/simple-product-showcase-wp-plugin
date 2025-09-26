<?php
/**
 * Fix permalink untuk produk
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/fix-permalink.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Fix Permalink untuk Produk</h1>";

// 1. Flush rewrite rules
echo "<h2>1. Flushing Rewrite Rules...</h2>";
flush_rewrite_rules(true);
echo "<p>✅ Rewrite rules telah di-flush.</p>";

// 2. Test beberapa produk
echo "<h2>2. Testing Produk URLs...</h2>";
$products = get_posts(array(
    'post_type' => 'sps_product',
    'numberposts' => 3,
    'post_status' => 'publish'
));

if ($products) {
    foreach ($products as $product) {
        echo "<h3>ID: {$product->ID} - {$product->post_title}</h3>";
        
        // Test URL yang dihasilkan
        $permalink = get_permalink($product->ID);
        echo "<p><strong>Current URL:</strong> <a href='{$permalink}' target='_blank'>{$permalink}</a></p>";
        
        // Test apakah URL menggunakan slug atau ID
        if (strpos($permalink, $product->post_name) !== false) {
            echo "<p>✅ URL menggunakan slug: <strong>{$product->post_name}</strong></p>";
        } else {
            echo "<p>❌ URL TIDAK menggunakan slug!</p>";
        }
        
        // Test SPS_Settings function
        if (class_exists('SPS_Settings')) {
            $detail_url = SPS_Settings::get_product_detail_url($product->ID);
            echo "<p><strong>SPS Detail URL:</strong> <a href='{$detail_url}' target='_blank'>{$detail_url}</a></p>";
            
            if (strpos($detail_url, $product->post_name) !== false) {
                echo "<p>✅ SPS URL menggunakan slug: <strong>{$product->post_name}</strong></p>";
            } else {
                echo "<p>❌ SPS URL TIDAK menggunakan slug!</p>";
            }
        }
        
        echo "<hr>";
    }
} else {
    echo "<p>Tidak ada produk yang ditemukan.</p>";
}

// 3. Test permalink structure
echo "<h2>3. Permalink Structure Info:</h2>";
$permalink_structure = get_option('permalink_structure');
echo "<p><strong>WordPress Permalink Structure:</strong> {$permalink_structure}</p>";

if (empty($permalink_structure)) {
    echo "<p>⚠️ <strong>PERINGATAN:</strong> Permalink structure kosong! Ini akan menyebabkan URL menggunakan ID.</p>";
    echo "<p>Silakan pergi ke <strong>Settings → Permalinks</strong> dan pilih <strong>'Post name'</strong> atau struktur lainnya.</p>";
}

// 4. Test rewrite rules
echo "<h2>4. Rewrite Rules untuk 'product':</h2>";
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
    echo "<p>❌ Tidak ada rewrite rules untuk 'product' ditemukan!</p>";
}

// 5. Force re-register custom post type
echo "<h2>5. Re-registering Custom Post Type...</h2>";
if (class_exists('SPS_CPT')) {
    SPS_CPT::register();
    echo "<p>✅ Custom post type re-registered.</p>";
} else {
    echo "<p>❌ SPS_CPT class tidak ditemukan.</p>";
}

echo "<h2>6. Final Test:</h2>";
echo "<p>Silakan coba akses produk Anda lagi. Jika masih bermasalah:</p>";
echo "<ol>";
echo "<li>Pastikan permalink structure di <strong>Settings → Permalinks</strong> bukan 'Plain'</li>";
echo "<li>Setelah mengubah permalink, klik <strong>Save Changes</strong></li>";
echo "<li>Refresh halaman produk Anda</li>";
echo "</ol>";
?>
