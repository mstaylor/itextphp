<?php
//tester2::$testvar = array();

function cool()
{
return 1;
}

//echo tester::$myvar;
//tester::$thetest = array(tester2::$testvar);
$val = 1.43;
$val += 2;
echo (float)$val;

$varclassinher = new tester2a();
$varclassinher->test3();

testinheritanceagain($varclassinher);
$test1222 = "bud[]cool[]test[]dfadf";

$theArray = preg_split("/[\s\[\]]+/", $test1222);

echo count($theArray);

$testneg = 10;
echo -$testneg+1;

echo "<BR>";

$testchar = ' ';

if (($c=' ') == $testchar)
    echo "cool";

$testingc = "test";
$c = $testingc[0];

echo (-100 & 0xff00);

$i = 100;

echo ord('0');
echo ord('1');

$testarray = array();

$testarray[0] = array();
$testarray[0][0] = array();

echo "<BR>";
var_dump($testarray);

echo "<BR>";
$testtext = "cool";

if (is_resource($testtext))
echo "true";
else
echo "false";

echo "<BR>";


class tester
{
public static $myvar = array(tester2::testbud);
const testing = tester2::testbud;
static $atestdf = array(1,2);
const budtest = FALSE;
const budtest2 = '1';
const budtest2 = tester::budtest2;
protected $testingbal = tester::testbud1;
private static $ert = 1;
private $testvar = array(1);
public final function testme()
{
echo "cool";
}

//public static $test58 = tester2::$testing56;
public static function initstatic()
{
   if (tester::$myvar == NULL)
   {
   tester::$myvar = "cool";
   }
tester2::$testing56 = "hello world";
}

public function __construct()
{
initstatic();
}


}



class tester2
{
const testbud = 1;
//public static $testvar;
public static $testing56;

public function test3()
{
   echo "bud";
}
}

class tester2a extends tester2
{
public function test3()
{
  parent::test3();
}
public function test4()
{
echo "cool baby";
}
}

function testinheritanceagain(tester2 $theValue)
{
$theValue->test4();
}
?>