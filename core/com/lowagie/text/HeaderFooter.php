<?php

namespace com\lowagie\text;

require_once "Rectangle.php";
require_once "Phrase.php";
require_once "Paragraph.php";

use com\lowagie\text\Rectangle as Rectangle;
use com\lowagie\text\Phrase as Phrase;
use com\lowagie\text\Paragraph as Paragraph;

class HeaderFooter extends Rectangle
{
    /** Does the page contain a pagenumber? */
    private $numbered = FALSE;
    /** This is the <CODE>Phrase</CODE> that comes before the pagenumber. */
    private $before = NULL;
    /** This is number of the page. */
    private $pageN = 0 ;
    /** This is the <CODE>Phrase</CODE> that comes after the pagenumber. */
    private $after = NULL;
    /** This is alignment of the header/footer. */
    private $alignment;
    
    public function __construct()
    {
        $argCount = func_num_args();
        switch($argCount)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                
                if (($arg1 instanceof Phrase) == TRUE && ($arg2 instanceof Phrase) == TRUE)
                {
                    $this->construct2argPhrasePhrase($arg1, $arg2);
                }
                elseif (($arg1 instanceof Phrase) == TRUE && is_bool($arg2) == TRUE)
                {
                    $this->construct2argPhraseBool($arg1, $arg2);
                }
                break;
            }
            default:
                parent::__construct(0,0,0,0);
            
        }
    }
    
    private function construct2argPhrasePhrase($before, $after)
    {
        echo "2phase";
        parent::__construct(0,0,0,0);
        parent::setBorder(parent::TOP + parent::BOTTOM);
        parent::setBorderWidth(1.0);
        $this->numbered = TRUE;
        $this->before = $before;
        $this->after = $after;
    }
    
    private function construct2argPhraseBool($before, $numbered)
    {
        echo "1phase1bool";
        parent::__construct(0,0,0,0);
        parent::setBorder(parent::TOP + parent::BOTTOM);
        parent::setBorderWidth(1.0);
        $this->numbered = $numbered;
        $this->before = $before;
    }
    
    public function isNumbered() {
        return $this->numbered;
    }
    
    public function getBefore() {
        return $this->before;
    }
    
    public function getAfter() {
        return $this->after;
    }
    
    public function setPageNumber($pageN) {
        $this->pageN = $pageN;
    }
    
    public function setAlignment($alignment) {
        $this->alignment = $alignment;
    }
    
    public function paragraph() {
        $paragraph = new Paragraph($this->before->getLeading());
        $paragraph->add($before);
        if ($this->numbered) {
            $paragraph->addSpecial(new Chunk(strval(pageN), $this->before->getFont()));
        }
        if ($this->after != NULL) {
            $paragraph->addSpecial($this->after);
        }
        $this->paragraph->setAlignment($this->alignment);
        return $paragraph;
    }
}

?>