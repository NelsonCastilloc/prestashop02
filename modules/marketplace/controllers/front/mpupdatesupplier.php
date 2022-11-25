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

class MarketplaceMpUpdateSupplierModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->isLogged()) {
            $wkMsgCode = Tools::getValue('msg_code');
            if ($wkMsgCode) {
                $this->context->smarty->assign('msg_code', $wkMsgCode);
            }
            $idLang = $this->context->language->id;
            $id = Tools::getValue('id');
            if ($id) {
                $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
                if ($mpSeller && $mpSeller['active']) {
                    if (!Configuration::get('WK_MP_PRODUCT_SUPPLIER')) {
                        Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                    }

                    if (file_exists(_PS_MODULE_DIR_ . 'marketplace/views/img/mpsuppliers/' . $id . '.jpg')) {
                        $supplier_image = _MODULE_DIR_ . 'marketplace/views/img/mpsuppliers/' . $id . '.jpg';
                    } else {
                        $supplier_image = _MODULE_DIR_ . 'marketplace/views/img/mpsuppliers/default_supplier.png';
                    }
                    $smartyVars = array(
                        'logic' => 'mpsupplierlist',
                        'self' => dirname(__FILE__),
                        'ps_img_dir' => _PS_IMG_ . 'l/',
                        'supplier_image' => $supplier_image,
                        'stateinfo' => State::getStates($idLang),
                        'countryinfo' => Country::getCountries($idLang),
                        'title_bg_color' => Configuration::get('WK_MP_TITLE_BG_COLOR'),
                        'title_text_color' => Configuration::get('WK_MP_TITLE_TEXT_COLOR')
                    );

                    // Set default lang at every form according to configuration multi-language
                    WkMpHelper::assignDefaultLang($mpSeller['id_seller']);
                    WkMpHelper::assignGlobalVariables(); // Assign global static variable on tpl
                    WkMpHelper::defineGlobalJSVariables(); // Define global js variable on js file

                    // get supplier details
                    $objMpSupplier = new WkMpSuppliers((int)$id);
                    $supplierInfo = $objMpSupplier->getMpSupplierAllDetails($id);
                    if ($supplierInfo) {
                        $smartyVars['supplierInfo'] = $supplierInfo;
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'mpaddsupplier'));
                    }

                    if ($supplierInfo['id_ps_supplier'] && $supplierInfo['active']) {
                        $productList = WkMpSuppliers::getProductsForUpdateSupplierBySellerIdAndPsSupplierId(
                            $mpSeller['id_seller'],
                            $supplierInfo['id_ps_supplier'],
                            $idLang
                        );
                        if ($productList) {
                            $smartyVars['productList'] = $productList;
                        }
                    }

                    $this->addCustomJSVars();
                    $this->context->smarty->assign($smartyVars);
                    $this->setTemplate('module:marketplace/views/templates/front/product/suppliers/addsupplier.tpl');
                } else {
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
                }
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'mpsupplierlist'));
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitStay_supplier') || Tools::isSubmit('submit_supplier')) {
            $id_customer = $this->context->customer->id;
            if ($id_customer) {
                $mpSeller = WkMpSeller::getSellerDetailByCustomerId($id_customer);
                if ($mpSeller && $mpSeller['active']) {
                    $sellerid = $mpSeller['id_seller'];
                    $suppname = Tools::getValue('suppname');
                    $suppphone = Tools::getValue('suppphone');
                    $suppmobile = Tools::getValue('suppmobile');
                    $suppaddress = Tools::getValue('suppaddress');
                    $suppzip = Tools::getValue('suppzip');
                    $suppcity = Tools::getValue('suppcity');
                    $suppcountry = Tools::getValue('suppcountry');
                    $suppstate = Tools::getValue('suppstate');
                    $default_lang = $mpSeller['default_lang'];
                    $selected_products = Tools::getValue('selected_products');

                    // data validation
                    if ($suppname == '') {
                        $this->errors[] = $this->module->l('Supplier name is required.', 'mpupdatesupplier');
                    } elseif (!Validate::isGenericName($suppname)) {
                        $this->errors[] = $this->module->l('Supplier name is not valid.', 'mpupdatesupplier');
                    }

                    if (!Validate::isPhoneNumber($suppphone)) {
                        $this->errors[] = $this->module->l('Phone number is invalid.', 'mpupdatesupplier');
                    }

                    if (!Validate::isPhoneNumber($suppmobile)) {
                        $this->errors[] = $this->module->l('Mobile Phone number is invalid.', 'mpupdatesupplier');
                    }

                    if (trim($suppaddress) == '') {
                        $this->errors[] = $this->module->l('Address is required.', 'mpupdatesupplier');
                    } elseif (!Validate::isAddress($suppaddress)) {
                        $this->errors[] = $this->module->l('Invalid address.', 'mpupdatesupplier');
                    }

                    if ($suppzip) {
                        if (!Validate::isPostCode($suppzip)) {
                            $this->errors[] = $this->module->l('Invaid Zip/Postal Code.', 'mpupdatesupplier');
                        }
                    }

                    if ($suppcity == '') {
                        $this->errors[] = $this->module->l('City is required.', 'mpupdatesupplier');
                    } elseif (!Validate::isCityName($suppcity)) {
                        $this->errors[] = $this->module->l('City name is invalid.', 'mpupdatesupplier');
                    }

                    if (!$suppcountry) {
                        $this->errors[] = $this->module->l('Country is required field.', 'mpupdatesupplier');
                    } elseif (Address::dniRequired($suppcountry)) {
                        if (Tools::getValue('dni') == '') {
                            $this->errors[] = $this->module->l('DNI is Required', 'mpupdatesupplier');
                        } elseif (!Validate::isDniLite('dni')) {
                            $this->errors[] = $this->module->l('Invalid DNI', 'mpupdatesupplier');
                        } elseif (Tools::strlen('dni') > 16) {
                            $this->errors[] = sprintf(
                                $this->module->l('DNI must be between 0 and %s chars.', 'mpaddsupplier'),
                                16
                            );
                        } else {
                            $addressDNI = Tools::getValue('dni');
                        }
                    }

                    if (!empty($_FILES['supplier_logo']['name'])) {
                        if ($_FILES['supplier_logo']['size'] > 0) {
                            if ($_FILES['supplier_logo']['tmp_name'] != '') {
                                if ($errorMsg = ImageManager::validateUpload($_FILES['supplier_logo'])) {
                                    $this->errors[] = $errorMsg;
                                }
                            }
                        } else {
                            $this->errors[] = $this->module->l('Invalid image size.', 'mpupdatesupplier');
                        }
                    }

                    foreach (Language::getLanguages(true) as $language) {
                        if (Tools::strlen(Tools::getValue('meta_title_'.$language['id_lang'])) > 255) {
                            $this->errors[] =
                            $this->l('Meta title must be between 0 and 255 chars.', 'mpupdatesupplier');
                        }
                        if (Tools::strlen(Tools::getValue('meta_desc_'.$language['id_lang'])) > 512) {
                            $this->errors[] =
                            $this->l('Meta description must be between 0 and 512 chars.', 'mpupdatesupplier');
                        }
            
                        if (Tools::strlen(Tools::getValue('meta_key_'.$language['id_lang'])) > 255) {
                            $this->errors[] =
                            $this->l('Meta keywords must be between 0 and 255 chars.', 'mpupdatesupplier');
                        }
                    }

                    if (!count($this->errors)) {
                        $mpIdSupplier = Tools::getValue('id');
                        if ($mpIdSupplier) {
                            $mpSupplierInfo = WkMpSuppliers::getMpSupplierAllDetails($mpIdSupplier);
                            $psIdSupplier = $mpSupplierInfo['id_ps_supplier'];
                            $psIdSupplierAddress = $mpSupplierInfo['id_ps_supplier_address'];
                            $objmpsupp = new WkMpSuppliers($mpIdSupplier); //edit supplier
                            $objpssupp = new Supplier($psIdSupplier);
                            $objpssupp->name = $suppname;
                            foreach (Language::getLanguages(true) as $language) {
                                if (Tools::getValue('description_' . $language['id_lang'])) {
                                    $objpssupp->description[$language['id_lang']] = Tools::getValue(
                                        'description_' . $language['id_lang']
                                    );
                                } else {
                                    $objpssupp->description[$language['id_lang']] = Tools::getValue(
                                        'description_' . $default_lang
                                    );
                                }

                                if (Tools::getValue('meta_title_' . $language['id_lang'])) {
                                    $objpssupp->meta_title[$language['id_lang']] = Tools::getValue(
                                        'meta_title_' . $language['id_lang']
                                    );
                                } else {
                                    $objpssupp->meta_title[$language['id_lang']] = Tools::getValue(
                                        'meta_title_' . $default_lang
                                    );
                                }

                                if (Tools::getValue('meta_desc_' . $language['id_lang'])) {
                                    $objpssupp->meta_description[$language['id_lang']] = Tools::getValue(
                                        'meta_desc_' . $language['id_lang']
                                    );
                                } else {
                                    $objpssupp->meta_description[$language['id_lang']] = Tools::getValue(
                                        'meta_desc_' . $default_lang
                                    );
                                }

                                if (Tools::getValue('meta_key_' . $language['id_lang'])) {
                                    $objpssupp->meta_keywords[$language['id_lang']] = Tools::getValue(
                                        'meta_key_' . $language['id_lang']
                                    );
                                } else {
                                    $objpssupp->meta_keywords[$language['id_lang']] = Tools::getValue(
                                        'meta_key_' . $default_lang
                                    );
                                }
                            }
                            $objpssupp->save();
                            if (!$psIdSupplier) {
                                $psIdSupplier = $objpssupp->id;
                            }
                            // ***start*** save to mp supplier
                            $objmpsupp->id_seller = (int)$sellerid;
                            $objmpsupp->id_ps_supplier = (int)$psIdSupplier;
                            $objmpsupp->id_ps_supplier_address = (int)$psIdSupplierAddress;
                            $objmpsupp->save();

                            if (!$mpIdSupplier) {
                                $mpIdSupplier = $objmpsupp->id;
                            }
                        }
                        // Upload Supplier Logo
                        if ($mpIdSupplier) {
                            if (!empty($_FILES['supplier_logo'])) {
                                $logo = $_FILES['supplier_logo'];
                                if ($logo['size'] > 0) {
                                    $logoName = $mpIdSupplier . '.jpg';
                                    $mpImageDir = _PS_MODULE_DIR_ . 'marketplace/views/img/mpsuppliers/';
                                    $uploaded = ImageManager::resize($logo['tmp_name'], $mpImageDir . $logoName, 45, 45);
                                    if ($uploaded) {
                                        $objmpsupp->uploadSupplierLogoToPs($psIdSupplier, $mpIdSupplier, $mpImageDir);
                                    }
                                }
                            }

                            // *** start *** add address of supplier in prestashop
                            if ($psIdSupplierAddress) {
                                $address = new Address($psIdSupplierAddress);
                            } else {
                                $address = new Address();
                            }
                            $address->alias = pSQL('supplier');
                            $address->lastname = pSQL('supplier');
                            // skip problem with numeric characters in supplier name
                            $address->firstname = pSQL('supplier');
                            // skip problem with numeric characters in supplier name
                            $address->address1 = pSQL($suppaddress);
                            if (isset($addressDNI)) {
                                $address->dni = pSQL($addressDNI);
                            }
                            $address->postcode = pSQL($suppzip);
                            $address->phone = pSQL($suppphone);
                            $address->phone_mobile = pSQL($suppmobile);
                            $address->id_country = (int)$suppcountry;
                            $address->id_state = (int)$suppstate;
                            $address->city = pSQL($suppcity);
                            $address->id_supplier = (int)$psIdSupplier;
                            $address->save();
                            $psIdSupplierAddress = $address->id;
                            if ($psIdSupplierAddress) {
                                //Update supplier table with psIdManuf
                                $objmpsupp->updateMpSupplierDetails($psIdSupplier, $mpIdSupplier, $psIdSupplierAddress);
                            }
                            // *** end *** add address of supplier in prestashop

                            WkMpSuppliers::updateSupplierProducts(
                                $mpIdSupplier,
                                $psIdSupplier,
                                $selected_products
                            );
                        }
                        $param = array();
                        if ($mpIdSupplier) {
                            $param['msg_code'] = 2;
                        } else {
                            $param['msg_code'] = 3;
                        }

                        if (Tools::isSubmit('submitStay_supplier')) {
                            $param['id'] = $mpIdSupplier;
                            Tools::redirect(Context::getContext()->link->getModuleLink('marketplace', 'mpupdatesupplier', $param));
                        } else {
                            Tools::redirect(Context::getContext()->link->getModuleLink('marketplace', 'mpsupplierlist', $param));
                        }
                    }
                } else {
                    $this->errors[] = $this->module->l('You don\'t have permission to add supplier.', 'mpupdatesupplier');
                }
            } else {
                Tools::redirect($this->context->link->getPageLink('my-account'));
            }
        }
    }

    public function addCustomJSVars()
    {
        $jsVars = array(
            'supplier_ajax_link' => $this->context->link->getModuleLink('marketplace', 'mpsupplierlist'),
            'req_suppname' => $this->module->l('Supplier name is required', 'mpupdatesupplier'),
            'inv_suppname' => $this->module->l('Invalid supplier name', 'mpupdatesupplier'),
            'inv_suppphone' => $this->module->l('Invalid phone number', 'mpupdatesupplier'),
            'inv_suppmobile' => $this->module->l('Invalid mobile phone number', 'mpupdatesupplier'),
            'req_suppaddress' => $this->module->l('Address is required', 'mpupdatesupplier'),
            'inv_suppaddress' => $this->module->l('Invalid address', 'mpupdatesupplier'),
            'req_suppzip' => $this->module->l('Zip/Postal code is required', 'mpupdatesupplier'),
            'inv_suppzip' => $this->module->l('Invalid zip/postal Code', 'mpupdatesupplier'),
            'req_suppcity' => $this->module->l('City is required', 'mpupdatesupplier'),
            'inv_suppcity' => $this->module->l('City name is invalid', 'mpupdatesupplier'),
            'allow_tagify' => 1,
            'addkeywords' => $this->module->l('Add keywords', 'mpupdatesupplier'),
            'languages' => Language::getLanguages(),
            'inv_language_title' => $this->module->l('Invalid meta title', 'mpupdatesupplier'),
            'inv_language_desc' => $this->module->l('Invalid meta description', 'mpupdatesupplier'),
            'invalid_logo' => $this->module->l('Invalid image extensions, only jpg, jpeg and png are allowed.', 'mpupdatesupplier'),
            'static_token' => Tools::getToken(false),
        );

        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'mpupdatesupplier'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Update Supplier', 'mpupdatesupplier'),
            'url' => ''
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addJqueryPlugin('tagify');
        $this->registerStylesheet(
            'mp-marketplace_account',
            'modules/marketplace/views/css/marketplace_account.css'
        );
        $this->registerStylesheet(
            'mp_global_style-css',
            'modules/marketplace/views/css/mp_global_style.css'
        );

        $this->registerJavascript(
            'supplier_form_validation',
            'modules/' . $this->module->name . '/views/js/suppliers/supplier_form_validation.js'
        );

        $this->registerStylesheet(
            'tagify-css',
            'modules/' . $this->module->name . '/views/css/addmanufacturer.css'
        );
    }
}
