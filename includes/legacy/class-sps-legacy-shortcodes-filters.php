<?php
/**
 * SPS Legacy Shortcodes Filters
 * 
 * Class untuk shortcode dengan filters (products_with_filters, products_sub_category)
 * 
 * @package Simple_Product_Showcase
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPS_Legacy_Shortcodes_Filters {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}

    /**
     * Direct products with filters shortcode handler (fallback)
     */
    public function direct_products_with_filters_shortcode($atts) {
        $atts = shortcode_atts(array(
            'columns' => '3',
            'limit' => '-1',
            'orderby' => 'title',
            'order' => 'ASC',
            'show_price' => 'true',
            'show_description' => 'false',
            'show_whatsapp' => 'true'
        ), $atts, 'sps_products_with_filters');
        
        $categories = get_terms(array(
            'taxonomy' => 'sps_product_category',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        $current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $current_url = remove_query_arg('category');
        
        ob_start();
        ?>
        <style>
        .sps-filter-container { margin: 30px 0; }
        .sps-filter-tabs { display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-bottom: 30px; padding: 20px; background: transparent; border-radius: 8px; }
        .sps-filter-tab { display: inline-block; padding: 12px 24px; background: #ffffff; color: #333333; text-decoration: none; border-radius: 25px; font-weight: 500; transition: all 0.3s ease; border: 2px solid #e0e0e0; cursor: pointer; font-size: 14px; }
        .sps-filter-tab:hover { background: #f5f5f5; color: #000000; text-decoration: none; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .sps-filter-tab.active { background: #FDB913; color: #000000; border-color: #FDB913; font-weight: 600; }
        .sps-filter-tab.active:hover { background: #E5A711; border-color: #E5A711; }
        .sps-products-grid { display: grid; grid-template-columns: repeat(<?php echo intval($atts['columns']); ?>, 1fr); gap: 30px; margin: 20px 0; justify-items: center; }
        .sps-no-category-message { text-align: center; padding: 60px 20px; background: #f9f9f9; border-radius: 8px; color: #666; font-size: 18px; }
        .sps-no-category-message p { margin: 0; font-weight: 500; }
        @media (max-width: 768px) { .sps-filter-tabs { gap: 10px; padding: 15px; } .sps-filter-tab { padding: 10px 18px; font-size: 13px; } .sps-products-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; } }
        @media (max-width: 480px) { .sps-products-grid { grid-template-columns: 1fr; } }
        </style>
        
        <div class="sps-filter-container">
            <div class="sps-filter-tabs">
                <?php
                if (!empty($categories) && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $category_url = add_query_arg('category', $category->slug, $current_url);
                        $active_class = ($current_category === $category->slug) ? 'active' : '';
                        ?>
                        <a href="<?php echo esc_url($category_url); ?>" class="sps-filter-tab <?php echo esc_attr($active_class); ?>"><?php echo esc_html($category->name); ?></a>
                        <?php
                    }
                } else {
                    echo '<p class="sps-no-filters">' . __('No categories available.', 'simple-product-showcase') . '</p>';
                }
                ?>
            </div>
            
            <?php
            if (!empty($current_category)) {
                echo do_shortcode('[sps_products category="' . esc_attr($current_category) . '" columns="' . esc_attr($atts['columns']) . '" limit="' . esc_attr($atts['limit']) . '" orderby="' . esc_attr($atts['orderby']) . '" order="' . esc_attr($atts['order']) . '"]');
            } else {
                echo '<div class="sps-no-category-message"><p>' . __('Please select a category to view products.', 'simple-product-showcase') . '</p></div>';
            }
            ?>
        </div>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Direct products with sub category shortcode handler (NEW)
     */
    public function direct_products_sub_category_shortcode($atts) {
        $atts = shortcode_atts(array(
            'columns' => '3',
            'limit' => '-1',
            'orderby' => 'title',
            'order' => 'ASC',
            'show_price' => 'true',
            'show_description' => 'false',
            'show_whatsapp' => 'true'
        ), $atts, 'sps_products_sub_category');
        
        $current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $current_sub_category = isset($_GET['sub_category']) ? sanitize_text_field($_GET['sub_category']) : '';
        $current_query = isset($_GET['query']) ? sanitize_text_field($_GET['query']) : '';
        
        $current_url = remove_query_arg(array('category', 'sub_category', 'query'));
        $grid_columns = intval($atts['columns']);
        if ($grid_columns < 1 || $grid_columns > 6) { $grid_columns = 3; }
        
        ob_start();
        ?>
        <style>
        .sps-sub-category-container { margin: 30px 0; }
        .sps-sub-category-tabs { display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-bottom: 30px; padding: 20px; background: transparent; border-radius: 8px; }
        .sps-sub-category-tab { display: inline-block; padding: 12px 24px; background: #ffffff; color: #333333; text-decoration: none; border-radius: 25px; font-weight: 500; transition: all 0.3s ease; border: 2px solid #e0e0e0; cursor: pointer; font-size: 14px; }
        .sps-sub-category-tab:hover { background: #f5f5f5; color: #000000; text-decoration: none; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .sps-sub-category-tab.active { background: #FDB913; color: #000000; border-color: #FDB913; font-weight: 600; }
        .sps-sub-category-tab.active:hover { background: #E5A711; border-color: #E5A711; }
        .sps-no-category-message { text-align: center; padding: 60px 20px; background: #f9f9f9; border-radius: 8px; color: #666; font-size: 18px; }
        .sps-no-category-message p { margin: 0; font-weight: 500; }
        .sps-no-sub-category-message { text-align: center; padding: 60px 20px; background: #fff9e6; border-radius: 8px; color: #666; font-size: 18px; border: 2px dashed #FDB913; }
        .sps-no-sub-category-message p { margin: 0; font-weight: 500; }
        @media (max-width: 768px) { .sps-sub-category-tabs { gap: 10px; padding: 15px; } .sps-sub-category-tab { padding: 10px 18px; font-size: 13px; } }
        .sps-search-container { margin: 20px 0 30px; max-width: 600px; margin-left: auto; margin-right: auto; position: relative; }
        .sps-search-wrapper { position: relative; display: flex; align-items: center; background: #ffffff; border: 2px solid #e0e0e0; border-radius: 30px; padding: 8px 15px; transition: all 0.3s ease; margin: 0; }
        .sps-search-wrapper:focus-within { border-color: #FDB913; box-shadow: 0 0 0 3px rgba(253, 185, 19, 0.1); }
        .sps-search-input { flex: 1; border: none; outline: none; padding: 8px 12px; font-size: 14px; background: transparent; color: #333333; }
        .sps-search-input::placeholder { color: #999999; }
        .sps-search-button { background: #FDB913; color: #000000; border: none; padding: 10px 24px; border-radius: 20px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s ease; white-space: nowrap; margin-left: 10px; }
        .sps-search-button:hover { background: #E5A711; transform: translateY(-1px); box-shadow: 0 2px 8px rgba(253, 185, 19, 0.3); }
        .sps-search-button:active { transform: translateY(0); }
        .sps-autocomplete-results { position: absolute; top: 100%; left: 0; right: 0; background: #ffffff; border: 2px solid #e0e0e0; border-top: none; border-radius: 0 0 15px 15px; max-height: 300px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .sps-autocomplete-results.show { display: block; }
        @media (max-width: 768px) { .sps-search-container { max-width: 100%; margin-left: 15px; margin-right: 15px; } .sps-search-button { padding: 8px 16px; font-size: 13px; } }
        .sps-products-grid { display: grid; grid-template-columns: repeat(<?php echo $grid_columns; ?>, 1fr); gap: 30px; margin: 20px 0; justify-items: center; }
        .sps-product-item { display: flex; flex-direction: column; align-items: center; max-width: 300px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 15px; }
        .sps-product-image { margin-bottom: 15px; text-align: center; }
        .sps-product-image img { max-width: 100%; height: auto; border-radius: 4px; }
        .sps-product-info { display: flex; justify-content: space-between; align-items: center; width: 100%; gap: 15px; }
        .sps-product-title { margin: 0; flex: 1; }
        .sps-product-title-text { color: #333; font-weight: 600; font-size: 16px; margin: 0; line-height: 1.3; }
        .sps-detail-button { background: linear-gradient(to bottom, #FFEB3B, #FFD700); color: #333; padding: 10px 24px; border: none; border-radius: 20px; text-decoration: none; display: inline-block; font-weight: 600; font-size: 14px; transition: all 0.3s ease; box-shadow: 0 3px 6px rgba(255, 215, 0, 0.4); min-width: 80px; text-align: center; }
        .sps-detail-button:hover { background: linear-gradient(to bottom, #FFF176, #FFEB3B); color: #333; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(255, 215, 0, 0.5); }
        .sps-detail-button:active { transform: translateY(0); }
        @media (max-width: 1024px) and (min-width: 769px) { .sps-products-grid { grid-template-columns: repeat(<?php echo min($grid_columns, 3); ?>, 1fr); gap: 20px; } .sps-product-item { padding: 20px; min-height: 280px; } }
        @media (max-width: 768px) { .sps-products-grid { grid-template-columns: repeat(<?php echo min($grid_columns, 2); ?>, 1fr); gap: 25px; } }
        @media (max-width: 480px) { .sps-products-grid { grid-template-columns: 1fr; gap: 20px; } .sps-product-item { max-width: 100%; padding: 12px; } .sps-product-info { flex-direction: column; align-items: center; gap: 10px; } .sps-product-title { text-align: center; } }
        </style>
        
        <div class="sps-sub-category-container">
            <div class="sps-search-container">
                <form method="get" class="sps-search-wrapper" id="sps-search-form">
                    <input type="text" class="sps-search-input" id="sps-product-search" name="query" placeholder="<?php esc_attr_e('Cari produk...', 'simple-product-showcase'); ?>" value="<?php echo esc_attr($current_query); ?>" data-category="<?php echo esc_attr($current_category); ?>" data-sub-category="<?php echo esc_attr($current_sub_category); ?>">
                    <button type="submit" class="sps-search-button" id="sps-search-button"><?php _e('Cari', 'simple-product-showcase'); ?></button>
                    <div class="sps-autocomplete-results" id="sps-autocomplete-results"></div>
                </form>
            </div>
            <?php
            
            if (empty($current_category)) {
                if (!empty($current_query)) {
                    $args = array(
                        'post_type' => 'sps_product',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        'orderby' => $atts['orderby'],
                        'order' => $atts['order']
                    );
                    
                    $products_query = new WP_Query($args);
                    $filtered_products = array();
                    if ($products_query->have_posts()) {
                        $search_term = strtolower(trim($current_query));
                        while ($products_query->have_posts()) {
                            $products_query->the_post();
                            $post_id = get_the_ID();
                            $product_obj = get_post($post_id);
                            if (!$product_obj) continue;
                            $title = strtolower($product_obj->post_title);
                            if (strpos($title, $search_term) !== false) {
                                $filtered_products[] = $product_obj;
                            }
                        }
                        wp_reset_postdata();
                        $limit = intval($atts['limit']);
                        if ($limit > 0 && count($filtered_products) > $limit) {
                            $filtered_products = array_slice($filtered_products, 0, $limit);
                        }
                    }
                    
                    if (!empty($filtered_products)) {
                        ?>
                        <div class="sps-products-grid">
                            <?php
                            foreach ($filtered_products as $product_obj) {
                                setup_postdata($product_obj);
                                ?>
                                <div class="sps-product-item">
                                    <?php if (has_post_thumbnail($product_obj->ID)) : ?>
                                        <div class="sps-product-image">
                                            <?php echo get_the_post_thumbnail($product_obj->ID, 'medium', array('alt' => get_the_title($product_obj->ID))); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="sps-product-info">
                                        <div class="sps-product-title">
                                            <p class="sps-product-title-text"><?php echo get_the_title($product_obj->ID); ?></p>
                                        </div>
                                        <?php $detail_url = SPS_Configuration::get_product_detail_url($product_obj->ID); ?>
                                        <a href="<?php echo esc_url($detail_url); ?>" class="sps-detail-button">Detail</a>
                                    </div>
                                </div>
                                <?php
                            }
                            wp_reset_postdata();
                            ?>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="sps-no-category-message">
                            <p><?php _e('Tidak ada produk yang ditemukan untuk kata kunci: ', 'simple-product-showcase'); ?><strong><?php echo esc_html($current_query); ?></strong></p>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="sps-no-category-message">
                        <p><?php _e('Silakan pilih kategori utama terlebih dahulu', 'simple-product-showcase'); ?></p>
                    </div>
                    <?php
                }
            } else {
                $parent_term = get_term_by('slug', $current_category, 'sps_product_category');
                
                if ($parent_term && !is_wp_error($parent_term)) {
                    $sub_categories = get_terms(array(
                        'taxonomy' => 'sps_product_category',
                        'hide_empty' => true,
                        'parent' => $parent_term->term_id,
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));
                    
                    if (!empty($sub_categories) && !is_wp_error($sub_categories)) {
                        ?>
                        <div class="sps-sub-category-tabs">
                            <?php
                            foreach ($sub_categories as $sub_category) {
                                $sub_category_url = add_query_arg(array('category' => $current_category, 'sub_category' => $sub_category->slug), $current_url);
                                $active_class = ($current_sub_category === $sub_category->slug) ? 'active' : '';
                                ?>
                                <a href="<?php echo esc_url($sub_category_url); ?>" class="sps-sub-category-tab <?php echo esc_attr($active_class); ?>"><?php echo esc_html($sub_category->name); ?></a>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    
                    if (!empty($current_sub_category)) {
                        $sub_term = get_term_by('slug', $current_sub_category, 'sps_product_category');
                        if ($sub_term && !is_wp_error($sub_term) && $sub_term->parent == $parent_term->term_id) {
                            echo do_shortcode('[sps_products category="' . esc_attr($current_sub_category) . '" columns="' . esc_attr($atts['columns']) . '" limit="' . esc_attr($atts['limit']) . '" orderby="' . esc_attr($atts['orderby']) . '" order="' . esc_attr($atts['order']) . '"]');
                        } else {
                            ?><div class="sps-no-sub-category-message"><p><?php _e('Sub kategori tidak valid', 'simple-product-showcase'); ?></p></div><?php
                        }
                    } else {
                        echo do_shortcode('[sps_products category="' . esc_attr($current_category) . '" columns="' . esc_attr($atts['columns']) . '" limit="' . esc_attr($atts['limit']) . '" orderby="' . esc_attr($atts['orderby']) . '" order="' . esc_attr($atts['order']) . '"]');
                    }
                } else {
                    ?><div class="sps-no-category-message"><p><?php _e('Kategori tidak ditemukan', 'simple-product-showcase'); ?></p></div><?php
                }
            }
            ?>
        </div>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Fallback shortcode handler untuk sub category
     */
    public function fallback_products_sub_category_shortcode($atts) {
        $atts = shortcode_atts(array(
            'columns' => '3',
            'limit' => '-1',
            'orderby' => 'title',
            'order' => 'ASC',
            'show_price' => 'true',
            'show_description' => 'false',
            'show_whatsapp' => 'true'
        ), $atts, 'sps_products_sub_category');
        
        $current_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $current_sub_category = isset($_GET['sub_category']) ? sanitize_text_field($_GET['sub_category']) : '';
        $current_url = remove_query_arg(array('category', 'sub_category'));
        $columns = intval($atts['columns']);
        if ($columns < 1 || $columns > 6) { $columns = 3; }
        
        ob_start();
        ?>
        <style>
        .sps-sub-category-container { margin: 30px 0; }
        .sps-sub-category-tabs { display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-bottom: 30px; padding: 20px; background: transparent; border-radius: 8px; }
        .sps-sub-category-tab { display: inline-block; padding: 12px 24px; background: #ffffff; color: #333333; text-decoration: none; border-radius: 25px; font-weight: 500; transition: all 0.3s ease; border: 2px solid #e0e0e0; cursor: pointer; font-size: 14px; }
        .sps-sub-category-tab:hover { background: #f5f5f5; color: #000000; text-decoration: none; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .sps-sub-category-tab.active { background: #FDB913; color: #000000; border-color: #FDB913; font-weight: 600; }
        .sps-sub-category-tab.active:hover { background: #E5A711; border-color: #E5A711; }
        .sps-no-category-message { text-align: center; padding: 60px 20px; background: #f9f9f9; border-radius: 8px; color: #666; font-size: 18px; }
        .sps-no-category-message p { margin: 0; font-weight: 500; }
        .sps-no-sub-category-message { text-align: center; padding: 60px 20px; background: #fff9e6; border-radius: 8px; color: #666; font-size: 18px; border: 2px dashed #FDB913; }
        .sps-no-sub-category-message p { margin: 0; font-weight: 500; }
        @media (max-width: 768px) { .sps-sub-category-tabs { gap: 10px; padding: 15px; } .sps-sub-category-tab { padding: 10px 18px; font-size: 13px; } }
        </style>
        
        <div class="sps-sub-category-container">
            <?php
            if (empty($current_category)) {
                ?><div class="sps-no-category-message"><p><?php _e('Silakan pilih kategori utama terlebih dahulu', 'simple-product-showcase'); ?></p></div><?php
            } else {
                $parent_term = get_term_by('slug', $current_category, 'sps_product_category');
                
                if ($parent_term && !is_wp_error($parent_term)) {
                    $sub_categories = get_terms(array(
                        'taxonomy' => 'sps_product_category',
                        'hide_empty' => true,
                        'parent' => $parent_term->term_id,
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));
                    
                    if (!empty($sub_categories) && !is_wp_error($sub_categories)) {
                        ?>
                        <div class="sps-sub-category-tabs">
                            <?php
                            foreach ($sub_categories as $sub_category) {
                                $sub_category_url = add_query_arg(array('category' => $current_category, 'sub_category' => $sub_category->slug), $current_url);
                                $active_class = ($current_sub_category === $sub_category->slug) ? 'active' : '';
                                ?>
                                <a href="<?php echo esc_url($sub_category_url); ?>" class="sps-sub-category-tab <?php echo esc_attr($active_class); ?>"><?php echo esc_html($sub_category->name); ?></a>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    
                    if (!empty($current_sub_category)) {
                        $sub_term = get_term_by('slug', $current_sub_category, 'sps_product_category');
                        if ($sub_term && !is_wp_error($sub_term) && $sub_term->parent == $parent_term->term_id) {
                            $products_atts = array_merge($atts, array('category' => $current_sub_category));
                            echo SPS_Legacy_Shortcodes::get_instance()->fallback_products_shortcode($products_atts);
                        } else {
                            ?><div class="sps-no-sub-category-message"><p><?php _e('Sub kategori tidak valid', 'simple-product-showcase'); ?></p></div><?php
                        }
                    } else {
                        $products_atts = array_merge($atts, array('category' => $current_category));
                        echo SPS_Legacy_Shortcodes::get_instance()->fallback_products_shortcode($products_atts);
                    }
                } else {
                    ?><div class="sps-no-category-message"><p><?php _e('Kategori tidak ditemukan', 'simple-product-showcase'); ?></p></div><?php
                }
            }
            ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
}

