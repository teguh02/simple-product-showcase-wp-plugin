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
            'orderby' => 'title',
            'order' => 'ASC',
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
        // Default: show thumbnail or first available image (PHP loads this initially)
        if (has_post_thumbnail($product->ID)) {
            return '<div class="sps-product-detail-image" id="sps-main-image-container">' . get_the_post_thumbnail($product->ID, 'large', array('class' => 'sps-main-image')) . '</div>';
        }
        
        return '<div class="sps-product-detail-image" id="sps-main-image-container"><p>' . __('No image available.', 'simple-product-showcase') . '</p></div>';
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
            return '<div class="sps-product-gallery-empty"><p>' . __('No gallery images available.', 'simple-product-showcase') . '</p></div>';
        }
        
        // If only thumbnail exists (1 image) or thumbnail + 1 gallery (2 images), don't show gallery
        if (count($gallery_images) <= 2) {
            return '';
        }
        
        ob_start();
        ?>
        <style>
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
        // AJAX Gallery Image Changer
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
     * Normalize WhatsApp number format
     * 
     * @param string $number Raw WhatsApp number
     * @return string Normalized number
     */
    private function normalize_whatsapp_number($number) {
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
     * Render WhatsApp button
     */
    private function render_whatsapp_button($product) {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            return '<p class="sps-whatsapp-error">' . __('WhatsApp number not configured.', 'simple-product-showcase') . '</p>';
        }
        
        // Normalize WhatsApp number format
        $whatsapp_number = $this->normalize_whatsapp_number($whatsapp_number);
        
        // Get custom message for this product or use global message
        $custom_message = get_post_meta($product->ID, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
        
        $message = !empty($custom_message) ? $custom_message : $global_message;
        
        // Replace placeholders
        $product_url = SPS_Settings::get_product_detail_url($product->ID);
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
                <img src="<?php echo esc_url(plugin_dir_url(dirname(__FILE__)) . 'assets/img/whatsapp.png'); ?>" alt="WhatsApp" class="sps-whatsapp-icon" />
                <?php echo esc_html($button_text); ?>
            </a>
        </div>
        <?php
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
