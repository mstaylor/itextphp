<?PHP
/*
 * $Id: MetaFont.php,v 1.2 2005/11/10 18:14:19 mstaylor Exp $
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

require_once("MetaObject.php");
require_once("../../BaseFont.php");
require_once("InputMeta.php");
require_once("MetaState.php");

class MetaFont extends MetaObject
{

    protected static $fontNames = array(
        "Courier", "Courier-Bold", "Courier-Oblique", "Courier-BoldOblique",
        "Helvetica", "Helvetica-Bold", "Helvetica-Oblique", "Helvetica-BoldOblique",
        "Times-Roman", "Times-Bold", "Times-Italic", "Times-BoldItalic",
        "Symbol", "ZapfDingbats");

    protected static $MARKER_BOLD = 1;
    protected static $MARKER_ITALIC = 2;
    protected static $MARKER_COURIER = 0;
    protected static $MARKER_HELVETICA = 4;
    protected static $MARKER_TIMES = 8;
    protected static $MARKER_SYMBOL = 12;

    protected static $DEFAULT_PITCH = 0;
    protected static $FIXED_PITCH = 1;
    protected static $VARIABLE_PITCH = 2;
    protected static $FF_DONTCARE = 0;
    protected static $FF_ROMAN = 1;
    protected static $FF_SWISS = 2;
    protected static $FF_MODERN = 3;
    protected static $FF_SCRIPT = 4;
    protected static $FF_DECORATIVE = 5;
    protected static $BOLDTHRESHOLD = 600;    
    protected static $nameSize = 32;
    protected static $ETO_OPAQUE = 2;
    protected static $ETO_CLIPPED = 4;

    protected $height = 0;
    protected $angle = 0.0;
    protected $bold = 0;
    protected $italic = 0;
    protected $underline = FALSE;
    protected $strikeout = FALSE;
    protected $charset = 0;
    protected $pitchAndFamily = 0;
    protected $faceName = "arial";
    protected $font = NULL; //basefont


    public function __construct() {
        $type = MetaObject::META_FONT;
    }

    public function init(InputMeta $in) {
        $height = abs($in->readShort());
        $in->skip(2);
        $angle = (float)($in->readShort() / 1800.0 * M_PI);
        $in->skip(2);
        $bold = ($in->readShort() >= MetaFont::$BOLDTHRESHOLD ? MetaFont::$MARKER_BOLD : 0);
        $italic = ($in->readByte() != 0 ? MetaFont::$MARKER_ITALIC : 0);
        $underline = ($in->readByte() != 0);
        $strikeout = ($in->readByte() != 0);
        $charset = $in->readByte();
        $in->skip(3);
        $pitchAndFamily = $in->readByte();
        $name = itextphp_bytes_create($nameSize);
        $k = 0;
        for ($k = 0; $k < $nameSize; ++$k) {
            $c = $in->readByte();
            if ($c == 0) {
                break;
            }
            itextphp_bytes_write(($name, $k, itextphp_bytes_createfromInt($c), 0);
        }
        try {
            $faceName = itextphp_bytes_getBytesBasedonEncoding(substr($name, 0, $k), "Cp1252");
        }
        catch (UnsupportedEncodingException $e) {
            $faceName = substr($name, 0, $k);
        }
        $faceName = strtolower($faceName);
    }

    public function getFont() {
        if ($font != NULL)
            return $font;
        $fontName = NULL;//should be a string
        if (strpos($faceName, "courier") != FALSE || strpos($faceName, "terminal") != FALSE
            || strpos($faceName, "fixedsys") != FALSE) {
            $fontName = MetaFont::$fontNames[MetaFont::$MARKER_COURIER + $italic + $bold];
        }
        else if (strpos($faceName, "ms sans serif") != FALSE || strpos($faceName, "arial") != FALSE
            || strpos($faceName, "system") != FALSE) {
            $fontName = MetaFont::$fontNames[MetaFont::$MARKER_HELVETICA + $italic + $bold];
        }
        else if (strpos($faceName, "arial black") != FALSE) {
            $fontName = MetaFont::$fontNames[MetaFont::$MARKER_HELVETICA + $italic + MetaFont::$MARKER_BOLD];
        }
        else if (strpos($faceName, "times") != FALSE || strpos($faceName, "ms serif") != FALSE || strpos($faceName, "roman") != FALSE) {
            $fontName = $fontNames[MetaFont::$MARKER_TIMES + $italic + $bold];
        }
        else if (strpos($faceName, "symbol") != FALSE) {
            $fontName = $fontNames[MetaFont::$MARKER_SYMBOL];
        }
        else {
            $pitch = $pitchAndFamily & 3;
            $family = ($pitchAndFamily >> 4) & 7;
            switch ($family) {
                case MetaFont::$FF_MODERN:
                    $fontName = $fontNames[MetaFont::$MARKER_COURIER + $italic + $bold];
                    break;
                case MetaFont::$FF_ROMAN:
                    $fontName = $fontNames[MetaFont::$MARKER_TIMES + $italic + $bold];
                    break;
                case MetaFont::$FF_SWISS:
                case MetaFont::$FF_SCRIPT:
                case MetaFont::$FF_DECORATIVE:
                    $fontName = $fontNames[MetaFont::$MARKER_HELVETICA + $italic + $bold];
                    break;
                default:
                {
                    switch ($pitch) {
                        case MetaFont::$FIXED_PITCH:
                            $fontName = $fontNames[MetaFont::$MARKER_COURIER + $italic + $bold];
                            break;
                        default:
                            $fontName = $fontNames[MetaFont::$MARKER_HELVETICA + $italic + $bold];
                            break;
                    }
                }
            }
        }
        try {
            $font = BaseFont::createFont($fontName, "Cp1252", FALSE);
        }
        catch (Exception $e) {
            throw new Exception($e);
        }

        return $font;
    }

    public function getAngle() {
        return $angle;
    }

    public function isUnderline() {
        return $underline;
    }

    public function isStrikeout() {
        return $strikeout;
    }

    public function getFontSize(MetaState $state) {
        return abs($state->transformY($height) - $state->transformY(0)) * 0.86;
    }

}



?>