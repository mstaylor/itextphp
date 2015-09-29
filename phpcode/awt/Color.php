<?PHP
/*
 * $Id: Color.php,v 1.5 2005/11/10 18:14:30 mstaylor Exp $
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

require_once("../exceptions/IllegalArgumentException.php");




class Color
{
    $value = 0;
    $falpha = 0.0;
    /** Internal mask for red. */
    const RED_MASK = 255 << 16;

    /** Internal mask for green. */
    const GREEN_MASK = 255 << 8;

    /** Internal mask for blue. */
    const BLUE_MASK = 255;

    /** Internal mask for alpha. */
    const ALPHA_MASK = 255 << 24;

    public static $black = NULL;

    public static $gray = NULL;

    public static $white = NULL;

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(Color::$initialized == FALSE)
        {
            Color::$black = new Color(0x000000, FALSE);
            Color::$gray = new Color(0x808080, FALSE);
            Color::$white = new Color(0xffffff, FALSE);
            Color::$initialized = TRUE;
        }
    }

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 2:
           {
               $arg1 = func_get_arg(0); 
               $arg2 = func_get_arg(1);
               construct2args($arg1, $arg2);
               break;
           }
           case 3:
           {
               $arg1 = func_get_arg(0); 
               $arg2 = func_get_arg(1);
               $arg3 = func_get_arg(2);
               construct3args($arg1, $arg2, $arg3);
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
        }
    }

    private function construct2args($value, $hasalpha)
    {
        if ($hasalpha == TRUE)
            $falpha = (($value & Color::ALPHA_MASK) >> 24) / 255.0;
        else
        {
            $value |= Color::ALPHA_MASK;
            $falpha = 1;
        }
        $this->value = $value;
        //$cs = NULL;
    }

    private function construct3args()
    {
    construct4args($reg,$green,$blue,255);
    }

    private function construct4args($red, $green, $blue, $alpha)
    {
        if (($red & 255) != $red || ($green & 255) != $green || ($blue & 255) != $blue
        || ($alpha & 255) != $alpha)
        {
            throw new IllegalArgumentException("Bad RGB values" . 
                                        " red=0x". bin2hex(pack("c",$red)) .
                                        " green=0x" . bin2hex(pack("c",$green)) .
                                        " blue=0x". bin2hex(pack("c",$blue)) .
                                        " alpha=0x" . bin2hex(pack("c",$alpha))  );
            return;
        }

        $value = ($alpha << 24) | ($red << 16) | ($green << 8) | $blue;
        $falpha = 1;
    }

    /**
    * Returns the RGB value for this color, in the sRGB color space. The blue
    * value will be in bits 0-7, green in 8-15, red in 16-23, and alpha value in
    * 24-31.
    *
    * @return the RGB value for this color
    * @see ColorModel#getRGBdefault()
    * @see #getRed()
    * @see #getGreen()
    * @see #getBlue()
    * @see #getAlpha()
    */
    public function getRGB()
    {
        return $value;
    }

    /**
    * Returns the red value for this color, as an integer in the range 0-255
    * in the sRGB color space.
    *
    * @return the red value for this color
    * @see #getRGB()
    */
    public function getRed()
    {
        // Do not inline getRGB() to value, because of SystemColor.
        return (getRGB() & Color::RED_MASK) >> 16;
    }

    /**
    * Returns the green value for this color, as an integer in the range 0-255
    * in the sRGB color space.
    *
    * @return the green value for this color
    * @see #getRGB()
    */
    public int getGreen()
    {
        // Do not inline getRGB() to value, because of SystemColor.
        return (getRGB() & Color::GREEN_MASK) >> 8;
    }

    /**
    * Returns the blue value for this color, as an integer in the range 0-255
    * in the sRGB color space.
    *
    * @return the blue value for this color
    * @see #getRGB()
    */
    public int getBlue()
    {
        // Do not inline getRGB() to value, because of SystemColor.
        return getRGB() & Color::BLUE_MASK;
    }

    public function equals($obj)
    {
        return $obj instanceof Color && $obj->value == $value;
    }
}


Color::initializeStatics();
?>