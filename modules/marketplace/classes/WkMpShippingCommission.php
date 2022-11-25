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

class WkMpShippingCommission extends ObjectModel
{
    public $id_seller;
    public $commission_rate;

    public static $definition = array(
        'table' => 'wk_mp_shipping_commission',
        'primary' => 'id_wk_mp_shipping_commission',
        'fields' => array(
            'id_seller' => array('type' => self::TYPE_INT, 'required' => true),
            'commission_rate' => array('type' => self::TYPE_FLOAT),
        ),
    );

    /**
     * Get all those sellers who has no shipping commission yet
     *
     * @return array/boolean
     */
    public function getSellerWithoutShippingCommission()
    {
        $mpSellerInfo = Db::getInstance()->executeS(
            'SELECT `id_seller`, `business_email`
            FROM `'._DB_PREFIX_.'wk_mp_seller`
            WHERE `active` = 1 '.WkMpSeller::addSqlRestriction().'
            AND `id_seller` NOT IN (SELECT `id_seller` FROM `'._DB_PREFIX_.'wk_mp_shipping_commission`)'
        );

        if (empty($mpSellerInfo)) {
            return false;
        }

        return $mpSellerInfo;
    }

    /**
     * Get Commission Rate by using Seller customer ID, if customer id is false then current customer id will be used
     *
     * @return float
     */
    public function getCommissionRateBySellerCustomerId($sellerCustomerId = false)
    {
        if (!$sellerCustomerId) { // customer id is false we will take current customer's id
            $sellerCustomerId = Context::getContext()->customer->id;
        }

        if ($sellerInfo = WkMpSeller::getSellerDetailByCustomerId($sellerCustomerId)) {
            return Db::getInstance()->getValue(
                'SELECT `commission_rate` FROM `'._DB_PREFIX_.'wk_mp_shipping_commission`
                WHERE `id_seller` = '.(int) $sellerInfo['id_seller']
            );
        }

        return false;
    }
}
