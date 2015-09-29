<?PHP
/*
 * $Id: LZWDecoder.php,v 1.2 2005/10/12 21:23:12 mstaylor Exp $
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
require_once("../../exceptions/ArrayIndexOutOfBoundsException.php");

class LZWDecoder
{

    $stringTable = array();
    $data = NULL;
    $uncompData = NULL;
    $tableIndex = 0;
    $bitsToGet = 9;
    $bytePointer = 0;
    $bitPointer = 0;
    $nextData = 0;
    $nextBits = 0;

    $andTable = array(
        511,
        1023,
        2047,
        4095
    );

    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Method to decode LZW compressed data.
    *
    * @param data            The compressed data.
    * @param uncompData      Array to return the uncompressed data in.
    */
    public function decode($data, OutputStream $uncompData) {

        if(itextphp_bytes_getIntValue($data, 0) == 0x00 && itextphp_bytes_getIntValue($data, 1) == 0x01) {
            throw new Exception("LZW flavour not supported.");
        }

        initializeStringTable();

        $this->data = $data;
        $this->uncompData = $uncompData;

        // Initialize pointers
        $bytePointer = 0;
        $bitPointer = 0;

        $nextData = 0;
        $nextBits = 0;

        $code = 0;
        $oldCode = 0;
        $string = NULL;

        while (($code = getNextCode()) != 257) {

            if (code == 256) {

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
                    addStringToTable($stringTable[$oldCode], $string, 0);
                    $oldCode = $code;

                } else {

                    $string = $stringTable[$oldCode];
                    $string = composeString($string, $string, 0);
                    writeString($string);
                    addStringToTable($string);
                    $oldCode = $code;
                }
            }
        }
    }

    /**
    * Initialize the string table.
    */
    public function initializeStringTable() {

        $stringTable = array();

        for ($i=0; $i<256; $i++) {
            $stringTable[$i] = itextphp_bytes_create(1);
            $stringTable[$i][0] = itextphp_bytes_createfromInt($i);
        }

        $tableIndex = 258;
        $bitsToGet = 9;
    }

    /**
    * Write out the string just uncompressed.
    */
    public function writeString($string) {
        try {
            $uncompData->write($string);
        }
        catch (IOException $e) {
            throw new Exception($e);
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
                addStringToTable1arg($arg1);
                break;
            }
            case 2:
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                addStringToTagle3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    /**
    * Add a new string to the string table.
    */
    private function addStringToTable3args($oldString, $newString, $location) {
        $length = itextphp_bytes_getSize($oldString);
        $string = itextphp_bytes_create($length + 1);
        for ($k = 0; $k < $length; $k++)
            itextphp_bytes_write($string, $k, $oldString, $k);

        itextphp_bytes_write($string, $length, $newString, $location);

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
    public function composeString($oldString, $newString, $location) {
        $length = itextphp_bytes_getSize($oldString);
        $string = itextphp_bytes_create($length + 1);
        for ($k = 0; $k < $length; $k++)
            itextphp_bytes_write($string, $k, $oldString, $k);

        itextphp_bytes_write($string, $length, $newString, $location);

        return $string;
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