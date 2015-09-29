<?PHP
/*
 * $Id: BaseFont.php,v 1.2 2005/11/17 21:47:04 mstaylor Exp $
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
require_once("PdfName.php");
require_once("PdfStream.php");
require_once("PdfNumber.php");
require_once("DocumentFont.php");
require_once("CJKFont.php");
require_once("Type1Font.php");
require_once("TrueTypeFontUnicode.php");
require_once("TrueTypeFont.php");
require_once("PdfEncodings.php");
require_once("../DocumentException.php");
require_once("GlyphList.php");
require_once("EnumerateTTC.phps");
require_once("PdfObject.php");
require_once("PdfDictionary.php");
require_once("PdfReader.php");
require_once("../../util/StringHelpers.php");


abstract class BaseFont
{
    /** This is a possible value of a base 14 type 1 font */
    const COURIER = "Courier";

    /** This is a possible value of a base 14 type 1 font */
    const COURIER_BOLD = "Courier-Bold";

    /** This is a possible value of a base 14 type 1 font */
    const COURIER_OBLIQUE = "Courier-Oblique";

    /** This is a possible value of a base 14 type 1 font */
    const COURIER_BOLDOBLIQUE = "Courier-BoldOblique";

    /** This is a possible value of a base 14 type 1 font */
    const HELVETICA = "Helvetica";

    /** This is a possible value of a base 14 type 1 font */
    const HELVETICA_BOLD = "Helvetica-Bold";

    /** This is a possible value of a base 14 type 1 font */
    const HELVETICA_OBLIQUE = "Helvetica-Oblique";

    /** This is a possible value of a base 14 type 1 font */
    const HELVETICA_BOLDOBLIQUE = "Helvetica-BoldOblique";

    /** This is a possible value of a base 14 type 1 font */
    const SYMBOL = "Symbol";

    /** This is a possible value of a base 14 type 1 font */
    const TIMES_ROMAN = "Times-Roman";

    /** This is a possible value of a base 14 type 1 font */
    const TIMES_BOLD = "Times-Bold";

    /** This is a possible value of a base 14 type 1 font */
    const TIMES_ITALIC = "Times-Italic";

    /** This is a possible value of a base 14 type 1 font */
    const TIMES_BOLDITALIC = "Times-BoldItalic";

    /** This is a possible value of a base 14 type 1 font */
    const ZAPFDINGBATS = "ZapfDingbats";

    /** The maximum height above the baseline reached by glyphs in this
    * font, excluding the height of glyphs for accented characters.
    */
    const ASCENT = 1;
    /** The y coordinate of the top of flat capital letters, measured from
    * the baseline.
    */
    const CAPHEIGHT = 2;
    /** The maximum depth below the baseline reached by glyphs in this
    * font. The value is a negative number.
    */
    const DESCENT = 3;
    /** The angle, expressed in degrees counterclockwise from the vertical,
    * of the dominant vertical strokes of the font. The value is
    * negative for fonts that slope to the right, as almost all italic fonts do.
    */
    const ITALICANGLE = 4;
    /** The lower left x glyph coordinate.
    */
    const BBOXLLX = 5;
    /** The lower left y glyph coordinate.
    */
    const BBOXLLY = 6;
    /** The upper right x glyph coordinate.
    */
    const BBOXURX = 7;
    /** The upper right y glyph coordinate.
    */
    const BBOXURY = 8;

    /** java.awt.Font property */
    const AWT_ASCENT = 9;
    /** java.awt.Font property */
    const AWT_DESCENT = 10;
    /** java.awt.Font property */
    const AWT_LEADING = 11;
    /** java.awt.Font property */
    const AWT_MAXADVANCE = 12;

    /** The font is Type 1.
    */
    const FONT_TYPE_T1 = 0;
    /** The font is True Type with a standard encoding.
    */
    const FONT_TYPE_TT = 1;
    /** The font is CJK.
    */
    const FONT_TYPE_CJK = 2;
    /** The font is True Type with a Unicode encoding.
    */
    const FONT_TYPE_TTUNI = 3;
    /** A font already inside the document.
    */
    const FONT_TYPE_DOCUMENT = 4;
    /** The Unicode encoding with horizontal writing.
    */
    const IDENTITY_H = "Identity-H";
    /** The Unicode encoding with vertical writing.
    */
    const IDENTITY_V = "Identity-V";

    /** A possible encoding. */    
    const CP1250 = "Cp1250";

    /** A possible encoding. */    
    const CP1252 = "Cp1252";

    /** A possible encoding. */    
    const CP1257 = "Cp1257";

    /** A possible encoding. */    
    const WINANSI = "Cp1252";

    /** A possible encoding. */    
    const MACROMAN = "MacRoman";

    /** if the font has to be embedded */
    const EMBEDDED = TRUE;

    /** if the font doesn't have to be embedded */
    const NOT_EMBEDDED = FALSE;

    /** if the font has to be cached */
    const CACHED = TRUE;

    /** if the font doesn't have to be cached */
    const NOT_CACHED = FALSE;

    /** The path to the font resources. */    
    const RESOURCE_PATH = "com/lowagie/text/pdf/fonts/";

    /** The fake CID code that represents a newline. */    
    const CID_NEWLINE = '\u7fff';

    /** The font type.
    */
    public $fontType;

    /** a not defined character in a custom PDF encoding */
    const notdef = ".notdef";

    /** table of characters widths for this encoding */
    protected $widths = array();

    /** encoding names */
    protected $differences = array();

    /** same as differences but with the unicode codes */
    protected $unicodeDifferences = array();

    protected $charBBoxes = array(256,0);

    /** encoding used with this font */
    protected $encoding;

    /** true if the font is to be embedded in the PDF */
    protected $embedded;

    /**
    * true if the font must use it's built in encoding. In that case the
    * <CODE>encoding</CODE> is only used to map a char to the position inside
    * the font, not to the expected char name.
    */
    protected $fontSpecific = TRUE;

    /** cache for the fonts already used. */
    protected static $fontCache = array();

    /** list of the 14 built in fonts. */
    protected static $BuiltinFonts14 = array();

    /** Forces the output of the width array. Only matters for the 14
    * built-in fonts.
    */
    protected $forceWidthsOutput = FALSE;

    /** Converts <CODE>char</CODE> directly to <CODE>byte</CODE>
    * by casting.
    */
    protected $directTextToByte = FALSE;

    /** Indicates if all the glyphs and widths for that particular
    * encoding should be included in the document.
    */
    protected $subset = TRUE;

    protected $fastWinansi = FALSE;

    private function initializeStatics()
    {
        BaseFont::$BuiltinFonts14[COURIER] = PdfName::$COURIER;
        BaseFont::$BuiltinFonts14[COURIER_BOLD] = PdfName::$COURIER_BOLD;
        BaseFont::$BuiltinFonts14[COURIER_BOLDOBLIQUE] = PdfName::$COURIER_BOLDOBLIQUE;
        BaseFont::$BuiltinFonts14[COURIER_OBLIQUE] = PdfName::$COURIER_OBLIQUE;
        BaseFont::$BuiltinFonts14[HELVETICA] = PdfName::$HELVETICA;
        BaseFont::$BuiltinFonts14[HELVETICA_BOLD] = PdfName::$HELVETICA_BOLD;
        BaseFont::$BuiltinFonts14[HELVETICA_BOLDOBLIQUE] = PdfName::$HELVETICA_BOLDOBLIQUE;
        BaseFont::$BuiltinFonts14[HELVETICA_OBLIQUE] = PdfName::$HELVETICA_OBLIQUE;
        BaseFont::$BuiltinFonts14[SYMBOL] = PdfName::$SYMBOL;
        BaseFont::$BuiltinFonts14[TIMES_ROMAN] = PdfName::$TIMES_ROMAN;
        BaseFont::$BuiltinFonts14[TIMES_BOLD] = PdfName::$TIMES_BOLD;
        BaseFont::$BuiltinFonts14[TIMES_BOLDITALIC] = PdfName::$TIMES_BOLDITALIC;
        BaseFont::$BuiltinFonts14[TIMES_ITALIC] = PdfName::$TIMES_ITALIC;
        BaseFont::$BuiltinFonts14[ZAPFDINGBATS] = PdfName::$ZAPFDINGBATS;
    }

    /**
    *Creates new BaseFont
    */
    protected function __construct()
    {
    }

    public static function createFont()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                return createFont1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                return createFont3args($arg1, $arg2, $arg3);
                break;
            }
            case 6:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                return createFont6args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
            }

        }
    }

    /**
    * Creates a font based on an existing document font. The created font font may not
    * behave as expected, depending on the encoding or subset.
    * @param fontRef the reference to the document font
    * @return the font
    */
    private function createFont1arg($fontRef)
    {
       return new DocumentFont($fontRef);
    }

    /** Creates a new font. This font can be one of the 14 built in types,
    * a Type1 font referred by an AFM file, a TrueType font (simple or collection) or a CJK font from the
    * Adobe Asian Font Pack. TrueType fonts and CJK fonts can have an optional style modifier
    * appended to the name. These modifiers are: Bold, Italic and BoldItalic. An
    * example would be "STSong-Light,Bold". Note that this modifiers do not work if
    * the font is embedded. Fonts in TrueType collections are addressed by index such as "msgothic.ttc,1".
    * This would get the second font (indexes start at 0), in this case "MS PGothic".
    * <P>
    * The fonts are cached and if they already exist they are extracted from the cache,
    * not parsed again.
    * <P>
    * This method calls:<br>
    * <PRE>
    * createFont(name, encoding, embedded, true, null, null);
    * </PRE>
    * @param name the name of the font or it's location on file
    * @param encoding the encoding to be applied to this font
    * @param embedded true if the font is to be embedded in the PDF
    * @return returns a new font. This font may come from the cache
    * @throws DocumentException the font is invalid
    * @throws IOException the font file could not be read
    */
    private function createFont3args($name, $encoding, $embedded)
    {
        return createFont6args($name, $encoding, $embedded, TRUE, NULL, NULL);
    }


    /** Creates a new font. This font can be one of the 14 built in types,
    * a Type1 font referred by an AFM file, a TrueType font (simple or collection) or a CJK font from the
    * Adobe Asian Font Pack. TrueType fonts and CJK fonts can have an optional style modifier
    * appended to the name. These modifiers are: Bold, Italic and BoldItalic. An
    * example would be "STSong-Light,Bold". Note that this modifiers do not work if
    * the font is embedded. Fonts in TrueType collections are addressed by index such as "msgothic.ttc,1".
    * This would get the second font (indexes start at 0), in this case "MS PGothic".
    * <P>
    * The fonts may or may not be cached depending on the flag <CODE>cached</CODE>.
    * If the <CODE>byte</CODE> arrays are present the font will be
    * read from them instead of the name. The name is still required to identify
    * the font type.
    * @param name the name of the font or it's location on file
    * @param encoding the encoding to be applied to this font
    * @param embedded true if the font is to be embedded in the PDF
    * @param cached true if the font comes from the cache or is added to
    * the cache if new, false if the font is always created new
    * @param ttfAfm the true type font or the afm in a byte array
    * @param pfb the pfb in a byte array
    * @return returns a new font. This font may come from the cache but only if cached
    * is true, otherwise it will always be created new
    * @throws DocumentException the font is invalid
    * @throws IOException the font file could not be read
    */
    private function createFont6args($name, $encoding, $embedded, $cached, $ttfAfm, $pfb)
    {
        $nameBase = getBaseName($name);
        $encoding = normalizeEncoding($encoding);
        $isBuiltinFonts14 = array_key_exists($name, $BuiltinFonts14);
        $isCJKFont = $isBuiltinFonts14 ? FALSE : CJKFont::isCJKFont($nameBase, $encoding);
        if ($isBuiltinFonts14 || $isCJKFont)
            $embedded = FALSE;
        else if (strcmp($encoding, IDENTITY_H) == 0 || strcmp($encoding, IDENTITY_V) == 0)
            $embedded = TRUE;
        $fontFound = NULL;
        $fontBuilt = NULL;
        $key = $name . "\n" . $encoding . "\n" . $embedded;
        if ($cached) {
            //synchronized (fontCache) {
                $fontFound = $fontCache[$key];
            //}
            if ($fontFound != NULL)
                return $fontFound;
        }
        if ($isBuiltinFonts14 || endsWith(strtolower($name), ".afm") == TRUE || endsWith(strtolower($name), ".pfm") == TRUE) {
            $fontBuilt = new Type1Font($name, $encoding, $embedded, $ttfAfm, $pfb);
            if (strcmp($encoding, CP1252) == 0)
            {
                $fontBuilt->fastWinansi = TRUE;
            }
            else
            {
                $fontBuilt->fastWinansi = FALSE;
            }

        }
        else if (endsWith(strtolower($nameBase), ".ttf") == TRUE || endsWith(strtolower($nameBase), ".otf") == TRUE || stripos(strtolower($nameBase), ".ttc,") > 0) {
            if (strcmp($encoding, IDENTITY_H) == 0 || strcmp($encoding, IDENTITY_V) == 0)
                $fontBuilt = new TrueTypeFontUnicode($name, $encoding, $embedded, $ttfAfm);
            else {
                $fontBuilt = new TrueTypeFont($name, $encoding, $embedded, $ttfAfm);
            if (strcmp($encoding, CP1252) == 0)
            {
                $fontBuilt->fastWinansi = TRUE;
            }
            else
            {
                $fontBuilt->fastWinansi = FALSE;
            }
            }
        }
        else if ($isCJKFont == TRUE)
            $fontBuilt = new CJKFont($name, $encoding, $embedded);
        else
            throw new DocumentException("Font '" . $name . "' with '" . $encoding . "' is not recognized.");
        if ($cached) {
            //synchronized (fontCache) {
                $fontFound = $fontCache[$key];
                if ($fontFound != NULL)
                    return $fontFound;
                $fontCache[$key] = $fontBuilt;
            //}
        }
        return fontBuilt;
    }


    /**
    * Gets the name without the modifiers Bold, Italic or BoldItalic.
    * @param name the full name of the font
    * @return the name without the modifiers Bold, Italic or BoldItalic
    */
    protected static function getBaseName($name) {
        if (endsWith($name, ",Bold") == TRUE)
            return substr($name, 0, strlen($name) - 5);
        else if (endsWith($name, ",Italic") == TRUE)
            return substr($name, 0, strlen($name) - 7);
        else if (endsWith($name, ",BoldItalic") == TRUE)
            return substr($name, 0, strlen($name) - 11);
        else
            return $name;
    }


    /**
    * Normalize the encoding names. "winansi" is changed to "Cp1252" and
    * "macroman" is changed to "MacRoman".
    * @param enc the encoding to be normalized
    * @return the normalized encoding
    */
    protected static function normalizeEncoding($enc) {
        if (strcmp($enc, "winansi") == 0 || strcmp($enc, "") == 0)
            return CP1252;
        else if (strcmp($enc, "macroman") == 0)
            return MACROMAN;
        else
            return $enc;
    }


    /**
    * Creates the <CODE>widths</CODE> and the <CODE>differences</CODE> arrays
    */
    protected function createEncoding() {
        if ($fontSpecific) {
            for ($k = 0; $k < 256; ++$k) {
                $widths[$k] = getRawWidth($k, NULL);
                $charBBoxes[$k] = getRawCharBBox($k, NULL);
            }
        }
        else {
            $s;
            $name;
            char c;
            $b = itextphp_bytes_create(1);
            for ($k = 0; $k < 256; ++$k) {
                itextphp_bytes_update($b, 0, $k);
                $s = PdfEncodings::convertToString($b, $encoding);
                if (itextphp_StringLength($s) > 0) {
                    $c = itextphp_char_getInt($s, 0);
                }
                else {
                    $c = itextphp_char_create('?');
                }
                $name = GlyphList::unicodeToName($c);
                if ($name == NULL)
                    $name = $notdef;
                $differences[$k] = $name;
                $unicodeDifferences[$k] = $c;
                $widths[$k] = getRawWidth($c, $name);
                $charBBoxes[$k] = getRawCharBBox($c, $name);
            }
        }
    }


    /**
    * Gets the width from the font according to the Unicode char <CODE>c</CODE>
    * or the <CODE>name</CODE>. If the <CODE>name</CODE> is null it's a symbolic font.
    * @param c the unicode char
    * @param name the glyph name
    * @return the width of the char
    */
    //note c should be a resource!
    abstract function getRawWidth($c, $name);

    /**
    * Gets the kerning between two Unicode chars.
    * @param char1 the first char
    * @param char2 the second char
    * @return the kerning to be applied in normalized 1000 units
    */
    //note both params should be a resource
    public abstract function getKerning($char1, $char2);

    /**
    * Sets the kerning between two Unicode chars.
    * @param char1 the first char
    * @param char2 the second char
    * @param kern the kerning to apply in normalized 1000 units
    * @return <code>true</code> if the kerning was applied, <code>false</code> otherwise
    */
    //note first two parameters should be a resource
    public abstract function setKerning($char1, $char2, $kern);

    public function getWidth()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (strcmp(gettype($arg1),"resource") == 0)
                {
                    if (strcmp(get_resource_type($arg1),"aChar") == 0)
                    {
                        return getWidth1argResource($arg1);
                    }
                    else
                    {
                        return getWidth1argStringResource($arg1);
                    }
                }
                else
                {
                    return getWidth1argString($arg1);
                }
                break;
            }
        }
    }


    /**
    * Gets the width of a <CODE>char</CODE> in normalized 1000 units.
    * @param char1 the unicode <CODE>char</CODE> to get the width of
    * @return the width in normalized 1000 units
    */
    private function getWidth1argResource($char1)
    {
        if ($fastWinansi == TRUE) {
            $ichar1 = itextphp_char_getIntRep($char1, 0)
            if ($ichar1 < 128 || ($ichar1 >= 160 && $ichar1 <= 255))
                return widths[$ichar1];
            return widths[PdfEncodings::winansi[itextphp_getWinAnsiValue($char1)]];
        }
        return getWidth(itextphp_newStringfromChar($char1));

    }


    /**
    * Gets the width of a <CODE>String</CODE> in normalized 1000 units.
    * @param text the <CODE>String</CODE> to get the witdth of
    * @return the width in normalized 1000 units
    */

    private function getWidth1argStringResource($text)
    {
        $total = 0;
        if ($fastWinansi == TRUE) {
            $len = itextphp_StringLength($text);
            for ($k = 0; $k < $len; ++$k) {
                $char1 = itextphp_char_getInt($text, $k);
                $ichar1 = itextphp_char_getIntRep($char1, 0)
                if ($ichar1 < 128 || ($ichar1 >= 160 && $ichar1 <= 255))
                    $total = $total + $widths[$ichar1];
                else
                    $total = $total +  $widths[PdfEncodings::winansi[itextphp_getWinAnsiValue($char1)]];
            }
            return $total;
        }
        else {
            $mbytes = convertToBytes($text);
            for ($k = 0; $k < itextphp_bytes_getSize($mbytes); ++$k)
                $total = $total + $widths[itextphp_bytes_getIntValue($mbytes, $k, 0xff)];
        }
        return $total;
    }

    //non-resource version
    private function getWidth1argString($text)
    {
          $total = 0;
        if ($fastWinansi == TRUE) {
            $len = strlen($text);
            for ($k = 0; $k < $len; ++$k) {
                $char1 = itextphp_char_getInt(itextphp_newString($text, $len, 1), $k);
                $ichar1 = itextphp_char_getIntRep($char1, 0)
                if ($ichar1 < 128 || ($ichar1 >= 160 && $ichar1 <= 255))
                    $total = $total + $widths[$ichar1];
                else
                    $total = $total +  $widths[PdfEncodings::winansi[itextphp_getWinAnsiValue($char1)]];
            }
            return $total;
        }
        else {
            $mbytes = convertToBytes($text);
            for ($k = 0; $k < itextphp_bytes_getSize($mbytes); ++$k)
                $total = $total + $widths[itextphp_bytes_getIntValue($mbytes, $k, 0xff)];
        }
        return $total;
    }


    /**
    * Gets the descent of a <CODE>String</CODE> in normalized 1000 units. The descent will always be
    * less than or equal to zero even if all the characters have an higher descent.
    * @param text the <CODE>String</CODE> to get the descent of
    * @return the dexcent in normalized 1000 units
    */
    public function getDescent($text) {
        $min = 0;
        $chars = $text;
        for ($k = 0; $k < strlen($chars); ++$k) {
            $bbox = getCharBBox($chars[$k]);
            if ($bbox != NULL && $bbox[1] < $min)
                $min = $bbox[1];
        }
        return $min;
    }

    /**
    * Gets the ascent of a <CODE>String</CODE> in normalized 1000 units. The ascent will always be
    * greater than or equal to zero even if all the characters have a lower ascent.
    * @param text the <CODE>String</CODE> to get the ascent of
    * @return the ascent in normalized 1000 units
    */
    public function getAscent($text) {
        $max = 0;
        $chars = $text;
        for ($k = 0; $k < strlen($chars); ++$k) {
            $bbox = getCharBBox($chars[$k]);
            if ($bbox != NULL && $bbox[3] > $max)
                $max = $bbox[3];
        }
        return $max;
    }

    /**
    * Gets the descent of a <CODE>String</CODE> in points. The descent will always be
    * less than or equal to zero even if all the characters have an higher descent.
    * @param text the <CODE>String</CODE> to get the descent of
    * @param fontSize the size of the font
    * @return the dexcent in points
    */
    public function getDescentPoint($text, $fontSize)
    {
        return getDescent($text) * 0.001 * $fontSize;
    }

    /**
    * Gets the ascent of a <CODE>String</CODE> in points. The ascent will always be
    * greater than or equal to zero even if all the characters have a lower ascent.
    * @param text the <CODE>String</CODE> to get the ascent of
    * @param fontSize the size of the font
    * @return the ascent in points
    */
    public function getAscentPoint($text, $fontSize)
    {
        return getAscent($text) * 0.001 * $fontSize;
    }



    /**
    * Gets the width of a <CODE>String</CODE> in points taking kerning
    * into account.
    * @param text the <CODE>String</CODE> to get the witdth of
    * @param fontSize the font size
    * @return the width in points
    */
    public function getWidthPointKerned($text, $fontSize) {
        $size = $getWidth($text) * 0.001 * $fontSize;
        if (hasKernPairs() == FALSE)
            return $size;
        $len = strlen($text) - 1;
        $kern = 0;
        $c = $text;
        for ($k = 0; $k < $len; ++$k) {
            $kern = $kern + getKerning($c[$k], $c[$k + 1]);
        }
        return $size + $kern * 0.001 * $fontSize;
    }

    /**
    * Gets the width of a <CODE>String</CODE> in points.
    * @param text the <CODE>String</CODE> to get the witdth of
    * @param fontSize the font size
    * @return the width in points
    */
    public function getWidthPoint($text, $fontSize) {
        return getWidth($text) * 0.001 * $fontSize;
    }

    /**
    * Gets the width of a <CODE>char</CODE> in points.
    * @param char1 the <CODE>char</CODE> to get the witdth of
    * @param fontSize the font size
    * @return the width in points
    */
    public function getWidthPoint($char1, $fontSize) {
        return getWidth($char1) * 0.001 * $fontSize;
    }

    /**
    * Converts a <CODE>String</CODE> to a </CODE>byte</CODE> array according
    * to the font's encoding.
    * @param text the <CODE>String</CODE> to be converted
    * @return an array of <CODE>byte</CODE> representing the conversion according to the font's encoding
    */
    function convertToBytes($text) {
        if ($directTextToByte)
            return PdfEncodings::convertToBytes($text, NULL);
        return PdfEncodings::convertToBytes($text, $encoding);
    }

    /** Outputs to the writer the font dictionaries and streams.
    * @param writer the writer for this document
    * @param ref the font indirect reference
    * @param params several parameters that depend on the font type
    * @throws IOException on error
    * @throws DocumentException error in generating the object
    */
    abstract function writeFont($writer, $ref, $params);

    /** Gets the encoding used to convert <CODE>String</CODE> into <CODE>byte[]</CODE>.
    * @return the encoding name
    */
    public function getEncoding() {
        return $encoding;
    }


    /** Gets the font parameter identified by <CODE>key</CODE>. Valid values
    * for <CODE>key</CODE> are <CODE>ASCENT</CODE>, <CODE>AWT_ASCENT</CODE>, <CODE>CAPHEIGHT</CODE>, 
    * <CODE>DESCENT</CODE>, <CODE>AWT_DESCENT</CODE>,
    * <CODE>ITALICANGLE</CODE>, <CODE>BBOXLLX</CODE>, <CODE>BBOXLLY</CODE>, <CODE>BBOXURX</CODE>
    * and <CODE>BBOXURY</CODE>.
    * @param key the parameter to be extracted
    * @param fontSize the font size in points
    * @return the parameter in points
    */
    public abstract function getFontDescriptor($key, $fontSize);


    /** Gets the font type. The font types can be: FONT_TYPE_T1,
    * FONT_TYPE_TT, FONT_TYPE_CJK and FONT_TYPE_TTUNI.
    * @return the font type
    */
    public function getFontType() {
        return $fontType;
    }

    /** Gets the embedded flag.
    * @return <CODE>true</CODE> if the font is embedded.
    */
    public function isEmbedded() {
        return $embedded;
    }

    /** Gets the symbolic flag of the font.
    * @return <CODE>true</CODE> if the font is symbolic
    */
    public function isFontSpecific() {
        return $fontSpecific;
    }


    /** Creates a unique subset prefix to be added to the font name when the font is embedded and subset.
    * @return the subset prefix
    */
    public static function createSubsetPrefix() {
        $s = "";
        for ($k = 0; $k < 6; ++$k)
            $s . (rand() * 26 . 'A');
        return $s . "+";
    }

    /** Gets the Unicode character corresponding to the byte output to the pdf stream.
    * @param index the byte index
    * @return the Unicode character
    */
    function getUnicodeDifferences($index) {
        return $unicodeDifferences[$index];
    }

    /** Gets the postscript font name.
    * @return the postscript font name
    */
    public abstract function getPostscriptFontName();

    /**
    * Sets the font name that will appear in the pdf font dictionary.
    * Use with care as it can easily make a font unreadable if not embedded.
    * @param name the new font name
    */
    public abstract function setPostscriptFontName($name);

    /** Gets the full name of the font. If it is a True Type font
    * each array element will have {Platform ID, Platform Encoding ID,
    * Language ID, font name}. The interpretation of this values can be
    * found in the Open Type specification, chapter 2, in the 'name' table.<br>
    * For the other fonts the array has a single element with {"", "", "",
    * font name}.
    * @return the full name of the font
    */
    public abstract function getFullFontName();

    /** Gets the full name of the font. If it is a True Type font
    * each array element will have {Platform ID, Platform Encoding ID,
    * Language ID, font name}. The interpretation of this values can be
    * found in the Open Type specification, chapter 2, in the 'name' table.<br>
    * For the other fonts the array has a single element with {"", "", "",
    * font name}.
    * @param name the name of the font
    * @param encoding the encoding of the font
    * @param ttfAfm the true type font or the afm in a byte array
    * @throws DocumentException on error
    * @throws IOException on error
    * @return the full name of the font
    */
    public static function getFullFontName($name, $encoding, $ttfAfm) {
        $nameBase = getBaseName($name);
        $fontBuilt = NULL;
        if (endsWith(strtolower(nameBase), ".ttf")== TRUE || endsWith(strtolower(nameBase), ".otf") == TRUE  || stripos(strtolower($nameBase), ".ttc,") > 0)
            $fontBuilt = new TrueTypeFont($name, CP1252, FALSE, $ttfAfm, TRUE);
        else
            $fontBuilt = createFont($name, $encoding, FALSE, FALSE, $ttfAfm, NULL);
        return $fontBuilt->getFullFontName();
    }

    /** Gets all the names from the font. Only the required tables are read.
    * @param name the name of the font
    * @param encoding the encoding of the font
    * @param ttfAfm the true type font or the afm in a byte array
    * @throws DocumentException on error
    * @throws IOException on error
    * @return an array of Object[] built with {getPostscriptFontName(), getFamilyFontName(), getFullFontName()}
    */    
    public static function getAllFontNames($name, $encoding, $ttfAfm) {
        $nameBase = getBaseName($name);
        $fontBuilt = NULL;
        if (endsWith(strtolower(nameBase), ".ttf")== TRUE || endsWith(strtolower(nameBase), ".otf") == TRUE  || stripos(strtolower($nameBase), ".ttc,") > 0)
            $fontBuilt = new TrueTypeFont($name, CP1252, FALSE, $ttfAfm, TRUE);
        else
            $fontBuilt = createFont($name, $encoding, FALSE, FALSE, $ttfAfm, NULL);
        return Array($fontBuilt->getPostscriptFontName(), $fontBuilt->getFamilyFontName(), $fontBuilt->getFullFontName());
    }

    /** Gets the family name of the font. If it is a True Type font
    * each array element will have {Platform ID, Platform Encoding ID,
    * Language ID, font name}. The interpretation of this values can be
    * found in the Open Type specification, chapter 2, in the 'name' table.<br>
    * For the other fonts the array has a single element with {"", "", "",
    * font name}.
    * @return the family name of the font
    */
    public abstract function getFamilyFontName();

    /** Gets the code pages supported by the font. This has only meaning
    * with True Type fonts.
    * @return the code pages supported by the font
    */
    public function getCodePagesSupported() {
        return Array("");
    }

    /** Enumerates the postscript font names present inside a
     * True Type Collection.
     * @param ttcFile the file name of the font
     * @throws DocumentException on error
     * @throws IOException on error
     * @return the postscript font names
     */
    public static function enumerateTTCNames($ttcFile) {
        return new EnumerateTTC($ttcFile)->getNames();
    }

    /** Enumerates the postscript font names present inside a
    * True Type Collection.
    * @param ttcArray the font as a <CODE>byte</CODE> array
    * @throws DocumentException on error
    * @throws IOException on error
    * @return the postscript font names
    */
    public static function enumerateTTCNames($ttcArray) {
        return new EnumerateTTC($ttcArray)->getNames();
    }

    /** Gets the font width array.
    * @return the font width array
    */
    public function getWidths() {
        return $widths;
    }

    /** Gets the array with the names of the characters.
    * @return the array with the names of the characters
    */
    public function getDifferences() {
        return $differences;
    }

    /** Gets the array with the unicode characters.
    * @return the array with the unicode characters
    */
    public function getUnicodeDifferences() {
        return $unicodeDifferences;
    }

    /** Gets the state of the property.
    * @return value of property forceWidthsOutput
    */
    public function isForceWidthsOutput() {
        return $forceWidthsOutput;
    }

    /** Set to <CODE>true</CODE> to force the generation of the
    * widths array.
    * @param forceWidthsOutput <CODE>true</CODE> to force the generation of the
    * widths array
    */
    public function setForceWidthsOutput($forceWidthsOutput) {
        $this->forceWidthsOutput = $forceWidthsOutput;
    }

    /** Gets the direct conversion of <CODE>char</CODE> to <CODE>byte</CODE>.
    * @return value of property directTextToByte.
    * @see #setDirectTextToByte(boolean directTextToByte)
    */
    public function isDirectTextToByte() {
        return $directTextToByte;
    }

    /** Sets the conversion of <CODE>char</CODE> directly to <CODE>byte</CODE>
    * by casting. This is a low level feature to put the bytes directly in
    * the content stream without passing through String.getBytes().
    * @param directTextToByte New value of property directTextToByte.
    */
    public function setDirectTextToByte($directTextToByte) {
        $this->directTextToByte = $directTextToByte;
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
    public void setSubset(boolean subset) {
        $this->subset = $subset;
    }

    /** Gets the font resources.
    * @param key the full name of the resource
    * @return the <CODE>InputStream</CODE> to get the resource or
    * <CODE>null</CODE> if not found
    */
    public static function getResourceStream($key) {
        return fopen("/" . $key, "r");
    }

    /** Gets the Unicode equivalent to a CID.
    * The (inexistent) CID <FF00> is translated as '\n'. 
    * It has only meaning with CJK fonts with Identity encoding.
    * @param c the CID code
    * @return the Unicode equivalent
    */
    public function getUnicodeEquivalent($c) {
        return $c;
    }

    /** Gets the CID code given an Unicode.
    * It has only meaning with CJK fonts.
    * @param c the Unicode
    * @return the CID equivalent
    */
    public function getCidCode($c) {
        return $c;
    }

    /** Checks if the font has any kerning pairs.
    * @return <CODE>true</CODE> if the font has any kerning pairs
    */    
    public abstract function hasKernPairs();

    /**
    * Checks if a character exists in this font.
    * @param c the character to check
    * @return <CODE>true</CODE> if the character has a glyph,
    * <CODE>false</CODE> otherwise
    */
    public function charExists($c) {
        $b = convertToBytes(itextphp_newStringfromChar($c));
        return itextphp_bytes_getSize($b) > 0;
    }

    /**
    * Sets the character advance.
    * @param c the character
    * @param advance the character advance normalized to 1000 units
    * @return <CODE>true</CODE> if the advance was set,
    * <CODE>false</CODE> otherwise
    */
    public function setCharAdvance($c, $advance) {
        $b = convertToBytes(itextphp_newStringfromChar($c));
        if (itextphp_bytes_getSize($b) == 0)
            return FALSE;
        $widths[itextphp_bytes_getIntValue($b, 0, 0xff)] = $advance;
        return TRUE;
    }

    private static function addFont($fontRef, $hits, $fonts) {
        $obj = PdfReader::getPdfObject($fontRef);
        if ($obj->isDictionary() == FALSE)
            return;
        $font = $obj;
        $subtype = $PdfReader->getPdfObject($font->get(PdfName::$SUBTYPE));
        if (strcmp(PdfName::$TYPE1, $subtype) != 0 && strcmp(PdfName::$TRUETYPE, $subtype) != 0)
            return;
        $name = PdfReader::getPdfObject($font->get(PdfName::$BASEFONT));
        array_push($fonts, Array(PdfName::decodeName($name->toString()), fontRef));
        $hits[$fontRef->getNumber()] = 1;
    }

    private static function recourseFonts($page, $hits, $fonts, $level) {
        ++$level;
        if ($level > 50) // in case we have an endless loop
            return;
        $resources = PdfReader::getPdfObject($page->get(PdfName::$RESOURCES));
        if ($resources == NULL)
            return;
        $font = PdfReader::getPdfObject($resources->get(PdfName::$FONT));
        if ($font != NULL) {
            $keys = $font->getKeys();
            foreach($keys as &$value)
            {
                $ft = $font->get($value);        
                if ($ft == NULL || $ft->isIndirect() == FALSE)
                    continue;
                $hit = $ft->getNumber();
                if (array_key_exists($hit, $hits) == TRUE)
                    continue;
                addFont($ft, $hits, $fonts);
            }
        }
        $xobj = PdfReader::getPdfObject($resources->get(PdfName::$XOBJECT));
        if ($xobj != NULL) {
            $keys = $xobj->getKeys();
            foreach($keys as &$value)
            {
                recourseFonts(PdfReader::getPdfObject($xobj->get($value), $hits, $fonts, $level);
            }

        }
    }

    /**
    * Gets a list of all document fonts. Each element of the <CODE>ArrayList</CODE>
    * contains a <CODE>Object[]{String,PRIndirectReference}</CODE> with the font name
    * and the indirect reference to it.
    * @param reader the document where the fonts are to be listed from
    * @return the list of fonts and references
    */
    public static function getDocumentFonts($reader) {
        $hits = array();
        $fonts = array();
        $npages = $reader->getNumberOfPages();
        for ($k = 1; $k <= $npages; ++$k)
            recourseFonts($reader->getPageN($k), $hits, $fonts, 1);
        return $fonts;
    }

    /**
    * Gets a list of the document fonts in a particular page. Each element of the <CODE>ArrayList</CODE>
    * contains a <CODE>Object[]{String,PRIndirectReference}</CODE> with the font name
    * and the indirect reference to it.
    * @param reader the document where the fonts are to be listed from
    * @param page the page to list the fonts from
    * @return the list of fonts and references
    */
    public static function getDocumentFonts($reader, $page) {
        $hits = array();
        $fonts = array();
        recourseFonts($reader->getPageN($page), $hits, $fonts, 1);
        return $fonts;
    }


    /**
    * Gets the smallest box enclosing the character contours. It will return
    * <CODE>null</CODE> if the font has not the information or the character has no
    * contours, as in the case of the space, for example. Characters with no contours may
    * also return [0,0,0,0].
    * @param c the character to get the contour bounding box from
    * @return an array of four floats with the bounding box in the format [llx,lly,urx,ury] or
    * <code>null</code>
    */
    public function getCharBBox($c) {
        $b = convertToBytes(itextphp_newStringfromChar($c));
        if (itextphp_bytes_getSize($b) == 0)
            return NULL;
        else
            return charBBoxes[itextphp_bytes_getIntValue($b, 0, 0xff)];
    }

    protected abstract function getRawCharBBox($c, $name);




/** Generates the PDF stream with the Type1 and Truetype fonts returning
* a PdfStream.
*/

class StreamFont extends PdfStream {

    public function __construct()
    {

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(2);
                if (strcmp(gettype($arg2),"string") == 0)
                {
                    construct2argsstring($arg1, $arg2);
                }
                else
                {
                    construct2args($arg1, $arg2);
                }
                break; 
            }

        }


    }

    /**
    * Generates the PDF stream for a font.
    * @param contents the content of a stream
    * @param subType the subtype of the font.
    * @throws DocumentException
    */
    private function construct2argsstring($contents, $subType)
    {
          try
          {
              $bytes = $contents;
              put(PdfName::$LENGTH, new PdfNumber(itextphp_bytes_getSize($bytes));
              if ($subType != NULL)
                  put(PdfName::$SUBTYPE, new PdfName($subType));
              flateCompress();
          }
          catch (Exception $e) 
          {
              throw new DocumentException($e);
          }
    }

    /** Generates the PDF stream with the Type1 and Truetype fonts returning
    * a PdfStream.
    * @param contents the content of the stream
    * @param lengths an array of int that describes the several lengths of each part of the font
    * @throws DocumentException error in the stream compression
    */

    private function construct2args($contents, $lengths)
    {
        try 
        {
            $bytes = $contents;
            put(PdfName::$LENGTH, new PdfNumber(itextphp_bytes_getSize($bytes));
            for ($k = 0; $k < count($lengths); ++$k) 
            {
                put(new PdfName("Length" + ($k + 1)), new PdfNumber($lengths[$k]));
            }
            flateCompress();
            }
            catch (Exception $e) {
                throw new DocumentException($e);
            }
    }

}


?>