<?PHP
/*
 * $Id: Font.php,v 1.1 2005/10/25 16:02:19 mstaylor Exp $
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
* This class represents a windowing system font. (Adopted from GNU Classpath by Mills W. Staylor, III)
*
* @author Aaron M. Renn (arenn@urbanophile.com)
* @author Warren Levy (warrenl@cygnus.com)
* @author Graydon Hoare (graydon@redhat.com)
*/
class Font
{

    /*
    * Static Variables
    */

    /**
    * Constant indicating a "plain" font.
    */
    const PLAIN = 0;

    /**
    * Constant indicating a "bold" font.
    */
    const BOLD = 1;

    /**
    * Constant indicating an "italic" font.
    */
    const ITALIC = 2;

    /**
    * Constant indicating the baseline mode characteristic of Roman.
    */
    const ROMAN_BASELINE = 0;

    /**
    * Constant indicating the baseline mode characteristic of Chinese.
    */
    const CENTER_BASELINE = 1;

    /**
    * Constant indicating the baseline mode characteristic of Devanigri.
    */
    const HANGING_BASELINE = 2;  


    /**
    * Indicates to <code>createFont</code> that the supplied font data
    * is in TrueType format.
    *
    * <p><em>Specification Note:</em> The Sun JavaDoc for J2SE 1.4 does
    * not indicate whether this value also subsumes OpenType. OpenType
    * is essentially the same format as TrueType, but allows to define
    * glyph shapes in the same way as PostScript, using cubic bezier
    * curves.
    *
    * @since 1.3
    */
    const TRUETYPE_FONT = 0;


    /**
    * A flag for <code>layoutGlyphVector</code>, indicating that the
    * orientation of a text run is from left to right.
    *
    * @since 1.4
    */
    const LAYOUT_LEFT_TO_RIGHT = 0;


    /**
    * A flag for <code>layoutGlyphVector</code>, indicating that the
    * orientation of a text run is from right to left.
    *
    * @since 1.4
    */
    const LAYOUT_RIGHT_TO_LEFT = 1;


    /**
    * A flag for <code>layoutGlyphVector</code>, indicating that the
    * text does not contain valid characters before the
    * <code>start</code> position.  If this flag is set,
    * <code>layoutGlyphVector</code> does not examine the text before
    * <code>start</code>, even if this would be necessary to select the
    * correct glyphs (e.g., for Arabic text).
    *
    * @since 1.4
    */
    const LAYOUT_NO_START_CONTEXT = 2;


    /**
    * A flag for <code>layoutGlyphVector</code>, indicating that the
    * text does not contain valid characters after the
    * <code>limit</code> position.  If this flag is set,
    * <code>layoutGlyphVector</code> does not examine the text after
    * <code>limit</code>, even if this would be necessary to select the
    * correct glyphs (e.g., for Arabic text).
    *
    * @since 1.4
    */
    const LAYOUT_NO_LIMIT_CONTEXT = 4;

    /**
    * The logical name of this font.
    *
    * @since 1.0
    */
    protected $name = NULL;//a string

    /**
    * The size of this font in pixels.
    *
    * @since 1.0
    */
    protected $size = 0;

    /**
    * The style of this font -- PLAIN, BOLD, ITALIC or BOLD+ITALIC.
    *
    * @since 1.0
    */
    protected $style = 0;


}





?>