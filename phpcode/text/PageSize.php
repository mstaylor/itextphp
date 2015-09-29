<?PHP
/*
 * $Id: PageSize.php,v 1.2 2005/10/03 19:58:37 mstaylor Exp $
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

/**
* The <CODE>PageSize</CODE>-object contains a number of rectangles representing the most common papersizes.
*
* @see		Rectangle
*/

require_once("Rectangle.php");


class PageSize
{

    // membervariables

    /** This is the letter format */
    public static $LETTER = NULL;

    /** This is the note format */
    public static $NOTE = NULL;

    /** This is the legal format */
    public static $LEGAL = NULL;

    /** This is the a0 format */
    public static $A0 = NULL;

    /** This is the a1 format */
    public static $A1 = NULL;

    /** This is the a2 format */
    public static $A2 = NULL;

    /** This is the a3 format */
    public static $A3 = NULL;

    /** This is the a4 format */
    public static $A4 = NULL;

    /** This is the a5 format */
    public static $A5 = NULL;

    /** This is the a6 format */
    public static $A6 = NULL;

    /** This is the a7 format */
    public static $A7 = NULL;

    /** This is the a8 format */
    public static $A8 = NULL;

    /** This is the a9 format */
    public static $A9 = NULL;

    /** This is the a10 format */
    public static $A10 = NULL;

    /** This is the b0 format */
    public static $B0 = NULL;

    /** This is the b1 format */
    public static $B1 = NULL;

    /** This is the b2 format */
    public static $B2 = NULL;

    /** This is the b3 format */
    public static $B3 = NULL;

    /** This is the b4 format */
    public static $B4 = NULL;

    /** This is the b5 format */
    public static $B5 = NULL;

    /** This is the archE format */
    public static $ARCH_E = NULL;

    /** This is the archD format */
    public static $ARCH_D = NULL;

    /** This is the archC format */
    public static $ARCH_C = NULL;

    /** This is the archB format */
    public static $ARCH_B = NULL;

    /** This is the archA format */
    public static $ARCH_A = NULL;

    /** This is the flsa format */
    public static $FLSA = NULL;

    /** This is the flse format */
    public static $FLSE = NULL;

    /** This is the halfletter format */
    public static $HALFLETTER = NULL;

    /** This is the 11x17 format */
    public static $_11X17 = NULL;

    /** This is the ledger format */
    public static $LEDGER = NULL;

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PageSize::$initialized == FALSE)
        {
            PageSize::$LETTER = new Rectangle(612,792);
            PageSize::$NOTE = new Rectangle(540,720);
            PageSize::$LEGAL = new Rectangle(612,1008);
            PageSize::$A0 = new Rectangle(2384,3370);
            PageSize::$A1 = new Rectangle(1684,2384);
            PageSize::$A2 = new Rectangle(1190,1684);
            PageSize::$A3 = new Rectangle(842,1190);
            PageSize::$A4 = new Rectangle(595,842);
            PageSize::$A5 = new Rectangle(421,595);
            PageSize::$A6 = new Rectangle(297,421);
            PageSize::$A7 = new Rectangle(210,297);
            PageSize::$A8 = new Rectangle(148,210);
            PageSize::$A9 = new Rectangle(105,148);
            PageSize::$A10 = new Rectangle(74,105);
            PageSize::$B0 = new Rectangle(2836,4008);
            PageSize::$B1 = new Rectangle(2004,2836);
            PageSize::$B2 = new Rectangle(1418,2004);
            PageSize::$B3 = new Rectangle(1002,1418);
            PageSize::$B4 = new Rectangle(709,1002);
            PageSize::$B5 = new Rectangle(501,709);
            PageSize::$ARCH_E = new Rectangle(2592,3456);
            PageSize::$ARCH_D = new Rectangle(1728,2592);
            PageSize::$ARCH_C = new Rectangle(1296,1728);
            PageSize::$ARCH_B = new Rectangle(864,1296);
            PageSize::$ARCH_A = new Rectangle(648,864);
            PageSize::$FLSA = new Rectangle(612,936);
            PageSize::$FLSE = new Rectangle(612,936);
            PageSize::$HALFLETTER = new Rectangle(396,612);
            PageSize::$_11X17 = new Rectangle(792,1224);
            PageSize::$LEDGER = new Rectangle(1224,792);
            PageSize::$initialized  = TRUE;
        }
    }

}


?>