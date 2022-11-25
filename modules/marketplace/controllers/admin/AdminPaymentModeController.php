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

class AdminPaymentModeController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'wk_mp_payment_mode';
        $this->className = 'WkMpSellerPaymentMode';
        $this->identifier = 'id_mp_payment';
        parent::__construct();

        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
            //In case of All Shops
            $this->_select = 'shp.`name` as wk_ps_shop_name';
            $this->_join .= 'JOIN `'._DB_PREFIX_.'shop` shp ON (shp.`id_shop` = a.`id_shop`)';
        } else {
            $this->_where .= ' AND a.`id_shop` = '.(int) $this->context->shop->id;
        }

        $this->fields_list = array(
            'id_mp_payment' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'payment_mode' => array(
                'title' => $this->l('Payment mode'),
                'width' => '100',
            ),
        );
        if (WkMpHelper::isMultiShopEnabled()) {
            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
                //In case of All Shops
                $this->fields_list['wk_ps_shop_name'] = array(
                    'title' => $this->l('Shop'),
                    'havingFilter' => true,
                );
            }
        }
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ),
        );
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
            'desc' => $this->l('Add payment mode'),
        );
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        return parent::renderList();
    }

    public function renderForm()
    {
        if ((Shop::getContext() !== Shop::CONTEXT_SHOP) && (Shop::getContext() !== Shop::CONTEXT_ALL)) {
            //For shop group
            $this->errors[] = $this->l('You can not add or edit a payment mode in this shop context: select a shop instead of a group of shops.');
        } else {
            $this->fields_form = array(
                'legend' => array(
                    'title' => $this->l('Manage Payment Mode'),
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => 'payment_mode',
                        'label' => $this->l('Payment mode'),
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitPaymentMode',
                ),
            );
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitPaymentMode')) {
            $wkPaymentMode = trim(Tools::getValue('payment_mode'));
            if ($wkPaymentMode) {
                $idPaymentMode = Tools::getValue('id_mp_payment');
                if ($idPaymentMode) {
                    $objPaymentMode = new WkMpSellerPaymentMode((int) $idPaymentMode);
                } else {
                    $objPaymentMode = new WkMpSellerPaymentMode();
                }
                $objPaymentMode->id_shop = (int) $this->context->shop->id;
                $objPaymentMode->payment_mode = pSQL($wkPaymentMode);
                $objPaymentMode->save();

                if ($idPaymentMode) {
                    Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                } else {
                    Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                }
            }
        }
        parent::postProcess();
    }
}
