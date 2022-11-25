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

$(window).load(function() {
    setProductById();
});

$(document).ready(function() {
    $(document).on('change', "select[name='shop_customer']", function() {
        setProductById();
    });
});

//Find product on add manufacturer page according to seller choose at backend
function setProductById() {
    var customer_id = $("select[name='shop_customer'] option:selected").val();
    if (customer_id == undefined) {
        customer_id = id_customer;
    }

    if (customer_id != '') {
        $.ajax({
            url: find_seller_product,
            method: 'POST',
            dataType: 'json',
            data: {
                customer_id: customer_id,
                action: "findSellerProduct",
                ajax: "1"
            },
            success: function(result) {
                if (result != '') {
                    if (result == '-1') {
                        $("#loadsellerproduct").html('<div class="alert alert-info">' + all_associated + '</div><div class="help-block">' + temp_manuf + '</div>');
                    } else {
                        $("#loadsellerproduct").html('<select id="select_product" class="form-control" name="selected_products[]" multiple="multiple"></select>');

                        $.each(result, function(key, value) {
                            if (value.assigned !== undefined) {
                                $("#select_product").append('<option style="color:#828282;" disabled value="' + value.id_mp_product + '">' + value.product_name + ' ' + already_assigned + '</option>')
                            } else {
                                $("#select_product").append('<option value="' + value.id_mp_product + '">' + value.product_name + '</option>')
                            }
                        });
                        $("#loadsellerproduct").append('</select>');
                    }
                } else {
                    showErrorMessage(some_error);
                }
            }
        });
    }
}