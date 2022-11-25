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

<hr>
<div class="row">
	{if isset($backendController)}
		<h4 class="col-lg-6 col-lg-offset-3">{l s='Visibility' mod='marketplace'}</h4>
	{else}
		<h4 class="col-md-12">{l s='Visibility' mod='marketplace'}</h4>
	{/if}
</div>

<div class="form-group {if isset($backendController)}row{/if}">
	<label for='visibility' class="control-label {if isset($backendController)}col-lg-3{/if}">
		{l s='Where do you want your product to appear?' mod='marketplace'}
	</label>
	<div class="row">
		<div class="{if isset($backendController)}col-lg-4{else}col-md-6{/if}">
			<select name='visibility' class="form-control form-control-select">
				<option value='both' {if isset($product_info)}{if $product_info.visibility == 'both'}selected{/if}{/if}>
					{l s='Everywhere' mod='marketplace'}
				</option>
				<option value='catalog' {if isset($product_info)}{if $product_info.visibility == 'catalog'}selected{/if}{/if}>
					{l s='Catalog Only' mod='marketplace'}
				</option>
				<option value='search' {if isset($product_info)}{if $product_info.visibility == 'search'}selected{/if}{/if} >
					{l s='Search Only' mod='marketplace'}
				</option>
				<option value='none' {if isset($product_info)}{if $product_info.visibility == 'none'}selected{/if}{/if} >
					{l s='Nowhere' mod='marketplace'}
				</option>
			</select>
		</div>
	</div>
</div>

<div class="form-group row">
	<div class="{if isset($backendController)}col-lg-6 col-lg-offset-3{else}col-md-6{/if}">
		<div class="checkbox">
			<label for="available_for_order">
				<input type="checkbox" value="1" id="available_for_order" name="available_for_order"
				{if isset($product_info)}{if $product_info.available_for_order =='1'}checked{/if}{else}checked{/if} >
				{l s='Available for order' mod='marketplace'}
			</label>
		</div>
		<div class="checkbox">
			<label for="show_price">
				<input type="checkbox" value="1" id="show_price" name="show_price"
				{if isset($product_info)}{if $product_info.show_price =='1'}checked{/if}{else}checked{/if} >
				{l s='Show price' mod='marketplace'}
			</label>
		</div>
		<div class="checkbox">
			<label for="online_only">
				<input type="checkbox" value="1" id="online_only" name="online_only"
				{if isset($product_info)}{if $product_info.online_only =='1'}checked
				{/if}{/if} >
				{l s='Online only (not sold in your retail store)' mod='marketplace'}
			</label>
		</div>
	</div>
</div>
<hr>