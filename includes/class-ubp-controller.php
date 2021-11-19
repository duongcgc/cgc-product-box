<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if(!class_exists('UBP_Woo_Box_Product_Controller')){
	class UBP_Woo_Box_Product_Controller{ 
		public function __construct(){
            add_action('wp_ajax_ubp_load_box_product_items',array($this,'ubp_load_products'));
            add_action('wp_ajax_nopriv_ubp_load_box_product_items',array($this,'ubp_load_products'));
        }
        public function ubp_load_products(){
            if(!isset($_POST['product_id']) || !isset($_POST['paged']))
                wp_send_json_error(esc_html__('Sorry! the product id is missing.','wc-ubp'));
            $product_id=absint($_POST['product_id']);
            $paged=absint($_POST['paged']);
            $addons=get_post_meta($product_id,'ubp_products_addons_values',true);
			$addons=json_decode($addons);
			$columns=get_post_meta($product_id,'ubp_products_columns',true);
			$qty=get_post_meta($product_id,'ubp_show_item_qty',true);
			$show_prod=get_option('ubp_mix_match_box_max_posts_per_page');
			$show_prod=empty($show_prod) ? count($addons) : absint($show_prod);
			$qty_selector='';
			if($qty!=='yes'){
				$qty_selector='style="display:none;opacity:0;"';
			}
			$cats_enabled=get_post_meta($product_id,'ubp_box_product_categories_enable',true);
			$cats=get_post_meta($product_id,'ubp_products_categories',true);
			$all_prod=array();
			if($cats_enabled=='yes'){
				$cat_prod_ids=array();
				if(!empty($cats)){
					$args=array(
						'post_type'      => array('product'),
						'posts_per_page' => -1,
						'fields'		 => 'ids',
						'tax_query'      => array(
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => $cats,
							)
						)
					);
					$cat_prod_ids=get_posts($args);
					if(!empty($cat_prod_ids)){
						$args=array(
							'post_type'      => array('product_variation'),
							'posts_per_page' => -1,
							'fields'		 => 'ids',
							'post_parent__in' => $cat_prod_ids
						);
						$variation_ids=get_posts($args);
						$all_prod=array_merge($variation_ids,$cat_prod_ids);
					}
				}
			}
			$addons=wp_list_pluck($addons, 'id');
			$all_prod=array_merge($addons,$all_prod);
			$arg=array(
				'post_type'		 => array('product','product_variation'),
				'post__in'		 => $all_prod,
				'posts_per_page' => $show_prod,
				'paged'			 => $paged,
				'tax_query'  	 => array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array('simple'),
						'operator' => 'IN',
					),
					array(
						'taxonomy' => 'product_type',
						'operator' => 'NOT EXISTS',
					)
				)
			);
			$products=new WP_Query($arg);
			if(!empty($products)){
				while($products->have_posts()){ $products->the_post();
					$temp=$itemHtml='';
					$_product=wc_get_product(get_the_ID());
					if(!$_product->is_type('variable')){
						$data=array();
						$data['id']=$_product->get_id();
						$data['price']=$_product->get_price();
						$data['purchaseable']=($_product->is_in_stock() && $_product->is_purchasable()) ? 1 : 0;
						$data['max']=$_product->get_max_purchase_quantity();
						$disable=($data['purchaseable']==1) ? '' : 'class="ubp-disabled"';
						$itemHtml.='<li data-product_id="'.esc_attr($_product->get_id()).'" '.($disable).'>';
							$itemHtml.='<figure>';
								if(!empty($disable))
								$itemHtml.='<span class="outofstock_prod_box">'.apply_filters("wc_ubp_box_product_outofstock_label",esc_html__("Out of stock","wc-ubp")).'</span>';	
								else
								$itemHtml.='<span class="add_prod_box"></span>';
								if(has_post_thumbnail($_product->get_id())){
									$itemHtml.=wp_get_attachment_image(get_post_thumbnail_id($_product->get_id()),'woocommerce_thumbnail');
								}else{
									$itemHtml.='<img src="'.WOO_UBP_URL.'/assets/images/placeholder.png" alt="product-image">';
								}
								$itemHtml.='<div class="qty" '.$qty_selector.'><input type="button" value="-" class="qtyminus" field="qty_'.esc_attr($_product->get_id()).'" />
								<input type="text" name="qty_'.esc_attr($_product->get_id()).'" value="1" >
								<input type="button" value="+" class="qtyplus" field="qty_'.esc_attr($_product->get_id()).'" /></div>';
								$itemHtml.='<figcaption><a href="'.esc_url(get_the_permalink($_product->get_id())).'">'.esc_html($_product->get_name()).'</a>';
								if(get_post_meta($product_id,'ubp_show_item_pricing',true)=='yes'){
									$itemHtml.='<span class="box_product_item_price">'.$_product->get_price_html().'</span>';
								}
								$itemHtml.='</figcaption>';
							$itemHtml.='</figure>';
							$itemHtml.='<input type="hidden" value="'.wc_esc_json(wp_json_encode($data)).'">';
						$itemHtml.='</li>';
					}
					$html.=apply_filters('ubp_box_product_single_item_content',$itemHtml,$_product,wc_get_product($product_id));
				}
			}else{
				$html.=esc_html__('Please add product addons for this box.','wc-ubp');
			}
			if($products->max_num_pages>$paged){
               $paged++;
            }else{
                $paged=null;
            }
			wp_send_json_success(array('paged'=>$paged,'html'=>$html));
        }
    }
    new UBP_Woo_Box_Product_Controller();
}