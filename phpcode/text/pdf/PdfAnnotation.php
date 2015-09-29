<?PHP
/*
 * $Id: PdfAnnotation.php,v 1.3 2005/09/29 22:02:44 mstaylor Exp $
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

require_once("../Rectangle.php");
require_once("../../awt/Color.php");
require_once("PdfDictionarry.php");
require_once("PdfName.php");
require_once("PdfWriter.php");
require_once("PdfAction.php");
require_once("PdfRectangle.php");
require_once("PdfBorderArray.php");
require_once("PdfColor.php");
require_once("PdfIndirectReference.php");
require_once("PdfBorderArray.php");
require_once("PdfColor.php");
require_once("PdfString.php");
require_once("PdfFileSpecification.php");
require_once("PdfNumber.php");
require_once("PdfObject.php");
require_once("PdfDestination.php");
require_once("PdfContentByte.php");
require_once("PdfArray.php");
require_once("PdfBorderDictionary.php");
require_once("PdfTemplate.php");
require_once("ExtendedColor.php");
require_once("GrayColor.php");
require_once("CMYKColor.php");
require_once("PdfOCG.php");

class PdfAnnotation extends PdfDictionary 
{

    /** highlight attributename */
    public static $HIGHLIGHT_NONE = NULL;
    /** highlight attributename */
    public static $HIGHLIGHT_INVERT = NULL;
    /** highlight attributename */
    public static $HIGHLIGHT_OUTLINE = NULL;
    /** highlight attributename */
    public static $HIGHLIGHT_PUSH = NULL;
    /** highlight attributename */
    public static $HIGHLIGHT_TOGGLE = NULL;
    /** flagvalue */
    const FLAGS_INVISIBLE = 1;
    /** flagvalue */
    const FLAGS_HIDDEN = 2;
    /** flagvalue */
    const FLAGS_PRINT = 4;
    /** flagvalue */
    const FLAGS_NOZOOM = 8;
    /** flagvalue */
    const FLAGS_NOROTATE = 16;
    /** flagvalue */
    const FLAGS_NOVIEW = 32;
    /** flagvalue */
    const FLAGS_READONLY = 64;
    /** flagvalue */
    const FLAGS_LOCKED = 128;
    /** flagvalue */
    const FLAGS_TOGGLENOVIEW = 256;
    /** appearance attributename */
    public static $APPEARANCE_NORMAL = NULL;
    /** appearance attributename */
    public static $APPEARANCE_ROLLOVER = NULL;
    /** appearance attributename */
    public static $APPEARANCE_DOWN = NULL;
    /** attributevalue */
    public static $AA_ENTER = NULL;
    /** attributevalue */
    public static $AA_EXIT = NULL;
    /** attributevalue */
    public static $AA_DOWN = NULL;
    /** attributevalue */
    public static $AA_UP = NULL;
    /** attributevalue */
    public static $AA_FOCUS = NULL;
    /** attributevalue */
    public static $AA_BLUR = NULL;
    /** attributevalue */
    public static $AA_JS_KEY = NULL;
    /** attributevalue */
    public static $AA_JS_FORMAT = NULL;
    /** attributevalue */
    public static $AA_JS_CHANGE = NULL;
    /** attributevalue */
    public static $AA_JS_OTHER_CHANGE = NULL;
    /** attributevalue */
    const MARKUP_HIGHLIGHT = 0;
    /** attributevalue */
    const MARKUP_UNDERLINE = 1;
    /** attributevalue */
    const MARKUP_STRIKEOUT = 2;

    protected $writer;
    protected $reference;
    protected $templates = array();
    protected $form = FALSE;
    protected $annotation = FALSE;

    /** Holds value of property used. */
    protected $used = FALSE;

    /** Holds value of property placeInPage. */
    private $placeInPage = -1;

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(PdfAnnotation::$initialized == FALSE)
        {
            PdfAnnotation::$HIGHLIGHT_NONE = PdfName::$N;
            PdfAnnotation::$HIGHLIGHT_INVERT = PdfName::$I;
            PdfAnnotation::$HIGHLIGHT_OUTLINE = PdfName::$O;
            PdfAnnotation::$HIGHLIGHT_PUSH = PdfName::$P;
            PdfAnnotation::$HIGHLIGHT_TOGGLE = PdfName::$T;
            PdfAnnotation::$APPEARANCE_NORMAL = PdfName::$N;
            PdfAnnotation::$APPEARANCE_ROLLOVER = PdfName::$R;
            PdfAnnotation::$APPEARANCE_DOWN = PdfName::$D;
            PdfAnnotation::$AA_ENTER = PdfName::$E;
            PdfAnnotation::$AA_EXIT = PdfName::$X;
            PdfAnnotation::$AA_DOWN = PdfName::$D;
            PdfAnnotation::$AA_UP = PdfName::$U;
            PdfAnnotation::$AA_FOCUS = PdfName::$FO;
            PdfAnnotation::$AA_BLUR = PdfName::$BL;
            PdfAnnotation::$AA_JS_KEY = PdfName::$K;
            PdfAnnotation::$AA_JS_FORMAT = PdfName::$F;
            PdfAnnotation::$AA_JS_CHANGE = PdfName::$V;
            PdfAnnotation::$AA_JS_OTHER_CHANGE = PdfName::$C;
            PdfAnnotation::$initialized = TRUE;
        }
    }

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
            case 6:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                $arg3 = func_get_arg(2); 
                $arg4 = func_get_arg(3); 
                $arg5 = func_get_arg(4); 
                $arg6 = func_get_arg(5); 
                construct6args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
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
                construct7args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
                break;
            }
        }
    }

    // constructors
    private function construct2args(PdfWriter $writer, Rectangle $rect)
    {
        $this->writer = $writer;
        if ($rect != NULL)
            put(PdfName::$RECT, new PdfRectangle($rect));
    }

    /**
    * Constructs a new <CODE>PdfAnnotation</CODE> of subtype link (Action).
    * @param writer
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @param action
    */

    private function construct6args(PdfWriter $writer, $llx, $lly, $urx, $ury, PdfAction $action) {
        $this->writer = $writer;
        put(PdfName::$SUBTYPE, PdfName::$LINK);
        put(PdfName::$RECT, new PdfRectangle($llx, $lly, $urx, $ury));
        put(PdfName::$A, $action);
        put(PdfName::$BORDER, new PdfBorderArray(0, 0, 0));
        put(PdfName::$C, new PdfColor(0x00, 0x00, 0xFF));
    }

    /**
    * Constructs a new <CODE>PdfAnnotation</CODE> of subtype text.
    * @param writer
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @param title
    * @param content
    */

    private function construct7args(PdfWriter $writer, $llx, $lly, $urx, $ury, PdfString $title, PdfString $content) {
        $this->writer = $writer;
        put(PdfName::$SUBTYPE, PdfName::$TEXT);
        put(PdfName::$T, $title);
        put(PdfName::$RECT, new PdfRectangle($llx, $lly, $urx, $ury));
        put(PdfName::$CONTENTS, $content);
    }

    /**
    * Creates a screen PdfAnnotation
    * @param writer
    * @param rect
    * @param clipTitle
    * @param fs
    * @param mimeType
    * @param playOnDisplay
    * @return a screen PdfAnnotation
    * @throws IOException
    */
    public static function createScreen(PdfWriter $writer, Rectangle $rect, $clipTitle, PdfFileSpecification $fs,$mimeType, $playOnDisplay)  {
        $ann = new PdfAnnotation($writer, $rect);
        $ann->put(PdfName::$SUBTYPE, PdfName::$SCREEN);
        $ann->put (PdfName::$F, new PdfNumber(PdfAnnotation::FLAGS_PRINT));
        $ann->put(PdfName::$TYPE, PdfName::$ANNOT);
        $ann->setPage();
        $ref = $ann->getIndirectReference();
        $action = PdfAction::rendition($clipTitle,$fs,$mimeType, $ref);
        $actionRef = $writer->addToBody($action)->getIndirectReference();
        // for play on display add trigger event
        if ($playOnDisplay == TRUE)
        {
            $aa = new PdfDictionary();
            $aa->put(new PdfName("PV"), $actionRef);
            $ann->put(PdfName::$AA, $aa);
        }
        $ann->put(PdfName::$A, $actionRef);
        return $ann;
    }

    function getIndirectReference() {
        if ($reference == NULL) {
            $reference = $writer->getPdfIndirectReference();
        }
        return $reference;
    }

    /**
    * @param writer
    * @param rect
    * @param title
    * @param contents
    * @param open
    * @param icon
    * @return a PdfAnnotation
    */
    public static function createText(PdfWriter $writer, Rectangle $rect, $title, $contents, $open, $icon) {
        $annot = new PdfAnnotation($writer, $rect);
        $annot->put(PdfName::$SUBTYPE, PdfName::$TEXT);
        if ($title != NULL)
            $annot->put(PdfName::$T, new PdfString($title, PdfObject::TEXT_UNICODE));
        if ($contents != NULL)
            $annot->put(PdfName::$CONTENTS, new PdfString($contents, PdfObject::TEXT_UNICODE));
        if ($open == TRUE)
            $annot->put(PdfName::$OPEN, PdfBoolean::$PDFTRUE);
        if ($icon != NULL) {
            $annot->put(PdfName::$NAME, new PdfName($icon));
        }
        return $annot;
    }

    /**
    * Creates a link.
    * @param writer
    * @param rect
    * @param highlight
    * @return A PdfAnnotation
    */
    protected static function createLink(PdfWriter $writer, Rectangle $rect, PdfName $highlight) {
        $annot = new PdfAnnotation($writer, $rect);
        $annot->put(PdfName::$SUBTYPE, PdfName::$LINK);
        if ($highlight->equals(PdfAnnotation::$HIGHLIGHT_INVERT) == FALSE)
            $annot->put(PdfName::$H, $highlight);
        return $annot;
    }


    public static function createLink()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 3:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                $arg3 = func_get_arg(2); 
                return PdfAnnotation::createLink3args($arg1, $arg2, $arg3);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                $arg3 = func_get_arg(2); 
                $arg4 = func_get_arg(3); 
                if ($arg4 instanceof PdfAction)
                {
                    return PdfAnnotation::createLink4argsPdfAction($arg1, $arg2, $arg3, $arg4);
                }
                else if (strcmp(gettype($arg4), "string") == 0)
                {
                    return PdfAnnotation::createLink4argsString($arg1, $arg2, $arg3, $arg4);
                }
                break;
            }
            case 5:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                $arg3 = func_get_arg(2); 
                $arg4 = func_get_arg(3); 
                $arg5 = func_get_arg(4); 
                return PdfAnnotation::createLink5args($arg1, $arg2, $arg3, $arg4, $arg5);
                break;
            }
        }
    }


    /**
    * Creates a link.
    * @param writer
    * @param rect
    * @param highlight
    * @return A PdfAnnotation
    */
    private static function createLink3args(PdfWriter $writer, Rectangle $rect, PdfName $highlight)
    {
        $annot = new PdfAnnotation($writer, $rect);
        $annot->put(PdfName::$SUBTYPE, PdfName::$LINK);
        if ($highlight->equals(PdfAnnotation::HIGHLIGHT_INVERT) == FALSE)
            $annot->put(PdfName::$H, $highlight);
        return a$nnot;
    }

    /**
    * Creates an Annotation with an Action.
    * @param writer
    * @param rect
    * @param highlight
    * @param action
    * @return A PdfAnnotation
    */
    private static function createLink4argsPdfAction(PdfWriter $writer, Rectangle r$ect, PdfName $highlight, PdfAction $action)
    {
        $annot = createLink($writer, $rect, $highlight);
        $annot->putEx(PdfName::$A, $action);
        return $annot;
    }

     /**
     * Creates an Annotation with an local destination.
     * @param writer
     * @param rect
     * @param highlight
     * @param namedDestination
     * @return A PdfAnnotation
     */
    private static function createLink4argsString(PdfWriter $writer, Rectangle $rect, PdfName $highlight, $namedDestination) {
        $annot = createLink($writer, $rect, $highlight);
        $annot->put(PdfName::$DEST, new PdfString($namedDestination));
        return $annot;
    }

    /**
    * Creates an Annotation with a PdfDestination.
    * @param writer
    * @param rect
    * @param highlight/**
     * Creates an Annotation with a PdfDestination.
     * @param writer
     * @param rect
     * @param highlight
     * @param page
     * @param dest
     * @return A PdfAnnotation
     */

    private static function createLink5args(PdfWriter $writer, Rectangle $rect, PdfName $highlight, $page, PdfDestination $dest) {
        $annot = createLink($writer, $rect, $highlight);
        $ref = $writer->getPageReference($page);
        $dest->addPage($ref);
        $annot->put(PdfName::$DEST, $dest);
        return $annot;
    }

    /**
    * Add some free text to the document.
    * @param writer
    * @param rect
    * @param contents
    * @param defaultAppearance
    * @return A PdfAnnotation
    */
    public static function createFreeText(PdfWriter $writer, Rectangle $rect, $contents, PdfContentByte $defaultAppearance) {
        $annot = new PdfAnnotation($writer, $rect);
        $annot->put(PdfName::$SUBTYPE, PdfName::$FREETEXT);
        $annot->put(PdfName::$CONTENTS, new PdfString($contents, PdfObject::TEXT_UNICODE));
        $annot->setDefaultAppearanceString($defaultAppearance);
        return $annot;
    }

    /**
    * Adds a line to the document. Move over the line and a tooltip is shown.
    * @param writer
    * @param rect
    * @param contents
    * @param x1
    * @param y1
    * @param x2
    * @param y2
    * @return A PdfAnnotation
    */
    public static function createLine(PdfWriter $writer, Rectangle $rect, $contents, $x1, $y1, $x2, $y2) {
        $annot = new PdfAnnotation($writer, $rect);
        $annot->put(PdfName::$SUBTYPE, PdfName::$LINE);
        $annot->put(PdfName::$CONTENTS, new PdfString($contents, PdfObject::TEXT_UNICODE));
        $array = new PdfArray(new PdfNumber($x1));
        $array->add(new PdfNumber($y1));
        $array->add(new PdfNumber($x2));
        $array->add(new PdfNumber($y2));
        $annot->put(PdfName::$L, $array);
        return $annot;
    }

    /**
    * Adds a circle or a square that shows a tooltip when you pass over it.
    * @param writer
    * @param rect
    * @param contents The tooltip
    * @param square true if you want a square, false if you want a circle
    * @return A PdfAnnotation
    */
    public static function createSquareCircle(PdfWriter $writer, Rectangle $rect, $contents, $square) {
        $annot = new PdfAnnotation($writer, $rect);
        if ($square == TRUE)
            $annot->put(PdfName::$SUBTYPE, PdfName::$SQUARE);
        else
            $annot->put(PdfName::$SUBTYPE, PdfName::$CIRCLE);
        $annot->put(PdfName::$CONTENTS, new PdfString($contents, PdfObject::TEXT_UNICODE));
        return $annot;
    }

     public static function createMarkup(PdfWriter $writer, Rectangle $rect, $contents, $type, array $quadPoints) {
        $annot = new PdfAnnotation($writer, $rect);
        $name = PdfName::$HIGHLIGHT;
        switch ($type) {
            case PdfAnnotation::MARKUP_UNDERLINE:
                $name = PdfName::$UNDERLINE;
                break;
            case PdfAnnotation::MARKUP_STRIKEOUT:
                $name = PdfName::$STRIKEOUT;
                break;
        }
        $annot->put(PdfName::$SUBTYPE, $name);
        $annot->put(PdfName::$CONTENTS, new PdfString($contents, PdfObject::TEXT_UNICODE));
        PdfArray array = new PdfArray();
        for ($k = 0; k < count($quadPoints); ++$k)
            $array->add(new PdfNumber(q$uadPoints[$k]));
        $annot->put(PdfName::$QUADPOINTS, $array);
        return $annot;
    }

    /**
    * Adds a Stamp to your document. Move over the stamp and a tooltip is shown
    * @param writer
    * @param rect
    * @param contents
    * @param name
    * @return A PdfAnnotation
    */
    public static function createStamp(PdfWriter $writer, Rectangle $rect, $contents, $name) {
        $annot = new PdfAnnotation($writer, $rect);
        $annot->put(PdfName::$SUBTYPE, PdfName::$STAMP);
        $annot->put(PdfName::$CONTENTS, new PdfString($contents, PdfObject::TEXT_UNICODE));
        $annot->put(PdfName::$NAME, new PdfName($name));
        return $annot;
    }

     public static PdfAnnotation createInk(PdfWriter $writer, Rectangle $rect, $contents, array $inkList) {
        $annot = new PdfAnnotation($writer, $rect);
        $annot->put(PdfName::$SUBTYPE, PdfName::$INK);
        $annot->put(PdfName::$CONTENTS, new PdfString($contents, PdfObject::TEXT_UNICODE));
        $outer = new PdfArray();
        for ($k = 0; $k < count($inkList); ++$k) {
            $inner = new PdfArray();
            $deep = $inkList[$k];
            for ($j = 0; $j < count($deep); ++$j)
                $inner->add(new PdfNumber($deep[$j]));
            $outer->add($inner);
        }
        $annot->put(PdfName::$INKLIST, $outer);
        return $annot;
    }

    public static function createFileAttachment()
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
                return PdfAnnotation::createFileAtachment4args($arg1, $arg2, $arg3, $arg4);
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
                return PdfAnnotation::createFileAtachment6args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
                break;
            }
        }
    }

    /** Creates a file attachment annotation.
    * @param writer the <CODE>PdfWriter</CODE>
    * @param rect the dimensions in the page of the annotation
    * @param contents the file description
    * @param fileStore an array with the file. If it's <CODE>null</CODE>
    * the file will be read from the disk
    * @param file the path to the file. It will only be used if
    * <CODE>fileStore</CODE> is not <CODE>null</CODE>
    * @param fileDisplay the actual file name stored in the pdf
    * @throws IOException on error
    * @return the annotation
    */    
    private static function createFileAttachment6args(PdfWriter $writer, Rectangle $rect, $contents, $fileStore, $file, $fileDisplay) 
    {
        return createFileAttachment($writer, $rect, $contents, PdfFileSpecification::fileEmbedded($writer, $file, $fileDisplay, $fileStore));
    }

    /** Creates a file attachment annotation
    * @param writer
    * @param rect
    * @param contents
    * @param fs
    * @return the annotation
    * @throws IOException
    */
    private static function createFileAttachment4args(PdfWriter $writer, Rectangle $rect, $contents, PdfFileSpecification $fs) 
    {
        $annot = new PdfAnnotation($writer, $rect);
        $annot->put(PdfName::$SUBTYPE, PdfName::$FILEATTACHMENT);
        $annot->put(PdfName::$CONTENTS, new PdfString($contents, PdfObject::TEXT_UNICODE));
        $annot->put(PdfName::$FS, $fs->getReference());
        return $annot;
    }

    /**
    * Adds a popup to your document.
    * @param writer
    * @param rect
    * @param contents
    * @param open
    * @return A PdfAnnotation
    */
    public static function createPopup(PdfWriter $writer, Rectangle $rect, $contents, $open) {
        $annot = new PdfAnnotation($writer, $rect);
        $annot->put(PdfName::$SUBTYPE, PdfName::$POPUP);
        if ($contents != NULL)
            $annot->put(PdfName::$CONTENTS, new PdfString($contents, PdfObject::TEXT_UNICODE));
        if ($open == TRUE)
            $annot->put(PdfName::$OPEN, PdfBoolean::$PDFTRUE);
        return $annot;
    }

     public function setDefaultAppearanceString(PdfContentByte $cb) {
        $b = $cb->getInternalBuffer();
        $len = itextphp_bytes_getSize($b);
        for ($k = 0; $k < $len; ++$k) {
            if (itextphp_bytes_equalsAnotherChar($b, $k, '\n') == TRUE)
                itextphp_bytes_update($b, $k, 32);
        }
        put(PdfName::$DA, new PdfString($b));
    }

    public function setFlags($flags) {
        if ($flags == 0)
            remove(PdfName::$F);
        else
            put(PdfName::$F, new PdfNumber($flags));
    }

    public function setBorder(PdfBorderArray $border) {
        putDel(PdfName::$BORDER, $border);
    }

    public void setBorderStyle(PdfBorderDictionary $border) {
        putDel(PdfName::$BS, $border);
    }

    /**
    * Sets the annotation's highlighting mode. The values can be
    * <CODE>HIGHLIGHT_NONE</CODE>, <CODE>HIGHLIGHT_INVERT</CODE>,
    * <CODE>HIGHLIGHT_OUTLINE</CODE> and <CODE>HIGHLIGHT_PUSH</CODE>;
    * @param highlight the annotation's highlighting mode
    */
    public function setHighlighting(PdfName $highlight) {
        if ($highlight->equals(PdfAnnotation::$HIGHLIGHT_INVERT) == TRUE)
            remove(PdfName::$H);
        else
            put(PdfName::$H, $highlight);
    }
    
    public function setAppearance()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                setAppearance2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                $arg3 = func_get_arg(2); 
                setAppearance3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    private function setAppearance2args(PdfName $ap, PdfTemplate $template) {
        $dic = get(PdfName::$AP);
        if ($dic == NULL)
            $dic = new PdfDictionary();
        $dic->put($ap, $template->getIndirectReference());
        put(PdfName::$AP, $dic);
        if ($form == FALSE)
            return;
        if ($templates == NULL)
            $templates = array();
        $templates[$template] = NULL;
    }

    private function setAppearance3args(PdfName $ap, $state, PdfTemplate $template) {
        $dicAp = get(PdfName::$AP);
        if ($dicAp == NULL)
            $dicAp = new PdfDictionary();

        $dic = NULL;
        $obj = $dicAp->get($ap);
        if ($obj != NULL && $obj->isDictionary() == TRUE)
            $dic = $obj;
        else
            $dic = new PdfDictionary();
        $dic->put(new PdfName($state), $template->getIndirectReference());
        $dicAp->put($ap, $dic);
        put(PdfName::$AP, $dicAp);
        if ($form == FALSE)
            return;
        if ($templates == NULL)
            $templates = array();
        $templates[$template] = NULL;
    }

     public function setAppearanceState($state) {
        if ($state == NULL) {
            remove(PdfName::$AS);
            return;
        }
        put(PdfName::$AS, new PdfName($state));
    }

    public function setColor(Color $color) {
        putDel(PdfName::$C, new PdfColor($color));
    }

    public function setTitle($title) {
        if ($title == NULL) {
            remove(PdfName::$T);
            return;
        }
        put(PdfName::$T, new PdfString($title, PdfObject::TEXT_UNICODE));
    }

    public function setPopup(PdfAnnotation $popup) {
        put(PdfName::$POPUP, $popup->getIndirectReference());
        $popup->put(PdfName::$PARENT, getIndirectReference());
    }

    public function setAction(PdfAction $action) {
        putDel(PdfName::$A, $action);
    }

    public function setAdditionalActions(PdfName $key, PdfAction $action) {
        $dic = NULL;
        $obj = get(PdfName::$AA);
        if ($obj != NULL && $obj->isDictionary() == TRUE)
            $dic = $obj;
        else
            $dic = new PdfDictionary();
        $dic->put($key, $action);
        put(PdfName::$AA, $dic);
    }

    /** Getter for property used.
    * @return Value of property used.
    */
    public function isUsed() {
        return $used;
    }

    /** Setter for property used.
    */
    function setUsed() {
        $used = TRUE;
    }

    function getTemplates() {
        return $templates;
    }

    /** Getter for property form.
    * @return Value of property form.
    */
    public function isForm() {
        return $form;
    }

    /** Getter for property annotation.
    * @return Value of property annotation.
    */
    public function isAnnotation() {
        return $annotation;
    }

    public function setPage($page) {
        put(PdfName::$P, $writer->getPageReference($page));
    }

    public function setPage() {
        put(PdfName::$P, $writer->getCurrentPage());
    }

    /** Getter for property placeInPage.
    * @return Value of property placeInPage.
    */
    public function getPlaceInPage() {
        return $placeInPage;
    }

    /** Places the annotation in a specified page that must be greater
    * or equal to the current one. With <code>PdfStamper</code> the page
    * can be any. The first page is 1.
    * @param placeInPage New value of property placeInPage.
    */
    public function setPlaceInPage($placeInPage) {
        $this->placeInPage = $placeInPage;
    }

     public void setRotate(int v) {
        put(PdfName.ROTATE, new PdfNumber(v));
    }

    function getMK() {
        $mk = get(PdfName::$MK);
        if ($mk == NULL) {
            $mk = new PdfDictionary();
            put(PdfName::$MK, $mk);
        }
        return $mk;
    }

    public function setMKRotation($rotation) {
        getMK()->put(PdfName::$R, new PdfNumber($rotation));
    }

    public static function getMKColor(Color $color) {
        $array = new PdfArray();
        $type = ExtendedColor::getType($color);
        switch ($type) {
            case ExtendedColor::TYPE_GRAY: {
                $array->add(new PdfNumber((($color)->getGray()));
                break;
            }
            case ExtendedColor::TYPE_CMYK: {
                $cmyk = $color;
                $array->add(new PdfNumber($cmyk->getCyan()));
                $array->add(new PdfNumber($cmyk->getMagenta()));
                $array->add(new PdfNumber($cmyk->getYellow()));
                $array->add(new PdfNumber($cmyk->getBlack()));
                break;
            }
            case ExtendedColor::TYPE_SEPARATION:
            case ExtendedColor::TYPE_PATTERN:
            case ExtendedColor::TYPE_SHADING:
                throw new Exception("Separations, patterns and shadings are not allowed in MK dictionary.");
            default:
                $array->add(new PdfNumber($color->getRed() / 255.0));
                $array->add(new PdfNumber($color->getGreen() / 255.0));
                $array->add(new PdfNumber($color->getBlue() / 255.0));
        }
        return array;
    }

    public function setMKBorderColor(Color $color) {
        if ($color == NULL)
            getMK()->remove(PdfName::$BC);
        else
            getMK()->put(PdfName::$BC, getMKColor($color));
    }

    public function setMKBackgroundColor(Color $color) {
        if ($color == NULL)
            getMK()->remove(PdfName::$BG);
        else
            getMK()->put(PdfName::$BG, getMKColor($color));
    }

    public function setMKNormalCaption($caption) {
        getMK()->put(PdfName::$CA, new PdfString($caption, PdfObject::TEXT_UNICODE));
    }

    public function setMKRolloverCaption($caption) {
        getMK()->put(PdfName::$RC, new PdfString($caption, PdfObject::TEXT_UNICODE));
    }

    public function setMKAlternateCaption($caption) {
        getMK()->put(PdfName::$AC, new PdfString($caption, PdfObject::TEXT_UNICODE));
    }

    public function setMKNormalIcon(PdfTemplate $template) {
        getMK()->put(PdfName::$I, template->getIndirectReference());
    }

    public function setMKRolloverIcon(PdfTemplate $template) {
        getMK()->put(PdfName::$RI, template->getIndirectReference());
    }

    public function setMKAlternateIcon(PdfTemplate $template) {
        getMK()->put(PdfName::$IX, template->getIndirectReference());
    }

    public function setMKIconFit(PdfName $scale, PdfName $scalingType, $leftoverLeft, $leftoverBottom, $fitInBounds) {
        $dic = new PdfDictionary();
        if ($scale->equals(PdfName::$A) == FALSE)
            $dic->put(PdfName::$SW, $scale);
        if ($scalingType->equals(PdfName::$P) == FALSE)
            $dic->put(PdfName::$S, $scalingType);
        if ($leftoverLeft != 0.5 || $leftoverBottom != 0.5) {
            $array = new PdfArray(new PdfNumber($leftoverLeft));
            $array->add(new PdfNumber($leftoverBottom));
            $dic->put(PdfName::$A, $array);
        }
        if ($fitInBounds == TRUE)
            $dic->put(PdfName::$FB, PdfBoolean::$PDFTRUE);
        getMK()->put(PdfName::$IF, $dic);
    }

    public function setMKTextPosition($tp) {
        getMK()->put(PdfName::$TP, new PdfNumber($tp));
    }

    /**
    * Sets the layer this annotation belongs to.
    * @param layer the layer this annotation belongs to
    */
    public function setLayer(PdfOCG $layer) {
        put(PdfName::$OC, $layer->getRef());
    }

}
?>