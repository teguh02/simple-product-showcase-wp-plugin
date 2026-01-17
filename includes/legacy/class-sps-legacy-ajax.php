<?php
/**
 * SPS Legacy AJAX
 * 
 * Class untuk menangani semua fungsi AJAX legacy dari Simple Product Showcase
 * Dipindahkan dari simple-product-showcase.php untuk refactoring
 * 
 * @package Simple_Product_Showcase
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Legacy_Ajax {
    
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
        // No hooks here, methods called from Simple_Product_Showcase
    }
    
    /**
     * AJAX handler untuk mendapatkan URL gambar gallery
     */
    public function ajax_get_gallery_image() {
        if (!wp_verify_nonce($_GET['nonce'], 'sps_gallery_image_nonce')) {
            wp_die('Security check failed');
        }
        
        $image_id = intval($_GET['image_id']);
        
        if (!$image_id) {
            wp_send_json_error('Invalid image ID');
        }
        
        $image_url = wp_get_attachment_image_url($image_id, 'large');
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        
        if (!$image_url) {
            wp_send_json_error('Image not found');
        }
        
        wp_send_json_success(array(
            'image_url' => $image_url,
            'image_alt' => $image_alt
        ));
    }
    
    /**
     * AJAX handler untuk autocomplete search produk
     */
    public function ajax_search_products() {
        check_ajax_referer('sps_nonce', 'nonce');
        
        $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $sub_category = isset($_POST['sub_category']) ? sanitize_text_field($_POST['sub_category']) : '';
        
        if (empty($search_term) || strlen($search_term) < 2) {
            wp_send_json_success(array('products' => array()));
        }
        
        $args = array(
            'post_type' => 'sps_product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        
        if (!empty($category)) {
            $normalized_category = strtolower(str_replace(' ', '-', $category));
            $parent_term = get_term_by('slug', $normalized_category, 'sps_product_category');
            
            if (!$parent_term || is_wp_error($parent_term)) {
                $parent_term = get_term_by('slug', $category, 'sps_product_category');
            }
            
            if (!$parent_term || is_wp_error($parent_term)) {
                $parent_term = get_term_by('name', $category, 'sps_product_category');
            }
            
            if ($parent_term && !is_wp_error($parent_term)) {
                $filter_category = $parent_term;
                $include_children = false;
                
                if (!empty($sub_category)) {
                    $sub_term = get_term_by('slug', $sub_category, 'sps_product_category');
                    if ($sub_term && !is_wp_error($sub_term) && $sub_term->parent == $parent_term->term_id) {
                        $filter_category = $sub_term;
                    }
                } else {
                    $include_children = true;
                }
                
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'sps_product_category',
                        'field' => 'term_id',
                        'terms' => $filter_category->term_id,
                        'include_children' => $include_children
                    )
                );
            }
        }
        
        $query = new WP_Query($args);
        $products = array();
        $search_term_lower = strtolower(trim($search_term));
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product_obj = get_post($product_id);
                
                if (!$product_obj) continue;
                
                $title = strtolower($product_obj->post_title);
                
                if (strpos($title, $search_term_lower) !== false) {
                    $image = get_the_post_thumbnail_url($product_id, 'thumbnail');
                    
                    $terms = get_the_terms($product_id, 'sps_product_category');
                    $category_name = '';
                    if ($terms && !is_wp_error($terms)) {
                        $category_name = $terms[0]->name;
                    }
                    
                    $products[] = array(
                        'id' => $product_id,
                        'title' => get_the_title($product_id),
                        'image' => $image ? $image : '',
                        'category' => $category_name,
                        'url' => get_permalink($product_id)
                    );
                    
                    if (count($products) >= 10) {
                        break;
                    }
                }
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success(array('products' => $products));
    }
}

