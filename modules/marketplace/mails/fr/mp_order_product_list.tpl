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

{foreach $list as $product_attribute}
	{foreach $product_attribute as $product_customization}
		{foreach $product_customization as $product}
			<tr>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td>
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									<strong>{$product['product_name']}</strong>
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td align="right">
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									{$product['unit_price_tax_excl']}
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td align="right">
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									{$product['unit_price_tax_incl']}
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td align="right">
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									{$product['product_quantity']}
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td align="right">
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									{$product['total_price_tax_incl']}
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
		{/foreach}
	{/foreach}
{/foreach}