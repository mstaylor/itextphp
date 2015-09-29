<?php
namespace com\lowagie\text;
require_once dirname(__FILE__) . "/../../../php/lang/TypeHint.php";
require_once "Element.php";
require_once "ElementListener.php";
require_once "Font.php";
require_once "Image.php";
require_once "DocumentException.php";

use com\lowagie\text\Element as Element;
use com\lowagie\text\ElementListener as ElementListener;
use com\lowagie\text\Font as Font;
use com\lowagie\text\Image as Image;
use com\lowagie\text\DocumentException as DocumentException;

class Chunk implements Element
{
    const OBJECT_REPLACEMENT_CHARACTER = "\ufffc";
    
    
    public static $NEWLINE = NULL;
    public static $initialized = FALSE;
    public static $NEXTPAGE = NULL;
    
    protected $content = NULL;//stringbuffer
    protected $font = NULL;//Font
    protected $attributes = null;//hashmap
    
    
    
    public function __construct()
    {
        $argCount = func_num_args();
        switch($argCount) {
            case 1: {
                $arg1 = func_get_arg(0); 
                if (($arg1 instanceof Chunk) == TRUE) {
                    $this->construct1argChunk($arg1);
                }
                elseif (is_string($arg1)) {
                    $this->construct1argString($arg1);
                }
                break;
            }
            case 2 : {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1);
                if (is_string($arg1) && ($arg2 instanceof Font) == TRUE) {
                    $this->construct2argStringFont($arg1, $arg2);
                }
                elseif ($is_string($arg1) && is_bool($arg2)) {
                    
                }
                break;
            }
            case 3: {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if (is_string($arg1) && ($arg2 instanceof Font) == TRUE && is_bool($arg3)) {
                    $this->construct3argStringFontBool($arg1, $arg2, $arg3);
                }
                elseif (($arg1 instanceof Image) == TRUE && is_float($arg2) && is_float($arg3)) {
                    $this->construct3argImageFloatFloat($arg1, $arg2, $arg3);
                }
                break;
            }
            case 4: {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (($arg1 instanceof Image) == TRUE && is_float($arg2) && is_float($arg3) && is_bool($arg4)) {
                    $this->construct4arg($arg1, $arg2, $arg3, $arg4);
                }
                break;
            }
            default: {
                $this->content = "";
                $this->font = new Font();
            }
        }
    }
    
    /**
    * A <CODE>Chunk</CODE> copy constructor.
    * @param ck the <CODE>Chunk</CODE> to be copied
    */ 
    private function construct1argChunk(Chunk $ck) {
        if ($ck->attributes != NULL) {
            $this->content = $ck->content;
        }
        if ($ck->font != NULL) {
            $this->font = new Font($ck->font);
        }
        if ($ck->attributes != NULL) {
            $this->attributes = array_merge($ck->attributes);
        }
    }
    
    /**
    * Constructs a chunk of text with a certain content, without specifying a
    * <CODE>Font</CODE>.
    * 
    * @param content
    *            the content
    */
    private function construct1argString(string $content) {
        $this->construct2argStringFont($content, new Font());
    }
    
    /**
    * Constructs a chunk of text with a certain content and a certain <CODE>
    * Font</CODE>.
    * 
    * @param content
    *            the content
    * @param font
    *            the font
    */
    private function construct2argStringFont(string $content, Font $font) {
        $this->content= $content;
        $this->font = $font;
    }
    
    
    private function construct2argStringBool(string $c, boolean $isChar) {
        if ($isChar) {
            $this->construct3argStringFontBool($c, new Font(), TRUE);
        }
        else {
            $this->construct1argString($c);
        }
    }
    
    /**
    * Constructs a chunk of text with a char and a certain <CODE>Font</CODE>.
    * 
    * @param c
    *            the content
    * @param font
    *            the font
    */
    private function construct3argStringFontBool(string $c, Font $font, boolean $isChar)
    {
        if ($isChar) {
            $this->content = "" . $c;
            $this->font = $font;
        }
        else {
            $this->construct2argStringFont($c, $font);
        }
    }
    
    /**
    * Constructs a chunk containing an <CODE>Image</CODE>.
    * 
    * @param image
    *            the image
    * @param offsetX
    *            the image offset in the x direction
    * @param offsetY
    *            the image offset in the y direction
    */
    private function construct3argImageFloatFloat(Image $image, float $offsetX, float $offsetY) {
        $this->construct2argStringFont(Chunk::OBJECT_REPLACEMENT_CHARACTER, new Font());
        $copyImage = Image::getInstance($image);
        $this->setAttribute(Chunk::IMAGE, array($copyImage, $offsetX, $offsetY, FALSE));
    }
    
    private function construct4arg(Image $image, float $offsetX, float $offsetY, boolean $changeLeading) {
        $this->construct2argStringFont(Chunk::OBJECT_REPLACEMENT_CHARACTER, new Font());
        $this->setAttribute(Chunk::IMAGE, array($image, $offsetX, $offsetY, $changeLeading));
    }
    
    //implementation of Element methods
    
    /**
    * Processes the element by adding it (or the different parts) to an <CODE>
    * ElementListener</CODE>.
    * 
    * @param listener
    *            an <CODE>ElementListener</CODE>
    * @return <CODE>true</CODE> if the element was processed successfully
    */
    public function process(ElementListener $listener) {
        try {
            return $listener->add($this);
        }
        catch (DocumentException $de) {
            return FALSE;
        }
    }
    
    /**
    * Gets the type of the text element.
    * 
    * @return a type
    */
    public function type() {
        return Element::CHUNK;
    }
    
    /**
    * Gets all the chunks in this element.
    * 
    * @return an <CODE>ArrayList</CODE>
    */
    public function getChunks() {
        $tmp = array();
        array_push($tmp, $this);
        return $tmp;
    }
    
    /**
    * appends some text to this <CODE>Chunk</CODE>.
    * 
    * @param string
    *            <CODE>String</CODE>
    * @return a <CODE>StringBuffer</CODE>
    */
    public function append(string $string) {
        return $this->content .= $string;
    }
    
    /**
    * Sets the font of this <CODE>Chunk</CODE>.
    * 
    * @param font
    *            a <CODE>Font</CODE>
    */
    public function setFont(Font $font) {
        $this->font = $font;
    }
    
    /**
    * Gets the font of this <CODE>Chunk</CODE>.
    * 
    * @return a <CODE>Font</CODE>
    */
    public function getFont() {
        return $this->font;
    }
    
    /**
    * Returns the content of this <CODE>Chunk</CODE>.
    * 
    * @return a <CODE>String</CODE>
    */
    public function getContent() {
        return $this->content;
    }
    
    /**
    * Returns the content of this <CODE>Chunk</CODE>.
    * 
    * @return a <CODE>String</CODE>
    */
    public function toString() {
        return $this->getContent();
    }
    
    /**
    * Checks is this <CODE>Chunk</CODE> is empty.
    * 
    * @return <CODE>false</CODE> if the Chunk contains other characters than
    *         space.
    */
    public function isEmpty() {
        return (strlen(trim($this->content)) == 0) && (strpos($this->content, '\n') == FALSE) && ($this->attributes == NULL); 
    }
    
    /**
    * Gets the width of the Chunk in points.
    * 
    * @return a width in points
    */
    public function getWidthPoint() {
        if ($this->getImage() != NULL) {
            return this->getImage()->getScaledWidth();
        }
        return this->font->getCalculatedBaseFont(TRUE)->getWidthPoint($this->getContent(), $font->getCalculatedSize()), $this->getHorizontalScaling();
    }
    
    
    
    public static function initializeStatics() {
        if(Chunk::$initialized == FALSE) {
            Chunk::$NEWLINE = new Chunk("\n");
            Chunk::$NEXTPAGE = new Chunk("");
            Chunk::$NEXTPAGE->setNewPage();
            Chunk::$initialized = TRUE;
        }
    }
}

Chunk::initializeStatics();
?>