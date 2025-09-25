<?php
/**
 * Class SPS_Frontend
 * 
 * Class untuk mengelola tampilan frontend dan template
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Frontend {
    
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
        // Template hooks
        add_filter('template_include', array($this, 'template_loader'));
        add_action('template_redirect', array($this, 'template_redirect'));
        
        // Content hooks
        add_filter('the_content', array($this, 'single_product_content'));
        add_action('sps_single_product_content', array($this, 'single_product_template'));
        add_action('sps_archive_product_content', array($this, 'archive_product_template'));
        
        // Search and filter
        add_action('wp_ajax_sps_filter_products', array($this, 'ajax_filter_products'));
        add_action('wp_ajax_nopriv_sps_filter_products', array($this, 'ajax_filter_products'));
    }
    
    /**
     * Template loader
     */
    public function template_loader($template) {
        if (is_singular('sps_product')) {
            $template = $this->locate_template('single-sps_product.php');
        } elseif (is_post_type_archive('sps_product')) {
            $template = $this->locate_template('archive-sps_product.php');
        } elseif (is_tax('sps_product_category')) {
            $template = $this->locate_template('taxonomy-sps_product_category.php');
        }
        
        return $template;
    }
    
    /**
     * Locate template file
     */
    private function locate_template($template_name) {
        // Check theme directory first
        $template = locate_template(array(
            'simple-product-showcase/' . $template_name,
            $template_name
        ));
        
        // If not found in theme, use plugin template
        if (!$template) {
            $template = SPS_PLUGIN_PATH . 'templates/' . $template_name;
        }
        
        return $template;
    }
    
    /**
     * Template redirect for custom templates
     */
    public function template_redirect() {
        if (is_singular('sps_product')) {
            $this->load_single_product_template();
        } elseif (is_post_type_archive('sps_product') || is_tax('sps_product_category')) {
            $this->load_archive_product_template();
        }
    }
    
    /**
     * Load single product template
     */
    private function load_single_product_template() {
        $template = $this->locate_template('single-sps_product.php');
        
        if (file_exists($template)) {
            include $template;
            exit;
        } else {
            // Fallback to default template
            $this->single_product_fallback();
        }
    }
    
    /**
     * Load archive product template
     */
    private function load_archive_product_template() {
        $template = $this->locate_template('archive-sps_product.php');
        
        if (file_exists($template)) {
            include $template;
            exit;
        } else {
            // Fallback to default template
            $this->archive_product_fallback();
        }
    }
    
    /**
     * Single product content filter
     */
    public function single_product_content($content) {
        if (is_singular('sps_product') && in_the_loop() && is_main_query()) {
            ob_start();
            do_action('sps_single_product_content');
            $product_content = ob_get_clean();
            
            return $content . $product_content;
        }
        
        return $content;
    }
    
    /**
     * Single product template
     */
    public function single_product_template() {
        global $post;
        
        $product_id = $post->ID;
        $product_title = get_the_title();
        $product_content = get_the_content();
        $product_price = get_post_meta($product_id, '_sps_product_price', true);
        $product_categories = get_the_terms($product_id, 'sps_product_category');
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        ?>
        <div class="sps-single-product">
            <div class="sps-product-header">
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
            </div>
            
            <div class="sps-product-content-wrapper">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="sps-product-image">
                        <?php the_post_thumbnail('large', array('alt' => $product_title)); ?>
                    </div>
                <?php endif; ?>
                
                <div class="sps-product-details">
                    <?php if ($product_content) : ?>
                        <div class="sps-product-description">
                            <?php echo wpautop($product_content); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($whatsapp_number)) : ?>
                        <div class="sps-product-contact">
                            <h3><?php _e('Interested in this product?', 'simple-product-showcase'); ?></h3>
                            <p><?php _e('Contact us via WhatsApp for more information or to place an order.', 'simple-product-showcase'); ?></p>
                            <?php echo $this->get_whatsapp_button($product_id, $product_title, get_permalink()); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Archive product template
     */
    public function archive_product_template() {
        ?>
        <div class="sps-archive-products">
            <div class="sps-archive-header">
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
            </div>
            
            <div class="sps-archive-filters">
                <?php echo $this->get_search_filter(); ?>
                <?php echo $this->get_category_filter(); ?>
            </div>
            
            <div class="sps-products-container">
                <?php
                if (have_posts()) :
                    echo '<div class="sps-products-grid sps-columns-3">';
                    while (have_posts()) : the_post();
                        $this->product_loop_item();
                    endwhile;
                    echo '</div>';
                    
                    // Pagination
                    the_posts_pagination(array(
                        'prev_text' => __('Previous', 'simple-product-showcase'),
                        'next_text' => __('Next', 'simple-product-showcase'),
                    ));
                else :
                    echo '<p class="sps-no-products">' . __('No products found.', 'simple-product-showcase') . '</p>';
                endif;
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Product loop item template
     */
    private function product_loop_item() {
        global $post;
        
        $product_id = $post->ID;
        $product_title = get_the_title();
        $product_price = get_post_meta($product_id, '_sps_product_price', true);
        $product_categories = get_the_terms($product_id, 'sps_product_category');
        
        ?>
        <div class="sps-product-item">
            <?php if (has_post_thumbnail()) : ?>
                <div class="sps-product-image">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail('medium', array('alt' => $product_title)); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="sps-product-content">
                <h3 class="sps-product-title">
                    <a href="<?php the_permalink(); ?>"><?php echo esc_html($product_title); ?></a>
                </h3>
                
                <?php if ($product_price) : ?>
                    <div class="sps-product-price"><?php echo esc_html($product_price); ?></div>
                <?php endif; ?>
                
                <?php if ($product_categories && !is_wp_error($product_categories)) : ?>
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
                    <?php echo $this->get_whatsapp_button($product_id, $product_title, get_permalink()); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get WhatsApp button
     */
    private function get_whatsapp_button($product_id, $product_title, $product_url) {
        $whatsapp_number = get_option('sps_whatsapp_number', '');
        
        if (empty($whatsapp_number)) {
            return '';
        }
        
        // Get custom message for this product or use global message
        $custom_message = get_post_meta($product_id, '_sps_whatsapp_message', true);
        $global_message = get_option('sps_whatsapp_message', 'Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}');
        
        $message = !empty($custom_message) ? $custom_message : $global_message;
        
        // Replace placeholders
        $message = str_replace('{product_link}', $product_url, $message);
        $message = str_replace('{product_title}', $product_title, $message);
        
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
            __('WhatsApp', 'simple-product-showcase')
        );
    }
    
    /**
     * Get search filter
     */
    private function get_search_filter() {
        $search_query = get_search_query();
        
        return sprintf(
            '<div class="sps-search-filter">
                <form method="get" class="sps-search-form">
                    <input type="hidden" name="post_type" value="sps_product" />
                    <input type="search" name="s" value="%s" placeholder="%s" class="sps-search-input" />
                    <button type="submit" class="sps-search-button">%s</button>
                </form>
            </div>',
            esc_attr($search_query),
            __('Search products...', 'simple-product-showcase'),
            __('Search', 'simple-product-showcase')
        );
    }
    
    /**
     * Get category filter
     */
    private function get_category_filter() {
        $categories = get_terms(array(
            'taxonomy' => 'sps_product_category',
            'hide_empty' => true,
        ));
        
        if (is_wp_error($categories) || empty($categories)) {
            return '';
        }
        
        $current_category = '';
        if (is_tax('sps_product_category')) {
            $current_category = get_queried_object()->slug;
        }
        
        $options = '<option value="">' . __('All Categories', 'simple-product-showcase') . '</option>';
        foreach ($categories as $category) {
            $selected = selected($current_category, $category->slug, false);
            $options .= sprintf(
                '<option value="%s" %s>%s (%d)</option>',
                esc_attr($category->slug),
                $selected,
                esc_html($category->name),
                $category->count
            );
        }
        
        return sprintf(
            '<div class="sps-category-filter">
                <select class="sps-category-select" onchange="window.location.href=this.value">
                    %s
                </select>
            </div>',
            $options
        );
    }
    
    /**
     * AJAX filter products
     */
    public function ajax_filter_products() {
        check_ajax_referer('sps_nonce', 'nonce');
        
        $category = sanitize_text_field($_POST['category'] ?? '');
        $search = sanitize_text_field($_POST['search'] ?? '');
        
        $args = array(
            'post_type' => 'sps_product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        
        if (!empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'sps_product_category',
                    'field' => 'slug',
                    'terms' => $category
                )
            );
        }
        
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        if ($query->have_posts()) {
            echo '<div class="sps-products-grid sps-columns-3">';
            while ($query->have_posts()) {
                $query->the_post();
                $this->product_loop_item();
            }
            echo '</div>';
        } else {
            echo '<p class="sps-no-products">' . __('No products found.', 'simple-product-showcase') . '</p>';
        }
        wp_reset_postdata();
        
        wp_send_json_success(ob_get_clean());
    }
    
    /**
     * Single product fallback template
     */
    private function single_product_fallback() {
        get_header();
        ?>
        <div class="container">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
                    do_action('sps_single_product_content');
                endwhile;
            endif;
            ?>
        </div>
        <?php
        get_footer();
        exit;
    }
    
    /**
     * Archive product fallback template
     */
    private function archive_product_fallback() {
        get_header();
        ?>
        <div class="container">
            <?php do_action('sps_archive_product_content'); ?>
        </div>
        <?php
        get_footer();
        exit;
    }
}
