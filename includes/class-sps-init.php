<?php
/**
 * Class SPS_Init
 * 
 * Class untuk menginisialisasi semua komponen plugin Simple Product Showcase
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Init {
    
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
        // Inisialisasi custom post type dan taxonomy
        add_action('init', array($this, 'init_cpt'));
        
        // Inisialisasi admin menu dan settings - harus di admin_menu hook
        add_action('admin_menu', array($this, 'init_admin_menu'));
        
        // Enqueue scripts dan styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Inisialisasi shortcodes
        add_action('init', array($this, 'init_shortcodes'));
        
        // Inisialisasi frontend
        add_action('init', array($this, 'init_frontend'));
    }
    
    /**
     * Inisialisasi Custom Post Type dan Taxonomy
     */
    public function init_cpt() {
        // Pastikan class sudah loaded
        if (class_exists('SPS_CPT')) {
            SPS_CPT::get_instance();
        } else {
            // Fallback: load class manual jika belum loaded
            require_once SPS_PLUGIN_PATH . 'includes/class-sps-cpt.php';
            SPS_CPT::get_instance();
        }
        
        // Double-check registration by calling static method
        if (class_exists('SPS_CPT')) {
            SPS_CPT::register();
        }
    }
    
    /**
     * Inisialisasi admin menu
     */
    public function init_admin_menu() {
        // Pastikan class sudah loaded
        if (class_exists('SPS_Settings')) {
            SPS_Settings::get_instance();
        } else {
            // Fallback: load class manual jika belum loaded
            require_once SPS_PLUGIN_PATH . 'includes/class-sps-settings.php';
            SPS_Settings::get_instance();
        }
    }
    
    /**
     * Enqueue scripts dan styles untuk frontend
     */
    public function enqueue_scripts() {
        // Hanya load di halaman yang relevan
        if (is_singular('sps_product') || is_post_type_archive('sps_product') || $this->has_sps_shortcode()) {
            wp_enqueue_style(
                'sps-style',
                SPS_PLUGIN_URL . 'assets/css/style.css',
                array(),
                SPS_PLUGIN_VERSION
            );
            
            wp_enqueue_script(
                'sps-script',
                SPS_PLUGIN_URL . 'assets/js/script.js',
                array('jquery'),
                SPS_PLUGIN_VERSION,
                true
            );
            
            // Localize script untuk AJAX
            wp_localize_script('sps-script', 'sps_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('sps_nonce')
            ));
        }
    }
    
    /**
     * Enqueue scripts dan styles untuk admin
     */
    public function admin_enqueue_scripts($hook) {
        // Hanya load di halaman admin yang relevan
        if (strpos($hook, 'sps_') !== false || $hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_style(
                'sps-admin-style',
                SPS_PLUGIN_URL . 'assets/css/admin-style.css',
                array(),
                SPS_PLUGIN_VERSION
            );
            
            wp_enqueue_script(
                'sps-admin-script',
                SPS_PLUGIN_URL . 'assets/js/admin-script.js',
                array('jquery'),
                SPS_PLUGIN_VERSION,
                true
            );
        }
    }
    
    /**
     * Inisialisasi shortcodes
     */
    public function init_shortcodes() {
        SPS_Shortcodes::get_instance();
    }
    
    /**
     * Inisialisasi frontend
     */
    public function init_frontend() {
        SPS_Frontend::get_instance();
    }
    
    /**
     * Cek apakah halaman memiliki shortcode SPS
     */
    private function has_sps_shortcode() {
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'sps_products')) {
            return true;
        }
        
        return false;
    }
}
