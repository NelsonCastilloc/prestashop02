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

class AdminManufacturerDetailController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'wk_mp_manufacturers';
        $this->className = 'WkMpManufacturers';
        $this->identifier = 'id_wk_mp_manufacturers';

        parent::__construct();

        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller` mpsi ON (a.`id_seller` = mpsi.id_seller)';
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (a.`id_ps_manuf` = m.id_manufacturer)'.
        Shop::addSqlAssociation('manufacturer', 'm');
        $this->_select .= "CONCAT(mpsi.`seller_firstname`, ' ', mpsi.`seller_lastname`)
        as `seller_name`, mpsi.`shop_name_unique`, m.`name`,  m.`active`,
        a.`id_wk_mp_manufacturers` as `id_seller_manuf`";

        if (!Shop::isFeatureActive() || Shop::getContext() !== Shop::CONTEXT_SHOP) {
            //In case of All Shops
            $this->_select .= ',shp.`name` as wk_ps_shop_name';
            $this->_join .= 'JOIN `'._DB_PREFIX_.'shop` shp ON (shp.`id_shop` = manufacturer_shop.`id_shop`)';
        }

        $this->fields_list = array(
           'id_wk_mp_manufacturers' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'id_ps_manuf' => array(
                'title' => $this->l('Prestashop brand ID'),
                'align' => 'center',
                'havingFilter' => true,
                'callback' => 'callPsManufId',
            ),
            'id_seller_manuf' => array(
                'title' => $this->l('Logo'),
                'callback' => 'displayBrandImage',
                'search' => false,
                'havingFilter' => true,
            ),
            'name' => array(
                'title' => $this->l('Brand name'),
                'havingFilter' => true,
            ),
            'products_quantity' => array(
                'title' => $this->l('Products'),
                'align' => 'center',
                'search' => false,
                'orderby' => false,
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
                'filter_key' => 'm!active'
            ),
        );

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            if (!Shop::isFeatureActive() || Shop::getContext() !== Shop::CONTEXT_SHOP) {
                //In case of All Shops
                $this->fields_list['wk_ps_shop_name'] = array(
                    'title' => $this->l('Shop'),
                    'havingFilter' => true,
                    'orderby' => false,
                );
            }
        }

        $this->addRowAction('edit');
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
            'text' => $this->l('Delete selected'),
            'confirm' => $this->l('Delete selected items?'),
            'icon' => 'icon-trash', ),
        );
    }

    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

        $nb_items = count($this->_list);
        for ($i = 0; $i < $nb_items; ++$i) {
            $item = &$this->_list[$i];
            $query = new DbQuery();
            $query->select('COUNT(p.id_product) as products_quantity');
            $query->from('product', 'p');
            $query->where('p.id_manufacturer ='.(int) $item['id_ps_manuf']);
            $query->orderBy('products_quantity DESC');
            $item['products_quantity'] = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
            unset($query);
        }
    }

    public function displayBrandImage($idMpManuf, $rowData)
    {
        $imageLink = _MODULE_DIR_.'marketplace/views/img/mpmanufacturers/default_img.png';
        if ($idMpManuf) {
            $manufImg = 'marketplace/views/img/mpmanufacturers/'.$idMpManuf.'.jpg';
            if (file_exists(_PS_MODULE_DIR_.$manufImg)) {
                $imageLink = _MODULE_DIR_.$manufImg;
            }
        }
        return '<img class="img-thumbnail" width="45" height="45" src="'.$imageLink.'">';
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $mpSellers = WkMpSeller::getAllSeller();
        if ($mpSellers) {
            $this->page_header_toolbar_btn['new'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new brand'),
            );
        } else {
            unset($this->toolbar_btn['new']);
        }
    }

    public function callPsManufId($value)
    {
        return ($value == 0 ? $this->l('-') : $this->l($value));
    }

    public function renderForm()
    {
        if ((($this->display == 'edit') &&  (Shop::getContext() != Shop::CONTEXT_SHOP))
            || ($this->display == 'add' && (Shop::getContext() != Shop::CONTEXT_SHOP))) {
            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/manufacturer_detail/_partials/shop_warning.tpl'
            );
        }

        $objMpManuf = new WkMpManufacturers();
        $brandStatus = 0;
        if ($this->display == 'add') {
            $customerInfo = WkMpSeller::getAllSeller();
            if ($customerInfo) {
                $this->context->smarty->assign('customer_info', $customerInfo);

                //get first seller from the list
                $firstSellerDetails = $customerInfo[0];
                $mpIdSeller = $firstSellerDetails['id_seller'];
            } else {
                $mpIdSeller = 0;
            }
        } elseif ($this->display == 'edit') {
            if (Tools::getValue('id_wk_mp_manufacturers')) {
                $manufId = Tools::getValue('id_wk_mp_manufacturers');
                $manufacInfo = WkMpManufacturers::getMpManufacturerAllDetails($manufId);
                if ($manufacInfo) {
                    $mpIdSeller = $manufacInfo['id_seller'];
                }

                if ($manufacInfo['active']) {
                    $brandStatus = $manufacInfo['active'];
                }

                $imageexist = _PS_MODULE_DIR_.'marketplace/views/img/mpmanufacturers/'.$manufId.'.jpg';
                if (file_exists($imageexist)) {
                    $this->context->smarty->assign('imageexist', $imageexist);
                }
                $this->context->smarty->assign('manuf_id', $manufId);
                $this->context->smarty->assign('manufac_info', $manufacInfo);
            }
        }

        $currLang = WkMpManufacturers::getCurrentLang($mpIdSeller);
        if ($this->display == 'edit' && $brandStatus) {
            $productList = $objMpManuf->getProductsForAddManufacturerBySellerId($mpIdSeller, $currLang);
            $objMpManfProduct = new WkMpManufacturers();
            $assignedProductList = $objMpManfProduct->getSellerProductAssigned($mpIdSeller);
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
                $this->context->smarty->assign('product_list', $productList);
            }
        }

        WkMpHelper::assignDefaultLang($mpIdSeller);
        WkMpHelper::defineGlobalJSVariables(); // Define global js variable on js file
        $this->context->smarty->assign(
            array(
                'modules_dir' => _MODULE_DIR_,
                'countryinfo' => Country::getCountries($this->context->language->id),
                'stateinfo' => State::getStates($this->context->language->id),
                'languages' => Language::getLanguages(),
                'total_languages' => count(Language::getLanguages()),
                'current_lang' => Language::getLanguage((int) $currLang),
                'multi_lang' => Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE'),
                'mp_tinymce_path' => _MODULE_DIR_.'marketplace/libs',
                'path_css' => _THEME_CSS_DIR_,
                'ad' => __PS_BASE_URI__.basename(_PS_ADMIN_DIR_),
                'autoload_rte' => true,
                'lang' => true,
                'iso' => $this->context->language->iso_code,
                'mp_module_dir' => _MODULE_DIR_,
                'ps_module_dir' => _PS_MODULE_DIR_,
                'ps_img_dir' => _PS_IMG_.'l/',
                'self' => dirname(__FILE__),
            )
        );

        //for tiny mce field
        Media::addJsDef(array(
            'iso' => $this->context->language->iso_code,
            'mp_tinymce_path' => _MODULE_DIR_.'marketplace/libs',
            'addkeywords' => $this->l('Add keywords'),
            'req_manuf_name' => $this->l('Brand name is required.'),
            'req_manuf_address' => $this->l('Address is required.'),
            'req_manuf_city' => $this->l('City is required.'),
            'req_manuf_country' => $this->l('Country is required.'),
            'invalid_logo' => $this->l('Invalid image extensions, only jpg, jpeg and png are allowed.'),
            'invalid_city' => $this->l('City is not valid'),
            'invalid_zipcode' => $this->l('Zipcode is not valid'),
            'length_exceeds_address' => $this->l('Length must be smaller than 128 character'),
            'invalid_address' => $this->l('Address is not valid')
        ));

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Brand'),
                'icon' => 'icon-user',
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return parent::renderForm();
    }

    public function renderView()
    {
        $mpManufId = Tools::getValue('id_wk_mp_manufacturers');
        if ($mpManufId) {
            $manufProductListData = WkMpManufacturers::getSellerProductByMpManufId($mpManufId);
            if ($manufProductListData) {
                $psManufacturer = array();
                foreach ($manufProductListData as $key => $manufProductlist) {
                    $mpProductId = $manufProductlist['id_mp_product'];
                    $sellerProductData = WkMpSellerProduct::getSellerProductByIdProduct(
                        $mpProductId,
                        $this->context->language->id
                    );
                    if ($sellerProductData) {
                        $psManufacturer[$key]['mp_manuf_id'] = $mpManufId;
                        $psManufacturer[$key]['id'] = $manufProductlist['id_ps_product'];
                        $psManufacturer[$key]['mp_product_id'] = $mpProductId;
                        if (isset($sellerProductData['name'])) {
                            $psManufacturer[$key]['product_name'] = $sellerProductData['name'];
                        } else {
                            $psManufacturer[$key]['product_name'] = $sellerProductData['product_name'];
                        }
                        $psManufacturer[$key]['quantity'] = $sellerProductData['quantity'];
                    }
                }

                if (!empty($psManufacturer)) {
                    $this->context->smarty->assign('manufproductinfo', $psManufacturer);
                }
            }
        }

        return parent::renderView();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('status'.$this->table)) {
            $this->activeSellerManufacturer();
        }

        //Save manufacturer details
        if (Tools::isSubmit('submit_manufacturer') || Tools::isSubmit('submitStay_manufacturer')) {
            if (Tools::getValue('id_wk_mp_manufacturers')) {
                $idSeller = Tools::getValue('manuf_id_seller');
            } else {
                $idCustomer = Tools::getValue('shop_customer');
                $mpSeller = WkMpSeller::getSellerByCustomerId($idCustomer);
                $idSeller = $mpSeller['id_seller'];
            }

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
            if (!$idSeller) {
                $this->errors[] = $this->l('No seller selected');
            }
            if (!$name) {
                $this->errors[] = $this->l('Brand name is required field.');
            } elseif (!Validate::isCatalogName($name)) {
                $this->errors[] = $this->l('Invalid brand name.');
            }
            if ($phone) {
                if (!Validate::isPhoneNumber($phone)) {
                    $this->errors[] = $this->l('Invalid phone number.');
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
                            $this->l('Short description field %s is invalid', $className),
                            $languageName
                        );
                    } elseif (Tools::strlen(strip_tags($shortDesc)) > $limit) {
                        $this->errors[] = sprintf(
                            $this->l('Short description field %s is too long: (%d chars max).', $className),
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
                        $this->errors[] = sprintf($this->l('Product description field %s is invalid', $className), $languageName);
                    }
                }
                if (Tools::getValue('meta_title_'.$language['id_lang'])) {
                    if (!Validate::isGenericName(Tools::getValue('meta_title_'.$language['id_lang']))) {
                        $this->errors[] = sprintf(
                            $this->l('Meta title field %s is invalid', $className),
                            $languageName
                        );
                    } elseif (Tools::strlen(Tools::getValue('meta_title_'.$language['id_lang'])) > 128) {
                        $this->errors[] = sprintf(
                            $this->l('Meta title field is too long (%2$d chars max).', $className),
                            call_user_func(array($className, 'displayFieldName'), $className),
                            128
                        );
                    }
                }
                if (Tools::getValue('meta_desc_'.$language['id_lang'])) {
                    if (!Validate::isGenericName(Tools::getValue('meta_desc_'.$language['id_lang']))) {
                        $this->errors[] = sprintf(
                            $this->l('Meta description field %s is invalid', $className),
                            $languageName
                        );
                    } elseif (Tools::strlen(Tools::getValue('meta_desc_'.$language['id_lang'])) > 255) {
                        $this->errors[] = sprintf($this->l('Meta description field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), 255);
                    }
                }
                if (Tools::getValue('meta_key_'.$language['id_lang'])) {
                    if (!Validate::isGenericName(Tools::getValue('meta_key_'.$language['id_lang']))) {
                        $this->errors[] = sprintf(
                            $this->l('Meta key field %s is invalid', $className),
                            $languageName
                        );
                    }
                }
            }
            if (!trim($address)) {
                $this->errors[] = $this->l('Address is required field.');
            }
            if (!trim($city)) {
                $this->errors[] = $this->l('City is required field.');
            }
            if (!$country) {
                $this->errors[] = $this->l('Country is required field.');
            } elseif (Address::dniRequired($country)) {
                if (Tools::getValue('dni') == '') {
                    $this->errors[] = $this->l('DNI is required');
                } elseif (!Validate::isDniLite('dni')) {
                    $this->errors[] = $this->l('Invalid DNI');
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
                $mpManufId = Tools::getValue('id_wk_mp_manufacturers');

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
                    $psIdManuf = $psIdManufAddress = 0;
                    $updatemanuf = 0;
                }

                $objPsManufacturer->active = 1; //automatically approved by admin

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

                $objPsManufacturer->name = $name;
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
                        //Update manufacturer and manufacturer product table with psIdManuf
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
                if (Tools::isSubmit('submitStay_manufacturer')) {
                    if ($updatemanuf) {
                        Tools::redirectAdmin(self::$currentIndex.'&id_wk_mp_manufacturers='.(int) $mpManufId.
                        '&update'.$this->table.'&conf=4&token='.$this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex.'&id_wk_mp_manufacturers='.(int) $mpManufId.
                        '&update'.$this->table.'&conf=3&token='.$this->token);
                    }
                } else {
                    if ($updatemanuf) {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                    }
                }
            }
        }

        //Delete Product from Manufacturer Product list which is assign to Manufacturer
        if (Tools::isSubmit('deleteManufProduct')) {
            $manufProdId = Tools::getValue('manufproductid');
            if ($manufProdId) {
                $manufProdData = WkMpManufacturers::getSellerProductByManufProdId($manufProdId);
                if ($manufProdData) {
                    WkMpManufacturers::updateProductManufByPsIdProduct($manufProdData['id_ps_product']);
                }

                $objProdManufdel = new WkMpManufacturers($manufProdId);
                $objProdManufdel->delete();

                Tools::redirectAdmin(self::$currentIndex.'&conf=1&token='.$this->token);
            }
        }

        return parent::postProcess();
    }

    public function processStatus()
    {
        if (empty($this->errors)) {
            parent::processStatus();
        }
    }

    public function activeSellerManufacturer($manufId = false)
    {
        if (!$manufId) {
            $singleItem = 1;
            $manufId = Tools::getValue('id_wk_mp_manufacturers');
        }

        $objMpManufacturer = new WkMpManufacturers();
        $manufacInfo = $objMpManufacturer->getMpManufacturerAllDetails($manufId);

        $manufStatus = $manufacInfo['active'];
        if ($manufStatus) {
            $status = 0; //go for Inactive manufacturer
        } else {
            $status = 1; //go for Active manufacturer
        }

        if ($manufacInfo['id_ps_manuf']) { //if activated before
            WkMpManufacturers::updatePsManufacturerStatus($status, $manufacInfo['id_ps_manuf']);
        }

        if (!$manufStatus && !$manufacInfo['id_ps_manuf']) {
            $psIdManuf = $manufacInfo['id_ps_manuf'];
            if ($psIdManuf) {
                // Mail to seller for manufacturer activated
                $objMpSeller = new WkMpSeller($manufacInfo['id_seller']);
                $businessEmail = $objMpSeller->business_email;
                if ($businessEmail) {
                    $temp_path = _PS_MODULE_DIR_.'marketplace/mails/';
                    $templateVars = array(
                        '{seller_name}' => $objMpSeller->seller_firstname.' '.$objMpSeller->seller_lastname,
                        '{manufacturer_name}' => $manufacInfo['name'],
                    );

                    Mail::Send(
                        $this->context->language->id,
                        'manufacturer_active',
                        Mail::l('Manufacturer Active', $this->context->language->id),
                        $templateVars,
                        $businessEmail,
                        null,
                        null,
                        'Marketplace Manufacturer',
                        null,
                        null,
                        $temp_path,
                        false,
                        null,
                        null
                    );
                }
            }
        }

        if (isset($singleItem) && $singleItem) {
            Tools::redirectAdmin(self::$currentIndex.'&conf=5&token='.$this->token);
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
                    $objSellerManuf = new WkMpManufacturers($id);
                    $psManufacturer = new Manufacturer($objSellerManuf->id_ps_manuf);
                    if (isset($psManufacturer->active) && $psManufacturer->active == 0) {
                        $this->activeSellerManufacturer($id);
                    }
                }
            }
        } elseif ($status == 0) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                foreach ($this->boxes as $id) {
                    $objSellerManuf = new WkMpManufacturers($id);
                    $psManufacturer = new Manufacturer($objSellerManuf->id_ps_manuf);
                    if (isset($psManufacturer->active) && $psManufacturer->active == 1) {
                        $this->activeSellerManufacturer($id);
                    }
                }
            }
        }
    }

    public function ajaxProcessGetStateByCountry()
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

    public function ajaxProcessFindSellerManufacturers()
    {
        $customerId = Tools::getValue('customer_id');
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($customerId);
        if ($mpSeller && $mpSeller['active']) {
            $mpIdSeller = $mpSeller['id_seller'];
            $objMpManuf = new WkMpManufacturers();
            if (Configuration::get('WK_MP_PRODUCT_MANUFACTURER_ADMIN') == 1) {
                $manufacturers = $objMpManuf->getSellerPsManufacturers($mpIdSeller, $this->context->language->id);
            } else {
                $manufacturers = $objMpManuf->getOnlySellerManufacturers($mpIdSeller, $this->context->language->id);
            }

            if ($manufacturers) {
                die(Tools::jsonEncode($manufacturers)); //close ajax
            }
        }

        die;//ajax close
    }

    public function ajaxProcessFindSellerProduct()
    {
        $sellerIdCustomer = Tools::getValue('customer_id');
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($sellerIdCustomer);
        if ($mpSeller && $mpSeller['active']) {
            $mpIdSeller = $mpSeller['id_seller'];

            $currLang = WkMpManufacturers::getCurrentLang($mpIdSeller);

            $objMpManuf = new WkMpManufacturers();
            $productList = $objMpManuf->getProductsForAddManufacturerBySellerId($mpIdSeller, $currLang);

            $objMpManfProduct = new WkMpManufacturers();
            $assignedProductList = $objMpManfProduct->getSellerProductAssigned($mpIdSeller);

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
                echo Tools::jsonEncode($productList);
            } else {
                echo '-1';
            }
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

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        Media::addJsDef(array(
            'languages' => Language::getLanguages(),
            'allow_tagify' => 1,
            'already_assigned' => $this->l('(Already assigned)')
        ));

        $this->addJqueryPlugin('tagify');
        //tinymce
        $this->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
        if (version_compare(_PS_VERSION_, '1.6.0.11', '>')) {
            $this->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
        } else {
            $this->addJS(_PS_JS_DIR_.'tinymce.inc.js');
        }

        $this->addCSS(_MODULE_DIR_.'marketplace/views/css/addmanufacturer.css');
        $this->addCSS(_MODULE_DIR_.'marketplace/views/css/mp_global_style.css');
    }
}
