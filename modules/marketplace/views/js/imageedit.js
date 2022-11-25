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
    $(".mp_bulk_delete_btn").on("click", function(e) {
        e.preventDefault();
        if (!$('.mp_bulk_select:checked').length) {
            alert(checkbox_select_warning);
            return false;
        } else {
            if (!confirm(confirm_delete_msg)) {
                return false;
            } else {
                $('#mp_productlist_form').submit();
            }
        }
    });

    $("#mp_all_select").on("click", function() {
        if ($(this).is(':checked')) {
            //$('.mp_bulk_select').parent().addClass('checker checked');
            //$('.mp_bulk_select').attr('checked', 'checked');
            $('.mp_bulk_select').prop('checked', true);
        } else {
            //$('.mp_bulk_select').parent().removeClass('checker checked');
            //$('.mp_bulk_select').removeAttr('checked');
            $('.mp_bulk_select').prop('checked', false);
        }
    });

    //Delete seller product
    $(".delete_mp_product").on("click", function() {
        if (!confirm(confirm_delete_msg))
            return false;
    });

    //Duplicate seller product
    $(".duplicate_mp_product").on("click", function() {
        if (!confirm(confirm_duplicate_msg))
            return false;
    });

    if ($("#mp_product_list").length) {
        $('#mp_product_list').DataTable({
            "bStateSave": true,
            "order": [],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }],
            "language": {
                "lengthMenu": display_name + " _MENU_ " + records_name,
                "zeroRecords": no_product,
                "info": show_page + " _PAGE_ " + show_of + " _PAGES_ ",
                "infoEmpty": no_record,
                "infoFiltered": "(" + filter_from + " _MAX_ " + t_record + ")",
                "sSearch": search_item,
                "oPaginate": {
                    "sPrevious": p_page,
                    "sNext": n_page
                }
            }
        });
    }

    // popup image details on image quick details
    $(document).on('click', '.edit_seq', function(e) {
        e.preventDefault();
        var id_product = $(this).attr("product-id");
        $.ajax({
            type: 'POST',
            url: ajax_urlpath,
            data: {
                id_product: id_product,
                id_lang: id_lang,
                token: $('#wk-static-token').val(),
                image_type: 'cart_default'
            },
            cache: true,
            async: false,
            success: function(data) {
                if (data != 0) {
                    $('#content' + id_product).html(data);
                    moveImagePosition();
                } else {
                    alert(space_error);
                }
            }
        });
    });

    // Image preview popup
    $(document).on('click', '.mp-img-preview', function(e) {
        e.preventDefault();
        $('.mp-image-popup').attr('src', $(this).attr('href'));
        $('#mp_image_preview').modal('show');
    });

    // delete product image
    $(document).on('click', '.delete_pro_image', function(e) {
        e.preventDefault();
        var id_image = $(this).attr('id_image');
        var is_cover = $(this).attr('is_cover');
        var id_mp_product = $(this).attr('id_mp_product');

        if (confirm(confirm_delete_msg)) {
            $.ajax({
                type: 'POST',
                url: ajax_urlpath,
                data: {
                    id_image: id_image,
                    is_cover: is_cover,
                    id_mp_product: id_mp_product,
                    ajax: true,
                    token: $('input[name="token"]').val(),
                    action: 'deleteProductImage',
                },
                cache: true,
                success: function(data) {
                    if (data == 0) {
                        showErrorMessage(error_msg);
                    } else if (data == 1) {
                        $(".imageinforow" + id_image).fadeOut("normal", function() {
                            $(this).remove();
                            $($('.mp-active-image-table tbody')).find('tr').each(function(index, value) {
                                $(value).attr('id_image_position', parseInt(index+1));
                            });
                            $($('.mp-active-image-table tbody')).find('tr td:nth-child(3)').each(function(i, val) {
                                $(val).html('<center>'+parseInt(i+1)+'</center>');
                            });
                        });
                    } else if (data == 2) {
                        location.reload();
                    }
                }
            });
        }
    });

    // change cover image
    $(document).on('click', '.covered', function(e) {
        e.preventDefault();
        var id_image = $(this).attr('alt');
        var is_cover = $(this).attr('is_cover');
        var id_mp_product = $(this).attr('id_mp_product');
        if (is_cover == 0) {
            $.ajax({
                type: 'POST',
                url: ajax_urlpath,
                data: {
                    id_image: id_image,
                    is_cover: is_cover,
                    id_mp_product: id_mp_product,
                    ajax: true,
                    token: $('input[name="token"]').val(),
                    action: 'changeCoverImage',
                },
                cache: true,
                success: function(data) {
                    if (data == 0) {
                        showErrorMessage(error_msg);
                    } else {
                        if (is_cover == 0) {
                            $('.covered').attr('src', mp_image_dir + 'forbbiden.gif');
                            $('.covered').attr('is_cover', '0');
                            $('.covered').css('cursor', 'pointer');
                            $('#changecoverimage' + id_image).attr('src', mp_image_dir + 'icon/icon-check.png');
                            $('#changecoverimage' + id_image).attr('is_cover', '1');
                            $('#changecoverimage' + id_image).css('cursor', 'move');
                            // to change attribue is cover to 1 in delete image link
                            $('.delete_pro_image').attr('is_cover', '0');
                            $('.delete_pro_image[id_image=' + id_image + ']').attr('is_cover', '1');

                            if (typeof $('#wk-product-detail-cover').attr('src') != 'undefined') {
                                //For product details page
                                var wk_cover_img_url = $('#mp_image_'+id_image+' .mp-img-preview img').attr('src');
                                $('#wk-product-detail-cover').attr('src', wk_cover_img_url);
                            }

                            showSuccessMessage(update_success); //Success message
                        }
                    }
                }
            });
        }
    });

    // Drag & Drop positioning the table list
    if (typeof image_drag_drop != 'undefined') {
        moveImagePosition();
    }

    // show product image caption edit
    $(document).on('click', '.edit_legend', function(e) {
        e.preventDefault();
        var id_image = $(this).attr('id_image');
        $('#legendForm'+id_image).show();
        $('.textlegend'+id_image).hide();
        $(this).hide();
    });

    // cancel product image caption edit
    $(document).on('click', '.cancel_legend', function(e) {
        e.preventDefault();
        var id_image = $(this).attr('id_image');
        $('.textlegend'+id_image+":first").show();
        $('#legendForm'+id_image).hide();
        $('#editlegend'+id_image).show();
    });

    // save product image caption
    $(document).on('click', '.save_legend', function(e) {
        e.preventDefault();
        var id_image = $(this).attr('id_image');
        var id_mp_product = $(this).attr('id_mp_product');
        var legend = Array();
        wkError = 0;
        languages.forEach(lang => {
            captionTitle = $('.changelegend_'+id_image+'_'+lang.id_lang).val();
            if (!captionTitle.match(/^[^<>={}]*$/)) {
                showErrorMessage(ImageCaptionLangError + ' '+ lang.name);
                wkError++;
            }
            legend[lang.id_lang] = captionTitle;
        });
        if (wkError > 0) {
            return false;
        }
        var currentLang = $('#seller_lang_btn').val();
        if (currentLang == undefined || currentLang == '') {
            currentLang = id_lang;
        }

        $.ajax({
            type: 'POST',
            url: ajax_urlpath,
            data: {
                id_image: id_image,
                legend: legend,
                id_mp_product: id_mp_product,
                ajax: true,
                token: $('input[name="token"]').val(),
                action: 'addProductImageCaption',
            },
            cache: true,
            success: function(data) {
                if (data == 0) {
                    showErrorMessage(error_msg);
                } else if (data == 1) {
                    showSuccessMessage(update_success); //Success message
                    $('.textlegend'+id_image+'.wk_text_field_'+currentLang).text(legend[currentLang]);
                    $('.textlegend'+id_image+'.wk_text_field_'+currentLang).show();
                    $('#legendForm'+id_image).hide();
                    $('#editlegend'+id_image).show();
                } else {
                    showErrorMessage(data);
                }
            }
        });
    });
});

function moveImagePosition() {
    $(".mp-active-image-table").tableDnD({
        onDrop: function(table, row) {
            var id_mp_product = $(row).attr("id_mp_product");
            var id_row = $(row).attr("id");
            var id_image_position = $(row).attr("id_image_position");
            var id_image = $(row).attr("id_image");
            var to_row_index = $('#' + id_row).index();

            $.ajax({
                method: 'POST',
                url: ajax_urlpath,
                async: false,
                data: {
                    id_image: id_image,
                    id_image_position: id_image_position,
                    to_row_index: to_row_index,
                    id_mp_product: id_mp_product,
                    ajax: true,
                    token: $('input[name="token"]').val(),
                    action: 'changeImagePosition'
                },
                success: function() {
                    $($('.mp-active-image-table tbody')).find('tr').each(function(index, value) {
                        $(value).attr('id_image_position', parseInt(index+1));
                    });
                    $($('.mp-active-image-table tbody')).find('tr td:nth-child(4)').each(function(i, val) {
                        $(val).html('<center>'+parseInt(i+1)+'</center>');
                    });
                    showSuccessMessage(update_success);
                },
                error: function(xhr, status, error) {
                    showErrorMessage(error);
                }
            });
        }
    });
}

function showSuccessMessage(msg) {
    $.growl.notice({ title: "", message: msg });
}

function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}