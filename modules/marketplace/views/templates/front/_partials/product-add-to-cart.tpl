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

<form action="{url entity=cart}" method="post">
	<input type="hidden" name="token" value="{$static_token}">
	<input type="hidden" name="id_product" value="{$product.id_ps_product}">
	<input type="hidden" name="id_customization" value="0">

	{if isset($product.hasCombination) && isset($product.combinationData)}
		{foreach $product.combinationData as $combination}
			<input type="hidden" data-product-attribute="{$combination.id_attribute_group}" name="group[{$combination.id_attribute_group}]" value="{$combination.id_attribute}">
		{/foreach}
	{/if}

	<input type="hidden" name="qty" value="{if $product.minimal_quantity}{$product.minimal_quantity}{else}1{/if}">
	<button type="submit" id="wk_shop_cart_{$product.id_ps_product}" data-button-action="add-to-cart" class="btn btn-primary">
		<i class="material-icons shopping-cart">&#xE8CC;</i>{l s='Add to cart' mod='marketplace'}
	</button>
</form>