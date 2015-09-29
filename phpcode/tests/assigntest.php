<?php

//$test = array(2);
//echo $test[1];

//$var = "100"/100;
//echo $var;

//$anewarray = array();
//$anewarray["bud"] = "cool";

//echo $anewarray["bud"];

//$text = "test";
//echo 100 << 4;
//$theTest = new testing();
//$test = '\n';

//if ($test == '\n')
//echo "cool";

$testing = array();

$testing[0] = "test";
$testing["bud"] = "cool";
var_dump($testing);

$testing2 = array_values(&$testing);

var_dump($testing2);

$testing2[1] = "shit";

var_dump($testing2);

var_dump($testing);

echo "<BR>";

$str = pack("c", 100);
echo bin2hex($str);
class testing
{
protected function __construct()
{
}

}
?>