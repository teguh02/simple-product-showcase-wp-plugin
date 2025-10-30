<?php
/**
 * Debug file untuk explore database struktur dan data produk
 */

// Load WordPress
require_once('/home/teguh/public_html/wp.test/wp-load.php');

echo "<h2>🔍 Database Explorer untuk Simple Product Showcase</h2>";

global $wpdb;

// ========== 1. Check page dengan shortcode ==========
echo "<h3>1️⃣ Page dengan shortcode 'sps_products_sub_category'</h3>";

$posts_with_shortcode = $wpdb->get_results("
    SELECT ID, post_type, post_name, post_title, post_content 
    FROM {$wpdb->posts} 
    WHERE post_content LIKE '%sps_products_sub_category%' 
    AND post_type IN ('page', 'post')
    LIMIT 5
");

if ($posts_with_shortcode) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Type</th><th>Name</th><th>Title</th><th>Shortcode Preview</th></tr>";
    foreach ($posts_with_shortcode as $post) {
        $shortcode_preview = preg_match('/\[sps_products_sub_category[^\]]*\]/', $post->post_content, $matches) 
            ? $matches[0] 
            : 'Not found';
        echo "<tr>";
        echo "<td>{$post->ID}</td>";
        echo "<td>{$post->post_type}</td>";
        echo "<td>{$post->post_name}</td>";
        echo "<td>{$post->post_title}</td>";
        echo "<td><code>$shortcode_preview</code></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No pages found with sps_products_sub_category shortcode</p>";
}

// ========== 2. Check kategori struktur ==========
echo "<h3>2️⃣ Struktur Kategori (Parent-Child)</h3>";

$all_terms = $wpdb->get_results("
    SELECT t.term_id, t.name, t.slug, tt.parent, tt.count
    FROM {$wpdb->terms} t
    LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
    WHERE tt.taxonomy = 'sps_product_category'
    ORDER BY tt.parent, t.name
");

if ($all_terms) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Term ID</th><th>Name</th><th>Slug</th><th>Parent</th><th>Product Count</th></tr>";
    foreach ($all_terms as $term) {
        $level = $term->parent > 0 ? "└─ " : "";
        echo "<tr>";
        echo "<td>{$term->term_id}</td>";
        echo "<td>{$level}{$term->name}</td>";
        echo "<td>{$term->slug}</td>";
        echo "<td>" . ($term->parent > 0 ? $term->parent : "ROOT") . "</td>";
        echo "<td>{$term->count}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No categories found</p>";
}

// ========== 3. Check produk assignments ==========
echo "<h3>3️⃣ Produk dan Category Assignment</h3>";

$products_with_categories = $wpdb->get_results("
    SELECT 
        p.ID,
        p.post_title,
        p.post_status,
        GROUP_CONCAT(t.name SEPARATOR ', ') as categories,
        GROUP_CONCAT(t.term_id SEPARATOR ', ') as term_ids,
        GROUP_CONCAT(tt.term_id SEPARATOR ', ') as tt_term_ids
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
    LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'sps_product_category'
    LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
    WHERE p.post_type = 'sps_product'
    AND p.post_status = 'publish'
    GROUP BY p.ID
    ORDER BY p.post_title
");

if ($products_with_categories) {
    echo "<p><strong>Total Produk:</strong> " . count($products_with_categories) . "</p>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Categories (names)</th><th>Category Term IDs</th></tr>";
    foreach ($products_with_categories as $product) {
        echo "<tr>";
        echo "<td>{$product->ID}</td>";
        echo "<td>{$product->post_title}</td>";
        echo "<td>{$product->post_status}</td>";
        echo "<td>{$product->categories}</td>";
        echo "<td>{$product->tt_term_ids}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No products found</p>";
}

// ========== 4. Test manual SQL query untuk "Tes Kategori" ==========
echo "<h3>4️⃣ Test SQL Query untuk 'Tes Kategori' (include_children)</h3>";

$test_term = get_term_by('name', 'Tes Kategori', 'sps_product_category');

if ($test_term && !is_wp_error($test_term)) {
    echo "<p><strong>Term Found:</strong> ID={$test_term->term_id}, Name={$test_term->name}, Slug={$test_term->slug}, Parent={$test_term->parent}</p>";
    
    // Get child terms
    $child_terms = get_terms(array(
        'taxonomy' => 'sps_product_category',
        'parent' => $test_term->term_id,
        'hide_empty' => false,
        'fields' => 'ids'
    ));
    
    $term_ids = array($test_term->term_id);
    if ($child_terms && !is_wp_error($child_terms)) {
        $term_ids = array_merge($term_ids, $child_terms);
    }
    
    echo "<p><strong>All Term IDs (parent + children):</strong> " . implode(', ', $term_ids) . "</p>";
    
    // Manual SQL with IN clause
    $posts_table = $wpdb->prefix . 'posts';
    $term_relationships_table = $wpdb->prefix . 'term_relationships';
    $term_taxonomy_table = $wpdb->prefix . 'term_taxonomy';
    
    $term_ids_placeholder = implode(',', array_fill(0, count($term_ids), '%d'));
    
    $sql = "SELECT DISTINCT p.ID, p.post_title, p.post_status, p.post_date, tt.term_id, t.name
    FROM {$posts_table} p
    JOIN {$term_relationships_table} tr ON p.ID = tr.object_id
    JOIN {$term_taxonomy_table} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
    LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
    WHERE p.post_type = 'sps_product'
    AND p.post_status = 'publish'
    AND tt.taxonomy = 'sps_product_category'
    AND tt.term_id IN ({$term_ids_placeholder})
    ORDER BY p.post_title ASC";
    
    $sql = $wpdb->prepare($sql, ...$term_ids);
    
    echo "<p><strong>SQL Query:</strong></p>";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
    
    $results = $wpdb->get_results($sql);
    
    echo "<p><strong>Results Count:</strong> " . count($results) . "</p>";
    
    if ($results) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Product ID</th><th>Product Title</th><th>Status</th><th>Term ID</th><th>Term Name</th></tr>";
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>{$row->ID}</td>";
            echo "<td>{$row->post_title}</td>";
            echo "<td>{$row->post_status}</td>";
            echo "<td>{$row->term_id}</td>";
            echo "<td>{$row->name}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p>❌ Term 'Tes Kategori' not found</p>";
}

// ========== 5. Debug WP_Query approach ==========
echo "<h3>5️⃣ Test WP_Query dengan tax_query</h3>";

if ($test_term && !is_wp_error($test_term)) {
    // Get child terms
    $child_terms = get_terms(array(
        'taxonomy' => 'sps_product_category',
        'parent' => $test_term->term_id,
        'hide_empty' => false
    ));
    
    $term_ids = array($test_term->term_id);
    if ($child_terms && !is_wp_error($child_terms)) {
        foreach ($child_terms as $child) {
            $term_ids[] = $child->term_id;
        }
    }
    
    echo "<p><strong>All Term IDs (parent + children):</strong> " . implode(', ', $term_ids) . "</p>";
    
    $query_args = array(
        'post_type' => 'sps_product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'sps_product_category',
                'field' => 'term_id',
                'terms' => $term_ids,
                'operator' => 'IN'
            )
        )
    );
    
    $wp_query = new WP_Query($query_args);
    
    echo "<p><strong>WP_Query Results Count:</strong> " . $wp_query->post_count . "</p>";
    
    if ($wp_query->have_posts()) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Title</th></tr>";
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            echo "<tr>";
            echo "<td>" . get_the_ID() . "</td>";
            echo "<td>" . get_the_title() . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    wp_reset_postdata();
}

echo "<hr>";
echo "<p>✅ Debug selesai. Last check: " . current_time('Y-m-d H:i:s') . "</p>";
