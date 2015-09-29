<?php
namespace com\lowagie\text\pdf\PdfContentByte;
require_once dirname(__FILE__) . "/../../../../php/lang/IllegalArgumentException.php";
use php\lang\IllegalArgumentException as IllegalArgumentException;

class GraphicState
{
    public $fontDetails = NULL;
    public $colorDetails = NULL;
    public $size = 0.0;
    public $xTLM = 0.0;
    public $yTLM = 0.0;
    public $leading = 0.0;
    public $scale = 0.0;
    public $charSpace = 0.0;
    public $wordSpace = 0;

    public function __construct()
    {
        $argCount = func_num_args();
        switch($argCount)
        {
            case 0:
            {
                return;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof GraphicState)
                {
                    $this->construct1argGraphicState($arg1);
                }
                else
                {
                    throw new IllegalArgumentException("GraphicState does not support supplied argument type " . gettype($arg1));
                }
                break;
            }
        }


    }


    private function construct1argGraphicState(GraphicState $cp)
    {
        $this->fontDetails = $cp->fontDetails;
        $this->colorDetails = $cp->colorDetails;
        $this->size = $cp->size;
        $this->xTLM = $cp->xTLM;
        $this->yTLM = $cp->yTLM;
        $this->leading = $cp->leading;
        $this->scale = $cp->scale;
        $this->charSpace = $cp->charSpace;
        $this->wordSpace = $cp->wordSpace;
    }
}
?>