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
	$(".mp_order_row").on("click", function(){
		var id_order =  $(this).attr('is_id_order');
		if (friendly_url) {
			window.location.href = mporderdetails_link+'?id_order='+id_order;
		} else {
			window.location.href = mporderdetails_link+'&id_order='+id_order;
		}
	});

	$('#my-orders-table').DataTable({
		"bStateSave": true,
		"language": {
			"lengthMenu": display_name+" _MENU_ "+records_name,
			"zeroRecords": no_product,
			"info": show_page+" _PAGE_ "+ show_of +" _PAGES_ ",
			"infoEmpty": no_record,
			"infoFiltered": "("+filter_from +" _MAX_ "+ t_record+")",
			"sSearch" : search_item,
			"oPaginate": {
				"sPrevious": p_page,
				"sNext": n_page
				}
		},
		"order": [[ 0, "desc" ]]
	});

	$('select[name="my-orders-table_length"]').addClass('form-control-select');

	$(document).on("submit", "#order_export_csv", function() {
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

	$(".mp_csv_order_export_all").on("click", function(){
		if (friendly_url) {
			window.location.href = mporder_link+'?export_all=1'
		} else {
			window.location.href = mporder_link+'&export_all=1';
		}
	});

	$(document).on("click", ".wk_export_button", function() {
		$('#order_export_csv').toggle(400);
	});
});

function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}