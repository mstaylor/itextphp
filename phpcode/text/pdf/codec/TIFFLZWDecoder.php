<?PHP
/*
 * $Id: TIFFLZWDecoder.php,v 1.1 2005/11/14 21:14:39 mstaylor Exp $
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


require_once("../../../UnsupportedOperationException.php");
require_once("../../../ArrayIndexOutOfBoundsException.php");

/**
* A class for performing LZW decoding.
*
*
*/

class TIFFLZWDecoder
{

    protected $stringTable = NULL;//array of byte array
    protected $data = NULL;//byte array
    protected $uncompData = NULL;//byte array
    protected $tableIndex = 0;
    protected $bitsToGet = 9;
    protected $bytePointer = 0;
    protected $bitPointer = 0;
    protected $dstIndex = 0;
    protected $w = 0;
    protected $h = 0;
    protected $predictor = 0;
    protected $samplesPerPixel = 0;
    protected $nextData = 0;
    protected $nextBits = 0;

    protected $andTable = array(
        511,
        1023,
        2047,
        4095
    );

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                construct3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }


    private function construct3args($w, $predictor, $samplesPerPixel) {
        $this->w = $w;
        $this->predictor = $predictor;
        $this->samplesPerPixel = $samplesPerPixel;
    }


    /**
    * Method to decode LZW compressed data.
    *
    * @param data            The compressed data.
    * @param uncompData      Array to return the uncompressed data in.
    * @param h               The number of rows the compressed data contains.
    */
    public function decode($data, $uncompData, $h) {

        if(itextphp_bytes_getIntValue($data, 0) == 0x00 && itextphp_bytes_getIntValue($data, 1) == 0x01) {
            throw new UnsupportedOperationException("TIFF 5.0-style LZW codes are not supported.");
        }

        initializeStringTable();

        $this->data = $data;
        $this->h = $h;
        $this->uncompData = $uncompData;

        // Initialize pointers
        $bytePointer = 0;
        $bitPointer = 0;
        $dstIndex = 0;


        $nextData = 0;
        $nextBits = 0;

        $code = 0;
        $oldCode = 0;
        $string = NULL;//byte array

        while ( (($code = getNextCode()) != 257) &&
        $dstIndex < itextphp_bytes_getSize($uncompData)) {

            if ($code == 256) {

                initializeStringTable();
                $code = getNextCode();

                if (code == 257) {
                    break;
                }

                writeString($stringTable[$code]);
                $oldCode = $code;

            } else {

                if ($code < $tableIndex) {

                    $string = $stringTable[$code];

                    writeString($string);
                    addStringToTable($stringTable[$oldCode], itextphp_bytes_getIntValue($string, 0));
                    $oldCode = $code;

                } else {

                    $string = $stringTable[$oldCode];
                    $string = composeString($string, itextphp_bytes_getIntValue($string, 0));
                    writeString($string);
                    addStringToTable($string);
                    $oldCode = $code;
                }

            }

        }

        // Horizontal Differencing Predictor
        if ($predictor == 2) {

            $count = 0;
            for ($j = 0; $j < $h; $j++) {

                $count = $samplesPerPixel * ($j * $w + 1);

                for ($i = $samplesPerPixel; $i < $w * $samplesPerPixel; $i++) {

                    itextphp_updateByteWithByte($uncompData, $count, $uncompData, $count - $samplesPerPixel);
                    $count++;
                }
            }
        }

        return $uncompData;
    }

    /**
    * Initialize the string table.
    */
    public function initializeStringTable() {

        $stringTable = array();

        for ($i=0; $i<256; $i++) {
            $stringTable[$i] = itextphp_bytes_create(1);
            $stringTable[$i][0] = $i;
        }

        $tableIndex = 258;
        $bitsToGet = 9;
    }

    /**
    * Write out the string just uncompressed.
    */
    public function writeString($string) {

        for ($i=0; $i<itextphp_bytes_getSize($string); $i++) {
            itextphp_bytes_write($uncompData, $dstIndex++, $string, $i);
        }
    }


    public function addStringToTable()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_resource($arg1)  && strcmp(get_resource_type($arg1), "anotherByte") == 0)
                    addStringToTable1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_resource($arg1)  && strcmp(get_resource_type($arg1), "anotherByte") == 0 && && is_integer($arg2) == TRUE)
                    addStringToTable2args($arg1, $arg2);
                break;
            }
        }
    }

    /**
     * Add a new string to the string table.
     */
    private function addStringToTable2args($oldString, $newString) {
        $length = itextphp_bytes_getSize($oldString);
        $string = itextphp_bytes_create($length + 1);
        for ($k = 0; $k < $length; $k++)
        {
            itextphp_bytes_write($string, $k, $oldString, $k);
        }
        itextphp_bytes_write($string, $length, itextphp_bytes_createfromInt($newString), 0);

        // Add this new String to the table
        $stringTable[$tableIndex++] = $string;

        if ($tableIndex == 511) {
            $bitsToGet = 10;
        } else if ($tableIndex == 1023) {
            $bitsToGet = 11;
        } else if ($tableIndex == 2047) {
            $bitsToGet = 12;
        }
    }


    /**
    * Add a new string to the string table.
    */
    private function addStringToTable1arg($string) {

        // Add this new String to the table
        $stringTable[$tableIndex++] = $string;

        if ($tableIndex == 511) {
            $bitsToGet = 10;
        } else if ($tableIndex == 1023) {
            $bitsToGet = 11;
        } else if ($tableIndex == 2047) {
            $bitsToGet = 12;
        }
    }

    /**
    * Append <code>newString</code> to the end of <code>oldString</code>.
    */
    public function composeString($oldString, $newString) {
        $length = itextphp_bytes_getSize($oldString);
        $string = itextphp_bytes_create($length + 1);
        //System.arraycopy(oldString, 0, string, 0, length);
        for ($k = 0; $k < $length; $k++)
        {
            itextphp_bytes_write($string, $k, $oldString, $k);
        }
        itextphp_bytes_write($string, $length, itextphp_bytes_createfromInt($newString), 0);
        return string;
    }

    // Returns the next 9, 10, 11 or 12 bits
    public function getNextCode() {
        // Attempt to get the next code. The exception is caught to make
        // this robust to cases wherein the EndOfInformation code has been
        // omitted from a strip. Examples of such cases have been observed
        // in practice.
        try {
            $nextData = ($nextData << 8) | (itextphp_bytes_getIntValue($data, $bytePointer++) & 0xff);
            $nextBits += 8;
            
            if ($nextBits < $bitsToGet) {
                $nextData = ($nextData << 8) | (itextphp_bytes_getIntValue($data, $bytePointer++) & 0xff);
                $nextBits += 8;
            }

            $code =
            ($nextData >> ($nextBits - $bitsToGet)) & $andTable[$bitsToGet-9];
            $nextBits -= $bitsToGet;

            return $code;
        } catch(ArrayIndexOutOfBoundsException $e) {
            // Strip not terminated as expected: return EndOfInformation code.
            return 257;
        }
    }




}



?>