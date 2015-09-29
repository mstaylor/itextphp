<?PHP
/*
 * $Id: CFFFont.php,v 1.2 2005/10/21 18:20:50 mstaylor Exp $
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

/*
 * Comments by Sivan Toledo:
 * I created this class in order to add to iText the ability to utilize
 * OpenType fonts with CFF glyphs (these usually have an .otf extension).
 * The CFF font within the CFF table of the OT font might be either a CID
 * or a Type1 font. (CFF fonts may also contain multiple fonts; I do not
 * know if this is allowed in an OT table). The PDF spec, however, only
 * allow a CID font with an Identity-H or Identity-V encoding. Otherwise,
 * you are limited to an 8-bit encoding.
 * Adobe fonts come in both flavors. That is, the OTFs sometimes have
 * a CID CFF inside (for Japanese fonts), and sometimes a Type1 CFF
 * (virtually all the others, Latin/Greek/Cyrillic). So to easily use
 * all the glyphs in the latter, without creating multiple 8-bit encoding,
 * I wrote this class, whose main purpose is to convert a Type1 font inside
 * a CFF container (which might include other fonts) into a CID CFF font
 * that can be directly embeded in the PDF.
 *
 * Limitations of the current version:
 * 1. It does not extract a single CID font from a CFF that contains that
 *    particular CID along with other fonts. The Adobe Japanese OTF's that
 *    I have only have one font in the CFF table, so these can be
 *    embeded in the PDF as is.
 * 2. It does not yet subset fonts.
 * 3. It may or may not work on CFF fonts that are not within OTF's.
 *    I didn't try that. In any case, that would probably only be
 *    useful for subsetting CID fonts, not for CFF Type1 fonts (I don't
 *    think there are any available.
 * I plan to extend the class to support these three features at some
 * future time.
 */

require_once("RandomAccessFileOrArray.php");

class CFFFont
{

    static $operatorNames = array(
        "version", "Notice", "FullName", "FamilyName",
        "Weight", "FontBBox", "BlueValues", "OtherBlues",
        "FamilyBlues", "FamilyOtherBlues", "StdHW", "StdVW",
        "UNKNOWN_12", "UniqueID", "XUID", "charset",
        "Encoding", "CharStrings", "Private", "Subrs",
        "defaultWidthX", "nominalWidthX", "UNKNOWN_22", "UNKNOWN_23",
        "UNKNOWN_24", "UNKNOWN_25", "UNKNOWN_26", "UNKNOWN_27",
        "UNKNOWN_28", "UNKNOWN_29", "UNKNOWN_30", "UNKNOWN_31",
        "Copyright", "isFixedPitch", "ItalicAngle", "UnderlinePosition",
        "UnderlineThickness", "PaintType", "CharstringType", "FontMatrix",
        "StrokeWidth", "BlueScale", "BlueShift", "BlueFuzz",
        "StemSnapH", "StemSnapV", "ForceBold", "UNKNOWN_12_15",
        "UNKNOWN_12_16", "LanguageGroup", "ExpansionFactor", "initialRandomSeed",
        "SyntheticBase", "PostScript", "BaseFontName", "BaseFontBlend",
        "UNKNOWN_12_24", "UNKNOWN_12_25", "UNKNOWN_12_26", "UNKNOWN_12_27",
        "UNKNOWN_12_28", "UNKNOWN_12_29", "ROS", "CIDFontVersion",
        "CIDFontRevision", "CIDFontType", "CIDCount", "UIDBase",
        "FDArray", "FDSelect", "FontName"
    );

    static $standardStrings = array(
        // Automatically generated from Appendix A of the CFF specification; do
        // not edit. Size should be 391.
        ".notdef", "space", "exclam", "quotedbl", "numbersign", "dollar",
        "percent", "ampersand", "quoteright", "parenleft", "parenright",
        "asterisk", "plus", "comma", "hyphen", "period", "slash", "zero", "one",
        "two", "three", "four", "five", "six", "seven", "eight", "nine", "colon",
        "semicolon", "less", "equal", "greater", "question", "at", "A", "B", "C",
        "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "bracketleft", "backslash",
        "bracketright", "asciicircum", "underscore", "quoteleft", "a", "b", "c",
        "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r",
        "s", "t", "u", "v", "w", "x", "y", "z", "braceleft", "bar", "braceright",
        "asciitilde", "exclamdown", "cent", "sterling", "fraction", "yen",
        "florin", "section", "currency", "quotesingle", "quotedblleft",
        "guillemotleft", "guilsinglleft", "guilsinglright", "fi", "fl", "endash",
        "dagger", "daggerdbl", "periodcentered", "paragraph", "bullet",
        "quotesinglbase", "quotedblbase", "quotedblright", "guillemotright",
        "ellipsis", "perthousand", "questiondown", "grave", "acute", "circumflex",
        "tilde", "macron", "breve", "dotaccent", "dieresis", "ring", "cedilla",
        "hungarumlaut", "ogonek", "caron", "emdash", "AE", "ordfeminine", "Lslash",
        "Oslash", "OE", "ordmasculine", "ae", "dotlessi", "lslash", "oslash", "oe",
        "germandbls", "onesuperior", "logicalnot", "mu", "trademark", "Eth",
        "onehalf", "plusminus", "Thorn", "onequarter", "divide", "brokenbar",
        "degree", "thorn", "threequarters", "twosuperior", "registered", "minus",
        "eth", "multiply", "threesuperior", "copyright", "Aacute", "Acircumflex",
        "Adieresis", "Agrave", "Aring", "Atilde", "Ccedilla", "Eacute",
        "Ecircumflex", "Edieresis", "Egrave", "Iacute", "Icircumflex", "Idieresis",
        "Igrave", "Ntilde", "Oacute", "Ocircumflex", "Odieresis", "Ograve",
        "Otilde", "Scaron", "Uacute", "Ucircumflex", "Udieresis", "Ugrave",
        "Yacute", "Ydieresis", "Zcaron", "aacute", "acircumflex", "adieresis",
        "agrave", "aring", "atilde", "ccedilla", "eacute", "ecircumflex",
        "edieresis", "egrave", "iacute", "icircumflex", "idieresis", "igrave",
        "ntilde", "oacute", "ocircumflex", "odieresis", "ograve", "otilde",
        "scaron", "uacute", "ucircumflex", "udieresis", "ugrave", "yacute",
        "ydieresis", "zcaron", "exclamsmall", "Hungarumlautsmall",
        "dollaroldstyle", "dollarsuperior", "ampersandsmall", "Acutesmall",
        "parenleftsuperior", "parenrightsuperior", "twodotenleader",
        "onedotenleader", "zerooldstyle", "oneoldstyle", "twooldstyle",
        "threeoldstyle", "fouroldstyle", "fiveoldstyle", "sixoldstyle",
        "sevenoldstyle", "eightoldstyle", "nineoldstyle", "commasuperior",
        "threequartersemdash", "periodsuperior", "questionsmall", "asuperior",
        "bsuperior", "centsuperior", "dsuperior", "esuperior", "isuperior",
        "lsuperior", "msuperior", "nsuperior", "osuperior", "rsuperior",
        "ssuperior", "tsuperior", "ff", "ffi", "ffl", "parenleftinferior",
        "parenrightinferior", "Circumflexsmall", "hyphensuperior", "Gravesmall",
        "Asmall", "Bsmall", "Csmall", "Dsmall", "Esmall", "Fsmall", "Gsmall",
        "Hsmall", "Ismall", "Jsmall", "Ksmall", "Lsmall", "Msmall", "Nsmall",
        "Osmall", "Psmall", "Qsmall", "Rsmall", "Ssmall", "Tsmall", "Usmall",
        "Vsmall", "Wsmall", "Xsmall", "Ysmall", "Zsmall", "colonmonetary",
        "onefitted", "rupiah", "Tildesmall", "exclamdownsmall", "centoldstyle",
        "Lslashsmall", "Scaronsmall", "Zcaronsmall", "Dieresissmall", "Brevesmall",
        "Caronsmall", "Dotaccentsmall", "Macronsmall", "figuredash",
        "hypheninferior", "Ogoneksmall", "Ringsmall", "Cedillasmall",
        "questiondownsmall", "oneeighth", "threeeighths", "fiveeighths",
        "seveneighths", "onethird", "twothirds", "zerosuperior", "foursuperior",
        "fivesuperior", "sixsuperior", "sevensuperior", "eightsuperior",
        "ninesuperior", "zeroinferior", "oneinferior", "twoinferior",
        "threeinferior", "fourinferior", "fiveinferior", "sixinferior",
        "seveninferior", "eightinferior", "nineinferior", "centinferior",
        "dollarinferior", "periodinferior", "commainferior", "Agravesmall",
        "Aacutesmall", "Acircumflexsmall", "Atildesmall", "Adieresissmall",
        "Aringsmall", "AEsmall", "Ccedillasmall", "Egravesmall", "Eacutesmall",
        "Ecircumflexsmall"readChar, "Edieresissmall", "Igravesmall", "Iacutesmall",
        "Icircumflexsmall", "Idieresissmall", "Ethsmall", "Ntildesmall",
        "Ogravesmall", "Oacutesmall", "Ocircumflexsmall", "Otildesmall",
        "Odieresissmall", "OEsmall", "Oslashsmall", "Ugravesmall", "Uacutesmall",
        "Ucircumflexsmall", "Udieresissmall", "Yacutesmall", "Thornsmall",
        "Ydieresissmall", "001.000", "001.001", "001.002", "001.003", "Black",
        "Bold", "Book", "Light", "Medium", "Regular", "Roman", "Semibold"
    );

    //private String[] strings;
    public function getString($sid) {
        if (ord($sid) < count($standardStrings) return $standardStrings[ord($sid)];
        if (ord($sid) >= count($standardStrings)+(count($stringOffsets)-1)) return NULL;
        $j = ord($sid) - count($standardStrings);
        //java.lang.System.err.println("going for "+j);
        $p = getPosition();
        seek($stringOffsets[$j]);
        $s = "";
        for ($k=$stringOffsets[$j]; $k<$stringOffsets[$j+1]; $k++) {
            $s .= getCard8();
        }
        seek($p);
        return $s;
    }

    function getCard8() {
        try {
            $i = $buf->readByte();
            return chr($i & 0xff);
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    function getCard16() {
        try {
            return $buf->readChar();
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    function getOffset($offSize) {
        $offset = 0;
        for ($i=0; $i<$offSize; $i++) {
            $offset *= 256;
            $offset += ord(getCard8());
        }
        return $offset;
    }

    function seek($offset) {
        try {
            $buf->seek($offset);
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    function getShort() {
        try {
            return $buf->readShort();
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    function getInt() {
        try {
            return $buf->readInt();
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    function getPosition() {
        try {
            return $buf->getFilePointer();
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }
    $nextIndexOffset = 0;

    // read the offsets in the next index
    // data structure, convert to global
    // offsets, and return them.
    // Sets the nextIndexOffset.
    function getIndex($nextIndexOffset) {
        $count = 0;
        $indexOffSize = 0;

        seek($nextIndexOffset);
        $count = ord(getCard16());
        $offsets = array();

        if ($count==0) {
            $offsets[0] = -1;
            $nextIndexOffset += 2;
            return $offsets;
        }

        $indexOffSize = ord(getCard8());

        for ($j=0; $j<=$count; $j++) {
            //nextIndexOffset = ofset to relative segment
            $offsets[$j] = $nextIndexOffset
            //2-> count in the index header. 1->offset size in index header
            + 2+1
            //offset array size * offset size 
            + ($count+1)*$indexOffSize
            //???zero <-> one base
            - 1
            // read object offset relative to object array base 
            + getOffset($indexOffSize);
        }
        //nextIndexOffset = offsets[count];
        return $offsets;
    }

    protected $key = "";
    protected $args = array();
    protected $arg_count = 0;

    protected function getDictItem() {
        for ($i=0; $i<$arg_count; $i++) $args[$i]=NULL;
        $arg_count = 0;
        $key = NULL;
        $gotKey = FALSE;

        while ($gotKey == FALSE) {
            $b0 = getCard8();
            if (ord($b0) == 29) {
                $item = getInt();
                $args[$arg_count] = $item;
                $arg_count++;
                //System.err.println(item+" ");
                continue;
            }
            if (ord($b0) == 28) {
                $item = getShort();
                $args[$arg_count] = $item;
                $arg_count++;
                //System.err.println(item+" ");
                continue;
            }
            if (ord($b0) >= 32 && ord($b0) <= 246) {
                $item = (ord($b0)-139);
                $args[$arg_count] = $item;
                $arg_count++;
                //System.err.println(item+" ");
                continue;
            }
            if (ord($b0) >= 247 && ord($b0) <= 250) {
                $b1 = getCard8();
                $item = ((ord($b0)-247)*256+ord($b1)+108);
                $args[$arg_count] = $item;
                $arg_count++;
                //System.err.println(item+" ");
                continue;
            }
            if (ord($b0) >= 251 && ord($b0) <= 254) {
                $b1 = getCard8();
                $item = (-(ord($b0)-251)*256-ord($b1)-108);
                $args[$arg_count] = $item;
                $arg_count++;
                //System.err.println(item+" ");
                continue;
            }
            if (ord($b0) == 30) {
                $item = "";
                $done = FALSE;
                $buffer = 0;
                $avail = 0;
                $nibble = 0;
                while ($done == FALSE) {
                    // get a nibble
                    if ($avail==0) { $buffer = getCard8(); $avail=2; }
                    if ($avail==1) { $nibble = (ord($buffer) / 16); $avail--; }
                    if ($avail==2) { $nibble = (ord($buffer) % 16); $avail--; }
                    switch ($nibble) {
                        case 0xa: $item .= "." ; break;
                        case 0xb: $item .= "E" ; break;
                        case 0xc: $item .= "E-"; break;
                        case 0xe: $item .= "-" ; break;
                        case 0xf: $done=TRUE   ; break;
                        default:
                            if ($nibble >= 0 && $nibble <= 9)
                                $item += (string)$nibble;
                            else {
                                $item .= "<NIBBLE ERROR: "+(string)$nibble+">";
                                $done = TRUE;
                            }
                            break;
                    }
                }
                $args[$arg_count] = $item;
                $arg_count++;
                //System.err.println(" real=["+item+"]");
                continue;
            }
            if (ord($b0) <= 21) {
                $gotKey=TRUE;
                if (ord($b0) != 12) $key = $operatorNames[ord($b0)];
                else $key = $operatorNames[32 + ord(getCard8())];
                //for (int i=0; i<arg_count; i++)
                //  System.err.print(args[i].toString()+" ");
                //System.err.println(key+" ;");
                continue;
            }
        }
    }


     /** a utility that creates a range item for an entire index
    *
    * @param indexOffset where the index is
    * @return a range item representing the entire index
    */
    protected function getEntireIndexRange($indexOffset) {
        seek($indexOffset);
        $count = ord(getCard16());
        if ($count==0) {
            return new RangeItem($buf,$indexOffset,2);
        } else {
            $indexOffSize = ord(getCard8());
            seek($indexOffset+2+1+$count*$indexOffSize);
            $size = getOffset($indexOffSize)-1;
            return new RangeItem($buf,$indexOffset,
            2+1+($count+1)*$indexOffSize+size);
        }
    }

    /** get a single CID font. The PDF architecture (1.4)
    * supports 16-bit strings only with CID CFF fonts, not
    * in Type-1 CFF fonts, so we convert the font to CID if
    * it is in the Type-1 format.
    * Two other tasks that we need to do are to select
    * only a single font from the CFF package (this again is
    * a PDF restriction) and to subset the CharStrings glyph
    * description.
    */

    public function getCID($fontName)
    //throws java.io.FileNotFoundException
    {
        if (is_resource($fontName) == TRUE)
            $fontName = itextphp_string_toPHPString($fontName);
        $j = 0;
        for ($j=0; $j<count($fonts); $j++)
            if (strcmp($fontName, $fonts[$j]->name) == 0) break;
        if ($j==count($fonts)) return NULL;

        $l = array();

        // copy the header

        seek(0);

        $major = ord(getCard8());
        $minor = ord(getCard8());
        $hdrSize = ord(getCard8());
        $offSize = ord(getCard8());
        $nextIndexOffset = $hdrSize;

        array_push($l, new RangeItem($buf,0,$hdrSize);

        $nglyphs=-1;
        $nstrings=-1;
        if ($fonts[$j]->isCID == FALSE ) {
            // count the glyphs
            seek($fonts[$j]->charstringsOffset);
            $nglyphs = ord(getCard16());
            seek($stringIndexOffset);
            $nstrings = ord(getCard16())+count(CFFFont::$standardStrings);
            //System.err.println("number of glyphs = "+nglyphs);
        }

        // create a name index

        array_push($l, new UInt16Item(chr(1))); // count
        array_push($l, new UInt8Item(chr(1)); // offSize
        array_push($l, new UInt8Item(chr(1))); // first offset
        array_push($l, new UInt8Item(chr( 1+strlen($fonts[$j]->name) )));
        array_push($l, new StringItem($fonts[$j]->name));

        // create the topdict Index


        array_push($l, new UInt16Item(chr(1))); // count
        array_push($l, new UInt8Item(chr(2))); // offSize
        array_push($l, new UInt16Item(chr(1))); // first offset
        $topdictIndex1Ref = new IndexOffsetItem(2);
        array_push($l, $topdictIndex1Ref);
        $topdictBase = new IndexBaseItem();
        array_push($l, $topdictBase);

        /*
        int maxTopdictLen = (topdictOffsets[j+1]-topdictOffsets[j])
                            + 9*2 // at most 9 new keys
                            + 8*5 // 8 new integer arguments
                            + 3*2;// 3 new SID arguments
         */

        //int    topdictNext = 0;
        //byte[] topdict = new byte[maxTopdictLen];

        $charsetRef     = new DictOffsetItem();
        $charstringsRef = new DictOffsetItem();
        $fdarrayRef     = new DictOffsetItem();
        $fdselectRef    = new DictOffsetItem();

        if ($fonts[$j]->isCID == FALSE ) {
            // create a ROS key
            array_push($l, new DictNumberItem($nstrings));
            array_push($l, new DictNumberItem($nstrings+1));
            array_push($l, new DictNumberItem(0));
            array_push($l, new UInt8Item(chr(12)));
            array_push($l, new UInt8Item(chr(30)));
            // create a CIDCount key
            array_push($l, new DictNumberItem($nglyphs));
            array_push($l, new UInt8Item(chr(12)));
            array_push($l, new UInt8Item(chr(34)));
            // What about UIDBase (12,35)? Don't know what is it.
            // I don't think we need FontName; the font I looked at didn't have it.
        }

        // create an FDArray key
        array_push($l, $fdarrayRef);
        array_push($l, new UInt8Item(chr(12)));
        array_push($l, new UInt8Item(chr(36)));
        // create an FDSelect key
        array_push($l, $fdselectRef);
        array_push($l, new UInt8Item(chr(12)));
        array_push($l, new UInt8Item(chr(37)));
        // create an charset key
        array_push($l, $charsetRef);
        array_push($l, new UInt8Item(chr(15)));
        // create a CharStrings key
        array_push($l, $charstringsRef);
        array_push($l, new UInt8Item(chr(17)));

        seek($topdictOffsets[$j]);
        while (getPosition() < $topdictOffsets[$j+1]) {
            $p1 = getPosition();
            getDictItem();
            $p2 = getPosition();
            if (strcmp($key, "Encoding") == 0
            || strcmp($key, "Private") == 0
            || strcmp($key, "FDSelect") == 0
            || strcmp($key, "FDArray") == 0
            || strcmp($key, "charset") == 0
            || strcmp($key, "CharStrings") == 0
            ) {
                // just drop them
            } else {
                array_push($l, new RangeItem($buf,$p1,$p2-$p1));
            }
        }

        array_push($l, new IndexMarkerItem($topdictIndex1Ref,$topdictBase));

        // Copy the string index and append new strings.
        // We need 3 more strings: Registry, Ordering, and a FontName for one FD.
        // The total length is at most "Adobe"+"Identity"+63 = 76

        if ($fonts[$j]->isCID == TRUE) {
            array_push($l, getEntireIndexRange($stringIndexOffset));
        } else {
            $fdFontName = $fonts[$j]->name . "-OneRange";
            if (strlen($fdFontName) > 127)
                $fdFontName = substr($fdFontName, 0,127);
            $extraStrings = "Adobe" . "Identity" . $fdFontName;

            $origStringsLen = $stringOffsets[count($stringOffsets)-1]
            - $stringOffsets[0];
            $stringsBaseOffset = $stringOffsets[0]-1;

            $stringsIndexOffSize = NULL;
            if ($origStringsLen+strlen($extraStrings) <= 0xff) $stringsIndexOffSize = 1;
            else if ($origStringsLen+strlen($extraStrings) <= 0xffff) $stringsIndexOffSize = 2;
            else if ($origStringsLen+strlen($extraStrings) <= 0xffffff) $stringsIndexOffSize = 3;
            else $stringsIndexOffSize = 4;

            array_push($l, new UInt16Item(chr((count($stringOffsets)-1)+3))); // count
            array_push($l, new UInt8Item(chr($stringsIndexOffSize))); // offSize
            for ($i=0; $i<count($stringOffsets); $i++)
                array_push($l, new IndexOffsetItem($stringsIndexOffSize,
                $stringOffsets[$i]-$stringsBaseOffset));
            $currentStringsOffset = $stringOffsets[count($stringOffsets)-1]
            - $stringsBaseOffset;
            //l.addLast(new IndexOffsetItem(stringsIndexOffSize,currentStringsOffset));
            $currentStringsOffset += strlen("Adobe");
            array_push($l, new IndexOffsetItem($stringsIndexOffSize,$currentStringsOffset));
            $currentStringsOffset += strlen("Identity");
            array_push($l, new IndexOffsetItem($stringsIndexOffSize,$currentStringsOffset));
            $currentStringsOffset += strlen($fdFontName);
            array_push($l, new IndexOffsetItem($stringsIndexOffSize,$currentStringsOffset));

            array_push($l, new RangeItem($buf,$stringOffsets[0],$origStringsLen));
            array_push($l, new StringItem($extraStrings));
        }

        // copy the global subroutine index

        array_push($l, getEntireIndexRange($gsubrIndexOffset));

        // deal with fdarray, fdselect, and the font descriptors

        if ($fonts[$j]->isCID == TRUE) {
            // copy the FDArray, FDSelect, charset
        } else {
            // create FDSelect
            array_push($l, new MarkerItem($fdselectRef));
            array_push($l, new UInt8Item(chr(3))); // format identifier
            array_push($l, new UInt16Item(chr(1))); // nRanges

            array_push($l, new UInt16Item(chr(0))); // Range[0].firstGlyph
            array_push($l, new UInt8Item(chr(0))); // Range[0].fd

            array_push($l, new UInt16Item(chr($nglyphs))); // sentinel

            // recreate a new charset
            // This format is suitable only for fonts without subsetting

            array_push($l, new MarkerItem($charsetRef));
            array_push($l, new UInt8Item(chr(2))); // format identifier

            array_push($l, new UInt16Item(chr(1))); // first glyph in range (ignore .notdef)
            array_push($l, new UInt16Item(chr(($nglyphs-1)))); // nLeft
            // now all are covered, the data structure is complete.

            // create a font dict index (fdarray)

            array_push($l, new MarkerItem($fdarrayRef));
            array_push($l, new UInt16Item(chr(1)));
            array_push($l, new UInt8Item(chr(1))); // offSize
            array_push($l, new UInt8Item(chr(1))); // first offset

            $privateIndex1Ref = new IndexOffsetItem(1);
            array_push($l, $privateIndex1Ref);
            $privateBase = new IndexBaseItem();
            array_push($l, $privateBase);

            // looking at the PS that acrobat generates from a PDF with
            // a CFF opentype font embeded with an identity-H encoding,
            // it seems that it does not need a FontName.
            //l.addLast(new DictNumberItem((standardStrings.length+(stringOffsets.length-1)+2)));
            //l.addLast(new UInt8Item((char)12));
            //l.addLast(new UInt8Item((char)38)); // FontName

            array_push($l, new DictNumberItem($fonts[$j]->privateLength));
            $privateRef = new DictOffsetItem();
            array_push($l, $privateRef);
            array_push($l, new UInt8Item(chr(18))); // Private

            array_push($l, new IndexMarkerItem($privateIndex1Ref,$privateBase));

            // copy the private index & local subroutines

            array_push($l, new MarkerItem($privateRef));
            // copy the private dict and the local subroutines.
            // the length of the private dict seems to NOT include
            // the local subroutines.
            array_push($l, new RangeItem($buf,$fonts[$j]->privateOffset,$fonts[$j]->privateLength));
            if ($fonts[$j]->privateSubrs >= 0) {
                //System.err.println("has subrs="+fonts[j].privateSubrs+" ,len="+fonts[j].privateLength);
                array_push($l, getEntireIndexRange($fonts[$j]->privateSubrs));
            }
        }

        // copy the charstring index

        array_push($l, new MarkerItem($charstringsRef));
        array_push($l, getEntireIndexRange($fonts[$j]->charstringsOffset));

        // now create the new CFF font

        $currentOffset = array();
        $currentOffset[0] = 0;

        foreach ($l as &$item) {
            $item->increment($currentOffset);
        }

        foreach ($l as &$item) {
            $item->xref();
        }

        $size = $currentOffset[0];
        $b = itextphp_bytes_create($size);

       foreach ($l as &$item) {
            $item->emit($b);
        }

        return $b;
    }


    public function isCID($fontName) {
        $j = 0;
        if (is_resource($fontName) == TRUE)
            $fontName = itextphp_string_toPHPString($fontName);
        for ($j=0; $j<count($fonts); $j++)
            if (strcmp($fontName, $fonts[$j]->name) == 0) return $fonts[$j]->isCID;
        return FALSE;
    }

    public function exists($fontName) {
        $j = 0;
        if (is_resource($fontName) == TRUE)
            $fontName = itextphp_string_toPHPString($fontName);
        for ($j=0; $j<count($fonts); $j++)
            if (strcmp($fontName, $fonts[$j]->name) == 0) return TRUE;
        return FALSE;
    }


    public function getNames() {
        $names = array();
        for ($i=0; $i<count($fonts); $i++)
            $names[$i] = $fonts[$i]->name;
        return $names;
    }

    /**
    * A random Access File or an array
    * (contributed by orly manor)
    */
    protected $buf = NULL;
    private $offSize = 0;

    protected $nameIndexOffset = 0;
    protected $topdictIndexOffset = 0;
    protected $stringIndexOffset = 0;
    protected $gsubrIndexOffset = 0;
    protected $nameOffsets = array();
    protected $topdictOffsets = array();
    protected $stringOffsets = array();
    protected $gsubrOffsets = array();

    // Changed from private to protected by Ygal&Oren
    protected $fonts = array();

    public function __construct(RandomAccessFileOrArray $inputbuffer) {

        //System.err.println("CFF: nStdString = "+standardStrings.length);
        $buf = $inputbuffer;
        seek(0);

        $major = ord(getCard8());
        $minor = ord(getCard8());

        //System.err.println("CFF Major-Minor = "+major+"-"+minor);

        $hdrSize = ord(getCard8());

        $offSize = ord(getCard8());

        //System.err.println("offSize = "+offSize);

        //int count, indexOffSize, indexOffset, nextOffset;

        $nameIndexOffset    = $hdrSize;
        $nameOffsets        = getIndex($nameIndexOffset);
        $topdictIndexOffset = $nameOffsets[count($nameOffsets)-1];
        $topdictOffsets     = getIndex($topdictIndexOffset);
        $stringIndexOffset  = $topdictOffsets[count($topdictOffsets)-1];
        $stringOffsets      = getIndex($stringIndexOffset);
        $gsubrIndexOffset   = $stringOffsets[count($stringOffsets)-1];
        $gsubrOffsets       = getIndex($gsubrIndexOffset);

        $fonts = array();

        // now get the name index

        /*
        names             = new String[nfonts];
        privateOffset     = new int[nfonts];
        charsetOffset     = new int[nfonts];
        encodingOffset    = new int[nfonts];
        charstringsOffset = new int[nfonts];
        fdarrayOffset     = new int[nfonts];
        fdselectOffset    = new int[nfonts];
         */

        for ($j=0; $j<count($nameOffsets)-1; $j++) {
            $fonts[$j] = new Font();
            seek($nameOffsets[$j]);
            $fonts[$j]->name = "";
            for ($k=$nameOffsets[$j]; $k<$nameOffsets[$j+1]; $k++) {
                $fonts[$j]->name .= getCard8();
            }
            //System.err.println("name["+j+"]=<"+fonts[j].name+">");
        }

        // string index

        //strings = new String[stringOffsets.length-1];
        /*
        System.err.println("std strings = "+standardStrings.length);
        System.err.println("fnt strings = "+(stringOffsets.length-1));
        for (char j=0; j<standardStrings.length+(stringOffsets.length-1); j++) {
            //seek(stringOffsets[j]);
            //strings[j] = "";
            //for (int k=stringOffsets[j]; k<stringOffsets[j+1]; k++) {
            //	strings[j] += (char)getCard8();
            //}
            System.err.println("j="+(int)j+" <? "+(standardStrings.length+(stringOffsets.length-1)));
            System.err.println("strings["+(int)j+"]=<"+getString(j)+">");
        }
         */

        // top dict

        for ($j=0; $j<count($topdictOffsets)-1; $j++) {
            seek($topdictOffsets[$j]);
            while (getPosition() < $topdictOffsets[$j+1]) {            	
                getDictItem();
                if (strcmp($key, "FullName") == 0) {
                    //System.err.println("getting fullname sid = "+((Integer)args[0]).intValue());
                    $fonts[$j]->fullName = getString(chr(((integer)$args[0])));
                    //System.err.println("got it");
                } else if (strcmp($key, "ROS") == 0)
                    $fonts[$j]->isCID = TRUE;
                else if (strcmp($key, "Private") == 0) {
                    $fonts[$j]->privateLength  = ((integer)$args[0]);
                    $fonts[$j]->privateOffset  = ((integer)$args[1]);
                }
                else if (strcmp($key, "charset") == 0){
                    $fonts[$j]->charsetOffset = ((integer)$args[0]);

                }
                else if (strcmp($key, "Encoding") == 0){
                    $fonts[$j]->encodingOffset = ((integer)$args[0]);
                    ReadEncoding($fonts[$j]->encodingOffset);
                }
                else if (strcmp($key, "CharStrings") == 0) {
                    $fonts[$j]->charstringsOffset = ((integer)$args[0]);
                    //System.err.println("charstrings "+fonts[j].charstringsOffset);
                    // Added by Oren & Ygal
                    $p = getPosition();
                    $fonts[$j]->charstringsOffsets = getIndex($fonts[$j]->charstringsOffset);
                    seek($p);
                } else if (strcmp($key, "FDArray") == 0)
                    $fonts[$j]->fdarrayOffset = ((integer)$args[0]);
                else if (strcmp($key, "FDSelect") == 0)
                    $fonts[$j]->fdselectOffset = ((integer)$args[0]);
                else if (key=="CharstringType")
                    $fonts[$j]->CharstringType = ((integer)$args[0]);
            }

            // private dict
            if ($fonts[$j]->privateOffset >= 0) {
                //System.err.println("PRIVATE::");
                seek($fonts[$j]->privateOffset);
                while (getPosition() < $fonts[$j]->privateOffset+$fonts[$j]->privateLength) {
                    getDictItem();
                    if (key=="Subrs")
                        //Add the private offset to the lsubrs since the offset is 
                        // relative to the begining of the PrivateDict
                        $fonts[$j]->privateSubrs = ((integer)$args[0])+$fonts[$j]->privateOffset;
                }
            }

            // fdarray index
            if ($fonts[$j]->fdarrayOffset >= 0) {
                $fdarrayOffsets = getIndex($fonts[$j]->fdarrayOffset);

                $fonts[$j]->fdprivateOffsets = array();
                $fonts[$j]->fdprivateLengths = array();

                //System.err.println("FD Font::");

                for ($k=0; $k<count($fdarrayOffsets)-1; $k++) {
                    seek($fdarrayOffsets[$k]);
                    while (getPosition() < $fdarrayOffsets[$k+1])
                        getDictItem();
                    if (strcmp($key, "Private") == 0) {
                        $fonts[$j]->fdprivateLengths[$k]  = ((integer)$args[0]);
                        $fonts[$j]->fdprivateOffsets[$k]  = ((integer)$args[1]);
                    }

                }
            }
        }
        //System.err.println("CFF: done");
    }

    // ADDED BY Oren & Ygal

    function ReadEncoding($nextIndexOffset){
        $format = 0;
        seek($nextIndexOffset);
        $format = ord(getCard8());
    }


}



/** List items for the linked list that builds the new CID font.
*/
abstract class Item 
{
    protected $myOffset = -1;
    /** remember the current offset and increment by item's size in bytes. */
    public function increment(array $currentOffset) {
        $myOffset = $currentOffset[0];
    }
    /** Emit the byte stream for this item. */
    public function emit($buffer) {}
    /** Fix up cross references to this item (applies only to markers). */
    public function xref() {}
}

abstract class OffsetItem extends Item
{
    public $value = 0;
    /** set int the value of an offset item that was initially unknown.
    * It will be fixed up latex by a call to xref on some marker.
    */
    public function set($offset) { $this->value = $offset; }
}


/** A range item.
*/
class RangeItem extends Item {
    public $offset = 0;
    public $length = 0;
    private $buf = NULL;
    public function __construct(RandomAccessFileOrArray $buf, $offset, $length) {
        $this->offset = $offset;
        $this->length = $length;
        $this->buf = $buf;
    }
    public function increment(array $currentOffset) {
        parent::increment($currentOffset);
        $currentOffset[0] += $length;
    }
    public function emit($buffer) {
        //System.err.println("range emit offset "+offset+" size="+length);
        try {
            $buf->seek($offset);
            for ($i=$myOffset; $i<$myOffset+$length; $i++)
                itextphp_bytes_write($buffer, $i, itextphp_bytes_createfromInt($buf->readByte()), 0);
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
        //System.err.println("finished range emit");
    }
}


/** An index-offset item for the list.
* The size denotes the required size in the CFF. A positive
* value means that we need a specific size in bytes (for offset arrays)
* and a negative value means that this is a dict item that uses a
* variable-size representation.
*/
class IndexOffsetItem extends OffsetItem {
    public $size = 0;
    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE)
                    construct1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE)
                    construct2args($arg1, $arg2);
                break;
            }
        }
    }
    private function construct2args($size, $value) {$this->size=$size; $this->value=$value;}
    private function construct1arg($size) {$this->size=$size; }

    public function increment(array $currentOffset) {
        parent::increment($currentOffset);
        $currentOffset[0] += $size;
    }
    public void emit($buffer) {
        $i=0;
        switch ($size) {
            case 4:
                itextphp_bytes_write($buffer, $myOffset+$i, itextphp_bytes_createfromInt(($value >>> 24) & 0xff), 0);
                $i++;
            case 3:
                itextphp_bytes_write($buffer, $myOffset+$i, itextphp_bytes_createfromInt(($value >>> 16) & 0xff), 0);
                $i++;
            case 2:
                itextphp_bytes_write($buffer, $myOffset+$i,  itextphp_bytes_createfromInt((value >>>  8) & 0xff), 0);
                $i++;
            case 1:
                itextphp_bytes_write($buffer, $myOffset+$i, itextphp_bytes_createfromInt((value >>>  0) & 0xff), 0);
                $i++;
        }
        /*
        int mask = 0xff;
        for (int i=size-1; i>=0; i--) {
        buffer[myOffset+i] = (byte) (value & mask);
        mask <<= 8;
        }
        */
    }
}

class IndexBaseItem extends Item {
    public function __construct() {}
}

class IndexMarkerItem extends Item {
    private $offItem = NULL;
    private $indexBase = NULL;
    public function __construct(OffsetItem $offItem, IndexBaseItem $indexBase) {
        $this->offItem   = $offItem;
        $this->indexBase = $indexBase;
    }
    public function xref() {
        //System.err.println("index marker item, base="+indexBase.myOffset+" my="+this.myOffset);
           $offItem->set($this->myOffset-$indexBase->myOffset+1);
    }
}

/**
*
* @author orly manor
*
* TODO To change the template for this generated type comment go to
* Window - Preferences - Java - Code Generation - Code and Comments
*/
class SubrMarkerItem extends Item {
    private $OffsetItem = NULL;
    private $IndexBaseItem = NULL;
    public function __construct(OffsetItem $offItem, IndexBaseItem $indexBase) {
        $this->offItem   = $offItem;
        $this->indexBase = $indexBase;
    }
    public function xref() {
        //System.err.println("index marker item, base="+indexBase.myOffset+" my="+this.myOffset);
        $offItem->set($this->myOffset-$indexBase->myOffset);
    }
}

/** an unknown offset in a dictionary for the list.
* We will fix up the offset later; for now, assume it's large.
*/
class DictOffsetItem extends OffsetItem {
    public $size = 0;
    public function __construct() {$this->size=5; }

    public function increment(array $currentOffset) {
        parent::increment($currentOffset);
        $currentOffset[0] += $size;
    }
    // this is incomplete!
    public function emit($buffer) {
        if ($size==5) {
            itextphp_bytes_write($buffer, $myOffset,  itextphp_bytes_createfromInt(29), 0);
            itextphp_bytes_write($buffer, $myOffset+1, textphp_bytes_createfromInt(($value >>> 24) & 0xff), 0);
            itextphp_bytes_write($buffer, $myOffset+2, itextphp_bytes_createfromInt(($value >>> 16) & 0xff), 0);
            itextphp_bytes_write($buffer, $myOffset+3, itextphp_bytes_createfromInt(($value >>>  8) & 0xff), 0);
            itextphp_bytes_write($buffer, $myOffset+4, itextphp_bytes_createfromInt(($value >>>  0) & 0xff), 0);
        }
    }
}


/** Card24 item.
*/
class UInt24Item extends Item {
    public $value = 0;
    public function __construct($value) {$this->value=$value;}
    public function increment(array $currentOffset) {
        parent::increment($currentOffset);
        $currentOffset[0] += 3;
    }
    // this is incomplete!
    public function emit($buffer) {
        itextphp_bytes_write($buffer, $myOffset+0, itextphp_bytes_createfromInt(($value >>> 16) & 0xff), 0);
        itextphp_bytes_write($buffer, $myOffset+1, itextphp_bytes_createfromInt(($value >>> 8) & 0xff), 0);
        itextphp_bytes_write($buffer, $myOffset+2, itextphp_bytes_createfromInt(($value >>> 0) & 0xff), 0);
    }
}


/** Card32 item.
*/
class UInt32Item extends Item {
    public $value = 0;
    public function __construct($value) {$this->value=$value;}
    public function increment(array $currentOffset) {
        parent::increment($currentOffset);
        $currentOffset[0] += 4;
    }
    // this is incomplete!
    public function emit($buffer) {
        itextphp_bytes_write($buffer, $myOffset+0, itextphp_bytes_createfromInt(($value >>> 24) & 0xff), 0);
        itextphp_bytes_write($buffer, $myOffset+1, itextphp_bytes_createfromInt(($value >>> 16) & 0xff), 0);
        itextphp_bytes_write($buffer, $myOffset+2, itextphp_bytes_createfromInt(($value >>> 8) & 0xff), 0);
        itextphp_bytes_write($buffer, $myOffset+3, itextphp_bytes_createfromInt(($value >>> 0) & 0xff), 0);
    }
}



/** A SID or Card16 item.
*/
class UInt16Item extends Item {
    public $value = NULL;
    public function __construct($value) {$this->value=$value;}
    public void increment(array $currentOffset) {
        parent::increment($currentOffset);
        $currentOffset[0] += 2;
    }
    // this is incomplete!
    public function emit($buffer) {
        itextphp_bytes_write($buffer, $myOffset+0, itextphp_bytes_createfromInt((ord($value) >>> 8) & 0xff), 0);
        itextphp_bytes_write($buffer, $myOffset+1, itextphp_bytes_createfromInt((ord($value) >>> 0) & 0xff), 0);
    }
}


/** A Card8 item.
*/
class UInt8Item extends Item {
    public $value = NULL;
    public function __construct($value) {$this->value=$value;}
    public function increment(array $currentOffset) {
        parent::increment($currentOffset);
        $currentOffset[0] += 1;
    }
    // this is incomplete!
    public function emit($buffer) {
        itextphp_bytes_write($buffer, $myOffset+0, itextphp_bytes_createfromInt((ord($value) >>> 0) & 0xff), 0);
    }
}


class StringItem extends Item {
    public $s = NULL;
    public function __construct($s) {$this->s=$s;}
    public function increment(array $currentOffset) {
        if (is_resource($s) == TRUE)
            $s = itextphp_string_toPHPString($s);
        parent::increment($currentOffset);
        $currentOffset[0] += strlen($s);
    }
    public function emit($buffer) {
        if (is_resource($s) == TRUE)
            $s = itextphp_string_toPHPString($s);
        for ($i=0; $i<strlen($s); $i++)
            itextphp_bytes_write($buffer, $myOffset+$i, itextphp_bytes_createfromInt(ord($s[$i]) & 0xff), 0);
    }
}

/** A dictionary number on the list.
* This implementation is inefficient: it doesn't use the variable-length
* representation.
*/
class DictNumberItem extends Item {
    public $value = 0;
    public $size = 5;
    public function __construct($value) {$this->value=$value;}
    public function increment(array $currentOffset) {
        parent::increment($currentOffset);
        $currentOffset[0] += $size;
    }
    // this is imcomplete!
    public function emit($buffer) {
        if ($size==5) {
            itextphp_bytes_write($buffer, $myOffset, 29;
            itextphp_bytes_write($buffer, $myOffset+1, itextphp_bytes_createfromInt(($value >>> 24) & 0xff), 0);
            itextphp_bytes_write($buffer, $myOffset+2, itextphp_bytes_createfromInt(($value >>> 16) & 0xff), 0);
            itextphp_bytes_write($buffer, $myOffset+3, itextphp_bytes_createfromInt(($value >>>  8) & 0xff), 0);
            itextphp_bytes_write($buffer, $myOffset+4, itextphp_bytes_createfromInt(($value >>>  0) & 0xff), 0);
        }
    }


}


/** An offset-marker item for the list.
* It is used to mark an offset and to set the offset list item.
*/
class MarkerItem extends Item {
    $p = NULL;
    public function __construct(OffsetItem $pointerToMarker) {$p=$pointerToMarker;}
    public function xref() {
        $p->set($this->myOffset);
    }
}

/**
* @author orly manor
* TODO Changed from private to protected by Ygal&Oren
*/
class Font {
        public $name = "";
        public $fullName = "";
        public $isCID = FALSE;
        public $privateOffset     = -1; // only if not CID
        public $privateLength     = -1; // only if not CID
        public $privateSubrs      = -1;
        public $charstringsOffset = -1;
        public $encodingOffset    = -1;
        public $charsetOffset     = -1;
        public $fdarrayOffset     = -1; // only if CID
        public $fdselectOffset    = -1; // only if CID
        public $fdprivateOffsets = array();
        public $fdprivateLengths = array();
        public $fdprivateSubrs = array();

        // Added by Oren & Ygal
        public $nglyphs = 0;
        public $nstrings = 0;
        public $CharsetLength = 0;
        public $charstringsOffsets = array();
        public $charset = array();
        public $FDSelect = array();
        public $FDSelectLength = 0;
        public $FDSelectFormat = 0;
        public $CharstringType = 2;
        public $FDArrayCount = 0;
        public $FDArrayOffsize = 0;
        public $FDArrayOffsets = array();
        public $PrivateSubrsOffset = array();
        public $PrivateSubrsOffsetsArray = array();
        public $SubrsOffsets = array();
    }

?>