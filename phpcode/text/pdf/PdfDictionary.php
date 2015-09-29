<?PHP
/*
 * $Id: PdfDictionary.php,v 1.2 2005/09/29 22:02:44 mstaylor Exp $
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
require_once("PdfName.php");
require_once("PdfWriter.php");
/**
* <CODE>PdfDictionary</CODE> is the Pdf dictionary object.
* <P>
* A dictionary is an associative table containing pairs of objects. The first element
* of each pair is called the <I>key</I> and the second element is called the <I>value</I>.
* Unlike dictionaries in the PostScript language, a key must be a <CODE>PdfName</CODE>.
* A value can be any kind of <CODE>PdfObject</CODE>, including a dictionary. A dictionary is
* generally used to collect and tie together the attributes of a complex object, with each
* key-value pair specifying the name and value of an attribute.<BR>
* A dictionary is represented by two left angle brackets (<<), followed by a sequence of
* key-value pairs, followed by two right angle brackets (>>).<BR>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 4.7 (page 40-41).
* <P>
*
* @see		PdfObject
* @see		PdfName
* @see		BadPdfFormatException
*/

class PdfDictionary extends PdfObject
{
    // static membervariables (types of dictionary's)

    /** This is a possible type of dictionary */
    public static $FONT = NULL;

    /** This is a possible type of dictionary */
    public static $OUTLINES = NULL;

    /** This is a possible type of dictionary */
    public static $PAGE = NULL;

    /** This is a possible type of dictionary */
    public static $PAGES = NULL;

    /** This is a possible type of dictionary */
    public static $CATALOG = NULL;

    // membervariables

    /** This is the type of this dictionary */
    private $dictionaryType = NULL;

    /** This is the hashmap that contains all the values and keys of the dictionary */
    protected $hashMap;

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfDictionary::$initialized == FALSE)
        {
            PdfDictionary::$FONT = PdfName::$FONT;
            PdfDictionary::$OUTLINES = PdfName::$OUTLINES;
            PdfDictionary::$PAGE = PdfName::$PAGE;
            PdfDictionary::$PAGES = PdfName::$PAGES;
            PdfDictionary::$CATALOG = PdfName::$CATALOG;
            PdfDictionary::$initialized = TRUE;
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
                construct1arg($arg1);
                break;
            }
            default:
                construct0args();
        }
    }

    // constructors

    /**
    * Constructs an empty <CODE>PdfDictionary</CODE>-object.
    */
    private function construct0args()
    {
        parent::_construct(PdfObject::DICTIONARY)
        hashMap = array();, OutputStream os
    }


    /**
    * Constructs a <CODE>PdfDictionary</CODE>-object of a certain type.
    *
    * @param		type	a <CODE>PdfName</CODE>
    */
    private function construct1arg(PdfName $type)
    {
        construct0args();
        $dictionaryType = $type;
        put(PdfName::$TYPE, $dictionaryType);
    }

     // methods overriding some methods in PdfObject

    /**
    * Returns the PDF representation of this <CODE>PdfDictionary</CODE>.
    *
    * @return		an array of <CODE>byte</CODE>
    */

    public void toPdf(PdfWriter $writer, OutputStream $os) 
    {
        $os->write('<');
        $os->write('<');

        // loop over all the object-pairs in the HashMap

        $value = NULL;
        $type = 0;
        foreach (array_keys($hashMap) as &$key) {
            $value = $hashMap[$key];
            $key->toPdf($writer, $os);
            $type = $value->type();
            if ($type != PdfObject::ARRAY && $type != PdfObject::DICTIONARY && $type != PdfObject::NAME && $type != PdfObject::STRING)
                $os->write(' ');
            $value->toPdf($writer, $os);
        }
        $os->write('>');
        $os->write('>');
    }

    // methods concerning the HashMap member value

    /**
    * Adds a <CODE>PdfObject</CODE> and its key to the <CODE>PdfDictionary</CODE>.
    *
    * @param		key		key of the entry (a <CODE>PdfName</CODE>)
    * @param		value	value of the entry (a <CODE>PdfObject</CODE>)
    * @return		the previous </CODE>PdfObject</CODE> corresponding with the <VAR>key</VAR>
    */

    public function put(PdfName $key, PdfObject $value) {
        $oldvalue = $hashMap[$key];
        $hashMap[$key] = $value;
        return $oldvalue;
    }

    /**
    * Adds a <CODE>PdfObject</CODE> and its key to the <CODE>PdfDictionary</CODE>.
    * If the value is null it does nothing.
    *
    * @param		key		key of the entry (a <CODE>PdfName</CODE>)
    * @param		value	value of the entry (a <CODE>PdfObject</CODE>)
    * @return		the previous </CODE>PdfObject</CODE> corresponding with the <VAR>key</VAR>
    */
    public function putEx(PdfName $key, PdfObject $value) {
        if ($value == NULL)
            return NULL;
        return put($key, $value);
    }

    /**
    * Adds a <CODE>PdfObject</CODE> and its key to the <CODE>PdfDictionary</CODE>.
    * If the value is null the key is deleted.
    *
    * @param		key		key of the entry (a <CODE>PdfName</CODE>)
    * @param		value	value of the entry (a <CODE>PdfObject</CODE>)
    * @return		the previous </CODE>PdfObject</CODE> corresponding with the <VAR>key</VAR>
    */
    public function putDel(PdfName $key, PdfObject $value) {
        if ($value == NULL)
        {
            $oldvalue = $hashMap[$key];
            unset($hashMap[$key]);
            return $oldvalue;
        }
        return put($key, $value);
    }

    /**
    * Removes a <CODE>PdfObject</CODE> and its key from the <CODE>PdfDictionary</CODE>.
    *
    * @param		key		key of the entry (a <CODE>PdfName</CODE>)
    * @return		the previous </CODE>PdfObject</CODE> corresponding with the <VAR>key</VAR>
    */

    public function remove(PdfName $key) {
        $oldvalue = $hashMap[$key];
        unset($hashMap[$key]);
        return $oldvalue;
    }

    /**
    * Gets a <CODE>PdfObject</CODE> with a certain key from the <CODE>PdfDictionary</CODE>.
    *
    * @param		key		key of the entry (a <CODE>PdfName</CODE>)
    * @return		the previous </CODE>PdfObject</CODE> corresponding with the <VAR>key</VAR>
    */

    public function get(PdfName $key) {
        return $hashMap[$key];
    }

    // methods concerning the type of Dictionary

    /**
    * Checks if a <CODE>PdfDictionary</CODE> is of a certain type.
    *
    * @param		type	a type of dictionary
    * @return		<CODE>true</CODE> of <CODE>false</CODE>
    *
    * @deprecated
    */

    public function isDictionaryType(PdfName $type) {
        return $dictionaryType->compareTo($type) == 0;
    }
    /**
    *  Checks if a <CODE>Dictionary</CODE> is of the type FONT.
    *
    * @return		<CODE>true</CODE> if it is, <CODE>false</CODE> if it isn't.
    */

    public function isFont() {
        return $dictionaryType->compareTo(PdfDictionary::$FONT) == 0;
    }

    /**
    *  Checks if a <CODE>Dictionary</CODE> is of the type PAGE.
    *
    * @return		<CODE>true</CODE> if it is, <CODE>false</CODE> if it isn't.
    */

    public function isPage() {
        return $dictionaryType->compareTo(PdfDictionary::$PAGE) == 0;
    }

    /**
    *  Checks if a <CODE>Dictionary</CODE> is of the type PAGES.
    *
    * @return		<CODE>true</CODE> if it is, <CODE>false</CODE> if it isn't.
    */

    public function isPages() {
        return $dictionaryType->compareTo(PdfDictionary::$PAGES) == 0;
    }

    /**
    *  Checks if a <CODE>Dictionary</CODE> is of the type CATALOG.
    *
    * @return		<CODE>true</CODE> if it is, <CODE>false</CODE> if it isn't.
    */

    public function isCatalog() {
        return $dictionaryType->compareTo(PdfDictionary::$CATALOG) == 0;
    }

   /**
   *  Checks if a <CODE>Dictionary</CODE> is of the type OUTLINES.
   *
   * @return		<CODE>true</CODE> if it is, <CODE>false</CODE> if it isn't.
   */
 
    public function isOutlineTree() {
        return $dictionaryType->compareTo(PdfDictionary::$OUTLINES) == 0;
    }

    public function merge(PdfDictionary $other) {
        $hashMap = array_merge($hashMap, $other->hashMap);
    }

    public function mergeDifferent(PdfDictionary $other) {
        foreach (array_keys($other->hashMap) as &$key) {
            if (array_key_exists($key, $hashMap) == FALSE) {
                $hashMap[$key] = $other->hashMap[$key];
            }
        }
    }

    public function getKeys() {
        return $array_keys($hashMap);
    }

    public function putAll(PdfDictionary $dic) {
        $hashMap = array_merge($hashMap, $dic->hashMap);
    }

    public function size() {
        return count($hashMap);
    }

    public function contains(PdfName $key) {
        return array_key_exists($key, $hashMap);
    }

}

?>