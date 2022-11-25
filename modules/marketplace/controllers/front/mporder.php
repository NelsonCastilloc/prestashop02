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

class MarketplaceMpOrderModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $idCustomer = $this->context->customer->id;
            //Override customer id if any staff of seller want to use this controller
            if (Module::isEnabled('mpsellerstaff')) {
                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                // ---------- Get Seller's Order Records ---------//
                $objMpOrder = new WkMpSellerOrder();
                $objOrderStatus = new WkMpSellerOrderStatus();
                if ($mporders = $objMpOrder->getSellerOrders($this->context->language->id, $idCustomer)) {
                    foreach ($mporders as &$order) {
                        $objOrder = new Order($order['id_order']);
                        if (!$objMpOrder->checkSellerOrder($objOrder, $seller['id_seller'])) {
                            $idOrderState = $objOrderStatus->getCurrentOrderState(
                                $order['id_order'],
                                $seller['id_seller']
                            );
                            if ($idOrderState) {
                                $state = new OrderState($idOrderState, $this->context->language->id);
                                $order['order_status'] = $state->name;
                            }
                        }
                        $order['buyer_info'] = new Customer($order['buyer_id_customer']);
                        if ($sellerOrderTotal = $objMpOrder->getTotalOrder($order['id_order'], $idCustomer)) {
                            //Add shipping amount in total orders
                            if ($sellerShippingEarning = WkMpAdminShipping::getSellerShippingByIdOrder(
                                $order['id_order'],
                                $idCustomer
                            )) {
                                $sellerOrderTotal += $sellerShippingEarning;
                            }

                            $order['total_paid'] = Tools::displayPrice(
                                $sellerOrderTotal,
                                (int) $order['id_currency']
                            );
                            $order['total_paid_without_sign'] = $sellerOrderTotal;
                        }
                    }

                    $this->context->smarty->assign('mporders', $mporders);
                }
                //----- End of Seller's order records ------
                Media::addJsDef(array('id_seller' => $seller['id_seller']));
                $this->context->smarty->assign(array(
                        'logic' => 4,
                        'is_seller' => $seller['active'],
                    ));

                $this->defineJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/order/mporder.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect(
                'index.php?controller=authentication&back='.
                urlencode($this->context->link->getModuleLink('marketplace', 'mporder'))
            );
        }
    }

    public function postProcess()
    {
        if (Configuration::get('WK_MP_SELLER_EXPORT')
        && (Tools::isSubmit('mp_csv_order_export') || Tools::getValue('export_all'))) {
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
                $idCustomer = $this->context->customer->id;
                if ($idCustomer) {
                    $this->exportOrdersCSV($idCustomer, $fromExportDate, $toExportDate, $exportAll);
                }
            }
        }

        parent::postProcess();
    }

    public function exportOrdersCSV($idCustomer, $fromExportDate, $toExportDate, $exportAll)
    {
        if ($fromExportDate && $toExportDate && $idCustomer) {
            $sellerOrderDetails = new WkMpSellerOrderDetail();
            if ($exportAll) {
                $allOrders = $sellerOrderDetails->getExportAllOrders($idCustomer);
                if (empty($allOrders)) {
                    $this->errors = $this->module->l('There is no orders are available.', 'mporder');
                    return;
                }
            } else {
                $allOrders = $sellerOrderDetails->getExportAllOrders($idCustomer, $fromExportDate, $toExportDate);
                if (empty($allOrders)) {
                    $this->errors = $this->module->l(
                        'No orders are available on selected date range.',
                        'mporder'
                    );
                    return;
                }
            }
            $idLang = Context::getContext()->language->id;
            $fileName = "ordercsv_".date("Y-m-d_H:i", time()).".csv";
            header('Content-Type: text/csv');
            header('Content-Type: application/force-download; charset=UTF-8');
            header('Cache-Control: no-store, no-cache');
            header('Content-Disposition: attachment; filename='.$fileName);
            ob_end_clean();
            $output = fopen('php://output', 'w');
            fputcsv($output, array(
                $this->module->l('Order ID', 'mporder'),
                $this->module->l('Reference', 'mporder'),
                $this->module->l('Customer', 'mporder'),
                $this->module->l('Amount', 'mporder'),
                $this->module->l('Payment Status', 'mporder'),
                $this->module->l('Payment Method', 'mporder'),
                $this->module->l('Date', 'mporder'),
            ));
            if ($allOrders) {
                $count = 1;
                foreach ($allOrders as $eachOrderCsvData) {
                    $csvData = array();
                    $orderObj = new Order($eachOrderCsvData['id_order']);
                    $customerObj = new Customer($orderObj->id_customer);
                    $orderStateObj = new OrderState($orderObj->current_state);
                    $objCurrency = new Currency($orderObj->id_currency);
                    $csvData['ID_entry'] = $orderObj->id;
                    $csvData['shop_order_number'] = $orderObj->reference;
                    $csvData['customer'] = $customerObj->firstname.' '.$customerObj->lastname;
                    $csvData['amount'] = $objCurrency->symbol . $eachOrderCsvData['price_ti'];
                    $csvData['payment_status'] = $orderStateObj->name[$idLang];
                    $csvData['provider_name'] = $orderObj->payment;
                    $csvData['created_at_dat'] = $eachOrderCsvData['date_add'];
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
                'mporder_link' => $this->context->link->getModuleLink('marketplace', 'mporder'),
                'mporderdetails_link' => $this->context->link->getModuleLink('marketplace', 'mporderdetails'),
                'display_name' => $this->module->l('Display', 'mporder'),
                'records_name' => $this->module->l('records per page', 'mporder'),
                'no_product' => $this->module->l('No order found', 'mporder'),
                'show_page' => $this->module->l('Showing page', 'mporder'),
                'show_of' => $this->module->l('of', 'mporder'),
                'no_record' => $this->module->l('No records available', 'mporder'),
                'filter_from' => $this->module->l('filtered from', 'mporder'),
                't_record' => $this->module->l('total records', 'mporder'),
                'search_item' => $this->module->l('Search', 'mporder'),
                'p_page' => $this->module->l('Previous', 'mporder'),
                'n_page' => $this->module->l('Next', 'mporder'),
                'empty_from_date' => $this->module->l('Please select from date.', 'mporder'),
                'empty_to_date' => $this->module->l('Please select to date.', 'mporder'),
                'compare_date_error' => $this->module->l('To date must be greater than from date.', 'mporder'),
            );

        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            $jsVars['friendly_url'] = 1;
        } else {
            $jsVars['friendly_url'] = 0;
        }
        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'mporder'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Orders', 'mporder'),
            'url' => '',
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addjQueryPlugin('growl', null, false);
        $this->registerStylesheet(
            'marketplace_account',
            'modules/'.$this->module->name.'/views/css/marketplace_account.css'
        );
        $this->registerStylesheet(
            'marketplace_global',
            'modules/'.$this->module->name.'/views/css/mp_global_style.css'
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
            'mp-order',
            'modules/'.$this->module->name.'/views/js/mporder.js'
        );
    }
}
