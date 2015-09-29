<?PHP
/*
 * $Id: PdfContents.php,v 1.2 2005/11/23 19:25:33 mstaylor Exp $
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

require_once("../DocWriter.php");
require_once("../Document.php");
require_once("../Rectangle.php");
require_once("PdfStream.php");
require_once("PdfContentByte.php");
require_once("BadPdfFormatException.php");
require_once("PdfName.php");
require_once("PdfNumber.php");

/**
* <CODE>PdfContents</CODE> is a <CODE>PdfStream</CODE> containing the contents (text + graphics) of a <CODE>PdfPage</CODE>.
*/



class PdfContents extends PdfStream
{

    protected static $SAVESTATE = NULL;
    protected static $RESTORESTATE = NULL;
    protected static $ROTATE90 = NULL;
    protected static $ROTATE180 = NULL;
    protected static $ROTATE270 = NULL;
    protected static $ROTATEFINAL = NULL;

    public static $initialized = FALSE;


    public static function initializeStatics()
    {
        if(PdfContents::$initialized == FALSE)
        {
            PdfContents::$SAVESTATE = DocWriter.getISOBytes("q\n");
            PdfContents::$RESTORESTATE = DocWriter.getISOBytes("Q\n");
            PdfContents::$ROTATE90 = DocWriter::getISOBytes("0 1 -1 0 ");
            PdfContents::$ROTATE180 = DocWriter::getISOBytes("-1 0 0 -1 ");
            PdfContents::$ROTATE270 = DocWriter::getISOBytes("0 -1 1 0 ");
            PdfContents::$ROTATEFINAL = DocWriter::getISOBytes(" cm\n");
            PdfContents::$initialized = TRUE;
        }
    }


    // constructor

    /**
    * Constructs a <CODE>PdfContents</CODE>-object, containing text and general graphics.
    *
    * @param under the direct content that is under all others
    * @param content the graphics in a page
    * @param text the text in a page
    * @param secondContent the direct content that is over all others
    * @throws BadPdfFormatException on error
    */

    public function __construct(PdfContentByte $under, PdfContentByte $content, PdfContentByte $text, PdfContentByte $secondContent, Rectangle $page) 
    {
        parent::__construct();
        try {
            //OutputStream out = null;
            //streamBytes = new ByteArrayOutputStream();
            if (Document::compress== TRUE)
            {
                $compressed = TRUE;
                //out = new DeflaterOutputStream(streamBytes);
            }
            //else
              //  out = streamBytes;
            $out = itextphp_bytes_create(1);
            $rotation = $page->getRotation();
            switch ($rotation) {
                case 90:
                    itextphp_bytes_append($out, PdfContents::$ROTATE90);
                    itextphp_bytes_append($out, DocWriter::getISOBytes(ByteBuffer::formatDouble($page->top())));
                    itextphp_bytes_append($out, itextphp_bytes_createfromRaw(' '));
                    itextphp_bytes_append($out, itextphp_bytes_createfromRaw('0'));
                    itextphp_bytes_append($out, PdfContents::$ROTATEFINAL);
                    break;
                case 180:
                    itextphp_bytes_append($out, PdfContents::$ROTATE180);
                    itextphp_bytes_append($out, DocWriter::getISOBytes(ByteBuffer::formatDouble($page->right())));
                    itextphp_bytes_append($out, itextphp_bytes_createfromRaw(' '));
                    itextphp_bytes_append($out, DocWriter::getISOBytes(ByteBuffer::formatDouble($page->top())));
                    itextphp_bytes_append($out, PdfContents::$ROTATEFINAL);
                    break;
                case 270:
                    itextphp_bytes_append($out, PdfContents::$ROTATE270);
                    itextphp_bytes_append($out, itextphp_bytes_createfromRaw('0'));
                    itextphp_bytes_append($out, itextphp_bytes_createfromRaw(' '));
                    itextphp_bytes_append($out, DocWriter::getISOBytes(ByteBuffer::formatDouble($page->right())));
                    itextphp_bytes_append($out, PdfContents::$ROTATEFINAL);
                    break;
            }
            if ($under->size() > 0) {
                itextphp_bytes_append($out, PdfContents::$SAVESTATE);
                $under->getInternalBuffer()->writeTo($out);
                itextphp_bytes_append($out, PdfContents::$RESTORESTATE);
            }
            if ($content->size() > 0) {
                itextphp_bytes_append($out, PdfContents::$SAVESTATE);
                $content->getInternalBuffer()->writeTo($out);
                itextphp_bytes_append($out, PdfContents::$RESTORESTATE);
            }
            if ($text != NULL) {
                itextphp_bytes_append($out, PdfContents::$SAVESTATE);
                $text->getInternalBuffer().writeTo($out);
                itextphp_bytes_append($out, PdfContents::$RESTORESTATE);
            }
            if ($secondContent->size() > 0) {
                $secondContent->getInternalBuffer()->writeTo($out);
            }

            if (Document::compress== TRUE)
            {
                $val = itextphp_getAnsiString($out);
                //compress val and recreate byte array resource
                $out = itextphp_bytes_createfromRaw(gzdeflate($val));
            }
                    }
        catch (Exception $e) {
            throw new BadPdfFormatException($e->getMessage());
        }
        put(PdfName::$LENGTH, new PdfNumber(itextphp_bytes_getSize($streamBytes)));
        if ($compressed == TRUE)
            put(PdfName::$FILTER, PdfName::$FLATEDECODE);
    }


}

PdfContents::initializeStatics();


?>