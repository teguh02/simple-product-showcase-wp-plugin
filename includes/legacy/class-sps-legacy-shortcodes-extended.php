<?php
/**
 * SPS Legacy Shortcodes Extended
 * 
 * Class untuk menangani shortcode legacy extended dari Simple Product Showcase
 * Method-method yang terlalu besar untuk class utama
 * 
 * @package Simple_Product_Showcase
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Legacy_Shortcodes_Extended {
    
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
     * Set cookie informasi produk via PHP (tanpa JS).
     */
    public function set_product_info_cookie() {
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        if (headers_sent()) {
            return;
        }

        $product = SPS_Legacy_Shortcodes::get_instance()->get_current_product_fallback();
        if (!$product) {
            return;
        }

        $product_id = $product->ID;
        $cookie_key = 'sps_product_info';

        $price_numeric = get_post_meta($product_id, '_sps_product_price_numeric', true);
        $price_discount = get_post_meta($product_id, '_sps_product_price_discount', true);

        $terms = get_the_terms($product_id, 'sps_product_category');
        $category_names = array();
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $category_names[] = $term->name;
            }
        }

        $data = array(
            'id' => $product_id,
            'title' => $product->post_title,
            'url' => get_permalink($product_id),
            'image' => get_the_post_thumbnail_url($product_id, 'medium'),
            'price' => $price_numeric,
            'discount' => $price_discount,
            'categories' => $category_names,
        );

        $value = base64_encode(wp_json_encode($data));

        setcookie(
            $cookie_key,
            $value,
            time() + (7 * DAY_IN_SECONDS),
            COOKIEPATH ? COOKIEPATH : '/',
            COOKIE_DOMAIN,
            is_ssl(),
            false
        );
    }

    /**
     * Direct detail products shortcode handler (fallback)
     */
    public function direct_detail_products_shortcode($atts) {
        $atts = shortcode_atts(array(
            'section' => 'title',
            'style' => 'grid'
        ), $atts, 'sps_detail_products');
        
        $product = SPS_Legacy_Shortcodes::get_instance()->get_current_product_fallback();
        
        if (!$product) {
            return '<p class="sps-no-product">No product found.</p>';
        }
        
        $output = '';
        $section_value = '';
        switch ($atts['section']) {
            case 'title':
                $valid_styles = array('h1', 'h2', 'h3', 'h4', 'h5');
                $heading_tag = in_array($atts['style'], $valid_styles) ? $atts['style'] : 'h1';
                $section_value = '<' . $heading_tag . ' class="sps-product-detail-title">' . esc_html($product->post_title) . '</' . $heading_tag . '>';
                $output = $section_value;
                break;
                
            case 'price':
                $valid_styles = array('h1', 'h2', 'h3', 'h4', 'h5');
                $heading_tag = in_array($atts['style'], $valid_styles) ? $atts['style'] : 'h3';
                
                $price = get_post_meta($product->ID, '_sps_product_price', true);
                $price_numeric = get_post_meta($product->ID, '_sps_product_price_numeric', true);
                $price_discount = get_post_meta($product->ID, '_sps_product_price_discount', true);
                
                $price_numeric = is_numeric($price_numeric) ? floatval($price_numeric) : 0;
                $price_discount = is_numeric($price_discount) ? floatval($price_discount) : 0;
                
                $price_original = 'Rp ' . number_format($price_numeric, 0, ',', '.');
                $price_discounted = 'Rp ' . number_format($price_discount, 0, ',', '.');
                
                if (!empty($price_discount) && is_numeric($price_discount) && $price_discount > 0) {
                    $section_value = '<' . $heading_tag . ' class="sps-product-detail-price">' .
                           '<span class="sps-product-price-original" data-price="' . esc_attr($price_numeric) . '" style="text-decoration: line-through; color: #999; margin-right: 10px;">' . esc_html($price_original) . '</span>' .
                           '<span class="sps-product-price-discount" data-price="' . esc_attr($price_discount) . '" style="color: #f56c2d; font-weight: bold;">' . esc_html($price_discounted) . '</span>' .
                           '</' . $heading_tag . '>';
                } else {
                    $section_value = '<' . $heading_tag . ' class="sps-product-detail-price" data-price="' . esc_attr($price_numeric) . '">' . esc_html($price_original) . '</' . $heading_tag . '>';
                }
                $output = $section_value;
                break;
                
            case 'weight':
                $valid_styles = array('h1', 'h2', 'h3', 'h4', 'h5', 'p');
                $weight_style = ($atts['style'] === 'grid') ? 'p' : $atts['style'];
                $heading_tag = in_array($weight_style, $valid_styles) ? $weight_style : 'p';
                
                $weight = get_post_meta($product->ID, '_sps_product_weight', true);
                $weight = is_numeric($weight) ? absint($weight) : 0;
                $weight_display = number_format($weight, 0, ',', '.') . ' gram';
                
                $section_value = '<' . $heading_tag . ' class="sps-product-detail-weight" data-weight="' . esc_attr($weight) . '">' . esc_html($weight_display) . '</' . $heading_tag . '>';
                $output = $section_value;
                break;
                
            case 'image':
                if (has_post_thumbnail($product->ID)) {
                    $section_value = '<div class="sps-product-detail-image" id="sps-main-image-container">' . get_the_post_thumbnail($product->ID, 'large', array('class' => 'sps-main-image')) . '</div>';
                } else {
                    $section_value = '<div class="sps-product-detail-image" id="sps-main-image-container"><p>No image available.</p></div>';
                }
                $output = $section_value;
                break;
                
            case 'description':
                $content = apply_filters('the_content', $product->post_content);
                $section_value = '<div class="sps-product-detail-description">' . $content . '</div>';
                $output = $section_value;
                break;
                
            case 'gallery':
                $section_value = $this->render_gallery_fallback($product, $atts['style']);
                $output = $section_value;
                break;
                
            case 'whatsapp':
                $section_value = $this->render_whatsapp_fallback($product);
                $output = $section_value;
                break;
                
            case 'button':
                $section_value = $this->render_all_buttons_fallback($product);
                $output = $section_value;
                break;

            case 'category':
                $terms = get_the_terms($product->ID, 'sps_product_category');
                if (empty($terms) || is_wp_error($terms)) {
                    $section_value = '<span class="sps-product-category-empty">No category</span>';
                } else {
                    $main = null;
                    $subs = array();
                    foreach ($terms as $term) {
                        if ($term->parent && $term->parent > 0) {
                            $subs[] = $term;
                        } else {
                            $main = $term;
                        }
                    }
                    if (!$main && count($terms) > 0) {
                        $main = $terms[0];
                    }
                    $category_output = '';
                    if ($main) {
                        $category_output .= esc_html($main->name);
                    }
                    if (!empty($subs)) {
                        $sub_names = array_map(function($t) { return esc_html($t->name); }, $subs);
                        $category_output .= ' &gt; ' . implode(' , ', $sub_names);
                    }
                    $section_value = '<span class="sps-product-category">' . $category_output . '</span>';
                }
                $output = $section_value;
                break;
                
            default:
                $section_value = '<p class="sps-invalid-section">Invalid section: ' . esc_html($atts['section']) . '</p>';
                $output = $section_value;
                break;
        }

        return $output;
    }

    /**
     * Render gallery fallback
     */
    public function render_gallery_fallback($product, $style = 'grid') {
        $gallery_images = array();
        
        $thumbnail_id = get_post_thumbnail_id($product->ID);
        if ($thumbnail_id) {
            $gallery_images[] = $thumbnail_id;
        }
        
        for ($i = 1; $i <= 5; $i++) {
            $image_id = get_post_meta($product->ID, '_sps_gallery_' . $i, true);
            if ($image_id) {
                $gallery_images[] = $image_id;
            }
        }
        
        if (empty($gallery_images)) {
            return '<div class="sps-product-gallery-empty"><p>No gallery images available.</p></div>';
        }
        
        if (count($gallery_images) <= 2) {
            return '';
        }
        
        ob_start();
        ?>
        <style>
        .sps-product-detail-image { margin: 20px 0; text-align: center; }
        .sps-main-image { width: 100%; max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: block; margin: 0 auto; }
        .sps-product-gallery { margin: 20px 0; }
        .sps-gallery-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        @media (max-width: 1024px) {
            .sps-product-gallery { position: relative; }
            .sps-gallery-grid { display: flex; overflow-x: auto; gap: 15px; padding: 10px 0; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; }
            .sps-gallery-item { flex: 0 0 auto !important; width: 100px !important; min-width: 100px !important; }
            .sps-gallery-item .sps-gallery-image { width: 100px !important; height: 100px !important; max-width: 100px !important; max-height: 100px !important; }
        }
        @media (max-width: 768px) {
            .sps-gallery-item { width: 80px !important; min-width: 80px !important; }
            .sps-gallery-item .sps-gallery-image { width: 80px !important; height: 80px !important; max-width: 80px !important; max-height: 80px !important; }
        }
        .sps-gallery-carousel { display: flex; overflow: hidden; gap: 15px; padding: 10px 0; transition: transform 0.3s ease; }
        .sps-gallery-item { flex: 0 0 calc(33.333% - 10px); min-width: 200px; }
        .sps-gallery-slider { position: relative; max-width: 600px; margin: 0 auto; }
        .sps-gallery-slide { display: none; text-align: center; }
        .sps-gallery-slide.active { display: block; }
        .sps-gallery-image { max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .sps-gallery-controls { text-align: center; margin: 15px 0; }
        .sps-gallery-prev, .sps-gallery-next { background: #0073aa; color: white; border: none; padding: 10px 20px; margin: 0 10px; border-radius: 5px; cursor: pointer; font-size: 18px; }
        .sps-gallery-image-link { display: block; text-decoration: none; transition: transform 0.3s ease, opacity 0.3s ease; border: 3px solid transparent; border-radius: 8px; }
        .sps-gallery-image-link:hover { transform: scale(1.05); opacity: 0.9; }
        .sps-gallery-image-link.active { border-color: #0073aa !important; box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.2); }
        .sps-gallery-image-link .sps-gallery-image { display: block; width: 100%; height: auto; border-radius: 5px; }
        </style>
        <div class="sps-product-gallery sps-gallery-<?php echo esc_attr($style); ?>" data-product-id="<?php echo esc_attr($product->ID); ?>">
            <?php 
            foreach ($gallery_images as $index => $image_id) : 
                $thumbnail_number = $index + 1;
                $active_class = ($thumbnail_number == 1) ? ' active' : '';
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
        function spsGalleryNext() { if (totalSlides > 0) { slides[currentSlide].style.display = 'none'; currentSlide = (currentSlide + 1) % totalSlides; slides[currentSlide].style.display = 'block'; } }
        function spsGalleryPrev() { if (totalSlides > 0) { slides[currentSlide].style.display = 'none'; currentSlide = (currentSlide - 1 + totalSlides) % totalSlides; slides[currentSlide].style.display = 'block'; } }
        document.addEventListener('DOMContentLoaded', function() { slides.forEach((slide, index) => { slide.style.display = index === 0 ? 'block' : 'none'; }); });
        </script>
        <?php endif; ?>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const galleryLinks = document.querySelectorAll('.sps-gallery-image-link');
            const mainImageContainer = document.getElementById('sps-main-image-container');
            checkHashParameter();
            window.addEventListener('hashchange', checkHashParameter);
            galleryLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const thumbnailNumber = this.getAttribute('data-thumbnail');
                    const imageId = this.getAttribute('data-image-id');
                    window.location.hash = 'thumbnail=' + thumbnailNumber;
                    updateActiveGalleryImage(thumbnailNumber);
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
                        updateActiveGalleryImage(thumbnailNumber);
                        changeMainImage(imageId);
                    }
                } else { updateActiveGalleryImage(1); }
            }
            function updateActiveGalleryImage(thumbnailNumber) {
                galleryLinks.forEach(function(link) { link.classList.remove('active'); });
                const activeLink = document.querySelector(`[data-thumbnail="${thumbnailNumber}"]`);
                if (activeLink) { activeLink.classList.add('active'); }
            }
            function changeMainImage(imageId) {
                if (!mainImageContainer || !imageId) { return; }
                const xhr = new XMLHttpRequest();
                const nonce = '<?php echo wp_create_nonce('sps_gallery_image_nonce'); ?>';
                const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=get_gallery_image&image_id=' + imageId + '&nonce=' + nonce;
                xhr.open('GET', ajaxUrl, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success && response.data.image_url) {
                                const mainImage = mainImageContainer.querySelector('.sps-main-image');
                                if (mainImage) {
                                    mainImage.src = response.data.image_url;
                                    mainImage.alt = response.data.image_alt || '';
                                    mainImage.removeAttribute('srcset');
                                    mainImage.removeAttribute('sizes');
                                } else {
                                    const newImage = document.createElement('img');
                                    newImage.src = response.data.image_url;
                                    newImage.alt = response.data.image_alt || '';
                                    newImage.className = 'sps-main-image';
                                    newImage.style.cssText = 'width:100%;max-width:100%;height:auto;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1);display:block;margin:0 auto;';
                                    mainImageContainer.innerHTML = '';
                                    mainImageContainer.appendChild(newImage);
                                }
                            }
                        } catch (e) { console.error('Error parsing AJAX response:', e); }
                    }
                };
                xhr.send();
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render WhatsApp fallback
     */
    public function render_whatsapp_fallback($product) {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            return '<p class="sps-whatsapp-error">WhatsApp number not configured.</p>';
        }
        
        $whatsapp_number = SPS_Legacy_Shortcodes::get_instance()->normalize_whatsapp_number_fallback($whatsapp_number);
        
        $custom_message = get_post_meta($product->ID, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
        
        $message = !empty($custom_message) ? $custom_message : $global_message;
        
        $product_url = SPS_Legacy_Shortcodes::get_instance()->get_product_detail_url_fallback($product->ID);
        $message = str_replace('{product_link}', $product_url, $message);
        $message = str_replace('{product_name}', $product->post_title, $message);
        
        $encoded_message = urlencode($message);
        $whatsapp_url = "https://wa.me/{$whatsapp_number}?text={$encoded_message}";
        $button_text = get_option('sps_whatsapp_button_text', 'Tanya Produk Ini');
        
        ob_start();
        ?>
        <style>
        .sps-product-whatsapp { margin: 30px 0; text-align: center; }
        .sps-whatsapp-detail-button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #25D366; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; border: none; cursor: pointer; }
        .sps-whatsapp-detail-button:hover { background: #128C7E; color: white; text-decoration: none; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3); }
        .sps-whatsapp-icon { width: 20px; height: 20px; margin-right: 8px; vertical-align: middle; display: inline-block; filter: invert(1); }
        </style>
        <div class="sps-product-whatsapp">
            <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="sps-whatsapp-detail-button">
                <img src="<?php echo esc_url(plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/img/whatsapp.png'); ?>" alt="WhatsApp" class="sps-whatsapp-icon" />
                <?php echo esc_html($button_text); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render all buttons fallback (WhatsApp + Custom 1 + Custom 2)
     */
    public function render_all_buttons_fallback($product) {
        $buttons = array();
        
        $main_is_whatsapp = get_option('sps_main_is_whatsapp', true);
        $main_visible = get_option('sps_main_visible', true);
        
        if ($main_visible) {
            if ($main_is_whatsapp) {
                $buttons[] = $this->render_whatsapp_fallback($product);
            } else {
                $buttons[] = $this->render_custom_button_fallback($product, 'main');
            }
        }
        
        $custom1_visible = get_option('sps_custom1_visible', true);
        if ($custom1_visible) {
            $buttons[] = $this->render_custom_button_fallback($product, 'custom1');
        }
        
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
        .sps-all-buttons { margin: 30px 0; text-align: center; display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; align-items: center; }
        .sps-all-buttons .sps-button-item { flex: 0 0 auto; }
        @media (max-width: 768px) { .sps-all-buttons { flex-direction: column; align-items: stretch; } .sps-all-buttons .sps-button-item { width: 100%; } .sps-all-buttons .sps-button-item a { width: 100%; justify-content: center; } }
        </style>
        <div class="sps-all-buttons">
            <?php foreach ($buttons as $button): ?>
                <div class="sps-button-item"><?php echo $button; ?></div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render custom button fallback
     */
    public function render_custom_button_fallback($product, $button_id) {
        $button_text = get_option('sps_' . $button_id . '_text', 'Custom Button');
        $button_icon = get_option('sps_' . $button_id . '_icon', '');
        $button_icon_size = get_option('sps_' . $button_id . '_icon_size', 20);
        $button_url = get_option('sps_' . $button_id . '_url', '#');
        $button_target = get_option('sps_' . $button_id . '_target', '_self');
        $button_bg_color = get_option('sps_' . $button_id . '_background_color', '#007cba');
        $button_text_color = get_option('sps_' . $button_id . '_text_color', '#ffffff');
        
        $button_class = 'sps-custom-button-' . $button_id;
        $button_html_id = 'sps-button-' . $button_id;
        $darken_color = SPS_Legacy_Shortcodes::get_instance()->darken_color_fallback($button_bg_color, 10);
        
        ob_start();
        ?>
        <style>
        .<?php echo esc_attr($button_class); ?> { display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: <?php echo esc_attr($button_bg_color); ?>; color: <?php echo esc_attr($button_text_color); ?>; padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s ease; border: none; cursor: pointer; min-width: 140px; }
        .<?php echo esc_attr($button_class); ?>:hover { background: <?php echo esc_attr($darken_color); ?>; color: <?php echo esc_attr($button_text_color); ?>; text-decoration: none; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .<?php echo esc_attr($button_class); ?> .sps-button-icon { width: <?php echo esc_attr($button_icon_size); ?>px; height: <?php echo esc_attr($button_icon_size); ?>px; display: inline-block; vertical-align: middle; }
        .<?php echo esc_attr($button_class); ?> .sps-button-icon img { width: 100%; height: 100%; object-fit: contain; vertical-align: middle; }
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
}

