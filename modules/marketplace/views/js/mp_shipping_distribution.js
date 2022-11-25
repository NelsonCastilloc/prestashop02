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
    $(".distribute_type").on("change", function() {
        var shipping_distribute_type = $(this).val();
        var id_ps_reference = $(this).data('id-ps-reference');
        if (id_ps_reference) {
            $.ajax({
                url: path_admin_mp_shipping,
                data: {
                    ajax: true,
                    action: "changeShippingDistributionType",
                    id_ps_reference: id_ps_reference,
                    shipping_distribute_type: shipping_distribute_type
                },
                dataType: 'json',
                success: function(result) {
                    if (result == '1') {
                        showSuccessMessage(success_msg);
                    } else if (result == '0') {
                        showErrorMessage(error_msg);
                    }
                },
                error: function(xhr, status, error) {
                    return 0;
                }
            });
        }
    });
});

function showSuccessMessage(msg) {
    $.growl.notice({ title: "", message: msg });
}

function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}