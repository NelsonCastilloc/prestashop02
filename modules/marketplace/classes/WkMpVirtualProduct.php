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

class WkMpVirtualProduct
{
    const ENABLE = 1;

    /**
     * Delete Product Download by using Product Download ID
     *
     * @param  int  $idProdDownload Product Download ID
     * @return bool
     */
    public function deleteProdDownloadByIdProductDownload($idProdDownload)
    {
        return Db::getInstance()->delete('product_download', '`id_product_download` = '.(int) $idProdDownload, 1);
    }

    /**
     * Get virtual product download details by using MP Product ID
     *
     * @param  int  $mpProductId MP Product ID
     * @return array
     */
    public function isMpProductIsVirtualProduct($mpProductId)
    {
        $idPSProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpProductId);
        $product = new Product($idPSProduct);
        if ($product->is_virtual == 1) {
            $download = new ProductDownload(ProductDownload::getIdFromIdProduct($idPSProduct));
            if ($download) {
                $virtualProductArray = array();
                $virtualProductArray['display_filename'] = $download->display_filename;
                $virtualProductArray['date_expiration'] = $download->date_expiration;
                $virtualProductArray['nb_days_accessible'] = $download->nb_days_accessible;
                $virtualProductArray['nb_downloadable'] = $download->nb_downloadable;
                return $virtualProductArray;
            }
        }
        return false;
    }

    /**
     * Update virtual product download file using params
     *
     * @param  int  $mpProductId MP Product ID
     * @param  int  $psProductId PS Product ID
     * @param  int  $psProductActive PS Product status
     * @param  int  $fileName Download file name
     * @param  int  $virtualProductArray virtualProductData
     */
    public function updateFile($psProductId, $filePath, $psProductActive, $fileName = '', $virtualProductArray = null)
    {
        $download = new ProductDownload(ProductDownload::getIdFromIdProduct($psProductId));
        $download->id_product = (int) $psProductId;
        $download->display_filename = trim($fileName) != '' ? $fileName : 'dummy.zip';
        $download->date_add = date('Y-m-d H:i:s');

        if ($virtualProductArray['date_expiration'] != 0) {
            //add 23 hours 59 min 59 seconds in selected date same as prestashop do
            $download->date_expiration = date('Y-m-d H:i:s', strtotime($virtualProductArray['date_expiration']) + 86399);
        } else {
            $download->date_expiration = '';
        }

        $download->nb_days_accessible = (int) $virtualProductArray['nb_days_accessible'];
        $download->nb_downloadable = (int) $virtualProductArray['nb_downloadable'];
        $download->active = (int)$psProductActive;
        $download->is_shareable = 0;

        if ($filePath && file_exists($filePath)) {
            $download->filename = ProductDownload::getNewFilename();
            $download->save();
            copy($filePath, _PS_DOWNLOAD_DIR_.$download->filename);
            unlink($filePath);
        } else {
            $download->filename = $download->filename;
            $download->save();
        }
    }

    /**
     * Check Product Combination by using MP Product ID
     *
     * @param  int  $mpProductId MP Product ID
     * @return bool
     */
    public static function checkProductCombination($mpProductId)
    {
        $combinationExist = 0;
        $idPsProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpProductId);
        if ($idPsProduct) {
            $objProduct = new Product($idPsProduct);
            if (Validate::isLoadedObject($objProduct) && $objProduct->hasCombinations()) {
                $combinationExist = 1;
            }
        }

        return $combinationExist;
    }

    /**
     * Check Delete Attachment
     *
     * @param  int  $mpProductId MP Product ID
     * @return bool
     */
    public static function deleteAttachfile($mpProductId)
    {
        $objMpVirtualProduct = new self();
        $isVirtualProduct = $objMpVirtualProduct->isMpProductIsVirtualProduct($mpProductId);
        if ($isVirtualProduct) {
            $mpProductdetail = WkMpSellerProduct::getSellerProductByIdProduct($mpProductId);
            if ($mpProductdetail['id_ps_product']) {
                $psProductId = $mpProductdetail['id_ps_product'];
                $download = new ProductDownload(ProductDownload::getIdFromIdProduct($psProductId));
                if (trim($download->filename)) {
                    if (file_exists(_PS_DOWNLOAD_DIR_.$download->filename)) {
                        unlink(_PS_DOWNLOAD_DIR_.$download->filename);
                    }
                }

                $download->display_filename = 'dummy.zip';
                $download->filename = '';
                $download->date_add = date('Y-m-d H:i:s');
                $download->date_expiration = '';
                $download->nb_days_accessible = 0;
                $download->nb_downloadable = 0;
                $download->active = 1;
                $download->is_shareable = 0;
                $download->update();
            }

            return true;
        }
    }
}
