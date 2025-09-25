<?php
/**
 * View Documentation Directly
 * Access via: wp.test/wp-content/plugins/simple-product-showcase/view-documentation.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<h1>📚 Simple Product Showcase - Shortcode Documentation</h1>';
echo '<p><em>Last updated: ' . date('Y-m-d H:i:s') . '</em></p>';

echo '<h2>Basic Usage</h2>';
echo '<code>[sps_products]</code>';
echo '<p>Display all products in a responsive grid layout.</p>';

echo '<h2>All Available Parameters</h2>';
echo '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
echo '<thead>';
echo '<tr style="background: #e9ecef;">';
echo '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Parameter</th>';
echo '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Description</th>';
echo '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Default</th>';
echo '<th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;">Example</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$parameters = [
    ['columns', 'Number of columns in the grid (1-6)', '3', 'columns="4"'],
    ['category', 'Filter by product category slug', '-', 'category="electronics"'],
    ['limit', 'Maximum number of products to display', '-1 (all)', 'limit="6"'],
    ['orderby', 'Sort products by: title, date, menu_order, price', 'date', 'orderby="title"'],
    ['order', 'Sort order: ASC or DESC', 'DESC', 'order="ASC"'],
    ['show_price', 'Show product price: true or false', 'true', 'show_price="false"'],
    ['show_description', 'Show product description: true or false', 'true', 'show_description="false"'],
    ['show_whatsapp', 'Show WhatsApp contact button: true or false', 'true', 'show_whatsapp="false"'],
    ['show_gallery', 'Show product gallery images: true or false', 'true', 'show_gallery="false"'],
    ['gallery_style', 'Gallery display style: grid, slider, or carousel', 'grid', 'gallery_style="slider"']
];

foreach ($parameters as $param) {
    echo '<tr>';
    echo '<td style="padding: 8px; border: 1px solid #dee2e6;"><code>' . $param[0] . '</code></td>';
    echo '<td style="padding: 8px; border: 1px solid #dee2e6;">' . $param[1] . '</td>';
    echo '<td style="padding: 8px; border: 1px solid #dee2e6;">' . $param[2] . '</td>';
    echo '<td style="padding: 8px; border: 1px solid #dee2e6;"><code>' . $param[3] . '</code></td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';

echo '<h2>Usage Examples</h2>';
echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;">';
echo '<p><strong>Basic Grid (3 columns):</strong></p>';
echo '<code>[sps_products]</code><br><br>';

echo '<p><strong>4-Column Grid with 8 Products:</strong></p>';
echo '<code>[sps_products columns="4" limit="8"]</code><br><br>';

echo '<p><strong>Electronics Category Only:</strong></p>';
echo '<code>[sps_products category="electronics" columns="2"]</code><br><br>';

echo '<p><strong>Alphabetical Order, No Price:</strong></p>';
echo '<code>[sps_products orderby="title" order="ASC" show_price="false"]</code><br><br>';

echo '<p><strong>Gallery Slider, No WhatsApp:</strong></p>';
echo '<code>[sps_products gallery_style="slider" show_whatsapp="false"]</code><br><br>';

echo '<p><strong>Minimal Display (Image + Title Only):</strong></p>';
echo '<code>[sps_products show_price="false" show_description="false" show_whatsapp="false" show_gallery="false"]</code>';
echo '</div>';

echo '<h2>Gallery Images</h2>';
echo '<p>Each product can have up to 5 additional gallery images:</p>';
echo '<ul>';
echo '<li>Access via: Products → Edit Product → Product Gallery meta box</li>';
echo '<li>Images are stored as attachment IDs in post meta</li>';
echo '<li>Displayed automatically on product pages and in shortcodes</li>';
echo '<li>Supports different display styles: grid, slider, carousel</li>';
echo '</ul>';

echo '<h2>WhatsApp Integration</h2>';
echo '<p>Automatic WhatsApp contact buttons on all products:</p>';
echo '<ul>';
echo '<li>Uses the WhatsApp number configured in settings</li>';
echo '<li>Pre-filled message with product link</li>';
echo '<li>Customizable message per product</li>';
echo '<li>Can be disabled per shortcode with show_whatsapp="false"</li>';
echo '</ul>';

echo '<h2>Frontend Display</h2>';
echo '<p>How to use the shortcode on your website:</p>';
echo '<ol>';
echo '<li>Go to any page or post editor</li>';
echo '<li>Add the shortcode: [sps_products]</li>';
echo '<li>Customize with parameters as needed</li>';
echo '<li>Preview or publish to see the product grid</li>';
echo '</ol>';

echo '<h2>Pro Tips</h2>';
echo '<ul>';
echo '<li>Use different shortcodes on different pages for variety</li>';
echo '<li>Combine with categories to create filtered product sections</li>';
echo '<li>Test different column layouts for your theme</li>';
echo '<li>Use limit parameter to create "Featured Products" sections</li>';
echo '</ul>';

echo '<hr>';
echo '<p><strong>Settings Page:</strong> <a href="' . admin_url('edit.php?post_type=sps_product&page=sps-settings') . '" target="_blank">Go to Plugin Settings</a></p>';
echo '<p><strong>Plugin Version:</strong> ' . (defined('SPS_PLUGIN_VERSION') ? SPS_PLUGIN_VERSION : 'Unknown') . '</p>';
?>
