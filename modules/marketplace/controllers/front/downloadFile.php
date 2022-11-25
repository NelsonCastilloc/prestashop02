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

class MarketplaceDownloadFileModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if ($this->context->customer->id && !Tools::getValue('admin')) {
            $idCustomer = $this->context->customer->id;
            $mpSellerDetail = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($mpSellerDetail && $mpSellerDetail['active']) {
                $mpSellerDetail = $mpSellerDetail['id_seller'];
                if (Tools::getValue('id_value')) {
                    $idDownload = Tools::getValue('id_value');
                    $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($idDownload);
                    if ($mpProductDetail['id_seller'] == $mpSellerDetail) {
                        $this->downloadVirtualContent($mpProductDetail);
                    }
                }
            }
        } else if (Tools::getValue('admin')) {
            $idDownload = Tools::getValue('id_value');
            $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($idDownload);
            $this->downloadVirtualContent($mpProductDetail);
        }

        parent::initContent();
    }

    /**
     * [downloadContent -> download media content].
     *
     * @param [type] $download_id [file id]
     *
     * @return [type] [description]
     */
    public function downloadVirtualContent($mpProductDetail)
    {
        $filePath = '';
        $objMpVirtualProduct = new WkMpVirtualProduct();
        $mpProductId = $mpProductDetail['id_mp_product'];
        $isVertualProduct = $objMpVirtualProduct->isMpProductIsVirtualProduct($mpProductId);
        if (!$mpProductDetail['id_ps_product']) { //if virtual product  not approve by admin
            $fileName = $isVertualProduct['reference_file'];
            $displayFileName = $isVertualProduct['display_filename'];
            $filePath = _PS_MODULE_DIR_.$this->module->name.'/views/upload/'.$fileName;
            if (!file_exists($filePath)) {
                $this->context->controller->errors[] = Tools::displayError('Image Not exist');
            } else {
                set_time_limit(0);
                $this->downloadVirtualFile($filePath, ''.$displayFileName.'', '');
            }
        } else {
            //if virtual product approve by admin
            $psProductId = $mpProductDetail['id_ps_product'];
            $objProductDownload = new ProductDownload();
            $fileKey = ($objProductDownload->getFilenameFromIdProduct($psProductId));
            if (!Validate::isSha1($fileKey)) {
                $this->context->controller->errors[] = Tools::displayError('Invalid Key');
            }
            $file = _PS_DOWNLOAD_DIR_.strval(preg_replace('/\.{2,}/', '.', $fileKey));
            $fileName = ProductDownload::getFilenameFromFilename($fileKey);
            if (!file_exists($file)) {
                $this->context->controller->errors[] = Tools::displayError('Image Not exist');
            } else {
                set_time_limit(0);
                $this->downloadVirtualFile($file, ''.$fileName.'', '');
            }
        }
    }

    /**
     * [downloadFile -> php download code].
     *
     * @param [type] $file      [file]
     * @param [type] $name      [name of file]
     * @param string $mimeType [file type (format)]
     *
     * @return [type] [description]
     */
    public function downloadVirtualFile($file, $name, $mimeType = '')
    {
        if (!is_readable($file)) {
            die('File not found or inaccessible!');
        }
        $mimeType = false;
        if (function_exists('mime_content_type')) {
            $mimeType = @mime_content_type($file);
        }
        $size = filesize($file);
        $name = rawurldecode($name);
        $knownMimeTypes = array(
            'ez' => 'application/andrew-inset',
            'hqx' => 'application/mac-binhex40',
            'cpt' => 'application/mac-compactpro',
            'doc' => 'application/msword',
            'oda' => 'application/oda',
            'pdf' => 'application/pdf',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wmlc' => 'application/vnd.wap.wmlc',
            'wmlsc' => 'application/vnd.wap.wmlscriptc',
            'bcpio' => 'application/x-bcpio',
            'vcd' => 'application/x-cdlink',
            'pgn' => 'application/x-chess-pgn',
            'cpio' => 'application/x-cpio',
            'csh' => 'application/x-csh',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi',
            'spl' => 'application/x-futuresplash',
            'gtar' => 'application/x-gtar',
            'hdf' => 'application/x-hdf',
            'js' => 'application/x-javascript',
            'skp' => 'application/x-koan',
            'skd' => 'application/x-koan',
            'skt' => 'application/x-koan',
            'skm' => 'application/x-koan',
            'latex' => 'application/x-latex',
            'nc' => 'application/x-netcdf',
            'cdf' => 'application/x-netcdf',
            'sh' => 'application/x-sh',
            'shar' => 'application/x-shar',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc' => 'application/x-sv4crc',
            'tar' => 'application/x-tar',
            'tcl' => 'application/x-tcl',
            'tex' => 'application/x-tex',
            'texinfo' => 'application/x-texinfo',
            'texi' => 'application/x-texinfo',
            't' => 'application/x-troff',
            'tr' => 'application/x-troff',
            'roff' => 'application/x-troff',
            'man' => 'application/x-troff-man',
            'me' => 'application/x-troff-me',
            'ms' => 'application/x-troff-ms',
            'ustar' => 'application/x-ustar',
            'src' => 'application/x-wais-source',
            'xhtml' => 'application/xhtml+xml',
            'xht' => 'application/xhtml+xml',
            'zip' => 'application/zip',
            'au' => 'audio/basic',
            'snd' => 'audio/basic',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'kar' => 'audio/midi',
            'mpga' => 'audio/mpeg',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'aif' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'm3u' => 'audio/x-mpegurl',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'ra' => 'audio/x-realaudio',
            'wav' => 'audio/x-wav',
            'pdb' => 'chemical/x-pdb',
            'xyz' => 'chemical/x-xyz',
            'bmp' => 'image/bmp',
            'gif' => 'image/gif',
            'ief' => 'image/ief',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'png' => 'image/png',
            'tiff' => 'image/tiff',
            'tif' => 'image/tif',
            'djvu' => 'image/vnd.djvu',
            'djv' => 'image/vnd.djvu',
            'wbmp' => 'image/vnd.wap.wbmp',
            'ras' => 'image/x-cmu-raster',
            'pnm' => 'image/x-portable-anymap',
            'pbm' => 'image/x-portable-bitmap',
            'pgm' => 'image/x-portable-graymap',
            'ppm' => 'image/x-portable-pixmap',
            'rgb' => 'image/x-rgb',
            'xbm' => 'image/x-xbitmap',
            'xpm' => 'image/x-xpixmap',
            'xwd' => 'image/x-windowdump',
            'igs' => 'model/iges',
            'iges' => 'model/iges',
            'msh' => 'model/mesh',
            'mesh' => 'model/mesh',
            'silo' => 'model/mesh',
            'wrl' => 'model/vrml',
            'vrml' => 'model/vrml',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html',
            'asc' => 'text/plain',
            'txt' => 'text/plain',
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',
            'sgml' => 'text/sgml',
            'sgm' => 'text/sgml',
            'tsv' => 'text/tab-seperated-values',
            'wml' => 'text/vnd.wap.wml',
            'wmls' => 'text/vnd.wap.wmlscript',
            'etx' => 'text/x-setext',
            'xml' => 'text/xml',
            'xsl' => 'text/xml',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'mxu' => 'video/vnd.mpegurl',
            'avi' => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            'ice' => 'x-conference-xcooltalk'
        );

        if (($mimeType == '') || !$mimeType) {
            $fileExtension = Tools::strtolower(Tools::substr(strrchr($file, '.'), 1));
            if (array_key_exists($fileExtension, $knownMimeTypes)) {
                $mimeType = $knownMimeTypes[$fileExtension];
            } else {
                $mimeType = 'application/force-download';
            }
        }
        foreach ($knownMimeTypes as $ext => $meme) {
            if ($mimeType == $meme) {
                $bName = explode('.', $name);
                $bName = $bName[0];
                $name = $bName.'.'.$ext;
                break;
            }
        }
        @ob_end_clean();
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        header('Content-Type: '.$mimeType);
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        header('Cache-control: private');
        header('Pragma: private');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        if (isset($_SERVER['HTTP_RANGE'])) {
            list($a, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            list($range) = explode(',', $range, 2);
            list($range, $rangeEnd) = explode('-', $range);
            $range = (int) $range;

            if (!$rangeEnd) {
                $rangeEnd = $size - 1;
            } else {
                $rangeEnd = (int) $rangeEnd;
            }

            $newLength = $rangeEnd - $range + 1;
            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: $newLength");
            header("Content-Range: bytes $range-$rangeEnd/$size");
        } else {
            $newLength = $size;
            header('Content-Length: '.$size);
        }

        $chunkSize = 1 * (1024 * 1024);
        $bytesSend = 0;
        if ($file = fopen($file, 'r')) {
            if (isset($_SERVER['HTTP_RANGE'])) {
                fseek($file, $range);
            }
            while (!feof($file) && (!connection_aborted()) && ($bytesSend < $newLength)) {
                $buffer = fread($file, $chunkSize);
            }
            print($buffer);
            flush();
            $bytesSend += Tools::strlen($buffer);
        }

        fclose($file);
    }
}
