!function(t){"use strict";"function"==typeof define&&define.amd?define(["jquery"],t):"undefined"!=typeof module&&module.exports?module.exports=t(require("jquery")):t(jQuery)}(function(t){var e=-1,o=-1,n=function(t){return parseFloat(t)||0},a=function(e){var o=1,a=t(e),i=null,r=[];return a.each(function(){var e=t(this),a=e.offset().top-n(e.css("margin-top")),s=r.length>0?r[r.length-1]:null;null===s?r.push(e):Math.floor(Math.abs(i-a))<=o?r[r.length-1]=s.add(e):r.push(e),i=a}),r},i=function(e){var o={
	byRow:!0,property:"height",target:null,remove:!1};return"object"==typeof e?t.extend(o,e):("boolean"==typeof e?o.byRow=e:"remove"===e&&(o.remove=!0),o)},r=t.fn.matchHeight=function(e){var o=i(e);if(o.remove){var n=this;return this.css(o.property,""),t.each(r._groups,function(t,e){e.elements=e.elements.not(n)}),this}return this.length<=1&&!o.target?this:(r._groups.push({elements:this,options:o}),r._apply(this,o),this)};r.version="0.7.2",r._groups=[],r._throttle=80,r._maintainScroll=!1,r._beforeUpdate=null,
	r._afterUpdate=null,r._rows=a,r._parse=n,r._parseOptions=i,r._apply=function(e,o){var s=i(o),h=t(e),l=[h],c=t(window).scrollTop(),p=t("html").outerHeight(!0),u=h.parents().filter(":hidden");return u.each(function(){var e=t(this);e.data("style-cache",e.attr("style"))}),u.css("display","block"),s.byRow&&!s.target&&(h.each(function(){var e=t(this),o=e.css("display");"inline-block"!==o&&"flex"!==o&&"inline-flex"!==o&&(o="block"),e.data("style-cache",e.attr("style")),e.css({display:o,"padding-top":"0",
	"padding-bottom":"0","margin-top":"0","margin-bottom":"0","border-top-width":"0","border-bottom-width":"0",height:"100px",overflow:"hidden"})}),l=a(h),h.each(function(){var e=t(this);e.attr("style",e.data("style-cache")||"")})),t.each(l,function(e,o){var a=t(o),i=0;if(s.target)i=s.target.outerHeight(!1);else{if(s.byRow&&a.length<=1)return void a.css(s.property,"");a.each(function(){var e=t(this),o=e.attr("style"),n=e.css("display");"inline-block"!==n&&"flex"!==n&&"inline-flex"!==n&&(n="block");var a={
	display:n};a[s.property]="",e.css(a),e.outerHeight(!1)>i&&(i=e.outerHeight(!1)),o?e.attr("style",o):e.css("display","")})}a.each(function(){var e=t(this),o=0;s.target&&e.is(s.target)||("border-box"!==e.css("box-sizing")&&(o+=n(e.css("border-top-width"))+n(e.css("border-bottom-width")),o+=n(e.css("padding-top"))+n(e.css("padding-bottom"))),e.css(s.property,i-o+"px"))})}),u.each(function(){var e=t(this);e.attr("style",e.data("style-cache")||null)}),r._maintainScroll&&t(window).scrollTop(c/p*t("html").outerHeight(!0)),
	this},r._applyDataApi=function(){var e={};t("[data-match-height], [data-mh]").each(function(){var o=t(this),n=o.attr("data-mh")||o.attr("data-match-height");n in e?e[n]=e[n].add(o):e[n]=o}),t.each(e,function(){this.matchHeight(!0)})};var s=function(e){r._beforeUpdate&&r._beforeUpdate(e,r._groups),t.each(r._groups,function(){r._apply(this.elements,this.options)}),r._afterUpdate&&r._afterUpdate(e,r._groups)};r._update=function(n,a){if(a&&"resize"===a.type){var i=t(window).width();if(i===e)return;e=i;
	}n?o===-1&&(o=setTimeout(function(){s(a),o=-1},r._throttle)):s(a)},t(r._applyDataApi);var h=t.fn.on?"on":"bind";t(window)[h]("load",function(t){r._update(!1,t)}),t(window)[h]("resize orientationchange",function(t){r._update(!0,t)})});

	function isFloat(n){
		return Number(n) === n && n % 1 !== 0;
	}

	var masonryContainer = jQuery('.col-right ul');
	function ubp_resize_the_product_items(){
		if(ubp.layout=='masonry'){
			masonryContainer.masonry({
			  itemSelector: '.col-right ul li',
			  columnWidth: 0,
				//fitWidth: true
			});
		}else{
			jQuery('.col-right ul li figure img').matchHeight();
			jQuery('.col-right ul li figure figcaption').matchHeight();
			jQuery('.col-right ul li').matchHeight();
		}
	}
	function ubp_is_ok_to_add_in_box(prod_id){
		var added=0;
		jQuery('.col-left ul li').each(function(){
			var targ=jQuery(this);
			if(targ.attr('data-prod_id')===prod_id){
				added++;
			}
		});
		//console.log(added);
		if(ubp.options[prod_id] && (ubp.options[prod_id].max===-1 || ubp.options[prod_id].max>added)){
			return true;
		}else{
			return false;
		}
	}
	function ubp_update_options(){
		jQuery('#ubp_cutom_box_product_layouts .col-right ul li').each(function(){
			var item_obj=jQuery.parseJSON(jQuery(this).find('input[type="hidden"]').val());
			if(item_obj.id){
				var id=item_obj.id;
				ubp.options[id]=item_obj;
			}
		});
	}
	jQuery(window).load(function(){
		ubp_resize_the_product_items();
	})
	jQuery(document).ready(function($) {
		var price=0, prod_id=0;
		jQuery('.ubp-loader-ripple').hide();
		if(ubp.options.enabled_categories)
			ubp_update_options();
		// count added
		function ubp_box_count_added_max(){
			var arr =[];
			if(jQuery('#ubp-bundle-add-to-cart').val()!=''){
			  arr = jQuery('#ubp-bundle-add-to-cart').val().split(",");
			}
			return parseInt(arr.length);
		}
		//may be added minimum
		function ubp_box_maybe_added_min(){
			var arr =[];
			if(jQuery('#ubp-bundle-add-to-cart').val()){
				arr= jQuery('#ubp-bundle-add-to-cart').val().split(",");
			}
			if(ubp.options && arr.length && ubp.options.box_min<=arr.length){
				return 1;
			}else{
				return 0;
			}
		}
		//may be variable price
		function ubp_may_be_price_increase(prod_id){
			if(ubp.options.pricing==='per_product_only' || ubp.options.pricing==='per_product_box'){
				var current=0;
				price=0;
				current=jQuery('.ubp_bundle .bundle_price').text();
				if(ubp.options[prod_id] && ubp.options[prod_id].price){
					price=ubp.options[prod_id].price;
					current=parseFloat(current)+parseFloat(price);
					if(isFloat(current)){
						current=current.toFixed(2);
					}
					jQuery('#ubp_cutom_box_product_layouts .price.ubp_bundle .bundle_price').html(current);
				}
			}
		}
		setTimeout(function(){
			if(ubp_box_maybe_added_min()===1 && jQuery('.price.ubp_bundle .bundle_price').text().length){
				jQuery('.ubp_box_add_to_cart_button').removeAttr('disabled');
			}
		},300);
		function ubp_may_be_price_decrease(prod_id){
			if(ubp.options.pricing==='per_product_only' || ubp.options.pricing==='per_product_box'){
				var current=0;
				price=0;
				current=jQuery('.ubp_bundle .bundle_price').text();
				if(ubp.options[prod_id] && ubp.options[prod_id].price){
					price=ubp.options[prod_id].price;
					current=parseFloat(current)-parseFloat(price);
					if(isFloat(current)){
						current=current.toFixed(2);
					}
					jQuery('#ubp_cutom_box_product_layouts .price.ubp_bundle .bundle_price').html(current);
				}
			}
		}
		$('#ubp_error ').hide();
		function ubp_show_error(str){
			$('#ubp_error ').show();
				document.getElementById('ubp_error').innerHTML=str;
				setTimeout(function() {
				$('#ubp_error ').fadeOut(1000);
			}, 1000);
		}
		jQuery(document).on("click",".col-right ul li figure span.add_prod_box", function (e) {
			var targ=$(this);
			prod_id=targ.closest('li').attr('data-product_id');
			var qty =targ.closest('li').find('input[name="qty_'+prod_id+'"]').val();
			if(!targ.closest('li').hasClass('ubp-disabled')){
				for (var i = 0; i < qty; i++) {
				var f =ubp_box_count_added_max();
				var t=parseInt(f);
				if(t>=ubp.options.box_max){
					ubp_show_error("<p>"+ubp.message+"</p>");
					return;
				}else{
					if(ubp_is_ok_to_add_in_box(prod_id)){
						  var image =targ.closest('li').find('img').clone();
						targ.closest('#ubp_cutom_box_product_layouts').find('.col-left ul li:empty:first').attr('data-prod_id',prod_id).append(image);
						var items=jQuery('#ubp-bundle-add-to-cart').val();
						 if(items.length===0){
							 jQuery('#ubp-bundle-add-to-cart').val(prod_id);
						}else{
							jQuery('#ubp-bundle-add-to-cart').val(items+','+prod_id); }
						if(ubp_box_maybe_added_min()===1 && jQuery('.price.ubp_bundle .bundle_price').text().length){
							jQuery('.ubp_box_add_to_cart_button').removeAttr('disabled');
						}
						ubp_may_be_price_increase(prod_id);
					}else{
						ubp_show_error("<p>Product is not available</p>");
						return;
					}
				}
			}
		}
		});
		// remove from box
		$(".col-left ul li").on("click", function (e) {
			e.preventDefault();
			var targ=$(this);
			if(targ.attr('data-need')!=='yes'){
				prod_id=targ.attr('data-prod_id');
				ubp_may_be_price_decrease(prod_id);
				targ.empty();
				targ.attr('data-prod_id','').removeAttr('data-prod_id');
				setTimeout(function(){
					if(ubp_box_maybe_added_min()!==1 ){
						jQuery('.ubp_box_add_to_cart_button').attr('disabled','disabled');
					}
				},100);
			}
		});
		// update hidden field
		jQuery(".col-left ul li").bind("click", function (e) {
			var arr=[];
			jQuery(".col-left ul li").each(function(){
				if($(this).attr('data-prod_id')){
					 prod_id=$(this).data('prod_id');
					arr.push(prod_id);
				}
			});
			jQuery('#ubp-bundle-add-to-cart').val(arr.join(","));
		});
		jQuery(document).on("input", ".col-right ul li figure .qty input[type='text']", function() {
			this.value = this.value.replace(/\D/g,'');
		});
		// quantity update
		 jQuery('.col-right ul li figure .qty .qtyplus').click(function(e){
			// Stop acting like a button
			e.preventDefault();
			// Get the field name
			fieldName = jQuery(this).attr('field');
			// Get its current value
			var currentVal = parseInt(jQuery('input[name='+fieldName+']').val());
			// If is not undefined
			if (!isNaN(currentVal)) {
				// Increment
				jQuery('input[name='+fieldName+']').val(currentVal + 1);
			} else {
				// Otherwise put a 1 there
				jQuery('input[name='+fieldName+']').val(1);
			}
		});
		// This button will decrement the value till 1
		jQuery(".col-right ul li figure .qty .qtyminus").click(function(e) {
			// Stop acting like a button
			e.preventDefault();
			// Get the field name
			fieldName = $(this).attr('field');
			// Get its current value
			var currentVal = parseInt(jQuery('input[name='+fieldName+']').val());
			// If it isn't undefined or its greater than 0
			if (!isNaN(currentVal) && currentVal > 1) {
				// Decrement one
				$('input[name='+fieldName+']').val(currentVal - 1);
			} else {
				// Otherwise put a 1 there
				jQuery('input[name='+fieldName+']').val(1);
			}
		});
		if(jQuery('#ubp_cutom_box_product_layouts p.box_subscription_details').length>=2){
			jQuery('#ubp_cutom_box_product_layouts p.box_subscription_details').last().remove();
		}
		jQuery('#ubp_load_more_items').on('click',function(e){
			e.preventDefault();
			var btn = jQuery(this);
			jQuery('.ubp-loader-ripple').show();
			var paged = btn.attr('data-paged');
			var data = {
				action       : 'ubp_load_box_product_items',
				'product_id'    : ubp.product_id,
				'paged'  : paged
			};
			var scroll = jQuery(window).scrollTop();
			jQuery.post(ubp.ajaxurl, data, function(response) {
				if(response.success){

					let html = $(response.data.html);

					jQuery('#ubp_cutom_box_product_layouts .col-right ul').append(html);
					setTimeout(function(){

						masonryContainer.imagesLoaded(function() {
							masonryContainer.masonry();
							masonryContainer.masonry('appended', $(html));
						});

						// ubp_resize_the_product_items();
						window.scroll(0, scroll);
						jQuery('.ubp-loader-ripple').hide();
						if(ubp.options.enabled_categories)
							ubp_update_options();
					},500);
					if(response.data.paged){
						jQuery('#ubp_load_more_items').attr('data-paged',parseInt(jQuery('#ubp_load_more_items').attr('data-paged'))+1).show();
					}else{
						jQuery('#ubp_load_more_items').hide();
					}
				}
			});
		});
	});