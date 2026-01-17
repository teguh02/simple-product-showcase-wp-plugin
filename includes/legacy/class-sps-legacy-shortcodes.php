<?php
/**
 * SPS Legacy Shortcodes
 * 
 * Class untuk menangani semua shortcode legacy dari Simple Product Showcase
 * Dipindahkan dari simple-product-showcase.php untuk refactoring
 * 
 * @package Simple_Product_Showcase
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Legacy_Shortcodes {
    
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
     * Direct random products shortcode handler
     */
    public function direct_random_products_shortcode($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'columns' => '3',
            'limit' => '-1'
        ), $atts, 'sps_random_products');
        
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
        
        // Get all categories yang punya produk
        $all_categories = get_terms(array(
            'taxonomy' => 'sps_product_category',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        if (is_wp_error($all_categories) || empty($all_categories)) {
            return '<p class="sps-no-products">' . __('No products found.', 'simple-product-showcase') . '</p>';
        }
        
        // Shuffle categories untuk random urutan
        shuffle($all_categories);
        
        // Tentukan target jumlah produk berdasarkan limit
        $target_count = ($limit > 0) ? min($limit, count($all_categories)) : count($all_categories);
        
        // Koleksi produk random (1 produk per kategori berbeda)
        $random_products = array();
        $selected_product_ids = array();
        
        $category_index = 0;
        $product_count = 0;
        $max_attempts = count($all_categories) * 2;
        $attempts = 0;
        
        while ($product_count < $target_count && $attempts < $max_attempts) {
            if ($category_index >= count($all_categories)) {
                $category_index = 0;
            }
            
            $category = $all_categories[$category_index];
            
            $category_args = array(
                'post_type' => 'sps_product',
                'post_status' => 'publish',
                'posts_per_page' => 10,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'sps_product_category',
                        'field' => 'term_id',
                        'terms' => $category->term_id
                    )
                ),
                'orderby' => 'rand',
                'order' => 'ASC',
                'post__not_in' => $selected_product_ids
            );
            
            $category_query = new WP_Query($category_args);
            
            if ($category_query->have_posts()) {
                $category_query->the_post();
                $current_post = get_post();
                
                if (!in_array($current_post->ID, $selected_product_ids)) {
                    $random_products[] = $current_post;
                    $selected_product_ids[] = $current_post->ID;
                    $product_count++;
                }
                wp_reset_postdata();
            }
            
            $category_index++;
            $attempts++;
        }
        
        if (empty($random_products)) {
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
            <?php foreach ($random_products as $post) : setup_postdata($post); ?>
                <div class="sps-product-item">
                    <?php if (has_post_thumbnail($post->ID)) : ?>
                        <div class="sps-product-image">
                            <?php echo get_the_post_thumbnail($post->ID, 'medium', array('alt' => get_the_title($post->ID))); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="sps-product-info">
                        <div class="sps-product-title">
                            <p class="sps-product-title-text"><?php echo get_the_title($post->ID); ?></p>
                        </div>
                                <?php 
                                $detail_url = SPS_Configuration::get_product_detail_url($post->ID);
                                ?>
                                <a href="<?php echo esc_url($detail_url); ?>" class="sps-detail-button">Detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Get current product fallback
     */
    public function get_current_product_fallback() {
        global $post;

        if (is_singular('sps_product') && $post) {
            return $post;
        }

        $product_slug = isset($_GET['product']) ? sanitize_text_field($_GET['product']) : '';
        if ($product_slug) {
            $product = get_page_by_path($product_slug, OBJECT, 'sps_product');
            if ($product) {
                return $product;
            }
        }

        $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
        if ($product_id) {
            $product = get_post($product_id);
            if ($product && $product->post_type === 'sps_product') {
                return $product;
            }
        }

        $product_slug = get_query_var('product');
        if ($product_slug) {
            $product = get_page_by_path($product_slug, OBJECT, 'sps_product');
            if ($product) {
                return $product;
            }
        }

        $post_id = get_query_var('p');
        if ($post_id) {
            $product = get_post($post_id);
            if ($product && $product->post_type === 'sps_product') {
                return $product;
            }
        }

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
     * Normalize WhatsApp number format (fallback)
     */
    public function normalize_whatsapp_number_fallback($number) {
        $number = preg_replace('/[^0-9+]/', '', $number);
        
        if (strpos($number, '+62') === 0) {
            return substr($number, 1);
        } elseif (strpos($number, '62') === 0) {
            return $number;
        } elseif (strpos($number, '0') === 0) {
            return '62' . substr($number, 1);
        } else {
            return '62' . $number;
        }
    }
    
    /**
     * Get product detail URL fallback
     */
    public function get_product_detail_url_fallback($product_id) {
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

        return get_permalink($product_id);
    }
    
    /**
     * Darken a hex color by percentage (fallback)
     */
    public function darken_color_fallback($hex, $percent) {
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
     * Generate fallback WhatsApp button HTML
     */
    public function get_fallback_whatsapp_button($product_id, $product_title, $product_url) {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            return '<p class="sps-whatsapp-error">' . __('WhatsApp number not configured.', 'simple-product-showcase') . '</p>';
        }
        
        $custom_message = get_post_meta($product_id, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
        
        $message = !empty($custom_message) ? $custom_message : $global_message;
        
        $message = str_replace('{product_link}', $product_url, $message);
        $message = str_replace('{product_name}', $product_title, $message);
        
        $encoded_message = urlencode($message);
        
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
        
        $columns = intval($atts['columns']);
        if ($columns < 1 || $columns > 6) {
            $columns = 3;
        }
        
        $limit = intval($atts['limit']);
        if ($limit < -1) {
            $limit = -1;
        }
        
        $args = array(
            'post_type' => 'sps_product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        );
        
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'sps_product_category',
                    'field' => 'slug',
                    'terms' => $atts['category']
                )
            );
        }
        
        $products_query = new WP_Query($args);
        
        if (!$products_query->have_posts()) {
            return '<p class="sps-no-products">' . __('No products found.', 'simple-product-showcase') . '</p>';
        }
        
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
        wp_reset_postdata();
        
        return ob_get_clean();
    }
}

