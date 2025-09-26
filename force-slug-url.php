<?php
/**
 * Force slug-based URL untuk produk
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/force-slug-url.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Force Slug-based URL untuk Produk</h1>";

// 1. Pastikan permalink structure tidak kosong
echo "<h2>1. Checking Permalink Structure...</h2>";
$permalink_structure = get_option('permalink_structure');
echo "<p><strong>Current Permalink Structure:</strong> '{$permalink_structure}'</p>";

if (empty($permalink_structure)) {
    echo "<p>⚠️ <strong>MASALAH DITEMUKAN:</strong> Permalink structure kosong!</p>";
    echo "<p>🔧 <strong>SOLUSI:</strong> Silakan pergi ke <strong>Settings → Permalinks</strong> dan pilih <strong>'Post name'</strong></p>";
    echo "<p>Atau jalankan perintah ini:</p>";
    echo "<pre>update_option('permalink_structure', '/%postname%/');</pre>";
    
    // Auto-fix jika user mengizinkan
    if (isset($_GET['auto_fix']) && $_GET['auto_fix'] === '1') {
        update_option('permalink_structure', '/%postname%/');
        echo "<p>✅ <strong>FIXED:</strong> Permalink structure telah diubah ke '/%postname%/'</p>";
        $permalink_structure = get_option('permalink_structure');
        echo "<p><strong>New Permalink Structure:</strong> '{$permalink_structure}'</p>";
    } else {
        echo "<p><a href='?auto_fix=1' style='background: #0073aa; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;'>🔧 Auto Fix Permalink Structure</a></p>";
    }
} else {
    echo "<p>✅ Permalink structure sudah diatur dengan benar.</p>";
}

// 2. Flush rewrite rules
echo "<h2>2. Flushing Rewrite Rules...</h2>";
flush_rewrite_rules(true);
echo "<p>✅ Rewrite rules telah di-flush.</p>";

// 3. Test produk
echo "<h2>3. Testing Product URLs...</h2>";
$products = get_posts(array(
    'post_type' => 'sps_product',
    'numberposts' => 5,
    'post_status' => 'publish'
));

if ($products) {
    foreach ($products as $product) {
        echo "<h3>ID: {$product->ID} - {$product->post_title}</h3>";
        echo "<p><strong>Slug:</strong> {$product->post_name}</p>";
        
        // Test get_permalink
        $permalink = get_permalink($product->ID);
        echo "<p><strong>get_permalink():</strong> <a href='{$permalink}' target='_blank'>{$permalink}</a></p>";
        
        // Test apakah menggunakan slug
        if (strpos($permalink, $product->post_name) !== false) {
            echo "<p>✅ <strong>BERHASIL:</strong> URL menggunakan slug '{$product->post_name}'</p>";
        } else {
            echo "<p>❌ <strong>GAGAL:</strong> URL TIDAK menggunakan slug!</p>";
            
            // Coba buat URL manual
            $manual_url = home_url('/product/' . $product->post_name . '/');
            echo "<p><strong>Manual URL:</strong> <a href='{$manual_url}' target='_blank'>{$manual_url}</a></p>";
        }
        
        echo "<hr>";
    }
} else {
    echo "<p>Tidak ada produk yang ditemukan.</p>";
}

// 4. Test rewrite rules
echo "<h2>4. Rewrite Rules Check...</h2>";
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
}

// 5. Re-register custom post type
echo "<h2>5. Re-registering Custom Post Type...</h2>";
if (class_exists('SPS_CPT')) {
    SPS_CPT::register();
    echo "<p>✅ Custom post type re-registered.</p>";
} else {
    echo "<p>❌ SPS_CPT class tidak ditemukan.</p>";
}

// 6. Final flush
flush_rewrite_rules(true);
echo "<p>✅ Final rewrite rules flush completed.</p>";

echo "<h2>6. Instructions:</h2>";
echo "<ol>";
echo "<li>Jika permalink structure masih kosong, klik tombol 'Auto Fix' di atas</li>";
echo "<li>Atau manual: Pergi ke <strong>Settings → Permalinks</strong> dan pilih <strong>'Post name'</strong></li>";
echo "<li>Setelah itu, klik <strong>Save Changes</strong></li>";
echo "<li>Refresh halaman produk Anda</li>";
echo "<li>Tombol 'Detail' seharusnya sekarang mengarah ke URL dengan slug, bukan ID</li>";
echo "</ol>";

echo "<p><strong>Expected URL format:</strong> <code>wp.test/product/product-name/</code></p>";
echo "<p><strong>NOT:</strong> <code>wp.test/show-product/?product_id=123</code></p>";
?>
