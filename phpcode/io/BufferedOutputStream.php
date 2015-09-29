<?PHP
/*
 * $Id: BufferedOutputStream.php,v 1.1 2005/10/12 17:14:03 mstaylor Exp $
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

require_once("FilterOutputStream.php");


class BufferedOutputStream extends FilterOutputStream
{

    /**
    * This is the default buffer size
    */
    private static $DEFAULT_BUFFER_SIZE = 512;

    /**
    * This is the internal byte array used for buffering output before
    * writing it.
    */
    protected $buf = NULL;

   /**
   * This is the number of bytes that are currently in the buffer and
   * are waiting to be written to the underlying stream.  It always points to
   * the index into the buffer where the next byte of data will be stored
   */
   protected $count = 0;


    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                construct1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                construct2args($arg1, $arg2);
                break;
            }
        }
    }

    /**
    * This method initializes a new <code>BufferedOutputStream</code> instance
    * that will write to the specified subordinate <code>OutputStream</code>
    * and which will use a default buffer size of 512 bytes.
    *
    * @param out The underlying <code>OutputStream</code> to write data to
    */
    private function construct1arg(OutputStream out)
    {
        construct2args($out, BufferedOutputStream::$DEFAULT_BUFFER_SIZE);
    }

    /**
    * This method initializes a new <code>BufferedOutputStream</code> instance
    * that will write to the specified subordinate <code>OutputStream</code>
    * and which will use the specified buffer size
    *
    * @param out The underlying <code>OutputStream</code> to write data to
    * @param size The size of the internal buffer
    */
    private function construct2args(OutputStream out, int size)
    {
        parent::__construct($out);

        $buf = itextphp_bytes_create($size);
    }


    /**
    * This method causes any currently buffered bytes to be immediately
    * written to the underlying output stream.
    *
    * @exception IOException If an error occurs
    */
    public function flush() 
    {
        if ($count == 0)
            return;

        $out->write($buf, 0, $count);
        $count = 0;
        $out->flush();
    }


    public function write()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                write1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                write3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    /**
    * This method writes <code>len</code> bytes from the byte array 
    * <code>buf</code> starting at position <code>offset</code> in the buffer. 
    * These bytes will be written to the internal buffer.  However, if this
    * write operation fills the buffer, the buffer will be flushed to the
    * underlying output stream.
    *
    * @param buf The array of bytes to write.
    * @param offset The index into the byte array to start writing from.
    * @param len The number of bytes to write.
    *
    * @exception IOException If an error occurs
    */
    private function write1arg($b) 
    {
        if ($count == itextphp_bytes_getSize($buf))
            flush();

        itextphp_bytes_write($buf, $count, itextphp_bytes_createfromInt(b & 0xFF), 0);
        ++$count;
    }

    private function write3args($buf, $offset, $len) 
    {
        // Buffer can hold everything.  Note that the case where LEN < 0
        // is automatically handled by the downstream write.
        if ($len < (itextphp_bytes_getSize($this->buf) - $count))
        {
            $newCount = $count;
            for ($k = 0; $k < $len;$k++)
            {
                itextphp_bytes_write($this->buf, $newCount, $buf, $offset);
                $newCount++;
                $offset++;
            }
            $count += $len;
        }
        else
        {
            // The write was too big.  So flush the buffer and write the new
            // bytes directly to the underlying stream, per the JDK 1.2
            // docs.
            flush();
            $out->write ($buf, $offset, $len);
        }
  }


}

?>