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

class WkMpSellerPaymentMode extends ObjectModel
{
    public $id_shop;
    public $payment_mode;

    public static $definition = array(
        'table' => 'wk_mp_payment_mode',
        'primary' => 'id_mp_payment',
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'payment_mode' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
        ),
    );

    public function delete()
    {
        $deleteMpPayment = Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'wk_mp_customer_payment_detail`
            WHERE `payment_mode_id` = '.(int) $this->id
        );

        if (!$deleteMpPayment || !parent::delete()) {
            return false;
        }

        return true;
    }

    public static function getPaymentMode($idShop = false)
    {
        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }

        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_payment_mode`
            WHERE `id_shop` = '.(int) $idShop
        );
    }
}
