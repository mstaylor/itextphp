<?PHP
/*
 * $Id: InputStream.php,v 1.1 2005/10/18 16:23:04 mstaylor Exp $
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

require_once("../exceptions/IndexOutOfBoundsException.php");
require_once("../exceptions/IOException.php");



abstract class InputStream
{
    public function __construct()
    {
    }

    public function available()
    {
        return 0;
    }

    public function close()
    {
        // Do nothing
    }

    public function mark($readLimit)
    {
        // Do nothing
    }

    public function markSupported()
    {
        return FALSE;
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
                if (is_resource($arg1) == TRUE)
                    return read1argresource($arg1);
                break;
            }
        }
    }

    abstract private function read0args()

    private function read1argresource($b)
    {
    return read3args($b, 0, itextphp_bytes_getSize($b));
    }

    private function read3args($b, $off, $len) 
    {
        if ($off < 0 || $len < 0 || $off + $len > itextphp_bytes_getSize($b))
            throw new IndexOutOfBoundsException();
        if (itextphp_bytes_getSize($b) == 0)
            return 0;

        $i = 0;
        $ch = 0;

        for ($i = 0; $i < $len; ++$i)
            try
            {
                if (($ch = read()) < 0)
                    return $i == 0 ? -1 : $i;  // EOF
                $tmpByte = itextphp_bytes_createfromInt($ch);
                itextphp_bytes_write($b. $off + $i, $tmpByte, 0);
            }
            catch (IOException $ex)
            {
                // Only reading the first byte should cause an IOException.
                if ($i == 0)
                    throw $ex;
                return $i;
            }

            return $i;
    }

    public function reset()
    {
        throw new IOException("mark/reset not supported");
    }


    public function skip($n)
    {
        // Throw away n bytes by reading them into a temp byte[].
        // Limit the temp array to 2Kb so we don't grab too much memory.
        $buflen = $n > 2048 ? 2048 : $n;
        $tmpbuf = itextphp_bytes_create($buflen);
        $origN = $n;

        while ($n > 0)
        {
            $numread = read($tmpbuf, 0, $n > $buflen ? $buflen : $n);
            if ($numread <= 0)
            break;
            $n -= $numread;
        }

        return $origN - $n;
    }





}


?>