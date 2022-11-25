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

class MarketplaceMpPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $seller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($seller && $seller['active']) {
                $mpPayment = new WkMpCustomerPayment();
                //if seller edit or delete payment details
                if ($idMpPayment = Tools::getValue('id')) {
                    if ($paymentDetail = WkMpCustomerPayment::getPaymentDetailById($idMpPayment)) {
                        if ($paymentDetail['seller_customer_id'] == $this->context->customer->id) { //Check same seller
                            if (Tools::getValue('delete_payment')) {
                                //if seller delete payment mode
                                $objPaymentDetail = new WkMpCustomerPayment($idMpPayment);
                                if ($objPaymentDetail->delete()) {
                                    Tools::redirect(
                                        $this->context->link->getModuleLink(
                                            'marketplace',
                                            'mppayment',
                                            array('deleted' => 1)
                                        )
                                    );
                                }
                            } else {
                                $this->context->smarty->assign('edit', 1);
                            }
                        } else {
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'dashboard'));
                        }
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'mppayment'));
                    }
                }

                //get all admin payment option
                if ($adminPaymentOption = WkMpSellerPaymentMode::getPaymentMode()) {
                    $this->context->smarty->assign('mp_payment_option', $adminPaymentOption);
                }

                //get seller selected payment
                if ($sellerPayments = $mpPayment->getPaymentDetailByIdCustomer($this->context->customer->id)) {
                    $this->context->smarty->assign('seller_payment_details', $sellerPayments);
                }

                $this->context->smarty->assign(array(
                        'customer_id' => $this->context->customer->id,
                        'is_seller' => $seller['active'],
                        'logic' => 6,
                    ));

                Media::addJsDef(array(
                        'required_payment' => $this->module->l('Payment mode is required field.', 'mppayment'),
                        'confirm_msg' => $this->module->l('Are you sure want to delete?', 'mppayment'),
                    ));

                $this->setTemplate('module:marketplace/views/templates/front/mppayment.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('marketplace', 'mppayment')));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit_payment_details') && $this->context->customer->id) {
            $seller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($seller && $seller['active']) {
                $idMpPayment = Tools::getValue('id');
                $paymentMode = Tools::getValue('payment_mode_id');
                $paymentDetail = Tools::getValue('payment_detail');

                if (!$paymentMode) {
                    $this->errors[] = $this->module->l('Payment mode is required field.', 'mppayment');
                }

                if (empty($this->errors)) {
                    if ($idMpPayment) {
                        $mpPayment = new WkMpCustomerPayment($idMpPayment);
                    } else {
                        $mpPayment = new WkMpCustomerPayment();
                    }

                    $mpPayment->seller_customer_id = (int)$this->context->customer->id;
                    $mpPayment->payment_mode_id = (int)$paymentMode;
                    $mpPayment->payment_detail = pSQL($paymentDetail);
                    if ($mpPayment->save()) {
                        if ($idMpPayment) {
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'mppayment', array('edited' => 1)));
                        } else {
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'mppayment', array('created' => 1)));
                        }
                    }
                }
            }
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'mppayment'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Payment', 'mppayment'),
            'url' => ''
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet('marketplace_account-css', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerJavascript('mp_form_validation-js', 'modules/'.$this->module->name.'/views/js/mp_form_validation.js');
    }
}
