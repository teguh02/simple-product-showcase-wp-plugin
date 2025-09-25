<?php
/**
 * Debug Meta Boxes
 * 
 * File ini untuk debugging meta boxes yang terdaftar
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/debug-meta-boxes.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin privileges
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to access this page.');
}

echo '<h1>Debug Meta Boxes</h1>';

// Check if plugin is active
if (is_plugin_active('simple-product-showcase/simple-product-showcase.php')) {
    echo '<p style="color: green;">✅ Plugin is ACTIVE</p>';
} else {
    echo '<p style="color: red;">❌ Plugin is NOT active</p>';
}

// Check if SPS_CPT class exists
if (class_exists('SPS_CPT')) {
    echo '<p style="color: green;">✅ SPS_CPT class is loaded</p>';
} else {
    echo '<p style="color: red;">❌ SPS_CPT class is NOT loaded</p>';
}

// Check if meta box hooks are registered
global $wp_filter;

echo '<h2>Meta Box Hooks:</h2>';
if (isset($wp_filter['add_meta_boxes'])) {
    echo '<p style="color: green;">✅ add_meta_boxes hook is registered</p>';
    
    // Check if our meta box is registered
    $meta_box_callbacks = $wp_filter['add_meta_boxes']->callbacks;
    $found_our_meta_box = false;
    
    foreach ($meta_box_callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function']) && 
                isset($callback['function'][1]) && 
                $callback['function'][1] === 'add_meta_boxes') {
                $found_our_meta_box = true;
                echo '<p style="color: green;">✅ SPS_CPT::add_meta_boxes is registered at priority ' . $priority . '</p>';
                break 2;
            }
        }
    }
    
    if (!$found_our_meta_box) {
        echo '<p style="color: red;">❌ SPS_CPT::add_meta_boxes is NOT registered</p>';
    }
} else {
    echo '<p style="color: red;">❌ add_meta_boxes hook is NOT registered</p>';
}

// Check if we can manually trigger the meta box registration
echo '<h2>Manual Meta Box Test:</h2>';
if (class_exists('SPS_CPT')) {
    $sps_cpt = SPS_CPT::get_instance();
    if (method_exists($sps_cpt, 'add_meta_boxes')) {
        echo '<p style="color: green;">✅ add_meta_boxes method exists</p>';
        
        // Try to manually call the method
        try {
            $sps_cpt->add_meta_boxes();
            echo '<p style="color: green;">✅ add_meta_boxes method executed successfully</p>';
        } catch (Exception $e) {
            echo '<p style="color: red;">❌ Error executing add_meta_boxes: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p style="color: red;">❌ add_meta_boxes method does NOT exist</p>';
    }
}

// Check WordPress version and editor type
echo '<h2>WordPress Info:</h2>';
echo '<p>WordPress Version: ' . get_bloginfo('version') . '</p>';
echo '<p>Block Editor: ' . (function_exists('use_block_editor_for_post_type') ? 'Available' : 'Not Available') . '</p>';

// Check if we're using block editor for our post type
if (function_exists('use_block_editor_for_post_type')) {
    $use_block_editor = use_block_editor_for_post_type('sps_product');
    echo '<p>Block Editor for sps_product: ' . ($use_block_editor ? 'YES' : 'NO') . '</p>';
}

// Check if meta boxes are disabled for this post type
$post_type_object = get_post_type_object('sps_product');
if ($post_type_object) {
    echo '<p>Supports editor: ' . (post_type_supports('sps_product', 'editor') ? 'YES' : 'NO') . '</p>';
    echo '<p>Supports custom-fields: ' . (post_type_supports('sps_product', 'custom-fields') ? 'YES' : 'NO') . '</p>';
}

echo '<h2>Test Instructions:</h2>';
echo '<ol>';
echo '<li>Go to <a href="' . admin_url('post-new.php?post_type=sps_product') . '">Add New Product</a></li>';
echo '<li>Look for meta boxes in the sidebar or below the editor</li>';
echo '<li>Check if "Product Gallery" meta box appears</li>';
echo '<li>If not, try switching to Classic Editor</li>';
echo '</ol>';

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';
?>
