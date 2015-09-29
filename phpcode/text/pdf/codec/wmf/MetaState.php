<?PHP
/*
 * $Id: MetaState.php,v 1.2 2005/11/10 18:14:19 mstaylor Exp $
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

require_once("../../../../awt/Color.php");
require_once("../../../../awt/Point.php");
require_once("MetaPen.php");
require_once("MetaBrush.php");
require_once("MetaFont.php");
require_once("MetaObject.php");
require_once("MetaState.php");
require_once("../../PdfContentByte.php");



class MetaState {

    const TA_NOUPDATECP = 0;
    const TA_UPDATECP = 1;
    const TA_LEFT = 0;
    const TA_RIGHT = 2;
    const TA_CENTER = 6;
    const TA_TOP = 0;
    const TA_BOTTOM = 8;
    const TA_BASELINE = 24;

    const TRANSPARENT = 1;
    const OPAQUE = 2;

    const ALTERNATE = 1;
    const WINDING = 2;

    public $savedStates = array();
    public $MetaObjects = array();
    public $currentPoint = NULL;//Point
    public $currentPen = NULL; //MetaPen 
    public $currentBrush = NULL;//MetaBrush
    public $currentFont = NULL;//MetaFont
    public $currentBackgroundColor = NULL;//color
    public $currentTextColor = NULL;//color
    public $backgroundMode = 0;
    public $polyFillMode = 0;
    public $lineJoin = 1;
    public $textAlign = 0;
    public $offsetWx = 0;
    public $offsetWy = 0;
    public $extentWx = 0;
    public $extentWy = 0;
    public $scalingX = 0.0;
    public $scalingY = 0.0;


    private function onConstruct()
    {
        $currentBackgroundColor = Color::$white;
        $currentTextColor = Color::$black;
        $backgroundMode = MetaState::OPAQUE;
        $polyFillMode = MetaState::ALTERNATE;
    }

    public function __construct()
    {
        onConstruct();
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                construct0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                construct1arg($arg1);
                break;
            }
        }
    }

    public function construct0args()
    {
        $savedStates = array();
        $MetaObjects = array();
        $currentPoint = new Point(0, 0);
        $currentPen = new MetaPen();
        $currentBrush = new MetaBrush();
        $currentFont = new MetaFont();
    }

    public function construct1arg(MetaState $state) {
        setMetaState($state);
    }


    public function setMetaState(MetaState $state) {
        $savedStates = $state->savedStates;
        $MetaObjects = $state->MetaObjects;
        $currentPoint = $state->currentPoint;
        $currentPen = $state->currentPen;
        $currentBrush = $state->currentBrush;
        $currentFont = $state->currentFont;
        $currentBackgroundColor = $state->currentBackgroundColor;
        $currentTextColor = $state->currentTextColor;
        $backgroundMode = $state->backgroundMode;
        $polyFillMode = $state->polyFillMode;
        $textAlign = $state->textAlign;
        $lineJoin = $state->lineJoin;
        $offsetWx = $state->offsetWx;
        $offsetWy = $state->offsetWy;
        $extentWx = $state->extentWx;
        $extentWy = $state->extentWy;
        $scalingX = $state->scalingX;
        $scalingY = $state->scalingY;
    }

    public function addMetaObject(MetaObject $object) {
        for ($k = 0; $k < count($MetaObjects); ++$k) {
            if ($MetaObjects[$k] == NULL) {
                $MetaObjects[$k] = $object;
                return;
            }
        }
        array_push($MetaObjects, $object);
    }

    public function selectMetaObject($index, PdfContentByte $cb) {
        $obj = MetaObjects[$index];
        if ($obj == NULL)
            return;
        $style = 0;
        switch ($obj->getType()) {
            case MetaObject::META_BRUSH:
                $currentBrush = $obj;
                $style = $currentBrush->getStyle();
                if ($style == MetaBrush::BS_SOLID) {
                    $color = $currentBrush->getColor();
                    $cb->setColorFill($color);
                }
                else if ($style == MetaBrush::BS_HATCHED) {
                    $color = $currentBackgroundColor;
                    $cb->setColorFill($color);
                }
                break;
            case MetaObject::META_PEN:
            {
                $currentPen = $obj;
                $style = $currentPen->getStyle();
                if ($style != MetaPen::PS_NULL) {
                    $color = $currentPen->getColor();
                    $cb->setColorStroke($color);
                    $cb->setLineWidth(abs((float)$currentPen->getPenWidth() * $scalingX / $extentWx));
                    switch ($style) {
                        case MetaPen::PS_DASH:
                            $cb->setLineDash(18, 6, 0);
                            break;
                        case MetaPen::PS_DASHDOT:
                            $cb->setLiteral("[9 6 3 6]0 d\n");
                            break;
                        case MetaPen::PS_DASHDOTDOT:
                            $cb->setLiteral("[9 3 3 3 3 3]0 d\n");
                            break;
                        case MetaPen::PS_DOT:
                            $cb->setLineDash(3, 0);
                            break;
                        default:
                            $cb->setLineDash(0);
                            break;
                    }
                }
                break;
            }
            case MetaObject::META_FONT:
            {
                $currentFont = $obj;
                break;
            }
        }
    }


    public function deleteMetaObject($index) {
        unset($MetaObjects[$index]);
    }

    public function saveState(PdfContentByte $cb) {
        $cb->saveState();
        $state = new MetaState($this);
        array_push($savedStates, $this);
    }

    public function restoreState($index, PdfContentByte $cb) {
        $pops = 0;
        if ($index < 0)
            $pops = min(-$index, count($savedStates));
        else
            $pops = max(count($savedStates) - $index, 0);
        if ($pops == 0)
            return;
        $state = NULL;//MetaState
        while ($pops-- != 0) {
            $cb->restoreState();
            $state = $savedStates[count($savedStates)-1];
            unset($savedStates[count($savedStates)-1]);
        }
        setMetaState($state);
    }


    public function transformX($x) {
        return ((float)$x - $offsetWx) * $scalingX / $extentWx;
    }

    public function transformY($y) {
        return (1.0 - ((float)$y - $offsetWy) / $extentWy) * $scalingY;
    }

    public function setScalingX($scalingX) {
        $this->scalingX = $scalingX;
    }

    public function setScalingY($scalingY) {
        $this->scalingY = $scalingY;
    }

    public function setOffsetWx($offsetWx) {
        $this->offsetWx = $offsetWx;
    }

    public function setOffsetWy($offsetWy) {
        $this->offsetWy = $offsetWy;
    }

    public function setExtentWx($extentWx) {
        $this->extentWx = $extentWx;
    }

    public function setExtentWy($extentWy) {
        $this->extentWy = $extentWy;
    }

    public function transformAngle($angle) {
        $ta = $extentWy < 0 ? -$angle : $angle;
        return $extentWx < 0 ? 180 - $ta : $ta;
    }

    public function setCurrentPoint(Point $p) {
        $currentPoint = $p;
    }

    public function getCurrentPoint() {
        return $currentPoint;
    }

    public function getCurrentBrush() {
        return $currentBrush;
    }

    public function getCurrentPen() {
        return $currentPen;
    }

    public function getCurrentFont() {
        return $currentFont;
    }

    /** Getter for property currentBackgroundColor.
    * @return Value of property currentBackgroundColor.
    */
    public function getCurrentBackgroundColor() {
        return $currentBackgroundColor;
    }

    /** Setter for property currentBackgroundColor.
    * @param currentBackgroundColor New value of property currentBackgroundColor.
    */
    public function setCurrentBackgroundColor(Color $currentBackgroundColor) {
        $this->currentBackgroundColor = $currentBackgroundColor;
    }

    /** Getter for property currentTextColor.
    * @return Value of property currentTextColor.
    */
    public function getCurrentTextColor() {
        return $currentTextColor;
    }

    /** Setter for property currentTextColor.
    * @param currentTextColor New value of property currentTextColor.
    */
    public function setCurrentTextColor(Color $currentTextColor) {
        $this->currentTextColor = $currentTextColor;
    }

    /** Getter for property backgroundMode.
    * @return Value of property backgroundMode.
    */
    public function getBackgroundMode() {
        return $backgroundMode;
    }


    /** Setter for property backgroundMode.
    * @param backgroundMode New value of property backgroundMode.
    */
    public function setBackgroundMode($backgroundMode) {
        $this->backgroundMode = $backgroundMode;
    }

    /** Getter for property textAlign.
    * @return Value of property textAlign.
    */
    public function getTextAlign() {
        return $textAlign;
    }

    /** Setter for property textAlign.
    * @param textAlign New value of property textAlign.
    */
    public function setTextAlign($textAlign) {
        $this->textAlign = $textAlign;
    }

    /** Getter for property polyFillMode.
     * @return Value of property polyFillMode.
     */
    public int getPolyFillMode() {
        return polyFillMode;
    }

    /** Setter for property polyFillMode.
    * @param polyFillMode New value of property polyFillMode.
    */
    public function setPolyFillMode($polyFillMode) {
        $this->polyFillMode = $polyFillMode;
    }

    public function setLineJoinRectangle(PdfContentByte $cb) {
        if ($lineJoin != 0) {
            $lineJoin = 0;
            $cb->setLineJoin(0);
        }
    }

    public function setLineJoinPolygon(PdfContentByte $cb) {
        if ($lineJoin == 0) {
            $lineJoin = 1;
            $cb->setLineJoin(1);
        }
    }

    public function getLineNeutral() {
        return ($lineJoin == 0);
    }


}



?>