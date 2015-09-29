<?php

$result = pack("A*","test");
var_dump($result);
$result2 = unpack("A*", $result);
var_dump($result2);


echo "<BR>";
echo "value of array is " . $result2[1];


class test
{

}

//
//echo pack("A4", 'Hi t');
function asc2bin ($temp) {
  $len = strlen($temp);
  for ($i=0; $i<$len; $i++) $data.=sprintf("%08b",ord(substr($temp,$i,1)));
  return $data;
}

?>