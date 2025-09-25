<?php
/**
 * Test New Gallery Meta Box
 * 
 * File ini untuk testing meta box Product Gallery yang baru
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/test-new-gallery.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin privileges
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to access this page.');
}

echo '<h1>Test New Gallery Meta Box</h1>';

// Check plugin status
if (is_plugin_active('simple-product-showcase/simple-product-showcase.php')) {
    echo '<p style="color: green;">✅ Plugin is ACTIVE</p>';
} else {
    echo '<p style="color: red;">❌ Plugin is NOT active</p>';
    exit;
}

// Check if SPS_Metabox class exists
if (class_exists('SPS_Metabox')) {
    echo '<p style="color: green;">✅ SPS_Metabox class is loaded</p>';
} else {
    echo '<p style="color: red;">❌ SPS_Metabox class is NOT loaded</p>';
    exit;
}

// Check if SPS_CPT class exists
if (class_exists('SPS_CPT')) {
    echo '<p style="color: green;">✅ SPS_CPT class is loaded</p>';
} else {
    echo '<p style="color: red;">❌ SPS_CPT class is NOT loaded</p>';
    exit;
}

// Check post type
$post_type_object = get_post_type_object('sps_product');
if ($post_type_object) {
    echo '<p style="color: green;">✅ sps_product post type exists</p>';
} else {
    echo '<p style="color: red;">❌ sps_product post type does NOT exist</p>';
    exit;
}

// Check if block editor is disabled
if (function_exists('use_block_editor_for_post_type')) {
    $use_block_editor = use_block_editor_for_post_type('sps_product');
    echo '<p>Block Editor for sps_product: ' . ($use_block_editor ? 'YES (This might cause issues)' : 'NO (Good!)') . '</p>';
}

// Get products
$products = get_posts(array(
    'post_type' => 'sps_product',
    'posts_per_page' => 5,
    'post_status' => 'any'
));

echo '<h2>Products Found:</h2>';
if ($products) {
    echo '<ul>';
    foreach ($products as $product) {
        echo '<li><a href="' . admin_url('post.php?post=' . $product->ID . '&action=edit') . '" target="_blank">' . $product->post_title . ' (ID: ' . $product->ID . ')</a></li>';
        
        // Check for existing gallery images
        $gallery_images = array();
        for ($i = 1; $i <= 5; $i++) {
            $image_id = get_post_meta($product->ID, '_sps_gallery_' . $i, true);
            if ($image_id) {
                $gallery_images[] = $i;
            }
        }
        
        if ($gallery_images) {
            echo '<ul><li>Gallery images: ' . implode(', ', $gallery_images) . '</li></ul>';
        }
    }
    echo '</ul>';
} else {
    echo '<p style="color: orange;">⚠️ No products found</p>';
}

echo '<h2>Test Instructions:</h2>';
echo '<ol>';
echo '<li>Click on any product link above to edit it</li>';
echo '<li>Look for the <strong>"Product Gallery"</strong> meta box below the content editor</li>';
echo '<li>You should see 5 image upload slots with "Select Image" buttons</li>';
echo '<li>Test uploading images and saving the product</li>';
echo '<li>Verify that images are saved and loaded correctly</li>';
echo '</ol>';

echo '<h2>Expected Meta Box Features:</h2>';
echo '<ul>';
echo '<li>✅ 5 image upload slots</li>';
echo '<li>✅ WordPress Media Uploader integration</li>';
echo '<li>✅ Image previews</li>';
echo '<li>✅ Select/Change/Remove buttons</li>';
echo '<li>✅ Proper saving to post meta</li>';
echo '<li>✅ Loading saved images on edit</li>';
echo '</ul>';

echo '<h2>Meta Keys Used:</h2>';
echo '<ul>';
for ($i = 1; $i <= 5; $i++) {
    echo '<li><code>_sps_gallery_' . $i . '</code> - Stores image ID for gallery position ' . $i . '</li>';
}
echo '</ul>';

echo '<h2>Frontend Usage Example:</h2>';
echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 4px;">';
echo '// Get gallery images in your theme' . "\n";
echo '$gallery_images = array();' . "\n";
echo 'for ($i = 1; $i <= 5; $i++) {' . "\n";
echo '    $image_id = get_post_meta($post_id, \'_sps_gallery_\' . $i, true);' . "\n";
echo '    if ($image_id) {' . "\n";
echo '        $gallery_images[] = $image_id;' . "\n";
echo '    }' . "\n";
echo '}' . "\n\n";
echo '// Display images' . "\n";
echo 'foreach ($gallery_images as $image_id) {' . "\n";
echo '    $image_url = wp_get_attachment_image_url($image_id, \'large\');' . "\n";
echo '    echo \'<img src="\' . $image_url . \'" alt="Gallery Image" />\';' . "\n";
echo '}' . "\n";
echo '</pre>';

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';
?>
