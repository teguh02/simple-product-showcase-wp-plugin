<?php
/**
 * Force Settings Registration
 * Access via: wp.test/wp-content/plugins/simple-product-showcase/force-settings.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<h1>Force Settings Registration</h1>';

// Check if we're in admin context
if (!is_admin()) {
    echo '<p>❌ Not in admin context. Redirecting...</p>';
    echo '<script>window.location.href = "' . admin_url('edit.php?post_type=sps_product&page=sps-settings') . '";</script>';
    exit;
}

echo '<p>✅ In admin context</p>';

// Force register settings
register_setting('sps_settings_group', 'sps_detail_page_mode', array(
    'type' => 'string',
    'default' => 'default'
));

register_setting('sps_settings_group', 'sps_custom_detail_page', array(
    'type' => 'integer',
    'default' => 0
));

// Add settings section
add_settings_section(
    'sps_detail_page_section',
    __('Detail Page Settings', 'simple-product-showcase'),
    function() {
        echo '<p>' . __('Configure how product detail pages are displayed when users click the "Detail" button.', 'simple-product-showcase') . '</p>';
    },
    'sps-settings'
);

// Add settings fields
add_settings_field(
    'sps_detail_page_mode',
    __('Detail Page Mode', 'simple-product-showcase'),
    function() {
        $value = get_option('sps_detail_page_mode', 'default');
        ?>
        <select name="sps_detail_page_mode" id="sps_detail_page_mode">
            <option value="default" <?php selected($value, 'default'); ?>>
                <?php _e('Default Single Product Page', 'simple-product-showcase'); ?>
            </option>
            <option value="custom" <?php selected($value, 'custom'); ?>>
                <?php _e('Custom Page with Shortcodes', 'simple-product-showcase'); ?>
            </option>
        </select>
        <p class="description">
            <?php _e('Choose how product detail pages are displayed:', 'simple-product-showcase'); ?><br>
            <strong><?php _e('Default:', 'simple-product-showcase'); ?></strong> <?php _e('Uses the built-in single product template with all information.', 'simple-product-showcase'); ?><br>
            <strong><?php _e('Custom:', 'simple-product-showcase'); ?></strong> <?php _e('Redirects to a custom page where you can use shortcodes.', 'simple-product-showcase'); ?>
        </p>
        <?php
    },
    'sps-settings',
    'sps_detail_page_section'
);

add_settings_field(
    'sps_custom_detail_page',
    __('Custom Detail Page', 'simple-product-showcase'),
    function() {
        $value = get_option('sps_custom_detail_page', 0);
        $pages = get_pages(array(
            'post_status' => 'publish',
            'sort_column' => 'post_title',
            'sort_order' => 'ASC'
        ));
        ?>
        <select name="sps_custom_detail_page" id="sps_custom_detail_page">
            <option value="0"><?php _e('-- Select a page --', 'simple-product-showcase'); ?></option>
            <?php foreach ($pages as $page) : ?>
                <option value="<?php echo $page->ID; ?>" <?php selected($value, $page->ID); ?>>
                    <?php echo esc_html($page->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e('Select the page where you want to display product details using shortcodes. This page should contain shortcodes like [sps_detail_products section="title"].', 'simple-product-showcase'); ?>
        </p>
        <?php
    },
    'sps-settings',
    'sps_detail_page_section'
);

echo '<p>✅ Settings force registered</p>';

// Check if registered
global $wp_registered_settings;
if (isset($wp_registered_settings['sps_detail_page_mode'])) {
    echo '<p>✅ sps_detail_page_mode is now registered</p>';
} else {
    echo '<p>❌ sps_detail_page_mode still not registered</p>';
}

if (isset($wp_registered_settings['sps_custom_detail_page'])) {
    echo '<p>✅ sps_custom_detail_page is now registered</p>';
} else {
    echo '<p>❌ sps_custom_detail_page still not registered</p>';
}

echo '<p><a href="' . admin_url('edit.php?post_type=sps_product&page=sps-settings') . '" target="_blank">Go to Settings Page</a></p>';
?>
