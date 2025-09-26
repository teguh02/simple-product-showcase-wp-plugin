<?php
/**
 * Class SPS_Shortcodes
 * 
 * Class untuk mengelola shortcodes plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Shortcodes {
    
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
        add_action('init', array($this, 'register_shortcodes'));
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('sps_products', array($this, 'products_shortcode'));
    }
    
    /**
     * Shortcode untuk menampilkan produk
     * 
     * @param array $atts Attributes dari shortcode
     * @return string HTML output
     */
    public function products_shortcode($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'columns' => '3',
            'category' => '',
            'limit' => '-1',
            'orderby' => 'date',
            'order' => 'DESC',
            'show_price' => 'true',
            'show_description' => 'false',
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
            'order' => $atts['order'],
            'meta_query' => array(
                'relation' => 'AND'
            )
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
        }
        .sps-product-image {
            margin-bottom: 15px;
            text-align: center;
        }
        .sps-product-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .sps-product-title {
            margin: 0 0 15px 0;
            text-align: center;
        }
        .sps-product-title-text {
            color: #333;
            font-weight: 600;
            font-size: 16px;
            margin: 0;
            line-height: 1.3;
        }
        .sps-detail-button {
            background-color: #FFD700;
            color: #333;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);
        }
        .sps-detail-button:hover {
            background-color: #FFC107;
            color: #333;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(255, 215, 0, 0.4);
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
                    
                    <div class="sps-product-title">
                        <p class="sps-product-title-text"><?php the_title(); ?></p>
                    </div>
                    
                    <a href="<?php the_permalink(); ?>" class="sps-detail-button">Detail</a>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php
        // Reset post data
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Generate WhatsApp button HTML
     * 
     * @param int $product_id Product ID
     * @param string $product_title Product title
     * @param string $product_url Product URL
     * @return string WhatsApp button HTML
     */
    private function get_whatsapp_button($product_id, $product_title, $product_url) {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            return '<p class="sps-whatsapp-error">' . __('WhatsApp number not configured.', 'simple-product-showcase') . '</p>';
        }
        
        // Get custom message for this product or use global message
        $custom_message = get_post_meta($product_id, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}');
        
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
     * Get product categories for filter dropdown
     * 
     * @return array Array of categories
     */
    public function get_product_categories() {
        $categories = get_terms(array(
            'taxonomy' => 'sps_product_category',
            'hide_empty' => true,
        ));
        
        if (is_wp_error($categories)) {
            return array();
        }
        
        return $categories;
    }
    
    /**
     * Generate category filter HTML
     * 
     * @param string $current_category Current selected category
     * @return string Filter HTML
     */
    public function get_category_filter($current_category = '') {
        $categories = $this->get_product_categories();
        
        if (empty($categories)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="sps-category-filter">
            <label for="sps-category-select"><?php _e('Filter by Category:', 'simple-product-showcase'); ?></label>
            <select id="sps-category-select" class="sps-category-select">
                <option value=""><?php _e('All Categories', 'simple-product-showcase'); ?></option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo esc_attr($category->slug); ?>" <?php selected($current_category, $category->slug); ?>>
                        <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
        return ob_get_clean();
    }
}
