<?php /*
 * $Id: MarkupParser.php,v 1.1.1.1 2005/09/22 16:10:04 mstaylor Exp $
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

require_once("../../util/Properties.php");
require_once("../../util/StringHelpers.php");
require_once("../../awt/Color.php");
require_once("../FontFactory.php");
require_once("MarkupTags.php");


/**
* This class contains several static methods that can be used to parse markup.
*
* @author  blowagie
*/

class MarkupParser
{

    /** Creates new MarkupParser */
    private function __construct() {
    }

    /**
    * This method parses a String with attributes and returns a Properties object.
    *
    * @param   string   a String of this form: 'key1="value1"; key2="value2";... keyN="valueN" '
    * @return  a Properties object
    */

    public static function parseAttributes($string) {
        $result = new Properties();
        if ($string == NULL) return $result;
        $keyValuePairs = preg_split(";", $string);
        $keyValuePair = NULL;
        $key = "";
        $value = "";
        foreach ($keyValuePairs as &$avalue) {
            $keyValuePair = preg_split(":", $avalue);
            if (count($keyValuePair) > 0) $key = trim($keyValuePair[0]);
            else continue;
            if (count($keyValuePair) > 1) $value = trim($keyValuePair[1]);;
            else continue;
            if (beginsWith($value, "\"") == TRUE) $value = substr($value,1);
            if (endsWith($value, "\"") == TRUE) $value = substr($value, 0 , strlen($value)  - 1);
            $result->setProperty($key, $value);
        }
        return $result;
    }

    /**
    * This method parses the value of 'font' attribute and returns a Properties object.
    *
    * @param   string   a String of this form: 'style1 ... styleN size/leading font1 ... fontN'
    * @return  a Properties object
    */

    public static function parseFont($string) {
        $result = new Properties();
        if ($string == NULL) return $result;
        $pos = 0;
        $value = "";
        $string = trim($string);
        while (strlen($string) > 0) {
            $pos = strpos($string, " ", $pos);
            if ($pos == FALSE) {
                $value = $string;
                $string = "";
            }
            else {
                $value = substr($string, 0, $pos);
                $string = trim(substr($string, $pos));
            }
            if (strcasecmp($value, "bold") == 0) {
                $result->setProperty(MarkupTags::CSS_FONTWEIGHT, MarkupTags::CSS_BOLD);
                continue;
            }
            if (strcasecmp($value, "italic") == 0) {
                $result->setProperty(MarkupTags::CSS_FONTSTYLE, MarkupTags::CSS_ITALIC);
                continue;
            }
            if (strcasecmp($value, "oblique") == 0) {
                $result->setProperty(MarkupTags::CSS_FONTSTYLE, MarkupTags::CSS_OBLIQUE);
                continue;
            }
            $f = 0.0;
            if (($f = parseLength($value)) > 0) {
                $result->setProperty(MarkupTags::CSS_FONTSIZE, $f . "pt");
                $p = strpos($value, "/");
                if ($p != FALSE && $p < strlen($value) - 1) {
                    $result->setProperty(MarkupTags::CSS_LINEHEIGHT, substr($value, $p + 1) . "pt");
                }
            }
            if (endsWith($value, ",") == TRUE) {
                $value = substr($value, 0, strlen($value) - 1);
                if (FontFactory::contains($value) == TRUE) {
                    $result->setProperty(MarkupTags::CSS_FONTFAMILY, $value);
                    return result;
                }
            }
            if (strcmp("", $string) == 0 && FontFactory::contains($value) == TRUE) {
                $result->setProperty(MarkupTags::CSS_FONTFAMILY, $value);
            }
        }
        return $result;
    }


    /**
    * Parses a length.
    *
    * @param   string  a length in the form of an optional + or -, followed by a number and a unit.
    * @return  a float
    */

    public static function parseLength($string) {
        $pos = 0;
        $length = strlen($string);
        $ok = TRUE;
        while ($ok && $pos < $length) {
            switch(ord($string[$pos])) {
                case '+':
                case '-':
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                case '.':
                    $pos++;
                    break;
                    default:
                        $ok = FALSE;
            }
        }
        if ($pos == 0) return 0.0;
        if ($pos == $length) return $string;
        $f = substr($string, $pos);
        $string = substr($string, $pos);
        // inches
        if (beginsWith($string, "in") == TRUE) {
            return $f * 72.0;
        }
        // centimeters
        if (beginsWith($string, "cm") == TRUE) {
            return ($f / 2.54) * 72.0;
        }
        // millimeters
        if (beginsWith($string, "mm") == TRUE) {
            return ($f / 25.4) * 72.0;
        }
        // picas
        if (beginsWith($string, "pc") == TRUE) {
            return $f * 12.0;
        }
        // default: we assume the length was measured in points
        return $f;
    }

    /**
    * Converts a <CODE>Color</CODE> into a HTML representation of this <CODE>Color</CODE>.
    *
    * @param	color	the <CODE>Color</CODE> that has to be converted.
    * @return	the HTML representation of this <COLOR>Color</COLOR>
    */

    public static function decodeColor($color) {
        $red = 0;
        $green = 0;
        $blue = 0;
        try {
            $red = intval(substr($color, 1, 3), 16);
            $green = intval(substr($color, 3, 5), 16);
            $blue = intval(substr($color,5), 16);
        }
        catch(Exception $sioobe) {
            // empty on purpose
        }
        return new Color($red, $green, $blue);
    }
}

?>