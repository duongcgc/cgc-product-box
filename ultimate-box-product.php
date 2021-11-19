<?php
/**
* Plugin Name: WooCommerce CGC Box Product
* Plugin URI: https://progostech.com/
* Description: WooCommerce Mix & Match - Custom Product Boxes
* Version: 1.3.3
* Author: Progos
* Author URI: https://progostech.com/
* Support: http://www.progos.com/
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain: wc-ubp
* Domain Path: /languages/
**/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
} //!defined('ABSPATH')
if (!defined('WOO_UBP_DIR'))
    define('WOO_UBP_DIR', plugin_dir_path(__FILE__));
if (!defined('WOO_UBP_URL'))
    define('WOO_UBP_URL', plugin_dir_url(__FILE__));
if (!class_exists("UBP_Custom_Product_Box")){
    class UBP_Custom_Product_Box
    {
    	protected static $instance = null;
    	public function __construct(){
            add_action('init', function (){
                require_once WOO_UBP_DIR . 'includes/class-ubp-wc-ajax.php';
                require_once WOO_UBP_DIR.'includes/class-ubp-frontend-cart.php';
            });


    		/**
			 * Check if WooCommerce is installed and active.
			 **/
    		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins')))){
	    		//register product type
				add_action( 'plugins_loaded', array($this,'register_box_product_product_type'));
				$this->ubp_init();
	    	}else{
	    		 add_action('admin_notices',  array($this, 'ubp_admin_notices'));
	    	}
		}
		public function ubp_init(){
			/**        
		    * Load language.     
		    */
		    if ( function_exists( 'load_plugin_textdomain' ) ){
				load_plugin_textdomain('wc-ubp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
			}
			require_once WOO_UBP_DIR.'/includes/class-ubp-controller.php';
			if(is_admin()){
    			require_once WOO_UBP_DIR.'/includes/class-ubp-admin-settings.php';
				require_once WOO_UBP_DIR.'/includes/class-ubp-settings.php';
    		}else{
    			require_once WOO_UBP_DIR.'/includes/class-ubp-frontend.php';
    		}
		}
		public function register_box_product_product_type(){
			require WOO_UBP_DIR.'/includes/class-ubp-product-type.php';
		}
	    public function ubp_admin_notices(){
	    	global $pagenow;
	    	if($pagenow==='plugins.php'){
		    	$class = 'notice notice-error is-dismissible';
	            $message = esc_html__('Custom Box Product needs Woocommerce to be installed and active.', 'wc-ubp');
	            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
	        }
	    }
	}
	new UBP_Custom_Product_Box();
}