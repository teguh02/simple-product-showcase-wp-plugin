<?php
/**
 * SPS Legacy Admin
 * 
 * Class untuk menangani semua fungsi admin legacy dari Simple Product Showcase
 * Dipindahkan dari simple-product-showcase.php untuk refactoring
 * 
 * @package Simple_Product_Showcase
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Legacy_Admin {
    
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
        // No hooks here, methods called from Simple_Product_Showcase
    }
    
    /**
     * Fallback admin menu jika class SPS_Settings tidak berfungsi
     */
    public function add_fallback_admin_menu() {
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
                update_option('sps_' . $button_id . '_is_whatsapp', isset($_POST['sps_' . $button_id . '_is_whatsapp']) ? (bool) $_POST['sps_' . $button_id . '_is_whatsapp'] : false);
                update_option('sps_' . $button_id . '_visible', isset($_POST['sps_' . $button_id . '_visible']) ? (bool) $_POST['sps_' . $button_id . '_visible'] : false);
                
                if (isset($_POST['sps_' . $button_id . '_text'])) {
                    update_option('sps_' . $button_id . '_text', sanitize_text_field($_POST['sps_' . $button_id . '_text']));
                }
                
                if (isset($_POST['sps_' . $button_id . '_icon'])) {
                    update_option('sps_' . $button_id . '_icon', esc_url_raw($_POST['sps_' . $button_id . '_icon']));
                }
                if (isset($_POST['sps_' . $button_id . '_url'])) {
                    update_option('sps_' . $button_id . '_url', esc_url_raw($_POST['sps_' . $button_id . '_url']));
                }
                
                if (isset($_POST['sps_' . $button_id . '_target'])) {
                    update_option('sps_' . $button_id . '_target', sanitize_text_field($_POST['sps_' . $button_id . '_target']));
                }
                
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
                <p><strong>📚 Complete Shortcode Documentation</strong> - Please refer to SHORTCODE-DOCUMENTATION.md file for complete documentation.</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Initialize settings class (DEPRECATED - now using Configuration)
     */
    public function init_settings_class() {
        add_option('sps_whatsapp_button_text', 'Tanya Produk Ini');
        add_filter('post_row_actions', array($this, 'modify_admin_view_link'), 10, 2);
    }
    
    /**
     * Modify admin view link fallback
     */
    public function modify_admin_view_link($actions, $post) {
        if ($post->post_type === 'sps_product') {
            $detail_mode = get_option('sps_detail_page_mode', 'default');
            
            if ($detail_mode === 'custom') {
                $custom_page_id = get_option('sps_custom_detail_page', 0);
                if ($custom_page_id) {
                    $page_url = get_permalink($custom_page_id);
                    $slug_url = add_query_arg('product', $post->post_name, $page_url);
                    
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
        
        add_settings_section(
            'sps_detail_page_section',
            __('Detail Page Settings', 'simple-product-showcase'),
            array($this, 'detail_page_section_callback'),
            'sps-settings'
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
}

