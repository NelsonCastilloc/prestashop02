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

class MarketplaceMpSupplierListModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->isLogged()) {
            $smartyVars = array();
            $wkMsgCode = Tools::getValue('msg_code');
            if ($wkMsgCode) {
                $smartyVars['msg_code'] = $wkMsgCode;
            }

            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller && $mpSeller['active']) {
                if (!Configuration::get('WK_MP_PRODUCT_SUPPLIER')) {
                    Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                }

                $objMpSupplier = new WkMpSuppliers();
                $mpSupplierInfo = $objMpSupplier->getSuppliersBySellerId($mpSeller['id_seller']);
                if ($mpSupplierInfo) {
                    foreach ($mpSupplierInfo as $key => $mpSupplier) {
                        $noOfProducts = WkMpSuppliers::getNoOfProductsByMpSupplierId(
                            $mpSupplier['id_wk_mp_supplier']
                        );
                        if ($noOfProducts) {
                            $mpSupplierInfo[$key]['no_of_products'] = $noOfProducts;
                        } else {
                            $mpSupplierInfo[$key]['no_of_products'] = 0;
                        }

                        if (file_exists(_PS_MODULE_DIR_.'marketplace/views/img/mpsuppliers/'.$mpSupplier['id_wk_mp_supplier'].'.jpg')) {
                            $mpSupplierInfo[$key]['image'] = _MODULE_DIR_.'marketplace/views/img/mpsuppliers/'.$mpSupplier['id_wk_mp_supplier'].'.jpg';
                        } else {
                            $mpSupplierInfo[$key]['image'] = _MODULE_DIR_.'marketplace/views/img/mpsuppliers/default_supplier.png';
                        }
                    }
                    $smartyVars['mpSupplierInfo'] = $mpSupplierInfo;
                }

                $this->context->smarty->assign('logic', 'mpsupplierlist');
                $this->context->smarty->assign($smartyVars);
                $this->addCustomJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/product/suppliers/supplierlist.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function addCustomJSVars()
    {
        $jsVars = array(
            'supplier_ajax_link' => $this->context->link->getModuleLink('marketplace', 'mpsupplierlist'),
            'dsbl' => $this->module->l('Disabled', 'mpsupplierlist'),
            'enbl' => $this->module->l('Enabled', 'mpsupplierlist'),
            'tech_error' => $this->module->l('Their is some technical error', 'mpsupplierlist'),
            'dsbl_sucs' => $this->module->l('Supplier successfully disabled !', 'mpsupplierlist'),
            'enbl_sucs' => $this->module->l('Supplier successfully enabled !', 'mpsupplierlist'),
            'del_sucs' => $this->module->l('Supplier successfully deleted !', 'mpsupplierlist'),
            'conf_msg' => $this->module->l('Are you sure, you want to delete this supplier ?', 'mpsupplierlist'),
        );

        Media::addJsDef($jsVars);
    }

    public function displayAjaxGetStateByCountry()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
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
            die($result);
        }

        die; //ajax close
    }

    public function displayAjaxDniRequired()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        if ($id_country = Tools::getValue('id_country')) {
            $resp = Address::dniRequired($id_country);
            die($resp);
        }

        die; //ajax close
    }

    public function displayAjaxDeleteSupplier()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        $id = Tools::getValue('mp_supplier_id');
        if ($id) {
            if (Context::getContext()->customer->id) {
                $mpSeller = WkMpSeller::getSellerDetailByCustomerId(Context::getContext()->customer->id);
                if ($mpSeller && $mpSeller['active']) {
                    $mpSellerId = $mpSeller['id_seller'];
                    $objMpSupplier = new WkMpSuppliers((int)$id);
                    if ($mpSellerId == $objMpSupplier->id_seller) {
                        if (WkMpSuppliers::deleteSupplier($id)) {
                            echo 1;
                            die;
                        }
                    }
                }
            }
            echo 0;
            die;
        }

        die; //ajax close
    }

    public function displayAjaxDeleteBulkSupplier()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        if (Context::getContext()->customer->id) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId(Context::getContext()->customer->id);
            if ($mpSeller && $mpSeller['active']) {
                $mpSellerId = $mpSeller['id_seller'];
                $mpSupIds = json_decode(Tools::getValue('mpSupIds'));
                foreach ($mpSupIds as $id) {
                    $objMpSupplier = new WkMpSuppliers((int)$id);
                    if ($mpSellerId == $objMpSupplier->id_seller) {
                        WkMpSuppliers::deleteSupplier($id);
                    }
                }
            }
        }
        die("1");

        die; //ajax close
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'supplierlist'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Supplier list', 'supplierlist'),
            'url' => ''
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $jsVars = array(
            'ajax_urlpath' => $this->context->link->getModuleLink('marketplace', 'productimageedit'),
            'image_drag_drop' => 1,
            'space_error' => $this->module->l('Space is not allowed.', 'mpsupplierlist'),
            'confirm_delete_msg' => $this->module->l('Are you sure you want to delete?', 'mpsupplierlist'),
            'confirm_duplicate_msg' => $this->module->l('Are you sure you want to duplicate?', 'mpsupplierlist'),
            'delete_msg' => $this->module->l('Deleted.', 'mpsupplierlist'),
            'error_msg' => $this->module->l('An error occurred.', 'mpsupplierlist'),
            'checkbox_select_warning' => $this->module->l('You must select at least one element.', 'mpsupplierlist'),
            'display_name' => $this->module->l('Display', 'mpsupplierlist'),
            'records_name' => $this->module->l('records per page', 'mpsupplierlist'),
            'no_product' => $this->module->l('No supplier yet', 'mpsupplierlist'),
            'show_page' => $this->module->l('Showing page', 'mpsupplierlist'),
            'show_of' => $this->module->l('of', 'mpsupplierlist'),
            'no_record' => $this->module->l('No records available', 'mpsupplierlist'),
            'filter_from' => $this->module->l('filtered from', 'mpsupplierlist'),
            't_record' => $this->module->l('total records', 'mpsupplierlist'),
            'search_item' => $this->module->l('Search', 'mpsupplierlist'),
            'p_page' => $this->module->l('Previous', 'mpsupplierlist'),
            'n_page' => $this->module->l('Next', 'mpsupplierlist'),
            'update_success' => $this->module->l('Updated successfully', 'mpsupplierlist'),
            'static_token' => Tools::getToken(false),
        );
        Media::addJsDef($jsVars);

        $this->registerStylesheet(
            'mp-marketplace_account',
            'modules/marketplace/views/css/marketplace_account.css'
        );
        $this->registerJavascript(
            'supplier_form_validation',
            'modules/'.$this->module->name.'/views/js/suppliers/supplierlist.js'
        );

        //data table file included
        $this->registerStylesheet(
            'datatable_bootstrap',
            'modules/marketplace/views/css/datatable_bootstrap.css'
        );
        $this->registerJavascript(
            'mp-jquery-dataTables',
            'modules/marketplace/views/js/jquery.dataTables.min.js'
        );
        $this->registerJavascript(
            'mp-dataTables.bootstrap',
            'modules/marketplace/views/js/dataTables.bootstrap.js'
        );
    }
}
