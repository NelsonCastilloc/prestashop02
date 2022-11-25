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

class WkMpSellerProductImage
{
    public static function uploadImage($files, $actionIdForUpload, $adminupload)
    {
        if (isset($files['sellerprofileimage'])) { //upload seller profile image
            $dirName = 'seller_img/';
            $imageFiles = $files['sellerprofileimage'];
            $actionType = 'sellerprofileimage';
        } elseif (isset($files['shopimage'])) { //upload shop image
            $dirName = 'shop_img/';
            $imageFiles = $files['shopimage'];
            $actionType = 'shopimage';
        } elseif (isset($files['productimages'])) {//upload seller product image
            $dirName = 'product_img/';
            $imageFiles = $files['productimages'];
            $actionType = 'productimage';
        } elseif (isset($files['profilebannerimage'])) { //upload seller profile Banner
            $dirName = 'seller_banner/';
            $imageFiles = $files['profilebannerimage'];
            $actionType = 'profilebannerimage';
        } elseif (isset($files['shopbannerimage'])) { //upload shop banner
            $dirName = 'shop_banner/';
            $imageFiles = $files['shopbannerimage'];
            $actionType = 'shopbannerimage';
        }

        if ($adminupload) {
            $uploadDirPath = '../modules/marketplace/views/img/' . $dirName;
        } else {
            $uploadDirPath = 'modules/marketplace/views/img/' . $dirName;
        }

        if ($actionType == 'productimage') {
            //Max upload size for product image
            $wkMaxSize = (int)Configuration::get('PS_LIMIT_UPLOAD_IMAGE_VALUE');
        } else {
            //Max upload size for seller, shop image and banner
            $wkMaxSize = (int)str_replace('M', '', ini_get('post_max_size'));
        }

        $uploader = new WkMpImageUploader();
        $data = $uploader->upload($imageFiles, array(
            'actionType' => $actionType, //Maximum Limit of files. {null, Number}
            //'limit' => 10, //Maximum Limit of files.{null, Number}
            'maxSize' => $wkMaxSize, //Max Size of files{null, Number(in MB)}-If not set then it will take server size]
            'extensions' => array('jpg', 'png', 'gif', 'jpeg'), //Whitelist for file extension.
            'required' => false, //Minimum one file is required for upload {Boolean}
            'uploadDir' => $uploadDirPath, //Upload directory {String}
            'title' => array('name'), //New file name {null, String, Array} *please read documentation in README.md
        ));

        $finalData = array();
        $finalResult = false;
        if ($data['hasErrors']) {
            $finalData['status'] = 'fail';
            $finalData['file_name'] = '';
            $finalData['error_message'] = $data['errors'][0];
        } elseif ($data['isComplete']) {
            if ($data['data']['metas'][0]['name']) {
                $imageNewName = $data['data']['metas'][0]['name'];
                $imageId = 0;
                if ($actionType == 'productimage') {
                    // $actionIdForUpload is mpIdProduct if it is product image
                    $imageId = self::uploadProductImage($actionIdForUpload, $imageNewName);
                    if ($imageId) {
                        $finalResult = true;
                    }
                } elseif ($actionType == 'sellerprofileimage') {
                    // $actionIdForUpload is mpIdSeller if it is seller profile image
                    $objMpSeller = new WkMpSeller($actionIdForUpload);
                    if ($objMpSeller->profile_image) { //delete old seller image if exist
                        $imageFile = $uploadDirPath . $objMpSeller->profile_image;
                        if (file_exists($imageFile)) {
                            unlink($imageFile);
                        }
                    }
                    $objMpSeller->profile_image = $imageNewName;
                    if ($objMpSeller->save()) {
                        $finalResult = true;
                    }
                } elseif ($actionType == 'shopimage') {
                    // $actionIdForUpload is mpIdSeller if it is shop image
                    $objMpSeller = new WkMpSeller($actionIdForUpload);
                    if ($objMpSeller->shop_image) { //delete old shop image if exist
                        $imageFile = $uploadDirPath . $objMpSeller->shop_image;
                        if (file_exists($imageFile)) {
                            unlink($imageFile);
                        }
                    }
                    $objMpSeller->shop_image = $imageNewName;
                    if ($objMpSeller->save()) {
                        $finalResult = true;
                    }
                } elseif ($actionType == 'profilebannerimage') {
                    // $actionIdForUpload is mpIdSeller if it is profile banner
                    $objMpSeller = new WkMpSeller($actionIdForUpload);
                    if ($objMpSeller->profile_banner) { //delete old shop image if exist
                        $imageFile = $uploadDirPath . $objMpSeller->profile_banner;
                        if (file_exists($imageFile)) {
                            unlink($imageFile);
                        }
                    }
                    $objMpSeller->profile_banner = $imageNewName;
                    if ($objMpSeller->save()) {
                        $finalResult = true;
                    }
                } elseif ($actionType == 'shopbannerimage') {
                    // $actionIdForUpload is mpIdSeller if it is shop banner
                    $objMpSeller = new WkMpSeller($actionIdForUpload);
                    if ($objMpSeller->shop_banner) { //delete old shop image if exist
                        $imageFile = $uploadDirPath . $objMpSeller->shop_banner;
                        if (file_exists($imageFile)) {
                            unlink($imageFile);
                        }
                    }
                    $objMpSeller->shop_banner = $imageNewName;
                    if ($objMpSeller->save()) {
                        $finalResult = true;
                    }
                }

                if ($finalResult == true) {
                    $finalData['status'] = 'success';
                    $finalData['file_name'] = $imageNewName;
                    $finalData['image_id'] = $imageId;
                    $finalData['error_message'] = '';
                }
            }
        }

        return $finalData;
    }

    /**
     * Upload product images.
     *
     * @param int $mpIdProduct Seller Product ID
     * @param string $imageNewName Image name
     *
     * @return bool
     */
    public static function uploadProductImage($mpIdProduct, $imageNewName)
    {
        if ($idPsProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
            //if product is active then check configuration that product after update need to approved by admin or not
            WkMpSellerProduct::deactivateProductAfterUpdate($mpIdProduct);

            //Upload images in ps product
            return self::uploadPsProductImage($idPsProduct, $imageNewName);
        }

        return false;
    }

    /**
     * Update prestashop product's images.
     *
     * @param int $psIdProduct Prestashop Id Product
     * @param int $imageNewName Mp image name
     *
     * @return bool true/false
     */
    public static function uploadPsProductImage($psIdProduct, $imageNewName)
    {
        $oldPath = _PS_MODULE_DIR_.'marketplace/views/img/product_img/'.$imageNewName;
        $objImage = new Image();
        $objImage->id_product = $psIdProduct;
        if (Product::getCover($psIdProduct)) {
            $objImage->cover = 0;
        } else {
            $objImage->cover = 1;
        }
        $objImage->add();
        if ($imageId = $objImage->id) {
            $newPath = $objImage->getPathForCreation();
            $imagesTypes = ImageType::getImagesTypes('products');
            if ($imagesTypes) {
                foreach ($imagesTypes as $imageType) {
                    ImageManager::resize(
                        $oldPath,
                        $newPath.'-'.Tools::stripslashes($imageType['name']).'.'.$objImage->image_format,
                        $imageType['width'],
                        $imageType['height'],
                        $objImage->image_format
                    );
                }
            }

            ImageManager::resize($oldPath, $newPath.'.'.$objImage->image_format);
            Hook::exec(
                'actionWatermark',
                array(
                    'id_image' => $imageId,
                    'id_product' => $psIdProduct
                )
            );
            Hook::exec(
                'actionPsMpImageMap',
                array(
                    'mp_product_id' => WkMpSellerProduct::getMpIdProductByPsIdProduct($psIdProduct),
                    'ps_id_product' => $psIdProduct,
                    'ps_id_image' => $imageId
                )
            );

            unlink($oldPath);
            return $imageId;
        }
        return false;
    }

    /**
     * Assign and display product active/inactive images at product page.
     *
     * @param int $mpIdProduct seller product id
     *
     * @return assign
     */
    public static function getProductImageDetails($mpIdProduct)
    {
        $context = Context::getContext();
        $idLang = $context->language->id;

        if ($psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
            $objProduct = new Product($psIdProduct);
            $productImages = $objProduct->getImages($idLang);
            // Code Added for Multishop issue
            $temp = array_unique(array_column($productImages, 'id_image'));
            $productImages = array_intersect_key($productImages, $temp);

            if ($productImages) {
                $productLinkRewrite = $objProduct->link_rewrite[$idLang];
                foreach ($productImages as &$image) {
                    $image['image_link'] = $context->link->getImageLink(
                        $productLinkRewrite,
                        $psIdProduct.'-'.$image['id_image'],
                        ImageType::getFormattedName('cart')
                    );
                    $objImage = new Image($image['id_image']);
                    $image['legend'] = $objImage->legend;
                }

                $context->smarty->assign(array(
                    'image_detail' => $productImages,
                    'cover_image' => self::getProductCoverImage($mpIdProduct, $psIdProduct),
                    'id_mp_product' => $mpIdProduct,
                ));
            }
        }

        $editPermission = 1;
        if (Module::isEnabled('mpsellerstaff') && isset($context->customer->id)) {
            $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($context->customer->id);
            if ($staffDetails
                && $staffDetails['active']
                && $staffDetails['id_seller']
                && $staffDetails['seller_status']
            ) {
                $idTab = WkMpTabList::MP_PRODUCT_TAB; //For Product
                $staffTabDetails = WkMpTabList::getStaffPermissionWithTabName(
                    $staffDetails['id_staff'],
                    $context->language->id,
                    $idTab
                );
                if ($staffTabDetails) {
                    $editPermission = $staffTabDetails['edit'];
                }
            }
        }
        $context->smarty->assign('edit_permission', $editPermission);
    }

    public static function getProductCoverImage($mpIdProduct, $psIdProduct = false)
    {
        if (!$psIdProduct) {
            $psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct);
        }

        $coverImage = Product::getCover($psIdProduct);
        if ($coverImage && isset($coverImage['id_image'])) {
            return $coverImage['id_image'];
        }
        return false;
    }

    public static function setProductCoverImage($mpIdProduct, $idImage)
    {
        if ($psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
            Image::deleteCover((int) $psIdProduct);

            $image = new Image((int) $idImage);
            $image->cover = 1;
            if ($image->update()) {
                // unlink existing cover image in temp folder
                @unlink(_PS_TMP_IMG_DIR_.'product_'.(int) $psIdProduct);
                @unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int) $psIdProduct.'_'.Context::getContext()->shop->id);


                return true;
            }
        }
        return false;
    }

    public static function deleteProductImage($mpIdProduct, $idImage, $isCover = false)
    {
        if ($psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
            $image = new Image($idImage);
            if ($image->delete()) {
                Product::cleanPositions($idImage);

                // if cover image deleting, make first image as a cover
                if ($isCover) {
                    $productFirstImage = Db::getInstance()->getRow(
                        'SELECT  `id_image` FROM '._DB_PREFIX_.'image
                        WHERE `id_product` = '.(int) $psIdProduct
                    );
                    if ($productFirstImage) {
                        self::setProductCoverImage($mpIdProduct, $productFirstImage['id_image']);
                    }
                }

                return true;
            }
        }
        return false;
    }

    public static function deleteProductFilerImage($idImage, $adminDelete = false)
    {
        //Delete image after upload
        $image = new Image($idImage);
        if ($psIdProduct = $image->id_product) {
            $wkAllowed = false;
            if ($adminDelete) {
                $wkAllowed = true;
            } else {
                if (isset(Context::getContext()->customer->id)) {
                    $mpIdProduct = WkMpSellerProduct::getMpIdProductByPsIdProduct($psIdProduct);
                    $seller = WkMpSeller::getSellerDetailByCustomerId(Context::getContext()->customer->id);
                    if ($seller && $seller['active']) {
                        $mpSellerProduct = new WkMpSellerProduct($mpIdProduct);
                        // If seller of current product and current seller customer is match
                        if ($mpSellerProduct->id_seller == $seller['id_seller']) {
                            $wkAllowed = true;
                        }
                    }
                }
            }
            if ($wkAllowed) {
                if ($image->delete()) {
                    Product::cleanPositions($idImage);
                    return true;
                }
            }
        }
        return false;
    }

    public static function changePsProductImagePosition($idPsProduct, $idImage, $toRowIndex, $idImagePosition)
    {
        if ($toRowIndex < $idImagePosition) {
            return Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'image` SET `position` = position+1
                WHERE position >= '.(int) $toRowIndex.'
                AND `position` <= '.(int) $idImagePosition.'
                AND `id_image` != '.(int) $idImage.'
                AND `id_product` = '.(int) $idPsProduct
            );
        } elseif ($toRowIndex >= $idImagePosition) {
            return Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'image` SET `position` = position-1
                WHERE position <= '.(int) $toRowIndex.'
                AND `position` >= '.(int) $idImagePosition.'
                AND `id_image` != '.(int) $idImage.'
                AND `id_product` =' . (int) $idPsProduct
            );
        }

        return false;
    }

    public static function copyMpProductImages($originalMpProductId, $duplicateMpProductId)
    {
        $originalPsProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($originalMpProductId);
        $duplicatePsProductId = WkMpSellerProduct::getPsIdProductByMpIdProduct($duplicateMpProductId);
        if ($originalPsProductId && $duplicatePsProductId) {
            $combinationImages = array();
            return Image::duplicateProductImages($originalPsProductId, $duplicatePsProductId, $combinationImages);
        }
        return false;
    }

    public function getProductImageBySellerIdProduct($mpIdProduct)
    {
        if ($psIdProduct = WkMpSellerProduct::getPsIdProductByMpIdProduct($mpIdProduct)) {
            $objProduct = new Product($psIdProduct);
            $productImages = $objProduct->getImages((int)Context::getContext()->language->id);
            if ($productImages) {
                return $productImages;
            }
        }

        return false;
    }

    public static function setProductImageLegend($idImage, $legend)
    {
        if ($idImage && $legend) {
            $image = new Image((int) $idImage);
            foreach (Language::getLanguages(false) as $lang) {
                $image->legend[$lang['id_lang']] = $legend[$lang['id_lang']];
            }
            if ($image->update()) {
                return true;
            }
        }
        return false;
    }
}
