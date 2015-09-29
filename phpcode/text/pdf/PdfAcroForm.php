<?PHP
/*
 * $Id: PdfAcroForm.php,v 1.2 2005/12/22 21:51:25 mstaylor Exp $
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
require_once("../../util/Helpers.php");
require_once("PdfDictionary.php");
require_once("PdfWriter.php");
require_once("PdfArray.php");
require_once("PdfIndirectReference.php");
require_once("PdfName.php");
require_once("PdfNumber.php");
require_once("PdfString.php");
require_once("PdfTemplate.php");
require_once("PdfFormField.php");
require_once("PdfAction.php");
require_once("BaseFont.php");
require_once("PdfContentByte.php");
require_once("PdfAppearance.php");
require_once("PdfAnnotation.php");
require_once("PdfBorderDictionary.php");
require_once("../../awt/Color.php");



/**
* Each PDF document can contain maximum 1 AcroForm.
*/
class PdfAcroForm extends PdfDictionary 
{
    private $writer = NULL;//PdfWriter


    /** This is a map containing FieldTemplates. */
    private $fieldTemplates = array();

    /** This is an array containing DocumentFields. */
    private $documentFields = new PdfArray();

    /** This is an array containing the calculationorder of the fields. */
    private $calculationOrder = new PdfArray();

    /** Contains the signature flags. */
    private $sigFlags = 0;


    /** Creates new PdfAcroForm 
    * @param writer*/
    public function __construct(PdfWriter $writer) {
        parent::__construct();
        $this->writer = $writer;
    }

    /**
    * Adds fieldTemplates.
    * @param ft
    */

    protected function addFieldTemplates(array $ft) {
        array_merge($fieldTemplates, $ft);
    }

    /**
    * Adds documentFields.
    * @param ref
    */

    protected function addDocumentField(PdfIndirectReference $ref) {
        $documentFields->add($ref);
    }


    /**
    * Checks if the Acroform is valid
    * @return true if the Acroform is valid
    */

    protected function isValid() {
        if ($documentFields->size() == 0) return FALSE;
        put(PdfName::$FIELDS, $documentFields);
        if ($sigFlags != 0)
            put(PdfName::$SIGFLAGS, new PdfNumber($sigFlags));
        if ($calculationOrder->size() > 0)
            put(PdfName::$CO, $calculationOrder);
        if (count($fieldTemplates) == 0) return TRUE;
        $dic = new PdfDictionary();
        foreach (array_keys($fieldTemplates) as &$template) {
            PdfFormField::mergeResources($dic, $template->getResources());
        }
        put(PdfName::$DR, $dic);
        $fonts = $dic->get(PdfName::$FONT);
        if ($fonts != NULL) {
            put(PdfName::$DA, new PdfString("/Helv 0 Tf 0 g "));
            $writer->eliminateFontSubset($fonts);
        }
        return TRUE;
    }

    /**
    * Adds an object to the calculationOrder.
    * @param formField
    */

    public function addCalculationOrder(PdfFormField $formField) {
        $calculationOrder->add($formField->getIndirectReference());
    }

    /**
    * Sets the signature flags.
    * @param f
    */

    public function setSigFlags($f) {
        $sigFlags |= $f;
    }

    /**
    * Adds a formfield to the AcroForm.
    * @param formField
    */

    public function addFormField(PdfFormField $formField) {
        $writer->addAnnotation($formField);
    }

    /**
    * @param name
    * @param caption
    * @param value
    * @param url
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    public function addHtmlPostButton($name, $caption, $value, $url, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $action = PdfAction::createSubmitForm($url, NULL, PdfAction::SUBMIT_HTML_FORMAT);
        $button = new PdfFormField($writer, $llx, $lly, $urx, $ury, $action);
        setButtonParams($button, PdfFormField::FF_PUSHBUTTON, $name, $value);
        drawButton($button, $caption, $font, $fontSize, $llx, $lly, $urx, $ury);
        addFormField($button);
	return $button;
    }

    /**
    * @param name
    * @param caption
    * @param value
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    public function addResetButton($name, $caption, $value, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $action = PdfAction::createResetForm(NULL, 0);
        $button = new PdfFormField($writer, $llx, $lly, $urx, $ury, $action);
        setButtonParams($button, PdfFormField::FF_PUSHBUTTON, $name, $value);
        drawButton($button, $caption, $font, $fontSize, $llx, $lly, $urx, $ury);
        addFormField($button);
        return $button;
    }

    /**
    * @param name
    * @param value
    * @param url
    * @param appearance
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    public function addMap($name, $value, $url, PdfContentByte $appearance, $llx, $lly, $urx, $ury) {
        $action = PdfAction::createSubmitForm($url, NULL, PdfAction::SUBMIT_HTML_FORMAT | PdfAction::SUBMIT_COORDINATES);
        $button = new PdfFormField($writer, $llx, $lly, $urx, $ury, $action);
        setButtonParams($button, PdfFormField::FF_PUSHBUTTON, $name, NULL);
        $cb = $writer->getDirectContent();
        $pa = $cb->createAppearance($urx - $llx, $ury - $lly);
        $pa->add($appearance);
        $button->setAppearance(PdfAnnotation::$APPEARANCE_NORMAL, $pa);
        addFormField($button);
        return $button;
    }

    /**
    * @param button
    * @param characteristics
    * @param name
    * @param value
    */
    public function setButtonParams(PdfFormField $button, $characteristics, $name, $value) {
        $button->setButton($characteristics);
        $button->setFlags(PdfAnnotation::FLAGS_PRINT);
        $button->setPage();
        $button->setFieldName($name);
        if ($value != NULL) $button->setValueAsString($value);
    }

    /**
    * @param button
    * @param caption
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function drawButton(PdfFormField $button, $caption, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $cb = $writer->getDirectContent();
        $pa = $cb->createAppearance($urx - $llx, $ury - $lly);
        $pa->drawButton(0.0, 0.0, $urx - $llx, $ury - $lly, $caption, $font, $fontSize);
        $button->setAppearance(PdfAnnotation::$APPEARANCE_NORMAL, $pa);
    }

    /**
    * @param name
    * @param value
    * @return a PdfFormField
    */
    public function addHiddenField($name, $value) {
        $hidden = PdfFormField::createEmpty($writer);
        $hidden->setFieldName($name);
        $hidden->setValueAsName($value);
        addFormField($hidden);
        return $hidden;
    }

    /**
    * @param name
    * @param text
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    public function addSingleLineTextField($name, $text, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $field = PdfFormField::createTextField($writer, PdfFormField::SINGLELINE, PdfFormField::PLAINTEXT, 0);
        setTextFieldParams($field, $text, $name, $llx, $lly, $urx, $ury);
        drawSingleLineOfText($field, $text, $font, $fontSize, $llx, $lly, $urx, $ury);
        addFormField($field);
        return $field;
    }

    /**
    * @param name
    * @param text
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    public function addMultiLineTextField($name, $text, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $field = PdfFormField::createTextField($writer, PdfFormField::MULTILINE, PdfFormField::PLAINTEXT, 0);
        setTextFieldParams($field, $text, $name, $llx, $lly, $urx, $ury);
        drawMultiLineOfText($field, $text, $font, $fontSize, $llx, $lly, $urx, $ury);
        addFormField($field);
        return $field;
    }

    /**
    * @param name
    * @param text
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return PdfFormField
    */
    public function addSingleLinePasswordField($name, $text, BaseFont $font, $fontSize, $llx, $lly, $urx, float ury) {
        $field = PdfFormField::createTextField($writer, PdfFormField::SINGLELINE, PdfFormField::PASSWORD, 0);
        setTextFieldParams($field, $text, $name, $llx, $lly, $urx, $ury);
        drawSingleLineOfText($field, $text, $font, $fontSize, $llx, $lly, $urx, $ury);
        addFormField($field);
        return $field;
    }

    /**
    * @param field
    * @param text
    * @param name
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function setTextFieldParams(PdfFormField $field, $text, $name, $llx, $lly, $urx, $ury) {
        $field->setWidget(new Rectangle($llx, $lly, $urx, $ury), PdfAnnotation::$HIGHLIGHT_INVERT);
        $field->setValueAsString($text);
        $field->setDefaultValueAsString($text);
        $field->setFieldName($name);
        $field->setFlags(PdfAnnotation::FLAGS_PRINT);
        $field->setPage();
    }


    /**
    * @param field
    * @param text
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function drawSingleLineOfText(PdfFormField $field, $text, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $cb = $writer->getDirectContent();
        $tp = $cb->createAppearance($urx - $llx, $ury - $lly);
        $tp2 = $tp->getDuplicate();
        $tp2->setFontAndSize($font, $fontSize);
        $tp2->resetRGBColorFill();
        $field->setDefaultAppearanceString($tp2);
        $tp->drawTextField(0.0, 0.0, $urx - $llx, $ury - $lly);
        $tp->beginVariableText();
        $tp->saveState();
        $tp->rectangle(3.0, 3.0, $urx - $llx - 6.0, $ury - $lly - 6.0);
        $tp->clip();
        $tp->newPath();
        $tp->beginText();
        $tp->setFontAndSize($font, $fontSize);
        $tp->resetRGBColorFill();
        $tp->setTextMatrix(4, ($ury - $lly) / 2 - ($fontSize * 0.3));
        $tp->showText($text);
        $tp->endText();
        $tp->restoreState();
        $tp->endVariableText();
        $field->setAppearance(PdfAnnotation::$APPEARANCE_NORMAL, $tp);
    }

    /**
    * @param field
    * @param text
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function drawMultiLineOfText(PdfFormField $field, $text, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $cb = $writer->getDirectContent();
        $tp = $cb->createAppearance($urx - $llx, $ury - $lly);
        $tp2 = $tp->getDuplicate();
        $tp2->setFontAndSize($font, $fontSize);
        $tp2->resetRGBColorFill();
        $field->setDefaultAppearanceString($tp2);
        $tp->drawTextField(0.0, 0.0, $urx - $llx, $ury - $lly);
        $tp->beginVariableText();
        $tp->saveState();
        $tp->rectangle(3.0, 3.0, $urx - $llx - 6.0, $ury - $lly - 6.0);
        $tp->clip();
        $tp->newPath();
        $tp->beginText();
        $tp->setFontAndSize($font, $fontSize);
        $tp->resetRGBColorFill();
        $tp->setTextMatrix(4, 5);
        $tokenizer = strtok($text, "\n");
        $yPos = $ury - $lly;
        while ($tokenizer != false) {
            $yPos -= $fontSize * 1.2;
            $tp->showTextAligned(PdfContentByte::ALIGN_LEFT, $tokenizer, 3, $yPos, 0);
            $tokenizer = strtok("\n");
        }
        $tp->endText();
        $tp->restoreState();
        $tp->endVariableText();
        $field->setAppearance(PdfAnnotation::$APPEARANCE_NORMAL, $tp);
    }


    /**
    * @param name
    * @param value
    * @param status
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    public function addCheckBox($name, $value, $status, $llx, $lly, $urx, $ury) {
        $field = PdfFormField::createCheckBox($writer);
        setCheckBoxParams($field, $name, $value, $status, $llx, $lly, $urx, $ury);
        drawCheckBoxAppearences($field, $value, $llx, $lly, $urx, $ury);
        addFormField($field);
        return $field;
    }


    /**
    * @param field
    * @param name
    * @param value
    * @param status
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function setCheckBoxParams(PdfFormField $field, $name, $value, $status, $llx, $lly, $urx, $ury) {
        $field->setWidget(new Rectangle($llx, $lly, $urx, $ury), PdfAnnotation::$HIGHLIGHT_TOGGLE);
        $field->setFieldName($name);
        if ($status == TRUE) {
            $field->setValueAsName($value);
            $field->setAppearanceState($value);
        }
        else {
            $field->setValueAsName("Off");
            $field->setAppearanceState("Off");
        }
        $field->setFlags(PdfAnnotation::FLAGS_PRINT);
        $field->setPage();
        $field->setBorderStyle(new PdfBorderDictionary(1, PdfBorderDictionary::STYLE_SOLID));
    }

    /**
    * @param field
    * @param value
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function drawCheckBoxAppearences(PdfFormField $field, $value, $llx, $lly, $urx, $ury) {
        $font = NULL;//BaseFont
        try {
            $font = BaseFont::createFont(BaseFont::ZAPFDINGBATS, BaseFont::WINANSI, BaseFont::NOT_EMBEDDED);
        }
        catch(Exception $e) {
            throw new Exception($e);
        }
        $size = ($ury - $lly);
        $cb = $writer->getDirectContent();
        $tpOn = $cb->createAppearance($urx - $llx, $ury - $lly);
        $tp2 = $tpOn->getDuplicate();
        $tp2->setFontAndSize($font, $size);
        $tp2->resetRGBColorFill();
        $field->setDefaultAppearanceString($tp2);
        $tpOn->drawTextField(0.0, 0.0, $urx - $llx, $ury - $lly);
        $tpOn->saveState();
        $tpOn->resetRGBColorFill();
        $tpOn->beginText();
        $tpOn->setFontAndSize($font, $size);
        $tpOn->showTextAligned(PdfContentByte::ALIGN_CENTER, "4", ($urx - $llx) / 2, ($ury - $lly) / 2 - ($size * 0.3), 0);
        $tpOn->endText();
        $tpOn->restoreState();
        $field->setAppearance(PdfAnnotation::$APPEARANCE_NORMAL, $value, $tpOn);
        $tpOff = $cb->createAppearance($urx - $llx, $ury - $lly);
        $tpOff->drawTextField(0.0, 0.0, $urx - $llx, $ury - $lly);
        $field->setAppearance(PdfAnnotation::$APPEARANCE_NORMAL, "Off", $tpOff);
    }

    /**
    * @param name
    * @param defaultValue
    * @param noToggleToOff
    * @return a PdfFormField
    */
    public function getRadioGroup($name, $defaultValue, $noToggleToOff) {
        $radio = PdfFormField::createRadioButton($writer, $noToggleToOff);
        $radio->setFieldName($name);
        $radio->setValueAsName($defaultValue);
        return $radio;
    }

    /**
    * @param radiogroup
    */
    public function addRadioGroup(PdfFormField $radiogroup) {
        addFormField($radiogroup);
    }

    /**
    * @param radiogroup
    * @param value
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    public function addRadioButton(PdfFormField $radiogroup, $value, $llx, $lly, $urx, $ury) {
        $radio = PdfFormField::createEmpty($writer);
        $radio->setWidget(new Rectangle($llx, $lly, $urx, $ury), PdfAnnotation::$HIGHLIGHT_TOGGLE);
        $name = substr(($radiogroup->get(PdfName::$V))->toString(), 1);
        if (strcmp($name, $value) == 0) {
            $radio->setAppearanceState($value);
        }
        else {
            $radio->setAppearanceState("Off");
        }
        drawRadioAppearences($radio, $value, $llx, $lly, $urx, $ury);
        $radiogroup->addKid($radio);
        return $radio;
    }

    /**
    * @param field
    * @param value
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function drawRadioAppearences(PdfFormField $field, $value, $llx, $lly, $urx, $ury) {
        $cb = $writer->getDirectContent();
        $tpOn = $cb->createAppearance($urx - $llx, $ury - $lly);
        $tpOn->drawRadioField(0.0, 0.0, $urx - $llx, $ury - $lly, TRUE);
        $field->setAppearance(PdfAnnotation::$APPEARANCE_NORMAL, $value, $tpOn);
        $tpOff = $cb->createAppearance($urx - $llx, $ury - $lly);
        $tpOff->drawRadioField(0.0, 0.0, $urx - $llx, $ury - $lly, FALSE);
        $field->setAppearance(PdfAnnotation::$APPEARANCE_NORMAL, "Off", $tpOff);
    }


    public function addSelectList()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 9:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                $arg7 = func_get_arg(6);
                $arg8 = func_get_arg(7);
                $arg9 = func_get_arg(8);


                if (is_multi_array($arg2, 2) == 1)
                {
                    return addSelectList9ArgsMultiArray($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
                }
                else
                {
                    return addSelectList9Args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
                }
                break;
            }
        }
    }

    /**
    * @param name
    * @param options
    * @param defaultValue
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    private function addSelectList9Args($name, array $options, $defaultValue, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $choice = PdfFormField::createList($writer, $options, 0);
        setChoiceParams($choice, $name, $defaultValue, $llx, $lly, $urx, $ury);
        $text = "";
        for ($i = 0; $i < count($options); $i++) {
            $text . = $options[$i] . "\n";
        }
        drawMultiLineOfText($choice, $text, $font, $fontSize, $llx, $lly, $urx, $ury);
        addFormField($choice);
        return $choice;
    }

    /**
    * @param name
    * @param options
    * @param defaultValue
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    private function addSelectList9ArgsMultiArray($name, array $options, $defaultValue, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $choice = PdfFormField::createList($writer, $options, 0);
        setChoiceParams($choice, $name, $defaultValue, $llx, $lly, $urx, $ury);
        $text = "";
        for ($i = 0; $i < count($options); $i++) {
            $text .= $options[$i][1] . "\n";
        }
        drawMultiLineOfText($choice, $text, $font, $fontSize, $llx, $lly, $urx, $ury);
        addFormField($choice);
        return $choice;
    }

    public function addComboBox()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 10:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                $arg7 = func_get_arg(6);
                $arg8 = func_get_arg(7);
                $arg9 = func_get_arg(8);
                $arg10 = func_get_arg(9);

                if (is_multi_array($arg2, 2) == 1)
                {
                    return addComboBox10ArgsMultiArray($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9, $arg10);
                }
                else
                {
                    return addComboBox10Args($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9, $arg10);
                }
                break;
            }
        }
    }

    /**
    * @param name
    * @param options
    * @param defaultValue
    * @param editable
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    private function addComboBox10Args($name, array $options, $defaultValue, $editable, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $choice = PdfFormField::createCombo($writer, $editable, $options, 0);
        setChoiceParams($choice, $name, $defaultValue, $llx, $lly, $urx, $ury);
        if ($defaultValue == NULL) {
            $defaultValue = $options[0];
        }
        drawSingleLineOfText($choice, $defaultValue, $font, $fontSize, $llx, $lly, $urx, $ury);
        addFormField($choice);
        return $choice;
    }

    /**
    * @param name
    * @param options
    * @param defaultValue
    * @param editable
    * @param font
    * @param fontSize
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    private function addComboBox10ArgsMultiArray($name, array $options, $defaultValue, $editable, BaseFont $font, $fontSize, $llx, $lly, $urx, $ury) {
        $choice = PdfFormField::createCombo($writer, $editable, $options, 0);
        setChoiceParams($choice, $name, $defaultValue, $llx, $lly, $urx, $ury);
        $value = NULL;
        for ($i = 0; $i < count($options); $i++) {
            if (strcmp($options[$i][0], $defaultValue) == 0) {
                $value = $options[$i][1];
                break;
            }
        }
        if ($value == NULL) {
            $value = $options[0][1];
        }
        drawSingleLineOfText($choice, $value, $font, $fontSize, $llx, $lly, $urx, $ury);
        addFormField($choice);
        return $choice;
    }

    /**
    * @param field
    * @param name
    * @param defaultValue
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function setChoiceParams(PdfFormField $field, $name, $defaultValue, $llx, $lly, $urx, $ury) {
        $field->setWidget(new Rectangle($llx, $lly, $urx, $ury), PdfAnnotation::$HIGHLIGHT_INVERT);
        if ($defaultValue != NULL) {
            $field->setValueAsString($defaultValue);
            $field->setDefaultValueAsString($defaultValue);
        }
        $field->setFieldName($name);
        $field->setFlags(PdfAnnotation::FLAGS_PRINT);
        $field->setPage();
        $field->setBorderStyle(new PdfBorderDictionary(2, PdfBorderDictionary::STYLE_SOLID));
    }

    /**
    * @param name
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    * @return a PdfFormField
    */
    public function addSignature($name, $llx, $lly, $urx, $ury) {
        $signature = PdfFormField::createSignature($writer);
        setSignatureParams($signature, $name, $llx, $lly, $urx, $ury);
        drawSignatureAppearences($signature, $llx, $lly, $urx, $ury);
        addFormField($signature);
        return $signature;
    }

    /**
    * @param field
    * @param name
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function setSignatureParams(PdfFormField $field, $name, $llx, $lly, $urx, $ury) {
        $field->setWidget(new Rectangle($llx, $lly, $urx, $ury), PdfAnnotation::$HIGHLIGHT_INVERT);
        $field->setFieldName($name);
        $field->setFlags(PdfAnnotation::FLAGS_PRINT);
        $field->setPage();
        $field->setMKBorderColor(Color::$black);
        $field->setMKBackgroundColor(Color::$white);
    }

    /**
    * @param field
    * @param llx
    * @param lly
    * @param urx
    * @param ury
    */
    public function drawSignatureAppearences(PdfFormField $field, $llx, $lly, $urx, $ury) {
        $cb = $writer->getDirectContent();
        $tp = $cb->createAppearance($urx - $llx, $ury - $lly);
        $tp->setGrayFill(1.0);
        $tp->rectangle(0, 0, $urx - $llx, $ury - $lly);
        $tp->fill();
        $tp->setGrayStroke(0);
        $tp->setLineWidth(1);
        $tp->rectangle(0.5, 0.5, $urx - $llx - 0.5, $ury - $lly - 0.5);
        $tp->closePathStroke();
        $tp->saveState();
        $tp->rectangle(1, 1, $urx - $llx - 2, $ury - $lly - 2);
        $tp->clip();
        $tp->newPath();
        $tp->restoreState();
        $field->setAppearance(PdfAnnotation::$APPEARANCE_NORMAL, $tp);
    }

}




?>