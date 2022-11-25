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
    $(document).on('click', '#mp_all_manf', function() {
        let boxes = document.querySelectorAll('.mp_bulk_select');
        if (this.checked) {
            boxes.forEach((box) => {
                if (!box.checked) {
                    box.checked = true;
                }
            })
        } else {
            boxes.forEach((box) => {
                box.checked = false;
            })
        }
    })

    $("#update_manuf").on("click", function() {
        var name = $("#manuf_name").val().trim();
        var special_char = /^[^<>;=#{}]*$/;
        if (name == '') {
            showErrorMessage("Manufacturer name is required field.");
            $('#manuf_name').focus();
            return false;
        } else if (!isNaN(name) || !special_char.test(name)) {
            showErrorMessage("Invalid manufacturer name.");
            $('#manuf_name').focus();
            return false;
        }
    });

    $(document).on('click', '.mp_bulk_manufacturer_delete_btn', function(e) {
        e.preventDefault();
        var selectedManuf = [];
        
        document.querySelectorAll('.mp_bulk_select').forEach(function(box) {
            if (box.checked) {
                selectedManuf.push(box.value);
            }
        })

        if (selectedManuf.length > 0) {
            if (confirm(confirm_msg)) {
                $(".loading_overlay").show();
                $(".loading_overlay").html("<img class='loading-img' src='" + module_dir + "marketplace/views/img/loader.gif'>");
    
                $.ajax({
                    url: delete_manufacturer,
                    data: {
                        remove_bulk_manuf: 1,
                        manufIds: JSON.stringify(selectedManuf),
                        token: static_token,
                        ajax: true,
                        action: 'bulkDeleteManufacturer'
                    },
                    method: 'POST',
                    success: function(data) {
                        $(".loading_overlay").html('');
                        $(".loading_overlay").hide();
                        if (data) {
                            $('#deletemanufajax').css("display", "block");
                            selectedManuf.forEach((mp_manuf_id) => {
                                $('#manufid_' + mp_manuf_id).remove();
                            })
                            window.location.href = '';
                        }
                    }
                });
            }
        }
    })

    $(document).on('click', '.mp_bulk_manufacturer_prod_delete_btn', function(e) {
        e.preventDefault();
        var selectedManufProd = [];
        
        document.querySelectorAll('.mp_bulk_select').forEach(function(box) {
            if (box.checked) {
                selectedManufProd.push(box.value);
            }
        })

        if (selectedManufProd.length > 0) {
            if (confirm(confirm_msg)) {
                $(".loading_overlay").show();
                $(".loading_overlay").html("<img class='loading-img' src='" + module_dir + "marketplace/views/img/loader.gif'>");
    
                $.ajax({
                    url: delete_manufacturer_prod,
                    data: { 
                        remove_bulk_manuf_prod: 1,
                        idProds: JSON.stringify(selectedManufProd),
                        token: static_token,
                        ajax: true,
                        action: 'bulkDeleteManufacturerProduct'
                    },
                    method: 'POST',
                    success: function(data) {
                        $(".loading_overlay").html('');
                        $(".loading_overlay").hide();
                        if (data) {
                            $('#deleteajax').css("display", "block");
                            selectedManufProd.forEach((mp_manuf_id) => {
                                $('#manufprodid_' + mp_manuf_id).remove();
                            })
                            if ($('.mp_bulk_select').length == 0) {
                                $('#mp_all_manf').parent().remove();
                            }
                        }
                    }
                });
            }
        }
    })

    $(".delete_manuf_data").on("click", function(e) {
        e.preventDefault();
        var mp_manuf_id = $(this).attr("delmanufid");
        if (confirm(confirm_msg)) {
            $(".loading_overlay").show();
            $(".loading_overlay").html("<img class='loading-img' src='" + module_dir + "marketplace/views/img/loader.gif'>");

            $.ajax({
                url: delete_manufacturer,
                data: {
                    remove_manuf: 1,
                    mp_manuf_id: mp_manuf_id,
                    token: static_token,
                    ajax: true,
                    action: 'deleteManufacturer'
                },
                method: 'POST',
                success: function(data) {
                    $(".loading_overlay").html('');
                    $(".loading_overlay").hide();
                    if (data == 1) {
                        $('#deletemanufajax').css("display", "block");
                        $('#manufid_' + mp_manuf_id).remove();
                    }
                }
            });
        }
    });

    $(".delete_manuf_product").on("click", function(e) {
        e.preventDefault();
        var manufproductid = $(this).attr("delmanufproductid");
        if (confirm(confirm_msg)) {
            $(".loading_overlay").show();
            $(".loading_overlay").html("<img class='loading-img' src='" + module_dir + "marketplace/views/img/loader.gif'>");

            $.ajax({
                url: delete_manufacturer_prod,
                data: {
                    removemanufprod: 1,
                    manufproductid: manufproductid,
                    token: static_token,
                    ajax: true,
                    action: 'deleteManufacturerProduct'
                },
                method: 'POST',
                success: function(data) {
                    $(".loading_overlay").html('');
                    $(".loading_overlay").hide();
                    if (data) {
                        $('#deleteajax').css("display", "block");
                        $('#manufprodid_' + manufproductid).remove();
                       
                    }
                }
            });
        }
    });

    if (typeof wk_dataTables != 'undefined') {
        $('#mp_manufacturer_list').DataTable({
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
            },
            "order": [
                [1, "asc"]
            ]
        });

        $('select[name="mp_manufacturer_list_length"]').addClass('form-control-select');
    }
});