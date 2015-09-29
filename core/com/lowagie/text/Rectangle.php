<?php
namespace com\lowagie\text;
require_once "Element.php";
require_once "ElementListener.php";
require_once "DocumentException.php";
require_once "TypeChecker.php";
require_once dirname(__FILE__) . "/../../../php/lang/IllegalArgumentException.php";
require_once dirname(__FILE__) . "/../../../php/awt/Color.php";
require_once "pdf/GrayColor.php";

use com\lowagie\text\Element as Element;
use com\lowagie\text\ElementListener as ElementListener;
use com\lowagie\text\DocumentException as DocumentException;
use php\lang\IllegalArgumentException as IllegalArgumentException;
use php\awt\Color as Color;
use com\lowagie\text\pdf\GrayColor as GrayColor;
use com\lowagie\text\TypeChecker as TypeChecker;
class Rectangle implements Element
{
    const UNDEFINED = -1;
    const TOP = 1;
    const BOTTOM = 2;
    const LEFT = 4;
    const RIGHT = 8;
    const NO_BORDER = 0;
    public static $BOX = 0;
    /**
    Member Variables 
    **/
    protected $llx;
    protected $lly;
    protected $urx;
    protected $ury;
    protected $rotation = 0;
    protected $border = Rectangle::UNDEFINED;
    protected $borderWidth = Rectangle::UNDEFINED;
    protected $borderColor = NULL;
    protected $backgroundColor = NULL;
    protected $useVariableBorders = false;
    protected $borderWidthLeft = Rectangle::UNDEFINED;
    protected $borderWidthRight = Rectangle::UNDEFINED;
    protected $borderWidthTop = Rectangle::UNDEFINED;
    protected $borderWidthBottom = Rectangle::UNDEFINED;
    protected $borderColorLeft = NULL;
    protected $borderColorRight = NULL;
    protected $borderColorTop = NULL;
    protected $borderColorBottom = NULL;


    public function __construct()
    {
        $argCount = func_num_args();
        switch($argCount)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Rectangle)
                {
                    $this->construct1argRectangle($arg1);
                }
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_float($arg1) == TRUE && is_float($arg2) == TRUE)
                {
                    $this->construct2argFloat($arg1, $arg2);
                }
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (is_float($arg1) == TRUE && is_float($arg2) == TRUE && is_float($arg3) == TRUE && is_float($arg4) == TRUE)
                {
                    $this->construct4argFloat($arg1, $arg2, $arg3, $arg4);
                }
                break;
            }
        }
    }


    private function construct4argFloat($arg1, $arg2, $arg3, $arg4)
    {
        $this->llx = $arg1;
        $this->lly = $arg2;
        $this->urx = $arg3;
        $this->ury = $arg4;
    }

    private function construct2argFloat($arg1, $arg2)
    {
        $this->construct4argFloat(0,0,$arg1, $arg2);
    }

    private function construct1argRectangle($arg1)
    {
        $this->construct4argFloat($arg1->llx, $arg1->lly, $arg1->urx, $arg1->ury);
        $this->cloneNonPositionParameters($arg1);
    }

    public function process(ElementListener $listener)
    {
        try
        {
            return $listener->add($this);
        }
        catch (DocumentException $de)
        {
            return FALSE;
        }
    }

    public function type()
    {
        return Element::RECTANGLE;
    }

    public function getChunks()
    {
        return array();
    }

    public function isContent()
    {
        return TRUE;
    }

    public function isNestable()
    {
        return FALSE;
    }

    public function setLeft($value)
    {
       TypeChecker::checkForFloat($value);
       $llx = $value;
    }

    public function getLeft()
    {
        $numberArgs = func_num_args();
        switch ($numberArgs)
        {
            case 0:
            {
                $this->getLeftZeroArgs();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                $this->getLeft1Arg($arg1);
                break;
            }
            default:
            {
                throw new IllegalArgumentException("Invalid Number of Arguments");
            }
        }
    }


    private function getLeftZeroArgs()
    {
        return $this->llx;
    }

    private function getLeft1Arg($margin)
    {
        TypeChecker::checkForFloat($margin);
        return $this->llx + $this->margin;
    }

    public function setRight($value)
    {
        $this->checkForFloat($value);
        $this->llx = $value;
    }

    public function getRight()
    {
        $numberArgs = func_num_args();
        switch ($numberArgs)
        {
            case 0:
            {
                $this->getRightZeroArgs();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                $this->getRight1Arg($arg1);
                break;
            }
            default:
            {
                throw new IllegalArgumentException("Invalid Number of Arguments");
            }
        }
    }

    private function getRightZeroArgs()
    {
        return $this->urx;
    }

    private function getRight1Arg($margin)
    {
        TypeChecker::checkForFloat($margin);
        return $this->urx - $margin;
    }

    public function getWidth()
    {
        return $this->urx - $llx;
    }

    public function setTop($value)
    {
        TypeChecker::checkForFloat($value);
        $this->ury = $value;
    }

    public function getTop()
    {
        $numberArgs = func_num_args();
        switch ($numberArgs)
        {
            case 0:
            {
                $this->getTopZeroArgs();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                $this->getTop1Arg($arg1);
                break;
            }
            default:
            {
                throw new IllegalArgumentException("Invalid Number of Arguments");
            }
        }
    }

    private function getTopZeroArgs()
    {
        return $this->ury;
    }

    private function getTop1Arg($margin)
    {
        TypeChecker::checkForFloat($margin);
        return $this->ury - $margin;
    }

    public function setBottom($value)
    {
        $this->lly = $value;
    }

    public function getBottom()
    {
        $numberArgs = func_num_args();
        switch ($numberArgs)
        {
            case 0:
            {
                $this->getBottomZeroArgs();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                $this->getBottom1Arg($arg1);
                break;
            }
            default:
            {
                throw new IllegalArgumentException("Invalid Number of Arguments");
            }
        }
    }

    private function getBottomZeroArgs()
    {
        return $this->lly;
    }

    private function getBottom1Arg($margin)
    {
        TypeChecker::checkForFloat($margin);
        return $lly + $margin;
    }

    public function getHeight()
    {
        return $ury - $lly;
    }

    public function normalize()
    {
        if ($this->llx > $this->urx)
        {
            $a = $this->llx;
            $this->llx = $this->urx;
            $this->urx = $a;
        }
        if ($this->lly > $this->ury)
        {
            $a = $this->lly;
            $this->lly = $this->ury;
            $this->ury = a;
        }
    }

    public function getRotation()
    {
        return $this->rotation;
    }

    public function rotate()
    {
        $rect = new Rectange($this->lly, $this->llx, $this->ury, $this->urx);
        $rect->rotation = $this->rotation + 90;
        $rect->rotation %= 360;
        return $rect;
    }

    public function getBorder()
    {
        return $this->border;
    }

    public function getBorders()
    {
        return ($this->border > 0) && (($this->borderWidth > 0) || ($this->borderWidthLeft > 0) || ($this->borderWidthRight > 0) || ($this->borderWidthTop > 0) || ($this->borderWidthBottom > 0));
    }

    public function hasBorder($type)
    {
        TypeChecker::checkForInteger($type);
        return $this->border != Rectangle::UNDEFINED && ($this->border & $type) == $type;
    }

    public function setBorder($value)
    {
        TypeChecker::checkForInteger($value);
        $this->border = $value;
    }

    public function enableBorderSide($side)
    {
        TypeChecker::checkForInteger($side);
        if ($this->border == Rectangle::UNDEFINED)
        {
            $this->border = 0;
        }
        $this->border |= $side;
    }

    public function disableBorderSide($side)
    {
        TypeChecker::checkForInteger($side);
        if ($this->border == Rectangle::UNDEFINED) 
        {
            $this->border = 0;
        }
        $this->border &= ~$side;
    }


    public function getBorderWidth()
    {
        return $this->borderWidth;
    }

    public function setBorderWidth($value)
    {
        TypeChecker::checkForFloat($value);
        $this->borderWidth = $value;
    }

    public function getBorderColor()
    {
        return $this->borderColor;
    }

    public function setBorderColor(Color $value)
    {
        $this->borderColor = $value;
    }

    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(Color $value)
    {
        $this->backgroundColor = $value;
    }

    public function getGreyFill()
    {
        if ($this->backgroundColor instanceof GrayColor)
            return $this->backgroundColor->getGray();
        else
            return 0;
    }

    public function setGrayFill($value)
    {
        TypeChecker::checkForFloat($value);
        $this->backgroundColor = new GrayColor($value);
    }

    public function isUseVariableBorders()
    {
        return $this->useVariableBorders;
    }

    public function setUseVariableBorders($useVariableBorders)
    {
        TypeChecker::checkForBool($useVariableBorders);
        $this->useVariableBorders = $useVariableBorders;
    }

    private function getVariableBorderWidth($variableWidthValue, $side)
    {
        TypeChecker::checkForFloat($variableWidthValue);
        TypeChecker::checkForInteger($side);
        if (($this->border & $side) != 0)
        {
            return $variableWidthValue != Rectangle::UNDEFINED ? $variableWidthValue : $this->borderWidth;
        }
    }

    private function updateBorderBasedOnWidth($width, $side)
    {
        TypeChecker::checkForFloat($width);
        TypeChecker::checkForInteger($side);
        $this->useVariableBorders = TRUE;
        if ($width > 0)
        {
            $this->enableBorderSide($side);
        }
        else
        {
            $this->disableBorderSide($side);
        }
    }

    public function getBorderWidthLeft()
    {
        $this->getVariableBorderWidth($this->borderWidthLeft, Rectangle::LEFT);
    }

    public function setBorderWidthLeft($borderWidthLeft)
    {
        TypeChecker::checkForFloat($borderWidthLeft);
        $this->borderWidthLeft = $borderWidthLeft;
        $this->updateBorderBasedOnWidth($borderWidthLeft, Rectangle::LEFT);
    }

    public function getBorderWidthRight()
    {
        return $this->getVariableBorderWidth($this->borderWidthRight, Rectangle::RIGHT);
    }

    public function setBorderWidthRight($borderWidthRight)
    {
        TypeChecker::checkForFloat($borderWidthRight);
        $this->borderWidthRight = $borderWidthRight;
        $this->updateBorderBasedOnWidth($borderWidthRight, Rectangle::RIGHT);
    }

    public function getBorderWidthTop()
    {
        return $this->getVariableBorderWidth($this->borderWidthTop, Rectangle::TOP);
    }

    public function setBorderWidthTop($borderWidthTop)
    {
        TypeChecker::checkForFloat($borderWidthTop);
        $this->borderWidthTop = $borderWidthTop;
        $this->updateBorderBasedOnWidth($borderWidthTop, Rectangle::TOP);
    }

    public function getBorderWidthBottom()
    {
        return $this->getVariableBorderWidth($this->borderWidthBottom, Rectangle::BOTTOM);
    }

    public function setBorderWidthBottom($borderWidthBottom)
    {
        TypeChecker::checkForFloat($borderWidthBottom);
        $this->borderWidthBottom = $borderWidthBottom;
        $this->updateBorderBasedOnWidth($borderWidthBottom, Rectangle::BOTTOM);
    }

    public function getBorderColorLeft()
    {
        if ($this->borderColorLeft == NULL)
        {
            return $this->borderColor;
        }

        return $this->borderColorLeft;
    }

    public function setBorderColorLeft(Color $value)
    {
        $this->borderColorLeft = $value;
    }

    public function getBorderColorRight()
    {
        if ($this->borderColorRight == NULL)
        {
            return $this->borderColor;
        }

        return $this->borderColorRight;
    }

    public function setBorderColorRight(Color $value)
    {
        $this->borderColorRight = $value;
    }

    public function getBorderColorTop()
    {
        if ($this->borderColorTop == NULL)
        {
            return $this->borderColor;
        }

        return $this->borderColorTop;
    }

    public function setBorderColorTop(Color $value)
    {
        $this->borderColorTop = $value;
    }

    public function getBorderColorBottom()
    {
        if ($this->borderColorBottom == NULL)
        {
            return $this->borderColor;
        }

        return $this->borderColorBottom;
    }

    public function setBorderColorBottom(Color $value)
    {
        $this->borderColorBottom = $value;
    }

    public function rectangle($top, $bottom)
    {
        TypeChecker::checkForFloat($top);
        TypeChecker::checkForFloat($bottom);
        $tmp = new Rectangle($this);
        if ($this->getTop() > $top) 
        {
            $tmp->setTop($top);
            $tmp->disableBorderSide(Rectangle::TOP);
        }
        if ($this->getBottom() < $bottom)
        {
            $tmp->setBottom($bottom);
            $tmp->disableBorderSide(Rectangle::BOTTOM);
        }
        return $tmp;
    }

    public function cloneNonPositionParameters(Rectangle $rect)
    {
        $this->rotation = $rect->rotation;
        $this->border = $rect->border;
        $this->borderWidth = $rect->borderWidth;
        $this->borderColor = $rect->borderColor;
        $this->backgroundColor = $rect->backgroundColor;
        $this->useVariableBorders = $rect->useVariableBorders;
        $this->borderWidthLeft = $rect->borderWidthLeft;
        $this->borderWidthRight = $rect->borderWidthRight;
        $this->borderWidthTop = $rect->borderWidthTop;
        $this->borderWidthBottom = $rect->borderWidthBottom;
        $this->borderColorLeft = $rect->borderColorLeft;
        $this->borderColorRight = $rect->borderColorRight;
        $this->borderColorTop = $rect->borderColorTop;
        $this->borderColorBottom = $rect->borderColorBottom;
    }

    public function softCloneNonPositionParameters(Rectangle $rect) 
    {
        if ($rect->rotation != 0)
        {
            $this->rotation = $rect->rotation;
        }
        if ($rect->border != Rectangle::UNDEFINED)
        {
            $this->border = $rect->border;
        }
        if ($rect->borderWidth != Rectangle::UNDEFINED)
        {
            $this->borderWidth = $rect->borderWidth;
        }
        if ($rect->borderColor != NULL)
        {
            $this->borderColor = $rect->borderColor;
        }
        if ($rect->backgroundColor != NULL)
        {
            $this->backgroundColor = $rect->backgroundColor;
        }
        if ($this->useVariableBorders == TRUE)
        {
            $this->useVariableBorders = $rect->useVariableBorders;
        }
        if ($rect->borderWidthLeft != Rectangle::UNDEFINED)
        {
            $this->borderWidthLeft = $rect->borderWidthLeft;
        }
        if ($rect->borderWidthRight != Rectangle::UNDEFINED)
        {
            $this->borderWidthRight = $rect->borderWidthRight;
        }
        if ($rect->borderWidthTop != Rectangle::UNDEFINED)
        {
            $this->borderWidthTop = $rect->borderWidthTop;
        }
        if ($rect->borderWidthBottom != Rectangle::UNDEFINED)
        {
            $this->borderWidthBottom = $rect->borderWidthBottom;
        }
        if ($rect->borderColorLeft != NULL)
        {
            $this->borderColorLeft = $rect->borderColorLeft;
        }
        if ($rect->borderColorRight != NULL)
        {
            $this->borderColorRight = $rect->borderColorRight;
        }
        if ($rect->borderColorTop != NULL)
        {
            $this->borderColorTop = $rect->borderColorTop;
        }
        if ($rect->borderColorBottom != NULL)
        {
            $this->borderColorBottom = $rect->borderColorBottom;
        }
    }

    public function toString() 
    {
        $buf = "Rectangle: ";
        $buf .= getWidth();
        $buf .= 'x';
        $buf .= getHeight();
        $buf .= " (rot: ";
        $buf .= rotation;
        $buf .= " degrees)";
        return buf;
    }

    
}

?>