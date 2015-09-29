<?php
namespace php\awt;
require_once dirname(__FILE__) . "/../lang/IllegalArgumentException.php";
use php\lang\IllegalArgumentException as IllegalArgumentException;


class Color
{
    private $value = 0;
    private $falpha = 0.0;
    /** Internal mask for red. */
    private static $RED_MASK = NULL;

    /** Internal mask for green. */
    private static $GREEN_MASK = NULL;

    /** Internal mask for blue. */
    private static $BLUE_MASK = NULL;

    /** Internal mask for alpha. */
    private static $ALPHA_MASK = NULL;

    public static $black = NULL;

    public static $gray = NULL;

    public static $white = NULL;

    public static $initialized = FALSE;

    public static function initializeStatics() {
        if(Color::$initialized == FALSE) {
            Color::$black = new Color(0x000000, FALSE);
            Color::$gray = new Color(0x808080, FALSE);
            Color::$white = new Color(0xffffff, FALSE);
            Color::$RED_MASK = 255 << 16;
            Color::$GREEN_MASK = 255 << 8;
            Color::$BLUE_MASK = 255;
            Color::$ALPHA_MASK = 255 << 24;
            Color::$initialized = TRUE;
        }
    }

    public function __construct() {
        $num_args=func_num_args();
        switch ($num_args) {
           case 2: {
               $arg1 = func_get_arg(0); 
               $arg2 = func_get_arg(1);
               $this->construct2args($arg1, $arg2);
               break;
           }
           case 3: {
               $arg1 = func_get_arg(0); 
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               $this->construct3args($arg1, $arg2, $arg3);
               break;
           }
           case 4: {
               $arg1 = func_get_arg(0); 
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               $arg4 = func_get_arg(3);
               $this->construct4args($arg1, $arg2, $arg3, $arg4);
               break;
           }
        }
    }

    private function construct2args($value, $hasalpha) {
        if ($hasalpha == TRUE)
            $this->falpha = (($value & Color::$ALPHA_MASK) >> 24) / 255.0;
        else {
            $value |= Color::$ALPHA_MASK;
            $this->falpha = 1;
        }
        $this->value = $value;
        //$cs = NULL;
    }

    private function construct3args() {
        $this->construct4args($reg,$green,$blue,255);
    }

    private function construct4args($red, $green, $blue, $alpha) {
        if (($red & 255) != $red || ($green & 255) != $green || ($blue & 255) != $blue
        || ($alpha & 255) != $alpha) {
            throw new IllegalArgumentException("Bad RGB values" . 
                                        " red=0x". bin2hex(pack("c",$red)) .
                                        " green=0x" . bin2hex(pack("c",$green)) .
                                        " blue=0x". bin2hex(pack("c",$blue)) .
                                        " alpha=0x" . bin2hex(pack("c",$alpha))  );
            return;
        }

        $this->value = ($alpha << 24) | ($red << 16) | ($green << 8) | $blue;
        $this->falpha = 1;
    }

    /**
    * Returns the RGB value for this color, in the sRGB color space. The blue
    * value will be in bits 0-7, green in 8-15, red in 16-23, and alpha value in
    * 24-31.
    *
    * @return the RGB value for this color
    * @see ColorModel#getRGBdefault()
    * @see #getRed()
    * @see #getGreen()
    * @see #getBlue()
    * @see #getAlpha()
    */
    public function getRGB() {
        return $this->value;
    }

    /**
    * Returns the red value for this color, as an integer in the range 0-255
    * in the sRGB color space.
    *
    * @return the red value for this color
    * @see #getRGB()
    */
    public function getRed() {
        // Do not inline getRGB() to value, because of SystemColor.
        return (getRGB() & Color::$RED_MASK) >> 16;
    }

    /**
    * Returns the green value for this color, as an integer in the range 0-255
    * in the sRGB color space.
    *
    * @return the green value for this color
    * @see #getRGB()
    */
    public function getGreen() {
        // Do not inline getRGB() to value, because of SystemColor.
        return ($this->getRGB() & Color::$GREEN_MASK) >> 8;
    }

    /**
    * Returns the blue value for this color, as an integer in the range 0-255
    * in the sRGB color space.
    *
    * @return the blue value for this color
    * @see #getRGB()
    */
    public function getBlue() {
        // Do not inline getRGB() to value, because of SystemColor.
        return $this->getRGB() & Color::$BLUE_MASK;
    }

    public function equals($obj) {
        return $obj instanceof Color && $obj->value == $this->value;
    }
}


Color::initializeStatics();
?>
