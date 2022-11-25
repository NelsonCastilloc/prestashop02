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

{if isset($WK_MP_SELLER_DETAILS_ACCESS_8)}
<div class="box-account">
	<div class="box-content">
		<div class="row">
			<label class="col-md-6 text-uppercase wk_text_left">
				<strong>{l s='Recent products' mod='marketplace'}</strong>
			</label>
		</div>
		{if isset($mp_shop_product) && !empty($mp_shop_product)}
			<div id="product-slider_block_center" class="wk-product-slider">
				<ul class="mp-prod-slider {if $mp_shop_product|@count > 3}mp-bx-slider{/if}">
					{foreach $mp_shop_product as $key => $product}
						<li class="wk_relative{if $mp_shop_product|@count <= 3} wk-product-out-slider{/if}" {if $key == 2}style="margin-right:0;"{/if}>
							<a href="{$link->getProductLink($product.objproduct)}" class="product_img_link" title="{$product.name}">
								{if Configuration::get('WK_MP_PRODUCT_ON_SALE') && isset($product.on_sale) && $product.on_sale}
									<div class="wk_product_on_sale">{l s='On Sale!' mod='marketplace'}</div>
								{/if}
								<div class="wk-slider-product-img" data-id="{$product.id_mp_product}">
									{if $product.image}
										<img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.image, 'home_default')}" alt="{$product.name}">
									{else}
										<img class="replace-2x img-responsive" src="{$smarty.const._MODULE_DIR_}/marketplace/views/img/home-default.jpg" alt="{$product.name}">
									{/if}
								</div>
								<div class="wk-slider-product-info">
									<div style="margin-bottom:5px;">{$product.name|truncate:45:'...'}</div>
									{if $product.show_price && $showPriceByCustomerGroup}
										<div style="font-weight:bold;">
											{$product.price}
											{if $product.price != $product.retail_price}
												<span class="wk_retail_price">{$product.retail_price}<span>
											{/if}
										</div>
									{/if}
								</div>
								{hook h="displayMpSellerProfileProductListReviews" product=$product}
							</a>
						</li>
					{/foreach}
				</ul>
			</div>
		{else}
			<div class="alert alert-info">
				{l s='No product found' mod='marketplace'}
			</div>
		{/if}
	</div>
</div>
{/if}