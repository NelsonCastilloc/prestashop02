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

class AdminMpSuppliersController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'wk_mp_suppliers';
        $this->className = 'WkMpSuppliers';
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller` mpsi ON (mpsi.`id_seller` = a.`id_seller`)';
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'supplier` s ON (a.`id_ps_supplier` = s.id_supplier)'.
        Shop::addSqlAssociation('supplier', 's');
        $this->_select = 'CONCAT(mpsi.`seller_firstname`, \' \', mpsi.`seller_lastname`) as `seller_name`,
        mpsi.`shop_name_unique`, a.`id_wk_mp_supplier` as `no_of_products`, s.`name`,  s.`active`,
        a.`id_wk_mp_supplier` as `id_seller_supplier`';
        $this->identifier = 'id_wk_mp_supplier';
        parent::__construct();
        if (!Shop::isFeatureActive() || Shop::getContext() !== Shop::CONTEXT_SHOP) {
            //In case of All Shops
            $this->_select .= ',shp.`name` as wk_ps_shop_name';
            $this->_join .= 'JOIN `'._DB_PREFIX_.'shop` shp ON (shp.`id_shop` = supplier_shop.`id_shop`)';
        }

        $this->fields_list = array();
        $this->fields_list['id_wk_mp_supplier'] = array(
            'title' => $this->l('ID'),
            'align' => 'center',
        );

        $this->fields_list['id_ps_supplier'] = array(
            'title' => $this->l('Prestashop supplier ID'),
            'align' => 'center',
            'callback' => 'prestashopSuppId',
            'havingFilter' => true,
        );

        $this->fields_list['id_seller_supplier'] = array(
            'title' => $this->l('Logo'),
            'callback' => 'displaySupplierImage',
            'search' => false,
            'havingFilter' => true,
        );

        $this->fields_list['name'] = array(
            'title' => $this->l('Supplier name'),
            'align' => 'center',
            'havingFilter' => true,
            'maxlength'=>'64',
        );

        $this->fields_list['no_of_products'] = array(
            'title' => $this->l('Products'),
            'align' => 'center',
            'callback' => 'getNoOfProducts',
            'search' => false,
        );

        $this->fields_list['seller_name'] = array(
            'title' => $this->l('Seller name'),
            'havingFilter' => true,
        );

        $this->fields_list['shop_name_unique'] = array(
            'title' => $this->l('Unique shop name'),
            'havingFilter' => true,
        );

        $this->fields_list['active'] = array(
            'title' => $this->l('Status'),
            'align' => 'center',
            'active' => 'status',
            'type' => 'bool',
            'orderby' => false,
            'filter_key' => 's!active'
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

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );
    }

    public function displaySupplierImage($idMpSupplier, $rowData)
    {
        $imageLink = _MODULE_DIR_.'marketplace/views/img/mpsuppliers/default_supplier.png';
        if ($idMpSupplier) {
            $supplierImg = 'marketplace/views/img/mpsuppliers/'.$idMpSupplier.'.jpg';
            if (file_exists(_PS_MODULE_DIR_.$supplierImg)) {
                $imageLink = _MODULE_DIR_.$supplierImg;
            }
        }
        return '<img class="img-thumbnail" width="45" height="45" src="'.$imageLink.'">';
    }

    public function initToolbar()
    {
        $allCustomers = WkMpSeller::getAllSeller();
        if ($allCustomers) {
            parent::initToolbar();
            $this->page_header_toolbar_btn['new'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new supplier'),
            );
        }

        unset($allCustomers);
    }

    public function renderList()
    {
        $this->addRowAction('view');
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function getNoOfProducts($value)
    {
        $no_products = WkMpSuppliers::getNoOfProductsByMpSupplierId($value);
        if ($no_products) {
            return $no_products;
        } else {
            return 0;
        }
    }

    public function prestashopSuppId($value)
    {
        if ($value == 0) {
            return '-';
        } else {
            return $value;
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('status'.$this->table)) {
            $mpSupplierId = Tools::getValue('id_wk_mp_supplier');

            if ($mpSupplierId) {
                $objMpSupplier = new WkMpSuppliers((int)$mpSupplierId);
                $supplierInfo = $objMpSupplier->getMpSupplierAllDetails($mpSupplierId);
                $suppStatus = $supplierInfo['active'];
                if ($suppStatus) {
                    $status = 0; //go for Inactive supplier
                } else {
                    $status = 1; //go for Active supplier
                }

                if ($supplierInfo['id_ps_supplier']) { //if activated before
                    WkMpSuppliers::changeStatus($status, $supplierInfo['id_ps_supplier']);
                }

                $obj_mp_seller = new WkMpSeller();
                $mp_seller_info = $obj_mp_seller->getSellerWithLangBySellerId($supplierInfo['id_seller']);
                $id_lang = $this->context->language->id;
                if ($mp_seller_info['business_email']) {
                    $temp_path = _PS_MODULE_DIR_.'marketplace/mails/';
                    $templateVars = array(
                        '{seller_name}' => $mp_seller_info['seller_firstname'].' '.
                        $mp_seller_info['seller_lastname'],
                        '{supplier_name}' => $supplierInfo['name'],
                    );

                    if ($status) {
                        // mail to seller for supplier activated
                        Mail::Send(
                            $id_lang,
                            'supplier_active',
                            Mail::l('Supplier active', $id_lang),
                            $templateVars,
                            $mp_seller_info['business_email'],
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
                    } else {
                        // mail to seller for supplier deactivated
                        Mail::Send(
                            $id_lang,
                            'supplier_deactive',
                            Mail::l('Supplier deactive', $id_lang),
                            $templateVars,
                            $mp_seller_info['business_email'],
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
                Tools::redirectAdmin(self::$currentIndex.'&conf=5&token='.$this->token);
            }

            if (!Tools::isSubmit('submitAdd'.$this->table.'AndAssignStay')) {
                Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
            }
        } elseif (Tools::isSubmit('delete'.$this->table)) {
            WkMpSuppliers::deleteSupplier(Tools::getValue('id_wk_mp_supplier'));
            Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$this->token);
        }

        parent::postProcess();
    }

    protected function processBulkDelete()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $id) {
                WkMpSuppliers::deleteSupplier($id);
            }
        }

        Tools::redirectAdmin(self::$currentIndex.'&conf=2&token='.$this->token);
        parent::processBulkDelete();
    }

    protected function processBulkEnableSelection()
    {
        return $this->processBulkStatusSelection(1);
    }

    protected function processBulkDisableSelection()
    {
        return $this->processBulkStatusSelection(0);
    }

    protected function processBulkStatusSelection($active)
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $obj_mp_seller = new WkMpSeller();
            foreach ($this->boxes as $mpSupplierId) {
                $objMpSupplier = new WkMpSuppliers((int)$mpSupplierId);
                $supplierInfo = $objMpSupplier->getMpSupplierAllDetails($mpSupplierId);
                $suppStatus = $supplierInfo['active'];
                $status = $active;

                if ($supplierInfo['id_ps_supplier']) { //if activated before
                    WkMpSuppliers::changeStatus($status, $supplierInfo['id_ps_supplier']);
                }

                $obj_mp_seller = new WkMpSeller();
                $mp_seller_info = $obj_mp_seller->getSellerWithLangBySellerId($supplierInfo['id_seller']);
                $id_lang = $this->context->language->id;
                if ($mp_seller_info['business_email']) {
                    $temp_path = _PS_MODULE_DIR_.'marketplace/mails/';
                    $templateVars = array(
                        '{seller_name}' => $mp_seller_info['seller_firstname'].' '.
                        $mp_seller_info['seller_lastname'],
                        '{supplier_name}' => $supplierInfo['name'],
                    );

                    if ($active) {
                        // mail to seller for supplier activated
                        Mail::Send(
                            $id_lang,
                            'supplier_active',
                            Mail::l('Supplier active', $id_lang),
                            $templateVars,
                            $mp_seller_info['business_email'],
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
                    } else {
                        // mail to seller for supplier deactivated
                        Mail::Send(
                            $id_lang,
                            'supplier_deactive',
                            Mail::l('Supplier deactive', $id_lang),
                            $templateVars,
                            $mp_seller_info['business_email'],
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
            }

            Tools::redirectAdmin(self::$currentIndex.'&conf=5&token='.$this->token);
        }
    }

    public function processSave()
    {
        $mp_supplier_id = Tools::getValue('id_wk_mp_supplier'); //if edit
        $suppname = Tools::getValue('suppname');
        $suppphone = Tools::getValue('suppphone');
        $suppmobile = Tools::getValue('suppmobile');
        $suppaddress = Tools::getValue('suppaddress');
        $suppzip = Tools::getValue('suppzip');
        $suppcity = Tools::getValue('suppcity');
        $suppcountry = Tools::getValue('suppcountry');
        $suppstate = Tools::getValue('suppstate');
        $selected_products = Tools::getValue('selected_products');
        $default_lang = Tools::getValue('choosedLangId');
        $customerId = Tools::getValue('mp_customer_id');

        // data validation
        if ($suppname == '') {
            $this->errors[] = $this->l('Supplier name is required.');
        } elseif (!Validate::isGenericName($suppname)) {
            $this->errors[] = $this->l('Supplier name is not valid.');
        }

        if (!Validate::isPhoneNumber($suppphone)) {
            $this->errors[] = $this->l('Phone number is invalid.');
        }
        if (!Validate::isPhoneNumber($suppmobile)) {
            $this->errors[] = $this->l('Mobile phone number is invalid.');
        }

        if (trim($suppaddress) == '') {
            $this->errors[] = $this->l('Address is required.');
        } elseif (!Validate::isAddress($suppaddress)) {
            $this->errors[] = $this->l('Invalid address.');
        }

        if ($suppzip) {
            if (!Validate::isPostCode($suppzip)) {
                $this->errors[] = $this->l('Invaid zip/postal Code.');
            }
        }

        if ($suppcity == '') {
            $this->errors[] = $this->l('City is required.');
        } elseif (!Validate::isCityName($suppcity)) {
            $this->errors[] = $this->l('City name is invalid.');
        }

        if (!$suppcountry) {
            $this->errors[] = $this->l('Country is required field.');
        } elseif (Address::dniRequired($suppcountry)) {
            if (Tools::getValue('dni') == '') {
                $this->errors[] = $this->l('DNI is required');
            } elseif (!Validate::isDniLite('dni')) {
                $this->errors[] = $this->l('Invalid DNI');
            } else {
                $addressDNI = Tools::getValue('dni');
            }
        }

        if (!empty($_FILES['supplier_logo']['name'])
        && $_FILES['supplier_logo']['size'] > 0
        && $_FILES['supplier_logo']['tmp_name'] != '') {
            if ($errorMsg = ImageManager::validateUpload($_FILES['supplier_logo'])) {
                $this->errors[] = $errorMsg;
            }
        }

        foreach (Language::getLanguages(true) as $language) {
            if (Tools::strlen(Tools::getValue('meta_title_'.$language['id_lang'])) > 255) {
                $this->errors[] = $this->l('Meta title must be between 0 and 255 chars.');
            }
            if (Tools::strlen(Tools::getValue('meta_desc_'.$language['id_lang'])) > 512) {
                $this->errors[] = $this->l('Meta description must be between 0 and 512 chars.');
            }

            if (Tools::strlen(Tools::getValue('meta_key_'.$language['id_lang'])) > 255) {
                $this->errors[] = $this->l('Meta keywords must be between 0 and 255 chars.');
            }
        }

        if (empty($this->errors)) {
            $mpIdSupplier = Tools::getValue('id');
            if ($mpIdSupplier) {
                $mpSupplierInfo = WkMpSuppliers::getMpSupplierAllDetails($mpIdSupplier);
                $psIdSupplier = $mpSupplierInfo['id_ps_supplier'];
                $sellerid = $mpSupplierInfo['id_seller'];
                $psIdSupplierAddress = $mpSupplierInfo['id_ps_supplier_address'];
                $objmpsupp = new WkMpSuppliers($mpIdSupplier); //edit supplier
                $objpssupp = new Supplier($psIdSupplier);
            } else {
                $objmpsupp = new WkMpSuppliers(); //add manufacturer
                $objpssupp = new Supplier();
                if (Configuration::get('WK_MP_PRODUCT_SUPPLIER_APPROVED')) {
                    $objpssupp->active = 0; //need to approved by admin
                } else {
                    $objpssupp->active = 1; //automatically approved
                }

                if ($customerId) {
                    $mpSeller = WkMpSeller::getSellerDetailByCustomerId($customerId);
                    $sellerid = $mpSeller['id_seller'];
                }
                $psIdSupplier = $psIdSupplierAddress = 0;
            }
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

                if ($selected_products = Tools::getValue('selected_products')) {
                    WkMpSuppliers::updateSupplierProducts(
                        $mpIdSupplier,
                        $psIdSupplier,
                        $selected_products
                    );
                }
                // *** end *** add address of supplier in prestashop
            }
            if (Tools::isSubmit('submitAddwk_mp_suppliersAndAssignStay')) {
                Tools::redirectAdmin(
                    self::$currentIndex.'&updatewk_mp_suppliers=&id_wk_mp_supplier='.(int)$objmpsupp->id.
                    '&conf=4&token='.$this->token
                );
            } else {
                Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
            }
        } else {
            if ($mp_supplier_id) {
                $this->display = 'edit';
            } else {
                $this->display = 'add';
            }
        }
    }

    public function renderView()
    {
        $id_lang = $this->context->language->id;
        $mp_supplier_id = Tools::getValue('id_wk_mp_supplier');
        if ($mp_supplier_id) {
            $objMpSupplier = new WkMpSuppliers();
            $supplierInfo = $objMpSupplier->getMpSupplierAllDetails($mp_supplier_id);
            $product_list = WkMpSuppliers::getProductListByMpSupplierIdAndIdSeller(
                $mp_supplier_id,
                $supplierInfo['id_seller'],
                $id_lang
            );
            if ($product_list) {
                $this->context->smarty->assign('product_list', $product_list);
            }
            $this->context->smarty->assign('supplierInfo', $supplierInfo);
        } else {
            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
        }

        return parent::renderView();
    }

    public function renderForm()
    {
        if ((($this->display == 'edit') &&  (Shop::getContext() != Shop::CONTEXT_SHOP))
            || ($this->display == 'add' && (Shop::getContext() != Shop::CONTEXT_SHOP))) {
            $shopWarning = $this->l('You can not add/edit in this shop context.');
            $shopWarning .= $this->l(' Select a shop instead of a group of shops.');
            $this->warnings[] = $shopWarning;
            return;
        }

        $this->context->smarty->assign('is_front_controller', 0);
        if ($this->display == 'add') {
            $all_active_sellers = WkMpSeller::getAllSeller();
            if ($all_active_sellers) {
                $this->context->smarty->assign('seller_list', $all_active_sellers);

                //get first seller from the list
                $first_seller_details = $all_active_sellers[0];
                $mp_id_seller = $first_seller_details['id_seller'];
            } else {
                $mp_id_seller = 0;
            }
        } elseif ($this->display == 'edit') {
            $mp_supplier_id = Tools::getValue('id_wk_mp_supplier');
            if ($mp_supplier_id) {
                $image = file_exists(
                    _PS_MODULE_DIR_.'marketplace/views/img/mpsuppliers/'.$mp_supplier_id.'.jpg'
                ) ? $mp_supplier_id.'.jpg' : 'default_supplier.png';
                $image = __PS_BASE_URI__.'modules/marketplace/views/img/mpsuppliers/'.$image;
                $this->context->smarty->assign('supplier_image', $image);
                $objMpSupplier = new WkMpSuppliers($mp_supplier_id);
                $mp_id_seller = $objMpSupplier->id_seller;
                $supplierInfo = $objMpSupplier->getMpSupplierAllDetails($mp_supplier_id);
                $this->context->smarty->assign('supplier_info', $supplierInfo);
                if ($supplierInfo['active']) {
                    $product_list = WkMpSuppliers::getProductsForUpdateSupplierBySellerIdAndPsSupplierId(
                        $objMpSupplier->id_seller,
                        $objMpSupplier->id_ps_supplier,
                        $this->context->language->id
                    );
                    if ($product_list) {
                        $this->context->smarty->assign('product_list', $product_list);
                    }
                }
            }
        }

        // Set default lang at every form according to configuration multi-language
        WkMpHelper::assignDefaultLang($mp_id_seller);
        WkMpHelper::defineGlobalJSVariables(); // Define global js variable on js file

        $this->context->smarty->assign(
            array(
                'countryinfo' => Country::getCountries($this->context->language->id),
                'path_css' => _THEME_CSS_DIR_,
                'ad' => __PS_BASE_URI__.basename(_PS_ADMIN_DIR_),
                'autoload_rte' => true,
                'lang' => true,
                'iso' => $this->context->language->iso_code,
                'mp_module_dir' => _MODULE_DIR_,
                'ps_module_dir' => _PS_MODULE_DIR_,
                'ps_img_dir' => _PS_IMG_.'l/',
                'self' => dirname(__FILE__)
            )
        );

        $this->fields_form = array(
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return parent::renderForm();
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

    public function ajaxProcessGetSupplierByCustomerId()
    {
        $idCustomer = Tools::getValue('selected_id_customer');
        $result = array();
        $result['status'] = 0;
        if ($idCustomer) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($mpSeller) {
                $objMpSupplier = new WkMpSuppliers();
                $suppliers = $objMpSupplier->getSuppliersForProductBySellerId($mpSeller['id_seller']);
                if ($suppliers) {
                    $result['status'] = 1;
                    $result['info'] = $suppliers;
                }
            }
        }

        $data = Tools::jsonEncode($result);
        die($data);
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
        if ((($this->display == 'edit') &&  (Shop::getContext() != Shop::CONTEXT_SHOP))
            || ($this->display == 'add' && (Shop::getContext() != Shop::CONTEXT_SHOP))) {
            return;
        }

        $this->addJqueryPlugin('tagify');
        //tinymce
        $this->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
        if (version_compare(_PS_VERSION_, '1.6.0.11', '>')) {
            $this->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
        } else {
            $this->addJS(_PS_JS_DIR_.'tinymce.inc.js');
        }

        if (Tools::getValue('addwk_mp_suppliers') !== false
        || Tools::getValue('updatewk_mp_suppliers') !== false) {
            Media::addJsDef(
                array(
                    'req_suppname' => $this->l('Supplier name is required'),
                    'inv_suppname' => $this->l('Invalid supplier name'),
                    'inv_suppphone' => $this->l('Invalid phone number'),
                    'inv_suppmobile' => $this->l('Invalid mobile phone number'),
                    'req_suppaddress' => $this->l('Address is required'),
                    'inv_suppaddress' => $this->l('Invalid address'),
                    'req_suppzip' => $this->l('Zip/Postal code is required'),
                    'inv_suppzip' => $this->l('Invalid zip/postal code'),
                    'req_suppcity' => $this->l('City is required'),
                    'inv_suppcity' => $this->l('City name is invalid'),
                    'allow_tagify' => 1,
                    'addkeywords' => $this->l('Add keywords'),
                    'languages' => Language::getLanguages(),
                    'inv_language_title' => $this->l('Invalid meta title'),
                    'inv_language_desc' => $this->l('Invalid meta description'),
                    'invalid_logo' => $this->l('Invalid image extensions, only jpg, jpeg and png are allowed.'),
                    'static_token' => Tools::getValue('token'),
                )
            );
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/suppliers/supplier_form_validation.js');
        }
    }
}
