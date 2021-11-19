<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
	
}
if(!class_exists('UBP_Ultimate_Box_Main_Settings')){
	class UBP_Ultimate_Box_Main_Settings{
		public function __construct(){
			add_action('admin_menu', array($this, 'register_settings_page'));
			add_action('admin_init', array($this, 'register_settings'));
			add_action('admin_footer', array($this, 'css'));
		}
		public function register_settings_page(){
			add_menu_page(esc_html__('Mix & Match Box Product','wc-rbp'), esc_html__('Mix & Match Box','wc-rbp'), 'manage_options', 'mix-match-box', array($this, 'menu_page_callback'),'dashicons-products',57);
			add_submenu_page('mix-match-box', esc_html__('Settings','wc-rbp'), esc_html__('Settings','wc-rbp'), 'manage_options' , 'mix-match-box', array($this, 'menu_page_callback') );
			add_submenu_page('mix-match-box', esc_html__('Addon Plugins','wc-rbp'), esc_html__('Addon Plugins','wc-rbp'), 'manage_options' , 'mix-match-addons', array($this, 'mix_match_addons_func') );
		}
		public function register_settings(){
			register_setting('ubp_mix_match_box_settings', 'ubp_mix_match_box_columns_layout'); 
			register_setting('ubp_mix_match_box_settings', 'ubp_mix_match_box_short_description');
			register_setting('ubp_mix_match_box_settings', 'ubp_mix_match_box_price_label');
			register_setting('ubp_mix_match_box_settings', 'ubp_mix_match_box_bg_color'); 
			register_setting('ubp_mix_match_box_settings', 'ubp_mix_match_box_border_color');
			register_setting('ubp_mix_match_box_settings', 'ubp_mix_match_box_max_posts_per_page');
		}
		public function menu_page_callback(){
			?>
			<div class="wrap">
				<h1><?php _e('Mix & Match Box Product','wc-ubp'); ?></h1>
				<form action="options.php" method="post">
					<?php settings_errors(); ?>
					<?php settings_fields( 'ubp_mix_match_box_settings' );
						  do_settings_sections('ubp_mix_match_box_settings'); ?>
					<table class="form-table">
						<tr>
							<th><label for="ubp_mix_match_box_columns_layout"><?php esc_html_e('Box Items Column Layout','wc-ubp'); ?></label></th>
							<td><?php $value=get_option('ubp_mix_match_box_columns_layout'); ?>
								<p><label><input type="radio" name="ubp_mix_match_box_columns_layout" value="grid" <?php checked( $value, 'grid'); ?>>
								<?php esc_html_e('Grid Layout','wc-ubp'); ?></label></p>
								<p><label><input type="radio" name="ubp_mix_match_box_columns_layout" value="masonry" <?php checked( $value, 'masonry'); ?>>
								<?php esc_html_e('Masonry Layout','wc-ubp'); ?></label></p>
							</td>
						</tr>
						<tr>
							<th><label for="ubp_mix_match_box_short_description"><?php esc_html_e('Show Product Short Description','wc-ubp'); ?></label></th>
							<td><?php $value=get_option('ubp_mix_match_box_short_description'); ?>
                                <select name="ubp_mix_match_box_short_description" id="ubp_mix_match_box_short_description">
                                    <option value="above_title" <?php selected($value, 'above_title', true) ?>><?php esc_html_e('Above the box product title','wc-ubp'); ?></option>
                                    <option value="below_title" <?php selected($value, 'below_title', true) ?>><?php esc_html_e('Below the box product title','wc-ubp'); ?></option>
                                    <option value="below_layout" <?php selected($value, 'below_layout', true) ?>><?php esc_html_e('Below the box products layout','wc-ubp'); ?></option>
                                </select>
							</td>
						</tr>
						<tr>
							<th><label for="ubp_mix_match_box_max_posts_per_page"><?php esc_html_e('Products Per Page','wc-ubp'); ?></label></th>
							<td><?php $value=get_option('ubp_mix_match_box_max_posts_per_page');  ?>
								<p><input type="number" name="ubp_mix_match_box_max_posts_per_page" id="ubp_mix_match_box_max_posts_per_page" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="<?php esc_html_e('Box items per page', 'wc-ubp' ); ?>" min="0"><p>
							</td>
						</tr>
						<tr>
							<th><label for="ubp_mix_match_box_price_label"><?php esc_html_e('Box Price Label','wc-ubp'); ?></label></th>
							<td><?php $value=get_option('ubp_mix_match_box_price_label');  ?>
								<p><input type="text" name="ubp_mix_match_box_price_label" id="ubp_mix_match_box_price_label" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="<?php esc_html_e( 'Box Total: ', 'wc-ubp' ); ?>"><p>
							</td>
						</tr>
						<tr>
							<th><label for="ubp_mix_match_box_bg_color"><?php esc_html_e('Boxes Background Color','wc-ubp'); ?></label></th>
							<td><?php $value=get_option('ubp_mix_match_box_bg_color'); 
									  $value=empty($value) ? '#8224e3' : $value; ?>
								<p><input type="text" name="ubp_mix_match_box_bg_color" id="ubp_mix_match_box_bg_color" value="<?php echo esc_attr($value); ?>"><p>
							</td>
						</tr>
						<tr>
							<th><label for="ubp_mix_match_box_border_color"><?php esc_html_e('Boxes Border Color','wc-ubp'); ?></label></th>
							<td><?php $value=get_option('ubp_mix_match_box_border_color'); 
									  $value=empty($value) ? '#8224e3' : $value; ?>
								<p><input type="text" name="ubp_mix_match_box_border_color" id="ubp_mix_match_box_border_color" value="<?php echo esc_attr($value); ?>"><p>
							</td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}
		public function mix_match_addons_func(){
			?>
			<div class="wrap mix_match_addons">
				<h1><?php esc_html_e('Mix & Match Box Product Addon Plugins','wc-ubp'); ?></h1><br>
				<div class="upb-row">
					<div class="ubp-col-2">
						<div class="ubp-inner">
							<span>
								<img src="<?php echo WOO_UBP_URL; ?>assets/images/box_subscription.jpg"/>
							</span>
							<span class="ubp_caption">
								<span class="ubp_title">
									<?php esc_html_e('Mix & Match Compability Addon For Woocommerce Subscriptions','wc-ubp') ?>
								</span>
								<span>
									<a href="https://codecanyon.net/item/mix-match-pro-addon-for-subscription-plugin/23102423" class="button button-secondary" target="_blank"><?php esc_html_e('View Product','wc-ubp'); ?></a>
								</span>
							</span>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		public function css(){
			?>
			<style type="text/css">
				li#toplevel_page_mix-match-box div.wp-menu-image:before {
				    color: #d60000 !important;
				}
			</style>
			<?php
		}
	}
	new UBP_Ultimate_Box_Main_Settings();
}