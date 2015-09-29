<?php
namespace php\io;
require_once dirname(__FILE__) . "/../lang/IllegalArgumentException.php";
require_once dirname(__FILE__) . "/../lang/ArrayIndexOutOfBoundsException.php";
use php\lang\IllegalArgumentException as IllegalArgumentException;
use php\lang\ArrayIndexOutOfBoundsException as ArrayIndexOutOfBoundsException;

abstract class OutputStream
{
   public function write()
   {
       $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_resource($arg1) == TRUE)
                {
                    $this->writeresource($arg1);
                }
                else if (is_integer($arg1) == TRUE)
                {
                    $this->writeInt($arg);
                }
                else
                {
                    throw new IllegalArgumentException("write (1 argument version) does not support supplied argument type " . gettype($arg1));
                }
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if (is_resource($arg1) && is_integer($arg2) == TRUE && is_integer($arg3) == TRUE)
                {
                    $this->writeresource3args($arg1, $arg2, $ar3);
                }
                else
                {
                    throw new IllegalArgumentException("wirte (3 argument version) does not support supplied argument types:  " . gettype($arg1) . ", " . gettype($arg2) . ", " . gettype($arg3));
                }
                break;
            }
        }
   }
   abstract public function writeInt($b);
   private function writeresource($b){ write($b,0, itextphp_bytes_getSize($b));}
   private function writeresource3args($b, $off, $len)
   {
       if ($off < 0 || $len < 0 || $off + $len > itextphp_bytes_getSize($b))
       {
           throw new ArrayIndexOutOfBoundsException();
       }
       for ($i = 0; $i < $len; ++$i)
       {
            /*itextphp_write_bytes_to_page($b, $off + $i);*/
            $this->writeInt(itextphp_bytes_getIntValue($b, $off + $i));
       }
   }
   public function close(){}
   public function flush(){}
}




?>