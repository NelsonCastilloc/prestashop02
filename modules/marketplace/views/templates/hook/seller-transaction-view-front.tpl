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

<div class="clearfix modal-header" style="height:70px;">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <div class="col-xs-6 col-sm-8 box-stats color3">
        <h4>{l s='Settlement Transaction' mod='marketplace'} </h4>
    </div>
    <div class="col-xs-6 col-sm-3 box-stats color3">
        <div class="kpi-content" style="padding-left:30px; font-size:16px;">
            <i class="material-icons">date_range</i>
            <span class="title">{l s='Date' mod='marketplace'}</span>
            <span class="value" style="float:left; margin-left:24px;">
                {if isset($objTransaction->date_add)}{$objTransaction->date_add|date_format:"%D"}{/if}
            </span>
        </div>
    </div>
</div>
<div class="clearfix modal-body">
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label">{l s='Payment Method:' mod='marketplace'}</label>
        <div class="col-lg-5">
            {if isset($objTransaction->payment_method) && $objTransaction->payment_method}{$objTransaction->payment_method|escape:'htmlall':'UTF-8'}{else}{l s='N/A' mod='marketplace'}{/if}
        </div>
    </div>
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label">{l s='Payment Details:' mod='marketplace'}</label>
        <div class="col-lg-5">
            {if isset($payment_mode_details) && $payment_mode_details}{$payment_mode_details|escape:'htmlall':'UTF-8'}{else}N/A{/if}
        </div>
    </div>
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label">
            {l s='Transaction ID:' mod='marketplace'}
        </label>
        <div class="col-lg-5">
            {if isset($objTransaction->id_transaction) && $objTransaction->id_transaction}{$objTransaction->id_transaction|escape:'htmlall':'UTF-8'}{else}{l s='N/A' mod='marketplace'}{/if}
        </div>
    </div>
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label">
            {l s='Remark:' mod='marketplace'}
        </label>
        <div class="col-lg-5">
            {if isset($objTransaction->remark) && $objTransaction->remark}{$objTransaction->remark|escape:'htmlall':'UTF-8'}{else}{l s='N/A' mod='marketplace'}{/if}
        </div>
    </div>
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label required">{l s='Amount:' mod='marketplace'}</label>
        <div class="col-lg-5">
            {if isset($amount)}{$amount|escape:'htmlall':'UTF-8'}{/if}
        </div>
    </div>
    {hook h='displayExtraTransactionDetailFront' id_transaction_history=$objTransaction->id}
</div>