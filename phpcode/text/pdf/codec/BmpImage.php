<?PHP
/*
 * $Id: BmpImage.php,v 1.2 2005/11/02 21:42:44 mstaylor Exp $
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

require_once("../../../io/InputStream.php");
require_once("../../../exceptions/IOException.php");
require_once("../../Image.php");
require_once("../../BadElementException.php");
require_once("../../ImgRaw.php");
require_once("../PdfArray.php");
require_once("../PdfName.php");
require_once("../PdfNumber.php");
require_once("../PdfDictionary.php");
require_once("../PdfString.php");



/** Reads a BMP image. All types of BMP can be read.
* <p>
* It is based in the JAI codec.
*
* @author  Paulo Soares (psoares@consiste.pt)
*/

class BmpImage
{

    // BMP variables
    private $inputStream = NULL;//InputStream changed to resource handle
    private $bitmapFileSize = 0;
    private $bitmapOffset = 0;
    private $compression = 0;
    private $imageSize = 0;
    private $palette = NULL;//byte
    private $imageType = 0;
    private $numBands = 0;
    private $isBottomUp = FALSE;
    private $bitsPerPixel = 0;
    private $redMask = 0, $greenMask = 0, $blueMask = 0, $alphaMask = 0;
    public $properties = array();
    private $xPelsPerMeter = 0;
    private $yPelsPerMeter = 0;
    // BMP Image types
    private static $VERSION_2_1_BIT = 0;
    private static $VERSION_2_4_BIT = 1;
    private static $VERSION_2_8_BIT = 2;
    private static $VERSION_2_24_BIT = 3;

    private static $VERSION_3_1_BIT = 4;
    private static $VERSION_3_4_BIT = 5;
    private static $VERSION_3_8_BIT = 6;
    private static $VERSION_3_24_BIT = 7;

    private static $VERSION_3_NT_16_BIT = 8;
    private static $VERSION_3_NT_32_BIT = 9;

    private static $VERSION_4_1_BIT = 10;
    private static $VERSION_4_4_BIT = 11;
    private static $VERSION_4_8_BIT = 12;
    private static $VERSION_4_16_BIT = 13;
    private static $VERSION_4_24_BIT = 14;
    private static $VERSION_4_32_BIT = 15;

    // Color space types
    private static $LCS_CALIBRATED_RGB = 0;
    private static $LCS_sRGB = 1;
    private static $LCS_CMYK = 2;

    // Compression Types
    private static $BI_RGB = 0;
    private static $BI_RLE8 = 1;
    private static $BI_RLE4 = 2;
    private static $BI_BITFIELDS = 3;

    $width = 0;
    $height = 0;

    public function __construct($is, $noHeader, $size) {
        $bitmapFileSize = $size;
        $bitmapOffset = 0;
        process($is, $noHeader);
    }


    public static function getImage()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_string($arg1) == TRUE)
                    return BmpImage::getImage1argString($arg1);
                else if (is_resource($arg1) == TRUE && strcmp(get_resource_type($arg1), "file") == TRUE)
                    return BmpImage::getImage1argResource($arg1);
                else if (is_resource($arg1) == TRUE)
                    return BmpImage::getImage1argResourceByte($arg1);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if ((is_resource($arg1) == TRUE || $arg1 == NULL) && is_bool($arg2) == TRUE && is_integer($arg3) == TRUE)
                    return BmpImage::getImage3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    /** Reads a BMP from an url.
    * @param url the url
    * @throws IOException on error
    * @return the image
    */
    public static function getImage1argString($url) {
        $is = null;
        try {
            is = fopen($url, "r");
            $img = BmpImage::getImage($is);
            $img->setUrl($url);
            return $img;
        }
        catch (Exception $e) {
            if ($is != NULL) {
                $is->close();
            return;
            }
        }

        if ($is != NULL) {
            fclose($is);
        }
    }


    /** Reads a BMP from a stream. The stream is not closed.
    * @param is the stream
    * @throws IOException on error
    * @return the image
    */
    public static function getImage1argResource($is) {
        return BmpImage::getImage($is, FALSE, 0);
    }

    /** Reads a BMP from a stream. The stream is not closed.
    * The BMP may not have a header and be considered as a plain DIB.
    * @param is the stream
    * @param noHeader true to process a plain DIB
    * @param size the size of the DIB. Not used for a BMP
    * @throws IOException on error
    * @return the image
    */
    public static function getImage3args($is, $noHeader, $size) throws IOException {
        $bmp = new BmpImage($is, $noHeader, $size);
        try {
            $img = $bmp->getImage();
            $img->setDpi((integer)((double)$bmp->xPelsPerMeter * 0.0254), (integer)((double)$bmp->xPelsPerMeter * 0.0254));
            $img->setOriginalType(Image::ORIGINAL_BMP);
            return $img;
        }
        catch (BadElementException $be) {
            throw new Exception($be);
        }
    }

    /** Reads a BMP from a byte array.
    * @param data the byte array
    * @throws IOException on error
    * @return the image
    */
    public static function getImage1argResourceByte($data) {
        $is = NULL;
        try {
            $is = $data;
            $img = getImage($is);
            $img->setOriginalData($data);
            return $img;
        }
        catch (Exception $e) {
            if ($is != NULL) {
                fclose($is);
            return;
            }
        }
        if ($is != NULL) {
            fclose($is);
            return;
        }
    }

    protected function process($stream, $noHeader) {
        //if (noHeader || stream instanceof BufferedInputStream) {
        $inputStream = stream;
        //} else {
        //    inputStream = new BufferedInputStream(stream);
        //}
        if ($noHeader == FALSE) {
            // Start File Header
            if (!(strcmp(chr(readUnsignedByte($inputStream)), 'B') == 0 &&
            strcmp(chr(readUnsignedByte($inputStream)), 'M') == 0) {
                throw new
                Exception("Invalid magic value for BMP file.");
            }

            // Read file size
            $bitmapFileSize = readDWord($inputStream);

            // Read the two reserved fields
            readWord($inputStream);
            readWord($inputStream);

            // Offset to the bitmap from the beginning
            $bitmapOffset = readDWord($inputStream);

            // End File Header
        }
        // Start BitmapCoreHeader
        $size = readDWord($inputStream);

        if ($size == 12) {
            $width = readWord($inputStream);
            $height = readWord($inputStream);
        } else {
            $width = readLong($inputStream);
            $height = readLong($inputStream);
        }

        $planes = readWord($inputStream);
        $bitsPerPixel = readWord($inputStream);

        $properties["color_planes"] = $planes;
        properties["bits_per_pixel"] = $bitsPerPixel;

        // As BMP always has 3 rgb bands, except for Version 5,
        // which is bgra
        $numBands = 3;
        if ($bitmapOffset == 0)
            $bitmapOffset = $size;
        if (size == 12) {
            // Windows 2.x and OS/2 1.x
            $properties["bmp_version"] = "BMP v. 2.x";

            // Classify the image type
            if ($bitsPerPixel == 1) {
                $imageType = BmpImage::$VERSION_2_1_BIT;
            } else if ($bitsPerPixel == 4) {
                $imageType = BmpImage::$VERSION_2_4_BIT;
            } else if ($bitsPerPixel == 8) {
                $imageType = BmpImage::$VERSION_2_8_BIT;
            } else if ($bitsPerPixel == 24) {
                $imageType = BmpImage::$VERSION_2_24_BIT;
            }

            // Read in the palette
            $numberOfEntries = (integer)(($bitmapOffset-14-$size) / 3);
            $sizeOfPalette = $numberOfEntries*3;
            if ($bitmapOffset == $size) {
                switch ($imageType) {
                    case BmpImage::$VERSION_2_1_BIT:
                        $sizeOfPalette = 2 * 3;
                        break;
                    case BmpImage::$VERSION_2_4_BIT:
                        $sizeOfPalette = 16 * 3;
                        break;
                    case BmpImage::$VERSION_2_8_BIT:
                        $sizeOfPalette = 256 * 3;
                        break;
                    case BmpImage::$VERSION_2_24_BIT:
                        $sizeOfPalette = 0;
                        break;
                }
                $bitmapOffset = $size + $sizeOfPalette;
            }
            //$palette = itextphp_bytes_create($sizeOfPalette);
            rewind($inputStream);
            $palette = itextphp_bytes_createfromRaw(fgets($inputStream, $sizeOfPalette));

            $properties["palette"] = $palette;
        } else {

            $compression = readDWord($inputStream);
            $imageSize = readDWord($inputStream);
            $xPelsPerMeter = readLong($inputStream);
            $yPelsPerMeter = readLong($inputStream);
            $colorsUsed = readDWord($inputStream);
            $colorsImportant = readDWord($inputStream);

            switch((integer)$compression) {
                case BmpImage::$BI_RGB:
                    $properties["compression"] = "BI_RGB";
                    break;

                case BmpImage::$BI_RLE8:
                    $properties["compression"] = "BI_RLE8";
                    break;

                case BmpImage::$BI_RLE4:
                    $properties["compression"] = "BI_RLE4";
                    break;

                case BmpImage::$BI_BITFIELDS:
                    $properties["compression"] = "BI_BITFIELDS";
                    break;
            }

            $properties["x_pixels_per_meter"] = $xPelsPerMeter;
            $properties["y_pixels_per_meter"] = $yPelsPerMeter;
            $properties["colors_used"] = $colorsUsed;
            $properties["colors_important"] = $colorsImportant;

            if ($size == 40) {
                // Windows 3.x and Windows NT
                switch((integer)$compression) {

                    case BmpImage::$BI_RGB:  // No compression
                    case BmpImage::$BI_RLE8:  // 8-bit RLE compression
                    case BmpImage::$BI_RLE4:  // 4-bit RLE compression

                        if ($bitsPerPixel == 1) {
                            $imageType = BmpImage::$VERSION_3_1_BIT;
                        } else if ($bitsPerPixel == 4) {
                            $imageType = BmpImage::$VERSION_3_4_BIT;
                        } else if ($bitsPerPixel == 8) {
                            $imageType = BmpImage::$VERSION_3_8_BIT;
                        } else if ($bitsPerPixel == 24) {
                            $imageType = BmpImage::$VERSION_3_24_BIT;
                        } else if ($bitsPerPixel == 16) {
                            $imageType = BmpImage::$VERSION_3_NT_16_BIT;
                            $redMask = 0x7C00;
                            $greenMask = 0x3E0;
                            $blueMask = 0x1F;
                            $properties["red_mask"] = $redMask;
                            $properties["green_mask"] = $greenMask;
                            $properties["blue_mask"] = $blueMask;
                        } else if ($bitsPerPixel == 32) {
                            $imageType = BmpImage::$VERSION_3_NT_32_BIT;
                            $redMask   = 0x00FF0000;
                            $greenMask = 0x0000FF00;
                            $blueMask  = 0x000000FF;
                            $properties["red_mask"] = $redMask;
                            $properties["green_mask"] = $greenMask;
                            $properties["blue_mask"] = $blueMask;
                        }

                        // Read in the palette
                        $numberOfEntries = (integer)(($bitmapOffset-14-$size) / 4);
                        $sizeOfPalette = $numberOfEntries*4;
                        if ($bitmapOffset == $size) {
                            switch ($imageType) {
                                case BmpImage::$VERSION_3_1_BIT:
                                    $sizeOfPalette = (integer)($colorsUsed == 0 ? 2 : $colorsUsed) * 4;
                                    break;
                                case BmpImage::$VERSION_3_4_BIT:
                                    $sizeOfPalette = (integer)($colorsUsed == 0 ? 16 : $colorsUsed) * 4;
                                    break;
                                case BmpImage::$VERSION_3_8_BIT:
                                    $sizeOfPalette = (integer)($colorsUsed == 0 ? 256 : $colorsUsed) * 4;
                                    break;
                                default:
                                    $sizeOfPalette = 0;
                                    break;
                            }
                            $bitmapOffset = $size + $sizeOfPalette;
                        }
                        //palette = new byte[sizeOfPalette];
                        rewind($inputStream);
                        $palette = itextphp_bytes_createfromRaw(fgets($inputStream, $sizeOfPalette));
                        //inputStream.read(palette, 0, sizeOfPalette);
                        $properties["palette"] = $palette;

                        $properties["bmp_version"] = "BMP v. 3.x";
                        break;

                    case BmpImage::$BI_BITFIELDS:

                        if ($bitsPerPixel == 16) {
                            $imageType = BmpImage::$VERSION_3_NT_16_BIT;
                        } else if ($bitsPerPixel == 32) {
                            $imageType = BmpImage::$VERSION_3_NT_32_BIT;
                        }

                        // BitsField encoding
                        $redMask = (integer)readDWord($inputStream);
                        $greenMask = (integer)readDWord($inputStream);
                        $blueMask = (integer)readDWord($inputStream);

                        $properties["red_mask"] = $redMask;
                        $properties["green_mask"] = $greenMask;
                        $properties["blue_mask"] = $blueMask;

                        if ($colorsUsed != 0) {
                            // there is a palette
                            $sizeOfPalette = (integer)$colorsUsed*4;
                            //palette = new byte[sizeOfPalette];
                            //inputStream.read(palette, 0, sizeOfPalette);
                            $palette = itextphp_bytes_createfromRaw(fgets($inputStream, $sizeOfPalette));
                            $properties["palette"] = $palette;
                        }

                        $properties["bmp_version"] = "BMP v. 3.x NT";
                        break;

                    default:
                        throw new
                        Exception("Invalid compression specified in BMP file.");
                }
            } else if ($size == 108) {
                // Windows 4.x BMP

                $properties["bmp_version"] = "BMP v. 4.x";

                // rgb masks, valid only if comp is BI_BITFIELDS
                $redMask = (integer)readDWord($inputStream);
                greenMask = (integer)readDWord($inputStream);
                blueMask = (integer)readDWord($inputStream);
                // Only supported for 32bpp BI_RGB argb
                $alphaMask = (integer)readDWord($inputStream);
                $csType = readDWord($inputStream);
                $redX = readLong($inputStream);
                $redY = readLong($inputStream);
                $redZ = readLong($inputStream);
                $greenX = readLong($inputStream);
                $greenY = readLong($inputStream);
                $greenZ = readLong($inputStream);
                $blueX = readLong($inputStream);
                $blueY = readLong($inputStream);
                $blueZ = readLong($inputStream);
                $gammaRed = readDWord($inputStream);
                $gammaGreen = readDWord($inputStream);
                $gammaBlue = readDWord($inputStream);

                if ($bitsPerPixel == 1) {
                    $imageType = BmpImage::$VERSION_4_1_BIT;
                } else if ($bitsPerPixel == 4) {
                    $imageType = BmpImage::$VERSION_4_4_BIT;
                } else if ($bitsPerPixel == 8) {
                    $imageType = BmpImage::$VERSION_4_8_BIT;
                } else if ($bitsPerPixel == 16) {
                    $imageType = BmpImage::$VERSION_4_16_BIT;
                    if ((integer)$compression == BmpImage::$BI_RGB) {
                        $redMask = 0x7C00;
                        $greenMask = 0x3E0;
                        $blueMask = 0x1F;
                    }
                } else if ($bitsPerPixel == 24) {
                    $imageType = BmpImage::$VERSION_4_24_BIT;
                } else if ($bitsPerPixel == 32) {
                    $imageType = BmpImage::$VERSION_4_32_BIT;
                    if ((integer)$compression == BmpImage::$BI_RGB) {
                        $redMask   = 0x00FF0000;
                        $greenMask = 0x0000FF00;
                        $blueMask  = 0x000000FF;
                    }
                }

                $properties["red_mask"] = $redMask;
                $properties["green_mask"] = $greenMask;
                $properties["blue_mask"] = $blueMask;
                $properties["alpha_mask"] = $alphaMask;

                // Read in the palette
                $numberOfEntries = (integer)(($bitmapOffset-14-$size) / 4);
                $sizeOfPalette = $numberOfEntries*4;
                if ($bitmapOffset == $size) {
                    switch ($imageType) {
                        case BmpImage::$VERSION_4_1_BIT:
                            $sizeOfPalette = (integer)($colorsUsed == 0 ? 2 : $colorsUsed) * 4;
                            break;
                        case BmpImage::$VERSION_4_4_BIT:
                            $sizeOfPalette = (integer)($colorsUsed == 0 ? 16 : $colorsUsed) * 4;
                            break;
                        case BmpImage::$VERSION_4_8_BIT:
                            $sizeOfPalette = (integer)($colorsUsed == 0 ? 256 : $colorsUsed) * 4;
                            break;
                        default:
                            $sizeOfPalette = 0;
                            break;
                    }
                    $bitmapOffset = $size + $sizeOfPalette;
                }
                //palette = new byte[sizeOfPalette];
                //inputStream.read(palette, 0, sizeOfPalette);
                $palette = itextphp_bytes_createfromRaw(fgets($inputStream, $sizeOfPalette));
                if ($palette != NULL || itextphp_bytes_getSize($palette) != 0) {
                    $properties["palette"] = $palette;
                }

                switch((integer)$csType) {
                    case BmpImage::$LCS_CALIBRATED_RGB:
                        // All the new fields are valid only for this case
                        $properties["color_space"] = "LCS_CALIBRATED_RGB";
                        $properties["redX"] = $redX;
                        $properties["redY"] = $redY;
                        $properties["redZ"] = $redZ;
                        $properties["greenX"] = $greenX;
                        $properties["greenY"] = $greenY;
                        $properties["greenZ"] = $greenZ;
                        $properties["blueX"] = $blueX;
                        $properties["blueY"] = $blueY;
                        $properties["blueZ"] = $blueZ;
                        $properties["gamma_red"] = $gammaRed;
                        $properties["gamma_green"] = $gammaGreen;
                        $properties["gamma_blue"] = $gammaBlue;

                        // break;
                        throw new Exception("Not implemented yet.");

                    case BmpImage::$LCS_sRGB:
                        // Default Windows color space
                        $properties["color_space"] = "LCS_sRGB";
                        break;

                    case BmpImage::$LCS_CMYK:
                        $properties["color_space"] = "LCS_CMYK";
                        //		    break;
                        throw new Exception("Not implemented yet.");
                }

            } else {
                $properties["bmp_version"] = "BMP v. 5.x";
                throw new Exception("BMP version 5 not implemented yet.");
            }
        }

        if ($height > 0) {
            // bottom up image
            $isBottomUp = TRUE;
        } else {
            // top down image
            $isBottomUp = FALSE;
            $height = abs($height);
        }
        // When number of bitsPerPixel is <= 8, we use IndexColorModel.
        if ($bitsPerPixel == 1 || $bitsPerPixel == 4 || $bitsPerPixel == 8) {

            $numBands = 1;


            // Create IndexColorModel from the palette.
            $r = NULL;
            $g = NULL;
            $b = NULL;
            $sizep = 0;
            if ($imageType == BmpImage::$VERSION_2_1_BIT ||
            $imageType == BmpImage::$VERSION_2_4_BIT ||
            $imageType == BmpImage::$VERSION_2_8_BIT) {

                $sizep = itextphp_bytes_getSize($palette)/3;

                if ($sizep > 256) {
                    $sizep = 256;
                }

                $off = 0;
                $r = itextphp_bytes_create($sizep);
                $g = itextphp_bytes_create($sizep);
                $b = itextphp_bytes_create($sizep);
                for ($i=0; i<$sizep; $i++) {
                    $off = 3 * i;
                    itextphp_bytes_write($b, $i, $palette, $off);
                    itextphp_bytes_write($g, $i, $palette, $off+1);
                    itextphp_bytes_write($r, $i, $palette, $off+2);
                }
            } else {
                $sizep = itextphp_bytes_getSize($palette)/4;

                if ($sizep > 256) {
                    $sizep = 256;
                }

                $off = 0;
                $r = itextphp_bytes_create($sizep);
                $g = itextphp_bytes_create($sizep);
                $b = itextphp_bytes_create($sizep);
                for ($i=0; $i<$sizep; $i++) {
                    $off = 4 * $i;
                    itextphp_bytes_write($b, $i, $palette, $off);
                    itextphp_bytes_write($g, $i, $palette, $off+1);
                    itextphp_bytes_write($r, $i, $palette, $off+2);
                }
            }

        } else if ($bitsPerPixel == 16) {
            $numBands = 3;
        } else if ($bitsPerPixel == 32) {
            $numBands = $alphaMask == 0 ? 3 : 4;

            // The number of bands in the SampleModel is determined by
            // the length of the mask array passed in.
            $bitMasks = $numBands == 3 ?
            array ($redMask, $greenMask, $blueMask) :
                array( $redMask, $greenMask, $blueMask, $alphaMask);

        } else {
            $numBands = 3;
        }
    }

    private function getPalette($group) {
        if ($palette == NULL)
            return NULL;
        $np = itextphp_bytes_create(itextphp_bytes_getSize($palette) / $group * 3);
        $e = itextphp_bytes_getSize($palette) / $group;
        for ($k = 0; $k < $e; ++$k) {
            $src = $k * g$roup;
            $dest = $k * 3;
            itextphp_bytes_write($np, $dest + 2, $palette, $src++);
            itextphp_bytes_write($np, $dest + 1, $palette, $src++);
            itextphp_bytes_write($np, $dest, $palette, $src);
        }
        return $np;
    }


    private function getImage() {
        $bdata = NULL; // buffer for byte data
        $sdata = NULL; // buffer for short data
        $idata = NULL; // buffer for int data

        //	if (sampleModel.getDataType() == DataBuffer.TYPE_BYTE)
        //	    bdata = (byte[])((DataBufferByte)tile.getDataBuffer()).getData();
        //	else if (sampleModel.getDataType() == DataBuffer.TYPE_USHORT)
        //	    sdata = (short[])((DataBufferUShort)tile.getDataBuffer()).getData();
        //	else if (sampleModel.getDataType() == DataBuffer.TYPE_INT)
        //	    idata = (int[])((DataBufferInt)tile.getDataBuffer()).getData();

        // There should only be one tile.
        switch($imageType) {

            case BmpImage::$VERSION_2_1_BIT:
                // no compression
                return read1Bit(3);

            case BmpImage::$VERSION_2_4_BIT:
                // no compression
                return read4Bit(3);

            case BmpImage::$VERSION_2_8_BIT:
                // no compression
                return read8Bit(3);

            case BmpImage::$VERSION_2_24_BIT:
                // no compression
                $bdata = itextphp_bytes_create($width * $height * 3);
                read24Bit($bdata);
                return new ImgRaw($width, $height, 3, 8, $bdata);

            case BmpImage::$VERSION_3_1_BIT:
                // 1-bit images cannot be compressed.
                return read1Bit(4);

            case BmpImage::$VERSION_3_4_BIT:
                switch((integer)$compression) {
                    case BmpImage::$BI_RGB:
                        return read4Bit(4);

                    case BmpImage::$BI_RLE4:
                        return readRLE4();

                    default:
                        throw new Exception("Invalid compression specified for BMP file.");
                }

            case BmpImage::$VERSION_3_8_BIT:
                switch((integer)$compression) {
                    case BmpImage::$BI_RGB:
                        return read8Bit(4);

                    case BmpImage::$BI_RLE8:
                        return readRLE8();

                    default:
                        throw new Exception("Invalid compression specified for BMP file.");
                }

            case BmpImage::$VERSION_3_24_BIT:
                // 24-bit images are not compressed
                $bdata = itextphp_bytes_create($width * $height * 3);
                read24Bit($bdata);
                return new ImgRaw($width, $height, 3, 8, $bdata);

            case BmpImage::$VERSION_3_NT_16_BIT:
                return read1632Bit(FALSE);

            case BmpImage::$VERSION_3_NT_32_BIT:
                return read1632Bit(TRUE);

            case BmpImage::$VERSION_4_1_BIT:
                return read1Bit(4);

            case BmpImage::$VERSION_4_4_BIT:
                switch((integer)$compression) {

                    case BmpImage::$BI_RGB:
                        return read4Bit(4);

                    case BmpImage::$BI_RLE4:
                        return readRLE4();

                    default:
                        throw new Exception("Invalid compression specified for BMP file.");
                }

            case BmpImage::$VERSION_4_8_BIT:
                switch((integer)$compression) {

                    case BmpImage::$BI_RGB:
                        return read8Bit(4);

                    case BmpImage::$BI_RLE8:
                        return readRLE8();

                    default:
                        throw new Exception("Invalid compression specified for BMP file.");
                }

            case BmpImage::$VERSION_4_16_BIT:
                return read1632Bit(FALSE);

            case BmpImage::$VERSION_4_24_BIT:
                $bdata = itextphp_bytes_create($width * $height * 3);
                read24Bit($bdata);
                return new ImgRaw($width, $height, 3, 8, $bdata);

            case BmpImage::$VERSION_4_32_BIT:
                return read1632Bit(TRUE);
        }
        return NULL;
    }

    private function indexedModel($bdata, $bpc, $paletteEntries){
        $img = new ImgRaw($width, $height, 1, $bpc, $bdata);
        $colorspace = new PdfArray();
        $colorspace->add(PdfName::$INDEXED);
        $colorspace->add(PdfName::$DEVICERGB);
        $np = getPalette($paletteEntries);
        $len = itextphp_bytes_getSize($np);
        $colorspace->add(new PdfNumber($len / 3 - 1));
        $colorspace->add(new PdfString($np));
        $ad = new PdfDictionary();
        $ad->put(PdfName::$COLORSPACE, $colorspace);
        $img->setAdditional($ad);
        return $img;
    }

    // Deal with 1 Bit images using IndexColorModels
    private function read1Bit($paletteEntries) {
        $bdata = itextphp_bytes_create((($width + 7) / 8) * $height);
        $padding = 0;
        $bytesPerScanline = (integer)ceil((double)$width/8.0);

        $remainder = $bytesPerScanline % 4;
        if ($remainder != 0) {
            $padding = 4 - $remainder;
        }

        $imSize = ($bytesPerScanline + $padding) * $height;

        // Read till we have the whole image
        $values = itextphp_bytes_create($imSize);
        $bytesRead = 0;
        while ($bytesRead < $imSize) {
            fseek($inputStream,$bytesRead);
            $tmpValue = fread($inputStream, $imSize - $bytesRead);
            itextphp_bytes_write($values, $tmpValue);
            $bytesRead += $imSize - $bytesRead;
        }

        if ($isBottomUp == TRUE) {

            // Convert the bottom up image to a top down format by copying
            // one scanline from the bottom to the top at a time.

            for ($i=0; $i<$height; $i++) {
                $srcpos = $imSize - ($i+1)*($bytesPerScanline + $padding);
                $despos = $i*$bytesPerScanline;
                for ($k = 0; $k < $bytesPerScanline;$k++)
                {
                    itextphp_bytes_write($bdata, $despos, $values, $srcpos);
                    $srcpos++;
                    $despos++;
                }
            }
        } else {

            for ($i=0; $i<$height; $i++) {
                $srcpos = $i * ($bytesPerScanline + $padding);
                $despos = $i * $bytesPerScanline
                for ($k = 0; $k < $bytesPerScanline;$k++)
                {
                    itextphp_bytes_write($bdata, $despos, $values, $srcpos);
                    $srcpos++;
                    $despos++;
                }
            }
        }
        return indexedModel($bdata, 1, $paletteEntries);
    }


    // Method to read a 4 bit BMP image data
    private function read4Bit($paletteEntries) {
        $bdata = itextphp_bytes_create((($width + 1) / 2) * $height);

        // Padding bytes at the end of each scanline
        $padding = 0;

        $bytesPerScanline = (integer)ceil((double)$width/2.0);
        $remainder = $bytesPerScanline % 4;
        if ($remainder != 0) {
            $padding = 4 - $remainder;
        }

        $imSize = ($bytesPerScanline + $padding) * $height;

        // Read till we have the whole image
        $values = itextphp_bytes_create($imSize);
        $bytesRead = 0;
        while ($bytesRead < $imSize) {
            fseek($inputStream,$bytesRead);
            $tmpValue = fread($inputStream, $imSize - $bytesRead);
            itextphp_bytes_write($values, $tmpValue);
            $bytesRead += $imSize - $bytesRead;
        }

        if ($isBottomUp == TRUE) {

            // Convert the bottom up image to a top down format by copying
            // one scanline from the bottom to the top at a time.
            for ($i=0; $i<$height; $i++) {
                $srcpos = $imSize - ($i+1)*($bytesPerScanline + $padding);
                $despos = $i*$bytesPerScanline;
                for ($k = 0; $k < $bytesPerScanline;$k++)
                {
                    itextphp_bytes_write($bdata, $despos, $values, $srcpos);
                    $srcpos++;
                    $despos++;
                }
            }
        } else {
            for ($i=0; $i<$height; $i++) {
                $srcpos = $i * ($bytesPerScanline + $padding);
                $despos = $i*$bytesPerScanline;
                for ($k = 0; $k < $bytesPerScanline;$k++)
                {
                    itextphp_bytes_write($bdata, $despos, $values, $srcpos);
                    $srcpos++;
                    $despos++;
                }
            }
        }
        return indexedModel($bdata, 4, $paletteEntries);
    }


    // Method to read 8 bit BMP image data
    private function read8Bit($paletteEntries) {
        $bdata = itextphp_bytes_create($width * $height);
        // Padding bytes at the end of each scanline
        $padding = 0;

        // width * bitsPerPixel should be divisible by 32
        $bitsPerScanline = $width * 8;
        if ( $bitsPerScanline%32 != 0) {
            $padding = ($bitsPerScanline/32 + 1)*32 - $bitsPerScanline;
            $padding = (integer)ceil($padding/8.0);
        }

        $imSize = ($width + $padding) * $height;

        // Read till we have the whole image
        $values = itextphp_bytes_create($imSize);
        $bytesRead = 0;
        while ($bytesRead < $imSize) {
            fseek($inputStream,$bytesRead);
            $tmpValue = fread($inputStream, $imSize - $bytesRead);
            itextphp_bytes_write($values, $tmpValue);
            $bytesRead += $imSize - $bytesRead;
        }

        if ($isBottomUp == TRUE) {

            // Convert the bottom up image to a top down format by copying
            // one scanline from the bottom to the top at a time.
            for ($i=0; $i<$height; $i++) {
                $srcpos = $imSize - ($i+1) * ($width + $padding);
                $despos = $i * $width;
                for ($k = 0; $k < $width;$k++)
                {
                    itextphp_bytes_write($bdata, $despos, $values, $srcpos);
                    $srcpos++;
                    $despos++;
                }
            }
        } else {
            for ($i=0; $i<$height; $i++) {
                $srcpos = $i * ($width + $padding);
                $despos = $i * $width;
                for ($k = 0; $k < $width;$k++)
                {
                    itextphp_bytes_write($bdata, $despos, $values, $srcpos);
                    $srcpos++;
                    $despos++;
                }
            }
        }
        return indexedModel($bdata, 8, $paletteEntries);
    }

    // Method to read 24 bit BMP image data
    private function read24Bit($bdata) {
        // Padding bytes at the end of each scanline
        $padding = 0;

        // width * bitsPerPixel should be divisible by 32
        $bitsPerScanline = $width * 24;
        if ( $bitsPerScanline%32 != 0) {
            $padding = ($bitsPerScanline/32 + 1)*32 - $bitsPerScanline;
            $padding = (integer)ceil($padding/8.0);
        }


        $imSize = (($width * 3 + 3) / 4 * 4) * $height;
        // Read till we have the whole image
        $values = itextphp_bytes_create($imSize);
        try {
            $bytesRead = 0;
            while ($bytesRead < $imSize) {
                fseek($inputStream,$bytesRead);
                $tmpValue = fread($inputStream, $imSize - $bytesRead);
                itextphp_bytes_write($values, $tmpValue);
                $r = $imSize - $bytesRead;
                if ($r < 0)
                    break;
                $bytesRead += $r;
            }
        } catch (IOException $ioe) {
            throw new Exception($ioe);
        }

        $l=0;
        $count = 0;

        if ($isBottomUp == TRUE) {
            $max = $width*$height*3-1;

            $count = -$padding;
            for ($i=0; $i<$height; $i++) {
                $l = $max - ($i+1)*$width*3 + 1;
                $count += $padding;
                for ($j=0; $j<$width; $j++) {
                    itextphp_bytes_write($bdata, $l + 2, $values, $count++);
                    itextphp_bytes_write($bdata, $l + 1, $values, $count++);
                    itextphp_bytes_write($bdata, $l, $values, $count++);
                    $l += 3;
                }
            }
        } else {
            $count = -$padding;
            for ($i=0; $i<$height; $i++) {
                $count += $padding;
                for ($j=0; $j<$width; $j++) {
                    itextphp_bytes_write($bdata, $l + 2, $values, $count++);
                    itextphp_bytes_write($bdata, $l + 1, $values, $count++);
                    itextphp_bytes_write($bdata, $l, $values, $count++);
                    $l += 3;
                }
            }
        }
    }

    private function findMask($mask) {
        $k = 0;
        for (; $k < 32; ++$k) {
            if (($mask & 1) == 1)
                break;
            $mask >>>= 1;
        }
        return $mask;
    }

    private function findShift($mask) {
        $k = 0;
        for (; $k < 32; ++$k) {
            if (($mask & 1) == 1)
                break;
            $mask >>>= 1;
        }
        return $k;
    }

    private Image read1632Bit($is32) {

        $red_mask = findMask($redMask);
        $red_shift = findShift($redMask);
        $red_factor = $red_mask + 1;
        $green_mask = findMask($greenMask);
        $green_shift = findShift($greenMask);
        $green_factor = $green_mask + 1;
        $blue_mask = findMask($blueMask);
        $blue_shift = findShift($blueMask);
        $blue_factor = $blue_mask + 1;
        $bdata = itextphp_bytes_create($width * $height * 3);
        // Padding bytes at the end of each scanline
        $padding = 0;

        if ($is32 == FALSE) {
        // width * bitsPerPixel should be divisible by 32
            $bitsPerScanline = $width * 16;
            if ( $bitsPerScanline%32 != 0) {
                $padding = ($bitsPerScanline/32 + 1)*32 - $bitsPerScanline;
                $padding = (integer)ceil($padding/8.0);
            }
        }

        $imSize = (integer)$imageSize;
        if ($imSize == 0) {
            $imSize = (integer)($bitmapFileSize - $bitmapOffset);
        }

        $l=0;
        $v = 0;
        if ($isBottomUp == TRUE) {
            $max = $width*$height-1;

            for ($i=$height - 1; $i >= 0; --$i) {
                $l = $width * 3 * $i;
                for ($j=0; $j<$width; $j++) {
                    if ($is32 == TRUE)
                        $v = (integer)readDWord($inputStream);
                    else
                        $v = readWord($inputStream);
                    itextphp_bytes_write($bdata, $l++, itextphp_bytes_createfromInt((($v >>> $red_shift) & $red_mask) * 256 / $red_factor), 0);
                    itextphp_bytes_write($bdata, $l++, itextphp_bytes_createfromInt((($v >>> $green_shift) & $green_mask) * 256 / $green_factor), 0);
                    itextphp_bytes_write($bdata, $l++, itextphp_bytes_createfromInt((($v >>> $blue_shift) & $blue_mask) * 256 / $blue_factor), 0);
                }
                for ($m=0; $m<$padding; $m++) {
                    fread($inputStream);
                }
            }
        } else {
            for ($i=0; $i<$height; $i++) {
                for ($j=0; $j<$width; $j++) {
                    if ($is32 == TRUE)
                        $v = (integer)readDWord($inputStream);
                    else
                        $v = readWord($inputStream);
                    itextphp_bytes_write($bdata, $l++, itextphp_bytes_createfromInt((($v >>> $red_shift) & $red_mask) * 256 / $red_factor), 0);
                    itextphp_bytes_write($bdata, $l++, itextphp_bytes_createfromInt((($v >>> $green_shift) & $green_mask) * 256 / $green_factor), 0);
                    itextphp_bytes_write($bdata, $l++, itextphp_bytes_createfromInt((($v >>> $blue_shift) & $blue_mask) * 256 / $blue_factor), 0);
                }
                for ($m=0; $m<$padding; $m++) {
                    fread($inputStream);
                }
            }
        }
        return new ImgRaw($width, $height, 3, 8, $bdata);
    }

    private function readRLE8() {

        // If imageSize field is not provided, calculate it.
        $imSize = (integer)$imageSize;
        if ($imSize == 0) {
            $imSize = (integer)($bitmapFileSize - $bitmapOffset);
        }

        $padding = 0;
        // If width is not 32 bit aligned, then while uncompressing each
        // scanline will have padding bytes, calculate the amount of padding
        $remainder = $width % 4;
        if ($remainder != 0) {
            $padding = 4 - $remainder;
        }

        // Read till we have the whole image
        $values = itextphp_bytes_create($imSize);
        $bytesRead = 0;
        while ($bytesRead < $imSize) {
            fseek($inputStream,$bytesRead);
            $tmpValue = fread($inputStream, $imSize - $bytesRead);
            itextphp_bytes_write($values, $tmpValue);
            $bytesRead += $imSize - $bytesRead;
        }

        // Since data is compressed, decompress it
        $val = decodeRLE(TRUE, $values);

        // Uncompressed data does not have any padding
        $imSize = $width * $height;

        if ($isBottomUp == TRUE) {

            // Convert the bottom up image to a top down format by copying
            // one scanline from the bottom to the top at a time.
            // int bytesPerScanline = (int)Math.ceil((double)width/8.0);
            $temp = itextphp_bytes_create(itextphp_bytes_getSize($val));
            $bytesPerScanline = $width;
            for ($i=0; $i<$height; $i++) {
                $srcpos = $imSize - ($i+1)*($bytesPerScanline);
                $despos = $i*$bytesPerScanline;
                for ($k = 0; $k < $bytesPerScanline;$k++)
                {
                    itextphp_bytes_write($temp, $despos, $val, $srcpos);
                    $srcpos++;
                    $despos++;
                }
            }
            $val = $temp;
        }
        return indexedModel($val, 8, 4);
    }

    private function readRLE4() {

        // If imageSize field is not specified, calculate it.
        $imSize = (integer)$imageSize;
        if ($imSize == 0) {
            $imSize = (integer)($bitmapFileSize - $bitmapOffset);
        }

        $padding = 0;
        // If width is not 32 byte aligned, then while uncompressing each
        // scanline will have padding bytes, calculate the amount of padding
        $remainder = $width % 4;
        if ($remainder != 0) {
            $padding = 4 - $remainder;
        }

        // Read till we have the whole image
        $values = itextphp_bytes_create($imSize);
        $bytesRead = 0;
        while ($bytesRead < $imSize) {
            fseek($inputStream,$bytesRead);
            $tmpValue = fread($inputStream, $imSize - $bytesRead);
            itextphp_bytes_write($values, $tmpValue);
            $bytesRead += $imSize - $bytesRead;

        }

        // Decompress the RLE4 compressed data.
        $val = decodeRLE(FALSE, $values);

        // Invert it as it is bottom up format.
        if ($isBottomUp == TRUE) {

            $inverted = $val;
            $val = itextphp_bytes_create($width * $height);
            $l = 0;
            $index = 0;
            $lineEnd = 0;

            for ($i = $height-1; $i >= 0; $i--) {
                $index = $i * $width;
                $lineEnd = $l + $width;
                while($l != $lineEnd) {
                    itextphp_bytes_write($val, $l++, $inverted, $index++);
                }
            }
        }
        $stride = (($width + 1) / 2);
        $bdata = itextphp_bytes_create($stride * $height);
        $ptr = 0;
        $flip = TRUE;
        $sh = 0;
        for ($h = 0; $h < $height; ++$h) {
            for ($w = 0; $w < $width; ++$w) {
                if (($w & 1) == 0)
                    itextphp_bytes_write($bdata, $sh + $w / 2, itextphp_bytes_createfromInt(itextphp_bytes_getIntValue($val, $ptr++) << 4), 0);
                else
                    itextphp_bytes_bitwiseAssign($bdata, $sh + $w / 2], itextphp_bytes_createfromInt(itextphp_bytes_getIntValue($val, $ptr++) & 0x0f), 0);
            }
            $sh += $stride;
        }
        return indexedModel($bdata, 4, 4);
    }

    private function decodeRLE($is8, $values) {
        $val = itextphp_bytes_create($width * $height);
        try {
            $ptr = 0;
            $x = 0;
            $q = 0;
            for ($y = 0; $y < $height && $ptr < itextphp_bytes_getSize($values);) {
                $count = itextphp_bytes_getIntValue($values, $ptr++) & 0xff;
                if ($count != 0) {
                    // encoded mode
                    $bt = itextphp_bytes_getIntValue($values, $ptr++) & 0xff;
                    if ($is8 == TRUE) {
                        for ($i = $count; $i != 0; --$i) {
                            itextphp_bytes_write($val, $q++,  itextphp_bytes_createfromInt($bt), 0);
                        }
                    }
                    else {
                        for ($i = 0; $i < $count; ++$i) {
                            itextphp_bytes_write($val, $q++, itextphp_bytes_createfromInt(($i & 1) == 1 ? ($bt & 0x0f) : (($bt >>> 4) & 0x0f)), 0);
                        }
                    }
                    $x += $count;
                }
                else {
                    // escape mode
                    $count = itextphp_bytes_getIntValue($values, $ptr++) & 0xff;
                    if ($count == 1)
                        break;
                    switch ($count) {
                        case 0:
                            $x = 0;
                            ++$y;
                            $q = $y * $width;
                            break;
                        case 2:
                            // delta mode
                            $x += itextphp_bytes_getIntValue($values, $ptr++) & 0xff;
                            $y += itextphp_bytes_getIntValue($values, $ptr++) & 0xff;
                            $q = $y * $width + $x;
                            break;
                        default:
                            // absolute mode
                            if ($is8 == TRUE) {
                                for ($i = $count; $i != 0; --$i)
                                    itextphp_bytes_write($val, $q++, itextphp_bytes_createfromInt(itextphp_bytes_getIntValue($values, $ptr++) & 0xff), 0);
                            }
                            else {
                                $bt = 0;
                                for ($i = 0; $i < $count; ++$i) {
                                    if (($i & 1) == 0)
                                        $bt = itextphp_bytes_getIntValue($values, $ptr++) & 0xff;
                                    itextphp_bytes_write($val, $q++, itextphp_bytes_createfromInt(($i & 1) == 1 ? ($bt & 0x0f) : (($bt >>> 4) & 0x0f)), 0);
                                }
                            }
                            $x += $count;
                            // read pad byte
                            if ($is8 == TRUE) {
                                if (($count & 1) == 1)
                                    ++$ptr;
                            }
                            else {
                                if (($count & 3) == 1 || ($count & 3) == 2)
                                    ++$ptr;
                            }
                            break;
                    }
                }
            }
        }
        catch (Exception $e) {
            //empty on purpose
        }

        return $val;
    }


    // Windows defined data type reading methods - everything is little endian

    // Unsigned 8 bits
    private function readUnsignedByte($stream) {
        return (ord(fread($stream)) & 0xff);
    }

    // Unsigned 2 bytes
    private function readUnsignedShort($stream)  {
        $b1 = readUnsignedByte($stream);
        $b2 = readUnsignedByte($stream);
        return (($b2 << 8) | $b1) & 0xffff;
    }

    // Signed 16 bits
    private function readShort($stream) {
        $b1 = readUnsignedByte($stream);
        $b2 = readUnsignedByte($stream);
        return ($b2 << 8) | $b1;
    }

     // Unsigned 16 bits
    private function readWord($stream) {
        return readUnsignedShort($stream);
    }

    // Unsigned 4 bytes
    private function readUnsignedInt($stream)  {
        $b1 = readUnsignedByte($stream);
        $b2 = readUnsignedByte($stream);
        $b3 = readUnsignedByte($stream);
        $b4 = readUnsignedByte($stream);
        $l = (integer)(($b4 << 24) | ($b3 << 16) | ($b2 << 8) | $b1);
        return $l & 0xffffffff;
    }

    // Signed 4 bytes
    private function readInt($stream) {
        $b1 = readUnsignedByte($stream);
        $b2 = readUnsignedByte($stream);
        $b3 = readUnsignedByte($stream);
        $b4 = readUnsignedByte($stream);
        return ($b4 << 24) | ($b3 << 16) | ($b2 << 8) | $b1;
    }

    // Unsigned 4 bytes
    private function readDWord($stream) {
        return readUnsignedInt($stream);
    }

    // 32 bit signed value
    private function readLong($stream) {
        return readInt($stream);
    }
}



?>