<?php

namespace com\lowagie\text;
require_once dirname(__FILE__) . "/../../../php/lang/TypeHint.php";

require_once "Rectangle.php";
require_once "pdf/PdfTemplate.php";

use com\lowagie\text\Rectangle as Rectangle;
use com\lowagie\text\pdf\PdfTemplate as PdfTemplate;

abstract class Image extends Rectangle {

    //Class Constants
    /** this is a kind of image alignment. */
    const iDEFAULT = 0;
    /** this is a kind of image alignment. */
    const RIGHT = 2;
    /** this is a kind of image alignment. */
    const LEFT = 0;
    /** this is a kind of image alignment. */
    const MIDDLE = 1;
    /** this is a kind of image alignment. */
    const TEXTWRAP = 4;
    /** this is a kind of image alignment. */
    const UNDERLYING = 8;
    /** This represents a coordinate in the transformation matrix. */
    const AX = 0;
    /** This represents a coordinate in the transformation matrix. */
    const AY = 1;
    /** This represents a coordinate in the transformation matrix. */
    const BX = 2;
    /** This represents a coordinate in the transformation matrix. */
    const BY = 3;
    /** This represents a coordinate in the transformation matrix. */
    const CX = 4;
    /** This represents a coordinate in the transformation matrix. */
    const CY = 5;
    /** This represents a coordinate in the transformation matrix. */
    const DX = 6;
    /** This represents a coordinate in the transformation matrix. */
    const DY = 7;
    /** type of image */
    const ORIGINAL_NONE = 0;
    /** type of image */
    const ORIGINAL_JPEG = 1;
    /** type of image */
    const ORIGINAL_PNG = 2;
    /** type of image */
    const ORIGINAL_GIF = 3;
    /** type of image */
    const ORIGINAL_BMP = 4;
    /** type of image */
    const ORIGINAL_TIFF = 5;
    /** type of image */
    const ORIGINAL_WMF = 6;
    /** type of image */
    const ORIGINAL_PS = 7;
    /** type of image */
    const ORIGINAL_JPEG2000 = 8;
    
    //Member Variables
    /** The image type. */
    protected $type;//int
    /** The URL of the image. */
    protected $url;//url?
    /** The raw data of the image. */
    protected $rawDate;//byte array
    /** The bits per component of the raw image. It also flags a CCITT image. */
    protected $bpc = 1;
    /** The template to be treated as an image. */
    protected $template;//array of template -- size 1
    /** The alignment of the Image. */
    protected $alignment;//int
    /** Text that can be shown instead of the image. */
    protected $alt;//string
    /** This is the absolute X-position of the image. */
    protected $absoluteX = NAN;
    /** This is the absolute Y-position of the image. */
    protected $absoluteY = NAN;
    /** This is the width of the image without rotation. */
    protected $plainWidth;//float
    /** This is the width of the image without rotation. */
    protected $plainHeight;//float
    /** This is the scaled width of the image taking rotation into account. */
    protected $scaledWidth;//float
    
    protected $mySerialId;//long
    
    //static variables
    static $serialId = 0;
    
    public function __construct()
    {
        $this->template = array(new PdfTemplate());
        $this->mySerialId = Image::getSerialId();
        $argCount = func_num_args();
        switch($argCount) {
        
            Default: {
               
            }
        }
    }
    
    
    
    public static function getInstance() {
        $argCount = func_num_args();
        switch($argCount) {
            case 1: {
                arg1 = func_get_arg(0);
                if (($arg1 instanceof Image) == TRUE) {
                    $this->getInstance1argImage($arg1);
                }
                break;
            }
        }
    }
    
    private static function getInstance1argImage(Image $image) {
        if ($image == NULL) {
            return;
        }
    }
    
    /**
    * Gets the scaled width of the image.
    * 
    * @return a value
    */
    public function getScaledWidth() {
        return $this->scaledWidth;
    }
    
    
    
    static function getSerialId() {
        ++Image::$serialId;
        return Image::$serialId;
    }
    
}

?>