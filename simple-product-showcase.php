<?php
/**
 * Plugin Name: Simple Product Showcase
 * Plugin URI: https://github.com/teguh02/simple-product-showcase-wp-plugin
 * Description: Plugin WordPress ringan untuk menampilkan produk dengan integrasi WhatsApp tanpa fitur checkout, cart, atau pembayaran.
 * Version: 1.5.6
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
define('SPS_PLUGIN_VERSION', '1.5.6');

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
        
        // Register AJAX actions
        add_action('wp_ajax_get_gallery_image', array($this, 'ajax_get_gallery_image'));
        add_action('wp_ajax_nopriv_get_gallery_image', array($this, 'ajax_get_gallery_image'));
    }
    
    /**
     * Load file dependencies
     */
    private function load_dependencies() {
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-init.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-cpt.php';
        // require_once SPS_PLUGIN_PATH . 'includes/class-sps-settings.php'; // BACKUP: Uncomment if Configuration page has issues
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-configuration.php'; // NEW: Button Configuration Page
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-shortcodes.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-frontend.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-duplicate.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-persistent.php';
        require_once SPS_PLUGIN_PATH . 'includes/class-sps-metabox.php';
        
        // DEBUG: Removed debug-settings.php - no longer needed
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
        // if (!$settings_exists) {
        //     add_submenu_page(
        //         'edit.php?post_type=sps_product',
        //         'Settings',
        //         'Settings',
        //         'manage_options',
        //         'sps-settings',
        //         array($this, 'fallback_settings_page')
        //     );
        // }
        
        // REMOVED: Documentation menu - refer to SHORTCODE-DOCUMENTATION.md file instead
        /*
        add_submenu_page(
            'edit.php?post_type=sps_product',
            'Documentation',
            'Documentation',
            'manage_options',
            'sps-documentation',
            array($this, 'documentation_page')
        );
        */
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
        </div>
        <?php
    }
    
    /**
     * WhatsApp button text field callback
     */
    public function whatsapp_button_text_callback() {
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
     * Documentation page
     */
    public function documentation_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Simple Product Showcase - Documentation', 'simple-product-showcase'); ?></h1>
            
            <div class="notice notice-info">
                <p><strong>📚 Complete Shortcode Documentation</strong> - This page contains comprehensive documentation for all available shortcodes, parameters, and usage examples. <em>Last updated: <?php echo date('Y-m-d H:i:s'); ?></em></p>
            </div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin: 20px 0;">
                
                <h2>🚀 Available Shortcodes</h2>
                
                <h3>1. Product Grid/List Display</h3>
                <p>The main shortcode to display products:</p>
                <code>[sps_products]</code>
                
                <h3>2. Product Detail Display</h3>
                <p>New shortcode for displaying individual product details:</p>
                <code>[sps_detail_products section="title"]</code>
                
                <hr style="margin: 30px 0;">
                
                <h2>📋 Product Grid Shortcode Parameters</h2>
                <p>The <code>[sps_products]</code> shortcode supports several attributes to customize the display of your products.</p>
                
                <table class="widefat" style="margin: 20px 0;">
                    <thead>
                        <tr>
                            <th style="padding: 10px; background: #f1f1f1;">Parameter</th>
                            <th style="padding: 10px; background: #f1f1f1;">Description</th>
                            <th style="padding: 10px; background: #f1f1f1;">Default</th>
                            <th style="padding: 10px; background: #f1f1f1;">Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 10px;"><code>columns</code></td>
                            <td style="padding: 10px;">Number of columns in the grid (1-6)</td>
                            <td style="padding: 10px;">3</td>
                            <td style="padding: 10px;"><code>columns="4"</code></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;"><code>category</code></td>
                            <td style="padding: 10px;">Filter by product category slug</td>
                            <td style="padding: 10px;">-</td>
                            <td style="padding: 10px;"><code>category="electronics"</code></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;"><code>limit</code></td>
                            <td style="padding: 10px;">Limit number of products (-1 for all)</td>
                            <td style="padding: 10px;">-1</td>
                            <td style="padding: 10px;"><code>limit="10"</code></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;"><code>orderby</code></td>
                            <td style="padding: 10px;">Order by: title, date, menu_order, price</td>
                            <td style="padding: 10px;">date</td>
                            <td style="padding: 10px;"><code>orderby="title"</code></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;"><code>order</code></td>
                            <td style="padding: 10px;">Sort order: ASC or DESC</td>
                            <td style="padding: 10px;">DESC</td>
                            <td style="padding: 10px;"><code>order="ASC"</code></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;"><code>show_price</code></td>
                            <td style="padding: 10px;">Show product price: true or false</td>
                            <td style="padding: 10px;">true</td>
                            <td style="padding: 10px;"><code>show_price="false"</code></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;"><code>show_description</code></td>
                            <td style="padding: 10px;">Show product description: true or false</td>
                            <td style="padding: 10px;">true</td>
                            <td style="padding: 10px;"><code>show_description="false"</code></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;"><code>show_whatsapp</code></td>
                            <td style="padding: 10px;">Show WhatsApp contact button: true or false</td>
                            <td style="padding: 10px;">true</td>
                            <td style="padding: 10px;"><code>show_whatsapp="false"</code></td>
                        </tr>
                    </tbody>
                </table>
                
                <hr style="margin: 30px 0;">
                
                <h2>📋 Product Detail Shortcode Parameters</h2>
                <p>The <code>[sps_detail_products]</code> shortcode automatically detects the current product from the URL and displays specific sections.</p>
                
                <table class="widefat" style="margin: 20px 0;">
                    <thead>
                        <tr>
                            <th style="padding: 10px; background: #f1f1f1;">Parameter</th>
                            <th style="padding: 10px; background: #f1f1f1;">Description</th>
                            <th style="padding: 10px; background: #f1f1f1;">Options</th>
                            <th style="padding: 10px; background: #f1f1f1;">Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 10px;"><code>section</code></td>
                            <td style="padding: 10px;">Which part of the product to display</td>
                            <td style="padding: 10px;">title, image, description, gallery, whatsapp</td>
                            <td style="padding: 10px;"><code>section="title"</code></td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;"><code>style</code></td>
                            <td style="padding: 10px;">Display style based on section:<br>• Title: h1, h2, h3, h4, h5<br>• Gallery: grid, slider, carousel</td>
                            <td style="padding: 10px;">Title: h1, h2, h3, h4, h5<br>Gallery: grid, slider, carousel</td>
                            <td style="padding: 10px;"><code>style="h2"</code> or <code>style="slider"</code></td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Available Sections:</h3>
                <ul>
                    <li><strong>title</strong> - Display product title as heading (supports h1, h2, h3, h4, h5 styles)</li>
                    <li><strong>image</strong> - Display main product image (featured image)</li>
                    <li><strong>description</strong> - Display full product description/content</li>
                    <li><strong>gallery</strong> - Display up to 5 gallery images (supports grid, slider, carousel styles)</li>
                    <li><strong>whatsapp</strong> - Display WhatsApp contact button</li>
                </ul>
                
                <h3>Title Styles:</h3>
                <ul>
                    <li><strong>h1</strong> - Display as H1 heading (default)</li>
                    <li><strong>h2</strong> - Display as H2 heading</li>
                    <li><strong>h3</strong> - Display as H3 heading</li>
                    <li><strong>h4</strong> - Display as H4 heading</li>
                    <li><strong>h5</strong> - Display as H5 heading</li>
                </ul>
                
                <h3>Gallery Styles:</h3>
                <ul>
                    <li><strong>grid</strong> - Display images in a responsive grid (default)</li>
                    <li><strong>slider</strong> - Display images in a slideshow with navigation</li>
                    <li><strong>carousel</strong> - Display images in a horizontal scrolling carousel</li>
                </ul>
                
                <hr style="margin: 30px 0;">
                
                <h2>💡 Usage Examples</h2>
                
                <h3>Product Grid Examples</h3>
                
                <h4>Basic Grid (3 columns)</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_products]</code></pre>
                
                <h4>2 Columns, 6 Products</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_products columns="2" limit="6"]</code></pre>
                
                <h4>Filter by Category</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_products category="electronics" columns="4"]</code></pre>
                
                <h4>Custom Ordering</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_products orderby="title" order="ASC"]</code></pre>
                
                <h4>Featured Products Section</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_products limit="4" orderby="menu_order" columns="2"]</code></pre>
                
                <h3>Product Detail Examples</h3>
                
                <h4>Display Product Title (H1)</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="title"]</code></pre>
                
                <h4>Display Product Title (H2)</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="title" style="h2"]</code></pre>
                
                <h4>Display Product Title (H3)</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="title" style="h3"]</code></pre>
                
                <h4>Display Main Product Image</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="image"]</code></pre>
                
                <h4>Display Product Description</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="description"]</code></pre>
                
                <h4>Display Gallery in Grid Layout</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="gallery" style="grid"]</code></pre>
                
                <h4>Display Gallery as Slider</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="gallery" style="slider"]</code></pre>
                
                <h4>Display Gallery as Carousel</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="gallery" style="carousel"]</code></pre>
                
                <h4>Display WhatsApp Contact Button</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="whatsapp"]</code></pre>
                
                <h3>Complete Product Detail Page Layout</h3>
                <p>For a complete product detail page, use multiple shortcodes:</p>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="title" style="h2"]
[sps_detail_products section="image"]
[sps_detail_products section="description"]
[sps_detail_products section="gallery" style="slider"]
[sps_detail_products section="whatsapp"]</code></pre>
                
                <h4>Alternative Layout with H3 Title and Carousel</h4>
                <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>[sps_detail_products section="title" style="h3"]
[sps_detail_products section="image"]
[sps_detail_products section="gallery" style="carousel"]
[sps_detail_products section="description"]
[sps_detail_products section="whatsapp"]</code></pre>
                
                <hr style="margin: 30px 0;">
                
                <h2>🔧 How Product Detection Works</h2>
                <p>The <code>[sps_detail_products]</code> shortcode automatically detects the current product using the <code>product_id</code> parameter from the URL:</p>
                <ul>
                    <li><strong>Product ID Detection</strong> (<code>/show-product/?product_id=123</code>) - Detects by product ID parameter</li>
                    <li><strong>WordPress Permalinks</strong> - Works with any permalink structure (Post name, Numeric, etc.)</li>
                    <li><strong>Automatic Fallback</strong> - If no product_id is found, displays "No product found" message</li>
                </ul>
                
                <h4>Example URLs:</h4>
                <ul>
                    <li><code>/show-product/?product_id=28</code> - Will display product with ID 28</li>
                    <li><code>/product-detail/?product_id=15</code> - Will display product with ID 15</li>
                </ul>
                
                <p>This approach ensures reliable product detection regardless of WordPress permalink settings.</p>
                
                <hr style="margin: 30px 0;">
                
                <h2>📱 Responsive Design</h2>
                <p>Both shortcodes are fully responsive and will automatically adapt to different screen sizes:</p>
                <ul>
                    <li><strong>Mobile</strong>: <code>columns="1"</code> or <code>columns="2"</code></li>
                    <li><strong>Tablet</strong>: <code>columns="2"</code> or <code>columns="3"</code></li>
                    <li><strong>Desktop</strong>: <code>columns="3"</code> or <code>columns="4"</code></li>
                </ul>
                
                <hr style="margin: 30px 0;">
                
                <h2>🎨 Custom Styling</h2>
                <p>The shortcodes generate HTML with CSS classes that you can customize:</p>
                <ul>
                    <li><code>.sps-products-grid</code> - Main container</li>
                    <li><code>.sps-product-item</code> - Individual product</li>
                    <li><code>.sps-product-title</code> - Product title</li>
                    <li><code>.sps-detail-button</code> - Detail button</li>
                    <li><code>.sps-product-gallery</code> - Gallery container</li>
                    <li><code>.sps-gallery-slider</code> - Slider container</li>
                    <li><code>.sps-gallery-carousel</code> - Carousel container</li>
                </ul>
                
                <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-left: 4px solid #0073aa; border-radius: 4px;">
                    <h3>💡 Pro Tips</h3>
                    <ul>
                        <li>Use <code>limit</code> parameter to control the number of products displayed for better performance</li>
                        <li>Combine with <code>orderby="menu_order"</code> for custom ordering</li>
                        <li>Use <code>show_description="false"</code> if you don't need descriptions for faster loading</li>
                        <li>Test different gallery styles to find what works best for your design</li>
                        <li>Use the detail page settings to choose between default and custom layouts</li>
                    </ul>
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
            'orderby' => 'title',
            'order' => 'ASC',
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
        
        // Add category filter if specified in shortcode attributes or URL parameter
        $category_filter = '';
        
        // Check URL parameter first
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $category_filter = sanitize_text_field($_GET['category']);
        }
        
        // Override with shortcode attribute if provided
        if (!empty($atts['category'])) {
            $category_filter = $atts['category'];
        }
        
        // Apply category filter
        if (!empty($category_filter)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'sps_product_category',
                    'field' => 'slug',
                    'terms' => $category_filter
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
        /* Tablet specific improvements */
        @media (max-width: 1024px) and (min-width: 769px) {
            .sps-products-grid {
                grid-template-columns: repeat(<?php echo min($columns, 3); ?>, 1fr);
                gap: 20px;
            }
            .sps-product-item {
                padding: 20px;
                min-height: 280px;
            }
            .sps-product-title {
                font-size: 15px;
                line-height: 1.4;
                margin-bottom: 15px;
                min-height: 42px;
            }
            .sps-product-actions {
                margin-top: 15px;
            }
            .sps-detail-button {
                padding: 12px 20px;
                font-size: 13px;
                min-width: 90px;
            }
        }
        
        /* Smaller tablet improvements */
        @media (max-width: 992px) and (min-width: 769px) {
            .sps-products-grid {
                grid-template-columns: repeat(<?php echo min($columns, 2); ?>, 1fr);
                gap: 25px;
            }
            .sps-product-item {
                padding: 18px;
                min-height: 260px;
            }
            .sps-product-title {
                font-size: 14px;
                line-height: 1.3;
                margin-bottom: 12px;
                min-height: 36px;
            }
            .sps-product-actions {
                margin-top: 12px;
            }
            .sps-detail-button {
                padding: 10px 18px;
                font-size: 12px;
                min-width: 85px;
            }
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
                                $detail_url = SPS_Configuration::get_product_detail_url(get_the_ID());
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
                // Validasi style untuk title
                $valid_styles = array('h1', 'h2', 'h3', 'h4', 'h5');
                $heading_tag = in_array($atts['style'], $valid_styles) ? $atts['style'] : 'h1';
                return '<' . $heading_tag . ' class="sps-product-detail-title">' . esc_html($product->post_title) . '</' . $heading_tag . '>';
                
            case 'image':
                // Default: show thumbnail or first available image (PHP loads this initially)
                if (has_post_thumbnail($product->ID)) {
                    return '<div class="sps-product-detail-image" id="sps-main-image-container">' . get_the_post_thumbnail($product->ID, 'large', array('class' => 'sps-main-image')) . '</div>';
                }
                return '<div class="sps-product-detail-image" id="sps-main-image-container"><p>No image available.</p></div>';
                
            case 'description':
                $content = apply_filters('the_content', $product->post_content);
                return '<div class="sps-product-detail-description">' . $content . '</div>';
                
            case 'gallery':
                return $this->render_gallery_fallback($product, $atts['style']);
                
            case 'whatsapp':
                return $this->render_whatsapp_fallback($product);
                
            case 'button':
                return $this->render_all_buttons_fallback($product);
                
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

        // Try to get from product parameter (for custom pages) - SEO friendly
        $product_slug = isset($_GET['product']) ? sanitize_text_field($_GET['product']) : '';
        if ($product_slug) {
            $product = get_page_by_path($product_slug, OBJECT, 'sps_product');
            if ($product) {
                return $product;
            }
        }

        // Try to get from product_id parameter (for backward compatibility)
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
        
        // Add thumbnail as first image
        $thumbnail_id = get_post_thumbnail_id($product->ID);
        if ($thumbnail_id) {
            $gallery_images[] = $thumbnail_id;
        }
        
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
        
        // If only thumbnail exists (1 image) or thumbnail + 1 gallery (2 images), don't show gallery
        if (count($gallery_images) <= 2) {
            return '';
        }
        
        ob_start();
        ?>
        <style>
        .sps-product-detail-image {
            margin: 20px 0;
            text-align: center;
        }
        .sps-main-image {
            width: 100%;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: block;
            margin: 0 auto;
        }
        .sps-product-gallery {
            margin: 20px 0;
        }
        .sps-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        /* Mobile and Tablet Slider Layout */
        @media (max-width: 1024px) {
            .sps-product-gallery {
                position: relative;
            }
            
            .sps-gallery-grid {
                display: flex;
                overflow-x: auto;
                gap: 15px;
                padding: 10px 0;
                scroll-behavior: smooth;
                -webkit-overflow-scrolling: touch;
            }
            
            .sps-gallery-item {
                flex: 0 0 auto !important;
                width: 100px !important;
                min-width: 100px !important;
            }
            
            .sps-gallery-item .sps-gallery-image {
                width: 100px !important;
                height: 100px !important;
                max-width: 100px !important;
                max-height: 100px !important;
            }
            
        }

        @media (max-width: 768px) {
            .sps-gallery-item {
                width: 80px !important;
                min-width: 80px !important;
            }
            
            .sps-gallery-item .sps-gallery-image {
                width: 80px !important;
                height: 80px !important;
                max-width: 80px !important;
                max-height: 80px !important;
            }
        }

        /* Scrollbar styling for mobile gallery */
        @media (max-width: 1024px) {
            .sps-gallery-grid::-webkit-scrollbar {
                height: 6px;
            }
            
            .sps-gallery-grid::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 3px;
            }
            
            .sps-gallery-grid::-webkit-scrollbar-thumb {
                background: #0073aa;
                border-radius: 3px;
            }
            
            .sps-gallery-grid::-webkit-scrollbar-thumb:hover {
                background: #005a87;
            }
        }
        .sps-gallery-carousel {
            display: flex;
            overflow: hidden;
            gap: 15px;
            padding: 10px 0;
            transition: transform 0.3s ease;
        }
        .sps-gallery-item {
            flex: 0 0 calc(33.333% - 10px);
            min-width: 200px;
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
        
        /* Mobile and Tablet - Larger Navigation Arrows */
        @media (max-width: 1024px) {
            .sps-gallery-controls {
                position: relative;
                margin: 20px 0;
                padding: 0 20px;
            }
            
            .sps-gallery-prev,
            .sps-gallery-next {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(0, 115, 170, 0.9);
                color: white;
                border: none;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                font-size: 24px;
                font-weight: bold;
                cursor: pointer;
                z-index: 10;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                transition: all 0.3s ease;
                margin: 0;
                padding: 0;
            }
            
            .sps-gallery-prev {
                left: 10px;
            }
            
            .sps-gallery-next {
                right: 10px;
            }
            
            .sps-gallery-prev:hover,
            .sps-gallery-next:hover {
                background: rgba(0, 115, 170, 1);
                transform: translateY(-50%) scale(1.1);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
            }
            
            .sps-gallery-prev:active,
            .sps-gallery-next:active {
                transform: translateY(-50%) scale(0.95);
            }
        }

        @media (max-width: 768px) {
            .sps-gallery-prev,
            .sps-gallery-next {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
            
            .sps-gallery-prev {
                left: 5px;
            }
            
            .sps-gallery-next {
                right: 5px;
            }
        }
        .sps-whatsapp-icon {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            vertical-align: middle;
            display: inline-block;
            filter: invert(1); /* Invert black icon to white */
        }
        .sps-whatsapp-detail-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #25D366; /* WhatsApp green */
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .sps-whatsapp-detail-button:hover {
            background: #128C7E; /* Darker green on hover */
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }
        .sps-gallery-image-link {
            display: block;
            text-decoration: none;
            transition: transform 0.3s ease, opacity 0.3s ease;
            border: 3px solid transparent;
            border-radius: 8px;
        }
        .sps-gallery-image-link:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        .sps-gallery-image-link.active {
            border-color: #0073aa !important;
            box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.2);
        }
        .sps-gallery-image-link .sps-gallery-image {
            display: block;
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        </style>
        <div class="sps-product-gallery sps-gallery-<?php echo esc_attr($style); ?>" data-product-id="<?php echo esc_attr($product->ID); ?>">
            <?php 
            foreach ($gallery_images as $index => $image_id) : 
                $thumbnail_number = $index + 1; // Start from 1
                $active_class = ($thumbnail_number == 1) ? ' active' : ''; // First image is active by default
            ?>
                <?php if ($style === 'slider') : ?>
                    <div class="sps-gallery-slide">
                        <a href="#thumbnail=<?php echo $thumbnail_number; ?>" class="sps-gallery-image-link<?php echo $active_class; ?>" data-thumbnail="<?php echo $thumbnail_number; ?>" data-image-id="<?php echo esc_attr($image_id); ?>">
                            <?php echo wp_get_attachment_image($image_id, 'large', false, array('class' => 'sps-gallery-image')); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <div class="sps-gallery-item">
                        <a href="#thumbnail=<?php echo $thumbnail_number; ?>" class="sps-gallery-image-link<?php echo $active_class; ?>" data-thumbnail="<?php echo $thumbnail_number; ?>" data-image-id="<?php echo esc_attr($image_id); ?>">
                            <?php echo wp_get_attachment_image($image_id, 'medium', false, array('class' => 'sps-gallery-image')); ?>
                        </a>
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
            if (totalSlides > 0) {
                slides[currentSlide].style.display = 'none';
                currentSlide = (currentSlide + 1) % totalSlides;
                slides[currentSlide].style.display = 'block';
            }
        }
        
        function spsGalleryPrev() {
            if (totalSlides > 0) {
                slides[currentSlide].style.display = 'none';
                currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
                slides[currentSlide].style.display = 'block';
            }
        }
        
        // Initialize slider
        document.addEventListener('DOMContentLoaded', function() {
            slides.forEach((slide, index) => {
                slide.style.display = index === 0 ? 'block' : 'none';
            });
        });
        </script>
        <?php elseif ($style === 'carousel') : ?>
        <div class="sps-gallery-controls">
            <button class="sps-gallery-prev" onclick="spsCarouselPrev()">‹</button>
            <button class="sps-gallery-next" onclick="spsCarouselNext()">›</button>
        </div>
        <script>
        let currentCarouselIndex = 0;
        const carouselContainer = document.querySelector('.sps-gallery-carousel');
        const carouselItems = document.querySelectorAll('.sps-gallery-item');
        const itemsToShow = 3; // Show 3 items at once
        const totalItems = carouselItems.length;
        
        function spsCarouselNext() {
            if (totalItems > itemsToShow) {
                currentCarouselIndex = Math.min(currentCarouselIndex + 1, totalItems - itemsToShow);
                updateCarouselPosition();
            }
        }
        
        function spsCarouselPrev() {
            if (totalItems > itemsToShow) {
                currentCarouselIndex = Math.max(currentCarouselIndex - 1, 0);
                updateCarouselPosition();
            }
        }
        
        function updateCarouselPosition() {
            if (carouselContainer && totalItems > 0) {
                const translateX = -(currentCarouselIndex * (100 / itemsToShow));
                carouselContainer.style.transform = `translateX(${translateX}%)`;
            }
        }
        
        // Initialize carousel
        document.addEventListener('DOMContentLoaded', function() {
            updateCarouselPosition();
        });
        </script>
        <?php endif; ?>
        
        <script>
        // AJAX Gallery Image Changer (Fallback)
        document.addEventListener('DOMContentLoaded', function() {
            const galleryLinks = document.querySelectorAll('.sps-gallery-image-link');
            const mainImageContainer = document.getElementById('sps-main-image-container');
            
            // Check for hash parameter on page load
            checkHashParameter();
            
            // Listen for hash changes
            window.addEventListener('hashchange', checkHashParameter);
            
            // Add click event listeners to gallery images
            galleryLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const thumbnailNumber = this.getAttribute('data-thumbnail');
                    const imageId = this.getAttribute('data-image-id');
                    
                    // Update URL hash
                    window.location.hash = 'thumbnail=' + thumbnailNumber;
                    
                    // Update active state
                    updateActiveGalleryImage(thumbnailNumber);
                    
                    // Change main image via AJAX
                    changeMainImage(imageId);
                });
            });
            
            function checkHashParameter() {
                const hash = window.location.hash;
                const thumbnailMatch = hash.match(/thumbnail=(\d+)/);
                
                if (thumbnailMatch) {
                    const thumbnailNumber = parseInt(thumbnailMatch[1]);
                    const galleryLink = document.querySelector(`[data-thumbnail="${thumbnailNumber}"]`);
                    
                    if (galleryLink) {
                        const imageId = galleryLink.getAttribute('data-image-id');
                        
                        // Update active state
                        updateActiveGalleryImage(thumbnailNumber);
                        
                        // Change main image
                        changeMainImage(imageId);
                    }
                } else {
                    // No hash parameter, show first image (default)
                    updateActiveGalleryImage(1);
                    // Main image is already loaded by PHP, no need to change
                }
            }
            
            function updateActiveGalleryImage(thumbnailNumber) {
                // Remove active class from all gallery links
                galleryLinks.forEach(function(link) {
                    link.classList.remove('active');
                });
                
                // Add active class to clicked image
                const activeLink = document.querySelector(`[data-thumbnail="${thumbnailNumber}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            }
            
            function changeMainImage(imageId) {
                if (!mainImageContainer || !imageId) {
                    console.log('Missing mainImageContainer or imageId:', mainImageContainer, imageId);
                    return;
                }
                
                console.log('Changing main image for imageId:', imageId);
                
                // Create AJAX request to get image URL
                const xhr = new XMLHttpRequest();
                const nonce = '<?php echo wp_create_nonce('sps_gallery_image_nonce'); ?>';
                const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=get_gallery_image&image_id=' + imageId + '&nonce=' + nonce;
                
                console.log('AJAX URL:', ajaxUrl);
                
                xhr.open('GET', ajaxUrl, true);
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log('AJAX Response:', xhr.responseText);
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.log('Parsed response:', response);
                            
                            if (response.success && response.data.image_url) {
                                console.log('Updating image with URL:', response.data.image_url);
                                
                                // Find main image element
                                const mainImage = mainImageContainer.querySelector('.sps-main-image');
                                console.log('Found main image element:', mainImage);
                                
                                if (mainImage) {
                                    // Update existing image
                                    mainImage.src = response.data.image_url;
                                    mainImage.alt = response.data.image_alt || '';
                                    // Remove srcset to prevent conflicts
                                    mainImage.removeAttribute('srcset');
                                    mainImage.removeAttribute('sizes');
                                    console.log('Updated existing image src to:', mainImage.src);
                                    console.log('Removed srcset and sizes attributes');
                                } else {
                                    // Create new image if not exists
                                    const newImage = document.createElement('img');
                                    newImage.src = response.data.image_url;
                                    newImage.alt = response.data.image_alt || '';
                                    newImage.className = 'sps-main-image';
                                    newImage.style.width = '100%';
                                    newImage.style.maxWidth = '100%';
                                    newImage.style.height = 'auto';
                                    newImage.style.borderRadius = '8px';
                                    newImage.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
                                    newImage.style.display = 'block';
                                    newImage.style.margin = '0 auto';
                                    
                                    // Clear container and add new image
                                    mainImageContainer.innerHTML = '';
                                    mainImageContainer.appendChild(newImage);
                                    console.log('Created new image element with src:', newImage.src);
                                }
                            } else {
                                console.error('AJAX response failed:', response);
                            }
                        } catch (e) {
                            console.error('Error parsing AJAX response:', e);
                            console.error('Raw response:', xhr.responseText);
                        }
                    } else if (xhr.readyState === 4) {
                        console.error('AJAX request failed with status:', xhr.status);
                    }
                };
                
                xhr.onerror = function() {
                    console.error('AJAX request error');
                };
                
                xhr.send();
            }
        });
        </script>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Normalize WhatsApp number format (fallback)
     * 
     * @param string $number Raw WhatsApp number
     * @return string Normalized number
     */
    private function normalize_whatsapp_number_fallback($number) {
        // Remove all non-numeric characters except +
        $number = preg_replace('/[^0-9+]/', '', $number);
        
        // Handle different formats
        if (strpos($number, '+62') === 0) {
            // Format: +6289655541804 -> 6289655541804
            return substr($number, 1);
        } elseif (strpos($number, '62') === 0) {
            // Format: 6289655541804 -> 6289655541804 (already correct)
            return $number;
        } elseif (strpos($number, '0') === 0) {
            // Format: 089655541804 -> 6289655541804
            return '62' . substr($number, 1);
        } else {
            // If none of the above, assume it needs 62 prefix
            return '62' . $number;
        }
    }
    
    /**
     * Get product detail URL fallback
     */
    private function get_product_detail_url_fallback($product_id) {
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
     * Render WhatsApp fallback
     */
    private function render_whatsapp_fallback($product) {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            return '<p class="sps-whatsapp-error">WhatsApp number not configured.</p>';
        }
        
        // Normalize WhatsApp number format
        $whatsapp_number = $this->normalize_whatsapp_number_fallback($whatsapp_number);
        
        // Get custom message for this product or use global message
        $custom_message = get_post_meta($product->ID, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
        
        $message = !empty($custom_message) ? $custom_message : $global_message;
        
        // Replace placeholders
        $product_url = $this->get_product_detail_url_fallback($product->ID);
        $message = str_replace('{product_link}', $product_url, $message);
        $message = str_replace('{product_name}', $product->post_title, $message);
        
        // URL encode the message
        $encoded_message = urlencode($message);
        
        // Generate WhatsApp URL
        $whatsapp_url = "https://wa.me/{$whatsapp_number}?text={$encoded_message}";
        
        // Get button text from settings
        $button_text = get_option('sps_whatsapp_button_text', 'Tanya Produk Ini');
        
        ob_start();
        ?>
        <style>
        .sps-product-whatsapp {
            margin: 30px 0;
            text-align: center;
        }
        .sps-whatsapp-detail-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #25D366; /* WhatsApp green */
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .sps-whatsapp-detail-button:hover {
            background: #128C7E; /* Darker green on hover */
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }
        .sps-whatsapp-icon {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            vertical-align: middle;
            display: inline-block;
            filter: invert(1); /* Invert black icon to white */
        }
        .sps-whatsapp-error {
            color: #d63638;
            background: #ffeaea;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }
        </style>
        <div class="sps-product-whatsapp">
            <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="sps-whatsapp-detail-button">
                <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/img/whatsapp.png'); ?>" alt="WhatsApp" class="sps-whatsapp-icon" />
                <?php echo esc_html($button_text); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Direct products with filters shortcode handler (fallback)
     */
    public function direct_products_with_filters_shortcode($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'columns' => '3',
            'limit' => '-1',
            'orderby' => 'title',
            'order' => 'ASC',
            'show_price' => 'true',
            'show_description' => 'false',
            'show_whatsapp' => 'true'
        ), $atts, 'sps_products_with_filters');
        
        // Get all categories
        $categories = get_terms(array(
            'taxonomy' => 'sps_product_category',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        // Get current category from URL
        $current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        
        // Get current page URL
        $current_url = remove_query_arg('category');
        
        // Start output
        ob_start();
        ?>
        <style>
        .sps-filter-container {
            margin: 30px 0;
        }
        
        .sps-filter-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            padding: 20px;
            background: transparent;
            border-radius: 8px;
        }
        
        .sps-filter-tab {
            display: inline-block;
            padding: 12px 24px;
            background: #ffffff;
            color: #333333;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            font-size: 14px;
        }
        
        .sps-filter-tab:hover {
            background: #f5f5f5;
            color: #000000;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .sps-filter-tab.active {
            background: #FDB913;
            color: #000000;
            border-color: #FDB913;
            font-weight: 600;
        }
        
        .sps-filter-tab.active:hover {
            background: #E5A711;
            border-color: #E5A711;
        }
        
        .sps-products-grid {
            display: grid;
            grid-template-columns: repeat(<?php echo intval($atts['columns']); ?>, 1fr);
            gap: 30px;
            margin: 20px 0;
            justify-items: center;
        }
        
        .sps-no-category-message {
            text-align: center;
            padding: 60px 20px;
            background: #f9f9f9;
            border-radius: 8px;
            color: #666;
            font-size: 18px;
        }
        
        .sps-no-category-message p {
            margin: 0;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .sps-filter-tabs {
                gap: 10px;
                padding: 15px;
            }
            
            .sps-filter-tab {
                padding: 10px 18px;
                font-size: 13px;
            }
            
            .sps-products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .sps-products-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <div class="sps-filter-container">
            <!-- Category Filter Tabs -->
            <div class="sps-filter-tabs">
                <?php
                if (!empty($categories) && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $category_url = add_query_arg('category', $category->slug, $current_url);
                        $active_class = ($current_category === $category->slug) ? 'active' : '';
                        ?>
                        <a href="<?php echo esc_url($category_url); ?>" 
                           class="sps-filter-tab <?php echo esc_attr($active_class); ?>">
                            <?php echo esc_html($category->name); ?>
                        </a>
                        <?php
                    }
                } else {
                    echo '<p class="sps-no-filters">' . __('No categories available.', 'simple-product-showcase') . '</p>';
                }
                ?>
            </div>
            
            <!-- Products Display -->
            <?php
            if (!empty($current_category)) {
                // Show products when category is selected
                echo do_shortcode('[sps_products category="' . esc_attr($current_category) . '" columns="' . esc_attr($atts['columns']) . '" limit="' . esc_attr($atts['limit']) . '" orderby="' . esc_attr($atts['orderby']) . '" order="' . esc_attr($atts['order']) . '"]');
            } else {
                // Show message to select a category
                echo '<div class="sps-no-category-message"><p>' . __('Please select a category to view products.', 'simple-product-showcase') . '</p></div>';
            }
            ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Direct products with sub category shortcode handler (NEW)
     */
    public function direct_products_sub_category_shortcode($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'columns' => '3',
            'limit' => '-1',
            'orderby' => 'title',
            'order' => 'ASC',
            'show_price' => 'true',
            'show_description' => 'false',
            'show_whatsapp' => 'true'
        ), $atts, 'sps_products_sub_category');
        
        // Get category and sub_category from URL
        $current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $current_sub_category = isset($_GET['sub_category']) ? sanitize_text_field($_GET['sub_category']) : '';
        
        // Get current page URL
        $current_url = remove_query_arg(array('category', 'sub_category'));
        
        // Start output
        ob_start();
        ?>
        <style>
        .sps-sub-category-container {
            margin: 30px 0;
        }
        
        .sps-sub-category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            padding: 20px;
            background: transparent;
            border-radius: 8px;
        }
        
        .sps-sub-category-tab {
            display: inline-block;
            padding: 12px 24px;
            background: #ffffff;
            color: #333333;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            font-size: 14px;
        }
        
        .sps-sub-category-tab:hover {
            background: #f5f5f5;
            color: #000000;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .sps-sub-category-tab.active {
            background: #FDB913;
            color: #000000;
            border-color: #FDB913;
            font-weight: 600;
        }
        
        .sps-sub-category-tab.active:hover {
            background: #E5A711;
            border-color: #E5A711;
        }
        
        .sps-no-category-message {
            text-align: center;
            padding: 60px 20px;
            background: #f9f9f9;
            border-radius: 8px;
            color: #666;
            font-size: 18px;
        }
        
        .sps-no-category-message p {
            margin: 0;
            font-weight: 500;
        }
        
        .sps-no-sub-category-message {
            text-align: center;
            padding: 60px 20px;
            background: #fff9e6;
            border-radius: 8px;
            color: #666;
            font-size: 18px;
            border: 2px dashed #FDB913;
        }
        
        .sps-no-sub-category-message p {
            margin: 0;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .sps-sub-category-tabs {
                gap: 10px;
                padding: 15px;
            }
            
            .sps-sub-category-tab {
                padding: 10px 18px;
                font-size: 13px;
            }
        }
        </style>
        
        <div class="sps-sub-category-container">
            <?php
            // STEP 1: Jika tidak ada category parameter, tidak tampilkan apa-apa
            if (empty($current_category)) {
                ?>
                <div class="sps-no-category-message">
                    <p><?php _e('Silakan pilih kategori utama terlebih dahulu', 'simple-product-showcase'); ?></p>
                </div>
                <?php
            } else {
                // STEP 2: Category ada, tampilkan filter sub kategori
                // Get sub categories (child terms) dari parent category
                $parent_term = get_term_by('slug', $current_category, 'sps_product_category');
                
                if ($parent_term && !is_wp_error($parent_term)) {
                    $sub_categories = get_terms(array(
                        'taxonomy' => 'sps_product_category',
                        'hide_empty' => true,
                        'parent' => $parent_term->term_id,
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));
                    
                    // Display sub category filters
                    if (!empty($sub_categories) && !is_wp_error($sub_categories)) {
                        ?>
                        <div class="sps-sub-category-tabs">
                            <?php
                            foreach ($sub_categories as $sub_category) {
                                $sub_category_url = add_query_arg(array(
                                    'category' => $current_category,
                                    'sub_category' => $sub_category->slug
                                ), $current_url);
                                $active_class = ($current_sub_category === $sub_category->slug) ? 'active' : '';
                                ?>
                                <a href="<?php echo esc_url($sub_category_url); ?>" 
                                   class="sps-sub-category-tab <?php echo esc_attr($active_class); ?>">
                                    <?php echo esc_html($sub_category->name); ?>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    
                    // STEP 3: Tampilkan produk hanya jika sub_category sudah dipilih
                    if (!empty($current_sub_category)) {
                        // Verify sub_category exists and is child of parent category
                        $sub_term = get_term_by('slug', $current_sub_category, 'sps_product_category');
                        
                        if ($sub_term && !is_wp_error($sub_term) && $sub_term->parent == $parent_term->term_id) {
                            // Display products filtered by sub category using direct_products_shortcode
                            echo do_shortcode('[sps_products category="' . esc_attr($current_sub_category) . '" columns="' . esc_attr($atts['columns']) . '" limit="' . esc_attr($atts['limit']) . '" orderby="' . esc_attr($atts['orderby']) . '" order="' . esc_attr($atts['order']) . '"]');
                        } else {
                            ?>
                            <div class="sps-no-sub-category-message">
                                <p><?php _e('Sub kategori tidak valid', 'simple-product-showcase'); ?></p>
                            </div>
                            <?php
                        }
                    } else {
                        // Sub category belum dipilih
                        if (!empty($sub_categories) && !is_wp_error($sub_categories)) {
                            ?>
                            <div class="sps-no-sub-category-message">
                                <p><?php _e('Silakan pilih sub kategori untuk melihat produk', 'simple-product-showcase'); ?></p>
                            </div>
                            <?php
                        } else {
                            // Tidak ada sub kategori, tampilkan produk langsung dari parent category
                            echo do_shortcode('[sps_products category="' . esc_attr($current_category) . '" columns="' . esc_attr($atts['columns']) . '" limit="' . esc_attr($atts['limit']) . '" orderby="' . esc_attr($atts['orderby']) . '" order="' . esc_attr($atts['order']) . '"]');
                        }
                    }
                } else {
                    ?>
                    <div class="sps-no-category-message">
                        <p><?php _e('Kategori tidak ditemukan', 'simple-product-showcase'); ?></p>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render all buttons fallback (WhatsApp + Custom 1 + Custom 2)
     */
    private function render_all_buttons_fallback($product) {
        $buttons = array();
        
        // Main button (WhatsApp or Custom)
        $main_is_whatsapp = get_option('sps_main_is_whatsapp', true);
        $main_visible = get_option('sps_main_visible', true);
        
        if ($main_visible) {
            if ($main_is_whatsapp) {
                $buttons[] = $this->render_whatsapp_fallback($product);
            } else {
                $buttons[] = $this->render_custom_button_fallback($product, 'main');
            }
        }
        
        // Custom Button 1
        $custom1_visible = get_option('sps_custom1_visible', true);
        if ($custom1_visible) {
            $buttons[] = $this->render_custom_button_fallback($product, 'custom1');
        }
        
        // Custom Button 2
        $custom2_visible = get_option('sps_custom2_visible', true);
        if ($custom2_visible) {
            $buttons[] = $this->render_custom_button_fallback($product, 'custom2');
        }
        
        if (empty($buttons)) {
            return '<p class="sps-no-buttons">No buttons configured.</p>';
        }
        
        ob_start();
        ?>
        <style>
        .sps-all-buttons {
            margin: 30px 0;
            text-align: center;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            align-items: center;
        }
        
        .sps-all-buttons .sps-button-item {
            flex: 0 0 auto;
        }
        
        @media (max-width: 768px) {
            .sps-all-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .sps-all-buttons .sps-button-item {
                width: 100%;
            }
            
            .sps-all-buttons .sps-button-item a {
                width: 100%;
                justify-content: center;
            }
        }
        </style>
        <div class="sps-all-buttons">
            <?php foreach ($buttons as $button): ?>
                <div class="sps-button-item">
                    <?php echo $button; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render custom button fallback
     */
    private function render_custom_button_fallback($product, $button_id) {
        $button_text = get_option('sps_' . $button_id . '_text', 'Custom Button');
        $button_icon = get_option('sps_' . $button_id . '_icon', '');
        $button_icon_size = get_option('sps_' . $button_id . '_icon_size', 20);
        $button_url = get_option('sps_' . $button_id . '_url', '#');
        $button_target = get_option('sps_' . $button_id . '_target', '_self');
        $button_bg_color = get_option('sps_' . $button_id . '_background_color', '#007cba');
        $button_text_color = get_option('sps_' . $button_id . '_text_color', '#ffffff');
        
        // Generate unique ID and class for custom styling
        $button_class = 'sps-custom-button-' . $button_id;
        $button_html_id = 'sps-button-' . $button_id;
        
        ob_start();
        ?>
        <style>
        .<?php echo esc_attr($button_class); ?> {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: <?php echo esc_attr($button_bg_color); ?>;
            color: <?php echo esc_attr($button_text_color); ?>;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            min-width: 140px;
        }
        
        .<?php echo esc_attr($button_class); ?>:hover {
            background: <?php echo esc_attr($this->darken_color_fallback($button_bg_color, 10)); ?>;
            color: <?php echo esc_attr($button_text_color); ?>;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .<?php echo esc_attr($button_class); ?> .sps-button-icon {
            width: <?php echo esc_attr($button_icon_size); ?>px;
            height: <?php echo esc_attr($button_icon_size); ?>px;
            display: inline-block;
            vertical-align: middle;
        }
        
        .<?php echo esc_attr($button_class); ?> .sps-button-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            vertical-align: middle;
        }
        </style>
        <a href="<?php echo esc_url($button_url); ?>" 
           target="<?php echo esc_attr($button_target); ?>" 
           rel="<?php echo ($button_target === '_blank') ? 'noopener' : ''; ?>"
           class="<?php echo esc_attr($button_class); ?>" 
           id="<?php echo esc_attr($button_html_id); ?>">
            <?php if ($button_icon): ?>
                <span class="sps-button-icon">
                    <img src="<?php echo esc_url($button_icon); ?>" alt="<?php echo esc_attr($button_text); ?> Icon">
                </span>
            <?php endif; ?>
            <span class="sps-button-text"><?php echo esc_html($button_text); ?></span>
        </a>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Darken a hex color by percentage (fallback)
     */
    private function darken_color_fallback($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = (int) max(0, min(255, $r - ($r * $percent / 100)));
        $g = (int) max(0, min(255, $g - ($g * $percent / 100)));
        $b = (int) max(0, min(255, $b - ($b * $percent / 100)));
        
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
               str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
               str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
    
    /**
     * Initialize settings class (DEPRECATED - now using Configuration)
     */
    public function init_settings_class() {
        // Using new Configuration class instead
        // SPS_Settings::get_instance(); // REMOVED
        
        // Force register WhatsApp button text setting
        add_option('sps_whatsapp_button_text', 'Tanya Produk Ini');
        
        // Add admin view link filter
        add_filter('post_row_actions', array($this, 'modify_admin_view_link'), 10, 2);
    }
    
    /**
     * Modify admin view link fallback
     */
    public function modify_admin_view_link($actions, $post) {
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
                        esc_attr(sprintf('View &#8220;%s&#8221;', $post->post_title)),
                        'Lihat'
                    );
                }
            }
        }
        
        return $actions;
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
        
        register_setting('sps_settings_group', 'sps_whatsapp_button_text', array(
            'type' => 'string',
            'default' => 'Tanya Produk Ini',
            'sanitize_callback' => 'sanitize_text_field'
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
        
        // Add WhatsApp button text field
        add_settings_field(
            'sps_whatsapp_button_text',
            __('WhatsApp Button Text', 'simple-product-showcase'),
            array($this, 'whatsapp_button_text_callback'),
            'sps-settings',
            'sps_whatsapp_section'
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
        add_shortcode('sps_products_sub_category', array($this, 'fallback_products_sub_category_shortcode'));
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
            'orderby' => 'title',
            'order' => 'ASC',
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
     * Fallback shortcode handler untuk sub category
     */
    public function fallback_products_sub_category_shortcode($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'columns' => '3',
            'limit' => '-1',
            'orderby' => 'title',
            'order' => 'ASC',
            'show_price' => 'true',
            'show_description' => 'false',
            'show_whatsapp' => 'true'
        ), $atts, 'sps_products_sub_category');
        
        // Get category and sub_category from URL
        $current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $current_sub_category = isset($_GET['sub_category']) ? sanitize_text_field($_GET['sub_category']) : '';
        
        // Get current page URL
        $current_url = remove_query_arg(array('category', 'sub_category'));
        
        // Validate columns
        $columns = intval($atts['columns']);
        if ($columns < 1 || $columns > 6) {
            $columns = 3;
        }
        
        // Start output
        ob_start();
        ?>
        <style>
        .sps-sub-category-container {
            margin: 30px 0;
        }
        
        .sps-sub-category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            padding: 20px;
            background: transparent;
            border-radius: 8px;
        }
        
        .sps-sub-category-tab {
            display: inline-block;
            padding: 12px 24px;
            background: #ffffff;
            color: #333333;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            font-size: 14px;
        }
        
        .sps-sub-category-tab:hover {
            background: #f5f5f5;
            color: #000000;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .sps-sub-category-tab.active {
            background: #FDB913;
            color: #000000;
            border-color: #FDB913;
            font-weight: 600;
        }
        
        .sps-sub-category-tab.active:hover {
            background: #E5A711;
            border-color: #E5A711;
        }
        
        .sps-no-category-message {
            text-align: center;
            padding: 60px 20px;
            background: #f9f9f9;
            border-radius: 8px;
            color: #666;
            font-size: 18px;
        }
        
        .sps-no-category-message p {
            margin: 0;
            font-weight: 500;
        }
        
        .sps-no-sub-category-message {
            text-align: center;
            padding: 60px 20px;
            background: #fff9e6;
            border-radius: 8px;
            color: #666;
            font-size: 18px;
            border: 2px dashed #FDB913;
        }
        
        .sps-no-sub-category-message p {
            margin: 0;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .sps-sub-category-tabs {
                gap: 10px;
                padding: 15px;
            }
            
            .sps-sub-category-tab {
                padding: 10px 18px;
                font-size: 13px;
            }
        }
        </style>
        
        <div class="sps-sub-category-container">
            <?php
            // STEP 1: Jika tidak ada category parameter, tidak tampilkan apa-apa
            if (empty($current_category)) {
                ?>
                <div class="sps-no-category-message">
                    <p><?php _e('Silakan pilih kategori utama terlebih dahulu', 'simple-product-showcase'); ?></p>
                </div>
                <?php
            } else {
                // STEP 2: Category ada, tampilkan filter sub kategori
                // Get sub categories (child terms) dari parent category
                $parent_term = get_term_by('slug', $current_category, 'sps_product_category');
                
                if ($parent_term && !is_wp_error($parent_term)) {
                    $sub_categories = get_terms(array(
                        'taxonomy' => 'sps_product_category',
                        'hide_empty' => true,
                        'parent' => $parent_term->term_id,
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));
                    
                    // Display sub category filters
                    if (!empty($sub_categories) && !is_wp_error($sub_categories)) {
                        ?>
                        <div class="sps-sub-category-tabs">
                            <?php
                            foreach ($sub_categories as $sub_category) {
                                $sub_category_url = add_query_arg(array(
                                    'category' => $current_category,
                                    'sub_category' => $sub_category->slug
                                ), $current_url);
                                $active_class = ($current_sub_category === $sub_category->slug) ? 'active' : '';
                                ?>
                                <a href="<?php echo esc_url($sub_category_url); ?>" 
                                   class="sps-sub-category-tab <?php echo esc_attr($active_class); ?>">
                                    <?php echo esc_html($sub_category->name); ?>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    
                    // STEP 3: Tampilkan produk hanya jika sub_category sudah dipilih
                    if (!empty($current_sub_category)) {
                        // Verify sub_category exists and is child of parent category
                        $sub_term = get_term_by('slug', $current_sub_category, 'sps_product_category');
                        
                        if ($sub_term && !is_wp_error($sub_term) && $sub_term->parent == $parent_term->term_id) {
                            // Display products filtered by sub category
                            $products_atts = array_merge($atts, array('category' => $current_sub_category));
                            echo $this->fallback_products_shortcode($products_atts);
                        } else {
                            ?>
                            <div class="sps-no-sub-category-message">
                                <p><?php _e('Sub kategori tidak valid', 'simple-product-showcase'); ?></p>
                            </div>
                            <?php
                        }
                    } else {
                        // Sub category belum dipilih
                        if (!empty($sub_categories) && !is_wp_error($sub_categories)) {
                            ?>
                            <div class="sps-no-sub-category-message">
                                <p><?php _e('Silakan pilih sub kategori untuk melihat produk', 'simple-product-showcase'); ?></p>
                            </div>
                            <?php
                        } else {
                            // Tidak ada sub kategori, tampilkan produk langsung dari parent category
                            $products_atts = array_merge($atts, array('category' => $current_category));
                            echo $this->fallback_products_shortcode($products_atts);
                        }
                    }
                } else {
                    ?>
                    <div class="sps-no-category-message">
                        <p><?php _e('Kategori tidak ditemukan', 'simple-product-showcase'); ?></p>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <?php
        
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
    
    /**
     * AJAX handler untuk mendapatkan URL gambar gallery
     */
    public function ajax_get_gallery_image() {
        // Verify nonce for security
        if (!wp_verify_nonce($_GET['nonce'], 'sps_gallery_image_nonce')) {
            wp_die('Security check failed');
        }
        
        $image_id = intval($_GET['image_id']);
        
        if (!$image_id) {
            wp_send_json_error('Invalid image ID');
        }
        
        // Get image URL and alt text
        $image_url = wp_get_attachment_image_url($image_id, 'large');
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        
        if (!$image_url) {
            wp_send_json_error('Image not found');
        }
        
        wp_send_json_success(array(
            'image_url' => $image_url,
            'image_alt' => $image_alt
        ));
    }
    
}

// Inisialisasi plugin
Simple_Product_Showcase::get_instance();
