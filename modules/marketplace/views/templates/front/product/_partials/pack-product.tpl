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

<div class="form-group pkprod_container">
    <input type="hidden" name="current_lang_id" id="current_lang_id" value="{$current_lang.id_lang}">
    <div class="form-group">
        <label class="control-label" for="selectproduct">
        {l s='Add products to your pack' mod='marketplace'}
        <img class="wk_pack_product_loader" src="{$module_dir}marketplace/views/img/loader.gif" width="20" />
        </label>
        <input class="form-control" type="text" name="selectproduct" id="selectproduct" data-value="" data-img="" autocomplete="off"/>
        <small class="help-block">
            {l s='Start by typing the first letter of the product name, then select the product from the drop-down list.' mod='marketplace'}
        </small>
        <div class="row no_margin sug_container">
            <ul id="sugpkprod_ul"  style="margin-top: -20px;"></ul>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label" for="packproductquant">{l s='Quantity' mod='marketplace'}</label>
        <div class="input-group">
            <span class="input-group-addon">x</span>
            <input class="form-control" type="text" name="quant" id="packproductquant" value="1" autocomplete="off">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-3">
            <button class="btn btn-info" id="addpackprodbut">
                <span> <i class="icon-plus-sign-alt"></i> {l s='Add this product to the pack' mod='marketplace'} </span>
            </button>
        </div>
    </div>

    <div class="row margin-top-20 no_margin pkprodlist">
        {if isset($isPackProduct) && isset($mpPackProducts) && $mpPackProducts}
            {foreach from=$mpPackProducts key=k item=mpPackProduct}
                <div class="col-sm-4 col-xs-12">
                    <div class="row no_margin pk_sug_prod" ps_prod_id="{$mpPackProduct->id_ps_product}" ps_id_prod_attr="{$mpPackProduct->ps_prod_attr_id}">
                        <div class="col-sm-12 col-xs-12">
                            <img class="img-responsive pk_sug_img" src="{$mpPackProduct->image_link}">
                            <p class="text-center">{$mpPackProduct->product_name}</p>
                            {if $mpPackProduct->product_ref != ''}
                                <p class="text-center">{l s='REF:' mod='marketplace'} {$mpPackProduct->product_ref}</p>
                            {/if}
                            <span class="pull-left">x{$mpPackProduct->quantity}</span>
                            <a class="pull-right dltpkprod"><i class="material-icons">&#xE872;</i></a>
                            <input type="hidden" class="pspk_id_prod" name="pspk_id_prod[]" value="{$mpPackProduct->id_ps_product}">
                            <input type="hidden" name="pspk_prod_quant[]" value="{$mpPackProduct->quantity}">
                            <input type='hidden' class='pspk_id_prod_attr' name='pspk_id_prod_attr[]' value="{$mpPackProduct->ps_prod_attr_id}">
                        </div>
                    </div>
                </div>
            {/foreach}
        {/if}
    </div>
    <div class="form-group">
        <label for="pack_qty_mgmt" class="control-label">
            {l s='Pack quantities' mod='marketplace'}
        </label>
        <select name="pack_qty_mgmt" id="pack_qty_mgmt" class="form-control form-control-select">
            <option value="0" {if isset($product_stock_type) && $product_stock_type == 0}selected{/if}>{l s='Decrement pack only.' mod='marketplace'}</option>
            <option value="1" {if isset($product_stock_type) && $product_stock_type == 1}selected{/if}>{l s='Decrement products in pack only.' mod='marketplace'}</option>
            <option value="2" {if isset($product_stock_type) && $product_stock_type == 2}selected{/if}>{l s='Decrement both.' mod='marketplace'}</option>
            <option value="3" {if !isset($product_stock_type) || $product_stock_type == 3} selected {/if}>
            {l s='Default:' mod='marketplace'}
            {if isset($pack_stock_type) && $pack_stock_type == 0}
                {l s='Decrement pack only.' mod='marketplace'}
            {elseif isset($pack_stock_type) && $pack_stock_type == 1}
                {l s='Decrement products in pack only.' mod='marketplace'}
            {else}
                {l s='Decrement both.' mod='marketplace'}
            {/if}
            </option>
        </select>
    </div>
</div>