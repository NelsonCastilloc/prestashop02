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

/*New view for category management functionality on front Seller*/

{extends file=$layout}
{block name='content'}
	{if isset($smarty.get.created_conf)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Created Successfully' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_conf)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Updated Successfully' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_withdeactive)}
		<p class="alert alert-info">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Product has been updated successfully but it has been deactivated. Please wait till the approval from admin.' mod='marketplace'}
		</p>
	{else if isset($smarty.get.deleted)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Deleted Successfully' mod='marketplace'}
		</p>
	{else if isset($smarty.get.status_updated)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Status updated Successfully' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_qty) && isset($smarty.get.edited_price)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Only Quantity and Price have been updated successfully. You do not have permission to edit other fields.' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_qty)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Only Quantity has been updated successfully. You do not have permission to edit other fields.' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_price)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Only Price has been updated successfully. You do not have permission to edit other fields.' mod='marketplace'}
		</p>
	{else if isset($smarty.get.error)}
		<p class="alert alert-danger">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='There is some error.' mod='marketplace'}
		</p>
	{else if isset($smarty.get.pack_permission_error)}
		<p class="alert alert-danger">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='You do not have permission to edit pack products.' mod='marketplace'}
		</p>
	{else if isset($smarty.get.virtual_permission_error)}
		<p class="alert alert-danger">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='You do not have permission to edit virtual products.' mod='marketplace'}
		</p>
	{/if}
	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">{l s='product category' mod='marketplace'}</span>
			</div>
			<div class="wk-mp-right-column">
				<div class="wk_product_list">
					<p class="wk_text_right">
						{if $add_permission}
							<a title="{l s='Add product' mod='marketplace'}"
								href="{$link->getModuleLink('marketplace', 'addproduct')}">
								<button class="btn btn-primary btn-sm" type="button">
									<i class="material-icons">&#xE145;</i>
									{l s='Add category' mod='marketplace'}
								</button>
							</a>
							{hook h="displayMpProductListTop"}
						{/if}
						{if Configuration::get('WK_MP_SELLER_EXPORT')}
							<a title="{l s='Export' mod='marketplace'}" href="javascript:;">
								<button class="btn btn-primary btn-sm wk_product_export_button" type="button">
									<i class="material-icons">file_download</i>
									{l s='Export' mod='marketplace'}
								</button>
							</a>
						{/if}
					</p>
					{block name='mpproduct_export'}
						{include file="module:marketplace/views/templates/front/product/_partials/mpproductexport.tpl"}
					{/block}
					
                    <form action="{$link->getModuleLink('marketplace', productlist)}" method="post"
						id="mp_productlist_form">
						<input type="hidden" name="token" id="wk-static-token" value="{$static_token}">
						<table class="table table-striped" id="mp_product_list">
							<thead>
								<tr>
									<th>{l s='ID' mod='marketplace'}</th>
									<th>{l s='Name' mod='marketplace'}</th>
									<th>{l s='Description' mod='marketplace'}</th>
									<th>{l s='Actions' mod='marketplace'}</th>
								</tr>
							</thead>
							<tbody>
								{if $category_list != 0}
									{foreach $category_list as $key => $cat}
										<tr>
											<td>{$cat.id}</td>
											<td><b>{l s=$cat.name mod='marketplace'}</b></td>
											<td>{l s=$cat.description mod='marketplace'}</td>
											<td>
												<a title="{l s='Edit' mod='marketplace'}"
													href="{$link->getModuleLink('marketplace', 'updatecategory', ['id_mp_category' => $cat.id])}">
													<i class="material-icons">&#xE254;</i>
												</a>
												<a title="{l s='Delete' mod='marketplace'}"
													href="{$link->getModuleLink('marketplace', 'updatecategory', ['id_mp_category' => $cat.id, 'deletecategory' => 1])}"
													class="delete_mp_product">
													<i class="material-icons">&#xE872;</i>
												</a>
											</td>
										</tr>
									{/foreach}
								{/if}
							</tbody>
						</table>
						
					</form>
				</div>
			</div>
		</div>
		<div class="left full">
			{hook h="displayMpProductListFooter"}
		</div>

		{block name='mp_image_preview'}
			{include file='module:marketplace/views/templates/front/product/_partials/mp-image-preview.tpl'}
		{/block}
	</div>
{/block}