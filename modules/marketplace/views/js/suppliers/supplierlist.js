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

	$(".mp_bulk_delete_btn").on("click", function(e) {
        e.preventDefault();
        if (!$('.mp_bulk_select:checked').length) {
            showErrorMessage(checkbox_select_warning);
            return false;
        } else {
            if (!confirm(confirm_delete_msg)) {
                return false;
            } else {
				var allSupIds = $('.mp_bulk_select').length;
                var mpSupIds = [];
				$('.mp_bulk_select').each(function() {
					if ($(this).is(':checked')) {
						mpSupIds.push($(this).val())
					}
				})

				var data1 = {
					ajax: 1,
					mpSupIds: JSON.stringify(mpSupIds),
					action:'deleteBulkSupplier',
					token: static_token,
				}
				$('body').css('opacity', '0.5');
				$('#ajax_loader').css('display', 'block');
				$.ajax({
					url:supplier_ajax_link,
					type: 'POST',
					data: data1,
					success: function(data) {
						$('body').css('opacity', '1');
						$('#ajax_loader').css('display', 'none');
						$('#alert_div').slideUp();
						$('#alert_div').empty();
						let res = JSON.parse(data);
						if(res==1) {
							mpSupIds.forEach((mp_supplier_id) => {
								$('#mp_spllier_'+mp_supplier_id).remove();
							})
							if (allSupIds == mpSupIds.length) {
								$('#mp_all_select').parents('th').remove();
							}
							$('#alert_div').html("<div class='alert alert-success'>"+del_sucs+"</div>");
							$('#alert_div').slideDown('slow');
						}
					}
				});
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

	$('.mp_bulk_delete_btn').click(function() {
		var mpSupIds = [];
		$('.mp_bulk_select').each(function() {
			if ($(this).is(':checked')) {
				mpSupIds.push($(this).val())
			}
		})



	})


	if ($("#mpSupplierList").length) {
        $('#mpSupplierList').DataTable({
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

	$(document).on('click', '.mp_supplier_delete', function (event) {
		var ps_supplier_id = $(this).attr('ps_supplier_id');
		var mp_supplier_id = $(this).attr('mp_supplier_id');
		var data1 = {
			ajax: 1,
			ps_supplier_id:ps_supplier_id,
			mp_supplier_id:mp_supplier_id,
			action:'deleteSupplier',
            token: static_token,
		}
		var r=confirm(conf_msg);
		if (r==true) {
			$('body').css('opacity', '0.5');
			$('#ajax_loader').css('display', 'block');
			$.ajax({
				url:supplier_ajax_link,
				type: 'POST',
				data: data1,
				success: function(data) {
					$('body').css('opacity', '1');
					$('#ajax_loader').css('display', 'none');
					$('#alert_div').slideUp();
					$('#alert_div').empty();
					if(data==1) {
						$('#mp_spllier_'+mp_supplier_id).remove();
						$('#alert_div').html("<div class='alert alert-success'>"+del_sucs+"</div>");
						$('#alert_div').slideDown('slow');
					}
				}, fail: function(data) {
					$('#alert_div').slideUp();
					$('#alert_div').empty();
					$('body').css('opacity', '1');
					$('#ajax_loader').css('display', 'none');
					$('#alert_div').html("<div class='alert alert-danger'>"+tech_error+"</div>");
					$('#alert_div').slideDown('slow');
				}, error: function(data) {
					$('#alert_div').slideUp();
					$('#alert_div').empty();
					$('body').css('opacity', '1');
					$('#ajax_loader').css('display', 'none');
					$('#alert_div').html("<div class='alert alert-danger'>"+tech_error+"</div>");
					$('#alert_div').slideDown('slow');
				}
			});
		}
    });

});

function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}