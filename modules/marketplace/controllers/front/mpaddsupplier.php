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

class MarketplaceMpAddSupplierModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->isLogged()) {
            $idLang = $this->context->language->id;
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller && $mpSeller['active']) {
                if (!Configuration::get('WK_MP_PRODUCT_SUPPLIER')) {
                    Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                }

                // Set default lang at every form according to configuration multi-language
                WkMpHelper::assignDefaultLang($mpSeller['id_seller']);
                WkMpHelper::assignGlobalVariables(); // Assign global static variable on tpl
                WkMpHelper::defineGlobalJSVariables(); // Define global js variable on js file

                $this->context->smarty->assign(
                    array(
                        'logic' => 'mpsupplierlist',
                        'self'=> dirname(__FILE__),
                        'ps_img_dir' => _PS_IMG_.'l/',
                        'stateinfo' => State::getStates($idLang),
                        'countryinfo' => Country::getCountries($idLang),
                        'title_bg_color' => Configuration::get('WK_MP_TITLE_BG_COLOR'),
                        'title_text_color' => Configuration::get('WK_MP_TITLE_TEXT_COLOR')
                    )
                );

                $this->addCustomJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/product/suppliers/addsupplier.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
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

                    // data validation
                    if ($suppname == '') {
                        $this->errors[] = $this->module->l('Supplier name is required.', 'mpaddsupplier');
                    } elseif (!Validate::isGenericName($suppname)) {
                        $this->errors[] = $this->module->l('Supplier name is not valid.', 'mpaddsupplier');
                    }

                    if (!Validate::isPhoneNumber($suppphone)) {
                        $this->errors[] = $this->module->l('Phone number is invalid.', 'mpaddsupplier');
                    }

                    if (!Validate::isPhoneNumber($suppmobile)) {
                        $this->errors[] = $this->module->l('Mobile phone number is invalid.', 'mpaddsupplier');
                    }

                    if (trim($suppaddress) == '') {
                        $this->errors[] = $this->module->l('Address is required.', 'mpaddsupplier');
                    } elseif (!Validate::isAddress($suppaddress)) {
                        $this->errors[] = $this->module->l('Invalid address.', 'mpaddsupplier');
                    }

                    if ($suppzip) {
                        if (!Validate::isPostCode($suppzip)) {
                            $this->errors[] = $this->module->l('Invaid Zip/Postal Code.', 'mpaddsupplier');
                        }
                    }

                    if ($suppcity == '') {
                        $this->errors[] = $this->module->l('City is required.', 'mpaddsupplier');
                    } elseif (!Validate::isCityName($suppcity)) {
                        $this->errors[] = $this->module->l('City name is invalid.', 'mpaddsupplier');
                    }

                    if (!$suppcountry) {
                        $this->errors[] = $this->module->l('Country is required field.', 'mpaddsupplier');
                    } elseif (Address::dniRequired($suppcountry)) {
                        if (Tools::getValue('dni') == '') {
                            $this->errors[] = $this->module->l('DNI is required', 'mpaddsupplier');
                        } elseif (!Validate::isDniLite('dni')) {
                            $this->errors[] = $this->module->l('Invalid DNI', 'mpaddsupplier');
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
                            $this->errors[] = $this->module->l('Invalid image size.', 'mpaddsupplier');
                        }
                    }

                    foreach (Language::getLanguages(true) as $language) {
                        if (Tools::strlen(Tools::getValue('meta_title_'.$language['id_lang'])) > 255) {
                            $this->errors[] =
                            $this->l('Meta title must be between 0 and 255 chars.', 'mpaddsupplier');
                        }
                        if (Tools::strlen(Tools::getValue('meta_desc_'.$language['id_lang'])) > 512) {
                            $this->errors[] =
                            $this->l('Meta description must be between 0 and 512 chars.', 'mpaddsupplier');
                        }
            
                        if (Tools::strlen(Tools::getValue('meta_key_'.$language['id_lang'])) > 255) {
                            $this->errors[] =
                            $this->l('Meta keywords must be between 0 and 255 chars.', 'mpaddsupplier');
                        }
                    }

                    if (!count($this->errors)) {
                        $objmpsupp = new WkMpSuppliers(); //add manufacturer
                        $objpssupp = new Supplier();
                        if (Configuration::get('WK_MP_PRODUCT_SUPPLIER_APPROVED')) {
                            $objpssupp->active = 0; //need to approved by admin
                        } else {
                            $objpssupp->active = 1; //automatically approved
                        }
                        $psIdSupplier = $psIdSupplierAddress = $mpIdSupplier = 0;
                        $objpssupp->name = $suppname;
                        foreach (Language::getLanguages(true) as $language) {
                            if (Tools::getValue('description_'.$language['id_lang'])) {
                                $objpssupp->description[$language['id_lang']] = Tools::getValue(
                                    'description_'.$language['id_lang']
                                );
                            } else {
                                $objpssupp->description[$language['id_lang']] = Tools::getValue(
                                    'description_'.$default_lang
                                );
                            }

                            if (Tools::getValue('meta_title_'.$language['id_lang'])) {
                                $objpssupp->meta_title[$language['id_lang']] = Tools::getValue(
                                    'meta_title_'.$language['id_lang']
                                );
                            } else {
                                $objpssupp->meta_title[$language['id_lang']] = Tools::getValue(
                                    'meta_title_'.$default_lang
                                );
                            }

                            if (Tools::getValue('meta_desc_'.$language['id_lang'])) {
                                $objpssupp->meta_description[$language['id_lang']] = Tools::getValue(
                                    'meta_desc_'.$language['id_lang']
                                );
                            } else {
                                $objpssupp->meta_description[$language['id_lang']] = Tools::getValue(
                                    'meta_desc_'.$default_lang
                                );
                            }

                            if (Tools::getValue('meta_key_'.$language['id_lang'])) {
                                $objpssupp->meta_keywords[$language['id_lang']] = Tools::getValue(
                                    'meta_key_'.$language['id_lang']
                                );
                            } else {
                                $objpssupp->meta_keywords[$language['id_lang']] = Tools::getValue(
                                    'meta_key_'.$default_lang
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
                        }

                        $business_email = $mpSeller['business_email'];
                        $seller_name = $mpSeller['seller_firstname'].' '.$mpSeller['seller_lastname'];
                        $id_lang = $this->context->language->id;

                        if ($mpIdSupplier && $objpssupp->active) {
                            // mail to seller for supplier activated
                            if ($business_email && $psIdSupplier) {
                                $temp_path = _PS_MODULE_DIR_.'marketplace/mails/';
                                $templateVars = array(
                                    '{seller_name}' => $seller_name,
                                    '{supplier_name}' => $suppname,
                                );
                                Mail::Send(
                                    $id_lang,
                                    'supplier_active',
                                    Mail::l('Supplier Active', $id_lang),
                                    $templateVars,
                                    $business_email,
                                    null,
                                    null,
                                    'Marketplace Supplier',
                                    null,
                                    null,
                                    $temp_path,
                                    false,
                                    null,
                                    null
                                );
                            }
                        }
                        // mail to admin for supplier request
                        $admin_email = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
                        if ($admin_email && $business_email) {
                            $temp_path = _PS_MODULE_DIR_.'marketplace/mails/';
                            $templateVars = array(
                                '{seller_name}' => $seller_name,
                                '{supplier_name}' => $suppname,
                                '{seller_email}' => $business_email,
                            );
                            Mail::Send(
                                $id_lang,
                                'supplier_request',
                                Mail::l('Supplier Request', $id_lang),
                                $templateVars,
                                $admin_email,
                                null,
                                null,
                                'Marketplace Supplier',
                                null,
                                null,
                                $temp_path,
                                false,
                                null,
                                null
                            );
                        }

                        $param = array();
                        if ($mpIdSupplier) {
                            $param['msg_code'] = 1;
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
                    Tools::redirect($this->context->link->getPageLink('my-account'));
                }
            }
        }
    }

    public function addCustomJSVars()
    {
        $jsVars = array(
            'supplier_ajax_link' => $this->context->link->getModuleLink('marketplace', 'mpsupplierlist'),
            'req_suppname' => $this->module->l('Supplier name is required', 'mpaddsupplier'),
            'inv_suppname' => $this->module->l('Invalid supplier name', 'mpaddsupplier'),
            'inv_suppphone' => $this->module->l('Invalid phone number', 'mpaddsupplier'),
            'inv_suppmobile' => $this->module->l('Invalid mobile phone number', 'mpaddsupplier'),
            'req_suppaddress' => $this->module->l('Address is required', 'mpaddsupplier'),
            'inv_suppaddress' => $this->module->l('Invalid address', 'mpaddsupplier'),
            'req_suppzip' => $this->module->l('Zip/Postal Code is required', 'mpaddsupplier'),
            'allow_tagify' => 1,
            'inv_suppzip' => $this->module->l('Invalid zip/postal Code', 'mpaddsupplier'),
            'req_suppcity' => $this->module->l('City is required', 'mpaddsupplier'),
            'inv_suppcity' => $this->module->l('City name is invalid', 'mpaddsupplier'),
            'languages' => Language::getLanguages(),
            'addkeywords' => $this->l('Add keywords'),
            'inv_language_title' => $this->l('Invalid meta title'),
            'inv_language_desc' => $this->l('Invalid meta description'),
            'invalid_logo' => $this->l('Invalid image extensions, only jpg, jpeg and png are allowed.'),
            'static_token' => Tools::getToken(false),
        );

        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'mpaddsupplier'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Add supplier', 'mpaddsupplier'),
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
            'modules/'.$this->module->name.'/views/js/suppliers/supplier_form_validation.js'
        );
        $this->registerStylesheet(
            'tagify-css',
            'modules/'.$this->module->name.'/views/css/addmanufacturer.css'
        );
    }
}
