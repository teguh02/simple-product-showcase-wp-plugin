<?php
/**
 * Class SPS_Persistent
 * 
 * Class untuk menjaga data produk tetap terlihat meskipun plugin dinonaktifkan
 * File ini akan selalu dimuat untuk memastikan custom post type tetap terdaftar
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Persistent {
    
    /**
     * Instance tunggal dari class ini
     */
    private static $instance = null;
    
    /**
     * Mendapatkan instance tunggal dari class ini
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Inisialisasi hooks WordPress
     */
    private function init_hooks() {
        // Register post type and taxonomy on init
        add_action('init', array($this, 'register_persistent_cpt'), 1);
        
        // Add admin notice when plugin is deactivated
        add_action('admin_notices', array($this, 'show_deactivation_notice'));
    }
    
    /**
     * Register persistent CPT to keep data visible even when plugin is deactivated
     */
    public function register_persistent_cpt() {
        // Check if there are any sps_product posts in the database
        $product_count = wp_count_posts('sps_product');
        $has_products = $product_count && ($product_count->publish > 0 || $product_count->draft > 0 || $product_count->private > 0);
        
        // Only register if there are products in the database
        if ($has_products) {
            // Register sps_product post type
            register_post_type('sps_product', array(
                'labels' => array(
                    'name' => __('Products', 'simple-product-showcase'),
                    'singular_name' => __('Product', 'simple-product-showcase'),
                    'add_new' => __('Add New Product', 'simple-product-showcase'),
                    'add_new_item' => __('Add New Product', 'simple-product-showcase'),
                    'edit_item' => __('Edit Product', 'simple-product-showcase'),
                    'new_item' => __('New Product', 'simple-product-showcase'),
                    'view_item' => __('View Product', 'simple-product-showcase'),
                    'search_items' => __('Search Products', 'simple-product-showcase'),
                    'not_found' => __('No products found', 'simple-product-showcase'),
                    'not_found_in_trash' => __('No products found in trash', 'simple-product-showcase'),
                    'menu_name' => __('Products', 'simple-product-showcase'),
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'products'),
                'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
                'show_in_menu' => true,
                'show_in_admin_bar' => true,
                'show_in_nav_menus' => true,
                'can_export' => true,
                'publicly_queryable' => true,
                'query_var' => true,
                'capability_type' => 'post',
                'hierarchical' => false,
                'menu_position' => 20,
                'menu_icon' => 'dashicons-products',
            ));
            
            // Register sps_product_category taxonomy
            register_taxonomy('sps_product_category', 'sps_product', array(
                'labels' => array(
                    'name' => __('Product Categories', 'simple-product-showcase'),
                    'singular_name' => __('Product Category', 'simple-product-showcase'),
                    'search_items' => __('Search Categories', 'simple-product-showcase'),
                    'all_items' => __('All Categories', 'simple-product-showcase'),
                    'parent_item' => __('Parent Category', 'simple-product-showcase'),
                    'parent_item_colon' => __('Parent Category:', 'simple-product-showcase'),
                    'edit_item' => __('Edit Category', 'simple-product-showcase'),
                    'update_item' => __('Update Category', 'simple-product-showcase'),
                    'add_new_item' => __('Add New Category', 'simple-product-showcase'),
                    'new_item_name' => __('New Category Name', 'simple-product-showcase'),
                    'menu_name' => __('Categories', 'simple-product-showcase'),
                ),
                'hierarchical' => true,
                'public' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'show_tagcloud' => true,
                'rewrite' => array('slug' => 'product-category'),
            ));
        }
    }
    
    /**
     * Show admin notice when plugin is deactivated
     */
    public function show_deactivation_notice() {
        if (!is_plugin_active('simple-product-showcase/simple-product-showcase.php')) {
            $product_count = wp_count_posts('sps_product');
            $has_products = $product_count && ($product_count->publish > 0 || $product_count->draft > 0 || $product_count->private > 0);
            
            if ($has_products) {
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p><strong>Simple Product Showcase:</strong> Plugin is deactivated, but your products are still accessible. ';
                echo '<a href="' . admin_url('plugins.php') . '">Reactivate the plugin</a> to restore full functionality.</p>';
                echo '</div>';
            }
        }
    }
}

// Initialize the persistent class
SPS_Persistent::get_instance();
