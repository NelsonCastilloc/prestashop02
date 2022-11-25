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

class MarketplaceMpCreateManufacturersModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->id) {
            $idLang = $this->context->language->id;
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller && $mpSeller['active']) {
                if (!Configuration::get('WK_MP_PRODUCT_MANUFACTURER')) {
                    Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                }

                $mpSellerId = $mpSeller['id_seller'];
                $brandStatus = 0;
                if (Tools::getValue('id')) { //edit page
                    $manufId = Tools::getValue('id');

                    $objMpManuf = new WkMpManufacturers($manufId);
                    if ($mpSellerId == $objMpManuf->id_seller) {
                        $manufacInfo = WkMpManufacturers::getMpManufacturerAllDetails($manufId);
                        if ($manufacInfo['active']) {
                            $brandStatus = $manufacInfo['active'];
                        }

                        $imageexist = _PS_MODULE_DIR_.'marketplace/views/img/mpmanufacturers/'.$manufId.'.jpg';
                        if (file_exists($imageexist)) {
                            $this->context->smarty->assign('imageexist', $imageexist);
                        }
                        $this->context->smarty->assign('manufId', $manufId);
                        $this->context->smarty->assign('manufacInfo', $manufacInfo);
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'dashboard'));
                    }
                }
                
                if ($brandStatus) {
                    $objMpManfProduct = new WkMpManufacturers();
                    $productList = $objMpManfProduct->getProductsForAddManufacturerBySellerId($mpSellerId, $idLang);
                    $assignedProductList = $objMpManfProduct->getSellerProductAssigned($mpSellerId);
                    if ($assignedProductList && $productList) {
                        foreach ($productList as &$plist) {
                            foreach ($assignedProductList as $assignedProduct) {
                                if ($plist['id_mp_product'] == $assignedProduct['id_mp_product']) {
                                    $plist['assigned'] = true;
                                }
                            }
                        }
                    }
                    if ($productList) {
                        $this->context->smarty->assign('productList', $productList);
                    }
                }

                // Set default lang at every form according to configuration multi-language
                WkMpHelper::assignDefaultLang($mpSellerId);
                WkMpHelper::assignGlobalVariables(); // Assign global static variable on tpl
                WkMpHelper::defineGlobalJSVariables(); // Define global js variable on js file

                $this->context->smarty->assign(
                    array(
                        'logic' => 'mpmanufacturerlist',
                        'stateinfo' => State::getStates($idLang),
                        'countryinfo' => Country::getCountries($idLang)
                    )
                );

                $this->setTemplate(
                    'module:marketplace/views/templates/front/product/manufacturers/createmanufacturers.tpl'
                );
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect('index.php?controller=authentication&back='.
            urlencode($this->context->link->getModuleLink('marketplace', 'mpcreatemanufacturers')));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit_manufacturer') || Tools::isSubmit('submitStay_manufacturer')) {
            $link = new Link();
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            $idSeller = $mpSeller['id_seller'];
            $name = trim(Tools::getValue('manuf_name'));
            $phone = trim(Tools::getValue('manuf_phone'));
            $address = Tools::getValue('manuf_address');
            $zipcode = Tools::getValue('manuf_zipcode');
            $city = Tools::getValue('manuf_city');
            $country = Tools::getValue('manuf_country');
            $state = Tools::getValue('manuf_state');
            $selectedProducts = Tools::getValue('selected_products');
            $defaultLang = WkMpManufacturers::getCurrentLang($idSeller);

            // Data Validations
            if (!$name) {
                $this->errors[] = $this->module->l('Brand name is required field.', 'mpcreatemanufacturers');
            } elseif (!Validate::isCatalogName($name)) {
                $this->errors[] = $this->module->l('Invalid brand name.', 'mpcreatemanufacturers');
            }
            if ($phone) {
                if (!Validate::isPhoneNumber($phone)) {
                    $this->errors[] = $this->module->l('Invalid phone number.', 'mpcreatemanufacturers');
                }
            }
            // Check fields sizes
            $className = 'WkMpManufacturers';
            $rules = call_user_func(array($className, 'getValidationRules'), $className);
            foreach (Language::getLanguages() as $language) {
                $languageName = '';
                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                    $languageName = '('.$language['name'].')';
                }

                if (Tools::getValue('short_description_'.$language['id_lang'])) {
                    $shortDesc = Tools::getValue('short_description_'.$language['id_lang']);
                    $limit = (int) Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');
                    if ($limit <= 0) {
                        $limit = 400;
                    }
                    if (!Validate::isCleanHtml($shortDesc)) {
                        $this->errors[] = sprintf(
                            $this->module->l('Short description field %s is invalid', $className),
                            $languageName
                        );
                    } elseif (Tools::strlen(strip_tags($shortDesc)) > $limit) {
                        $this->errors[] = sprintf(
                            $this->module->l('Short description field %s is too long: (%d chars max).', $className),
                            $languageName,
                            $limit
                        );
                    }
                }
                if (Tools::getValue('description_'.$language['id_lang'])) {
                    if (!Validate::isCleanHtml(
                        Tools::getValue('description_'.$language['id_lang']),
                        (int) Configuration::get('PS_ALLOW_HTML_IFRAME')
                    )) {
                        $this->errors[] = sprintf(
                            $this->module->l('Product description field %s is invalid', $className),
                            $languageName
                        );
                    }
                }
                if (Tools::getValue('meta_title_'.$language['id_lang'])) {
                    if (!Validate::isGenericName(Tools::getValue('meta_title_'.$language['id_lang']))) {
                        $this->errors[] = sprintf(
                            $this->module->l('Meta title field %s is invalid', $className),
                            $languageName
                        );
                    } elseif (Tools::strlen(Tools::getValue('meta_title_'.$language['id_lang'])) > 128) {
                        $this->errors[] = sprintf(
                            $this->module->l('Meta title field is too long (%2$d chars max).', $className),
                            128
                        );
                    }
                }
                if (Tools::getValue('meta_desc_'.$language['id_lang'])) {
                    if (!Validate::isGenericName(Tools::getValue('meta_desc_'.$language['id_lang']))) {
                        $this->errors[] = sprintf(
                            $this->module->l('Meta description field %s is invalid', $className),
                            $languageName
                        );
                    } elseif (Tools::strlen(Tools::getValue('meta_desc_'.$language['id_lang'])) > 255) {
                        $this->errors[] = sprintf(
                            $this->module->l('Meta description field is too long (%2$d chars max).', $className),
                            call_user_func(array($className, 'displayFieldName'), $className),
                            255
                        );
                    }
                }
                if (Tools::getValue('meta_key_'.$language['id_lang'])) {
                    if (!Validate::isGenericName(Tools::getValue('meta_key_'.$language['id_lang']))) {
                        $this->errors[] = sprintf(
                            $this->module->l('Meta key field %s is invalid', $className),
                            $languageName
                        );
                    }
                }
            }

            if (!trim($address)) {
                $this->errors[] = $this->module->l('Address is required field.', 'mpcreatemanufacturers');
            }
            if (!trim($city)) {
                $this->errors[] = $this->module->l('City is required field.', 'mpcreatemanufacturers');
            }
            if (!$country) {
                $this->errors[] = $this->module->l('Country is required field.', 'mpcreatemanufacturers');
            } elseif (Address::dniRequired($country)) {
                if (Tools::getValue('dni') == '') {
                    $this->errors[] = $this->module->l('DNI is Required', 'mpcreatemanufacturers');
                } elseif (!Validate::isDniLite('dni')) {
                    $this->errors[] = $this->module->l('Invalid DNI', 'mpcreatemanufacturers');
                } else {
                    $addressDNI = Tools::getValue('dni');
                }
            }
            
            if (!empty($_FILES['manuf_logo']['name'])
            && $_FILES['manuf_logo']['size'] > 0
            && $_FILES['manuf_logo']['tmp_name'] != '') {
                if ($errorMsg = ImageManager::validateUpload($_FILES['manuf_logo'])) {
                    $this->errors[] = $errorMsg;
                }
            }

            if (!count($this->errors)) {
                $mpManufId = Tools::getValue('manuf_id');

                // Save in marketplace_manufactuerer table
                if ($mpManufId) {
                    $mpManufacInfo = WkMpManufacturers::getMpManufacturerAllDetails($mpManufId);
                    $psIdManuf = $mpManufacInfo['id_ps_manuf'];
                    $psIdManufAddress = $mpManufacInfo['id_ps_manuf_address'];
                    $objMpManufacturer = new WkMpManufacturers($mpManufId); //edit manufacturer
                    $objPsManufacturer = new Manufacturer($psIdManuf);
                    $updatemanuf = 1;
                } else {
                    $objMpManufacturer = new WkMpManufacturers(); //add manufacturer
                    $objPsManufacturer = new Manufacturer();
                    if (Configuration::get('WK_MP_PRODUCT_MANUFACTURER_APPROVED')) {
                        $objPsManufacturer->active = 0; //need to approved by admin
                    } else {
                        $objPsManufacturer->active = 1; //automatically approved
                    }
                    $psIdManuf = $psIdManufAddress = 0;
                    $updatemanuf = 0;
                }

                foreach (Language::getLanguages(false) as $language) {
                    $shortdescLangId = $language['id_lang'];
                    $descLangId = $language['id_lang'];
                    $metaTitleLangId = $language['id_lang'];
                    $metaDescLangId = $language['id_lang'];
                    $metaKeyLangId = $language['id_lang'];

                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                        //if manufacturer name in other language is not available then fill with seller language same for others
                        if (!Tools::getValue('short_description_'.$language['id_lang'])) {
                            $shortdescLangId = $defaultLang;
                        }
                        if (!Tools::getValue('description_'.$language['id_lang'])) {
                            $descLangId = $defaultLang;
                        }
                        if (!Tools::getValue('meta_title_'.$language['id_lang'])) {
                            $metaTitleLangId = $defaultLang;
                        }
                        if (!Tools::getValue('meta_desc_'.$language['id_lang'])) {
                            $metaDescLangId = $defaultLang;
                        }
                        if (!Tools::getValue('meta_key_'.$language['id_lang'])) {
                            $metaKeyLangId = $defaultLang;
                        }
                    } else {
                        //if multilang is OFF then all fields will be filled as default lang content
                        $shortdescLangId = $defaultLang;
                        $descLangId = $defaultLang;
                        $metaTitleLangId = $defaultLang;
                        $metaDescLangId = $defaultLang;
                        $metaKeyLangId = $defaultLang;
                    }
                    $objPsManufacturer->short_description[$language['id_lang']] = Tools::getValue(
                        'short_description_'.$shortdescLangId
                    );
                    $objPsManufacturer->description[$language['id_lang']] = Tools::getValue(
                        'description_'.$descLangId
                    );
                    $objPsManufacturer->meta_title[$language['id_lang']] = Tools::getValue(
                        'meta_title_'.$metaTitleLangId
                    );
                    $objPsManufacturer->meta_description[$language['id_lang']] = Tools::getValue(
                        'meta_desc_'.$metaDescLangId
                    );
                    $objPsManufacturer->meta_keywords[$language['id_lang']] = Tools::getValue(
                        'meta_key_'.$metaKeyLangId
                    );
                }

                $objPsManufacturer->name = pSQL($name);
                $objPsManufacturer->link_rewrite = Tools::link_rewrite($name);
                $objPsManufacturer->save();
                if (!$psIdManuf) {
                    $psIdManuf = $objPsManufacturer->id;
                }
                $objMpManufacturer->id_seller = (int)$idSeller;
                $objMpManufacturer->id_ps_manuf = (int)$psIdManuf;
                $objMpManufacturer->id_ps_manuf_address = (int)$psIdManufAddress;
                $objMpManufacturer->save();

                if (!$mpManufId) {
                    $mpManufId = $objMpManufacturer->id;
                }

                // Upload Manufacturer Logo
                if ($mpManufId) {
                    if (!empty($_FILES['manuf_logo'])) {
                        $logo = $_FILES['manuf_logo'];
                        if ($logo['size'] > 0) {
                            $logoName = $mpManufId.'.jpg';
                            $mpImageDir = _PS_MODULE_DIR_.'marketplace/views/img/mpmanufacturers/';
                            $uploaded = ImageManager::resize($logo['tmp_name'], $mpImageDir.$logoName, 45, 45);
                            if ($uploaded) {
                                $objMpManufacturer->uploadManufacturerLogoToPs($psIdManuf, $mpManufId, $mpImageDir);
                            }
                        }
                    }

                    //Manufacturer Address entry
                    if ($psIdManufAddress) {
                        $objAddress = new Address($psIdManufAddress);
                    } else {
                        $objAddress = new Address();
                    }
                    $objAddress->id_country = (int)$country;
                    $objAddress->id_state = (int)$state;
                    $objAddress->id_manufacturer = (int)$psIdManuf;
                    $objAddress->alias = pSQL('manufacturer');
                    $objAddress->lastname = pSQL('manufacturer');
                    $objAddress->firstname = pSQL('manufacturer');

                    $objAddress->address1 = pSQL($address);
                    if (isset($addressDNI)) {
                        $objAddress->dni = pSQL($addressDNI);
                    }
                    $objAddress->postcode = pSQL($zipcode);
                    $objAddress->city = pSQL($city);
                    $objAddress->phone_mobile = pSQL($phone);
                    $objAddress->save();
                    $idPsManufAddress = $objAddress->id;
                    if ($idPsManufAddress) {
                        //Update manufacturer table with psIdManuf
                        $objMpManufacturer->updateMpManufacturerDetails($psIdManuf, $mpManufId, $idPsManufAddress);
                    }

                    if ($selectedProducts) {
                        foreach ($selectedProducts as $mpProductId) {
                            // Save in marketplace_product_manufactuerer table
                            $mpProductDetails = WkMpSellerProduct::getSellerProductByIdProduct($mpProductId);
                            if ($mpProductDetails) {
                                $objMpProductManufacturers = new WkMpManufacturers();
                                $mpProductManufIsAvail = $objMpProductManufacturers->getSellerProductByMpProductId(
                                    $mpProductId
                                );

                                if (empty($mpProductManufIsAvail)) {
                                    WkMpManufacturers::updatePsProductManufIdByPsProductId(
                                        $mpProductDetails['id_ps_product'],
                                        $psIdManuf
                                    );

                                    unset($objMpProductManufacturers);
                                }
                            }
                        }
                    }
                }
                if (!$updatemanuf) {
                    //Send mail to admin for manufacturer request
                    WkMpManufacturers::sendManufacturerRequestMail($idSeller, $name);
                }

                if ($updatemanuf) {
                    $successParams = array('updatemanuf' => 1);
                } else {
                    $successParams = array('createmanuf' => 1);
                }

                if (Tools::isSubmit('submitStay_manufacturer')) {
                    $successParams['id'] = $mpManufId;
                    Tools::redirect($link->getModuleLink('marketplace', 'mpcreatemanufacturers', $successParams));
                } else {
                    Tools::redirect($link->getModuleLink('marketplace', 'mpmanufacturerlist', $successParams));
                }
            }
        }
    }

    public function displayAjaxGetStateByCountry()
    {
        if (Tools::getValue('fun') == 'get_state') {
            $result = array();
            $result['status'] = 'fail';
            $countryId = Tools::getValue('countryid');
            $states = State::getStatesByIdCountry($countryId);
            if ($states) {
                $result['status'] = 'success';
                $result['info'] = $states;
            }
            $result = Tools::jsonEncode($result);
            echo $result;
        }

        die; //ajax close
    }

    public function displayAjaxDniRequired()
    {
        if ($id_country = Tools::getValue('id_country')) {
            $resp = Address::dniRequired($id_country);
            die($resp);
        }

        die; //ajax close
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->module->l('Marketplace', 'mpcreatemanufacturers'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        ];

        if (Tools::getValue('id')) {
            $title = $this->module->l('Update Brand', 'mpcreatemanufacturers');
        } else {
            $title = $this->module->l('Create Brand', 'mpcreatemanufacturers');
        }

        $breadcrumb['links'][] = [
            'title' => $title,
            'url' => '',
        ];

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $jsDef = array(
            'manuf_ajax_link' => $this->context->link->getModuleLink('marketplace', 'mpcreatemanufacturers'),
            'mp_tinymce_path' => _MODULE_DIR_.'marketplace/libs',
            'iso' => $this->context->language->iso_code,
            'languages' => Language::getLanguages(),
            'allow_tagify' => 1,
            'addkeywords' => $this->module->l('Add keywords', 'mpcreatemanufacturers'),
            'req_manuf_name' => $this->module->l('Brand name is required.', 'mpcreatemanufacturers'),
            'req_manuf_address' => $this->module->l('Address is required.', 'mpcreatemanufacturers'),
            'req_manuf_city' => $this->module->l('City is required.', 'mpcreatemanufacturers'),
            'req_manuf_country' => $this->module->l('Country is required.', 'mpcreatemanufacturers'),
            'invalid_logo' => $this->module->l('Invalid image extensions, only jpg, jpeg and png are allowed.', 'mpcreatemanufacturers'),
            'invalid_city' => $this->module->l('City is not valid', 'mpcreatemanufacturers'),
            'invalid_zipcode' => $this->module->l('Zipcode is not valid', 'mpcreatemanufacturers'),
            'length_exceeds_address' => $this->module->l('Length must be smaller than 128 character', 'mpcreatemanufacturers'),
            'invalid_address' => $this->module->l('Address is not valid', 'mpcreatemanufacturers'),
            'invalid_tag' => $this->module->l('Tag is not valid', 'mpcreatemanufacturers')
        );

        Media::addJsDef($jsDef);
        $this->addJqueryPlugin('tagify');
        $this->registerStylesheet(
            'marketplace_account',
            'modules/'.$this->module->name.'/views/css/marketplace_account.css'
        );
        $this->registerStylesheet(
            'mp_global_style',
            'modules/'.$this->module->name.'/views/css/mp_global_style.css'
        );
        $this->registerStylesheet(
            'addmanufacturer-css',
            'modules/'.$this->module->name.'/views/css/addmanufacturer.css'
        );
        $this->registerJavascript(
            'createmanufac-js',
            'modules/'.$this->module->name.'/views/js/manufacturers/mpcreatemanufacturer.js'
        );
    }
}
