{**
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
*}

<form method="post" id='order_export_csv' class="wk_display_none">
	<div class="box-content">
		<div class="mp_order_export">
			<div class="row">
				<div class="col-md-10">
					<div class="form-group col-md-4">
						<label class="control-label required">{l s='From' mod='marketplace'}</label>
						<input type="text" name="from_export_date" class="from_export_date form-control">
					</div>
					<div class="form-group col-md-4">
						<label class="control-label required">{l s='To' mod='marketplace'}</label>
						<input type="text" name="to_export_date" class="to_export_date form-control">
					</div>
					<div class="form-group col-md-4">
						<label class="control-label d-block">&emsp;</label>
						<button class="btn btn-info mp_csv_order_export" type="submit" name="mp_csv_order_export">
							<span>{l s='Export' mod='marketplace'}</span>
						</button>
					</div>
				</div>

				<div class="form-group col-md-2">
					<label class="control-label d-block">&emsp;</label>
					<button class="btn btn-info mp_csv_order_export_all" type="button" name="mp_csv_order_export_all">
						<span>{l s='Export All' mod='marketplace'}</span>
					</button>
				</div>
			</div>
			<hr>
		</div>
	</div>
</form>