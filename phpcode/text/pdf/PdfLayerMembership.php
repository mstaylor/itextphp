<?PHP
/*
 * $Id: PdfLayerMembership.php,v 1.1 2005/09/23 20:34:59 mstaylor Exp $
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
require_once("PdfOCG.php");
require_once("PdfName.php");
require_once("PdfArray.php");
require_once("PdfWriter.php");
require_once("PdfLayer.php");
require_once("PdfObject.php");

/**
* Content typically belongs to a single optional content group,
* and is visible when the group is <B>ON</B> and invisible when it is <B>OFF</B>. To express more
* complex visibility policies, content should not declare itself to belong to an optional
* content group directly, but rather to an optional content membership dictionary
* represented by this class.
*
* @author Paulo Soares (psoares@consiste.pt)
*/



class PdfLayerMembership extends PdfDictionary implements PdfOCG 
{

    /**
    * Visible only if all of the entries are <B>ON</B>.
    */
    public static $ALLON = NULL;
    /**
    * Visible if any of the entries are <B>ON</B>.
    */
    public static $ANYON = NULL;
    /**
    * Visible if any of the entries are <B>OFF</B>.
    */
    public static $ANYOFF = NULL;
    /**
    * Visible only if all of the entries are <B>OFF</B>.
    */
    public static $ALLOFF = NULL;

    $ref = NULL;

    $members = NULL;

    $layers = NULL;

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfLayerMembership::$initialized == FALSE)
        {
            PdfLayerMembership::$ALLON = new PdfName("AllOn");
            PdfLayerMembership::$ANYON = new PdfName("AnyOn");
            PdfLayerMembership::$ALLOFF = new PdfName("AllOff");
            PdfLayerMembership::$ANYOFF = new PdfName("AnyOff");
            PdfLayerMembership::$initialized = TRUE;
        }
    }

    private function initializeClass()
    {
        $members = new PdfArray();
        $layers = array();
    }

    public function __construct()
    {
        initializeClass();
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                construct1arg($arg1);
            }
        }
    }

    /**
    * Creates a new, empty, membership layer.
    * @param writer the writer
    */ 
    private function construct1arg($writer)
    {
        parent::__construct(PdfName::$OCMD);
        put(PdfName::$OCGS, $members);
        $ref = $writer->getPdfIndirectReference();
    }

    /**
    * Gets the <CODE>PdfIndirectReference</CODE> that represents this membership layer.
    * @return the <CODE>PdfIndirectReference</CODE> that represents this layer
    */
    public function getRef() {
        return $ref;
    }

    /**
    * Adds a new member to the layer.
    * @param layer the new member to the layer
    */
    public function addMember(PdfLayer $layer) {
        if ($layers->contains($layer) == FALSE) {
            $members->add($layer->getRef());
            $layers->add($layer);
        }
    }

    /**
    * Gets the member layers.
    * @return the member layers
    */
    public function getLayers() {
        return $layers;
    }

    /**
    * Sets the visibility policy for content belonging to this
    * membership dictionary. Possible values are ALLON, ANYON, ANYOFF and ALLOFF.
    * The default value is ANYON.
    * @param type the visibility policy
    */
    public function setVisibilityPolicy(PdfName $type) {
        put(PdfName::$P, $type);
    }

    /**
    * Gets the dictionary representing the membership layer. It just returns <CODE>this</CODE>.
    * @return the dictionary representing the layer
    */
    public function getPdfObject() {
        return $this;
    }


}



?>