<?php
/**
 * Class SPS_Duplicate
 * 
 * Class untuk menambahkan fitur duplicate ke semua post types di WordPress admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Duplicate {
    
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
        // Add duplicate action to all post types
        add_filter('post_row_actions', array($this, 'add_duplicate_action'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_duplicate_action'), 10, 2);
        
        // Handle duplicate actions
        add_action('admin_action_duplicate_post', array($this, 'duplicate_post'));
        add_action('admin_action_bulk_duplicate_posts', array($this, 'bulk_duplicate_posts'));
        
        // Add bulk duplicate action to all post types
        add_filter('bulk_actions-edit-post', array($this, 'add_bulk_duplicate_action'));
        add_filter('bulk_actions-edit-page', array($this, 'add_bulk_duplicate_action'));
        add_filter('bulk_actions-edit-sps_product', array($this, 'add_bulk_duplicate_action'));
        
        // Admin notices
        add_action('admin_notices', array($this, 'duplicate_admin_notice'));
        
        // Enqueue admin styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }
    
    /**
     * Add duplicate action to post row actions
     */
    public function add_duplicate_action($actions, $post) {
        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            return $actions;
        }
        
        // Skip for certain post types that shouldn't be duplicated
        $excluded_types = array('attachment', 'revision', 'nav_menu_item');
        if (in_array($post->post_type, $excluded_types)) {
            return $actions;
        }
        
        $duplicate_url = wp_nonce_url(
            admin_url('admin.php?action=duplicate_post&post=' . $post->ID),
            'duplicate_post_' . $post->ID,
            'duplicate_nonce'
        );
        
        $actions['duplicate'] = sprintf(
            '<a href="%s" title="%s" rel="permalink">%s</a>',
            $duplicate_url,
            __('Duplicate this item', 'simple-product-showcase'),
            __('Duplicate', 'simple-product-showcase')
        );
        
        return $actions;
    }
    
    /**
     * Add bulk duplicate action to bulk actions dropdown
     */
    public function add_bulk_duplicate_action($bulk_actions) {
        $bulk_actions['duplicate'] = __('Duplicate', 'simple-product-showcase');
        return $bulk_actions;
    }
    
    /**
     * Handle duplicate post action
     */
    public function duplicate_post() {
        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to duplicate items.', 'simple-product-showcase'));
        }
        
        // Get the original post ID
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        
        if (!$post_id) {
            wp_die(__('No item selected for duplication.', 'simple-product-showcase'));
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_GET['duplicate_nonce'], 'duplicate_post_' . $post_id)) {
            wp_die(__('Security check failed.', 'simple-product-showcase'));
        }
        
        // Get the original post
        $original_post = get_post($post_id);
        
        if (!$original_post) {
            wp_die(__('Invalid item selected.', 'simple-product-showcase'));
        }
        
        // Create duplicate post data
        $duplicate_post = array(
            'post_title'     => $original_post->post_title . ' (Copy)',
            'post_content'   => $original_post->post_content,
            'post_excerpt'   => $original_post->post_excerpt,
            'post_status'    => 'draft', // Set as draft initially
            'post_type'      => $original_post->post_type,
            'post_author'    => get_current_user_id(),
            'post_parent'    => $original_post->post_parent,
            'menu_order'     => $original_post->menu_order,
            'comment_status' => $original_post->comment_status,
            'ping_status'    => $original_post->ping_status,
        );
        
        // Insert the duplicate post
        $duplicate_id = wp_insert_post($duplicate_post);
        
        if (is_wp_error($duplicate_id)) {
            wp_die(__('Failed to duplicate item.', 'simple-product-showcase'));
        }
        
        // Copy meta data
        $this->duplicate_post_meta($post_id, $duplicate_id);
        
        // Copy taxonomy terms
        $this->duplicate_post_taxonomies($post_id, $duplicate_id);
        
        // Copy featured image
        $this->duplicate_featured_image($post_id, $duplicate_id);
        
        // Redirect to edit page of the duplicate
        $redirect_url = add_query_arg(
            array(
                'post' => $duplicate_id,
                'action' => 'edit',
                'duplicated' => '1'
            ),
            admin_url('post.php')
        );
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Handle bulk duplicate posts action
     */
    public function bulk_duplicate_posts() {
        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to duplicate items.', 'simple-product-showcase'));
        }
        
        // Get the selected post IDs
        $post_ids = isset($_REQUEST['post']) ? array_map('intval', $_REQUEST['post']) : array();
        
        if (empty($post_ids)) {
            wp_die(__('No items selected for duplication.', 'simple-product-showcase'));
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-posts')) {
            wp_die(__('Security check failed.', 'simple-product-showcase'));
        }
        
        $duplicated_count = 0;
        
        foreach ($post_ids as $post_id) {
            // Get the original post
            $original_post = get_post($post_id);
            
            if (!$original_post) {
                continue;
            }
            
            // Skip certain post types
            $excluded_types = array('attachment', 'revision', 'nav_menu_item');
            if (in_array($original_post->post_type, $excluded_types)) {
                continue;
            }
            
            // Create duplicate post data
            $duplicate_post = array(
                'post_title'     => $original_post->post_title . ' (Copy)',
                'post_content'   => $original_post->post_content,
                'post_excerpt'   => $original_post->post_excerpt,
                'post_status'    => 'draft', // Set as draft initially
                'post_type'      => $original_post->post_type,
                'post_author'    => get_current_user_id(),
                'post_parent'    => $original_post->post_parent,
                'menu_order'     => $original_post->menu_order,
                'comment_status' => $original_post->comment_status,
                'ping_status'    => $original_post->ping_status,
            );
            
            // Insert the duplicate post
            $duplicate_id = wp_insert_post($duplicate_post);
            
            if (!is_wp_error($duplicate_id)) {
                // Copy meta data
                $this->duplicate_post_meta($post_id, $duplicate_id);
                
                // Copy taxonomy terms
                $this->duplicate_post_taxonomies($post_id, $duplicate_id);
                
                // Copy featured image
                $this->duplicate_featured_image($post_id, $duplicate_id);
                
                $duplicated_count++;
            }
        }
        
        // Get the post type for redirect
        $post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'post';
        
        // Redirect back to list with success message
        $redirect_url = add_query_arg(
            array(
                'post_type' => $post_type,
                'bulk_duplicated' => '1',
                'duplicated_count' => $duplicated_count
            ),
            admin_url('edit.php')
        );
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Duplicate post meta data
     */
    private function duplicate_post_meta($original_id, $duplicate_id) {
        $meta_data = get_post_meta($original_id);
        
        foreach ($meta_data as $meta_key => $meta_values) {
            // Skip certain meta keys that shouldn't be duplicated
            $excluded_keys = array(
                '_edit_lock',
                '_edit_last',
                '_wp_old_slug',
                '_wp_old_date'
            );
            
            if (in_array($meta_key, $excluded_keys)) {
                continue;
            }
            
            foreach ($meta_values as $meta_value) {
                add_post_meta($duplicate_id, $meta_key, maybe_unserialize($meta_value));
            }
        }
    }
    
    /**
     * Duplicate post taxonomies
     */
    private function duplicate_post_taxonomies($original_id, $duplicate_id) {
        $taxonomies = get_object_taxonomies(get_post_type($original_id));
        
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($original_id, $taxonomy, array('fields' => 'slugs'));
            if (!is_wp_error($terms) && !empty($terms)) {
                wp_set_post_terms($duplicate_id, $terms, $taxonomy);
            }
        }
    }
    
    /**
     * Duplicate featured image
     */
    private function duplicate_featured_image($original_id, $duplicate_id) {
        $thumbnail_id = get_post_thumbnail_id($original_id);
        if ($thumbnail_id) {
            set_post_thumbnail($duplicate_id, $thumbnail_id);
        }
    }
    
    /**
     * Show admin notice after duplication
     */
    public function duplicate_admin_notice() {
        if (isset($_GET['duplicated']) && $_GET['duplicated'] == '1') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('Item duplicated successfully!', 'simple-product-showcase') . '</p>';
            echo '</div>';
        }
        
        if (isset($_GET['bulk_duplicated']) && $_GET['bulk_duplicated'] == '1') {
            $count = isset($_GET['duplicated_count']) ? intval($_GET['duplicated_count']) : 0;
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . sprintf(__('%d items duplicated successfully!', 'simple-product-showcase'), $count) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Enqueue admin styles for duplicate functionality
     */
    public function enqueue_admin_styles($hook) {
        // Only load on edit pages
        if (strpos($hook, 'edit.php') !== false) {
            wp_enqueue_style(
                'sps-duplicate-style',
                SPS_PLUGIN_URL . 'assets/css/duplicate-style.css',
                array(),
                SPS_PLUGIN_VERSION
            );
        }
    }
}
