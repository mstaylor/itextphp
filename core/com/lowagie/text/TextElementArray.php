<?php
namespace com\lowagie\text;

require_once "Element.php";

use com\lowagie\text\Element as Element;

interface TextElementArray extends Element
{
    public function add($o);
}

?>