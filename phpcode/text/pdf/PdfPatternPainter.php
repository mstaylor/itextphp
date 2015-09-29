<?PHP
/*
 * $Id: PdfPatternPainter.php,v 1.2 2005/10/19 16:03:23 mstaylor Exp $
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
require_once("../Image.php");
require_once("../Rectangle.php");
require_once("../../awt/Color.php");
require_once("PdfTemplate.php");
require_once("PdfWriter.php");
require_once("PdfContentByte.php");
require_once("PdfPattern.php");
require_once("PdfSpotColor.php");

/**
* Implements the pattern.
*/
class PdfPatternPainter extends PdfTemplate 
{

    protected $xstep = 0.0;
    protected $ystep = 0.0;
    protected $stencil = FALSE;
    protected $defaultColor = NULL;


    public function __construct()
    {
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
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                construct2args($arg1, $arg2);
                break;
            }
        }
    }

    /**
    *Creates a <CODE>PdfPattern</CODE>.
    */
    private function construct0args()
    {
        parent::__construct(NULL);
        $type = PdfTemplate::TYPE_PATTERN;
    }

    /**
    * Creates new PdfPattern
    *
    * @param wr the <CODE>PdfWriter</CODE>
    */
    private function construct1arg(PdfWriter $wr) {
        parent::__construct($wr);
        $type = PdfTemplate::TYPE_PATTERN;
    }

    private function construct2args(PdfWriter $wr, Color $defaultColor) {
        construct1arg($wr);
        $stencil = TRUE;
        if ($defaultColor == NULL)
            $this->defaultColor = Color::$gray;
        else
            $this->defaultColor = $defaultColor;
    }

    /**
    * Sets the horizontal interval of this pattern.
    *
    * @param xstep the xstep in horizontal painting
    */

    public function setXStep($xstep) {
        $this->xstep = $xstep;
    }

    /**
    * Sets the vertical interval of this pattern.
    *
    * @param ystep in vertical painting
    */

    public function setYStep($ystep) {
        $this->ystep = $ystep;
    }

    /**
    * Returns the horizontal interval when repeating the pattern.
    * @return a value
    */
    public function getXStep() {
        return $this->xstep;
    }

    /**
    * Returns the vertical interval when repeating the pattern.
    * @return a value
    */
    public function getYStep() {
        return $this->ystep;
    }


    /**
    * Tells you if this pattern is colored/uncolored (stencil = uncolored, you need to set a default color).
    * @return true if the pattern is an uncolored tiling pattern (stencil).
    */
    public function isStencil() {
        return $stencil;
    }

    /**
    * Sets the transformation matrix for the pattern.
    * @param a
    * @param b
    * @param c
    * @param d
    * @param e
    * @param f
    */
    public function setPatternMatrix($a, $b, $c, $d, $e, $f) {
        setMatrix($a, $b, $c, $d, $e, $f);
    }

    /**
    * Gets the stream representing this pattern
    *
    * @return the stream representing this pattern
    */

    function getPattern() {
        return new PdfPattern($this);
    }

    /**
    * Gets a duplicate of this <CODE>PdfPatternPainter</CODE>. All
    * the members are copied by reference but the buffer stays different.
    * @return a copy of this <CODE>PdfPatternPainter</CODE>
    */

    public function getDuplicate() {
        $tpl = new PdfPatternPainter();
        $tpl->writer = $writer;
        $tpl->pdf = $pdf;
        $tpl->thisReference = $thisReference;
        $tpl->pageResources = $pageResources;
        $tpl->bBox = new Rectangle($bBox);
        $tpl->xstep = $xstep;
        $tpl->ystep = $ystep;
        $tpl->matrix = $matrix;
        $tpl->stencil = $stencil;
        $tpl->defaultColor = $defaultColor;
        return $tpl;
    }

    /**
    * Returns the default color of the pattern.
    * @return a Color
    */
    public function getDefaultColor() {
        return $defaultColor;
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setGrayFill(float)
    */
    public function setGrayFill($gray) {
        checkNoColor();
        parent::setGrayFill($gray);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#resetGrayFill()
    */
    public function resetGrayFill() {
        checkNoColor();
        parent::resetGrayFill();
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setGrayStroke(float)
    */
    public function setGrayStroke($gray) {
        checkNoColor();
        parent::setGrayStroke($gray);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#resetGrayStroke()
    */
    public function resetGrayStroke() {
        checkNoColor();
        parent::resetGrayStroke();
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setRGBColorFillF(float, float, float)
    */
    public function setRGBColorFillF($red, $green, $blue) {
        checkNoColor();
        parent::setRGBColorFillF($red, $green, $blue);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#resetRGBColorFill()
    */
    public function resetRGBColorFill() {
        checkNoColor();
        parent::resetRGBColorFill();
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setRGBColorStrokeF(float, float, float)
    */
    public function setRGBColorStrokeF($red, $green, $blue) {
        checkNoColor();
        parent::setRGBColorStrokeF($red, $green, $blue);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#resetRGBColorStroke()
    */
    public function resetRGBColorStroke() {
        checkNoColor();
        parent::resetRGBColorStroke();
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setCMYKColorFillF(float, float, float, float)
    */
    public function setCMYKColorFillF($cyan, $magenta, $yellow, $black) {
        checkNoColor();
        parent::setCMYKColorFillF($cyan, $magenta, $yellow, $black);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#resetCMYKColorFill()
    */
    public function resetCMYKColorFill() {
        checkNoColor();
        parent::resetCMYKColorFill();
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setCMYKColorStrokeF(float, float, float, float)
    */
    public function setCMYKColorStrokeF($cyan, $magenta, $yellow, $black) {
        checkNoColor();
        parent::setCMYKColorStrokeF($cyan, $magenta, $yellow, $black);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#resetCMYKColorStroke()
    */
    public function resetCMYKColorStroke() {
        checkNoColor();
        parent::resetCMYKColorStroke();
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#addImage(com.lowagie.text.Image, float, float, float, float, float, float)
    */
    public function addImage(Image $image, $a, $b, $c, $d, $e, $f) {
        if ($stencil == TRUE && $image->isMask() == FALSE)
            checkNoColor();
        parent::addImage($image, $a, $b, $c, $d, $e, $f);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setCMYKColorFill(int, int, int, int)
    */
    public function setCMYKColorFill($cyan, $magenta, $yellow, $black) {
        checkNoColor();
        parent::setCMYKColorFill($cyan, $magenta, $yellow, $black);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setCMYKColorStroke(int, int, int, int)
    */
    public function setCMYKColorStroke($cyan, $magenta, $yellow, $black) {
        checkNoColor();
        parent::setCMYKColorStroke($cyan, $magenta, $yellow, $black);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setRGBColorFill(int, int, int)
    */
    public function setRGBColorFill($red, $green, $blue) {
        checkNoColor();
        parent::setRGBColorFill($red, $green, $blue);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setRGBColorStroke(int, int, int)
    */
    public function setRGBColorStroke($red, $green, $blue) {
        checkNoColor();
        parent::setRGBColorStroke($red, $green, $blue);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setColorStroke(java.awt.Color)
    */
    public function setColorStroke(Color $color) {
        checkNoColor();
        parent::setColorStroke($color);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setColorFill(java.awt.Color)
    */
    public function setColorFill(Color $color) {
        checkNoColor();
        parent::setColorFill(color);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setColorFill(com.lowagie.text.pdf.PdfSpotColor, float)
    */
    public function setColorFill(PdfSpotColor $sp, $tint) {
        checkNoColor();
        parent::setColorFill($sp, $tint);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setColorStroke(com.lowagie.text.pdf.PdfSpotColor, float)
    */
    public function setColorStroke(PdfSpotColor $sp, $tint) {
        checkNoColor();
        parent::setColorStroke($sp, $tint);
    }

    public function setPatternFill()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                setPatternFill1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                setPatternFill3args($arg1, $arg2, $arg3);
                break;
            }
        }

    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setPatternFill(com.lowagie.text.pdf.PdfPatternPainter)
    */
    private function setPatternFill1arg(PdfPatternPainter $p) {
        checkNoColor();
        parent::setPatternFill($p);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setPatternFill(com.lowagie.text.pdf.PdfPatternPainter, java.awt.Color, float)
    */
    private function setPatternFill3args(PdfPatternPainter p, Color color, float tint) {
        checkNoColor();
        parent::setPatternFill($p, $color, $tint);
    }

    public function setPatternStroke
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                setPatternStroke1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                setPatternStroke3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }


    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setPatternStroke(com.lowagie.text.pdf.PdfPatternPainter, java.awt.Color, float)
    */
    private function setPatternStroke3args(PdfPatternPainter $p, Color $color, $tint) {
        checkNoColor();
        parent::setPatternStroke($p, $color, $tint);
    }

    /**
    * @see com.lowagie.text.pdf.PdfContentByte#setPatternStroke(com.lowagie.text.pdf.PdfPatternPainter)
    */
    private function setPatternStroke1arg(PdfPatternPainter $p) {
        checkNoColor();
        parent::setPatternStroke($p);
    }

    function checkNoColor() {
        if ($stencil == TRUE)
            throw new Exception("Colors are not allowed in uncolored tile patterns.");
    }
}


?>