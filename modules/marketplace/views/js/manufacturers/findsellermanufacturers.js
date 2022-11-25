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
    setDataById();
});

$(document).ready(function() {
    $(document).on('change', "select[name='shop_customer']", function() {
        setDataById();
    });
});

//Find manufacturer on add product page according to seller choose at backend
function setDataById() {
    var customer_id = $("select[name='shop_customer'] option:selected").val();
    if (customer_id == undefined) {
        customer_id = id_customer;
    }
    if (customer_id != '') {
        $.ajax({
            url: add_manufacturer_admin,
            method: 'POST',
            dataType: 'json',
            data: {
                customer_id: customer_id,
                action: 'findSellerManufacturers',
                ajax: true
            },
            success: function(result) {
                if (result !== null) {
                    $("#product_manufacturer").empty();
                    $("#product_manufacturer").append("<option value=''>" + choose_optional + "</option>");
                    $.each(result, function(key, value) {
                        if (value.id_manufacturer == selected_id_manuf) {
                            $("#product_manufacturer").append("<option value='" + value.id_manufacturer + "' selected>" + value.name + "</option>");
                        } else {
                            $("#product_manufacturer").append("<option value='" + value.id_manufacturer + "'>" + value.name + "</option>");
                        }
                    });
                } else {
                    $("#product_manufacturer").remove();
                    $('.wk-manuf-add').html('<div class="alert alert-info text-left">' + no_manufacuturer + '</div>');
                }
            }
        });
    }
}