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
	var countryid = $("#suppcountry").val();
	getAndSetStateByCountryId(countryid);


    if (typeof allow_tagify != 'undefined') {
        $.each(languages, function(key, value) {
            $('#meta_key_'+value.id_lang).tagify({
                delimiters: [13,44],
                addTagPrompt: addkeywords
            });
        });
    }

	$(document).on('submit', '#supplier_form', function(e){
		disbleButton();
		var name = $('#suppname').val().trim();
		var phone = $('#suppphone').val().trim();
		var mobile = $('#suppmobile').val().trim();
		var suppaddress = $('#suppaddress').val().trim();
		var suppzip = $('#suppzip').val().trim();
		var suppcity = $('#suppcity').val().trim();
		let supplier_logo = $('#supplier_logo').val();
		let genericFieldName = /^[^.<>={}]*$/u
		let genericFieldRegex = /^[^<>={}]*$/u
		let checkAddress = /^[^!<>?=+@{}_$%]*$/u
		let checkCity = /^[^!<>;?=+@#"°{}_$%]*$/u
		let checkPhone = /^[+0-9. ()\/-]*$/
		let pinCheckCode = /^[a-zA-Z 0-9-]+$/
		let zipCheckCode = /^[NLCnlc 0-9-]+$/

		if (name == '') {
			showErrorMessage(req_suppname);
			$('#suppname').focus();
			enableButton();
			return false;
		} else if (supplier_logo != '') {
			var logo_arry = supplier_logo.split('.');
            var ext = logo_arry.pop();
            ext_array = new Array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF');
            if ($.inArray(ext, ext_array) == -1) {
                showErrorMessage(invalid_logo);
                enableButton();
				return false;
            }
        } else if (!genericFieldName.test(name)) {
			showErrorMessage(inv_suppname);
			$('#suppname').focus();
			enableButton();
			return false;
		} else if(suppaddress == '') {
			showErrorMessage(req_suppaddress);
			$('#suppaddress').focus();
			enableButton();
			return false;
		} else if(!checkAddress.test(suppaddress)) {
			showErrorMessage(inv_suppaddress);
			$('#suppaddress').focus();
			enableButton();
			return false;
		} else if (suppcity == '') {
			showErrorMessage(req_suppcity);
			$('#suppcity').focus();
			enableButton();
			return false;
		} else if (!checkCity.test(suppcity)) {
			showErrorMessage(inv_suppcity);
			$('#suppcity').focus();
			enableButton();
			return false;
		} else if (!checkPhone.test(phone)) {
			if (phone) {
				showErrorMessage(inv_suppphone);
				$('#suppphone').focus();
				enableButton();
				return false;
			}
		} else if (!checkPhone.test(mobile)) {
			if (mobile) {
				showErrorMessage(inv_suppphone);
				$('#suppmobile').focus();
				enableButton();
				return false;
			}
		} else if (!zipCheckCode.test(suppzip) || !pinCheckCode.test(suppzip)) {
			if (suppzip) {
				showErrorMessage(inv_suppzip);
				$('#suppzip').focus();
				enableButton();
				return false;
			}
		}


		$.each(languages, function(key, value) {
    		if ($('#meta_title_'+value.id_lang).val().trim()) {
				if (!genericFieldRegex.test($('#meta_title_'+value.id_lang).val().trim())) {
					$('#meta_title_'+value.id_lang).focus();
					showErrorMessage(inv_language_title)
					e.preventDefault()
				}
			}

        });



		$.each(languages, function(key, value) {
            if ($('#meta_desc_'+value.id_lang).val().trim()) {
				if (!genericFieldRegex.test($('#meta_desc_'+value.id_lang).val().trim())) {
					$('#meta_desc_'+value.id_lang).focus();
					showErrorMessage(inv_language_desc)
					e.preventDefault()
				}
			}
        });


		$.each(languages, function(key, value) {
            $('#meta_key_'+value.id_lang).tagify('serialize');
        });

	});

	$(document).on('change', '#suppcountry', function(){
		var countryid = $("#suppcountry").val();
		getAndSetStateByCountryId(countryid);
	});

	function getAndSetStateByCountryId(countryid)
	{
		$('#dni_required').hide();
		var suppstate_temp = $('#suppstate_temp').val();
		$('#ajax_loader').css('display', 'block');
		$.ajax({
			url: supplier_ajax_link,
			type: 'POST',
			data: {
				fun:'get_state',
				countryid: countryid,
				token: static_token,
				action: 'getStateByCountry'
			},
			dataType: 'json',
			success: function(data) {
				$('body').css('opacity', '1');
				if(data.status == 'success') {
					$('#suppstate').empty();
					$.each(data.info, function(index, value){
						if (suppstate_temp == value.id_state) {
							$('#suppstate').append("<option value='"+value.id_state+"' selected='selected'>"+value.name+"</option>");
						} else {
							$('#suppstate').append("<option value='"+value.id_state+"'>"+value.name+"</option>");
						}
					});
					$('.divsuppstate').show();
				} else {
					$('.divsuppstate').hide();
					$('#suppstate').empty();
				}
				$('#ajax_loader').css('display', 'none');
			}, fail: function(){
				$('.divsuppstate').hide();
				$('#suppstate').empty();
				$('#ajax_loader').css('display', 'none');
			}
		});
		dniRequired(countryid);
	}
});

function dniRequired(id_country) {
    $.ajax({
        url: supplier_ajax_link,
        data: {
            ajax: "1",
            id_country: id_country,
            token: static_token,
            action: "DniRequired",
        },
        success: function(resp) {
            if (resp) {
                $("#dni_required").fadeIn();
            } else {
                $("#dni_required").fadeOut();
            }
        }
    });
}

function showSupplierLangField(lang_iso_code, id_lang)
{
	$('#supplier_lang_btn').html(lang_iso_code + ' <span class="caret"></span>');

	$('.desc_div_all').hide();
	$('#desc_div_'+id_lang).show();

	$('.meta_title_div_all').hide();
	$('#meta_title_div_'+id_lang).show();

	$('.meta_desc_div_all').hide();
	$('#meta_desc_div_'+id_lang).show();

	$('.meta_key_div_all').hide();
	$('#meta_key_div_'+id_lang).show();

	$('.all_lang_icon').attr('src', img_dir_l+id_lang+'.jpg');
    $('#choosedLangId').val(id_lang);
}

function showSuccessMessage(msg) {
    $.growl.notice({ title: "", message: msg });
}

function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}

function enableButton() {
	$('#add_supplier').css('pointer-events', '');
	$('#add_supplier').parent().css('cursor', 'pointer');
}

function disbleButton() {
	$('#add_supplier').css('pointer-events', 'none');
	$('#add_supplier').parent().css('cursor', 'not-allowed');
}
