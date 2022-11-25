<?php
/**
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
*/

class WkMpOrderVoucher extends ObjectModel
{
    public $order_id;
    public $seller_id;
    public $voucher_name;
    public $voucher_value;

    public static $definition = array(
        'table' => 'wk_mp_order_voucher',
        'primary' => 'id_order_voucher',
        'fields' => array(
            'order_id' => array('type' => self::TYPE_INT, 'required' => true),
            'seller_id' => array('type' => self::TYPE_INT, 'required' => true),
            'voucher_name' => array('type' => self::TYPE_STRING, 'required' => true),
            'voucher_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
        ),
    );

    /**
     * Get Voucher Details Using Seller ID and Order ID
     *
     * @param  int $idOrder  Order ID
     * @param  int $idSeller Seller ID
     * @return array/bool
     */
    public static function getVoucherDetailByIdSeller($idOrder, $idSeller)
    {
        $sellerInfo = Db::getInstance()->executeS(
            'SELECT * FROM  `'._DB_PREFIX_.'wk_mp_order_voucher` WHERE `order_id` = '.(int) $idOrder
            .' AND `seller_id` = '. (int) $idSeller
        );

        if ($sellerInfo) {
            return $sellerInfo;
        }

        return false;
    }
}
