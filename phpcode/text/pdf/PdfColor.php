<?PHP
/*
 * $Id: PdfColor.php,v 1.2 2005/10/25 19:13:48 mstaylor Exp $
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
require_once("PdfArray.php");
require_once("PdfNumber.php");

/**
* A <CODE>PdfColor</CODE> defines a Color (it's a <CODE>PdfArray</CODE> containing 3 values).
*
* @see		PdfDictionary
*/



class PdfColor extends PdfArray
{

    // constructors
    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Color)
                    construct1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_integer($arg3) == TRUE)
                    construct3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }


    /**
    * Constructs a new <CODE>PdfColor</CODE>.
    *
    * @param		red			a value between 0 and 255
    * @param		green		a value between 0 and 255
    * @param		blue		a value between 0 and 255
    */

    private function construct3args($red, $green, $blue) {
        parent::__construct(new PdfNumber((double)($red & 0xFF) / 0xFF));
        add(new PdfNumber((double)($green & 0xFF) / 0xFF));
        add(new PdfNumber((double)($blue & 0xFF) / 0xFF));
    }

    private function construct1arg(Color $color) {
        construct3args($color->getRed(), $color->getGreen(), $color->getBlue());
    }



}



?>