<?PHP
/*
 * $Id: TiffImage.php,v 1.2 2005/11/14 20:39:31 mstaylor Exp $
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


require_once("../../Image.php");
require_once("../../Element.php");
require_once("../RandomAccessFileOrArray.php");
require_once("../PdfArray.php");
require_once("../PdfName.php");
require_once("../PdfNumber.php");
require_once("../PdfString.php");
require_once("../PdfDictionary.php");
require_once("TIFFField.php");
require_once("TIFFDirectory.php");
require_once("TIFFConstants.php");
require_once("CCITTG4Encoder.php");
require_once("TIFFFaxDecoder.php");
require_once("TIFFLZWDecoder.php");
require_once("../../../exceptions/IllegalArgumentException.php");




/** Reads TIFF images
* @author Paulo Soares (psoares@consiste.pt)
* @author adopted to PHP by Mills Staylor (bud.staylor@gmail.com)
*/


class TiffImage
{

    /** Gets the number of pages the TIFF document has.
    * @param s the file source
    * @return the number of pages
    */
    public static function getNumberOfPages(RandomAccessFileOrArray $s) {
        try {
            return TIFFDirectory::getNumDirectories($s);
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    static function getDpi(TIFFField $fd, $resolutionUnit) {
        if ($fd == NULL)
            return 0;
        $res = $fd->getAsRational(0);
        $frac = (float)$res[0] / (float)$res[1];
        $dpi = 0;
        switch ($resolutionUnit) {
            case TIFFConstants::RESUNIT_INCH:
            case TIFFConstants::RESUNIT_NONE:
                $dpi = (integer)$frac;
                break;
            case TIFFConstants::RESUNIT_CENTIMETER:
                $dpi = (integer)($frac * 2.54);
                break;
        }
        return $dpi;
    }


    public static function getTiffImage()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if ($arg1 instance of RandomAccessFileOrArray && is_integer($arg2) == TRUE)
                    return TiffImage::getTiffImage2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if ($arg1 instanceof RandomAccessFileOrArray && is_integer($arg2) == TRUE && is_bool($arg3) == TRUE)
                    return TiffImage::getTiffImage3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }


    /** Reads a page from a TIFF image. Direct mode is not used.
    * @param s the file source
    * @param page the page to get. The first page is 1
    * @return the <CODE>Image</CODE>
    */
    private static function getTiffImage2args(RandomAccessFileOrArray $s, $page) {
        return TiffImage::getTiffImage($s, $page, FALSE);
    }


    /** Reads a page from a TIFF image.
    * @param s the file source
    * @param page the page to get. The first page is 1
    * @param direct for single strip, CCITT images, generate the image
    * by direct byte copying. It's faster but may not work
    * every time
    * @return the <CODE>Image</CODE>
    */
    private static function getTiffImage3args(RandomAccessFileOrArray $s, $page, $direct) {
        if ($page < 1)
            throw new IllegalArgumentException("The page number must be >= 1.");
        try {
            $dir = new TIFFDirectory($s, $page - 1);
            if ($dir->isTagPresent(TIFFConstants::TIFFTAG_TILEWIDTH) == TRUE)
                throw new IllegalArgumentException("Tiles are not supported.");
            $compression = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_COMPRESSION);
            switch ($compression) {
                case TIFFConstants::COMPRESSION_CCITTRLEW:
                case TIFFConstants::COMPRESSION_CCITTRLE:
                case TIFFConstants::COMPRESSION_CCITTFAX3:
                case TIFFConstants::COMPRESSION_CCITTFAX4:
                    break;
                default:
                    return TiffImage::getTiffImageColor($dir, $s);
            }
            $img = NULL;//Image
            $tiffT4Options = 0;
            $tiffT6Options = 0;
            $fillOrder = 1;
            $h = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_IMAGELENGTH);
            $w = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_IMAGEWIDTH);
            $dpiX = 0;
            $dpiY = 0;
            $XYRatio = 0.0;
            $resolutionUnit = TIFFConstants::RESUNIT_INCH;
            if ($dir->isTagPresent(TIFFConstants::TIFFTAG_RESOLUTIONUNIT) == TRUE)
                $resolutionUnit = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_RESOLUTIONUNIT);
            $dpiX = TiffImage::getDpi($dir->getField(TIFFConstants::TIFFTAG_XRESOLUTION), $resolutionUnit);
            $dpiY = TiffImage::getDpi($dir->getField(TIFFConstants::TIFFTAG_YRESOLUTION), $resolutionUnit);
            if ($resolutionUnit == TIFFConstants::RESUNIT_NONE) {
                if ($dpiY != 0)
                    $XYRatio = (float)$dpiX / (float)$dpiY;
                $dpiX = 0;
                $dpiY = 0;
            }
            $tstrip = 0xFFFFFFFFL;
            if ($dir->isTagPresent(TIFFConstants::TIFFTAG_ROWSPERSTRIP) == TRUE)
                $tstrip = $dir->getFieldAsLong(TIFFConstants::TIFFTAG_ROWSPERSTRIP);
            $rowsStrip = (integer)min((integer)$h, $tstrip);
            $field = $dir->getField(TIFFConstants::TIFFTAG_STRIPOFFSETS);
            $offset = TiffImage::getArrayLongShort($dir, TIFFConstants::TIFFTAG_STRIPOFFSETS);
            $size = TiffImage::getArrayLongShort($dir, TIFFConstants::TIFFTAG_STRIPBYTECOUNTS);
            $reverse = FALSE;
            $fillOrderField =  $dir->getField(TIFFConstants::TIFFTAG_FILLORDER);
            if ($fillOrderField != NULL)
                $fillOrder = $fillOrderField->getAsInt(0);
            $reverse = ($fillOrder == TIFFConstants::FILLORDER_LSB2MSB);
            $params = 0;
            if ($dir->isTagPresent(TIFFConstants::TIFFTAG_PHOTOMETRIC) == TRUE) {
                $photo = $dir->getFieldAsLong(TIFFConstants::TIFFTAG_PHOTOMETRIC);
                if ($photo == TIFFConstants::PHOTOMETRIC_MINISBLACK)
                    $params |= Element::CCITT_BLACKIS1;
            }
            $imagecomp = 0;
            switch ($compression) {
                case TIFFConstants::COMPRESSION_CCITTRLEW:
                case TIFFConstants::COMPRESSION_CCITTRLE:
                    $imagecomp = Element::CCITTG3_1D;
                    $params |= Element::CCITT_ENCODEDBYTEALIGN | Element::CCITT_ENDOFBLOCK;
                    break;
                case TIFFConstants::COMPRESSION_CCITTFAX3:
                    $imagecomp = Element::CCITTG3_1D;
                    $params |= Element::CCITT_ENDOFLINE | Element::CCITT_ENDOFBLOCK;
                    $t4OptionsField = $dir->getField(TIFFConstants::TIFFTAG_GROUP3OPTIONS);
                    if ($t4OptionsField != NULL) {
                        $tiffT4Options = $t4OptionsField->getAsLong(0);
                    if (($tiffT4Options & TIFFConstants::GROUP3OPT_2DENCODING) != 0)
                        $compression = Element::CCITTG3_2D;
                    if (($tiffT4Options & TIFFConstants::GROUP3OPT_FILLBITS) != 0)
                        $params |= Element::CCITT_ENCODEDBYTEALIGN;
                    }
                    break;
                case TIFFConstants::COMPRESSION_CCITTFAX4:
                    $imagecomp = Element::CCITTG4;
                    $t6OptionsField = $dir->getField(TIFFConstants::TIFFTAG_GROUP4OPTIONS);
                    if ($t6OptionsField != NULL)
                        $tiffT6Options = $t6OptionsField->getAsLong(0);
                    break;
            }
            if ($direct && $rowsStrip == $h) { //single strip, direct
                $im = itextphp_bytes_create((integer)$size[0]);
                $s->seek($offset[0]);
                $s->readFully($im);
                $img = Image::getInstance((integer) $w, (integer) $h, $reverse, $imagecomp, $params, $im);
                $img->setInverted(TRUE);
            }
            else {
                $rowsLeft = h;
                $g4 = new CCITTG4Encoder((integer)$w);
                for ($k = 0; $k < count($offset); ++$k) {
                    $im = itextphp_bytes_create((integer)$size[$k]);
                    $s->seek($offset[$k]);
                    $s->readFully($im);
                    $height = min($rowsStrip, $rowsLeft);
                    $decoder = new TIFFFaxDecoder($fillOrder, (integer)$w, $height);
                    $outBuf = itextphp_bytes_create(($w + 7) / 8 * $height);
                    switch ($compression) {
                        case TIFFConstants::COMPRESSION_CCITTRLEW:
                        case TIFFConstants::COMPRESSION_CCITTRLE:
                            $decoder->decode1D($outBuf, $im, 0, $height);
                            $g4->encodeT6Lines($outBuf, 0, $height);
                            break;
                        case TIFFConstants::COMPRESSION_CCITTFAX3:
                            try {
                                $decoder->decode2D($outBuf, $im, 0, $height, $tiffT4Options);
                            }
                            catch (Exception $e) {
                                // let's flip the fill bits and try again...
                                $tiffT4Options ^= TIFFConstants::GROUP3OPT_FILLBITS;
                                try {
                                    $decoder->decode2D($outBuf, $im, 0, $height, $tiffT4Options);
                                }
                                catch (Exception $e2) {
                                    throw $e;
                                }
                            }
                            $g4->encodeT6Lines($outBuf, 0, $height);
                            break;
                        case TIFFConstants::COMPRESSION_CCITTFAX4:
                            $decoder->decodeT6($outBuf, $im, 0, $height, $tiffT6Options);
                            $g4->encodeT6Lines($outBuf, 0, $height);
                            break;
                    }
                    $rowsLeft -= $rowsStrip;
                }
                $g4pic = $g4->close();
                $img = Image::getInstance((integer) $w, (integer) $h, FALSE, Element::CCITTG4, $params & Element::CCITT_BLACKIS1, $g4pic);
            }
            $img->setDpi($dpiX, $dpiY);
            $img.setXYRatio($XYRatio);
            /*** TO DO Implement ICC Profile
            /***if ($dir->isTagPresent(TIFFConstants::TIFFTAG_ICCPROFILE) == TRUE) {
                try {
                    TIFFField fd = dir.getField(TIFFConstants.TIFFTAG_ICCPROFILE);
                    ICC_Profile icc_prof = ICC_Profile.getInstance(fd.getAsBytes());
                    if (icc_prof.getNumComponents() == 1)
                        img.tagICC(icc_prof);
                }
                catch (Exception e) {
                    //empty
                }
            }**/
            $img->setOriginalType(Image::ORIGINAL_TIFF);
            return $img;
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }


    protected static function getTiffImageColor(TIFFDirectory $dir, RandomAccessFileOrArray $s) {
        try {
            $compression = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_COMPRESSION);
            $predictor = 1;
            $lzwDecoder = NULL;//TIFFLZWDecoder
            switch ($compression) {
                case TIFFConstants::COMPRESSION_NONE:
                case TIFFConstants::COMPRESSION_LZW:
                case TIFFConstants::COMPRESSION_PACKBITS:
                case TIFFConstants::COMPRESSION_DEFLATE:
                    break;
                default:
                    throw new IllegalArgumentException("The compression " . $compression . " is not supported.");
            }
            $photometric = (integer)$dir->getFieldAsLong(TIFFConstants.TIFFTAG_PHOTOMETRIC);
            switch ($photometric) {
                case TIFFConstants::PHOTOMETRIC_MINISWHITE:
                case TIFFConstants::PHOTOMETRIC_MINISBLACK:
                case TIFFConstants::PHOTOMETRIC_RGB:
                case TIFFConstants::PHOTOMETRIC_SEPARATED:
                case TIFFConstants::PHOTOMETRIC_PALETTE:
                    break;
                default:
                    throw new IllegalArgumentException("The photometric " . $photometric . " is not supported.");
            }
            if ($dir->isTagPresent(TIFFConstants::TIFFTAG_PLANARCONFIG) == TRUE
                && $dir->getFieldAsLong(TIFFConstants::TIFFTAG_PLANARCONFIG) == TIFFConstants::PLANARCONFIG_SEPARATE)
                throw new IllegalArgumentException("Planar images are not supported.");
            if ($dir->isTagPresent(TIFFConstants::TIFFTAG_EXTRASAMPLES))
                throw new IllegalArgumentException("Extra samples are not supported.");
            $samplePerPixel = 1;
            if ($dir->isTagPresent(TIFFConstants::TIFFTAG_SAMPLESPERPIXEL) == TRUE) // 1,3,4
                $samplePerPixel = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_SAMPLESPERPIXEL);
            $bitsPerSample = 1;
            if ($dir->isTagPresent(TIFFConstants::TIFFTAG_BITSPERSAMPLE) == TRUE)
                $bitsPerSample = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_BITSPERSAMPLE);
            switch ($bitsPerSample) {
                case 1:
                case 2:
                case 4:
                case 8:
                    break;
                default:
                    throw new IllegalArgumentException("Bits per sample " . $bitsPerSample . " is not supported.");
            }
            $img = NULL;//Image

            $h = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_IMAGELENGTH);
            $w = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_IMAGEWIDTH);
            $dpiX = 0;
            $dpiY = 0;
            $resolutionUnit = TIFFConstants::RESUNIT_INCH;
            if (dir.isTagPresent(TIFFConstants.TIFFTAG_RESOLUTIONUNIT))
                resolutionUnit = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_RESOLUTIONUNIT);
            $dpiX = TiffImage::getDpi($dir->getField(TIFFConstants::TIFFTAG_XRESOLUTION), $resolutionUnit);
            $dpiY = TiffImage::getDpi($dir->getField(TIFFConstants::TIFFTAG_YRESOLUTION), $resolutionUnit);
            $rowsStrip = (integer)$dir->getFieldAsLong(TIFFConstants::TIFFTAG_ROWSPERSTRIP);
            $offset = TiffImage::getArrayLongShort($dir, TIFFConstants::TIFFTAG_STRIPOFFSETS);
            $size = TiffImage::getArrayLongShort($dir, TIFFConstants::TIFFTAG_STRIPBYTECOUNTS);
            if ($compression == TIFFConstants::COMPRESSION_LZW) {
                $predictorField = $dir->getField(TIFFConstants::TIFFTAG_PREDICTOR);
                if ($predictorField != NULL) {
                    $predictor = $predictorField->getAsInt(0);
                    if ($predictor != 1 && $predictor != 2) {
                        throw new Exception("Illegal value for Predictor in TIFF file."); 
                    }
                    if ($predictor == 2 && $bitsPerSample != 8) {
                        throw new Exception($bitsPerSample . "-bit samples are not supported for Horizontal differencing Predictor.");
                    }
                }
                $lzwDecoder = new TIFFLZWDecoder($w, $predictor, 
                                                $samplePerPixel); 
            }
            $rowsLeft = $h;
            //ByteArrayOutputStream stream = null;
            //DeflaterOutputStream zip = null;
            $g4 = NULL;
            if ($bitsPerSample == 1 && $samplePerPixel == 1) {
                $g4 = new CCITTG4Encoder($w);
            }
            else {
                //stream = new ByteArrayOutputStream();
                //zip = new DeflaterOutputStream(stream);
            }
            for ($k = 0; $k < count($offset); ++$k) {
                $im = itextphp_bytes_create((integer)$size[$k]);
                $s->seek($offset[$k]);
                $s->readFully($im);
                $height = min($rowsStrip, $rowsLeft);
                $outBuf = NULL;//a byte array
                if ($compression != TIFFConstants::COMPRESSION_NONE)
                    $outBuf = itextphp_bytes_create(($w * $bitsPerSample * $samplePerPixel + 7) / 8 * $height);
                switch ($compression) {
                    case TIFFConstants::COMPRESSION_DEFLATE:
                        TiffImage::inflate($im, $outBuf);
                        break;
                    case TIFFConstants::COMPRESSION_NONE:
                        $outBuf = $im;
                        break;
                    case TIFFConstants::COMPRESSION_PACKBITS:
                        TiffImage::decodePackbits($im,  $outBuf);
                        break;
                    case TIFFConstants::COMPRESSION_LZW:
                        $lzwDecoder->decode($im, $outBuf, $height);
                        break;
                }
                if ($bitsPerSample == 1 && $samplePerPixel == 1) {
                    $g4->encodeT6Lines($outBuf, 0, $height);
                }
                else {
                    //zip.write(outBuf);
                    $tmpString = itextphp_getAnsiString($outBuf, itextphp_bytes_getSize($outBuf));
                    $outBuf = itextphp_bytes_createfromRaw(gzdeflate($tmpString));
                }
                $rowsLeft -= $rowsStrip;
            }
            if ($bitsPerSample == 1 && $samplePerPixel == 1) {
                $img = Image::getInstance($w, $h, FALSE, Element::CCITTG4, $photometric == TIFFConstants::PHOTOMETRIC_MINISBLACK ? Element::CCITT_BLACKIS1 : 0, $g4->close());
            }
            else {
                //zip.close();
                $img = Image::getInstance($w, $h, $samplePerPixel, $bitsPerSample, $outBuf);
                $img->setDeflated(TRUE);
            }
            $img->setDpi($dpiX, $dpiY);
            /*** TO DO Implement ICC Profile
            /**if (dir.isTagPresent(TIFFConstants.TIFFTAG_ICCPROFILE)) {
                try {
                    TIFFField fd = dir.getField(TIFFConstants.TIFFTAG_ICCPROFILE);
                    ICC_Profile icc_prof = ICC_Profile.getInstance(fd.getAsBytes());
                    if (samplePerPixel == icc_prof.getNumComponents())
                        img.tagICC(icc_prof);
                }
                catch (Exception e) {
                    //empty
                }
            }**/
            if ($dir->isTagPresent(TIFFConstants::TIFFTAG_COLORMAP) == TRUE) {
                $fd = $dir->getField(TIFFConstants::TIFFTAG_COLORMAP);
                $rgb = $fd->getAsChars();
                $palette = itextphp_bytes_create(strlen($rgb));
                $gColor = strlen($rgb) / 3;
                $bColor = $gColor * 2;
                for ($k = 0; $k < $gColor; ++$k) {
                    itextphp_bytes_write($palette, $k * 3, itextphp_bytes_createfromInt((ord($rgb[$k]) >>> 8)), 0);
                    itextphp_bytes_write($palette, $k * 3 + 1, itextphp_bytes_createfromInt((ord($rgb[$k + $gColor]) >>> 8)), 0);
                    itextphp_bytes_write($palette, $k * 3 + 2, itextphp_bytes_createfromInt((ord($rgb[$k + $bColor]) >>> 8)), 0);
                }
                $indexed = new PdfArray();
                $indexed->add(PdfName::$INDEXED);
                $indexed->add(PdfName::$DEVICERGB);
                $indexed->add(new PdfNumber($gColor - 1));
                $indexed->add(new PdfString($palette));
                $additional = new PdfDictionary();
                $additional->put(PdfName::$COLORSPACE, $indexed);
                $img->setAdditional($additional);
            }
            if ($photometric == TIFFConstants::PHOTOMETRIC_MINISWHITE)
                $img->setInverted(TRUE);
            $img->setOriginalType(Image::ORIGINAL_TIFF);
            return $img;
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    protected static function getArrayLongShort(TIFFDirectory $dir, $tag) {
        $field = $dir->getField($tag);
        if ($field == NULL)
            return NULL;
        $offset = NULL;//array
        if ($field->getType() == TIFFField::TIFF_LONG)
            $offset = $field->getAsLongs();
        else { // must be short
            $temp = $field->getAsChars();
            $offset = array();
            for ($k = 0; $k < strlen($temp); ++$k)
                $offset[$k] = ord($temp[$k]);
        }
        return $offset;
    }

    // Uncompress packbits compressed image data.
    public static function decodePackbits($data, $dst) {
        $srcCount = 0;
        $dstCount = 0;
        $repeat = NULL;//byte
        $b = NULL;//byte

        while ($dstCount < itextphp_bytes_getSize($dst)) {
            $b = itextphp_bytes_getIntValue($data, $srcCount++);
            if ($b >= 0 && $b <= 127) {
                // literal run packet
                for ($i=0; $i<($b + 1); $i++) {
                    itextphp_bytes_write($dst, $dstCount++, $data, $srcCount++);
                }

            } else if ($b <= -1 && $b >= -127) {
                // 2 byte encoded run packet
                $repeat = itextphp_bytes_getIntValue($data, $srcCount++);
                for ($i=0; $i<(-$b + 1); $i++) {
                    itextphp_bytes_write($dst, $dstCount++, itextphp_bytes_createfromInt($repeat), 0);
                }
            } else {
                // no-op packet. Do nothing
                $srcCount++;
            }
        }
    }


    public static function inflate($deflated, $inflated) {
        //Inflater inflater = new Inflater();
        //inflater.setInput(deflated);
        //try {
            //inflater.inflate(inflated);
        //}
        //catch(DataFormatException dfe) {
         //   throw new ExceptionConverter(dfe);
        //}
        $tempString = gzinflate(itextphp_getAnsiString($deflated, itextphp_bytes_getSize($deflated));

        $inflated = itextphp_bytes_createfromRaw($tempString);
    }

}


?>