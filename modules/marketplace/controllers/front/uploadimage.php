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

class MarketplaceUploadImageModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $this->display_header = false;
        $this->display_footer = false;

        if (Tools::getValue('action') == 'uploadimage') {
            // Upload image
            if (Tools::getValue('actionIdForUpload')) {
                $actionIdForUpload = Tools::getValue('actionIdForUpload'); //it will be Product Id OR Seller Id
                $adminupload = Tools::getValue('adminupload'); //if uploaded by Admin from backend

                $finalData = WkMpSellerProductImage::uploadImage($_FILES, $actionIdForUpload, $adminupload);

                echo Tools::jsonEncode($finalData);
            }
        } elseif (Tools::getValue('action') == 'deleteimage' && Tools::getValue('actionpage') == 'product') {
            //Delete image (This action works only on Product page)
            $idImage = Tools::getValue('image_id');
            if ($idImage) {
                WkMpSellerProductImage::deleteProductFilerImage($idImage);
            }
        }

        die; //ajax close
    }
}
