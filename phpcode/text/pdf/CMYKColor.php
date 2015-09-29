<?PHP
/*
 * $Id: CMYKColor.php,v 1.2 2005/10/19 16:19:17 mstaylor Exp $
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

require_once("ExtendedColor.php");

class CMYKColor extends ExtendedColor
{

    $cyan = 0.0;
    $magenta = 0.0;
    $yellow = 0.0;
    $black = 0.0;


    public function __construct()
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
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_integer($arg3) == TRUE && is_integer($arg4) == TRUE)
                    construct4argsInteger($arg1);
                else if(is_float($arg1) == TRUE && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE)
                    construct4argsFloat($arg1);
                break;
            }
        }
    }

    private function construct4argsInteger($intCyan, $intMagenta, $intYellow, $intBlack) {
        construct4argsFloat((float)$intCyan / 255.0, (float)$intMagenta / 255.0, (float)$intYellow / 255.0, (float)$intBlack / 255.0);
    }

    private function construct4argsFloat($floatCyan, $floatMagenta, $floatYellow, $floatBlack) {
        parent::__construct(ExtendedColor::TYPE_CMYK, 1.0 - $floatCyan - $floatBlack, 1.0 - $floatMagenta - $floatBlack, 1.0 - $floatYellow - $floatBlack);
        $cyan = ExtendedColor::normalize($floatCyan);
        $magenta = ExtendedColor::normalize($floatMagenta);
        $yellow = ExtendedColor::normalize($floatYellow);
        $black = ExtendedColor::normalize($floatBlack);
    }

    public function getCyan() {
        return $cyan;
    }

    public function getMagenta() {
        return $magenta;
    }

    public function getYellow() {
        return $yellow;
    }

    public function getBlack() {
        return $black;
    }
}

?>