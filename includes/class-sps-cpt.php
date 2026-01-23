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
        // REST API support removed - forcing Classic Editor instead
        add_action('admin_init', array($this, 'check_and_create_weight_column'));
        add_action('admin_init', array($this, 'check_and_create_price_column'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_product_meta'), 10, 1);
        add_filter('manage_sps_product_posts_columns', array($this, 'add_product_columns'));
        add_action('manage_sps_product_posts_custom_column', array($this, 'populate_product_columns'), 10, 2);
        
        // Gallery admin scripts are now handled by SPS_Metabox class
        
        // Disable block editor for sps_product to ensure meta boxes work
        // Multiple filters with HIGHEST priority to ensure Gutenberg is completely disabled
        add_filter('use_block_editor_for_post_type', array($this, 'disable_block_editor'), PHP_INT_MAX, 2);
        add_filter('use_block_editor_for_post', array($this, 'disable_block_editor_for_post'), PHP_INT_MAX, 2);
        add_filter('gutenberg_can_edit_post_type', array($this, 'disable_block_editor'), PHP_INT_MAX, 2);
        add_filter('gutenberg_can_edit_post', array($this, 'disable_block_editor_for_post'), PHP_INT_MAX, 2);
        
        // Additional filters to force classic editor
        add_filter('classic_editor_enabled_editors_for_post_type', array($this, 'force_classic_editor'), PHP_INT_MAX, 2);
        add_filter('classic_editor_enabled_editors_for_post', array($this, 'force_classic_editor_for_post'), PHP_INT_MAX, 2);
        
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
            'show_in_rest'       => false, // DISABLE REST API to force Classic Editor
        );
        
        register_post_type('sps_product', $args);
    }
    
    /**
     * Register meta fields for REST API support (for Gutenberg compatibility)
     */
    public function register_meta_fields_for_rest() {
        // Register meta fields untuk sps_product post type
        $meta_fields = array(
            '_sps_product_price_numeric' => array(
                'type' => 'number',
                'description' => 'Product price (numeric)',
                'single' => true,
                'sanitize_callback' => 'floatval',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                },
                'show_in_rest' => true,
            ),
            '_sps_product_price_discount' => array(
                'type' => 'number',
                'description' => 'Product discount price',
                'single' => true,
                'sanitize_callback' => 'floatval',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                },
                'show_in_rest' => true,
            ),
            '_sps_product_weight' => array(
                'type' => 'integer',
                'description' => 'Product weight in grams',
                'single' => true,
                'sanitize_callback' => 'absint',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                },
                'show_in_rest' => true,
            ),
            '_sps_product_price' => array(
                'type' => 'string',
                'description' => 'Product price (display)',
                'single' => true,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                },
                'show_in_rest' => true,
            ),
        );
        
        foreach ($meta_fields as $meta_key => $args) {
            register_post_meta('sps_product', $meta_key, $args);
        }
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
        
        // WhatsApp Settings meta box removed - settings only available in Button Configuration page
        
        // Product Gallery meta box is now handled by SPS_Metabox class
    }
    
    /**
     * Meta box untuk harga produk
     */
    public function product_price_meta_box($post) {
        wp_nonce_field('sps_product_meta', 'sps_product_meta_nonce');
        
        $price = get_post_meta($post->ID, '_sps_product_price', true);
        $price_numeric = get_post_meta($post->ID, '_sps_product_price_numeric', true);
        $price_discount = get_post_meta($post->ID, '_sps_product_price_discount', true);
        $weight = get_post_meta($post->ID, '_sps_product_weight', true);
        $short_description = get_post_meta($post->ID, '_sps_product_short_description', true);
        ?>
        <p>
            <label for="sps_product_short_description"><strong><?php _e('Deskripsi Singkat:', 'simple-product-showcase'); ?></strong></label>
            <textarea id="sps_product_short_description" name="sps_product_short_description" class="widefat" rows="4" placeholder="<?php esc_attr_e('Masukkan deskripsi singkat produk di sini...', 'simple-product-showcase'); ?>"><?php echo esc_textarea($short_description); ?></textarea>
            <small><?php _e('Masukkan deskripsi singkat produk. Deskripsi ini akan digunakan untuk tampilan ringkas produk.', 'simple-product-showcase'); ?></small>
        </p>
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
        <p style="display: none;">
            <label for="sps_product_price"><?php _e('Price (Display):', 'simple-product-showcase'); ?></label>
            <input type="text" id="sps_product_price" name="sps_product_price" value="<?php echo esc_attr($price); ?>" class="widefat" placeholder="Rp 0" />
            <small><?php _e('Enter price with currency symbol (e.g., Rp 100,000)', 'simple-product-showcase'); ?></small>
        </p>
        <?php
    }
    
    // Product Gallery meta box is now handled by SPS_Metabox class
    // WhatsApp Settings meta box removed - settings only available in Button Configuration page
    
    /**
     * Simpan meta data produk
     */
    public function save_product_meta($post_id) {
        // Cek post type
        if (get_post_type($post_id) !== 'sps_product') {
            return;
        }
        
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
        
        // Simpan harga produk (display)
        if (isset($_POST['sps_product_price'])) {
            update_post_meta($post_id, '_sps_product_price', sanitize_text_field($_POST['sps_product_price']));
        }
        
        // Simpan harga produk (numeric) - selalu simpan jika field ada di form
        if (isset($_POST['sps_product_price_numeric'])) {
            $price_numeric = floatval($_POST['sps_product_price_numeric']); // Sanitize as float to support decimals
            if ($price_numeric < 0) {
                $price_numeric = 0; // Ensure non-negative
            }
            update_post_meta($post_id, '_sps_product_price_numeric', $price_numeric);
            
            // Jika kolom price ada di wp_posts, simpan juga ke sana
            $this->save_price_to_posts_table($post_id, $price_numeric);
        }
        
        // Simpan harga diskon produk - selalu simpan jika field ada di form
        if (isset($_POST['sps_product_price_discount'])) {
            $price_discount = floatval($_POST['sps_product_price_discount']); // Sanitize as float to support decimals
            if ($price_discount < 0) {
                $price_discount = 0; // Ensure non-negative
            }
            update_post_meta($post_id, '_sps_product_price_discount', $price_discount);
        }
        
        // Simpan berat produk (weight) - selalu simpan jika field ada di form
        if (isset($_POST['sps_product_weight'])) {
            $weight = absint($_POST['sps_product_weight']); // Sanitize as positive integer
            update_post_meta($post_id, '_sps_product_weight', $weight);
            
            // Jika kolom weight ada di wp_posts, simpan juga ke sana
            $this->save_weight_to_posts_table($post_id, $weight);
        }
        
        // Simpan deskripsi singkat produk
        if (isset($_POST['sps_product_short_description'])) {
            $short_description = sanitize_textarea_field($_POST['sps_product_short_description']);
            update_post_meta($post_id, '_sps_product_short_description', $short_description);
        }
        
        // WhatsApp message settings are now handled in Button Configuration page only
        // Custom per-product WhatsApp message removed - use global settings only
        
        // Gallery images are now handled by SPS_Metabox class
    }
    
    /**
     * Save product meta from REST API (Gutenberg compatibility)
     * This is called by save_post_sps_product hook
     */
    public function save_product_meta_rest($post_id, $post, $update) {
        // Skip jika bukan update (new post)
        if (!$update) {
            return;
        }
        
        // Skip autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Ambil nilai dari post meta (yang sudah disimpan oleh REST API)
        $price_numeric = get_post_meta($post_id, '_sps_product_price_numeric', true);
        $weight = get_post_meta($post_id, '_sps_product_weight', true);
        
        // Simpan ke kolom custom di wp_posts jika ada nilai
        if (!empty($price_numeric)) {
            $this->save_price_to_posts_table($post_id, floatval($price_numeric));
        }
        
        if (!empty($weight)) {
            $this->save_weight_to_posts_table($post_id, absint($weight));
        }
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
    
    /**
     * Disable block editor for sps_product post (by post object)
     */
    public function disable_block_editor_for_post($use_block_editor, $post) {
        if (empty($post)) {
            return $use_block_editor;
        }
        
        // Handle both object and ID
        $post_type = '';
        if (is_object($post)) {
            $post_type = $post->post_type;
        } elseif (is_numeric($post)) {
            $post_type = get_post_type($post);
        }
        
        if ($post_type === 'sps_product') {
            return false;
        }
        return $use_block_editor;
    }
    
    /**
     * Force classic editor for sps_product (Classic Editor plugin compatibility)
     */
    public function force_classic_editor($editors, $post_type) {
        if ($post_type === 'sps_product') {
            return array('classic_editor' => true, 'block_editor' => false);
        }
        return $editors;
    }
    
    /**
     * Force classic editor for sps_product post (Classic Editor plugin compatibility)
     */
    public function force_classic_editor_for_post($editors, $post) {
        if (empty($post)) {
            return $editors;
        }
        
        $post_type = '';
        if (is_object($post)) {
            $post_type = $post->post_type;
        } elseif (is_numeric($post)) {
            $post_type = get_post_type($post);
        }
        
        if ($post_type === 'sps_product') {
            return array('classic_editor' => true, 'block_editor' => false);
        }
        return $editors;
    }
    
    // Gallery admin scripts are now handled by SPS_Metabox class
    
    /**
     * Check apakah kolom weight ada di tabel wp_posts, jika belum ada tambahkan
     * Dipanggil via admin_init hook untuk memastikan database sudah siap
     */
    public function check_and_create_weight_column() {
        global $wpdb;
        
        // Cek apakah kolom sudah ada (gunakan cache untuk efisiensi)
        $column_exists = get_option('sps_weight_column_exists', null);
        
        if ($column_exists === null) {
            // Belum pernah di-check, cek sekarang
            $table_name = $wpdb->posts;
            $column_name = 'weight';
            
            // Cek apakah kolom sudah ada dengan query yang lebih aman
            $column_check = $wpdb->get_results($wpdb->prepare(
                "SHOW COLUMNS FROM {$table_name} LIKE %s",
                $column_name
            ));
            
            $column_exists = !empty($column_check);
            update_option('sps_weight_column_exists', $column_exists);
        }
        
        // Jika kolom belum ada, tambahkan
        if (!$column_exists) {
            $table_name = $wpdb->posts;
            $column_name = 'weight';
            
            // Coba tambahkan kolom
            $sql = "ALTER TABLE {$table_name} ADD COLUMN `{$column_name}` INT(11) UNSIGNED DEFAULT 0 AFTER `post_excerpt`";
            
            // Execute query langsung (ALTER TABLE tidak bisa menggunakan $wpdb->prepare dengan baik)
            $result = $wpdb->query($sql);
            
            // Jika berhasil, update cache
            if ($result !== false) {
                update_option('sps_weight_column_exists', true);
            }
        }
    }
    
    /**
     * Simpan weight ke kolom wp_posts (jika kolom ada)
     */
    private function save_weight_to_posts_table($post_id, $weight) {
        global $wpdb;
        
        // Cek apakah kolom weight ada menggunakan cache
        $column_exists = get_option('sps_weight_column_exists', false);
        
        if ($column_exists) {
            // Kolom ada, update langsung ke tabel wp_posts
            $table_name = $wpdb->posts;
            $wpdb->update(
                $table_name,
                array('weight' => absint($weight)),
                array('ID' => $post_id),
                array('%d'),
                array('%d')
            );
        }
    }
    
    /**
     * Check apakah kolom price ada di tabel wp_posts, jika belum ada tambahkan
     * Dipanggil via admin_init hook untuk memastikan database sudah siap
     */
    public function check_and_create_price_column() {
        global $wpdb;
        
        // Cek apakah kolom sudah ada (gunakan cache untuk efisiensi)
        $column_exists = get_option('sps_price_column_exists', null);
        
        if ($column_exists === null) {
            // Belum pernah di-check, cek sekarang
            $table_name = $wpdb->posts;
            $column_name = 'price';
            
            // Cek apakah kolom sudah ada dengan query yang lebih aman
            $column_check = $wpdb->get_results($wpdb->prepare(
                "SHOW COLUMNS FROM {$table_name} LIKE %s",
                $column_name
            ));
            
            $column_exists = !empty($column_check);
            update_option('sps_price_column_exists', $column_exists);
        }
        
        // Jika kolom belum ada, tambahkan
        if (!$column_exists) {
            $table_name = $wpdb->posts;
            $column_name = 'price';
            
            // Coba tambahkan kolom dengan tipe DECIMAL untuk support angka desimal
            $sql = "ALTER TABLE {$table_name} ADD COLUMN `{$column_name}` DECIMAL(15,2) UNSIGNED DEFAULT 0.00 AFTER `post_excerpt`";
            
            // Execute query langsung (ALTER TABLE tidak bisa menggunakan $wpdb->prepare dengan baik)
            $result = $wpdb->query($sql);
            
            // Jika berhasil, update cache
            if ($result !== false) {
                update_option('sps_price_column_exists', true);
            }
        }
    }
    
    /**
     * Simpan price ke kolom wp_posts (jika kolom ada)
     */
    private function save_price_to_posts_table($post_id, $price) {
        global $wpdb;
        
        // Cek apakah kolom price ada menggunakan cache
        $column_exists = get_option('sps_price_column_exists', false);
        
        if ($column_exists) {
            // Kolom ada, update langsung ke tabel wp_posts
            $table_name = $wpdb->posts;
            $wpdb->update(
                $table_name,
                array('price' => floatval($price)),
                array('ID' => $post_id),
                array('%f'),
                array('%d')
            );
        }
    }
    
}
