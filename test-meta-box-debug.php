<?php
/**
 * Comprehensive Meta Box Debug Test
 * 
 * File ini untuk debugging lengkap masalah meta box
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/test-meta-box-debug.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin privileges
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to access this page.');
}

echo '<h1>Comprehensive Meta Box Debug Test</h1>';

// 1. Check plugin status
echo '<h2>1. Plugin Status:</h2>';
if (is_plugin_active('simple-product-showcase/simple-product-showcase.php')) {
    echo '<p style="color: green;">✅ Plugin is ACTIVE</p>';
} else {
    echo '<p style="color: red;">❌ Plugin is NOT active</p>';
    exit;
}

// 2. Check class loading
echo '<h2>2. Class Loading:</h2>';
if (class_exists('SPS_CPT')) {
    echo '<p style="color: green;">✅ SPS_CPT class is loaded</p>';
} else {
    echo '<p style="color: red;">❌ SPS_CPT class is NOT loaded</p>';
    exit;
}

// 3. Check post type registration
echo '<h2>3. Post Type Registration:</h2>';
$post_type_object = get_post_type_object('sps_product');
if ($post_type_object) {
    echo '<p style="color: green;">✅ sps_product post type is registered</p>';
    echo '<p>Supports editor: ' . (post_type_supports('sps_product', 'editor') ? 'YES' : 'NO') . '</p>';
    echo '<p>Supports custom-fields: ' . (post_type_supports('sps_product', 'custom-fields') ? 'YES' : 'NO') . '</p>';
} else {
    echo '<p style="color: red;">❌ sps_product post type is NOT registered</p>';
    exit;
}

// 4. Check WordPress version and editor
echo '<h2>4. WordPress Environment:</h2>';
echo '<p>WordPress Version: ' . get_bloginfo('version') . '</p>';
echo '<p>Block Editor Available: ' . (function_exists('use_block_editor_for_post_type') ? 'YES' : 'NO') . '</p>';

if (function_exists('use_block_editor_for_post_type')) {
    $use_block_editor = use_block_editor_for_post_type('sps_product');
    echo '<p>Block Editor for sps_product: ' . ($use_block_editor ? 'YES' : 'NO') . '</p>';
}

// 5. Check hooks
echo '<h2>5. Hook Registration:</h2>';
global $wp_filter;

$hooks_to_check = array(
    'add_meta_boxes',
    'admin_enqueue_scripts',
    'save_post'
);

foreach ($hooks_to_check as $hook) {
    if (isset($wp_filter[$hook])) {
        echo '<p style="color: green;">✅ ' . $hook . ' hook is registered</p>';
    } else {
        echo '<p style="color: red;">❌ ' . $hook . ' hook is NOT registered</p>';
    }
}

// 6. Check if our specific hooks are registered
echo '<h2>6. Our Specific Hooks:</h2>';
if (isset($wp_filter['add_meta_boxes'])) {
    $found_our_hook = false;
    foreach ($wp_filter['add_meta_boxes']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function']) && 
                isset($callback['function'][0]) && 
                is_object($callback['function'][0]) && 
                get_class($callback['function'][0]) === 'SPS_CPT' &&
                $callback['function'][1] === 'add_meta_boxes') {
                $found_our_hook = true;
                echo '<p style="color: green;">✅ SPS_CPT::add_meta_boxes is registered at priority ' . $priority . '</p>';
                break 2;
            }
        }
    }
    if (!$found_our_hook) {
        echo '<p style="color: red;">❌ SPS_CPT::add_meta_boxes is NOT registered</p>';
    }
}

// 7. Test manual meta box registration
echo '<h2>7. Manual Meta Box Test:</h2>';
add_action('add_meta_boxes', function() {
    add_meta_box(
        'sps_test_meta_box',
        'Test Meta Box',
        function($post) {
            echo '<p>This is a test meta box to verify meta box functionality.</p>';
            echo '<p>Post ID: ' . $post->ID . '</p>';
            echo '<p>Post Type: ' . $post->post_type . '</p>';
        },
        'sps_product',
        'side',
        'high'
    );
});

echo '<p style="color: green;">✅ Test meta box registered</p>';

// 8. Check if we can access the edit screen
echo '<h2>8. Edit Screen Access:</h2>';
$products = get_posts(array(
    'post_type' => 'sps_product',
    'posts_per_page' => 1,
    'post_status' => 'any'
));

if ($products) {
    $product = $products[0];
    echo '<p style="color: green;">✅ Found product: ' . $product->post_title . ' (ID: ' . $product->ID . ')</p>';
    echo '<p><a href="' . admin_url('post.php?post=' . $product->ID . '&action=edit') . '" target="_blank">Edit Product</a></p>';
} else {
    echo '<p style="color: orange;">⚠️ No products found. <a href="' . admin_url('post-new.php?post_type=sps_product') . '">Create one</a></p>';
}

// 9. Check error logs
echo '<h2>9. Error Logs:</h2>';
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $recent_errors = tail($error_log, 10);
    echo '<p>Recent errors from ' . $error_log . ':</p>';
    echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;">';
    echo htmlspecialchars($recent_errors);
    echo '</pre>';
} else {
    echo '<p>No error log found or accessible</p>';
}

// 10. Test instructions
echo '<h2>10. Test Instructions:</h2>';
echo '<ol>';
echo '<li>Go to <a href="' . admin_url('post-new.php?post_type=sps_product') . '">Add New Product</a></li>';
echo '<li>Look for meta boxes in the sidebar (right side)</li>';
echo '<li>You should see: "Test Meta Box" and "Product Gallery"</li>';
echo '<li>If you see "Test Meta Box" but not "Product Gallery", there\'s an issue with our specific meta box</li>';
echo '<li>If you don\'t see any meta boxes, there\'s a deeper issue</li>';
echo '</ol>';

echo '<h2>11. Quick Fixes to Try:</h2>';
echo '<ol>';
echo '<li>Deactivate and reactivate the plugin</li>';
echo '<li>Clear any caching plugins</li>';
echo '<li>Check if you\'re using a custom admin theme</li>';
echo '<li>Try switching to Classic Editor temporarily</li>';
echo '<li>Check browser console for JavaScript errors</li>';
echo '</ol>';

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';

// Helper function to get last N lines of a file
function tail($file, $lines = 10) {
    $handle = fopen($file, "r");
    $linecounter = $lines;
    $pos = -2;
    $beginning = false;
    $text = array();
    while ($linecounter > 0) {
        $t = " ";
        while ($t != "\n") {
            if(fseek($handle, $pos, SEEK_END) == -1) {
                $beginning = true; 
                break; 
            }
            $t = fgetc($handle);
            $pos --;
        }
        $linecounter --;
        if ($beginning) {
            rewind($handle);
        }
        $text[$lines-$linecounter-1] = fgets($handle);
        if ($beginning) break;
    }
    fclose ($handle);
    return implode("", array_reverse($text));
}
?>
