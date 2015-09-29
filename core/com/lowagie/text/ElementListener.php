<?php
namespace com\lowagie\text;
require_once dirname(__FILE__) . "/../../../php/util/EventListener.php";
require_once "Element.php";

use php\util\EventListener as EventListener;
use com\lowagie\text\Element as Element;

interface ElementListener extends EventListener
{
    public function add(Element $element);
}
?>