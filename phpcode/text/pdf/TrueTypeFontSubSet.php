<?PHP
/*
 * $Id: TrueTypeFontSubSet.php,v 1.2 2005/10/25 21:06:22 mstaylor Exp $
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
require_once("RandomAccessFileOrArray.php");
require_once("BaseFont.php");
require_once("PdfEncodings.php");

/** Subsets a True Type font by removing the unneeded glyphs from
* the font.
*
* @author  Paulo Soares (psoares@consiste.pt)
*/

class TrueTypeFontSubSet
{

    static $tableNamesSimple = array("cvt ", "fpgm", "glyf", "head",
        "hhea", "hmtx", "loca", "maxp", "prep");
    static $tableNamesCmap = array("cmap", "cvt ", "fpgm", "glyf", "head",
        "hhea", "hmtx", "loca", "maxp", "prep");
    static $entrySelectors = array(0,0,1,1,2,2,2,2,3,3,3,3,3,3,3,3,4,4,4,4,4);
    static $TABLE_CHECKSUM = 0;
    static $TABLE_OFFSET = 1;
    static $TABLE_LENGTH = 2;
    static $HEAD_LOCA_FORMAT_OFFSET = 51;

    static $ARG_1_AND_2_ARE_WORDS = 1;
    static $WE_HAVE_A_SCALE = 8;
    static $MORE_COMPONENTS = 32;
    static $WE_HAVE_AN_X_AND_Y_SCALE = 64;
    static $WE_HAVE_A_TWO_BY_TWO = 128;


    /** Contains the location of the several tables. The key is the name of
    * the table and the value is an <CODE>int[3]</CODE> where position 0
    * is the checksum, position 1 is the offset from the start of the file
    * and position 2 is the length of the table.
    */
    protected $tableDirectory = array();
    /** The file in use.
     */
    protected $rf = NULL;
    /** The file name.
     */
    protected $fileName = NULL;
    protected $includeCmap = FALSE;
    protected $locaShortTable = FALSE;
    protected $locaTable = array();
    protected $glyphsUsed = array();
    protected $glyphsInList = array();
    protected $tableGlyphOffset = 0;
    protected $newLocaTable = array();
    protected $newLocaTableOut = NULL;
    protected $newGlyfTable = NULL;
    protected $glyfTableRealSize = 0;
    protected $locaTableRealSize = 0; 
    protected $outFont = NULL;
    protected $fontPtr = 0;
    protected $directoryOffset = 0;

    /** Creates a new TrueTypeFontSubSet
    * @param directoryOffset The offset from the start of the file to the table directory
    * @param fileName the file name of the font
    * @param glyphsUsed the glyphs used
    * @param includeCmap <CODE>true</CODE> if the table cmap is to be included in the generated font
    */
    function __construct($fileName, RandomAccessFileOrArray $rf, array $glyphsUsed, $directoryOffset, $includeCmap) {
        $this->fileName = $fileName;
        $this->rf = $rf;
        $this->glyphsUsed = $glyphsUsed;
        $this->includeCmap = $includeCmap;
        $this->directoryOffset = $directoryOffset;
        $glyphsInList = array_merge(array(), array_keys($glyphsUsed));
    }


    /** Does the actual work of subsetting the font.
    * @throws IOException on error
    * @throws DocumentException on error
    * @return the subset font
    */
    function process() {
        try {
            $rf->reOpen();
            createTableDirectory();
            readLoca();
            flatGlyphs();
            createNewGlyphTables();
            locaTobytes();
            assembleFont();
            return $outFont;
        }
        catch (Exception $e){
            try {
                $rf->close();
                return;
            }
            catch (Exception $e) {
                // empty on purpose
                return;
            }
        }


        try 
        {
            $rf->close();
            return;
        }
        catch (Exception $e) {
            // empty on purpose
        }
    }


    protected function assembleFont() {
        $tableLocation = array();
        $fullFontSize = 0;
        $tableNames = array();
        if ($includeCmap == TRUE)
            $tableNames = $tableNamesCmap;
        else
            $tableNames = $tableNamesSimple;
        $tablesUsed = 2;
        $len = 0;
        for ($k = 0; $k < count($tableNames); ++$k) {
            $name = $tableNames[$k];
            if (strcmp($name, "glyf") == 0 || strcmp($name, "loca") == 0)
                continue;
            $tableLocation = $tableDirectory[$name];
            if ($tableLocation == NULL)
                continue;
            ++$tablesUsed;
            $fullFontSize += ($tableLocation[TrueTypeFontSubSet::$TABLE_LENGTH] + 3) & (~3);
        }
        $fullFontSize += itextphp_bytes_getSize($newLocaTableOut);
        $fullFontSize += itextphp_bytes_getSize($newGlyfTable);
        $ref = 16 * $tablesUsed + 12;
        $fullFontSize += $ref;
        $outFont = itextphp_bytes_create($fullFontSize);
        $fontPtr = 0;
        writeFontInt(0x00010000);
        writeFontShort($tablesUsed);
        $selector = $entrySelectors[$tablesUsed];
        writeFontShort((1 << $selector) * 16);
        writeFontShort($selector);
        writeFontShort(($tablesUsed - (1 << $selector)) * 16);
        for ($k = 0; $k < count($tableNames); ++$k) {
            $name = $tableNames[$k];
            $tableLocation = $tableDirectory[$name];
            if ($tableLocation == NULL)
                continue;
            writeFontString($name);
            if (strcmp($name, "glyf") == 0) {
                writeFontInt(calculateChecksum($newGlyfTable));
                $len = $glyfTableRealSize;
            }
            else if (strcmp($name, "loca") == 0) {
                writeFontInt(calculateChecksum($newLocaTableOut));
                $len = $locaTableRealSize;
            }
            else {
                writeFontInt($tableLocation[TrueTypeFontSubSet::$TABLE_CHECKSUM]);
                $len = $tableLocation[TrueTypeFontSubSet::$TABLE_LENGTH];
            }
            writeFontInt($ref);
            writeFontInt($len);
            $ref += ($len + 3) & (~3);
        }
        for ($k = 0; $k < count($tableNames); ++%k) {
            $name = $tableNames[$k];
            $tableLocation = $tableDirectory[$name];
            if ($tableLocation == NULL)
                continue;
            if (strcmp($name, "glyf") == 0) {
                $tmpFontPtr = $fontPtr;
                for ($k = 0; $k < itextphp_bytes_getSize($newGlyfTable); $k++)
                {
                    itextphp_bytes_write($outFont, $tmpFontPtr, $newGlyfTable, $k);
                    $tmpFontPtr++;
                }
                $fontPtr += itextphp_bytes_getSize($newGlyfTable);
                $newGlyfTable = NULL;
            }
            else if (strcmp($name, "loca") == 0) {
                $tmpFontPtr = $fontPtr;
                for ($k = 0; $k < itextphp_bytes_getSize($newLocaTableOut); $k++)
                {
                    itextphp_bytes_write($outFont, $tmpFontPtr, $newLocaTableOut, $k);
                    $tmpFontPtr++;
                }
                $fontPtr += itextphp_bytes_getSize($newLocaTableOut);
                $newLocaTableOut = NULL;
            }
            else {
                $rf->seek($tableLocation[TrueTypeFontSubSet::$TABLE_OFFSET]);
                $rf->readFully($outFont, $fontPtr, $tableLocation[TrueTypeFontSubSet::$TABLE_LENGTH]);
                $fontPtr += ($tableLocation[TrueTypeFontSubSet::$TABLE_LENGTH] + 3) & (~3);
            }
        }
    }


    protected function createTableDirectory(){
        $tableDirectory = array();
        $rf->seek($directoryOffset);
        $id = $rf->readInt();
        if ($id != 0x00010000)
            throw new DocumentException($fileName . " is not a true type file.");
        $num_tables = $rf->readUnsignedShort();
        $rf->skipBytes(6);
        for ($k = 0; $k < $num_tables; ++$k) {
            $tag = readStandardString(4);
            $tableLocation = array();
            $tableLocation[TrueTypeFontSubSet::$TABLE_CHECKSUM] = $rf->readInt();
            $tableLocation[TrueTypeFontSubSet::$TABLE_OFFSET] = $rf->readInt();
            $tableLocation[TrueTypeFontSubSet::$TABLE_LENGTH] = $rf->readInt();
            $tableDirectory[$tag] = $tableLocation;
        }
    }


    protected function readLoca() {
        $tableLocation = array();
        $tableLocation = $tableDirectory["head"];
        if ($tableLocation == NULL)
            throw new DocumentException("Table 'head' does not exist in " . $fileName);
        $rf->seek($tableLocation[TrueTypeFontSubSet::$TABLE_OFFSET] + TrueTypeFontSubSet::$HEAD_LOCA_FORMAT_OFFSET);
        $locaShortTable = ($rf->readUnsignedShort() == 0);
        $tableLocation = $tableDirectory["loca"];
        if ($tableLocation == NULL)
            throw new DocumentException("Table 'loca' does not exist in " . $fileName);
        $rf->seek($tableLocation[TrueTypeFontSubSet::$TABLE_OFFSET]);
        if ($locaShortTable == TRUE) {
            $entries = $tableLocation[TrueTypeFontSubSet::$TABLE_LENGTH] / 2;
            $locaTable = array();
            for ($k = 0; $k < $entries; ++$k)
                $locaTable[$k] = $rf->readUnsignedShort() * 2;
        }
        else {
            $entries = $tableLocation[TrueTypeFontSubSet::$TABLE_LENGTH] / 4;
            $locaTable = array();
            for ($k = 0; $k < $entries; ++$k)
                $locaTable[$k] = $rf->readInt();
        }
    }


     protected function createNewGlyphTables() {
        $newLocaTable = array();
        $activeGlyphs = array();
        for ($k = 0; $k < count($glyphsInList); ++$k)
            $activeGlyphs[$k] = ((integer)$glyphsInList[$k]);
        sort($activeGlyphs);
        $glyfSize = 0;
        for ($k = 0; $k < count($activeGlyphs); ++$k) {
            $glyph = $activeGlyphs[$k];
            $glyfSize += $locaTable[$glyph + 1] - $locaTable[$glyph];
        }
        $glyfTableRealSize = $glyfSize;
        $glyfSize = ($glyfSize + 3) & (~3);
        $newGlyfTable = itextphp_bytes_create($glyfSize);
        $glyfPtr = 0;
        $listGlyf = 0;
        for ($k = 0; $k < count($newLocaTable); ++$k) {
            $newLocaTable[$k] = $glyfPtr;
            if ($listGlyf < count($activeGlyphs) && $activeGlyphs[$listGlyf] == $k) {
                ++$listGlyf;
                $newLocaTable[$k] = $glyfPtr;
                $start = $locaTable[$k];
                $len = $locaTable[$k + 1] - $start;
                if ($len > 0) {
                    $rf->seek($tableGlyphOffset + $start);
                    $rf->readFully($newGlyfTable, $glyfPtr, $len);
                    $glyfPtr += $len;
                }
            }
        }
    }

    protected function locaTobytes() {
        if ($locaShortTable == TRUE)
            $locaTableRealSize = count($newLocaTable) * 2;
        else
            $locaTableRealSize = count($newLocaTable) * 4;
        $newLocaTableOut = itextphp_bytes_create(($locaTableRealSize + 3) & (~3));
        $outFont = $newLocaTableOut;
        $fontPtr = 0;
        for ($k = 0; $k < count($newLocaTable); ++$k) {
            if ($locaShortTable == TRUE)
                writeFontShort($newLocaTable[$k] / 2);
            else
                writeFontInt($newLocaTable[$k]);
        }

    }

    protected function flatGlyphs() {
        $tableLocation = array();
        $tableLocation = $tableDirectory["glyf"];
        if ($tableLocation == NULL)
            throw new DocumentException("Table 'glyf' does not exist in " + fileName);
        $glyph0 = 0;
        if (!array_key_exists($glyph0, $glyphsUsed) == TRUE) {
            $glyphsUsed[$glyph0] = NULL;
            array_push($glyphsInList, $glyph0);
        }
        $tableGlyphOffset = $tableLocation[TrueTypeFontSubSet::$TABLE_OFFSET];
        for ($k = 0; $k < count($glyphsInList); ++$k) {
            $glyph = ((integer)$glyphsInList[$k]);
            checkGlyphComposite($glyph);
        }
    }

    protected function checkGlyphComposite($glyph) {
        $start = $locaTable[$glyph];
        if ($start == $locaTable[$glyph + 1]) // no contour
            return;
        $rf->seek($tableGlyphOffset + $start);
        $numContours = $rf->readShort();
        if ($numContours >= 0)
            return;
        $rf->skipBytes(8);
        for(;;) {
            $flags = $rf->readUnsignedShort();
            $cGlyph = $rf.readUnsignedShort();
            if (!array_key_exists(cGlyph, $glyphsUsed) == TRUE) {
                $glyphsUsed[$cGlyph] = NULL;
                array_push($glyphsInList, $cGlyph);
            }
            if (($flags & TrueTypeFontSubSet::$MORE_COMPONENTS) == 0)
                return;
            $skip = 0;
            if (($flags & TrueTypeFontSubSet::$ARG_1_AND_2_ARE_WORDS) != 0)
                $skip = 4;
            else
                $skip = 2;
            if (($flags & TrueTypeFontSubSet::$WE_HAVE_A_SCALE) != 0)
                $skip += 2;
            else if (($flags & TrueTypeFontSubSet::$WE_HAVE_AN_X_AND_Y_SCALE) != 0)
                $skip += 4;
            if (($flags & TrueTypeFontSubSet::$WE_HAVE_A_TWO_BY_TWO) != 0)
                $skip += 8;
            $rf->skipBytes($skip);
        }
    }


    /** Reads a <CODE>String</CODE> from the font file as bytes using the Cp1252
    *  encoding.
    * @param length the length of bytes to read
    * @return the <CODE>String</CODE> read
    * @throws IOException the font file could not be read
    */
    protected function readStandardString($length){
        $buf = itextphp_bytes_create($length);
        $rf->readFully($buf);
        try {
            return itextphp_string_toPHPString(itextphp_byteToStringPDFDocEncoding($buf,0,BaseFont::WINANSI));
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }


    protected void writeFontShort($n) {
        itextphp_bytes_write($outFont, $fontPtr++, itextphp_bytes_createfromInt(($n >> 8)), 0);
        itextphp_bytes_write($outFont, $fontPtr++, itextphp_bytes_createfromInt(($n)), 0);
    }

    protected function writeFontInt($n) {
        itextphp_bytes_write($outFont, $fontPtr++, itextphp_bytes_createfromInt(($n >> 24)), 0);
        itextphp_bytes_write($outFont, $fontPtr++, itextphp_bytes_createfromInt(($n >> 16)), 0);
        itextphp_bytes_write($outFont, $fontPtr++, itextphp_bytes_createfromInt(($n >> 8)), 0);
        itextphp_bytes_write($outFont, $fontPtr++, itextphp_bytes_createfromInt(($n)), 0);
    }

    protected function writeFontString($s) {
        $b = PdfEncodings::convertToBytes($s, BaseFont::WINANSI);
        $tmpfontPtr = $fontPtr;
        for ($k = 0; $k < itextphp_bytes_getSize($b); $k++)
        {
            itextphp_bytes_write($outFont, $tmpfontPtr, $b, $k);
            $tmpfontPtr++;
        }

        $fontPtr += itextphp_bytes_getSize($b);
    }

    protected function calculateChecksum($b) {
        $len = itextphp_bytes_getSize($b) / 4;
        $v0 = 0;
        $v1 = 0;
        $v2 = 0;
        $v3 = 0;
        $ptr = 0;
        for ($k = 0; $k < $len; ++$k) {
            $v3 += (integer)itextphp_bytes_getIntValue($b, $ptr++) & 0xff;
            v2 += (integer)itextphp_bytes_getIntValue($b, $ptr++) & 0xff;
            v1 += (integer)itextphp_bytes_getIntValue($b, $ptr++) & 0xff;
            v0 += (integer)itextphp_bytes_getIntValue($b, $ptr++) & 0xff;
        }
        return $v0 + ($v1 << 8) + ($v2 << 16) + ($v3 << 24);
    }
}




?>