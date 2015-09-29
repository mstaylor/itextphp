<?PHP
/*
 * $Id: SequenceList.php,v 1.2 2005/10/12 21:22:58 mstaylor Exp $
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
* This class expands a string into a list of numbers. The main use is to select a
* range of pages.
* <p>
* The general systax is:<br>
* [!][o][odd][e][even]start-end
* <p>
* You can have multiple ranges separated by commas ','. The '!' modifier removes the
* range from what is already selected. The range changes are incremental, that is,
* numbers are added or deleted as the range appears. The start or the end, but not both, can be ommited.
*/

class SequenceList
{

    protected static $COMMA = 1;
    protected static $MINUS = 2;
    protected static $NOT = 3;
    protected static $TEXT = 4;
    protected static $NUMBER = 5;
    protected static $END = 6;
    protected static $EOT = '\uffff';

    private static $FIRST = 0;
    private static $DIGIT = 1;
    private static $OTHER = 2;
    private static $DIGIT2 = 3;
    private static $NOT_OTHER = "-,!0123456789";

    protected $text = "";
    protected $ptr = 0;
    protected $number = 0;
    protected $other = "";

    protected $low;
    protected $high;
    protected $odd = FALSE;
    protected $even = FALSE;
    protected $inverse = FALSE;

    protected function __construct($range) {
        $ptr = 0;
        $text = $range;
    }

    protected function nextChar() {
        while (TRUE) {
            if ($ptr >= strlen($text))
                return SequenceList::$EOT;
            $c = $text[$ptr++];
            if ($c > ' ')
                return $c;
        }
    }

    protected function putBack() {
        --$ptr;
        if ($ptr < 0)
            $ptr = 0;
    }

    protected function getType() {
        $buf = "";
        $state = SequenceList::$FIRST;
        while (TRUE) {
            $c = nextChar();
            if ($c == SequenceList::$EOT) {
                if ($state == SequenceList::$DIGIT) {
                    $number = (integer)$other = $buf;
                    return SequenceList::$NUMBER;
                }
                else if ($state == SequenceList::$OTHER) {
                    $other = strtolower($buf);
                    return SequenceList::$TEXT;
                }
                return SequenceList::$END;
            }
            switch ($state) {
                case SequenceList::$FIRST:
                    switch ($c) {
                        case '!':
                            return SequenceList::$NOT;
                        case '-':
                            return SequenceList::$MINUS;
                        case ',':
                            return SequenceList::$COMMA;
                    }
                    $buf .= $c;
                    if ($c >= '0' && $c <= '9')
                        $state = SequenceList::$DIGIT;
                    else
                        $state = SequenceList::$OTHER;
                    break;
                case SequenceList::$DIGIT:
                    if ($c >= '0' && $c <= '9')
                        $buf .= $c;
                    else {
                        putBack();
                        $number = (integer)$other = buf;
                        return SequenceList::$NUMBER;
                    }
                    break;
                case SequenceList::$OTHER:
                    if (strpos(SequenceList::$NOT_OTHER, $c) < 0)
                        $buf .= $c;
                    else {
                        putBack();
                        $other = strtolower($buf);
                        return SequenceList::$TEXT;
                    }
                    break;
            }
        }
    }

    private function otherProc() {
        if (strcmp($other, "odd") == 0 || strcmp($other, "o") == 0) {
            $odd = TRUE;
            $even = FALSE;
        }
        else if (strcmp($other, "even") == 0 || strcmp($other, "e") == 0) {
            $odd = FALSE;
            $even = TRUE;
        }
    }

    protected function getAttributes() {
        $low = -1;
        $high = -1;
        $odd = $even = $inverse = FALSE;
        $state = SequenceList::$OTHER;
        while (TRUE) {
            $type = getType();
            if ($type == SequenceList::$END || $type == SequenceList::$COMMA) {
                if ($state == SequenceList::$DIGIT)
                    $high = $low;
                return ($type == SequenceList::$END);
            }
            switch ($state) {
                case SequenceList::$OTHER:
                    switch ($type) {
                        case SequenceList::$NOT:
                            $inverse = TRUE;
                            break;
                        case SequenceList::$MINUS:
                            $state = SequenceList::$DIGIT2;
                            break;
                        default:
                            if ($type == SequenceList::$NUMBER) {
                                $low = $number;
                                $state = SequenceList::$DIGIT;
                            }
                            else
                                otherProc();
                            break;
                    }
                    break;
                case SequenceList::$DIGIT:
                    switch ($type) {
                        case SequenceList::$NOT:
                            $inverse = TRUE;
                            $state = SequenceList::$OTHER;
                            $high = $low;
                            break;
                        case SequenceList::$MINUS:
                            $state = SequenceList::$DIGIT2;
                            break;
                        default:
                            $high = $low;
                            $state = SequenceList::$OTHER;
                            otherProc();
                            break;
                    }
                    break;
                case SequenceList::$DIGIT2:
                    switch ($type) {
                        case SequenceList::$NOT:
                            $inverse = TRUE;
                            $state = SequenceList::$OTHER;
                            break;
                        case SequenceList::$MINUS:
                            break;
                        case SequenceList::$NUMBER:
                            $high = $number;
                            $state = SequenceList::$OTHER;
                            break;
                        default:
                            $state = SequenceList::$OTHER;
                            otherProc();
                            break;
                    }
                    break;
            }
        }
    }

    /**
    * Generates a list of numbers from a string.
    * @param ranges the comma separated ranges
    * @param maxNumber the maximum number in the range
    * @return a list with the numbers as <CODE>Integer</CODE>
    */
    public static function expand($ranges, $maxNumber) {
        $parse = new SequenceList($ranges);
        //LinkedList list = new LinkedList();
        $list = array();
        $sair = FALSE;
        while ($sair == FALSE) {
            $sair = $parse->getAttributes();
            if ($parse->low == -1 && $parse->high == -1)
                continue;
            if ($parse->low < 1)
                $parse->low = 1;
            if ($parse->high < 1 || $parse->high > $maxNumber)
                $parse->high = $maxNumber;
            if ($parse->low > $maxNumber)
                $parse->low = $maxNumber;
            
            //System.out.println("low="+parse.low+",high="+parse.high+",odd="+parse.odd+",even="+parse.even+",inverse="+parse.inverse);
            $inc = 1;
            if ($parse->inverse == TRUE) {
                if ($parse->low > $parse->high) {
                    $t = $parse->low;
                    $parse->low = $parse->high;
                    $parse->high = t;
                }
                $currentlocation = 0;
                foreach ($list as &$n) {
                    if ($parse->even && ((integer)$n & 1) == 1)
                        continue;
                    if ($parse->odd && ((integer)$n & 1) == 0)
                        continue;
                    if ((integer)$n >= $parse->low && (integer)$n <= $parse->high)
                        unset($list[$currentlocation]);
                    $currentlocation++;
                }
            }
            else {
                if ($parse->low > $parse->high) {
                    $inc = -1;
                    if ($parse->odd == TRUE || $parse->even == TRUE) {
                        --$inc;
                        if ($parse->even == TRUE)
                            $parse->low &= ~1;
                        else
                            $parse->low -= (($parse->low & 1) == 1 ? 0 : 1);
                    }
                    for ($k = $parse->low; $k >= $parse->high; $k += $inc)
                        array_push($list, $k);
                }
                else {
                    if ($parse->odd == TRUE || $parse->even == TRUE) {
                        ++$inc;
                        if ($parse->odd == TRUE)
                            $parse->low |= 1;
                        else
                            $parse->low += (($parse->low & 1) == 1 ? 1 : 0);
                    }
                    for ($k = $parse->low; $k <= $parse->high; $k += $inc)
                        array_push($list, $k);
                }
            }
//            for (int k = 0; k < list.size(); ++k)
//                System.out.print(((Integer)list.get(k)).intValue() + ",");
//            System.out.println();
        }
        return $list;
    }


}



?>