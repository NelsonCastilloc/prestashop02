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

class MarketplaceMpSupplierProductsListModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->isLogged()) {
            $id = Tools::getValue('id');
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller && $mpSeller['active'] && $id) {
                if (!Configuration::get('WK_MP_PRODUCT_SUPPLIER')) {
                    Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                }

                $objMpSupplier = new WkMpSuppliers();
                $supplierInfo = $objMpSupplier->getMpSupplierAllDetails($id);
                $smartyVars = array(
                    'logic' => 'mpsupplierlist',
                    'supplierInfo' => $supplierInfo,
                    'title_bg_color' => Configuration::get('WK_MP_TITLE_BG_COLOR'),
                    'title_text_color' => Configuration::get('WK_MP_TITLE_TEXT_COLOR')
                );

                $productList = WkMpSuppliers::getProductListByMpSupplierIdAndIdSeller(
                    $id,
                    $mpSeller['id_seller'],
                    $this->context->language->id
                );
                if ($productList) {
                    $smartyVars['productList'] = $productList;
                }

                $this->context->smarty->assign($smartyVars);
                $this->setTemplate('module:marketplace/views/templates/front/product/suppliers/supplierproductslist.tpl');
            } else {
                Tools::redirect($this->context->link->getPageLink('my-account'));
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'mpsupplierproductslist'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Supplier', 'mpsupplierproductslist'),
            'url' => $this->context->link->getModuleLink('marketplace', 'mpsupplierlist')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Supplier product list', 'mpsupplierproductslist'),
            'url' => ''
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->registerStylesheet(
            'mp-marketplace_account',
            'modules/marketplace/views/css/marketplace_account.css'
        );
    }
}
