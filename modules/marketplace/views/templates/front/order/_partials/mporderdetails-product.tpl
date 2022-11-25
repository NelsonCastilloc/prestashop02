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

<div class="box-account box-recent">
	<div class="box-head">
		<div class="box-head-left">
			<h2><i class="icon-shopping-cart"></i> {l s='Products' mod='marketplace'} ({count($order_products)})
				{$reference}</h2>
		</div>
		<div class="box-head-right">
			<a class="btn btn-primary btn-sm" href="{$link->getModuleLink('marketplace','mporder')}">
				<span>{l s='Back to orders' mod='marketplace'}</span>
			</a>
		</div>
	</div>
	<div class="table-responsive clearfix box-content wk-order-table">
		<table class="table">
			<thead>
				<th>
					<span class="title_box">{l s='Product' mod='marketplace'}</span>
				</th>
				<th>
					<span class="title_box">{l s='Unit Price' mod='marketplace'}</span>
					<small class="text-muted">{l s='(Tax Excl.)' mod='marketplace'}</small>
				</th>
				<th>
					<span class="title_box">{l s='Unit Price' mod='marketplace'}</span>
					<small class="text-muted">{l s='(Tax Incl.)' mod='marketplace'}</small>
				</th>
				<th>
					<span class="title_box">{l s='Quantity' mod='marketplace'}</span>
				</th>
				<th>
					<span class="title_box">{l s='Total Price' mod='marketplace'}</span>
					<small class="text-muted">{l s='(Tax Incl.)' mod='marketplace'}</small>
				</th>
				{hook h="displayMpOrderProductTableTh"}
			</thead>
			<tbody>
				{foreach $order_products as $product}
					<tr>
						<td>
							{if isset($product.active) && ($product.active == 1) && ($currentShopId == $product.id_shop)}
								<a href="{$link->getProductLink($product.product_id)|addslashes}" target="_blank"
									title="{l s='View Products' mod='marketplace'}">{$product.product_name|escape:'html':'UTF-8'}</a>
							{else}
								{$product.product_name|escape:'html':'UTF-8'}
							{/if}

							{if isset($product['pack_items']) && is_array($product['pack_items']) && !empty($product['pack_items'])}
								<br><br>
								{include file='module:marketplace/views/templates/front/order/_partials/pack_detail.tpl'}
							{/if}

							{if isset($product['customization_data']) && is_array($product['customization_data']) && !empty($product['customization_data'])}
								<br><br>
								{include file='module:marketplace/views/templates/front/order/_partials/customization_detail.tpl'}
							{/if}
						</td>
						<td>{$product.unit_price_tax_excl|escape:'html':'UTF-8'}</td>
						<td>{$product.unit_price_tax_incl|escape:'html':'UTF-8'}</td>
						<td class="">{$product.quantity|escape:'html':'UTF-8'}</td>
						<td>{$product.total_price_tax_incl_formatted|escape:'html':'UTF-8'}</td>
						{hook h="displayMpOrderProductTableTd" product=$product}
					</tr>
					{hook h="displayMpOrderDetailListRow" params=$product type='productlist'}
				{/foreach}
				<tr>
					<td class="wk_empty_row"></td>
					<td class="wk_empty_row"></td>
					<td class="wk_empty_row"></td>
					<td><strong>{l s='Products' mod='marketplace'}</strong></td>
					<td>{$product_total|escape:'html':'UTF-8'}</td>
				</tr>
				{if isset($mp_voucher_info) && $mp_voucher_info}
					<tr>
						<td class="wk_empty_row"></td>
						<td class="wk_empty_row"></td>
						<td class="wk_empty_row"></td>
						<td class="wk_empty_row"><strong>{l s='Voucher' mod='marketplace'}</strong></td>
						<td class="wk_empty_row"></td>
					</tr>
					{foreach $mp_voucher_info as $mp_voucher}
						<tr>
							<td class="wk_empty_row"></td>
							<td class="wk_empty_row"></td>
							<td></td>
							<td>{$mp_voucher.voucher_name|escape:'html':'UTF-8'}</td>
							<td>-{$mp_voucher.voucher_value|escape:'html':'UTF-8'}</td>
						</tr>
					{/foreach}
				{/if}
				{*Display Shipping amount after Mp shipping distribution*}
				{if (isset($seller_shipping_earning))}
					<tr>
						<td class="wk_empty_row"></td>
						<td class="wk_empty_row"></td>
						<td class=""></td>
						<td><strong>{l s='Shipping' mod='marketplace'}</strong></td>
						<td>{$seller_shipping_earning|escape:'html':'UTF-8'}</td>
					</tr>
				{/if}
				<tr>
					<td class="wk_empty_row"></td>
					<td class="wk_empty_row"></td>
					<td class="wk_empty_row"></td>
					<td><strong>{l s='Total' mod='marketplace'}</strong></td>
					<td>{$mp_total_order|escape:'html':'UTF-8'}</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
{hook h='displayMpOrderDetailProductBottom'}