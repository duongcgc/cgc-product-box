<?php

if (!defined('ABSPATH')) {

    exit; // Exit if accessed directly. 

}

if(!class_exists('UBP_Box_Product_Frontend_Cart')){

    class UBP_Box_Product_Frontend_Cart{

        public function __construct() {

            add_filter( 'woocommerce_add_cart_item_data', array( $this, 'ubp_box_product_add_cart_item_data' ), 1, 2 );

            add_action( 'woocommerce_add_to_cart', array( $this, 'ubp_box_product_add_to_cart' ), 10, 6 );

            add_filter( 'woocommerce_add_cart_item', array( $this, 'ubp_box_product_add_cart_item' ), 10, 1 );

            add_filter( 'woocommerce_cart_tax_totals', array($this, 'woocommerce_cart_tax_totals_callback'),10,2);

            add_filter( 'woocommerce_cart_item_name', array( $this, 'ubp_box_product_cart_item_name' ), 10, 2 );
            add_action( 'woocommerce_before_cart', array( $this, 'remove_orphan_contents' ), 1 );

            add_filter( 'woocommerce_cart_item_price', array( $this, 'ubp_box_product_cart_item_price' ), 10, 3 );

            add_filter( 'woocommerce_cart_item_quantity', array( $this, 'ubp_box_product_cart_item_quantity' ), 1, 2 );

            add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'ubp_box_product_cart_item_subtotal' ), 10, 3 );

            add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'ubp_box_product_cart_item_remove_link' ), 10, 3 );

            add_filter( 'woocommerce_cart_contents_count', array( $this, 'ubp_box_product_cart_contents_count' ) );

            add_action( 'woocommerce_after_cart_item_quantity_update', array($this,'ubp_box_product_update_cart_item_quantity'), 1, 2 );

            add_action( 'woocommerce_before_cart_item_quantity_zero', array($this,'ubp_box_product_update_cart_item_quantity'), 1 );

            add_action( 'woocommerce_cart_item_removed', array( $this, 'ubp_box_product_cart_item_removed' ), 10, 2 );

            // Checkout item

            add_filter( 'woocommerce_checkout_item_subtotal', array( $this, 'ubp_box_product_cart_item_subtotal' ), 10, 3 );

            // Calculate totals

            add_action( 'woocommerce_before_calculate_totals', array( $this, 'ubp_box_product_before_calculate_totals' ), 10, 1 );

            // Shipping

            add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'ubp_box_product_cart_shipping_packages' ) );

        }



        public function woocommerce_cart_tax_totals_callback($tax_total,$obj){

            $array=array();

            return $tax_total;

        }

        public function remove_orphan_contents()
        {
            $cart_contents = WC()->cart->cart_contents;
            foreach ($cart_contents as $item_hash => $item){

                if (isset($item['ubp_box_product_parent_id']) && !empty($item['ubp_box_product_parent_id'])){
                    $parent_id = $item['ubp_box_product_parent_id'];
                    $parent_key = $item['ubp_box_product_parent_key'];

                    $parent_item = $cart_contents[$parent_key];
                    if (! $parent_item){
                        WC()->cart->remove_cart_item($item_hash);
                    }
                }
            }
        }

        public function ubp_box_product_cart_item_name( $name, $item ) {

            if ( isset( $item['ubp_box_product_parent_id'] ) && ! empty( $item['ubp_box_product_parent_id'] ) ) {

                if ( ( strpos( $name, '</a>' ) !== false ) && ( get_option( '_ubp_box_product_bundled_link', 'yes' ) == 'yes' ) ) {

                    return '<a href="' . esc_url(get_permalink( $item['ubp_box_product_parent_id'] )) . '">' . esc_attr(get_the_title( $item['ubp_box_product_parent_id'] )) . '</a> &rarr; ' . strip_tags($name);

                } else {

                    return get_the_title( $item['ubp_box_product_parent_id'] ) . ' &rarr; ' . strip_tags( $name );

                }

            } else {

                return $name;

            }

        }

        public function ubp_box_product_add_cart_item_data( $cart_item_data, $product_id ) {

            $terms        = get_the_terms( $product_id, 'product_type' );

            $product_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

            if ( $product_type == 'box_product' ) {

                $cart_item_data['ubp-bundle-add-to-cart'] = isset($_POST['ubp-bundle-add-to-cart']) ? wc_clean($_POST['ubp-bundle-add-to-cart']) : '';

            }

            return $cart_item_data;

        }

        //remove x link from cart for bix items

        public function ubp_box_product_cart_item_remove_link( $link, $cart_item_key ) {

            if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['ubp_box_product_parent_id'] ) ) {

                return '';

            }

            return $link;

        }

        public function ubp_box_product_cart_item_quantity( $quantity, $cart_item_key ) {

            if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['ubp_box_product_parent_id'] ) ) {

                return WC()->cart->cart_contents[ $cart_item_key ]['quantity'];

            }

            return $quantity;

        }

        public function ubp_box_product_cart_item_price( $price, $cart_item, $cart_item_key ) {

            if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['ubp_box_product_parent_id'] ) ) {

                return '';

            }

            if (isset(WC()->cart->cart_contents[$cart_item_key]['ubp-bundle-add-to-cart']) && WC()->cart->cart_contents[$cart_item_key]['ubp-bundle-add-to-cart'] !== '') {
                $product_id = WC()->cart->cart_contents[$cart_item_key]['product_id'];
                $pricing_type = get_post_meta($product_id, 'ubp_pricing_type', true);
                $quantity = WC()->cart->cart_contents[$cart_item_key]['quantity'];

                $sale_price = floatval(get_post_meta($product_id, '_sale_price', true));
                $regular_price = !empty($sale_price) ? $sale_price : floatval(get_post_meta($product_id, '_regular_price', true));

                if ($pricing_type !== 'fixed_pricing') {
                    $box_items = explode(',', WC()->cart->cart_contents[$cart_item_key]['ubp-bundle-add-to-cart']);
                    $p = 0;

                    foreach ($box_items as $key => $ubp_item_prod_id) {
                        $ubp_item_product = wc_get_product($ubp_item_prod_id);

                        if (!$ubp_item_product || $ubp_item_product->is_type('box_product')) {
                            continue;
                        }

                        $p += floatval(($ubp_item_product->get_price()));
                    }

                    //$p = $p / $quantity;

                    if ($pricing_type === 'per_product_box') {
                        return wc_price(($regular_price + $p));
                    } else if ($pricing_type === 'per_product_only') {
                        return wc_price($p);
                    }
                }

            }

            return $price;

        }

        public function ubp_box_product_cart_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {

            if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['ubp_box_product_parent_id'] ) ) {

                return '';

            }

            return $subtotal;

        }

        public function ubp_box_product_cart_contents_count( $count ) {

            $cart_contents = WC()->cart->cart_contents;

            $bundled_items = 0;

            foreach ( $cart_contents as $cart_item_key => $cart_item ) {

                if ( ! empty( $cart_item['ubp_box_product_parent_id'] ) ) {

                    $bundled_items += $cart_item['quantity'];

                }

            }

            return intval( $count - $bundled_items );

        }

        public function ubp_box_product_update_cart_item_quantity( $cart_item_key, $quantity = 0 ) {

            if ( ! empty( WC()->cart->cart_contents[ $cart_item_key ] ) && ( isset( WC()->cart->cart_contents[ $cart_item_key ]['ubp_box_product_keys'] ) ) ) {

                if ( $quantity <= 0 ) {

                    $quantity = 0;

                } else {

                    $quantity = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];

                }

                foreach ( WC()->cart->cart_contents[ $cart_item_key ]['ubp_box_product_keys'] as $ubp_box_product_key ) {

                    WC()->cart->set_quantity( $ubp_box_product_key, $quantity * ( WC()->cart->cart_contents[ $ubp_box_product_key ]['ubp_box_product_qty'] ? WC()->cart->cart_contents[ $ubp_box_product_key ]['ubp_box_product_qty'] : 1 ), false );

                }

            }

        }

        public function ubp_box_product_cart_item_removed( $cart_item_key, $cart ) {

            if ( isset( $cart->removed_cart_contents[ $cart_item_key ]['ubp_box_product_keys'] ) ) {

                $ubp_box_product_keys = $cart->removed_cart_contents[ $cart_item_key ]['ubp_box_product_keys'];

                foreach ( $ubp_box_product_keys as $ubp_box_product_key ) {

                    unset( $cart->cart_contents[ $ubp_box_product_key ] );

                }

            }

        }

        public function ubp_box_product_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

            if ( isset( $cart_item_data['ubp-bundle-add-to-cart'] ) && ( $cart_item_data['ubp-bundle-add-to-cart'] != '' ) ) {

                $items = explode( ',', $cart_item_data['ubp-bundle-add-to-cart'] );

                $items=array_count_values($items);

                if ( is_array( $items ) && ( count( $items ) > 0 ) ) {

                    // add child products

                    foreach ( $items as $item => $qty ) {

                        $ubp_box_product_item_id           = $item;

                        $ubp_box_product_item_qty          = $qty;

                        $ubp_box_product_item_variation_id = 0;

                        $ubp_box_product_item_variation    = array();

                        $ubp_box_product_product = wc_get_product( $ubp_box_product_item_id );

                        if ( $ubp_box_product_product ) {

                            // set price zero for child product

                            $ubp_box_product_product->set_price( 0 );

                            // add to cart

                            $ubp_box_product_product_qty = $ubp_box_product_item_qty * $quantity;

                            $ubp_box_product_cart_id     = WC()->cart->generate_cart_id( $ubp_box_product_item_id, $ubp_box_product_item_variation_id, $ubp_box_product_item_variation, array(

                                'ubp_box_product_parent_id'  => $product_id,

                                'ubp_box_product_parent_key' => $cart_item_key,

                                'ubp_box_product_qty'        => $ubp_box_product_item_qty

                            ) );

                            $ubp_box_product_item_key    = WC()->cart->find_product_in_cart( $ubp_box_product_cart_id );

                            if ( ! $ubp_box_product_item_key ) {

                                $ubp_box_product_item_key                              = $ubp_box_product_cart_id;

                                WC()->cart->cart_contents[ $ubp_box_product_item_key ] = array(

                                    'product_id'       			=> $ubp_box_product_item_id,

                                    'variation_id'     			=> $ubp_box_product_item_variation_id,

                                    'variation'        			=> $ubp_box_product_item_variation,

                                    'quantity'         			=> $ubp_box_product_product_qty,

                                    'data'             			=> $ubp_box_product_product,

                                    'ubp_box_product_parent_id'  => $product_id,

                                    'ubp_box_product_parent_key' => $cart_item_key,

                                    'ubp_box_product_qty'        => $ubp_box_product_item_qty,

                                );

                            }

                            WC()->cart->cart_contents[ $cart_item_key ]['ubp_box_product_keys'][] = $ubp_box_product_item_key;

                        }

                    }

                }

            }

        }

        function ubp_box_product_add_cart_item( $cart_item ) {

            if ( isset( $cart_item['ubp_box_product_parent_key'] ) ) {

                $cart_item['data']->price = 0;

            }

            return $cart_item;

        }

        public function ubp_box_product_before_calculate_totals( $cart_object ) {

            //  This is necessary for WC 3.0+

            if (is_admin() && !defined( 'DOING_AJAX' ) ) {

                return;

            }

            foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {

                // child product price

                if ( isset( $cart_item['ubp_box_product_parent_id'] ) && ( $cart_item['ubp_box_product_parent_id'] != '' ) ) {

                    $cart_item['data']->set_price( 0 );

                }

                // main product price

                if ( isset( $cart_item['ubp-bundle-add-to-cart'] ) && ( $cart_item['ubp-bundle-add-to-cart'] != '' ) ) {

                    $ubp_box_product_items = explode( ',', $cart_item['ubp-bundle-add-to-cart'] );

                    $ubp_box_product_items =array_count_values($ubp_box_product_items);

                    $product_id  = $cart_item['product_id'];

                    if(get_post_meta($product_id,'ubp_allow_tax_for_box',true)!=='yes'){

                        $cart_item['data']->set_tax_status('none');

                    }

                    $ubp_box_product_price = 0;

                    $pricing_type=get_post_meta( $product_id, 'ubp_pricing_type', true );

                    if ( $pricing_type == 'per_product_only'  || $pricing_type == 'per_product_box') {

                        if ( is_array( $ubp_box_product_items ) && count( $ubp_box_product_items ) > 0 ) {

                            foreach ( $ubp_box_product_items as $ubp_box_product_item => $qty ) {

                                $ubp_box_product_item_id      = absint( $ubp_box_product_item ? $ubp_box_product_item : 0 );

                                $ubp_box_product_item_qty     = absint( $qty ? $qty : 1 );

                                $ubp_box_product_item_product = wc_get_product( $ubp_box_product_item_id );

                                if ( ! $ubp_box_product_item_product || $ubp_box_product_item_product->is_type( 'box_product' ) ) {

                                    continue;

                                }

                                $ubp_box_product_price += $ubp_box_product_item_product->get_price() * $ubp_box_product_item_qty;

                            }

                        }

                    } else {

                        $box_product=wc_get_product( $product_id );

                        $box_price=$box_product->get_price();

                        $ubp_box_product_price = $box_price;

                    }

                    // per item + base price

                    if ( ( $pricing_type=='per_product_box') && is_numeric( $ubp_box_product_price ) ) {

                        $box_product=wc_get_product( $product_id );

                        $box_price=$box_product->get_price();

                        $ubp_box_product_price +=$box_price;

                    }

                    $cart_item['data']->set_price( floatval( $ubp_box_product_price ) );

                }

            }

        }

        public function ubp_box_product_cart_shipping_packages( $packages ) {

            if ( ! empty( $packages ) ) {

                foreach ( $packages as $package_key => $package ) {

                    if ( ! empty( $package['contents'] ) ) {

                        foreach ( $package['contents'] as $cart_item_key => $cart_item ) {

                            if ( isset( $cart_item['ubp_box_product_parent_id'] ) && ( $cart_item['ubp_box_product_parent_id'] != '' ) ) {

                                $prod_id=$cart_item['ubp_box_product_parent_id'];

                                if(get_post_meta($prod_id,'ubp_per_item_shipping',true)!=='yes'){

                                    unset( $packages[ $package_key ]['contents'][ $cart_item_key ] );

                                }

                            }

                        }

                    }

                }

            }

            return $packages;

        }

    }

    new UBP_Box_Product_Frontend_Cart();

}