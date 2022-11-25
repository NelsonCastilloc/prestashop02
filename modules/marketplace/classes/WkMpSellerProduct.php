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

use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductType;

class WkMpSellerProduct extends ObjectModel
{
    public $id_mp_product;
    public $id_seller;
    public $id_ps_product;  // prestashop id product first time 0 when product is not created in ps
    public $id_mp_shop_default; //Shop Id for this seller's product
    public $id_mp_duplicate_product_parent;
    public $status_before_deactivate; //Product Status before seller activated
    public $admin_assigned;  // if product assigned by admin to seller this will be 1
    public $admin_approved; //Approved by Admin or not
    public $is_pack_product;
    public $pack_stock_type;
    public $date_add;
    public $date_upd;

    public $checkCategories;
    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id);

        $this->checkCategories = false;
    }

    public static $definition = array(
        'table' => 'wk_mp_seller_product',
        'primary' => 'id_mp_product',
        'fields' => array(
            'id_seller' => array('type' => self::TYPE_INT, 'required' => false),
            'id_ps_product' => array('type' => self::TYPE_INT, 'required' => false),
            'id_mp_shop_default' => array('type' => self::TYPE_INT, 'required' => false),
            'id_mp_duplicate_product_parent' => array('type' => self::TYPE_INT, 'required' => false),
            'status_before_deactivate' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'admin_assigned' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'admin_approved' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'is_pack_product' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'pack_stock_type' => array('type' => self::TYPE_INT),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
        ),
    );

    public function toggleStatus()
    {
        return true;
    }

    public function delete()
    {
        if (!$this->deleteSellerProduct($this->id)
            || !parent::delete()) {
            return false;
        }

        return true;
    }

    /**
     * Delete seller product from all tables if product is activated.
     *
     * @param int $mpIdProduct seller's product id
     *
     * @return bool
     */
    public function deleteSellerProduct($mpIdProduct)
    {
        $objMpProduct = new self($mpIdProduct);
        //This hook must be call before delete of mp product details
        // otherwise we can't check mp product was exist or not
        Hook::exec('actionMpProductDelete', array('id_mp_product' => (int) $mpIdProduct));
        if ($objMpProduct->id) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
            $productName = '';
            if (!$objMpProduct->admin_assigned && $objMpProduct->id_ps_product) {
                //delete only seller created products not the admin assigned products from catalog list
                $objProduct = new Product($objMpProduct->id_ps_product, true, $idLang);
                $productName = $objProduct->name;

                $objProduct->delete();
            }

            //mail to admin or seller on mp product delete
            if (Configuration::get('WK_MP_MAIL_PRODUCT_DELETE')) {
                $mpProduct = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct, $idLang);
                if ($mpProduct) {
                    $sellerDetail = WkMpSeller::getSeller($mpProduct['id_seller'], $idLang);
                    if ($sellerDetail) {
                        $sellerName = $sellerDetail['seller_firstname'].' '.$sellerDetail['seller_lastname'];
                        $shopName = $sellerDetail['shop_name'];
                        $sellerPhone = $sellerDetail['phone'];
                        $sellerEmail = $sellerDetail['business_email'];
                        $mailLangId = $sellerDetail['default_lang'];

                        if (Tools::getValue('controller') == 'productlist'
                        || Tools::getValue('controller') == 'updateproduct') {
                            //mail to admin if seller delete product from product list page
                            $mailLangId = Configuration::get('PS_LANG_DEFAULT');
                            $mailTo = 'admin';
                        } else {
                            $mailTo = 'seller';
                        }

                        WkMpSellerProduct::mailOnProductDelete(
                            $productName,
                            $sellerName,
                            $sellerPhone,
                            $shopName,
                            $sellerEmail,
                            $mailLangId,
                            $mailTo
                        );
                    }
                }
            }
        }

        return true;
    }

    public static function getPsIdProductByMpIdProduct($idMpProduct)
    {
        return Db::getInstance()->getValue(
            'SELECT `id_ps_product` FROM `'._DB_PREFIX_.'wk_mp_seller_product`
            WHERE `id_mp_product` = '.(int) $idMpProduct
        );
    }

    public static function getMpIdProductByPsIdProduct($idPsProduct)
    {
        return Db::getInstance()->getValue(
            'SELECT `id_mp_product` FROM `'._DB_PREFIX_.'wk_mp_seller_product`
            WHERE `id_ps_product` = '.(int) $idPsProduct
        );
    }

    /**
     * Get Seller Product By Using Seller Id product.
     *
     * @param int  $idMpProduct Seller Id Product
     * @param bool $idLang      language ID
     *
     * @return array/bool array containing seller's product
     */
    public static function getSellerProductByIdProduct($idMpProduct, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }

        $productData = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
            JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = msp.`id_ps_product`)
            ' . Shop::addSqlAssociation('product', 'p') . '
            JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`'
            . Shop::addSqlRestrictionOnLang('pl') . ')
            WHERE msp.`id_mp_product` = '.(int) $idMpProduct.' AND pl.`id_lang` = '.(int) $idLang
        );
        if ($productData) {
            $idShopDefault = Context::getContext()->shop->id;
            if (isset($productData['id_mp_shop_default'])
            && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //In case of all shops
                $idShopDefault = (int) $productData['id_mp_shop_default'];
            }
            $productData['quantity'] = StockAvailable::getQuantityAvailableByProduct(
                $productData['id_ps_product'],
                null,
                $idShopDefault
            );
            $productData['unit_price'] = self::getProductUnitPrice(
                $productData['price'],
                $productData['unit_price_ratio']
            );

            return $productData;
        }
        return false;
    }

    /**
     * Get Seller Product Details by using prestashop product ID.
     *
     * @param int $idPsProduct Prestashop Product ID
     * @param bool $idLang      language ID
     *
     * @return array/boolean If exist then array of current product else false
     */
    public static function getSellerProductByPsIdProduct($idPsProduct, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }

        $productData = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
            JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = msp.`id_ps_product`)
            ' . Shop::addSqlAssociation('product', 'p') . '
            JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`'
            . Shop::addSqlRestrictionOnLang('pl') . ')
            WHERE msp.`id_ps_product` = '.(int) $idPsProduct.' AND pl.`id_lang` = '.(int) $idLang
        );
        if ($productData) {
            $idShopDefault = Context::getContext()->shop->id;
            if (isset($productData['id_mp_shop_default'])
            && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //In case of all shops
                $idShopDefault = (int) $productData['id_mp_shop_default'];
            }
            $productData['quantity'] = StockAvailable::getQuantityAvailableByProduct(
                $productData['id_ps_product'],
                null,
                $idShopDefault
            );
            $productData['unit_price'] = self::getProductUnitPrice(
                $productData['price'],
                $productData['unit_price_ratio']
            );

            return $productData;
        }
        return false;
    }

    /**
     * Get Seller Product Details by using prestashop product ID without shop context.
     *
     * @param int $idPsProduct Prestashop Product ID
     * @param bool $idLang      language ID
     *
     * @return array/boolean If exist then array of current product else false
     */
    public static function getSellerProductInfoByPsIdProduct($idPsProduct, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }

        $productData = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
            JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = msp.`id_ps_product`)
            JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`'
            . Shop::addSqlRestrictionOnLang('pl') . ')
            WHERE msp.`id_ps_product` = '.(int) $idPsProduct.' AND pl.`id_lang` = '.(int) $idLang
        );
        if ($productData) {
            $idShopDefault = Context::getContext()->shop->id;
            if (isset($productData['id_mp_shop_default'])
            && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //In case of all shops
                $idShopDefault = (int) $productData['id_mp_shop_default'];
            }
            $productData['quantity'] = StockAvailable::getQuantityAvailableByProduct(
                $productData['id_ps_product'],
                null,
                $idShopDefault
            );
            $productData['unit_price'] = self::getProductUnitPrice(
                $productData['price'],
                $productData['unit_price_ratio']
            );

            return $productData;
        }
        return false;
    }

    /**
     * Get Seller Product By Using Seller Id product.
     *
     * @param int  $idMpProduct Seller Id Product
     * @param bool $idLang      language ID
     *
     * @return array/bool array containing seller's product
     */
    public static function getSellerProductWithLang($idMpProduct)
    {
        $productData = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
            JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = msp.`id_ps_product`)
            ' . Shop::addSqlAssociation('product', 'p') . '
            WHERE msp.`id_mp_product` = '.(int) $idMpProduct
        );
        if ($productData) {
            $idShopDefault = Context::getContext()->shop->id;
            if (isset($productData['id_mp_shop_default'])
            && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //In case of all shops
                $idShopDefault = (int) $productData['id_mp_shop_default'];
            }
            $productData['quantity'] = StockAvailable::getQuantityAvailableByProduct(
                $productData['id_ps_product'],
                null,
                $idShopDefault
            );
            $productData['unit_price'] = self::getProductUnitPrice(
                $productData['price'],
                $productData['unit_price_ratio']
            );
            $productData['out_of_stock'] = StockAvailable::outOfStock(
                $productData['id_ps_product'],
                $idShopDefault
            );

            $langDetail = Db::getInstance()->executeS(
                'SELECT * FROM  `'._DB_PREFIX_.'product_lang` pl
                WHERE pl.`id_product` = '.(int) $productData['id_ps_product'] . Shop::addSqlRestrictionOnLang('pl')
            );
            if ($langDetail) {
                foreach ($langDetail as $detail) {
                    $productData['name'][$detail['id_lang']] = $detail['name'];
                    $productData['description'][$detail['id_lang']] = $detail['description'];
                    $productData['description_short'][$detail['id_lang']] = $detail['description_short'];
                    $productData['link_rewrite'][$detail['id_lang']] = $detail['link_rewrite'];
                    $productData['meta_title'][$detail['id_lang']] = $detail['meta_title'];
                    $productData['meta_description'][$detail['id_lang']] = $detail['meta_description'];
                    $productData['meta_keywords'][$detail['id_lang']] = $detail['meta_keywords'];
                    $productData['available_now'][$detail['id_lang']] = $detail['available_now'];
                    $productData['available_later'][$detail['id_lang']] = $detail['available_later'];
                    $productData['delivery_in_stock'][$detail['id_lang']] = $detail['delivery_in_stock'];
                    $productData['delivery_out_stock'][$detail['id_lang']] = $detail['delivery_out_stock'];
                }
            }

            return $productData;
        }
        return false;
    }

    public static function getProductUnitPrice($price, $unitPriceRatio)
    {
        return Tools::ps_round(($unitPriceRatio != 0 ? $price / $unitPriceRatio : 0), 2);
    }

    public function saveSellerProductToPs($productInfo, $active = false, $editIdPsProduct = false)
    {
        Hook::exec(
            'actionBeforeSaveSellerProductToPs',
            array(
                'ps_id_product' => $editIdPsProduct ? $editIdPsProduct : false,
            )
        );

        if ($editIdPsProduct) {
            $product = new Product($editIdPsProduct); //Update Product
        } else {
            $product = new Product(); //Add Product
        }

        $idShopDefault = Context::getContext()->shop->id;
        if (isset($productInfo['id_mp_shop_default']) && $productInfo['id_mp_shop_default']) {
            $idShopDefault = (int) $productInfo['id_mp_shop_default'];
        }

        $product->name = array();
        $product->description = array();
        $product->description_short = array();
        $product->meta_title = array();
        $product->meta_description = array();
        $product->link_rewrite = array();
        $product->available_now = array();
        $product->available_later = array();
        $product->delivery_in_stock = array();
        $product->delivery_out_stock = array();

        foreach (Language::getLanguages(false) as $lang) {
            if (isset($productInfo['name'][$lang['id_lang']])) {
                $product->name[$lang['id_lang']] = $productInfo['name'][$lang['id_lang']];
            }
            if (isset($productInfo['description'][$lang['id_lang']])) {
                $product->description[$lang['id_lang']] = $productInfo['description'][$lang['id_lang']];
            }
            if (isset($productInfo['short_description'][$lang['id_lang']])) {
                $product->description_short[$lang['id_lang']] = $productInfo['short_description'][$lang['id_lang']];
            }
            if (isset($productInfo['meta_title'][$lang['id_lang']])) {
                $product->meta_title[$lang['id_lang']] = $productInfo['meta_title'][$lang['id_lang']];
            }
            if (isset($productInfo['meta_description'][$lang['id_lang']])) {
                $product->meta_description[$lang['id_lang']] = $productInfo['meta_description'][$lang['id_lang']];
            }
            if (isset($productInfo['link_rewrite'][$lang['id_lang']])) {
                $product->link_rewrite[$lang['id_lang']] = $productInfo['link_rewrite'][$lang['id_lang']];
            }
            if (isset($productInfo['available_now'][$lang['id_lang']])) {
                $product->available_now[$lang['id_lang']] = $productInfo['available_now'][$lang['id_lang']];
            }
            if (isset($productInfo['available_later'][$lang['id_lang']])) {
                $product->available_later[$lang['id_lang']] = $productInfo['available_later'][$lang['id_lang']];
            }

            if (_PS_VERSION_ >= '1.7.3.0') {
                //Prestashop added this feature in PS V1.7.3.0 and above
                if (isset($productInfo['delivery_in_stock'][$lang['id_lang']])) {
                    $product->delivery_in_stock[$lang['id_lang']] = $productInfo['delivery_in_stock'][$lang['id_lang']];
                }
                if (isset($productInfo['delivery_out_stock'][$lang['id_lang']])) {
                    $product->delivery_out_stock[$lang['id_lang']] = $productInfo['delivery_out_stock'][$lang['id_lang']];
                }
            }
        }

        $product->id_shop_default = $idShopDefault;
        $product->id_category_default = (int) $productInfo['id_category_default'];
        $product->active = $active;
        $product->indexed = 1;

        if (isset($productInfo['show_condition'])) {
            $product->show_condition = $productInfo['show_condition'];
        }
        if (isset($productInfo['condition'])) {
            $product->condition = $productInfo['condition'];
        }
        if (isset($productInfo['minimal_quantity'])) {
            $product->minimal_quantity = $productInfo['minimal_quantity'];
        }

        //Price Section
        if ($editIdPsProduct) {
            if (isset($productInfo['price'])) {
                $product->price = $productInfo['price'];
            }
        } else {
            $product->price = $productInfo['price'];
        }
        if (isset($productInfo['wholesale_price'])) {
            $product->wholesale_price = $productInfo['wholesale_price'];
        }
        if (isset($productInfo['unit_price'])) {
            $product->unit_price = $productInfo['unit_price']; //ps automatically get unit_price_ratio
        }
        if (isset($productInfo['unity'])) {
            $product->unity = $productInfo['unity'];
        }
        if (isset($productInfo['on_sale'])) {
            $product->on_sale = $productInfo['on_sale'];
        }

        if (isset($productInfo['height'])) {
            $product->height = $productInfo['height'];
        }
        if (isset($productInfo['width'])) {
            $product->width = $productInfo['width'];
        }
        if (isset($productInfo['depth'])) {
            $product->depth = $productInfo['depth'];
        }
        if (isset($productInfo['weight'])) {
            $product->weight = $productInfo['weight'];
        }
        if (isset($productInfo['additional_shipping_cost'])) {
            $product->additional_shipping_cost = $productInfo['additional_shipping_cost'];
        }

        if (isset($productInfo['out_of_stock'])) {
            $product->out_of_stock = $productInfo['out_of_stock'];
        }
        if (isset($productInfo['available_date'])) {
            $product->available_date = $productInfo['available_date'];
        }

        if (isset($productInfo['location'])) {
            $product->location = $productInfo['location'];
        }

        if (isset($productInfo['pack_stock_type'])) {
            $product->pack_stock_type = $productInfo['pack_stock_type'];
        }

        if (isset($productInfo['redirect_type'])) {
            $product->redirect_type = $productInfo['redirect_type'];
        }

        if (isset($productInfo['id_type_redirected'])) {
            $product->id_type_redirected = $productInfo['id_type_redirected'];
        }

        if (isset($productInfo['cache_is_pack'])) {
            $product->cache_is_pack = $productInfo['cache_is_pack'];
        }

        if (_PS_VERSION_ >= '1.7.3.0') {
            //Prestashop added this feature in PS V1.7.3.0 and above
            if (isset($productInfo['additional_delivery_times'])) {
                $product->additional_delivery_times = $productInfo['additional_delivery_times'];
            }
            if (isset($productInfo['low_stock_threshold'])) {
                $product->low_stock_threshold = $productInfo['low_stock_threshold'];
            }
            if (isset($productInfo['low_stock_alert'])) {
                $product->low_stock_alert = $productInfo['low_stock_alert'];
            }
        }

        //Product Visibility Options
        if (isset($productInfo['available_for_order'])) {
            $product->available_for_order = $productInfo['available_for_order'];
        }
        if (isset($productInfo['show_price'])) {
            $product->show_price = $productInfo['show_price'];
        }
        if (isset($productInfo['online_only'])) {
            $product->online_only = $productInfo['online_only'];
        }
        if (isset($productInfo['visibility'])) {
            $product->visibility = $productInfo['visibility'];
        }

        if (isset($productInfo['reference'])) {
            $product->reference = $productInfo['reference'];
        }
        if (isset($productInfo['upc'])) {
            $product->upc = $productInfo['upc'];
        }
        if (isset($productInfo['ean13'])) {
            $product->ean13 = $productInfo['ean13'];
        }
        if (isset($productInfo['isbn'])) {
            $product->isbn = $productInfo['isbn'];
        }

        if (isset($productInfo['mpn'])) {
            $product->mpn = $productInfo['mpn'];
        }

        if (isset($productInfo['id_tax_rules_group'])) {
            $idTaxRulesGroup = $productInfo['id_tax_rules_group'];
        } else {
            $idTaxRulesGroup = 1; //If id_tax_rules_group not send then send default id_tax_rules_group as 1
        }
        $objTaxRule = new TaxRulesGroup($idTaxRulesGroup);
        if ($objTaxRule->active) {
            $product->id_tax_rules_group = $idTaxRulesGroup;
        } else {
            $product->id_tax_rules_group = 0;
        }

        if (isset($productInfo['product_type'])) {
            $product->product_type = pSQL($productInfo['product_type']);
        }
        if (isset($productInfo['is_virtual'])) {
            $product->is_virtual = (int) $productInfo['is_virtual'];
        }
        if (isset($productInfo['id_manufacturer'])) {
            $product->id_manufacturer = (int) $productInfo['id_manufacturer'];
        }

        $product->save();
        if ($psIdProduct = $product->id) {
            foreach (Language::getLanguages(false) as $lang) {
                if (isset($productInfo['link_rewrite'][$lang['id_lang']])) {
                    Search::indexation($productInfo['link_rewrite'][$lang['id_lang']], $psIdProduct);
                }
            }

            if (isset($productInfo['quantity'])) {
                $quantity = (int) $productInfo['quantity'];
            }
            if (isset($productInfo['id_category_default'])) {
                $categoryID = (int) $productInfo['id_category_default'];
            } else {
                $categoryID = 0;
            }
            if (isset($productInfo['category'])) {
                $categoryIds = $productInfo['category'];
            } else {
                $categoryIds = array(2); //2 for home
            }

            if (!$editIdPsProduct) {
                //Add product
                if ($categoryID > 0) {
                    if ($categoryIds) {
                        $product->addToCategories($categoryIds);
                    }
                }

                //Set product quantity on PS
                if (isset($quantity)) {
                    StockAvailable::updateQuantity($psIdProduct, null, $quantity, $idShopDefault);
                }

                if (isset($productInfo['ps_id_carrier_reference']) && $productInfo['ps_id_carrier_reference']) {
                    $product->setCarriers($productInfo['ps_id_carrier_reference']);
                }
            } else {
                //Update Product
                if ($categoryID > 0) {
                    if ($categoryIds) {
                        $product->updateCategories($categoryIds);
                    }
                }

                //Set product quantity on PS
                if (isset($quantity)) {
                    StockAvailable::setQuantity($psIdProduct, 0, $quantity, $idShopDefault);
                }

                if (isset($productInfo['ps_id_carrier_reference'])) {
                    if ($productInfo['ps_id_carrier_reference']) {
                        $product->setCarriers($productInfo['ps_id_carrier_reference']);
                    } else {
                        $this->removeCarriers($psIdProduct);
                    }
                }
            }

            //Entry for deny orders, allow orders or Default
            if (isset($productInfo['out_of_stock'])) {
                StockAvailable::setProductOutOfStock(
                    $psIdProduct,
                    $productInfo['out_of_stock'],
                    $idShopDefault
                );
            }

            if (isset($productInfo['featureAllowed']) && $productInfo['featureAllowed']
            && isset($productInfo['product_feature'])
            && isset($productInfo['default_lang']) && $productInfo['default_lang']
            ) {
                // add product features
                WkMpProductFeature::saveProductFeature(
                    $psIdProduct,
                    $productInfo['product_feature'],
                    $productInfo['default_lang']
                );
            }

            // Set Location in Stock
            if (isset($productInfo['location'])) {
                StockAvailable::setLocation($psIdProduct, $productInfo['location']);
            }

            return $psIdProduct;
        } else {
            return false;
        }
    }

    public function addSellerProduct($productInfo, $active = false, $sendMailToAdmin = false)
    {
        if (isset($productInfo['id_seller']) && $productInfo['id_seller']) {
            $idSeller = (int) $productInfo['id_seller'];
            $idMpShopDefault = Context::getContext()->shop->id;
            if ($sellerDetail = WkMpSeller::getSeller($idSeller, Configuration::get('PS_LANG_DEFAULT'))) {
                if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
                    //If case of all shop, get shop Id from selected seller
                    $idMpShopDefault = (int) $sellerDetail['id_shop'];
                    Shop::setContext(Shop::CONTEXT_SHOP, $idMpShopDefault);
                }
            }
            $productInfo['id_mp_shop_default'] = (int) $idMpShopDefault;

            $psIdProduct = $this->saveSellerProductToPs($productInfo, $active);
            if ($psIdProduct) {
                $objSellerProduct = new self();
                $objSellerProduct->id_seller = $idSeller;
                if ($active) {
                    $objSellerProduct->status_before_deactivate = 1;
                    $objSellerProduct->admin_approved = 1;
                } else {
                    $objSellerProduct->status_before_deactivate = 0;
                }
                $objSellerProduct->id_ps_product = $psIdProduct;
                $objSellerProduct->id_mp_shop_default = $idMpShopDefault;
                $objSellerProduct->save();
                if ($mpIdProduct = $objSellerProduct->id) {
                    if ($sendMailToAdmin) {
                        //Mail to admin on product add by seller
                        if ($sellerDetail) {
                            $this->mailToAdminOnProductAdd(
                                $productInfo['name'][$productInfo['default_lang']],
                                $sellerDetail['seller_firstname'].' '.$sellerDetail['seller_lastname'],
                                $sellerDetail['phone'],
                                $sellerDetail['shop_name'],
                                $sellerDetail['business_email']
                            );
                        }
                    }

                    $productIds = array(
                        'id_mp_product' => $mpIdProduct,
                        'id_ps_product' => $psIdProduct
                    );

                    Hook::exec(
                        'actionAfterAddSellerProductToPs',
                        $productIds
                    );

                    return $productIds;
                }
            }
        }
        return false;
    }

    public function updateSellerProduct($productInfo, $active, $idPsProduct)
    {
        $idMpShopDefault = Context::getContext()->shop->id;
        if (isset($productInfo['id_ps_shop'])
        && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //If case of all shops
            $idMpShopDefault = (int) $productInfo['id_ps_shop'];
            Shop::setContext(Shop::CONTEXT_SHOP, $idMpShopDefault);
        }
        $productInfo['id_mp_shop_default'] = (int) $idMpShopDefault;

        $psIdProduct = $this->saveSellerProductToPs(
            $productInfo,
            $active,
            $idPsProduct
        );
        if ($psIdProduct) {
            Hook::exec(
                'actionAfterUpdateSellerProductToPs',
                array(
                    'id_ps_product' => $psIdProduct
                )
            );

            return $psIdProduct;
        }

        return false;
    }

    /**
     * Remove carriers from the prestashop table.
     *
     * @param int $id_product Prestashop Id Product
     *
     * @return bool
     */
    public function removeCarriers($id_product)
    {
        return Db::getInstance()->delete('product_carrier', 'id_product='.(int) $id_product);
    }

    /**
     * Get prestashop jstree category
     *
     * @param  int $catId node id of jstree category
     * @param  int $selectedCatIds selected category in jstree
     * @param  int $idLang content display of selected language
     * @return category load
     */
    public function getProductCategory($catId, $selectedCatIds, $idLang, $idSeller = false)
    {
        if ($idSeller && Configuration::get('WK_MP_PRODUCT_CATEGORY_RESTRICTION')) {
            $objMpSeller = new WkMpSeller($idSeller);
            if ($objMpSeller->category_permission) {
                $sellerAllowedCatIds = Tools::jsonDecode(($objMpSeller->category_permission));
            }
        }
        if (!isset($sellerAllowedCatIds) || empty($sellerAllowedCatIds)) {
            $idCategories = array();
            $rootIdCategory = Category::getRootCategory()->id;
            $categories = Category::getAllCategoriesName();
            foreach ($categories as $category) {
                if ($rootIdCategory != $category) {
                    $idCategories[] = $category['id_category'];
                }
            }
            $sellerAllowedCatIds = $idCategories;
        }

        if ($catId == '#') {
            //First time load
            $root = Category::getRootCategory();
            $category = Category::getHomeCategories($idLang, false);
            $categoryArray = array();
            $catkey = 0;
            foreach ($category as $cat) {
                if (in_array($cat['id_category'], $sellerAllowedCatIds)) {
                    $categoryArray[$catkey]['id'] = $cat['id_category'];
                    $categoryArray[$catkey]['text'] = $cat['name'];
                    $subcategory = $this->getPsCategories($cat['id_category'], $idLang);
                    $subChildSelect = false;
                    if ($subcategory) {
                        $categoryArray[$catkey]['children'] = true;
                        $showChildIcon = false;
                        foreach ($subcategory as $subcat) {
                            if (in_array($subcat['id_category'], $selectedCatIds)) {
                                $subChildSelect = true;
                            } else {
                                $this->findChildCategory($subcat['id_category'], $idLang, $selectedCatIds, $sellerAllowedCatIds);
                                if ($this->checkCategories) {
                                    $subChildSelect = true;
                                    $this->checkCategories = false;
                                }
                            }
                            if (in_array($subcat['id_category'], $sellerAllowedCatIds)) {
                                $showChildIcon = true;
                            }
                        }
                        if (!$showChildIcon) {
                            $categoryArray[$catkey]['children'] = false;
                        }
                    } else {
                        $categoryArray[$catkey]['children'] = false;
                    }

                    if (in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == true) {
                        $categoryArray[$catkey]['state'] = array('opened' => true, 'selected' => true);
                    } elseif (in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == false) {
                        $categoryArray[$catkey]['state'] = array('selected' => true);
                    } elseif (!in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == true) {
                        $categoryArray[$catkey]['state'] = array('opened' => true);
                    }
                    $catkey++;
                }
            }

            $treeLoad = array();
            if (in_array($root->id_category, $selectedCatIds)) {
                $treeLoad =  array("id" => $root->id_category,
                                    "text" => $root->name,
                                    "children" => $categoryArray,
                                    "state" => array('opened' => true, 'selected' => true)
                                );
            } else {
                $treeLoad =  array("id" => $root->id_category,
                                    "text" => $root->name,
                                    "children" => $categoryArray,
                                    "state" => array('opened' => true)
                                );
            }
        } else {
            //If sub-category is selected then its automatically called
            $childcategory = $this->getPsCategories($catId, $idLang);
            $treeLoad = array();
            $singletreeLoad = array();
            foreach ($childcategory as $cat) {
                if (in_array($cat['id_category'], $sellerAllowedCatIds)) {
                    $subcategoryArray = array();
                    $subcategoryArray['id'] = $cat['id_category'];
                    $subcategoryArray['text'] = $cat['name'];
                    $subcategory = $this->getPsCategories($cat['id_category'], $idLang);

                    $subChildSelect = false;
                    if ($subcategory) {
                        $subcategoryArray['children'] = true;
                        $showChildIcon = false;
                        foreach ($subcategory as $subcat) {
                            if (in_array($subcat['id_category'], $selectedCatIds)) {
                                $subChildSelect = true;
                            } else {
                                $this->findChildCategory($subcat['id_category'], $idLang, $selectedCatIds, $sellerAllowedCatIds);
                                if ($this->checkCategories) {
                                    $subChildSelect = true;
                                    $this->checkCategories = false;
                                }
                            }
                            if (in_array($subcat['id_category'], $sellerAllowedCatIds)) {
                                $showChildIcon = true;
                            }
                        }
                        if (!$showChildIcon) {
                            $categoryArray['children'] = false;
                        }
                    } else {
                        $subcategoryArray['children'] = false;
                    }

                    if (in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == true) {
                        $subcategoryArray['state'] = array('opened' => true, 'selected' => true);
                    } elseif (in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == false) {
                        $subcategoryArray['state'] = array('selected' => true);
                    } elseif (!in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == true) {
                        $subcategoryArray['state'] = array('opened' => true);
                    }

                    $singletreeLoad[] = $subcategoryArray;
                }
                $treeLoad = $singletreeLoad;
            }
        }

        return $treeLoad;
    }


    public function findChildCategory($id_category, $idLang, $selectedCatIds, $sellerAllowedCatIds)
    {
        $subcategory = $this->getPsCategories($id_category, $idLang);
        if ($subcategory) {
            foreach ($subcategory as $subcat) {
                if (in_array($subcat['id_category'], $selectedCatIds)
                && in_array($subcat['id_category'], $sellerAllowedCatIds)) {
                    $this->checkCategories = true;
                    return;
                } else {
                    $this->findChildCategory($subcat['id_category'], $idLang, $selectedCatIds, $sellerAllowedCatIds);
                }
            }
        } else {
            return false;
        }
    }

    public function getPsCategories($id_parent, $id_lang)
    {
        return Db::getInstance()->executeS(
            'SELECT a.`id_category`, a.`id_parent`, l.`name` FROM `'._DB_PREFIX_.'category` a
            LEFT JOIN `'._DB_PREFIX_.'category_lang` l ON (a.`id_category` = l.`id_category`)
            WHERE a.`id_parent` = '.(int) $id_parent.'
            AND l.`id_lang` = '.(int) $id_lang.'
            AND l.`id_shop` = '.(int) Context::getContext()->shop->id.'
            ORDER BY a.`id_category`'
        );
    }

    /**
     * Get seller's product whether added into prestashop or not.
     *
     * @param int  $idSeller Seller ID
     * @param bool $idLang   Language id
     * @param bool $active   activated or not
     *
     * @return array
     */
    public static function getSellerProduct(
        $idSeller = false,
        $active = 'all',
        $idLang = false,
        $orderby = false,
        $orderway = false,
        $startPoint = false,
        $limitPoint = false
    ) {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }
        if (!$orderway) {
            $orderway = 'desc';
        }
        if (!$startPoint) {
            $startPoint = 0;
        }
        if (!$limitPoint) {
            $limitPoint = 10000000;
        }

        $idShopDefault = (int) Context::getContext()->shop->id;

        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
        JOIN `'._DB_PREFIX_.'product` p
        ON (p.`id_product` = msp.`id_ps_product`)'.Shop::addSqlAssociation('product', 'p').'
        JOIN `'._DB_PREFIX_.'product_lang` pl
        ON (p.`id_product` = pl.`id_product`'.Shop::addSqlRestrictionOnLang('pl').')';

        if ($orderby == 'quantity') {
            $sql .= 'JOIN `'._DB_PREFIX_.'stock_available` stk ON (stk.`id_product` = p.`id_product`)';
        }

        $sql .= ' WHERE pl.`id_lang` = '.(int) $idLang.'
        AND msp.`id_mp_shop_default` = '.(int) $idShopDefault;

        if ($orderby == 'quantity') {
            $sql .= ' AND stk.`id_product_attribute` = 0 AND stk.`id_shop` = '.(int) $idShopDefault;
        }

        if ($idSeller) {
            $sql .= ' AND msp.`id_seller` = '.(int) $idSeller;
        }

        if ($active === true || $active === 1) {
            $sql .= ' AND p.`active` = 1 ';
        } elseif ($active === false || $active === 0) {
            $sql .= ' AND p.`active` = 0 ';
        }

        if (!$orderby) {
            $sql .= ' ORDER BY msp.`id_mp_product` '.pSQL($orderway);
        } elseif ($orderby == 'name') {
            $sql .= ' ORDER BY pl.`name` '.pSQL($orderway);
        } elseif ($orderby == 'quantity') {
            $sql .= ' ORDER BY stk.`quantity` '.pSQL($orderway);
        } elseif ($orderby == 'id_mp_product') {
            $sql .= ' ORDER BY msp.`id_mp_product` '.pSQL($orderway);
        } else {
            $sql .= ' ORDER BY p.`'.$orderby.'` '.pSQL($orderway);
        }
        $sql .= ' LIMIT '.$startPoint.', '.$limitPoint;

        $mpProducts = Db::getInstance()->executeS($sql);

        Hook::exec(
            'actionSellerProductsListResultModifier',
            array('seller_product_list' => &$mpProducts)
        );

        if (!empty($mpProducts)) {
            $language = Language::getLanguage((int) $idLang);
            foreach ($mpProducts as &$mpProduct) {
                if (isset($mpProduct['id_shop_default'])
                && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //In case of all shops
                    $idShopDefault = (int) $mpProduct['id_shop_default'];
                }
                $mpProduct['quantity'] = StockAvailable::getQuantityAvailableByProduct(
                    $mpProduct['id_ps_product'],
                    null,
                    $idShopDefault
                );
                $mpProduct['unit_price'] = self::getProductUnitPrice(
                    $mpProduct['price'],
                    $mpProduct['unit_price_ratio']
                );
                $mpProduct['id_product'] = $mpProduct['id_ps_product'];
                $mpProduct['id_lang'] = $idLang;
                $mpProduct['lang_iso'] = $language['iso_code'];

                $coverImage = WkMpSellerProductImage::getProductCoverImage(
                    $mpProduct['id_mp_product'],
                    $mpProduct['id_ps_product']
                );
                if ($coverImage) {
                    $mpProduct['cover_image'] = $coverImage;
                }

                //convert price for multiple currency
                $mpProduct['price_per_context'] = Tools::convertPrice($mpProduct['price']);
                $mpProduct['price_per_context_without_sign'] = $mpProduct['price_per_context'];
                $mpProduct['price_per_context_with_sign'] = Tools::displayPrice($mpProduct['price_per_context']);
            }
            return $mpProducts;
        }

        return false;
    }

    public static function isSameSellerProduct($idMpProduct, $idCustomer = false)
    {
        if (!$idCustomer) {
            $idCustomer = Context::getContext()->customer->id;
        }

        $wkAllow = false;
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
        if ($mpSeller) {
            $mpSellerProduct = new self($idMpProduct);
            if ($mpSellerProduct->id_seller == $mpSeller['id_seller']) {
                $wkAllow = true;
            }
        }
        return $wkAllow;
    }

    public static function isSameProductImage($idMpProduct, $idImage)
    {
        if ($idPsProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($idMpProduct)) {
            return Db::getInstance()->getValue(
                'SELECT i.`id_image` FROM `'._DB_PREFIX_.'image` i
                '.Shop::addSqlAssociation('image', 'i').'
                WHERE i.`id_product` = '.(int) $idPsProduct.' AND i.`id_image` = '.(int) $idImage
            );
        }
        return false;
    }

    /**
     * Update prestashop product's images - deprecated function
     */
    public function updatePsProductImage($psIdProduct, $imageNewName)
    {
        return WkMpSellerProductImage::uploadPsProductImage($psIdProduct, $imageNewName);
    }

    /**
     * Send Email on various action perfom on product
     * Please read all the variables used in functions because same function is using for various event.
     *
     * @param int    $mpIdProduct Seller Id Product
     * @param string $subject     Mail Subject
     * @param bool   $mailFor     1 active product, 2 deactive product, 3 delete product
     *
     * @return bolean
     */
    public static function sendMail(
        $mpIdProduct,
        $subject,
        $mailFor = false,
        $reason = false,
        $idPsProductAttribute = false
    ) {
        $objSellerProdVal = new self($mpIdProduct);
        $mpIDSeller = $objSellerProdVal->id_seller;
        $objSellerVal = new WkMpSeller($mpIDSeller);
        $idLang = $objSellerVal->default_lang;

        $psIdProduct = $objSellerProdVal->id_ps_product;
        $objProduct = new Product($psIdProduct);

        if ($mailFor == 1) {
            $mailReason = 'activated';
        } elseif ($mailFor == 2) {
            $mailReason = 'deactivated';
        } elseif ($mailFor == 3) {
            $mailReason = 'deleted';
        } else {
            $mailReason = 'activated';
        }

        if ($mailFor == 'assignment') {
            $objMp = new Marketplace();
            $mailReason = $objMp->l('Admin Assign Product To You', 'WkMpSellerProduct');
        }

        $productName = $objProduct->name[$idLang];
        $quantity = StockAvailable::getQuantityAvailableByProduct(
            $psIdProduct,
            null,
            Context::getContext()->shop->id
        );

        //If product combination exist then add combination name is product name
        if ($idPsProductAttribute) {
            $combinationName = '';
            $attributeIdsSet = $objProduct->getAttributeCombinationsById($idPsProductAttribute, $idLang, true);
            $attributes = Attribute::getAttributes($idLang, true);
            if ($attributes && $attributeIdsSet) {
                foreach ($attributes as $attributeVal) {
                    foreach ($attributeIdsSet as $attributeIdsSetVal) {
                        if ($attributeVal['id_attribute'] == $attributeIdsSetVal['id_attribute']) {
                            $combinationName .= $attributeVal['attribute_group'].' : '.$attributeVal['name'].' ';
                        }
                    }
                }
            }
            $productName = $productName.' - '.$combinationName;

            $quantity = StockAvailable::getQuantityAvailableByProduct(
                $psIdProduct,
                $idPsProductAttribute,
                Context::getContext()->shop->id
            );
            $objCombination = new Combination($idPsProductAttribute);
            $lowStockThreshold = $objCombination->low_stock_threshold;
        } else {
            $lowStockThreshold = $objProduct->low_stock_threshold;
        }

        $idPsShop = $objProduct->id_shop_default;
        $productPrice = $objProduct->price;

        $objCategory = new Category($objProduct->id_category_default, $idLang);
        $categoryName = $objCategory->name;

        $objSeller = new WkMpSeller($mpIDSeller, $idLang);
        $mpSellerName = $objSeller->seller_firstname.' '.$objSeller->seller_lastname;
        $mpShopName = $objSeller->shop_name;
        $businessEmail = $objSeller->business_email;
        if ($businessEmail == '') {
            $idCustomer = $objSeller->seller_customer_id;
            $objCustomer = new Customer($idCustomer);
            $businessEmail = $objCustomer->email;
        }

        $objShop = new Shop($idPsShop);
        $psShopName = $objShop->name;

        $tempPath = _PS_MODULE_DIR_.'marketplace/mails/';

        $templateVars = array(
            '{seller_name}' => $mpSellerName,
            '{product_name}' => $productName,
            '{mp_shop_name}' => $mpShopName,
            '{mail_reason}' => $mailReason,
            '{category_name}' => $categoryName,
            '{product_price}' => Tools::displayPrice($productPrice),
            '{quantity}' => $quantity,
            '{last_quantity}' => $lowStockThreshold,
            '{ps_shop_name}' => $psShopName,
        );
        if ($reason && $reason != '') {
            $templateVars['{reason_text}'] = $reason;
        } else {
            $templateVars['{reason_text}'] = '';
        }

        if (Configuration::get('WK_MP_SUPERADMIN_EMAIL')) {
            $adminEmail = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
        } else {
            $idEmployee = WkMpHelper::getSupperAdmin();
            $employee = new Employee($idEmployee);
            $adminEmail = $employee->email;
        }

        $fromTitle = Configuration::get('WK_MP_FROM_MAIL_TITLE');

        if ($subject == 1) {
            //Product Activated
            if (Configuration::get('WK_MP_MAIL_SELLER_PRODUCT_APPROVE')) {
                Mail::Send(
                    $idLang,
                    'product_active',
                    Mail::l('Product Activated', $idLang),
                    $templateVars,
                    $businessEmail,
                    $mpSellerName,
                    $adminEmail,
                    $fromTitle,
                    null,
                    null,
                    $tempPath,
                    false,
                    null,
                    null
                );
            }
        } elseif ($subject == 2) {
            //Product Deactivated
            if (Configuration::get('WK_MP_MAIL_SELLER_PRODUCT_DISAPPROVE')) {
                Mail::Send(
                    $idLang,
                    'product_deactive',
                    Mail::l('Product Deactivated', $idLang),
                    $templateVars,
                    $businessEmail,
                    $mpSellerName,
                    $adminEmail,
                    $fromTitle,
                    null,
                    null,
                    $tempPath,
                    false,
                    null,
                    null
                );
            }
        } elseif ($subject == 3) {
            if (Configuration::get('WK_MP_MAIL_SELLER_PRODUCT_ASSIGN')) {
                //Admin assign product to seller
                Mail::Send(
                    $idLang,
                    'product_assignment_to_seller',
                    Mail::l('Product Assignment', $idLang),
                    $templateVars,
                    $businessEmail,
                    $mpSellerName,
                    $adminEmail,
                    $fromTitle,
                    null,
                    null,
                    $tempPath,
                    false,
                    null,
                    null
                );
            }
        } elseif ($subject == 4) {
            //Product Out of stock mail to seller
            //Here we are not checking configuration settings for Low Stock level because
            //seller is not able to set low stock level according to configuration
            //but Admin can still set this on behalf seller
            Mail::Send(
                $idLang,
                'product_out_of_stock',
                Mail::l('Product out of stock', $idLang),
                $templateVars,
                $businessEmail,
                $mpSellerName,
                $adminEmail,
                $fromTitle,
                null,
                null,
                $tempPath,
                false,
                null,
                null
            );
        }

        return true;
    }

    /**
     * Send Email to admin when seller add any product in their seller account.
     *
     * @param string $productName     Product Name
     * @param string $sellerName      Seller name
     * @param int    $phone           Seller Phone Number
     * @param string $shopName        Seller Shop Name
     * @param string $businessEmailID Seller Email Address
     *
     * @return bool true/false
     */
    public function mailToAdminOnProductAdd($productName, $sellerName, $phone, $shopName, $businessEmailID)
    {
        if (Configuration::get('WK_MP_SUPERADMIN_EMAIL')) {
            $adminEmail = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
        } else {
            $idEmployee = WkMpHelper::getSupperAdmin();
            $employee = new Employee($idEmployee);
            $adminEmail = $employee->email;
        }

        $sellerVars = array(
            '{product_name}' => $productName,
            '{seller_name}' => $sellerName,
            '{seller_shop}' => $shopName,
            '{seller_email_id}' => $businessEmailID,
            '{seller_phone}' => $phone,
        );

        $templatePath = _PS_MODULE_DIR_.'marketplace/mails/';
        Mail::Send(
            (int) Configuration::get('PS_LANG_DEFAULT'),
            'mp_product_add',
            Mail::l('New product added', (int) Configuration::get('PS_LANG_DEFAULT')),
            $sellerVars,
            $adminEmail,
            null,
            null,
            null,
            null,
            null,
            $templatePath,
            false,
            null,
            null
        );
    }

    /**
     * Send Email to admin or seller when admin/seller delete mp product
     *
     * @param string $productName     Seller product name
     * @param string $sellerName      Seller name
     * @param int    $phone           Seller phone number
     * @param string $shopName        Seller Shop Name
     * @param string $businessEmailID Seller email address
     * @param string $mailLangId      mail send in language
     * @param string $mailTo          mail send to admin or seller
     *
     * @return bool true/false
     */
    public static function mailOnProductDelete(
        $productName,
        $sellerName,
        $phone,
        $shopName,
        $businessEmailID,
        $mailLangId,
        $mailTo
    ) {
        if (Configuration::get('WK_MP_SUPERADMIN_EMAIL')) {
            $adminEmail = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
        } else {
            $idEmployee = WkMpHelper::getSupperAdmin();
            $employee = new Employee($idEmployee);
            $adminEmail = $employee->email;
        }

        $sellerVars = array(
            '{product_name}' => $productName,
            '{seller_name}' => $sellerName,
            '{seller_shop}' => $shopName,
            '{seller_email_id}' => $businessEmailID,
            '{seller_phone}' => $phone,
        );

        if ($mailTo == 'admin') {
            //deleted by seller
            $mailToEmail = $adminEmail;
            $sellerVars['{to_mail_person}'] = 'Admin';
            $sellerVars['{from_mail_person}'] = $sellerName;
        } else {
            //deleted by admin
            $mailToEmail = $businessEmailID;
            $sellerVars['{to_mail_person}'] = $sellerName;
            $sellerVars['{from_mail_person}'] = 'Admin';
        }

        $templatePath = _PS_MODULE_DIR_.'marketplace/mails/';
        Mail::Send(
            (int) $mailLangId,
            'mp_product_delete',
            Mail::l('Product Deleted', (int) $mailLangId),
            $sellerVars,
            $mailToEmail,
            null,
            null,
            null,
            null,
            null,
            $templatePath,
            false,
            null,
            null
        );
    }

    /**
     * Get Seller Default Language when seller add product or update product.
     *
     * @param int $sellerDefaultLanguage seller current default language
     *
     * @return array
     */
    public static function getDefaultLanguageOnProductSave()
    {
        //If multi-lang is OFF then PS default lang will be default lang for seller
        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
            $defaultLang = Tools::getValue('seller_default_lang');
        } else {
            if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {
                //Admin default lang
                $defaultLang = Configuration::get('PS_LANG_DEFAULT');
            } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {
                //Seller default lang
                $defaultLang = Tools::getValue('seller_default_lang');
            }
        }
        $objLang = new Language((int) $defaultLang);
        if (!$objLang->active) {
            $defaultLang = Configuration::get('PS_LANG_DEFAULT');
        }

        return $defaultLang;
    }

    /**
     * Duplicate seller product
     *
     * @param int $originalMpProductId - Seller Original Product ID
     * @param int $targetSellerId            - Seller ID for which product want to duplicate
     *
     * @return array/bool containing seller's new product information
     */
    public function duplicateSellerProduct($originalMpProductId, $targetSellerId = false)
    {
        Hook::exec('actionBeforeDuplicateMPProduct', array('id_mp_product' => $originalMpProductId));

        $objOriginalMpProduct = new self($originalMpProductId);
        if (Validate::isLoadedObject($objOriginalMpProduct)) {
            if (!$targetSellerId) {
                //If targetSellerId is not defined then create duplicate product for same seller
                $targetSellerId = $objOriginalMpProduct->id_seller;
            }

            if ($duplicatePsProductId = $this->copyMpProductToPs($originalMpProductId)) {
                $objMpProduct = new self();
                $objMpProduct->id_seller = $targetSellerId;
                $objMpProduct->id_ps_product = $duplicatePsProductId;

                $idMpShopDefault = Context::getContext()->shop->id;
                $sellerDetail = WkMpSeller::getSeller($targetSellerId, Configuration::get('PS_LANG_DEFAULT'));
                if ($sellerDetail) {
                    if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
                        //If case of all shop, get shop Id from selected seller
                        $idMpShopDefault = $sellerDetail['id_shop'];
                    }
                }
                $objMpProduct->id_mp_shop_default = $idMpShopDefault;

                $objMpProduct->id_mp_duplicate_product_parent = $originalMpProductId;
                $objMpProduct->admin_assigned = 0;
                if (!Configuration::get('WK_MP_PRODUCT_ADMIN_APPROVE')) { //if Auto approval
                    $objMpProduct->admin_approved = 1;
                    $objMpProduct->status_before_deactivate = 1;
                } else {
                    $objMpProduct->admin_approved = 0;
                    $objMpProduct->status_before_deactivate = 0;
                }
                if ($objMpProduct->save()) {
                    $duplicateMpProductId = $objMpProduct->id;

                    if (!Configuration::get('WK_MP_PRODUCT_ADMIN_APPROVE')) {
                        Hook::exec(
                            'actionToogleMPProductCreateStatus',
                            array(
                                'id_product' => $duplicatePsProductId,
                                'id_mp_product' => $duplicateMpProductId,
                                'active' => 1
                            )
                        );

                        //If seller product default active approval is ON then mail to seller of product activation
                        self::sendMail($duplicateMpProductId, 1, 1);
                    }

                    if (Configuration::get('WK_MP_MAIL_ADMIN_PRODUCT_ADD')) {
                        //Mail to admin on product add by seller
                        $sellerDetail = WkMpSeller::getSeller($targetSellerId, Configuration::get('PS_LANG_DEFAULT'));
                        if ($sellerDetail) {
                            $objProduct = new Product(
                                $duplicatePsProductId,
                                true,
                                Context::getContext()->language->id
                            );
                            $objMpProduct->mailToAdminOnProductAdd(
                                $objProduct->name,
                                $sellerDetail['seller_firstname'].' '.$sellerDetail['seller_lastname'],
                                $sellerDetail['phone'],
                                $sellerDetail['shop_name'],
                                $sellerDetail['business_email']
                            );
                            if (Pack::isPack($duplicatePsProductId)) {
                                $objMpPack = new WkMpPackProduct();
                                $stockType = $objMpPack->getPackedProductStockType($originalMpProductId);
                                if (!$stockType) {
                                    $stockType = (int) Configuration::get('PS_PACK_STOCK_TYPE');
                                }
                                $objMpPack->updateStockTypeMpPack($duplicateMpProductId, $stockType);
                                $objMpPack->isPackProductFieldUpdate($duplicateMpProductId, 1);
                            }
                        }
                    }

                    Hook::exec('actionAfterAddMPProduct', array('id_mp_product' => $originalMpProductId));

                    Hook::exec(
                        'actionAfterDuplicateMPProduct',
                        array(
                            'id_mp_product_original' => $originalMpProductId,
                            'id_mp_product_duplicate' => $duplicateMpProductId
                        )
                    );

                    return $duplicateMpProductId;
                }
            }
        }
        return false;
    }

    public function copyMpProductToPs($originalMpProductId)
    {
        if ($originalPsProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($originalMpProductId)) {
            $objOriginalProduct = new Product($originalPsProductId);

            if (Validate::isLoadedObject($objOriginalProduct)) {
                $objDuplicateProduct = new Product($originalPsProductId);
                if (empty($objDuplicateProduct->price)) {
                    $objDuplicateProduct->price = $objOriginalProduct->price;
                }

                $objDuplicateProduct->unit_price = $objOriginalProduct->unit_price;
                $objDuplicateProduct->unity = $objOriginalProduct->unity;

                foreach (Language::getLanguages(false) as $language) {
                    if (Configuration::get('WK_MP_PRODUCT_DUPLICATE_TITLE', $language['id_lang'])
                    && Validate::isCatalogName(
                        Configuration::get('WK_MP_PRODUCT_DUPLICATE_TITLE', $language['id_lang'])
                    )) {
                        $wkNamePattern = trim(
                            Configuration::get('WK_MP_PRODUCT_DUPLICATE_TITLE', $language['id_lang'])
                        ).' %s';
                    } else {
                        $wkNamePattern = '%s';
                    }

                    if (isset($objDuplicateProduct->name[$language['id_lang']])) {
                        $oldName = $objDuplicateProduct->name[$language['id_lang']];
                        if (!preg_match(
                            '/^'.str_replace('%s', '.*', preg_quote($wkNamePattern, '/').'$/'),
                            $oldName
                        )) {
                            $newName = sprintf($wkNamePattern, $oldName);
                            if (mb_strlen($newName, 'UTF-8') <= 127) {
                                $objDuplicateProduct->name[$language['id_lang']] = $newName;
                            }
                        }
                    }
                }

                unset($objDuplicateProduct->id);
                unset($objDuplicateProduct->id_product);

                $objDuplicateProduct->indexed = 0;
                if (!Configuration::get('WK_MP_PRODUCT_ADMIN_APPROVE')) { //if Auto approval
                    $objDuplicateProduct->active = 1;
                } else {
                    $objDuplicateProduct->active = 0;
                }
                if ($objDuplicateProduct->add()) { //Copy old ps product object and then call add on same object
                    $duplicatePsProductId = $objDuplicateProduct->id;

                    if (Configuration::get('WK_MP_PRODUCT_DUPLICATE_QUANTITY')) {
                        //if zero quantity settings is enabled
                        $quantity = 0;
                    } else {
                        $quantity = StockAvailable::getQuantityAvailableByProduct(
                            $originalPsProductId,
                            null,
                            Context::getContext()->shop->id
                        );
                    }
                    StockAvailable::setQuantity(
                        $duplicatePsProductId,
                        0,
                        $quantity,
                        Context::getContext()->shop->id
                    );

                    //Set product behaviour
                    $wkProductBehaviour = StockAvailable::outOfStock(
                        $originalPsProductId,
                        Context::getContext()->shop->id
                    );
                    StockAvailable::setProductOutOfStock(
                        $duplicatePsProductId,
                        $wkProductBehaviour,
                        Context::getContext()->shop->id
                    );

                    $combinationImagesWithCopy = false;
                    if ($objOriginalProduct->hasAttributes()) {
                        $combinationImagesWithCopy = WkMpProductAttribute::duplicateAttributes(
                            $originalPsProductId,
                            $duplicatePsProductId
                        );
                    }

                    $copyImages = Image::duplicateProductImages(
                        $originalPsProductId,
                        $duplicatePsProductId,
                        $combinationImagesWithCopy
                    );

                    if (self::copyProductCategories($originalPsProductId, $duplicatePsProductId)
                    && Product::duplicateFeatures($originalPsProductId, $duplicatePsProductId)
                    && self::duplicateCarriers($originalPsProductId, $duplicatePsProductId)
                    && Product::duplicateSuppliers($originalPsProductId, $duplicatePsProductId)
                    && GroupReduction::duplicateReduction($originalPsProductId, $duplicatePsProductId)
                    && Product::duplicateAccessories($originalPsProductId, $duplicatePsProductId)
                    && Product::duplicateSpecificPrices($originalPsProductId, $duplicatePsProductId)
                    && Pack::duplicate($originalPsProductId, $duplicatePsProductId)
                    && Product::duplicateCustomizationFields($originalPsProductId, $duplicatePsProductId)
                    && Product::duplicateTags($originalPsProductId, $duplicatePsProductId)
                    && Product::duplicateDownload($originalPsProductId, $duplicatePsProductId)
                    && Product::duplicateAttachments($originalPsProductId, $duplicatePsProductId)
                    && $copyImages
                    && $combinationImagesWithCopy
                    ) {
                        return $duplicatePsProductId;
                    }

                    return $duplicatePsProductId;
                }
            }
        }
        return false;
    }

    public static function duplicateCarriers($originalPsProductId, $duplicatePsProductId)
    {
        $oldProductCarriers = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'product_carrier` WHERE `id_product` = ' . (int) $originalPsProductId
        );
        if ($oldProductCarriers) {
            foreach ($oldProductCarriers as $row) {
                $row['id_product'] = (int) $duplicatePsProductId;
                if (!Db::getInstance()->insert('product_carrier', $row)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
    * Copy seller product category into other product
    *
    * @param int $originalMpProductId - Original Mp Product ID
    * @param int $duplicateMpProductId - Duplicate Mp Product ID
    *
    * @return array/boolean
    */
    public static function copyMpProductCategories($originalMpProductId, $duplicateMpProductId)
    {
        $originalPsProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($originalMpProductId);
        $duplicatePsProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($duplicateMpProductId);
        if ($originalPsProductId && $duplicatePsProductId) {
            return self::copyProductCategories($originalPsProductId, $duplicatePsProductId);
        }
        return false;
    }

    /**
    * Copy seller product category into other product
    *
    * @param int $originalPsProductId - Original Product ID
    * @param int $duplicatePsProductId - Duplicate Product ID
    *
    * @return array/boolean
    */
    public static function copyProductCategories($originalPsProductId, $duplicatePsProductId)
    {
        $objProduct = new Product($originalPsProductId);
        if ($categories = $objProduct->getCategories()) {
            $objNewProduct = new Product($duplicatePsProductId);
            return $objNewProduct->addToCategories($categories);
        }
        return true;
    }

    /**
     * Get seller's product with prestashop product object and image.
     *
     * @param int    $idSeller Seller ID
     * @param int    $idPsShop prestashop shop ID
     * @param object $objProd  If you pass true then prestashop product object will be added in objProduct
     * @param bool   $active   true/false
     * @param bool   $idLang   pass language specific id if you want
     *
     * @return array/bool containing seller's product information
     */
    public static function getSellerProductWithPs($idSeller, $objProd = false, $active = 1, $idPsShop = false, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        if (!$idPsShop) {
            $idPsShop = Context::getContext()->shop->id;
        }
        $mpProducts = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` mpsp
            JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = mpsp.`id_ps_product`)
            ' . Shop::addSqlAssociation('product', 'p') . '
            JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`'
            . Shop::addSqlRestrictionOnLang('pl') . ')
            WHERE mpsp.`id_seller` = '.(int) $idSeller.'
            AND mpsp.`id_mp_shop_default` = '.(int) $idPsShop.'
            AND pl.`id_lang` = '.(int) $idLang.'
            AND p.`active` = '.(int) $active.'
            AND mpsp.`id_ps_product` != 0
            ORDER BY p.`date_add` DESC LIMIT 10'
        );

        if ($mpProducts && $objProd) {
            foreach ($mpProducts as $key => $product) {
                $objProduct = new Product($product['id_product'], true, $idLang);
                $mpProducts[$key]['objproduct'] = $objProduct;
                $mpProducts[$key]['lang_iso'] = Context::getContext()->language->iso_code;
                $mpProducts[$key]['price'] = $objProduct->price;
                $cover = Product::getCover($product['id_product']);
                if ($cover) {
                    $mpProducts[$key]['image'] = $product['id_product'].'-'.$cover['id_image'];
                } else {
                    $mpProducts[$key]['image'] = 0;
                }
            }

            return $mpProducts;
        } else {
            return $mpProducts;
        }

        return false;
    }

    /**
     * Get converted price according to context currency.
     *
     * @param float $price       price/amount
     * @param int   $id_currency if you want to specify currency ID or it will take context currency
     *
     * @return float/false
     */
    public static function getConvertedPrice($price, $idCurrency = false)
    {
        if (!$idCurrency) {
            $idCurrency = Context::getContext()->currency->id;
        }

        if ($price != '') {
            $objCurreny = Currency::getCurrency($idCurrency);
            $conversionRate = $objCurreny['conversion_rate'];

            return ($price * $conversionRate);
        }

        return false;
    }

    /**
     * Get seller's product images with seller id product.
     *
     * @param int $mpIdProduct Seller Product ID
     *
     * @return array/bool containing product images
     */
    public static function getSellerProductImages($mpIdProduct)
    {
        $context = Context::getContext();
        $idLang = $context->language->id;

        if ($psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
            $objProduct = new Product($psIdProduct);
            $productImages = $objProduct->getImages($idLang);
            if ($productImages) {
                $productLinkRewrite = $objProduct->link_rewrite[$idLang];
                foreach ($productImages as &$image) {
                    $image['image_link'] = $context->link->getImageLink(
                        $productLinkRewrite,
                        $psIdProduct.'-'.$image['id_image'],
                        ImageType::getFormattedName('cart')
                    );
                }
            }

            if ($productImages && !empty($productImages)) {
                return $productImages;
            }
        }

        return false;
    }

    /**
     * Get Seller's Product Categories by using Seller ID product.
     *
     * @param int $idSellerProduct Seller Id Product
     *
     * @return array/boolean Array of categories/false
     */
    public function getSellerProductCategories($mpIdProduct)
    {
        if ($psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
            return Product::getProductCategories($psIdProduct);
        }

        return false;
    }

    /**
     * If admin set configuration Yes for product update after need approval
     * then seller product will be activated after product update from update product page in front end.
     *
     * @param int $mpIdProduct seller product id
     *
     * @return bool
     */
    public static function deactivateProductAfterUpdate($mpIdProduct, $extraController = false)
    {
        // Product after update need to approved is ON only for product update page
        if (Configuration::get('WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE')
            && (
                'updateproduct' == Tools::getValue('controller') ||
                'managecombination' == Tools::getValue('controller') ||
                'generatecombination' == Tools::getValue('controller') ||
                'uploadimage' == Tools::getValue('controller') ||
                $extraController == 1
            )
            ) {
            //Deactivate the product after seller update that product
            $objSellerProduct = new self($mpIdProduct);
            if ($idPsProduct = $objSellerProduct->id_ps_product) {
                $objProduct = new Product($idPsProduct);
                if (Validate::isLoadedObject($objProduct) && $objProduct->active) {
                    $objSellerProduct->status_before_deactivate = 0;
                    $objSellerProduct->admin_approved = 0;
                    if ($objSellerProduct->save()) {
                        $objProduct->active = 0;
                        if ($objProduct->save()) {
                            self::sendMail($mpIdProduct, 2, 2);
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * When seller or admin change tax rule or price from add/update product page
     * then display product price with Tax included (Final price after Tax Incl.).
     */
    public static function getMpProductTaxIncludedPrice()
    {
        //Get Tax Include Product Price according to selected Tax Rate and Product Price (tax excl.)
        $productPrice = Tools::getValue('product_price');
        $productTIPrice = Tools::getValue('productTI_price');
        $idTaxRulesGroup = Tools::getValue('id_tax_rules_group');
        $inputAction = Tools::getValue('input_action');

        if ($productTIPrice == '') {
            $productTIPrice = 0;
        }

        $idCountryDefault = Configuration::get('PS_COUNTRY_DEFAULT');
        $adminDefaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $taxesRatesByGroup = TaxRulesGroup::getAssociatedTaxRatesByIdCountry($idCountryDefault);
        if ($taxesRatesByGroup) {
            if (isset($taxesRatesByGroup[$idTaxRulesGroup]) && $taxesRatesByGroup[$idTaxRulesGroup]) {
                $taxRate = $taxesRatesByGroup[$idTaxRulesGroup];
            } else {
                $taxRate = 0;
            }

            if ($inputAction == 'input_incl') {
                //Get tax incl price to tax excl price
                $productPrice = (float) $productTIPrice / (($taxRate / 100) + 1);
            } else {
                //Get tax excl price to tax incl price
                $productPrice = (float) $productPrice + ((float) $productPrice * $taxRate) / 100;
            }
        }

        die(Tools::jsonEncode(
            array(
                'status' => 'ok',
                'prod_price' => Tools::ps_round($productPrice),
                'display_product_price' => Tools::displayPrice($productPrice, $adminDefaultCurrency),
                'display_productTI_price' => Tools::displayPrice($productTIPrice, $adminDefaultCurrency),
            )
        ));

        //ajax close
    }

    /**
     * Load Prestashop category with ajax load of plugin jstree.
     */
    public static function getMpProductCategory($sellerIdCustomer = false)
    {
        if (Tools::getValue('id_mp_product') == '') {
            // Add product
            $catId = Tools::getValue('catsingleId');
            $selectedCatIds = array(Category::getRootCategory()->id); //Root Category will be automatically selected
        } else {
            // Edit product
            $catId = Tools::getValue('catsingleId');
            $selectedCatIds = explode(',', Tools::getValue('catIds'));
            $mpSellerProduct = new WkMpSellerProduct(Tools::getValue('id_mp_product'));
            $mpSellerId = $mpSellerProduct->id_seller;
        }

        if (!isset($mpSellerId)) {
            if (!$sellerIdCustomer) {
                $sellerIdCustomer = Context::getContext()->customer->id;
            }
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($sellerIdCustomer);
            $mpSellerId = $mpSeller['id_seller'];
        }

        $objSellerProduct = new WkMpSellerProduct();
        $treeLoad = $objSellerProduct->getProductCategory(
            $catId,
            $selectedCatIds,
            Context::getContext()->language->id,
            $mpSellerId
        );
        if ($treeLoad) {
            die(Tools::jsonEncode($treeLoad)); //ajax close
        } else {
            die('fail'); //ajax close
        }
    }

    /**
     * Check whether prestashop product belong to seller or not.
     *
     * @param int $SellerIdCustomer Prestashop Product ID
     * @param int $idSeller         Seller ID
     *
     * @return array
     */
    public static function checkPsProduct($idPsProduct, $idSeller)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product`
            WHERE `id_ps_product` ='.(int) $idPsProduct.' AND `id_seller` = '.(int) $idSeller
        );
    }

    /**
     * PHP Validation all the fields entered by seller during add or update product.
     *
     * @return array/bool
     */
    public static function validateMpProductForm()
    {
        $className = 'WkMpSellerProduct';
        $objMp = new Marketplace();
        $wkErrors = array();

        $quantity = Tools::getValue('quantity');
        if (Configuration::get('WK_MP_PRODUCT_MIN_QTY')
        || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $minimalQuantity = Tools::getValue('minimal_quantity');
        } else {
            $minimalQuantity = 1; //default value
        }

        if (Configuration::get('WK_MP_PRODUCT_LOW_STOCK_ALERT')
        || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $lowStockThreshold = Tools::getValue('low_stock_threshold');
        } else {
            $lowStockThreshold = '';
        }

        $categories = Tools::getValue('product_category');

        $price = Tools::getValue('price');

        if (Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE')
        || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $wholesalePrice = Tools::getValue('wholesale_price');
        } else {
            $wholesalePrice = '';
        }

        if (Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT')
        || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $unitPrice = Tools::getValue('unit_price');
        } else {
            $unitPrice = '';
        }

        if (Configuration::get('WK_MP_PRODUCT_ADDITIONAL_FEES')
        || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $additionalFees = Tools::getValue('additional_shipping_cost');
        } else {
            $additionalFees = '';
        }

        $reference = trim(Tools::getValue('reference'));
        $ean13JanBarcode = trim(Tools::getValue('ean13'));
        $upcBarcode = trim(Tools::getValue('upc'));
        $isbn = trim(Tools::getValue('isbn'));
        $availableDate = Tools::getValue('available_date');

        // height, width, depth and weight
        $width = Tools::getValue('width');
        $width = empty($width) ? '0' : str_replace(',', '.', $width);

        $height = Tools::getValue('height');
        $height = empty($height) ? '0' : str_replace(',', '.', $height);

        $depth = Tools::getValue('depth');
        $depth = empty($depth) ? '0' : str_replace(',', '.', $depth);

        $weight = Tools::getValue('weight');
        $weight = empty($weight) ? '0' : str_replace(',', '.', $weight);

        // Check fields sizes
        $rules = call_user_func(array('Product', 'getValidationRules'), 'Product');

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $languageName = '';
            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $languageName = '('.$language['name'].')';
            }

            if (!Validate::isCatalogName(Tools::getValue('product_name_'.$language['id_lang']))) {
                $wkErrors[] = sprintf($objMp->l('Product name field %s is invalid', $className), $languageName);
            } elseif (Tools::strlen(Tools::getValue('product_name_'.$language['id_lang'])) > $rules['sizeLang']['name']) {
                $wkErrors[] = sprintf($objMp->l('The Product Name field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['name']);
            }

            if (Tools::getValue('short_description_'.$language['id_lang'])) {
                $shortDesc = Tools::getValue('short_description_'.$language['id_lang']);
                $limit = (int) Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');
                if ($limit <= 0) {
                    $limit = 400;
                }
                if (!Validate::isCleanHtml($shortDesc, (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                    $wkErrors[] = sprintf(
                        $objMp->l('Short description field %s is invalid', $className),
                        $languageName
                    );
                } elseif (Tools::strlen(strip_tags($shortDesc)) > $limit) {
                    $wkErrors[] = sprintf(
                        $objMp->l('Short description field %s is too long: (%d chars max).', $className),
                        $languageName,
                        $limit
                    );
                }
            }

            if (Tools::getValue('description_'.$language['id_lang'])) {
                if (!Validate::isCleanHtml(Tools::getValue('description_'.$language['id_lang']), (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                    $wkErrors[] = sprintf($objMp->l('Product description field %s is invalid', $className), $languageName);
                }
            }

            //Product Availability Preferences Validation
            if (Tools::getValue('available_now_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('available_now_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Label when in stock field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('available_now_'.$language['id_lang'])) > $rules['sizeLang']['available_now']) {
                    $wkErrors[] = sprintf($objMp->l('Label when in stock field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['available_now']);
                }
            }
            if (Tools::getValue('available_later_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('available_later_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Label when out of stock field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('available_later_'.$language['id_lang'])) > $rules['sizeLang']['available_later']) {
                    $wkErrors[] = sprintf($objMp->l('Label when out of stock field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['available_later']);
                }
            }

            //Product Delivery Time Validation
            if (Tools::getValue('delivery_in_stock_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('delivery_in_stock_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Delivery time of in-stock products field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('delivery_in_stock_'.$language['id_lang'])) > $rules['sizeLang']['delivery_in_stock']) {
                    $wkErrors[] = sprintf($objMp->l('Delivery time of in-stock products field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['delivery_in_stock']);
                }
            }
            if (Tools::getValue('delivery_out_stock_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('delivery_out_stock_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Delivery time of out-of-stock products field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('delivery_out_stock_'.$language['id_lang'])) > $rules['sizeLang']['delivery_out_stock']) {
                    $wkErrors[] = sprintf($objMp->l('Delivery time of out-of-stock products field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['delivery_out_stock']);
                }
            }

            //Product SEO Validation
            if (Tools::getValue('meta_title_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('meta_title_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Product meta title field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('meta_title_'.$language['id_lang'])) > $rules['sizeLang']['meta_title']) {
                    $wkErrors[] = sprintf($objMp->l('Product meta title field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['meta_title']);
                }
            }
            if (Tools::getValue('meta_description_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('meta_description_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Product meta description field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('meta_description_'.$language['id_lang'])) > $rules['sizeLang']['meta_description']) {
                    $wkErrors[] = sprintf($objMp->l('Product meta description field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['meta_description']);
                }
            }
            if (Tools::getValue('link_rewrite_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('link_rewrite_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Product friendly url field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('link_rewrite_'.$language['id_lang'])) > $rules['sizeLang']['link_rewrite']) {
                    $wkErrors[] = sprintf($objMp->l('Product friendly url field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['link_rewrite']);
                }
            }
        }

        //Product Price Validation
        if ($price == '') {
            $wkErrors[] = $objMp->l('Product price is required field.', $className);
        } elseif (!Validate::isPrice($price)) {
            $wkErrors[] = $objMp->l('Product price should be valid.', $className);
        }
        if ($wholesalePrice != '') {
            if (!Validate::isPrice($wholesalePrice)) {
                $wkErrors[] = $objMp->l('Cost price should be valid.', $className);
            }
        }
        if ($unitPrice != '') {
            if (!Validate::isPrice($unitPrice)) {
                $wkErrors[] = $objMp->l('Price per unit should be valid.', $className);
            }
        }

        //Product Quantity Validation
        if ($quantity == '') {
            $wkErrors[] = $objMp->l('Product quantity is required field.', $className);
        } elseif (!Validate::isInt($quantity)) {
            $wkErrors[] = $objMp->l('Product quantity should be valid.', $className);
        }
        if ($minimalQuantity == '') {
            $wkErrors[] = $objMp->l('Product minimum quantity is required field.', $className);
        } elseif (!Validate::isUnsignedInt($minimalQuantity)) {
            $wkErrors[] = $objMp->l('Product minimum quantity should be valid.', $className);
        }

        if ($lowStockThreshold != '') {
            if (!Validate::isInt($lowStockThreshold)) {
                $wkErrors[] = $objMp->l('Low stock level should be valid.', $className);
            }
        }

        if (!$categories) {
            $wkErrors[] = $objMp->l('You have not selected any category.', $className);
        }

        //Product Package Dimension Validation
        if ($width && !Validate::isUnsignedFloat($width)) {
            $wkErrors[] = $objMp->l('Value of width is not valid.', $className);
        }
        if ($height && !Validate::isUnsignedFloat($height)) {
            $wkErrors[] = $objMp->l('Value of height is not valid.', $className);
        }
        if ($depth && !Validate::isUnsignedFloat($depth)) {
            $wkErrors[] = $objMp->l('Value of depth is not valid.', $className);
        }
        if ($weight && !Validate::isUnsignedFloat($weight)) {
            $wkErrors[] = $objMp->l('Value of weight is not valid.', $className);
        }

        if ($additionalFees != '') {
            if (!Validate::isPrice($additionalFees)) {
                $wkErrors[] = $objMp->l('Shipping fees should be valid.', $className);
            }
        }

        // Product Reference, SEO, EAN, ISBN and UPC Code
        if ($reference && !Validate::isReference($reference)) {
            $wkErrors[] = $objMp->l('Reference is not valid.', $className);
        }
        if ($ean13JanBarcode) {
            if (!Validate::isEan13($ean13JanBarcode)) {
                $wkErrors[] = $objMp->l('EAN-13 or JAN barcode is not valid.', $className);
            }
        }
        if ($upcBarcode && !Validate::isUpc($upcBarcode)) {
            $wkErrors[] = $objMp->l('UPC Barcode is not valid.', $className);
        }
        if ($isbn && !Validate::isIsbn($isbn)) {
            $wkErrors[] = $objMp->l('ISBN Code is not valid.', $className);
        }

        if ($availableDate && !Validate::isDateFormat($availableDate)) {
            $wkErrors[] = $objMp->l('Available date must be valid.', $className);
        }

        $productType = (int) Tools::getValue('product_type');
        if ($productType == 2) {
            $pkProducts = Tools::getValue('pspk_id_prod');
            $pkProdQuant = Tools::getValue('pspk_prod_quant');

            if (empty($pkProducts) || empty($pkProdQuant)) {
                $wkErrors[] = $objMp->l('This pack is empty. You must add at least one product item.', $className);
            } elseif (count($pkProducts) != count($pkProdQuant)) {
                $wkErrors[] =
                $objMp->l('There is some internal error while creating pack product, please try again.', $className);
            } else {
                foreach ($pkProdQuant as $value) {
                    if (!Validate::isInt($value) || $value <= 0) {
                        $wkErrors[] =
                        $objMp->l('Please enter product quantity greater than or equal to 1.', $className);
                        break;
                    }
                }
            }
        } elseif ($productType == 3) {
            //if virtual product
            $mpVirtualProductName = trim(Tools::getValue('mp_vrt_prod_name'));
            $mpVirtualProductNbDownloadable = Tools::getValue('mp_vrt_prod_nb_downloable');
            $mpVirtualProductExpDate = Tools::getValue('mp_vrt_prod_expdate');
            $mpVirtualProductNbDays = Tools::getValue('mp_vrt_prod_nb_days');
            $allowedSize = Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1000000;
            if ($_FILES['mp_vrt_prod_file']['size']) {
                if ($_FILES['mp_vrt_prod_file']['size'] > $allowedSize) {
                    $wkErrors[] = sprintf(
                        $objMp->l('Uploaded file size must be less than %s MB.', $className),
                        Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')
                    );
                } elseif (Tools::isEmpty($mpVirtualProductName) || !Validate::isCleanHtml($mpVirtualProductName)) {
                    $wkErrors[] = $objMp->l('Please enter valid file name.', $className);
                } elseif (Tools::strlen($mpVirtualProductName) > 32) {
                    $wkErrors[] = sprintf($objMp->l('The filename is too long (%2d chars max).', $className), 32);
                }
            } elseif (!$_FILES['mp_vrt_prod_file']['size']) {
                if (Tools::isEmpty($mpVirtualProductName) || !Validate::isCleanHtml($mpVirtualProductName)) {
                    $wkErrors[] = $objMp->l('Please enter valid file name.', $className);
                } elseif (Tools::strlen($mpVirtualProductName) > 32) {
                    $wkErrors[] = sprintf($objMp->l('The filename is too long (%2d chars max).', $className), 32);
                }
            }

            if ($mpVirtualProductNbDownloadable != '') {
                if (!Validate::isUnsignedInt($mpVirtualProductNbDownloadable)) {
                    $wkErrors[] = $objMp->l('Number of allowed file downloads is not valid.', $className);
                }
            }

            if ($mpVirtualProductExpDate != '') {
                if (!Validate::isDate($mpVirtualProductExpDate)) {
                    $wkErrors[] = $objMp->l('Expiration date is not valid.', $className);
                }
            }

            if ($mpVirtualProductNbDays != '') {
                if (!Validate::isUnsignedInt($mpVirtualProductNbDays)) {
                    $wkErrors[] = $objMp->l('Number of days is not valid.', $className);
                }
            }
        }
        
        if (!Tools::getValue('id_mp_product') && (Configuration::get('WK_MP_PRODUCT_SPECIFIC_RULE')
        || Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $leave_bprice = 0;
            if (1 == Tools::getValue('leave_bprice')) {
                $leave_bprice = Tools::getValue('leave_bprice');
            }
            $sp_price = -1;
            if (Tools::getValue('sp_price')) {
                $sp_price = Tools::getValue('sp_price');
            }
            $price = $leave_bprice ? '-1' : $sp_price;
            $from_quantity = Tools::getValue('sp_from_quantity');
            $reduction = (float) Tools::getValue('sp_reduction');
            $reduction_type = !$reduction ? 'amount' : Tools::getValue('sp_reduction_type');
            $reduction_type = $reduction_type == '-' ? 'amount' : $reduction_type;
            $from = Tools::getValue('sp_from');
            if (!$from) {
                $from = '0000-00-00 00:00:00';
            }
            $to = Tools::getValue('sp_to');
            if (!$to) {
                $to = '0000-00-00 00:00:00';
            }
            if ($reduction > 0) {
                if (($price == '-1') && ((float)$reduction == '0')) {
                    $wkErrors[] = $objMp->l('No reduction value has been submitted', $className);
                } elseif ($to != '0000-00-00 00:00:00' && strtotime($to) < strtotime($from)) {
                    $wkErrors[] = $objMp->l('Invalid date range', $className);
                } elseif ($reduction_type == 'percentage' && ((float)$reduction <= 0 || (float)$reduction > 100)) {
                    $wkErrors[] = $objMp->l('Submitted reduction value (0-100) is out-of-range', $className);
                } elseif ((!isset($price) && !isset($reduction))
                || (isset($price) && !Validate::isNegativePrice($price))
                || (isset($reduction) && !Validate::isPrice($reduction))) {
                    $wkErrors[] = $objMp->l('Invalid price/discount amount', $className);
                } elseif (!Validate::isUnsignedInt($from_quantity)) {
                    $wkErrors[] = $objMp->l('Invalid starting at quantity', $className);
                } elseif ($reduction && !Validate::isReductionType($reduction_type)) {
                    $wkErrors[] =
                    $objMp->l('Please select a discount type (amount or percentage).', $className);
                } elseif ($from && $to && (!Validate::isDateFormat($from) || !Validate::isDateFormat($to))) {
                    $wkErrors[] = $objMp->l('The from/to date is invalid.', $className);
                }
            }
        }

        foreach (Language::getLanguages(true) as $language) {
            if (Tools::getValue('tag_'.$language['id_lang'])) {
                $productTag = Tools::getValue('tag_'.$language['id_lang']);
                $tagData = explode(',', $productTag);
                if ($tagData) {
                    foreach ($tagData as $tag) {
                        $tag = trim($tag);
                        if (empty($tag)) {
                            $tagError = $objMp->l('The tags list is invalid.', $className).' ';
                            $tagError .= $objMp->l('Tag(s) should not contain spaces.', $className);
                            $wkErrors[] = $tagError;
                        } else {
                            $isValidTag = preg_match('/^[^!<>;?=+#"°{}_$%]*$/u', $tag);
                            if ($isValidTag == 0) {
                                $tagError = $objMp->l('The tags list is invalid.', $className).' ';
                                $tagError .= $objMp->l('It should not contain special characters.', $className);
                                $wkErrors[] = $tagError;
                            }
                        }
                    }
                }
            }
        }

        if ($customFields = Tools::getValue('custom_fields')) {
            if (!empty($customFields)) {
                if (Tools::getValue('controller') == 'AdminSellerProductDetail') {
                    $sellerDefaultLanguage = Tools::getValue('seller_default_lang');
                } else {
                    $sellerDefaultLanguage = Tools::getValue('default_lang');
                }
                foreach ($customFields as $fields) {
                    foreach (Language::getLanguages(true) as $language) {
                        if ($fields['label'][$language['id_lang']] == '' && $language['id_lang'] == $sellerDefaultLanguage) {
                            $wkErrors[] = sprintf($objMp->l('Customization label field %s is empty', $className), $language['name']);
                        } elseif (!Validate::isGenericName($fields['label'][$language['id_lang']])) {
                            $wkErrors[] = sprintf($objMp->l('Customization label field %s is invalid', $className), $language['name']);
                        }
                    }
                }
            }
        }

        if ($location = Tools::getValue('location')) {
            if (!empty($location)) {
                if (!Validate::isString($location)) {
                    $wkErrors[] = $objMp->l('Stock location is not valid.', $className);
                }
            }
        }

        if ($mpn = Tools::getValue('mpn')) {
            if (!empty($mpn)) {
                if (!(Tools::strlen($mpn) <= 40)) {
                    $wkErrors[] = $objMp->l('MPN value is too long. It should have 40 character or less.', $className);
                }
            }
        }

        if ($wkErrors) {
            return $wkErrors;
        }

        return false;
    }

    /**
     * JS Validation on all the fields entered by seller during add or update product.
     *
     * @param int $idLang - context lang id
     *
     * @return array/bool
     */
    public static function validationProductFormField($params)
    {
        $className = 'WkMpSellerProduct';
        $objMp = new Marketplace();

        if (isset($params['default_lang'])) {
            $sellerDefaultLanguage = $params['default_lang'];
        } else {
            $sellerDefaultLanguage = $params['seller_default_lang'];
        }
        $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

        $quantity = $params['quantity'];
        if (Configuration::get('WK_MP_PRODUCT_MIN_QTY') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $minimalQuantity = $params['minimal_quantity'];
        } else {
            $minimalQuantity = 1; //default value
        }

        if (Configuration::get('WK_MP_PRODUCT_LOW_STOCK_ALERT') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $lowStockThreshold = $params['low_stock_threshold'];
        } else {
            $lowStockThreshold = '';
        }

        $categories = $params['product_category'];

        $price = $params['price'];

        if (Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $wholesalePrice = $params['wholesale_price'];
        } else {
            $wholesalePrice = '';
        }

        if (Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $unitPrice = $params['unit_price'];
        } else {
            $unitPrice = '';
        }

        if (Configuration::get('WK_MP_PRODUCT_ADDITIONAL_FEES') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $additionalFees = $params['additional_shipping_cost'];
        } else {
            $additionalFees = '';
        }

        if (Configuration::get('WK_MP_SELLER_PRODUCT_REFERENCE') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $reference = trim($params['reference']);
        } else {
            $reference = '';
        }
        if (Configuration::get('WK_MP_SELLER_PRODUCT_EAN') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $ean13JanBarcode = trim($params['ean13']);
        } else {
            $ean13JanBarcode = '';
        }
        if (Configuration::get('WK_MP_SELLER_PRODUCT_UPC') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $upcBarcode = trim($params['upc']);
        } else {
            $upcBarcode = '';
        }
        if (Configuration::get('WK_MP_SELLER_PRODUCT_ISBN') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $isbn = trim($params['isbn']);
        } else {
            $isbn = '';
        }

        if (Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') || Module::isEnabled('mpshipping')) {
            // height, width, depth and weight
            $width = $params['width'];
            $width = empty($width) ? '0' : str_replace(',', '.', $width);

            $height = $params['height'];
            $height = empty($height) ? '0' : str_replace(',', '.', $height);

            $depth = $params['depth'];
            $depth = empty($depth) ? '0' : str_replace(',', '.', $depth);

            $weight = $params['weight'];
            $weight = empty($weight) ? '0' : str_replace(',', '.', $weight);
        } else {
            $width = '';
            $height = '';
            $depth = '';
            $weight = '';
        }

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            if (!Validate::isCatalogName($params['product_name_'.$language['id_lang']])) {
                $invalidProductName = 1;
            }

            if ($params['short_description_'.$language['id_lang']]) {
                $shortDesc = $params['short_description_'.$language['id_lang']];
                $limit = (int) Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');
                if ($limit <= 0) {
                    $limit = 400;
                }
                if (!Validate::isCleanHtml($shortDesc, (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                    $invalidSortDesc = 1;
                } elseif (Tools::strlen(strip_tags($shortDesc)) > $limit) {
                    $invalidSortDesc = 2;
                }
            }

            if ($params['description_'.$language['id_lang']]) {
                if (!Validate::isCleanHtml($params['description_'.$language['id_lang']], (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                    $invalidDesc = 1;
                }
            }
        }

        if (!$params['product_name_'.$defaultLang]) {
            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $sellerLang = Language::getLanguage((int) $defaultLang);
                $msg = sprintf($objMp->l('Product name is required in %s', $className), $sellerLang['name']);
            } else {
                $msg = $objMp->l('Product name is required', $className);
            }
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '1',
                'inputName' => 'product_name_all',
                'msg' => $msg
            );
            die(Tools::jsonEncode($data));
        } elseif (isset($invalidProductName)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '1',
                'inputName' => 'product_name_all',
                'msg' => $objMp->l('Product name have Invalid characters.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        if (isset($invalidSortDesc)) {
            if ($invalidSortDesc == 1) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '1',
                    'inputName' => 'wk_short_desc',
                    'msg' => $objMp->l('Short description have not valid data.', $className)
                );
                die(Tools::jsonEncode($data));
            } elseif ($invalidSortDesc == 2) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '1',
                    'inputName' => 'wk_short_desc',
                    'msg' => sprintf($objMp->l('This short description field is too long: %s characters max.', $className), $limit)
                );
                die(Tools::jsonEncode($data));
            }
        }

        if (isset($invalidDesc)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '1',
                'inputName' => 'wk_desc',
                'msg' => $objMp->l('Product description does not have valid data.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        //Product Price Js Validation
        if ($price == '') {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'price',
                'msg' => $objMp->l('Product price is required field.', $className)
            );
            die(Tools::jsonEncode($data));
        } elseif (!Validate::isPrice($price)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'price',
                'msg' => $objMp->l('Product price should be valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($wholesalePrice != '') {
            if (!Validate::isPrice($wholesalePrice)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '0',
                    'inputName' => 'wholesale_price',
                    'msg' => $objMp->l('Cost price should be valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }
        if ($unitPrice != '') {
            if (!Validate::isPrice($unitPrice)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '0',
                    'inputName' => 'unit_price',
                    'msg' => $objMp->l('Price per unit should be valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }

        //Product Quantity Js Validation
        if ($quantity == '') {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'quantity',
                'msg' => $objMp->l('Product quantity is required field.', $className)
            );
            die(Tools::jsonEncode($data));
        } elseif (!Validate::isInt($quantity)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'quantity',
                'msg' => $objMp->l('Product quantity should be valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($minimalQuantity == '') {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'minimal_quantity',
                'msg' => $objMp->l('Product minimum quantity is required field.', $className)
            );
            die(Tools::jsonEncode($data));
        } elseif (!Validate::isUnsignedInt($minimalQuantity)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'minimal_quantity',
                'msg' => $objMp->l('Product minimum quantity should be valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        if ($lowStockThreshold != '') {
            if (!Validate::isInt($lowStockThreshold)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '0',
                    'inputName' => 'low_stock_threshold',
                    'msg' => $objMp->l('Low stock level should be valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }

        if (!$categories) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'categorycontainer',
                'msg' => $objMp->l('You have not selected any category.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        //Product Package Dimenstion Js Validation
        if ($width && !Validate::isUnsignedFloat($width)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-product-shipping',
                'multilang' => '0',
                'inputName' => 'width',
                'msg' => $objMp->l('Value of width is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($height && !Validate::isUnsignedFloat($height)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-product-shipping',
                'multilang' => '0',
                'inputName' => 'height',
                'msg' => $objMp->l('Value of height is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($depth && !Validate::isUnsignedFloat($depth)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-product-shipping',
                'multilang' => '0',
                'inputName' => 'depth',
                'msg' => $objMp->l('Value of depth is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($weight && !Validate::isUnsignedFloat($weight)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-product-shipping',
                'multilang' => '0',
                'inputName' => 'weight',
                'msg' => $objMp->l('Value of weight is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        if ($additionalFees != '') {
            if (!Validate::isPrice($additionalFees)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-product-shipping',
                    'multilang' => '0',
                    'inputName' => 'additional_shipping_cost',
                    'msg' => $objMp->l('Shipping fees should be valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }

        //Product Reference, EAN, UPC Js Validation
        if ($reference && !Validate::isReference($reference)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'reference',
                'msg' => $objMp->l('Reference is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($ean13JanBarcode) {
            if (!Validate::isEan13($ean13JanBarcode)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-options',
                    'multilang' => '0',
                    'inputName' => 'ean13',
                    'msg' => $objMp->l('EAN-13 or JAN barcode is not valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }
        if ($upcBarcode) {
            if (!Validate::isUpc($upcBarcode)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-options',
                    'multilang' => '0',
                    'inputName' => 'upc',
                    'msg' => $objMp->l('UPC Barcode is not valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }
        if ($isbn) {
            if (!Validate::isIsbn($isbn)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-options',
                    'multilang' => '0',
                    'inputName' => 'isbn',
                    'msg' => $objMp->l('ISBN Code is not valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }

        $productType = (int) $params['product_type'];
        if ($productType == 2) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'selectproduct',
            );
            if (!isset($params['pspk_id_prod']) || !isset($params['pspk_prod_quant'])) {
                $data['msg'] = $objMp->l('This pack is empty. You must add at least one product item.', $className);
                die(Tools::jsonEncode($data));
            } else {
                $pkProdQuant = $params['pspk_prod_quant'];
            }
            if (!empty($pkProdQuant)) {
                foreach ($pkProdQuant as $value) {
                    if (!Validate::isInt($value) || $value <= 0) {
                        $data['msg'] = $objMp->l('Please enter product quantity greater than or equal to 1.', $className);
                        die(Tools::jsonEncode($data));
                    }
                }
            }
        } elseif ($productType == 3) {
            //if virtual product
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
            );
            $mpVirtualProductName = trim($params['mp_vrt_prod_name']);
            $mpVirtualProductNbDownloadable = $params['mp_vrt_prod_nb_downloable'];
            $mpVirtualProductExpDate = $params['mp_vrt_prod_expdate'];
            $mpVirtualProductNbDays = $params['mp_vrt_prod_nb_days'];
            if (Tools::isEmpty($mpVirtualProductName) || !Validate::isCleanHtml($mpVirtualProductName)) {
                $data['inputName'] = 'mp_vrt_prod_name';
                $data['msg'] = $objMp->l('Please enter valid file name.', $className);
                die(Tools::jsonEncode($data));
            } elseif (Tools::strlen($mpVirtualProductName) > 32) {
                $data['inputName'] = 'mp_vrt_prod_name';
                $data['msg'] = sprintf($objMp->l('The filename is too long (%2d chars max).', $className), 32);
                die(Tools::jsonEncode($data));
            }

            if ($mpVirtualProductNbDownloadable != '') {
                if (!Validate::isUnsignedInt($mpVirtualProductNbDownloadable)) {
                    $data['inputName'] = 'mp_vrt_prod_nb_downloable';
                    $data['msg'] = $objMp->l('Number of allowed file downloads is not valid.', $className);
                    die(Tools::jsonEncode($data));
                }
            }

            if ($mpVirtualProductExpDate != '') {
                if (!Validate::isDate($mpVirtualProductExpDate)) {
                    $data['inputName'] = 'mp_vrt_prod_expdate';
                    $data['msg'] = $objMp->l('Expiration date is not valid.', $className);
                    die(Tools::jsonEncode($data));
                }
            }

            if ($mpVirtualProductNbDays != '') {
                if (!Validate::isUnsignedInt($mpVirtualProductNbDays)) {
                    $data['inputName'] = 'mp_vrt_prod_nb_days';
                    $data['msg'] = $objMp->l('Number of days is not valid.', $className);
                    die(Tools::jsonEncode($data));
                }
            }
        }

        if (Configuration::get('WK_MP_PRODUCT_TAGS')) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-options',
                'multilang' => '1',
                'inputName' => 'tag',
            );
            foreach (Language::getLanguages(true) as $language) {
                if ($params['tag_'.$language['id_lang']]) {
                    $productTag = $params['tag_'.$language['id_lang']];
                    $tagData = explode(',', $productTag);
                    if ($tagData) {
                        foreach ($tagData as $tag) {
                            $tag = trim($tag);
                            if (empty($tag)) {
                                $tagError = $objMp->l('The tags list is invalid.', $className).' ';
                                $tagError .= $objMp->l('Tag(s) should not contain spaces.', $className);
                                $data['msg'] = $tagError;
                                die(Tools::jsonEncode($data));
                            } else {
                                $isValidTag = preg_match('/^[^!<>;?=+#"°{}_$%]*$/u', $tag);
                                if ($isValidTag == 0) {
                                    $tagError = $objMp->l('The tags list is invalid.', $className).' ';
                                    $tagError .= $objMp->l('It should not contain special characters.', $className);
                                    $data['msg'] = $tagError;
                                    die(Tools::jsonEncode($data));
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($params['custom_fields'])) {
            $customFields = $params['custom_fields'];
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-options',
                'multilang' => '1',
                'inputName' => 'custom_fields',
            );
            if (!empty($customFields)) {
                foreach ($customFields as $fields) {
                    foreach (Language::getLanguages(true) as $language) {
                        if ($fields['label'][$language['id_lang']] == '' && $language['id_lang'] == $sellerDefaultLanguage) {
                            $fieldError = sprintf(
                                $objMp->l('Customization label field %s is empty', $className),
                                $language['name']
                            );
                            $data['msg'] = $fieldError;
                            die(Tools::jsonEncode($data));
                        } elseif (!Validate::isGenericName($fields['label'][$language['id_lang']])) {
                            $fieldError = sprintf(
                                $objMp->l('Customization label field %s is invalid', $className),
                                $language['name']
                            );
                            $data['msg'] = $fieldError;
                            die(Tools::jsonEncode($data));
                        }
                    }
                }
            }
        }

        if (isset($params['location'])) {
            $location = $params['location'];
            if (!empty($location)) {
                if (!Validate::isString($location)) {
                    $data = array(
                        'status' => 'ko',
                        'tab' => 'wk-information',
                        'multilang' => '0',
                        'inputName' => 'location',
                        'msg' => $objMp->l('Stock location is not valid.', $className)
                    );
                    die(Tools::jsonEncode($data));
                }
            }
        }

        if (Configuration::get('WK_MP_PRODUCT_MPN')) {
            if (isset($params['mpn'])) {
                $mpn = $params['mpn'];
                if (!empty($mpn)) {
                    if (!Validate::isString($mpn)) {
                        $data = array(
                            'status' => 'ko',
                            'tab' => 'wk-options',
                            'multilang' => '0',
                            'inputName' => 'mpn',
                            'msg' =>
                            $objMp->l('MPN value is too long. It should have 40 character or less.', $className)
                        );
                        die(Tools::jsonEncode($data));
                    }
                }
            }
        }

        if (isset($params['selected_suppliers'])) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-options',
                'multilang' => '0',
                'inputName' => 'selected_suppliers',
            );
            $productSuppliers = $params['selected_suppliers'];
            if (!empty($productSuppliers)) {
                foreach ($productSuppliers as $idSupplier) {
                    $supplierCombination = $params['supplier_combination_'.$idSupplier];
                    if (!empty($supplierCombination)) {
                        foreach ($supplierCombination as $sComb) {
                            if (!Validate::isReference($sComb['supplier_reference'])) {
                                $supError =
                                $objMp->l('Supplier reference is not a valid string.', $className);
                                $data['msg'] = $supError;
                                die(Tools::jsonEncode($data));
                            }
                            if (!Validate::isFloat($sComb['product_price'])) {
                                $supError =
                                $objMp->l('Supplier cost price is not a valid price.', $className);
                                $data['msg'] = $supError;
                                die(Tools::jsonEncode($data));
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get product cover - deprecated function
     */
    public static function getCover($mpIdProduct)
    {
        return WkMpSellerProductImage::getProductCoverImage($mpIdProduct);
    }

    /**
     * Get only ps catalog product list (seller product will not include)
     * @param int $idLang - context lang id
     * @return array
     */
    public static function getPsProductsForAssigned($idLang, $idPsShop = false)
    {
        if (!$idPsShop) {
            $idPsShop = Context::getContext()->shop->id;
        }

        $sql =  'SELECT p.`id_product`, pl.`name`
        FROM `'._DB_PREFIX_.'product` p' . Shop::addSqlAssociation('product', 'p') . '
        JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`' . Shop::addSqlRestrictionOnLang('pl') . ')
        WHERE p.`id_product` NOT IN (SELECT `id_ps_product` FROM '._DB_PREFIX_.'wk_mp_seller_product)
        AND pl.`id_lang` = '.(int) $idLang.'
        AND pl.`id_shop` = '.(int) $idPsShop.'
        AND p.`active` = 1 ORDER BY p.`id_product` ASC';

        $assignedProducts = Db::getInstance()->executeS($sql);
        if ($assignedProducts) {
            return $assignedProducts;
        }

        return false;
    }

    /**
     * Assign prestashop product to Seller.
     *
     * @param int $id_product  Prestashop Id Product
     * @param int $id_customer Prestashop Id Customer
     *
     * @return int/boolean Seller ID product or false
     */
    public function assignProductToSeller($idPsProduct, $idCustomer)
    {
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
        if (!$mpSeller) {
            return false;
        }

        $idSeller = $mpSeller['id_seller'];

        //If this prestashop product is not assigned to any seller OR not mapped with any seller
        if (!WkMpSellerProduct::getSellerProductByPsIdProduct($idPsProduct)) {
            //Insert into wk_mp_seller_product table
            $objSellerProduct = new self();
            $objSellerProduct->id_seller = $idSeller;
            $objSellerProduct->id_ps_product = $idPsProduct;

            $idMpShopDefault = Context::getContext()->shop->id;
            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
                //If case of all shop, get shop Id from selected seller
                $idMpShopDefault = $mpSeller['id_shop'];
            }
            $objSellerProduct->id_mp_shop_default = $idMpShopDefault;

            $objSellerProduct->admin_approved = 1;
            $objSellerProduct->status_before_deactivate = 1;
            $objSellerProduct->admin_assigned = 1;  // if product assigned by admin to seller
            if (Pack::isPack($idPsProduct)) {
                $objSellerProduct->is_pack_product = 1;
            }
            $objSellerProduct->save();
            if ($idMpProduct = $objSellerProduct->id) {
                Hook::exec(
                    'actionAfterAssignProduct',
                    array(
                        'id_seller' => $idSeller,
                        'id_product' => $idPsProduct,
                        'mp_id_product' => $idMpProduct,
                    )
                );

                return $idMpProduct;
            }
        }

        return false;
    }

    /**
     * Get seller's product whether added into prestashop or not.
     *
     * @param int  $idSeller Seller ID
     * @param bool $idLang   Language id
     * @param bool $active   activated or not
     *
     * @return array
     */
    public static function getSellerAllShopProduct(
        $idSeller = false,
        $active = 'all',
        $idLang = false,
        $fromExportDate = null,
        $toExportDate = null
    ) {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
        JOIN `'._DB_PREFIX_.'product` p
        ON (p.`id_product` = msp.`id_ps_product`)
        JOIN `'._DB_PREFIX_.'product_lang` pl
        ON (p.`id_product` = pl.`id_product`'.Shop::addSqlRestrictionOnLang('pl').')
        WHERE pl.`id_lang` = '.(int) $idLang;

        if ($idSeller) {
            $sql .= ' AND msp.`id_seller` = '.(int) $idSeller;
        }

        if ($fromExportDate && $toExportDate) {
            $sql .= ' AND DATE(msp.`date_add`) BETWEEN \'' .pSQL($fromExportDate) .'\' AND \''. pSQL($toExportDate). '\'';
        }

        if ($active === true || $active === 1) {
            $sql .= ' AND p.`active` = 1 ';
        } elseif ($active === false || $active === 0) {
            $sql .= ' AND p.`active` = 0 ';
        }

        $mpProducts = Db::getInstance()->executeS($sql);

        Hook::exec(
            'actionSellerProductsListResultModifier',
            array('seller_product_list' => &$mpProducts)
        );

        if (!empty($mpProducts)) {
            $language = Language::getLanguage((int) $idLang);
            $idShopDefault = Context::getContext()->shop->id;

            foreach ($mpProducts as &$mpProduct) {
                if (isset($mpProduct['id_shop_default'])
                && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //In case of all shops
                    $idShopDefault = (int) $mpProduct['id_shop_default'];
                }
                $mpProduct['quantity'] = StockAvailable::getQuantityAvailableByProduct(
                    $mpProduct['id_ps_product'],
                    null,
                    $idShopDefault
                );
                $mpProduct['unit_price'] = self::getProductUnitPrice(
                    $mpProduct['price'],
                    $mpProduct['unit_price_ratio']
                );
                $mpProduct['id_product'] = $mpProduct['id_ps_product'];
                $mpProduct['id_lang'] = $idLang;
                $mpProduct['lang_iso'] = $language['iso_code'];

                $coverImage = WkMpSellerProductImage::getProductCoverImage(
                    $mpProduct['id_mp_product'],
                    $mpProduct['id_ps_product']
                );
                if ($coverImage) {
                    $mpProduct['cover_image'] = $coverImage;
                }

                //convert price for multiple currency
                $mpProduct['price_per_context'] = Tools::convertPrice($mpProduct['price']);
                $mpProduct['price_per_context_without_sign'] = $mpProduct['price_per_context'];
                $mpProduct['price_per_context_with_sign'] = Tools::displayPrice($mpProduct['price_per_context']);

                $objShop = new Shop((int) $mpProduct['id_mp_shop_default']);
                $mpProduct['ps_shop_name'] = $objShop->name;
                unset($objShop);
            }
            return $mpProducts;
        }

        return false;
    }

    /**
     * Get Seller Product Type By Using Seller Id product.
     *
     * @param int  $idMpProduct Seller Id Product
     *
     * @return array/bool array containing seller's product
     */
    public static function getSellerProductTypeByIdProduct($idMpProduct)
    {
        $idPSProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($idMpProduct);
        $product = new Product($idPSProduct);
        if ($product->is_virtual == 1) {
            return ProductType::TYPE_VIRTUAL;
        } elseif ($product->cache_is_pack == 1) {
            return ProductType::TYPE_PACK;
        } else {
            return ProductType::TYPE_STANDARD;
        }
        return false;
    }

    /**
     * Get Customization Fields By Using Seller Id product.
     *
     * @param int  $mpProductId Seller Id Product
     *
     * @return array/bool array
     */
    public function getCustomizationFieldIds($mpProductId)
    {
        $idPSProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpProductId);
        return Db::getInstance()->executeS('
			SELECT `id_customization_field` AS `id`, `type`, `required`
			FROM `'._DB_PREFIX_.'customization_field`
			WHERE `id_product` = '.(int)$idPSProduct);
    }

    /**
     * Get Customization Fields Lang By Using Seller Id product.
     *
     * @param int  $mpProductId Seller Id Product
     * @param int  $idLang Language Id
     *
     * @return array
     */
    public function getLangFieldValue($mpProductId, $idLang = false)
    {
        $idPSProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpProductId);
        if (!$result = Db::getInstance()->executeS('
            SELECT pc.`id_customization_field` AS id, pc.`type` AS type , pc.`required` AS required,
            pcl.`name` AS name, pcl.`id_lang` AS id_lang
            FROM `'._DB_PREFIX_.'customization_field` pc
            NATURAL JOIN `'._DB_PREFIX_.'customization_field_lang` pcl
            WHERE pc.`id_product` = '.(int)$idPSProduct.($idLang ? ' AND pcl.`id_lang` = '.(int)$idLang : '').'
            ORDER BY pc.`id_customization_field`')) {
            return false;
        }

        $customizationFields = array();
        foreach ($result as $row) {
            $customizationFields[(int)$row['id']][(int)$row['id_lang']] = $row;
        }

        return $customizationFields;
    }

    /**
     * Insert customization field values in PS
     *
     * @param int  $mpProductId Seller Id Product
     * @param int  $psProductId PS Id Product
     * @param int  $customFields Customization field details array
     * @param int  $idLang Language Id
     *
     * @return array
     */
    public function insertIntoPsProductCustomization($mpProductId, $psProductId, $customFields)
    {
        if ($psProductId) {
            $files = $text = 0;
            $objProduct = new Product($psProductId);
            if (Tools::getValue('controller') == 'AdminSellerProductDetail') {
                $sellerDefaultLanguage = Tools::getValue('seller_default_lang');
            } else {
                $sellerDefaultLanguage = Tools::getValue('default_lang');
            }
            $objProductCustomization = new WkMpSellerProduct();
            $currentCustomization = $objProductCustomization->getCustomizationFieldIds($mpProductId);
            
            foreach ($customFields as $fields) {
                if ($fields['type'] == 1) {
                    ++$text;
                } else {
                    ++$files;
                }
                $idCustomizationField = $fields['id_customization_field'];
                if ($currentCustomization) {
                    foreach ($currentCustomization as $key => $cc) {
                        if ($cc['id'] == $idCustomizationField) {
                            unset($currentCustomization[$key]);
                            break;
                        }
                    }
                }
                if ($idCustomizationField) {
                    $objCustomizationField = new CustomizationField($idCustomizationField);
                } else {
                    $objCustomizationField = new CustomizationField();
                }
                $objCustomizationField->id_product = (int)$psProductId;
                $objCustomizationField->type = (int)$fields['type'];
                $objCustomizationField->required = (isset($fields['required'])) ? $fields['required'] : 0;
                foreach (Language::getLanguages(true) as $language) {
                    if ($fields['label'][$language['id_lang']] != '') {
                        $labelName = $fields['label'][$language['id_lang']];
                    } else {
                        $labelName = $fields['label'][$sellerDefaultLanguage];
                    }

                    $objCustomizationField->name[$language['id_lang']] = pSQL($labelName);
                }
                $objCustomizationField->save();
            }
            $objProduct->uploadable_files = $files;
            $objProduct->text_fields = $text;
            $objProduct->customizable= ($files > 0 || $text > 0) ? 1 : 0;
            $objProduct->save();

            if (!empty($currentCustomization)) {
                foreach ($currentCustomization as $cc) {
                    $objCustomizationField = new CustomizationField($cc['id']);
                    $objCustomizationField->delete();
                }
            }
        }
    }

    /**
     * Add Related Product in PS product
     *
     * @param int  $idPsProduct PS Id Product
     * @param array  $relatedProducts Customization field details array
     *
     * @return bool
     */
    public static function addRelatedProducts($idPsProduct, $relatedProducts)
    {
        Db::getInstance()->execute(
            'DELETE FROM  `'._DB_PREFIX_.'accessory` WHERE `id_product_1` = '.(int) $idPsProduct
        );
        if (is_array($relatedProducts)) {
            $relatedProducts = array_unique($relatedProducts);
            foreach ($relatedProducts as $relatedProduct) {
                $row = array();
                $row['id_product_1'] = $idPsProduct;
                $row['id_product_2'] = $relatedProduct;
                Db::getInstance()->insert('accessory', $row);
            }
        }
        return true;
    }

    /**
     * Get Related Product in PS product
     *
     * @param int  $idMpProduct MP Id Product
     * @param array  $relatedProducts Customization field details array
     *
     * @return array
     */
    public static function getRelatedProducts($idMpProduct)
    {
        $idPsProduct = self::getPsIdProductByMpIdProduct($idMpProduct);
        $relatedProducts = Db::getInstance()->executeS(
            'SELECT * FROM  `'._DB_PREFIX_.'accessory` WHERE `id_product_1` = '.(int) $idPsProduct
        );
        $results = array();
        if ($relatedProducts) {
            foreach ($relatedProducts as $relatedProduct) {
                $product = new Product($relatedProduct['id_product_2']);
                $idLang = Context::getContext()->language->id;
                $image = $product->getImages($idLang);
                if (isset($image[0]['id_image'])) {
                    $image = str_replace(
                        'http://',
                        Tools::getShopProtocol(),
                        Context::getContext()->link->getImageLink(
                            $product->link_rewrite[$idLang],
                            $image[0]['id_image'],
                            ImageType::getFormattedName('home')
                        )
                    );
                } else {
                    $image = _PS_IMG_.'p/'.Context::getContext()->language->iso_code.'-default-'.
                    ImageType::getFormattedName('home').'.jpg';
                }
                $product = array(
                    'id' => (int)($relatedProduct['id_product_2']),
                    'name' => Product::getProductName($relatedProduct['id_product_2']),
                    'image' => $image,
                );
                array_push($results, $product);
            }
        }
        if ($results) {
            return $results;
        }
        return false;
    }

     /**
     * Attach Mp Seller Attachment
     *
     * @param int  $idSeller MP Id Seller
     * @param array  $idAttachment PS attachment id
     *
     * @return bool
     */
    public static function attachMpSellerAttachment($idSeller, $idAttachment)
    {
        if ($idSeller && $idAttachment) {
            $row = array();
            $row['id_seller'] = $idSeller;
            $row['id_ps_attachment'] = $idAttachment;
            Db::getInstance()->insert('wk_mp_attachments', $row);
        }
        return true;
    }

    /**
     * Get Product Attachments in PS product
     *
     * @return array
     */
    public static function getProductAttachments($idSeller, $idLang)
    {
        $attachmentProducts = Db::getInstance()->executeS(
            'SELECT * FROM  `'._DB_PREFIX_.'wk_mp_attachments` mpa INNER JOIN `'._DB_PREFIX_.'attachment` a ON
            (a.`id_attachment`=mpa.`id_ps_attachment`) INNER JOIN `'._DB_PREFIX_.'attachment_lang` al ON
            (a.`id_attachment`=al.`id_attachment`) WHERE al.`id_lang`='.(int)$idLang
            . ' AND mpa.`id_seller`='.(int)$idSeller
        );
        
        if ($attachmentProducts) {
            return $attachmentProducts;
        }
        return false;
    }

    /**
     * Check Product Mapped with Restricted Product in PS product
     *
     * @return array
     */
    public static function checkCategoryMappedWithProduct($selectedCategories, $idSeller)
    {
        $mpSellerProduct = WkMpSellerProduct::getSellerProduct($idSeller);
        if (!empty($mpSellerProduct) && !empty($selectedCategories)) {
            $productData = array();
            foreach ($mpSellerProduct as $mpProduct) {
                if (!in_array($mpProduct['id_category_default'], $selectedCategories)) {
                    $productData[] = $mpProduct;
                }
            }
        }
        if (!empty($productData)) {
            return $productData;
        }

        return false;
    }

    public static function getAllCustomizedDatas(
        $idCart,
        $idShop,
        $idCustomization
    ) {
        $customizedDatas = Db::getInstance()->executeS('
            SELECT cd.`id_customization`, c.`id_address_delivery`, c.`id_product`, cfl.`id_customization_field`,
            c.`id_product_attribute`, cd.`type`, cd.`index`, cd.`value`, cd.`id_module`, cfl.`name`
            FROM `' . _DB_PREFIX_ . 'customized_data` cd
            NATURAL JOIN `' . _DB_PREFIX_ . 'customization` c
            LEFT JOIN `' . _DB_PREFIX_ . 'customization_field_lang` cfl ON (cfl.id_customization_field = cd.`index`
            AND id_lang = ' . (int) Context::getContext()->language->id .
            ($idShop ? ' AND cfl.`id_shop` = ' . (int) $idShop : '') . ')
            WHERE c.`id_cart` = ' . (int) $idCart.
            ' AND c.`in_cart` = 1' .
            ((int) $idCustomization ? ' AND cd.`id_customization` = ' . (int) $idCustomization : '') . '
            ORDER BY `id_product`, `id_product_attribute`, `type`, `index`');

        return $customizedDatas;
    }
}
