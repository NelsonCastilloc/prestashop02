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

class WkMpProductAttribute
{
    /**
     * Get Seller Product Id according to combination Id
     *
     * @param int $idPsProductAttribute Ps combination id
     *
     * @return bool/int
     */
    public static function getSellerProductIdByIdCombination($idPsProductAttribute)
    {
        $objCombination = new Combination($idPsProductAttribute);
        if ($idPsProduct = $objCombination->id_product) {
            if ($mpIdProduct = WkMpSellerProduct::getMpIdProductByPsIdProduct($idPsProduct)) {
                return $mpIdProduct;
            }
        }
        return false;
    }

    /**
     * Assign all details that are used for creating a combination ie. groups, countries, currencied etc.
     *
     * @param int $mpProduct            seller product details array
     * @param int $idMpProduct          seller product id
     * @param int $idPsProductAttribute Ps combination id
     *
     * @return array
     */
    public static function assignCombinationCreationFormData($mpProduct, $idMpProduct, $idPsProductAttribute = false)
    {
        $context = Context::getContext();

        $idShopDefault = $context->shop->id;
        if (isset($mpProduct['id_mp_shop_default'])
        && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //In case of all shops
            $idShopDefault = (int) $mpProduct['id_mp_shop_default'];
        }

        $idPsProduct = $mpProduct['id_ps_product'];
        $mpProductPrice = $mpProduct['price'];

        // ADDED FOR TAX CALCULATION
        $idTaxRulesGroup = $mpProduct['id_tax_rules_group'];
        $taxesRatesByGroup = TaxRulesGroup::getAssociatedTaxRatesByIdCountry(
            Configuration::get('PS_COUNTRY_DEFAULT')
        );
        if (isset($taxesRatesByGroup[$idTaxRulesGroup])) {
            $taxRate = $taxesRatesByGroup[$idTaxRulesGroup];
        } else {
            $taxRate = 0;
        }

        // ADDED FOR COMBINATION IMAGES
        $psImages = Image::getImages($context->language->id, $idPsProduct);
        $i = 0;
        foreach ($psImages as $k => $image) {
            $psImages[$k]['obj'] = new Image($image['id_image']);
            ++$i;
        }

        $context->smarty->assign('mp_pro_image', $psImages);
        $context->smarty->assign('is_ps_product', 1);

        if ($idPsProductAttribute) { //edit combination
            $attributeBoxGroupIds = array();

            $objProduct = new Product($idPsProduct);

            //Get Selected combination attribute group and value for display combination in Box
            $i = 0;
            $selectedAttributeInBox = array();
            $attributeIdsSet = $objProduct->getAttributeCombinationsById(
                $idPsProductAttribute,
                $context->language->id,
                true
            );
            $attributes = Attribute::getAttributes($context->language->id, true);
            if ($attributes && $attributeIdsSet) {
                foreach ($attributes as $attributeVal) {
                    foreach ($attributeIdsSet as $attributeIdsSetVal) {
                        if ($attributeVal['id_attribute'] == $attributeIdsSetVal['id_attribute']) {
                            $selectedAttributeInBox[$i]['groupid'] = $attributeVal['id_attribute_group'];
                            $selectedAttributeInBox[$i]['id'] = $attributeVal['id_attribute'];
                            $selectedAttributeInBox[$i]['name'] = $attributeVal['attribute_group'].' : '.$attributeVal['name'];
                            $attributeBoxGroupIds[$i] = $attributeVal['id_attribute_group'];
                            ++$i;
                        }
                    }
                }
            }

            // Calculate tax include impact price on this combination
            $objCombination = new Combination($idPsProductAttribute);
            $impactPrice = $objCombination->price;
            $impactTaxIncl = ($impactPrice) * (($taxRate / 100) + 1);
            $context->smarty->assign(array(
                'impact_tax_incl' => $impactTaxIncl,
                'selectedAttributeInBox' => $selectedAttributeInBox,
                'id_combination' => $idPsProductAttribute,
                'productAttribute' => (array) $objCombination,
                'quantity' => StockAvailable::getQuantityAvailableByProduct(
                    $idPsProduct,
                    $idPsProductAttribute,
                    $idShopDefault
                ),
                'ps_attribute_images' => self::getPsAttributeImages($idPsProductAttribute),
                'edit' => 1,
            ));
        }

        if (isset($attributeBoxGroupIds)) {
            $selectedAttributeGroup = Tools::jsonEncode($attributeBoxGroupIds);
        } else {
            $selectedAttributeGroup = array();
        }

        Media::addJsDef(array(
                'selected_attribute_group' => $selectedAttributeGroup,
                'tax_rate' => $taxRate,
            ));

        $context->smarty->assign(array(
            'mp_id_product' => $idMpProduct,
            'mp_product_price' => $mpProductPrice,
            'attributeGroup' => AttributeGroup::getAttributesGroups($context->language->id),
            'def_currency' => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
            'ps_weight_unit' => Configuration::get('PS_WEIGHT_UNIT'),
            'logic' => 1,
        ));
    }

    /**
     * Get images of product attribute using prestashop product attribute id.
     *
     * @param int $idPsProductAttribute Prestashop Product attribute id
     *
     * @return array/bool
     */
    public static function getPsAttributeImages($idPsProductAttribute)
    {
        return Db::getInstance()->executeS(
            'SELECT `id_image` FROM `'._DB_PREFIX_.'product_attribute_image`
            WHERE `id_product_attribute`  = '.(int) $idPsProductAttribute
        );
    }

    /**
     * Create/Update marketplace product combination with given data and
     * if product is active then also create/update for prestashop product.
     *
     * @param type $idMpProduct - Seller product id
     * @param type $idPsProductAttribute - Seller product attribute id
     * @param type $productAttributeList - Product combination attribute list
     * @param type $mpReference - Combination reference
     * @param type $mpEan13 - Combination EAN
     * @param type $mpUPC - Combination UPC
     * @param type $mpISBN - Combination ISBN
     * @param type $mpPrice - Combination price
     * @param type $mpWholesalePrice - Combination wholesale price
     * @param type $mpUnitPriceImpact - Combination impact on unit price price
     * @param type $mpQuantity - Combination quantity
     * @param type $mpWeight - Combination weight
     * @param type $mpMinimalQuantity - Combination minimum quantity
     * @param type $mpAvailableDate - Combination available date
     * @param type $idImages - Combination images
     * @param type $lowStockThreshold - Combination low stock level value
     * @param type $lowStockAlert - Combination low stock level checkbox
     *
     * @return int
     */
    public static function saveMpProductCombination(
        $idMpProduct,
        $idPsProductAttribute,
        $productAttributeList,
        $mpReference,
        $mpEan13,
        $mpUPC,
        $mpISBN,
        $mpPrice,
        $mpWholesalePrice,
        $mpUnitPriceImpact,
        $mpQuantity,
        $mpWeight,
        $mpMinimalQuantity,
        $mpAvailableDate,
        $idImages,
        $lowStockThreshold = false,
        $lowStockAlert = false,
        $location = '',
        $mpMPN = ''
    ) {
        if (!$lowStockThreshold) {
            $lowStockThreshold = 0;
        }
        if (!$lowStockAlert) {
            $lowStockAlert = 0;
        }

        $idPsProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($idMpProduct);
        if (!$idPsProduct) {
            return false;
        }

        $objMpProductAttribute = new self();

        if ($idPsProductAttribute) {
            //edit combination
            $editCombi = 1;
            $objCombination = new Combination($idPsProductAttribute);
        } else {
            //Create combination
            $editCombi = 0;
            $objCombination = new Combination();
        }

        $objCombination->id_product = (int)$idPsProduct;
        $objCombination->reference = pSQL($mpReference);
        $objCombination->ean13 = pSQL($mpEan13);
        $objCombination->upc = pSQL($mpUPC);
        $objCombination->isbn = pSQL($mpISBN);
        $objCombination->mpn = pSQL($mpMPN);
        $objCombination->price = (float)$mpPrice;
        $objCombination->wholesale_price = (float)$mpWholesalePrice;
        $objCombination->unit_price_impact = (float)$mpUnitPriceImpact;
        $objCombination->weight = (float)$mpWeight;
        $objCombination->minimal_quantity = (int)$mpMinimalQuantity;
        $objCombination->available_date = $mpAvailableDate;
        if (_PS_VERSION_ >= '1.7.3.0') {
            //Prestashop added this feature in PS V1.7.3.0 and above
            $objCombination->low_stock_threshold = (int)$lowStockThreshold;
            $objCombination->low_stock_alert = (int)$lowStockAlert;
        }
        if ($location != '') {
            $objCombination->location = pSQL($location);
        }
        $psProductHasCombination = self::getPsProductDefaultAttributesIds($idPsProduct);
        if (!$psProductHasCombination) {
            $objCombination->default_on = 1;
        }

        if ($objCombination->save()) {
            if ($editCombi) {
                //if admin delete combination from catalog
                //then another combination will automatially created when seller update the combination of product.
                self::deleteProductAttrCombByPsAttrId($idPsProductAttribute);
            }

            $idPsProductAttribute = $objCombination->id;

            foreach ($productAttributeList as $group) {
                $objMpProductAttribute->insertIntoPsProductCombination($group, $idPsProductAttribute);
            }

            $objCombination->setImages($idImages); //combination ps Images

            $idShopDefault = Context::getContext()->shop->id;
            $objMpProduct = new WkMpSellerProduct($idMpProduct);
            if (isset($objMpProduct->id_mp_shop_default) && $objMpProduct->id_mp_shop_default) {
                $idShopDefault = (int) $objMpProduct->id_mp_shop_default;
            }
            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //In case of all shops
                Shop::setContext(Shop::CONTEXT_SHOP, $idShopDefault);
            }
            StockAvailable::setQuantity($idPsProduct, $idPsProductAttribute, $mpQuantity, $idShopDefault);

            //If mp product combination create or update
            Hook::exec(
                'actionAfterUpdateMPProductCombination',
                array(
                    'id_mp_product' => $idMpProduct,
                    'id_ps_product' => $idPsProduct,
                    'id_ps_product_attribute' => $idPsProductAttribute
                )
            );
            
            // Set Location in Stock
            if (isset($location) && $location) {
                StockAvailable::setLocation($idPsProduct, $location, null, $idPsProductAttribute);
            }
            return $idPsProductAttribute;
        }

        return false;
    }

    /**
     * This function is deprecated in Mp V5.2.0 and V3.2.0 (but may be using in mp addons)
     */
    public static function createOrUpdateMpProductCombination(
        $idMpProduct,
        $idPsProductAttribute,
        $productAttributeList,
        $mpReference,
        $mpEan13,
        $mpUPC,
        $mpISBN,
        $mpPrice,
        $mpWholesalePrice,
        $mpUnitPriceImpact,
        $mpQuantity,
        $mpWeight,
        $mpMinimalQuantity,
        $mpAvailableDate,
        $idImages,
        $lowStockThreshold = false,
        $lowStockAlert = false
    ) {
        if (!$idPsProductAttribute) {
            $idPsProductAttribute = 0;
        }
        if (!$lowStockThreshold) {
            $lowStockThreshold = 0;
        }
        if (!$lowStockAlert) {
            $lowStockAlert = 0;
        }

        return self::saveMpProductCombination(
            $idMpProduct,
            $idPsProductAttribute,
            $productAttributeList,
            $mpReference,
            $mpEan13,
            $mpUPC,
            $mpISBN,
            $mpPrice,
            $mpWholesalePrice,
            $mpUnitPriceImpact,
            $mpQuantity,
            $mpWeight,
            $mpMinimalQuantity,
            $mpAvailableDate,
            $idImages,
            $lowStockThreshold,
            $lowStockAlert,
            '',
            ''
        );
    }

    /**
     * Get ps product default combination details according to ps product id.
     *
     * @param int $idPsProduct PS product id
     *
     * @return array
     */
    public static function getPsProductDefaultAttributesIds($idPsProduct)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'product_attribute`
            WHERE `default_on` = 1 AND `id_product` = '.(int) $idPsProduct
        );
    }

    public static function deleteProductAttrCombByPsAttrId($idPsProductAttribute)
    {
        return Db::getInstance()->delete(
            'product_attribute_combination',
            '`id_product_attribute` = '.(int) $idPsProductAttribute
        );
    }

    /**
     * Get ps product default combination details according to ps product id.
     *
     * @param int $idAttribute        collection of id attribute of making combination
     * @param int $idProductAttribute product attribute id
     *
     * @return bool
     */
    public function insertIntoPsProductCombination($idAttribute, $idProductAttribute)
    {
        return Db::getInstance()->insert(
            'product_attribute_combination',
            array(
                'id_attribute' => (int) $idAttribute,
                'id_product_attribute' => (int) $idProductAttribute,
            )
        );
    }

    /**
     * Get total quantity of all combinations of any seller product.
     *
     * @param int $idMpProduct seller product id
     *
     * @return array
     */
    public static function getMpProductQty($idMpProduct)
    {
        if ($psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($idMpProduct)) {
            return StockAvailable::getQuantityAvailableByProduct(
                $psIdProduct,
                null,
                Context::getContext()->shop->id
            );
        }
        return false;
    }

    /**
     * Assign and Display product combination list at update product page in frontend/backend.
     *
     * @param int $mpIdProduct seller product id
     *
     * @return bool
     */
    public static function displayProductCombinationList($mpIdProduct)
    {
        $context = Context::getContext();

        // check if pack product or virtual product If yes combinations will not be shown.
        $isVirtualProduct = 0;
        $isPackProduct = 0;

        if (Configuration::get('WK_MP_PACK_PRODUCTS')) {
            $objPackProduct = new WkMpPackProduct();
            $isPackProduct = $objPackProduct->isPackProduct($mpIdProduct);
        }
        if (Configuration::get('WK_MP_VIRTUAL_PRODUCT')) {
            $objVirtualProduct = new WkMpVirtualProduct();
            $isVirtualProduct = $objVirtualProduct->isMpProductIsVirtualProduct($mpIdProduct);
        }

        if (!$isPackProduct && !$isVirtualProduct) {
            //if product is not a virtual product or pack product
            //check ps product (if exist) is virtual or not
            $flag = 0;
            if ($psProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
                $objProduct = new Product($psProductId);

                $isVirtualProduct = $objProduct->is_virtual;
                if ($isVirtualProduct == 1) {
                    $flag = 1;
                }

                if ($flag == 0) { //if product is not a virtual product
                    $combinationDetail = self::getMpCombinationsResume($mpIdProduct);
                    if ($combinationDetail) {
                        $context->smarty->assign('combination_detail', $combinationDetail);
                    }

                    $context->smarty->assign(array(
                        'id' => $mpIdProduct,
                        'def_currency_id' => Configuration::get('PS_CURRENCY_DEFAULT'),
                        'ps_weight_unit' => Configuration::get('PS_WEIGHT_UNIT'),
                        'admin_img_path' => _PS_ADMIN_IMG_,
                        'modules_dir' => _MODULE_DIR_,
                        'link' => $context->link,
                    ));
                }
            }
        }
    }

    public static function getMpCombinationsResume($mpIdProduct, $psProductId = false)
    {
        $context = Context::getContext();

        if (!$psProductId) {
            $psProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct);
        }

        if ($psProductId) {
            $objProduct = new Product($psProductId);
            $combinationDetail = $objProduct->getAttributesResume($context->language->id);
            if ($combinationDetail) {
                $idShopDefault = $context->shop->id;
                $objMpProduct = new WkMpSellerProduct($mpIdProduct);
                foreach ($combinationDetail as &$valCombination) {
                    if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) { //In case of all shops
                        $idShopDefault = (int) $objMpProduct->id_mp_shop_default;
                    }

                    $valCombination['id_mp_product'] = $mpIdProduct;
                    $valCombination['mp_quantity'] = StockAvailable::getQuantityAvailableByProduct(
                        $psProductId,
                        $valCombination['id_product_attribute'],
                        $idShopDefault
                    );
                    $valCombination['mp_price'] = Tools::displayPrice(
                        $valCombination['price'],
                        new Currency(Configuration::get('PS_CURRENCY_DEFAULT'))
                    );
                    $valCombination['mp_weight'] = $valCombination['weight'];
                    $valCombination['mp_reference'] = $valCombination['reference'];
                    $valCombination['mp_default_on'] = $valCombination['default_on'];
                    //$valCombination['active'] = $valCombination['active']; //For combination activate/deactivate
                    $valCombination['active'] = 1;
                }

                return $combinationDetail;
            }
        }

        return false;
    }

    /**
     * Update default combination for seller product through ajaxProcess.
     */
    public static function updateMpProductDefaultAttribute()
    {
        $idPsProductAttribute = Tools::getValue('id_combination');
        if ($idPsProductAttribute) {
            $mpIdProduct = Tools::getValue('id_mp_product');
            $combinationMpProductId = self::getSellerProductIdByIdCombination($idPsProductAttribute);
            //Check condition if combination is existing in seller product
            //Rest seller condition will be check by initContent()
            if ($combinationMpProductId == $mpIdProduct) {
                //Set a combination as default combination in existing combinations
                if (self::setMpProductDefaultAttribute($mpIdProduct, $idPsProductAttribute)) {
                    //To manage staff log (changes add/update/delete)
                    if (Tools::getValue('controller') == 'managecombination') {
                        WkMpHelper::setStaffHook(
                            Context::getContext()->customer->id,
                            'managecombination',
                            $mpIdProduct,
                            2
                        ); // 2 for Update action
                    }
                    die('1'); //ajax close
                }
            }
        }

        die('fail'); //ajax close
    }

    /**
     * Delete seller product combination through ajaxProcess.
     */
    public static function deleteMpProductAttribute()
    {
        //Delete Product combination from combination list at edit product page
        $idPsProductAttribute = Tools::getValue('id_combination');
        if ($idPsProductAttribute) {
            $mpIdProduct = Tools::getValue('id_mp_product');
            $combinationMpProductId = self::getSellerProductIdByIdCombination($idPsProductAttribute);
            //Check condition if combination is existing in seller product
            //Rest seller condition will be check by initContent()
            if ($combinationMpProductId == $mpIdProduct) {
                //delete Mp combination
                if (self::deleteSellerProductCombination($mpIdProduct, $idPsProductAttribute)) {
                    WkMpSellerProduct::deactivateProductAfterUpdate($mpIdProduct);
                    //To manage staff log (changes add/update/delete)
                    if (Tools::getValue('controller') == 'managecombination') {
                        WkMpHelper::setStaffHook(
                            Context::getContext()->customer->id,
                            'managecombination',
                            $mpIdProduct,
                            3
                        ); // 3 for delete action
                    }
                    die('1'); //ajax close
                }
            }
        }

        die('fail'); //ajax close
    }

    /**
     * Set a default attribute in existing combination of seller product by product attribute id.
     *
     * @param int $idPsProductAttribute Ps product attribute id
     *
     * @return array
     */
    public static function setMpProductDefaultAttribute($mpIdProduct, $idPsProductAttribute)
    {
        if ($psProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
            $objProduct = new Product($psProductId);

            if ($objProduct->deleteDefaultAttributes()
            && $objProduct->setDefaultAttribute($idPsProductAttribute)) {
                return true;
            }
        }

        return false;
    }

    public static function deleteSellerProductCombination($mpIdProduct, $idPsProductAttribute)
    {
        if ($psProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
            Hook::exec(
                'actionBeforeMpProductAttributeDelete',
                array(
                    'id_mp_product' => (int) $mpIdProduct,
                    'id_ps_product' => (int) $psProductId,
                    'id_product_attribute' => (int) $idPsProductAttribute,
                )
            );

            $objProduct = new Product($psProductId);
            if ($objProduct->deleteAttributeCombination((int) $idPsProductAttribute)) {
                $objProduct->checkDefaultAttributes();
                Tools::clearColorListCache((int) $objProduct->id);
                if (!$objProduct->hasAttributes()) {
                    $objProduct->cache_default_attribute = 0;
                    $objProduct->update();
                } else {
                    Product::updateDefaultAttribute($psProductId);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Set Mp product combination qty
     *
     * @param int  $idPsProductAttribute    Prestashop Product Combination Id
     * @param int $combinationQty   Combination qty
     *
     * @return bool
     */
    public static function setMpProductCombinationQuantity($idPsProductAttribute, $combinationQty)
    {
        if (($combinationQty == '') || (!Validate::isInt($combinationQty))) {
            die('10'); //If invalid value
        }

        $mpIdProduct = Tools::getValue('id_mp_product');
        if ($idPsProductAttribute && $mpIdProduct) {
            $combinationMpProductId = self::getSellerProductIdByIdCombination($idPsProductAttribute);
            //Check condition if combination is existing in seller product
            //Rest seller condition will be check by initContent()
            if ($combinationMpProductId == $mpIdProduct) {
                if ($psProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
                    $oldQuantity = StockAvailable::getQuantityAvailableByProduct(
                        $psProductId,
                        $idPsProductAttribute,
                        Context::getContext()->shop->id
                    );

                    //If seller change combination qty
                    if ($oldQuantity != $combinationQty) {
                        //Update combination qty
                        StockAvailable::setQuantity($psProductId, $idPsProductAttribute, $combinationQty);

                        if (Configuration::get('WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE')
                        && ('updateproduct' == Tools::getValue('controller'))) {
                            //deactivate product if seller update product by change combi qty
                            WkMpSellerProduct::deactivateProductAfterUpdate($mpIdProduct);
                        }
                        //To manage staff log (changes add/update/delete)
                        if (Tools::getValue('controller') == 'managecombination') {
                            WkMpHelper::setStaffHook(
                                Context::getContext()->customer->id,
                                'managecombination',
                                $mpIdProduct,
                                2
                            ); // 2 for Update action
                        }
                        die('1');
                    }
                }
            } else {
                die('Something went wrong!');
            }
        }
        die('no change'); //ajax close
    }

    /**
     * Get Attribute Value after choosing group ie. when choose color, display all colors in value field.
     *
     * @param int $attributeGroupId Attribute Group id
     *
     * @return array
     */
    public static function getAttributeValueByGroup($attributeGroupId)
    {
        //Get Attribute Value according to Attribute Group
        if ($attributeGroupId) {
            $i = 0;
            $attributeVal = array();
            $attributes = Attribute::getAttributes(Context::getContext()->language->id, true);
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    if ($attributeGroupId == $attribute['id_attribute_group']) {
                        $attributeVal[$i]['id'] = $attribute['id_attribute'];
                        $attributeVal[$i]['name'] = $attribute['name'];
                        ++$i;
                    }
                }

                return $attributeVal;
            }
        }

        return false;
    }

    public static function copyMpProductCombination($originalMpProductId, $duplicateMpProductId)
    {
        $originalPsProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($originalMpProductId);
        $duplicatePsProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($duplicateMpProductId);
        if ($originalPsProductId && $duplicatePsProductId) {
            return self::duplicateAttributes($originalPsProductId, $duplicatePsProductId);
        }
        return false;
    }

    public static function assignAttributeValues()
    {
        $message = Tools::getValue('msg');
        if ($message) {
            Context::getContext()->smarty->assign('message', $message);
        } else {
            Context::getContext()->smarty->assign('message', 0);
        }

        $idLang = Context::getContext()->language->id;
        $mpProductId = Tools::getValue('id_mp_product');
        $psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpProductId);
        if (!$psIdProduct) {
            return false;
        }

        $objProduct = new Product($psIdProduct);

        $mpAttirbuteCombination = Configuration::get('MP_ATTRIBUTE_COMBINATION');
        if ($mpAttirbuteCombination == 1) {
            Hook::exec('actionAttibuteDisplayBySeller', array('id_mp_product' => $mpProductId));
        } elseif ($mpAttirbuteCombination == 2) {
            Hook::exec('actionAttibuteDisplayByBoth', array('id_mp_product' => $mpProductId));
        } else {
            //only by admin
            $attributeData = array();
            $attributeDetail = AttributeGroup::getAttributesGroups($idLang);
            foreach ($attributeDetail as $attributeDetailEach) {
                $name = $attributeDetailEach['name'];
                $idAttributeGroup = $attributeDetailEach['id_attribute_group'];
                $attributeValueInfo = AttributeGroup::getAttributes($idLang, $idAttributeGroup);
                $attributeData[] = array(
                    'attibute_group_name' => $name,
                    'id_attribute_group' => $idAttributeGroup,
                    'attribute_value' => $attributeValueInfo
                );
            }
        }

        $combinationsGroups = $objProduct->getAttributesGroups($idLang);
        $attributes = array();

        $taxRate = 0;
        $taxesRatesByGroup = TaxRulesGroup::getAssociatedTaxRatesByIdCountry(Configuration::get('PS_COUNTRY_DEFAULT'));
        $idTaxRule = $objProduct->id_tax_rules_group;
        if (isset($taxesRatesByGroup[$idTaxRule])) {
            $taxRate = $taxesRatesByGroup[$idTaxRule];
        } else {
            $taxRate = 0;
        }

        $impacts = Product::getAttributesImpacts($psIdProduct);
        foreach ($combinationsGroups as &$combination) {
            $newPrice = ($combination['price']) * (($taxRate / 100) + 1);
            $target = &$attributes[$combination['id_attribute_group']][$combination['id_attribute']];
            $combination['price_tx_incl'] = $newPrice;
            $target = $combination;
            if (isset($impacts[$combination['id_attribute']])) {
                $newPrice = ($impacts[$combination['id_attribute']]['price']) * (($taxRate / 100) + 1);
                $target['price'] = $impacts[$combination['id_attribute']]['price'];
                $target['price_incl'] = $newPrice;
                $target['weight'] = $impacts[$combination['id_attribute']]['weight'];
            }
        }

        $defaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        Context::getContext()->smarty->assign(array(
            'wkself' => dirname(__FILE__),
            'logic' => 'mp_prod_attribute',
            'currency_sign' => $defaultCurrency->sign,
            'attribute_groups' => AttributeGroup::getAttributesGroups($idLang),
            'weight_unit' => Configuration::get('PS_WEIGHT_UNIT'),
            'id_mp_product' => $mpProductId,
            'attribute_array' => $attributeData,
            'mp_attirbute_com' => $mpAttirbuteCombination,
            'tax_rates' => $taxRate,
            'attributes' => $attributes,
        ));

        Media::addJsDef(array(
            'product_tax' => $taxRate,
        ));
    }

    public static function setAttributesImpacts($idPsProduct, $tab)
    {
        $attributes = array();
        foreach ($tab as $group) {
            foreach ($group as $attribute) {
                $price = preg_replace('/[^0-9.-]/', '', str_replace(',', '.', Tools::getValue('price_impact_'.(int) $attribute)));
                $weight = preg_replace('/[^0-9.-]/', '', str_replace(',', '.', Tools::getValue('weight_impact_'.(int) $attribute)));
                $attributes[] = '('.(int) $idPsProduct.', '.(int) $attribute.', '.(float) $price.', '.(float) $weight.')';
            }
        }

        $sql = 'INSERT INTO `'._DB_PREFIX_.'attribute_impact` (`id_product`, `id_attribute`, `price`, `weight`) VALUES '.pSQL(implode(',', $attributes)).' ON DUPLICATE KEY UPDATE `price` = VALUES(price), `weight` = VALUES(weight)';

        $result = Db::getInstance()->execute($sql);
        if ($result) {
            return $result;
        }

        return false;
    }

    public static function ifColorAttributegroup($groupId)
    {
        $objAttrGroup = new AttributeGroup($groupId);
        $flag = $objAttrGroup->is_color_group;
        if ($flag == 1) {
            return true;
        }

        return false;
    }

    public static function checkCombinationByAttribute($attributeId)
    {
        $result = Db::getInstance()->getValue(
            'SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute_combination`
            WHERE `id_attribute` = '.(int) $attributeId
        );
        if (!$result) {
            return false;
        }

        return true;
    }

    public static function checkCombinationByGroup($idLang, $attribute_group)
    {
        $groupAttribute = AttributeGroup::getAttributes($idLang, $attribute_group);
        $existFlag = 0;
        foreach ($groupAttribute as $groupAttributeEach) {
            $result = self::checkCombinationByAttribute($groupAttributeEach['id_attribute']);
            if ($result) {
                $existFlag = 1;
            }
        }

        return $existFlag;
    }

    public static function setIndexableValue($data)
    {
        return Db::getInstance()->insert('layered_indexable_attribute_group', $data);
    }

    /**
    * Duplicate attributes when duplicating a product.
    *
    * @param int $originalPsProductId Old product id
    * @param int $duplicatePsProductId New product id
    *
    * @return array/boolean
    */
    public static function duplicateAttributes($originalPsProductId, $duplicatePsProductId)
    {
        $return = true;
        $combinationImages = [];

        $result = Db::getInstance()->executeS(
            'SELECT pa.*, product_attribute_shop.*
            FROM `'._DB_PREFIX_.'product_attribute` pa '.Shop::addSqlAssociation('product_attribute', 'pa').'
            WHERE pa.`id_product` = '.(int) $originalPsProductId
        );
        $combinations = [];

        foreach ($result as $row) {
            $idProductAttributeOld = (int) $row['id_product_attribute'];

            if (!isset($combinations[$idProductAttributeOld])) {
                $idCombination = null;
                $idShop = null;
                $result2 = Db::getInstance()->executeS(
                    'SELECT * FROM `'._DB_PREFIX_.'product_attribute_combination`
                    WHERE `id_product_attribute` = '.(int) $idProductAttributeOld
                );
            } else {
                $idCombination = (int) $combinations[$idProductAttributeOld];
                $idShop = (int) $row['id_shop'];
                $contextOld = Shop::getContext();
                $contextShopIdOld = Shop::getContextShopID();
                Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
            }

            $row['id_product'] = $duplicatePsProductId;
            unset($row['id_product_attribute']);

            $combination = new Combination($idCombination, null, $idShop);
            foreach ($row as $k => $v) {
                if ($k !== 'id_shop') {
                    $combination->$k = $v;
                }
            }
            $return &= $combination->save();

            $idProductAttributeNew = (int) $combination->id;

            if ($resultImages = Product::_getAttributeImageAssociations($idProductAttributeOld)) {
                $combinationImages['old'][$idProductAttributeOld] = $resultImages;
                $combinationImages['new'][$idProductAttributeNew] = $resultImages;
            }

            if (!isset($combinations[$idProductAttributeOld])) {
                $combinations[$idProductAttributeOld] = (int) $idProductAttributeNew;
                foreach ($result2 as $row2) {
                    $row2['id_product_attribute'] = (int) $idProductAttributeNew;
                    $return &= Db::getInstance()->insert(
                        'product_attribute_combination',
                        $row2
                    );
                }
            } else {
                Shop::setContext($contextOld, $contextShopIdOld);
            }

            //Copy Stock
            if (!Configuration::get('WK_MP_PRODUCT_DUPLICATE_QUANTITY')) {
                $wkOldCombinationQty = StockAvailable::getQuantityAvailableByProduct(
                    $originalPsProductId,
                    $idProductAttributeOld,
                    Context::getContext()->shop->id
                );
                StockAvailable::updateQuantity(
                    $duplicatePsProductId,
                    $idProductAttributeNew,
                    $wkOldCombinationQty,
                    Context::getContext()->shop->id
                );
            }

            //Copy suppliers
            $result3 = Db::getInstance()->executeS(
                'SELECT * FROM `'._DB_PREFIX_.'product_supplier`
                WHERE `id_product_attribute` = '.(int) $idProductAttributeOld.'
                AND `id_product` = '.(int) $originalPsProductId
            );

            foreach ($result3 as $row3) {
                unset($row3['id_product_supplier']);
                $row3['id_product'] = (int) $duplicatePsProductId;
                $row3['id_product_attribute'] = (int) $idProductAttributeNew;
                $return &= Db::getInstance()->insert('product_supplier', $row3);
            }
        }

        $impacts = Product::getAttributesImpacts($originalPsProductId);

        if (is_array($impacts) && count($impacts)) {
            $impactSql = 'INSERT INTO `'._DB_PREFIX_.'attribute_impact` (`id_product`, `id_attribute`, `weight`, `price`) VALUES ';

            foreach ($impacts as $idAttribute => $impact) {
                $impactSql .= '(' . (int) $duplicatePsProductId . ', ' . (int) $idAttribute . ', ' .
                (float) $impacts[$idAttribute]['weight'] . ', ' . (float) $impacts[$idAttribute]['price'] . '),';
            }

            $impactSql = substr_replace($impactSql, '', -1);
            $impactSql .= ' ON DUPLICATE KEY UPDATE `price` = VALUES(price), `weight` = VALUES(weight)';

            Db::getInstance()->execute($impactSql);
        }

        return !$return ? false : $combinationImages;
    }
}
