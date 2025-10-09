<?php
/**
 * Template untuk halaman single product
 * 
 * Template ini akan digunakan untuk menampilkan halaman detail produk
 * 
 * @package Simple Product Showcase
 */

// Mencegah akses langsung
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="sps-single-product-wrapper">
    <div class="container">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('sps-single-product'); ?>>
                
                <?php
                // Header produk
                $product_title = get_the_title();
                $product_price = get_post_meta(get_the_ID(), '_sps_product_price', true);
                $product_categories = get_the_terms(get_the_ID(), 'sps_product_category');
                ?>
                
                <header class="sps-product-header">
                    <?php echo do_shortcode('[sps_detail_products section="title"]'); ?>
                    <?php echo do_shortcode('[sps_detail_products section="price"]'); ?>
                    
                    <?php if ($product_categories && !is_wp_error($product_categories)) : ?>
                        <div class="sps-product-categories">
                            <span class="sps-category-label"><?php _e('Categories:', 'simple-product-showcase'); ?></span>
                            <?php foreach ($product_categories as $category) : ?>
                                <a href="<?php echo get_term_link($category); ?>" class="sps-category-link">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </header>
                
                <div class="sps-product-content-wrapper">
                    <?php echo do_shortcode('[sps_detail_products section="image"]'); ?>
                    
                    <div class="sps-product-details">
                        <?php echo do_shortcode('[sps_detail_products section="description"]'); ?>
                        
                        <div class="sps-product-gallery-section">
                            <?php echo do_shortcode('[sps_detail_products section="gallery" style="slider"]'); ?>
                        </div>
                        
                        <div class="sps-product-contact">
                            <?php echo do_shortcode('[sps_detail_products section="whatsapp"]'); ?>
                        </div>
                    </div>
                </div>
                
                <?php
                // Navigation ke produk lain
                $prev_product = get_previous_post(true, '', 'sps_product_category');
                $next_product = get_next_post(true, '', 'sps_product_category');
                
                if ($prev_product || $next_product) :
                ?>
                    <nav class="sps-product-navigation">
                        <?php if ($prev_product) : ?>
                            <div class="sps-nav-previous">
                                <a href="<?php echo get_permalink($prev_product->ID); ?>" class="sps-nav-link">
                                    <span class="sps-nav-arrow">←</span>
                                    <span class="sps-nav-title"><?php echo get_the_title($prev_product->ID); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($next_product) : ?>
                            <div class="sps-nav-next">
                                <a href="<?php echo get_permalink($next_product->ID); ?>" class="sps-nav-link">
                                    <span class="sps-nav-title"><?php echo get_the_title($next_product->ID); ?></span>
                                    <span class="sps-nav-arrow">→</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
                
            </article>
        <?php endwhile; ?>
    </div>
</div>

<style>
/* Additional styles for single product template */
.sps-single-product-wrapper {
    padding: 20px 0;
}

.sps-product-content-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin: 30px 0;
}

.sps-product-details {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.sps-product-gallery-section {
    margin: 20px 0;
}

.sps-product-contact {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

/* Product Detail Styles */
.sps-product-detail-title {
    color: #333;
    font-size: 28px;
    font-weight: bold;
    margin: 20px 0;
    line-height: 1.2;
}

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

.sps-product-detail-description {
    margin: 20px 0;
    line-height: 1.6;
    color: #555;
}

.sps-product-detail-description p {
    margin-bottom: 15px;
}

.sps-product-detail-price {
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
    margin: 20px 0;
}

/* Gallery Styles */
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
    transition: background-color 0.3s ease;
}

.sps-gallery-prev:hover,
.sps-gallery-next:hover {
    background: #005a87;
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

/* WhatsApp Detail Button */
.sps-product-whatsapp {
    margin: 30px 0;
    text-align: center;
}

.sps-whatsapp-detail-button {
    background: linear-gradient(to bottom, #25D366, #128C7E);
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 25px;
    text-decoration: none;
    display: inline-block;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
}

.sps-whatsapp-detail-button:hover {
    background: linear-gradient(to bottom, #128C7E, #075E54);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(37, 211, 102, 0.4);
}

.sps-whatsapp-icon {
    margin-right: 8px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sps-product-content-wrapper {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .sps-product-detail-title {
        font-size: 24px;
    }
    
    .sps-gallery-slider {
        max-width: 100%;
    }
    
    .sps-whatsapp-detail-button {
        padding: 12px 24px;
        font-size: 14px;
    }
}

.sps-product-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e1e5e9;
}

.sps-nav-previous,
.sps-nav-next {
    flex: 1;
}

.sps-nav-next {
    text-align: right;
}

.sps-nav-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #007cba;
    text-decoration: none;
    padding: 10px 15px;
    border: 1px solid #e1e5e9;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.sps-nav-link:hover {
    background: #f8f9fa;
    border-color: #007cba;
    color: #007cba;
}

.sps-nav-arrow {
    font-size: 18px;
    font-weight: bold;
}

.sps-nav-title {
    font-size: 14px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .sps-product-navigation {
        flex-direction: column;
        gap: 15px;
    }
    
    .sps-nav-next {
        text-align: left;
    }
}
</style>

<?php get_footer(); ?>
