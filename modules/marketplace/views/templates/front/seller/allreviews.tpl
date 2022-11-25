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
{if Configuration::get('WK_MP_REVIEW_SETTINGS')}
	<div class="wk-mp-block">
		<div class="page-title" style="background-color:{$title_bg_color};">
			<span style="color:{$title_text_color};">{l s='All Reviews' mod='marketplace'} {if isset($nbReviews)}({$nbReviews}){/if}</span>
		</div>
		<div class="wk-mp-right-column">
			<div class="box-account">
				<div class="box-content" style="margin-top:0px;">
					{if isset($reviews_info)}
						<div class="wk-allreviews-page-seller-rating">
							{block name='mp-seller-rating-summary'}
								{include file='module:marketplace/views/templates/front/seller/_partials/seller-rating-summary.tpl'}
							{/block}
						</div>
						{foreach from=$reviews_info item=review}
							{block name='mp-seller-review-list'}
								{include file='module:marketplace/views/templates/front/seller/_partials/seller-review-list.tpl'}
							{/block}
						{/foreach}
						<div class="wk-pagination-right">
							<div class="col-sm-12">
								<ul class="pagination pagination-sm">
									<li class="page-item">
										<a class="page-link {if $p == 1}wk-disabled{/if}" {if $p > 1}href="{$link->getModuleLink('marketplace', 'allreviews', ['mp_shop_name' => $shopLinkRewrite, 'p' => $p-1])}"{/if} aria-label="Previous">
											<span aria-hidden="true">{l s='Previous' mod='marketplace'}</span>
										</a>
									</li>
									{for $i = 1 to $page_count}
										<li class="page-item">
											<a class="page-link {if $p == $i}wk-page-active{/if}" {if $p != $i}href="{$link->getModuleLink('marketplace', 'allreviews', ['mp_shop_name' => $shopLinkRewrite, 'p' => $i])}"{/if}>{$i}</a>
										</li>
									{/for}
									<li class="page-item">
										<a class="page-link {if $p == $page_count}wk-disabled{/if}" {if $p < $page_count}href="{$link->getModuleLink('marketplace', 'allreviews', ['mp_shop_name' => $shopLinkRewrite, 'p' => $p+1])}"{/if} aria-label="Next">
											<span aria-hidden="true">{l s='Next' mod='marketplace'}</span>
										</a>
									</li>
								</ul>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="clearfix"></div>
					{else}
						<p>{l s='No reviews available' mod='marketplace'}</p>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/if}
{/block}