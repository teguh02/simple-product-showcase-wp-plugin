<?php
/**
 * Template untuk halaman archive produk
 * 
 * Template ini akan digunakan untuk menampilkan daftar semua produk
 * 
 * @package Simple Product Showcase
 */

// Mencegah akses langsung
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="sps-archive-products-wrapper">
    <div class="container">
        <div class="sps-archive-products">
            
            <?php
            // Header archive
            ?>
            <header class="sps-archive-header">
                <h1 class="sps-archive-title">
                    <?php
                    if (is_tax('sps_product_category')) {
                        single_term_title();
                    } else {
                        _e('Our Products', 'simple-product-showcase');
                    }
                    ?>
                </h1>
                
                <?php if (is_post_type_archive('sps_product')) : ?>
                    <div class="sps-archive-description">
                        <p><?php _e('Browse through our collection of products. Click on any product to learn more or contact us via WhatsApp.', 'simple-product-showcase'); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (is_tax('sps_product_category')) : ?>
                    <div class="sps-archive-description">
                        <p><?php echo term_description(); ?></p>
                    </div>
                <?php endif; ?>
            </header>
            
            <?php
            // Filters
            ?>
            <div class="sps-archive-filters">
                <?php
                // Search filter
                $search_query = get_search_query();
                ?>
                <div class="sps-search-filter">
                    <form method="get" class="sps-search-form">
                        <input type="hidden" name="post_type" value="sps_product" />
                        <input type="search" 
                               name="s" 
                               value="<?php echo esc_attr($search_query); ?>" 
                               placeholder="<?php _e('Search products...', 'simple-product-showcase'); ?>" 
                               class="sps-search-input" />
                        <button type="submit" class="sps-search-button">
                            <?php _e('Search', 'simple-product-showcase'); ?>
                        </button>
                    </form>
                </div>
                
                <?php
                // Category filter
                $categories = get_terms(array(
                    'taxonomy' => 'sps_product_category',
                    'hide_empty' => true,
                ));
                
                if (!is_wp_error($categories) && !empty($categories)) :
                    $current_category = '';
                    if (is_tax('sps_product_category')) {
                        $current_category = get_queried_object()->slug;
                    }
                    ?>
                    <div class="sps-category-filter">
                        <select class="sps-category-select" onchange="window.location.href=this.value">
                            <option value="<?php echo get_post_type_archive_link('sps_product'); ?>">
                                <?php _e('All Categories', 'simple-product-showcase'); ?>
                            </option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?php echo get_term_link($category); ?>" 
                                        <?php selected($current_category, $category->slug); ?>>
                                    <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php
            // Products grid
            ?>
            <div class="sps-products-container">
                <?php if (have_posts()) : ?>
                    <div class="sps-products-grid sps-columns-3">
                        <?php while (have_posts()) : the_post(); ?>
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
                                    
                                    <?php
                                    $price = get_post_meta(get_the_ID(), '_sps_product_price', true);
                                    if ($price) :
                                    ?>
                                        <div class="sps-product-price"><?php echo esc_html($price); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $product_categories = get_the_terms(get_the_ID(), 'sps_product_category');
                                    if ($product_categories && !is_wp_error($product_categories)) :
                                    ?>
                                        <div class="sps-product-categories">
                                            <?php foreach ($product_categories as $category) : ?>
                                                <span class="sps-category-tag"><?php echo esc_html($category->name); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="sps-product-actions">
                                        <a href="<?php the_permalink(); ?>" class="sps-view-product">
                                            <?php _e('View Details', 'simple-product-showcase'); ?>
                                        </a>
                                        
                                        <?php
                                        // WhatsApp button
                                        $whatsapp_number = get_option('sps_whatsapp_number', '');
                                        if (!empty($whatsapp_number)) :
                                            
                                            // Get custom message for this product or use global message
                                            $custom_message = get_post_meta(get_the_ID(), '_sps_whatsapp_message', true);
                                            $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}');
                                            
                                            $message = !empty($custom_message) ? $custom_message : $global_message;
                                            
                                            // Replace placeholders
                                            $message = str_replace('{product_link}', get_permalink(), $message);
                                            $message = str_replace('{product_title}', get_the_title(), $message);
                                            
                                            // URL encode the message
                                            $encoded_message = urlencode($message);
                                            
                                            // Generate WhatsApp URL
                                            $whatsapp_url = "https://wa.me/{$whatsapp_number}?text={$encoded_message}";
                                            ?>
                                            
                                            <a href="<?php echo esc_url($whatsapp_url); ?>" 
                                               target="_blank" 
                                               rel="noopener" 
                                               class="sps-whatsapp-button">
                                                <span class="sps-whatsapp-icon">📱</span>
                                                <?php _e('WhatsApp', 'simple-product-showcase'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <?php
                    // Pagination
                    the_posts_pagination(array(
                        'prev_text' => __('Previous', 'simple-product-showcase'),
                        'next_text' => __('Next', 'simple-product-showcase'),
                        'mid_size' => 2,
                    ));
                    ?>
                    
                <?php else : ?>
                    <div class="sps-no-products">
                        <h3><?php _e('No products found', 'simple-product-showcase'); ?></h3>
                        <p><?php _e('Sorry, no products were found matching your criteria.', 'simple-product-showcase'); ?></p>
                        
                        <?php if (is_search()) : ?>
                            <p><?php _e('Try adjusting your search terms or browse all products.', 'simple-product-showcase'); ?></p>
                            <a href="<?php echo get_post_type_archive_link('sps_product'); ?>" class="sps-view-all-products">
                                <?php _e('View All Products', 'simple-product-showcase'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</div>

<style>
/* Additional styles for archive template */
.sps-archive-products-wrapper {
    padding: 20px 0;
}

.sps-view-all-products {
    display: inline-block;
    background: #007cba;
    color: #fff;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 4px;
    margin-top: 15px;
    transition: background-color 0.3s ease;
}

.sps-view-all-products:hover {
    background: #005a87;
    color: #fff;
}

.sps-no-products {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
}

.sps-no-products h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 24px;
}

.sps-no-products p {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 16px;
}

/* Pagination styles */
.sps-archive-products .page-numbers {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 2px;
    background: #fff;
    border: 1px solid #ddd;
    color: #007cba;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.sps-archive-products .page-numbers:hover,
.sps-archive-products .page-numbers.current {
    background: #007cba;
    color: #fff;
    border-color: #007cba;
}

.sps-archive-products .page-numbers.dots {
    background: transparent;
    border: none;
    color: #666;
}

.sps-archive-products .page-numbers.prev,
.sps-archive-products .page-numbers.next {
    font-weight: 600;
}
</style>

<?php get_footer(); ?>
