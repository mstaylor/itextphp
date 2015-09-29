<?php

//$CRLF_CID_NEWLINE = array(1,2);
    //$CRLF_CID_NEWLINE[]{{(byte)'\n'}, {(byte)'\r', (byte)'\n'}};
    $CRLF_CID_NEWLINE[0][0] = pack("A",'\n');
    $CRLF_CID_NEWLINE[1][0] = pack("A",'\r');
    $CRLF_CID_NEWLINE[1][1] = pack("A",'\n');



if (strcmp($CRLF_CID_NEWLINE[0],"Arraya")==0)
echo "cool";

$test2 = array(array(256));

$line = "cadfadfadf sfgsfg";
$tk = preg_split("/[\s]+/", $line);
var_dump($tk);
//$t = string[256];
$t = 'a';
$tres =  pack("c*", $t);
var_dump($tres);


$data = "\u2762";

echo intval("0161",16);
echo "<BR>";
//echo ord("0x0161");
//$arrayb = array(intval(0061,16));
//echo intval("1",16);
$testa = array(0x2014);

echo $testa[0];

echo "<BR>";

echo intval('\n',16);


$testad = array();
$testad[0] = array(1);

var_dump($testad);

echo "<BR>";
echo $testad[0][0];
?>