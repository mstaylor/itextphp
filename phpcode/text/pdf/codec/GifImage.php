<?PHP
/*
 * $Id: GifImage.php,v 1.3 2005/11/09 15:39:00 mstaylor Exp $
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

require_once("../../../exceptions/IOException.php");
require_once("../../../util/StringHelpers.php");
require_once("../../Image.php");
require_once("../../ImgRaw.php");
require_once("../../PdfArray.php");
require_once("../../PdfName.php");
require_once("../../PdfNumber.php");
require_once("../../PdfString.php");
require_once("../../PdfDictionary.php");

/** Reads gif images of all types. All the images in a gif are read in the constructors
* and can be retrieved with other methods.
* @author Paulo Soares (psoares@consiste.pt)
*/

class GifImage
{

    protected $in = NULL;//DataInputStream
    protected $width = 0;            // full image width
    protected $height = 0;           // full image height
    protected $gctFlag = FALSE;      // global color table used

    protected $bgIndex = 0;          // background color index
    protected $bgColor = 0;          // background color
    protected $pixelAspect = 0;      // pixel aspect ratio

    protected $lctFlag = FALSE;      // local color table flag
    protected $interlace = FALSE;    // interlace flag
    protected $lctSize = 0;          // local color table size

    protected $ix = 0, $iy = 0, $iw = 0, $ih = 0;   // current image rectangle

    protected $block = NULL;  // (a byte array)current data block
    protected $blockSize = 0;    // block size

    // last graphic control extension info
    protected $dispose = 0;   // 0=no action; 1=leave in place; 2=restore to bg; 3=restore to prev
    protected $transparency = FALSE;   // use transparent color
    protected $delay = 0;        // delay in milliseconds
    protected $transIndex = 0;       // transparent color index

    protected static $MaxStackSize = 4096;   // max decoder pixel stack size

    // LZW decoder working arrays
    protected $prefix = NULL; //array of short
    protected $suffix = NULL; //byte array
    protected $pixelStack = NULL; //byte array
    protected $pixels = NULL; //byte array

    protected $m_out = NULL; //byte array
    protected $m_bpc = 0;
    protected $m_gbpc = 0;
    protected $m_global_table = NULL; //byte array
    protected $m_local_table = NULL; //byte array
    protected $m_curr_table = NULL; //byte array
    protected $m_line_stride = 0;
    protected $fromData = NULL; //byte array
    protected $fromUrl = NULL;

    protected $frames = array();     // frames read from current file

    private function onConstruct()
    {
       $block = itextphp_bytes_create(256);
    }


    public function __construct()
    {
        $num_args=func_num_args();
        onConstruct();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_string($arg1) == TRUE)
                    construct1argString($arg1);
                else if (is_resource($arg1) == TRUE) //a byte resource
                    construct1argResource($arg1);
                break;
            }
        }
    }

    private function construct1argString($file)
    {
        try
        {
        $is = fopen($file, "r");
        process($is);
        }
        catch(Exception $e)
        {
            if ($is != FALSE && $is != NULL)
                fclose($is);
            return;
        }
        if ($is != FALSE && $is != NULL)
                fclose($is);
            return;
    }


    /** Reads gif images from a byte array.
    * @param data the byte array
    * @throws IOException on error
    */
    private function construct1argResource($data) {
        $fromData = $data;
        $is = NULL;
        try {
            $is = $data;
            process($is);
        }
        catch (Exception $e) {
            if ($is != NULL) {
                fclose($is);
            return;
            }
        }
        if ($is != NULL) {
            fclose($is);
    }


    /** Gets the number of frames the gif has.
    * @return the number of frames the gif has
    */
    public function getFrameCount() {
        return count($frames);
    }

    /** Gets the image from a frame. The first frame is 1.
    * @param frame the frame to get the image from
    * @return the image
    */
    public function getImage(#frame) {
        $gf = $frames[$frame - 1];
        return $gf->image;
    }

    /** Gets the [x,y] position of the frame in reference to the
    * logical screen.
    * @param frame the frame
    * @return the [x,y] position of the frame
    */
    public function getFramePosition($frame) {
        $gf = $frames[$frame - 1];
        return array($gf->ix, $gf->iy);

    }

    /** Gets the logical screen. The images may be smaller and placed
    * in some position in this screen to playback some animation.
    * No image will be be bigger that this.
    * @return the logical screen dimensions as [x,y]
    */
    public function getLogicalScreen() {
        return array($width, $height);
    }

    public function process()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                if(strcmp(get_resource_type($arg1), "stream") == 0)
                    process1argFile($arg1);
                else if (is_resource($arg1) == TRUE)
                    process1argByte($arg1);
                break;
            }
        }
    }


    private function process1argFile($is)
    {

        $contents = stream_get_contents($handle);
        $in = itextphp_bytes_createfromRaw($is);
        readHeader();
        readContents();
        if (count($frames) == 0)
            throw new IOException("The file does not contain any valid image.");
    }

    private function process1argByte($is)
    {
        $in = $is;
        readHeader();
        readContents();
        if (count($frames) == 0)
            throw new IOException("The file does not contain any valid image.");
    }

    /**
    * Reads GIF file header information.
    */
    protected function readHeader()  {
        $id = "";
        for ($i = 0; $i < 6; $i++)
            $id .= itextphp_bytes_read($in);
        if (beginsWith($id., "GIF8") == TRUE) {
            throw new IOException("Gif signature nor found.");
        }

        readLSD();
        if ($gctFlag == TRUE) {
            $m_global_table = readColorTable($m_gbpc);
        }
    }

    /**
    * Reads Logical Screen Descriptor
    */
    protected function readLSD()  {

        // logical screen size
        $width = readShort();
        $height = readShort();

        // packed fields
        $packed = ord(itextphp_bytes_read($in));
        $gctFlag = ($packed & 0x80) != 0;      // 1   : global color table flag
        $m_gbpc = ($packed & 7) + 1;
        $bgIndex = ord(itextphp_bytes_read($in));        // background color index
        $pixelAspect = ord(itextphp_bytes_read($in));    // pixel aspect ratio
    }

    /**
    * Reads next 16-bit value, LSB first
    */
    protected function readShort() {
        // read 16-bit value, LSB first
        return ord(itextphp_bytes_read($in)) | (ord(itextphp_bytes_read($in)) << 8);
    }

    /**
    * Reads next variable length block from input.
    *
    * @return number of bytes stored in "buffer"
    */
    protected function readBlock() {
        $blockSize = ord(itextphp_bytes_read($in));
        if ($blockSize <= 0)
            return $blockSize = 0;
        for ($k = 0; $k < $blockSize; ++$k) {
            $v = ord(itextphp_bytes_read($in));
            if ($v < 0) {
                return $blockSize = k;
            }
            itextphp_bytes_write($block, $k, itextphp_bytes_createfromInt($v), 0);
        }
        return $blockSize;
    }

    protected function readColorTable($bpc) {
        $ncolors = 1 << $bpc;
        $nbytes = 3*$ncolors;
        $bpc = newBpc($bpc);
        $table = itextphp_bytes_create((1 << $bpc) * 3);
        for ($k = 0; $k < $nbytes; $k++)
            itextphp_bytes_write($table, $k, $in, $k);
        return $table;
    }

    static protected function newBpc($bpc) {
        switch ($bpc) {
            case 1:
            case 2:
            case 4:
                break;
            case 3:
                return 4;
            default:
                return 8;
        }
        return $bpc;
    }

    protected function readContents()  {
        // read GIF file content blocks
        $done = FALSE;
        while ($done == FALSE) {
            $code = ord(itextphp_bytes_read($in));
            switch ($code) {

                case 0x2C:    // image separator
                    readImage();
                    break;

                case 0x21:    // extension
                    $code = ord(itextphp_bytes_read($in));
                    switch ($code) {

                        case 0xf9:    // graphics control extension
                            readGraphicControlExt();
                            break;

                        case 0xff:    // application extension
                            readBlock();
                            skip();        // don't care
                            break;

                        default:    // uninteresting extension
                            skip();
                    }
                    break;

                default:
                    $done = TRUE;
                    break;
            }
        }
    }


    /**
    * Reads next frame image
    */
    protected function readImage() {
        $ix = readShort();    // (sub)image position & size
        $iy = readShort();
        $iw = readShort();
        $ih = readShort();

        $packed = ord(itextphp_bytes_read($in));
        $lctFlag = ($packed & 0x80) != 0;     // 1 - local color table flag
        $interlace = ($packed & 0x40) != 0;   // 2 - interlace flag
        // 3 - sort flag
        // 4-5 - reserved
        $lctSize = 2 << ($packed & 7);        // 6-8 - local color table size
        $m_bpc = newBpc($m_gbpc);
        if ($lctFlag == TRUE) {
            $m_curr_table = readColorTable(($packed & 7) + 1);   // read table
            $m_bpc = newBpc(($packed & 7) + 1);
        }
        else {
            $m_curr_table = $m_global_table;
        }
        if ($transparency && $transIndex >= itextphp_bytes_getSize($m_curr_table) / 3)
            $transparency = FALSE;
        if ($transparency && $m_bpc == 1) { // Acrobat 5.05 doesn't like this combination
            $tp = itextphp_bytes_create(12);
            for ($k = 0; $k < 6; $k++)
            {
                itextphp_bytes_write($tp, $k, $m_curr_table, $k);
            }
            $m_curr_table = $tp;
            $m_bpc = 2;
        }
        $skipZero = decodeImageData();   // decode pixel data
        if ($skipZero == FALSE)
            skip();

        $img = NULL;
        try {
            $img = new ImgRaw($iw, $ih, 1, $m_bpc, $m_out);
            $colorspace = new PdfArray();
            $colorspace->add(PdfName::$INDEXED);
            $colorspace->add(PdfName::$DEVICERGB);
            $len = itextphp_bytes_getSize($m_curr_table);
            $colorspace->add(new PdfNumber($len / 3 - 1));
            $colorspace->add(new PdfString($m_curr_table));
            $ad = new PdfDictionary();
            $ad->put(PdfName::$COLORSPACE, $colorspace);
            $img->setAdditional($ad);
            if ($transparency == TRUE) {
                $img->setTransparency(array($transIndex, $transIndex));
            }
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
        $img->setOriginalType(Image::ORIGINAL_GIF);
        $img->setOriginalData($fromData);
        $img->setUrl($fromUrl);
        $gf = new GifFrame();
        $gf->image = $img;
        $gf->ix = $ix;
        $gf->iy = $iy;
        array_push($frames, $gf);   // add image to frame list

        resetFrame();

    }

    protected function decodeImageData() {
        $NullCode = -1;
        $npix = $iw * $ih;
        $available = 0;
        $clear = 0;
        $code_mask = 0;
        $code_size = 0;
        $end_of_information = 0;
        $in_code = 0;
        $old_code = 0;
        $bits = 0;
        $code = 0;
        $count = 0;
        $i = 0;
        $datum = 0;
        $data_size = 0;
        $first = 0;
        $top = 0;
        $bi = 0;
        $pi = 0;
        $skipZero = FALSE;

        if ($prefix == NULL)
            $prefix = array();
        if ($suffix == NULL)
            $suffix = itextphp_bytes_create($MaxStackSize);
        if ($pixelStack == NULL)
            $pixelStack = itextphp_bytes_create($MaxStackSize+1);

        $m_line_stride = ($iw * $m_bpc + 7) / 8;
        $m_out = itextphp_bytes_create($m_line_stride * $ih);
        $pass = 1;
        $inc = interlace ? 8 : 1;
        $line = 0;
        $xpos = 0;

        //  Initialize GIF data stream decoder.

        $data_size = ord(itextphp_bytes_read($in));
        $clear = 1 << $data_size;
        $end_of_information = $clear + 1;
        $available = $clear + 2;
        $old_code = $NullCode;
        $code_size = $data_size + 1;
        $code_mask = (1 << $code_size) - 1;
        for ($code = 0; $code < $clear; $code++) {
            $prefix[$code] = 0;
            itextphp_bytes_write($suffix, $code, itextphp_bytes_createfromInt($code), 0);
        }

        //  Decode GIF pixel stream.

        $datum = $bits = $count = $first = $top = $pi = $bi = 0;

        for ($i = 0; $i < $npix; ) {
            if ($top == 0) {
                if ($bits < $code_size) {
                    //  Load bytes until there are enough bits for a code.
                    if ($count == 0) {
                        // Read a new data block.
                        $count = readBlock();
                        if ($count <= 0) {
                            $skipZero = TRUE;
                            break;
                        }
                        $bi = 0;
                    }
                    $datum += (((integer) itextphp_bytes_getIntValue($block, $bi)) & 0xff) << $bits;
                    $bits += 8;
                    $bi++;
                    $count--;
                    continue;
                }

                //  Get the next code.

                $code = $datum & $code_mask;
                $datum >>= $code_size;
                $bits -= $code_size;

                //  Interpret the code

                if (($code > $available) || ($code == $end_of_information))
                    break;
                if ($code == $clear) {
                    //  Reset decoder.
                    $code_size = $data_size + 1;
                    $code_mask = (1 << $code_size) - 1;
                    $available = $clear + 2;
                    $old_code = $NullCode;
                    continue;
                }
                if ($old_code == $NullCode) {
                    itextphp_bytes_write($pixelStack, $top++, $suffix, $code);
                    $old_code = $code;
                    $first = $code;
                    continue;
                }
                $in_code = $code;
                if ($code == $available) {
                    itextphp_bytes_write($pixelStack, $top++, itextphp_bytes_createfromInt($first), 0);
                    $code = $old_code;
                }
                while ($code > $clear) {
                    itextphp_bytes_write($pixelStack, $top++, $suffix, $code);
                    $code = $prefix[$code];
                }
                $first = ((integer) itextphp_bytes_getIntValue($suffix, $code)) & 0xff;

                //  Add a new string to the string table,

                if ($available >= $MaxStackSize)
                    break;
                itextphp_bytes_write($pixelStack, $top++, itextphp_bytes_createfromInt($first), 0);
                $prefix[$available] = $old_code;
                itextphp_bytes_write($suffix, $available, itextphp_bytes_createfromInt($first), 0);
                $available++;
                if ((($available & $code_mask) == 0) && ($available < $MaxStackSize)) {
                    $code_size++;
                    $code_mask += $available;
                }
                $old_code = $in_code;
            }

            //  Pop a pixel off the pixel stack.

            $top--;
            $i++;

            setPixel($xpos, $line, itextphp_bytes_getIntValue($pixelStack, $top));
            ++$xpos;
            if ($xpos >= $iw) {
                $xpos = 0;
                $line += $inc;
                if ($line >= $ih) {
                    if ($interlace == TRUE) {
                        do {
                            $pass++;
                            switch ($pass) {
                                case 2:
                                    $line = 4;
                                    break;
                                case 3:
                                    $line = 2;
                                    $inc = 4;
                                    break;
                                case 4:
                                    $line = 1;
                                    $inc = 2;
                                    break;
                                default: // this shouldn't happen
                                    $line = $ih - 1;
                                    $inc = 0;
                            }
                        } while ($line >= $ih);
                    }
                    else {
                        $line = $ih - 1; // this shouldn't happen
                        $inc = 0;
                    }
                }
            }
        }
        return $skipZero;
    }

    protected function setPixel($x, $y, $v) {
        if ($m_bpc == 8) {
            $pos = $x + $iw * $y;
            itextphp_bytes_write($m_out, $pos, itextphp_bytes_getIntValue($v), 0);
        }
        else {
            $pos = $m_line_stride * $y + $x / (8 / $m_bpc);
            $vout = $v << (8 - $m_bpc * ($x % (8 / $m_bpc))- $m_bpc);
            itextphp_bytes_bitwiseAssign($m_out, $pos, $vout);
        }
    }

    /**
    * Resets frame state for reading next image.
    */
    protected function resetFrame() {
        $transparency = FALSE;
        $delay = 0;
    }

    /**
    * Reads Graphics Control Extension values
    */
    protected function readGraphicControlExt() {
        itextphp_bytes_read($in);    // block size
        $packed = ord(itextphp_bytes_read($in));   // packed fields
        $dispose = ($packed & 0x1c) >> 2;   // disposal method
        if ($dispose == 0)
            $dispose = 1;   // elect to keep old image if discretionary
        $transparency = ($packed & 1) != 0;
        $delay = readShort() * 10;   // delay in milliseconds
        $transIndex = ord(itextphp_bytes_read($in));        // transparent color index
        itextphp_bytes_read($in);                     // block terminator
    }

    /**
    * Skips variable length blocks up to and including
    * next zero length block.
    */
    protected function skip(){
        do {
            readBlock();
        } while ($blockSize > 0);
    }


}


class GifFrame {
        public $image = NULL; //an Image
        public $ix = 0;
        public $iy = 0;
    }

?>