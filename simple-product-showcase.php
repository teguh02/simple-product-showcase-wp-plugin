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
        
        // Inisialisasi duplicate functionality
        SPS_Duplicate::get_instance();
        
        // Fallback: tambahkan menu admin langsung jika class tidak berfungsi
        add_action('admin_menu', array($this, 'add_fallback_admin_menu'));
        
        // Fallback: daftarkan custom post type langsung jika class tidak berfungsi
        add_action('init', array($this, 'register_fallback_cpt'));
        
        // Direct registration as additional fallback
        add_action('init', array($this, 'direct_cpt_registration'), 20);
        
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
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        $whatsapp_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}');
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
                
                <?php submit_button(); ?>
            </form>
            
            <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                <h2>Plugin Information</h2>
                <p><strong>Shortcode:</strong> <code>[sps_products]</code></p>
                <p><strong>Product Pages:</strong> <code><?php echo home_url('/product/product-name/'); ?></code></p>
                <p><strong>Available Attributes:</strong> columns, category, limit, orderby, order, show_price, show_description, show_whatsapp</p>
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
    
}

// Inisialisasi plugin
Simple_Product_Showcase::get_instance();
