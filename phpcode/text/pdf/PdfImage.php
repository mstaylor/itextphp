<?PHP
/*
 * $Id: PdfImage.php,v 1.2 2005/11/30 19:04:31 mstaylor Exp $
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
 * REMARK:
 * LZW/GIF is covered by a software patent which is owned by Unisys Corporation.
 * Unisys refuses to license this patent for PDF-related use in software
 * even when this software is released for free and may be freely distributed.
 * HOWEVER:
 * This library doesn't compress or decompress data using the LZW
 * algorithm, nor does it create or visualize GIF-images in any way;
 * it only copies parts of an existing GIF file into a PDF file.
 *
 * More information about the GIF format can be found in the following documents:
 * * GRAPHICS INTERCHANGE FORMAT(sm) Version 89a
 *   (c)1987,1988,1989,1990 Copyright CompuServe Incorporated. Columbus, Ohio
 * * LZW and GIF explained
 *   Steve Blackstock
 * * http://mistress.informatik.unibw-muenchen.de/
 *   very special thanks to klee@informatik.unibw-muenchen.de for the algorithm
 *   to extract the LZW data from a GIF.
 */


require_once("../../exceptions/IOException.php");
require_once("../Image.php");
require_once("PdfStream.php");
require_once("PdfName.php");
require_once("PdfName.php");
require_once("PdfNumber.php");
require_once("PdfBoolean.php");
require_once("PdfIndirectReference.php");
require_once("PdfLiteral.php");
require_once("Element.php");
require_once("PdfDictionary.php");
require_once("BadPdfFormatException.php");



/**
* <CODE>PdfImage</CODE> is a <CODE>PdfStream</CODE> containing an image-<CODE>Dictionary</CODE> and -stream.
*/

class PdfImage extends PdfStream
{

    protected static $TRANSFERSIZE = 4096;
    // membervariables

    /** This is the <CODE>PdfName</CODE> of the image. */
    protected $name = NULL;//PdfName

    // constructor
    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if ($arg1 instanceof Image && is_string($arg2) == TRUE && $arg3 instanceof PdfIndirectReference)
                    construct3args($arg1, $arg2, $arg3);
                break;
            }
        }

    }

    /**
    * Constructs a <CODE>PdfImage</CODE>-object.
    *
    * @param image the <CODE>Image</CODE>-object
    * @param name the <CODE>PdfName</CODE> for this image
    * @throws BadPdfFormatException on error
    */

    private function construct3args(Image $image, $name, PdfIndirectReference $maskRef){
        parent::__construct();
        $this->name = new PdfName($name);
        put(PdfName::$TYPE, PdfName::$XOBJECT);
        put(PdfName::$SUBTYPE, PdfName::$IMAGE);
        put(PdfName::$WIDTH, new PdfNumber($image->width()));
        put(PdfName::$HEIGHT, new PdfNumber($image->height()));
        if ($image->getLayer() != NULL)
            put(PdfName::$OC, $image->getLayer()->getRef());
        if ($image->isMask() == TRUE && ($image->bpc() == 1 || $image->bpc() > 0xff))
            put(PdfName::$IMAGEMASK, PdfBoolean::$PDFTRUE);
        if ($maskRef != NULL) {
            if ($image->isSmask() == TRUE)
                put(PdfName::$SMASK, $maskRef);
            else
                put(PdfName::$MASK, $maskRef);
        }
        if ($image->isMask() == TRUE && $image->isInvertMask() == TRUE)
            put(PdfName::$DECODE, new PdfLiteral("[1 0]"));
        if ($image->isInterpolation() == TRUE)
            put(PdfName::$INTERPOLATE, PdfBoolean::$PDFTRUE);
        $is = NULL;//inputstream
        try {

            // Raw Image data
            if ($image->isImgRaw() == TRUE) {
                // will also have the CCITT parameters
                $colorspace = $image->colorspace();
                $transparency = $image->getTransparency();
                if ($transparency != NULL && $image->isMask() == FALSE && $maskRef == NULL) {
                    $s = "[";
                    for ($k = 0; k < count($transparency); ++$k)
                        $s .= $transparency[$k] . " ";
                    $s .= "]";
                    put(PdfName::$MASK, new PdfLiteral($s));
                }
                $bytes = $image->rawData();
                put(PdfName::$LENGTH, new PdfNumber(itextphp_bytes_getSize($bytes));
                $bpc = $image->bpc();
                if ($bpc > 0xff) {
                    if ($image->isMask() == FALSE)
                        put(PdfName::$COLORSPACE, PdfName::$DEVICEGRAY);
                    put(PdfName::$BITSPERCOMPONENT, new PdfNumber(1));
                    put(PdfName::$FILTER, PdfName::$CCITTFAXDECODE);
                    $k = $bpc - Element::CCITTG3_1D;
                    $decodeparms = new PdfDictionary();
                    if ($k != 0)
                        decodeparms->put(PdfName::$K, new PdfNumber($k));
                    if (($colorspace & Element::CCITT_BLACKIS1) != 0)
                        $decodeparms->put(PdfName::$BLACKIS1, PdfBoolean::$PDFTRUE);
                    if (($colorspace & Element::CCITT_ENCODEDBYTEALIGN) != 0)
                        $decodeparms->put(PdfName::$ENCODEDBYTEALIGN, PdfBoolean::$PDFTRUE);
                    if (($colorspace & Element::CCITT_ENDOFLINE) != 0)
                        $decodeparms->put(PdfName::$ENDOFLINE, PdfBoolean::$PDFTRUE);
                    if (($colorspace & Element::CCITT_ENDOFBLOCK) != 0)
                        $decodeparms->put(PdfName::$ENDOFBLOCK, PdfBoolean::$PDFFALSE);
                    $decodeparms->put(PdfName::$COLUMNS, new PdfNumber($image->width()));
                    $decodeparms.put(PdfName::$ROWS, new PdfNumber($image->height()));
                    put(PdfName::$DECODEPARMS, $decodeparms);
                }
                else {
                    switch($colorspace) {
                        case 1:
                            put(PdfName::$COLORSPACE, PdfName::$DEVICEGRAY);
                            if ($image->isInverted() == TRUE)
                                put(PdfName::$DECODE, new PdfLiteral("[1 0]"));
                            break;
                        case 3:
                            put(PdfName::$COLORSPACE, PdfName::$DEVICERGB);
                            if ($image->isInverted() == TRUE)
                                put(PdfName::$DECODE, new PdfLiteral("[1 0 1 0 1 0]"));
                            break;
                        case 4:
                        default:
                            put(PdfName::$COLORSPACE, PdfName::$DEVICECMYK);
                            if ($image->isInverted() == TRUE)
                                put(PdfName::$DECODE, new PdfLiteral("[1 0 1 0 1 0 1 0]"));
                    }
                    $additional = $image->getAdditional();
                    if ($additional != NULL)
                        putAll($additional);
                    if ($image->isMask() == TRUE && ($image->bpc() == 1 || $image_>bpc() > 8))
                        remove(PdfName::$COLORSPACE);
                    put(PdfName::$BITSPERCOMPONENT, new PdfNumber($image->bpc()));
                    if ($image->isDeflated() == TRUE)
                        put(PdfName::$FILTER, PdfName::$FLATEDECODE);
                    else {
                        flateCompress();
                    }
                }
                return;
            }

            // GIF, JPEG or PNG
            $errorID = NULL;//a string
            if ($image->rawData() == NULL){
                //$is = image.url().openStream();
                $handle = fopen($image->url(), "rb");
                $contents = '';
                while (!feof($handle)) {
                    $contents .= fread($handle, 8192);
                }
                fclose($handle);

                $is = itextphp_bytes_createfromRaw($contents);
                $errorID = $image->url();
            }
            else{
                //this may need to be fixed TO DO
                $is = /*new java.io.ByteArrayInputStream(*/$image->rawData()/*)*/;
                $errorID = "Byte array";
            }
            $i = 0;
            switch($image->type()) {
                case Element::JPEG:
                    put(PdfName::$FILTER, PdfName::$DCTDECODE);
                    switch($image->colorspace()) {
                        case 1:
                            put(PdfName::$COLORSPACE, PdfName::$DEVICEGRAY);
                            break;
                        case 3:
                            put(PdfName::$COLORSPACE, PdfName::$DEVICERGB);
                            break;
                        default:
                            put(PdfName::$COLORSPACE, PdfName::$DEVICECMYK);
                            if ($image->isInverted() == TRUE) {
                                put(PdfName::$DECODE, new PdfLiteral("[1 0 1 0 1 0 1 0]"));
                            }
                    }
                    put(PdfName::$BITSPERCOMPONENT, new PdfNumber(8));
                    if ($image->rawData() != null){
                        $bytes = $image->rawData();
                        put(PdfName::$LENGTH, new PdfNumber(itextphp_bytes_getSize($bytes));
                        return;
                    }
                    $streamBytes = itextphp_bytes_create(itextphp_bytes_getSize($bytes));
                    PdfImage::transferBytes($is, $streamBytes, -1);
                    break;
                default:
                    throw new BadPdfFormatException($errorID . " is an unknown Image format.");
            }
            put(PdfName::$LENGTH, new PdfNumber(itextphp_bytes_getSize($streamBytes)));
        }
        catch(IOException $ioe) {
            throw new BadPdfFormatException($ioe->getMessage());
        }

    }

    /**
    * Returns the <CODE>PdfName</CODE> of the image.
    *
    * @return		the name
    */

    public function name() {
        return $name;
    }

    protected static function transferBytes($in, $out, $len) 
    {
        $buffer = itextphp_bytes_create(PdfImage::$TRANSFERSIZE);
        if ($len < 0)
            $len = 0x7ffffff;
        $size = 0;
        while ($len != 0) {
            $size = itextphp_bytes_readFully($buffer, 0, $in, min($len, PdfImage::$TRANSFERSIZE));
            //$size = in.read(buffer, 0, min($len, PdfImage::$TRANSFERSIZE));
            $size = atoi($size);
            if ($size < 0)
                return;
            itextphp_bytes_append($out, 0, $buffer, $size);
            $len -= $size;
        }
    }


    protected function importAll(PdfImage $dup) {
        $name = $dup->name;
        $compressed = $dup->compressed;
        $streamBytes = $dup->streamBytes;
        $bytes = $dup->bytes;
        $hashMap = $dup->hashMap;
    }






}


?>