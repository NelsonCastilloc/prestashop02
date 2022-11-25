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
	<h3>{l s='Products' mod='marketplace'} <span class="badge">{if isset($manufproductinfo)}{count($manufproductinfo)}{else}0{/if}</span></h3>
	{if isset($manufproductinfo)}
		{foreach $manufproductinfo as $manufproduct}
			<div class="panel">
				<div class="panel-heading">
					<a href="?controller=AdminSellerProductDetail&amp;id_mp_product={$manufproduct.mp_product_id}&amp;updatewk_mp_seller_product&amp;token={getAdminToken tab='AdminSellerProductDetail'}">
						{$manufproduct.product_name}
					</a>
					<div class="pull-right">
						<a href="?controller=AdminManufacturerDetail&amp;id={$manufproduct.mp_manuf_id}&amp;manufproductid={$manufproduct.id}&amp;deleteManufProduct&amp;token={getAdminToken tab='AdminManufacturerDetail'}" class="btn btn-default btn-sm">
							<i class="icon-trash"></i> {l s='Delete' mod='marketplace'}
						</a>
					</div>
					<div class="pull-right">
						<a href="?controller=AdminSellerProductDetail&amp;id_mp_product={$manufproduct.mp_product_id}&amp;updatewk_mp_seller_product&amp;token={getAdminToken tab='AdminSellerProductDetail'}" class="btn btn-default btn-sm">
							<i class="icon-edit"></i> {l s='Edit' mod='marketplace'}
						</a>
					</div>
				</div>
				<table class="table">
					<thead>
						<tr><th>
							<span class="title_box">{l s='Qty:' mod='marketplace'}</span> {$manufproduct.quantity}
							</th>
						</tr>
					</thead>
				</table>
			</div>
		{/foreach}
	{else}
		<label class="control-label">{l s='No product assign in this brand' mod='marketplace'}</label>
	{/if}
</div>
{/block}
