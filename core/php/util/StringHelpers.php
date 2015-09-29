<?php
/*
 * $Id: StringHelpers.php,v 1.1.1.1 2010/04/27 15:42:39 mstaylor Exp $
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
namespace php\util;
use php\util\StringHelpers as StringHelpers;

class StringHelpers {

    // returns true if $str begins with $sub
    final public static function beginsWith( $str, $sub ) {
        return strncmp($str, $sub, strlen($sub)) == 0;




    }

    // return tru if $str ends with $sub
    final public static function endsWith( $str, $sub ) {
        return StringHelpers::beginsWith(strrev($str),strrev($sub));

    }

    //for tokenizing strings, returns an array of tokenized strings, based on the index of the array
    final public static function nextToken(array &$theArray, $place, $expression) {
        $newString = "";
        for ($i=$place; $i < count($theArray); $i++)
        {
            $newString .= $theArray[$i];
        }
        $theArray = preg_split($expression,$newString);
        return $theArray[0];
    }
}
?>