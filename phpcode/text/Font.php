<?PHP
/*
 * $Id: Font.php,v 1.1.1.1 2005/09/22 16:08:20 mstaylor Exp $
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
require_once("../awt/Color.php");
require_once("pdf/BaseFont.php");
require_once("FontFactory.php");
require_once("markup/MarkupTags.php");
class Font
{

    // static membervariables for the different families

    /** a possible value of a font family. */
    const COURIER = 0;

    /** a possible value of a font family. */
    const HELVETICA = 1;

    /** a possible value of a font family. */
    const TIMES_ROMAN = 2;

    /** a possible value of a font family. */
    const SYMBOL = 3;

    /** a possible value of a font family. */
    const ZAPFDINGBATS = 4;

    // static membervariables for the different styles

    /** this is a possible style. */
    const NORMAL = 0;

    /** this is a possible style. */
    const BOLD = 1;

    /** this is a possible style. */
    const ITALIC = 2;

    /** this is a possible style. */
    const UNDERLINE	= 4;

    /** this is a possible style. */
    const STRIKETHRU	= 8;

    /** this is a possible style. */
    public static $BOLDITALIC = NULL;

    // static membervariables

    /** the value of an undefined attribute. */
    const UNDEFINED = -1;

    /** the value of the default size. */
    const DEFAULTSIZE = 12;

    // membervariables

    /** the value of the fontfamily. */
    private $family = UNDEFINED;

    /** the value of the fontsize. */
    private $size = UNDEFINED;

    /** the value of the style. */
    private $style = UNDEFINED;

    /** the value of the color. */
    private $color = null;

    /** the external font */
    private $baseFont = null;

    public function initializeStatics()
    {
        if (Font::$BOLDITALIC == NULL)
        {
            Font::$BOLDITALIC = BOLD | ITALIC;
        }
    }

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                construct0arg();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0); 
                if ($arg1 instanceof Font)
                {
                    construct1argFont($arg1);
                }
                else if ($arg1 instanceof BaseFont)
                {
                    construct1argBaseFont($arg1);
                }
                else
                {
                    construct1arg($arg1);
                }
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if ($arg1 instanceof BaseFont)
                {
                    construct2argsBaseFont($arg1, $arg2);
                }
                else 
                {
                    construct2args($arg1, $arg2);
                }
                break;
            }
            case 3:
            {
                construct4argsBaseFont(func_get_arg(0), func_get_arg(1), func_get_arg(2), NULL);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if ($arg1 instanceof BaseFont)
                {
                    construct4argsBaseFont($arg1, $arg2, $arg3, $arg4);
                }
                else
                {
                    construct4args($arg1, $arg2, $arg3, $arg4);
                }
                break;
            }
        }
    }

    /**
    * Constructs a Font.
    *
    * @param	family	the family to which this font belongs
    * @param	size	the size of this font
    * @param	style	the style of this font
    * @param	color	the <CODE>Color</CODE> of this font.
    */
    private function construct4args($family, $size, $style, $color)
    {
        $this->family = $family;
        $this->size = $size;
        $this->style = $style;
        $this->color = $color;
    }

    /**
    * Constructs a Font.
    *
    * @param	bf	    the external font
    * @param	size	the size of this font
    * @param	style	the style of this font
    * @param	color	the <CODE>Color</CODE> of this font.
    */
    private function construct4argsBaseFont($bf, $size, $style, $color)
    {

        $this->baseFont = $bf;
        $this->size = $size;
        $this->style = $style;
        $this->color = $color;
    }



    /**
    * Constructs a Font.
    *
    * @param	bf	    the external font
    * @param	size	the size of this font
    * @param	style	the style of this font
    */

    private function construct3argsBasefont($bf, $size, $style)
    {
        construct4argsBaseFont($bf,$size,$style,NULL);
    }

    /**
    * Constructs a Font.
    */

    private function construct0arg()
    {
        construct4args(UNDEFINED, UNDEFINED, UNDEFINED, NULL);
    }

    /**
    * Copy constructor of a Font
    * @param other the font that has to be copied
    */

    private function construct1argFont($other)
    {
        $this->color = $other->color;
        $this->family = $other->family;
        $this->size = $other->size;
        $this->style = $other->style;
        $this->baseFont = $other->baseFont;
    }

    /**
    * Constructs a Font.
    *
    * @param	bf	    the external font
    */


    private function construct1argBaseFont($bf)
    {
        construct3argsBasefont($bf, UNDEFINED, UNDEFINED, NULL);
    }
    
    /**
    * Constructs a Font.
    *
    * @param	family	the family to which this font belongs
    */
    private function construct1arg($family)
    {
        construct4args($family, UNDEFINED, UNDEFINED, NULL);
    }

    /**
    * Constructs a Font.
    *
    * @param	bf	    the external font
    * @param	size	the size of this font
    */
    private function construct2argsBaseFont($bf, $size)
    {

        construct4argsBaseFont($bf, $size, UNDEFINED, NULL);
    }

    private function construct2args($family, $size)
    {
        construct4argsBaseFont($family, $size, UNDEFINED, NULL);
    }

    /**
    * Compares this <CODE>Font</CODE> with another
    *
    * @param	object	the other <CODE>Font</CODE>
    * @return	a value
    */

    public function compareTo($object)
    {
        if ($object == NULL) {
            return -1;
        }
        $font;
        try {
            $font = $object;
            if ($baseFont != NULL && strcmp($baseFont, $font->getBaseFont()) != 0) {
                return -2;
            }
            if ($this->family != $font->family) {
                return 1;
            }
            if ($this->size != $font->size()) {
                return 2;
            }
            if ($this->style != $font->style()) {
                return 3;
            }
            if ($this->color == NULL) {
                if ($font->color == NULL) {
                    return 0;
                }
                return 4;
            }
            if ($font->color == NULL) {
                return 4;
            }
            if (strcmp($this->color, $font->color())==0) {
                return 0;
            }
            return 4;
        }
        catch(Exception $cce) {
            return -3;
        }
    }


    // methods

    /**
    * Sets the family using a <CODE>String</CODE> ("Courier",
    * "Helvetica", "Times New Roman", "Symbol" or "ZapfDingbats").
    *
    * @param	family		A <CODE>String</CODE> representing a certain font-family.
    */

    public function setFamily($family) {
        $this->family = getFamilyIndex($family);
    }

    /**
    * Translates a <CODE>String</CODE>-value of a certain family
    * into the index that is used for this family in this class.
    *
    * @param	family		A <CODE>String</CODE> representing a certain font-family
    * @return	the corresponding index
    */

    public static function getFamilyIndex($family) {
        if (stristr($family, FontFactory::COURIER)!=FALSE) {
            return COURIER;
        }
        if (stristr($family, FontFactory::HELVETICA) != FALSE) {
            return HELVETICA;
        }
        if (stristr($family, FontFactory::TIMES_ROMAN) != FALSE) {
            return TIMES_ROMAN;
        }
        if (stristr($family, FontFactory::SYMBOL) != FALSE) {
            return SYMBOL;
        }
        if (stristr($family, FontFactory::ZAPFDINGBATS) != FALSE) {
            return ZAPFDINGBATS;
        }
        return UNDEFINED;
    }


    /**
    * Gets the familyname as a String.
    *
    * @return  the familyname
    */
    public function getFamilyname() 
    {
        $tmp = "unknown";
        switch(family()) {
            case Font::COURIER:
                return FontFactory::COURIER;
            case Font::HELVETICA:
                return FontFactory::HELVETICA;
            case Font::TIMES_ROMAN:
                return FontFactory::TIMES_ROMAN;
            case Font::SYMBOL:
                return FontFactory::SYMBOL;
            case Font::ZAPFDINGBATS:
                return FontFactory::ZAPFDINGBATS;
            default:
                if (!baseFont != NULL) 
                {
                    $names = $baseFont->getFamilyFontName();
                    for ($i = 0; i < count($names); $i++) {
                        if (strcmp("0", $names[$i][2]) == 0) {
                            return $names[$i][3];
                        }
                        if (strcmp("1033", $names[$i][2]) == 0) {
                            $tmp = $names[$i][3];
                        }
                        if (strcmp("", $names[$i][2]) == 0) {
                            $tmp = $names[$i][3];
                        }
                    }
                }
        }
        return $tmp;
    }

    /**
    * Sets the size.
    *
    * @param	size		The new size of the font.
    */

    public function setSize($size) {
        $this->size = $size;
    }

    /**
    * Sets the style using a <CODE>String</CODE> containing one of
    * more of the following values: normal, bold, italic, underline, strike.
    *
    * @param	style	A <CODE>String</CODE> representing a certain style.
    */

    public function setStyle()
    {
        $arg1 = func_get_arg(0); 
        if (strcmp(gettype($arg1), "string") == 0)
        {
           setStyleString($arg1);
        }
        else
        {
          setStyleInt($arg1);
        }
    }

    /**
    * Sets the style using a <CODE>String</CODE> containing one of
    * more of the following values: normal, bold, italic, underline, strike.
    *
    * @param	style	A <CODE>String</CODE> representing a certain style.
    */

    public function setStyleString($style) {
        if ($this->style == UNDEFINED) $this->style = NORMAL;
        $this->style |= getStyleValue($style);
    }

    /**
    * Sets the style.
    * @param	style	the style.
    */

    public function setStyleInt($style) {
        if ($this->style == UNDEFINED) $this->style = NORMAL;
        $this->style |= $style;
    }

    /**
    * Translates a <CODE>String</CODE>-value of a certain style
    * into the index value is used for this style in this class.
    *
    * @param	style			A <CODE>String</CODE>
    * @return	the corresponding value
    */

    public static function getStyleValue($style) 
    {
        $s = 0;
        if (strpos($style, MarkupTags::CSS_NORMAL) != -1) {
            $s |= NORMAL;
        }
        if (strpos(style, MarkupTags::CSS_BOLD) != -1) {
            $s |= BOLD;
        }
        if (strpos($style, MarkupTags::CSS_ITALIC) != -1) {
            $s |= ITALIC;
        }
        if (strpos($style, MarkupTags::CSS_OBLIQUE) != -1) {
            $s |= ITALIC;
        }
        if (strpos($style, MarkupTags::CSS_UNDERLINE) != -1) {
            $s |= UNDERLINE;
        }
        if (strpos($style, MarkupTags::CSS_LINETHROUGH) != -1) {
            $s |= STRIKETHRU;
        }
        return s;
    }

    public function setColor()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0); 
                setColor1arg($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                setColor3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    /**
    * Sets the color.
    *
    * @param	color		the new color of the font
    */
    private function setColor1arg($color)
    {
         $this->color = $color;
    }


    /**
    * Sets the color.
    *
    * @param	red			the red-value of the new color
    * @param	green		the green-value of the new color
    * @param	blue		the blue-value of the new color
    */
    private function setColor3args($red, $green, $blue)
    {
        this.color = new Color($red, $green, $blue);
    }

    /**
    * Gets the leading that can be used with this font.
    *
    * @param	linespacing		a certain linespacing
    * @return	the height of a line
    */

    public function leading($linespacing) {
        if ($size == UNDEFINED) {
            return $linespacing * DEFAULTSIZE;
        }
        return $linespacing * $size;
    }

    /**
    * Checks if the properties of this font are undefined or null.
    * <P>
    * If so, the standard should be used.
    *
    * @return	a <CODE>boolean</CODE>
    */

    public function isStandardFont() {
        return ($family == UNDEFINED
        && $size == UNDEFINED
        && $style == UNDEFINED
        && $color == NULL
        && $baseFont == NULL);
    }

    /**
    * Replaces the attributes that are equal to <VAR>null</VAR> with
    * the attributes of a given font.
    *
    * @param	font	the font of a bigger element class
    * @return	a <CODE>Font</CODE>
    */

    public function difference($font) {
        // size
        $dSize = $font->size;
        if ($dSize == UNDEFINED) {
            $dSize = $this->size;
        }
        // style
        $dStyle = UNDEFINED;
        $style1 = $this->style;
        $style2 = $font->style();
        if ($style1 != UNDEFINED || $style2 != UNDEFINED) {
            if ($style1 == UNDEFINED) $style1 = 0;
            if ($style2 == UNDEFINED) $style2 = 0;
            $dStyle = $style1 | $style2;
        }
        // color
        $dColor = $font->color;
        if ($dColor == NULL) {
            $dColor = $this->color;
        }
        // family
        if ($font->baseFont != NULL) {
            return new Font($font->baseFont, $dSize, $dStyle, $dColor);
        }
        if ($font->family() != UNDEFINED) {
            return new Font($font->family, $dSize, $dStyle, $dColor);
        }
        if ($this->baseFont != NULL) {
            if ($dStyle == $style1) {
                return new Font($this->baseFont, $dSize, $dStyle, $dColor);
            }
            else {
                return FontFactory::getFont($this->getFamilyname(), $dSize, $dStyle, $dColor);
            }
        }
        return new Font($this->family, $dSize, $dStyle, $dColor);
    }

    // methods to retrieve the membervariables
    /**
    * Gets the family of this font.
    *
    * @return	the value of the family
    */
    public function family() {
        return $family;
    }

    /**
    * Gets the size of this font.
    *
    * @return	a size
    */
    public function size() {
        return $size;
    }

    /**
    * Gets the style of this font.
    *
    * @return	a size
    */
    public function style() {
        return $style;
    }

    /**
    * checks if this font is Bold.
    *
    * @return	a <CODE>boolean</CODE>
    */
    public function isBold() {
        if ($style == UNDEFINED) {
            return FALSE;
        }
        return ($style & BOLD) == BOLD;
    }


    /**
    * checks if this font is Bold.
    *
    * @return	a <CODE>boolean</CODE>
    */
    public function isItalic() {
        if ($style == UNDEFINED) {
            return FALSE;
        }
        return ($style &ITALIC) == ITALIC;
    }

    /**
    * checks if this font is underlined.
    *
    * @return	a <CODE>boolean</CODE>
    */
    public function isUnderlined() {
        if ($style == UNDEFINED) {
            return FALSE;
        }
        return ($style & UNDERLINE) == UNDERLINE;
    }


    /**
    * checks if the style of this font is STRIKETHRU.
    *
    * @return	a <CODE>boolean</CODE>
    */
    public function isStrikethru() {
        if ($style == UNDEFINED) {
            return FALSE;
        }
        return ($style & STRIKETHRU) == STRIKETHRU;
    }

    /**
    * Gets the color of this font.
    *
    * @return	a color
    */
    public function color() {
        return $color;
    }

    /** Gets the <CODE>BaseFont</CODE> inside this object.
    * @return the <CODE>BaseFont</CODE>
    */
    public function getBaseFont() {
        return $baseFont;
    }

    /** Gets the <CODE>BaseFont</CODE> this class represents.
    * For the built-in fonts a <CODE>BaseFont</CODE> is calculated.
    * @param specialEncoding <CODE>true</CODE> to use the special encoding for Symbol and ZapfDingbats,
    * <CODE>false</CODE> to always use <CODE>Cp1252</CODE>
    * @return the <CODE>BaseFont</CODE> this class represents
    */
    public function getCalculatedBaseFont($specialEncoding) {
        if ($baseFont != NULL)
            return $baseFont;
        $style = $this->style;
        if ($style == UNDEFINED) {
            $style = NORMAL;
        }
        $fontName = BaseFont::HELVETICA;
        $encoding = BaseFont::WINANSI;
        $cfont = NULL;
        switch($family) {
            case COURIER:
                switch($style & BOLDITALIC) {
                    case BOLD:
                        $fontName = BaseFont::COURIER_BOLD;
                        break;
                    case ITALIC:
                        $fontName = BaseFont::COURIER_OBLIQUE;
                        break;
                    case BOLDITALIC:
                        $fontName = BaseFont::COURIER_BOLDOBLIQUE;
                        break;
                    default:
                    //case NORMAL:
                        $fontName = BaseFont::COURIER;
                        break;
                }
                break;
            case TIMES_ROMAN:
                switch($style & BOLDITALIC) {
                    case BOLD:
                        $fontName = BaseFont::TIMES_BOLD;
                        break;
                    case ITALIC:
                        $fontName = BaseFont::TIMES_ITALIC;
                        break;
                    case BOLDITALIC:
                        $fontName = BaseFont::TIMES_BOLDITALIC;
                        break;
                        default:
                    case NORMAL:
                        $fontName = BaseFont::TIMES_ROMAN;
                        break;
                }
                break;
            case SYMBOL:
                $fontName = BaseFont::SYMBOL;
                if ($specialEncoding)
                    $encoding = BaseFont::SYMBOL;
                break;
            case ZAPFDINGBATS:
                $fontName = BaseFont::ZAPFDINGBATS;
                if ($specialEncoding)
                    $encoding = BaseFont::ZAPFDINGBATS;
                break;
            default:
            //case Font.HELVETICA:
                switch($style & BOLDITALIC) {
                    case BOLD:
                        $fontName = BaseFont::HELVETICA_BOLD;
                        break;
                    case ITALIC:
                        $fontName = BaseFont::HELVETICA_OBLIQUE;
                        break;
                    case BOLDITALIC:
                        $fontName = BaseFont::HELVETICA_BOLDOBLIQUE;
                        break;
                        default:
                    case NORMAL:
                        $fontName = BaseFont::HELVETICA;
                        break;
                }
                break;
        }
        try {
            $cfont = BaseFont::createFont($fontName, $encoding, FALSE);
        }
        catch (Exception $ee) {
            throw new Exception($ee);
        }
        return $cfont;
    }

    /** Gets the style that can be used with the calculated <CODE>BaseFont</CODE>.
    * @return the style that can be used with the calculated <CODE>BaseFont</CODE>
    */
    public function getCalculatedStyle() {
        $style = $this->style;
        if ($style == UNDEFINED) {
            $style = NORMAL;
        }
        if ($baseFont != NULL)
            return $style;
        if ($family == SYMBOL || $family == ZAPFDINGBATS)
            return $style;
        else
            return $style & (~BOLDITALIC);
    }

    /** Gets the size that can be used with the calculated <CODE>BaseFont</CODE>.
    * @return the size that can be used with the calculated <CODE>BaseFont</CODE>
    */
    public function getCalculatedSize() {
        $s = $this->size;
        if ($s == UNDEFINED) {
            $s = DEFAULTSIZE;
        }
        return $s;
    }


}
?>