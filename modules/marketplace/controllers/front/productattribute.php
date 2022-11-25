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

class MarketplaceProductAttributeModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller['active'] && Configuration::get('WK_MP_PRESTA_ATTRIBUTE_ACCESS')) {
                // Delete Attribute group
                if (Tools::getValue('delete_attribute')) {
                    $deleteSuccess = 0;
                    if ($idGroup = Tools::getValue('id_group')) {
                        if (!(WkMpProductAttribute::checkCombinationByGroup($this->context->language->id, $idGroup))) {
                            $objAttributeGroup = new AttributeGroup($idGroup);
                            if ($objAttributeGroup->delete()) {
                                $deleteSuccess = 1;
                                Tools::redirect(
                                    $this->context->link->getModuleLink(
                                        'marketplace',
                                        'productattribute',
                                        array('deleted' => 1)
                                    )
                                );
                            }
                        }
                    }
                    if (!$deleteSuccess) {
                        $this->errors[] = $this->module->l('This Attribute group is already in use you cannot edit or delete it.', 'productattribute');
                    }
                }

                //Get List of Attribute Group
                $attibuteGroup = AttributeGroup::getAttributesGroups($this->context->language->id);
                $attributeSet = array();
                foreach ($attibuteGroup as $attibuteGroupEach) {
                    $countValue = 0;
                    $i = $attibuteGroupEach['id_attribute_group'];
                    $attributeSet[$i]['name'] = $attibuteGroupEach['name'];
                    $attributeSet[$i]['public_name'] = $attibuteGroupEach['public_name'];
                    $attributeSet[$i]['group_type'] = $attibuteGroupEach['group_type'];
                    $attributeSet[$i]['id'] = $attibuteGroupEach['id_attribute_group'];
                    $countValue = count(
                        AttributeGroup::getAttributes(
                            $this->context->language->id,
                            $attibuteGroupEach['id_attribute_group']
                        )
                    );
                    $attributeSet[$i]['count_value'] = $countValue;
                    if (WkMpProductAttribute::checkCombinationByGroup(
                        $this->context->language->id,
                        $attibuteGroupEach['id_attribute_group']
                    )) {
                        $attributeSet[$i]['editable'] = 0;
                    } else {
                        $attributeSet[$i]['editable'] = $attibuteGroupEach['id_attribute_group'];
                    }
                }
                ksort($attributeSet);

                $this->context->smarty->assign('logic', 'mp_prod_attribute');
                $this->context->smarty->assign('attributeSet', $attributeSet);
                $this->defineJSVars();
                $this->setTemplate(
                    'module:marketplace/views/templates/front/product/combination/productattribute.tpl'
                );
            } else {
                Tools::redirect(__PS_BASE_URI__.'pagenotfound');
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
                'error_msg1' => $this->module->l('This Attribute group is already in use you cannot edit or delete it.', 'productattribute'),
                'confirm_delete' => $this->module->l('Are you sure?', 'productattribute'),
                'display_name' => $this->module->l('Display', 'productattribute'),
                'records_name' => $this->module->l('records per page', 'productattribute'),
                'no_product' => $this->module->l('No data found', 'productattribute'),
                'show_page' => $this->module->l('Showing page', 'productattribute'),
                'show_of' => $this->module->l('of', 'productattribute'),
                'no_record' => $this->module->l('No records available', 'productattribute'),
                'filter_from' => $this->module->l('filtered from', 'productattribute'),
                't_record' => $this->module->l('total records', 'productattribute'),
                'search_item' => $this->module->l('Search', 'productattribute'),
                'p_page' => $this->module->l('Previous', 'productattribute'),
                'n_page' => $this->module->l('Next', 'productattribute'),
            );

        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'productattribute'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Product Attribute', 'productattribute'),
            'url' => '',
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->registerStylesheet(
            'mp-marketplace_account',
            'modules/'.$this->module->name.'/views/css/marketplace_account.css'
        );
        $this->registerJavascript(
            'mp-productattribute',
            'modules/'.$this->module->name.'/views/js/productattribute.js'
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
        $this->registerJavascript(
            'wk-mp-dataTables',
            'modules/'.$this->module->name.'/views/js/wk_mp_datatables.js'
        );
    }
}
