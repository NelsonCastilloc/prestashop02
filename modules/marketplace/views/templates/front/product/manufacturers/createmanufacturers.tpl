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

{if isset($smarty.get.createmanuf)}
	<div class="alert alert-success">
		<button data-dismiss="alert" class="close" type="button">×</button>
		{l s='Brand created successfully' mod='marketplace'}
	</div>
{else}
	{if isset($smarty.get.updatemanuf)}
		<div class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Brand updated successfully' mod='marketplace'}
		</div>
	{/if}
{/if}

<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/tinymce/tinymce_wk_setup.js"></script>
<div class="wk-mp-block">
	{hook h="displayMpMenu"}
	<div class="wk-mp-content">
		<div class="page-title" style="background-color:{$title_bg_color};">
			<span style="color:{$title_text_color};">
				{if isset($manufId)}
					{l s='Update Brand' mod='marketplace'}
				{else}
					{l s='Create Brand' mod='marketplace'}
				{/if}
			</span>
		</div>
		<div class="wk-mp-right-column">
			<form action="{if isset($manufId)}{$link->getModuleLink('marketplace', 'mpcreatemanufacturers', ['id' => $manufId])}{else}{$link->getModuleLink('marketplace', 'mpcreatemanufacturers')}{/if}" method="post" enctype="multipart/form-data" accept-charset="UTF-8,ISO-8859-1,UTF-16">
			{if isset($manufId)}
				<input type="hidden" name="manuf_id" id="manuf_id" value="{$manufId}">
			{/if}
				{block name='change-product-language'}
					{include file='module:marketplace/views/templates/front/product/manufacturers/_partials/change-language.tpl'}
				{/block}
				<input type="hidden" name="current_lang" id="current_lang" value="{$current_lang.id_lang}">
				<div class="form-group">
					<label for="manuf_name" class="control-label required">{l s='Brand name' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" name="manuf_name" id="manuf_name" value="{if isset($smarty.post.manuf_name)}{$smarty.post.manuf_name}{else}{if isset($manufId)}{$manufacInfo.name}{/if}{/if}" maxlength="64">
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="manuf_short_desc" class="control-label">
						{l s='Short Description' mod='marketplace'}
						{block name='form-fields-flag'}
							{include file='module:marketplace/views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl'}
						{/block}
					</label>
					<div class="row">
						<div class="col-md-12">
							{foreach from=$languages item=language}
								{assign var="short_desc_name" value="short_description_`$language.id_lang`"}
								<div id="short_desc_div_{$language.id_lang}" class="short_desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<textarea name="short_description_{$language.id_lang}" id="short_description_{$language.id_lang}" cols="2" rows="3" class="wk_tinymce form-control">{if isset($smarty.post.$short_desc_name)}{$smarty.post.$short_desc_name}{else}{if isset($manufId)}{$manufacInfo.short_description[{$language.id_lang}]}{/if}{/if}</textarea>
								</div>
							{/foreach}
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="description" class="control-label">
						{l s='Description' mod='marketplace'}
						{block name='form-fields-flag'}
							{include file='module:marketplace/views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl'}
						{/block}
					</label>
					<div class="row">
						<div class="col-md-12">
							{foreach from=$languages item=language}
								{assign var="description" value="description_`$language.id_lang`"}
								<div id="desc_div_{$language.id_lang}" class="desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<textarea name="description_{$language.id_lang}"
									id="description_{$language.id_lang}" cols="2" rows="3" class="wk_tinymce form-control">{if isset($smarty.post.$description)}{$smarty.post.$description}{else}{if isset($manufId)}{$manufacInfo.description[{$language.id_lang}]}{/if}{/if}</textarea>
								</div>
							{/foreach}
				  		</div>
					</div>
				</div>
				<div class="form-group">
					<label for="manuf_logo">{l s='Logo' mod='marketplace'}</label>
					{if isset($imageexist)}
					<div>
						<img class="imgm img-thumbnail" alt="{l s='Brand Image' mod='marketplace'}" src="{$smarty.const._MODULE_DIR_}marketplace/views/img/mpmanufacturers/{$manufId}.jpg">
					</div>
					{/if}
					<input type="file" id="manuf_logo" name="manuf_logo" class="form-control" size="chars">
				</div>
				<div class="form-group">
					<label for="meta_title" class="control-label">
						{l s='Meta title' mod='marketplace'}
						{block name='form-fields-flag'}
							{include file='module:marketplace/views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl'}
						{/block}
					</label>
					<div class="row">
						<div class="col-md-12">
							{foreach from=$languages item=language}
								{assign var="meta_title" value="meta_title_`$language.id_lang`"}
								<div id="meta_title_div_{$language.id_lang}" class="meta_title_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<input type="text"
									name="meta_title_{$language.id_lang}"
									id="meta_title_{$language.id_lang}"
									class="form-control"
									maxlength="128"
									value="{if isset($smarty.post.$meta_title)}{$smarty.post.$meta_title}{else}{if isset($manufId)}{$manufacInfo.meta_title[{$language.id_lang}]}{/if}{/if}">
								</div>
							{/foreach}
				  		</div>
					</div>
				</div>
				<div class="form-group">
					<label for="meta_desc" class="control-label">
						{l s='Meta description' mod='marketplace'}
						{block name='form-fields-flag'}
							{include file='module:marketplace/views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl'}
						{/block}
					</label>
					<div class="row">
						<div class="col-md-12">
							{foreach from=$languages item=language}
								{assign var="meta_desc" value="meta_desc_`$language.id_lang`"}
								<div id="meta_desc_div_{$language.id_lang}" class="meta_desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<input type="text"
									name="meta_desc_{$language.id_lang}"
									id="meta_desc_{$language.id_lang}"
									class="form-control"
									maxlength="255"
									value="{if isset($smarty.post.$meta_desc)}{$smarty.post.$meta_desc}{else}{if isset($manufId)}{$manufacInfo.meta_description[{$language.id_lang}]}{/if}{/if}">
								</div>
							{/foreach}
				  		</div>
					</div>
				</div>
				<div class="form-group">
					<label for="meta_key" class="control-label">
						{l s='Meta keywords' mod='marketplace'}
						<div class="wk_tooltip">
							<span class="wk_tooltiptext">{l s='To add "tags", click inside the field, write something, and then press "Enter".' mod='marketplace'}</span>
						</div>
						{block name='form-fields-flag'}
							{include file='module:marketplace/views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl'}
						{/block}
					</label>
					<div class="row">
						<div class="col-md-12">
							{foreach from=$languages item=language}
								{assign var="meta_key" value="meta_key_`$language.id_lang`"}
								<div id="meta_key_div_{$language.id_lang}" class="meta_desc_div_all wktag_container" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<input type="text"
									name="meta_key_{$language.id_lang}"
									id="meta_key_{$language.id_lang}"
									class="form-control"
									value="{if isset($smarty.post.$meta_key)}{$smarty.post.$meta_key}{else}{if isset($manufId)}{$manufacInfo.meta_keywords[{$language.id_lang}]}{/if}{/if}">
								</div>
							{/foreach}
				  		</div>
					</div>
				</div>
				<div class="form-group">
					<label for="manuf_phone">{l s='Phone' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" name="manuf_phone" id="update_phone" value="{if isset($smarty.post.manuf_phone)}{$smarty.post.manuf_phone}{else}{if isset($manufId)}{$manufacInfo.phone}{/if}{/if}" maxlength="32">
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="manuf_address" class="control-label required">{l s='Address' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" name="manuf_address" id="manuf_address" value="{if isset($smarty.post.manuf_address)}{$smarty.post.manuf_address}{else}{if isset($manufId)}{$manufacInfo.address}{/if}{/if}">
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="manuf_zipcode" class="control-label">{l s='ZipCode' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" id="manuf_zipcode" name="manuf_zipcode" value="{if isset($smarty.post.manuf_zipcode)}{$smarty.post.manuf_zipcode}{else}{if isset($manufId)}{$manufacInfo.zipcode}{/if}{/if}" maxlength="12">
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="manuf_city" class="control-label required">{l s='City' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" name="manuf_city" id="manuf_city" value="{if isset($smarty.post.manuf_city)}{$smarty.post.manuf_city}{else}{if isset($manufId)}{$manufacInfo.city}{/if}{/if}" maxlength="64">
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="manuf_country" class="control-label required">{l s='Country' mod='marketplace'}</label>
					<select name="manuf_country" id="manufcountry" class="form-control" style="width: 250px !important;">
						{foreach $countryinfo as $country}
							<option value="{$country.id_country}" {if isset($smarty.post.manuf_country)}{if $smarty.post.manuf_country == $country.id_country}selected="selected"{/if}{else}{if isset($manufId)}{if $manufacInfo.country == $country.id_country}selected="selected"{/if}{/if}{/if}>{$country.name}</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group divsuppstate" style="display:none;">
					<label for="manuf_state">{l s='State' mod='marketplace'}</label>
					<select name="manuf_state" id="manufstate" class="form-control" style="width: 250px !important;">
					</select>
					<input type="hidden" id="suppstate_temp" name="suppstate_temp" {if isset($manufId)} value="{$manufacInfo.state}" {else} value="0" {/if} />
				</div>
				<div class="required form-group" id='dni_required'>
					<label for="dni" class="control-label required">{l s='DNI' mod='marketplace'}</label>
					<input type="text" class="form-control" placeholder="{l s='DNI' mod='marketplace'}" name="dni" id="dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{else}{if isset($manufacInfo.dni)}{$manufacInfo.dni}{/if}{/if}" />
				</div>
				<div class="form-group">
					<label class="control-label">{l s='Select products to add to this brand:' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							{if isset($productList)}
								<select class="form-control" name="selected_products[]" multiple="multiple">
									{foreach $productList as $product}
										<option {if isset($product['assigned']) && $product['assigned']}style="color:#b2b2b2;" disabled{/if} value="{$product.id_mp_product}">{$product.product_name}{if isset($product['assigned']) && $product['assigned']} {l s='(already assigned)' mod='marketplace'}{/if}</option>
									{/foreach}
								</select>
								{if empty($manufId)}
									<div class="help-block">{l s='Brand will be assigned on selected products only if the brand will be active. You can add this temporarily.' mod='marketplace'}</div>
								{else}
									{if !$manufacInfo.active}
										<div class="help-block">{l s='Brand will be assigned on selected products only if the brand will be active. You can add this temporarily.' mod='marketplace'}</div>
									{/if}
								{/if}
							{else}
								<div class="alert alert-info">
									{l s='Either brand is inactive or there is no active products or all products are associated with brand.' mod='marketplace'}
								</div>
							{/if}
						</div>
					</div>
				</div>
				{hook h="DisplayMpmanufactureraddfooterhook"}
				<div class="form-group row" style="display:flex;justify-content:space-between">
					<div class="col-xs-4 col-sm-4 col-md-3">
						<a href="{$link->getModuleLink('marketplace', 'mpmanufacturerlist')|escape:'htmlall':'UTF-8'}" class="btn wk_btn_cancel wk_btn_extra">
							{l s='Cancel' mod='marketplace'}
						</a>
					</div>
					<div class="col-xs-8 col-sm-8 col-md-9 wk_text_right" data-action="{l s='Save' mod='marketplace'}">
						<img class="wk_product_loader" src="{$smarty.const._MODULE_DIR_}marketplace/views/img/loader.gif" width="25" />
						<button type="submit" id="submitStay_manufacturer" name="submitStay_manufacturer" class="btn btn-success wk_btn_extra form-control-submit">
							<span>{l s='Save & Stay' mod='marketplace'}</span>
						</button>
						<button type="submit" id="submit_manufacturer" name="submit_manufacturer" class="btn btn-success wk_btn_extra form-control-submit">
							<span>{l s='Save' mod='marketplace'}</span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
{/block}