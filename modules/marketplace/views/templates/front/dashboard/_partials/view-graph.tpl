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

<div class="row">
    <div class="col-sm-12">
        <section class="panel wk-graph">
            <div>
                <div class="col-xs-6 col-md-6 dashboard-options wk-sales" onclick="selectDashtrendsChart(this, 'sales');" style="border-left: none;background-color: #1777b6;color: #fff;">
                    <div class="dash-item">{l s='Sales' mod='marketplace'}</div>
                    {*<div class="wk_tooltip">
                        <span class="wk_tooltiptext">{l s='Sum of revenue (excl. tax) generated within the date range by orders considered validated.' mod='marketplace'}</span>
                    </div>*}
                    <div class="data_value"><span id="sales_score"></span></div>
                    <div class="dash_trend"><span id="sales_score_trends"></span></div>
                </div>
                <div class="col-xs-6 col-md-6 dashboard-options wk-orders" onclick="selectDashtrendsChart(this, 'orders');">
                    <div class="dash-item">{l s='Orders' mod='marketplace'}</div>
                    {*<div class="wk_tooltip">
                        <span class="wk_tooltiptext">{l s='Total number of orders received within the date range that are considered validated.' mod='marketplace'}</span>
                    </div>*}
                    <div class="data_value"><span id="orders_score"></span></div>
                    <div class="dash_trend"><span id="orders_score_trends"></span></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div id="wk-dashboad-graph-chart">
                <svg></svg>
            </div>
        </section>
    </div>
</div>