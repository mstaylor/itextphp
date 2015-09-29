<?PHP
/*
 * $Id: Chunk.php,v 1.1.1.1 2005/09/22 16:08:19 mstaylor Exp $
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


require_once("Element.php");
require_once("ElementTags.php");
require_once("MarkupAttributes.php");
require_once("Font.php");
require_once("Image.php");
require_once("../util/Properties.php");
require_once("FontFactory.php");
require_once("markup/MarkupParser.php");
require_once("pdf/PdfAction.php");
require_once("pdf/PdfContentByte.php");
require_once("pdf/PdfAnnotation.php");
require_once("pdf/HyphenationEvent.php");
require_once("SplitCharacter.php");
/**
* This is the smallest significant part of text that can be added to a document.
* <P>
* Most elements can be divided in one or more <CODE>Chunk</CODE>s.
* A chunk is a <CODE>String</CODE> with a certain <CODE>Font</CODE>.
* all other layoutparameters should be defined in the object to which
* this chunk of text is added.
* <P>
* Example:
* <BLOCKQUOTE><PRE>
* <STRONG>Chunk chunk = new Chunk("Hello world", FontFactory.getFont(FontFactory.COURIER, 20, Font.ITALIC, new Color(255, 0, 0)));</STRONG>
* document.add(chunk);
* </PRE></BLOCKQUOTE>
*/

class Chunk implements MarkupAttributes
{
    // public static membervariables

    /**
    * The character stand in for an image.
    */
    const OBJECT_REPLACEMENT_CHARACTER = "\ufffc";

    /** This is a Chunk containing a newline. */
    public static $NEWLINE = NULL;

    /** This is a Chunk containing a newpage. */
    public static $NEXTPAGE = NULL;

    /** Key for sub/superscript. */
    const SUBSUPSCRIPT = "SUBSUPSCRIPT";

    /** Key for underline. */
    const UNDERLINE = "UNDERLINE";

    /** Key for color. */
    const COLOR = "COLOR";

    /** Key for encoding. */
    const ENCODING = "ENCODING";

    /** Key for remote goto. */
    const REMOTEGOTO = "REMOTEGOTO";

    /** Key for local goto. */
    const LOCALGOTO = "LOCALGOTO";

    /** Key for local destination. */
    const LOCALDESTINATION = "LOCALDESTINATION";

    /** Key for image. */
    const IMAGE = "IMAGE";

    /** Key for generic tag. */
    const GENERICTAG = "GENERICTAG";

    /** Key for newpage. */
    const NEWPAGE = "NEWPAGE";

    /** Key for split character. */
    const SPLITCHARACTER = "SPLITCHARACTER";

    /** Key for Action. */
    const ACTION = "ACTION";

    /** Key for background. */
    const BACKGROUND = "BACKGROUND";

    /** Key for annotation. */
    const PDFANNOTATION = "PDFANNOTATION";

    /** Key for hyphenation. */
    const HYPHENATION = "HYPHENATION";

    /** Key for text rendering mode. */
    const TEXTRENDERMODE = "TEXTRENDERMODE";

    /** Key for text skewing. */
    const SKEW = "SKEW";

    /** Key for text horizontal scaling. */
    const HSCALE = "HSCALE";

    // member variables

    /** This is the content of this chunk of text. */
    protected $content = NULL;

    /** This is the <CODE>Font</CODE> of this chunk of text. */
    protected $font = NULL;

    /** Contains some of the attributes for this Chunk. */
    protected $attributes = NULL;

    /** Contains extra markupAttributes */
    protected $markupAttributes = NULL;


    private function initializeStatics()
    {
        if (Chunk::$NEWLINE == NULL)
        {
            $NEWLINE = new Chunk("\n");
        }
        if (Chunk::$NEXTPAGE == NULL)
        {
            $NEXTPAGE = new Chunk("");
        }
    }

    // constructors

    /**
    * Empty constructor.
    */
    protected function __construct() {
        initializeStatics();
    }

    /**
    * Constructs a chunk of text with a certain content and a certain <CODE>Font</CODE>.
    *
    * @param	content		the content
    * @param	font		the font
    */

    public function _construct() {

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $theContent = func_get_arg(0); 
                $theFont = func_get_arg(1);
                construct2args($theContent, $theFont);
                break;
            }

            case 1:
            {
                if (func_get_arg(0) instanceof Properties)
                {
                    construct1argproperties(func_get_arg(0));
                }
                else
                {
                    construct1arg(func_get_arg(0)); 
                }
                break;
            }
            case 3:
            {
                $image = func_get_arg(0); 
                $offsetX = func_get_arg(1);
                $offsetY = func_get_arg(2);
                construct3args($image, $offsetX, $offsetY);
                break;
            }
            case 4:
            {
                $image = func_get_arg(0);
                $offsetX = func_get_arg(1);
                $offsetY = func_get_arg(2);
                $changeLeading = func_get_arg(3);
            }
        }


    }

    /**
    * Constructs a chunk of text with a char and a certain <CODE>Font</CODE>.
    *
    * @param	c		the content
    * @param	font		the font
    */
    private function construct2args($c, $font)
    {
        $this->content = $c;
        $this->font = $font;
    }

    
    /**
    * Constructs a chunk of text with a char, without specifying a <CODE>Font</CODE>.
    *
    * @param	c		the content
    */
    private function construct1arg($c)
    {
        construct2args($c, new Font());
    }

    /**
    * Returns a <CODE>Chunk</CODE> that has been constructed taking in account
    * the value of some <VAR>attributes</VAR>.
    *
    * @param	attributes		Some attributes
    */
    private function construct1argproperties($attributes)
    {
        construct2args("", FontFactory::getFont($attributes));
        $value = NULL;
        if (array_key_exists(ElementTags::ITEXT, $attributes) == TRUE)
        {
            $value = $attributes[ElementTags::ITEXT];
            $offset = array_search($value, array_keys($attributes));
            array_splice($attributes,$offset, 1);

        }
        if ($value !=NULL) {
            append($value);
        }

        $value = NULL;
        if (array_key_exists(ElementTags::LOCALGOTO, $attributes) == TRUE)
        {
            $value = $attributes[ElementTags::LOCALGOTO];
            $offset = array_search($value, array_keys($attributes));
            array_splice($attributes,$offset, 1);

        }

        if ($value !=NULL) {
            setLocalGoto($value);
        }


        $value = NULL;
        if (array_key_exists(ElementTags::REMOTEGOTO, $attributes) == TRUE)
        {
            $value = $attributes[ElementTags::REMOTEGOTO];
            $offset = array_search($value, array_keys($attributes));
            array_splice($attributes,$offset, 1);
            $destination = NULL;
            if (array_key_exists(ElementTags::DESTINATION, $attributes) == TRUE)
            {
                $destination = $attributes[ElementTags::DESTINATION];
                $offset = array_search($value, array_keys($attributes));
                array_splice($attributes,$offset, 1);
            }

            $page = NULL;
            if (array_key_exists(ElementTags::PAGE, $attributes) == TRUE)
            {
                $page = $attributes[ElementTags::PAGE];
                $offset = array_search($value, array_keys($attributes));
                array_splice($attributes,$offset, 1);
            }

            if ($page != NULL) {
                setRemoteGoto($value, $page);
            }
            else if ($destination != NULL) {
                setRemoteGoto($value, $destination);
            }

        }

        $value = NULL;
        if (array_key_exists(ElementTags::LOCALDESTINATION, $attributes) == TRUE)
        {
            $value = $attributes[ElementTags::LOCALDESTINATION];
            $offset = array_search($value, array_keys($attributes));
            array_splice($attributes,$offset, 1);

        }

        if ($value !=NULL) {
            setLocalDestination($value);
        }


        $value = NULL;
        if (array_key_exists(ElementTags::SUBSUPSCRIPT, $attributes) == TRUE)
        {
            $value = $attributes[ElementTags::SUBSUPSCRIPT];
            $offset = array_search($value, array_keys($attributes));
            array_splice($attributes,$offset, 1);

        }

        if ($value !=NULL) {
            setTextRise($value);
        }


        if (array_key_exists(ElementTags::CSS_VERTICALALIGN, $attributes) == TRUE)
        {
            $value = $attributes[ElementTags::CSS_VERTICALALIGN];
            if (strcmp("%", substr($value, strlen($value)-1)) == 0 && $value != NULL)
            {
                $p = substr($value, strlen($value)-1)/100;
                setTextRise($p * $font->size());

            }
        }

        $value = NULL;
        if (array_key_exists(ElementTags::GENERICTAG, $attributes) == TRUE)
        {
            $value = $attributes[ElementTags::GENERICTAG];
            $offset = array_search($value, array_keys($attributes));
            array_splice($attributes,$offset, 1);

        }

        if ($value !=NULL) {
            setGenericTag($value);
        }

        $value = NULL;
        if (array_key_exists(ElementTags::BACKGROUNDCOLOR, $attributes) == TRUE)
        {
            $value = $attributes[ElementTags::BACKGROUNDCOLOR];
            $offset = array_search($value, array_keys($attributes));
            array_splice($attributes,$offset, 1);

        }

        if ($value !=NULL) {
            setBackground(MarkupParser::decodeColor($value));
        }

        if (count($attributes) > 0) setMarkupAttributes($attributes);






    }

    /**
    * Constructs a chunk containing an <CODE>Image</CODE>.
    *
    * @param image the image
    * @param offsetX the image offset in the x direction
    * @param offsetY the image offset in the y direction
    */
    private function construct3args($image, $offsetX, $offsetY)
    {
         construct2args(OBJECT_REPLACEMENT_CHARACTER, new Font());
         $copyImage = Image::getInstance($image);
         $copyImage->setAbsolutePosition(NAN, NAN);
         setAttribute(IMAGE, array($copyImage, $offsetX, $offsetY, FALSE));
    }

    private function construct4args($image, $offsetX, $offsetY, $changeLeading)
    {
        construct2args(OBJECT_REPLACEMENT_CHARACTER, new Font());
        setAttribute(IMAGE, array($image, $offsetX, $offsetY, $changeLeading));
    }


    // implementation of the Element-methods

    /**
    * Processes the element by adding it (or the different parts) to an
    * <CODE>ElementListener</CODE>.
    *
    * @param	listener	an <CODE>ElementListener</CODE>
    * @return	<CODE>true</CODE> if the element was processed successfully
    */

    public function process($listener) {
        try {
            return $listener->add($this);
        }
        catch(DocumentException $de) {
            return FALSE;
        }
    }


    /**
    * Gets the type of the text element.
    *
    * @return	a type
    */

    public function type() {
        return Element::CHUNK;
    }


    /**
    * Gets all the chunks in this element.
    *
    * @return	an <CODE>ArrayList</CODE>
    */

    public function getChunks() {
        $tmp = array();
        array_push($tmp,$this);
        return $tmp;
    }


    /**
    * appends some text to this <CODE>Chunk</CODE>.
    *
    * @param	string <CODE>String</CODE>
    * @return	a <CODE>StringBuffer</CODE>
    */

    public function append($string) {
        $content = $content . $string;
        return $content;
    }

    /**
    * Gets the font of this <CODE>Chunk</CODE>.
    *
    * @return	a <CODE>Font</CODE>
    */

    public function font() {
        return $font;
    }

    /**
    * Sets the font of this <CODE>Chunk</CODE>.
    *
    * @param	font a <CODE>Font</CODE>
    */

    public function setFont($font) {
        $this->font = $font;
    }

    /**
    * Returns the content of this <CODE>Chunk</CODE>.
    *
    * @return	a <CODE>String</CODE>
    */

    public function content() {
        return $content;
    }

    /**
    * Checks is this <CODE>Chunk</CODE> is empty.
    *
    * @return	<CODE>false</CODE> if the Chunk contains other characters than space.
    */

    public function isEmpty() {
        return (strlen(trim($content)) == 0) && (strrpos($content, "\n") == -1) && (#attributes == NULL);
    }

    /**
    * Gets the width of the Chunk in points.
    * @return a width in points
    */
    public function getWidthPoint() {
        if (getImage() != NULL) {
            return getImage()->scaledWidth();
        }
    	return $font->getCalculatedBaseFont(TRUE)->getWidthPoint($content(), $font->getCalculatedSize()) * getHorizontalScaling();
    }


    /**
    * Sets the text displacement relative to the baseline. Positive values rise the text,
    * negative values lower the text.
    * <P>
    * It can be used to implement sub/superscript.
    * @param rise the displacement in points
    * @return this <CODE>Chunk</CODE>
    */

    public function setTextRise($rise) {
        return setAttribute(SUBSUPSCRIPT, $rise);
    }

    /**
    * Gets the text displacement relatiev to the baseline.
    * @return a displacement in points
    */
    public function getTextRise() {
        if (array_key_exists(SUBSUPSCRIPT, $attributes) == TRUE) {
            $f = $attributes[SUBSUPSCRIPT];
            return $f;
        }
        return 0.0;
    }

    /** Sets the text rendering mode. It can outline text, simulate bold and make
    * text invisible.
    * @param mode the text rendering mode. It can be <CODE>PdfContentByte.TEXT_RENDER_MODE_FILL</CODE>,
    * <CODE>PdfContentByte.TEXT_RENDER_MODE_STROKE</CODE>, <CODE>PdfContentByte.TEXT_RENDER_MODE_FILL_STROKE</CODE>
    * and <CODE>PdfContentByte.TEXT_RENDER_MODE_INVISIBLE</CODE>.
    * @param strokeWidth the stroke line width for the modes <CODE>PdfContentByte.TEXT_RENDER_MODE_STROKE</CODE> and
    * <CODE>PdfContentByte.TEXT_RENDER_MODE_FILL_STROKE</CODE>.
    * @param strokeColor the stroke color or <CODE>null</CODE> to follow the text color
    * @return this <CODE>Chunk</CODE>
    */
    public function setTextRenderMode($mode, $strokeWidth, $strokeColor) {
        return setAttribute(TEXTRENDERMODE, array($mode, $strokeWidth, $strokeColor));
    }

    /**
    * Skews the text to simulate italic and other effects.
    * Try <CODE>alpha=0</CODE> and <CODE>beta=12</CODE>.
    * @param alpha the first angle in degrees
    * @param beta the second angle in degrees
    * @return this <CODE>Chunk</CODE>
    */
    public function setSkew($alpha, $beta) {
        $alpha = tan($alpha * M_PI / 180);
        $beta = tan($beta * M_PI / 180);
        return setAttribute(SKEW, array($alpha, $beta));
    }

    /**
    * Sets the text horizontal scaling. A value of 1 is normal and a value of 0.5f
    * shrinks the text to half it's width.
    * @param scale the horizontal scaling factor
    * @return this <CODE>Chunk</CODE>
    */
    public function setHorizontalScaling($scale) {
        return setAttribute(HSCALE, $scale);
    }

    /**
    * Gets the horizontal scaling.
    * @return a percentage in float
    */
    public function getHorizontalScaling() {
        if ($attributes == NULL) return 1.0;

        $f = $attributes[HSCALE];
        if ($f == NULL) return 1.0;

        return $f;
    }


    /**
    * Sets an action for this <CODE>Chunk</CODE>.
    *
    * @param action the action
    * @return this <CODE>Chunk</CODE>
    */

    public function setAction($action) {
        return setAttribute(ACTION, $action);
    }

    /**
    * Sets an anchor for this <CODE>Chunk</CODE>.
    *
    * @param url the <CODE>URL</CODE> to link to
    * @return this <CODE>Chunk</CODE>
    */

    public function setAnchor($url) {
        return setAttribute(ACTION, new PdfAction($url));
    }

    /**
    * Sets a local goto for this <CODE>Chunk</CODE>.
    * <P>
    * There must be a local destination matching the name.
    * @param name the name of the destination to go to
    * @return this <CODE>Chunk</CODE>
    */

    public function setLocalGoto($name) {
        return setAttribute(LOCALGOTO, $name);
    }

    public function setBackground()
    {

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $color = func_get_arg(0);
                return setBackground1arg($color);
                break;
            }
            case 5:
            {
                $color = func_get_arg(0);
                $extraLeft = func_get_arg(1);
                $extraBottom = func_get_arg(2);
                $extraRight = func_get_arg(3);
                $extraTop = func_get_arg(4);
                return setBackground5arg($color, $extraLeft, $extraBottom, $extraRight, $extraTop);
                break;
            }
        }
    }
    
    /**
    * Sets the color of the background <CODE>Chunk</CODE>.
    * @param color the color of the background
    * @return this <CODE>Chunk</CODE>
    */
    public function setBackground1arg($color)
    {
        return setBackground($color, 0, 0, 0, 0);
    }

    /** Sets the color and the size of the background <CODE>Chunk</CODE>.
    * @param color the color of the background
    * @param extraLeft increase the size of the rectangle in the left
    * @param extraBottom increase the size of the rectangle in the bottom
    * @param extraRight increase the size of the rectangle in the right
    * @param extraTop increase the size of the rectangle in the top
     * @return this <CODE>Chunk</CODE>
     */
    public function setBackground5arg($color, $extraLeft, $extraBottom, $extraRight, float $extraTop)
    {

         return setAttribute(BACKGROUND, array($color, array($extraLeft, $extraBottom, $extraRight, $extraTop)));
    }

    public function setUnderline
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $thickness = func_get_arg(0);
                $yPosition = func_get_arg(1);
                return setUnderline2args($thickness, $yPosition);
                break;
            }
            case 6:
            {
                $color = func_get_arg(0);
                $thickness = func_get_arg(1);
                $thicknessMul = func_get_arg(2);
                $yPosition = func_get_arg(3);
                $yPositionMul = func_get_arg(4);
                $cap = func_get_arg(5);
                return setUnderline6args($color, $thickness, $thicknessMul, $yPosition, $yPositionMul, $cap);
                break;
            }
        }
    }

    /**
    * Sets an horizontal line that can be an underline or a strikethrough.
    * Actually, the line can be anywhere vertically and has always the
    * <CODE>Chunk</CODE> width. Multiple call to this method will
    * produce multiple lines.
    * @param thickness the absolute thickness of the line
    * @param yPosition the absolute y position relative to the baseline
    * @return this <CODE>Chunk</CODE>
    */


    public function setUnderline2args($thickness, $yPosition)
    {
        return setUnderline(NULL, $thickness, 0.0, $yPosition, 0.0, PdfContentByte::LINE_CAP_BUTT);
    }

    /**
    * Sets an horizontal line that can be an underline or a strikethrough.
    * Actually, the line can be anywhere vertically and has always the
    * <CODE>Chunk</CODE> width. Multiple call to this method will
    * produce multiple lines.
    * @param color the color of the line or <CODE>null</CODE> to follow
    * the text color
    * @param thickness the absolute thickness of the line
    * @param thicknessMul the thickness multiplication factor with the font size
    * @param yPosition the absolute y position relative to the baseline
    * @param yPositionMul the position multiplication factor with the font size
    * @param cap the end line cap. Allowed values are
    * PdfContentByte.LINE_CAP_BUTT, PdfContentByte.LINE_CAP_ROUND and
    * PdfContentByte.LINE_CAP_PROJECTING_SQUARE
    * @return this <CODE>Chunk</CODE>
    */


    public function setUnderline6args($color, $thickness, $thicknessMul, $yPosition, $yPositionMul, $cap)
    {
        if ($attributes == NULL)
            attributes = array();
        $obj = array($color, array($thickness, $thicknessMul, $yPosition, $yPositionMul, $cap));
        $unders = addToArray($attributes[UNDERLINE], $obj);
        return setAttribute(UNDERLINE, $unders);
    }

    /**
    * Utility method to extend an array.
    * @param original the original array or <CODE>null</CODE>
    * @param item the item to be added to the array
    * @return a new array with the item appended
    */
    public static function addToArray($original, $item) {
        if ($original == NULL) {
            $original = array();
            $original[0] = $item;
            return $original;
        }
        else {
            $original2 = array();
            for ($i=0; i < count($original); ++$i)
            {
                $original2[$i] = $original[$i];
            }
            $original2[count($original)] = $item;
            return $original2;
        }
    }

    /**
    * Sets a generic annotation to this <CODE>Chunk</CODE>.
    * @param annotation the annotation
    * @return this <CODE>Chunk</CODE>
    */
    public function setAnnotation($annotation) {
        return setAttribute(PDFANNOTATION, $annotation);
    }

    /** sets the hyphenation engine to this <CODE>Chunk</CODE>.
    * @param hyphenation the hyphenation engine
    * @return this <CODE>Chunk</CODE>
    */
    public function setHyphenation($hyphenation) {
        return setAttribute(HYPHENATION, $hyphenation);
    }

    /**
    * Sets a goto for a remote destination for this <CODE>Chunk</CODE>.
    * @param filename the file name of the destination document
    * @param name the name of the destination to go to
    * @return this <CODE>Chunk</CODE>
    */

    public function setRemoteGoto($filename, $name) {
        return setAttribute(REMOTEGOTO, array($filename, $name));
    }

    /**
    * Sets a goto for a remote destination for this <CODE>Chunk</CODE>.
    *
    * @param filename the file name of the destination document
    * @param page the page of the destination to go to. First page is 1
    * @return this <CODE>Chunk</CODE>
    */

    public function setRemoteGoto($filename, $page) {
        return setAttribute(REMOTEGOTO, array($filename, $page));
    }


    /**
    * Sets a local destination for this <CODE>Chunk</CODE>.
    *
    * @param name the name for this destination
    * @return this <CODE>Chunk</CODE>
    */
    public function setLocalDestination($name) {
        return setAttribute(LOCALDESTINATION, $name);
    }


    /**
    * Sets the generic tag <CODE>Chunk</CODE>.
    * <P>
    * The text for this tag can be retrieved with <CODE>PdfPageEvent</CODE>.
    *
    * @param text the text for the tag
    * @return this <CODE>Chunk</CODE>
    */

    public function setGenericTag($text) {
        return setAttribute(GENERICTAG, text);
    }

    /**
    * Sets the split characters.
    *
    * @param splitCharacter the <CODE>SplitCharacter</CODE> interface
    * @return this <CODE>Chunk</CODE>
    */

    public function setSplitCharacter($splitCharacter) {
        return setAttribute(SPLITCHARACTER, $splitCharacter);
    }


    /**
    * Sets a new page tag..
    * @return this <CODE>Chunk</CODE>
    */

    public Function setNewPage() {
        return setAttribute(NEWPAGE, NULL);
    }

    /**
    * Sets an arbitrary attribute.
    *
    * @param name the key for the attribute
    * @param obj the value of the attribute
    * @return this <CODE>Chunk</CODE>
    */

    private function setAttribute($name, $obj) {
        if ($attributes == NULL)
            $attributes = array());
        $attributes[$name] = $obj;
        return $this;
    }

    /**
    * Gets the attributes for this <CODE>Chunk</CODE>.
    * <P>
    * It may be null.
    *
    * @return the attributes for this <CODE>Chunk</CODE>
    */

    public function getAttributes() {
        return $attributes;
    }

    /**
    * Checks the attributes of this <CODE>Chunk</CODE>.
    *
    * @return false if there aren't any.
    */

    public function hasAttributes() {
        return $attributes != NULL;
    }

    /**
    * Returns the image.
    * @return the image
    */

    public function getImage() {
        if ($attributes == NULL) return NULL;
        $obj = $attributes[Chunk::IMAGE];
        if ($obj == NULL)
            return NULL;
        else {
            return $obj;
        }
    }

    /**
    * Checks if a given tag corresponds with this object.
    *
    * @param   tag     the given tag
    * @return  true if the tag corresponds
    */

    public static function isTag($tag) {
        if (strcmp(ElementTags::CHUNK, $tag)==0)
            return TRUE;
        else
            return FALSE;

    }

    /**
    * @see com.lowagie.text.MarkupAttributes#setMarkupAttribute(java.lang.String, java.lang.String)
    */
    public function setMarkupAttribute($name, $value) {
        $markupAttributes = ($markupAttributes == NULL) ? new Properties() : $markupAttributes;
        $markupAttributes->setProperty($name, $value);
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#setMarkupAttributes(java.util.Properties)
    */
    public function setMarkupAttributes($markupAttributes) {
        $this->markupAttributes = $markupAttributes;
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#getMarkupAttribute(java.lang.String)
    */
    public function getMarkupAttribute($name) {
        return ($markupAttributes == NULL) ? NULL : $markupAttributes->getProperty($name);
    }


    /**
    * @see com.lowagie.text.MarkupAttributes#getMarkupAttributeNames()
    */
    public function getMarkupAttributeNames() {
        return getKeySet($markupAttributes);
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#getMarkupAttributes()
    */
    public function getMarkupAttributes() {
        return $markupAttributes;
    }

    /**
    * Gets the keys of a Hashtable
    * @param table a Hashtable
    * @return the keyset of a Hashtable (or an empty set if table is null)
    */
    public static function getKeySet($table) {
        return ($table == NULL) ? array() : array_keys($table);
    }




}



?>