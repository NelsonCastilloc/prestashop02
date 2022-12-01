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

{extends file=$layout}
{block name='content'}
	<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/tinymce/tinymce.min.js"></script>
	<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/tinymce/tinymce_wk_setup.js">
	</script>
	{if $logged}
		<h3>This is the category form!...</h3>
	{else}
		<div class="alert alert-danger">
			{l s='You are logged out. Please login to add product.' mod='marketplace'}</span>
		</div>
	{/if}
{/block}