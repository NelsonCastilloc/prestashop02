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
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/tinymce/tinymce_wk_setup.js"></script>
<div id="alert_div">
	{if isset($msg_code)}
		{if $msg_code == 1}
			<div class="alert alert-success">{l s='Supplier added successfully.' mod='marketplace'}</div>
		{elseif $msg_code == 2}
			<div class="alert alert-success">{l s='Supplier updated successfully.' mod='marketplace'}</div>
		{/if}
	{/if}
</div>
<div class="wk-mp-block">
	{hook h="displayMpMenu"}
	<div class="wk-mp-content">
		<div class="page-title" style="background-color:{$title_bg_color};">
			<span style="color:{$title_text_color};">
				{if isset($supplierInfo)}
					{l s='Update Supplier' mod='marketplace'}
				{else}
					{l s='Add Supplier' mod='marketplace'}
				{/if}
			</span>
		</div>
		<div class="wk-mp-right-column">
			<form action="{if isset($supplierInfo)}{$link->getModuleLink('marketplace', 'mpupdatesupplier')}{else}{$link->getModuleLink('marketplace', 'mpaddsupplier')}{/if}" id="supplier_form" method="post" enctype="multipart/form-data" accept-charset="UTF-8,ISO-8859-1,UTF-16">
				<input type="hidden" name="current_lang" id="current_lang" value="{$current_lang.id_lang}">
				{block name='change-product-language'}
					{include file='module:marketplace/views/templates/front/product/suppliers/_partials/change-language.tpl'}
				{/block}

				<div class="form-group">
					<label for="suppname" class="control-label required">{l s='Name' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" name="suppname" id="suppname" maxlength="64" {if isset($smarty.post.suppname)} value="{$smarty.post.suppname|stripslashes}" {elseif isset($supplierInfo)} value="{$supplierInfo.name}" {/if}/>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="suppdesc" class="control-label">
						{l s='Description' mod='marketplace'}
						{block name='form-fields-flag'}
							{include file='module:marketplace/views/templates/front/product/suppliers/_partials/supplier-form-fields-flag.tpl'}
						{/block}
					</label>
					<div class="row">
						<div class="col-md-12">
							{foreach from=$languages item=language}
								{assign var="description" value="description_`$language.id_lang`"}
								<div id="desc_div_{$language.id_lang}" class="desc_div_all {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
									<textarea name="description_{$language.id_lang}"
									id="description_{$language.id_lang}" cols="2" rows="3" class="suppdesc wk_tinymce form-control">{if isset($supplierInfo)}{$supplierInfo.description[{$language.id_lang}]}{/if}</textarea>
								</div>
							{/foreach}
				  		</div>
					</div>
				</div>

				<div class="form-group">
					<label for="supplier_logo">{l s='Upload Image' mod='marketplace'}</label>
					{if isset($supplier_image)}
						<br /><img class="img-thumbnail" src="{$supplier_image}" width="150" height="150" style="margin-bottom:5px;" />
					{/if}
					<input type="file" name="supplier_logo" id="supplier_logo" class="form-control" size="chars" />
				</div>


				<div class="form-group">
					<label for="suppmetatitle" class="control-label">
						{l s='Meta title' mod='marketplace'}
						{block name='form-fields-flag'}
							{include file='module:marketplace/views/templates/front/product/suppliers/_partials/supplier-form-fields-flag.tpl'}
						{/block}
					</label>
					<div class="row">
						<div class="col-md-12">
							{foreach from=$languages item=language}
								{assign var="meta_title" value="meta_title_`$language.id_lang`"}
								<div id="meta_title_div_{$language.id_lang}" class="meta_title_div_all {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
									<input type="text"
									name="meta_title_{$language.id_lang}"
									id="meta_title_{$language.id_lang}"
									class="form-control"
									value="{if isset($supplierInfo)}{$supplierInfo.meta_title[{$language.id_lang}]}{/if}">
								</div>
							{/foreach}
				  		</div>
					</div>
				</div>

				<div class="form-group">
					<label for="suppmetadesc" class="control-label">
						{l s='Meta description' mod='marketplace'}
						{block name='form-fields-flag'}
							{include file='module:marketplace/views/templates/front/product/suppliers/_partials/supplier-form-fields-flag.tpl'}
						{/block}
					</label>
					<div class="row">
						<div class="col-md-12">
							{foreach from=$languages item=language}
								{assign var="meta_desc" value="meta_desc_`$language.id_lang`"}
								<div id="meta_desc_div_{$language.id_lang}" class="meta_desc_div_all {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
									<input type="text"
									name="meta_desc_{$language.id_lang}"
									id="meta_desc_{$language.id_lang}"
									class="form-control"
									value="{if isset($supplierInfo)}{$supplierInfo.meta_description[{$language.id_lang}]}{/if}">
								</div>
							{/foreach}
				  		</div>
					</div>
				</div>

				<div class="form-group">
					<label for="suppmetakeywords" class="control-label">
						{l s='Meta keywords' mod='marketplace'}
						{block name='form-fields-flag'}
							{include file='module:marketplace/views/templates/front/product/suppliers/_partials/supplier-form-fields-flag.tpl'}
						{/block}
					</label>
					<div class="row">
						<div class="col-md-12">
							{foreach from=$languages item=language}
								{assign var="meta_key" value="meta_key_`$language.id_lang`"}
								<div id="meta_key_div_{$language.id_lang}" class="meta_desc_div_all wktag_container {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
									<input type="text" name="meta_key_{$language.id_lang}" id="meta_key_{$language.id_lang}" class="form-control" value="{if isset($supplierInfo)}{$supplierInfo.meta_keywords[{$language.id_lang}]}{/if}">
								</div>
							{/foreach}
				  		</div>
					</div>
				    {* <p class="help-block">{l s='Separate by comma for multiple keywords' mod='marketplace'}</p> *}
				</div>
				
				<div class="form-group">
					<label for="suppphone" class="control-label">{l s='Phone' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" name="suppphone" id="suppphone" maxlength="32" {if isset($smarty.post.suppphone)} value="{$smarty.post.suppphone}" {elseif isset($supplierInfo)} value="{$supplierInfo.phone}" {/if}/>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="suppmobile" class="control-label">{l s='Mobile Phone' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" name="suppmobile" id="suppmobile" maxlength="32" {if isset($smarty.post.suppmobile)} value="{$smarty.post.suppmobile}" {elseif isset($supplierInfo)} value="{$supplierInfo.mobile_phone}" {/if}/>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="suppaddress" class="control-label required">{l s='Address' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<textarea name="suppaddress" id="suppaddress" class="form-control" maxlength="128" >{if isset($smarty.post.suppaddress)}{$smarty.post.suppaddress}{elseif isset($supplierInfo)}{$supplierInfo.address}{/if}</textarea>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="suppzip" class="control-label">{l s='Zip/Postal Code' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" name="suppzip" id="suppzip" maxlength="12" {if isset($smarty.post.suppzip)} value="{$smarty.post.suppzip}" {elseif isset($supplierInfo)} value="{$supplierInfo.zip}" {/if}/>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="suppcity" class="control-label required">{l s='City' mod='marketplace'}</label>
					<div class="row">
						<div class="col-md-12">
							<input class="form-control" type="text" name="suppcity" id="suppcity" maxlength="64"{if isset($smarty.post.suppcity)} value="{$smarty.post.suppcity}" {elseif isset($supplierInfo)} value="{$supplierInfo.city}" {/if}/>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label for="supp_country" class="control-label required">{l s='Country' mod='marketplace'}</label>
					<select name="suppcountry" id="suppcountry" class="form-control" style="width: 250px !important;">
						{foreach $countryinfo as $country}
							<option value="{$country.id_country}" {if isset($supplierInfo)}{if $supplierInfo.country == $country.id_country} selected="selected" {/if}{/if}>{$country.name}</option>
						{/foreach}
					</select>
				</div>

				<div class="form-group divsuppstate" style="display:none;">
					<label for="supp_state">{l s='State' mod='marketplace'}</label>
					<select name="suppstate" id="suppstate" class="form-control" style="width: 250px !important;">
					</select>
					<input type="hidden" id="suppstate_temp" name="suppstate_temp" {if isset($supplierInfo)} value="{$supplierInfo.state}" {else} value="0" {/if} />
				</div>

				<div class="required form-group" id='dni_required'>
					<label for="dni" class="control-label required">{l s='DNI' mod='marketplace'}</label>
					<input type="text" class="form-control" placeholder="{l s='DNI' mod='marketplace'}" name="dni" id="dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{else}{if isset($supplierInfo.dni)}{$supplierInfo.dni}{/if}{/if}" />
				</div>

				<input type="hidden" name="id" {if isset($supplierInfo)} value="{$supplierInfo.id_wk_mp_supplier}" {/if} />

				<div class="form-group">
					<label class="control-label">{l s='Select products to add this supplier:' mod='marketplace'}</label>
					{if isset($productList)}
						<select class="form-control" name="selected_products[]" multiple="multiple" size="5">
							{foreach $productList as $product}
								<option value="{$product.id_mp_product}">{$product.product_name}</option>
							{/foreach}
						</select>
					{else}
						<div class="alert alert-info">
							{l s='Either supplier is inactive or there is no active products or all products are associated with supplier.' mod='marketplace'}
						</div>
					{/if}
				</div>

				<div class="form-group">
					<div class="form-group row" style="display:flex;justify-content:space-between">
						<div class="col-xs-4 col-sm-4 col-md-3">
							<a href="{$link->getModuleLink('marketplace', 'mpsupplierlist')|escape:'htmlall':'UTF-8'}" class="btn wk_btn_cancel wk_btn_extra">
								{l s='Cancel' mod='marketplace'}
							</a>
						</div>
						<div class="col-xs-8 col-sm-8 col-md-9 wk_text_right" data-action="{l s='Save' mod='marketplace'}">
							<button type="submit" id="submitStay_supplier" name="submitStay_supplier" class="btn btn-success wk_btn_extra form-control-submit">
								<span>{l s='Save & Stay' mod='marketplace'}</span>
							</button>
							<button type="submit" id="submit_supplier" name="submit_supplier" class="btn btn-success wk_btn_extra form-control-submit">
								<span>{l s='Save' mod='marketplace'}</span>
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
{/block}