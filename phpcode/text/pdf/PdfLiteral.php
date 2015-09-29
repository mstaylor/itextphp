<?PHP
/*
 * $Id: PdfLiteral.php,v 1.2 2005/10/12 21:22:58 mstaylor Exp $
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
require_once("PdfWriter.php");
require_once("../../io/OutputStream.php");
require_once("OutputStreamCounter.php");


class PdfLiteral extends PdfObject
{
    /**
    * Holds value of property position.
    */
    private $position = 0;

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0); 
                if (is_string($arg1) == TRUE)
                    construct1argString($arg1);
                else if (is_resource($arg1) == TRUE)
                    construct1argResource($arg1);
                else if (is_integer($arg1) == TRUE)
                    construct1argInteger($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_integer($arg1) == TRUE && is_string($arg2) == TRUE)
                   construct2argsString($arg1, $arg2);
                else if(is_integer($arg1) == TRUE && is_resource($arg2) == TRUE)
                   construct2argsResource($arg1, $arg2);
                break;
            }
        }
    }


    private function construct1argString($text) {
        parent::__construct(0, $text);
    }

    private function construct1argResource($b) {
        parent::__construct(0, $b);
    }

    private function construct1argInteger($size) {
        parent::__construct(0, NULL);
        $bytes = itextphp_bytes_create($size);
        for ($k = 0; $k < $size; $k++)
            itextphp_bytes_write($bytes, $k, itextphp_bytes_createfromInt(32),0);
    }

    private function construct2argsString($type, $text) {
        parent::__construct($type, $text);
    }

    private function construct2argsResource($type, $b) {
        parent::__construct($type, $b);
    }

    public function toPdf(PdfWriter $writer, OutputStream $os) {
        if ($os instanceof OutputStreamCounter)
            $position = ($os)->getCounter();
        parent::toPdf($writer, $os);
    }

    /**
    * Getter for property position.
    * @return Value of property position.
    */
    public function getPosition() {
        return $this->position;
    }

    /**
    * Getter for property posLength.
    * @return Value of property posLength.
    */
    public function getPosLength() {
        if ($bytes != NULL)
            return itextphp_bytes_getSize($bytes);
        else
            return 0;
    }
}


?>