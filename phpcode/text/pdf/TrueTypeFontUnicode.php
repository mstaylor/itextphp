<?PHP
/*
 * $Id: TrueTypeFontUnicode.php,v 1.2 2005/09/29 22:02:44 mstaylor Exp $
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

require_once("../../exceptions/IOException.php");
require_once("../DocumentException.php");
require_once("../../util/StringHelpers.php");
require_once("BaseFont.php");
require_once("TrueTypeFont.php");
require_once("PdfStream.php");
require_once("PdfEncodings.php");
require_once("PdfDictionary.php")
require_once("PdfIndirectReference.php");
require_once("PdfName.php");
require_once("PdfString.php");
require_once("PdfNumber.php");
require_once("PdfLiteral.php");
require_once("PdfArray.php");
require_once("PdfWriter.php");
require_once("RandomAccessFileOrArray.php");
require_once("CFFFontSubset.php");
require_once("TrueTypeFontSubSet.php");

/** Represents a True Type font with Unicode encoding. All the character
* in the font can be used directly by using the encoding Identity-H or
* Identity-V. This is the only way to represent some character sets such
* as Thai.
* @author  Paulo Soares (psoares@consiste.pt)
*/
class TrueTypeFontUnicode extends TrueTypeFont
{
    /** <CODE>true</CODE> if the encoding is vertical.
    */
    $vertical = FALSE;

    /** Creates a new TrueType font addressed by Unicode characters. The font
    * will always be embedded.
    * @param ttFile the location of the font on file. The file must end in '.ttf'.
    * The modifiers after the name are ignored.
    * @param enc the encoding to be applied to this font
    * @param emb true if the font is to be embedded in the PDF
    * @param ttfAfm the font as a <CODE>byte</CODE> array
    * @throws DocumentException the font is invalid
    * @throws IOException the font file could not be read
    */
    function __construct($ttFile, $enc, $emb, $ttfAfm)  {
        $nameBase = BaseFont::getBaseName($ttFile);
        $ttcName = TrueTypeFont::getTTCName($nameBase);
        if (strlen($nameBase) < strlen($ttFile)) {
            $style = substr($ttFile, strlen($nameBase));
        }
        $encoding = $enc;
        $embedded = $emb;
        $fileName = $ttcName;
        $ttcIndex = "";
        if (strlen($ttcName) < strlen($nameBase))
            $ttcIndex = substr($nameBase, strlen($ttcName) + 1);
        $fontType = BaseFont::FONT_TYPE_TTUNI;
        if ((endsWith(strtolower($fileName), ".ttf") == TRUE || endsWith(strtolower($fileName), ".otf") == TRUE || endsWith(strtolower($fileName), ".ttc") == TRUE) && ((strcmp($enc, BaseFont::IDENTITY_H) == 0|| strcmp($enc, BaseFont::IDENTITY_V) == 0) && $emb == TRUE)) {
            process($ttfAfm);
            if ($os_2->fsType == 2)
                throw new DocumentException($fileName . $style . " cannot be embedded due to licensing restrictions.");
            // Sivan
            if (($cmap31 == NULL && $fontSpecific == FALSE) || ($cmap10 == NULL && $fontSpecific == TRUE))
                $directTextToByte=TRUE;
                //throw new DocumentException(fileName + " " + style + " does not contain an usable cmap.");
            if ($fontSpecific = TRUE) {
                $fontSpecific = FALSE;
                $tempEncoding = $encoding;
                $encoding = "";
                createEncoding();
                $encoding = $tempEncoding;
                $fontSpecific = TRUE;
            }
        }
        else
            throw new DocumentException($fileName . " " . $style . " is not a TTF font file.");
        $vertical = endsWith($enc, "V");
    }

    /**
    * Gets the width of a <CODE>String</CODE> in normalized 1000 units.
    * @param text the <CODE>String</CODE> to get the witdth of
    * @return the width in normalized 1000 units
    */
    public function getWidth($text)
    {
        if ($vertical  == TRUE)
            return strlen($text) * 1000;
        $total = 0;
        if ($fontSpecific == TRUE) {
            $cc[] = $text;
            $ptr = 0;
            $len = strlen($cc);
            for ($k = 0; $k < $len; ++$k) {
                $c = $cc[$k];
                if (($c & 0xff00) == 0 || ($c & 0xff00) == 0xf000)
                    $total .= getRawWidth($c & 0xff, NULL);
            }
        }
        else {
            $len = strlen($text);
            for ($k = 0; $k < $len; ++$k)
                $total .= getRawWidth($text[$k], $encoding);
        }
        return $total;
    }

    /** Creates a ToUnicode CMap to allow copy and paste from Acrobat.
    * @param metrics metrics[0] contains the glyph index and metrics[2]
    * contains the Unicode code
    * @throws DocumentException on error
    * @return the stream representing this CMap or <CODE>null</CODE>
    */
    private function getToUnicode(array $metrics) 
    {
        if (count($metrics) == 0)
            return NULL;
        $buf = "/CIDInit /ProcSet findresource begin\n" .
        "12 dict begin\n" .
        "begincmap\n" .
        "/CIDSystemInfo\n" .
        "<< /Registry (Adobe)\n" .
        "/Ordering (UCS)\n" .
        "/Supplement 0\n" .
        ">> def\n" .
        "/CMapName /Adobe-Identity-UCS def\n" .
        "/CMapType 2 def\n" .
        "1 begincodespacerange\n" .
        toHex(($metrics[0])[0]) . toHex(($metrics[strlen($metrics) - 1])[0]) . "\n" .
        "endcodespacerange\n";
        $size = 0;
        for ($k = 0; $k < count($metrics); ++$k) {
            if ($size == 0) {
                if ($k != 0) {
                    $buf .= "endbfrange\n";
                }
                $size = min(100, count($metrics) - $k);
                $buf .= $size . " beginbfrange\n";
            }
            --$size;
            $metric = $metrics[$k];
            $fromTo = toHex($metric[0]);
            $buf .= $fromTo . $fromTo . toHex($metric[2] . "\n";
        }
        $buf .= "endbfrange\n" .
        "endcmap\n" .
        "CMapName currentdict /CMap defineresource pop\n" .
        "end end\n";
        $s = $buf;
        $stream = new PdfStream(PdfEncodings::convertToBytes($s, null));
        $stream->flateCompress();
        return $stream;
    }

    /** Gets an hex string in the format "&lt;HHHH&gt;".
    * @param n the number
    * @return the hex string
    */
    static function toHex($n) {
        $str = pack("c", $n);
        $s = bin2hex($str);
        return substr("<0000", 0, 5 - strlen($s)) . $s . ">";
    }

    /** Generates the CIDFontTyte2 dictionary.
    * @param fontDescriptor the indirect reference to the font descriptor
    * @param subsetPrefix the subset prefix
    * @param metrics the horizontal width metrics
    * @return a stream
    */
    private function getCIDFontType2(PdfIndirectReference $fontDescriptor, $subsetPrefix, array $metrics) {
        $dic = new PdfDictionary(PdfName::$FONT);
        // sivan; cff
        if ($cff == TRUE) {
            $dic->put(PdfName::$SUBTYPE, PdfName::CIDFONTTYPE0);
            $dic->put(PdfName::$BASEFONT, new PdfName($fontName . "-" . $encoding));
        }
        else {
            $dic->put(PdfName::$SUBTYPE, PdfName::$CIDFONTTYPE2);
            $dic->put(PdfName::$BASEFONT, new PdfName($subsetPrefix . $fontName));
        }
        $dic->put(PdfName::$FONTDESCRIPTOR, $fontDescriptor);
        if ($cff == FALSE)
            $dic->put(PdfName::$CIDTOGIDMAP,PdfName::$IDENTITY);
        $cdic = new PdfDictionary();
        $cdic->put(PdfName::$REGISTRY, new PdfString("Adobe"));
        $cdic->put(PdfName::$ORDERING, new PdfString("Identity"));
        $cdic->put(PdfName::$SUPPLEMENT, new PdfNumber(0));
        $dic->put(PdfName::$CIDSYSTEMINFO, $cdic);
        if ($vertical == FALSE) {
            $dic->put(PdfName::$DW, new PdfNumber(1000));
            $buf = "[";
            $lastNumber = -10;
            $firstTime = TRUE;
            for ($k = 0; k < count($metrics); ++$k) {
                $metric = $metrics[$k];
                if ($metric[1] == 1000)
                    continue;
                $m = $metric[0];
                if ($m == $lastNumber + 1) {
                    $buf .= " " . $metric[1];
                }
                else {
                    if ($firstTime == FALSE) {
                        $buf .= "]";
                    }
                    $firstTime = FALSE;
                    $buf .= $m . "[" . $metric[1];
                }
                $lastNumber = $m;
            }
            if (strlen($buf) > 1) {
                $buf .= "]]";
                $dic->put(PdfName::$W, new PdfLiteral($buf));
            }
        }
        return $dic;
    }

    /** Generates the font dictionary.
    * @param descendant the descendant dictionary
    * @param subsetPrefix the subset prefix
    * @param toUnicode the ToUnicode stream
    * @return the stream
    */
    private function getFontBaseType(PdfIndirectReference $descendant, $subsetPrefix, PdfIndirectReference $toUnicode) {
        $dic = new PdfDictionary(PdfName::$FONT);

        $dic->put(PdfName::$SUBTYPE, PdfName::$TYPE0);
        // The PDF Reference manual advises to add -encoding to CID font names
        if ($cff == TRUE)
            $dic->put(PdfName::$BASEFONT, new PdfName($fontName . "-" $encoding));
        //dic.put(PdfName.BASEFONT, new PdfName(subsetPrefix+fontName));
        else
            $dic->put(PdfName::$BASEFONT, new PdfName($subsetPrefix . $fontName));
        //dic.put(PdfName.BASEFONT, new PdfName(fontName));
        $dic->put(PdfName::$ENCODING, new PdfName($encoding));
        $dic->put(PdfName::$DESCENDANTFONTS, new PdfArray($descendant));
        if ($toUnicode != NULL)
            $dic->put(PdfName::$TOUNICODE, $toUnicode);
        return $dic;
    }

    /** The method used to sort the metrics array.
    * @param o1 the first element
    * @param o2 the second element
    * @return the comparisation
    */
    public function compare($o1, $o2) {
        $m1 = ($o1)[0];
        $m2 = ($o2)[0];
        if ($m1 < $m2)
            return -1;
        if ($m1 == $m2)
            return 0;
        return 1;
    }

    /** Outputs to the writer the font dictionaries and streams.
    * @param writer the writer for this document
    * @param ref the font indirect reference
    * @param params several parameters that depend on the font type
    * @throws IOException on error
    * @throws DocumentException error in generating the object
    */
    void writeFont(PdfWriter $writer, PdfIndirectReference $ref, array $params) 
    {
        $longTag = $params[0];
        $metrics = array_values($longTag);
        usort($metrics, array("TrueTypeFontUnicode", "compare"));
        $ind_font = NULL;
        $pobj = NULL;
        $obj = NULL;
        $closed = FALSE;
        // sivan: cff
        if ($cff == TRUE) {
            $rf2 = new RandomAccessFileOrArray($rf);
            $b = itextphp_bytes_create($cffLength);
            try {
                $rf2->reOpen();
                $rf2->seek($cffOffset);
                $rf2->readFully($b);
            } catch {
                try {
                    $rf2->close();
                } catch (Exception $e) {
                // empty on purpose
                }
                $closed = TRUE;
            }
            if ($closed == FALSE)
            {
                try {
                    $rf2->close();
                } catch (Exception $e) {
                // empty on purpose
                }
                $closed = TRUE;
            }
            /*
            CFFFont cffFont = new CFFFont(new RandomAccessFileOrArray(b));
            // test if we can find the font by name and if it's a type1 CFF
            if (cffFont.exists(fontName) && !cffFont.isCID(fontName)) {
            byte[] cid = cffFont.getCID( (cffFont.getNames())[0] );
            if (cid != null) b=cid;
            }
            */

            $cff = new CFFFontSubset(new RandomAccessFileOrArray($b),$longTag);
            $b = $cff->Process( ($cff->getNames())[0] );

            // if the font is already CID, or not found by name, or 
            // getCID returned null, we just use the data in the CFF
            // table and hope for the best.


            // for debugging, force a reparsing
            /*
            java.lang.System.err.println("");
            java.lang.System.err.println("");
            java.lang.System.err.println("");
            CFFFont dummy = new CFFFont(java.nio.ByteBuffer.wrap(b));	
            java.lang.System.err.println("");
            java.lang.System.err.println("");
            java.lang.System.err.println("");
            */	
            $pobj = new StreamFont($b, "CIDFontType0C");
            $obj = $writer->addToBody($pobj);
            $ind_font = $obj->getIndirectReference();
        } else {
          $sb = new TrueTypeFontSubSet($fileName, new RandomAccessFileOrArray($rf), $longTag, $directoryOffset, FALSE);
          $b = $sb.process();
          $lengths = array(itextphp_bytes_getSize($b));
          $pobj = new StreamFont($b, $lengths);
          $obj = writer.addToBody($pobj);
          $ind_font = $obj->getIndirectReference();
        }
        $subsetPrefix = BaseFont::createSubsetPrefix();
        //if (cff) subsetPrefix = "";
        $dic = getFontDescriptor($ind_font, $subsetPrefix);
        $obj = $writer->addToBody($dic);
        $ind_font = $obj->getIndirectReference();

        $pobj = getCIDFontType2($ind_font, $subsetPrefix, $metrics);
        $obj = $writer->addToBody($pobj);
        $ind_font = $obj->getIndirectReference();

        $pobj = getToUnicode($metrics);
        $toUnicodeRef = NULL;

        if ($pobj != NULL) {
            $obj = $writer->addToBody($pobj);
            $toUnicodeRef = $obj->getIndirectReference();
        }

        $pobj = getFontBaseType($ind_font, $subsetPrefix, $toUnicodeRef);
        $writer->addToBody($pobj, $ref);
    }

    /** A forbidden operation. Will throw a null pointer exception.
    * @param text the text
    * @return always <CODE>null</CODE>
    */
    function convertToBytes($text)
    {
        return NULL;
    }

    /**
    * Checks if a character exists in this font.
    * @param c the character to check
    * @return <CODE>true</CODE> if the character has a glyph,
    * <CODE>false</CODE> otherwise
    */
    public function charExists($c) {
        $map = NULL;
        if ($fontSpecific == TRUE)
            $map = $cmap10;
        else
            $map = $cmap31;
        if ($map == NULL)
            return FALSE;
        if (fontSpecific) {
            if (($c & 0xff00) == 0 || ($c & 0xff00) == 0xf000)
                return $map[($c & 0xff)] != NULL;
            else
                return FALSE;
        }
        else
            return $map[ord($c)] != NULL;
    }

    /**
    * Sets the character advance.
    * @param c the character
    * @param advance the character advance normalized to 1000 units
    * @return <CODE>true</CODE> if the advance was set,
    * <CODE>false</CODE> otherwise
    */
    public function setCharAdvance($c, $advance) {
        $map = NULL;
        if ($fontSpecific == TRUE)
            $map = $cmap10;
        else
            $map = $cmap31;
        if ($map == NULL)
            return FALSE;
        $m = NULL;
        if ($fontSpecific == TRUE) {
            if (($c & 0xff00) == 0 || ($c & 0xff00) == 0xf000)
                $m = $map[($c & 0xff)];
            else
                return FALSE;
        }
        else
            $m = $map[ord($c)];
        if ($m == NULL)
            return FALSE;
        else
            $m[1] = $advance;
        return TRUE;
    }

     public function getCharBBox($c) {
        if ($bboxes == NULL)
            return NULL;
        $map = NULL;
        if ($fontSpecific == TRUE)
            $map = $cmap10;
        else
            $map = $cmap31;
        if ($map == NULL)
            return NULL;
        $m = NULL;
        if ($fontSpecific == TRUE) {
            if (($c & 0xff00) == 0 || ($c & 0xff00) == 0xf000)
                $m = $map[($c & 0xff)];
            else
                return NULL;
        }
        else
            $m = $map[ord($c)];
        if ($m == NULL)
            return NULL;
        return $bboxes[$m[0]];
    }



}

?>