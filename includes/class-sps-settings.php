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
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Fallback: register settings immediately if admin_init already passed
        if (did_action('admin_init')) {
            $this->register_settings();
        }
    }
    
    /**
     * Tambahkan menu admin
     */
    public function add_admin_menu() {
        // Add Settings submenu under the Products menu (from custom post type)
        add_submenu_page(
            'edit.php?post_type=sps_product',
            __('Settings', 'simple-product-showcase'),
            __('Settings', 'simple-product-showcase'),
            'manage_options',
            'sps-settings',
            array($this, 'settings_page')
        );
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
            'sps_whatsapp_section',
            __('WhatsApp Integration', 'simple-product-showcase'),
            array($this, 'whatsapp_section_callback'),
            'sps-settings'
        );
        
        add_settings_section(
            'sps_detail_page_section',
            __('Detail Page Settings', 'simple-product-showcase'),
            array($this, 'detail_page_section_callback'),
            'sps-settings'
        );
        
        // Add settings fields
        add_settings_field(
            'sps_whatsapp_number',
            __('WhatsApp Number', 'simple-product-showcase'),
            array($this, 'whatsapp_number_field_callback'),
            'sps-settings',
            'sps_whatsapp_section'
        );
        
        add_settings_field(
            'sps_whatsapp_message',
            __('Default WhatsApp Message', 'simple-product-showcase'),
            array($this, 'whatsapp_message_field_callback'),
            'sps-settings',
            'sps_whatsapp_section'
        );
        
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
     * Sanitize WhatsApp number
     */
    public function sanitize_whatsapp_number($value) {
        // Remove all non-numeric characters except +
        $value = preg_replace('/[^0-9+]/', '', $value);
        
        // If it doesn't start with +, add country code (assume Indonesia +62)
        if (!empty($value) && !str_starts_with($value, '+')) {
            // Remove leading 0 if present
            if (str_starts_with($value, '0')) {
                $value = substr($value, 1);
            }
            $value = '+62' . $value;
        }
        
        return $value;
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
     * WhatsApp number field callback
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
                    return add_query_arg('product_id', $product_id, $page_url);
                }
            }
        }
        
        // Default: use single product permalink
        return get_permalink($product_id);
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'sps_settings-options')) {
            $this->save_settings();
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Simple Product Showcase Settings', 'simple-product-showcase'); ?></h1>
            
            <div class="notice notice-info">
                <p><strong>📚 Enhanced Documentation Available!</strong> Scroll down to see the comprehensive shortcode documentation with detailed parameters, examples, and usage instructions. <em>Last updated: <?php echo date('Y-m-d H:i:s'); ?></em></p>
            </div>
            
            <?php $this->display_admin_notices(); ?>
            
            <form method="post" action="">
                <?php
                wp_nonce_field('sps_settings-options');
                settings_fields('sps_settings_group');
                do_settings_sections('sps-settings');
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
     * Save settings
     */
    private function save_settings() {
        if (isset($_POST['sps_whatsapp_number'])) {
            $whatsapp_number = $this->sanitize_whatsapp_number($_POST['sps_whatsapp_number']);
            update_option('sps_whatsapp_number', $whatsapp_number);
        }
        
        if (isset($_POST['sps_whatsapp_message'])) {
            $whatsapp_message = sanitize_textarea_field($_POST['sps_whatsapp_message']);
            update_option('sps_whatsapp_message', $whatsapp_message);
        }
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'simple-product-showcase') . '</p></div>';
        });
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
