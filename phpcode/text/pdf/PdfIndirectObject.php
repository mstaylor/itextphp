<?PHP
/*
 * $Id: PdfIndirectObject.php,v 1.2 2005/11/14 22:01:59 mstaylor Exp $
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


require_once("../../exceptions/IOException.php");
require_once("../../io/OutputStream.php");
require_once("../DocWriter.php");
require_once("PdfObject.php");
require_once("PdfWriter.php");
require_once("PdfIndirectReference.php");
require_once("PdfEncryption.php");


/**
* <CODE>PdfIndirectObject</CODE> is the Pdf indirect object.
* <P>
* An <I>indirect object</I> is an object that has been labeled so that it can be referenced by
* other objects. Any type of <CODE>PdfObject</CODE> may be labeled as an indirect object.<BR>
* An indirect object consists of an object identifier, a direct object, and the <B>endobj</B>
* keyword. The <I>object identifier</I> consists of an integer <I>object number</I>, an integer
* <I>generation number</I>, and the <B>obj</B> keyword.<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 4.10 (page 53).
*
* @see		PdfObject
* @see		PdfIndirectReference
*/


class PdfIndirectObject
{

    // membervariables

    /** The object number */
    protected $number = 0;

    /** the generation number */
    protected $generation = 0;

    protected static $STARTOBJ = NULL;
    protected static $ENDOBJ = NULL;
    protected static $SIZEOBJ = 0;
    protected $object = NULL;//PdfObject
    protected $writer = NULL;//PdfWriter

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfIndirectObject::$initialized == FALSE)
        {
            PdfIndirectObject::$STARTOBJ = DocWriter::getISOBytes(" obj");
            PdfIndirectObject::$ENDOBJ = DocWriter::getISOBytes("\nendobj\n");
            PdfIndirectObject::$SIZEOBJ = itextphp_bytes_getSize(PdfIndirectObject::$STARTOBJ) + itextphp_bytes_getSize(PdfIndirectObject::$ENDOBJ);
            PdfIndirectObject::$initialized = TRUE;
        }
    }


    // constructors
    public functin __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if (is_integer($arg1) == TRUE && $arg2 instanceof PdfObject && $arg3 instanceof PdfWriter)
                    construct3argsInteger($arg1, $arg2, $arg3);
                else if ($arg1 instanceof PdfIndirectReference && $arg2 instanceof PdfObject && $arg3 instanceof PdfWriter)
                    construct3argsPdfIndirectReference($arg1, $arg2, $arg3);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && $arg3 instanceof PdfObject && $arg4 instanceof PdfWriter)
                    construct4args($arg1, $arg2, $arg3, $arg4);
                break;
            }
        }
    }


    /**
    * Constructs a <CODE>PdfIndirectObject</CODE>.
    *
    * @param		number			the object number
    * @param		object			the direct object
    */

    private function construct3argsInteger($number, PdfObject $object, PdfWriter $writer) {
        construct4args($number, 0, $object, $writer);
    }

    private function construct3argsPdfIndirectReference(PdfIndirectReference $ref, PdfObject $object, PdfWriter $writer) {
        construct4args($ref->getNumber(),$ref->getGeneration(),$object,$writer);
    }

    /**
    * Constructs a <CODE>PdfIndirectObject</CODE>.
    *
    * @param		number			the object number
    * @param		generation		the generation number
    * @param		object			the direct object
    */

    private function construct4args($number, $generation, PdfObject $object, PdfWriter $writer) {
        $this->writer = $writer;
        $this->number = $number;
        $this->generation = $generation;
        $this->object = $object;
        $crypto = NULL;//PdfEncryption
        if ($writer != NULL)
            $crypto = $writer->getEncryption();
        if ($crypto != NULL) {
            $crypto->setHashKey($number, $generation);
        }
    }

    // methods

    /**
    * Return the length of this <CODE>PdfIndirectObject</CODE>.
    *
    * @return		the length of the PDF-representation of this indirect object.
    */

    //    public int length() {
    //        if (isStream)
    //            return bytes.size() + SIZEOBJ + stream.getStreamLength(writer);
    //        else
    //            return bytes.size();
    //    }


    /**
    * Returns a <CODE>PdfIndirectReference</CODE> to this <CODE>PdfIndirectObject</CODE>.
    *
    * @return		a <CODE>PdfIndirectReference</CODE>
    */

    public function getIndirectReference() {
        return new PdfIndirectReference($object->type(), $number, $generation);
    }


    /**
    * Writes eficiently to a stream
    *
    * @param os the stream to write to
    * @throws IOException on write error
    */
    function writeTo(OutputStream $os) 
    {
        $os->write(DocWriter::getISOBytes((string)$number));
        $os->write(' ');
        $os->write(DocWriter::getISOBytes((string)$generation));
        $os->write(PdfIndirectObject::$STARTOBJ);
        $type = $object->type();
        if ($type != PdfObject::ARRAY && $type != PdfObject::DICTIONARY && $type != PdfObject::NAME && $type != PdfObject::STRING)
            $os->write(' ');
        $object->toPdf($writer, $os);
        $os->write(PdfIndirectObject::$ENDOBJ);
    }



}

PdfIndirectObject::initializeStatics();




?>