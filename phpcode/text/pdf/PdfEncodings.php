<?PHP
/*
 * $Id: PdfEncodings.php,v 1.1.1.1 2005/09/22 16:10:04 mstaylor Exp $
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

/** Supports fast encodings for winansi and PDFDocEncoding.
 * Supports conversions from CJK encodings to CID.
 * Supports custom encodings.
 * @author (original author)Paulo Soares (psoares@consiste.pt)
 */

require_once("ExtraEncoding.php");
require_once("PdfObject.php");
require_once("BaseFont.php");
require_once("../exceptions/IOException.php");
require_once("../util/Helpers.php");

class PdfEncodings
{
    protected static $CIDNONE = 0;
    protected static $CIDRANGE = 1;
    protected static $CIDCHAR = 2;
   

    static $winansi = array();
    static $pdfEncoding = array();

    static $extraEncodings = array();

    static $cmaps = array();

    static $CRLF_CID_NEWLINE = NULL;


    public static function initializeStatics()
    {
        itextphp_initializepdfencodingsConstants($winansi, $pdfEncoding);

        addExtraEncoding("Wingdings", new WingdingsConversion());
        addExtraEncoding("Symbol", new SymbolConversion(TRUE));
        addExtraEncoding("ZapfDingbats", new SymbolConversion(FALSE));
        addExtraEncoding("SymbolTT", new SymbolTTConversion());
        addExtraEncoding("Cp437", new Cp437Conversion());




 
    }



    /** Converts a <CODE>String</CODE> to a </CODE>byte</CODE> array according
    * to the font's encoding.
    * @return an array of <CODE>byte</CODE> representing the conversion according to the font's encoding
    * @param encoding the encoding
    * @param text the <CODE>String</CODE> to be converted
    */
    public static final function convertToBytes($text, $encoding) {
        ///start here tom....
        if ($text == NULL)
            return /**new byte[0]**/ NULL;
        if ($encoding == NULL || strlen($encoding) == 0) {
            $len = strlen($text);
            return itextphp_charToByte(itextphp_newString($text,$len,1), $len);
        }
        $extra = NULL;
            $extra = $extraEncodings[strtolower($encoding)];
        if ($extra != NULL) {
            $b = $extra->charToByte($text, $encoding);
            if ($b != NULL)
                return $b;
        }
        $hash = NULL;
        if (strcmp($encoding,BaseFont::WINANSI)==0)
            $hash = $winansi;
        else if (strcmp($encoding, PdfObject::TEXT_PDFDOCENCODING)==0)
            $hash = $pdfEncoding;
        if ($hash != NULL) {

            $len = count($text);

            $theArray = itextphp_charToBytePDFDocEncoding(itextphp_newString($text,$len,1), $len, $hash);
            if ($theArray[0] == TRUE)
                return $theArray[1];
            else
                return $theArray[2];

        }
        if (strcmp($encoding,PdfObject::TEXT_UNICODE)) {
            // workaround for jdk 1.2.2 bug
            //char cc[] = text.toCharArray();
            
            return itextphp_charToByteUnicodeEncoding(itextphp_newString($text,strlen($text),2),$len);
        }
         $len = strlen($text);
         return itextphp_charToByte(itextphp_newString($text,$len,1), $len);

    }



    /** Converts a </CODE>byte</CODE> array to a <CODE>String</CODE> according
    * to the some encoding.
    * @param bytes the bytes to convert
    * @param encoding the encoding
    * @return the converted <CODE>String</CODE>
    */
    public static final function convertToString($bytes, $encoding) {
        if ($bytes == NULL)
            return PdfObject::NOTHING;
        if ($encoding == NULL || strlen($encoding) == 0) {
            /*char c[] = new char[bytes.length];*/
            return itextphp_byteToString($bytes, 0);
//            return new String(c);
        }
            $extra = NULL;

            $extra = $extraEncodings[strtolower($encoding)];

        if ($extra != NULL) {
            $text = $extra->byteToChar($bytes, $encoding);
            if ($text != NULL)
                return $text;
        }
        //char ch[] = null;
        $ch = NULL;
        if (strcmp($encoding,BaseFont::WINANSI) == 0)
            $ch = $winansiByteToChar;
        else if (strcmp($encoding, PdfObject::TEXT_PDFDOCENCODING)==0)
            $ch = $pdfEncodingByteToChar;
        if ($ch != NULL) {
            return itextphp_byteToStringPDFDocEncoding($bytes,0,$ch);
        }

        $converted_string = itextphp_byteToStringUnicodeEncoding($bytes,$encoding);

        if ($converted_string == NULL)
        {
            throw new Exception("Unsupported Encoding");
        }
        else
        {
            return $converted_string;
        }
    }


    /** Checks is <CODE>text</CODE> only has PdfDocEncoding characters.
    * @param text the <CODE>String</CODE> to test
    * @return <CODE>true</CODE> if only PdfDocEncoding characters are present
    */
    public static function isPdfDocEncoding($text) {
        if ($text == NULL)
            return TRUE;
        return itextphp_isPdfDocEncodings($text, $pdfEncoding);
    }

    /** Clears the CJK cmaps from the cache. If <CODE>name</CODE> is the
    * empty string then all the cache is cleared. Calling this method
    * has no consequences other than the need to reload the cmap
    * if needed.
    * @param name the name of the cmap to clear or all the cmaps if the empty string
    */
    public static function clearCmap($name) {

            if (strlen($name) == 0)
                cmaps = array();
            else
            {

               if (isset($cmaps[$name]))
                   unset($cmaps[$name]);
            }

    }

    /** Loads a CJK cmap to the cache with the option of associating
    * sequences to the newline.
    * @param name the CJK cmap name
    * @param newline the sequences to be replaced bi a newline in the resulting CID. See <CODE>CRLF_CID_NEWLINE</CODE>
    */    
    public static function loadCmap($name, $newline) {
        try {
            $planes = NULL;
            $planes = $cmaps[$name];
            if ($planes == null) {
                $planes = PdfEncodings::readCmap($name, $newline);
                    cmaps.put(name, planes);
                }

        }
        catch (IOException $e) {
            throw new IOException($e->getMessage());
        }
    }



    /** Converts a <CODE>byte</CODE> array encoded as <CODE>name</CODE>
    * to a CID string. This is needed to reach some CJK characters
    * that don't exist in 16 bit Unicode.</p>
    * The font to use this result must use the encoding "Identity-H"
    * or "Identity-V".</p>
    * See ftp://ftp.oreilly.com/pub/examples/nutshell/cjkv/adobe/.
    * @param name the CJK encoding name
    * @param seq the <CODE>byte</CODE> array to be decoded
    * @return the CID string
    */
    public static function convertCmap() {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $name = func_get_arg(0);
                $seq = func_get_arg(1);
                return convertCmap($name, $seq, 0, itextphp_bytes_getSize($seq));
                break;
            }
            case 3:
            {
                $name = func_get_arg(0);
                $seq = func_get_arg(1);
                $start = func_get_arg(2);
                $length = func_get_arg(3);
                try 
                {
                    $planes = NULL;
                    $planes = $cmaps[$name];
                    if ($planes == NULL) {
                        $planes = readCmap($name, NULL);
                        $cmaps[$name] = $planes;

                    }
                    return PdfEncodings::decodeSequence($seq, $start, $length, $planes);
                }
                catch (IOException $e) {
                    throw new ExceptionConverter($e->getMessage());
                } 
                break;
            }
        }
    }

    static function decodeSequence($seq, $start, $length, $planes) {
        return itextphp_decodesequence($seq,$start, $length, $planes);
    }


    static function readCmap($name, $newline)
    {
        if (strcmp($newline[0],"Array")==0)
        {
        $planes = array();
        $planes[0] = array(itextphp_newString("",256,2))
        PdfEncodings::readCmapPlane($name, $planes);
        if ($newline != NULL)
        {
             for ($k = 0; $k < count($newline); ++$k)
                 PdfEncodings::encodeSequence(itextphp_multiArrayByteDataTypeSize($newline, $k), itextphp_multiArrayByteDataTypeValue($newline,$k), BaseFont::CID_NEWLINE, $planes);

             return $planes;//note: mayneed to convert to multidimensional array here
        }
        }

    }

    static function readCmapPlane($name, $planes)
    {

        $fullName = "../" . BaseFont::RESOURCE_PATH . "cmaps/" . $name;
        $file = file($fullName);
        if ($file == FALSE)
        {
            throw new IOException("The Cmap " .  $name . " was not found.");
        }

        PdfEncodings::encodeStream($file, $planes);
        fclose($file);
    }

    static function encodeStream($in, $planes)
    {
        //BufferedReader rd = new BufferedReader(new InputStreamReader(in, "iso-8859-1"));
        $line = NULL;
        $state = PdfEncodings::$CIDNONE;
        $seqs = array(7);
        foreach ($lines as $line_num => $line) {
            if (strlen($line) < 6)
                continue;
            switch ($state) {
                case PdfEncodings::$CIDNONE: {
                    if (strpos($line, "begincidrange") >= 0)
                        $state = PdfEncodings::$CIDRANGE;
                    else if (strpos($line, "begincidchar") >= 0)
                        $state = PdfEncodings::$CIDCHAR;
                    else if (strpos($line, "usecmap") >= 0) {
                        $tk = preg_split("/[\s]+/", $line);
                        $t = $tk[0];
                        PdfEncodings::readCmap(substr($t,1), $planes);
                    }
                    break;
                }
                case PdfEncodings::$CIDNONE: {
                    if (strpos($line, "endcidrange") >= 0) {
                        $state = PdfEncodings::$CIDNONE;
                        break;
                    }
                    $tk = preg_split("/[\s]+/", $line);
                    $t = $tk[0];
                    $size = strlen($t) / 2 - 1;
                    $start = substr($t,1,strlen($t) - 1);
                    $t = $tk[1];
                    $end = substr($t, 1, strlen($t) - 1);
                    $t = $tk[2];
                    $cid = $t;
                    for ($k = $start; $k <= $end; ++$k) {
                        PdfEncodings::breakLong($k, $size, $seqs);
                        PdfEncodings::encodeSequence($size, $seqs, chr($cid), $planes);
                        ++$cid;
                    }
                    break;
                }
                case PdfEncodings::$CIDCHAR: {
                    if (strpos($line, "endcidchar") >= 0) {
                        $state = PdfEncodings::$CIDNONE;
                        break;
                    }
                    $tk = preg_split("/[\s]+/", $line);
                    $t = $tk[0];
                    $size = strlen($t) / 2 - 1;
                    $start = substr($t, 1, strlen($t) - 1);
                    $t = $tk[1];
                    $cid = $t;
                    PdfEncodings::breakLong($start, $size, $seqs);
                    PdfEncodings::encodeSequence($size, $seqs, chr($cid), $planes);
                    break;
                }
            }
        }
    }


    static function breakLong($n, $size, $seqs) {
        itextphp_breaklong($n, $size, $seqs);
    }

    static function encodeSequence($size, $seqs, $cid, $planes) {
        $result = itextphp_encodesequence($size, $seqs, $cid, $planes);
        if (is_array($result) == TRUE)
                throw new Exception($result[0]);
    }


    /** Adds an extra encoding.
    * @param name the name of the encoding. The encoding recognition is case insensitive
    * @param enc the conversion class
    */
    public static function addExtraEncoding($name, $enc) 
    {
        $extraEncodings[strtolower($name)] = $enc;
    }

}


class WingdingsConversion implements ExtraEncoding 
{
    public function charToByte($text, $encoding) {
       return itextphp_wingdings_chartobyte($text,$encoding);
    }

    public function byteToChar($b, $encoding) {
        return NULL;
    }

}


class Cp437Conversion implements ExtraEncoding {
    private static $c2b = array();

    public function charToByte($text, $encoding) {
        return itextphp_cp437_chartobyte($text, $encoding, $c2b);
    }

    public function byteToChar($b, $encoding) {
        return itextphp_cp437_bytetochar($b, $encoding);
    }


    public static function initializeStatics {
        itextphp_cp437_initialize($c2b);
    }
}


class SymbolConversion implements ExtraEncoding {
    private static $t1 = array();
    private static $t2 = array();
    private $translation;

    public function __construct($symbol) {
        if ($symbol == TRUE)
            $translation = $t1;
        else
            $translation = $t2;
    }

    public function charToByte($text, $encoding) {
        return itextphp_symbolconversion_chartobyte($text, $encoding, $translation);
    }

        public function byteToChar($b, $encoding) {
            return NULL;
        }


    public static function initializeStatics() {
        itextphp_symbolconversion_initialize($t1, $t2);
    }
}


class SymbolTTConversion implements ExtraEncoding {

        public function charToByte($text, $encoding) {
            return itextphp_symbolttconversion_chartobyte($text, $encoding);
        }

        public String byteToChar(byte[] b, String encoding) {
            return null;
        }

    }

?>