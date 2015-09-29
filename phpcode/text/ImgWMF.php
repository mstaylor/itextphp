<?PHP
/*
 * $Id: ImgWMF.php,v 1.2 2005/10/31 18:54:51 mstaylor Exp $
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


require_once("../exceptions/IOException.php");
require_once("pdf/codec/wmf/InputMeta.php");
require_once("pdf/codec/wmf/MetaDo.php");
require_once("Element.php");
require_once("Image.php");
require_once("BadElementException.php");
require_once("PdfTemplate.php");

/**
* An <CODE>ImgWMF</CODE> is the representation of a windows metafile
* that has to be inserted into the document
*
* @see		Element
* @see		Image
*/

class ImgWMF extends Image implements Element
{

    // Constructors

    public function __construct()
    {
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
                else if (is_resource($arg1) == TRUE)
                    construct1argResource($arg1);
                break;
            }
        }
    }


    construct1argImage(Image $image) {
        parent::__construct($image);
    }


    /**
    * Constructs an <CODE>ImgWMF</CODE>-object, using a <VAR>filename</VAR>.
    *
    * @param filename a <CODE>String</CODE>-representation of the file that contains the image.
    * @throws BadElementException on error
    * @throws MalformedURLException on error
    * @throws IOException on error
    */

    private function construct1argString($filename) {
        parent::__construct($filename);
    }

    /**
    * Constructs an <CODE>ImgWMF</CODE>-object from memory.
    *
    * @param img the memory image
    * @throws BadElementException on error
    * @throws IOException on error
    */

    private function construct1argResource($img) {
        parent::__construct(NULL);
        $rawData = $img;
        $originalData = $img;
        processParameters();
    }

    /**
    * This method checks if the image is a valid WMF and processes some parameters.
    * @throws BadElementException
    * @throws IOException
    */

    private function processParameters() {
        $type = Element::IMGTEMPLATE;
        $originalType = Image::ORIGINAL_WMF;
        $is = NULL;
        try {
            $errorID = NULL;
            if ($rawData == NULL){
                $is = fopen($url);
                $errorID = $url;
            }
            else{
                $is = $rawData;
                $errorID = "Byte array";
            }
            $in = new InputMeta($is);
            if ($in->readInt() != 0x9AC6CDD7)	{
                throw new BadElementException($errorID . " is not a valid placeable windows metafile.");
            }
            $in->readWord();
            $left = $in->readShort();
            $top = $in->readShort();
            $right = $in->readShort();
            $bottom = $in->readShort();
            $inch = $in->readWord();
            $dpiX = 72;
            $dpiY = 72;
            $scaledHeight = (float)($bottom - $top) / $inch * 72.0;
            setTop($scaledHeight);
            $scaledWidth = (float)($right - $left) / $inch * 72.0;
            setRight($scaledWidth);
        }
        catch (Exception $e) {
            if ($is != NULL) {
                fclose($is);
            }
            $plainWidth = width();
            $plainHeight = height();
            return;
        }

        if ($is != NULL) {
                fclose($is);
            }
        $plainWidth = width();
        $plainHeight = height();
    }


    /** Reads the WMF into a template.
    * @param template the template to read to
    * @throws IOException on error
    * @throws DocumentException on error
    */
    public function readWMF(PdfTemplate $template){
        setTemplateData($template);
        $template->setWidth(width());
        $template->setHeight(height());
        $is = NULL;
        try {
            if ($rawData == NULL){
                $is = fopen($url);
            }
            else{
                $is = new java.io.ByteArrayInputStream(rawData);
            }
            $meta = new MetaDo($is, $template);
            $meta->readAll();
        }
        catch (Exception $e) {
            if ($is != NULL) {
                fclose($is);
            }

            return;
        }

        if ($is != NULL) {
                fclose($is);
        }

        return;
    }


}



?>