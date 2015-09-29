<?PHP
/*
 * $Id: TrueTypeFont.php,v 1.4 2005/10/18 20:18:54 mstaylor Exp $
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
require_once("RandomAccessFileOrArray.php");
require_once("../../util/StringHelpers.php");
require_once("PdfEncodings.php");
require_once("TrueTypeFontSubSet.php");
require_once("PdfDictionary.php");
require_once("PdfIndirectReference.php");
require_once("PdfName.php");
require_once("PdfNumber.php");
require_once("PdfRectangle.php");
require_once("PdfArray.php");



/** Reads a Truetype font
*
* @author Paulo Soares (psoares@consiste.pt)
*/
class TrueTypeFont extends BaseFont 
{

    /** The code pages possible for a True Type font.
    */
    static $codePages = array(
        "1252 Latin 1",
        "1250 Latin 2: Eastern Europe",
        "1251 Cyrillic",
        "1253 Greek",
        "1254 Turkish",
        "1255 Hebrew",
        "1256 Arabic",
        "1257 Windows Baltic",
        "1258 Vietnamese",
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        "874 Thai",
        "932 JIS/Japan",
        "936 Chinese: Simplified chars--PRC and Singapore",
        "949 Korean Wansung",
        "950 Chinese: Traditional chars--Taiwan and Hong Kong",
        "1361 Korean Johab",
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        "Macintosh Character Set (US Roman)",
        "OEM Character Set",
        "Symbol Character Set",
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        "869 IBM Greek",
        "866 MS-DOS Russian",
        "865 MS-DOS Nordic",
        "864 Arabic",
        "863 MS-DOS Canadian French",
        "862 Hebrew",
        "861 MS-DOS Icelandic",
        "860 MS-DOS Portuguese",
        "857 IBM Turkish",
        "855 IBM Cyrillic; primarily Russian",
        "852 Latin 2",
        "775 MS-DOS Baltic",
        "737 Greek; former 437 G",
        "708 Arabic; ASMO 708",
        "850 WE/Latin 1",
        "437 US");

    protected $justNames = FALSE;
    /** Contains the location of the several tables. The key is the name of
    * the table and the value is an <CODE>int[2]</CODE> where position 0
    * is the offset from the start of the file and position 1 is the length
    * of the table.
    */
    protected $tables = array();
    /** The file in use.
    */
    protected $rf;
    /** The file name.
    */
    protected $fileName = "";

    protected $cff = FALSE;

    protected $cffOffset = 0;

    protected $cffLength = 0;

    /** The offset from the start of the file to the table directory.
    * It is 0 for TTF and may vary for TTC depending on the chosen font.
    */
    protected $directoryOffset = 0;
    /** The index for the TTC font. It is an empty <CODE>String</CODE> for a
    * TTF file.
    */
    protected $ttcIndex = "";
    /** The style modifier */
    protected $style = "";
    /** The content of table 'head'.
    */
    protected $head = NULL;
    /** The content of table 'hhea'.
    */
    protected $hhea = NULL;
    /** The content of table 'OS/2'.
    */
    protected $os_2 = NULL);
    /** The width of the glyphs. This is essentially the content of table
     * 'hmtx' normalized to 1000 units.
     */
    protected $GlyphWidths = array();

    protected $bboxes;
    /** The map containing the code information for the table 'cmap', encoding 1.0.
    * The key is the code and the value is an <CODE>int[2]</CODE> where position 0
    * is the glyph number and position 1 is the glyph width normalized to 1000
    * units.
    */
    protected $cmap10 = array();
    /** The map containing the code information for the table 'cmap', encoding 3.1
    * in Unicode.
    * <P>
    * The key is the code and the value is an <CODE>int</CODE>[2] where position 0
    * is the glyph number and position 1 is the glyph width normalized to 1000
    * units.
    */
    protected $cmap31 = array();
    /** The map containing the kerning information. It represents the content of
    * table 'kern'. The key is an <CODE>Integer</CODE> where the top 16 bits
    * are the glyph number for the first character and the lower 16 bits are the
    * glyph number for the second character. The value is the amount of kerning in
    * normalized 1000 units as an <CODE>Integer</CODE>. This value is usually negative.
    */
    protected $kerning = array();
    /**
    * The font name.
    * This name is usually extracted from the table 'name' with
    * the 'Name ID' 6.
    */
    protected $fontName;

    /** The full name of the font
    */
    protected $fullName;

    /** The family name of the font
    */
    protected $familyName;
    /** The italic angle. It is usually extracted from the 'post' table or in it's
    * absence with the code:
    * <P>
    * <PRE>
    * -Math.atan2(hhea.caretSlopeRun, hhea.caretSlopeRise) * 180 / Math.PI
    * </PRE>
    */
    protected $italicAngle = 0.0;
    /** <CODE>true</CODE> if all the glyphs have the same width.
    */
    protected $isFixedPitch = FALSE;



    public function __construct()
    {
        $head = new FontHeader();
        $hhea = new HorizontalHeader();
        $os_2 = new WindowsMetrics();

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                construct4args($arg1, $arg2, $arg3, $arg4);
                break; 
            }
            case 5:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                construct5args($arg1, $arg2, $arg3, $arg4, $arg5);
                break; 
            }
        }
    }

    
    private function construct4args($ttFile, $enc, $emb, $ttfAfm)
    {
       construct5args($ttFile, $enc, $emb, $ttfAfm, FALSE);
    }

    /** Creates a new TrueType font.
    * @param ttFile the location of the font on file. The file must end in '.ttf' or
    * '.ttc' but can have modifiers after the name
    * @param enc the encoding to be applied to this font
    * @param emb true if the font is to be embedded in the PDF
    * @param ttfAfm the font as a <CODE>byte</CODE> array
    * @throws DocumentException the font is invalid
    * @throws IOException the font file could not be read
    */
    private function construct5args($ttFile, $enc, $emb, $ttfAfm, $justNames) 
    {
        $this->justNames = $justNames;
        $nameBase = BaseFont::getBaseName($ttFile);
        $ttcName = TrueTypeFont::getTTCName($nameBase);
        if (strlen($nameBase) < strlen($ttFile)) {
            $style = substr($ttFile, strlen($nameBase));
        }
        $encoding = $enc;
        $embedded = $emb;
        $fileName = $ttcName;
        $fontType = BaseFont::FONT_TYPE_TT;
        $ttcIndex = "";
        if (strlen($ttcName) < strlen($nameBase))
            $ttcIndex = substr($nameBase, strlen($ttcName) + 1);
        if (endsWiths(strtolower($fileName), ".ttf") == TRUE || endsWith(strtolower($fileName), ".otf") == TRUE|| endsWith(strtolower($fileName), ".ttc") == TRUE) {
            process($ttfAfm);
            if ($justNames == FALSE && $embedded == TRUE && $os_2->fsType == 2)
                throw new DocumentException($fileName . $style . " cannot be embedded due to licensing restrictions.");
        }
        else
            throw new DocumentException($fileName . $style . " is not a TTF, OTF or TTC font file.");
        PdfEncodings::convertToBytes(" ", $enc); // check if the encoding exists
        createEncoding();
    }


    /** Gets the name from a composed TTC file name.
    * If I have for input "myfont.ttc,2" the return will
    * be "myfont.ttc".
    * @param name the full name
    * @return the simple file name
    */
    protected static function getTTCName($name) {
        $idx = strpos(strtolower($name), ".ttc,");
        if ($idx < 0)
            return $name;
        else
            return substr($name, 0, $idx + 4);
    }

    /**
    * Reads the tables 'head', 'hhea', 'OS/2' and 'post' filling several variables.
    * @throws DocumentException the font is invalid
    * @throws IOException the font file could not be read
    */
    function fillTables() 
    {
        $table_location = NULL;
        $table_location = $tables["head"];
        if ($table_location == NULL)
            throw new DocumentException("Table 'head' does not exist in " + fileName + style);
        $rf->seek($table_location[0] + 16);
        $head->flags = $rf->readUnsignedShort();
        $head->unitsPerEm = $rf->readUnsignedShort();
        $rf->skipBytes(16);
        $head->xMin = $rf->readShort();
        $head->yMin = $rf->readShort();
        $head->xMax = $rf->readShort();
        $head->yMax = $rf->readShort();
        $head->macStyle = $rf->readUnsignedShort();

        $table_location = $tables["hhea"];
        if ($table_location == NULL)
            throw new DocumentException("Table 'hhea' does not exist " . $fileName . $style);
        $rf->seek(table_location[0] + 4);
        $hhea->Ascender = $rf->readShort();
        $hhea->Descender = $rf->readShort();
        $hhea->LineGap = $rf->readShort();
        $hhea->advanceWidthMax = $rf->readUnsignedShort();
        $hhea->minLeftSideBearing = $rf->readShort();
        $hhea->minRightSideBearing = $rf->readShort();
        $hhea->xMaxExtent = $rf->readShort();
        $hhea->caretSlopeRise = $rf->readShort();
        $hhea->caretSlopeRun = $rf->readShort();
        $rf->skipBytes(12);
        $hhea->numberOfHMetrics = $rf->readUnsignedShort();

        $table_location = $tables["OS/2"];
        if ($table_location == NULL)
            throw new DocumentException("Table 'OS/2' does not exist in " . $fileName . $style);
        $rf->seek($table_location[0]);
        $version = $rf->readUnsignedShort();
        $os_2->xAvgCharWidth = $rf->readShort();
        $os_2->usWeightClass = $rf->readUnsignedShort();
        $os_2->usWidthClass = $rf->readUnsignedShort();
        $os_2->fsType = $rf->readShort();
        $os_2->ySubscriptXSize = $rf->readShort();
        $os_2->ySubscriptYSize = $rf->readShort();
        $os_2->ySubscriptXOffset = $rf->readShort();
        $os_2->ySubscriptYOffset = $rf->readShort();
        $os_2->ySuperscriptXSize = $rf->readShort();
        $os_2->ySuperscriptYSize = $rf->readShort();
        $os_2->ySuperscriptXOffset = $rf->readShort();
        $os_2->ySuperscriptYOffset = $rf->readShort();
        $os_2->yStrikeoutSize = $rf->readShort();
        $os_2->yStrikeoutPosition = $rf->readShort();
        $os_2->sFamilyClass = $rf->readShort();
        $rf->readFully($os_2->panose);
        $rf->skipBytes(16);
        $rf->readFully($os_2->achVendID);
        $os_2->fsSelection = $rf->readUnsignedShort();
        $os_2->usFirstCharIndex = $rf->readUnsignedShort();
        $os_2->usLastCharIndex = $rf->readUnsignedShort();
        $os_2->sTypoAscender = $rf->readShort();
        $os_2->sTypoDescender = $rf->readShort();
        if ($os_2->sTypoDescender > 0)
            $os_2->sTypoDescender = -$os_2->sTypoDescender;
        $os_2->sTypoLineGap = $rf->readShort();
        $os_2->usWinAscent = $rf->readUnsignedShort();
        $os_2->usWinDescent = $rf->readUnsignedShort();
        $os_2->ulCodePageRange1 = 0;
        $os_2->ulCodePageRange2 = 0;
        if ($version > 0) {
            $os_2->ulCodePageRange1 = $rf->readInt();
            $os_2->ulCodePageRange2 = $rf->readInt();
        }
        if ($version > 1) {
            $rf->skipBytes(2);
            $os_2->sCapHeight = $rf->readShort();
        }
        else
            $os_2->sCapHeight = 0.7 * $head->unitsPerEm;

        $table_location = $tables["post"];
        if ($table_location == NULL) {
            $italicAngle = -atan2($hhea->caretSlopeRun, $hhea->caretSlopeRise) * 180 / M_PI;
            return;
        }
        $rf->seek($table_location[0] + 4);
        $mantissa = $rf->readShort();
        $fraction = $rf->readUnsignedShort();
        $italicAngle = (double)$mantissa + (double)$fraction / 16384.0;
        $rf->skipBytes(4);
        $isFixedPitch = $rf->readInt() != 0;
    }

    /**
    * Gets the Postscript font name.
    * @throws DocumentException the font is invalid
    * @throws IOException the font file could not be read
    * @return the Postscript font name
    */
    function getBaseFont() 
    {
        $table_location = NULL;
        $table_location = $tables["name"];
        if ($table_location == NULL)
            throw new DocumentException("Table 'name' does not exist in " . $fileName . $style);
        $rf->seek($table_location[0] + 2);
        $numRecords = $rf->readUnsignedShort();
        $startOfStorage = $rf->readUnsignedShort();
        for ($k = 0; $k < $numRecords; ++$k) {
            $platformID = $rf->readUnsignedShort();
            $platformEncodingID = $rf->readUnsignedShort();
            $languageID = $rf->readUnsignedShort();
            $nameID = $rf->readUnsignedShort();
            $length = $rf->readUnsignedShort();
            $offset = $rf->readUnsignedShort();
            if ($nameID == 6) {
                $rf->seek($table_location[0] + $startOfStorage + $offset);
                if ($platformID == 0 || $platformID == 3)
                    return readUnicodeString($length);
                else
                    return readStandardString($length);
            }
        }
        $file = fopen($fileName,"r+");
        return str_replace(' ', '-', basename($filename));

    }


    /** Extracts the names of the font in all the languages available.
    * @param id the name id to retrieve
    * @throws DocumentException on error
    * @throws IOException on error
    */
    function getNames($id) 
    {
        $table_location = NULL;
        $table_location = $tables["name"];
        if ($table_location == NULL)
            throw new DocumentException("Table 'name' does not exist in " . $fileName . $style);
        $rf->seek($table_location[0] + 2);
        $numRecords = $rf->readUnsignedShort();
        $startOfStorage = $rf->readUnsignedShort();
        $names = array();
        for ($k = 0; k < $numRecords; ++$k) {
            $platformID = $rf->readUnsignedShort();
            $platformEncodingID = $rf->readUnsignedShort();
            $languageID = $rf->readUnsignedShort();
            $nameID = $rf->readUnsignedShort();
            $length = $rf->readUnsignedShort();
            $offset = $rf->readUnsignedShort();
            if ($nameID == $id) {
                $pos = $rf->getFilePointer();
                $rf->seek($table_location[0] + $startOfStorage + $offset);
                $name = NULL;
                if ($platformID == 0 || $platformID == 3 || ($platformID == 2 && $platformEncodingID == 1)){
                    $name = readUnicodeString($length);
                }
                else {
                    $name = readStandardString($length);
                }
                array_push($names, array($platformID,$platformEncodingID,$languageID, $name));
                $rf->seek($pos);
            }
        }
        $thisName = NULL;
        for ($k = 0; $k < count($names); ++$k)
            $thisName[$k] = $names[$k];
        return $thisName;
    }

    function checkCff() 
    {
        $table_location = NULL;
        $table_location = $tables["CFF "];
        if ($table_location != NULL) {
            $cff = TRUE;
            $cffOffset = $table_location[0];
            $cffLength = $table_location[1];
        }
    }

    /** Reads the font data.
    * @param ttfAfm the font as a <CODE>byte</CODE> array, possibly <CODE>null</CODE>
    * @throws DocumentException the font is invalid
    * @throws IOException the font file could not be read
    */
    void process($ttfAfm) 
    {
        $tables = array();

        try {
            if ($ttfAfm == NULL)
                $rf = new RandomAccessFileOrArray($fileName);
            else
                $rf = new RandomAccessFileOrArray($ttfAfm);
            if (strlen($ttcIndex) > 0) {
                $dirIdx = (integer)$ttcIndex;
                if ($dirIdx < 0)
                    throw new DocumentException("The font index for " . $fileName . " must be positive.");
                $mainTag = readStandardString(4);
                if (strcmp($mainTag, "ttcf") != 0)
                    throw new DocumentException($fileName . " is not a valid TTC file.");
                $rf->skipBytes(4);
                $dirCount = $rf->readInt();
                if ($dirIdx >= $dirCount)
                    throw new DocumentException("The font index for " . $fileName . " must be between 0 and " . ($dirCount - 1) . ". It was " . $dirIdx . ".");
                $rf->skipBytes($dirIdx * 4);
                $directoryOffset = $rf->readInt();
            }
            $rf->seek($directoryOffset);
            $ttId = $rf->readInt();
            if ($ttId != 0x00010000 && $ttId != 0x4F54544F)
                throw new DocumentException($fileName . " is not a valid TTF or OTF file.");
            $num_tables = $rf->readUnsignedShort();
            $rf->skipBytes(6);
            for ($k = 0; $k < $num_tables; ++$k) {
                $tag = readStandardString(4);
                $rf->skipBytes(4);
                $table_location[] = array();
                $table_location[0] = $rf->readInt();
                $table_location[1] = $rf->readInt();
                $tables->put($tag, $table_location);
            }
            checkCff();
            $fontName = getBaseFont();
            $fullName = getNames(4); //full name
            $familyName = getNames(1); //family name
            if ($justNames == FALSE) {
                fillTables();
                readGlyphWidths();
                readCMaps();
                readKerning();
                readBbox();
                $GlyphWidths = null;
            }
        }
        catch (Exception $e)
        {
            if ($rf != NULL) {
                $rf->close();
                if ($embedded == FALSE)
                    $rf = NULL;
            }
            return;
        }

        if ($rf != NULL) {
            $rf->close();
            if ($embedded == FALSE)
                $rf = NULL;
        }
    }

    /** Reads a <CODE>String</CODE> from the font file as bytes using the Cp1252
    *  encoding.
    * @param length the length of bytes to read
    * @return the <CODE>String</CODE> read
    * @throws IOException the font file could not be read
    */
    protected function readStandardString($length)  {
        $buf = itextphp_bytes_create($length);
        $rf->readFully($buf);
        try {
            return itextphp_getAnsiString($buf, $length);/*new String(buf, WINANSI);*/
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /** Reads a Unicode <CODE>String</CODE> from the font file. Each character is
    *  represented by two bytes.
    * @param length the length of bytes to read. The <CODE>String</CODE> will have <CODE>length</CODE>/2
    * characters
    * @return the <CODE>String</CODE> read
    * @throws IOException the font file could not be read
    */
    protected function readUnicodeString($length)
    {
        $buf;
        $length /= 2;
        for ($k = 0; $k < $length; ++k) {
            $buf .= $rf->readChar();
        }
        return $buf;
    }

    /** Reads the glyphs widths. The widths are extracted from the table 'hmtx'.
    *  The glyphs are normalized to 1000 units.
    * @throws DocumentException the font is invalid
    * @throws IOException the font file could not be read
    */
    protected function readGlyphWidths() 
    {
        $table_location = NULL;
        $table_location = $tables["hmtx"];
        if ($table_location == NULL)
            throw new DocumentException("Table 'hmtx' does not exist in " . $fileName . $style);
        $rf->seek($table_location[0]);
        $GlyphWidths = array();
        for ($k = 0; $k < $hhea->numberOfHMetrics; ++$k) {
            $GlyphWidths[$k] = ($rf->readUnsignedShort() * 1000) / $head->unitsPerEm;
            $rf->readUnsignedShort();
        }
    }

    /** Gets a glyph width.
    * @param glyph the glyph to get the width of
    * @return the width of the glyph in normalized 1000 units
    */
    protected function getGlyphWidth($glyph) {
        if ($glyph >= count($GlyphWidths))
            $glyph = count($GlyphWidths) - 1;
        return $GlyphWidths[$glyph];
    }

    private function readBbox()
    {
        $tableLocation = NULL;
        $tableLocation = $tables["head"];
        if ($tableLocation == NULL)
            throw new DocumentException("Table 'head' does not exist in " . $fileName . $style);
        $rf->seek($tableLocation[0] . TrueTypeFontSubSet::HEAD_LOCA_FORMAT_OFFSET);
        $locaShortTable = ($rf->readUnsignedShort() == 0);
        $tableLocation = $tables["loca"];
        if ($tableLocation == NULL)
            return;
        $rf->seek($tableLocation[0]);
        $locaTable = NULL;
        if ($locaShortTable == TRUE) {
            $entries = $tableLocation[1] / 2;
            $locaTable = array();
            for ($k = 0; $k < $entries; ++$k)
                $locaTable[$k] = $rf->readUnsignedShort() * 2;
        }
        else {
            $entries = $tableLocation[1] / 4;
            $locaTable = array();
            for ($k = 0; $k < $entries; ++$k)
                $locaTable[$k] = $rf->readInt();
        }
        $tableLocation = $tables["glyf"];
        if ($tableLocation == NULL)
            throw new DocumentException("Table 'glyf' does not exist in " . $fileName . $style);
        $tableGlyphOffset = $tableLocation[0];
        $bboxes = array();
        for ($glyph = 0; $glyph < count($locaTable) - 1; ++$glyph) {
            $start = $locaTable[$glyph];
            if ($start != $locaTable[$glyph + 1]) {
                $rf->seek($tableGlyphOffset + $start + 2);
                $bboxes[$glyph] = array(
                    ($rf->readShort() * 1000) / $head->unitsPerEm,
                    ($rf->readShort() * 1000) / $head->unitsPerEm,
                    ($rf->readShort() * 1000) / $head->unitsPerEm,
                    ($rf->readShort() * 1000) / $head->unitsPerEm);
            }
        }
    }

    /** Reads the several maps from the table 'cmap'. The maps of interest are 1.0 for symbolic
    *  fonts and 3.1 for all others. A symbolic font is defined as having the map 3.0.
    * @throws DocumentException the font is invalid
    * @throws IOException the font file could not be read
    */
    function readCMaps() 
    {
        $table_location = $tables["cmap"];
        if ($table_location == NULL)
            throw new DocumentException("Table 'cmap' does not exist in " . $fileName . $style);
        $rf->seek($table_location[0]);
        $rf->skipBytes(2);
        $num_tables = $rf->readUnsignedShort();
        $fontSpecific = FALSE;
        $map10 = 0;
        $map31 = 0;
        $map30 = 0;
        for ($k = 0; $k < $num_tables; ++$k) {
            $platId = $rf->readUnsignedShort();
            $platSpecId = $rf->readUnsignedShort();
            $offset = $rf->readInt();
            if ($platId == 3 && $platSpecId == 0) {
                $fontSpecific = TRUE;
                $map30 = $offset;
            }
            else if ($platId == 3 && $platSpecId == 1) {
                $map31 = $offset;
            }
            if ($platId == 1 && $platSpecId == 0) {
                $map10 = $offset;
            }
        }
        if ($map10 > 0) {
            $rf->seek($table_location[0] + $map10);
            $format = $rf->readUnsignedShort();
            switch ($format) {
                case 0:
                    $cmap10 = readFormat0();
                    break;
                case 4:
                    $cmap10 = readFormat4();
                    break;
                case 6:
                    $cmap10 = readFormat6();
                    break;
            }
        }
        if ($map31 > 0) {
            $rf->seek($table_location[0] + $map31);
            $format = $rf->readUnsignedShort();
            if ($format == 4) {
                $cmap31 = readFormat4();
            }
        }
        if ($map30 > 0) {
            $rf->seek($table_location[0] + $map30);
            $format = $rf->readUnsignedShort();
            if ($format == 4) {
                $cmap10 = readFormat4();
            }
        }
    }

    /** The information in the maps of the table 'cmap' is coded in several formats.
    *  Format 0 is the Apple standard character to glyph index mapping table.
    * @return a <CODE>HashMap</CODE> representing this map
    * @throws IOException the font file could not be read
    */
    function readFormat0() 
    {
        $h = array();
        $rf->skipBytes(4);
        for ($k = 0; $k < 256; ++$k) {
            $r = array();
            $r[0] = $rf->readUnsignedByte();
            $r[1] = getGlyphWidth($r[0]);
            $h[$k] = $r;
        }
        return $h;
    }

    /** The information in the maps of the table 'cmap' is coded in several formats.
    *  Format 4 is the Microsoft standard character to glyph index mapping table.
    * @return a <CODE>HashMap</CODE> representing this map
    * @throws IOException the font file could not be read
    */
    HashMap readFormat4() 
    {
        $mask = ($fontSpecific ? 0xff : 0xffff);
        $h = array();
        $table_lenght = $rf->readUnsignedShort();
        $rf->skipBytes(2);
        $segCount = $rf->readUnsignedShort() / 2;
        $rf->skipBytes(6);
        $endCount = array();
        for ($k = 0; $k < $segCount; ++$k) {
            $endCount[$k] = $rf->readUnsignedShort();
        }
        $rf->skipBytes(2);
        $startCount = array();
        for ($k = 0; $k < $segCount; ++$k) {
            $startCount[$k] = $rf->readUnsignedShort();
        }
        $idDelta = array();
        for ($k = 0; $k < $segCount; ++$k) {
            $idDelta[$k] = $rf->readUnsignedShort();
        }
        $idRO = array();
        for ($k = 0; $k < $segCount; ++$k) {
            $idRO[$k] = $rf->readUnsignedShort();
        }
        $glyphId = array();
        for ($k = 0; $k < ($table_lenght / 2 - 8 - $segCount * 4); ++$k) {
            $glyphId[$k] = $rf.readUnsignedShort();
        }
        for ($k = 0; $k < $segCount; ++$k) {
            $glyph = 0;
            for ($j = $startCount[$k]; $j <= $endCount[$k] && $j != 0xFFFF; ++$j) {
                if ($idRO[$k] == 0) {
                    $glyph = ($j + $idDelta[$k]) & 0xFFFF;
                }
                else {
                    $idx = $k + $idRO[$k] / 2 - $segCount + $j - $startCount[$k];
                    if ($idx >= count($glyphId))
                        continue;
                    $glyph = ($glyphId[$idx] + $idDelta[$k]) & 0xFFFF;
                }
                $r = array();
                $r[0] = $glyph;
                $r[1] = $getGlyphWidth($r[0]);
                $h[($j & $mask)] = $r;
            }
        }
        return $h;
    }

    /** The information in the maps of the table 'cmap' is coded in several formats.
    *  Format 6 is a trimmed table mapping. It is similar to format 0 but can have
    *  less than 256 entries.
    * @return a <CODE>HashMap</CODE> representing this map
    * @throws IOException the font file could not be read
    */
    function readFormat6() 
    {
        $h = array();
        $rf->skipBytes(4);
        $start_code = $rf->readUnsignedShort();
        $code_count = $rf->readUnsignedShort();
        for ($k = 0; $k < $code_count; ++$k) {
            $r = array();
            $r[0] = $rf->readUnsignedShort();
            $r[1] = getGlyphWidth($r[0]);
            $h[($k + $start_code)] = $r;
        }
        return $h;
    }


    /** Reads the kerning information from the 'kern' table.
    * @throws IOException the font file could not be read
    */
    function readKerning() 
    {
        $table_location = $tables["kern"];
        if ($table_location == NULL)
            return;
        $rf->seek($table_location[0] + 2);
        $nTables = $rf->readUnsignedShort();
        $checkpoint = $table_location[0] + 4;
        $length = 0;
        for ($k = 0; $k < $nTables; ++$k) {
            $checkpoint += $length;
            $rf->seek($checkpoint);
            $rf->skipBytes(2);
            $length = $rf->readUnsignedShort();
            $coverage = $rf->readUnsignedShort();
            if (($coverage & 0xfff7) == 0x0001) {
                $nPairs = $rf->readUnsignedShort();
                $rf->skipBytes(6);
                for ($j = 0; $j < $nPairs; ++$j) {
                    $pair = $rf->readInt();
                    $value = ((integer)$rf->readShort() * 1000) / $head->unitsPerEm;
                    $kerning[$pair] = $value;
                }
            }
        }
    }

    /** Gets the kerning between two Unicode chars.
    * @param char1 the first char
    * @param char2 the second char
    * @return the kerning to be applied
    */
    public function getKerning($char1, $char2) {
        $metrics = getMetricsTT($char1);
        if ($metrics == NULL)
            return 0;
        $c1 = $metrics[0];
        $metrics = getMetricsTT($char2);
        if ($metrics == NULL)
            return 0;
        $c2 = $metrics[0];
        return $kerning[($c1 << 16) + $c2];
    }

    /** Gets the width from the font according to the unicode char <CODE>c</CODE>.
    * If the <CODE>name</CODE> is null it's a symbolic font.
    * @param c the unicode char
    * @param name the glyph name
    * @return the width of the char
    */
    function getRawWidth($c, $name) {
        $map = NULL;
        if ($name == NULL || $cmap31 == NULL)
            $map = $cmap10;
        else
            $map = $cmap31;
        if ($map == NULL)
            return 0;
        $metric = $map[$c];
        if ($metric == NULL)
            return 0;
        return $metric[1];
    }

     /** Generates the font descriptor for this font.
     * @return the PdfDictionary containing the font descriptor or <CODE>null</CODE>
     * @param subsetPrefix the subset prefix
     * @param fontStream the indirect reference to a PdfStream containing the font or <CODE>null</CODE>
     * @throws DocumentException if there is an error
     */
    protected function getFontDescriptor(PdfIndirectReference $fontStream, $subsetPrefix) 
    {
        $dic = new PdfDictionary(PdfName::$FONTDESCRIPTOR);
        $dic->put(PdfName::$ASCENT, new PdfNumber((integer)$os_2->sTypoAscender * 1000 / $head->unitsPerEm));
        $dic->put(PdfName::$CAPHEIGHT, new PdfNumber((integer)$os_2->sCapHeight * 1000 / $head->unitsPerEm));
        $dic->put(PdfName::$DESCENT, new PdfNumber((integer)$os_2->sTypoDescender * 1000 / $head->unitsPerEm));
        $dic->put(PdfName::$FONTBBOX, new PdfRectangle(
        (integer)$head->xMin * 1000 / $head->unitsPerEm,
        (integer)$head->yMin * 1000 / $head->unitsPerEm,
        (integer)$head->xMax * 1000 / $head->unitsPerEm,
        (integer)$head->yMax * 1000 / $head->unitsPerEm));
        if ($cff == TRUE) {
            if (beginsWith($encoding, "Identity-") == TRUE)
                $dic->put(PdfName::$FONTNAME, new PdfName($fontName . "-" . $encoding));
            else
                $dic->put(PdfName::$FONTNAME, new PdfName($fontName . $style));
        }
        else
            $dic->put(PdfName::$FONTNAME, new PdfName($subsetPrefix . $fontName . $style));
        $dic->put(PdfName::$ITALICANGLE, new PdfNumber($italicAngle));
        $dic->put(PdfName::$STEMV, new PdfNumber(80));
        if ($fontStream != NULL) {
            if ($cff == TRUE)
                $dic->put(PdfName::$FONTFILE3, $fontStream);
            else
                $dic->put(PdfName::$FONTFILE2, $fontStream);
        }
        $flags = 0;
        if ($isFixedPitch == TRUE)
            $flags |= 1;
        $flags |= $fontSpecific == TRUE ? 4 : 32;
        if (($head->macStyle & 2) != 0)
            $flags |= 64;
        if (($head->macStyle & 1) != 0)
            $flags |= 262144;
        $dic->put(PdfName::$FLAGS, new PdfNumber($flags));

        return $dic;
    }


    /** Generates the font dictionary for this font.
    * @return the PdfDictionary containing the font dictionary
    * @param subsetPrefix the subset prefx
    * @param firstChar the first valid character
    * @param lastChar the last valid character
    * @param shortTag a 256 bytes long <CODE>byte</CODE> array where each unused byte is represented by 0
    * @param fontDescriptor the indirect reference to a PdfDictionary containing the font descriptor or <CODE>null</CODE>
    * @throws DocumentException if there is an error
    */
    protected function getFontBaseType(PdfIndirectReference $fontDescriptor, $subsetPrefix, $firstChar, $lastChar, $shortTag) 
    {
        $dic = new PdfDictionary(PdfName::$FONT);
        if ($cff == TRUE) {
            $dic->put(PdfName::$SUBTYPE, PdfName::$TYPE1);
            $dic->put(PdfName::$BASEFONT, new PdfName($fontName . $style));
        }
        else {
            $dic->put(PdfName::$SUBTYPE, PdfName::$TRUETYPE);
            $dic->put(PdfName::$BASEFONT, new PdfName($subsetPrefix . $fontName . $style));
        }
        $dic->put(PdfName::$BASEFONT, new PdfName($subsetPrefix . $fontName . $style));
        if ($fontSpecific == FALSE) {
            for ($k = $firstChar; $k <= $lastChar; ++$k) {
                if (strcmp($differences[$k], BaseFont::notdef) != 0) {
                    $firstChar = $k;
                    break;
                }
            }
        if (strcmp($encoding, "Cp1252") == 0 || strcmp($encoding, "MacRoman") == 0)
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
                        $gap = TUR;
                }
                $enc->put(PdfName::$DIFFERENCES, $dif);
                $dic->put(PdfName::$ENCODING, $enc);
            }
        }
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
        if ($fontDescriptor != NULL)
            $dic->put(PdfName::$FONTDESCRIPTOR, $fontDescriptor);
        return $dic;
    }

    private function getFullFont()  {
        $rf2 = new RandomAccessFileOrArray($rf);
        $rf2->reOpen();
        $b = itextphp_bytes_create($rf2->length);
        $rf2->readFully($b);
        $rf2->close();
        return $b;
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
                itextphp_bytes_update($shortTag, $k, 1);
        }
        $ind_font = NULL;
        $pobj = NULL;
        $obj = NULL;
        $subsetPrefix = "";
        if ($embedded == TRUE) {
            if ($cff == TRUE) {
                $rf2 = new RandomAccessFileOrArray($rf);
                $b = itextphp_bytes_create($cffLength);
                try {
                    $rf2->reOpen();
                    $rf2->seek($cffOffset);
                    $rf2->readFully($b);
                }
                catch {
                    try {
                        $rf2->close();
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
                }

                try {
                        $rf2->close();
                }
                catch (Exception $e) {
                        // empty on purpose
                }
                $pobj = new StreamFont($b, "Type1C");
                $obj = $writer->addToBody($pobj);
                $ind_font = $obj->getIndirectReference();
            }
            else {
                if ($subset == TRUE)
                    subsetPrefix = BaseFont::createSubsetPrefix();
                $glyphs = array();
                for ($k = $firstChar; $k <= $lastChar; ++$k) {
                    if (itextphp_bytes_getIntValue($shortTag, $k) != 0) {
                        $metrics = NULL;
                        if ($fontSpecific == TRUE)
                            $metrics = getMetricsTT($k);
                        else
                            $metrics = getMetricsTT($unicodeDifferences[$k]);
                        if ($metrics != NULL)
                            $glyphs[$metrics[0] = NULL;
                    }
                }
                $sb = new TrueTypeFontSubSet($fileName, new RandomAccessFileOrArray($rf), $glyphs, $directoryOffset, TRUE);
                $b = $sb->process();
                $lengths = array(itextphp_bytes_getSize($b));
                $pobj = new StreamFont($b, $lengths);
                $obj = $writer->addToBody(pobj);
                $ind_font = $obj->getIndirectReference();
            }
        }
        $pobj = getFontDescriptor($ind_font, $subsetPrefix);
        if ($pobj != NULL){
            $obj = $writer->addToBody(pobj);
            $ind_font = $obj->getIndirectReference();
        }
        $pobj = getFontBaseType($ind_font, $subsetPrefix, $firstChar, $lastChar, $shortTag);
        $writer->addToBody($pobj, $ref);
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
            case BaseFont::ASCENT:
                return (float)$os_2->sTypoAscender * $fontSize / (float)$head->unitsPerEm;
            case BaseFont::CAPHEIGHT:
                return (float)$os_2->sCapHeight * $fontSize / (float)$head->unitsPerEm;
            case BaseFont::DESCENT:
                return (float)$os_2->sTypoDescender * $fontSize / (float)$head->unitsPerEm;
            case BaseFont::ITALICANGLE:
                return (float)$italicAngle;
            case BaseFont::BBOXLLX:
                return $fontSize * (integer)$head->xMin / $head->unitsPerEm;
            case BaseFont::BBOXLLY:
                return $fontSize * (integer)$head->yMin / $head->unitsPerEm;
            case BaseFont::BBOXURX:
                return $fontSize * (integer)$head->xMax / $head->unitsPerEm;
            case BaseFont::BBOXURY:
                return $fontSize * (integer)$head->yMax / $head->unitsPerEm;
            case BaseFont::AWT_ASCENT:
                return $fontSize * (integer)$hhea->Ascender / $head->unitsPerEm;
            case BaseFont::AWT_DESCENT:
                return $fontSize * (integer)$hhea->Descender / $head->unitsPerEm;
            case BaseFont::AWT_LEADING:
                return $fontSize * (integer)$hhea->LineGap / $head->unitsPerEm;
            case BaseFont::AWT_MAXADVANCE:
                return $fontSize * (integer)$hhea->advanceWidthMax / $head->unitsPerEm;
        }
        return 0;
    }

    /** Gets the glyph index and metrics for a character.
    * @param c the character
    * @return an <CODE>int</CODE> array with {glyph index, width}
    */
    public function getMetricsTT($c) {
        if ($fontSpecific == FALSE && $cmap31 != NULL) 
            return $cmap31[$c];
        if ($fontSpecific == TRUE && $cmap10 != NULL) 
            return $cmap10[$c];
        if ($cmap31 != NULL) 
            return $cmap31[$c];
        if ($cmap10 != NULL) 
            return $cmap10[$c];
        return NULL;
    }

    /** Gets the postscript font name.
    * @return the postscript font name
    */
    public Function getPostscriptFontName() {
        return $fontName;
    }

    /** Gets the code pages supported by the font.
    * @return the code pages supported by the font
    */
    public function getCodePagesSupported() {
        $cp = (($os_2->ulCodePageRange2) << 32) + ($os_2->ulCodePageRange1 & 0xffffffffL);
        $count = 0;
        $bit = 1;
        for ($k = 0; $k < 64; ++$k) {
            if (($cp & $bit) != 0 && $codePages[$k] != NULL)
                ++$count;
            $bit <<= 1;
        }
        $ret = array();
        $count = 0;
        $bit = 1;
        for ($k = 0; $k < 64; ++$k) {
            if (($cp & $bit) != 0 && $codePages[$k] != NULL)
                $ret[$count++] = $codePages[$k];
            $bit <<= 1;
        }
        return $ret;
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
        return $fullName;
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
        return $familyName;
    }

    /** Checks if the font has any kerning pairs.
    * @return <CODE>true</CODE> if the font has any kerning pairs
    */
    public function hasKernPairs() {
        return count($kerning) > 0;
    }

    /**
    * Sets the font name that will appear in the pdf font dictionary.
    * Use with care as it can easily make a font unreadable if not embedded.
    * @param name the new font name
    */
    public function setPostscriptFontName($name) {
        $fontName = $name;
    }

    /**
    * Sets the kerning between two Unicode chars.
    * @param char1 the first char
    * @param char2 the second char
    * @param kern the kerning to apply in normalized 1000 units
    * @return <code>true</code> if the kerning was applied, <code>false</code> otherwise
    */
    public function setKerning($char1, $char2, $kern) {
        $metrics = getMetricsTT($char1);
        if ($metrics == NULL)
            return FALSE;
        $c1 = $metrics[0];
        $metrics = getMetricsTT($char2);
        if ($metrics == NULL)
            return FALSE;
        $c2 = $metrics[0];
        $kerning[($c1 << 16) + $c2] = $kern;
        return TRUE;
    }

    protected function getRawCharBBox($c, $name) {
        $map = NULL;
        if ($name == NULL || $cmap31 == NULL)
            $map = $cmap10;
        else
            $map = $cmap31;
        if ($map == NULL)
            return NULL;
        $metric = $map[$c];
        if ($metric == NULL || $bboxes == NULL)
            return NULL;
        return $bboxes[$metric[0]];
    }

}




/** The components of table 'head'.
*/
class FontHeader 
{
    /** A variable. */
    $flags = 0;
    /** A variable. */
    $unitsPerEm = 0;;
    /** A variable. */
    $xMin;
    /** A variable. */
    $yMin;
    /** A variable. */
    $xMax;
    /** A variable. */
    $yMax;
    /** A variable. */
    $macStyle = 0;
    }

/** The components of table 'hhea'.
*/
class HorizontalHeader 
{
    /** A variable. */
    $Ascender;
    /** A variable. */
    $Descender;
    /** A variable. */
    $LineGap;
    /** A variable. */
    $advanceWidthMax = 0;
    /** A variable. */
    $minLeftSideBearing;
    /** A variable. */
    $minRightSideBearing;
    /** A variable. */
    $xMaxExtent;
    /** A variable. */
    $caretSlopeRise;
    /** A variable. */
    $caretSlopeRun;
    /** A variable. */
    $numberOfHMetrics = 0;
    }

/** The components of table 'OS/2'.
*/
protected static class WindowsMetrics {
    /** A variable. */
    $xAvgCharWidth;
    /** A variable. */
    $usWeightClass = 0;
    /** A variable. */
    $usWidthClass = 0;
    /** A variable. */
    $fsType;
    /** A variable. */
    $ySubscriptXSize;
    /** A variable. */
    $ySubscriptYSize;
    /** A variable. */
    $ySubscriptXOffset;
    /** A variable. */
    $ySubscriptYOffset;
    /** A variable. */
    $ySuperscriptXSize;
    /** A variable. */
    $ySuperscriptYSize;
    /** A variable. */
    $ySuperscriptXOffset;
    /** A variable. */
    $ySuperscriptYOffset;
    /** A variable. */
    $yStrikeoutSize;
    /** A variable. */
    $yStrikeoutPosition;
    /** A variable. */
    $sFamilyClass;
    /** A variable. */
    $panose;
    /** A variable. */
    $achVendID;
    /** A variable. */
    $fsSelection = 0;
    /** A variable. */
    $usFirstCharIndex = 0;
    /** A variable. */
    $usLastCharIndex = 0;
    /** A variable. */
    $sTypoAscender;
    /** A variable. */
    $sTypoDescender;
    /** A variable. */
    $sTypoLineGap;
    /** A variable. */
    $usWinAscent = 0;
    /** A variable. */
    $usWinDescent = 0;
    /** A variable. */
    $ulCodePageRange1 = 0;
    /** A variable. */
    $ulCodePageRange2 = 0;
    /** A variable. */
    $sCapHeight = 0;

    function __construct()
    {
        $panose = itextphp_bytes_create(10);
        $achVendID = itextphp_bytes_create(4);
    }
}


?>