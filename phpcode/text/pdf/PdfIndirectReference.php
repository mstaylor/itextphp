<?PHP
/*
 * $Id: PdfIndirectReference.php,v 1.1.1.1 2005/09/22 16:10:04 mstaylor Exp $
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

class PdfIndirectReference extends PdfObject {

    // membervariables

    /** the object number */
    protected $number;

    /** the generation number */
    protected $generation = 0;


    protected function __construct()
    {
        parent::__construct(0);
    }

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 3:
           {
               $type = func_get_arg(0); 
               $number = func_get_arg(1);
               $generation = func_get_arg(2); 
               construct3args($type,$number,$generation);
               break;
           }
           case 2:
           {
               $type = func_get_arg(0); 
               $number = func_get_arg(1);
               construct2args($type,$number,0);
               break;
           }
        }


    }

    private function construct2args($type, $number)
    {
         construct3args($type,$number,0);
    }
    private function construct3args($type, $number, $generation)
    {
        parent::__construct(0, "" . $number . " " . $generation . " R");
        $this->number = $number;
        $this->generation = $generation;
    }

    // methods

    /**
    * Returns the number of the object.
    *
    * @return		a number.
    */

    public function getNumber() {
        return $number;
    }

    /**
    * Returns the generation of the object.
    *
    * @return		a number.
    */

    public function getGeneration() {
        return $generation;
    }





}

?>