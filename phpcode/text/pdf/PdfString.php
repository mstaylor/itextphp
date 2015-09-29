<?PHP
/*
 * $Id: PdfString.php,v 1.2 2005/10/12 15:03:18 mstaylor Exp $
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

require_once("../../exceptions/IOException.php");
require_once("../../io/OutputStream.php");
require_once("PdfObject.php");
require_once("PdfEncodings.php");
require_once("PdfWriter.php");
require_once("PdfEncryption.php");
require_once("ByteBuffer.php");
require_once("PdfContentByte.php");
require_once("PdfEncodings.php");
require_once("PdfReader.php");

/**
* A <CODE>PdfString</CODE>-class is the PDF-equivalent of a JAVA-<CODE>String</CODE>-object.
* <P>
* A string is a sequence of characters delimited by parenthesis. If a string is too long
* to be conveniently placed on a single line, it may be split across multiple lines by using
* the backslash character (\) at the end of a line to indicate that the string continues
* on the following line. Within a string, the backslash character is used as an escape to
* specify unbalanced parenthesis, non-printing ASCII characters, and the backslash character
* itself. Use of the \<I>ddd</I> escape sequence is the preferred way to represent characters
* outside the printable ASCII character set.<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 4.4 (page 37-39).
*
* @see		PdfObject
* @see		BadPdfFormatException
*/

class PdfString extends PdfObject
{

    // membervariables

    /** The value of this object. */
    protected $value = PdfObject::NOTHING;
    protected $originalValue = NULL;
        /** The encoding. */
    protected $encoding = PdfObject::TEXT_PDFDOCENCODING;
    protected $objNum = 0;
    protected $objGen = 0;
    protected $hexWriting = FALSE;

    // constructors

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
                if (is_resource($arg1) == TRUE)
                    construct1argResource($arg1);
                else if (is_string($arg1) == TRUE)
                    construct1argString($arg1);
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
    * Constructs an empty <CODE>PdfString</CODE>-object.
    */

    private function construct0args() {
        parent::__construct(PdfObject::STRING);
    }

    /**
    * Constructs a <CODE>PdfString</CODE>-object.
    *
    * @param		bytes	an array of <CODE>byte</CODE>
    */
    private function construct1argResource($bytes) {
        parent::__construct(PdfObject::STRING);
        $value = PdfEncodings::convertToString($bytes, NULL);
        $encoding = PdfObject::NOTHING;
    }

    /**
    * Constructs a <CODE>PdfString</CODE>-object.
    *
    * @param		value		the content of the string
    */

    private function construct1argString($value) {
        parent::__construct(PdfObject::STRING);
        $this->value = $value;
    }

    /**
    * Constructs a <CODE>PdfString</CODE>-object.
    *
    * @param		value		the content of the string
    * @param		encoding	an encoding
    */

    private function construct2args($value, $encoding) {
        parent::__construct(PdfObject::STRING);
        $this->value = $value;
        $this->encoding = $encoding;
    }

     // methods overriding some methods in PdfObject

    /**
    * Returns the PDF representation of this <CODE>PdfString</CODE>.
    *
    * @return		an array of <CODE>byte</CODE>s
    */

    public function toPdf(PdfWriter $writer, OutputStream $os) {
        $b = getBytes();
        $crypto = NULL;
        if ($writer != NULL)
            $crypto = $writer->getEncryption();
        if ($crypto != NULL) {
            $b = itextphp_bytes_create(itextphp_bytes_getSize($bytes));
            for($i = 0; $i < itextphp_bytes_getSize($bytes); $i++)
            {
                itextphp_bytes_write($b, $i, $bytes, $i);
            }
            $crypto->prepareKey();
            $crypto->encryptRC4(b);
        }
        if ($hexWriting == TRUE) {
            $buf = new ByteBuffer();
            $buf->append('<');
            $len = itextphp_bytes_getSize($b);
            for ($k = 0; $k < $len; ++$k)
                $buf->appendHex($b, $k);
            $buf->append('>');
            $os->write($buf->toByteArray());
        }
        else
            $os->write(PdfContentByte::escapeString($b));
    }

    /**
    * Returns the <CODE>String</CODE> value of the <CODE>PdfString</CODE>-object.
    *
    * @return		a <CODE>String</CODE>
    */

    public function toString() {
        return $value;
    }

     // other methods

    /**
    * Gets the encoding of this string.
    *
    * @return		a <CODE>String</CODE>
    */

    public function getEncoding() {
        return $encoding;
    }

    public function toUnicodeString() {
        if ($encoding != NULL && strlen($encoding) != 0)
            return $value;
        getBytes();
        if (itextphp_bytes_getSize($bytes) >= 2 && itextphp_bytes_getIntValue($bytes, 0) == 254 && itextphp_bytes_getIntValue($bytes, 1) == 255)
            return PdfEncodings::convertToString($bytes, PdfObject::TEXT_UNICODE);
        else
            return PdfEncodings::convertToString($bytes, PdfObject::TEXT_PDFDOCENCODING);
    }

    function setObjNum($objNum, $objGen) {
        $this->objNum = $objNum;
        $this->objGen = $objGen;
    }

    function decrypt(PdfReader $reader) {
        $decrypt = $reader->getDecrypt();
        if ($decrypt != NULL) {
            $originalValue = $value;
            $decrypt->setHashKey($objNum, $objGen);
            $decrypt->prepareKey();
            $bytes = PdfEncodings::convertToBytes($value, NULL);
            $decrypt->encryptRC4($bytes);
            $value = PdfEncodings::convertToString($bytes, NULL);
        }
    }

    public function getBytes() {
        if ($bytes == NULL) {
            if ($encoding != NULL && strcmp($encoding, PdfObject::TEXT_UNICODE) == 0) && PdfEncodings::isPdfDocEncoding($value))
                $bytes = PdfEncodings::convertToBytes($value, PdfObject::TEXT_PDFDOCENCODING);
            else
                $bytes = PdfEncodings::convertToBytes($value, $encoding);
        }
        return $bytes;
    }

    public function getOriginalBytes() {
        if ($originalValue == NULL)
            return getBytes();
        return PdfEncodings::convertToBytes($originalValue, NULL);
    }

    public function setHexWriting($hexWriting) {
        $this->hexWriting = $hexWriting;
        return $this;
    }

    public function isHexWriting() {
        return $hexWriting;
    }




}

?>