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

{if isset($assignmpproduct)}
	{if isset($mp_sellers)}
		{if isset($ps_products) && $ps_products}
			<form id="wk_mp_seller_assign_product_form" method="post"
				action="{$current}&{if !empty($submit_action)}{$submit_action}{/if}&token={$token}&assignmpproduct=1"
				class="defaultForm form-horizontal {$name_controller}" enctype="multipart/form-data"
				name="wk_mp_seller_assign_product_form">
				<div class="panel">
					<div class="panel-heading">
						<i class="icon-user"></i> {l s='Assign product' mod='marketplace'}
					</div>
					<div class="form-wrapper">
						<div class="form-group">
							<label class="control-label col-lg-3 required">
								<span>{l s='Select seller' mod='marketplace'}</span>
							</label>
							<div class="col-lg-3">
								<select name="id_customer">
									{foreach $mp_sellers as $seller}
										<option value="{$seller.id_customer}">
											{$seller.business_email}
											{if isset($all_shop) && $all_shop && isset($seller.ps_shop_name)}
												({$seller.ps_shop_name})
											{/if}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-3 required">
								<span>{l s='Select product' mod='marketplace'}</span>
							</label>
							<div class="col-lg-3">
								<select class="chosen" name="id_product[]" multiple style="height:112px;">
									{foreach $ps_products as $product}
										<option value="{$product.id_product}">{$product.name} ({$product.id_product})</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<a href="{$link->getAdminLink('AdminSellerProductDetail')}" class="btn btn-default"><i
								class="process-icon-cancel"></i> {l s='Cancel' mod='marketplace'}</a>
						<button type="submit" name="submitAdd{$table}" class="btn btn-default pull-right wk-prod-assign"><i
								class="process-icon-save"></i> {l s='Assign' mod='marketplace'}</button>
						<button type="submit" name="submitAdd{$table}AndAssignStay"
							class="btn btn-default pull-right wk-prod-assign">
							<i class="process-icon-save"></i> {l s='Assign and stay' mod='marketplace'}
						</button>
					</div>
				</div>
			</form>
		{else}
			<div class="alert alert-danger">
				{l s='No products available for assign' mod='marketplace'}
			</div>
		{/if}
	{else}
		<div class="alert alert-danger">
			{l s='No seller found' mod='marketplace'}
		</div>
	{/if}
{else}
	<div class="panel">
		<div class="panel-heading">
			{if isset($edit)}
				{l s='Edit product' mod='marketplace'}
			{else}
				{l s='Add new product' mod='marketplace'}
			{/if}
		</div>
		<form name="mp_admin_saveas_button" id="mp_admin_saveas_button"
			class="defaultForm {$name_controller} form-horizontal"
			action="{if isset($edit)}{$current}&update{$table}&id_mp_product={$product_info.id_mp_product}&token={$token}{else}{$current}&add{$table}&token={$token}{/if}"
			method="post" enctype="multipart/form-data" {if isset($style)}style="{$style}" {/if}>

			{hook h='displayMpAddProductHeader'}
			<div class="form-group">
				<div class="col-lg-6">
					{if !isset($edit)}
						<div class="form-group">
							<label class="control-label pull-left required">
								{l s='Choose seller' mod='marketplace'}&nbsp;
							</label>
							{if isset($customer_info)}
								<select name="shop_customer" id="wk_shop_customer" class="fixed-width-xl pull-left">
									{foreach $customer_info as $cusinfo}
										<option value="{$cusinfo.id_customer}">
											{$cusinfo.business_email}
											{if isset($all_shop) && $all_shop && isset($cusinfo.ps_shop_name)}
												({$cusinfo.ps_shop_name})
											{/if}
										</option>
									{/foreach}
								</select>
							{else}
								<p>{l s='No seller found.' mod='marketplace'}</p>
							{/if}
						</div>
					{/if}
					{if $multi_lang}
						<div class="form-group">
							<label class="control-label">
								&nbsp;&nbsp;{l s='Seller default language -' mod='marketplace'}
								<span id="seller_default_lang_div">{$current_lang.name}</label>
							</label>
						</div>
					{/if}
				</div>
				{if $allow_multilang && $total_languages > 1}
					<div class="col-lg-6">
						<label class="control-label">{l s='Choose language' mod='marketplace'}</label>
						<input type="hidden" name="choosedLangId" id="choosedLangId" value="{$current_lang.id_lang}">
						<button type="button" id="seller_lang_btn" class="btn btn-default dropdown-toggle wk_language_toggle"
							data-toggle="dropdown">
							{$current_lang.name}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu wk_language_menu" style="left:14%;top:32px;">
							{foreach from=$languages item=language}
								<li>
									<a href="javascript:void(0)"
										onclick="showProdLangField('{$language.name}', {$language.id_lang});">
										{$language.name}
									</a>
								</li>
							{/foreach}
						</ul>
						<p class="help-block">
							{l s='Change language for updating information in multiple language.' mod='marketplace'}</p>
					</div>
				{/if}
			</div>

			<input type="hidden" name="active_tab" value="{if isset($active_tab)}{$active_tab}{/if}" id="active_tab">
			<input type="hidden" value="{if isset($edit)}{$product_info.id_mp_product}{/if}" name="id" id="mp_product_id" />
			<input type="hidden" name="seller_default_lang" id="seller_default_lang" value="{$current_lang.id_lang}">
			<div class="alert alert-danger wk_display_none" id="wk_mp_form_error"></div>
			<div class="tabs wk-tabs-panel">
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#wk-information" data-toggle="tab">
							<i class="icon-info-sign"></i>
							{l s='Information' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-images" data-toggle="tab">
							<i class="icon-image"></i>
							{l s='Images' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-combination" data-toggle="tab">
							<i class="icon-cubes"></i>
							{l s='Combination' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-feature" data-toggle="tab">
							<i class="icon-star"></i>
							{l s='Features' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-product-shipping" data-toggle="tab">
							<i class="icon-truck"></i>
							{l s='Shipping' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-seo" data-toggle="tab">
							<i class="icon-star-empty"></i>
							{l s='SEO' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-options" data-toggle="tab">
							<i class="icon-list"></i>
							{l s='Options' mod='marketplace'}
						</a>
					</li>
					{hook h='displayMpProductNavTab'}
				</ul>
				<div class="tab-content panel collapse in">
					<div class="tab-pane active" id="wk-information">
						{if isset($edit)}
							{hook h='displayMpUpdateProductContentTop'}
						{else}
							{hook h='displayMpAddProductContentTop'}
						{/if}
						{if (isset($backendController) || Configuration::get('WK_MP_PACK_PRODUCTS') || Configuration::get('WK_MP_VIRTUAL_PRODUCT'))}
							<div class="form-group">
								<div class="row">
									<div class="col-sm-3">
										<label class="control-label required pull-right">
											{l s='Product type :' mod='marketplace'}
										</label>
									</div>
									<div class="col-sm-9">
										<div class="row">
											<div class="col-sm-12">
												<label class="control-label">
													<input type="radio" name="product_type" class="product_type" value="1"
														{if isset($product_info.product_type) && $product_info.product_type == 'standard'}checked{else}checked{/if}>
													{l s='Standard product' mod='marketplace'}
												</label>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-12">
												<label class="control-label">
													<input type="radio" name="product_type" class="product_type" value="2"
														{if (isset($product_info.is_pack_product) && ($product_info.is_pack_product == '1'))}checked
														{else if isset($smarty.post.product_type) && ($smarty.post.product_type == 2)}checked
														{/if}
														{if (isset($is_pack_item) && ($is_pack_item==1)) || (isset($combi_exist) && $combi_exist == 1)}disabled="disabled"
														{/if}>
													{l s='Pack of existing products' mod='marketplace'}
												</label>
											</div>
										</div>

										<div class="row">
											<div class="col-sm-12">
												<label class="control-label">
													<input type="radio" name="product_type" class="product_type" value="3"
														{if isset($product_info.is_virtual) && $product_info.is_virtual == '1'}checked{else if isset($smarty.post.product_type) && ($smarty.post.product_type == 3)}checked{/if}
														{if isset($combi_exist) && $combi_exist == 1}disabled="disabled" {/if}>
													{l s='Virtual product (services, booking, downloadable products, etc.)' mod='marketplace'}
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						{/if}
						<div class="form-group">
							<label for="product_name" class="col-lg-3 control-label required">
								{l s='Product name' mod='marketplace'}
								{include file="$wkself/../../views/templates/front/_partials/mp-form-fields-flag.tpl"}
							</label>
							<div class="col-lg-6">
								{foreach from=$languages item=language}
									{assign var="product_name" value="product_name_`$language.id_lang`"}
									<input type="text" id="product_name_{$language.id_lang}"
										name="product_name_{$language.id_lang}"
										value="{if isset($smarty.post.$product_name)}{$smarty.post.$product_name|escape:'htmlall':'UTF-8'}{elseif isset($edit)}{$product_info.name[{$language.id_lang}]|escape:'htmlall':'UTF-8'}{/if}"
										class="form-control product_name_all wk_text_field_all wk_text_field_{$language.id_lang}"
										maxlength="128" {if $current_lang.id_lang != $language.id_lang}style="display:none;"
										{/if} />
								{/foreach}
							</div>
						</div>
						{if !isset($edit)}
							{hook h="displayMpAddProductNameBottom"}
						{else}
							{hook h="DisplayMpUpdateProductNameBottom"}
						{/if}
						<div class="row pkprod_container">
							<input type="hidden" name="current_lang_id" id="current_lang_id"
								value="{$current_lang.id_lang}">
							<div class="col-sm-12">
								<div class="form-group">
									<label class="col-sm-3 text-right">{l s='Select Pack Products' mod='marketplace'}
										<img class="wk_pack_product_loader"
											src="{$module_dir}marketplace/views/img/loader.gif" width="20" />
									</label>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label"
										for="selectproduct">{l s='Product :' mod='marketplace'}</label>
									<div class="col-sm-6">
										<input class="form-control" type="text" name="selectproduct" id="selectproduct"
											data-value="" data-img="" autocomplete="off">
										<p class="help-block">
											{l s='Start by typing the first letter of the product name, then select the product from the drop-down list.' mod='marketplace'}
										</p>
										<div class="row no_margin sug_container">
											<ul id="sugpkprod_ul" style="top: -25px;"></ul>
										</div>
									</div>
								</div>

								<div class="form-group">
									<label class="col-sm-3 control-label"
										for="packproductquant">{l s='Quantity :' mod='marketplace'}</label>
									<div class="col-sm-6">
										<div class="input-group">
											<span class="input-group-addon">x</span>
											<input class="form-control" type="text" name="quant" id="packproductquant"
												value="1" autocomplete="off">
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="col-sm-offset-3 col-sm-9">
										<button class="btn btn-info" id="addpackprodbut">
											<span> <i class="icon-plus-sign-alt"></i>
												{l s='Add this product to the pack' mod='marketplace'} </span>
										</button>
									</div>
								</div>

								<div class="form-group">
									<div class="col-sm-offset-3 col-sm-9">
										<div class="row no_margin pkprodlist">
											{if isset($isPackProduct)}
												{foreach from=$mpPackProducts key=k item=mpPackProduct}
													<div class="col-sm-4 col-xs-12">
														<div class="row no_margin pk_sug_prod"
															ps_prod_id="{$mpPackProduct->id_ps_product}"
															ps_id_prod_attr="{$mpPackProduct->ps_prod_attr_id}">
															<div class="col-sm-12 col-xs-12">
																<img class="img-responsive pk_sug_img"
																	src="{$mpPackProduct->image_link}">
																<p class="text-center">{$mpPackProduct->product_name}</p>
																{if $mpPackProduct->product_ref != ''}
																	<p class="text-center">{l s='REF:' mod='marketplace'}
																		{$mpPackProduct->product_ref}</p>
																{/if}
																<span class="pull-left">x{$mpPackProduct->quantity}</span>
																<a class="pull-right dltpkprod">
																	<i class="material-icons">&#xE872;</i></a>
																<input type="hidden" class="pspk_id_prod" name="pspk_id_prod[]"
																	value="{$mpPackProduct->id_ps_product}">
																<input type="hidden" name="pspk_prod_quant[]"
																	value="{$mpPackProduct->quantity}">
																<input type='hidden' class='pspk_id_prod_attr'
																	name='pspk_id_prod_attr[]'
																	value="{$mpPackProduct->ps_prod_attr_id}">
															</div>
														</div>
													</div>
												{/foreach}
											{/if}
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<label for="pack_qty_mgmt" class="col-lg-3 control-label">
											{l s='Pack quantities' mod='marketplace'}
										</label>
										<div class="col-lg-6">
											<select name="pack_qty_mgmt" id="pack_qty_mgmt"
												class="form-control form-control-select">
												<option value="0"
													{if isset($product_stock_type) && $product_stock_type == 0}selected{/if}>
													{l s='Decrement pack only.' mod='marketplace'}</option>
												<option value="1"
													{if isset($product_stock_type) && $product_stock_type == 1}selected{/if}>
													{l s='Decrement products in pack only.' mod='marketplace'}</option>
												<option value="2"
													{if isset($product_stock_type) && $product_stock_type == 2}selected{/if}>
													{l s='Decrement both.' mod='marketplace'}</option>
												<option value="3"
													{if !isset($product_stock_type) || $product_stock_type == 3} selected
													{/if}>
													{l s='Default:' mod='marketplace'}
													{if isset($pack_stock_type) && $pack_stock_type == 0}
														{l s='Decrement pack only.' mod='marketplace'}
													{elseif isset($pack_stock_type) && $pack_stock_type == 1}
														{l s='Decrement products in pack only.' mod='marketplace'}
													{else}
														{l s='Decrement both.' mod='marketplace'}
													{/if}
												</option>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="short_description" class="col-lg-3 control-label">
								{l s='Short description' mod='marketplace'}
								{include file="$wkself/../../views/templates/front/_partials/mp-form-fields-flag.tpl"}
							</label>
							<div class="col-lg-6">
								{foreach from=$languages item=language}
									{assign var="short_desc_name" value="short_description_`$language.id_lang`"}
									<div id="short_desc_div_{$language.id_lang}"
										class="wk_text_field_all wk_text_field_{$language.id_lang}"
										{if $current_lang.id_lang != $language.id_lang}style="display:none;" {/if}>
										<textarea name="short_description_{$language.id_lang}"
											id="short_description_{$language.id_lang}" cols="2" rows="3"
											class="wk_tinymce form-control">{if isset($smarty.post.$short_desc_name)}{$smarty.post.$short_desc_name}{elseif isset($edit)}{$product_info.description_short[{$language.id_lang}]}{/if}</textarea>
									</div>
								{/foreach}
							</div>
						</div>
						<div class="form-group">
							<label for="product_description" class="col-lg-3 control-label">
								{l s='Description' mod='marketplace'}
								{include file="$wkself/../../views/templates/front/_partials/mp-form-fields-flag.tpl"}
							</label>
							<div class="col-lg-6">
								{foreach from=$languages item=language}
									{assign var="description" value="description_`$language.id_lang`"}
									<div id="product_desc_div_{$language.id_lang}"
										class="wk_text_field_all wk_text_field_{$language.id_lang}"
										{if $current_lang.id_lang != $language.id_lang}style="display:none;" {/if}>
										<textarea name="description_{$language.id_lang}" id="description_{$language.id_lang}"
											cols="2" rows="3"
											class="wk_tinymce form-control">{if isset($smarty.post.$description)}{$smarty.post.$description}{elseif isset($edit)}{$product_info.description[{$language.id_lang}]}{/if}</textarea>
									</div>
								{/foreach}
							</div>
						</div>
						<div class="form-group">
							<label for="reference" class="col-lg-3 control-label">
								{l s='Reference code' mod='marketplace'}
								<div class="wk_tooltip">
									<span
										class="wk_tooltiptext">{l s='Your internal reference code for this product. Allowed max 32 character. Allowed special characters' mod='marketplace'}:.-_#.</span>
								</div>
							</label>
							<div class="col-lg-6">
								<input type="text" class="form-control" name="reference" id="reference"
									value="{if isset($smarty.post.reference)}{$smarty.post.reference}{else if isset($edit)}{$product_info.reference}{/if}"
									maxlength="32" />
							</div>
						</div>
						<div class="form-group">
							<label for="condition" class="control-label col-lg-3">
								{l s='Condition' mod='marketplace'}
								<div class="wk_tooltip">
									<span
										class="wk_tooltiptext">{l s='This option enables you to indicate the condition of the product.' mod='marketplace'}</span>
								</div>
							</label>
							<div class="col-lg-4">
								<div>
									<select class="form-control" name="condition" id="condition">
										<option value="new"
											{if isset($edit)}
												{if $product_info.condition == 'new'}Selected="Selected"
													{/if}{else}{if isset($smarty.post.condition)}{if $smarty.post.condition == 'new'}Selected="Selected"
												{/if}
											{/if}
										{/if}>
										{l s='New' mod='marketplace'}
									</option>
									<option value="used"
										{if isset($edit)}
											{if $product_info.condition == 'used'}Selected="Selected"
												{/if}{else}{if isset($smarty.post.condition)}{if $smarty.post.condition == 'used'}Selected="Selected"
											{/if}
										{/if}
										{/if}>
										{l s='Used' mod='marketplace'}
									</option>
									<option value="refurbished"
										{if isset($edit)}
											{if $product_info.condition == 'refurbished'}Selected="Selected"
												{/if}{else}{if isset($smarty.post.condition)}{if $smarty.post.condition == 'refurbished'}Selected="Selected"
											{/if}
										{/if}
										{/if}>
										{l s='Refurbished' mod='marketplace'}
									</option>
								</select>
							</div>
							<div class="checkbox">
								<label for="show_condition">
									<input type="checkbox" name="show_condition" id="show_condition" value="1"
										{if isset($edit)}
											{if $product_info.show_condition == '1'}checked="checked"
												{/if}{else}{if isset($smarty.post.show_condition)}{if $smarty.post.show_condition == '1'}checked="checked"
											{/if}
										{/if}
										{/if} />
									<span>{l s='Display condition on product page' mod='marketplace'}</span>
								</label>
							</div>
						</div>
					</div>
					{if !isset($edit)}
						<div class="form-group">
							<label class="col-lg-3 control-label">{l s='Enable product' mod='marketplace'}</label>
							<div class="col-lg-6">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" checked="checked" value="1" id="product_active_on"
										name="product_active">
									<label for="product_active_on">{l s='Yes' mod='marketplace'}</label>
									<input type="radio" value="0" id="product_active_off" name="product_active">
									<label for="product_active_off">{l s='No' mod='marketplace'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>
					{/if}
					{* Product quantity section *}
					{include file="$wkself/../../views/templates/front/product/_partials/product-quantity.tpl"}
					<div class="form-group">
						<label class="col-lg-3 control-label required" for="product_category">
							{l s='Category' mod='marketplace'}
							<div class="wk_tooltip">
								<span
									class="wk_tooltiptext">{l s='Where should the product be available on your site? The main category is where the product appears by default: this is the category which is seen in the product page\'s URL.' mod='marketplace'}</span>
							</div>
						</label>
						<div class="col-lg-6">
							<div id="categorycontainer"></div>
							<input type="hidden" name="product_category" id="product_category"
								value="{if isset($catIdsJoin)}{$catIdsJoin}{/if}" />
						</div>
					</div>
					<div class="form-group" id="default_category_div">
						<label class="col-lg-3 control-label required" for="default_category">
							{l s='Main category' mod='marketplace'}
						</label>
						<div class="col-lg-4">
							<select name="default_category" class="form-control" id="default_category">
								{if isset($defaultCategory)}
									{foreach $defaultCategory as $defaultCategoryVal}
										<option id="default_cat{$defaultCategoryVal.id_category}"
											value="{$defaultCategoryVal.id_category}"
											{if isset($defaultIdCategory)}
												{if $defaultIdCategory == $defaultCategoryVal.id_category}
												selected {/if}
											{/if}>
											{$defaultCategoryVal.name}
										</option>
									{/foreach}
								{else}
									<option id="default_cat2" value="2">Home</option>
								{/if}
							</select>
						</div>
					</div>
					{include file="$wkself/../../views/templates/front/product/_partials/product-pricing.tpl"}
					<div class="row vir_container">
						<div class="row col-lg-offset-2">
							<h4 class="col-md-12">
								{l s='Virtual product' mod='marketplace'}</h4>
						</div>
						<div class="col-sm-12">
							<div class="form-group">
								<label class="col-sm-3 control-label required"
									for="mp_vrt_prod">{l s='Upload file:' mod='marketplace'}
									<div class="wk_tooltip">
										<span
											class="wk_tooltiptext">{l s='Upload a file from your computer' mod='marketplace'}
											({Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')|string_format:'%.2f'}
											{l s='MB max.' mod='marketplace'})</span>
									</div>
								</label>
								<div class="col-sm-6">
									<div class="mp_virtual_upload-btn-wrapper">
										<button class="mp_vitual_btnr">{l s='Choose file' mod='marketplace'}</button>
										<span class="mp_vrt_prod_name">{l s='No file selected' mod='marketplace'}</span>
										<input type="file" name="mp_vrt_prod_file" id="mp_vrt_prod"
											style="display:none" />
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"
									for="mp_vrt_prod_name">{l s='File name:' mod='marketplace'}
									<div class="wk_tooltip">
										<span
											class="wk_tooltiptext">{l s='The full filename with its extension (e.g. Book.pdf)' mod='marketplace'}</span>
									</div>
								</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" name="mp_vrt_prod_name"
										id="mp_vrt_prod_name"
										{if isset($is_virtual_prod)}value="{$is_virtual_prod['display_filename']}"
										{/if}>
								</div>
							</div>
							{if isset($is_virtual_prod) && isset($attach_file_exist) && isset($id) && isset($showTab)}
								<div class="form-group">
									<div class="col-sm-6 col-sm-offset-3">
										<a href="{$link->getModuleLink('marketplace', 'downloadFile',['id_value' => $id, 'admin' => 1])}"
											class="btn btn-default">
											<i class="icon-download"></i>
											{l s='download file' mod='marketplace'}
										</a>
										<input type="hidden" name="mp_id_prod" id="mp_id_prod" value="{$id}">
										<span class="btn btn-default deletefile">
											<i class="icon-trash"></i>
											{l s='Delete this file' mod='marketplace'}
										</span>
									</div>
								</div>
							{/if}
							<div class="form-group">
								<label class="col-sm-3 control-label" for="mp_vrt_prod_nb_downloable">
									{l s='Number of allowed downloads' mod='marketplace'}
									<div class="wk_tooltip">
										<span
											class="wk_tooltiptext">{l s='Number of downloads allowed per customer. (Set to 0 for unlimited downloads)' mod='marketplace'}</span>
									</div>
								</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" name="mp_vrt_prod_nb_downloable"
										id="mp_vrt_prod_nb_downloable"
										value="{if isset($is_virtual_prod)}{$is_virtual_prod['nb_downloadable']}{else}0{/if}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" for="mp_vrt_prod_expdate">
									{l s='Expiration date' mod='marketplace'}
									<div class="wk_tooltip">
										<span
											class="wk_tooltiptext">{l s='If set, the file will not be downloadable after this date. Leave blank if you do not wish to attach an expiration date.' mod='marketplace'}</span>
									</div>
								</label>
								<div class="col-sm-6">
									<input type="text" autocomplete="off" class="datepicker form-control"
										name="mp_vrt_prod_expdate" placeholder="YYYY-MM-DD" id="mp_vrt_prod_expdate"
										{if isset($is_virtual_prod)}value="{$is_virtual_prod['date_expiration']|date_format:"Y-m-d"}"
										{/if}>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" for="mp_vrt_prod_nb_days">
									{l s='Number of days' mod='marketplace'}
									<div class="wk_tooltip">
										<span
											class="wk_tooltiptext">{l s='Number of days this file can be accessed by customers - (Set to zero for unlimited access.)' mod='marketplace'}</span>
									</div>
								</label>
								<div class="col-sm-6">
									<input type="text" class="form-control" name="mp_vrt_prod_nb_days"
										id="mp_vrt_prod_nb_days"
										value="{if isset($is_virtual_prod)}{$is_virtual_prod['nb_days_accessible']}{else}0{/if}">
								</div>
							</div>
						</div>
					</div>

					{include file='module:marketplace/views/templates/front/product/_partials/product-specific-rule.tpl'}
					{include file='module:marketplace/views/templates/front/product/manufacturers/product_manufacturers_list.tpl'}
					{include file='module:marketplace/views/templates/front/product/_partials/related_product.tpl'}

					{if !isset($edit)}
						{hook h="displayMpAddProductFooter"}
					{else}
						{hook h="displayMpUpdateProductFooter"}
					{/if}
				</div>
				<div class="tab-pane" id="wk-images">
					{if isset($edit)}
						<div class="form-group">
							<div class="wk_upload_product_image">
								<input type="file" name="productimages[]" class="uploadimg_container"
									data-jfiler-name="productimg">
							</div>
						</div>
						{include file="$wkself/../../views/templates/front/product/imageedit.tpl"}
					{else}
						<div class="alert alert-danger">
							{l s='You must save this product before adding images.' mod='marketplace'}
						</div>
					{/if}
				</div>
				<div class="tab-pane fade in" id="wk-combination">
					{if isset($edit)}
						{include file="$wkself/../../views/templates/front/product/_partials/product-combination.tpl"}
					{else}
						<div class="alert alert-danger">
							{l s='You must save this product before adding combination.' mod='marketplace'}
						</div>
					{/if}
				</div>
				<div class="tab-pane fade in" id="wk-feature">
					{include file="$wkself/../../views/templates/front/product/_partials/product-feature.tpl"}
				</div>
				<div class="tab-pane fade in" id="wk-product-shipping">
					{include file="$wkself/../../views/templates/front/product/_partials/product-shipping.tpl"}
				</div>
				<div class="tab-pane fade in" id="wk-seo">
					{include file="$wkself/../../views/templates/front/product/_partials/product-seo.tpl"}
				</div>
				<div class="tab-pane" id="wk-options">
					<div class="form-group">
						<label for="reference" class="col-lg-3 control-label">
							{l s='EAN-13 or JAN barcode' mod='marketplace'}
							<div class="wk_tooltip">
								<span
									class="wk_tooltiptext">{l s='Allowed max 13 character. This type of product code is specific to Europe and Japan, but is widely used internationally. It is a superset of the UPC code: all products marked with an EAN will be accepted in North America.' mod='marketplace'}</span>
							</div>
						</label>
						<div class="col-lg-6">
							<input type="text" class="form-control" name="ean13" id="ean13"
								value="{if isset($smarty.post.ean13)}{$smarty.post.ean13}{else if isset($edit)}{$product_info.ean13}{/if}"
								maxlength="13" />
						</div>
					</div>
					<div class="form-group">
						<label for="reference" class="col-lg-3 control-label">
							{l s='UPC Barcode' mod='marketplace'}
							<div class="wk_tooltip">
								<span
									class="wk_tooltiptext">{l s='Allowed max 12 character. This type of product code is widely used in the United States, Canada, the United Kingdom, Australia, New Zealand and in other countries.' mod='marketplace'}</span>
							</div>
						</label>
						<div class="col-lg-6">
							<input type="text" class="form-control" name="upc" id="upc"
								value="{if isset($smarty.post.upc)}{$smarty.post.upc}{else if isset($edit)}{$product_info.upc}{/if}"
								maxlength="12" />
						</div>
					</div>
					<div class="form-group">
						<label for="reference" class="col-lg-3 control-label">
							{l s='ISBN' mod='marketplace'}
							<div class="wk_tooltip">
								<span
									class="wk_tooltiptext">{l s='Allowed max 13 character. This type of code is widely used internationally to identify books and their various editions' mod='marketplace'}</span>
							</div>
						</label>
						<div class="col-lg-6">
							<input type="text" class="form-control" name="isbn" id="isbn"
								value="{if isset($smarty.post.isbn)}{$smarty.post.isbn}{else if isset($edit)}{$product_info.isbn}{/if}"
								maxlength="13" />
						</div>
					</div>
					{if !(_PS_VERSION_ < '1.7.7.0')}
						<div class="form-group">
							<label for="reference" class="col-lg-3 control-label">
								{l s='MPN' mod='marketplace'}
								<div class="wk_tooltip">
									<span class="wk_tooltiptext">{l s='MPN is used internationally to identify the Manufacturer Part Number.' mod='marketplace'}</span>
								</div>
							</label>
							<div class="col-lg-6">
								<input type="text" class="form-control" name="mpn" id="mpn"
									value="{if isset($smarty.post.mpn)}{$smarty.post.mpn}{else if isset($edit)}{$product_info.mpn}{/if}"
								/>
							</div>
						</div>
					{/if}
					<!-- Product tags -->
					{include file='module:marketplace/views/templates/front/product/_partials/product-tags.tpl'}

					<!-- Product suppliers -->
					{include file='module:marketplace/views/templates/front/product/suppliers/update_product_suppliers_list.tpl'}

					<!-- Product customization -->
					{include file='module:marketplace/views/templates/front/product/_partials/add_product_customization_form.tpl'}

					<!-- Product attach files -->
					{include file='module:marketplace/views/templates/front/product/_partials/attach_files_form.tpl'}

					<!-- Product visibility options -->
					{include file="$wkself/../../views/templates/front/product/_partials/product-visibility.tpl"}
					{include file="$wkself/../../views/templates/front/product/_partials/product-availability-preferences.tpl"}
				</div>
				{hook h='displayMpProductTabContent'}
			</div>
		</div>
		<div class="panel-footer">
			<a href="{$link->getAdminLink('AdminSellerProductDetail')}" class="btn btn-default">
				<i class="process-icon-cancel"></i>{l s='Cancel' mod='marketplace'}
			</a>
			<button type="submit" name="submitAdd{$table}" class="btn btn-default pull-right" id="mp_admin_save_button">
				<i class="process-icon-save"></i> {l s='Save' mod='marketplace'}
			</button>
			<button type="submit" name="submitAdd{$table}AndStay" class="btn btn-default pull-right"
				id="mp_admin_saveandstay_button">
				<i class="process-icon-save"></i> {l s='Save and stay' mod='marketplace'}
			</button>
		</div>
	</form>
</div>
{/if}

{block name=script}
	<script type="text/javascript">
		$(document).ready(function() {
			tinySetup({
				editor_selector: "wk_tinymce",
				width: 450
			});
		});

		$('.fancybox').fancybox();
	</script>
{/block}

<style type="text/css">
	.price_with_tax {
		font-size: 14px;
		font-weight: bold;
		padding-top: 8px;
	}
</style>

{strip}
	{addJsDef path_sellerproduct = $link->getAdminlink('AdminSellerProductDetail')}
	{addJsDef path_uploader = $link->getAdminlink('AdminSellerProductDetail')}
	{addJsDef ajax_urlpath = $link->getAdminlink('AdminSellerProductDetail')}

	{addJsDef actionpage = 'product'}
	{addJsDef adminupload = 1}
	{addJsDef backend_controller = 1}
	{addJsDef img_module_dir = 1}
	{addJsDef mp_image_dir = $mp_image_dir}
	{addJsDef iso = $iso}
	{addJsDef ad = $ad}
	{addJsDef pathCSS = $smarty.const._THEME_CSS_DIR_}
	{addJsDef multi_lang = $multi_lang}
	{addJsDef deleteaction = 'jFiler-item-trash-action'}

	{if isset($edit)}
		{addJsDef actionIdForUpload = $product_info.id_mp_product}
		{addJsDef defaultIdCategory = $defaultIdCategory}
	{else}
		{addJsDef actionIdForUpload = ''}
		{addJsDef actionIdForUpload = ''}
	{/if}

	{addJsDefL name='drag_drop'}{l s='Drag & drop to upload' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name='or'}{l s='or' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name='pick_img'}{l s='Pick image' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=choosefile}{l s='Choose images' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=choosefiletoupload}{l s='Choose images to upload' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=imagechoosen}{l s='Images were chosen' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=dragdropupload}{l s='Drop file here to upload' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=confirm_delete_msg}{l s='Are you sure want to delete this image?' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=only}{l s='Only' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=imagesallowed}{l s='Images are allowed to be uploaded.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=onlyimagesallowed}{l s='Images are allowed to be uploaded.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=imagetoolarge}{l s='is too large! Please upload image up to' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=imagetoolargeall}{l s='Images you have choosed are too large! Please upload images up to' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=error_msg}{l s='Some error occurs while deleting image' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=req_price}{l s='Product price is required.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=notax_avalaible}{l s='No tax available' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=some_error}{l s='Some error occured.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=Choose}{l s='Choose' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=confirm_delete_combination}{l s='Are you sure you want to delete this combination?' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=noAllowDefaultAttribute}{l s='You can not make deactivated attribute as default attribute.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=not_allow_todeactivate_combination}{l s='You can not deactivate this combination. Atleast one combination must be active.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=req_prod_name}{l s='Product name is required in Default Language -' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=req_catg}{l s='Please select atleast one category.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDef path_addfeature = $link->getAdminlink('AdminSellerProductDetail')}
	{addJsDefL name=choose_value}{l s='Choose a value' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=no_value}{l s='No value found' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=value_missing}{l s='Feature value is missing' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=value_length_err}{l s='Feature value is too long' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=value_name_err}{l s='Feature value is not valid' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=feature_err}{l s='Feature is not selected' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=enabled}{l s='Enabled' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=disabled}{l s='Disabled' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=update_success}{l s='Updated successfully' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=invalid_value}{l s='Invalid value' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=choose_one}{l s='Choose atleast one product' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=confirm_assign_msg}{l s='Are you sure you want to assign selected product(s) to seller?' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=success_msg}{l s='Success' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=error_msg}{l s='Error' js=1 mod='marketplace'}{/addJsDefL}

	{addJsDef ajaxurl_virtual = $link->getModuleLink('marketplace', 'getFile')}
	{addJsDef adminlink = $link->getAdminLink('AdminSellerProductDetail')}
	{addJsDef checkcontroller = 0}
	{addJsDefL name=confirmation_msg}{l s='Are you sure?' js=1 mod='marketplace'}{/addJsDefL}

	{addJsDefL name=prod_err}{l s='Please enter valid product name' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=quant_err}{l s='Please enter valid product quantity' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDef sugpackprod_url = $link->getModuleLink('marketplace', 'suggestpackproducts')}
	{addJsDef module_dir = $module_dir}
	{if isset($id_seller)}
		{addJsDef id_seller = $id_seller|intval}
	{/if}
	{if isset($mp_id_prod)}
		{addJsDef mp_pk_id_prod = $mp_id_prod|intval}
	{/if}
{/strip}