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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_3_1()
{
    $wkQueries = array(
        "ALTER TABLE `"._DB_PREFIX_."wk_mp_seller_product`
        MODIFY COLUMN `ps_id_carrier_reference` text",
    );

    $mpDatabaseInstance = Db::getInstance();
    $mpSuccess = true;
    foreach ($wkQueries as $mpQuery) {
        $mpSuccess &= $mpDatabaseInstance->execute(trim($mpQuery));
    }
    if ($mpSuccess) {
        return true;
    }
    return true;
}
