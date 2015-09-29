<?php

//$var = new testing();

//echo "the actual type is " . $var->type;
$var[] = new testing();
//$var[] = new testing();
var_dump($var);

class testing
{
   public $type = 0;
   const THISTEST = "cool";
   public function __construct()
   {
        $num_args=func_num_args();
        switch ($num_args)
        {
           case 0:
           {
              
              break;
           }
           case 1:
           {
              $type = func_get_arg(0); 
              echo "the type is " . $type;
              echo "<BR>";
              $this->type = $type;
              echo "from here the type is " . $this->type . "<BR>";
              break;
           }
        }
    }
}
?>