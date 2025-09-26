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
        add_shortcode('sps_detail_products', array($this, 'detail_products_shortcode'));
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
                                $detail_url = SPS_Settings::get_product_detail_url(get_the_ID());
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
     * Shortcode untuk menampilkan detail produk
     * 
     * @param array $atts Attributes dari shortcode
     * @return string HTML output
     */
    public function detail_products_shortcode($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'section' => 'title',
            'style' => 'grid'
        ), $atts, 'sps_detail_products');
        
        // Get current product
        $product = $this->get_current_product();
        
        if (!$product) {
            return '<p class="sps-no-product">' . __('No product found.', 'simple-product-showcase') . '</p>';
        }
        
        // Switch berdasarkan section
        switch ($atts['section']) {
            case 'title':
                return $this->render_product_title($product, $atts['style']);
                
            case 'image':
                return $this->render_product_image($product);
                
            case 'description':
                return $this->render_product_description($product);
                
            case 'gallery':
                return $this->render_product_gallery($product, $atts['style']);
                
            case 'whatsapp':
                return $this->render_whatsapp_button($product);
                
            default:
                return '<p class="sps-invalid-section">' . sprintf(__('Invalid section: %s', 'simple-product-showcase'), esc_html($atts['section'])) . '</p>';
        }
    }
    
    /**
     * Get current product from URL
     * 
     * @return WP_Post|false Current product post or false if not found
     */
    private function get_current_product() {
        global $post;
        
        // If we're on a single product page
        if (is_singular('sps_product') && $post) {
            return $post;
        }
        
        // Try to get from product_id parameter (for custom pages)
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
     * Render product title
     */
    private function render_product_title($product, $style = 'h1') {
        // Validasi style untuk title
        $valid_styles = array('h1', 'h2', 'h3', 'h4', 'h5');
        $heading_tag = in_array($style, $valid_styles) ? $style : 'h1';
        
        return '<' . $heading_tag . ' class="sps-product-detail-title">' . esc_html($product->post_title) . '</' . $heading_tag . '>';
    }
    
    /**
     * Render product image
     */
    private function render_product_image($product) {
        if (has_post_thumbnail($product->ID)) {
            return '<div class="sps-product-detail-image">' . get_the_post_thumbnail($product->ID, 'large', array('class' => 'sps-main-image')) . '</div>';
        }
        return '<div class="sps-product-detail-image"><p>' . __('No image available.', 'simple-product-showcase') . '</p></div>';
    }
    
    /**
     * Render product description
     */
    private function render_product_description($product) {
        $content = apply_filters('the_content', $product->post_content);
        return '<div class="sps-product-detail-description">' . $content . '</div>';
    }
    
    /**
     * Render product gallery
     */
    private function render_product_gallery($product, $style = 'grid') {
        $gallery_images = array();
        
        // Get gallery images from meta
        for ($i = 1; $i <= 5; $i++) {
            $image_id = get_post_meta($product->ID, '_sps_gallery_' . $i, true);
            if ($image_id) {
                $gallery_images[] = $image_id;
            }
        }
        
        if (empty($gallery_images)) {
            return '<div class="sps-product-gallery-empty"><p>' . __('No gallery images available.', 'simple-product-showcase') . '</p></div>';
        }
        
        ob_start();
        ?>
        <div class="sps-product-gallery sps-gallery-<?php echo esc_attr($style); ?>">
            <?php foreach ($gallery_images as $image_id) : ?>
                <?php if ($style === 'slider') : ?>
                    <div class="sps-gallery-slide">
                        <?php echo wp_get_attachment_image($image_id, 'large', false, array('class' => 'sps-gallery-image')); ?>
                    </div>
                <?php else : ?>
                    <div class="sps-gallery-item">
                        <?php echo wp_get_attachment_image($image_id, 'medium', false, array('class' => 'sps-gallery-image')); ?>
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
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render WhatsApp button
     */
    private function render_whatsapp_button($product) {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            return '<p class="sps-whatsapp-error">' . __('WhatsApp number not configured.', 'simple-product-showcase') . '</p>';
        }
        
        // Get custom message for this product or use global message
        $custom_message = get_post_meta($product->ID, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
        
        $message = !empty($custom_message) ? $custom_message : $global_message;
        
        // Replace placeholders
        $message = str_replace('{product_link}', get_permalink($product->ID), $message);
        $message = str_replace('{product_name}', $product->post_title, $message);
        
        // URL encode the message
        $encoded_message = urlencode($message);
        
        // Generate WhatsApp URL
        $whatsapp_url = "https://wa.me/{$whatsapp_number}?text={$encoded_message}";
        
        return sprintf(
            '<div class="sps-product-whatsapp">
                <a href="%s" target="_blank" rel="noopener" class="sps-whatsapp-detail-button">
                    <img src="%s" alt="WhatsApp" class="sps-whatsapp-icon" />
                    %s
                </a>
            </div>',
            esc_url($whatsapp_url),
            esc_url(plugin_dir_url(dirname(__FILE__)) . 'assets/img/whatsapp.png'),
            __('Tanya Produk Ini', 'simple-product-showcase')
        );
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
