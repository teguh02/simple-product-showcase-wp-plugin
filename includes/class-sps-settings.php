<?php
/**
 * Class SPS_Settings
 * 
 * Class untuk mengelola halaman settings admin plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Settings {
    
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
     * DISABLED: Menu Settings digantikan dengan Configuration page
     */
    private function init_hooks() {
        // COMMENTED OUT: Settings menu replaced by Configuration
        // add_action('admin_menu', array($this, 'add_admin_menu'));
        // add_action('admin_init', array($this, 'register_settings'));
        
        add_filter('post_row_actions', array($this, 'modify_view_link'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Fallback: register settings immediately if admin_init already passed
        // COMMENTED OUT: Not needed for Configuration page
        // if (did_action('admin_init')) {
        //     $this->register_settings();
        // }
    }
    
    /**
     * Enqueue scripts and styles specifically for settings page
     */
    public function enqueue_settings_page_scripts() {
        // Enqueue WordPress color picker CSS
        wp_enqueue_style('wp-color-picker');
        
        // Enqueue WordPress color picker JS
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue media uploader
        wp_enqueue_media();
        
        // Enqueue jQuery (dependency for color picker)
        wp_enqueue_script('jquery');
    }
    
    /**
     * Enqueue admin scripts and styles for settings page (fallback)
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our settings page
        if ($hook !== 'sps_product_page_sps-settings') {
            return;
        }
        
        // Enqueue WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue media uploader
        wp_enqueue_media();
    }
    
    /**
     * Tambahkan menu admin
     * COMMENTED OUT: Using new Configuration page instead
     * To restore this menu, uncomment the code below and comment out class-sps-configuration.php in main plugin file
     */
    public function add_admin_menu() {
        /* BACKUP - OLD SETTINGS MENU - Uncomment if Configuration page has issues
        // Add Settings submenu under the Products menu (from custom post type)
        $hook = add_submenu_page(
            'edit.php?post_type=sps_product',
            __('Settings', 'simple-product-showcase'),
            __('Settings', 'simple-product-showcase'),
            'manage_options',
            'sps-settings',
            array($this, 'settings_page')
        );
        
        // Add action to enqueue scripts only on this page
        add_action('load-' . $hook, array($this, 'enqueue_settings_page_scripts'));
        */
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Register setting group
        register_setting('sps_settings_group', 'sps_whatsapp_number', array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_whatsapp_number')
        ));
        
        register_setting('sps_settings_group', 'sps_whatsapp_message', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field'
        ));
        
        register_setting('sps_settings_group', 'sps_whatsapp_button_text', array(
            'type' => 'string',
            'default' => 'Tanya Produk Ini',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        register_setting('sps_settings_group', 'sps_detail_page_mode', array(
            'type' => 'string',
            'default' => 'default'
        ));
        
        register_setting('sps_settings_group', 'sps_custom_detail_page', array(
            'type' => 'integer',
            'default' => 0
        ));
        
        // Register button settings
        $button_ids = array('main', 'custom1', 'custom2');
        foreach ($button_ids as $button_id) {
            register_setting('sps_settings_group', 'sps_' . $button_id . '_is_whatsapp', array(
                'type' => 'boolean',
                'default' => ($button_id === 'main') ? true : false
            ));
            
            register_setting('sps_settings_group', 'sps_' . $button_id . '_visible', array(
                'type' => 'boolean',
                'default' => true
            ));
            
            register_setting('sps_settings_group', 'sps_' . $button_id . '_text', array(
                'type' => 'string',
                'default' => ($button_id === 'main') ? 'Tanya Produk Ini' : 'Custom Button',
                'sanitize_callback' => 'sanitize_text_field'
            ));
            
            register_setting('sps_settings_group', 'sps_' . $button_id . '_icon', array(
                'type' => 'string',
                'default' => '',
                'sanitize_callback' => 'esc_url_raw'
            ));
            
            register_setting('sps_settings_group', 'sps_' . $button_id . '_url', array(
                'type' => 'string',
                'default' => '',
                'sanitize_callback' => 'esc_url_raw'
            ));
            
            register_setting('sps_settings_group', 'sps_' . $button_id . '_target', array(
                'type' => 'string',
                'default' => '_self'
            ));
            
            register_setting('sps_settings_group', 'sps_' . $button_id . '_background_color', array(
                'type' => 'string',
                'default' => ($button_id === 'main') ? '#25D366' : '#007cba',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            
            register_setting('sps_settings_group', 'sps_' . $button_id . '_text_color', array(
                'type' => 'string',
                'default' => '#ffffff',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
        }
        
        // NOTE: We no longer use add_settings_section() or add_settings_field()
        // because we render everything manually in settings_page() method
        // This gives us more control over the layout and structure
    }
    
    /**
     * Sanitize WhatsApp number
     */
    public function sanitize_whatsapp_number($value) {
        // Remove all non-numeric characters except +
        $value = preg_replace('/[^0-9+]/', '', $value);
        
        // If it doesn't start with +, add country code (assume Indonesia +62)
        if (!empty($value) && substr($value, 0, 1) !== '+') {
            // Remove leading 0 if present
            if (substr($value, 0, 1) === '0') {
                $value = substr($value, 1);
            }
            $value = '+62' . $value;
        }
        
        return $value;
    }
    
    /**
     * ========================================================================
     * DEPRECATED CALLBACK METHODS (kept for reference)
     * These are no longer used because we render everything manually in settings_page()
     * ========================================================================
     */
    
    /**
     * Button section callback (DEPRECATED - not used)
     */
    /*
    public function button_section_callback() {
        echo '<p>' . __('Configure the buttons that will be displayed in the product detail pages. Enter your WhatsApp number for the main button if it\'s set as WhatsApp type.', 'simple-product-showcase') . '</p>';
    }
    
    /**
     * WhatsApp section callback
     */
    public function whatsapp_section_callback() {
        echo '<p>' . __('Configure WhatsApp integration settings for product contact buttons.', 'simple-product-showcase') . '</p>';
    }
    
    /**
     * Detail Page section callback
     */
    public function detail_page_section_callback() {
        echo '<p>' . __('Configure how product detail pages are displayed when users click the "Detail" button.', 'simple-product-showcase') . '</p>';
    }
    
    /**
     * WhatsApp message field callback
     */
    public function whatsapp_message_field_callback() {
        $value = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
        ?>
        <textarea 
            name="sps_whatsapp_message" 
            id="sps_whatsapp_message" 
            rows="4" 
            cols="50" 
            class="large-text"
            placeholder="Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}"
        ><?php echo esc_textarea($value); ?></textarea>
        <p class="description">
            <?php _e('Default message template for WhatsApp contact. Available placeholders: {product_link} for product URL, {product_name} for product title.', 'simple-product-showcase'); ?>
        </p>
        <?php
    }
    
    /**
     * WhatsApp button text field callback
     */
    public function whatsapp_button_text_field_callback() {
        $value = get_option('sps_whatsapp_button_text', 'Tanya Produk Ini');
        ?>
        <input 
            type="text" 
            name="sps_whatsapp_button_text" 
            id="sps_whatsapp_button_text" 
            value="<?php echo esc_attr($value); ?>" 
            class="regular-text"
            placeholder="Tanya Produk Ini"
        />
        <p class="description">
            <?php _e('Customize the text displayed on the WhatsApp contact button. Default: "Tanya Produk Ini"', 'simple-product-showcase'); ?>
        </p>
        <?php
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
     * WhatsApp number field callback
     */
    public function whatsapp_number_field_callback() {
        $value = get_option('sps_whatsapp_number', '');
        ?>
        <input 
            type="text" 
            name="sps_whatsapp_number" 
            id="sps_whatsapp_number" 
            value="<?php echo esc_attr($value); ?>" 
            class="regular-text"
            placeholder="+6281234567890"
            required
        />
        <p class="description">
            <?php _e('Enter your WhatsApp number with country code (e.g., +6281234567890). This number will be used for all product contact buttons.', 'simple-product-showcase'); ?>
        </p>
        <?php
    }
    
    /**
     * Save settings
     */
    public function save_settings() {
        // DEBUG LOGGING
        error_log('*** save_settings() START');
        error_log('*** POST data count: ' . count($_POST));
        error_log('*** File: ' . __FILE__ . ' Line: ' . __LINE__);
        
        // Save existing settings
        if (isset($_POST['sps_whatsapp_number'])) {
            error_log('*** Saving WhatsApp Number: ' . $_POST['sps_whatsapp_number']);
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
        
        // Save button settings
        $button_ids = array('main', 'custom1', 'custom2');
        foreach ($button_ids as $button_id) {
            // Save boolean values
            update_option('sps_' . $button_id . '_is_whatsapp', isset($_POST['sps_' . $button_id . '_is_whatsapp']) ? (bool) $_POST['sps_' . $button_id . '_is_whatsapp'] : false);
            update_option('sps_' . $button_id . '_visible', isset($_POST['sps_' . $button_id . '_visible']) ? (bool) $_POST['sps_' . $button_id . '_visible'] : false);
            
            // Save text values
            if (isset($_POST['sps_' . $button_id . '_text'])) {
                update_option('sps_' . $button_id . '_text', sanitize_text_field($_POST['sps_' . $button_id . '_text']));
            }
            
            // Save URL values
            if (isset($_POST['sps_' . $button_id . '_icon'])) {
                update_option('sps_' . $button_id . '_icon', esc_url_raw($_POST['sps_' . $button_id . '_icon']));
            }
            if (isset($_POST['sps_' . $button_id . '_url'])) {
                update_option('sps_' . $button_id . '_url', esc_url_raw($_POST['sps_' . $button_id . '_url']));
            }
            
            // Save target
            if (isset($_POST['sps_' . $button_id . '_target'])) {
                update_option('sps_' . $button_id . '_target', sanitize_text_field($_POST['sps_' . $button_id . '_target']));
            }
            
            // Save colors
            if (isset($_POST['sps_' . $button_id . '_background_color'])) {
                update_option('sps_' . $button_id . '_background_color', sanitize_hex_color($_POST['sps_' . $button_id . '_background_color']));
            }
            if (isset($_POST['sps_' . $button_id . '_text_color'])) {
                update_option('sps_' . $button_id . '_text_color', sanitize_hex_color($_POST['sps_' . $button_id . '_text_color']));
            }
        }
        
        error_log('*** Button settings saved successfully');
        error_log('*** save_settings() END');
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'simple-product-showcase') . '</p></div>';
    }
    public function modify_view_link($actions, $post) {
        // Only modify for sps_product post type
        if ($post->post_type === 'sps_product') {
            $detail_mode = get_option('sps_detail_page_mode', 'default');
            
            if ($detail_mode === 'custom') {
                $custom_page_id = get_option('sps_custom_detail_page', 0);
                if ($custom_page_id) {
                    $page_url = get_permalink($custom_page_id);
                    $slug_url = add_query_arg('product', $post->post_name, $page_url);
                    
                    // Replace the view action with our custom URL
                    $actions['view'] = sprintf(
                        '<a href="%s" aria-label="%s" target="_blank" rel="noopener">%s</a>',
                        esc_url($slug_url),
                        esc_attr(sprintf(__('View &#8220;%s&#8221;'), $post->post_title)),
                        __('Lihat')
                    );
                }
            }
        }
        
        return $actions;
    }
    
    /**
     * Get product detail URL based on settings
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
     * Render button settings section (without tabs)
     */
    public function render_button_settings_section() {
        // Force output untuk debugging
        echo '<div style="background: #00ff00; color: #000; padding: 20px; margin: 20px 0; border: 3px solid #000; font-size: 16px; font-weight: bold;">';
        echo '✅ Method render_button_settings_section() IS BEING CALLED!';
        echo '</div>';
        
        ?>
        <div class="sps-button-settings-section">
            <h2><?php _e('Button Configuration', 'simple-product-showcase'); ?></h2>
            <p><?php _e('Configure the three buttons that will be displayed in the product detail pages. You can customize text, icons, URLs, colors, and visibility for each button.', 'simple-product-showcase'); ?></p>
            
            <div class="notice notice-success">
                <p><strong>Button Settings:</strong> Configure your WhatsApp and custom buttons below.</p>
            </div>
            
            <?php 
            error_log('>>> Calling render_button_config_section for MAIN button');
            $this->render_button_config_section('main', __('Main Button (WhatsApp/Custom)', 'simple-product-showcase'), __('This is the primary button. You can choose to keep it as WhatsApp or make it a custom button.', 'simple-product-showcase')); 
            ?>
            
            <?php 
            error_log('>>> Calling render_button_config_section for CUSTOM1 button');
            $this->render_button_config_section('custom1', __('Custom Button 1', 'simple-product-showcase'), __('Second button for custom actions like contact page, catalog, etc.', 'simple-product-showcase')); 
            ?>
            
            <?php 
            error_log('>>> Calling render_button_config_section for CUSTOM2 button');
            $this->render_button_config_section('custom2', __('Custom Button 2', 'simple-product-showcase'), __('Third button for additional custom actions.', 'simple-product-showcase')); 
            ?>
        </div>
        
        <style>
        .sps-button-settings-section {
            margin: 30px 0;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .sps-button-settings-section h2 {
            margin-top: 0;
            color: #23282d;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        
        .sps-button-config-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin: 20px 0;
            padding: 20px;
        }
        
        .sps-button-config-section h3 {
            margin-top: 0;
            color: #23282d;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .sps-button-config-section .form-table {
            margin-top: 15px;
        }
        
        .sps-button-config-section .form-table th {
            width: 200px;
            padding: 15px 10px 15px 0;
        }
        
        .sps-button-config-section .form-table td {
            padding: 15px 10px;
        }
        
        .sps-color-picker {
            width: 100px;
            height: 35px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .sps-icon-preview {
            width: 32px;
            height: 32px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: inline-block;
            margin-right: 10px;
            vertical-align: middle;
            background: #f9f9f9;
            text-align: center;
            line-height: 30px;
        }
        
        .sps-icon-preview img {
            max-width: 24px;
            max-height: 24px;
            vertical-align: middle;
        }
        
        .sps-button-preview {
            margin-top: 15px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .sps-button-preview h4 {
            margin-top: 0;
            color: #666;
        }
        
        .sps-preview-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .sps-preview-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .sps-preview-icon {
            width: 20px;
            height: 20px;
            display: inline-block;
        }
        
        .sps-preview-icon img {
            max-width: 100%;
            max-height: 100%;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Color picker initialization
            $('.sps-color-picker').wpColorPicker({
                change: function(event, ui) {
                    var buttonId = $(this).data('button');
                    var colorType = $(this).data('color-type');
                    updateButtonPreview(buttonId, colorType, ui.color.toString());
                }
            });
            
            // Icon upload
            $('.sps-icon-upload').click(function(e) {
                e.preventDefault();
                var buttonId = $(this).data('button');
                var frame = wp.media({
                    title: 'Select Button Icon',
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#sps_' + buttonId + '_icon').val(attachment.url);
                    $('#sps_' + buttonId + '_icon_preview').html('<img src="' + attachment.url + '" alt="Icon">');
                    updateButtonPreview(buttonId, 'icon', attachment.url);
                });
                
                frame.open();
            });
            
            // Remove icon
            $('.sps-icon-remove').click(function(e) {
                e.preventDefault();
                var buttonId = $(this).data('button');
                $('#sps_' + buttonId + '_icon').val('');
                $('#sps_' + buttonId + '_icon_preview').html('No Icon');
                updateButtonPreview(buttonId, 'icon', '');
            });
            
            // Update button preview on text change
            $('.sps-button-text').on('input', function() {
                var buttonId = $(this).data('button');
                updateButtonPreview(buttonId, 'text', $(this).val());
            });
            
            // Update button preview on visibility change
            $('.sps-button-visibility').change(function() {
                var buttonId = $(this).data('button');
                var isVisible = $(this).is(':checked');
                $('#sps-preview-' + buttonId).toggle(isVisible);
            });
            
            function updateButtonPreview(buttonId, type, value) {
                var previewButton = $('#sps-preview-' + buttonId);
                
                if (type === 'text') {
                    previewButton.find('.sps-preview-text').text(value || 'Button Text');
                } else if (type === 'icon') {
                    var iconHtml = value ? '<img src="' + value + '" alt="Icon">' : '';
                    previewButton.find('.sps-preview-icon').html(iconHtml);
                } else if (type === 'background') {
                    previewButton.css('background-color', value);
                } else if (type === 'text-color') {
                    previewButton.css('color', value);
                }
            }
            
            // Initial preview update
            $('.sps-button-config-section').each(function() {
                var buttonId = $(this).data('button');
                updateButtonPreview(buttonId, 'text', $('#sps_' + buttonId + '_text').val());
                updateButtonPreview(buttonId, 'icon', $('#sps_' + buttonId + '_icon').val());
                updateButtonPreview(buttonId, 'background', $('#sps_' + buttonId + '_background_color').val());
                updateButtonPreview(buttonId, 'text-color', $('#sps_' + buttonId + '_text_color').val());
            });
        });
        </script>
        <?php
        error_log('>>> render_button_settings_section() END');
    }
    
    /**
     * Render button settings tab (deprecated - keeping for compatibility)
     */
    public function render_button_settings_tab() {
        error_log('SPS_Settings::render_button_settings_tab() called');
        ?>
        <div class="sps-button-settings">
            <h2><?php _e('Button Configuration', 'simple-product-showcase'); ?></h2>
            <p><?php _e('Configure the three buttons that will be displayed in the product detail pages. You can customize text, icons, URLs, colors, and visibility for each button.', 'simple-product-showcase'); ?></p>
            
            <div class="notice notice-success">
                <p><strong>DEBUG:</strong> Button Settings Tab is working!</p>
            </div>
            
            <?php $this->render_button_config_section('main', __('Main Button (WhatsApp/Custom)', 'simple-product-showcase'), __('This is the primary button. You can choose to keep it as WhatsApp or make it a custom button.', 'simple-product-showcase')); ?>
            
            <?php $this->render_button_config_section('custom1', __('Custom Button 1', 'simple-product-showcase'), __('Second button for custom actions like contact page, catalog, etc.', 'simple-product-showcase')); ?>
            
            <?php $this->render_button_config_section('custom2', __('Custom Button 2', 'simple-product-showcase'), __('Third button for additional custom actions.', 'simple-product-showcase')); ?>
        </div>
        <?php
    }
    
    /**
     * Render button configuration section
     */
    public function render_button_config_section($button_id, $title, $description) {
        // DEBUG LOGGING
        error_log('>>>>> render_button_config_section() START for button: ' . $button_id);
        error_log('>>>>> Title: ' . $title);
        error_log('>>>>> File: ' . __FILE__ . ' Line: ' . __LINE__);
        
        $is_main_button = ($button_id === 'main');
        $is_whatsapp = get_option('sps_' . $button_id . '_is_whatsapp', $is_main_button ? true : false);
        $is_visible = get_option('sps_' . $button_id . '_visible', true);
        $button_text = get_option('sps_' . $button_id . '_text', $is_main_button ? 'Tanya Produk Ini' : 'Custom Button');
        $button_icon = get_option('sps_' . $button_id . '_icon', '');
        $button_url = get_option('sps_' . $button_id . '_url', '');
        $button_target = get_option('sps_' . $button_id . '_target', '_self');
        $button_bg_color = get_option('sps_' . $button_id . '_background_color', $is_main_button ? '#25D366' : '#007cba');
        $button_text_color = get_option('sps_' . $button_id . '_text_color', '#ffffff');
        
        ?>
        <div class="sps-button-config-section" data-button="<?php echo esc_attr($button_id); ?>">
            <h3><?php echo esc_html($title); ?></h3>
            <p><?php echo esc_html($description); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Visibility', 'simple-product-showcase'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="sps_<?php echo esc_attr($button_id); ?>_visible" value="1" 
                                   class="sps-button-visibility" data-button="<?php echo esc_attr($button_id); ?>"
                                   <?php checked($is_visible); ?> />
                            <?php _e('Show this button', 'simple-product-showcase'); ?>
                        </label>
                    </td>
                </tr>
                
                <?php if ($is_main_button): ?>
                <tr>
                    <th scope="row"><?php _e('Button Type', 'simple-product-showcase'); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="sps_<?php echo esc_attr($button_id); ?>_is_whatsapp" value="1" 
                                   <?php checked($is_whatsapp); ?> />
                            <?php _e('WhatsApp Button', 'simple-product-showcase'); ?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="sps_<?php echo esc_attr($button_id); ?>_is_whatsapp" value="0" 
                                   <?php checked(!$is_whatsapp); ?> />
                            <?php _e('Custom Button', 'simple-product-showcase'); ?>
                        </label>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th scope="row"><?php _e('Button Text', 'simple-product-showcase'); ?></th>
                    <td>
                        <input type="text" name="sps_<?php echo esc_attr($button_id); ?>_text" 
                               value="<?php echo esc_attr($button_text); ?>" 
                               class="regular-text sps-button-text" data-button="<?php echo esc_attr($button_id); ?>" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Button Icon', 'simple-product-showcase'); ?></th>
                    <td>
                        <div class="sps-icon-preview" id="sps_<?php echo esc_attr($button_id); ?>_icon_preview">
                            <?php if ($button_icon): ?>
                                <img src="<?php echo esc_url($button_icon); ?>" alt="Icon">
                            <?php else: ?>
                                No Icon
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="sps_<?php echo esc_attr($button_id); ?>_icon" 
                               id="sps_<?php echo esc_attr($button_id); ?>_icon" 
                               value="<?php echo esc_url($button_icon); ?>" />
                        <button type="button" class="button sps-icon-upload" data-button="<?php echo esc_attr($button_id); ?>">
                            <?php _e('Upload Icon', 'simple-product-showcase'); ?>
                        </button>
                        <button type="button" class="button sps-icon-remove" data-button="<?php echo esc_attr($button_id); ?>">
                            <?php _e('Remove Icon', 'simple-product-showcase'); ?>
                        </button>
                        <p class="description"><?php _e('Upload a PNG, JPG, or SVG icon for the button. Recommended size: 20x20px.', 'simple-product-showcase'); ?></p>
                    </td>
                </tr>
                
                <?php if (!$is_main_button || !$is_whatsapp): ?>
                <tr>
                    <th scope="row"><?php _e('Button URL', 'simple-product-showcase'); ?></th>
                    <td>
                        <input type="url" name="sps_<?php echo esc_attr($button_id); ?>_url" 
                               value="<?php echo esc_attr($button_url); ?>" class="regular-text" />
                        <p class="description"><?php _e('Enter the URL where this button should link to.', 'simple-product-showcase'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Link Target', 'simple-product-showcase'); ?></th>
                    <td>
                        <select name="sps_<?php echo esc_attr($button_id); ?>_target">
                            <option value="_self" <?php selected($button_target, '_self'); ?>><?php _e('Same Window', 'simple-product-showcase'); ?></option>
                            <option value="_blank" <?php selected($button_target, '_blank'); ?>><?php _e('New Window', 'simple-product-showcase'); ?></option>
                        </select>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th scope="row"><?php _e('Background Color', 'simple-product-showcase'); ?></th>
                    <td>
                        <input type="text" name="sps_<?php echo esc_attr($button_id); ?>_background_color" 
                               id="sps_<?php echo esc_attr($button_id); ?>_background_color"
                               value="<?php echo esc_attr($button_bg_color); ?>" 
                               class="sps-color-picker" data-button="<?php echo esc_attr($button_id); ?>" data-color-type="background" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Text Color', 'simple-product-showcase'); ?></th>
                    <td>
                        <input type="text" name="sps_<?php echo esc_attr($button_id); ?>_text_color" 
                               id="sps_<?php echo esc_attr($button_id); ?>_text_color"
                               value="<?php echo esc_attr($button_text_color); ?>" 
                               class="sps-color-picker" data-button="<?php echo esc_attr($button_id); ?>" data-color-type="text-color" />
                    </td>
                </tr>
            </table>
            
            <div class="sps-button-preview">
                <h4><?php _e('Button Preview', 'simple-product-showcase'); ?></h4>
                <a href="#" class="sps-preview-button" id="sps-preview-<?php echo esc_attr($button_id); ?>" 
                   style="background-color: <?php echo esc_attr($button_bg_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;"
                   <?php echo $is_visible ? '' : 'style="display: none;"'; ?>>
                    <span class="sps-preview-icon">
                        <?php if ($button_icon): ?>
                            <img src="<?php echo esc_url($button_icon); ?>" alt="Icon">
                        <?php endif; ?>
                    </span>
                    <span class="sps-preview-text"><?php echo esc_html($button_text); ?></span>
                </a>
            </div>
        </div>
        <?php
        error_log('>>>>> render_button_config_section() END for button: ' . $button_id);
    }
    public function settings_page() {
        // Enqueue scripts directly here to ensure they're loaded
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_media();
        
        // DEBUG LOGGING
        error_log('========================================');
        error_log('SPS_Settings::settings_page() CALLED');
        error_log('Time: ' . date('Y-m-d H:i:s'));
        error_log('File: ' . __FILE__);
        error_log('Line: ' . __LINE__);
        error_log('Method exists render_button_settings_section: ' . (method_exists($this, 'render_button_settings_section') ? 'YES' : 'NO'));
        error_log('Method exists render_button_config_section: ' . (method_exists($this, 'render_button_config_section') ? 'YES' : 'NO'));
        error_log('========================================');
        
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'sps_settings-options')) {
            error_log('SPS: Saving settings...');
            $this->save_settings();
        }
        
        ?>
        <div class="wrap">
            <!-- DEBUG: Page is rendering -->
            <div style="background: #0066ff; color: #fff; padding: 20px; margin: 20px 0; border: 3px solid #000; font-size: 16px; font-weight: bold;">
                🔵 BLUE BOX: settings_page() is executing! Time: <?php echo date('H:i:s'); ?>
            </div>
            
            <h1><?php _e('Simple Product Showcase Settings', 'simple-product-showcase'); ?></h1>
            
            <div class="notice notice-info">
                <p><strong>📚 Enhanced Documentation Available!</strong> Scroll down to see the comprehensive shortcode documentation with detailed parameters, examples, and usage instructions. <em>Last updated: <?php echo date('Y-m-d H:i:s'); ?></em></p>
            </div>
            
            <?php $this->display_admin_notices(); ?>
            
            <form method="post" action="">
                <?php
                wp_nonce_field('sps_settings-options');
                // NOTE: We don't use settings_fields() because we're doing manual rendering
                // settings_fields('sps_settings_group'); // REMOVED - causes old fields to appear
                
                // Render NEW Button Settings Section (replaces old WhatsApp-only fields)
                $this->render_button_settings_section();
                
                // Detail Page Settings
                ?>
                <h2><?php _e('Detail Page Settings', 'simple-product-showcase'); ?></h2>
                <p><?php _e('Configure how product detail pages are displayed when users click the \'Detail\' button.', 'simple-product-showcase'); ?></p>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <?php
                        // Detail Page Mode
                        $detail_mode = get_option('sps_detail_page_mode', 'default');
                        ?>
                        <tr>
                            <th scope="row"><label for="sps_detail_page_mode"><?php _e('Detail Page Mode', 'simple-product-showcase'); ?></label></th>
                            <td>
                                <select name="sps_detail_page_mode" id="sps_detail_page_mode">
                                    <option value="default" <?php selected($detail_mode, 'default'); ?>><?php _e('Default Template', 'simple-product-showcase'); ?></option>
                                    <option value="custom" <?php selected($detail_mode, 'custom'); ?>><?php _e('Custom Page with Shortcodes', 'simple-product-showcase'); ?></option>
                                </select>
                                <p class="description"><?php _e('Choose how product detail pages are displayed: Default: Uses the built-in single product template with all information. Custom: Redirects to a custom page where you can use shortcodes.', 'simple-product-showcase'); ?></p>
                            </td>
                        </tr>
                        
                        <?php
                        // Custom Detail Page
                        $custom_page = get_option('sps_custom_detail_page', 0);
                        ?>
                        <tr>
                            <th scope="row"><label for="sps_custom_detail_page"><?php _e('Custom Detail Page', 'simple-product-showcase'); ?></label></th>
                            <td>
                                <?php
                                wp_dropdown_pages(array(
                                    'name' => 'sps_custom_detail_page',
                                    'id' => 'sps_custom_detail_page',
                                    'selected' => $custom_page,
                                    'show_option_none' => __('Select a page...', 'simple-product-showcase'),
                                    'option_none_value' => 0
                                ));
                                ?>
                                <p class="description"><?php _e('Select the page where you want to display product details using shortcodes. This page should contain shortcodes like [sps_detail_products section="title"].', 'simple-product-showcase'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php
                error_log('SPS: Detail Page Settings rendered manually');
                
                submit_button();
                ?>
            </form>
            
            <div class="sps-settings-info">
                <h2><?php _e('Plugin Information', 'simple-product-showcase'); ?> <small style="color: #666; font-size: 12px;">(Enhanced Documentation - <?php echo date('Y-m-d H:i:s'); ?>)</small></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Plugin Version', 'simple-product-showcase'); ?></th>
                        <td><?php echo SPS_PLUGIN_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Shortcode', 'simple-product-showcase'); ?></th>
                        <td>
                            <h4><?php _e('Basic Usage', 'simple-product-showcase'); ?></h4>
                            <code>[sps_products]</code>
                            <p class="description"><?php _e('Display all products in a responsive grid layout.', 'simple-product-showcase'); ?></p>
                            
                            <h4><?php _e('All Available Parameters', 'simple-product-showcase'); ?> <span style="color: #0073aa; font-size: 12px;">(NEW - <?php echo date('H:i:s'); ?>)</span></h4>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #e9ecef;">
                                            <th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;"><?php _e('Parameter', 'simple-product-showcase'); ?></th>
                                            <th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;"><?php _e('Description', 'simple-product-showcase'); ?></th>
                                            <th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;"><?php _e('Default', 'simple-product-showcase'); ?></th>
                                            <th style="padding: 8px; border: 1px solid #dee2e6; text-align: left;"><?php _e('Example', 'simple-product-showcase'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>columns</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Number of columns in the grid (1-6)', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">3</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>columns="4"</code></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>category</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Filter by product category slug', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">-</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>category="electronics"</code></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>limit</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Maximum number of products to display', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">-1 (all)</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>limit="6"</code></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>orderby</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Sort products by: title, date, menu_order, price', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">date</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>orderby="title"</code></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>order</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Sort order: ASC or DESC', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">DESC</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>order="ASC"</code></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>show_price</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Show product price: true or false', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">true</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>show_price="false"</code></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>show_description</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Show product description: true or false', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">true</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>show_description="false"</code></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>show_whatsapp</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Show WhatsApp contact button: true or false', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">true</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>show_whatsapp="false"</code></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>show_gallery</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Show product gallery images: true or false', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">true</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>show_gallery="false"</code></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>gallery_style</code></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><?php _e('Gallery display style: grid, slider, or carousel', 'simple-product-showcase'); ?></td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;">grid</td>
                                            <td style="padding: 8px; border: 1px solid #dee2e6;"><code>gallery_style="slider"</code></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <h4><?php _e('Usage Examples', 'simple-product-showcase'); ?></h4>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0;">
                                <p><strong><?php _e('Basic Grid (3 columns):', 'simple-product-showcase'); ?></strong></p>
                            <code>[sps_products]</code>
                                
                                <p><strong><?php _e('4-Column Grid with 8 Products:', 'simple-product-showcase'); ?></strong></p>
                                <code>[sps_products columns="4" limit="8"]</code>
                                
                                <p><strong><?php _e('Electronics Category Only:', 'simple-product-showcase'); ?></strong></p>
                                <code>[sps_products category="electronics" columns="2"]</code>
                                
                                <p><strong><?php _e('Alphabetical Order, No Price:', 'simple-product-showcase'); ?></strong></p>
                                <code>[sps_products orderby="title" order="ASC" show_price="false"]</code>
                                
                                <p><strong><?php _e('Gallery Slider, No WhatsApp:', 'simple-product-showcase'); ?></strong></p>
                                <code>[sps_products gallery_style="slider" show_whatsapp="false"]</code>
                                
                                <p><strong><?php _e('Minimal Display (Image + Title Only):', 'simple-product-showcase'); ?></strong></p>
                                <code>[sps_products show_price="false" show_description="false" show_whatsapp="false" show_gallery="false"]</code>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Product Pages', 'simple-product-showcase'); ?></th>
                        <td>
                            <p><?php _e('Individual product pages are automatically available at:', 'simple-product-showcase'); ?></p>
                            <code><?php echo home_url('/product/product-name/'); ?></code>
                            <p class="description"><?php _e('Each product has its own dedicated page with full details, gallery, and WhatsApp contact.', 'simple-product-showcase'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Gallery Images', 'simple-product-showcase'); ?></th>
                        <td>
                            <p><?php _e('Each product can have up to 5 additional gallery images:', 'simple-product-showcase'); ?></p>
                            <ul>
                                <li><?php _e('Access via: Products → Edit Product → Product Gallery meta box', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Images are stored as attachment IDs in post meta', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Displayed automatically on product pages and in shortcodes', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Supports different display styles: grid, slider, carousel', 'simple-product-showcase'); ?></li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('WhatsApp Integration', 'simple-product-showcase'); ?></th>
                        <td>
                            <p><?php _e('Automatic WhatsApp contact buttons on all products:', 'simple-product-showcase'); ?></p>
                            <ul>
                                <li><?php _e('Uses the WhatsApp number configured above', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Pre-filled message with product link and name', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Available placeholders: {product_link}, {product_name}', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Customizable message per product', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Can be disabled per shortcode with show_whatsapp="false"', 'simple-product-showcase'); ?></li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Frontend Display', 'simple-product-showcase'); ?></th>
                        <td>
                            <p><?php _e('How to use the shortcode on your website:', 'simple-product-showcase'); ?></p>
                            <ol>
                                <li><?php _e('Go to any page or post editor', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Add the shortcode: [sps_products]', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Customize with parameters as needed', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Preview or publish to see the product grid', 'simple-product-showcase'); ?></li>
                            </ol>
                            <p><strong><?php _e('Pro Tips:', 'simple-product-showcase'); ?></strong></p>
                            <ul>
                                <li><?php _e('Use different shortcodes on different pages for variety', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Combine with categories to create filtered product sections', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Test different column layouts for your theme', 'simple-product-showcase'); ?></li>
                                <li><?php _e('Use limit parameter to create "Featured Products" sections', 'simple-product-showcase'); ?></li>
                            </ul>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <style>
        /* Enhanced Settings Documentation - Updated: <?php echo date('Y-m-d H:i:s'); ?> - Cache Bust: <?php echo uniqid(); ?> */
        .sps-settings-info {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .sps-settings-info h2 {
            margin-top: 0;
            color: #23282d;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        .sps-settings-info h4 {
            color: #23282d;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .sps-settings-info code {
            background: #fff;
            padding: 2px 6px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-family: Consolas, Monaco, monospace;
            font-size: 13px;
        }
        .sps-settings-info table {
            font-size: 13px;
        }
        .sps-settings-info table th {
            font-weight: 600;
            color: #23282d;
        }
        .sps-settings-info ul, .sps-settings-info ol {
            margin-left: 20px;
        }
        .sps-settings-info li {
            margin-bottom: 5px;
        }
        @media (max-width: 768px) {
            .sps-settings-info table {
                font-size: 11px;
            }
            .sps-settings-info table th,
            .sps-settings-info table td {
                padding: 6px 4px;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Display admin notices
     */
    private function display_admin_notices() {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            echo '<div class="notice notice-warning"><p>';
            printf(
                __('<strong>Warning:</strong> WhatsApp number is not configured. Please set your WhatsApp number in the settings below to enable contact buttons.', 'simple-product-showcase')
            );
            echo '</p></div>';
        }
    }
}
