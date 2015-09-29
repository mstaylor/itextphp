<?PHP
/*
 * $Id: PdfShading.php,v 1.2 2005/10/19 20:11:37 mstaylor Exp $
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


require_once("../../awt/Color.php");
require_once("../../exceptions/IOException.php");
require_once("../../exceptions/IllegalArgumentException.php");
require_once("PdfDictionary.php");
require_once("PdfWriter.php");
require_once("ColorDetails.php");
require_once("PdfName.php");
require_once("PdfIndirectReference.php");
require_once("ExtendedColor.php");
require_once("PdfObject.php");
require_once("SpotColor.php");
require_once("GrayColor.php");
require_once("CMYKColor.php");
require_once("PdfFunction.php");
require_once("PdfArray.php");
require_once("PdfNumber.php");
require_once("PdfBoolean.php");



class PdfShading
{

    protected $shading = NULL;

    protected $writer = NULL;

    protected $shadingType = 0;

    protected $colorDetails = NULL;

    protected $shadingName = NULL;

    protected $shadingReference = NULL;

    private $cspace = NULL;

    /** Holds value of property bBox. */
    protected $bBox = array();

    /** Holds value of property antiAlias. */
    protected $antiAlias = FALSE;

    /** Creates new PdfShading */
    protected function __construct(PdfWriter $writer) {
        $this->writer = $writer;
    }

    protected function setColorSpace(Color $color) {
        $cspace = $color;
        $type = ExtendedColor::getType($color);
        $colorSpace = NULL;
        switch ($type) {
            case ExtendedColor::TYPE_GRAY: {
                $colorSpace = PdfName::$DEVICEGRAY;
                break;
            }
            case ExtendedColor::TYPE_CMYK: {
                $colorSpace = PdfName::$DEVICECMYK;
                break;
            }
            case ExtendedColor::TYPE_SEPARATION: {
                $spot = $color;
                $colorDetails = $writer->addSimple($spot->getPdfSpotColor());
                $colorSpace = $colorDetails->getIndirectReference();
                break;
            }
            case ExtendedColor::TYPE_PATTERN:
            case ExtendedColor::TYPE_SHADING: {
                PdfShading::throwColorSpaceError();
            }
            default:
                $colorSpace = PdfName::$DEVICERGB;
                break;
        }
        $shading->put(PdfName::$COLORSPACE, $colorSpace);
    }


    function getColorSpace() {
        return $cspace;
    }

    public static function throwColorSpaceError() {
        throw new IllegalArgumentException("A tiling or shading pattern cannot be used as a color space in a shading pattern");
    }

    public static function checkCompatibleColors(Color $c1, Color $c2) {
        $type1 = ExtendedColor::getType($c1);
        $type2 = ExtendedColor::getType($c2);
        if ($type1 != $type2)
            throw new IllegalArgumentException("Both colors must be of the same type.");
        if ($type1 == ExtendedColor::TYPE_SEPARATION && $c1->getPdfSpotColor() != $c2->getPdfSpotColor())
            throw new IllegalArgumentException("The spot color must be the same, only the tint can vary.");
        if ($type1 == ExtendedColor::TYPE_PATTERN || $type1 == ExtendedColor::TYPE_SHADING)
            PdfShading::throwColorSpaceError();
    }

    public static function getColorArray(Color $color) {
        $type = ExtendedColor::getType($color);
        switch ($type) {
            case ExtendedColor::TYPE_GRAY: {
                return array($color->getGray());
            }
            case ExtendedColor::TYPE_CMYK: {
                $cmyk = $color;
                return array($cmyk->getCyan(), $cmyk->getMagenta(), $cmyk->getYellow(), $cmyk->getBlack());
            }
            case ExtendedColor::TYPE_SEPARATION: {
                return array($color->getTint());
            }
            case ExtendedColor::TYPE_RGB: {
                return array($color->getRed() / 255.0, $color->getGreen() / 255.0, $color->getBlue() / 255.0);
            }
        }
        PdfShading::throwColorSpaceError();
        return NULL;
    }

    public static function type1(PdfWriter $writer, Color $colorSpace, array $domain, array $tMatrix, PdfFunction $function) {
        $sp = new PdfShading($writer);
        $sp->shading = new PdfDictionary();
        $sp->shadingType = 1;
        $sp->shading.put(PdfName::$SHADINGTYPE, new PdfNumber($sp->shadingType));
        $sp->setColorSpace($colorSpace);
        if ($domain != NULL)
            $sp->shading->put(PdfName::$DOMAIN, new PdfArray($domain));
        if ($tMatrix != NULL)
            $sp->shading->put(PdfName::$MATRIX, new PdfArray($tMatrix));
        $sp->shading->put(PdfName::$FUNCTION, $function->getReference());
        return $sp;
    }

     public static function type2(PdfWriter $writer, Color $colorSpace, array $coords, array $domain, PdfFunction $function, array $extend) {
        $sp = new PdfShading($writer);
        $sp->shading = new PdfDictionary();
        $sp->shadingType = 2;
        $sp->shading->put(PdfName::$SHADINGTYPE, new PdfNumber($sp->shadingType));
        $sp->setColorSpace($colorSpace);
        $sp->shading->put(PdfName::$COORDS, new PdfArray($coords));
        if ($domain != NULL)
            $sp->shading->put(PdfName::$DOMAIN, new PdfArray($domain));
        $sp->shading->put(PdfName::$FUNCTION, $function->getReference());
        if ($extend != NULL && ($extend[0] == TRUE || $extend[1] == TRUE)) {
            $array = new PdfArray($extend[0] ? PdfBoolean::$PDFTRUE : PdfBoolean::$PDFFALSE);
            $array->add($extend[1] ? PdfBoolean::$PDFTRUE : PdfBoolean::$PDFFALSE);
            $sp->shading->put(PdfName::$EXTEND, $array);
        }
        return $sp;
    }

     public static function type3(PdfWriter $writer, Color $colorSpace, array $coords, array $domain, PdfFunction $function, array $extend) {
        $sp = type2($writer, $colorSpace, $coords, $domain, $function, $extend);
        $sp->shadingType = 3;
        $sp->shading->put(PdfName::$SHADINGTYPE, new PdfNumber($sp->shadingType));
        return $sp;
    }

    public static function simpleAxial()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 7:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                $arg7 = func_get_arg(6);
                if ($arg1 instanceof PdfWriter && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) && is_float($arg5) == TRUE && $arg6 instanceof Color && $arg7 instanceof Color)
                    return PdfShading::simpleAxial7args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
                break;
            }
            case 9:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                $arg7 = func_get_arg(6);
                $arg8 = func_get_arg(7);
                $arg9 = func_get_arg(8);
                if ($arg1 instanceof PdfWriter && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE && is_float($arg5) == TRUE && $arg6 instanceof Color && $arg7 instanceof Color && is_boolean($arg8) == TRUE && is_boolean($arg9) == TRUE)
                    return PdfShading::simpleAxial9args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
                break;
            }
         }


    }
    private static function simpleAxial9args(PdfWriter $writer, $x0, $y0, $x1, $y1, Color $startColor, Color $endColor, $extendStart, $extendEnd) {
        PdfShading::checkCompatibleColors($startColor, $endColor);
        $function = PdfFunction::type2($writer, array(0, 1), NULL, PdfShading::getColorArray($startColor),
            PdfShading::getColorArray($endColor), 1);
        return PdfShading::type2($writer, $startColor, array($x0, $y0, $x1, $y1), NULL, $function, array($extendStart, $extendEnd));
    }

    private static function simpleAxial7args(PdfWriter $writer, $x0, $y0, $x1, $y1, Color $startColor, Color $endColor) {
        return PdfShading::simpleAxial($writer, $x0, $y0, $x1, $y1, $startColor, $endColor, TRUE, TRUE);
    }

    public static function simpleRadial()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 9:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                $arg7 = func_get_arg(6);
                $arg8 = func_get_arg(7);
                $arg9 = func_get_arg(8);
                if ($arg1 instanceof PdfWriter && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE && is_float($arg5) == TRUE && is_float($arg6) == TRUE && is_float($arg7) && $arg8 instanceof Color && $arg9 instanceof Color)
                   return PdfShading::simpleRadial9args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
                break;
            }
            case 11:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                $arg7 = func_get_arg(6);
                $arg8 = func_get_arg(7);
                $arg9 = func_get_arg(8);
                $arg10 = func_get_arg(9);
                $arg11 = func_get_arg(10);
                if ($arg1 instanceof PdfWriter && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE && is_float($arg5) == TRUE && is_float($arg6) == TRUE && is_float($arg7) == TRUE && $arg8 instanceof Color && $arg9 instanceof Color && is_boolean($arg10) == TRUE && is_boolean($arg11) == TRUE)
                    return PdfShading::simpleRadial11args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9, $arg10, $arg11);
                break;
            }
        }
    }

    private static function simpleRadial11args(PdfWriter $writer, $x0, $y0, $r0, $x1, $y1, $r1, Color $startColor, Color $endColor, $extendStart, $extendEnd) {
        PdfShading::checkCompatibleColors($startColor, $endColor);
        $function = PdfFunction::type2($writer, array(0, 1), NULL, PdfShading::getColorArray($startColor),
            PdfShading::getColorArray($endColor), 1);
        return PdfShading::type3($writer, $startColor, array($x0, $y0, $r0, $x1, $y1, $r1), NULL, $function, array($extendStart, $extendEnd));
    }

    private static function simpleRadial9args(PdfWriter $writer, $x0, $y0, $r0, $x1, $y1, $r1, Color $startColor, Color $endColor) {
        return PdfShading::simpleRadial($writer, $x0, $y0, $r0, $x1, $y1, $r1, $startColor, $endColor, TRUE, TRUE);
    }

    function getShadingName() {
        return $shadingName;
    }

    function getShadingReference() {
        if ($shadingReference == NULL)
            $shadingReference = $writer->getPdfIndirectReference();
        return $shadingReference;
    }

    function setName($number) {
        $shadingName = new PdfName("Sh" . $number);
    }

    function addToBody() {
        if ($bBox != NULL)
            $shading->put(PdfName::$BBOX, new PdfArray($bBox));
        if ($antiAlias == TRUE)
            $shading->put(PdfName::$ANTIALIAS, PdfBoolean::$PDFTRUE);
        $writer->addToBody($shading, getShadingReference());
    }

    function getWriter() {
        return $writer;
    }

    function getColorDetails() {
        return $colorDetails;
    }

    public function getBBox() {
        return $bBox;
    }

    public function setBBox(array $bBox) {
        if (count($bBox) != 4)
            throw new IllegalArgumentException("BBox must be a 4 element array.");
        $this->bBox = $bBox;
    }

    public function isAntiAlias() {
        return $antiAlias;
    }

    public function setAntiAlias($antiAlias) {
        $this->antiAlias = $antiAlias;
    }

}


?>