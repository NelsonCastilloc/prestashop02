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
    //Manage profile and shop banner in width:height 4:1 according to screen size change
    const bannerImgHeight = parseInt($('.wk_banner_image').width()) / 4;
    $(".wk_banner_image").css("height", bannerImgHeight);

    $(window).on('resize', function() { //While resize screen
        const bannerImgHeight = parseInt($('.wk_banner_image').width()) / 4;
        $(".wk_banner_image").css("height", bannerImgHeight);
    });

    if (typeof avg_rating !== 'undefined') {
        $('.avg_rating').raty({
            path: module_dir + 'marketplace/views/img',
            score: avg_rating,
            readOnly: true,
        });
    } else {
        $('.wk_seller_rating').hide();
    }

    $(document).on('change', '.selectMpProductSort', function() {
        var splitData = $(this).val().split(':');
        if ($(this).val() == 'id') {
            //default sorting by last added product
            document.location.href = requestSortProducts;
        } else {
            document.location.href = requestSortProducts + ((requestSortProducts.indexOf('?') < 0) ? '?' : '&') + 'orderby=' + splitData[0] + '&orderway=' + splitData[1];
        }
    });

    $("#collectionquickview").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget);
        $(this).find(".modal-body").load(link.attr("href"));
    });
});