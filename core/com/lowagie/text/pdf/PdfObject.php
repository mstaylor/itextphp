<?php
namespace com\lowagie\text\pdf;

abstract class PdjObject
{
    const BOOLEAN = 1;
    const NUMBER = 2;
    const STRING = 3;
    const NAME = 4;
    const anARRAY = 5;
    const DICTIONARY = 6;
    const STREAM = 7;
    const NULL = 8;
    const INDIRECT = 10;
    const NOTHING = "";
    const TEXT_PDFDOCENCODING = "PDF";
    const TEXT_UNICODE = "UnicodeBig";
    protected $bytes;//resource
    protected $type;//integer
    protected $indRef;//PRIndirectReference
    
}

?>