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

class WkMpSuppliers extends ObjectModel
{
    public $id_wk_mp_supplier;
    public $id_seller;
    public $id_ps_supplier;
    public $id_ps_supplier_address;

    public static $definition = array(
        'table' => 'wk_mp_suppliers',
        'primary' => 'id_wk_mp_supplier',
        'fields' => array(
            'id_seller' => array('type' => self::TYPE_INT),
            'id_ps_supplier' => array('type' => self::TYPE_INT),
            'id_ps_supplier_address' => array('type' => self::TYPE_INT),
        ),
    );

    public static function deleteSupplier($idSupplier)
    {
        if ($idSupplier) {
            $current_logo_file = _PS_MODULE_DIR_.'marketplace/views/img/mpsuppliers/'.$idSupplier.'.jpg';
            if (file_exists($current_logo_file)) {
                unlink($current_logo_file);
            }

            $objMpSupplier = new self($idSupplier);
            $idPsSupplier = $objMpSupplier->id_ps_supplier;

            if ($idPsSupplier) {
                $current_logo_file = _PS_IMG_DIR_.'s/'.$idSupplier.'.jpg';

                if (file_exists($current_logo_file)) {
                    unlink($current_logo_file);
                }

                $objPsSupplier = new Supplier($idPsSupplier);
                $objPsSupplier->delete();

                //delete all product_supplier linked to this supplier
                Db::getInstance()->execute(
                    'DELETE FROM `'._DB_PREFIX_.'product_supplier`
                    WHERE `id_supplier`='.(int) $idPsSupplier
                );

                $id_address = Address::getAddressIdBySupplierId($idPsSupplier);
                $address = new Address($id_address);
                if (Validate::isLoadedObject($address)) {
                    $address->deleted = 1;
                    $address->save();
                }
            }

            return $objMpSupplier->delete();
        }

        return false;
    }

    public function getSuppliersBySellerId($idSeller)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_mp_suppliers`
        WHERE `id_seller` = '.(int) $idSeller;

        $sellerSuppliers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (empty($sellerSuppliers)) {
            return false;
        } else {
            foreach ($sellerSuppliers as &$suppliers) {
                $supplier = new Supplier($suppliers['id_ps_supplier']);
                $suppliers['name'] = $supplier->name;
                $suppliers['active'] = $supplier->active;
            }
        }

        return $sellerSuppliers;
    }


    public static function getNoOfProductsByMpSupplierId($mpIdSupplier)
    {
        if ($mpIdSupplier) {
            return DB::getInstance()->getValue(
                'SELECT count(`id_wk_mp_supplier`) as `id` FROM `'._DB_PREFIX_.'wk_mp_suppliers` mps
                INNER JOIN `'._DB_PREFIX_.'product_supplier` ps ON ps.`id_supplier`=mps.`id_ps_supplier`
                WHERE mps.`id_wk_mp_supplier` = '.(int) $mpIdSupplier
            );
        }

        return false;
    }

    /**
     * Upload supplier details
     *
     * @param int $psIdSupplier Product Id Supplier
     * @param int $mpIdSupplier Mp Supplier Id
     * @param int $psIdSupplierAddress PS Supplier ID Address
     * @return bool
     */
    public function updateMpSupplierDetails($psIdSupplier, $mpIdSupplier, $psIdSupplierAddress)
    {
        Db::getInstance()->update(
            'wk_mp_suppliers',
            array('id_ps_supplier' => $psIdSupplier,
            'id_ps_supplier_address' => $psIdSupplierAddress),
            'id_wk_mp_supplier ='.(int) $mpIdSupplier
        );

        return true;
    }

    /**
     * Get MP supplier details by using mp supplier id
     *
     * @param int $mpManufId Mp Supplier Id
     * @return array
     */
    public static function getMpSupplierAllDetails($mpIdSupplier)
    {
        $objMpSupplier = new self();
        $supplierInfo = $objMpSupplier->getAllMpSuppliersById($mpIdSupplier);
        if ($supplierInfo) {
            $supplier = new Supplier($supplierInfo['id_ps_supplier']);
            if ($supplier) {
                $supplierInfo['name'] = $supplier->name;
                $supplierInfo['description'] = $supplier->description;
                $supplierInfo['meta_title'] = $supplier->meta_title;
                $supplierInfo['meta_description'] = $supplier->meta_description;
                $supplierInfo['meta_keywords'] = $supplier->meta_keywords;
            }
            $supplierInfo['active'] = $supplier->active;

            $mAddress = new Address($supplierInfo['id_ps_supplier_address']);
            if (!empty($mAddress)) {
                $supplierInfo['phone'] = $mAddress->phone_mobile;
                $supplierInfo['mobile_phone'] = $mAddress->phone_mobile;
                $supplierInfo['address'] = $mAddress->address1;
                $supplierInfo['zip'] = $mAddress->postcode;
                $supplierInfo['city'] = $mAddress->city;
                $supplierInfo['country'] = $mAddress->id_country;
                $supplierInfo['dni'] = $mAddress->dni;
                $supplierInfo['state'] = $mAddress->id_state;
            } else {
                $supplierInfo['phone'] = '';
                $supplierInfo['mobile_phone'] = '';
                $supplierInfo['address'] = '';
                $supplierInfo['zip'] = '';
                $supplierInfo['city'] = '';
                $supplierInfo['country'] = '';
                $supplierInfo['dni'] = '';
                $supplierInfo['state'] = '';
            }
            return $supplierInfo;
        } else {
            return false;
        }
    }



    /**
     * Get Only MP Supplier records by using mp Supplier id
     *
     * @param int $idMpSupplier Mp Supplier Id
     * @return array
     */
    public function getAllMpSuppliersById($idMpSupplier)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_mp_suppliers`
                WHERE `id_wk_mp_supplier`='.(int) $idMpSupplier);

        if (!$result) {
            return false;
        }

        return $result;
    }



    public static function getProductsForUpdateSupplierBySellerIdAndPsSupplierId(
        $idSeller,
        $idPsSupplier,
        $id_lang
    ) {
        if ($idSeller && $idPsSupplier) {
            return DB::getInstance()->executeS(
                'SELECT msp.`id_mp_product`, pl.`name` as product_name
                FROM `'._DB_PREFIX_.'wk_mp_seller_product` as msp
                JOIN `' . _DB_PREFIX_ . 'product` p ON (p.id_product = msp.id_ps_product)
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
				WHERE msp.`id_seller` = '.(int) $idSeller.'
				AND pl.`id_lang` = '.(int) $id_lang.'
				AND p.`id_product` NOT IN ( SELECT `id_product` FROM `'._DB_PREFIX_.'product_supplier`
                WHERE `id_supplier` = '.(int) $idPsSupplier.')
            	ORDER BY msp.`id_mp_product` ASC'
            );
        }
        // AND p.`id_supplier` != '.(int) $idPsSupplier.'

        return false;
    }

    /**
     * Upload Supplier logo to PS Brand
     *
     * @param int $psIdSupplier Ps Supplier Id
     * @param int $mpIdSupplier Mp Supplier Id
     * @param int $mpImageDir Mp image Directory
     */
    public function uploadSupplierLogoToPs($psIdSupplier, $mpIdSupplier, $mpImageDir)
    {
        $oldPath = $mpImageDir.$mpIdSupplier.'.jpg';
        if (file_exists($oldPath)) {
            $newPath = _PS_IMG_DIR_.'s/'.$psIdSupplier;
            $imagesTypes = ImageType::getImagesTypes('suppliers');

            //default image
            ImageManager::resize($oldPath, $newPath.'.jpg');

            //other images
            foreach ($imagesTypes as $image_type) {
                ImageManager::resize(
                    $oldPath,
                    $newPath.'-'.$image_type['name'].'.jpg',
                    $image_type['width'],
                    $image_type['height']
                );
            }
        }
    }

    public static function changeStatus($active, $idPsSupplier)
    {
        if ($idPsSupplier) {
            if ($idPsSupplier) {
                $objpssupp = new Supplier($idPsSupplier);
                $objpssupp->active = (int)$active;
                $objpssupp->save();
            }
        }
    }

    public function getInfoBySupplierIdAndSellerId($idSupplier, $idSeller)
    {
        if ($idSupplier && $idSeller) {
            return Db::getInstance()->getRow(
                'SELECT * FROM `'._DB_PREFIX_.'wk_mp_supplier`
                WHERE `id_seller`='.$idSeller.'
                AND `id_wk_mp_supplier` ='.$idSupplier
            );
        }

        return false;
    }

    public static function updateSupplierProducts($idMpSupplier, $idPsSupplier, $selectedProducts)
    {
        if ($selectedProducts && $idPsSupplier && $idMpSupplier) {
            $mpProductSupplier = new self();
            foreach ($selectedProducts as $mp_product_id) {
                $mpProduct = WkMpSellerProduct::getSellerProductByIdProduct($mp_product_id);
                if ($mpProduct) {
                    if ($mpProduct['id_ps_product']) {
                        $objProduct = new Product($mpProduct['id_ps_product']);
                        if ($objProduct->id_supplier) {
                            if (!Supplier::getNameById($objProduct->id_supplier)) {
                                $objProduct->id_supplier = (int)$idPsSupplier;
                                $objProduct->save();
                            }
                        } else {
                            $objProduct->id_supplier = (int)$idPsSupplier;
                            $objProduct->save();
                        }

                        $isExist = DB::getInstance()->getRow(
                            'SELECT `id_product_supplier` FROM `'._DB_PREFIX_.'product_supplier`
                            WHERE `id_product` = '.(int) $mpProduct['id_ps_product'].'
                            AND `id_product_attribute` = 0
                            AND `id_supplier` = '.(int) $idPsSupplier
                        );

                        if (!$isExist) {
                            $objProductSupplier = new ProductSupplier();
                            $objProductSupplier->id_product = (int)$mpProduct['id_ps_product'];
                            $objProductSupplier->id_product_attribute = 0;
                            $objProductSupplier->id_supplier = (int)$idPsSupplier;
                            $objProductSupplier->save();
                        }
                    } else {
                        $defaultSupplier = $mpProductSupplier->getDefaultSupplierByIdProduct($mp_product_id);
                        if (!$defaultSupplier) {
                        }
                    }
                }
            }
        }

        return true;
    }

    public function getDefaultSupplierByIdProduct($idProduct)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'marketplace_product_supplier`
            WHERE `mp_product_id` = '.(int) $idProduct.'
            AND `is_default` = 1'
        );
    }

    public static function getProductListByMpSupplierIdAndIdSeller($idMpSupplier, $idSeller, $id_lang)
    {
        if ($idMpSupplier && $idSeller) {
            return DB::getInstance()->executeS(
                'SELECT pl.`name` as product_name, mpp.`id_mp_product`
                FROM `'._DB_PREFIX_.'wk_mp_seller_product` mpp
                JOIN `' . _DB_PREFIX_ . 'product` p ON (p.id_product = mpp.id_ps_product)
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
				JOIN `'._DB_PREFIX_.'product_supplier` ps
				ON(ps.`id_product` = p.`id_product`)
                JOIN `'._DB_PREFIX_.'wk_mp_suppliers` mps
				ON(ps.`id_supplier` = mps.`id_ps_supplier`)
				WHERE mpp.`id_seller` = '.(int) $idSeller.'
				AND mps.`id_wk_mp_supplier` = '.(int) $idMpSupplier.'
				AND pl.`id_lang` = '.(int) $id_lang
            );
        }

        return false;
    }

    /**
     * Get seller product suppliers by MP product id
     *
     * @param int $idMpProduct MP Product Id
     * @return array
     */
    public static function getInfoByMpProductId($idMpProduct)
    {
        $idPSProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($idMpProduct);
        $getpro = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'product_supplier` ps
            INNER JOIN `'._DB_PREFIX_.'supplier` s ON ps.`id_supplier` = s.`id_supplier`
            WHERE ps.`id_product`='.(int)$idPSProduct
        );
        if (empty($getpro)) {
            return false;
        }

        return $getpro;
    }

    /**
     * Get suppliers for product by Seller id
     *
     * @param int $idSeller Seller Id
     * @return array
     */
    public function getSuppliersForProductBySellerId($idSeller)
    {
        if ($idSeller) {
            if (Configuration::get('WK_MP_PRODUCT_SUPPLIER_ADMIN')) {
                return DB::getInstance()->executeS(
                    'SELECT s.`id_supplier`, s.`name` FROM `'._DB_PREFIX_.'supplier` s
                    '.Shop::addSqlAssociation('supplier', 's').'
					WHERE s.`id_supplier` NOT IN (
                        SELECT pss.`id_supplier` FROM `'._DB_PREFIX_.'supplier` pss
                        JOIN `'._DB_PREFIX_.'wk_mp_suppliers` mps
                        ON (pss.`id_supplier` = mps.`id_ps_supplier`)
                        WHERE mps.`id_seller` != '.(int) $idSeller.'
                    )
                    AND s.`active` = 1 '
                );
            } else {
                return DB::getInstance()->executeS(
                    'SELECT pss.`id_supplier`, pss.`name` FROM `'._DB_PREFIX_.'supplier` pss
                    '.Shop::addSqlAssociation('supplier', 'pss').'
                    JOIN `'._DB_PREFIX_.'wk_mp_suppliers` mps
                    ON (pss.`id_supplier` = mps.`id_ps_supplier`)
                    WHERE mps.`id_seller` = '.(int) $idSeller.'
                    AND pss.`active` = 1'
                );
            }
        }

        return false;
    }
    
    /**
     * Delete suppliers for product by PS product id
     *
     * @param int $idPsProduct PS Product Id
     * @return array
     */
    public function deleteSuppliersByPsProductId($idPsProduct)
    {
        if ($idPsProduct) {
            $obj_product = new Product($idPsProduct);
            $obj_product->id_supplier = 0;
            $obj_product->save();
            DB::getInstance()->delete('product_supplier', '`id_product` = '.(int) $idPsProduct);
        }
    }
}
