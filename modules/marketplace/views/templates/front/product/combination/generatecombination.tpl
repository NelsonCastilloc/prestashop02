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
{if $logged}
	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">
					{l s='Attribute Generator' mod='marketplace'}
				</span>
			</div>
			<div class="wk-mp-right-column">
				<p style="margin:bottom:25px;">
					<a href="{$link->getModuleLink('marketplace', 'updateproduct',['id_mp_product'=>$id_mp_product])}" class="btn btn-link wk_padding_none">
				        <i class="material-icons">&#xE5C4;</i>
				        <span>{l s='Back to product' mod='marketplace'}</span>
				    </a>
				</p>
				{block name='generate-combination-fields'}
					{include file='module:marketplace/views/templates/front/product/combination/_partials/generate-combination-fields.tpl'}
				{/block}
			</div>
		</div>
	</div>
{else}
	<div class="alert alert-danger">
		{l s='You are logged out. Please login to generate combination.' mod='marketplace'}</span>
	</div>
{/if}
{/block}
