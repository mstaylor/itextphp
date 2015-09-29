<?PHP
/*
 * $Id: InputMeta.php,v 1.1 2005/11/07 21:31:16 mstaylor Exp $
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

require_once("../../../awt/Color.php");




class InputMeta
{

    $in = NULL;//a byte array
    $length = 0;

    public function __construct($in) {
        if (is_resource($in) == TRUE)
            $this->in = $in;
    }

    public function readWord() {
        $length += 2;
        $k1 = ord(itextphp_bytes_read($in));
        if ($k1 < 0)
            return 0;
        return ($k1 + (ord(itextphp_bytes_read($in)) << 8)) & 0xffff;
    }

    public function readShort() {
        $k = readWord();
        if ($k > 0x7fff)
            $k -= 0x10000;
        return $k;
    }

    public function readInt() {
        $length += 4;
        $k1 = ord(itextphp_bytes_read($in));
        if ($k1 < 0)
            return 0;
        $k2 = ord(itextphp_bytes_read($in)) << 8;
        $k3 = ord(itextphp_bytes_read($in)) << 16;
        return $k1 + $k2 + $k3 + (ord(itextphp_bytes_read($in)) << 24);
    }

    public function readByte() {
        ++$length;
        return ord(itextphp_bytes_read($in)) & 0xff;
    }

    public function skip($len) {
        $length += $len;
        while ($len > 0) {
            $len -= itextphp_bytes_skip($in, $len);
        }
    }

    public function getLength() {
        return $length;
    }

    public function readColor() {
        $red = readByte();
        $green = readByte();
        $blue = readByte();
        readByte();
        return new Color($red, $green, $blue);
    }



}

?>