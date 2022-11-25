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

{if isset($wk_ad_footer)}
	<li>
		<a href="{$sellerLink}">{l s='Become a Seller' mod='marketplace'}</a>
    </li>
{else if isset($wk_ad_nav)}
<div class="mp_advertise">
	<a href="{$sellerLink}">{l s='Become a Seller' mod='marketplace'}</a>
</div>
{else if isset($wk_ad_footer_pop) && !isset($no_advertisement) && !isset($content_only)}
	{if !$cms_content_only}
		<footer class='wk_ad_footer'>
			<div class="box">
				<a class="boxclose" id="wk_ad_close"></a>
				<div class="wk_ad_content">
					<span>{l s='Want to sell products on shop' mod='marketplace'}</span>
					<a class="btn btn-primary btn-sm" href="{$sellerLink}">
						{l s='Become a Seller' mod='marketplace'}
					</a>
				</div>
			</div>
		</footer>
	{/if}
{/if}