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

<div class="form-group">
	<label for="address" class="control-label">{l s='Address' mod='marketplace'}</label>
	<div id="address_div">
		<textarea
		name="address"
		id="address" cols="2" rows="3"
		class="validate form-control">{if isset($smarty.post.address)}{$smarty.post.address}{else}{$mp_seller_info.address}{/if}</textarea>
	</div>
</div>

<div class="form-group row">
	{if $seller_country_need}
		<div class="col-md-6" id="seller_zipcode">
			<label for="postcode" class="control-label required">
				{l s='Zip/Postal Code' mod='marketplace'}
			</label>
			<input class="form-control"
			type="text"
			value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{else}{$mp_seller_info.postcode}{/if}"
			name="postcode"
			id="postcode" />
		</div>
		<div class="col-md-6">
			<label for="city" class="control-label required">
				{l s='City' mod='marketplace'}
			</label>
			<input class="form-control"
			type="text"
			value="{if isset($smarty.post.city)}{$smarty.post.city}{else}{$mp_seller_info.city}{/if}"
			name="city"
			id="city"
			maxlength="64" />
		</div>
	{/if}
</div>

{if $seller_country_need && isset($country)}
	<div class="form-group row">
		<div class="col-md-6">
			<label for="id_country" class="control-label required">
				{l s='Country' mod='marketplace'}
			</label>
			<select name="id_country" id="id_country" class="form-control form-control-select">
				<option value="">{l s='Select Country' mod='marketplace'}</option>
				{foreach $country as $countrydetail}
					<option value="{$countrydetail.id_country}"
					{if $mp_seller_info.id_country == $countrydetail.id_country}Selected="Selected"{/if}>
						{$countrydetail.name}
					</option>
				{/foreach}
			</select>
		</div>
		<div id="wk_seller_state_div" class="col-md-6 {if !$mp_seller_info['id_state']}wk_display_none{/if}">
			<label for="id_state" class="control-label required">
				{l s='State' mod='marketplace'}
			</label>
			<select name="id_state" id="id_state" class="form-control form-control-select">
				<option value="">{l s='Select State' mod='marketplace'}</option>
			</select>
			<input type="hidden" name="state_available" id="state_available" value="0" />
		</div>
	</div>
{/if}