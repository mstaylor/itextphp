<?PHP
/*
 * $Id: DocumentException.php,v 1.1.1.1 2005/09/22 16:08:19 mstaylor Exp $
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
 * Signals that an error has occurred in a <CODE>Document</CODE>.
 *
 * @see		BadElementException
 * @see		Document
 * @see		DocWriter
 * @see		DocListener
 */

class DocumentException extends Exception {

    private $ex;

    /**
    * Creates a Document exception.
    * @param ex an exception that has to be turned into a DocumentException
    */

    public function __contruct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                parent::_construct();
                break;
            }
            case 1:
            {
                $var = func_get_arg(0); 
                if ($var instanceof Exception)
                {
                    $this->$ex = $var;
                }
                else
                {
                    parent::__construct($var);
                }
                break;
            }
        }
    }

    /**
    * We print the message of the checked exception 
    * @return the error message
    */
    public function getAMessage() {
        if ($ex == NULL)
            return parent::getMessage();
        else
            return $ex->getMessage();
    }

    /**
    * and make sure we also produce a localized version 
    * @return a localized message
    */
    public function getLocalizedMessage() {
        if ($ex == NULL)
            return parent::getLocalizedMessage();
        else
            return $ex->getLocalizedMessage();
    }

    /**
    * The toString() is changed to be prefixed with ExceptionConverter 
    * @return the String version of the exception
    */
    public function __toString()
    {
        if ($ex == NULL)
            return parent::__toString();
        else
            return split(get_class($this)) + ": " + ex;
    }


    /**
    * Removes everything in a String that comes before a '.'
    * @param s the original string
    * @return the part that comes after the dot
    */
    private static function split($s) {
        $i = strrpos($s,".");
        if ($i < 0)
            return s;
        else
            return substr($s,i + 1);
    }





}
?>