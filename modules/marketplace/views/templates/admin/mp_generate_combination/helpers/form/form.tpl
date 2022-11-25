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

<div class="panel">
	<div class="panel-heading">
		{if isset($edit)}
			{l s='Edit attribute generator' mod='marketplace'}
		{else}
			{l s='Attribute generator' mod='marketplace'}
		{/if}
	</div>
	<div class="form group">
		<a class="btn btn-link wk_padding_none" href="{$link->getAdminLink('AdminSellerProductDetail')}&updatewk_mp_seller_product&id_mp_product={$id_mp_product}">
			<i class="icon-arrow-left"></i>
			<span>{l s='Back to product' mod='marketplace'}</span>
		</a>
	</div>
	<div class="row">
		{include file="$wkself/../../views/templates/front/product/combination/_partials/generate-combination-fields.tpl"}
	</div>
</div>

{strip}
	{addJsDefL name=i18n_tax_exc}{l s='Tax excluded' mod='marketplace' js=1}{/addJsDefL}
	{addJsDefL name=i18n_tax_inc}{l s='Tax included' mod='marketplace' js=1}{/addJsDefL}
{/strip}