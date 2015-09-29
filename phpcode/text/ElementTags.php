<?php /*
 * $Id: ElementTags.php,v 1.1.1.1 2005/09/22 16:08:21 mstaylor Exp $
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

require_once("Chunk.php");
require_once("Element.php");

class ElementTags 
{

    /** the root tag. */
    const ITEXT = "itext";

    /** attribute of the root and annotation tag (also a special tag within a chapter or section) */
    const TITLE = "title";

    /** attribute of the root tag */
    const SUBJECT = "subject";

    /** attribute of the root tag */
    const KEYWORDS = "keywords";

    /** attribute of the root tag */
    const AUTHOR = "author";

    /** attribute of the root tag */
    const CREATIONDATE = "creationdate";

    /** attribute of the root tag */
    const PRODUCER = "producer";

    // Chapters and Sections
    /** the chapter tag */
    const CHAPTER = "chapter";

    /** the section tag */
    const SECTION = "section";

    /** attribute of section/chapter tag */
    const NUMBERDEPTH = "numberdepth";

    /** attribute of section/chapter tag */
    const DEPTH = "depth";

    /** attribute of section/chapter tag */
    const NUMBER = "number";

    /** attribute of section/chapter tag */
    const INDENT = "indent";

    /** attribute of chapter/section/paragraph/table/cell tag */
    const LEFT = "left";

    /** attribute of chapter/section/paragraph/table/cell tag */
    const RIGHT = "right";

    // Phrases, Anchors, Lists and Paragraphs

    /** the phrase tag */
    const PHRASE = "phrase";

    /** the anchor tag */
    const ANCHOR = "anchor";

    /** the list tag */
    const LIST = "list";

    /** the listitem tag */
    const LISTITEM = "listitem";

    /** the paragraph tag */
    const PARAGRAPH = "paragraph";

    /** attribute of phrase/paragraph/cell tag */
    const LEADING = "leading";

    /** attribute of paragraph/image/table tag */
    const ALIGN = "align";

    /** attribute of paragraph */
    const KEEPTOGETHER = "keeptogether";

    /** attribute of anchor tag */
    const NAME = "name";

    /** attribute of anchor tag */
    const REFERENCE = "reference";

    /** attribute of list tag */
    const LISTSYMBOL = "listsymbol";

    /** attribute of list tag */
    const NUMBERED = "numbered";

    /** attribute of the list tag */
    const LETTERED = "lettered";

    /** attribute of list tag */
    const FIRST = "first";

    /** attribute of list tag */
   const SYMBOLINDENT = "symbolindent";

    /** attribute of list tag */
    const INDENTATIONLEFT = "indentationleft";

    /** attribute of list tag */
    const INDENTATIONRIGHT = "indentationright";

    // Chunks

    /** the chunk tag */
    const IGNORE = "ignore";

    /** the chunk tag */
    const ENTITY = "entity";
 
    /** the chunk tag */
    const ID = "id";

    /** the chunk tag */
    const CHUNK = "chunk";

    /** attribute of the chunk tag */
    const ENCODING = "encoding";

    /** attribute of the chunk tag */
    const EMBEDDED = "embedded";

    /** attribute of the chunk/table/cell tag */
    const COLOR = "color";

    /** attribute of the chunk/table/cell tag */
    const RED = "red";

    /** attribute of the chunk/table/cell tag */
    const GREEN = "green";

    /** attribute of the chunk/table/cell tag */
    const BLUE = "blue";

    /** attribute of the chunk tag */
    public static $SUBSUPSCRIPT = NULL;

    /** attribute of the chunk tag */
    public static $LOCALGOTO = NULL;

    /** attribute of the chunk tag */
    public static $REMOTEGOTO = NULL;

    /** attribute of the chunk tag */
    public static $LOCALDESTINATION = NULL;

    /** attribute of the chunk tag */
    public static $GENERICTAG = NULL;

    // tables/cells

    /** the table tag */
    const TABLE = "table";

    /** the cell tag */
    const ROW = "row";

    /** the cell tag */
    const CELL = "cell";

    /** attribute of the table tag */
    const COLUMNS = "columns";

    /** attribute of the table tag */
    const LASTHEADERROW = "lastHeaderRow";

    /** attribute of the table tag */
    const CELLPADDING = "cellpadding";

    /** attribute of the table tag */
    const CELLSPACING = "cellspacing";

    /** attribute of the table tag */
    const OFFSET = "offset";

    /** attribute of the table tag */
    const WIDTHS = "widths";

    /** attribute of the table tag */
    const TABLEFITSPAGE = "tablefitspage";

    /** attribute of the table tag */
    const CELLSFITPAGE = "cellsfitpage";

    /** attribute of the cell tag */
    const HORIZONTALALIGN = "horizontalalign";

    /** attribute of the cell tag */
    const VERTICALALIGN = "verticalalign";

    /** attribute of the cell tag */
    const COLSPAN = "colspan";

    /** attribute of the cell tag */
    const ROWSPAN = "rowspan";

    /** attribute of the cell tag */
    const HEADER = "header";

    /** attribute of the cell tag */
    const NOWRAP = "nowrap";

    /** attribute of the table/cell tag */
    const BORDERWIDTH = "borderwidth";

    /** attribute of the table/cell tag */
    const TOP = "top";

    /** attribute of the table/cell tag */
    const BOTTOM = "bottom";

    /** attribute of the table/cell tag */
    const WIDTH = "width";

    /** attribute of the table/cell tag */
    const BORDERCOLOR = "bordercolor";

    /** attribute of the table/cell tag */
    const BACKGROUNDCOLOR = "backgroundcolor";

    /** attribute of the table/cell tag */
    const BGRED = "bgred";

    /** attribute of the table/cell tag */
    const BGGREEN = "bggreen";

    /** attribute of the table/cell tag */
    const BGBLUE = "bgblue";

    /** attribute of the table/cell tag */
    const GRAYFILL = "grayfill";

    // Misc

    /** the image tag */
    const IMAGE = "image";

    /** attribute of the image and annotation tag */
    const URL = "url";

    /** attribute of the image tag */
    const UNDERLYING = "underlying";

    /** attribute of the image tag */
    const TEXTWRAP = "textwrap";

    /** attribute of the image tag */
    const ALT = "alt";

    /** attribute of the image tag */
    const ABSOLUTEX = "absolutex";

    /** attribute of the image tag */
    const ABSOLUTEY = "absolutey";

    /** attribute of the image tag */
    const PLAINWIDTH = "plainwidth";

    /** attribute of the image tag */
    const PLAINHEIGHT = "plainheight";

    /** attribute of the image tag */
    const SCALEDWIDTH = "scaledwidth";

    /** attribute of the image tag */
    const SCALEDHEIGHT = "scaledheight";

    /** attribute of the image tag */
    const ROTATION = "rotation";

    /** the newpage tag */
    const NEWPAGE = "newpage";

    /** the newpage tag */
    const NEWLINE = "newline";

    /** the annotation tag */
    const ANNOTATION = "annotation";

    /** attribute of the annotation tag */
    const FILE = "file";

    /** attribute of the annotation tag */
    const DESTINATION = "destination";

    /** attribute of the annotation tag */
    const PAGE = "page";

    /** attribute of the annotation tag */
    const NAMED = "named";

    /** attribute of the annotation tag */
    const APPLICATION = "application";

    /** attribute of the annotation tag */
    const PARAMETERS = "parameters";

    /** attribute of the annotation tag */
    const OPERATION = "operation";

    /** attribute of the annotation tag */
    const DEFAULTDIR = "defaultdir";

    /** attribute of the annotation tag */
    const LLX = "llx";

    /** attribute of the annotation tag */
    const LLY = "lly";

    /** attribute of the annotation tag */
    const URX = "urx";

    /** attribute of the annotation tag */
    const URY = "ury";

    /** attribute of the annotation tag */
    const CONTENT = "content";

    // alignment attribute values

    /** the possible value of an alignment attribute */
    const ALIGN_LEFT = "Left";

    /** the possible value of an alignment attribute */
    const ALIGN_CENTER = "Center";

    /** the possible value of an alignment attribute */
    const ALIGN_RIGHT = "Right";

    /** the possible value of an alignment attribute */
    const ALIGN_JUSTIFIED = "Justify";

    /** the possible value of an alignment attribute */
    const ALIGN_JUSTIFIED_ALL = "JustifyAll";

    /** the possible value of an alignment attribute */
    const ALIGN_TOP = "Top";

    /** the possible value of an alignment attribute */
    const ALIGN_MIDDLE = "Middle";

    /** the possible value of an alignment attribute */
    const ALIGN_BOTTOM = "Bottom";

    /** the possible value of an alignment attribute */
    const ALIGN_BASELINE = "Baseline";

    /** the possible value of an alignment attribute */
    const DEFAULT = "Default";

    /** the possible value of an alignment attribute */
    const UNKNOWN = "unknown";

    /** the possible value of an alignment attribute */
    const FONT = "font";

    /** the possible value of an alignment attribute */
    const SIZE = "size";

    /** the possible value of an alignment attribute */
    const STYLE = "fontstyle";

    /** the possible value of a tag */
    const HORIZONTALRULE = "horizontalrule";

    /** the possible value of a tag */
    const PAGE_SIZE  = "pagesize";

    /** the possible value of a tag */
    const ORIENTATION  = "orientation";

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(ElementTags::$initialized == FALSE)
        {
           ElementTags::$SUBSUPSCRIPT = strtolower(Chunk::SUBSUPSCRIPT);
           ElementTags::$LOCALGOTO =  strtolower(Chunk::LOCALGOTO);
           ElementTags::$REMOTEGOTO = strtolower(Chunk::REMOTEGOTO);
           ElementTags::$LOCALDESTINATION = strtolower(Chunk::LOCALDESTINATION);
           ElementTags::$GENERICTAG = strtolower(Chunk::GENERICTAG);
           ElementTags::$initialized = TRUE;
        }
    }

    /**
    * Translates the alignment value.
    *
    * @param   alignment   the alignment value
    * @return  the translated value
    */

    public static function getAlignment($alignment) {
        switch($alignment) {
            case Element::ALIGN_LEFT:
                return ElementTags::ALIGN_LEFT;
            case Element::ALIGN_CENTER:
                return ElementTags::ALIGN_CENTER;
            case Element::ALIGN_RIGHT:
                return ElementTags::ALIGN_RIGHT;
            case Element::ALIGN_JUSTIFIED:
            case Element::ALIGN_JUSTIFIED_ALL:
                return ElementTags::ALIGN_JUSTIFIED;
            case Element::ALIGN_TOP:
                return ElementTags::ALIGN_TOP;
            case Element::ALIGN_MIDDLE:
                return ElementTags::ALIGN_MIDDLE;
            case Element::ALIGN_BOTTOM:
                return ElementTags::ALIGN_BOTTOM;
            case Element::ALIGN_BASELINE:
                return ElementTags::ALIGN_BASELINE;
                default:
                    return ElementTags::DEFAULT;
        }
    }

}


?>