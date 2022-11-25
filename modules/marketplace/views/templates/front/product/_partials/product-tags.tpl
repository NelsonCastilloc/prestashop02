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

{if isset($backendController) || Configuration::get('WK_MP_PRODUCT_TAGS')}
	<hr>
	<div class="form-group">
		<label for="tag" class="control-label {if isset($backendController)}col-lg-3{/if}">
			{l s='Tags' mod='marketplace'}
			<div class="wk_tooltip">
				<span class="wk_tooltiptext">
					{l s='Each tag has to be followed by a comma. The following characters are forbidden: !<;>;?=+#"°{}_$%.' mod='marketplace'}
				</span>
			</div>
			{if $allow_multilang && $total_languages > 1}
				<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang|escape:'htmlall':'UTF-8'}"
					src="{$ps_img_dir|escape:'htmlall':'UTF-8'}{$current_lang.id_lang|escape:'htmlall':'UTF-8'}.jpg">
			{/if}
		</label>
		<div class="row {if isset($backendController)}input-group col-lg-6{/if}">
			<div class="col-md-12">
				{foreach from=$languages item=language}
					{assign var="tag" value="tag_`$language.id_lang`"}
					<div class="tag_container wk_text_field_all wk_text_field_{$language.id_lang|escape:'htmlall':'UTF-8'} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}"
						style="{if isset($backendController)}border:none;{/if}">
						<input type="text" name="tag_{$language.id_lang|escape:'htmlall':'UTF-8'}"
							id="tag_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="form-control"
							value="{if isset($productTag[{$language.id_lang|escape:'htmlall':'UTF-8'}])}{$productTag[{$language.id_lang|escape:'htmlall':'UTF-8'}]|escape:'htmlall':'UTF-8'}{/if}">
					</div>
				{/foreach}
			</div>
		</div>
	</div>
{/if}