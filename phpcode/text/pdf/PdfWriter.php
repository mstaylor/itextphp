<?php /*
 * $Id: PdfWriter.php,v 1.4 2005/12/08 22:48:00 mstaylor Exp $
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

require_once("../../awt/Color.php");
require_once("../../exceptions/IOException.php");
require_once("../../exceptions/IllegalArgumentException.php");
require_once("../../exceptions/IndexOutOfBoundsException.php");
require_once("../../io/OutputStream.php");
require_once("../DocListener.php");
require_once("../DocWriter.php");
require_once("../Document.php");
require_once("../DocumentException.php");
require_once("../Image.php");
require_once("../ImgWMF.php");
require_once("../Rectangle.php");
require_once("../Table.php");
require_once("../ImgPostscript.php");

require_once("ByteBuffer.php");
require_once("PdfObject.php");
require_once("PdfEncryption.php");
require_once("PdfStream.php");
require_once("PdfName.php");
require_once("PdfNumber.php");
require_once("PdfIndirectObject.php");
require_once("PdfIndirectReference.php");
require_once("PdfArray.php");
require_once("PdfDictionary.php");
require_once("PdfPages.php");
require_once("PdfContentByte.php");
require_once("ColorDetails.php");
require_once("PdfOCProperties.php");
require_once("PdfDocument.php");
require_once("PdfPageEvent.php");
require_once("PdfReaderInstance.php");
require_once("PdfContents.php");
require_once("PdfPage.php");
require_once("PdfException.php");
require_once("PdfImage.php");
require_once("PdfICCBased.php");
require_once("PdfLayer.php");
require_once("PdfLayerMembership.php");
require_once("PdfString.php");
require_once("FontDetails.php");
require_once("PdfTemplate.php");
require_once("PdfOCG.php");
require_once("PdfPTable.php");
require_once("PdfAcroForm.php");
require_once("PdfOutline.php");
require_once("OutputStreamCounter.php");
require_once("BaseFont.php");
require_once("DocumentFont.php");
require_once("ExtendedColor.php");
require_once("PdfShadingPattern.php");
require_once("PdfShading.php");
require_once("PdfPatternPainter.php");
require_once("PdfDestination.php");
require_once("PdfAction.php");
require_once("PdfPageLabels.php");
require_once("PdfReader.php");
require_once("PdfImportedPage.php");
require_once("PdfAnnotation.php");
require_once("PdfTransition.php");
require_once("PdfXConformanceException.php");
require_once("SpotColor.php");
require_once("ShadingColor.php");
require_once("PatternColor.php");
require_once("PdfGState.php");
require_once("PRStream.php");

/**
* A <CODE>DocWriter</CODE> class for PDF.
* <P>
* When this <CODE>PdfWriter</CODE> is added
* to a certain <CODE>PdfDocument</CODE>, the PDF representation of every Element
* added to this Document will be written to the outputstream.</P>
*/


class PdfWriter extends DocWriter
{


    // static membervariables

    /** A viewer preference */
    const PageLayoutSinglePage = 1;
    /** A viewer preference */
    const PageLayoutOneColumn = 2;
    /** A viewer preference */
    const PageLayoutTwoColumnLeft = 4;
    /** A viewer preference */
    const PageLayoutTwoColumnRight = 8;

    /** A viewer preference */
    const PageModeUseNone = 16;
    /** A viewer preference */
    const PageModeUseOutlines = 32;
    /** A viewer preference */
    const PageModeUseThumbs = 64;
    /** A viewer preference */
    const PageModeFullScreen = 128;
    /** A viewer preference */
    const PageModeUseOC = 1 << 20;

    /** A viewer preference */
    const HideToolbar = 256;
    /** A viewer preference */
    const HideMenubar = 512;
    /** A viewer preference */
    const HideWindowUI = 1024;
    /** A viewer preference */
    const FitWindow = 2048;
    /** A viewer preference */
    const CenterWindow = 4096;

    /** A viewer preference */
    const NonFullScreenPageModeUseNone = 8192;
    /** A viewer preference */
    const NonFullScreenPageModeUseOutlines = 16384;
    /** A viewer preference */
    const NonFullScreenPageModeUseThumbs = 32768;
    /** A viewer preference */
    const NonFullScreenPageModeUseOC = 1 << 19;

    /** A viewer preference */
    const DirectionL2R = 1 << 16;
    /** A viewer preference */
    const DirectionR2L = 1 << 17;
    /** A viewer preference */
    const DisplayDocTitle = 1 << 18;
    /** The mask to decide if a ViewerPreferences dictionary is needed */
    const ViewerPreferencesMask = 0xfff00;
    /** The operation permitted when the document is opened with the user password */
    const AllowPrinting = 4 + 2048;
    /** The operation permitted when the document is opened with the user password */
    const AllowModifyContents = 8;
    /** The operation permitted when the document is opened with the user password */
    const AllowCopy = 16;
    /** The operation permitted when the document is opened with the user password */
    const AllowModifyAnnotations = 32;
    /** The operation permitted when the document is opened with the user password */
    const AllowFillIn = 256;
    /** The operation permitted when the document is opened with the user password */
    const AllowScreenReaders = 512;
    /** The operation permitted when the document is opened with the user password */
    const AllowAssembly = 1024;
    /** The operation permitted when the document is opened with the user password */
    const AllowDegradedPrinting = 4;
    /** Type of encryption */
    const STRENGTH40BITS = FALSE;
    /** Type of encryption */
    const STRENGTH128BITS = TRUE;
    /** action value */
    public static $DOCUMENT_CLOSE = NULL;
    /** action value */
    public static $WILL_SAVE = NULL;
    /** action value */
    public static $DID_SAVE = NULL;
    /** action value */
    public static $WILL_PRINT = NULL;
    /** action value */
    public static $DID_PRINT = NULL;
    /** action value */
    public static $PAGE_OPEN = NULL;
    /** action value */
    public static $PAGE_CLOSE = NULL;

    /** signature value */
    const SIGNATURE_EXISTS = 1;
    /** signature value */
    const SIGNATURE_APPEND_ONLY = 2;

    /** possible PDF version */
    const VERSION_1_2 = '2';
    /** possible PDF version */
    const VERSION_1_3 = '3';
    /** possible PDF version */
    const VERSION_1_4 = '4';
    /** possible PDF version */
    const VERSION_1_5 = '5';
    /** possible PDF version */
    const VERSION_1_6 = '6';

    const int VPOINT = 7;
    /** this is the header of a PDF document */
    protected $HEADER = NULL;

    protected $prevxref = 0;

    protected $root = NULL;

    /** Dictionary, containing all the images of the PDF document */
    protected $imageDictionary = NULL;

    /** This is the list with all the images in the document. */
    private $images = array();

    /** The form XObjects in this document. The key is the xref and the value
        is Object[]{PdfName, template}.*/
    protected $formXObjects = array();

    /** The name counter for the form XObjects name. */
    protected $formXObjectsCounter = 1;

    /** The font number counter for the fonts in the document. */
    protected $fontNumber = 1;

    /** The color number counter for the colors in the document. */
    protected $colorNumber = 1;

    /** The patten number counter for the colors in the document. */
    protected $patternNumber = 1;

    /** The direct content in this document. */
    protected $directContent = NULL;

    /** The direct content under in this document. */
    protected $directContentUnder = NULL;

    /** The fonts of this document */
    protected $documentFonts = array();

    /** The colors of this document */
    protected $documentColors = array();

    /** The patterns of this document */
    protected $documentPatterns = array();

    protected $documentShadings = array();

    protected $documentShadingPatterns = array();

    protected $patternColorspaceRGB = NULL;
    protected $patternColorspaceGRAY = NULL;
    protected $patternColorspaceCMYK = NULL;
    protected $documentSpotPatterns = array();

    protected $documentExtGState = array();

    protected $documentLayers = array();
    protected $documentOCG = array();
    protected $documentOCGorder = array();
    protected $OCProperties = NULL;
    protected $OCGRadioGroup = NULL;

    protected $defaultColorspace = NULL;

    /** PDF/X value */
    const PDFXNONE = 0;
    /** PDF/X value */
    const PDFX1A2001 = 1;
    /** PDF/X value */
    const PDFX32002 = 2;

    private $pdfxConformance = PdfWriter::PDFXNONE;

    const PDFXKEY_COLOR = 1;
    const PDFXKEY_CMYK = 2;
    const PDFXKEY_RGB = 3;
    const PDFXKEY_FONT = 4;
    const PDFXKEY_IMAGE = 5;
    const PDFXKEY_GSTATE = 6;
    const PDFXKEY_LAYER = 7;

    // membervariables

    /** body of the PDF document */
    protected $body = NULL;

    /** the pdfdocument object. */
    protected $pdf = NULL;

    /** The <CODE>PdfPageEvent</CODE> for this document. */
    private $pageEvent = NULL;

    protected $crypto = NULL;

    protected $importedPages = array();

    protected $currentPdfReaderInstance = NULL;

    /** The PdfIndirectReference to the pages. */
    protected $pageReferences = array();

    protected $currentPageNumber = 1;

    protected $group = NULL;

    /** The default space-char ratio. */    
    const SPACE_CHAR_RATIO_DEFAULT = 2.5;
    /** Disable the inter-character spacing. */    
    const NO_SPACE_CHAR_RATIO = 10000000;

    /** Use the default run direction. */    
    const RUN_DIRECTION_DEFAULT = 0;
    /** Do not use bidirectional reordering. */    
    const RUN_DIRECTION_NO_BIDI = 1;
    /** Use bidirectional reordering with left-to-right
     * preferential run direction.
     */
    const RUN_DIRECTION_LTR = 2;
    /** Use bidirectional reordering with right-to-left
     * preferential run direction.
     */
    const RUN_DIRECTION_RTL = 3;
    protected $runDirection = PdfWriter::RUN_DIRECTION_NO_BIDI;
    /**
    * The ratio between the extra word spacing and the extra character spacing.
    * Extra word spacing will grow <CODE>ratio</CODE> times more than extra character spacing.
    */
    private $spaceCharRatio = PdfWriter::SPACE_CHAR_RATIO_DEFAULT;

    /** Holds value of property extraCatalog. */
    private $extraCatalog = NULL;

    /**
    * Holds value of property fullCompression.
    */
    protected $fullCompression = FALSE;

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfWriter::$initialized == FALSE)
        {
            PdfWriter::$DOCUMENT_CLOSE = PdfName::$WC;
            PdfWriter::$WILL_SAVE = PdfName::$WS;
            PdfWriter::$DID_SAVE = PdfName::$DS;
            PdfWriter::$WILL_PRINT = PdfName::$WP;
            PdfWriter::$DID_PRINT = PdfName::$DP;
            PdfWriter::$PAGE_OPEN = PdfName::$O;
            PdfWriter::$PAGE_CLOSE = PdfName::$C;
            PdfWriter::$initialized = TRUE;
        }
    }

    private function onConstruct()
    {
        $HEADER = DocWriter::getISOBytes("%PDF-1.4\n%\u00e2\u00e3\u00cf\u00d3\n");
        $root = new PdfPages($this);
        $imageDictionary = new PdfDictionary();
        $OCGRadioGroup = new PdfArray();
        $defaultColorspace = new PdfDictionary();
    }

    public function __construct()
    {
        onConstruct();
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0:
            {
                construct0args();
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                construct2args($arg1, $arg2);
                break;
            }
        }
    }
    // constructor

    private function construct0args()
    {
    }

    /**
    * Constructs a <CODE>PdfWriter</CODE>.
    * <P>
    * Remark: a PdfWriter can only be constructed by calling the method
    * <CODE>getInstance(Document document, OutputStream os)</CODE>.
    *
    * @param	document	The <CODE>PdfDocument</CODE> that has to be written
    * @param	os			The <CODE>OutputStream</CODE> the writer has to write to.
    */
    private function construct2args(PdfDocument $document, OutputStream $os)
    {
        parent::__construct($document, $os);
        $pdf = $document;
        $directContent = new PdfContentByte($this);
        $directContentUnder = new PdfContentByte($this);
    }

    // get an instance of the PdfWriter

    public static function getInstance()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return getInstance2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                return getInstance3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    private static function getInstance2args(Document $document, OutputStream $os)
    {
        $pdf = new PdfDocument();
        $document->addDocListener($pdf);
        $writer = new PdfWriter($pdf, $os);
        $pdf->addWriter($writer);
        return $writer;
    }

    private static function getInstance3args(Document $document, OutputStream $os, DocListener $listener)
    {
        $pdf = new PdfDocument();
        $pdf->addDocListener($listener);
        $document->addDocListener($pdf);
        $writer = new PdfWriter($pdf, $os);
        $pdf->addWriter($writer);
        return $writer;
    }

    // methods to write objects to the outputstream

    function add()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return add2args($arg1, $arg2);
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof PdfImage)
                   return add1argPdfImage($arg1);
                else if ($arg1 instanceof PdfICCBased)
                   return add1argPdfICCBased($arg1);
                break;
            }
        }
    }

    private function add2args(PdfPage $page, PdfContents $contents)
    {
        if ($open == FALSE) {
            throw new PdfException("The document isn't open.");
        }
        $object = NULL;
        try {
            $object = addToBody($contents);
        }
        catch(IOException $ioe) {
            throw new Exception($ioe);
        }
        $page->add($object->getIndirectReference());
        if ($group != NULL) {
            $page->put(PdfName::$GROUP, $group);
            $group = NULL;
        }
        $root->addPage($page);
        $currentPageNumber++;
        return NULL;
    }

    /** Adds an image to the document but not to the page resources. It is used with
    * templates and <CODE>Document.add(Image)</CODE>.
    * @param image the <CODE>Image</CODE> to add
    * @return the name of the image added
    * @throws PdfException on error
    * @throws DocumentException on error
    */
    function addDirectImageSimple(Image $image){
        $name = NULL;
        // if the images is already added, just retrieve the name
        if (array_key_exists($image->getMySerialId(), $images) == TRUE) {
            $name = $images[$image->getMySerialId()];
        }
        // if it's a new image, add it to the document
        else {
            if ($image->isImgTemplate() == TRUE) {
                $name = new PdfName("img" . count($images));
                if ($image->templateData() == null) {
                    if($image instanceof ImgWMF){
                        try {
                            $wmf = ($image;
                            $wmf->readWMF(getDirectContent()->createTemplate(0, 0));
                        }
                        catch (Exception $e) {
                            throw new DocumentException($e);
                        }
                    }else{
                        try {
                            image->readPostscript(getDirectContent()->createTemplate(0, 0));
                        }
                        catch (Exception $e) {
                            throw new DocumentException($e);
                        }

                    }
                }
            }
            else {
                $maskImage = $image->getImageMask();
                $maskRef = NULL;
                if ($maskImage != NULL) {
                    $mname = $images[$maskImage->getMySerialId()];
                    $maskRef = getImageReference($mname);
                }
                $i = new PdfImage($image, "img" . count($images), $maskRef);
                if ($image->hasICCProfile() == TRUE) {
                    $icc = new PdfICCBased($image->getICCProfile());
                    $iccRef = add($icc);
                    $iccArray = new PdfArray();
                    $iccArray->add(PdfName::$ICCBASED);
                    $iccArray->add($iccRef);
                    $colorspace = $i->get(PdfName::$COLORSPACE);
                    if ($colorspace != NULL && $colorspace->isArray() == TRUE) {
                        $ar = ($colorspace)->getArrayList();
                        if (count($ar) > 1 && PdfName::$INDEXED->equals($ar[0]) == TRUE)
                            $ar[1] = $iccArray;
                        else
                            $i->put(PdfName::$COLORSPACE, $iccArray);
                    }
                    else
                        $i->put(PdfName::$COLORSPACE, $iccArray);
                }
                add($i);
                $name = $i->name();
            }
            $images[$image->getMySerialId()] = $name;
        }
        return $name;
    }

    /**
    * Writes a <CODE>PdfImage</CODE> to the outputstream.
    *
    * @param pdfImage the image to be added
    * @return a <CODE>PdfIndirectReference</CODE> to the encapsulated image
    * @throws PdfException when a document isn't open yet, or has been closed
    */
    private static function add1argPdfImage(PdfImage $pdfImage)
    {
        if ($imageDictionary->contains($pdfImage->name()) == FALSE) {
            PdfWriter::checkPDFXConformance($this, PdfWriter::PDFXKEY_IMAGE, $pdfImage);
            $object = NULL;
            try {
                $object = addToBody($pdfImage);
            }
            catch(IOException $ioe) {
                throw new Exception($ioe);
            }
            $imageDictionary->put($pdfImage->name(), $object->getIndirectReference());
            return $object->getIndirectReference();
        }
        return $imageDictionary->get($pdfImage->name());
    }


    private static function add1argPdfICCBased(PdfICCBased $icc) {
        $object = NULL;
        try {
            $object = addToBody($icc);
        }
        catch(IOException $ioe) {
            throw new Exception($ioe);
        }
        return $object->getIndirectReference();
    }


    /**
    * return the <CODE>PdfIndirectReference</CODE> to the image with a given name.
    *
    * @param name the name of the image
    * @return a <CODE>PdfIndirectReference</CODE>
    */

    function getImageReference(PdfName $name) {
        return $imageDictionary->get($name);
    }

    // methods to open and close the writer

    /**
    * Signals that the <CODE>Document</CODE> has been opened and that
    * <CODE>Elements</CODE> can be added.
    * <P>
    * When this method is called, the PDF-document header is
    * written to the outputstream.
    */

    public function open() {
        parent::open();
        try {
            $os->write($HEADER);
            $body = new PdfBody($this);
            if ($pdfxConformance == PdfWriter::PDFX32002) {
                $sec = new PdfDictionary();
                $sec->put(PdfName::$GAMMA, new PdfArray(array(2.2,2.2,2.2)));
                $sec->put(PdfName::$MATRIX, new PdfArray(array(0.4124,0.2126,0.0193,0.3576,0.7152,0.1192,0.1805,0.0722,0.9505)));
                $sec->put(PdfName::$WHITEPOINT, new PdfArray(array(0.9505,1.0,1.089)));
                $arr = new PdfArray(PdfName::$CALRGB);
                $arr->add($sec);
                setDefaultColorspace(PdfName.DEFAULTRGB, addToBody($arr)->getIndirectReference());
            }
        }
        catch(IOException $ioe) {
            throw new Exception($ioe);
        }
    }

    private static function getOCGOrder(PdfArray $order, PdfLayer $layer) {
        if ($layer->isOnPanel() == FALSE)
            return;
        if ($layer->getTitle() == NULL)
            $order->add($layer->getRef());
        $children = $layer->getChildren();
        if ($children == NULL)
            return;
        $kids = new PdfArray();
        if ($layer->getTitle() != NULL)
            $kids->add(new PdfString($layer->getTitle(), PdfObject::TEXT_UNICODE));
        for ($k = 0; $k < count($children); ++$k) {
            getOCGOrder($kids, $children[$k]);
        }
        if ($kids->size() > 0)
            $order->add($kids);
    }

    private function addASEvent(PdfName $event, PdfName $category) {
        $arr = new PdfArray();

        foreach ($documentOCG as &$layer) {
            $usage = $layer->get(PdfName::$USAGE);
            if ($usage != NULL && $usage->get($category) != NULL)
                $arr->add($layer->getRef());
        }
        if ($arr->size() == 0)
            return;
        $d = $OCProperties->get(PdfName::$D);
        $arras = $d->get(PdfName::$AS);
        if ($arras == NULL) {
            $arras = new PdfArray();
            $d->put(PdfName::$AS, $arras);
        }
        $as = new PdfDictionary();
        $as.put(PdfName::$EVENT, $event);
        $as.put(PdfName::$CATEGORY, new PdfArray($category));
        $as.put(PdfName::$OCGS, $arr);
        $arras->add($as);
    }


    private function fillOCProperties($erase) {
        if ($OCProperties == NULL)
            $OCProperties = new PdfOCProperties();
        if ($erase == TRUE) {
            $OCProperties->remove(PdfName::$OCGS);
            $OCProperties->remove(PdfName::$D);
        }
        if ($OCProperties->get(PdfName::$OCGS) == NULL) {
            $gr = new PdfArray();
            foreach ($documentOCG as &$layer) {
                $gr->add($layer->getRef());
            }
            $OCProperties->put(PdfName::$OCGS, $gr);
        }
        if ($OCProperties->get(PdfName::$D) != NULL)
            return;
        $docOrder = array_merge(array(), $documentOCGorder);
        $count = 0;
        foreach ($docOrder as &$layer) {
            if ($layer->getParent() != NULL)
                unset($docOrder[$count]);
            $count++;
        }
        $order = new PdfArray();
        foreach ($docOrder as &$layer) {
            PdfWriter::getOCGOrder($order, $layer);
        }
        $d = new PdfDictionary();
        $OCProperties->put(PdfName::$D, $d);
        $d->put(PdfName::$ORDER, $order);
        $gr = new PdfArray();
        foreach ($documentOCG as &$layer) {
            if ($layer->isOn() == FALSE)
                $gr->add($layer->getRef());
        }
        if ($gr->size() > 0)
            $d->put(PdfName::$OFF, $gr);
        if ($OCGRadioGroup->size() > 0)
            $d->put(PdfName::$RBGROUPS, $OCGRadioGroup);
        addASEvent(PdfName::$VIEW, PdfName::$ZOOM);
        addASEvent(PdfName::$VIEW, PdfName::$VIEW);
        addASEvent(PdfName::$PRINT, PdfName::$PRINT);
        addASEvent(PdfName::$EXPORT, PdfName::$EXPORT);
        $d->put(PdfName::$LISTMODE, PdfName::$VISIBLEPAGES);
    }

    protected function getCatalog(PdfIndirectReference $rootObj)
    {
        $catalog = ($document)->getCatalog($rootObj);
        if ($documentOCG->size() == 0)
            return $catalog;
        fillOCProperties(FALSE);
        $catalog->put(PdfName::$OCPROPERTIES, $OCProperties);
        return $catalog;
    }

    protected function addSharedObjectsToBody()  {
        // add the fonts
        foreach (array_values($documentOCG) as &$details) {
            $details->writeFont($this);
        }
        // add the form XObjects
        foreach (array_values($formXObjects) as &$objs) {
            $template = $objs[1];
            if ($template != NULL && $template->getIndirectReference() instanceof PRIndirectReference)
                continue;
            if ($template != NULL && $template->getType() == PdfTemplate::TYPE_TEMPLATE) {
               $obj = addToBody($template->getFormXObject(), $template->getIndirectReference());
            }
        }
        // add all the dependencies in the imported pages
        foreach (array_values($importedPages) as &$currentPdfReaderInstance) {
            $currentPdfReaderInstance->writeAllPages();
        }
        $currentPdfReaderInstance = NULL;
        // add the color
        foreach (array_values($documentColors) as &$color) {
            $cobj = addToBody($color->getSpotColor($this), $color->getIndirectReference());
        }
        // add the pattern
        foreach (array_keys($documentPatterns) as &$pat) {
            $pobj = addToBody($pat->getPattern(), $pat->getIndirectReference());
        }
        // add the shading patterns
        foreach (array_keys($documentShadingPatterns) as &$shadingPattern) {
            $shadingPattern->addToBody();
        }
        // add the shadings
        foreach (array_keys($documentShadings) as &$shading) {
            $shading->addToBody();
        }
        // add the extgstate
        foreach (array_keys($documentExtGState) as &$gstate) {
            $obj = $documentExtGState[$gstate];
            addToBody($gstate, $obj[1]);
        }
        // add the layers
        foreach (array_keys($documentLayers) as &$layer) {
            if ($layer instanceof PdfLayerMembership)
                addToBody($layer->getPdfObject(), $layer->getRef());
        }
        foreach ($documentOCG as &$layer) {
            addToBody($layer->getPdfObject(), $layer->getRef());
        }
    }

    /**
    * Signals that the <CODE>Document</CODE> was closed and that no other
    * <CODE>Elements</CODE> will be added.
    * <P>
    * The pages-tree is built and written to the outputstream.
    * A Catalog is constructed, as well as an Info-object,
    * the referencetable is composed and everything is written
    * to the outputstream embedded in a Trailer.
    */
    public function close() {
        if ($open == TRUE) {
            if (($currentPageNumber - 1) != count($pageReferences))
                throw new Exception("The page " . count($pageReferences) .
                " was requested but the document has only " . ($currentPageNumber - 1) . " pages.");
            $pdf->close();
            try {
                addSharedObjectsToBody();
                // add the root to the body
                $rootRef = $root->writePageTree();
                // make the catalog-object and add it to the body
                $catalog = getCatalog($rootRef);
                // make pdfx conformant
                $info = getInfo();
                if ($pdfxConformance != PdfWriter::PDFXNONE) {
                    if ($info->get(PdfName::$GTS_PDFXVERSION) == NULL) {
                        if ($pdfxConformance == PdfWriter::PDFX1A2001) {
                            $info->put(PdfName::$GTS_PDFXVERSION, new PdfString("PDF/X-1:2001"));
                            $info->put(new PdfName("GTS_PDFXConformance"), new PdfString("PDF/X-1a:2001"));
                        }
                        else if ($pdfxConformance == PdfWriter::PDFX32002)
                            $info->put(PdfName::$GTS_PDFXVERSION, new PdfString("PDF/X-3:2002"));
                    }
                    if ($info->get(PdfName::$TITLE) == NULL) {
                        $info->put(PdfName::$TITLE, new PdfString("Pdf document"));
                    }
                    if ($info->get(PdfName::$CREATOR) == NULL) {
                        $info->put(PdfName::$CREATOR, new PdfString("Unknown"));
                    }
                    if ($info->get(PdfName::$TRAPPED) == NULL) {
                        $info->put(PdfName::$TRAPPED, new PdfName("False"));
                    }
                    getExtraCatalog();
                    if ($extraCatalog->get(PdfName::$OUTPUTINTENTS) == NULL) {
                        $out = new PdfDictionary(PdfName::$OUTPUTINTENT);
                        $out->put(PdfName::$OUTPUTCONDITION, new PdfString("SWOP CGATS TR 001-1995"));
                        $out->put(PdfName::$OUTPUTCONDITIONIDENTIFIER, new PdfString("CGATS TR 001"));
                        $out->put(PdfName::$REGISTRYNAME, new PdfString("http://www.color.org"));
                        $out->put(PdfName::$INFO, new PdfString(""));
                        $out->put(PdfName::$S, PdfName::$GTS_PDFX);
                        $extraCatalog->put(PdfName::$OUTPUTINTENTS, new PdfArray($out));
                    }
                }
                if ($extraCatalog != NULL) {
                    $catalog->mergeDifferent($extraCatalog);
                }
                $indirectCatalog = addToBody($catalog, FALSE);
                // add the info-object to the body
                $infoObj = addToBody($info, FALSE);
                $encryption = NULL;
                $fileID = NULL;
                $body->flushObjStm();
                if ($crypto != NULL) {
                    $encryptionObject = addToBody($crypto->getEncryptionDictionary(), FALSE);
                    $encryption = $encryptionObject->getIndirectReference();
                    $fileID = $crypto->getFileID();
                }
                else
                    $fileID = PdfEncryption::createInfoId(PdfEncryption::createDocumentId());
                
                // write the cross-reference table of the body
                $body->writeCrossReferenceTable($os, $indirectCatalog->getIndirectReference(),
                    $infoObj->getIndirectReference(), $encryption,  $fileID, $prevxref);

                // make the trailer
                if ($fullCompression == TRUE) {
                    $os->write(getISOBytes("startxref\n"));
                    $os->write(getISOBytes((string)$body->offset()));
                    $os->write(getISOBytes("\n%%EOF\n"));
                }
                else {
                    $trailer = new PdfTrailer($body->size(),
                    $body->offset(),
                    $indirectCatalog->getIndirectReference(),
                    $infoObj->getIndirectReference(),
                    $encryption,
                    $fileID, $prevxref);
                    $trailer->toPdf($this, $os);
                }
                parent::close();
            }
            catch(IOException $ioe) {
                throw new Exception($ioe);
            }
        }
    }

    // methods

    /**
    * Sometimes it is necessary to know where the just added <CODE>Table</CODE> ends.
    *
    * For instance to avoid to add another table in a page that is ending up, because
    * the new table will be probably splitted just after the header (it is an
    * unpleasant effect, isn't it?).
    *
    * Added on September 8th, 2001
    * by Francesco De Milato
    * francesco.demilato@tiscalinet.it
    * @param table the <CODE>Table</CODE>
    * @return the bottom height of the just added table
    */

    public function getTableBottom(Table $table) {
        return $pdf->bottom($table) - $pdf->indentBottom();
    }

    /**
    * Gets a pre-rendered table.
    * (Contributed by dperezcar@fcc.es) 
    * @param table		Contains the table definition.  Its contents are deleted, after being pre-rendered.
    * @return a PdfTable
    */
    public function getPdfTable(Table $table) {
        return $pdf->getPdfTable($table, TRUE);
    }

    /**
    * Row additions to the original {@link Table} used to build the {@link PdfTable} are processed and pre-rendered,
    * and then the contents are deleted. 
    * If the pre-rendered table doesn't fit, then it is fully rendered and its data discarded.  
    * There shouldn't be any column change in the underlying {@link Table} object.
    * (Contributed by dperezcar@fcc.es) 
    *
    * @param	table		The pre-rendered table obtained from {@link #getPdfTable(Table)} 
    * @return	true if the table is rendered and emptied.
    * @throws DocumentException
    * @see #getPdfTable(Table)
    */
    public function breakTableIfDoesntFit(PdfTable $table) {
        return $pdf->breakTableIfDoesntFit($table);
    }

    public function fitsPage()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Table)
                    return fitsPage1arg($arg1);
                else if ($arg1 instanceof PdfPTable)
                    return fitsPage1argPdfPTable($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if ($arg1 instanceof Table)
                    return fitsPage2args($arg1, $arg2);
                else if ($arg1 instanceof PdfPTable)
                    return fitsPage2argsPdfPTable($arg1, $arg2)
                break;
            }
        }
    }

    /**
    * Checks if a <CODE>Table</CODE> fits the current page of the <CODE>PdfDocument</CODE>.
    *
    * @param	table	the table that has to be checked
    * @param	margin	a certain margin
    * @return	<CODE>true</CODE> if the <CODE>Table</CODE> fits the page, <CODE>false</CODE> otherwise.
    */
    private function fitsPage2args(Table $table, $margin) {
        return $pdf->bottom($table) > $pdf->indentBottom() + $margin;
    }

    /**
    * Checks if a <CODE>Table</CODE> fits the current page of the <CODE>PdfDocument</CODE>.
    *
    * @param	table	the table that has to be checked
    * @return	<CODE>true</CODE> if the <CODE>Table</CODE> fits the page, <CODE>false</CODE> otherwise.
    */

    private function fitsPage1arg(Table $table) {
        return fitsPage2args($table, 0);
    }

    /**
    * Checks if a <CODE>PdfPTable</CODE> fits the current page of the <CODE>PdfDocument</CODE>.
    *
    * @param	table	the table that has to be checked
    * @param	margin	a certain margin
    * @return	<CODE>true</CODE> if the <CODE>PdfPTable</CODE> fits the page, <CODE>false</CODE> otherwise.
    */
    private function fitsPage2argsPdfPTable(PdfPTable $table, $margin) {
        return $pdf->fitsPage($table, $margin);
    }

    /**
    * Checks if a <CODE>PdfPTable</CODE> fits the current page of the <CODE>PdfDocument</CODE>.
    *
    * @param	table	the table that has to be checked
    * @return	<CODE>true</CODE> if the <CODE>PdfPTable</CODE> fits the page, <CODE>false</CODE> otherwise.
    */
    private function fitsPage1argPdfPTable(PdfPTable $table) {
        return $pdf->fitsPage($table, 0);
    }

    /**
    * Gets the current vertical page position.
    * @param ensureNewLine Tells whether a new line shall be enforced. This may cause side effects 
    *   for elements that do not terminate the lines they've started because those lines will get
    *   terminated. 
    * @return The current vertical page position.
    */
    public function getVerticalPosition($ensureNewLine) {
        return $pdf->getVerticalPosition($ensureNewLine);
    }

    /**
    * Checks if writing is paused.
    *
    * @return		<CODE>true</CODE> if writing temporarely has to be paused, <CODE>false</CODE> otherwise.
    */

    function isPaused() {
        return $pause;
    }

    /**
    * Gets the direct content for this document. There is only one direct content,
    * multiple calls to this method will allways retrieve the same.
    * @return the direct content
    */
    public function getDirectContent() {
        if ($open == FALSE)
            throw new Exception("The document is not open.");
        return $directContent;
    }

    /**
    * Gets the direct content under for this document. There is only one direct content,
    * multiple calls to this method will allways retrieve the same.
    * @return the direct content
    */

    public function getDirectContentUnder() {
        if ($open == FALSE)
            throw new Exception("The document is not open.");
        return $directContentUnder;
    }

    /**
    * Resets all the direct contents to empty. This happens when a new page is started.
    */

    function resetContent() {
        $directContent->reset();
        $directContentUnder->reset();
    }


    /** Gets the AcroForm object.
    * @return the <CODE>PdfAcroForm</CODE>
    */

    public function getAcroForm() {
        return $pdf->getAcroForm();
    }

    /** Gets the root outline.
    * @return the root outline
    */

    public function getRootOutline() {
        return $directContent->getRootOutline();
    }

    /**
    * Returns the outputStreamCounter.
    * @return the outputStreamCounter
    */
    public function getOs() {
        return $os;
    }

    function addSimple()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof BaseFont)
                    return addSimple1arg($arg1);
                else if ($arg1 instanceof PdfSpotColor)
                    return addSimple1argPdfSpotColor($arg1);
                break;
            }
        }
    }

    /**
    * Adds a <CODE>BaseFont</CODE> to the document but not to the page resources.
    * It is used for templates.
    * @param bf the <CODE>BaseFont</CODE> to add
    * @return an <CODE>Object[]</CODE> where position 0 is a <CODE>PdfName</CODE>
    * and position 1 is an <CODE>PdfIndirectReference</CODE>
    */

    private function addSimple1arg(BaseFont $bf) {
        if ($bf->getFontType() == BaseFont::FONT_TYPE_DOCUMENT) {
            return new FontDetails(new PdfName("F" . ($fontNumber++)), ($bf)->getIndirectReference(), $bf);
        }
        $ret = $documentFonts[$bf];
        if ($ret == NULL) {
            PdfWriter::checkPDFXConformance($this, PdfWriter::PDFXKEY_FONT, $bf);
            $ret = new FontDetails(new PdfName("F" . ($fontNumber++)), $body->getPdfIndirectReference(), $bf);
            $documentFonts[$bf] = $ret;
        }
        return $ret;
    }


    function eliminateFontSubset(PdfDictionary $fonts) {
        foreach (array_values($documentFonts) as &$ft) {
            if ($fonts->get($ft->getFontName()) != NULL)
                $ft->setSubset(FALSE);
        }
    }

    private function addSimple1argPdfSpotColor(PdfSpotColor $spc) {
        $ret = $documentColors[$spc];
        if ($ret == NULL) {
            $ret = new ColorDetails(new PdfName("CS" . ($colorNumber++)), $body->getPdfIndirectReference(), $spc);
            $documentColors[$spc] = $ret;
        }
        return $ret;
    }

    function addSimplePatternColorspace(Color $color) {
        $type = ExtendedColor::getType($color);
        if ($type == ExtendedColor::TYPE_PATTERN || $type == ExtendedColor::TYPE_SHADING)
            throw new Exception("An uncolored tile pattern can not have another pattern or shading as color.");
        try {
            switch ($type) {
                case ExtendedColor::TYPE_RGB:
                    if ($patternColorspaceRGB == NULL) {
                        $patternColorspaceRGB = new ColorDetails(new PdfName("CS" . ($colorNumber++)), $body->getPdfIndirectReference(), NULL);
                        $array = new PdfArray(PdfName::$PATTERN);
                        $array->add(PdfName::$DEVICERGB);
                        $cobj = addToBody($array, $patternColorspaceRGB->getIndirectReference());
                    }
                    return $patternColorspaceRGB;
                case ExtendedColor::TYPE_CMYK:
                    if ($patternColorspaceCMYK == NULL) {
                        $patternColorspaceCMYK = new ColorDetails(new PdfName("CS" . ($colorNumber++)), $body->getPdfIndirectReference(), NULL);
                        $array = new PdfArray(PdfName::$PATTERN);
                        $array->add(PdfName::$DEVICECMYK);
                        $cobj = addToBody($array, $patternColorspaceCMYK->getIndirectReference());
                    }
                    return $patternColorspaceCMYK;
                case ExtendedColor::TYPE_GRAY:
                    if ($patternColorspaceGRAY == NULL) {
                        $patternColorspaceGRAY = new ColorDetails(new PdfName("CS" . ($colorNumber++)), $body->getPdfIndirectReference(), NULL);
                        $array = new PdfArray(PdfName::$PATTERN);
                        $array->add(PdfName::$DEVICEGRAY);
                        $cobj = addToBody($array, $patternColorspaceGRAY->getIndirectReference());
                    }
                    return $patternColorspaceGRAY;
                case ExtendedColor::TYPE_SEPARATION: {
                    $details = addSimple(($color)->getPdfSpotColor());
                    $patternDetails = $documentSpotPatterns[$details];
                    if ($patternDetails == NULL) {
                        $patternDetails = new ColorDetails(new PdfName("CS" . ($colorNumber++)), $body->getPdfIndirectReference(), NULL);
                        $array = new PdfArray(PdfName::$PATTERN);
                        $array->add($details->getIndirectReference());
                        $cobj = addToBody($array, $patternDetails->getIndirectReference());
                        $documentSpotPatterns[$details] = $patternDetails;
                    }
                    return $patternDetails;
                }
                default:
                    throw new Exception("Invalid color type in PdfWriter.addSimplePatternColorspace().");
            }
        }
        catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function addSimpleShadingPattern(PdfShadingPattern $shading) {
        if (array_key_exists($shading, $documentShadingPatterns) == FALSE) {
            $shading->setName($patternNumber);
            ++$patternNumber;
            $documentShadingPatterns[$shading] = NULL;
            addSimpleShading($shading->getShading());
        }
    }

    function addSimpleShading(PdfShading $shading) {
        if (array_key_exists($shading, $documentShadings) == FALSE) {
            $documentShadings[$shading] = NULL;
            $shading->setName(count($documentShadings));
        }
    }

    function addSimpleExtGState(PdfDictionary $gstate) {
        if (array_key_exists($gstate, $documentExtGState) == FALSE) {
            PdfWriter::checkPDFXConformance($this, PdfWriter::PDFXKEY_GSTATE, $gstate);
            $documentExtGState[$gstate] = array(new PdfName("GS" . (count($documentExtGState) + 1)), getPdfIndirectReference());
        }
        return $documentExtGState[$gstate];
    }

    function registerLayer(PdfOCG $layer) {
        PdfWriter::checkPDFXConformance($this, PdfWriter::PDFXKEY_LAYER, NULL);
        if ($layer instanceof PdfLayer) {
            $la = $layer;
            if ($la->getTitle() == NULL) {
                if (in_array($layer,$documentOCG) == FALSE) {
                    array_push($documentOCG, $layer);
                    array_push($documentOCGorder, $layer);
                }
            }
            else {
                array_push($documentOCGorder, $layer);
            }
        }
        else
            throw new IllegalArgumentException("Only PdfLayer is accepted.");
    }

     function addSimpleLayer(PdfOCG $layer) {
        if (array_key_exists($layer, $documentLayers) == FALSE) {
            PdfWriter::checkPDFXConformance($this, PdfWriter::PDFXKEY_LAYER, NULL);
            $documentLayers[$layer] = new PdfName("OC" . (count($documentLayers) + 1));
        }
        return $documentLayers[$layer];
    }

    /**
    * Gets the <CODE>PdfDocument</CODE> associated with this writer.
    * @return the <CODE>PdfDocument</CODE>
    */

    function getPdfDocument() {
        return $pdf;
    }

    /**
    * Gets a <CODE>PdfIndirectReference</CODE> for an object that
    * will be created in the future.
    * @return the <CODE>PdfIndirectReference</CODE>
    */

    public function getPdfIndirectReference() {
        return $body->getPdfIndirectReference();
    }

    function getIndirectReferenceNumber() {
        return $body->getIndirectReferenceNumber();
    }

    function addSimplePattern(PdfPatternPainter $painter) {
        $name = $documentPatterns[$painter];
        try {
            if ( $name == NULL ) {
                $name = new PdfName("P" . $patternNumber);
                ++$patternNumber;
                $documentPatterns[$painter] = $name;
            }
        } catch (Exception $e) {
            throw new Exception($e);
        }
        return $name;
    }

    /**
    * Adds a template to the document but not to the page resources.
    * @param template the template to add
    * @param forcedName the template name, rather than a generated one. Can be null
    * @return the <CODE>PdfName</CODE> for this template
    */

    function addDirectTemplateSimple(PdfTemplate $template, PdfName $forcedName) {
        $ref = $template->getIndirectReference();
        $obj = $formXObjects[$ref];
        $name = NULL;
        try {
            if ($obj == NULL) {
                if ($forcedName == NULL) {
                    $name = new PdfName("Xf" . $formXObjectsCounter);
                    ++$formXObjectsCounter;
                }
                else
                    $name = $forcedName;
                if ($template->getType() == PdfTemplate::TYPE_IMPORTED)
                    $template = NULL;
                $formXObjects[$ref] =  array($name, $template);
            }
            else
                $name = $obj[0];
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
        return $name;
    }

    /**
    * Sets the <CODE>PdfPageEvent</CODE> for this document.
    * @param pageEvent the <CODE>PdfPageEvent</CODE> for this document
    */

    public function setPageEvent(PdfPageEvent $pageEvent) {
        $this->pageEvent = $pageEvent;
    }

    /**
    * Gets the <CODE>PdfPageEvent</CODE> for this document or <CODE>null</CODE>
    * if none is set.
    * @return the <CODE>PdfPageEvent</CODE> for this document or <CODE>null</CODE>
    * if none is set
    */

    public function getPageEvent() {
        return $pageEvent;
    }

    /**
    * Adds the local destinations to the body of the document.
    * @param dest the <CODE>HashMap</CODE> containing the destinations
    * @throws IOException on error
    */

    function addLocalDestinations(array $dest) {
        foreach (array_keys($arr) as &$name) {
            $obj = $dest[$name];
            $destination = $obj[2];
            if ($destination == NULL)
                throw new Exception("The name '" . $name . "' has no local destination.");
            if ($obj[1] == NULL)
                $obj[1] = getPdfIndirectReference();
            $iob = addToBody($destination, $obj[1]);
        }
    }

    /**
    * Gets the current pagenumber of this document.
    *
    * @return a page number
    */

    public function getPageNumber() {
        return $pdf->getPageNumber();
    }

    /**
    * Sets the viewer preferences by ORing some of these constants:<br>
    * <ul>
    * <li>The page layout to be used when the document is opened (choose one).
    *   <ul>
    *   <li><b>PageLayoutSinglePage</b> - Display one page at a time. (default)
    *   <li><b>PageLayoutOneColumn</b> - Display the pages in one column.
    *   <li><b>PageLayoutTwoColumnLeft</b> - Display the pages in two columns, with
    *       oddnumbered pages on the left.
    *   <li><b>PageLayoutTwoColumnRight</b> - Display the pages in two columns, with
    *       oddnumbered pages on the right.
    *   </ul>
    * <li>The page mode how the document should be displayed
    *     when opened (choose one).
    *   <ul>
    *   <li><b>PageModeUseNone</b> - Neither document outline nor thumbnail images visible. (default)
    *   <li><b>PageModeUseOutlines</b> - Document outline visible.
    *   <li><b>PageModeUseThumbs</b> - Thumbnail images visible.
    *   <li><b>PageModeFullScreen</b> - Full-screen mode, with no menu bar, window
    *       controls, or any other window visible.
    *   <li><b>PageModeUseOC</b> - Optional content group panel visible
    *   </ul>
    * <li><b>HideToolbar</b> - A flag specifying whether to hide the viewer application's tool
    *     bars when the document is active.
    * <li><b>HideMenubar</b> - A flag specifying whether to hide the viewer application's
    *     menu bar when the document is active.
    * <li><b>HideWindowUI</b> - A flag specifying whether to hide user interface elements in
    *     the document's window (such as scroll bars and navigation controls),
    *     leaving only the document's contents displayed.
    * <li><b>FitWindow</b> - A flag specifying whether to resize the document's window to
    *     fit the size of the first displayed page.
    * <li><b>CenterWindow</b> - A flag specifying whether to position the document's window
    *     in the center of the screen.
    * <li><b>DisplayDocTitle</b> - A flag specifying whether to display the document's title
    *     in the top bar.
    * <li>The predominant reading order for text. This entry has no direct effect on the
    *     document's contents or page numbering, but can be used to determine the relative
    *     positioning of pages when displayed side by side or printed <i>n-up</i> (choose one).
    *   <ul>
    *   <li><b>DirectionL2R</b> - Left to right
    *   <li><b>DirectionR2L</b> - Right to left (including vertical writing systems such as
    *       Chinese, Japanese, and Korean)
    *   </ul>
    * <li>The document's page mode, specifying how to display the
    *     document on exiting full-screen mode. It is meaningful only
    *     if the page mode is <b>PageModeFullScreen</b> (choose one).
    *   <ul>
    *   <li><b>NonFullScreenPageModeUseNone</b> - Neither document outline nor thumbnail images
    *       visible
    *   <li><b>NonFullScreenPageModeUseOutlines</b> - Document outline visible
    *   <li><b>NonFullScreenPageModeUseThumbs</b> - Thumbnail images visible
    *   <li><b>NonFullScreenPageModeUseOC</b> - Optional content group panel visible
    *   </ul>
    * </ul>
    * @param preferences the viewer preferences
    */

    public function setViewerPreferences($preferences) {
        $pdf->setViewerPreferences($preferences);
    }

    public function setEncryption()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (is_resource($arg1) == TRUE)
                    setEncryption4argsbytes($arg1, $arg2, $arg3, $arg4);
                else
                    setEncryption($arg1, $arg2, $arg3, $arg4);
                break;
            }
        }
    }

    /** Sets the encryption options for this document. The userPassword and the
    *  ownerPassword can be null or have zero length. In this case the ownerPassword
    *  is replaced by a random string. The open permissions for the document can be
    *  AllowPrinting, AllowModifyContents, AllowCopy, AllowModifyAnnotations,
    *  AllowFillIn, AllowScreenReaders, AllowAssembly and AllowDegradedPrinting.
    *  The permissions can be combined by ORing them.
    * @param userPassword the user password. Can be null or empty
    * @param ownerPassword the owner password. Can be null or empty
    * @param permissions the user permissions
    * @param strength128Bits <code>true</code> for 128 bit key length, <code>false</code> for 40 bit key length
    * @throws DocumentException if the document is already open
    */
    private function setEncryption4argsbytes($userPassword, $ownerPassword, $permissions, $strength128Bits)  {
        if ($pdf->isOpen() == TRUE)
            throw new DocumentException("Encryption can only be added before opening the document.");
        $crypto = new PdfEncryption();
        $crypto->setupAllKeys($userPassword, $ownerPassword, $permissions, $strength128Bits);
    }

    /**
    * Sets the encryption options for this document. The userPassword and the
    *  ownerPassword can be null or have zero length. In this case the ownerPassword
    *  is replaced by a random string. The open permissions for the document can be
    *  AllowPrinting, AllowModifyContents, AllowCopy, AllowModifyAnnotations,
    *  AllowFillIn, AllowScreenReaders, AllowAssembly and AllowDegradedPrinting.
    *  The permissions can be combined by ORing them.
    * @param strength <code>true</code> for 128 bit key length, <code>false</code> for 40 bit key length
    * @param userPassword the user password. Can be null or empty
    * @param ownerPassword the owner password. Can be null or empty
    * @param permissions the user permissions
    * @throws DocumentException if the document is already open
    */
    private function setEncryption4args($strength, $userPassword, $ownerPassword, $permissions)  {
        setEncryption(DocWriter::getISOBytes($userPassword), DocWriter::getISOBytes($ownerPassword), $permissions, $strength);
    }

    public function addToBody()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof PdfObject)
                    return addToBody1argPdfObject($arg1);

                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if ($arg1 instanceof PdfObject && is_bool($arg2) == TRUE)
                    return addToBody2argsPdfObjectBool($arg1, $arg2);
                else if ($arg1 instanceof PdfObject && $arg2 instanceof PdfIndirectReference)
                    return addToBody2argsPdfObjectPdfIndirectReference($arg1, $arg2);
                else if ($arg1 instanceof PdfObject && is_integer($arg2) == TRUE)
                    return addToBody2argsPdfObjectInt($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if ($arg2 instanceof PdfIndirectReference)
                    return addToBody3argsPdfIndirectReference($arg1, $arg2, $arg3);
                else if (is_integer($arg2) == TRUE)
                    return addToBody3argsInt($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    /**
    * Adds an object to the PDF body.
    * @param object
    * @return a PdfIndirectObject
    * @throws IOException
    */
    private function addToBody1argPdfObject(PdfObject $object)
    {
        $iobj = $body->add($object);
        return $iobj;
    }

    /**
    * Adds an object to the PDF body.
    * @param object
    * @param inObjStm
    * @return a PdfIndirectObject
    * @throws IOException
    */
    private function addToBody2argsPdfObjectBool(PdfObject $object, $inObjStm)
    {
        $iobj = $body->add($object, $inObjStm);
        return $iobj;
    }

    /**
    * Adds an object to the PDF body.
    * @param object
    * @param ref
    * @return a PdfIndirectObject
    * @throws IOException
    */
    private function addToBody2argsPdfObjectPdfIndirectReference(PdfObject $object, PdfIndirectReference $ref)
    {
        $iobj = $body->add($object, $ref);
        return $iobj;
    }

    /**
    * Adds an object to the PDF body.
    * @param object
    * @param refNumber
    * @return a PdfIndirectObject
    * @throws IOException
    */
    private function addToBody2argsPdfObjectInt(PdfObject $object, $refNumber) 
    {
        $iobj = $body->add($object, $refNumber);
        return $iobj;
    }

    /**
    * Adds an object to the PDF body.
    * @param object
    * @param ref
    * @param inObjStm
    * @return a PdfIndirectObject
    * @throws IOException
    */
    private function addToBody3argsPdfIndirectReference(PdfObject $object, PdfIndirectReference $ref, $inObjStm)
    {
        $iobj = $body->add($object, $ref, $inObjStm);
        return $iobj;
    }

    private function addToBody3argsInt(PdfObject $object, $refNumber, $inObjStm) 
    {
        $iobj = $body->add($object, $refNumber, $inObjStm);
        return $iobj;
    }

    function setOpenAction()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_string($arg1) == TRUE)
                    setOpenActionString($arg1);
                else if ($arg1 instanceof PdfAction)
                    setOpenActionPdfAction($arg1);

                break;
            }
        }
    }

    /** When the document opens it will jump to the destination with
    * this name.
    * @param name the name of the destination to jump to
    */
    private function setOpenActionString($name) {
        $pdf->setOpenAction($name);
    }

    /** Additional-actions defining the actions to be taken in
    * response to various trigger events affecting the document
    * as a whole. The actions types allowed are: <CODE>DOCUMENT_CLOSE</CODE>,
    * <CODE>WILL_SAVE</CODE>, <CODE>DID_SAVE</CODE>, <CODE>WILL_PRINT</CODE>
    * and <CODE>DID_PRINT</CODE>.
    *
    * @param actionType the action type
    * @param action the action to execute in response to the trigger
    * @throws PdfException on invalid action type
    */
    public function setAdditionalAction(PdfName $actionType, PdfAction $action) throws PdfException {
        if (($actionType->equals(PdfWriter::$DOCUMENT_CLOSE) ||
        $actionType->equals(PdfWriter::$WILL_SAVE) ||
        $actionType->equals(PdfWriter::$DID_SAVE) ||
        $actionType->equals(PdfWriter::$WILL_PRINT) ||
        $actionType->equals(PdfWriter::$DID_PRINT))==FALSE) {
            throw new PdfException("Invalid additional action type: " + $actionType->toString());
        }
        $pdf->addAdditionalAction($actionType, $action);
    }

    /** When the document opens this <CODE>action</CODE> will be
    * invoked.
    * @param action the action to be invoked
    */
    private function setOpenActionPdfAction(PdfAction $action) {
        $pdf->setOpenAction($action);
    }

    /** Sets the page labels
    * @param pageLabels the page labels
    */
    public function setPageLabels(PdfPageLabels $pageLabels) {
        $pdf->setPageLabels($pageLabels);
    }

    function getEncryption() {
        return $crypto;
    }

    function getReaderFile(PdfReader $reader) {
        return $currentPdfReaderInstance->getReaderFile();
    }

    protected function getNewObjectNumber(PdfReader $reader, $number, $generation) {
        return $currentPdfReaderInstance->getNewObjectNumber($number, $generation);
    }

    /** Gets a page from other PDF document. The page can be used as
    * any other PdfTemplate. Note that calling this method more than
    * once with the same parameters will retrieve the same object.
    * @param reader the PDF document where the page is
    * @param pageNumber the page number. The first page is 1
    * @return the template representing the imported page
    */
    public getImportedPage(PdfReader $reader, $pageNumber) {
        $inst = $importedPages[$reader];
        if ($inst == NULL) {
            $inst = $reader->getPdfReaderInstance($this);
            $importedPages[$reader] = $inst;
        }
        return $inst->getImportedPage($pageNumber);
    }

    public function addJavascript()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instance of PdfAction)
                    addJavaScript1argPdfAction($arg1);
                else if (is_string($arg1) == TRUE)
                    addJavaScript1argstring($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                addJavaScript2args($arg1, $arg2);
                break;
            }
        }
    }

    /** Adds a JavaScript action at the document level. When the document
    * opens all this JavaScript runs.
    * @param js The JavaScrip action
    */
    private function addJavaScript1argPdfAction(PdfAction $js) {
        $pdf->addJavaScript($js);
    }


    /** Adds a JavaScript action at the document level. When the document
    * opens all this JavaScript runs.
    * @param code the JavaScript code
    * @param unicode select JavaScript unicode. Note that the internal
    * Acrobat JavaScript engine does not support unicode,
    * so this may or may not work for you
    */
    private function addJavaScript2args(String code, boolean unicode) {
        addJavaScript(PdfAction.javaScript(code, this, unicode));
    }

    /** Adds a JavaScript action at the document level. When the document
    * opens all this JavaScript runs.
    * @param code the JavaScript code
    */
    private function addJavaScript1argstring($code) {
        addJavaScript2args($code, FALSE);
    }

    /** Sets the crop box. The crop box should not be rotated even if the
    * page is rotated. This change only takes effect in the next
    * page.
    * @param crop the crop box
    */
    public function setCropBoxSize(Rectangle $crop) {
        $pdf->setCropBoxSize(crop);
    }

    /** Gets a reference to a page existing or not. If the page does not exist
    * yet the reference will be created in advance. If on closing the document, a
    * page number greater than the total number of pages was requested, an
    * exception is thrown.
    * @param page the page number. The first page is 1
    * @return the reference to the page
    */
    public function getPageReference($page) {
        --$page;
        if ($page < 0)
            throw new IndexOutOfBoundsException("The page numbers start at 1.");
        $ref = NULL;
        if ($page < count($pageReferences)) {
            $ref = $pageReferences[$page];
            if ($ref == NULL) {
                $ref = $body->getPdfIndirectReference();
                $pageReferences[$page] = $ref;
            }
        }
        else {
            $empty = $page - count($pageReferences);
            for ($k = 0; $k < $empty; ++$k)
                array_push($pageReferences, NULL);
            $ref = $body->getPdfIndirectReference();
            array_push($pageReferences, $ref);
        }
        return $ref;
    }

    function getCurrentPage() {
        return getPageReference($currentPageNumber);
    }

    function getCurrentPageNumber() {
        return $currentPageNumber;
    }

    /** Adds the <CODE>PdfAnnotation</CODE> to the calculation order
    * array.
    * @param annot the <CODE>PdfAnnotation</CODE> to be added
    */
    public function addCalculationOrder(PdfFormField $annot) {
        $pdf->addCalculationOrder($annot);
    }

    /** Set the signature flags.
    * @param f the flags. This flags are ORed with current ones
    */
    public function setSigFlags($f) {
        $pdf->setSigFlags(f);
    }

    public function addAnnotation()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                addAnnotation1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                addAnnotation2args($arg1, $arg2);
                break;
            }
        }

    }

    /** Adds a <CODE>PdfAnnotation</CODE> or a <CODE>PdfFormField</CODE>
    * to the document. Only the top parent of a <CODE>PdfFormField</CODE>
    * needs to be added.
    * @param annot the <CODE>PdfAnnotation</CODE> or the <CODE>PdfFormField</CODE> to add
    */
    private function addAnnotation1arg(PdfAnnotation $annot) {
        $pdf->addAnnotation($annot);
    }

    private function addAnnotation2args(PdfAnnotation $annot, $page) {
        addAnnotation($annot);
    }

    /** Sets the PDF version. Must be used right before the document
    * is opened. Valid options are VERSION_1_2, VERSION_1_3,
    * VERSION_1_4, VERSION_1_5 and VERSION_1_6. VERSION_1_4 is the default.
    * @param version the version number
    */
    public void setPdfVersion($version) {
        if (itextphp_bytes_getSize($HEADER) > PdfWriter::VPOINT)
        {
            $tmpVersion = itextphp_bytes_createfromRaw($version);
            itextphp_bytes_write($HEADER, PdfWriter::VPOINT, $tmpVersion, 0);

        }
    }

    /** Reorder the pages in the document. A <CODE>null</CODE> argument value
    * only returns the number of pages to process. It is
    * advisable to issue a <CODE>Document.newPage()</CODE>
    * before using this method.
    * @return the total number of pages
    * @param order an array with the new page sequence. It must have the
    * same size as the number of pages.
    * @throws DocumentException if all the pages are not present in the array
    */
    public function reorderPages(array $order) 
    {
        return $root->reorderPages($order);
    }

    /** Gets the space/character extra spacing ratio for
    * fully justified text.
    * @return the space/character extra spacing ratio
    */
    public function getSpaceCharRatio() {
        return $spaceCharRatio;
    }

    /** Sets the ratio between the extra word spacing and the extra character spacing
    * when the text is fully justified.
    * Extra word spacing will grow <CODE>spaceCharRatio</CODE> times more than extra character spacing.
    * If the ratio is <CODE>PdfWriter.NO_SPACE_CHAR_RATIO</CODE> then the extra character spacing
    * will be zero.
    * @param spaceCharRatio the ratio between the extra word spacing and the extra character spacing
    */
    public function setSpaceCharRatio($spaceCharRatio) {
        if ($spaceCharRatio < 0.001)
            $this->spaceCharRatio = 0.001;
        else
            $this->spaceCharRatio = $spaceCharRatio;
    }

    /** Sets the run direction. This is only used as a placeholder
    * as it does not affect anything.
    * @param runDirection the run direction
    */
    public function setRunDirection($runDirection) {
        if ($runDirection < PdfWriter::RUN_DIRECTION_NO_BIDI || $runDirection > PdfWriter::RUN_DIRECTION_RTL)
            throw new Exception("Invalid run direction: " . $runDirection);
        this.runDirection = runDirection;
    }

    /** Gets the run direction.
    * @return the run direction
    */
    public function getRunDirection() {
        return $runDirection;
    }

    /**
    * Sets the display duration for the page (for presentations)
    * @param seconds   the number of seconds to display the page
    */
    public function setDuration($seconds) {
        $pdf->setDuration($seconds);
    }

    /**
    * Sets the transition for the page
    * @param transition   the Transition object
    */
    public function setTransition(PdfTransition $transition) {
        $pdf->setTransition($transition);
    }

    /** Writes the reader to the document and frees the memory used by it.
    * The main use is when concatenating multiple documents to keep the
    * memory usage restricted to the current appending document.
    * @param reader the <CODE>PdfReader</CODE> to free
    * @throws IOException on error
    */
    public function freeReader(PdfReader $reader)  {
        $currentPdfReaderInstance = $importedPages[$reader];
        if ($currentPdfReaderInstance == NULL)
            return;
        $currentPdfReaderInstance->writeAllPages();
        $currentPdfReaderInstance = NULL;
        unset($importedPages[$reader]);
    }

    /** Sets the open and close page additional action.
    * @param actionType the action type. It can be <CODE>PdfWriter.PAGE_OPEN</CODE>
    * or <CODE>PdfWriter.PAGE_CLOSE</CODE>
    * @param action the action to perform
    * @throws PdfException if the action type is invalid
    */
    public function setPageAction(PdfName $actionType, PdfAction $action) 
    {
        if ($actionType->equals(PdfWriter::$PAGE_OPEN) == FALSE && $actionType->equals(PdfWriter::$PAGE_CLOSE) == FALSE)
            throw new PdfException("Invalid page additional action type: " . $actionType->toString());
        $pdf->setPageAction($actionType, $action);
    }

    /** Gets the current document size. This size only includes
    * the data already writen to the output stream, it does not
    * include templates or fonts. It is usefull if used with
    * <CODE>freeReader()</CODE> when concatenating many documents
    * and an idea of the current size is needed.
    * @return the approximate size without fonts or templates
    */
    public function getCurrentDocumentSize() {
        return $body->offset() + $body->size() * 20 + 0x48;
    }

    /** Getter for property strictImageSequence.
    * @return value of property strictImageSequence
    *
    */
    public function isStrictImageSequence() {
        return $pdf->isStrictImageSequence();
    }

    /** Sets the image sequence to follow the text in strict order.
    * @param strictImageSequence new value of property strictImageSequence
    *
    */
    public function setStrictImageSequence($strictImageSequence) {
        $pdf->setStrictImageSequence($strictImageSequence);
    }

    /** If you use setPageEmpty(false), invoking newPage() after a blank page will add a newPage.
    * @param pageEmpty
    */
    public function setPageEmpty($pageEmpty) {
        $pdf->setPageEmpty($pageEmpty);
    }

    /** Gets the info dictionary for changing.
    * @return the info dictionary
    */
    public function getInfo() {
        return $document->getInfo();
    }

    /**
    * Sets extra keys to the catalog.
    * @return the catalog to change
    */
    public function getExtraCatalog() {
        if ($extraCatalog == NULL)
            $extraCatalog = new PdfDictionary();
        return $this->extraCatalog;
    }

    /**
    * Sets the document in a suitable way to do page reordering.
    */
    public function setLinearPageMode() {
        $root->setLinearMode(NULL);
    }

    /** Getter for property group.
    * @return Value of property group.
    *
    */
    public function getGroup() {
        return $this->group;
    }

    /** Setter for property group.
    * @param group New value of property group.
    *
    */
    public function setGroup(PdfDictionary $group) {
        $this->group = $group;
    }

    /**
    * Sets the PDFX conformance level. Allowed values are PDFX1A2001 and PDFX32002. It
    * must be called before opening the document.
    * @param pdfxConformance the conformance level
    */
    public function setPDFXConformance($pdfxConformance) {
        if ($this->pdfxConformance == $pdfxConformance)
            return;
        if ($pdf->isOpen() == TRUE)
            throw new PdfXConformanceException("PDFX conformance can only be set before opening the document.");
        if (crypto != null)
            throw new PdfXConformanceException("A PDFX conforming document cannot be encrypted.");
        if ($pdfxConformance != PdfWriter::PDFXNONE)
            setPdfVersion(PdfWriter::VERSION_1_3);
        $this->pdfxConformance = $pdfxConformance;
    }

    /**
    * Gets the PDFX conformance level.
    * @return the PDFX conformance level
    */
    public function getPDFXConformance() {
        return $pdfxConformance;
    }

    static function checkPDFXConformance(PdfWriter $writer, $key, $obj1) {
        if ($writer == NULL || $writer->pdfxConformance == PdfWriter::PDFXNONE)
            return;
        $conf = $writer->pdfxConformance;
        switch ($key) {
            case PdfWriter::PDFXKEY_COLOR:
                switch ($conf) {
                    case PdfWriter::PDFX1A2001:
                        if ($obj1 instanceof ExtendedColor) {
                            $ec = $obj1;
                            switch ($ec->getType()) {
                                case ExtendedColor::TYPE_CMYK:
                                case ExtendedColor::TYPE_GRAY:
                                    return;
                                case ExtendedColor::TYPE_RGB:
                                    throw new PdfXConformanceException("Colorspace RGB is not allowed.");
                                case ExtendedColor::TYPE_SEPARATION:
                                    $sc = $ec;
                                    PdfWriter::checkPDFXConformance($writer, PdfWriter::PDFXKEY_COLOR, $sc->getPdfSpotColor()->getAlternativeCS());
                                    break;
                                case ExtendedColor::TYPE_SHADING:
                                    $xc = $ec;
                                    PdfWriter::checkPDFXConformance($writer, PdfWriter::PDFXKEY_COLOR, $xc->getPdfShadingPattern()->getShading()->getColorSpace());
                                    break;
                                case ExtendedColor::TYPE_PATTERN:
                                    $pc = $ec;
                                    PdfWriter::checkPDFXConformance($writer, PdfWriter::PDFXKEY_COLOR, $pc->getPainter()->getDefaultColor());
                                    break;
                            }
                        }
                        else if ($obj1 instanceof Color)
                            throw new PdfXConformanceException("Colorspace RGB is not allowed.");
                        break;
                }
                break;
            case PdfWriter::PDFXKEY_CMYK:
                break;
            case PdfWriter::PDFXKEY_RGB:
                if ($conf == PdfWriter::PDFX1A2001)
                    throw new PdfXConformanceException("Colorspace RGB is not allowed.");
                break;
            case PdfWriter::PDFXKEY_FONT:
                if (($obj1)->isEmbedded() == FALSE)
                    throw new PdfXConformanceException("All the fonts must be embedded.");
                break;
            case PdfWriter::PDFXKEY_IMAGE:
                $image = $obj1;
                if ($image->get(PdfName::$SMASK) != NULL)
                    throw new PdfXConformanceException("The /SMask key is not allowed in images.");
                switch ($conf) {
                    case PdfWriter::PDFX1A2001:
                        $cs = $image->get(PdfName::$COLORSPACE);
                        if ($cs == NULL)
                            return;
                        if ($cs->isName() == TRUE) {
                            if (PdfName::$DEVICERGB->equals($cs) == TRUE)
                                throw new PdfXConformanceException("Colorspace RGB is not allowed.");
                        }
                        else if ($cs->isArray() == TRUE) {
                            if (PdfName::$CALRGB->equals(($cs)->getArrayList()[0]) == TRUE)
                                throw new PdfXConformanceException("Colorspace CalRGB is not allowed.");
                        }
                        break;
                }
                break;
            case PdfWriter::PDFXKEY_GSTATE:
                $gs = $obj1;
                $obj = $gs->get(PdfName::$BM);
                if ($obj != NULL && PdfGState::$BM_NORMAL->equals($obj) == FALSE && PdfGState::$BM_COMPATIBLE->equals($obj) == FALSE)
                    throw new PdfXConformanceException("Blend mode " . $obj->__toString() . " not allowed.");
                $obj = $gs->get(PdfName::$CA);
                $v = 0.0;
                if ($obj != NULL && (v = ($obj)->doubleValue()) != 1.0)
                    throw new PdfXConformanceException("Transparency is not allowed: /CA = " . $v);
                $obj = $gs->get(PdfName::$ca);
                $v = 0.0;
                if ($obj != NULL && ($v = ($obj)->doubleValue()) != 1.0)
                    throw new PdfXConformanceException("Transparency is not allowed: /ca = " . $v);
                break;
            case PdfWriter::PDFXKEY_LAYER:
                throw new PdfXConformanceException("Layers are not allowed.");
        }
    }

    public function setOutputIntents()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return setOutputIntents2args($arg1, $arg2);
                break;
            }
            case 5:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                setOutputIntents5args($arg1, $arg2, $arg3, $arg4, $arg5);
                break;
            }
         }
    }

    /**
    * Sets the values of the output intent dictionary. Null values are allowed to
    * suppress any key.
    * @param outputConditionIdentifier a value
    * @param outputCondition a value
    * @param registryName a value
    * @param info a value
    * @param destOutputProfile a value
    * @throws IOException on error
    */
    private function setOutputIntents5args($outputConditionIdentifier, $outputCondition, $registryName, $info, $destOutputProfile) 
    {
        getExtraCatalog();
        $out = new PdfDictionary(PdfName::$OUTPUTINTENT);
        if ($outputCondition != NULL)
            $out->put(PdfName::$OUTPUTCONDITION, new PdfString($outputCondition, PdfObject::TEXT_UNICODE));
        if ($outputConditionIdentifier != NULL)
            $out->put(PdfName::$OUTPUTCONDITIONIDENTIFIER, new PdfString($outputConditionIdentifier, PdfObject::TEXT_UNICODE));
        if ($registryName != NULL)
            $out->put(PdfName::$REGISTRYNAME, new PdfString($registryName, PdfObject::TEXT_UNICODE));
        if ($info != NULL)
            $out->put(PdfName::$INFO, new PdfString($registryName, PdfObject::TEXT_UNICODE));
        if ($destOutputProfile != NULL) {
            $stream = new PdfStream($destOutputProfile);
            $stream->flateCompress();
            $out->put(PdfName::$DESTOUTPUTPROFILE, addToBody($stream)->getIndirectReference());
        }
        $out->put(PdfName::$S, PdfName::$GTS_PDFX);
        $extraCatalog->put(PdfName::$OUTPUTINTENTS, new PdfArray($out));
    }

    private static function getNameString(PdfDictionary $dic, PdfName $key) {
        $obj = PdfReader::getPdfObject($dic->get(key));
        if ($obj == NULL || $obj->isString() == FALSE)
            return NULL;
        return ($obj)->toUnicodeString();
    }

    /**
    * Copies the output intent dictionary from other document to this one.
    * @param reader the other document
    * @param checkExistence <CODE>true</CODE> to just check for the existence of a valid output intent
    * dictionary, <CODE>false</CODE> to insert the dictionary if it exists
    * @throws IOException on error
    * @return <CODE>true</CODE> if the output intent dictionary exists, <CODE>false</CODE>
    * otherwise
    */
    private function setOutputIntents2args(PdfReader $reader, $checkExistence) 
    {
        $catalog = $reader->getCatalog();
        $outs = PdfReader::getPdfObject($catalog->get(PdfName::$OUTPUTINTENTS));
        if ($outs == NULL)
            return FALSE;
        $arr = $outs->getArrayList();
        if (count($arr) == 0)
            return FALSE;
        $out = PdfReader::getPdfObject(arr[0]);
        $obj = PdfReader::getPdfObject($out->get(PdfName::$S));
        if ($obj == NULL || PdfName::$GTS_PDFX->equals($obj) == FALSE)
            return FALSE;
        if ($checkExistence == TRUE)
            return TRUE;
        $stream = PdfReader::getPdfObject($out->get(PdfName::$DESTOUTPUTPROFILE));
        $destProfile = NULL;
        if ($stream != NULL) {
            $destProfile = PdfReader::getStreamBytes($stream);
        }
        setOutputIntents(getNameString($out, PdfName::$OUTPUTCONDITIONIDENTIFIER), getNameString($out, PdfName::$OUTPUTCONDITION),
            getNameString($out, PdfName::$REGISTRYNAME), getNameString($out, PdfName::$INFO), $destProfile);
        return TRUE;
    }

    /**
    * Sets the page box sizes. Allowed names are: "crop", "trim", "art" and "bleed".
    * @param boxName the box size
    * @param size the size
    */
    public function setBoxSize($boxName, Rectangle $size) {
        $pdf->setBoxSize($boxName, $size);
    }

    /**
    * Gets the default colorspaces.
    * @return the default colorspaces
    */
    public function getDefaultColorspace() {
        return $defaultColorspace;
    }

    /**
    * Sets the default colorspace that will be applied to all the document.
    * The colorspace is only applied if another colorspace with the same name
    * is not present in the content.
    * <p>
    * The colorspace is applied immediately when creating templates and at the page
    * end for the main document content.
    * @param key the name of the colorspace. It can be <CODE>PdfName.DEFAULTGRAY</CODE>, <CODE>PdfName.DEFAULTRGB</CODE>
    * or <CODE>PdfName.DEFAULTCMYK</CODE>
    * @param cs the colorspace. A <CODE>null</CODE> or <CODE>PdfNull</CODE> removes any olorspace with the same name
    */
    public function setDefaultColorspace(PdfName $key, PdfObject $cs) {
        if ($cs == null || $cs->isNull() == TRUE)
            $defaultColorspace->remove($key);
        $defaultColorspace->put($key, $cs);
    }

    /**
    * Gets the 1.5 compression status.
    * @return <code>true</code> if the 1.5 compression is on
    */
    public function isFullCompression() {
        return $this->fullCompression;
    }

    /**
    * Sets the document's compression to the new 1.5 mode with object streams and xref
    * streams. It can be set at any time but once set it can't be unset.
    * <p>
    * If set before opening the document it will also set the pdf version to 1.5.
    */
    public function setFullCompression() {
        $this->fullCompression = TRUE;
        setPdfVersion(PdfWriter::VERSION_1_5);
    }

    /**
    * Gets the <B>Optional Content Properties Dictionary</B>. Each call fills the dictionary with the current layer
    * state. It's advisable to only call this method right before close and do any modifications
    * at that time.
    * @return the Optional Content Properties Dictionary
    */
    public PdfOCProperties getOCProperties() {
        fillOCProperties(TRUE);
        return $OCProperties;
    }


    /**
    * Sets a collection of optional content groups whose states are intended to follow
    * a "radio button" paradigm. That is, the state of at most one optional
    * content group in the array should be ON at a time: if one group is turned
    * ON, all others must be turned OFF.
    * @param group the radio group
    */
    public function addOCGRadioGroup(array $group) {
        $ar = new PdfArray();
        for ($k = 0; $k < count($group); ++$k) {
            $layer = $group[$k];
            if ($layer->getTitle() == NULL)
                $ar->add($layer->getRef());
        }
        if ($ar->size() == 0)
            return;
        $OCGRadioGroup->add($ar);
    }

}

/**
* This class generates the structure of a PDF document.
* <P>
* This class covers the third section of Chapter 5 in the 'Portable Document Format
* Reference Manual version 1.3' (page 55-60). It contains the body of a PDF document
* (section 5.14) and it can also generate a Cross-reference Table (section 5.15).
*
* @see		PdfWriter
* @see		PdfObject
* @see		PdfIndirectObject
*/

class PdfBody
{

    // membervariables

    /** array containing the cross-reference table of the normal objects. */
    private $xrefs = NULL;//should be sorted
    private $refnum = 0;
    /** the current byteposition in the body. */
    private $position = 0;
    private $writer = NULL;

    public function __construct(PdfWriter $writer)
    {
        $xrefs = array();
        array_push(new PdfCrossReference(0, 0, 65535));
        $position = $writer->getOs()->getCounter();
        $refnum = 1;
        $this->writer = $writer;
    }

    function setRefnum($refnum) {
        $this->refnum = $refnum;
    }

    // methods
    private static $OBJSINSTREAM = 200;
    private $index = NULL;
    private $streamObjects = NULL;
    private $currentObjNum = 0;
    private $numObj = 0;

    private function addToObjStm(PdfObject $obj, $nObj) {

        if ($numObj >= PdfBody::$OBJSINSTREAM)
            flushObjStm();
        if ($index == NULL) {
            $index = new ByteBuffer();
            $streamObjects = new ByteBuffer();
            $currentObjNum = getIndirectReferenceNumber();
            $numObj = 0;
        }
        $p = $streamObjects->size();
        $idx = $numObj++;
        $enc = $writer->crypto;
        $writer->crypto = NULL;
        $obj->toPdf($writer, $streamObjects);
        $writer->crypto = $enc;
        $streamObjects->append(' ');
        $index->append($nObj)->append(' ')->append($p)->append(' ');
        return new PdfCrossReference(2, $nObj, $currentObjNum, $idx);
    }

    private function flushObjStm() {
        if (numObj == 0)
            return;
        $first = $index->size();
        $index->append($streamObjects);
        $stream = new PdfStream($index->toByteArray());
        $stream->flateCompress();
        $stream->put(PdfName::$TYPE, PdfName::$OBJSTM);
        $stream->put(PdfName::$N, new PdfNumber($numObj));
        $stream->put(PdfName::$FIRST, new PdfNumber($first));
        add($stream, $currentObjNum);
        $index = NULL;
        $streamObjects = NULL;
        $numObj = 0;
    }

    /**
    * Adds a <CODE>PdfObject</CODE> to the body.
    * <P>
    * This methods creates a <CODE>PdfIndirectObject</CODE> with a
    * certain number, containing the given <CODE>PdfObject</CODE>.
    * It also adds a <CODE>PdfCrossReference</CODE> for this object
    * to an <CODE>ArrayList</CODE> that will be used to build the
    * Cross-reference Table.
    *
    * @param		object			a <CODE>PdfObject</CODE>
    * @return		a <CODE>PdfIndirectObject</CODE>
    * @throws IOException
    */

    function add()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                return add1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return add2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                return add3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    function add1arg(PdfObject $object)  {
        return add2args($object, getIndirectReferenceNumber());
    }

    function add2args(PdfObject $object, $inObjStm)  {
        return add3args($object, getIndirectReferenceNumber(), $inObjStm);
    }

    function add3args(PdfObject $object, $refNumber, $inObjStm)  {
        if ($inObjStm == TRUE && $object->canBeInObjStm() == TRUE && $writer->isFullCompression() == TRUE) {
            $pxref = addToObjStm($object, $refNumber);
            $indirect = new PdfIndirectObject($refNumber, $object, $writer);
            array_push($xrefs, $pxref);
            sort($xrefs);

            return $indirect;
        }
        else {
            $indirect = new PdfIndirectObject($refNumber, $object, $writer);
            $pxref = new PdfCrossReference($refNumber, $position);
            array_push($xrefs, $pxref);
            sort($xrefs);
            }
            $indirect->writeTo($writer->getOs());
            $position = $writer->getOs()->getCounter();
            return $indirect;

    }

    /**
    * Adds a <CODE>PdfResources</CODE> object to the body.
    *
    * @param		object			the <CODE>PdfResources</CODE>
    * @return		a <CODE>PdfIndirectObject</CODE>
    */

    //        PdfIndirectObject add(PdfResources object) {
    //            return add(object);
    //        }

    /**
    * Adds a <CODE>PdfPages</CODE> object to the body.
    *
    * @param		object			the root of the document
    * @return		a <CODE>PdfIndirectObject</CODE>
    */

    //        PdfIndirectObject add(PdfPages object) throws IOException {
    //            PdfIndirectObject indirect = new PdfIndirectObject(PdfWriter.ROOT, object, writer);
    //            rootOffset = position;
    //            indirect.writeTo(writer.getOs());
    //            position = writer.getOs().getCounter();
    //            return indirect;
    //        }

    /**
    * Returns the offset of the Cross-Reference table.
    *
    * @return		an offset
    */
    function offset() {
        return $position;
    }

    /**
    * Returns the total number of objects contained in the CrossReferenceTable of this <CODE>Body</CODE>.
    *
    * @return	a number of objects
    */

    function size() {
        return max((xrefs[count($xrefs)-1])->getRefnum() + 1, $refnum);
    }

    /**
    * Returns the CrossReferenceTable of the <CODE>Body</CODE>.
    * @param os
    * @param root
    * @param info
    * @param encryption
    * @param fileID
    * @param prevxref
    * @throws IOException
    */
    function writeCrossReferenceTable(OutputStream $os, PdfIndirectReference $root, PdfIndirectReference $info, PdfIndirectReference $encryption, PdfObject $fileID, $prevxref) {
        $refNumber = 0;
        if ($writer->isFullCompression() == TRUE) {
            flushObjStm();
            $refNumber = getIndirectReferenceNumber();
            array_push($xrefs, new PdfCrossReference($refNumber, $position))
        }
        $entry = $xrefs[0];
        $first = $entry->getRefnum();
        $len = 0;
        $sections = array();
        foreach ($xrefs as &$entry) {
            if ($first + $len == $entry->getRefnum())
                ++$len;
            else {
                array_push($sections, $first);
                array_push($sections, $len);
                $first = $entry->getRefnum();
                $len = 1;
            }
        }
        array_push($sections, $first);
        array_push($sections, $len);
        if ($writer->isFullCompression() == TRUE) {
            $mid = 4;
            $mask = 0xff000000;
            for (; mid > 1; --mid) {
                if (($mask & $position) != 0)
                    break;
                $mask >>>= 8;
            }
            $buf = new ByteBuffer();
            foreach ($xrefs as &$entry) {
                $entry->toPdf($mid, $buf);
            }
            $xr = new PdfStream($buf->toByteArray());
            $buf = NULL;
            $xr->flateCompress();
            $xr->put(PdfName::$SIZE, new PdfNumber(size()));
            $xr->put(PdfName::$ROOT, $root);
            if ($info != NULL) {
                $xr->put(PdfName::$INFO, $info);
            }
            if ($encryption != NULL)
                $xr->put(PdfName::$ENCRYPT, $encryption);
            if ($fileID != NULL)
                $xr->put(PdfName::$ID, $fileID);
            $xr->put(PdfName::$W, new PdfArray(array(1, $mid, 2)));
            $xr->put(PdfName::$TYPE, $PdfName::$XREF);
            $idx = new PdfArray();
            for ($k = 0; k < count($sections); ++$k)
                $idx->add(new PdfNumber(((integer)$sections[$k]));
            $xr->put(PdfName::$INDEX, $idx);
            if ($prevxref > 0)
                $xr->put(PdfName::$PREV, new PdfNumber($prevxref));
            $enc = $writer->crypto;
            $writer->crypto = NULL;
            $indirect = new PdfIndirectObject($refNumber, $xr, $writer);
            $indirect->writeTo($writer->getOs());
            $writer->crypto = $enc;
        }
        else {
            $os->write(getISOBytes("xref\n"));
            //Iterator i = xrefs.iterator();
            for ($k = 0; k < count($sections); $k += 2) {
                $first = ((integer)$sections[k]);
                $len = ((integer)$sections[$k + 1]);
                $os->write(getISOBytes((string)$first));
                $os->write(getISOBytes(" "));
                $os->write(getISOBytes((string)$len));
                $os->write('\n');
                $countj = 0;
                while ($len-- > 0) {
                    $entry = $xrefs[$countj];
                    $entry->toPdf($os);
                    $countj++;
                }
            }
        }
        sort($xrefs);
    }

    


}



/**
* <CODE>PdfCrossReference</CODE> is an entry in the PDF Cross-Reference table.
*/

class PdfCrossReference
{

    // membervariables
    private $type = 0;

    /** Byte offset in the PDF file. */
    private $offset = 0;

    private $refnum = 0;
    /** generation of the object. */
    private $generation = 0;

    // constructors
    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                construct2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                construct3args($arg1, $arg2, $arg3);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                construct4args($arg1, $arg2, $arg3, $arg4);
                break;
            }
        }
    }

    /**
    * Constructs a cross-reference element for a PdfIndirectObject.
    * @param refnum
    * @param	offset		byte offset of the object
    * @param	generation	generationnumber of the object
    */
    private function construct3args($refnum, $offset, $generation)
    {
         $type = 0;
         $this->offset = $offset;
         $this->refnum = $refnum;
         $this->generation = $generation;
    }

    /**
    * Constructs a cross-reference element for a PdfIndirectObject.
    * @param refnum
    * @param	offset		byte offset of the object
    */
    private function construct2args($refnum, $offset)
    {
        $type = 1;
        $this->offset = $offset;
        $this->refnum = $refnum;
        $this->generation = 0;
    }

    private function construct4args($type, $refnum, $offset, $generation)
    {
        $this->type = $type;
        $this->offset = $offset;
        $this->refnum = $refnum;
        $this->generation = $generation;
    }

    function getRefnum() {
        return $refnum;
    }

    /**
    * Returns the PDF representation of this <CODE>PdfObject</CODE>.
    * @param os
    * @throws IOException
    */
    public function toPdf(OutputStream $os) {
        // This code makes it more difficult to port the lib to JDK1.1.x:
        // StringBuffer off = new StringBuffer("0000000000").append(offset);
        // off.delete(0, off.length() - 10);
        // StringBuffer gen = new StringBuffer("00000").append(generation);
        // gen.delete(0, gen.length() - 5);
        // so it was changed into this:
        $s = "0000000000" . $offset;
        $off = "" . substr($s, strlen($s)  - 10);
        $s = "00000" . $generation;
        $gen = substr($s, strlen($s) - 5);
        if ($generation == 65535) {
            $os->write(getISOBytes($off . ' ' . $gen . " f \n"));
        }
        else
            $os->write(getISOBytes($off . ' ' . $gen . " n \n"));
    }


    /**
    * Writes PDF syntax to the OutputStream
    * @param midSize
    * @param os
    * @throws IOException
    */
    public function toPdf($midSize, OutputStream $os) {
        $os->write($type);
        while (--$midSize >= 0)
            os.write((($offset >>> (8 * $midSize)) & 0xff));
        $os->write((($generation >>> 8) & 0xff));
        $os->write(($generation & 0xff));
    }

    /**
    * @see java.lang.Comparable#compareTo(java.lang.Object)
    */
    public function compareTo($o) {
        $other = $o;
        return ($refnum < $other->refnum ? -1 : ($refnum==$other->refnum ? 0 : 1));
    }

    /**
    * @see java.lang.Object#equals(java.lang.Object)
    */
    public function equals($obj) {
        if ($obj instanceof PdfCrossReference) {
            $PdfCrossReference $other = $obj;
            return ($refnum == $other->refnum);
        }
        else
            return FALSE;
    }

}


/**
* <CODE>PdfTrailer</CODE> is the PDF Trailer object.
* <P>
* This object is described in the 'Portable Document Format Reference Manual version 1.3'
* section 5.16 (page 59-60).
*/

class PdfTrailer extends PdfDictionary {

    // membervariables
    $offset = 0;

    // constructors
    /**
    * Constructs a PDF-Trailer.
    *
    * @param		size		the number of entries in the <CODE>PdfCrossReferenceTable</CODE>
    * @param		offset		offset of the <CODE>PdfCrossReferenceTable</CODE>
    * @param		root		an indirect reference to the root of the PDF document
    * @param		info		an indirect reference to the info object of the PDF document
    * @param encryption
    * @param fileID
    * @param prevxref
    */
    function __construct($size, $offset, PdfIndirectReference $root, PdfIndirectReference $info, PdfIndirectReference $encryption, PdfObject $fileID, $prevxref) {
        $this->offset = $offset;
        put(PdfName::$SIZE, new PdfNumber($size));
        put(PdfName::$ROOT, $root);
        if ($info != NULL) {
            put(PdfName::$INFO, $info);
        }
        if ($encryption != NULL)
            put(PdfName::$ENCRYPT, $encryption);
        if ($fileID != NULL)
            put(PdfName::$ID, $fileID);
        if ($prevxref > 0)
            put(PdfName::$PREV, new PdfNumber($prevxref));
    }

    /**
    * Returns the PDF representation of this <CODE>PdfObject</CODE>.
    * @param writer
    * @param os
    * @throws IOException
    */
    public void toPdf(PdfWriter $writer, OutputStream $os)  {
        $os->write(getISOBytes("trailer\n"));
        parent::toPdf(NULL, $os);
        $os->write(getISOBytes("\nstartxref\n"));
        $os->write(getISOBytes((string)$offset));
        $os->write(getISOBytes("\n%%EOF\n"));
    }



}

?>