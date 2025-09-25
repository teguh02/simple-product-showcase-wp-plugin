<?php
/**
 * Class SPS_Metabox
 * 
 * Class untuk menangani meta box Product Gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Metabox {
    
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
        add_action('add_meta_boxes', array($this, 'register_meta_box'));
        add_action('save_post', array($this, 'save_gallery_images'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Register meta box
     */
    public function register_meta_box() {
        add_meta_box(
            'sps_product_gallery',
            __('Product Gallery', 'simple-product-showcase'),
            array($this, 'render_meta_box'),
            'sps_product',
            'normal',
            'default'
        );
    }
    
    /**
     * Render meta box content
     */
    public function render_meta_box($post) {
        // Add nonce field
        wp_nonce_field('sps_save_gallery', 'sps_gallery_nonce');
        
        echo '<div class="sps-gallery-metabox">';
        echo '<p>' . __('Add up to 5 additional images to showcase your product. These images will be displayed in a gallery on the frontend.', 'simple-product-showcase') . '</p>';
        
        for ($i = 1; $i <= 5; $i++) {
            $image_id = get_post_meta($post->ID, '_sps_gallery_' . $i, true);
            $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
            
            echo '<div class="sps-gallery-item" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">';
            echo '<label style="display: block; margin-bottom: 8px; font-weight: bold;">' . sprintf(__('Gallery Image %d', 'simple-product-showcase'), $i) . '</label>';
            
            // Image preview
            echo '<div class="sps-image-preview" style="margin-bottom: 10px;">';
            if ($image_url) {
                echo '<img id="sps_gallery_preview_' . $i . '" src="' . esc_url($image_url) . '" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; display: block;" />';
            } else {
                echo '<div id="sps_gallery_preview_' . $i . '" style="width: 200px; height: 150px; border: 2px dashed #ddd; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #666; background: #f9f9f9;">';
                echo __('No image selected', 'simple-product-showcase');
                echo '</div>';
            }
            echo '</div>';
            
            // Hidden input for image ID
            echo '<input type="hidden" id="sps_gallery_' . $i . '" name="sps_gallery_' . $i . '" value="' . esc_attr($image_id) . '">';
            
            // Buttons
            echo '<div class="sps-gallery-controls">';
            echo '<button type="button" class="button sps-upload" data-target="sps_gallery_' . $i . '" data-preview="sps_gallery_preview_' . $i . '">';
            echo $image_id ? __('Change Image', 'simple-product-showcase') : __('Select Image', 'simple-product-showcase');
            echo '</button>';
            
            if ($image_id) {
                echo '<button type="button" class="button sps-remove" data-target="sps_gallery_' . $i . '" data-preview="sps_gallery_preview_' . $i . '" style="margin-left: 10px; color: #a00;">';
                echo __('Remove Image', 'simple-product-showcase');
                echo '</button>';
            }
            echo '</div>';
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Save gallery images
     */
    public function save_gallery_images($post_id) {
        // Check nonce
        if (!isset($_POST['sps_gallery_nonce']) || !wp_verify_nonce($_POST['sps_gallery_nonce'], 'sps_save_gallery')) {
            return;
        }
        
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save gallery images
        for ($i = 1; $i <= 5; $i++) {
            $meta_key = '_sps_gallery_' . $i;
            
            if (isset($_POST['sps_gallery_' . $i])) {
                $image_id = intval($_POST['sps_gallery_' . $i]);
                
                if ($image_id > 0) {
                    update_post_meta($post_id, $meta_key, $image_id);
                } else {
                    delete_post_meta($post_id, $meta_key);
                }
            }
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        // Only load on product edit screens
        if (($hook == 'post.php' || $hook == 'post-new.php') && $post_type == 'sps_product') {
            // Enqueue WordPress media uploader
            wp_enqueue_media();
            
            // Enqueue custom CSS
            wp_enqueue_style(
                'sps-gallery-metabox',
                SPS_PLUGIN_URL . 'assets/css/gallery-metabox.css',
                array(),
                SPS_PLUGIN_VERSION
            );
            
            // Enqueue custom JS
            wp_enqueue_script(
                'sps-gallery-metabox',
                SPS_PLUGIN_URL . 'assets/js/gallery-metabox.js',
                array('jquery', 'media-upload', 'media-views'),
                SPS_PLUGIN_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script('sps-gallery-metabox', 'spsGalleryMetabox', array(
                'selectImage' => __('Select Image', 'simple-product-showcase'),
                'changeImage' => __('Change Image', 'simple-product-showcase'),
                'removeImage' => __('Remove Image', 'simple-product-showcase'),
                'useImage' => __('Use this image', 'simple-product-showcase'),
                'noImageSelected' => __('No image selected', 'simple-product-showcase'),
            ));
        }
    }
}
