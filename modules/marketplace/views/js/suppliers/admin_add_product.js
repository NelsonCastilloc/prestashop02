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
	var selected_id_customer = $("[name='shop_customer']").val();
	getSuppliersByIdCustomerAndLangId(selected_id_customer);
	$(document).on('change', "[name='shop_customer']", function(){
		selected_id_customer = $("[name='shop_customer']").val();
		getSuppliersByIdCustomerAndLangId(selected_id_customer);
	});

	function getSuppliersByIdCustomerAndLangId(selected_id_customer)
	{
		$('body').css('opacity', '0.5');
		$('#ajax_loader').css('display', 'block');
		$.ajax({
			url: admin_ajax_link,
			type: 'POST',
			data: {
				selected_id_customer: selected_id_customer,
				ajax: true,
				action: 'getSupplierByCustomerId'
			},
			dataType: 'json',
			success: function(response) {
				$('body').css('opacity', '1');
				$('#ajax_loader').css('display', 'none');
				$('#supplier_list_tbody tr').remove();
				if(response.status == 1) {
					$.each(response.info, function(index, value){
						$('#supplier_list_tbody').append("<tr><td><input type='checkbox' class='form-control supplier_checkbox' name='selected_suppliers[]' value="+value.id_supplier+" style='width:unset;border:unset;outline:unset;box-shadow:unset;' /></td><td>"+value.name+"</td><td><input id=supplier_radio_"+value.id_supplier+" value="+value.id_supplier+" type='radio' disabled='disabled' class='form-control supplier_radio' name='default_supplier' style='width:unset;' /></td></tr>");
					});
					if($('.supplier_checkbox').length > 1 && $('#selectAllSuppliers').length == 0){
						$('.wk_select_supplier').html('<input value="" id="selectAllSuppliers" type="checkbox" class="selectAllSuppliers  supplier_custom_css" name=""/>')
					}
				} else {
					$('#supplier_list_tbody').append("<tr><td colspan='3'>"+no_supplier+"</td></tr>");
				}
			},fail: function(response){
				$('body').css('opacity', '1');
				$('#ajax_loader').css('display', 'none');
				$('#supplier_list_tbody tr').remove();
				$('#supplier_list_tbody').append("<tr><td colspan='3'>"+no_supplier+"</td></tr>");
			},error: function(response){
				$('body').css('opacity', '1');
				$('#ajax_loader').css('display', 'none');
				$('#supplier_list_tbody tr').remove();
				$('#supplier_list_tbody').append("<tr><td colspan='3'>"+no_supplier+"</td></tr>");
			}
		});
	}
});