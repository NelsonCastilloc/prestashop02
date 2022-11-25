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

<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			{if isset($displayCancelIcon)}
        		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			{/if}
        	<h4 class="modal-title" id="myModalLabel">{l s='Image' mod='marketplace'}</h4>
		</div>
		<div class="modal-body wk-productlist-images">
			<div class="table-responsive">
				<table id="imageTable" class="table table-hover {if isset($image_detail) && $image_detail && isset($edit_permission) && $edit_permission}mp-active-image-table{/if}">
					<thead>
						<tr>
							<th><center>{l s='Image Id' mod='marketplace'}</center></th>
							<th><center>{l s='Image' mod='marketplace'}</center></th>
							{if isset($edit_permission) && $edit_permission}
								<th><center>{l s='Caption' mod='marketplace'}</center></th>
								<th><center>{l s='Position' mod='marketplace'}</center></th>
								<th><center>{l s='Cover' mod='marketplace'}</center></th>
								<th><center>{l s='Action' mod='marketplace'}</center></th>
							{/if}
						</tr>
					</thead>
					{if isset($image_detail) && $image_detail}
						<tbody>
							{foreach $image_detail as $image}
								<tr class="jFiler-items imageinforow{$image.id_image}" id="mp_image_{$image.id_image}" id_mp_product="{$id_mp_product}" id_image="{$image.id_image}" id_image_position="{$image.position}">
									<td><center>{$image.id_image}</center></td>
									<td><center>
										<a class="mp-img-preview" href="{$image.image_link}">
											<img class="img-thumbnail" width="80" height="80" src="{$image.image_link}" />
										</a>
										</center>
									</td>
									{if isset($edit_permission) && $edit_permission}
										<td><center>
											{foreach $languages as $lang}
												<span class="textlegend{$image.id_image} wk_text_field_all wk_text_field_{$lang.id_lang} {if $lang.id_lang != $current_lang.id_lang}wk_display_none{/if}" >
													{if $image.legend[$lang.id_lang]}
														{$image.legend[$lang.id_lang]}
													{else}
														--
													{/if}
												</span>
											{/foreach}
											{if isset($backendController) || Configuration::get('WK_MP_PRODUCT_IMAGE_CAPTION')}
												&emsp;<a href="javascripit:;"><span class="material-icons edit_legend" id="editlegend{$image.id_image}" alt="{l s='edit' mod='marketplace'}" src="{$mp_image_dir}icon/icon-pencil.png" id_mp_product="{$id_mp_product}" id_image="{$image.id_image}">
												&#xE254;</span></a>
												<div class="row wk_display_none" id="legendForm{$image.id_image}">
													<div class="col-sm-10">
														{foreach $languages as $lang}
															<div class="wk_text_field_all wk_text_field_{$lang.id_lang} {if $lang.id_lang != $current_lang.id_lang}wk_display_none{/if}">
																<input type="text" value="{$image.legend[$lang.id_lang]}" class="changelegend_{$image.id_image}_{$lang.id_lang}">
															</div>
														{/foreach}
														</div>
													<div class="col-sm-2">
														{block name='mp-form-fields-flag'}
															{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
														{/block}
													</div>
													<div class="col-sm-12">
														&emsp; <a href="javascripit:;"><img class="save_legend" id="savelegend{$image.id_image}" alt="{l s='save' mod='marketplace'}" src="{$mp_image_dir}icon/icon-check.png" id_mp_product="{$id_mp_product}" id_image="{$image.id_image}"/></a>
														&emsp; <a href="javascripit:;"><img class="cancel_legend" id="cancellegend{$image.id_image}" alt="{l s='cancel' mod='marketplace'}" src="{$mp_image_dir}icon/icon-close.png" id_mp_product="{$id_mp_product}" id_image="{$image.id_image}"/></a>
													</div>
												</div>
											{/if}
											</center>
										</td>
										<td><center>{$image.position}</center></td>
										<td><center>
											{if $image.cover == 1 }
												<img class="covered" id="changecoverimage{$image.id_image}" alt="{$image.id_image}" src="{$mp_image_dir}icon/icon-check.png" is_cover="1" id_mp_product="{$id_mp_product}"/>
											{else}
												<img class="covered" id="changecoverimage{$image.id_image}" alt="{$image.id_image}" src="{$mp_image_dir}forbbiden.gif" is_cover="0" id_mp_product="{$id_mp_product}" style="cursor:pointer" />
											{/if}
											</center>
										</td>
										<td><center>
											{if $image.cover == 1}
												<a class="delete_pro_image pull-left btn btn-default" href="" is_cover="1" id_mp_product="{$id_mp_product}" id_image="{$image.id_image}">
													<i class="material-icons">&#xE872;</i>
												</a>
											{else}
												<a class="delete_pro_image pull-left btn btn-default" href="" is_cover="0" id_mp_product="{$id_mp_product}" id_image="{$image.id_image}">
													<i class="material-icons">&#xE872;</i>
												</a>
											{/if}
											</center>
										</td>
									{/if}
								</tr>
							{/foreach}
						</tbody>
					{else}
						<tbody>
							<tr>
								<td colspan="6">{l s='No image available' mod='marketplace'}</td>
							</tr>
						</tbody>
					{/if}
				</table>
			</div>
		</div>
	</div>
</div>
