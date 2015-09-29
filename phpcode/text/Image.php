<?PHP
/*
 * $Id: Image.php,v 1.6 2005/12/08 22:47:20 mstaylor Exp $
 * $Name:  $
 *
 * Copyright 2005 by Mills W. Staylor, III.
 *
 * The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the License.
//  * You may obtain a copy of the License at http://www.mozilla.org/MPL/
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

require_once("pdf/PdfTemplate.php");
require_once("pdf/RandomAccessFileOrArray.php");
require_once("Annotation.php");
require_once("../util/Properties.php");
require_once("pdf/PdfDictionary.php");
require_once("pdf/PdfOCG.php");
require_once("Image.php");
require_once("pdf/codec/GifImage.php");
require_once("pdf/codec/PngImage.php");
require_once("pdf/codec/TiffImage.php");
require_once("pdf/codec/BmpImage.php");
require_once("pdf/codec/CCITTG4Encoder.php");
require_once("Jpeg.php");
require_once("ImgPostscript.php");
require_once("ImgWMF.php");
require_once("../exceptions/IOException.php");
require_once("ImgTemplate.php");
require_once("BadElementException.php");
require_once("ImgCCITT.php");
require_once("ImgRaw.php");
require_once("ElementTags.php");
require_once("../io/InputStream.php");
require_once("DocumentException.php");
require_once("MarkupAttributes.php");
require_once("Chunk.php");


class Image extends Rectangle implements MarkupAttributes
{

    // static membervariables
    /** this is a kind of image alignment. */
    const aDEFAULT = 0;

    /** this is a kind of image alignment. */
    const RIGHT = 2;

    /** this is a kind of image alignment. */
    const LEFT = 0;

    /** this is a kind of image alignment. */
    const MIDDLE = 1;

    /** this is a kind of image alignment. */
    const TEXTWRAP = 4;

    /** this is a kind of image alignment. */
    const UNDERLYING = 8;

    /** This represents a coordinate in the transformation matrix. */
    const AX = 0;

    /** This represents a coordinate in the transformation matrix. */
    const AY = 1;

    /** This represents a coordinate in the transformation matrix. */
    const BX = 2;

    /** This represents a coordinate in the transformation matrix. */
    const BY = 3;

    /** This represents a coordinate in the transformation matrix. */
    const CX = 4;

    /** This represents a coordinate in the transformation matrix. */
    const CY = 5;

    /** This represents a coordinate in the transformation matrix. */
    const DX = 6;

    /** This represents a coordinate in the transformation matrix. */
    const DY = 7;

    /** type of image */
    const ORIGINAL_NONE = 0;

    /** type of image */
    const ORIGINAL_JPEG = 1;

    /** type of image */
    const ORIGINAL_PNG = 2;

    /** type of image */
    const ORIGINAL_GIF = 3;

    /** type of image */
    const ORIGINAL_BMP = 4;

    /** type of image */
    const ORIGINAL_TIFF = 5;

    /** type of image */
    const ORIGINAL_WMF = 6;

    /** type of image */
    const ORIGINAL_PS = 7;

    /** Image color inversion */
    protected $invert = FALSE;

    /** The imagetype. */
    protected $type;

    /** The URL of the image. */
    protected $url;

    /** The raw data of the image. */
    protected $rawData = NULL;//byte array

    /** The template to be treated as an image. */
    protected $template = array();

    /** The alignment of the Image. */
    protected $alignment;

    /** Text that can be shown instead of the image. */
    protected $alt;

    /** This is the absolute X-position of the image. */
    protected $absoluteX = NAN;

    /** This is the absolute Y-position of the image. */
    protected $absoluteY = NAN;

    /** This is the width of the image without rotation. */
    protected $plainWidth;

    /** This is the width of the image without rotation. */
    protected $plainHeight;

    /** This is the scaled width of the image taking rotation into account. */
    protected $scaledWidth;

    /** This is the original height of the image taking rotation into account. */
    protected $scaledHeight;

    /** This is the rotation of the image. */
    protected $rotation;

    /** this is the colorspace of a jpeg-image. */
    protected $colorspace = -1;

    /**
    * this is the bits per component of the raw image. It also flags a CCITT
    * image.
    */
    protected $bpc = 1;

    /** this is the transparency information of the raw image */
    protected $transparency  = array();

    // for the moment these variables are only used for Images in class Table
    // code contributed by Pelikan Stephan
    /** the indentation to the left. */
    protected $indentationLeft = 0;

    /** the indentation to the right. */
    protected $indentationRight = 0;

    // serial stamping

    protected $mySerialId;

    const serialId = 0;

    /** Holds value of property dpiX. */
    protected $dpiX = 0;

    /** Holds value of property dpiY. */
    protected $dpiY = 0;

    protected $mask = FALSE;

    protected $imageMask;

    /** Holds value of property interpolation. */
    protected $interpolation;

    /** if the annotation is not null the image will be clickable. */
    protected $annotation = NULL;

    /** Contains extra markupAttributes */
    protected $markupAttributes;

    /** ICC Profile attached */
    //Note: To DO...implement the ICC_PROFILE
    protected $profile = NULL;

    /** Holds value of property deflated. */
    protected $deflated = FALSE;

    private $additional = null;

    /** Holds value of property smask. */
    private $smask;

    /** Holds value of property XYRatio. */
    private $XYRatio = 0;

    /** Holds value of property originalType. */
    protected $originalType = ORIGINAL_NONE;

    /** Holds value of property originalData. */
    protected $originalData = NULL;

    /** The spacing before the image. */
    protected $spacingBefore;

    /** The spacing after the image. */
    protected $spacingAfter;

    /**
    * Holds value of property widthPercentage.
    */
    private $widthPercentage = 100;

    protected $layer = NULL;

    public function initializeClassVars()
    {
        $this->$mySerialId = getSerialId();
    }



    static protected function getSerialId() {
        ++$serialId;
        return $serialId;
    }

    // constructors

    public function __construct()
    {
        initializedClassVars();
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Image)
                    construct1argImage($arg1);
                else if (is_string($arg1) == TRUE)
                    construct1argString($arg1);
                break;
            }
        }
    }

    /**
    * Constructs an <CODE>Image</CODE> -object, using an <VAR>url </VAR>.
    * 
    * @param url
    *            the <CODE>URL</CODE> where the image can be found.
    */

    private function construct1argString($url) 
    {
        parent::__construct(0, 0);
        $this->url = url;
        $this->alignment = Image::aDEFAULT;
        $rotation = 0;
    }

    private function construct1argImage(Image $image) {
        parent::__construct($image);
        $this->type = $image->type;
        $this->url = $image->url;
        $this->alignment = $image->alignment;
        $this->alt = $image->alt;
        $this->absoluteX = $image->absoluteX;
        $this->absoluteY = $image->absoluteY;
        $this->plainWidth = $image->plainWidth;
        $this->plainHeight = $image->plainHeight;
        $this->scaledWidth = $image->scaledWidth;
        $this->scaledHeight = $image->scaledHeight;
        $this->rotation = $image->rotation;
        $this->colorspace = $image->colorspace;
        $this->rawData = $image->rawData;
        $this->template = $image->template;
        $this->bpc = $image->bpc;
        $this->transparency = $image->transparency;
        $this->mySerialId = $image->mySerialId;
        $this->invert = $image->invert;
        $this->dpiX = $image->dpiX;
        $this->dpiY = $image->dpiY;
        $this->mask = $image->mask;
        $this->imageMask = $image->imageMask;
        $this->interpolation = $image->interpolation;
        $this->annotation = $image->annotation;
        $this->markupAttributes = $image->markupAttributes;
        $this->profile = $image->profile;
        $this->deflated = $image->deflated;
        $this->additional = $image->additional;
        $this->smask = $image->smask;
        $this->XYRatio = $image->XYRatio;
        $this->originalData = $image->originalData;
        $this->originalType = $image->originalType;
        $this->spacingAfter = $image->spacingAfter;
        $this->spacingBefore = $image->spacingBefore;
        $this->widthPercentage = $image->widthPercentage;
        $this->layer = $image->layer;
    }

    public static function getInstance()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Image)
                    return Image::getInstance1argImage($arg1);
                else if (is_string($arg1) == TRUE)
                    return Image::getInstance1argString($arg1);
                else if (is_resource($arg1) == TRUE)
                    return Image::getInstance1argResource($arg1);
                else if ($arg1 instanceof PdfTemplate)
                    return Image::getInstance1argPdfTemplate($arg1);
                else if ($arg1 instanceof Properties)
                    return Image::getInstance1argProperties($arg1);
                break;
            }
            case 5:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_integer($arg3) == TRUE && is_integer($arg4) == TRUE && is_resource($arg5) == TRUE)
                    return Image::getInstance5args($arg1, $arg2, $arg3, $arg4, $arg5);
                break;
            }
            case 6:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_bool($arg3) == TRUE && is_integer($arg4) == TRUE && is_integer($arg5) == TRUE && (is_resource($arg6) == TRUE || $arg6 == NULL))
                    return Image::getInstance6argsResource($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
                else if(is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_integer($arg3) == TRUE && is_integer($arg4) == TRUE && (is_resource($arg5) == TRUE || $arg5 == NULL) && (is_array($arg6) == TRUE || $arg6 == NULL))
                    return Image::getInstance6argsArray($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
                break;
            }
            case 7:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                $arg7 = func_get_arg(6);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_bool($arg3) == TRUE && is_integer($arg4) == TRUE && is_integer($arg5) == TRUE && (is_resource($arg6) == TRUE || $arg6 == NULL) && (is_array($arg7) == TRUE || $arg7 == NULL))
                    return Image::getInstance7argsArray($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
                break;
            }
        }
    }


    /**
    * gets an instance of an Image
    * 
    * @param image
    *            an Image object
    * @return a new Image object
    */

    private static function getInstance1argImage(Image $image) {
        if ($image == NULL)
            return NULL;
        try {
            //Class cs = image.getClass();
            //Constructor constructor = cs.getDeclaredConstructor(new Class[] { Image.class });
            //return (Image) constructor.newInstance(new Object[] { image });
            return __construct($image);
            } catch (Exception $e) {
              throw new Exception($e);
        }
    }


    /**
    * Gets an instance of an Image.
    *
    * @param url
    *            an URL
    * @return an Image
    * @throws BadElementException
    * @throws MalformedURLException
    * @throws IOException
    */

    private static function  getInstance1argString($url) {
        $is = NULL;
        try {
            $is = fopen($url);
            $c1 = ord(fgetc($is));
            $c2 = ord(fgetc($is));
            $c3 = ord(fgetc($is));
            $c4 = ord(fgetc($is));
            fclose($is);

            $is = NULL;
            if ($c1 == ord('G') && $c2 == ord('I') && $c3 == ord('F')) {
                $gif = new GifImage($url);
                $img = $gif->getImage(1);
                return $img;
            }
            if ($c1 == 0xFF && $c2 == 0xD8) {
                return new Jpeg($url);
            }
            if ($c1 == PngImage::$PNGID[0] && $c2 == PngImage::$PNGID[1] && $c3 == PngImage::$PNGID[2] && $c4 == PngImage::$PNGID[3]) {
                return PngImage::getImage($url);
            }
            if ($c1 == ord('%') && $c2 == ord('!') && $c3 == ord('P') && $c4 == ord('S')) {
                return new ImgPostscript($url);
            }
            if ($c1 == 0xD7 && $c2 == 0xCD) {
                return new ImgWMF($url);
            }
            if ($c1 == ord('B') && $c2 == ord('M')) {
                return  BmpImage::getImage($url);
            }
            if (($c1 == ord('M') && $c2 == ord('M') && $c3 == 0 && $c4 == 42) || ($c1 == ord('I') && $c2 == ord('I') && $c3 == 42 && $c4 == 0)) {
                $ra = NULL;
                try {
                    //if (url.getProtocol().equals("file")) {
                    //    String file = url.getFile();
                    //    ra = new RandomAccessFileOrArray(file);
                    //} else
                        $ra = new RandomAccessFileOrArray($url);
                        $img = TiffImage::getTiffImage($ra, 1);
                        $img->url = $url;
                        return $img;
                    } catch (Exception $e) {
                        if ($ra != NULL)
                        $ra->close();
                        return
                    }

            }
            throw new IOException($url . " is not a recognized imageformat.");
        } catch (Exception $e) {
            if ($is != NULL) {
                fclose($is);
            if ($ra != NULL)
                $ra->close();
            return;
            }
        }

        if ($is != NULL) {
                fclose($is);
            if ($ra != NULL)
                $ra->close();
    }

    /**
    * gets an instance of an Image
    * 
    * @param imgb
    *            raw image date
    * @return an Image object
    * @throws BadElementException
    * @throws MalformedURLException
    * @throws IOException
    */
    private static function getInstance1argResource($imgb) {
        $is = NULL;
        try {
            $is = $imgb;
            $c1 = itextphp_bytes_getIntValue($is, 0);
            $c2 = itextphp_bytes_getIntValue($is, 1);
            $c3 = itextphp_bytes_getIntValue($is, 2);
            $c4 = itextphp_bytes_getIntValue($is, 3);
            //fcloseis.close();

            $is = NULL;
            if ($c1 == ord('G') && $c2 == ord('I') && $c3 == ord('F')) {
                $gif = new GifImage($imgb);
                return $gif->getImage(1);
            }
            if ($c1 == 0xFF && $c2 == 0xD8) {
                return new Jpeg($imgb);
            }
            if ($c1 == PngImage::$PNGID[0] && $c2 == PngImage::$PNGID[1] && $c3 == PngImage::$PNGID[2] && $c4 == PngImage::$PNGID[3]) {
                return PngImage::getImage($imgb);
            }
            if ($c1 == ord('%') && $c2 == ord('!') && $c3 == ord('P') && $c4 == ord('S')) {
                return new ImgPostscript($imgb);
            }
            if ($c1 == 0xD7 && $c2 == 0xCD) {
                return new ImgWMF($imgb);
            }
            if ($c1 == ord('B') && $c2 == ord('M')) {
                return BmpImage::getImage($imgb);
            }
            if (($c1 == ord('M') && $c2 == ord('M') && $c3 == 0 && $c4 == 42) || ($c1 == 'I' && $c2 == ord('I') && $c3 == 42 && $c4 == 0)) {
                $ra = NULL;
                try {
                    $ra = new RandomAccessFileOrArray($imgb);
                    $img = TiffImage::getTiffImage($ra, 1);
                    $img->setOriginalData($imgb);
                    return $img;
                } catch (Exception $e){
                    if ($ra != NULL)
                        $ra->close();
                }

            }
            throw new IOException("The byte array is not a recognized imageformat.");
        } catch (Exception $e) {
            if ($ra != NULL)
                $ra->close();
            return;
            }
        }

        if ($ra != NULL)
            $ra->close();
    }

    /**
    * Gets an instance of an Image in raw mode.
    *
    * @param width
    *            the width of the image in pixels
    * @param height
    *            the height of the image in pixels
    * @param components
    *            1,3 or 4 for GrayScale, RGB and CMYK
    * @param data
    *            the image data
    * @param bpc
    *            bits per component
    * @return an object of type <CODE>ImgRaw</CODE>
    * @throws BadElementException
    *             on error
    */

    private static function getInstance5args($width, $height, $components,$bpc, $data) {
        return Image::getInstance6argsarray($width, $height, $components, $bpc, $data, NULL);
    }

    /**
    * gets an instance of an Image
    *
    * @param template
    *            a PdfTemplate that has to be wrapped in an Image object
    * @return an Image object
    * @throws BadElementException
    */
    private static function getInstance1argPdfTemplate(PdfTemplate $template){
        return new ImgTemplate($template);
    }

    /**
    * Creates an Image with CCITT G3 or G4 compression. It assumes that the
    * data bytes are already compressed.
    *
    * @param width
    *            the exact width of the image
    * @param height
    *            the exact height of the image
    * @param reverseBits
    *            reverses the bits in <code>data</code>. Bit 0 is swapped
    *            with bit 7 and so on
    * @param typeCCITT
    *            the type of compression in <code>data</code>. It can be
    *            CCITTG4, CCITTG31D, CCITTG32D
    * @param parameters
    *            parameters associated with this stream. Possible values are
    *            CCITT_BLACKIS1, CCITT_ENCODEDBYTEALIGN, CCITT_ENDOFLINE and
    *            CCITT_ENDOFBLOCK or a combination of them
    * @param data
    *            the image data
    * @return an Image object
    * @throws BadElementException
    *             on error
    */
    private static function getInstance6argsResource($width, $height, $reverseBits,$typeCCITT, $parameters, $data){
        return Image::getInstance7argsArray($width, $height, $reverseBits, $typeCCITT,$parameters, $data, NULL);
    }


    /**
    * Creates an Image with CCITT G3 or G4 compression. It assumes that the
    * data bytes are already compressed.
    *
    * @param width
    *            the exact width of the image
    * @param height
    *            the exact height of the image
    * @param reverseBits
    *            reverses the bits in <code>data</code>. Bit 0 is swapped
    *            with bit 7 and so on
    * @param typeCCITT
    *            the type of compression in <code>data</code>. It can be
    *            CCITTG4, CCITTG31D, CCITTG32D
    * @param parameters
    *            parameters associated with this stream. Possible values are
    *            CCITT_BLACKIS1, CCITT_ENCODEDBYTEALIGN, CCITT_ENDOFLINE and
    *            CCITT_ENDOFBLOCK or a combination of them
    * @param data
    *            the image data
    * @param transparency
    *            transparency information in the Mask format of the image
    *            dictionary
    * @return an Image object
    * @throws BadElementException
    *             on error
    */
    private static function getInstance7argsArray($width, $height, $reverseBits, $typeCCITT, $parameters, $data, $transparency[])  {
        if ($transparency != NULL && count($transparency) != 2)
            throw new BadElementException("Transparency length must be equal to 2 with CCITT images");
        $img = new ImgCCITT($width, $height, $reverseBits, $typeCCITT,$parameters, $data);
        $img->transparency = $transparency;
        return $img;
    }

    /**
    * Gets an instance of an Image in raw mode.
    *
    * @param width
    *            the width of the image in pixels
    * @param height
    *            the height of the image in pixels
    * @param components
    *            1,3 or 4 for GrayScale, RGB and CMYK
    * @param data
    *            the image data
    * @param bpc
    *            bits per component
    * @param transparency
    *            transparency information in the Mask format of the image
    *            dictionary
    * @return an object of type <CODE>ImgRaw</CODE>
    * @throws BadElementException
    *             on error
    */

    private static function getInstance6argsArray($width, $height, $components, $bpc, $data, $transparency){
        if ($transparency != NULL && count($transparency) != $components * 2)
            throw new BadElementException("Transparency length must be equal to (componentes * 2)");
        if ($components == 1 && $bpc == 1) {
            $g4 = CCITTG4Encoder::compress($data, $width, $height);
            return Image::getInstance7argsArray($width, $height, FALSE, Image::CCITTG4,Element::CCITT_BLACKIS1, $g4, $transparency);
        }
        $img = new ImgRaw($width, $height, $components, $bpc, $data);
        $img->transparency = $transparency;
        return $img;
    }


    /**
    * Returns an <CODE>Image</CODE> that has been constructed taking in
    * account the value of some <VAR>attributes </VAR>.
    *
    * @param attributes
    *            Some attributes
    * @return an <CODE>Image</CODE>
    * @throws BadElementException
    * @throws MalformedURLException
    * @throws IOException
    */

    private static function getInstance1argProperties(Properties $attributes){
        $value = (String) $attributes->remove(ElementTags::URL);
        if ($value == NULL)
            throw new MalformedURLException("The URL of the image is missing.");
        $image = Image::getInstance1argString($value);
        $align = 0;
        if (($value = (String) $attributes->remove(ElementTags::ALIGN)) != NULL) {
            if (strcasecmp(ElementTags::ALIGN_LEFT, $value) == 0)
                $align |= Image::LEFT;
            else if (strcasecmp(ElementTags::ALIGN_RIGHT, $value) == 0)
                $align |= Image::RIGHT;
            else if (strcasecmp(ElementTags::ALIGN_MIDDLE, $value) == 0)
                $align |= Image::MIDDLE;
        }
        if (($value = (String) $attributes->remove(ElementTags::UNDERLYING)) != NULL) {
            if ($value == TRUE)
                $align |= Image::UNDERLYING;
        }
        if (($value = (String) $attributes->remove(ElementTags.TEXTWRAP)) != NULL) {
            if ($value == TRUE)
                $align |= Image::TEXTWRAP;
        }
        $image->setAlignment($align);
        if (($value = (String) $attributes->remove(ElementTags::ALT)) != NULL) {
            $image->setAlt($value);
        }
        $x = NULL;// a string
        $y = NULL;// a string
        if ((($x = (String) $attributes->remove(ElementTags::ABSOLUTEX)) != NULL) && (($y = (String) $attributes->remove(ElementTags::ABSOLUTEY)) != NULL)) {
            $image->setAbsolutePosition((float)$x, (float)$y);
        }
        if (($value = (String) $attributes->remove(ElementTags::PLAINWIDTH)) != NULL) {
            $image->scaleAbsoluteWidth((float)$value);
        }
        if (($value = (String) $attributes->remove(ElementTags::PLAINHEIGHT)) != NULL) {
            $image->scaleAbsoluteHeight((float)$value);
        }
        if (($value = (String) $attributes->remove(ElementTags::ROTATION)) != NULL) {
            $image->setRotation((float)$value);
        }
        if ($attributes->size() > 0)
            $image->setMarkupAttributes($attributes);
        return $image;
    }


    // methods to set information

    /**
    * Sets the alignment for the image.
    *
    * @param alignment
    *            the alignment
    */

    public function setAlignment($alignment) {
        $this->alignment = $alignment;
    }

    /**
    * Sets the alternative information for the image.
    *
    * @param alt
    *            the alternative information
    */

    public function setAlt($alt) {
        $this->alt = $alt;
    }

    /**
    * Sets the absolute position of the <CODE>Image</CODE>.
    * 
    * @param absoluteX
    * @param absoluteY
    */

    public function setAbsolutePosition($absoluteX, $absoluteY) {
        $this->absoluteX = $absoluteX;
        $this->absoluteY = $absoluteY;
    }

    /**
    * Scale the image to an absolute width and an absolute height.
    *
    * @param newWidth
    *            the new width
    * @param newHeight
    *            the new height
    */

    public function scaleAbsolute($newWidth, $newHeight) {
        $plainWidth = $newWidth;
        $plainHeight = $newHeight;
        $amatrix = matrix();
        $scaledWidth = $amatrix[Image::DX] - $amatrix[Image::CX];
        $scaledHeight = $amatrix[Image::DY] - $amatrix[Image::CY];
    }

    /**
    * Scale the image to an absolute width.
    *
    * @param newWidth
    *            the new width
    */

    public function scaleAbsoluteWidth($newWidth) {
        $plainWidth = $newWidth;
        $amatrix = matrix();
        $scaledWidth = $amatrix[Image::DX] - $amatrix[Image::CX];
        $scaledHeight = $amatrix[Image::DY] - $amatrix[Image::CY];
    }

    /**
    * Scale the image to an absolute height.
    *
    * @param newHeight
    *            the new height
    */

    public function scaleAbsoluteHeight($newHeight) {
        $plainHeight = $newHeight;
        $amatrix = matrix();
        $scaledWidth = $amatrix[Image::DX] - $amatrix[Image::CX];
        $scaledHeight = $amatrix[Image::DY] - $amatrix[Image::CY];
    }

    public function scalePercent()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                scalePercent1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                scalePercent2args($arg1, $arg2);
                break;
            }
        }
    }

    /**
    * Scale the image to a certain percentage.
    *
    * @param percent
    *            the scaling percentage
    */

    private function scalePercent($percent) {
        scalePercent2args($percent, $percent);
    }

    /**
    * Scale the width and height of an image to a certain percentage.
    *
    * @param percentX
    *            the scaling percentage of the width
    * @param percentY
    *            the scaling percentage of the height
    */

    private function scalePercent($percentX, $percentY) {
        $plainWidth = (width() * $percentX) / 100.0;
        $plainHeight = (height() * $percentY) / 100.0;
        $amatrix = matrix();
        $scaledWidth = $amatrix[Image::DX] - $amatrix[Image::CX];
        $scaledHeight = $amatrix[Image::DY] - $amatrix[Image::CY];
    }

    /**
    * Scales the image so that it fits a certain width and height.
    *
    * @param fitWidth
    *            the width to fit
    * @param fitHeight
    *            the height to fit
    */

    public function scaleToFit($fitWidth, $fitHeight) {
        $percentX = ($fitWidth * 100) / width();
        $percentY = ($fitHeight * 100) / height();
        scalePercent($percentX < $percentY ? $percentX : $percentY);
    }

    /**
    * Sets the rotation of the image in radians.
    *
    * @param r
    *            rotation in radians
    */

    public function setRotation($r) {
        $d = M_PI; //__IDS__
        $rotation = (float) ($r % (2.0 * $d)); //__IDS__
        if ($rotation < 0) {
            $rotation += 2.0 * d; //__IDS__
        }
        $amatrix = matrix();
        $scaledWidth = $amatrix[Image::DX] - $amatrix[Image::CX];
        $scaledHeight = $amatrix[Image::DY] - $amatrix[Image::CY];
    }

    /**
    * Sets the rotation of the image in degrees.
    *
    * @param deg
    *            rotation in degrees
    */

    public function setRotationDegrees($deg) {
        $d = M_PI; //__IDS__
        setRotation($deg / 180 * (float) $d); //__IDS__
    }

    /**
    * Sets the annotation of this Image.
    *
    * @param annotation
    *            the annotation
    */

    public function setAnnotation(Annotation $annotation) {
        $this->annotation = $annotation;
    }

    /**
    * Gets the annotation.
    *
    * @return the annotation that is linked to this image
    */

    public function annotation() {
        return $annotation;
    }

    // methods to retrieve information

    /**
    * Gets the bpc for the image.
    * <P>
    * Remark: this only makes sense for Images of the type <CODE>RawImage
    * </CODE>.
    *
    * @return a bpc value
    */

    public function bpc() {
        return $bpc;
    }

    /**
    * Gets the raw data for the image.
    * <P>
    * Remark: this only makes sense for Images of the type <CODE>RawImage
    * </CODE>.
    *
    * @return the raw data
    */

    public function rawData() {
        return $rawData;
    }

    /**
    * Gets the template to be used as an image.
    * <P>
    * Remark: this only makes sense for Images of the type <CODE>ImgTemplate
    * </CODE>.
    *
    * @return the template
    */

    public function templateData() {
        return $template[0];
    }

    /**
    * Sets data from a PdfTemplate
    *
    * @param template
    *            the template with the content
    */
    public function setTemplateData(PdfTemplate $template) {
        $this->template[0] = $template;
    }

    /**
    * Checks if the <CODE>Images</CODE> has to be added at an absolute
    * position.
    *
    * @return a boolean
    */

    public function hasAbsolutePosition() {
       return !is_nan($absoluteY);
    }

    /**
    * Checks if the <CODE>Images</CODE> has to be added at an absolute X
    * position.
    *
    * @return a boolean
    */

    public function hasAbsoluteX() {
       return !is_nan($absoluteX);
    }

    /**
    * Returns the absolute X position.
    *
    * @return a position
    */

    public function absoluteX() {
       return $absoluteX;
    }

    /**
    * Returns the absolute Y position.
    *
    * @return a position
    */

    public function absoluteY() {
        return $absoluteY;
    }

    /**
    * Returns the type.
    *
    * @return a type
    */

    public function type() {
        return $type;
    }

    /**
    * Returns <CODE>true</CODE> if the image is a <CODE>Jpeg</CODE>
    * -object.
    *
    * @return a <CODE>boolean</CODE>
    */

    public boolean isJpeg() {
        return $type == Element::JPEG;
    }

    /**
    * Returns <CODE>true</CODE> if the image is a <CODE>ImgRaw</CODE>
    * -object.
    *
    * @return a <CODE>boolean</CODE>
    */

    public boolean isImgRaw() {
       return $type == Element::IMGRAW;
    }

    /**
    * Returns <CODE>true</CODE> if the image is an <CODE>ImgTemplate</CODE>
    * -object
    *
    * @return a <CODE>boolean</CODE>
    */

    public function isImgTemplate() {
        return $type == Element::IMGTEMPLATE;
    }

    /**
    * Gets the alignment for the image.
    *
    * @return a value
    */

    public function alignment() {
        return $alignment;
    }

    /**
    * Gets the alternative text for the image.
    *
    * @return a <CODE>String</CODE>
    */

    public function alt() {
        return $alt;
    }

    /**
    * Gets the scaled width of the image.
    *
    * @return a value
    */

    public function scaledWidth() {
        return $scaledWidth;
    }

    /**
    * Gets the scaled height of the image.
    *
    * @return a value
    */

    public function scaledHeight() {
        return $scaledHeight;
    }

    /**
    * Gets the colorspace for the image.
    * <P>
    * Remark: this only makes sense for Images of the type <CODE>Jpeg</CODE>.
    *
    * @return a colorspace value
    */

    public function colorspace() {
        return $colorspace;
    }

    /**
    * Returns the transformation matrix of the image.
    *
    * @return an array [AX, AY, BX, BY, CX, CY, DX, DY]
    */

    public function matrix() {
        $matrix = array();
        $cosX = (float) cos($rotation);
        $sinX = (float) sin($rotation);
        $matrix[Image::AX] = $plainWidth * $cosX;
        $matrix[Image::AY] = $plainWidth * $sinX;
        $matrix[Image::BX] = (-$plainHeight) * $sinX;
        $matrix[Image::BY] = $plainHeight * $cosX;
        if ($rotation < M_PI / 2f) {
            $matrix[Image::CX] = $matrix[Image::BX];
            $matrix[Image::CY] = 0;
            $matrix[Image::DX] = $matrix[Image::AX];
            $matrix[Image::DY] = $matrix[Image::AY] + $matrix[Image::BY];
        } else if ($rotation < M_PI) {
            $matrix[Image::CX] = $matrix[Image::AX] + $matrix[Image::BX];
            $matrix[Image::CY] = $matrix[Image::BY];
            $matrix[Image::DX] = 0;
            $matrix[Image::DY] = $matrix[Image::AY];
        } else if ($rotation < M_PI * 1.5) {
            $matrix[Image::CX] = $matrix[Image::AX];
            $matrix[Image::CY] = $matrix[Image::AY] + $matrix[Image::BY];
            $matrix[Image::DX] = $matrix[Image::BX];
            $matrix[Image::DY] = 0;
        } else {
            $matrix[Image::CX] = 0;
            $matrix[Image::CY] = $matrix[Image::AY];
            $matrix[Image::DX] = $matrix[Image::AX] + $matrix[Image::BX];
            $matrix[Image::DY] = $matrix[Image::BY];
        }
        return $matrix;
    }


    /**
    * This method is an alternative for the <CODE>InputStream.skip()</CODE>
    * -method that doesn't seem to work properly for big values of <CODE>size
    * </CODE>.
    *
    * @param is
    *            the <CODE>InputStream</CODE>
    * @param size
    *            the number of bytes to skip
    * @throws IOException
    */

    static public function skip(InputStream $is, $size) {
        while ($size > 0) {
            $size -= $is->skip($size);
        }
    }

    /**
    * Returns the transparency.
    *
    * @return the transparency values
    */

    public function getTransparency() {
        return $transparency;
    }

    /**
    * Sets the transparency values
    *
    * @param transparency
    *            the transparency values
    */
    public function setTransparency(array $transparency) {
        $this->transparency = $transparency;
    }

    /**
    * Checks if a given tag corresponds with this object.
    *
    * @param tag
    *            the given tag
    * @return true if the tag corresponds
    */

    public static function isTag($tag) {
        if (strcmp(ElementTags::IMAGE, $tag) == 0)
            return TRUE;
        else
            return FALSE;
    }

    /**
    * Gets the plain width of the image.
    *
    * @return a value
    */

    public function plainWidth() {
        return $plainWidth;
    }

    /**
    * Gets the plain height of the image.
    *
    * @return a value
    */

    public function plainHeight() {
        return $plainHeight;
    }

    /**
    * Returns a serial id for the Image (reuse the same image more than once)
    *
    * @return a serialId
    */
    public function getMySerialId() {
        return $mySerialId;
    }

    /**
    * Gets the dots-per-inch in the X direction. Returns 0 if not available.
    *
    * @return the dots-per-inch in the X direction
    */
    public function getDpiX() {
        return $dpiX;
    }

    /**
    * Gets the dots-per-inch in the Y direction. Returns 0 if not available.
    *
    * @return the dots-per-inch in the Y direction
    */
    public function getDpiY() {
        return $dpiY;
    }

    /**
    * Sets the dots per inch value
    *
    * @param dpiX
    *            dpi for x coordinates
    * @param dpiY
    *            dpi for y coordinates
    */
    public function setDpi($dpiX, $dpiY) {
        $this->dpiX = $dpiX;
        $this->dpiY = $dpiY;
    }

    /**
    * Returns <CODE>true</CODE> if this <CODE>Image</CODE> has the
    * requisites to be a mask.
    *
    * @return <CODE>true</CODE> if this <CODE>Image</CODE> can be a mask
    */
    public function isMaskCandidate() {
        if ($type == Element::IMGRAW) {
            if ($bpc > 0xff)
                return TRUE;
        }
        return $colorspace == 1;
    }

    /**
    * Make this <CODE>Image</CODE> a mask.
    *
    * @throws DocumentException
    *             if this <CODE>Image</CODE> can not be a mask
    */
    public function makeMask() {
        if (isMaskCandidate() == FALSE)
            throw new DocumentException("This image can not be an image mask.");
        $mask = TRUE;
    }


    /**
    * Sets the explicit masking.
    *
    * @param mask
    *            the mask to be applied
    * @throws DocumentException
    *             on error
    */
    public function setImageMask(Image $mask) {
        if ($this->mask == TRUE)
            throw new DocumentException("An image mask cannot contain another image mask.");
        if ($mask->mask == FALSE)
            throw new DocumentException("The image mask is not a mask. Did you do makeMask()?");
        $imageMask = $mask;
        $smask = ($mask->bpc > 1 && $mask->bpc <= 8);
    }

    /**
    * Gets the explicit masking.
    *
    * @return the explicit masking
    */
    public function getImageMask() {
        return $imageMask;
    }

    /**
    * Returns <CODE>true</CODE> if this <CODE>Image</CODE> is a mask.
    *
    * @return <CODE>true</CODE> if this <CODE>Image</CODE> is a mask
    */
    public function isMask() {
        return $mask;
    }

    /**
    * Inverts the meaning of the bits of a mask.
    *
    * @param invert
    *            <CODE>true</CODE> to invert the meaning of the bits of a
    *            mask
    */
    public function setInvertMask($invert) {
        $this->invert = $invert;
    }

    /**
    * Returns <CODE>true</CODE> if the bits are to be inverted in the mask.
    *
    * @return <CODE>true</CODE> if the bits are to be inverted in the mask
    */
    public function isInvertMask() {
        return $invert;
    }

    /**
    * Getter for the inverted value
    *
    * @return true if the image is inverted
    */
    public function isInverted() {
        return $invert;
    }

    /**
    * Sets inverted true or false
    *
    * @param invert
    *            true or false
    */
    public function setInverted($invert) {
        $this->invert = $invert;
    }

    /**
    * Getter for property interpolation.
    *
    * @return Value of property interpolation.
    */
    public function isInterpolation() {
        return $interpolation;
    }

    /**
    * Sets the image interpolation. Image interpolation attempts to produce a
    * smooth transition between adjacent sample values.
    *
    * @param interpolation
    *            New value of property interpolation.
    */
    public function setInterpolation($interpolation) {
        $this->interpolation = $interpolation;
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#setMarkupAttribute(java.lang.String,
    *      java.lang.String)
    */
    public function setMarkupAttribute($name, $value) {
        $markupAttributes = ($markupAttributes == NULL) ? new Properties() : $markupAttributes;
        $markupAttributes->setProperty($name, $value);
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#setMarkupAttributes(java.util.Properties)
    */
    public function setMarkupAttributes(Properties $markupAttributes) {
        $this->markupAttributes = $markupAttributes;
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#getMarkupAttribute(java.lang.String)
    */
    public function getMarkupAttribute($name) {
        return ($markupAttributes == NULL) ? NULL : (String)$markupAttributes->getProperty($name);
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#getMarkupAttributeNames()
    */
    public function getMarkupAttributeNames() {
        return Chunk::getKeySet($markupAttributes);
    }

    /**
    * @see com.lowagie.text.MarkupAttributes#getMarkupAttributes()
    */
    public Properties getMarkupAttributes() {
        return markupAttributes;
    }

    /**
    * Tags this image with an ICC profile.
    *
    * @param profile
    *            the profile
    */
    /**
    *  TO DO: Implement this
    **/
    /**public void tagICC(ICC_Profile profile) {
        this.profile = profile;
    }**/

    /**
    * Checks is the image has an ICC profile.
    *
    * @return the ICC profile or <CODE>null</CODE>
    */
    public function hasICCProfile() {
        return ($this->profile != NULL);
    }

    /**
    * Gets the images ICC profile.
    *
    * @return the ICC profile
    */
    public function getICCProfile() {
        return $profile;
    }

    /**
    * Getter for property deflated.
    *
    * @return Value of property deflated.
    *
    */
    public function isDeflated() {
        return $this->deflated;
    }

    /**
    * Setter for property deflated.
    *
    * @param deflated
    *            New value of property deflated.
    *
    */
    public function setDeflated($deflated) {
        $this->deflated = $deflated;
    }

    /**
    * Getter for property indexed.
    *
    * @return Value of property indexed.
    *
    */
    public function getAdditional() {
        return $this->additional;
    }

    /**
    * Sets the /Colorspace key.
    *
    * @param additional
    *            New value of property indexed.
    */
    public function setAdditional(PdfDictionary $additional) {
        $this->additional = $additional;
    }

    /**
    * Getter for property smask.
    *
    * @return Value of property smask.
    *
    */
    public function isSmask() {
        return $this->smask;
    }

    /**
    * Setter for property smask.
    *
    * @param smask
    *            New value of property smask.
    *
    */
    public function setSmask($smask) {
        $this->smask = $smask;
    }

    /**
    * Gets the X/Y pixel dimensionless aspect ratio.
    *
    * @return the X/Y pixel dimensionless aspect ratio
    */
    public function getXYRatio() {
        return $this->XYRatio;
    }

    /**
    * Sets the X/Y pixel dimensionless aspect ratio.
    *
    * @param XYRatio
    *            the X/Y pixel dimensionless aspect ratio
    */
    public function setXYRatio($XYRatio) {
        $this->XYRatio = $XYRatio;
    }

    /**
    * Gets the left indentation.
    *
    * @return the left indentation
    */
    public function indentationLeft() {
        return $indentationLeft;
    }

    /**
    * Gets the right indentation.
    *
    * @return the right indentation
    */
    public function indentationRight() {
        return $indentationRight;
    }

    /**
    * Sets the left indentation.
    *
    * @param f
    */
    public function setIndentationLeft($f) {
        $indentationLeft = $f;
    }

    /**
    * Sets the right indentation.
    *
    * @param f
    */
    public function setIndentationRight($f) {
        $indentationRight = $f;
    }

    /**
    * Getter for property originalType.
    *
    * @return Value of property originalType.
    *
    */
    public function getOriginalType() {
        return $this->originalType;
    }

    /**
    * Setter for property originalType.
    *
    * @param originalType
    *            New value of property originalType.
    *
    */
    public function setOriginalType($originalType) {
        $this->originalType = $originalType;
    }

    /**
    * Getter for property originalData.
    *
    * @return Value of property originalData.
    *
    */
    public function getOriginalData() {
        return $this->originalData;
    }

    /**
    * Setter for property originalData.
    *
    * @param originalData
    *            New value of property originalData.
    *
    */
    public function setOriginalData($originalData) {
        $this->originalData = $originalData;
    }

    /**
    * Sets the url of the image
    *
    * @param url
    *            the url of the image
    */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
    * Sets the spacing before this image.
    *
    * @param spacing
    *            the new spacing
    */

    public function setSpacingBefore($spacing) {
        $this->spacingBefore = $spacing;
    }

    /**
    * Sets the spacing after this image.
    *
    * @param spacing
    *            the new spacing
    */

    public function setSpacingAfter($spacing) {
       $this->spacingAfter = $spacing;
    }

    /**
    * Gets the spacing before this image.
    *
    * @return the spacing
    */

    public function spacingBefore() {
        return $spacingBefore;
    }

    /**
    * Gets the spacing before this image.
    *
    * @return the spacing
    */

    public function spacingAfter() {
        return $spacingAfter;
    }

    /**
    * Getter for property widthPercentage.
    *
    * @return Value of property widthPercentage.
    */
    public function getWidthPercentage() {
        return $this->widthPercentage;
    }

    /**
    * Setter for property widthPercentage.
    *
    * @param widthPercentage
    *            New value of property widthPercentage.
    */
    public function setWidthPercentage($widthPercentage) {
        $this->widthPercentage = $widthPercentage;
    }

    /**
    * Gets the layer this image belongs to.
    *
    * @return the layer this image belongs to or <code>null</code> for no
    *         layer defined
    */
    public function getLayer() {
        return $layer;
    }

    /**
    * Sets the layer this image belongs to.
    *
    * @param layer
    *            the layer this image belongs to
    */
    public function setLayer(PdfOCG $layer) {
        $this->layer = $layer;
    }


    /**
    * Gets the <CODE>String</CODE> -representation of the reference to the
    * image.
    * 
    * @return a <CODE>String</CODE>
    */

    public function url() {
        return $url;
    }



}






?>