<?PHP
/*
 * $Id: Element.php,v 1.1.1.1 2005/09/22 16:08:19 mstaylor Exp $
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
/**
 * Interface for a text element.
 * <P>
 * Remark: I looked at the interface javax.swing.text.Element, but
 * I decided to write my own text-classes for two reasons:
 * <OL>
 * <LI>The javax.swing.text-classes may be very generic, I think
 * they are overkill: they are to heavy for what they have to do.
 * <LI>A lot of people using iText (formerly known as rugPdf), still use JDK1.1.x.
 * I try to keep the Java2 requirements limited to the Collection classes
 * (I think they're really great). However, if I use the javax.swing.text
 * classes, it will become very difficult to downgrade rugPdf.
 * </OL>
 *
 * @see		Anchor
 * @see		Cell
 * @see		Chapter
 * @see		Chunk
 * @see		Graphic
 * @see		Header
 * @see		Image
 * @see		Jpeg
 * @see		List
 * @see		ListItem
 * @see		Meta
 * @see		Paragraph
 * @see		Phrase
 * @see		Rectangle
 * @see		Row
 * @see		Section
 * @see		Table
 */

interface Element {

    // static membervariables (meta information)

    /** This is a possible type of <CODE>Element</CODE>. */
    const HEADER = 0;

    /** This is a possible type of <CODE>Element</CODE>. */
    const TITLE = 1;

    /** This is a possible type of <CODE>Element</CODE>. */
    const SUBJECT = 2;

    /** This is a possible type of <CODE>Element</CODE>. */
    const KEYWORDS = 3;

    /** This is a possible type of <CODE>Element</CIDE>. */
    const AUTHOR = 4;

    /** This is a possible type of <CODE>Element</CIDE>. */
    const PRODUCER = 5;

    /** This is a possible type of <CODE>Element</CIDE>. */
    const CREATIONDATE = 6;

    /** This is a possible type of <CODE>Element</CIDE>. */
    const CREATOR = 7;

    // static membervariables (content)

    /** This is a possible type of <CODE>Element</CODE>. */
    const CHUNK = 10;

    /** This is a possible type of <CODE>Element</CODE>. */
    const PHRASE = 11;

    /** This is a possible type of <CODE>Element</CODE>. */
    const PARAGRAPH = 12;

    /** This is a possible type of <CODE>Element</CODE> */
    const SECTION = 13;

    /** This is a possible type of <CODE>Element</CODE> */
    const aLIST = 14;

    /** This is a possible type of <CODE>Element</CODE> */
    const LISTITEM = 15;

    /** This is a possible type of <CODE>Element</CODE> */
    const CHAPTER = 16;

    /** This is a possible type of <CODE>Element</CODE> */
    const ANCHOR = 17;

    // static membervariables (tables)

    /** This is a possible type of <CODE>Element</CODE>. */
    const CELL = 20;

    /** This is a possible type of <CODE>Element</CODE>. */
    const ROW = 21;

    /** This is a possible type of <CODE>Element</CODE>. */
    const TABLE = 22;

    /** This is a possible type of <CODE>Element</CODE>. */
    const PTABLE = 23;

    // static membervariables (annotations)

    /** This is a possible type of <CODE>Element</CODE>. */
    const ANNOTATION = 29;

    // static membervariables (geometric figures)

    /** This is a possible type of <CODE>Element</CODE>. */
    const RECTANGLE = 30;

    /** This is a possible type of <CODE>Element</CODE>. */
    const JPEG = 32;

    /** This is a possible type of <CODE>Element</CODE>. */
    const IMGRAW = 34;

    /** This is a possible type of <CODE>Element</CODE>. */
    const IMGTEMPLATE = 35;

    /** This is a possible type of <CODE>Element</CODE>. */
    const GRAPHIC = 39;

    /** This is a possible type of <CODE>Element</CODE>. */
    const MULTI_COLUMN_TEXT = 40;

    // static membervariables (alignment)

    /**
    * A possible value for paragraph alignment.  This
    * specifies that the text is aligned to the left
    * indent and extra whitespace should be placed on
    * the right.
    */
    const ALIGN_UNDEFINED = -1;

    /**
    * A possible value for paragraph alignment.  This
    * specifies that the text is aligned to the left
    * indent and extra whitespace should be placed on
    * the right.
    */
    const ALIGN_LEFT = 0;

    /**
    * A possible value for paragraph alignment.  This
    * specifies that the text is aligned to the center
    * and extra whitespace should be placed equally on
    * the left and right.
    */
    const ALIGN_CENTER = 1;

    /**
    * A possible value for paragraph alignment.  This
    * specifies that the text is aligned to the right
    * indent and extra whitespace should be placed on
    * the left.
    */
    const ALIGN_RIGHT = 2;

    /**
    * A possible value for paragraph alignment.  This
    * specifies that extra whitespace should be spread
    * out through the rows of the paragraph with the
    * text lined up with the left and right indent
    * except on the last line which should be aligned
    * to the left.
    */
    const ALIGN_JUSTIFIED = 3;

    /**
    * A possible value for vertical alignment.
    */

    const ALIGN_TOP = 4;

    /**
    * A possible value for vertical alignment.
    */

    const ALIGN_MIDDLE = 5;

    /**
    * A possible value for vertical alignment.
    */

    const ALIGN_BOTTOM = 6;

    /**
    * A possible value for vertical alignment.
    */
    const ALIGN_BASELINE = 7;

    /**
    * Does the same as ALIGN_JUSTIFIED but the last line is also spread out.
    */
    const ALIGN_JUSTIFIED_ALL = 8;

    // static member variables for CCITT compression

    /** Pure two-dimensional encoding (Group 4)
    */
    const CCITTG4 = 0x100;
    /** Pure one-dimensional encoding (Group 3, 1-D)
    */
    const CCITTG3_1D = 0x101;
    /** Mixed one- and two-dimensional encoding (Group 3, 2-D)
    */
    const CCITTG3_2D = 0x102;
    /** A flag indicating whether 1-bits are to be interpreted as black pixels
    *  and 0-bits as white pixels,
    */
    const CCITT_BLACKIS1 = 1;
    /** A flag indicating whether the filter expects extra 0-bits before each
    *  encoded line so that the line begins on a byte boundary.
    */
    const CCITT_ENCODEDBYTEALIGN = 2;
    /** A flag indicating whether end-of-line bit patterns are required to be
    *  present in the encoding.
    */
    const CCITT_ENDOFLINE = 4;
    /** A flag indicating whether the filter expects the encoded data to be
    *  terminated by an end-of-block pattern, overriding the Rows
    *  parameter. The use of this flag will set the key /EndOfBlock to false.
    */
    const CCITT_ENDOFBLOCK = 8;

    // methods

    /**
    * Processes the element by adding it (or the different parts) to an
    * <CODE>ElementListener</CODE>.
    *
    * @param	listener	an <CODE>ElementListener</CODE>
    * @return	<CODE>true</CODE> if the element was processed successfully
    */

    public function process($listener);

    /**
    * Gets the type of the text element.
    *
    * @return	a type
    */

    public function type();

    /**
    * Gets all the chunks in this element.
    *
    * @return	an <CODE>ArrayList</CODE>
    */

    public function getChunks();

    /**
    * Gets the content of the text element.
    *
    * @return	a type
    */

    public function toString();

}
?>