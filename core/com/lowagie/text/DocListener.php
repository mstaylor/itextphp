<?php
namespace com\lowagie\text;
require_once "Element.php";
require_once "Rectangle.php";

use php\util\EventListener as EventListener;
use com\lowagie\text\Rectangle as Rectangle;

interface DocListener extends EventListener
{
    public function open();
    public function close();
    public function newPage();
    public function setPageSize(Rectangle $pageSize);
    public function setMargins($marginLeft, $marginRight, $marginTop, $marginBottom);
    public function setMarginMirroring($marginMirroring);
    public function setPageCount($pageN);
    public function resetPageCount();
    public function setHeader(HeaderFooter $header);
}


?>