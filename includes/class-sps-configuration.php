<?php
/**
 * SPS Configuration Page Class
 * 
 * Purpose: Mengelola halaman konfigurasi button untuk Simple Product Showcase plugin
 * Location: Products → Configuration (admin submenu)
 * Version: 1.5.0 (NEW - replaces class-sps-settings.php)
 * 
 * Fitur Utama:
 * - Konfigurasi 3 tombol custom: Main Button, Custom Button 1, Custom Button 2
 * - Main Button dengan 2 mode: WhatsApp Mode (simplified) atau Custom Mode (full control)
 * - Detail Page Settings: Default template atau Custom page dengan shortcodes
 * - Integration dengan WordPress Color Picker dan Media Library
 * - Save semua settings ke wp_options table dengan prefix 'sps_'
 * 
 * Database Structure (wp_options):
 * - sps_main_button_mode: 'whatsapp' atau 'custom'
 * - sps_main_visible: '1' atau '0' (boolean untuk show/hide)
 * - sps_main_text: String untuk button text
 * - sps_main_bg_color: Hex color untuk background
 * - sps_main_text_color: Hex color untuk text
 * - sps_main_icon: URL icon (hanya untuk custom mode)
 * - sps_main_url: URL tujuan (hanya untuk custom mode)
 * - sps_main_target: '_blank' atau '_self'
 * - (Similar structure untuk custom1 dan custom2)
 * - sps_detail_page_mode: 'default' atau 'custom'
 * - sps_custom_detail_page: WordPress page ID
 * 
 * Architecture Pattern:
 * - Singleton pattern untuk single instance
 * - WordPress Settings API untuk form handling
 * - Hook-based integration (admin_menu, admin_init)
 * - Static method untuk URL generation (backward compatibility)
 * 
 * Technical Notes:
 * - Class ini menggantikan class-sps-settings.php di version 1.5.0
 * - Old Settings class tetap ada tapi tidak di-load (backup emergency)
 * - Priority 11 pada admin_menu untuk load setelah CPT menu
 * - Enqueue scripts hanya di configuration page (performance optimization)
 * 
 * @package Simple_Product_Showcase
 * @subpackage Configuration
 * @since 1.5.0
 * @author Teguh Rijanandi
 */

if (!defined('ABSPATH')) {
    exit; // Direct access protection
}

class SPS_Configuration {
    
    /**
     * Single instance of this class (Singleton pattern)
     * @var SPS_Configuration|null
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * 
     * @return SPS_Configuration Single instance of class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - Private untuk enforce Singleton
     * 
     * Registers WordPress hooks:
     * - admin_menu: Add Configuration submenu page
     * - admin_init: Register all button settings dengan Settings API
     */
    private function __construct() {
        add_action('admin_menu', array($this, 'add_configuration_menu'), 11);
        add_action('admin_init', array($this, 'register_button_settings'));
    }
    
    /**
     * Add Configuration submenu to Products menu
     * 
     * Menu Structure:
     * - Parent: edit.php?post_type=sps_product (Products menu)
     * - Slug: sps-configuration
     * - Capability: manage_options (admin only)
     * - Priority: 11 (after default CPT menus)
     * 
     * Also registers load-{$hook} action untuk enqueue scripts
     */
    public function add_configuration_menu() {
        $hook = add_submenu_page(
            'edit.php?post_type=sps_product',
            __('Button Configuration', 'simple-product-showcase'),
            __('Configuration', 'simple-product-showcase'),
            'manage_options',
            'sps-configuration',
            array($this, 'configuration_page')
        );
        
        // Enqueue scripts only on configuration page (performance)
        add_action('load-' . $hook, array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue scripts and styles for configuration page
     * 
     * Dependencies:
     * - wp-color-picker: WordPress color picker untuk background & text color
     * - wp-media: WordPress Media Library untuk icon upload
     * - sps-gallery-admin.css: Custom styles untuk admin form
     * 
     * Called via load-{$hook} untuk load hanya di configuration page
     */
    public function enqueue_scripts() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_media();
        wp_enqueue_style('sps-gallery-admin', plugins_url('assets/css/gallery-admin.css', dirname(__FILE__)), array(), '1.0.0');
    }
    
    /**
     * Register button settings
     */
    public function register_button_settings() {
        $button_ids = array('main', 'custom1', 'custom2');
        
        foreach ($button_ids as $button_id) {
            register_setting('sps_configuration_group', 'sps_' . $button_id . '_visible', array(
                'type' => 'boolean',
                'default' => true
            ));
            
            register_setting('sps_configuration_group', 'sps_' . $button_id . '_text', array(
                'type' => 'string',
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field'
            ));
            
            // Main button mode (whatsapp or custom)
            if ($button_id === 'main') {
                register_setting('sps_configuration_group', 'sps_main_button_mode', array(
                    'type' => 'string',
                    'default' => 'whatsapp'
                ));
            }
            
            register_setting('sps_configuration_group', 'sps_' . $button_id . '_icon', array(
                'type' => 'string',
                'default' => '',
                'sanitize_callback' => 'esc_url_raw'
            ));
            
            register_setting('sps_configuration_group', 'sps_' . $button_id . '_icon_size', array(
                'type' => 'integer',
                'default' => 20,
                'sanitize_callback' => 'absint'
            ));
            
            register_setting('sps_configuration_group', 'sps_' . $button_id . '_url', array(
                'type' => 'string',
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field'
            ));
            
            register_setting('sps_configuration_group', 'sps_' . $button_id . '_target', array(
                'type' => 'string',
                'default' => '_blank'
            ));
            
            register_setting('sps_configuration_group', 'sps_' . $button_id . '_bg_color', array(
                'type' => 'string',
                'default' => '#25D366',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            
            register_setting('sps_configuration_group', 'sps_' . $button_id . '_text_color', array(
                'type' => 'string',
                'default' => '#ffffff',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
        }
        
        // Legacy WhatsApp settings
        register_setting('sps_configuration_group', 'sps_whatsapp_number', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        register_setting('sps_configuration_group', 'sps_whatsapp_message', array(
            'type' => 'string',
            'default' => 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}',
            'sanitize_callback' => 'sanitize_textarea_field'
        ));
        
        // Detail Page Settings
        register_setting('sps_configuration_group', 'sps_detail_page_mode', array(
            'type' => 'string',
            'default' => 'default'
        ));
        
        register_setting('sps_configuration_group', 'sps_custom_detail_page', array(
            'type' => 'integer',
            'default' => 0
        ));
    }
    
    /**
     * Configuration page content
     */
    public function configuration_page() {
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['sps_configuration_nonce'], 'sps_configuration_save')) {
            $this->save_configuration();
        }
        ?>
        <div class="wrap">
            <h1>🎨 <?php _e('Button Configuration', 'simple-product-showcase'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('sps_configuration_save', 'sps_configuration_nonce'); ?>
                
                <?php $this->render_button_settings(); ?>
                
                <?php $this->render_detail_page_settings(); ?>
                
                <?php submit_button(__('Save Configuration', 'simple-product-showcase')); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Initialize color pickers
            $('.sps-color-picker').wpColorPicker();
            
            // Main button mode switcher
            $('#sps_main_button_mode').on('change', function() {
                var mode = $(this).val();
                if (mode === 'whatsapp') {
                    $('#sps-main-whatsapp-fields').show();
                    $('#sps-main-custom-fields').hide();
                } else {
                    $('#sps-main-whatsapp-fields').hide();
                    $('#sps-main-custom-fields').show();
                }
            });
            
            // Media uploader for icons
            $('.sps-upload-icon-button').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                var inputField = button.prev('.sps-icon-input');
                var preview = button.siblings('.sps-icon-preview');
                
                var mediaUploader = wp.media({
                    title: 'Choose Button Icon',
                    button: { text: 'Use this icon' },
                    multiple: false,
                    library: { type: 'image' }
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    inputField.val(attachment.url);
                    preview.html('<img src="' + attachment.url + '" style="max-width: 50px; max-height: 50px;">');
                });
                
                mediaUploader.open();
            });
            
            // Remove icon
            $('.sps-remove-icon-button').on('click', function(e) {
                e.preventDefault();
                $(this).siblings('.sps-icon-input').val('');
                $(this).siblings('.sps-icon-preview').html('');
            });
        });
        </script>
        
        <style>
        .sps-button-config-box {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .sps-button-config-box h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #0073aa;
        }
        .sps-form-row {
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sps-form-row label {
            min-width: 150px;
            font-weight: 600;
        }
        .sps-form-row input[type="text"],
        .sps-form-row textarea {
            flex: 1;
            max-width: 500px;
        }
        .sps-icon-preview {
            display: inline-block;
            min-width: 60px;
            min-height: 50px;
            border: 1px dashed #ccc;
            padding: 5px;
            text-align: center;
        }
        .sps-icon-preview img {
            max-width: 50px;
            max-height: 50px;
            object-fit: contain;
        }
        </style>
        <?php
    }
    
    /**
     * Render button settings
     */
    private function render_button_settings() {
        $buttons = array(
            'main' => array(
                'title' => __('Main Button (WhatsApp/Custom)', 'simple-product-showcase'),
                'desc' => __('Primary button - can be WhatsApp or custom action', 'simple-product-showcase')
            ),
            'custom1' => array(
                'title' => __('Custom Button 1', 'simple-product-showcase'),
                'desc' => __('Second button for custom actions', 'simple-product-showcase')
            ),
            'custom2' => array(
                'title' => __('Custom Button 2', 'simple-product-showcase'),
                'desc' => __('Third button for additional actions', 'simple-product-showcase')
            )
        );
        
        foreach ($buttons as $button_id => $button_info) {
            $this->render_button_box($button_id, $button_info['title'], $button_info['desc']);
        }
    }
    
    /**
     * Render single button configuration box
     */
    private function render_button_box($button_id, $title, $description) {
        // Check if this is the main button
        $is_main_button = ($button_id === 'main');
        
        // Default visibility: true for main, false for custom1 and custom2
        $default_visible = $is_main_button ? true : false;
        
        $visible = get_option('sps_' . $button_id . '_visible', $default_visible);
        $text = get_option('sps_' . $button_id . '_text', $is_main_button ? 'Tanya Produk' : '');
        $icon = get_option('sps_' . $button_id . '_icon', '');
        $icon_size = get_option('sps_' . $button_id . '_icon_size', 20);
        $url = get_option('sps_' . $button_id . '_url', '');
        $target = get_option('sps_' . $button_id . '_target', '_blank');
        $bg_color = get_option('sps_' . $button_id . '_bg_color', '#25D366');
        $text_color = get_option('sps_' . $button_id . '_text_color', '#ffffff');
        
        // Main button specific
        $main_mode = get_option('sps_main_button_mode', 'whatsapp');
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        $whatsapp_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
        ?>
        <div class="sps-button-config-box" id="sps-config-<?php echo esc_attr($button_id); ?>">
            <h2><?php echo esc_html($title); ?></h2>
            <p><?php echo esc_html($description); ?></p>
            
            <div class="sps-form-row">
                <label>
                    <input type="checkbox" name="sps_<?php echo esc_attr($button_id); ?>_visible" value="1" <?php checked($visible, true); ?>>
                    <?php _e('Show this button', 'simple-product-showcase'); ?>
                </label>
            </div>
            
            <?php if ($is_main_button): ?>
                <!-- Main Button Mode Selector -->
                <div class="sps-form-row">
                    <label><?php _e('Button Mode:', 'simple-product-showcase'); ?></label>
                    <select name="sps_main_button_mode" id="sps_main_button_mode" style="max-width: 300px;">
                        <option value="whatsapp" <?php selected($main_mode, 'whatsapp'); ?>><?php _e('WhatsApp Mode', 'simple-product-showcase'); ?></option>
                        <option value="custom" <?php selected($main_mode, 'custom'); ?>><?php _e('Custom Mode', 'simple-product-showcase'); ?></option>
                    </select>
                    <small style="margin-left: 10px; color: #666;">
                        <?php _e('WhatsApp = Simplified settings | Custom = Full control', 'simple-product-showcase'); ?>
                    </small>
                </div>
                
                <!-- WhatsApp Mode Fields -->
                <div id="sps-main-whatsapp-fields" style="<?php echo $main_mode === 'custom' ? 'display:none;' : ''; ?>">
                    <div class="sps-form-row">
                        <label><?php _e('Button Text:', 'simple-product-showcase'); ?></label>
                        <input type="text" name="sps_main_text_wa" value="<?php echo esc_attr($text); ?>" class="regular-text" placeholder="Tanya Produk">
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('WhatsApp Number:', 'simple-product-showcase'); ?></label>
                        <input type="text" name="sps_whatsapp_number" value="<?php echo esc_attr($whatsapp_number); ?>" class="regular-text" placeholder="+6281234567890" required>
                        <small style="margin-left: 10px; color: #666;">
                            <?php _e('With country code (e.g., +6281234567890)', 'simple-product-showcase'); ?>
                        </small>
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('WhatsApp Message:', 'simple-product-showcase'); ?></label>
                        <textarea name="sps_whatsapp_message" rows="3" class="regular-text" style="max-width: 600px;" placeholder="<?php _e('Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}', 'simple-product-showcase'); ?>"><?php echo esc_textarea($whatsapp_message); ?></textarea>
                        <br><small style="color: #666;">
                            <?php _e('Use {product_name} and {product_link} as placeholders', 'simple-product-showcase'); ?>
                        </small>
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('Background Color:', 'simple-product-showcase'); ?></label>
                        <input type="text" name="sps_main_bg_color_wa" value="<?php echo esc_attr($bg_color); ?>" class="sps-color-picker">
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('Text Color:', 'simple-product-showcase'); ?></label>
                        <input type="text" name="sps_main_text_color_wa" value="<?php echo esc_attr($text_color); ?>" class="sps-color-picker">
                    </div>
                </div>
                
                <!-- Custom Mode Fields -->
                <div id="sps-main-custom-fields" style="<?php echo $main_mode === 'whatsapp' ? 'display:none;' : ''; ?>">
                    <div class="sps-form-row">
                        <label><?php _e('Button Text:', 'simple-product-showcase'); ?></label>
                        <input type="text" name="sps_main_text" value="<?php echo esc_attr($text); ?>" class="regular-text" placeholder="<?php _e('e.g., Contact Us', 'simple-product-showcase'); ?>">
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('Button Icon:', 'simple-product-showcase'); ?></label>
                        <input type="text" name="sps_main_icon" value="<?php echo esc_attr($icon); ?>" class="regular-text sps-icon-input" placeholder="<?php _e('Icon URL', 'simple-product-showcase'); ?>">
                        <button type="button" class="button sps-upload-icon-button"><?php _e('Upload', 'simple-product-showcase'); ?></button>
                        <button type="button" class="button sps-remove-icon-button"><?php _e('Remove', 'simple-product-showcase'); ?></button>
                        <div class="sps-icon-preview">
                            <?php if ($icon): ?>
                                <img src="<?php echo esc_url($icon); ?>" alt="Icon">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('Icon Size:', 'simple-product-showcase'); ?></label>
                        <input type="number" name="sps_main_icon_size" value="<?php echo esc_attr($icon_size); ?>" class="small-text" min="10" max="100" step="1"> px
                        <p class="description"><?php _e('Icon width in pixels (10-100px, default: 20px)', 'simple-product-showcase'); ?></p>
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('Button URL:', 'simple-product-showcase'); ?></label>
                        <input type="text" name="sps_main_url" value="<?php echo esc_attr($url); ?>" class="regular-text" placeholder="<?php _e('https://example.com', 'simple-product-showcase'); ?>">
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('Open in:', 'simple-product-showcase'); ?></label>
                        <select name="sps_main_target">
                            <option value="_blank" <?php selected($target, '_blank'); ?>><?php _e('New Tab', 'simple-product-showcase'); ?></option>
                            <option value="_self" <?php selected($target, '_self'); ?>><?php _e('Same Tab', 'simple-product-showcase'); ?></option>
                        </select>
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('Background Color:', 'simple-product-showcase'); ?></label>
                        <input type="text" name="sps_main_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="sps-color-picker">
                    </div>
                    
                    <div class="sps-form-row">
                        <label><?php _e('Text Color:', 'simple-product-showcase'); ?></label>
                        <input type="text" name="sps_main_text_color" value="<?php echo esc_attr($text_color); ?>" class="sps-color-picker">
                    </div>
                </div>
            <?php else: ?>
                <!-- Custom Button 1 & 2 Fields -->
                <div class="sps-form-row">
                    <label><?php _e('Button Text:', 'simple-product-showcase'); ?></label>
                    <input type="text" name="sps_<?php echo esc_attr($button_id); ?>_text" value="<?php echo esc_attr($text); ?>" class="regular-text" placeholder="<?php _e('e.g., Contact Us', 'simple-product-showcase'); ?>">
                </div>
                
                <div class="sps-form-row">
                    <label><?php _e('Button Icon:', 'simple-product-showcase'); ?></label>
                    <input type="text" name="sps_<?php echo esc_attr($button_id); ?>_icon" value="<?php echo esc_attr($icon); ?>" class="regular-text sps-icon-input" placeholder="<?php _e('Icon URL', 'simple-product-showcase'); ?>">
                    <button type="button" class="button sps-upload-icon-button"><?php _e('Upload', 'simple-product-showcase'); ?></button>
                    <button type="button" class="button sps-remove-icon-button"><?php _e('Remove', 'simple-product-showcase'); ?></button>
                    <div class="sps-icon-preview">
                        <?php if ($icon): ?>
                            <img src="<?php echo esc_url($icon); ?>" alt="Icon">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="sps-form-row">
                    <label><?php _e('Icon Size:', 'simple-product-showcase'); ?></label>
                    <input type="number" name="sps_<?php echo esc_attr($button_id); ?>_icon_size" value="<?php echo esc_attr($icon_size); ?>" class="small-text" min="10" max="100" step="1"> px
                    <p class="description"><?php _e('Icon width in pixels (10-100px, default: 20px)', 'simple-product-showcase'); ?></p>
                </div>
                
                <div class="sps-form-row">
                    <label><?php _e('Button URL:', 'simple-product-showcase'); ?></label>
                    <input type="text" name="sps_<?php echo esc_attr($button_id); ?>_url" value="<?php echo esc_attr($url); ?>" class="regular-text" placeholder="<?php _e('https://example.com', 'simple-product-showcase'); ?>">
                </div>
                
                <div class="sps-form-row">
                    <label><?php _e('Open in:', 'simple-product-showcase'); ?></label>
                    <select name="sps_<?php echo esc_attr($button_id); ?>_target">
                        <option value="_blank" <?php selected($target, '_blank'); ?>><?php _e('New Tab', 'simple-product-showcase'); ?></option>
                        <option value="_self" <?php selected($target, '_self'); ?>><?php _e('Same Tab', 'simple-product-showcase'); ?></option>
                    </select>
                </div>
                
                <div class="sps-form-row">
                    <label><?php _e('Background Color:', 'simple-product-showcase'); ?></label>
                    <input type="text" name="sps_<?php echo esc_attr($button_id); ?>_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="sps-color-picker">
                </div>
                
                <div class="sps-form-row">
                    <label><?php _e('Text Color:', 'simple-product-showcase'); ?></label>
                    <input type="text" name="sps_<?php echo esc_attr($button_id); ?>_text_color" value="<?php echo esc_attr($text_color); ?>" class="sps-color-picker">
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render Detail Page Settings
     */
    private function render_detail_page_settings() {
        $detail_mode = get_option('sps_detail_page_mode', 'default');
        $custom_page = get_option('sps_custom_detail_page', 0);
        ?>
        <div class="sps-button-config-box">
            <h2><?php _e('Detail Page Settings', 'simple-product-showcase'); ?></h2>
            <p><?php _e('Configure how product detail pages are displayed when users click the "Detail" button.', 'simple-product-showcase'); ?></p>
            
            <div class="sps-form-row">
                <label><?php _e('Detail Page Mode:', 'simple-product-showcase'); ?></label>
                <select name="sps_detail_page_mode">
                    <option value="default" <?php selected($detail_mode, 'default'); ?>><?php _e('Default Template', 'simple-product-showcase'); ?></option>
                    <option value="custom" <?php selected($detail_mode, 'custom'); ?>><?php _e('Custom Page with Shortcodes', 'simple-product-showcase'); ?></option>
                </select>
            </div>
            
            <div class="sps-form-row">
                <p class="description">
                    <strong><?php _e('Default:', 'simple-product-showcase'); ?></strong> <?php _e('Uses the built-in single product template with all information.', 'simple-product-showcase'); ?><br>
                    <strong><?php _e('Custom:', 'simple-product-showcase'); ?></strong> <?php _e('Redirects to a custom page where you can use shortcodes.', 'simple-product-showcase'); ?>
                </p>
            </div>
            
            <div class="sps-form-row">
                <label><?php _e('Custom Detail Page:', 'simple-product-showcase'); ?></label>
                <?php
                wp_dropdown_pages(array(
                    'name' => 'sps_custom_detail_page',
                    'id' => 'sps_custom_detail_page',
                    'selected' => $custom_page,
                    'show_option_none' => __('-- Select a page --', 'simple-product-showcase'),
                    'option_none_value' => '0'
                ));
                ?>
            </div>
            
            <div class="sps-form-row">
                <p class="description">
                    <?php _e('Select the page where you want to display product details using shortcodes. This page should contain shortcodes like [sps_detail_products section="title"].', 'simple-product-showcase'); ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get product detail URL based on settings (static method for backward compatibility)
     */
    public static function get_product_detail_url($product_id) {
        $detail_mode = get_option('sps_detail_page_mode', 'default');

        if ($detail_mode === 'custom') {
            $custom_page_id = get_option('sps_custom_detail_page', 0);
            if ($custom_page_id) {
                $product = get_post($product_id);
                if ($product && $product->post_type === 'sps_product') {
                    $page_url = get_permalink($custom_page_id);
                    return add_query_arg('product', $product->post_name, $page_url);
                }
            }
        }

        // Default: use single product permalink
        return get_permalink($product_id);
    }
    
    /**
     * Save configuration
     */
    private function save_configuration() {
        $button_ids = array('main', 'custom1', 'custom2');
        
        // Save main button mode
        if (isset($_POST['sps_main_button_mode'])) {
            $main_mode = sanitize_text_field($_POST['sps_main_button_mode']);
            update_option('sps_main_button_mode', $main_mode);
            
            // Save based on mode
            if ($main_mode === 'whatsapp') {
                // WhatsApp mode - save simplified fields
                update_option('sps_main_visible', isset($_POST['sps_main_visible']));
                update_option('sps_main_text', sanitize_text_field($_POST['sps_main_text_wa'] ?? 'Tanya Produk'));
                update_option('sps_main_bg_color', sanitize_hex_color($_POST['sps_main_bg_color_wa'] ?? '#25D366'));
                update_option('sps_main_text_color', sanitize_hex_color($_POST['sps_main_text_color_wa'] ?? '#ffffff'));
                
                // Save WhatsApp specific
                update_option('sps_whatsapp_number', sanitize_text_field($_POST['sps_whatsapp_number'] ?? ''));
                update_option('sps_whatsapp_message', sanitize_textarea_field($_POST['sps_whatsapp_message'] ?? ''));
                
                // Set URL to whatsapp automatically
                update_option('sps_main_url', '{whatsapp}');
                update_option('sps_main_target', '_blank');
            } else {
                // Custom mode - save full fields
                update_option('sps_main_visible', isset($_POST['sps_main_visible']));
                update_option('sps_main_text', sanitize_text_field($_POST['sps_main_text'] ?? ''));
                update_option('sps_main_icon', esc_url_raw($_POST['sps_main_icon'] ?? ''));
                update_option('sps_main_icon_size', absint($_POST['sps_main_icon_size'] ?? 20));
                update_option('sps_main_url', sanitize_text_field($_POST['sps_main_url'] ?? ''));
                update_option('sps_main_target', sanitize_text_field($_POST['sps_main_target'] ?? '_blank'));
                update_option('sps_main_bg_color', sanitize_hex_color($_POST['sps_main_bg_color'] ?? '#25D366'));
                update_option('sps_main_text_color', sanitize_hex_color($_POST['sps_main_text_color'] ?? '#ffffff'));
            }
        }
        
        // Save custom1 and custom2 buttons (always full fields)
        foreach (array('custom1', 'custom2') as $button_id) {
            update_option('sps_' . $button_id . '_visible', isset($_POST['sps_' . $button_id . '_visible']));
            update_option('sps_' . $button_id . '_text', sanitize_text_field($_POST['sps_' . $button_id . '_text'] ?? ''));
            update_option('sps_' . $button_id . '_icon', esc_url_raw($_POST['sps_' . $button_id . '_icon'] ?? ''));
            update_option('sps_' . $button_id . '_icon_size', absint($_POST['sps_' . $button_id . '_icon_size'] ?? 20));
            update_option('sps_' . $button_id . '_url', sanitize_text_field($_POST['sps_' . $button_id . '_url'] ?? ''));
            update_option('sps_' . $button_id . '_target', sanitize_text_field($_POST['sps_' . $button_id . '_target'] ?? '_blank'));
            update_option('sps_' . $button_id . '_bg_color', sanitize_hex_color($_POST['sps_' . $button_id . '_bg_color'] ?? '#25D366'));
            update_option('sps_' . $button_id . '_text_color', sanitize_hex_color($_POST['sps_' . $button_id . '_text_color'] ?? '#ffffff'));
        }
        
        // Save Detail Page Settings
        if (isset($_POST['sps_detail_page_mode'])) {
            update_option('sps_detail_page_mode', sanitize_text_field($_POST['sps_detail_page_mode']));
        }
        if (isset($_POST['sps_custom_detail_page'])) {
            update_option('sps_custom_detail_page', intval($_POST['sps_custom_detail_page']));
        }
        
        echo '<div class="notice notice-success"><p><strong>✅ Configuration saved successfully!</strong></p></div>';
    }
}

// Initialize
SPS_Configuration::get_instance();
