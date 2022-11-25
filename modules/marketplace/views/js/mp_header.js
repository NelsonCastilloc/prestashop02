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

$(document).ready(function(){
	$('#wk_ad_close').on('click', function() {
		$('footer.wk_ad_footer').remove();
		var now = new Date();
		var time = now.getTime();
		time += 3600 * 1000;
		now.setTime(time);
		document.cookie =
			'no_advertisement=' + 1 + '; expires=' + now.toUTCString() + '; path=/';
		});

		$(".from_export_date").datepicker({
			prevText: '',
			nextText: '',
			dateFormat: 'dd-mm-yy',
			maxDate: 0
		});
	
		$(".to_export_date").datepicker({
			prevText: '',
			nextText: '',
			dateFormat: 'dd-mm-yy',
			maxDate: 0
		});
	
		$(document).on("submit", "#product_export_csv", function() {
			var from_export_date = $('.from_export_date').val();
			var to_export_date = $('.to_export_date').val();
			if (from_export_date == '') {
				showErrorMessage(empty_from_date);
				return false;
			} else if (to_export_date == '') {
				showErrorMessage(empty_to_date);
				return false;
			} else if(from_export_date > to_export_date){
				showErrorMessage(compare_date_error);
				return false;
			}
		});

		$(".mp_csv_product_export_all").on("click", function(){
			if (friendly_url) {
				window.location.href = productlist_link+'?export_all=1'
			} else {
				window.location.href = productlist_link+'&export_all=1';
			}
		});

		$(document).on("click", ".wk_product_export_button", function() {
			$('#product_export_csv').toggle(400);
		});
});