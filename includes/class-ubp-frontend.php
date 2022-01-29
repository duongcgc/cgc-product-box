<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly. 
}
if (!class_exists('UBP_Box_Product_Frontend')) {
    class UBP_Box_Product_Frontend
    {
        public function __construct()
        {
            add_filter('wc_get_template_part', array($this, 'ubp_custom_product_template'), 999, 3);
            add_action('ubp_custom_box_product_layout', array($this, 'ubp_product_layouts'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_front_scripts'));
            add_action('ubp_after_box_layout_content', array($this, 'ubp_quantity_button'));
            $value = get_option('ubp_mix_match_box_short_description');
            if ($value == 'above_title') {
                add_action('ubp_before_box_product_title', array($this, 'ubp_print_short_description'));
            } elseif ($value == 'below_title') {
                add_action('ubp_after_box_product_title', array($this, 'ubp_print_short_description'));
            } elseif ($value == 'below_layout') {
                add_action('ubp_after_box_product_layout', array($this, 'ubp_print_short_description'));
            }
            add_action('wp_head', array($this, 'add_styles'), 100);
            add_action('wp_footer', array($this, 'ubp_add_footer_data'));

            add_action('woocommerce_cart_updated', array($this, 'update_cart_action'), 10);
            add_action( 'woocommerce_restore_cart_item', array($this, 'restore_cart_item'), 99, 2 );
        }

        public function update_cart_action()
        {
            if (!(isset($_REQUEST['remove_item']))) {
                return;
            }

            wc_nocache_headers();

            $nonce_value = wc_get_var($_REQUEST['woocommerce-cart-nonce'], wc_get_var($_REQUEST['_wpnonce'], '')); // @codingStandardsIgnoreLine.

            if (!empty($_GET['remove_item']) && wp_verify_nonce($nonce_value, 'woocommerce-cart')) {
                $cart_item_key = sanitize_text_field(wp_unslash($_GET['remove_item']));
                $cart_item = WC()->cart->cart_contents[$cart_item_key];

                if ($cart_item && isset($cart_item['ubp-bundle-add-to-cart']) && !empty($cart_item['ubp-bundle-add-to-cart'])) {
                    $this->before_cart_item_removed($cart_item_key, $cart_item);
                }
            }
        }

        private function before_cart_item_removed($key, $cart_item)
        {
            if (isset($cart_item['ubp_box_product_keys']) && !empty($cart_item['ubp_box_product_keys'])){
                foreach ($cart_item['ubp_box_product_keys'] as $msb_key){
                    $msb_key_data[ $msb_key ] = WC()->cart->cart_contents[ $msb_key ];
                }
            }

            set_transient($key . '__cart_item_data', $cart_item['data'], 3600);
            set_transient($key . '__key', $key, 3600);
            set_transient($key . '__cart_item', $cart_item, 3600);
            set_transient($key . '__cart_item_child_data', $msb_key_data, 3600);
        }

        public function restore_cart_item($cart_item_key, $instance)
        {
            if (null !== get_transient($cart_item_key . '__cart_item_data') && !empty(get_transient($cart_item_key . '__cart_item_data'))){
                $instance->cart_contents[ $cart_item_key ] = get_transient($cart_item_key . '__cart_item');
                $instance->cart_contents[ $cart_item_key ] = get_transient($cart_item_key . '__cart_item');

                if (null !== get_transient($cart_item_key . '__cart_item_child_data') && !empty(get_transient($cart_item_key . '__cart_item_child_data'))){
                    $childs = get_transient($cart_item_key . '__cart_item_child_data');
                    foreach ($childs as $key => $child){
                        $instance->cart_contents[ $key ] = $child;
                    }
                }
            }
        }

        public function ubp_custom_product_template($template, $slug, $name)
        {
            global $product;

            if (is_singular('product') && $name === 'single-product' && strpos($slug, 'content') !== false && $product->get_type() === 'box_product') {
                $template = apply_filters('ubp_box_product_template', WOO_UBP_DIR . '/templates/content-single-product.php');
            }

            return $template;
        }

        public function ubp_product_layouts($product_id)
        {
            
            $product_id = is_numeric($product_id) ? $product_id : get_the_ID();

            // get custom product layout
            $cgc_layout = get_post_meta($product_id, 'cgc_ubp_box_layout', true);

            $layout = get_post_meta($product_id, 'ubp_box_layout', true);
            $layout = !empty($layout) ? $layout : 'vertical_left';
            echo '<div id="ubp_cutom_box_product_layouts" class="' . $layout . ' ' . $cgc_layout . '">';
            $this->print_title($product_id);
            $this->ubp_box_layout($product_id, $cgc_layout);
            $this->ubp_product_layout($product_id);
            echo '</div>';
        }

        // Render Product Layout Custom 

        public function ubp_box_layout($product_id = '', $custom_layout)        {
            
            $max = get_post_meta($product_id, 'ubp_box_max_products', true);
            $max = ($max <= 0 || $max == '') ? 6 : absint($max);
            $prefilled = get_post_meta($product_id, 'ubp_all_prefill_products', true);
            $columns = get_post_meta($product_id, 'ubp_box_columns', true);
            ?>
            <div class="col-left ubp-column-<?php echo esc_attr($columns); ?>">
                <div class="ubp_box_content">
                    <?php if (!empty($max) && $max > 0): ?>
                        <ul>
                            <?php
                            if (empty($prefilled)) {
                                for ($i = 0; $i < $max; $i++) {
                                    echo '<li></li>';
                                }
                            } else {
                                for ($i = 0; $i < $max; $i++) {
                                    if (isset($prefilled[$i]) && isset($prefilled[$i]['product']) && is_numeric($prefilled[$i]['product'])) {
                                        if (isset($prefilled[$i]['qty'])) {
                                            for ($j = 0; $j < $prefilled[$i]['qty']; $j++) {
                                                $prefil_product = wc_get_product($prefilled[$i]['product']);
                                                if (!$prefil_product->is_type('variable')) {
                                                    echo '<li data-prod_id="' . esc_attr($prefilled[$i]['product']) . '" ' . ($prefilled[$i]['mandatory'] == 'yes' ? 'data-need="yes"' : '') . '>' . wp_get_attachment_image(get_post_thumbnail_id($prefilled[$i]['product']), 'woocommerce_thumbnail') . '</li>';
                                                    if ($j == ($max - $i)) {
                                                        break;
                                                    }
                                                } else {
                                                    echo '<li></li>';
                                                }
                                            }
                                            $max = $max - ($prefilled[$i]['qty']) + 1;
                                        } else {
                                            echo '<li></li>';
                                        }
                                    } else {
                                        echo '<li></li>';
                                    }
                                }
                            } ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <?php do_action('ubp_after_box_layout_content'); ?>
            </div>
            <?php
        }

        public function ubp_product_layout($product_id = '')
        {
            $addons = get_post_meta($product_id, 'ubp_products_addons_values', true);
            $addons = json_decode($addons);
            
            if ($addons === null){
                $addons = [];
            }

            $columns = get_post_meta($product_id, 'ubp_products_columns', true);
            $qty = get_post_meta($product_id, 'ubp_show_item_qty', true);
            $show_prod = get_option('ubp_mix_match_box_max_posts_per_page');
            $show_prod = empty($show_prod) ? count($addons) : absint($show_prod);
            $qty_selector = '';
            if ($qty !== 'yes') {
                $qty_selector = 'style="display:none;opacity:0;"';
            }
            $cats_enabled = get_post_meta($product_id, 'ubp_box_product_categories_enable', true);
            $cats = get_post_meta($product_id, 'ubp_products_categories', true);
            $all_prod = array();
            if ($cats_enabled == 'yes') {
                $cat_prod_ids = array();
                if (!empty($cats)) {
                    $args = array(
                        'post_type' => array('product'),
                        'posts_per_page' => -1,
                        'fields' => 'ids',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'product_cat',
                                'field' => 'term_id',
                                'terms' => $cats,
                            )
                        )
                    );
                    $cat_prod_ids = get_posts($args);
                    if (!empty($cat_prod_ids)) {
                        $args = array(
                            'post_type' => array('product_variation'),
                            'posts_per_page' => -1,
                            'fields' => 'ids',
                            'post_parent__in' => $cat_prod_ids
                        );
                        $variation_ids = get_posts($args);
                        $all_prod = array_merge($variation_ids, $cat_prod_ids);
                    }
                }
            }
            $addons = wp_list_pluck((array) $addons, 'id');
            $all_prod = array_merge($addons, $all_prod);
            $arg = array(
                'post_type' => array('product', 'product_variation'),
                'post__in' => $all_prod,
                'posts_per_page' => $show_prod,
                'paged' => 1,
                'tax_query' => array(
                    'relation' => 'OR',
                    array(
                        'taxonomy' => 'product_type',
                        'field' => 'slug',
                        'terms' => array('simple'),
                        'operator' => 'IN',
                    ),
                    array(
                        'taxonomy' => 'product_type',
                        'operator' => 'NOT EXISTS',
                    )
                )
            );
            $products = new WP_Query($arg);
            $html = '<div class="col-right ubp-column-' . esc_attr($columns) . '">';
            if (!empty($products)) {
                $html .= '<ul>';
                while ($products->have_posts()) {
                    $products->the_post();
                    $temp = $itemHtml = '';
                    $_product = wc_get_product(get_the_ID());
                    if (!$_product->is_type('variable')) {
                        $data = array();
                        $data['id'] = $_product->get_id();
                        $data['price'] = $_product->get_price();
                        $data['purchaseable'] = ($_product->is_in_stock() && $_product->is_purchasable()) ? 1 : 0;
                        $data['max'] = $_product->get_max_purchase_quantity();
                        $disable = ($data['purchaseable'] == 1) ? '' : 'class="ubp-disabled"';
                        $itemHtml .= '<li data-product_id="' . esc_attr($_product->get_id()) . '" ' . ($disable) . '>';
                        $itemHtml .= '<figure>';
                        if (!empty($disable))
                            $itemHtml .= '<span class="outofstock_prod_box">' . apply_filters("wc_ubp_box_product_outofstock_label", esc_html__("Out of stock", "wc-ubp")) . '</span>';
                        else
                            $itemHtml .= '<span class="add_prod_box"></span>';
                        if (has_post_thumbnail($_product->get_id())) {
                            $itemHtml .= wp_get_attachment_image(get_post_thumbnail_id($_product->get_id()), 'woocommerce_thumbnail');
                        } else {
                            $itemHtml .= '<img src="' . WOO_UBP_URL . '/assets/images/placeholder.png" alt="product-image">';
                        }
                        $itemHtml .= '<div class="qty" ' . $qty_selector . '><input type="button" value="-" class="qtyminus" field="qty_' . esc_attr($_product->get_id()) . '" />
								<input type="text" name="qty_' . esc_attr($_product->get_id()) . '" value="1" >
								<input type="button" value="+" class="qtyplus" field="qty_' . esc_attr($_product->get_id()) . '" /></div>';
                        $itemHtml .= '<figcaption><a href="' . esc_url(get_the_permalink($_product->get_id())) . '">' . esc_html($_product->get_name()) . '</a>';
                        if (get_post_meta($product_id, 'ubp_show_item_pricing', true) == 'yes') {
                            $itemHtml .= '<span class="box_product_item_price">' . $_product->get_price_html() . '</span>';
                        }
                        $itemHtml .= '</figcaption>';
                        $itemHtml .= '</figure>';
                        $itemHtml .= '<input type="hidden" value="' . wc_esc_json(wp_json_encode($data)) . '">';
                        $itemHtml .= '</li>';
                    }
                    $html .= apply_filters('ubp_box_product_single_item_content', $itemHtml, $_product, wc_get_product($product_id));
                }
                $html .= '</ul>';
                if ($products->max_num_pages > 1) {
                    $html .= '<div class="wc-ubp-footer"><div class="ubp-loader-ripple"><div></div><div></div></div><button class="button" id="ubp_load_more_items" data-paged="2">' . esc_html__("Load More", "wc-ubp") . '</button></div>';
                }
            } else {
                $html .= esc_html__('Please add product addons for this box.', 'wc-ubp');
            }
            $html .= '</div>';
            echo $html;

            wp_reset_query();
        }

        public function ubp_quantity_button($product_id = '')
        {
            global $product;
            $price = 0;

            if (get_post_meta($product->get_id(), 'ubp_pricing_type', true) !== 'per_product_only') {
                if ($product->get_price() == '') {
                    $price = 0;
                } else {
                    $price += $product->get_price();
                };
            }
            // Prefilled
            $max = get_post_meta($product_id, 'ubp_box_max_products', true);
            $max = ($max <= 0 || $max == '') ? 6 : absint($max);
            $pre_ids = '';
            $prefilled = get_post_meta($product->get_id(), 'ubp_all_prefill_products', true);
            if (!empty($prefilled)) {
                $temp = array();
                foreach ($prefilled as $key => $value) {
                    $prefil_product = wc_get_product($value['product']);
                    if (!$prefil_product->is_type('variable')) {
                        for ($i = 0; $i < $value['qty']; $i++) {
                            $price += $prefil_product->get_price();
                            array_push($temp, $value['product']);
                            if ($i == ($max) - 1) {
                                break;
                            }
                        }
                    }
                }
                $pre_ids = implode(',', $temp);
            }
            if (get_post_meta($product->get_id(), 'ubp_pricing_type', true) == 'fixed_pricing') {
                $price = $product->get_price();
            }
            $price_label = get_option('ubp_mix_match_box_price_label');
            $price_label = !empty($price_label) ? $price_label : esc_html__("Box Total: ", "wc-ubp");
			
			// esc_attr(floatval($price))

            if ($product->get_price() == '') {
                $display_price = array( 'price' => 0 );
            } else {
                $display_price = array( 'price' => $product->get_price() * 1.04 ) ;
            }

            ?>

            <p class="price ubp_bundle"> <?php echo esc_attr($price_label) . '<span class="bundle_price">' . wc_price( wc_get_price_to_display( $product, $display_price ) ) . '</span>' . get_woocommerce_currency_symbol(); ?></p>
            
            <?php
            do_action('wc_ubp_box_product_after_price', $product->get_id());

            if(get_post_meta($product->get_id(),'ubp_enable_box_gift_message',true)=='yes'){
                $type=get_post_meta($product->get_id(),'ubp_enable_box_gift_field_type',true);
                $required=get_post_meta($product->get_id(),'ubp_enable_box_gift_required',true);
                $label=get_post_meta($product->get_id(),'ubp_box_message_field_label',true);
                $label=!empty($label) ? $label : esc_html__('Message', 'wc-ubp');
                $required=($required=='yes') ? 'requried' : '';
                $html='<div class="ubp_extra_field">';
                $html.='<label for="ubp_box_message_field">'.esc_html($label).'</label>';
                if($type=='textarea')
                    $html.='<span class="ubp_field"><textarea name="ubp_box_message_field" id="ubp_box_message_field" '.esc_attr($required).'></textarea></span>';
                else
                    $html.='<span class="ubp_field"><input type="text" name="ubp_box_message_field" id="ubp_box_message_field" value="" '.esc_attr($required).'></span>';
                $html.='</div>';
                echo $html;
            }

            if ($product->is_purchasable() && $product->is_in_stock()) {
                woocommerce_quantity_input(array(
                    'min_value' => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                    'max_value' => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                    'input_value' => isset($_POST['quantity']) ? wc_stock_amount($_POST['quantity']) : $product->get_min_purchase_quantity(),
                ));
            }
            if ($product->is_purchasable() && $product->is_in_stock()) {
                ?>
                <button type="submit"
                        name="<?php echo apply_filters('wc_ubp_add_to_cart_button_name', 'add-to-cart', $product->get_id()); ?>"
                        value="<?php echo esc_attr($product->get_id()); ?>"
                        class="single_add_to_cart_button ubp_box_add_to_cart_button button alt"
                        disabled="disabled"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
                <input type="hidden" id="ubp-bundle-add-to-cart" name="ubp-bundle-add-to-cart"
                       value="<?php echo esc_attr($pre_ids); ?>"/>
                <input type="hidden" id="ubp-product-id" name="ubp-product-id"
                       value="<?php echo esc_attr($product->get_id()); ?>"/>
            <?php }
            do_action('wc_ubp_box_product_after_add_to_cart', $product->get_id());
        }

        public function print_title($product_id = '')
        {
            ?>
            <div class="product_title"><h1><?php echo get_the_title($product_id); ?></h1>
                <?php do_action('ubp_after_box_product_title', $product_id); ?>
            </div>
            <?php
        }

        public function ubp_categories_enabled($product_id)
        {
            if (get_post_meta($product_id, 'ubp_box_product_categories_enable', true) !== 'yes' || empty(get_post_meta($product_id, 'ubp_products_categories', true))) {
                return true;
            } else {
                return false;
            }
        }

        public function get_box_product_options($product_id = '')
        {
            global $product;
            $product = wc_get_product($product_id);
            $type = get_post_meta($product_id, 'ubp_pricing_type', true);
            $type = !empty($type) ? $type : 'fixed_pricing';
            $box_min = get_post_meta($product_id, 'ubp_box_min_products', true);
            $box_min = (is_numeric($box_min) && $box_min > 0) ? $box_min : 1;
            $box_max = get_post_meta($product_id, 'ubp_box_max_products', true);
            $box_max = (is_numeric($box_max) && $box_max > 0) ? $box_max : 1;
            $data = array();
            if ($this->ubp_categories_enabled($product_id)) {
                $data['enabled_categories'] = false;
                $addons = get_post_meta($product_id, 'ubp_products_addons_values', true);
                $addons = json_decode($addons);
                if (!empty($addons)) {
                    foreach ($addons as $key => $addon) {
                        $prod = wc_get_product($addon->id);
                        if (! $prod)
                            continue;

                        $data[$prod->get_id()] = array(
                            'price' => $prod->get_price(),
                            'max' => $prod->get_max_purchase_quantity(),
                        );
                    }
                }else {
                    $show_prod = get_option('ubp_mix_match_box_max_posts_per_page');
                    $show_prod = empty($show_prod) ? 10 : absint($show_prod);

                    $arg = array(
                        'post_type' => array('product', 'product_variation'),
                        'post__in' => array(),
                        'posts_per_page' => $show_prod,
                        'paged' => 1,
                        'tax_query' => array(
                            'relation' => 'OR',
                            array(
                                'taxonomy' => 'product_type',
                                'field' => 'slug',
                                'terms' => array('simple'),
                                'operator' => 'IN',
                            ),
                            array(
                                'taxonomy' => 'product_type',
                                'operator' => 'NOT EXISTS',
                            )
                        )
                    );
                    $products = new WP_Query($arg);

                    while ($products->have_posts()) {
                        $products->the_post();
                        $_product = wc_get_product(get_the_ID());

                        $data[$_product->get_id()] = array(
                            'price' => $_product->get_price(),
                            'max' => $_product->get_max_purchase_quantity(),
                        );
                    }
                }
            } else {
                $data['enabled_categories'] = true;
            }
            $data['box_min'] = $box_min;
            $data['box_max'] = $box_max;
            $data['box_price'] = $product->get_price();
            $data['pricing'] = $type;
            return apply_filters('upb_box_product_addons_options', $data, $product_id);
        }

        public function ubp_print_short_description($product_id)
        {
            echo get_the_excerpt($product_id);
        }

        public function add_styles()
        {
            $url_path = trim(parse_url(add_query_arg(array()), PHP_URL_PATH), '/');
            if (is_product() || strpos($url_path, 'edit-box-subscription/') !== false) {
                $bg = get_option('ubp_mix_match_box_bg_color');
                $border = get_option('ubp_mix_match_box_border_color');
                $bg = empty($bg) ? '#8224e3' : $bg;
                $border = empty($border) ? '#8224e3' : $border;
                ?>
                <style type="text/css">
                    .col-left ul li {
                        background: <?php echo $bg; ?>;
                        border-color: <?php echo $border; ?>;
                    }
                </style>
                <?php
            }
        }

        public function ubp_add_footer_data()
        {
            $url_path = trim(parse_url(add_query_arg(array()), PHP_URL_PATH), '/');
            if (is_product() || strpos($url_path, 'edit-box-subscription/') !== false) {
                echo '<div id="ubp_error" class="alert alert-danger"></div>';
            }
        }

        public function enqueue_front_scripts()
        {
            $url_path = trim(parse_url(add_query_arg(array()), PHP_URL_PATH), '/');
            if (is_product() || strpos($url_path, 'edit-box-subscription/') !== false) {
                $product_id = apply_filters('wc_ubp_box_edit_subscription_product_id', get_the_ID());
                $layout = get_option('ubp_mix_match_box_columns_layout');
                $layout = empty($layout) ? 'grid' : $layout;
                wp_enqueue_style('cp-box-product-style', WOO_UBP_URL . '/assets/css/frontend_styles.css', array(), '');
                wp_enqueue_style('cgc-box-product-style', WOO_UBP_URL . '/assets/css/cgc-frontend-styles.css', array('cp-box-product-style'), '');
                wp_enqueue_script('cp-box-masonry', WOO_UBP_URL . '/assets/js/masonry.js', array('jquery'), '');
                wp_register_script('cp-box-product-script', WOO_UBP_URL . '/assets/js/frontend-script.js', array('jquery', 'cp-box-masonry'), '', true);
                wp_localize_script('cp-box-product-script', 'ubp', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'product_id' => $product_id,
                    'options' => $this->get_box_product_options($product_id),
                    'layout' => $layout,
                    'message' => esc_html__('The box is full.', 'wc-ubp')
                ));
                wp_enqueue_script('cp-box-product-script');
            }
        }
    }

    new UBP_Box_Product_Frontend();
}