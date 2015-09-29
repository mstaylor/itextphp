<?PHP
/*
 * $Id: FilterOutputStream.php,v 1.1 2005/10/12 17:14:03 mstaylor Exp $
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


require_once("OutputStream.php");

class FilterOutputStream extends OutputStream
{

    /**
    * This is the subordinate <code>OutputStream</code> that this class
    * redirects its method calls to.
    */
    protected $out = NULL;


    /**
    * This method initializes an instance of <code>FilterOutputStream</code>
    * to write to the specified subordinate <code>OutputStream</code>.
    *
    * @param out The <code>OutputStream</code> to write to
    */
    public function __construct(OutputStream $out)
    {
        $this->out = $out;
    }

    /**
    * This method closes the underlying <code>OutputStream</code>.  Any
    * further attempts to write to this stream may throw an exception.
    *
    * @exception IOException If an error occurs
    */
    public function close() 
    {
        flush();
        $out->close();
    }

    /**
    * This method attempt to flush all buffered output to be written to the
    * underlying output sink.
    *
    * @exception IOException If an error occurs
    */
    public function flush() 
    {
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
                if (is_integer($arg1) == TRUE)
                    write1argInt($arg1);
                else if (is_resource($arg1) == TRUE)
                    write1argResource($arg1);
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
    * This method writes a single byte of output to the underlying
    * <code>OutputStream</code>.
    *
    * @param b The byte to write, passed as an int.
    *
    * @exception IOException If an error occurs
    */
    private function write1argInt($b) 
    {
        $out->write($b);
    }


    /**
    * This method writes all the bytes in the specified array to the underlying
    * <code>OutputStream</code>.  It does this by calling the three parameter
    * version of this method - <code>write(byte[], int, int)</code> in this
    * class instead of writing to the underlying <code>OutputStream</code>
    * directly.  This allows most subclasses to avoid overriding this method.
    *
    * @param buf The byte array to write bytes from
    *
    * @exception IOException If an error occurs
    */
    public function write1argResource($buf) 
    {
    // Don't do checking here, per Java Lang Spec.
        write($buf, 0, itextphp_bytes_getSize($buf));
    }

    /**
    * This method calls the <code>write(int)</code> method <code>len</code>
    * times for all bytes from the array <code>buf</code> starting at index
    * <code>offset</code>. Subclasses should overwrite this method to get a
    * more efficient implementation.
    *
    * @param buf The byte array to write bytes from
    * @param offset The index into the array to start writing bytes from
    * @param len The number of bytes to write
    *
    * @exception IOException If an error occurs
    */
    public function write3args($buf, $offset, $len)
    {
    // Don't do checking here, per Java Lang Spec.
    for ($i=0; $i < $len; $i++) 
        $write(itextphp_bytes_getIntValue($buf, $offset + $i));

    }



}

?>