<?PHP
/*
 * $Id: Type1Font.php,v 1.2 2005/09/29 22:02:44 mstaylor Exp $
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


require_once("fonts/FontsResourceAnchor.php");
require_once("../DocumentException.php");
require_once("BaseFont.php");
require_once("RandomAccessFileOrArray.php");
require_once("../../util/StringHelpers.php");
require_once("Pfm2afm.php");
require_once("GlyphList.php");
require_once("RandomAccessFileOrArray.php");
require_once("PdfStream.php");
require_once("PdfDictionary.php");
require_once("PdfIndirectReference.php");
require_once("PdfNumber.php");
require_once("PdfName.php");
require_once("PdfRectangle.php");
require_once("PdfArray.php");
require_once("PdfWriter.php");
require_once("PdfObject.php");
require_once("GlyphList.php");



/** Reads a Type1 font
*
* @author Paulo Soares (psoares@consiste.pt)
*/
class Type1Font extends BaseFont
{
     private static $resourceAnchor;

    /** The PFB file if the input was made with a <CODE>byte</CODE> array.
    */
    protected $pfb;
    /** The Postscript font name.
    */
    private $FontName = "";
    /** The full name of the font.
    */
    private $FullName = "";
    /** The family name of the font.
    */
    private $FamilyName = "";
    /** The weight of the font: normal, bold, etc.
    */
    private $Weight = "";
    /** The italic angle of the font, usually 0.0 or negative.
    */
    private $ItalicAngle = 0.0;
    /** <CODE>true</CODE> if all the characters have the same
    *  width.
    */
    private $IsFixedPitch = FALSE;
    /** The character set of the font.
    */
    private $CharacterSet = "";
    /** The llx of the FontBox.
    */
    private $llx = -50;
    /** The lly of the FontBox.
    */
    private $lly = -200;
    /** The lurx of the FontBox.
    */
    private $urx = 1000;
    /** The ury of the FontBox.
    */
    private $ury = 900;
    /** The underline position.
    */
    private $UnderlinePosition = -100;
    /** The underline thickness.
    */
    private $UnderlineThickness = 50;
    /** The font's encoding name. This encoding is 'StandardEncoding' or
    *  'AdobeStandardEncoding' for a font that can be totally encoded
    *  according to the characters names. For all other names the
    *  font is treated as symbolic.
    */
    private $EncodingScheme = "FontSpecific";
    /** A variable.
    */
    private $CapHeight = 700;
    /** A variable.
    */
    private $XHeight = 480;
    /** A variable.
    */
    private $Ascender = 800;
    /** A variable.
    */
    private $Descender = -200;
    /** A variable.
    */
    private $StdHW = 0;
    /** A variable.
    */
    private $StdVW = 80;

    /** Represents the section CharMetrics in the AFM file. Each
    *  value of this array contains a <CODE>Object[4]</CODE> with an
    *  Integer, Integer, String and int[]. This is the code, width, name and char bbox.
    *  The key is the name of the char and also an Integer with the char number.
    */
    private $CharMetrics = array();
    /** Represents the section KernPairs in the AFM file. The key is
    *  the name of the first character and the value is a <CODE>Object[]</CODE>
    *  with 2 elements for each kern pair. Position 0 is the name of
    *  the second character and position 1 is the kerning distance. This is
    *  repeated for all the pairs.
    */
    private $KernPairs = array();
    /** The file in use.
    */
    private $fileName ="";
    /** <CODE>true</CODE> if this font is one of the 14 built in fonts.
    */
    private $builtinFont = FALSE;
    /** Types of records in a PFB file. ASCII is 1 and BINARY is 2.
    *  They have to appear in the PFB file in this sequence.
    */
    private static final $pfbTypes = array(1, 2, 1);

    /** Creates a new Type1 font.
    * @param ttfAfm the AFM file if the input is made with a <CODE>byte</CODE> array
    * @param pfb the PFB file if the input is made with a <CODE>byte</CODE> array
    * @param afmFile the name of one of the 14 built-in fonts or the location of an AFM file. The file must end in '.afm'
    * @param enc the encoding to be applied to this font
    * @param emb true if the font is to be embedded in the PDF
    * @throws DocumentException the AFM file is invalid
    * @throws IOException the AFM file could not be read
    */
    function __construct($afmFile, $enc, $emb, $ttfAfm, $pfb) 
    {
        if ($emb && $ttfAfm != NULL && $pfb == NULL)
            throw new DocumentException("Two byte arrays are needed if the Type1 font is embedded.");
        if ($emb && $ttfAfm != NULL)
            $this->pfb = $pfb;
        $encoding = $enc;
        $embedded = $emb;
        $fileName = $afmFile;
        $fontType = BaseFont::FONT_TYPE_T1;
        $rf = NULL;
        $is = NULL;
        if (key_exists($afmFile, BaseFont::$BuiltinFonts14) == TRUE) {
            $embedded = FALSE;
            $builtinFont = TRUE;
            $buf = itextphp_bytes_create(1024);
            try {
                if ($resourceAnchor == NULL)
                    $resourceAnchor = new FontsResourceAnchor();
                $is = BaseFont::getResourceStream(BaseFont::RESOURCE_PATH . $afmFile . ".afm");
                if ($is == NULL) {
                    $msg = $afmFile . " not found as resource. (The *.afm files must exist as resources in the package com.lowagie.text.pdf.fonts)";
                    trigger_error($msg, "Cannot divide by zero", E_USER_ERROR);
                    throw new DocumentException($msg);
                }
                //ByteArrayOutputStream out = new ByteArrayOutputStream();
                $out = itextphp_bytes_create(4096);
                while (!feof($handle)) {
                    $buf = fgets($handle, 1024);
                    $bufbyte =  itextphp_bytes_createfromRaw($buf);
                    //$size = is.read(buf);
                    //if (size < 0)
                    //    break;
                    itextphp_bytes_write($out, $bufbyte);

                }
                //buf = out.toByteArray();
            }
            catch {
                if ($is != NULL) {
                    try {
                        fclose($is);
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
                }
            }

            if ($is != NULL) {
                    try {
                        fclose($is);
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
            }
            try {
                $rf = new RandomAccessFileOrArray($buf);
                process($rf);
            }
            catch {
                if ($rf != NULL) {
                    try {
                        $rf->close();
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
                }
            }
            if ($rf != NULL) {
                    try {
                        $rf->close();
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
            }
        }
        else if (endsWith(strtolower($afmFile), ".afm") == TRUE) {
            try {
                if ($ttfAfm == NULL)
                    $rf = new RandomAccessFileOrArray($afmFile);
                else
                    $rf = new RandomAccessFileOrArray($ttfAfm);
                process($rf);
            }
            catch {
                if ($rf != NULL) {
                    try {
                        $rf->close();
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
                }
            }

            if ($rf != NULL) {
                    try {
                        $rf->close();
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
            }
        }
        else if (endsWith(strtolower($afmFile), ".pfm") == TRUE) {
            try {
                //ByteArrayOutputStream ba = new ByteArrayOutputStream();
                $ba = itextphp_bytes_create(4096);
                if ($ttfAfm == NULL)
                    $rf = new RandomAccessFileOrArray($afmFile);
                else
                    $rf = new RandomAccessFileOrArray($ttfAfm);
                Pfm2afm::convert($rf, $ba);
                $rf->close();
                $rf = new RandomAccessFileOrArray($ba);
                process($rf);
            }
            catch {
                if ($rf != NULL) {
                    try {
                        $rf->close();
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
                }
            }

            if ($rf != NULL) {
                    try {
                        $rf->close();
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
            }
        }
        else
            throw new DocumentException($afmFile . " is not an AFM or PFM font file.");
        try {
            $EncodingScheme = trim($EncodingScheme);
            if (strcmp($EncodingScheme, "AdobeStandardEncoding") == 0 || strcmp($EncodingScheme, "StandardEncoding") == 0) {
                $fontSpecific = FALSE;
            }
            PdfEncodings::convertToBytes(" ", $enc); // check if the encoding exists
            createEncoding();
        }
        catch (Exception $re) {
            throw $re;
        }
        catch (Exception $e) {
            throw new DocumentException($e);
        }
    }

    /** Gets the width from the font according to the <CODE>name</CODE> or,
    * if the <CODE>name</CODE> is null, meaning it is a symbolic font,
    * the char <CODE>c</CODE>.
    * @param c the char if the font is symbolic
    * @param name the glyph name
    * @return the width of the char
    */
    function getRawWidth($c, $name) {
        $metrics = NULL;
        if ($name == NULL) { // font specific
            $metrics = $CharMetrics[ord($c)];
        }
        else {
            if (strcmp($name, ".notdef") == 0)
                return 0;
            $metrics = $CharMetrics[$name];
        }
        if ($metrics != NULL)
            return (integer)$metrics[1];
        return 0;
    }

    /** Gets the kerning between two Unicode characters. The characters
    * are converted to names and this names are used to find the kerning
    * pairs in the <CODE>HashMap</CODE> <CODE>KernPairs</CODE>.
    * @param char1 the first char
    * @param char2 the second char
    * @return the kerning to be applied
    */
    public function getKerning($char1, $char2)
    {
        $first = GlyphList::unicodeToName(ord($char1));
        if ($first == NULL)
            return 0;
        $second = GlyphList::unicodeToName(ord($char2));
        if ($second == NULL)
            return 0;
        $obj = $KernPairs[$first];
        if ($obj == NULL)
            return 0;
        for ($k = 0; $k < count($obj); $k += 2) {
            if (strcmp($second, $obj[$k]) == 0)
                return ((integer)$obj[$k + 1]);
        }
        return 0;
    }

    /** Reads the font metrics
    * @param rf the AFM file
    * @throws DocumentException the AFM file is invalid
    * @throws IOException the AFM file could not be read
    */
    public function process(RandomAccessFileOrArray $rf)
    {
        $line = "";
        $isMetrics = FALSE;
        while (($line = $rf->readLine()) != NULL)
        {
            $tok = preg_split("/[\s]+/",$line);
            if (count($tok).hasMoreTokens() < 1)
                continue;
            $ident = $tok[0];
            if (strcmp($ident, "FontName") == 0)
                $FontName = substr(nextToken($tok, 1, "\u00ff"),1);
            else if (strcmp($ident, "FullName") == 0)
                $FullName = substr(nextToken($tok, 1, "\u00ff"),1);
            else if (strcmp($ident, "FamilyName") == 0)
                $FamilyName = substr(nextToken($tok, 1, "\u00ff"),1);
            else if (strcmp($ident, "Weight") == 0)
                $Weight = substr(nextToken($tok, 1, "\u00ff"),1);
            else if (strcmp($ident, "ItalicAngle") == 0)
                $ItalicAngle = (float)$tok[1];
            else if (strcmp($ident, "IsFixedPitch") == 0)
                $IsFixedPitch = strcmp($tok[1], "true") == 0;
            else if (strcmp($ident, "CharacterSet") == 0)
                $CharacterSet = substr(nextToken($tok, 1, "\u00ff"),1);
            else if (strcmp($ident, "FontBBox") == 0)
            {
                $llx = (float)$tok[1];
                $lly = (float)$tok[2];
                $urx = (float)$tok[3];
                $ury = (float)$tok[4];
            }
            else if (strcmp($ident, "UnderlinePosition") == 0)
                $UnderlinePosition = (integer)$tok[1];
            else if (strcmp($ident, "UnderlineThickness") == 0)
                $UnderlineThickness = (integer)$tok[1];
            else if (strcmp($ident, "EncodingScheme") == 0)
                $EncodingScheme = tsubstr(nextToken($tok, 1, "\u00ff"),1);
            else if (strcmp($ident, "CapHeight") == 0)
                $CapHeight = (integer)$tok[1];
            else if (strcmp($ident, "XHeight") == 0)
                $XHeight = (integer)$tok[1];
            else if (strcmp($ident, "Ascender") == 0)
                $Ascender = (integer)$tok[1];
            else if (strcmp($ident, "Descender") == 0)
                $Descender = (integer)$tok[1];
            else if (strcmp($ident, "StdHW") == 0)
                $StdHW = (integer)$tok[1];
            else if (strcmp($ident, "StdVW") == 0)
                $StdVW = (integer)$tok[1];
            else if (strcmp($ident,"StartCharMetrics") == 0)
            {
                $isMetrics = TRUE;
                break;
            }
        }
        if ($isMetrics == FALSE)
            throw new DocumentException("Missing StartCharMetrics in " . $fileName);
        while (($line = $rf->readLine()) != NULL)
        {
            $tok = preg_split("/[\s]+/",$line);
            if (count($tok).hasMoreTokens() < 1)
                continue;
            $ident = $tok[0];
            if (strcmp($ident, "EndCharMetrics") == 0)
            {
                $isMetrics = FALSE;
                break;
            }
            $C = -1;
            $WX = 250;
            $N = "";
            $B = NULL;

            $tok = preg_split(";", $line);
            $place = 0;
            while ($place < $count($tok))
            {
                $tokc = preg_split("/[\s]+/",$tok[$place]);
                if (count($tokc).hasMoreTokens() < 1)
                    continue;
                $ident = $tokc[0];
                if (strcmp($ident, "C") == 0)
                    $C = (integer)$tokc[1];
                else if (strcmp($ident, "WX")  == 0)
                    $WX = (integer)$tokc[1];
                else if (strcmp($ident, "N") == 0)
                    $N = $tokc[1];
                else if (strcmp($ident, "B") == 0) {
                    $B = array((integer)$tokc[1], (integer)$tokc[2], (integer)$tokc[3], (integer)$tokc[4]);
                }
                $place++;
            }
            $metrics = array($C, $WX, $N, $B);
            if ((integer)$C >= 0)
                $CharMetrics[$C] = $metrics;
            $CharMetrics[$N] = $metrics;
        }
        if ($isMetrics == TRUE)
            throw new DocumentException("Missing EndCharMetrics in " . $fileName);
        while (($line = $rf->readLine()) != NULL)
        {
            $tok = preg_split("/[\s]+/",$line);
            if (count($tok).hasMoreTokens() < 1)
                continue;
            $ident = $tok[0];
            if (strcmp($ident, "EndFontMetrics") == 0)
                return;
            if (strcmp($ident, "StartKernPairs") == 0)
            {
                $isMetrics = TRUE;
                break;
            }
        }
        if ($isMetrics == FALSE)
            throw new DocumentException("Missing EndFontMetrics in " . $fileName);
        while (($line = $rf->readLine()) != NULL)
        {
            $tok = preg_split("/[\s]+/",$line);
            if (count($tok).hasMoreTokens() < 1)
                continue;
            $ident = $tok[0];
            if (strcmp($ident, "KPX") == 0)
            {
                $first = $tok[1];
                $second = $tok[2];
                $width = (integer)$tok[3];
                $relates = $KernPairs[$first];
                if ($relates == NULL)
                    $KernPairs[$first] = array($second, $width);
                else
                {
                    $n = count($relates);
                    $relates2 = array();//new Object[n + 2];
                    for ($k = 0; $k < $n; $k++)
                    {
                        $relates2[$k] = $relates[$k];
                    }
                    $relates2[$n] = $second;
                    $relates2[$n + 1] = $width;
                    $KernPairs[$first] = $relates2;
                }
            }
            else if (strcmp($ident, "EndKernPairs") == 0)
            {
                $isMetrics = false;
                break;
            }
        }
        if ($isMetrics == TRUE)
            throw new DocumentException("Missing EndKernPairs in " . $fileName);
        $rf->close();
    }

    /** If the embedded flag is <CODE>false</CODE> or if the font is
    *  one of the 14 built in types, it returns <CODE>null</CODE>,
    * otherwise the font is read and output in a PdfStream object.
    * @return the PdfStream containing the font or <CODE>null</CODE>
    * @throws DocumentException if there is an error reading the font
    */
    private function getFontStream() 
    {
        if ($builtinFont == TRUE || $embedded == FALSE)
            return NULL;
        $rf = NULL;
        try {
            $filePfb = substr($fileName, 0, strlen($fileName) - 3) . "pfb";
            if ($pfb == NULL)
                $rf = new RandomAccessFileOrArray($filePfb);
            else
                $rf = new RandomAccessFileOrArray($pfb);
            $fileLength = $rf->length();
            $st = new byte[fileLength - 18];
            $lengths = array();
            array_pad($lengths, 3,0);
            $bytePtr = 0;
            for ($k = 0; $k < 3; ++$k) {
                if ($rf->read() != 0x80)
                    throw new DocumentException("Start marker missing in " . $filePfb);
                if ($rf.read() != $pfbTypes[$k])
                    throw new DocumentException("Incorrect segment type in " + filePfb);
                $size = $rf->read();
                $size += $rf->read() << 8;
                $size += $rf->read() << 16;
                $size += $rf->read() << 24;
                $lengths[$k] = $size;
                while ($size != 0) {
                    $got = $rf->read($st, $bytePtr, $size);
                    if ($got < 0)
                        throw new DocumentException("Premature end in " . $filePfb);
                    $bytePtr += $got;
                    $size -= $got;
                }
            }
            return new StreamFont($st, $lengths);
        }
        catch (Exception $e) {
            throw new DocumentException($e);
            if ($rf != NULL) {
                try {
                    $rf->close();
                }
                catch (Exception $e) {
                    // empty on purpose
                }
            }
        }

            if ($rf != NULL) {
                try {
                    $rf->close();
                }
                catch (Exception $e) {
                    // empty on purpose
                }
            }

    }


    /** Generates the font descriptor for this font or <CODE>null</CODE> if it is
    * one of the 14 built in fonts.
    * @param fontStream the indirect reference to a PdfStream containing the font or <CODE>null</CODE>
    * @return the PdfDictionary containing the font descriptor or <CODE>null</CODE>
    */
    private function getFontDescriptor(PdfIndirectReference $fontStream)
    {
        if ($builtinFont == TRUE)
            return NULL;
        $dic = new PdfDictionary(PdfName::$FONTDESCRIPTOR);
        $dic->put(PdfName::$ASCENT, new PdfNumber($Ascender));
        $dic->put(PdfName::$CAPHEIGHT, new PdfNumber($CapHeight));
        $dic->put(PdfName::$DESCENT, new PdfNumber($Descender));
        $dic->put(PdfName::$FONTBBOX, new PdfRectangle($llx, $lly, $urx, $ury));
        $dic->put(PdfName::$FONTNAME, new PdfName($FontName));
        $dic->put(PdfName::$ITALICANGLE, new PdfNumber($ItalicAngle));
        $dic->put(PdfName::$STEMV, new PdfNumber($StdVW));
        if ($fontStream != NULL)
            $dic->put(PdfName::$FONTFILE, $fontStream);
        $flags = 0;
        if ($IsFixedPitch == TRUE)
            $flags |= 1;
        $flags |= $fontSpecific ? 4 : 32;
        if ($ItalicAngle < 0)
            $flags |= 64;
        if (strpos($FontName, "Caps") >= 0 || endsWith($FontName, "SC") == TRUE)
            $flags |= 131072;
        if (strcmp($Weight, "Bold") == 0)
            $flags |= 262144;
        $dic->put(PdfName::$FLAGS, new PdfNumber($flags));

        return $dic;
    }

    /** Generates the font dictionary for this font.
    * @return the PdfDictionary containing the font dictionary
    * @param firstChar the first valid character
    * @param lastChar the last valid character
    * @param shortTag a 256 bytes long <CODE>byte</CODE> array where each unused byte is represented by 0
    * @param fontDescriptor the indirect reference to a PdfDictionary containing the font descriptor or <CODE>null</CODE>
    */
    private function getFontBaseType(PdfIndirectReference $fontDescriptor, $firstChar, $lastChar, $shortTag)
    {
        $dic = new PdfDictionary(PdfName::$FONT);
        $dic->put(PdfName::$SUBTYPE, PdfName::$TYPE1);
        $dic->put(PdfName::$BASEFONT, new PdfName($FontName));
        $stdEncoding = strcmp($encoding, "Cp1252") == 0 || strcmp($encoding, "MacRoman") ==0;
        if ($fontSpecific == FALSE) {
            for ($k = $firstChar; $k <= $lastChar; ++$k) {
                if (strcmp($differences[$k], (BaseFont::notdef)) != 0) {
                    $firstChar = $k;
                    break;
                }
            }
            if ($stdEncoding == TRUE)
                $dic->put(PdfName::$ENCODING, strcmp($encoding, "Cp1252") == 0 ? PdfName::$WIN_ANSI_ENCODING : PdfName::$MAC_ROMAN_ENCODING);
            else {
                $enc = new PdfDictionary(PdfName::$ENCODING);
                $dif = new PdfArray();
                $gap = TRUE;
                for ($k = $firstChar; $k <= $lastChar; ++$k) {
                    if (itextphp_bytes_getIntValue($shortTag, $k) != 0) {
                        if ($gap == TRUE) {
                            $dif->add(new PdfNumber($k));
                            $gap = FALSE;
                        }
                        $dif->add(new PdfName($differences[$k]));
                    }
                    else
                        $gap = TRUE;
                }
                $enc->put(PdfName::DIFFERENCES, $dif);
                $dic->put(PdfName::ENCODING, $enc);
            }
        }
        if ($forceWidthsOutput == TRUE || !($builtinFont == TRUE && ($fontSpecific == TRUE || $stdEncoding == TRUE))) {
            $dic->put(PdfName::$FIRSTCHAR, new PdfNumber($firstChar));
            $dic->put(PdfName::$LASTCHAR, new PdfNumber($lastChar));
            $wd = new PdfArray();
            for ($k = $firstChar; $k <= $lastChar; ++$k) {
                if (itextphp_bytes_getIntValue($shortTag, $k) == 0)
                    $wd->add(new PdfNumber(0));
                else
                    $wd->add(new PdfNumber($widths[$k]));
            }
            $dic->put(PdfName::$WIDTHS, $wd);
        }
        if ($builtinFont==FALSE && $fontDescriptor != NULL)
            $dic->put(PdfName::$FONTDESCRIPTOR, $fontDescriptor);
        return $dic;
    }

    /** Outputs to the writer the font dictionaries and streams.
    * @param writer the writer for this document
    * @param ref the font indirect reference
    * @param params several parameters that depend on the font type
    * @throws IOException on error
    * @throws DocumentException error in generating the object
    */
    function writeFont(PdfWriter $writer, PdfIndirectReference $ref, array $params) 
    {
        $firstChar = (integer)$params[0];
        $lastChar = (integer)$params[1];
        $shortTag = $params[2];
        if ($subset == FALSE) {
            $firstChar = 0;
            $lastChar = itextphp_bytes_getSize($shortTag) - 1;
            for ($k = 0; $k < itextphp_bytes_getSize($shortTag); ++$k)
                itextphp_bytes_update($shortTag,$k, 1);
        }
        $ind_font = NULL;
        $pobj = NULL;
        $obj = $;
        $pobj = getFontStream();
        if ($pobj != NULL){
            $obj = $writer->addToBody($pobj);
            $ind_font = $obj->getIndirectReference();
        }
        $pobj = getFontDescriptor($ind_font);
        if ($pobj != NULL){
            $obj = $writer->addToBody($pobj);
            $ind_font = $obj->getIndirectReference();
        }
        $pobj = getFontBaseType($ind_font, $firstChar, $lastChar, $shortTag);
        $writer->addToBody($pobj, $ref);
    }

    /** Gets the font parameter identified by <CODE>key</CODE>. Valid values
    * for <CODE>key</CODE> are <CODE>ASCENT</CODE>, <CODE>CAPHEIGHT</CODE>, <CODE>DESCENT</CODE>,
    * <CODE>ITALICANGLE</CODE>, <CODE>BBOXLLX</CODE>, <CODE>BBOXLLY</CODE>, <CODE>BBOXURX</CODE>
    * and <CODE>BBOXURY</CODE>.
    * @param key the parameter to be extracted
    * @param fontSize the font size in points
    * @return the parameter in points
    */
    public function getFontDescriptor($key, $fontSize) {
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

    /** Gets the postscript font name.
    * @return the postscript font name
    */
    public function getPostscriptFontName() {
        return $FontName;
    }

    /** Gets the full name of the font. If it is a True Type font
    * each array element will have {Platform ID, Platform Encoding ID,
    * Language ID, font name}. The interpretation of this values can be
    * found in the Open Type specification, chapter 2, in the 'name' table.<br>
    * For the other fonts the array has a single element with {"", "", "",
    * font name}.
    * @return the full name of the font
    */
    public function getFullFontName() {
        return array(array("", "", "", $FullName));
    }

    /** Gets the family name of the font. If it is a True Type font
    * each array element will have {Platform ID, Platform Encoding ID,
    * Language ID, font name}. The interpretation of this values can be
    * found in the Open Type specification, chapter 2, in the 'name' table.<br>
    * For the other fonts the array has a single element with {"", "", "",
    * font name}.
    * @return the family name of the font
    */
    public function getFamilyFontName() {
        return array(("", "", "", $FamilyName));
    }

    /** Checks if the font has any kerning pairs.
    * @return <CODE>true</CODE> if the font has any kerning pairs
    */
    public function hasKernPairs() {
        return count($KernPairs) > 0;
    }

    /**
    * Sets the font name that will appear in the pdf font dictionary.
    * Use with care as it can easily make a font unreadable if not embedded.
    * @param name the new font name
    */
    public function setPostscriptFontName($name) {
        $FontName = $name;
    }

    /**
    * Sets the kerning between two Unicode chars.
    * @param char1 the first char
    * @param char2 the second char
    * @param kern the kerning to apply in normalized 1000 units
    * @return <code>true</code> if the kerning was applied, <code>false</code> otherwise
    */
    public function setKerning($char1, $char2, $kern) {
        $first = GlyphList::unicodeToName(ord($char1));
        if ($first == NULL)
            return FALSE;
        $second = GlyphList::unicodeToName(ord(char2));
        if ($second == null)
            return FALSE;
        $obj = $KernPairs[$first];
        if ($obj == NULL) {
            $obj = new array({$second, intval($kern)};
            $KernPairs[$first[] = $obj;
            return TRUE;
        }
        for ($k = 0; $k < count($obj); $k += 2) {
            if (strcmp($second, $obj[$k]) == 0) {
                $obj[$k + $1] = intval($kern);
                return TRUE;
            }
        }
        $size = count($obj);
        $obj2 = array();
        array_pad($obj2, $size+2, NULL);
        
        System.arraycopy(obj, 0, obj2, 0, size);

        for ($k = 0; $k < $size; $k++)
        {
            $obj2[$k] = $obj[$k];
        }
        $obj2[$size] = $second;
        $obj2[$size + 1] = intval($kern);
        $KernPairs[$first] = $obj2;
        return FALSE;
    }

     protected function getRawCharBBox($c, $name) {
        $metrics = NULL;
        if ($name == NULL) { // font specific
            $metrics = $CharMetrics[$c];
        }
        else {
            if (strcmp($name, ".notdef") == 0)
                return NULL;
            $metrics = $CharMetrics[$name];
        }
        if ($metrics != NULL)
            return $metrics[3];
        return NULL;
    }
    
}
?>