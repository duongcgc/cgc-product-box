<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('UBP_Woo_Admin_Settings')) {
    class UBP_Woo_Admin_Settings
    {
        public function __construct()
        {
            //register product type
            add_action('plugins_loaded', array($this, 'register_box_product_product_type'));
            // add a product type
            add_filter('product_type_selector', array(&$this, 'add_custom_product_type'));
            add_filter('woocommerce_product_data_tabs', array(&$this, 'custom_product_tabs'));
            add_action('woocommerce_product_data_panels', array(&$this, 'ubp_product_tab_content'));
            add_action('woocommerce_process_product_meta_box_product', array($this, 'save_product_box_options_field'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('wp_ajax_ubp_search_product_addons', array($this, 'search_product_addons'));
            add_filter('ubp_wc_general_tab_classes', array($this, 'show_general_tab_if_box_product'));
        }

        public function show_general_tab_if_box_product($general_tab_classes)
        {
            $extra_classes = [
                'show_if_box_product',
                'show_if_simple'
            ];

            return array_merge($general_tab_classes, $extra_classes);
		}

        public function register_box_product_product_type()
        {
            require WOO_UBP_DIR . '/includes/class-ubp-product-type.php';
        }

        public function add_custom_product_type($types)
        {
            $types['box_product'] = esc_html__('Box Product', 'wc-ubp');
            return $types;
        }

        public function custom_product_tabs($tabs)
        {
            /**
             * use this filter to add extra classes into general tab.
             */
            $tabs['general']['class'] = apply_filters('ubp_wc_general_tab_classes', $tabs['general']['class']);

            $tabs['ubp_box'] = array(
                'label' => esc_html__('Box Product', 'wc-ubp'),
                'target' => 'ubp_custom_product_box',
                'class' => array('hide_if_simple', 'hide_if_variable', 'hide_if_grouped', 'hide_if_external', 'show_if_box_product'),
                'priority' => 80,
            );

            $tabs['ubp_box_extra_options'] = array(
                'label'		=> esc_html__('Box Product Options', 'wc-ubp' ),
                'target'	=> 'ubp_box_extra_options',
                'class'		=> array( 'hide_if_simple', 'hide_if_variable','hide_if_grouped','hide_if_external'),
                'priority' => 85,
            );

            return $tabs;
        }

        public function ubp_product_tab_content()
        {
            global $woocommerce, $post;
            $post_id = $post->ID;
            echo "<div id='ubp_custom_product_box' class='panel woocommerce_options_panel'>";
            wp_nonce_field('ubp_box_product_nonce', 'ubp_box_product_nonce');
            echo '<div class="options_group">';
            // Create a select field, for pricing type
            $ubp = get_post_meta($post_id, 'ubp_pricing_type', true);
            woocommerce_wp_select(
                array(
                    'id' => 'ubp_pricing_type',
                    'label' => esc_html__('Box Pricing', 'wc-ubp'),
                    'description' => esc_html__('Select box pricing type', 'wc-ubp'),
                    'type' => 'select',
                    'options' => array(
                        'fixed_pricing' => esc_html__('Box regular price', 'wc-ubp'),
                        'per_product_only' => esc_html__('Only addons price', 'wc-ubp'),
                        'per_product_box' => esc_html__('Box regular price with product addons price', 'wc-ubp')
                    ),
                    'desc_tip' => 'true',
                    'value' => $ubp
                ));

            /**
             * Start Custom Product Layout
             */    
            // Create a select field, for product box layout
            $ubp = get_post_meta($post_id, 'cgc_ubp_box_layout', true);
            woocommerce_wp_select(
                array(
                    'id' => 'cgc_ubp_box_layout',
                    'label' => esc_html__('Single Product Layout', 'wc-ubp'),
                    'description' => esc_html__('Select one product box layout', 'wc-ubp'),
                    'type' => 'select',
                    'options' => array(
                        'cgc_layout_01' => esc_html__('Layout 01', 'wc-ubp'),
                        'cgc_layout_02' => esc_html__('Layout 02', 'wc-ubp'),
                        'cgc_layout_03' => esc_html__('Layout 03', 'wc-ubp'),
                        'cgc_layout_04' => esc_html__('Layout 04', 'wc-ubp'),
                    ),
                    'desc_tip' => 'true',
                    'value' => $ubp
                ));

            // Create a select field, for box layout
            $ubp = get_post_meta($post_id, 'ubp_box_layout', true);
            woocommerce_wp_select(
                array(
                    'id' => 'ubp_box_layout',
                    'label' => esc_html__('Single Page Layout', 'wc-ubp'),
                    'description' => esc_html__('Select custom product box layout', 'wc-ubp'),
                    'type' => 'select',
                    'options' => array(
                        'vertical_left' => esc_html__('Vertical Left Box Layout', 'wc-ubp'),
                        'vertical_right' => esc_html__('Vertical Right Box Layout', 'wc-ubp'),
                        'horizontal' => esc_html__('Horizontal Layout', 'wc-ubp'),
                    ),
                    'desc_tip' => 'true',
                    'value' => $ubp
                ));
            // Create a number field, for box min products
            $ubp = get_post_meta($post_id, 'ubp_box_min_products', true);
            woocommerce_wp_text_input(
                array(
                    'id' => 'ubp_box_min_products',
                    'label' => esc_html__('Box Minimum Products', 'wc-ubp'),
                    'placeholder' => '',
                    'value' => (!empty($ubp) ? $ubp : 1),
                    'desc_tip' => 'true',
                    'description' => esc_html__('Enter box minimum products', 'wc-ubp'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'min' => 1
                    )
                ));
            // Create a number field, for box max products
            $ubp = get_post_meta($post_id, 'ubp_box_max_products', true);
            woocommerce_wp_text_input(
                array(
                    'id' => 'ubp_box_max_products',
                    'label' => esc_html__('Box Maximum Products', 'wc-ubp'),
                    'placeholder' => '',
                    'value' => (!empty($ubp) ? $ubp : 1),
                    'desc_tip' => 'true',
                    'description' => esc_html__('Enter box maximum products', 'wc-ubp'),
                    'type' => 'number',
                    'custom_attributes' => array(
                        'min' => 1
                    )
                ));
            // Create a number field, for box columns
            $ubp = get_post_meta($post_id, 'ubp_box_columns', true);
            woocommerce_wp_select(
                array(
                    'id' => 'ubp_box_columns',
                    'label' => esc_html__('Box Layout Columns', 'wc-ubp'),
                    'type' => 'select',
                    'desc_tip' => 'true',
                    'description' => esc_html__('Select box products columns', 'wc-ubp'),
                    'options' => array(
                        '4' => 4,
                        '3' => 3,
                        '2' => 2
                    ),
                    'value' => $ubp
                ));
            // Create a number field, for product columns
            $ubp = get_post_meta($post_id, 'ubp_products_columns', true);
            woocommerce_wp_select(
                array(
                    'id' => 'ubp_products_columns',
                    'label' => esc_html__('Products Layout Columns', 'wc-ubp'),
                    'desc_tip' => 'true',
                    'description' => esc_html__('Select products layout columns', 'wc-ubp'),
                    'type' => 'select',
                    'options' => array(
                        '4' => 4,
                        '3' => 3,
                        '2' => 2
                    ),
                    'value' => $ubp
                ));
            // Create a checkbox for box partially filled
            $ubp = get_post_meta($post_id, 'ubp_show_item_qty', true);
            woocommerce_wp_checkbox(
                array(
                    'id' => 'ubp_show_item_qty',
                    'label' => esc_html__('Show Quantity Selector', 'wc-ubp'),
                    'description' => esc_html__('Allows to show products quantity selector', 'wc-ubp'),
                    'value' => $ubp
                ));
            // Create a checkbox for box partially filled
            $ubp = get_post_meta($post_id, 'ubp_show_item_pricing', true);
            woocommerce_wp_checkbox(
                array(
                    'id' => 'ubp_show_item_pricing',
                    'label' => esc_html__('Show Box Items Price', 'wc-ubp'),
                    'description' => esc_html__('Allows to show products price on single product page', 'wc-ubp'),
                    'value' => $ubp
                ));
            // create a checkboc for tax
            $ubp = get_post_meta($post_id, 'ubp_allow_tax_for_box', true);
            woocommerce_wp_checkbox(
                array(
                    'id' => 'ubp_allow_tax_for_box',
                    'label' => esc_html__('Allow Tax Calculation?', 'wc-ubp'),
                    'description' => esc_html__('Enable tax calculation for box products', 'wc-ubp'),
                    'value' => $ubp
                ));
            // Create a checkbox for per item shipping
            $ubp = get_post_meta($post_id, 'ubp_per_item_shipping', true);
            woocommerce_wp_checkbox(
                array(
                    'id' => 'ubp_per_item_shipping',
                    'label' => esc_html__('Per Item Shipping?', 'wc-ubp'),
                    'description' => esc_html__('Enable per item shipping for box products', 'wc-ubp'),
                    'value' => $ubp
                ));
            //enable subscription
            do_action('ubp_box_product_meta_box');
            // products addons
            $ubp = get_post_meta($post_id, 'ubp_products_addons_values', true);
            woocommerce_wp_hidden_input(
                array(
                    'id' => 'ubp_products_addons_values',
                    'value' => (!empty($ubp) ? $ubp : ''),
                )
            );
            $products = !empty($ubp) ? json_decode($ubp) : '';
            woocommerce_wp_select(
                array(
                    'id' => 'ubp_products_addons',
                    'name' => 'ubp_products_addons[]',
                    'label' => esc_html__('Product Addons', 'wc-ubp'),
                    'desc_tip' => 'true',
                    'description' => esc_html__('Search for products', 'wc-ubp'),
                    'type' => 'select',
                    'options' => array()
                ));
            // Enable categories for box product
            $ubp = get_post_meta($post_id, 'ubp_box_product_categories_enable', true);
            woocommerce_wp_checkbox(
                array(
                    'id' => 'ubp_box_product_categories_enable',
                    'label' => esc_html__('Enable Categories & AJAX Pagination?', 'wc-ubp'),
                    'description' => esc_html__('Enable categories support to add products in the box this will enable the AJAX based products load on frontend.', 'wc-ubp'),
                    'value' => $ubp
                ));
            $args = array('taxonomy' => 'product_cat');
            $categories = get_terms($args);
            $cats = array();
            if (!empty($categories)):
                foreach ($categories as $category):
                    $cats[$category->term_id] = $category->name;
                endforeach;
            endif;
            $ubp = get_post_meta($post_id, 'ubp_products_categories', true);
            woocommerce_wp_select(
                array(
                    'id' => 'ubp_products_categories',
                    'name' => 'ubp_products_categories[]',
                    'label' => esc_html__('Product Categories', 'wc-ubp'),
                    'desc_tip' => 'true',
                    'description' => esc_html__('Select categories you want to include the products of categories.', 'wc-ubp'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'custom_attributes' => array('multiple' => 'multiple'),
                    'options' => $cats,
                    'value' => $ubp
                ));

            echo '<hr/>';
            // Create a checkbox for box partially filled
            $ubp = get_post_meta($post_id, 'ubp_allow_prefilled', true);
            woocommerce_wp_checkbox(
                array(
                    'id' => 'ubp_allow_prefilled',
                    'label' => esc_html__('Pre Added Products in box', 'wc-ubp'),
                    'description' => esc_html__('Allow partial filled box', 'wc-ubp'),
                    'value' => $ubp
                ));
            $prefilled = get_post_meta($post_id, 'ubp_all_prefill_products', true);
            $prefill_html = '';
            if (!empty($prefilled) && !empty($products)) {
                $cnt = count($prefilled);
                $style = 'style="display:none"';
                foreach ($prefilled as $key => $value) {
                    $cnt--;
                    $checked = (isset($value['mandatory']) && $value['mandatory'] === 'yes') ? 'checked' : '';
                    $prefill_html .= '<tr><td><input type="checkbox" name="ubp_mandatory[]" value="' . esc_attr($value['product']) . '" ' . $checked . '></td>';
                    $prefill_html .= '<td><select name="ubp_select_prefill_products[]" class="widefat">';
                    foreach ($products as $pkey => $pval) {
                        $sel = ($pval->id === $value['product']) ? 'selected' : '';
                        $prefill_html .= '<option value="' . esc_attr($pval->id) . '" ' . $sel . '>' . esc_attr($pval->text) . '</option>';
                    }
                    if ($cnt === 0) {
                        $style = '';
                    }
                    $prefill_html .= '</select></td>';
                    $prefill_html .= '<td><input type="number" name="ubp_prefill_qty[]" value="' . esc_attr($value['qty']) . '" min="1"></td>';
                    $prefill_html .= '<td><span class="ubp_remove_prefill">-</span><span class="ubp_add_prefill" ' . $style . '>+</span></td>';
                    $prefill_html .= '</tr>';
                }
            }
            echo '<div class="ubp_prefilled_section">
		    		<p>' . __("<strong>Note: </strong>Variable products are not supported in pre-fill section instead use product variations.", "wc-ubp") . '</p>
		    		<table>
		    			<thead>
		    			<tr>
			    			<th>' . esc_html__("Rrequired", "wc-ubp") . '</th>
			    			<th>' . esc_html__("Product", "wc-ubp") . '</th>
			    			<th>' . esc_html__("Quantity", "wc-ubp") . '</th>
			    			<th>' . esc_html__("Action", "wc-ubp") . '</th>
		    			</tr>
		    		</thead>
		    		<tbody>' . $prefill_html . '</tbody>
		    		</table>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            echo '<div id="ubp_box_extra_options" class="panel woocommerce_options_panel">';
            $ubp=get_post_meta($post_id,'ubp_enable_box_gift_message',true);
            woocommerce_wp_checkbox(
                array(
                    'id'            => 'ubp_enable_box_gift_message',
                    'label'         => esc_html__('Enable Message Field', 'wc-ubp' ),
                    'description'   => esc_html__( 'Enable a text field to collect necessary information.', 'wc-ubp' ),
                    'value'		   => $ubp
                ));
            $ubp=get_post_meta($post_id,'ubp_box_message_field_label',true);
            woocommerce_wp_text_input(
                array(
                    'id'                => 'ubp_box_message_field_label',
                    'label'             => esc_html__( 'Field Label', 'wc-ubp' ),
                    'placeholder'       => '',
                    'value'			   => (!empty($ubp) ? $ubp : esc_html__('Message','wc-ubp')),
                    'desc_tip'    	   => 'true',
                    'description'       => esc_html__( 'Enter field Label', 'wc-ubp' )
                ));
            $ubp=get_post_meta($post_id,'ubp_enable_box_gift_field_type',true);
            woocommerce_wp_select(
                array(
                    'id'                => 'ubp_enable_box_gift_field_type',
                    'label'             => esc_html__( 'Field Type', 'wc-ubp' ),
                    'desc_tip'    	   => 'true',
                    'description'       => esc_html__( 'Select field type.', 'wc-ubp' ),
                    'type'              => 'select',
                    'options'     => array(
                        'text' =>	esc_html__( 'Text Field', 'wc-ubp' ),
                        'textarea' => esc_html__( 'Textarea Field', 'wc-ubp' )
                    ),
                    'value'			   => $ubp
                ));
            $ubp=get_post_meta($post_id,'ubp_enable_box_gift_required',true);
            woocommerce_wp_checkbox(
                array(
                    'id'            => 'ubp_enable_box_gift_required',
                    'label'         => esc_html__('Field requried?', 'wc-ubp' ),
                    'description'   => esc_html__( 'Enable a text field to be required.', 'wc-ubp' ),
                    'value'		   => $ubp
                ));
            echo '</div>';
        }

        /*
         *	save box products meta
         */
        public function save_product_box_options_field($post_id)
        {
            //if doing an auto save
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            // if our nonce isn't there, or we can't verify it
            if (!isset($_POST['ubp_box_product_nonce']) || !wp_verify_nonce($_POST['ubp_box_product_nonce'], 'ubp_box_product_nonce')) return;
            // if current user can't edit this post
            if (!current_user_can('edit_post')) return;
            if (isset($_POST['ubp_pricing_type'])) {
                update_post_meta($post_id, 'ubp_pricing_type', sanitize_text_field($_POST['ubp_pricing_type']));
            }

            // update custom product layout
            if (isset($_POST['cgc_ubp_box_layout'])) {
                update_post_meta($post_id, 'cgc_ubp_box_layout', sanitize_text_field($_POST['cgc_ubp_box_layout']));
            }


            if (isset($_POST['ubp_box_layout'])) {
                update_post_meta($post_id, 'ubp_box_layout', sanitize_text_field($_POST['ubp_box_layout']));
            }
            $min = 1;
            $max = 1;
            if (isset($_POST['ubp_box_min_products']) && is_numeric($_POST['ubp_box_min_products']) && $_POST['ubp_box_min_products'] >= 0) {
                $min = $_POST['ubp_box_min_products'];
            }
            if (isset($_POST['ubp_box_max_products']) && is_numeric($_POST['ubp_box_max_products']) && $_POST['ubp_box_max_products'] >= 0) {
                $max = $_POST['ubp_box_max_products'];
            }
            //min & max
            if ($min > 0 && $max > 0 && $min <= $max) {
                update_post_meta($post_id, 'ubp_box_min_products', sanitize_text_field($min));
                update_post_meta($post_id, 'ubp_box_max_products', sanitize_text_field($max));
            }
            if (isset($_POST['ubp_box_columns']) && is_numeric($_POST['ubp_box_columns']) && $_POST['ubp_box_columns'] >= 0) {
                update_post_meta($post_id, 'ubp_box_columns', sanitize_text_field($_POST['ubp_box_columns']));
            }
            if (isset($_POST['ubp_products_columns']) && is_numeric($_POST['ubp_products_columns']) && $_POST['ubp_products_columns'] >= 0) {
                update_post_meta($post_id, 'ubp_products_columns', sanitize_text_field($_POST['ubp_products_columns']));
            }
            if (isset($_POST['ubp_show_item_pricing']) && $_POST['ubp_show_item_pricing'] === 'yes') {
                update_post_meta($post_id, 'ubp_show_item_pricing', 'yes');
            } else {
                update_post_meta($post_id, 'ubp_show_item_pricing', 'no');
            }
            if (isset($_POST['ubp_show_item_qty']) && $_POST['ubp_show_item_qty'] === 'yes') {
                update_post_meta($post_id, 'ubp_show_item_qty', 'yes');
            } else {
                update_post_meta($post_id, 'ubp_show_item_qty', 'no');
            }
            if (isset($_POST['ubp_allow_tax_for_box']) && $_POST['ubp_allow_tax_for_box'] === 'yes') {
                update_post_meta($post_id, 'ubp_allow_tax_for_box', 'yes');
            } else {
                update_post_meta($post_id, 'ubp_allow_tax_for_box', 'no');
            }
            if (isset($_POST['ubp_per_item_shipping']) && $_POST['ubp_per_item_shipping'] === 'yes') {
                update_post_meta($post_id, 'ubp_per_item_shipping', 'yes');
            } else {
                update_post_meta($post_id, 'ubp_per_item_shipping', 'no');
            }
            if (isset($_POST['ubp_allow_prefilled']) && $_POST['ubp_allow_prefilled'] === 'yes') {
                update_post_meta($post_id, 'ubp_allow_prefilled', 'yes');
                $selects = array();
                $prod_ids = array();
                $mandatories = array();
                $qtys = array();
                $allowed_qty = 0;
                if (isset($_POST['ubp_select_prefill_products']) && count($_POST['ubp_select_prefill_products'])) {
                    for ($i = 0; $i < count($_POST['ubp_select_prefill_products']); $i++) {
                        $prod = $_POST['ubp_select_prefill_products'][$i];
                        if (in_array($prod, $prod_ids)) {
                            continue;
                        } else {
                            $prod_ids[$i] = $prod;
                        }
                        $mandatory = (in_array($prod, $_POST['ubp_mandatory'])) ? 'yes' : 'no';
                        $mandatories[$prod] = $mandatory;
                        $qty = (isset($_POST['ubp_prefill_qty'][$i]) && !empty($_POST['ubp_prefill_qty'][$i])) ? $_POST['ubp_prefill_qty'][$i] : 1;
                        $qtys[$prod] = $qty;
                        $allowed_qty += $qty;
                        if ($allowed_qty > $max) {
                            $qty = $qty - ($allowed_qty - $max);
                        }
                        $selects[] = array(
                            'mandatory' => $mandatory,
                            'product' => $prod,
                            'qty' => $qty
                        );
                        if ($allowed_qty >= $max) {
                            break;
                        }
                    }
                    update_post_meta($post_id, 'ubp_all_prefill_products', $selects);
                    update_post_meta($post_id, 'ubp_mandatory', $mandatories);
                    update_post_meta($post_id, 'ubp_prefill_qty', $qtys);
                } else {
                    update_post_meta($post_id, 'ubp_all_prefill_products', '');
                    update_post_meta($post_id, 'ubp_select_prefill_products', '');
                    update_post_meta($post_id, 'ubp_prefill_qty', '');
                    update_post_meta($post_id, 'ubp_mandatory', '');
                }
            } else {
                update_post_meta($post_id, 'ubp_allow_prefilled', 'no');
                update_post_meta($post_id, 'ubp_all_prefill_products', '');
                update_post_meta($post_id, 'ubp_select_prefill_products', '');
                update_post_meta($post_id, 'ubp_prefill_qty', '');
                update_post_meta($post_id, 'ubp_mandatory', '');
            }
            if (isset($_POST['ubp_products_addons_values']) && !empty($_POST['ubp_products_addons_values'])) {
                update_post_meta($post_id, 'ubp_products_addons_values', stripcslashes($_POST['ubp_products_addons_values']));
            } else {
                update_post_meta($post_id, 'ubp_products_addons_values', '');
            }
            if (isset($_POST['ubp_box_product_categories_enable'])) {
                update_post_meta($post_id, 'ubp_box_product_categories_enable', 'yes');
            } else {
                update_post_meta($post_id, 'ubp_box_product_categories_enable', 'no');
            }
            if (isset($_POST['ubp_products_categories']))
                update_post_meta($post_id, 'ubp_products_categories', wc_clean($_POST['ubp_products_categories']));

            if(isset($_POST['ubp_box_message_field_label']))
                update_post_meta($post_id,'ubp_box_message_field_label',wc_clean($_POST['ubp_box_message_field_label']));
            if(isset($_POST['ubp_enable_box_gift_message']))
                update_post_meta($post_id,'ubp_enable_box_gift_message','yes');
            else
                update_post_meta($post_id,'ubp_enable_box_gift_message','no');
            if(isset($_POST['ubp_enable_box_gift_required']))
                update_post_meta($post_id,'ubp_enable_box_gift_required','yes');
            else
                update_post_meta($post_id,'ubp_enable_box_gift_required','no');
            if(isset($_POST['ubp_enable_box_gift_field_type'])){
                update_post_meta($post_id,'ubp_enable_box_gift_field_type',wc_clean($_POST['ubp_enable_box_gift_field_type']));
            }
        }

        /**
         *    Ajax search products
         */
        public function search_product_addons()
        {
            if (!isset($_REQUEST['search']) || empty($_REQUEST['search']))
                die();
            $term = isset($_REQUEST['search']) ? esc_attr($_REQUEST['search']) : '';
            $args = array(
                'post_type' => array('product', 'product_variation'),
                'post_status' => 'publish',
                'posts_per_page' => -1,
                's' => $term,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_type',
                        'field' => 'slug',
                        'terms' => array('variable', 'box_product', 'subscription', 'variable-subscription'),
                        'operator' => 'NOT IN',
                    )
                )
            );
            $args = apply_filters('ubp_box_product_addon_search_args', $args);
            $arr = array();
            $products = new WP_Query($args);
            if ($products->have_posts()) {
                while ($products->have_posts()): $products->the_post();
                    array_push($arr, array('id' => get_the_ID(), 'text' => get_the_title() . '|#' . get_the_ID()));
                endwhile;
            }
            $arr = apply_filters('ubp_box_product_addon_search_results', $arr, $term);
            echo json_encode($arr);
            die();
        }

        /*
         *	Enqueue scripts
         */
        public function enqueue_scripts($hook)
        {
            global $post_type;
            if ('product' == $post_type || (isset($_GET['page']) && $_GET['page'] == 'mix-match-addons')
                || (isset($_GET['page']) && $_GET['page'] == 'mix-match-box')) {
                wp_enqueue_style('select2', WOO_UBP_URL . '/assets/css/select2.min.css', array(), '4.0');
                wp_enqueue_style('ubp-styles', WOO_UBP_URL . '/assets/css/backend_styles.css', array(), '1.0');
                wp_register_script('ubp-admin', WOO_UBP_URL . '/assets/js/admin-script.js', array('jquery'), '1.0');
                wp_enqueue_script('select2', WOO_UBP_URL . '/assets/js/select2.min.js', array('jquery', 'ubp-admin'), '4.0');
                wp_localize_script('ubp-admin', 'wooubp', array(
                    'ajaxurl' => admin_url('admin-ajax.php')
                ));
                // Css rules for Color Picker
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');
                wp_enqueue_script('ubp-admin');
            }
        }
    }

    new UBP_Woo_Admin_Settings();
}