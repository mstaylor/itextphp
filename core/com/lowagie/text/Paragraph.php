<?php
namespace com\lowagie\text;
require_once dirname(__FILE__) . "/../../../php/lang/TypeHint.php";
require_once "Phrase.php";
require_once "Element.php";
require_once "Chunk.php";
require_once "Font.php";
require_once "Image.php";
require_once "List.php";
require_once "ElementTags.php";


use com\lowagie\text\Phrase as Phrase;
use com\lowagie\text\Element as Element;
use com\lowagie\text\Chunk as Chunk;
use com\lowagie\text\Font as Font;
use com\lowagie\text\Image as Image;
use com\lowagie\text\List as List;
use com\lowagie\test\ElementTags as ElementTags;


class Paragraph extends Phrase
{
    /** The alignment of the text. */
    protected $alignment = Element::ALIGN_UNDEFINED;//int
    
    /** The text leading that is multiplied by the biggest font size in the line. */
    protected $multipliedLeading = 0.0;//float
    
    /** The indentation of this paragraph on the left side. */
    protected $indentationLeft;
    
    /** The indentation of this paragraph on the right side. */
    protected $indentationRight;//float
    
    /** Holds value of property firstLineIndent. */
    private $firstLineIndent = 0.0;//float
    
    /** The spacing before the paragraph. */
    protected $spacingBefore;//float
    
    /** The spacing after the paragraph. */
    protected $spacingAfter;//float
    
    /** Holds value of property extraParagraphSpace. */
    private $extraParagraphSpace = 0.0;//float
    
    /** Does the paragraph has to be kept together on 1 page. */
    protected $keeptogether = false;//float
    
    /**
    * Constructs a <CODE>Paragraph</CODE>.
    */
    public function __construct() {
        $argCount = func_num_args();
        switch($argCount) {
            case 1: {
                $arg1 = func_get_arg(0);
                if (($arg1 instanceof Chunk) == TRUE) {
                    $this->construct1argChunk($arg1);
                }
                elseif (is_float($arg1)) {
                    $this->construct1argFloat($arg1);
                }
                elseif (is_string($arg1)) {
                    $this->construct1argString($arg1);
                }
                elseif ( ($arg1 instanceof parent) == TRUE) {
                    $this->construct1argPhrase($arg1);
                }
                break;
            }
            case 2: {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_float($arg1) && ($arg2 instanceof Chunk) == TRUE) {
                    $this->construct2argFloatChunk($arg1, $arg2);
                }
                else if (is_string($arg1) && ($arg2 instanceof Font) == TRUE) {
                    $this->construct2argStringFont($arg1, $arg2);
                }
                else if (is_float($arg1) && is_string($arg2))
                {
                    $this->construct2argFloatString($arg1, $arg2);
                }
                break;
            }
            case 3: {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if (is_float($arg1) && is_string($arg2) && ($arg3 instanceof Font) == TRUE) {
                    $this->construct3argFloatStringFont($arg1, $arg2, $arg3);
                }
                break;
            }
            default:
                parent::__construct();
            
        }
    }
    
    /**
    * Constructs a <CODE>Paragraph</CODE> with a certain leading.
    *
    * @param	leading		the leading
    */
    private function construct1argFloat($leading) {
        parent::__construct($leading);
    }
    
    /**
    * Constructs a <CODE>Paragraph</CODE> with a certain <CODE>Chunk</CODE>.
    *
    * @param	chunk		a <CODE>Chunk</CODE>
    */  
    private function construct1argChunk(Chunk $chunk) {
        parent::__construct($chunk);
    }
    
    /**
    * Constructs a <CODE>Paragraph</CODE> with a certain <CODE>String</CODE>.
    *
    * @param	string		a <CODE>String</CODE>
    */
    private function construct1argString($string){
        parent::__construct($string);
    }
    
    /**
    * Constructs a <CODE>Paragraph</CODE> with a certain <CODE>Phrase</CODE>.
    *
    * @param	phrase		a <CODE>Phrase</CODE>
    */  
    private function construct1argParent(parent $phrase)
    {
        parent::__construct($phrase);
        if (($phrase instanceof Paragraph) == TRUE) {
            $p = $phrase;
            $this->setAlignment($p->alignment);
            $this->setLeading($p->parent::getLeading(), $p->multipliedLeading);
            $this->setIndentationLeft($p->getIndentationLeft());
            $this->setIndentationRight($p->getIndentationRight());
            $this->setFirstLineIndent($p->getFirstLineIndent());
            $this->setSpacingAfter($p->spacingAfter());
            $this->setSpacingBefore($p->spacingBefore());
            $this->setExtraParagraphSpace($p->getExtraParagraphSpace());
        }
    }
    
    /**
    * Constructs a <CODE>Paragraph</CODE> with a certain <CODE>Chunk</CODE>
    * and a certain leading.
    *
    * @param	leading		the leading
    * @param	chunk		a <CODE>Chunk</CODE>
    */ 
    private function construct2argFloatChunk(float $leading, Chunk $chunk) {
        parent::__construct($leading, $chunk);
    }
    
    /**
    * Constructs a <CODE>Paragraph</CODE> with a certain <CODE>String</CODE>
    * and a certain <CODE>Font</CODE>.
    *
    * @param	string		a <CODE>String</CODE>
    * @param	font		a <CODE>Font</CODE>
    */
    private function construct2argStringFont(string $string, Font $font) {
        parent::__construct($string, $font);
    }
    
    /**
    * Constructs a <CODE>Paragraph</CODE> with a certain <CODE>String</CODE>
    * and a certain leading.
    *
    * @param	leading		the leading
    * @param	string		a <CODE>String</CODE>
    */
    private function construct2argFloatString(float $leading, string $string) {
        parent::__construct($leading, $string);
    }
    
    /**
    * Constructs a <CODE>Paragraph</CODE> with a certain leading, <CODE>String</CODE>
    * and <CODE>Font</CODE>.
    *
    * @param	leading		the leading
    * @param	string		a <CODE>String</CODE>
    * @param	font		a <CODE>Font</CODE>
    */
    private function construct3argFloatStringFont(float $leading, string $string, Font $font) {
        parent::__construct($leading, $string, $font);
    }
    
    /**
    * Gets the type of the text element.
    *
    * @return	a type
    */
    public function type() {
        return Element::PARAGRAPH;
    }
    
    /**
    * Adds an <CODE>Object</CODE> to the <CODE>Paragraph</CODE>.
    *
    * @param	o   object		the object to add.
    * @return true is adding the object succeeded
    */
    public function add(object $o) {
       if (($o instanceof List) == TRUE) {
           $o->setIndentationLeft($o->getIndentationLeft() + $this->indentationLeft);
           $o->setIndentationRight($this->indentationRight);
           return parent::add($o);
       }
       elseif (($o instanceof Image) == TRUE) {
           parent::addSpecial($o);
           return TRUE;
       }
       elseif (($o instanceof Paragraph) == TRUE) {
           parent::add($o);
           parent::add(Chunk::NEWLINE);
           return TRUE;
       }
    }
    /**
     * Sets the alignment of this paragraph.
     *
    **/
    public function setAlignment($alignment) {
        if (is_string($alignment)) {
            $this->setAlignmentString($alignment);
        }
        elseif (is_integer($alignment) {
            $this->setAlignmentInt($alignment);
        }
    }
    
    /**
    * Sets the alignment of this paragraph.
    *
    * @param	alignment		the new alignment as a <CODE>String</CODE>
    */
    private function setAlignmentString(string $alignment) {
        $this->alignment = $alignment;
    }
    
    /**
    * Sets the alignment of this paragraph.
    *
    * @param	alignment		the new alignment
    */
    private function setAlignmentInt($alignment) {
        if (strcasecmp(ElementTags::ALIGN_CENTER, $alignment) == 0) {
            $this->alignment = Element::ALIGN_CENTER;
            return;
        }
        if (strcasecmp(ElementTags::ALIGN_RIGHT, $alignment) == 0) {
            $this->alignment = Element::ALIGN_RIGHT;
            return;
        }
        if (strcasecmp(ElementTags::ALIGN_JUSTIFIED, $alignment) == 0) {
            $this->alignment = Element::ALIGN_JUSTIFIED;
            return;
        }
        if (strcasecmp(ElementTags::ALIGN_JUSTIFIED_ALL, $alignment) == 0) {
            $this->alignment = Element::ALIGN_JUSTIFIED_ALL;
            return;
        }
        $this->alignment = Element::ALIGN_LEFT;
    }
    
    
    public function setLeading()
    {
        $argCount = func_num_args();
        switch($argCount) {
            case 1: {
                $arg1 = func_get_arg(0);
                $this->setLeading1arg($arg1);
                break;
            }
            case 2 : {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $this->setLeading2arg($arg1, $arg2);
                break;
            }
        }
    }
    
    /**
    * @see com.lowagie.text.Phrase#setLeading(float)
    */
    private function setLeading1arg(float $fixedLeading) {
        $this->leading = $fixedLeading;
        $this->multipliedLeading = 0.0;
    }
    
    /**
    * Sets the leading fixed and variable. The resultant leading will be
    * fixedLeading+multipliedLeading*maxFontSize where maxFontSize is the
    * size of the biggest font in the line.
    * @param fixedLeading the fixed leading
    * @param multipliedLeading the variable leading
    */
    private function setLeading2arg(float $fixedLeading, float $multipliedLeading) {
        $this->leading = $fixedLeading;
        $this->multipliedLeading = $multipliedLeading;
    }
    
    /**
    * Sets the variable leading. The resultant leading will be
    * multipliedLeading*maxFontSize where maxFontSize is the
    * size of the biggest font in the line.
    * @param multipliedLeading the variable leading
    */
    public function setMultipliedLeading(float $multipliedLeading) {
        $this->leading = 0.0;
        $this->multipliedLeading = multipliedLeading;
    }
    
    /**
    * Sets the indentation of this paragraph on the left side.
    *
    * @param indentation the new indentation
    */    
    public function setIndentationLeft(float $indentation) {
        $this->indentationLeft = $indentation;
    }
    
    /**
    * Sets the indentation of this paragraph on the right side.
    *
    * @param indentation the new indentation
    */
    public function setIndentationRight(float $indentation) {
        $this->indentationRight = $indentation;
    }
    
    /**
    * Setter for property firstLineIndent.
    * @param firstLineIndent New value of property firstLineIndent.
    */
    public function setFirstLineIndent(float $firstLineIndent) {
        $this->firstLineIndent = $firstLineIndent;
    }
    
    /**
    * Sets the spacing before this paragraph.
    *
    * @param spacing the new spacing
    */
    public function setSpacingBefore(float $spacing) {
        $this->spacingBefore = $spacing;
    }
    
    /**
    * Sets the spacing after this paragraph.
    *
    * @param spacing the new spacing
    */
    public function setSpacingAfter(float $spacing) {
        $this->spacingAfter = $spacing;
    }
    
    /**
    * Indicates that the paragraph has to be kept together on one page.
    *
    * @param   keeptogether    true of the paragraph may not be split over 2 pages
    */
    public function setKeepTogether(boolean $keeptogether) {
        $this->keeptogether = $keeptogether;
    }
    
    /**
    * Checks if this paragraph has to be kept together on one page.
    *
    * @return  true if the paragraph may not be split over 2 pages.
    */
    public function getKeepTogether() {
        return $this->keeptogether;
    }
    
    // methods to retrieve information

    /**
    * Gets the alignment of this paragraph.
    *
    * @return alignment
    */
    public function getAlignment() {
        return $this->alignment;
    }
    
    /**
    * Gets the variable leading
    * @return the leading
    */
    public function getMultipliedLeading() {
        return $this->multipliedLeading;
    }
    
    /**
    * Gets the total leading.
    * This method is based on the assumption that the
    * font of the Paragraph is the font of all the elements
    * that make part of the paragraph. This isn't necessarily
    * true.
    * @return the total leading (fixed and multiplied)
    */
    public function getTotalLeading() {
         $m = font == null ? Font::DEFAULTSIZE * $this->multipliedLeading : $font->getCalculatedLeading($this->multipliedLeading);
        if ($m > 0 && !$this->hasLeading()) {
            return $m;
        }
        return $this->getLeading() + $m;
    }
        
    /**
    * Gets the indentation of this paragraph on the left side.
    *
    * @return the indentation
    */
    public function getIndentationLeft() {
        return $this->indentationLeft;
    }
    
    /**
    * Gets the indentation of this paragraph on the right side.
    *
    * @return	the indentation
    */
    public function getIndentationRight() {
        return $this->indentationRight;
    }
    
    /**
    * Getter for property firstLineIndent.
    * @return Value of property firstLineIndent.
    */
    public function getFirstLineIndent() {
        return $this->firstLineIndent;
    }
    
    /**
    * Gets the spacing before this paragraph.
    *
    * @return	the spacing
    */
    public function spacingBefore() {
        return $this->spacingBefore;
    }
    
    
    /**
    * Gets the spacing after this paragraph.
    *
    * @return	the spacing
    */
    public function spacingAfter() {
        return $this->spacingAfter;
    }
    
    /**
    * Getter for property extraParagraphSpace.
    * @return Value of property extraParagraphSpace.
    */
    public function getExtraParagraphSpace() {
        return $this->extraParagraphSpace;
    }
    
    /**
    * Setter for property extraParagraphSpace.
    * @param extraParagraphSpace New value of property extraParagraphSpace.
    */
    public function setExtraParagraphSpace(float $extraParagraphSpace) {
        $this->extraParagraphSpace = $extraParagraphSpace;
    }
    
    
    
    
    
    
    
    
}

?>