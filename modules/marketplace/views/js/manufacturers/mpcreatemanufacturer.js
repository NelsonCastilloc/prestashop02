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

$(document).ready(function() {
    $(document).on('click', '#submit_manufacturer', function() {
        setTimeout(() => {
            $(this).attr('disabled','disabled')
            
        }, 100);
    })

    $(document).on('click', '#submitStay_manufacturer', function() {
        setTimeout(() => {
            $(this).attr('disabled','disabled')
            
        }, 100);
    })


    var countryid = $("#manufcountry").val();
    getAndSetStateByCountryId(countryid);

    $(document).on('change', '#manufcountry', function() {
        var countryid = $("#manufcountry").val();
        getAndSetStateByCountryId(countryid);
    });

    //display key with tagify
    if (typeof allow_tagify != 'undefined') {
        $.each(languages, function(key, value) {
            $('#meta_key_'+value.id_lang).tagify({
                delimiters: [13,44],
                addTagPrompt: addkeywords
            });
        });
    }

    $('#submit_manufacturer, #submitStay_manufacturer').on("click", function(e) {
        var manuf_name = $('#manuf_name').val().trim();
        var manuf_address = $('#manuf_address').val().trim();
        var manuf_city = $('#manuf_city').val().trim();
        var manuf_country = $('#manuf_country').val();
        var manuf_logo = $('#manuf_logo').val();
        let manuf_zip = $('#manuf_zipcode').val().trim();
        let manu_city_valid = /^[^!<>;?=+@#"°{}_$%]*$/u
        let zip_code_valid = /^[NLCnlc 0-9-]+$/
        let address_valid = /^[^!<>?=+@{}_$%]*$/u
        let valid_tag = /^[^<>={}]*$/u


        if (manuf_name == '') {
            showErrorMessage(req_manuf_name);
            $('#manuf_name').focus();
            return false;
        } else if (manuf_logo != '') {
            var logo_arry = manuf_logo.split('.');
            var ext = logo_arry.pop();
            ext_array = new Array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF');
            if ($.inArray(ext, ext_array) == -1) {
                showErrorMessage(invalid_logo);
                return false;
            }
        } else if (manuf_address == '') {
            showErrorMessage(req_manuf_address);
            $('#manuf_address').focus();
            return false;
        } else if (manuf_address.length > 127) {
            showErrorMessage(length_exceeds_address);
            $('#manuf_address').focus();
            return false;
        } else if (!address_valid.test(manuf_address)) {
            showErrorMessage(invalid_address)
            $('#manuf_address').focus();
            return false
        } else if (manuf_city == '') {
            showErrorMessage(req_manuf_city);
            $('#manuf_city').focus();
            return false;
        } else if (!manu_city_valid.test(manuf_city)) {
            showErrorMessage(invalid_city)
            $('#manuf_city').focus();
            return false
        } else if (!zip_code_valid.test(manuf_zip)) {
            if (manuf_zip != '') {
                showErrorMessage(invalid_zipcode)
                $('#manuf_zipcode').focus();
                return false
            }
        } else if (manuf_country == '') {
            showErrorMessage(req_manuf_country);
            return false;
        }



        $.each(languages, function(key, value) {
            $.each($(`#meta_key_div_${value.id_lang} .tagify-container span`), function(key, value) {
                if( $(value).css('display') != 'none' )  {
                    if (!valid_tag.test($(value).text())) {
                        showErrorMessage(invalid_tag)
                        return false
                    }
                }
            })
            $('#meta_key_'+value.id_lang).tagify('serialize');
        });



        $('.wk_product_loader').show();
        $('#submit_manufacturer, #submitStay_manufacturer').addClass('wk_mp_disabled');
    });
});

function getAndSetStateByCountryId(countryid) {
    var suppstate_temp = $('#suppstate_temp').val();
    $('#ajax_loader').css('display', 'block');
    $('#dni_required').hide();

    $.ajax({
        url: manuf_ajax_link,
        type: 'POST',
        data: {
            fun: 'get_state',
            countryid: countryid,
            action: 'getStateByCountry',
            ajax:true
        },
        dataType: 'json',
        success: function(data) {
            if (data.status == 'success') {
                $('#manufstate').empty();
                var i = 0;
                $.each(data.info, function(index, value) {
                    if (suppstate_temp == value.id_state) {
                        $('#uniform-manufstate').children('span').text(value.name);
                        $('#manufstate').append("<option value='" + value.id_state + "' selected='selected'>" + value.name + "</option>");
                    } else {
                        if (i == 0) {
                            $('#uniform-manufstate').children('span').text(value.name);
                            $('#manufstate').append("<option value='" + value.id_state + "' selected='selected'>" + value.name + "</option>");
                        } else
                            $('#manufstate').append("<option value='" + value.id_state + "' >" + value.name + "</option>");
                    }
                    i += 1;
                });
                $('.divsuppstate').show();
            } else {
                $('#manufstate').empty();
                $('.divsuppstate').hide();
            }
            //$('#manufstate').uniform();
            $('#ajax_loader').css('display', 'none');
        },
        fail: function() {
            $('#manufstate').empty();
            $('.divsuppstate').hide();
            //$('#manufstate').uniform();
            $('#ajax_loader').css('display', 'none');
        }
    });
    dniRequired(countryid);
}

function dniRequired(id_country) {
    $.ajax({
        url: manuf_ajax_link,
        data: {
            ajax: "1",
            id_country: id_country,
            token: $('#wk-static-token').val(),
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

function showManufLangField(lang_iso_code, id_lang) {
    $('#manufacturers_lang_btn').html(lang_iso_code + ' <span class="caret"></span>');

    $('.short_desc_div_all').hide();
    $('#short_desc_div_' + id_lang).show();

    $('.desc_div_all').hide();
    $('#desc_div_' + id_lang).show();

    $('.meta_title_div_all').hide();
    $('#meta_title_div_' + id_lang).show();

    $('.meta_desc_div_all').hide();
    $('#meta_desc_div_' + id_lang).show();

    $('.meta_key_div_all').hide();
    $('#meta_key_div_' + id_lang).show();

    $('.all_lang_icon').attr('src', img_dir_l + id_lang + '.jpg');
    $('#choosedLangId').val(id_lang);
}

function showSuccessMessage(msg) {
    $.growl.notice({ title: "", message: msg });
}

function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}