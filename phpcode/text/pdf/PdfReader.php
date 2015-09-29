<?PHP
/*
 * $Id: PdfReader.php,v 1.1.1.1 2005/09/22 16:10:04 mstaylor Exp $
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
require_once("../PageSize.php");
require_once("../StringCompare.php");
require_once("PdfName.php");
require_once("PdfWriter.php");
require_once("PdfEncodings.php");
require_once("PRAcroForm.php");
require_once("PdfEncryption.php");
require_once("PRTokeniser.php");
require_once("RandomAccessFileOrArray.php");
require_once("PdfReaderInstance.php");
require_once("PdfObject,php");
require_once("PdfNumber.php");
require_once("PdfArray.php");
require_once("PdfDictionary.php");
require_once("PdfString.php");
require_once("PRIndirectReference.php");
require_once("PdfNull.php");
require_once("PdfBoolean.php");
require_once("PRStream.php");
require_once("LZWDecoder.php");
require_once("PdfNameTree.php");
require_once("SequenceList.php");
require_once("AcroFields.php");
require_once("../DocWriter.php");

require_once("../../exceptions/IOException.php");
require_once("../../util/StringHelpers.php");

class PdfReader
{
    static $pageInhCandidates = NULL;
    static $vpnames = NULL;
    static $vpints = array(PdfWriter::HideToolbar, PdfWriter::HideMenubar,
        PdfWriter::HideWindowUI, PdfWriter::FitWindow, PdfWriter::CenterWindow, PdfWriter::DisplayDocTitle);
    static $endstream = NULL;
    static $endobj = NULL;
    protected $tokens;
    // Each xref pair is a position
    // type 0 -> -1, 0
    // type 1 -> offset, 0
    // type 2 -> index, obj num
    protected $xref = array();
    protected $objStmMark = array();
    protected $newXrefType;
    protected $xrefObj = array();
    protected $trailer;
    protected $pages = array();
    protected $catalog;
    protected $pageRefs = array();
    protected $acroForm = NULL;
    protected $acroFormParsed = FALSE;
    protected $pageInh = array();
    protected $encrypted = FALSE;
    protected $rebuilt = FALSE;
    protected $freeXref;
    protected $tampered = FALSE;
    protected $lastXref;
    protected $eofPos;
    protected $pdfVersion;
    protected $decrypt;
    protected $password = NULL; //added by ujihara for decryption
    protected $strings = array();
    protected $sharedStreams = TRUE;
    protected $consolidateNamedDestinations = FALSE;
    protected $rValue;
    protected $pValue;
    private $objNum;
    private $objGen;
    private $visited = array();
    private $newHits = array();
    private $fileLength;
    private $hybridXref;
    /**
    * Holds value of property appendable.
    */
    private $appendable;

    public static $initialized = FALSE;


    public static void initializeStatics()
    {
        if(PdfReader::$initialized == FALSE)
        {
            PdfReader::$pageInhCandidates = array( PdfName::$MEDIABOX, PdfName::$ROTATE, PdfName::$RESOURCES, PdfName::$CROPBOX);
            PdfReader::$vpnames = array(PdfName::$HIDETOOLBAR, PdfName::$HIDEMENUBAR,
        PdfName::$HIDEWINDOWUI, PdfName::$FITWINDOW, PdfName::$CENTERWINDOW, PdfName::$DISPLAYDOCTITLE);
            PdfReader::$endstream = PdfEncodings::convertToBytes("endstream", NULL);
            PdfReader::$endobj = PdfEncodings::convertToBytes("endobj", NULL);
            PdfReader::$initialized = TRUE;
        }
    }

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (strcmp(gettype($arg1),"string") == 0)
                {
                    construct1argString($arg1);
                }
                else if (strcmp(gettype($arg1),"resource") == 0)
                {
                    construct1argbyte($arg1);
                }
                else
                {
                    construct1arg($arg1);//arg is pdfreader
                }
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (strcmp(gettype($arg1),"string") == 1)
                {
                    construct2argsString($arg1$, arg2);
                }
                break;
            }
        }
    }

    /** Reads and parses a PDF document.
    * @param filename the file name of the document
    * @throws IOException on error
    */
    private function construct1argString($filename)
    {
        construct2argsString($filename, NULL);
    }


    /** Reads and parses a PDF document.
    * @param pdfIn the byte array with the document
    * @throws IOException on error
    */

    private function construct1argbyte($pdfIn)
    {
        construct2argsbyte($pdfIn, NULL);
    }

    /** Creates an independent duplicate.
    * @param reader the <CODE>PdfReader</CODE> to duplicate
    */
    private function construct1arg(PdfReader $reader) {
        $this->appendable = $reader->appendable;
        $this->consolidateNamedDestinations = $reader->consolidateNamedDestinations;
        $this->encrypted = $reader->encrypted;
        $this->rebuilt = $reader->rebuilt;
        $this->sharedStreams = $reader->sharedStreams;
        $this->tampered = $reader->tampered;
        $this->password = $reader->password;
        $this->pdfVersion = $reader->pdfVersion;
        $this->eofPos = $reader->eofPos;
        $this->freeXref = $reader->freeXref;
        $this->lastXref = $reader->lastXref;
        $this->tokens = $reader->tokens;
        $this->decrypt = $reader->decrypt;
        $this->pValue = $reader->pValue;
        $this->rValue = $reader->rValue;
        $this->xrefObj = array($reader->xrefObj);
        for ($k = 0; k < count($reader->xrefObj); ++$k) {
            $this->xrefObj[$k] = duplicatePdfObject($reader->xrefObj[k], $this);
        }
        $this->pageRefs = array($reader->pageRefs);
        $this->pages = array($reader->pages);
        for ($k = 0; $k < count($reader->pageRefs); ++$k) {
            $this->pageRefs[$k] = duplicatePdfObject($reader->pageRefs[$k], $this);
            $this->pages[$k] = getPdfObject($this->pageRefs[$k]);
        }
        $this->trailer = duplicatePdfObject($reader->trailer, $this);
        $this->catalog = getPdfObject($trailer[PdfName::$ROOT]);
        $this->fileLength = $reader->fileLength;
    }

    /** Reads and parses a PDF document.
    * @param filename the file name of the document
    * @param ownerPassword the password to read the document
    * @throws IOException on error
    */
    private function construct2argsString($filename, $ownerPassword) 
    {
        $password = $ownerPassword;
        $tokens = new PRTokeniser($filename);
        readPdf();
    }

    /** Reads and parses a PDF document.
    * @param pdfIn the byte array with the document
    * @param ownerPassword the password to read the document
    * @throws IOException on error
    */
    private function construct2argsbyte($pdfIn, $ownerPassword)
    {
        $password = $ownerPassword;
        $tokens = new PRTokeniser($pdfIn);
        readPdf();
    }


    /** Gets a new file instance of the original PDF
    * document.
    * @return a new file instance of the original PDF document
    */
    public function getSafeFile() {
        return $tokens->getSafeFile();
    }


    protected function getPdfReaderInstance(PdfWriter $writer) {
        return new PdfReaderInstance($this, $writer, $xrefObj, $pages);
    }


    /** Gets the number of pages in the document.
    * @return the number of pages in the document
    */
    public function getNumberOfPages() {
        return count($pages);
    }

    /** Returns the document's catalog. This dictionary is not a copy,
    * any changes will be reflected in the catalog.
    * @return the document's catalog
    */
    public function getCatalog() {
        return catalog;
    }



    /** Returns the document's acroform, if it has one.
    * @return he document's acroform
    */
    public Function getAcroForm() {
        if ($acroFormParsed == FALSE) {
            $acroFormParsed = TRUE;
            $form = $catalog->get(PdfName::$ACROFORM);
            if ($form != NULL) {
                try {
                    $acroForm = new PRAcroForm($this);
                    $acroForm->readAcroForm(getPdfObject($form));
                }
                catch (Exception $e) {
                    $acroForm = NULL;
                }
            }
        }
        return $acroForm;
    }


    /**
    * Gets the page rotation. This value can be 0, 90, 180 or 270.
    * @param index the page number. The first page is 1
    * @return the page rotation
    */
    public function getPageRotation($index) {
        return getPageRotation(pages[$index - 1]);
    }

    function getPageRotation(PdfDictionary $page) {
        $rotate = getPdfObject($page->get(PdfName::$ROTATE);
        if ($rotate == NULL)
            return 0;
        else {
            $n = $rotate->intValue();
            $n = $n % 360;
            return n < 0 ? $n + 360 : $n;
        }
    }


    /** Gets the page size, taking rotation into account. This
    * is a <CODE>Rectangle</CODE> with the value of the /MediaBox and the /Rotate key.
    * @param index the page number. The first page is 1
    * @return a <CODE>Rectangle</CODE>
    */
    public function getPageSizeWithRotation($index) {
        return getPageSizeWithRotation($pages[$index - 1]);
    }

    public function getPageSizeWithRotation(PdfDictionary $page) {
        $rect = getPageSize($page);
        $rotation = getPageRotation($page);
        while ($rotation > 0) {
            $rect = $rect->rotate();
            $rotation  = $rotation - 90;
        }
        return rect;
    }


    /** Gets the page size without taking rotation into account. This
    * is the value of the /MediaBox key.
    * @param index the page number. The first page is 1
    * @return the page size
    */
    public function getPageSize($index) {
        return getPageSize($pages[$index - 1]);
    }

    public function getPageSize(PdfDictionary $page) {
        $mediaBox = getPdfObject($page->get(PdfName::$MEDIABOX));
        return getNormalizedRectangle($mediaBox);
    }

    /** Gets the crop box without taking rotation into account. This
    * is the value of the /CropBox key. The crop box is the part
    * of the document to be displayed or printed. It usually is the same
    * as the media box but may be smaller. If the page doesn't have a crop
    * box the page size will be returned.
    * @param index the page number. The first page is 1
    * @return the crop box
    */
    public function getCropBox($index) {
        $page = $pages[$index - 1];
        $cropBox = getPdfObject($page->get(PdfName::$CROPBOX));
        if ($cropBox == NULL)
            return getPageSize($index);
        return getNormalizedRectangle($cropBox);
    }


    /** Gets the box size. Allowed names are: "crop", "trim", "art", "bleed" and "media".
    * @param index the page number. The first page is 1
    * @param boxName the box name
    * @return the box rectangle or null
    */
    public function getBoxSize($index, $boxName) {
        $page = pages[$index - 1];
        $box = NULL;
        if (strcmp(boxName, "trim") == 0)
            $box = getPdfObject($page->get(PdfName::$TRIMBOX));
        else if (strcmp($boxName, "art") == 0)
            $box = getPdfObject($page->get(PdfName::$ARTBOX));
        else if (strcmp($boxName, "bleed") == 0)
            $box = getPdfObject($page->get(PdfName::$BLEEDBOX));
        else if (strcmp(boxName, "crop") == 0)
            $box = getPdfObject($page->get(PdfName::$CROPBOX));
        else if (strcmp($boxName, "media") == 0)
            $box = getPdfObject($page->get(PdfName::$MEDIABOX));
        if ($box == NULL)
            return NULL;
        return getNormalizedRectangle($box);
    }

    /** Returns the content of the document information dictionary as a <CODE>HashMap</CODE>
    * of <CODE>String</CODE>.
    * @return content of the document information dictionary
    */
    public function getInfo() {
        $map = array();
        $info = getPdfObject($trailer->get(PdfName::$INFO));
        if ($info == NULL)
            return $map;
        foreach ($info->getKeys() as &$key) {
            $obj = getPdfObject($info->get($key));
            if ($obj == NULL)
                continue;
            $value = $obj->toString();
            switch ($obj->type()) {
                case PdfObject::STRING: {
                    $value = $obj->toUnicodeString();
                    break;
                }
                case PdfObject::NAME: {
                    $value = PdfName::decodeName($value);
                    break;
                }
            }
            $map[PdfName.decodeName($key)] = $value;
        }
        return $map;
    }



    /** Normalizes a <CODE>Rectangle</CODE> so that llx and lly are smaller than urx and ury.
    * @param box the original rectangle
    * @return a normalized <CODE>Rectangle</CODE>
    */
    public static function getNormalizedRectangle($box) {
        $rect = $box->getArrayList();
        $llx =  $rect[0]->floatValue();
        $lly =  $rect[1]->floatValue();
        $urx =  $rect[2]->floatValue();
        $ury =  $rect[3]->floatValue();
        return new Rectangle(min($llx, $urx), min($lly, $ury),
        max($llx, $urx), max($lly, $ury));
    }


    protected function readPdf()
    {
        try {
            $fileLength = $tokens->getFile()->length();
            $pdfVersion = $tokens->checkPdfHeader();
            try {
                readXref();
            }
            catch (Exception $e) {
                try {
                    $rebuilt = TRUE;
                    rebuildXref();
                    $lastXref = -1;
                }
                catch (Exception $ne) {
                    throw new IOException("Rebuild failed: " . $ne->getMessage() . "; Original message: " . $e->getMessage());
                }
            }
            try {
                readDocObj();
            }
            catch (IOException $ne) {
                if ($rebuilt == TRUE)
                    throw $ne;
                $rebuilt = TRUE;
                $encrypted = FALSE;
                rebuildXref();
                $lastXref = -1;
                readDocObj();
            }
            
            $strings = array();
            readPages();
            eliminateSharedStreams();
            removeUnusedObjects();
        }
        catch (Exception $e){}
        
            try {
                $tokens->close();
            }
            catch (Exception $e) {
                // empty on purpose
            }
        
    }


    private function equalsArray($ar1, $ar2, $size) {
        for ($k = 0; $k < $size; ++$k) {
            if (itextphp_bytes_equalsoperator($ar1, $ar2, $k, $k) == FALSE)
                return FALSE;
        }
        return TRUE;
    }


    /**
    */
    private function readDecryptedDocObj()  {
        if ($encrypted == TRUE)
            return;
        $encDic = $trailer->get(PdfName::$ENCRYPT);
        if ($encDic == NULL || strcmp($encDic->toString(), "null") == 0)
            return;
        $encrypted = TRUE;
        $enc = getPdfObject($encDic);

        $s = "";
        $o = NULL;
        
        $documentIDs = getPdfObject($trailer->get(PdfName::$ID));
        $documentID = null;
        if ($documentIDs != NULL) {
            $o = $documentIDs->getArrayList()[0];
            $s = $o->toString();
            $documentID = DocWriter::getISOBytes($s);
        }

        $s = $enc->get(PdfName::$U)->toString();
        $uValue = DocWriter::getISOBytes($s);
        $s = $enc->get(PdfName::$O)->toString();
        $oValue = DocWriter::getISOBytes($s);

        $o = $enc->get(PdfName::$R);
        if ($o->isNumber() == FALSE) throw new IOException("Illegal R value.");
        $rValue = $o->intValue();
        if ($rValue != 2 && $rValue != 3) throw new IOException("Unknown encryption type (" . $rValue . ")");

        $o = $enc->get(PdfName::$P);
        if ($o->isNumber() == FALSE) throw new IOException("Illegal P value.");
        $pValue = $o->intValue();

        $decrypt = new PdfEncryption();

        //check by user password
        $decrypt->setupByUserPassword($documentID, $password, $oValue, $pValue, $rValue == 3);
        if (equalsArray($uValue, $decrypt->userKey, $rValue == 3 ? 16 : 32) == FALSE) {
            //check by owner password
            $decrypt->setupByOwnerPassword($documentID, $password, $uValue, $oValue, $pValue, $rValue == 3);
            if (itextphp_bytes_equalsoperatorObject($uValue, $decrypt->userKey) == FALSE) {
                throw new IOException("Bad user password");
            }
        }
        for ($k = 0; $k < count($strings); ++$k) {
            $str = $strings[$k];
            $str->decrypt($this);
        }
        if ($encDic->isIndirect())
            $xrefObj[$encDic=>getNumber()] = NULL;
    }


    public static function getPdfObject()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                return getPdfObject1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return getPdfObject2args($arg1, $arg2);
                break;
            }
        }
    }

    /** Reads a <CODE>PdfObject</CODE> resolving an indirect reference
    * if needed.
    * @param obj the <CODE>PdfObject</CODE> to read
    * @return the resolved <CODE>PdfObject</CODE>
    */
    private function getPdfObject1arg(PdfObject $obj)
    {
        if ($obj == NULL)
            return NULL;
        if ($obj->isIndirect() == FALSE)
            return $obj;
        $ref = $obj;
        $idx = $ref->getNumber();
        $appendable = $ref->getReader()->appendable;
        $obj = $ref->getReader()->xrefObj[$idx];
        if ($obj == NULL) {
            if ($appendable) {
                $obj = new PdfNull();
                $obj->setIndRef($ref);
                return $obj;
            }
            else
                return PdfNull::$PDFNULL;
        }
        else {
            if ($appendable) {
                switch ($obj->type()) {
                    case PdfObject::NULL:
                        $obj = new PdfNull();
                        break;
                    case PdfObject::BOOLEAN:
                        $obj = new PdfBoolean($obj->booleanValue());
                        break;
                    case PdfObject::NAME:
                        $obj = new PdfName($obj->getBytes());
                        break;
                }
                $obj->setIndRef($ref);
            }
            return $obj;
        }
    }


    private function getPdfObject2arg(PdfObject $obj, PdfObject $parent)
    {
        if ($obj == NULL)
            return NULL;
        if ($obj->isIndirect() == FALSE) {
            $ref = NULL;
            if ($parent != NULL && ($ref = $parent->getIndRef()) != NULL && $ref->getReader()->isAppendable()) {
                switch ($obj->type()) {
                    case PdfObject::NULL:
                        $obj = new PdfNull();
                        break;
                    case PdfObject::BOOLEAN:
                        $obj = new PdfBoolean($obj->booleanValue());
                        break;
                    case PdfObject::NAME:
                        $obj = new PdfName($obj->getBytes());
                        break;
                }
                obj->setIndRef($ref);
            }
            return $obj;
        }
        return getPdfObject($obj);
    }


    protected function pushPageAttributes(PdfDictionary $nodePages) {
        $dic = new PdfDictionary();
        if (count($pageInh) != 0) {
            $dic->putAll($pageInh[count($pageInh) - 1]);
        }
        for ($k = 0; $k < count($pageInhCandidates); ++$k) {
            $obj = $nodePages->get($pageInhCandidates[$k]);
            if ($obj != NULL)
                $dic->put($pageInhCandidates[$k], $obj);
        }
        array_push($pageInh, $dic);
    }

    protected function popPageAttributes() {
        unset($pageInh[count($pageInh) - 1)]);
    }

    protected function iteratePages(PRIndirectReference $rpage) 
    {
        $page = getPdfObject($rpage);
        $kidsPR = getPdfObject($page->get(PdfName::$KIDS));
        if ($kidsPR == NULL) {
            $page->put(PdfName::$TYPE, PdfName::$PAGE);
            $dic = $pageInh[count($pageInh) - 1];
            $key = NULL;
            foreach ($dic->getKeys() as &$key) {
                if ($page->get($key) == NULL)
                    $page->put($key, $dic[$key]);
            }
            if ($page->get(PdfName::$MEDIABOX) == NULL) {
                $arr = new PdfArray(array(0,0,PageSize::$LETTER->right(),PageSize::$LETTER->top()));
                $page->put(PdfName::MEDIABOX, $arr);
            }
            array_push($pages, $page);
            array_push($pageRefs, $rpage);
        }
        else {
            $page->put(PdfName::$TYPE, PdfName::$PAGES);
            pushPageAttributes($page);
            $kids = $kidsPR->getArrayList();
            for ($k = 0; k < count($kids); ++$k){
                iteratePages($kids[$k]);
            }
            popPageAttributes();
        }
    }

    protected function readPages()
    {
        $pageInh = array();
        $catalog = getPdfObject($trailer->get(PdfName::$ROOT));
        $rootPages = getPdfObject($catalog->get(PdfName::$PAGES));
        $pages = array();
        $pageRefs = array();
        iteratePages($catalog->get(PdfName::$PAGES));
        $pageInh = NULL;
        $rootPages->put(PdfName::$COUNT, new PdfNumber(count($pages)));
    }

     protected function PRSimpleRecursive(PdfObject $obj)  {
        switch ($obj->type()) {
            case PdfObject::DICTIONARY:
            case PdfObject::STREAM:
                $dic = $obj;
                foreach ($dic->getKeys() as &$key) {
                    PRSimpleRecursive($dic->get($key));
                }
                break;
            case PdfObject::ARRAY:
                $list = $obj->getArrayList();
                for ($k = 0; $k < count($list); ++$k) {
                    PRSimpleRecursive($list[$k]);
                }
                break;
            case PdfObject::INDIRECT:
                $ref = $obj;
                $num = $ref->getNumber();
                if ($visited[$num] == FALSE) {
                    $visited[$num] = TRUE;
                    $newhits[$num] = 1;
                }
                break;
        }
    }

    protected functio readDocObj() 
    {
        $streams = array();
        $xrefObj = array();
        array_pad($xrefObj, count($xref)/2, NULL);
        for ($k = 2; $k < count($xref); $k += 2) {
            $pos = $xref[$k];
            if ($pos <= 0 || $xref[$k + 1] > 0)
                continue;
            $tokens->seek($pos);
            $tokens->nextValidToken();
            if ($tokens->getTokenType() != PRTokeniser::TK_NUMBER)
                $tokens->throwError("Invalid object number.");
            $objNum = $tokens->intValue();
            $tokens->nextValidToken();
            if ($tokens->getTokenType() != PRTokeniser::TK_NUMBER)
                $tokens->throwError("Invalid generation number.");
            $objGen = $tokens->intValue();
            $tokens->nextValidToken();
            if (strcmp($tokens->getStringValue(),"obj") != 0)
                $tokens->throwError("Token 'obj' expected.");
            $obj = NULL;
            try {
                $obj = readPRObject();
                if ($obj->isStream()) {
                    array_push($streams, $obj);
                }
            }
            catch (Exception $e) {
                $obj = NULL;
            }
            $xrefObj[$k / 2] = $obj;
        }
        $fileLength = $tokens->length();
        $tline = itextphp_bytes_create(16);
        for ($k = 0; $k < count($streams); ++$k) {
            $stream = $streams[$k];
            $length = getPdfObject($stream->get(PdfName::$LENGTH));
            $start = $stream->getOffset();
            $streamLength = $length->intValue();
            $calc = FALSE;
            if ($streamLength + $start > $fileLength - 20)
                $calc = TRUE;
            else {
                $tokens->seek($start + $streamLength);
                $line = $tokens->readString(20);
                if (beginsWith($line, "\nendstream") == FALSE &&
                beginsWith($line, "\r\nendstream")== FALSE &&
                beginsWith($line, "\rendstream") == FALSE&&
                beginsWith($line, "endstream") == FALSE)
                    $calc = TRUE;
            }
            if ($calc == TRUE) {
                $tokens->seek($start);
                while (TRUE) {
                    $pos = $tokens->getFilePointer();
                    if ($tokens->readLineSegment($tline) == FALSE)
                        break;
                    if (equalsn($tline, $endstream) == TRUE) {
                        $streamLength = $pos - $start;
                        break;
                    }
                    if (equalsn($tline, $endobj) == TRUE) {
                        $tokens->seek($pos - 16);
                        $s = $tokens->readString(16);
                        $index = strpos($s, "endstream");
                        if ($index >= 0)
                            $pos = $pos - 16 + $index;
                        $streamLength = $pos - $start;
                        break;
                    }
                }
            }
            $stream->setLength($streamLength);
        }
        readDecryptedDocObj();
        if ($objStmMark != NULL) {
            foreach (array_keys($objStmMark) as &$key) {
                $n = $key->intValue();
                $h = $objStmMark[$key];
                readObjStm($xrefObj[$n], $h);
                $xrefObj[$n] = NULL;
            }
            $objStmMark = null;
        }
        $xref = null;
    }

    protected function readObjStm(PRStream $stream, array $map) 
    {
        $first = (getPdfObject($stream->get(PdfName::$FIRST)))->intValue();
        $n = (getPdfObject($stream->get(PdfName::$N)))->intValue();
        $b = getStreamBytes($stream, $tokens->getFile());
        $saveTokens = $tokens;
        $tokens = new PRTokeniser($b);
        try {
            $address = array();
            array_pad($address,$n,0);
            $objNumber = array()
            array_pad($$objNumber, $n, 0);
            $ok = TRUE;
            for ($k = 0; $k < $n; ++$k) {
                $ok = $tokens->nextToken();
                if ($ok == FALSE)
                    break;
                if ($tokens->getTokenType() != PRTokeniser::TK_NUMBER) {
                    $ok = FALSE;
                    break;
                }
                $objNumber[$k] = $tokens->intValue();
                $ok = $tokens->nextToken();
                if ($ok == FALSE)
                    break;
                if ($tokens->getTokenType() != PRTokeniser::TK_NUMBER) {
                    $ok = FALSE;
                    break;
                }
                $address[$k] = $tokens->intValue() + $first;
            }
            if ($ok == FALSE)
                throw new IOException("Error reading ObjStm");
            for ($k = 0; $k < $n; ++$k) {
                if (array_key_exists($k, $map) == TRUE) {
                    $tokens->seek($address[$k]);
                    $obj = readPRObject();
                    $xrefObj[$objNumber[$k]] = $obj;
                }
            }
        }
        catch {
            $tokens = $saveTokens;
        }

        $tokens = $saveTokens;
    }

    static function killIndirect(PdfObject $obj) {
        if ($obj == NULL || $obj->isNull())
            return NULL;
        $ret = getPdfObject($obj);
        if ($obj->isIndirect()) {
            $ref = $obj;
            $ref->getReader()->xrefObj[$ref->getNumber()] = NULL;
        }
        return $ret;
    }

     private function ensureXrefSize($size) {
        if ($size == 0)
            return;
        if ($xref == NULL)
        {
            $xref = array();
            array_pad($xref,$size, 0);
        }
        else {
            if ($count($xref) < $size) {
                $xref2 = array();
                array_pad($xref2,$size, 0);
                for ($k = 0; $k < count($xref); $k++)
                {
                    $xref2[$k] = $xref[$k];
                }

                $xref = $xref2;
            }
        }
    }

    protected function readXref()  {
        $hybridXref = FALSE;
        $newXrefType = FALSE;
        $tokens->seek($tokens->getStartxref());
        $tokens->nextToken();
        if (strcmp($tokens->getStringValue(),"startxref") != 0)
            throw new IOException("startxref not found.");
        $tokens->nextToken();
        if ($tokens->getTokenType() != PRTokeniser::TK_NUMBER)
            throw new IOException("startxref is not followed by a number.");
        $startxref = $tokens->intValue();
        $lastXref = $startxref;
        $eofPos = $tokens->getFilePointer();
        try {
            if (readXRefStream($startxref)) {
                $newXrefType = TRUE;
                return;
            }
        }
        catch (Exception $e) {}
        $xref = NULL;
        $tokens->seek($startxref);
        $trailer = readXrefSection();
        $trailer2 = $trailer;
        while (TRUE) {
            $prev = $trailer2->get(PdfName::$PREV);
            if ($prev == NULL)
                break;
            $tokens->seek($prev->intValue());
            $trailer2 = readXrefSection();
        }
    }


    protected function readXrefSection()  {
        $tokens->nextValidToken();
        if (strcmp($tokens->getStringValue(), "xref") != 0)
            $tokens->throwError("xref subsection not found");
        $start = 0;
        $end = 0;
        $pos = 0;
        $gen = 0;
        while (TRUE) {
            $tokens->nextValidToken();
            if (strcmp($tokens->getStringValue(),"trailer") == 0)
                break;
            if ($tokens->getTokenType() != PRTokeniser::TK_NUMBER)
                $tokens->throwError("Object number of the first object in this xref subsection not found");
            $start = $tokens->intValue();
            $tokens->nextValidToken();
            if ($tokens->getTokenType() != PRTokeniser::TK_NUMBER)
                $tokens->throwError("Number of entries in this xref subsection not found");
            $end = $tokens->intValue() + $start;
            if ($start == 1) { // fix incorrect start number
                $back = $tokens->getFilePointer();
                $tokens->nextValidToken();
                $pos = $tokens->intValue();
                $tokens->nextValidToken();
                $gen = $tokens->intValue();
                if ($pos == 0 && $gen == 65535) {
                    --$start;
                    --$end;
                }
                $tokens->seek($back);
            }
            ensureXrefSize($end * 2);
            for ($k = $start; $k < $end; ++$k) {
                $tokens->nextValidToken();
                $pos = $tokens->intValue();
                $tokens->nextValidToken();
                $gen = $tokens->intValue();
                $tokens->nextValidToken();
                $p = $k * 2;
                if (strcmp($tokens->getStringValue(),"n") == 0) {
                    if ($xref[$p] == 0 && $xref[$p + 1] == 0) {
//                        if (pos == 0)
//                            tokens.throwError("File position 0 cross-reference entry in this xref subsection");
                        $xref[$p] = $pos;
                    }
                }
                else if (strcmp($tokens->getStringValue(),"f") == 0) {
                    if ($xref[$p] == 0 && $xref[$p + 1] == 0)
                        $xref[$p] = -1;
                }
                else
                    $tokens->throwError("Invalid cross-reference entry in this xref subsection");
            }
        }
        $trailer = readPRObject();
        $xrefSize = $trailer->get(PdfName::$SIZE);
        ensureXrefSize($xrefSize->intValue() * 2);
        $xrs = $trailer->get(PdfName::$XREFSTM);
        if ($xrs != NULL && $xrs->isNumber()) {
            $loc = ($xrs)->intValue();
            try {
                readXRefStream($loc);
                $newXrefType = TRUE;
                $hybridXref = TRUE;
            }
            catch (IOException $e) {
                $xref = NULL;
                throw $e;
            }
        }
        return $trailer;
    }


     protected function readXRefStream($ptr) 
     {
        $tokens->seek($ptr);
        $thisStream = 0;
        if ($tokens->nextToken() == FALSE)
            return FALSE;
        if ($tokens->getTokenType() != PRTokeniser::TK_NUMBER)
            return FALSE;
        $thisStream = $tokens->intValue();
        if ($tokens->nextToken() == FALSE || $tokens->getTokenType() != PRTokeniser::TK_NUMBER)
            return FALSE;
        if ($tokens->nextToken() == FALSE || strcmp($tokens->getStringValue(), "obj") != 0)
            return FALSE;
        $object = readPRObject();
        $stm = NULL;
        if ($object->isStream()) {
            $stm = $object;
            if (strcmp($PdfName::$XREF->toString(), $stm->get(PdfName::$TYPE)) != 0)
                return FALSE;
        }
        if ($trailer == NULL) {
            $trailer = new PdfDictionary();
            $trailer->putAll($stm);
        }
        $stm->setLength(($stm->get(PdfName::$LENGTH))->intValue());
        $size = ($stm->get(PdfName::$SIZE))->intValue();
        $index;
        $obj = $stm->get(PdfName::$INDEX);
        if ($obj == NULL) {
            $index = new PdfArray();
            $index->add(array(0, $size));
        }
        else
            $index = $obj;
        $w = $stm->get(PdfName::$W);
        $prev = -1;
        $obj = $stm->get(PdfName::$PREV);
        if ($obj != NULL)
            $prev = ($obj)->intValue();
        // Each xref pair is a position
        // type 0 -> -1, 0
        // type 1 -> offset, 0
        // type 2 -> index, obj num
        ensureXrefSize($size * 2);
        if ($objStmMark == NULL)
            $objStmMark = array();
        $b = getStreamBytes($stm, $tokens->getFile());
        $bptr = 0;
        $wa = $w->getArrayList();
        $wc = array();
        array_pad($wc,3,0);
        for ($k = 0; $k < 3; ++$k)
            $wc[$k] = ($wa[$k])->intValue();
        $sections = $index->getArrayList();
        for ($idx = 0; $idx < count($sections); $idx += 2) {
            $start = ($sections[$idx])->intValue();
            $length = ($sections[$idx + 1])->intValue();
            ensureXrefSize(($start + $length) * 2);
            while ($length-- > 0) {
                $total = 0;
                $type = 1;
                if ($wc[0] > 0) {
                    $type = 0;
                    for ($k = 0; $k < $wc[0]; ++$k)
                        $type = ($type << 8) + (itextphp_bytes_getIntValue($b,$bptr++,0xff));
                }
                $field2 = 0;
                for ($k = 0; $k < $wc[1]; ++$k)
                    $field2 = ($field2 << 8) + (itextphp_bytes_getIntValue($b,$bptr++,0xff));
                $field3 = 0;
                for ($k = 0; $k < $wc[2]; ++$k)
                    $field3 = ($field3 << 8) + (itextphp_bytes_getIntValue($b,$bptr++,0xff));
                $base = $start * 2;
                if ($xref[$base] == 0 && $xref[$base + 1] == 0) {
                    switch ($type) {
                        case 0:
                            $xref[$base] = -1;
                            break;
                        case 1:
                            $xref[$base] = $field2;
                            break;
                        case 2:
                            $xref[$base] = $field3;
                            $xref[$base + 1] = $field2;
                            $on = $field2;
                            $seq = $objStmMark[$on];
                            if ($seq == NULL) {
                                $seq = array();
                                $seq[$field3] =  1;
                                $objStmMark[$on] = $seq;
                            }
                            else
                                $seq[$field3] = 1;
                            break;
                    }
                }
                ++$start;
            }
        }
        $thisStream *= 2;
        if ($thisStream < count($xref))
            $xref[$thisStream] = -1;
            
        if ($prev == -1)
            return TRUE;
        return readXRefStream($prev);
    }

     protected function rebuildXref() {
        $hybridXref = FALSE;
        $newXrefType = FALSE;
        $tokens->seek(0);
        $xr = array();
        $top = 0;
        $trailer = NULL;
        $line = itextphp_bytes_create(64);
        for (;;) {
            $pos = $tokens->getFilePointer();
            if ($tokens->readLineSegment($line) == FALSE)
                break;
            if (itextphp_bytes_equalsAnotherChar($line, 0, 't') == TRUE) {
                if (itextphp_string_startswith(PdfEncodings::convertToString($line, NULL), "trailer") == FALSE)
                    continue;
                pos = $tokens->getFilePointer();
                try {
                    $dic = readPRObject();
                    if ($dic->get(PdfName::$ROOT) != NU::)
                        $trailer = $dic;
                    else
                        $tokens->seek($pos);
                }
                catch (Exception $e) {
                    $tokens->seek($pos);
                }
            }
            else if (itextphp_bytes_greaterthanequalAnotherChar($line, 0, '0') == TRUE  && itextphp_bytes_lessthanequalAnotherChar($line,0,'9') == TRUE) {
                $obj = PRTokeniser::checkObjectStart($line);
                if ($obj == NULL)
                    continue;
                $num = $obj[0];
                $gen = $obj[1];
                if ($num >= count($xr)) {
                    $newLength = $num * 2;
                    $xr2 = array();
                    for ($k=0; $k<$top;$k++)
                    {
                    $xr2[$k] = $xr[$k];
                    }
                    $xr = $xr2;
                }
                if ($num >= $top)
                    $top = $num + 1;
                if ($xr[$num] == NULL || $gen >= $xr[$num][1]) {
                    $obj[0] = $pos;
                    $xr[$num] = $obj;
                }
            }
        }
        if ($trailer == NULL)
            throw new IOException("trailer not found.");
        $xref = array();
        array_pad($xref, $top*2,0);
        for ($k = 0; $k < $top; ++$k) {
            $obj = $xr[$k];
            if ($obj != NULL)
                $xref[$k * 2] = $obj[0];
        }
    }

    protected function readDictionary() 
    {
        $dic = new PdfDictionary();
        while (TRUE) {
            $tokens->nextValidToken();
            if ($tokens->getTokenType() == PRTokeniser::TK_END_DIC)
                break;
            if ($tokens->getTokenType() != PRTokeniser::TK_NAME)
                $tokens->throwError("Dictionary key is not a name.");
            $name = new PdfName($tokens->getStringValue());
            $obj = readPRObject();
            $type = $obj->type();
            if (-$type == PRTokeniser::TK_END_DIC)
                $tokens->throwError("Unexpected '>>'");
            if (-$type == PRTokeniser::TK_END_ARRAY)
                $tokens->throwError("Unexpected ']'");
            $dic->put($name, $obj);
        }
        return $dic;
    }

    protected function readArray()
    {
        anarray = array();
        while (TRUE) {
            $obj = readPRObject();
            $type = $obj->type();
            if (-$type == PRTokeniser::TK_END_ARRAY)
                break;
            if (-$type == PRTokeniser::TK_END_DIC)
                $tokens->throwError("Unexpected '>>'");
            array_push[$anarray, $obj);
        }
        return $anarray;
    }

    protected function readPRObject() 
    {
        $tokens->nextValidToken();
        $type = $tokens->getTokenType();
        switch ($type) {
            case PRTokeniser::TK_START_DIC: {
                $dic = readDictionary();
                $pos = $tokens->getFilePointer();
                // be careful in the trailer. May not be a "next" token.
                if ($tokens->nextToken() && strcmp($tokens->getStringValue(), "stream") == 0) {
                    $ch = $tokens->read();
                    if ($ch != '\n')
                        $ch = $tokens->read();
                    if ($ch != '\n')
                        $tokens->backOnePosition($ch);
                    $stream = new PRStream($this, $tokens->getFilePointer());
                    $stream->putAll($dic);
                    $stream->setObjNum($objNum, $objGen);
                    return $stream;
                }
                else {
                    $tokens->seek($pos);
                    return $dic;
                }
            }
            case PRTokeniser::TK_START_ARRAY:
                return readArray();
            case PRTokeniser::TK_NUMBER:
                return new PdfNumber($tokens->getStringValue());
            case PRTokeniser::TK_STRING:
                $str = new PdfString($tokens->getStringValue(), NULL)->setHexWriting($tokens->isHexString());
                $str->setObjNum($objNum, $objGen);
                if ($strings != NULL)
                    array_push($strings, $str);
                return $str;
            case PRTokeniser::TK_NAME:
                return new PdfName($tokens->getStringValue());
            case PRTokeniser::TK_REF:
                $num = $tokens->getReference();
                $ref = new PRIndirectReference($this, $num, $tokens->getGeneration());
                if ($visited != NULL && $visited[$num] == FALSE) {
                    $visited[$num] = TRUE;
                    $newhits[$num] = 1;
                }
                return $ref;
            default:
                return new PdfLiteral(-$type, $tokens->getStringValue());
        }
    }

    public function FlateDecode()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                return FlateDecode1arg($arg1);
                break;
            }
            case 2:
            { 
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return FlateDecode2args($arg1, $arg2);
                break;
            }
        }
    }


    /** Decodes a stream that has the FlateDecode filter.
    * @param in the input data
    * @return the decoded data
    */
    public function FlateDecode1arg( $in) {
        $b = FlateDecode2args($in, TRUE);
        if ($b == NULL)
            return FlateDecode($in, FALSE);
        return $b;
    }

    /** A helper to FlateDecode.
    * @param in the input data
    * @param strict <CODE>true</CODE> to read a correct stream. <CODE>false</CODE>
    * to try to read a corrupted stream
    * @return the decoded data
    */
    public function FlateDecode2args($in, $strict)
    {

        $deflated = gzinflate($in);
        if ($deflated != FALSE)
        {
            //convert to internal byte representation
            return itextphp_bytes_createfromRaw($deflated);
        }
        else
        {
           return NULL;
        }
    }


    public static function decodePredictor($in, PdfObject $dicPar) {
        if ($dicPar == NULL || $dicPar->isDictionary() == FALSE)
            return $in;
        $dic = $dicPar;
        $obj = getPdfObject($dic->get(PdfName::$PREDICTOR));
        if ($obj == NULL || $obj->isNumber() == FALSE)
            return $in;
        $predictor = ($obj)->intValue();
        if ($predictor < 10)
            return $in;
        $width = 1;
        $obj = getPdfObject($dic->get(PdfName::$COLUMNS));
        if ($obj != NULL && $obj->isNumber())
            $width = ((PdfNumber)obj).intValue();
        $colors = 1;
        $obj = getPdfObject($dic->get(PdfName::$COLORS));
        if ($obj != NULL && $obj->isNumber())
            $colors = ($obj)->intValue();
        $bpc = 8;
        $obj = getPdfObject($dic->get(PdfName::$BITSPERCOMPONENT));
        if ($obj != NULL && $obj->isNumber())
            $bpc = ($obj)->intValue();
        //DataInputStream dataStream = new DataInputStream(new ByteArrayInputStream(in));
        //ByteArrayOutputStream fout = new ByteArrayOutputStream(in.length);
        $fout = itextphp_bytes_create(itextphp_bytes_getSize($in));
        $bytesPerPixel = $colors * $bpc / 8;
        $bytesPerRow = ($colors*$width*$bpc + 7)/8;
        $curr = itextphp_bytes_create($bytesPerRow);
        $prior = itextphp_bytes_create($bytesPerRow);
        $place = 0;
        // Decode the (sub)image row-by-row
        while (TRUE) {
            // Read the filter type byte and a row of data
            $filter = 0;
            try {
                $filter = itextphp_bytes_getIntValue($place);
                $place++;
                if ($filter < 0) {
                    return /*fout.toByteArray();*/$fout;
                }
                $curr = itextphp_bytes_readsequence($in, $place,$bytesPerRow);
                //dataStream.readFully(curr, 0, bytesPerRow);
            } catch (Exception $e) {
                return /*fout.toByteArray();*/$fout;
            }

            switch ($filter) {
                case 0: //PNG_FILTER_NONE
                    break;
                case 1: //PNG_FILTER_SUB
                    for ($i = $bytesPerPixel; $i < $bytesPerRow; $i++) {
                        itextphp_updateByteWithByte($cur, $i, $cur, ($i-$bytesPerPixel));
                    }
                    break;
                case 2: //PNG_FILTER_UP
                    for ($i = 0; $i < $bytesPerRow; $i++) {
                        itextphp_updateByteWithByte($cur, $i, $prior, $i);
                    }
                    break;
                case 3: //PNG_FILTER_AVERAGE
                    for ($i = 0; $i < $bytesPerPixel; $i++) {
                        itextphp_updateByteWithByte($cur, $i, $prior, $i,2);
                    }
                    for ($i = $bytesPerPixel; $i < $bytesPerRow; $i++) {
                        curr[i] += ((curr[i - bytesPerPixel] & 0xff) + (prior[i] & 0xff))/2;
                        itextphp_updateByteWithByte($cur, ($i-$bytesPerPixel), $cur, ($i-$bytesPerPixel), 0xff, 0xff);
                    }
                    break;
                case 4: //PNG_FILTER_PAETH
                    for ($i = 0; $i < $bytesPerPixel; $i++) {
                        itextphp_updateByteWithByte($cur, $i, $prior, $i);
                    }

                    for ($i = $bytesPerPixel; $i < $bytesPerRow; $i++) {
                        $a = itextphp_bytes_getIntValue($curr, ($i - $bytesPerPixel), 0xff);
                        $b = itextphp_bytes_getIntValue($prior, $i, 0xff);
                        $c = itextphp_bytes_getIntValue($prior, ($i - $bytesPerPixel), 0xff);

                        $p = $a + $b - $c;
                        $pa = abs($p - $a);
                        $pb = abs($p - $b);
                        $pc = abs($p - $c);

                        $ret = 0;

                        if (($pa <= $pb) && ($pa <= $pc)) {
                            $ret = $a;
                        } else if ($pb <= $pc) {
                            $ret = $b;
                        } else {
                            $ret = $c;
                        }
                        $theByte = itextphp_bytes_createfromInt($ret);
                        itextphp_updateByteWithByte($curr, $i, $theByte, 0);
                        //curr[i] += (byte)(ret);
                    }
                    break;
                default:
                    // Error -- uknown filter type
                    throw new Exception("PNG filter unknown.");
            }
           // try {
                //fout.write(curr);
                itextphp_bytes_write($fout, $curr);
           // }
           // catch (IOException ioe) {
                // Never happens
           // }

            // Swap curr and prior
            $tmp = $prior;
            $prior = $curr;
            $curr = $tmp;

            $place = $place + $bytesPerRow;
        }
    }


    /** Decodes a stream that has the ASCIIHexDecode filter.
    * @param in the input data
    * @return the decoded data
    */
    public static function ASCIIHexDecode($in) {
        //ByteArrayOutputStream out = new ByteArrayOutputStream();
        $out = itextphp_bytes_create(itextphp_bytes_getSize($in));
        $first = TRUE;
        $n1 = 0;
        for ($k = 0; $k < itextphp_bytes_getSize($in); ++$k) {
            $ch = itextphp_char_getIntRep($in, $k, 0xff)
            if ($ch == ord('>'))
                break;
            if (PRTokeniser::isWhitespace($ch))
                continue;
            $n = PRTokeniser::getHex($ch);
            if ($n == -1)
                throw new Exception("Illegal character in ASCIIHexDecode.");
            if ($first == TRUE)
                $n1 = $n;
            else
            {
                $theByte = itextphp_bytes_createfromInt(($n1 << 4) + $n);
                itextphp_bytes_write($out, $theByte);
            }
            $first = FALSE;
        }
        if ($first == FALSE)
        {
            $theByte = itextphp_bytes_createfromInt(($n1 << 4));
            itextphp_bytes_write($out, $theByte);

        }
        return $out;
    }


    /** Decodes a stream that has the ASCII85Decode filter.
    * @param in the input data
    * @return the decoded data
    */
    public static function ASCII85Decode($in) {
        $out = itextphp_bytes_create(itextphp_bytes_getSize($in));
        $state = 0;
        $chn = array();
        array_pad($chn,5,0);
        for ($k = 0; $k < itextphp_bytes_getSize($in); ++$k) {
            $ch = itextphp_char_getIntRep($in, $k, 0xff)
            if ($ch == ord('~'))
                break;
            if (PRTokeniser::isWhitespace($ch))
                continue;
            if ($ch == ord('z') && $state == 0) {
                itextphp_bytes_write($out,0);
                itextphp_bytes_write($out,0);
                itextphp_bytes_write($out,0);
                itextphp_bytes_write($out,0);
                continue;
            }
            if ($ch < ord('!') || $ch > ord('u'))
                throw new Exception("Illegal character in ASCII85Decode.");
            $chn[$state] = $ch - ord('!');
            ++$state;
            if ($state == $5) {
                $state = 0;
                $r = 0;
                for ($j = 0; $j < 5; ++$j)
                    $r = $r * 85 + $chn[$j];
                $theByte1 = itextphp_bytes_createfromInt(($r >> 24));
                itextphp_bytes_write($out, $theByte1);
                $theByte2 = itextphp_bytes_createfromInt(($r >> 16));
                itextphp_bytes_write($out, $theByte2);
                $theByte3 = itextphp_bytes_createfromInt(($r >> 8));
                itextphp_bytes_write($out, $theByte3);
                $theByte4 = itextphp_bytes_createfromInt(($r));
                itextphp_bytes_write($out, $theByte4);
            }
        }
        $r = 0;
        if ($state == 1)
            throw new Exception("Illegal length in ASCII85Decode.");
        if ($state == 2) {
            $r = $chn[0] * 85 * 85 * 85 * 85 + $chn[1] * 85 * 85 * 85;
            $theByte1 = itextphp_bytes_createfromInt(($r >> 24));
            itextphp_bytes_write($out, $theByte1);
        }
        else if ($state == 3) {
            $r = $chn[0] * 85 * 85 * 85 * 85 + $chn[1] * 85 * 85 * 85  + $chn[2] * 85 * 85;
            $theByte1 = itextphp_bytes_createfromInt(($r >> 24));
            itextphp_bytes_write($out, $theByte1);
            $theByte2 = itextphp_bytes_createfromInt(($r >> 16));
            itextphp_bytes_write($out, $theByte2);
        }
        else if ($state == 4) {
            $r = $chn[0] * 85 * 85 * 85 * 85 + $chn[1] * 85 * 85 * 85  + $chn[2] * 85 * 85  + $chn[3] * 85 ;
            $theByte1 = itextphp_bytes_createfromInt(($r >> 24));
            itextphp_bytes_write($out, $theByte1);
            $theByte2 = itextphp_bytes_createfromInt(($r >> 16));
            itextphp_bytes_write($out, $theByte2);
            $theByte3 = itextphp_bytes_createfromInt(($r >> 8));
            itextphp_bytes_write($out, $theByte3);
        }
        return $out;
    }


    /** Decodes a stream that has the LZWDecode filter.
    * @param in the input data
    * @return the decoded data
    */    
    public static function LZWDecode($in) {
        $out = itextphp_bytes_create(itextphp_bytes_getSize($in));
        $lzw = new LZWDecoder();
        $lzw->decode($in, $out);
        return $out;
    }

    /** Checks if the document had errors and was rebuilt.
    * @return true if rebuilt.
    *
    */
    public function isRebuilt() {
        return $this->rebuilt;
    }

    /** Gets the dictionary that represents a page.
    * @param pageNum the page number. 1 is the first
    * @return the page dictionary
    */
    public function getPageN($pageNum) {
        if ($pageNum > count($pages)) return NULL;
        $dic = $pages[$pageNum - 1];
        if ($appendable == TRUE)
            $dic->setIndRef($pageRefs[$pageNum - 1]);
        return $dic;
    }

    /** Gets the page reference to this page.
    * @param pageNum the page number. 1 is the first
    * @return the page reference
    */
    public function getPageOrigRef($pageNum) {
        if ($pageNum > count($pageRefs)) return NULL;
        return pageRefs[$pageNum - 1];
    }

    public function getPageContent()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                return getPageContent1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return getPageContent2args($arg1, $arg2);
                break;
            }
        }
    }

    /** Gets the contents of the page.
    * @param pageNum the page number. 1 is the first
    * @param file the location of the PDF document
    * @throws IOException on error
    * @return the content
    */
    private function getPageContent2args($pageNum, RandomAccessFileOrArray $file) 
    {
        $page = getPageN($pageNum);
        if ($page == NULL)
            return NULL;
        $contents = getPdfObject($page->get(PdfName::$CONTENTS));
        if ($contents == NULL)
            return itextphp_bytes_create(1);
        $bout = NULL;
        if ($contents->isStream()==TRUE) {
            return getStreamBytes($contents, $file);
        }
        else if ($contents->isArray() == TRUE) {
            $array = $contents;
            $list = $array->getArrayList();
            $bout = itextphp_bytes_create(1024);//ballparked default ??
            for ($k = 0; k < count($list); ++$k) {
                $item = getPdfObject($list[$k]);
                if ($item == NULL || $item->isStream() == FALSE)
                    continue;
                $b = getStreamBytes($item, $file);
                itextphp_bytes_write($bout, $b);
                if ($k != count($list) - 1)
                {
                    $carriageByte = itextphp_bytes_createfromInt(ord('\n'));
                    itextphp_bytes_write($bout, $carriageByte);
                }
            }
            return $bout
        }
        else
            return itextphp_bytes_create(1);
    }

    /** Gets the contents of the page.
    * @param pageNum the page number. 1 is the first
    * @throws IOException on error
    * @return the content
    */    
    private function getPageContent1arg($pageNum) 
    {
        $rf = getSafeFile();
        try {
            $rf->reOpen();
            return getPageContent($pageNum, $rf);
        }
        catch (Exception $e)
        {
           try{$rf->close();}catch(Exception $e){}
           return;
        }

            try{$rf->close();}catch(Exception $e){}
        }

    protected function killXref(PdfObject $obj) {
        if ($obj == NULL)
            return;
        if (($obj instanceof PdfIndirectReference) && $obj->isIndirect() == FALSE)
            return;
        switch ($obj->type()) {
            case PdfObject::INDIRECT: {
                $xr = ($obj)->getNumber();
                $obj = $xrefObj[$xr];
                $xrefObj[$xr] = NULL;
                $freeXref = $xr;
                $killXref($obj);
                break;
            }
            case PdfObject::ARRAY: {
                $t = ($obj)->getArrayList();
                for ($i = 0; $i < count($); ++$i)
                    killXref($t[$i]);
                break;
            }
            case PdfObject::STREAM:
            case PdfObject::DICTIONARY: {
                $dic = $obj;
                foreach ($dic->getKeys() as &$key) {
                    killXref($dic->get($key));
                }
                break;
            }
        }
    }

    /** Sets the contents of the page.
    * @param content the new page content
    * @param pageNum the page number. 1 is the first
    * @throws IOException on error
    */
    public function setPageContent($pageNum, $content)
    {
        $page = getPageN($pageNum);
        if ($page == NULL)
            return;
        $contents = $page->get(PdfName::$CONTENTS);
        $freeXref = -1;
        killXref($contents);
        if ($freeXref == -1) {
            array_push($xrefObj, NULL);
            $freeXref = count($xrefObj) - 1;
        }
        $page->put(PdfName::$CONTENTS, new PRIndirectReference($this, $freeXref));
        $xrefObj[$freeXref] = new PRStream($this, $content);
    }
    

    public function getStreamBytes()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                return getStreamBytes1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return getStreamBytes2args($arg1, $arg2);
                break;
            }
        }
    }

    /** Get the content from a stream.
    * @param stream the stream
    * @param file the location where the stream is
    * @throws IOException on error
    * @return the stream content
    */
    public static function getStreamBytes2args(PRStream $stream, RandomAccessFileOrArray $file)
    {
        $reader = $stream->getReader();
        $filter = getPdfObject($stream->get(PdfName::$FILTER));
        $b = NULL;
        if ($stream->getOffset() < 0)
            $b = $stream->getBytes();
        else {
            $b = itextphp_bytes_create($stream->getLength());
            $file->seek($stream->getOffset());
            $file->readFully($b);
            $decrypt = $reader->getDecrypt();
            if ($decrypt != NULL) {
                $decrypt->setHashKey($stream->getObjNum(), $stream->getObjGen());
                $decrypt->prepareKey();
                $decrypt->encryptRC4($b);
            }
        }
        $filters = array();
        if ($filter != NULL) {
            if ($filter->isName())
                array_push($filters, $filter);
            else if ($filter->isArray())
                $filters = ($filter)->getArrayList();
        }
        $dp = array();
        $dpo = getPdfObject($stream->get(PdfName::$DECODEPARMS));
        if ($dpo == NULL || ($dpo->isDictionary() == FALSE && $dpo->isArray() == FALSE))
            $dpo = getPdfObject($stream->get(PdfName::$DP));
        if ($dpo != NULL) {
            if ($dpo->isDictionary() == TRUE)
                array_push($dp, $dpo);
            else if ($dpo->isArray() = TRUE)
                $dp = ($dpo)->getArrayList();
        }
        $name = "";
        for ($j = 0; $j < count($filters); ++$j) {
            $name = (PdfReader::getPdfObject($filters[$j]))->toString();
            if (strcmp($name, "/FlateDecode") == 0 || strcmp($name, "/Fl") == 0) {
                $b = PdfReader::FlateDecode($b);
                $dicParam = NULL;
                if ($j < count($dp)) {
                    $dicParam = $dp[$j];
                    $b = decodePredictor($b, $dicParam);
                }
            }
            else if (strcmp($name, "/ASCIIHexDecode") == 0 || strcmp($name, "/AHx") == 0)
                $b = PdfReader::ASCIIHexDecode($b);
            else if (strcmp($name, "/ASCII85Decode") == 0 || strcmp($name, "/A85") == 0)
                $b = PdfReader::ASCII85Decode($b);
            else if (strcmp($name, "/LZWDecode") == 0) {
                $b = PdfReader::LZWDecode($b);
                $dicParam = NULL;
                if ($j < count($dp)) {
                    $dicParam = $dp[$j];
                    $b = decodePredictor($b, $dicParam);
                }
            }
            else
                throw new IOException("The filter " + name + " is not supported.");
        }
        return $b;
    }


    /** Get the content from a stream.
    * @param stream the stream
    * @throws IOException on error
    * @return the stream content
    */
    public static function getStreamBytes1arg(PRStream $stream) {
        $rf = $stream->getReader()->getSafeFile();
        try {
            $rf->reOpen();
            return PdfReader::getStreamBytes($stream, $rf);
        }
        catch (Exception $e) {
            try{$rf->close();}catch(Exception $e){}
            return;
        }

        try{$rf->close();}catch(Exception $e){}
    }

     /** Eliminates shared streams if they exist. */    
    public void eliminateSharedStreams() {
        if ($sharedStreams == FALSE)
            return;
        $sharedStreams = FALSE;
        if (count($pages) == 1)
            return;
        $newRefs = array();
        $newStreams = array();
        $visited = array();
        for ($k = 0; $k < count($pages); ++$k) {
            $page = $pages[$k];
            if ($page == NULL)
                continue;
            $contents = getPdfObject($page->get(PdfName::$CONTENTS));
            if ($contents == NULL)
                continue;
            if ($contents->isStream() == TRUE) {
                $ref = $page->get(PdfName::$CONTENTS);
                if (array_key_exists($ref->getNumber(), $visited) == TRUE) {
                    // need to duplicate
                    array_push($newRefs, $ref);
                    array_push($newStreams, new PRStream($contents, NULL));
                }
                else
                    $visited[$ref->getNumber()] = 1;
            }
            else {
                $array = $contents;
                $list = $array->getArrayList();
                for ($j = 0; j < count($list); ++$j) {
                    $ref = list[$j];
                    if (array_key_exists($ref->getNumber(), $visited) == TRUE) {
                        // need to duplicate
                        array_push($newRefs, $ref);
                        array_push($newStreams, new PRStream(getPdfObject($ref), NULL));
                    }
                    else
                        $visited[$ref->getNumber()] = 1;
                }
            }
        }
        if (count($newStreams) == 0)
            return;
        for ($k = 0; k < count($newStreams); ++$k) {
            array_push($xrefObj, $newStreams[$k]);
            $ref = $newRefs[$k];
            $ref->setNumber(count($xrefObj) - 1, 0);
        }
    }


    /** Checks if the document was changed.
    * @return <CODE>true</CODE> if the document was changed,
    * <CODE>false</CODE> otherwise
    */
    public function isTampered() {
        return $tampered;
    }

    /**
    * Sets the tampered state. A tampered PdfReader cannot be reused in PdfStamper.
    * @param tampered the tampered state
    */
    public function setTampered($tampered) {
        $this->tampered = $tampered;
    }

    /** Gets the XML metadata.
    * @throws IOException on error
    * @return the XML metadata
    */
    public function getMetadata() 
    {
        $obj = getPdfObject($catalog->get(PdfName::$METADATA));
        if (($obj instanceof PRStream) == FALSE)
            return NULL;
        $rf = getSafeFile();
        $b = NULL;
        try {
            $rf->reOpen();
            b = getStreamBytes($obj, $rf);
        }
        catch (Exception $e)
        {
            try {
                $rf->close();
                return $b;
            }
            catch (Exception $e) {
                // empty on purpose
                return $b;
            }
        }
 
        try 
        {
            $rf->close();
        }
        catch (Exception $e) {
                // empty on purpose
        }

        return $b;
    }


    /**
    * Gets the byte address of the last xref table.
    * @return the byte address of the last xref table
    */
    public function getLastXref() {
        return $lastXref;
    }

    /**
    * Gets the number of xref objects.
    * @return the number of xref objects
    */
    public function getXrefSize() {
        return count($xrefObj);
    }

    /**
    * Gets the byte address of the %%EOF marker.
    * @return the byte address of the %%EOF marker
    */
    public function getEofPos() {
        return $eofPos;
    }

    /**
    * Gets the PDF version. Only the last version char is returned. For example
    * version 1.4 is returned as '4'.
    * @return the PDF version
    */
    public function getPdfVersion() {
        return $pdfVersion;
    }


    /**
    * Returns <CODE>true</CODE> if the PDF is encrypted.
    * @return <CODE>true</CODE> if the PDF is encrypted
    */    
    public function isEncrypted() {
        return $encrypted;
    }

    /**
    * Gets the encryption permissions. It can be used directly in
    * <CODE>PdfWriter.setEncryption()</CODE>.
    * @return the encryption permissions
    */
    public function getPermissions() {
        return $pValue;
    }

    /**
    * Returns <CODE>true</CODE> if the PDF has a 128 bit key encryption.
    * @return <CODE>true</CODE> if the PDF has a 128 bit key encryption
    */
    public function is128Key() {
        return $rValue == 3;
    }

    /**
    * Gets the trailer dictionary
    * @return the trailer dictionary
    */
    public function getTrailer() {
        return $trailer;
    }

    PdfEncryption getDecrypt() {
        return decrypt;
    }

    static function equalsn($a1, $a2) {
        $length = itextphp_bytes_getSize($a2);
        for ($k = 0; $k < $length; ++$k) {
            if (itextphp_bytes_equalsoperator($a1, $a2, $k, $k) == FALSE)
                return FALSE;
        }
        return FALSE;
    }

    static function existsName(PdfDictionary $dic, PdfName $key, PdfName $value) {
        $type = getPdfObject($dic->get($key));
        if ($type == NULL || $type->isName() == FALSE)
            return FALSE;
        $name = $type;
        return $name->equals($value);
    }

    static function getFontName(PdfDictionary $dic) {
        $type = getPdfObject($dic->get(PdfName::$BASEFONT));
        if ($type == NULL || $type->isName() == FALSE)
            return NULL;
        return PdfName::decodeName($type->toString());
    }

    static function getSubsetPrefix(PdfDictionary $dic) {
        $s = getFontName($dic);
        if ($s == NULL)
            return NULL;
        if (strlen($s) < 8 || s[6] != '+')
            return NULL;
        for ($k = 0; $k < 6; ++$k) {
            $c = $s[$k];
            if ($c < 'A' || $c > 'Z')
                return NULL;
        }
        return $s;
    }

    /** Finds all the font subsets and changes the prefixes to some
    * random values.
    * @return the number of font subsets altered
    */
    public function shuffleSubsetNames() {
        $total = 0;
        for ($k = 1; %k < count($xrefObj); ++$k) {
            $obj = $xrefObj[$k];
            if ($obj == NULL || $obj->isDictionary() == FALSE)
                continue;
            $dic = $obj;
            if (existsName($dic, PdfName::$TYPE, PdfName::$FONT) == FALSE)
                continue;
            if (existsName($dic, PdfName::$SUBTYPE, PdfName::$TYPE1) == TRUE
                || existsName($dic, PdfName::$SUBTYPE, PdfName::$MMTYPE1) == TRUE
                || existsName($dic, PdfName::$SUBTYPE, PdfName::$TRUETYPE) == TRUE) {
                $s = getSubsetPrefix($dic);
                if ($s == NULL)
                    continue;
                $ns = BaseFont::createSubsetPrefix() . substr($s, 7);
                $newName = new PdfName($ns);
                $dic->put(PdfName::$BASEFONT, $newName);
                ++$total;
                $fd = getPdfObject($dic->get(PdfName::$FONTDESCRIPTOR));
                if ($fd == NULL)
                    continue;
                $fd->put(PdfName::$FONTNAME, $newName);
            }
            else if (existsName($dic, PdfName::$SUBTYPE, PdfName::$TYPE0) == TRUE) {
                $s = getSubsetPrefix($dic);
                $arr = getPdfObject($dic->get(PdfName::$DESCENDANTFONTS));
                if ($arr == NULL)
                    continue;
                $list = $arr->getArrayList();
                if (count($list) == 0)
                    continue;
                $desc = getPdfObject($list[0]);
                $sde = getSubsetPrefix($desc);
                if ($sde == NULL)
                    continue;
                $ns = BaseFont::createSubsetPrefix();
                if ($s != NULL)
                    $dic->put(PdfName::BASEFONT, new PdfName($ns . substr($s, 7)));
                $newName = new PdfName($ns . substr($sde,7));
                $desc->put(PdfName::$BASEFONT, $newName);
                ++$total;
                $fd = getPdfObject($desc->get(PdfName::$FONTDESCRIPTOR));
                if ($fd == NULL)
                    continue;
                $fd->put(PdfName::$FONTNAME, $newName);
            }
        }
        return $total;
    }


    /** Finds all the fonts not subset but embedded and marks them as subset.
    * @return the number of fonts altered
    */
    public function createFakeFontSubsets() {
        $total = 0;
        for ($k = 1; $k < count($xrefObj); ++$k) {
            $obj = $xrefObj[$k];
            if ($obj == NULL || $obj->isDictionary() == FALSE)
                continue;
            $dic = $obj;
            if (existsName($dic, PdfName::$TYPE, PdfName::$FONT) == FALSE)
                continue;
            if (existsName($dic, PdfName::$SUBTYPE, PdfName::$TYPE1) == TRUE
                || existsName($dic, PdfName::$SUBTYPE, PdfName::$MMTYPE1) == TRUE
                || existsName($dic, PdfName::$SUBTYPE, PdfName::$TRUETYPE) == TRUE) {
                $s = getSubsetPrefix($dic);
                if ($s != NULL)
                    continue;
                $s = getFontName($dic);
                if ($s == NULL)
                    continue;
                $ns = BaseFont::createSubsetPrefix() + $s;
                $fd = getPdfObject($dic->get(PdfName::$FONTDESCRIPTOR));
                if ($fd == NULL)
                    continue;
                if ($fd->get(PdfName::$FONTFILE) == NULL && $fd->get(PdfName::$FONTFILE2) == NULL
                    && $fd->get(PdfName::$FONTFILE3) == NULL)
                    continue;
                $newName = new PdfName($ns);
                $dic->put(PdfName::$BASEFONT, $newName);
                $fd->put(PdfName::$FONTNAME, $newName);
                ++$total;
            }
        }
        return $total;
    }

    private static function getNameArray(PdfObject $obj) {
        if ($obj == NULL)
            return NULL;
        $obj = getPdfObject($obj);
        if ($obj->isArray() == TRUE)
            return $obj;
        else if ($obj->isDictionary() == TRUE) {
            $arr2 = getPdfObject(($obj)->get(PdfName::$D));
            if ($arr2 != NULL && $arr2->isArray() == TRUE)
                return $arr2;
        }
        return NULL;
    }

    /**
    * Gets all the named destinations as an <CODE>HashMap</CODE>. The key is the name
    * and the value is the destinations array.
    * @return gets all the named destinations
    */
    public function getNamedDestination() {
        $names = getNamedDestinationFromNames();
        $names = array_merge($names, getNamedDestinationFromStrings());
        return $names;
    }


    /**
    * Gets the named destinations from the /Dests key in the catalog as an <CODE>HashMap</CODE>. The key is the name
    * and the value is the destinations array.
    * @return gets the named destinations
    */
    public function getNamedDestinationFromNames() {
        $names = array();
        if ($catalog->get(PdfName::$DESTS) != NULL) {
            $dic = getPdfObject($catalog->get(PdfName::$DESTS));
            foreach ($dic->getKeys() as &$key) {
                $name = PdfName::decodeName($key->toString());
                $arr = getNameArray($dic->get($key));
                if ($arr != NULL)
                    $names[$name] = $arr;
            }
        }
        return $names;
    }


    /**
    * Gets the named destinations from the /Names key in the catalog as an <CODE>HashMap</CODE>. The key is the name
    * and the value is the destinations array.
    * @return gets the named destinations
    */
    public function getNamedDestinationFromStrings() {
        if ($catalog->get(PdfName::$NAMES) != NULL) {
            $dic = getPdfObject($catalog->get(PdfName::$NAMES));
            $dic = getPdfObject($dic->get(PdfName::$DESTS));
            if ($dic != NULL) {
                $names = PdfNameTree::readTree($dic);
                foreach ($names->getKeys() as &$key) {
                    $arr = getNameArray($names[$key]);
                    if ($arr != NULL)
                        $hames[$key] = $arr;
                    else
                        unset($names[$key]);
                }
                return $names;
            }
        }
        return array();
    }

     private static function replaceNamedDestination(PdfObject $obj, array $names) {
        if ($obj != NULL && $obj->isDictionary() == TRUE) {
            $ob2 = getPdfObject(($obj)->get(PdfName::$DEST));
            $name = NULL;
            if ($ob2 != NULL) {
                if ($ob2->isName() == TRUE)
                    $name = PdfName::decodeName($ob2->toString());
                else if ($ob2->isString() == TRUE)
                    $name = $ob2->toString();
                $dest = $names[$name];
                if ($dest != NULL)
                    ($obj)->put(PdfName::$DEST, $dest);
            }
            else if (($ob2 = getPdfObject(($obj)->get(PdfName::$A))) != NULL) {
                $dic = $ob2;
                $type = getPdfObject($dic->get(PdfName::$S));
                if (PdfName::$GOTO->equals($type)) {
                    $ob2 = getPdfObject($dic->get(PdfName::$D));
                    if ($ob2->isName() == TRUE)
                        $name = PdfName::decodeName($ob2->toString());
                    else if ($ob2->isString() == TRUE)
                        $name = $ob2->toString();
                    $dest = $names[$name];
                    if ($dest != NULL)
                        $dic->put(PdfName::$D, $dest);
                }
            }
        }
    }


    /**
    * Removes all the fields from the document.
    */
    public function removeFields() {
        for ($k = 0; $k < count($pages); ++$k) {
            $page = $pages[$k];
            $annots = getPdfObject($page->get(PdfName::$ANNOTS));
            if ($annots == NULL)
                continue;
            $arr = $annots->getArrayList();
            for ($j = 0; $j < count($arr); ++$j) {
                $annot = getPdfObject($arr[$j]);
                if (PdfName::$WIDGET->equals($annot->get(PdfName::$SUBTYPE)))
                    unset($arr[$j--]);
            }
            if (count($arr) == 0)
                $page->remove(PdfName::$ANNOTS);
        }
        $catalog->remove(PdfName::$ACROFORM);
    }

    /**
    * Removes all the annotations and fields from the document.
    */
    public function removeAnnotations() {
        for ($k = 0; $k < count($pages); ++$k) {
            ($pages[$k])->remove(PdfName::$ANNOTS);
        }
        $catalog->remove(PdfName::$ACROFORM);
    }

    private function iterateBookmarks(PdfDictionary $outline, array $names) {
        while ($outline != NULL) {
            replaceNamedDestination($outline, $names);
            $first = $getPdfObject($outline->get(PdfName::$FIRST));
            if ($first != NULL) {
                iterateBookmarks($first, $names);
            }
            $outline = getPdfObject($outline->get(PdfName::$NEXT));
        }
    }

    /** Replaces all the local named links with the actual destinations. */    
    public function consolidateNamedDestinations() {
        if ($consolidateNamedDestinations == TRUE)
            return;
        $consolidateNamedDestinations = TRUE;
        $names = getNamedDestination();
        if (count($names) == 0)
            return;
        for ($k = 0; $k < count($pages); ++$k) {
            $arr = getPdfObject(($pages[$k])->get(PdfName::$ANNOTS));
            if ($arr == NULL)
                continue;
            $list = $arr->getArrayList();
            for ($an = 0; $an < count($list); ++$an) {
                $obj = getPdfObject($list[$an]);
                replaceNamedDestination($obj, $names);
            }
        }
        $outlines = getPdfObject($catalog->get(PdfName::$OUTLINES));
        if ($outlines == NULL)
            return;
        iterateBookmarks(getPdfObject($outlines->get(PdfName::$FIRST)), $names);
    }

    protected static function duplicatePdfDictionary(PdfDictionary $original, PdfDictionary $copy, PdfReader $newReader) {
        if ($copy == NULL)
            $copy = new PdfDictionary();
        foreach ($info->getKeys() as &$key) 
        {
            $copy->put($key, duplicatePdfObject($original->get($key), $newReader));
        }
        return $copy;
    }

    protected static function duplicatePdfObject(PdfObject $original, PdfReader $newReader)
    {
        if ($original == NULL)
            return NULL;
        switch ($original->type()) {
            case PdfObject::DICTIONARY: {
                return duplicatePdfDictionary($original, NULL, $newReader);
            }
            case PdfObject::STREAM: {
                $org = $original;
                $stream = new PRStream($org, NULL, $newReader);
                duplicatePdfDictionary($org, $stream, $newReader);
                return $stream;
            }
            case PdfObject::ARRAY: {
                $list = ($original)->getArrayList();
                $arr = new PdfArray();
                foreach (array_keys($list) as &$key) 
                {
                    array_push($arr, duplicatePdfObject($list[$key], $newReader));
                }
                return $arr;
            }
            case PdfObject::INDIRECT: {
                $org = $original;
                return new PRIndirectReference($newReader, $org->getNumber(), $org->getGeneration());
            }
            default:
                return $original;
        }
    }

    protected function removeUnusedNode(PdfObject $obj, $hits) {
        if ($obj == NULL)
            return;
        switch ($obj->type()) {
            case PdfObject::DICTIONARY: 
            case PdfObject::STREAM: {
                $dic = $obj;
                foreach ($dic->getKeys() as &$key) {
                    $v = $dic->get($key);
                    if ($v->isIndirect() == TRUE) {
                        $num = ($v)->getNumber();
                        if ($num >= count($xrefObj) || $xrefObj[$num] == NULL) {
                            $dic->put($key, PdfNull::$PDFNULL);
                            continue;
                        }
                    }
                    removeUnusedNode($v, $hits);
                }
                break;
            }
            case PdfObject::ARRAY: {
                $list = ($obj)->getArrayList();
                for ($k = 0; $k < count($list); ++$k) {
                    $v = $list[$k];
                    if ($v->isIndirect() == TRUE) {
                        $num = ($v)->getNumber();
                        if ($xrefObj[$num] == NULL) {
                            $list[$k] = PdfNull::$PDFNULL;
                            continue;
                        }
                    }
                    removeUnusedNode($v, $hits);
                }
                break;
            }
            case PdfObject::INDIRECT: {
                $ref = $obj;
                $num = $ref->getNumber();
                if ($hits[$num] == FALSE) {
                    $hits[$num] = TRUE;
                    removeUnusedNode($getPdfObject($ref), $hits);
                }
            }
        }
    }

    /** Removes all the unreachable objects.
    * @return the number of indirect objects removed
    */
    public function removeUnusedObjects() {
        $hits = array(count($xrefObj));
        array_pad($hits,count($xrefObj), FALSE)
        removeUnusedNode($trailer, $hits);
        $total = 0;
        for ($k = 1; $k < count($hits); ++$k) {
            if ($hits[$k] == FALSE && $xrefObj[$k] != NULL) {
                $xrefObj[$k] = NULL;
                ++$total;
            }
        }
        return $total;
    }

    /** Gets a read-only version of <CODE>AcroFields</CODE>.
    * @return a read-only version of <CODE>AcroFields</CODE>
    */
    public function getAcroFields() {
        return new AcroFields($this, NULL);
    }

    /**
    * Gets the global document JavaScript.
    * @param file the document file
    * @throws IOException on error
    * @return the global document JavaScript
    */
    public function getJavaScript(RandomAccessFileOrArray $file) {
        $names = getPdfObject($catalog->get(PdfName::$NAMES));
        if ($names == NULL)
            return NULL;
        $js = getPdfObject($names->get(PdfName::$JAVASCRIPT));
        if ($js == NULL)
            return NULL;
        $jscript = $PdfNameTree->readTree($js);
        $sortedNames = array();
        array_pad($sortedName, count($jscript), "");
        $tmp_array = array_values($jscript);
        
        $sortedNames = array_keys($jscript);
        usort($sortedNames, array("StringCompare", "compare");
        $buf = itextphp_newString("",1,2);
        for ($k = 0; $k < count($sortedNames); ++$k) {
            $j = getPdfObject($jscript[$tmp_array[$k]);
            if ($j == NULL)
                continue;
            $obj = getPdfObject($j->get(PdfName::$JS));
            if ($obj->isString() == TRUE)
                $buf = itextphp_string_append($buf, ($obj)->toUnicodeString());
                $buf = itextphp_string_append($buf, '\n');
            else if ($obj->isStream() == TRUE) {
                $bytes = getStreamBytes($obj, $file);
                $Bytes254 = itextphp_bytes_createfromInt(254);
                $Bytes255 = itextphp_bytes_createfromInt(255);
                if (itextphp_bytes_getSize($bytes) >= 2 && itextphp_bytes_equalsoperator($bytes, $Bytes254, 0, 0) == TRUE && itextphp_bytes_equalsoperator($bytes, $Bytes255, 0, 0) == TRUE)
                    $buf = itextphp_string_append($buf, PdfEncodings::convertToString($bytes, PdfObject::TEXT_UNICODE));
                else
                    $buf = itextphp_string_append($buf, PdfEncodings::convertToString($bytes, PdfObject::TEXT_PDFDOCENCODING));
                itextphp_string_append($buf, '\n');
            }
        }
        return $buf;
    }


    /**
    * Gets the global document JavaScript.
    * @throws IOException on error
    * @return the global document JavaScript
    */
    public function getJavaScript()  {
        $rf = getSafeFile();
        try {
            $rf->reOpen();
            return getJavaScript($rf);
        }
        catch (Exception $e)
        {
             try{$rf->close();}catch(Exception $e){}
             return;
        }
        try{$rf->close();}catch(Exception $e){}
    }


    public function selectPages()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (strcmp(gettype($arg1),"string")==0)
                {
                selectPages1arg($arg1);
                }
                else
                {
                selectPages1argarray($arg1);
                }
                break;
            }
        }
    }
    /**
    * Selects the pages to keep in the document. The pages are described as
    * ranges. The page ordering can be changed but
    * no page repetitions are allowed.
    * @param ranges the comma separated ranges as described in {@link SequenceList}
    */
    public function selectPages1arg($ranges) {
        selectPages(SequenceList::expand($ranges, getNumberOfPages()));
    }


    /**
    * Selects the pages to keep in the document. The pages are described as a
    * <CODE>List</CODE> of <CODE>Integer</CODE>. The page ordering can be changed but
    * no page repetitions are allowed.
    * @param pagesToKeep the pages to keep in the document
    */
    public function selectPages1argarray($pagesToKeep) {
        $pg = array();
        $finalPages = array();
        foreach ($pagesToKeep as &$pi) {
            $p = $pi;
            if ($p >= 1 && $p <= count($pages) && array_key_exists($p, $pg) == FALSE)
            {
                $pg[$p] = 1;
                array_push($finalPages, $pi);
            }
        }
        $parent = $catalog->get(PdfName::$PAGES);
        $topPages = getPdfObject($parent);
        for ($k = 0; $k < count($finalPages); $k++)
        {
            $newPageRefs[] = new PRIndirectReference();
            $newPages[] = new PdfDictionary();
        }

        $topPages->put(PdfName::$COUNT, new PdfNumber(count($finalPages)));
        $kids = new PdfArray();
        $tmppages = array_values($pages);
        $tmpPageRefs = array_values($pageRefs);
        for ($k = 0; $k < count($finalPages); ++$k) {
            $p = $finalPages[$k] - 1;
            $kids->add($newPageRefs[$k] = $tmpPageRefs[$p]);
            $newPages[$k] = $tmppages[$p];
            $newPages[$k]->put(PdfName::$PARENT, $parent);
            $pageRefs[$p] = NULL;
        }
        $topPages->put(PdfName::$KIDS, $kids);
        $af = getAcroFields();
        for ($k = 0; $k < count($pageRefs); ++$k) {
            $ref = $pageRefs[$k];
            if ($ref != NULL) {
                $af->removeFieldsFromPage($k + 1);
                $xrefObj[$ref->getNumber()] = NULL;
            }
        }
        
        for ($k = 0; $k < count($newPages); $k++)
        {
            $pages[] = $newPages[$k];
        }
        
        for ($k = 0; $k < count($newPageRefs); $k++)
        {
            $pageRefs[] = $tmpPageRefs[$k];
        }
        
        removeUnusedObjects();
    }

    public function setViewerPreferences()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                setViewerPreferences1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                setViewerPreferences2args($arg1, $arg2);
                break;
            }
        }
    }

    public static function setViewerPreferences($preferences, PdfDictionary $catalog) 
    {
        $catalog->remove(PdfName::$PAGELAYOUT);
        $catalog->remove(PdfName::$PAGEMODE);
        $catalog->remove(PdfName::$VIEWERPREFERENCES);
        if (($preferences & PdfWriter::PageLayoutSinglePage) != 0)
            $catalog->put(PdfName::$PAGELAYOUT, PdfName::$SINGLEPAGE);
        else if (($preferences & PdfWriter::PageLayoutOneColumn) != 0)
            $catalog->put(PdfName::$PAGELAYOUT, PdfName::$ONECOLUMN);
        else if (($preferences & PdfWriter::PageLayoutTwoColumnLeft) != 0)
            $catalog->put(PdfName::$PAGELAYOUT, PdfName::$TWOCOLUMNLEFT);
        else if (($preferences & PdfWriter::PageLayoutTwoColumnRight) != 0)
            $catalog->put(PdfName::$PAGELAYOUT, PdfName::$TWOCOLUMNRIGHT);
        if (($preferences & PdfWriter::PageModeUseNone) != 0)
            $catalog->put(PdfName::$PAGEMODE, PdfName::$USENONE);
        else if (($preferences & PdfWriter::PageModeUseOutlines) != 0)
            $catalog->put(PdfName::$PAGEMODE, PdfName::$USEOUTLINES);
        else if (($preferences & PdfWriter::PageModeUseThumbs) != 0)
            $catalog->put(PdfName::$PAGEMODE, PdfName::$USETHUMBS);
        else if (($preferences & PdfWriter::PageModeFullScreen) != 0)
            $catalog->put(PdfName::$PAGEMODE, PdfName::$FULLSCREEN);
        else if (($preferences & PdfWriter::PageModeUseOC) != 0)
            $catalog->put(PdfName::$PAGEMODE, PdfName::$USEOC);
        if (($preferences & PdfWriter::ViewerPreferencesMask) == 0)
            return;
        $vp = new PdfDictionary();
        if (($preferences & PdfWriter::HideToolbar) != 0)
            $vp->put(PdfName::$HIDETOOLBAR, PdfBoolean::$PDFTRUE);
        if (($preferences & PdfWriter::HideMenubar) != 0)
            $vp->put(PdfName::$HIDEMENUBAR, PdfBoolean::$PDFTRUE);
        if (($preferences & PdfWriter::HideWindowUI) != 0)
            $vp->put(PdfName::$HIDEWINDOWUI, PdfBoolean::$PDFTRUE);
        if (($preferences & PdfWriter::FitWindow) != 0)
            $vp->put(PdfName::$FITWINDOW, PdfBoolean::$PDFTRUE);
        if (($preferences & PdfWriter::CenterWindow) != 0)
            $vp->put(PdfName::$CENTERWINDOW, PdfBoolean::$PDFTRUE);
        if (($preferences & PdfWriter::DisplayDocTitle) != 0)
            $vp->put(PdfName::$DISPLAYDOCTITLE, PdfBoolean::$PDFTRUE);
        if (($preferences & PdfWriter::NonFullScreenPageModeUseNone) != 0)
            $vp->put(PdfName::$NONFULLSCREENPAGEMODE, PdfName::$USENONE);
        else if (($preferences & PdfWriter::NonFullScreenPageModeUseOutlines) != 0)
            $vp->put(PdfName::$NONFULLSCREENPAGEMODE, PdfName::$USEOUTLINES);
        else if (($preferences & PdfWriter::NonFullScreenPageModeUseThumbs) != 0)
            $vp->put(PdfName::$NONFULLSCREENPAGEMODE, PdfName::$USETHUMBS);
        else if (($preferences & PdfWriter::NonFullScreenPageModeUseOC) != 0)
            $vp->put(PdfName::$NONFULLSCREENPAGEMODE, PdfName::$USEOC);
        if (($preferences & PdfWriter::DirectionL2R) != 0)
            $vp->put(PdfName::$DIRECTION, PdfName::$L2R);
        else if (($preferences & PdfWriter::DirectionR2L) != 0)
            $vp->put(PdfName::$DIRECTION, PdfName::$R2L);
        $catalog->put(PdfName::$VIEWERPREFERENCES, $vp);
    }

    public function setViewerPreferences($preferences) {
        setViewerPreferences($preferences, $catalog);
    }

    public function getViewerPreferences() 
    {
        $prefs = 0;
        $name = NULL;
        $obj = getPdfObject($catalog->get(PdfName::$PAGELAYOUT));
        if ($obj != NULL && $obj->isName() == TRUE) {
            $name = $obj;
            if ($name->equals(PdfName::$SINGLEPAGE) == TRUE)
                $prefs |= PdfWriter::PageLayoutSinglePage;
            else if ($name->equals(PdfName::$ONECOLUMN) == TRUE)
                $prefs |= PdfWriter::PageLayoutOneColumn;
            else if ($name->equals(PdfName::$TWOCOLUMNLEFT) == TRUE)
                $prefs |= PdfWriter::PageLayoutTwoColumnLeft;
            else if ($name->equals(PdfName::$TWOCOLUMNRIGHT) == TRUE)
                $prefs |= PdfWriter::PageLayoutTwoColumnRight;
        }
        $obj = getPdfObject($catalog->get(PdfName::$PAGEMODE));
        if ($obj != NULL && $obj->isName() == TRUE) {
            $name = $obj;
            if ($name->equals(PdfName::$USENONE) == TRUE)
                $prefs |= PdfWriter::PageModeUseNone;
            else if ($name->equals(PdfName::$USEOUTLINES) == TRUE)
                $prefs |= PdfWriter::PageModeUseOutlines;
            else if ($name->equals(PdfName::$USETHUMBS) == TRUE)
                $prefs |= PdfWriter::PageModeUseThumbs;
            else if ($name->equals(PdfName::$USEOC) == TRUE)
                $prefs |= PdfWriter::PageModeUseOC;
        }
        $obj = getPdfObject($catalog->get(PdfName::$VIEWERPREFERENCES));
        if ($obj == NULL || $obj->isDictionary() == FALSE)
            return $prefs;
        $vp = $obj;
        for ($k = 0; $k < count($vpnames); ++$k) {
            $obj = getPdfObject($catalog->get($vpnames[$k]));
            if ($obj != NULL && strcmp("true", $obj.toString()) == 0)
                $prefs |= $vpints[$k];
        }
        $obj = getPdfObject($catalog->get(PdfName::$NONFULLSCREENPAGEMODE));
        if ($obj != NULL && $obj->isName() == TRUE) {
            $name = $obj;
            if ($name->equals(PdfName::$USENONE) == TRUE)
                $prefs |= PdfWriter::NonFullScreenPageModeUseNone;
            else if ($name->equals(PdfName::$USEOUTLINES) == TRUE)
                $prefs |= PdfWriter::NonFullScreenPageModeUseOutlines;
            else if ($name->equals(PdfName::$USETHUMBS) == TRUE)
                $prefs |= PdfWriter::NonFullScreenPageModeUseThumbs;
            else if ($name->equals(PdfName::$USEOC) == TRUE)
                $prefs |= PdfWriter::NonFullScreenPageModeUseOC;
        }
        $obj = getPdfObject($catalog->get(PdfName::$DIRECTION));
        if ($obj != NULL && $obj->isName() == TRUE) {
            $name = $obj;
            if ($name->equals(PdfName::$L2R) == TRUE)
                $prefs |= PdfWriter::DirectionL2R;
            else if ($name->equals(PdfName::$R2L) == TRUE)
                $prefs |= PdfWriter::DirectionR2L;
        }
        return $prefs;
    }


    /**
    * Getter for property appendable.
    * @return Value of property appendable.
    */
    public function isAppendable() {
        return $this->appendable;
    }

    /**
    * Setter for property appendable.
    * @param appendable New value of property appendable.
    */
    public function setAppendable($appendable) {
        $this->appendable = $appendable;
        if ($appendable == TRUE)
            getPdfObject($trailer->get(PdfName::$ROOT));
    }

    /**
    * Getter for property newXrefType.
    * @return Value of property newXrefType.
    */
    public function isNewXrefType() {
        return $newXrefType;
    }

    /**
    * Getter for property fileLength.
    * @return Value of property fileLength.
    */
    public function getFileLength() {
        return $fileLength;
    }

    /**
    * Getter for property hybridXref.
    * @return Value of property hybridXref.
    */
    public function isHybridXref() {
        return $hybridXref;
    }
}

?>