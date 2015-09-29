<?PHP
/*
 * $Id: CJKFont.php,v 1.2 2005/09/29 22:02:44 mstaylor Exp $
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
require_once("../../util/Properties.php");
require_once("BaseFont.php");
require_once("../../util/StringHelpers.php");
require_once("PdfDictionary.php");
require_once("PdfName.php");
require_once("PdfNumber.php");
require_once("PdfLiteral.php");
require_once("PdfString.php");
require_once("PdfArray.php");
require_once("PdfWriter.php");
require_once("PdfIndirectReference.php");
require_once("PdfObject.php");


/**
* Creates a CJK font compatible with the fonts in the Adobe Asian font Pack.
*
* @author  Paulo Soares (psoares@consiste.pt)
*/
class CJKFont extends BaseFont
{

    /** The encoding used in the PDF document for CJK fonts
    */
    const CJK_ENCODING = "UnicodeBigUnmarked";
    const FIRST = 0;
    const BRACKET = 1;
    const SERIAL = 2;
    const V1Y = 880;

    static $cjkFonts = NULL;
    static $cjkEncodings = NULL;
    static $allCMaps = array();
    static $allFonts = array();
    private static $propertiesLoaded = FALSE;

    /** The font name */
    private $fontName="";
    /** The style modifier */
    private $style = "";
    /** The CMap name associated with this font */
    private $CMap = "";

    private $cidDirect = FALSE;
    
    private $translationMap;
    private $vMetrics = array();
    private $hMetrics = array();
    private $fontDesc = array();
    private $vertical = FALSE;

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(CJKFont::$initialized == FALSE)
        {
            CJKFont::$cjkFonts = new Properties();
            CJKFont::$cjkEncodings = new Properties();
            CJKFont::$initialized = TRUE;
        }
    }

     private static function loadProperties() {
        if ($propertiesLoaded == TRUE)
            return;

            if ($propertiesLoaded == TRUE)
                return;
            try {
                $is = BaseFont::getResourceStream(BaseFont::RESOURCE_PATH . "cjkfonts.properties");
                $cjkFonts->load($is);
                fclose($is);
                $is = getResourceStream(BaseFont::RESOURCE_PATH . "cjkencodings.properties");
                $cjkEncodings->load($is);
                fclose($is);
            }
            catch (Exception $e) {
                $cjkFonts = new Properties();
                $cjkEncodings = new Properties();
            }
            $propertiesLoaded = TRUE;
        }

    /** Creates a CJK font.
    * @param fontName the name of the font
    * @param enc the encoding of the font
    * @param emb always <CODE>false</CODE>. CJK font and not embedded
    * @throws DocumentException on error
    * @throws IOException on error
    */
    public function __construct($fontName, $enc, $emb)
    {
        CJKFont::loadProperties();
        $fontType = BaseFont::FONT_TYPE_CJK;
        $nameBase = BaseFont::getBaseName($fontName);
        if (isCJKFont($nameBase, $enc) == FALSE)
            throw new DocumentException("Font '" . $fontName . "' with '" . $enc . "' encoding is not a CJK font.");
        if (strlen($nameBase) < strlen($fontName)) {
            $style = substr($fontName, strlen($nameBase));
            $fontName = $nameBase;
        }
        $this->fontName = $fontName;
        encoding = CJKFont::CJK_ENCODING;
        $vertical = endsWith($enc, "V");
        $CMap = $enc;
        if (beginsWith($enc, "Identity-") == TRUE) {
            $cidDirect = TRUE;
            $s = $cjkFonts->getProperty($fontName);
            $s = substr($s, strpos($s, '_'));
            $c = $allCMaps[$s];
            if ($c == NULL) {
                $c = readCMap($s);
                if ($c == NULL)
                    throw new DocumentException("The cmap " . $s . " does not exist as a resource.");
                $c[BaseFont::CID_NEWLINE] = '\n';
                $allCMaps[$s] = $c;
            }
            $translationMap = $c;
        }
        else {
            $c = $allCMaps[$enc];
            if ($c == NULL) {
                $s = $cjkEncodings->getProperty($enc);
                if ($s == NULL)
                    throw new DocumentException("The resource cjkencodings.properties does not contain the encoding " . $enc);
                $tk = preg_split("/[\s]+/",$s)
                $nt = $tk[0];
                $c = $allCMaps[$nt];
                if ($c == NULL) {
                    $c = readCMap($nt);
                    $allCMaps[$nt] = $c;
                }
                if (count($tk) > 1) {
                    $nt2 = $tk[1];
                    $m2 = readCMap($nt2);
                    for ($k = 0; $k < 0x10000; ++$k) {
                        if ($m2[$k] == 0)
                            $m2[$k] = $c[$k];
                    }
                    $allCMaps[$enc] = $m2;
                    $c = $m2;
                }
            }
            $translationMap = $c;
        }
        $fontDesc = $allFonts[$fontName];
        if ($fontDesc == NULL) {
            $fontDesc = readFontProperties($fontName);
            $allFonts[$fontName] = $fontDesc;
        }
        $hMetrics = $fontDesc["W"];
        $vMetrics = $fontDesc["W2"];
    }


    /** Checks if its a valid CJK font.
    * @param fontName the font name
    * @param enc the encoding
    * @return <CODE>true</CODE> if it is CJK font
    */
    public static function isCJKFont($fontName, $enc) {
        CJKFont::loadProperties();
        $encodings = $cjkFonts->getProperty($fontName);
        return ($encodings != NULL && (strcmp($enc, "Identity-H") == 0 || strcmp($enc, "Identity-V") == 0 || strpos($encodings, ("_" . $enc . "_")) >= 0));
    }

    public function getWidth($text) {
        $total = 0;
        for ($k = 0; k < strlen($text); ++$k) {
            $c = ord($text[$k]);
            if ($cidDirect == FALSE)
                $c = $translationMap[$c];
            $v;
            if ($vertical == TRUE)
                $v = $vMetrics[$c];
            else
                $v = $hMetrics[$c];
            if ($v > 0)
                $total += $v;
            else
                $total += $1000;
        }
        return $total;
    }

    function getRawWidth($c, $name) {
        return 0;
    }

    public function getKerning($char1, $char2) {
        return 0;
    }

    private function getFontDescriptor() {
        $dic = new PdfDictionary(PdfName::$FONTDESCRIPTOR);
        $dic->put(PdfName::$ASCENT, new PdfLiteral($fontDesc["Ascent"]));
        $dic->put(PdfName::$CAPHEIGHT, new PdfLiteral($fontDesc["CapHeight"]));
        $dic->put(PdfName::$DESCENT, new PdfLiteral($fontDesc["Descent"]));
        $dic->put(PdfName::$FLAGS, new PdfLiteral($fontDesc["Flags"]));
        $dic->put(PdfName::$FONTBBOX, new PdfLiteral($fontDesc["FontBBox"]));
        $dic->put(PdfName::$FONTNAME, new PdfName($fontName . $style));
        $dic->put(PdfName::$ITALICANGLE, new PdfLiteral($fontDesc["ItalicAngle"]));
        $dic->put(PdfName.STEMV, new PdfLiteral($fontDesc["StemV"]));
        $pdic = new PdfDictionary();
        $pdic->put(PdfName::$PANOSE, new PdfString($fontDesc["Panose"], NULL));
        $dic->put(PdfName::$STYLE, $pdic);
        return dic;
    }

    private function getCIDFont(PdfIndirectReference $fontDescriptor, array $cjkTag) {
        $dic = new PdfDictionary(PdfName::$FONT);
        $dic->put(PdfName::$SUBTYPE, PdfName::$CIDFONTTYPE0);
        $dic->put(PdfName::$BASEFONT, new PdfName($fontName . $style));
        $dic->put(PdfName::$FONTDESCRIPTOR, $fontDescriptor);
        $keys = array_keys($cjkTag);
        sort($keys);
        $w = convertToHCIDMetrics($keys, $hMetrics);
        if ($w != NULL)
            $dic->put(PdfName::$W, new PdfLiteral($w));
        if ($vertical == TRUE) {
            $w = convertToVCIDMetrics($keys, $vMetrics, $hMetrics);;
            if ($w != NULL)
                $dic->put(PdfName::$W2, new PdfLiteral($w));
        }
        else
            $dic->put(PdfName::$DW, new PdfNumber(1000));
        $cdic = new PdfDictionary();
        $cdic->put(PdfName::$REGISTRY, new PdfString($fontDesc["Registry"], NULL));
        $cdic->put(PdfName::$ORDERING, new PdfString($fontDesc["Ordering"], NULL));
        $cdic->put(PdfName::$SUPPLEMENT, new PdfLiteral($fontDesc["Supplement"]));
        $dic->put(PdfName::$CIDSYSTEMINFO, $cdic);
        return $dic;
    }

    private function getFontBaseType(PdfIndirectReference $CIDFont) {
        $dic = new PdfDictionary(PdfName::$FONT);
        $dic->put(PdfName::$SUBTYPE, PdfName::$TYPE0);
        $name = $fontName;
        if (strlen($style) > 0)
            $name .= "-" . substr($style, 1);
        $name .= "-" . $CMap;
        $dic->put(PdfName::$BASEFONT, new PdfName($name));
        $dic->put(PdfName::$ENCODING, new PdfName($CMap));
        $dic->put(PdfName::$DESCENDANTFONTS, new PdfArray($CIDFont));
        return $dic;
    }

     function writeFont(PdfWriter $writer, PdfIndirectReference $ref, array $params) {
        $cjkTag = $params[0];
        $ind_font = NULL;
        $pobj = NULL;
        $obj = NULL;
        $pobj = getFontDescriptor();
        if ($pobj != NULL){
            $obj = $writer->addToBody($pobj);
            $ind_font = $obj->getIndirectReference();
        }
        $pobj = getCIDFont($ind_font, $cjkTag);
        if ($pobj != NULL){
            $obj = $writer->addToBody($pobj);
            $ind_font = $obj->getIndirectReference();
        }
        $pobj = getFontBaseType($ind_font);
        $writer->addToBody($pobj, $ref);
    }

    private function getDescNumber($name) {
        return (float)$fontDesc[$name];
    }

    private function getBBox($idx) {
        $s = $fontDesc["FontBBox"];
        $tk = preg_split("/[\s\[\]]+/",$s)
        $ret = $tk[0];
        for ($k = 0; $k < $idx; ++$k)
            $ret = $tk[$k+1];
        return (float)$ret;
    }

    /** Gets the font parameter identified by <CODE>key</CODE>. Valid values
    * for <CODE>key</CODE> are <CODE>ASCENT</CODE>, <CODE>CAPHEIGHT</CODE>, <CODE>DESCENT</CODE>
    * and <CODE>ITALICANGLE</CODE>.
    * @param key the parameter to be extracted
    * @param fontSize the font size in points
    * @return the parameter in points
    */
    public function getFontDescriptor($key, $fontSize) {
        switch ($key) {
            case BaseFont::AWT_ASCENT:
            case BaseFont::ASCENT:
                return getDescNumber("Ascent") * $fontSize / 1000;
            case BaseFont::CAPHEIGHT:
                return getDescNumber("CapHeight") * $fontSize / 1000;
            case BaseFont::AWT_DESCENT:
            case BaseFont::DESCENT:
                return getDescNumber("Descent") * $fontSize / 1000;
            case BaseFont::ITALICANGLE:
                return getDescNumber("ItalicAngle");
            case BaseFont::BBOXLLX:
                return $fontSize * getBBox(0) / 1000;
            case BaseFont::BBOXLLY:
                return $fontSize * getBBox(1) / 1000;
            case BaseFont::BBOXURX:
                return $fontSize * getBBox(2) / 1000;
            case BaseFont::BBOXURY:
                return $fontSize * getBBox(3) / 1000;
            case BaseFont::AWT_LEADING:
                return 0;
            case BaseFont::AWT_MAXADVANCE:
                return $fontSize * (getBBox(2) - getBBox(0)) / 1000;
        }
        return 0;
    }

    public function getPostscriptFontName() {
        return $fontName;
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
        return array(array("", "", "", $fontName));
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
        return getFullFontName();
    }

    static function readCMap($name) {
        try {
            $name = $name . ".cmap";
            $is = getResourceStream(BaseFont::RESOURCE_PATH . $name);
            //char c[] = new char[0x10000];
            $c = NULL;
            for ($k = 0; $k < 0x10000; ++$k)
                $c[$k] = (fread($is,1) << 8) . fread($is,1);
            return $c;
        }
        catch (Exception $e) {
            // empty on purpose
        }
        return NULL;
    }

    static function createMetric($s) {
        $h = array();
        $tk = preg_split("/[\s]+/",$s);
        $moreToks = TRUE;
        $k = 0;

        while ($moreToks == TRUE)
        {
            $n1 = (integer)$tk[$k];
            $h[$n1] = (integer)$tk[$k++];
            $k++;
            if ($k >= count($tk))
                $moreToks = FALSE;
        }
        return $h;
    }

    static function convertToHCIDMetrics(array $keys, array $h) {
        if (count($keys) == 0)
            return NULL;
        $lastCid = 0;
        $lastValue = 0;
        $start = 0;
        for ($start = 0; $start < count($keys); ++$start) {
            $lastCid = $keys[$start];
            $lastValue = $h[$lastCid];
            if ($lastValue != 0) {
                ++$start;
                break;
            }
        }
        if ($lastValue == 0)
            return NULL;
        $buf = "";
        $buf .= '[';
        $buf .= $lastCid;
        $state = CJKFont::FIRST;
        for ($k = $start; $k < count($keys); ++$k) {
            $cid = $keys[$k];
            $value = $h[$cid];
            if ($value == 0)
                continue;
            switch ($state) {
                case CJKFont::FIRST: {
                    if ($cid == $lastCid + 1 && $value == $lastValue) {
                        $state = CJKFont::SERIAL;
                    }
                    else if ($cid == $lastCid + 1) {
                        $state = CJKFont::BRACKET;
                        $buf .= '[' . $lastValue;
                    }
                    else {
                        $buf .= '[' . $lastValue . ']' . $cid;
                    }
                    break;
                }
                case CJKFont::BRACKET: {
                    if ($cid == $lastCid + 1 && $value == $lastValue) {
                        $state = CJKFont::SERIAL;
                        $buf .= ']' . $lastCid;
                    }
                    else if ($cid == $lastCid + 1) {
                        $buf .= ' ' . $lastValue;
                    }
                    else {
                        $state = CJKFont::FIRST;
                        $buf .= ' ') . $lastValue . ']' . $cid;
                    }
                    break;
                }
                case CJKFont::SERIAL: {
                    if ($cid != $lastCid + 1 || $value != $lastValue) {
                        $buf .= ' ' . $lastCid . ' ' . $lastValue . ' ' . $cid;
                        $state = CJKFont::FIRST;
                    }
                    break;
                }
            }
            $lastValue = $value;
            $lastCid = $cid;
        }
        switch ($state) {
            case CJKFont::FIRST: {
                $buf .= '[' . $lastValue . "]]";
                break;
            }
            case CJKFont::BRACKET: {
                $buf .= ' ' . $lastValue . "]]";
                break;
            }
            case CJKFont::SERIAL: {
                $buf .= ' ' . $lastCid . ' ' . $lastValue . ']';
                break;
            }
        }
        return $buf;
    }


    static function convertToVCIDMetrics(array $keys, array $v, array $h) {
        if (count($keys) == 0)
            return NULL;
        $lastCid = 0;
        $lastValue = 0;
        $lastHValue = 0;
        $start = 0;
        for ($start = 0; $start < count($keys); ++$start) {
            $lastCid = $keys[$start];
            $lastValue = $v[$lastCid];
            if ($lastValue != 0) {
                ++$start;
                break;
            }
            else
                $lastHValue = $h[$lastCid];
        }
        if ($lastValue == 0)
            return NULL;
        if ($lastHValue == 0)
            $lastHValue = 1000;
        $buf = "";
        $buf .= '[';
        $buf .= $lastCid;
        $state = CJKFont::FIRST;
        for ($k = $start; $k < count($keys); ++$k) {
            $cid = $keys[$k];
            $value = $v[$cid];
            if ($value == 0)
                continue;
            $hValue = $h[$lastCid];
            if ($hValue == 0)
                $hValue = 1000;
            switch ($state) {
                case CJKFont::FIRST: {
                    if ($cid == $lastCid + 1 && $value == $lastValue && $hValue == $lastHValue) {
                        $state = CJKFont::SERIAL;
                    }
                    else {
                        $buf .= ' ' . $lastCid . ' ' . -$lastValue . ' ' . $lastHValue / 2 . ' ' . $V1Y . ' ' . $cid;
                    }
                    break;
                }
                case CJKFont::SERIAL: {
                    if ($cid != $lastCid + 1 || $value != $lastValue || $hValue != $lastHValue) {
                        $buf .= ' ' . $lastCid . ' ' . -$lastValue . ' ' . $lastHValue / 2 . ' ' . $V1Y . ' ' . $cid;
                        $state = CJKFont::FIRST;
                    }
                    break;
                }
            }
            $lastValue = $value;
            $lastCid = $cid;
            $lastHValue = $hValue;
        }
        $buf .= ' ' . $lastCid . ' ' . -$lastValue . ' ' . $lastHValue / 2 . ' ' . $V1Y . " ]";
        return $buf;
    }


     static function readFontProperties($name) {
        try {
            $name .= ".properties";
            $is = getResourceStream(BaseFont::RESOURCE_PATH . $name);
            $p = new Properties();
            $p->load($is);
            fclose($is);
            $W = createMetric($p->getProperty("W"));
            $p->remove("W");
            $W2 = createMetric($p->getProperty("W2"));
            $p->remove("W2");
            $map = array();
            foreach (array_keys($p) as &$obj) {
                $map[$obj] = p->getProperty($obj);
            }
            $map["W"] = $W;
            $map["W2"] = $W2;
            return $map;
        }
        catch (Exception $e) {
            // empty on purpose
        }
        return NULL;
    }

    public function getUnicodeEquivalent($c) {
        if ($cidDirect == TRUE)
            return $translationMap[$c];
        return $c;
    }

    public function getCidCode($c) {
        if ($cidDirect == TRUE)
            return $c;
        return $translationMap[$c];
    }

    /** Checks if the font has any kerning pairs.
    * @return always <CODE>false</CODE>
    */
    public function hasKernPairs() {
        return FALSE;
    }

    /**
    * Checks if a character exists in this font.
    * @param c the character to check
    * @return <CODE>true</CODE> if the character has a glyph,
    * <CODE>false</CODE> otherwise
    */
    public function charExists($c) {
        return ord($translationMap[$c]) != 0;
    }

    /**
    * Sets the character advance.
    * @param c the character
    * @param advance the character advance normalized to 1000 units
    * @return <CODE>true</CODE> if the advance was set,
    * <CODE>false</CODE> otherwise. Will always return <CODE>false</CODE>
    */
    public function setCharAdvance($c, $advance) {
        return FALSE;
    }

    /**
    * Sets the font name that will appear in the pdf font dictionary.
    * Use with care as it can easily make a font unreadable if not embedded.
    * @param name the new font name
    */
    public function setPostscriptFontName($name) {
        $fontName = $name;
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