<?PHP
/*
 * $Id: MetaDo.php,v 1.1 2005/11/09 15:39:19 mstaylor Exp $
 * $Name:  $
 *
 * Copyright 2005 by Mills W. Staylor, III.
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the License.
 *
 * The Original Code is 'iText, a free Java-PDF library'.
 *
 *
 * Alternatively, the contents of this file may be used under the terms of the
 * LGPL license (the "GNU LIBRARY GENERAL PUBLIC LICENSE"), in which case the
 * provisions of LGPL are applicable instead of those above.  If you wish to
 * allow use of your version of this file only under the terms of the LGPL
 * License and not to allow others to use your version of this file under
 * the MPL, indicate your decision by deleting the provisions above and
 * replace them with the notice and other provisions required by the LGPL.
 * If you do not delete the provisions above, a recipient may use your version
 * of this file under either the MPL or the GNU LIBRARY GENERAL PUBLIC LICENSE.
 *
 * This library is free software; you can redistribute it and/or modify it
 * under the terms of the MPL as stated above or under the terms of the GNU
 * Library General Public License as published by the Free Software Foundation;
 * either version 2 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Library general Public License for more
 * details.
 */

require_once("../../../../awt/Color.php");
require_once("../../../../awt/Point.php");
require_once("../BmpImage.php");
require_once("../../PdfContentByte.php");
require_once("../../BaseFont.php");
require_once("MetaState.php");
require_once("InputMeta.php");
require_once("MetaObject.php");
require_once("MetaPen.php");
require_once("MetaBrush.php");
require_once("MetaFont.php");
require_once("../../../DocumentException.php");
require_once("../../../Image.php");
require_once("../../../../exceptions/UnsupportedEncodingException.php");
require_once("../../../../exceptions/IOException.php");


class MetaDo
{

    const META_SETBKCOLOR            = 0x0201;
    const META_SETBKMODE             = 0x0102;
    const META_SETMAPMODE            = 0x0103;
    const META_SETROP2               = 0x0104;
    const META_SETRELABS             = 0x0105;
    const META_SETPOLYFILLMODE       = 0x0106;
    const META_SETSTRETCHBLTMODE     = 0x0107;
    const META_SETTEXTCHAREXTRA      = 0x0108;
    const META_SETTEXTCOLOR          = 0x0209;
    const META_SETTEXTJUSTIFICATION  = 0x020A;
    const META_SETWINDOWORG          = 0x020B;
    const META_SETWINDOWEXT          = 0x020C;
    const META_SETVIEWPORTORG        = 0x020D;
    const META_SETVIEWPORTEXT        = 0x020E;
    const META_OFFSETWINDOWORG       = 0x020F;
    const META_SCALEWINDOWEXT        = 0x0410;
    const META_OFFSETVIEWPORTORG     = 0x0211;
    const META_SCALEVIEWPORTEXT      = 0x0412;
    const META_LINETO                = 0x0213;
    const META_MOVETO                = 0x0214;
    const META_EXCLUDECLIPRECT       = 0x0415;
    const META_INTERSECTCLIPRECT     = 0x0416;
    const META_ARC                   = 0x0817;
    const META_ELLIPSE               = 0x0418;
    const META_FLOODFILL             = 0x0419;
    const META_PIE                   = 0x081A;
    const META_RECTANGLE             = 0x041B;
    const META_ROUNDRECT             = 0x061C;
    const META_PATBLT                = 0x061D;
    const META_SAVEDC                = 0x001E;
    const META_SETPIXEL              = 0x041F;
    const META_OFFSETCLIPRGN         = 0x0220;
    const META_TEXTOUT               = 0x0521;
    const META_BITBLT                = 0x0922;
    const META_STRETCHBLT            = 0x0B23;
    const META_POLYGON               = 0x0324;
    const META_POLYLINE              = 0x0325;
    const META_ESCAPE                = 0x0626;
    const META_RESTOREDC             = 0x0127;
    const META_FILLREGION            = 0x0228;
    const META_FRAMEREGION           = 0x0429;
    const META_INVERTREGION          = 0x012A;
    const META_PAINTREGION           = 0x012B;
    const META_SELECTCLIPREGION      = 0x012C;
    const META_SELECTOBJECT          = 0x012D;
    const META_SETTEXTALIGN          = 0x012E;
    const META_CHORD                 = 0x0830;
    const META_SETMAPPERFLAGS        = 0x0231;
    const META_EXTTEXTOUT            = 0x0a32;
    const META_SETDIBTODEV           = 0x0d33;
    const META_SELECTPALETTE         = 0x0234;
    const META_REALIZEPALETTE        = 0x0035;
    const META_ANIMATEPALETTE        = 0x0436;
    const META_SETPALENTRIES         = 0x0037;
    const META_POLYPOLYGON           = 0x0538;
    const META_RESIZEPALETTE         = 0x0139;
    const META_DIBBITBLT             = 0x0940;
    const META_DIBSTRETCHBLT         = 0x0b41;
    const META_DIBCREATEPATTERNBRUSH = 0x0142;
    const META_STRETCHDIB            = 0x0f43;
    const META_EXTFLOODFILL          = 0x0548;
    const META_DELETEOBJECT          = 0x01f0;
    const META_CREATEPALETTE         = 0x00f7;
    const META_CREATEPATTERNBRUSH    = 0x01F9;
    const META_CREATEPENINDIRECT     = 0x02FA;
    const META_CREATEFONTINDIRECT    = 0x02FB;
    const META_CREATEBRUSHINDIRECT   = 0x02FC;
    const META_CREATEREGION          = 0x06FF;

    public $cb = NULL;
    public InputMeta in;
    protected $left = 0;
    protected $top = 0;
    protected $right = 0;
    protected $bottom = 0;
    protected $inch = 0;
    $state = NULL; //MetaState

    private function onConstruct()
    {
        $state = new MetaState();
    }

    public function __construct($in, PdfContentByte $cb) {
        if (is_resource($in) == TRUE && strcmp(get_resource_type($in), "anotherByte")==0)
        {
            $this->cb = $cb;
            $this->in = new InputMeta($in);
        }
    }

    public function readAll() {
        if ($in->readInt() != 0x9AC6CDD7) {
            throw new DocumentException("Not a placeable windows metafile");
        }
        $in->readWord();
        $left = $in->readShort();
        $top = $in->readShort();
        $right = $in->readShort();
        $bottom = $in->readShort();
        $inch = $in->readWord();
        $state->setScalingX((float)($right - $left) / (float)$inch * 72.0);
        $state->setScalingY((float)($bottom - $top) / (float)$inch * 72.0);
        $state->setOffsetWx($left);
        $state->setOffsetWy($top);
        $state->setExtentWx($right - $left);
        $state->setExtentWy($bottom - $top);
        $in->readInt();
        $in->readWord();
        $in->skip(18);

        $tsize = 0;
        $function = 0;
        $cb->setLineCap(1);
        $cb->setLineJoin(1);
        for (;;) {
            $lenMarker = $in->getLength();
            $tsize = $in->readInt();
            if ($tsize < 3)
                break;
            $function = $in->readWord();
            switch ($function) {
                case 0:
                    break;
                case MetaDo::META_CREATEPALETTE:
                case MetaDo::META_CREATEREGION:
                case MetaDo::META_DIBCREATEPATTERNBRUSH:
                    $state->addMetaObject(new MetaObject());
                    break;
                case MetaDo::META_CREATEPENINDIRECT:
                {
                    $pen = new MetaPen();
                    $pen->init($in);
                    $state->addMetaObject($pen);
                    break;
                }
                case MetaDo::META_CREATEBRUSHINDIRECT:
                {
                    $brush = new MetaBrush();
                    $brush->init(in);
                    $state->addMetaObject($brush);
                    break;
                }
                case MetaDo::META_CREATEFONTINDIRECT:
                {
                    $font = new MetaFont();
                    $font->init($in);
                    $state->addMetaObject($font);
                    break;
                }
                case MetaDo::META_SELECTOBJECT:
                {
                    $idx = $in->readWord();
                    $state->selectMetaObject($idx, $cb);
                    break;
                }
                case MetaDo::META_DELETEOBJECT:
                {
                    $idx = $in->readWord();
                    $state->deleteMetaObject($idx);
                    break;
                }
                case MetaDo::META_SAVEDC:
                    $state->saveState($cb);
                    break;
                case MetaDo::META_RESTOREDC:
                {
                    $idx = $in->readShort();
                    $state->restoreState($idx, $cb);
                    break;
                }
                case MetaDo::META_SETWINDOWORG:
                    $state->setOffsetWy($in->readShort());
                    $state->setOffsetWx($in->readShort());
                    break;
                case MetaDo::META_SETWINDOWEXT:
                    $state->setExtentWy($in->readShort());
                    $state->setExtentWx($in->readShort());
                    break;
                case MetaDo::META_MOVETO:
                {
                    $y = $in->readShort();
                    $p = new Point($in->readShort(), $y);
                    $state->setCurrentPoint($p);
                    break;
                }
                case MetaDo::META_LINETO:
                {
                    $y = $in->readShort();
                    $x = $in->readShort();
                    $p = $state->getCurrentPoint();
                    $cb->moveTo($state->transformX($p->x), $state->transformY($p->y));
                    $cb->lineTo($state->transformX($x), $state->transformY($y));
                    $cb->stroke();
                    $state->setCurrentPoint(new Point($x, $y));
                    break;
                }
                case MetaDo::META_POLYLINE:
                {
                    $state->setLineJoinPolygon($cb);
                    $len = $in->readWord();
                    $x = $in->readShort();
                    $y = $in->readShort();
                    $cb->moveTo($state->transformX($x), $state->transformY($y));
                    for ($k = 1; $k < $len; ++$k) {
                        $x = $in->readShort();
                        $y = $in->readShort();
                        $cb->lineTo($state->transformX($x), $state->transformY($y));
                    }
                    $cb->stroke();
                    break;
                }
                case MetaDo::META_POLYGON:
                {
                    if (isNullStrokeFill(FALSE) == TRUE)
                        break;
                    $len = $in->readWord();
                    $sx = $in->readShort();
                    $sy = $in->readShort();
                    $cb->moveTo($state->transformX($sx), $state->transformY($sy));
                    for ($k = 1; $k < $len; ++$k) {
                        $x = $in->readShort();
                        $y = $in->readShort();
                        $cb->lineTo($state->transformX($x), $state->transformY($y));
                    }
                    $cb->lineTo($state->transformX($sx), $state->transformY($sy));
                    strokeAndFill();
                    break;
                }
                case MetaDo::META_POLYPOLYGON:
                {
                    if (isNullStrokeFill(FALSE) == TRUE)
                        break;
                    $numPoly = $in->readWord();
                    $lens = array();
                    for ($k = 0; $k < count($lens); ++$k)
                        $lens[$k] = $in->readWord();
                    for ($j = 0; $j < count($lens); ++$j) {
                        $len = $lens[$j];
                        $sx = $in->readShort();
                        $sy = $in->readShort();
                        $cb->moveTo($state->transformX($sx), $state->transformY($sy));
                        for ($k = 1; $k < $len; ++$k) {
                            $x = $in->readShort();
                            $y = $in->readShort();
                            $cb->lineTo($state->transformX($x), $state->transformY($y));
                        }
                        $cb->lineTo($state->transformX($sx), $state->transformY($sy));
                    }
                    strokeAndFill();
                    break;
                }
                case MetaDo::META_ELLIPSE:
                {
                    if (isNullStrokeFill($state->getLineNeutral()) == TRUE)
                        break;
                    $b = $in->readShort();
                    $r = $in->readShort();
                    $t = $in->readShort();
                    $l = $in->readShort();
                    $cb->arc($state->transformX($l), $state->transformY($b), $state->transformX($r), $state->transformY($t), 0, 360);
                    strokeAndFill();
                    break;
                }
                case MetaDo::META_ARC:
                {
                    if (isNullStrokeFill($state->getLineNeutral()) == TRUE)
                        break;
                    $yend = $state->transformY($in->readShort());
                    $xend = $state->transformX($in->readShort());
                    $ystart = $state->transformY($in->readShort());
                    $xstart = $state->transformX($in->readShort());
                    $b = $state->transformY($in->readShort());
                    $r = $state->transformX($in->readShort());
                    $t = $state->transformY($in->readShort());
                    $l = $state->transformX($in->readShort());
                    $cx = ($r + $l) / 2;
                    $cy = ($t + $b) / 2;
                    $arc1 = getArc($cx, $cy, $xstart, $ystart);
                    $arc2 = getArc($cx, $cy, $xend, $yend);
                    $arc2 -= $arc1;
                    if ($arc2 <= 0)
                        $arc2 += 360;
                    $cb->arc($l, $b, $r, $t, $arc1, $arc2);
                    $cb->stroke();
                    break;
                }
                case MetaDo::META_PIE:
                {
                    if (isNullStrokeFill($state->getLineNeutral()) == TRUE)
                        break;
                    $yend = $state->transformY($in->readShort());
                    $xend = $state->transformX($in->readShort());
                    $ystart = $state->transformY($in->readShort());
                    $xstart = $state->transformX($in->readShort());
                    $b = $state->transformY($in->readShort());
                    $r = $state->transformX($in->readShort());
                    $t = $state->transformY($in->readShort());
                    $l = $state->transformX($in->readShort());
                    $cx = ($r + $l) / 2;
                    $cy = ($t + $b) / 2;
                    $arc1 = getArc($cx, $cy, $xstart, $ystart);
                    $arc2 = getArc($cx, $cy, $xend, $yend);
                    $arc2 -= $arc1;
                    if ($arc2 <= 0)
                        $arc2 += 360;
                    ar = PdfContentByte::bezierArc($l, $b, $r, $t, $arc1, $arc2);
                    if (count($ar) == 0)
                        break;
                    $pt = $ar[0];
                    $cb->moveTo($cx, $cy);
                    $cb->lineTo($pt[0], $pt[1]);
                    for ($k = 0; $k < count($ar); ++$k) {
                        $pt = $ar[$k];
                        $cb->curveTo($pt[2], $pt[3], $pt[4], $pt[5], $pt[6], $pt[7]);
                    }
                    $cb->lineTo($cx, $cy);
                    strokeAndFill();
                    break;
                }
                case MetaDo::META_CHORD:
                {
                    if (isNullStrokeFill($state->getLineNeutral()) == TRUE)
                        break;
                    $yend = $state->transformY($in->readShort());
                    $xend = $state->transformX($in->readShort());
                    $ystart = $state->transformY($in->readShort());
                    $xstart = $state->transformX($in->readShort());
                    $b = $state->transformY($in->readShort());
                    $r = $state->transformX($in->readShort());
                    $t = $state->transformY($in->readShort());
                    $l = $state->transformX($in->readShort());
                    $cx = ($r + $l) / 2;
                    $cy = ($t + $b) / 2;
                    $arc1 = getArc($cx, $cy, $xstart, $ystart);
                    $arc2 = getArc($cx, $cy, $xend, $yend);
                    $arc2 -= $arc1;
                    if ($arc2 <= 0)
                        $arc2 += 360;
                    $ar = PdfContentByte::bezierArc($l, $b, $r, $t, $arc1, $arc2);
                    if (count($ar) == 0)
                        break;
                    $pt = $ar[0];
                    $cx = $pt[0];
                    $cy = $pt[1];
                    $cb->moveTo($cx, $cy);
                    for ($k = 0; $k < count($ar); ++$k) {
                        $pt = $ar[$k];
                        $cb->curveTo($pt[2], $pt[3], $pt[4], $pt[5], $pt[6], $pt[7]);
                    }
                    $cb->lineTo($cx, $cy);
                    strokeAndFill();
                    break;
                }
                case MetaDo::META_RECTANGLE:
                {
                    if (isNullStrokeFill(TRUE) == TRUE)
                        break;
                    $b = $state->transformY($in->readShort());
                    $r = $state->transformX($in->readShort());
                    $t = $state->transformY($in->readShort());
                    $l = $state->transformX($in->readShort());
                    $cb->rectangle($l, $b, $r - $l, $t - $b);
                    strokeAndFill();
                    break;
                }
                case MetaDo::META_ROUNDRECT:
                {
                    if (isNullStrokeFill(TRUE) == TRUE)
                        break;
                    $h = $state->transformY(0) - $state->transformY($in->readShort());
                    $w = $state->transformX($in->readShort()) - $state->transformX(0);
                    $b = $state->transformY($in->readShort());
                    $r = $state->transformX($in->readShort());
                    $t = $state->transformY($in->readShort());
                    $l = $state->transformX($in->readShort());
                    $cb->roundRectangle($l, $b, $r - $l, $t - $b, ($h + $w) / 4);
                    strokeAndFill();
                    break;
                }
                case MetaDo::META_INTERSECTCLIPRECT:
                {
                    $b = $state->transformY($in->readShort());
                    $r = $state->transformX($in->readShort());
                    $t = $state->transformY($in->readShort());
                    $l = $state->transformX($in->readShort());
                    $cb->rectangle($l, $b, $r - $l, $t - $b);
                    $cb->eoClip();
                    $cb->newPath();
                    break;
                }
                case MetaDo::META_EXTTEXTOUT:
                {
                    $y = $in->readShort();
                    $x = $in->readShort();
                    $count = $in->readWord();
                    $flag = $in->readWord();
                    $x1 = 0;
                    $y1 = 0;
                    $x2 = 0;
                    $y2 = 0;
                    if (($flag & (MetaFont::ETO_CLIPPED | MetaFont::ETO_OPAQUE)) != 0) {
                        $x1 = $in->readShort();
                        $y1 = $in->readShort();
                        $x2 = $in->readShort();
                        $y2 = $in->readShort();
                    }
                    $text = itextphp_bytes_create($count);
                    $k = 0;
                    for ($k = 0; $k < $count; ++$k) {
                        $c = $in->readByte();
                        if ($c == 0)
                            break;
                        itextphp_bytes_write($text, $k, itextphp_bytes_createfromInt($c), 0);
                    }
                    $s = NULL;
                    try {
                        $s = itextphp_bytes_getBytesBasedonEncoding(substr($text, 0, $k), "Cp1252");
                    }
                    catch (UnsupportedEncodingException $e) {
                        $s = substr($text, 0, $k);
                    }
                    outputText($x, $y, $flag, $x1, $y1, $x2, $y2, $s);
                    break;
                }
                case MetaDo::META_TEXTOUT:
                {
                    $count = $in->readWord();
                    $text = itextphp_bytes_create($count);
                    $k = 0;
                    for ($k = 0; $k < $count; ++$k) {
                         $c = $in->readByte();
                        if ($c == 0)
                            break;
                        itextphp_bytes_write($text, $k, itextphp_bytes_createfromInt($c), 0);
                    }
                    $s = NULL;
                    try {
                        $s = itextphp_bytes_getBytesBasedonEncoding(substr($text, 0, $k), "Cp1252");
                    }
                    catch (UnsupportedEncodingException $e) {
                        $s = substr($text, 0, $k);
                    }
                    $count = ($count + 1) & 0xfffe;
                    $in->skip($count - k);
                    $y = $in->readShort();
                    $x = $in->readShort();
                    outputText($x, $y, 0, 0, 0, 0, 0, $s);
                    break;
                }
                case MetaDo::META_SETBKCOLOR:
                    $state->setCurrentBackgroundColor($in->readColor());
                    break;
                case MetaDo::META_SETTEXTCOLOR:
                    $state->setCurrentTextColor($in->readColor());
                    break;
                case MetaDo::META_SETTEXTALIGN:
                    $state->setTextAlign($in->readWord());
                    break;
                case MetaDo::META_SETBKMODE:
                    $state->setBackgroundMode($in->readWord());
                    break;
                case MetaDo::META_SETPOLYFILLMODE:
                    $state->setPolyFillMode($in->readWord());
                    break;
                case MetaDo::META_SETPIXEL:
                {
                    $color = $in->readColor();
                    $y = $in->readShort();
                    $x = $in->readShort();
                    $cb->saveState();
                    $cb->setColorFill($color);
                    $cb->rectangle($state->transformX($x), $state->transformY($y), .2, .2);
                    $cb->fill();
                    $cb->restoreState();
                    break;
                }
                case MetaDo::META_DIBSTRETCHBLT:
                case MetaDo::META_STRETCHDIB: {
                    $rop = $in->readInt();
                    if ($function == MetaDo::META_STRETCHDIB) {
                        /*int usage = */ $in->readWord();
                    }
                    $srcHeight = $in->readShort();
                    $srcWidth = $in->readShort();
                    $ySrc = $in->readShort();
                    $xSrc = $in->readShort();
                    $destHeight = $state->transformY($in->readShort()) - $state->transformY(0);
                    $destWidth = $state->transformX($in->readShort()) - $state->transformX(0);
                    $yDest = $state->transformY($in->readShort());
                    $xDest = $state->transformX($in->readShort());
                    $b = itextphp_bytes_create(($tsize * 2) - ($in->getLength() - $lenMarker));
                    for ($k = 0; $k < itextphp_bytes_getSize($b); ++$k)
                        itextphp_bytes_write($b, $k, itextphp_bytes_createfromInt($in->readByte()), 0);
                    try {
                        $inb = $b;
                        $bmp = BmpImage::getImage($inb, TRUE, itextphp_bytes_getSize($b));
                        $cb->saveState();
                        $cb->rectangle($xDest, $yDest, $destWidth, $destHeight);
                        $cb->clip();
                        $cb->newPath();
                        $bmp->scaleAbsolute($destWidth * $bmp->width() / $srcWidth, -$destHeight * $bmp->height() / $srcHeight);
                        $bmp->setAbsolutePosition($xDest - $destWidth * $xSrc / $srcWidth, $yDest + $destHeight * $ySrc / $srcHeight - $bmp->scaledHeight());
                        $cb->addImage($bmp);
                        $cb.restoreState();
                    }
                    catch (Exception $e) {
                        // empty on purpose
                    }
                    break;
                }
            }
            $in->skip(($tsize * 2) - ($in->getLength() - $lenMarker));
        }

    }

    public function outputText($x, $y, $flag, $x1, $y1, $x2, $y2, $text) {
        $font = $state->getCurrentFont();
        $refX = $state->transformX($x);
        $refY = $state->transformY($y);
        $angle = $state->transformAngle($font->getAngle());
        $sin = (float)sin($angle);
        $cos = (float)cos($angle);
        $fontSize = $font->getFontSize($state);
        $bf = $font->getFont();
        $align = $state->getTextAlign();
        $textWidth = $bf->getWidthPoint($text, $fontSize);
        $tx = 0;
        $ty = 0;
        $descender = $bf->getFontDescriptor(BaseFont::DESCENT, $fontSize);
        $ury = $bf->getFontDescriptor(BaseFont::BBOXURY, $fontSize);
        $cb->saveState();
        $cb->concatCTM($cos, $sin, -$sin, $cos, $refX, $refY);
        if (($align & MetaState::TA_CENTER) == MetaState::TA_CENTER)
            $tx = -$textWidth / 2;
        else if (($align & MetaState::TA_RIGHT) == MetaState::TA_RIGHT)
            $tx = -$textWidth;
        if (($align & MetaState::TA_BASELINE) == MetaState::TA_BASELINE)
            $ty = 0;
        else if (($align & MetaState::TA_BOTTOM) == MetaState::TA_BOTTOM)
            $ty = -$descender;
        else
            $ty = -$ury;
        $textColor = NULL;//Color
        if ($state->getBackgroundMode() == MetaState::OPAQUE) {
            $textColor = $state->getCurrentBackgroundColor();
            $cb->setColorFill($textColor);
            $cb->rectangle($tx, $ty + $descender, $textWidth, $ury - $descender);
            $cb->fill();
        }
        $textColor = $state->getCurrentTextColor();
        $cb->setColorFill($textColor);
        $cb->beginText();
        $cb->setFontAndSize($bf, $fontSize);
        $cb->setTextMatrix($tx, $ty);
        $cb->showText($text);
        $cb->endText();
        if ($font->isUnderline() == TRUE) {
            $cb->rectangle($tx, $ty - $fontSize / 4, $textWidth, $fontSize / 15);
            $cb->fill();
        }
        if ($font->isStrikeout() == TRUE) {
            $cb->rectangle($tx, $ty + $fontSize / 3, $textWidth, $fontSize / 15);
            $cb->fill();
        }
        $cb->restoreState();
    }


    public function isNullStrokeFill($isRectangle) {
        $pen = $state->getCurrentPen();
        $brush = $state->getCurrentBrush();
        $noPen = ($pen->getStyle() == MetaPen::PS_NULL);
        $style = $brush->getStyle();
        $isBrush = ($style == MetaBrush::BS_SOLID || ($style == MetaBrush::BS_HATCHED && $state->getBackgroundMode() == MetaState::OPAQUE));
        $result = $noPen && !$isBrush;
        if (!$noPen) {
            if ($isRectangle == TRUE)
                $state->setLineJoinRectangle($cb);
            else
                $state->setLineJoinPolygon($cb);
        }
        return $result;
    }

    public function strokeAndFill(){
        $pen = $state->getCurrentPen();
        $brush = $state->getCurrentBrush();
        $penStyle = $pen->getStyle();
        $brushStyle = $brush->getStyle();
        if ($penStyle == MetaPen::PS_NULL) {
            $cb->closePath();
            if ($state->getPolyFillMode() == MetaState::ALTERNATE) {
                $cb->eoFill();
            }
            else {
                $cb->fill();
            }
        }
        else {
            $isBrush = ($brushStyle == MetaBrush::BS_SOLID || ($brushStyle == MetaBrush::BS_HATCHED && $state->getBackgroundMode() == MetaState::OPAQUE));
            if ($isBrush == TRUE) {
                if ($state->getPolyFillMode() == MetaState::ALTERNATE)
                    $cb->closePathEoFillStroke();
                else
                    $cb->closePathFillStroke();
            }
            else {
                $cb->closePathStroke();
            }
        }
    }


    static function getArc($xCenter, $yCenter, $xDot, $yDot) {
        $s = atan2($yDot - $yCenter, $xDot - $xCenter);
        if ($s < 0)
            $s += M_PI * 2;
        return (float)($s / MPI * 180);
    }

    public static function wrapBMP(Image $image) {
        if ($image->getOriginalType() != Image::ORIGINAL_BMP)
            throw new IOException("Only BMP can be wrapped in WMF.");
        //InputStream imgIn;
        $data = NULL;//an array of bytes
        if ($image->getOriginalData() == NULL) {
            $fhandle = fopen($image->url());
            $contents = stream_get_contents($fhandle);
            $imgIn = itextphp_bytes_createfromRaw($contents);
            //ByteArrayOutputStream out = new ByteArrayOutputStream();
            $b = 0;
            //while ((b = imgIn.read()) != -1)
             //   out.write(b);
            //imgIn.close();
            fclose($fhandle);
            //data = out.toByteArray();
            $data = $imgIn;
        }
        else
            $data = $image->getOriginalData();
        $sizeBmpWords = (itextphp_bytes_getSize($data) - 14 + 1) >>> 1;
        //ByteArrayOutputStream os = new ByteArrayOutputStream();
        // write metafile header
        $os = itextphp_bytes_create(1);//arbitrary number
        MetaDo::writeWord($os, 1);
        MetaDo::writeWord($os, 9);
        MetaDo::writeWord($os, 0x0300);
        MetaDo::writeDWord($os, 9 + 4 + 5 + 5 + (13 + $sizeBmpWords) + 3); // total metafile size
        MetaDo::writeWord($os, 1);
        MetaDo::writeDWord($os, 14 + $sizeBmpWords); // max record size
        MetaDo::writeWord($os, 0);
        // write records
        MetaDo::writeDWord($os, 4);
        MetaDo::writeWord($os, MetaDo::META_SETMAPMODE);
        MetaDo::writeWord($os, 8);

        MetaDo::writeDWord($os, 5);
        MetaDo::writeWord($os, MetaDo::META_SETWINDOWORG);
        MetaDo::writeWord($os, 0);
        MetaDo::writeWord($os, 0);

        MetaDo::writeDWord($os, 5);
        MetaDo::writeWord($os, MetaDo::META_SETWINDOWEXT);
        MetaDo::writeWord($os, (integer)$image->height());
        MetaDo::writeWord($os, (integer)$image->width());

        MetaDo::writeDWord($os, 13 + $sizeBmpWords);
        MetaDo::writeWord($os, MetaDo::META_DIBSTRETCHBLT);
        MetaDo::writeDWord($os, 0x00cc0020);
        MetaDo::writeWord($os, (integer)$image->height());
        MetaDo::writeWord($os, (integer)$image->width());
        MetaDo::writeWord($os, 0);
        MetaDo::writeWord($os, 0);
        MetaDo::writeWord($os, (integer)$image->height());
        MetaDo::writeWord($os, (integer)$image->width());
        MetaDo::writeWord($os, 0);
        MetaDo::writeWord($os, 0);
        itextphp_bytes_append($os, $data, 14, itextphp_bytes_getSize($data) - 14);
        if ((itextphp_bytes_getSize($data) & 1) == 1)
            itextphp_bytes_append($os, 0);
//        writeDWord(os, 14 + sizeBmpWords);
//        writeWord(os, META_STRETCHDIB);
//        writeDWord(os, 0x00cc0020);
//        writeWord(os, 0);
//        writeWord(os, (int)image.height());
//        writeWord(os, (int)image.width());
//        writeWord(os, 0);
//        writeWord(os, 0);
//        writeWord(os, (int)image.height());
//        writeWord(os, (int)image.width());
//        writeWord(os, 0);
//        writeWord(os, 0);
//        os.write(data, 14, data.length - 14);
//        if ((data.length & 1) == 1)
//            os.write(0);

        MetaDo::writeDWord($os, 3);
        MetaDo::writeWord($os, 0);
        //os.close();
        return $os/*.toByteArray()*/;
    }

    public static function writeWord($os, $v) {
        itextphp_bytes_append($os, $v & 0xff);
        itextphp_bytes_append($os, ($v >>> 8) & 0xff);
    }

    public static function writeDWord($os, $v)  {
        MetaDo::writeWord($os, $v & 0xffff);
        MetaDo::writeWord($os, ($v >>> 16) & 0xffff);
    }

}



?>