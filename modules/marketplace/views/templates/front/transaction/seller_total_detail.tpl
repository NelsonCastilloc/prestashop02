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

<div class="table-responsive table-responsive-row clearfix box-account wk_seller_total">
	{hook h="displayMpTransactionTopContent"}
	{if (Configuration::get('WK_MP_COMMISSION_DISTRIBUTE_ON') == 1)}
		<div class="alert alert-info">{l s='Only payment accepted earning are available' mod='marketplace'}</div>
	{/if}
	<table class="table table-bordered table-striped">
		<thead>
			<tr class="nodrag nodrop">
				<th class="wk_text_center">{l s='Total earning' mod='marketplace'}</th>
				<th class="wk_text_center">{l s='Admin commission' mod='marketplace'}</th>
				<th class="wk_text_center">{l s='Admin tax' mod='marketplace'}</th>
				<th class="wk_text_center">{l s='Admin shipping' mod='marketplace'}</th>
				<th class="wk_text_center">
					{l s='Your Earning' mod='marketplace'}
					<div class="wk_tooltip">
						<span class="wk_tooltiptext">
							{l s='Sum of seller amount, seller tax and seller shipping amount' mod='marketplace'}
						</span>
					</div>
				</th>
				<th class="wk_text_center">{l s='Your withdrawal' mod='marketplace'}</th>
				<th class="wk_text_center">{l s='Your due' mod='marketplace'}</th>
				{hook h=displayMpSellerTransactionTableColumnHead}
			</tr>
		</thead>
		<tbody>
		{if isset($sellerOrderTotal) && $sellerOrderTotal}
		{foreach $sellerOrderTotal as $orderTotal}
			<tr>
				<td class="wk_text_center">{$orderTotal.total_earning|escape:'htmlall':'UTF-8'}</td>
				<td class="wk_text_center">{$orderTotal.admin_commission|escape:'htmlall':'UTF-8'}</td>
				<td class="wk_text_center">{$orderTotal.admin_tax|escape:'htmlall':'UTF-8'}</td>
				<td class="wk_text_center">{$orderTotal.admin_shipping|escape:'htmlall':'UTF-8'}</td>
				<td class="wk_text_center">
					<span class="wkbadge wkbadge-success">{$orderTotal.seller_total|escape:'htmlall':'UTF-8'}</span>
				</td>
				<td class="wk_text_center">
					<span class="wkbadge wkbadge-paid">{$orderTotal.seller_recieve|escape:'htmlall':'UTF-8'}</span>
				</td>
				<td class="wk_text_center">
					<span class="wkbadge wkbadge-pending">{$orderTotal.seller_due|escape:'htmlall':'UTF-8'}</span>
				</td>
				{hook h=displayMpSellerTransactionTableColumnBody seller_payment_data = $orderTotal id_seller_customer = $id_customer}
			</tr>
		{/foreach}
		{else}
		<tr>
			<td colspan="12" class="wk_text_center">{l s='No data found' mod='marketplace'}</td>
		</tr>
		{/if}
		</tbody>
	</table>
</div>