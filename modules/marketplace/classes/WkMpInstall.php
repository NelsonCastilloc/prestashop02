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

class WkMpInstall
{
    public function createMpTables()
    {
        $mpSuccess = true;
        $mpDatabaseInstance = Db::getInstance();
        if ($tableQueries = $this->getMpTableQueries()) {
            foreach ($tableQueries as $mpQuery) {
                $mpSuccess &= $mpDatabaseInstance->execute(trim($mpQuery));
            }
        }

        return $mpSuccess;
    }

    private function getMpTableQueries()
    {
        return array(
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller` (
                `id_seller` int(10) unsigned NOT NULL auto_increment,
                `shop_name_unique` varchar(255) character set utf8 NOT NULL,
                `link_rewrite` varchar(255) character set utf8 NOT NULL,
                `seller_firstname` varchar(255) character set utf8 NOT NULL,
                `seller_lastname` varchar(255) character set utf8 NOT NULL,
                `business_email` varchar(128) NOT NULL,
                `phone` varchar(32) DEFAULT NULL,
                `fax` varchar(32) DEFAULT NULL,
                `address` text,
                `postcode` varchar(12) DEFAULT NULL,
                `city` varchar(64) DEFAULT NULL,
                `id_country` int(10) unsigned NOT NULL DEFAULT '0',
                `id_state` int(10) unsigned NOT NULL DEFAULT '0',
                `tax_identification_number` varchar(255) DEFAULT  NULL,
                `default_lang` int(10) unsigned NOT NULL DEFAULT '0',
                `facebook_id` varchar(255) character set utf8 NOT NULL,
                `twitter_id` varchar(255) character set utf8 NOT NULL,
                `youtube_id` varchar(255) character set utf8 NOT NULL,
                `instagram_id` varchar(255) character set utf8 NOT NULL,
                `profile_image` varchar(15) NOT NULL,
                `profile_banner` varchar(15) NOT NULL,
                `shop_image` varchar(15) NOT NULL,
                `shop_banner` varchar(15) NOT NULL,
                `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `shop_approved` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `seller_customer_id` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL DEFAULT '1',
                `id_shop_group` int(10) unsigned NOT NULL DEFAULT '1',
                `seller_details_access` varchar(255) character set utf8 NOT NULL,
                `category_permission` text,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_seller`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_lang` (
                `id_seller` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `shop_name` varchar(255) character set utf8 NOT NULL,
                `about_shop` text,
                PRIMARY KEY (`id_seller`, `id_lang`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_help_desk` (
                `id_mp_help_desk` int(10) unsigned NOT NULL auto_increment,
                `id_product` int(11),
                `id_customer` int(11),
                `id_seller` int(11),
                `subject` varchar(128) DEFAULT NULL,
                `description` text,
                `customer_email` varchar(128) NOT NULL,
                `active` tinyint(1) NOT NULL DEFAULT '0',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_mp_help_desk`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_review` (
                `id_review` int(10) unsigned NOT NULL auto_increment,
                `id_seller` int(11),
                `id_customer` int(11),
                `customer_email` varchar(100),
                `rating` int(11),
                `review` text,
                `active` tinyint(1),
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_review`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_review_likes` (
                `id_review_like` int(10) unsigned NOT NULL auto_increment,
                `id_review` int(10) unsigned NOT NULL,
                `id_customer` int(10) unsigned NOT NULL,
                `like` tinyint(1) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_review_like`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_product` (
                `id_mp_product` int(10) unsigned NOT NULL auto_increment,
                `id_seller` int(10) unsigned NOT NULL,
                `id_ps_product` int(10) unsigned DEFAULT '0',
                `id_mp_shop_default` int(10) unsigned NOT NULL DEFAULT '1',
                `id_mp_duplicate_product_parent` int(10) unsigned DEFAULT '0',
                `status_before_deactivate` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `admin_assigned` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `admin_approved` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `is_pack_product` int(10) unsigned NOT NULL DEFAULT '0',
                `pack_stock_type` int(10) unsigned NOT NULL DEFAULT '3',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_mp_product`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_commision` (
                `id_wk_mp_commision` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_seller` int(10) NOT NULL,
                `commision_type` varchar(64) DEFAULT NULL,
                `commision_rate` decimal(20,2) NOT NULL DEFAULT '0.00',
                `commision_amt` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `commision_tax_amt` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_customer_id` int(10) NOT NULL,
                PRIMARY KEY (`id_wk_mp_commision`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_payment_mode` (
                `id_mp_payment` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_shop` int(10) unsigned NOT NULL DEFAULT '1',
                `payment_mode` varchar(255) NOT NULL,
                PRIMARY KEY (`id_mp_payment`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_customer_payment_detail` (
                `id_customer_payment` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `seller_customer_id` int(10) unsigned NOT NULL,
                `payment_mode_id` int(10) unsigned NOT NULL,
                `payment_detail` varchar(255) NOT NULL,
                PRIMARY KEY (`id_customer_payment`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_order` (
                `id_mp_order` int(10) unsigned NOT NULL auto_increment,
                `seller_customer_id` int(10) unsigned NOT NULL,
                `seller_id` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL DEFAULT '1',
                `id_shop_group` int(10) unsigned NOT NULL DEFAULT '1',
                `seller_shop` varchar(255) character set utf8 NOT NULL,
                `seller_firstname` varchar(255) character set utf8 NOT NULL,
                `seller_lastname` varchar(255) character set utf8 NOT NULL,
                `seller_email` varchar(128) NOT NULL,
                `total_earn_ti` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_earn_te` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_admin_commission` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_admin_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_seller_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `total_seller_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_mp_order`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_order_detail` (
                `id_mp_order_detail` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_seller_order` int(10) NOT NULL,
                `id_shop` int(10) unsigned NOT NULL DEFAULT '1',
                `product_id` int(10) NOT NULL,
                `product_attribute_id` int(10) NOT NULL,
                `id_customization` int(10) NOT NULL DEFAULT '0',
                `seller_customer_id` int(10) NOT NULL,
                `seller_name` varchar(255) NOT NULL,
                `product_name` varchar(255) NOT NULL,
                `quantity` int(10) NOT NULL,
                `price_ti` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `price_te` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_commission` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `id_order` int(10) NOT NULL,
                `commission_type` varchar(64) DEFAULT NULL,
                `commission_rate` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `commission_amt` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `commission_tax_amt` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `tax_distribution_type` varchar(255) NOT NULL,
                `id_currency` int(10) NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_mp_order_detail`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_order_status` (
                `id_order_status` int(10) unsigned NOT NULL auto_increment,
                `id_order` int(10) unsigned NOT NULL,
                `id_seller` int(10) unsigned NOT NULL,
                `current_state` int(10) unsigned NOT NULL,
                `tracking_number` varchar(64) DEFAULT NULL,
                `tracking_url` varchar(255) DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_order_status`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_order_history` (
                `id_order_history` int(10) unsigned NOT NULL auto_increment,
                `id_order` int(10) unsigned NOT NULL,
                `id_seller` int(10) unsigned NOT NULL,
                `id_order_state` int(10) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_order_history`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_order_voucher` (
                `id_order_voucher` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `order_id` int(10) NOT NULL,
                `seller_id` int(10) NOT NULL,
                `voucher_name` varchar(255) NOT NULL,
                `voucher_value` decimal(20,6) NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_order_voucher`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_admin_shipping` (
                `id_wk_mp_admin_shipping` int(11) unsigned NOT NULL auto_increment,
                `order_id` int(11) NOT NULL,
                `order_reference` VARCHAR(9),
                `shipping_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_earn` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_earn` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_wk_mp_admin_shipping`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_shipping_distribution` (
                `id_seller_shipping_distribution` int(11) unsigned NOT NULL auto_increment,
                `order_id` int(11) NOT NULL,
                `order_reference` VARCHAR(9),
                `seller_customer_id` int(10) unsigned NOT NULL,
                `seller_earn` decimal(20,6) NOT NULL DEFAULT '0.000000',
                PRIMARY KEY (`id_seller_shipping_distribution`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_seller_transaction_history`(
                `id_seller_transaction_history` int(10) unsigned NOT NULL auto_increment,
                `id_customer_seller` int(10) NOT NULL,
                `id_currency` int(10) unsigned NOT NULL,
                `id_mp_order_detail` int(10) NOT NULL DEFAULT '0',
                `id_shop` int(10) unsigned NOT NULL DEFAULT '1',
                `seller_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_shipping` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_refunded_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `seller_receive` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_commission` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_tax` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_shipping` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `admin_refunded_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
                `payment_method` varchar(255) NOT NULL DEFAULT 'Manual',
                `transaction_type` varchar(255) NOT NULL DEFAULT 'order',
                `id_transaction` varchar(254) NULL DEFAULT '0',
                `remark` varchar(255) DEFAULT NULL,
                `status` int(10) unsigned NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_seller_transaction_history`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_carrier_distributor_type` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_ps_reference` int(11) unsigned NOT NULL,
                `type` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_shipping_commission` (
                `id_wk_mp_shipping_commission` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_seller` int(10) NOT NULL,
                `commission_rate` decimal(20,2) NOT NULL DEFAULT '0.00',
                PRIMARY KEY (`id_wk_mp_shipping_commission`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_manufacturers` (
                `id_wk_mp_manufacturers` int(10) unsigned NOT NULL auto_increment,
                `id_seller` int(10) unsigned NOT NULL,
                `id_ps_manuf` int(10) unsigned NOT NULL,
                `id_ps_manuf_address` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_wk_mp_manufacturers`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_suppliers` (
                `id_wk_mp_supplier` int(10) unsigned NOT NULL auto_increment,
                `id_seller` int(10) unsigned NOT NULL,
                `id_ps_supplier` int(10) unsigned NOT NULL,
                `id_ps_supplier_address` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_wk_mp_supplier`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wk_mp_attachments` (
                `id_wk_mp_attachment` int(10) unsigned NOT NULL auto_increment,
                `id_seller` int(10) unsigned NOT NULL,
                `id_ps_attachment` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id_wk_mp_attachment`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8",
        );
    }

    public function deleteMpTables()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS
            `'._DB_PREFIX_.'wk_mp_seller`,
            `'._DB_PREFIX_.'wk_mp_seller_lang`,
            `'._DB_PREFIX_.'wk_mp_seller_help_desk`,
            `'._DB_PREFIX_.'wk_mp_seller_review`,
            `'._DB_PREFIX_.'wk_mp_seller_review_likes`,
            `'._DB_PREFIX_.'wk_mp_seller_product`,
            `'._DB_PREFIX_.'wk_mp_commision`,
            `'._DB_PREFIX_.'wk_mp_payment_mode`,
            `'._DB_PREFIX_.'wk_mp_customer_payment_detail`,
            `'._DB_PREFIX_.'wk_mp_seller_order`,
            `'._DB_PREFIX_.'wk_mp_seller_order_detail`,
            `'._DB_PREFIX_.'wk_mp_seller_order_status`,
            `'._DB_PREFIX_.'wk_mp_seller_order_history`,
            `'._DB_PREFIX_.'wk_mp_order_voucher`,
            `'._DB_PREFIX_.'wk_mp_admin_shipping`,
            `'._DB_PREFIX_.'wk_mp_seller_shipping_distribution`,
            `'._DB_PREFIX_.'wk_mp_seller_transaction_history`,
            `'._DB_PREFIX_.'wk_mp_carrier_distributor_type`,
            `'._DB_PREFIX_.'wk_mp_shipping_commission`,
            `'._DB_PREFIX_.'wk_mp_manufacturers`,
            `'._DB_PREFIX_.'wk_mp_suppliers`,
            `'._DB_PREFIX_.'wk_mp_attachments`
            '
        );
    }
}
