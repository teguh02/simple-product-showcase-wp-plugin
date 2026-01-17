<?php
/**
 * SPS Legacy CPT
 * 
 * Class untuk menangani semua fungsi CPT legacy dari Simple Product Showcase
 * Dipindahkan dari simple-product-showcase.php untuk refactoring
 * 
 * @package Simple_Product_Showcase
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Legacy_CPT {
    
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
     * Fallback custom post type registration
     */
    public function register_fallback_cpt() {
        if (post_type_exists('sps_product')) {
            return;
        }
        
        $labels = array(
            'name'                  => 'Products',
            'singular_name'         => 'Product',
            'menu_name'             => 'Products',
            'name_admin_bar'        => 'Product',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Product',
            'new_item'              => 'New Product',
            'edit_item'             => 'Edit Product',
            'view_item'             => 'View Product',
            'all_items'             => 'All Products',
            'search_items'          => 'Search Products',
            'parent_item_colon'     => 'Parent Products:',
            'not_found'             => 'No products found.',
            'not_found_in_trash'    => 'No products found in Trash.',
            'featured_image'        => 'Product Image',
            'set_featured_image'    => 'Set product image',
            'remove_featured_image' => 'Remove product image',
            'use_featured_image'    => 'Use as product image',
            'archives'              => 'Product archives',
            'insert_into_item'      => 'Insert into product',
            'uploaded_to_this_item' => 'Uploaded to this product',
            'filter_items_list'     => 'Filter products list',
            'items_list_navigation' => 'Products list navigation',
            'items_list'            => 'Products list',
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'product'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-products',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true,
        );
        
        register_post_type('sps_product', $args);
        
        $taxonomy_labels = array(
            'name'              => 'Product Categories',
            'singular_name'     => 'Product Category',
            'search_items'      => 'Search Categories',
            'all_items'         => 'All Categories',
            'parent_item'       => 'Parent Category',
            'parent_item_colon' => 'Parent Category:',
            'edit_item'         => 'Edit Category',
            'update_item'       => 'Update Category',
            'add_new_item'      => 'Add New Category',
            'new_item_name'     => 'New Category Name',
            'menu_name'         => 'Categories',
        );
        
        $taxonomy_args = array(
            'hierarchical'      => true,
            'labels'            => $taxonomy_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'product-category'),
            'show_in_rest'      => true,
        );
        
        register_taxonomy('sps_product_category', array('sps_product'), $taxonomy_args);
        
        if (!get_option('sps_rewrite_rules_flushed')) {
            flush_rewrite_rules();
            update_option('sps_rewrite_rules_flushed', true);
        }
    }
    
    /**
     * Direct custom post type registration as final fallback
     */
    public function direct_cpt_registration() {
        if (post_type_exists('sps_product')) {
            return;
        }
        
        $labels = array(
            'name'                  => 'Products',
            'singular_name'         => 'Product',
            'menu_name'             => 'Products',
            'name_admin_bar'        => 'Product',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Product',
            'new_item'              => 'New Product',
            'edit_item'             => 'Edit Product',
            'view_item'             => 'View Product',
            'all_items'             => 'All Products',
            'search_items'          => 'Search Products',
            'parent_item_colon'     => 'Parent Products:',
            'not_found'             => 'No products found.',
            'not_found_in_trash'    => 'No products found in Trash.',
            'featured_image'        => 'Product Image',
            'set_featured_image'    => 'Set product image',
            'remove_featured_image' => 'Remove product image',
            'use_featured_image'    => 'Use as product image',
            'archives'              => 'Product archives',
            'insert_into_item'      => 'Insert into product',
            'uploaded_to_this_item' => 'Uploaded to this product',
            'filter_items_list'     => 'Filter products list',
            'items_list_navigation' => 'Products list navigation',
            'items_list'            => 'Products list',
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'product'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-products',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true,
        );
        
        register_post_type('sps_product', $args);
        
        $taxonomy_labels = array(
            'name'              => 'Product Categories',
            'singular_name'     => 'Product Category',
            'search_items'      => 'Search Categories',
            'all_items'         => 'All Categories',
            'parent_item'       => 'Parent Category',
            'parent_item_colon' => 'Parent Category:',
            'edit_item'         => 'Edit Category',
            'update_item'       => 'Update Category',
            'add_new_item'      => 'Add New Category',
            'new_item_name'     => 'New Category Name',
            'menu_name'         => 'Categories',
        );
        
        $taxonomy_args = array(
            'hierarchical'      => true,
            'labels'            => $taxonomy_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'product-category'),
            'show_in_rest'      => true,
        );
        
        register_taxonomy('sps_product_category', array('sps_product'), $taxonomy_args);
    }
    
    /**
     * Fallback meta boxes registration
     */
    public function register_fallback_meta_boxes() {
        global $post_type;
        if ($post_type !== 'sps_product') {
            return;
        }
        
        global $wp_meta_boxes;
        if (isset($wp_meta_boxes['sps_product']['side']['high']['sps_product_price'])) {
            return;
        }
        
        add_meta_box(
            'sps_product_price',
            __('Product Price', 'simple-product-showcase'),
            array($this, 'fallback_product_price_meta_box'),
            'sps_product',
            'side',
            'high'
        );
    }
    
    /**
     * Fallback Product Price meta box content
     */
    public function fallback_product_price_meta_box($post) {
        if (class_exists('SPS_CPT')) {
            $cpt_instance = SPS_CPT::get_instance();
            if (method_exists($cpt_instance, 'product_price_meta_box')) {
                $cpt_instance->product_price_meta_box($post);
                return;
            }
        }
        
        wp_nonce_field('sps_product_meta', 'sps_product_meta_nonce');
        
        $price_numeric = get_post_meta($post->ID, '_sps_product_price_numeric', true);
        $price_discount = get_post_meta($post->ID, '_sps_product_price_discount', true);
        $weight = get_post_meta($post->ID, '_sps_product_weight', true);
        ?>
        <p>
            <label for="sps_product_price_numeric"><strong><?php _e('Harga Normal:', 'simple-product-showcase'); ?></strong></label>
            <input type="number" id="sps_product_price_numeric" name="sps_product_price_numeric" value="<?php echo esc_attr($price_numeric); ?>" class="widefat" placeholder="0" min="0" step="1" />
            <small><?php _e('Masukkan harga normal produk (contoh: 100000). Data akan disimpan ke kolom database.', 'simple-product-showcase'); ?></small>
        </p>
        <p>
            <label for="sps_product_price_discount"><strong><?php _e('Harga Diskon Coret:', 'simple-product-showcase'); ?></strong></label>
            <input type="number" id="sps_product_price_discount" name="sps_product_price_discount" value="<?php echo esc_attr($price_discount); ?>" class="widefat" placeholder="0" min="0" step="1" />
            <small><?php _e('Jika diisi, harga normal akan dicoret dan harga diskon yang digunakan untuk kalkulasi (contoh: 75000).', 'simple-product-showcase'); ?></small>
        </p>
        <p>
            <label for="sps_product_weight"><strong><?php _e('Berat Produk (dalam gram):', 'simple-product-showcase'); ?></strong></label>
            <input type="number" id="sps_product_weight" name="sps_product_weight" value="<?php echo esc_attr($weight); ?>" class="widefat" placeholder="0" min="0" step="1" />
            <small><?php _e('Masukkan berat produk dalam gram (contoh: 500). Data akan disimpan ke kolom database.', 'simple-product-showcase'); ?></small>
        </p>
        <?php
    }
}

