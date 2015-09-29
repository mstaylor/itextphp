<?PHP
/*
 * $Id: FontDetails.php,v 1.2 2005/10/18 20:14:29 mstaylor Exp $
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


require_once("PdfIndirectReference.php");
require_once("PdfName.php");
require_once("BaseFont.php");
require_once("TrueTypeFontUnicode.php");
require_once("CJKFont.php");
require_once("PdfEncodings.php");
require_once("../../exceptions/UnsupportedEncodingException.php");
require_once("PdfWriter.php");



/** Each font in the document will have an instance of this class
* where the characters used will be represented.
*
* @author  Paulo Soares (psoares@consiste.pt)
*/

class FontDetails 
{

    /** The indirect reference to this font
    */
    $indirectReference = NULL;
    /** The font name that appears in the document body stream
    */
    $fontName = NULL;
    /** The font
    */
    $baseFont = NULL;
    /** The font if its an instance of <CODE>TrueTypeFontUnicode</CODE>
    */
    $ttu = NULL;

    $cjkFont = NULL;
    /** The array used with single byte encodings
    */
    $shortTag = NULL;
    /** The map used with double byte encodings. The key is Integer(glyph) and the
    * value is int[]{glyph, width, Unicode code}
    */
    $longTag = array();

    $cjkTag = array();
    /** The font type
    */
    $fontType = 0;
    /** <CODE>true</CODE> if the font is symbolic
    */
    $symbolic = FALSE;
    /** Indicates if all the glyphs and widths for that particular
    * encoding should be included in the document.
    */
    protected $subset = TRUE;

    /** Each font used in a document has an instance of this class.
    * This class stores the characters used in the document and other
    * specifics unique to the current working document.
    * @param fontName the font name
    * @param indirectReference the indirect reference to the font
    * @param baseFont the <CODE>BaseFont</CODE>
    */
    public function __construct(PdfName $fontName, PdfIndirectReference $indirectReference, BaseFont $baseFont) {
        $this->fontName = $fontName;
        $this->indirectReference = $indirectReference;
        $this->baseFont = $baseFont;
        $fontType = $baseFont->getFontType();
        switch (fontType) {
            case BaseFont::FONT_TYPE_T1:
            case BaseFont::FONT_TYPE_TT:
                $shortTag = itextphp_bytes_create(256);
                break;
            case BaseFont::FONT_TYPE_CJK:
                $cjkTag = array();
                $cjkFont = $baseFont;
                break;
            case BaseFont::FONT_TYPE_TTUNI:
                $longTag = array();
                $ttu = $baseFont;
                $symbolic = $baseFont->isFontSpecific();
                break;
        }
    }

    /** Gets the indirect reference to this font.
    * @return the indirect reference to this font
    */
    function getIndirectReference() {
        return $indirectReference;
    }

    /** Gets the font name as it appears in the document body.
    * @return the font name
    */
    function getFontName() {
        return $fontName;
    }

    /** Gets the <CODE>BaseFont</CODE> of this font.
    * @return the <CODE>BaseFont</CODE> of this font
    */
    function getBaseFont() {
        return $baseFont;
    }


    /** Converts the text into bytes to be placed in the document.
    * The conversion is done according to the font and the encoding and the characters
    * used are stored.
    * @param text the text to convert
    * @return the conversion
    */
    function convertToBytes($text) {
        if (is_resource($text) == TRUE)
            $text = itextphp_string_toPHPString($text);
        $b = NULL;
        switch ($fontType) {
            case BaseFont::FONT_TYPE_T1:
            case BaseFont::FONT_TYPE_TT: {
                $b = $baseFont->convertToBytes($text);
                $len = itextphp_bytes_getSize($b);
                for ($k = 0; $k < $len; ++$k)
                    itextphp_bytes_write($shortTag, itextphp_bytes_getIntValue($b, $k) & 0xff, itextphp_bytes_createfromInt(1), 0);
                break;
            }
            case BaseFont::FONT_TYPE_CJK: {
                $len = strlen($text);
                for ($k = 0; $k < $len; ++$k)
                    $cjkTag[$cjkFont->getCidCode($text[$k])] = 0;
                $b = $baseFont->convertToBytes($text);
                break;
            }
            case BaseFont::FONT_TYPE_DOCUMENT: {
                $b = $baseFont->convertToBytes($text);
                break;
            }
            case BaseFont::FONT_TYPE_TTUNI: {
                try {
                    $len = strlen($text);
                    $metrics = NULL;
                    $glyph = "";
                    $i = 0;
                    if ($symbolic == TRUE) {
                        $b = PdfEncodings::convertToBytes(text, "symboltt");
                        $len = itextphp_bytes_getSize($b);
                        for ($k = 0; $k < $len; ++$k) {
                            $metrics = $ttu->getMetricsTT(itextphp_bytes_getIntValue($b, $k) & 0xff);
                            if ($metrics == NULL)
                                continue;
                            $longTag[$metrics[0]] = array($metrics[0], $metrics[1], $ttu->getUnicodeDifferences(itextphp_bytes_getIntValue($b, $k) & 0xff));
                            $glyph[$i++] = chr($metrics[0]);
                        }
                    }
                    else {
                        for ($k = 0; $k < $len; ++$k) {
                            $c = $text[$k];
                            $metrics = $ttu->getMetricsTT(ord($c));
                            if ($metrics == NULL)
                                continue;
                            $m0 = $metrics[0];
                            $gl = $m0;
                            if (array_key_exists($gl, $longTag) == FALSE)
                                $longTag[$gl] = array($m0, $metrics[1], ord($c));
                            $glyph[$i++] = chr($m0);
                        }
                    }
                    $s = "";
                    for ($k = 0; $k < $i; $k++)
                    {
                        $s .= chr(glyph[$k]);
                    }

                    $b = itextphp_bytes_getBytesBasedonEncoding($s, CJKFont::CJK_ENCODING);
                }
                catch (UnsupportedEncodingException $e) {
                    throw new Exception($e);
                }
                break;
            }
        }
        return $b;
    }

    /** Writes the font definition to the document.
    * @param writer the <CODE>PdfWriter</CODE> of this document
    */
    function writeFont(PdfWriter $writer) {
        try {
            switch ($fontType) {
                case BaseFont::FONT_TYPE_T1:
                case BaseFont::FONT_TYPE_TT: {
                    $firstChar = 0;
                    $lastChar = 0;
                    if ($subset == TRUE) {
                        for ($firstChar = 0; $firstChar < 256; ++$firstChar) {
                            if (itextphp_bytes_getIntValue($shortTag, $firstChar) != 0)
                                break;
                        }
                        for ($lastChar = 255; $lastChar >= $firstChar; --$lastChar) {
                            if (itextphp_bytes_getIntValue($shortTag, $lastChar) != 0)
                                break;
                        }
                        if ($firstChar > 255) {
                            $firstChar = 255;
                            $lastChar = 255;
                        }
                    }
                    else {
                        for ($k = 0; $k < itextphp_bytes_getSize($shortTag); ++$k)
                            itextphp_bytes_write($shortTag, $k,  itextphp_bytes_createfromInt(1), 0);
                        $firstChar = 0;
                        $lastChar = itextphp_bytes_getSize($shortTag) - 1;
                    }
                    $baseFont->writeFont($writer, $indirectReference, array($firstChar, $lastChar, $shortTag));
                    break;
                }
                case BaseFont::FONT_TYPE_CJK:
                    $baseFont->writeFont($writer, $indirectReference, array($cjkTag));
                    break;
                case BaseFont::FONT_TYPE_TTUNI:
                    baseFont.writeFont($writer, $indirectReference, array($longTag));
                    break;
            }
        }
        catch(Exception $e) {
            throw new Exception($e);
        }
    }

    /** Indicates if all the glyphs and widths for that particular
    * encoding should be included in the document.
    * @return <CODE>false</CODE> to include all the glyphs and widths.
    */
    public function isSubset() {
        return $subset;
    }

    /** Indicates if all the glyphs and widths for that particular
    * encoding should be included in the document. Set to <CODE>false</CODE>
    * to include all.
    * @param subset new value of property subset
    */
    public function setSubset($subset) {
        $this->subset = $subset;
    }
}

?>