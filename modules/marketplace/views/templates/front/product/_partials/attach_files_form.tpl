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

{if isset($backendController) || Configuration::get('WK_MP_PRODUCT_ATTACHMENT')}
	<div id="mp_product_attachment" class="form-group">
		<hr>
		<div class="">
			<h4 class="control-label {if isset($backendController)}col-lg-3{/if}">
				{l s='Attached files' mod='marketplace'}
				<div class="wk_tooltip">
					<span class="wk_tooltiptext">
						{l s='Select the files (instructions, documentation, recipes, etc.) your customers can directly download on this product page.' mod='marketplace'}
					</span>
				</div>
			</h4>
			<div class='{if isset($backendController)}col-lg-6{/if}'>
				<input type="hidden" class="form-control" id="custom_field_count" name="number"
					value="{if isset($customizationFields) && is_array($customizationFields)}{$customizationFields|count}{else}0{/if}">

				{if isset($productAttachments) && $productAttachments}
					<table id="product-attachment-file" class="table">
						<thead class="thead-default">
							<tr>
								<th class="col-md-1">&nbsp;</th>
								<th class="col-md-3">{l s='Title' mod='marketplace'}</th>
								<th class="col-md-6">{l s='File name' mod='marketplace'}</th>
								<th class="col-md-2">{l s='Type' mod='marketplace'}</th>
							</tr>
						</thead>
						<tbody>
							{foreach $productAttachments as $akey => $psAttachment}
								<tr>
									<td class="col-md-1">
										<input type="checkbox" class="checkbox" id="attachments_{$akey}" name="mp_attachments[]"
											value="{$psAttachment.id_attachment}"
											{if isset($psAttachment.selected) && $psAttachment.selected}checked{/if}>
									</td>
									<td class="col-md-3">
										<span class="">{$psAttachment.name}</span>
									</td>
									<td class="col-md-6 file-name"><span>{$psAttachment.file_name}</span></td>
									<td class="col-md-2 wk-attachment-file-type">{$psAttachment.mime}</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				{/if}

				<a href="javascript:;" class="btn btn-info add_mp_attachemnts">
					{l s='Attach a new file' mod='marketplace'}
				</a>
				<div id="mp_attachment_product">
					<div class="form-group">
						<div class="custom-file">
							<input type="file" id="attachment_product_file" name="file" class="custom-file-input">
							<div class="mp_attachment_upload-btn-wrapper">
								<button class="mp_attachment_btnr">{l s='Choose file' mod='marketplace'}</button>
								<span class="mp_attachment_name">{l s='No file selected' mod='marketplace'}</span>
								<input type="file" name="mp_attachment_file" id="mp_attachment" style="display:none" />
							</div>
						</div>
					</div>
					<div class="form-group">
						<input type="text" id="attachment_product_name" name="attachment_product_name" maxlength="32"
							placeholder="{l s='Title' mod='marketplace'}" class="form-control">
					</div>
					<div class="form-group">
						<input type="text" id="attachment_product_description" name="description" maxlength="256"
							placeholder="{l s='Description' mod='marketplace'}"
							aria-label="attachment_product_description input" class="form-control">
					</div>
					<div class="form-group">
						<button type="button" id="attachment_product_add" name="add"
							class="btn-info btn">{l s='Add' mod='marketplace'}</button>
						<button type="button" id="attachment_product_cancel" name="cancel" class="btn-default btn"
							data-toggle="collapse" data-target="#collapsedForm"
							aria-expanded="true">{l s='Cancel' mod='marketplace'}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/if}