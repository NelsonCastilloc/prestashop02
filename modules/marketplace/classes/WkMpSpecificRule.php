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

class WkMpSpecificRule
{
    /**
     * Get all product specific rules using MP product ID
     *
     * @param int mpProductId MP product id
     * @return array
     */
    public function getAllProductSpecificRule($mpProductId)
    {
        $idPSProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpProductId);
        $specificRule = SpecificPrice::getByProductId($idPSProduct);
        if ($specificRule) {
            return $specificRule;
        } else {
            return false;
        }
    }

    /**
     * Get all product specific rules using MP product ID
     *
     * @param int is_search
     * @return array
     */
    public function searchCustomer($is_search)
    {
        if ($is_search) {
            $searches = explode(' ', Tools::getValue('keywords'));
            $customers = array();
            $searches = array_unique($searches);
            foreach ($searches as $search) {
                if (!empty($search) && $results = Customer::searchByName($search, 50)) {
                    foreach ($results as $result) {
                        if ($result['active']) {
                            $customers[$result['id_customer']] = $result;
                        }
                    }
                }
            }

            if (count($customers)) {
                $to_return = array(
                    'customers' => $customers,
                    'found' => true
                );
            } else {
                $to_return = array('found' => false);
            }

            die(Tools::jsonEncode($to_return));
        }
    }

    /**
     * Apply Specific Rule to PS Product
     *
     * @param array data
     * @return array
     */
    public function processPriceAddition($data)
    {
        sleep(2);
        $id_product = $data['mp_product_id'];
        $id_currency = $data['sp_id_currency'];
        $id_country = $data['sp_id_country'];
        $id_group = $data['sp_id_group'];
        $id_customer = $data['sp_id_customer'];
        $id_edit = isset($data['editSpecificPriceId']) &&  $data['editSpecificPriceId'] ? $data['editSpecificPriceId'] : false;

        $leave_bprice = 0;
        $sp_price = -1;
        if ($id_edit) {
            $from = $data['sp_from_edit'];
            $to = $data['sp_to_edit'];
            if (isset($data['leave_bprice_edit']) && 1 == $data['leave_bprice_edit']) {
                $leave_bprice = $data['leave_bprice_edit'];
            }
            if (isset($data['sp_price_edit'])) {
                $sp_price = $data['sp_price_edit'];
            }
        } else {
            $from = $data['sp_from'];
            $to = $data['sp_to'];
            if (isset($data['leave_bprice']) && 1 == $data['leave_bprice']) {
                $leave_bprice = $data['leave_bprice'];
            }
            if (isset($data['sp_price'])) {
                $sp_price = $data['sp_price'];
            }
        }

        $price = $leave_bprice ? '-1' : $sp_price;
        $from_quantity = $data['sp_from_quantity'];
        $reduction = $data['sp_reduction'];
        $reduction_tax = $data['sp_reduction_tax'];
        $reduction_type = !$reduction ? 'amount' : $data['sp_reduction_type'];
        $reduction_type = $reduction_type == '-' ? 'amount' : $reduction_type;

        if (!$from) {
            $from = '0000-00-00 00:00:00';
        }

        if (!$to) {
            $to = '0000-00-00 00:00:00';
        }
        $id_shop = 1;       // hardcoded id_shop = 1

        $id_product_attribute = isset($data['sp_id_product_attribute']) && $data['sp_id_product_attribute']
        ? $data['sp_id_product_attribute'] : 0;  // hardcoded id_product_attribute = 0


        if (($price == '-1') && ((float)$reduction == '0') && 1) {
            die('2');   //No reduction value has been submitted
        } elseif ($to != '0000-00-00 00:00:00' && strtotime($to) < strtotime($from)) {
            die('3');   //Invalid date range
        } elseif ($reduction_type == 'percentage' && ((float)$reduction <= 0 || (float)$reduction > 100)) {
            die('4');   //Submitted reduction value (0-100) is out-of-range
        } elseif ($this->validateSpecificPrice(
            $id_product,
            $id_shop,
            $id_currency,
            $id_country,
            $id_group,
            $id_customer,
            $price,
            $from_quantity,
            $reduction,
            $reduction_type,
            $from,
            $to,
            $id_product_attribute,
            $id_edit
        )
        ) {
            $productDetail = WkMpSellerProduct::getSellerProductByIdProduct($id_product);
            if ($id_edit) {
                $id_specific_price = $data['editSpecificPriceId'];
            } else {
                $id_specific_price = 0;
            }
            // product is created in prestashop
            if ($productDetail && $productDetail['id_ps_product']) {
                //adding specific price to prestashop
                $id_specific_price = $this->addSpecificProductPriceToPs(
                    $productDetail['id_ps_product'],
                    $id_shop,
                    $id_currency,
                    $id_country,
                    $id_group,
                    $id_customer,
                    $price,
                    $from_quantity,
                    $reduction,
                    $reduction_tax,
                    $reduction_type,
                    $from,
                    $to,
                    $id_product_attribute,
                    $id_specific_price,
                    $id_edit
                );
            }
            if ($id_specific_price) {
                die('1');
            } else {
                die('0');
            }
        }
    }

    /**
     * Validate Specific Rule for PS Product
     *
     * @param int id_product
     * @param int id_shop
     * @param int id_currency
     * @param int id_country
     * @param int id_group
     * @param int id_customer
     * @param float price
     * @param int from_quantity
     * @param int reduction
     * @param int reduction_type
     * @param date from
     * @param date to
     * @param int id_combination = 0
     * @param int id_edit = false
     * @return array
     */
    public function validateSpecificPrice(
        $id_product,
        $id_shop,
        $id_currency,
        $id_country,
        $id_group,
        $id_customer,
        $price,
        $from_quantity,
        $reduction,
        $reduction_type,
        $from,
        $to,
        $id_combination = 0,
        $id_edit = false
    ) {
        if (!Validate::isUnsignedId($id_shop) || !Validate::isUnsignedId($id_currency)||
        !Validate::isUnsignedId($id_country) || !Validate::isUnsignedId($id_group)
        || !Validate::isUnsignedId($id_customer)) {
            die('5');   //'Wrong IDs'
        } elseif ((!isset($price) && !isset($reduction)) || (isset($price) && !Validate::isNegativePrice($price))
        || (isset($reduction) && !Validate::isPrice($reduction))) {
            die('6');   //Invalid price/discount amount'
        } elseif (!Validate::isUnsignedInt($from_quantity)) {
            die('7');   //'Invalid quantity'
        } elseif ($reduction && !Validate::isReductionType($reduction_type)) {
            die('8');   //Please select a discount type (amount or percentage).
        } elseif ($from && $to && (!Validate::isDateFormat($from) || !Validate::isDateFormat($to))) {
            die('9');   //The from/to date is invalid.
        } else {
            if (!$id_edit) {
                $product_detail = WkMpSellerProduct::getSellerProductByIdProduct($id_product);
                if ($product_detail) {
                    if ($product_detail['id_ps_product']) {
                        if (SpecificPrice::exists(
                            (int)$product_detail['id_ps_product'],
                            $id_combination,
                            $id_shop,
                            $id_group,
                            $id_country,
                            $id_currency,
                            $id_customer,
                            $from_quantity,
                            $from,
                            $to,
                            false
                        )) {
                            die('10');  //A specific price already exists for these parameters
                        } else {
                            return true;
                        }
                    }
                }
            } else {
                return true;
            }
        }
    }

    /**
     * Add Specific Rule for PS Product
     *
     * @param int id_product
     * @param int id_shop
     * @param int id_currency
     * @param int id_country
     * @param int id_group
     * @param int id_customer
     * @param float price
     * @param int from_quantity
     * @param int reduction
     * @param int reduction_tax
     * @param int reduction_type
     * @param date from
     * @param date to
     * @param int id_product_attribute = 0
     * @param int id_specific_price
     * @param int id_edit = false
     * @return int
     */
    public function addSpecificProductPriceToPs(
        $id_product,
        $id_shop,
        $id_currency,
        $id_country,
        $id_group,
        $id_customer,
        $price,
        $from_quantity,
        $reduction,
        $reduction_tax,
        $reduction_type,
        $from,
        $to,
        $id_product_attribute,
        $id_specific_price,
        $id_edit = false
    ) {
        if ($id_edit) {
            $specificPrice = new SpecificPrice($id_specific_price);
        } else {
            $specificPrice = new SpecificPrice();
        }

        $specificPrice->id_product = (int)$id_product;
        $specificPrice->id_shop = (int)$id_shop;
        $specificPrice->id_product_attribute = (int)$id_product_attribute;
        $specificPrice->id_currency = (int)($id_currency);
        $specificPrice->id_country = (int)($id_country);
        $specificPrice->id_group = (int)($id_group);
        $specificPrice->id_customer = (int)$id_customer;
        $specificPrice->price = (float)($price);
        $specificPrice->from_quantity = (int)($from_quantity);
        $specificPrice->reduction = (float)($reduction_type == 'percentage' ? $reduction / 100 : $reduction);
        $specificPrice->reduction_tax = (int)$reduction_tax;
        $specificPrice->reduction_type = pSQL($reduction_type);
        $specificPrice->from = $from;
        $specificPrice->to = $to;
        if (!$specificPrice->save()) {
            return 0;
        } else {
            return $specificPrice->id;
        }
    }

    /**
     * Get MP Specific rules by using MP Product ID
     *
     * @param  int  $mpProductId MP Product ID
     * @return array
     */
    public function getMPSpecificRules($mpProductId)
    {
        if ($mpProductId) {
            $productDetail = WkMpSellerProduct::getSellerProductByIdProduct($mpProductId);

            $combinationMpDetails = WkMpProductAttribute::getMpCombinationsResume($mpProductId);
            $combinationDetailsWithPsIds = array();
            if (!empty($combinationMpDetails)) {
                foreach ($combinationMpDetails as $key => $value) {
                    $idProductAttribute = $value['id_product_attribute'];
                    array_push(
                        $combinationDetailsWithPsIds,
                        array_merge($value, array('id_product_attribute' => $idProductAttribute))
                    );
                }
            }


            $obMpSpecificPrice = new WkMpSpecificRule();
            $priceRules = $obMpSpecificPrice->getAllProductSpecificRule($mpProductId);
            $currencies = Currency::getCurrencies();
            $countries = Country::getCountries(Context::getContext()->language->id);
            $groups = Group::getGroups(Context::getContext()->language->id);
            $tmp = array();
            foreach ($currencies as $currency) {
                $tmp[$currency['id_currency']] = $currency;
            }
            $currencies = $tmp;

            $tmp = array();
            foreach ($countries as $country) {
                $tmp[$country['id_country']] = $country;
            }
            $countries = $tmp;

            $tmp = array();
            foreach ($groups as $group) {
                $tmp[$group['id_group']] = $group;
            }
            $groups = $tmp;
            $objMP = new Marketplace();
            if ($priceRules) {
                $productPriceRule = array();
                // $i = 0;
                foreach ($priceRules as $key => $specificPrice) {
                    $id_currency = $specificPrice['id_currency'] ?
                    $specificPrice['id_currency'] : Configuration::get('PS_CURRENCY_DEFAULT');
                    $current_specific_currency = $currencies[$id_currency];
                    if ($specificPrice['reduction_type'] == 'percentage') {
                        $impact = '- '.($specificPrice['reduction'] * 100).' %';
                    } elseif ($specificPrice['reduction'] > 0) {
                        $impact = '- '.Tools::displayPrice(
                            Tools::ps_round($specificPrice['reduction'], 2),
                            $current_specific_currency
                        ).' ';
                        if ($specificPrice['reduction_tax']) {
                            $impact .= '('.$objMP->l('Tax incl.').')';
                        } else {
                            $impact .= '('.$objMP->l('Tax excl.').')';
                        }
                    } else {
                        $impact = '--';
                    }

                    if ($specificPrice['id_customer']) {
                        $customer = new Customer((int) $specificPrice['id_customer']);
                        if ($customer) {
                            $customer_full_name = $customer->firstname.' '.$customer->lastname;
                            unset($customer);
                        }
                    }

                    if ($specificPrice['from'] ==
                    '0000-00-00 00:00:00' && $specificPrice['to'] == '0000-00-00 00:00:00') {
                        $period = $objMP->l('Unlimited');
                    } else {
                        $period = $objMP->l('From').' '.($specificPrice['from'] !=
                        '0000-00-00 00:00:00' ? $specificPrice['from']
                        : '0000-00-00 00:00:00').'<br />'.$objMP->l('To').' '.($specificPrice['to']
                        != '0000-00-00 00:00:00' ? $specificPrice['to'] : '0000-00-00 00:00:00');
                    }
                    $price = Tools::ps_round($specificPrice['price'], 2);

                    $fixed_price = ($price == Tools::ps_round($productDetail['price'], 2)
                    || $specificPrice['price'] == -1) ? '--' :
                        Tools::displayPrice($price, $current_specific_currency);
                    $productPriceRule[$key]['id'] = $specificPrice['id_specific_price'];
                    $productPriceRule[$key]['id_currency'] = $specificPrice['id_currency'] ?
                    $currencies[$specificPrice['id_currency']]['name'] : $objMP->l('All currencies');
                    $idProductAttribute = $specificPrice['id_product_attribute'];
                    $productPriceRule[$key]['id_combiantion_name'] =  $specificPrice['id_product_attribute']  ? array_values(array_filter($combinationDetailsWithPsIds, function ($element) use ($idProductAttribute) {
                        return $element['id_product_attribute'] == $idProductAttribute;
                    }))[0]['attribute_designation'] : $objMP->l('All Combinations');


                    $productPriceRule[$key]['id_country'] = $specificPrice['id_country'] ?
                    $countries[$specificPrice['id_country']]['name'] : $objMP->l('All countries');
                    $productPriceRule[$key]['id_group'] = $specificPrice['id_group'] ?
                    $groups[$specificPrice['id_group']]['name'] : $objMP->l('All groups');
                    $productPriceRule[$key]['id_customer'] = isset($customer_full_name) ?
                    $customer_full_name : $objMP->l('All customers');
                    $productPriceRule[$key]['price'] = $fixed_price;
                    $productPriceRule[$key]['impact'] = $impact;
                    $productPriceRule[$key]['period'] = $period;
                    $productPriceRule[$key]['from_quantity'] = $specificPrice['from_quantity'];
                    unset($customer_full_name);
                }

                Context::getContext()->smarty->assign(array(
                    'priceRules' => $productPriceRule,
                ));
            }

            $productDetail = WkMpSellerProduct::getSellerProductByIdProduct($mpProductId);
            if ($productDetail) {
                Context::getContext()->smarty->assign('productDetail', $productDetail);
            }

            $specificPricePriority = SpecificPrice::getPriority($productDetail['id_ps_product']);
            Context::getContext()->smarty->assign(array(
                'updateProduct' => 1,
                'controller' => Tools::getValue('controller'),
                'currencies' => $currencies,
                'countries' => $countries,
                'groups' => $groups,
                'multi_shop' => Shop::isFeatureActive(),
                'combinationDetailsWithPsIds' => empty($combinationDetailsWithPsIds) ?
                false : $combinationDetailsWithPsIds ,
                'link' => new Link(),
                'pack' => new Pack(),
                'country_display_tax_label' => Context::getContext()->country->display_tax_label,
                'mp_product_id' => $mpProductId,
                'modules_dir' => _MODULE_DIR_,
                'specificPricePriority' => $specificPricePriority,
            ));
        }
    }

    /**
     * Helper function
     * Assign Add MP Specific Rules Variables
     */
    public function assignAddMPSpecificRulesVars()
    {
        $currencies = Currency::getCurrencies();
        $countries = Country::getCountries(Context::getContext()->language->id);
        $groups = Group::getGroups(Context::getContext()->language->id);
        Context::getContext()->smarty->assign(array(
            'controller' => Tools::getValue('controller'),
            'currencies' => $currencies,
            'countries' => $countries,
            'groups' => $groups,
            'multi_shop' => Shop::isFeatureActive(),
            'link' => new Link(),
            'pack' => new Pack(),
            'country_display_tax_label' => Context::getContext()->country->display_tax_label,
            'modules_dir' => _MODULE_DIR_,
        ));
    }

    /**
     * Helper function
     * Add MP Specific Rules
     */
    public function addMpSpecificRules($mpIdProduct)
    {
        if ($mpIdProduct && (Tools::getValue('sp_price') > 0 || Tools::getValue('sp_reduction') > 0)) {
            $leave_bprice = 0;
            if (1 == Tools::getValue('leave_bprice')) {
                $leave_bprice = Tools::getValue('leave_bprice');
            }
            $sp_price = -1;
            if (Tools::getValue('sp_price')) {
                $sp_price = Tools::getValue('sp_price');
            }
            $price = $leave_bprice ? '-1' : $sp_price;
            $from_quantity = Tools::getValue('sp_from_quantity');
            $reduction = (float) Tools::getValue('sp_reduction');
            $reduction_tax = Tools::getValue('sp_reduction_tax');
            // $specificPrice = Tools::getValue('sp_price');
            $reduction_type = !$reduction ? 'amount' : Tools::getValue('sp_reduction_type');
            $reduction_type = $reduction_type == '-' ? 'amount' : $reduction_type;
            $from = Tools::getValue('sp_from');
            if (!$from) {
                $from = '0000-00-00 00:00:00';
            }
            $to = Tools::getValue('sp_to');
            if (!$to) {
                $to = '0000-00-00 00:00:00';
            }
            $id_shop = Context::getContext()->shop->id;       // hardcoded id_shop = 1
            $id_product_attribute = 0;  // hardcoded id_product_attribute = 0
            $id_currency = Tools::getValue('sp_id_currency');
            $id_country = Tools::getValue('sp_id_country');
            $id_group = Tools::getValue('sp_id_group');
            $id_customer = Tools::getValue('sp_id_customer');

            $productDetail = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct);
            $obMpSpecificPrice = new WkMpSpecificRule();
            $id_specific_price = 0;
            // product is created in prestashop
            if ($productDetail && $productDetail['id_ps_product']) {
                //adding specific price to prestashop
                $id_specific_price = $obMpSpecificPrice->addSpecificProductPriceToPs(
                    $productDetail['id_ps_product'],
                    $id_shop,
                    $id_currency,
                    $id_country,
                    $id_group,
                    $id_customer,
                    $price,
                    $from_quantity,
                    $reduction,
                    $reduction_tax,
                    $reduction_type,
                    $from,
                    $to,
                    $id_product_attribute,
                    $id_specific_price
                );
            }
        }
    }
}
