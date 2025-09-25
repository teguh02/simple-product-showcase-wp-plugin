<?php
/**
 * Class SPS_CPT
 * 
 * Class untuk mengelola Custom Post Type dan Taxonomy untuk produk
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_CPT {
    
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
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomy'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_product_meta'));
        add_filter('manage_sps_product_posts_columns', array($this, 'add_product_columns'));
        add_action('manage_sps_product_posts_custom_column', array($this, 'populate_product_columns'), 10, 2);
        
        // Gallery admin scripts are now handled by SPS_Metabox class
        
        // Disable block editor for sps_product to ensure meta boxes work
        add_filter('use_block_editor_for_post_type', array($this, 'disable_block_editor'), 10, 2);
        
        // Duplicate functionality is now handled by SPS_Duplicate class
    }
    
    /**
     * Static method to register post type (for direct calling)
     */
    public static function register() {
        $instance = self::get_instance();
        $instance->register_post_type();
        $instance->register_taxonomy();
    }
    
    /**
     * Register Custom Post Type untuk produk
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Products', 'Post type general name', 'simple-product-showcase'),
            'singular_name'         => _x('Product', 'Post type singular name', 'simple-product-showcase'),
            'menu_name'             => _x('Products', 'Admin Menu text', 'simple-product-showcase'),
            'name_admin_bar'        => _x('Product', 'Add New on Toolbar', 'simple-product-showcase'),
            'add_new'               => __('Add New', 'simple-product-showcase'),
            'add_new_item'          => __('Add New Product', 'simple-product-showcase'),
            'new_item'              => __('New Product', 'simple-product-showcase'),
            'edit_item'             => __('Edit Product', 'simple-product-showcase'),
            'view_item'             => __('View Product', 'simple-product-showcase'),
            'all_items'             => __('All Products', 'simple-product-showcase'),
            'search_items'          => __('Search Products', 'simple-product-showcase'),
            'parent_item_colon'     => __('Parent Products:', 'simple-product-showcase'),
            'not_found'             => __('No products found.', 'simple-product-showcase'),
            'not_found_in_trash'    => __('No products found in Trash.', 'simple-product-showcase'),
            'featured_image'        => _x('Product Image', 'Overrides the "Featured Image" phrase', 'simple-product-showcase'),
            'set_featured_image'    => _x('Set product image', 'Overrides the "Set featured image" phrase', 'simple-product-showcase'),
            'remove_featured_image' => _x('Remove product image', 'Overrides the "Remove featured image" phrase', 'simple-product-showcase'),
            'use_featured_image'    => _x('Use as product image', 'Overrides the "Use as featured image" phrase', 'simple-product-showcase'),
            'archives'              => _x('Product archives', 'The post type archive label', 'simple-product-showcase'),
            'insert_into_item'      => _x('Insert into product', 'Overrides the "Insert into post" phrase', 'simple-product-showcase'),
            'uploaded_to_this_item' => _x('Uploaded to this product', 'Overrides the "Uploaded to this post" phrase', 'simple-product-showcase'),
            'filter_items_list'     => _x('Filter products list', 'Screen reader text for the filter links', 'simple-product-showcase'),
            'items_list_navigation' => _x('Products list navigation', 'Screen reader text for the pagination', 'simple-product-showcase'),
            'items_list'            => _x('Products list', 'Screen reader text for the items list', 'simple-product-showcase'),
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
    }
    
    /**
     * Register Taxonomy untuk kategori produk
     */
    public function register_taxonomy() {
        $labels = array(
            'name'              => _x('Product Categories', 'taxonomy general name', 'simple-product-showcase'),
            'singular_name'     => _x('Product Category', 'taxonomy singular name', 'simple-product-showcase'),
            'search_items'      => __('Search Categories', 'simple-product-showcase'),
            'all_items'         => __('All Categories', 'simple-product-showcase'),
            'parent_item'       => __('Parent Category', 'simple-product-showcase'),
            'parent_item_colon' => __('Parent Category:', 'simple-product-showcase'),
            'edit_item'         => __('Edit Category', 'simple-product-showcase'),
            'update_item'       => __('Update Category', 'simple-product-showcase'),
            'add_new_item'      => __('Add New Category', 'simple-product-showcase'),
            'new_item_name'     => __('New Category Name', 'simple-product-showcase'),
            'menu_name'         => __('Categories', 'simple-product-showcase'),
        );
        
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'product-category'),
            'show_in_rest'      => true,
        );
        
        register_taxonomy('sps_product_category', array('sps_product'), $args);
    }
    
    /**
     * Tambahkan meta boxes untuk produk
     */
    public function add_meta_boxes() {
        add_meta_box(
            'sps_product_price',
            __('Product Price', 'simple-product-showcase'),
            array($this, 'product_price_meta_box'),
            'sps_product',
            'side',
            'high'
        );
        
        add_meta_box(
            'sps_product_whatsapp',
            __('WhatsApp Settings', 'simple-product-showcase'),
            array($this, 'product_whatsapp_meta_box'),
            'sps_product',
            'side',
            'default'
        );
        
        // Product Gallery meta box is now handled by SPS_Metabox class
    }
    
    /**
     * Meta box untuk harga produk
     */
    public function product_price_meta_box($post) {
        wp_nonce_field('sps_product_meta', 'sps_product_meta_nonce');
        
        $price = get_post_meta($post->ID, '_sps_product_price', true);
        ?>
        <p>
            <label for="sps_product_price"><?php _e('Price:', 'simple-product-showcase'); ?></label>
            <input type="text" id="sps_product_price" name="sps_product_price" value="<?php echo esc_attr($price); ?>" class="widefat" placeholder="Rp 0" />
            <small><?php _e('Enter price with currency symbol (e.g., Rp 100,000)', 'simple-product-showcase'); ?></small>
        </p>
        <?php
    }
    
    /**
     * Meta box untuk pengaturan WhatsApp
     */
    public function product_whatsapp_meta_box($post) {
        $custom_message = get_post_meta($post->ID, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}');
        ?>
        <p>
            <label for="sps_whatsapp_message"><?php _e('Custom WhatsApp Message:', 'simple-product-showcase'); ?></label>
            <textarea id="sps_whatsapp_message" name="sps_whatsapp_message" class="widefat" rows="3" placeholder="<?php echo esc_attr($global_message); ?>"><?php echo esc_textarea($custom_message); ?></textarea>
            <small><?php _e('Leave empty to use global message. Use {product_link} placeholder for product URL.', 'simple-product-showcase'); ?></small>
        </p>
        <?php
    }
    
    // Product Gallery meta box is now handled by SPS_Metabox class
    
    /**
     * Simpan meta data produk
     */
    public function save_product_meta($post_id) {
        // Cek nonce
        if (!isset($_POST['sps_product_meta_nonce']) || !wp_verify_nonce($_POST['sps_product_meta_nonce'], 'sps_product_meta')) {
            return;
        }
        
        // Cek autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Cek permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Simpan harga produk
        if (isset($_POST['sps_product_price'])) {
            update_post_meta($post_id, '_sps_product_price', sanitize_text_field($_POST['sps_product_price']));
        }
        
        // Simpan pesan WhatsApp custom
        if (isset($_POST['sps_whatsapp_message'])) {
            update_post_meta($post_id, '_sps_whatsapp_message', sanitize_textarea_field($_POST['sps_whatsapp_message']));
        }
        
        // Gallery images are now handled by SPS_Metabox class
    }
    
    /**
     * Tambahkan kolom custom di admin list produk
     */
    public function add_product_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['product_price'] = __('Price', 'simple-product-showcase');
                $new_columns['product_category'] = __('Category', 'simple-product-showcase');
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Populate kolom custom di admin list produk
     */
    public function populate_product_columns($column, $post_id) {
        switch ($column) {
            case 'product_price':
                $price = get_post_meta($post_id, '_sps_product_price', true);
                echo $price ? esc_html($price) : '—';
                break;
                
            case 'product_category':
                $terms = get_the_terms($post_id, 'sps_product_category');
                if ($terms && !is_wp_error($terms)) {
                    $term_names = array();
                    foreach ($terms as $term) {
                        $term_names[] = $term->name;
                    }
                    echo implode(', ', $term_names);
                } else {
                    echo '—';
                }
                break;
        }
    }
    
    /**
     * Disable block editor for sps_product post type
     */
    public function disable_block_editor($use_block_editor, $post_type) {
        if ($post_type === 'sps_product') {
            return false;
        }
        return $use_block_editor;
    }
    
    // Gallery admin scripts are now handled by SPS_Metabox class
    
}
