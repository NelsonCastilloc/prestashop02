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

class MarketplaceProductImageEditModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $this->display_header = false;
        $this->display_footer = false;
    }

    public function initContent()
    {
        WkMpHelper::assignGlobalVariables(); // Assign global static variable on tpl
        WkMpHelper::defineGlobalJSVariables(); // Define global js variable on js file

        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        if ($mpIdProduct = Tools::getValue('id_product')) {
            //Assign and display product active/inactive images
            WkMpSellerProductImage::getProductImageDetails($mpIdProduct);
            $this->context->smarty->assign('displayCancelIcon', 1);
            if ($idSeller = (new WkMpSellerProduct($mpIdProduct))->id_seller) {
                WkMpHelper::assignDefaultLang($idSeller);
                $this->setTemplate('module:marketplace/views/templates/front/product/imageedit.tpl');
            }
        }
    }

    public function displayAjaxDeleteProductImage()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        // Ajax Delete image
        $idImage = Tools::getValue('id_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        if ($idImage && $idMpProduct) {
            if (WkMpSellerProduct::isSameSellerProduct($idMpProduct)
            && WkMpSellerProduct::isSameProductImage($idMpProduct, $idImage)) {
                $isCover = Tools::getValue('is_cover');
                $objMpImage = new WkMpSellerProductImage();
                if ($objMpImage->deleteProductImage($idMpProduct, $idImage, $isCover)) {
                    //To manage staff log (changes add/update/delete) => 2 for Add action
                    WkMpHelper::setStaffHook($this->context->customer->id, 'updateproduct', $idMpProduct, 2);

                    if ($isCover) {
                        die('2'); // if cover image deleted
                    } else {
                        die('1'); // if normal image deleted
                    }
                }
            }
        }
        die('0');
    }

    public function displayAjaxChangeCoverImage()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        // Ajax Change cover image
        $idImage = Tools::getValue('id_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        if ($idImage && $idMpProduct) {
            if (WkMpSellerProduct::isSameSellerProduct($idMpProduct)
            && WkMpSellerProduct::isSameProductImage($idMpProduct, $idImage)) {
                $objMpImage = new WkMpSellerProductImage();
                if ($objMpImage->setProductCoverImage($idMpProduct, $idImage)) {
                    //To manage staff log (changes add/update/delete) => 2 for Add action
                    WkMpHelper::setStaffHook($this->context->customer->id, 'updateproduct', $idMpProduct, 2);
                    die('1');
                }
            }
        }
        die('0');
    }

    public function displayAjaxChangeImagePosition()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        // Ajax Image position
        $idImage = Tools::getValue('id_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        if ($idImage && $idMpProduct) {
            $idImagePosition = Tools::getValue('id_image_position');
            $toRowIndex = Tools::getValue('to_row_index') + 1;

            if (WkMpSellerProduct::isSameSellerProduct($idMpProduct)
            && WkMpSellerProduct::isSameProductImage($idMpProduct, $idImage)) {
                if ($psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($idMpProduct)) {
                    $objImage = new Image($idImage);
                    $objImage->position = $toRowIndex;
                    if ($objImage->update()) {
                        $result = WkMpSellerProductImage::changePsProductImagePosition(
                            $psIdProduct,
                            $idImage,
                            $toRowIndex,
                            $idImagePosition
                        );
                        if ($result) {
                            //To manage staff log (changes add/update/delete) => 2 for Add action
                            WkMpHelper::setStaffHook($this->context->customer->id, 'updateproduct', $idMpProduct, 2);
                            die('1');//ajax close
                        }
                    }
                }
            }
        }
        die('0');//ajax close
    }

    public function displayAjaxAddProductImageCaption()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        // Ajax Change cover image
        $idImage = Tools::getValue('id_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        $legend = Tools::getValue('legend');
        foreach (Language::getLanguages() as $lang) {
            if (!Validate::isCatalogName($legend[$lang['id_lang']])) {
                $wkError = sprintf(
                    $this->module->l('Image caption field in %s is invalid', 'productimageedit'),
                    $lang['name']
                );
                die($wkError);
            }
            if (Tools::strlen($legend[$lang['id_lang']]) > 128) {
                $wkError = sprintf(
                    $this->module->l('Image caption must be less than %s characters.', 'productimageedit'),
                    128
                );
                die($wkError);
            }
        }
        if ($idImage && $idMpProduct && $legend) {
            if (WkMpSellerProduct::isSameSellerProduct($idMpProduct)
            && WkMpSellerProduct::isSameProductImage($idMpProduct, $idImage)) {
                $objMpImage = new WkMpSellerProductImage();
                if ($objMpImage->setProductImageLegend($idImage, $legend)) {
                    //To manage staff log (changes add/update/delete) => 2 for Add action
                    WkMpHelper::setStaffHook($this->context->customer->id, 'updateproduct', $idMpProduct, 2);
                    die('1');
                }
            }
        }
        die('0');
    }
}
