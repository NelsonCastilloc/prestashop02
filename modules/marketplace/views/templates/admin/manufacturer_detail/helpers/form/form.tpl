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

{if isset($customer_info) || isset($manuf_id)}
	<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/manufacturers/mpcreatemanufacturer.js"></script>
	{* <script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/manufacturers/findsellerproducts.js"></script> *}

	<div class="panel row">
		<div class="panel-heading">
			<i class="icon-star"></i>
			{if isset($manuf_id)}
				{l s='Edit brand' mod='marketplace'}
			{else}
				{l s='Add new brand' mod='marketplace'}
			{/if}
		</div>
		<div class="form-wrapper">
			<form method="post" class="defaultForm {$name_controller|escape:'htmlall':'UTF-8'} form-horizontal" enctype="multipart/form-data" accept-charset="UTF-8,ISO-8859-1,UTF-16">
				<input type="hidden" name="current_lang" id="current_lang" value="{$current_lang.id_lang}">
				<div class="form-group row">
					{if isset($manuf_id)}
						<input type="hidden" name="manuf_id" id="manuf_id" value="{$manuf_id}">
						<input type="hidden" name="manuf_id_seller" id="manuf_id_seller" value="{$manufac_info.id_seller}">
					{else}
						<input type="hidden" name="manuf_id" id="manuf_id" value="0">
						<div class="row">
							<label class="col-lg-3 text-right control-label required">{l s='Choose seller' mod='marketplace'}</label>
							<div class="col-lg-3">
								<select name="shop_customer">
									{foreach $customer_info as $cusinfo}
										<option value="{$cusinfo['id_customer']}" {if isset($smarty.post.shop_customer)}{if $smarty.post.shop_customer == $cusinfo['id_customer']}Selected="Selected"{/if}{/if}>
											{$cusinfo['business_email']}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
					{/if}
				</div>

				{include file="$self/../../views/templates/admin/manufacturer_detail/_partials/change-language.tpl"}

				<div class="form-group">
					<div class="row">
						<label for="manuf_name" class="col-lg-3 text-right control-label required">{l s='Brand name' mod='marketplace'}</label>
						<div class="col-lg-7">
							<input class="form-control" type="text" name="manuf_name" id="manuf_name" value="{if isset($smarty.post.manuf_name)}{$smarty.post.manuf_name}{else}{if isset($manuf_id)}{$manufac_info.name}{/if}{/if}" maxlength="64">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<label for="manuf_short_desc" class="col-lg-3 text-right control-label">{l s='Short description' mod='marketplace'} {include file="$self/../../views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl"}</label>
						<div class="col-lg-7">
							{foreach from=$languages item=language}
								{assign var="short_desc_name" value="short_description_`$language.id_lang`"}
								<div id="short_desc_div_{$language.id_lang}" class="short_desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<textarea name="short_description_{$language.id_lang}" id="short_description_{$language.id_lang}" cols="2" rows="3" class="manufdesc wk_tinymce form-control">{if isset($smarty.post.$short_desc_name)}{$smarty.post.$short_desc_name}{else}{if isset($manuf_id)}{$manufac_info.short_description[{$language.id_lang}]}{/if}{/if}</textarea>
								</div>

							{/foreach}
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<label for="description" class="col-lg-3 text-right control-label">{l s='Description' mod='marketplace'} {include file="$self/../../views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl"}</label>
						<div class="col-lg-7">
							{foreach from=$languages item=language}
								{assign var="description" value="description_`$language.id_lang`"}
								<div id="desc_div_{$language.id_lang}" class="desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<textarea name="description_{$language.id_lang}"
									id="description_{$language.id_lang}" cols="2" rows="3" class="manufdesc wk_tinymce form-control">{if isset($smarty.post.$description)}{$smarty.post.$description}{else}{if isset($manuf_id)}{$manufac_info.description[{$language.id_lang}]}{/if}{/if}</textarea>
								</div>
							{/foreach}
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<label for="manuf_logo" class="col-lg-3 text-right control-label">{l s='Logo' mod='marketplace'}</label>
						<div class="col-lg-7">
							{if isset($imageexist)}
							<div>
								<img class="imgm img-thumbnail" alt="{l s='Brand image' mod='marketplace'}" src="{$modules_dir}marketplace/views/img/mpmanufacturers/{$manuf_id}.jpg">
							</div>
							{/if}
							<input type="file" id="manuf_logo" name="manuf_logo" size="chars">
						</div>
					</div>
				</div>
				{if !isset($manuf_id)}
					<div class="form-group">
						<div class="row">
							<label class="col-lg-3 text-right control-label">{l s='Enable brand' mod='marketplace'}</label>
							<div class="col-lg-6">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" checked="checked" value="1" id="manufacturer_active_on" name="manufacturer_active">
									<label for="manufacturer_active_on">{l s='Yes' mod='marketplace'}</label>
									<input type="radio" value="0" id="manufacturer_active_off" name="manufacturer_active">
									<label for="manufacturer_active_off">{l s='No' mod='marketplace'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>
					</div>
				{/if}
				<div class="form-group">
					<div class="row">
						<label for="meta_title" class="col-lg-3 text-right control-label">{l s='Meta title' mod='marketplace'} {include file="$self/../../views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl"}</label>
						<div class="col-lg-7">
							{foreach from=$languages item=language}
								{assign var="meta_title" value="meta_title_`$language.id_lang`"}
								<div id="meta_title_div_{$language.id_lang}" class="meta_title_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<input type="text"
									name="meta_title_{$language.id_lang}"
									id="meta_title_{$language.id_lang}"
									class="form-control"
									maxlength="128"
									value="{if isset($smarty.post.$meta_title)}{$smarty.post.$meta_title}{else}{if isset($manuf_id)}{$manufac_info.meta_title[{$language.id_lang}]}{/if}{/if}">
								</div>
							{/foreach}
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<label for="meta_desc" class="col-lg-3 text-right control-label">{l s='Meta description' mod='marketplace'} {include file="$self/../../views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl"}</label>
						<div class="col-lg-7">
							{foreach from=$languages item=language}
								{assign var="meta_desc" value="meta_desc_`$language.id_lang`"}
								<div id="meta_desc_div_{$language.id_lang}" class="meta_desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<input type="text"
									name="meta_desc_{$language.id_lang}"
									id="meta_desc_{$language.id_lang}"
									class="form-control"
									maxlength="255"
									value="{if isset($smarty.post.$meta_desc)}{$smarty.post.$meta_desc}{else}{if isset($manuf_id)}{$manufac_info.meta_description[{$language.id_lang}]}{/if}{/if}">
								</div>
							{/foreach}
							{* <div class="help-block">{l s='To enter multiple keywords, you need to seperated with comma(,)' mod='marketplace'}</div> *}
						</div>
					</div>
					{* {foreach $languages as $language}
					{assign var="shortDesc" value="short_description_`$language['id_lang']`"}
					{$smarty.post.$shortDesc}
					{/foreach} *}

				</div>
				<div class="form-group">
					<div class="row">
						<label for="meta_key" class="col-lg-3 text-right control-label">{l s='Meta keywords' mod='marketplace'}
						<div class="wk_tooltip">
							<span class="wk_tooltiptext">{l s='To add "tags", click inside the field, write something, and then press "Enter".' mod='marketplace'}</span>
						</div>{include file="$self/../../views/templates/front/product/manufacturers/_partials/manuf-form-fields-flag.tpl"}</label>
						<div class="col-lg-7">
							{foreach from=$languages item=language}
								{assign var="meta_key" value="meta_key_`$language.id_lang`"}
								<div id="meta_key_div_{$language.id_lang}" class="meta_desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<input type="text"
									name="meta_key_{$language.id_lang}"
									id="meta_key_{$language.id_lang}"
									class="form-control"
									value="{if isset($smarty.post.$meta_key)}{$smarty.post.$meta_key}{else}{if isset($manuf_id)}{$manufac_info.meta_keywords[{$language.id_lang}]}{/if}{/if}">
								</div>
							{/foreach}
							<div class="help-block">{l s='To enter multiple keywords, you need to seperated with comma(,)' mod='marketplace'}</div>
						</div>

					</div>

				</div>
				<div class="form-group">
					<div class="row">
						<label for="manuf_phone" class="col-lg-3 text-right control-label">{l s='Phone' mod='marketplace'}</label>
						<div class="col-lg-7">
							<input class="form-control" type="text" name="manuf_phone" id="update_phone" value="{if isset($smarty.post.manuf_phone)}{$smarty.post.manuf_phone}{else}{if isset($manuf_id)}{$manufac_info.phone}{/if}{/if}" maxlength="32">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<label for="manuf_address" class="col-lg-3 text-right control-label required">{l s='Address' mod='marketplace'}</label>
						<div class="col-lg-7">
							<input class="form-control" type="text" name="manuf_address" id="manuf_address" value="{if isset($smarty.post.manuf_address)}{$smarty.post.manuf_address}{else}{if isset($manuf_id)}{$manufac_info.address}{/if}{/if}">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<label for="manuf_zipcode" class="col-lg-3 text-right control-label">{l s='Zip code' mod='marketplace'}</label>
						<div class="col-lg-7">
							<input class="form-control" type="text" name="manuf_zipcode" id="manuf_zipcode" value="{if isset($smarty.post.manuf_zipcode)}{$smarty.post.manuf_zipcode}{else}{if isset($manuf_id)}{$manufac_info.zipcode}{/if}{/if}" maxlength="12">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<label for="manuf_city" class="col-lg-3 text-right control-label required">{l s='City' mod='marketplace'}</label>
						<div class="col-lg-7">
							<input class="form-control" type="text" name="manuf_city" id="manuf_city" value="{if isset($smarty.post.manuf_city)}{$smarty.post.manuf_city}{else}{if isset($manuf_id)}{$manufac_info.city}{/if}{/if}" maxlength="64">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<label for="manuf_country" class="col-lg-3 text-right control-label required">{l s='Country' mod='marketplace'}</label>
						<div class="col-lg-7">
							<select name="manuf_country" id="manufcountry" class="form-control" style="width: 250px !important;">
								{foreach $countryinfo as $country}
									<option value="{$country.id_country}" {if isset($smarty.post.manufcountry)}{if $smarty.post.manufcountry == $country.id_country}selected="selected"{/if}{else}{if isset($manuf_id)}{if $manufac_info.country == $country.id_country}selected="selected"{/if}{/if}{/if}>{$country.name}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
				<div class="form-group divsuppstate" style="display:none;">
					<div class="row">
						<label for="manuf_state" class="col-lg-3 text-right control-label">{l s='State' mod='marketplace'}</label>
						<div class="col-lg-7">
							<select name="manuf_state" id="manufstate" class="form-control" style="width: 250px !important;">
							</select>
							<input type="hidden" id="suppstate_temp" name="suppstate_temp" {if isset($manuf_id)} value="{$manufac_info.state}" {else} value="0" {/if} />
						</div>
					</div>
				</div>
				<div class="form-group" id='dni_required'>
					<div class="row">
						<label for="dni" class="col-lg-3 text-right control-label required">{l s='DNI' mod='marketplace'}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control" placeholder="{l s='DNI' mod='marketplace'}" name="dni" id="dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{else}{if isset($manufac_info.dni)}{$manufac_info.dni}{/if}{/if}" />
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 text-right control-label">{l s='Select products to add this brand:' mod='marketplace'}</label>
					<div class="row">
						{* {if isset($manuf_id)} *}
							<div class="col-lg-7">
								{if isset($product_list)}
									<select class="form-control" name="selected_products[]" multiple="multiple">
										{foreach $product_list as $product}
											<option {if isset($product['assigned']) && $product['assigned']}style="color:#828282;" disabled{/if} value="{$product.id_mp_product}">{$product.product_name}{if isset($product['assigned']) && $product['assigned']} {l s='(already assigned)' mod='marketplace'}{/if}</option>
										{/foreach}
									</select>
									{if empty($manuf_id)}
										<div class="help-block">{l s='Brand will be assigned on selected products only if the brand will be active. You can add this temporarily.' mod='marketplace'}</div>
									{else}
										{if !$manufac_info.active}
											<div class="help-block">{l s='Brand will be assigned on selected products only if the brand will be active. You can add this temporarily.' mod='marketplace'}</div>
										{/if}
									{/if}
								{else}
									<div class="alert alert-info">
										{l s='Either brand is inactive or there is no active products or all products are associated with brand.' mod='marketplace'}
									</div>
								{/if}
							</div>
						{* {else}
							<div class="col-lg-7" id="loadsellerproduct"></div>
						{/if} *}
					</div>
				</div>
				{hook h="DisplayMpmanufactureraddfooterhook"}

				<div class="panel-footer">
					<a href="{$link->getAdminLink('AdminManufacturerDetail')}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='marketplace'}</a>
					<button type="submit" id="submit_manufacturer" class="btn btn-default pull-right" name="submit_manufacturer">
						<i class="process-icon-save"></i>{l s='Save' mod='marketplace'}
					</button>
					<button type="submit" id="submitStay_manufacturer" class="btn btn-default pull-right" name="submitStay_manufacturer">
						<i class="process-icon-save"></i>{l s='Save and Stay' mod='marketplace'}
					</button>
				</div>
			</form>
		</div>
	</div>

	<script type="text/javascript">
		var iso = "{$iso|escape:'htmlall':'UTF-8'}";
		var ad = "{$ad|escape:'htmlall':'UTF-8'}";
		var pathCSS = "{$smarty.const._THEME_CSS_DIR_|escape:'quotes':'UTF-8'}";
		var is_admin = 1;

		$(document).ready(function(){
			{block name="autoload_tinyMCE"}
				tinySetup({
					editor_selector :"manufdesc",
					width : 700
				});
			{/block}
		});

		$(document).ready(function(){
			$(document).on('change',"select[name='shop_customer']",function(){
				var seller_customer_id =$("select[name='shop_customer'] option:selected").val();
				getSellerDefaultLangId(seller_customer_id);
			});
		});

		//Find Brand on add product page according to seller choose
		function getSellerDefaultLangId(customer_id)
		{
			if (customer_id != '')
			{
				$.ajax({
					url: "{$link->getAdminLink('AdminSellerProductDetail')}",
					method:'POST',
					dataType:'json',
					data: {
						customer_id:customer_id,
						action: "findSellerDefaultLang",
						ajax: "1"
					},
					success:function(data){
						showManufLangField(data.name, data.id_lang);
					}
				});
			}
		}

		function showManufLangField(lang_iso_code, id_lang)
		{
			$('#manufacturers_lang_btn').html(lang_iso_code + ' <span class="caret"></span>');

			$('#short_desc_btn').html(lang_iso_code + ' <span class="caret"></span>');
			$('#desc_btn').html(lang_iso_code + ' <span class="caret"></span>');
			$('#meta_title_btn').html(lang_iso_code + ' <span class="caret"></span>');
			$('#meta_desc_btn').html(lang_iso_code + ' <span class="caret"></span>');
			$('#meta_key_btn').html(lang_iso_code + ' <span class="caret"></span>');

			$('.short_desc_div_all').hide();
			$('#short_desc_div_'+id_lang).show();
			$('.desc_div_all').hide();
			$('#desc_div_'+id_lang).show();
			$('.meta_title_div_all').hide();
			$('#meta_title_div_'+id_lang).show();
			$('.meta_desc_div_all').hide();
			$('#meta_desc_div_'+id_lang).show();
			$('.meta_key_div_all').hide();
			$('#meta_key_div_'+id_lang).show();

			$('.all_lang_icon').attr('src', img_dir_l+id_lang+'.jpg');
	    	$('#choosedLangId').val(id_lang);
		}
	</script>

	{strip}
		{addJsDef manuf_ajax_link = $link->getAdminLink('AdminManufacturerDetail')}
		{addJsDef find_seller_product = $link->getAdminLink('AdminManufacturerDetail')}
		{addJsDefL name='all_associated'}{l s='Either brand is inactive or there is no active products or all products are associated with brand.' js=1 mod='marketplace'}{/addJsDefL}
		{addJsDefL name='temp_manuf'}{l s='Brand will assign on selected products only when product will active. You can assign products temporarily' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='req_manuf_name'}{l s='Brand name is required' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='req_manuf_address'}{l s='Address is required' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='req_manuf_city'}{l s='City is required' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='req_manuf_country'}{l s='Country is required' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='invalid_logo'}{l s='Invalid image extensions, only jpg, jpeg and png are allowed.' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='some_error'}{l s='Some error occured' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='addkeywords'}{l s='Add keywords' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='invalid_address'}{l s='Length must be smaller than 128 character' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='length_exceeds_address'}{l s='Address is not valid' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='invalid_zipcode'}{l s='Zipcode is not valid' mod='marketplace'}{/addJsDefL}
		{addJsDefL name='invalid_city'}{l s='City is not valid' mod='marketplace'}{/addJsDefL}
	{/strip}
{else}
	<div class="alert alert-danger">{l s='No seller found' mod='marketplace'}</div>
{/if}
