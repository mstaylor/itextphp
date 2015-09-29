<?php
namespace com\lowagie\text\pdf;
require_once dirname(__FILE__) . "/../../../../php/awt/Color.php";
require_once dirname(__FILE__) . "/../../../../php/lang/IllegalArgumentException.php";

use php\awt\Color as Color;
use php\lang\IllegalArgumentException as IllegalArgumentException;

class ExtendedColor extends Color
{
    const TYPE_RGB = 0;
    const TYPE_GRAY = 1;
    const TYPE_CMYK = 2;
    const TYPE_SEPARATION = 3;
    const TYPE_PATTERN = 4;
    const TYPE_SHADING = 5;

    protected $type;

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
                    $this->construct1arg($arg1);
                }
                else
                {
                    throw new IllegalArgumentException("Constructor with 1 argument should be int");
                }
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (is_integer($arg1) == TRUE && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE)
                {
                    $this->construct4arg($arg1, $arg2, $arg3, $arg4);
                }
                else
                {
                     throw new IllegalArgumentException("Constructor with 4 arguments should be int, float, float, float");
                }
                break;
            }
            default:
            {
                throw new IllegalArgumentException("Constructor does not support $argCount args");
            }
        }
    }

    private function construct1arg($type)
    {
        parent::__construct(0, 0, 0);
        $this->type = $type;
    }

    private function construct4arg($type, $red, $green, $blue)
    {
        parent::__construct($this->normalize($red), $this->normalize($green), $this->normalize($blue));
        $this->type = $type;
    }

    public function getType()
    {
        $numberArgs = func_num_args();
        switch ($numberArgs)
        {
            case 0:
            {
                return $this->getTypeZeroArgs();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                return $this->getTypeOneArg($arg1);
                break;
            }
            default:
            {
                throw new IllegalArgumentException("Invalid Number of Arguments");
            }
        }
    }

    private function getTypeZeroArgs()
    {
        return $this->type;
    }

    private function getTypeOneArg(Color $color)
    {
        if ($color instanceof ExtendedColor)
        {
            return parent::getType();
        }

        return Color::TYPE_RGB;
    }

    final static function normalize($value)
    {
        if (is_float($value) == FALSE)
        {
            throw new Exception("value $value is not type float");
        }

        if ($value < 0)
            return 0;
        if ($value > 1)
            return 1;
        return $value;
    }
}

?>