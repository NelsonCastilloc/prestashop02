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
	<div class="box-head">
		<h2><i class="icon-user"></i> {l s='Customer Details' mod='marketplace'}</h2>
		<div class="wk_border_line"></div>
	</div>
	<div class="box-content">
		<div class="tabs">
			<ul class="nav nav-tabs">
				<li class="nav-item">
					<a class="nav-link active" href="#ship_addr" data-toggle="tab">
						<i class="icon-truck"></i>
						{l s='Shipping Address' mod='marketplace'}
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="#invoice_addr" data-toggle="tab">
						<i class="icon-file-text"></i>
						{l s='Invoice Address' mod='marketplace'}
					</a>
				</li>
				{hook h="displayMpOrderCustomerDetailsTab"}
			</ul>
			<div class="tab-content" id="tab-content">
				<div class="tab-pane fade in active show" id="ship_addr">
					<div class="well">
						<div class="row">
							<div class="col-sm-12">
								{$addresses.deliveryFormat nofilter}
								{if $addresses.delivery->other}
									<hr />{$addresses.delivery->other}<br />
								{/if}
							</div>
						</div>
					</div>
					{hook h="displayMpOrderDeliveryAddressBottom"}
				</div>
				<div class="tab-pane fade in" id="invoice_addr">
					<div class="well">
						<div class="row">
							<div class="col-sm-6">
								{$addresses.invoiceFormat nofilter}
								{if $addresses.invoice->other}
									<hr />{$addresses.invoice->other}<br />
								{/if}
							</div>
						</div>
					</div>
					{hook h="displayMpOrderInvoiceAddressBottom"}
				</div>
				{hook h="displayMpOrderCustomerDetailsTabContent"}
			</div>
		</div>
	</div>
</div>