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

<div class="wk_catg_list">
	{if isset($catg_details)}
		<ul class="wk_catg_list_ul">
			<li>
				<span class="wk_catg_head">
					{l s='Seller Category' mod='marketplace'}
				</span>
			</li>
			{assign var="selected_cat_id" value="0"}
			{if isset($smarty.get.id_category)}
				{assign var="selected_cat_id" value="{$smarty.get.id_category}"}
			{/if}

			{if isset($smarty.get.id_category)}
				<li>
					<span>
						<a class="wk-collection-category" href="{$link->getModuleLink('marketplace', 'shopstore', ['mp_shop_name' => $name_shop])}">
							<button class="btn btn-tertiary">
								<i class="material-icons" style="margin-right:0px;">clear</i>
								{l s='Clear filter' mod='marketplace'}
							</button>
						</a>
					</span>
				</li>
			{/if}

			{foreach $catg_details as $catg}
				<a class="wk-collection-category" href="{$link->getModuleLink('marketplace', 'shopstore', ['mp_shop_name' => $name_shop, 'id_category' => $catg.id_category])}">
					<li {if $selected_cat_id == $catg.id_category}style="background: #f2f2f2;"{/if}>
						<span>{$catg.Name} ({$catg.NoOfProduct})</span>
					</li>
				</a>
			{/foreach}
		</ul>
	{/if}
</div>