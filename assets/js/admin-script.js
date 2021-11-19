// JavaScript Document
jQuery(document).ready(function ($) {
	jQuery('.options_group.pricing').addClass('show_if_box_product');
	jQuery('.options_group.subscription_pricing ').addClass('show_if_box_product');
	jQuery('.options_group ._tax_status_field').parent().addClass('show_if_box_product');
	jQuery('.inventory_options.inventory_tab').addClass('show_if_box_product');
	jQuery('.options_group ._manage_stock_field').addClass('show_if_box_product');
	if (jQuery('#ubp_mix_match_box_bg_color').length > 0) {
		jQuery('#ubp_mix_match_box_bg_color').wpColorPicker();
	}
	if (jQuery('#ubp_mix_match_box_border_color').length > 0) {
		jQuery('#ubp_mix_match_box_border_color').wpColorPicker();
	}
	/*if(jQuery('.ubp_box_layout').val()){
		let box = jQuery('.ubp_box_layout').val();
		if(box=='horizontal'){
			setTimeout(function(){
				jQuery('#ubp_custom_product_box #ubp_box_columns').hide();
				jQuery('#ubp_custom_product_box #ubp_products_columns').hide();
			},1000);
		}
	}*/


	// Change number products by layout
	$('#cgc_ubp_box_layout').on('change', function () {

		if ($(this).find(":selected").val() === 'cgc_layout_01') {
			$('#ubp_box_max_products, #ubp_box_min_products').val(5);
		}

		if ($(this).find(":selected").val() === 'cgc_layout_02') {
			$('#ubp_box_max_products, $ubp_box_min_products').val(5);
		}

		if ($(this).find(":selected").val() === 'cgc_layout_03') {
			$('#ubp_box_max_products, #ubp_box_min_products').val(4);
		}

		if ($(this).find(":selected").val() === 'cgc_layout_04') {
			$('#ubp_box_max_products, #ubp_box_min_products').val(4);
		}
	})


	$('#ubp_box_layout').on('change', function () {
		if ($(this).find(":selected").val() === 'horizontal') {
			$('.form-field.ubp_box_columns_field, .form-field.ubp_products_columns_field').hide();
		} else {
			$('.form-field.ubp_box_columns_field, .form-field.ubp_products_columns_field').show();
		}
	});

	if (!$('#ubp_box_product_categories_enable').is(':checked')) {
		$('.ubp_products_categories_field').hide();
	}

	jQuery('#ubp_box_product_categories_enable').on('click', function () {
		if (jQuery(this).is(':checked')) {
			jQuery('p.form-field.ubp_products_categories_field').show();
		} else {
			jQuery('p.form-field.ubp_products_categories_field').hide();
		}
	});
	function ubp_get_products_prefilled() {
		let products = jQuery('#ubp_custom_product_box input[name="ubp_products_addons_values"]').val();
		products = jQuery.parseJSON(products);
		let html = '';
		if (products && products.length > 0) {
			html += '<tr><td><input type="checkbox" name="ubp_mandatory[]" value=""></td>';
			html += '<td><select name="ubp_select_prefill_products[]" class="widefat">';
			let options = '';
			for (var i = 0; i < products.length; i++) {
				options += '<option value="' + products[i].id + '">' + products[i].text + '</option>';
			}
			html += options + '</select></td>';
			html += '<td><input type="number" name="ubp_prefill_qty[]" value="1" min="1"></td>';
			html += '<td><span class="ubp_remove_prefill">-</span><span class="ubp_add_prefill">+</span></td>';
			html += '</tr>';
		}
		return html;
	}
	jQuery(document).on('click', '.ubp_prefilled_section .ubp_add_prefill', function () {
		let me = jQuery(this);
		if (jQuery('.ubp_prefilled_section table tbody tr').length < jQuery('#ubp_box_max_products').val()) {
			let html = ubp_get_products_prefilled();
			me.closest('#ubp_custom_product_box').find('.ubp_prefilled_section table tbody').append(html);
			me.hide();
			jQuery(document).find('.ubp_prefilled_section table tbody select').trigger('change');
		}
	});
	jQuery(document).on('click', '.ubp_prefilled_section .ubp_remove_prefill', function () {
		let me = jQuery(this);
		if (me.closest('tbody').children().length === 1) {
			jQuery("#ubp_allow_prefilled").trigger('click');
		}
		me.closest('tr').remove();
		jQuery('.ubp_prefilled_section table tbody').find('tr:last').find('.ubp_add_prefill').show();
	});
	jQuery(document).on("click", "#ubp_allow_prefilled", function (event) {
		let me = jQuery(this);
		let tbody = me.closest('#ubp_custom_product_box').find('.ubp_prefilled_section table tbody');
		if (me.is(':checked')) {
			if (tbody.children().length === 0) {
				let html = ubp_get_products_prefilled();
				tbody.html(html);
				jQuery(document).find('.ubp_prefilled_section table tbody select').trigger('change');
			}
		}
		ubp_prefill_box_products();
	});
	function ubp_prefill_box_products() {
		if (jQuery("#ubp_allow_prefilled").is(':checked') && jQuery('#ubp_custom_product_box table tbody').children().length !== 0) {
			jQuery('#ubp_custom_product_box').find('.ubp_prefilled_section').show();
		} else {
			jQuery('#ubp_custom_product_box').find('.ubp_prefilled_section').hide();
			jQuery("#ubp_allow_prefilled").prop('checked', false);
		}
	}
	ubp_prefill_box_products();
	// pre selected values of select2
	var preselected = jQuery('#ubp_custom_product_box input[name="ubp_products_addons_values"]').val();
	// select multitselect ajax
	jQuery('#ubp_products_addons').select2({
		ajax: {
			url: wooubp.ajaxurl,
			dataType: 'json',
			delay: 250,
			data: function (params) {
				var query = {
					search: params.term,
					action: 'ubp_search_product_addons'
				}
				return query;
			},
			processResults: function (data) {
				return {
					results: data
				};
			},
			cache: false
		},
		escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
		minimumInputLength: 3,
		multiple: true,
		data: jQuery.parseJSON(preselected),
		//maximumSelectionLength: 2
		closeOnSelect: false,
		templateResult: formatProduct,
		templateSelection: formatProductSelection
	});
	jQuery('#ubp_products_addons option').each(function () {
		jQuery(this).attr('selected', 'selected');
	});
	jQuery('#ubp_products_addons option').trigger('change');
	function formatProduct(repo) {
		var markup = "<li class='select2-result-products clearfix' prod-id='" + repo.id + "'>" + repo.text + "</li>";
		return markup;
	}
	function formatProductSelection(repo) {
		return repo.text;
	}
	// triggers on selection of products
	jQuery('#ubp_products_addons').change(function () {
		var selections = (jQuery('#ubp_products_addons').select2('data'));
		let arr = [];
		if (selections.length > 0) {
			for (var i = 0; i < selections.length; ++i) {
				arr.push({ 'id': selections[i].id, 'text': selections[i].text });
			}
		}
		jQuery('#ubp_custom_product_box input[name="ubp_products_addons_values"]').val(JSON.stringify(arr));
	});
	// on select a product
	jQuery('#ubp_products_addons').on('select2:select', function (e) {
		let opt = e.params.data;
		jQuery('.ubp_prefilled_section table tbody tr select').each(function (o) {
			jQuery(this).append('<option value="' + opt.id + '">' + opt.text + '</option>').trigger("chosen:updated");
		});
	});
	// remove any selected product
	jQuery('#ubp_products_addons').on('select2:unselect', function (e) {
		let opt = e.params.data;
		jQuery('.ubp_prefilled_section table tbody tr select').each(function (o) {
			let select = jQuery(this);
			jQuery.each(select.find('option'), function (value, txt) {
				if (jQuery(this).val() === opt.id && jQuery(this).is(':selected')) {
					jQuery(this).closest('tr').remove();
				} else if (jQuery(this).val() === opt.id) {
					jQuery(this).remove();
				}
			});
		});
		ubp_prefill_box_products();
	});
	// set value for prefill
	jQuery(document).find('.ubp_prefilled_section table tbody select').live('change', function () {
		let me = jQuery(this);
		me.closest('tr').find('input[type="checkbox"]').attr('value', me.val());
	});
	//top ends
	// jQuery('.ubp_products_addons_field .select2-selection ul').sortable({
	//   containment: 'parent',
	// 	update: function() {
	//        jQuery('#ubp_products_addons').trigger('change');
	//     }
	// });
});