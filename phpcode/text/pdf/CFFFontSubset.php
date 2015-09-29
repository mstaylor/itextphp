<?PHP
/*
 * $Id: CFFFontSubset.php,v 1.4 2005/10/25 16:01:37 mstaylor Exp $
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

/**
* This Class subsets a CFF Type Font. The subset is preformed for CID fonts and NON CID fonts.
* The Charstring is subseted for both types. For CID fonts only the FDArray which are used are embedded. 
* The Lsubroutines of the FDArrays used are subsetted as well. The Subroutine subset supports both Type1 and Type2
* formatting altough only tested on Type2 Format.
* For Non CID the Lsubroutines are subsetted. On both types the Gsubroutines is subsetted. 
* A font which was not of CID type is transformed into CID as a part of the subset process. 
* The CID synthetic creation was written by Sivan Toledo <sivan@math.tau.ac.il> 
* @author Oren Manor <manorore@post.tau.ac.il> & Ygal Blum <blumygal@post.tau.ac.il>
*/

require_once("CFFFont.php");
require_once("RandomAccessFileOrArray.php");

class CFFFontSubset extends CFFFont
{
    /**
    *  The Strings in this array represent Type1/Type2 operator names
    */
    static $SubrsFunctions = array(
    "RESERVED_0","hstem","RESERVED_2","vstem","vmoveto","rlineto","hlineto","vlineto",
    "rrcurveto","RESERVED_9","callsubr","return","escape","RESERVED_13",
    "endchar","RESERVED_15","RESERVED_16","RESERVED_17","hstemhm","hintmask",
    "cntrmask","rmoveto","hmoveto","vstemhm","rcurveline","rlinecurve","vvcurveto",
    "hhcurveto","shortint","callgsubr","vhcurveto","hvcurveto"
    );
    /**
    * The Strings in this array represent Type1/Type2 escape operator names
    */
    static $SubrsEscapeFuncs = array(
    "RESERVED_0","RESERVED_1","RESERVED_2","and","or","not","RESERVED_6",
    "RESERVED_7","RESERVED_8","abs","add","sub","div","RESERVED_13","neg",
    "eq","RESERVED_16","RESERVED_17","drop","RESERVED_19","put","get","ifelse",
    "random","mul","RESERVED_25","sqrt","dup","exch","index","roll","RESERVED_31",
    "RESERVED_32","RESERVED_33","hflex","flex","hflex1","flex1","RESERVED_REST"
    );

    /**
    * A HashMap containing the glyphs used in the text after being converted
    * to glyph number by the CMap 
    */
    $GlyphsUsed = array();
    /**
    * The GlyphsUsed keys as an ArrayList
    */
    $glyphsInList = array();
    /**
    * A HashMap for keeping the FDArrays being used by the font
    */
    $FDArrayUsed = array();
    /**
    * A HashMaps array for keeping the subroutines used in each FontDict
    */
    $hSubrsUsed = array();
    /**
    * The SubroutinesUsed HashMaps as ArrayLists
    */
    $lSubrsUsed = array();
    /**
    * A HashMap for keeping the Global subroutines used in the font
    */
    $hGSubrsUsed  = array();
    /**
    * The Global SubroutinesUsed HashMaps as ArrayLists
    */
    $lGSubrsUsed = array();
    /**
    * A HashMap for keeping the subroutines used in a non-cid font
    */
    $hSubrsUsedNonCID  = array();
    /**
    * The SubroutinesUsed HashMap as ArrayList
    */
    $lSubrsUsedNonCID = array();
    /**
    * An array of the new Indexs for the local Subr. One index for each FontDict
    */
    $NewLSubrsIndex = array();
    /**
    * The new subroutines index for a non-cid font
    */
    $NewSubrsIndexNonCID = NULL;
    /**
    * The new global subroutines index of the font
    */
    $NewGSubrsIndex = NULL;
    /**
    * The new CharString of the font
    */
    $NewCharStringsIndex = NULL;

    /**
    * The bias for the global subroutines
    */
    $GBias = 0;

    /**
    * The linked list for generating the new font stream
    */
    $OutputList = array();

    /**
    * Number of arguments to the stem operators in a subroutine calculated recursivly
    */
    $NumOfHints=0;


    /**	 
    * C'tor for CFFFontSubset
    * @param rf - The font file
    * @param GlyphsUsed - a HashMap that contains the glyph used in the subset 
    */
    public function __construct(RandomAccessFileOrArray $rf,array $GlyphsUsed){
        // Use CFFFont c'tor in order to parse the font file.
        parent::__construct($rf);
        $this->GlyphsUsed = $GlyphsUsed;
        //Put the glyphs into a list
        $glyphsInList = array_merge($glyphsInList, array_keys($GlyphsUsed)));

        for ($i=0;$i<count($fonts);++$i)
        {
            // Read the number of glyphs in the font
            seek($fonts[$i]->charstringsOffset);
            $fonts[$i]->nglyphs = ord(getCard16());

            // Jump to the count field of the String Index
            seek($stringIndexOffset);
            $fonts[$i]->nstrings = ord(getCard16())+count($standardStrings);

            // For each font save the offset array of the charstring
            $fonts[$i]->charstringsOffsets = getIndex($fonts[$i]->charstringsOffset);

            // Proces the FDSelect if exist 
            if ($fonts[$i]->fdselectOffset>=0)
            {
                // Proces the FDSelect
                readFDSelect($i);
                // Build the FDArrayUsed hashmap
                BuildFDArrayUsed($i);
            }
            if ($fonts[$i]->isCID == TRUE)
                // Build the FD Array used Hash Map
                ReadFDArray($i);
                // compute the charset length 
                $fonts[$i]->CharsetLength = CountCharset($fonts[$i]->charsetOffset,$fonts[$i]->nglyphs);
        }
    }


    /**
    * Calculates the length of the charset according to its format
    * @param Offset The Charset Offset
    * @param NumofGlyphs Number of glyphs in the font
    * @return the length of the Charset
    */
    function CountCharset($Offset, $NumofGlyphs){
        $format = 0;
        $Length=0;
        seek($Offset);
        // Read the format
        $format = ord(getCard8());
        // Calc according to format
        switch ($format){
            case 0:
                $Length = 1+2*$NumofGlyphs;
                break;
            case 1:
                $Length = 1+3*CountRange($NumofGlyphs,1);
                break;
            case 2:
                $Length = 1+4*CountRange($NumofGlyphs,2);
                break;
            default:
                break;
        }
        return $Length;
    }


    /**
    * Function calculates the number of ranges in the Charset
    * @param NumofGlyphs The number of glyphs in the font
    * @param Type The format of the Charset
    * @return The number of ranges in the Charset data structure
    */
    function CountRange($NumofGlyphs, $Type){
        $num=0;
        $Sid;
        $i=1;
        $nLeft = 0;
        $Places = 0;
        while ($i<$NumofGlyphs){
            $num++;
            $Sid = getCard16();
            if ($Type==1)
                $nLeft = getCard8();
            else
                $nLeft = getCard16();
            $i += ord($nLeft)+1;
        }
        return $num;
    }

    /**
    * Read the FDSelect of the font and compute the array and its length
    * @param Font The index of the font being processed
    * @return The Processed FDSelect of the font
    */
    protected function readFDSelect($Font)
    {
        // Restore the number of glyphs
        $NumOfGlyphs = $fonts[$Font]->nglyphs;
        $FDSelect = array();
        // Go to the beginning of the FDSelect
        seek($fonts[$Font]->fdselectOffset);
        // Read the FDSelect's format
        $fonts[$Font]->FDSelectFormat = ord(getCard8());

        switch($fonts[$Font]->FDSelectFormat){
            // Format==0 means each glyph has an entry that indicated
            // its FD.
            case 0:
                for ($i=0;$i<$NumOfGlyphs;$i++)
                {
                    $FDSelect[$i] = ord(getCard8());
                }
                // The FDSelect's Length is one for each glyph + the format
                // for later use
                $fonts[$Font]->FDSelectLength = $fonts[$Font]->nglyphs+1;
                break;
            case 3:
                // Format==3 means the ranges version
                // The number of ranges
                $nRanges = ord(getCard16());
                $l=0;
                // Read the first in the first range
                $first = ord(getCard16());
                for ($i=0;$i<$nRanges;$i++)
                {
                    // Read the FD index
                    $fd = ord(getCard8());
                    // Read the first of the next range
                    $last = ord(getCard16());
                    // Calc the steps and write to the array
                    $steps = $last-$first;
                    for ($k=0;$k<$steps;$k++)
                    {
                        $FDSelect[$l] = $fd;
                        $l++;
                    }
                    // The last from this iteration is the first of the next
                    $first = $last;
                }
                // Store the length for later use
                $fonts[$Font]->FDSelectLength = 1+2+$nRanges*3+2;
                break;
            default:
                break;
        }
        // Save the FDSelect of the font 
        $fonts[$Font]->FDSelect = $FDSelect; 
    }

    /**
    * Function reads the FDSelect and builds the FDArrayUsed HashMap According to the glyphs used
    * @param Font the Number of font being processed
    */
    protected function BuildFDArrayUsed($Font)
    {
        $FDSelect = $fonts[$Font]->FDSelect;
        // For each glyph used
        for ($i=0;$i<count($glyphsInList);$i++)
        {
            // Pop the glyphs index
            $glyph = ((integer)$glyphsInList[$i]);
            // Pop the glyph's FD
            $FD = $FDSelect[$glyph];
            // Put the FD index into the FDArrayUsed HashMap
            $FDArrayUsed[$FD] = NULL;
        }
    }


    /**
    * Read the FDArray count, offsize and Offset array
    * @param Font
    */
    protected function ReadFDArray($Font)
    {
        seek($fonts[$Font]->fdarrayOffset);
        $fonts[$Font]->FDArrayCount = ord(getCard16());
        $fonts[$Font]->FDArrayOffsize = ord(getCard8());
        // Since we will change values inside the FDArray objects
        // We increase its offsize to prevent errors 
        if ($fonts[$Font]->FDArrayOffsize < 4)
            $fonts[$Font]->FDArrayOffsize++;
        $fonts[$Font]->FDArrayOffsets = getIndex($fonts[$Font]->fdarrayOffset);
    }

    /**
    * The Process function extracts one font out of the CFF file and returns a
    * subset version of the original.
    * @param fontName - The name of the font to be taken out of the CFF
    * @return The new font stream
    * @throws IOException
    */
    public function Process($fontName){
       if (is_resource($fontName) == TRUE)
            $fontName = itextphp_string_toPHPString($fontName);
       try
       {
           // Verify that the file is open
           $buf->reOpen();
           // Find the Font that we will be dealing with
           $j = 0;
           for ($j=0; $j<count($fonts); $j++)
               if (strcmp($fontName, $fonts[$j]->name) == 0) break;
           if ($j==count($fonts)) return NULL;

           // Calc the bias for the global subrs
           if ($gsubrIndexOffset >= 0)
               $GBias = CalcBias($gsubrIndexOffset,$j);

               // Prepare the new CharStrings Index
               BuildNewCharString($j);
               // Prepare the new Global and Local Subrs Indices
               BuildNewLGSubrs($j);
               // Build the new file 
               $Ret = BuildNewFile(j);
               return $Ret;
        }
        catch (Exception $e){
            try {
                $buf->close();
                return;
            }
            catch (Exception $e) {
                // empty on purpose
                return;
            }
        }


        try {
            $buf->close();
            return;
        }
        catch (Exception $e) {
            // empty on purpose
            return;
        }
    }


    /**
    * Function calcs bias according to the CharString type and the count
    * of the subrs
    * @param Offset The offset to the relevent subrs index
    * @param Font the font
    * @return The calculated Bias
    */
    protected function CalcBias($Offset, $Font)
    {
        seek($Offset);
        $nSubrs = ord(getCard16());
        // If type==1 -> bias=0 
        if ($fonts[$Font]->CharstringType == 1)
            return 0;
        // else calc according to the count
        else if ($nSubrs < 1240)
           return 107;
        else if ($nSubrs < 33900)
           return 1131;
        else
        return 32768;
    }

    /**
    *Function uses BuildNewIndex to create the new index of the subset charstrings
    * @param FontIndex the font
    * @throws IOException
    */
    protected function BuildNewCharString($FontIndex)
    {
        $NewCharStringsIndex = BuildNewIndex($fonts[$FontIndex]->charstringsOffsets,$GlyphsUsed);
    }

    /**
    * Function builds the new local & global subsrs indices. IF CID then All of 
    * the FD Array lsubrs will be subsetted. 
    * @param Font the font
    * @throws IOException
    */
    protected function BuildNewLGSubrs($Font)
    {
        // If the font is CID then the lsubrs are divided into FontDicts.
        // for each FD array the lsubrs will be subsetted.
        if($fonts[$Font]->isCID == TRUE)
        {
            // Init the hasmap-array and the arraylist-array to hold the subrs used
            // in each private dict.
            $hSubrsUsed = array();
            $lSubrsUsed = array();
            // A [][] which will store the byte array for each new FD Array lsubs index
            $NewLSubrsIndex = array();
            // An array to hold the offset for each Lsubr index 
            $fonts[$Font]->PrivateSubrsOffset = array();
            // A [][] which will store the offset array for each lsubr index			
            $fonts[$Font]->PrivateSubrsOffsetsArray = array();

            // Put the FDarrayUsed into a list
            $FDInList = array_merge(array(), array_keys($FDArrayUsed));
            // For each FD array which is used subset the lsubr 
            for ($j=0;$j<count($FDInList);$j++)
            {
                // The FDArray index, Hash Map, Arrat List to work on
                $FD = ((integer)$FDInList[$j];
                $hSubrsUsed[$FD] = array();
                $lSubrsUsed[$FD] = array();
                //Reads the private dicts looking for the subr operator and 
                // store both the offest for the index and its offset array
                BuildFDSubrsOffsets($Font,$FD);
                // Verify that FDPrivate has a LSubrs index
                if($fonts[$Font]->PrivateSubrsOffset[$FD]>=0)
                {
                    //Scans the Charsting data storing the used Local and Global subroutines 
                    // by the glyphs. Scans the Subrs recursivley. 
                    BuildSubrUsed($Font,$FD,$fonts[$Font]->PrivateSubrsOffset[$FD],$fonts[$Font]->PrivateSubrsOffsetsArray[$FD],$hSubrsUsed[$FD],$lSubrsUsed[$FD]);
                    // Builds the New Local Subrs index
                    $NewLSubrsIndex[$FD] = BuildNewIndex($fonts[$Font]->PrivateSubrsOffsetsArray[$FD],hSubrsUsed[$FD]);
                }
            }
        }
        // If the font is not CID && the Private Subr exists then subset:
        else if ($fonts[$Font]->privateSubrs>=0)
        {
            // Build the subrs offsets;
            $fonts[$Font]->SubrsOffsets = getIndex($fonts[$Font]->privateSubrs);
            //Scans the Charsting data storing the used Local and Global subroutines 
            // by the glyphs. Scans the Subrs recursivley.
            BuildSubrUsed($Font,-1,$fonts[$Font]->privateSubrs,$fonts[$Font]->SubrsOffsets,$hSubrsUsedNonCID,$lSubrsUsedNonCID);
        }
        // For all fonts susbset the Global Subroutines
        // Scan the Global Subr Hashmap recursivly on the Gsubrs
        BuildGSubrsUsed($Font);
        if ($fonts[$Font]->privateSubrs>=0)
            // Builds the New Local Subrs index
            $NewSubrsIndexNonCID = BuildNewIndex($fonts[$Font]->SubrsOffsets,$hSubrsUsedNonCID);
            //Builds the New Global Subrs index
            $NewGSubrsIndex = BuildNewIndex($gsubrOffsets,$hGSubrsUsed);
    }


    /**
    * The function finds for the FD array processed the local subr offset and its 
    * offset array.  
    * @param Font the font
    * @param FD The FDARRAY processed
    */
    protected function BuildFDSubrsOffsets($Font, $FD)
    {
        // Initiate to -1 to indicate lsubr operator present
        $fonts[$Font]->PrivateSubrsOffset[$FD] = -1;
        // Goto begining of objects
        seek($fonts[$Font]->fdprivateOffsets[$FD]);
        // While in the same object:
        while (getPosition() < $fonts[$Font]->fdprivateOffsets[$FD]+$fonts[$Font]->fdprivateLengths[$FD])
        {
            getDictItem();
            // If the dictItem is the "Subrs" then find and store offset, 
            if (strcmp($key, "Subrs") == 0)
                $fonts[$Font]->PrivateSubrsOffset[$FD] = ((integer)$args[0])+$fonts[$Font]->fdprivateOffsets[$FD];
        }
        //Read the lsub index if the lsubr was found
        if ($fonts[$Font]->PrivateSubrsOffset[$FD] >= 0)
            $fonts[$Font]->PrivateSubrsOffsetsArray[$FD] = getIndex($fonts[$Font]->PrivateSubrsOffset[$FD]); 
    }

    /**
    * Function uses ReadAsubr on the glyph used to build the LSubr & Gsubr HashMap.
    * The HashMap (of the lsub only) is then scaned recursivly for Lsubr & Gsubrs
    * calls.
    * @param Font the font
    * @param FD FD array processed. 0 indicates function was called by non CID font
    * @param SubrOffset the offset to the subr index to calc the bias
    * @param SubrsOffsets the offset array of the subr index
    * @param hSubr HashMap of the subrs used
    * @param lSubr ArrayList of the subrs used
    */
    protected void BuildSubrUsed($Font, $FD, $SubrOffset,array $SubrsOffsets,array $hSubr,array $lSubr)
    {

        // Calc the Bias for the subr index
        $LBias = CalcBias($SubrOffset,$Font);

        // For each glyph used find its GID, start & end pos
        for ($i=0;$i<count($glyphsInList);$i++)
        {
            $glyph = ((integer)$glyphsInList[$i]);
            $Start = $fonts[$Font]->charstringsOffsets[$glyph];
            $End = $fonts[$Font]->charstringsOffsets[$glyph+1];

            // IF CID:
            if ($FD >= 0)
            {
                EmptyStack();
                $NumOfHints=0;
                // Using FDSELECT find the FD Array the glyph belongs to.
                $GlyphFD = $fonts[$Font]->FDSelect[$glyph];
                // If the Glyph is part of the FD being processed 
                if ($GlyphFD == $FD)
                    // Find the Subrs called by the glyph and insert to hash:
                    ReadASubr($Start,$End,$GBias,$LBias,$hSubr,$lSubr,$SubrsOffsets);
            }
            else
                // If the font is not CID 
                //Find the Subrs called by the glyph and insert to hash:
                ReadASubr($Start,$End,$GBias,$LBias,$hSubr,$lSubr,$SubrsOffsets);
            }
            // For all Lsubrs used, check recusrivly for Lsubr & Gsubr used
            for ($i=0;$i<count($lSubr);$i++)
            {
                // Pop the subr value from the hash
                $Subr = ((integer)$lSubr[$i]);
                // Ensure the Lsubr call is valid
                if ($Subr < count($SubrsOffsets)-1 && $Subr>=0)
                {
                    // Read and process the subr
                    $Start = $SubrsOffsets[$Subr];
                    $End = $SubrsOffsets[$Subr+1];
                    ReadASubr($Start,$End,$GBias,$LBias,$hSubr,$lSubr,$SubrsOffsets);
                }
            }
    }

    /**
    * Function scans the Glsubr used ArrayList to find recursive calls 
    * to Gsubrs and adds to Hashmap & ArrayList
    * @param Font the font
    */
    protected function BuildGSubrsUsed($Font)
    {
        $LBias = 0;
        $SizeOfNonCIDSubrsUsed = 0;
        if ($fonts[$Font]->privateSubrs>=0)
        {
            $LBias = CalcBias($fonts[$Font]->privateSubrs,$Font);
            $SizeOfNonCIDSubrsUsed = count($lSubrsUsedNonCID);
        }

        // For each global subr used 
        for ($i=0;$i<count($lGSubrsUsed);$i++)
        {
            //Pop the value + check valid 
            $Subr = ((integer)$lGSubrsUsed[$i]);
            if ($Subr < count($gsubrOffsets)-1 && $Subr>=0)
            {
                // Read the subr and process
                $Start = $gsubrOffsets[$Subr];
                $End = $gsubrOffsets[$Subr+1];

                if ($fonts[$Font]->isCID == TRUE)
                    ReadASubr($Start,$End,$GBias,0,$hGSubrsUsed,$lGSubrsUsed,NULL);
                else
                {
                    ReadASubr($Start,$End,$GBias,$LBias,$hSubrsUsedNonCID,$lSubrsUsedNonCID,$fonts[$Font]->SubrsOffsets);
                    if ($SizeOfNonCIDSubrsUsed < count($lSubrsUsedNonCID))
                    {
                        for ($j=$SizeOfNonCIDSubrsUsed;$j<count($lSubrsUsedNonCID);$j++)
                        {
                            //Pop the value + check valid 
                            $LSubr = ((integer)$lSubrsUsedNonCID[$j]);
                            if ($LSubr < count($fonts[$Font]->SubrsOffsets)-1 && $LSubr>=0)
                            {
                                // Read the subr and process
                                $LStart = $fonts[$Font]->SubrsOffsets[$LSubr];
                                $LEnd = $fonts[$Font]->SubrsOffsets[$LSubr+1];
                                ReadASubr($LStart,$LEnd,$GBias,$LBias,$hSubrsUsedNonCID,$lSubrsUsedNonCID,$fonts[$Font]->SubrsOffsets);
                            }
                        }
                        $SizeOfNonCIDSubrsUsed = count($lSubrsUsedNonCID);
                    }
                }
            }
        }
    }


    /**
    * The function reads a subrs (glyph info) between begin and end.
    * Adds calls to a Lsubr to the hSubr and lSubrs.
    * Adds calls to a Gsubr to the hGSubr and lGSubrs.
    * @param begin the start point of the subr
    * @param end the end point of the subr
    * @param GBias the bias of the Global Subrs
    * @param LBias the bias of the Local Subrs
    * @param hSubr the HashMap for the lSubrs
    * @param lSubr the ArrayList for the lSubrs
    */
    protected function ReadASubr($begin,$end,$GBias,$LBias,array $hSubr,array $lSubr,array $LSubrsOffsets)
    {
        // Clear the stack for the subrs
        EmptyStack();
        $NumOfHints = 0;
        // Goto begining of the subr
        seek($begin);
        while ($getPosition() < $end)
        {
            // Read the next command
            ReadCommand();
            $pos = getPosition();
            $TopElement=NULL;
            if ($arg_count > 0)
                $TopElement = $args[$arg_count-1];
            $NumOfArgs = arg_count;
            // Check the modification needed on the Argument Stack according to key;
            HandelStack();
            // a call to a Lsubr
            if (strcmp($key, "callsubr") == 0)
            {
                // Verify that arguments are passed 
                if ($NumOfArgs > 0)
                {
                    // Calc the index of the Subrs
                    $Subr = ((integer)$TopElement) + $LBias;
                    // If the subr isn't in the HashMap -> Put in
                    if (array_key_exists($subr, $hSubr) == FALSE)
                    {
                        $hSubr[$Subr] = NULL;
                        array_push($lSubr, $Subr);
                    }
                    CalcHints($LSubrsOffsets[$Subr],$LSubrsOffsets[$Subr+1],$LBias,$GBias,$LSubrsOffsets);
                    seek($pos);
                }
            }
            // a call to a Gsubr
            else if (strcmp($key, "callgsubr") == 0)
            {
                // Verify that arguments are passed 
                if ($NumOfArgs > 0)
                {
                    // Calc the index of the Subrs
                    $Subr = ((integer)$TopElement) + $GBias;
                    // If the subr isn't in the HashMap -> Put in
                    if (array_key_exists($subr, $hGSubrsUsed) == FALSE)
                    {
                        $hGSubrsUsed[$Subr] = NULL;
                        array_push($lGSubrsUsed, $Subr);
                    }
                    CalcHints($gsubrOffsets[$Subr],$gsubrOffsets[$Subr+1],$LBias,$GBias,$LSubrsOffsets);
                    seek($pos);
                }
            }
            // A call to "stem"
            else if (strcmp($key, "hstem") == 0 || strcmp($key, "vstem") == 0 || strcmp($key, "hstemhm") == 0 || strcmp($key, "vstemhm") == 0)
                // Increment the NumOfHints by the number couples of of arguments
                $NumOfHints += $NumOfArgs/2;
            // A call to "mask"
            else if (strcmp($key, "hintmask") == 0 || strcmp($key, "cntrmask") == 0)
            {
                // Compute the size of the mask
                $SizeOfMask = $NumOfHints/8;
                if ($NumOfHints%8 != 0 || $SizeOfMask == 0)
                    $SizeOfMask++;
                // Continue the pointer in SizeOfMask steps
                for ($i=0;$i<$SizeOfMask;i++)
                    getCard8();
            }
        }
    }


    /**
    * Function Checks how the current operator effects the run time stack after being run 
    * An operator may increase or decrease the stack size
    */
    protected function HandelStack()
    {
        // Findout what the operator does to the stack
        $StackHandel = StackOpp();
        if ($StackHandel < 2)
        {
            // The operators that enlarge the stack by one
            if ($StackHandel==1)
                PushStack();
            // The operators that pop the stack
            else
            {
                // Abs value for the for loop
                $StackHandel *= -1;
                for ($i=0;$i<$StackHandel;i++)
                    PopStack();
            }

        }
        // All other flush the stack
        else
            EmptyStack();
    }

    /**
    * Function checks the key and return the change to the stack after the operator
    * @return The change in the stack. 2-> flush the stack
    */
    protected function StackOpp()
    {
        if (strcmp($key, "ifelse") == 0)
            return -3;
        if (strcmp($key, "roll") == 0 || strcmp($key, "put") == 0)
            return -2;
        if (strcmp($key, "callsubr") == 0 || strcmp($key, "callgsubr") == 0 || strcmp($key, "add") == 0 || strcmp($key, "sub") == 0 || strcmp($key, "div") == 0 ||         strcmp($key, "mul") == 0 || strcmp($key, "drop") == 0 || strcmp($key, "and") == 0 || strcmp($key, "or") == 0 || strcmp($key, "eq") == 0)
            return -1;
        if (strcmp($key, "abs") == 0 || strcmp($key, "neg") == 0 || strcmp($key, "sqrt") == 0 || strcmp($key, "exch") == 0 || strcmp($key, "index") == 0 || strcmp($key, "get") == 0 || strcmp($key, "not") == 0 || strcmp($key, "return") == 0)
            return 0;
        if (strcmp($key, "random") == 0 || strcmp($key, "dup") == 0)
            return 1;
        return 2;
    }


    /**
    * Empty the Type2 Stack
    *
    */
    protected function EmptyStack()
    {
        // Null the arguments
        for ($i=0; $i<$arg_count; $i++) $args[$i]=NULL;
            $arg_count = 0;
    }

    /**
    * Pop one element from the stack 
    *
    */
    protected function PopStack()
    {
        if ($arg_count>0)
        {
            $args[$arg_count-1]=NULL;
            $arg_count--;
        }
    }


    /**
    * Add an item to the stack
    *
    */
    protected function PushStack()
    {
        $arg_count++;
    }


    /**
    * The function reads the next command after the file pointer is set
    */
    protected function ReadCommand()
    {
        $key = NULL;
        $gotKey = FALSE;
        // Until a key is found
        while ($gotKey == FALSE) {
            // Read the first Char
            $b0 = getCard8();
            // decode according to the type1/type2 format
            if (ord($b0) == 28) // the two next bytes represent a short int;
            {
                $first = ord(getCard8());
                $second = ord(getCard8());
                $args[$arg_count] = $first<<8 | $second);
                $arg_count++;
                continue;
            }
            if (ord($b0) >= 32 && ord($b0) <= 246) // The byte read is the byte;
            {
                $args[$arg_count] = ord($b0) - 139;
                $arg_count++;
                continue;
            }
            if (ord($b0) >= 247 && ord($b0) <= 250) // The byte read and the next byte constetute a short int
            {
                $w = ord(getCard8());
                $args[$arg_count] = (ord($b0)-247)*256 + $w + 108;
                $arg_count++;
                continue;
            }
            if (ord($b0) >= 251 && ord($b0) <= 254)// Same as above except negative
            {
                $w = ord(getCard8());
                $args[$arg_count] = -(ord($b0)-251)*256 - $w - 108;
                $arg_count++;
                continue;
            }
            if (ord($b0) == 255)// The next for bytes represent a double.
            {
                $first = ord(getCard8());
                $second = ord(getCard8());
                $third = ord(getCard8());
                $fourth = ord(getCard8());
                $args[$arg_count] = $first<<24 | $second<<16 | $third<<8 | $fourth;
                $arg_count++;
                continue;
            }
            if (ord($b0)<=31 && ord($b0) != 28) // An operator was found.. Set Key.
            {
                $gotKey=TRUE;
                // 12 is an escape command therefor the next byte is a part
                // of this command
                if (ord($b0) == 12)
                {
                    $b1 = ord(getCard8());
                    if ($b1>count(CFFFontSubset::$SubrsEscapeFuncs)-1)
                        $b1 = count(CFFFontSubset::$SubrsEscapeFuncs)-1;
                    $key = CFFFontSubset::$SubrsEscapeFuncs[$b1];
                }
                else
                    $key = CFFFontSubset::$SubrsFunctions[$b0];
                continue;
            }
        }
    }


    /**
    * The function reads the subroutine and returns the number of the hint in it.
    * If a call to another subroutine is found the function calls recursively.
    * @param begin the start point of the subr
    * @param end the end point of the subr
    * @param LBias the bias of the Local Subrs
    * @param GBias the bias of the Global Subrs
    * @param LSubrsOffsets The Offsets array of the subroutines
    * @return The number of hints in the subroutine read.
    */
    protected function CalcHints($begin,$end,$LBias,$GBias,array $LSubrsOffsets)
    {
        // Goto begining of the subr
        seek($begin);
        while ($getPosition() < $end)
        {
            // Read the next command
            ReadCommand();
            $pos = getPosition();
            $TopElement = NULL;
            if ($arg_count>0)
                $TopElement = $args[$arg_count-1];
            $NumOfArgs = $arg_count;
            //Check the modification needed on the Argument Stack according to key;
            HandelStack();
            // a call to a Lsubr
            if (strcmp($key, "callsubr") == 0) 
            {
                if ($NumOfArgs>0)
                {
                    $Subr = ((integer)$TopElement + $LBias;
                    CalcHints($LSubrsOffsets[$Subr],$LSubrsOffsets[$Subr+1],$LBias,$GBias,$LSubrsOffsets);
                    seek($pos);
                }
            }
            // a call to a Gsubr
            else if (strcmp($key, "callgsubr") == 0)
            {
                if ($NumOfArgs>0)
                {
                    $Subr = ((integer)$TopElement + $GBias;
                    CalcHints($gsubrOffsets[$Subr],$gsubrOffsets[$Subr+1],$LBias,$GBias,$LSubrsOffsets);
                    seek($pos);
                }
            }
            // A call to "stem"
            else if (strcmp($key, "hstem") == 0 || strcmp($key, "vstem") == 0 || strcmp($key, "hstemhm") == 0 || strcmp($key, "vstemhm") == 0)
                // Increment the NumOfHints by the number couples of of arguments
                $NumOfHints += $NumOfArgs/2;
                // A call to "mask"
            else if (strcmp($key, "hintmask") == 0 || strcmp($key, "cntrmask") == 0)
            {
                // Compute the size of the mask
                $SizeOfMask = $NumOfHints/8;
                if ($NumOfHints%8 != 0 || $SizeOfMask == 0)
                    $SizeOfMask++;
                // Continue the pointer in SizeOfMask steps
                for ($int $i=0;$i<$SizeOfMask;$i++)
                    getCard8();
            }
        }
        return $NumOfHints;
    }

    /**
    * Function builds the new offset array, object array and assembles the index.
    * used for creating the glyph and subrs subsetted index 
    * @param Offsets the offset array of the original index  
    * @param Used the hashmap of the used objects
    * @return the new index subset version 
    * @throws IOException
    */
    protected function BuildNewIndex(array $Offsets,array $Used)
    {
        $Offset=0;
        $NewOffsets = array();
        // Build the Offsets Array for the Subset
        for ($i=0;$i<count($Offsets);++$i)
        {
            $NewOffsets[$i] = $Offset;
            // If the object in the offset is also present in the used
            // HashMap then increment the offset var by its size
            if (array_key_exists($i, $Used) == TRUE)
                $Offset += $Offsets[$i+1] - $Offsets[$i];
                // Else the same offset is kept in i+1.
        }
        // Offset var determines the size of the object array
        $NewObjects = itextphp_bytes_create($Offset);
        // Build the new Object array
        for ($i=0;$i<count($Offsets)-1;++$i)
        {
            $start = $NewOffsets[$i];
            $end = $NewOffsets[$i+1];
            // If start != End then the Object is used
            // So, we will copy the object data from the font file
            if ($start != $end)
            {
                // All offsets are Global Offsets relative to the begining of the font file.
                // Jump the file pointer to the start address to read from.
                $buf->seek($Offsets[$i]);
                // Read from the buffer and write into the array at start.  
                $buf->readFully($NewObjects, $start, $end-$start);
            }
        }
        // Use AssembleIndex to build the index from the offset & object arrays
        return AssembleIndex($NewOffsets,$NewObjects);
    }

    /**
    * Function creates the new index, inserting the count,offsetsize,offset array
    * and object array.
    * @param NewOffsets the subsetted offset array
    * @param NewObjects the subsetted object array
    * @return the new index created
    */
    protected function AssembleIndex(array $NewOffsets,array $NewObjects)
    {
        // Calc the index' count field
        $Count = chr(count($NewOffsets)-1);
        // Calc the size of the object array
        $Size = $NewOffsets[count($NewOffsets)-1];
        // Calc the Offsize
        $Offsize = 0;
        if ($Size <= 0xff) $Offsize = 1;
        else if ($Size <= 0xffff) $Offsize = 2;
        else if ($Size <= 0xffffff) $Offsize = 3;
        else $Offsize = 4;
        // The byte array for the new index. The size is calc by
        // Count=2, Offsize=1, OffsetArray = Offsize*(Count+1), The object array
        $NewIndex = itextphp_bytes_create(2+1+$Offsize*(ord($Count)+1)+count($NewObjects));
        // The counter for writing
        $Place = 0;
        // Write the count field
        $NewIndex[$Place++] = ((ord($Count) >>> 8) & 0xff);
        $NewIndex[$Place++] = ((ord($Count) >>> 0) & 0xff);
        // Write the offsize field
        $NewIndex[$Place++] = $Offsize;
        // Write the offset array according to the offsize
        for ($i=0;$i<count($NewOffsets);$i++)
        {
            // The value to be written
            $Num = $NewOffsets[$i]-$NewOffsets[0]+1;
            // Write in bytes according to the offsize
            switch ($Offsize) {
                case 4:
                    $NewIndex[$Place++] = (($Num >>> 24) & 0xff);
                case 3:
                    $NewIndex[$Place++] = (($Num >>> 16) & 0xff);
                case 2:
                    $NewIndex[$Place++] = (($Num >>>  8) & 0xff);
                case 1:
                    $NewIndex[$Place++] = (($Num >>>  0) & 0xff);
            }
        }
        // Write the new object array one by one
        for ($i=0;$i<count($NewObjects);$i++)
        {
            $NewIndex[$Place++] = $NewObjects[$i];
        }
        // Return the new index
        return $NewIndex;
   }


    /**
    * The function builds the new output stream according to the subset process
    * @param Font the font
    * @return the subseted font stream
    * @throws IOException
    */
    protected function BuildNewFile($Font)
    {
        // Prepare linked list for new font components
        $OutputList = array();

        // copy the header of the font
        CopyHeader();

        // create a name index
        BuildIndexHeader(1,1,1);
        array_push($OutputList, new UInt8Item(chr( 1+strlen($fonts[$Font]->name) )));
        array_push($OutputList, new StringItem($fonts[$Font]->name));

        // create the topdict Index
        BuildIndexHeader(1,2,1);
        $topdictIndex1Ref = new IndexOffsetItem(2);
        array_push($OutputList, $topdictIndex1Ref);
        $topdictBase = new IndexBaseItem();
        array_push($OutputList, $topdictBase);

        // Initialise the Dict Items for later use
        $charsetRef     = new DictOffsetItem();
        $charstringsRef = new DictOffsetItem();
        $fdarrayRef     = new DictOffsetItem();
        $fdselectRef    = new DictOffsetItem();
        $privateRef     = new DictOffsetItem();

        // If the font is not CID create the following keys
        if ( $fonts[$Font]->isCID == FALSE ) {
            // create a ROS key
            array_push($OutputList, new DictNumberItem($fonts[$Font]->nstrings));
            array_push($OutputList, new DictNumberItem($fonts[$Font]->nstrings+1));
            array_push($OutputList, new DictNumberItem(0));
            array_push($OutputList, new UInt8Item(chr(12)));
            array_push($OutputList, new UInt8Item(chr(30)));
            // create a CIDCount key
            array_push($OutputList, new DictNumberItem($fonts[$Font]->nglyphs));
            array_push($OutputList, new UInt8Item(chr(12)));
            array_push($OutputList, new UInt8Item(chr(34)));
            // Sivan's comments
            // What about UIDBase (12,35)? Don't know what is it.
            // I don't think we need FontName; the font I looked at didn't have it.
        }
        // Go to the TopDict of the font being processed
        seek($topdictOffsets[$Font]);
        // Run untill the end of the TopDict
        while (getPosition() < $topdictOffsets[$Font+1]) {
            $p1 = getPosition();
            getDictItem();
            $p2 = getPosition();
            // The encoding key is disregarded since CID has no encoding
            if (strcmp($key, "Encoding") == 0
            // These keys will be added manualy by the process.
            || strcmp($key, "Private") == 0
            || strcmp($key, "FDSelect") == 0
            || strcmp($key, "FDArray") == 0
            || strcmp($key, "charset") == 0
            || strcmp($key, "CharStrings") == 0
            ) {
            }else {
            //OtherWise copy key "as is" to the output list
                array_push($OutputList, new RangeItem($buf,$p1,$p2-$p1));
            }
        }
        // Create the FDArray, FDSelect, Charset and CharStrings Keys
        CreateKeys($fdarrayRef,$fdselectRef,$charsetRef,$charstringsRef);

        // Mark the end of the top dict area
        array_push($OutputList, new IndexMarkerItem($topdictIndex1Ref,$topdictBase));

        // Copy the string index

        if ($fonts[$Font]->isCID == TRUE)
            array_push($OutputList, getEntireIndexRange($stringIndexOffset));
        // If the font is not CID we need to append new strings.
        // We need 3 more strings: Registry, Ordering, and a FontName for one FD.
        // The total length is at most "Adobe"+"Identity"+63 = 76
        else
            CreateNewStringIndex($Font);

        // copy the new subsetted global subroutine index       
        array_push($OutputList, new RangeItem(new RandomAccessFileOrArray($NewGSubrsIndex),0,$NewGSubrsIndex.length));

        // deal with fdarray, fdselect, and the font descriptors
        // If the font is CID:
        if ($fonts[$Font]->isCID == TRUE) {
            // copy the FDArray, FDSelect, charset

            // Copy FDSelect
            // Mark the beginning
            array_push($OutputList, new MarkerItem($fdselectRef));
            // If an FDSelect exists copy it
            if ($fonts[$Font]->fdselectOffset>=0)
                array_push($OutputList, new RangeItem($buf,$fonts[$Font]->fdselectOffset,$fonts[$Font]->FDSelectLength));
            // Else create a new one
            else
                CreateFDSelect($fdselectRef,$fonts[$Font]->nglyphs);

            // Copy the Charset
            // Mark the beginning and copy entirly 
            array_push($OutputList, new MarkerItem($charsetRef));
            array_push($OutputList, new RangeItem($buf,$fonts[$Font]->charsetOffset,$fonts[$Font]->CharsetLength));

            // Copy the FDArray
            // If an FDArray exists
            if ($fonts[$Font]->fdarrayOffset>=0)
            {
                // Mark the beginning
                array_push($OutputList, new MarkerItem($fdarrayRef));
                // Build a new FDArray with its private dicts and their LSubrs
                Reconstruct($Font);
            }
            else
                // Else create a new one
                CreateFDArray($fdarrayRef,$privateRef,$Font);

        }
        // If the font is not CID
        else
        {
            // create FDSelect
            CreateFDSelect($fdselectRef,$fonts[$Font]->nglyphs);
            // recreate a new charset
            CreateCharset($charsetRef,$fonts[$Font]->nglyphs);
            // create a font dict index (fdarray)
            CreateFDArray($fdarrayRef,$privateRef,$Font);
        }

        // if a private dict exists insert its subsetted version
        if ($fonts[$Font]->privateOffset >=0)
        {
            // Mark the beginning of the private dict
            $PrivateBase = new IndexBaseItem();
            array_push($OutputList, $PrivateBase);
            array_push($OutputList, new MarkerItem($privateRef));

            $Subr = new DictOffsetItem();
            // Build and copy the new private dict
            CreateNonCIDPrivate($Font,$Subr);
            // Copy the new LSubrs index
            CreateNonCIDSubrs($Font,$PrivateBase,$Subr);
        }

        // copy the charstring index
        array_push($OutputList, new MarkerItem($charstringsRef));

        // Add the subsetted charstring
        array_push($OutputList, new RangeItem(new RandomAccessFileOrArray($NewCharStringsIndex),0,itextphp_bytes_getSize($NewCharStringsIndex));

        // now create the new CFF font
        $currentOffset = array();
        $currentOffset[0] = 0;
        // Count and save the offset for each item
        foreach ($OutputList as &$item) {
            $item->increment($currentOffset);
        }
        // Compute the Xref for each of the offset items
        foreach ($OutputList as &$item) {
            $item->xref();
        }

        $size = $currentOffset[0];
        $b = itextphp_bytes_create($size);

        // Emit all the items into the new byte array
        foreach ($OutputList as &$item) {
            $item->emit($b);
        }
        // Return the new stream
        return $b;
    }


    /**
    * Function Copies the header from the original fileto the output list
    */
    protected function CopyHeader()
    {
        seek(0);
        $major = ord(getCard8());
        $minor = ord(getCard8());
        $hdrSize = ord(getCard8());
        $offSize = ord(getCard8());
        $nextIndexOffset = $hdrSize;
        array_push($OutputList, new RangeItem($buf,0,$hdrSize));
    }


    /**
    * Function Build the header of an index
    * @param Count the count field of the index
    * @param Offsize the offsize field of the index
    * @param First the first offset of the index
    */
    protected function BuildIndexHeader($Count,$Offsize,$First)
    {
        // Add the count field
        array_push($OutputList, new UInt16Item(chr($Count)); // count
        // Add the offsize field
        array_push($OutputList, new UInt8Item(chr($Offsize)); // offSize
        // Add the first offset according to the offsize
        switch($Offsize){
            case 1:
                array_push($OutputList, new UInt8Item(chr($First))); // first offset
                break;
            case 2:
                array_push($OutputList, new UInt16Item(chr($First))); // first offset
                break;
            case 3:
                array_push($OutputList, new UInt24Item(chr($First))); // first offset
                break;
            case 4:
                array_push($OutputList, new UInt32Item(chr($First))); // first offset
                break;
            default:
                break;
        }
    }

    /**
    * Function adds the keys into the TopDict
    * @param fdarrayRef OffsetItem for the FDArray
    * @param fdselectRef OffsetItem for the FDSelect
    * @param charsetRef OffsetItem for the CharSet
    * @param charstringsRef OffsetItem for the CharString
    */
    protected function CreateKeys(OffsetItem $fdarrayRef,OffsetItem $fdselectRef,OffsetItem $charsetRef,OffsetItem $charstringsRef)
    {
        // create an FDArray key
        array_push($OutputList, $fdarrayRef);
        array_push($OutputList, new UInt8Item(chr(12)));
        array_push($OutputList, new UInt8Item(chr(36)));
        // create an FDSelect key
        array_push($OutputList, $fdselectRef);
        array_push($OutputList, new UInt8Item(chr(12)));
        array_push($OutputList, new UInt8Item(chr(37)));
        // create an charset key
        array_push($OutputList, $charsetRef);
        array_push($OutputList, new UInt8Item(chr(15)));
        // create a CharStrings key
        array_push($OutputList, $charstringsRef);
        array_push($OutputList, new UInt8Item(chr(17)));
    }


    protected function CreateNewStringIndex($Font)
    {
        $fdFontName = $fonts[$Font]->name . "-OneRange";
        if (strlen($fdFontName) > 127)
            $fdFontName = substr($fdFontName, 0,127);
        $extraStrings = "Adobe" . "Identity" . $fdFontName;

        $origStringsLen = $stringOffsets[count($stringOffsets)-1]
        - $stringOffsets[0];
        $stringsBaseOffset = $stringOffsets[0]-1;

        $stringsIndexOffSize = 0;
        if ($origStringsLen+strlen($extraStrings) <= 0xff) $stringsIndexOffSize = 1;
        else if ($origStringsLen+strlen($extraStrings) <= 0xffff) $stringsIndexOffSize = 2;
        else if ($origStringsLen+strlen($extraStrings) <= 0xffffff) $stringsIndexOffSize = 3;
        else $stringsIndexOffSize = 4;

        array_push($OutputList, new UInt16Item(chr((count($stringOffsets)-1)+3))); // count
        array_push($OutputList, new UInt8Item(chr($stringsIndexOffSize))); // offSize
        for ($i=0; $i<count($stringOffsets); $i++)
            array_push($OutputList, new IndexOffsetItem($stringsIndexOffSize,
            $stringOffsets[$i]-$stringsBaseOffset));
        $currentStringsOffset = $stringOffsets[count($stringOffsets)-1]
        - $stringsBaseOffset;
        //l.addLast(new IndexOffsetItem(stringsIndexOffSize,currentStringsOffset));
        $currentStringsOffset += strlen("Adobe");
        array_push($OutputList, new IndexOffsetItem($stringsIndexOffSize,$currentStringsOffset));
        $currentStringsOffset += strlen("Identity");
        array_push($OutputList, new IndexOffsetItem($stringsIndexOffSize,$currentStringsOffset));
        $currentStringsOffset += strlen($fdFontName);
        array_push($OutputList, new IndexOffsetItem($stringsIndexOffSize,$currentStringsOffset));

        array_push($OutputList, new RangeItem($buf,$stringOffsets[0],$origStringsLen));
        array_push($OutputList, new StringItem($extraStrings));
    }

    /**
    * Function creates new FDSelect for non-CID fonts.
    * The FDSelect built uses a single range for all glyphs
    * @param fdselectRef OffsetItem for the FDSelect
    * @param nglyphs the number of glyphs in the font
    */
    protected function CreateFDSelect(OffsetItem $fdselectRef,$nglyphs)
    {
        array_push($OutputList, new MarkerItem($fdselectRef));
        array_push($OutputList, new UInt8Item(chr(3))); // format identifier
        array_push($OutputList, new UInt16Item(chr(1))); // nRanges

        array_push($OutputList, new UInt16Item(chr(0))); // Range[0].firstGlyph
        array_push($OutputList, new UInt8Item(chr(0))); // Range[0].fd

        array_push($OutputList, new UInt16Item(chr($nglyphs))); // sentinel
    }

    /**
    * Function creates new CharSet for non-CID fonts.
    * The CharSet built uses a single range for all glyphs
    * @param charsetRef OffsetItem for the CharSet
    * @param nglyphs the number of glyphs in the font
    */
    protected function CreateCharset(OffsetItem $charsetRef,$nglyphs)
    {
        array_push($OutputList, new MarkerItem($charsetRef));
        array_push($OutputList, new UInt8Item(chr(2))); // format identifier
        array_push($OutputList, new UInt16Item(chr(1))); // first glyph in range (ignore .notdef)
        array_push($OutputList, new UInt16Item(chr($nglyphs-1))); // nLeft
    }

    /**
    * Function creates new FDArray for non-CID fonts.
    * The FDArray built has only the "Private" operator that points to the font's
    * original private dict 
    * @param fdarrayRef OffsetItem for the FDArray
    * @param privateRef OffsetItem for the Private Dict
    * @param Font the font
    */
    protected function CreateFDArray(OffsetItem $fdarrayRef,OffsetItem $privateRef,$Font)
    {
        array_push($OutputList, new MarkerItem($fdarrayRef));
        // Build the header (count=offsize=first=1)
        BuildIndexHeader(1,1,1);

        // Mark
        $privateIndex1Ref = new IndexOffsetItem(1);
        array_push($OutputList, $privateIndex1Ref);
        $privateBase = new IndexBaseItem();
        // Insert the private operands and operator
        array_push($OutputList, $privateBase);
        // Calc the new size of the private after subsetting
        // Origianl size
        $NewSize = $fonts[$Font]->privateLength;
        // Calc the original size of the Subr offset in the private
        $OrgSubrsOffsetSize = CalcSubrOffsetSize($fonts[$Font]->privateOffset,$fonts[$Font]->privateLength);
        // Increase the ptivate's size
        if ($OrgSubrsOffsetSize != 0)
            $NewSize += 5-$OrgSubrsOffsetSize;
        array_push($OutputList, new DictNumberItem($NewSize));
        array_push($OutputList, $privateRef);
        array_push($OutputListnew UInt8Item(chr(18))); // Private

        array_push($OutputList, new IndexMarkerItem($privateIndex1Ref,$privateBase));
    }


    /**
    * Function reconstructs the FDArray, PrivateDict and LSubr for CID fonts
    * @param Font the font
    * @throws IOException
    */
    function Reconstruct($Font)
    {
        // Init for later use
        $fdPrivate = array();
        $fdPrivateBase = array();
        $fdSubrs = array();
        // Reconstruct each type
        ReconstructFDArray($Font,$fdPrivate);
        ReconstructPrivateDict($Font,$fdPrivate,$fdPrivateBase,$fdSubrs);
        ReconstructPrivateSubrs($Font,$fdPrivateBase,$fdSubrs);
    }

    /**
    * Function subsets the FDArray and builds the new one with new offsets
    * @param Font The font
    * @param fdPrivate OffsetItem Array (one for each FDArray)
    * @throws IOException
    */
    function ReconstructFDArray($Font,array $fdPrivate)
    {
        // Build the header of the index
        BuildIndexHeader($fonts[$Font]->FDArrayCount,$fonts[$Font]->FDArrayOffsize,1);

        // For each offset create an Offset Item
        //OffsetItem[] fdOffsets = new IndexOffsetItem[fonts[Font].FDArrayOffsets.length-1];
        for ($i=0;i<count($fonts[$Font]->FDArrayOffsets)-1;$i++)
        {
            $fdOffsets[$i] = new IndexOffsetItem($fonts[$Font]->FDArrayOffsize);
            array_push($OutputList, $fdOffsets[$i]);
        }

        // Declare beginning of the object array
        $fdArrayBase = new IndexBaseItem();
        array_push($OutputList, $fdArrayBase);

        // For each object check if that FD is used.
        // if is used build a new one by changing the private object
        // Else do nothing
        // At the end of each object mark its ending (Even if wasn't written)
        for ($k=0; $k<count($fonts[$Font]->FDArrayOffsets)-1; $k++) {
            if (array_key_exists($k, $FDArrayUsed) == TRUE)
            {
                // Goto begining of objects
                seek($fonts[$Font]->FDArrayOffsets[$k]);
                while (getPosition() < $fonts[$Font]->FDArrayOffsets[$k+1])
                {
                    $p1 = getPosition();
                    getDictItem();
                    $p2 = getPosition();
                    // If the dictItem is the "Private" then compute and copy length, 
                    // use marker for offset and write operator number
                    if (strcmp($key, "Private") == 0) {
                        // Save the original length of the private dict
                        $NewSize = (integer)$args[0];
                        // Save the size of the offset to the subrs in that private
                        $OrgSubrsOffsetSize = CalcSubrOffsetSize($fonts[$Font]->fdprivateOffsets[$k],$fonts[$Font]->fdprivateLengths[$k]);
                        // Increase the private's length accordingly
                        if ($OrgSubrsOffsetSize != 0)
                            $NewSize += 5-$OrgSubrsOffsetSize;
                        // Insert the new size, OffsetItem and operator key number
                        array_push($OutputList, new DictNumberItem($NewSize));
                        $fdPrivate[$k] = new DictOffsetItem();
                        array_push($OutputList, $fdPrivate[$k]);
                        array_push($OutputList, new UInt8Item(chr(18))); // Private
                        // Go back to place 
                        seek($p2);
                    }
                    // Else copy the entire range
                    else  // other than private
                        array_push($OutputList, new RangeItem($buf,$p1,$p2-$p1));
                }
            }
            // Mark the ending of the object (even if wasn't written)
            array_push($OutputList, new IndexMarkerItem($fdOffsets[$k],$fdArrayBase));
        }
    }

    /**
    * Function Adds the new private dicts (only for the FDs used) to the list
    * @param Font the font
    * @param fdPrivate OffsetItem array one element for each private
    * @param fdPrivateBase IndexBaseItem array one element for each private
    * @param fdSubrs OffsetItem array one element for each private
    * @throws IOException
    */
    function ReconstructPrivateDict($Font,array $fdPrivate,array $fdPrivateBase, array $fdSubrs)
    {

        // For each fdarray private dict check if that FD is used.
        // if is used build a new one by changing the subrs offset
        // Else do nothing
        for ($i=0;i<count($fonts[$Font]->fdprivateOffsets);$i++)
        {
            if (array_key_exists($i, $FDArrayUsed) == TRUE)
            {
                // Mark beginning
                array_push($OutputList, new MarkerItem($fdPrivate[$i]));
                $fdPrivateBase[$i] = new IndexBaseItem();
                array_push($OutputList, $fdPrivateBase[$i]);
                // Goto begining of objects
                seek($fonts[$Font]->fdprivateOffsets[$i]);
                while (getPosition() < $fonts[$Font]->fdprivateOffsets[$i]+$fonts[$Font]->fdprivateLengths[$i])
                {
                   $p1 = getPosition();
                   getDictItem();
                   $p2 = getPosition();
                   // If the dictItem is the "Subrs" then, 
                   // use marker for offset and write operator number
                   if (strcmp($key, "Subrs") == 0) {
                       $fdSubrs[$i] = new DictOffsetItem();
                       array_push($OutputList, $fdSubrs[$i]);
                       array_push($OutputList, new UInt8Item(chr(19))); // Subrs
                   }
                   // Else copy the entire range
                   else
                       array_push($OutputList, new RangeItem($buf,$p1,$p2-$p1));
                }
            }
        }
    }

    /**
    * Function Adds the new LSubrs dicts (only for the FDs used) to the list
    * @param Font  The index of the font
    * @param fdPrivateBase The IndexBaseItem array for the linked list
    * @param fdSubrs OffsetItem array for the linked list
    * @throws IOException
    */

    function ReconstructPrivateSubrs($Font,array $fdPrivateBase,
    array $fdSubrs)
    {
        // For each private dict
        for ($i=0;$i<count($fonts[$Font]->fdprivateLengths);$i++)
        {
            // If that private dict's Subrs are used insert the new LSubrs
            // computed earlier
            if ($fdSubrs[$i]!= NULL && $fonts[$Font]->PrivateSubrsOffset[$i] >= 0)
            {
                array_push($OutputList, new SubrMarkerItem($fdSubrs[$i],$fdPrivateBase[$i]));
                array_push($OutputList, new RangeItem(new RandomAccessFileOrArray($NewLSubrsIndex[$i]),0,itextphp_bytes_getSize($NewLSubrsIndex[$i])));
            }
        }
    }


    /**
    * Calculates how many byte it took to write the offset for the subrs in a specific
    * private dict.
    * @param Offset The Offset for the private dict
    * @param Size The size of the private dict
    * @return The size of the offset of the subrs in the private dict
    */
    function CalcSubrOffsetSize($Offset,$Size)
    {
        // Set the size to 0
        $OffsetSize = 0;
        // Go to the beginning of the private dict
        seek($Offset);
        // Go until the end of the private dict 
        while (getPosition() < $Offset+$Size)
        {
            $p1 = getPosition();
            getDictItem();
            $p2 = getPosition();
            // When reached to the subrs offset
            if (strcmp($key, "Subrs") == 0) {
                // The Offsize (minus the subrs key)
                $OffsetSize = $p2-$p1-1;
            }
            // All other keys are ignored
        }
        // return the size
        return $OffsetSize;
    }

    /**
    * Function computes the size of an index
    * @param indexOffset The offset for the computed index
    * @return The size of the index
    */
    protected function countEntireIndexRange($indexOffset)
    {
        // Go to the beginning of the index 
        seek($indexOffset);
        // Read the count field
        $count = ord(getCard16());
        // If count==0 -> size=2
        if ($count==0)
            return 2;
        else
        {
            // Read the offsize field
            $indexOffSize = ord(getCard8());
            // Go to the last element of the offset array
            seek($indexOffset+2+1+$count*$indexOffSize);
            // The size of the object array is the value of the last element-1
            $size = getOffset($indexOffSize)-1;
            // Return the size of the entire index
            return 2+1+($count+1)*$indexOffSize+$size;
        }
    }


    /**
    * The function creates a private dict for a font that was not CID
    * All the keys are copied as is except for the subrs key 
    * @param Font the font
    * @param Subr The OffsetItem for the subrs of the private 
    */
    function CreateNonCIDPrivate($Font,OffsetItem $Subr)
    {
        // Go to the beginning of the private dict and read untill the end
        seek($fonts[$Font]->privateOffset);
        while (getPosition() < $fonts[$Font]->privateOffset+$fonts[$Font]->privateLength)
        {
            $p1 = getPosition();
            getDictItem();
            $p2 = getPosition();
            // If the dictItem is the "Subrs" then, 
            // use marker for offset and write operator number
            if (strcmp($key, "Subrs") == 0) {
                array_push($OutputList, $Subr);
                array_push($OutputList, new UInt8Item(chr(19))); // Subrs
            }
            // Else copy the entire range
            else
                array_push($OutputList, new RangeItem($buf,$p1,$p2-$p1));
        }
    }

    /**
    * the function marks the beginning of the subrs index and adds the subsetted subrs
    * index to the output list. 
    * @param Font the font
    * @param PrivateBase IndexBaseItem for the private that's referencing to the subrs
    * @param Subrs OffsetItem for the subrs
    * @throws IOException
    */
    function CreateNonCIDSubrs($Font,IndexBaseItem $PrivateBase,OffsetItem $Subrs)
    {
        // Mark the beginning of the Subrs index
        array_push($OutputList, new SubrMarkerItem($Subrs,$PrivateBase));
        // Put the subsetted new subrs index
        array_push($OutputList, new RangeItem(new RandomAccessFileOrArray($NewSubrsIndexNonCID),0,itextphp_bytes_getSize($NewSubrsIndexNonCID)));
    }


}



?>