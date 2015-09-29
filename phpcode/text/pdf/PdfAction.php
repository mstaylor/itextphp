<?php /*
 * $Id: PdfAction.php,v 1.1.1.1 2005/09/22 16:10:04 mstaylor Exp $
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

require_once("PdfDictionary.php");
require_once("PdfName.php");
require_once("PdfString.php");
require_once("PdfBoolean.php");
require_once("PdfLiteral.php");
require_once("PdfRendition.php");
require_once("PdfFileSpecification.php");
require_once("PdfIndirectReference.php");
require_once("PdfDestination.php");
require_once("PdfWriter.php");
require_once("PdfArray.php");
require_once("PdfNumber.php");
require_once("PdfObject.php");
require_once("../../exceptions/IOException.php");
require_once("../../exceptions/IllegalArgumentException.php");

class PdfAction extends PdfDictionary
{

    /** A named action to go to the first page.
    */
    const FIRSTPAGE = 1;
    /** A named action to go to the previous page.
    */
    const PREVPAGE = 2;
    /** A named action to go to the next page.
    */
    const NEXTPAGE = 3;
    /** A named action to go to the last page.
    */
    const LASTPAGE = 4;

    /** A named action to open a print dialog.
    */
    const PRINTDIALOG = 5;

    /** a possible submitvalue */
    const SUBMIT_EXCLUDE = 1;
    /** a possible submitvalue */
    const SUBMIT_INCLUDE_NO_VALUE_FIELDS = 2;
    /** a possible submitvalue */
    const SUBMIT_HTML_FORMAT = 4;
    /** a possible submitvalue */
    const SUBMIT_HTML_GET = 8;
    /** a possible submitvalue */
    const SUBMIT_COORDINATES = 16;
    /** a possible submitvalue */
    const SUBMIT_XFDF = 32;
    /** a possible submitvalue */
    const SUBMIT_INCLUDE_APPEND_SAVES = 64;
    /** a possible submitvalue */
    const SUBMIT_INCLUDE_ANNOTATIONS = 128;
    /** a possible submitvalue */
    const SUBMIT_PDF = 256;
    /** a possible submitvalue */
    const SUBMIT_CANONICAL_FORMAT = 512;
    /** a possible submitvalue */
    const SUBMIT_EXCL_NON_USER_ANNOTS = 1024;
    /** a possible submitvalue */
    const SUBMIT_EXCL_F_KEY = 2048;
    /** a possible submitvalue */
    const SUBMIT_EMBED_FORM = 8196;
    /** a possible submitvalue */
    const RESET_EXCLUDE = 1;

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0); 
                if ($arg1 instanceof PdfIndirectReference)
                {
                    construct1argPdfIndirectRef($arg1)
                }
                else if (strcmp(gettype($arg1),"integer") == 0)
                {
                    construct1argint($arg1);
                }
                else
                {
                    construct1argstring($arg1);
                }
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (strcmp(gettype($arg1), "string") == 0 && strcmp(gettype($arg2), "string") == 0)
                {
                    construct2argsstringstring($arg1, $arg2);
                }
                else
                {
                    construct2argsstringint($arg1, $arg2);
                }
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
    * Constructs a new <CODE>PdfAction</CODE> of Subtype URI.
    *
    * @param url the Url to go to
    */
    private function construct1argstring($url)
    {
        construct2argstring($url, FALSE);
    }

    /**
    * Constructs a new <CODE>PdfAction</CODE> of Subtype GoTo.
    * @param destination the destination to go to
    */
    private function construct1argPdfIndirectRef(PdfIndirectReference $destination)
    {
        put(PdfName::$S, PdfName::$GOTO);
        put(PdfName::$D, $destination); 
    }

    /** Implements name actions. The action can be FIRSTPAGE, LASTPAGE,
    * NEXTPAGE, PREVPAGE and PRINTDIALOG.
    * @param named the named action
    */
    private function construct1argint($named)
    {
        put(PdfName::$S, PdfName::$NAMED);
        switch ($named) {
            case PdfAction::FIRSTPAGE:
                put(PdfName::$N, PdfName::$FIRSTPAGE);
                break;
            case PdfAction::LASTPAGE:
                put(PdfName::$N, PdfName::$LASTPAGE);
                break;
            case PdfAction::NEXTPAGE:
                put(PdfName::$N, PdfName::$NEXTPAGE);
                break;
            case PdfAction::PREVPAGE:
                put(PdfName::$N, PdfName::$PREVPAGE);
                break;
            case PdfAction::PRINTDIALOG:
                put(PdfName::$S, PdfName::$JAVASCRIPT);
                put(PdfName::$JS, new PdfString("this.print(true);\r"));
                break;
            default:
                throw new Exception("Invalid named action.");
        } 
    }
    

    /**
    * Construct a new <CODE>PdfAction</CODE> of Subtype URI that accepts the x and y coordinate of the position that was clicked.
    * @param url
    * @param isMap
    */
    private function construct2argsstring($url, $isMap)
    {
        put(PdfName::$S, PdfName::$URI);
        put(PdfName::$URI, new PdfString($url));
        if ($isMap == TRUE)
            put(PdfName::$ISMAP, PdfBoolean::$PDFTRUE);
    }

    /**
    * Constructs a new <CODE>PdfAction</CODE> of Subtype GoToR.
    * @param filename the file name to go to
    * @param name the named destination to go to
    */
    private function construct2argsstringstring($filename, $name)
    {
       put(PdfName::$S, PdfName::$GOTOR);
       put(PdfName::$F, new PdfString($filename));
       put(PdfName::$D, new PdfString($name));
    }
    
    /**
    * Constructs a new <CODE>PdfAction</CODE> of Subtype GoToR.
    * @param filename the file name to go to
    * @param page the page destination to go to
    */
    private function construct2argsstringint($filename, $page)
    {
        put(PdfName::$S, PdfName::$GOTOR);
        put(PdfName::$F, new PdfString($filename));
        put(PdfName::$D, new PdfLiteral("[" . ($page - 1) . " /FitH 10000]"));
    }
     
    /** Launchs an application or a document.
    * @param application the application to be launched or the document to be opened or printed.
    * @param parameters (Windows-specific) A parameter string to be passed to the application.
    * It can be <CODE>null</CODE>.
    * @param operation (Windows-specific) the operation to perform: "open" - Open a document,
    * "print" - Print a document.
    * It can be <CODE>null</CODE>.
    * @param defaultDir (Windows-specific) the default directory in standard DOS syntax.
    * It can be <CODE>null</CODE>.
    */
    private function construct4args($application, $parameters, $operation, $defaultDir)
    {
        put(PdfName::$S, PdfName::$LAUNCH);
        if ($parameters == NULL && $operation == NULL && $defaultDir == NULL)
            put(PdfName::$F, new PdfString($application));
        else {
            $dic = new PdfDictionary();
            $dic->put(PdfName::$F, new PdfString($application));
            if ($parameters != NULL)
                $dic->put(PdfName::$P, new PdfString($parameters));
            if ($operation != NULL)
                $dic->put(PdfName::$O, new PdfString($operation));
            if ($defaultDir != NULL)
                $dic->put(PdfName::$D, new PdfString($defaultDir));
            put(PdfName::$WIN, $dic);
        }
    }

    /** Launchs an application or a document.
    * @param application the application to be launched or the document to be opened or printed.
    * @param parameters (Windows-specific) A parameter string to be passed to the application.
    * It can be <CODE>null</CODE>.
    * @param operation (Windows-specific) the operation to perform: "open" - Open a document,
    * "print" - Print a document.
    * It can be <CODE>null</CODE>.
    * @param defaultDir (Windows-specific) the default directory in standard DOS syntax.
    * It can be <CODE>null</CODE>.
    * @return a Launch action
    */
    public static function createLaunch($application, $parameters, $operation, $defaultDir) 
    {
        return new PdfAction($application, $parameters, $operation, $defaultDir);
    }

    /**Creates a Rendition action
    * @param file
    * @param fs
    * @param mimeType
    * @param ref
    * @return a Media Clip action
    * @throws IOException
    */
    public static function rendition($file, PdfFileSpecification $fs, $mimeType, PdfIndirectReference $ref) 
    {
        $js = new PdfAction();
        $js->put(PdfName::$S, PdfName::$RENDITION);
        $js->put(PdfName::$R, new PdfRendition($file, $fs, $mimeType));
        $js->put(new PdfName("OP"), new PdfNumber(0));
        $js->put(new PdfName("AN"), $ref);
        return $js;
    }


    public static function javascript()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                return javaScript2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                $arg3 = func_get_arg(2); 
                return javaScript3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    /** Creates a JavaScript action. If the JavaScript is smaller than
    * 50 characters it will be placed as a string, otherwise it will
    * be placed as a compressed stream.
    * @param code the JavaScript code
    * @param writer the writer for this action
    * @param unicode select JavaScript unicode. Note that the internal
    * Acrobat JavaScript engine does not support unicode,
    * so this may or may not work for you
    * @return the JavaScript action
    */
    public static function javaScript3args($code, PdfWriter $writer, $unicode) {
        $js = new PdfAction();
        js->put(PdfName::$S, PdfName::$JAVASCRIPT);
        if ($unicode == true && strlen($code) < 50) {
                $js->put(PdfName::$JS, new PdfString($code, PdfObject::TEXT_UNICODE));
        }
        else if ($unicode == FALSE && strlen($code) < 100) {
                $js->put(PdfName::$JS, new PdfString($code));
        }
        else {
            try {
                $b = PdfEncodings.convertToBytes($code, $unicode ? PdfObject::TEXT_UNICODE : PdfObject::TEXT_PDFDOCENCODING);
                $stream = new PdfStream($b);
                $stream->flateCompress();
                $js->put(PdfName::$JS, $writer->addToBody($stream)->getIndirectReference());
            }
            catch (Exception $e) {
                throw new Exception($e);
            }
        }
        return $js;
    }

    /** Creates a JavaScript action. If the JavaScript is smaller than
    * 50 characters it will be place as a string, otherwise it will
    * be placed as a compressed stream.
    * @param code the JavaScript code
    * @param writer the writer for this action
    * @return the JavaScript action
    */
    public static function javaScript2args($code, PdfWriter $writer) {
        return PdfAction::javaScript3args($code, $writer, FALSE);
    }

    static function createHide()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                if ($arg1 instanceof PdfObject)
                {
                    return createHide2argsPdfObject($arg1, $arg2);
                }
                else if ($arg1 instanceof PdfAnnotation)
                {
                    return createHide2argsPdfAnnotation($arg1, $arg2);
                }
                else if (strcmp(gettype($arg1,"string"))==0)
                {
                    return createHide2argsStringBool($arg1, $arg2);
                }
                else if (strcmp(getType($arg1),"array") == 0)
                {
                    return createHide2argsarray($arg1, $arg2);
                }
                break;
            }
        }
    }

    /**
    * A Hide action hides or shows an object.
    * @param obj object to hide or show
    * @param hide true is hide, false is show
    * @return a Hide Action
    */
    static function createHide2argsPdfObject(PdfObject $obj, $hide) {
        $action = new PdfAction();
        $action->put(PdfName::$S, PdfName::$HIDE);
        $action->put(PdfName::$T, $obj);
        if ($hide == FALSE)
            $action->put(PdfName.H, PdfBoolean.PDFFALSE);
        return $action;
    }
    
    /**
    * A Hide action hides or shows an annotation.
    * @param annot
    * @param hide
    * @return A Hide Action
    */
    static function createHide2argsPdfAnnotation(PdfAnnotation $annot, $hide) {
        return createHide2argsPdfObject($annot->getIndirectReference(), $hide);
    }

    /**
    * A Hide action hides or shows an annotation.
    * @param name
    * @param hide
    * @return A Hide Action
    */
    public static function createHide2argsStringBool($name, $hide) {
        return createHide2argsPdfObject(new PdfString($name), $hide);
    }

    /**
    * A Hide action hides or shows objects.
    * @param names
    * @param hide
    * @return A Hide Action
    */
    public static function createHide2argsarray($names, $hide) {
        return createHide2argsPdfObject(buildArray($names), $hide);
    }

    static function buildArray(array $names) {
        $array = new PdfArray();
        for ($k = 0; k < count($names); ++$k) {
            $obj = $names[$k];
            if (strcmp(gettype($obj), "string") == 0)
                $array->add(new PdfString($obj));
            else if ($obj instanceof PdfAnnotation)
                $array->add($obj->getIndirectReference());
            else
                throw new Exception("The array must contain String or PdfAnnotation.");
        }
        return $array;
    }

    /**
    * Creates a submit form.
    * @param file	the URI to submit the form to
    * @param names	the objects to submit
    * @param flags	submit properties
    * @return A PdfAction
    */
    public static function createSubmitForm($file, array $names, $flags) {
        $action = new PdfAction();
        $action->put(PdfName::$S, PdfName::$SUBMITFORM);
        $dic = new PdfDictionary();
        $dic->put(PdfName::$F, new PdfString($file));
        $dic->put(PdfName::$FS, PdfName::$URL);
        $action->put(PdfName::$F, $dic);
        if ($names != NULL)
            $action->put(PdfName::$FIELDS, buildArray($names));
        $action->put(PdfName::$FLAGS, new PdfNumber($flags));
        return $action;
    }

    /**
    * Creates a resetform.
    * @param names	the objects to reset
    * @param flags	submit properties
    * @return A PdfAction
    */
    public static function createResetForm(array $names, $flags) {
        $action = new PdfAction();
        $action->put(PdfName::$S, PdfName::$RESETFORM);
        if ($names != NULL)
            $action->put(PdfName::$FIELDS, buildArray($names));
        $action->put(PdfName::$FLAGS, new PdfNumber($flags));
        return $action;
    }

    /**
    * Creates an Import field.
    * @param file
    * @return A PdfAction
    */
    public static function createImportData($file) {
        $action = new PdfAction();
        $action->put(PdfName::$S, PdfName::$IMPORTDATA);
        $action->put(PdfName::$F, new PdfString($file));
        return $action;
    }

    /** Add a chained action.
    * @param na the next action
    */
    public function next(PdfAction $na) {
        $nextAction = get(PdfName::$NEXT);
        if ($nextAction == NULL)
            put(PdfName::$NEXT, $na);
        else if ($nextAction->isDictionary() == TRUE) {
            $array = new PdfArray($nextAction);
            $array->add($na);
            put(PdfName::$NEXT, $array);
        }
        else {
            $nextAction->add($na);
        }
    }
    
    static function createHide()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1);
                return  gotoLocalPage2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0); 
                $arg2 = func_get_arg(1); 
                $arg3 = func_get_arg(2); 
                return gotoLocalPage3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    /** Creates a GoTo action to an internal page.
    * @param page the page to go. First page is 1
    * @param dest the destination for the page
    * @param writer the writer for this action
    * @return a GoTo action
    */
    public static function gotoLocalPage3args($page, PdfDestination $dest, PdfWriter $writer) {
        $ref = writer.getPageReference($page);
        $dest->addPage($ref);
        $action = new PdfAction();
        $action->put(PdfName::$S, PdfName::$GOTO);
        $action->put(PdfName::$D, $dest);
        return $action;
    }

    /**
    * Creates a GoTo action to a named destination.
    * @param dest the named destination
    * @param isName if true sets the destination as a name, if false sets it as a String
    * @return a GoToR action
    */
    public static function gotoLocalPage2args($dest, $isName) {
        $action = new PdfAction();
        $action->put(PdfName::$S, PdfName::$GOTO);
        if ($isName == TRUE)
            $action->put(PdfName::$D, new PdfName($dest));
        else
            $action->put(PdfName::$D, new PdfString($dest, NULL));
        return $action;
    }


    /**
    * Creates a GoToR action to a named destination.
    * @param filename the file name to go to
    * @param dest the destination name
    * @param isName if true sets the destination as a name, if false sets it as a String
    * @param newWindow open the document in a new window if <CODE>true</CODE>, if false the current document is replaced by the new document.
    * @return a GoToR action
    */
    public static function gotoRemotePage($filename, $dest, $isName, $newWindow) {
        $action = new PdfAction();
        $action->put(PdfName::$F, new PdfString($filename));
        $action->put(PdfName::$S, PdfName::$GOTOR);
        if ($isName == TRUE)
            $action->put(PdfName::$D, new PdfName($dest));
        else
            $action->put(PdfName::$D, new PdfString($dest, NULL));
        if ($newWindow == TRUE)
            $action->put(PdfName::$NEWWINDOW, PdfBoolean::$PDFTRUE);
        return $action;
    }

    /**
    * A set-OCG-state action (PDF 1.5) sets the state of one or more optional content
    * groups.
    * @param state an array consisting of any number of sequences beginning with a <CODE>PdfName</CODE>
    * or <CODE>String</CODE> (ON, OFF, or Toggle) followed by one or more optional content group dictionaries
    * <CODE>PdfLayer</CODE> or a <CODE>PdfIndirectReference</CODE> to a <CODE>PdfLayer</CODE>.<br>
    * The array elements are processed from left to right; each name is applied
    * to the subsequent groups until the next name is encountered:
    * <ul>
    * <li>ON sets the state of subsequent groups to ON</li>
    * <li>OFF sets the state of subsequent groups to OFF</li>
    * <li>Toggle reverses the state of subsequent groups</li>
    * </ul>
    * @param preserveRB if <CODE>true</CODE>, indicates that radio-button state relationships between optional
    * content groups (as specified by the RBGroups entry in the current configuration
    * dictionary) should be preserved when the states in the
    * <CODE>state</CODE> array are applied. That is, if a group is set to ON (either by ON or Toggle) during
    * processing of the <CODE>state</CODE> array, any other groups belong to the same radio-button
    * group are turned OFF. If a group is set to OFF, there is no effect on other groups.<br>
    * If <CODE>false</CODE>, radio-button state relationships, if any, are ignored
    * @return the action
    */ 
    public static function setOCGstate(array $state, $preserveRB) {
        $action = new PdfAction();
        $action->put(PdfName::$S, PdfName::$SETOCGSTATE);
        $a = new PdfArray();
        for ($k = 0; $k < count($state); ++$k) {
            $o = $state[$k];
            if ($o == NULL)
                continue;
            if ($o instanceof PdfIndirectReference)
                $a->add($o);
            else if ($o instanceof PdfLayer)
                $a->add($o->getRef());
            else if ($o instanceof PdfName)
                $a.add($o);
            else if (strcmpy(gettype($o), "string") == 0) {
                $name = NULL;
                $s = $o;
                if (strcasecmp($s, "on") == 0)
                    $name = PdfName::$ON;
                else if (strcasecmp($s, "off") == 0)
                    $name = PdfName::$OFF;
                else if (strcasecmp($s, "toggle") == 0)
                    $name = PdfName::$TOGGLE;
                else
                    throw new IllegalArgumentException("A string '" . $s . " was passed in state. Only 'ON', 'OFF' and 'Toggle' are allowed.");
                $a->add($name);
            }
            else
                throw new IllegalArgumentException("Invalid type was passed in state: " . $o);
        }
        $action->put(PdfName::$STATE, $a);
        if ($preserveRB == FALSE)
            $action->put(PdfName::$PRESERVERB, PdfBoolean::$PDFFALSE);
        return $action;
    }
}

?>