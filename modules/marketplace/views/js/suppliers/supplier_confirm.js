/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

$(document).ready(function(){
	$(document).on('click', '#supplier_confirm_leave', function(){
		var con = confirm(confirm_msg);
		if(con) {
			if ((typeof backend_controller != 'undefined') && backend_controller == 1) {
				window.location.href = admin_ajax_link+'&addwk_mp_suppliers';
			} else {
				window.location.href = add_supplier;
			}
		}
	});

	$(document).on('click', '.selectAllSuppliers', function(){
		if ($(this).is(':checked')) {
			$('input[name="selected_suppliers[]"]').each(function(){
				$(this).attr('checked','checked')
			})
		} else {
			$('input[name="selected_suppliers[]"]').each(function(){
				$(this).removeAttr('checked')
			})
		}
	})

	$(document).on('change', '.supplier_checkbox', function(){
		if($('#selectAllSuppliers').prop('checked')){
            $('#selectAllSuppliers').prop('checked',false);
        }
        if($('.supplier_checkbox').length == $('.supplier_checkbox:checked').length){
            $('#selectAllSuppliers').prop('checked',true);
        }

		var id = $(this).val();
		if ($(this).is(":checked")) {
			$('#supplier_radio_'+id).removeAttr('disabled');
			$('.supplier_radio').removeAttr('checked');
			$('#supplier_radio_'+id).prop({checked: true});
		} else {
			var allVals = [];
			$('.supplier_checkbox:checkbox:checked').each(function() {
				allVals.push($(this).val());
			});

			if (allVals.length) {
				if (id == $('.supplier_radio:radio:checked').val()) {
					$('#supplier_radio_'+allVals[0]).prop({checked: true});
				}
			} else {
				$('#supplier_radio_'+id).removeAttr('checked');
			}
			$('#supplier_radio_'+id).prop({disabled: true});
		}
		if (typeof path_addproduct == 'undefined') {
			path_addproduct = path_sellerproduct;
		}
		$.ajax({
			url: path_addproduct,
			data: {
				ajax: "1",
				id_supplier: id,
				mp_product_id: $('#mp_product_id').val(),
				token: $('#wk-static-token').val(),
				action: "getSupplierReferences",
			},
			success: function(resp) {
				if (resp) {
					$("#supplier_combination_collection").html(resp);
				} else {
					$("#supplier_combination_collection").html('');
				}
			}
		});
	});

	$(document).on('click','#selectAllSuppliers',function(){
		$('.supplier_checkbox').prop('checked',$(this).prop('checked'));
		if($(this).prop('checked')){
			$('.supplier_checkbox').each(function(i,ele){
				var id = $(ele).val();
				$('#supplier_radio_'+id).removeAttr('disabled');
					$('.supplier_radio').removeAttr('checked');
					$('#supplier_radio_'+id).prop({checked: true});
				}
			)
		}else{
			$('.supplier_checkbox').each(function(i,ele){
				var id = $(ele).val();
				$('#supplier_radio_'+id).removeAttr('checked');
				$('#supplier_radio_'+id).prop({disabled: true});
			});
		}
	})
});

function changeSuppliersCurrency(id_supplier, id_comb)
{
	selector = '#supplier_combination_'+id_supplier+'_'+id_comb+'_product_price_currency'
	var symbol = $(selector+' :selected').data('symbol');
	if (typeof symbol !== 'undefined') {
		$(selector).closest('tr').find('.input-group-addon').text(symbol);
	}
}