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

{if isset($backendController) || Configuration::get('WK_MP_PRODUCT_SPECIFIC_RULE')}
	{capture assign=priceDisplayPrecisionFormat}{'%.'|cat:$smarty.const._PS_PRICE_DISPLAY_PRECISION_|cat:'f'}{/capture}
	<hr>
	<div class="form-group row col-md-12">
		<div class="wkslotprice {if isset($backendController)}col-lg-8 col-lg-offset-2{/if} wk_padding_none">
			<h4 class="control-label {if isset($backendController)}col-lg-3{/if}" style="text-align: left;">
				{l s='Specific price' mod='marketplace'}
				<div class="wk_tooltip">
					<span class="wk_tooltiptext">
						{l s='You can set specific prices for customer(s) belonging to different groups, different countries, etc.' mod='marketplace'}
					</span>
				</div>
			</h4>
			<div class="form-group">
				<div id="slotbutton">
					<a class="btn btn-info"
						href="#" id="show_specific_price">
						{if isset($controller)}
							{if $controller == 'addproduct' || $controller == 'updateproduct'}
								<i class="material-icons">&#xE145;</i>
							{else if $controller == 'admin'}
								<i class="icon-plus-sign"></i>
							{/if}
						{/if}
						{l s='Add specific price' mod='marketplace'}
					</a>
					<a class="btn btn-info"
						href="#" id="hide_specific_price" style="display:none">
						{if isset($controller)}
							{if $controller == 'addproduct' || $controller == 'updateproduct'}
								<i class="material-icons">&#xE14C;</i>
							{else if $controller == 'admin'}
								<i class="icon-remove text-danger"></i>
							{/if}
						{/if}
						{l s='Cancel specific price' mod='marketplace'}
					</a>
				</div>
			</div>

			<div id="add_specific_price" class="clearfix" style="display:none;">
				<input type="hidden" id="showTpl" name="showTpl" value="">
				<input type="hidden" name="mp_product_id" value="{if isset($mp_product_id) && $mp_product_id}{$mp_product_id}{/if}" id="mp_product_id">
				<div class="form-group clearfix">
					<label class="control-label col-lg-2 wklabel" for="">{l s='For' mod='marketplace'}</label>
					<div class="col-lg-10">
						<div class="row">
							<div class="col-lg-4">
								<select name="sp_id_currency" id="spm_currency_0" onchange="changeCurrencySpecificPrice(0);" class="form-control form-control-select wkinput">
									<option value="0">{l s='All currencies' mod='marketplace'}</option>
									{foreach from=$currencies item=curr}
										<option value="{$curr.id_currency}">
											{$curr.name}
										</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-4">
								<select name="sp_id_country" id="sp_id_country" class="form-control form-control-select wkinput">
									<option value="0">{l s='All countries' mod='marketplace'}</option>
									{foreach from=$countries item=country}
										<option value="{$country.id_country}">
											{$country.name}
										</option>
									{/foreach}
								</select>
							</div>
							<div class="col-lg-4">
								<select name="sp_id_group" id="sp_id_group" class="form-control form-control-select wkinput">
									<option value="0">{l s='All groups' mod='marketplace'}</option>
									{foreach from=$groups item=group}
										<option value="{$group.id_group}">{$group.name}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="control-label col-lg-2 wklabel" for="customer">{l s='Customer' mod='marketplace'}</label>
					<div class="col-lg-4">
						<input type="hidden" name="sp_id_customer" id="id_customer" value="0" />
						<div class="input-group">
							<input type="text" name="customer" value="" id="wkslotcustomer" autocomplete="off" class="form-control wk_text_field" placeholder="{l s='All customers' mod='marketplace'}" />
							<span class="input-group-addon">
								<i id="customerLoader" class="icon-refresh icon-spin" style="display: none;"></i>
								{if isset($controller) && $controller == 'admin'}
									<i class="icon-search"></i>
								{else}
									<i class="material-icons">&#xE8B6;</i>
								{/if}
							</span>
						</div>
					</div>
				</div>
				<div class="form-group clearfix">
					<div class="col-lg-10 col-lg-offset-2">
						<div id="customers"></div>
					</div>
				</div>
				{if isset($updateProduct) && $updateProduct == 1}
					{if isset($combinationDetailsWithPsIds) && $combinationDetailsWithPsIds}
						<div class="form-group clearfix">
							<label class="control-label col-lg-2 wklabel" for="combination">
								{l s='Combinations' mod='marketplace'}
							</label>
							<div class="col-lg-6">
								<select name="sp_id_product_attribute" id="sp_id_product_attribute" class="form-control form-control-select wkinput">
									<option value="0" selected="selected">
										{l s='All combinations' mod='marketplace'}
									</option>
									{foreach from=$combinationDetailsWithPsIds item=sp_comb}
										<option value={$sp_comb.id_product_attribute}>
											{$sp_comb['attribute_designation']}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
					{/if}
				{/if}
				<div class="form-group clearfix">
					<label class="control-label col-lg-2 wklabel" for="sp_from">{l s='Available' mod='marketplace'}</label>
					<div class="col-lg-10">
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon">{l s='from' mod='marketplace'}</span>
									<input type="text" name="sp_from" class="form-control wk_text_field" value="" id="sp_from" placeholder="YYYY-MM-DD HH:MM:SS" />
									<span class="input-group-addon">
										{if isset($controller) && $controller == 'admin'}
											<i class="icon-calendar-empty"></i>
										{else}
											<i class="material-icons">&#xE916;</i>
										{/if}
									</span>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon">{l s='to' mod='marketplace'}</span>
									<input type="text" name="sp_to" class="form-control wk_text_field" value="" id="sp_to" placeholder="YYYY-MM-DD HH:MM:SS" />
									<span class="input-group-addon">
										{if isset($controller) && $controller == 'admin'}
											<i class="icon-calendar-empty"></i>
										{else}
											<i class="material-icons">&#xE916;</i>
										{/if}
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="control-label col-lg-2 wklabel" for="sp_from_quantity">
						{l s='Starting at' mod='marketplace'}
					</label>
					<div class="col-lg-4">
						<div class="input-group">
							<span class="input-group-addon">{l s='unit' mod='marketplace'}</span>
							<input type="text" name="sp_from_quantity" id="sp_from_quantity" value="1" class="form-control wk_text_field" />
						</div>
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="control-label col-lg-2 wklabel" for="sp_price">{l s='Product price' mod='marketplace'}
						{if $country_display_tax_label}
							{l s='(tax excl.)' mod='marketplace'}
						{/if}
					</label>
					<div class="col-lg-10">
						<div class="row">
							<div class="col-lg-4">
								<div class="input-group">
									<span class="input-group-addon">{$defaultCurrencySign}</span>
									<input type="text" disabled="disabled" name="sp_price" id="sp_price"
									class="form-control wk_text_field" />
								</div>
								<p class="checkbox">
									<label for="leave_bprice">{l s='Leave base price:' mod='marketplace'}</label>
									<input type="checkbox" id="leave_bprice" name="leave_bprice" value="" checked="checked" />
								</p>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group clearfix">
					<label class="control-label col-lg-2 wklabel" for="sp_reduction">
						{l s='Apply a discount of' mod='marketplace'}
					</label>
					<div class="col-lg-10">
						<div class="row">
							<div class="col-lg-4">
								<input type="text" name="sp_reduction" id="sp_reduction" value="0.00"
									class="form-control wk_text_field" />
							</div>
							<div class="col-lg-4">
								<select name="sp_reduction_type" id="sp_reduction_type" class="form-control form-control-select wkinput">
									<option value="amount" selected="selected">
										{if isset($defaultCurrencySign) && $defaultCurrencySign}
											{$defaultCurrencySign}
										{else}
											{l s='Amount' mod='marketplace'}
										{/if}
									</option>
									<option value="percentage">{l s='%' mod='marketplace'}</option>
								</select>
							</div>
							<div class="col-lg-4" id="sp_reduction_tax">
								<select name="sp_reduction_tax" class="form-control form-control-select wkinput">
									<option value="0">{l s='Tax excluded' mod='marketplace'}</option>
									<option value="1" selected="selected">{l s='Tax included' mod='marketplace'}</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				{if isset($updateProduct) && $updateProduct == 1}
					<div class="slot-button">
						<a id="add_btn" class="btn btn-primary" href="#" {if isset($controller) &&
						$controller=='admin' }style="padding: 8px 32px;" {/if}>
							{l s='Add' mod='marketplace'}
						</a>
					</div>
				{/if}
			</div>
		</div>

		{if isset($priceRules) && isset($updateProduct) && $updateProduct == 1}
			<div id="slot_listing" class="table-responsive col-lg-8 col-lg-offset-2">
				<table class="data-table table" id="my-orders-table">
					<thead>
						<tr>
							<th>{l s='#' mod='marketplace'}</th>
							<th>{l s='Combination' mod='marketplace'}</th>
							<th>{l s='Currency' mod='marketplace'}</th>
							<th>{l s='Country' mod='marketplace'}</th>
							<th>{l s='Group' mod='marketplace'}</th>
							<th>{l s='Customer' mod='marketplace'}</th>
							<th>{l s='Fixed price' mod='marketplace'}</th>
							<th>{l s='Impact' mod='marketplace'}</th>
							<th>{l s='Period' mod='marketplace'}</th>
							<th>{l s='From (quantity)' mod='marketplace'}</th>
							<th>{l s='Action' mod='marketplace'}</th>
						</tr>
					</thead>
					<tbody>
						{if isset($priceRules)}
							{foreach $priceRules as $rule}
								<tr class="even" id="slotcontent{$rule['id']}">
									<td>{$rule['id']}</td>
									<td>{$rule['id_combiantion_name']}</td>
									<td>{$rule['id_currency']}</td>
									<td>{$rule['id_country']}</td>
									<td>{$rule['id_group']}</td>
									<td>{$rule['id_customer']}</td>
									<td>{$rule['price']}</td>
									<td>{$rule['impact']}</td>
									<td>{$rule['period'] nofilter}</td>
									<td class="text-center">{$rule['from_quantity']}</td>
									<td>
										<div style="display:flex;">
											<a id="{$rule['id']}" class="slot_edit_link" name="slot_edit_link" data-toggle="modal" data-target="#slot_edit_link_modal" title={l s='Edit' mod='marketplace'} href="javascript:void(0);">
												<i class="material-icons">&#xe3c9;</i>
											</a>
											&nbsp;
											<a id="{$rule['id']}" name="slot_delete_link" data-toggle="tooltip" data-placement="top" title={l s='Delete' mod='marketplace'} href="javascript:void(0);">
												<i class="material-icons">&#xE872;</i>
											</a>
										</div>
									</td>
								</tr>
							{/foreach}
						{else}
							<tr class="odd">
								<td colspan="11" style="text-align: center;">
									{l s='No data found' mod='marketplace'}
								</td>
							</tr>
						{/if}
					</tbody>
				</table>
			</div>
			<div class="wkslotprice_loader">
				<img src="{$modules_dir}marketplace/views/img/loading-small.gif" class="wkslotprice-loading-img" />
			</div>

			<div class="modal fade bd-example-modal-lg" id="slot_edit_link_modal" tabindex="-1" role="dialog"
				aria-labelledby="slot_edit_link_modalTitle" aria-hidden="true">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="slot_edit_link_modalTitle">
								{l s='Edit a specific price' mod='marketplace'}
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</h5>
						</div>
						<div class="modal-body">
							<div id="edit_specific_price" class="clearfix">
								<input type="hidden" id="showTpl" name="showTpl" value="">
								{* <input type="hidden" id="id_ps_product" name="id_ps_product" value=""> *}
								<input type="hidden" id="editSpecificPriceId" name="editSpecificPriceId" value="">
								<input type="hidden" id="id_specific_price" name="id_specific_price" value="">
								<input type="hidden" name="mp_product_id" value="{if isset($mp_product_id) && $mp_product_id}{$mp_product_id}{/if}" id="mp_product_id">
								<div class="form-group clearfix">
									<label class="control-label col-lg-2 wklabel" for="">
										{l s='For' mod='marketplace'}
									</label>
									<div class="col-lg-10">
										<div class="row">
											<div class="col-lg-4">
												<select name="sp_id_currency" id="spm_currency_edit_0"
													onchange="changeCurrencySpecificPriceUpdate(0);" class="form-control form-control-select wkinput">
													<option value="0">{l s='All currencies' mod='marketplace'}</option>
													{foreach from=$currencies item=curr}
														<option value="{$curr.id_currency}">
															{$curr.name}
														</option>
													{/foreach}
												</select>
											</div>
											<div class="col-lg-4">
												<select name="sp_id_country" id="sp_id_country_edit" class="form-control form-control-select wkinput">
													<option value="0">{l s='All countries' mod='marketplace'}</option>
													{foreach from=$countries item=country}
														<option value="{$country.id_country}">
															{$country.name}
														</option>
													{/foreach}
												</select>
											</div>
											<div class="col-lg-4">
												<select name="sp_id_group" id="sp_id_group_edit" class="form-control form-control-select wkinput">
													<option value="0">{l s='All groups' mod='marketplace'}</option>
													{foreach from=$groups item=group}
														<option value="{$group.id_group}">{$group.name}</option>
													{/foreach}
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group clearfix">
									<label class="control-label col-lg-2 wklabel" for="customer">
										{l s='Customer' mod='marketplace'}
									</label>
									<div class="col-lg-4">
										<input type="hidden" name="sp_id_customer" id="id_customer_edit" value="0" />
										<div class="input-group">
											<input type="text" name="customer" value="" id="wkslotcustomer_edit"
											autocomplete="off" class="form-control wk_text_field"
											placeholder="{l s='All customers' mod='marketplace'}" />
											<span class="input-group-addon">
												<i id="customerLoader" class="icon-refresh icon-spin" style="display: none;"></i>
												{if isset($controller) && $controller == 'admin'}
													<i class="icon-search"></i>
												{else}
													<i class="material-icons">&#xE8B6;</i>
												{/if}
											</span>
										</div>
									</div>
								</div>
								<div class="form-group clearfix">
									<div class="col-lg-2">
									</div>
									<div class="col-lg-10">
										<div id="customers_edit"></div>
									</div>
								</div>
								{if isset($updateProduct) && $updateProduct == 1}
									{if isset($combinationDetailsWithPsIds) && $combinationDetailsWithPsIds}
										<div class="form-group clearfix">
											<label class="control-label col-lg-2 wklabel" for="combination">
												{l s='Combinations' mod='marketplace'}
											</label>
											<div class="col-lg-6">
												<select name="sp_id_product_attribute" id="sp_id_product_attribute_edit"
													class="form-control form-control-select wkinput">
													<option value="0" selected="selected">
														{l s='All combinations' mod='marketplace'}
													</option>
													{foreach from=$combinationDetailsWithPsIds item=sp_comb}
														<option value={$sp_comb.id_product_attribute}>{$sp_comb['attribute_designation']}
														</option>
													{/foreach}
												</select>
											</div>
										</div>
									{/if}
								{/if}
								<div class="form-group clearfix">
									<label class="control-label col-lg-2 wklabel" for="sp_from_edit">{l s='Available'
										mod='marketplace'}</label>
									<div class="col-lg-9">
										<div class="row">
											<div class="col-lg-6">
												<div class="input-group">
													<span class="input-group-addon">{l s='from' mod='marketplace'}</span>
													<input type="text" name="sp_from_edit" class="form-control wk_text_field" value="" id="sp_from_edit" placeholder="YYYY-MM-DD HH:MM:SS" />
													<span class="input-group-addon">
														{if isset($controller) && $controller == 'admin'}
															<i class="icon-calendar-empty"></i>
														{else}
															<i class="material-icons">&#xE916;</i>
														{/if}
													</span>
												</div>
											</div>
											<div class="col-lg-6">
												<div class="input-group">
													<span class="input-group-addon">{l s='to' mod='marketplace'}</span>
													<input type="text" name="sp_to_edit" class="form-control wk_text_field" value="" id="sp_to_edit" placeholder="YYYY-MM-DD HH:MM:SS" />
													<span class="input-group-addon">
														{if isset($controller) && $controller == 'admin'}
															<i class="icon-calendar-empty"></i>
														{else}
															<i class="material-icons">&#xE916;</i>
														{/if}
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group clearfix">
									<label class="control-label col-lg-2 wklabel" for="sp_from_quantity_edit">
										{l s='Starting at' mod='marketplace'}
									</label>
									<div class="col-lg-4">
										<div class="input-group">
											<span class="input-group-addon">{l s='unit' mod='marketplace'}</span>
											<input type="text" name="sp_from_quantity" id="sp_from_quantity_edit" value="1" class="form-control wk_text_field" />
										</div>
									</div>
								</div>
								<div class="form-group clearfix">
									<label class="control-label col-lg-2 wklabel" for="sp_price_edit">{l s='Product price' mod='marketplace'}
										{if $country_display_tax_label}
											{l s='(tax excl.)' mod='marketplace'}
										{/if}
									</label>
									<div class="col-lg-10">
										<div class="row">
											<div class="col-lg-4">
												<div class="input-group">
													<span class="input-group-addon">{$defaultCurrencySign}</span>
													<input type="text" disabled="disabled" name="sp_price_edit" id="sp_price_edit"
													class="form-control wk_text_field" />
												</div>
												<p class="checkbox">
													<label for="leave_bprice_edit">{l s='Leave base price:' mod='marketplace'}</label>
													<input type="checkbox" id="leave_bprice_edit" name="leave_bprice_edit" value="1" checked="checked" style="margin:0px" />
												</p>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group clearfix">
									<label class="control-label col-lg-2 wklabel" for="sp_reduction_edit">
										{l s='Apply a discount of' mod='marketplace'}
									</label>
									<div class="col-lg-10">
										<div class="row">
											<div class="col-lg-4">
												<input type="text" name="sp_reduction" id="sp_reduction_edit" value="0.00" class="form-control wk_text_field" />
											</div>
											<div class="col-lg-4">
												<select name="sp_reduction_type" id="sp_reduction_type_edit" class="form-control form-control-select wkinput">
													<option value="amount" selected="selected">
														{if isset($defaultCurrencySign) && $defaultCurrencySign}
															{$defaultCurrencySign}
														{else}
															{l s='Amount' mod='marketplace'}
														{/if}
													</option>
													<option value="percentage">{l s='%' mod='marketplace'}</option>
												</select>
											</div>
											<div class="col-lg-4">
												<select name="sp_reduction_tax" id="sp_reduction_tax_edit" class="form-control form-control-select wkinput">
													<option value="0">{l s='Tax excluded' mod='marketplace'}</option>
													<option value="1" selected="selected">{l s='Tax included' mod='marketplace'}</option>
												</select>
											</div>
										</div>
									</div>
								</div>
								{if isset($updateProduct) && $updateProduct == 1}
									<div class="slot-button">
										<button type="button" id="update_btn" class="btn btn-primary" {if isset($controller) && $controller=='admin' }style="padding: 8px 32px;" {/if}>{l s='Update' mod='marketplace'}</button>
									</div>
								{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
		<div class="{if isset($backendController)}col-lg-8 col-lg-offset-2{else}col-md-12{/if} wk_padding_none">
			<div class="">
				<br>
				<h4 class="wk_padding_none">
					{l s='Priority management' mod='marketplace'}
					<div class="wk_tooltip">
						<span class="wk_tooltiptext">
							{l s='Sometimes one customer can fit into multiple price rules. Priorities allow you to define which rules apply first.' mod='marketplace'}
						</span>
					</div>
				</h4>
			</div>
			<div class='row'>
				<div class="col-lg-3">
					<label>{l s='Priorities' mod='marketplace'}</label>
					<select id="specificPricePriority_0" name="specificPricePriority[]"
						class="form-control form-control-select" aria-label="specificPricePriority_0 input">
						<option value="id_shop"
							{if !isset($specificPricePriority[1])}selected{elseif isset($specificPricePriority[1]) && ($specificPricePriority[1] == 'id_shop')}selected{/if}>
							{l s='Shop' mod='marketplace'}</option>
						<option value="id_currency"
							{if isset($specificPricePriority[1]) && ($specificPricePriority[1] == 'id_currency')}selected{/if}>
							{l s='Currency' mod='marketplace'}</option>
						<option value="id_country"
							{if isset($specificPricePriority[1]) && ($specificPricePriority[1] == 'id_country')}selected{/if}>
							{l s='Country' mod='marketplace'}</option>
						<option value="id_group"
							{if isset($specificPricePriority[1]) && ($specificPricePriority[1] == 'id_group')}selected{/if}>
							{l s='Group' mod='marketplace'}</option>
					</select>
				</div>
				<div class="col-lg-3">
					<label>&nbsp;</label>
					<select id="specificPricePriority_1" name="specificPricePriority[]"
						class="form-control form-control-select" aria-label="specificPricePriority_1 input">
						<option value="id_shop"
							{if isset($specificPricePriority[2]) && ($specificPricePriority[2] == 'id_shop')}selected{/if}>
							{l s='Shop' mod='marketplace'}</option>
						<option value="id_currency"
							{if !isset($specificPricePriority[2])}selected{elseif isset($specificPricePriority[2]) && ($specificPricePriority[2] == 'id_currency')}selected{/if}>
							{l s='Currency' mod='marketplace'}</option>
						<option value="id_country"
							{if isset($specificPricePriority[2]) && ($specificPricePriority[2] == 'id_country')}selected{/if}>
							{l s='Country' mod='marketplace'}</option>
						<option value="id_group"
							{if isset($specificPricePriority[2]) && ($specificPricePriority[2] == 'id_group')}selected{/if}>
							{l s='Group' mod='marketplace'}</option>
					</select>
				</div>
				<div class="col-lg-3">
					<label>&nbsp;</label>
					<select id="specificPricePriority_2" name="specificPricePriority[]"
						class="form-control form-control-select" aria-label="specificPricePriority_2 input">
						<option value="id_shop"
							{if isset($specificPricePriority[3]) && ($specificPricePriority[3] == 'id_shop')}selected{/if}>
							{l s='Shop' mod='marketplace'}</option>
						<option value="id_currency"
							{if isset($specificPricePriority[3]) && ($specificPricePriority[3] == 'id_currency')}selected{/if}>
							{l s='Currency' mod='marketplace'}</option>
						<option value="id_country"
							{if !isset($specificPricePriority[3])}selected{elseif isset($specificPricePriority[3]) && ($specificPricePriority[3] == 'id_country')}selected{/if}>
							{l s='Country' mod='marketplace'}</option>
						<option value="id_group"
							{if isset($specificPricePriority[3]) && ($specificPricePriority[3] == 'id_group')}selected{/if}>
							{l s='Group' mod='marketplace'}</option>
					</select>
				</div>
				<div class="col-lg-3">
					<label>&nbsp;</label>
					<select id="specificPricePriority_3" name="specificPricePriority[]"
						class="form-control form-control-select" aria-label="specificPricePriority_3 input">
						<option value="id_shop"
							{if isset($specificPricePriority[4]) && ($specificPricePriority[4] == 'id_shop')}selected{/if}>
							{l s='Shop' mod='marketplace'}</option>
						<option value="id_currency"
							{if isset($specificPricePriority[4]) && ($specificPricePriority[4] == 'id_currency')}selected{/if}>
							{l s='Currency' mod='marketplace'}</option>
						<option value="id_country"
							{if isset($specificPricePriority[4]) && ($specificPricePriority[4] == 'id_country')}selected{/if}>
							{l s='Country' mod='marketplace'}</option>
						<option value="id_group"
							{if !isset($specificPricePriority[4])}selected{elseif isset($specificPricePriority[4]) && ($specificPricePriority[4] == 'id_group')}selected{/if}>
							{l s='Group' mod='marketplace'}</option>
					</select>
				</div>
			</div>
		</div>
	</div>
{/if}