<?PHP
/*
 * $Id: MetaPen.php,v 1.2 2005/11/10 18:14:19 mstaylor Exp $
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
require_once("InputMeta.php");


class MetaPen extends MetaObject {

    const PS_SOLID = 0;
    const PS_DASH = 1;
    const PS_DOT = 2;
    const PS_DASHDOT = 3;
    const PS_DASHDOTDOT = 4;
    const PS_NULL = 5;
    const PS_INSIDEFRAME = 6;

    protected $style = 0;
    protected $penWidth = 1;
    protected $color = NULL;//color

    public function __construct() {
        $style = MetaPen::PS_SOLID;
        $color = Color::$black;
        $type = MetaObject::META_PEN;
    }

    public function init(InputMeta $in) {
        $style = $in->readWord();
        $penWidth = $in->readShort();
        $in->readWord();
        $color = $in->readColor();
    }

    public function getStyle() {
        return $style;
    }

    public function getPenWidth() {
        return $penWidth;
    }

    public function getColor() {
        return $color;
    }
}

?>