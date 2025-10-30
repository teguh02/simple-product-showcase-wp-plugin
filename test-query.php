<?php
/**
 * Manual SQL Query Test untuk Simple Product Showcase
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/test-query.php?category=Tes%20Kategori
 */

// Load WordPress
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
    die('Could not find wp-load.php');
}

require_once($wp_load);

global $wpdb;

echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
    h2 { color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 12px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #0073aa; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .debug-box { background: #fffbcc; border: 1px solid #ffeb3b; padding: 15px; margin: 15px 0; border-radius: 3px; }
    code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
    .sql { background: #f0f0f0; border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 3px; overflow-x: auto; }
    pre { margin: 0; }
</style>';

echo '<div class="container">';
echo '<h1>🔍 Manual SQL Query Test</h1>';

$category_param = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'Tes Kategori';
echo '<div class="debug-box"><strong>Category Parameter:</strong> <code>' . $category_param . '</code></div>';

// Table names
$terms_table = $wpdb->prefix . 'terms';
$term_taxonomy_table = $wpdb->prefix . 'term_taxonomy';
$term_relationships_table = $wpdb->prefix . 'term_relationships';
$posts_table = $wpdb->prefix . 'posts';

echo '<h2>1. Find Category Term ID</h2>';
$sql1 = "SELECT t.term_id, t.name, t.slug, tt.parent, tt.taxonomy, tt.count 
FROM {$terms_table} t 
JOIN {$term_taxonomy_table} tt ON t.term_id = tt.term_id 
WHERE tt.taxonomy = 'sps_product_category' 
AND (t.name = '%s' OR t.slug = '%s')
LIMIT 1";

$sql1_prepared = $wpdb->prepare($sql1, $category_param, sanitize_title($category_param));
echo '<div class="sql"><pre>' . htmlspecialchars($sql1_prepared) . '</pre></div>';

$term = $wpdb->get_row($sql1_prepared);
echo '<table>';
echo '<tr><th>Term ID</th><th>Name</th><th>Slug</th><th>Parent ID</th><th>Taxonomy</th><th>Count</th></tr>';
if ($term) {
    echo '<tr>';
    echo '<td>' . $term->term_id . '</td>';
    echo '<td>' . $term->name . '</td>';
    echo '<td>' . $term->slug . '</td>';
    echo '<td>' . $term->parent . '</td>';
    echo '<td>' . $term->taxonomy . '</td>';
    echo '<td>' . $term->count . '</td>';
    echo '</tr>';
} else {
    echo '<tr><td colspan="6">❌ No term found</td></tr>';
}
echo '</table>';

if ($term) {
    $term_id = $term->term_id;
    $parent_id = $term->parent;
    
    // Get child terms
    echo '<h2>2. Find Child Terms (if parent has children)</h2>';
    $sql2 = "SELECT t.term_id, t.name, t.slug, tt.count 
    FROM {$terms_table} t 
    JOIN {$term_taxonomy_table} tt ON t.term_id = tt.term_id 
    WHERE tt.taxonomy = 'sps_product_category' 
    AND tt.parent = %d";
    
    $sql2_prepared = $wpdb->prepare($sql2, $term_id);
    echo '<div class="sql"><pre>' . htmlspecialchars($sql2_prepared) . '</pre></div>';
    
    $children = $wpdb->get_results($sql2_prepared);
    echo '<table>';
    echo '<tr><th>Child Term ID</th><th>Name</th><th>Slug</th><th>Count</th></tr>';
    if (count($children) > 0) {
        foreach ($children as $child) {
            echo '<tr>';
            echo '<td>' . $child->term_id . '</td>';
            echo '<td>' . $child->name . '</td>';
            echo '<td>' . $child->slug . '</td>';
            echo '<td>' . $child->count . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="4">No children found</td></tr>';
    }
    echo '</table>';
    
    // Query 1: Products directly tagged to parent category
    echo '<h2>3. Products directly tagged to parent category (ID: ' . $term_id . ')</h2>';
    $sql3 = "SELECT p.ID, p.post_title, p.post_status 
    FROM {$posts_table} p 
    JOIN {$term_relationships_table} tr ON p.ID = tr.object_id 
    WHERE p.post_type = 'sps_product' 
    AND p.post_status = 'publish' 
    AND tr.term_taxonomy_id IN (
        SELECT term_taxonomy_id FROM {$term_taxonomy_table} 
        WHERE term_id = %d AND taxonomy = 'sps_product_category'
    )";
    
    $sql3_prepared = $wpdb->prepare($sql3, $term_id);
    echo '<div class="sql"><pre>' . htmlspecialchars($sql3_prepared) . '</pre></div>';
    
    $products_direct = $wpdb->get_results($sql3_prepared);
    echo '<table>';
    echo '<tr><th>Product ID</th><th>Title</th><th>Status</th></tr>';
    if (count($products_direct) > 0) {
        foreach ($products_direct as $p) {
            echo '<tr>';
            echo '<td>' . $p->ID . '</td>';
            echo '<td>' . $p->post_title . '</td>';
            echo '<td>' . $p->post_status . '</td>';
            echo '</tr>';
        }
        echo '<tr><td colspan="3"><strong>Total: ' . count($products_direct) . '</strong></td></tr>';
    } else {
        echo '<tr><td colspan="3">❌ No products found</td></tr>';
    }
    echo '</table>';
    
    // Query 2: Products in child categories
    echo '<h2>4. Products in child categories</h2>';
    $sql4 = "SELECT p.ID, p.post_title, p.post_status, t.name as category_name 
    FROM {$posts_table} p 
    JOIN {$term_relationships_table} tr ON p.ID = tr.object_id 
    JOIN {$term_taxonomy_table} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
    JOIN {$terms_table} t ON tt.term_id = t.term_id 
    WHERE p.post_type = 'sps_product' 
    AND p.post_status = 'publish' 
    AND tt.taxonomy = 'sps_product_category' 
    AND tt.parent = %d";
    
    $sql4_prepared = $wpdb->prepare($sql4, $term_id);
    echo '<div class="sql"><pre>' . htmlspecialchars($sql4_prepared) . '</pre></div>';
    
    $products_children = $wpdb->get_results($sql4_prepared);
    echo '<table>';
    echo '<tr><th>Product ID</th><th>Title</th><th>Status</th><th>Category</th></tr>';
    if (count($products_children) > 0) {
        foreach ($products_children as $p) {
            echo '<tr>';
            echo '<td>' . $p->ID . '</td>';
            echo '<td>' . $p->post_title . '</td>';
            echo '<td>' . $p->post_status . '</td>';
            echo '<td>' . $p->category_name . '</td>';
            echo '</tr>';
        }
        echo '<tr><td colspan="4"><strong>Total: ' . count($products_children) . '</strong></td></tr>';
    } else {
        echo '<tr><td colspan="4">❌ No products found</td></tr>';
    }
    echo '</table>';
    
    // Query 3: Combined - Products from parent OR child categories
    echo '<h2>5. Combined: Products from parent OR any child categories (include_children)</h2>';
    
    // Get all term_taxonomy_ids for parent and children
    $sql5 = "SELECT p.ID, p.post_title, p.post_status, GROUP_CONCAT(t.name) as categories 
    FROM {$posts_table} p 
    JOIN {$term_relationships_table} tr ON p.ID = tr.object_id 
    JOIN {$term_taxonomy_table} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
    JOIN {$terms_table} t ON tt.term_id = t.term_id 
    WHERE p.post_type = 'sps_product' 
    AND p.post_status = 'publish' 
    AND tt.taxonomy = 'sps_product_category' 
    AND (
        tt.term_id = %d 
        OR tt.parent = %d
    )
    GROUP BY p.ID";
    
    $sql5_prepared = $wpdb->prepare($sql5, $term_id, $term_id);
    echo '<div class="sql"><pre>' . htmlspecialchars($sql5_prepared) . '</pre></div>';
    
    $products_combined = $wpdb->get_results($sql5_prepared);
    echo '<table>';
    echo '<tr><th>Product ID</th><th>Title</th><th>Status</th><th>Categories</th></tr>';
    if (count($products_combined) > 0) {
        foreach ($products_combined as $p) {
            echo '<tr>';
            echo '<td>' . $p->ID . '</td>';
            echo '<td>' . $p->post_title . '</td>';
            echo '<td>' . $p->post_status . '</td>';
            echo '<td>' . $p->categories . '</td>';
            echo '</tr>';
        }
        echo '<tr><td colspan="4"><strong>✅ Total: ' . count($products_combined) . '</strong></td></tr>';
    } else {
        echo '<tr><td colspan="4">❌ No products found</td></tr>';
    }
    echo '</table>';
    
} else {
    echo '<div class="debug-box">❌ Category not found. Try with different parameter.</div>';
}

echo '</div>';
?>
