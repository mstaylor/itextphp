<?PHP
/*
 * $Id: PdfTemplate.php,v 1.3 2005/10/18 20:52:40 mstaylor Exp $
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

require_once("PdfContentByte.php");
require_once("../Rectangle.php");
require_once("PdfTransparencyGroup.php");
require_once("PdfIndirectReference.php");
require_once("PageResources.php");
require_once("PdfArray.php");
require_once("PdfOCG.php");
require_once("PdfWriter.php");
require_once("PdfNumber.php");
require_once("PdfFormXObject.php");
require_once("PdfContentByte.php");
require_once("PdfTemplate.php");

class PdfTemplate extends PdfContentByte 
{
    const TYPE_TEMPLATE = 1;
    const TYPE_IMPORTED = 2;
    const TYPE_PATTERN = 3;
    protected $type;
    /** The indirect reference to this template */
    protected $thisReference;

    /** The resources used by this template */
    protected $pageResources;

    protected $bBox = NULL;
 
    protected $matrix;

    protected $group;

    protected $layer;

    private function initializeClass()
    {
        $bBox = new Rectangle(0,0);
    }

    public function __construct()
    {
        initializeClass();
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                construct0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                construct1arg($arg1);
                break;
            }
        }
    }


    /**
    *Creates a <CODE>PdfTemplate</CODE>.
    */
    private function construct0args()
    {
        parent::__construct(NULL);
        $type = PdfTemplate::TYPE_TEMPLATE;
    }

    /**
    * Creates new PdfTemplate
    *
    * @param wr the <CODE>PdfWriter</CODE>
    */
    private function construct1arg(PdfWriter $wr)
    {
        parent::__construct($wr);
        $type = PdfTemplate::TYPE_TEMPLATE;
        $pageResources = new PageResources();
        $pageResources->addDefaultColor($wr->getDefaultColorspace());
        $thisReference = $writer->getPdfIndirectReference();
    }

    /**
    * Sets the bounding width of this template.
    *
    * @param width the bounding width
    */

    public function setWidth($width) {
        $bBox->setLeft(0);
        $bBox->setRight($width);
    }

    /**
    * Sets the bounding heigth of this template.
    *
    * @param height the bounding height
    */

    public function setHeight($height) {
        $bBox->setBottom(0);
        $bBox->setTop($height);
    }

    /**
    * Gets the bounding width of this template.
    *
    * @return width the bounding width
    */
    public function getWidth() {
        return $bBox->width();
    }

    /**
    * Gets the bounding heigth of this template.
    *
    * @return heigth the bounding height
    */

    public function getHeight() {
        return $bBox->height();
    }

    public Rectangle getBoundingBox() {
        return bBox;
    }

    public function setBoundingBox(Rectangle $bBox) {
        $this->bBox = $bBox;
    }

    /**
    * Sets the layer this template belongs to.
    * @param layer the layer this template belongs to
    */
    public function setLayer(PdfOCG $layer) {
        $this->layer = $layer;
    }

    /**
    * Gets the layer this template bthrows IOException elongs to.
    * @return the layer this template belongs to or <code>null</code> for no layer defined
    */
    public function getLayer() {
        return $layer;
    }

    public void setMatrix($a, $b, $c, $d, $e, $f) {
        $matrix = new PdfArray();
        $matrix->add(new PdfNumber($a));
        $matrix->add(new PdfNumber($b));
        $matrix->add(new PdfNumber($c));
        $matrix->add(new PdfNumber($d));
        $matrix->add(new PdfNumber($e));
        $matrix->add(new PdfNumber($f));
    }

    function getMatrix() {
        return $matrix;
    }

    /**
    * Gets the indirect reference to this template.
    *
    * @return the indirect reference to this template
    */

    function getIndirectReference() {
        return $thisReference;
    }

    public function beginVariableText() {
        $content->append("/Tx BMC ");
    }

    public function endVariableText() {
        $content->append("EMC ");
    }

    /**
    * Constructs the resources used by this template.
    *
    * @return the resources used by this template
    */

    function getResources() {
        return getPageResources()->getResources();
    }

    /**
    * Gets the stream representing this template.
    *
    * @return the stream representing this template
    */

    PdfStream getFormXObject() {
        return new PdfFormXObject($this);
    }

    /**
    * Gets a duplicate of this <CODE>PdfTemplate</CODE>. All
    * the members are copied by reference but the buffer stays different.
    * @return a copy of this <CODE>PdfTemplate</CODE>
    */

    public function getDuplicate() {
        $tpl = new PdfTemplate();
        $tpl->writer = $writer;
        $tpl->pdf = $pdf;
        $tpl->thisReference = $thisReference;
        $tpl->pageResources = $pageResources;
        $tpl->bBox = new Rectangle($bBox);
        $tpl->group = $group;
        $tpl->layer = $layer;
        if ($matrix != NULL) {
            $tpl->matrix = new PdfArray($matrix);
        }
        $tpl->separator = $separator;
        return $tpl;
    }

    public function getType() {
        return $type;
    }

    function getPageResources() {
        return $pageResources;
    }

    /** Getter for property group.
    * @return Value of property group.
    *
    */
    public function getGroup() {
        return $this->group;
    }

    /** Setter for property group.
    * @param group New value of property group.
    *
    */
    public function setGroup(PdfTransparencyGroup $group) {
        $this->group = $group;
    }

}
?>