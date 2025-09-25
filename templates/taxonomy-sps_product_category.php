<?php
/**
 * Template untuk halaman taxonomy kategori produk
 * 
 * Template ini akan digunakan untuk menampilkan produk berdasarkan kategori
 * 
 * @package Simple Product Showcase
 */

// Mencegah akses langsung
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="sps-category-products-wrapper">
    <div class="container">
        <div class="sps-category-products">
            
            <?php
            // Header kategori
            $current_term = get_queried_object();
            ?>
            <header class="sps-category-header">
                <h1 class="sps-category-title">
                    <?php single_term_title(); ?>
                </h1>
                
                <?php if (term_description()) : ?>
                    <div class="sps-category-description">
                        <?php echo term_description(); ?>
                    </div>
                <?php endif; ?>
                
                <div class="sps-category-meta">
                    <span class="sps-product-count">
                        <?php
                        printf(
                            _n('%d product', '%d products', $current_term->count, 'simple-product-showcase'),
                            $current_term->count
                        );
                        ?>
                    </span>
                </div>
            </header>
            
            <?php
            // Breadcrumb
            ?>
            <nav class="sps-breadcrumb">
                <a href="<?php echo home_url(); ?>"><?php _e('Home', 'simple-product-showcase'); ?></a>
                <span class="sps-breadcrumb-separator">/</span>
                <a href="<?php echo get_post_type_archive_link('sps_product'); ?>"><?php _e('Products', 'simple-product-showcase'); ?></a>
                <span class="sps-breadcrumb-separator">/</span>
                <span class="sps-breadcrumb-current"><?php single_term_title(); ?></span>
            </nav>
            
            <?php
            // Filters
            ?>
            <div class="sps-category-filters">
                <?php
                // Search filter
                $search_query = get_search_query();
                ?>
                <div class="sps-search-filter">
                    <form method="get" class="sps-search-form">
                        <input type="hidden" name="post_type" value="sps_product" />
                        <input type="hidden" name="sps_product_category" value="<?php echo $current_term->slug; ?>" />
                        <input type="search" 
                               name="s" 
                               value="<?php echo esc_attr($search_query); ?>" 
                               placeholder="<?php _e('Search in this category...', 'simple-product-showcase'); ?>" 
                               class="sps-search-input" />
                        <button type="submit" class="sps-search-button">
                            <?php _e('Search', 'simple-product-showcase'); ?>
                        </button>
                    </form>
                </div>
                
                <?php
                // Sub-categories filter
                $sub_categories = get_terms(array(
                    'taxonomy' => 'sps_product_category',
                    'parent' => $current_term->term_id,
                    'hide_empty' => true,
                ));
                
                if (!is_wp_error($sub_categories) && !empty($sub_categories)) :
                    ?>
                    <div class="sps-subcategory-filter">
                        <label for="sps-subcategory-select"><?php _e('Sub-categories:', 'simple-product-showcase'); ?></label>
                        <select id="sps-subcategory-select" class="sps-subcategory-select" onchange="window.location.href=this.value">
                            <option value="<?php echo get_term_link($current_term); ?>">
                                <?php _e('All in this category', 'simple-product-showcase'); ?>
                            </option>
                            <?php foreach ($sub_categories as $sub_category) : ?>
                                <option value="<?php echo get_term_link($sub_category); ?>">
                                    <?php echo esc_html($sub_category->name); ?> (<?php echo $sub_category->count; ?>)
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
                                                <span class="sps-category-tag <?php echo ($category->term_id == $current_term->term_id) ? 'sps-current-category' : ''; ?>">
                                                    <?php echo esc_html($category->name); ?>
                                                </span>
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
                        <h3><?php _e('No products found in this category', 'simple-product-showcase'); ?></h3>
                        <p><?php _e('Sorry, no products were found in this category.', 'simple-product-showcase'); ?></p>
                        
                        <a href="<?php echo get_post_type_archive_link('sps_product'); ?>" class="sps-view-all-products">
                            <?php _e('View All Products', 'simple-product-showcase'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</div>

<style>
/* Additional styles for category template */
.sps-category-products-wrapper {
    padding: 20px 0;
}

.sps-category-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e1e5e9;
}

.sps-category-title {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    margin-bottom: 15px;
}

.sps-category-description {
    font-size: 16px;
    color: #666;
    line-height: 1.5;
    margin-bottom: 15px;
}

.sps-category-meta {
    color: #999;
    font-size: 14px;
}

.sps-breadcrumb {
    margin-bottom: 20px;
    font-size: 14px;
    color: #666;
}

.sps-breadcrumb a {
    color: #007cba;
    text-decoration: none;
}

.sps-breadcrumb a:hover {
    text-decoration: underline;
}

.sps-breadcrumb-separator {
    margin: 0 8px;
    color: #999;
}

.sps-breadcrumb-current {
    color: #333;
    font-weight: 500;
}

.sps-category-filters {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
    align-items: center;
}

.sps-subcategory-filter {
    min-width: 200px;
}

.sps-subcategory-filter label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.sps-subcategory-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background: #fff;
    cursor: pointer;
}

.sps-subcategory-select:focus {
    outline: none;
    border-color: #007cba;
    box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
}

.sps-current-category {
    background: #007cba !important;
    color: #fff !important;
}

@media (max-width: 768px) {
    .sps-category-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .sps-subcategory-filter {
        min-width: auto;
    }
    
    .sps-category-title {
        font-size: 24px;
    }
}
</style>

<?php get_footer(); ?>
