<?php

use PrestaShopBundle\Entity\Lang;

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

class MarketplaceAddProductModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
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
                    $idStaff = $staffDetails['id_staff'];
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

            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($mpSeller && $mpSeller['active']) {
                // show admin commission on product base price for seller
                if (Configuration::get('WK_MP_SHOW_ADMIN_COMMISSION')) {
                    $objMpCommission = new WkMpCommission();
                    $adminCommission = $objMpCommission->finalCommissionSummaryForSeller($mpSeller['id_seller']);
                    if ($adminCommission) {
                        $this->context->smarty->assign('admin_commission', $adminCommission);
                    }
                }

                // Set default lang at every form according to configuration multi-language
                WkMpHelper::assignDefaultLang($mpSeller['id_seller']);

                //show tax rule group on add product page
                $taxRuleGroups = TaxRulesGroup::getTaxRulesGroups(true);
                if ($taxRuleGroups && Configuration::get('WK_MP_SELLER_APPLIED_TAX_RULE')) {
                    $this->context->smarty->assign('tax_rules_groups', $taxRuleGroups);
                    $this->context->smarty->assign('mp_seller_applied_tax_rule', 1);
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

                $idCategory = array(Category::getRootCategory()->id); //home category id
                $defaultCategory = Category::getCategoryInformations($idCategory, $this->context->language->id);

                $objDefaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                $this->context->smarty->assign(array(
                    'module_dir' => _MODULE_DIR_,
                    'active_tab' => Tools::getValue('tab'),
                    'static_token' => Tools::getToken(false),
                    'default_lang' => $mpSeller['default_lang'],
                    'defaultCategory' => $defaultCategory,
                    'defaultCurrencySign' => $objDefaultCurrency->sign,
                    'logic' => 3,
                    'logged' => $this->context->customer->isLogged(),
                    'carriersChoices' => $carriersChoices,
                    'ps_img_dir' => _PS_IMG_.'l/',
                    'available_features' => Feature::getFeatures(
                        $this->context->language->id,
                        (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)
                    ),
                    'permissionData' => $permissionData,
                ));

                $this->defineJSVars();


                // Display Add Specific Rules
                if (Configuration::get('WK_MP_PRODUCT_SPECIFIC_RULE')) {
                    $obMpSpecificPrice = new WkMpSpecificRule();
                    $obMpSpecificPrice->assignAddMPSpecificRulesVars();
                }

                // Display brands for products
                if (Configuration::get('WK_MP_PRODUCT_MANUFACTURER')) {
                    $objManuf = new WkMpManufacturers();
                    $manufacturers = $objManuf->sellerManufacturers($mpSeller['id_seller'], $this->context->language->id);
                    if ($manufacturers) {
                        $this->context->smarty->assign(array(
                            'manufacturers' => $manufacturers,
                            'front' => 1,
                        ));
                    }
                }

                // Display brands for products
                if (Configuration::get('WK_MP_PRODUCT_SUPPLIER')) {
                    $this->context->smarty->assign('front', 1);
                    $this->context->smarty->assign('selected_id_supplier', 0);
                    $objMpSupplier = new WkMpSuppliers();
                    $suppliers = $objMpSupplier->getSuppliersForProductBySellerId($mpSeller['id_seller']);

                    if ($suppliers) {
                        $this->context->smarty->assign('suppliers', $suppliers);
                    }

                    $this->context->smarty->assign('modules_dir', _MODULE_DIR_);
                }

                // Display customization for products
                if (Configuration::get('WK_MP_PRODUCT_CUSTOMIZATION')) {
                    Media::addJsDef(array(
                        'languages' => Language::getLanguages(),
                        'fieldlabel' => $this->module->l('Field label', 'addproduct'),
                        'wk_ctype' => $this->module->l('Type', 'addproduct'),
                        'wk_crequired' => $this->module->l('Required', 'addproduct'),
                        'custimzationtext' => $this->module->l('Text', 'addproduct'),
                        'custimzationfile' => $this->module->l('File', 'addproduct'),
                    ));
                }

                // Display Page Redirection Category or Product
                if (Configuration::get('WK_MP_PRODUCT_PAGE_REDIRECTION')) {
                    if (isset($mpSeller['category_permission'])
                    && Configuration::get('WK_MP_PRODUCT_CATEGORY_RESTRICTION')
                    && $mpSeller['category_permission']) {
                        $sellerAllowedCatIds = Tools::jsonDecode(($mpSeller['category_permission']));
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
                    $redirectProducts = WkMpSellerProduct::getSellerProduct($mpSeller['id_seller']);
                    $this->context->smarty->assign(array(
                        'redirectCategories' => $redirectCategories,
                        'redirectProducts' => $redirectProducts,
                    ));
                }

                // Display attachments for products
                if (Configuration::get('WK_MP_PRODUCT_ATTACHMENT')) {
                    $productAttachments = WkMpSellerProduct::getProductAttachments($mpSeller['id_seller'], $mpSeller['default_lang']);
                    if ($productAttachments) {
                        foreach ($productAttachments as &$productAttachment) {
                            $productAttachment['selected'] = false;
                        }
                        $this->context->smarty->assign('productAttachments', $productAttachments);
                    }
                }

                $this->setTemplate('module:marketplace/views/templates/front/product/addproduct.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect(
                'index.php?controller=authentication&back='.
                urlencode($this->context->link->getModuleLink('marketplace', 'addproduct'))
            );
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('SubmitProduct') || Tools::isSubmit('StayProduct')) {
            $objSellerProduct = new WkMpSellerProduct();

            //get data from add product form
            $quantity = Tools::getValue('quantity');

            //save product minimum quantity
            if (Configuration::get('WK_MP_PRODUCT_MIN_QTY')) {
                $minimalQuantity = Tools::getValue('minimal_quantity');
            } else {
                $minimalQuantity = 1; //default value
            }

            //save product condition new, used, refurbished
            if (Configuration::get('WK_MP_PRODUCT_CONDITION')) {
                $showCondition = Tools::getValue('show_condition');
                if (!$showCondition) {
                    $showCondition = 0;
                }
                $condition = Tools::getValue('condition');
            } else {
                $showCondition = 0;
                $condition = 'new';
            }

            //save product price
            $price = Tools::getValue('price');

            //save product wholesale price
            if (Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE')) {
                $wholesalePrice = Tools::getValue('wholesale_price');
            } else {
                $wholesalePrice = 0;
            }

            //save product unit price
            if (Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT')) {
                $unitPrice = Tools::getValue('unit_price');
                $unity = Tools::getValue('unity');
            } else {
                $unitPrice = 0;
                $unity = '';
            }

            //save product tax rule
            if (Configuration::get('WK_MP_SELLER_APPLIED_TAX_RULE')) {
                $idTaxRulesGroup = Tools::getValue('id_tax_rules_group');
            } else {
                $idTaxRulesGroup = 1;
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

            if (Configuration::get('WK_MP_SELLER_PRODUCT_VISIBILITY')) {
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
                        $this->module->l('Product name is required in %s', 'addproduct'),
                        $sellerLang['name']
                    );
                } else {
                    $this->errors[] = $this->module->l('Product name is required', 'addproduct');
                }
            } else {
                // Validate form
                $this->errors = WkMpSellerProduct::validateMpProductForm();

                $idCustomer = $this->context->customer->id;
                $permissionData = WkMpHelper::productTabPermission();
                //Override customer id if any staff of seller want to use this controller
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

                    $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                    if ($getCustomerId) {
                        $idCustomer = $getCustomerId;
                    }
                }

                $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
                $idSeller = $mpSeller['id_seller'];

                Hook::exec('actionBeforeAddMPProduct', array('id_seller' => $idSeller));

                if (empty($this->errors)) {
                    $productInfo = array();
                    $productInfo['id_seller'] = $idSeller;
                    $productInfo['default_lang'] = $defaultLang;
                    $productInfo['quantity'] = $quantity;
                    $productInfo['minimal_quantity'] = $minimalQuantity;
                    $productInfo['id_ps_product'] = 0; // prestashop product id
                    $productInfo['id_category_default'] = $defaultCategory;
                    $productInfo['id_ps_shop'] = $this->context->shop->id;
                    $productInfo['show_condition'] = $showCondition;
                    $productInfo['condition'] = $condition;

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
                    }

                    //Page Redirection
                    if (Configuration::get('WK_MP_PRODUCT_PAGE_REDIRECTION')) {
                        $productInfo['redirect_type'] = Tools::getValue('redirect_type');
                        $productInfo['id_type_redirected'] = Tools::getValue('id_type_redirected');
                    }

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
                    }

                    if ((Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') || Module::isEnabled('mpshipping'))
                    && $permissionData['shippingPermission']['add']) {
                        $productInfo['width'] = $width;
                        $productInfo['height'] = $height;
                        $productInfo['depth'] = $depth;
                        $productInfo['weight'] = $weight;

                        $productInfo['ps_id_carrier_reference'] = $psIDCarrierReference;

                        if (Configuration::get('WK_MP_PRODUCT_DELIVERY_TIME')) {
                            $productInfo['additional_delivery_times'] = Tools::getValue('additional_delivery_times');
                        }
                        if (Configuration::get('WK_MP_PRODUCT_ADDITIONAL_FEES')) {
                            $productInfo['additional_shipping_cost'] = Tools::getValue('additional_shipping_cost');
                        }
                    }

                    if (Configuration::get('WK_MP_SELLER_PRODUCT_REFERENCE')) {
                        $productInfo['reference'] = $reference;
                    }

                    if ($permissionData['optionsPermission']['add']) {
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

                        //if product name in other language is not available
                        //then fill with seller language same for others
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
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
                        && $permissionData['seoPermission']['add']) {
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
                        && $permissionData['optionsPermission']['add']) {
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
                        if ((Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') || Module::isEnabled('mpshipping'))
                        && $permissionData['shippingPermission']['add']) {
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
                    && $permissionData['optionsPermission']['add']) {
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
                    && $permissionData['featuresPermission']['add']) {
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

                    $wkActive = 1; //Default approved
                    if (Configuration::get('WK_MP_PRODUCT_ADMIN_APPROVE')) {
                        $wkActive = 0; //Need to approve by admin
                    }

                    $sendMailToAdmin = false;
                    if (Configuration::get('WK_MP_MAIL_ADMIN_PRODUCT_ADD')) {
                        $sendMailToAdmin = true;
                    }

                    // Set manufacturers for products
                    if (Configuration::get('WK_MP_PRODUCT_MANUFACTURER')) {
                        $psManufacturerId = Tools::getValue('product_manufacturer');
                        if ($psManufacturerId) {
                            $productInfo['id_manufacturer'] = $psManufacturerId;
                        }
                    }

                    $productCreated = $objSellerProduct->addSellerProduct($productInfo, $wkActive, $sendMailToAdmin);
                    if ($productCreated) {
                        $mpIdProduct = $productCreated['id_mp_product'];
                        $productType = Tools::getValue('product_type');
                        $productInfo['cache_is_pack'] = '0';
                        if (Configuration::get('WK_MP_PACK_PRODUCTS') && $productType == 2) {
                            $productInfo['product_type'] = 'pack';
                            $pspkProducts = Tools::getValue('pspk_id_prod');
                            $pspkProdQuant = Tools::getValue('pspk_prod_quant');
                            $pspkIdProdAttr = Tools::getValue('pspk_id_prod_attr');
                            $stockType = Tools::getValue('pack_qty_mgmt');
                            $productInfo['pack_stock_type'] = $stockType;
                            $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct);
                            $idPsProduct = $mpProductDetail['id_ps_product'];
                            $objMpPack = new WkMpPackProduct();
                            $isPackProduct = $objMpPack->isPackProduct($mpIdProduct);
                            if (count($pspkProducts) == count($pspkProdQuant)) {
                                $objMpPack = new WkMpPackProduct();
                                if (!$isPackProduct) {
                                    // Standard product to pack product
                                    $objMpPack->isPackProductFieldUpdate($mpIdProduct, 1);
                                } else {
                                    // Update pack product
                                    if ($idPsProduct) {
                                        Pack::deleteItems($idPsProduct);
                                    }
                                }
                                $objMpPack->updateStockTypeMpPack($mpIdProduct, $stockType);
                                $packProductArray = array();
                                foreach ($pspkProducts as $key => $value) {
                                    $mpProdDtls = WkMpSellerProduct::getSellerProductByPsIdProduct($value);
                                    $idProdAttr = $pspkIdProdAttr[$key];
                                    $mpIdProdAttr = $objMpPack->getMpProductAttrID($idProdAttr, $value);
                                    $params = array(
                                        'pack_product_id' => $mpIdProduct,
                                        'mp_product_id' => $mpProdDtls['id_mp_product'],
                                        'mp_product_id_attribute' => $mpIdProdAttr,
                                        'quantity' => $pspkProdQuant[$key]
                                    );
                                    $packProductArray[] = $params;
                                }
                                if ($idPsProduct) {
                                    $objMpPack->addToPsPack($mpIdProduct, $idPsProduct, $packProductArray);
                                }
                            }
                        } elseif (Configuration::get('WK_MP_VIRTUAL_PRODUCT') && $productType == 3) {
                            $productInfo['product_type'] = 'virtual';
                            $productInfo['is_virtual'] = 1;
                            $mpVirtualProductName = Tools::getValue('mp_vrt_prod_name');
                            $mpVirtualProductNbDownloadable = Tools::getValue('mp_vrt_prod_nb_downloable');
                            $mpVirtualProductExpDate = Tools::getValue('mp_vrt_prod_expdate');
                            $mpVirtualProductNbDays = Tools::getValue('mp_vrt_prod_nb_days');

                            $objMpVirtualProduct = new WkMpVirtualProduct();
                            $isVirtualProduct = $objMpVirtualProduct->isMpProductIsVirtualProduct($mpIdProduct);
                            if (!$isVirtualProduct) {
                                // standard to virtual product
                                if ($_FILES['mp_vrt_prod_file']['size'] > 0) {
                                    $extension = pathinfo($_FILES['mp_vrt_prod_file']['name'], PATHINFO_EXTENSION);

                                    $filePath = _PS_MODULE_DIR_.$this->module->name.'/views/upload/';
                                    $fileName = 'virtual_'.$mpIdProduct.'.'.$extension;
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

                                $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct);
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
                                    $virtualProductArray['mp_product_id'] = $mpIdProduct;
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
                                    $fileName = 'virtual_'.$mpIdProduct.'.'.$extension;
                                    $fileLink = $filePath.$fileName;

                                    if ($mpVirtualProductName == '') {
                                        $mpVirtualProductName = $_FILES['mp_vrt_prod_file']['name'];
                                    }

                                    $previousFile = glob($filePath.'virtual_'.$mpIdProduct.'.*');
                                    if (count($previousFile)) {
                                        unlink($previousFile[0]);
                                    }
                                    if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'png') {
                                        ImageManager::resize($_FILES['mp_vrt_prod_file']['tmp_name'], $fileLink, null, null, $extension);
                                    } else {
                                        move_uploaded_file($_FILES['mp_vrt_prod_file']['tmp_name'], $fileLink);
                                    }
                                }

                                $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct);

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
                                    $virtualProductArray['mp_product_id'] = $mpIdProduct;
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
                        }

                        // Set specific rule and priority management
                        if ($mpIdProduct && Configuration::get('WK_MP_PRODUCT_SPECIFIC_RULE')) {
                            $obMpSpecificPrice = new WkMpSpecificRule();
                            $obMpSpecificPrice->addMpSpecificRules($mpIdProduct);
                            // Set priority management
                            $specificPricePriority = Tools::getValue('specificPricePriority');
                            if ($specificPricePriority) {
                                SpecificPrice::setSpecificPriority($productCreated['id_ps_product'], $specificPricePriority);
                            }
                        }
                        // Set related products
                        if (Configuration::get('WK_MP_RELATED_PRODUCT')) {
                            $relatedProducts = Tools::getValue('related_product');
                            WkMpSellerProduct::addRelatedProducts($productCreated['id_ps_product'], $relatedProducts);
                        }
                        // Set tags for products
                        if (Configuration::get('WK_MP_PRODUCT_TAGS')) {
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

                        // Set Supplier for products
                        if (Configuration::get('WK_MP_PRODUCT_SUPPLIER')) {
                            $productSuppliers = Tools::getValue('selected_suppliers');
                            $defaultSupplier = Tools::getValue('default_supplier');
                            $idMpProduct = $productCreated['id_mp_product'];
                            $idPsProduct = $productCreated['id_ps_product'];
                            if ($idMpProduct && $idPsProduct) {
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

                        // Set customization for products
                        if (Configuration::get('WK_MP_PRODUCT_CUSTOMIZATION')) {
                            $this->addNewCustomizationField($mpIdProduct, $productCreated['id_ps_product']);
                        }

                        // Set attachments for products
                        if (Configuration::get('WK_MP_PRODUCT_ATTACHMENT')) {
                            $idPsProduct = $productCreated['id_ps_product'];
                            $productAttachments = Tools::getValue('mp_attachments');
                            if ($productAttachments) {
                                foreach ($productAttachments as $idAttachment) {
                                    $objAttachment = new Attachment($idAttachment);
                                    $objAttachment->attachProduct($idPsProduct);
                                }
                            }
                        }

                        Hook::exec(
                            'actionToogleMPProductCreateStatus',
                            array(
                                'id_product' => $productCreated['id_ps_product'],
                                'id_mp_product' => $mpIdProduct,
                                'active' => $wkActive
                            )
                        );

                        Hook::exec('actionAfterAddMPProduct', array('id_mp_product' => $mpIdProduct));

                        //To manage staff log (changes add/update/delete)
                        WkMpHelper::setStaffHook(
                            $this->context->customer->id,
                            Tools::getValue('controller'),
                            $mpIdProduct,
                            1
                        ); // 1 for Add action

                        $params = array('created_conf' => 1);
                        if (Tools::isSubmit('StayProduct')) {
                            $params['id_mp_product'] = $mpIdProduct;
                            $params['tab'] = Tools::getValue('active_tab');
                            Tools::redirect(
                                $this->context->link->getModuleLink(
                                    'marketplace',
                                    'updateproduct',
                                    $params
                                )
                            );
                        } else {
                            Tools::redirect(
                                $this->context->link->getModuleLink(
                                    'marketplace',
                                    'productlist',
                                    $params
                                )
                            );
                        }
                    }
                }
            }
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
                'actionpage' => 'product',
                'path_sellerproduct' => $this->context->link->getModuleLink('marketplace', 'addproduct'),
                'path_addfeature' => $this->context->link->getModuleLink('marketplace', 'addproduct'),
                'req_prod_name' => $this->module->l('Product name is required in Default Language -', 'addproduct'),
                'amt_valid' => $this->module->l('Amount should be numeric only.', 'addproduct'),
                'req_catg' => $this->module->l('Please select atleast one category.', 'addproduct'),
                'req_price' => $this->module->l('Product price is required.', 'addproduct'),
                'notax_avalaible' => $this->module->l('No tax available', 'addproduct'),
                'some_error' => $this->module->l('Some error occured.', 'addproduct'),
                'no_value' => $this->module->l('No Value Found', 'addproduct'),
                'choose_value' => $this->module->l('Choose a value', 'addproduct'),
                'value_missing' => $this->module->l('Feature value is missing', 'addproduct'),
                'value_length_err' => $this->module->l('Feature value is too long', 'addproduct'),
                'value_name_err' => $this->module->l('Feature value is not valid', 'addproduct'),
                'feature_err' => $this->module->l('Feature is not selected', 'addproduct'),
            );

        Media::addJsDef($jsVars);
    }

    public function addNewCustomizationField($mpProductId, $psProductId)
    {
        $customFields = Tools::getValue('custom_fields');
        if ($mpProductId && !empty($customFields)) {
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

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'addproduct'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Add product', 'addproduct'),
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

        Media::addJsDef(
            array('confirm_delete_customization' =>
                $this->module->l('Are you sure you want to delete this customization field?', 'addproduct'),
            )
        );

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

    public function displayAjaxMpSpecificPriceRule()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        $mpProductId = Tools::getValue('mp_product_id');
        $keywords = Tools::getValue('keywords');
        parse_str(Tools::getValue('dataval'), $data);
        $custSearch = Tools::getValue('cust_search');
        $specificRule = new WkMpSpecificRule();
        if ($custSearch && $keywords) {
            $specificRule->searchCustomer($custSearch);
        } elseif (!$custSearch && $data) {
            $specificRule->processPriceAddition($data);
            die;
        }
        if ($editId = Tools::getValue('editId')) {
            $specificPriceData = new SpecificPrice($editId);
        } elseif (Tools::getValue('id_delete')) {
            $specificPriceData = new SpecificPrice(Tools::getValue('id_delete'));
        }

        $idPSProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpProductId);
        if ($specificPriceData->id_product == $idPSProduct) {
            if (Tools::getValue('delete_slot')) {
                if (WkMpSellerProduct::isSameSellerProduct($mpProductId)) {
                    $specificPriceData->delete();
                    die('1');
                }
            } elseif ($editId) {
                if (Validate::isLoadedObject($specificPriceData)) {
                    if ($specificPriceData->id_customer > 0) {
                        $customer = new Customer($specificPriceData->id_customer);
                        $specificPriceData->customer_name = $customer->firstname.' '.$customer->lastname;
                        $specificPriceData->customer_email = $customer->email;
                    }
                    $this->ajaxDie(json_encode($specificPriceData));
                }
            }
        } else {
            die('fail');
        }
    }

    public function displayAjaxMpSearchProduct()
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
                            $image = _MODULE_DIR_.$this->module->name.'/views/img/home-default.jpg';
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
                        $image = _MODULE_DIR_.$this->module->name.'/views/img/home-default.jpg';
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

    public function displayAjaxMpSearchProductOnly()
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
        $excludeVirtuals = (bool)0;
        $excludePacks = (bool)0;
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
            foreach ($items as $item) {
                if (isset($item['id_image'])) {
                    $image = str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], 'home_default'));
                } else {
                    $image = _MODULE_DIR_.$this->module->name.'/views/img/home-default.jpg';
                }
                $product = array(
                    'id' => (int)($item['id_product']),
                    'name' => $item['name'],
                    'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                    'image' => $image,
                );
                array_push($results, $product);
            }
            $results = array_values($results);
        }
        echo Tools::jsonEncode($results);
    }

    public function displayAjaxAddProductAttachment()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
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

    public function displayAjaxGetSupplierReferences()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
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
            $currencies = Currency::getCurrencies();
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
}
