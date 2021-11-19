jQuery(document).ready(function ($) {
    $('.ubp_box_add_to_cart_button').click(function (e) {
        let $this = this;

        jQuery.ajax({
            type: 'post',
            url: ubpAjaxObj.ajax_url,
            data: {
                action: 'ubp_ajax_add_to_cart',
                ubp_products_id: $('#ubp-bundle-add-to-cart').val(),
                ubp_main_product_id : $('#ubp-product-id').val(),
            },
            success: function (data) {
            },
            always: function (response) {

            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
    });
});