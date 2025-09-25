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
                    <h1 class="sps-product-title"><?php echo esc_html($product_title); ?></h1>
                    
                    <?php if ($product_price) : ?>
                        <div class="sps-product-price"><?php echo esc_html($product_price); ?></div>
                    <?php endif; ?>
                    
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
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="sps-product-image">
                            <?php the_post_thumbnail('large', array('alt' => $product_title)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="sps-product-details">
                        <?php if (get_the_content()) : ?>
                            <div class="sps-product-description">
                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        // WhatsApp contact section
                        $whatsapp_number = get_option('sps_whatsapp_number', '');
                        if (!empty($whatsapp_number)) :
                            
                            // Get custom message for this product or use global message
                            $custom_message = get_post_meta(get_the_ID(), '_sps_whatsapp_message', true);
                            $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}');
                            
                            $message = !empty($custom_message) ? $custom_message : $global_message;
                            
                            // Replace placeholders
                            $message = str_replace('{product_link}', get_permalink(), $message);
                            $message = str_replace('{product_title}', $product_title, $message);
                            
                            // URL encode the message
                            $encoded_message = urlencode($message);
                            
                            // Generate WhatsApp URL
                            $whatsapp_url = "https://wa.me/{$whatsapp_number}?text={$encoded_message}";
                            ?>
                            
                            <div class="sps-product-contact">
                                <h3><?php _e('Interested in this product?', 'simple-product-showcase'); ?></h3>
                                <p><?php _e('Contact us via WhatsApp for more information or to place an order.', 'simple-product-showcase'); ?></p>
                                
                                <a href="<?php echo esc_url($whatsapp_url); ?>" 
                                   target="_blank" 
                                   rel="noopener" 
                                   class="sps-whatsapp-button">
                                    <span class="sps-whatsapp-icon">📱</span>
                                    <?php _e('Contact via WhatsApp', 'simple-product-showcase'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
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
