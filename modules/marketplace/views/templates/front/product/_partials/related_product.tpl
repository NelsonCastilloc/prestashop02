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

{if isset($backendController) || Configuration::get('WK_MP_RELATED_PRODUCT')}
	<div class="form-group">
		<br>
		<h4 class="control-label {if isset($backendController)}col-lg-3{/if}">
			{l s='Related product' mod='marketplace'}
			<img class="wk_related_product_loader wk_display_none" src="{$module_dir}marketplace/views/img/loader.gif" width="20" />
		</h4>
		<div class='{if isset($backendController)}col-lg-6{/if}'>
			<input class="form-control" type="text" name="relatedproductsearch" id="relatedproductsearch" autocomplete="off" />
			<small class="help-block">
				{l s='Start by typing the first letter of the product name, then select the product from the drop-down list.' mod='marketplace'}
			</small>
			<div class="row no_margin related_container">
				<ul id="relatedprod_ul" style="margin-top: -20px;"></ul>
			</div>
			<div id='selected_related_product'>
				<br>
				{if isset($relatedProducts) && $relatedProducts}
					{foreach $relatedProducts as $relatedProduct}
						<div class="alert wk-selected-products alert-dismissible" role="alert">
							<span><img src="{$relatedProduct.image}" width="50" height="50">
								<input type="hidden" value="{$relatedProduct.id}"
									name="related_product[]">{$relatedProduct.name}</span>
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
								<span aria-hidden="true">×</span>
							</button>
						</div>
					{/foreach}
				{/if}
			</div>
		</div>
	</div>
{/if}