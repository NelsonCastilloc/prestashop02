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

{if (isset($backendController)) || Configuration::get('WK_MP_PRODUCT_CUSTOMIZATION')}
	<div id="mp_product_customization" class="form-group">
		<hr>
		<div id="custom_fields">
			<h4 class="control-label {if isset($backendController)}col-lg-3{/if}">{l s='Customization' mod='marketplace'}
				<div class="wk_tooltip">
					<span class="wk_tooltiptext">
						{l s='Customers can personalize the product by entering some text or by providing custom image files.' mod='marketplace'}</span>
				</div>
			</h4>
			<div class='{if isset($backendController)}col-lg-6{/if}'>
				<input type="hidden" class="form-control" id="custom_field_count" name="number"
					value="{if isset($customizationFields) && is_array($customizationFields)}{$customizationFields|count}{else}0{/if}">
				{if isset($customizationFields) && $customizationFields}
					{foreach $customizationFields as $fieldrow => $fields}
						<div class="form-group row">
							<div class="col-md-4">
								<label class="control-label">{l s='Label' mod='marketplace'}
									{block name='mp-form-fields-flag'}
										{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
									{/block}
								</label>

								<input type="hidden" name="custom_fields[{$fieldrow}][id_customization_field]"
									id="custom_fields_{$fieldrow}_id_customization_field" value="{$fieldrow}">
								<div>
									<div class="">
										{foreach from=$languages item=lang}
											{assign var="texttitle" value="textname_`$lang.id_lang`"}
											<div class="wk_text_field_all wk_text_field_{$lang.id_lang}"
												{if $current_lang.id_lang != $lang.id_lang}style="display:none;" {/if}>
												<input type="text" name="custom_fields[{$fieldrow}][label][{$lang.id_lang}]"
													id="custom_fields_{$fieldrow}_label_{$lang.id_lang}" class="form-control"
													value="{$fields[$lang.id_lang]['name']}"
													placeholder="{l s='Field Label' mod='marketplace'}">
											</div>
										{/foreach}
									</div>
								</div>
							</div>

							<div class="col-md-8">

								<div class="col-md-12">
									<label class="control-label">{l s='Type' mod='marketplace'}
									</label>
								</div>
								<div class="col-md-5">
									<select id="custom_fields_{$fieldrow}_type" name="custom_fields[{$fieldrow}][type]"
										class="form-control form-control-select">
										<option value="1" {if $fields[$current_lang.id_lang]['type'] == 1}selected{/if}>
											{l s='Text' mod='marketplace'}</option>
										<option value="0" {if $fields[$current_lang.id_lang]['type'] == 0}selected{/if}>
											{l s='File' mod='marketplace'}</option>
									</select>
								</div>

								<div class="col-md-2">
									<a href="javascript:;" class="btn delete_customization"><i class="material-icons">delete</i></a>
								</div>
								<div class="col-md-5">
									<div class="checkbox">
										<label for="required">
											<input type="checkbox" id="custom_fields_{$fieldrow}_required" value="1"
												{if $fields[$current_lang.id_lang]['required'] == 1}checked{/if}
												name="custom_fields[{$fieldrow}][required]">
											{l s='Required' mod='marketplace'}</label>
									</div>
								</div>
							</div>
						</div>
					{/foreach}

				{/if}

				<a href="javascript:;" class="btn btn-info add_customization_field">
					{l s='Add a customization field' mod='marketplace'}
				</a>
			</div>
		</div>
	</div>
{/if}