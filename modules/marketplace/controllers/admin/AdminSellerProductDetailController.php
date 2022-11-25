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

class AdminSellerProductDetailController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'wk_mp_seller_product';
        $this->className = 'WkMpSellerProduct';
        $this->allow_export = true;

        $this->_select = '
        CONCAT(mpsi.`seller_firstname`, " ", mpsi.`seller_lastname`) as seller_name,
        a.`id_mp_product` as `seller_product_id`,
        mpsi.`shop_name_unique`,
        p.`active`,
        pl.`id_lang`,
        pl.`name`,
        a.`id_ps_product` as temp_ps_id';

        $this->_join .= 'JOIN `'._DB_PREFIX_.'product` p
        ON (p.`id_product` = a.`id_ps_product`)'.Shop::addSqlAssociation('product', 'p');
        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
            //In case of All Shops
            $this->_select .= ',shp.`name` as wk_ps_shop_name';
            $this->_join .= 'JOIN `'._DB_PREFIX_.'shop` shp ON (shp.`id_shop` = product_shop.`id_shop`)';
        }
        $this->_join .= 'JOIN `'._DB_PREFIX_.'product_lang` pl
        ON (p.`id_product` = pl.`id_product`'.Shop::addSqlRestrictionOnLang('pl').')';
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller` mpsi ON (mpsi.`id_seller` = a.`id_seller`)';
        $this->_where = WkMpSeller::addSqlRestriction('mpsi');

        //if filter only seller products by seller view page
        if ($idSeller = Tools::getValue('id_seller')) {
            $this->_where .= ' AND a.`id_seller` = '.(int) $idSeller;
        }

        $this->_group = 'GROUP BY pl.`id_product`';
        $this->identifier = 'id_mp_product';

        parent::__construct();

        $this->fields_list = array();
        $this->fields_list = array(
            'id_mp_product' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'id_ps_product' => array(
                'title' => $this->l('Prestashop product ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'hint' => $this->l('Generated prestashop ID in catalog'),
                'callback' => 'prestashopDisplayId',
            ),
            'seller_product_id' => array(
                'title' => $this->l('Image'),
                'callback' => 'displayProductImage',
                'search' => false,
                'havingFilter' => true,
            ),
            'name' => array(
                'title' => $this->l('Product name'),
                'havingFilter' => true,
            ),
            'seller_name' => array(
                'title' => $this->l('Seller name'),
                'havingFilter' => true,
            ),
            'shop_name_unique' => array(
                'title' => $this->l('Unique shop name'),
                'havingFilter' => true,
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'filter_key' => 'p!active'
            ),
            'temp_ps_id' => array(
                'title' => $this->l('Preview'),
                'align' => 'center',
                'search' => false,
                'remove_onclick' => true,
                'hint' => $this->l('Preview active products only'),
                'callback' => 'previewProduct',
                'orderby' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Add date'),
                'type' => 'date',
                'havingFilter' => true,
            ),
        );

        $hookColumn = Hook::exec('addColumnSellerProductList');

        $i = 0;
        if ($hookColumn) {
            $column = explode('-', $hookColumn);
            $numColums = count($column);
            for ($i = 0; $i < $numColums; $i = $i + 2) {
                $this->fields_list[$column[$i]] = array(
                    'title' => $this->l($column[$i + 1]),
                    'align' => 'center',
                );
            }
        }

        if (WkMpHelper::isMultiShopEnabled()) {
            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
                //In case of All Shops
                $this->fields_list['wk_ps_shop_name'] = array(
                    'title' => $this->l('Shop'),
                    'havingFilter' => true,
                    'orderby' => false,
                );
            }
        }

        $this->bulk_actions = array(
            'duplicate' => array(
                'text' => $this->l('Duplicate selected'),
                'icon' => 'icon-copy',
                'confirm' => $this->l('Duplicate selected items?'),
            ),
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            )
        );

        if ($wkErrorCode = Tools::getValue('wk_error_code')) {
            if ($wkErrorCode == 1) {
                $this->errors[] = $this->l('There is some error to map marketplace product.');
            } elseif ($wkErrorCode == 2) {
                $this->errors[] = $this->l('Can not able to create product in prestashop catalog.');
            }
        }
    }

    public function prestashopDisplayId($idPsProduct)
    {
        if ($idPsProduct) {
            return $idPsProduct;
        } else {
            return '-';
        }
    }

    public function displayProductImage($idMpProduct, $rowData)
    {
        $imageLink = ''._MODULE_DIR_.'marketplace/views/img/home-default.jpg';
        if ($rowData['id_ps_product']) {
            $idPsProduct = $rowData['id_ps_product'];
            $objProduct = new Product($idPsProduct, false, $this->context->language->id);
            if ($coverImageId = WkMpSellerProductImage::getProductCoverImage($idMpProduct, $idPsProduct)) {
                $imageLink = $this->context->link->getImageLink(
                    $objProduct->link_rewrite,
                    $idPsProduct.'-'.$coverImageId,
                    'cart_default'
                );
            }
        }
        return '<img class="img-thumbnail" width="45" height="45" src="'.$imageLink.'">';
    }

    public function displayDuplicateLink($token, $id, $name = null)
    {
        $adminSellerProductLink = $this->context->link->getAdminlink('AdminSellerProductDetail')
        .'&id_mp_product='.(int) $id.'&wkduplicate'.$this->table;
        return '<li><a href="'.$adminSellerProductLink.'"><i class="icon-copy"></i> '.
        $this->l('Duplicate').'</a></li>';
    }

    public function previewProduct($id, $rowData)
    {
        if ($id && $rowData['active']) {
            $productLink = $this->context->link->getProductLink(
                (int) $id,
                null,
                null,
                null,
                (int) $this->context->language->id,
                (int) $rowData['id_mp_shop_default']
            );

            return '<span class="btn-group-action"><span class="btn-group">
                        <a target="_blank" class="btn btn-default" href="'.$productLink.'">
                        <i class="icon-eye"></i>&nbsp;'.$this->l('Preview').'</a>
                    </span>
                </span>';
        }
    }

    public function initToolbar()
    {
        if (WkMpSeller::getAllSeller()) {
            parent::initToolbar();
            $this->page_header_toolbar_btn['new'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new product'),
            );
            $this->page_header_toolbar_btn['assignproducts'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token.'&assignmpproduct=1',
                'desc' => $this->l('Assign product to seller'),
                'imgclass' => 'new',
            );
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function postProcess()
    {
        if (!$this->loadObject(true)) {
            return;
        }

        Media::addJsDef(array(
            'back_end' => 1,
            'image_drag_drop' => 1,
            'seller_product_page' => 1,
            'is_need_reason' => Configuration::get('WK_MP_SELLER_PRODUCTS_DEACTIVATE_REASON'),
            'path_addfeature' => $this->context->link->getAdminlink('AdminSellerProductDetail'),
            'generate_combination_confirm_msg' => $this->l('You will lose all unsaved modifications. Are you sure that you want to proceed?'),
        ));

        $this->addjQueryPlugin('growl', null, false);
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/sellerprofile.js');
        if (isset($this->display)) {
            $this->addJqueryPlugin(array('fancybox', 'tablednd'));

            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/mp_global_style.css');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/mp_form_validation.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/change_multilang.js');

            //tinymce
            $this->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
            if (version_compare(_PS_VERSION_, '1.6.0.11', '>')) {
                $this->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
            } else {
                $this->addJS(_PS_JS_DIR_.'tinymce.inc.js');
            }

            //Category tree
            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/js/categorytree/themes/default/style.min.css');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/categorytree/jstree.min.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/categorytree/wk_jstree.js');
        }

        // send reason for deactivating product
        if ($idProductForReason = Tools::getValue('actionId_for_reason')) {
            $msg = trim(Tools::getValue('reason_text'));
            if (!$msg) {
                $msg = $this->l('Admin has deactivated your product.');
            }
            $this->activeSellerProduct($idProductForReason, $msg);
            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token.'&conf=5');
        }

        if (Tools::isSubmit('status'.$this->table)) {
            $this->activeSellerProduct();
        }

        //Duplicate seller product
        if (Tools::getIsset('wkduplicate'.$this->table)) {
            if ($duplicateMpProductId = $this->duplicateMpProduct()) {
                Tools::redirectAdmin(
                    self::$currentIndex.'&id_mp_product='.(int) $duplicateMpProductId.'&update'.$this->table
                    .'&conf=19&token='.$this->token
                );
            }
        }

        parent::postProcess();
    }

    public function processExport($text_delimiter = '"')
    {
        // clean buffer
        if (ob_get_level() && ob_get_length() > 0) {
            ob_clean();
        }
        $this->getList($this->context->language->id, null, null, 0, false);
        if (!count($this->_list)) {
            return;
        }

        header('Content-type: text/csv');
        header('Content-Type: application/force-download; charset=UTF-8');
        header('Cache-Control: no-store, no-cache');
        header('Content-disposition: attachment; filename="' . $this->table . '_' . date('Y-m-d_His') . '.csv"');

        $fd = fopen('php://output', 'wb');
        $headers = [];
        foreach ($this->fields_list as $key => $datas) {
            if ('PDF' === $datas['title']) {
                unset($this->fields_list[$key]);
            } else {
                if ('ID' === $datas['title']) {
                    $headers[] = strtolower(Tools::htmlentitiesDecodeUTF8($datas['title']));
                } elseif ('Preview' != $datas['title']) {
                    $headers[] = Tools::htmlentitiesDecodeUTF8($datas['title']);
                }
            }
        }
        fputcsv($fd, $headers, ';', $text_delimiter);

        foreach ($this->_list as $i => $row) {
            $content = [];
            $path_to_image = false;
            foreach ($this->fields_list as $key => $params) {
                if ('temp_ps_id' != $key) {
                    $field_value = isset($row[$key]) ? Tools::htmlentitiesDecodeUTF8(Tools::nl2br($row[$key])) : '';
                    if ($key == 'image') {
                        if ($params['image'] != 'p' || Configuration::get('PS_LEGACY_IMAGES')) {
                            $path_to_image = Tools::getShopDomain(true) . _PS_IMG_ . $params['image'] . '/' . $row['id_' . $this->table] . (isset($row['id_image']) ? '-' . (int) $row['id_image'] : '') . '.' . $this->imageType;
                        } else {
                            $path_to_image = Tools::getShopDomain(true) . _PS_IMG_ . $params['image'] . '/' . Image::getImgFolderStatic($row['id_image']) . (int) $row['id_image'] . '.' . $this->imageType;
                        }
                        if ($path_to_image) {
                            $field_value = $path_to_image;
                        }
                    }
                    if (isset($params['callback'])) {
                        $callback_obj = (isset($params['callback_object'])) ? $params['callback_object'] : $this->context->controller;
                        if (!preg_match('/<([a-z]+)([^<]+)*(?:>(.*)<\/\1>|\s+\/>)/ism', call_user_func_array([$callback_obj, $params['callback']], [$field_value, $row]))) {
                            $field_value = call_user_func_array([$callback_obj, $params['callback']], [$field_value, $row]);
                        }
                    }
                    $content[] = $field_value;
                }
            }
            fputcsv($fd, $content, ';', $text_delimiter);
        }
        @fclose($fd);
        exit;
    }

    public function renderForm()
    {
        $permissionData = WkMpHelper::productTabPermission();
        //tinymce setup
        $this->context->smarty->assign(array(
            'path_css' => _THEME_CSS_DIR_,
            'ad' => __PS_BASE_URI__.basename(_PS_ADMIN_DIR_),
            'autoload_rte' => true,
            'lang' => true,
            'iso' => $this->context->language->iso_code,
            'permissionData' => $permissionData,
        ));

        if (Tools::getValue('assignmpproduct')) {
            $mpSellers = WkMpSeller::getAllSeller();
            if ($mpSellers) {
                if (WkMpHelper::isMultiShopEnabled()) {
                    if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
                        $this->context->smarty->assign('all_shop', 0);
                    } else {
                        foreach ($mpSellers as &$customer) {
                            $objShop = new Shop($customer['id_shop']);
                            $customer['ps_shop_name'] = $objShop->name;
                        }
                        $this->context->smarty->assign('all_shop', 1);
                    }
                }
                $psProducts = WkMpSellerProduct::getPsProductsForAssigned($this->context->language->id);
                if ($psProducts) {
                    $this->context->smarty->assign('ps_products', $psProducts);
                }
                $this->context->smarty->assign('mp_sellers', $mpSellers);
            }
            $this->context->smarty->assign('assignmpproduct', 1);
        }

        // Admin Shipping
        $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, ALL_CARRIERS);
        $carriersChoices = array();
        if ($carriers) {
            foreach ($carriers as $carrier) {
                $carriersChoices[$carrier['id_reference'].' - '.$carrier['name'].' ('.$carrier['delay'].')']
                = $carrier['id_reference'];
            }
        }

        $mpIdSeller = 0;
        if ($this->display == 'add') {
            $customerInfo = WkMpSeller::getAllSeller();
            if ($customerInfo) {
                if (WkMpHelper::isMultiShopEnabled()) {
                    if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
                        $this->context->smarty->assign('all_shop', 0);
                    } else {
                        foreach ($customerInfo as &$customer) {
                            $objShop = new Shop($customer['id_shop']);
                            $customer['ps_shop_name'] = $objShop->name;
                        }
                        $this->context->smarty->assign('all_shop', 1);
                    }
                }
                $this->context->smarty->assign('customer_info', $customerInfo);

                //get first seller from the list
                $firstSellerDetails = $customerInfo[0];
                $mpIdSeller = $firstSellerDetails['id_seller'];
            }

            // Display add specific rule
            $obMpSpecificPrice = new WkMpSpecificRule();
            $obMpSpecificPrice->assignAddMPSpecificRulesVars();
        } elseif ($this->display == 'edit') {
            $mpIdProduct = Tools::getValue('id_mp_product');

            $mpSellerProduct = new WkMpSellerProduct($mpIdProduct);
            $idPsProduct = $mpSellerProduct->id_ps_product;

            $objProduct = new Product($idPsProduct);
            $mpProduct = WkMpSellerProduct::getSellerProductWithLang($mpIdProduct);
            if ($mpProduct) {
                $mpProduct['id'] = $mpIdProduct;
                $mpIdSeller = $mpProduct['id_seller'];

                //Assign and display product active/inactive images
                WkMpSellerProductImage::getProductImageDetails($mpIdProduct);

                // Category tree
                $defaultIdCategory = $mpProduct['id_category_default'];

                $productCategoryIds = Product::getProductCategories($idPsProduct);
                if ($productCategoryIds) {
                    $catIdsJoin = implode(',', $productCategoryIds);
                    $this->context->smarty->assign('catIdsJoin', $catIdsJoin);
                } else {
                    $productCategoryIds = array();
                }

                $defaultCategory = Category::getCategoryInformations(
                    $productCategoryIds,
                    $this->context->language->id
                );

                // Product carriers
                $selectedCarriers = array();
                $productCarriers = $objProduct->getCarriers();
                if ($productCarriers) {
                    foreach ($productCarriers as $carrier) {
                        $selectedCarriers[] = $carrier['id_reference'];
                    }
                }

                //Display Product Combination list
                WkMpProductAttribute::displayProductCombinationList($mpIdProduct);

                // checking current product has attribute or not
                $hasAttribute = $objProduct->hasAttributes();
                if ($hasAttribute) {
                    $this->context->smarty->assign('hasAttribute', 1);
                }

                // Get Seller Product Features and Assign on Smarty
                WkMpProductFeature::assignProductFeatureOnTpl($idPsProduct);
                $idMpProduct = $mpIdProduct;
                if (Pack::isPack($idPsProduct)) {
                    $objMpPack = new WkMpPackProduct();
                    $mpPackProducts = Pack::getItems($idPsProduct, Context::getContext()->language->id);
                    if (!$mpPackProducts) {
                        $objMpPack->isPackProductFieldUpdate($idMpProduct, 0);
                    }

                    $objMpSeller = new WkMpSellerProduct($idMpProduct);
                    //Assign current lang according to multilanguage functionality
                    WkMpHelper::assignDefaultLang($objMpSeller->id_seller);
                    $isPackProduct = $objMpPack->isPackProduct($idMpProduct);
                    if ($isPackProduct) {
                        if ($mpPackProducts) {
                            $mpPackProducts = $objMpPack->customizedAllPactProducsArray($mpPackProducts, $idPsProduct);
                        }

                        $this->context->smarty->assign([
                            'isPackProduct' => $isPackProduct,
                            'mpPackProducts' => $mpPackProducts,
                            'pack_stock_type' => Configuration::get('PS_PACK_STOCK_TYPE'),
                            'product_stock_type' => $objMpPack->getPackedProductStockType($idMpProduct)
                        ]);
                    }
                } elseif ($objProduct->is_virtual) {
                    $objMpVirtualProduct = new WkMpVirtualProduct();
                    $isVirtualProduct = $objMpVirtualProduct->isMpProductIsVirtualProduct($idMpProduct);
                    $attachFileNameExist = $isVirtualProduct['display_filename'];
                    if ($attachFileNameExist) {
                        $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct);
                        if ($mpProductDetail['id_ps_product']) {
                            $psProductId = $mpProductDetail['id_ps_product'];
                            $objProductDownload = new ProductDownload();
                            $fileKey = ($objProductDownload->getFilenameFromIdProduct($psProductId));
                            $file = _PS_DOWNLOAD_DIR_.strval(preg_replace('/\.{2,}/', '.', $fileKey));
                            if (file_exists($file) && $fileKey) {
                                $this->context->smarty->assign('showTab', 1);
                            }
                        } else {
                            $fileName = $isVirtualProduct['display_filename'];
                            $filePath = _PS_MODULE_DIR_.$this->module->name.'/views/upload/'.$fileName;
                            if (file_exists($filePath) && $fileName) {
                                $this->context->smarty->assign('showTab', 1);
                            }
                        }
                        $this->context->smarty->assign('attach_file_exist', $attachFileNameExist);
                    }
                    if ($isVirtualProduct) {
                        if ($isVirtualProduct['date_expiration'] == '0000-00-00') {
                            $isVirtualProduct['date_expiration'] = '';
                        }
                        $this->context->smarty->assign('is_virtual_prod', $isVirtualProduct);
                    }
                }

                $this->context->smarty->assign(array(
                    'selectedCarriers' => $selectedCarriers,
                    'product_info' => $mpProduct,
                    'id_tax_rules_group' => $mpProduct['id_tax_rules_group'],
                    'defaultCategory' => $defaultCategory,
                    'defaultIdCategory' => $defaultIdCategory,
                    'edit' => 1,
                    'id' => $mpIdProduct,
                ));
            } else {
                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
            }

            // Display Added Specific Rules
            $obMpSpecificPrice = new WkMpSpecificRule();
            $obMpSpecificPrice->getMPSpecificRules($mpIdProduct);

            // Display Related Products
            $relatedProducts = WkMpSellerProduct::getRelatedProducts($mpIdProduct);
            $this->context->smarty->assign('relatedProducts', $relatedProducts);

            // Display tags for products
            $productTags = Tag::getProductTags($idPsProduct);
            if ($productTags) {
                $productTag = array();
                foreach ($productTags as $tag_key => $tagVal) {
                    $productTag[$tag_key] = implode(',', $tagVal);
                }
                if ($productTag) {
                    $this->context->smarty->assign('productTag', $productTag);
                }
            }
        }

        // Display brands for products
        $idCustomer = $idManufacturer = 0;
        if (isset($mpProduct['id_seller'])) {
            $seller = WkMpSeller::getSellerWithLangBySellerId($mpProduct['id_seller']);
            $idCustomer = $seller['seller_customer_id'];
        }
        if (isset($objProduct)) {
            $idManufacturer = $objProduct->id_manufacturer;
        }
        Media::addJsDef(array(
            'add_manufacturer_admin' => $this->context->link->getAdminLink('AdminManufacturerDetail'),
            'no_manufacuturer' => $this->l('No brand found'),
            'choose_optional' => $this->l('Choose(Optional)'),
            'id_customer' => $idCustomer,
            'selected_id_manuf' => $idManufacturer,
        ));

        //manufacturer will load by js
        $this->context->smarty->assign('manufacturers', array());
        $this->context->controller->addJS(_MODULE_DIR_.'marketplace/views/js/manufacturers/findsellermanufacturers.js');

        // Display brands for products
        $this->context->smarty->assign('front', 0);
        if (isset($objProduct)) {
            $objMpProductSupplier = new WkMpSuppliers();
            $ps_suppliers = $objMpProductSupplier->getInfoByMpProductId($idMpProduct);
            if ($ps_suppliers) {
                $selected_suppliers = array();
                foreach ($ps_suppliers as $supplier) {
                    $selected_suppliers[$supplier['id_supplier']][] = $supplier;
                }
                $this->context->smarty->assign('selected_suppliers_list', $ps_suppliers);
                $this->context->smarty->assign('selected_suppliers_data', $selected_suppliers);
            }
            if (isset($objProduct) && $objProduct->id_supplier) {
                $this->context->smarty->assign('selected_id_supplier', $objProduct->id_supplier);
            } else {
                $this->context->smarty->assign('selected_id_supplier', 0);
            }
            $objMpSupplier = new WkMpSuppliers();
            $suppliers = $objMpSupplier->getSuppliersForProductBySellerId($mpProduct['id_seller']);
            if ($suppliers) {
                $this->context->smarty->assign('suppliers', $suppliers);
            }
            $currencies = Currency::getCurrencies(false, true, true);
            $this->context->smarty->assign(array(
                'modules_dir' => _MODULE_DIR_,
                'currencies' => $currencies,
            ));
        }

        // Display customization for products
        if (isset($idMpProduct)) {
            $objProductCustomization = new WkMpSellerProduct();
            $customizationFields = $objProductCustomization->getLangFieldValue($idMpProduct);
            $this->context->smarty->assign('customizationFields', $customizationFields);
        }
        Media::addJsDef(array(
            'languages' => Language::getLanguages(),
            'fieldlabel' => $this->l('Field label'),
            'wk_ctype' => $this->l('Type'),
            'wk_crequired' => $this->l('Required'),
            'custimzationtext' => $this->l('Text'),
            'custimzationfile' => $this->l('File'),
        ));

        // Display Page Redirection Category or Product
        if (isset($objProduct)) {
            Media::addJsDef(array(
                'wk_rtype' => $objProduct->redirect_type,
                'wk_rtypeId' => $objProduct->id_type_redirected,
                'SomethingWentWrong' => $this->l('Something went wrong.')
            ));
        } else {
            Media::addJsDef(array(
                'wk_rtype' => '404',
                'wk_rtypeId' => 0,
                'SomethingWentWrong' => $this->l('Something went wrong.')
            ));
        }

        // Display attachments for products
        if ($defaultLang = WkMPSeller::getSellerDefaultLanguage($mpIdSeller)) {
            $productAttachments = WkMpSellerProduct::getProductAttachments($mpIdSeller, $defaultLang);
            if ($productAttachments) {
                if (isset($idPsProduct)) {
                    $associatedProduct = Attachment::getAttachments($defaultLang, $idPsProduct);
                    foreach ($productAttachments as &$productAttachment) {
                        $productAttachment['selected'] = false;
                        foreach ($associatedProduct as $assocProduct) {
                            if ($assocProduct['id_attachment'] == $productAttachment['id_attachment']) {
                                $productAttachment['selected'] = true;
                                break;
                            }
                        }
                    }
                }
                $this->context->smarty->assign('productAttachments', $productAttachments);
            }
        }

        // Set default lang at every form according to configuration multi-language
        WkMpHelper::assignDefaultLang($mpIdSeller);

        //show tax rule group on add product page
        $taxRuleGroups = TaxRulesGroup::getTaxRulesGroups(true);
        if ($taxRuleGroups) {
            $this->context->smarty->assign('tax_rules_groups', $taxRuleGroups);
        }

        $this->context->smarty->assign('mp_seller_applied_tax_rule', 1);

        WkMpHelper::defineGlobalJSVariables(); // Define global js variable on js file

        $objProduct = new Product();
        $objDefaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $this->context->smarty->assign(array(
                'modules_dir' => _MODULE_DIR_,
                'mp_image_dir' => _MODULE_DIR_.'marketplace/views/img/',
                'img_ps_dir' => _MODULE_DIR_.$this->module->name.'/views/img/',
                'wkself' => dirname(__FILE__),
                'active_tab' => Tools::getValue('tab'),
                'defaultCurrencySign' => $objDefaultCurrency->sign,
                'img_module_dir' => _MODULE_DIR_.$this->module->name.'/views/img/',
                'carriersChoices' => $carriersChoices,
                'backendController' => 1,
                'available_features' => Feature::getFeatures(
                    $this->context->language->id,
                    (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)
                ),
                'controller' => 'admin',
                'ps_img_dir' => _PS_IMG_.'l/',
                'defaultTaxRuleGroup' => $objProduct->getIdTaxRulesGroup(),
            ));

        $this->fields_form = array(
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        if ((Shop::getContext() !== Shop::CONTEXT_SHOP) && (Shop::getContext() !== Shop::CONTEXT_ALL)) {
            //For shop group
            $this->errors[] = $this->l('You can not add or edit a payment mode in this shop context: select a shop instead of a group of shops.');
        } else {
            return parent::renderForm();
        }
    }

    public function processSave()
    {
        if (Tools::getValue('assignmpproduct')) { //Process of assigning products
            $idCustomer = Tools::getValue('id_customer');
            if ($idCustomer) {
                $assignedProducts = Tools::getValue('id_product');
                if (!$assignedProducts) {
                    $this->errors[] = $this->l('Choose atleast one product.');
                }

                if (empty($this->errors)) {
                    $objSellerProduct = new WkMpSellerProduct();
                    if ($assignedProducts) {
                        foreach ($assignedProducts as $idProduct) {
                            Hook::exec(
                                'actionBeforeAssignMpProduct',
                                array('id_product' => $idProduct, 'id_customer' => $idCustomer)
                            );
                            if (empty($this->errors)) {
                                $idMpProduct = $objSellerProduct->assignProductToSeller($idProduct, $idCustomer);
                                if ($idMpProduct) {
                                    Hook::exec(
                                        'actionAfterAssignMpProduct',
                                        array('id_mp_product' => $idMpProduct)
                                    );
                                    WkMpSellerProduct::sendMail($idMpProduct, 3, 'assignment', 'assignment');
                                }
                            }
                        }
                    }

                    if (empty($this->errors)) {
                        if (Tools::isSubmit('submitAddwk_mp_seller_productAndAssignStay')) {
                            $this->redirect_after = self::$currentIndex.'&add'.$this->table.'&conf=3&token='.
                            $this->token.'&assignmpproduct=1';
                        } elseif (Tools::isSubmit('submitAddwk_mp_seller_product')) {
                            Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                        }
                    }
                } else {
                    if (Tools::isSubmit('submitAdd'.$this->table.'AndAssignStay')) {
                        $this->display = 'edit';
                    }
                }
            } else {
                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
            }
        } else {
            $productQuantity = Tools::getValue('quantity');
            $minimalQuantity = Tools::getValue('minimal_quantity');
            $productShowCondition = Tools::getValue('show_condition');
            if (!$productShowCondition) {
                $productShowCondition = 0;
            }
            $productCondition = Tools::getValue('condition');

            $productPrice = Tools::getValue('price');
            $wholesalePrice = Tools::getValue('wholesale_price');
            $unitPrice = Tools::getValue('unit_price');
            $unity = Tools::getValue('unity');
            $idTaxRulesGroup = Tools::getValue('id_tax_rules_group');

            // height, width, depth and weight
            $width = Tools::getValue('width');
            $width = empty($width) ? '0' : str_replace(',', '.', $width);

            $height = Tools::getValue('height');
            $height = empty($height) ? '0' : str_replace(',', '.', $height);

            $depth = Tools::getValue('depth');
            $depth = empty($depth) ? '0' : str_replace(',', '.', $depth);

            $weight = Tools::getValue('weight');
            $weight = empty($weight) ? '0' : str_replace(',', '.', $weight);

            $reference = Tools::getValue('reference');
            $ean13JanBarcode = Tools::getValue('ean13');
            $upcBarcode = Tools::getValue('upc');
            $isbn = Tools::getValue('isbn');
            $mpn = Tools::getValue('mpn');

            // Admin Shipping
            $psIDCarrierReference = Tools::getValue('ps_id_carrier_reference');
            if (!$psIDCarrierReference) {
                $psIDCarrierReference = 0;  // No Shipping Selected
            }

            $defaultCategory = Tools::getValue('default_category');
            $categories = Tools::getValue('product_category');
            $categories = explode(',', $categories);

            $idMpProduct = Tools::getValue('id'); //if edit

            $sellerDefaultLanguage = Tools::getValue('seller_default_lang');
            $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

            //Product Visibility
            $availableForOrder = trim(Tools::getValue('available_for_order'));
            $showPrice = $availableForOrder ? 1 : trim(Tools::getValue('show_price'));
            $onlineOnly = trim(Tools::getValue('online_only'));
            $visibility = trim(Tools::getValue('visibility'));

            //Product Name Validate
            if (!Tools::getValue('product_name_'.$defaultLang)) {
                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                    $sellerLang = Language::getLanguage((int) $defaultLang);
                    $this->errors[] = sprintf($this->l('Product name is required in %s'), $sellerLang['name']);
                } else {
                    $this->errors[] = $this->l('Product name is required');
                }
            } else {
                // Validate form
                $this->errors = WkMpSellerProduct::validateMpProductForm();
            }

            if ($idMpProduct) {
                Hook::exec('actionBeforeUpdateMPProduct', array('id_mp_product' => $idMpProduct));
            } else {
                $idCustomer = Tools::getValue('shop_customer');
                $mpShopInfo = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
                $idSeller = $mpShopInfo['id_seller'];
                Hook::exec('actionBeforeAddMPProduct', array('id_seller' => $idSeller));
            }

            if (empty($this->errors)) {
                $productInfo = array();
                $productInfo['default_lang'] = $defaultLang;
                $productInfo['id_ps_shop'] = (int) $this->context->shop->id;

                if ($idMpProduct) {
                    //if update product
                    $objSellerProduct = new WkMpSellerProduct($idMpProduct);
                    $idPsProduct = (int) $objSellerProduct->id_ps_product;
                    $idMpShopDefault = (int) $objSellerProduct->id_mp_shop_default;
                    $objProduct = new Product($idPsProduct, false, null, $idMpShopDefault);
                    $hasAttribute = $objProduct->hasAttributes();
                    $productInfo['id_ps_shop'] = $idMpShopDefault;
                } else {
                    //if add new product
                    $objSellerProduct = new WkMpSellerProduct();
                    $objProduct = new Product();
                    $hasAttribute = false; //Set false in case of add product
                    $productInfo['id_seller'] = $idSeller;
                }

                //stock location
                $productInfo['location'] = Tools::getValue('location');

                //Page Redirection
                $productInfo['redirect_type'] = Tools::getValue('redirect_type');
                $productInfo['id_type_redirected'] = Tools::getValue('id_type_redirected');

                // If current product has no combination then product qty will update
                if (!$hasAttribute) {
                    $productInfo['quantity'] = $productQuantity;
                    $productInfo['minimal_quantity'] = $minimalQuantity;

                    //Low stock alert
                    $productInfo['low_stock_threshold'] = Tools::getValue('low_stock_threshold');
                    if (Tools::getValue('low_stock_alert')) {
                        $productInfo['low_stock_alert'] = 1;
                    } else {
                        $productInfo['low_stock_alert'] = 0;
                    }
                }

                $productInfo['id_category_default'] = $defaultCategory;
                $productInfo['show_condition'] = $productShowCondition;
                $productInfo['condition'] = $productCondition;

                //Pricing
                $productInfo['price'] = $productPrice;
                $productInfo['wholesale_price'] = $wholesalePrice;
                $productInfo['unit_price'] = $unitPrice; //(Total price divide by unit price)
                $productInfo['unity'] = $unity;
                $productInfo['id_tax_rules_group'] = $idTaxRulesGroup;

                if (Tools::getValue('on_sale')) {
                    $productInfo['on_sale'] = 1;
                } else {
                    $productInfo['on_sale'] = 0;
                }

                $productInfo['width'] = $width;
                $productInfo['height'] = $height;
                $productInfo['depth'] = $depth;
                $productInfo['weight'] = $weight;

                $productInfo['additional_delivery_times'] = Tools::getValue('additional_delivery_times');
                $productInfo['additional_shipping_cost'] = Tools::getValue('additional_shipping_cost');

                $productInfo['out_of_stock'] = Tools::getValue('out_of_stock');
                $productInfo['available_date'] = Tools::getValue('available_date');

                $productInfo['reference'] = $reference ? $reference : '';
                $productInfo['ean13'] = $ean13JanBarcode ? $ean13JanBarcode : '';
                $productInfo['upc'] = $upcBarcode ? $upcBarcode : '';
                $productInfo['isbn'] = $isbn ? $isbn : '';
                $productInfo['mpn'] = $mpn ? $mpn : '';

                $productInfo['ps_id_carrier_reference'] = $psIDCarrierReference;

                foreach (Language::getLanguages(false) as $language) {
                    $productIdLang = $language['id_lang'];
                    $shortDescIdLang = $language['id_lang'];
                    $descIdLang = $language['id_lang'];
                    $availableNowIdLang = $language['id_lang'];
                    $availableLaterIdLang = $language['id_lang'];
                    $metaTitleIdLang = $language['id_lang'];
                    $metaDescriptionIdLang = $language['id_lang'];
                    $deliveryInStockIdLang = $language['id_lang'];
                    $deliveryOutStockIdLang = $language['id_lang'];

                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                        //if product name in other language is not available
                        //then fill with seller language same for others
                        if (!Tools::getValue('product_name_'.$language['id_lang'])) {
                            $productIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('short_description_'.$language['id_lang'])) {
                            $shortDescIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('description_'.$language['id_lang'])) {
                            $descIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('meta_title_'.$language['id_lang'])) {
                            $metaTitleIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('meta_description_'.$language['id_lang'])) {
                            $metaDescriptionIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('available_now_'.$language['id_lang'])) {
                            $availableNowIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('available_later_'.$language['id_lang'])) {
                            $availableLaterIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('delivery_in_stock_'.$language['id_lang'])) {
                            $deliveryInStockIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('delivery_out_stock_'.$language['id_lang'])) {
                            $deliveryOutStockIdLang = $defaultLang;
                        }
                    } else {
                        //if multilang is OFF then all fields will be filled as default lang content
                        $productIdLang = $defaultLang;
                        $shortDescIdLang = $defaultLang;
                        $descIdLang = $defaultLang;
                        $availableNowIdLang = $defaultLang;
                        $availableLaterIdLang = $defaultLang;
                        $metaTitleIdLang = $defaultLang;
                        $metaDescriptionIdLang = $defaultLang;
                        $deliveryInStockIdLang = $defaultLang;
                        $deliveryOutStockIdLang = $defaultLang;
                    }

                    $productInfo['name'][$language['id_lang']] = Tools::getValue(
                        'product_name_'.$productIdLang
                    );
                    $productInfo['short_description'][$language['id_lang']] = Tools::getValue(
                        'short_description_'.$shortDescIdLang
                    );
                    $productInfo['description'][$language['id_lang']] = Tools::getValue(
                        'description_'.$descIdLang
                    );
                    //Product SEO
                    $productInfo['meta_title'][$language['id_lang']] = Tools::getValue(
                        'meta_title_'.$metaTitleIdLang
                    );
                    $productInfo['meta_description'][$language['id_lang']] = Tools::getValue(
                        'meta_description_'.$metaDescriptionIdLang
                    );
                    //Friendly URL
                    if (Tools::getValue('link_rewrite_'.$language['id_lang'])) {
                        $productInfo['link_rewrite'][$language['id_lang']] = Tools::link_rewrite(
                            Tools::getValue('link_rewrite_'.$language['id_lang'])
                        );
                    } else {
                        $productInfo['link_rewrite'][$language['id_lang']] = Tools::link_rewrite(
                            Tools::getValue('product_name_'.$productIdLang)
                        );
                    }

                    $productInfo['available_now'][$language['id_lang']] = Tools::getValue(
                        'available_now_'.$availableNowIdLang
                    );
                    $productInfo['available_later'][$language['id_lang']] = Tools::getValue(
                        'available_later_'.$availableLaterIdLang
                    );
                    $productInfo['delivery_in_stock'][$language['id_lang']] = Tools::getValue(
                        'delivery_in_stock_'.$deliveryInStockIdLang
                    );
                    $productInfo['delivery_out_stock'][$language['id_lang']] = Tools::getValue(
                        'delivery_out_stock_'.$deliveryOutStockIdLang
                    );
                }

                $productInfo['available_for_order'] = $availableForOrder;
                $productInfo['show_price'] = $showPrice;
                $productInfo['online_only'] = $onlineOnly;
                $productInfo['visibility'] = $visibility;

                if ($categories) {
                    $productInfo['category'] = $categories;
                } else {
                    $productInfo['category'] = array();
                }

                $productInfo['featureAllowed'] = 1;
                $productInfo['product_feature'] = array();
                $wkFeatureRow = Tools::getValue('wk_feature_row');
                for ($i = 1; $i <= $wkFeatureRow; $i++) {
                    $idFeature = Tools::getValue('wk_mp_feature_'.$i);
                    if ($idFeature) {
                        $productInfo['product_feature'][$i]['id_feature'] = $idFeature;
                        $productInfo['product_feature'][$i]['id_feature_value'] = Tools::getValue(
                            'wk_mp_feature_val_'.$i
                        );
                        $productInfo['product_feature'][$i]['custom_value'] = trim(
                            Tools::getValue('wk_mp_feature_custom_'.$defaultLang.'_'.$i)
                        );
                    }
                }
                $productType = Tools::getValue('product_type');
                $productInfo['cache_is_pack'] = '0';
                if ($productType == 2) {
                    $productInfo['product_type'] = 'pack';
                    $productInfo['cache_is_pack'] = '1';
                } elseif ($productType == 3) {
                    $productInfo['product_type'] = 'virtual';
                    $productInfo['is_virtual'] = 1;
                } else {
                    $productInfo['product_type'] = 'standard';
                }

                // Set manufacturers for products
                if ($psManufacturerId = Tools::getValue('product_manufacturer')) {
                    $psManufacturerId = Tools::getValue('product_manufacturer');
                    if ($psManufacturerId) {
                        $productInfo['id_manufacturer'] = $psManufacturerId;
                    }
                }

                if ($idMpProduct) {
                    $productType = Tools::getValue('product_type');
                    if ($productType == 2) {
                        $objMpVirtualProduct = new WkMpVirtualProduct();
                        $isVirtualProduct = $objMpVirtualProduct->isMpProductIsVirtualProduct($idMpProduct);
                        if ($idPsProduct) {
                            $psProductId = $idPsProduct;
                            $product = new Product($psProductId);
                            $product->is_virtual = 0;
                            $product->save();

                            $idProductDownload = ProductDownload::getIdFromIdProduct($psProductId);
                            $download = new ProductDownload($idProductDownload);

                            if (trim($download->filename)) {
                                if (file_exists(_PS_DOWNLOAD_DIR_.$download->filename)) {
                                    unlink(_PS_DOWNLOAD_DIR_.$download->filename);
                                }
                            }

                            $objMpVirtualProduct->deleteProdDownloadByIdProductDownload($idProductDownload); //row delete from product download table
                        } else {
                            if ($isVirtualProduct['reference_file']) {
                                $fileLink = _PS_MODULE_DIR_.$this->name.'/upload/'.$isVirtualProduct['reference_file'];
                                if (file_exists($fileLink)) {
                                    unlink($fileLink);
                                }
                            }
                        }

                        $pspkProducts = Tools::getValue('pspk_id_prod');
                        $pspkProdQuant = Tools::getValue('pspk_prod_quant');
                        $pspkIdProdAttr = Tools::getValue('pspk_id_prod_attr');
                        $stockType = Tools::getValue('pack_qty_mgmt');
                        $productInfo['pack_stock_type'] = $stockType;
                        $mpSellerProduct = new WkMpSellerProduct($idMpProduct);
                        $objMpPack = new WkMpPackProduct();
                        $isPackProduct = $objMpPack->isPackProduct($idMpProduct);
                        if (count($pspkProducts) == count($pspkProdQuant)) {
                            $objMpPack = new WkMpPackProduct();
                            if (!$isPackProduct) {
                                // Standard product to pack product
                                $objMpPack->isPackProductFieldUpdate($idMpProduct, 1);
                            } else {
                                // Update pack product
                                if ($idPsProduct) {
                                    Pack::deleteItems($idPsProduct);
                                }
                            }
                            $objMpPack->updateStockTypeMpPack($idMpProduct, $stockType);
                            $packProductArray = array();
                            foreach ($pspkProducts as $key => $value) {
                                $mpProdDtls = WkMpSellerProduct::getSellerProductByPsIdProduct($value);
                                if (!empty($mpProdDtls)) {
                                    $idProdAttr = $pspkIdProdAttr[$key];
                                    $mpIdProdAttr = $objMpPack->getMpProductAttrID($idProdAttr, $value);
                                    $params = array(
                                        'pack_product_id' => $idMpProduct,
                                        'mp_product_id' => $mpProdDtls['id_mp_product'],
                                        'mp_product_id_attribute' => $mpIdProdAttr,
                                        'quantity' => $pspkProdQuant[$key]
                                    );
                                    $packProductArray[] = $params;
                                }
                            }
                            if ($idPsProduct) {
                                $objMpPack->addToPsPack($idMpProduct, $idPsProduct, $packProductArray);
                            }
                        }
                    } elseif ($productType == 3) {
                        $objMpPack = new WkMpPackProduct();
                        $isPackProduct = $objMpPack->isPackProduct($idMpProduct);
                        if ($isPackProduct) {
                            //pack product to standard product
                            $objMpPack->isPackProductFieldUpdate($idMpProduct, 0);

                            if ($idPsProduct) {
                                Pack::deleteItems($idPsProduct);
                            }
                        }
                        $productInfo['product_type'] = 'virtual';
                        $productInfo['is_virtual'] = 1;
                        $mpVirtualProductName = Tools::getValue('mp_vrt_prod_name');
                        $mpVirtualProductNbDownloadable = Tools::getValue('mp_vrt_prod_nb_downloable');
                        $mpVirtualProductExpDate = Tools::getValue('mp_vrt_prod_expdate');
                        $mpVirtualProductNbDays = Tools::getValue('mp_vrt_prod_nb_days');

                        $objMpVirtualProduct = new WkMpVirtualProduct();
                        $isVirtualProduct = $objMpVirtualProduct->isMpProductIsVirtualProduct($idMpProduct);
                        if (!$isVirtualProduct) {
                            // standard to virtual product
                            if ($_FILES['mp_vrt_prod_file']['size'] > 0) {
                                $extension = pathinfo($_FILES['mp_vrt_prod_file']['name'], PATHINFO_EXTENSION);

                                $filePath = _PS_MODULE_DIR_.$this->module->name.'/views/upload/';
                                $fileName = 'virtual_'.$idMpProduct.'.'.$extension;
                                $fileLink = $filePath.$fileName;

                                if ($mpVirtualProductName == '') {
                                    $mpVirtualProductName = $_FILES['mp_vrt_prod_file']['name'];
                                }

                                if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'png') {
                                    ImageManager::resize($_FILES['mp_vrt_prod_file']['tmp_name'], $fileLink, null, null, $extension);
                                } else {
                                    move_uploaded_file($_FILES['mp_vrt_prod_file']['tmp_name'], $fileLink);
                                }
                            }

                            $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct);
                            if ($mpProductDetail['id_ps_product']) {
                                $psProductId = $mpProductDetail['id_ps_product'];

                                $product = new Product($psProductId);
                                $product->is_virtual = 1;
                                $product->available_for_order = true;
                                $product->save();
                                StockAvailable::setProductOutOfStock($product->id, 1);

                                if (!$_FILES['mp_vrt_prod_file']['size']) {
                                    $fileLink = 0;
                                }

                                $virtualProductArray = array();
                                $virtualProductArray['mp_product_id'] = $idMpProduct;
                                $virtualProductArray['display_filename'] = $mpVirtualProductName;

                                if ($_FILES['mp_vrt_prod_file']['size'] > 0) {
                                    $virtualProductArray['reference_file'] = $fileName;
                                }

                                $virtualProductArray['date_expiration'] = $mpVirtualProductExpDate;
                                $virtualProductArray['nb_days_accessible'] = $mpVirtualProductNbDays;
                                $virtualProductArray['nb_downloadable'] = $mpVirtualProductNbDownloadable;

                                $objMpVirtualProduct->updateFile($psProductId, $fileLink, WkMpVirtualProduct::ENABLE, $mpVirtualProductName, $virtualProductArray);
                            }
                        } else {
                            // update virtual product
                            if ($_FILES['mp_vrt_prod_file']['size'] > 0) {
                                $extension = pathinfo($_FILES['mp_vrt_prod_file']['name'], PATHINFO_EXTENSION);

                                $filePath = _PS_MODULE_DIR_.$this->module->name.'/views/upload/';
                                $fileName = 'virtual_'.$idMpProduct.'.'.$extension;
                                $fileLink = $filePath.$fileName;

                                if ($mpVirtualProductName == '') {
                                    $mpVirtualProductName = $_FILES['mp_vrt_prod_file']['name'];
                                }

                                $previousFile = glob($filePath.'virtual_'.$idMpProduct.'.*');
                                if (count($previousFile)) {
                                    unlink($previousFile[0]);
                                }
                                if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'png') {
                                    ImageManager::resize($_FILES['mp_vrt_prod_file']['tmp_name'], $fileLink, null, null, $extension);
                                } else {
                                    move_uploaded_file($_FILES['mp_vrt_prod_file']['tmp_name'], $fileLink);
                                }
                            }

                            $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct);

                            if ($mpProductDetail['id_ps_product']) {
                                $ps_id_prod = $mpProductDetail['id_ps_product'];

                                $product = new Product($ps_id_prod);
                                $product->is_virtual = 1;
                                $product->available_for_order = true;
                                $product->save();

                                StockAvailable::setProductOutOfStock($product->id, 1);

                                //Admin can set NO to virtual file option from catalog that's why first we do active that product file option
                                Db::getInstance()->update('product_download', array('active' => 1), 'id_product = '.(int) $ps_id_prod);

                                $idProductDownload = ProductDownload::getIdFromIdProduct($ps_id_prod);
                                $download = new ProductDownload($idProductDownload);

                                if ($_FILES['mp_vrt_prod_file']['size'] > 0) {
                                    if (trim($download->filename)) {
                                        if (file_exists(_PS_DOWNLOAD_DIR_.$download->filename)) {
                                            unlink(_PS_DOWNLOAD_DIR_.$download->filename);
                                        }
                                    }
                                }

                                if (!$_FILES['mp_vrt_prod_file']['size']) {
                                    $fileLink = 0;
                                }

                                $virtualProductArray = array();
                                $virtualProductArray['mp_product_id'] = $idMpProduct;
                                $virtualProductArray['display_filename'] = $mpVirtualProductName;

                                if ($_FILES['mp_vrt_prod_file']['size'] > 0) {
                                    $virtualProductArray['reference_file'] = $fileName;
                                }

                                $virtualProductArray['date_expiration'] = $mpVirtualProductExpDate;
                                $virtualProductArray['nb_days_accessible'] = $mpVirtualProductNbDays;
                                $virtualProductArray['nb_downloadable'] = $mpVirtualProductNbDownloadable;

                                $objMpVirtualProduct->updateFile($ps_id_prod, $fileLink, WkMpVirtualProduct::ENABLE, $mpVirtualProductName, $virtualProductArray);
                            }
                        }
                    } else {
                        $productInfo['product_type'] = 'standard';
                        $objMpPack = new WkMpPackProduct();
                        $isPackProduct = $objMpPack->isPackProduct($idMpProduct);
                        if ($isPackProduct) {
                            //pack product to standard product
                            $objMpPack->isPackProductFieldUpdate($idMpProduct, 0);

                            if ($idPsProduct) {
                                Pack::deleteItems($idPsProduct);
                            }
                        }
                        $objMpVirtualProduct = new WkMpVirtualProduct();
                        $isVirtualProduct = $objMpVirtualProduct->isMpProductIsVirtualProduct($idMpProduct);
                        if ($idPsProduct) {
                            $psProductId = $idPsProduct;
                            $product = new Product($psProductId);
                            $product->is_virtual = 0;
                            $product->save();

                            $idProductDownload = ProductDownload::getIdFromIdProduct($psProductId);
                            $download = new ProductDownload($idProductDownload);

                            if (trim($download->filename)) {
                                if (file_exists(_PS_DOWNLOAD_DIR_.$download->filename)) {
                                    unlink(_PS_DOWNLOAD_DIR_.$download->filename);
                                }
                            }

                            $objMpVirtualProduct->deleteProdDownloadByIdProductDownload($idProductDownload); //row delete from product download table
                        } else {
                            if ($isVirtualProduct['reference_file']) {
                                $fileLink = _PS_MODULE_DIR_.$this->name.'/upload/'.$isVirtualProduct['reference_file'];
                                if (file_exists($fileLink)) {
                                    unlink($fileLink);
                                }
                            }
                        }
                    }
                    if ($idMpProduct && ($specificPricePriority = Tools::getValue('specificPricePriority'))) {
                        // Set priority management
                        if ($specificPricePriority) {
                            SpecificPrice::setSpecificPriority($idPsProduct, $specificPricePriority);
                        }
                    }

                    // Set related products
                    if ($relatedProducts = Tools::getValue('related_product')) {
                        WkMpSellerProduct::addRelatedProducts($idPsProduct, $relatedProducts);
                    }
                    // Set tags for products
                    if ($idPsProduct) {
                        $tagLangData = array();
                        foreach (Language::getLanguages(true) as $language) {
                            if (Tools::getValue('tag_'.$language['id_lang'])) {
                                $tagLangData[$language['id_lang']] = explode(
                                    ',',
                                    Tools::getValue('tag_'.$language['id_lang'])
                                );
                            }
                        }
                        Tag::deleteTagsForProduct($idPsProduct);
                        foreach ($tagLangData as $langKey => $tagData) {
                            $tagPsData = array();
                            $tagData = array_unique($tagData);
                            foreach ($tagData as $tag) {
                                if (!empty(trim($tag))) {
                                    $tagPsData[] = $tag;
                                }
                            }
                            $tagPsData = implode(',', $tagPsData);
                            Tag::addTags(
                                $langKey,
                                $idPsProduct,
                                $tagPsData,
                                ','
                            );
                        }
                    }

                    // Set Supplier for products
                    if ($defaultSupplier = Tools::getValue('default_supplier')) {
                        $productSuppliers = Tools::getValue('selected_suppliers');
                        if ($idMpProduct && $idPsProduct) {
                            $objMpSupplier = new WkMpSuppliers();
                            $objMpSupplier->deleteSuppliersByPsProductId($idPsProduct);
                            if ($productSuppliers && $defaultSupplier) {
                                foreach ($productSuppliers as $idSupplier) {
                                    if ($idPsProduct) {
                                        $supplierCombination =
                                        Tools::getValue('supplier_combination_'.$idSupplier);
                                        if (!empty($supplierCombination)) {
                                            foreach ($supplierCombination as $sComb) {
                                                $objPSup = new ProductSupplier();
                                                $objPSup->id_product = (int)$idPsProduct;
                                                $objPSup->id_product_attribute =
                                                (int)$sComb['id_product_attribute'];
                                                $objPSup->id_supplier = (int)$idSupplier;
                                                $objPSup->product_supplier_reference =
                                                pSQL($sComb['supplier_reference']);
                                                $objPSup->id_currency = (int)$sComb['product_price_currency'];
                                                $objPSup->product_supplier_price_te =
                                                (float)$sComb['product_price'];
                                                $objPSup->save();
                                            }
                                        }
                                    }
                                }
                                $objProduct = new Product($idPsProduct);
                                $objProduct->id_supplier = (int)$defaultSupplier;
                                $objProduct->save();
                            }
                        }
                    }
                    // Set customization for products
                    if (isset($idMpProduct)) {
                        $this->saveProductCustomizationField($idMpProduct);
                    }

                    // Set attachments for products
                    if ($productAttachments = Tools::getValue('mp_attachments')) {
                        if ($productAttachments) {
                            Attachment::deleteProductAttachments($idPsProduct);
                            foreach ($productAttachments as $idAttachment) {
                                $objAttachment = new Attachment($idAttachment);
                                $objAttachment->attachProduct($idPsProduct);
                            }
                        }
                    }

                    //update product
                    $wkEdit = true;
                    $wkActive = $objProduct->active;
                    $productUpdated = $objSellerProduct->updateSellerProduct(
                        $productInfo,
                        $wkActive,
                        $idPsProduct
                    );
                    if ($productUpdated) {
                        Hook::exec(
                            'actionAfterUpdateMPProduct',
                            array(
                                'id_mp_product' => $idMpProduct,
                                'id_ps_product' => $idPsProduct,
                                'id_ps_product_attribute' => 0
                            )
                        );
                    }
                } else {
                    //add product
                    $wkEdit = false;
                    $wkActive = Tools::getValue('product_active');
                    $productCreated = $objSellerProduct->addSellerProduct($productInfo, $wkActive, false);
                    if ($productCreated) {
                        $idMpProduct = $productCreated['id_mp_product'];
                        $psProductId = $productCreated['id_ps_product'];

                        // Pack Product
                        $productType = Tools::getValue('product_type');
                        if ($productType == 2) {
                            $mpProductDtl = WkMpSellerProduct::getSellerProductByPsIdProduct($psProductId);
                            if ($mpProductDtl) {
                                $pspkProducts = Tools::getValue('pspk_id_prod');
                                $pspkProdQuant = Tools::getValue('pspk_prod_quant');
                                $pspkIdProdAttr = Tools::getValue('pspk_id_prod_attr');
                                $stockType = Tools::getValue('pack_qty_mgmt');
                                $productInfo['pack_stock_type'] = $stockType;
                                $objMpPack = new WkMpPackProduct();
                                $isPackProduct = $objMpPack->isPackProduct($idMpProduct);
                                if (count($pspkProducts) == count($pspkProdQuant)) {
                                    $objMpPack = new WkMpPackProduct();
                                    if (!$isPackProduct) {
                                        // Standard product to pack product
                                        $objMpPack->isPackProductFieldUpdate($idMpProduct, 1);
                                    } else {
                                        // Update pack product
                                        if ($psProductId) {
                                            Pack::deleteItems($psProductId);
                                        }
                                    }
                                    $objMpPack->updateStockTypeMpPack($idMpProduct, $stockType);
                                    $packProductArray = array();
                                    foreach ($pspkProducts as $key => $value) {
                                        $mpProdDtls = WkMpSellerProduct::getSellerProductByPsIdProduct($value);
                                        $idProdAttr = $pspkIdProdAttr[$key];
                                        $mpIdProdAttr = $objMpPack->getMpProductAttrID($idProdAttr, $value);
                                        $params = array(
                                            'pack_product_id' => $idMpProduct,
                                            'mp_product_id' => $mpProdDtls['id_mp_product'],
                                            'mp_product_id_attribute' => $mpIdProdAttr,
                                            'quantity' => $pspkProdQuant[$key]
                                        );
                                        $packProductArray[] = $params;
                                    }
                                    if ($psProductId) {
                                        $objMpPack->addToPsPack($idMpProduct, $psProductId, $packProductArray);
                                    }
                                }
                            }
                            // End Pack Product
                        } elseif ($productType == 3) {
                            $productInfo['product_type'] = 'virtual';
                            $productInfo['is_virtual'] = 1;
                            $mpVirtualProductName = Tools::getValue('mp_vrt_prod_name');
                            $mpVirtualProductNbDownloadable = Tools::getValue('mp_vrt_prod_nb_downloable');
                            $mpVirtualProductExpDate = Tools::getValue('mp_vrt_prod_expdate');
                            $mpVirtualProductNbDays = Tools::getValue('mp_vrt_prod_nb_days');

                            $objMpVirtualProduct = new WkMpVirtualProduct();
                            $isVirtualProduct = $objMpVirtualProduct->isMpProductIsVirtualProduct($idMpProduct);
                            if (!$isVirtualProduct['display_filename']) {
                                // standard to virtual product
                                if ($_FILES['mp_vrt_prod_file']['size'] > 0) {
                                    $extension = pathinfo($_FILES['mp_vrt_prod_file']['name'], PATHINFO_EXTENSION);

                                    $filePath = _PS_MODULE_DIR_.$this->module->name.'/views/upload/';
                                    $fileName = 'virtual_'.$idMpProduct.'.'.$extension;
                                    $fileLink = $filePath.$fileName;

                                    if ($mpVirtualProductName == '') {
                                        $mpVirtualProductName = $_FILES['mp_vrt_prod_file']['name'];
                                    }

                                    if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'png') {
                                        ImageManager::resize($_FILES['mp_vrt_prod_file']['tmp_name'], $fileLink, null, null, $extension);
                                    } else {
                                        move_uploaded_file($_FILES['mp_vrt_prod_file']['tmp_name'], $fileLink);
                                    }
                                }
                                $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct);
                                if ($mpProductDetail['id_ps_product']) {
                                    $psProductId = $mpProductDetail['id_ps_product'];

                                    $product = new Product($psProductId);
                                    $product->is_virtual = 1;
                                    $product->available_for_order = true;
                                    $product->save();
                                    StockAvailable::setProductOutOfStock($product->id, 1);

                                    if (!$_FILES['mp_vrt_prod_file']['size']) {
                                        $fileLink = 0;
                                    }

                                    $virtualProductArray = array();
                                    $virtualProductArray['mp_product_id'] = $idMpProduct;
                                    $virtualProductArray['display_filename'] = $mpVirtualProductName;

                                    if ($_FILES['mp_vrt_prod_file']['size'] > 0) {
                                        $virtualProductArray['reference_file'] = $fileName;
                                    }

                                    $virtualProductArray['date_expiration'] = $mpVirtualProductExpDate;
                                    $virtualProductArray['nb_days_accessible'] = $mpVirtualProductNbDays;
                                    $virtualProductArray['nb_downloadable'] = $mpVirtualProductNbDownloadable;

                                    $objMpVirtualProduct->updateFile($psProductId, $fileLink, WkMpVirtualProduct::ENABLE, $mpVirtualProductName, $virtualProductArray);
                                }
                            }
                        }

                        if ($idMpProduct && ($specificPricePriority = Tools::getValue('specificPricePriority'))) {
                            $obMpSpecificPrice = new WkMpSpecificRule();
                            $obMpSpecificPrice->addMpSpecificRules($idMpProduct);
                            // Set priority management
                            if ($specificPricePriority) {
                                SpecificPrice::setSpecificPriority($productCreated['id_ps_product'], $specificPricePriority);
                            }
                        }

                        // Set related products
                        if ($relatedProducts = Tools::getValue('related_product')) {
                            WkMpSellerProduct::addRelatedProducts($productCreated['id_ps_product'], $relatedProducts);
                        }
                        // Set tags for products
                        if (isset($productCreated['id_ps_product']) && $productCreated['id_ps_product']) {
                            $tagLangData = array();
                            foreach (Language::getLanguages(true) as $language) {
                                if (Tools::getValue('tag_'.$language['id_lang'])) {
                                    $tagLangData[$language['id_lang']] = explode(
                                        ',',
                                        Tools::getValue('tag_'.$language['id_lang'])
                                    );
                                }
                            }
                            foreach ($tagLangData as $langKey => $tagData) {
                                $tagPsData = array();
                                $tagData = array_unique($tagData);
                                foreach ($tagData as $tag) {
                                    if (!empty(trim($tag))) {
                                        $tagPsData[] = $tag;
                                    }
                                }
                                $tagPsData = implode(',', $tagPsData);
                                Tag::addTags(
                                    $langKey,
                                    $productCreated['id_ps_product'],
                                    $tagPsData,
                                    ','
                                );
                            }
                        }

                        // Set customization for products
                        if (isset($idMpProduct)) {
                            $this->saveProductCustomizationField($idMpProduct);
                        }

                        // Set attachments for products
                        if ($productAttachments = Tools::getValue('mp_attachments')) {
                            $idPsProduct = $productCreated['id_ps_product'];
                            $productAttachments = Tools::getValue('mp_attachments');
                            if ($productAttachments) {
                                foreach ($productAttachments as $idAttachment) {
                                    $objAttachment = new Attachment($idAttachment);
                                    $objAttachment->attachProduct($idPsProduct);
                                }
                            }
                        }

                        // Set Supplier for products
                        if ($defaultSupplier = Tools::getValue('default_supplier')) {
                            $productSuppliers = Tools::getValue('selected_suppliers');
                            $idPsProduct = $productCreated['id_ps_product'];
                            if ($idMpProduct && $idPsProduct) {
                                $objMpSupplier = new WkMpSuppliers();
                                $objMpSupplier->deleteSuppliersByPsProductId($idPsProduct);
                                if ($productSuppliers && $defaultSupplier) {
                                    foreach ($productSuppliers as $idSupplier) {
                                        if ($idPsProduct) {
                                            $objProductSupplier = new ProductSupplier();
                                            $objProductSupplier->id_product = (int)$idPsProduct;
                                            $objProductSupplier->id_product_attribute = 0;
                                            $objProductSupplier->id_supplier = (int)$idSupplier;
                                            $objProductSupplier->id_currency = (int)Context::getContext()->currency->id;
                                            $objProductSupplier->save();
                                        }
                                    }
                                    $objProduct = new Product($idPsProduct);
                                    $objProduct->id_supplier = (int)$defaultSupplier;
                                    $objProduct->save();
                                }
                            }
                        }

                        WkMpSellerProduct::sendMail($idMpProduct, 1, 1);

                        Hook::exec(
                            'actionToogleMPProductCreateStatus',
                            array(
                                'id_product' => $productCreated['id_ps_product'],
                                'id_mp_product' => $idMpProduct,
                                'active' => $wkActive
                            )
                        );

                        Hook::exec('actionAfterAddMPProduct', array('id_mp_product' => $idMpProduct));
                    }
                }

                if (Tools::isSubmit('submitAdd'.$this->table.'AndStay')) {
                    if ($wkEdit) {
                        Tools::redirectAdmin(self::$currentIndex.'&id_mp_product='.(int) $idMpProduct.'&update'.$this->table.'&conf=4&tab='.Tools::getValue('active_tab').'&token='.$this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex.'&id_mp_product='.(int) $idMpProduct.'&update'.$this->table.'&conf=3&tab='.Tools::getValue('active_tab').'&token='.$this->token);
                    }
                } else {
                    if ($wkEdit) {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                    }
                }
            } else {
                if ($idMpProduct) {
                    $this->display = 'edit';
                } else {
                    $this->display = 'add';
                }
            }
        }
    }

    public function saveProductCustomizationField($mpProductId)
    {
        $customFields = Tools::getValue('custom_fields');
        if ($mpProductId && !empty($customFields)) {
            $productDetail = WkMpSellerProduct::getSellerProductByIdProduct($mpProductId);
            $psProductId = $productDetail['id_ps_product'];

            if ($psProductId) {
                $objProductCustomization = new WkMpSellerProduct();
                $objProductCustomization->insertIntoPsProductCustomization($mpProductId, $psProductId, $customFields);
            }
        }
    }

    public function processStatus()
    {
        if (empty($this->errors)) {
            parent::processStatus();
        }
    }

    public function activeSellerProduct($mpProductId = false, $reasonText = false)
    {
        $psProductId = 0;
        if (!$mpProductId) {
            $mpProductId = Tools::getValue('id_mp_product');
        }

        Hook::exec('actionBeforeToggleMPProductStatus', array('id_mp_product' => $mpProductId));
        if (!count($this->errors)) {
            $objMpProduct = new WkMpSellerProduct($mpProductId);
            if ($psProductId = $objMpProduct->id_ps_product) {
                $objPsProduct = new Product($psProductId, false, null, (int) $objMpProduct->id_mp_shop_default);
                if ($objPsProduct->active) { // going to deactivate
                    //product created but deactive now
                    $objMpProduct->status_before_deactivate = 0;
                    $objMpProduct->save();

                    //Update on ps
                    $objPsProduct->active = 0;
                    $objPsProduct->save();

                    WkMpSellerProduct::sendMail($mpProductId, 2, 2, $reasonText);
                } else {
                    // going to activate
                    $objMpSeller = new WkMpSeller($objMpProduct->id_seller);
                    if ($objMpSeller->active) { //if seller is active
                        $isAlreadyApproved = $objMpProduct->admin_approved;

                        $objMpProduct->status_before_deactivate = 1;
                        $objMpProduct->admin_approved = 1;
                        $objMpProduct->save();

                        //Update on ps
                        $objPsProduct->active = 1;
                        $objPsProduct->save();

                        if (!$isAlreadyApproved) {
                            //not approved yet, first time activating
                            Hook::exec(
                                'actionToogleMPProductCreateStatus',
                                array('id_product' => $psProductId, 'id_mp_product' => $mpProductId, 'active' => 1)
                            );
                        }
                        Hook::exec(
                            'actionToogleMPProductActive',
                            array('id_mp_product' => $mpProductId, 'active' => $objPsProduct->active)
                        );
                        WkMpSellerProduct::sendMail($mpProductId, 1, 1);
                    } else {
                        $this->context->controller->errors[] = sprintf(
                            $this->l('You can not activate this product because shop %s is not active right now.'),
                            $objMpSeller->shop_name_unique
                        );
                    }
                }
                Hook::exec(
                    'actionAfterToggleMPProductStatus',
                    array('id_product' => $psProductId, 'active' => $objPsProduct->active)
                );
            }
        }
    }

    protected function processBulkEnableSelection()
    {
        return $this->processBulkStatusSelection(1);
    }

    protected function processBulkDisableSelection()
    {
        return $this->processBulkStatusSelection(0);
    }

    protected function processBulkStatusSelection($status)
    {
        if ($status == 1) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                foreach ($this->boxes as $id) {
                    $objMpProduct = new WkMpSellerProduct($id);
                    if ($psProductId = $objMpProduct->id_ps_product) {
                        $objPsProduct = new Product($psProductId, false, null, (int) $objMpProduct->id_mp_shop_default);
                        if ($objPsProduct->active == 0) {
                            $this->activeSellerProduct($id);
                        }
                    }
                }
            }
        } elseif ($status == 0) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                foreach ($this->boxes as $id) {
                    $objMpProduct = new WkMpSellerProduct($id);
                    if ($psProductId = $objMpProduct->id_ps_product) {
                        $objPsProduct = new Product($psProductId, false, null, (int) $objMpProduct->id_mp_shop_default);
                        if ($objPsProduct->active == 1) {
                            $this->activeSellerProduct($id);
                        }
                    }
                }
            }
        }
    }

    public function duplicateMpProduct($mpProductId = false)
    {
        if (!$mpProductId) {
            $mpProductId = Tools::getValue('id_mp_product');
        }

        $objMpSellerProduct = new WkMpSellerProduct();
        return $objMpSellerProduct->duplicateSellerProduct($mpProductId);
    }

    public function processBulkDuplicate()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $mpProductId) {
                if (!empty($mpProductId) && $mpProductId) {
                    $this->duplicateMpProduct($mpProductId);
                }
            }
            if (empty($this->context->controller->errors)) {
                Tools::redirectAdmin(
                    AdminController::$currentIndex.'&token='.$this->context->controller->token.'&conf=19'
                );
            }
        } else {
            $this->context->controller->errors[] = $this->l('You have to select at least one product in order to duplicate the product.');
        }
    }

    public function ajaxProcessDeleteProductImage()
    {
        //Delete images
        $idImage = Tools::getValue('id_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        if ($idImage && $idMpProduct) {
            $isCover = Tools::getValue('is_cover');
            $objMpImage = new WkMpSellerProductImage();
            if ($objMpImage->deleteProductImage($idMpProduct, $idImage, $isCover)) {
                if ($isCover) {
                    die('2'); // if cover image deleted
                } else {
                    die('1'); // if normal image deleted
                }
            }
        }
        die('0');
    }

    public function ajaxProcessChangeCoverImage()
    {
        //Change cover image in product images
        $idImage = Tools::getValue('id_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        if ($idImage && $idMpProduct) {
            $objMpImage = new WkMpSellerProductImage();
            if ($objMpImage->setProductCoverImage($idMpProduct, $idImage)) {
                die('1');
            }
        }
        die('0');
    }

    public function ajaxProcessfindSellerDefaultLang()
    {
        //Get seller default langauge
        $mpIdCustomer = Tools::getValue('customer_id');
        $mpSellerInfo = WkMpSeller::getSellerDetailByCustomerId($mpIdCustomer);
        if ($mpSellerInfo) {
            $sellerLanguageData = Language::getLanguage((int) $mpSellerInfo['default_lang']);
            die(Tools::jsonEncode($sellerLanguageData)); //close ajax
        }
    }

    public function ajaxProcessUploadimage()
    {
        //Update product image
        if (Tools::getValue('actionIdForUpload')) {
            $actionIdForUpload = Tools::getValue('actionIdForUpload'); //it will be Product Id OR Seller Id
            $adminupload = Tools::getValue('adminupload'); //if uploaded by Admin from backend

            $finalData = WkMpSellerProductImage::uploadImage($_FILES, $actionIdForUpload, $adminupload);

            echo Tools::jsonEncode($finalData);
        }

        die; //ajax close
    }

    public function ajaxProcessDeleteimage()
    {
        //Delete product image
        if (Tools::getValue('actionpage') == 'product') {
            $idImage = Tools::getValue('image_id');
            if ($idImage) {
                WkMpSellerProductImage::deleteProductFilerImage($idImage, true);
            }
        }

        die; //ajax close
    }

    public function ajaxProcessChangeImagePosition()
    {
        $idImage = Tools::getValue('id_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        if ($idImage && $idMpProduct) {
            $idImagePosition = Tools::getValue('id_image_position');
            $toRowIndex = Tools::getValue('to_row_index') + 1;

            if ($psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($idMpProduct)) {
                $objImage = new Image($idImage);
                $objImage->position = $toRowIndex;
                if ($objImage->update()) {
                    $result = WkMpSellerProductImage::changePsProductImagePosition(
                        $psIdProduct,
                        $idImage,
                        $toRowIndex,
                        $idImagePosition
                    );
                    if ($result) {
                        die('1');//ajax close
                    }
                }
            }
        }
        die('0');//ajax close
    }

    public function ajaxProcessProductCategory()
    {
        $sellerIdCustomer = Tools::getValue('seller_id_customer');
        //Load Prestashop category with ajax load of plugin jstree
        WkMpSellerProduct::getMpProductCategory($sellerIdCustomer);
    }

    public function ajaxProcessUpdateDefaultAttribute()
    {
        //Update default combination for seller product
        WkMpProductAttribute::updateMpProductDefaultAttribute();
    }

    public function ajaxProcessDeleteMpCombination()
    {
        //Delete Product combination from combination list at edit product page
        WkMpProductAttribute::deleteMpProductAttribute();
    }

    public function ajaxProcessUpdateMpCombinationQuantity()
    {
        //Change combination qty from product combination list
        $idPsProductAttribute = Tools::getValue('mp_product_attribute_id');
        $combinationQty = Tools::getValue('combi_qty');

        WkMpProductAttribute::setMpProductCombinationQuantity($idPsProductAttribute, $combinationQty);
    }

    public function ajaxProcessAddMoreFeature()
    {
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId(Tools::getValue('idSeller'));
        WkMpHelper::assignDefaultLang($mpSeller['id_seller']);
        $sellerDefaultLanguage = Tools::getValue('sellerDefaultLang');
        if ($sellerDefaultLanguage) {
            $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);
            $this->context->smarty->assign(array(
                'current_lang' => Language::getLanguage((int) $defaultLang),
                'default_lang' => $defaultLang,
                ));
        }
        $permissionData = WkMpHelper::productTabPermission();
        $this->context->smarty->assign(
            array(
                'ps_img_dir' => _PS_IMG_.'l/',
                'controller' => 'admin',
                'fieldrow' => Tools::getValue('fieldrow'),
                'choosedLangId' => Tools::getValue('choosedLangId'),
                'available_features' => Feature::getFeatures(
                    $this->context->language->id,
                    (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)
                ),
                'permissionData' => $permissionData,
            )
        );
        die(
            $this->context->smarty->fetch(
                _PS_MODULE_DIR_.'marketplace/views/templates/front/product/_partials/more-product-feature.tpl'
            )
        );
    }

    public function ajaxProcessGetFeatureValue()
    {
        $featuresValue = FeatureValue::getFeatureValuesWithLang(
            $this->context->language->id,
            (int) Tools::getValue('idFeature')
        );
        if (!empty($featuresValue)) {
            die(Tools::jsonEncode($featuresValue));
        }

        die(false);
    }

    public function ajaxProcessValidateMpForm()
    {
        $data = array('status' => 'ok');
        $params = array();
        parse_str(Tools::getValue('formData'), $params);
        if (!empty($params)) {
            WkMpSellerProduct::validationProductFormField($params);

            // if features are enable or seller is trying to add features
            if (isset($params['wk_feature_row'])) {
                WkMpProductFeature::checkFeatures($params);
            }
        }
        die(Tools::jsonEncode($data));
    }

    public function ajaxProcessMpSpecificPriceRule()
    {
        $mpProductId = Tools::getValue('mp_product_id');
        $keywords = Tools::getValue('keywords');
        parse_str(Tools::getValue('dataval'), $data);
        $cust_search = Tools::getValue('cust_search');
        $editId = Tools::getValue('editId');
        $specificRule = new WkMpSpecificRule();
        if ($cust_search && $keywords) {
            $specificRule->searchCustomer($cust_search);
        } elseif (!$cust_search && $data) {
            $specificRule->processPriceAddition($data);
            die;
        }
        if ($editId) {
            $specificPriceData = new SpecificPrice($editId);
        } elseif (Tools::getValue('id_delete')) {
            $specificPriceData = new SpecificPrice(Tools::getValue('id_delete'));
        }
        $idPSProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpProductId);
        if ($specificPriceData->id_product == $idPSProduct) {
            if (Tools::getValue('delete_slot')) {
                $specificPriceData->delete();
                die('1');
            } elseif ($editId) {
                if (Validate::isLoadedObject($specificPriceData)) {
                    if ($specificPriceData->id_customer > 0) {
                        $customer = new Customer($specificPriceData->id_customer);
                        $specificPriceData->customer_name = $customer->firstname.' '.$customer->lastname;
                        $specificPriceData->customer_email = $customer->email;
                    }
                    $this->ajaxDie(json_encode($specificPriceData));
                }
                die;
            }
        }
        die('');
    }

    public function ajaxProcessMpSearchProduct()
    {
        if (Tools::getValue('module_token') != $this->module->secure_key) {
            die('something went wrong');
        }
        $query = Tools::getValue('prod_letter');
        if (!$query or $query == '' or Tools::strlen($query) < 1) {
            die();
        }

        if ($pos = strpos($query, ' (ref:')) {
            $query = Tools::substr($query, 0, $pos);
        }

        $excludeIds = Tools::getValue('excludeIds');

        if ($excludeIds && $excludeIds != 'NaN') {
            $excludeIds = implode(',', array_map('intval', explode(',', $excludeIds)));
        } else {
            $excludeIds = '';
        }

        // Excluding downloadable products from packs because download from pack is not supported
        $excludeVirtuals = (bool)1;
        $excludePacks = (bool)1;
        $currentLangId = Tools::getValue('current_lang_id');
        $sellerCustId = (int) Tools::getValue('seller_cust_id');
        $currentProduct = (int) Tools::getValue('id_mp_product');
        $idPsCurrent = false;
        if ($currentProduct) {
            $objMpProduct = new WkMpSellerProduct($currentProduct);
            if (Validate::isLoadedObject($objMpProduct)) {
                $idPsCurrent = $objMpProduct->id_ps_product;
            }
        }
        if (isset($sellerCustId) && $sellerCustId) {
            $sellerInfo = WkMpSeller::getSellerDetailByCustomerId($sellerCustId);
            $idSeller = (int) $sellerInfo['id_seller'];
        } else {
            $idSeller = (int) Tools::getValue('seller_id');
        }

        $context = Context::getContext();

        $prevIdProd = array();
        if (Tools::getValue('prev_id')) {
            $prevIdProd = implode(",", Tools::jsonDecode(Tools::getValue('prev_id'), true));
        }
        $objMpPack = new WkMpPackProduct();
        $items = $objMpPack->getSellerProductDetails(
            $idSeller,
            $currentLangId,
            $query,
            $excludePacks,
            $excludeVirtuals,
            $idPsCurrent
        );
        $results = array();
        if ($items && ($excludeIds || strpos($_SERVER['HTTP_REFERER'], 'AdminScenes') !== false)) {
            foreach ($items as $item) {
                echo trim($item['name']).(!empty($item['reference']) ? ' (ref: '.$item['reference'].')' : '').'|'.(int)($item['id_product'])."\n";
            }
        } elseif ($items) {
            // packs
            foreach ($items as $item) {
                // check if product have combination
                if (Combination::isFeatureActive() && $item['cache_default_attribute']) {
                    $combinations = $objMpPack->getSellerProductCombinationDetails($item['id_product'], $currentLangId);
                    if (!empty($combinations)) {
                        foreach ($combinations as $k => $combination) {
                            $mpIdProdAttr = $combination['id_product_attribute'];
                            if ($mpIdProdAttr) {
                                $results[$combination['id_product_attribute']]['id'] = $item['id_product'];
                                $results[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                                !empty($results[$combination['id_product_attribute']]['name']) ? $results[$combination['id_product_attribute']]['name'] .= ' '.$combination['group_name'].'-'.$combination['attribute_name']
                                : $results[$combination['id_product_attribute']]['name'] = $item['name'].' '.$combination['group_name'].'-'.$combination['attribute_name'];
                                if (!empty($combination['reference'])) {
                                    $results[$combination['id_product_attribute']]['ref'] = $combination['reference'];
                                } else {
                                    $results[$combination['id_product_attribute']]['ref'] = !empty($item['reference']) ? $item['reference'] : '';
                                }
                                if (isset($combination['id_image']) && $combination['id_image']) {
                                    if (empty($results[$combination['id_product_attribute']]['image'])) {
                                        $results[$combination['id_product_attribute']]['image'] = str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $combination['id_image'], 'home_default'));
                                    }
                                } else {
                                    $images = Image::getCover($item['id_product']);
                                    if (isset($images['id_image'])) {
                                        $image = $images['id_image'];
                                        $results[$combination['id_product_attribute']]['image'] = str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $image, 'home_default'));
                                    } else {
                                        $results[$combination['id_product_attribute']]['image'] =
                                        _PS_IMG_.'p/'.$this->context->language->iso_code.'-default-'.
                                        ImageType::getFormattedName('home').'.jpg';
                                    }
                                }
                            }
                        }
                    } else {
                        if (isset($item['id_image'])) {
                            $image = str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], 'home_default'));
                        } else {
                            $image = _PS_IMG_.'p/'.$this->context->language->iso_code.'-default-'.
                            ImageType::getFormattedName('home').'.jpg';
                        }
                        $product = array(
                            'id' => (int)($item['id_product']),
                            'name' => $item['name'],
                            'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                            'image' => $image,
                        );
                        array_push($results, $product);
                    }
                } else {
                    if (isset($item['id_image'])) {
                        $image = str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], 'home_default'));
                    } else {
                        $image = _PS_IMG_.'p/'.$this->context->language->iso_code.'-default-'.
                        ImageType::getFormattedName('home').'.jpg';
                    }
                    $product = array(
                        'id' => (int)($item['id_product']),
                        'name' => $item['name'],
                        'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                        'image' => $image,
                    );
                    array_push($results, $product);
                }
            }
            $results = array_values($results);
        }
        echo Tools::jsonEncode($results);
    }

    public function ajaxProcessAddProductImageCaption()
    {
        // Ajax Change cover image
        $idImage = Tools::getValue('id_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        $legend = Tools::getValue('legend');
        foreach (Language::getLanguages() as $lang) {
            if (!Validate::isCatalogName($legend[$lang['id_lang']])) {
                $wkError = sprintf(
                    $this->l('Image caption field in %s is invalid'),
                    $lang['name']
                );
                die($wkError);
            }
            if (Tools::strlen($legend[$lang['id_lang']]) > 128) {
                $wkError = sprintf(
                    $this->l('Image caption must be less than %s characters.'),
                    128
                );
                die($wkError);
            }
        }
        if ($idImage && $idMpProduct && $legend) {
            if (WkMpSellerProduct::isSameProductImage($idMpProduct, $idImage)) {
                $objMpImage = new WkMpSellerProductImage();
                if ($objMpImage->setProductImageLegend($idImage, $legend)) {
                    die('1');
                }
            }
        }
        die('0');
    }

    public function ajaxProcessGetRedirectionType()
    {
        $currentLangId = Tools::getValue('current_lang_id');
        $sellerCustId = (int) Tools::getValue('seller_cust_id');
        $redirectType = Tools::getValue('redirectType');
        if (isset($sellerCustId) && $sellerCustId) {
            $sellerInfo = WkMpSeller::getSellerDetailByCustomerId($sellerCustId);
            $idSeller = (int) $sellerInfo['id_seller'];
        } else {
            $idSeller = (int) Tools::getValue('id_seller');
        }
        if ($redirectType == '301-category' || $redirectType == '302-category') {
            $seller = new WkMpSeller($idSeller);
            if (isset($seller->category_permission)
            && Configuration::get('WK_MP_PRODUCT_CATEGORY_RESTRICTION')
            && $seller->category_permission) {
                $sellerAllowedCatIds = Tools::jsonDecode(($seller->category_permission));
                $sqlFilter = ' AND c.`id_category` IN ('.implode(',', $sellerAllowedCatIds).')';
            } else {
                $sqlFilter = '';
            }
            $redirectCategories = Category::getAllCategoriesName(
                Category::getRootCategory()->id,
                $currentLangId,
                true,
                null,
                true,
                $sqlFilter
            );
            die(Tools::jsonEncode($redirectCategories));
        } elseif ($redirectType == '301-product' || $redirectType == '302-product') {
            $redirectProducts = WkMpSellerProduct::getSellerProduct($idSeller);
            die(Tools::jsonEncode($redirectProducts));
        }
        die('ko');
    }

    public function ajaxProcessAddProductAttachment()
    {
        $idSeller = Tools::getValue('id_seller');
        if ($idSeller && $_FILES['product_attachment']['size'] > 0) {
            $attachment = new Attachment();

            $maximumSize = ((int) Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')) * 1024 * 1024;
            if (_PS_VERSION_ < '1.7.8') {
                if (is_uploaded_file($_FILES['product_attachment']['tmp_name'])) {
                    if ($_FILES['product_attachment']['size'] > (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024)) {
                        die(sprintf(
                            'The file is too large. Maximum size allowed is: %1$d kB. The file you are trying to upload is %2$d kB.',
                            (Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024),
                            number_format(($_FILES['product_attachment']['size'] / 1024), 2, '.', '')
                        ));
                    } else {
                        do {
                            $uniqid = sha1(microtime());
                        } while (file_exists(_PS_DOWNLOAD_DIR_ . $uniqid));
                        if (!copy($_FILES['product_attachment']['tmp_name'], _PS_DOWNLOAD_DIR_ . $uniqid)) {
                            die('File copy failed');
                        }
                        @unlink($_FILES['product_attachment']['tmp_name']);
                    }
                } else {
                    die('The file is missing.');
                }
                $file['id'] = $uniqid;
                $file['mime_type'] = $_FILES['product_attachment']['type'];
                $file['file_name'] = $_FILES['product_attachment']['name'];
            } else {
                $uploader = new PrestaShop\PrestaShop\Core\File\FileUploader(
                    _PS_DOWNLOAD_DIR_,
                    $maximumSize
                );

                if (isset($_FILES['product_attachment'])) {
                    // Standard HTTP upload
                    $fileToUpload = $_FILES['product_attachment'];
                } else {
                    // Get data from binary
                    $fileToUpload = file_get_contents('php://input');
                }

                $file = $uploader->upload($fileToUpload);
                if (!empty($attachment->id)) {
                    unlink(_PS_DOWNLOAD_DIR_ . $attachment->file);
                }
            }

            $attachment->file = $file['id'];
            $attachment->file_name = $_FILES['product_attachment']['name'];
            $attachment->mime = $file['mime_type'];
            foreach (Language::getLanguages() as $lang) {
                $attachment->name[$lang['id_lang']] =
                Tools::getValue('attachment_product_name') ?? $file['file_name'];
                $attachment->description[$lang['id_lang']] =
                Tools::getValue('attachment_product_description') ?? $file['file_name'];
            }
            $attachment->add();
            // Remember affected entity
            $idAttachment = $attachment->id;
            if ($idAttachment) {
                if (WkMpSellerProduct::attachMpSellerAttachment($idSeller, $idAttachment)) {
                    $data = array();
                    $data['id_attachment'] = $idAttachment;
                    $data['attachment_name'] = Tools::getValue('attachment_product_name') ?? $file['file_name'];
                    $data['file_name'] = $attachment->file_name;
                    $data['mime'] = $attachment->mime;
                    die(Tools::jsonEncode($data));
                }
            }
        }
        die('');
    }

    public function ajaxProcessGetSupplierReferences()
    {
        $idMpProduct = Tools::getValue('mp_product_id');
        $idSupplier = Tools::getValue('id_supplier');
        if ($idMpProduct) {
            $combinationDetail = WkMpProductAttribute::getMpCombinationsResume($idMpProduct);
            if (!empty($combinationDetail)) {
                foreach ($combinationDetail as $comb) {
                    $itemId = ProductSupplier::getIdByProductAndSupplier(
                        $comb['id_product'],
                        $comb['id_product_attribute'],
                        $idSupplier
                    );
                    if (!$itemId) {
                        $objProductSupplier = new ProductSupplier();
                        $objProductSupplier->id_product = (int)$comb['id_product'];
                        $objProductSupplier->id_product_attribute = (int)$comb['id_product_attribute'];
                        $objProductSupplier->id_supplier = (int)$idSupplier;
                        $objProductSupplier->id_currency = (int)Context::getContext()->currency->id;
                        $objProductSupplier->save();
                    } else {
                        $objProductSupplier = new ProductSupplier($itemId);
                        $objProductSupplier->delete();
                    }
                }
            } else {
                $idPSProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($idMpProduct);
                $itemId = ProductSupplier::getIdByProductAndSupplier($idPSProduct, 0, $idSupplier);
                if (!$itemId) {
                    $objProductSupplier = new ProductSupplier();
                    $objProductSupplier->id_product = (int)$idPSProduct;
                    $objProductSupplier->id_product_attribute = (int)0;
                    $objProductSupplier->id_supplier = (int)$idSupplier;
                    $objProductSupplier->id_currency = (int)Context::getContext()->currency->id;
                    $objProductSupplier->save();
                } else {
                    $objProductSupplier = new ProductSupplier($itemId);
                    $objProductSupplier->delete();
                }
            }
            $mpProduct = WkMpSellerProduct::getSellerProductWithLang($idMpProduct);
            WkMpHelper::assignDefaultLang($mpProduct['id_seller']);
            $objMpProductSupplier = new WkMpSuppliers();
            $ps_suppliers = $objMpProductSupplier->getInfoByMpProductId($idMpProduct);
            if ($ps_suppliers) {
                $selected_suppliers = array();
                foreach ($ps_suppliers as $supplier) {
                    $selected_suppliers[$supplier['id_supplier']][] = $supplier;
                }
                $this->context->smarty->assign('selected_suppliers_list', $ps_suppliers);
                $this->context->smarty->assign('selected_suppliers_data', $selected_suppliers);
            }
            $currencies = Currency::getCurrencies(false, true, true);
            $this->context->smarty->assign(array(
                'product_info' => $mpProduct,
                'combination_detail' => $combinationDetail,
                'currencies' => $currencies,
            ));
            die(
                $this->context->smarty->fetch(
                    'module:marketplace/views/templates/front/product/suppliers/_partials/mp_supplier_references.tpl'
                )
            );
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        Media::addJsDef(
            array('confirm_delete_customization' =>
                $this->module->l('Are you sure you want to delete this customization field?'),
                'languages' => Language::getLanguages(),
                'ImageCaptionLangError' => $this->l('Image caption field is invalid in'),
            )
        );
        if ($this->display == 'edit') {
            //Upload images
            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/uploadimage-css/jquery.filer.css');
            $this->addCSS(
                _MODULE_DIR_.$this->module->name.'/views/css/uploadimage-css/jquery.filer-dragdropbox-theme.css'
            );
            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/uploadimage-css/uploadphoto.css');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/uploadimage-js/jquery.filer.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/uploadimage-js/uploadimage.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/imageedit.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/managecombination.js');
        }
    }
}
