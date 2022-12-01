<?php
require_once(dirname(__FILE__) . '/../../classes/PseFonts.php');
require_once _PS_MODULE_DIR_ . 'crazyelements/PrestaHelper.php';

use CrazyElements\PrestaHelper;

class AdminCrazyFontsController extends AdminController
{
    public $folder_name = '';

    public function __construct()
    {
        $this->table = 'crazy_fonts';  // give the table name which we have create,
        $this->className = 'PseFonts'; // class name of our object model
        $this->lang = false;
        $this->deleted = false;
        $this->bootstrap = true;
        $this->module = 'crazyelements';
        parent::__construct();
        // now we will create the table to show
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );
        $this->fields_list = array(
            'id_crazy_fonts' => array(
                'title' => $this->l('Id'),
                'width' => 100,
                'type' => 'text',
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'width' => 440,
                'type' => 'text',
                'lang' => true,
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'font_weight' => array(
                'title' => $this->l('Font Weight'),
                'width' => 440,
                'type' => 'file',
                'lang' => true,
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'font_style' => array(
                'title' => $this->l('Font Style'),
                'width' => 440,
                'type' => 'file',
                'lang' => true,
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'ttf' => array(
                'title' => $this->l('TTF'),
                'width' => 440,
                'type' => 'file',
                'lang' => true,
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),

            'woff' => array(
                'title' => $this->l('Woff'),
                'width' => 440,
                'type' => 'file',
                'lang' => true,
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'woff2' => array(
                'title' => $this->l('Woff2'),
                'width' => 440,
                'type' => 'file',
                'lang' => true,
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'svg' => array(
                'title' => $this->l('SVG'),
                'width' => 440,
                'type' => 'file',
                'lang' => true,
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'eot' => array(
                'title' => $this->l('Eot'),
                'width' => 440,
                'type' => 'file',
                'lang' => true,
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'width' => '70',
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'filter' => false,
                'search' => false
            )
        );
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );
        parent::__construct();
        $this->actions = array('delete');
    }

    public function renderForm()
    {

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Content Any Where'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title'),
                    'name' => 'title',
                    'lang' => false,
                    'required' => true,
                    'desc' => $this->l('Enter Your Title')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('font_weight'),
                    'name' => 'font_weight',
                    'lang' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Font Style'),
                    'name' => 'font_style',
                    'lang' => false,
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('TTF FIle'),
                    'name' => 'ttf_file',
                    'lang' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('ttf'),
                    'name' => 'ttf',
                    'lang' => false,
                    'readonly' => true,
                    'class' => "psecustomfont customttf",
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Woff FIle'),
                    'name' => 'woff_file',
                    'lang' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Woff'),
                    'name' => 'woff',
                    'lang' => false,
                    'readonly' => true,
                    'class' => "psecustomfont customwoff",
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Woff2 FIle'),
                    'name' => 'woff2_file',
                    'lang' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Woff2'),
                    'name' => 'woff2',
                    'lang' => false,
                    'readonly' => true,
                    'class' => "psecustomfont customwoff2",
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('SVG FIle'),
                    'name' => 'svg_file',
                    'lang' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Svg'),
                    'name' => 'svg',
                    'lang' => false,
                    'readonly' => true,
                    'class' => "psecustomfont customsvg",
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Eot FIle'),
                    'name' => 'eot_file',
                    'lang' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Eot'),
                    'name' => 'eot',
                    'lang' => false,
                    'readonly' => true,
                    'class' => "psecustomfont customeot",
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ),
        );

        return parent::renderForm();
    }


    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $html = '';
        $html .= '<div class="panel col-lg-12"> <div class="panel-heading"> Fonts Preview<span class="badge"></span></div><div class="row fontgroup">';
        $psefonts = PseFonts::get_data_font();
        foreach ($psefonts as  $fontData) {
            $context = Context::getContext();
            $context->controller->addCSS(CRAZY_ASSETS_URL . 'fonts/' . $fontData['fontname'] . '/' . $fontData['fontname'] . '.css');
            $html .= '<div class="col-md-12"><div class="col-md-2"><span>' . $fontData['fontname'] . '</span></div><div class="col-md-8"><div class="fonts pselower ' . $fontData['fontname'] . '">the quick brown fox jumps over the lazy dog</div><div class="fonts pseupper ' . $fontData['fontname'] . '">The quick brown fox jumps over the lazy dog</div></div></div>';
        }
        $html .= '</div></div><style>
    .fontgroup .fonts { font-size: 40px; }
    .fontgroup .col-md-2 { height: 60px; }
    .fontgroup span { position: relative; top: 50%; transform: translateY(-50%); float: left; font-size: 20px; text-transform: capitalize; }.fontgroup .col-md-12 {
    border-bottom: 1px solid #eee;
    margin-bottom: 2px;}
    .fonts.pseupper { text-transform: uppercase; font-size: 35px; }
    </style>';
        $htmlfinal = parent::renderList() . $html;
        return $htmlfinal;
    }

    public function initToolbar()
    {
        parent::initToolbar();
    }

    public function initContent()
    {
        PrestaHelper::get_lience_expired_date();
        $a = "<div></div>" . parent::initContent();
        return $a;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddcrazy_fonts')) {

            $fontname = preg_replace('/\s+/', '',  $_POST['title']);
            $dirnamne = dirname(__DIR__, 2) . "/assets/fonts/";
            $this->folder_name = dirname(__DIR__, 2) . "/assets/fonts/" . $fontname . "/";
            if (!is_dir($this->folder_name)) {
                if (is_writable($dirnamne)) {
                    mkdir($this->folder_name, 0777, true);
                    chmod($this->folder_name, 0777);
                } else {
                    chmod($dirnamne, 0777);
                    mkdir($this->folder_name, 0777, true);
                    chmod($this->folder_name, 0777);
                    // parent::postProcess(true);
                    // return;
                }
            } else {
                if (!Tools::isSubmit('id_crazy_fonts')) {
                    parent::postProcess(true);
                    return;
                }
            }
            $str = "";
            if (move_uploaded_file($_FILES['ttf_file']['tmp_name'], $this->folder_name . $_FILES["ttf_file"]['name'])) {
                $_POST['ttf'] = $_FILES["ttf_file"]['name'];
            }
            if (move_uploaded_file($_FILES['woff_file']['tmp_name'], $this->folder_name . $_FILES["woff_file"]['name'])) {
                $_POST['woff'] = $_FILES["woff_file"]['name'];
            }
            if (move_uploaded_file($_FILES['woff2_file']['tmp_name'], $this->folder_name . $_FILES["woff2_file"]['name'])) {
                $_POST['woff2'] = $_FILES["woff2_file"]['name'];
            }
            if (move_uploaded_file($_FILES['svg_file']['tmp_name'], $this->folder_name . $_FILES["svg_file"]['name'])) {
                $_POST['svg'] = $_FILES["svg_file"]['name'];
            }
            if (move_uploaded_file($_FILES['eot_file']['tmp_name'], $this->folder_name . $_FILES["eot_file"]['name'])) {
                $_POST['eot'] = $_FILES["eot_file"]['name'];
            }
            if (isset($_POST['ttf']) && $_POST['ttf'] != '') {
                $str .= "url('" . $_POST['ttf'] . "') format('truetype'),";
            }
            if (isset($_POST['woff']) && $_POST['woff'] != '') {
                $str .= "url('" . $_POST['woff'] . "') format('woff'),";
            }
            if (isset($_POST['ttf']) && $_POST['woff2'] != '') {
                $str .= "url('" . $_POST['woff2'] . "') format('woff2'),";
            }
            if (isset($_POST['ttf']) && $_POST['svg'] != '') {
                $str .= "url('" . $_POST['svg'] . "') format('svg'),";
            }
            if (isset($_POST['ttf']) && $_POST['eot'] != '') {
                $str .= "url('" . $_POST['eot'] . "') format('embedded-opentype'),";
            }
            $_POST['fontname'] = $fontname;
            $stylefile =  $this->folder_name . $fontname . ".css";
            $str = rtrim($str, ',');
            $style = "@font-face {
                font-family: '" . $fontname . "';
                src: " . $str . ";
                font-display: auto;
                font-weight: " . $_POST['font_weight'] . ";
                font_style: " . $_POST['font_style'] . ";
            }
            ." . $fontname . "{font-family: '" . $fontname . "' !important;}";
            $fp = fopen($stylefile, 'w');
            fwrite($fp, $style);
            fclose($fp);
            chmod($stylefile, 0777);
        }
        parent::postProcess(true);
    }
}
