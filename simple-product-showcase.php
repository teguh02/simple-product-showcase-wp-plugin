<?php
/**
 * Plugin Name: Simple Product Showcase
 * Plugin URI: https://github.com/teguh02/simple-product-showcase-wp-plugin
 * Description: Plugin WordPress ringan untuk menampilkan produk dengan integrasi WhatsApp tanpa fitur checkout, cart, atau pembayaran.
 * Version: 1.7.0
 * Author: Teguh Rijanandi
 * Author URI: https://github.com/teguh02/simple-product-showcase-wp-plugin
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
define('SPS_PLUGIN_VERSION', '1.7.0');

/**
 * Class Simple_Product_Showcase
 * 
 * Class utama untuk menginisialisasi plugin Simple Product Showcase
 * Refactored: v1.7.0 - Method implementations moved to includes/legacy/
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
        add_shortcode('sps_random_products', array($this, 'direct_random_products_shortcode'));
        add_shortcode('sps_products_with_filters', array($this, 'direct_products_with_filters_shortcode'));
        add_shortcode('sps_products_sub_category', array($this, 'direct_products_sub_category_shortcode'));
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
        
        // Fallback: register meta boxes directly to ensure they appear
        add_action('add_meta_boxes', array($this, 'register_fallback_meta_boxes'));

        // Register AJAX actions
        add_action('wp_ajax_get_gallery_image', array($this, 'ajax_get_gallery_image'));
        add_action('wp_ajax_nopriv_get_gallery_image', array($this, 'ajax_get_gallery_image'));
        add_action('wp_ajax_sps_search_products', array($this, 'ajax_search_products'));
        add_action('wp_ajax_nopriv_sps_search_products', array($this, 'ajax_search_products'));

        // Set cookie info produk via PHP sebelum output
        add_action('template_redirect', array($this, 'set_product_info_cookie'));
    }
    
    /**
     * Load file dependencies
     */
    private function load_dependencies() {
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-init.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-cpt.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-configuration.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-shortcodes.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-frontend.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-duplicate.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-persistent.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-metabox.php';
        
        // Legacy classes (refactored from main file v1.7.0)
        require_once SPS_PLUGIN_PATH . 'includes/legacy/class-sps-legacy-shortcodes.php';
        require_once SPS_PLUGIN_PATH . 'includes/legacy/class-sps-legacy-shortcodes-extended.php';
        require_once SPS_PLUGIN_PATH . 'includes/legacy/class-sps-legacy-shortcodes-filters.php';
        require_once SPS_PLUGIN_PATH . 'includes/legacy/class-sps-legacy-admin.php';
        require_once SPS_PLUGIN_PATH . 'includes/legacy/class-sps-legacy-cpt.php';
        require_once SPS_PLUGIN_PATH . 'includes/legacy/class-sps-legacy-ajax.php';
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
        $this->init();
        flush_rewrite_rules();
        $this->set_default_options();
    }
    
    /**
     * Deaktivasi plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Cleanup custom post type saat uninstall
     */
    public function cleanup_custom_post_type() {
        global $wpdb;
        
        $wpdb->delete($wpdb->posts, array('post_type' => 'sps_product'), array('%s'));
        $wpdb->delete($wpdb->postmeta, array('meta_key' => '_sps_product_price'), array('%s'));
        $wpdb->delete($wpdb->postmeta, array('meta_key' => '_sps_whatsapp_message'), array('%s'));
        
        $terms = get_terms(array('taxonomy' => 'sps_product_category', 'hide_empty' => false));
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                wp_delete_term($term->term_id, 'sps_product_category');
            }
        }
        
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
    
    // ====================================================================
    // WRAPPER METHODS - Calls to Legacy Classes
    // ====================================================================
    
    // --- Admin Methods (SPS_Legacy_Admin) ---
    
    public function add_fallback_admin_menu() {
        return SPS_Legacy_Admin::get_instance()->add_fallback_admin_menu();
    }
    
    public function fallback_settings_page() {
        return SPS_Legacy_Admin::get_instance()->fallback_settings_page();
    }
    
    public function whatsapp_button_text_callback() {
        return SPS_Legacy_Admin::get_instance()->whatsapp_button_text_callback();
    }
    
    public function documentation_page() {
        return SPS_Legacy_Admin::get_instance()->documentation_page();
    }
    
    public function init_settings_class() {
        return SPS_Legacy_Admin::get_instance()->init_settings_class();
    }
    
    public function modify_admin_view_link($actions, $post) {
        return SPS_Legacy_Admin::get_instance()->modify_admin_view_link($actions, $post);
    }
    
    public function register_fallback_settings() {
        return SPS_Legacy_Admin::get_instance()->register_fallback_settings();
    }
    
    public function detail_page_section_callback() {
        return SPS_Legacy_Admin::get_instance()->detail_page_section_callback();
    }
    
    public function detail_page_mode_callback() {
        return SPS_Legacy_Admin::get_instance()->detail_page_mode_callback();
    }
    
    public function custom_detail_page_callback() {
        return SPS_Legacy_Admin::get_instance()->custom_detail_page_callback();
    }
    
    // --- CPT Methods (SPS_Legacy_CPT) ---
    
    public function register_fallback_cpt() {
        return SPS_Legacy_CPT::get_instance()->register_fallback_cpt();
    }
    
    public function direct_cpt_registration() {
        return SPS_Legacy_CPT::get_instance()->direct_cpt_registration();
    }
    
    public function register_fallback_meta_boxes() {
        return SPS_Legacy_CPT::get_instance()->register_fallback_meta_boxes();
    }
    
    public function fallback_product_price_meta_box($post) {
        return SPS_Legacy_CPT::get_instance()->fallback_product_price_meta_box($post);
    }
    
    // --- AJAX Methods (SPS_Legacy_Ajax) ---
    
    public function ajax_get_gallery_image() {
        return SPS_Legacy_Ajax::get_instance()->ajax_get_gallery_image();
    }
    
    public function ajax_search_products() {
        return SPS_Legacy_Ajax::get_instance()->ajax_search_products();
    }
    
    // --- Shortcode Methods (SPS_Legacy_Shortcodes) ---
    
    public function direct_products_shortcode($atts) {
        return SPS_Legacy_Shortcodes::get_instance()->direct_products_shortcode($atts);
    }
    
    public function direct_random_products_shortcode($atts) {
        return SPS_Legacy_Shortcodes::get_instance()->direct_random_products_shortcode($atts);
    }
    
    public function register_fallback_shortcode() {
        return SPS_Legacy_Shortcodes::get_instance()->register_fallback_shortcode();
    }
    
    public function fallback_products_shortcode($atts) {
        return SPS_Legacy_Shortcodes::get_instance()->fallback_products_shortcode($atts);
    }
    
    // --- Shortcode Extended Methods (SPS_Legacy_Shortcodes_Extended) ---
    
    public function direct_detail_products_shortcode($atts) {
        return SPS_Legacy_Shortcodes_Extended::get_instance()->direct_detail_products_shortcode($atts);
    }
    
    public function set_product_info_cookie() {
        return SPS_Legacy_Shortcodes_Extended::get_instance()->set_product_info_cookie();
    }
    
    // --- Shortcode Filters Methods (SPS_Legacy_Shortcodes_Filters) ---
    
    public function direct_products_with_filters_shortcode($atts) {
        return SPS_Legacy_Shortcodes_Filters::get_instance()->direct_products_with_filters_shortcode($atts);
    }
    
    public function direct_products_sub_category_shortcode($atts) {
        return SPS_Legacy_Shortcodes_Filters::get_instance()->direct_products_sub_category_shortcode($atts);
    }
    
    public function fallback_products_sub_category_shortcode($atts) {
        return SPS_Legacy_Shortcodes_Filters::get_instance()->fallback_products_sub_category_shortcode($atts);
    }
}

// Inisialisasi plugin
Simple_Product_Showcase::get_instance();
