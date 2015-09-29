<?PHP
/*
 * $Id: RandomAccessFileOrArray.php,v 1.3 2005/10/11 16:15:54 mstaylor Exp $
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

require_once("../../io/DataInput.php");
require_once("../../exceptions/IOException.php");
require_once("../../exceptions/EOFException.php");
require_once("../../exceptions/UTFDataFormatException.php");
require_once("../../util/StringHelpers.php");
require_once("BaseFont.php");


class RandomAccessFileOrArray implements DataInput
{

    $rf = NULL;
    $filename = NULL;
    $arrayIn = NULL;
    $arrayInPtr = 0;
    $back = NULL;
    $isBack = FALSE;

    /** Holds value of property startOffset. */
    private $startOffset = 0;


    public function __construct()
    {

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_string($arg1) == TRUE)
                    construct1argstring($arg1);
                else if (is_resource($arg1) == TRUE)
                    construct1argstream($arg1);
                else if ($arg1 instanceof RandomAccessFileOrArray)
                    construct1argRandomAccessFileOrArray($aarg1);
                break;
            }
        }
    }

    private function construct1argstring($filename)
    {
        //$file = new File(filename);
        if (is_readable($filename) == FALSE) {
            if (beginsWith($filename, "file:/") == TRUE || beginsWith($filename, "http://") == TRUE || beginsWith($filename, "https://") == TRUE) {
                $buffer = "";
                $is = fopen($filename, "r+");
                //try {
                if ($is)
                {
 
                while (!feof($is)) {
                    $buffer .= fgets($is, 4096);

                }
                }
                $this->arrayIn = itextphp_bytes_createfromRaw($buffer);
                    return;
                //}
               // finally {
                 //   try {is.close();}catch(IOException ioe){}
                //}
            }
            else {
                $buffer = "";
                $is = BaseFont::getResourceStream($filename);
                if ($is == null)
                    throw new IOException($filename . " not found as file or resource.");
                if ($is)
                {
 
                    while (!feof($is)) {
                        $buffer .= fgets($is, 4096);

                    }
                }
                $this->arrayIn = itextphp_bytes_createfromRaw($buffer);
                return;

            }
        }

        $this->filename = $filename;
        $rf = fopen($filename, "r");


    }


    private function construct1argstream($is)
    {
        $buffer = "";
        if ($is)
        {
            while (!feof($is)) {
                $buffer .= fgets($is, 4096);
            }
        }
        $this->arrayIn = itextphp_bytes_createfromRaw($buffer);
    }

    private function construct1argRandomAccessFileOrArray($file)
    {
        $filename = $file->filename;
        $arrayIn = $file->arrayIn;
        $startOffset = $file->startOffset;
    }

    public function pushBack($b) {
        $back = $b;
        $isBack = TRUE;
    }

    public function read()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                return read0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                return read1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                return read3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }


    private function read0args()
    {
        if($isBack == TRUE) {
            $isBack = FALSE;
            return $back & 0xff;
        }
        if ($arrayIn == NULL)
            return ord(fgetc($rf,1));
        else {
            if ($arrayInPtr >= itextphp_bytes_getSize($arrayIn))
                return -1;
            return itextphp_bytes_getIntValue($arrayIn, $arrayInPtr++) & 0xff;
        }
    }

    private function read1arg($b)
    {
        return read3args($b, 0, itextphp_bytes_getSize($b));
    }

    private function read3args($b, $off, $len) {
        if ($len == 0)
            return 0;
        $n = 0;
        if ($isBack == TRUE) {
            $isBack = FALSE;
            if ($len == 1) {
                itextphp_bytes_write($b, $off, itextphp_bytes_createfromInt($back), 0);
                return 1;
            }
            else {
                $n = 1;
                itextphp_bytes_write($b, $off++,  itextphp_bytes_createfromInt($back), 0);
                --$len;
            }
        }
        if ($arrayIn == NULL) {
            fseek($rf, $off);
            $position = 0;
            for ($k = 0; $k < $len; $k++)
            {
               $position ++;
               itextphp_bytes_write($b, $k, itextphp_bytes_createfromRaw(fgetc($rf), 0)
            }
            return $position + $n;
        }
        else {
            if ($arrayInPtr >= itextphp_bytes_getSize($arrayIn))
                return -1;
            if ($arrayInPtr + $len > itextphp_bytes_getSize($arrayIn))
                $len = itextphp_bytes_getSize($arrayIn) - $arrayInPtr;

            for ($k = 0; $k < $len; $k++)
            {
                itextphp_bytes_write($b, $off, $arrayIn, $arrayInPtr);
                $off++;
                $arrayInPtr++;
            }

            $arrayInPtr += $len;
            return $len + $n;
        }
    }

    public function readFully()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {

            case 1:
            {
                $arg1 = func_get_arg(0);
                readFully1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                readFully3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    private function readFully1arg($b)
    {
        readFully3args($b, 0, itextphp_bytes_getSize($b));
    }

    private function readFully3args($b, $off, $len) 
    {
        $n = 0;
        do {
            $count = read($b, $off + $n, $len - $n);
            if ($count < 0)
                throw new EOFException();
            $n += $count;
        } while ($n < $len);
    }

    public function skip($n) 
    {
        return skipBytes($n);
    }

    public function skipBytes($n) 
    {
        if ($n <= 0) {
            return 0;
        }
        $adj = 0;
        if ($isBack == TRUE) {
            $isBack = FALSE;
            if ($n == 1) {
                return 1;
            }
            else {
                --$n;
                $adj = 1;
            }
        }
        $pos = 0;
        $len = 0;
        $newpos = 0;

        $pos = getFilePointer();
        $len = length();
        $newpos = $pos + $n;
        if ($newpos > $len) {
            $newpos = $len;
        }
        seek($newpos);

        /* return the actual number of bytes skipped */
        return $newpos - $pos + $adj;
    }

    public function reOpen() 
    {
        $isBack = FALSE;
        if ($filename != NULL) {
            close();
            $rf = fopen($filename, "r");
            if ($startOffset != 0)
                fseek($rf, $startOffset);
        }
        else {
            $arrayInPtr = $startOffset;
        }
    }

    protected function insureOpen() 
    {
        if ($filename != NULL && $rf == NULL) {
            reOpen();
        }
    }

    public function isOpen() {
        return ($filename == NULL || $rf != NULL);
    }

    public function close() 
    {
        $isBack = FALSE;
        if ($rf != NULL) {
            fclose($rf);
            $rf = NULL;
        }
    }

    public function length()
    {
        if ($arrayIn == NULL)
        {
            $theSize = 0;
            if ($rf) {
            while (!feof($rf)) {
                fgets($rf, 1);
                $theSize++;
            }
            fseek($rf, 0);
            }
            return $theSize - $startOffset;
        }
        else
            return itextphp_bytes_getSize($arrayIn) - $startOffset;
    }

    public function seek($pos)
    {
        $pos += $startOffset;
        $isBack = FALSE;
        if ($arrayIn == NULL) {
            insureOpen();
            fseek($rf, $pos);
        }
        else
            $arrayInPtr = $pos;
    }

    public function getFilePointer() 
    {
        $n = $isBack ? 1 : 0;
        if ($arrayIn == NULL)
            return ftell($rf) - $n - $startOffset;
        else
            return $arrayInPtr - $n - $startOffset;
    }

     public function readBoolean() 
     {
        $ch = $this->read();
        if ($ch < 0)
            throw new EOFException();
        return ($ch != 0);
    }

    public function readByte()  {
        $ch = $this->read();
        if ($ch < 0)
            throw new EOFException();
        return ($ch);
    }

    public function readUnsignedByte() 
    {
        $ch = $this->read();
        if ($ch < 0)
            throw new EOFException();
        return $ch;
    }

    public function readShort() 
    {
        $ch1 = $this->read();
        $ch2 = $this->read();
        if (($ch1 | $ch2) < 0)
            throw new EOFException();
        return (($ch1 << 8) + $ch2);
    }

    /**
    * Reads a signed 16-bit number from this stream in little-endian order.
    * The method reads two
    * bytes from this stream, starting at the current stream pointer.
    * If the two bytes read, in order, are
    * <code>b1</code> and <code>b2</code>, where each of the two values is
    * between <code>0</code> and <code>255</code>, inclusive, then the
    * result is equal to:
    * <blockquote><pre>
    *     (short)((b2 &lt;&lt; 8) | b1)
    * </pre></blockquote>
    * <p>
    * This method blocks until the two bytes are read, the end of the
    * stream is detected, or an exception is thrown.
    *
    * @return     the next two bytes of this stream, interpreted as a signed
    *             16-bit number.
    * @exception  EOFException  if this stream reaches the end before reading
    *               two bytes.
    * @exception  IOException   if an I/O error occurs.
    */
    public final function readShortLE() {
        $ch1 = $this->read();
        $ch2 = $this->read();
        if (($ch1 | $ch2) < 0)
            throw new EOFException();
        return (($ch2 << 8) + ($ch1 << 0));
    }

    public function readUnsignedShort() 
    {
        $ch1 = $this->read();
        $ch2 = $this->read();
        if (($ch1 | $ch2) < 0)
            throw new EOFException();
        return ($ch1 << 8) + $ch2;
    }

    /**
    * Reads an unsigned 16-bit number from this stream in little-endian order.
    * This method reads
    * two bytes from the stream, starting at the current stream pointer.
    * If the bytes read, in order, are
    * <code>b1</code> and <code>b2</code>, where
    * <code>0&nbsp;&lt;=&nbsp;b1, b2&nbsp;&lt;=&nbsp;255</code>,
    * then the result is equal to:
    * <blockquote><pre>
    *     (b2 &lt;&lt; 8) | b1
    * </pre></blockquote>
    * <p>
    * This method blocks until the two bytes are read, the end of the
    * stream is detected, or an exception is thrown.
    *
    * @return     the next two bytes of this stream, interpreted as an
    *             unsigned 16-bit integer.
    * @exception  EOFException  if this stream reaches the end before reading
    *               two bytes.
    * @exception  IOException   if an I/O error occurs.
    */
    public final function readUnsignedShortLE() 
    {
        $ch1 = $this->read();
        $ch2 = $this->read();
        if (($ch1 | $ch2) < 0)
            throw new EOFException();
        return ($ch2 << 8) + ($ch1 << 0);
    }

     public function readChar()
     {
        $ch1 = this.read();
        $ch2 = this.read();
        if (($ch1 | $ch2) < 0)
            throw new EOFException();
        return chr((($ch1 << 8) + $ch2));
    }

    /**
    * Reads a Unicode character from this stream in little-endian order.
    * This method reads two
    * bytes from the stream, starting at the current stream pointer.
    * If the bytes read, in order, are
    * <code>b1</code> and <code>b2</code>, where
    * <code>0&nbsp;&lt;=&nbsp;b1,&nbsp;b2&nbsp;&lt;=&nbsp;255</code>,
    * then the result is equal to:
    * <blockquote><pre>
    *     (char)((b2 &lt;&lt; 8) | b1)
    * </pre></blockquote>
    * <p>
    * This method blocks until the two bytes are read, the end of the
    * stream is detected, or an exception is thrown.
    *
    * @return     the next two bytes of this stream as a Unicode character.
    * @exception  EOFException  if this stream reaches the end before reading
    *               two bytes.
    * @exception  IOException   if an I/O error occurs.
    */
    public final function readCharLE() {
        $ch1 = $this->read();
        $ch2 = $this->read();
        if (($ch1 | $ch2) < 0)
            throw new EOFException();
        return itextphp_newString((($ch2 << 8) + ($ch1 << 0)),2,1);
    }

     public function readInt() {
        $ch1 = $this->read();
        $ch2 = $this->read();
        $ch3 = $this->read();
        $ch4 = $this->read();
        if (($ch1 | $ch2 | $ch3 | $ch4) < 0)
            throw new EOFException();
        return (($ch1 << 24) + ($ch2 << 16) + ($ch3 << 8) + $ch4);
    }

    /**
    * Reads a signed 32-bit integer from this stream in little-endian order.
    * This method reads 4
    * bytes from the stream, starting at the current stream pointer.
    * If the bytes read, in order, are <code>b1</code>,
    * <code>b2</code>, <code>b3</code>, and <code>b4</code>, where
    * <code>0&nbsp;&lt;=&nbsp;b1, b2, b3, b4&nbsp;&lt;=&nbsp;255</code>,
    * then the result is equal to:
    * <blockquote><pre>
    *     (b4 &lt;&lt; 24) | (b3 &lt;&lt; 16) + (b2 &lt;&lt; 8) + b1
    * </pre></blockquote>
    * <p>
    * This method blocks until the four bytes are read, the end of the
    * stream is detected, or an exception is thrown.
    *
    * @return     the next four bytes of this stream, interpreted as an
    *             <code>int</code>.
    * @exception  EOFException  if this stream reaches the end before reading
    *               four bytes.
    * @exception  IOException   if an I/O error occurs.
    */
    public final function readIntLE() 
    {
        $ch1 = $this->read();
        $ch2 = $this->read();
        $ch3 = $this->read();
        $ch4 = $this->read();
        if (($ch1 | $ch2 | $ch3 | $ch4) < 0)
            throw new EOFException();
        return (($ch4 << 24) + ($ch3 << 16) + ($ch2 << 8) + ($ch1 << 0));
    }

    /**
    * Reads an unsigned 32-bit integer from this stream. This method reads 4
    * bytes from the stream, starting at the current stream pointer.
    * If the bytes read, in order, are <code>b1</code>,
    * <code>b2</code>, <code>b3</code>, and <code>b4</code>, where
    * <code>0&nbsp;&lt;=&nbsp;b1, b2, b3, b4&nbsp;&lt;=&nbsp;255</code>,
    * then the result is equal to:
    * <blockquote><pre>
    *     (b1 &lt;&lt; 24) | (b2 &lt;&lt; 16) + (b3 &lt;&lt; 8) + b4
    * </pre></blockquote>
    * <p>
    * This method blocks until the four bytes are read, the end of the
    * stream is detected, or an exception is thrown.
    *
    * @return     the next four bytes of this stream, interpreted as a
    *             <code>long</code>.
    * @exception  EOFException  if this stream reaches the end before reading
    *               four bytes.
    * @exception  IOException   if an I/O error occurs.
    */
    public final function readUnsignedInt() 
    {
        $ch1 = $this->read();
        $ch2 = $this->read();
        $ch3 = $this->read();
        $ch4 = $this->read();
        if (($ch1 | $ch2 | $ch3 | $ch4) < 0)
            throw new EOFException();
        return (($ch1 << 24) + ($ch2 << 16) + ($ch3 << 8) + ($ch4 << 0));
    }

    public final function readUnsignedIntLE()
    {
        $ch1 = $this->read();
        $ch2 = $this->read();
        $ch3 = $this->read();
        $ch4 = $this->read();
        if (($ch1 | $ch2 | $ch3 | $ch4) < 0)
            throw new EOFException();
        return (($ch4 << 24) + ($ch3 << 16) + ($ch2 << 8) + ($ch1 << 0));
    }

    public function readLong() 
    {
        return ((readInt()) << 32) + (readInt() & 0xFFFFFFFFL);
    }

    public final long readLongLE() throws IOException {
        $i1 = readIntLE();
        $i2 = readIntLE();
        return ($i2 << 32) + ($i1 & 0xFFFFFFFFL);
    }

    public function readFloat()
    {
        return (float)readInt();
    }

    public final function readFloatLE() 
    {
        return (float)readIntLE();
    }

    public function readDouble()
    {
        return (double)readLong();
    }

    public final function readDoubleLE() 
    {
        return (double)readLongLE();
    }

    public function readLine()
    {
        $input = "";
        $c = -1;
        $eol = FALSE;

        while ($eol == FALSE) {
            switch ($c = read()) {
                case -1:
                case '\n':
                    $eol = TRUE;
                    break;
                case '\r':
                    $eol = TRUE;
                    $cur = getFilePointer();
                    if ((read()) != '\n') {
                        seek($cur);
                    }
                    break;
                default:
                    $input .= chr($c);
                    break;
            }
        }

        if (($c == -1) && (strlen($input) == 0)) {
            return NULL;
        }
        return $input;
    }

    public function readUTF() 
    {
        $UTFlen = readUnsignedShort();
        $buf = itextphp_bytes_create($UTFlen);

        // This blocks until the entire string is available rather than
        // doing partial processing on the bytes that are available and then
        // blocking.  An advantage of the latter is that Exceptions
        // could be thrown earlier.  The former is a bit cleaner.
        readFully ($buf, 0, $UTFlen);

        return RandomAccessFileOrArray::convertFromUTF ($buf);
    }


    // FIXME: This method should be re-thought.  I suspect we have multiple
    // UTF-8 decoders floating around.  We should use the standard charset
    // converters, maybe and adding a direct call into one of the new
    // NIO converters for a super-fast UTF8 decode.
    static function convertFromUTF ($buf) 
    {
        // Give StringBuffer an initial estimated size to avoid 
        // enlarge buffer frequently
        $strbuf = itextphp_newString("",itextphp_bytes_getSize($buf)/2 + 2, 2);

        for ($i = 0; $i < itextphp_bytes_getSize($buf); )
        {
        if (itextphp_bytes_equalsoperator($buf, $i, 0x80) == TRUE)  // bit pattern 0xxxxxxx
            itextphp_string_append($strbuf,  itextphp_bytes_getIntValue($buf, $i++) & 0xFF);
        else if (itextphp_bytes_equalsoperator($buf, $i, 0xE0, 0xC0) == TRUE) // bit pattern 110xxxxx
        {
            if ($i + 1 >= itextphp_bytes_getSize($buf) || itextphp_bytes_notequaloperator($buf , $i + 1, 0xC0, 0x80) == TRUE)
                throw new UTFDataFormatException ();

            itextphp_string_append($strbuf ,(((itextphp_bytes_getIntValue($buf, $i++) & 0x1F) << 6) | (itextphp_bytes_getIntValue($buf, $i++) & 0x3F)));
        }
        else if (itextphp_bytes_equalsoperator($buf, $i, 0xF0, 0xE0) == TRUE)  // bit pattern 1110xxxx
        {
            if ($i + 2 >= itextphp_bytes_getSize($buf) || itextphp_bytes_notequaloperator($buf, $i + 1, 0xC0, 0x80) == TRUE
            || itextphp_bytes_notequaloperator($buf, $i + 2, 0xC0, 0x80) == TRUE)
                throw new UTFDataFormatException ();

            itextphp_string_append(strbuf,  (((itextphp_bytes_getIntValue($buf, $i++) & 0x0F) << 12)
            | ((itextphp_bytes_getIntValue($buf, $i++) & 0x3F) << 6)
            | (itextphp_bytes_getIntValue($buf, $i++) & 0x3F)));
        }
        else // must be ((buf [i] & 0xF0) == 0xF0 || (buf [i] & 0xC0) == 0x80)
            throw new UTFDataFormatException ();    // bit patterns 1111xxxx or
                                                   // 10xxxxxx
        }

        return $strbuf;
    }

    /** Getter for property startOffset.
    * @return Value of property startOffset.
    *
    */
    public function getStartOffset() {
        return $this->startOffset;
    }

    /** Setter for property startOffset.
    * @param startOffset New value of property startOffset.
    *
    */
    public function setStartOffset($startOffset) {
        $this->startOffset = $startOffset;
    }
}
?>