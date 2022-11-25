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

function upgrade_module_6_0_0($module)
{
    $wkQueries = array(
        "ALTER TABLE `"._DB_PREFIX_."wk_mp_seller`
        ADD COLUMN `category_permission` text AFTER `seller_details_access`",

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


    if (Module::isEnabled('mppackproducts')) {
        Module::disableAllByName('mppackproducts');
    } else {
        $wkQueries[] = "ALTER TABLE `"._DB_PREFIX_."wk_mp_seller_product`
        ADD COLUMN `is_pack_product` int(10) unsigned DEFAULT 0 AFTER `admin_approved`";
        
        $wkQueries[] = "ALTER TABLE `"._DB_PREFIX_."wk_mp_seller_product`
        ADD COLUMN `pack_stock_type` int(10) unsigned DEFAULT 3 AFTER `is_pack_product`";
    }
    
    $mpDatabaseInstance = Db::getInstance();
    $mpSuccess = true;
    foreach ($wkQueries as $mpQuery) {
        $mpSuccess &= $mpDatabaseInstance->execute(trim($mpQuery));
    }
    if ($mpSuccess) {
        $module->installTab('AdminManufacturerDetail', 'Brands', 'AdminMarketplaceManagement');
        $module->installTab('AdminMpSuppliers', 'Suppliers', 'AdminMarketplaceManagement');

        if (Module::isEnabled('mpmanufacturers')) {
            $sql = "SELECT  * FROM `"._DB_PREFIX_."marketplace_manufacturers` WHERE `id_ps_manuf` = 0";
            $manufData = Db::getInstance()->executeS($sql);
            if (!empty($manufData)) {
                include_once _PS_MODULE_DIR_.'mpmanufacturers/classes/MarketplaceManufacturers.php';
                include_once _PS_MODULE_DIR_.'mpmanufacturers/classes/MarketplaceProductManufacturers.php';
                foreach ($manufData as $manuf) {
                    $manufId = $manuf['id'];
                    MarketplaceManufacturers::createAndUpdatePsManufacturer($manufId, 0);
                }
            }

            $sql = "INSERT INTO `"._DB_PREFIX_."wk_mp_manufacturers`(`id_wk_mp_manufacturers`,
            `id_seller`, `id_ps_manuf`, `id_ps_manuf_address`)
            SELECT  `id`, `id_seller`, `id_ps_manuf`, `id_ps_manuf_address` FROM
            `"._DB_PREFIX_."marketplace_manufacturers`";
            $mpSuccess &= Db::getInstance()->execute($sql);

            foreach (glob(_PS_MODULE_DIR_.'mpmanufacturers/views/img/*') as $image) {
                copy($image, _PS_MODULE_DIR_.'marketplace/views/img/mpmanufacturers/' . basename($image));
            }

            Module::disableAllByName('mpmanufacturers');
        }
        if (Module::isEnabled('mpsupplier')) {
            $sql = "SELECT  * FROM `"._DB_PREFIX_."marketplace_supplier`";
            $supplierData = Db::getInstance()->executeS($sql);
            if (!empty($supplierData)) {
                include_once _PS_MODULE_DIR_.'mpsupplier/classes/MarketplaceSupplier.php';
                include_once _PS_MODULE_DIR_.'mpsupplier/classes/MarketplaceProductSupplier.php';
                foreach ($supplierData as $mpSupplier) {
                    $idPsSupplier = $mpSupplier['supplier_id_presta'];
                    if (!$idPsSupplier) {
                        MarketplaceSupplier::changeStatus(0, $idPsSupplier, $mpSupplier['id_shop']);
                    }
                    $idAddress = Address::getAddressIdBySupplierId($idPsSupplier);
                    $objMpSupplier = new WkMpSuppliers();
                    $objMpSupplier->id_wk_mp_supplier = (int)$mpSupplier['id'];
                    $objMpSupplier->id_seller = (int)$mpSupplier['id_seller'];
                    $objMpSupplier->id_ps_supplier = (int)$idPsSupplier;
                    $objMpSupplier->id_ps_supplier_address = (int)$idAddress;
                    $objMpSupplier->save();
                }

                foreach (glob(_PS_MODULE_DIR_.'mpsupplier/views/img/*') as $image) {
                    copy($image, _PS_MODULE_DIR_.'marketplace/views/img/mpsuppliers/' . basename($image));
                }
            }
            Module::disableAllByName('mpsupplier');
        }
        if (Module::isEnabled('mpvirtualproduct')) {
            Module::disableAllByName('mpvirtualproduct');
        }
        if (Module::isEnabled('mpslotpricing')) {
            Module::disableAllByName('mpslotpricing');
        }
        if (Module::isEnabled('mpproducttags')) {
            Module::disableAllByName('mpproducttags');
        }
        if (Module::isEnabled('mpproductcustomization')) {
            Module::disableAllByName('mpproductcustomization');
        }
    }
    return true;
}
