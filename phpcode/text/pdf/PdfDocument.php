<?PHP
/*
 * $Id: PdfDocument.php,v 1.2 2005/11/17 21:47:04 mstaylor Exp $
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

require_once("../../awt/Color.php");
require_once("../../exceptions/IOException.php");
require_once("../Anchor.php");
require_once("../Annotation.php");
require_once("../Cell.php");
require_once("../Chunk.php");
require_once("../DocListener.php");
require_once("../Document.php");
require_once("../DocumentException.php");
require_once("../Element.php");
require_once("../Graphic.php");
require_once("../HeaderFooter.php");
require_once("../Image.php");
require_once("../List.php");
require_once("../ListItem.php");
require_once("../Meta.php");
require_once("../Paragraph.php");
require_once("../Phrase.php");
require_once("../Rectangle.php");
require_once("../Section.php");
require_once("../StringCompare.php");
require_once("../Table.php");
require_once("../Watermark.php");

require_once("PdfDictionary.php");
require_once("PdfName.php");
require_once("PdfString.php");
require_once("PdfObject.php");
require_once("PdfData.php");
require_once("PdfWriter.php");
require_once("PdfIndirectReference.php");
require_once("PdfReader.php");
require_once("PdfAction.php");
require_once("PdfPageLabels.php");
require_once("PdfArray.php");
require_once("TrueTypeFontUnicode.php");
require_once("PdfLine.php");
require_once("PdfContentByte.php");
require_once("PageResources.php");
require_once("PdfAcroForm.php");
require_once("PdfOutline.php");
require_once("PdfTransition.php");
require_once("PdfFormField.php");
require_once("PdfRectangle.php");
require_once("PdfPageEvent.php");
require_once("PdfXConformanceException.php");
require_once("PdfException.php");
require_once("PdfContents.php");
require_once("PdfPTable.php");
require_once("ColumnText.php");
require_once("PdfTable.php");
require_once("PdfCell.php");
require_once("PdfChunk.php");
require_once("PdfFileSpecification.php");
require_once("PdfDestination.php");
require_once("MultiColumnText.php");
require_once("PdfFont.php");
require_once("BaseFont.php");
require_once("PdfAnnotation.php");
require_once("PdfTextArray.php");


/**
* <CODE>PdfDocument</CODE> is the class that is used by <CODE>PdfWriter</CODE>
* to translate a <CODE>Document</CODE> into a PDF with different pages.
* <P>
* A <CODE>PdfDocument</CODE> always listens to a <CODE>Document</CODE>
* and adds the Pdf representation of every <CODE>Element</CODE> that is
* added to the <CODE>Document</CODE>.
*
* @see		com.lowagie.text.Document
* @see		com.lowagie.text.DocListener
* @see		PdfWriter
*/


class PdfDocument extends Document implements DocListener
{
    // membervariables

    /** The characters to be applied the hanging ponctuation. */
    protected static $hangingPunctuation = ".,;:'";

    /** The <CODE>PdfWriter</CODE>. */
    private $writer = NULL;//PdfWriter

    /** some meta information about the Document. */
    private $info = NULL;//PdfInfno

    /** Signals that OnOpenDocument should be called. */
    private $firstPageEvent = TRUE;

    /** Signals that onParagraph is valid. */
    private $isParagraph = TRUE;

    // Horizontal line

    /** The line that is currently being written. */
    private $line = NULL;//PdfLine

    /** This represents the current indentation of the PDF Elements on the left side. */
    private $indentLeft = 0.0;

    /** This represents the current indentation of the PDF Elements on the right side. */
    private $indentRight = 0.0;

    /** This represents the current indentation of the PDF Elements on the left side. */
    private $listIndentLeft = 0.0;

    /** This represents the current alignment of the PDF Elements. */
    private $alignment = Element::ALIGN_LEFT;

    // Vertical lines

    /** This is the PdfContentByte object, containing the text. */
    private $text = NULL;//PdfContentByte

    /** This is the PdfContentByte object, containing the borders and other Graphics. */
    private $graphics = NULL;//PdfContentByte

    /** The lines that are written until now. */
    private $lines = array();

    /** This represents the leading of the lines. */
    private $leading = 0.0;

    /** This is the current height of the document. */
    private $currentHeight = 0.0;

    /** This represents the current indentation of the PDF Elements on the top side. */
    private $indentTop = 0.0;

    /** This represents the current indentation of the PDF Elements on the bottom side. */
    private $indentBottom = 0.0;

    /** This checks if the page is empty. */
    private $pageEmpty = TRUE;

    private $textEmptySize = 0;
    // resources

    /** This is the size of the next page. */
    protected $nextPageSize = NULL;//Rectangle

    /** This is the size of the several boxes of the current Page. */
    protected $thisBoxSize = array();

    /** This is the size of the several boxes that will be used in
    * the next page. */
    protected $boxSize = array();

    /** This are the page resources of the current Page. */
    protected $pageResources = NULL;//PageResources

    // images

    /** This is the image that could not be shown on a previous page. */
    private $imageWait = NULL;//Image

    /** This is the position where the image ends. */
    private $imageEnd = -1.0;

    /** This is the indentation caused by an image on the left. */
    private $imageIndentLeft = 0.0;

    /** This is the indentation caused by an image on the right. */
    private $imageIndentRight = 0.0;

    // annotations and outlines

    /** This is the array containing the references to the annotations. */
    private $annotations = NULL;//array

    /** This is an array containg references to some delayed annotations. */
    private $delayedAnnotations = array();

    /** This is the AcroForm object. */
    protected $acroForm = NULL;//PdfAcroForm

    /** This is the root outline of the document. */
    private $rootOutline = NULL;//PdfOutline

    /** This is the current <CODE>PdfOutline</CODE> in the hierarchy of outlines. */
    private $currentOutline = NULL;//PdfOutline

    /** The current active <CODE>PdfAction</CODE> when processing an <CODE>Anchor</CODE>. */
    private $currentAction = NULL;//PdfAction

    /**
    * Stores the destinations keyed by name. Value is
    * <CODE>Object[]{PdfAction,PdfIndirectReference,PdfDestintion}</CODE>.
    */
    private $localDestinations = array();

    private $documentJavaScript = array();

    /** these are the viewerpreferences of the document */
    private $viewerPreferences = 0;

    private $openActionName = NULL;//string
    private $openActionAction = NULL;//PdfAction
    private $additionalActions = NULL;//PdfDictionary
    private $pageLabels = NULL;//PdfPageLabels

    //add by Jin-Hsia Yang
    private $isNewpage = FALSE;

    private $paraIndent = 0.0;
    //end add by Jin-Hsia Yang

    /** margin in x direction starting from the left. Will be valid in the next page */
    protected $nextMarginLeft = 0.0;

    /** margin in x direction starting from the right. Will be valid in the next page */
    protected $nextMarginRight = 0.0;

    /** margin in y direction starting from the top. Will be valid in the next page */
    protected $nextMarginTop = 0.0;

    /** margin in y direction starting from the bottom. Will be valid in the next page */
    protected $nextMarginBottom = 0.0;

    /** The duration of the page */
    protected $duration=-1; // negative values will indicate no duration

    /** The page transition */
    protected $transition = NULL;//PdfTransition

    protected $pageAA = NULL;//PdfDictionary

    /** Holds value of property strictImageSequence. */
    private $strictImageSequence = FALSE;

    /** Holds the type of the last element, that has been added to the document. */
    private $lastElementType = -1;



    /**
    * Common initialization tidbit called on construction
    **/
    private function onConstruct()
    {
        $info = new PdfInfo();
    }


    // constructors

    /**
    * Constructs a new PDF document.
    * @throws DocumentException on error
    */

    public function __construct() {
        parent::__construct();
        addProducer();
        addCreationDate();
    }

    // listener methods

    /**
    * Adds a <CODE>PdfWriter</CODE> to the <CODE>PdfDocument</CODE>.
    *
    * @param writer the <CODE>PdfWriter</CODE> that writes everything
    *                     what is added to this document to an outputstream.
    * @throws DocumentException on error
    */

    public function addWriter(PdfWriter $writer) {
        if ($this->writer == NULL) {
            $this->writer = $writer;
            $acroForm = new PdfAcroForm($writer);
            return;
        }
        throw new DocumentException("You can only add a writer to a PdfDocument once.");
    }


    /**
    * Sets the pagesize.
    *
    * @param pageSize the new pagesize
    * @return <CODE>true</CODE> if the page size was set
    */

    public function setPageSize(Rectangle $pageSize) {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return FALSE;
        }
        $nextPageSize = new Rectangle($pageSize);
        return TRUE;
    }

    /**
    * Changes the header of this document.
    *
    * @param header the new header
    */

    public function setHeader(HeaderFooter $header) {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return;
        }
        parent::setHeader($header);
    }

    /**
    * Resets the header of this document.
    */

    public function resetHeader() {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return;
        }
        parent::resetHeader();
    }

    /**
    * Changes the footer of this document.
    *
    * @param	footer		the new footer
    */

    public function setFooter(HeaderFooter $footer) {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return;
        }
        parent::setFooter($footer);
    }

    /**
    * Resets the footer of this document.
    */

    public function resetFooter() {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return;
        }
        parent::resetFooter();
    }

    /**
    * Sets the page number to 0.
    */

    public function resetPageCount() {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return;
        }
        parent::resetPageCount();
    }

    /**
    * Sets the page number.
    *
    * @param	pageN		the new page number
    */

    public function setPageCount($pageN) {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return;
        }
        parent::setPageCount($pageN);
    }


    /**
    * Sets the <CODE>Watermark</CODE>.
    *
    * @param watermark the watermark to add
    * @return <CODE>true</CODE> if the element was added, <CODE>false</CODE> if not.
    */

    public function add(Watermark watermark) {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return FALSE;
        }
        $this->watermark = $watermark;
        return TRUE;
    }

    /**
    * Removes the <CODE>Watermark</CODE>.
    */

    public function removeWatermark() {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return;
        }
        $this->watermark = NULL;
    }


    /**
    * Sets the margins.
    *
    * @param	marginLeft		the margin on the left
    * @param	marginRight		the margin on the right
    * @param	marginTop		the margin on the top
    * @param	marginBottom	the margin on the bottom
    * @return	a <CODE>boolean</CODE>
    */

    public function setMargins($marginLeft, $marginRight, $marginTop, $marginBottom) {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return FALSE;
        }
        $nextMarginLeft = $marginLeft;
        $nextMarginRight = $marginRight;
        $nextMarginTop = $marginTop;
        $nextMarginBottom = $marginBottom;
        return TRUE;
    }

    protected function rotateAnnotations() {
        $array = new PdfArray();
        $rotation = $pageSize->getRotation() % 360;
        $currentPage = $writer->getCurrentPageNumber();
        for ($k = 0; $k < count($annotations); ++$k) {
            $dic = $annotations[$k];
            $page = $dic->getPlaceInPage();
            if ($page > $currentPage) {
                array_push($delayedAnnotations, $dic);
                continue;
            }
            if ($dic->isForm() == TRUE) {
                if ($dic->isUsed() == FALSE) {
                    $templates = $dic->getTemplates();
                    if ($templates != NULL)
                        $acroForm->addFieldTemplates($templates);
                }
                $field = $dic;
                if ($field->getParent() == NULL)
                    $acroForm->addDocumentField($field->getIndirectReference());
            }
            if ($dic->isAnnotation() == TRUE) {
                $array->add($dic->getIndirectReference());
                if ($dic->isUsed() == FALSE) {
                    $rect = $dic->get(PdfName::$RECT);
                    switch ($rotation) {
                        case 90:
                            $dic->put(PdfName::$RECT, new PdfRectangle(         $pageSize->top() - $rect->bottom(),                            $rect->left(), $pageSize->top() - $rect->top(),                $rect->right()));
                            break;
                        case 180:
                            $dic->put(PdfName::$RECT, new PdfRectangle(                      $pageSize->right() - $rect->left(),                           $pageSize->top() - $rect->bottom(),                             $pageSize->right() - $rect->right(),                             $pageSize->top() - $rect->top()));
                            break;
                        case 270:
                            $dic->put(PdfName::$RECT, new PdfRectangle(                      $rect->bottom(),  $pageSize->right() - $rect->left(),        $rect->top(), $pageSize->right() - $rect->right()));
                            break;
                    }
                }
            }
            if ($dic->isUsed() == FALSE) {
                $dic->setUsed();
                try {
                    $writer->addToBody($dic, $dic->getIndirectReference());
                }
                catch (IOException $e) {
                    throw new Exception($e);
                }
            }
        }
        return $array;
    }


    /**
    * Makes a new page and sends it to the <CODE>PdfWriter</CODE>.
    *
    * @return a <CODE>boolean</CODE>
    * @throws DocumentException on error
    */

    public function newPage() {
        $lastElementType = -1;
        //add by Jin-Hsia Yang
        $isNewpage = TRUE;
        //end add by Jin-Hsia Yang
        if ($writer->getDirectContent()->size() == 0 && $writer->getDirectContentUnder()->size() == 0 && ($pageEmpty == TRUE || ($writer != NULL && $writer->isPaused() == TRUE))) {
            return FALSE;
        }
        $pageEvent = $writer->getPageEvent();
        if ($pageEvent != NULL)
            $pageEvent->onEndPage($writer, $this);

        //Added to inform any listeners that we are moving to a new page (added by David Freels)
        parent::newPage();

        // the following 2 lines were added by Pelikan Stephan
        $imageIndentLeft = 0;
        $imageIndentRight = 0;

        // we flush the arraylist with recently written lines
        flushLines();
        // we assemble the resources of this pages
        $pageResources->addDefaultColorDiff($writer->getDefaultColorspace());
        $resources = $pageResources->getResources();
        // we make a new page and add it to the document
        if ($writer->getPDFXConformance() != PdfWriter::PDFXNONE) {
            if (array_key_exists("art", $thisBoxSize) == TRUE && array_key_exist("trim", $thisBoxSize) == TRUE)
                throw new PdfXConformanceException("Only one of ArtBox or TrimBox can exist in the page.");
            if (array_key_exists("art", $thisBoxSize) == FALSE && array_key_exists("trim", $thisBoxSize) == FALSE) {
                if (array_key_exists("crop", $thisBoxSize) == TRUE)
                    $thisBoxSize["trim"] = $thisBoxSize["crop"];
                else
                    $thisBoxSize["trim"] = new PdfRectangle($pageSize, $pageSize->getRotation());
            }
        }
        $page = NULL;//PdfPage
        $rotation = $pageSize->getRotation();
        $page = new PdfPage(new PdfRectangle($pageSize, $rotation), $thisBoxSize, $resources, $rotation);
        // we add the transitions
        if ($this->transition != NULL) {
            $page->put(PdfName::$TRANS, $this->transition->getTransitionDictionary());
            $transition = NULL;
        }
        if ($this->duration > 0) {
            $page->put(PdfName::$DUR,new PdfNumber($this->duration));
            $duration = 0;
        }
        // we add the page object additional actions
        if ($pageAA != null) {
            try {
                $page->put(PdfName::$AA, $writer->addToBody($pageAA)->getIndirectReference());
            }
            catch (IOException $ioe) {
                throw new Exception($ioe);
            }
            $pageAA = NULL;
        }
        // we add the annotations
        if (count($annotations) > 0) {
            $array = rotateAnnotations();
            if ($array->size() != 0)
                $page->put(PdfName::$ANNOTS, $array);
        }
        if ($open == FALSE || $close ==TRUE) {
            throw new PdfException("The document isn't open.");
        }
        if ($text->size() > $textEmptySize)
            $text->endText();
        else
            $text = NULL;
        $pageReference = $writer->add($page, new PdfContents($writer->getDirectContentUnder(), $graphics, $text, $writer->getDirectContent(), $pageSize));
        // we initialize the new page
        initPage();

        //add by Jin-Hsia Yang
        $isNewpage = FALSE;
        //end add by Jin-Hsia Yang

        return TRUE;
    }


    // methods to open and close a document

    /**
    * Opens the document.
    * <P>
    * You have to open the document before you can begin to add content
    * to the body of the document.
    */

    public function open() {
        if ($open == FALSE) {
            parent::open();
            $writer->open();
            $rootOutline = new PdfOutline($writer);
            $currentOutline = $rootOutline;
        }
        try {
            initPage();
        }
        catch(DocumentException $de) {
            throw new Exception($de);
        }
    }


    function outlineTree(PdfOutline $outline) {
        $outline->setIndirectReference($writer->getPdfIndirectReference());
        if ($outline->parent() != NULL)
            $outline->put(PdfName::$PARENT, $outline->parent()->indirectReference());
        $kids = $outline->getKids();
        $size = count($kids);
        for ($k = 0; $k < $size; ++$k)
            outlineTree($kids[$k]);
        for ($k = 0; $k < $size; ++$k) {
            if ($k > 0)
                ($kids[$k])->put(PdfName::$PREV, ($kids[$k - 1])->indirectReference());
            if ($k < $size - 1)
                ($kids[$k])->put(PdfName::$NEXT, ($kids[$k + 1])->indirectReference());
        }
        if ($size > 0) {
            $outline->put(PdfName::$FIRST, ($kids[0])->indirectReference());
            $outline->put(PdfName::$LAST, ($kids[$size - 1])->indirectReference());
        }
        for ($k = 0; $k < $size; ++$k) {
            $kid = $kids[$k];
            $writer->addToBody($kid, $kid->indirectReference());
        }
    }

    function writeOutlines() {
        if (count($rootOutline->getKids()) == 0)
            return;
        outlineTree($rootOutline);
        $writer->addToBody($rootOutline, $rootOutline->indirectReference());
    }

    function traverseOutlineCount(PdfOutline $outline) {
        $kids = $outline->getKids();
        $parent = $outline->parent();
        if (count($kids) == 0) {
            if ($parent != NULL) {
                $parent->setCount($parent->getCount() + 1);
            }
        }
        else {
            for ($k = 0; $k < count($kids); ++$k) {
                traverseOutlineCount($kids[$k]);
            }
            if ($parent != NULL) {
                if ($outline->isOpen() == TRUE) {
                    $parent->setCount($outline->getCount() + $parent->getCount() + 1);
                }
                else {
                    $parent->setCount($parent->getCount() + 1);
                    $outline->setCount(-$outline->getCount());
                }
            }
        }
    }


    function calculateOutlineCount() {
        if (count($rootOutline->getKids()) == 0)
            return;
        traverseOutlineCount($rootOutline);
    }


    /**
    * Closes the document.
    * <B>
    * Once all the content has been written in the body, you have to close
    * the body. After that nothing can be written to the body anymore.
    */

    public function close() {
        if ($close == TRUE) {
            return;
        }
        try {
            newPage();
            if ($imageWait != NULL) newPage();
            if (count($annotations) > 0)
                throw new Exception(count($annotations) + " annotations had invalid placement pages.");
            $pageEvent = $writer->getPageEvent();
            if ($pageEvent != NULL)
                $pageEvent->onCloseDocument($writer, $this);
            parent::close();

            $writer->addLocalDestinations($localDestinations);
            calculateOutlineCount();
            writeOutlines();
        }
        catch(Exception $e) {
            throw new Exception($e);
        }

        $writer->close();
    }

    function getPageResources() {
        return $pageResources;
    }

    /** Adds a <CODE>PdfPTable</CODE> to the document.
    * @param ptable the <CODE>PdfPTable</CODE> to be added to the document.
    * @throws DocumentException on error
    */
    function addPTable(PdfPTable $ptable) {
        $ct = new ColumnText($writer->getDirectContent());
        if ($currentHeight > 0) {
            $p = new Paragraph();
            $p->setLeading(0);
            $ct->addElement($p);
        }
        $ct->addElement($ptable);
        $he = $ptable->isHeadersInEvent();
        $ptable->setHeadersInEvent(TRUE);
        $loop = 0;
        while (TRUE) {
            $ct->setSimpleColumn(indentLeft(), indentBottom(), indentRight(), indentTop() - $currentHeight);
            $status = $ct->go();
            if ((status & ColumnText::NO_MORE_TEXT) != 0) {
                $text->moveText(0, $ct->getYLine() - indentTop() + $currentHeight);
                $currentHeight = indentTop() - $ct->getYLine();
                break;
            }
            if (indentTop() - $currentHeight == $ct->getYLine())
                ++$loop;
            else
                $loop = 0;
            if ($loop == 3) {
                add(new Paragraph("ERROR: Infinite table loop"));
                break;
            }
            newPage();
        }
        $ptable->setHeadersInEvent($he);
    }

    /**
    * Gets a PdfTable object
    * (contributed by dperezcar@fcc.es)
    * @param table a high level table object
    * @param supportRowAdditions
    * @return returns a PdfTable object
    * @see PdfWriter#getPdfTable(Table)
    */

    function getPdfTable(Table $table, $supportRowAdditions) {
        return new PdfTable($table, indentLeft(), indentRight(), indentTop() - $currentHeight, $supportRowAdditions);
    }


    /**
    * @see PdfWriter#breakTableIfDoesntFit(PdfTable)
    * (contributed by dperezcar@fcc.es)
    * @param table				Table to add
    * @return true if the table will be broken
    * @throws DocumentException
    */

    function breakTableIfDoesntFit(PdfTable $table) {
        $table->updateRowAdditions();
        // Do we have any full page available?
        if ($table->hasToFitPageTable() == FALSE && $table->bottom() <= $indentBottom) {
            // Then output that page
            add($table, TRUE);
            return TRUE;
        }
        return FALSE;
    }


    public function add()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Element)
                    return add1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if ($arg1 instanceof PdfTable && is_bool($arg2) == TRUE)
                   return add2args($arg1, $arg2);
                break;
            }
        }
    }


    /**
    * Adds a new table to 
    * @param table				Table to add.  Rendered rows will be deleted after processing.
    * @param onlyFirstPage		Render only the first full page
    * @throws DocumentException
    */

    private function add2args(PdfTable $table, $onlyFirstPage){
        // before every table, we flush all lines
        flushLines();

        // initialisation of parameters
        $pagetop = indentTop();
        $oldHeight = $currentHeight;
        $cellDisplacement = 0.0;
        $cell = NULL;//PdfCell
        $cellGraphics = new PdfContentByte($writer);

        $tableHasToFit = $table->hasToFitPageTable() ? $table->bottom() < indentBottom() : FALSE;
        if ($pageEmpty == TRUE)
            $tableHasToFit = FALSE;
        $cellsHaveToFit = $table->hasToFitPageCells();

        // drawing the table
        $cells = $table->getCells();
        $headercells = $table->getHeaderCells();
        // Check if we have removed header cells in a previous call
        if (count($headercells) > 0 && (count($cells) == 0 || $cells[0] != $headercells[0])) {
            $allCells = array();
            $allCells = array_merge($allCells, $headercells);
            $allCells = array_merge($allCells, $cells);
            $cells = $allCells;
        }
        while (count($cells) > 0) {
            // initialisation of some extra parameters;
            $lostTableBottom = 0.0;

            // loop over the cells
            $cellsShown = FALSE;
            $currentGroupNumber = 0;
            $headerChecked = FALSE;
            for ($k = 0; $k < count($cells) && $tableHasToFit == FALSE;$k++) {
                $cell = $cells[$k];
                if( $cellsHaveToFit == TRUE ) {
                    if( $cell->isHeader() == FALSE ) {
                        if ($cell->getGroupNumber() != $currentGroupNumber) {
                            $cellsFit = TRUE;
                            $currentGroupNumber = $cell->getGroupNumber();
                            $cellCount = 0;
                            while ($cell->getGroupNumber() == $currentGroupNumber && $cellsFit == TRUE && ($k+1) < count($cells)) {
                                if ($cell->bottom() < indentBottom()) {
                                    $cellsFit = FALSE;
                                }
                                $k++;
                                $cell = $cells[$k];
                                $cellCount++;
                            }
                            if ($cellsFit == FALSE) {
                                break;
                            }
                            for ($i = $cellCount; $i >= 0; $i--) {
                                $k--;
                                $cell = $cells[$k];
                            }
                       }
                    }
                    else {
                        if( $headerChecked == FALSE ) {
                            $headerChecked = TRUE;
                            $cellsFit = TRUE;
                            $cellCount = 0;
                            $firstTop = cell.top();
                            while ($cell->isHeader() == TRUE && $cellsFit == TRUE && ($k+1) < count($cells)) {
                                if ($firstTop - $cell->bottom(0) > indentTop() - $currentHeight - indentBottom()) {
                                    $cellsFit = FALSE;
                                }
                                $k++;
                                $cell = $cells[$k];
                                $cellCount++;
                            }
                            $currentGroupNumber = $cell->getGroupNumber();
                            while ($cell->getGroupNumber() == $currentGroupNumber && $cellsFit == TRUE && ($k+1) < count($cells)) {
                                if ($firstTop - $cell->bottom(0) > indentTop() - $currentHeight - indentBottom() - 10.0) {
                                    $cellsFit = FALSE;
                                }
                                $k++;
                                $cell = $cells[$k];
                                $cellCount++;
                            }
                            for ($i = $cellCount; $i >= 0; $i--) {
                                $k--;
                                $cell = $cells[$k];
                            }
                            if ($cellsFit == FALSE) {
                                while( $cell->isHeader() == TRUE ) {
                                    unset($cells[$k];
                                    $k++;
                                    $cell = $cells[$k];
                                }
                                break;
                            }
                        }
                    }
                }
                $lines = $cell->getLines($pagetop, indentBottom());
                // if there are lines to add, add them
                if ($lines != NULL && count($lines) > 0) {
                    // we paint the borders of the cells
                    $cellsShown = TRUE;
                    $cellGraphics->rectangle($cell->rectangle($pagetop, indentBottom()));
                    $lostTableBottom = max($cell->bottom(), indentBottom());

                    // we write the text
                    $cellTop = $cell->top($pagetop - $oldHeight);
                    $text->moveText(0, $cellTop);
                    $cellDisplacement = flushLines() - $cellTop;
                    $text->moveText(0, $cellDisplacement);
                    if ($oldHeight + $cellDisplacement > $currentHeight) {
                        $currentHeight = $oldHeight + $cellDisplacement;
                    }
                }
                $images = $cell->getImages($pagetop, indentBottom());
                foreach ($images as &$image) {
                    $cellsShown = TRUE;
                    addImage($graphics,$ image, 0, 0, 0, 0, 0, 0);
                }
                // if a cell is allready added completely, remove it
                if ($cell->mayBeRemoved() == TRUE) {
                    unset($cells[$k]);
                }
            }
            $tableHasToFit = FALSE;
           // we paint the graphics of the table after looping through all the cells
            if ($cellsShown == TRUE) {
                $tablerec = new Rectangle($table);
                $tablerec->setBorder($table->border());
                $tablerec->setBorderWidth($table->borderWidth());
                $tablerec->setBorderColor($table->borderColor());
                $tablerec->setBackgroundColor(table.backgroundColor());
                $tablerec->setGrayFill($table->grayFill());
                $under = $writer->getDirectContentUnder();
                $under->rectangle($tablerec->rectangle(top(), indentBottom()));
                $under->add($cellGraphics);
                // bugfix by Gerald Fehringer: now again add the border for the table
                // since it might have been covered by cell backgrounds
                $tablerec->setGrayFill(0);
                $tablerec->setBackgroundColor(NULL);
                $under->rectangle($tablerec->rectangle(top(), indentBottom()));
                // end bugfix
            }
            $cellGraphics = new PdfContentByte(NULL);
            // if the table continues on the next page
            if (count($cells) > 0) {
                $graphics->setLineWidth($table->borderWidth());
                if ($cellsShown == TRUE && ($table->border() & Rectangle::BOTTOM) == Rectangle::BOTTOM) {
                    // Draw the bottom line

                    // the color is set to the color of the element
                    $tColor = $table->borderColor();
                    if ($tColor != NULL) {
                        $graphics->setColorStroke($tColor);
                    }
                    $graphics->moveTo($table->left(), max($table->bottom(), indentBottom()));
                    $graphics->lineTo($table->right(), max($table->bottom(), indentBottom()));
                    $graphics->stroke();
                    if ($tColor != NULL) {
                        $graphics->resetRGBColorStroke();
                    }
                }

                // old page
                $pageEmpty = FALSE;
                $difference = $lostTableBottom;

                // new page
                newPage();
                // G.F.: if something added in page event i.e. currentHeight > 0
                $heightCorrection = 0;
                $somethingAdded = FALSE;
                if ($currentHeight > 0) {
                    $heightCorrection = 6;
                    $currentHeight += $heightCorrection;
                    $somethingAdded = TRUE;
                    newLine();
                    flushLines();
                    $indentTop = $currentHeight - $leading;
                    $currentHeight = 0;
                }
                else {
                    flushLines();
                }

                // this part repeats the table headers (if any)
                $size = count($headercells);
                if ($size > 0) {
                    // this is the top of the headersection
                    $cell = $headercells[0];
                    $oldTop = $cell->top(0);
                    // loop over all the cells of the table header
                    for ($i = 0; $i < $size; $i++) {
                        $cell = $headercells[$i];
                        // calculation of the new cellpositions
                        $cell->setTop(indentTop() - $oldTop + $cell->top(0));
                        $cell->setBottom(indentTop() - $oldTop + $cell->bottom(0));
                        $pagetop = $cell->bottom();
                        // we paint the borders of the cell
                        $cellGraphics->rectangle($cell->rectangle(indentTop(), indentBottom()));
                        // we write the text of the cell
                        $images = $cell->getImages(indentTop(), indentBottom());
                        foreach ($images as &$image) {
                            $cellsShown = TRUE;
                            addImage($graphics, $image, 0, 0, 0, 0, 0, 0);
                        }
                        $lines = $cell->getLines(indentTop(), indentBottom());
                        $cellTop = $cell->top(indentTop());
                        $text->moveText(0, $cellTop-$heightCorrection);
                        $cellDisplacement = flushLines() - $cellTop+$heightCorrection;
                        $text->moveText(0, $cellDisplacement);
                    }

                    $currentHeight = indentTop() - $pagetop + $table->cellspacing();
                    $text->moveText(0, $pagetop - indentTop() - $currentHeight);
                }
                else {
                    if ($somethingAdded == TRUE) {
                        $pagetop = indentTop();
                        $text->moveText(0, -$table->cellspacing());
                    }
                }
                $oldHeight = $currentHeight - $heightCorrection;

                // calculating the new positions of the table and the cells
                $size = min(count($cells), $table->columns());
                $i = 0;
                while ($i < $size) {
                    $cell = $cells[$i];
                    if ($cell->top(-$table->cellspacing()) > $lostTableBottom) {
                        $newBottom = $pagetop - $difference + $cell->bottom();
                        $neededHeight = $cell->remainingHeight();
                        if ($newBottom > $pagetop - $neededHeight) {
                            $difference += $newBottom - ($pagetop - $neededHeight);
                        }
                    }
                    $i++;
                }
                $size = count($cells);
                $table->setTop(indentTop());
                $table->setBottom($pagetop - $difference + $table->bottom($table->cellspacing()));
                for ($i = 0; $i < $size; $i++) {
                    $cell = $cells[$i];
                    $newTop = $pagetop - $difference + $cell->top(-$table->cellspacing());
                    if ($newTop > indentTop() - $currentHeight) {
                        $newTop = indentTop() - $currentHeight;
                    }
                    $newBottom = $newTop - $cell->height();
                    $cell->setTop($newTop);
                    $cell->setBottom($newBottom);
                }
                if ($onlyFirstPage == TRUE) {
                    break;
                }
            }
        }

        $tableHeight = $table->top() - $table->bottom();
        $currentHeight = $oldHeight + $tableHeight;
        $text->moveText(0, -$tableHeight);
        $pageEmpty = FALSE;
    }


    /**
    * Signals that an <CODE>Element</CODE> was added to the <CODE>Document</CODE>.
    *
    * @param element the element to add
    * @return <CODE>true</CODE> if the element was added, <CODE>false</CODE> if not.
    * @throws DocumentException when a document isn't open yet, or has been closed
    */

    private function add1arg(Element $element) {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return FALSE;
        }
        try {

            switch($element->type()) {

                // Information (headers)
                case Element::HEADER:
                    $info->addkey(($element)->name(), ($element)->content());
                    break;
                case Element::TITLE:
                    $info->addTitle(($element)->content());
                    break;
                case Element::SUBJECT:
                    $info->addSubject(($element)->content());
                    break;
                case Element::KEYWORDS:
                    $info->addKeywords(($element)->content());
                    break;
                case Element::AUTHOR:
                    $info->addAuthor(($element)->content());
                    break;
                case Element::CREATOR:
                    $info->addCreator(($element)->content());
                    break;
                case Element::PRODUCER:
                    // you can not change the name of the producer
                    $info->addProducer();
                    break;
                case Element::CREATIONDATE:
                    // you can not set the creation date, only reset it
                    $info->addCreationDate();
                    break;

                    // content (text)
                case Element::CHUNK: {
                    // if there isn't a current line available, we make one
                    if ($line == NULL) {
                        carriageReturn();
                    }

                    // we cast the element to a chunk
                    $chunk = new PdfChunk($element, $currentAction);
                    // we try to add the chunk to the line, until we succeed
                    {
                        $overflow = NULL;//PdfChunk
                        while (($overflow = $line->add($chunk)) != NULL) {
                            carriageReturn();
                            $chunk = $overflow;
                        }
                    }
                    $pageEmpty = FALSE;
                    if ($chunk->isAttribute(Chunk::NEWPAGE)) {
                        newPage();
                    }
                    break;
                }
                case Element::ANCHOR: {
                    $anchor = $element;
                    $url = $anchor->reference();
                    $leading = $anchor->leading();
                    if ($url != NULL) {
                        $currentAction = new PdfAction($url);
                    }

                    // we process the element
                    $element->process($this);
                    $currentAction = NULL;
                    break;
                }
                case Element::ANNOTATION: {
                    if ($line == NULL) {
                        carriageReturn();
                    }
                    $annot = $element;
                    switch($annot->annotationType()) {
                        case Annotation::URL_NET:
                            $annotations->add(new PdfAnnotation($writer, $annot->llx(), $annot->lly(), $annot->urx(), $annot->ury(), new PdfAction($annot->attributes()[Annotation::URL])));
                            break;
                        case Annotation::URL_AS_STRING:
                            $annotations->add(new PdfAnnotation($writer, $annot->llx(), $annot->lly(), $annot->urx(), $annot->ury(), new PdfAction((string) $annot->attributes()[Annotation::FILE])));
                            break;
                        case Annotation::FILE_DEST:
                            $annotations->add(new PdfAnnotation($writer, $annot->llx(), $annot->lly(), $annot->urx(), $annot->ury(), new PdfAction((string) $annot->attributes()[Annotation::FILE], (string) $annot->attributes()[Annotation::DESTINATION])));
                            break;
                        case Annotation::SCREEN:
                            $sparams = $annot->attributes()[Annotation::PARAMETERS];
                            $fname = (string) $annot->attributes()[Annotation::FILE];
                            $mimetype = (string)$annot->attributes()[Annotation::MIMETYPE];
                            $fs = NULL;//PdfFileSpecification
                            if ($sparams[0] == TRUE)
                                $fs = PdfFileSpecification::fileEmbedded($writer, $fname, $fname, NULL);
                            else
                                $fs = PdfFileSpecification::fileExtern($writer, $fname);
                            $ann = PdfAnnotation::createScreen($writer, new Rectangle($annot->llx(), $annot->lly(), $annot->urx(), $annot->ury()), $fname, $fs, $mimetype, $sparams[1]);
                            $annotations->add($ann);
                            break;
                        case Annotation::FILE_PAGE:
                            $annotations->add(new PdfAnnotation($writer, $annot->llx(), $annot->lly(), $annot->urx(), $annot->ury(), new PdfAction((string)$annot->attributes()[Annotation::FILE], ((integer)$annot->attributes()[Annotation::PAGE])));
                            break;
                        case Annotation::NAMED_DEST:
                            $annotations->add(new PdfAnnotation($writer, $annot->llx(), $annot->lly(), $annot->urx(), $annot->ury(), new PdfAction(((integer)$annot->attributes()[Annotation::NAMED]))));
                            break;
                        case Annotation::LAUNCH:
                            $annotations->add(new PdfAnnotation($writer, $annot->llx(), $annot->lly(), $annot->urx(), $annot->ury(), new PdfAction((string)$annot->attributes()[Annotation::APPLICATION],(string)$annot->attributes()[Annotation::PARAMETERS],(string) $annot->attributes()[Annotation::OPERATION],(string) $annot->attributes()[Annotation::DEFAULTDIR])));
                            break;
                        default:
                            $an = new PdfAnnotation($writer, $annot->llx(indentRight() - $line->widthLeft()), $annot->lly(indentTop() - $currentHeight), $annot->urx(indentRight() - $line->widthLeft() + 20), $annot->ury(indentTop() - $currentHeight - 20), new PdfString($annot->title()), new PdfString($annot->content()));
                            //PdfAnnotation pop = PdfAnnotation.createPopup(writer, new Rectangle(annot.llx(indentRight() - line.widthLeft()), annot.lly(indentTop() - currentHeight), annot.urx(indentRight() - line.widthLeft() + 20), annot.ury(indentTop() - currentHeight - 20)), null, true);
                            //an.setPopup(pop);
                            $annotations->add($an);
                            //annotations.add(pop);
                    }
                    $pageEmpty = FALSE;
                    break;
                }
                case Element::PHRASE: {
                    // we cast the element to a phrase and set the leading of the document
                    $leading = ($element)->leading();
                    // we process the element
                    $element->process($this);
                    break;
                }
                case Element::PARAGRAPH: {
                    // we cast the element to a paragraph
                    $paragraph = $element;

                    $spacingBefore = $paragraph->spacingBefore();
                    if ($spacingBefore != 0) {
                        $leading = $spacingBefore;
                        carriageReturn();
                        if ($pageEmpty == FALSE) {
                            /*
                             * Don't add spacing before a paragraph if it's the first
                             * on the page
                             */
                            $space = new Chunk(" ");
                            $space->process($this);
                            carriageReturn();
                        }
                    }

                    // we adjust the parameters of the document
                    $alignment = $paragraph->alignment();
                    $leading = $paragraph->leading();

                    carriageReturn();
                    // we don't want to make orphans/widows
                    if ($currentHeight + $line->height() + $leading > indentTop() - indentBottom()) {
                        newPage();
                    }

                    // Begin added: Bonf (Marc Schneider) 2003-07-29
                    //carriageReturn();
                    // End added: Bonf (Marc Schneider) 2003-07-29

                    $indentLeft += $paragraph->indentationLeft();
                    $indentRight += $paragraph->indentationRight();

                    // Begin removed: Bonf (Marc Schneider) 2003-07-29
                    carriageReturn();
                    // End removed: Bonf (Marc Schneider) 2003-07-29


                    //add by Jin-Hsia Yang

                    $paraIndent += $paragraph->indentationLeft();
                    //end add by Jin-Hsia Yang

                    $pageEvent = $writer->getPageEvent();
                    if ($pageEvent != NULL && $isParagraph == TRUE)
                        $pageEvent->onParagraph($writer, $this, indentTop() - $currentHeight);

                    // if a paragraph has to be kept together, we wrap it in a table object
                    if ($paragraph->getKeepTogether() == TRUE) {
                        $table = new Table(1, 1);
                        $table->setOffset(0.0);
                        $table->setBorder(Table::NO_BORDER);
                        $table->setWidth(100.0);
                        $table->setTableFitsPage(TRUE);
                        $cell = new Cell($paragraph);
                        $cell->setBorder(Table::NO_BORDER);
                        //patch by Matt Benson 11/01/2002 - 14:32:00
                        $cell->setHorizontalAlignment($paragraph->alignment());
                        //end patch by Matt Benson
                        $table->addCell($cell);
                        $this->add($table);
                        break;
                    }
                    else
                        // we process the paragraph
                        $element->process($this);

                    //add by Jin-Hsia Yang and blowagie
                    $paraIndent -= $paragraph->indentationLeft();
                    //end add by Jin-Hsia Yang and blowagie

                    // Begin removed: Bonf (Marc Schneider) 2003-07-29
                    //       carriageReturn();
                    // End removed: Bonf (Marc Schneider) 2003-07-29

                    $spacingAfter = paragraph.spacingAfter();
                    if ($spacingAfter != 0) {
                        $leading = $spacingAfter;
                        carriageReturn();
                        if ($currentHeight + $line->height() + $leading < indentTop() - indentBottom()) {
                            /*
                            * Only add spacing after a paragraph if the extra
                            * spacing fits on the page.
                            */
                            $space = new Chunk(" ");
                            $space->process($this);
                            carriageReturn();
                        }
                        $leading = $paragraph->leading();      // restore original leading
                    }

                    if ($pageEvent != NULL && $isParagraph == TRUE)
                        $pageEvent->onParagraphEnd($writer, $this, indentTop() - $currentHeight);

                    $alignment = Element::ALIGN_LEFT;
                    $indentLeft -= $paragraph->indentationLeft();
                    $indentRight -= $paragraph->indentationRight();

                    // Begin added: Bonf (Marc Schneider) 2003-07-29
                    carriageReturn();
                    // End added: Bonf (Marc Schneider) 2003-07-29

                    //add by Jin-Hsia Yang

                    //end add by Jin-Hsia Yang

                    break;
                }
                case Element::SECTION:
                case Element::CHAPTER: {
                    // Chapters and Sections only differ in their constructor
                    // so we cast both to a Section
                    $section = $element;

                    $hasTitle = $section->title() != NULL;

                    // if the section is a chapter, we begin a new page
                    if ($section->isChapter() == TRUE) {
                        newPage();
                    }
                    // otherwise, we begin a new line
                    else {
                        newLine();
                    }

                    if ($hasTitle == TRUE) {
                    $fith = indentTop() - $currentHeight;
                    $rotation = $pageSize->getRotation();
                    if ($rotation == 90 || $rotation == 180)
                        $fith = $pageSize->height() - $fith;
                    $destination = new PdfDestination(PdfDestination::FITH, $fith);
                    while ($currentOutline->level() >= $section->depth()) {
                        $currentOutline = $currentOutline->parent();
                    }
                    $outline = new PdfOutline($currentOutline, $destination, $section->getBookmarkTitle(), $section->isBookmarkOpen());
                    $currentOutline = $outline;
                    }

                    // some values are set
                    carriageReturn();
                    $indentLeft += $section->indentationLeft();
                    $indentRight += $section->indentationRight();

                    $pageEvent = $writer->getPageEvent();
                    if ($pageEvent != NULL)
                        if ($element->type() == Element::CHAPTER)
                            $pageEvent->onChapter($writer, $this, indentTop() - $currentHeight, $section->title());
                        else
                            $pageEvent->onSection($writer, $this, indentTop() - $currentHeight, $section->depth(), $section->title());

                    // the title of the section (if any has to be printed)
                    if ($hasTitle == TRUE) {
                        $isParagraph = FALSE;
                        add($section->title());
                        $isParagraph = TRUE;
                    }
                    $indentLeft += $section->indentation();
                    // we process the section
                    $element->process($this);
                    // some parameters are set back to normal again
                    $indentLeft -= $section->indentationLeft() + $section->indentation();
                    $indentRight -= $section->indentationRight();

                    if ($pageEvent != NULL)
                        if ($element->type() == Element::CHAPTER)
                            $pageEvent->onChapterEnd($writer, $this, indentTop() - $currentHeight);
                        else
                            $pageEvent->onSectionEnd($writer, $this, indentTop() - $currentHeight);

                    break;
                }
                case Element::LIST: {
                    // we cast the element to a List
                    $list = $element;
                    // we adjust the document
                    $listIndentLeft += $list->indentationLeft();
                    $indentRight += $list->indentationRight();
                    // we process the items in the list
                    $element->process($this);
                    // some parameters are set back to normal again
                    $listIndentLeft -= $list->indentationLeft();
                    $indentRight -= $list->indentationRight();
                    break;
                }
                case Element::LISTITEM: {
                    // we cast the element to a ListItem
                    $listItem = $element;

                    $spacingBefore = $listItem->spacingBefore();
                    if ($spacingBefore != 0) {
                        $leading = $spacingBefore;
                        carriageReturn();
                        if ($pageEmpty == FALSE) {
                            /*
                            * Don't add spacing before a paragraph if it's the first
                            * on the page
                            */
                            $space = new Chunk(" ");
                            $space->process($this);
                            carriageReturn();
                        }
                    }

                    // we adjust the document
                    $alignment = $listItem->alignment();
                    $listIndentLeft += $listItem->indentationLeft();
                    $indentRight += $listItem->indentationRight();
                    $leading = $listItem->leading();
                    carriageReturn();
                    // we prepare the current line to be able to show us the listsymbol
                    $line->setListItem($listItem);
                    // we process the item
                    $element->process($this);

                    $spacingAfter = $listItem->spacingAfter();
                    if ($spacingAfter != 0) {
                        $leading = $spacingAfter;
                        carriageReturn();
                        if ($currentHeight + $line->height() + $leading < indentTop() - indentBottom()) {
                            /*
                            * Only add spacing after a paragraph if the extra
                            * spacing fits on the page.
                            */
                            $space = new Chunk(" ");
                            $space->process($this);
                            carriageReturn();
                        }
                        $leading = $listItem->leading();      // restore original leading
                    }

                    // if the last line is justified, it should be aligned to the left
                    //                          if (line.hasToBeJustified()) {
                    //                                  line.resetAlignment();
                    //                          }
                    // some parameters are set back to normal again
                    carriageReturn();
                    $listIndentLeft -= $listItem->indentationLeft();
                    $indentRight -= $listItem->indentationRight();
                    break;
                }
                case Element::RECTANGLE: {
                    $rectangle = $element;
                    $graphics->rectangle($rectangle);
                    $pageEmpty = FALSE;
                    break;
                }
                case Element::PTABLE: {
                    $ptable = $element;
                    if ($ptable->size() <= $ptable->getHeaderRows())
                        break; //nothing to do

                    // before every table, we add a new line and flush all lines
                    ensureNewLine();
                    flushLines();
                    addPTable($ptable);
                    $pageEmpty = FALSE;
                    break;
                }
                case Element::MULTI_COLUMN_TEXT: {
                    ensureNewLine();
                    flushLines();
                    $multiText = $element;
                    $height = $multiText->write($writer->getDirectContent(), $this, indentTop() - $currentHeight);
                    $currentHeight += $height;
                    $text->moveText(0, -1.0* $height);
                    $pageEmpty = FALSE;
                    break;
                }
                case Element::TABLE : {

                    /**
                    * This is a list of people who worked on the Table functionality.
                    * To see who did what, please check the CVS repository:
                    *
                    * Leslie Baski
                    * Matt Benson
                    * Francesco De Milato
                    * David Freels
                    * Bruno Lowagie
                    * Veerendra Namineni
                    * Geert Poels
                    * Tom Ring
                    * Paulo Soares
                    * Gerald Fehringer
                    * Steve Appling
                    */

                    $table = NULL;//PdfTable
                    if ($element instanceof PdfTable) {
                        // Already pre-rendered
                        $table = $element;
                        $table->updateRowAdditions();
                    } else if ($element instanceof Table) {
                        // constructing the PdfTable
                        // Before the table, add a blank line using offset or default leading
                        $offset = $element->getOffset();
                        if (is_nan($offset) == TRUE)
                            $offset = $leading;
                        carriageReturn();
                        array_push($lines, new PdfLine(indentLeft(), indentRight(), $alignment, $offset));
                        $currentHeight += $offset;
                        $table = getPdfTable($element, FALSE);
                    } else {
                        return FALSE;
                    }
                    add($table, FALSE);
                    break;
                }
                case Element::JPEG:
                case Element::IMGRAW:
                case Element::IMGTEMPLATE: {
                    //carriageReturn(); suggestion by Marc Campforts
                    add($element);
                    break;
                }
                case Element::GRAPHIC: {
                    $graphic = $element;
                    $graphic->processAttributes(indentLeft(), indentBottom(), indentRight(), indentTop(), indentTop() - $currentHeight);
                    $graphics->add($graphic);
                    $pageEmpty = FALSE;
                    break;
                }
                default:
                    return FALSE;
            }
            $lastElementType = $element->type();
            return TRUE;
        }
        catch(Exception $e) {
            throw new DocumentException($e);
        }
    }



    // methods to add Content

    /**
    * Adds an image to the Graphics object.
    * 
    * @param graphics the PdfContentByte holding the graphics layer of this PdfDocument
    * @param image the image
    * @param a an element of the transformation matrix
    * @param b an element of the transformation matrix
    * @param c an element of the transformation matrix
    * @param d an element of the transformation matrix
    * @param e an element of the transformation matrix
    * @param f an element of the transformation matrix
    * @throws DocumentException
    */

    private function addImage(PdfContentByte $graphics, Image $image, $a, $b, $c, $d, $e, $f) {
        $annotation = $image->annotation();
        if ($image->hasAbsolutePosition() == TRUE) {
            $graphics->addImage($image);
            if ($annotation != NULL) {
                $annotation->setDimensions($image->absoluteX(), $image->absoluteY(), $image->absoluteX() + $image->scaledWidth(), $image->absoluteY() + $image->scaledHeight());
                add($annotation);
            }
        }
        else {
            $graphics->addImage($image, $a, $b, $c, $d, $e, $f);
            if ($annotation != NULL) {
                $annotation->setDimensions($e, $f, $e + $image->scaledWidth(), $f + $image->scaledHeight());
                add($annotation);
            }
        }
    }

    /**
    * Adds an image to the document.
    * @param image the <CODE>Image</CODE> to add
    * @throws PdfException on error
    * @throws DocumentException on error
    */

    private function add(Image $image){

        if ($image->hasAbsolutePosition() == TRUE) {
            addImage($graphics, $image, 0, 0, 0, 0, 0, 0);
            $pageEmpty = FALSE;
            return;
        }

        // if there isn't enough room for the image on this page, save it for the next page
        if ($currentHeight != 0 && indentTop() - $currentHeight - $image->scaledHeight() < indentBottom()) {
            if ($strictImageSequence -- FALSE && $imageWait == NULL) {
                $imageWait = $image;
                return;
            }
            newPage();
            if ($currentHeight != 0 && indentTop() - $currentHeight - $image->scaledHeight() < indentBottom()) {
                $imageWait = $image;
                return;
            }
        }
        $pageEmpty = FALSE;
        // avoid endless loops
        if ($image == $imageWait)
            $imageWait = NULL;
        $textwrap = ($image->alignment() & Image::TEXTWRAP) == Image::TEXTWRAP
        && !(($image->alignment() & Image::MIDDLE) == Image::MIDDLE);
        $underlying = ($image->alignment() & Image::UNDERLYING) == Image::UNDERLYING;
        $diff = $leading / 2;
        if ($textwrap == TRUE) {
            $diff += $leading;
        }
        $lowerleft = indentTop() -$ currentHeight - $image->scaledHeight() -$diff;
        $mt = $image->matrix();
        $startPosition = indentLeft() - $mt[4];
        if (($image->alignment() & Image::RIGHT) == Image::RIGHT) $startPosition = indentRight() - $image->scaledWidth() - $mt[4];
        if (($image->alignment() & Image::MIDDLE) == Image::MIDDLE) $startPosition = indentLeft() + ((indentRight() - indentLeft() - $image->scaledWidth()) / 2) - $mt[4];
        if ($image->hasAbsoluteX()) $startPosition = $image->absoluteX();
        addImage($graphics, $image, $mt[0], $mt[1], $mt[2], $mt[3], $startPosition, $lowerleft - $mt[5]);
        if ($textwrap == TRUE) {
            if ($imageEnd < 0 || $imageEnd < $currentHeight + $image->scaledHeight() + $diff) {
                $imageEnd = $currentHeight + $image->scaledHeight() + $diff;
            }
            if (($image->alignment() & Image::RIGHT) == Image::RIGHT) {
                // indentation suggested by Pelikan Stephan
                $imageIndentRight += $image->scaledWidth() + $image->indentationLeft();
            }
            else {
                // indentation suggested by Pelikan Stephan
                $imageIndentLeft += $image->scaledWidth() + $image->indentationRight();
            }
        }
        if (!($textwrap || $underlying)) {
            $currentHeight += $image->scaledHeight() + $diff;
            flushLines();
            $text->moveText(0, - ($image->scaledHeight() + $diff));
            newLine();
        }
    }

    /**
    * Initializes a page.
    * <P>
    * If the footer/header is set, it is printed.
    * @throws DocumentException on error
    */

    private function initPage() {

        // initialisation of some page objects
        $annotations = $delayedAnnotations;
        $delayedAnnotations = array();
        $pageResources = new PageResources();
        $writer->resetContent();

        // the pagenumber is incremented
        $pageN++;

        // graphics and text are initialized
        $oldleading = $leading;
        $oldAlignment = $alignment;

        if ($marginMirroring && (getPageNumber() & 1) == 0) {
            $marginRight = $nextMarginLeft;
            $marginLeft = $nextMarginRight;
        }
        else {
            $marginLeft = $nextMarginLeft;
            $marginRight = $nextMarginRight;
        }
        $marginTop = $nextMarginTop;
        $marginBottom = $nextMarginBottom;
        $imageEnd = -1;
        $imageIndentRight = 0;
        $imageIndentLeft = 0;
        $graphics = new PdfContentByte($writer);
        $text = new PdfContentByte($writer);
        $text->beginText();
        $text->moveText(left(), top());
        $textEmptySize = $text->size();
        $text->reset();
        $text->beginText();
        $leading = 16;
        $indentBottom = 0;
        $indentTop = 0;
        $currentHeight = 0;

        // backgroundcolors, etc...
        $pageSize = $nextPageSize;
        $thisBoxSize = array();
        if ($pageSize->backgroundColor() != NULL
        || $pageSize->hasBorders() == TRUE
        || $pageSize->borderColor() != NULL
        || $pageSize->grayFill() > 0) {
            add($pageSize);
        }

        // if there is a watermark, the watermark is added
        if ($watermark != NULL) {
            $mt = $watermark->matrix();
            addImage($graphics, $watermark, $mt[0], $mt[1], $mt[2], $mt[3], $watermark->offsetX() - $mt[4], $watermark->offsetY() - $mt[5]);
        }

        // if there is a footer, the footer is added
        if ($footer != NULL) {
            /*
            Added by Edgar Leonardo Prieto Perilla
            */
            // Avoid footer identation
            $tmpIndentLeft = $indentLeft;
            $tmpIndentRight = $indentRight;
            // Begin added: Bonf (Marc Schneider) 2003-07-29
            $tmpListIndentLeft = $listIndentLeft;
            $tmpImageIndentLeft = $imageIndentLeft;
            $tmpImageIndentRight = $imageIndentRight;
            // End added: Bonf (Marc Schneider) 2003-07-29

            $indentLeft = $indentRight = 0;
            // Begin added: Bonf (Marc Schneider) 2003-07-29
            $listIndentLeft = 0;
            $imageIndentLeft = 0;
            $imageIndentRight = 0;
            // End added: Bonf (Marc Schneider) 2003-07-29
            /*
            End Added by Edgar Leonardo Prieto Perilla
            */

            $footer->setPageNumber($pageN);
            $leading = $footer->paragraph()->leading();
            add($footer->paragraph());
            // adding the footer limits the height
            $indentBottom = $currentHeight;
            $text->moveText(left(), indentBottom());
            flushLines();
            $text->moveText(-left(), -bottom());
            $footer->setTop(bottom($currentHeight));
            $footer->setBottom(bottom() - (0.75 * $leading));
            $footer->setLeft(left());
            $footer->setRight(right());
            $graphics->rectangle($footer);
            $indentBottom = $currentHeight + $leading * 2;
            $currentHeight = 0;

            /*
            Added by Edgar Leonardo Prieto Perilla
            */
            $indentLeft = $tmpIndentLeft;
            $indentRight = $tmpIndentRight;
            // Begin added: Bonf (Marc Schneider) 2003-07-29
            $listIndentLeft = $tmpListIndentLeft;
            $imageIndentLeft = $tmpImageIndentLeft;
            $imageIndentRight = $tmpImageIndentRight;
            // End added: Bonf (Marc Schneider) 2003-07-29
            /*
            End Added by Edgar Leonardo Prieto Perilla
            */
        }

        // we move to the left/top position of the page
        $text->moveText(left(), top());

        // if there is a header, the header = added
        if ($header != NULL) {
            /*
            Added by Edgar Leonardo Prieto Perilla
            */
            // Avoid header identation
            $tmpIndentLeft = $indentLeft;
            $tmpIndentRight = $indentRight;
            // Begin added: Bonf (Marc Schneider) 2003-07-29
            $tmpListIndentLeft = $listIndentLeft;
            $tmpImageIndentLeft = $imageIndentLeft;
            $tmpImageIndentRight = $imageIndentRight;
            // End added: Bonf (Marc Schneider) 2003-07-29

            $indentLeft = indentRight = 0;
            //  Added: Bonf
            $listIndentLeft = 0;
            $imageIndentLeft = 0;
            $imageIndentRight = 0;
            // End added: Bonf
            /*
            End Added by Edgar Leonardo Prieto Perilla
            */

            $header->setPageNumber($pageN);
            $leading = $header->paragraph()->leading();
            $text->moveText(0, $leading);
            add($header->paragraph());
            newLine();
            $indentTop = $currentHeight - $leading;
            $header->setTop(top() + $leading);
            $header->setBottom(indentTop() + $leading * 2 / 3);
            $header->setLeft(left());
            $header->setRight(right());
            $graphics->rectangle($header);
            flushLines();
            $currentHeight = 0;

            /*
            Added by Edgar Leonardo Prieto Perilla
            */
            // Restore identation
            $indentLeft = $tmpIndentLeft;
            $indentRight = $tmpIndentRight;
            // Begin added: Bonf (Marc Schneider) 2003-07-29
            $listIndentLeft = $tmpListIndentLeft;
            $imageIndentLeft = $tmpImageIndentLeft;
            $imageIndentRight = $tmpImageIndentRight;
            // End added: Bonf (Marc Schneider) 2003-07-29
            /*
            End Added by Edgar Leonardo Prieto Perilla
            */
        }

        $pageEmpty = TRUE;

        // if there is an image waiting to be drawn, draw it
        try {
            if ($imageWait != NULL) {
                add($imageWait);
                $imageWait = NULL;
            }
        }
        catch(Exception $e) {
            throw new Exception($e);
        }

        $leading = $oldleading;
        $alignment = $oldAlignment;
        $carriageReturn();
        $pageEvent = $writer->getPageEvent();
        if ($pageEvent != NULL) {
            if ($firstPageEvent == TRUE) {
                $pageEvent->onOpenDocument($writer, $this);
            }
            $pageEvent->onStartPage($writer, $this);
        }
        $firstPageEvent = FALSE;
    }


    /**
    * If the current line is not empty or null, it is added to the arraylist
    * of lines and a new empty line is added.
    * @throws DocumentException on error
    */

    private function carriageReturn() {
        // the arraylist with lines may not be null
        if ($lines == NULL) {
            $lines = array();
        }
        // If the current line is not null
        if ($line != NULL) {
            // we check if the end of the page is reached (bugfix by Francois Gravel)
            if ($currentHeight + $line->height() + $leading < indentTop() - indentBottom()) {
                // if so nonempty lines are added and the heigt is augmented
                if ($line->size() > 0) {
                    $currentHeight += $line->height();
                    array_push($lines, $line);
                    $pageEmpty = FALSE;
                }
            }
            // if the end of the line is reached, we start a new page
            else {
                newPage();
            }
        }
        if ($imageEnd > -1 && $currentHeight > $imageEnd) {
            $imageEnd = -1;
            $imageIndentRight = 0;
            $imageIndentLeft = 0;
        }
        // a new current line is constructed
        $line = new PdfLine(indentLeft(), indentRight(), $alignment, $leading);
    }



    /**
    * Adds the current line to the list of lines and also adds an empty line.
    * @throws DocumentException on error
    */

    private function newLine()  {
        $lastElementType = -1;
        carriageReturn();
        if ($lines != NULL && count($lines) > 0) {
            array_push($lines, $line);
            $currentHeight += $line->height();
        }
        $line = new PdfLine(indentLeft(), indentRight(), $alignment, $leading);
    }

    /**
    * Writes all the lines to the text-object.
    *
    * @return the displacement that was caused
    * @throws DocumentException on error
    */

    private function flushLines() {

        // checks if the ArrayList with the lines is not null
        if ($lines == null) {
            return 0;
        }

        //add by Jin-Hsia Yang
        $newline=FALSE;
        //end add by Jin-Hsia Yang

        // checks if a new Line has to be made.
        if ($line != NULL && $line->size() > 0) {
            array_push($lines, $line);
            $line = new PdfLine(indentLeft(), indentRight(), $alignment, $leading);

            //add by Jin-Hsia Yang
            $newline=TRUE;
            //end add by Jin-Hsia Yang

        }

        // checks if the ArrayList with the lines is empty
        if (count($lines) == 0) {
            return 0;
        }

        // initialisation of some parameters
        $currentValues = array();
        $currentFont = NULL;//PdfFont
        $displacement = 0;
        $l = NULL;//PdfLine
        $chunk = NULL;//PdfChunk
        $lastBaseFactor = 0.0;
        $currentValues[1] = $lastBaseFactor;
        // looping over all the lines
        foreach ($lines as &$l) {
            if($isNewpage == TRUE && $newline == TRUE) { // fix Ken@PDI
                $newline=FALSE;
                $text->moveText($l->indentLeft() - indentLeft() + $listIndentLeft + $paraIndent,-$l->height());
            }
            else {
                $text->moveText($l->indentLeft() - indentLeft() + $listIndentLeft, -$l->height());
            }

            // is the line preceeded by a symbol?
            if ($l->listSymbol() != NULL) {
                $chunk = $l->listSymbol();
                $text->moveText(- $l->listIndent(), 0);
                if ($chunk->font()->compareTo($currentFont) != 0) {
                    $currentFont = $chunk->font();
                    $text->setFontAndSize($currentFont->getFont(), $currentFont->size());
                }
                if ($chunk->color() != NULL) {
                    $color = $chunk->color();
                    $text->setColorFill(color);
                    $text->showText($chunk->toString());
                    $text->resetRGBColorFill();
                }
                else if ($chunk->isImage() == TRUE) {
                    $image = $chunk->getImage();
                    $matrix = $image->matrix();
                    $xMarker = $text->getXTLM();
                    $yMarker = $text->getYTLM();
                    $matrix[Image::CX] = $xMarker + $chunk->getImageOffsetX() - $matrix[Image::CX];
                    $matrix[Image::CY] = $yMarker + $chunk->getImageOffsetY() - $matrix[Image::CY];
                    addImage($graphics, $image, $matrix[0], $matrix[1], $matrix[2], $matrix[3], $matrix[4], $matrix[5]);
                }
                else {
                    $text->showText($chunk->toString());
                }
                $text->moveText($l->listIndent(), 0);
            }

            $currentValues[0] = $currentFont;

            writeLineToContent($l, $text, $graphics, $currentValues, $writer->getSpaceCharRatio());

            $currentFont = $currentValues[0];

            $displacement += $l->height();
            if (indentLeft() - $listIndentLeft != $l->indentLeft()) {
                $text->moveText(indentLeft() - $l->indentLeft() - $listIndentLeft, 0);
            }

        }
        $lines = array();
        return $displacement;
    }

    // methods to retrieve information

    /**
    * Gets the <CODE>PdfInfo</CODE>-object.
    *
    * @return	<CODE>PdfInfo</COPE>
    */

    function getInfo() {
        return $info;
    }

    /**
    * Gets the <CODE>PdfCatalog</CODE>-object.
    *
    * @param pages an indirect reference to this document pages
    * @return <CODE>PdfCatalog</CODE>
    */

    function getCatalog(PdfIndirectReference $pages) {
        $catalog = NULL;//PdfCatalog
        if (count($rootOutline->getKids()) > 0) {
            $catalog = new PdfCatalog($pages, $rootOutline->indirectReference(), $writer);
        }
        else
            $catalog = new PdfCatalog($pages, $writer);
        if ($openActionName != NULL) {
            $action = getLocalGotoAction($openActionName);
            $catalog->setOpenAction($action);
        }
        else if ($openActionAction != NULL)
            $catalog->setOpenAction($openActionAction);

        if ($additionalActions != NULL)   {
            $catalog->setAdditionalActions($additionalActions);
        }

        if ($pageLabels != NULL)
            $catalog->setPageLabels($pageLabels);
        $catalog->addNames($localDestinations, $documentJavaScript, $writer);
        $catalog->setViewerPreferences($viewerPreferences);
        if ($acroForm->isValid() == TRUE) {
            try {
                $catalog->setAcroForm($writer->addToBody($acroForm)->getIndirectReference());
            }
            catch (IOException $e) {
                throw new Exception($e);
            }
        }
        return $catalog;
    }

    // methods concerning the layout

    /**
    * Returns the bottomvalue of a <CODE>Table</CODE> if it were added to this document.
    *
    * @param	table	the table that may or may not be added to this document
    * @return	a bottom value
    */

    function bottom(Table $table) {
        // where will the table begin?
        $h = ($currentHeight > 0) ? indentTop() - $currentHeight - 2.0 * $leading : indentTop();
        // constructing a PdfTable
        $tmp = getPdfTable($table, FALSE);
        return $tmp->bottom();
    }

    /**
    * Checks if a <CODE>PdfPTable</CODE> fits the current page of the <CODE>PdfDocument</CODE>.
    *
    * @param	table	the table that has to be checked
    * @param	margin	a certain margin
    * @return	<CODE>true</CODE> if the <CODE>PdfPTable</CODE> fits the page, <CODE>false</CODE> otherwise.
    */

    function fitsPage(PdfPTable $table, $margin) {
        if ($table->isLockedWidth() == FALSE) {
            $totalWidth = (indentRight() - indentLeft()) * $table->getWidthPercentage() / 100;
            $table->setTotalWidth($totalWidth);
        }
        // ensuring that a new line has been started.
        ensureNewLine();
        return $table->getTotalHeight() <= indentTop() - $currentHeight - indentBottom() - $margin;
    }

    /**
    * Gets the current vertical page position.
    * @param ensureNewLine Tells whether a new line shall be enforced. This may cause side effects 
    *   for elements that do not terminate the lines they've started because those lines will get
    *   terminated. 
    * @return The current vertical page position.
    */
    public function getVerticalPosition($ensureNewLine) {
        // ensuring that a new line has been started.
        if ($ensureNewLine == TRUE) {
          ensureNewLine();
        }
        return top() -  $currentHeight - $indentTop;
    }

    /**
    * Ensures that a new line has been started. 
    */
    private function ensureNewLine() {
        try {
            if (($lastElementType == Element::PHRASE) || ($lastElementType == Element::CHUNK)) {
            newLine();
            flushLines();
            }
        } catch (DocumentException $ex) {
            throw new Exception($ex);
        }
    }

    /**
    * Gets the indentation on the left side.
    *
    * @return	a margin
    */

    private function indentLeft() {
        return left($indentLeft + $listIndentLeft + $imageIndentLeft);
    }

    /**
    * Gets the indentation on the right side.
    *
    * @return	a margin
    */

    private function indentRight() {
        return right($indentRight + $imageIndentRight);
    }

    /**
    * Gets the indentation on the top side.
    *
    * @return	a margin
    */

    private function indentTop() {
        return top($indentTop);
    }

    /**
    * Gets the indentation on the bottom side.
    *
    * @return	a margin
    */

    function indentBottom() {
        return bottom($indentBottom);
    }

    /**
    * Adds a named outline to the document .
    * @param outline the outline to be added
    * @param name the name of this local destination
    */
    function addOutline(PdfOutline $outline, $name) {
        localDestination($name, $outline->getPdfDestination());
    }

    /**
    * Gets the AcroForm object.
    * @return the PdfAcroform object of the PdfDocument
    */

    public function getAcroForm() {
        return $acroForm;
    }

    /**
    * Gets the root outline. All the outlines must be created with a parent.
    * The first level is created with this outline.
    * @return the root outline
    */
    public function getRootOutline() {
        return $rootOutline;
    }


    /**
    * Writes a text line to the document. It takes care of all the attributes.
    * <P>
    * Before entering the line position must have been established and the
    * <CODE>text</CODE> argument must be in text object scope (<CODE>beginText()</CODE>).
    * @param line the line to be written
    * @param text the <CODE>PdfContentByte</CODE> where the text will be written to
    * @param graphics the <CODE>PdfContentByte</CODE> where the graphics will be written to
    * @param currentValues the current font and extra spacing values
    * @param ratio
    * @throws DocumentException on error
    */
    function writeLineToContent(PdfLine $line, PdfContentByte $text, PdfContentByte $graphics, array $currentValues, $ratio)  {
        $currentFont = $currentValues[0];
        $lastBaseFactor = $currentValues[1];
        $chunk = NULL;//PdfChunk
        $numberOfSpaces = 0;
        $lineLen = 0;
        $isJustified = FALSE;
        $hangingCorrection = 0.0;
        $hScale = 1.0;
        $lastHScale = 0.0/0.0;
        $baseWordSpacing = 0;
        $baseCharacterSpacing = 0;

        $numberOfSpaces = $line->numberOfSpaces();
        $lineLen = strlen($line->toString());
        // does the line need to be justified?
        $isJustified = $line->hasToBeJustified() && ($numberOfSpaces != 0 || $lineLen > 1);
        if ($isJustified == TRUE) {
            if ($line->isNewlineSplit() && $line->widthLeft() >= ($lastBaseFactor * ($ratio * $numberOfSpaces + $lineLen - 1))) {
                if ($line->isRTL() == TRUE) {
                    $text->moveText($line->widthLeft() - $lastBaseFactor * ($ratio * $numberOfSpaces + $lineLen - 1), 0);
                }
                $baseWordSpacing = $ratio * $lastBaseFactor;
                $baseCharacterSpacing = $lastBaseFactor;
            }
            else {
                $width = $line->widthLeft();
                $last = $line->getChunk($line->size() - 1);
                if ($last != NULL) {
                    $s = $last->toString();
                    $c = NULL;//a char
                    if (strlen($s) > 0 && strpos(PdfDocument::$hangingPunctuation, $s[strlen($s) - 1]) >= 0) {
                        $oldWidth = $width;
                        $width += $last->font()->width(ord($s[strlen($s) - 1])) * 0.4;
                        $hangingCorrection = $width - $oldWidth;
                    }
                }
                $baseFactor = $width / ($ratio * $numberOfSpaces + $lineLen - 1);
                $baseWordSpacing = $ratio * $baseFactor;
                $baseCharacterSpacing = $baseFactor;
                $lastBaseFactor = $baseFactor;
            }
        }

        $lastChunkStroke = $line->getLastStrokeChunk();
        $chunkStrokeIdx = 0;
        $xMarker = $text->getXTLM();
        $baseXMarker = $xMarker;
        $yMarker = $text->getYTLM();
        $adjustMatrix = FALSE;

        // looping over all the chunks in 1 line
        foreach ($line->iterator() as &$chunk) {
            $color = $chunk->color();
            $hScale = 1;

            if ($chunkStrokeIdx <= $lastChunkStroke) {
                $width = 0.0;
                if ($isJustified == TRUE) {
                    $width = $chunk->getWidthCorrected($baseCharacterSpacing, $baseWordSpacing);
                }
                else
                    $width = $chunk->width();
                if ($chunk->isStroked() == TRUE) {
                    $nextChunk = $line->getChunk($chunkStrokeIdx + 1);
                    if ($chunk->isAttribute(Chunk::UNDERLINE) == TRUE) {
                        $subtract = $lastBaseFactor;
                        if ($nextChunk != NULL && $nextChunk->isAttribute(Chunk::UNDERLINE) == TRUE)
                            $subtract = 0;
                        if ($nextChunk == NULL)
                            $subtract += $hangingCorrection;
                        $unders = $chunk->getAttribute(Chunk::UNDERLINE);
                        $scolor = NULL;//Color
                        $cap = 0;
                        for ($k = 0; $k < count($unders); ++$k) {
                            $obj = $unders[$k];
                            $scolor = $obj[0];
                            $ps = $obj[1];
                            if ($scolor == NULL)
                                $scolor = $color;
                            if ($scolor != NULL)
                                $graphics->setColorStroke($scolor);
                            $fsize = $chunk->font()->size();
                            $graphics->setLineWidth($ps[0] + $fsize * $ps[1]);
                            $shift = $ps[2] + $fsize * $ps[3];
                            $cap2 = (integer)$ps[4];
                            if ($cap2 != 0)
                                $graphics->setLineCap($cap2);
                            $graphics->moveTo($xMarker, $yMarker + $shift);
                            $graphics->lineTo($xMarker + $width - $subtract, $yMarker + $shift);
                            $graphics->stroke();
                            if ($scolor != NULL)
                                $graphics->resetGrayStroke();
                            if ($cap2 != 0)
                                $graphics->setLineCap(0);
                        }
                        $graphics->setLineWidth(1);
                    }
                    if ($chunk->isAttribute(Chunk::ACTION) == TRUE) {
                        $subtract = $lastBaseFactor;
                        if ($nextChunk != NULL && $nextChunk->isAttribute(Chunk::ACTION))
                            $subtract = 0;
                        if ($nextChunk == NULL)
                            $subtract += $hangingCorrection;
                        $text->addAnnotation(new PdfAnnotation($writer, $xMarker, $yMarker, $xMarker + $width - $subtract, $yMarker + $chunk->font()->size(), $chunk->getAttribute(Chunk::ACTION)));
                    }
                    if ($chunk->isAttribute(Chunk::REMOTEGOTO) == TRUE) {
                        $subtract = $lastBaseFactor;
                        if ($nextChunk != NULL && $nextChunk->isAttribute(Chunk::REMOTEGOTO) == TRUE)
                            $subtract = 0;
                        if ($nextChunk == NULL)
                            $subtract += $hangingCorrection;
                        $obj = $chunk->getAttribute(Chunk::REMOTEGOTO);
                        $filename = (string)$obj[0];
                        if (is_string($obj[1]) == TRUE)
                            remoteGoto($filename, (string)$obj[1], $xMarker, $yMarker, $xMarker + $width - $subtract, $yMarker + $chunk->font()->size());
                        else
                            remoteGoto($filename, (integer)$obj[1], $xMarker, $yMarker, $xMarker + $width - $subtract, $yMarker + $chunk->font()->size());
                    }
                    if ($chunk->isAttribute(Chunk::LOCALGOTO) == TRUE) {
                        $subtract = $lastBaseFactor;
                        if ($nextChunk != NULL && $nextChunk->isAttribute(Chunk::LOCALGOTO) == TRUE)
                            $subtract = 0;
                        if ($nextChunk == NULL)
                            $subtract += $hangingCorrection;
                        localGoto((string)$chunk->getAttribute(Chunk::LOCALGOTO), $xMarker, $yMarker, $xMarker + $width - $subtract, $yMarker + $chunk->font()->size());
                    }
                    if ($chunk->isAttribute(Chunk::LOCALDESTINATION) == TRUE) {
                        $subtract = $lastBaseFactor;
                        if ($nextChunk != NULL && $nextChunk->isAttribute(Chunk::LOCALDESTINATION) == TRUE)
                            $subtract = 0;
                        if ($nextChunk == NULL)
                            $subtract += $hangingCorrection;
                        $localDestination((string)$chunk->getAttribute(Chunk::LOCALDESTINATION), new PdfDestination(PdfDestination::XYZ, $xMarker, $yMarker + $chunk->font()->size(), 0));
                    }
                    if ($chunk->isAttribute(Chunk::GENERICTAG) == TRUE) {
                        $subtract = $lastBaseFactor;
                        if ($nextChunk != NULL && $nextChunk->isAttribute(Chunk::GENERICTAG) == TRUE)
                            $subtract = 0;
                        if ($nextChunk == NULL)
                            $subtract += $hangingCorrection;
                        $rect = new Rectangle($xMarker, $yMarker, $xMarker + $width - $subtract, $yMarker + $chunk->font()->size());
                        $pev = $writer->getPageEvent();
                        if ($pev != NULL)
                            $pev->onGenericTag($writer, $this, $rect, (string)$chunk->getAttribute(Chunk::GENERICTAG));
                    }
                    if ($chunk->isAttribute(Chunk::BACKGROUND) == TRUE) {
                        $subtract = $lastBaseFactor;
                        if ($nextChunk != NULL && $nextChunk->isAttribute(Chunk::BACKGROUND) == TRUE)
                            $subtract = 0;
                        if ($nextChunk == NULL)
                            $subtract += $hangingCorrection;
                        $fontSize = $chunk->font()->size();
                        $ascender = $chunk->font()->getFont()->getFontDescriptor(BaseFont::ASCENT, $fontSize);
                        $descender = $chunk->font()->getFont()->getFontDescriptor(BaseFont::DESCENT, $fontSize);
                        $bgr = $chunk->getAttribute(Chunk::BACKGROUND);
                        $graphics->setColorFill($bgr[0]);
                        $extra = $bgr[1];
                        $graphics->rectangle($xMarker - $extra[0],                    $yMarker + $descender - $extra[1] + $chunk->getTextRise(),               $width - $subtract + $extra[0] + $extra[2],                            $ascender - $descender + $extra[1] + $extra[3]);
                        $graphics->fill();
                        $graphics->setGrayFill(0);
                    }
                    if ($chunk->isAttribute(Chunk::PDFANNOTATION) == TRUE) {
                        $subtract = $lastBaseFactor;
                        if ($nextChunk != NULL && $nextChunk->isAttribute(Chunk::PDFANNOTATION) == TRUE)
                            $subtract = 0;
                        if ($nextChunk == NULL)
                            $subtract += $hangingCorrection;
                        $fontSize = $chunk->font()->size();
                        $ascender = $chunk->font()->getFont()->getFontDescriptor(BaseFont::ASCENT, $fontSize);
                        $descender = $chunk->font()->getFont()->getFontDescriptor(BaseFont::DESCENT, $fontSize);
                        $annot = PdfFormField::shallowDuplicate($chunk->getAttribute(Chunk::PDFANNOTATION));
                        $annot->put(PdfName::$RECT, new PdfRectangle($xMarker, $yMarker + $descender, $xMarker + $width - $subtract, $yMarker + $ascender));
                        $text->addAnnotation($annot);
                    }
                    $params = $chunk->getAttribute(Chunk::SKEW);
                    $hs = (float)$chunk->getAttribute(Chunk::HSCALE);
                    if ($params != NULL || $hs != NULL) {
                        $a = 1.0;
                        $b = 0.0;
                        $c = 0;
                        if ($params != NULL) {
                            $b = $params[0];
                            $c = $params[1];
                        }
                        if ($hs != NULL)
                            $hScale = $hs;
                        $text->setTextMatrix($hScale, $b, $c, 1, $xMarker, $yMarker);
                    }
                    if ($chunk->isImage() == TRUE) {
                        $image = $chunk->getImage();
                        $matrix = $image->matrix();
                        $matrix[Image::CX] = $xMarker + $chunk->getImageOffsetX() - $matrix[Image::CX];
                        $matrix[Image::CY] = $yMarker + $chunk->getImageOffsetY() - $matrix[Image::CY];
                        addImage($graphics, $image, $matrix[0], $matrix[1], $matrix[2], $matrix[3], $matrix[4], $matrix[5]);
                        $text->moveText($xMarker + $lastBaseFactor + $image->scaledWidth() - $text->getXTLM(), 0);
                    }
                }
                $xMarker += $width;
                ++$chunkStrokeIdx;
            }

            if ($chunk->font()->compareTo($currentFont) != 0) {
                $currentFont = $chunk->font();
                $text->setFontAndSize($currentFont->getFont(), $currentFont->size());
            }
            $rise = 0;
            $textRender = $chunk->getAttribute(Chunk::TEXTRENDERMODE);
            $tr = 0;
            $strokeWidth = 1.0;
            $strokeColor = NULL;//Color
            $fr = (float)$chunk->getAttribute(Chunk::SUBSUPSCRIPT);
            if ($textRender != NULL) {
                $tr = (integer)$textRender[0] & 3;
                if ($tr != PdfContentByte::TEXT_RENDER_MODE_FILL)
                    $text->setTextRenderingMode($tr);
                if ($tr == PdfContentByte::TEXT_RENDER_MODE_STROKE || $tr == PdfContentByte::TEXT_RENDER_MODE_FILL_STROKE) {
                    $strokeWidth = (float)$textRender[1];
                    if ($strokeWidth != 1)
                        $text->setLineWidth($strokeWidth);
                    $strokeColor = $textRender[2];
                    if ($strokeColor == NULL)
                        $strokeColor = $color;
                    if ($strokeColor != NULL)
                        $text->setColorStroke($strokeColor);
                }
            }
            if ($fr != NULL)
                $rise = $fr;
            if ($color != NULL)
                $text->setColorFill($color);
            if ($rise != 0)
                $text->setTextRise($rise);
            if ($chunk->isImage() == TRUE) {
                $adjustMatrix = TRUE;
            }
            // If it is a CJK chunk or Unicode TTF we will have to simulate the
            // space adjustment.
            else if ($isJustified == TRUE && $numberOfSpaces > 0 && $chunk->isSpecialEncoding() == TRUE) {
                if ($hScale != $lastHScale) {
                    $lastHScale = $hScale;
                    $text->setWordSpacing($baseWordSpacing / $hScale);
                    $text->setCharacterSpacing($baseCharacterSpacing / $hScale);
                }
                $s = $chunk->toString();
                $idx = strpos($s, ' ');
                if ($idx < 0)
                    $text->showText($chunk->toString());
                else {
                    $spaceCorrection = - $baseWordSpacing * 1000.0 / $chunk->font->size() / $hScale;
                    $textArray = new PdfTextArray(substr($s, 0, $idx));
                    $lastIdx = $idx;
                    while (($idx = strpos($s, ' ', $lastIdx + 1)) >= 0) {
                        $textArray->add($spaceCorrection);
                        $textArray->add(substr($s, $lastIdx, $idx));
                        $lastIdx = $idx;
                    }
                    $textArray->add($spaceCorrection);
                    $textArray->add(substr($s, $lastIdx));
                    $text->showText($textArray);
                }
            }
            else {
                if ($isJustified == TRUE && $hScale != $lastHScale) {
                    $lastHScale = $hScale;
                    $text->setWordSpacing($baseWordSpacing / $hScale);
                    $text->setCharacterSpacing($baseCharacterSpacing / $hScale);
                }
                $text->showText($chunk->toString());
            }

            if ($rise != 0)
                $text->setTextRise(0);
            if ($color != NULL)
                $text->resetRGBColorFill();
            if ($tr != PdfContentByte::TEXT_RENDER_MODE_FILL)
                $text->setTextRenderingMode(PdfContentByte::TEXT_RENDER_MODE_FILL);
            if ($strokeColor != NULL)
                $text->resetRGBColorStroke();
            if ($strokeWidth != 1)
                $text->setLineWidth(1);
            if ($chunk->isAttribute(Chunk::SKEW) == TRUE || $chunk->isAttribute(Chunk::HSCALE) == TRUE) {
                $adjustMatrix = TRUE;
                $text->setTextMatrix($xMarker, $yMarker);
            }
        }
        if ($isJustified == TRUE) {
            $text->setWordSpacing(0);
            $text->setCharacterSpacing(0);
            if ($line->isNewlineSplit() == TRUE)
                $lastBaseFactor = 0;
        }
        if ($adjustMatrix == TRUE)
            $text->moveText($baseXMarker - $text->getXTLM(), 0);
        $currentValues[0] = $currentFont;
        $currentValues[1] = $lastBaseFactor;
    }


    /**
    * Implements a link to other part of the document. The jump will
    * be made to a local destination with the same name, that must exist.
    * @param name the name for this link
    * @param llx the lower left x corner of the activation area
    * @param lly the lower left y corner of the activation area
    * @param urx the upper right x corner of the activation area
    * @param ury the upper right y corner of the activation area
    */
    function localGoto($name, $llx, $lly, $urx, $ury) {
        $action = getLocalGotoAction($name);
        array_push($annotations, new PdfAnnotation($writer, $llx, $lly, $urx, $ury, $action));
    }

    function getLocalGotoAction($name) {
        $action = NULL;//PdfAction
        $obj = $localDestinations[$name];
        if ($obj == NULL)
            $obj = array();
        if ($obj[0] == NULL) {
            if ($obj[1] == NULL) {
                $obj[1] = $writer->getPdfIndirectReference();
            }
            $action = new PdfAction($obj[1]);
            $obj[0] = $action;
            $localDestinations[$name] = $obj;
        }
        else {
            $action = $obj[0];
        }
        return $action;
    }

    /**
    * The local destination to where a local goto with the same
    * name will jump to.
    * @param name the name of this local destination
    * @param destination the <CODE>PdfDestination</CODE> with the jump coordinates
    * @return <CODE>true</CODE> if the local destination was added,
    * <CODE>false</CODE> if a local destination with the same name
    * already existed
    */
    function localDestination($name, PdfDestination $destination) {
        $obj = $localDestinations[$name];
        if ($obj == NULL)
            $obj = array();
        if ($obj[2] != NULL)
            return FALSE;
        $obj[2] = $destination;
        $localDestinations[$name] = $obj;
        $destination->addPage($writer->getCurrentPage());
        return TRUE;
    }

    function remoteGoto()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 6:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                if ((is_string($arg1) == TRUE || $arg == NULL) && (is_string($arg2) == TRUE || $arg2 == NULL) && is_float($arg3) == TRUE && is_float($arg4) == TRUE && is_float($arg5) == TRUE && is_float($arg6) == TRUE)
                    remoteGoto6argsString($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
                else if ((is_string($arg1) == TRUE || $arg == NULL) && is_integer($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE && is_float($arg5) == TRUE && is_float($arg6) == TRUE)
                    remoteGoto6argsInteger($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
                break;
            }
        }
    }

    /**
    * Implements a link to another document.
    * @param filename the filename for the remote document
    * @param name the name to jump to
    * @param llx the lower left x corner of the activation area
    * @param lly the lower left y corner of the activation area
    * @param urx the upper right x corner of the activation area
    * @param ury the upper right y corner of the activation area
    */
    private function remoteGoto6argsString($filename, $name, $llx, $lly, $urx, $ury) {
        array_push($annotations, new PdfAnnotation($writer, $llx, $lly, $urx, $ury, new PdfAction($filename, $name)));
    }


    /**
    * Implements a link to another document.
    * @param filename the filename for the remote document
    * @param page the page to jump to
    * @param llx the lower left x corner of the activation area
    * @param lly the lower left y corner of the activation area
    * @param urx the upper right x corner of the activation area
    * @param ury the upper right y corner of the activation area
    */
    private function remoteGoto6argsInteger($filename, $page, $llx, $lly, $urx, $ury) {
        $writer->addAnnotation(new PdfAnnotation($writer, $llx, $lly, $urx, $ury, new PdfAction($filename, $page)));
    }

    /** Sets the viewer preferences as the sum of several constants.
    * @param preferences the viewer preferences
    * @see PdfWriter#setViewerPreferences
    */

    public function setViewerPreferences($preferences) {
        $viewerPreferences |= $preferences;
    }

    /** Implements an action in an area.
    * @param action the <CODE>PdfAction</CODE>
    * @param llx the lower left x corner of the activation area
    * @param lly the lower left y corner of the activation area
    * @param urx the upper right x corner of the activation area
    * @param ury the upper right y corner of the activation area
    */
    function setAction(PdfAction $action, $llx, $lly, $urx, $ury) {
        $writer->addAnnotation(new PdfAnnotation($writer, $llx, $lly, $urx, $ury, $action));
    }

    function setOpenAction()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_string($arg1) == TRUE)
                    setOpenAction1argString($arg1);
                else if ($arg1 instanceof PdfAction)
                    setOpenAction1argPdfAction($arg1);
                break;
            }
        }
    }

    private function setOpenAction1argString($name) {
        $openActionName = $name;
        $openActionAction = NULL;
    }

    private function setOpenAction1argPdfAction(PdfAction $action) {
        $openActionAction = $action;
        $openActionName = NULL;
    }

    void addAdditionalAction(PdfName $actionType, PdfAction $action)  {
        if ($additionalActions == NULL)  {
            $additionalActions = new PdfDictionary();
        }
        if ($action == NULL)
            $additionalActions->remove($actionType);
        else
            $additionalActions->put($actionType, $action);
        if ($additionalActions->size() == 0)
            $additionalActions = NULL;
    }

    function setPageLabels(PdfPageLabels $pageLabels) {
        $this->pageLabels = $pageLabels;
    }

    function addJavaScript(PdfAction $js) {
        if ($js->get(PdfName::$JS) == NULL)
            throw new Exception("Only JavaScript actions are allowed.");
        try {
            array_push($documentJavaScript, $writer->addToBody($js)->getIndirectReference());
        }
        catch (IOException $e) {
            throw new Exception($e);
        }
    }

    function setCropBoxSize(Rectangle $crop) {
        setBoxSize("crop", $crop);
    }

    function setBoxSize($boxName, Rectangle $size) {
        if ($size == NULL)
            unset($boxSize[$boxName]);;
        else
            $boxSize[$boxName] = new PdfRectangle($size);
    }

    function addCalculationOrder(PdfFormField $formField) {
        $acroForm->addCalculationOrder($formField);
    }

    function setSigFlags($f) {
        $acroForm->setSigFlags($f);
    }

    function addFormFieldRaw(PdfFormField $field) {
        array_push($annotations, $field);
        $kids = $field->getKids();
        if ($kids != NULL) {
            for ($k = 0; $k < count($kids); ++$k)
                addFormFieldRaw($kids[$k]);
        }
    }

    function addAnnotation(PdfAnnotation annot) {
        $pageEmpty = FALSE;
        if ($annot->isForm() == TRUE) {
            $field = $annot;
            if ($field->getParent() == NULL)
                addFormFieldRaw($field);
        }
        else
            array_push($annotations, $annot);
    }

    /**
    * Sets the display duration for the page (for presentations)
    * @param seconds   the number of seconds to display the page
    */
    function setDuration($seconds) {
        if ($seconds > 0)
            $this->duration=$seconds;
        else
            $this->duration=-1;
    }

    /**
    * Sets the transition for the page
    * @param transition   the PdfTransition object
    */
    function setTransition(PdfTransition $transition) {
        $this->transition=$transition;
    }

    function setPageAction(PdfName $actionType, PdfAction $action) {
        if ($pageAA == NULL) {
            $pageAA = new PdfDictionary();
        }
        $pageAA->put($actionType, $action);
    }


    /** Getter for property strictImageSequence.
    * @return Value of property strictImageSequence.
    *
    */
    function isStrictImageSequence() {
        return $this->strictImageSequence;
    }

    /** Setter for property strictImageSequence.
    * @param strictImageSequence New value of property strictImageSequence.
    *
    */
    function setStrictImageSequence($strictImageSequence) {
        $this->strictImageSequence = $strictImageSequence;
    }

    function setPageEmpty($pageEmpty) {
        $this->pageEmpty = $pageEmpty;
    }

    /**
    * Method added by Pelikan Stephan
    * @see com.lowagie.text.DocListener#clearTextWrap()
    */
    public function clearTextWrap() {
        parent::clearTextWrap();
        $tmpHeight = $imageEnd - $currentHeight;
        if ($line != NULL) {
            $tmpHeight += $line->height();
        }
        if (($imageEnd > -1) && ($tmpHeight > 0)) {
            carriageReturn();
            $currentHeight += $tmpHeight;
        }
    }

    function getDocumentJavaScript() {
        return $documentJavaScript;
    }


    /**
    * @see com.lowagie.text.DocListener#setMarginMirroring(boolean)
    */
    public function setMarginMirroring($MarginMirroring) {
        if ($writer != NULL && $writer->isPaused() == TRUE) {
            return FALSE;
        }
        return parent::setMarginMirroring$(MarginMirroring);
    }


}



/**
* <CODE>PdfInfo</CODE> is the PDF InfoDictionary.
* <P>
* A document's trailer may contain a reference to an Info dictionary that provides information
* about the document. This optional dictionary may contain one or more keys, whose values
* should be strings.<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 6.10 (page 120-121)
*/

class PdfInfo extends PdfDictionary 
{

    // constructors

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                construct0args();
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if (is_string($arg1) == TRUE && is_string($arg2) == TRUE && is_string($arg3) == TRUE)
                    construct3args($arg1, $arg2, $arg3);
                break;
            }
        }

    }


    /**
    * Construct a <CODE>PdfInfo</CODE>-object.
    */
    private function construct0args() {
        parent::__construct();
        addProducer();
        addCreationDate();
    }

    /**
    * Constructs a <CODE>PdfInfo</CODE>-object.
    *
    * @param		author		name of the author of the document
    * @param		title		title of the document
    * @param		subject		subject of the document
    */
    private function construct3args($author, $title, $subject) {
        construct0args();
        addTitle($title);
        addSubject($subject);
        addAuthor($author);
    }

    /**
    * Adds the title of the document.
    *
    * @param	title		the title of the document
    */

    function addTitle($title) {
        put(PdfName::$TITLE, new PdfString($title, PdfObject::TEXT_UNICODE));
    }

    /**
    * Adds the subject to the document.
    *
    * @param	subject		the subject of the document
    */

    function addSubject($subject) {
        put(PdfName::$SUBJECT, new PdfString($subject, PdfObject::TEXT_UNICODE));
    }

    /**
    * Adds some keywords to the document.
    *
    * @param	keywords		the keywords of the document
    */

    function addKeywords($keywords) {
        put(PdfName::$KEYWORDS, new PdfString($keywords, PdfObject::TEXT_UNICODE));
    }

    /**
    * Adds the name of the author to the document.
    *
    * @param	author		the name of the author
    */

    function addAuthor($author) {
        put(PdfName::$AUTHOR, new PdfString($author, PdfObject::TEXT_UNICODE));
    }

    /**
    * Adds the name of the creator to the document.
    *
    * @param	creator		the name of the creator
    */

    function addCreator($creator) {
        put(PdfName::$CREATOR, new PdfString($creator, PdfObject::TEXT_UNICODE));
    }

    /**
    * Adds the name of the producer to the document.
    */

    function addProducer() {
        // This line may only be changed by Bruno Lowagie, Mills Staylor or Paulo Soares
        put(PdfName::$PRODUCER, new PdfString(getVersion()));
        // Do not edit the line above!
    }

    /**
    * Adds the date of creation to the document.
    */

    function addCreationDate() {
        $date = new PdfDate();
        put(PdfName::$CREATIONDATE, $date);
        put(PdfName::$MODDATE, $date);
    }

    function addkey($key, $value) {
        if (strcmp($key, "Producer") == 0 || strcmp($key, "CreationDate") == 0)
            return;
        put(new PdfName($key), new PdfString($value, PdfObject::TEXT_UNICODE));
    }
}


/**
* <CODE>PdfCatalog</CODE> is the PDF Catalog-object.
* <P>
* The Catalog is a dictionary that is the root node of the document. It contains a reference
* to the tree of pages contained in the document, a reference to the tree of objects representing
* the document's outline, a reference to the document's article threads, and the list of named
* destinations. In addition, the Catalog indicates whether the document's outline or thumbnail
* page images should be displayed automatically when the document is viewed and wether some location
* other than the first page should be shown when the document is opened.<BR>
* In this class however, only the reference to the tree of pages is implemented.<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 6.2 (page 67-71)
*/

class PdfCatalog extends PdfDictionary {

    protected $writer = NULL;//PdfWriter

    // constructors

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if ($arg1 instanceof PdfIndirectReference && $arg2 instanceof PdfWriter)
                    construct2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if ($arg1 instanceof PdfIndirectReference && $arg2 instanceof PdfIndirectReference && $arg3 instanceof PdfWriter)
                    construct3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }


    /**
    * Constructs a <CODE>PdfCatalog</CODE>.
    *
    * @param		pages		an indirect reference to the root of the document's Pages tree.
    * @param writer the writer the catalog applies to
    */

    private function construct2args(PdfIndirectReference $pages, PdfWriter $writer) {
        parent::__construct(PdfDictionary::$CATALOG);
        $this->writer = $writer;
        put(PdfName::$PAGES, $pages);
    }

    /**
    * Constructs a <CODE>PdfCatalog</CODE>.
    *
    * @param		pages		an indirect reference to the root of the document's Pages tree.
    * @param		outlines	an indirect reference to the outline tree.
    * @param writer the writer the catalog applies to
    */

    private function construct3args(PdfIndirectReference $pages, PdfIndirectReference $outlines, PdfWriter $writer) {
        parent::__construct(PdfDictionary::$CATALOG);
        $this->writer = $writer;
        put(PdfName::$PAGES, $pages);
        put(PdfName::$PAGEMODE, PdfName::$USEOUTLINES);
        put(PdfName::$OUTLINES, $outlines);
    }

    /**
    * Adds the names of the named destinations to the catalog.
    * @param localDestinations the local destinations
    * @param documentJavaScript the javascript used in the document
    * @param writer the writer the catalog applies to
    */
    function addNames(array $localDestinations, array $documentJavaScript, PdfWriter $writer) {
            if (count($localDestinations) == 0 && count($documentJavaScript) == 0)
                return;
            try {
                $names = new PdfDictionary();
                if (count($localDestinations) > 0) {
                    $ar = new PdfArray();
                    foreach (array_keys($localDestinations) as &$i) {
                        $name = (string)i;
                        $obj = localDestinations[$name];
                        $ref = $obj[1];
                        $ar->add(new PdfString($name));
                        $ar->add($ref);
                    }
                    $dests = new PdfDictionary();
                    $dests->put(PdfName::$NAMES, $ar);
                    $names->put(PdfName::$DESTS, $writer->addToBody($dests)->getIndirectReference());
                }
                if (count($documentJavaScript) > 0) {
                    $s = array();//array of strings
                    for ($k = 0; $k < count($documentJavaScript); ++$k)
                        $s[$k] = TrueTypeFontUnicode::toHex($k);
                    usort($s, array("StringCompare", "compare"));
                    $ar = new PdfArray();
                    for ($k = 0; $k < count($s; ++$k) {
                        $ar->add(new PdfString($s[$k]));
                        $ar->add($documentJavaScript[$k]);
                    }
                    $js = new PdfDictionary();
                    $js->put(PdfName::$NAMES, $ar);
                    $names->put(PdfName::JAVASCRIPT, $writer->addToBody($js)->getIndirectReference());
                }
                put(PdfName::$NAMES, $writer->addToBody($names)->getIndirectReference());
            }
            catch (IOException $e) {
                throw new Exception($e);
            }
        }

    /** Sets the viewer preferences as the sum of several constants.
    * @param preferences the viewer preferences
    * @see PdfWriter#setViewerPreferences
    */
    function setViewerPreferences($preferences) {
        PdfReader::setViewerPreferences($preferences, $this);
    }

    function setOpenAction(PdfAction $action) {
        put(PdfName::OPENACTION, $action);
    }

    /** Sets the document level additional actions.
    * @param actions   dictionary of actions
    */
    function setAdditionalActions(PdfDictionary $actions) {
        try {
            put(PdfName::$AA, $writer->addToBody($actions)->getIndirectReference());
        } catch (Exception $e) {
            new Exception($e);
        }
    }


    function setPageLabels(PdfPageLabels $pageLabels) {
        put(PdfName::$PAGELABELS, $pageLabels->getDictionary());
    }

    function setAcroForm(PdfObject $fields) {
        put(PdfName::$ACROFORM, $fields);
    }
}



?>