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

class MarketplaceManufacturerProductListModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $link = new Link();
        if ($this->context->customer->id) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller && $mpSeller['active']) {
                if (!Configuration::get('WK_MP_PRODUCT_MANUFACTURER')) {
                    Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                }

                $objMpManuf = new WkMpManufacturers();

                $mpManufId = Tools::getValue('mp_manuf_id');
                if ($mpManufId) {
                    $manufData = $objMpManuf->getMpManufacturerAllDetails($mpManufId);
                    if ($manufData['id_seller'] == $mpSeller['id_seller']) {
                        $manufacturerName = $manufData['name'];
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

                        $this->context->smarty->assign('manuf_name', $manufacturerName);
                        $this->context->smarty->assign('id_lang', $this->context->language->id);
                        $this->context->smarty->assign('logic', 'mpmanufacturerlist');
                        Media::addJsDef(
                            array(
                                'redirect_link' => $this->context->link->getModuleLink(
                                    'marketplace',
                                    'mpmanufacturerlist',
                                    array('deleted' => 1)
                                ),
                                'delete_manufacturer_prod' => $this->context->link->getModuleLink(
                                    'marketplace',
                                    'mpmanufacturerlist'
                                ),
                                'module_dir' => _MODULE_DIR_,
                                'confirm_msg' => $this->module->l('Are you sure ?', 'manufacturerproductlist'),
                                'static_token' => Tools::getToken(false),
                            )
                        );

                        $this->setTemplate('module:marketplace/views/templates/front/product/manufacturers/manufacturer_productlist.tpl');
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'dashboard'));
                    }
                } else {
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'dashboard'));
                }
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect('index.php?controller=authentication&back='.
            urlencode($link->getModuleLink('marketplace', 'mpmanufacturerproductlist')));
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->l('Marketplace', 'Manufacturer_ProductList'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        ];

        $breadcrumb['links'][] = [
            'title' => $this->l('Manufacturer Products', 'Manufacturer_ProductList'),
            'url' => '',
        ];

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerJavascript(
            'validate_manufacturer-js',
            'modules/'.$this->module->name.'/views/js/manufacturers/validate_manufacturer.js'
        );
        $this->registerStylesheet(
            'addmanufacturer-css',
            'modules/'.$this->module->name.'/views/css/addmanufacturer.css'
        );
        $this->registerStylesheet('marketplace_account', 'modules/marketplace/views/css/marketplace_account.css');
    }
}
