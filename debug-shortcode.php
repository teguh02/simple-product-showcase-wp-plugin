<?php
/**
 * Debug file untuk test sps_products_sub_category shortcode
 */

// Load WordPress
require_once('/home/teguh/public_html/wp.test/wp-load.php');

// Get category parameter
$category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'Tes Kategori';
$sub_category = isset($_GET['sub_category']) ? sanitize_text_field($_GET['sub_category']) : '';

echo "<h2>Debug sps_products_sub_category</h2>";
echo "<p><strong>Category:</strong> " . $category . "</p>";
echo "<p><strong>Sub Category:</strong> " . $sub_category . "</p>";

// Get parent term
$normalized_category = strtolower(str_replace(' ', '-', $category));
$parent_term = get_term_by('slug', $normalized_category, 'sps_product_category');

if (!$parent_term || is_wp_error($parent_term)) {
    $parent_term = get_term_by('slug', $category, 'sps_product_category');
}

if (!$parent_term || is_wp_error($parent_term)) {
    $parent_term = get_term_by('name', $category, 'sps_product_category');
}

if (!$parent_term || is_wp_error($parent_term)) {
    $parent_term = get_term_by('name', trim($category), 'sps_product_category');
}

if ($parent_term && !is_wp_error($parent_term)) {
    echo "<p><strong>Parent Term Found:</strong></p>";
    echo "<pre>" . print_r($parent_term, true) . "</pre>";
    
    // Get sub categories
    $sub_categories = get_terms(array(
        'taxonomy' => 'sps_product_category',
        'hide_empty' => true,
        'parent' => $parent_term->term_id,
        'orderby' => 'name',
        'order' => 'ASC'
    ));
    
    echo "<p><strong>Sub Categories:</strong></p>";
    echo "<pre>" . print_r($sub_categories, true) . "</pre>";
    
    // Determine which category to use for filtering
    $filter_category = $category;
    $include_children = false;
    
    if (!empty($sub_category)) {
        $sub_term = get_term_by('slug', $sub_category, 'sps_product_category');
        
        if ($sub_term && !is_wp_error($sub_term) && $sub_term->parent == $parent_term->term_id) {
            $filter_category = $sub_category;
            echo "<p><strong>Using sub category:</strong> " . $sub_category . "</p>";
        }
    } else {
        $include_children = true;
        echo "<p><strong>No sub category selected, include_children = true</strong></p>";
    }
    
    // Get category term for query
    $normalized_filter = strtolower(str_replace(' ', '-', $filter_category));
    $category_term = get_term_by('slug', $normalized_filter, 'sps_product_category');
    
    if (!$category_term || is_wp_error($category_term)) {
        $category_term = get_term_by('slug', $filter_category, 'sps_product_category');
    }
    
    if (!$category_term || is_wp_error($category_term)) {
        $category_term = get_term_by('name', $filter_category, 'sps_product_category');
    }
    
    if (!$category_term || is_wp_error($category_term)) {
        $category_term = get_term_by('name', trim($filter_category), 'sps_product_category');
    }
    
    if ($category_term && !is_wp_error($category_term)) {
        echo "<p><strong>Category Term for Query:</strong></p>";
        echo "<pre>" . print_r($category_term, true) . "</pre>";
        
        // Test direct SQL query
        global $wpdb;
        
        $posts_table = $wpdb->prefix . 'posts';
        $term_relationships_table = $wpdb->prefix . 'term_relationships';
        $term_taxonomy_table = $wpdb->prefix . 'term_taxonomy';
        
        $sql = "SELECT DISTINCT p.ID, p.post_title, p.post_status, p.post_date
        FROM {$posts_table} p
        JOIN {$term_relationships_table} tr ON p.ID = tr.object_id
        JOIN {$term_taxonomy_table} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        WHERE p.post_type = 'sps_product'
        AND p.post_status = 'publish'
        AND tt.taxonomy = 'sps_product_category'
        AND ";
        
        if ($include_children) {
            $sql .= "(tt.term_id = %d OR tt.parent = %d)";
            $sql = $wpdb->prepare($sql, $category_term->term_id, $category_term->term_id);
        } else {
            $sql .= "tt.term_id = %d";
            $sql = $wpdb->prepare($sql, $category_term->term_id);
        }
        
        $sql .= " ORDER BY p.post_title ASC";
        
        echo "<p><strong>SQL Query:</strong></p>";
        echo "<pre>" . htmlspecialchars($sql) . "</pre>";
        
        $results = $wpdb->get_results($sql);
        
        echo "<p><strong>Results Count:</strong> " . count($results) . "</p>";
        echo "<p><strong>Results:</strong></p>";
        echo "<pre>" . print_r($results, true) . "</pre>";
        
    } else {
        echo "<p><strong>ERROR: Category term not found!</strong></p>";
    }
} else {
    echo "<p><strong>ERROR: Parent term not found!</strong></p>";
}
