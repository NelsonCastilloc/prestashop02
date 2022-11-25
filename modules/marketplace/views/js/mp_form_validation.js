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
    $('.wk_related_product_loader').hide();
    $('#mp_attachment_product').hide();

    //Tab active code
    if ($('#active_tab').val() != '') {
        var active_tab = $('#active_tab').val();
        changeTabStatus(active_tab);
    }

    $('#SubmitProduct, #StayProduct, #mp_admin_saveas_button, #mp_admin_save_button').removeClass('wk_mp_disabled');
    //Seller registration form validation
    $('#sellerRequest,#updateProfile,#mp_seller_save_button,#mp_seller_saveas_button').on("click", function() {
        window.wkerror = false;

        getActiveTabAfterSubmitForm();

        if (typeof multi_lang !== 'undefined' && multi_lang == '1') {
            var default_lang = $('.seller_default_shop').data('lang-name');
            var shop_name = $('.seller_default_shop').val().trim();
            if (shop_name == '') {
                $('#wk_mp_form_error').text(req_shop_name_lang + ' ' + default_lang).show('slow');
                $('html,body').animate({
                    scrollTop: $("#wk_mp_form_error").offset().top - 10
                }, 'slow');
                return false;
            }
        }

        // validating all seller form field
        $('.wk_product_loader').show();
        $('#sellerRequest,#updateProfile,#mp_seller_save_button,#mp_seller_saveas_button').addClass('wk_mp_disabled');
        $.ajax({
            url: path_sellerdetails,
            cache: false,
            type: 'POST',
            async: false,
            dataType: "json",
            data: {
                ajax: true,
                action: 'validateMpSellerForm',
                formData: $("form").serialize(),
                token: $('#wk-static-token').val(),
                mp_seller_id: $('#mp_seller_id').val(),
            },
            success: function(result) {
                clearFormField();
                $('.wk_product_loader').hide();
                if (result.status == 'ko') {
                    $('#wk_mp_form_error').text(result.msg).show('slow');
                    changeTabStatus(result.tab);

                    $('html,body').animate({
                        scrollTop: $("#wk_mp_form_error").offset().top - 10
                    }, 'slow');

                    if (result.multilang == 1) {
                        $('.' + result.inputName).addClass('border_warning');
                    } else {
                        $('input[name="' + result.inputName + '"]').addClass('border_warning');
                    }
                    window.wkerror = true;
                }
            }
        });

        if (window.wkerror) {
            $('#sellerRequest,#updateProfile,#mp_seller_save_button,#mp_seller_saveas_button').removeClass('wk_mp_disabled');
            return false;
        }
        // end of ajax code
    });

    //Add product and update product form validation
    $('#wk_mp_seller_product_form,#mp_admin_save_button,#mp_admin_saveas_button').on("submit", function(e) {
        window.wkerror = false;

        getActiveTabAfterSubmitForm();

        //get all checked category value in a input hidden type name 'product_category'
        var rawCheckedID = [];
        $('#categorycontainer .jstree-clicked').each(function() {
            var rawIsChecked = $(this).parent('.jstree-node').attr('id');
            rawCheckedID.push(rawIsChecked);
        });

        $('#product_category').val(rawCheckedID.join(","));

        var checkbox_length = $('#product_category').val();
        if (checkbox_length == 0) {
            showErrorMessage(req_catg);
            return false;
        }

        // validate seller product form
        $('.wk_product_loader').show();
        $('#SubmitProduct, #StayProduct, button#mp_admin_saveas_button, button#mp_admin_save_button').addClass('wk_mp_disabled');
        $.ajax({
            url: path_addfeature,
            cache: false,
            type: 'POST',
            async: false,
            dataType: "json",
            data: {
                ajax: true,
                action: 'validateMpForm',
                formData: $("form").serialize(),
                token: $('#wk-static-token').val(),
                id_mp_product: $('#mp_product_id').val(),
            },
            success: function(result) {
                $('.wk_product_loader').hide();
                clearFormField();
                if (result.status == 'ko') {
                    $('#wk_mp_form_error').text(result.msg).show('slow');
                    changeTabStatus(result.tab);

                    $('html,body').animate({
                        scrollTop: $("#wk_mp_form_error").offset().top - 10
                    }, 'slow');

                    if (result.multilang == 1) {
                        $('.' + result.inputName).addClass('border_warning');
                    } else {
                        $('input[name="' + result.inputName + '"]').addClass('border_warning');
                    }
                    window.wkerror = true;
                    $("html, body").animate({ scrollTop: 300 }, "slow");
                }
            }
        });

        if (window.wkerror) {
            $('#SubmitProduct, #StayProduct, button#mp_admin_saveas_button, button#mp_admin_save_button').removeClass('wk_mp_disabled');
            return false;
        }

        // return false;
    });

    if ($("#updateProfile").length) {
        id_seller = $("#id_seller").val();
    }

    // payment details link
    $('#submit_payment_details').click(function() {
        var payment_mode = $('#payment_mode').val();
        if (payment_mode == "") {
            alert(req_payment_mode)
            $('#payment_mode').focus();
            return false;
        }
    });

    // code only for page where category tree is using
    if ($('#wk_mp_category_tree').length) {
        //for category tree
        $('#wk_mp_category_tree').checkboxTree({
            initializeChecked: 'expanded',
            initializeUnchecked: 'collapsed'
        });
    }

    //Only for update seller profile page
    if (typeof actionpage != 'undefined' && actionpage == 'seller') {
        $(document).on("click", ".wk_delete_img", function(e) {
            e.preventDefault();
            var img_uploaded = $(this).data("uploaded");
            if (img_uploaded) { //if image already upload for profile or shop
                if (confirm(confirm_delete_msg)) {
                    deleteSellerImages($(this));
                }
            } else {
                deleteSellerImages($(this));
            }
            return false;
        });
    }

    //When seller deactivate their shop then first confirm
    $(document).on("click", ".wk_shop_deactivate", function() {
        if (confirm(confirm_deactivate_msg)) {
            return true;
        }

        return false;
    });

    //Delete seller payment details then first confirm
    $(".delete_mp_data").on("click", function() {
        if (confirm(confirm_msg)) {
            return true;
        } else {
            return false;
        }
    });

    // check all check box when seller want to check all permission
    $(document).on('change', '#wk_select_all_checkbox', function() {
        $('input[name="seller_details_access[]"]').prop('checked', $(this).prop("checked"));
    });

    //" uncheck checkbox if any other checkbox get uncheck
    $('input[name="seller_details_access[]"]').change(function() {
        if (false == $(this).prop("checked")) {
            $("#wk_select_all_checkbox").prop('checked', false);
        }
    });

    //Product Visibility
    if ($('#available_for_order').is(':checked')) {
        $('#show_price').parent().parent().removeClass('checker');
        $('#show_price').prop('disabled', true);
        $('#show_price').prop('checked', true);
    }

    $('#available_for_order').click(function() {
        //check if checkbox is checked
        if ($('#available_for_order').is(':checked')) {
            $('#show_price').parent().parent().removeClass('checker');
            $('#show_price').prop('disabled', true);
            $('#show_price').prop('checked', true);
        } else {
            $('#show_price').removeAttr('disabled'); //disable
        }
    });

    $(document).on('keyup', '#relatedproductsearch', function() {
        if ($(this).val() != '') {
            $('.wk_related_product_loader').show();
        }

        if ($(this).attr('data-value') != '') {
            $(this).attr('data-value', '');
        }

        if ($(this).attr('data-img') != '') {
            $(this).attr('data-img', '');
        }

        var relatedprod_ul = $('#relatedprod_ul');
        relatedprod_ul.html('').hide();

        var prod = $.trim($(this).val());
        if (prod) {
            var current_lang_id = $('#current_lang_id').val();
            // add prod from admin
            if (typeof id_seller == 'undefined' || id_seller == null) {
                var seller_cust_id = $("select[name='shop_customer']").val();
                ajax_data = {
                    prod_letter: prod,
                    current_lang_id: current_lang_id,
                    seller_cust_id: seller_cust_id,
                    // module_token: module_secure_key,
                    id_mp_product: $('#mp_product_id').val(),
                };
            } else {
                ajax_data = {
                    prod_letter: prod,
                    seller_id: id_seller,
                    current_lang_id: current_lang_id,
                    module_token: module_secure_key,
                    id_mp_product: $('#mp_product_id').val(),
                };
            }
            var prev_id_prod = [];
            $('.mppk_id_prod').each(function(key, value) {
                prev_id_prod[key] = value.value;
            });

            if (typeof id_mp_related_product != 'undefined')
                prev_id_prod[prev_id_prod.length] = id_mp_related_product;

            if (prev_id_prod.length) {
                prev_id_prod = JSON.stringify(prev_id_prod);
                ajax_data.prev_id = prev_id_prod;
            }
            ajax_data.action = 'MpSearchProductOnly';
            $.ajax({
                url: mp_related_ajax,
                type: 'POST',
                dataType: 'json',
                data: ajax_data,
                success: function(result) {
                    var excludeIds = getSelectedRelatedIds();
                    var returnIds = new Array();
                    relatedprod_ul.show();
                    if (result && result.length !== 0) {
                        for (var i = result.length - 1; i >= 0; i--) {
                            var is_in = 0;
                            for (var j = 0; j < excludeIds.length; j++) {
                                if (result[i].id == excludeIds[j]) {
                                    is_in = 1;
                                }
                            }
                            if (!is_in) {
                                returnIds.push(result[i]);
                            }
                        }
                    }
                    if (returnIds && returnIds.length !== 0) {
                        var html = '';
                        $.each(returnIds, function(key, value) {
                            if (typeof value.id_product_attribute == 'undefined') {
                                value.id_product_attribute = 0;
                            }
                            html += "<li class='relatedprod_li' data-id_ps_product='" + value.id + "' data-img='" + value.image + "' data-id_ps_product_attr='" + value.id_product_attribute + "'>";
                            html += "<div style='float:left;margin-right:5px;'><img src=" + value.image + " width='40' /></div>"
                            html += "<div style='float:left;'><h4 class='li_prod_name'>" + value.name + "</h4>";
                            if (value.ref != '') {
                                html += "<span class='li_prod_ref'> REF: " + value.ref + " </span></div>";
                            }
                            html += "</li>";
                        });
                        relatedprod_ul.html(html);
                    } else {
                        var html;
                        html = "<li>";
                        html += "<div>"+ noMatchesFound +"</div>"
                        html += "</li>";
                        relatedprod_ul.html(html);
                    }
                    $('.wk_related_product_loader').hide();
                }
            });
        }
    });


    function getSelectedRelatedIds() {
        var returnIds = new Array();
        $.each($('[name="related_product[]"]'), function(key, value) {
            returnIds.push($(this).val());
        });
        return returnIds;
    }

    $('body').on('click', '.relatedprod_li', function() {
        var isExist = false;
        var id_product = $(this).data('id_ps_product');
        $('[name="related_product[]"]').each(function(){
            if ($(this).val() == id_product) {
                $('#relatedprod_ul').html('').hide();
                isExist = true;
            }
        });
        if (isExist == false) {
            var hiddeninput = `<input type='hidden' value='`+id_product+`' name='related_product[]'>`
            var html = `<div class="alert wk-selected-products alert-dismissible" role="alert">
                <span><img src=`+$(this).data('img')+` height='50' width='50'>
                `+hiddeninput+ $(this).find('.li_prod_name').text()+`</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>`;
            $('#selected_related_product').append(html);
            $('#relatedprod_ul').html('').hide();
        }
    });
    if (typeof back_end !== 'undefined' && typeof wk_rtype !== 'undefined') {
        if (back_end == 1) {
            showRedirectDropdown(wk_rtype);
        }
    }
});

$(document).ready(function() {
    // get feature values when seller change the feature
    $(document).on('change', '.wk_mp_feature', function() {
        var idFeature = $(this).val();
        var dataIdFeature = $(this).attr('data-id-feature');
        if (idFeature > 0) {
            $.ajax({
                url: path_addfeature,
                cache: false,
                type: 'POST',
                data: {
                    id_mp_product: $('#mp_product_id').val(),
                    token: $('#wk-static-token').val(),
                    ajax: true,
                    idFeature: idFeature,
                    action: "getFeatureValue"
                },
                success: function(result) {
                    $("select[data-id-feature-val=" + dataIdFeature + "]").empty();
                    if (result) {
                        data = JSON.parse(result);
                        $("select[data-id-feature-val=" + dataIdFeature + "]").removeAttr('disabled');
                        $('.custom_value_' + dataIdFeature).prop('value', '');
                        $("select[data-id-feature-val=" + dataIdFeature + "]").append('<option value="0">' + choose_value + '</option>');
                        $.each(data, function(i, item) {
                            $("select[data-id-feature-val=" + dataIdFeature + "]").append('<option value="' + item.id_feature_value + '">' + item.value + '</option>');
                        });
                    } else {
                        $("select[data-id-feature-val=" + dataIdFeature + "]").append('<option value="0">' + no_value + '</option>');
                    }
                }
            });
        } else {
            $("select[data-id-feature-val=" + dataIdFeature + "]").empty();
        }
    });

    //When admin assigned product to seller
    $(document).on('click', '.wk-prod-assign', function() {
        if ($('select[name="id_product[]"] option:selected').val()) {
            if (confirm(confirm_assign_msg)) {
                $('.wk-prod-assign').addClass('wk_mp_disabled');
                return true;
            }
        } else {
            alert(choose_one);
        }

        return false;
    });

    $("#available_date").datepicker({
        dateFormat: "yy-mm-dd",
    });

    // add more feature list
    $(document).on('click', '#add_feature_button', function() {
        var fieldrow = parseInt($('#wk_feature_row').val());
        var idSeller = seller_default_lang = false;
        idSeller = $('select[name="shop_customer"] option:selected').val();
        sellerDefaultLang = $('#seller_default_lang').val();
        choosedLangId = $('#choosedLangId').val();
        $('.wk-feature-loader').css('display', 'inline-block');
        $('#add_feature_button').attr('disabled', 'disabled');

        $.ajax({
            url: path_addfeature,
            cache: false,
            type: 'POST',
            data: {
                id_mp_product: $('#mp_product_id').val(),
                token: $('#wk-static-token').val(),
                ajax: true,
                fieldrow: fieldrow,
                idSeller: idSeller,
                action: "addMoreFeature",
                sellerDefaultLang: sellerDefaultLang,
                choosedLangId: choosedLangId,
            },
            success: function(result) {
                $('.wk-feature-loader').hide();
                $('#add_feature_button').removeAttr('disabled');
                if (result) {
                    $('#features-content').last().append(result);
                    $('#wk_feature_row').val(fieldrow + 1);
                }
            }
        });
    });

    // delete feature list
    $(document).on('click', '.wkmp_feature_delete', function() {
        $(this).closest('.wk_mp_feature_delete_row').parent().fadeOut(500, function() {
            $(this).remove();
        });
    });

    //Display cms page in modal box
    $('.wk_terms_link').on('click', function() {
        var linkCmsPageContent = $(this).attr('href');
        $('#wk_terms_condtion_content').load(linkCmsPageContent, function() {
            //remove extra content
            $('#wk_terms_condtion_content section#wrapper').css({ "background-color": "#fff", "padding": "0px", "box-shadow": "0px 0px 0px #fff" });
            $('#wk_terms_condtion_content .breadcrumb').remove();
            $('#wk_terms_condtion_content header').remove();
            //display content
            $('#wk_terms_condtion_div').modal('show');
        });
        return false;
    });
});

/*------------------------------  Mandatory Checked Functions  --------------------------*/

var i = 2;
var id_seller;
var shop_name_exist = false;
var seller_email_exist = false;

//Check Seller unique shop name validation
function onblurCheckUniqueshop() {
    var shop_name_unique = $('#shop_name_unique').val().trim();
    id_seller = $("#mp_seller_id").val();
    if (checkUniqueShopName(shop_name_unique, id_seller)) {
        $('#shop_name_unique').focus();
        return false;
    }
}

function getActiveTabAfterSubmitForm() {
    //put active tab in input hidden type
    if (typeof backend_controller !== 'undefined') { //for admin
        var active_tab_id = $('.wk-tabs-panel .nav-tabs li.active a').attr('href');
    } else {
        var active_tab_id = $('.wk-tabs-panel .nav-tabs li a.active').attr('href');
    }

    if (typeof active_tab_id !== 'undefined') {
        var active_tab_name = active_tab_id.substring(1, active_tab_id.length);
        $('#active_tab').val(active_tab_name);
    }
}

function changeTabStatus(active_tab) {
    //Remove all tabs from active (make normal)
    $('.wk-tabs-panel .tab-content .tab-pane').removeClass('active');
    //$('.wk-tabs-panel .tab-content .tab-pane').removeClass('show');
    if (typeof backend_controller !== 'undefined') { //for admin
        $('.wk-tabs-panel .nav-tabs li').removeClass('active');
        //$('.wk-tabs-panel .nav-tabs li').removeClass('show');
        $('[href*="#' + active_tab + '"]').parent('li').addClass('active');
        //$('[href*="#' + active_tab + '"]').parent('li').addClass('show');
    } else {
        $('.wk-tabs-panel .nav-tabs li.nav-item a').removeClass('active');
        //$('.wk-tabs-panel .nav-tabs li.nav-item a').removeClass('show');
        $('[href*="#' + active_tab + '"]').addClass('active');
        //$('[href*="#' + active_tab + '"]').addClass('show');
    }
    //Add active class in selected tab
    $('#' + active_tab).addClass('active in');
}

function checkUniqueShopName(shop_name_unique, id_seller) {
    if (shop_name_unique != "") {
        $('.seller-loading-img').css('display', 'inline-block');
        $('.wk-mp-block').css({'pointer-events': 'none'});
        $.ajax({
            url: path_sellerdetails,
            type: "POST",
            data: {
                ajax: true,
                action: "checkUniqueShopName",
                shop_name: shop_name_unique,
                token: $('#wk-static-token').val(),
                id_seller: id_seller !== 'undefined' ? id_seller : false,
            },
            success: function(result) {
                $('.wk-mp-block').css({'pointer-events': 'inherit'});
                $('.seller-loading-img').css('display', 'none');
                if (result == 1) {
                    $(".wk-msg-shopnameunique").html(shop_name_exist_msg);
                    shop_name_exist = true;
                } else if (result == 2) {
                    $(".wk-msg-shopnameunique").html(shop_name_error_msg);
                    $(".seller_shop_name_uniq").addClass('form-error').removeClass('form-ok');
                    shop_name_exist = true;
                } else {
                    $(".wk-msg-shopnameunique").empty();
                    shop_name_exist = false;
                }
            }
        });
    } else {
        $(".wk-msg-shopnameunique").empty();
        shop_name_exist = false;
    }

    return shop_name_exist;
}

//Check Seller registration unique email validation
function onblurCheckUniqueSellerEmail() {
    var business_email = $('#business_email').val().trim();
    id_seller = $("#mp_seller_id").val();
    if (checkUniqueSellerEmail(business_email, id_seller)) {
        $('#business_email').focus();
        return false;
    }
}

function checkUniqueSellerEmail(business_email, id_seller) {
    if (business_email != "") {
        $.ajax({
            url: path_sellerdetails,
            type: "POST",
            data: {
                ajax: true,
                action: "checkUniqueSellerEmail",
                token: $('#wk-static-token').val(),
                seller_email: business_email,
                id_seller: id_seller !== 'undefined' ? id_seller : false
            },
            async: false,
            success: function(result) {
                if (result == 1) {
                    $(".wk-msg-selleremail").html(seller_email_exist_msg);
                    seller_email_exist = true;
                } else {
                    $(".wk-msg-selleremail").empty();
                    seller_email_exist = false;
                }
            }
        });
    } else {
        $(".wk-msg-selleremail").empty();
        seller_email_exist = false;
    }

    return seller_email_exist;
}

//On delete seller profile image and shop image by seller or admin
function deleteSellerImages(t) {
    var id_seller = t.data("id_seller");
    var target = t.data("imgtype");

    if (id_seller != '' && target != '') {
        $.ajax({
            url: path_sellerdetails,
            type: 'POST',
            dataType: 'json',
            async: false,
            data: {
                id_seller: id_seller,
                token: $('#wk-static-token').val(),
                delete_img: target,
                ajax: true,
                action: "deleteSellerImage"
            },
            success: function(result) {

                if (result.status == 'ok') {
                    $('.jFiler-item-others').remove();
                    var target_container = $('.jFiler-items-' + target);
                    target_container.show();

                    if (target == 'seller_img') {
                        target_container.find('.jFiler-item-inner img').attr("src", seller_default_img_path);
                    } else if (target == 'shop_img') {
                        target_container.find('.jFiler-item-inner img').attr("src", shop_default_img_path);
                    } else {
                        target_container.find('.jFiler-item-inner img').attr("src", no_image_path);
                    }

                    target_container.find('.wk_delete_img').remove();
                    t.parent().removeClass('wk_hover_img');
                    t.remove();
                }
            }
        });
    }
}
$(document).on("click", ".add_customization_field", function() {
    var fieldrow = parseInt($('#custom_field_count').val())+1;
    choosedLangId = $('#choosedLangId').val();
    var inputHtml = '';
    languages.forEach(lang => {
        if (choosedLangId == lang.id_lang) {
            inputHtml += `<div class="wk_text_field_all wk_text_field_`+lang.id_lang+`">
            <input type="text" name="custom_fields[`+fieldrow+`][label][`+lang.id_lang+`]" id="custom_fields_`+fieldrow+`_label_`+lang.id_lang+`"
            class="form-control" placeholder="`+fieldlabel+`"></div>`;
        }else {
            inputHtml += `<div class="wk_text_field_all wk_text_field_`+lang.id_lang+`" style='display:none'>
            <input type="text" name="custom_fields[`+fieldrow+`][label][`+lang.id_lang+`]" id="custom_fields_`+fieldrow+`_label_`+lang.id_lang+`"
            class="form-control" placeholder="`+fieldlabel+`"></div>`;
        }
    });
    html = `<div class="form-group row">
        <div class="col-md-4">
            <label class="control-label">Label <img class="all_lang_icon" src="`+img_dir_l+choosedLangId+`.jpg"></label>
            <input type="hidden" name="custom_fields[`+fieldrow+`][id_customization_field]" id="custom_fields_`+fieldrow+`_id_customization_field">
            <div class="">
                <div class="">`+inputHtml+`</div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="col-md-12">
                <label class="control-label">`+wk_ctype+`</label>
            </div>
            <div class="col-md-5">
                <select id="custom_fields_2_type" name="custom_fields[`+fieldrow+`][type]" class="form-control form-control-select">
                    <option value="1">`+custimzationtext+`</option>
                    <option value="0">`+custimzationfile+`</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="javascript:;" class="btn delete_customization"><i class="material-icons">delete</i></a>
            </div>
            <div class="col-md-5">
                <div class="checkbox">
                    <label for="required">
                        <input type="checkbox" id="custom_fields_2_required" value="1" name="custom_fields[`+fieldrow+`][required]">
                        `+wk_crequired+`</label>
                </div>
            </div>
        </div>
    </div>`;
    $('.add_customization_field').before(html);
    $('#custom_field_count').val(fieldrow);
});
$(document).on("click", ".delete_customization", function() {
    if (confirm(confirm_delete_customization)) {
        $(this).parent().closest('.form-group').remove();
    }
});

$(document).on("click", ".add_mp_attachemnts", function() {
    $('#mp_attachment_product').toggle(400);
});

$(document).on("click", "#attachment_product_cancel", function() {
    $('#mp_attachment_product').toggle(400);
    $('#mp_attachment').val('');
    $('#attachment_product_name').val('');
    $('#attachment_product_description').val('');
    $('.mp_attachment_name').text('');    
});

$(document).on("click", "#attachment_product_add", function(e) {
    e.preventDefault();
    if ($('#mp_attachment').val() == '') {
        showProductErrors(attachment_file_error);
        return false;
    }
    if ($('#attachment_product_name').val() == '') {
        showProductErrors(attachment_name_error);
        return false;
    } else if ($('#attachment_product_name').val().length < 3) {
        showProductErrors(attachment_name_length_error);
        return false;
    } else if ($('#attachment_product_name').val().length > 32) {
        showProductErrors(attachment_name_maxlength_error);
        return false;
    }
    var formData = new FormData();
    formData.append('product_attachment', $('#mp_attachment')[0].files[0]);
    formData.append('attachment_product_name', $('#attachment_product_name').val());
    formData.append('attachment_product_description', $('#attachment_product_description').val());
    formData.append('id_seller', id_seller);
    formData.append('ajax', true);
    formData.append('action', 'addProductAttachment');
    if (typeof path_addproduct == "undefined") {
        path_addproduct = path_sellerproduct;
    } else {
        formData.append('token', $('#wk-static-token').val());
    }
    $.ajax({
        type:'POST',
        url: path_addproduct,
        data:formData,
        cache:false,
        contentType: false,
        processData: false,
        dataType: "json",
        success:function(data){
            if (data['id_attachment'] > 0) {
                htmltr = `<tr>
                    <td class="col-md-1">
                        <input type="checkbox" class="checkbox" id="attachments_`+data['id_attachment']+`" name="mp_attachments[]" value="`+data['id_attachment']+`">
                    </td>
                    <td class="col-md-3">
                        <span class="">`+data['attachment_name']+`</span>
                    </td>
                    <td class="col-md-6 file-name"><span>`+data['file_name']+`</span></td>
                    <td class="col-md-2 wk-attachment-file-type">`+data['mime']+`</td>
                </tr>`;

                $('#product-attachment-file tbody').append(htmltr);
                $('#mp_attachment').val('');
                $('#attachment_product_name').val('');
                $('#attachment_product_description').val('');
                $(".mp_attachment_name").text('');
                showSuccessMessage(attachment_success);
            } else {
                showProductErrors(attachment_error);
            }
        }
    });
});

$(document).on("click", ".mp_attachment_btnr", function(e) {
    e.preventDefault();
    $('#mp_attachment').trigger('click');
});

$(document).on("change", "#mp_attachment", function() {
    var file_name = $(this).val().split('/').pop().split('\\').pop();
    if (file_name !== '') {
        if ((Math.round(this.files[0].size / 1024 / 1024) * 100) / 100 > allowed_file_size) {
            showProductErrors(allowed_file_size_error);
            $(this).val('');
        } else {
            $("#mp_attachment_name").val(file_name);
            $(".mp_attachment_name").text(file_name);
        }
    }
});

// seller Page categoies settings at BO
$(document).on("change", "#plan-categories [type='checkbox']", function() {
    var $this = $(this);
    var checkboxes = $this.parents().children('span').find('[type="checkbox"]');
    if($this.is(":checked")) {
        return checkboxes.prop("checked", true);
    } else {
        $this.parent().parent().find('[type="checkbox"]').prop("checked", false);
    }
});

$(document).on("change", "#redirect_type", function() {
    $('#id_type_redirected').val(0);
    if ($(this).val() == '301-category' || $(this).val() == '302-category') {
        $('.target_product_div').addClass('wk_display_none');
        $('.target_category_div').removeClass('wk_display_none');
    } else if ($(this).val() == '301-product' || $(this).val() == '302-product') {
        $('.target_category_div').addClass('wk_display_none');
        $('.target_product_div').removeClass('wk_display_none');
    } else {
        $('.target_product_div').addClass('wk_display_none');
        $('.target_category_div').addClass('wk_display_none');
    }
    if (typeof back_end !== 'undefined') {
        if (back_end == 1) {
            showRedirectDropdown($(this).val());
        }
    }
});

$(document).on("change", "#target_category, #target_product", function() {
    $('#id_type_redirected').val($(this).val());
});

function showRedirectDropdown(redirectType) {
    // add prod from admin
    var current_lang_id = $('#current_lang_id').val();
    ajax_data = {
        redirectType: redirectType,
        current_lang_id: current_lang_id,
        id_mp_product: $('#mp_product_id').val(),
        action: 'getRedirectionType',
    };
    if (typeof id_seller == 'undefined' || id_seller == null) {
        var seller_cust_id = $("select[name='shop_customer']").val();
        ajax_data.seller_cust_id = seller_cust_id;
    } else {
        ajax_data.id_seller = id_seller;
    }
    $.ajax({
        url: path_sellerproduct,
        cache: false,
        type: 'POST',
        async: false,
        dataType: "json",
        data: ajax_data,
        success: function(result) {
            if (result == 'ko') {
                showProductErrors(SomethingWentWrong)
            } else {
                var options = ``;
                if (redirectType == '301-category' || redirectType == '302-category') {
                    result.forEach(element => {
                        if (wk_rtypeId == element.id_category) {
                            options += `<option id="target_category`+element.id_category+`" value="`+element.id_category+`" selected>`+element.name+`</option>`;
                        } else {
                            options += `<option id="target_category`+element.id_category+`" value="`+element.id_category+`">`+element.name+`</option>`;
                        }
                    });
                    $('#target_category').html(options);
                    $('#target_category').chosen().trigger('chosen:updated');
                } else if (redirectType == '301-product' || redirectType == '302-product') {
                    result.forEach(element => {
                        if (wk_rtypeId == element.id_product) {
                            options += `<option id="target_product`+element.id_product+`" value="`+element.id_product+`" selected>`+element.name+`</option>`;
                        } else {
                            options += `<option id="target_product`+element.id_product+`" value="`+element.id_product+`">`+element.name+`</option>`;
                        }
                    });
                    $('#target_product').html(options);
                    $('#target_product').chosen().trigger('chosen:updated');
                }
            }
        }
    });
}

function clearFormField() {
    $(":input").removeClass('border_warning');
}
function showSuccessMessage(msg) {
    $.growl.notice({ title: "", message: msg });
}
function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}

function showProductErrors(msg, inputName = false) {
	$('#wk_mp_form_error').text(msg).show('slow');
	$('html,body').animate({
		scrollTop: $("#wk_mp_form_error").offset().top - 10
	}, 'slow');
	$('input[name="' + inputName + '"]').addClass('border_warning');
	$("html, body").animate({ scrollTop: 300 }, "slow");
}