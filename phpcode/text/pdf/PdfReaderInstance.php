<?PHP
/*
 * $Id: PdfReaderInstance.php,v 1.2 2005/10/11 20:35:47 mstaylor Exp $
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


require_once("PdfWriter.php");
require_once("PdfLiteral.php");
require_once("PdfNumber.php");
require_once("PdfReader.php");
require_once("RandomAccessFileOrArray.php");
require_once("PdfImportedPage.php");
require_once("../../exceptions/IllegalArgumentException.php");
require_once("PdfObject.php");
require_once("PdfDictionary.php");
require_once("PdfName.php");
require_once("PdfStream.php");
require_once("PRStream.php");
require_once("PdfRectangle.php");
require_once("PdfArray.php");

/**
* Instance of PdfReader in each output document.
*
* @author Paulo Soares (psoares@consiste.pt)
*/

class PdfReaderInstance {
    static $IDENTITYMATRIX = NULL;
    static $ONE = NULL;
    $xrefObj = array();
    $pages = array();
    $myXref = array();
    $reader = NULL;
    $file = NULL;
    $importedPages = array();
    $writer = NULL;
    $visited = array();
    $nextRound = array();
    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfReaderInstance::$initialized == FALSE)
        {
            PdfReaderInstance::$IDENTITYMATRIX = new PdfLiteral("[1 0 0 1 0 0]");
            PdfReaderInstance::$ONE = new PdfNumber(1);
            PdfReaderInstance::$initialized = TRUE;
        }
    }

    public function __construct(PdfReader $reader, PdfWriter $writer, array $xrefObj, array $pages) {
        $this->reader = $reader;
        $this->xrefObj = $xrefObj;
        $this->pages = $pages;
        $this->writer = $writer;
        $file = $reader->getSafeFile();
        $myXref = array();
        array_pad($myXref, count($xrefObj),0);
    }

    function getReader() {
        return $reader;
    }

    function getImportedPage($pageNumber) {
        if ($pageNumber < 1 || $pageNumber > count($pages))
            throw new IllegalArgumentException("Invalid page number");
        $i = $pageNumber;
        $pageT = $importedPages[$i];
        if ($pageT == NULL) {
            $pageT = new PdfImportedPage($this, $writer, $pageNumber);
            $importedPages[$i] = $pageT;
        }
        return $pageT;
    }

    function getNewObjectNumber($number, $generation) {
        if ($myXref[$number] == 0) {
            $myXref[$number] = $writer->getIndirectReferenceNumber();
            array_push($nextRound, $number);
        }
        return $myXref[$number];
    }

    function getReaderFile() {
        return $file;
    }

    function getResources($pageNumber) {
        return PdfReader::getPdfObject(($pages[$pageNumber - 1])->get(PdfName::$RESOURCES));
    }

    function getFormXObject($pageNumber) {
        $page = $pages[$pageNumber - 1];
        $contents = PdfReader::getPdfObject($page->get(PdfName::$CONTENTS));
        $dic = new PdfDictionary();
        $bout = NULL;
        $filters = NULL;
        if ($contents != NULL) {
            if ($contents->isStream() == TRUE)
                $dic->putAll($contents);
            else
                $bout = $reader->getPageContent($pageNumber, $file);
        }
        else
            $bout = itextphp_bytes_create(1);
        $dic->put(PdfName::$RESOURCES, PdfReader::getPdfObject($page->get(PdfName::$RESOURCES)));
        $dic->put(PdfName::$TYPE, PdfName::$XOBJECT);
        $dic->put(PdfName::$SUBTYPE, PdfName::$FORM);
        $impPage = $importedPages[$pageNumber];
        $dic->put(PdfName::$BBOX, new PdfRectangle($impPage->getBoundingBox()));
        $matrix = $impPage->getMatrix();
        if ($matrix == NULL)
            $dic->put(PdfName::$MATRIX, PdfReaderInstance::$IDENTITYMATRIX);
        else
            $dic->put(PdfName::$MATRIX, $matrix);
        $dic->put(PdfName::$FORMTYPE, PdfReaderInstance::$ONE);
        $stream = NULL;
        if ($bout == NULL) {
            $stream = new PRStream($contents, $dic);
        }
        else {
            $stream = new PRStream($reader, $bout);
            $stream->putAll($dic);
        }
        return $stream;
    }

    function writeAllVisited() {
        while (count($nextRound) > 0) {
            $vec = $nextRound;
            $nextRound = array();
            for ($k = 0; $k < count($vec); ++$k) {
                $i = $vec[$k];
                if (array_key_exists($i, $visited) == FALSE) {
                    $visited[$i] = NULL;
                    $n = $i;
                    $writer->addToBody($xrefObj[$n], $myXref[$n]);
                }
            }
        }
    }

     function writeAllPages() {
        try {
            $file->reOpen();
            foreach (array_values($importedPages) as &$ip) {
                $writer->addToBody($ip->getFormXObject(), $ip->getIndirectReference());
            }
            writeAllVisited();
        }
        catch (Exception $e) {
            try {
                $file->close();
                return;
            }
            catch (Exception $e) {
                //Empty on purpose
                return;
            }
        }

         try {
                $file->close();
                return;
            }
            catch (Exception $e) {
                //Empty on purpose
                return;
            }
    }

}


?>