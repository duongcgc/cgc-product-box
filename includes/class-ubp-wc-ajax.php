<?php

if (! defined('ABSPATH'))
    return;


if (! class_exists('UBP_WC_Ajax')){ 

    if (! defined('WC_UBP_URL'))
        define('WC_UBP_URL', plugin_dir_url(dirname(__FILE__)));

    class UBP_WC_Ajax{
        public function __construct()
        {
            add_action('wp_enqueue_scripts', [$this, 'enqueu_scripts'], 10);
            add_action('wp_ajax_ubp_ajax_add_to_cart', [$this, 'ajax_add_to_cart']);
            add_action('wp_ajax_nopriv_ubp_ajax_add_to_cart', [$this, 'ajax_add_to_cart']);

            //add_action( 'wp_enqueue_scripts', [$this, 'remove_wc_cart_fragment'], 999 );
        }

        public function remove_wc_cart_fragment()
        {
            wp_dequeue_script( 'wc-cart-fragments' );
        }

        public function enqueu_scripts()
        {
            wp_enqueue_script('ubp-ajax-add-to-cart', trim(WC_UBP_URL, '/') . '/assets/js/ajax-add-to-cart.js', array('jquery'), 1.0, true);
            wp_localize_script('ubp-ajax-add-to-cart', 'ubpAjaxObj', array('ajax_url' => admin_url('admin-ajax.php')));
        }

        public function ajax_add_to_cart()
        {
            $posted_data = filter_input_array(INPUT_POST);

            if (isset($posted_data['ubp_products_id'])){
                $product_id = $posted_data['ubp_main_product_id'];

                try{
                    $item_data = [
                        'ubp-bundle-add-to-cart' => $posted_data['ubp_products_id'],
                    ];
                    $quantity          = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_REQUEST['quantity'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
                    $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

                    if ( $passed_validation ) {
                        do_action('ubp_ajax_add_to_cart', $product_id, $quantity, 0, [], $item_data);
                    }
                }catch (Exception $x){
                    wp_send_json_error(['data' => $x->getMessage()]);
                }
            }else {
                echo ''; // housekeeping
            }

            wp_send_json_success('Success');
            wp_die();
        }
    }

    new UBP_WC_Ajax();
}