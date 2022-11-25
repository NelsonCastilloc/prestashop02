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

{if isset($backendController) || Configuration::get('WK_MP_PRODUCT_MANUFACTURER')}
	<div class="clearfix"></div>
	<div class="form-group">
		<br>
		<h4 class="control-label {if isset($backendController)}col-md-3 text-right{/if}" for="manuf_list">
			{l s='Brand' mod='marketplace'}
		</h4>
		<div class='{if isset($backendController)}col-lg-6{/if}'>
			{if isset($manufacturers)}
				<div class="wk-manuf-add ">
					<select id="product_manufacturer" name="product_manufacturer" class="form-control">
						<option value="">{l s='Choose(Optional)' mod='marketplace'}</option>
						{foreach $manufacturers as $data}
							<option value='{$data.id_manufacturer}'
								{if isset($selected_id_manuf)}{if $data.id_manufacturer == $selected_id_manuf}selected{/if}{/if}>
								{$data.name}
							</option>
						{/foreach}
					</select>
					{if !isset($backendController)}
						<br>
						<a class="btn btn-info" onclick="confirmRedirect();" href="javascript:;">
							{l s='Add brand' mod='marketplace'}
						</a>
					{/if}
				</div>
			{else}
				<div class="{if isset($backendController)}col-md-6{/if}">
					<div class="alert alert-info text-left">{l s='No Brand found' mod='marketplace'}</div>
				</div>
			{/if}
		</div>
	</div>
{/if}