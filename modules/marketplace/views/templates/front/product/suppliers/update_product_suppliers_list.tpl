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

{if isset($backendController) || Configuration::get('WK_MP_PRODUCT_SUPPLIER')}
	<hr>
	<div class="form-group">
		<h4 class="control-label {if isset($backendController)}col-lg-3{/if}">
			{l s='Suppliers' mod='marketplace'}
			<div class="wk_tooltip">
				<span class="wk_tooltiptext">
					{l s='Please choose the suppliers associated with this product and select a default supplier as well.' mod='marketplace'}
				</span>
				<img src="{$smarty.const._MODULE_DIR_}marketplace/views/img/loader.gif" id="ajax_loader"
					style="display: none;z-index: 10000;position: absolute;top: 20%;left: 50%;" />
			</div>
		</h4>
		<div class='{if isset($backendController)}col-lg-6{/if}'>
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-labeled hidden-sm-down">
					<thead>
						<tr>
							<th style="width:10%" class='wk_select_supplier'>
								{if isset($suppliers) && count($suppliers) > 1}<input value="" id="selectAllSuppliers"
									type="checkbox" class="selectAllSuppliers  supplier_custom_css" name="" />{/if}</th>
							<th>{l s='Supplier Name' mod='marketplace'}</th>
							<th>{l s='Default' mod='marketplace'}</th>
						</tr>
					</thead>
					<tbody id="supplier_list_tbody">
						{if isset($suppliers)}
							{foreach $suppliers as $supplier}
								{assign var=selected_supplier_checkbox value=0}
								{assign var=selected_supplier_is_enable value=0}
								<tr>
									<td>
										<input value="{$supplier.id_supplier|escape:'html':'UTF-8'}" type="checkbox"
											class="supplier_checkbox mpsupplier_custom_checkbox_radio" name="selected_suppliers[]"
											{if isset($selected_suppliers_list)}
												{foreach $selected_suppliers_list as $temp_selected_supplier}
													{if $supplier.id_supplier == $temp_selected_supplier['id_supplier']} checked="checked"
													{assign var=selected_supplier_is_enable value=1} 
												{/if} 
											{/foreach} 
										{/if} />
								</td>
								<td>{$supplier.name|escape:'html':'UTF-8'}</td>
								<td>
									<input id="supplier_radio_{$supplier.id_supplier|escape:'html':'UTF-8'}"
										value="{$supplier.id_supplier|escape:'html':'UTF-8'}" type="radio"
										{if $supplier.id_supplier == $selected_id_supplier} checked="checked" {/if}
										{if $selected_supplier_is_enable == 1} 
										{else} disabled="disabled"
										{/if}class="supplier_radio mpsupplier_custom_checkbox_radio" name="default_supplier" />
								</td>
							</tr>
						{/foreach}
					{else}
						<tr>
							<td colspan="3">{l s='You didn\'t create any supplier' mod='marketplace'}</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
		<a href="javascript:;" class="btn btn-info" id="supplier_confirm_leave">
			{l s='Create new supplier' mod='marketplace'}
		</a>
		{if isset($backendController)}
		</div>
	<div>{/if}
		<div id="supplier_combination_collection"
			class="{if isset($backendController)}col-md-8 col-lg-offset-2{else}col-md-12{/if} wk_padding_none">
			{if isset($selected_suppliers_data)}
				<br>
				<h4>{l s='Supplier reference(s)' mod='marketplace'}</h4>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-info" role="alert">
							<p class="alert-text">
								{l s='You can specify product reference(s) for each associated supplier. Click "Save" after changing selected suppliers to display the associated product references.' mod='marketplace'}
							</p>
						</div>
					</div>
				</div>
				{foreach $selected_suppliers_data as $selected_supplier_attr}
					<div class="panel panel-default">
						<div class="panel-heading"><strong>{$selected_supplier_attr[0].id_supplier} -
								{$selected_supplier_attr[0].name}</strong>
						</div>

						<div class="{if !isset($backendController)}panel-body{/if}"
							id="supplier_combination_{$selected_supplier_attr[0].id_supplier}">
							<div>
								<table class="table">
									<thead class="thead-default">
										<tr>
											<th width="30%">{l s='Product name' mod='marketplace'}</th>
											<th width="30%">{l s='Supplier reference' mod='marketplace'}</th>
											<th width="20%">{l s='Cost price (tax excl.)' mod='marketplace'}</th>
											<th width="20%">{l s='Currency' mod='marketplace'}</th>
										</tr>
									</thead>
									<tbody>
										{foreach $selected_supplier_attr as $selected_supplier}
											{if (isset($combination_detail) && $selected_supplier.id_product_attribute != 0) || !isset($combination_detail)}
												<tr>
													{if isset($combination_detail) && $selected_supplier.id_product_attribute > 0}
														{foreach from=$combination_detail item=$combination}
															{if $combination.id_product_attribute == $selected_supplier.id_product_attribute}
																<td>{$combination.attribute_designation}</td>
															{/if}
														{/foreach}
													{else}
														<td>{$product_info.name[$default_lang]}</td>
													{/if}

													<td>
														<input type="text"
															name="supplier_combination_{$selected_supplier.id_supplier}[{$selected_supplier.id_product_attribute}][supplier_reference]"
															value='{$selected_supplier.product_supplier_reference}'
															class="form-control">
													</td>
													<td>
														<div class="input-group">
															<span class="input-group-addon">
																{foreach from=$currencies item=curr}
																	{if $selected_supplier.id_currency == $curr.id_currency}{$curr.symbol}{/if}
																{/foreach}
															</span>
															<input type="text" id="" class="form-control wk_text_field"
																name="supplier_combination_{$selected_supplier.id_supplier}[{$selected_supplier.id_product_attribute}][product_price]"
																value='{$selected_supplier.product_supplier_price_te}'
																class="form-control wk_text_field">
														</div>
													</td>
													<td>
														<select
															id="supplier_combination_{$selected_supplier.id_supplier}_{$selected_supplier.id_product_attribute}_product_price_currency"
															name="supplier_combination_{$selected_supplier.id_supplier}[{$selected_supplier.id_product_attribute}][product_price_currency]"
															onchange="changeSuppliersCurrency({$selected_supplier.id_supplier}, {$selected_supplier.id_product_attribute});"
															class="form-control form-control-select wkinput">
															{foreach from=$currencies item=curr}
																<option value="{$curr.id_currency}" data-symbol='{$curr.symbol}'
																	{if $selected_supplier.id_currency == $curr.id_currency}selected{/if}>
																	{$curr.id_currency} - {$curr.name}
																</option>
															{/foreach}
														</select>
														<input type="hidden"
															name="supplier_combination_{$selected_supplier.id_supplier}[{$selected_supplier.id_product_attribute}][id_product_attribute]"
															class="form-control" value="{$selected_supplier.id_product_attribute}">

														<input type="hidden"
															name="supplier_combination[{$selected_supplier.id_supplier}][{$selected_supplier.id_product_attribute}][supplier_id]"
															class="form-control" value="{$selected_supplier.id_supplier}">
													</td>
												</tr>
											{/if}
										{/foreach}
									</tbody>
								</table>
							</div>
						</div>
					</div>
				{/foreach}
			{/if}
		</div>
	</div>
</div>
{/if}