<?php
/**
 * Force Shortcode Registration
 * Access via: wp.test/wp-content/plugins/simple-product-showcase/force-shortcode-registration.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<h1>Force Shortcode Registration</h1>';

// Check if plugin is active
$active_plugins = get_option('active_plugins');
$plugin_file = 'simple-product-showcase/simple-product-showcase.php';

if (in_array($plugin_file, $active_plugins)) {
    echo '<p>✅ Plugin is active</p>';
    
    // Force register shortcode immediately
    echo '<p>🔄 Force registering shortcode...</p>';
    
    add_shortcode('sps_products', function($atts) {
        // Default attributes
        $atts = shortcode_atts(array(
            'columns' => '3',
            'category' => '',
            'limit' => '-1',
            'orderby' => 'date',
            'order' => 'DESC',
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
            return '<p class="sps-no-products">No products found.</p>';
        }
        
        // Start output
        ob_start();
        ?>
        <style>
        .sps-products-grid {
            display: grid;
            grid-template-columns: repeat(<?php echo $columns; ?>, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        .sps-product-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .sps-product-image img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .sps-product-title a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            font-size: 18px;
        }
        .sps-product-title a:hover {
            color: #0073aa;
        }
        .sps-product-price {
            color: #0073aa;
            font-weight: bold;
            margin: 10px 0;
            font-size: 16px;
        }
        .sps-product-description {
            margin: 10px 0;
            color: #666;
            line-height: 1.4;
        }
        .sps-whatsapp-button {
            background-color: #25D366;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        .sps-whatsapp-button:hover {
            background-color: #128C7E;
            color: white;
        }
        @media (max-width: 768px) {
            .sps-products-grid {
                grid-template-columns: repeat(<?php echo min($columns, 2); ?>, 1fr);
                gap: 15px;
            }
        }
        @media (max-width: 480px) {
            .sps-products-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <div class="sps-products-grid">
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
                                <?php 
                                $whatsapp_number = get_option('sps_whatsapp_number', '');
                                if (!empty($whatsapp_number)) {
                                    $message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tentang produk {product_name} ini yaa: {product_link}');
                                    $message = str_replace('{product_link}', get_permalink(), $message);
                                    $message = str_replace('{product_name}', get_the_title(), $message);
                                    $encoded_message = urlencode($message);
                                    $whatsapp_url = "https://wa.me/{$whatsapp_number}?text={$encoded_message}";
                                    echo '<a href="' . esc_url($whatsapp_url) . '" target="_blank" rel="noopener" class="sps-whatsapp-button">📱 Contact via WhatsApp</a>';
                                } else {
                                    echo '<p style="color: #666; font-size: 12px;">WhatsApp not configured</p>';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php
        // Reset post data
        wp_reset_postdata();
        
        return ob_get_clean();
    });
    
    echo '<p>✅ Shortcode registered successfully!</p>';
    
    // Check if shortcode is now registered
    global $shortcode_tags;
    if (isset($shortcode_tags['sps_products'])) {
        echo '<p>✅ sps_products shortcode is now registered</p>';
    } else {
        echo '<p>❌ sps_products shortcode is still NOT registered</p>';
    }
    
    // Test the shortcode
    echo '<h2>Testing Shortcode:</h2>';
    echo do_shortcode('[sps_products]');
    
} else {
    echo '<p>❌ Plugin is not active</p>';
}
?>
