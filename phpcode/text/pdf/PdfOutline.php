<?PHP
/*
 * $Id: PdfOutline.php,v 1.2 2005/10/19 15:12:34 mstaylor Exp $
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

require_once("../Chunk.php");
require_once("../Paragraph.php");
require_once("../../awt/Color.php");
require_once("../Font.php");
require_once("../../io/OutputStream.php");
require_once("../../exceptions/IOException.php");
require_once("PdfDictionary.php");
require_once("PdfIndirectReference.php");
require_once("PdfDestination.php");
require_once("PdfWriter.php");
require_once("PdfName.php");
require_once("PdfString.php");
require_once("PdfObject.php");
require_once("PdfArray.php");
require_once("PdfNumber.php");

/**
* <CODE>PdfOutline</CODE> is an object that represents a PDF outline entry.
* <P>
* An outline allows a user to access views of a document by name.<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 6.7 (page 104-106)
*
* @see		PdfDictionary
*/
class PdfOutline extends PdfDictionary 
{

    // membervariables

    /** the <CODE>PdfIndirectReference</CODE> of this object */
    private $reference = NULL;

    /** value of the <B>Count</B>-key */
    private $count = 0;

    /** value of the <B>Parent</B>-key */
    private $parent = NULL;

    /** value of the <B>Destination</B>-key */
    private $destination = NULL;

    /** The <CODE>PdfAction</CODE> for this outline.
    */
    private $action = NULL;

    protected $kids = array();

    protected $writer = NULL;

    /** Holds value of property tag. */
    private $tag = "";

    /** Holds value of property open. */
    private $open = NULL;

    /** Holds value of property color. */
    private $color = NULL;

    /** Holds value of property style. */
    private $style = 0;

    // constructors

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                construct1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfAction && is_string($arg3) == TRUE)
                    construct3argsString($arg1, $arg2, $arg3);
                else if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfDestination && is_string($arg3) == TRUE)
                    construct3argsPdfDestinationString($arg1, $arg2, $arg3);
                else if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfAction && $arg3 instanceof PdfString)
                    construct3argsPdfOutlinePdfActionPdfString($arg1, $arg2, $arg3);
                else if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfDestination && $arg3 instanceof PdfString)
                    construct3argsPdfOutlinePdfDestinationPdfString($arg1, $arg2, $arg3);
                else if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfAction && $arg3 instance of Paragraph)
                    construct3argsPdfOutlinePdfActionParagraph($arg1, $arg2, $arg3);
                else if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfDestination && $arg3 instanceof Paragraph)
                    construct3argsPdfOutlinePdfDestinationParagraph($arg1, $arg2, $arg3);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfAction && is_string($arg3) == TRUE && is_bool($arg4) == TRUE)
                    construct4argsStringBoolean($arg1, $arg2, $arg3, $arg4);
                else if ($arg1 instance of PdfOutline && $arg2 instanceof PdfDestination && is_string($arg3) == TRUE && is_bool($arg4) == TRUE)
                    construct4argsPdfDestinationStringBoolean($arg1, $arg2, $arg3, $arg4);
                else if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfAction && $arg3 instanceof PdfString && is_bool($arg4) == TRUE)
                    construct4argsPdfOutlinePdfActionPdfStringBoolean($arg1, $arg2, $arg3, $arg4);
                else if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfDestination && $arg3 instanceof PdfString && is_bool($arg4) == TRUE)
                    construct4argsPdfOutlinePdfDestinationPdfStringBoolean($arg1, $arg2, $arg3, $arg4);
                else if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfAction && $arg3 instanceof Paragraph && is_bool($arg4) == TRUE)
                    construct4argsPdfOutlinePdfActionParagraphBoolean($arg1, $arg2, $arg3, $arg4);
                else if ($arg1 instanceof PdfOutline && $arg2 instanceof PdfDestination && $arg3 instanceof Paragraph && is_bool($arg4) == TRUE)
                    construct4argsPdfOutlinePdfDestinationParagraphBoolean($arg1, $arg2, $arg3, $arg4);
                break;
            }
        }
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for the <CODE>outlines object</CODE>.
    * 
    * @param writer The PdfWriter you are adding the outline to
    */

    private function construct1arg(PdfWriter $writer) {
        parent::__construct(PdfDictionary::$OUTLINES);
        $open = TRUE;
        $parent = NULL;
        $this->writer = $writer;
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>. The open mode is
    * <CODE>true</CODE>.
    *
    * @param parent the parent of this outline item
    * @param action the <CODE>PdfAction</CODE> for this outline item
    * @param title the title of this outline item
    */

    private function construct3argsString(PdfOutline $parent, PdfAction $action, $title) {
        construct4argsStringBoolean($parent, $action, $title, TRUE);
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>.
    *
    * @param parent the parent of this outline item
    * @param action the <CODE>PdfAction</CODE> for this outline item
    * @param title the title of this outline item
    * @param open <CODE>true</CODE> if the children are visible
    */
    private function construct4argsStringBoolean(PdfOutline $parent, PdfAction $action, $title, $open) {
        parent::__construct();
        $this->action = $action;
        initOutline($parent, $title, $open);
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>. The open mode is
    * <CODE>true</CODE>.
    *
    * @param parent the parent of this outline item
    * @param destination the destination for this outline item
    * @param title the title of this outline item
    */

    private function construct3args PdfDestinationString(PdfOutline $parent, PdfDestination $destination, $title) {
        construct4argsPdfDestinationStringBoolean($parent, $destination, $title, TRUE);
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>.
    *
    * @param parent the parent of this outline item
    * @param destination the destination for this outline item
    * @param title the title of this outline item
    * @param open <CODE>true</CODE> if the children are visible
    */
    private function construct4argsPdfDestinationStringBoolean(PdfOutline $parent, PdfDestination $destination, $title, $open) {
        parent::__construct();
        $this->destination = $destination;
        initOutline($parent, $title, $open);
    }



    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>. The open mode is
    * <CODE>true</CODE>.
    *
    * @param parent the parent of this outline item
    * @param action the <CODE>PdfAction</CODE> for this outline item
    * @param title the title of this outline item
    */
    private function construct3argsPdfOutlinePdfActionPdfString(PdfOutline $parent, PdfAction $action, PdfString $title) {
        construct4argsPdfOutlinePdfActionPdfStringBoolean($parent, $action, $title, TRUE);
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>.
    *
    * @param parent the parent of this outline item
    * @param action the <CODE>PdfAction</CODE> for this outline item
    * @param title the title of this outline item
    * @param open <CODE>true</CODE> if the children are visible
    */
    private function construct4argsPdfOutlinePdfActionPdfStringBoolean(PdfOutline $parent, PdfAction $action, PdfString $title, $open) {
        construct4argsStringBoolean($parent, $action, $title->toString(), TRUE);
    }


    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>. The open mode is
    * <CODE>true</CODE>.
    *
    * @param parent the parent of this outline item
    * @param destination the destination for this outline item
    * @param title the title of this outline item
    */
    private function construct3argsPdfOutlinePdfDestinationPdfString(PdfOutline $parent, PdfDestination $destination, PdfString $title) {
        construct4argsPdfOutlinePdfDestinationPdfStringBoolean($parent, $destination, $title, TRUE);
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>.
    *
    * @param parent the parent of this outline item
    * @param destination the destination for this outline item
    * @param title the title of this outline item
    * @param open <CODE>true</CODE> if the children are visible
    */
    private function construct4argsPdfOutlinePdfDestinationPdfStringBoolean(PdfOutline $parent, PdfDestination $destination, PdfString $title, $open) {
        construct4argsPdfDestinationStringBoolean($parent, $destination, $title->toString(), TRUE);
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>. The open mode is
    * <CODE>true</CODE>.
    *
    * @param parent the parent of this outline item
    * @param action the <CODE>PdfAction</CODE> for this outline item
    * @param title the title of this outline item
    */
    private function construct3argsPdfOutlinePdfActionParagraph(PdfOutline $parent, PdfAction $action, Paragraph $title) {
        construct4argsPdfOutlinePdfActionParagraphBoolean($parent, $action, $title, TRUE);
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>.
    *
    * @param parent the parent of this outline item
    * @param action the <CODE>PdfAction</CODE> for this outline item
    * @param title the title of this outline item
    * @param open <CODE>true</CODE> if the children are visible
    */
    private function construct4argsPdfOutlinePdfActionParagraphBoolean(PdfOutline $parent, PdfAction $action, Paragraph $title, $open) {
        parent::__construct();
        $buf = "";
        foreach ($title->getChunks() as &$chunk) {
            $buf .= $chunk->content();
        }
        $this->action = $action;
        initOutline($parent, $buf, $open);
    }

    /**
    * Constructs a <CODE>PdfOutline</CODE>.
    * <P>
    * This is the constructor for an <CODE>outline entry</CODE>. The open mode is
    * <CODE>true</CODE>.
    *
    * @param parent the parent of this outline item
    * @param destination the destination for this outline item
    * @param title the title of this outline item
    */
    private function construct3argsPdfOutlinePdfDestinationParagraph(PdfOutline $parent, PdfDestination $destination, Paragraph $title) {
        construct4argsPdfOutlinePdfDestinationParagraphBoolean($parent, $destination, $title, TRUE);
    }


    private function construct4argsPdfOutlinePdfDestinationParagraphBoolean(PdfOutline $parent, PdfDestination $destination, Paragraph $title, $open) {
        parent::__construct();
        $buf = "";
        foreach ($title->getChunks() as &$chunk) {
            $buf .= $chunk->content();
        }
        $this->destination = $destination;
        initOutline($parent, $buf, $open);
    }

    // methods

    /** Helper for the constructors.
    * @param parent the parent outline
    * @param title the title for this outline
    * @param open <CODE>true</CODE> if the children are visible
    */
    function initOutline(PdfOutline $parent, $title, $open) {
        $this->open = $open;
        $this->parent = $parent;
        $writer = $parent->writer;
        put(PdfName::$TITLE, new PdfString($title, PdfObject::TEXT_UNICODE));
        $parent->addKid($this);
        if ($destination != NULL && $destination->hasPage() == FALSE) // bugfix Finn Bock
            setDestinationPage($writer->getCurrentPage());
    }

    /**
    * Sets the indirect reference of this <CODE>PdfOutline</CODE>.
    *
    * @param reference the <CODE>PdfIndirectReference</CODE> to this outline.
    */

    public function setIndirectReference(PdfIndirectReference $reference) {
        $this->reference = $reference;
    }

    /**
    * Gets the indirect reference of this <CODE>PdfOutline</CODE>.
    *
    * @return		the <CODE>PdfIndirectReference</CODE> to this outline.
    */

    public function indirectReference() {
        return $reference;
    }

    /**
    * Gets the parent of this <CODE>PdfOutline</CODE>.
    *
    * @return		the <CODE>PdfOutline</CODE> that is the parent of this outline.
    */

    public function parent() {
        return $parent;
    }

    /**
    * Set the page of the <CODE>PdfDestination</CODE>-object.
    *
    * @param pageReference indirect reference to the page
    * @return <CODE>true</CODE> if this page was set as the <CODE>PdfDestination</CODE>-page.
    */

    public function setDestinationPage(PdfIndirectReference $pageReference) {
        if ($destination == NULL) {
            return FALSE;
        }
        return $destination->addPage($pageReference);
    }


    /**
    * Gets the destination for this outline.
    * @return the destination
    */
    public function getPdfDestination() {
        return $destination;
    }

    function getCount() {
        return $count;
    }

    function setCount($count) {
        $this->count = $count;
    }

    /**
    * returns the level of this outline.
    *
    * @return		a level
    */

    public function level() {
        if ($parent == NULL) {
            return 0;
        }
        return ($parent->level() + 1);
    }

    /**
    * Returns the PDF representation of this <CODE>PdfOutline</CODE>.
    *
    * @param writer the encryption information
    * @param os
    * @throws IOException
    */

    public function toPdf(PdfWriter $writer, OutputStream $os) {
        if ($color != NULL && $color->equals(Color::black)) {
            put(PdfName::$C, new PdfArray(array($color->getRed()/255.0,$color->getGreen()/255.0,$color->getBlue()/255.0)));
        }
        $flag = 0;
        if (($style & Font::BOLD) != 0)
            $flag |= 2;
        if (($style & Font::ITALIC) != 0)
            $flag |= 1;
        if ($flag != 0)
            put(PdfName::$F, new PdfNumber($flag));
        if ($parent != NULL) {
            put(PdfName::$PARENT, $parent->indirectReference());
        }
        if ($destination != NULL && $destination->hasPage() == TRUE) {
            put(PdfName::$DEST, $destination);
        }
        if ($action != NULL)
            put(PdfName::$A, $action);
        if ($count != 0) {
            put(PdfName::$COUNT, new PdfNumber($count));
        }
        parent::toPdf($writer, $os);
    }

    /**
    * Adds a kid to the outline
    * @param outline
    */
    public function addKid(PdfOutline $outline) {
        array_push($kids, $outline);
    }

    /**
    * Returns the kids of this outline
    * @return an ArrayList with PdfOutlines
    */
    public function getKids() {
        return $kids;
    }

    /**
    * Sets the kids of this outline
    * @param kids
    */
    public function setKids(array $kids) {
        $this->kids = $kids;
    }

    /** Getter for property tag.
    * @return Value of property tag.
    */
    public function getTag() {
        return $tag;
    }

    /** Setter for property tag.
    * @param tag New value of property tag.
    */
    public function setTag($tag) {
        $this->tag = $tag;
    }

    /**
    * Gets the title of this outline
    * @return the title as a String
    */
    public function getTitle() {
        $title = get(PdfName::$TITLE);
        return $title->toString();
    }

    /**
    * Sets the title of this outline
    * @param title
    */
    public function setTitle($title) {
        put(PdfName::$TITLE, new PdfString($title, PdfObject::TEXT_UNICODE));
    }

    /** Getter for property open.
    * @return Value of property open.
    */
    public function isOpen() {
        return $open;
    }

     /** Setter for property open.
     * @param open New value of property open.
     */
    public void setOpen(boolean open) {
        this.open = open;
    }

    /** Getter for property color.
    * @return Value of property color.
    *
    */
    public function getColor() {
        return $this->color;
    }

    /** Setter for property color.
    * @param color New value of property color.
    *
    */
    public function setColor(Color $color) {
        $this->color = $color;
    }

    /** Getter for property style.
    * @return Value of property style.
    *
    */
    public function getStyle() {
        return $this->style;
    }

    /** Setter for property style.
    * @param style New value of property style.
    *
    */
    public function setStyle($style) {
        $this->style = $style;
    }
}


?>