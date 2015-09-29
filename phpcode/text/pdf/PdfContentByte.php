<?PHP
/*
 * $Id: PdfContentByte.php,v 1.3 2005/10/25 16:01:57 mstaylor Exp $
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
require_once("../../awt/print/PrinterJob.php");
require_once("../../awt/geom/AffineTransform.php");
require_once("../DocumentException.php");
require_once("../../exceptions/NullPointerException.php");
require_once("../../exceptions/IllegalArgumentException.php");
require_once("../Element.php");
require_once("../Image.php");
require_once("../Rectangle.php");
require_once("FontDetails.php");
require_once("ColorDetails.php");
require_once("ByteBuffer.php");
require_once("PdfWriter.php");
require_once("PdfDocument.php");
require_once("PdfTemplate.php");
require_once("PdfName.php");
require_once("PageResources.php");
require_once("BaseFont.php");
require_once("PdfTextArray.php");
require_once("PdfOutline.php");
require_once("PdfPatternPainter.php");
require_once("PdfAppearance.php");
require_once("PdfPSXObject.php");
require_once("ExtendedColor.php");
require_once("GrayColor.php");
require_once("CMYKColor.php");
require_once("SpotColor.php");
require_once("PatternColor.php");
require_once("ShadingColor.php");
require_once("PdfShading.php");
require_once("PdfShadingPattern.php");
require_once("PdfDestination.php");
require_once("PdfAction.php");
//require_once("PdfGraphics2D.php");
require_once("PdfPrinterGraphics2D.php");
require_once("FontMapper.php");
require_once("PdfGState.php");
require_once("PdfObject.php");
require_once("PdfOCG.php");
require_once("PdfLayer.php");
require_once("PdfLayerMembership.php");
require_once("PdfAnnotation.php");

class PdfContentByte 
{

    /** The alignement is center */
    const ALIGN_CENTER = Element::ALIGN_CENTER;

    /** The alignement is left */
    const ALIGN_LEFT = Element::ALIGN_LEFT;

    /** The alignement is right */
    const ALIGN_RIGHT = Element::ALIGN_RIGHT;

    /** A possible line cap value */
    const LINE_CAP_BUTT = 0;
    /** A possible line cap value */
    const LINE_CAP_ROUND = 1;
    /** A possible line cap value */
    const LINE_CAP_PROJECTING_SQUARE = 2;

    /** A possible line join value */
    const LINE_JOIN_MITER = 0;
    /** A possible line join value */
    const LINE_JOIN_ROUND = 1;
    /** A possible line join value */
    const LINE_JOIN_BEVEL = 2;

    /** A possible text rendering value */
    const TEXT_RENDER_MODE_FILL = 0;
    /** A possible text rendering value */
    const TEXT_RENDER_MODE_STROKE = 1;
    /** A possible text rendering value */
    const TEXT_RENDER_MODE_FILL_STROKE = 2;
    /** A possible text rendering value */
    const TEXT_RENDER_MODE_INVISIBLE = 3;
    /** A possible text rendering value */
    const TEXT_RENDER_MODE_FILL_CLIP = 4;
    /** A possible text rendering value */
    const TEXT_RENDER_MODE_STROKE_CLIP = 5;
    /** A possible text rendering value */
    const TEXT_RENDER_MODE_FILL_STROKE_CLIP = 6;
    /** A possible text rendering value */
    const TEXT_RENDER_MODE_CLIP = 7;

    // membervariables

    /** This is the actual content */
    protected $content;

    /** This is the writer */
    protected $writer;

    /** This is the PdfDocument */
    protected $pdf;

    /** This is the GraphicState in use */
    protected $state = NULL;

    /** The list were we save/restore the state */
    protected $stateList = array();

    /** The list were we save/restore the layer depth */
    protected $layerDepth;

    /** The separator between commands.
     */
    protected $separator;


    private function initializationonConstruct()
    {
        $content = new ByteBuffer();
        $separator = ord('\n');
        $state = new GraphicState();
    }

    public function __construct()
    {
        initializeonConstruct();

        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0); 
               construct1arg($arg);
               break;
           }
        }
    }

    private function construct1arg(PdfWriter $wr)
    {
         if ($wr != NULL) {
            $writer = $wr;
            $pdf = $writer->getPdfDocument();
        }
    }

     // methods to get the content of this object

    /**
    * Returns the <CODE>String</CODE> representation of this <CODE>PdfContentByte</CODE>-object.
    *
    * @return		a <CODE>String</CODE>
    */

    public function toString() {
        return $content->toString();
    }

    /**
    * Gets the internal buffer.
    * @return the internal buffer
    */
    public function getInternalBuffer() {
        return $content;
    }

    /** Returns the PDF representation of this <CODE>PdfContentByte</CODE>-object.
    *
    * @param writer the <CODE>PdfWriter</CODE>
    * @return a <CODE>byte</CODE> array with the representation
    */

    public function toPdf(PdfWriter $writer) {
        return $content->toByteArray();
    }

    // methods to add graphical content

    /**
    * Adds the content of another <CODE>PdfContent</CODE>-object to this object.
    *
    * @param		other		another <CODE>PdfByteContent</CODE>-object
    */

    public function add(PdfContentByte $other) {
        if ($other->writer != NULL && $writer != $other->writer)
            throw new Exception("Inconsistent writers. Are you mixing two documents?");
        $content->append($other->content);
    }

    /**
    * Gets the x position of the text line matrix.
    *
    * @return the x position of the text line matrix
    */
    public function getXTLM() {
        return $state->xTLM;
    }

    /**
    * Gets the y position of the text line matrix.
    *
    * @return the y position of the text line matrix
    */
    public function getYTLM() {
        return $state->yTLM;
    }

    /**
    * Gets the current text leading.
    *
    * @return the current text leading
    */
    public function getLeading() {
        return $state->leading;
    }

    /**
    * Changes the <VAR>Flatness</VAR>.
    * <P>
    * <VAR>Flatness</VAR> sets the maximum permitted distance in device pixels between the
    * mathematically correct path and an approximation constructed from straight line segments.<BR>
    *
    * @param		flatness		a value
    */

    public function setFlatness($flatness) {
        if ($flatness >= 0 && $flatness <= 100) {
            $content->append($flatness)->append(" i")->append_i($separator);
        }
    }

    /**
    * Changes the <VAR>Line cap style</VAR>.
    * <P>
    * The <VAR>line cap style</VAR> specifies the shape to be used at the end of open subpaths
    * when they are stroked.<BR>
    * Allowed values are LINE_CAP_BUTT, LINE_CAP_ROUND and LINE_CAP_PROJECTING_SQUARE.<BR>
    *
    * @param		style		a value
    */

    public function setLineCap($style) {
        if ($style >= 0 && $style <= 2) {
            $content->append($style)->append(" J")->append_i($separator);
        }
    }


    public function setLineDash()
    {


        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0); 
               setLineDash1arg($arg1);
               break;
           }
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               if (strcmp(gettype($arg1), "array") != 0)
               setLineDash2args($arg1. $arg2);
               else
               setLineDash2argsarray($arg1, $arg2);
               break;
           }
           case 3:
           {
              $arg1 = func_get_arg(0);
              $arg2 = func_get_arg(1);
              $arg3 = func_get_arg(2);
              setLineDash3args($arg1. $arg2);
              break;
           }
        }
    }

    /**
    * Changes the value of the <VAR>line dash pattern</VAR>.
    * <P>
    * The line dash pattern controls the pattern of dashes and gaps used to stroke paths.
    * It is specified by an <I>array</I> and a <I>phase</I>. The array specifies the length
    * of the alternating dashes and gaps. The phase specifies the distance into the dash
    * pattern to start the dash.<BR>
    *
    * @param		phase		the value of the phase
    */

    private function setLineDash1arg($phase) {
        $content->append("[] ")->append($phase)->append(" d")->append_i($separator);
    }

    /**
    * Changes the value of the <VAR>line dash pattern</VAR>.
    * <P>
    * The line dash pattern controls the pattern of dashes and gaps used to stroke paths.
    * It is specified by an <I>array</I> and a <I>phase</I>. The array specifies the length
    * of the alternating dashes and gaps. The phase specifies the distance into the dash
    * pattern to start the dash.<BR>
    *
    * @param		phase		the value of the phase
    * @param		unitsOn		the number of units that must be 'on' (equals the number of units that must be 'off').
    */

    private function setLineDash2args($unitsOn, $phase) {
        $content->append("[")->append($unitsOn)->append("] ")->append($phase)->append(" d")->append_i($separator);
    }

    /**
    * Changes the value of the <VAR>line dash pattern</VAR>.
    * <P>
    * The line dash pattern controls the pattern of dashes and gaps used to stroke paths.
    * It is specified by an <I>array</I> and a <I>phase</I>. The array specifies the length
    * of the alternating dashes and gaps. The phase specifies the distance into the dash
    * pattern to start the dash.<BR>
    *
    * @param		array		length of the alternating dashes and gaps
    * @param		phase		the value of the phase
    */

    private final function setLineDash2argsarray(array $array, $phase) {
        $content->append("[");
        for ($i = 0; i < count($array); $i++) {
            $content->append($array[$i]);
            if ($i < count($array) - 1) $content->append(' ');
        }
        $content->append("] ")->append($phase)->append(" d")->append_i($separator);
    }

    /**
    * Changes the value of the <VAR>line dash pattern</VAR>.
    * <P>
    * The line dash pattern controls the pattern of dashes and gaps used to stroke paths.
    * It is specified by an <I>array</I> and a <I>phase</I>. The array specifies the length
    * of the alternating dashes and gaps. The phase specifies the distance into the dash
    * pattern to start the dash.<BR>
    *
    * @param		phase		the value of the phase
    * @param		unitsOn		the number of units that must be 'on'
    * @param		unitsOff	the number of units that must be 'off'
    */

    private function setLineDash3args($unitsOn, $unitsOff, $phase) {
        $content->append("[")->append($unitsOn)->append(' ')->append($unitsOff)->append("] ")->append($phase)->append(" d")->append_i($separator);
    }


    /**
    * Changes the <VAR>Line join style</VAR>.
    * <P>
    * The <VAR>line join style</VAR> specifies the shape to be used at the corners of paths
    * that are stroked.<BR>
    * Allowed values are LINE_JOIN_MITER (Miter joins), LINE_JOIN_ROUND (Round joins) and LINE_JOIN_BEVEL (Bevel joins).<BR>
    *
    * @param		style		a value
    */

    public function setLineJoin($style) {
        if ($style >= 0 && $style <= 2) {
            $content->append($style)->append(" j")->append_i($separator);
        }
    }

    /**
    * Changes the <VAR>line width</VAR>.
    * <P>
    * The line width specifies the thickness of the line used to stroke a path and is measured
    * in used space units.<BR>
    *
    * @param		w			a width
    */
    
    public function setLineWidth($w) {
        $content->append($w)->append(" w")->append_i($separator);
    }
    
    /**
    * Changes the <VAR>Miter limit</VAR>.
    * <P>
    * When two line segments meet at a sharp angle and mitered joins have been specified as the
    * line join style, it is possible for the miter to extend far beyond the thickness of the line
    * stroking path. The miter limit imposes a maximum on the ratio of the miter length to the line
    * witdh. When the limit is exceeded, the join is converted from a miter to a bevel.<BR>
    *
    * @param		miterLimit		a miter limit
    */

    public function setMiterLimit($miterLimit) {
        if ($miterLimit > 1) {
            $content->append($miterLimit)->append(" M")->append_i($separator);
        }
    }

    /**
    * Modify the current clipping path by intersecting it with the current path, using the
    * nonzero winding number rule to determine which regions lie inside the clipping
    * path.
    */

    public function clip() {
        $content->append("W")->append_i($separator);
    }

    /**
    * Modify the current clipping path by intersecting it with the current path, using the
    * even-odd rule to determine which regions lie inside the clipping path.
    */

    public function eoClip() {
        $content->append("W*")->append_i($separator);
    }

    /**
    * Changes the currentgray tint for filling paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceGray</B> (or the <B>DefaultGray</B> color space),
    * and sets the gray tint to use for filling paths.</P>
    *
    * @param	gray	a value between 0 (black) and 1 (white)
    */
    
    public function setGrayFill($gray) {
        content->append($gray)->append(" g")->append_i($separator);
    }
    
    /**
    * Changes the current gray tint for filling paths to black.
    */
    
    public function resetGrayFill() {
        content->append("0 g")->append_i($separator);
    }

    /**
    * Changes the currentgray tint for stroking paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceGray</B> (or the <B>DefaultGray</B> color space),
    * and sets the gray tint to use for stroking paths.</P>
    *
    * @param	gray	a value between 0 (black) and 1 (white)
    */

    public function setGrayStroke($gray) {
        $content->append($gray)->append(" G")->append_i($separator);
    }
    
    /**
    * Changes the current gray tint for stroking paths to black.
    */
    
    public function resetGrayStroke() {
        $content->append("0 G")->append_i($separator);
    }

    /**
    * Helper to validate and write the RGB color components
    * @param	red		the intensity of red. A value between 0 and 1
    * @param	green	the intensity of green. A value between 0 and 1
    * @param	blue	the intensity of blue. A value between 0 and 1
    */
    private function HelperRGB($red, $green, $blue) {
        PdfWriter::checkPDFXConformance($writer, PdfWriter::PDFXKEY_RGB, NULL);
        if ($red < 0)
            $red = 0.0;
        else if ($red > 1.0)
            $red = 1.0;
        if ($green < 0)
            $green = 0.0;
        else if ($green > 1.0)
            $green = 1.0;
        if ($blue < 0)
            $blue = 0.0;
        else if ($blue > 1.0)
            $blue = 1.0;
        $content->append($red)->append(' ')->append($green)->append(' ')->append($blue);
    }

    /**
    * Changes the current color for filling paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceRGB</B> (or the <B>DefaultRGB</B> color space),
    * and sets the color to use for filling paths.</P>
    * <P>
    * Following the PDF manual, each operand must be a number between 0 (minimum intensity) and
    * 1 (maximum intensity).</P>
    *
    * @param	red		the intensity of red. A value between 0 and 1
    * @param	green	the intensity of green. A value between 0 and 1
    * @param	blue	the intensity of blue. A value between 0 and 1
    */

    public function setRGBColorFillF($red, $green, $blue) {
        HelperRGB($red, $green, $blue);
        $content->append(" rg")->append_i($separator);
    }
    
     /**
     * Changes the current color for filling paths to black.
     */
    
    public void resetRGBColorFill() {
        content.append("0 g").append_i(separator);
    }
    
    /**
    * Changes the current color for stroking paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceRGB</B> (or the <B>DefaultRGB</B> color space),
    * and sets the color to use for stroking paths.</P>
    * <P>
    * Following the PDF manual, each operand must be a number between 0 (miniumum intensity) and
    * 1 (maximum intensity).
    *
    * @param	red		the intensity of red. A value between 0 and 1
    * @param	green	the intensity of green. A value between 0 and 1
    * @param	blue	the intensity of blue. A value between 0 and 1
    */

    public function setRGBColorStrokeF($red, $green, $blue) {
        HelperRGB($red, $green, $blue);
        $content->append(" RG")->append_i($separator);
    }

    /**
    * Changes the current color for stroking paths to black.
    *
    */

    public function resetRGBColorStroke() {
        $content->append("0 G")->append_i($separator);
    }

    /**
    * Helper to validate and write the CMYK color components.
    *
    * @param	cyan	the intensity of cyan. A value between 0 and 1
    * @param	magenta	the intensity of magenta. A value between 0 and 1
    * @param	yellow	the intensity of yellow. A value between 0 and 1
    * @param	black	the intensity of black. A value between 0 and 1
    */
    private function HelperCMYK($cyan, $magenta, $yellow, $black) {
        if ($cyan < 0)
            $cyan = 0.0;
        else if ($cyan > 1.0)
            $cyan = 1.0;
        if ($magenta < 0)
            $magenta = 0.0;
        else if ($magenta > 1.0)
            $magenta = 1.0;
        if ($yellow < 0)
            $yellow = 0.0$;
        else if ($yellow > 1.0)
            $yellow = 1.0;
        if ($black < 0)
            $black = 0.0;
        else if ($black > 1.0)
            $black = 1.0;
        $content->append($cyan)->append(' ')->append($magenta)->append(' ')->append($yellow)->append(' ')->append($black);
    }

    /**
    * Changes the current color for filling paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceCMYK</B> (or the <B>DefaultCMYK</B> color space),
    * and sets the color to use for filling paths.</P>
    * <P>
    * Following the PDF manual, each operand must be a number between 0 (no ink) and
    * 1 (maximum ink).</P>
    *
    * @param	cyan	the intensity of cyan. A value between 0 and 1
    * @param	magenta	the intensity of magenta. A value between 0 and 1
    * @param	yellow	the intensity of yellow. A value between 0 and 1
    * @param	black	the intensity of black. A value between 0 and 1
    */

    public function setCMYKColorFillF($cyan, $magenta, $yellow, $black) {
        HelperCMYK($cyan, $magenta, $yellow, $black);
        $content->append(" k")->append_i($separator);
    }

    /**
    * Changes the current color for filling paths to black.
    *
    */

    public function resetCMYKColorFill() {
        $content->append("0 0 0 1 k")->append_i($separator);
    }

    /**
    * Changes the current color for stroking paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceCMYK</B> (or the <B>DefaultCMYK</B> color space),
    * and sets the color to use for stroking paths.</P>
    * <P>
    * Following the PDF manual, each operand must be a number between 0 (miniumum intensity) and
    * 1 (maximum intensity).
    *
    * @param	cyan	the intensity of cyan. A value between 0 and 1
    * @param	magenta	the intensity of magenta. A value between 0 and 1
    * @param	yellow	the intensity of yellow. A value between 0 and 1
    * @param	black	the intensity of black. A value between 0 and 1
    */

    public function setCMYKColorStrokeF($cyan, $magenta, $yellow, $black) {
        HelperCMYK($cyan, $magenta, $yellow, $black);
        $content->append(" K")->append_i($separator);
    }

    /**
    * Changes the current color for stroking paths to black.
    *
    */

    public function resetCMYKColorStroke() {
        $content->append("0 0 0 1 K")->append_i($separator);
    }

    /**
    * Move the current point <I>(x, y)</I>, omitting any connecting line segment.
    *
    * @param		x				new x-coordinate
    * @param		y				new y-coordinate
    */

    public function moveTo($x, $y) {
        $content->append($x)->append(' ')->append($y)->append(" m")->append_i($separator);
    }


    /**
    * Appends a straight line segment from the current point <I>(x, y)</I>. The new current
    * point is <I>(x, y)</I>.
    *
    * @param		x				new x-coordinate
    * @param		y				new y-coordinate
    */

    public function lineTo($x, $y) {
        $content->append($x)->append(' ')->append($y)->append(" l")->append_i($separator);
    }

    public function curveTo()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 4:
           {
               $arg1 = func_get_arg(0); 
               $arg2 = func_get_arg(1); 
               $arg3 = func_get_arg(2); 
               $arg4 = func_get_arg(3); 
               curveTo4args($arg1, $arg2, $arg3, $arg4);
               break;
           }
           case 6:
           {
               $arg1 = func_get_arg(0); 
               $arg2 = func_get_arg(1); 
               $arg3 = func_get_arg(2); 
               $arg4 = func_get_arg(3); 
               $arg5 = func_get_arg(4);  
               $arg6 = func_get_arg(5); 
               curveTo6args($arg1, $arg2, $arg3, $arg4. $arg5, $arg6)
               break;
           }
        }
    }

    /**
    * Appends a Bêzier curve to the path, starting from the current point.
    *
    * @param		x1		x-coordinate of the first control point
    * @param		y1		y-coordinate of the first control point
    * @param		x2		x-coordinate of the second control point
    * @param		y2		y-coordinate of the second control point
    * @param		x3		x-coordinaat of the ending point (= new current point)
    * @param		y3		y-coordinaat of the ending point (= new current point)
    */

    private function curveTo6args($x1, $y1, $x2, $y2, $x3, $y3) {
        $content->append($x1)->append(' ')->append($y1)->append(' ')->append($x2)->append(' ')->append($y2)->append(' ')->append($x3)->append(' ')->append($y3)->append(" c")->append_i($separator);
    }

    /**
    * Appends a Bêzier curve to the path, starting from the current point.
    *
    * @param		x2		x-coordinate of the second control point
    * @param		y2		y-coordinate of the second control point
    * @param		x3		x-coordinaat of the ending point (= new current point)
    * @param		y3		y-coordinaat of the ending point (= new current point)
    */

    private function curveTo4args($x2, $y2, $x3, $y3) {
        $content->append($x2)->append(' ')->append($y2)->append(' ')->append($x3)->append(' ')->append($y3)->append(" v")->append_i($separator);
    }

    /**
    * Appends a Bêzier curve to the path, starting from the current point.
    *
    * @param		x1		x-coordinate of the first control point
    * @param		y1		y-coordinate of the first control point
    * @param		x3		x-coordinaat of the ending point (= new current point)
    * @param		y3		y-coordinaat of the ending point (= new current point)
    */

    public function curveFromTo($x1, $y1, $x3, $y3) {
        $content->append($x1)->append(' ')->append($y1)->append(' ')->append($x3)->append(' ')->append($y3)->append(" y")->append_i($separator);
    }

    /** Draws a circle. The endpoint will (x+r, y).
    *
    * @param x x center of circle
    * @param y y center of circle
    * @param r radius of circle
    */
    public function circle($x, $y, $r) {
        $b = 0.5523;
        moveTo($x + $r, $y);
        curveTo($x + $r, $y + $r * $b, $x + $r * $b, $y + $r, $x, $y + $r);
        curveTo($x - $r * $b, $y + $r, $x - $r, $y + $r * $b, $x - $r, $y);
        curveTo($x - $r, $y - $r * $b, $x - $r * $b, $y - $r, $x, $y - $r);
        curveTo($x + $r * $b, $y - $r, $x + $r, $y - $r * $b, $x + $r, $y);
    }

    public function rectangle()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0); 
               rectangle1arg($arg1);
               break;
           }
           case 4:
           {
               $arg1 = func_get_arg(0); 
               $arg2 = func_get_arg(1); 
               $arg3 = func_get_arg(2); 
               $arg4 = func_get_arg(3); 
               rectangle4args($arg1, $arg2, $arg3, $arg4);
               break;
           }
        }
    }

    /**
    * Adds a rectangle to the current path.
    *
    * @param		x		x-coordinate of the starting point
    * @param		y		y-coordinate of the starting point
    * @param		w		width
    * @param		h		height
    */

    private function rectangle4args($x, $y, $w, $h) {
        $content->append($x)->append(' ')->append($y)->append(' ')->append($w)->append(' ')->append($h)->append(" re")->append_i($separator);
    }

    // Contribution by Barry Richards and Prabhakar Chaganti
    /**
    * Adds a variable width border to the current path.
    * Only use if {@link com.lowagie.text.Rectangle#isUseVariableBorders() Rectangle.isUseVariableBorders}
    * = true.
    * @param		rect		a <CODE>Rectangle</CODE>
    */
    public void variableRectangle(Rectangle $rect) {
        $limit = 0;
        $startX = $rect->left();
        $startY = $rect->bottom();

        // start at the origin
        // draw bottom
        if ($rect->getBorderWidthBottom() > $limit) {
            moveTo($startX, $startY);
            if ($rect->getBorderColorBottom() == NULL)
                resetRGBColorFill();
            else
                setColorFill($rect->getBorderColorBottom());
            // DRAW BOTTOM EDGE.
            lineTo($startX + $rect->width(), $startY);
            // DRAW RIGHT EDGE.
            lineTo(($startX + $rect->width()) - $rect->getBorderWidthRight(), $startY + $rect->getBorderWidthBottom());
            //DRAW TOP EDGE.
            lineTo(($startX + $rect->getBorderWidthLeft()), $startY + $rect->getBorderWidthBottom());
            lineTo($startX, $startY);
            fill();
        }

        // Draw left
        if ($rect->getBorderWidthLeft() > $limit) {
            moveTo($startX, $startY);
            if ($rect->getBorderColorLeft() == NULL)
                resetRGBColorFill();
            else
                setColorFill($rect->getBorderColorLeft());
            // DRAW BOTTOM EDGE.
            lineTo($startX, $startY + $rect->height());
            // DRAW RIGHT EDGE.
            lineTo($startX + $rect->getBorderWidthLeft(), ($startY + $rect->height()) - $rect->getBorderWidthTop());
            //DRAW TOP EDGE.
            lineTo($startX + $rect->getBorderWidthLeft(), $startY + $rect->getBorderWidthBottom());

            lineTo($startX, $startY);
            fill();
        }


        $startX = $startX + $rect->width();
        $startY = $startY + $rect->height();

        // Draw top
        if ($rect->getBorderWidthTop() > $limit) {
            moveTo($startX, $startY);
            if ($rect->getBorderColorTop() == NULL)
                resetRGBColorFill();
            else
                setColorFill($rect->getBorderColorTop());
            // DRAW LONG EDGE.
            lineTo($startX - $rect->width(), $startY);
            // DRAW LEFT EDGE.
            lineTo($startX - $rect->width() + $rect->getBorderWidthLeft(), $startY - $rect->getBorderWidthTop());
            //DRAW SHORT EDGE.
            lineTo($startX - $rect->getBorderWidthRight(), $startY - $rect->getBorderWidthTop());

            lineTo($startX, $startY);
            fill();
        }

        // Draw Right
        if ($rect->getBorderWidthRight() > $limit) {
            moveTo($startX, $startY);
            if (rect.getBorderColorRight() == NULL)
                resetRGBColorFill();
            else
                setColorFill($rect->getBorderColorRight());
            // DRAW LONG EDGE.
            lineTo($startX, $startY - $rect->height());
            // DRAW LEFT EDGE.
            lineTo($startX - $rect->getBorderWidthRight(), $startY - $rect->height() + $rect->getBorderWidthBottom());
            //DRAW SHORT EDGE.
            lineTo($startX - $rect->getBorderWidthRight(), $startY - $rect->getBorderWidthTop());

            lineTo($startX, $startY);
            fill();
        }
        resetRGBColorFill();
    }
    
    /**
    * Adds a border (complete or partially) to the current path..
    *
    * @param		rectangle		a <CODE>Rectangle</CODE>
    */
    private function rectangle1arg(Rectangle $rectangle) {
        // the coordinates of the border are retrieved
        $x1 = $rectangle->left();
        $y1 = $rectangle->bottom();
        $x2 = $rectangle->right();
        $y2 = $rectangle->top();

        // the backgroundcolor is set
        $background = $rectangle->backgroundColor();
        if ($background != NULL) {
            setColorFill($background);
            rectangle($x1, $y1, $x2 - $x1, $y2 - $y1);
            fill();
            resetRGBColorFill();
        }
        else if ($rectangle->grayFill() > 0.0) {
            setGrayFill($rectangle->grayFill());
            rectangle($x1, $y1, $x2 - $x1, $y2 - $y1);
            fill();
            resetGrayFill();
        }


        // if the element hasn't got any borders, nothing is added
        if ($rectangle->hasBorders() == FALSE) {
            return;
        }

        // if any of the individual border colors are set
        // we draw the borders all around using the
        // different colors
        if ($rectangle->isUseVariableBorders() == TRUE) {
            variableRectangle($rectangle);
        }
        else {
            // the width is set to the width of the element
            if ($rectangle->borderWidth() != Rectangle::UNDEFINED) {
                setLineWidth($rectangle->borderWidth());
            }

            // the color is set to the color of the element
            $color = $rectangle->borderColor();
            if ($color != NULL) {
                setColorStroke($color);
            }

            // if the box is a rectangle, it is added as a rectangle
            if ($rectangle->hasBorder(Rectangle::BOX) == TRUE) {
               rectangle($x1, $y1, $x2 - $x1, $y2 - $y1);
            }
            // if the border isn't a rectangle, the different sides are added apart
            else {
                if ($rectangle->hasBorder(Rectangle::RIGHT) == TRUE) {
                    moveTo($x2, $y1);
                    lineTo($x2, $y2);
                }
                if ($rectangle->hasBorder(Rectangle::LEFT) == TRUE) {
                    moveTo($x1, $y1);
                    lineTo($x1, $y2);
                }
                if ($rectangle->hasBorder(Rectangle::BOTTOM) == TRUE) {
                    moveTo($x1, $y1);
                    lineTo($x2, $y1);
                }
                if ($rectangle->hasBorder(Rectangle::TOP) == TRUE) {
                    moveTo($x1, $y2);
                    lineTo($x2, $y2);
                }
            }

            stroke();

            if ($color != NULL) {
                resetRGBColorStroke();
            }
        }
    }


    /**
    * Closes the current subpath by appending a straight line segment from the current point
    * to the starting point of the subpath.
    */

    public function closePath() {
        $content->append("h")->append_i($separator);
    }

    /**
    * Ends the path without filling or stroking it.
    */

    public function newPath() {
        $content->append("n")->append_i($separator);
    }

    /**
    * Strokes the path.
    */

    public function stroke() {
        $content->append("S")->append_i($separator);
    }

    /**
    * Closes the path and strokes it.
    */

    public function closePathStroke() {
        $content->append("s")->append_i($separator);
    }

    /**
    * Fills the path, using the non-zero winding number rule to determine the region to fill.
    */

    public function fill() {
        $content->append("f")->append_i($separator);
    }

    /**
    * Fills the path, using the even-odd rule to determine the region to fill.
    */

    public function eoFill() {
        $content->append("f*")->append_i($separator);
    }

    /**
    * Fills the path using the non-zero winding number rule to determine the region to fill and strokes it.
    */

    public function fillStroke() {
        $content->append("B")->append_i($separator);
    }

    /**
    * Closes the path, fills it using the non-zero winding number rule to determine the region to fill and strokes it.
    */

    public function closePathFillStroke() {
        $content->append("b")->append_i($separator);
    }

     /**
     * Fills the path, using the even-odd rule to determine the region to fill and strokes it.
     */

    public function eoFillStroke() {
        $content->append("B*")->append_i($separator);
    }

    /**
    * Closes the path, fills it using the even-odd rule to determine the region to fill and strokes it.
    */

    public function closePathEoFillStroke() {
        $content->append("b*")->append_i($separator);
    }

    public addImage()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0);
               addImage1arg($arg1);
               break; 
           }
           case 7:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               $arg4 = func_get_arg(3);
               $arg5 = func_get_arg(4);
               $arg6 = func_get_arg(5);
               $arg7 = func_get_arg(6);
               addImage7args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
               break;
           }
        }
    }
    
    /**
    * Adds an <CODE>Image</CODE> to the page. The <CODE>Image</CODE> must have
    * absolute positioning.
    * @param image the <CODE>Image</CODE> object
    * @throws DocumentException if the <CODE>Image</CODE> does not have absolute positioning
    */
    private function addImage1arg(Image $image) 
    {
        if ($image->hasAbsolutePosition() == FALSE)
            throw new DocumentException("The image must have absolute positioning.");
        $matrix = $image->matrix();
        $matrix[Image::CX] = $image->absoluteX() - $matrix[Image::CX];
        $matrix[Image::CY] = $image->absoluteY() - $matrix[Image::CY];
        addImage($image, $matrix[0], $matrix[1], $matrix[2], $matrix[3], $matrix[4], $matrix[5]);
    }

    /**
    * Adds an <CODE>Image</CODE> to the page. The positioning of the <CODE>Image</CODE>
    * is done with the transformation matrix. To position an <CODE>image</CODE> at (x,y)
    * use addImage(image, image_width, 0, 0, image_height, x, y).
    * @param image the <CODE>Image</CODE> object
    * @param a an element of the transformation matrix
    * @param b an element of the transformation matrix
    * @param c an element of the transformation matrix
    * @param d an element of the transformation matrix
    * @param e an element of the transformation matrix
    * @param f an element of the transformation matrix
    * @throws DocumentException on error
    */
    private function addImage7args(Image $image, $a, $b, $c, $d, $e, $f) {
        try {

            if ($image->isImgTemplate() == TRUE) {
                $writer->addDirectImageSimple($image);
                $template = $image->templateData();
                $w = $template->getWidth();
                $h = $template->getHeight();
                if ($image->getLayer() != NULL)
                    beginLayer($image->getLayer());
                addTemplate($template, $a / $w, $b / $w, $c / $h, $d / $h, $e, $f);
                if ($image->getLayer() != NULL)
                    endLayer();
            }
            else {
                $name = NULL;
                $prs = getPageResources();
                $maskImage = $image->getImageMask();
                if ($maskImage != NULL) {
                    $name = $writer->addDirectImageSimple($maskImage);
                    $prs->addXObject($name, $writer->getImageReference($name));
                }
                $name = $writer->addDirectImageSimple($image);
                $name = $prs->addXObject($name, $writer->getImageReference($name));
                $content->append("q ");
                $content->append($a)->append(' ');
                $content->append($b)->append(' ');
                $content->append($c)->append(' ');
                $content->append($d)->append(' ');
                $content->append($e)->append(' ');
                $content->append($f)->append(" cm ");
                $content->append($name->getBytes())->append(" Do Q")->append_i($separator);
            }
        }
        catch (Exception $ee) {
            throw new $DocumentException($ee);
        }
    }

    /**
    * Makes this <CODE>PdfContentByte</CODE> empty.
    */
    public function reset() {
        $content->reset();
        $stateList = array();
        $state = new GraphicState();
    }

    /**
    * Starts the writing of text.
    */
    public function beginText() {
        $state->xTLM = 0;
        $state->yTLM = 0;
        $content->append("BT")->append_i($separator);
    }

    /**
    * Ends the writing of text and makes the current font invalid.
    */
    public function endText() {
        $content->append("ET")->append_i($separator);
    }

    /**
    * Saves the graphic state. <CODE>saveState</CODE> and
    * <CODE>restoreState</CODE> must be balanced.
    */
    public function saveState() {
        $content->append("q")->append_i($separator);
        array_push($stateList, $state);
    }
    
    /**
    * Restores the graphic state. <CODE>saveState</CODE> and
    * <CODE>restoreState</CODE> must be balanced.
    */
    public function restoreState() {
        $content->append("Q")->append_i($separator);
        $idx = count($stateList) - 1;
        if ($idx < 0)
            throw new Exception("Unbalanced save/restore state operators.");
        $state = $stateList[$idx];
        unset($stateList[$idx]);
    }


    /**
    * Sets the character spacing parameter.
    *
    * @param		charSpace			a parameter
    */
    public function setCharacterSpacing($charSpace) {
        $content->append($charSpace)->append(" Tc")->append_i($separator);
    }

    /**
    * Sets the word spacing parameter.
    *
    * @param		wordSpace			a parameter
    */
    public function setWordSpacing($wordSpace) {
        $content->append($wordSpace)->append(" Tw")->append_i($separator);
    }

    /**
    * Sets the horizontal scaling parameter.
    *
    * @param		scale				a parameter
    */
    public function setHorizontalScaling($scale) {
        $content->append($scale)->append(" Tz")->append_i($separator);
    }

    /**
    * Sets the text leading parameter.
    * <P>
    * The leading parameter is measured in text space units. It specifies the vertical distance
    * between the baselines of adjacent lines of text.</P>
    *
    * @param		leading			the new leading
    */
    public function setLeading($leading) {
        $state->leading = $leading;
        $content->append($leading)->append(" TL")->append_i($separator);
    }

    /**
    * Set the font and the size for the subsequent text writing.
    *
    * @param bf the font
    * @param size the font size in points
    */
    public void setFontAndSize(BaseFont $bf, $size) {
        checkWriter();
        $state->size = $size;
        $state->fontDetails = $writer->addSimple($bf);
        $prs = getPageResources();
        $name = $state->fontDetails->getFontName();
        $name = $prs->addFont($name, $state->fontDetails->getIndirectReference());
        $content->append($name->getBytes())->append(' ')->append($size)->append(" Tf")->append_i($separator);
    }

    /**
    * Sets the text rendering parameter.
    *
    * @param		rendering				a parameter
    */
    public function setTextRenderingMode($rendering) {
        $content->append($rendering)->append(" Tr")->append_i($separator);
    }

    /**
    * Sets the text rise parameter.
    * <P>
    * This allows to write text in subscript or superscript mode.</P>
    *
    * @param		rise				a parameter
    */
    public function setTextRise($rise) {
        $content->append($rise)->append(" Ts")->append_i($separator);
    }

    /**
    * A helper to insert into the content stream the <CODE>text</CODE>
    * converted to bytes according to the font's encoding.
    *
    * @param text the text to write
    */
    private function showText2($text) {
        if ($state->fontDetails == NULL)
            throw new NullPointerException("Font and size must be set before writing any text");
        $b = $state->fontDetails->convertToBytes($text);
        escapeString($b, $content);
    }

    /**
    * Shows the <CODE>text</CODE>.
    *
    * @param text the text to write
    */
    public function showText($text) {
        showText2($text);
        $content->append("Tj")->append_i($separator);
    }

    /**
    * Constructs a kern array for a text in a certain font
    * @param text the text
    * @param font the font
    * @return a PdfTextArray
    */
    public static function getKernArray($text, BaseFont $font) {
        $pa = new PdfTextArray();
        $acc = "";
        $len = strlen($text) - 1;
        //char c[] = text.toCharArray();
        if ($len >= 0)
            $acc = $acc . $text[0];
        for ($k = 0; $k < $len; ++$k) {
            $c2 = $text[$k + 1];
            $kern = $font->getKerning($text[$k], $c2);
            if ($kern == 0) {
                $acc = $acc . $c2;
            }
            else {
                $pa->add($acc);
                $acc->setLength(0);
                $acc = $acc . $text[$k+1];
                $pa->add(-$kern);
            }
        }
        $pa->add($acc);
        return $pa;
    }

    /**
    * Shows the <CODE>text</CODE> kerned.
    *
    * @param text the text to write
    */
    public function showTextKerned($text) {
        if ($state->fontDetails == NULL)
            throw new NullPointerException("Font and size must be set before writing any text");
        $bf = $state->fontDetails->getBaseFont();
        if ($bf->hasKernPairs() == TRUE)
            showText(getKernArray($text, $bf));
        else
            showText($text);
    }

    public function newlineShowText()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0);
               newlineShowText1arg($arg1);
               break;
           }
           case 3:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               newlineShowText3args($arg1, $arg2, $arg3);
               break;
           }
        }
    }

    /**
    * Moves to the next line and shows <CODE>text</CODE>.
    *
    * @param text the text to write
    */
    private function newlineShowText1arg($text) {
        $state->yTLM -= $state->leading;
        showText2($text);
        $content->append("'")->append_i($separator);
    }

    /**
    * Moves to the next line and shows text string, using the given values of the character and word spacing parameters.
    *
    * @param		wordSpacing		a parameter
    * @param		charSpacing		a parameter
    * @param text the text to write
    */
    private function newlineShowText3args($wordSpacing, $charSpacing, $text) {
        $state->yTLM -= $state->leading;
        $content->append($wordSpacing)->append(' ')->append($charSpacing);
        showText2($text);
        $content->append("\"")->append_i($separator);
    }

    function setTextMatrix()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               setTextMatrix2args($arg1, $arg2);
               break;
           }
           case 6:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               $arg4 = func_get_arg(3);
               $arg5 = func_get_arg(4);
               $arg6 = func_get_arg(5);
               setTextMatrix6args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
               break;
           }
        }
    }

    /**
    * Changes the text matrix.
    * <P>
    * Remark: this operation also initializes the current point position.</P>
    *
    * @param		a			operand 1,1 in the matrix
    * @param		b			operand 1,2 in the matrix
    * @param		c			operand 2,1 in the matrix
    * @param		d			operand 2,2 in the matrix
    * @param		x			operand 3,1 in the matrix
    * @param		y			operand 3,2 in the matrix
    */
    private function setTextMatrix6args($a, $b, $c, $d, $x, $y) {
        $state->xTLM = $x;
        $state->yTLM = $y;
        $content->append($a)->append(' ')->append($b)->append_i(' ')->append($c)->append_i(' ')->append($d)->append_i(' ')->append($x)->append_i(' ')->append($y)->append(" Tm")->append_i($separator);
    }

    /**
    * Changes the text matrix. The first four parameters are {1,0,0,1}.
    * <P>
    * Remark: this operation also initializes the current point position.</P>
    *
    * @param		x			operand 3,1 in the matrix
    * @param		y			operand 3,2 in the matrix
    */
    private function setTextMatrix2args($x, $y) {
        setTextMatrix(1, 0, 0, 1, $x, $y);
    }

    /**
    * Moves to the start of the next line, offset from the start of the current line.
    *
    * @param		x			x-coordinate of the new current point
    * @param		y			y-coordinate of the new current point
    */
    public function moveText($x, $y) {
        $state->xTLM += $x;
        $state->yTLM += $y;
        $content->append($x)->append(' ')->append($y)->append(" Td")->append_i($separator);
    }

    /**
    * Moves to the start of the next line, offset from the start of the current line.
    * <P>
    * As a side effect, this sets the leading parameter in the text state.</P>
    *
    * @param		x			offset of the new current point
    * @param		y			y-coordinate of the new current point
    */
    public function moveTextWithLeading($x, $y) {
        $state->xTLM += $x;
        $state->yTLM += $y;
        $state->leading = -$y;
        $content->append($x)->append(' ')->append($y)->append(" TD")->append_i($separator);
    }

    /**
    * Moves to the start of the next line.
    */
    public function newlineText() {
        $state->yTLM -= $state->leading;
        $content->append("T*")->append_i($separator);
    }

    /**
    * Gets the size of this content.
    *
    * @return the size of the content
    */
    function size() {
        return $content->size();
    }

    static function escapeString()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0);
               return escapeString1arg($arg1);
               break;
           }
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               return escapeString2args($arg1, $arg2);
               break;
           }
        }
    }

    /**
    * Escapes a <CODE>byte</CODE> array according to the PDF conventions.
    *
    * @param b the <CODE>byte</CODE> array to escape
    * @return an escaped <CODE>byte</CODE> array
    */
    private static function escapeString1arg($b) {
        $content = new ByteBuffer();
        escapeString($b, $content);
        return $content->toByteArray();
    }

    /**
    * Escapes a <CODE>byte</CODE> array according to the PDF conventions.
    *
    * @param b the <CODE>byte</CODE> array to escape
    * @param content the content
    */
    private static function escapeString2args($b, ByteBuffer $content) {
        $content->append_i('(');
        for ($k = 0; $k < itextphp_bytes_getSize($b); ++$k) {
            //byte c = b[k];
            switch (itextphp_bytes_getIntValue($b, $k)) {
                case ord('\r'):
                    $content->append("\\r");
                    break;
                case ord('\n'):
                    $content->append("\\n");
                    break;
                case ord('\t'):
                    $content->append("\\t");
                    break;
                case ord('\b'):
                    $content->append("\\b");
                    break;
                case ord('\f'):
                    $content->append("\\f");
                    break;
                case ord('('):
                case ord(')'):
                case ord('\\'):
                    $content->append_i('\\')->append_i(itextphp_bytes_createfromInt($b, $k));
                    break;
                default:
                    $content->append_i(itextphp_bytes_createfromInt($b, $k));
            }
        }
        $content->append(")");
    }

    public function addOutline()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0);
               addOutline1arg($arg1);
               break;
           }
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               addOutline2args($arg1, $arg2);
               break;
           }
        }
    }

    /**
    * Adds an outline to the document.
    *
    * @param outline the outline
    * @deprecated not needed anymore. The outlines are extracted
    * from the root outline
    */
    private function addOutline1arg(PdfOutline $outline) {
        // for compatibility
    }
    /**
    * Adds a named outline to the document.
    *
    * @param outline the outline
    * @param name the name for the local destination
    */
    private function addOutline2args(PdfOutline $outline, $name) {
        checkWriter();
        $pdf->addOutline($outline, $name);
    }

    /**
    * Gets the root outline.
    *
    * @return the root outline
    */
    public function getRootOutline() {
        checkWriter();
        return $pdf->getRootOutline();
    }

    /**
    * Shows text right, left or center aligned with rotation.
    * @param alignment the alignment can be ALIGN_CENTER, ALIGN_RIGHT or ALIGN_LEFT
    * @param text the text to show
    * @param x the x pivot position
    * @param y the y pivot position
    * @param rotation the rotation to be applied in degrees counterclockwise
    */
    public function showTextAligned($alignment, $text, $x, $y, $rotation) {
        if ($state->fontDetails == NULL)
            throw new NullPointerException("Font and size must be set before writing any text");
        $bf = $state->fontDetails->getBaseFont();
        if ($rotation == 0) {
            switch ($alignment) {
                case PdfContentByte::ALIGN_CENTER:
                    $x -= $bf->getWidthPoint($text, $state->size) / 2;
                    break;
                case PdfContentByte::ALIGN_RIGHT:
                    $x -= $bf->getWidthPoint($text, $state->size);
                    break;
            }
            setTextMatrix($x, $y);
            showText($text);
        }
        else {
            $alpha = $rotation * M_PI / 180.0;
            $cos = cos($alpha);
            $sin = sin($alpha);
            $len = 0.0;
            switch ($alignment) {
                case PdfContentByte::ALIGN_CENTER:
                    $len = $bf->getWidthPoint($text, $state->size) / 2;
                    $x -=  $len * $cos;
                    $y -=  $len * $sin;
                    break;
                case PdfContentByte::ALIGN_RIGHT:
                    $len = $bf->getWidthPoint($text, $state->size);
                    $x -=  $len * $cos;
                    $y -=  $len * $sin;
                    break;
            }
            setTextMatrix($cos, $sin, -$sin, $cos, $x, $y);
            showText($text);
            setTextMatrix(0, 0);
        }
    }

    /**
    * Shows text kerned right, left or center aligned with rotation.
    * @param alignement the alignment can be ALIGN_CENTER, ALIGN_RIGHT or ALIGN_LEFT
    * @param text the text to show
    * @param x the x pivot position
    * @param y the y pivot position
    * @param rotation the rotation to be applied in degrees counterclockwise
    */
    public function showTextAlignedKerned($alignement, $text, $x, $y, $rotation) {
        if ($state->fontDetails == NULL)
            throw new NullPointerException("Font and size must be set before writing any text");
        $bf = $state->fontDetails->getBaseFont();
        if ($rotation == 0) {
            switch ($alignement) {
                case PdfContentByte::ALIGN_CENTER:
                    $x -= $bf->getWidthPointKerned($text, $state->size) / 2;
                    break;
                case PdfContentByte::ALIGN_RIGHT:
                    $x -= $bf->getWidthPointKerned($text, $state->size);
                    break;
            }
            setTextMatrix($x, $y);
            showTextKerned($text);
        }
        else {
            $alpha = $rotation * M_PI / 180.0;
            $cos = cos($alpha);
            $sin = sin($alpha);
            $len = 0.0;;
            switch ($alignement) {
                case PdfContentByte::ALIGN_CENTER:
                    $len = $bf->getWidthPointKerned($text, $state->size) / 2;
                    $x -=  $len * $cos;
                    $y -=  $len * $sin;
                    break;
                case PdfContentByte::ALIGN_RIGHT:
                    $len = $bf->getWidthPointKerned($text, $state->size);
                    $x -=  $len * $cos;
                    $y -=  $len * $sin;
                    break;
            }
            setTextMatrix($cos, $sin, -$sin, $cos, $x, $y);
            showTextKerned($text);
            setTextMatrix(0, 0);
        }
    }

    /**
    * Concatenate a matrix to the current transformation matrix.
    * @param a an element of the transformation matrix
    * @param b an element of the transformation matrix
    * @param c an element of the transformation matrix
    * @param d an element of the transformation matrix
    * @param e an element of the transformation matrix
    * @param f an element of the transformation matrix
    **/
    public function concatCTM($a, $b, $c, $d, $e, $f) {
        $content->append($a)->append(' ')->append($b)->append(' ')->append($c)->append(' ');
        $content->append($d)->append(' ')->append($e)->append(' ')->append($f)->append(" cm")->append_i($separator);
    }

    /**
    * Generates an array of bezier curves to draw an arc.
    * <P>
    * (x1, y1) and (x2, y2) are the corners of the enclosing rectangle.
    * Angles, measured in degrees, start with 0 to the right (the positive X
    * axis) and increase counter-clockwise.  The arc extends from startAng
    * to startAng+extent.  I.e. startAng=0 and extent=180 yields an openside-down
    * semi-circle.
    * <P>
    * The resulting coordinates are of the form float[]{x1,y1,x2,y2,x3,y3, x4,y4}
    * such that the curve goes from (x1, y1) to (x4, y4) with (x2, y2) and
    * (x3, y3) as their respective Bezier control points.
    * <P>
    * Note: this code was taken from ReportLab (www.reportlab.com), an excelent
    * PDF generator for Python.
    *
    * @param x1 a corner of the enclosing rectangle
    * @param y1 a corner of the enclosing rectangle
    * @param x2 a corner of the enclosing rectangle
    * @param y2 a corner of the enclosing rectangle
    * @param startAng starting angle in degrees
    * @param extent angle extent in degrees
    * @return a list of float[] with the bezier curves
    */
    public static function bezierArc($x1, $y1, $x2, $y2, $startAng, $extent) {
        $tmp = 0.0;
        if ($x1 > $x2) {
            $tmp = $x1;
            $x1 = $x2;
            $x2 = $tmp;
        }
        if ($y2 > $y1) {
            $tmp = $y1;
            $y1 = $y2;
            $y2 = $tmp;
        }

        $fragAngle = 0.0;
        $Nfrag = 0;;
        if (abs($extent) <= 90.0) {
            $fragAngle = $extent;
            $Nfrag = 1;
        }
        else {
            $Nfrag = ceil(abs($extent)/90.0);
            $fragAngle = $extent / $Nfrag;
        }
        $x_cen = ($x1+$x2)/2.0;
        $y_cen = ($y1+$y2)/2.0;
        $rx = ($x2-$x1)/2.0;
        $ry = ($y2-$y1)/2.0;
        $halfAng = $fragAngle * M_PI / 360;
        $kappa = (abs(4 / 3 * (1 - cos($halfAng)) / sin($halfAng)));
        $pointList = array();
        for ($i = 0; $i < $Nfrag; ++$i) {
            $theta0 = (($startAng + $i*$fragAngle) * M_PI / 180);
            $theta1 = (($startAng + ($i+1)*$fragAngle) * M_PI / 180);
            $cos0 = cos($theta0);
            $cos1 = cos($theta1);
            $sin0 = sin($theta0);
            $sin1 = sin($theta1);
            if ($fragAngle > 0.0) {
                array_push($pointList, array($x_cen + $rx * $cos0,
                $y_cen - $ry * $sin0,
                $x_cen + $rx * ($cos0 - $kappa * $sin0),
                $y_cen - $ry * ($sin0 + $kappa * $cos0),
                $x_cen + $rx * ($cos1 + $kappa * $sin1),
                $y_cen - $ry * ($sin1 - $kappa * $cos1),
                $x_cen + $rx * $cos1,
                $y_cen - $ry * $sin1));
            }
            else {
                array_push($pointList, array($x_cen + $rx * $cos0,
                $y_cen - $ry * $sin0,
                $x_cen + $rx * ($cos0 + $kappa * $sin0),
                $y_cen - $ry * ($sin0 - $kappa * $cos0),
                $x_cen + $rx * ($cos1 - $kappa * $sin1),
                $y_cen - $ry * ($sin1 + $kappa * $cos1),
                $x_cen + $rx * $cos1,
                $y_cen - $ry * $sin1));
            }
        }
        return $pointList;
    }

    /**
    * Draws a partial ellipse inscribed within the rectangle x1,y1,x2,y2,
    * starting at startAng degrees and covering extent degrees. Angles
    * start with 0 to the right (+x) and increase counter-clockwise.
    *
    * @param x1 a corner of the enclosing rectangle
    * @param y1 a corner of the enclosing rectangle
    * @param x2 a corner of the enclosing rectangle
    * @param y2 a corner of the enclosing rectangle
    * @param startAng starting angle in degrees
    * @param extent angle extent in degrees
    */
    public function arc($x1, $y1, $x2, $y2, $startAng, $extent) {
        $ar = bezierArc($x1, $y1, $x2, $y2, $startAng, $extent);
        if (count($ar) == 0)
            return;
        $pt = $ar[0];
        $moveTo($pt[0], $pt[1]);
        for ($k = 0; $k < count($ar); ++$k) {
            $pt = $ar[$k];
            curveTo($pt[2], $pt[3], $pt[4], $pt[5], $pt[6], $pt[7]);
        }
    }


    /**
    * Draws an ellipse inscribed within the rectangle x1,y1,x2,y2.
    *
    * @param x1 a corner of the enclosing rectangle
    * @param y1 a corner of the enclosing rectangle
    * @param x2 a corner of the enclosing rectangle
    * @param y2 a corner of the enclosing rectangle
    */
    public function ellipse($x1, $y1, $x2, $y2) {
        arc($x1, $y1, $x2, $y2, 0.0, 360.0);
    }

    public function createPattern()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               return createPattern2args($arg1, $arg2);
               break;
           }
           case 3:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               return createPattern3args($arg1, $arg2, $arg3);
               break;
           }
           case 4:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               $arg4 = func_get_arg(3);
               return createPattern4args($arg1, $arg2, $arg3, $arg4);
               break;
           }
           case 5:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               $arg4 = func_get_arg(3);
               $arg5 = func_get_arg(4);
               return createPattern5args($arg1, $arg2, $arg3, $arg4, $arg5);
               break;
           }
        }
    }

    /**
    * Create a new colored tiling pattern.
    *
    * @param width the width of the pattern
    * @param height the height of the pattern
    * @param xstep the desired horizontal spacing between pattern cells.
    * May be either positive or negative, but not zero.
    * @param ystep the desired vertical spacing between pattern cells.
    * May be either positive or negative, but not zero.
    * @return the <CODE>PdfPatternPainter</CODE> where the pattern will be created
    */
    private function createPattern4args($width, $height, $xstep, $ystep) {
        checkWriter();
        if ( $xstep == 0.0 || $ystep == 0.0 )
            throw new Exception("XStep or YStep can not be ZERO.");
        $painter = new PdfPatternPainter($writer);
        $painter->setWidth($width);
        $painter->setHeight($height);
        $painter->setXStep($xstep);
        $painter->setYStep($ystep);
        $writer->addSimplePattern($painter);
        return $painter;
    }

    /**
    * Create a new colored tiling pattern. Variables xstep and ystep are set to the same values
    * of width and height.
    * @param width the width of the pattern
    * @param height the height of the pattern
    * @return the <CODE>PdfPatternPainter</CODE> where the pattern will be created
    */
    private function createPattern2args($width, $height) {
        return createPattern($width, $height, $width, $height);
    }

    /**
    * Create a new uncolored tiling pattern.
    *
    * @param width the width of the pattern
    * @param height the height of the pattern
    * @param xstep the desired horizontal spacing between pattern cells.
    * May be either positive or negative, but not zero.
    * @param ystep the desired vertical spacing between pattern cells.
    * May be either positive or negative, but not zero.
    * @param color the default color. Can be <CODE>null</CODE>
    * @return the <CODE>PdfPatternPainter</CODE> where the pattern will be created
    */
    private function createPattern5args($width, $height, $xstep, $ystep, Color $color) {
        checkWriter();
        if ( $xstep == 0.0 || $ystep == 0.0 )
            throw new Exception("XStep or YStep can not be ZERO.");
        $painter = new PdfPatternPainter($writer, $color);
        $painter->setWidth($width);
        $painter->setHeight($height);
        $painter->setXStep($xstep);
        $painter->setYStep($ystep);
        $writer->addSimplePattern($painter);
        return $painter;
    }

    /**
    * Create a new uncolored tiling pattern.
    * Variables xstep and ystep are set to the same values
    * of width and height.
    * @param width the width of the pattern
    * @param height the height of the pattern
    * @param color the default color. Can be <CODE>null</CODE>
    * @return the <CODE>PdfPatternPainter</CODE> where the pattern will be created
    */
    private function createPattern3args($width, $height, $color) {
        return createPattern($width, $height, $width, $height, Color $color);
    }

    public function createTemplate()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               return createTemplate2args($arg1, $arg2);
               break;
           }
           case 3:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               return createTemplate3args($arg1, $arg2, $arg3);
               break;
           }
        }
    }

    /**
    * Creates a new template.
    * <P>
    * Creates a new template that is nothing more than a form XObject. This template can be included
    * in this <CODE>PdfContentByte</CODE> or in another template. Templates are only written
    * to the output when the document is closed permitting things like showing text in the first page
    * that is only defined in the last page.
    *
    * @param width the bounding box width
    * @param height the bounding box height
    * @return the templated created
    */
    private function createTemplate2args($width, $height) {
        return createTemplate($width, $height, NULL);
    }

    private function createTemplate3args($width, $height, $forcedName) {
        checkWriter();
        $template = new PdfTemplate($writer);
        $template->setWidth($width);
        $template->setHeight($height);
        $writer->addDirectTemplateSimple($template, $forcedName);
        return $template;
    }

    public function createAppearance()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               return createAppearance2args($arg1, $arg2);
               break;
           }
           case 3:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               return createAppearance3args($arg1, $arg2, $arg3);
               break;
           }
        }
    }

    /**
    * Creates a new appearance to be used with form fields.
    *
    * @param width the bounding box width
    * @param height the bounding box height
    * @return the appearance created
    */
    private function createAppearance2args($width, $height) {
        return createAppearance($width, $height, NULL);
    }

    private function createAppearance3args($width, $height, PdfName $forcedName) {
        checkWriter();
        $template = new PdfAppearance($writer);
        $template->setWidth($width);
        $template->setHeight($height);
        $writer->addDirectTemplateSimple($template, $forcedName);
        return $template;
    }

    /**
    * Adds a PostScript XObject to this content.
    *
    * @param psobject the object
    */
    public void addPSXObject(PdfPSXObject $psobject) {
        checkWriter();
        $name = $writer->addDirectTemplateSimple($psobject, null);
        $prs = getPageResources();
        $name = $prs->addXObject(name, $psobject->getIndirectReference());
        $content->append($name->getBytes())->append(" Do")->append_i($separator);
    }

    public function addTemplate()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 3:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               return addTemplate3args($arg1, $arg2, $arg3);
               break;
           }
           case 7:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               $arg4 = func_get_arg(3);
               $arg5 = func_get_arg(4);
               $arg6 = func_get_arg(5);
               $arg7 = func_get_arg(6);
               return addTemplate7args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
               break;
           }
        }
    }

    /**
    * Adds a template to this content.
    *
    * @param template the template
    * @param a an element of the transformation matrix
    * @param b an element of the transformation matrix
    * @param c an element of the transformation matrix
    * @param d an element of the transformation matrix
    * @param e an element of the transformation matrix
    * @param f an element of the transformation matrix
    */
    private function addTemplate7args(PdfTemplate $template, $a, $b, $c, $d, $e, $f) {
        checkWriter();
        checkNoPattern($template);
        $name = $writer->addDirectTemplateSimple($template, NULL);
        $prs = getPageResources();
        $name = $prs->addXObject($name, $template->getIndirectReference());
        $content->append("q ");
        $content->append($a)->append(' ');
        $content->append($b)->append(' ');
        $content->append($c)->append(' ');
        $content->append($d)->append(' ');
        $content->append($e)->append(' ');
        $content->append($f)->append(" cm ");
        $content->append($name->getBytes())->append(" Do Q")->append_i($separator);
    }

    /**
    * Adds a template to this content.
    *
    * @param template the template
    * @param x the x location of this template
    * @param y the y location of this template
    */
    private function addTemplate3args(PdfTemplate $template, $x, $y) {
        addTemplate($template, 1, 0, 0, 1, $x, $y);
    }


    /**
    * Changes the current color for filling paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceCMYK</B> (or the <B>DefaultCMYK</B> color space),
    * and sets the color to use for filling paths.</P>
    * <P>
    * This method is described in the 'Portable Document Format Reference Manual version 1.3'
    * section 8.5.2.1 (page 331).</P>
    * <P>
    * Following the PDF manual, each operand must be a number between 0 (no ink) and
    * 1 (maximum ink). This method however accepts only integers between 0x00 and 0xFF.</P>
    *
    * @param cyan the intensity of cyan
    * @param magenta the intensity of magenta
    * @param yellow the intensity of yellow
    * @param black the intensity of black
    */

    public function setCMYKColorFill($cyan, $magenta, $yellow, $black) {
        $content->append(($cyan & 0xFF) / 0xFF);
        $content->append(' ');
        $content->append(($magenta & 0xFF) / 0xFF);
        $content->append(' ');
        $content->append(($yellow & 0xFF) / 0xFF);
        $content->append(' ');
        $content->append(($black & 0xFF) / 0xFF);
        $content->append(" k")->append_i($separator);
    }

    /**
    * Changes the current color for stroking paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceCMYK</B> (or the <B>DefaultCMYK</B> color space),
    * and sets the color to use for stroking paths.</P>
    * <P>
    * This method is described in the 'Portable Document Format Reference Manual version 1.3'
    * section 8.5.2.1 (page 331).</P>
    * Following the PDF manual, each operand must be a number between 0 (miniumum intensity) and
    * 1 (maximum intensity). This method however accepts only integers between 0x00 and 0xFF.
    *
    * @param cyan the intensity of red
    * @param magenta the intensity of green
    * @param yellow the intensity of blue
    * @param black the intensity of black
    */

    public function setCMYKColorStroke($cyan, $magenta, $yellow, $black) {
        $content->append(($cyan & 0xFF) / 0xFF);
        $content->append(' ');
        $content->append(($magenta & 0xFF) / 0xFF);
        $content->append(' ');
        $content->append(($yellow & 0xFF) / 0xFF);
        $content->append(' ');
        $content->append(($black & 0xFF) / 0xFF);
        $content->append(" K")->append_i($separator);
    }

    /**
    * Changes the current color for filling paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceRGB</B> (or the <B>DefaultRGB</B> color space),
    * and sets the color to use for filling paths.</P>
    * <P>
    * This method is described in the 'Portable Document Format Reference Manual version 1.3'
    * section 8.5.2.1 (page 331).</P>
    * <P>
    * Following the PDF manual, each operand must be a number between 0 (miniumum intensity) and
    * 1 (maximum intensity). This method however accepts only integers between 0x00 and 0xFF.</P>
    *
    * @param red the intensity of red
    * @param green the intensity of green
    * @param blue the intensity of blue
    */

    public function setRGBColorFill($red, $green, $blue) {
        HelperRGB(($red & 0xFF) / 0xFF, ($green & 0xFF) / 0xFF, ($blue & 0xFF) / 0xFF);
        $content->append(" rg")->append_i($separator);
    }

    /**
    * Changes the current color for stroking paths (device dependent colors!).
    * <P>
    * Sets the color space to <B>DeviceRGB</B> (or the <B>DefaultRGB</B> color space),
    * and sets the color to use for stroking paths.</P>
    * <P>
    * This method is described in the 'Portable Document Format Reference Manual version 1.3'
    * section 8.5.2.1 (page 331).</P>
    * Following the PDF manual, each operand must be a number between 0 (miniumum intensity) and
    * 1 (maximum intensity). This method however accepts only integers between 0x00 and 0xFF.
    *
    * @param red the intensity of red
    * @param green the intensity of green
    * @param blue the intensity of blue
    */

    public function setRGBColorStroke($red, $green, $blue) {
        HelperRGB(($red & 0xFF) / 0xFF, ($green & 0xFF) / 0xFF, ($blue & 0xFF) / 0xFF);
        $content->append(" RG")->append_i($separator);
    }

    function setColorStroke()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0);
               setColorStroke1arg($arg1);
               break;
           }
        }
    }

    /** Sets the stroke color. <CODE>color</CODE> can be an
    * <CODE>ExtendedColor</CODE>.
    * @param color the color
    */
    private function setColorStroke1arg(Color $color) {
        PdfWriter::checkPDFXConformance($writer, PdfWriter::PDFXKEY_COLOR, $color);
        $type = ExtendedColor.getType($color);
        switch ($type) {
            case ExtendedColor::TYPE_GRAY: {
                setGrayStroke($color->getGray());
                break;
            }
            case ExtendedColor::TYPE_CMYK: {
                $cmyk = $color;
                setCMYKColorStrokeF($cmyk->getCyan(), $cmyk->getMagenta(), $cmyk->getYellow(), $cmyk->getBlack());
                break;
            }
            case ExtendedColor::TYPE_SEPARATION: {
                $spot = $color;
                setColorStroke($spot->getPdfSpotColor(), $spot->getTint());
                break;
            }
            case ExtendedColor::TYPE_PATTERN: {
                $pat = $color;
                setPatternStroke($pat->getPainter());
                break;
            }
            case ExtendedColor::TYPE_SHADING: {
                $shading = $color;
                setShadingStroke($shading->getPdfShadingPattern());
                break;
            }
            default:
                setRGBColorStroke($color->getRed(), $color->getGreen(), $color->getBlue());
        }
    }

    public function setColorFill()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0);
               setColorFill1arg($arg1);
               break;
           }
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               setColorFill1arg($arg1, $arg2);
               break;
           }
        }
    }

    /** Sets the fill color. <CODE>color</CODE> can be an
    * <CODE>ExtendedColor</CODE>.
    * @param color the color
    */
    private function setColorFill1arg(Color $color) {
        PdfWriter::checkPDFXConformance($writer, PdfWriter::PDFXKEY_COLOR, $color);
        $type = ExtendedColor::getType($color);
        switch ($type) {
            case ExtendedColor::TYPE_GRAY: {
                setGrayFill(($color)->getGray());
                break;
            }
            case ExtendedColor::TYPE_CMYK: {
                $cmyk = $color;
                setCMYKColorFillF($cmyk->getCyan(), $cmyk->getMagenta(), $cmyk->getYellow(), $cmyk->getBlack());
                break;
            }
            case ExtendedColor::TYPE_SEPARATION: {
                $spot = $color;
                setColorFill($spot->getPdfSpotColor(), $spot->getTint());
                break;
            }
            case ExtendedColor::TYPE_PATTERN: {
                $pat = $color;
                setPatternFill($pat->getPainter());
                break;
            }
            case ExtendedColor::TYPE_SHADING: {
                $shading = $color;
                setShadingFill($shading->getPdfShadingPattern());
                break;
            }
            default:
                setRGBColorFill($color->getRed(), $color->getGreen(), $color->getBlue());
        }
    }

    /** Sets the fill color to a spot color.
    * @param sp the spot color
    * @param tint the tint for the spot color. 0 is no color and 1
    * is 100% color
    */
    private function setColorFill2args(PdfSpotColor $sp, $tint) {
        checkWriter();
        $state->colorDetails = $writer->addSimple($sp);
        $prs = getPageResources();
        $name = $state->colorDetails->getColorName();
        $name = $prs->addColor($name, $state->colorDetails->getIndirectReference());
        $content->append($name->getBytes())->append(" cs ")->append($tint)->append(" scn")->append_i($separator);
    }

    /** Sets the stroke color to a spot color.
     * @param sp the spot color
     * @param tint the tint for the spot color. 0 is no color and 1
     * is 100% color
     */
    private function setColorStroke2args(PdfSpotColor $sp, $int) {
        checkWriter();
        $state->colorDetails = $writer->addSimple(sp);
        $prs = getPageResources();
        $name = $state->colorDetails->getColorName();
        $name = $prs->addColor($name, $state->colorDetails->getIndirectReference());
        $content->append($name->getBytes())->append(" CS ")->append($tint)->append(" SCN")->append_i($separator);
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
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               setPatternFill2args($arg1, $arg2);
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

    /** Sets the fill color to a pattern. The pattern can be
    * colored or uncolored.
    * @param p the pattern
    */
    public function setPatternFill1arg(PdfPatternPainter $p) {
        if ($p->isStencil() == TRUE) {
            setPatternFill($p, $p->getDefaultColor());
            return;
        }
        checkWriter();
        $prs = getPageResources();
        $name = $writer->addSimplePattern($p);
        $name = $prs->addPattern($name, $p->getIndirectReference());
        $content->append(PdfName::$PATTERN->getBytes())->append(" cs ")->append($name->getBytes())->append(" scn")->append_i($separator);
    }

    /** Outputs the color values to the content.
    * @param color The color
    * @param tint the tint if it is a spot color, ignored otherwise
    */
    function outputColorNumbers(Color $color, $tint) {
        PdfWriter::checkPDFXConformance($writer, PdfWriter::PDFXKEY_COLOR, $color);
        $type = ExtendedColor::getType($color);
        switch (type) {
            case ExtendedColor::TYPE_RGB:
                $content->append(($color->getRed()) / 0xFF);
                $content->append(' ');
                $content->append(($color->getGreen()) / 0xFF);
                $content->append(' ');
                $content->append(($color->getBlue()) / 0xFF);
                break;
            case ExtendedColor::TYPE_GRAY:
                $content->append(($color)->getGray());
                break;
            case ExtendedColor::TYPE_CMYK: {
                $cmyk = $color;
                $content->append($cmyk->getCyan())->append(' ')->append($cmyk->getMagenta());
                $content->append(' ')->append($cmyk->getYellow())->append(' ')->append($cmyk->getBlack());
                break;
            }
            case ExtendedColor::TYPE_SEPARATION:
                $content->append($tint);
                break;
            default:
                throw new Exception("Invalid color type.");
        }
    }
    /** Sets the fill color to an uncolored pattern.
    * @param p the pattern
    * @param color the color of the pattern
    */
    private function setPatternFill2args(PdfPatternPainter $p, Color $color) {
        if (ExtendedColor::getType($color) == ExtendedColor::TYPE_SEPARATION)
            setPatternFill($p, $color, ($color)->getTint());
        else
            setPatternFill($p, $color, 0);
    }

    /** Sets the fill color to an uncolored pattern.
    * @param p the pattern
    * @param color the color of the pattern
    * @param tint the tint if the color is a spot color, ignored otherwise
    */
    private function setPatternFill3args(PdfPatternPainter $p, Color $color, $tint) {
        checkWriter();
        if ($p->isStencil() == FALSE)
            throw new Exception("An uncolored pattern was expected.");
        $prs = getPageResources();
        $name = $writer->addSimplePattern($p);
        $name = $prs->addPattern($name, $p->getIndirectReference());
        $csDetail = $writer->addSimplePatternColorspace($color);
        $cName = $prs->addColor($csDetail->getColorName(), $csDetail->getIndirectReference());
        $content->append($cName->getBytes())->$append(" cs")->append_i($separator);
        outputColorNumbers($color, $tint);
        $content->append(' ')->append($name->getBytes())->append(" scn")->append_i($separator);
    }

    public function setPatternStroke()
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
           case 2:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               setPatternStroke2args($arg1, $arg2);
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

    /** Sets the stroke color to an uncolored pattern.
    * @param p the pattern
    * @param color the color of the pattern
    */
    private function setPatternStroke2args(PdfPatternPainter $p, Color $color) {
        if (ExtendedColor::getType($color) == ExtendedColor::TYPE_SEPARATION)
            setPatternStroke($p, $color, ($color)->getTint());
        else
            setPatternStroke($p, $color, 0);
    }
    

    /** Sets the stroke color to an uncolored pattern.
    * @param p the pattern
    * @param color the color of the pattern
    * @param tint the tint if the color is a spot color, ignored otherwise
    */
    private function setPatternStroke3args(PdfPatternPainter $p, Color $color, $tint) {
        checkWriter();
        if ($p->isStencil() == FALSE)
            throw new Exception("An uncolored pattern was expected.");
        $prs = getPageResources();
        $name = $writer->addSimplePattern($p);
        $name = $prs->addPattern($name, $p->getIndirectReference());
        $csDetail = $writer->addSimplePatternColorspace($color);
        $cName = $prs->addColor($csDetail->getColorName(), $csDetail->getIndirectReference());
        $content->append($cName->getBytes())->append(" CS")->append_i($separator);
        outputColorNumbers($color, $tint);
        $content->append(' ')->append($name->getBytes())->append(" SCN")->append_i($separator);
    }

    /** Sets the stroke color to a pattern. The pattern can be
    * colored or uncolored.
    * @param p the pattern
    */
    private function setPatternStroke1arg(PdfPatternPainter $p) {
        if ($p->isStencil() == TRUE) {
            setPatternStroke($p, $p->getDefaultColor());
            return;
        }
        checkWriter();
        $prs = getPageResources();
        $name = $writer->addSimplePattern($p);
        $name = $prs->addPattern($name, $p->getIndirectReference());
        $content->append(PdfName::$PATTERN->getBytes())->append(" CS ")->append($name->getBytes())->append(" SCN")->append_i($separator);
    }

    public function paintShading()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0);
               if ($arg1 instanceof PdfShading)
               {
                   paintShading1argPdfShading($arg1);
               }
               else if ($arg1 instance of PdfShadingPattern)
               {
                   paintShading1argPdfShadingPattern($arg1);
               }
               break;
           }
        }
    }


    /**
    * Paints using a shading object. 
    * @param shading the shading object
    */
    private function paintShading1argPdfShading(PdfShading $shading) {
        $writer->addSimpleShading($shading);
        $prs = getPageResources();
        $name = $prs->addShading($shading->getShadingName(), $shading->getShadingReference());
        $content->append($name->getBytes())->append(" sh")->append_i($separator);
        $details = $shading->getColorDetails();
        if ($details != NULL)
            $prs->addColor($details->getColorName(), $details->getIndirectReference());
    }

    /**
    * Paints using a shading pattern. 
    * @param shading the shading pattern
    */
    private function paintShading1argPdfShadingPattern(PdfShadingPattern $shading) {
        paintShading($shading->getShading());
    }

    /**
    * Sets the shading fill pattern.
    * @param shading the shading pattern
    */
    public function setShadingFill(PdfShadingPattern $shading) {
        $writer->addSimpleShadingPattern($shading);
        $prs = getPageResources();
        $name = $prs->addPattern($shading->getPatternName(), $shading->getPatternReference());
        $content->append(PdfName::$PATTERN->getBytes())->append(" cs ")->append($name->getBytes())->append(" scn")->append_i($separator);
        $details = $shading->getColorDetails();
        if ($details != NULL)
            $prs->addColor($details->getColorName(), $details->getIndirectReference());
    }

    /**
    * Sets the shading stroke pattern
    * @param shading the shading pattern
    */
    public function setShadingStroke(PdfShadingPattern $shading) {
        $writer->addSimpleShadingPattern($shading);
        $prs = getPageResources();
        $name = $prs->addPattern($shading->getPatternName(), $shading->getPatternReference());
        $content->append(PdfName::$PATTERN->getBytes())->append(" CS ")->append($name->getBytes())->append(" SCN")->append_i($separator);
        $details = $shading->getColorDetails();
        if ($details != NULL)
            $prs->addColor($details->getColorName(), $details->getIndirectReference());
    }


    /** Check if we have a valid PdfWriter.
    *
    */
    protected function checkWriter() {
        if ($writer == NULL)
            throw new NullPointerException("The writer in PdfContentByte is null.");
    }


    /**
    * Show an array of text.
    * @param text array of text
    */
    public function showText(PdfTextArray $text) {
        if ($state->fontDetails == NULL)
            throw new NullPointerException("Font and size must be set before writing any text");
        $content->append("[");
        $arrayList = $text->getArrayList();
        $lastWasNumber = FAKSE;
        for ($k = 0; k < count($arrayList); ++$k) {
            $obj = $arrayList[$k];
            if (strcmp(gettype($obj), "string") == 0) {
                showText2($obj);
                $lastWasNumber = FALSE;
            }
            else {
                if ($lastWasNumber == TRUE)
                    $content->append(' ');
                else
                    $lastWasNumber = TRUE;
                $content->append(($obj)->floatValue());
            }
        }
        $content->append("]TJ")->append_i($separator);
    }


    /**
    * Gets the <CODE>PdfWriter</CODE> in use by this object.
    * @return the <CODE>PdfWriter</CODE> in use by this object
    */
    public function getPdfWriter() {
        return $writer;
    }

    /**
    * Gets the <CODE>PdfDocument</CODE> in use by this object.
    * @return the <CODE>PdfDocument</CODE> in use by this object
    */
    public function getPdfDocument() {
        return $pdf;
    }

    /**
    * Implements a link to other part of the document. The jump will
    * be made to a local destination with the same name, that must exist.
    * @param name the name for this link
    * @param llx the lower left x corner of the activation area
    * @param lly the lower left y corner of the activation area
    * @param urx the upper right x corner of the activation area
    * @param ury the upper right y corner of the activation area
    */
    public function localGoto($name, $llx, $lly, $urx, $ury) {
        $pdf->localGoto($name, $llx, $lly, $urx, $ury);
    }

    /**
    * The local destination to where a local goto with the same
    * name will jump.
    * @param name the name of this local destination
    * @param destination the <CODE>PdfDestination</CODE> with the jump coordinates
    * @return <CODE>true</CODE> if the local destination was added,
    * <CODE>false</CODE> if a local destination with the same name
    * already exists
    */
    public function localDestination($name, PdfDestination $destination) {
        return $pdf->localDestination($name, $destination);
    }

    /**
    * Gets a duplicate of this <CODE>PdfContentByte</CODE>. All
    * the members are copied by reference but the buffer stays different.
    *
    * @return a copy of this <CODE>PdfContentByte</CODE>
    */
    public function getDuplicate() {
        return new PdfContentByte($writer);
    }

    public function remoteGoto()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0);
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               $arg4 = func_get_arg(3);
               $arg5 = func_get_arg(4);
               $arg6 = func_get_arg(5);
               if (strcmp(gettype($arg2), "string") == 0)
                   remoteGoto6args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
               else
                   remoteGoto6argswithpage($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
               break;
           }
        }
    }

    /**
    * Implements a link to another document.
    * @param filename the filename for the remote document
    * @param name the name to jump to
    * @param llx the lower left x corner of the activation area
    * @param lly the lower left y corner of the activation area
    * @param urx the upper right x corner of the activation area
    * @param ury the upper right y corner of the activation area
    */
    private function remoteGoto6args($filename, $name, $llx, $lly, $urx, $ury) {
        $pdf->remoteGoto($filename, $name, $llx, $lly, $urx, $ury);
    }

    /**
    * Implements a link to another document.
    * @param filename the filename for the remote document
    * @param page the page to jump to
    * @param llx the lower left x corner of the activation area
    * @param lly the lower left y corner of the activation area
    * @param urx the upper right x corner of the activation area
    * @param ury the upper right y corner of the activation area
    */
    private function remoteGoto6argswithpage($filename, $page, $llx, $lly, $urx, $ury) {
        $pdf->remoteGoto($filename, $page, $llx, $lly, $urx, $ury);
    }

    /**
    * Adds a round rectangle to the current path.
    *
    * @param x x-coordinate of the starting point
    * @param y y-coordinate of the starting point
    * @param w width
    * @param h height
    * @param r radius of the arc corner
    */
    public function roundRectangle($x, $y, $w, $h, $r) {
        if ($w < 0) {
            $x += $w;
            $w = -$w;
        }
        if ($h < 0) {
            $y += $h;
            $h = -$h;
        }
        if ($r < 0)
            $r = -$r;
        $b = 0.4477;
        moveTo($x + $r, $y);
        lineTo($x + $w - $r, $y);
        curveTo($x + $w - $r * $b, $y, $x + $w, $y + $r * $b, $x + $w, $y + $r);
        lineTo($x + $w, $y + $h - $r);
        curveTo($x + $w, $y + $h - $r * $b, $x + $w - $r * $b, $y + $h, $x + $w - $r, $y + $h);
        lineTo($x + $r, $y + $h);
        curveTo($x + $r * $b, $y + $h, $x, $y + $h - $r * $b, $x, $y + $h - $r);
        lineTo($x, $y + $r);
        curveTo($x, $y + $r * $b, $x + $r * $b, $y, $x + $r, $y);
    }

    /** Implements an action in an area.
    * @param action the <CODE>PdfAction</CODE>
    * @param llx the lower left x corner of the activation area
    * @param lly the lower left y corner of the activation area
    * @param urx the upper right x corner of the activation area
    * @param ury the upper right y corner of the activation area
    */
    public function setAction(PdfAction $action, $llx, $lly, $urx, $ury) {
        $pdf->setAction($action, $llx, $lly, $urx, $ury);
    }

    /** Outputs a <CODE>String</CODE> directly to the content.
    * @param s the <CODE>String</CODE>
    */
    public void setLiteral($s) {
        content.append($s);
    }

    /** Throws an error if it is a pattern.
    * @param t the object to check
    */
    function checkNoPattern(PdfTemplate $t) {
        if ($t->getType() == PdfTemplate::TYPE_PATTERN)
            throw new Exception("Invalid use of a pattern. A template was expected.");
    }

    /**
    * Draws a TextField.
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @param on
    */
    public function drawRadioField($llx, $lly, $urx, $ury, $on) {
        if ($llx > $urx) { $x = $llx; $llx = $urx; $urx = $x; }
        if ($lly > $ury) { $y = $lly; $lly = $ury; $ury = $y; }
        // silver circle
        setLineWidth(1);
        setLineCap(1);
        setColorStroke(new Color(0xC0, 0xC0, 0xC0));
        arc($llx + 1.0, $lly + 1.0, $urx - 1.0, $ury - 1.0, 0.0, 360.0);
        stroke();
        // gray circle-segment
        setLineWidth(1);
        setLineCap(1);
        setColorStroke(new Color(0xA0, 0xA0, 0xA0));
        arc($llx + 0.5, $lly + 0.5, $urx - 0.5, $ury - 0.5, 45, 180);
        stroke();
        // black circle-segment
        setLineWidth(1);
        setLineCap(1);
        setColorStroke(new Color(0x00, 0x00, 0x00));
        arc($llx + 1.5, $lly + 1.5, $urx - 1.5, $ury - 1.5, 45, 180);
        stroke();
        if ($on == TRUE) {
            // gray circle
            setLineWidth(1);
            setLineCap(1);
            setColorFill(new Color(0x00, 0x00, 0x00));
            arc($llx + 4.0, $lly + 4.0, $urx - 4.0, $ury - 4.0, 0, 360);
            fill();
        }
    }

    /**
    * Draws a TextField.
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function drawTextField($llx, $lly, $urx, $ury) {
        if ($llx > $urx) { $x = $llx; $llx = $urx; $urx = $x; }
        if ($lly > $ury) { $y = $lly; $lly = $ury; $ury = $y; }
        // silver rectangle not filled
        setColorStroke(new Color(0xC0, 0xC0, 0xC0));
        setLineWidth(1);
        setLineCap(0);
        rectangle($llx, $lly, $urx - $llx, $ury - $lly);
        stroke();
        // white rectangle filled
        setLineWidth(1);
        setLineCap(0);
        setColorFill(new Color(0xFF, 0xFF, 0xFF));
        rectangle($llx + 0.5, $lly + 0.5, $urx - $llx - 1.0, $ury -$lly - 1.0);
        fill();
        // silver lines
        setColorStroke(new Color(0xC0, 0xC0, 0xC0));
        setLineWidth(1);
        setLineCap(0);
        moveTo($llx + 1.0, $lly + 1.5);
        lineTo($urx - 1.5, $lly + 1.5);
        lineTo($urx - 1.5, $ury - 1.0);
        stroke();
        // gray lines
        setColorStroke(new Color(0xA0, 0xA0, 0xA0));
        setLineWidth(1);
        setLineCap(0);
        moveTo($llx + 1.0, $lly + 1);
        lineTo($llx + 1.0, $ury - 1.0);
        lineTo($urx - 1.0, $ury - 1.0);
        stroke();
        // black lines
        setColorStroke(new Color(0x00, 0x00, 0x00));
        setLineWidth(1);
        setLineCap(0);
        moveTo($llx + 2.0, $lly + 2.0);
        lineTo($llx + 2.0, $ury - 2.0);
        lineTo($urx - 2.0, $ury - 2.0);
        stroke();
    }

    /**
    * Draws a button.
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @param text
    * @param bf
    * @param size
    */
    public function drawButton($llx, $lly, $urx, $ury, $text, BaseFont $bf, $size) {
        if ($llx > $urx) { $x = $llx; $llx = $urx; $urx = $x; }
        if ($lly > $ury) { $y = $lly; $lly = $ury; $ury = $y; }
        // black rectangle not filled
        setColorStroke(new Color(0x00, 0x00, 0x00));
        setLineWidth(1);
        setLineCap(0);
        rectangle($llx, $lly, $urx - $llx, $ury - $lly);
        stroke();
        // silver rectangle filled
        setLineWidth(1);
        setLineCap(0);
        setColorFill(new Color(0xC0, 0xC0, 0xC0));
        rectangle($llx + 0.5, $lly + 0.5, $urx - $llx - 1.0, $ury -$lly - 1.0);
        fill();
        // white lines
        setColorStroke(new Color(0xFF, 0xFF, 0xFF));
        setLineWidth(1);
        setLineCap(0);
        moveTo($llx + 1.0, $lly + 1.0);
        lineTo($llx + 1.0, $ury - 1.0);
        lineTo($urx - 1.0, $ury - 1.0);
        stroke();
        // dark grey lines
        setColorStroke(new Color(0xA0, 0xA0, 0xA0));
        setLineWidth(1);
        setLineCap(0);
        moveTo($llx + 1.0, $lly + 1.0);
        lineTo($urx - 1.0, $lly + 1.0);
        lineTo($urx - 1.0, $ury - 1.0);
        stroke();
        // text
        resetRGBColorFill();
        beginText();
        setFontAndSize($bf, $size);
        showTextAligned(PdfContentByte::ALIGN_CENTER, $text, $llx + ($urx - $llx) / 2, $lly + ($ury - $lly - $size) / 2, 0);
        endText();
    }

    /** Gets a <CODE>Graphics2D</CODE> to write on. The graphics
    * are translated to PDF commands as shapes. No PDF fonts will appear.
    * @param width the width of the panel
    * @param height the height of the panel
    * @return a <CODE>Graphics2D</CODE>
    */
    public function createGraphicsShapes($width, $height) {
        //return new PdfGraphics2D($this, $width, $height, NULL, TRUE, FAKSE, 0);
        return NULL;//not implemented
    }

    /** Gets a <CODE>Graphics2D</CODE> to print on. The graphics
    * are translated to PDF commands as shapes. No PDF fonts will appear.
    * @param width the width of the panel
    * @param height the height of the panel
    * @param printerJob a printer job
    * @return a <CODE>Graphics2D</CODE>
    */
    public function createPrinterGraphicsShapes($width, $height, PrinterJob $printerJob) {
        //return new PdfPrinterGraphics2D($this, $width, $height, NULL, $true, FALSE, 0, $printerJob);
        return NULL;//not implemented
    }

    /** Gets a <CODE>Graphics2D</CODE> to write on. The graphics
    * are translated to PDF commands.
    * @param width the width of the panel
    * @param height the height of the panel
    * @return a <CODE>Graphics2D</CODE>
    */
    public function createGraphics($width, $height) {
        return new PdfGraphics2D($this, $width, $height, NULL, FALSE, FALSE, 0);
    }

    /** Gets a <CODE>Graphics2D</CODE> to print on. The graphics
    * are translated to PDF commands.
    * @param width the width of the panel
    * @param height the height of the panel
    * @param fontMapper the mapping from awt fonts to <CODE>BaseFont</CODE>
    * @param convertImagesToJPEG converts awt images to jpeg before inserting in pdf
    * @param quality the quality of the jpeg
    * @param printerJob a printer job
    * @return a <CODE>Graphics2D</CODE>
    */
    public function createPrinterGraphics($width, $height, FontMapper $fontMapper, $convertImagesToJPEG, $quality, PrinterJob $printerJob) {
        return new PdfPrinterGraphics2D($this, $width, $height, $fontMapper, FALSE, $convertImagesToJPEG, $quality, $printerJob);
    }

    function getPageResources() {
        return $pdf.getPageResources();
    }

    /** Sets the graphic state
    * @param gstate the graphic state
    */
    public function setGState(PdfGState $gstate) {
        $obj = $writer->addSimpleExtGState($gstate);
        $prs = getPageResources();
        $name = $prs->addExtGState($obj[0], $obj[1]);
        $content->append($name->getBytes())->append(" gs")->append_i($separator);
    }

    /**
    * Begins a graphic block whose visibility is controled by the <CODE>layer</CODE>.
    * Blocks can be nested. Each block must be terminated by an {@link #endLayer()}.<p>
    * Note that nested layers with {@link PdfLayer#addChild(PdfLayer)} only require a single
    * call to this method and a single call to {@link #endLayer()}; all the nesting control
    * is built in.
    * @param layer the layer
    */
    public function beginLayer(PdfOCG $layer) {
        if (($layer instanceof PdfLayer) && ($layer)->getTitle() != NULL)
            throw new IllegalArgumentException("A title is not a layer");
        if ($layerDepth == NULL)
            $layerDepth = array();
        if ($layer instanceof PdfLayerMembership) {
            array_push($layerDepth,1);
            beginLayer2($layer);
            return;
        }
        $n = 0;
        $la = $layer;
        while ($la != NULL) {
            if ($la->getTitle() == NULL) {
                beginLayer2($la);
                ++$n;
            }
            $la = $la->getParent();
        }
        array_push($layerDepth, $n);
    }

    private function beginLayer2(PdfOCG $layer) {
        $name = $writer->addSimpleLayer($layer);
        $prs = getPageResources();
        $name = $prs->addLayer($name, $layer->getRef());
        $content->append("/OC ")->append($name->getBytes())->append(" BDC")->append_i($separator);
    }

    /**
    * Ends a layer controled graphic block. It will end the most recent open block.
    */
    public function endLayer() {
        $n = 1;
        if ($layerDepth != NULL && count($layerDepth) > 0) {
            $n = $layerDepth[count($layerDepth) - 1];
            unset($layerDepth[count($layerDepth) - 1]);
        }
        while ($n-- > 0)
            $content->append("EMC")->append_i($separator);
    }

    /** Concatenates a transformation to the current transformation
    * matrix.
    * @param af the transformation
    */
    public function transform(AffineTransform $af) {
        $arr = array();
        array_pad($arr,6,0.0);;
        $af->getMatrix($arr);
        $content->append($arr[0])->append(' ')->append($arr[1])->append(' ')->append($arr[2])->append(' ');
        $content->append($arr[3])->append(' ')->append($arr[4])->append(' ')->append($arr[5])->append(" cm")->append_i($separator);
    }

    function addAnnotation(PdfAnnotation $annot) {
        $writer->addAnnotation($annot);
    }

    /**
    * Sets the default colorspace.
    * @param name the name of the colorspace. It can be <CODE>PdfName.DEFAULTGRAY</CODE>, <CODE>PdfName.DEFAULTRGB</CODE>
    * or <CODE>PdfName.DEFAULTCMYK</CODE>
    * @param obj the colorspace. A <CODE>null</CODE> or <CODE>PdfNull</CODE> removes any colorspace with the same name
    */
    public function setDefaultColorspace(PdfName $name, PdfObject $obj) {
        $prs = getPageResources();
        $prs->addDefaultColor($name, $obj);
    }
}


/**
* This class keeps the graphic state of the current page
*/

class GraphicState
{

    /** This is the font in use */
    $fontDetails = NULL;

    /** This is the color in use */
    $colorDetails = NULL;

    /** This is the font size in use */
    $size 0.0;

    /** The x position of the text line matrix. */
    protected $xTLM = 0;
    /** The y position of the text line matrix. */
    protected $yTLM = 0;

    /** The current text leading. */
    protected $leading = 0;

}


?>