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

class AdminSellerOrdersController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->identifier = 'id_mp_order';

        $this->bootstrap = true;
        $this->list_no_link = true;
        $this->table = 'wk_mp_seller_order';
        $this->className = 'WkMpSellerOrder';

        if (!Tools::getValue('mp_seller_details') &&
            !Tools::getValue('mp_order_details') &&
            !Tools::getValue('mp_shipping_detail') &&
            !Tools::getValue('mp_seller_settlement')) {
            // unset the filter if first renderlist contain any filteration
            if (!Tools::isSubmit('wk_mp_seller_orderOrderway')) {
                unset($this->context->cookie->sellerorderswk_mp_seller_orderOrderby);
                unset($this->context->cookie->sellerorderswk_mp_seller_orderOrderway);
            }

            $this->_select = '
                a.`id_mp_order` as `temp_id1`,
                a.`seller_customer_id` as `temp_shipping1`,
                CONCAT(a.`seller_firstname`," ",a.`seller_lastname`) as seller_name,
                a.`seller_email` as email';
            $this->_where = WkMpSellerOrder::addSqlRestriction('a');
            $this->_orderBy = 'id_mp_order';


            $this->fields_list = array(
                'id_mp_order' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                ),
                'seller_shop' => array(
                    'title' => $this->l('Unique shop name'),
                    'align' => 'center',
                    'havingFilter' => true,
                ),
                'seller_name' => array(
                    'title' => $this->l('Seller name'),
                    'align' => 'center',
                    'havingFilter' => true,
                ),
                'email' => array(
                    'title' => $this->l('Seller email'),
                    'align' => 'center',
                    'havingFilter' => true,
                ),
                'count_values' => array(
                    'title' => $this->l('Total orders'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                    'orderby' => false,
                    'search' => false,
                ),
            );

            if (Configuration::get('WK_MP_COMMISSION_DISTRIBUTE_ON') == 1) {
                $this->fields_list['pending_count_values'] = array(
                    'title' => $this->l('Pending orders'),
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                    'orderby' => false,
                    'search' => false,
                    'badge_danger' => true,
                    'hint' => $this->l('Number of orders whose payment is pending.'),
                );
            }

            $this->fields_list['temp_id1'] = array(
                'title' => $this->l('Order details'),
                'align' => 'center',
                'search' => false,
                'hint' => $this->l('View product-wise seller order details'),
                'callback' => 'viewDetailBtn',
            );

            $this->fields_list['temp_shipping1'] = array(
                'title' => $this->l('Seller shipping'),
                'align' => 'center',
                'search' => false,
                'hint' => $this->l('View seller shipping earning details'),
                'callback' => 'viewSellerShippingBtn',
            );
        }

        $this->_conf['1'] = $this->l('Seller amount settled successfully.');
        $this->_conf['2'] = $this->l('Seller settled amount cancelled successfully.');
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function viewDetailBtn($id, $arr)
    {
        if ($id) {
            $html = '<span class="btn-group-action">
                        <span class="btn-group">
                            <a class="btn btn-default" href="'.self::$currentIndex.'&token='.$this->token.'&viewwk_mp_seller_order&mp_seller_details=1&id_customer_seller='.$arr['seller_customer_id'].'"><i class="icon-search-plus"></i>&nbsp;'.$this->l('View orders').'
                            </a>
                        </span>
                    </span>';

            return $html;
        }
    }

    public function viewSellerShippingBtn($id)
    {
        if ($id) {
            $html = '<span class="btn-group-action">
                        <span class="btn-group">
                            <a class="btn btn-default" href="'.self::$currentIndex.'&token='.$this->token.'&viewwk_mp_seller_order&mp_shipping_detail=1&seller_id_customer='.$id.'"><i class="icon-search-plus"></i>&nbsp;'.$this->l('View shipping').'
                            </a>
                        </span>
                    </span>';

            return $html;
        }
    }

    public function viewOrderDetail($val, $arr)
    {
        if ($val) {
            if (Tools::getValue('mp_seller_details')) {
                $val = $arr['id_order'];
            }
            $orderLink = $this->context->link->getAdminLink('AdminOrders').'&id_order='.$val.'&vieworder';
            $html = '<span class="btn-group-action">
                        <span class="btn-group">
                            <a class="btn btn-default" href="'.$orderLink.'"><i class="icon-search-plus"></i>&nbsp;'
                            .$this->l('View order detail').
                            '</a>
                        </span>
                    </span>';

            return $html;
        }
    }

    public function initSellerDetail()
    {
        $idCustomerSeller = Tools::getValue('id_customer_seller');
        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('Id order'),
                'align' => 'text-center',
                'havingFilter' => true,
                'class' => 'fixed-width-xs',
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'align' => 'center',
                'havingFilter' => false,
                'search' => false,
            ),
            'price_ti' => array(
                'title' => $this->l('Total'),
                'align' => 'center',
                'type' => 'price',
                'hint' => $this->l('Total product price tax included'),
                'currency' => true,
                'callback' => 'setOrderCurrency',
            ),
            'admin_commission' => array(
                'title' => $this->l('Admin commission'),
                'align' => 'center',
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
            ),
            'admin_tax' => array(
                'title' => $this->l('Admin tax'),
                'align' => 'center',
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
            ),
            'seller_amount' => array(
                'title' => $this->l('Seller amount'),
                'align' => 'center',
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
            ),
            'seller_tax' => array(
                'title' => $this->l('Seller tax'),
                'align' => 'center',
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
            ),
        );

        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }
        $this->fields_list['osname'] = array(
            'title' => $this->l('Status'),
            'type' => 'select',
            'color' => 'color',
            'list' => $this->statuses_array,
            'filter_key' => 'os!id_order_state',
            'filter_type' => 'int',
            'order_key' => 'osname',
            'hint' => $this->l('Order payment status'),
        );

        $this->fields_list['date_add'] = array(
            'title' => $this->l('Date'),
            'type' => 'datetime',
            'align' => 'center',
            'havingFilter' => true,
        );
        $this->addRowAction('view');

        self::$currentIndex = self::$currentIndex.'&mp_seller_details=1&viewwk_mp_seller_order&id_customer_seller='.(int) $idCustomerSeller;

        $this->context->smarty->assign(array(
            'current' => self::$currentIndex,
        ));
        $this->toolbar_btn['export'] = [
            'href' => self::$currentIndex . '&export' . $this->table . '&token=' . $this->token,
            'desc' => $this->trans('Export'),
        ];
    }

    // Override View Link For initSellerDetail() function
    public function displayViewLink($token = null, $id, $name = null)
    {
        $objWkMpSellerOrderDetail = new WkMpSellerOrderDetail($id);
        $this->context->smarty->assign(array(
            'id_order' => $objWkMpSellerOrderDetail->id_order,
            'seller_customer_id' => $objWkMpSellerOrderDetail->seller_customer_id
        ));
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'marketplace/views/templates/admin/mp_order_detail_view.tpl'
        );
    }

    public function initShippingList()
    {
        if ($idCustomerSeller = Tools::getValue('seller_id_customer')) {
            $this->fields_list = array(
                'order_id' => array(
                    'title' => $this->l('Order ID'),
                    'align' => 'center',
                ),
                'order_reference' => array(
                    'title' => $this->l('Order reference'),
                    'align' => 'center',
                ),
                'seller_earn' => array(
                    'title' => $this->l('Seller shipping earning'),
                    'align' => 'center',
                    'type' => 'price',
                    'currency' => true,
                    'callback' => 'setOrderCurrency',
                ),
                'order_date' => array(
                    'title' => $this->l('Order date'),
                    'type' => 'datetime',
                    'align' => 'center',
                    'havingFilter' => true,
                ),
            );

            self::$currentIndex = self::$currentIndex.'&mp_shipping_detail=1&viewwk_mp_seller_order&seller_id_customer='.(int) $idCustomerSeller;
        }

        $this->context->smarty->assign(array(
            'current' => self::$currentIndex,
            'shippingDetail' => 1,
        ));
    }

    public function renderView()
    {
        $this->context->smarty->assign('noListHeader', 1);
        $idCustomerSeller = Tools::getValue('id_customer_seller');
        if ($idCustomerSeller) {
            $sellerRecord = WkMpSellerOrder::getSellerRecord($idCustomerSeller);
            if ($sellerRecord && Tools::getValue('mp_seller_details')) {
                // unset the filter if first renderlist contain any filteration
                if (!Tools::isSubmit('wk_mp_seller_orderOrderway')) {
                    unset($this->context->cookie->sellerorderswk_mp_seller_orderOrderby);
                    unset($this->context->cookie->sellerorderswk_mp_seller_orderOrderway);
                }

                $this->list_no_link = true;
                $this->table = 'wk_mp_seller_order_detail';
                $this->className = 'WkMpSellerOrderDetail';
                $this->identifier = 'id_mp_order_detail';

                $this->_select = '
                    os.`color`,
                    osl.`name` AS `osname`,
                    a.`id_order` as temp_order_id,
                    sum(a.`price_ti`) as price_ti,
                    sum(a.`admin_commission`) as admin_commission,
                    sum(a.`admin_tax`) as admin_tax,
                    sum(a.`seller_amount`) as seller_amount,
                    sum(a.`seller_tax`) as seller_tax,
                    CONCAT(c.`firstname`," ",c.`lastname`) as customer';

                $this->_join = 'JOIN `'._DB_PREFIX_.'orders` ord ON (a.`id_order` = ord.`id_order`)';
                $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'customer` c ON (ord.`id_customer` = c.`id_customer`) ';
                $this->_join .= 'JOIN `'._DB_PREFIX_.'wk_mp_seller_order_status` wksos ON (a.`id_order` = wksos.`id_order` AND wksos.`id_seller` = '.(int) $sellerRecord['seller_id'].')';
                $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = wksos.`current_state`)';
                $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int) $this->context->language->id.')';

                $this->_orderBy = 'id_order';
                $this->_orderWay = 'DESC';
                $this->_where = WkMpSellerOrder::addSqlRestriction('ord');
                $this->_where .= ' AND a.`seller_customer_id` = '.(int) $sellerRecord['seller_customer_id'];
                $this->_group = 'GROUP BY a.`id_order`';

                $this->toolbar_title = $sellerRecord['seller_shop'].' > '.$this->l('View');

                $this->initSellerDetail();

                $this->actions = array();
                $this->actions[0] = 'view';

                return parent::renderList();
            }
        } elseif (Tools::getValue('mp_shipping_detail')) {
            // unset the filter if first renderlist contain any filteration
            if (!Tools::isSubmit('wk_mp_seller_orderOrderway')) {
                unset($this->context->cookie->sellerorderswk_mp_seller_orderOrderby);
                unset($this->context->cookie->sellerorderswk_mp_seller_orderOrderway);
            }

            //If seller shipping distribution is avalaible
            if ($idCustomerSeller = Tools::getValue('seller_id_customer')) {
                $this->table = 'wk_mp_seller_shipping_distribution';
                $this->identifier = 'id_seller_shipping_distribution';

                $sellerRecord = WkMpSellerOrder::getSellerRecord($idCustomerSeller);
                if ($sellerRecord) {
                    $this->toolbar_title = $sellerRecord['seller_shop'].' > '.$this->l('View');
                }

                $this->_select = 'a.`order_id` as `temp_oid`, ord.`id_currency`, ord.`date_add` as order_date';
                $this->_orderBy = 'id_seller_shipping_distribution';
                $this->list_no_link = true;

                $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'orders` ord ON (a.`order_id` = ord.`id_order`) ';

                $this->_where = WkMpSellerOrder::addSqlRestriction('ord');
                $this->_where .= ' AND a.`seller_customer_id` = '.(int) $idCustomerSeller;
            }

            $this->initShippingList();

            return parent::renderList();
        }
    }

    protected function filterToField($key, $filter)
    {
        if (Tools::getValue('mp_shipping_detail')) {
            $this->initShippingList();
        } elseif (Tools::getValue('mp_seller_details')) {
            $this->initSellerDetail();
        }

        return parent::filterToField($key, $filter);
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitResetwk_mp_seller_order') || Tools::isSubmit('submitResetwk_mp_seller_order')) {
            $this->processResetFilters();
        }
        if (Tools::isSubmit('wk_mp_seller_orderOrderway')) {
            $this->processFilter();
        }

        if (Tools::isSubmit('submitFilterwk_mp_seller_order')) {
            $this->processFilter();
        }

        if (($sellerIdCustomer = Tools::getValue('id_customer_seller'))
        && Tools::isSubmit('exportwk_mp_seller_order_detail')) {
            $this->sellerOrderExport($sellerIdCustomer);
        }

        Media::addJsDef(array(
            'current_url' => $this->context->link->getAdminLink('AdminSellerOrders')
        ));
        parent::postProcess();
    }

    public static function setOrderCurrency($val, $arr)
    {
        if (Tools::getValue('mp_shipping_detail')) {
            if (Tools::getValue('seller_id_customer')) {
                return Tools::displayPrice($val, (int) $arr['id_currency']);
            }
        } else {
            return Tools::displayPrice($val, (int) $arr['id_currency']);
        }
    }

    public function sellerOrderExport($sellerIdCustomer) {
        if ($sellerIdCustomer) {
            $sellerOrderDetails = new WkMpSellerOrderDetail();
            $allOrders = $sellerOrderDetails->getExportAllOrders($sellerIdCustomer);
            if (empty($allOrders)) {
                $this->errors = $this->l('No orders are available on selected date range.');
                return;
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
                $this->l('Order ID'),
                $this->l('Reference'),
                $this->l('Customer'),
                $this->l('Amount'),
                $this->l('Payment status'),
                $this->l('Payment method'),
                $this->l('Date'),
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

    public function getList($idLang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $idLangShop = false)
    {
        parent::getList($idLang, $orderBy, $orderWay, $start, $limit, $idLangShop);

        //echo $this->table;
        $nb_items = count($this->_list);
        if ($this->table == 'wk_mp_seller_order') {
            for ($i = 0; $i < $nb_items; ++$i) {
                $item = &$this->_list[$i];
                $query = new DbQuery();
                $query->select('COUNT(DISTINCT mcc.`id_order`) as count_values');
                $query->from('wk_mp_seller_order_detail', 'mcc');
                $query->where('mcc.id_seller_order ='.(int) $item['id_mp_order'].
                WkMpSellerOrder::addSqlRestriction('mcc'));
                $query->orderBy('count_values DESC');
                $item['count_values'] = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

                //calculating pending orders
                $item['pending_count_values'] = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                    'SELECT COUNT(DISTINCT mcc.`id_order`) FROM `'._DB_PREFIX_.'wk_mp_seller_order_detail` mcc
                    LEFT JOIN `'._DB_PREFIX_.'orders` ordr on (ordr.`id_order` = mcc.`id_order`)
                    WHERE mcc.`id_seller_order` = '.(int) $item['id_mp_order'].'
                    AND mcc.`id_order` NOT IN (
                        SELECT `id_order` FROM `'._DB_PREFIX_.'order_history` oh
                        WHERE oh.`id_order` = ordr.`id_order`
                        AND oh.`id_order_state`= '.(int) Configuration::get('PS_OS_PAYMENT').'
                    )'.WkMpSellerOrder::addSqlRestriction('mcc')
                );

                $item['badge_danger'] = true;
                unset($query);
            }
        }
    }

    public function ajaxProcessViewOrderDetail()
    {
        $idOrder = Tools::getValue('id_order');
        $sellerCustomerId = Tools::getValue('seller_customer_id');
        if ($idOrder) {
            $objWkMpSellerOrderDetail = new WkMpSellerOrderDetail();
            $result = $objWkMpSellerOrderDetail->getOrderCommissionDetails($idOrder, $sellerCustomerId);
            if ($result) {
                foreach ($result as $key => &$data) {
                    $mpProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($data['product_id']);
                    $result[$key]['seller_amount'] = Tools::displayPrice(
                        $data['seller_amount'],
                        new Currency($data['id_currency'])
                    );
                    $result[$key]['seller_tax'] = Tools::displayPrice(
                        $data['seller_tax'],
                        new Currency($data['id_currency'])
                    );
                    $result[$key]['admin_commission'] = Tools::displayPrice(
                        $data['admin_commission'],
                        new Currency($data['id_currency'])
                    );
                    $result[$key]['admin_tax'] = Tools::displayPrice(
                        $data['admin_tax'],
                        new Currency($data['id_currency'])
                    );
                    $result[$key]['price_ti'] = Tools::displayPrice(
                        $data['price_ti'],
                        new Currency($data['id_currency'])
                    );
                    if ($mpProduct['id_mp_product']) {
                        $result[$key]['product_link'] = $this->context->link->getAdminLink('AdminSellerProductDetail')
                        .'&updatewk_mp_seller_product&id_mp_product='.(int) $mpProduct['id_mp_product'];
                    }
                }

                if (_PS_VERSION_ >= '1.7.7.0') {
                    $wkOrderLink = $this->context->link->getAdminLink(
                        'AdminOrders',
                        true,
                        array('vieworder' => 1, 'id_order' => (int) $idOrder),
                        array()
                    );
                } else {
                    $wkOrderLink = $this->context->link->getAdminLink('AdminOrders')
                    .'&vieworder&id_order='.(int) $idOrder.'#start_products';
                }

                $this->context->smarty->assign(array(
                    'result' => $result,
                    'orderInfo' => $objWkMpSellerOrderDetail->getSellerOrderDetail((int) $idOrder),
                    'orderlink' => $wkOrderLink,
                ));
                $output = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_.'marketplace/views/templates/admin/seller-product-line.tpl'
                );
                die($output);
            }
        }
        die;//return false;
    }

    public function ajaxProcessChangeShippingDistributionType()
    {
        if ($idPsReference = Tools::getValue('id_ps_reference')) {
            $shippingDistributeType = Tools::getValue('shipping_distribute_type');
            //Change shipping distribution type for Ps Carriers Controller
            if (WkMpAdminShipping::updatePsShippingDistributionType($idPsReference, $shippingDistributeType)) {
                die('1');
            }
        }

        die('0');
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS(_MODULE_DIR_.'marketplace/views/js/sellertransaction.js');
    }
}
