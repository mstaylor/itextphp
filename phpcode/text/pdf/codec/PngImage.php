<?PHP
/*
 * $Id: PngImage.php,v 1.2 2005/11/10 18:14:04 mstaylor Exp $
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
 *
 *
 * The original JAI codecs have the following license
 *
 * Copyright (c) 2001 Sun Microsystems, Inc. All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * -Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * -Redistribution in binary form must reproduct the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * Neither the name of Sun Microsystems, Inc. or the names of contributors may
 * be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * This software is provided "AS IS," without a warranty of any kind. ALL
 * EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING ANY
 * IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE OR
 * NON-INFRINGEMENT, ARE HEREBY EXCLUDED. SUN AND ITS LICENSORS SHALL NOT BE
 * LIABLE FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF USING, MODIFYING
 * OR DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES. IN NO EVENT WILL SUN OR ITS
 * LICENSORS BE LIABLE FOR ANY LOST REVENUE, PROFIT OR DATA, OR FOR DIRECT,
 * INDIRECT, SPECIAL, CONSEQUENTIAL, INCIDENTAL OR PUNITIVE DAMAGES, HOWEVER
 * CAUSED AND REGARDLESS OF THE THEORY OF LIABILITY, ARISING OUT OF THE USE OF
 * OR INABILITY TO USE SOFTWARE, EVEN IF SUN HAS BEEN ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 *
 * You acknowledge that Software is not designed,licensed or intended for use in
 * the design, construction, operation or maintenance of any nuclear facility.
 */

require_once("../../Image.php");
require_once("../../ImgRaw.php");
require_once("../ByteBuffer.php");
require_once("../PdfArray.php");
require_once("../PdfDictionary.php");
require_once("../PdfLiteral.php");
require_once("../PdfName.php");
require_once("../PdfNumber.php");
require_once("../PdfObject.php");
require_once("../PdfReader.php");
require_once("../PdfString.php");
require_once("../../../exceptions/IOException.php");


/** Reads a PNG image. All types of PNG can be read.
* <p>
* It is based in part in the JAI codec.
*
* @author  Paulo Soares (psoares@consiste.pt)
* @author Mills Staylor (bud.staylor@gmail.com) -- Adapted for PHP based on Paulo Soares' code
*/


class PngImage
{

    /** Some PNG specific values. */
    public static $PNGID = array(137, 80, 78, 71, 13, 10, 26, 10);

    /** A PNG marker. */
    const IHDR = "IHDR";

    /** A PNG marker. */
    const PLTE = "PLTE";

    /** A PNG marker. */
    const IDAT = "IDAT";

    /** A PNG marker. */
    const IEND = "IEND";

    /** A PNG marker. */
    const tRNS = "tRNS";

    /** A PNG marker. */
    const pHYs = "pHYs";

    /** A PNG marker. */
    const gAMA = "gAMA";

    /** A PNG marker. */
    const cHRM = "cHRM";

    /** A PNG marker. */
    const sRGB = "sRGB";

    /** A PNG marker. */
    const iCCP = "iCCP";

    private static $TRANSFERSIZE = 4096;
    private static $PNG_FILTER_NONE = 0;
    private static $PNG_FILTER_SUB = 1;
    private static $PNG_FILTER_UP = 2;
    private static $PNG_FILTER_AVERAGE = 3;
    private static $PNG_FILTER_PAETH = 4;
    private static $intents = NULL;//an array
    private $rawdata = NULL;//abyte array
    //InputStream is;
    protected $is = NULL;//a file handle
    protected $dataStream = NULL;//a byte array
    protected $width = 0;
    protected $height = 0;
    protected $bitDepth = 0;
    protected $colorType = 0;
    protected $compressionMethod = 0;
    protected $filterMethod = 0;
    protected $interlaceMethod = 0;
    protected $additional = NULL;//PdfDictionary
    protected $image = NULL;//byte array
    protected $smask = NULL;//byte array
    protected $trans = NULL;//byte array
    protected $idat = NULL;//NewByteArrayOutputStream
    protected $dpiX = 0;
    protected $dpiY = 0;
    protected $XYRatio = 0.0;
    protected $genBWMask = FALSE;
    protected $palShades = FALSE;
    protected $transRedGray = -1;
    protected $transGreen = -1;
    protected $transBlue = -1;
    protected $inputBands = 0;
    protected $bytesPerPixel = 0; // number of bytes per input pixel
    protected $colorTable= NULL;//byte array
    protected $gamma = 1.0;
    protected $hasCHRM = FALSE;
    protected $xW = 0.0;
    protected $yW = 0.0;
    protected $xR = 0.0;
    protected $yR = 0.0;
    protected $xG = 0.0;
    protected $yG = 0.0;
    protected $xB = 0.0;
    protected $yB = 0.0;;
    protected $intent = NULL;//PdfName
    public static $initialized = FALSE;
    //ICC_Profile icc_profile;

    private function onConstruct()
    {
        $additional = new PdfDictionary();
        $idat = new NewByteArrayOutputStream();
    }

    public static function initializeStatics()
    {
        if(PngImage::$initialized == FALSE)
        {
            PngImage::$intents = array(PdfName::$PERCEPTUAL, PdfName::$RELATIVECALORIMETRIC, PdfName::$SATURATION, PdfName::$ABSOLUTECALORIMETRIC);
            PngImage::$initialized = TRUE;
        }
    }

    public function __construct()
    {
        onConstruct();
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_resource($arg1) == TRUE && strcmp(get_resource_type($arg1), "stream") == 0)
                    construct1arg($arg1);
                break;
            }
        }
    }

    /** Creates a new instance of PngImage */
    private function construct1arg($is) {
        $this->is = $is;
    }

    public static function getImage()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_resource($arg1) == TRUE && strcmp(get_resource_type($arg1), "stream") == 0)
                    return PngImage::getImage1argHandle($arg1);
                else if (is_string($arg1) == TRUE)
                    return PngImage::getImage1argString($arg1);
                else if (is_resource($arg1) == TRUE && strcmp(get_resource_type($arg1), "anotherByte") == 0)
                    return PngImage::getImage1argByte($arg1);
                break;
            }
        }
    }

    /** Reads a PNG from an url.
    * @param url the url
    * @throws IOException on error
    * @return the image
    */
    private static function getImage1argString($url)
    {
        $is = NULL;
        try {
            $is = fopen($url, "r");
            $img = PngImage::getImage($is);
            $img->setUrl($url);
            return $img;
        }
        catch (Exception $e) {
            if ($is != NULL && $is != FALSE) {
                fclose($is);
            }
            return;
        }

        if ($is != NULL && $is != FALSE) {
            fclose($is);
        }
    }


    /** Reads a PNG from a stream.
    * @param is the stream
    * @throws IOException on error
    * @return the image
    */
    private static function getImage1argHandle($is) {
        $png = new PngImage($is);
        return $png->getAnImage();
    }

    /** Reads a PNG from a byte array.
    * @param data the byte array
    * @throws IOException on error
    * @return the image
    */
    private static function getImage1argByte($data) {
        //$is = NULL;
        try {
            //$is = new ByteArrayInputStream(data);
            $img = new PngImage();
            $img->rawdata = $data;
            $img->setOriginalData($data);
            return $img;
        }
        catch (Exception $e) {
            //if ($is != NULL && $is != FALSE) {
            //    is.close();
            //}
            //return;
        }
    }


    function checkMarker($s) {
        //check to see if param is a php string and if not convert....
        if (is_resource($s) && strcmp(get_resource_type($s), "aString") == 0)
            $s = itextphp_string_toPHPString($s);
        if (strlen($s) != 4)
            return FALSE;
        for ($k = 0; $k < 4; ++$k) {
            $c = $s[$k];
            if ((ord($c) < ord('a') || ord($c) > ord('z')) && (ord($c) < ord('A') || ord($c) > ord('Z')))
                return FALSE;
        }
        return TRUIE;
    }


    function readPng() {
        //new to convert file to byte

        if ($is != NULL)
        {
            $contents = stream_get_contents($is);
            $rawdata = itextphp_bytes_createfromRaw($contents);
        }
        for ($i = 0; i < count(PngImage::$PNGID); $i++) {
            if (PngImage::$PNGID[$i] != ord(itextphp_bytes_read($rawdata))) {
                throw new IOException("File is not a valid PNG.");
            }
        }
        $buffer = itextphp_bytes_create(PngImage::$TRANSFERSIZE);
        while (TRUE) {
            $len = PngImage::getInt($rawdata);
            $marker = PngImage::getString($rawdata);
            if ($len < 0 || !$checkMarker($marker))
                throw new IOException("Corrupted PNG file.");
            if (strcmp(PngImage::IDAT, $marker) == 0) {
                $size = 0;
                while ($len != 0) {
                    $size = ord(itextphp_bytes_read($rawdata, $buffer, 0, min($len, PngImage::$TRANSFERSIZE)));
                    if ($size < 0)
                        return;
                    for ($k = 0; $k < $size; $k++)
                    {
                        itextphp_bytes_write($idat, $k, $buffer, $k);
                    }
                    $len -= $size;
                }
            }
            else if (strcmp(PngImage::tRNS, $marker) == 0) {
                switch ($colorType) {
                    case 0:
                        if ($len >= 2) {
                            $len -= 2;
                            $gray = PngImage::getWord($rawdata);
                            if ($bitDepth == 16)
                                $transRedGray = $gray;
                            else
                                $additional->put(PdfName::$MASK, new PdfLiteral("[" . $gray . " ". $gray . "]"));
                        }
                        break;
                    case 2:
                        if ($len >= 6) {
                            $len -= 6;
                            $red = getWord($rawdata);
                            $green = getWord($rawdata);
                            $blue = getWord($rawdata);
                            if ($bitDepth == 16) {
                                $transRedGray = $red;
                                $transGreen = $green;
                                $transBlue = $blue;
                            }
                            else
                                $additional->put(PdfName::$MASK, new PdfLiteral("[" . $red . " " . $red . " " . $green . " " . $green . " " . $blue . " " . $blue . "]"));
                        }
                        break;
                    case 3:
                        if ($len > 0) {
                            $trans = itextphp_bytes_create($len);
                            for ($k = 0; $k < $len; ++$k)
                                itextphp_bytes_write($trans, $k, ord(itextphp_bytes_createfromInt(itextphp_bytes_read($rawdata))), 0);
                            $len = 0;
                        }
                        break;
                }
                Image::skip($rawdata, $len);
            }
            else if (strcmp(PngImage::IHDR, $marker) == 0) {
                $width = getInt($rawdata);
                $height = getInt($rawdata);

                $bitDepth = ord(itextphp_bytes_read($rawdata));
                $colorType = ord(itextphp_bytes_read($rawdata));
                $compressionMethod = ord(itextphp_bytes_read($rawdata));
                $filterMethod = ord(itextphp_bytes_read($rawdata));
                $interlaceMethod = ord(itextphp_bytes_read($rawdata));
            }
            else if (strcmp(PngImage::PLTE, $marker) == 0) {
                if ($colorType == 3) {
                    $colorspace = new PdfArray();
                    $colorspace->add(PdfName::$INDEXED);
                    $colorspace->add(getColorspace());
                    $colorspace->add(new PdfNumber($len / 3 - 1));
                    $colortable = new ByteBuffer();
                    while (($len--) > 0) {
                        $colortable->append_i(ord(itextphp_bytes_read($rawdata)));
                    }
                    $colorspace->add(new PdfString($colorTable = $colortable->toByteArray()));
                    $additional->put(PdfName::$COLORSPACE, $colorspace);
                }
                else {
                    Image::skip($rawdata, $len);
                }
            }
            else if (strcmp(PngImage::pHYs, $marker) == 0) {
                $dx = getInt($rawdata);
                $dy = getInt($rawdata);
                $unit = ord(itextphp_bytes_read($rawdata));
                if ($unit == 1) {
                    $dpiX = (integer)((float)$dx * 0.0254);
                    $dpiY = (integer)((float)$dy * 0.0254);
                }
                else {
                    if ($dy != 0)
                        $XYRatio = (float)$dx / (float)$dy;
                }
            }
            else if (strcmp(PngImage::cHRM, $marker) == 0) {
                $xW = (float)getInt($rawdata) / 100000.0;
                $yW = (float)getInt($rawdata) / 100000.0;
                $xR = (float)getInt($rawdata) / 100000.0;
                $yR = (float)getInt($rawdata) / 100000.0;
                $xG = (float)getInt($rawdata) / 100000.0;
                $yG = (float)getInt($rawdata) / 100000.0;
                $xB = (float)getInt($rawdata) / 100000.0;
                $yB = (float)getInt($rawdata) / 100000.0;
                $hasCHRM = !(abs($xW)<0.0001||abs($yW)<0.0001||abs($xR)<0.0001||abs($yR)<0.0001||abs($xG)<0.0001||abs($yG)<0.0001||abs($xB)<0.0001||abs($yB)<0.0001);
            }
            else if (strcmp(PngImage::sRGB, $marker) == 0) {
                $ri = ord(itextphp_bytes_read($rawdata));
                $intent = $intents[$ri];
                $gamma = 2.2;
                $xW = 0.3127;
                $yW = 0.329;
                $xR = 0.64;
                $yR = 0.33;
                $xG = 0.3;
                $yG = 0.6;
                $xB = 0.15;
                $yB = 0.06;
                $hasCHRM = TRUE;
            }
            else if (strcmp(PngImage::gAMA, $marker) == 0) {
                $gm = getInt($rawdata);
                if ($gm != 0) {
                    $gamma = 100000.0 / (float)$gm;
                    if ($hasCHRM == FALSE) {
                        $xW = 0.3127;
                        $yW = 0.329;
                        $xR = 0.64;
                        $yR = 0.33;
                        $xG = 0.3;
                        $yG = 0.6;
                        $xB = 0.15;
                        $yB = 0.06;
                        $hasCHRM = TRUE;
                    }
                }
            }
            else if (strcmp(PngImage::iCCP, $marker) == 0) {
                do {
                    --$len;
                } while (ord(itextphp_bytes_read($rawdata)) != 0);
                itextphp_bytes_read($rawdata);
                --$len;
                $icccom = itextphp_bytes_create($len);
                $p = 0;
                while ($len > 0) {
                    $r = ord(itextphp_bytes_read($rawdata, $icccom, $p, $len));
                    if ($r < 0)
                        throw new IOException("Premature end of file.");
                    $p += $r;
                    $len -= $r;
                }
                $iccp = PdfReader::FlateDecode($icccom, TRUE);
                $icccom = NULL;
                //TO DO ic profile disable...possible port and reenable later....
                /*try {
                    icc_profile = ICC_Profile.getInstance(iccp);
                }
                catch (Exception e) {
                    icc_profile = null;
                }*/
            }
            else if (strcmp(PngImage::IEND, $marker) == 0) {
                break;
            }
            else {
                Image::skip($rawdata, $len);
            }
            Image::skip($rawdata, 4);
        }
    }

    function getColorspace() {
        //TO DO ic profile disable...possible port and reenable later....
        /*if (icc_profile != null) {
            if ((colorType & 2) == 0)
                return PdfName.DEVICEGRAY;
            else
                return PdfName.DEVICERGB;
        }*/
        if ($gamma == 1.0 && $hasCHRM == FALSE) {
            if (($colorType & 2) == 0)
                return PdfName::$DEVICEGRAY;
            else
                return PdfName::$DEVICERGB;
        }
        else {
            $array = new PdfArray();
            $dic = new PdfDictionary();
            if (($colorType & 2) == 0) {
                if ($gamma == 1.0)
                    return PdfName::$DEVICEGRAY;
                $array->add(PdfName::$CALGRAY);
                $dic->put(PdfName::$GAMMA, new PdfNumber($gamma));
                $dic->put(PdfName::$WHITEPOINT, new PdfLiteral("[1 1 1]"));
                $array->add($dic);
            }
            else {
                $wp = new PdfLiteral("[1 1 1]");
                $array->add(PdfName::$CALRGB);
                if ($gamma != 1.0) {
                    $gm = new PdfArray();
                    $n = new PdfNumber($gamma);
                    $gm->add($n);
                    $gm->add($n);
                    $gm->add($n);
                    $dic->put(PdfName::$GAMMA, $gm);
                }
                if ($hasCHRM == TRUE) {
                    $z = $yW*(($xG-$xB)*$yR-($xR-$xB)*$yG+($xR-$xG)*$yB);
                    $YA = $yR*(($xG-$xB)*$yW-($xW-$xB)*$yG+($xW-$xG)*$yB)/$z;
                    $XA = $YA*$xR/$yR;
                    $ZA = $YA*((1-$xR)/$yR-1);
                    $YB = -$yG*(($xR-$xB)*$yW-($xW-$xB)*$yR+($xW-$xR)*$yB)/$z;
                    $XB = $YB*$xG/$yG;
                    $ZB = $YB*((1-$xG)/$yG-1);
                    $YC = $yB*(($xR-$xG)*$yW-($xW-$xG)*$yW+($xW-$xR)*$yG)/$z;
                    $XC = $YC*$xB/$yB;
                    $ZC = $YC*((1-$xB)/$yB-1);
                    $XW = $XA+$XB+$XC;
                    $YW = 1;//YA+YB+YC;
                    $ZW = $ZA+$ZB+$ZC;
                    $wpa = new PdfArray();
                    $wpa->add(new PdfNumber($XW));
                    $wpa->add(new PdfNumber($YW));
                    $wpa->add(new PdfNumber($ZW));
                    $wp = $wpa;
                    $matrix = new PdfArray();
                    $matrix->add(new PdfNumber($XA));
                    $matrix->add(new PdfNumber($YA));
                    $matrix->add(new PdfNumber($ZA));
                    $matrix->add(new PdfNumber($XB));
                    $matrix->add(new PdfNumber($YB));
                    $matrix->add(new PdfNumber($ZB));
                    $matrix->add(new PdfNumber($XC));
                    $matrix->add(new PdfNumber($YC));
                    $matrix->add(new PdfNumber($ZC));
                    $dic->put(PdfName::$MATRIX, $matrix);
                }
                $dic->put(PdfName::$WHITEPOINT, $wp);
                $array->add($dic);
            }
            return $array;
        }
    }


    function getAnImage()
    {
        readPng();
        try {
            $pal0 = 0;
            $palIdx = 0;
            $palShades = FALSE;
            if ($trans != NULL) {
                for ($k = 0; $k < itextphp_bytes_getSize($trans); ++$k) {
                    $n = itextphp_bytes_getIntValue($trans, $k) & 0xff;
                    if ($n == 0) {
                        ++$pal0;
                        $palIdx = $k;
                    }
                    if ($n != 0 && $n != 255) {
                        $palShades = TRUE;
                        break;
                    }
                }
            }
            if (($colorType & 4) != 0)
                $palShades = TRUE;
            $genBWMask = (!$palShades && ($pal0 > 1 || $transRedGray >= 0));
            if (!$palShades && !$genBWMask && $pal0 == 1) {
                $additional->put(PdfName::$MASK, new PdfLiteral("[" . $palIdx  . " " . $palIdx . "]"));
            }
            $needDecode = ($interlaceMethod == 1) || ($bitDepth == 16) || (($colorType & 4) != 0) || $palShades || $genBWMask;
            switch ($colorType) {
                case 0:
                    $inputBands = 1;
                    break;
                case 2:
                    $inputBands = 3;
                    break;
                case 3:
                    $inputBands = 1;
                    break;
                case 4:
                    $inputBands = 2;
                    break;
                case 6:
                    $inputBands = 4;
                    break;
            }
            if ($needDecode == TRUE)
                decodeIdat();
            $components = $inputBands;
            if (($colorType & 4) != 0)
                --$components;
            $bpc = $bitDepth;
            if ($bpc == 16)
                $bpc = 8;
            $img = NULL;//an Image
            if ($image != NULL)
                $img = Image::getInstance($width, $height, $components, $bpc, $image);
            else {
                $img = new ImgRaw($width, $height, $components, $bpc, $idat);
                $img->setDeflated(TRUE);
                $decodeparms = new PdfDictionary();
                $decodeparms->put(PdfName::$BITSPERCOMPONENT, new PdfNumber($bitDepth));
                $decodeparms->put(PdfName::$PREDICTOR, new PdfNumber(15));
                $decodeparms->put(PdfName::$COLUMNS, new PdfNumber($width));
                $decodeparms->put(PdfName::$COLORS, new PdfNumber(($colorType == 3 || ($colorType & 2) == 0) ? 1 : 3));
                $additional->put(PdfName::$DECODEPARMS, $decodeparms);
            }
            if ($additional->get(PdfName::$COLORSPACE) == NULL)
                $additional->put(PdfName::$COLORSPACE, getColorspace());
            if ($intent != NULL)
                $additional->put(PdfName::$INTENT, $intent);
            if ($additional->size() > 0)
                $img->setAdditional($additional);
            //TO DO ic profile disable...possible port and reenable later....
            /*if (icc_profile != null)
                img.tagICC(icc_profile);
            */if ($palShades == TRUE) {
                $im2 = Image::getInstance($width, $height, 1, 8, $smask);
                $im2->makeMask();
                $img->setImageMask($im2);
            }
            if ($genBWMask == TRUE) {
                $im2 = Image::getInstance($width, $height, 1, 1, $smask);
                $im2->makeMask();
                $img->setImageMask($im2);
            }
            $img->setDpi($dpiX, $dpiY);
            $img->setXYRatio($XYRatio);
            $img->setOriginalType(Image::ORIGINAL_PNG);
            return $img;
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    function decodeIdat() {
        $nbitDepth = $bitDepth;
        if ($nbitDepth == 16)
            $nbitDepth = 8;
        $size = -1;
        $bytesPerPixel = ($bitDepth == 16) ? 2 : 1;
        switch ($colorType) {
            case 0:
                $size = ($nbitDepth * $width + 7) / 8 * $height;
                break;
            case 2:
                $size = $width * 3 * $height;
                $bytesPerPixel *= 3;
                break;
            case 3:
                if ($interlaceMethod == 1)
                    $size = ($nbitDepth * $width + 7) / 8 * $height;
                $bytesPerPixel = 1;
                break;
            case 4:
                $size = $width * $height;
                $bytesPerPixel *= 2;
                break;
            case 6:
                $size = $width * 3 * $height;
                $bytesPerPixel *= 4;
                break;
        }
        if ($size >= 0)
            $image = itextphp_bytes_create($size);
        if ($palShades == TRUE)
            $smask = itextphp_bytes_create($width * $height);
        else if ($genBWMask == TRUE)
            $smask = itextphp_bytes_create(($width + 7) / 8 * $height);
        //ByteArrayInputStream bai = new ByteArrayInputStream(idat.getBuf(), 0, idat.size());
        //InputStream infStream = new InflaterInputStream(bai, new Inflater());
        //dataStream = new DataInputStream(infStream);


        //Inflate the idat buffer using the zlib decompression algorithm...
        $tmpstring = itextphp_getAnsiString($idat, itextphp_bytes_getSize($idat));
        $tmpstring = gzinflate($tmpstring);
        dataStream = itextphp_bytes_createfromRaw($tmpstring);





        if ($interlaceMethod != 1) {
            decodePass(0, 0, 1, 1, $width, $height);
        }
        else {
            decodePass(0, 0, 8, 8, ($width + 7)/8, ($height + 7)/8);
            decodePass(4, 0, 8, 8, ($width + 3)/8, ($height + 7)/8);
            decodePass(0, 4, 4, 8, ($width + 3)/4, ($height + 3)/8);
            decodePass(2, 0, 4, 4, ($width + 1)/4, ($height + 3)/4);
            decodePass(0, 2, 2, 4, ($width + 1)/2, ($height + 1)/4);
            decodePass(1, 0, 2, 2, $width/2, ($height + 1)/2);
            decodePass(0, 1, 1, 2, $width, $height/2);
        }

    }


    function decodePass( $xOffset, $yOffset, $xStep, $yStep, $passWidth, $passHeight) {
        if (($passWidth == 0) || ($passHeight == 0)) {
            return;
        }

        $bytesPerRow = ($inputBands*$passWidth*$bitDepth + 7)/8;
        $curr = itextphp_bytes_create($bytesPerRow);
        $prior = itextphp_bytes_create($bytesPerRow);

        // Decode the (sub)image row-by-row
        $srcY = 0;
        $dstY = 0;
        for ($srcY = 0, $dstY = $yOffset; $srcY < $passHeight; $srcY++, $dstY += $yStep) {
            // Read the filter type byte and a row of data
            $filter = 0;
            try {
                $filter = ord(itextphp_bytes_read($dataStream));
                itextphp_bytes_readFully($dataStream, $curr, 0, $bytesPerRow);
            } catch (Exception $e) {
                // empty on purpose
            }

            switch ($filter) {
                case PngImage::$PNG_FILTER_NONE:
                    break;
                case PngImage::$PNG_FILTER_SUB:
                    PngImage::decodeSubFilter($curr, $bytesPerRow, $bytesPerPixel);
                    break;
                case PngImage::$PNG_FILTER_UP:
                    PngImage::decodeUpFilter($curr, $prior, $bytesPerRow);
                    break;
                case PngImage::$PNG_FILTER_AVERAGE:
                    PngImage::decodeAverageFilter($curr, $prior, $bytesPerRow, $bytesPerPixel);
                    break;
                case PngImage::$PNG_FILTER_PAETH:
                    decodePaethFilter($curr, $prior, $bytesPerRow, $bytesPerPixel);
                    break;
                default:
                    // Error -- uknown filter type
                    throw new Exception("PNG filter unknown.");
            }

            processPixels($curr, $xOffset, $xStep, $dstY, $passWidth);

            // Swap curr and prior
            $tmp = $prior;
            $prior = $curr;
            $curr = $tmp;
        }
    }


    function processPixels($curr, $xOffset, $step, $y, $width) {
        $srcX = 0;
        $dstX = 0;

        $out = getaPixel($curr);
        $sizes = 0;
        switch ($colorType) {
            case 0:
            case 3:
            case 4:
                $sizes = 1;
                break;
            case 2:
            case 6:
                $sizes = 3;
                break;
        }
        if ($image != NULL) {
            $dstX = $xOffset;
            $yStride = ($sizes*$this->width*($bitDepth == 16 ? 8 : $bitDepth)+ 7)/8;
            for ($srcX = 0; $srcX < $width; $srcX++) {
                PngImage::setPixel($image, $out, $inputBands * $srcX, $sizes, $dstX, $y, $bitDepth, $yStride);
                $dstX += $step;
            }
        }
        if ($palShades == TRUE) {
            if (($colorType & 4) != 0) {
                if ($bitDepth == 16) {
                    for ($k = 0; $k < $width; ++$k)
                        $out[$k * $inputBands + $sizes] >>>= 8;
                }
                $yStride = $this->width;
                $dstX = $xOffset;
                for ($srcX = 0; $srcX < $width; $srcX++) {
                    PngImage::setPixel($smask, $out, $inputBands * $srcX + $sizes, 1, $dstX, $y, 8, $yStride);
                    $dstX += $step;
                }
            }
            else { //colorType 3
                $yStride = $this->width;
                $v = array(1);
                $dstX = $xOffset;
                for ($srcX = 0; $srcX < $width; $srcX++) {
                    $idx = $out[$srcX];
                    $r = 255;
                    if ($idx < itextphp_bytes_getSize($trans))
                        $v[0] = itextphp_bytes_getIntValue($trans, $idx);
                    PngImage::setPixel($smask, $v, 0, 1, $dstX, $y, 8, $yStride);
                    $dstX += $step;
                }
            }
        }
        else if ($genBWMask == TRUE) {
            switch ($colorType) {
                case 3: {
                    $yStride = ($this->width + 7) / 8;
                    $v = array(1);
                    $dstX = $xOffset;
                    for ($srcX = 0; $srcX < $width; $srcX++) {
                        $idx = $out[$srcX];
                        $r = 0;
                        if ($idx < itextphp_bytes_getSize($trans))
                            $v[0] = (itextphp_bytes_getIntValue($trans, $idx) == 0 ? 1 : 0);
                        PngImage::setPixel($smask, $v, 0, 1, $dstX, $y, 1, $yStride);
                        $dstX += $step;
                    }
                    break;
                }
                case 0: {
                    $yStride = ($this->width + 7) / 8;
                    $v = array(1);
                    $dstX = $xOffset;
                    for ($srcX = 0; $srcX < $width; $srcX++) {
                        $g = $out[$srcX];
                        $v[0] = ($g == $transRedGray ? 1 : 0);
                        PngImage::setPixel($smask, $v, 0, 1, $dstX, $y, 1, $yStride);
                        $dstX += $step;
                    }
                    break;
                }
                case 2: {
                    $yStride = ($this->width + 7) / 8;
                    $v = array(1);
                    $dstX = $xOffset;
                    for ($srcX = 0; $srcX < $width; $srcX++) {
                        $markRed = $inputBands * $srcX;
                        $v[0] = ($out[$markRed] == $transRedGray && $out[$markRed + 1] == $transGreen && $out[$markRed + 2] == $transBlue ? 1 : 0);
                        PngImage::setPixel($smask, $v, 0, 1, $dstX, $y, 1, $yStride);
                        $dstX += $step;
                    }
                    break;
                }
            }
        }
    }


    static function getPixel($image, $x, $y, $bitDepth, $bytesPerRow) {
        if ($bitDepth == 8) {
            $pos = $bytesPerRow * $y + $x;
            return itextphp_bytes_getIntValue($image, $pos) & 0xff;
        }
        else {
            $pos = $bytesPerRow * $y + $x / (8 / $bitDepth);
            $v = itextphp_bytes_getIntValue($image, $pos) >> (8 - $bitDepth * ($x % (8 / $bitDepth))- $bitDepth);
            return $v & ((1 << $bitDepth) - 1);
        }
    }

    static function setPixel($image, $data, $offset, $size, $x, $y, $bitDepth, $bytesPerRow) {
        if ($bitDepth == 8) {
            $pos = $bytesPerRow * $y + $size * $x;
            for ($k = 0; $k < $size; ++$k)
                itextphp_bytes_write($image, $pos + $k, itextphp_bytes_createfromInt($data[$k + $offset]), 0);
        }
        else if ($bitDepth == 16) {
            $pos = $bytesPerRow * $y + $size * $x;
            for ($k = 0; $k < $size; ++$k)
                itextphp_bytes_write($image, $pos + $k, itextphp_bytes_createfromInt(($data[$k + $offset] >>> 8)), 0);
        }
        else {
            $pos = $bytesPerRow * $y + $x / (8 / $bitDepth);
            $v = $data[$offset] << (8 - $bitDepth * ($x % (8 / $bitDepth))- $bitDepth);
            itextphp_bytes_bitwiseAssign($image, $pos, $v);
        }
    }


     function getaPixel($curr) {
        switch ($bitDepth) {
            case 8: {
                $out = array();
                for ($k = 0; $k < itextphp_bytes_getSize($curr); ++$k)
                    $out[$k] = itextphp_bytes_getIntValue($curr, $k) & 0xff;
                return $out;
            }
            case 16: {
                $out = array();
                for ($k = 0; $k < itextphp_bytes_getSize($curr)/2; ++$k)
                    $out[$k] = ((itextphp_bytes_getIntValue($curr. $k * 2) & 0xff) << 8) + (itextphp_bytes_getIntValue($curr, $k * 2 + 1) & 0xff);
                return $out;
            }
            default: {
                $out = array();
                $idx = 0;
                $passes = 8 / $bitDepth;
                $mask = (1 << $bitDepth) - 1;
                for ($k = 0; $k < itextphp_bytes_getSize($curr); ++$k) {
                    for ($j = $passes - 1; $j >= 0; --j) {
                        $out[$idx++] = (itextphp_bytes_getIntValue($curr, $k) >>> ($bitDepth * $j)) & $mask; 
                    }
                }
                return $out;
            }
        }
    }


    private static function decodeSubFilter($curr, $count, $bpp) {
        for ($i = $bpp; $i < $count; $i++) {
            $val = 0;

            $val = itextphp_bytes_getIntValue($curr, $i) & 0xff;
            $val += itextphp_bytes_getIntValue($curr, $i - $bpp) & 0xff;

            itextphp_bytes_write($curr, $i, itextphp_bytes_createfromInt($val), 0);
        }
    }

    private static function decodeUpFilter($curr, $prev, $count) {
        for ($i = 0; $i < $count; $i++) {
            $raw = itextphp_bytes_getIntValue($curr, $i) & 0xff;
            $prior = itextphp_bytes_getIntValue($prev, $i) & 0xff;

            itextphp_bytes_write($curr, $i, itextphp_bytes_createfromInt($raw + $prior), 0);
        }
    }


    private static function decodeAverageFilter($curr, $prev, $count, $bpp) {
        $raw = 0;
        $priorPixel = 0;
        $priorRow = 0;

        for ($i = 0; $i < $bpp;$i++) {
            $raw = itextphp_bytes_getIntValue($curr, $i) & 0xff;
            $priorRow = itextphp_bytes_getIntValue($prev, $i) & 0xff;

            itextphp_bytes_write($curr, $i, itextphp_bytes_createfromInt($raw + $priorRow/2), 0);
        }

        for ($i = $bpp; $i < $count; $i++) {
            $raw = itextphp_bytes_getIntValue($curr, $i) & 0xff;
            $priorPixel = itextphp_bytes_getIntValue($curr, $i - $bpp) & 0xff;
            $priorRow = itextphp_bytes_getIntValue($prev, $i) & 0xff;

            itextphp_bytes_write($curr, $i, (raw + itextphp_bytes_createfromInt($priorPixel + $priorRow)/2), 0);
        }
    }

    private static function paethPredictor($a, $b, $c) {
        $p = $a + $b - $c;
        $pa = abs($p - $a);
        $pb = abs($p - $b);
        $pc = abs($p - $c);

        if (($pa <= $pb) && ($pa <= $pc)) {
            return $a;
        } else if ($pb <= $pc) {
            return $b;
        } else {
            return $c;
        }
    }

    private static function decodePaethFilter($curr, $prev, $count, $bpp) {
        $raw = 0;
        $priorPixel = 0;
        $priorRow = 0;
        $priorRowPixel = 0;

        for ($i = 0; $i < $bpp; $i++) {
            $raw = itextphp_bytes_getIntValue($curr, $i) & 0xff;
            $priorRow = itextphp_bytes_getIntValue($prev, $i) & 0xff;

            itextphp_bytes_write($curr, $i, itextphp_bytes_createfromInt($raw + $priorRow), 0);
        }

        for ($i = $bpp; $i < $count; $i++) {
            $raw = itextphp_bytes_getIntValue($curr, $i) & 0xff;
            $priorPixel = itextphp_bytes_getIntValue($curr, $i - $bpp) & 0xff;
            $priorRow = itextphp_bytes_getIntValue($prev, $i) & 0xff;
            $priorRowPixel = itextphp_bytes_getIntValue($prev, $i - $bpp) & 0xff;

            itextphp_bytes_write($curr, $i, itextphp_bytes_createfromInt($raw + PngImage::paethPredictor($priorPixel, $priorRow, $priorRowPixel)), 0);
        }
    }


    /**
    * Gets an <CODE>int</CODE> from an <CODE>InputStream</CODE>.
    *
    * @param		is      an <CODE>InputStream</CODE>
    * @return		the value of an <CODE>int</CODE>
    */

    public static final function getInt($is) {
        return (ord(itextphp_bytes_read($is)) << 24) + (ord(itextphp_bytes_read($is)) << 16) + (ord(itextphp_bytes_read($is)) << 8) + ord(itextphp_bytes_read($is));
    }


    /**
    * Gets a <CODE>word</CODE> from an <CODE>InputStream</CODE>.
    *
    * @param		is      an <CODE>InputStream</CODE>
    * @return		the value of an <CODE>int</CODE>
    */

    public static final function getWord($is) {
        return (ord(itextphp_bytes_read($is)) << 8) + ord(itextphp_bytes_read($is));
    }

    /**
    * Gets a <CODE>String</CODE> from an <CODE>InputStream</CODE>.
    *
    * @param		is      an <CODE>InputStream</CODE>
    * @return		the value of an <CODE>int</CODE>
    */

    public static final function getString($is) {
        $buf = "";
        for ($i = 0; $i < 4; $i++) {
            $buf .= itextphp_bytes_read($is);
        }
        return $buf;
    }







}


PngImage::initializeStatics();


?>