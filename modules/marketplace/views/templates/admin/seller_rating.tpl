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


{if isset($seller_wise_rating)}
	<span id="seller_wise_rating_{$list.id_review|escape:'htmlall':'UTF-8'}"></span>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#seller_wise_rating_{$list.id_review}').raty({
		        path: "{$rating_start_path|escape:'htmlall':'UTF-8'}",
		        score: "{$sellerRating|escape:'htmlall':'UTF-8'}",
		        readOnly: true,
		    });
		});
	</script>
{else}
	<span id="seller_main_avg_rating_{$list.id_seller|escape:'htmlall':'UTF-8'}"></span>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#seller_main_avg_rating_{$list.id_seller}').raty({
		        path: "{$rating_start_path|escape:'htmlall':'UTF-8'}",
		        score: "{$sellerRating|escape:'htmlall':'UTF-8'}",
		        readOnly: true,
		    });
		});
</script>
{/if}