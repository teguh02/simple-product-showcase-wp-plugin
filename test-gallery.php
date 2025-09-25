<?php
/**
 * Test Gallery Functionality
 * 
 * File ini untuk testing fitur Product Gallery
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/test-gallery.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin privileges
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to access this page.');
}

echo '<h1>Test Product Gallery Functionality</h1>';

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

// Check products
$products = get_posts(array(
    'post_type' => 'sps_product',
    'posts_per_page' => 5,
    'post_status' => 'any'
));

echo '<h2>Products with Gallery Images:</h2>';
if ($products) {
    foreach ($products as $product) {
        echo '<h3>' . $product->post_title . ' (ID: ' . $product->ID . ')</h3>';
        
        $gallery_images = array();
        for ($i = 1; $i <= 5; $i++) {
            $image_id = get_post_meta($product->ID, '_sps_gallery_' . $i, true);
            if ($image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'medium');
                $gallery_images[] = array(
                    'id' => $image_id,
                    'url' => $image_url,
                    'position' => $i
                );
            }
        }
        
        if ($gallery_images) {
            echo '<p style="color: green;">✅ Found ' . count($gallery_images) . ' gallery images:</p>';
            echo '<ul>';
            foreach ($gallery_images as $img) {
                echo '<li>Gallery ' . $img['position'] . ': ID ' . $img['id'] . ' - <a href="' . $img['url'] . '" target="_blank">View Image</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="color: orange;">⚠️ No gallery images found</p>';
        }
        
        // Show how to retrieve images
        echo '<h4>How to retrieve images in your theme:</h4>';
        echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 4px;">';
        echo '// Get gallery image 1' . "\n";
        echo '$image_id = get_post_meta(' . $product->ID . ', \'_sps_gallery_1\', true);' . "\n";
        echo 'if ($image_id) {' . "\n";
        echo '    $image_url = wp_get_attachment_image_url($image_id, \'large\');' . "\n";
        echo '    echo \'<img src="\' . $image_url . \'" alt="Gallery Image 1" />\';' . "\n";
        echo '}' . "\n\n";
        
        echo '// Get all gallery images' . "\n";
        echo '$gallery_images = array();' . "\n";
        echo 'for ($i = 1; $i <= 5; $i++) {' . "\n";
        echo '    $image_id = get_post_meta(' . $product->ID . ', \'_sps_gallery_\' . $i, true);' . "\n";
        echo '    if ($image_id) {' . "\n";
        echo '        $gallery_images[] = $image_id;' . "\n";
        echo '    }' . "\n";
        echo '}' . "\n";
        echo '</pre>';
    }
} else {
    echo '<p style="color: orange;">⚠️ No products found</p>';
}

echo '<h2>Test Instructions:</h2>';
echo '<ol>';
echo '<li>Go to <a href="' . admin_url('post-new.php?post_type=sps_product') . '">Add New Product</a></li>';
echo '<li>Look for the "Product Gallery" meta box below the content editor</li>';
echo '<li>Click "Select Image" for any of the 5 gallery slots</li>';
echo '<li>Choose images from the WordPress Media Library</li>';
echo '<li>Save the product and verify images are saved</li>';
echo '<li>Edit the product again to see if images are loaded correctly</li>';
echo '</ol>';

echo '<h2>Meta Keys Used:</h2>';
echo '<ul>';
for ($i = 1; $i <= 5; $i++) {
    echo '<li><code>_sps_gallery_' . $i . '</code> - Stores image ID for gallery position ' . $i . '</li>';
}
echo '</ul>';

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';
?>
