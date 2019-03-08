<?php

/**
 * 条码模块 生成 条码 code128
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */


namespace lib;
class barcode {

    public $path;

    public function __construct() {
        $this->path = ILIB . 'barcode' . DIRECTORY_SEPARATOR;
    }

    /**
     * 
     * @param type $text    文本
     * @param type $fontSize  字体大小
     * @param type $Resolution  条码级别  分 1、2、3
     * @param type $Thickness   条码高度  
     */
    public function index($text='2015043021203484_5',$fontSize=16,$Resolution=2,$Thickness=25) {
        // Including all required classes
        require($this->path.'BCGFont.php');
        require($this->path.'BCGColor.php');
        require($this->path.'BCGDrawing.php');
        include_once($this->path.'BCGBarcode1D.php');
        include_once($this->path.'BCGBarcode.php');
        include_once($this->path.'BCGDrawJPG.php');
        include_once($this->path.'BCGDrawPNG.php');

// Including the barcode technology
        include($this->path.'BCGcode128.barcode.php');

// Loading Font
        $font = new barcode\BCGFont($this->path.'font/Arial.ttf', $fontSize);

// The arguments are R, G, B for color.
        $color_black = new barcode\BCGColor(0, 0, 0);
        $color_white = new barcode\BCGColor(255, 255, 255);

        $code = new barcode\BCGcode128();
        $code->setScale($Resolution); // Resolution
        $code->setThickness($Thickness); // Thickness
        $code->setForegroundColor($color_black); // Color of bars
        $code->setBackgroundColor($color_white); // Color of spaces
        $code->setFont($font); // Font (or 0)
        $code->parse($text); // Text


        /* Here is the list of the arguments
          1 - Filename (empty : display on screen)
          2 - Background color */
        $drawing = new barcode\BCGDrawing('', $color_white);
        $drawing->setBarcode($code);
        $drawing->draw();

// Header that says it is an image (remove it if you save the barcode to a file)
        header('Content-Type: image/png');

// Draw (or save) the image into PNG format.
        $drawing->finish(barcode\BCGDrawing::IMG_FORMAT_PNG);
    }

}
