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

<div class="wk_menu_item">
	{if $is_seller == 1}
		<div class="list_content">
			<ul>
				<li><span class="menutitle">{l s='Marketplace' mod='marketplace'}</span></li>
				<li {if $logic == 1}class="menu_active"{/if}>
					<span>
						<a href="{if isset($dashboard_link)}{$dashboard_link}{else}{$link->getModuleLink('marketplace', 'dashboard')|addslashes}{/if}">
							<i class="material-icons">&#xE871;</i>
							{l s='Dashboard' mod='marketplace'}
						</a>
					</span>
				</li>
				<li {if $logic == 2}class="menu_active"{/if}>
					<span>
						<a href="{if isset($edit_profile_link)}{$edit_profile_link}{else}{$link->getModuleLink('marketplace', 'editprofile')|addslashes}{/if}">
							<i class="material-icons">&#xE254;</i>
							{l s='Edit Profile' mod='marketplace'}
						</a>
					</span>
				</li>
				<li>
					<span>
						<a href="{if isset($seller_profile_link)}{$seller_profile_link}{else}{$link->getModuleLink('marketplace', 'sellerprofile', ['mp_shop_name' => $name_shop])|addslashes}{/if}">
							<i class="material-icons">&#xE851;</i>
							{l s='Seller Profile' mod='marketplace'}
						</a>
					</span>
				</li>
				<li>
					<span>
						<a href="{if isset($shop_link)}{$shop_link}{else}{$link->getModuleLink('marketplace', 'shopstore', ['mp_shop_name' => $name_shop])|addslashes}{/if}">
							<i class="material-icons">&#xE8D1;</i>
							{l s='Shop' mod='marketplace'}
						</a>
					</span>
				</li>
				<li {if $logic == 3}class="menu_active"{/if}>
					<span>
						<a href="{if isset($product_list_link)}{$product_list_link}{else}{$link->getModuleLink('marketplace', 'productlist')|addslashes}{/if}">
							<i class="material-icons">&#xE149;</i>
							{l s='Product' mod='marketplace'}
							<span class="wkbadge-primary" style="float:right;">{$totalSellerProducts}</span>
							<div class="clearfix"></div>
						</a>
					</span>
				</li>
				<li {if $logic == 4}class="menu_active"{/if}>
					<span>
						<a href="{if isset($my_order_link)}{$my_order_link}{else}{$link->getModuleLink('marketplace', 'mporder')|addslashes}{/if}">
							<i class="material-icons">&#xE8F6;</i>
							{l s='Orders' mod='marketplace'}
						</a>
					</span>
				</li>
				<li {if $logic == 5}class="menu_active"{/if}>
					<span>
						<a href="{if isset($my_transaction_link)}{$my_transaction_link}{else}{$link->getModuleLink('marketplace', 'mptransaction')|addslashes}{/if}">
							<i class="material-icons">swap_horiz</i>
							{l s='Transaction' mod='marketplace'}
						</a>
					</span>
				</li>
				<li {if $logic == 6}class="menu_active"{/if}>
					<span>
						<a href="{if isset($payment_detail_link)}{$payment_detail_link}{else}{$link->getModuleLink('marketplace', 'mppayment')|addslashes}{/if}">
							<i class="material-icons">&#xE8A1;</i>
							{l s='Payment Detail' mod='marketplace'}
						</a>
					</span>
				</li>

				<li {if $logic == 'custom_category_seller'}class="menu_active"{/if}>
					<span>
						<a href="{$link->getModuleLink('marketplace', 'customcategoryseller')|addslashes}">
							<i class="material-icons">&#xe574;</i>
							{l s='product category' mod='marketplace'}
						</a>
					</span>
				</li>

				{if Configuration::get('WK_MP_PRESTA_ATTRIBUTE_ACCESS')}
					<li {if $logic=='mp_prod_attribute'}class="menu_active"{/if}>
						<span>
							<a href="{$link->getModuleLink('marketplace', 'productattribute')}" title="{l s='Product Attributes' mod='marketplace'}">
								<i class="material-icons">&#xE839;</i>
								{l s='Product Attributes' mod='marketplace'}
							</a>
						</span>
					</li>
				{/if}
				{if Configuration::get('WK_MP_PRESTA_FEATURE_ACCESS')}
					<li {if $logic=='mp_prod_features'}class="menu_active"{/if}>
						<span>
							<a href="{$link->getModuleLink('marketplace', 'productfeature')}" title="{l s='Product Features' mod='marketplace'}">
								<i class="material-icons">&#xE8D0;</i>
								{l s='Product Features' mod='marketplace'}
							</a>
						</span>
					</li>
				{/if}
				{if Configuration::get('WK_MP_PRODUCT_MANUFACTURER')}
					<li {if $logic=='mpmanufacturerlist'}class="menu_active"{/if}>
						<span>
							<a href="{$link->getModuleLink('marketplace', 'mpmanufacturerlist')}" title="{l s='Brand' mod='marketplace'}" >
								<i class="material-icons">&#xE7EE;</i>
								{l s='Brands' mod='marketplace'}
							</a>
						</span>
						<div class="loading_overlay" style="display:none;"></div>
					</li>
				{/if}
				{if Configuration::get('WK_MP_PRODUCT_SUPPLIER')}
					<li {if $logic=='mpsupplierlist'}class="menu_active"{/if}>
						<span>
							<a href="{$link->getModuleLink('marketplace', 'mpsupplierlist')}" title="{l s='Suppliers' mod='marketplace'}" >
								<i class="material-icons">local_shipping</i>
								{l s='Suppliers' mod='marketplace'}
							</a>
						</span>
					</li>
				{/if}
				{hook h="displayMPMenuBottom"}
			</ul>
		</div>
	{else}
		{hook h="displayMPStaffMenu"}
	{/if}
</div>