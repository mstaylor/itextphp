<?PHP
/*
 * $Id: PdfEncryption.php,v 1.3 2005/10/10 15:57:06 mstaylor Exp $
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

/**
*
* @author  Paulo Soares (psoares@consiste.pt)
* @author Kazuya Ujihara
*/

require_once("PdfObject.php");
require_once("ByteBuffer.php");
require_once("PdfLiteral.php");
require_once("PdfDictionary.php");
require_once("PdfName.php");
require_once("PdfContentByte.php");
require_once("PdfNumber.php");

class PdfEncryption 
{

    static $byte = NULL;
    $state = NULL;
    $x = 0;
    $y = 0;
    /** The encryption key for a particular object/generation */
    $key = NULL;
    /** The encryption key length for a particular object/generation */
    $keySize = 0;
    /** The global encryption key */
    $mkey = NULL;

    /** Work area to prepare the object/generation bytes */
    $extra = NULL;

    /** The encryption key for the owner */
    $ownerKey] = NULL;
    /** The encryption key for the user */
    $userKey = NULL;;
    $permissions = 0;
    $documentID = NULL;
    $md5 = NULL;
    static seq = NULL;


    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfEncryption::$initialized == FALSE)
        {
            PdfEncryption::$byte = array(itextphp_bytes_createfromInt(0x28),
            itextphp_bytes_createfromInt(0xBF), itextphp_bytes_createfromInt(0x4E),
            itextphp_bytes_createfromInt(0x5E), itextphp_bytes_createfromInt(0x4E),
            itextphp_bytes_createfromInt(0x75), itextphp_bytes_createfromInt(0x8A),
            itextphp_bytes_createfromInt(0x41), itextphp_bytes_createfromInt(0x64),
            itextphp_bytes_createfromInt(0x00), itextphp_bytes_createfromInt(0x4E),
            itextphp_bytes_createfromInt(0x56), itextphp_bytes_createfromInt(0xFF),
            itextphp_bytes_createfromInt(0xFA), itextphp_bytes_createfromInt(0x01),
            itextphp_bytes_createfromInt(0x08), itextphp_bytes_createfromInt(0x2E),
            itextphp_bytes_createfromInt(0x2E), itextphp_bytes_createfromInt(0x00),
            itextphp_bytes_createfromInt(0xB6), itextphp_bytes_createfromInt(0xD0),
            itextphp_bytes_createfromInt(0x68), itextphp_bytes_createfromInt(0x3E),
            itextphp_bytes_createfromInt(0x80), itextphp_bytes_createfromInt(0x2F),
            itextphp_bytes_createfromInt(0x0C), itextphp_bytes_createfromInt(0xA9),
            itextphp_bytes_createfromInt(0xFE), itextphp_bytes_createfromInt(0x64),
            itextphp_bytes_createfromInt(0x53), itextphp_bytes_createfromInt(0x69),
            itextphp_bytes_createfromInt(0x7A));
            PdfEncryption::$seq = microtime(TRUE);
            PdfEncryption::$initialized = TRUE;
        }
    }

    private function onConstruct()
    {
        $state = itextphp_bytes_create(256);
        $extra = itextphp_bytes_create(5);
        $ownerKey = itextphp_bytes_create(32);
        $userKey = itextphp_bytes_create(32);
        $md5 = itextphp_md5INIT();
    }

    public function __construct
    {
       onConstruct();
    }

    /**
    */
    private function padPassword($userPassword) {
        $userPad = itextphp_bytes_create(32);
        if ($userPassword == NULL) {
            for ($k = 0; $k < 32; $k++)
                itextphp_bytes_write($userPad, $k, $pad, $k);
        }
        else {
               for ($k = 0; $k < min(itextphp_bytes_getSize($userPassword), 32); $k++)
                   itextphp_bytes_write($userPad, $k, $userPassword, $k);

             if (itextphp_bytes_getSize($userPassword) < 32)
             {
                $actplace = itextphp_bytes_getSize($userPassword);
                for ($k = 0; $k < 32 - itextphp_bytes_getSize($userPassword); $k++)
                {
                    itextphp_bytes_write($userPad, $actplace, $pad, $k);
                    $actplace++;
                }
             }
        }

        return $userPad;
    }

    /**
    */
    private function computeOwnerKey($userPad, $ownerPad, $strength128Bits) 
    {
        $ownerKey = itextphp_bytes_create(32);

        $digest = itextphp_md5DIGEST($md5, $ownerPad);
        if ($strength128Bits == TRUE) {
            $mkey = itextphp_bytes_create(16);
            for ($k = 0; $k < 50; ++$k)
                $digest = itextphp_md5DIGEST($md5, $digest);
            for ($k = 0; $k < 32; $k++)
            {
                itextphp_bytes_write($ownerKey, $k, $userPad, $k);
            }
            for ($i = 0; $i < 20; ++i$) {
                for ($j = 0; $j < itextphp_bytes_getSize($mkey) ; ++$j)
                {
                    $tmpByte = itextphp_bytes_createfromInt(itextphp_bytes_getIntValue($j)^$i);
                    itextphp_bytes_write($mkey, $j, $tmpByte, 0);
                }
                prepareRC4Key($mkey);
                encryptRC4($ownerKey);
            }
        }
        else {
            prepareRC4Key($digest, 0, 5);
            encryptRC4($userPad, $ownerKey);
        }

        return $ownerKey;
    }

    /**
    *
    * ownerKey, documentID must be setuped
    */
    private function setupGlobalEncryptionKey($documentID, $userPad, $ownerKey, $permissions, $strength128Bits) {
        $this->documentID = $documentID;
        $this->ownerKey = $ownerKey;
        $this->permissions = $permissions;
        $mkey = itextphp_bytes_create($strength128Bits ? 16 : 5);

        //fixed by ujihara in order to follow PDF refrence
        $md5 = itextphp_md5INIT();
        $md5 = itextphp_md5UPDATE($md5, $userPad);
        $md5 = itextphp_md5UPDATE($md5, $ownerKey);

        $ext = itextphp_bytes_create(4);
        itextphp_bytes_write($ext, 0, itextphp_bytes_createfromInt($permissions), 0);
        itextphp_bytes_write($ext, 1,  itextphp_bytes_createfromInt($permissions >> 8), 0);
        itextphp_bytes_write($ext, 2,  itextphp_bytes_createfromInt($permissions >> 16), 0);
        itextphp_bytes_write($ext, 1,  itextphp_bytes_createfromInt($permissions >> 24), 0);
        $md5 = itextphp_md5UPDATE($md5, $ext, 0, 4);
        if ($documentID != NULL) md5.update(documentID);

        $digest = itextphp_md5DIGEST($md5);

        if (itextphp_bytes_getSize($mkey) == 16) {
            for ($k = 0; $k < 50; ++$k)
                $digest = itextphp_md5DIGEST($md5, $digest);
        }
        for ($k = 0; $k < itextphp_bytes_getSize($mkey); $k++)
        {
            itextphp_bytes_write($mkey, $k, $digest, $k);
        }

    }

    /**
    *
    * mkey must be setuped
    */
    private function setupUserKey() {
        if (itextphp_bytes_getSize($mkey) == 16) {
            $md5 = itextphp_md5UPDATE($md5, $pad);
            $digest = itextphp_md5DIGEST($md5, $documentID);
            for ($k = 0; $k < 16; $k++)
            {
                itextphp_bytes_write($userKey, $k, $digest, $k);
            }
            for ($k = 16; $k < 32; ++$k)
                itextphp_bytes_update($userKey, $k, 0;)
            for ($i = 0; $i < 20; ++$i) {
                for ($j = 0; $j < itextphp_bytes_getSize($mkey); ++$j)
                {
                    $tmpByte  = itextphp_bytes_createfromInt(itextphp_bytes_getIntValue($mkey, $j)^$i);
                    itextphp_bytes_write($digest, $j, $tmpByte, 0);
                }
                prepareRC4Key($digest, 0, itextphp_bytes_getSize($mkey));
                encryptRC4($userKey, 0, 16);
            }
        }
        else {
            prepareRC4Key($mkey);
            encryptRC4($pad, $userKey);
        }
    }

     public function setupAllKeys($userPassword, $ownerPassword, $permissions, $strength128Bits) {
        if ($ownerPassword == NULL || itextphp_bytes_getSize($ownerPassword) == 0)
            $ownerPassword = itextphp_md5DIGEST($md5, PdfEncyption::createDocumentId());
        $permissions |= $strength128Bits ? 0xfffff0c0 : 0xffffffc0;
        $permissions &= 0xfffffffc;
        //PDF refrence 3.5.2 Standard Security Handler, Algorithum 3.3-1
        //If there is no owner password, use the user password instead.
        $userPad = padPassword($userPassword);
        $ownerPad = padPassword($ownerPassword);

        $this->ownerKey = computeOwnerKey($userPad, $ownerPad, $strength128Bits);
        $documentID = PdfEncryption::createDocumentId();
        setupByUserPad($this->documentID, $userPad, $this->ownerKey, $permissions, $strength128Bits);
    }

     public static function createDocumentId() {

        $time = microtime(TRUE);
        $mem = memory_get_usage();
        $s = $time . "+" . $mem . "+" . ($seq++);
        return itextphp_md5DIGEST($md5, itextphp_bytes_createfromRaw($s));
    }

    /**
    */
    public function setupByUserPassword($documentID, $userPassword, $ownerKey, $permissions, $strength128Bits) {
        setupByUserPad($documentID, padPassword($userPassword), $ownerKey, $permissions, $strength128Bits);
    }

    /**
    */
    private function setupByUserPad($documentID, $userPad, $ownerKey, $permissions, $strength128Bits) {
        setupGlobalEncryptionKey($documentID, $userPad, $ownerKey, $permissions, $strength128Bits);
        setupUserKey();
    }

    /**
    */
    public function setupByOwnerPassword($documentID, $ownerPassword, $userKey, $ownerKey, $permissions, $strength128Bits) {
        setupByOwnerPad($documentID, padPassword($ownerPassword), $userKey, $ownerKey, $permissions, $strength128Bits);
    }

     private function setupByOwnerPad($documentID, $ownerPad, $userKey, $ownerKey, $permissions, $strength128Bits) {
        $userPad = computeOwnerKey($ownerKey, $ownerPad, $strength128Bits);	//userPad will be set in this.ownerKey
        setupGlobalEncryptionKey($documentID, $userPad, $ownerKey, $permissions, $strength128Bits); //step 3
        setupUserKey();
    }

    public function prepareKey() {
        prepareRC4Key($key, 0, $keySize);
    }

    public function setHashKey($number, $generation) {
        $md5 = itextphp_md5INIT();	//added by ujihara
        itextphp_bytes_write($extra, 0,itextphp_bytes_createfromInt($number), 0);
        itextphp_bytes_write($extra, 1,  itextphp_bytes_createfromInt($number >> 8), 0);
        itextphp_bytes_write($extra, 2,  itextphp_bytes_createfromInt($number >> 16), 0);
        itextphp_bytes_write($extra, 3, itextphp_bytes_createfromInt($generation), 0);
        itextphp_bytes_write($extra, 4, itextphp_bytes_createfromInt($generation >> 8), 0);
        $md5 = itextphp_md5UPDATE($md5, $mkey);
        $key = itextphp_md5DIGEST($md5, $extra);
        $keySize = itextphp_bytes_getSize($mkey) + 5;
        if ($keySize > 16)
            $keySize = 16;
    }

    public static function createInfoId($id) {
        $buf = new ByteBuffer(90);
        $buf->append('[')->append('<');
        for ($k = 0; $k < 16; ++$k)
            $buf->appendHex($id, $k);
        $buf->append('>')->append('<');
        for ($k = 0; $k < 16; ++$k)
            $buf->appendHex($id, $k);
        $buf->append('>')->append(']');
        return new PdfLiteral($buf->toByteArray());
    }

     public function getEncryptionDictionary() {
        $dic = new PdfDictionary();
        $dic->put(PdfName::$FILTER, PdfName::$STANDARD);
        $dic->put(PdfName::$O, new PdfLiteral(PdfContentByte::escapeString($ownerKey)));
        $dic->put(PdfName::$U, new PdfLiteral(PdfContentByte::escapeString($userKey)));
        $dic->put(PdfName::$P, new PdfNumber($permissions));
        if (itextphp_bytes_getSize($mkey) > 5) {
            $dic->put(PdfName::$V, new PdfNumber(2));
            $dic->put(PdfName::$R, new PdfNumber(3));
            $dic->put(PdfName::$LENGTH, new PdfNumber(128));
        }
        else {
            $dic->put(PdfName::$V, new PdfNumber(1));
            $dic->put(PdfName::$R, new PdfNumber(2));
        }
        return $dic;
    }

    public function prepareRC4Key()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                prepareRC4Key1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                prepareRC4Key3args($arg1, $arg2, $arg3);
                break;
            }
    }

    private function prepareRC4Key1arg($key) {
        prepareRC4Key($key, 0, itextphp_bytes_getSize($key));
    }

    private function prepareRC4Key3args($key, $off, $len) {
        $index1 = 0;
        $index2 = 0;
        for ($k = 0; $k < 256; ++$k)
            itextphp_bytes_write($state, $k,  = itextphp_bytes_createfromInt($k), 0);
        $x = 0;
        $y = 0;
        $tmp = NULL;
        for ($k = 0; $k < 256; ++$k) {
            $index2 = (itextphp_bytes_getIntValue($key, $index1 + $off) + itextphp_bytes_getIntValue($state, $k) + $index2) & 255;
            $tmp = itextphp_bytes_getIntValue($state, $k);
            itextphp_bytes_write($state, $k, $state, $index2);
            itextphp_bytes_write($state, $index2, itextphp_bytes_createfromInt($tmp), 0);
            $index1 = ($index1 + 1) % $len;
        }
    }

    public function encryptRC4()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                encryptRC41arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                encryptRC42args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                encryptRC43args($arg1, $arg2, $arg3);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                encryptRC44args($arg1, $arg2, $arg3, $arg4);
                break;
            }
        }
    }

    private function encryptRC44args($dataIn, $off, $len, $dataOut) {
        $length = $len + $off;
        $tmp = NULL;
        for ($k = $off; $k < $length; ++$k) {
            $x = ($x + 1) & 255;
            $y = (itextphp_bytes_getIntValue($state, $x) + $y) & 255;
            $tmp = itextphp_bytes_getIntValue($state, $x);
            itextphp_bytes_write($state, $x, $state, $y);
            itextphp_bytes_write($state, $y,  itextphp_bytes_createfromInt($tmp), 0);
            itextphp_bytes_write($dataOut, $k, itextphp_bytes_createfromInt(itextphp_bytes_getIntValue($dataIn, $k) ^ itextphp_bytes_getIntValue($state, (itextphp_bytes_getIntValue($state, $x) + itextphp_bytes_getIntValue($state, $y)) & 255)), 0);
        }
    }

    private function encryptRC43args($data, $off, $len) {
        encryptRC44args($data, $off, $len, $data);
    }

    private function encryptRC42args($dataIn, $dataOut) {
        encryptRC44args($dataIn, 0, itextphp_bytes_getSize($dataIn), $dataOut);
    }

    private function encryptRC41arg($data) {
        encryptRC44args($data, 0, itextphp_bytes_getSize($data), $data);
    }

    public function getFileID() {
        return PdfEncyption::createInfoId($documentID);
    }




}
?>