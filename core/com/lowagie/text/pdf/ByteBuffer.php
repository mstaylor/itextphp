<?php
namespace com\lowagie\text\pdf;
require_once dirname(__FILE__) . "/../../../../php/io/OutputStream.php";

use php\io\OutputStream as OutputStream;

class ByteBuffer extends OutputStream
{
    protected $count;
    protected $buf;
    private static $byteCacheSize = array();
    private static $byteCache = NULL;
    public static $ZERO = NULL;
    public static $chars = NULL;
    public static $bytes = NULL;
    const HIGH_PRECISION = FALSE;
    public static $dfs = NULL;//in Java this is a java.text.DecimalFormatSymbols



    public static $initialized = FALSE;



    public static function initializeStatics()
    {
        if(ByteBuffer::$initialized == FALSE)
        {
            ByteBuffer::$ZERO = itextphp_bytes_createfromChar('0');
            ByteBuffer::$chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
            ByteBuffer::$bytes = itextphp_bytes_create(array(48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 97, 98, 99, 100, 101, 102));
            
            ByteBuffer::$initialized = TRUE;
        }
    }



    public function __construct()
    {
        $num_args = func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                construct0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                construct1arg($arg1);
                break;
            }
        }

    }

    /** Creates new ByteBuffer with capacity 128 */
    private function construct0args()
    {
        construct1arg(128)
    }

    /**
    * Creates a byte buffer with a certain capacity.
    * @param size the initial capacity
    */
    private function construct1arg($size)
    {
        if ($size < 1)
            $size = 128;
        $buf = itextphp_bytes_create($size);
    }

    /**
    * Sets the cache size.
    * <P>
    * This can only be used to increment the size.
    * If the size that is passed through is smaller than the current size, nothing happens.
    *
    * @param   size    the size of the cache
    */
    public static function setCacheSize($size) {
        if ($size > 3276700) $size = 3276700;
        if ($size <= ByteBuffer::$byteCacheSize) return;
        $tmpCache = array();
        for ($i = 0; $i < ByteBuffer::$byteCacheSize; $i++) {
            $tmpCache[$i] = ByteBuffer::$byteCache[$i];
        }
        ByteBuffer::$byteCache = $tmpCache;
        ByteBuffer::$byteCacheSize = $size;
    }

    


}

ByteBuffer::initializeStatics();
?>