<?PHP
/*
 * $Id: MetaBrush.php,v 1.2 2005/11/10 18:14:19 mstaylor Exp $
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


require_once("../../../../awt/Color.php");
require_once("MetaObject.php");
require_once("InputMeta.php");

class MetaBrush extends MetaObject
{

    const BS_SOLID = 0;
    const BS_NULL = 1;
    const BS_HATCHED = 2;
    const BS_PATTERN = 3;
    const BS_DIBPATTERN = 5;
    const HS_HORIZONTAL = 0;
    const HS_VERTICAL = 1;
    const HS_FDIAGONAL = 2;
    const HS_BDIAGONAL = 3;
    const HS_CROSS = 4;
    const HS_DIAGCROSS = 5;

    $style = 0;
    $hatch = 0;
    $color = NULL;//Color

    private function onConstruct()
    {
        $style = MetaBrush::BS_SOLID;
        $color = Color::$white;
    }

    public function __construct()
    {
        onConstruct();
        $type = MetaObject::META_BRUSH;
    }

    public function init(InputMeta $in) {
        $style = $in->readWord();
        $color = $in->readColor();
        $hatch = $in->readWord();
    }

    public function getStyle() {
        return $style;
    }

    public function getHatch() {
        return $hatch;
    }

    public function getColor() {
        return $color;
    }



}

?>