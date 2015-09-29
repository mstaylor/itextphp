<?PHP
/*
 * $Id: PdfNameTree.php,v 1.2 2005/10/12 21:22:58 mstaylor Exp $
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

require_once("../../exceptions/IOException.php");
require_once("../StringCompare.php");
require_once("PdfWriter.php");
require_once("PdfDictionary.php");
require_once("PdfArray.php");
require_once("PdfIndirectReference.php");
require_once("PdfName.php");
require_once("PdfString.php");

/**
* Creates a name tree.
* @author Paulo Soares (psoares@consiste.pt)
*/
class PdfNameTree {

    private static $leafSize = 64;
    private static $stringCompare = NULL;
    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfNameTree::$initialized == FALSE)
        {
            PdfNameTree::stringCompare = new StringCompare();
            PdfNameTree::$initialized = TRUE;
        }
    }

    /**
    * Creates a name tree.
    * @param items the item of the name tree. The key is a <CODE>String</CODE>
    * and the value is a <CODE>PdfIndirectReference</CODE>. Note that although the
    * keys are strings only the lower byte is used and no check is made for chars
    * with the same lower byte and different upper byte. This will generate a wrong
    * tree name.
    * @param writer the writer
    * @throws IOException on error
    * @return the dictionary with the name tree. This dictionary is the one
    * generally pointed to by the key /Dests
    */
    public static function writeTree(array $items, PdfWriter $writer) {
        if (count($items) == 0)
            return NULL;
        $names = array();
        $names = array_keys($items);
        usort($names,array("StringCompare", "compare"));
        if (count($names) <= PdfNameTree::$leafSize) {
            $dic = new PdfDictionary();
            $ar = new PdfArray();
            for ($k = 0; $k < count($names); ++$k) {
                $ar->add(new PdfString($names[$k], NULL));
                $ar->add($items[$names[$k]]);
            }
            $dic->put(PdfName::$NAMES, $ar);
            return $dic;
        }
        $skip = PdfNameTree::$leafSize;
        $kids = array();
        for ($k = 0; $k < (count($names) + PdfNameTree::$leafSize - 1) / PdfNameTree::$leafSize; $k++)
        {
            $kids[$k] = new PdfIndirectReference();
        }

        for ($k = 0; $k < count($kids); ++$k) {
            $offset = $k * PdfNameTree::$leafSize;
            $end = min($offset + PdfNameTree::$leafSize, count($names));
            $dic = new PdfDictionary();
            $arr = new PdfArray();
            $arr->add(new PdfString($names[$offset], NULL));
            $arr->add(new PdfString($names[$end - 1], NULL));
            $dic->put(PdfName::$LIMITS, $arr);
            $arr = new PdfArray();
            for (; $offset < $end; ++$offset) {
                $arr->add(new PdfString($names[$offset], NULL));
                $arr->add($items[$names[$offset]]);
            }
            $dic->put(PdfName::$NAMES, $arr);
            $kids[$k] = $writer->addToBody($dic)->getIndirectReference();
        }
        $top = count($kids);
        while (TRUE) {
            if ($top <= PdfNameTree::$leafSize) {
                $arr = new PdfArray();
                for ($k = 0; $k < $top; ++$k)
                    $arr->add($kids[$k]);
                $dic = new PdfDictionary();
                $dic->put(PdfName::$KIDS, $arr);
                return $dic;
            }
            $skip *= PdfNameTree::$leafSize;
            $tt = (count($names) + $skip - 1 )/ $skip;
            for ($k = 0; $k < $tt; ++$k) {
                $offset = $k * PdfNameTree::$leafSize;
                $end = min($offset + PdfNameTree::$leafSize, $top);
                $dic = new PdfDictionary();
                $arr = new PdfArray();
                $arr->add(new PdfString($names[$k * $skip], NULL));
                $arr->add(new PdfString($names[min(($k + 1) * $skip, count($names)) - 1], NULL));
                $dic->put(PdfName::$LIMITS, $arr);
                $arr = new PdfArray();
                for (; $offset < $end; ++$offset) {
                    $arr->add($kids[$offset]);
                }
                $dic->put(PdfName::$KIDS, $arr);
                $kids[$k] = $writer->addToBody($dic)->getIndirectReference();
            }
            $top = $tt;
        }
    }

    private static function iterateItems(PdfDictionary $dic, array $items) {
        $nn = PdfReader::getPdfObject($dic->get(PdfName::$NAMES));
        if ($nn != NULL) {
            $arr = $nn->getArrayList();
            for ($k = 0; $k < count($arr); ++$k) {
                $s = PdfReader::getPdfObject($arr[$k++]);
                $items[(string)$s] = $arr[$k];
            }
        }
        else if (($nn = PdfReader::getPdfObject($dic->get(PdfName::$KIDS))) != NULL) {
            $arr = $nn->getArrayList();
            for ($k = 0; $k < count($arr); ++$k) {
                $kid = PdfReader::getPdfObject($arr[$k]);
                iterateItems($kid, $items);
            }
        }
    }

    public static function readTree(PdfDictionary $dic) {
        $items = array();
        if ($dic != NULL)
            iterateItems($dic, $items);
        return $items;
    }

}





?>