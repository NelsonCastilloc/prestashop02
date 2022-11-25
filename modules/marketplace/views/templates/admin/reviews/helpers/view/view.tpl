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

<div id="row">
	<div class="col-lg-12">
		<div class="panel">
			<div class="panel-heading">
				<i class="icon-info"></i> {l s='Detail Information' mod='marketplace'}
				<div class="panel-heading-action">
					<a href="{$current|escape:'html':'UTF-8'}&amp;viewwk_mp_seller_review&amp;id_review={$id_review|intval}&amp;token={$token|escape:'html':'UTF-8'}" class="btn btn-default">
						<i class="icon-list"></i>
						{l s='Back to list' mod='marketplace'}
					</a>
				</div>
			</div>
			{if isset($customer_name)}
				<p><strong>{l s='Customer name' mod='marketplace'} :  </strong>{$customer_name|escape:'html':'UTF-8'}</p>
				<p><strong>{l s='Customer email' mod='marketplace'} :  </strong>{$review_detail->customer_email|escape:'html':'UTF-8'}</p>
			{else}
				<p><strong>{l s='Customer' mod='marketplace'} :  </strong>{l s='As a guest' mod='marketplace'}</p>
			{/if}

			<p><strong>{l s='Seller name' mod='marketplace'} :  </strong>{$obj_mp_seller->seller_name|escape:'html':'UTF-8'}</p>
			<p><strong>{l s='Seller email' mod='marketplace'} :  </strong>{$obj_mp_seller->business_email|escape:'html':'UTF-8'}</p>
			<p>
				<strong>{l s='Rating' mod='marketplace'} :  </strong>
				{for $foo=1 to $review_detail->rating}
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}marketplace/views/img/star-on.png" />
				{/for}
			</p>
			<p><strong>{l s='Customer review' mod='marketplace'} :  </strong>{$review_detail->review|escape:'html':'UTF-8'}</p>
			<p><strong>{l s='Time' mod='marketplace'} :  </strong>{$review_detail->date_add|escape:'html':'UTF-8'}</p>
		</div>
	</div>
</div>
