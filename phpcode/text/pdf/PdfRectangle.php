<?PHP
/*
 * $Id: PdfRectangle.php,v 1.2 2005/10/25 17:59:40 mstaylor Exp $
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



require_once("PdfArray.php");
require_once("PdfNumber.php");
require_once("PdfObject.php");
require_once("../Rectangle.php")

/**
* <CODE>PdfRectangle</CODE> is the PDF Rectangle object.
* <P>
* Rectangles are used to describe locations on the page and bounding boxes for several
* objects in PDF, such as fonts. A rectangle is represented as an <CODE>array</CODE> of
* four numbers, specifying the lower lef <I>x</I>, lower left <I>y</I>, upper right <I>x</I>,
* and upper right <I>y</I> coordinates of the rectangle, in that order.<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 7.1 (page 183).
*
* @see com.lowagie.text.Rectangle
* @see PdfArray
*/
class PdfRectangle extends PdfArray 
{

    // membervariables

    /** lower left x */
    private $llx = 0;

    /** lower left y */
    private $lly = 0;

    /** upper right x */
    private $urx = 0;

    /** upper right y */
    private $ury = 0;

    // constructors

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Rectangle)
                    construct1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if ($arg1 instanceof Rectangle && is_integer($arg2) == TRUE)
                    contruct2argsRectangle($arg1, $arg2);
                else if (is_float($arg1) == TRUE && is_float($arg2) == TRUE)
                    construct2argsFloat($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if (is_float($arg1) == TRUE && is_float($arg2) == TRUE && is_integer($arg3) == TRUE)
                    construct3args($arg1, $arg2, $arg3);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (is_float($arg1) == TRUE && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE)
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
                if (is_float($arg1) == TRUE && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE && is_integer($arg5) == TRUE)
                    construct5args($arg1, $arg2, $arg3, $arg4, $arg5);
                break;
            }
        }
    }


    /**
    * Constructs a <CODE>PdfRectangle</CODE>-object.
    *
    * @param		llx			lower left x
    * @param		lly			lower left y
    * @param		urx			upper right x
    * @param		ury			upper right y
    *
    * @since		rugPdf0.10
    */

    private function construct5args($llx, $lly, $urx, $ury, $rotation) {
        parent::__construct();
        if ($rotation == 90 || $rotation == 270) {
            $this->llx = $lly;
            $this->lly = $llx;
            $this->urx = $ury;
            $this->ury = $urx;
        }
        else {
            $this->llx = $llx;
            $this->lly = $lly;
            $this->urx = $urx;
            $this->ury = $ury;
        }
        parent::add(new PdfNumber($this->llx));
        parent::add(new PdfNumber($this->lly));
        parent::add(new PdfNumber($this->urx));
        parent::add(new PdfNumber($this->ury));
    }

    private function construct4args($llx, $lly, $urx, $ury) {
        construct5args($llx, $lly, $urx, $ury, 0);
    }

    /**
    * Constructs a <CODE>PdfRectangle</CODE>-object starting from the origin (0, 0).
    *
    * @param		urx			upper right x
    * @param		ury			upper right y
    */

    private function construct3args($urx, $ury, $rotation) {
        construct5args(0, 0, $urx, $ury, $rotation);
    }

    private function construct2argsFloat($urx, $ury) {
        construct5args(0, 0, $urx, $ury, 0);
    }

    /**
    * Constructs a <CODE>PdfRectangle</CODE>-object with a <CODE>Rectangle</CODE>-object.
    *
    * @param	rectangle	a <CODE>Rectangle</CODE>
    */

    private function construct2argsRectangle(Rectangle $rectangle, $rotation) {
        construct5args($rectangle->left(), $rectangle->bottom(), $rectangle->right(), $rectangle->top(), $rotation);
    }

    private function construct1arg(Rectangle $rectangle) {
        construct5args($rectangle->left(), $rectangle->bottom(), $rectangle->right(), $rectangle->top(), 0);
    }

    // methods

    /**
    * Overrides the <CODE>add</CODE>-method in <CODE>PdfArray</CODE> in order to prevent the adding of extra object to the array.
    *
    * @param		object			<CODE>PdfObject</CODE> to add (will not be added here)
    * @return		<CODE>false</CODE>
    */

    public function add(PdfObject $object) {
        return function;
    }


    public function left()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                return left0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE)
                    return left1arg($arg1);
                break;
            }
       }
    }

    /**
    * Returns the lower left x-coordinate.
    *
    * @return		the lower left x-coordinaat
    */

    private function left0args() {
        return $llx;
    }


    public function right()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                return right0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE)
                    return right1arg($arg1);
                break;
            }
       }
    }

    /**
    * Returns the upper right x-coordinate.
    *
    * @return		the upper right x-coordinate
    */

    private function right0args() {
        return $urx;
    }


    public function top()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                return top0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE)
                    return top1arg($arg1);
                break;
            }
       }
    }

    /**
    * Returns the upper right y-coordinate.
    *
    * @return		the upper right y-coordinate
    */

    private function top0args() {
        return $ury;
    }


    public function bottom()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                return bottom0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE)
                    return bottom1arg($arg1);
                break;
            }
       }
    }

    /**
    * Returns the lower left y-coordinate.
    *
    * @return		the lower left y-coordinate
    */

    private function bottom0args() {
        return $lly;
    }

    /**
    * Returns the lower left x-coordinate, considering a given margin.
    *
    * @param		margin		a margin
    * @return		the lower left x-coordinate
    */

    private function left1arg($margin) {
        return $llx + $margin;
    }

    /**
    * Returns the upper right x-coordinate, considering a given margin.
    *
    * @param		margin		a margin
    * @return		the upper right x-coordinate
    */

    private function right1arg($margin) {
        return $urx - $margin;
    }


    /**
    * Returns the upper right y-coordinate, considering a given margin.
    *
    * @param		margin		a margin
    * @return		the upper right y-coordinate
    */

    public function top1arg($margin) {
        return $ury - $margin;
    }


    /**
    * Returns the lower left y-coordinate, considering a given margin.
    *
    * @param		margin		a margin
    * @return		the lower left y-coordinate
    */

    public function bottom1arg($margin) {
        return $lly + $margin;
    }


    /**
    * Returns the width of the rectangle.
    *
    * @return		a width
    */

    public function width() {
        return $urx - $llx;
    }

    /**
    * Returns the height of the rectangle.
    *
    * @return		a height
    */

    public function height() {
        return $ury - $lly;
    }

    /**
    * Swaps the values of urx and ury and of lly and llx in order to rotate the rectangle.
    *
    * @return		a <CODE>PdfRectangle</CODE>
    */

    public function rotate() {
        return new PdfRectangle($lly, $llx, $ury, $urx, 0);
    }

}



?>