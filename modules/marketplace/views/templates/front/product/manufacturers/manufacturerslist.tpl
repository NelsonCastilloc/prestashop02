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

	{if isset($smarty.get.createmanuf)}
		<div class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Brand created successfully' mod='marketplace'}
		</div>
	{else}
		{if isset($smarty.get.updatemanuf)}
			<div class="alert alert-success">
				<button data-dismiss="alert" class="close" type="button">×</button>
				{l s='Brand updated successfully' mod='marketplace'}
			</div>
		{/if}
	{/if}
	<div class="alert alert-success" id="deletemanufajax" style="display:none;">
		<button data-dismiss="alert" class="close" type="button">×</button>
		{l s='Deleted Successfully' mod='marketplace'}
	</div>

	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">{l s='Brands' mod='marketplace'}</span>
			</div>
			<div class="wk-mp-right-column">
				<p class="wk_btn_add_product wk_product_list">
					<a title="{l s='Add Brand' mod='marketplace'}"
						href="{$link->getModuleLink('marketplace', 'mpcreatemanufacturers')}">
						<button type="button" class="btn btn-primary btn-sm">
							<i class="material-icons">&#xE145;</i>
							{l s='Add brand' mod='marketplace'}
						</button>
					</a>
				</p>
				<div class="table-responsive">
					<table class="table table-striped" {if isset($manufinfo) && $manufinfo}id="mp_manufacturer_list" {/if}>
						<thead>
							<tr>
								{if isset($manufinfo) && $manufinfo|is_array}
									{if $manufinfo|@count > 1}
										<th class="no-sort"><input type="checkbox" title="{l s='Select all' mod='marketplace'}"
												id="mp_all_manf" /></th>
									{/if}
								{/if}
								<th>{l s='ID' mod='marketplace'}</th>
								<th>{l s='Logo' mod='marketplace'}</th>
								<th>{l s='Name' mod='marketplace'}</th>
								<th>{l s='Products' mod='marketplace'}</th>
								<th>{l s='Status' mod='marketplace'}</th>
								<th>{l s='Action' mod='marketplace'}</th>
							</tr>
						</thead>
						<tbody>
							{if isset($manufinfo) && $manufinfo}
								{assign var="i" value="1"}
								{foreach $manufinfo as $data}

									<tr id="manufid_{$data.id}">
										{if isset($manufinfo) && $manufinfo|is_array}
											{if $manufinfo|@count > 1}<td><input type="checkbox" name="mp_manufacturer_selected[]"
													class="mp_bulk_select" value="{$data.id}" /></td>{/if}
										{/if}

										<td>{$data.id}</td>
										<td><img src="{$data.image}" class="img-thumbnail" width="45" height="45" /></td>
										<td>
											<a title="{l s='View' mod='marketplace'}"
												href="{$link->getModuleLink('marketplace', 'manufacturerproductlist',['mp_manuf_id' => $data.id])}">
												<u>{$data.name}</u>
											</a>
										</td>
										<td>{$data.product_num}</td>
										<td>
											{if ($data.active)}
												<span class="wk_product_approved">{l s='Approved' mod='marketplace'}</span>
											{else}
												<span class="wk_product_pending">{l s='Pending' mod='marketplace'}</span>
											{/if}
										</td>
										<td>
											<a title="{l s='Edit' mod='marketplace'}"
												href="{$link->getModuleLink('marketplace', 'mpcreatemanufacturers', ['id' => $data.id])}">
												<i class="material-icons">&#xE254;</i>
											</a>
											<a delmanufid="{$data.id}" title="{l s='Delete' mod='marketplace'}"
												class="delete_manuf_data" style="color:#2fb5d2;">
												<i class="material-icons">&#xE872;</i>
											</a>
										</td>
									</tr>
									{assign var="i" value=$i+1}
								{/foreach}
							{else}
								<tr>
									<td colspan="6">{l s='No brand yet.' mod='marketplace'}</td>
								</tr>
							{/if}
						</tbody>
					</table>

					{if isset($manufinfo) &&$manufinfo|is_array}
						{if $manufinfo|@count > 1}
							<div class="btn-group dropup">
								<button class="btn btn-default btn-sm dropdown-toggle wk_language_toggle" type="button"
									data-toggle="dropdown" aria-expanded="false">
									{l s='Bulk actions' mod='marketplace'} <span class="caret"></span>
								</button>
								<ul class="dropdown-menu wk_bulk_actions" role="menu">
									<li>
										<a href="" class="mp_bulk_manufacturer_delete_btn">
											<i class="material-icons">&#xE872;</i> {l s='Delete selected' mod='marketplace'}
										</a>
									</li>
								</ul>
							</div>
						{/if}
						<br>
						<br>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/block}