<?php
namespace com\lowagie\text;

require_once dirname(__FILE__) . "/../../../php/lang/TypeHint.php";
require_once dirname(__FILE__) . "/../../../php/lang/Comparable.php";
require_once dirname(__FILE__) . "/../../../php/awt/Color.php";
require_once "pdf/BaseFont.php";

use php\awt\Color as Color;
use com\lowagie\text\pdf\BaseFont as BaseFont;
use php\lang\Comparable as Comparable;

class Font implements Comparable
{
    const COURIER = 0;
    const HELVETICA = 1;
    const TIMES_ROMAN = 2;
    const SYMBOL = 3;
    const ZAPFDINGBATS = 4;


    const NORMAL = 0;
    const BOLD = 1;
    const ITALIC = 2;
    const UNDERLINE = 4;
    const STRIKETHRU = 8;

    public static $BOLDITALIC = 0;
    const UNDEFINED = -1;
    const DEFAULTSIZE = 12;

    public static $initialized = FALSE;


    private $family = Font::UNDEFINED;
    private $size = Font::UNDEFINED;
    private $style = Font::UNDEFINED;
    private $color = NULL;
    private $baseFont = NULL;
    
    public function __construct()
    {
        $argCount = func_num_args();
        switch($argCount) {
            case 1: {
                $arg1 = func_get_arg(0);
                if (($arg1 instanceof Font) == TRUE) {
                    $this->construct1argFont($arg1);
                }
                else if (($arg1 instanceof BaseFont) == TRUE) {
                    $this->construct1argBaseFont($arg1);
                }
                elseif (is_integer($arg1)) {
                    $this->construct1argInt($arg1);
                }
                break;
            }
            case 2: {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (($arg1 instanceof BaseFont) == TRUE && is_float($arg2)) {
                    $this->construct2argBaseFontFloat($arg1, $arg2);
                }
                elseif (is_integer($arg1) && is_float($arg2)) {
                    $this->construct2argIntFloat($arg1, $arg2);
                }
                break;
            }
            case 3: {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if (($arg1 instanceof BaseFont) == TRUE && is_float($arg2) && is_integer($arg3)) {
                    $this->construct3argBaseFontFloatInt($arg1, $arg2, $arg3);
                }
                else if (is_integer($arg1) && is_float($arg2) && is_integer($arg3)) {
                    $this->construct3argIntFloatInt($arg1, $arg2, $arg3);
                }
                break;
            }
            case 4: {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (is_integer($arg1) && is_float($arg2) && is_integer($arg3) && ($arg4 instanceof Color) == TRUE) {
                    $this->construct4argsIntColor($arg1, $arg2, $arg3, $arg4);
                }
                elseif (($arg1 instanceof BaseFont) == TRUE && is_float($arg2) && is_integer($arg3) && ($arg4 instanceof Color) == TRUE) {
                    $this->construct4argsBaseFontFloatIntColor($arg1, $arg2, $arg3, $arg4);
                }
                
                break;
            }
            default: {
                $this->construct4argsIntColor(Font::UNDEFINED, floatval(Font::UNDEFINED), Font::UNDEFINED, NULL);
            }
        }
    }
    
    /**
    * Copy constructor of a Font
    * 
    * @param other
    *            the font that has to be copied
    */
    private function construct1argFont(Font $other) {
        $this->family = $other->family;
        $this->size = $other->size;
        $this->style = $other->style;
        $this->color = $other->color;
    }
    
    /**
    * Constructs a Font.
    * 
    * @param family
    *            the family to which this font belongs
    * @param size
    *            the size of this font
    * @param style
    *            the style of this font
    * @param color
    *            the <CODE>Color</CODE> of this font.
    */
    private function construct4argsIntColor(integer $family, float $size, integer $style, Color $color) {
        $this->family = $family;
        $this->size = $size;
        $this->style = $style;
        $this->color = $color;
    }
    
    /**
    * Constructs a Font.
    * 
    * @param bf
    *            the external font
    * @param size
    *            the size of this font
    * @param style
    *            the style of this font
    * @param color
    *            the <CODE>Color</CODE> of this font.
    */
    private function construct4argsBaseFontFloatIntColor(BaseFont $bf, float $size, integer $style, Color $color) {
        $this->baseFont = $bf;
        $this->size = $size;
        $this->style = $style;
        $this->color = $color;
    }
    
    /**
    * Constructs a Font.
    * 
    * @param bf
    *            the external font
    * @param size
    *            the size of this font
    * @param style
    *            the style of this font
    */
    private function construct3argBaseFontFloatInt(BaseFont $bf, float $size, integer $style) {
        $this->construct4argsBaseFontFloatIntColor($bf, $size, $style, NULL);
    }
    
    /**
    * Constructs a Font.
    * 
    * @param bf
    *            the external font
    * @param size
    *            the size of this font
    */
    private function construct2argBaseFontFloat(BaseFont $bf, float $size) {
        $this->construct4argsBaseFontFloatIntColor($bf, $size, Font::UNDEFINED, NULL);
    }
    
    /**
    * Constructs a Font.
    * 
    * @param bf
    *            the external font
    */
    private function construct1argBaseFont(BaseFont $bf) {
        $this->construct4argsBaseFontFloatIntColor($bf, floatval(Font::UNDEFINED), Font::UNDEFINED, NULL);
    }
    
    /**
    * Constructs a Font.
    * 
    * @param family
    *            the family to which this font belongs
    * @param size
    *            the size of this font
    * @param style
    *            the style of this font
    */
    private function construct3argIntFloatInt(integer $family, float $size, integer $style) {
        $this->construct4argsIntColor($family, $size, $style, NULL);
    }
    
    /**
    * Constructs a Font.
    * 
    * @param family
    *            the family to which this font belongs
    * @param size
    *            the size of this font
    */
    private function construct2argIntFloat(integer $family, float $size) {
        $this->construct4argsIntColor($family, $size, Font::UNDEFINED, NULL);
    }
    
    /**
    * Constructs a Font.
    * 
    * @param family
    *            the family to which this font belongs
    */
    private function construct1argInt(integer $family) {
        $this->construct4argsIntColor($family, Font::UNDEFINED, Font:UNDEFINED, NULL);
    }
    
    // implementation of the Comparable interface

    /**
    * Compares this <CODE>Font</CODE> with another
    * 
    * @param object
    *            the other <CODE>Font</CODE>
    * @return a value
    */
    public function compareTo(object $o) {
        if ($o == NULL) {
            return -1;
        }
        
        if ((o instanceof Font) == FALSE) {
            return -3;
        }
        try {
            if ($this->baseFont != NULL && !($this->baseFont == $o->getBaseFont())) {
                return -2;
            }
            if ($this->family != $o->getFamily()) {
                return 1;
            }
            if ($this->size != $o->getSize()) {
                return 2;
            }
            if ($this->style != $o->getStyle()) {
                return 3;
            }
            if ($this->color == null) {
                if ($o->color == null) {
                    return 0;
                }
                return 4;
            }
            if ($o->color == null) {
                return 4;
            }
            if ($this->color == $o->getColor()) {
                return 0;
            }
            return 4;
        }
        catch (\Exception $e) {
            return -3;
        }
    }
    
    // FAMILY

    /**
    * Gets the family of this font.
    * 
    * @return the value of the family
    */
    public function getFamily() {
        return $this->family;
    }
    
    /**
    * Gets the familyname as a String.
    * 
    * @return the familyname
    */
    public function getFamilyname() {
        $tmp = "unknown";
        switch($this->getFamily()) {
            case Font::COURIER: {
                
            {
        }
    }
    
    
    
    /**
    * Gets the leading that can be used with this font.
    * 
    * @param linespacing
    *            a certain linespacing
    * @return the height of a line
    */
    public function getCalculatedLeading(float $linespacing) {
        return $linespacing * getCalculatedSize();
    }
    
    /**
    * Gets the size that can be used with the calculated <CODE>BaseFont
    * </CODE>.
    * 
    * @return the size that can be used with the calculated <CODE>BaseFont
    *         </CODE>
    */
    public function getCalculatedSize() {
        $s = $this->size;
        if ($s == Font::UNDEFINED) {
            $s = Fault::DEFAULTSIZE;
        }
        return $s;
    }
    
    

    public static function initializeStatics()
    {
        if(Font::$initialized == FALSE)
        {
            Font::$BOLDITALIC = Font::BOLD | Font::ITALIC;
            Font::$initialized = TRUE;
        }
    }
}

Font::initializeStatics();
?>