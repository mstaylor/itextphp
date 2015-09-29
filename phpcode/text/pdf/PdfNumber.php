<?PHP
/*
 * $Id: PdfNumber.php,v 1.2 2005/09/29 22:02:44 mstaylor Exp $
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

require_once("PdfObject.php");
require_once("ByteBuffer.php");
/**
* <CODE>PdfNumber</CODE> provides two types of numbers, integer and real.
* <P>
* Integers may be specified by signed or unsigned constants. Reals may only be
* in decimal format.<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 4.3 (page 37).
*
* @see		PdfObject
* @see		BadPdfFormatException
*/

class PdfNumber extends PdfObject
{
    /** actual value of this <CODE>PdfNumber</CODE>, represented as a <CODE>double</CODE> */
    private $value = 0.0;

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0); 
               if (strcmp(gettype($arg1) "string") == 0)
               {
                   construct1argstring($arg1);
               }
               else if (strcmp(gettype($arg1), "integer") == 0)
               {
                   construct1arginteger($arg1);
               }
               else if (strcmp(gettype($arg1), "double") == 0)
               {
                   construct1argdouble($arg1);
               }
               break;
           }
        }
    }

    /**
    * Constructs a <CODE>PdfNumber</CODE>-object.
    *
    * @param		content			value of the new <CODE>PdfNumber</CODE>-object
    */
    public function construct1argstring($content)
    {
        parent::__construct(PdfObject::NUMBER);
        $value = (double)trim($content); 
        setContent($content);
    }

    /**
    * Constructs a new INTEGER <CODE>PdfNumber</CODE>-object.
    *
    * @param		value				value of the new <CODE>PdfNumber</CODE>-object
    */
    public function construct1arginteger($value)
    {
        parent::__construct(PdfObject::NUMBER);
        $this->value = $value;
        setContent($value);
    }

    /**
    * Constructs a new REAL <CODE>PdfNumber</CODE>-object.
    *
    * @param		value				value of the new <CODE>PdfNumber</CODE>-object
    */
    public function construct1argdouble($value)
    {
        parent::__construct(PdfObject::NUMBER);
        $this->value = $value;
        setContent(ByteBuffer::formatDouble($value));
    }

    // methods returning the value of this object

    /**
    * Returns the primitive <CODE>int</CODE> value of this object.
    *
    * @return		a value
    */

    public function intValue() {
        return (integer) $value;
    }

    /**
    * Returns the primitive <CODE>double</CODE> value of this object.
    *
    * @return		a value
    */

    public function doubleValue() {
        return $value;
    }

    public function floatValue() {
        return (float)$value;
    }

    // other methods

    /**
    * Increments the value of the <CODE>PdfNumber</CODE>-object with 1.
    */

    public function increment() {
        $value += 1.0;
        setContent(ByteBuffer::formatDouble($value));
    }

}
?>