<?PHP
/*
 * $Id: EnumerateTTC.php,v 1.2 2005/09/30 16:07:14 mstaylor Exp $
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

require_once("../DocumentException.php");
require_once("TrueTypeFont.php");
require_once("RandomAccessFileOrArray.php");

class EnumerateTTC extends TrueTypeFont
{

    protected $names = array();


    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 1:
           {
               $arg1 = func_get_arg(0);
               if (is_resource($arg1) == TRUE)
               {
                   construct1argresource($arg1);
               } 
               else
               {
                   construct1argstring($arg1);
               }
               break;
           }
        }
    }


    private function construct1argstring($ttcFile)
    {
        $fileName = $ttcFile;
        $rf = new RandomAccessFileOrArray($ttcFile);
        findNames();
    }

    private function constructaargresource($ttcArray)
    {
        $fileName = "Byte array TTC";
        $rf = new RandomAccessFileOrArray($ttcArray);
        findNames();
    }

    function findNames() 
    {
        $tables = new HashMap();

        try {
            $mainTag = readStandardString(4);
            if (strcmp($mainTag, "ttcf") != 0)
                throw new DocumentException($fileName . " is not a valid TTC file.");
            $rf->skipBytes(4);
            $dirCount = $rf->readInt();
            $names = array();
            $dirPos = $rf->getFilePointer();
            for ($dirIdx = 0; $dirIdx < $dirCount; ++$dirIdx) {
                $tables = array();
                $rf->seek($dirPos);
                $rf->skipBytes($dirIdx * 4);
                $directoryOffset = $rf->readInt();
                $rf->seek($directoryOffset);
                if ($rf->readInt() != 0x00010000)
                    throw new DocumentException($fileName . " is not a valid TTF file.");
                $num_tables = $rf->readUnsignedShort();
                $rf->skipBytes(6);
                for ($k = 0; $k < $num_tables; ++$k) {
                    $tag = readStandardString(4);
                    $rf->skipBytes(4);
                    $table_location = array();
                    $table_location[0] = $rf->readInt();
                    $table_location[1] = $rf->readInt();
                    $tables[$tag] = $table_location;
                }
                $names[$dirIdx] = getBaseFont();
            }
        }
        catch (Exception $e) {
            if ($rf != NULL)
                $rf->close();
                return;
        }
        if ($rf != NULL)
                $rf->close();
    }

    function getNames() {
        return $names;
    }
}

?>