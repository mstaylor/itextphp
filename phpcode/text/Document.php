<?PHP
/*
 * $Id: Document.php,v 1.3 2005/11/17 21:46:10 mstaylor Exp $
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


require_once("DocListener.php");
require_once("Rectangle.php");
require_once("HeaderFooter.php");
require_once("Watermark.php");
require_once("PageSize.php");
require_once("Element.php");
require_once("DocumentException.php");
require_once("Meta.php");
/**
* A generic Document class.
* <P>
* All kinds of Text-elements can be added to a <CODE>HTMLDocument</CODE>.
* The <CODE>Document</CODE> signals all the listeners when an element has
* been added.
* <P>
* Remark:
* <OL>
*     <LI>Once a document is created you can add some meta information.
*     <LI>You can also set the headers/footers.
*     <LI>You have to open the document before you can write content.
* <LI>You can only write content (no more meta-formation!) once a document is
* opened.
* <LI>When you change the header/footer on a certain page, this will be
* effective starting on the next page.
* <LI>Ater closing the document, every listener (as well as its <CODE>
* OutputStream</CODE>) is closed too.
* </OL>
* Example: <BLOCKQUOTE>
*
* <PRE>// creation of the document with a certain size and certain margins
* <STRONG>Document document = new Document(PageSize.A4, 50, 50, 50, 50);
* </STRONG> try { // creation of the different writers HtmlWriter.getInstance(
* <STRONG>document </STRONG>, System.out); PdfWriter.getInstance(
* <STRONG>document </STRONG>, new FileOutputStream("text.pdf"));
*    // we add some meta information to the document
* <STRONG>document.addAuthor("Bruno Lowagie"); </STRONG>
* <STRONG>document.addSubject("This is the result of a Test."); </STRONG>
*  // we define a header and a footer HeaderFooter header = new
* HeaderFooter(new Phrase("This is a header."), false); HeaderFooter footer =
* new HeaderFooter(new Phrase("This is page "), new Phrase("."));
*    footer.setAlignment(Element.ALIGN_CENTER);
* <STRONG>document.setHeader(header); </STRONG>
* <STRONG>document.setFooter(footer); </STRONG>// we open the document for
* writing <STRONG>document.open(); </STRONG> <STRONG>document.add(new
* Paragraph("Hello world")); </STRONG>} catch(DocumentException de) {
* System.err.println(de.getMessage()); } <STRONG>document.close(); </STRONG>
* </CODE>
* </PRE>
* 
* </BLOCKQUOTE>
*/



class Document implements DocListener
{

    // membervariables
    /** This constant may only be changed by Paulo Soares and/or Bruno Lowagie. */
    private static $ITEXT_VERSION = "iText 1.3 by lowagie.com (based on itext-paulo-153)";

    /**
    * Allows the pdf documents to be produced without compression for debugging
    * purposes.
    */
    const compress = TRUE; 

    /** The DocListener. */
    private $listeners = array();

    /** Is the document open or not? */
    protected $open = FALSE;

    /** Has the document already been closed? */
    protected $close = FALSE;

    // membervariables concerning the layout

    /** The size of the page. */
    protected $pageSize = NULL;

    /** The watermark on the pages. */
    protected $watermark = NULL;//a Watermark

    /** margin in x direction starting from the left */
    protected $marginLeft = 0;

    /** margin in x direction starting from the right */
    protected $marginRight = 0;

    /** margin in y direction starting from the top */
    protected $marginTop = 0;

    /** margin in y direction starting from the bottom */
    protected $marginBottom = 0;

    protected $marginMirroring = FALSE;

    /** Content of JavaScript onLoad function */
    protected $javaScript_onLoad = NULL;// a string

    /** Content of JavaScript onUnLoad function */
    protected $javaScript_onUnLoad = NULL;// a string

    /** Style class in HTML body tag */
    protected $htmlStyleClass = NULL;// a string

    // headers, footers

    /** Current pagenumber */
    protected $pageN = 0;

    /** This is the textual part of a Page; it can contain a header */
    protected $header = NULL;// a headerfooter

    /** This is the textual part of the footer */
    protected $footer = NULL;// a headerfooter

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
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Rectangle)
                    construct1arg($arg1);
                break;
            }
            case 5:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                if ($arg1 instanceof Rectangle && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE && is_float($arg5) == TRUE)
                    construct5args($arg1, $arg2, $arg3, $arg4, $arg5);
                break;
            }
        }
    }

    /**
    * Constructs a new <CODE>Document</CODE> -object.
    *
    * @param pageSize
    *            the pageSize
    * @param marginLeft
    *            the margin on the left
    * @param marginRight
    *            the margin on the right
    * @param marginTop
    *            the margin on the top
    * @param marginBottom
    *            the margin on the bottom
    */

    public Document(Rectangle $pageSize, $marginLeft, $marginRight, $marginTop, $marginBottom) {
        $this->pageSize = $pageSize;
        $this->marginLeft = $marginLeft;
        $this->marginRight = $marginRight;
        $this->marginTop = $marginTop;
        $this->marginBottom = $marginBottom;
    }



    /**
    * Constructs a new <CODE>Document</CODE> -object.
    */

    private function construct0args() {
        construct1arg(PageSize::$A4);
    }

    /**
    * Constructs a new <CODE>Document</CODE> -object.
    *
    * @param pageSize
    *            the pageSize
    */

    private function construct1arg(Rectangle $pageSize) {
        construct5args($pageSize, 36, 36, 36, 36);
    }


     // listener methods

    /**
    * Adds a <CODE>DocListener</CODE> to the <CODE>Document</CODE>.
    *
    * @param listener
    *            the new DocListener.
    */

    public function addDocListener(DocListener $listener) {
        array_push($listeners, $listener);
    }

    /**
    * Removes a <CODE>DocListener</CODE> from the <CODE>Document</CODE>.
    *
    * @param listener
    *            the DocListener that has to be removed.
    */

    public function removeDocListener(DocListener $listener) {
        unset($listeners[$listener]);
    }

    // methods implementing the DocListener interface


    public function add()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {

            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Element)
                   return add1argElement($arg1);
                else if ($arg1 instanceof Watermark)
                   return add1argWatermark($arg1);
                break;
            }
        }
    }




    /**
    * Adds an <CODE>Element</CODE> to the <CODE>Document</CODE>.
    *
    * @param element
    *            the <CODE>Element</CODE> to add
    * @return <CODE>true</CODE> if the element was added, <CODE>false
    *         </CODE> if not
    * @throws DocumentException
    *             when a document isn't open yet, or has been closed
    */

    private function add1argElement(Element $element) {
        if ($close == TRUE) {
            throw new DocumentException("The document has been closed. You can't add any Elements.");
        }
        $type = $element->type();
        if (open) {
            if (!($type == Element::CHUNK || $type == Element::PHRASE || $type == Element::PARAGRAPH || $type == Element::TABLE || $type == Element::PTABLE || $type == Element::MULTI_COLUMN_TEXT || $type == Element::ANCHOR || $type == Element::ANNOTATION || $type == Element::CHAPTER || $type == Element::SECTION || $type == Element::LIST || $type == Element::LISTITEM || $type == Element::RECTANGLE || $type == Element::JPEG || $type == Element::IMGRAW || $type == Element::IMGTEMPLATE || $type == Element::GRAPHIC)) 
            {
                throw new DocumentException("The document is open; you can only add Elements with content.");
            }
        } else {
            if (!($type == Element::HEADER || $type == Element::TITLE || $type == Element::SUBJECT || $type == Element::KEYWORDS || $type == Element::AUTHOR || $type == Element::PRODUCER || $type == Element::CREATOR || $type == Element::CREATIONDATE)) {
                throw new DocumentException("The document is not open yet; you can only add Meta information.");
            }
        }
        $success = TRUE;
        $listener;
        foreach ($listeners as &$listener) {
            //$success |= listener.add(element);
            array_push($listener, $element)
        }
        return $success;
    }


    /**
    * Opens the document.
    * <P>
    * Once the document is opened, you can't write any Header- or
    * Meta-information anymore. You have to open the document before you can
    * begin to add content to the body of the document.
    */

    public function open() {
        if ($close == FALSE) {
            $open = true;
        }
        foreach ($listeners as &$listener) {
            $listener->setPageSize($pageSize);
            $listener->setMargins($marginLeft, $marginRight, $marginTop,$marginBottom);
            $listener->open();
        }
    }

    /**
    * Sets the pagesize.
    *
    * @param pageSize
    *            the new pagesize
    * @return	a <CODE>boolean</CODE>
    */

    public function setPageSize(Rectangle $pageSize) {
        $this->pageSize = $pageSize;
        foreach ($listeners as &$listener) {
            $listener->setPageSize($pageSize);
        }
        return TRUE;
    }

    /**
    * Sets the <CODE>Watermark</CODE>.
    *
    * @param watermark
    *            the watermark to add
    * @return <CODE>true</CODE> if the element was added, <CODE>false
    *         </CODE> if not.
    */

    private function add1argWatermark(Watermark $watermark) {
        $this->watermark = $watermark;

        foreach ($listeners as &$listener) {
            $listener->add($watermark);
        }
        return TRUE;
    }

    /**
    * Removes the <CODE>Watermark</CODE>.
    */

    public function removeWatermark() {
        $this->watermark = NULL;
        foreach ($listeners as &$listener) {
            $listener->removeWatermark();
        }
    }

    /**
    * Sets the margins.
    *
    * @param marginLeft
    *            the margin on the left
    * @param marginRight
    *            the margin on the right
    * @param marginTop
    *            the margin on the top
    * @param marginBottom
    *            the margin on the bottom
    * @return	a <CODE>boolean</CODE>
    */

    public function setMargins($marginLeft, $marginRight, $marginTop, $marginBottom) {
        $this->marginLeft = $marginLeft;
        $this->marginRight = $marginRight;
        $this->marginTop = $marginTop;
        $this->marginBottom = $marginBottom;
        foreach ($listeners as &$listener) {
            $listener->setMargins($marginLeft, $marginRight, $marginTop, $marginBottom);
        }
        return TRUE;
    }


    /**
    * Signals that an new page has to be started.
    *
    * @return <CODE>true</CODE> if the page was added, <CODE>false</CODE>
    *         if not.
    * @throws DocumentException
    *             when a document isn't open yet, or has been closed
    */

    public function newPage(){
        if ($open == FALSE || $close == TRUE) {
            return FALSE;
        }
        foreach ($listeners as &$listener) {
            $listener->newPage();
        }
        return TRUE;
    }

    /**
    * Changes the header of this document.
    *
    * @param header
    *            the new header
    */

    public function setHeader(HeaderFooter $header) {
        $this->header = $header;
        foreach ($listeners as &$listener) {
            $listener->setHeader($header);
        }
    }


    /**
    * Resets the header of this document.
    */

    public function resetHeader() {
        $this->header = NULL;
        foreach ($listeners as &$listener) {
            $listener->resetHeader();
        }
    }

    /**
    * Changes the footer of this document.
    *
    * @param footer
    *            the new footer
    */

    public function setFooter(HeaderFooter $footer) {
        $this->footer = $footer;
        foreach ($listeners as &$listener) {
            $listener->setFooter($footer);
        }
    }


    /**
    * Resets the footer of this document.
    */

    public function resetFooter() {
        $this->footer = NULL;
        foreach ($listeners as &$listener) {
            $listener->resetFooter();
        }
    }

    /**
    * Sets the page number to 0.
    */

    public function resetPageCount() {
        $pageN = 0;
        foreach ($listeners as &$listener) {
            $listener->resetPageCount();
        }
    }

    /**
    * Sets the page number.
    *
    * @param pageN
    *            the new page number
    */

    public function setPageCount($pageN) {
        $this->pageN = $pageN;
        foreach ($listeners as &$listener) {
            $listener->setPageCount($pageN);
        }
    }

    /**
    * Returns the current page number.
    *
    * @return the current page number
    */

    public function getPageNumber() {
        return $this->pageN;
    }


    /**
    * Closes the document.
    * <P>
    * Once all the content has been written in the body, you have to close the
    * body. After that nothing can be written to the body anymore.
    */

    public function close() {
        if ($close == FALSE) {
            $open = FALSE;
            $close = TRUE;
        }
        foreach ($listeners as &$listener) {
            $listener->close();
        }
    }

     // methods concerning the header or some meta information

    /**
    * Adds a user defined header to the document.
    *
    * @param name
    *            the name of the header
    * @param content
    *            the content of the header
    * @return	<CODE>true</CODE> if successful, <CODE>false</CODE> otherwise
    */

    public function addHeader($name, $content) {
        try {
            return add(new Header($name, $content));
        } catch (DocumentException $de) {
            throw new Exception($de);
        }
    }

    /**
    * Adds the title to a Document.
    *
    * @param title
    *            the title
    * @return	<CODE>true</CODE> if successful, <CODE>false</CODE> otherwise
    */

    public function addTitle($title) {
        try {
            return add(new Meta(Element::TITLE, $title));
        } catch (DocumentException $de) {
            throw new Exception($de);
        }
    }


    /**
    * Adds the subject to a Document.
    *
    * @param subject
    *            the subject
    * @return	<CODE>true</CODE> if successful, <CODE>false</CODE> otherwise
    */

    public function addSubject($subject) {
        try {
            return add(new Meta(Element::SUBJECT, $subject));
        } catch (DocumentException $de) {
            throw new Exception($de);
        }
    }

    /**
    * Adds the keywords to a Document.
    *
    * @param keywords
    *            adds the keywords to the document
    * @return <CODE>true</CODE> if successful, <CODE>false</CODE> otherwise
    */

    public function addKeywords($keywords) {
        try {
            return add(new Meta(Element::KEYWORDS, $keywords));
        } catch (DocumentException $de) {
            throw new Exception($de);
        }
    }

    /**
    * Adds the author to a Document.
    *
    * @param author
    *            the name of the author
    * @return	<CODE>true</CODE> if successful, <CODE>false</CODE> otherwise
    */

    public function addAuthor($author) {
        try {
            return add(new Meta(Element::AUTHOR, $author));
        } catch (DocumentException $de) {
            throw new Exception($de);
        }
    }

    /**
    * Adds the creator to a Document.
    *
    * @param creator
    *            the name of the creator
    * @return	<CODE>true</CODE> if successful, <CODE>false</CODE> otherwise
    */

    public function addCreator($creator) {
        try {
            return add(new Meta(Element::CREATOR, $creator));
		} catch (DocumentException $de) {
            throw new Exception($de);
        }
    }

    /**
    * Adds the producer to a Document.
    *
    * @return	<CODE>true</CODE> if successful, <CODE>false</CODE> otherwise
    */

    public function addProducer() {
        try {
            return add(new Meta(Element::PRODUCER, "iText by Mills W. Staylor, III"));
        } catch (DocumentException $de) {
            throw new Exception($de);
        }
    }

    /**
    * Adds the current date and time to a Document.
    *
    * @return	<CODE>true</CODE> if successful, <CODE>false</CODE> otherwise
    */

    public function addCreationDate() {
        try {
            /* bugfix by 'taqua' (Thomas) */
            return add(new Meta(Element::CREATIONDATE, date("D M d H:m:s T Y")));
        } catch (DocumentException $de) {
            throw new Exception($de);
        }
    }

    // methods to get the layout of the document.

    /**
    * Returns the left margin.
    *
    * @return	the left margin
    */

    public function leftMargin() {
        return $marginLeft;
    }

    /**
    * Return the right margin.
    *
    * @return	the right margin
    */

    public function rightMargin() {
        return $marginRight;
    }

    /**
    * Returns the top margin.
    *
    * @return	the top margin
    */

    public function topMargin() {
        return $marginTop;
    }

    /**
    * Returns the bottom margin.
    *
    * @return	the bottom margin
    */

    public function bottomMargin() {
        return $marginBottom;
    }

    public function left()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                return left0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                return left1arg($arg1);
                break;
            }
    }

    /**
    * Returns the lower left x-coordinate.
    *
    * @return	the lower left x-coordinate
    */

    private function left0args() {
        return $pageSize->left($marginLeft);
    }


    public function right()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                return right0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                return right1arg($arg1);
                break;
            }
    }

    /**
    * Returns the upper right x-coordinate.
    *
    * @return	the upper right x-coordinate
    */

    private function right0 args() {
        return $pageSize->right($marginRight);
    }

    public function top()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                return top0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                return top1arg($arg1);
                break;
            }
    }

    /**
    * Returns the upper right y-coordinate.
    *
    * @return	the upper right y-coordinate
    */

    public function top0args() {
        return $pageSize->top($marginTop);
    }

    /**
    * Returns the lower left y-coordinate.
    *
    * @return	the lower left y-coordinate
    */


    public function bottom()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                return bottom0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                return bottom1arg($arg1);
                break;
            }
    }

    public function bottom0args() {
        return $pageSize->bottom($marginBottom);
    }


    /**
    * Returns the lower left x-coordinate considering a given margin.
    *
    * @param margin
    *            a margin
    * @return	the lower left x-coordinate
    */

    private function left1arg($margin) {
        return $pageSize->left($marginLeft + $margin);
    }

    /**
    * Returns the upper right x-coordinate, considering a given margin.
    *
    * @param margin
    *            a margin
    * @return	the upper right x-coordinate
    */

    private function right1arg($margin) {
        return $pageSize->right($marginRight + $margin);
    }

    /**
    * Returns the upper right y-coordinate, considering a given margin.
    *
    * @param margin
    *            a margin
    * @return	the upper right y-coordinate
    */

    private function top1arg($margin) {
        return $pageSize->top($marginTop + $margin);
    }

    /**
    * Returns the lower left y-coordinate, considering a given margin.
    *
    * @param margin
    *            a margin
    * @return	the lower left y-coordinate
    */

    private function bottom1arg($margin) {
        return $pageSize->bottom($marginBottom + $margin);
    }

    /**
    * Gets the pagesize.
    *
    * @return the page size
    */

    public function getPageSize() {
        return $this->pageSize;
    }

    /**
    * Checks if the document is open.
    *
    * @return <CODE>true</CODE> if the document is open
    */
    public function isOpen() {
        return $open;
    }

    /**
    * Gets the iText version.
    *
    * @return iText version
    */
    public static function getVersion() {
        return Document::$ITEXT_VERSION;
    }

    /**
    * Adds a JavaScript onLoad function to the HTML body tag
    *
    * @param code
    *            the JavaScript code to be executed on load of the HTML page
    */

    public function setJavaScript_onLoad($code) {
        $this->javaScript_onLoad = $code;
    }

    /**
    * Gets the JavaScript onLoad command.
    *
    * @return the JavaScript onLoad command
    */

    public function getJavaScript_onLoad() {
        return $this->javaScript_onLoad;
    }

    /**
    * Adds a JavaScript onUnLoad function to the HTML body tag
    *
    * @param code
    *            the JavaScript code to be executed on unload of the HTML page
    */

    public function setJavaScript_onUnLoad($code) {
        $this->javaScript_onUnLoad = code;
    }

    /**
    * Gets the JavaScript onUnLoad command.
    *
    * @return the JavaScript onUnLoad command
    */

    public function getJavaScript_onUnLoad() {
        return $this->javaScript_onUnLoad;
    }

    /**
    * Adds a style class to the HTML body tag
    *
    * @param htmlStyleClass
    *            the style class for the HTML body tag
    */

    public function setHtmlStyleClass($htmlStyleClass) {
        $this->htmlStyleClass = $htmlStyleClass;
    }

    /**
    * Gets the style class of the HTML body tag
    *
    * @return		the style class of the HTML body tag
    */

    public function getHtmlStyleClass() {
        return $this->htmlStyleClass;
    }

    /**
    * @see com.lowagie.text.DocListener#clearTextWrap()
    */
    public function clearTextWrap() {
        if ($open == TRUE && $close === FALSE) {
            foreach ($listeners as &$listener) {
                $listener->clearTextWrap();
            }
        }
    }

    /**
    * Set the margin mirroring. It will mirror margins for odd/even pages.
    * <p>
    * Note: it will not work with {@link Table}.
    *
    * @param marginMirroring
    *            <CODE>true</CODE> to mirror the margins
    * @return always <CODE>true</CODE>
    */
    public function setMarginMirroring($marginMirroring) {
        $this->marginMirroring = $marginMirroring;
        foreach ($listeners as &$listener) {
            $listener->setMarginMirroring($marginMirroring);
        }
        return TRUE;
    }

    /**
    * Gets the margin mirroring flag.
    *
    * @return the margin mirroring flag
    */
    public function isMarginMirroring() {
        return $marginMirroring;
    }

}





?>