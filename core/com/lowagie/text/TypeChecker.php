<?php
namespace com\lowagie\text;


class TypeChecker
{

    public static function checkForFloat($value)
    {
       if (is_float($value) == FALSE)
       {
           throw new \Exception("value $value is not type float");
       }
    }

    public static function checkForInteger($value)
    {
        if (is_integer($value) == FALSE)
        {
            throw new \Exception("value $value is not type integer");
        }
    }

    public static function checkForBool($value)
    {
       if (is_bool($value) == FALSE)
       {
           throw new \Exception("value $value is not type bool");
       }
    }
}
?>