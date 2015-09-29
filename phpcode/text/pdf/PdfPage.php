<?PHP
/*
 * $Id: PdfPage.php,v 1.2 2005/11/23 19:43:39 mstaylor Exp $
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


require_once("PdfRectangle.php");
require_once("PdfDictionary.php");
require_once("PdfName.php");
require_once("PdfNumber.php");
require_once("PdfObject.php");
require_once("PdfIndirectReference.php");




/**
* <CODE>PdfPage</CODE> is the PDF Page-object.
* <P>
* A Page object is a dictionary whose keys describe a single page containing text,
* graphics, and images. A Page onjects is a leaf of the Pages tree.<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 6.4 (page 73-81)
*
* @see		PdfPageElement
* @see		PdfPages
*/


class PdfPage extends PdfDictionary
{


    private static $boxStrings = array("crop", "trim", "art", "bleed");
    private static $boxNames = NULL;
    // membervariables

    /** value of the <B>Rotate</B> key for a page in PORTRAIT */
    public static $PORTRAIT = NULL;

    /** value of the <B>Rotate</B> key for a page in LANDSCAPE */
    public static $LANDSCAPE = NULL;

    /** value of the <B>Rotate</B> key for a page in INVERTEDPORTRAIT */
    public static $INVERTEDPORTRAIT = NULL;

    /**	value of the <B>Rotate</B> key for a page in SEASCAPE */
    public static $EASCAPE = NULL;

    /** value of the <B>MediaBox</B> key */
    protected $mediaBox = NULL;//PdfRectangle 

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfPage::$initialized == FALSE)
        {
            PdfPage::$boxNames = array(PdfName::$CROPBOX, PdfName::$TRIMBOX, PdfName::$ARTBOX, PdfName::$BLEEDBOX);
            PdfPage::$PORTRAIT = new PdfNumber(0);
            PdfPage::$LANDSCAPE = new PdfNumber(90);
            PdfPage::$INVERTEDPORTRAIT = new PdfNumber(180);
            PdfPage::$SEASCAPE = new PdfNumber(270);
            PdfPage::$initialized = TRUE;
        }
    }

    // constructors

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if ($arg1 instanceof PdfRectangle && is_array($arg2) == TRUE && $arg3 instanceof PdfDictionary)
                    construct3args($arg1, $arg2, $arg3);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if ($arg1 instanceof PdfRectangle && is_array($arg2) == TRUE && $arg3 instanceof PdfDictionary && is_integer($arg4) == TRUE)
                    construct4args($arg1, $arg2, $arg3, $arg4);
                break;
            }
        }
    }

    /**
    * Constructs a <CODE>PdfPage</CODE>.
    *
    * @param		mediaBox		a value for the <B>MediaBox</B> key
    * @param		resources		an indirect reference to a <CODE>PdfResources</CODE>-object
    * @param		rotate			a value for the <B>Rotate</B> key
    */

    private function construct4args(PdfRectangle $mediaBox, array $boxSize, PdfDictionary $resources, $rotate) {
        parent::__construct(PdfDictionary::$PAGE);
        $this->mediaBox = $mediaBox;
        put(PdfName::$MEDIABOX, $mediaBox);
        put(PdfName::RESOURCES, $resources);
        if ($rotate != 0) {
            put(PdfName::$ROTATE, new PdfNumber($rotate));
        }
        for ($k = 0; k < count($boxStrings); ++$k) {
            $rect = $boxSize[$boxStrings[$k]);
            if ($rect != NULL)
                put($boxNames[$k], $rect);
        }
    }


    /**
    * Constructs a <CODE>PdfPage</CODE>.
    *
    * @param		mediaBox		a value for the <B>MediaBox</B> key
    * @param		resources		an indirect reference to a <CODE>PdfResources</CODE>-object
    */

    private function construct3args(PdfRectangle $mediaBox, array $boxSize, PdfDictionary $resources) {
        construct4args($mediaBox, $boxSize, $resources, 0);
    }

    /**
    * Checks if this page element is a tree of pages.
    * <P>
    * This method allways returns <CODE>false</CODE>.
    *
    * @return	<CODE>false</CODE> because this is a single page
    */

    public function isParent() {
        return FALSE;
    }

    // methods

    /**
    * Adds an indirect reference pointing to a <CODE>PdfContents</CODE>-object.
    *
    * @param		contents		an indirect reference to a <CODE>PdfContents</CODE>-object
    */

    protected function add(PdfIndirectReference $contents) {
        put(PdfName::$CONTENTS, $contents);
    }

    /**
    * Rotates the mediabox, but not the text in it.
    *
    * @return		a <CODE>PdfRectangle</CODE>
    */

    protected function rotateMediaBox() {
        $this->mediaBox =  $mediaBox->rotate();
        put(PdfName::$MEDIABOX, $this->mediaBox);
        return $this->mediaBox;
    }

    /**
    * Returns the MediaBox of this Page.
    *
    * @return		a <CODE>PdfRectangle</CODE>
    */

    protected function getMediaBox() {
        return $mediaBox;
    }

}


PdfPage::initializeStatics();

?>