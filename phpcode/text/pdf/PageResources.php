<?PHP
/*
 * $Id: PageResources.php,v 1.2 2005/10/18 20:52:40 mstaylor Exp $
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

require_once("PdfDictionary.php");
require_once("PdfObject.php")
require_once("PdfReader.php");
require_once("PdfName.php");
require_once("PdfIndirectReference.php");
require_once("PdfResources.php");
require_once("PdfLiteral.php");

class PageResources 
{

    protected $fontDictionary = NULL;
    protected $xObjectDictionary = NULL;
    protected $colorDictionary = NULL;
    protected $patternDictionary = NULL;
    protected $shadingDictionary = NULL;
    protected $extGStateDictionary = NULL;
    protected $LayerDictionary = NULL;
    protected $forbiddenNames = array();
    protected $originalResources = NULL;
    protected $namePtr = array(0);
    protected $usedNames = array();

    public function __construct() {
        $fontDictionary = new PdfDictionary();
        $xObjectDictionary = new PdfDictionary();
        $colorDictionary = new PdfDictionary();
        $patternDictionary = new PdfDictionary();
        $shadingDictionary = new PdfDictionary();
        $extGStateDictionary = new PdfDictionary();
        $LayerDictionary = new PdfDictionary();
    }

    function setOriginalResources(PdfDictionary $resources, array $newNamePtr) {
        if ($newNamePtr != NULL)
            $namePtr = $newNamePtr;
        $originalResources = $resources;
        $forbiddenNames = array();
        $usedNames = array();
        if ($resources == NULL)
            return;
        foreach (array_keys($resources) as &$i) {
            $sub = PdfReader::getPdfObject($resources->get($i));
            if ($sub->isDictionary() == TRUE) {
                $dic = $sub;
                foreach (array_keys($dic) as &$j) {
                    $forbiddenNames[$j] = NULL;
                }
            }
        }
    }

    function translateName(PdfName $name) {
        $translated = $name;
        if ($forbiddenNames != NULL) {
            $translated = $usedNames[$name];
            if ($translated == NULL) {
                while (TRUE) {
                    $translated = new PdfName("Xi" . ($namePtr[0]++));
                    if (array_key_exists($translated, $forbiddenNames) == FALSE)
                        break;
                }
                $usedNames[$name] = $translated;
            }
        }
        return $translated;
    }

    function addFont(PdfName $name, PdfIndirectReference $reference) {
        $name = translateName($name);
        $fontDictionary->put($name, $reference);
        return $name;
    }

    function addXObject(PdfName $name, PdfIndirectReference $reference) {
        $name = translateName($name);
        $xObjectDictionary->put($name, $reference);
        return $name;
    }

    function addColor(PdfName $name, PdfIndirectReference $reference) {
        $name = translateName($name);
        $colorDictionary->put($name, $reference);
        return $name;
    }

    function addDefaultColor(PdfName $name, PdfObject $obj) {
        if ($obj == NULL || $obj->isNull() == TRUE)
            $colorDictionary->remove($name);
        else
            $colorDictionary->put($name, $obj);
    }

    function addDefaultColor(PdfDictionary $dic) {
        $colorDictionary->merge($dic);
    }

    function addDefaultColorDiff(PdfDictionary $dic) {
        $colorDictionary->mergeDifferent($dic);
    }

    function addShading(PdfName $name, PdfIndirectReference $reference) {
        $name = translateName($name);
        $shadingDictionary->put($name, $reference);
        return $name;
    }

    function addPattern(PdfName $name, PdfIndirectReference $reference) {
        $name = translateName($name);
        $patternDictionary->put($name, $reference);
        return $name;
    }

    function addExtGState(PdfName $name, PdfIndirectReference $reference) {
        $name = translateName($name);
        $extGStateDictionary->put($name, $reference);
        return $name;
    }

    function addLayer(PdfName $name, PdfIndirectReference $reference) {
        $name = translateName($name);
        $LayerDictionary->put($name, $reference);
        return $name;
    }

    function getResources() {
        $resources = new PdfResources();
        if ($originalResources != NULL)
            $resources->putAll($originalResources);
        $resources->put(PdfName::$PROCSET, new PdfLiteral("[/PDF /Text /ImageB /ImageC /ImageI]"));
        $resources.add(PdfName::$FONT, $fontDictionary);
        $resources.add(PdfName::$XOBJECT, $xObjectDictionary);
        $resources.add(PdfName::$COLORSPACE, $colorDictionary);
        $resources.add(PdfName::$PATTERN, $patternDictionary);
        $resources.add(PdfName::$SHADING, $shadingDictionary);
        $resources.add(PdfName::$EXTGSTATE, $extGStateDictionary);
        $resources.add(PdfName::$PROPERTIES, $LayerDictionary);
        return $resources;
    }
}




?>