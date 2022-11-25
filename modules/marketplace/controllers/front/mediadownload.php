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

class MarketplaceMediaDownloadModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if (Tools::getValue('action') == 'customizationImage') {
            if (!Tools::isSubmit('img') || !Tools::isSubmit('name')) {
                return;
            }

            $name = Tools::getValue('name');
            $img = Tools::getValue('img');
            $path = _PS_UPLOAD_DIR_ . $img;
            if (function_exists('mime_content_type')) {
                $mimeType = @mime_content_type($path);
            }
            if (!$mimeType) {
                $mimeType = 'image/png';
            }
            if ($mimeType) {
                $mimeTypes = array(
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
                    'ice' => 'x-conference-xcooltalk',
                );
                foreach ($mimeTypes as $ext => $meme) {
                    if ($meme == $mimeType) {
                        $type = $ext;
                        break;
                    }
                }
                if (!$type) {
                    $type = 'png';
                }
            }

            if (Validate::isMd5($img) && Validate::isGenericName($path)) {
                $name = $name.'.'.$type;
                header('Content-type: image/jpeg');
                header('Content-Disposition: attachment; filename="' . $name . '"');
                readfile($path);
            }
        }
        parent::initContent();
    }
}
