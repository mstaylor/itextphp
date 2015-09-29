<?php
namespace com\lowagie\text\pdf;


interface ExtraEncoding
{

    public function charToByte($text, $encoding);
    public function byteToChar($b, $encoding);


}

?>