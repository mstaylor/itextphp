<?PHP
/*
 * $Id: ByteBuffer.php,v 1.3 2005/10/03 16:20:26 mstaylor Exp $
 * $Name:  $
 *
 * Copyright 2005 by Mills W. Staylor, III.
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the License.
 *
 * The Original Code is 'iText, a free Java-PDF library'.
 *
 *
 * Alternatively, the contents of this file may be used under the terms of the
 * LGPL license (the "GNU LIBRARY GENERAL PUBLIC LICENSE"), in which case the
 * provisions of LGPL are applicable instead of those above.  If you wish to
 * allow use of your version of this file only under the terms of the LGPL
 * License and not to allow others to use your version of this file under
 * the MPL, indicate your decision by deleting the provisions above and
 * replace them with the notice and other provisions required by the LGPL.
 * If you do not delete the provisions above, a recipient may use your version
 * of this file under either the MPL or the GNU LIBRARY GENERAL PUBLIC LICENSE.
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the MPL as stated above or under the terms of the GNU
 * Library General Public License as published by the Free Software Foundation;
 * either version 2 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Library general Public License for more
 * details.
 */


require_once("../../io/OutputStream.php");
require_once("../../exceptions/IOException.php");
require_once("PdfEncodings.php");
require_once("../DocWriter.php");


class ByteBuffer extends OutputStream 
{

    /** The count of bytes in the buffer. */
    protected $count = 0;

    /** The buffer where the bytes are stored. */
    protected $buf;
    
    private static $byteCacheSize = 0;
    
    private static $byteCache = array();
    public static $ZERO = NULL;
    private static $chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    private static $bytes = NULL;


    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(ByteBuffer::$initialized == FALSE)
        {
            ByteBuffer::$bytes = itextphp_bytes_create(16);
            itextphp_bytes_update(ByteBuffer::$bytes, 0, 48);
            itextphp_bytes_update(ByteBuffer::$bytes, 1, 49);
            itextphp_bytes_update(ByteBuffer::$bytes, 2, 50);
            itextphp_bytes_update(ByteBuffer::$bytes, 3, 51);
            itextphp_bytes_update(ByteBuffer::$bytes, 4, 52);
            itextphp_bytes_update(ByteBuffer::$bytes, 5, 53);
            itextphp_bytes_update(ByteBuffer::$bytes, 6, 54);
            itextphp_bytes_update(ByteBuffer::$bytes, 7, 55);
            itextphp_bytes_update(ByteBuffer::$bytes, 8, 56);
            itextphp_bytes_update(ByteBuffer::$bytes, 9, 57);
            itextphp_bytes_update(ByteBuffer::$bytes, 10, 97);
            itextphp_bytes_update(ByteBuffer::$bytes, 11, 98);
            itextphp_bytes_update(ByteBuffer::$bytes, 12, 99);
            itextphp_bytes_update(ByteBuffer::$bytes, 13, 100);
            itextphp_bytes_update(ByteBuffer::$bytes, 14, 101);
            itextphp_bytes_update(ByteBuffer::$bytes, 15, 102);

            ByteBuffer:$ZERO = itextphp_bytes_createfromRaw('0');
            ByteBuffer::$initialized = TRUE;
        }
    }

    public function __construct()
    {
        $num_args=func_num_args();
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
        construct1arg(128;)
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
        if ($size <= $byteCacheSize) return;
        $tmpCache = array();
        for ($i = 0; $i < $byteCacheSize; $i++) {
            $tmpCache[$i] = $byteCache[$i];
        }
        $byteCache = $tmpCache;
        $byteCacheSize = $size;
    }

    /**
    * You can fill the cache in advance if you want to.
    *
    * @param   decimals
    */

    public static function fillCache(int decimals) {
        $step = 1;
        switch($decimals) {
            case 0:
                $step = 100;
                break;
            case 1:
                $step = 10;
                break;
        }
        for ($i = 1; $i < $byteCacheSize; $i += $step) {
            if ($byteCache[$i] != NULL) continue;
            $byteCache[$i] = ByteBuffer::convertToBytes($i);
        }
    }


    /**
    * Converts an double (multiplied by 100 and cast to an int) into an array of bytes.
    *
    * @param   i   the int
    * @return  a bytearray
    */
    private static function convertToBytes($i) {
        $size = (integer)floor(log($i) / log(10));
        if ($i % 100 != 0) {
            $size += 2;
        }
        if ($i % 10 != 0) {
            $size++;
        }
        if ($i < 100) {
            $size++;
            if ($i < 10) {
                $size++;
            }
        }
        $size--;
        $cache = itextphp_bytes_create($size);
        $size --;
        if ($i < 100) {
            $tmpCache = itextphp_bytes_createfromRaw('0');
            itextphp_updateByteWithByte($cache, 0, $tmpCache, 0);
        }
        if ($i % 10 != 0) {
            itextphp_updateByteWithByte($cache, $size--, $bytes, ($i % 10));
        }
        if ($i % 100 != 0) {
            itextphp_updateByteWithByte($cache, $size--, $bytes, (($i / 10) % 10));
            $tmpCache = itextphp_bytes_createfromRaw('.');
            itextphp_updateByteWithByte($cache, $size--, $tmpCache, 0);
        }
        $size = (integer)floor(log($i) / log(10)) - 1;
        $add = 0;
        while ($add < $size) {
            cache[add] = bytes[(i / (int)Math.pow(10, size - add + 1)) % 10];
            itextphp_updateByteWithByte($cache, $add, $bytes, ($i / (integer)pow(10, $size - $add + 1)) % 10);
            $add++;
        }
        return $cache;
    }



    /**
    * Appends an <CODE>int</CODE>. The size of the array will grow by one.
    * @param b the int to be appended
    * @return a reference to this <CODE>ByteBuffer</CODE> object
    */
    public function append_i($b) {
        $newcount = $count + 1;
        if ($newcount > itextphp_bytes_getSize($buf)) {
            $newbuf = itextphp_bytes_create(max(itextphp_bytes_getSize($buf) << 1, $newcount));
            for ($k= 0; $k < $count; $k++)
            {
                itextphp_updateByteWithByte($newbuf, $k, $buf, $k);
            }
            $buf = $newbuf;
        }
        $tempByte = itextphp_bytes_createfromInt($b);
        itextphp_updateByteWithByte($buf, $count, $tempByte, 0);

        $count = $newcount;
        return $this;
    }

    public function append()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_string($arg1) == TRUE)
                {
                    return append1argstring($arg1);
                }
                else if (is_resource($arg1) == TRUE)
                {
                    return append1argbytes($arg1);
                }
                else if ($arg1 instanceof ByteBuffer)
                {
                    return append1argbytebuffer($arg1);
                }
                else if (is_integer($arg1) == TRUE)
                {
                    return append1arginteger($arg1);
                }
                else if (is_double($arg1) == TRUE)
                {
                    return append1argdouble($arg1);
                }
                else if (is_float($arg1) == TRUE)
                {
                    return append1argfloat($arg1);
                }
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                return append3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    /**
    * Appends a <CODE>String</CODE> to the buffer. The <CODE>String</CODE> is
    * converted according to the encoding ISO-8859-1.
    * @param str the <CODE>String</CODE> to be appended
    * @return a reference to this <CODE>ByteBuffer</CODE> object
    */
    private function append1argstring($str)
    {
        if ($str != NULL)
            return append1argbytes(DocWriter::getISOBytes($str));
        return $this;
    }


    /**
    * Appends an array of bytes.
    * @param b the array to be appended
    * @return a reference to this <CODE>ByteBuffer</CODE> object
    */
    private function append1argbytes($b)
    {
        return append3args($b, 0, itextphp_bytes_getSize($b));
    }

    /**
    * Appends another <CODE>ByteBuffer</CODE> to this buffer.
    * @param buf the <CODE>ByteBuffer</CODE> to be appended
    * @return a reference to this <CODE>ByteBuffer</CODE> object
    */
    private function append1argbytebuffer($buf)
    {
        return append3args($buf->buf, 0, $buf->count);
    }


    /**
    * Appends the string representation of an <CODE>int</CODE>.
    * @param i the <CODE>int</CODE> to be appended
    * @return a reference to this <CODE>ByteBuffer</CODE> object
    */
    private function append1arginteger($i)
    {
        return append1argdouble((double)$i);
    }


    /**
    * Appends a string representation of a <CODE>double</CODE> according
    * to the Pdf conventions.
    * @param d the <CODE>double</CODE> to be appended
    * @return a reference to this <CODE>ByteBuffer</CODE> object
    */
    private function append1argdouble($d) {
        append1argstring(formatDouble($d, $this));
        return $this;
    }

    /**
    * Appends a string representation of a <CODE>float</CODE> according
    * to the Pdf conventions.
    * @param i the <CODE>float</CODE> to be appended
    * @return a reference to this <CODE>ByteBuffer</CODE> object
    */
    private function append1argfloat append($i) {
        return append1argdouble((double)$i);
    }

    /**
    * Appends the subarray of the <CODE>byte</CODE> array. The buffer will grow by
    * <CODE>len</CODE> bytes.
    * @param b the array to be appended
    * @param off the offset to the start of the array
    * @param len the length of bytes to append
    * @return a reference to this <CODE>ByteBuffer</CODE> object
    */
    private function append3args($b, $off, $len) {
        if (($off < 0) || ($off > itextphp_bytes_getSize($b)) || ($len < 0) ||
        (($off + $len) > itextphp_bytes_getSize($b)) || (($off + $len) < 0) || $len == 0)
            return $this;
        $newcount = $count + $len;
        if ($newcount > itextphp_bytes_getSize($buf)) {
            $newbuf = itextphp_bytes_create(max(itextphp_bytes_getSize($buf) << 1, $newcount)];
            for ($k= 0; $k < $count; $k++)
            {
                itextphp_updateByteWithByte($newbuf, $k, $buf, $k);
            }
            $buf = $newbuf;
        }
        //System.arraycopy(b, off, buf, count, len);
        for ($k = 0; $k < $len; $k++)
        {
            itextphp_updateByteWithByte($buf, ($k+$count), $b, ($k+$off));
        }
        $count = $newcount;
        return $this;
    }
    ///note I changed the function below to require the actual buffer and place
    public function appendHex($buffer, $place) {
        append_i(itextphp_bytebuffer_appendhex($bytes, $b, 0x0f));
        return append_i(itextphp_bytebuffer_appendhex($bytes, $b, 0x0f,0));
    }

    public static function formatDouble()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                return formatDouble1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return formatDouble2args($arg1, $arg2);
                break;
            }
        }
    }

    /**
    * Outputs a <CODE>double</CODE> into a format suitable for the PDF.
    * @param d a double
    * @return the <CODE>String</CODE> representation of the <CODE>double</CODE>
    */
    private static function formatDouble1arg($d)
    {
        return formatDouble2args($d, NULL);
    }

    /**
    * Outputs a <CODE>double</CODE> into a format suitable for the PDF.
    * @param d a double
    * @return the <CODE>String</CODE> representation of the <CODE>double</CODE> if
    * <CODE>d</CODE> is <CODE>null</CODE>. If <CODE>d</CODE> is <B>not</B> <CODE>null</CODE>,
    * then the double is appended directly to the buffer and this methods returns <CODE>null</CODE>.
    */
    private static function formatDouble2args($d, ByteBuffer $buf)
    {
        $negative = FALSE;
        $tmpByte1 = itextphp_bytes_createfromRaw('-');
        $tmpByte2 = itextphp_bytes_createfromRaw('1');
        $tmpByte3 = itextphp_bytes_createfromRaw('0');
        $tmpByte4 = itextphp_bytes_createfromRaw('.');
        if (abs($d) < 0.000015) {
            if ($buf != NULL) {
                $buf->append_i(itextphp_bytes_getIntValue(ByteBuffer::$ZERO, 0));
                return NULL;
            } else {
                return "0";
            }
        }
        if ($d < 0) {
            $negative = NULL;
            $d = -$d;
        }
        if ($d < 1.0) {
            $d += 0.000005;
            if ($d >= 1) {
                if ($negative == TRUE) {
                    if ($buf != NULL) {

                        $buf->append_i(itextphp_bytes_getIntValue($tmpByte1, 0));
                        $buf->append_i(itextphp_bytes_getIntValue($tmpByte2, 0));
                        return NULL;
                    } else {
                        return "-1";
                    }
                } else {
                    if ($buf != NULL) {
                        $buf->append_i(itextphp_bytes_getIntValue($tmpByte2, 0));
                        return NULL;
                    } else {
                        return "1";
                    }
                }
            }
            if ($buf != NULL) {
                $v = (integer) ($d * 100000);

                if ($negative == TRUE) $buf->append_i(itextphp_bytes_getIntValue($tmpByte1, 0));
                $buf->append_i(itextphp_bytes_getIntValue($tmpByte3, 0));
                $buf->append_i(itextphp_bytes_getIntValue($tmpByte4, 0));
                
                $buf->append_i($v / 10000 + itextphp_bytes_getIntValue(ByteBuffer::$ZERO, 0) );
                if ($v % 10000 != 0) {
                    $buf->append_i((($v / 1000) % 10 + itextphp_bytes_getIntValue(ByteBuffer::$ZERO, 0)) );
                    if ($v % 1000 != 0) {
                        $buf->append_i( (($v / 100) % 10 + itextphp_bytes_getIntValue(ByteBuffer::$ZERO, 0)) );
                        if ($v % 100 != 0) {
                            $buf->append_i((($v / 10) % 10 + itextphp_bytes_getIntValue(ByteBuffer::$ZERO, 0)) );
                            if ($v % 10 != 0) {
                                $buf->append_i((($v) % 10 + itextphp_bytes_getIntValue(ByteBuffer::$ZERO, 0)) );
                            }
                        }
                    }
                }
                return NULL;
            } else {
                $x = 100000;
                $v = (integer) ($d * $x);

                $res = "";
                if ($negative == TRUE) $res .= '-';
                $res .= "0.";

                while( $v < $x/10 ) {
                    $res .= '0';
                    $x /= 10;
                }
                $res .= $v;
                $cut = strlen($res) - 1;
                while ($res[$cut] == '0') {
                    --$cut;
                }
                //res.setLength(cut + 1);
                return substr($res, $cut);
            }
        } else if ($d <= 32767) {
            $d += 0.005;
            $v = (integer) ($d * 100);
            
            if ($v < ByteBuffer::$byteCacheSize && ByteBuffer::$byteCache[$v] != NULL) {
                if ($buf != NULL) {
                    if ($negative == TRUE) $buf->append_i(itextphp_bytes_getIntValue($tmpByte1, 0));
                    $buf->append_i(itextphp_bytes_getIntValue($byteCache[$v], 0);
                    return null;
                } else {
                    $tmp = PdfEncodings::convertToString($byteCache[$v], NULL);
                    $tmp2 = "-";
                    if ($negative == TRUE) 
                    {
                        itextphp_string_append($tmp2, $tmp);
                    }
                    return $tmp;
                }
            }
            if ($buf != NULL) {
                if ($v < $byteCacheSize) {
                    //create the cachebyte[]
                    $cache = NULL;
                    $size = 0;
                    if ($v >= 1000000) {
                        //the original number is >=10000, we need 5 more bytes
                        $size += 5;
                    } else if ($v >= 100000) {
                        //the original number is >=1000, we need 4 more bytes
                        $size += 4;
                    } else if ($v >= 10000) {
                        //the original number is >=100, we need 3 more bytes
                        $size += 3;
                    } else if ($v >= 1000) {
                        //the original number is >=10, we need 2 more bytes
                        $size += 2;
                    } else if ($v >= 100) {
                        //the original number is >=1, we need 1 more bytes
                        $size += 1;
                    }
                    
                    //now we must check if we have a decimal number
                    if ($v % 100 != 0) {
                        //yes, do not forget the "."
                        $size += 2;
                    }
                    if ($v % 10 != 0) {
                        $size++;
                    }
                    $cache = itextphp_bytes_create($size);
                    $add = 0;
                    if ($v >= 1000000) {
                        itextphp_updateByteWithByte($cache, $add++, ByteBuffer::$bytes, ($v / 1000000));
                    }
                    if ($v >= 100000) {
                        itextphp_updateByteWithByte($cache, $add++, ByteBuffer::$bytes, ($v / 100000) % 10);
                    }
                    if ($v >= 10000) {
                        itextphp_updateByteWithByte($cache, $add++, ByteBuffer::$bytes, ($v / 10000) % 10);
                    }
                    if ($v >= 1000) {
                        itextphp_updateByteWithByte($cache, $add++, ByteBuffer::$bytes, ($v / 1000) % 10);
                    }
                    if ($v >= 100) {
                        itextphp_updateByteWithByte($cache, $add++, ByteBuffer::$bytes, ($v / 100) % 10);
                    }

                    if ($v % 100 != 0) {
                        itextphp_updateByteWithByte($cache, $add++,$tmpByte4,0);
                        itextphp_updateByteWithByte($cache, $add++, ByteBuffer::$bytes, ($v / 10) % 10);
                        if ($v % 10 != 0) {
                            itextphp_updateByteWithByte($cache, $add++, ByteBuffer::$bytes, $v % 10);
                        }
                    }
                    $byteCache[$v] = $cache;
                }
                
                if ($negative == TRUE) $buf->append_i(itextphp_bytes_getIntValue($tmpByte1, 0));
                if ($v >= 1000000) {
                    $buf->append_i( itextphp_bytes_getIntValue($bytes, ($v / 1000000) ));
                }
                if ($v >= 100000) {
                    $buf->append_i( itextphp_bytes_getIntValue($bytes, ($v / 100000) % 10) );
                }
                if ($v >= 10000) {
                    $buf->append_i( itextphp_bytes_getIntValue($bytes, ($v / 10000) % 10) );
                }
                if ($v >= 1000) {
                    $buf->append_i( itextphp_bytes_getIntValue($bytes, ($v / 1000) % 10) );
                }
                if ($v >= 100) {
                    $buf->append_i( itextphp_bytes_getIntValue($bytes, ($v / 100) % 10) );
                }

                if ($v % 100 != 0) {
                    $buf->append_i(itextphp_bytes_getIntValue($tmpByte4, 0));
                    $buf->append_i( itextphp_bytes_getIntValue($bytes, ($v / 10) % 10) );
                    if ($v % 10 != 0) {
                        $buf->append_i( itextphp_bytes_getIntValue($bytes, $v % 10) );
                    }
                }
                return NULL;
            } else {
                $res = "";
                if ($negative == TRUE) $res .= '-';
                if ($v >= 1000000) {
                    $res .= ByteBuffer::$chars[($v / 1000000)];
                }
                if ($v >= 100000) {
                    $res .= ByteBuffer::$chars[($v / 100000) % 10];
                }
                if ($v >= 10000) {
                    $res .= ByteBuffer::$chars[($v / 10000) % 10];
                }
                if ($v >= 1000) {
                    $res .= ByteBuffer::$chars[($v / 1000) % 10];
                }
                if ($v >= 100) {
                    $res .= ByteBuffer::$chars[($v / 100) % 10];
                }

                if ($v % 100 != 0) {
                    $res .= '.';
                    $res .= ByteBuffer::$chars[($v / 10) % 10];
                    if ($v % 10 != 0) {
                        $res .= ByteBuffer::$chars[$v % 10];
                    }
                }
                return $res;
            }
        } else {
            $res = "";
            if ($negative == TRUE) $res .= '-';
            $d += 0.5;
            $v = (integer)$d;
            return $res .= $v;
        }
    }

    /**
    * Sets the size to zero.
    */
    public function reset() {
        $count = 0;
    }


    /**
    * Creates a newly allocated byte array. Its size is the current
    * size of this output stream and the valid contents of the buffer
    * have been copied into it.
    *
    * @return  the current contents of this output stream, as a byte array.
    */
    public function toByteArray() {
        $newbuf = itextphp_bytes_create($count);
        //System.arraycopy(buf, 0, newbuf, 0, count);
        for($k = 0; $k < $count, $k++)
        {
            itextphp_updateByteWithByte($newbuf, $k, $buf, $k);
        }
        return $newbuf;
    }

    /**
    * Returns the current size of the buffer.
    *
    * @return the value of the <code>count</code> field, which is the number of valid bytes in this byte buffer.
    */
    public function size() {
        return $count;
    }

    /**
    * Converts the buffer's contents into a string, translating bytes into
    * characters according to the platform's default character encoding.
    *
    * @return String translated from the buffer's contents.
    */
    public function toString() {
        return itextphp_getAnsiString($buf, $count);
        //return new String(buf, 0, count);
    }

    /**
    * Converts the buffer's contents into a string, translating bytes into
    * characters according to the specified character encoding.
    *
    * @param   enc  a character-encoding name.
    * @return String RESOURCE!!! translated from the buffer's contents.
    * @throws UnsupportedEncodingException
    *         If the named encoding is not supported.
    */
    public function toString($enc) {
        return itextphp_byteToStringUnicodeEncoding($buf, $enc);
        //return new String(buf, 0, count, enc);
    }

    /**
    * Writes the complete contents of this byte buffer output to
    * the specified output stream argument, as if by calling the output
    * stream's write method using <code>out.write(buf, 0, count)</code>.
    *
    * @param      out   the output stream to which to write the data.
    * @exception  IOException  if an I/O error occurs.
    */
    public function writeTo(OutputStream out) {
        $out->write($buf, 0, $count);
    }

    public function write($b) {
        append_i($b);
    }

    public function write($b, $off, $len) {
        append($b, $off, $len);
    }

    public function getBuffer() {
        return $buf;
    }

}


?>