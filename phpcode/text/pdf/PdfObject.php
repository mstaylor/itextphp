<?PHP
/*
 * $Id: PdfObject.php,v 1.2 2005/10/14 22:19:25 mstaylor Exp $
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

require_once("PRIndirectReference.php");
require_once("PdfEncodings.php");
require_once("../io/OutputStream.php");

abstract class PdfObject
{
    // static membervariables (all the possible types of a PdfObject)

    /** a possible type of <CODE>PdfObject</CODE> */
    const BOOLEAN = 1;

    /** a possible type of <CODE>PdfObject</CODE> */
    const NUMBER = 2;

    /** a possible type of <CODE>PdfObject</CODE> */
    const STRING = 3;

    /** a possible type of <CODE>PdfObject</CODE> */
    const NAME = 4;

    /** a possible type of <CODE>PdfObject</CODE> */
    const ARRAY = 5;

    /** a possible type of <CODE>PdfObject</CODE> */
    const DICTIONARY = 6;

    /** a possible type of <CODE>PdfObject</CODE> */
    const STREAM = 7;

    /** a possible type of <CODE>PdfObject</CODE> */
    const aNULL = 8;

    /** a possible type of <CODE>PdfObject</CODE> */
    const INDIRECT = 10;

    /** This is an empty string used for the <CODE>PdfNull</CODE>-object and for an empty <CODE>PdfString</CODE>-object. */
    const NOTHING = "";

    /** This is the default encoding to be used for converting Strings into bytes and vice versa.
    * The default encoding is PdfDocEncoding.
    */
    const TEXT_PDFDOCENCODING = "PDF";

    /** This is the encoding to be used to output text in Unicode. */
    const TEXT_UNICODE = "UnicodeBig";

    // membervariables

    /** the content of this <CODE>PdfObject</CODE> */
    protected $bytes;

    /** the type of this <CODE>PdfObject</CODE> */
    protected $type;

    /**
    * Holds value of property indRef.
    */
    protected $indRef;


    // constructors

    protected function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 2:
           {
               $type = func_get_arg(0); 
               $content = func_get_arg(1);
               if (is_array($content) == FALSE)
               {
                   construct2args($type,$content);
               }
               else
               {
                   construct2argsbyte($type,$content);
               }
               break;
           }
           case 1:
           {
               $type = func_get_arg(0); 
               construct1arg($type);
               break;
           }
        }

    }

    /**
    * Constructs a <CODE>PdfObject</CODE> of a certain <VAR>type</VAR> without any <VAR>content</VAR>.
    *
    * @param		type			type of the new <CODE>PdfObject</CODE>
    */
    private function construct1arg($type)
    {
        $this->type = $type;
    }

    /**
    * Constructs a <CODE>PdfObject</CODE> of a certain <VAR>type</VAR> with a certain <VAR>content</VAR>.
    *
    * @param		type			type of the new <CODE>PdfObject</CODE>
    * @param		content			content of the new <CODE>PdfObject</CODE> as a <CODE>String</CODE>.
    */
    private function construct2args($type, $content)
    {
        $this->type = $type;
        $bytes = PdfEncodings::convertToBytes($content, NULL);
    }

    /**
    * Constructs a <CODE>PdfObject</CODE> of a certain <VAR>type</VAR> with a certain <VAR>content</VAR>.
    *
    * @param		type			type of the new <CODE>PdfObject</CODE>
    * @param		bytes			content of the new <CODE>PdfObject</CODE> as an array of <CODE>byte</CODE>.
    */
    private function construct2argsbyte($type, $bytes)
    {
        $this->type = $type;
        $this->bytes = $bytes;

    }

    // methods dealing with the content of this object

    /**
    * Writes the PDF representation of this <CODE>PdfObject</CODE> as an array of <CODE>byte</CODE>s to the writer.
    * @param writer for backwards compatibility
    * @param os the outputstream to write the bytes to.
    * @throws IOException
    */
    public void toPdf($writer, $os){
        if (bytes != null)
            os.write(bytes);
    }

    /**
    * Gets the presentation of this object in a byte array
    * @return a byte array
    */
    public function getBytes() {
        return $bytes;
    }

    /**
    * Can this object be in an object stream?
    * @return true if this object can be in an object stream.
    */
    public function canBeInObjStm() {
        return ($type >= 1 && $type <= 6) || $type == 8;
    }

    /**
    * Returns the length of the PDF representation of the <CODE>PdfObject</CODE>.
    * <P>
    * In some cases, namely for <CODE>PdfString</CODE> and <CODE>PdfStream</CODE>,
    * this method differs from the method <CODE>length</CODE> because <CODE>length</CODE>
    * returns the length of the actual content of the <CODE>PdfObject</CODE>.</P>
    * <P>
    * Remark: the actual content of an object is in most cases identical to its representation.
    * The following statement is always true: length() &gt;= pdfLength().</P>
    *
    * @return		a length
    */

//    public int pdfLength() {
//        return toPdf(null).length;
//    }

    /**
    * Returns the <CODE>String</CODE>-representation of this <CODE>PdfObject</CODE>.
    *
    * @return		a <CODE>String</CODE>
    */

    public function toString() {
        if ($bytes == NULL)
            return parent::toString();
        else
            return PdfEncodings::convertToString(bytes, NULL);
    }

    /**
    * Returns the length of the actual content of the <CODE>PdfObject</CODE>.
    * <P>
    * In some cases, namely for <CODE>PdfString</CODE> and <CODE>PdfStream</CODE>,
    * this method differs from the method <CODE>pdfLength</CODE> because <CODE>pdfLength</CODE>
    * returns the length of the PDF representation of the object, not of the actual content
    * as does the method <CODE>length</CODE>.</P>
    * <P>
    * Remark: the actual content of an object is in some cases identical to its representation.
    * The following statement is always true: length() &gt;= pdfLength().</P>
    *
    * @return		a length
    */

    public function length() {
        return strlen(toString());
    }


   /**
   * Changes the content of this <CODE>PdfObject</CODE>.
   *
   * @param		content			the new content of this <CODE>PdfObject</CODE>
   */

    protected function setContent($content) {
        $bytes = PdfEncodings::convertToBytes($content, NULL);
    }

    // methods dealing with the type of this object

    /**
    * Returns the type of this <CODE>PdfObject</CODE>.
    *
    * @return		a type
    */

    public function type() {
        return $type;
    }

    /**
    * Checks if this <CODE>PdfObject</CODE> is of the type <CODE>PdfNull</CODE>.
    *
    * @return		<CODE>true</CODE> or <CODE>false</CODE>
    */

    public function isNull() {
        return ($this->type == aNULL);
    }

    /**
    * Checks if this <CODE>PdfObject</CODE> is of the type <CODE>PdfBoolean</CODE>.
    *
    * @return		<CODE>true</CODE> or <CODE>false</CODE>
    */

    public function isBoolean() {
        return ($this->type == BOOLEAN);
    }

    /**
    * Checks if this <CODE>PdfObject</CODE> is of the type <CODE>PdfNumber</CODE>.
    *
    * @return		<CODE>true</CODE> or <CODE>false</CODE>
    */

    public function isNumber() {
        return ($this->type == NUMBER);
    }

    /**
    * Checks if this <CODE>PdfObject</CODE> is of the type <CODE>PdfString</CODE>.
    *
    * @return		<CODE>true</CODE> or <CODE>false</CODE>
    */

    public function isString() {
        return ($this->type == STRING);
    }

    /**
    * Checks if this <CODE>PdfObject</CODE> is of the type <CODE>PdfName</CODE>.
    *
    * @return		<CODE>true</CODE> or <CODE>false</CODE>
    */

    public function isName() {
        return ($this->type == NAME);
    }

    /**
    * Checks if this <CODE>PdfObject</CODE> is of the type <CODE>PdfArray</CODE>.
    *
    * @return		<CODE>true</CODE> or <CODE>false</CODE>
    */

    public function isArray() {
        return ($this->type == ARRAY);
    }

    /**
    * Checks if this <CODE>PdfObject</CODE> is of the type <CODE>PdfDictionary</CODE>.
    *
    * @return		<CODE>true</CODE> or <CODE>false</CODE>
    */

    public function isDictionary() {
        return ($this->type == DICTIONARY);
    }

    /**
    * Checks if this <CODE>PdfObject</CODE> is of the type <CODE>PdfStream</CODE>.
    *
    * @return		<CODE>true</CODE> or <CODE>false</CODE>
    */

    public function isStream() {
        return ($this->type == STREAM);
    }

    /**
    * Checks if this is an indirect object.
    * @return true if this is an indirect object
    */
    public function isIndirect() {
        return ($this->type == INDIRECT);
    }

    /**
    * Getter for property indRef.
    * @return Value of property indRef.
    */
    public function getIndRef() {
        return $this->indRef;
    }

    /**
     * Setter for property indRef.
     * @param indRef New value of property indRef.
     */
    public void setIndRef($indRef) {
        $this->indRef = $indRef;
    }

}

?>