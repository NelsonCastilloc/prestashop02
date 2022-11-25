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

<img src="{$mp_module_dir|escape:'htmlall':'UTF-8'}marketplace/views/img/loader.gif" id="ajax_loader" style="position: absolute;left: 50%;top: 40%;z-index: 10000;display: none;" />
<div class="panel">
	<div class="panel-heading">
		<i class="icon-truck"></i>
		{if isset($supplier_info)}
			{l s='Edit supplier' mod='marketplace'}
		{else}
			{l s='Add new supplier' mod='marketplace'}
		{/if}
	</div>
	<form id="supplier_form" class="defaultForm {$name_controller|escape:'htmlall':'UTF-8'} form-horizontal" action="{$current|escape:'htmlall':'UTF-8'}&{if !empty($submit_action)}{$submit_action|escape:'htmlall':'UTF-8'}{/if}&token={$token|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
	<div class="panel-body">
		{assign var=is_form_submit value=1}
		{if !isset($supplier_info)}
			<div class="form-group">
				<label class="col-lg-3 control-label required">{l s='Choose Seller' mod='marketplace'}</label>
				<div class="col-lg-3">
					{if isset($seller_list)}
						<select name="mp_customer_id">
							{foreach $seller_list as $seller}
								<option value="{$seller['seller_customer_id']|escape:'html':'UTF-8'}" {if isset($smarty.post.mp_customer_id)}{if $smarty.post.mp_customer_id == $seller['seller_customer_id']}Selected="Selected"{/if}{/if}>
									{$seller['business_email']}
								</option>
							{/foreach}
						</select>
					{else}
						{assign var=is_form_submit value=0}
						<div class="alert alert-info">{l s='No seller found (you can not add supplier untill seller list is not availble).' mod='marketplace'}</div>
					{/if}
				</div>
			</div>
		{/if}
		{if isset($supplier_info)}
			<input type="hidden" name="id" value="{$supplier_info.id_wk_mp_supplier|escape:'htmlall':'UTF-8'}" />
		{/if}

		<input type="hidden" name="current_lang" id="current_lang" value="{$current_lang.id_lang|escape:'htmlall':'UTF-8'}" />
		{include file="$self/../../views/templates/admin/mp_suppliers/_partials/change-language.tpl"}

		<div class="form-group">
			<label for="suppname" class="col-lg-3 control-label required">{l s='Name' mod='marketplace'}</label>
			<div class="col-lg-7">
				<input class="form-control" type="text" name="suppname" id="suppname" maxlength="64" {if isset($smarty.post.suppname)} value="{$smarty.post.suppname|stripslashes}" {elseif isset($supplier_info)} value="{$supplier_info.name|escape:'htmlall':'UTF-8'}" {/if}/>
			</div>
		</div>

		<div class="form-group">
			<div class="row">
				<label for="suppdesc" class="col-lg-3 control-label">{l s='Description' mod='marketplace'} {include file="$self/../../views/templates/admin/mp_suppliers/_partials/supplier-form-fields-flag.tpl"}</label>
				<div class="col-lg-7">
					{foreach from=$languages item=language}
						{assign var="description" value="description_`$language.id_lang`"}
						<div id="desc_div_{$language.id_lang|escape:'html':'UTF-8'}" class="desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
							<textarea name="description_{$language.id_lang|escape:'html':'UTF-8'}"
							id="description_{$language.id_lang|escape:'html':'UTF-8'}" cols="2" rows="3" class="suppdesc wk_tinymce form-control">{if isset($supplier_info)}{$supplier_info.description[{$language.id_lang|escape:'html':'UTF-8'}]|escape:'htmlall':'UTF-8'}{/if}</textarea>
						</div>
					{/foreach}
		  		</div>
			</div>
		</div>

		<div class="form-group">
			<label for="suppphone" class="col-lg-3 control-label">{l s='Phone' mod='marketplace'}</label>
			<div class="col-lg-5">
				<input class="form-control" type="text" name="suppphone" id="suppphone" maxlength="32" {if isset($smarty.post.suppphone)} value="{$smarty.post.suppphone|escape:'htmlall':'UTF-8'}" {elseif isset($supplier_info)} value="{$supplier_info.phone|escape:'htmlall':'UTF-8'}" {/if}/>
			</div>
		</div>

		<div class="form-group">
			<label for="suppmobile" class="col-lg-3 control-label">{l s='Mobile phone' mod='marketplace'}</label>
			<div class="col-lg-5">
				<input class="form-control" type="text" name="suppmobile" id="suppmobile" maxlength="32" {if isset($smarty.post.suppmobile)} value="{$smarty.post.suppmobile|escape:'htmlall':'UTF-8'}" {elseif isset($supplier_info)} value="{$supplier_info.mobile_phone|escape:'htmlall':'UTF-8'}" {/if}/>
			</div>
		</div>

		<div class="form-group">
			<label for="suppaddress" class="col-lg-3 control-label required">{l s='Address' mod='marketplace'}</label>
			<div class="col-lg-7">
				<textarea name="suppaddress" id="suppaddress" class="form-control" maxlength="128">{if isset($smarty.post.suppaddress)}{$smarty.post.suppaddress|escape:'htmlall':'UTF-8'}{elseif isset($supplier_info)}{$supplier_info.address|escape:'htmlall':'UTF-8'}{/if}</textarea>
			</div>
		</div>

		<div class="form-group">
			<label for="suppzip" class="col-lg-3 control-label">{l s='Zip/Postal Code' mod='marketplace'}</label>
			<div class="col-lg-7">
				<input class="form-control" type="text" name="suppzip" id="suppzip" maxlength="12" {if isset($smarty.post.suppzip)} value="{$smarty.post.suppzip|escape:'htmlall':'UTF-8'}" {elseif isset($supplier_info)} value="{$supplier_info.zip|escape:'htmlall':'UTF-8'}" {/if}/>
			</div>
		</div>

		<div class="form-group">
			<label for="suppcity" class="col-lg-3 control-label required">{l s='City' mod='marketplace'}</label>
			<div class="col-lg-7">
				<input class="form-control" type="text" name="suppcity" id="suppcity" maxlength="64" {if isset($smarty.post.suppcity)} value="{$smarty.post.suppcity|escape:'htmlall':'UTF-8'}" {elseif isset($supplier_info)} value="{$supplier_info.city|escape:'htmlall':'UTF-8'}" {/if}/>
			</div>
		</div>

		<div class="form-group">
			<label for="suppcountry" class="col-lg-3 control-label required">{l s='Country' mod='marketplace'}</label>
			<div class="col-lg-7">
				<select name="suppcountry" id="suppcountry" class="form-control" style="width: 250px !important;">
					{foreach $countryinfo as $country}
						<option value="{$country.id_country|escape:'htmlall':'UTF-8'}" {if isset($supplier_info)}{if $supplier_info.country == $country.id_country} selected="selected" {/if}{/if}>{$country.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</div>
		</div>

		<div class="form-group divsuppstate" style="display:none;">
			<label for="supp_state" class="col-lg-3 control-label">{l s='State' mod='marketplace'}</label>
			<div class="col-lg-7">
				<select name="suppstate" id="suppstate" class="form-control" style="width: 250px !important;">
				</select>
				<input type="hidden" id="suppstate_temp" name="suppstate_temp" {if isset($supplier_info)} value="{$supplier_info.state|escape:'htmlall':'UTF-8'}" {else} value="0" {/if} />
			</div>
		</div>

		<div class="form-group" id='dni_required'>
			<div class="row">
				<label for="dni" class="col-lg-3 text-right control-label required">{l s='DNI' mod='marketplace'}</label>
				<div class="col-lg-7">
					<input type="text" class="form-control" placeholder="{l s='DNI' mod='marketplace'}" name="dni" id="dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{else}{if isset($supplier_info.dni)}{$supplier_info.dni}{/if}{/if}" />
				</div>
			</div>
		</div>

		<div class="form-group">
			<div class="row">
				<label for="suppmetatitle" class="col-lg-3 control-label">{l s='Meta title' mod='marketplace'} {include file="$self/../../views/templates/admin/mp_suppliers/_partials/supplier-form-fields-flag.tpl"}</label>
				<div class="col-lg-7">
					{foreach from=$languages item=language}
						{assign var="meta_title" value="meta_title_`$language.id_lang`"}
						<div id="meta_title_div_{$language.id_lang|escape:'html':'UTF-8'}" class="meta_title_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
							<input type="text"
							name="meta_title_{$language.id_lang|escape:'html':'UTF-8'}"
							id="meta_title_{$language.id_lang|escape:'html':'UTF-8'}"
							class="form-control"
							value="{if isset($supplier_info)}{$supplier_info.meta_title[{$language.id_lang|escape:'html':'UTF-8'}]|escape:'htmlall':'UTF-8'}{/if}">
						</div>
					{/foreach}
		  		</div>
			</div>
		</div>

		<div class="form-group">
			<div class="row">
				<label for="suppmetadesc" class="col-lg-3 control-label">{l s='Meta description' mod='marketplace'} {include file="$self/../../views/templates/admin/mp_suppliers/_partials/supplier-form-fields-flag.tpl"}</label>
				<div class="col-lg-7">
					{foreach from=$languages item=language}
						{assign var="meta_desc" value="meta_desc_`$language.id_lang`"}
						<div id="meta_desc_div_{$language.id_lang|escape:'html':'UTF-8'}" class="meta_desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
							<input type="text"
							name="meta_desc_{$language.id_lang|escape:'html':'UTF-8'}"
							id="meta_desc_{$language.id_lang|escape:'html':'UTF-8'}"
							class="form-control"
							value="{if isset($supplier_info)}{$supplier_info.meta_description[{$language.id_lang|escape:'html':'UTF-8'}]|escape:'htmlall':'UTF-8'}{/if}">
						</div>
					{/foreach}
		  		</div>
			</div>
		</div>

		<div class="form-group">
			<div class="row">
				<label for="suppmetakeywords" class="col-lg-3 control-label">{l s='Meta keywords' mod='marketplace'} {include file="$self/../../views/templates/admin/mp_suppliers/_partials/supplier-form-fields-flag.tpl"}</label>
				<div class="col-lg-7">
					{foreach from=$languages item=language}
						{assign var="meta_key" value="meta_key_`$language.id_lang`"}
						<div id="meta_key_div_{$language.id_lang|escape:'html':'UTF-8'}" class="meta_desc_div_all" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
							<input type="text"
							name="meta_key_{$language.id_lang|escape:'html':'UTF-8'}"
							id="meta_key_{$language.id_lang|escape:'html':'UTF-8'}"
							placeholder="{l s='Add tag' mod='marketplace'}"
							class="form-control"
							value="{if isset($supplier_info)}{$supplier_info.meta_keywords[{$language.id_lang|escape:'html':'UTF-8'}]|escape:'htmlall':'UTF-8'}{/if}">
						</div>
					{/foreach}
		  		</div>
			</div>
		</div>

		<div class="form-group">
			<label for="supplier_logo" class="col-lg-3 control-label">{l s='Upload image' mod='marketplace'}</label>
			<div class="col-lg-5">
				{if isset($supplier_image)}
					<br /><img class="img-thumbnail" src="{$supplier_image|escape:'htmlall':'UTF-8'}" width="150" height="150" style="margin-bottom:5px;" />
				{/if}
				<input type="file" name="supplier_logo" id="supplier_logo" size="chars" />
			</div>
		</div>

		<div class="form-group">
			<label class="col-lg-3 control-label">{l s='Select products to add this supplier:' mod='marketplace'}</label>
			<div class="col-lg-7">
			{if isset($product_list)}
				<select class="form-control" name="selected_products[]" multiple="multiple" size="5">
					{foreach $product_list as $product}
						<option value="{$product.id_mp_product}">{$product.product_name}</option>
					{/foreach}
				</select>
			{else}
				<div class="alert alert-info">
					{l s='Either supplier is inactive or there is no active products or all products are associated with supplier.' mod='marketplace'}
				</div>
			{/if}
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<a href="{$link->getAdminLink('AdminMpSuppliers')|escape:'html':'UTF-8'}" class="btn btn-default">
			<i class="process-icon-cancel"></i>{l s='Cancel' mod='marketplace'}
		</a>
		<button type="submit" name="submitAdd{$table|escape:'html':'UTF-8'}" class="btn btn-default pull-right" {if $is_form_submit == 0} disabled="disabled" {/if}>
			<i class="process-icon-save"></i>{l s='Save' mod='marketplace'}
		</button>

		<button type="submit" name="submitAdd{$table|escape:'html':'UTF-8'}AndAssignStay" class="btn btn-default pull-right" {if $is_form_submit == 0} disabled="disabled" {/if}>
			<i class="process-icon-save"></i>{if isset($supplier_info)} {l s='Assign and stay' mod='marketplace'} {else}  {l s='Save and stay' mod='marketplace'} {/if}
		</button>
	</div>
	</form>
</div>
<script type="text/javascript">
	var iso = "{$iso|escape:'htmlall':'UTF-8'}";
	var ad = "{$ad|escape:'htmlall':'UTF-8'}";
	var is_front_controller = "{$is_front_controller|escape:'htmlall':'UTF-8'}";
	var pathCSS = "{$smarty.const._THEME_CSS_DIR_|escape:'quotes':'UTF-8'}";
	var supplier_ajax_link = "{$link->getAdminLink('AdminMpSuppliers')|escape:'quotes':'UTF-8'}";
	var is_admin = 1;

	$(document).ready(function(){
		{block name="autoload_tinyMCE"}
			tinySetup({
				editor_selector :"suppdesc",
				width : 700
			});
		{/block}
	});

	$(document).ready(function(){
		$(document).on('change',"select[name='mp_customer_id']",function(){
			var seller_customer_id =$("select[name='mp_customer_id'] option:selected").val();
			getSellerDefaultLangId(seller_customer_id);
		});
	});

	//Find manufacturer on add product page according to seller choose
	function getSellerDefaultLangId(customer_id)
	{
		if (customer_id != '') {
			$.ajax({
				url: "{$link->getAdminLink('AdminSellerProductDetail')|escape:'quotes':'UTF-8'}",
				method:'POST',
				dataType:'json',
				data: {
					customer_id:customer_id,
					action: "findSellerDefaultLang",
		            ajax: "1"
				},
				success:function(data){
					showSupplierLangField(data.name, data.id_lang);
				}
			});
		}
	}


	function showSupplierLangField(lang_iso_code, id_lang)
	{
		$('#supplier_lang_btn').html(lang_iso_code + ' <span class="caret"></span>');

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