<?php
namespace com\lowagie\text\pdf;
require_once dirname(__FILE__) . "/../Element.php";
require_once "GraphicState.php";

use com\lowagie\text\pdf\PdfContentByte\GraphicState as GraphicState;
use com\lowagie\text\Element as Element;

class PdfContentByte
{
    const ALIGN_CENTER = Element::ALIGN_CENTER;
    const ALIGN_LEFT = Element::ALIGN_LEFT;
    const ALIGN_RIGHT = Element::ALIGN_RIGHT;
    const LINE_CAP_BUTT = 0;
    const LINE_CAP_ROUND = 1;
    const LINE_CAP_PROJECTING_SQUARE = 2;
    const LINE_JOIN_MITER = 0;
    const LINE_JOIN_ROUND = 1;
    const LINE_JOIN_BEVEL = 2;
    const TEXT_RENDER_MODE_FILL = 0;
    const TEXT_RENDER_MODE_STROKE = 1;
    const TEXT_RENDER_MODE_FILL_STROKE = 2;
    const TEXT_RENDER_MODE_INVISIBLE = 3;
    const TEXT_RENDER_MODE_FILL_CLIP = 4;
    const TEXT_RENDER_MODE_STROKE_CLIP = 5;
    const TEXT_RENDER_MODE_FILL_STROKE_CLIP = 6;
    const TEXT_RENDER_MODE_CLIP = 7;
    private $unitRect = array(0, 0, 0, 1, 1, 0, 1, 1);
    protected $content = NULL;
    protected $writer = NULL;
    protected $pdf = NULL;
    protected $state = new GraphicState();
}
?>