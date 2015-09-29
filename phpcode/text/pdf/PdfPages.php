<?PHP
/*
 * $Id: PdfPages.php,v 1.2 2005/11/15 16:44:06 mstaylor Exp $
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
require_once("../DocumentException.php");
require_once("PdfWriter.php");
require_once("PdfIndirectReference.php");
require_once("PdfDictionary.php");
require_once("PdfName.php");
require_once("PdfNumber.php");
require_once("PdfArray.php");

/**
* <CODE>PdfPages</CODE> is the PDF Pages-object.
* <P>
* The Pages of a document are accessible through a tree of nodes known as the Pages tree.
* This tree defines the ordering of the pages in the document.<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 6.3 (page 71-73)
*
* @see		PdfPageElement
* @see		PdfPage
*/

public class PdfPages
{

    private $pages = array();
    private $parents = array();
    private $leafSize = 10;
    private $writer = NULL; //PdfWriter
    private $topParent = NULL; //PdfIndirectReference

    // constructors

    /**
    * Constructs a <CODE>PdfPages</CODE>-object.
    */

    public function __construct(PdfWriter $writer) {
        $this->writer = $writer;
    }

    function addPage(PdfDictionary $page) {
        try {
            if ((count($pages) % $leafSize) == 0)
                array_push($parents, $writer->getPdfIndirectReference());
            $parent = parents[count($parents) - 1];
            $page->put(PdfName::$PARENT, $parent);
            $current = $writer->getCurrentPage();
            $writer->addToBody($page, $current);
            array_push($pages, $current);
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }


     function addPageRef(PdfIndirectReference $pageRef) 
     {
         try {
             if ((count($pages) % $leafSize) == 0)
                 array_push($parents, $writer->getPdfIndirectReference());
             array_push($pages, $pageRef);
             return $parents[count($parents) - 1];
         }
         catch (Exception $e) {
             throw new Exception($e);
         }
    }

    // returns the top parent to include in the catalog
    function writePageTree() {
        if (count($pages) == 0)
            throw new IOException("The document has no pages.");
        $leaf = 1;
        $tParents = parents;
        $tPages = pages;
        $nextParents = array();
        while (TRUE) {
            $leaf *= $leafSize;
            $stdCount = $leafSize;
            $rightCount = count($tPages) % $leafSize;
            if ($rightCount == 0)
                $rightCount = $leafSize;
            for ($p = 0; $p < count($tParents); ++$p) {
                $count = 0;
                $thisLeaf = $leaf;
                if ($p == count($tParents) - 1) {
                    $count = $rightCount;
                    $thisLeaf = count($pages) % $leaf;
                    if ($thisLeaf == 0)
                        $thisLeaf = $leaf;
                }
                else
                    $count = $stdCount;
                $top = new PdfDictionary(PdfName::$PAGES);
                $top->put(PdfName::COUNT, new PdfNumber($thisLeaf));
                $kids = new PdfArray();
                $internal = $kids->getArrayList();
                $arraySublist = array();
                for ($k = $p * $stdCount; $k <= $p * $stdCount + $count; $k++)
                {
                    array_push($arraySublist, $tPages[$k]);
                }
                $internal = array_merge($internal, $arraySublist);
                $top->put(PdfName::$KIDS, $kids);
                if (count($tParents) > 1) {
                    if (($p % $leafSize) == 0)
                        array_push($nextParents, $writer->getPdfIndirectReference());
                    $top->put(PdfName::$PARENT, $nextParents[$p / $leafSize]);
                }
                $writer->addToBody($top, $tParents[$p]);
            }
            if (count($tParents) == 1) {
                $topParent = $tParents[0];
                return $topParent;
            }
            $tPages = $tParents;
            $tParents = $nextParents;
            $nextParents = array();
        }
    }

    function getTopParent() {
        return $topParent;
    }

    function setLinearMode(PdfIndirectReference $topParent) {
        if (count($parents) > 1)
            throw new Exception("Linear page mode can only be called with a single parent.");
        if ($topParent != NULL) {
            $this->topParent = $topParent;
            unset($parents);
            array_push($parents, $topParent);
        }
        $leafSize = 10000000;
    }

    function addPage(PdfIndirectReference $page) {
        array_push($pages, $page);
    }


    int reorderPages(array $order) {
        if ($order == NULL)
            return count($pages);
        if (count($parents) > 1)
            throw new DocumentException("Page reordering requires a single parent in the page tree. Call PdfWriter.setLinearMode() after open.");
        if (count($order) != count($pages))
            throw new DocumentException("Page reordering requires an array with the same size as the number of pages.");
        $max = count($pages);
        $temp = array();
        for ($k = 0; $k < $max; ++$k) {
            $p = $order[$k];
            if ($p < 1 || $p > $max)
                throw new DocumentException("Page reordering requires pages between 1 and " . $max . ". Found " . $p . ".");
            if ($temp[$p - 1])
                throw new DocumentException("Page reordering requires no page repetition. Page " . $p . " is repeated.");
            $temp[$p - 1] = TRUE;
        }
        $copy = $pages;
        for ($k = 0; $k < $max; ++$k) {
            $pages[$k] = $copy[$order[$k] - 1];
        }
        return $max;
    }





}




?>