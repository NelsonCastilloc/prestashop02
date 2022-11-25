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

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th width="6%">{l s='ID' mod='marketplace'}</th>
                <th width="10%">{l s='Reference' mod='marketplace'}</th>
                <th width="15%">{l s='Customer' mod='marketplace'}</th>
                <th width="12%">{l s='Total' mod='marketplace'}</th>
                <th width="20%">{l s='Status' mod='marketplace'}</th>
                <th width="17%">{l s='Payment' mod='marketplace'}</th>
                <th>{l s='Date' mod='marketplace'}</th>
            </tr>
        </thead>
        <tbody>
            {if isset($recentOrders) && $recentOrders}
                {foreach from=$recentOrders item=order}
                    <tr class="mp_order_row" is_id_order="{$order.id_order}">
                        <td>{$order.id_order}</td>
                        <td>{$order.reference}</td>
                        <td>{$order.buyer_info->firstname} {$order.buyer_info->lastname}</td>
                        <td>{$order.total_paid}</td>
                        <td>{$order.order_status}</td>
                        <td>{$order.payment_mode}</td>
                        <td>{dateFormat date=$order.date_add full=1}</td>
                    </tr>
                {/foreach}
            {else}
                <tr><td colspan="7"><center>{l s='No orders found' mod='marketplace'}</center></td>
            {/if}
        </tbody>
    </table>
</div>
{if $totalOrdersCount > 5}
<p class="wk_text_right">
    <a href="{$link->getModuleLink('marketplace', 'mporder')}">
        <button class="btn btn-primary btn-sm" type="button">{l s='View All Orders' mod='marketplace'}</button>
    </a>
</p>
{/if}