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

    $('.mp_vitual_btnr').click(function(e) {
        e.preventDefault();
        $('#mp_vrt_prod').trigger('click');
    })
    $("#mp_vrt_prod").on("change", function() {
        var file_name = $(this).val().split('/').pop().split('\\').pop();
        if (file_name !== '') {
            if ((Math.round(this.files[0].size / 1024 / 1024) * 100) / 100 > allowed_file_size) {
                showErrorMessage(allowed_file_size_error);
            } else {
                $("#mp_vrt_prod_name").val(file_name);
                $(".mp_vrt_prod_name").text(file_name);
            }
        }
    });
});