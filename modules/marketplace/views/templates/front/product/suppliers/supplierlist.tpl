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

{extends file=$layout}
{block name='content'}
<div id="alert_div">
	{if isset($msg_code)}
		{if $msg_code == 1}
			<div class="alert alert-success">{l s='Supplier added successfully.' mod='marketplace'}</div>
		{elseif $msg_code == 2}
			<div class="alert alert-success">{l s='Supplier updated successfully.' mod='marketplace'}</div>
		{elseif $msg_code == 3}
			<div class="alert alert-danger">{l s='Their is some technical error in updating supplier.' mod='marketplace'}</div>
		{elseif $msg_code == 4}
			<div class="alert alert-success">{l s='Supplier assigned successfully.' mod='marketplace'}</div>
		{/if}
	{/if}
</div>

<img src="{$smarty.const._MODULE_DIR_}marketplace/views/img/loader.gif" id="ajax_loader" style="display: none;z-index: 10000;position: absolute;top: 50%;left: 50%;" />

<div class="wk-mp-block">
	{hook h="displayMpMenu"}
	<div class="wk-mp-content">
		<div class="page-title" style="background-color:{$title_bg_color};">
			<span style="color:{$title_text_color};">{l s='Suppliers' mod='marketplace'}</span>
		</div>
		<div class="wk-mp-right-column">
			<p class="wk_text_right wk_product_list">
				<a href="{$link->getModuleLink('marketplace', 'mpaddsupplier')}" class="pull-right">
					<button class="btn btn-primary btn-sm" type="button">
						<i class="material-icons">&#xE145;</i>
						{l s='Add supplier' mod='marketplace'}
					</button>
				</a>
			</p>
			<div class="clearfix"></div>
			<div class="mt-2">
				<table class="table table-hover table-bordered" id="mpSupplierList">
					<thead>
						<tr>
							{if isset($mpSupplierInfo) && $mpSupplierInfo|is_array}
								{if $mpSupplierInfo|@count > 1}
									<th class="no-sort"><input type="checkbox" title="{l s='Select all' mod='marketplace'}" id="mp_all_select"/></th>
								{/if}
							{/if}
							<th>#</th>
							<th>{l s='Logo' mod='marketplace'}</th>
							<th>{l s='Name' mod='marketplace'}</th>
							<th>{l s='Products' mod='marketplace'}</th>
							<th>{l s='Status' mod='marketplace'}</th>
							<th>{l s='Action' mod='marketplace'}</th>
						</tr>
					</thead>
					<tbody>
						{if isset($mpSupplierInfo)}
							{foreach from=$mpSupplierInfo key=k item=supplier}
								<tr id="mp_spllier_{$supplier.id_wk_mp_supplier}">
									{if $mpSupplierInfo|is_array}{if $mpSupplierInfo|@count > 1}<td><input type="checkbox" name="mp_product_selected[]" class="mp_bulk_select" value="{$supplier.id_wk_mp_supplier}"/></td>{/if}{/if}
									<td>{$k +1}</td>
									<td><img src="{$supplier.image}" class="img-thumbnail" width="45" height="45"/></td>
									<td><a href="{$link->getModuleLink('marketplace', 'mpsupplierproductslist', ['id' => $supplier.id_wk_mp_supplier])}">{$supplier.name}</a></td>
									<td>{$supplier.no_of_products}</td>
									<td>
										<center>
											{if ($supplier.active)}
												<span class="wk_product_approved">{l s='Approved' mod='marketplace'}</span>
											{else}
												<span class="wk_product_pending">{l s='Pending' mod='marketplace'}</span>
											{/if}
										</center>
									</td>
									<td>
										<a title="{l s='Edit' mod='marketplace'}" href="{$link->getModuleLink('marketplace', 'mpupdatesupplier', ['id' => $supplier.id_wk_mp_supplier])}">
											<i class="material-icons">&#xE254;</i>
										</a>
										&nbsp;
										<a ps_supplier_id="{$supplier.id_ps_supplier}" mp_supplier_id="{$supplier.id_wk_mp_supplier}" style="color:#2fb5d2 !important;cursor: pointer;" title="{l s='Delete' mod='marketplace'}" class="mp_supplier_delete" style="cursor:pointer;">
											<i class="material-icons">&#xE872;</i>
										</a>
									</td>
								</tr>
							{/foreach}
						{/if}
					</tbody>
				</table>
				{if  isset($mpSupplierInfo) &&  $mpSupplierInfo|is_array}
					{if $mpSupplierInfo|@count > 1}
						<div class="btn-group">
							<button class="btn btn-default btn-sm dropdown-toggle wk_language_toggle" type="button" data-toggle="dropdown" aria-expanded="false">
							{l s='Bulk actions' mod='marketplace'} <span class="caret"></span>
							</button>
							<ul class="dropdown-menu wk_bulk_actions" role="menu">
								<li>
									<a href="" class="mp_bulk_delete_btn">
										<i class="material-icons">&#xE872;</i> {l s='Delete selected' mod='marketplace'}
									</a>
								</li>
							</ul>
						</div>
					{/if}
				{/if}
			</div>
		</div>
	</div>
</div>
{/block}