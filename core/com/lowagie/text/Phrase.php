<?php
namespace com\lowagie\text;

require_once "TextElementArray.php";
require_once "ElementListener.php";
require_once "Element.php";

use com\lowagie\text\TextElementArray as TextElementArray;
use com\lowagie\text\ElementListener as ElementListener;
use com\lowagie\text\Element as Element;

class Phrase implements TextElementArray, \Iterator
{
    protected $leading = NAN;
    private $position = 0;
    private $array = array();
    
    public function add($o)
    {
    
    }
    
    public function process(ElementListener $listener) {
    }
    
    public function type() {
        return Element::PHRASE;
    }
    
    public function isContent() {
        return TRUE;
    }
    
    public function isNestable() {
        return TRUE;
    }
    
    public function getChunks() {
        $tmp = array();
        $i = $this;
        foreach($i as $key => $value) {
            $this->addAll($tmp, $value->getChunks());
        }
        return $tmp;
    }
    
    public function toString()
    {
        return "";
    }
    
    private function addAll(array $dest, array $src)
    {
        foreach ($src as $i => $value) {
            array_push($dest, $value);
        }
    }
    
    
    function rewind() {
       $this->position = 0;
    }

    function current() {
       return $this->array[$this->position];
    }

    function key() {
       return $this->position;
    }

    function next() {
       ++$this->position;
    }

    function valid() {
       return isset($this->array[$this->position]);
    }
}

?>