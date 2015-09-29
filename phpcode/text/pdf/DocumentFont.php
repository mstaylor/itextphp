<?PHP
/*
 * $Id: DocumentFont.php,v 1.2 2005/09/29 22:02:44 mstaylor Exp $
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


require_once("../DocumentException.php");
require_once("BaseFont.php");
require_once("PdfDictionary.php");
require_once("PdfIndirectReference.php");
require_once("PdfReader.php");
require_once("PdfWriter.php");
require_once("PdfName.php");
require_once("PdfNumber.php");
require_once("PdfObject.php");
require_once("PdfArray.php");
require_once("GlyphList.php");
require_once("CJKFont.php");
require_once("../../util/StringHelpers.php");


class DocumentFont extends BaseFont
{
    $fontName = "";
    $refFont = NULL;
    $font = NULL;
    $uni2byte = array();
    $Ascender = 800.0;
    $CapHeight = 700.0;
    $Descender = -200.0;
    $ItalicAngle = 0.0;
    $llx = -50.0;
    $lly = -200.0;
    $urx = 100.0;
    $ury = 900.0;

    $cjkMirror = NULL;

    $cjkNames = array("HeiseiMin-W3", "HeiseiKakuGo-W5", "STSong-Light", "MHei-Medium",
        "MSung-Light", "HYGoThic-Medium", "HYSMyeongJo-Medium", "MSungStd-Light", "STSongStd-Light",
        "HYSMyeongJoStd-Medium", "KozMinPro-Regular");

    $cjkEncs = array("UniJIS-UCS2-H", "UniJIS-UCS2-H", "UniGB-UCS2-H", "UniCNS-UCS2-H",
        "UniCNS-UCS2-H", "UniKS-UCS2-H", "UniKS-UCS2-H", "UniCNS-UCS2-H", "UniGB-UCS2-H",
        "UniKS-UCS2-H", "UniJIS-UCS2-H");

    static $stdEnc = array(
        0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
        0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
        32,33,34,35,36,37,38,8217,40,41,42,43,44,45,46,47,
        48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,
        64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,
        80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,
        8216,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,
        112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,0,
        0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
        0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
        0,161,162,163,8260,165,402,167,164,39,8220,171,8249,8250,64257,64258,
        0,8211,8224,8225,183,0,182,8226,8218,8222,8221,187,8230,8240,0,191,
        0,96,180,710,732,175,728,729,168,0,730,184,0,733,731,711,
        8212,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
        0,198,0,170,0,0,0,0,321,216,338,186,0,0,0,0,
        0,230,0,0,0,305,0,0,322,248,339,223,0,0,0,0);

    public function __construct(PRIndirectReference $refFont)
    {
        $encoding = "";
        $fontSpecific = FALSE;
        $this->refFont = $refFont;
        $fontType = BaseFont::FONT_TYPE_DOCUMENT;
        $font = PdfReader::getPdfObject($refFont);
        $fontName = PdfName::decodeName((PdfReader::getPdfObject($font->get(PdfName::$BASEFONT)))->toString());
        $subType = PdfReader::getPdfObject($font->get(PdfName::$SUBTYPE));
        if (PdfName::$TYPE1->equals($subType) == TRUE || PdfName::$TRUETYPE->equals($subType) == TRUE)
            doType1TT();
        else {
            for ($k = 0; k < count($cjkNames); ++$k) {
                if (beginsWith($fontName, $cjkNames[$k]) == TRUE) {
                    $fontName = $cjkNames[$k];
                    try {
                        $cjkMirror = BaseFont::createFont($fontName, $cjkEncs[$k], FALSE);
                    }
                    catch (Exception $e) {
                        throw new Exception($e);
                    }
                    return;
                }
            }
        }
    }


    public function doType1TT() {
        $enc = PdfReader::getPdfObject($font->get(PdfName::$ENCODING));
        if ($enc == NULL)
            fillEncoding(NULL);
        else {
            if ($enc->isName() == TRUE)
                fillEncoding($enc);
            else {
                $encDic = $enc;
                $enc = PdfReader::getPdfObject($encDic->get(PdfName::$BASEENCODING));
                if ($enc == NULL)
                    fillEncoding(NULL);
                else
                    fillEncoding($enc);
                $diffs = PdfReader::getPdfObject($encDic->get(PdfName::$DIFFERENCES));
                if ($diffs != NULL) {
                    $dif = $diffs->getArrayList();
                    $currentNumber = 0;
                    for ($k = 0; $k < count($dif); ++$k) {
                        $obj = $dif[$k];
                        if ($obj->isNumber() == TRUE)
                            $currentNumber = ($obj)->intValue();
                        else {
                            $c = GlyphList::nameToUnicode(PdfName::decodeName(($obj)->toString()));
                            if ($c != NULL && count($c) > 0)
                                $uni2byte[$c[0]] = $currentNumber;
                            ++$currentNumber;
                        }
                    }
                }
            }
        }
        $newWidths = PdfReader::getPdfObject($font->get(PdfName::$WIDTHS));
        $first = PdfReader::getPdfObject($font->get(PdfName::$FIRSTCHAR));
        $last = PdfReader::getPdfObject($font->get(PdfName::$LASTCHAR));
        if (array_key_exists($fontName, BaseFont::BuiltinFonts14) == TRUE) {
            $bf = NULL;
            try {
                $bf = BaseFont::createFont($fontName, BaseFont::WINANSI, FALSE);
            }
            catch (Exception $e) {
                throw new Exception($e);
            }
            $e = array();
            $e = array_merge($e, $uni2byte);
            sort(arrays_keys($e));
            for ($k = 0; $k < count($e); ++$k) {
                $n = $uni2byte[$e[$k]];
                $widths[$n] = $bf->getRawWidth($n, GlyphList::unicodeToName($e[$k]));
            }
            $Ascender = $bf->getFontDescriptor(BaseFont::ASCENT, 1000);
            $CapHeight = $bf->getFontDescriptor(BaseFont::CAPHEIGHT, 1000);
            $Descender = $bf->getFontDescriptor(BaseFont::DESCENT, 1000);
            $ItalicAngle = $bf->getFontDescriptor(BaseFont::ITALICANGLE, 1000);
            $llx = $bf->getFontDescriptor(BaseFont::BBOXLLX, 1000);
            $lly = $bf->getFontDescriptor(BaseFont::BBOXLLY, 1000);
            $urx = $bf->getFontDescriptor(BaseFont::BBOXURX, 1000);
            $ury = $bf->getFontDescriptor(BaseFont::BBOXURY, 1000);
        }
        if ($first != NULL && $last != NULL && $newWidths != NULL) {
            $f = ($first)->intValue();
            $ar = ($newWidths)->getArrayList();
            for ($k = 0; $k < count($ar); ++$k) {
                $widths[$f + $k] = ($ar[$k])->intValue();
            }
        }
        fillFontDesc();
    }

     function fillFontDesc() {
        $fontDesc = PdfReader::getPdfObject($font->get(PdfName::$FONTDESCRIPTOR));
        if ($fontDesc == NULL)
            return;
        $v = PdfReader::getPdfObject($fontDesc->get(PdfName::$ASCENT));
        if ($v != NULL)
            $Ascender = $v->floatValue();
        $v = PdfReader::getPdfObject($fontDesc->get(PdfName::$CAPHEIGHT));
        if ($v != NULL)
            $CapHeight = $v->floatValue();
        $v = PdfReader::getPdfObject($fontDesc->get(PdfName::$DESCENT));
        if ($v != NULL)
            $Descender = $v->floatValue();
        $v = PdfReader::getPdfObject($fontDesc->get(PdfName::$ITALICANGLE));
        if ($v != NULL)
            $ItalicAngle = $v->floatValue();
        $bbox = PdfReader::getPdfObject($fontDesc->get(PdfName::$FONTBBOX));
        if ($bbox != NULL) {
            $ar = $bbox->getArrayList();
            $llx = ($ar[0])->floatValue();
            $lly = ($ar[1])->floatValue();
            $urx = ($ar[2])->floatValue();
            $ury = ($ar[3])->floatValue();
            if ($llx > $urx) {
                $t = $llx;
                $llx = $urx;
                $urx = $t;
            }
            if ($lly > $ury) {
                $t = $lly;
                $lly = $ury;
                $ury = $t;
            }
        }
    }


    function fillEncoding(PdfName $encoding) {
        if (PdfName::$MAC_ROMAN_ENCODING->equals($encoding) == TRUE || PdfName::$WIN_ANSI_ENCODING->equals($encoding) == TRUE) {
            $b = itextphp_bytes_create(256);
            for ($k = 0; $k < 256; ++$k)
            {
                itextphp_bytes_update($b,$k, $k)
            }
            $enc = BaseFont::WINANSI;
            if (PdfName::$MAC_ROMAN_ENCODING->equals($encoding) == TRUE)
                $enc = BaseFont::MACROMAN;
            $cv = PdfEncodings::convertToString($b, $enc);
            //char arr[] = cv.toCharArray();
            for ($k = 0; $k < 256; ++$k)
                uni2byte[itextphp_string_getIntFromIndex($arr, $k)] = $k;
        }
        else {
            for ($k = 0; $k < 256; ++$k)
                $uni2byte[$stdEnc[$k]]= $k;
        }
    }

    /** Gets the family name of the font. If it is a True Type font
    * each array element will have {Platform ID, Platform Encoding ID,
    * Language ID, font name}. The interpretation of this values can be
    * found in the Open Type specification, chapter 2, in the 'name' table.<br>
    * For the other fonts the array has a single element with {"", "", "",
    * font name}.
    * @return the family name of the font
    *
    */
    public function getFamilyFontName() {
        return $null;
    }

    /** Gets the font parameter identified by <CODE>key</CODE>. Valid values
    * for <CODE>key</CODE> are <CODE>ASCENT</CODE>, <CODE>CAPHEIGHT</CODE>, <CODE>DESCENT</CODE>,
    * <CODE>ITALICANGLE</CODE>, <CODE>BBOXLLX</CODE>, <CODE>BBOXLLY</CODE>, <CODE>BBOXURX</CODE>
    * and <CODE>BBOXURY</CODE>.
    * @param key the parameter to be extracted
    * @param fontSize the font size in points
    * @return the parameter in points
    *
    */
    public function getFontDescriptor($key, $fontSize) {
        if ($cjkMirror != NULL)
            return $cjkMirror->getFontDescriptor($key, $fontSize);
        switch ($key) {
            case BaseFont::AWT_ASCENT:
            case BaseFont::ASCENT:
                return $Ascender * $fontSize / 1000;
            case BaseFont::CAPHEIGHT:
                return $CapHeight * $fontSize / 1000;
            case BaseFont::AWT_DESCENT:
            case BaseFont::DESCENT:
                return $Descender * $fontSize / 1000;
            case BaseFont::ITALICANGLE:
                return $ItalicAngle;
            case BaseFont::BBOXLLX:
                return $llx * $fontSize / 1000;
            case BaseFont::BBOXLLY:
                return $lly * $fontSize / 1000;
            case BaseFont::BBOXURX:
                return $urx * $fontSize / 1000;
            case BaseFont::BBOXURY:
                return $ury * $fontSize / 1000;
            case BaseFont::AWT_LEADING:
                return 0;
            case BaseFont::AWT_MAXADVANCE:
                return ($urx - $llx) * $fontSize / 1000;
        }
        return 0;
    }

    /** Gets the full name of the font. If it is a True Type font
    * each array element will have {Platform ID, Platform Encoding ID,
    * Language ID, font name}. The interpretation of this values can be
    * found in the Open Type specification, chapter 2, in the 'name' table.<br>
    * For the other fonts the array has a single element with {"", "", "",
    * font name}.
    * @return the full name of the font
    *
    */
    public function getFullFontName() {
        return NULL;
    }

    /** Gets the kerning between two Unicode chars.
    * @param char1 the first char
    * @param char2 the second char
    * @return the kerning to be applied
    *
    */
    public function getKerning($char1, $char2) {
        return 0;
    }


    /** Gets the postscript font name.
    * @return the postscript font name
    *
    */
    public function getPostscriptFontName() {
        return $fontName;
    }

    /** Gets the width from the font according to the Unicode char <CODE>c</CODE>
    * or the <CODE>name</CODE>. If the <CODE>name</CODE> is null it's a symbolic font.
    * @param c the unicode char
    * @param name the glyph name
    * @return the width of the char
    *
    */
    function getRawWidth($c, $name) {
        return 0;
    }

    /** Checks if the font has any kerning pairs.
    * @return <CODE>true</CODE> if the font has any kerning pairs
    *
    */
    public function hasKernPairs() {
        return FALSE;
    }

    /** Outputs to the writer the font dictionaries and streams.
    * @param writer the writer for this document
    * @param ref the font indirect reference
    * @param params several parameters that depend on the font type
    * @throws IOException on error
    * @throws DocumentException error in generating the object
    *
    */
    function writeFont(PdfWriter $writer, PdfIndirectReference $ref, array $params) {
    }

    public function getWidth($text) {
        if ($cjkMirror != NULL)
            return $cjkMirror->getWidth($text);
        else
            return parent::getWidth($text);
    }

    function convertToBytes($text) {
        if ($cjkMirror != NULL)
            return PdfEncodings::convertToBytes($text, CJKFont::CJK_ENCODING);
        else {
            //char cc[] = text.toCharArray();
            $b = itextphp_bytes_create(strlen($text));
            for ($k = 0; $k < strlen($text); ++$k)
                itextphp_bytes_update($b, $k, uni2byte[ord($text[$k])]);
            return $b;
        }
    }

    function getIndirectReference() {
        return $refFont;
    }

    public function charExists($c) {
        if ($cjkMirror != NULL)
            return $cjkMirror->charExists($c);
        else
            return parent::charExists($c);
    }

    /**
    * Sets the font name that will appear in the pdf font dictionary.
    * It does nothing in this case as the font is already in the document.
    * @param name the new font name
    */
    public function setPostscriptFontName($name) {
    }

    public function setKerning($char1, $char2, $kern) {
        return FALSE;
    }

    public function getCharBBox($c) {
        return NULL;
    }
    
    protected function getRawCharBBox($c, $name) {
        return NULL;
    }

}

?>