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

{if isset($product['customization_data'])}
	<a href="#" data-toggle="modal"
		data-target="#product-customizations-modal-{$product.id_customization}">{l s='Product customization' mod='marketplace'}</a>
	<div class="modal fade customization-modal" id="product-customizations-modal-{$product.id_customization}" tabindex="-1"
		role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"
						aria-label="{l s='Close' mod='marketplace'}">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">{l s='Product customization' mod='marketplace'}</h4>
				</div>
				<div class="modal-body row">
					{foreach $product['customization_data'] as $customizationId => $customization}
						<div class="form-horizontal">
							{if ($customization.type == Product::CUSTOMIZE_FILE)}
								<div class="form-group">
									<span
										class="col-lg-4 control-label"><strong>{if $customization['name']}{$customization['name']}{else}{l s='Picture #' mod='marketplace'}{$customization@iteration}{/if}</strong></span>
									<div class="col-lg-8">
										<a href="{$link->getModuleLink('marketplace','mediadownload', ['ajax' => 1, 'action' => 'customizationImage', 'img' => $customization['value'], 'name' => $order->id|intval|cat:'-file'|cat:$customization@iteration])}"
											class="_blank">
											<img class="img-thumbnail"
												src="{$smarty.const._THEME_PROD_PIC_DIR_|escape:'quotes':'UTF-8'}{$customization['value']}_small"
												alt="" />
										</a>
									</div>
								</div>
							{elseif ($customization.type == Product::CUSTOMIZE_TEXTFIELD)}
								<div class="form-group">
									<span
										class="col-lg-4 control-label"><strong>{if $customization['name']}{l s='%s' sprintf=[$customization['name']] mod='marketplace'}{else}{l s='Text #%s' sprintf=[$customization@iteration] mod='marketplace'}{/if}</strong></span>
									<div class="col-lg-8">
										<p class="form-control-static">{$customization['value']}</p>
									</div>
								</div>
							{/if}
						</div>
					{/foreach}
				</div>
			</div>
		</div>
	</div>
{/if}