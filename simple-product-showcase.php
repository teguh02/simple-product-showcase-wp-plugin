<?php
/**
 * Plugin Name: Simple Product Showcase
 * Plugin URI: https://example.com/simple-product-showcase
 * Description: Plugin WordPress ringan untuk menampilkan produk dengan integrasi WhatsApp tanpa fitur checkout, cart, atau pembayaran.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-product-showcase
 * Domain Path: /languages
 */

// Mencegah akses langsung ke file ini
if (!defined('ABSPATH')) {
    exit;
}

// Definisi konstanta plugin
define('SPS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SPS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SPS_PLUGIN_VERSION', '1.0.0');

/**
 * Class Simple_Product_Showcase
 * 
 * Class utama untuk menginisialisasi plugin Simple Product Showcase
 */
class Simple_Product_Showcase {
    
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
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Hook aktivasi dan deaktivasi plugin
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Inisialisasi plugin
     */
    public function init() {
        // Load file-file yang diperlukan
        $this->load_dependencies();
        
        // Inisialisasi class-class utama
        SPS_Init::get_instance();
        
        // Inisialisasi settings class dengan hook admin_init
        add_action('admin_init', array($this, 'init_settings_class'));
        
        // Inisialisasi shortcodes
        SPS_Shortcodes::get_instance();
        
        // Force register shortcode immediately (direct registration)
        add_shortcode('sps_products', array($this, 'direct_products_shortcode'));
        add_shortcode('sps_detail_products', array($this, 'direct_detail_products_shortcode'));
        
        // Inisialisasi duplicate functionality
        SPS_Duplicate::get_instance();
        
        // Fallback: tambahkan menu admin langsung jika class tidak berfungsi
        add_action('admin_menu', array($this, 'add_fallback_admin_menu'));
        
        // Register settings directly
        add_action('admin_init', array($this, 'register_fallback_settings'));
        
        // Fallback: daftarkan custom post type langsung jika class tidak berfungsi
        add_action('init', array($this, 'register_fallback_cpt'));
        
        // Direct registration as additional fallback
        add_action('init', array($this, 'direct_cpt_registration'), 20);
        
        // Fallback: daftarkan shortcode langsung
        add_action('init', array($this, 'register_fallback_shortcode'));
        
        // Initialize persistent class to keep data visible
        SPS_Persistent::get_instance();
        
        // Initialize meta box class
        SPS_Metabox::get_instance();
    }
    
    /**
     * Load file dependencies
     */
    private function load_dependencies() {
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-init.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-cpt.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-settings.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-shortcodes.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-frontend.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-duplicate.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-persistent.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-metabox.php';
    }
    
    /**
     * Load text domain untuk internationalization
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'simple-product-showcase',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    /**
     * Aktivasi plugin
     */
    public function activate() {
        // Flush rewrite rules untuk custom post type
        $this->init();
        flush_rewrite_rules();
        
        // Set default options
        $this->set_default_options();
    }
    
    /**
     * Deaktivasi plugin
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // DO NOT delete data - keep products and categories in database
        // Data will be preserved and accessible when plugin is reactivated
    }
    
    /**
     * Cleanup custom post type saat uninstall (bukan deactivation)
     * Method ini hanya dipanggil saat plugin benar-benar dihapus
     */
    public function cleanup_custom_post_type() {
        global $wpdb;
        
        // Hapus posts dari custom post type
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
        
        // Hapus options
        delete_option('sps_whatsapp_number');
        delete_option('sps_whatsapp_message');
        delete_option('sps_rewrite_rules_flushed');
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        $default_options = array(
            'sps_whatsapp_number' => '',
            'sps_whatsapp_message' => 'Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}'
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Fallback admin menu jika class SPS_Settings tidak berfungsi
     */
    public function add_fallback_admin_menu() {
        // Cek apakah Settings submenu sudah ada (dari class SPS_Settings)
        global $submenu;
        $settings_exists = false;
        
        if (isset($submenu['edit.php?post_type=sps_product'])) {
            foreach ($submenu['edit.php?post_type=sps_product'] as $item) {
                if (isset($item[2]) && $item[2] === 'sps-settings') {
                    $settings_exists = true;
                    break;
                }
            }
        }
        
        // Jika Settings submenu belum ada, tambahkan fallback
        if (!$settings_exists) {
            add_submenu_page(
                'edit.php?post_type=sps_product',
                'Settings',
                'Settings',
                'manage_options',
                'sps-settings',
                array($this, 'fallback_settings_page')
            );
        }
    }
    
    /**
     * Fallback settings page
     */
    public function fallback_settings_page() {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'sps_settings-options')) {
            if (isset($_POST['sps_whatsapp_number'])) {
                update_option('sps_whatsapp_number', sanitize_text_field($_POST['sps_whatsapp_number']));
            }
            if (isset($_POST['sps_whatsapp_message'])) {
                update_option('sps_whatsapp_message', sanitize_textarea_field($_POST['sps_whatsapp_message']));
            }
            if (isset($_POST['sps_detail_page_mode'])) {
                update_option('sps_detail_page_mode', sanitize_text_field($_POST['sps_detail_page_mode']));
            }
            if (isset($_POST['sps_custom_detail_page'])) {
                update_option('sps_custom_detail_page', intval($_POST['sps_custom_detail_page']));
            }
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        $whatsapp_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}');
        $detail_mode = get_option('sps_detail_page_mode', 'default');
        $custom_page = get_option('sps_custom_detail_page', 0);
        ?>
        <div class="wrap">
            <h1>Simple Product Showcase Settings</h1>
            
            <?php if (empty($whatsapp_number)) : ?>
                <div class="notice notice-warning">
                    <p><strong>Warning:</strong> WhatsApp number is not configured. Please set your WhatsApp number below to enable contact buttons.</p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('sps_settings-options'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">WhatsApp Number</th>
                        <td>
                            <input type="text" name="sps_whatsapp_number" value="<?php echo esc_attr($whatsapp_number); ?>" class="regular-text" placeholder="+6281234567890" required />
                            <p class="description">Enter your WhatsApp number with country code (e.g., +6281234567890)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Default WhatsApp Message</th>
                        <td>
                            <textarea name="sps_whatsapp_message" rows="4" cols="50" class="large-text" placeholder="Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}"><?php echo esc_textarea($whatsapp_message); ?></textarea>
                            <p class="description">Default message template for WhatsApp contact. Use {product_link} placeholder for product URL.</p>
                        </td>
                    </tr>
                </table>
                
                <hr>
                <h2><?php _e('Detail Page Settings', 'simple-product-showcase'); ?></h2>
                <p><?php _e('Configure how product detail pages are displayed when users click the "Detail" button.', 'simple-product-showcase'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Detail Page Mode', 'simple-product-showcase'); ?></th>
                        <td>
                            <select name="sps_detail_page_mode" id="sps_detail_page_mode">
                                <option value="default" <?php selected($detail_mode, 'default'); ?>>
                                    <?php _e('Default Single Product Page', 'simple-product-showcase'); ?>
                                </option>
                                <option value="custom" <?php selected($detail_mode, 'custom'); ?>>
                                    <?php _e('Custom Page with Shortcodes', 'simple-product-showcase'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('Choose how product detail pages are displayed:', 'simple-product-showcase'); ?><br>
                                <strong><?php _e('Default:', 'simple-product-showcase'); ?></strong> <?php _e('Uses the built-in single product template with all information.', 'simple-product-showcase'); ?><br>
                                <strong><?php _e('Custom:', 'simple-product-showcase'); ?></strong> <?php _e('Redirects to a custom page where you can use shortcodes.', 'simple-product-showcase'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Custom Detail Page', 'simple-product-showcase'); ?></th>
                        <td>
                            <?php
                            $pages = get_pages(array(
                                'post_status' => 'publish',
                                'sort_column' => 'post_title',
                                'sort_order' => 'ASC'
                            ));
                            ?>
                            <select name="sps_custom_detail_page" id="sps_custom_detail_page">
                                <option value="0"><?php _e('-- Select a page --', 'simple-product-showcase'); ?></option>
                                <?php foreach ($pages as $page) : ?>
                                    <option value="<?php echo $page->ID; ?>" <?php selected($custom_page, $page->ID); ?>>
                                        <?php echo esc_html($page->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                <?php _e('Select the page where you want to display product details using shortcodes. This page should contain shortcodes like [sps_detail_products section="title"].', 'simple-product-showcase'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                <h2>Plugin Information</h2>
                
                <h3><?php _e('Product Grid Shortcode', 'simple-product-showcase'); ?></h3>
                <p><strong>Shortcode:</strong> <code>[sps_products]</code></p>
                <p><strong>Available Attributes:</strong> <code>columns, category, limit, orderby, order, show_price, show_description, show_whatsapp</code></p>
                
                <h3><?php _e('Product Detail Shortcode', 'simple-product-showcase'); ?> <span style="color: #0073aa; font-size: 12px;">(NEW)</span></h3>
                <p><strong>Shortcode:</strong> <code>[sps_detail_products section="title"]</code></p>
                <p><strong>Available Sections:</strong> <code>title, image, description, gallery, whatsapp, price</code></p>
                <p><strong>Gallery Styles:</strong> <code>grid, slider, carousel</code></p>
                
                <h3><?php _e('Product Pages', 'simple-product-showcase'); ?></h3>
                <p><strong>Default:</strong> <code><?php echo home_url('/product/product-name/'); ?></code></p>
                <p><strong>Custom:</strong> <code><?php echo home_url('/custom-page/?product_id=123'); ?></code></p>
                
                <div style="margin-top: 15px; padding: 15px; background: #e7f3ff; border-left: 4px solid #0073aa;">
                    <h4><?php _e('Example Usage:', 'simple-product-showcase'); ?></h4>
                    <p><code>[sps_detail_products section="title"]</code> - <?php _e('Display product title', 'simple-product-showcase'); ?></p>
                    <p><code>[sps_detail_products section="gallery" style="slider"]</code> - <?php _e('Display gallery as slider', 'simple-product-showcase'); ?></p>
                    <p><code>[sps_detail_products section="whatsapp"]</code> - <?php _e('Display WhatsApp button', 'simple-product-showcase'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Fallback custom post type registration
     */
    public function register_fallback_cpt() {
        // Cek apakah custom post type sudah terdaftar
        if (post_type_exists('sps_product')) {
            return;
        }
        
        // Daftarkan custom post type
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
        
        // Daftarkan taxonomy
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
        
        // Flush rewrite rules untuk memastikan URL berfungsi
        if (!get_option('sps_rewrite_rules_flushed')) {
            flush_rewrite_rules();
            update_option('sps_rewrite_rules_flushed', true);
        }
    }
    
    /**
     * Direct custom post type registration as final fallback
     */
    public function direct_cpt_registration() {
        // Only register if not already registered
        if (post_type_exists('sps_product')) {
            return;
        }
        
        // Register custom post type
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
        
        // Register taxonomy
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
     * Direct shortcode handler (called immediately)
     */
    public function direct_products_shortcode($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'columns' => '3',
            'category' => '',
            'limit' => '-1',
            'orderby' => 'date',
            'order' => 'DESC',
            'show_price' => 'true',
            'show_description' => 'true',
            'show_whatsapp' => 'true'
        ), $atts, 'sps_products');
        
        // Validate columns
        $columns = intval($atts['columns']);
        if ($columns < 1 || $columns > 6) {
            $columns = 3;
        }
        
        // Validate limit
        $limit = intval($atts['limit']);
        if ($limit < -1) {
            $limit = -1;
        }
        
        // Query arguments
        $args = array(
            'post_type' => 'sps_product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        );
        
        // Add category filter if specified
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'sps_product_category',
                    'field' => 'slug',
                    'terms' => $atts['category']
                )
            );
        }
        
        // Execute query
        $products_query = new WP_Query($args);
        
        if (!$products_query->have_posts()) {
            return '<p class="sps-no-products">No products found.</p>';
        }
        
        // Start output
        ob_start();
        ?>
        <style>
        .sps-products-grid {
            display: grid;
            grid-template-columns: repeat(<?php echo $columns; ?>, 1fr);
            gap: 30px;
            margin: 20px 0;
            justify-items: center;
        }
        .sps-product-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 300px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 15px;
        }
        .sps-product-image {
            margin-bottom: 15px;
            text-align: center;
        }
        .sps-product-image img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .sps-product-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            gap: 15px;
        }
        .sps-product-title {
            margin: 0;
            flex: 1;
        }
        .sps-product-title-text {
            color: #333;
            font-weight: 600;
            font-size: 16px;
            margin: 0;
            line-height: 1.3;
        }
        .sps-detail-button {
            background: linear-gradient(to bottom, #FFEB3B, #FFD700);
            color: #333;
            padding: 10px 24px;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 6px rgba(255, 215, 0, 0.4);
            min-width: 80px;
            text-align: center;
        }
        .sps-detail-button:hover {
            background: linear-gradient(to bottom, #FFF176, #FFEB3B);
            color: #333;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 215, 0, 0.5);
        }
        .sps-detail-button:active {
            transform: translateY(0);
        }
        @media (max-width: 768px) {
            .sps-products-grid {
                grid-template-columns: repeat(<?php echo min($columns, 2); ?>, 1fr);
                gap: 25px;
            }
        }
        @media (max-width: 480px) {
            .sps-products-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .sps-product-item {
                max-width: 100%;
                padding: 12px;
            }
            .sps-product-info {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
            .sps-product-title {
                text-align: center;
            }
        }
        </style>
        <div class="sps-products-grid">
            <?php while ($products_query->have_posts()) : $products_query->the_post(); ?>
                <div class="sps-product-item">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="sps-product-image">
                            <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="sps-product-info">
                        <div class="sps-product-title">
                            <p class="sps-product-title-text"><?php the_title(); ?></p>
                        </div>
                                <?php 
                                $detail_url = SPS_Settings::get_product_detail_url(get_the_ID());
                                ?>
                                <a href="<?php echo esc_url($detail_url); ?>" class="sps-detail-button">Detail</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php
        // Reset post data
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Direct detail products shortcode handler (fallback)
     */
    public function direct_detail_products_shortcode($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'section' => 'title',
            'style' => 'grid'
        ), $atts, 'sps_detail_products');
        
        // Get current product
        $product = $this->get_current_product_fallback();
        
        if (!$product) {
            return '<p class="sps-no-product">No product found.</p>';
        }
        
        // Switch berdasarkan section
        switch ($atts['section']) {
            case 'title':
                return '<h1 class="sps-product-detail-title">' . esc_html($product->post_title) . '</h1>';
                
            case 'image':
                if (has_post_thumbnail($product->ID)) {
                    return '<div class="sps-product-detail-image">' . get_the_post_thumbnail($product->ID, 'large', array('class' => 'sps-main-image')) . '</div>';
                }
                return '<div class="sps-product-detail-image"><p>No image available.</p></div>';
                
            case 'description':
                $content = apply_filters('the_content', $product->post_content);
                return '<div class="sps-product-detail-description">' . $content . '</div>';
                
            case 'gallery':
                return $this->render_gallery_fallback($product, $atts['style']);
                
            case 'whatsapp':
                return $this->render_whatsapp_fallback($product);
                
            case 'price':
                $price = get_post_meta($product->ID, '_sps_product_price', true);
                if ($price) {
                    return '<div class="sps-product-detail-price">' . esc_html($price) . '</div>';
                }
                return '<div class="sps-product-detail-price"><p>Price not available.</p></div>';
                
            default:
                return '<p class="sps-invalid-section">Invalid section: ' . esc_html($atts['section']) . '</p>';
        }
    }
    
    /**
     * Get current product fallback
     */
    private function get_current_product_fallback() {
        global $post;
        
        // If we're on a single product page
        if (is_singular('sps_product') && $post) {
            return $post;
        }
        
        // Try to get from product_id parameter (for custom pages)
        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        if ($product_id) {
            $product = get_post($product_id);
            if ($product && $product->post_type === 'sps_product') {
                return $product;
            }
        }
        
        // Try to get from URL parameters
        $product_slug = get_query_var('product');
        if ($product_slug) {
            $product = get_page_by_path($product_slug, OBJECT, 'sps_product');
            if ($product) {
                return $product;
            }
        }
        
        // Try to get from post ID in URL
        $post_id = get_query_var('p');
        if ($post_id) {
            $product = get_post($post_id);
            if ($product && $product->post_type === 'sps_product') {
                return $product;
            }
        }
        
        // Try to get from postname in URL
        $postname = get_query_var('name');
        if ($postname) {
            $product = get_page_by_path($postname, OBJECT, 'sps_product');
            if ($product) {
                return $product;
            }
        }
        
        return false;
    }
    
    /**
     * Render gallery fallback
     */
    private function render_gallery_fallback($product, $style = 'grid') {
        $gallery_images = array();
        
        // Get gallery images from meta
        for ($i = 1; $i <= 5; $i++) {
            $image_id = get_post_meta($product->ID, '_sps_gallery_' . $i, true);
            if ($image_id) {
                $gallery_images[] = $image_id;
            }
        }
        
        if (empty($gallery_images)) {
            return '<div class="sps-product-gallery-empty"><p>No gallery images available.</p></div>';
        }
        
        ob_start();
        ?>
        <style>
        .sps-product-gallery {
            margin: 20px 0;
        }
        .sps-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .sps-gallery-carousel {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            padding: 10px 0;
            scroll-behavior: smooth;
        }
        .sps-gallery-slider {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }
        .sps-gallery-slide {
            display: none;
            text-align: center;
        }
        .sps-gallery-slide.active {
            display: block;
        }
        .sps-gallery-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .sps-gallery-controls {
            text-align: center;
            margin: 15px 0;
        }
        .sps-gallery-prev,
        .sps-gallery-next {
            background: #0073aa;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
        }
        </style>
        <div class="sps-product-gallery sps-gallery-<?php echo esc_attr($style); ?>">
            <?php foreach ($gallery_images as $image_id) : ?>
                <?php if ($style === 'slider') : ?>
                    <div class="sps-gallery-slide">
                        <?php echo wp_get_attachment_image($image_id, 'large', false, array('class' => 'sps-gallery-image')); ?>
                    </div>
                <?php else : ?>
                    <div class="sps-gallery-item">
                        <?php echo wp_get_attachment_image($image_id, 'medium', false, array('class' => 'sps-gallery-image')); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <?php if ($style === 'slider') : ?>
        <div class="sps-gallery-controls">
            <button class="sps-gallery-prev" onclick="spsGalleryPrev()">‹</button>
            <button class="sps-gallery-next" onclick="spsGalleryNext()">›</button>
        </div>
        <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.sps-gallery-slide');
        const totalSlides = slides.length;
        
        function spsGalleryNext() {
            slides[currentSlide].style.display = 'none';
            currentSlide = (currentSlide + 1) % totalSlides;
            slides[currentSlide].style.display = 'block';
        }
        
        function spsGalleryPrev() {
            slides[currentSlide].style.display = 'none';
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            slides[currentSlide].style.display = 'block';
        }
        
        // Initialize slider
        document.addEventListener('DOMContentLoaded', function() {
            slides.forEach((slide, index) => {
                slide.style.display = index === 0 ? 'block' : 'none';
            });
        });
        </script>
        <?php endif; ?>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render WhatsApp fallback
     */
    private function render_whatsapp_fallback($product) {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            return '<p class="sps-whatsapp-error">WhatsApp number not configured.</p>';
        }
        
        // Get custom message for this product or use global message
        $custom_message = get_post_meta($product->ID, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
        
        $message = !empty($custom_message) ? $custom_message : $global_message;
        
        // Replace placeholders
        $message = str_replace('{product_link}', get_permalink($product->ID), $message);
        $message = str_replace('{product_name}', $product->post_title, $message);
        
        // URL encode the message
        $encoded_message = urlencode($message);
        
        // Generate WhatsApp URL
        $whatsapp_url = "https://wa.me/{$whatsapp_number}?text={$encoded_message}";
        
        return sprintf(
            '<div class="sps-product-whatsapp">
                <a href="%s" target="_blank" rel="noopener" class="sps-whatsapp-detail-button">
                    <span class="sps-whatsapp-icon">📱</span>
                    Contact via WhatsApp
                </a>
            </div>',
            esc_url($whatsapp_url)
        );
    }
    
    /**
     * Initialize settings class
     */
    public function init_settings_class() {
        SPS_Settings::get_instance();
    }
    
    /**
     * Register fallback settings
     */
    public function register_fallback_settings() {
        // Register settings
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
            array($this, 'detail_page_section_callback'),
            'sps-settings'
        );
        
        // Add settings fields
        add_settings_field(
            'sps_detail_page_mode',
            __('Detail Page Mode', 'simple-product-showcase'),
            array($this, 'detail_page_mode_callback'),
            'sps-settings',
            'sps_detail_page_section'
        );
        
        add_settings_field(
            'sps_custom_detail_page',
            __('Custom Detail Page', 'simple-product-showcase'),
            array($this, 'custom_detail_page_callback'),
            'sps-settings',
            'sps_detail_page_section'
        );
    }
    
    /**
     * Detail page section callback
     */
    public function detail_page_section_callback() {
        echo '<p>' . __('Configure how product detail pages are displayed when users click the "Detail" button.', 'simple-product-showcase') . '</p>';
    }
    
    /**
     * Detail page mode field callback
     */
    public function detail_page_mode_callback() {
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
    }
    
    /**
     * Custom detail page field callback
     */
    public function custom_detail_page_callback() {
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
    }
    
    /**
     * Register fallback shortcode
     */
    public function register_fallback_shortcode() {
        add_shortcode('sps_products', array($this, 'fallback_products_shortcode'));
    }
    
    /**
     * Fallback shortcode handler
     */
    public function fallback_products_shortcode($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'columns' => '3',
            'category' => '',
            'limit' => '-1',
            'orderby' => 'date',
            'order' => 'DESC',
            'show_price' => 'true',
            'show_description' => 'true',
            'show_whatsapp' => 'true'
        ), $atts, 'sps_products');
        
        // Validate columns
        $columns = intval($atts['columns']);
        if ($columns < 1 || $columns > 6) {
            $columns = 3;
        }
        
        // Validate limit
        $limit = intval($atts['limit']);
        if ($limit < -1) {
            $limit = -1;
        }
        
        // Query arguments
        $args = array(
            'post_type' => 'sps_product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        );
        
        // Add category filter if specified
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'sps_product_category',
                    'field' => 'slug',
                    'terms' => $atts['category']
                )
            );
        }
        
        // Execute query
        $products_query = new WP_Query($args);
        
        if (!$products_query->have_posts()) {
            return '<p class="sps-no-products">' . __('No products found.', 'simple-product-showcase') . '</p>';
        }
        
        // Start output
        ob_start();
        ?>
        <div class="sps-products-grid sps-columns-<?php echo esc_attr($columns); ?>">
            <?php while ($products_query->have_posts()) : $products_query->the_post(); ?>
                <div class="sps-product-item">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="sps-product-image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="sps-product-content">
                        <h3 class="sps-product-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        
                        <?php if ($atts['show_price'] === 'true') : ?>
                            <?php $price = get_post_meta(get_the_ID(), '_sps_product_price', true); ?>
                            <?php if ($price) : ?>
                                <div class="sps-product-price"><?php echo esc_html($price); ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_description'] === 'true') : ?>
                            <div class="sps-product-description">
                                <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_whatsapp'] === 'true') : ?>
                            <div class="sps-product-actions">
                                <?php echo $this->get_fallback_whatsapp_button(get_the_ID(), get_the_title(), get_permalink()); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php
        // Reset post data
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Generate fallback WhatsApp button HTML
     */
    private function get_fallback_whatsapp_button($product_id, $product_title, $product_url) {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            return '<p class="sps-whatsapp-error">' . __('WhatsApp number not configured.', 'simple-product-showcase') . '</p>';
        }
        
        // Get custom message for this product or use global message
        $custom_message = get_post_meta($product_id, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
        
        $message = !empty($custom_message) ? $custom_message : $global_message;
        
        // Replace placeholders
        $message = str_replace('{product_link}', $product_url, $message);
        $message = str_replace('{product_name}', $product_title, $message);
        
        // URL encode the message
        $encoded_message = urlencode($message);
        
        // Generate WhatsApp URL
        $whatsapp_url = "https://wa.me/{$whatsapp_number}?text={$encoded_message}";
        
        return sprintf(
            '<a href="%s" target="_blank" rel="noopener" class="sps-whatsapp-button">
                <span class="sps-whatsapp-icon">📱</span>
                %s
            </a>',
            esc_url($whatsapp_url),
            __('Contact via WhatsApp', 'simple-product-showcase')
        );
    }
    
}

// Inisialisasi plugin
Simple_Product_Showcase::get_instance();
