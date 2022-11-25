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

<div class="box-account box-recent">
	<p class="wk_text_right">
		{if isset($controller) && $controller == 'admin'}
			<a href="{$link->getAdminLink('AdminMpAttributeManage')}&id={$id}">
				<button class="btn btn-info sensitive add" type="button">
					<i class="icon-plus"></i>&nbsp;
					{l s='Create Combination' mod='marketplace'}
				</button>
			</a>
			<a  class="generate_combination" href="{$link->getAdminLink('AdminMpGenerateCombination')}&id_mp_product={if isset($id)}{$id}{elseif isset($mp_id_product)}{$mp_id_product}{/if}">
				<button class="btn btn-info sensitive add" type="button">
					<i class="icon-plus"></i>&nbsp;
					{l s='Generate Combination' mod='marketplace'}
				</button>
			</a>
		{else}
			{if !isset($editPermissionNotAllow) && $permissionData.combinationPermission.add}
				<a href="{$link->getModuleLink('marketplace', 'managecombination', ['id' => $id])}">
					<button class="btn btn-primary-outline sensitive add" type="button">
						<i class="material-icons">&#xE145;</i>
						{l s='Create Combination' mod='marketplace'}
					</button>
				</a>
			{/if}
			{if !isset($editPermissionNotAllow) && $permissionData.combinationPermission.add}
				<a  class="generate_combination" href="{if isset($id)}{$link->getModuleLink('marketplace', 'generatecombination', ['id_mp_product' => $id])}{elseif isset($mp_id_product)}{$link->getModuleLink('marketplace', 'generatecombination', ['id_mp_product' => $mp_id_product])}{/if}">
					<button class="btn btn-primary-outline sensitive add" type="button">
						<i class="material-icons">&#xE145;</i>
						{l s='Generate Combination' mod='marketplace'}
					</button>
				</a>
			{/if}
		{/if}

		{hook h="displayMpCombinationListButton"}
	</p>

	<div class="box-content" id="wk_product_combination">
		<div class="table-responsive clearfix">
		<table class="table clearfix">
			<thead>
				<tr>
					<th style="width:20%;">{l s='Attributes' mod='marketplace'}</th>
					<th><center>{l s='Quantity' mod='marketplace'}</center></th>
					<th><center>{l s='Impact on Price' mod='marketplace'}</center></th>
					<th><center>{l s='Impact on Weight' mod='marketplace'}</center></th>
					<th>{l s='Reference' mod='marketplace'}</th>
					{*--- Hook added for ps combination activate/deactivate module ---*}
					{hook h="displayMpCombinationListBeforeActionButtonColumn"}
					<th>{l s='Actions' mod='marketplace'}</th>
				</tr>
			</thead>
			<tbody>
				{if isset($combination_detail) && $combination_detail}
					{foreach $combination_detail as $wkRowKey => $combination_val}
						<tr id="combination_{$combination_val.id_product_attribute}" class="{if $combination_val.mp_default_on}highlighted{/if} combination">
							<td>{$combination_val.attribute_designation|rtrim:' '|rtrim:','}</td>
							<td>
								<center>
								{if (!isset($editPermissionNotAllow) && $permissionData.combinationPermission.edit) || isset($qtyAllow)}
									<input type="text"
									name="combination_qty_{$combination_val.id_product_attribute}"
									id="combination_qty_{$combination_val.id_product_attribute}"
									value="{$combination_val.mp_quantity}"
									data-id-combination="{$combination_val.id_product_attribute}"
									class="form-control wk-combi-list-qty">
								{else}
									{$combination_val.mp_quantity}
								{/if}
								</center>
							</td>
							<td><center>{$combination_val.mp_price}</center></td>
							<td><center>{$combination_val.mp_weight}{$ps_weight_unit}</center></td>
							<td>{$combination_val.mp_reference}</td>
							{*--- Hook added for ps combination activate/deactivate module ---*}
							{hook h="displayMpCombinationListBeforeActionButton" permissionData=$permissionData idProductAttribute=$combination_val.id_product_attribute}

							{if !isset($backendController)}
								<td>
									<a href="{$link->getModuleLink('marketplace', 'managecombination', ['id_combination' => $combination_val.id_product_attribute])}" title="{l s='Edit' mod='marketplace'}">
										<i class="material-icons">&#xE254;</i>
									</a>
									{if !isset($editPermissionNotAllow) && $permissionData.combinationPermission.delete}
										<a href="" title="{l s='Delete' mod='marketplace'}" class="delete_attribute" data-id="{$combination_val.id_product_attribute}" id="delete_attribute_{$combination_val.id_product_attribute}" data-default={if $combination_val.mp_default_on}1{else}0{/if}>
											<i class="material-icons">&#xE872;</i>
										</a>
									{/if}
									{if !isset($editPermissionNotAllow) && $permissionData.combinationPermission.edit}
										{if $combination_val.mp_default_on}
											<input type="hidden" id="default_product_attribute" value="{$combination_val.id_product_attribute}">
										{/if}
										<a href="" title="{l s='Make Default' mod='marketplace'}" data-controller="front" data-status="{if isset($combination_val.active)}{$combination_val.active}{else}1{/if}" class="default_attribute {if $combination_val.mp_default_on}wk_display_none{/if}" data-id="{$combination_val.id_product_attribute}" id="default_attribute_{$combination_val.id_product_attribute}">
											<i class="material-icons">&#xE838;</i>
										</a>
									{/if}
								</td>
							{else}
								<td class="center text-right">
									<div class="btn-group">
										<a class="btn btn-default" href="{$link->getAdminLink('AdminMpAttributeManage')}&id_combination={$combination_val.id_product_attribute}">
											<i class="icon-edit"></i> {l s='Edit' mod='marketplace'}
										</a>
										{if $combination_val.mp_default_on}
											<input type="hidden" id="default_product_attribute" value="{$combination_val.id_product_attribute}">
										{/if}
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu">
											<li>
												<a href="" class="delete_attribute" data-id="{$combination_val.id_product_attribute}" id="delete_attribute_{$combination_val.id_product_attribute}" data-default={if $combination_val.mp_default_on}1{else}0{/if}>
													<i class="icon-trash"></i>
													{l s='Delete' mod='marketplace'}
												</a>
											</li>
											<li>
												<a data-controller="admin" href="" title="{l s='Default' mod='marketplace'}" class="default_attribute" data-status="{$combination_val.active}" data-id="{$combination_val.id_product_attribute}" id="default_attribute_{$combination_val.id_product_attribute}" {if $combination_val.mp_default_on}style="display:none;"{/if}>
												<i class="icon-star"></i> {l s='Make Default' mod='marketplace'}
												</a>
											</li>
										</ul>
									</div>
								</td>
							{/if}
						</tr>
						<div class="left basciattr_update" id="attribute_div_{$combination_val.id_product_attribute}">
						</div>
					{/foreach}
				{else}
					<tr>
						<td colspan="7">
							<div class="full left planlistcontent call" style="text-align:center;">{l s='No combination available for this product' mod='marketplace'}</div>
						</td>
					</tr>
				{/if}
			</tbody>
		</table>
		</div>
	</div>
</div>