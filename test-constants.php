<?php
/**
 * Test Constants
 * 
 * File ini untuk testing apakah konstanta plugin sudah terdefinisi dengan benar
 * Akses: wp.test/wp-content/plugins/simple-product-showcase/test-constants.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has admin privileges
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You must be logged in as an administrator to access this page.');
}

echo '<h1>Test Plugin Constants</h1>';

// Check if constants are defined
$constants_to_check = array(
    'SPS_PLUGIN_FILE',
    'SPS_PLUGIN_PATH', 
    'SPS_PLUGIN_URL',
    'SPS_PLUGIN_VERSION'
);

echo '<h2>Plugin Constants Status:</h2>';
foreach ($constants_to_check as $constant) {
    if (defined($constant)) {
        $value = constant($constant);
        echo '<p style="color: green;">✅ ' . $constant . ' = ' . esc_html($value) . '</p>';
    } else {
        echo '<p style="color: red;">❌ ' . $constant . ' is NOT defined</p>';
    }
}

// Check if plugin is active
if (is_plugin_active('simple-product-showcase/simple-product-showcase.php')) {
    echo '<p style="color: green;">✅ Plugin is ACTIVE</p>';
} else {
    echo '<p style="color: orange;">⚠️ Plugin is DEACTIVATED</p>';
}

// Check if classes are loaded
$classes_to_check = array(
    'Simple_Product_Showcase',
    'SPS_Duplicate',
    'SPS_Persistent'
);

echo '<h2>Class Loading Status:</h2>';
foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        echo '<p style="color: green;">✅ ' . $class . ' class is loaded</p>';
    } else {
        echo '<p style="color: red;">❌ ' . $class . ' class is NOT loaded</p>';
    }
}

echo '<h2>Test Instructions:</h2>';
echo '<ol>';
echo '<li>Try accessing <a href="' . admin_url('edit.php?post_type=sps_product') . '">Products → All Products</a></li>';
echo '<li>Check if the duplicate functionality works</li>';
echo '<li>Verify that no fatal errors occur</li>';
echo '</ol>';

echo '<p><a href="' . admin_url() . '">← Back to WordPress Admin</a></p>';
?>
