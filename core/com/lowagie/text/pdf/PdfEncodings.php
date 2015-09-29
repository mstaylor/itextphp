<?php
namespace com\lowagie\text\pdf;

require_once "ExtraEncoding.php";
require_once "BaseFont.php";
require_once "PdfObject.php";
require_once dirname(__FILE__) . "/../../../../php/lang/IllegalArgumentException.php";
use com\lowagie\text\pdf\ExtraEncoding as ExtraEncoding;
use com\lowagie\text\pdf\BaseFont as BaseFont;
use com\lowagie\text\pdf\PdfObject as PdfObject;
class PdfEncodings
{
    const CIDNONE = 0;
    const CIDRANGE = 1;
    const CIDCHAR = 2;


    static $winansiByteToChar = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15,16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31,32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47,48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63,64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79,      80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95,96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 8364, 65533, 8218, 402, 8222, 8230, 8224, 8225, 710, 8240, 352, 8249, 338, 65533, 381, 65533, 65533, 8216, 8217, 8220, 8221, 8226, 8211, 8212, 732, 8482, 353, 8250, 339, 65533, 382, 376,160, 161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175,176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191,192, 193, 194, 195, 196, 197, 198, 199, 200, 201, 202, 203, 204, 205, 206, 207,208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 242, 243, 244, 245, 246, 247, 248, 249, 250, 251, 252, 253, 254, 255);
    static $pdfEncodingByteToChar = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15,16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31,32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47,48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63,64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79,  80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95,96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111,112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 0x2022, 0x2020, 0x2021, 0x2026, 0x2014, 0x2013, 0x0192, 0x2044, 0x2039, 0x203a, 0x2212, 0x2030, 0x201e, 0x201c, 0x201d, 0x2018,0x2019, 0x201a, 0x2122, 0xfb01, 0xfb02, 0x0141, 0x0152, 0x0160, 0x0178, 0x017d, 0x0131, 0x0142,0x0153,0x0161, 0x017e, 65533, 0x20ac, 161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175,176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199, 200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 242, 243, 244, 245, 246, 247, 248, 249, 250, 251, 252, 253, 254, 255);
    static $winansi = array();
    static $pdfEncoding = array();
    static $extraEncodings = array();
    static $cmaps = array();

    public static $initialized = FALSE;
    


    public static function initializeStatics()
    {
        if(PdfEncodings::$initialized == FALSE)
        {
            for ($k = 128; $k < 161; ++$k) 
            {
                $c = PdfEncodings::$winansiByteToChar[$k];
                if ($c != 65533)
                    PdfEncodings::$winansi[$c] = $k;
            }

            for ($k = 128; $k < 161; ++$k) 
            {
                $c = PdfEncodings::$pdfEncodingByteToChar[$k];
                if ($c != 65533)
                {
                    PdfEncodings::$pdfEncoding[$c] = $k;
                }
            }
            PdfEncodings::$extraEncodings["wingdings"] = new WingdingsConversion();

            Cp437Conversion::initializeStatics();
            SymbolConversion::initializeStatics();
            PdfEncodings::addExtraEncoding("Wingdings", new WingdingsConversion());
            PdfEncodings::addExtraEncoding("Symbol", new SymbolConversion(TRUE));
            PdfEncodings::addExtraEncoding("ZapfDingbats", new SymbolConversion(FALSE));
            PdfEncodings::addExtraEncoding("SymbolTT", new SymbolTTConversion());
            PdfEncodings::addExtraEncoding("Cp437", new Cp437Conversion());

            PdfEncodings::$initialized = TRUE;
        }
    }

    /** Converts a <CODE>String</CODE> to a </CODE>byte</CODE> array according
    * to the font's encoding.
    * @return an array of <CODE>byte</CODE> representing the conversion according to the font's encoding
    * @param encoding the encoding
    * @param text the <CODE>String</CODE> to be converted
    */
    public static final function convertToBytes($text, $encoding)
    {
        if ($text == NULL)
            return /**new byte[0]**/ NULL;
        if ($encoding == NULL || strlen($encoding) == 0)
        {
            $len = strlen($text);
            return itextphp_charToByte(itextphp_newString($text,$len,1), $len);
        }
        $extra = NULL;
        $extra = $extraEncodings[strtolower($encoding)];
        if ($extra != NULL)
        {
            $b = $extra->charToByte($text, $encoding);
            if ($b != NULL)
                return $b;
        }
        $hash = NULL;
        if (strcmp($encoding,BaseFont::WINANSI)==0)
        {
            $hash = $winansi;
        }
        else if (strcmp($encoding, PdfObject::TEXT_PDFDOCENCODING)==0)
        {
            $hash = PdfEncodings::$pdfEncoding;
        }
        if ($hash != NULL)
        {
            $len = count($text);
            $theArray = itextphp_charToBytePDFDocEncoding(itextphp_newString($text,$len,1), $len, $hash);
            if ($theArray[0] == TRUE)
                return $theArray[1];
            else
                return $theArray[2];
        }
        if (strcmp($encoding,PdfObject::TEXT_UNICODE))
        {
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
    public static final function convertToString($bytes, $encoding)
    {
        if ($bytes == NULL)
        {
            return PdfObject::NOTHING;
        }
        if ($encoding == NULL || strlen($encoding) == 0)
        {
            /*char c[] = new char[bytes.length];*/
            return itextphp_byteToString($bytes, 0);
            //return new String(c);
        }
        $extra = NULL;
        $extra = PdfEncodings::$extraEncodings[strtolower($encoding)];

        if ($extra != NULL)
        {
            $text = $extra->byteToChar($bytes, $encoding);
            if ($text != NULL)
            {
                return $text;
            }
        }
        //char ch[] = null;
        $ch = NULL;
        if (strcmp($encoding,BaseFont::WINANSI) == 0)
        {
            $ch = PdfEncodings::$winansiByteToChar;
        }
        else if (strcmp($encoding, PdfObject::TEXT_PDFDOCENCODING)==0)
        {
            $ch = PdfEncodings::$pdfEncodingByteToChar;
        }
        if ($ch != NULL)
        {
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
    public static function isPdfDocEncoding($text) 
    {
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
    public static function clearCmap($name) 
    {

            if (strlen($name) == 0)
                PdfEncodings::$cmaps = array();
            else
            {

               if (isset(PdfEncodings::$cmaps[$name]))
                   unset(PdfEncodings::$cmaps[$name]);
            }

    }

    /** Loads a CJK cmap to the cache with the option of associating
    * sequences to the newline.
    * @param name the CJK cmap name
    * @param newline the sequences to be replaced bi a newline in the resulting CID. See <CODE>CRLF_CID_NEWLINE</CODE>
    */
    public static function loadCmap($name, $newline)
    {
        try
        {
            $planes = NULL;
            $planes = PdfEncodings::$cmaps[$name];
            if ($planes == null) {
                $planes = PdfEncodings::readCmap($name, $newline);
                    PdfEncodings::$cmaps[$name] = $planes;
                }

        }
        catch (IOException $e) {
            throw new IOException($e->getMessage());
        }
    }




    /** Adds an extra encoding.
    * @param name the name of the encoding. The encoding recognition is case insensitive
    * @param enc the conversion class
    */
    public static function addExtraEncoding($name, $enc) 
    {
        PdfEncodings::$extraEncodings[strtolower($name)] = $enc;
    }

}


class WingdingsConversion implements ExtraEncoding
{

    public function charToByte($text, $encoding)
    {
        return itextphp_wingdings_chartobyte($text,$encoding);

    }
    public function byteToChar($b, $encoding)
    {
        return NULL;
    }
}

class Cp437Conversion implements ExtraEncoding 
{
    private static $c2b = NULL;

    public function charToByte($text, $encoding) 
    {
        return itextphp_cp437_chartobyte($text, $encoding, $c2b);
    }

    public function byteToChar($b, $encoding)
    {
        return itextphp_cp437_bytetochar($b, $encoding);
    }


    public static function initializeStatics()
    {
        Cp437Conversion::$c2b = array();
        itextphp_cp437_initialize(Cp437Conversion::$c2b);
    }
}

class SymbolConversion implements ExtraEncoding 
{
    private static $t1 = NULL;
    private static $t2 = NULL;
    private $translation;

    public function __construct($symbol) {
        if ($symbol == TRUE)
            $translation = $t1;
        else
            $translation = $t2;
    }

    public function charToByte($text, $encoding) 
    {
        return itextphp_symbolconversion_chartobyte($text, $encoding, $translation);
    }

    public function byteToChar($b, $encoding) 
    {
        return NULL;
    }


    public static function initializeStatics() 
    {
        SymbolConversion::$t1 = array();
        SymbolConversion::$t2 = array();
        itextphp_symbolconversion_initialize(SymbolConversion::$t1, SymbolConversion::$t2);
    }
}


class SymbolTTConversion implements ExtraEncoding 
{

        public function charToByte($text, $encoding)
        {
            return itextphp_symbolttconversion_chartobyte($text, $encoding);
        }

        public function byteToChar($b, $encoding) 
        {
            return NULL;
        }


}



PdfEncodings::initializeStatics();
?>