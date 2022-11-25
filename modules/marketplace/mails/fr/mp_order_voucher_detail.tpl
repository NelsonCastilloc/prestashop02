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

{if isset($list) && $list}
	<table class="table table-recap" bgcolor="#ffffff" style="float: right;width:50%;border-collapse:collapse">
		<!-- Title -->
		<thead>
			<tr>
				<th
					colspan="6"
					style="border:1px solid #D6D4D4;background-color:#fbfbfb;font-family:Arial;color:#333;font-size:13px;padding:10px;">
					Voucher Details
				</th>
			</tr>
			<tr>
				<th
					style="border:1px solid #D6D4D4;background-color:#fbfbfb;font-family:Arial;color:#333;font-size:13px;padding:10px;">
					Discount Name
				</th>
				<th
					style="border:1px solid #D6D4D4;background-color:#fbfbfb;font-family:Arial;color:#333;font-size:13px;padding:10px;">
					Value
				</th>
			</tr>
		</thead>
		<tbody>
			{foreach $list.mp_voucher_info as $mp_voucher}
				<tr>
					<td style="border:1px solid #D6D4D4;background-color:#fbfbfb;font-family:Arial;color:#333;font-size:13px;padding:10px; text-align: center;">{$mp_voucher['voucher_name']}</td>
					<td style="border:1px solid #D6D4D4;background-color:#fbfbfb;font-family:Arial;color:#333;font-size:13px;padding:10px; text-align: center;">{$mp_voucher['voucher_value']}</td>
				</tr>
			{/foreach}
			<tr>
				<td style="border:1px solid #D6D4D4;background-color:#fbfbfb;font-family:Arial;color:#333;font-size:13px;padding:10px; text-align: center;"><strong>{l s='Total' mod='marketplace'}</strong></td>
				<td style="border:1px solid #D6D4D4;background-color:#fbfbfb;font-family:Arial;color:#333;font-size:13px;padding:10px; text-align: center;">{$list.total_voucher}</td>
			</tr>
		</tbody>
	</table>
{/if}