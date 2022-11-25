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

class WkMpManufacturers extends ObjectModel
{
    public $id_wk_mp_manufacturers;
    public $id_seller;
    public $id_ps_manuf;
    public $id_ps_manuf_address;

    public static $definition = array(
        'table' => 'wk_mp_manufacturers',
        'primary' => 'id_wk_mp_manufacturers',
        'fields' => array(
            'id_seller' => array('type' => self::TYPE_INT),
            'id_ps_manuf' => array('type' => self::TYPE_INT),
            'id_ps_manuf_address' => array('type' => self::TYPE_INT),
        ),
    );

    /**
     * Override delete function to delete related records
     *
     * @param int $id MP Product ID
     * @return bool value
     */
    public function delete()
    {
        $mp_manuf_id = $this->id;
        $idPsManuf = $this->id_ps_manuf;

        if ($idPsManuf) {
            $obj_manufactor = new Manufacturer($idPsManuf);
            $obj_manufactor->delete();

            Db::getInstance()->delete('address', 'id_manufacturer ='.(int) $idPsManuf);
            Db::getInstance()->update('product', array('id_manufacturer' => 0), 'id_manufacturer ='.(int) $idPsManuf);
        }

        Db::getInstance()->delete('wk_mp_manufacturers', 'id_wk_mp_manufacturers ='.(int) $mp_manuf_id);

        if (!parent::delete()) {
            return false;
        }

        return true;
    }

    /**
     * Get Manufacturer details by using Seller ID
     *
     * @param int $sellerid Seller ID
     * @return array
     */
    public function getManufacturerInfo($sellerid)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_mp_manufacturers`
        WHERE `id_seller` = '.(int) $sellerid;

        $sellerManufacturers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (empty($sellerManufacturers)) {
            return false;
        } else {
            foreach ($sellerManufacturers as &$manufacturers) {
                $manufacturer = new Manufacturer($manufacturers['id_ps_manuf']);
                $manufacturers['name'] = $manufacturer->name;
                $manufacturers['active'] = $manufacturer->active;
            }
        }

        return $sellerManufacturers;
    }

    /**
     * Get product for add Manufacturer details by using Seller ID and language
     *
     * @param int $sellerid Seller ID
     * @param int $idLang Language ID
     * @return array
     */
    public function getProductsForAddManufacturerBySellerId($sellerid, $idLang = false)
    {
        $sql = 'SELECT mpsp.`id_ps_product`, mpsp.`id_mp_product`, pl.`name` as `product_name`
                FROM `'._DB_PREFIX_.'wk_mp_seller_product` mpsp
                JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = mpsp.`id_ps_product`)
                JOIN `'._DB_PREFIX_.'product_shop` ps ON (p.`id_product` = ps.`id_product`)
                JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = mpsp.`id_ps_product`)
                WHERE p.`active` = 1
                AND mpsp.`id_seller` = '.(int) $sellerid
                .' AND ps.`id_shop` = '. (int) Context::getContext()->shop->id
                .' AND pl.`id_lang` = '.(int) $idLang.' GROUP BY p.`id_product`';
        
        $result = false;
        if ($sellerid) {
            $product_list = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if ($product_list) {
                $result = $product_list;
            }
        }

        return $result;
    }

    /**
     * Get MP Manufacturer details by using mp manufacturer id
     *
     * @param int $mpManufId Mp Manufacturer Id
     * @return array
     */
    public static function getMpManufacturerAllDetails($mpManufId)
    {
        $objMpManufacturer = new WkMpManufacturers();
        $manufacInfo = $objMpManufacturer->getAllMpManufacturesById($mpManufId);
        if ($manufacInfo) {
            $manufacturer = new Manufacturer($manufacInfo['id_ps_manuf']);
            if ($manufacturer) {
                $manufacInfo['name'] = $manufacturer->name;
                $manufacInfo['short_description'] = $manufacturer->short_description;
                $manufacInfo['description'] = $manufacturer->description;
                $manufacInfo['meta_title'] = $manufacturer->meta_title;
                $manufacInfo['meta_description'] = $manufacturer->meta_description;
                $manufacInfo['meta_keywords'] = $manufacturer->meta_keywords;
            }
            $manufacInfo['active'] = $manufacturer->active;

            $mAddress = $manufacturer->getAddresses(Context::getContext()->language->id);
            if (!empty($mAddress)) {
                $manufacInfo['phone'] = $mAddress[0]['phone_mobile'];
                $manufacInfo['address'] = $mAddress[0]['address1'];
                $manufacInfo['zipcode'] = $mAddress[0]['postcode'];
                $manufacInfo['city'] = $mAddress[0]['city'];
                $manufacInfo['country'] = $mAddress[0]['id_country'];
                $manufacInfo['dni'] = $mAddress[0]['dni'];
                $manufacInfo['state'] = $mAddress[0]['state'];
            } else {
                $manufacInfo['phone'] = '';
                $manufacInfo['address'] = '';
                $manufacInfo['zipcode'] = '';
                $manufacInfo['city'] = '';
                $manufacInfo['country'] = '';
                $manufacInfo['dni'] = '';
                $manufacInfo['state'] = '';
            }
            return $manufacInfo;
        } else {
            return false;
        }
    }

    /**
     * Get Only MP Manufacturer records by using mp manufacturer id
     *
     * @param int $mpManufId Mp Manufacturer Id
     * @return array
     */
    public function getAllMpManufacturesById($id)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_mp_manufacturers`
                WHERE `id_wk_mp_manufacturers`='.(int) $id);

        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * Get seller language by using mp seller id
     *
     * @param int $mpIdSeller Mp seller Id
     * @return int
     */
    public static function getCurrentLang($mpIdSeller)
    {
        $currLang = 0;
        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
            Context::getContext()->smarty->assign('allow_multilang', 1);
            $currLang = WkMpSeller::getSellerDefaultLanguage($mpIdSeller);
        } else {
            Context::getContext()->smarty->assign('allow_multilang', 0);

            if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {
                $currLang = Configuration::get('PS_LANG_DEFAULT');
            } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {
                $currLang = WkMpSeller::getSellerDefaultLanguage($mpIdSeller);
            }
        }

        return $currLang;
    }

    /**
     * Upload manufacturer logo to PS Brand
     *
     * @param int $psid Product Id
     * @param int $mpIdManuf Mp manufacturer Id
     * @param int $mpImageDir Mp image Directory
     */
    public function uploadManufacturerLogoToPs($psid, $mpIdManuf, $mpImageDir)
    {
        $oldPath = $mpImageDir.$mpIdManuf.'.jpg';
        if (file_exists($oldPath)) {
            $newPath = _PS_IMG_DIR_.'m/'.$psid;
            $imagesTypes = ImageType::getImagesTypes('manufacturers');

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

    /**
     * Upload manufacturer details
     *
     * @param int $ps_id_manuf Product Id Manufacturer
     * @param int $manuf_id Mp manufacturer Id
     * @param int $ps_id_manuf_address PS Brand ID Address
     * @return bool
     */
    public function updateMpManufacturerDetails($ps_id_manuf, $manuf_id, $ps_id_manuf_address)
    {
        Db::getInstance()->update(
            'wk_mp_manufacturers',
            array('id_ps_manuf' => $ps_id_manuf,
            'id_ps_manuf_address' => $ps_id_manuf_address),
            'id_wk_mp_manufacturers ='.(int) $manuf_id
        );

        return true;
    }

    /**
     * Send Manufacturer request by email
     *
     * @param int $idSeller Seller Id
     * @param int $manufName Mp manufacturer Name
     * @return bool
     */
    public static function sendManufacturerRequestMail($idSeller, $manufName)
    {
        // Mail to admin for manufacturer request
        $idLang = Context::getContext()->language->id;
        if (Configuration::get('WK_MP_SUPERADMIN_EMAIL')) {
            $adminEmail = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
        } else {
            $idEmployee = WkMpHelper::getSupperAdmin();
            $employee = new Employee($idEmployee);
            $adminEmail = $employee->email;
        }

        if ($adminEmail) {
            $objMpSeller = new WkMpSeller($idSeller);

            $tempPath = _PS_MODULE_DIR_.'marketplace/mails/';
            $templateVars = array(
                '{seller_name}' => $objMpSeller->seller_firstname.' '.$objMpSeller->seller_lastname,
                '{seller_email}' => $objMpSeller->business_email,
                '{manufacturer_name}' => $manufName,
            );

            Mail::Send(
                $idLang,
                'manufacturer_request',
                Mail::l('Manufacturer Request', $idLang),
                $templateVars,
                $adminEmail,
                null,
                null,
                'Marketplace Manufacturer',
                null,
                null,
                $tempPath,
                false,
                null,
                null
            );
        }
    }

    /**
     * Update PS Brand Status
     *
     * @param int $status Active/Inactive
     * @param int $idPsManuf PS manufacturer Id
     * @return bool
     */
    public static function updatePsManufacturerStatus($status, $idPsManuf)
    {
        $updatemanuf = Db::getInstance()->update(
            'manufacturer',
            array('active' => $status),
            'id_manufacturer ='.(int) $idPsManuf
        );

        if (empty($updatemanuf)) {
            return false;
        }

        return true;
    }

    /**
     * Get allowed seller manufacturer
     *
     * @param int $mpIdSeller Mp Seller Id
     * @param int $langId Language Id
     * @return array
     */
    public function sellerManufacturers($mpIdSeller, $langId)
    {
        $objMpManuf = new self();
        if (Configuration::get('WK_MP_PRODUCT_MANUFACTURER_ADMIN') == 1) {
            $manufacturers = $objMpManuf->getSellerPsManufacturers($mpIdSeller, $langId);
        } else {
            $manufacturers = $objMpManuf->getOnlySellerManufacturers($mpIdSeller, $langId);
        }

        return $manufacturers;
    }

    /**
     * Get allowed seller and admin manufacturer
     *
     * @param int $mpIdSeller Mp Seller Id
     * @param int $langId Language Id
     * @return array
     */
    public function getSellerPsManufacturers($mpIdSeller, $langId)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * From '._DB_PREFIX_.'manufacturer psm
                INNER JOIN '._DB_PREFIX_.'manufacturer_lang psml WHERE psm.id_manufacturer=psml.id_manufacturer
                AND psml.id_lang='.(int) $langId.' AND psm.`active` = 1 AND psm.id_manufacturer
                NOT IN(SELECT id_ps_manuf FROM '._DB_PREFIX_.'wk_mp_manufacturers
                WHERE id_seller != '.(int) $mpIdSeller.' AND psm.`active` = 1)');

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get only seller's manufacturer
     *
     * @param int $mpIdSeller Mp Seller Id
     * @param int $langId Language Id
     * @return array
     */
    public function getOnlySellerManufacturers($mpIdSeller, $langId)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM '._DB_PREFIX_.'manufacturer psm
                INNER JOIN '._DB_PREFIX_.'manufacturer_lang psml WHERE psm.id_manufacturer=psml.id_manufacturer
                AND psml.id_lang='.(int) $langId.' AND psm.`active` = 1 AND psm.id_manufacturer
                IN(SELECT id_ps_manuf FROM '._DB_PREFIX_.'wk_mp_manufacturers
                WHERE id_seller = '.(int) $mpIdSeller.' AND psm.`active` = 1)');

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get seller product by mp product id
     *
     * @param int $mpproductid Mp Product Id
     * @return array
     */
    public function getSellerProductByMpProductId($mpproductid)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'product` p INNER JOIN `'._DB_PREFIX_.'wk_mp_seller_product` mpp ON
        p.`id_product`=mpp.`id_ps_product` WHERE p.`id_manufacturer` > 0 AND mpp.`id_mp_product`='.(int)$mpproductid;
        $getpro = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if (empty($getpro)) {
            return false;
        }

        return $getpro;
    }

    /**
     * Get seller product by PS product id
     *
     * @param int $psprodid PS Product Id
     * @return array
     */
    public static function getSellerProductByManufProdId($psprodid)
    {
        $getpro = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'product` p INNER JOIN `'._DB_PREFIX_.'wk_mp_seller_product` mpp ON
        p.`id_product`=mpp.`id_ps_product` WHERE p.`id_manufacturer` > 0 AND mpp.`id_ps_product`='.(int)$psprodid
        );
        if (empty($getpro)) {
            return false;
        }

        return $getpro;
    }

    /**
     * Get assigned seller product by MP product id
     *
     * @param int $mpSellerId MP Product Id
     * @return array
     */
    public static function getSellerProductAssigned($mpSellerId)
    {
        $getproductsmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'product` p INNER JOIN `'._DB_PREFIX_.'wk_mp_seller_product` mpp ON
            p.`id_product`=mpp.`id_ps_product` WHERE p.`id_manufacturer` > 0 AND mpp.`id_seller`='.(int)$mpSellerId
        );
        if (empty($getproductsmp)) {
            return false;
        }

        return $getproductsmp;
    }

    /**
     * Get seller product by MP manufacturer id
     *
     * @param int $mpManufId MP manufacturer Id
     * @return array
     */
    public static function getSellerProductByMpManufId($mpManufId)
    {
        $getproductsmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT mpp.* FROM `'._DB_PREFIX_.'product` p
            INNER JOIN `'._DB_PREFIX_.'wk_mp_manufacturers` mpf ON
            p.`id_manufacturer`=mpf.`id_ps_manuf` 
            INNER JOIN `'._DB_PREFIX_.'wk_mp_seller_product` mpp ON
            p.`id_product`=mpp.`id_ps_product`
            WHERE p.`id_manufacturer` > 0 AND mpf.`id_wk_mp_manufacturers`='.(int)$mpManufId
        );

        if (empty($getproductsmp)) {
            return false;
        }

        return $getproductsmp;
    }

    /**
     * Get PS product manufacturer id
     *
     * @param int $psProdId PS Product Id
     * @param int $idPsManuf PS manufacturer Id
     * @return bool
     */
    public static function updatePsProductManufIdByPsProductId($psProdId, $idPsManuf)
    {
        $updateproductmanuf = Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'product`
        SET `id_manufacturer` ='.(int) $idPsManuf.' WHERE `id_product` = '.(int) $psProdId);

        if (empty($updateproductmanuf)) {
            return false;
        }

        return true;
    }

    /**
     * Get PS product manufacturer id
     *
     * @param int $psProdId PS Product Id
     * @param int $idPsManuf PS manufacturer Id
     * @return array
     */
    public static function countProductsByPsManufId($idMpManuf)
    {
        $countproduct = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT COUNT(*)
        FROM `'._DB_PREFIX_.'product` p INNER JOIN `'._DB_PREFIX_.'wk_mp_manufacturers` mpf ON
        p.`id_manufacturer`=mpf.`id_ps_manuf` WHERE mpf.`id_wk_mp_manufacturers` = '.(int) $idMpManuf);

        return $countproduct;
    }
    
    /**
     * Get PS product manufacturer id
     *
     * @param int $idPsProduct PS Product Id
     * @return bool
     */
    public static function updateProductManufByPsIdProduct($idPsProduct)
    {
        return Db::getInstance()->update(
            'product',
            array('id_manufacturer' => 0),
            'id_product ='.(int) $idPsProduct
        );
    }
}
