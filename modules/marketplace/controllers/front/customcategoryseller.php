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

class MarketplaceCustomCategorySellerModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $idCustomer = $this->context->customer->id;
            $addPermission = 1;
            $editPermission = 1;
            $deletePermission = 1;
            
            //Override customer id if any staff of seller want to use this controller
            if (Module::isEnabled('mpsellerstaff')) {
                $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($idCustomer);
                if ($staffDetails
                    && $staffDetails['active']
                    && $staffDetails['id_seller']
                    && $staffDetails['seller_status']
                ) {
                    $idTab = WkMpTabList::MP_PRODUCT_TAB; //For Product
                    $staffTabDetails = WkMpTabList::getStaffPermissionWithTabName(
                        $staffDetails['id_staff'],
                        $this->context->language->id,
                        $idTab
                    );
                    if ($staffTabDetails) {
                        $addPermission = $staffTabDetails['add'];
                        $editPermission = $staffTabDetails['edit'];
                        $deletePermission = $staffTabDetails['delete'];
                    }
                }

                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                //delete selected checkbox process
                if ($selectedProducts = Tools::getValue('mp_product_selected')) {
                    $this->deleteSelectedProducts($selectedProducts, $seller['id_seller']);
                }

                //change product status if seller can activate/deactivate their product
                if (Tools::getValue('mp_product_status')
                && Configuration::get('WK_MP_SELLER_PRODUCTS_SETTINGS')) {
                    $this->changeProductStatus($seller['id_seller']);
                }

                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                    $idLang = $this->context->language->id;
                } else {
                    if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {
                        $idLang = Configuration::get('PS_LANG_DEFAULT');
                    } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {
                        $idLang = $seller['default_lang'];
                    }
                }
                $objLang = new Language((int) $idLang);
                if (!$objLang->active) {
                    $idLang = Configuration::get('PS_LANG_DEFAULT');
                }

                $sellerProduct = WkMpSellerProduct::getSellerAllShopProduct(
                    $seller['id_seller'],
                    'all',
                    $idLang
                );
                /* Getting category all for new functionality in front seller -category management-*/
                $complex_category = Category::getCategories();
                $category = [];
                foreach ($complex_category as $i => $Ivalue) {
                    foreach ($Ivalue as $j => $Jvalue) {
                        foreach ($Jvalue as $k => $Kvalue) {
                            $category[$i]['id'] = $Kvalue['id_category'];
                            $category[$i]['level'] = $Kvalue['level_depth'];
                            $category[$i]['name'] = $Kvalue['name'];
                            $category[$i]['description'] = strip_tags($Kvalue['description']);
                        }
                    }
                }
                if (!$sellerProduct) {
                    $sellerProduct = array();
                }

                $shareCustomerEnabled = false;
                if ($this->context->shop->id_shop_group) {
                    $objShopGroup = new ShopGroup((int) $this->context->shop->id_shop_group);
                    $shareCustomerEnabled = $objShopGroup->share_customer;
                }

                $this->context->smarty->assign(array(
                    'products_status' => Configuration::get('WK_MP_SELLER_PRODUCTS_SETTINGS'),
                    'imageediturl' => $this->context->link->getModuleLink('marketplace', 'productimageedit'),
                    'product_lists' => $sellerProduct,
                    'category_list' => $category,
                    'is_seller' => $seller['active'],
                    'logic' => 'custom_category_seller',
                    'static_token' => Tools::getToken(false),
                    'add_permission' => $addPermission,
                    'edit_permission' => $editPermission,
                    'delete_permission' => $deletePermission,
                    'isMultiShopEnabled' => WkMpHelper::isMultiShopEnabled(),
                    'currentShopId' => Context::getContext()->shop->id,
                    'shareCustomerEnabled' => $shareCustomerEnabled,
                ));

                $this->defineJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/category/categorylist.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect(
                'index.php?controller=authentication&back='.
                urlencode($this->context->link->getModuleLink('marketplace', 'productlist'))
            );
        }
    }


    public function postProcess()
    {
        if (Configuration::get('WK_MP_SELLER_EXPORT')
        && (Tools::isSubmit('mp_csv_product_export') || Tools::getValue('export_all'))) {
            $fromExportDate = Tools::getValue('from_export_date');
            $toExportDate = Tools::getValue('to_export_date');
            $fromExportDate = date('Y-m-d', strtotime($fromExportDate));
            $toExportDate = date('Y-m-d', strtotime($toExportDate));
            $exportAll = false;
            if (Tools::getValue('export_all')) {
                $exportAll = true;
            }
            if (!$exportAll) {
                if ($fromExportDate == '') {
                    $this->errors[] = $this->module->l('Export from date is required.', 'mporder');
                } elseif (!Validate::isDateFormat($fromExportDate)) {
                    $this->errors[] = $this->module->l('Export from date is not valid.', 'mporder');
                }
                if ($toExportDate == '') {
                    $this->errors[] = $this->module->l('Export to date is required.', 'mporder');
                } elseif (!Validate::isDateFormat($toExportDate)) {
                    $this->errors[] = $this->module->l('Export to date is not valid.', 'mporder');
                }
            }

            if (empty($this->errors)) {
                $id_customer = $this->context->customer->id;
                if ($id_customer) {
                    $mpSeller = WkMpSeller::getSellerDetailByCustomerId($id_customer);
                    $this->exportProductsCSV($mpSeller, $fromExportDate, $toExportDate, $exportAll);
                }
            }
        }

        parent::postProcess();
    }

    public function exportProductsCSV($seller, $fromExportDate, $toExportDate, $exportAll)
    {
        if ($fromExportDate && $toExportDate && $seller) {
            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $idLang = $this->context->language->id;
            } else {
                if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {
                    $idLang = Configuration::get('PS_LANG_DEFAULT');
                } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {
                    $idLang = $seller['default_lang'];
                }
            }
            $objLang = new Language((int) $idLang);
            if (!$objLang->active) {
                $idLang = Configuration::get('PS_LANG_DEFAULT');
            }
            if ($exportAll) {
                $csvDataArr = WkMpSellerProduct::getSellerAllShopProduct(
                    $seller['id_seller'],
                    'all',
                    $idLang
                );
                if (empty($csvDataArr)) {
                    $this->errors = $this->module->l('No products are available.', 'productlist');
                    return;
                }
            } else {
                $csvDataArr = WkMpSellerProduct::getSellerAllShopProduct(
                    $seller['id_seller'],
                    'all',
                    $idLang,
                    $fromExportDate,
                    $toExportDate
                );
                if (empty($csvDataArr)) {
                    $this->errors = $this->module->l(
                        'No products are available on selected date range.',
                        'productlist'
                    );
                    return;
                }
            }
            $idLang = Context::getContext()->language->id;
            $fileName = "product_csv_".date("Y-m-d_H:i", time()).".csv";
            header('Content-Type: text/csv');
            header('Content-Type: application/force-download; charset=UTF-8');
            header('Cache-Control: no-store, no-cache');
            header('Content-Disposition: attachment; filename='.$fileName);
            ob_end_clean();
            $output = fopen('php://output', 'w');
            fputcsv($output, array(
                $this->module->l('Product ID', 'mporder'),
                $this->module->l('Name', 'mporder'),
                $this->module->l('Price', 'mporder'),
                $this->module->l('Quantity', 'mporder'),
                $this->module->l('Status', 'mporder'),
                $this->module->l('Date', 'mporder'),
            ));
            if ($csvDataArr) {
                $count = 1;
                foreach ($csvDataArr as $eachProductCsvData) {
                    $csvData = array();
                    $csvData['product_id'] = $eachProductCsvData['id_mp_product'];
                    $csvData['name'] = $eachProductCsvData['name'];
                    $csvData['price'] = $eachProductCsvData['price'];
                    $csvData['quantity'] = $eachProductCsvData['quantity'];
                    $csvData['status'] =
                    ($eachProductCsvData['active']) ? $this->module->l('Active') : $this->module->l('Pending');
                    $csvData['date_add'] = $eachProductCsvData['date_add'];
                    fputcsv($output, $csvData);
                    $count++;
                }
            }
            fclose($output);
            exit;
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
                'productlist_link' => $this->context->link->getModuleLink('marketplace', 'productlist'),
                'ajax_urlpath' => $this->context->link->getModuleLink('marketplace', 'productimageedit'),
                'image_drag_drop' => 1,
                'space_error' => $this->module->l('Space is not allowed.', 'productlist'),
                'confirm_delete_msg' => $this->module->l('Are you sure you want to delete?', 'productlist'),
                'confirm_duplicate_msg' => $this->module->l('Are you sure you want to duplicate?', 'productlist'),
                'delete_msg' => $this->module->l('Deleted.', 'productlist'),
                'error_msg' => $this->module->l('An error occurred.', 'productlist'),
                'checkbox_select_warning' => $this->module->l('You must select at least one element.', 'productlist'),
                'display_name' => $this->module->l('Display', 'productlist'),
                'records_name' => $this->module->l('records per page', 'productlist'),
                'no_product' => $this->module->l('No product found', 'productlist'),
                'show_page' => $this->module->l('Showing page', 'productlist'),
                'show_of' => $this->module->l('of', 'productlist'),
                'no_record' => $this->module->l('No records available', 'productlist'),
                'filter_from' => $this->module->l('filtered from', 'productlist'),
                't_record' => $this->module->l('total records', 'productlist'),
                'search_item' => $this->module->l('Search', 'productlist'),
                'p_page' => $this->module->l('Previous', 'productlist'),
                'n_page' => $this->module->l('Next', 'productlist'),
                'update_success' => $this->module->l('Updated Successfully', 'productlist'),
                'empty_from_date' => $this->module->l('Please select from date.', 'productlist'),
                'empty_to_date' => $this->module->l('Please select to date.', 'productlist'),
                'compare_date_error' => $this->module->l('To date must be greater than from date.', 'productlist'),
            );
        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            $jsVars['friendly_url'] = 1;
        } else {
            $jsVars['friendly_url'] = 0;
        }
        Media::addJsDef($jsVars);
    }

    public function changeProductStatus($idSeller)
    {
        $idProduct = Tools::getValue('id_product');
        $sellerProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($idProduct);
        if ($sellerProduct && ($sellerProduct['id_seller'] == $idSeller)) {
            $mpIdProduct = $sellerProduct['id_mp_product'];
            Hook::exec('actionBeforeToggleMPProductStatus', array('id_mp_product' => $mpIdProduct));
            if (!count($this->errors)) {
                $objMpProduct = new WkMpSellerProduct($mpIdProduct);
                if ($psProductId = $objMpProduct->id_ps_product) {
                    $objPsProduct = new Product($psProductId);
                    if ($objPsProduct->active) {
                        $objMpProduct->status_before_deactivate = 0;
                        $objMpProduct->save();

                        //Update on ps
                        $objPsProduct->active = 0;
                        $objPsProduct->save();
                    } else {
                        $objMpProduct->status_before_deactivate = 1;
                        $objMpProduct->save();

                        //Update on ps
                        $objPsProduct->active = 1;
                        $objPsProduct->save();

                        Hook::exec(
                            'actionToogleMPProductActive',
                            array('id_mp_product' => $mpIdProduct, 'active' => $objPsProduct->active)
                        );
                    }

                    Hook::exec(
                        'actionAfterToggleMPProductStatus',
                        array('id_product' => $idProduct, 'active' => $objPsProduct->active)
                    );
                    Tools::redirect(
                        $this->context->link->getModuleLink('marketplace', 'productlist', array('status_updated' => 1))
                    );
                }
            }
        }
    }

    public function deleteSelectedProducts($mpIdProducts, $idSeller)
    {
        $mpDelete = true;
        foreach ($mpIdProducts as $idMpProduct) {
            $objMpProduct = new WkMpSellerProduct($idMpProduct);
            if ($objMpProduct->id_seller == $idSeller) {
                if (!$objMpProduct->delete()) {
                    $mpDelete = false;
                }
            }
            unset($objMpProduct);
        }

        if ($mpDelete) {
            Tools::redirect(
                $this->context->link->getModuleLink('marketplace', 'productlist', array('deleted' => 1))
            );
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'productlist'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Product List', 'productlist'),
            'url' => ''
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        Media::addJsDef(array(
            'languages' => Language::getLanguages(),
            'ImageCaptionLangError' => $this->module->l('Image caption field is invalid in', 'productlist'),
        ));
        $this->addJqueryPlugin('tablednd');
        $this->addjQueryPlugin('growl', null, false);
        $this->registerStylesheet(
            'marketplace_account',
            'modules/'.$this->module->name.'/views/css/marketplace_account.css'
        );
        $this->registerJavascript(
            'mp-imageedit-js',
            'modules/'.$this->module->name.'/views/js/imageedit.js'
        );

        //data table file included
        $this->registerStylesheet(
            'datatable_bootstrap',
            'modules/'.$this->module->name.'/views/css/datatable_bootstrap.css'
        );
        $this->registerJavascript(
            'mp-jquery-dataTables',
            'modules/'.$this->module->name.'/views/js/jquery.dataTables.min.js'
        );
        $this->registerJavascript(
            'mp-dataTables.bootstrap',
            'modules/'.$this->module->name.'/views/js/dataTables.bootstrap.js'
        );
    }
}
