<?PHP
/*
 * $Id: PdfGState.php,v 1.3 2005/10/25 16:25:54 mstaylor Exp $
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
require_once("PdfDictionary.php");
require_once("PdfBoolean.php");
require_once("PdfNumber.php");

/** The graphic state dictionary.
*
* @author Paulo Soares (psoares@consiste.pt)
*/

class PdfGState extends PdfDictionary 
{

    /** A possible blend mode */
    public static $BM_NORMAL = NULL;
    /** A possible blend mode */
    public static $BM_COMPATIBLE NULL;
    /** A possible blend mode */
    public static $BM_MULTIPLY = NULL;
    /** A possible blend mode */
    public static $BM_SCREEN = NULL;
    /** A possible blend mode */
    public static $BM_OVERLAY = NULL;
    /** A possible blend mode */
    public static $BM_DARKEN = NULL;
    /** A possible blend mode */
    public static $BM_LIGHTEN = NULL;
    /** A possible blend mode */
    public static $BM_COLORDODGE = NULL;
    /** A possible blend mode */
    public static $BM_COLORBURN = NULL;
    /** A possible blend mode */
    public static $BM_HARDLIGHT = NULL;
    /** A possible blend mode */
    public static $BM_SOFTLIGHT = NULL;
    /** A possible blend mode */
    public static $BM_DIFFERENCE = NULL;
    /** A possible blend mode */
    public static $BM_EXCLUSION = NULL;



    public static $initialized = FALSE;



    public static function initializeStatics()
    {
        if(PdfGState::$initialized == FALSE)
        {
            PdfGState::$BM_NORMAL = new PdfName("Normal");
            PdfGState::$BM_COMPATIBLE = new PdfName("Compatible");
            PdfGState::$BM_MULTIPLY = new PdfName("Multiply");
            PdfGState::$BM_SCREEN = new PdfName("Screen");
            PdfGState::$BM_OVERLAY = new PdfName("Overlay");
            PdfGState::$BM_DARKEN = new PdfName("Darken");
            PdfGState::$BM_LIGHTEN = new PdfName("Lighten");
            PdfGState::$BM_COLORDODGE = new PdfName("ColorDodge");
            PdfGState::$BM_COLORBURN = new PdfName("ColorBurn");
            PdfGState::$BM_HARDLIGHT = new PdfName("HardLight");
            PdfGState::$BM_SOFTLIGHT = new PdfName("SoftLight");
            PdfGState::$BM_DIFFERENCE = new PdfName("Difference");
            PdfGState::$BM_EXCLUSION = new PdfName("Exclusion");
            PdfGState::$initialized = TRUE;
        }
    }


    /**
    * Sets the flag whether to apply overprint for stroking.
    * @param ov
    */
    public function setOverPrintStroking($ov) {
        put(PdfName::$OP, $ov ? PdfBoolean::$PDFTRUE : PdfBoolean::$PDFFALSE);
    }

    /**
    * Sets the flag whether to apply overprint for non stroking painting operations.
    * @param ov
    */
    public function setOverPrintNonStroking($ov) {
        put(PdfName::$op, $ov ? PdfBoolean::$PDFTRUE : PdfBoolean::$PDFFALSE);
    }

    /**
    * Sets the current stroking alpha constant, specifying the constant shape or
    * constant opacity value to be used for stroking operations in the transparent
    * imaging model.
    * @param n
    */
    public function setStrokeOpacity($n) {
        put(PdfName::$CA, new PdfNumber($n));
    }

    /**
    * Sets the current stroking alpha constant, specifying the constant shape or
    * constant opacity value to be used for nonstroking operations in the transparent
    * imaging model.
    * @param n
    */
    public function setFillOpacity($n) {
        put(PdfName::$ca, new PdfNumber($n));
    }

    /**
    * The alpha source flag specifying whether the current soft mask
    * and alpha constant are to be interpreted as shape values (true)
    * or opacity values (false). 
    * @param v
    */
    public function setAlphaIsShape($v) {
        put(PdfName::$AIS, $v ? PdfBoolean::$PDFTRUE : PdfBoolean::$PDFFALSE);
    }

    /**
    * Determines the behaviour of overlapping glyphs within a text object
    * in the transparent imaging model.
    * @param v
    */
    public function setTextKnockout($v) {
        put(PdfName::$TK, $v ? PdfBoolean::$PDFTRUE : PdfBoolean::$PDFFALSE);
    }

    /**
    * The current blend mode to be used in the transparent imaging model.
    * @param bm
    */
    public function setBlendMode(PdfName $bm) {
        put(PdfName::$BM, $bm);
    }
}


PdfGState::initializeStatics();


?>