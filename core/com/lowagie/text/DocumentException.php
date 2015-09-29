<?php
namespace com\lowagie\text;

class DocumentException extends \Exception
{

    private $theEX = NULL;
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
                if ($arg1 instanceof Exception)
                {
                    construct1ArgException($arg1);
                }
                else if (is_string($arg1) == TRUE)
                {
                    construct1ArgString($arg1);
                }
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                parent::__construct($arg1, $arg2);
                break;
            }
        }
    }

    private function construct1ArgException($ex)
    {
        if ($ex == NULL)
        {
            parent::__construct();
        }
        $theEX = $ex;
        parent::__construct($ex->getMessage(), $ex->getCode());
    }

    private function construct1ArgString($str)
    {
        if ($str == NULL)
        {
            parent::__construct();
        }

        parent::__construct($str, 0);
    }

    public function __toString()
    {
        if ($theEX == NULL)
        {
            return parent::__toString();
        }

        return get_class($this) . ":" . $ex->__toString();
    }

    private static function split($s)
    {
        $i = strpos(s, ".");
        if ($i == FALSE)
        {
            return $s;
        }

        return substr($s, $i+1);
    }
}


?>