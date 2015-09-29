<?php
namespace php\util;

require_once dirname(__FILE__) . "/../lang/IllegalArgumentException.php";
use php\lang\IllegalArgumentException as IllegalArgumentException;

class NoSuchElementException extends Exception
{
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
                parent::__construct($arg1);
                break;
            }
            default:
            {
                throw new IllegalArgumentException();
            }
        }
    }
}

?>