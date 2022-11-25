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

tinymce.init({
    selector: "textarea.wk_tinymce",
    theme: "modern",
    plugins: [
        "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
        "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
        "save table contextmenu directionality emoticons template paste textcolor autoresize responsivefilemanager"
    ],
    browser_spellcheck: true,
    toolbar1: "code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,|,blockquote,forecolor,pasteword,|,bullist,numlist,|,outdent,indent,|,link,unlink,anchor,|,cleanup,|,media,image,preview",
    toolbar2: "",
    external_filemanager_path: mp_tinymce_path + "/filemanager/",
    filemanager_title: "Filemanager",
    external_plugins: {
        "filemanager": mp_tinymce_path + "/filemanager/plugin.min.js"
    },
    image_advtab: true,
    statusbar: false,
    relative_urls: false,
    convert_urls: false,
    extended_valid_elements: "em[class|name|id]",
    language: iso,
    menu: {
        edit: {
            title: 'Edit',
            items: 'undo redo | cut copy paste | selectall'
        },
        insert: {
            title: 'Insert',
            items: 'media image link | pagebreak'
        },
        view: {
            title: 'View',
            items: 'visualaid'
        },
        format: {
            title: 'Format',
            items: 'bold italic underline strikethrough superscript subscript | formats | removeformat'
        },
        table: {
            title: 'Table',
            items: 'inserttable tableprops deletetable | cell row column'
        },
        tools: {
            title: 'Tools',
            items: 'code'
        }
    }
});