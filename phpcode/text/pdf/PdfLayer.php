<?PHP
/*
 * $Id: PdfLayer.php,v 1.2 2005/10/25 17:26:00 mstaylor Exp $
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
require_once("PdfIndirectReference.php");
require_once("PdfWriter.php");
require_once("PdfName.php");
require_once("PdfString.php");
require_once("PdfObject.php");
require_once("../../exceptions/NullPointerException.php");
require_once("../../exceptions/IllegalArgumentException.php");
/**
* An optional content group is a dictionary representing a collection of graphics
* that can be made visible or invisible dynamically by users of viewer applications.
* In iText they are referenced as layers.
*
* @author Paulo Soares (psoares@consiste.pt)
*/

class PdfLayer extends PdfDictionary implements PdfOCG
{

    protected $ref = NULL;
    protected $children = array();
    protected $parent;
    protected $title = NULL;// a string

    /**
    * Holds value of property on.
    */
    private $on = TRUE;

    /**
    * Holds value of property onPanel.
    */
    private $onPanel = TRUE;

    public function __construct()
    {

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_string($arg1) == TRUE)
                    construct1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_string($arg1) == TRUE && $arg2 instanceof PdfWriter)
                    construct2args($arg1, $arg2);
                break;
            }
    }


    private function construct1arg($title)
    {
        $this->title = $title;
    }


    /**
    * Creates a new layer.
    * @param name the name of the layer
    * @param writer the writer
    */
    private function construct2args($name, PdfWriter $writer) {
        parent::__construct(PdfName::$OCG);
        setName($name);
        $ref = $writer->getPdfIndirectReference();
        $writer->registerLayer($this);
    }

    /**
    * Creates a title layer. A title layer is not really a layer but a collection of layers
    * under the same title heading.
    * @param title the title text
    * @param writer the <CODE>PdfWriter</CODE>
    * @return the title layer
    */
    public static function createTitle($title, PdfWriter $writer) {
        if ($title == NULL)
            throw new NullPointerException("Title cannot be null.");
        $layer = new PdfLayer($title);
        $writer->registerLayer(layer);
        return $layer;
    }

    function getTitle() {
        return $title;
    }

    /**
    * Adds a child layer. Nested layers can only have one parent.
    * @param child the child layer
    */
    public function addChild(PdfLayer $child) {
        if ($child->parent != NULL)
            throw new IllegalArgumentException("The layer '" . itextphp_string_toPHPString(($child->get(PdfName::NAME))->toUnicodeString()) . "' already has a parent.");
        $child->parent = $this;
        if ($children == NULL)
            $children = array();
        $children->add($child);
    }

    /**
    * Gets the parent layer.
    * @return the parent layer or <CODE>null</CODE> if the layer has no parent
    */
    public function getParent() {
        return $parent;
    }

    /**
    * Gets the children layers.
    * @return the children layers or <CODE>null</CODE> if the layer has no children
    */
    public function getChildren() {
        return $children;
    }

    /**
    * Gets the <CODE>PdfIndirectReference</CODE> that represents this layer.
    * @return the <CODE>PdfIndirectReference</CODE> that represents this layer
    */
    public function getRef() {
        return $ref;
    }

    /**
    * Sets the name of this layer.
    * @param name the name of this layer
    */
    public function setName($name) {
        put(PdfName::$NAME, new PdfString($name, PdfObject::TEXT_UNICODE));
    }

    /**
    * Gets the dictionary representing the layer. It just returns <CODE>this</CODE>.
    * @return the dictionary representing the layer
    */
    public function getPdfObject() {
        return $this;
    }

    /**
    * Gets the initial visibility of the layer.
    * @return the initial visibility of the layer
    */
    public function isOn() {
        return $this->on;
    }

    /**
    * Sets the initial visibility of the layer.
    * @param on the initial visibility of the layer
    */
    public function setOn($on) {
        $this->on = $on;
    }

    private function getUsage() {
        $usage = get(PdfName::$USAGE);
        if ($usage == NULL) {
            $usage = new PdfDictionary();
            put(PdfName::$USAGE, $usage);
        }
        return $usage;
    }

    /**
    * Used by the creating application to store application-specific
    * data associated with this optional content group.
    * @param creator a text string specifying the application that created the group
    * @param subtype a string defining the type of content controlled by the group. Suggested
    * values include but are not limited to <B>Artwork</B>, for graphic-design or publishing
    * applications, and <B>Technical</B>, for technical designs such as building plans or
    * schematics
    */
    public function setCreatorInfo($creator, $subtype) {
        $usage = getUsage();
        $dic = new PdfDictionary();
        $dic->put(PdfName::$CREATOR, new PdfString($creator, PdfObject::TEXT_UNICODE));
        $dic->put(PdfName::$SUBTYPE, new PdfName($subtype));
        $usage->put(PdfName::$CREATORINFO, $dic);
    }

    /**
    * Specifies the language of the content controlled by this
    * optional content group
    * @param lang a language string which specifies a language and possibly a locale
    * (for example, <B>es-MX</B> represents Mexican Spanish)
    * @param preferred used by viewer applications when there is a partial match but no exact
    * match between the system language and the language strings in all usage dictionaries
    */
    public function setLanguage($lang, $preferred) {
        $usage = getUsage();
        $dic = new PdfDictionary();
        $dic.put(PdfName::$LANG, new PdfString($lang, PdfObject::TEXT_UNICODE));
        if ($preferred == TRUE)
            $dic->put(PdfName::$PREFERRED, PdfName::$ON);
        $usage->put(PdfName::$LANGUAGE, $dic);
    }

    /**
    * Specifies the recommended state for content in this
    * group when the document (or part of it) is saved by a viewer application to a format
    * that does not support optional content (for example, an earlier version of
    * PDF or a raster image format).
    * @param export the export state
    */
    public function setExport($export) {
        $usage = getUsage();
        $dic = new PdfDictionary();
        $dic->put(PdfName::$EXPORTSTATE, $export ? PdfName::$ON : PdfName::$OFF);
        $usage->put(PdfName::$EXPORT, $dic);
    }


    /**
    * Specifies a range of magnifications at which the content
    * in this optional content group is best viewed.
    * @param min the minimum recommended magnification factors at which the group
    * should be ON. A negative value will set the default to 0
    * @param max the maximum recommended magnification factor at which the group
    * should be ON. A negative value will set the largest possible magnification supported by the
    * viewer application
    */
    public function setZoom($min, $max) {
        if ($min <= 0 && $max < 0)
            return;
        $usage = getUsage();
        $dic = new PdfDictionary();
        if ($min > 0)
            $dic->put(PdfName::$MIN, new PdfNumber($min));
        if ($max >= 0)
            $dic->put(PdfName::$MAX, new PdfNumber($max));
        $usage->put(PdfName::$ZOOM, $dic);
    }

    /**
    * Specifies that the content in this group is intended for
    * use in printing
    * @param subtype a name specifying the kind of content controlled by the group;
    * for example, <B>Trapping</B>, <B>PrintersMarks</B> and <B>Watermark</B>
    * @param printstate indicates that the group should be
    * set to that state when the document is printed from a viewer application
    */
    public function setPrint($subtype, $printstate) {
        $usage = getUsage();
        $dic = new PdfDictionary();
        $dic->put(PdfName::$SUBTYPE, new PdfName($subtype));
        $dic->put(PdfName::$PRINTSTATE, $printstate ? PdfName::$ON : PdfName::$OFF);
        $usage->put(PdfName::$PRINT, $dic);
    }

    /**
    * Indicates that the group should be set to that state when the
    * document is opened in a viewer application.
    * @param view the view state
    */
    public function setView($view) {
        $usage = getUsage();
        $dic = new PdfDictionary();
        $dic->put(PdfName::$VIEWSTATE, $view ? PdfName::$ON : PdfName::$OFF);
        $usage->put(PdfName::$VIEW, $dic);
    }

    /**
    * Gets the layer visibility in Acrobat's layer panel
    * @return the layer visibility in Acrobat's layer panel
    */
    public function isOnPanel() {
        return $this->onPanel;
    }

    /**
    * Sets the visibility of the layer in Acrobat's layer panel. If <CODE>false</CODE>
    * the layer cannot be directly manipulated by the user. Note that any children layers will
    * also be absent from the panel.
    * @param onPanel the visibility of the layer in Acrobat's layer panel
    */
    public function setOnPanel($onPanel) {
        $this->onPanel = $onPanel;
    }

}






?>