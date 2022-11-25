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

{if Configuration::get('WK_MP_SELLER_PRODUCT_SEO') || isset($backendController)}
	<div class="form-group">
		<label for="meta_title" class="control-label">
			{l s='Meta Title' mod='marketplace'}

			<div class="wk_tooltip">
				<span
					class="wk_tooltiptext">{l s='Public title for the product\'s page, and for search engines. Leave blank to use the product name. The number of remaining characters is displayed to the left of the field.' mod='marketplace'}</span>
			</div>
			{if $allow_multilang && $total_languages > 1}
				<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}"
					src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
			{/if}
		</label>
		{foreach from=$languages item=language}
			{assign var="meta_title" value="meta_title_`$language.id_lang`"}
			<div id="meta_title_div_{$language.id_lang}"
				class="wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
				<input type="text" name="meta_title_{$language.id_lang}" id="meta_title_{$language.id_lang}"
					class="form-control" maxlength="128"
					value="{if isset($smarty.post.$meta_title)}{$smarty.post.$meta_title}{else}{if isset($product_info)}{$product_info.meta_title[{$language.id_lang}]}{/if}{/if}"
					placeholder="{l s='To have a different title from the product name, enter it here.' mod='marketplace'}">
			</div>
		{/foreach}
	</div>
	<div class="form-group">
		<label for="meta_description" class="control-label">
			{l s='Meta Description' mod='marketplace'}

			<div class="wk_tooltip">
				<span
					class="wk_tooltiptext">{l s='This description will appear in search engines. You need a single sentence, shorter than 160 characters (including spaces).' mod='marketplace'}</span>
			</div>
			{if $allow_multilang && $total_languages > 1}
				<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}"
					src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
			{/if}
		</label>
		{foreach from=$languages item=language}
			{assign var="meta_description" value="meta_description_`$language.id_lang`"}
			<div id="meta_description_div_{$language.id_lang}"
				class="wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
				<textarea name="meta_description_{$language.id_lang}" id="meta_description_{$language.id_lang}"
					class="form-control" cols="2" rows="3" maxlength="255"
					placeholder="{l s='To have a different description than your product summary in search results pages, write it here.' mod='marketplace'}">{if isset($smarty.post.$meta_description)}{$smarty.post.$meta_description}{else}{if isset($product_info)}{$product_info.meta_description[{$language.id_lang}]}{/if}{/if}</textarea>
			</div>
		{/foreach}
	</div>
	<div class="form-group">
		<label for="link_rewrite" class="control-label">
			{l s='Friendly URL' mod='marketplace'}

			<div class="wk_tooltip">
				<span
					class="wk_tooltiptext">{l s='This is the human-readable URL, as generated from the product\'s name. You can change it if you want.' mod='marketplace'}</span>
			</div>
			{if $allow_multilang && $total_languages > 1}
				<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}"
					src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
			{/if}
		</label>
		{foreach from=$languages item=language}
			{assign var="link_rewrite" value="link_rewrite_`$language.id_lang`"}
			<div id="link_rewrite_div_{$language.id_lang}"
				class="wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
				<input type="text" name="link_rewrite_{$language.id_lang}" id="link_rewrite_{$language.id_lang}"
					class="form-control" maxlength="128"
					value="{if isset($smarty.post.$link_rewrite)}{$smarty.post.$link_rewrite}{else}{if isset($product_info)}{$product_info.link_rewrite[{$language.id_lang}]}{/if}{/if}">
			</div>
		{/foreach}
	</div>
{/if}
{if Configuration::get('WK_MP_PRODUCT_PAGE_REDIRECTION') || isset($backendController)}
	<div class="form-group row">
		<div class="col-md-6">
			<label for="redirect_type" class="control-label">
				{l s='Redirection page' mod='marketplace'}
				<div class="wk_tooltip">
					<span
						class="wk_tooltiptext">{l s='When your product is disabled, choose to which page you\’d like to redirect the customers visiting its page by typing the product or category name.' mod='marketplace'}</span>
				</div>
			</label>
			<select name="redirect_type" class="form-control form-control-select" id="redirect_type">
				<option value="301-category"
					{if isset($product_info) && $product_info.redirect_type == '301-category'}selected{/if}>
					{l s='Permanent redirection to a category (301)' mod='marketplace'}</option>
				<option value="302-category"
					{if isset($product_info) && $product_info.redirect_type == '302-category'}selected{/if}>
					{l s='Temporary redirection to a category (302)' mod='marketplace'}</option>
				<option value="301-product"
					{if isset($product_info) && $product_info.redirect_type == '301-product'}selected{/if}>
					{l s='Permanent redirection to a product (301)' mod='marketplace'}</option>
				<option value="302-product"
					{if isset($product_info) && $product_info.redirect_type == '302-product'}selected{/if}>
					{l s='Temporary redirection to a product (302)' mod='marketplace'}</option>
				<option value="404"
					{if (isset($product_info) && $product_info.redirect_type == '404') || !isset($product_info.redirect_type)}selected{/if}>
					{l s='No redirection (404)' mod='marketplace'}</option>
			</select>
		</div>
		<input type="hidden" name="id_type_redirected" value="{if isset($product_info) && $product_info.id_type_redirected}{$product_info.id_type_redirected}{/if}" id="id_type_redirected">
		<div class="col-md-6">
			<div class="target_category_div {if !isset($product_info) || ($product_info.redirect_type != '301-category' && $product_info.redirect_type != '302-category')} wk_display_none {/if}">
				<label for="target_category" class="control-label">
					{l s='Target category' mod='marketplace'}
					<div class="wk_tooltip">
						<span class="wk_tooltiptext">{l s='To which category the page should redirect?' mod='marketplace'}</span>
					</div>
				</label>
				<select class="form-control form-control-select chosen" name="target_category" id="target_category">
					<option>{l s='Select category' mod='marketplace'}</option>
					{if isset($redirectCategories)}
						{foreach item=category from=$redirectCategories}
							<option id="target_category{$category.id_category}" value="{$category.id_category}" {if isset($product_info.id_type_redirected)}{if $product_info.id_type_redirected == $category.id_category} selected {/if}{/if}>{$category.name}</option>
						{/foreach}
					{/if}
				</select>
				<small>{l s='If no category is selected, the Main Category is used' mod='marketplace'}</small>
			</div>
			<div class="target_product_div {if !isset($product_info) || ($product_info.redirect_type != '301-product' && $product_info.redirect_type != '302-product')} wk_display_none {/if}">
				<label for="target_product" class="control-label">
					{l s='Target Product' mod='marketplace'}
					<div class="wk_tooltip">
						<span class="wk_tooltiptext">{l s='To which product the page should redirect?' mod='marketplace'}</span>
					</div>
				</label>
				<select class="form-control form-control-select chosen" name="target_product" id="target_product">
					<option>{l s='Select product' mod='marketplace'}</option>
					{if isset($redirectProducts)}
						{foreach item=redirectProduct from=$redirectProducts}
							<option id="target_product{$redirectProduct.id_product}" value="{$redirectProduct.id_product}" {if isset($product_info.id_type_redirected)}{if $product_info.id_type_redirected == $redirectProduct.id_product} selected {/if}{/if}>{$redirectProduct.name}</option>
						{/foreach}
					{/if}
				</select>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="alert alert-info" role="alert">
			<p class="alert-text">
				{l s='No redirection (404) = Do not redirect anywhere and display a 404 "Not Found" page.' mod='marketplace'}<br>
				{l s='Permanent redirection (301) = Permanently display another product or category instead.' mod='marketplace'}<br>
				{l s='redirection (302) = Temporarily display another product or category instead.' mod='marketplace'}
			</p>
		</div>
	</div>
{/if}