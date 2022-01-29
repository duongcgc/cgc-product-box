<?php
/**
 * The template for displaying product content of box type product in the single-product.php template
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 */
defined( 'ABSPATH' ) || exit;
/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked wc_print_notices - 10
 */
do_action( 'woocommerce_before_single_product' );
if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
do_action('ubp_before_box_product_title', get_the_ID());
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class(); ?>>
	<form method="post" name="" enctype="multipart/form-data" class="cart">
	<?php
		/**
		 * Hook: cpb_custom_box_product_layout
		 *
		 * @hooked cpb_custom_box_product_title - 5
		 * @hooked cpb_custom_box_product_layouts - 6
		 */
		do_action( 'ubp_custom_box_product_layout',get_the_ID() );
	?>
	</form>
	<div class="ubp_short_description">
		<?php do_action('ubp_after_box_product_layout', get_the_ID()); ?>
	</div>
	<div class="summary entry-summary">
	</div>
	<?php
		/**
		 * Hook: woocommerce_after_single_product_summary.
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		do_action( 'woocommerce_after_single_product_summary' );
	?>
</div>
<?php do_action( 'woocommerce_after_single_product' ); ?>

