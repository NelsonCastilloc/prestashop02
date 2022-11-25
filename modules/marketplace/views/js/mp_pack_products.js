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
    var sug_ajax = '';
    $('.wk_pack_product_loader').hide();
    if ($('.product_type:checked').val() == 3) {
        $('.vir_container').show();
        $('.pkprod_container').hide();
        $('a[href="#wk-combination"]').hide();
        $('a[href="#wk-product-shipping"]').hide();
    } else if ($('.product_type:checked').val() == 2) {
        $('.pkprod_container').show();
        $('.vir_container').hide();
        $('a[href="#wk-combination"]').hide();
    } else if ($('.product_type:checked').val() == 1) {
        $('.vir_container').hide();
        $('.pkprod_container').hide();
        $('a[href="#wk-combination"]').show();
        $('a[href="#wk-product-shipping"]').show();
    }

    $('.product_type').on('click', function() {
        if ($(this).val() == 3) {
            $('.vir_container').show();
            $('.pkprod_container').hide();
            $('a[href="#wk-combination"]').hide();
            $('a[href="#wk-product-shipping"]').hide();
        } else if ($(this).val() == 2) {
            $('.pkprod_container').show();
            $('.vir_container').hide();
            $('a[href="#wk-combination"]').hide();
        } else if ($(this).val() == 1) {
            $('.vir_container').hide();
            $('.pkprod_container').hide();
            $('a[href="#wk-combination"]').show();
            $('a[href="#wk-product-shipping"]').show();
        }
    });

    $('body').on('click', function(event) {
        if ($('#sugpkprod_ul').css('display') == 'block') {
            $('#sugpkprod_ul').html('').hide();
        }
    });

    $(document).on('keyup', '#selectproduct', function() {
        if ($(this).val() != '') {
            $('.wk_pack_product_loader').show();
        }

        $('#selectproduct').removeClass('wkPkSelectedProduct');
        $('#selectproduct').addClass('form-control');

        if ($(this).attr('data-value') != '') {
            $(this).attr('data-value', '');
        }

        if ($(this).attr('data-img') != '') {
            $(this).attr('data-img', '');
        }

        if (sug_ajax) {
            sug_ajax.abort();
        }

        var sugprod_ul = $('#sugpkprod_ul');
        sugprod_ul.html('').hide();

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
                    module_token: module_secure_key,
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

            if (typeof id_mp_pack_product != 'undefined')
                prev_id_prod[prev_id_prod.length] = id_mp_pack_product;

            if (prev_id_prod.length) {
                prev_id_prod = JSON.stringify(prev_id_prod);
                ajax_data.prev_id = prev_id_prod;
            }
            ajax_data.action = 'MpSearchProduct';
            sug_ajax = $.ajax({
                url: mp_pack_ajax,
                type: 'POST',
                dataType: 'json',
                data: ajax_data,
                success: function(result) {
                    var excludeIds = getSelectedIds();
                    var returnIds = new Array();
                    sugprod_ul.show();
                    if (result && result.length !== 0) {
                        for (var i = result.length - 1; i >= 0; i--) {
                            var is_in = 0;
                            for (var j = 0; j < excludeIds.length; j++) {
                                if (result[i].id == excludeIds[j][0] && (typeof result[i].id_product_attribute == 'undefined' || result[i].id_product_attribute == excludeIds[j][1])) {
                                    is_in = 1;
                                }
                            }
                            if (!is_in) {
                                returnIds.push(result[i]);
                            }
                        }
                    }
                    if (returnIds && returnIds.length !== 0) {
                        var html;
                        $.each(returnIds, function(key, value) {
                            if (typeof value.id_product_attribute == 'undefined') {
                                value.id_product_attribute = 0;
                            }

                            html = "<li class='sugpkprod_li' data-id_ps_product='" + value.id + "' data-img='" + value.image + "' data-id_ps_product_attr='" + value.id_product_attribute + "'>";
                            html += "<div style='float:left;margin-right:5px;'><img src=" + value.image + " width='40' /></div>"
                            html += "<div style='float:left;'><h4 class='li_prod_name'>" + value.name + "</h4>";
                            if (value.ref != '') {
                                html += "<span class='li_prod_ref'> REF: " + value.ref + " </span></div>";
                            }
                            html += "</li>";
                            sugprod_ul.append(html);
                        });
                    } else {
                        var html;
                        html = "<li>";
                        html += "<div>"+ noMatchesFound +"</div>"
                        html += "</li>";
                        sugprod_ul.append(html);
                    }
                    $('.wk_pack_product_loader').hide();
                }
            });
        }
    });

    $('body').on('click', '.sugpkprod_li', function() {
        $('#selectproduct').removeClass('form-control');
        $('#selectproduct').addClass('wkPkSelectedProduct');
        $('#selectproduct').val($(this).find('.li_prod_name').text());
        $('#selectproduct').data('product_ref', $(this).find('.li_prod_ref').text());
        $('#selectproduct').data('id_ps_product', $(this).data('id_ps_product'));
        $('#selectproduct').data('id_ps_product_attr', $(this).data('id_ps_product_attr'));
        $('#selectproduct').data('img', $(this).data('img'));
        $('#sugpkprod_ul').html('').hide();
    });

    // validate if empty input field
    $('#addpackprodbut').on('click', function(e) {
        e.preventDefault();

        prod_name = $('#selectproduct').val();
        ps_id_prod = $('#selectproduct').data('id_ps_product');
        ps_id_prod_attr = $('#selectproduct').data('id_ps_product_attr');
        prod_img_link = $('#selectproduct').data('img');
        ps_prod_quantity = $('#packproductquant').val();
        prod_ref = $('#selectproduct').data('product_ref');
        var reg = /^\d+$/;
        if (prod_name == '' || ps_id_prod == '' || prod_img_link == '') {
            showErrorMessage(invalid_product_name);
        } else if (!$.isNumeric(ps_prod_quantity) || ps_prod_quantity <= 0 || !reg.test(ps_prod_quantity)) {
            showErrorMessage(invalid_quantity);
        } else {
            $('#selectproduct').val('').data('id_ps_product', '');
            $('#selectproduct').data('img', '');

            $('#packproductquant').val(1);

            listhtml = "<div class='col-sm-4 col-xs-12'>";
            listhtml += "<div class='row no_margin pk_sug_prod' ps_prod_id=" + ps_id_prod + " ps_id_prod_attr=" + ps_id_prod_attr + ">";
            listhtml += "<div class='col-sm-12 col-xs-12'>";
            listhtml += "<img src=" + prod_img_link + " class='img-responsive pk_sug_img'>";
            listhtml += "<p class='text-center'>" + prod_name + "</p>";
            listhtml += "<p class='text-center'>" + prod_ref + "</p>";
            listhtml += "<span class='pull-left'>x" + ps_prod_quantity + "</span>";
            listhtml += "<a class='pull-right dltpkprod'><i class='material-icons'>delete</i></a>";
            listhtml += "<input type='hidden' class='pspk_id_prod' name='pspk_id_prod[]' value='" + ps_id_prod + "'>";
            listhtml += "<input type='hidden' class='pspk_id_prod_attr' name='pspk_id_prod_attr[]' value='" + ps_id_prod_attr + "'>";
            listhtml += "<input type='hidden' name='pspk_prod_quant[]' value='" + ps_prod_quantity + "'>";
            listhtml += "</div>";
            listhtml += "</div>";
            listhtml += "</div>";

            $('.pkprodlist').append(listhtml);
        }
    });

    $('body').on('click', '.dltpkprod', function() {
        $(this).parent().parent().parent().remove();
    });
});

function getSelectedIds() {
    var packAddedIds = $('.pk_sug_prod').val();
    var ints = new Array();
    $.each($('.pk_sug_prod'), function(key, value) {
        var in_ints = new Array();
        in_ints[0] = $(this).attr('ps_prod_id');
        in_ints[1] = $(this).attr('ps_id_prod_attr');
        ints[key] = in_ints;

    });
    return ints;
}