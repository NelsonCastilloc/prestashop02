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

<div class="alert alert-success" id="deleteajax" style="display:none;">
	<button data-dismiss="alert" class="close" type="button">×</button>
	{l s='Deleted Successfully' mod='marketplace'}
</div>

<div class="wk-mp-block">
	{hook h="displayMpMenu"}
	<div class="wk-mp-content">
		<div class="page-title" style="background-color:{$title_bg_color};">
			<span style="color:{$title_text_color};">{l s='Brand Products' mod='marketplace'} - {$manuf_name}</span>
		</div>
		<div class="wk-mp-right-column">
			<p class="wk_btn_add_product wk_product_list">
				<a title="{l s='Brand List' mod='marketplace'}" href="{$link->getModuleLink('marketplace', 'mpmanufacturerlist')}">
					<button type="button" class="btn btn-primary btn-sm">
						<i class="material-icons">&#xE896;</i>
						{l s='Brand List' mod='marketplace'}
					</button>
				</a>
			</p>
			<table class="table">
				<thead>
					<tr>
						{if isset($manufproductinfo) && $manufproductinfo|is_array}
							{if $manufproductinfo|@count > 1}
								<th class="no-sort"><input type="checkbox" title="{l s='Select all' mod='marketplace'}" id="mp_all_manf"/></th>
							{/if}
						{/if}
						<th>{l s='Product Id' mod='marketplace'}</th>
						<th>{l s='Name' mod='marketplace'}</th>
						<th>{l s='Quantity' mod='marketplace'}</th>
						<th>{l s='Action' mod='marketplace'}</th>
					</tr>
				</thead>
				<tbody>
					{if isset($manufproductinfo)}
					{assign var="i" value="1"}
					{foreach $manufproductinfo as $data}
						<tr id="manufprodid_{$data.id}">
							{if isset($manufproductinfo) && $manufproductinfo|is_array}{if $manufproductinfo|@count > 1}<td><input type="checkbox" name="mp_manf_selected[]" class="mp_bulk_select" value="{$data.id}"/></td>{/if}{/if}
							<td>{$data.mp_product_id}</td>
							<td>
								<a href="{$link->getModuleLink('marketplace', 'updateproduct', ['id_mp_product' => $data.mp_product_id])}">
									{$data.product_name}
								</a>
							</td>
							<td>{$data.quantity}</td>
							<td>
								<a delmanufproductid="{$data.id}" title="{l s='Delete' mod='marketplace'}" class="delete_manuf_product" style="color:#2fb5d2;cursor:pointer;">
									<i class="material-icons">&#xE872;</i>
								</a>
							</td>
						</tr>
						{assign var="i" value=$i+1}
					{/foreach}
					{else}
						<tr>
							<td colspan="4">{l s='No product yet.' mod='marketplace'}</td>
						</tr>
					{/if}
				</tbody>
			</table>
			{if isset($manufproductinfo) &&$manufproductinfo|is_array}
				{if $manufproductinfo|@count > 1}
					<div class="btn-group">
						<button class="btn btn-default btn-sm dropdown-toggle wk_language_toggle" type="button" data-toggle="dropdown" aria-expanded="false">
						{l s='Bulk actions' mod='marketplace'} <span class="caret"></span>
						</button>
						<ul class="dropdown-menu wk_bulk_actions" role="menu">
							<li>
								<a href="" class="mp_bulk_manufacturer_prod_delete_btn">
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
{/block}