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
    $(document).on('click', '.deletefile', function(e){
        if (confirm(confirmation_msg)) {
            var mp_id_prod = $('#mp_id_prod').val();
            if (mp_id_prod) {
                $.ajax({
                    url: ajaxurl_virtual,
                    type: 'POST',
                    data: {
                        mp_id_prod : mp_id_prod,
                        deletefile : '1',
                    },
                    success: function(data) {
                        if (checkcontroller == 1) {
                            window.location.href = window.location.href;
                        } else {
                            window.location.href = adminlink+'&id_mp_product='+mp_id_prod+'&updatewk_mp_seller_product';
                        }
                    }
                });
            }
        } else {
            return false;
        }
    });

    $(".datepicker").datepicker({
        dateFormat: 'yy-mm-dd'
    });
});