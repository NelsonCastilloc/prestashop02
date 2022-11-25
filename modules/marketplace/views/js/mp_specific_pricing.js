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
	$('#leave_bprice').click(function() {
		if (this.checked) {
			$('#sp_price').val('');
			$('#sp_price').attr('disabled', 'disabled');
		} else {
			$('#sp_price').removeAttr('disabled');
		}
	});

	$('#leave_bprice_edit').click(function() {
		if (this.checked) {
			$('#sp_price_edit').val('');
			$('#sp_price_edit').attr('disabled', 'disabled');
		} else {
			$('#sp_price_edit').removeAttr('disabled');
		}
	});

	$('#sp_reduction_type').on('change', function() {
		if ($(this).attr('value') == 'percentage') {
			$('#sp_reduction_tax').hide();
		}
		else {
			$('#sp_reduction_tax').show();
		}
	});

	$('.slot_delete').click(function(e){
		e.preventDefault();
		var slot_id = $(this).attr('id_slot');
		if (confirm(conf_delete))
			delete_slot(slot_id);
	});



	$(function () {
		$("#sp_from, #sp_to, #sp_to_edit, #sp_from_edit").datetimepicker({
			showSecond: true,
			dateFormat:"yy-mm-dd",
			timeFormat: mpspecificadmin_datepicker ? "hh:mm:ss" : "HH:mm:ss",
			container : '#slot_edit_link_modal',


			beforeShow: function(input, inst) {
				if (input.id == 'sp_from_edit' || input.id == 'sp_to_edit') {
					if (mpspecificadmin_datepicker) {
						$(inst.dpDiv).css({
							marginTop : '-50px'
						})
					}
					$(inst.dpDiv).addClass('modaldatetime_height');
				} else {
					$(inst.dpDiv).removeClass('modaldatetime_height');
				}
			}

		});
	});

	$('#show_specific_price').click(function() {
		$('#SubmitCreate').hide();
		$('#add_specific_price').slideToggle('slow');
		$('#hide_specific_price').show();
		$('#show_specific_price').hide();

		$('#showTpl').val('1');
		return false;
	});

	$('#hide_specific_price').click(function() {
		$('#SubmitCreate').show();
		$('#add_specific_price').slideToggle('slow');
		$('#hide_specific_price').hide();
		$('#show_specific_price').show();

		$('#sp_from_quantity').val('1')
		$('#sp_reduction').val('0.00')


		$('#showTpl').val('0');
		return false;
	});
});

// delete slot price
$(document).on('click', 'a[name=slot_delete_link]', function(e){
	e.preventDefault();
	if (!confirm(conf_delete)) {
		return false;
	}
	$('.wkslotprice_loader').show();
	var del_id = this.id;
	$.ajax({
		url : wkmpspecific_ajax,
		type : 'POST',
		async 	: 	true,
		cache 	: 	false,
		dataType: "json",
		data:{
			token: $('#wk-static-token').val(),
			id_delete : del_id,
			delete_slot : 1,
			ajax : true,
			action : 'MpSpecificPriceRule',
			mp_product_id:$('#mp_product_id').val()
		},
		success:function(data1)
		{
			$('.wkslotprice_loader').hide();
			if(data1 == 1) {
				$('#slotcontent'+del_id).fadeOut(500, function(){ $(this).remove();});
			}
			else if(data1 == 2) {
				showProductErrors(delete_err);
				return false;
			}
		},error:function(){
			$('.wkslotprice_loader').hide();
		}
	});
	return false;
});

// aadding slot price
$(document).on('click', '#add_btn', function(e){
	e.preventDefault();
	var sp_reduction = $('#sp_reduction').val();
	var sp_from_quantity = $('#sp_from_quantity').val();
	if (!sp_from_quantity) {
		showProductErrors(sp_quantity_empty, 'sp_from_quantity');
		return false;
	} else if(!isInt(sp_from_quantity)) {
		showProductErrors(invalid_qty, 'sp_from_quantity');
		return false;
	}
	if (!sp_reduction) {
		showProductErrors(sp_reduction_err, 'sp_reduction');
		return false;
	}
	$('.wkslotprice_loader').show();
	var str = $("#add_specific_price *").serialize();
	$.ajax({
		url : wkmpspecific_ajax,
		type : 'POST',
		async 	: 	true,
		cache 	: 	false,
		dataType: "json",
		data:{
			token: $('#wk-static-token').val(),
			dataval : str ,ajax : true, action : 'MpSpecificPriceRule',mp_product_id:$('#mp_product_id').val()
		},
		success : function(data1){
			$('.wkslotprice_loader').hide();

			if(data1 == 1) {
				showSuccessMessage(success);
			}
			else if(data1 == 2) {
				showProductErrors(no_reduction);
				return false;
			}
			else if(data1 == 3) {
				showProductErrors(invalid_range);
				return false;
			}
			else if(data1 == 4) {
				showProductErrors(reduction_range);
				return false;
			}
			else if(data1 == 5) {
				showProductErrors(wrong_id);
				return false;
			}
			else if(data1 == 6) {
				showProductErrors(invalid_price);
				return false;
			}
			else if(data1 == 7) {
				showProductErrors(invalid_qty);
				return false;
			}
			else if(data1 == 8) {
				showProductErrors(select_dis_type);
				return false;
			}
			else if(data1 == 9) {
				showProductErrors(date_invalid);
				return false;
			}
			else if(data1 == 10) {
				showProductErrors(already_exist);
				return false;
			}
			location.reload(true);
		}
  	});

	  return false;
});



$(document).on('change', '#sp_reduction_type, #sp_reduction_type_edit', function() {
	reduction_type = $(this).val()

	if (reduction_type == 'percentage') {
		$('#sp_reduction_tax').hide()
		$('#sp_reduction_tax_edit').hide()
	} else {
		$('#sp_reduction_tax').show()
		$('#sp_reduction_tax_edit').show()
	}
})

$(document).on('click', '.slot_edit_link', function(e) {
	let editId = $(e.target).parent().attr('id') != undefined ? $(e.target).parent().attr('id') : $(e.target).attr('id')
	$('#editSpecificPriceId').val(editId)
	$.ajax({
		url : wkmpspecific_ajax,
		type : 'POST',
		async 	: 	true,
		cache 	: 	false,
		dataType: "json",
		data:{
                token: $('#wk-static-token').val(),
				editId : editId ,ajax : true, action : 'MpSpecificPriceRule',mp_product_id:$('#mp_product_id').val()
		},
		success : function(prevData){
			if (prevData) {
				$('#id_customer_edit').val(prevData.id_customer)
				$('#spm_currency_edit_0').val(prevData.id_currency)
				$('#sp_id_product_attribute_edit').val(prevData.id_product_attribute)
				$('#sp_id_country_edit').val(prevData.id_country)
				$('#sp_id_group_edit').val(prevData.id_group)
				$('#sp_from_edit').val(prevData.from)
				$('#sp_to_edit').val(prevData.to)
				$('#id_specific_price').val(prevData.id_specific_price)
				$('#sp_from_quantity_edit').val(prevData.from_quantity)
				if (prevData.price > 0) {
					$('#sp_price_edit').val(prevData.price)
				} else {
					$('#sp_price_edit').val('')
				}
				if (prevData.price > 0) {
					$('#leave_bprice_edit').click();
				}
				if (prevData.reduction_type == "percentage") {
					$('#sp_reduction_edit').val(prevData.reduction*100)
					$('#sp_reduction_tax_edit').val(prevData.reduction_tax).hide()
				} else {
					$('#sp_reduction_edit').val(prevData.reduction)
					$('#sp_reduction_tax_edit').val(prevData.reduction_tax).show()
				}
				$('#sp_reduction_type_edit').val(prevData.reduction_type)
				if (prevData.id_customer > 0) {
					selectedCustomer = `<ul class="list-unstyled" style="text-align:left;">
					<li>`+prevData.customer_name +` - `+prevData.customer_email +`<a onclick="removeSpecificCustomer(`+ prevData.id_customer +`); return false" href="#" style="position: absolute;top: -5px;"><i class="material-icons delete">clear</i></a></li></ul>`;
					$('#customers_edit').html(selectedCustomer);
				}
			}else{
				$('#slot_edit_link_modal').hide()
				$(".modal-backdrop").css('opacity','0');
			}
		}
  	});

	  return false;

})

function removeSpecificCustomer(idCustomer) {
	if (confirm(conf_delete_customer)) {
		$('#id_customer_edit').val(0)
		$('#customers_edit').html('');
	}
}

// updating the slot
$(document).on('click', '#update_btn', function(e){
	e.preventDefault();
	var sp_reduction = $('#sp_reduction_edit').val();
	var sp_from_quantity = $('#sp_from_quantity_edit').val();
	if (!sp_from_quantity) {
		showProductErrors(sp_quantity_empty, 'sp_from_quantity_edit');
		return false;
	} else if(!isInt(sp_from_quantity)) {
		showProductErrors(invalid_qty, 'sp_from_quantity_edit');
		return false;
	}
	if (!sp_reduction) {
		showProductErrors(sp_reduction_err, 'sp_reduction_edit');
		return false;
	}

	$('.wkslotprice_loader').show();
	var str = $("#edit_specific_price *").serialize();
	if ($("#leave_bprice_edit").checked) {
		str += '&leave_bprice_edit=1';
	}

	$.ajax({
		url : wkmpspecific_ajax,
		type : 'POST',
		async 	: 	true,
		cache 	: 	false,
		dataType: "json",
		data:{
			token: $('#wk-static-token').val(),
			dataval : str ,ajax : true, action : 'MpSpecificPriceRule',mp_product_id:$('#mp_product_id').val()
		},
		success : function(data1){
			$('.wkslotprice_loader').hide();
			if(data1 == 1) {
				showSuccessMessage(update_success);
			}
			else if(data1 == 2) {
				showErrorMessage(no_reduction);
				return false;
			}
			else if(data1 == 3) {
				showErrorMessage(invalid_range);
				return false;
			}
			else if(data1 == 4) {
				showErrorMessage(reduction_range);
				return false;
			}
			else if(data1 == 5) {
				showErrorMessage(wrong_id);
				return false;
			}
			else if(data1 == 6) {
				showErrorMessage(invalid_price);
				return false;
			}
			else if(data1 == 7) {
				showErrorMessage(invalid_qty);
				return false;
			}
			else if(data1 == 8) {
				showErrorMessage(select_dis_type);
				return false;
			}
			else if(data1 == 9) {
				showErrorMessage(date_invalid);
				return false;
			}
			else if(data1 == 10) {
				showErrorMessage(already_exist);
				return false;
			}else{
				showErrorMessage(wrong_id);
				return false;
			}
			location.reload(true);
		},
		error: function(){
			$('.wkslotprice_loader').hide();
		}

  	});

	  return false;
});


$(window).load(function(){
	$('.selector').attr('style','');
});
function selectcustomer(search_type ,id_customer, name)
{

	if (search_type.id == 'wkslotcustomer') {
		$('#id_customer').val(id_customer);
		$('#wkslotcustomer').val(name);
		$('#customers').empty();
	} else {
		$('#id_customer_edit').val(id_customer);
		$('#wkslotcustomer_edit').val(name);
		$('#customers_edit').empty();
	}

	}

function changeCurrencySpecificPrice(index)
{
	var id_currency = $('#spm_currency_'+index).val();
	if (id_currency > 0) {
		$('#sp_reduction_type option[value="amount"]').text($('#spm_currency_'+index+' option[value='+id_currency+']').text());
	}
	else if (typeof currencyName !== 'undefined') {
		$('#sp_reduction_type option[value="amount"]').text(currencyName);
	}
}

function changeCurrencySpecificPriceUpdate(index)
{
	var id_currency = $('#spm_currency_edit_'+index).val();
	if (id_currency > 0) {
		$('#sp_reduction_type_edit option[value="amount"]').text($('#spm_currency_'+index+' option[value='+id_currency+']').text());
	}
	else if (typeof currencyName !== 'undefined') {
		$('#sp_reduction_type_edit option[value="amount"]').text(currencyName);
	}
}

function isInt(value)
{
	return !isNaN(value) && parseInt(Number(value)) == value;
}

function delete_slot(slot_id)
{
	var mp_product_id = $('#mp_product_id').val();
	$.ajax({
		type:'post',
		url:$('#delete_link').val(),
		async: true,
		data:{
			slot_id:slot_id,
			mp_product_id:mp_product_id
		},
		cache: false,
		success: function(data1)
		{
			if(data1)
				$('#slotcontent'+slot_id).remove();
			else
			showProductErrors(error);
		}
	});
}
$(document).on('keyup', '#wkslotcustomer, #wkslotcustomer_edit', function(e){
	var field =  e.target.value
	if(field != '' && field.length > 2) {
		$.ajax({
			url : wkmpspecific_ajax,
			type : 'POST',
			async 	: 	true,
			cache 	: 	false,
			dataType: "json",
			'data': {
				token: $('#wk-static-token').val(),
				'cust_search': '1',
				'keywords' : field,
				ajax : true,
				action : 'MpSpecificPriceRule',
				mp_product_id:$('#mp_product_id').val()
			},
			'success': function(result) {
				if(result.found) {
					var html = `<ul class="list-unstyled" style="text-align:left"`;
					$.each(result.customers, function() {
						html += '<li>'+this.firstname+' '+this.lastname;
						html += ' - '+this.email;
						html += '<a onclick="selectcustomer('+e.target.id+','+this.id_customer+', \''+this.firstname+' '+this.lastname+'\'); return false" href="#" class="btn btn-default">'+Choose+'</a></li>';
					});
					html += '</ul>';
				} else {
					html = '<div class="alert alert-warning">'+no_customers_found+'</div>';
				}

				if (e.target.id =='wkslotcustomer_edit' ) {
					$('#customers_edit').html(html);
				} else {
					$('#customers').html(html);
				}

			},
		});
	}
});