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

class WkMpSellerHelpDesk extends ObjectModel
{
    public $id_product;
    public $id_customer;
    public $id_seller;
    public $subject;
    public $description;
    public $customer_email;
    public $active;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_mp_seller_help_desk',
        'primary' => 'id_mp_help_desk',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_seller' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'subject' => array('type' => self::TYPE_STRING),
            'description' => array('type' => self::TYPE_STRING),
            'customer_email' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isEmail',
                'required' => true,
                'size' => 128
            ),
            'active' => array('type' => self::TYPE_BOOL,'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
        ),
    );
}
