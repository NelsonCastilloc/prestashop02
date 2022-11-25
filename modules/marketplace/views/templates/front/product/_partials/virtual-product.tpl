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

<div class="vir_container no_margin">
    <div class="row">
        <h4 class="col-md-12">{l s='Virtual Product' mod='marketplace'}</h4>
    </div>
    <div class="form-group">
        <label class="control-label required" >{l s='Upload File:' mod='marketplace'}</label>
        <div class="wk_tooltip">
            <div class="wk_tooltiptext">
                {l s='Upload a file from your computer' mod='marketplace'} ({Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')|string_format:'%.2f'} {l s='MB max.' mod='marketplace'})
            </div>
        </div>
        <div class="mp_virtual_upload-btn-wrapper">
            <button class="mp_vitual_btnr">{l s='Choose file' mod='marketplace'}</button>
            <span class="mp_vrt_prod_name">{l s='No file selected' mod='marketplace'}</span>
            <input type="file" name="mp_vrt_prod_file" id="mp_vrt_prod" style="display:none"/>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label" >{l s='File name:' mod='marketplace'}</label>
        <div class="wk_tooltip">
            <div class="wk_tooltiptext">
                {l s='The full filename with its extension (e.g. Book.pdf)' mod='marketplace'}
            </div>
        </div>
        <input type="text" class="form-control" name="mp_vrt_prod_name" id="mp_vrt_prod_name" {if isset($is_virtual_prod)}value="{$is_virtual_prod['display_filename']}"{/if}>
    </div>
    {if isset($is_virtual_prod) && isset($attach_file_exist) && isset($id) && isset($showTab)}
    <div class="form-group">
        <a
            href="{$link->getModuleLink('marketplace', 'downloadFile',['id_value'=>$id])}"
            class="btn btn-default">
            <i class="material-icons">cloud_download</i>
            {l s='download file' mod='marketplace'}
        </a>
        <input type="hidden" name="mp_id_prod" id="mp_id_prod" value="{$id}">
        <span class="btn btn-default deletefile">
            <i class="material-icons">delete</i>
            {l s='Delete this file' mod='marketplace'}
        </span>
    </div>
    {/if}
    <div class="form-group">
        <label class="control-label">{l s='Number of allowed downloads' mod='marketplace'}</label>
        <div class="wk_tooltip">
            <div class="wk_tooltiptext">
                {l s='Number of downloads allowed per customer. (Set to 0 for unlimited downloads)' mod='marketplace'}
            </div>
        </div>
        <input type="text" class="form-control" name="mp_vrt_prod_nb_downloable" id="mp_vrt_prod_nb_downloable" value="{if isset($is_virtual_prod)}{$is_virtual_prod['nb_downloadable']}{else}0{/if}">
    </div>
    <div class="form-group">
        <label class="control-label">{l s='Expiration date' mod='marketplace'}</label>
        <div class="wk_tooltip">
            <div class="wk_tooltiptext">
                {l s='If set, the file will not be downloadable after this date. Leave blank if you do not wish to attach an expiration date.' mod='marketplace'}
            </div>
        </div>
        <input type="text" autocomplete="off" placeholder="YYYY-MM-DD" class="datepicker form-control" name="mp_vrt_prod_expdate" id="mp_vrt_prod_expdate"
        {if isset($is_virtual_prod)}value="{$is_virtual_prod['date_expiration']|date_format:"Y-m-d"}"{/if}>
    </div>
    <div class="form-group">
        <label class="control-label">{l s='Number of days' mod='marketplace'}</label>
        <div class="wk_tooltip">
            <div class="wk_tooltiptext">
                {l s='Number of days this file can be accessed by customers - (Set to zero for unlimited access.)' mod='marketplace'}
            </div>
        </div>
        <input type="text" class="form-control" name="mp_vrt_prod_nb_days" id="mp_vrt_prod_nb_days" value="{if isset($is_virtual_prod)}{$is_virtual_prod['nb_days_accessible']}{else}0{/if}">
    </div>
</div>