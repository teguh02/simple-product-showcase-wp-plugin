<?php
/**
 * Uninstall script untuk Simple Product Showcase
 * 
 * Script ini akan dijalankan saat plugin dihapus dari WordPress
 * untuk membersihkan data yang tidak diperlukan
 */

// Mencegah akses langsung
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Cek permission
if (!current_user_can('delete_plugins')) {
    exit;
}

global $wpdb;

// Hapus options plugin
delete_option('sps_whatsapp_number');
delete_option('sps_whatsapp_message');
delete_option('sps_rewrite_rules_flushed');

// Hapus semua posts dari custom post type
$wpdb->delete(
    $wpdb->posts,
    array('post_type' => 'sps_product'),
    array('%s')
);

// Hapus meta data terkait
$wpdb->delete(
    $wpdb->postmeta,
    array('meta_key' => '_sps_product_price'),
    array('%s')
);

$wpdb->delete(
    $wpdb->postmeta,
    array('meta_key' => '_sps_whatsapp_message'),
    array('%s')
);

// Hapus taxonomy terms
$terms = get_terms(array(
    'taxonomy' => 'sps_product_category',
    'hide_empty' => false,
));

if (!is_wp_error($terms) && !empty($terms)) {
    foreach ($terms as $term) {
        wp_delete_term($term->term_id, 'sps_product_category');
    }
}

// Hapus transients jika ada
delete_transient('sps_products_cache');
delete_transient('sps_categories_cache');

// Hapus user meta jika ada
$wpdb->delete(
    $wpdb->usermeta,
    array('meta_key' => 'sps_user_settings'),
    array('%s')
);

// Flush rewrite rules
flush_rewrite_rules();

// Log uninstall (opsional)
error_log('Simple Product Showcase plugin uninstalled successfully');
