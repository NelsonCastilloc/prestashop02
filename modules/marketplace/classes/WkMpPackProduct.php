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

class WkMpPackProduct
{
    /**
     * Check pack product details by using MP Product ID
     *
     * @param int $id MP Product ID
     * @return bool value
     */
    public function isPackProduct($id)
    {
        if ($id) {
            return Db::getInstance()->getValue('SELECT `is_pack_product` FROM `'._DB_PREFIX_.'wk_mp_seller_product`
            WHERE `id_mp_product` = '.(int) $id);
        }

        return false;
    }

    /**
     * Update pack product type by using MP Product ID
     *
     * @param int $mpIdProd MP Product ID
     * @param int $isPackProduct Pack Product bool value
     * @return bool
     */
    public function isPackProductFieldUpdate($mpIdProd, $isPackProduct)
    {
        return Db::getInstance()->update(
            'wk_mp_seller_product',
            array('is_pack_product' => $isPackProduct),
            'id_mp_product = '.(int) $mpIdProd
        );
    }

    /**
     * Update pack product stock type by using MP Product ID
     *
     * @param int $mpIdProd MP Product ID
     * @param int $stockType Pack Product Stock Type value
     * @return bool
     */
    public function updateStockTypeMpPack($mpIdProd, $stockType)
    {
        return Db::getInstance()->update(
            'wk_mp_seller_product',
            array('pack_stock_type' => $stockType),
            'id_mp_product = '.(int) $mpIdProd
        );
    }

    /**
     * Get Mp product attribute id by using PS Product ID and ID Attribute
     *
     * @param int $idPsProdAttr PS Product Attribute ID
     * @param int $idProduct PS Product ID
     * @return bool
     */
    public function getMpProductAttrID($idPsProdAttr, $idProduct)
    {
        if (!$idPsProdAttr) {
            $idPsProdAttr = (int) $idPsProdAttr ? (int) $idPsProdAttr : Product::getDefaultAttribute((int) $idProduct);
        }
        $mpIdProdAttr = $idPsProdAttr;
        return $mpIdProdAttr;
    }

    /**
     * Checks if the current entry is duplicate or not.
     *
     * @param array $params pack_product_id, mp_product_id, mp_product_id_attribute
     * @return bool/array
     */
    public function checkIfDuplicateEntry($params)
    {
        return Db::getInstance()->getValue('SELECT `id_product_pack`
        FROM `'._DB_PREFIX_.'pack` WHERE `id_product_pack` = '.(int) $params['pack_product_id'].'
        AND `id_product_item` = '.(int) $params['mp_product_id'].'
        AND `id_product_attribute_item` = '.(int) $params['mp_product_id_attribute']);
    }

    /**
     * Add Pack Product into PS
     *
     * @param int mp_product_id
     * @param int ps_product_id
     * @param array packedproduct
     */
    public function addToPsPack($mpprodid, $psproductid, $packedproduct)
    {
        $i = 0;
        $pspackproducts = array();
        foreach ($packedproduct as $packprod) {
            $mpSellerProduct = new WkMpSellerProduct($packprod['mp_product_id']);
            $psIdProd = $mpSellerProduct->id_ps_product;
            if ($psIdProd) {
                $psProdAttr = 0;
                if ($packprod['mp_product_id_attribute']) {
                    $psProdAttr = $packprod['mp_product_id_attribute'];
                }
                $pspackproducts[$i] = array(
                    'id' => $psIdProd,
                    'qty' => $packprod['quantity'],
                    'ps_product_id_attribute' => $psProdAttr,
                );
                ++$i;
            }
        }
        foreach ($pspackproducts as $pspackprod) {
            $params = array(
                'for' => 'ps',
                'pack_product_id' => $psproductid,
                'mp_product_id' => $pspackprod['id'],
                'mp_product_id_attribute' => $pspackprod['ps_product_id_attribute']
            );
            $isDuplicate = $this->checkIfDuplicateEntry($params);
            if (!$isDuplicate) {
                Pack::addItem(
                    $psproductid,
                    $pspackprod['id'],
                    $pspackprod['qty'],
                    $pspackprod['ps_product_id_attribute']
                );
            }
        }
        $stockType = $this->getPackedProductStockType($mpprodid);
        if (!$stockType) {
            $stockType = 3; //for default
        }
        Product::setPackStockType($psproductid, $stockType);
    }

    /**
     * Get Packed Product Stock Type
     *
     * @param int newproductid
     * @return int value
     */
    public function getPackedProductStockType($newproductid)
    {
        if ($newproductid) {
            return Db::getInstance()->getValue('SELECT `pack_stock_type`
            FROM `'._DB_PREFIX_.'wk_mp_seller_product` WHERE `id_mp_product` = '.(int) $newproductid);
        }
        return false;
    }

    /**
     * Get Packed Product Item Details
     *
     * @param array packedProds pack product item list
     * @return array
     */
    public function customizedAllPactProducsArray($packedProds, $idPsPackProduct = false)
    {
        if ($packedProds) {
            $objMpPack = new WkMpPackProduct();
            foreach ($packedProds as $key => $value) {
                $idPsProductItem = $value->id;
                if ($idPsProductItem) {
                    $packedProds[$key]->link_rewrite = $value->link_rewrite;
                    $idPsProductAttributeItem = $value->id_pack_product_attribute;
                    $packedProds[$key]->image_link = $this->getProductImageIdInPack(
                        $idPsProductItem,
                        $idPsProductAttributeItem
                    );
                    $packedProds[$key]->id_ps_product = $idPsProductItem;
                    $packedProds[$key]->ps_prod_attr_id = $idPsProductAttributeItem;
                    $packedProds[$key]->product_ref = $value->reference;

                    if ($idPsPackProduct) {
                        $packedProds[$key]->quantity = Db::getInstance()->getValue(
                            'SELECT `quantity` FROM `'._DB_PREFIX_.'pack`
                            WHERE `id_product_pack` = ' . (int) $idPsPackProduct.'
                            AND `id_product_item` = ' . (int) $idPsProductItem.'
                            AND `id_product_attribute_item` = ' . (int) $idPsProductAttributeItem
                        );
                    }
                }
                if (isset($idPsProductAttributeItem) && $idPsProductAttributeItem
                && $objMpPack->isPsCombinationExists($idPsProductAttributeItem)) {
                    $packedProds[$key]->product_name = Product::getProductName(
                        $idPsProductItem,
                        $idPsProductAttributeItem
                    );
                } else {
                    $packedProds[$key]->product_name = $value->name;
                }
            }

            return $packedProds;
        }

        return array();
    }

    /**
     * Get Packed Product Image
     *
     * @param int idProduct PS product id
     * @param int idProductAttribute PS product id attribute
     * @return array
     */
    public function getProductImageIdInPack($idProduct, $idProductAttribute)
    {
        $idImage = 0;
        $objProduct = new Product($idProduct, true, Context::getContext()->language->id);
        if ($idProductAttribute) {
            $idImageArr = Product::getCombinationImageById(
                $idProductAttribute,
                (int) Context::getContext()->cookie->id_lang
            );
            if (is_array($idImageArr)) {
                $idImage = $idImageArr['id_image'];
                if (!$idImage) {
                    $cover = Product::getCover($idProduct);
                    $idImage = $cover['id_image'];
                }
            }
        } else {
            $cover = Product::getCover($idProduct);
            if (is_array($cover)) {
                $idImage = $cover['id_image'];
            }
        }
        $objImage = new Image($idImage);
        if (Validate::isLoadedObject($objImage)) {
            $productImg = Context::getContext()->link->getImageLink(
                $objProduct->link_rewrite,
                $idProduct.'-'.$idImage,
                ImageType::getFormattedName('home')
            );
        } else {
            $productImg = _MODULE_DIR_.'/marketplace/views/img/home-default.jpg';
        }
        return $productImg;
    }

    /**
     * Check combination exists for product
     *
     * @param int psProdAttrId PS product id attribute
     * @return array
     */
    public function isPsCombinationExists($psProdAttrId)
    {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'product_attribute`
        WHERE `id_product_attribute` = '.(int) $psProdAttrId);
    }

    /**
     * Check combination exists for product
     *
     * @param int idSeller
     * @param int currentLangId
     * @param string query
     * @param bool excludePacks
     * @param string excludeVirtuals
     * @param int idPsCurrent
     * @return array
     */
    public function getSellerProductDetails(
        $idSeller,
        $currentLangId,
        $query,
        $excludePacks,
        $excludeVirtuals,
        $idPsCurrent
    ) {
        $excludeIds = '';
        $sql = 'SELECT p.`id_product`, msp.`id_seller`, pl.`link_rewrite`, p.`reference`, pl.`name`,
        image_shop.`id_image` id_image, p.`cache_default_attribute` FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
        LEFT JOIN `'._DB_PREFIX_.'product` p ON (msp.`id_ps_product` = p.`id_product`
        AND msp.`id_seller`='.(int)$idSeller.') '.Shop::addSqlAssociation('product', 'p').'
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product AND
        pl.id_lang = '.(int)$currentLangId.Shop::addSqlRestrictionOnLang('pl').')
        LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop ON (image_shop.`id_product` = p.`id_product`
        AND image_shop.cover=1 AND image_shop.id_shop='.(int)Context::getContext()->shop->id.')
        WHERE (msp.`id_seller`='.(int)$idSeller.' AND pl.`name` LIKE \'%'.pSQL($query).'%\'
        OR p.reference LIKE \'%'.pSQL($query).'%\')'.(!empty($excludeIds) ? '
        AND p.`id_product` NOT IN ('.$excludeIds.') ' : ' ').($excludeVirtuals ? '
        AND NOT EXISTS (SELECT 1 FROM `'._DB_PREFIX_.'product_download` pd
        WHERE (pd.id_product = p.`id_product`))' : '').
        ($excludePacks ? 'AND (p.`cache_is_pack` IS NULL OR p.`cache_is_pack` = 0)' : '').
        ($idPsCurrent ? ' AND (p.`id_product` != '.(int)$idPsCurrent.')' : '').'
        AND msp.`id_seller`='.(int)$idSeller.' GROUP BY p.id_product';
        return  Db::getInstance()->executeS($sql);
    }

    /**
     * Check combination exists for product
     *
     * @param int idProduct
     * @param int currentLangId
     * @return array
     */
    public function getSellerProductCombinationDetails($idProduct, $currentLangId)
    {
        $sql = 'SELECT pa.`id_product_attribute`, pa.`reference`, ag.`id_attribute_group`, pai.`id_image`,
        agl.`name` AS group_name, al.`name` AS attribute_name, a.`id_attribute`
        FROM `'._DB_PREFIX_.'product_attribute` pa '.Shop::addSqlAssociation('product_attribute', 'pa').'
        LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac
        ON pac.`id_product_attribute` = pa.`id_product_attribute`
        LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
        LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
        LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute`
        AND al.`id_lang` = '.(int)$currentLangId.')
        LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group`
        AND agl.`id_lang` = '.(int)$currentLangId.')
        LEFT JOIN `'._DB_PREFIX_.'product_attribute_image` pai ON pai.`id_product_attribute` = pa.`id_product_attribute`
        WHERE pa.`id_product` = '.(int)$idProduct. '
        GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
        ORDER BY pa.`id_product_attribute`';

        return Db::getInstance()->executeS($sql);
    }
}
