<?PHP
/*
 * $Id: AcroFields.php,v 1.4 2005/10/18 16:23:20 mstaylor Exp $
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
require_once("../Element.php");
require_once("../DocumentException.php");
require_once("../../exceptions/IOException.php");
require_once("../../exceptions/NullPointerException.php");
require_once("../../exceptions/IndexOutOfBoundsException.php");
require_once("../../awt/Color.php");
require_once("../../io/InputStream.php");
require_once("PdfReader.php");
require_once("PdfWriter.php");
require_once("PdfStamperImp.php");
require_once("PdfDictionary.php");
require_once("PdfArray.php");
require_once("PdfName.php");
require_once("PdfObject.php");
require_once("PdfIndirectReference.php");
require_once("PdfString.php");
require_once("PdfNumber.php");
require_once("PdfFormField.php");
require_once("FdfWriter.php");
require_once("PRTokeniser.php");
require_once("PdfEncodings.php");
require_once("GrayColor.php");
require_once("CMYKColor.php");
require_once("PdfAppearance.php");
require_once("TextField.php");
require_once("DocumentFont.php");
require_once("PRIndirectReference.php");
require_once("BaseFont.php");
require_once("PdfBorderDictionary.php");
require_once("FontDetails.php");
require_once("DocumentFont.php");
require_once("ByteBuffer.php");
require_once("FdfReader.php");
require_once("XfdfReader.php");
require_once("PdfBoolean.php");
require_once("PdfPKCS7.php");
require_once("PdfDate.php");
require_once("RandomAccessFileOrArray.php");


/** Query and change fields in existing documents either by method
* calls or by FDF merging.
* @author Paulo Soares (psoares@consiste.pt)
*/

class AcroFields
{

    $reader = NULL;
    $writer = NULL;
    $fields = array();
    private $topFirst = 0;
    private $sigNames = array();
    private $append = FALSE;
    static private $DA_FONT = 0;
    static private $DA_SIZE = 1;
    static private $DA_COLOR = 2;
    /**
    * A field type invalid or not found.
    */
    const FIELD_TYPE_NONE = 0;
    /**
    * A field type.
    */
    const FIELD_TYPE_PUSHBUTTON = 1;
    /**
    * A field type.
    */
    const FIELD_TYPE_CHECKBOX = 2;
    /**
    * A field type.
    */
    const FIELD_TYPE_RADIOBUTTON = 3;
    /**
    * A field type.
    */    
    const FIELD_TYPE_TEXT = 4;
    /**
    * A field type.
    */
    const FIELD_TYPE_LIST = 5;
    /**
    * A field type.
    */
    const FIELD_TYPE_COMBO = 6;
    /**
    * A field type.
    */
    const FIELD_TYPE_SIGNATURE = 7;

    private $lastWasString = FALSE;

    /** Holds value of property generateAppearances. */
    private $generateAppearances = TRUE;

    private $localFonts = array();

    public function __construct(PdfReader $reader, PdfWriter $writer) {
        $this->reader = $reader;
        $this->writer = $writer;
        if ($writer instanceof PdfStamperImp) {
            $append = ($writer)->isAppend();
        }
        fill();
    }


    function fill() {
        $fields = array();
        $top = PdfReader::getPdfObject($reader->getCatalog()->get(PdfName::$ACROFORM));
        for ($k = 1; $k <= $reader->getNumberOfPages(); ++$k) {
            $page = $reader->getPageN($k);
            $annots = PdfReader::getPdfObject($page->get(PdfName::$ANNOTS), $page);
            if ($annots == NULL)
                continue;
            $arr = $annots->getArrayList();
            for ($j = 0; $j < count($arr); ++$j) {
                $annoto = PdfReader::getPdfObject($arr[$j], $annots);
                if (($annoto instanceof PdfIndirectReference) && $annoto->isIndirect() == FALSE)
                    continue;
                $annot = $annoto;
                if (PdfName::$WIDGET->equals($annot->get(PdfName::$SUBTYPE)) == FALSE)
                    continue;
                $widget = $annot;
                $dic = new PdfDictionary();
                $dic->putAll($annot);
                $name = itextphp_newString("",2,2);
                $value = NULL;
                $lastV = NULL;
                while ($annot != NULL) {
                    $dic->mergeDifferent($annot);
                    $t = PdfReader::getPdfObject($annot->get(PdfName::$T));
                    if ($t != NULL)
                    {
                        $name = $t->toUnicodeString();
                        $tdot = itextphp_string_append($name, ".");
                        $name = itextphp_string_append($tdot, $name);
                    }
                    if ($lastV == NULL && $annot->get(PdfName::$V) != NULL)
                        $lastV = PdfReader::getPdfObject($annot->get(PdfName::$V));
                    if ($value == NULL &&  $t != NULL) {
                        $value = $annot;
                        if ($annot->get(PdfName::$V) == NULL && $lastV  != NULL)
                            $value->put(PdfName::$V, $lastV);
                    }
                    $annot = PdfReader::getPdfObject($annot->get(PdfName::$PARENT), $annot);
                }
                if (itextphp_StringLength($name) > 0)
                    name = itextphp_string_substring($name, 0, itextphp_StringLength($name) - 1);
                $item = $fields[$name];
                if ($item == NULL) {
                    $item = new Item();
                    $fields[$name] = $item;
                }
                if ($value == NULL)
                    array_push($item->values, $widget);
                else
                    array_push($item->values, $value);
                array_push($item->widgets, $widget);
                array_push($item->widget_refs, $arr[$j]); // must be a reference
                if ($top != NULL)
                    $dic->mergeDifferent($top);
                array_push($item, $dic);
                array_push($item->page, $k);
                array_push($item->tabOrder, $j);
            }
        }
    }


    /** Gets the list of appearance names. Use it to get the names allowed
    * with radio and checkbox fields. If the /Opt key exists the values will
    * also be included. The name 'Off' may also be valid
    * even if not returned in the list.
    * @param fieldName the fully qualified field name
    * @return the list of names or <CODE>null</CODE> if the field does not exist
    */
    public function getAppearanceStates($fieldName) {
        $fd = $fields[$fieldName];
        if ($fd == NULL)
            return NULL;
        $names = array();
        $vals = $fd->values[0];
        $opts = PdfReader::getPdfObject($vals->get(PdfName::$OPT));
        if ($opts != NULL) {
            if ($opts->isString() == TRUE)
                $names[($opts)->toUnicodeString()] = NULL;
            else if ($opts->isArray() == TRUE) {
                $list = $opts->getArrayList();
                for ($k = 0; $k < count($list); ++$k) {
                    $v = PdfReader::getPdfObject($list[$k]);
                    if ($v != NULL && $v->isString() == TRUE)
                        $names[$v->toUnicodeString()] = NULL;
                }
            }
        }
        $wd = $fd->widgets;
        for ($k = 0; $k < count($wd); ++$k) {
            $dic = $wd[$k];
            $dic = PdfReader::getPdfObject($dic->get(PdfName::$AP));
            if ($dic == NULL)
                continue;
            $ob = PdfReader::getPdfObject($dic->get(PdfName::$N));
            if ($ob == NULL || $ob->isDictionary() == FALSE)
                continue;
            $dic = $ob;
            foreach ($dic->getKeys() as &$aname) {
                $name = PdfName::decodeName($aname->toString());
                $names[$name] = NULL;
            }
        }
        $out = array();
        $keysArray = array_keys($names);
        for ($k = 0; $k < count($keysArray); $k++)
        {
            $out[$k] = $keysArray[$k];
        }
        return $out;
    }

    /**
    * Gets the field type. The type can be one of: <CODE>FIELD_TYPE_PUSHBUTTON</CODE>,
    * <CODE>FIELD_TYPE_CHECKBOX</CODE>, <CODE>FIELD_TYPE_RADIOBUTTON</CODE>,
    * <CODE>FIELD_TYPE_TEXT</CODE>, <CODE>FIELD_TYPE_LIST</CODE>,
    * <CODE>FIELD_TYPE_COMBO</CODE> or <CODE>FIELD_TYPE_SIGNATURE</CODE>.
    * <p>
    * If the field does not exist or is invalid it returns
    * <CODE>FIELD_TYPE_NONE</CODE>.
    * @param fieldName the field name
    * @return the field type
    */
    public function getFieldType($fieldName) {
        $fd = $fields[$fieldName];
        if ($fd == NULL)
            return AcroFields::FIELD_TYPE_NONE;
        $type = PdfReader::getPdfObject(($fd->merged[0])->get(PdfName::$FT));
        if ($type == NULL)
            return AcroFields::FIELD_TYPE_NONE;
        $ff = 0;
        $ffo = PdfReader::getPdfObject(($fd->merged[0])->get(PdfName::$FF));
        if ($ffo != NULL && $ffo->type() == PdfObject::NUMBER)
            $ff = ($ffo)->intValue();
        if (PdfName::$BTN->equals($type)) {
            if (($ff & PdfFormField::FF_PUSHBUTTON) != 0)
                return AcroFields::FIELD_TYPE_PUSHBUTTON;
            if (($ff & PdfFormField::FF_RADIO) != 0)
                return AcroFields::FIELD_TYPE_RADIOBUTTON;
            else
                return AcroFields::FIELD_TYPE_CHECKBOX;
        }
        else if (PdfName::$TX->equals($type)) {
            return AcroFields::FIELD_TYPE_TEXT;
        }
        else if (PdfName::$CH->equals($type)) {
            if (($ff & PdfFormField::FF_COMBO) != 0)
                return AcroFields::FIELD_TYPE_COMBO;
            else
                return AcroFields::FIELD_TYPE_LIST;
        }
        else if (PdfName::$SIG->equals($type)) {
            return AcroFields::FIELD_TYPE_SIGNATURE;
        }
        return AcroFields::FIELD_TYPE_NONE;
    }

    /**
    * Export the fields as a FDF.
    * @param writer the FDF writer
    */
    public function exportAsFdf(FdfWriter $writer) {
        foreach ($fields as &$entry) {
            item = $fields[$entry];
            $name = $entry;
            $v = PdfReader::getPdfObject(($item->merged[0])->get(PdfName::$V));
            if ($v == NULL)
                continue;
            $value = getField($name);
            if ($lastWasString == TRUE)
                $writer->setFieldAsString($name, $value);
            else
                $writer->setFieldAsName($name, $value);
        }
    }

    /**
    * Renames a field. Only the last part of the name can be renamed. For example,
    * if the original field is "ab.cd.ef" only the "ef" part can be renamed.
    * @param oldName the old field name
    * @param newName the new field name
    * @return <CODE>true</CODE> if the renaming was successful, <CODE>false</CODE>
    * otherwise
    */
    public function renameField($oldName, $newName) {
        $dotString = itextphp_newString('.', 2, 2);
        $idx1 = itextphp_string_lastIndexOf($oldName,$dotString) + 1;
        $idx2 = itextphp_string_lastIndexOf($newName,$dotString)  + 1;
        if ($idx1 != $idx2)
            return FALSE;
        if (itextphp_string_equals(itextphp_string_substring($oldName, 0, $idx1), itextphp_string_substring($newName, 0, $idx2)) == FALSE)
            return FALSE;
        if (array_key_exists($newName, $fields) == TRUE)
            return FALSE;
        $item = $fields[$oldName];
        if ($item == NULL)
            return FALSE;
        $newName = itextphp_string_substring($newName, $idx2);
        $ss = new PdfString(itextphp_string_toPHPString($newName), PdfObject::TEXT_UNICODE);
        for ($k = 0; $k < count($item->merged); ++$k) {
            $dic = $item->values[$k];
            $dic->put(PdfName::$T, $ss);
            markUsed($dic);
            $dic = $item->merged[$k];
            $dic->put(PdfName::$T, $ss);
        }
        unset($fields[$oldName]);
        $fields[$newName] = $item;
        return TRUE;
    }

    static private function splitDAelements($da) {
        try {
            $da = itextphp_string_toPHPString($da);
            $tk = new PRTokeniser(PdfEncodings::convertToBytes($da, null));
            $stack = array();
            $ret = array();
            while ($tk->nextToken()) {
                if ($tk->getTokenType() == PRTokeniser::TK_COMMENT)
                    continue;
                if ($tk->getTokenType() == PRTokeniser::TK_OTHER) {
                    $operator = $tk->getStringValue();
                    if (strcmp($operator, "Tf") == 0) {
                        if (count($stack) >= 2) {
                            $ret[AcroFields::$DA_FONT] = $stack[count($stack) - 2];
                            $ret[AcroFields::$DA_SIZE] = (float)$stack[count($stack) - 1];
                        }
                    }
                    else if (strcmp($operator, "g") == 0) {
                        if (count($stack) >= 1) {
                            $gray = (float)$stack[count($stack) - 1];
                            if ($gray != 0)
                                $ret[AcroFields::$DA_COLOR] = new GrayColor($gray);
                        }
                    }
                    else if (strcmp($operator, "rg") == 0) {
                        if (count($stack) >= 3) {
                            $red = (float)$stack[count($stack) - 3];
                            $green = (float)$stack[count($stack) - 2];
                            $blue = (flaot)$stack[count($stack) - 1];
                            $ret[AcroFields::$DA_COLOR] = new Color($red, $green, $blue);
                        }
                    }
                    else if (strcmp($operator, "k") == 0) {
                        if (count($stack) >= 4) {
                            $cyan = (float)$stack[count($stack) - 4];
                            $magenta = (float)$stack[count($stack) - 3];
                            $yellow = (float)$stack[count($stack) - 2];
                            $black = (float)$stack[count($stack) - 1];
                            $ret[AcroFields::$DA_COLOR] = new CMYKColor($cyan, $magenta, $yellow, $black);
                        }
                    }
                    $stack = array();
                }
                else
                    array_push($stack, $tk->getStringValue());
            }
            return $ret;
        }
        catch (IOException $ioe) {
            throw new Exception($ioe);
        }
    }


    function getAppearance(PdfDictionary $merged, $text, $fieldName) {
        $topFirst = 0;
        $int flags = 0;
        $tx = NULL;
        if ($fieldCache == NULL || array_key_exists($fieldName, $fieldCache) == FALSE) {
            $tx = new TextField($writer, NULL, NULL);
            $tx->setBorderWidth(0);
            // the text size and color
            $da = PdfReader::getPdfObject($merged->get(PdfName::$DA));
            if ($da != NULL) {
                $dab = AcroFields::splitDAelements($da->toUnicodeString());
                if ($dab[AcroFields::$DA_SIZE] != NULL)
                    $tx->setFontSize(((float)$dab[AcroFields::$DA_SIZE]));
                if ($dab[AcroFields::$DA_COLOR] != NULL)
                    $tx->setTextColor($dab[AcroFields::$DA_COLOR]);
                if ($dab[AcroFields::$DA_FONT] != NULL) {
                    $font = PdfReader::getPdfObject($merged->get(PdfName::$DR));
                    if ($font != NULL) {
                        $font = PdfReader::getPdfObject($font.get(PdfName::$FONT));
                        if ($font != NULL) {
                            $po = $font->get(new PdfName((string)$dab[AcroFields::$DA_FONT]));
                            if ($po != NULL && $po->type() == PdfObject::INDIRECT)
                                $tx->setFont(new DocumentFont($po));
                            else {
                                $bf = $localFonts[$dab[AcroFields::$DA_FONT]];
                                if ($bf == NULL) {
                                    $fn = stdFieldFontNames[$dab[AcroFields::$DA_FONT]];
                                    if ($fn != NULL) {
                                        try {
                                            $enc = "winansi";
                                            if (count($fn) > 1)
                                                $enc = $fn[1];
                                            $bf = BaseFont::createFont($fn[0], $enc, FALSE);
                                            $tx->setFont($bf);
                                        }
                                        catch (Exception $e) {
                                            // empty
                                        }
                                    }
                                }
                                else
                                    $tx->setFont($bf);
                            }
                        }
                    }
                }
            }
            //rotation, border and backgound color
            $mk = PdfReader::getPdfObject($merged->get(PdfName::$MK));
            if ($mk != NULL) {
                $ar = PdfReader::getPdfObject($mk->get(PdfName::$BC));
                $border = getMKColor($ar);
                $tx->setBorderColor($border);
                if ($border != NULL)
                    $tx->setBorderWidth(1);
                $ar = PdfReader::getPdfObject($mk->get(PdfName::$BG));
                $tx->setBackgroundColor(getMKColor($ar));
                $rotation = PdfReader::getPdfObject($mk->get(PdfName::$R));
                if ($rotation != NULL)
                    $tx->setRotation((integer)$rotation);
            }
            //multiline
            $nfl = PdfReader::getPdfObject($merged->get(PdfName::$FF));
            if ($nfl != NULL)
                $flags = (integer)$nfl;
            $tx->setOptions((($flags & PdfFormField::FF_MULTILINE) == 0 ? 0 : TextField::MULTILINE) | ((flags & PdfFormField::FF_COMB) == 0 ? 0 : TextField::COMB));
            if (($flags & PdfFormField::FF_COMB) != 0) {
                $maxLen = PdfReader::getPdfObject($merged->get(PdfName::$MAXLEN));
                $len = 0;
                if ($maxLen != NULL)
                    $len = (integer)maxLen;
                $tx->setMaxCharacterLength($len);
            }
            //alignment
            $nfl = PdfReader::getPdfObject($merged->get(PdfName::$Q));
            if ($nfl != NULL) {
                if ((integer)$nfl == PdfFormField::Q_CENTER)
                    $tx->setAlignment(Element::ALIGN_CENTER);
                else if ((integer)$nfl == PdfFormField::Q_RIGHT)
                    $tx->setAlignment(Element::ALIGN_RIGHT);
            }
            //border styles
            $bs = PdfReader::getPdfObject($merged->get(PdfName::$BS));
            if ($bs != NULL) {
                $w = PdfReader::getPdfObject($bs->get(PdfName::$W));
                if ($w != NULL)
                    $tx->setBorderWidth((float)$w);
                $s = PdfReader::getPdfObject($bs->get(PdfName::$S));
                if (PdfName::$D->equals($s))
                    $tx->setBorderStyle(PdfBorderDictionary::STYLE_DASHED);
                else if (PdfName::$B->equals($s))
                    $tx->setBorderStyle(PdfBorderDictionary::STYLE_BEVELED);
                else if (PdfName::$I->equals($s))
                    $tx->setBorderStyle(PdfBorderDictionary::STYLE_INSET);
                else if (PdfName::$U->equals($s))
                    $tx->setBorderStyle(PdfBorderDictionary::STYLE_UNDERLINE);
            }
            else {
                $bd = PdfReader::getPdfObject($merged->get(PdfName::$BORDER));
                if ($bd != NULL) {
                    $ar = $bd->getArrayList();
                    if (count($ar) >= 3)
                        $tx->setBorderWidth((float)$ar[2]);
                    if (count($ar) >= 4)
                        $tx->setBorderStyle(PdfBorderDictionary::STYLE_DASHED);
                }
            }
            //rect
            $rect = PdfReader::getPdfObject($merged->get(PdfName::$RECT));
            $box = PdfReader::getNormalizedRectangle($rect);
            if ($tx->getRotation() == 90 || $tx->getRotation() == 270)
                $box = $box->rotate();
            $tx->setBox($box);
            if ($fieldCache != NULL)
                $fieldCache[$fieldName] = $tx;
        }
        else {
            $tx = $fieldCache[$fieldName];
            $tx->setWriter($writer);
        }
        $fieldType = PdfReader::getPdfObject($merged->get(PdfName::$FT));
        if (PdfName::$TX->equals($fieldType) == TRUE) {
            $tx->setText($text);
            return $tx->getAppearance();
        }
        if (PdfName::$CH->equals($fieldType) == FALSE)
            throw new DocumentException("An appearance was requested without a variable text field.");
        $opt = PdfReader::getPdfObject($merged->get(PdfName::$OPT));
        if (($flags & PdfFormField::FF_COMBO) != 0 && $opt == NULL) {
            $tx->setText($text);
            return $tx->getAppearance();
        }
        $arrsize = 0;
        if ($opt != NULL) {
            $op = $opt->getArrayList();
            $choices = array();
            $choicesExp = array();
            for ($k = 0; $k < count($op); ++$k) {
                $obj = $op[$k];
                if ($obj->isString() == TRUE) {
                    $choices[$k] = $choicesExp[$k] = $obj->toUnicodeString();
                }
                else {
                    $opar = $obj->getArrayList();
                    $choicesExp[$k] = $opar[0]->toUnicodeString();
                    $choices[$k] = $opar[1]->toUnicodeString();
                }
            }
            if (($flags & PdfFormField::FF_COMBO) != 0) {
                $tmpText = itextphp_newString($text,strlen($text),2);
                for ($k = 0; k < count($choices); ++$k) {
                    if (itextphp_string_equals($tmpText, $choicesExp[$k]) == TRUE) {
                        $text = $choices[$k];
                        break;
                    }
                }
                $tx->setText($text);
                return $tx->getAppearance();
            }
            $idx = 0;
            for ($k = 0; $k < count($choices); ++$k) {
                $tmpText = itextphp_newString($text,strlen($text),2);
                if (itextphp_string_equals($tmpText, $choices[$k]) == TRUE) {
                    $idx = $k;
                    break;
                }
            }
            $tx->setChoices($choices);
            $tx->setChoiceExports($choicesExp);
            $tx->setChoiceSelection($idx);
        }
        $app = $tx->getListAppearance();
        $topFirst = $tx->getTopFirst();
        return $app;
    }

    function getMKColor(PdfArray $ar) {
        if ($ar == NULL)
            return null;
        $cc = $ar->getArrayList();
        switch (count($cc)) {
            case 1:
                return new GrayColor((float)$cc[0]);
            case 3:
                return new Color((float)$cc[0], (float)$cc[1], (float)$cc[2]);
            case 4:
                return new CMYKColor((float)$cc[0], (float)$cc[1], (float)$cc[2], (float)$cc[3]);
            default:
                return NULL;
        }
    }

    /** Gets the field value.
    * @param name the fully qualified field name
    * @return the field value
    */
    public function getField($name) {
        $item = fields[$name];
        if ($item == NULL)
            return NULL;
        $lastWasString = FALSE;
        $v = PdfReader::getPdfObject($item->merged[0]->get(PdfName::$V));
        if ($v == NULL)
            return itextphp_newString("",2,2);
        $type = PdfReader::getPdfObject($item->merged[0]->get(PdfName::$FT));
        if (PdfName::$BTN->equals($type) == TRUE) {
            $ff = PdfReader::getPdfObject($item->merged[0]->get(PdfName::$FF));
            $flags = 0;
            if ($ff != NULL)
                $flags = $ff->intValue();
            if (($flags & PdfFormField::FF_PUSHBUTTON) != 0)
                return itextphp_newString("",2,2);
            $value = itextphp_newString("",2,2);
            if ($v->isName() == TRUE)
            {
                $value = PdfName::decodeName($v->toString());
                $value = itextphp_newString($value, strlen($value), 2);
            }
            else if ($v->isString() == TRUE)
                $value = $v->toUnicodeString();
            $opts = PdfReader::getPdfObject($item->values[0]->get(PdfName::$OPT));
            if ($opts != NULL && $opts->isArray() == TRUE) {
                $list = $opts->getArrayList();
                $idx = 0;
                try {
                    $idx = itextphp_string_toInteger($value);
                    $ps = $list[$idx];
                    $value = $ps->toUnicodeString();
                    $lastWasString = TRUE;
                }
                catch (Exception $e) {
                }
            }
            return $value;
        }
        if ($v->isString()  == TRUE) {
            $lastWasString = TRUE;
            $value = $v->toUnicodeString();
            $value = itextphp_newString($value, strlen($value), 2);
            return $value;
        }
        $value =  PdfName::decodeName($v->toString());
        $value = itextphp_newString($value, strlen($value), 2);
        return $value;
    }


    public function setFieldProperty()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(0);
                if (is_integer($arg3) == TRUE )
                {
                    return setFieldPropertyInteger($arg1, $arg2, $arg3, $arg4);
                }
                else
                {
                    return setFieldPropertyObject($arg1, $arg2, $arg3, $arg4);
                }
                break;
            }
        }
    }

    /**
    * Sets a field property. Valid property names are:
    * <p>
    * <ul>
    * <li>textfont - sets the text font. The value for this entry is a <CODE>BaseFont</CODE>.<br>
    * <li>textcolor - sets the text color. The value for this entry is a <CODE>java.awt.Color</CODE>.<br>
    * <li>textsize - sets the text size. The value for this entry is a <CODE>Float</CODE>.
    * <li>bgcolor - sets the background color. The value for this entry is a <CODE>java.awt.Color</CODE>.
    *     If <code>null</code> removes the background.<br>
    * <li>bordercolor - sets the border color. The value for this entry is a <CODE>java.awt.Color</CODE>.
    *     If <code>null</code> removes the border.<br>
    * </ul>
    * @param field the field name
    * @param name the property name
    * @param value the property value
    * @param inst an array of <CODE>int</CODE> indexing into <CODE>AcroField.Item.merged</CODE> elements to process.
    * Set to <CODE>null</CODE> to process all
    * @return <CODE>true</CODE> if the property exists, <CODE>false</CODE> otherwise
    */
    private function setFieldPropertyObject($field, $name, $value, array $inst) {
        if (is_resource($name)  == TRUE)
            $name = itextphp_string_toPHPString($name);

        if ($writer == NULL)
            throw new Exception("This AcroFields instance is read-only.");
        try {
            $item = $fields[$field];
            if ($item == NULL)
                return FALSE;
            $hit = new InstHit($inst);
            if (strcasecmp($name, "textfont") == 0) {
                for ($k = 0; k < count($item->merged); ++$k) {
                    if ($hit->isHit($k) == TRUE) {
                        $da = PdfReader::getPdfObject($item->merged[$k]->get(PdfName::$DA));
                        $dr = PdfReader::getPdfObject($item->merged[$k]->get(PdfName::$DR));
                        if ($da != NULL && $dr != NULL) {
                            $dao = AcroFields::splitDAelements($da->toUnicodeString());
                            $cb = new PdfAppearance();
                            if ($dao[AcroFields::$DA_FONT] != NULL) {
                                $bf = $value;
                                $psn = PdfAppearance::$stdFieldFontNames[$bf->getPostscriptFontName()];
                                if ($psn == NULL) {
                                    $psn = new PdfName($bf->getPostscriptFontName());
                                }
                                $fonts = PdfReader::getPdfObject($dr->get(PdfName::$FONT));
                                if ($fonts == NULL) {
                                    $fonts = new PdfDictionary();
                                    $dr->put(PdfName::$FONT, $fonts);
                                }
                                $fref = $fonts->get($psn);
                                $top = PdfReader::getPdfObject($reader->getCatalog()->get(PdfName::$ACROFORM));
                                markUsed($top);
                                $dr = PdfReader::getPdfObject($top->get(PdfName::$DR));
                                if ($dr == NULL) {
                                    $dr = new PdfDictionary();
                                    $top->put(PdfName::$DR, $dr);
                                }
                                markUsed($dr);
                                $fontsTop = PdfReader::getPdfObject($dr->get(PdfName::$FONT));
                                if ($fontsTop == NULL) {
                                    $fontsTop = new PdfDictionary();
                                    $dr->put(PdfName::$FONT, $fontsTop);
                                }
                                markUsed($fontsTop);
                                $frefTop = fontsTop->get($psn);
                                if ($frefTop != NULL) {
                                    if ($fref == NULL)
                                        $fonts->put($psn, $frefTop);
                                }
                                else if ($fref == NULL) {
                                    $fd = NULL;
                                    if ($bf->getFontType() == BaseFont::FONT_TYPE_DOCUMENT) {
                                        $fd = new FontDetails(NULL, $bf->getIndirectReference(), $bf);
                                    }
                                    else {
                                        $bf->setSubset(FALSE);
                                        $fd = $writer->addSimple($bf);
                                        $localFonts[substr($psn->toString(), 1)] = $bf;
                                    }
                                    $fontsTop->put($psn, $fd->getIndirectReference());
                                    $fonts->put($psn, $fd->getIndirectReference());
                                }
                                $buf = $cb->getInternalBuffer();
                                $buf->append($psn->getBytes())->append(' ')->append(((float)$dao[AcroFields::$DA_SIZE]))->append(" Tf ");
                                if ($dao[AcroFields::$DA_COLOR] != NULL)
                                    $cb->setColorFill($dao[AcroFields::$DA_COLOR]);
                                $s = new PdfString($cb->toString());
                                $item->merged[$k]->put(PdfName::$DA, $s);
                                $item->widgets[$k]->put(PdfName::$DA, $s);
                                markUsed($item->widgets[$k]);
                            }
                        }
                    }
                }
            }
            else if (strcasecmp($name, "textcolor") == 0) {
                for ($k = 0; $k < count($item->merged); ++$k) {
                    if ($hit->isHit($k) == TRUE) {
                        $da = PdfReader::getPdfObject($item->merged[$k]->get(PdfName::$DA));
                        if ($da != NULL) {
                            $dao = AcroFields::splitDAelements($da->toUnicodeString());
                            $cb = new PdfAppearance();
                            if ($dao[AcroFields::$DA_FONT] != NULL) {
                                $buf = $cb->getInternalBuffer();
                                $buf->append(new PdfName((string)$dao[AcroFields::$DA_FONT])->getBytes())->append(' ')->append(((float)$dao[AcroFields::$DA_SIZE]))->append(" Tf ");
                                $cb->setColorFill($value);
                                $s = new PdfString($cb->toString());
                                $item->merged[$k]->put(PdfName::$DA, $s);
                                $item->widgets[$k]->put(PdfName::$DA, $s);
                                markUsed($item->widgets[$k]);
                            }
                        }
                    }
                }
            }
            else if (strcasecmp($name, "textsize") == 0) {
                for ($k = 0; $k < count($item->merged); ++$k) {
                    if ($hit->isHit($k) == TRUE) {
                        $da = PdfReader::getPdfObject($item->merged[k]->get(PdfName::$DA));
                        if ($da != NULL) {
                            $dao = AcroFields::splitDAelements($da->toUnicodeString());
                            $cb = new PdfAppearance();
                            if ($dao[AcroFields::$DA_FONT] != NULL) {
                                $buf = $cb->getInternalBuffer();
                                $buf->append(new PdfName((string)$dao[AcroFields::$DA_FONT])->getBytes())->append(' ')->append(((float)$value))->append(" Tf ");
                                if ($dao[AcroFields::$DA_COLOR] != NULL)
                                    $cb->setColorFill($dao[AcroFields::$DA_COLOR]);
                                $s = new PdfString($cb->toString());
                                $item->merged[$k]->put(PdfName::$DA, $s);
                                $item->widgets[$k]->put(PdfName::$DA, $s);
                                markUsed($item->widgets[$k]);
                            }
                        }
                    }
                }
            }
            else if (strcasecmp($name, "bgcolor") == 0 || strcasecmp($name, "bordercolor") == 0) {
                $dname = (strcasecmp($name, "bgcolor") ? PdfName::$BG : PdfName::$BC);
                for ($k = 0; $k < count($item->merged); ++$k) {
                    if ($hit->isHit($k) == TRUE) {
                       $obj = PdfReader::getPdfObject($item->merged[$k]->get(PdfName::$MK));
                        markUsed($obj);
                        $mk = $obj;
                        if ($mk == NULL) {
                            if ($value == NULL)
                                return TRUE;
                            $mk = new PdfDictionary();
                            $item->merged[$k]->put(PdfName::$MK, $mk);
                            $item->widgets[$k]->put(PdfName::$MK, $mk);
                            markUsed($item->widgets[$k]);
                        }
                        if ($value == NULL)
                            $mk->remove($dname);
                        else
                            $mk->put($dname, PdfFormField::getMKColor($value));
                    }
                }
            }
            else
                return FALSE;
            return TRUE;
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    /**
    * Sets a field property. Valid property names are:
    * <p>
    * <ul>
    * <li>flags - a set of flags specifying various characteristics of the field’s widget annotation.
    * The value of this entry replaces that of the F entry in the form’s corresponding annotation dictionary.<br>
    * <li>setflags - a set of flags to be set (turned on) in the F entry of the form’s corresponding
    * widget annotation dictionary. Bits equal to 1 cause the corresponding bits in F to be set to 1.<br>
    * <li>clrflags - a set of flags to be cleared (turned off) in the F entry of the form’s corresponding
    * widget annotation dictionary. Bits equal to 1 cause the corresponding
    * bits in F to be set to 0.<br>
    * <li>fflags - a set of flags specifying various characteristics of the field. The value
    * of this entry replaces that of the Ff entry in the form’s corresponding field dictionary.<br>
    * <li>setfflags - a set of flags to be set (turned on) in the Ff entry of the form’s corresponding
    * field dictionary. Bits equal to 1 cause the corresponding bits in Ff to be set to 1.<br>
    * <li>clrfflags - a set of flags to be cleared (turned off) in the Ff entry of the form’s corresponding
    * field dictionary. Bits equal to 1 cause the corresponding bits in Ff
    * to be set to 0.<br>
    * </ul>
    * @param field the field name
    * @param name the property name
    * @param value the property value
    * @param inst an array of <CODE>int</CODE> indexing into <CODE>AcroField.Item.merged</CODE> elements to process.
    * Set to <CODE>null</CODE> to process all
    * @return <CODE>true</CODE> if the property exists, <CODE>false</CODE> otherwise
    */
    private function setFieldPropertyInteger($field, $name, $value, array $inst) {
        if ($writer == NULL)
            throw new Exception("This AcroFields instance is read-only.");
        $item = $fields[$field];
        if ($item == NULL)
            return FALSE;
        $hit = new InstHit($inst);
        if (strcasecmp($name, "flags") == 0) {
            $num = new PdfNumber($value);
            for ($k = 0; $k < count($item->merged); ++$k) {
                if ($hit->isHit($k) == TRUE) {
                    $item->merged[$k]->put(PdfName::$F, $num);
                    $item->widgets[$k]->put(PdfName::$F, $num);
                    markUsed($item->widgets[$k]);
                }
            }
        }
        else if (strcasecmp($name, "setflags") == 0) {
            for ($k = 0; $k < count($item->merged); ++$k) {
                if ($hit->isHit($k) == TRUE) {
                    $num = PdfReader::getPdfObject($item->widgets[$k]->get(PdfName::$F));
                    $val = 0;
                    if ($num != NULL)
                        $val = $num->intValue();
                    $num = new PdfNumber($val | $value);
                    $item->merged[$k]->put(PdfName::$F, $num);
                    $item->widgets[$k]->put(PdfName::$F, $num);
                    markUsed($item->widgets[$k]);
                }
            }
        }
        else if (strcasecmp($name, "clrflags") == 0) {
            for ($k = 0; $k < count($item->merged); ++$k) {
                if ($hit->isHit($k) == TRUE) {
                    $num = PdfReader::getPdfObject($item->widgets[$k]->get(PdfName::$F));
                    $val = 0;
                    if ($num != NULL)
                        $val = $num->intValue();
                    $num = new PdfNumber($val & (~$value));
                    $item->merged[$k]->put(PdfName::$F, $num);
                    $item->widgets[$k]->put(PdfName::$F, $num);
                    markUsed($item->widgets[$k]);
                }
            }
        }
        else if (strcasecmp($name, "fflags") == 0) {
            $num = new PdfNumber($value);
            for ($k = 0; $k < count($item->merged); ++$k) {
                if ($hit->isHit($k) == TRUE) {
                    $item->merged[$k]->put(PdfName::$FF, $num);
                    $item->values[$k]->put(PdfName::$FF, $num);
                    markUsed($item->values[$k]);
                }
            }
        }
        else if (strcasecmp($name, "setfflags") == 0) {
            for ($k = 0; $k < count($item->merged); ++$k) {
                if ($hit->isHit($k) == TRUE) {
                    $num = PdfReader::getPdfObject($item->values[$k]->get(PdfName::$FF));
                    $val = 0;
                    if ($num != NULL)
                        $val = $num->intValue();
                    $num = new PdfNumber($val | $value);
                    $item->merged[$k]->put(PdfName::$FF, $num);
                    $item->values[$k]->put(PdfName::$FF, $num);
                    markUsed($item->values[$k]);
                }
            }
        }
        else if (strcasecmp($name, "clrfflags") == 0) {
            for ($k = 0; $k < count($item->merged); ++$k) {
                if ($hit->isHit($k) == TRUE) {
                    $num = PdfReader::getPdfObject($item->values[$k]->get(PdfName::$FF));
                    $val = 0;
                    if ($num != NULL)
                        $val = $num->intValue();
                    $num = new PdfNumber($val & (~$value));
                    $item->merged[$k]->put(PdfName::$FF, $num);
                    $item->values[$k]->put(PdfName::$FF, $num);
                    markUsed($item->values[$k]);
                }
            }
        }
        else
            return FALSE;
        return TRUE;
    }

    public function setFields()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof FdfReader)
                    setFieldsFdfReader($arg1);
                else if ($arg1 instanceof XfdfReader)
                    setFieldsXfdfReader($arg1);
                break;
            }
        }
    }


    /** Sets the fields by FDF merging.
    * @param fdf the FDF form
    * @throws IOException on error
    * @throws DocumentException on error
    */
    private function setFieldsFdfReader(FdfReader $fdf) {
        $fd = $fdf->getFields();
        foreach (array_keys($arr) as &$f) {
            $v = $fdf->getFieldValue($f);
            if ($v != NULL)
                setField($f, $v);
        }
    }


    /** Sets the fields by XFDF merging.
    * @param xfdf the XFDF form
    * @throws IOException on error
    * @throws DocumentException on error
    */

    private function setFieldsXfdfReader(XfdfReader $xfdf) {
        $fd = $xfdf->getFields();
        foreach (array_keys($arr) as &$f) {
            $v = $xfdf->getFieldValue($f);
            if ($v != NULL)
                setField($f, $v);
        }
    }

    public function setField()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return setField2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                return setField3args($arg1, $arg2, $arg3);
                break;
            }
        }
    }

    /** Sets the field value.
    * @param name the fully qualified field name
    * @param value the field value
    * @throws IOException on error
    * @throws DocumentException on error
    * @return <CODE>true</CODE> if the field was found and changed,
    * <CODE>false</CODE> otherwise
    */
    private function setField2args($name, $value) {
        return setField3args($name, $value, $value);
    }

    /** Sets the field value and the display string. The display string
    * is used to build the appearance in the cases where the value
    * is modified by Acrobat with JavaScript and the algorithm is
    * known.
    * @param name the fully qualified field name
    * @param value the field value
    * @param display the string that is used for the appearance
    * @return <CODE>true</CODE> if the field was found and changed,
    * <CODE>false</CODE> otherwise
    * @throws IOException on error
    * @throws DocumentException on error
    */
    private function setField3args($name, $value, $display) {
        if (is_resource($value) == FALSE)
            $value = itextphp_newString($value, strlen($value),2);

        if ($writer == NULL)
            throw new DocumentException("This AcroFields instance is read-only.");
        $item = $fields[$name];
        if ($item == NULL)
            return FALSE;
        $type = PdfReader::getPdfObject($item->merged[0]->get(PdfName::$FT));
        if (PdfName::$TX->equals($type) == TRUE) {
            $maxLen = PdfReader::getPdfObject($item->merged[0]->get(PdfName::$MAXLEN));
            $len = 0;
            if ($maxLen != NULL)
                $len = $maxLen->intValue();
            if ($len > 0)
                $value = itextphp_string_substring($value, 0, min($len, itextphp_StringLength($value)));
        }
        if (PdfName::$TX->equals($type) == TRUE || PdfName::$CH->equals($type) == TRUE) {
            $v = new PdfString(itextphp_string_toPHPString($value), PdfObject::TEXT_UNICODE);
            for ($idx = 0; $idx < count($item->values$); ++$idx) {
                $item->values[$idx]->put(PdfName::$V, $v);
                markUsed($item->values[$idx]);
                $merged = $item->merged[$idx];
                $merged->put(PdfName::$V, $v);
                $widget = $item->widgets[$idx];
                if ($generateAppearances == TRUE) {
                    $app = getAppearance($merged, $display, $name);
                    if (PdfName::$CH->equals($type) == TRUE) {
                        $n = new PdfNumber($topFirst);
                        $widget->put(PdfName::$TI, $n);
                        $merged->put(PdfName::$TI, $n);
                    }
                    $appDic = PdfReader::getPdfObject($widget->get(PdfName::$AP));
                    if ($appDic == NULL) {
                        $appDic = new PdfDictionary();
                        $widget->put(PdfName::$AP, $appDic);
                        $merged->put(PdfName::$AP, $appDic);
                    }
                    $appDic->put(PdfName::$N, $app->getIndirectReference());
                }
                else {
                    $widget->remove(PdfName::$AP);
                    $merged->remove(PdfName::$AP);
                }
                markUsed($widget);
            }
            return TRUE;
        }
        else if (PdfName::$BTN->equals($type) == TRUE) {
            $ff = PdfReader::getPdfObject($item->merged[0]->get(PdfName::$FF));
            $flags = 0;
            if ($ff != NULL)
                $flags = $ff->intValue();
            if (($flags & PdfFormField::FF_PUSHBUTTON) != 0)
                return TRUE;
            $v = new PdfName(itextphp_string_toPHPString($value));
            if (($flags & PdfFormField::FF_RADIO) == 0) {
                for ($idx = 0; $idx < count($item->values); ++$idx) {
                    $item->values[$idx]->put(PdfName::$V, $v);
                    markUsed($item->values[$idx]);
                    $merged = $item->merged[$idx];
                    $merged->put(PdfName::$V, $v);
                    $merged->put(PdfName::$AS, $v);
                    $widget = $item->widgets[$idx];
                    $widget->put(PdfName::$AS, $v);
                    markUsed($widget);
                }
            }
            else {
                $lopt = array();
                $opts = PdfReader::getPdfObject($item->values[0]->get(PdfName::$OPT));
                if ($opts != NULL && $opts->isArray() == TRUE) {
                    $list = $opts->getArrayList();
                    for ($k = 0; $k < count($list); ++$k) {
                        $vv = PdfReader::$getPdfObject($list[$k]);
                        if ($vv != NULL && $vv->isString() == TRUE)
                            array_add($lopt, $vv->toUnicodeString());
                        else
                            $lopt->add(NULL);
                    }
                }
                $vidx = array_search($value, $lopt);
                $valt = NULL;
                $vt = NULL;
                if ($vidx >= 0) {
                    $vt = $valt = new PdfName((string)$vidx);
                }
                else
                    $vt = $v;
                for ($idx = 0; $idx < count($item->values); ++$idx) {
                    $merged = $item->merged[$idx];
                    $widget = $item->widgets[$idx];
                    markUsed($item->values[$idx]);
                    if ($valt != NULL) {
                        $ps = new PdfString($value, PdfObject::TEXT_UNICODE);
                        $item->values[$idx]->put(PdfName::$V, $ps);
                        $merged->put(PdfName::$V, $ps);
                    }
                    else {
                        $item->values[$idx]->put(PdfName::$V, $v);
                        $merged->put(PdfName::$V, $v);
                    }
                    markUsed($widget);
                    if (isInAP($widget,  $vt) == TRUE) {
                        $merged->put(PdfName::$AS, $vt);
                        $widget->put(PdfName::$AS, $vt);
                    }
                    else {
                        $merged->put(PdfName::$AS, PdfName::$Off);
                        $widget->put(PdfName::$AS, PdfName::$Off);
                    }
                }
            }
            return TRUE;
        }
        return FALSE;
    }

    function isInAP(PdfDictionary $dic, PdfName $check) {
        $appDic = PdfReader::getPdfObject($dic->get(PdfName::$AP));
        if ($appDic == NULL)
            return FALSE;
        $NDic = PdfReader::getPdfObject($appDic->get(PdfName::$N));
        return ($NDic != NULL && $NDic->get($check) != NULL);
    }

    /** Gets all the fields. The fields are keyed by the fully qualified field name and
    * the value is an instance of <CODE>AcroFields.Item</CODE>.
    * @return all the fields
    */
    public function getFields() {
        return $fields;
    }

    /**
    * Gets the field structure.
    * @param name the name of the field
    * @return the field structure or <CODE>null</CODE> if the field
    * does not exist
    */
    public function getFieldItem($name) {
        return $fields[$name];
    }

    /**
    * Gets the field box positions in the document. The return is an array of <CODE>float</CODE>
    * multiple of 5. For each of this groups the values are: [page, llx, lly, urx,
    * ury].
    * @param name the field name
    * @return the positions or <CODE>null</CODE> if field does not exist
    */
    public function getFieldPositions($name) {
        $item = $fields[$name];
        if ($item == NULL)
            return NULL;
        $ret = array();
        $ptr = 0;
        for ($k = 0; $k < count($item->page); ++$k) {
            try {
                $wd = $item->widgets[$k];
                $rect = $wd->get(PdfName::$RECT);
                if ($rect == NULL)
                    continue;
                $r = PdfReader::getNormalizedRectangle($rect);
                $ret[$ptr] = (float)((integer)$item->page[$k]);
                ++$ptr;
                $ret[$ptr++] = $r->left();
                $ret[$ptr++] = $r->bottom();
                $ret[$ptr++] = $r->right();
                $ret[$ptr++] = $r->top();
            }
            catch (Exception $e) {
                // empty on purpose
            }
        }
        if ($ptr < count($ret)) {
            $ret2 = array();
            for ($k = 0; $k < $ptr; $k++)
            {
                $ret2[$k] = $ret[$k];
            }
            return $ret2;
        }
        return $ret;
    }

    private function removeRefFromArray(PdfArray $array, PdfObject $refo) {
        $ar = $array->getArrayList();
        if ($refo == NULL || $refo->isIndirect() == FALSE)
            return count($ar);
        $ref = $refo;
        for ($j = 0; $j < count($ar); ++$j) {
            $obj = $ar[$j];
            if ($obj->isIndirect() == FALSE)
                continue;
            if ($obj->getNumber() == $ref->getNumber())
                unset($ar[$j--]);
        }
        return count($ar);
    }


    /**
    * Removes all the fields from <CODE>page</CODE>.
    * @param page the page to remove the fields from
    * @return <CODE>true</CODE> if any field was removed, <CODE>false otherwise</CODE>
    */
    public function removeFieldsFromPage($page) {
        if ($page < 1)
            return FALSE;
        $names = array();
        $names = array_keys($fields);
        $found = FALSE;
        for ($k = 0; $k < count($names); ++$k) {
            $fr = removeField($names[$k], $page);
            $found = ($found || $fr);
        }
        return $found;
    }

    public function removeField()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                return removeField1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return removeField2args($arg1, $arg2);
                break;
            }
        }
    }


    /**
    * Removes a field from the document. If page equals -1 all the fields with this
    * <CODE>name</CODE> are removed from the document otherwise only the fields in
    * that particular page are removed.
    * @param name the field name
    * @param page the page to remove the field from or -1 to remove it from all the pages
    * @return <CODE>true</CODE> if the field exists, <CODE>false otherwise</CODE>
    */
    private function removeField2args($name, $page) {
        $item = $fields[$name];
        if ($item == NULL)
            return FALSE;
        $acroForm = PdfReader::getPdfObject($reader->getCatalog()->get(PdfName::$ACROFORM), $reader->getCatalog());
        if ($acroForm == NULL)
            return FALSE;
        $arrayf = PdfReader::getPdfObject($acroForm->get(PdfName::$FIELDS), $acroForm);
        if ($arrayf == NULL)
            return FALSE;
        for ($k = 0; $k < count($item->widget_refs); ++$k) {
            $pageV = (integer)$item->page[$k];
            if ($page != -1 && $page != $pageV)
                continue;
            $ref = $item->widget_refs[$k];
            $wd = PdfReader::getPdfObject($ref);
            $pageDic = $reader->getPageN($pageV);
            $annots = PdfReader::getPdfObject($pageDic->get(PdfName::$ANNOTS), $pageDic);
            if ($annots != NULL) {
                if (removeRefFromArray($annots, $ref) == 0) {
                    $pageDic->remove(PdfName::$ANNOTS);
                    markUsed($pageDic);
                }
                else
                    markUsed($annots);
            }
            PdfReader::killIndirect($ref);
            $kid = $ref;
            while (($ref = $wd->get(PdfName::$PARENT)) != $null) {
                $wd = PdfReader::getPdfObject($ref);
                $kids = PdfReader::getPdfObject($wd->get(PdfName::$KIDS));
                if (removeRefFromArray($kids, $kid) != 0)
                    break;
                $kid = $ref;
                PdfReader::killIndirect($ref);
            }
            if ($ref == NULL) {
                removeRefFromArray($arrayf, $kid);
                markUsed($arrayf);
            }
            if ($page != -1) {
                unset($item->merged[$k]);
                unset($item->page[$k]);
                unset($item->values[$k]);
                unset($item->widget_refs[$k]);
                unset($item->widgets[$k]);
                --$k;
            }
        }
        if ($page == -1 || count($item->merged) == 0)
            unset($fields[$name]);
        return TRUE;
    }

    /**
    * Removes a field from the document.
    * @param name the field name
    * @return <CODE>true</CODE> if the field exists, <CODE>false otherwise</CODE>
    */
    private function removeField1arg($name) {
        return removeField2args($name, -1);
    }

    /** Gets the property generateAppearances.
    * @return the property generateAppearances
    */
    public function isGenerateAppearances() {
        return $this->generateAppearances;
    }

    /** Sets the option to generate appearances. Not generating apperances
    * will speed-up form filling but the results can be
    * unexpected in Acrobat. Don't use it unless your environment is well
    * controlled. The default is <CODE>true</CODE>.
    * @param generateAppearances the option to generate appearances
    */
    public function setGenerateAppearances($generateAppearances) {
        $this->generateAppearances = $generateAppearances;
        $top = PdfReader::getPdfObject($reader->getCatalog()->get(PdfName::$ACROFORM));
        if ($generateAppearances == TRUE)
            $top->remove(PdfName::$NEEDAPPEARANCES);
        else
            $top->put(PdfName::$NEEDAPPEARANCES, PdfBoolean::$PDFTRUE);
    }

    /**
    * Gets the field names that have signatures and are signed.
    * @return the field names that have signatures and are signed
    */
    public function getSignatureNames() {
        if ($sigNames != NULL)
            return array_keys($sigNames);
        $sigNames = array();
        $sorter = array();
        foreach ($fields as &$key => &$item) {
            $merged = $item->merged[0];
            if (PdfName::$SIG->equals($merged->get(PdfName::$FT)) == FALSE)
                continue;
            $vo = PdfReader::getPdfObject($merged->get(PdfName::$V));
            if ($vo == NULL || $vo->type() != PdfObject::DICTIONARY)
                continue;
            $v = $vo;
            $contents = $v->get(PdfName::$CONTENTS);
            if ($contents == NULL || $contents->type() != PdfObject::STRING)
                continue;
            $ro = $v->get(PdfName::$BYTERANGE);
            if ($ro == NULL || $ro->type() != PdfObject::ARRAY)
                continue;
            $ra = $r->getArrayList();
            if (count($ra) < 2)
                continue;
            $length = (integer)$ra[count($ra) - 1] + (integer)$ra[count($ra) - 2];
            array_push($sorter, array($key, array($length, 0)));
        }
        usort($sorter. array("SorterComparator", "compare"));
        if (count($sorter) > 0) {
            if ((($sorter[count($sorter) - 1])[1])[0] == $reader->getFileLength())
                $totalRevisions = count($sorter);
            else
                $totalRevisions = count($sorter) + 1;
            for ($k = 0; $k < count($sorter); ++$k) {
                $objs = $sorter[$k];
                $name = (string)$objs[0];
                $p = $objs[1];
                $p[1] = $k + 1;
                $sigNames[$name] = $p;
            }
        }
        return array_keys($sigNames);
    }

    /**
    * Gets the field names that have blank signatures.
    * @return the field names that have blank signatures
    */
    public function getBlankSignatureNames() {
        getSignatureNames();
        $sigs = array();
        foreach ($fields as &$key => &$item) {
            $merged = $item->merged[0];
            if (PdfName::$SIG->equals($merged->get(PdfName::$FT)) == FALSE)
                continue;
            if (array_key_exists($key, $sigNames) == TRUE)
                continue;
            array_push($sigs, $key);
        }
        return $sigs;
    }

    /**
    * Gets the signature dictionary, the one keyed by /V.
    * @param name the field name
    * @return the signature dictionary keyed by /V or <CODE>null</CODE> if the field is not
    * a signature
    */
    public function getSignatureDictionary($name) {
        getSignatureNames();
        if (array_key_exists($name, $sigNames) == FALSE)
            return NULL;
        $item = $fields[$name];
        $merged = $item->merged[0];
        $vo = PdfReader::getPdfObject($merged->get(PdfName::$V));
        return PdfReader::getPdfObject($merged->get(PdfName::$V));
    }


    /**
    * Checks is the signature covers the entire document or just part of it.
    * @param name the signature field name
    * @return <CODE>true</CODE> if the signature covers the entire document,
    * <CODE>false</CODE> otherwise
    */
    public function signatureCoversWholeDocument($name) {
        getSignatureNames();
        if (array_key_exists($name, $sigNames) == FALSE)
            return FALSE;
        return ($sigNames[$name])[0] == $reader->getFileLength();
    }

    public function verifySignature()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                return verifySignature1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                return verifySignature2args($arg1, $arg2);
                break;
            }

        }
    }

    /**
    * Verifies a signature. An example usage is:
    * <p>
    * <pre>
    * KeyStore kall = PdfPKCS7.loadCacertsKeyStore();
    * PdfReader reader = new PdfReader("my_signed_doc.pdf");
    * AcroFields af = reader.getAcroFields();
    * ArrayList names = af.getSignatureNames();
    * for (int k = 0; k &lt; names.size(); ++k) {
    *    String name = (String)names.get(k);
    *    System.out.println("Signature name: " + name);
    *    System.out.println("Signature covers whole document: " + af.signatureCoversWholeDocument(name));
    *    PdfPKCS7 pk = af.verifySignature(name);
    *    Calendar cal = pk.getSignDate();
    *    Certificate pkc[] = pk.getCertificates();
    *    System.out.println("Subject: " + PdfPKCS7.getSubjectFields(pk.getSigningCertificate()));
    *    System.out.println("Document modified: " + !pk.verify());
    *    Object fails[] = PdfPKCS7.verifyCertificates(pkc, kall, null, cal);
    *    if (fails == null)
    *        System.out.println("Certificates verified against the KeyStore");
    *    else
    *        System.out.println("Certificate failed: " + fails[1]);
    * }
    * </pre>
    * @param name the signature field name
    * @return a <CODE>PdfPKCS7</CODE> class to continue the verification
    */
    private function verifySignature($name) {
        return verifySignature2args($name, NULL);
    }

    /**
    * Verifies a signature. An example usage is:
    * <p>
    * <pre>
    * KeyStore kall = PdfPKCS7.loadCacertsKeyStore();
    * PdfReader reader = new PdfReader("my_signed_doc.pdf");
    * AcroFields af = reader.getAcroFields();
    * ArrayList names = af.getSignatureNames();
    * for (int k = 0; k &lt; names.size(); ++k) {
    *    String name = (String)names.get(k);
    *    System.out.println("Signature name: " + name);
    *    System.out.println("Signature covers whole document: " + af.signatureCoversWholeDocument(name));
    *    PdfPKCS7 pk = af.verifySignature(name);
    *    Calendar cal = pk.getSignDate();
    *    Certificate pkc[] = pk.getCertificates();
    *    System.out.println("Subject: " + PdfPKCS7.getSubjectFields(pk.getSigningCertificate()));
    *    System.out.println("Document modified: " + !pk.verify());
    *    Object fails[] = PdfPKCS7.verifyCertificates(pkc, kall, null, cal);
    *    if (fails == null)
    *        System.out.println("Certificates verified against the KeyStore");
    *    else
    *        System.out.println("Certificate failed: " + fails[1]);
    * }
    * </pre>
    * @param name the signature field name
    * @param provider the provider or <code>null</code> for the default provider
    * @return a <CODE>PdfPKCS7</CODE> class to continue the verification
    */
    private function verifySignature2args($name, $provider) {
        $v = getSignatureDictionary($name);
        if ($v == NULL)
            return NULL;
        try {
            $sub = PdfReader::getPdfObject($v->get(PdfName::$SUBFILTER));
            $contents = (PdfReader::getPdfObject($v->get(PdfName::$CONTENTS));
            $pk = NULL;
            if ($sub->equals(PdfName::$ADBE_X509_RSA_SHA1) == TRUE) {
                $cert = PdfReader::getPdfObject($v->get(PdfName::$CERT));
                $pk = new PdfPKCS7($contents->getOriginalBytes(), $cert->getBytes(), $provider);
            }
            else
                $pk = new PdfPKCS7($contents->getOriginalBytes(), $provider);
            updateByteRange($pk, $v);
            $str = PdfReader::getPdfObject($v->get(PdfName::$M));
            if ($str != NULL)
                $pk->setSignDate(PdfDate::decode($str->toString()));
            $str = PdfReader::getPdfObject($v->get(PdfName::$NAME));
            if ($str != NULL)
                $pk->setSignName($str->toUnicodeString());
            str = (PdfString)PdfReader.getPdfObject(v.get(PdfName.REASON));
            if ($str != NULL)
                $pk->setReason($str->toUnicodeString());
            $str = PdfReader::getPdfObject($v->get(PdfName::$LOCATION));
            if ($str != NULL)
                $pk->setLocation($str->toUnicodeString());
            return $pk;
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }

    private function updateByteRange(PdfPKCS7 $pkcs7, PdfDictionary $v) {
        $b = PdfReader::getPdfObject($v->get(PdfName::$BYTERANGE));
        $rf = $reader->getSafeFile();
        try {
            $rf->reOpen();
            $buf = itextphp_bytes_create(8192);
            $ar = $b->getArrayList();
            for ($k = 0; $k < count($ar); ++$k) {
                $start = (integer)$ar[$k];
                $length = (integer)$ar[++$k];
                $rf->seek($start);
                while ($length > 0) {
                    $rd = $rf->read($buf, 0, min($length, itextphp_bytes_getSize($buf));
                    if ($rd <= 0)
                        break;
                    $length -= $rd;
                    $pkcs7->update($buf, 0, $rd);
                }
            }
        }
        catch (Exception $e) {
            try{$rf->close(); return;}catch(Exception $e){}
            throw new Exception($e);
        }

        try{$rf->close();}catch(Exception $e){}

    }

    private function markUsed(PdfObject $obj) {
        if ($append == FALSE)
            return;
        $writer->markUsed($obj);
    }

    /**
    * Gets the total number of revisions this document has.
    * @return the total number of revisions
    */
    public function getTotalRevisions() {
        getSignatureNames();
        return $this->totalRevisions;
    }

    /**
    * Gets this <CODE>field</CODE> revision.
    * @param field the signature field name
    * @return the revision or zero if it's not a signature field
    */
    public function getRevision($field) {
        getSignatureNames();
        if (aray_key_exists($field, $sigNames) == FALSE)
            return 0;
        return ($sigNames[$field])[1];
    }


    /**
    * Extracts a revision from the document.
    * @param field the signature field name
    * @return an <CODE>InputStream</CODE> covering the revision. Returns <CODE>null</CODE> if
    * it's not a signature field
    * @throws IOException on error
    */
    public function extractRevision($field) {
        getSignatureNames();
        $length = ($sigNames[$field])[0];
        $raf = $reader->getSafeFile();
        $raf->reOpen();
        $raf->seek(0);
        return new RevisionStream($raf, $length);
    }

    /**
    * Gets the appearances cache.
    * @return the appearances cache
    */
    public function getFieldCache() {
        return $this->fieldCache;
    }

    /**
    * Sets a cache for field appearances. Parsing the existing PDF to
    * create a new TextField is time expensive. For those tasks that repeatedly
    * fill the same PDF with different field values the use of the cache has dramatic
    * speed advantages. An example usage:
    * <p>
    * <pre>
    * String pdfFile = ...;// the pdf file used as template
    * ArrayList xfdfFiles = ...;// the xfdf file names
    * ArrayList pdfOutFiles = ...;// the output file names, one for each element in xpdfFiles
    * HashMap cache = new HashMap();// the appearances cache
    * PdfReader originalReader = new PdfReader(pdfFile);
    * for (int k = 0; k &lt; xfdfFiles.size(); ++k) {
    *    PdfReader reader = new PdfReader(originalReader);
    *    XfdfReader xfdf = new XfdfReader((String)xfdfFiles.get(k));
    *    PdfStamper stp = new PdfStamper(reader, new FileOutputStream((String)pdfOutFiles.get(k)));
    *    AcroFields af = stp.getAcroFields();
    *    af.setFieldCache(cache);
    *    af.setFields(xfdf);
    *    stp.close();
    * }
    * </pre>
    * @param fieldCache an HasMap that will carry the cached appearances
    */
    public function setFieldCache(array $fieldCache) {
        $this->fieldCache = $fieldCache;
    }

    private static $stdFieldFontNames = array();

    /**
    * Holds value of property totalRevisions.
    */
    private $totalRevisions = 0;

    /**
    * Holds value of property fieldCache.
    */
    private $fieldCache = array();

    public static $initialized = FALSE;

    public static function initializeStatics()
    {
        if(AcroFields::$initialized == FALSE)
        {
            AcroFields::$stdFieldFontNames["CoBO"] = array("Courier-BoldOblique");
            AcroFields::$stdFieldFontNames["CoBo"] = array("Courier-Bold");
            AcroFields::$stdFieldFontNames["CoOb"] = array("Courier-Oblique");
            AcroFields::$stdFieldFontNames["Cour"] = array("Courier");
            AcroFields::$stdFieldFontNames["HeBO"] = array("Helvetica-BoldOblique");
            AcroFields::$stdFieldFontNames["HeBo"] = array("Helvetica-Bold");
            AcroFields::$stdFieldFontNames["HeOb"] = array("Helvetica-Oblique");
            AcroFields::$stdFieldFontNames["Helv"] = array("Helvetica");
            AcroFields::$stdFieldFontNames["Symb"] = array("Symbol");
            AcroFields::$stdFieldFontNames["TiBI"] = array("Times-BoldItalic");
            AcroFields::$stdFieldFontNames["TiBo"] = array("Times-Bold");
            AcroFields::$stdFieldFontNames["TiIt"] = array("Times-Italic");
            AcroFields::$stdFieldFontNames["TiRo"] = array("Times-Roman");
            AcroFields::$stdFieldFontNames["ZaDb"] = array("ZapfDingbats");
            AcroFields::$stdFieldFontNames["HySm"] = array("HYSMyeongJo-Medium", "UniKS-UCS2-H");
            AcroFields::$stdFieldFontNames["HyGo"] = array("HYGoThic-Medium", "UniKS-UCS2-H");
            AcroFields::$stdFieldFontNames["KaGo"] = array("HeiseiKakuGo-W5", "UniKS-UCS2-H");
            AcroFields::$stdFieldFontNames["KaMi"] = array("HeiseiMin-W3", "UniJIS-UCS2-H");
            AcroFields::$stdFieldFontNames["MHei"] = array("MHei-Medium", "UniCNS-UCS2-H");
            AcroFields::$stdFieldFontNames["MSun"] = array("MSung-Light", "UniCNS-UCS2-H");
            AcroFields::$stdFieldFontNames["STSo"] = array("STSong-Light", "UniGB-UCS2-H");
            AcroFields::$initialized = TRUE;
        }
    }


}


/** The field representations for retrieval and modification. */
class Item {
    /** An array of <CODE>PdfDictionary</CODE> where the value tag /V
    * is present.
    */
    public $values = array();
    /** An array of <CODE>PdfDictionary</CODE> with the widgets.
    */
    public $widgets = array();
    /** An array of <CODE>PdfDictionary</CODE> with the widget re(PdfArray)ferences.
    */
    public $widget_refs = array();
    /** An array of <CODE>PdfDictionary</CODE> with all the field
    * and widget tags merged.
    */
    public $merged = array();
    /** An array of <CODE>Integer</CODE> with the page numbers where
    * the widgets are displayed.
    */
    public $page = array();
    /** An array of <CODE>Integer</CODE> with the tab order of the field in the page.
    */
    public $tabOrder = array();
}

class InstHit {
    $hits = NULL;
    public function __construct(array $inst) {
        if ($inst == null)
            return;
        $hits = array();
        for ($k = 0; $k < count($inst); ++$k)
            $hits[$inst[$k]] = 1;
    }

    public function isHit($n) {
        if ($hits == NULL)
            return TRUE;
        return array_key_exists($n, $hits);
    }
}

class RevisionStream extends InputStream 
{
    private $b = itextphp_bytes_create(1);
    private $raf = NULL;
    private $length = 0;
    private $rangePosition = 0;
    private $closed = FALSE;

    private function __construct(RandomAccessFileOrArray $raf, $length) {
        $this->raf = $raf;
        $this->length = $length;
    }

    private function read0args() {
        $n = read1arg($b);
        if ($n != 1)
            return -1;
        return itextphp_bytes_getIntValue($b, 0) & 0xff;
    }

    private function read3args($b, $off, $len)  {
        if ($b == NULL) {
            throw new NullPointerException();
        } else if (($off < 0) || ($off > itextphp_bytes_getSize($b) || ($len < 0) ||
            (($off + $len) > itextphp_bytes_getSize($b)) || (($off + $len) < 0)) {
            throw new IndexOutOfBoundsException();
        } else if ($len == 0) {
            return 0;
        }
        if ($rangePosition >= $length) {
            close();
        return -1;
        }
        $elen = $min($len, $length - $rangePosition);
        $raf->readFully($b, $off, $elen);
        $rangePosition += $elen;
        return $elen;
    }

    public function close() {
        if ($closed == FALSE) {
            $raf->close();
            $closed = TRUE;
        }
    }
}

class SorterComparator
{
    public compare($o1, $o2) {
        $n1 = (($o1)[1])[0];
        $n2 = (($o2)[1])[0];
        return $n1 - $n2;
    }
}



?>