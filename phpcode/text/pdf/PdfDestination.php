<?PHP
/*
 * $Id: PdfDestination.php,v 1.2 2005/10/18 16:40:06 mstaylor Exp $
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
require_once("PdfName.php");
require_once("PdfNumber.php");
require_once("PdfNull.php");
require_once("PdfIndirectReference.php");


class PdfDestination extends PdfArray 
{

    // public static final member-variables

    /** This is a possible destination type */
    const XYZ = 0;

    /** This is a possible destination type */
    const FIT = 1;

    /** This is a possible destination type */
    const FITH = 2;

    /** This is a possible destination type */
    const FITV = 3;

    /** This is a possible destination type */
    const FITR = 4;

    /** This is a possible destination type */
    const FITB = 5;

    /** This is a possible destination type */
    const FITBH = 6;

    /** This is a possible destination type */
    const FITBV = 7;

    // member variables

    /** Is the indirect reference to a page already added? */
    private $status = FALSE;

    // constructors

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
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
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
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
                construct5args($arg1, $arg2, $arg3, $arg4, $arg5);
                break;
            }
        }
    }

    /**
    * Constructs a new <CODE>PdfDestination</CODE>.
    * <P>
    * If <VAR>type</VAR> equals <VAR>FITB</VAR>, the bounding box of a page
    * will fit the window of the Reader. Otherwise the type will be set to
    * <VAR>FIT</VAR> so that the entire page will fit to the window.
    *
    * @param		type		The destination type
    */
    private function construct1arg($type)
    {
        parent::__construct();
        if ($type == PdfDestination::FITB) {
            add(PdfName::$FITB);
        }
        else {
            add(PdfName::$FIT);
        }
    }

    /**
    * Constructs a new <CODE>PdfDestination</CODE>.
    * <P>
    * If <VAR>type</VAR> equals <VAR>FITBH</VAR> / <VAR>FITBV</VAR>,
    * the width / height of the bounding box of a page will fit the window
    * of the Reader. The parameter will specify the y / x coordinate of the
    * top / left edge of the window. If the <VAR>type</VAR> equals <VAR>FITH</VAR>
    * or <VAR>FITV</VAR> the width / height of the entire page will fit
    * the window and the parameter will specify the y / x coordinate of the
    * top / left edge. In all other cases the type will be set to <VAR>FITH</VAR>.
    *
    * @param		type		the destination type
    * @param		parameter	a parameter to combined with the destination type
    */
    private function construct2args($type, $parameter) {
        parent::__construct(new PdfNumber($parameter));
        switch($type) {
            default:
                addFirst(PdfName::$FITH);
                break;
            case PdfDestination::FITV:
                addFirst(PdfName::$FITV);
                break;
            case PdfDestination::FITBH:
                addFirst(PdfName::$FITBH);
                break;
            case PdfDestination::FITBV:
                addFirst(PdfName::$FITBV);
        }
    }

    /** Constructs a new <CODE>PdfDestination</CODE>.
    * <P>
    * Display the page, with the coordinates (left, top) positioned
    * at the top-left corner of the window and the contents of the page magnified
    * by the factor zoom. A negative value for any of the parameters left or top, or a
    * zoom value of 0 specifies that the current value of that parameter is to be retained unchanged.
    * @param type must be a <VAR>PdfDestination.XYZ</VAR>
    * @param left the left value. Negative to place a null
    * @param top the top value. Negative to place a null
    * @param zoom The zoom factor. A value of 0 keeps the current value
    */
    private function construct4args($type, $left, $top, $zoom) {
        parent::__construct(PdfName::$XYZ);
        if ($left < 0)
            add(PdfNull::$PDFNULL);
        else
            add(new PdfNumber($left));
        if ($top < 0)
            add(PdfNull::$PDFNULL);
        else
            add(new PdfNumber($top));
        add(new PdfNumber($zoom));
    }


    /** Constructs a new <CODE>PdfDestination</CODE>.
    * <P>
    * Display the page, with its contents magnified just enough
    * to fit the rectangle specified by the coordinates left, bottom, right, and top
    * entirely within the window both horizontally and vertically. If the required
    * horizontal and vertical magnification factors are different, use the smaller of
    * the two, centering the rectangle within the window in the other dimension.
    *
    * @param type must be PdfDestination.FITR
    * @param left a parameter
    * @param bottom a parameter
    * @param right a parameter
    * @param top a parameter
    * @since iText0.38
    */
    private function construct5args($type, $left, $bottom, $right, $top) {
        parent::__construct(PdfName::$FITR);
        add(new PdfNumber($left));
        add(new PdfNumber($bottom));
        add(new PdfNumber($right));
        add(new PdfNumber($top));
    }

    // methods

    /**
    * Checks if an indirect reference to a page has been added.
    *
    * @return	<CODE>true</CODE> or <CODE>false</CODE>
    */

    public function hasPage() {
        return $status;
    }

    /** Adds the indirect reference of the destination page.
    *
    * @param page	an indirect reference
    * @return true if the page reference was added
    */

    public function addPage(PdfIndirectReference $page) {
        if ($status == FALSE) {
            addFirst($page);
            $status = TRUE;
            return TRUE;
        }
        return FALSE;
    }


}

?>