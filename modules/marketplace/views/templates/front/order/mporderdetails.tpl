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
{if empty($editOrderPermission)}
	<p class="alert alert-danger">
		<button data-dismiss="alert" class="close" type="button">×</button>
		{l s='You do not have permission to update order status and tracking details.' mod='marketplace'}
	</p>
{/if}
	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">{l s='Order Details' mod='marketplace'}</span>
			</div>
			<div class="clearfix wk-mp-right-column">
				{block name='mporderdetails_product'}
					{include file="module:marketplace/views/templates/front/order/_partials/mporderdetails-product.tpl"}
				{/block}
				{hook h='displayMpOrderProductDetailsBottom'}
				{block name='mpcommission_details'}
					{include file="module:marketplace/views/templates/front/order/_partials/commission-summary.tpl"}
				{/block}

				{block name='mporderdetails_customer'}
					{include file="module:marketplace/views/templates/front/order/_partials/mporderdetails-customer.tpl"}
				{/block}

				<div class="clearfix box-account box-recent">
					<div class="box-head">
						<h2><i class="icon-credit-card"></i> {l s='Shipping Details' mod='marketplace'}</h2>
						<div class="wk_border_line"></div>
					</div>
					{if isset($order_shipping_name)}
						<div class="wk-shipping-name">
							<b>{l s='Carrier Name' mod='marketplace'}</b> - {$order_shipping_name|escape:'html':'UTF-8'}
						</div>
					{/if}
					<div class="clearfix box-content">
						{block name='mporderdetails_shipping'}
							{include file="module:marketplace/views/templates/front/order/_partials/mporderdetails-shipping.tpl"}
						{/block}
						{hook h='displayMpOrderDetialShippingBottom'}
					</div>
				</div>
				{hook h='displayMpOrderDetailsBottom'}
			</div>
		</div>
	</div>
{/block}