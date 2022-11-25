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

class MarketplaceManageCombinationModuleFrontController extends ModuleFrontController
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
                    //Assign variable to display message that permission is allowed or not of this page
                    WkMpSellerStaffPermission::assignProductTabPermission(
                        $idStaff,
                        WkMpTabList::MP_PRODUCT_TAB,
                        WkMpTabList::MP_PRODUCT_COMBINATION_TAB
                    );

                    //Check product edit permission
                    $tabPermission = WkMpTabList::getStaffPermissionWithTabName(
                        $idStaff,
                        $this->context->language->id,
                        WkMpTabList::MP_PRODUCT_TAB
                    );
                    if ($tabPermission && !$tabPermission['edit']) {
                        //Display message that staff can not edit this page
                        $this->context->smarty->assign('editProductPermissionNotAllow', 1);
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

            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active'] && Configuration::get('WK_MP_SELLER_PRODUCT_COMBINATION')) {
                $idPsProductAttribute = Tools::getValue('id_combination');

                if ($idPsProductAttribute) {
                    $idMpProduct = WkMpProductAttribute::getSellerProductIdByIdCombination($idPsProductAttribute);
                } else {
                    $idMpProduct = Tools::getValue('id');
                }

                $mpProduct = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct);
                if ($mpProduct && $seller['id_seller'] == $mpProduct['id_seller']) {
                    //Assign Data for create/update combination
                    if ($idPsProductAttribute) {
                        WkMpProductAttribute::assignCombinationCreationFormData(
                            $mpProduct,
                            $idMpProduct,
                            $idPsProductAttribute
                        );
                    } else {
                        WkMpProductAttribute::assignCombinationCreationFormData(
                            $mpProduct,
                            $idMpProduct
                        );
                    }

                    $this->context->smarty->assign(array(
                        'logic' => 3,
                        'static_token' => Tools::getToken(false),
                        'permissionData' => $permissionData,
                    ));

                    $this->defineJSVars();
                    $this->setTemplate(
                        'module:marketplace/views/templates/front/product/combination/managecombination.tpl'
                    );
                }
                //Don't add else condition otherwise attribute selection with ajax will not work
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    protected function defineJSVars()
    {
        $jsVars = array(
                'path_managecombination' => $this->context->link->getModuleLink('marketplace', 'managecombination'),
                'attribute_req' => $this->module->l('Combination attribute cannot be blank.', 'managecombination'),
                'attribute_unity_invalid' => $this->module->l('Impact on price per unit should be valid.', 'managecombination'),
                'req_attr' => $this->module->l('Attribute is not selected.', 'managecombination'),
                'req_attr_val' => $this->module->l('Value is not selected.', 'managecombination'),
                'attr_already_selected' => $this->module->l('Attribute is already selected.', 'managecombination'),
            );

        Media::addJsDef($jsVars);
    }

    public function postProcess()
    {
        if ((Tools::isSubmit('submitStayCombination') || Tools::isSubmit('submitCombination'))
        && $this->context->customer->id) {
            $idCustomer = $this->context->customer->id;
            //Override customer id if any staff of seller want to use this controller
            if (Module::isEnabled('mpsellerstaff')) {
                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $idMpProduct = (int) Tools::getValue('mp_id_product');
            $productAttributeList = Tools::getValue('attribute_combination_list');
            $idPsProductAttribute = Tools::getValue('id_combination');

            $mpProduct = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct);
            if (!$mpProduct) {
                return false;
            }

            if ($idPsProductAttribute) {
                $combinationMpProductId = WkMpProductAttribute::getSellerProductIdByIdCombination(
                    $idPsProductAttribute
                );
                if ($combinationMpProductId != $idMpProduct) {
                    return false;
                }
                $objCombination = new Combination((int) $idPsProductAttribute);
            } else {
                $objCombination = new Combination();
            }

            $idPsProduct = $mpProduct['id_ps_product'];
            $objProduct = new Product($idPsProduct);

            $mpReference = '';
            if (Configuration::get('WK_MP_SELLER_PRODUCT_REFERENCE')) {
                $mpReference = Tools::getValue('mp_reference');
            } else {
                if ($idPsProductAttribute) {
                    $mpReference = $objCombination->reference;
                }
            }

            $mpEan13 = '';
            if (Configuration::get('WK_MP_SELLER_PRODUCT_EAN')) {
                $mpEan13 = Tools::getValue('mp_ean13');
            } else {
                if ($idPsProductAttribute) {
                    $mpEan13 = $objCombination->ean13;
                }
            }

            $mpUPC = '';
            if (Configuration::get('WK_MP_SELLER_PRODUCT_UPC')) {
                $mpUPC = Tools::getValue('mp_upc');
            } else {
                if ($idPsProductAttribute) {
                    $mpUPC = $objCombination->upc;
                }
            }

            $mpISBN = '';
            if (Configuration::get('WK_MP_SELLER_PRODUCT_ISBN')) {
                $mpISBN = Tools::getValue('mp_isbn');
            } else {
                if ($idPsProductAttribute) {
                    $mpISBN = $objCombination->isbn;
                }
            }

            $mpMPN = '';
            if (Configuration::get('WK_MP_PRODUCT_MPN')) {
                $mpMPN = Tools::getValue('mp_mpn');
            } else {
                if ($idPsProductAttribute) {
                    $mpMPN = $objCombination->mpn;
                }
            }

            $mpWholesalePrice = 0;
            if (Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE')) {
                $mpWholesalePrice = Tools::getValue('mp_wholesale_price');
            } else {
                if ($idPsProductAttribute) {
                    $mpWholesalePrice = $objCombination->wholesale_price;
                }
            }

            $mpUnitPriceImpact = 0;
            if (Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT')) {
                $mpUnitPriceImpact = Tools::getValue('mp_unit_price_impact');
            } else {
                if ($idPsProductAttribute) {
                    $mpUnitPriceImpact = $objCombination->unit_price_impact;
                }
            }

            //stock location
            if (Configuration::get('WK_MP_PRODUCT_STOCK_LOCATION')) {
                $location = Tools::getValue('location');
            } else {
                $location = '';
            }

            $lowStockThreshold = 0;
            $lowStockAlert = 0;
            if (Configuration::get('WK_MP_PRODUCT_LOW_STOCK_ALERT')) {
                $lowStockThreshold = Tools::getValue('low_stock_threshold');
                if (!$lowStockThreshold) {
                    $lowStockThreshold = 0;
                }
                if (Tools::getValue('low_stock_alert')) {
                    $lowStockAlert = 1;
                } else {
                    $lowStockAlert = 0;
                }
            } else {
                if ($idPsProductAttribute) {
                    $lowStockThreshold = $objCombination->low_stock_threshold;
                    $lowStockAlert = $objCombination->low_stock_alert;
                }
            }
            if (Configuration::get('WK_MP_PRODUCT_MIN_QTY')) {
                $mpMinimalQuantity = Tools::getValue('mp_minimal_quantity');
            } else {
                if ($idPsProductAttribute) {
                    $mpMinimalQuantity = $objCombination->minimal_quantity;
                } else {
                    $mpMinimalQuantity = 1;
                }
            }
            $mpPrice = Tools::getValue('mp_price');
            $mpQuantity = Tools::getValue('mp_quantity');
            $mpWeight = Tools::getValue('mp_weight');
            $mpAvailableDate = Tools::getValue('mp_available_date');
            $idImages = Tools::getValue('id_image_attr');

            if (!$productAttributeList) {
                $this->errors[] = $this->module->l('Combination attribute cannot be blank.', 'managecombination');
            }
            if (!Validate::isInt($mpQuantity)) {
                $this->errors[] = $this->module->l('Quantity should be numeric.', 'managecombination');
            }
            if (!Validate::isUnsignedInt($mpMinimalQuantity)) {
                $this->errors[] = $this->module->l('Minimum quantity should be valid.', 'managecombination');
            }
            if (trim($lowStockThreshold) != '' && !Validate::isInt($lowStockThreshold)) {
                $this->errors[] = $this->module->l('Low stock level should be valid.', 'managecombination');
            }
            if ($mpReference && !Validate::isReference($mpReference)) {
                $this->errors[] = $this->module->l('Reference is not valid.', 'managecombination');
            }
            if ($mpEan13 && !Validate::isEan13($mpEan13)) {
                $this->errors[] = $this->module->l('EAN-13 or JAN barcode is not valid.', 'managecombination');
            }
            if ($mpUPC && !Validate::isUpc($mpUPC)) {
                $this->errors[] = $this->module->l('UPC barcode is not valid.', 'managecombination');
            }
            if ($mpISBN && !Validate::isIsbn($mpISBN)) {
                $this->errors[] = $this->module->l('ISBN code is not valid.', 'managecombination');
            }
            if ($mpMPN && !(Tools::strlen($mpMPN) <= 40)) {
                $this->errors[] =
                $this->module->l('MPN value is too long. It should have 40 character or less.', 'managecombination');
            }
            if ($mpPrice) {
                if (!Validate::isNegativePrice($mpPrice)) {
                    $this->errors[] = $this->module->l('Impact price must be valid.', 'managecombination');
                }
            } else {
                $mpPrice = 0;
            }
            if ($mpWholesalePrice) {
                if (!Validate::isPrice($mpWholesalePrice)) {
                    $this->errors[] = $this->module->l('Cost price must be valid.', 'managecombination');
                }
            } else {
                $mpWholesalePrice = 0;
            }
            if ($mpUnitPriceImpact) {
                if (!Validate::isNegativePrice($mpUnitPriceImpact)) {
                    $this->errors[] = $this->module->l('Impact on unit price must be valid.', 'managecombination');
                }
            } else {
                $mpUnitPriceImpact = 0;
            }
            if ($mpWeight) {
                if (!Validate::isFloat($mpWeight)) {
                    $this->errors[] = $this->module->l('Impact on weight must be valid.', 'managecombination');
                }
            } else {
                $mpWeight = 0.00;
            }
            if ($mpAvailableDate && !Validate::isDateFormat($mpAvailableDate)) {
                $this->errors[] = $this->module->l('Available date must be valid.', 'managecombination');
            }
            if ($productAttributeList) { //if same combination is already exist
                if ($objProduct->productAttributeExists($productAttributeList, $idPsProductAttribute)) {
                    $this->errors[] = $this->module->l('This Combination is already exists for this product.', 'managecombination');
                }
            }

            if (!count($this->errors)) {
                $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
                if ($seller && $seller['active']) {
                    if ($seller['id_seller'] == $mpProduct['id_seller']) {
                        $udpatedIdPsProductAttribute = WkMpProductAttribute::saveMpProductCombination(
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
                            $location,
                            $mpMPN
                        );
                        if ($udpatedIdPsProductAttribute) {
                            if (Configuration::get('WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE')
                            && 'managecombination' == Tools::getValue('controller')) {
                                //Deactive product if editing is not allowed
                                WkMpSellerProduct::deactivateProductAfterUpdate($idMpProduct);
                            }

                            if ($idPsProductAttribute) {
                                $combiAction = 2; //edit
                                $wkParams = array('id_combination' => $udpatedIdPsProductAttribute, 'update' => 1);
                            } else {
                                $combiAction = 1; //add
                                $wkParams = array('id_combination' => $udpatedIdPsProductAttribute, 'add' => 1);
                            }

                            //To manage staff log (changes add/update/delete)
                            WkMpHelper::setStaffHook(
                                $this->context->customer->id,
                                Tools::getValue('controller'),
                                $idMpProduct,
                                $combiAction
                            ); // 1/2 for Add/Update action

                            if (Tools::isSubmit('submitStayCombination')) {
                                Tools::redirect(
                                    $this->context->link->getModuleLink(
                                        'marketplace',
                                        'managecombination',
                                        $wkParams
                                    )
                                );
                            } else {
                                $successParams = array();
                                $successParams['id_mp_product'] = $idMpProduct;
                                $successParams['tab'] = 'wk-combination';
                                Tools::redirect(
                                    $this->context->link->getModuleLink(
                                        'marketplace',
                                        'updateproduct',
                                        $successParams
                                    )
                                );
                            }
                        }
                    } else {
                        Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                    }
                }
            }
        }
    }

    public function displayAjaxGetAttributeValue()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        //Change Attribute Value according to Attribute Group
        $attributeGroupId = Tools::getValue('attribute_group_id');

        $attributeVal = WkMpProductAttribute::getAttributeValueByGroup($attributeGroupId);
        $jsondata = Tools::jsonEncode($attributeVal);
        echo $jsondata;

        die; //ajax close
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'updateproduct'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );
        if (Tools::getValue('id_combination')) {
            $breadcrumb['links'][] = array(
                'title' => $this->module->l('Edit combination', 'managecombination'),
                'url' => '',
            );
        } else {
            $breadcrumb['links'][] = array(
                'title' => $this->module->l('Add combination', 'managecombination'),
                'url' => '',
            );
        }

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryUI('ui.datepicker');

        $this->registerStylesheet(
            'marketplace_account-css',
            'modules/'.$this->module->name.'/views/css/marketplace_account.css'
        );
        $this->registerStylesheet(
            'mp_global_style-css',
            'modules/'.$this->module->name.'/views/css/mp_global_style.css'
        );
        $this->registerJavascript(
            'mp-managecombination-js',
            'modules/'.$this->module->name.'/views/js/managecombination.js'
        );
    }
}
