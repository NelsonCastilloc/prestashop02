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

class WkMpProductFeature
{
    /**
     * Get Product feature value with custom values if exist using feature id.
     *
     * @param int $idFeature Prestashop Product Feature ID
     *
     * @return array/bool
     */
    public static function getFeatureValue($idFeature)
    {
        $featuresValue = false;
        if ($idFeature) {
            $featuresValue = FeatureValue::getFeatureValuesWithLang(
                Context::getContext()->language->id,
                (int) $idFeature
            );
        }

        return $featuresValue;
    }

    /**
     * Get Product Feature Prefined Value Using Product ID Feature.
     *
     * @param int $idFeatureValue Prestashop Id Feature
     *
     * @return array/bool
     */
    public static function getFeatureValues($idFeatureValue)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'feature_value`
            WHERE `id_feature_value` = '.(int) $idFeatureValue
        );
    }

    /**
     * Adding custom values created by seller into prestashop table.
     *
     * @param int  $idValue Feature Value ID
     * @param int  $lang    ID Language
     * @param bool $customVal    Is it custom value or not 1/0
     */
    public static function addFeaturesCustomToPS($idValue, $lang, $customVal)
    {
        $featureValData = array(
            'id_feature_value' => (int) $idValue,
            'id_lang' => (int) $lang,
            'value' => pSQL($customVal)
        );
        Db::getInstance()->insert('feature_value_lang', $featureValData);

        return (int) Db::getInstance()->Insert_ID();
    }

    /**
     * Adding Marketplace feature into prestashop.
     *
     * @param int  $idPsProduct Prestashop Product ID
     * @param int  $idFeature   Prestashop feature ID
     * @param int  $idValue     Feature Value ID
     * @param bool $customVal        true/false
     */
    public static function addFeaturesToPS($idPsProduct, $idFeature, $idValue, $customVal = 0)
    {
        if ($customVal) {
            $featureValData = array(
                'id_feature' => (int) $idFeature,
                'custom' => 1
            );
            Db::getInstance()->insert('feature_value', $featureValData);
            $idValue = (int) Db::getInstance()->Insert_ID();
        }

        $featureValData = array(
            'id_feature' => (int) $idFeature,
            'id_product' => (int) $idPsProduct,
            'id_feature_value' => (int) $idValue
        );

        if (_PS_VERSION_ >= '1.7.3.0') {
            // If prestashop version is greater than and equal to V1.7.3.0 then we manage multifeature functionality
            // Means - One feature id can have multiple values
            $psFeatureExist = self::checkPsProductMultiFeature($idFeature, $idPsProduct, $idValue);
        } else {
            // If prestashop version is lest than V1.7.3.0 then we manage single feature functionality
            // Means - One feature id can have only one value
            $psFeatureExist = self::checkPsProductFeature($idFeature, $idPsProduct);
        }

        if (!$psFeatureExist) {
            Db::getInstance()->insert('feature_product', $featureValData);
        }

        SpecificPriceRule::applyAllRules(array((int) $idPsProduct));
        if ($idValue) {
            return ($idValue);
        }
    }

    /**
     * If PS_VERSION >= 1.7.3.0 THEN Check duplicate prestashop feature id for particular product
     *
     * @param int $idFeature      Prestashop feature ID
     * @param int $idPsProduct    Prestashop Product ID
     *
     * @return bool
     */
    public static function checkPsProductFeature($idFeature, $idPsProduct)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'feature_product`
            WHERE `id_product` = '.(int) $idPsProduct.'
            AND `id_feature` ='.(int) $idFeature
        );
    }

    /**
     * If PS_VERSION >= 1.7.3.0 THEN Check duplicate prestashop feature value with same feature Id.
     *
     * @param int $idFeature      Prestashop feature ID
     * @param int $idPsProduct    Prestashop Product ID
     * @param int $idFeatureValue    Prestashop feature value
     *
     * @return bool
     */
    public static function checkPsProductMultiFeature($idFeature, $idPsProduct, $idFeatureValue)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'feature_product
            WHERE `id_product` = '.(int) $idPsProduct.'
            AND `id_feature` ='.(int) $idFeature.'
            AND `id_feature_value` ='.(int) $idFeatureValue
        );
    }

    /**
     * Assigning Product Feature On Smarty template.
     *
     * @param int $idPsProduct PS ID Product
     *
     * @return bool
     */
    public static function assignProductFeatureOnTpl($idPsProduct)
    {
        $product = new Product($idPsProduct);
        $features = $product->getFeatures();
        if (!empty($features)) {
            $mpValueArr = array();
            foreach ($features as $key => $value) {
                $features[$key]['field_value_option'] = FeatureValue::getFeatureValuesWithLang(
                    Context::getContext()->language->id,
                    (int) $value['id_feature']
                );
                $mpFeatureValues = Db::getInstance()->executeS(
                    'SELECT * FROM `'._DB_PREFIX_.'feature_value_lang`
                    WHERE `id_feature_value` = '.(int) $value['id_feature_value']
                );
                if ($mpFeatureValues) {
                    foreach ($mpFeatureValues as $mpvalue) {
                        $mpValueArr[$mpvalue['id_lang']] = $mpvalue;
                    }

                    $features[$key]['mp_field_value'] = $mpValueArr;
                }
            }
            Context::getContext()->smarty->assign('productfeature', $features);
        }
    }

    /**
     * Deleting Product features from marketplace and prestashop.
     *
     * @param int $idMpProduct Marketplace Product ID
     *
     * @return bool
     */
    public static function deleteProductFeature($idMpProduct)
    {
        if ($idPsProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($idMpProduct)) {
            $objProduct = new Product($idPsProduct);
            return $objProduct->deleteFeatures();
        }
    }

    /**
     * Processing product features to add them into prestashop.
     *
     * @param int $idPsProduct Prestashop Product ID
     * @param array $productFeatures Selected features array
     * @param int $defaultLang Seller default lang ID
     *
     * @return bool
     */
    public static function saveProductFeature($idPsProduct, $productFeatures, $defaultLang)
    {
        //Delete PS product feature in case of update
        $psProductFeatures = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'feature_product`
            WHERE `id_product` ='.(int) $idPsProduct
        );
        if ($psProductFeatures) {
            $objProduct = new Product($idPsProduct);
            $objProduct->deleteFeatures();
        }

        if ($productFeatures) {
            foreach ($productFeatures as $feature) {
                $idFeature = $feature['id_feature'];
                if ($idFeature) {
                    $psIdFeatureValue = $feature['id_feature_value'];
                    $customValue = trim($feature['custom_value']);
                    $featureValueExist = self::getFeatureValue($idFeature);
                    if ($featureValueExist) {
                        //Pre-defined value priority is highen than custom value
                        if ($psIdFeatureValue) {
                            //if pre-defined value is selected then save that value
                            self::addFeaturesToPS($idPsProduct, $idFeature, $psIdFeatureValue, 0);
                        } elseif ($customValue) {
                            //if predefined is not selected and custom value is given
                            self::createPsCustomValue($idPsProduct, $idFeature, $customValue, $defaultLang);
                        }
                    } else {
                        if ($customValue) {
                            //if predefined is not selected and custom value is given
                            self::createPsCustomValue($idPsProduct, $idFeature, $customValue, $defaultLang);
                        }
                    }
                }
            }
        }
    }

    /**
     * Create custom value in PS
     *
     * @param int $idPsProduct PS Product ID
     * @param int $idFeature Feature ID
     * @param int $customValue Custom Value
     * @param int $defaultLang Seller default lang ID
     *
     * @return int
     */
    public static function createPsCustomValue($idPsProduct, $idFeature, $customValue, $defaultLang)
    {
        if ($idValue = self::addFeaturesToPS($idPsProduct, $idFeature, 0, 1)) {
            $idCustomFeature = self::addFeaturesCustomToPS(
                $idValue,
                (int) $defaultLang,
                $customValue
            );
            if ($idCustomFeature) {
                return self::addFeaturesToPS(
                    $idPsProduct,
                    $idFeature,
                    0,
                    $idCustomFeature
                );
            }
        }
        return false;
    }

    /**
     * Validating All the features and their values.
     *
     * @param int $params Array of features and their values
     *
     * @return int
     */
    public static function checkFeatures($params)
    {
        $className = 'WkMpProductFeature';
        $objMp = new Marketplace();
        $data = array('status' => 'ok');
        if (!isset($params['default_lang'])) {
            $params['default_lang'] = $params['seller_default_lang'];
        }
        $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($params['default_lang']);
        $wkFeatureRow = $params['wk_feature_row'];
        $rules = call_user_func(array('FeatureValue', 'getValidationRules'), 'FeatureValue');
        if ($wkFeatureRow) {
            for ($i = 1; $i <= $wkFeatureRow; ++$i) {
                $idFeature = isset($params['wk_mp_feature_'.$i]) ? $params['wk_mp_feature_'.$i] : false;
                $psIdFeatureValue = isset($params['wk_mp_feature_val_'.$i]) ? $params['wk_mp_feature_val_'.$i] : false;
                if ($idFeature) {
                    $predefinedValue = self::getFeatureValue($idFeature);
                    $customValue = false;
                    if (isset($params['wk_mp_feature_custom_'.$defaultLang.'_'.$i])) {
                        $customValue = $params['wk_mp_feature_custom_'.$defaultLang.'_'.$i];
                    }

                    if ($predefinedValue) {
                        if (!$psIdFeatureValue && !$customValue) {
                            $sellerLang = Language::getLanguage((int) $defaultLang);
                            $data = array(
                                'status' => 'ko',
                                'tab' => 'wk-feature',
                                'multilang' => '0',
                                'inputName' => 'wk_mp_feature_val',
                                'msg' => sprintf(
                                    $objMp->l('Feature value is required in %s', $className),
                                    $sellerLang['name']
                                )
                            );
                            die(Tools::jsonEncode($data));
                        } else {
                            if ($customValue) {
                                self::checkCustomFeatureValue($params, $rules, $i, $objMp, $className);
                            } elseif (!$psIdFeatureValue) {
                                $data = array(
                                    'status' => 'ko',
                                    'tab' => 'wk-feature',
                                    'multilang' => '0',
                                    'inputName' => 'wk_mp_feature_val',
                                    'msg' => $objMp->l('Feature value is not valid', $className)
                                );
                                die(Tools::jsonEncode($data));
                            }
                        }
                    } else {
                        if ($customValue) {
                            self::checkCustomFeatureValue($params, $rules, $i, $objMp, $className);
                        } else {
                            $sellerLang = Language::getLanguage((int) $defaultLang);
                            $data = array(
                                'status' => 'ko',
                                'tab' => 'wk-feature',
                                'multilang' => '0',
                                'inputName' => 'wk_mp_feature_val',
                                'msg' => sprintf(
                                    $objMp->l('Feature value is required in %s', $className),
                                    $sellerLang['name']
                                )
                            );
                            die(Tools::jsonEncode($data));
                        }
                    }
                }
            }
        }
        die(Tools::jsonEncode($data));
    }

    /**
     * Validating all the custom features values with language wise.
     *
     * @param int   $params Array containing all the information of product features
     * @param array $rules  Rules for product features definded by prestashop
     * @param int   $i      Iteration loop value
     *
     * @return int
     */
    public static function checkCustomFeatureValue($params, $rules, $i, $objMp, $className)
    {
        foreach (Language::getLanguages(false) as $language) {
            $customIdLang = $language['id_lang'];
            if (!isset($params['wk_mp_feature_custom_'.$language['id_lang'].'_'.$i])) {
                $customIdLang = $params['default_lang'];
            }
            $customValue = trim($params['wk_mp_feature_custom_'.$customIdLang.'_'.$i]);
            if (Tools::strlen($customValue) > $rules['sizeLang']['value']) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-feature',
                    'multilang' => '0',
                    'inputName' => 'wkmp_feature_custom',
                    'msg' => $objMp->l('Feature value is too long', $className)
                );
                die(Tools::jsonEncode($data));
            } elseif (!call_user_func(array('Validate', $rules['validateLang']['value']), $customValue)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-feature',
                    'multilang' => '0',
                    'inputName' => 'wk_mp_feature_val',
                    'msg' => $objMp->l('Feature value is not valid', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }
    }

    /**
     * [addPSLayeredIndexableFeature this function is used to insert data "layered_indexable_feature"
     * table of PS used in createfeatureprocess.php].
     *
     * @param [type] $data [description]
     */
    public static function addPSLayeredIndexableFeature($data)
    {
        Db::getInstance()->insert('layered_indexable_feature', $data);
    }

    /**
     * Check if feature is used for any product
     *
     * @param [type] $idFeature [description]
     *
     * @return [type] TRUE  =>  if feature is used for any product , FALSE =>  if not used
     */
    public static function ifFeatureAssigned($idFeature)
    {
        $result = Db::getInstance()->getValue(
            'SELECT * FROM `'._DB_PREFIX_.'feature_product`
            WHERE `id_feature` ='.(int) $idFeature
        );
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Check if feature value is used for any product
     *
     * @param [type] $idValue [description]
     *
     * @return [type] [true-> if feature value is used for any product, false -> if not used ]
     */
    public static function ifFeatureValueAssigned($idValue)
    {
        $result = Db::getInstance()->getValue(
            'SELECT * FROM `'._DB_PREFIX_.'feature_product`
            WHERE `id_feature_value` ='.(int) $idValue
        );
        if (!$result) {
            return false;
        }

        return true;
    }

    public static function copyMpProductFeatures($originalMpProductId, $duplicateMpProductId)
    {
        $originalPsProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($originalMpProductId);
        $duplicatePsProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($duplicateMpProductId);
        if ($originalPsProductId && $duplicatePsProductId) {
            return Product::duplicateFeatures($originalPsProductId, $duplicatePsProductId);
        }
        return false;
    }
}
