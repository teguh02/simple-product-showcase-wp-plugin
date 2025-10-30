<?php
/**
 * Debug Script untuk Simple Product Showcase
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/debug.php
 */

// Load WordPress
// Find wp-load.php by going up directories
$wp_load = false;
$dir = dirname(__FILE__);
for ($i = 0; $i < 10; $i++) {
    $dir = dirname($dir);
    if (file_exists($dir . '/wp-load.php')) {
        $wp_load = $dir . '/wp-load.php';
        break;
    }
}

if (!$wp_load) {
    die('Could not find wp-load.php. Please check your WordPress installation.');
}

require_once($wp_load);

echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
    h2 { color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #0073aa; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .debug-box { background: #fffbcc; border: 1px solid #ffeb3b; padding: 15px; margin: 15px 0; border-radius: 3px; }
    code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
</style>';

echo '<div class="container">';
echo '<h1>🔍 Simple Product Showcase - Database Debug</h1>';

// Check WordPress and Plugin
echo '<h2>1. Plugin Status</h2>';
if (class_exists('SPS_Init')) {
    echo '<div class="debug-box">✅ Plugin loaded successfully</div>';
} else {
    echo '<div class="debug-box">❌ Plugin not loaded</div>';
}

// Get all categories
echo '<h2>2. Product Categories</h2>';
$categories = get_terms(array(
    'taxonomy' => 'sps_product_category',
    'hide_empty' => false,
    'number' => 100
));

echo '<table>';
echo '<tr><th>Term ID</th><th>Name</th><th>Slug</th><th>Parent ID</th><th>Count</th></tr>';
foreach ($categories as $cat) {
    echo '<tr>';
    echo '<td>' . $cat->term_id . '</td>';
    echo '<td>' . $cat->name . '</td>';
    echo '<td><code>' . $cat->slug . '</code></td>';
    echo '<td>' . ($cat->parent ? $cat->parent : '-') . '</td>';
    echo '<td>' . $cat->count . '</td>';
    echo '</tr>';
}
echo '</table>';

// Get all products
echo '<h2>3. All Products</h2>';
$products = get_posts(array(
    'post_type' => 'sps_product',
    'posts_per_page' => 100,
    'post_status' => 'publish'
));

echo '<p><strong>Published Products:</strong> ' . count($products) . '</p>';

echo '<table>';
echo '<tr><th>ID</th><th>Title</th><th>Status</th><th>Categories</th></tr>';
foreach ($products as $prod) {
    $cats = wp_get_post_terms($prod->ID, 'sps_product_category', array('fields' => 'names'));
    $cat_names = !empty($cats) ? implode(', ', $cats) : 'None';
    
    echo '<tr>';
    echo '<td>' . $prod->ID . '</td>';
    echo '<td>' . $prod->post_title . '</td>';
    echo '<td>' . $prod->post_status . '</td>';
    echo '<td>' . $cat_names . '</td>';
    echo '</tr>';
}
echo '</table>';

// Get ALL products including unpublished
echo '<h2>3b. All Products (Including Unpublished)</h2>';
$all_products = get_posts(array(
    'post_type' => 'sps_product',
    'posts_per_page' => 100,
    'post_status' => array('publish', 'draft', 'pending', 'private', 'inherit')
));

echo '<p><strong>All Products (Any Status):</strong> ' . count($all_products) . '</p>';

echo '<table>';
echo '<tr><th>ID</th><th>Title</th><th>Status</th><th>Categories</th></tr>';
foreach ($all_products as $prod) {
    $cats = wp_get_post_terms($prod->ID, 'sps_product_category', array('fields' => 'names'));
    $cat_names = !empty($cats) ? implode(', ', $cats) : 'None';
    
    echo '<tr>';
    echo '<td>' . $prod->ID . '</td>';
    echo '<td>' . $prod->post_title . '</td>';
    echo '<td><strong>' . $prod->post_status . '</strong></td>';
    echo '<td>' . $cat_names . '</td>';
    echo '</tr>';
}
echo '</table>';

// Test queries
echo '<h2>4. Test Queries</h2>';

// Test search Tes Kategori
echo '<h3>4.1 Search "Tes Kategori"</h3>';
$test_term_by_name = get_term_by('name', 'Tes Kategori', 'sps_product_category');
$test_term_by_slug = get_term_by('slug', 'tes-kategori', 'sps_product_category');

echo '<div class="debug-box">';
echo 'By Name "Tes Kategori": ';
if ($test_term_by_name && !is_wp_error($test_term_by_name)) {
    echo '✅ Found (ID: ' . $test_term_by_name->term_id . ', Slug: ' . $test_term_by_slug->slug . ')';
} else {
    echo '❌ Not found';
}
echo '<br>';

echo 'By Slug "tes-kategori": ';
if ($test_term_by_slug && !is_wp_error($test_term_by_slug)) {
    echo '✅ Found (ID: ' . $test_term_by_slug->term_id . ')';
} else {
    echo '❌ Not found';
}
echo '</div>';

// Test query for Tes Kategori products
if ($test_term_by_name && !is_wp_error($test_term_by_name)) {
    echo '<h3>4.2 Products in "Tes Kategori" (Direct Query)</h3>';
    $parent_id = $test_term_by_name->term_id;
    
    $products_in_cat = get_posts(array(
        'post_type' => 'sps_product',
        'posts_per_page' => 100,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'sps_product_category',
                'field' => 'term_id',
                'terms' => $parent_id
            )
        )
    ));
    
    echo '<div class="debug-box">';
    echo 'Direct products in "Tes Kategori": ' . count($products_in_cat) . ' found<br>';
    foreach ($products_in_cat as $p) {
        echo '- ' . $p->post_title . ' (ID: ' . $p->ID . ')<br>';
    }
    echo '</div>';
    
    // Test query with children
    echo '<h3>4.3 Sub-categories of "Tes Kategori"</h3>';
    $sub_cats = get_terms(array(
        'taxonomy' => 'sps_product_category',
        'parent' => $parent_id,
        'hide_empty' => false
    ));
    
    echo '<div class="debug-box">';
    echo 'Sub-categories: ' . count($sub_cats) . '<br>';
    foreach ($sub_cats as $sc) {
        echo '- ' . $sc->name . ' (ID: ' . $sc->term_id . ', Count: ' . $sc->count . ')<br>';
    }
    echo '</div>';
    
    // Test query with parent + children
    echo '<h3>4.4 Products in "Tes Kategori" + All Sub-categories (Include Children)</h3>';
    $child_term_ids = wp_list_pluck($sub_cats, 'term_id');
    $all_term_ids = array_merge(array($parent_id), $child_term_ids);
    
    $products_with_children = get_posts(array(
        'post_type' => 'sps_product',
        'posts_per_page' => 100,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'sps_product_category',
                'field' => 'term_id',
                'terms' => $all_term_ids,
                'include_children' => false
            )
        )
    ));
    
    echo '<div class="debug-box">';
    echo 'Products with children included: ' . count($products_with_children) . ' found<br>';
    foreach ($products_with_children as $p) {
        echo '- ' . $p->post_title . ' (ID: ' . $p->ID . ')<br>';
    }
    echo '</div>';
}

// Test normalized slug
echo '<h3>4.5 Slug Normalization</h3>';
$test_input = 'Tes Kategori';
$normalized = strtolower(str_replace(' ', '-', $test_input));
echo '<div class="debug-box">';
echo 'Input: <code>' . $test_input . '</code><br>';
echo 'Normalized: <code>' . $normalized . '</code>';
echo '</div>';

echo '</div>';
?>
