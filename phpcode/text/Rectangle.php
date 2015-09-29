<?PHP
/*
 * $Id: Rectangle.php,v 1.1.1.1 2005/09/22 16:08:19 mstaylor Exp $
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
require_once("../awt/Color.php");
require_once("DocumentException.php");
require_once("../util/Properties.php");
require_once("MarkupAttributes.php");
require_once("Chunk.php");

class Rectangle
{
    // static membervariables (concerning the presence of borders)
    /** This is the value that will be used as <VAR>undefined</VAR>. */
    const UNDEFINED = -1;

    /** This represents one side of the border of the <CODE>Rectangle</CODE>. */
    const TOP = 1;

    /** This represents one side of the border of the <CODE>Rectangle</CODE>. */
    const BOTTOM = 2;

    /** This represents one side of the border of the <CODE>Rectangle</CODE>. */
    const LEFT = 4;

    /** This represents one side of the border of the <CODE>Rectangle</CODE>. */
    const RIGHT = 8;

    /** This represents a rectangle without borders. */
    const NO_BORDER = 0;

    /** This represents a type of border. */
    const BOX = 13;

    // membervariables

    /** the lower left x-coordinate. */
    protected $llx;

    /** the lower left y-coordinate. */
    protected $lly;

    /** the upper right x-coordinate. */
    protected $urx;

    /** the upper right y-coordinate. */
    protected $ury;

    /** This represents the status of the 4 sides of the rectangle. */
    protected $border = UNDEFINED;

    /** This is the width of the border around this rectangle. */
    protected $borderWidth = UNDEFINED;

    /** The color of the left border of this rectangle. */
    protected $borderColorLeft = NULL;

    /** The color of the right border of this rectangle. */
    protected $borderColorRight = NULL;

    /** The color of the top border of this rectangle. */
    protected $borderColorTop = NULL;

    /** The color of the bottom border of this rectangle. */
    protected $borderColorBottom = NULL;

    /** The width of the left border of this rectangle. */
    protected $borderWidthLeft = UNDEFINED;

    /** The width of the right border of this rectangle. */
    protected $borderWidthRight = UNDEFINED;

    /** The width of the top border of this rectangle. */
    protected $borderWidthTop = UNDEFINED;

    /** The width of the bottom border of this rectangle. */
    protected $borderWidthBottom = UNDEFINED;

    /** Whether variable width borders are used. */
    protected $useVariableBorders = FALSE;

    /** This is the color of the background of this rectangle. */
    protected $background = NULL;

    /** This is the grayscale value of the background of this rectangle. */
    protected $grayFill = 0;

    protected $rotation = 0;

    /** Contains extra markupAttributes */
    protected $markupAttributes;

    // constructors

    /**
    * Constructs a <CODE>Rectangle</CODE>-object.
    *
    */

    public function __construct() {
    $num_args=func_num_args();
    switch ($num_args)
    {
        case 1:
        {
            $rect = func_get_arg(0); 
            this($rect->llx, $rect->lly, $rect->urx, $rect->ury);
            cloneNonPositionParameters($rect);
            break;
        }
        case 2://Constructs a <CODE>Rectangle</CODE>-object starting from the origin (0, 0).
        {
            $urx = func_get_arg(0); 
            $ury = func_get_arg(1);
            this(0, 0, $urx, $ury);
            break;
        }
        case 4:
        {
            $llx = func_get_arg(0); 
            $lly = func_get_arg(1);
            $urx = func_get_arg(2); 
            $ury = func_get_arg(3);
            $this->llx = $llx;
            $this->lly = $lly;
            $this->urx = $urx;
            $this->ury = $ury;
            break;
        }

    }


    }

    /**
    * Copies all of the parameters from a <CODE>Rectangle</CODE>
    * object except the position.
    *
    * @param		rect	<CODE>Rectangle</CODE> to copy from
    */

    public function cloneNonPositionParameters($rect) {
       $this->rotation = $rect->rotation;
       $this->border = $rect->border;
       $this->borderWidth = $rect->borderWidth;
       $this->color = $rect->color;
       $this->background = $rect->background;
       $this->grayFill = $rect->grayFill;
       $this->borderColorLeft = $rect->borderColorLeft;
       $this->borderColorRight = $rect->borderColorRight;
       $this->borderColorTop = $rect->borderColorTop;
       $this->borderColorBottom = $rect->borderColorBottom;
       $this->borderWidthLeft = $rect->borderWidthLeft;
       $this->borderWidthRight = $rect->borderWidthRight;
       $this->borderWidthTop = $rect->borderWidthTop;
       $this->borderWidthBottom = $rect->borderWidthBottom;
       $this->useVariableBorders = $rect->useVariableBorders;
    }


    // implementation of the Element interface

    /**
    * Processes the element by adding it (or the different parts) to an
    * <CODE>ElementListener</CODE>.
    *
    * @param	listener	an <CODE>ElementListener</CODE>
    * @return	<CODE>true</CODE> if the element was processed successfully
    */

    public function process($listener) {
        try {
            return $listener->add($this);
        }
        catch(DocumentException $de) {
            return FALSE;
        }
    }

    /**
    * Gets the type of the text element.
    *
    * @return	a type
    */

    public function type() {
        return Element::RECTANGLE;
    }


    /**
    * Gets all the chunks in this element.
    *
    * @return	an <CODE>ArrayList</CODE>
    */

    public function getChunks() {
        return Array();
    }

    // methods

    /**
    * Switches lowerleft with upperright
    */
    public function normalize() {
        if ($llx > $urx) {
            $a = $llx;
            $llx = $urx;
            $urx = $a;
        }
        if ($lly > $ury) {
            $a = $lly;
            $lly = $ury;
            $ury = $a;
        }
    }

    /**
    * Gets a Rectangle that is altered to fit on the page.
    *
    * @param	top		the top position
    * @param	bottom	the bottom position
    * @return	a <CODE>Rectangle</CODE>
    */

    public function rectangle($top, $bottom) {
        $tmp = new Rectangle($this);
        if (top() > $top) {
            $tmp->setTop($top);
            $tmp->setBorder($border - ($border & TOP));
        }
        if (bottom() < $bottom) {
            $tmp->setBottom($bottom);
            $tmp->setBorder($border - ($border & BOTTOM));
        }
        return tmp;
    }

    /**
    * Swaps the values of urx and ury and of lly and llx in order to rotate the rectangle.
    *
    * @return		a <CODE>Rectangle</CODE>
    */

    public function rotate() {
        $rect = new Rectangle($lly, $llx, $ury, $urx);
        $rect->rotation = $rotation + 90;
        $rect->rotation %= 360;
        return rect;
    }

    // methods to set the membervariables

    /**
    * Sets the lower left x-coordinate.
    *
    * @param	value	the new value
    */

    public function setLeft($value) {
        $llx = $value;
    }

    /**
    * Sets the upper right x-coordinate.
    *
    * @param	value	the new value
    */

    public function setRight($value) {
        $urx = $value;
    }

    /**
    * Sets the upper right y-coordinate.
    *
    * @param	value	the new value
    */

    public function setTop($value) {
        $ury = $value;
    }


    /**
    * Sets the lower left y-coordinate.
    *
    * @param	value	the new value
    */

    public function setBottom($value) {
        $lly = $value;
    }


    /**
    * Enables/Disables the border on the specified sides.  The border is specified
    * as an integer bitwise combination of the constants:
    * <CODE>LEFT, RIGHT, TOP, BOTTOM</CODE>.
    * @see #enableBorderSide(int)
    * @see #disableBorderSide(int)
    * @param	value	the new value
    */

    public function setBorder($value) {
        $border = $value;
    }

    /**
    * Enables the border on the specified side.
    *
    * @param	side the side to enable. One of <CODE>LEFT, RIGHT, TOP, BOTTOM</CODE>
    */
    public function enableBorderSide($side) {
        if ($border == UNDEFINED) {
            $border = 0;
        }
        $border |= $side;
    }

    /**
    * Disables the border on the specified side.
    *
    * @param	side the side to disable. One of <CODE>LEFT, RIGHT, TOP, BOTTOM</CODE>
    */
    public function disableBorderSide($side) {
        if ($border == UNDEFINED) {
            $border = 0;
        }
        $border &= ~$side;
    }

    /**
    * Sets the borderwidth of the table.
    *
    * @param	value	the new value
    */

    public function setBorderWidth($value) {
        $borderWidth = $value;
    }

    /**
    * Sets the color of the border.
    *
    * @param	value	the new value
    */

    public function setBorderColor($value) {
        $color = $value;
    }

    /**
    * Sets the value of the border color
    * @param value a color value
    */
    public function setBorderColorRight($value)
    {
        $borderColorRight = $value;
    }

    /**
    * Sets the value of the border color
    * @param value a color value
    */
    public function setBorderColorLeft($value)
    {
        $borderColorLeft = $value;
    }

   /**
   * Sets the value of the border color
   * @param value a color value
   */
   public function setBorderColorTop($value)
   {
       $borderColorTop = $value;
   }

   /**
   * Sets the value of the border color
   * @param value a color value
   */
   public function setBorderColorBottom($value)
   {
       $borderColorBottom = $value;
   }

   /**
   * Sets the backgroundcolor of the rectangle.
   *
   * @param	value	the new value
   */

    public function setBackgroundColor($value) {
        $background = $value;
    }

    /**
    * Sets the grayscale of the rectangle.
    *
    * @param	value	the new value
    */

    public function setGrayFill($value) {
        if ($value >= 0 && $value <= 1.0) {
            $grayFill = $value;
        }
    }

    // methods to get the membervariables

    /**
    * Returns the lower left x-coordinate.
    *
    * @return		the lower left x-coordinate
    */

    public function left() {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                return $llx;
                break;
            }
            case 2:
            {
                $margin = func_get_arg(0); 
                return $llx + $margin;
                break;
            }
        }

    }

    /**
    * Returns the upper right x-coordinate.
    *
    * @return		the upper right x-coordinate
    */

    public function right() {

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                return $urx;
                break;
            }
            case 2:
            {
                $margin = func_get_arg(0); 
                return $urx - $margin;
                break;
            }
        }
    }

    /**
    * Returns the upper right y-coordinate.
    *
    * @return		the upper right y-coordinate
    */

    public function top() {

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                return $ury;
                break;
            }
            case 2:
            {
                $margin = func_get_arg(0); 
                return $ury - $margin;
                break;
            }
        }
    }

    /**
    * Returns the lower left y-coordinate.
    *
    * @return		the lower left y-coordinate
    */

    public function bottom() {

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                return $lly;
                break;
            }
            case 2:
            {
                $margin = func_get_arg(0); 
                return $lly + $margin;
                break;
            }
        }
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
    * Indicates if the table has borders.
    *
    * @return	a boolean
    */

    public function hasBorders() {
        return ($border > 0) &&
              (($borderWidth > 0)      ||
               ($borderWidthLeft > 0)  ||
               ($borderWidthRight > 0) ||
               ($borderWidthTop > 0)   ||
               ($borderWidthBottom > 0));
    }

    /**
    * Indicates if the table has a some type of border.
    *
    * @param	type    the type of border
    * @return	a boolean
    */

    public function hasBorder($type) {
        return $border != UNDEFINED && ($border & $type) == $type;
    }


    /**
    * Returns the exact type of the border.
    *
    * @return	a value
    */

    public function border() {
        return $border;
    }

    /**
    * Gets the borderwidth.
    *
    * @return	a value
    */

    public function borderWidth() {
        return $borderWidth;
    }

    /**
    * Gets the color of the border.
    *
    * @return	a value
    */

    public function borderColor() {
        return $color;
    }


    /**
    * Gets the backgroundcolor.
    *
    * @return	a value
    */

    public function backgroundColor() {
        return $background;
    }

    /**
    * Gets the grayscale.
    *
    * @return	a value
    */

    public function grayFill() {
        return $grayFill;
    }

    /**
    * Gets the rotation of the rectangle
    * @return a rotation value
    */
    public function getRotation() {
        return $rotation;
    }


    /**
    * @see com.lowagie.text.MarkupAttributes#setMarkupAttribute(java.lang.String, java.lang.String)
    */
    public function setMarkupAttribute($name, $value) {
        $markupAttributes = ($markupAttributes == NULL) ? new Properties() : $markupAttributes;
        $markupAttributes->put($name, $value);
    }


    /**
    * @see com.lowagie.text.MarkupAttributes#setMarkupAttributes(java.util.Properties)
    */
    public function setMarkupAttributes($markupAttributes) {
        $this->markupAttributes = $markupAttributes;
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#getMarkupAttribute(java.lang.String)
    */
    public function getMarkupAttribute($name) {
        return ($markupAttributes == NULL) ? NULL : $markupAttributes->get($name);
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#getMarkupAttributeNames()
    */
    public function getMarkupAttributeNames() {
        return Chunk::getKeySet($markupAttributes);
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#getMarkupAttributes()
    */
    public function getMarkupAttributes() {
        return $markupAttributes;
    }

    /**
    * Gets the color of a border.
    * @return a color value
    */
    public function getBorderColorLeft()
    {
      return $borderColorLeft;
    }

    /**
    * Gets the color of a border.
    * @return a color value
    */
    public function getBorderColorRight()
    {
        return $borderColorRight;
    }

    /**
    * Gets the color of a border.
    * @return a color value
    */
    public function getBorderColorTop()
    {
        return $borderColorTop;
    }

    /**
    * Gets the color of a border.
    * @return a color value
    */
    public function getBorderColorBottom()
    {
        return $borderColorBottom;
    }

    /**
    * Gets the width of a border.
    * @return a width
    */
    public function getBorderWidthLeft()
    {
        return getVariableBorderWidth( $borderWidthLeft, LEFT );
    }

    /**
    * Sets the width of a border
    * @param borderWidthLeft a width
    */
    public function setBorderWidthLeft( $borderWidthLeft )
    {
      $this->borderWidthLeft = $borderWidthLeft;
      updateBorderBasedOnWidth($borderWidthLeft, LEFT);
    }

    /**
    * Gets the width of a border.
    * @return a width
    */
    public function getBorderWidthRight()
    {
      return getVariableBorderWidth( $borderWidthRight, RIGHT );
    }

    /**
    * Sets the width of a border
    * @param borderWidthRight a width
    */
    public function setBorderWidthRight( $borderWidthRight )
    {
        $this->borderWidthRight = $borderWidthRight;
        updateBorderBasedOnWidth($borderWidthRight, RIGHT);
    }

    /**
    * Gets the width of a border.
    * @return a width
    */
    public function getBorderWidthTop()
    {
        return getVariableBorderWidth( $borderWidthTop, TOP );
    }

    /**
    * Sets the width of a border
    * @param borderWidthTop a width
    */
    public function setBorderWidthTop( $borderWidthTop )
    {
        $this->borderWidthTop = borderWidthTop;
        updateBorderBasedOnWidth($borderWidthTop, TOP);
    }

    /**
    * Gets the width of a border.
    * @return a width
    */
    public function getBorderWidthBottom()
    {
       return getVariableBorderWidth( $borderWidthBottom, BOTTOM );
    }

    /**
    * Sets the width of a border
    * @param borderWidthBottom a width
    */
    public function setBorderWidthBottom( $borderWidthBottom )
    {
       $this->borderWidthBottom = $borderWidthBottom;
       updateBorderBasedOnWidth($borderWidthBottom, BOTTOM);
    }

    /**
    * Updates the border flag for a side based on the specified
    * width.  A width of 0 will disable the border on that side.
    * Any other width enables it.
    * @param width  width of border
    * @param side   border side constant
    */

    private function updateBorderBasedOnWidth($width, $side)
    {
       $useVariableBorders = TRUE;
       if ($width > 0)
       {
           enableBorderSide($side);
       }
       else
       {
           disableBorderSide($side);
       }
    }

    private function getVariableBorderWidth( $variableWidthValue, $side )
    {
        if (($border & $side) != 0)
        {
            return  $variableWidthValue != UNDEFINED ? $variableWidthValue : $borderWidth;
        }
        else
        {
            return 0;
        }
    }


    /**
    * Indicates whether variable width borders are being used.
    * Returns true if <CODE>setBorderWidthLeft, setBorderWidthRight,
    * setBorderWidthTop, or setBorderWidthBottom</CODE> has been called.
    *
    * @return true if variable width borders are in use
    *
    */
    public function isUseVariableBorders()
    {
        return $useVariableBorders;
    }

    /**
    * Sets a parameter indicating if the rectangle has variable borders
    * @param useVariableBorders indication if the rectangle has variable borders
    */
    public function setUseVariableBorders($useVariableBorders)
    {
      $this->useVariableBorders = $useVariableBorders;
    }

}
?>