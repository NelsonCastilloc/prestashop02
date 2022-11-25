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

class MarketplaceUpdateProductModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->id) {
            $idCustomer = $this->context->customer->id;
            $addPermission = 1;
            $permissionData = WkMpHelper::productTabPermission();
            //Override customer id if any staff of seller want to use this controller with permission
            if (Module::isEnabled('mpsellerstaff')) {
                $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($idCustomer);
                if ($staffDetails
                    && $staffDetails['active']
                    && $staffDetails['id_seller']
                    && $staffDetails['seller_status']
                ) {
                    $idStaff = $staffDetails['id_staff'];
                    //Assign variable to display message that permission is allowed or not of this page
                    WkMpSellerStaffPermission::assignProductTabPermission($idStaff, WkMpTabList::MP_PRODUCT_TAB);

                    $staffTabDetails = WkMpTabList::getStaffPermissionWithTabName(
                        $idStaff,
                        $this->context->language->id,
                        WkMpTabList::MP_PRODUCT_TAB
                    );
                    if ($staffTabDetails) {
                        //For add product button permission
                        $addPermission = $staffTabDetails['add'];
                    }

                    //Check product sub tab permission
                    $permissionDetails = WkMpSellerStaffPermission::getProductSubTabPermissionData($idStaff);
                    if ($permissionDetails) {
                        $permissionData = $permissionDetails;
                    }
                }

                //Replace staff customer id to seller customer id for using seller panel pages
                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $mpIdProduct = Tools::getValue('id_mp_product');
            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                $idSeller = $seller['id_seller'];

                $mpSellerProduct = new WkMpSellerProduct($mpIdProduct);
                $idPsProduct = $mpSellerProduct->id_ps_product;

                if ($mpSellerProduct->id_mp_shop_default != Context::getContext()->shop->id) {
                    //Other shop product edit is not allowed
                    Tools::redirect(
                        $this->context->link->getModuleLink(
                            'marketplace',
                            'productlist',
                            array('error' => 1)
                        )
                    );
                }

                // show admin commission on product base price for seller
                if (Configuration::get('WK_MP_SHOW_ADMIN_COMMISSION')) {
                    $objMpCommission = new WkMpCommission();
                    $adminCommission = $objMpCommission->finalCommissionSummaryForSeller($idSeller);
                    if ($adminCommission) {
                        $this->context->smarty->assign('admin_commission', $adminCommission);
                    }
                }

                $objProduct = new Product($idPsProduct);
                $mpProduct = WkMpSellerProduct::getSellerProductWithLang($mpIdProduct);

                if (!Configuration::get('WK_MP_PACK_PRODUCTS')
                && isset($idPsProduct)
                && Pack::isPack($idPsProduct)) {
                    Tools::redirect(
                        $this->context->link->getModuleLink(
                            'marketplace',
                            'productlist',
                            array('pack_permission_error' => 1)
                        )
                    );
                } elseif (!Configuration::get('WK_MP_PACK_PRODUCTS')
                && isset($objProduct->is_virtual)
                && $objProduct->is_virtual) {
                    Tools::redirect(
                        $this->context->link->getModuleLink(
                            'marketplace',
                            'productlist',
                            array('virtual_permission_error' => 1)
                        )
                    );
                }

                // If seller of current product and current seller customer is match
                if ($mpSellerProduct->id_seller == $idSeller) {
                    // If delete product by seller
                    $deleteProduct = Tools::getValue('deleteproduct');
                    if ($deleteProduct) {
                        // if seller delete product, delete process
                        $objMpSellerProduct = new WkMpSellerProduct($mpIdProduct);
                        if ($objMpSellerProduct->delete()) {
                            //To manage staff log (changes add/update/delete)
                            WkMpHelper::setStaffHook(
                                $this->context->customer->id,
                                Tools::getValue('controller'),
                                $mpIdProduct,
                                3
                            ); // 3 for Delete action

                            Tools::redirect(
                                $this->context->link->getModuleLink(
                                    'marketplace',
                                    'productlist',
                                    array('deleted' => 1)
                                )
                            );
                        }
                    }

                    // If duplicate product by seller
                    if (Configuration::get('WK_MP_PRODUCT_ALLOW_DUPLICATE') && Tools::getValue('duplicateproduct')) {
                        //If seller is allowed to duplicate product
                        $objMpSellerProduct = new WkMpSellerProduct();
                        if ($duplicateMpProductId = $objMpSellerProduct->duplicateSellerProduct($mpIdProduct)) {
                            Tools::redirect(
                                $this->context->link->getModuleLink(
                                    'marketplace',
                                    'updateproduct',
                                    array(
                                        'id_mp_product' => (int) $duplicateMpProductId,
                                        'duplicate' => 1
                                    )
                                )
                            );
                        } else {
                            Tools::redirect(
                                $this->context->link->getModuleLink(
                                    'marketplace',
                                    'productlist',
                                    array('error' => 1)
                                )
                            );
                        }
                    }

                    Hook::exec('actionBeforeShowUpdatedProduct', array('mp_product_details' => $mpProduct));

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

                    // Set default lang at every form according to configuration multi-language
                    WkMpHelper::assignDefaultLang($idSeller);

                    //show tax rule group on update product page
                    $taxRuleGroups = TaxRulesGroup::getTaxRulesGroups(true);
                    if ($taxRuleGroups && Configuration::get('WK_MP_SELLER_APPLIED_TAX_RULE')) {
                        $this->context->smarty->assign('tax_rules_groups', $taxRuleGroups);
                        $this->context->smarty->assign('mp_seller_applied_tax_rule', 1);
                    }
                    $this->context->smarty->assign('id_tax_rules_group', $mpProduct['id_tax_rules_group']);

                    // Admin Shipping
                    $carriers = Carrier::getCarriers(
                        $this->context->language->id,
                        true,
                        false,
                        false,
                        null,
                        ALL_CARRIERS
                    );
                    $carriersChoices = array();
                    if ($carriers) {
                        foreach ($carriers as $carrier) {
                            $carriersChoices[$carrier['id_reference'].' - '.$carrier['name'].' ('.$carrier['delay'].')']
                             = $carrier['id_reference'];
                        }
                    }
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
                    $this->defineJSVars($mpIdProduct, $defaultIdCategory);
                    $idMpProduct = $mpIdProduct;
                    if (Configuration::get('WK_MP_PACK_PRODUCTS') && Pack::isPack($idPsProduct)) {
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
                                $mpPackProducts = $objMpPack->customizedAllPactProducsArray(
                                    $mpPackProducts,
                                    $idPsProduct
                                );
                            }

                            $this->context->smarty->assign([
                                'isPackProduct' => $isPackProduct,
                                'mpPackProducts' => $mpPackProducts,
                                'pack_stock_type' => Configuration::get('PS_PACK_STOCK_TYPE'),
                                'product_stock_type' => $objMpPack->getPackedProductStockType($idMpProduct)
                            ]);
                        }
                    } elseif (Configuration::get('WK_MP_VIRTUAL_PRODUCT') && $objProduct->is_virtual) {
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


                    $objDefaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                    $this->context->smarty->assign(array(
                        'id' => $mpIdProduct,
                        'controller' => 'updateproduct',
                        'active_tab' => Tools::getValue('tab'),
                        'static_token' => Tools::getToken(false),
                        'module_dir' => _MODULE_DIR_,
                        'ps_img_dir' => _PS_IMG_.'l/',
                        'defaultCategory' => $defaultCategory,
                        'defaultIdCategory' => $defaultIdCategory,
                        'product_info' => $mpProduct,
                        'is_seller' => 1,
                        'logic' => 3,
                        'defaultCurrencySign' => $objDefaultCurrency->sign,
                        'default_lang' => $seller['default_lang'],
                        'carriersChoices' => $carriersChoices,
                        'selectedCarriers' => $selectedCarriers,
                        'available_features' => Feature::getFeatures(
                            $this->context->language->id,
                            (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)
                        ),
                        'add_permission' => $addPermission,
                        'permissionData' => $permissionData,
                    ));

                    // Display Added Specific Rules
                    if ($idMpProduct && Configuration::get('WK_MP_PRODUCT_SPECIFIC_RULE')) {
                        $obMpSpecificPrice = new WkMpSpecificRule();
                        $obMpSpecificPrice->getMPSpecificRules($mpIdProduct);
                    }

                    // Display Related Products
                    if (Configuration::get('WK_MP_RELATED_PRODUCT')) {
                        $relatedProducts = WkMpSellerProduct::getRelatedProducts($mpIdProduct);
                        $this->context->smarty->assign('relatedProducts', $relatedProducts);
                    }

                    // Display tags for products
                    if (Configuration::get('WK_MP_PRODUCT_TAGS')) {
                        $productTags = Tag::getProductTags($objProduct->id);
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
                    if (Configuration::get('WK_MP_PRODUCT_MANUFACTURER')) {
                        $this->context->smarty->assign('front', 1);
                        if (isset($objProduct) && $objProduct->id_manufacturer) {
                            $this->context->smarty->assign('selected_id_manuf', $objProduct->id_manufacturer);
                        }
                        $objManuf = new WkMpManufacturers();
                        $manufacturers = $objManuf->sellerManufacturers($idSeller, $this->context->language->id);
                        if ($manufacturers) {
                            $this->context->smarty->assign('manufacturers', $manufacturers);
                        }
                    }

                    // Display suppliers for products
                    if (Configuration::get('WK_MP_PRODUCT_SUPPLIER')) {
                        $this->context->smarty->assign('front', 1);
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
                        $currencies = Currency::getCurrencies();
                        $this->context->smarty->assign(array(
                            'modules_dir' => _MODULE_DIR_,
                            'currencies' => $currencies,
                        ));
                    }

                    // Display customization for products
                    if (Configuration::get('WK_MP_PRODUCT_CUSTOMIZATION')) {
                        $objProductCustomization = new WkMpSellerProduct();
                        $customizationFields = $objProductCustomization->getLangFieldValue($idMpProduct);
                        $this->context->smarty->assign('customizationFields', $customizationFields);
                        Media::addJsDef(array(
                            'languages' => Language::getLanguages(),
                            'fieldlabel' => $this->module->l('Field label', 'updateproduct'),
                            'wk_ctype' => $this->module->l('Type', 'updateproduct'),
                            'wk_crequired' => $this->module->l('Required', 'updateproduct'),
                            'custimzationtext' => $this->module->l('Text', 'updateproduct'),
                            'custimzationfile' => $this->module->l('File', 'updateproduct'),
                        ));
                    }

                    // Display Page Redirection Category or Product
                    if (Configuration::get('WK_MP_PRODUCT_PAGE_REDIRECTION')) {
                        if (isset($seller['category_permission'])
                        && Configuration::get('WK_MP_PRODUCT_CATEGORY_RESTRICTION')
                        && $seller['category_permission']) {
                            $sellerAllowedCatIds = Tools::jsonDecode(($seller['category_permission']));
                            $sqlFilter = ' AND c.`id_category` IN ('.implode(',', $sellerAllowedCatIds).')';
                        } else {
                            $sqlFilter = '';
                        }
                        $redirectCategories = Category::getAllCategoriesName(
                            Category::getRootCategory()->id,
                            false,
                            true,
                            null,
                            true,
                            $sqlFilter
                        );
                        $redirectProducts = WkMpSellerProduct::getSellerProduct($idSeller);
                        $this->context->smarty->assign(array(
                            'redirectCategories' => $redirectCategories,
                            'redirectProducts' => $redirectProducts,
                        ));
                    }

                    // Display attachments for products
                    if (Configuration::get('WK_MP_PRODUCT_ATTACHMENT')) {
                        $productAttachments = WkMpSellerProduct::getProductAttachments($idSeller, $seller['default_lang']);
                        if ($productAttachments) {
                            $associatedProduct = Attachment::getAttachments($seller['default_lang'], $idPsProduct);
                            foreach ($productAttachments as &$productAttachment) {
                                $productAttachment['selected'] = false;
                                foreach ($associatedProduct as $assocProduct) {
                                    if ($assocProduct['id_attachment'] == $productAttachment['id_attachment']) {
                                        $productAttachment['selected'] = true;
                                        break;
                                    }
                                }
                            }
                            $this->context->smarty->assign('productAttachments', $productAttachments);
                        }
                    }

                    $this->setTemplate('module:marketplace/views/templates/front/product/updateproduct.tpl');
                } else {
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'dashboard'));
                }
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function postProcess()
    {
        if ((Tools::isSubmit('SubmitProduct') || Tools::isSubmit('StayProduct')) && $this->context->customer->id) {
            $idCustomer = $this->context->customer->id;
            $permissionData = WkMpHelper::productTabPermission();
            //Override customer id if any staff of seller want to use this controller with permission
            if (Module::isEnabled('mpsellerstaff')) {
                $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($idCustomer);
                if ($staffDetails
                    && $staffDetails['active']
                    && $staffDetails['id_seller']
                    && $staffDetails['seller_status']
                ) {
                    $permissionDetails = WkMpSellerStaffPermission::getProductSubTabPermissionData(
                        $staffDetails['id_staff']
                    );
                    if ($permissionDetails) {
                        $permissionData = $permissionDetails;
                    }
                }

                //Replace staff customer id to seller customer id for using seller panel pages
                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }
            // If seller updated the product, update process
            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                $idMpProduct = Tools::getValue('id_mp_product');

                $mpSellerProduct = new WkMpSellerProduct($idMpProduct);
                $idPsProduct = $mpSellerProduct->id_ps_product;

                $mpProduct = WkMpSellerProduct::getSellerProductWithLang($idMpProduct);

                // If seller of current product and current seller customer is match
                if ($mpSellerProduct->id_seller == $seller['id_seller']) {
                    $quantity = Tools::getValue('quantity');

                    //save product minimum quantity
                    if (Configuration::get('WK_MP_PRODUCT_MIN_QTY')) {
                        $minimalQuantity = Tools::getValue('minimal_quantity');
                    } else {
                        $minimalQuantity = $mpProduct['minimal_quantity'];
                    }

                    //save product condition new, used, refurbished
                    if (Configuration::get('WK_MP_PRODUCT_CONDITION')) {
                        $showCondition = Tools::getValue('show_condition');
                        if (!$showCondition) {
                            $showCondition = 0;
                        }
                        $condition = Tools::getValue('condition');
                    } else {
                        $showCondition = $mpProduct['show_condition'];
                        $condition = $mpProduct['condition'];
                    }

                    //save product price
                    $price = Tools::getValue('price');

                    //save product wholesale price
                    if (Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE')) {
                        $wholesalePrice = Tools::getValue('wholesale_price');
                    } else {
                        $wholesalePrice = $mpProduct['wholesale_price'];
                    }

                    //save product unit price
                    if (Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT')) {
                        $unitPrice = Tools::getValue('unit_price');
                        $unity = Tools::getValue('unity');
                    } else {
                        $unitPrice = $mpProduct['unit_price'];
                        $unity = $mpProduct['unity'];
                    }

                    //save product tax rule
                    if (Configuration::get('WK_MP_SELLER_APPLIED_TAX_RULE')) {
                        $idTaxRulesGroup = Tools::getValue('id_tax_rules_group');
                    } else {
                        $idTaxRulesGroup = $mpProduct['id_tax_rules_group'];
                    }

                    // height, width, depth and weight
                    $width = Tools::getValue('width');
                    $width = empty($width) ? '0' : str_replace(',', '.', $width);

                    $height = Tools::getValue('height');
                    $height = empty($height) ? '0' : str_replace(',', '.', $height);

                    $depth = Tools::getValue('depth');
                    $depth = empty($depth) ? '0' : str_replace(',', '.', $depth);

                    $weight = Tools::getValue('weight');
                    $weight = empty($weight) ? '0' : str_replace(',', '.', $weight);

                    // Admin Shipping
                    $psIDCarrierReference = Tools::getValue('ps_id_carrier_reference');
                    if (!$psIDCarrierReference) {
                        $psIDCarrierReference = 0;  // No Shipping Selected
                    }

                    $reference = trim(Tools::getValue('reference'));
                    $ean13JanBarcode = trim(Tools::getValue('ean13'));
                    $upcBarcode = trim(Tools::getValue('upc'));
                    $isbn = trim(Tools::getValue('isbn'));

                    $defaultCategory = Tools::getValue('default_category');
                    $categories = Tools::getValue('product_category');

                    $categories = explode(',', $categories);

                    $sellerDefaultLanguage = Tools::getValue('default_lang');
                    $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

                    if (Configuration::get('WK_MP_SELLER_PRODUCT_VISIBILITY')
                    && $permissionData['optionsPermission']['edit']) {
                        //Product Visibility
                        $availableForOrder = trim(Tools::getValue('available_for_order'));
                        $showPrice = $availableForOrder ? 1 : trim(Tools::getValue('show_price'));
                        $onlineOnly = trim(Tools::getValue('online_only'));
                        $visibility = trim(Tools::getValue('visibility'));
                    }

                    if (!Tools::getValue('product_name_'.$defaultLang)) {
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                            $sellerLang = Language::getLanguage((int) $defaultLang);
                            $this->errors[] = sprintf(
                                $this->module->l('Product name is required in %s', 'updateproduct'),
                                $sellerLang['name']
                            );
                        } else {
                            $this->errors[] = $this->module->l('Product name is required', 'updateproduct');
                        }
                    } else {
                        // Validate form
                        $this->errors = WkMpSellerProduct::validateMpProductForm();

                        Hook::exec('actionBeforeUpdateMPProduct', array('id_mp_product' => $idMpProduct));
                        if (empty($this->errors)) {
                            $productInfo = array();
                            $productInfo['default_lang'] = $defaultLang;

                            // If current product has no combination then product qty will update
                            $objProduct = new Product($idPsProduct);
                            $hasAttribute = $objProduct->hasAttributes();
                            if (!$hasAttribute) {
                                $productInfo['quantity'] = $quantity;
                                $productInfo['minimal_quantity'] = $minimalQuantity;

                                //stock location
                                if (Configuration::get('WK_MP_PRODUCT_STOCK_LOCATION')) {
                                    $productInfo['location'] = Tools::getValue('location');
                                }

                                //Low stock alert
                                if (Configuration::get('WK_MP_PRODUCT_LOW_STOCK_ALERT')) {
                                    $productInfo['low_stock_threshold'] = Tools::getValue('low_stock_threshold');
                                    if (Tools::getValue('low_stock_alert')) {
                                        $productInfo['low_stock_alert'] = 1;
                                    } else {
                                        $productInfo['low_stock_alert'] = 0;
                                    }
                                } else {
                                    $productInfo['low_stock_threshold'] = $mpProduct['low_stock_threshold'];
                                    $productInfo['low_stock_alert'] = $mpProduct['low_stock_alert'];
                                }
                            }

                            //Page Redirection
                            if (Configuration::get('WK_MP_PRODUCT_PAGE_REDIRECTION')) {
                                $productInfo['redirect_type'] = Tools::getValue('redirect_type');
                                $productInfo['id_type_redirected'] = Tools::getValue('id_type_redirected');
                            }

                            $productInfo['id_category_default'] = $defaultCategory;
                            $productInfo['show_condition'] = $showCondition;
                            $productInfo['condition'] = $condition;

                            //Pricing
                            $productInfo['price'] = $price;
                            $productInfo['wholesale_price'] = $wholesalePrice;
                            $productInfo['unit_price'] = $unitPrice; //(Total price divide by unit price)
                            $productInfo['unity'] = $unity;
                            $productInfo['id_tax_rules_group'] = $idTaxRulesGroup;

                            if (Configuration::get('WK_MP_PRODUCT_ON_SALE')) {
                                if (Tools::getValue('on_sale')) {
                                    $productInfo['on_sale'] = 1;
                                } else {
                                    $productInfo['on_sale'] = 0;
                                }
                            } else {
                                $productInfo['on_sale'] = $mpProduct['on_sale'];
                            }

                            $productInfo['additional_delivery_times'] = $mpProduct['additional_delivery_times'];
                            $productInfo['additional_shipping_cost'] = $mpProduct['additional_shipping_cost'];
                            if ((Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') || Module::isEnabled('mpshipping'))
                            && $permissionData['shippingPermission']['edit']) {
                                $productInfo['width'] = $width;
                                $productInfo['height'] = $height;
                                $productInfo['depth'] = $depth;
                                $productInfo['weight'] = $weight;

                                $productInfo['ps_id_carrier_reference'] = $psIDCarrierReference;

                                if (Configuration::get('WK_MP_PRODUCT_DELIVERY_TIME')) {
                                    $productInfo['additional_delivery_times'] = Tools::getValue(
                                        'additional_delivery_times'
                                    );
                                }
                                if (Configuration::get('WK_MP_PRODUCT_ADDITIONAL_FEES')) {
                                    $productInfo['additional_shipping_cost'] = Tools::getValue(
                                        'additional_shipping_cost'
                                    );
                                }
                            }

                            if (Configuration::get('WK_MP_SELLER_PRODUCT_REFERENCE')) {
                                $productInfo['reference'] = $reference;
                            }
                            if ($permissionData['optionsPermission']['edit']) {
                                if (Configuration::get('WK_MP_SELLER_PRODUCT_AVAILABILITY')) {
                                    $productInfo['out_of_stock'] = Tools::getValue('out_of_stock');
                                    $productInfo['available_date'] = Tools::getValue('available_date');
                                }
                                if (Configuration::get('WK_MP_SELLER_PRODUCT_EAN')) {
                                    $productInfo['ean13'] = $ean13JanBarcode;
                                }
                                if (Configuration::get('WK_MP_SELLER_PRODUCT_UPC')) {
                                    $productInfo['upc'] = $upcBarcode;
                                }
                                if (Configuration::get('WK_MP_SELLER_PRODUCT_ISBN')) {
                                    $productInfo['isbn'] = $isbn;
                                }
                                //MPN Reference
                                if (Configuration::get('WK_MP_PRODUCT_MPN')) {
                                    $productInfo['mpn'] = Tools::getValue('mpn');
                                }
                            }

                            foreach (Language::getLanguages(false) as $language) {
                                $productIdLang = $language['id_lang'];
                                $shortDescIdLang = $language['id_lang'];
                                $descIdLang = $language['id_lang'];

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
                                } else {
                                    //if multilang is OFF then all fields will be filled as default lang content
                                    $productIdLang = $defaultLang;
                                    $shortDescIdLang = $defaultLang;
                                    $descIdLang = $defaultLang;
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
                                if (Configuration::get('WK_MP_SELLER_PRODUCT_SEO')
                                && $permissionData['seoPermission']['edit']) {
                                    $metaTitleIdLang = $language['id_lang'];
                                    $metaDescriptionIdLang = $language['id_lang'];

                                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                                        if (!Tools::getValue('meta_title_'.$language['id_lang'])) {
                                            $metaTitleIdLang = $defaultLang;
                                        }
                                        if (!Tools::getValue('meta_description_'.$language['id_lang'])) {
                                            $metaDescriptionIdLang = $defaultLang;
                                        }
                                    } else {
                                        $metaTitleIdLang = $defaultLang;
                                        $metaDescriptionIdLang = $defaultLang;
                                    }

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
                                } else {
                                    $productInfo['link_rewrite'][$language['id_lang']] = Tools::link_rewrite(
                                        Tools::getValue('product_name_'.$productIdLang)
                                    );
                                }

                                //For Avalailiblity Preferences
                                if (Configuration::get('WK_MP_SELLER_PRODUCT_AVAILABILITY')
                                && $permissionData['optionsPermission']['edit']) {
                                    $availableNowIdLang = $language['id_lang'];
                                    $availableLaterIdLang = $language['id_lang'];

                                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                                        if (!Tools::getValue('available_now_'.$language['id_lang'])) {
                                            $availableNowIdLang = $defaultLang;
                                        }
                                        if (!Tools::getValue('available_later_'.$language['id_lang'])) {
                                            $availableLaterIdLang = $defaultLang;
                                        }
                                    } else {
                                        $availableNowIdLang = $defaultLang;
                                        $availableLaterIdLang = $defaultLang;
                                    }

                                    $productInfo['available_now'][$language['id_lang']] = Tools::getValue(
                                        'available_now_'.$availableNowIdLang
                                    );

                                    $productInfo['available_later'][$language['id_lang']] = Tools::getValue(
                                        'available_later_'.$availableLaterIdLang
                                    );
                                }

                                //Delivery Time
                                if ((Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING')
                                || Module::isEnabled('mpshipping'))
                                && $permissionData['shippingPermission']['edit']) {
                                    if (Configuration::get('WK_MP_PRODUCT_DELIVERY_TIME')) {
                                        $deliveryInStockIdLang = $language['id_lang'];
                                        $deliveryOutStockIdLang = $language['id_lang'];

                                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                                            if (!Tools::getValue('delivery_in_stock_'.$language['id_lang'])) {
                                                $deliveryInStockIdLang = $defaultLang;
                                            }
                                            if (!Tools::getValue('delivery_out_stock_'.$language['id_lang'])) {
                                                $deliveryOutStockIdLang = $defaultLang;
                                            }
                                        } else {
                                            $deliveryInStockIdLang = $defaultLang;
                                            $deliveryOutStockIdLang = $defaultLang;
                                        }

                                        $productInfo['delivery_in_stock'][$language['id_lang']] = Tools::getValue(
                                            'delivery_in_stock_'.$deliveryInStockIdLang
                                        );

                                        $productInfo['delivery_out_stock'][$language['id_lang']] = Tools::getValue(
                                            'delivery_out_stock_'.$deliveryOutStockIdLang
                                        );
                                    }
                                }
                            }

                            if (Configuration::get('WK_MP_SELLER_PRODUCT_VISIBILITY')
                            && $permissionData['optionsPermission']['edit']) {
                                $productInfo['available_for_order'] = $availableForOrder;
                                $productInfo['show_price'] = $showPrice;
                                $productInfo['online_only'] = $onlineOnly;
                                $productInfo['visibility'] = $visibility;
                            }

                            if ($categories) {
                                $productInfo['category'] = $categories;
                            } else {
                                $productInfo['category'] = array();
                            }

                            if (Configuration::get('WK_MP_PRODUCT_FEATURE')
                            && $permissionData['featuresPermission']['edit']) {
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
                            }

                            $productType = Tools::getValue('product_type');
                            $productInfo['cache_is_pack'] = '0';
                            if (Configuration::get('WK_MP_PACK_PRODUCTS') && $productType == 2) {
                                if (Configuration::get('WK_MP_VIRTUAL_PRODUCT')) {
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

                                $productInfo['product_type'] = 'pack';
                                $productInfo['cache_is_pack'] = '1';
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
                                    if ($idPsProduct) {
                                        $objMpPack->addToPsPack($idMpProduct, $idPsProduct, $packProductArray);
                                    }
                                }
                            } elseif (Configuration::get('WK_MP_VIRTUAL_PRODUCT') && $productType == 3) {
                                if (Configuration::get('WK_MP_PACK_PRODUCTS')) {
                                    $objMpPack = new WkMpPackProduct();
                                    $isPackProduct = $objMpPack->isPackProduct($idMpProduct);
                                    if ($isPackProduct) {
                                        //pack product to standard product
                                        $objMpPack->isPackProductFieldUpdate($idMpProduct, 0);

                                        if ($idPsProduct) {
                                            Pack::deleteItems($idPsProduct);
                                        }
                                    }
                                }
                                $productInfo['product_type'] = 'virtual';
                                $productInfo['cache_is_pack'] = '0';
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
                                if (Configuration::get('WK_MP_PACK_PRODUCTS')) {
                                    $objMpPack = new WkMpPackProduct();
                                    $isPackProduct = $objMpPack->isPackProduct($idMpProduct);
                                    if ($isPackProduct) {
                                        //pack product to standard product
                                        $objMpPack->isPackProductFieldUpdate($idMpProduct, 0);

                                        if ($idPsProduct) {
                                            Pack::deleteItems($idPsProduct);
                                        }
                                    }
                                } elseif (Configuration::get('WK_MP_VIRTUAL_PRODUCT')) {
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
                            }

                            // Set priority management
                            if (Configuration::get('WK_MP_PRODUCT_SPECIFIC_RULE')) {
                                $specificPricePriority = Tools::getValue('specificPricePriority');
                                if ($specificPricePriority) {
                                    SpecificPrice::setSpecificPriority($idPsProduct, $specificPricePriority);
                                }
                            }

                            // Set related products
                            if (Configuration::get('WK_MP_RELATED_PRODUCT')) {
                                $relatedProducts = Tools::getValue('related_product');
                                WkMpSellerProduct::addRelatedProducts($idPsProduct, $relatedProducts);
                            }

                            // Set tags for products
                            if (Configuration::get('WK_MP_PRODUCT_TAGS')) {
                                Tag::deleteTagsForProduct($idPsProduct);
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
                                        $idPsProduct,
                                        $tagPsData,
                                        ','
                                    );
                                }
                            }

                            // Set manufacturers for products
                            if (Configuration::get('WK_MP_PRODUCT_MANUFACTURER')) {
                                $psManufacturerId = Tools::getValue('product_manufacturer');
                                if ($psManufacturerId) {
                                    $productInfo['id_manufacturer'] = $psManufacturerId;
                                }
                            }

                            // Set Supplier for products
                            if (Configuration::get('WK_MP_PRODUCT_SUPPLIER')) {
                                $productSuppliers = Tools::getValue('selected_suppliers');
                                $defaultSupplier = Tools::getValue('default_supplier');
                                
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
                                        $objProduct->id_supplier = $defaultSupplier;
                                        $objProduct->save();
                                    }
                                }
                            }

                            // Set customization for products
                            if (Configuration::get('WK_MP_PRODUCT_CUSTOMIZATION')) {
                                $this->saveProductCustomizationField($idMpProduct);
                            }

                            // Set attachments for products
                            if (Configuration::get('WK_MP_PRODUCT_ATTACHMENT')) {
                                $productAttachments = Tools::getValue('mp_attachments');
                                if ($productAttachments) {
                                    Attachment::deleteProductAttachments($idPsProduct);
                                    foreach ($productAttachments as $idAttachment) {
                                        $objAttachment = new Attachment($idAttachment);
                                        $objAttachment->attachProduct($idPsProduct);
                                    }
                                }
                            }

                            //if product is active then check admin configure value
                            //that product after update need to approved by admin or not
                            $deactivateAfterUpdate = WkMpSellerProduct::deactivateProductAfterUpdate($idMpProduct);
                            $wkActive = $mpProduct['active'];
                            if (Configuration::get('WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE')) {
                                //approval is needed so deactivate this product
                                $wkActive = false;
                            }
                            $productUpdated = $mpSellerProduct->updateSellerProduct(
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

                                //To manage staff log (changes add/update/delete)
                                WkMpHelper::setStaffHook(
                                    $this->context->customer->id,
                                    Tools::getValue('controller'),
                                    $idMpProduct,
                                    2
                                ); // 2 for Update action

                                if (isset($deactivateAfterUpdate) && $deactivateAfterUpdate) {
                                    $successParams = array('edited_withdeactive' => 1);
                                } else {
                                    $successParams = array('edited_conf' => 1);
                                }
                                if (Tools::isSubmit('StayProduct')) {
                                    $successParams['id_mp_product'] = $idMpProduct;
                                    $successParams['tab'] = Tools::getValue('active_tab');
                                    Tools::redirect(
                                        $this->context->link->getModuleLink(
                                            'marketplace',
                                            'updateproduct',
                                            $successParams
                                        )
                                    );
                                } else {
                                    Tools::redirect(
                                        $this->context->link->getModuleLink(
                                            'marketplace',
                                            'productlist',
                                            $successParams
                                        )
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function defineJSVars($mpIdProduct, $defaultIdCategory)
    {
        $jsVars = array(
                'actionpage' => 'product',
                'adminupload' => 0,
                'actionIdForUpload' => $mpIdProduct,
                'defaultIdCategory' => $defaultIdCategory,
                'deleteaction' => 'jFiler-item-trash-action',
                'path_sellerproduct' => $this->context->link->getModuleLink('marketplace', 'updateproduct'),
                'path_uploader' => $this->context->link->getModulelink('marketplace', 'uploadimage'),
                'ajax_urlpath' => $this->context->link->getModuleLink('marketplace', 'productimageedit'),
                'path_addfeature' => $this->context->link->getModuleLink('marketplace', 'updateproduct'),
                'req_prod_name' => $this->module->l('Product name is required in Default Language -', 'updateproduct'),
                'req_catg' => $this->module->l('Please select atleast one category.', 'updateproduct'),
                'space_error' => $this->module->l('Space is not allowed.', 'updateproduct'),
                'confirm_delete_msg' => $this->module->l('Are you sure you want to delete this image?', 'updateproduct'),
                'confirm_delete_customization' =>
                $this->module->l('Are you sure you want to delete this customization field?', 'updateproduct'),
                'delete_msg' => $this->module->l('Deleted.', 'updateproduct'),
                'error_msg' => $this->module->l('An error occurred.', 'updateproduct'),
                'mp_tinymce_path' => _MODULE_DIR_.$this->module->name.'/libs',
                'img_module_dir' => _MODULE_DIR_.$this->module->name.'/views/img/',
                'image_drag_drop' => 1,
                'drag_drop' => $this->module->l('Drag & Drop to upload', 'updateproduct'),
                'or' => $this->module->l('or', 'updateproduct'),
                'pick_img' => $this->module->l('Pick image', 'updateproduct'),
                'choosefile' => $this->module->l('Choose images', 'updateproduct'),
                'choosefiletoupload' => $this->module->l('Choose images to upload', 'updateproduct'),
                'imagechoosen' => $this->module->l('Images were chosen', 'updateproduct'),
                'dragdropupload' => $this->module->l('Drop file here to upload', 'updateproduct'),
                'only' => $this->module->l('Only', 'updateproduct'),
                'imagesallowed' => $this->module->l('Images are allowed to be uploaded.', 'updateproduct'),
                'onlyimagesallowed' => $this->module->l('Only Images are allowed to be uploaded.', 'updateproduct'),
                'imagetoolarge' => $this->module->l('is too large! Please upload image up to', 'updateproduct'),
                'imagetoolargeall' => $this->module->l('Images you have choosed are too large! Please upload images up to', 'updateproduct'),
                'req_price' => $this->module->l('Product price is required.', 'updateproduct'),
                'notax_avalaible' => $this->module->l('No tax available', 'updateproduct'),
                'some_error' => $this->module->l('Some error occured.', 'updateproduct'),
                'Choose' => $this->module->l('Choose', 'updateproduct'),
                'confirm_delete_combination' => $this->module->l('Are you sure you want to delete this combination?', 'updateproduct'),
                'noAllowDefaultAttribute' => $this->module->l('You can not make deactivated attribute as default attribute.', 'updateproduct'),
                'not_allow_todeactivate_combination' => $this->module->l('You can not deactivate this combination. Atleast one combination must be active.', 'updateproduct'),
                'no_value' => $this->module->l('No Value Found', 'updateproduct'),
                'choose_value' => $this->module->l('Choose a value', 'updateproduct'),
                'value_missing' => $this->module->l('Feature value is missing', 'updateproduct'),
                'value_length_err' => $this->module->l('Feature value is too long', 'updateproduct'),
                'value_name_err' => $this->module->l('Feature value is not valid', 'updateproduct'),
                'feature_err' => $this->module->l('Feature is not selected', 'updateproduct'),
                'generate_combination_confirm_msg' => $this->module->l('You will lose all unsaved modifications. Are you sure that you want to proceed?', 'updateproduct'),
                'enabled' => $this->module->l('Enabled', 'updateproduct'),
                'disabled' => $this->module->l('Disabled', 'updateproduct'),
                'update_success' => $this->module->l('Updated Successfully', 'updateproduct'),
                'invalid_value' => $this->module->l('Invalid Value', 'updateproduct'),
                'success_msg' => $this->module->l('Success', 'updateproduct'),
                'error_msg' => $this->module->l('Error', 'updateproduct'),
                'ImageCaptionLangError' => $this->module->l('Image caption field is invalid in', 'updateproduct'),
            );

        Media::addJsDef($jsVars);
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

    /**
     * Load Prestashop category with ajax load of plugin jstree.
     */
    public function displayAjaxProductCategory()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        WkMpSellerProduct::getMpProductCategory();
    }

    public function displayAjaxUpdateDefaultAttribute()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        //Update default combination for seller product
        WkMpProductAttribute::updateMpProductDefaultAttribute();
    }

    public function displayAjaxDeleteMpCombination()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        //Delete Product combination from combination list at edit product page
        WkMpProductAttribute::deleteMpProductAttribute();
    }

    /**
     * Change combination qty from product combination list
     */
    public function displayAjaxUpdateMpCombinationQuantity()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        $idPsProductAttribute = Tools::getValue('mp_product_attribute_id');
        $combinationQty = Tools::getValue('combi_qty');

        WkMpProductAttribute::setMpProductCombinationQuantity($idPsProductAttribute, $combinationQty);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'updateproduct'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Update Product', 'updateproduct'),
            'url' => '',
        );

        return $breadcrumb;
    }

    public function displayAjaxAddMoreFeature()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        $idCustomer = $this->context->customer->id;
        //Override customer id if any staff of seller want to use this controller
        if (Module::isEnabled('mpsellerstaff')) {
            $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
            if ($getCustomerId) {
                $idCustomer = $getCustomerId;
            }
        }
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
        WkMpHelper::assignDefaultLang($mpSeller['id_seller']);
        $permissionData = WkMpHelper::productTabPermission();
        $this->context->smarty->assign(
            array(
                'default_lang' => $mpSeller['default_lang'],
                'permissionData' => $permissionData,
                'fieldrow' => Tools::getValue('fieldrow'),
                'choosedLangId' => Tools::getValue('choosedLangId'),
                'available_features' => Feature::getFeatures(
                    $this->context->language->id,
                    (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)
                ),
            )
        );
        die(
            $this->context->smarty->fetch(
                'module:marketplace/views/templates/front/product/_partials/more-product-feature.tpl'
            )
        );
    }

    public function displayAjaxGetFeatureValue()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        $idCustomer = $this->context->customer->id;
        //Override customer id if any staff of seller want to use this controller
        if (Module::isEnabled('mpsellerstaff')) {
            $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
            if ($getCustomerId) {
                $idCustomer = $getCustomerId;
            }
        }
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
        if ($mpSeller && $mpSeller['active']) {
            $featuresValue = FeatureValue::getFeatureValuesWithLang(
                $this->context->language->id,
                (int) Tools::getValue('idFeature')
            );
            if (!empty($featuresValue)) {
                die(Tools::jsonEncode($featuresValue));
            } else {
                die(false);
            }
        }
        die(false);
    }

    public function displayAjaxValidateMpForm()
    {
        $data = array('status' => 'ok');
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
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

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryUI('ui.datepicker');
        $this->addJqueryPlugin('tablednd');
        $this->addjQueryPlugin('growl', null, false);

        $this->registerStylesheet(
            'mp-marketplace_account',
            'modules/'.$this->module->name.'/views/css/marketplace_account.css'
        );
        $this->registerStylesheet(
            'mp_global_style-css',
            'modules/'.$this->module->name.'/views/css/mp_global_style.css'
        );

        $this->registerJavascript(
            'mp-mp_form_validation',
            'modules/'.$this->module->name.'/views/js/mp_form_validation.js'
        );
        $this->registerJavascript(
            'mp-change_multilang',
            'modules/'.$this->module->name.'/views/js/change_multilang.js'
        );

        //for mp product combination list
        $this->registerJavascript(
            'mp-managecombination-js',
            'modules/'.$this->module->name.'/views/js/managecombination.js'
        );

        //Upload images
        $this->registerStylesheet(
            'mp-filer-css',
            'modules/'.$this->module->name.'/views/css/uploadimage-css/jquery.filer.css'
        );
        $this->registerStylesheet(
            'mp-filer-dragdropbox-theme-css',
            'modules/'.$this->module->name.'/views/css/uploadimage-css/jquery.filer-dragdropbox-theme.css'
        );
        $this->registerStylesheet(
            'mp-uploadphoto-css',
            'modules/'.$this->module->name.'/views/css/uploadimage-css/uploadphoto.css'
        );
        $this->registerJavascript(
            'mp-filer-js',
            'modules/'.$this->module->name.'/views/js/uploadimage-js/jquery.filer.js'
        );
        $this->registerJavascript(
            'mp-uploadimage-js',
            'modules/'.$this->module->name.'/views/js/uploadimage-js/uploadimage.js'
        );
        $this->registerJavascript(
            'mp-imageedit',
            'modules/'.$this->module->name.'/views/js/imageedit.js'
        );

        //Category tree
        $this->registerStylesheet(
            'mp-categorytree-css',
            'modules/'.$this->module->name.'/views/js/categorytree/themes/default/style.min.css'
        );
        $this->registerJavascript(
            'mp-jstree-js',
            'modules/'.$this->module->name.'/views/js/categorytree/jstree.min.js'
        );
        $this->registerJavascript(
            'mp-wk_jstree-js',
            'modules/'.$this->module->name.'/views/js/categorytree/wk_jstree.js'
        );
    }
}
