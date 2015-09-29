<?php
namespace php\lang;

require_once dirname(__FILE__) . "/../../../php/lang/TypeHint.php";

interface Comparable {
    public function compareTo(object $o);
}

?>