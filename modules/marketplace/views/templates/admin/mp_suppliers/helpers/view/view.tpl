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

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
<div class="panel">
	<div class="panel-heading">{$supplierInfo['name']|escape:'htmlall':'UTF-8'}{l s=' Supplier Products List' mod='marketplace'}</div>
	<table class="table">
		<thead>
			<tr>
				<th>#</th>
				<th><span class="title_box">{l s='Product name' mod='marketplace'}</span></th>
			</tr>
		</thead>
		<tbody>
		{if isset($product_list)}
			{foreach from=$product_list key=id item=product}
				<tr>
					<td>{$id+1|escape:'htmlall':'UTF-8'}</td>
					<td><a class="btn btn-link" href="?controller=AdminSellerProductDetail&amp;id_mp_product={$product.id_mp_product|escape:'htmlall':'UTF-8'}&amp;updatewk_mp_seller_product&amp;token={getAdminToken tab='AdminSellerProductDetail'}">{$product.product_name|escape:'htmlall':'UTF-8'}</a></td>
				</tr>
			{/foreach}
		{else}
			<tr>
				<td colspan="2">{l s='No products assign in this supplier' mod='marketplace'}</td>
			</tr>
		{/if}
		</tbody>
	</table>
</div>
{/block}

