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

{if $allow_multilang && $total_languages > 1}
	<div class="form-group row">
		<div class="col-md-7">
			<label class="control-label">{l s='Choose Language' mod='marketplace'}</label>
			<input type="hidden" name="choosedLangId" id="choosedLangId" value="{$current_lang.id_lang|escape:'html':'UTF-8'}">

			<button type="button" id="manufacturers_lang_btn" class="btn btn-default dropdown-toggle wk_language_toggle" data-toggle="dropdown">
				{$current_lang.name|escape:'html':'UTF-8'}
				<span class="caret"></span>
			</button>

			<ul class="dropdown-menu wk_language_menu">
				{foreach from=$languages item=language}
					<li>
						<a href="javascript:void(0)" onclick="showSupplierLangField('{$language.name|escape:'html':'UTF-8'}', {$language.id_lang|escape:'html':'UTF-8'});">
							{$language.name|escape:'html':'UTF-8'}
						</a>
					</li>
				{/foreach}
			</ul>
			<p class="wk_formfield_comment">{l s='Change language for updating information in multiple language.' mod='marketplace'}</p>
		</div>
	</div>
{/if}