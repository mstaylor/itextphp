<?PHP
/*
 * $Id: DocWriter.php,v 1.2 2005/10/12 17:14:14 mstaylor Exp $
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


require_once("../io/OutputStream.php");
require_once("../io/BufferedOutputStream.php");
require_once("../exceptions/IOException.php");
require_once("pdf/OutputStreamCounter.php");
require_once("DocListener.php");
require_once("Rectangle.php");
require_once("Document.php");
require_once("Element.php");
require_once("Watermark.php");
require_once("HeaderFooter.php");
require_once("MarkupAttributes.php");

/**
* An abstract <CODE>Writer</CODE> class for documents.
* <P>
* <CODE>DocWriter</CODE> is the abstract class of several writers such
* as <CODE>PdfWriter</CODE> and <CODE>HtmlWriter</CODE>.
* A <CODE>DocWriter</CODE> can be added as a <CODE>DocListener</CODE>
* to a certain <CODE>Document</CODE> by getting an instance (see method
* <CODE>getInstance()</CODE> in the specific writer-classes).
* Every <CODE>Element</CODE> added to the original <CODE>Document</CODE>
* will be written to the <CODE>OutputStream</CODE> of the listening
* <CODE>DocWriter</CODE>.
*
* @see   Document
* @see   DocListener
*/

abstract class DocWriter implements DocListener 
{

    /** This is some byte that is often used. */
    public static $NEWLINE = NULL;

    /** This is some byte that is often used. */
    public static $TAB = NULL;

    /** This is some byte that is often used. */
    public static $LT = NULL;

    /** This is some byte that is often used. */
    public static $SPACE = NULL;

    /** This is some byte that is often used. */
    public static $EQUALS = NULL;

    /** This is some byte that is often used. */
    public static $QUOTE = NULL;

    /** This is some byte that is often used. */
    public static $GT = NULL;

    /** This is some byte that is often used. */
    public static $FORWARD = NULL;

    // membervariables

    /** The pageSize. */
    protected $pageSize = NULL;

    /** This is the document that has to be written. */
    protected $document = NULL;

    /** The outputstream of this writer. */
    protected $os = NULL;

    /** Is the writer open for writing? */
    protected $open = FALSE;

    /** Do we have to pause all writing actions? */
    protected $pause = FALSE;

    /** Closes the stream on document close */
    protected $closeStream = TRUE;

    public static $initialized = FALSE;


    public static function initializeStatics()
    {
        if(DocWriter::$initialized == FALSE)
        {
            DocWriter::$NEWLINE = itextphp_bytes_createfromRaw('\n');
            DocWriter::$TAB = itextphp_bytes_createfromRaw('\t');
            DocWriter::$LT = itextphp_bytes_createfromRaw('<');
            DocWriter::$SPACE = itextphp_bytes_createfromRaw(' ');
            DocWriter::$EQUALS = itextphp_bytes_createfromRaw('=');
            DocWriter::$QUOTE = itextphp_bytes_createfromRaw('\"');
            DocWriter::$GT = itextphp_bytes_createfromRaw('>');
            DocWriter::$FORWARD = itextphp_bytes_createfromRaw('/');
            DocWriter::$initialized = TRUE;
        }
    }

    // constructor

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
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                cosntruct2args($arg1, $arg2);
                break;
            }
        }
    }


    private function construct0args()
    {
        parent::__construct();
    }


    /**
    * Constructs a <CODE>DocWriter</CODE>.
    *
    * @param document  The <CODE>Document</CODE> that has to be written
    * @param os  The <CODE>OutputStream</CODE> the writer has to write to.
    */
    private function construct2args(Document $document, OutputStream $os)  {
        $this->document = $document;
        $this->os = new OutputStreamCounter(new BufferedOutputStream($os));
    }

    // implementation of the DocListener methods

    public function add()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Element)
                    return addElement($arg1);
                else if ($arg1 instanceof Watermark)
                    return addWatermark($arg1);
                break;
            }
        }
    }

    /**
    * Signals that an <CODE>Element</CODE> was added to the <CODE>Document</CODE>.
    * <P>
    * This method should be overriden in the specific <CODE>DocWriter<CODE> classes
    * derived from this abstract class.
    * 
    * @param element A high level object to add
    * @return  <CODE>false</CODE>
    * @throws  DocumentException when a document isn't open yet, or has been closed
    */

    private function addElement(Element $element) {
        return FALSE;
    }

    /**
    * Signals that the <CODE>Document</CODE> was opened.
    */

    public function open() {
        $open = TRUE;
    }

    /**
    * Sets the pagesize.
    *
    * @param pageSize  the new pagesize
    * @return  a <CODE>boolean</CODE>
    */

    public function setPageSize(Rectangle $pageSize) {
        $this->pageSize = $pageSize;
        return TRUE;
    }

    /**
    * Sets the <CODE>Watermark</CODE>.
    * <P>
    * This method should be overriden in the specific <CODE>DocWriter<CODE> classes
    * derived from this abstract class if they actually support the use of
    * a <CODE>Watermark</CODE>.
    * 
    * @param watermark A watermark object
    * @return  <CODE>false</CODE> (because watermarks aren't supported by default).
    */

    private function addWaterMark(Watermark $watermark) {
        return FALSE;
    }

    /**
    * Removes the <CODE>Watermark</CODE> (if there is one).
    */

    public function removeWatermark() {
    }

    /**
    * Sets the margins.
    * <P>
    * This does nothing. Has to be overridden if needed.
    *
    * @param marginLeft    the margin on the left
    * @param marginRight   the margin on the right
    * @param marginTop   the margin on the top
    * @param marginBottom  the margin on the bottom
    * @return  <CODE>false</CODE>
    */

    public function setMargins($marginLeft, $marginRight, $marginTop, $marginBottom) {
        return FALSE;
    }


    /**
    * Signals that an new page has to be started.
    * <P>
    * This does nothing. Has to be overridden if needed.
    *
    * @return  <CODE>true</CODE> if the page was added, <CODE>false</CODE> if not.
    * @throws  DocumentException when a document isn't open yet, or has been closed
    */

    public function newPage() {
        if ($open == FALSE) {
            return FALSE;
        }
        return TRUE;
    }

    /**
    * Changes the header of this document.
    * <P>
    * This method should be overriden in the specific <CODE>DocWriter<CODE> classes
    * derived from this abstract class if they actually support the use of
    * headers.
    *
    * @param header    the new header
    */

    public function setHeader(HeaderFooter $header) {
    }

    /**
    * Resets the header of this document.
    * <P>
    * This method should be overriden in the specific <CODE>DocWriter<CODE> classes
    * derived from this abstract class if they actually support the use of
    * headers.
    */

    public function resetHeader() {
    }

    /**
    * Changes the footer of this document.
    * <P>
    * This method should be overriden in the specific <CODE>DocWriter<CODE> classes
    * derived from this abstract class if they actually support the use of
    * footers.
    *
    * @param footer    the new footer
    */

    public function setFooter(HeaderFooter $footer) {
    }

    /**
    * Resets the footer of this document.
    * <P>
    * This method should be overriden in the specific <CODE>DocWriter<CODE> classes
    * derived from this abstract class if they actually support the use of
    * footers.
    */

    public function resetFooter() {
    }

    /**
    * Sets the page number to 0.
    * <P>
    * This method should be overriden in the specific <CODE>DocWriter<CODE> classes
    * derived from this abstract class if they actually support the use of
    * pagenumbers.
    */

    public function resetPageCount() {
    }

    /**
    * Sets the page number.
    * <P>
    * This method should be overriden in the specific <CODE>DocWriter<CODE> classes
    * derived from this abstract class if they actually support the use of
    * pagenumbers.
    *
    * @param pageN   the new page number
    */

    public function setPageCount($pageN) {
    }

    /**
    * Signals that the <CODE>Document</CODE> was closed and that no other
    * <CODE>Elements</CODE> will be added.
    */

    public function close() {
        $open = FALSE;
        try {
            $os->flush();
            if ($closeStream == TRUE)
                $os->close();
        }
        catch(IOException $ioe) {
            throw new Exception($ioe);
        }
    }

     // methods

    /** Converts a <CODE>String</CODE> into a <CODE>Byte</CODE> array
    * according to the ISO-8859-1 codepage.
    * @param text the text to be converted
    * @return the conversion result
    */

    public static final function getISOBytes($text)
    {
        if ($text == NULL)
            return NULL;
        $len = strlen($text);
        $b = itextphp_bytes_create($len);
        for ($k = 0; $k < $len; ++$k)
            itextphp_bytes_write($b, $k, itextphp_bytes_createfromRaw($text[$k]), 0);
        return $b;
    }


    /**
    * Let the writer know that all writing has to be paused.
    */

    public function pause() {
        $pause = TRUE;
    }

    /**
    * Let the writer know that writing may be resumed.
    */

    public function resume() {
        $pause = FALSE;
    }

    /**
    * Flushes the <CODE>BufferedOutputStream</CODE>.
    */

    public function flush() {
        try {
            $os->flush();
        }
        catch(IOException $ioe) {
            throw new Exception($ioe);
        }
    }

    protected function write()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                write1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                write2args($arg1, $arg2);
                break;
            }
        }
    }

    /**
    * Writes a <CODE>String</CODE> to the <CODE>OutputStream</CODE>.
    *
    * @param string    the <CODE>String</CODE> to write
    * @throws IOException
    */

    private function write1arg($string) {
        $os->write(DocWriter::getISOBytes($string));
    }

    /**
    * Writes a number of tabs.
    *
    * @param   indent  the number of tabs to add
    * @throws IOException
    */

    protected function addTabs($indent) {
        $os->write(DocWriter::$NEWLINE));
        for ($i = 0; $i < $indent; $i++) {
            $os->write(DocWriter::$TAB);
        }
    }

    /**
    * Writes a key-value pair to the outputstream.
    *
    * @param   key     the name of an attribute
    * @param   value   the value of an attribute
    * @throws IOException
    */

    private function write2args($key, $value)
    {
        $os->write(DocWriter::$SPACE);
        write($key);
        $os->write(DocWriter::$EQUALS);
        $os->write(DocWriter::$QUOTE);
        write($value);
        $os->write(DocWriter::$QUOTE);
    }

    /**
    * Writes a starttag to the outputstream.
    *
    * @param   tag     the name of the tag
    * @throws IOException
    */

    protected function writeStart($tag)
    {
        $os->write(DocWriter::$LT);
        write($tag);
    }

    protected function writeEnd()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                writeEnd0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                writeEnd1arg($arg1);
                break;
            }
        }
    }


    /**
    * Writes an endtag to the outputstream.
    *
    * @param   tag     the name of the tag
    * @throws IOException
    */

    private function writeEnd1arg($tag)
    {
        $os->write(DocWriter::$LT);
        $os->write(DocWriter::$FORWARD);
        write($tag);
        $os->write(DocWriter::$GT);
    }

    /**
    * Writes an endtag to the outputstream.
    * @throws IOException
    */

    private function writeEnd0args()
    {
        $os->write(DocWriter::$SPACE);
        $os->write(DocWriter::$FORWARD);
        $os->write(DocWriter::$GT);
    }

    /**
    * Writes the markup attributes of the specified <CODE>MarkupAttributes</CODE>
    * object to the <CODE>OutputStream</CODE>.
    * @param mAtt   the <CODE>MarkupAttributes</CODE> to write.
    * @return true, if writing the markup attributes succeeded
    * @throws IOException
    */
    protected function writeMarkupAttributes(MarkupAttributes $mAtt)
    {
      Iterator attributeIterator = mAtt.getMarkupAttributeNames().iterator();
      if (count($matt->getMarkupAttributeNames) > 0) 
          $result = TRUE;
      else
          $result = FALSE;
      foreach ($matt->getMarkupAttributeNames as &$name) {
        write($name, $mAtt->getMarkupAttribute((string)$name));
      }
      return $result;
    }

    /**
    * Returns <CODE>true</CODE> if the specified <CODE>Element</CODE> implements
    * <CODE>MarkupAttributes</CODE> and has one or more attributes to write.
    * @param element   the <CODE>Element</CODE> to check.
    * @return <CODE>boolean</CODE>.
    */
    protected static function hasMarkupAttributes(Element $element) {
      return ($element instanceof MarkupAttributes &&
       !(count(($element)->getMarkupAttributeNames()) == 0));
    }

    /** Checks if the stream is to be closed on document close
    * @return true if the stream is closed on documnt close
    *
    */
    public function isCloseStream() {
        return $closeStream;
    }

    /** Sets the close state of the stream after document close
    * @param closeStream true if the stream is closed on document close
    *
    */
    public function setCloseStream($closeStream) {
        $this->closeStream = $closeStream;
    }

    /**
    * @see com.lowagie.text.DocListener#clearTextWrap()
    */
    public function clearTextWrap() {
        // do nothing
    }
    /**
    * @see com.lowagie.text.DocListener#setMarginMirroring(boolean)
    */
    public function setMarginMirroring($MarginMirroring) {
        return FALSE;
    }

}

?>