<?PHP
/*
 * $Id: PdfArray.php,v 1.2 2005/10/11 21:19:32 mstaylor Exp $
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
require_once("PdfObject.php");
require_once("PdfWriter.php");
require_once("PdfNumber.php");

/**
* <CODE>PdfArray</CODE> is the PDF Array object.
* <P>
* An array is a sequence of PDF objects. An array may contain a mixture of object types.
* An array is written as a left square bracket ([), followed by a sequence of objects,
* followed by a right square bracket (]).<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 4.6 (page 40).
*
* @see		PdfObject
*/

class PdfArray extends PdfObject
{

    // membervariables

    /** this is the actual array of PdfObjects */
    protected $arrayList = array();

    // constructors

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
                if ($arg1 instanceof PdfObject)
                    construct1argPdfObject($arg1);
                else if ($arg1 instanceof PdfArray)
                    construct1argPdfArray($arg1);
                else if (is_array($arg1) == TRUE)
                    construct1argArray($arg1);
                break;
            }
        }
    }

    /**
    * Constructs an empty <CODE>PdfArray</CODE>-object.
    */
    private function construct0args()
    {
        parent::_construct(PdfObject::ARRAY);
        arrayList = array();
    }

    /**
    * Constructs an <CODE>PdfArray</CODE>-object, containing 1 <CODE>PdfObject</CODE>.
    *
    * @param	object		a <CODE>PdfObject</CODE> that has to be added to the array
    */
    private function construct1argPdfObject(PdfObject $object) {
        parent::__construct(PdfObject::ARRAY);
        arrayList = array();
        array_push($arrayList, $object);
    }

    /**
    * Constructs an <CODE>PdfArray</CODE>-object, containing all the <CODE>PdfObject</CODE>s in a given <CODE>PdfArray</CODE>.
    *
    * @param	array		a <CODE>PdfArray</CODE> that has to be added to the array
    */
    private function construct1argPdfArray(PdfArray $array) {
        parent::__construct(PdfObject::ARRAY);
        $arrayList = array_merge(array(), $array->getArrayList());
    }

    private function construct1argArray(array $values)
    {
        parent::__construct(PdfObject::ARRAY);
        arrayList = array();
        add($values);
    }

    // methods overriding some methods in PdfObject

    /**
    * Returns the PDF representation of this <CODE>PdfArray</CODE>.
    *
    * @return		an array of <CODE>byte</CODE>s
    */
    public function toPdf(PdfWriter $writer, OutputStream $os) {

        $os->write('[');
        $object = NULL;
        $type = 0;
        $i = 0;
        foreach ($arrayList as &$object) {
            if ($i == 0)
                $object->toPdf($writer, $os);
            else
            {
            $type = $object->type();
            if ($type != PdfObject::ARRAY && $type != PdfObject::DICTIONARY && $type != PdfObject::NAME && $type != PdfObject::STRING)
                $os->write(' ');
            $object->toPdf($writer, $os);
            }
            $i++;
        }
        $os->write(']');
    }

     // methods concerning the ArrayList-membervalue

    /**
    * Returns an ArrayList containing <CODE>PdfObject</CODE>s.
    *
    * @return		an ArrayList
    */

    public function getArrayList() {
        return $arrayList;
    }

    /**
    * Returns the number of entries in the array.
    *
    * @return		the size of the ArrayList
    */

    public function size() {
        return count($arrayList);
    }


    public function add()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof PdfObject)
                    return add1argPdfObject($arg1);
                else if (is_array($arg1) == TRUE)
                    return add1argArray($arg1);
                break;
            }
        }
    }


     /**
    * Adds a <CODE>PdfObject</CODE> to the <CODE>PdfArray</CODE>.
    *
    * @param		object			<CODE>PdfObject</CODE> to add
    * @return		<CODE>true</CODE>
    */
    private function add1argPdfObject(PdfObject $object) {
        return array_push($arrayList, $object);
    }

    private function add1argArray(array $values) {
        for ($k = 0; $k < count($values); ++$k)
            array_push($arrayList, new PdfNumber($values[$k]));
        return TRUE;
    }

    /**
    * Adds a <CODE>PdfObject</CODE> to the <CODE>PdfArray</CODE>.
    * <P>
    * The newly added object will be the first element in the <CODE>ArrayList</CODE>.
    *
    * @param		object			<CODE>PdfObject</CODE> to add
    */
    public function addFirst(PdfObject $object) {
        $arrayList[0] = $object;
    }

    /**
    * Checks if the <CODE>PdfArray</CODE> already contains a certain <CODE>PdfObject</CODE>.
    *
    * @param		object			<CODE>PdfObject</CODE> to check
    * @return		<CODE>true</CODE>
    */

    public function contains(PdfObject $object) {
        return in_array($object, $arrayList);
    }

}

?>