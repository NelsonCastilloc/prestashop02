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

{if isset($product['pack_items'])}
	<a href="#" data-toggle="modal"
		data-target="#product-pack-items-modal-{$product.product_id}">{l s='View pack content' mod='marketplace'}</a>
	<div class="modal fade customization-modal" id="product-pack-items-modal-{$product.product_id}" tabindex="-1"
		role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' mod='marketplace'}">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">{l s='Products in pack' mod='marketplace'}</h4>
				</div>
				<div class="modal-body">
					<table class="table" id="product-pack-modal-table">
						<thead>
							<tr>
								<th colspan="3">{l s='Product' mod='marketplace'}</th>
								<th>{l s='Quantity' mod='marketplace'}</th>
							</tr>
						</thead>
						<tbody>
							{foreach $product['pack_items'] as $item}
								<tr>
									<td>{l s='Package item' mod='marketplace'}</td>
									<td class="cell-product-img">
										<img class="img-thumbnail" src="{$item->image_link}" alt="" style="max-width: 100px;" />
									</td>
									<td class="cell-product-name">
										<a href="{$link->getProductLink($item->id)|addslashes}">
											{if $item->name}
												<p class="mb-0 product-name">{$item->name}</p>
											{/if}
											{if $item->supplier_reference}
												<p class="mb-0 product-reference">
													Reference number: {$item->reference}
												</p>
											{/if}

											{if $item->supplier_reference}
												<p class="mb-0 product-supplier-reference">
													Supplier reference: {$item->supplier_reference}
												</p>
											{/if}
										</a>
									</td>
									<td class="cell-product-quantity">
										<span>{$item->quantity}</span>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
{/if}