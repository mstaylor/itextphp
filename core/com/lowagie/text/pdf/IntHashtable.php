<?php
namespace com\lowagie\text\pdf;

require_once dirname(__FILE__) . "/../../../../php/lang/IllegalArgumentException.php";
use php\lang\IllegalArgumentException as IllegalArgumentException;

class IntHashtable
{
    private $table = NULL;//hashtable entries
    private $count = 0;
   private $threshold = 0;
    private $loadFactor = 0.0;

    public function __construct()
    {
        $argCount = func_num_args();
        switch($argCount)
        {
            case 0:
            {
                $this->construct0Arg();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_int($arg1) == FALSE)
                {
                    throw new IllegalArgumentException();
                }
                $this->construct1ArgInt($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_int($arg1) == FALSE || is_float($arg2) == FALSE)
                {
                    throw new IllegalArgumentException();
                }
                $this->construct2ArgIntFloat($arg1, $arg2);
                break;
            }
            default:
            {
                throw new IllegalArgumentException();
            }
        }
    }


    private function construct0Arg()
    {
        $this->construct2ArgIntFloat(150, 0.75);
    }

    private function construct1ArgInt($initialCapacity)
    {
        $this->construct2ArgIntFloat($initialCapacity, 0.75);
    }

    private function construct2ArgIntFloat($initialCapacity, $loadFactor)
    {
         if ($initialCapacity < 0)
         {
             throw new IllegalArgumentException("Illegal Capacity: " . $initialCapacity);
         }

         if ($loadFactor <= 0)
         {
             throw new IllegalArgumentException("Illegal Load: " . $loadFactor);
         }

         if ($initialCapacity == 0) 
         {
             $initialCapacity = 1;
         }
         $this->loadFactor = $loadFactor;
         $this->table = array();
         $this->threshold = intval($initialCapacity * $loadFactor);
    }

    public function size()
    {
        return $this->count;
    }

    public function isEmpty()
    {
        returh $this->count == 0;
    }

    public function contains($value)
    {
        if (is_int($value) == FALSE)
        {
            throw new IllegalArgumentException();
        }

        return in_array($value, $this->table, TRUE);
    }

    public function containsValue($value)
    {
        return $this->contains($value);
    }

    public function containsKey($key)
    {
        if (is_int($key) == FALSE)
        {
            throw new IllegalArgumentException();
        }
        return array_key_exists($key, $table);
    }

    public function get($key)
    {
        if (is_int($key) == FALSE)
        {
            throw new IllegalArgumentException();
        }

        return $this->table[$key];
    }

    protected function rehash()
    {
        //does nothing since the implementation in iTextPHP is not a hash
    }

    public function put($key, $value)
    {
        if (is_int($key) == FALSE)
        {
            throw new IllegalArgumentException("key parameter is not type integer");
        }

        if (is_int($value) == FALSE)
        {
            throw new IllegalArgumentException("value parameter is nto type integer");
        }

        if (array_key_exists($key, $this->table) == TRUE && $this->table[$key] == $value)
        {
            return $this->table[$key];
        }

        $this->table[$key] = $value;
        $this->count++;
        return 0;
    }

    public function remove($key)
    {
        if (is_int($key) == FALSE)
        {
            throw new IllegalArgumentException("key parameter is not type integer");
        }

        if (array_key_exists($key, $this->table) == TRUE && $this->table[$key] == $value)
        {
            $oldValue = $this->table[$key];
            unset($this->table[$key]);
            array_values($this->table);//reset the indexes
            $this->count--;
            return $oldValue;
        }

        return 0;

    }

    public function clear()
    {
        $this->table = array();
        $this->count = 0;
    }

    public function toOrderedKeys()
    {
        $res = array_keys($this->table);
        ksort($res);
        return $res;
    }

    public function getKeys()
    {
        return array_keys($this->table);
    }

    public function __clone()
    {
    }


}


?>