<?php
namespace com\lowagie\text\pdf;
require_once "ExtendedColor.php";

use com\lowagie\text\pdf\ExtendedColor as ExtendedColor;

class GrayColor extends ExtendedColor
{

   public static function initializeStatics()
    {
        if(GrayColor::$initialized == FALSE)
        {
            GrayColor::$GRAYBLACK = new GrayColor(0.0);
            GrayColor::$GRAYWHITE = new GrayColor(1.0);
            GrayColor::$initialized = TRUE;
        }
   }

    public function __construct()
    {
        $argCount = func_num_args();
        switch($argCount)
        {
            case 0:
            {
                parent::__construct();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE)
                {
                    $this->construct1argInteger($arg1);
                }
                else if (is_float($arg1) == TRUE)
                {
                    $this->construct1argFloat($arg1);
                }
                else
                {
                    throw new IllegalArgumentException("Constructor with 1 argument should be int or float");
                }
                break;
            }
            default:
            {
                throw new IllegalArgumentException("Constructor does not support $argCount args");
            }
        }
    }

    private function construct1argInteger($intGray)
    {
        $this->construct1argFloat($intGray / 255);
    }

    private function construct1argFloat($floatGray)
    {
        parent::__construct(ExtendedColor::TYPE_GRAY, $floatGray, $floatGray, $floatGray);
        $this->grey = $this->normalize($floatGray);
    }

    public function getGray()
    {
        return $this->grey;
    }

    public function equals($obj)
    {
        if (is_object($obj) == FALSE)
        {
            throw new IllegalArgumentException("Parameter type should be an object type");
        }

        return $obj instanceof GrayColor && $obj->gray == $this->gray;
    }

    public function hashCode()
    {
        return iTextPHP_floatToIntBits($this->grey);
    }
    private $grey;

    public static $initialized = FALSE;
    public static $GRAYBLACK = NULL;
    public static $GRAYWHITE = NULL;
}

GrayColor::initializeStatics();

?>