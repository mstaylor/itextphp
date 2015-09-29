<?PHP
/*
 * $Id: PdfPageEvent.php,v 1.2 2005/11/17 21:56:05 mstaylor Exp $
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


require_once("../Document.php");
require_once("../Rectangle.php");
require_once("../Paragraph.php");
require_once("PdfWriter.php");

/**
* Allows a class to catch several document events.
*<p>
* Note: do not use Document.add() inside a page event.
*
* @author  Paulo Soares (psoares@consiste.pt)
* @author Mills W. Staylor, III (bud.staylor@gmail.com) modified to PHP
*/

interface PdfPageEvent
{

    /**
    * Called when the document is opened.
    *
    * @param writer the <CODE>PdfWriter</CODE> for this document
    * @param document the document
    */
    public function onOpenDocument(PdfWriter $writer, Document $document);

    /**
    * Called when a page is initialized.
    * <P>
    * Note that if even if a page is not written this method is still
    * called. It is preferable to use <CODE>onEndPage</CODE> to avoid
    * infinite loops.
    *
    * @param writer the <CODE>PdfWriter</CODE> for this document
    * @param document the document
    */
    public fuunction onStartPage(PdfWriter $writer, Document $document);

    /**
    * Called when a page is finished, just before being written to the document.
    *
    * @param writer the <CODE>PdfWriter</CODE> for this document
    * @param document the document
    */
    public function onEndPage(PdfWriter $writer, Document $document);

    /**
    * Called when the document is closed.
    * <P>
    * Note that this method is called with the page number equal
    * to the last page plus one.
    *
    * @param writer the <CODE>PdfWriter</CODE> for this document
    * @param document the document
    */
    public function onCloseDocument(PdfWriter $writer, Document $document);

    /**
    * Called when a Paragraph is written.
    * <P>
    * <CODE>paragraphPosition</CODE> will hold the height at which the
    * paragraph will be written to. This is useful to insert bookmarks with
    * more control.
    *
    * @param writer the <CODE>PdfWriter</CODE> for this document
    * @param document the document
    * @param paragraphPosition the position the paragraph will be written to
    */
    public function onParagraph(PdfWriter $writer, Document $document, $paragraphPosition);

    /**
    * Called when a Paragraph is written.
    * <P>
    * <CODE>paragraphPosition</CODE> will hold the height of the end of the paragraph.
    *
    * @param writer the <CODE>PdfWriter</CODE> for this document
    * @param document the document
    * @param paragraphPosition the position of the end of the paragraph
    */
    public function onParagraphEnd(PdfWriter $writer,Document $document, $paragraphPosition);

    /**
    * Called when a Chapter is written.
    * <P>
    * <CODE>position</CODE> will hold the height at which the
    * chapter will be written to.
    *
    * @param writer            the <CODE>PdfWriter</CODE> for this document
    * @param document          the document
    * @param paragraphPosition the position the chapter will be written to
    * @param title             the title of the Chapter
    */
    public function onChapter(PdfWriter $writer,Document $document, $paragraphPosition, Paragraph $title);

    /**
    * Called when the end of a Chapter is reached.
    * <P>
    * <CODE>position</CODE> will hold the height of the end of the chapter.
    *
    * @param writer            the <CODE>PdfWriter</CODE> for this document
    * @param document          the document
    * @param paragraphPosition the position the chapter will be written to
    */
    public function onChapterEnd(PdfWriter $writer,Document $document, $paragraphPosition);

    /**
    * Called when a Section is written.
    * <P>
    * <CODE>position</CODE> will hold the height at which the
    * section will be written to.
    *
    * @param writer            the <CODE>PdfWriter</CODE> for this document
    * @param document          the document
    * @param paragraphPosition the position the section will be written to
    * @param depth				the number depth of the section
    * @param title             the title of the section
    */
    public function onSection(PdfWriter $writer,Document $document, $paragraphPosition, $depth, Paragraph $title);

    /**
    * Called when the end of a Section is reached.
    * <P>
    * <CODE>position</CODE> will hold the height of the section end.
    *
    * @param writer            the <CODE>PdfWriter</CODE> for this document
    * @param document          the document
    * @param paragraphPosition the position the section will be written to
    */
    public function onSectionEnd(PdfWriter $writer,Document $document, $paragraphPosition);

    /**
    * Called when a <CODE>Chunk</CODE> with a generic tag is written.
    * <P>
    * It is usefull to pinpoint the <CODE>Chunk</CODE> location to generate
    * bookmarks, for example.
    *
    * @param writer the <CODE>PdfWriter</CODE> for this document
    * @param document the document
    * @param rect the <CODE>Rectangle</CODE> containing the <CODE>Chunk</CODE>
    * @param text the text of the tag
    */
    public function onGenericTag(PdfWriter $writer, Document $document, Rectangle $rect, $text);


}



?>