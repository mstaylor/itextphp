<?PHP
/*
 * $Id: PdfBoolean.php,v 1.2 2005/10/25 21:06:22 mstaylor Exp $
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
require_once("BadPdfFormatException.php");

/**
* <CODE>PdfBoolean</CODE> is the boolean object represented by the keywords <VAR>true</VAR> or <VAR>false</VAR>.
* <P>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 4.2 (page 37).
*
* @see		PdfObject
* @see		BadPdfFormatException
*/


class PdfBoolean extends PdfObject
{

    // static membervariables (possible values of a boolean object)
    public static $PDFTRUE = NULL;
    public static $PDFFALSE = NULL;
    /** A possible value of <CODE>PdfBoolean</CODE> */
    const TRUE = "true";

    /** A possible value of <CODE>PdfBoolean</CODE> */
    const FALSE = "false";

    // membervariables

    /** the boolean value of this object */
    private $value = FALSE;

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfBoolean::$initialized == FALSE)
        {
            PdfBoolean::$PDFTRUE = new PdfBoolean(TRUE);
            PdfBoolean::$PDFFALSE = new PdfBoolean(FALSE);
            PdfBoolean::$initialized = TRUE;
        }
    }


    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($is_string($arg1) == TRUE)
                    construct1argString($arg1);
                else if (is_bool($arg1) == TRUE)
                    construct1argBoolean($arg1);
                break;
            }
        }
    }


    /**
    * Constructs a <CODE>PdfBoolean</CODE>-object.
    *
    * @param		value			the value of the new <CODE>PdfObject</CODE>
    */

    private function construct1argBoolean($value) {
        parent::__construct(PdfObject::BOOLEAN);
        if ($value == TRUE) {
            setContent(TRUE);
        }
        else {
            setContent(FALSE);
        }
        $this->value = $value;
    }

    /**
    * Constructs a <CODE>PdfBoolean</CODE>-object.
    *
    * @param		value			the value of the new <CODE>PdfObject</CODE>, represented as a <CODE>String</CODE>
    *
    * @throws		BadPdfFormatException	thrown if the <VAR>value</VAR> isn't '<CODE>true</CODE>' or '<CODE>false</CODE>'
    */

    private function construct1argString($value){
        parent::__construct(PdfObject::BOOLEAN, $value);
        if (strcmp($value, PdfBoolean::TRUE) == 0) {
            $this->value = TRUE;
        }
        else if (strcmp($value, PdfBoolean::FALSE) == 0) {
            $this->value = FALSE;
        }
        else {
            throw new BadPdfFormatException("The value has to be 'true' of 'false', instead of '" . (string)$value . "'.");
        }
    }
}

PdfBoolean::initializeStatics();

?>