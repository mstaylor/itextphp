<?PHP
/*
 * $Id: PRTokeniser.php,v 1.1 2005/10/03 19:26:43 mstaylor Exp $
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

require_once("RandomAccessFileOrArray.php");
require_once("../../exceptions/IOException.php");


class PRTokeniser
{

    const TK_NUMBER = 1;
    const TK_STRING = 2;
    const TK_NAME = 3;
    const TK_COMMENT = 4;
    const TK_START_ARRAY = 5;
    const TK_END_ARRAY = 6;
    const TK_START_DIC = 7;
    const TK_END_DIC = 8;
    const TK_REF = 9;
    const TK_OTHER = 10;


    public static $delims = array(
        TRUE,  TRUE,  FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        TRUE,  TRUE,  FALSE, TRUE,  TRUE,  FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, TRUE,  FALSE, FALSE, FALSE, FALSE, TRUE,  FALSE,
        FALSE, TRUE,  TRUE,  FALSE, FALSE, FALSE, FALSE, FALSE, TRUE,  FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, TRUE,  FALSE, TRUE,  FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, TRUE,  FALSE, TRUE,  FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE,
        FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE);

    static final $EMPTY = "";


    protected $file = NULL;
    protected $type = 0;
    protected $stringValue "";
    protected $reference = 0;
    protected $generation = 0;
    protected $hexString = FALSE;

    const LINE_SEGMENT_SIZE = 256;

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_string($arg1) == TRUE)
                {
                    construct1argstring($arg1);
                }
                else if (is_resource($arg1) == TRUE)
                {
                    construct1argbyte($arg1);
                }
                else if ($arg1 instanceof RandomAccessFileOrArray)
                {
                    construct1argRandomAccessFileOrArray($arg1);
                }
                break;
            }
        }
    }


    private function construct1argstring($filename)
    {
        $file = new RandomAccessFileOrArray($filename);
    }

    private function construct1argbyte($pdfIn)
    {
        $file = new RandomAccessFileOrArray($pdfIn);
    }

    private function construct1argRandomAccessFileOrArray($file)
    {
        $this->file = $file;
    }

    public function seek($pos)  {
        $file->seek($pos);
    }

    public function getFilePointer() {
        return $file->getFilePointer();
    }

    public function close() {
        $file->close();
    }

    public function length()  {
        return file.length();
    }

    public function read()  {
        return $file->read();
    }

    public function getSafeFile() {
        return new RandomAccessFileOrArray($file);
    }

    public function getFile() {
        return $file;
    }

    public function readString($size) {
        $buf = "";
        $ch = 0;
        while (($size--) > 0) {
            $ch = $file->read();
            if ($ch == -1)
                break;
            $buf .= chr($ch);
        }
        return $buf;
    }

    public static function isWhitespace($ch) {
        return ($ch == 0 || $ch == 9 || $ch == 10 || $ch == 12 || $ch == 13 || $ch == 32);
    }

     public static function isDelimiter($ch) {
        return ($ch == ord('(') || $ch == ord(')') || $ch == ord('<') || $ch == ord('>') || $ch == ord('[') || $ch == ord(']') || $ch == ord('/') || $ch == ord('%'));
    }

    public static function isDelimiterWhitespace($ch) {
        return $delims[$ch + 1];
    }

    public function getTokenType() {
        return $type;
    }

    public function getStringValue() {
        return $stringValue;
    }

    public function getReference() {
        return $reference;
    }

    public function getGeneration() {
        return $generation;
    }

    public function backOnePosition($ch)  {
        if ($ch != -1)
            $file->pushBack($ch);
    }

     public function throwError($error)  {
        throw new IOException($error . " at file pointer " . $file->getFilePointer());
    }

    public function checkPdfHeader()  {
        $file->setStartOffset(0);
        $str = readString(1024);
        $idx = strpos($str, "%PDF-1.");
        if ($idx < 0)
            throw new IOException("PDF header signature not found.");
        $file->setStartOffset($idx);
        return $str[$idx + 7];
    }

    public function checkFdfHeader() {
        $file->setStartOffset(0);
        $str = readString(1024);
        $idx = strpos($str, "%FDF-1.2");
        if ($idx < 0)
            throw new IOException("FDF header signature not found.");
        $file->setStartOffset($idx);
    }

    public function getStartxref()  {
        $size = min(1024, $file->length());
        $pos = $file->length() - $size;
        $file->seek($pos);
        $str = readString(1024);
        $idx = strpos($str, "startxref");
        if ($idx < 0)
            throw new IOException("PDF startxref not found.");
        return $pos + $idx;
    }

    public static function getHex($v) {
        if ($v >= ord('0') && $v <= ord('9'))
            return $v - ord('0');
        if ($v >= ord('A') && $v <= ord('F'))
            return $v - ord('A') + 10;
        if ($v >= ord('a') && $v <= ord('f'))
            return $v - ord('a') + 10;
        return -1;
    }

    public function nextValidToken()  {
        $level = 0;
        $n1 = NULL;
        $n2 = NULL;
        $ptr = 0;
        while (nextToken()) {
            if ($type == PRTokeniser::TK_COMMENT)
                continue;
            switch ($level) {
                case 0:
                {
                    if ($type != PRTokeniser::TK_NUMBER)
                        return;
                    $ptr = $file->getFilePointer();
                    $n1 = $stringValue;
                    ++$level;
                    break;
                }
                case 1:
                {
                    if ($type != PRTokeniser::TK_NUMBER) {
                        $file->seek($ptr);
                        $type = PRTokeniser::TK_NUMBER;
                        $stringValue = $n1;
                        return;
                    }
                    $n2 = $stringValue;
                    ++$level;
                    break;
                }
                default:
                {
                    if ($type != PRTokeniser::TK_OTHER || strcmp($stringValue,"R") != 0) {
                        $file->seek($ptr);
                        $type = PRTokeniser::TK_NUMBER;
                        $stringValue = $n1;
                        return;
                    }
                    $type = PRTokeniser::TK_REF;
                    $reference = (integer)$n1;
                    $generation = (integer)$n2;
                    return;
                }
            }
        }
        throwError("Unexpected end of file");
    }

    public function nextToken() {
        $outBuf = NULL;
        $stringValue = PRTokeniser::EMPTY;
        $ch = 0;
        do {
            $ch = $file->read();
        } while ($ch != -1 && isWhitespace($ch) == TRUE);
        if ($ch == -1)
            return FALSE;
        switch ($ch) {
            case ord('['):
                $type = PRTokeniser::TK_START_ARRAY;
                break;
            case ord(']'):
                $type = PRTokeniser::TK_END_ARRAY;
                break;
            case ord('/'):
            {
                $outBuf = "";
                $type = PRTokeniser::TK_NAME;
                while (TRUE) {
                    $ch = $file->read();
                    if (PRTokeniser::$delims[$ch + 1])
                        break;
                    if ($ch == ord('#')) {
                        $ch = (getHex($file->read()) << 4) + getHex($file->read());
                    }
                    $outBuf .= chr($ch);
                }
                backOnePosition($ch);
                break;
            }
            case ord('>'):
                $ch = $file->read();
                if ($ch != ord('>'))
                    throwError("'>' not expected");
                $type = PRTokeniser::TK_END_DIC;
                break;
            case ord('<'):
            {
                $v1 = $file->read();
                if ($v1 == ord('<')) {
                    $type = PRTokeniser::TK_START_DIC;
                    break;
                }
                $outBuf = "";
                $type = PRTokeniser::TK_STRING;
                $hexString = TRUE;
                $v2 = 0;
                while (TRUE) {
                    while (isWhitespace($v1) == TRUE)
                        $v1 = $file->read();
                    if ($v1 == ord('>'))
                        break;
                    $v1 = getHex($v1);
                    if ($v1 < 0)
                        break;
                    $v2 = $file->read();
                    while (isWhitespace($v2) == TRUE)
                        $v2 = $file->read();
                    if ($v2 == ord('>')) {
                        $ch = $v1 << 4;
                        $outBuf .= chr($ch);
                        break;
                    }
                    $v2 = getHex($v2);
                    if ($v2 < 0)
                        break;
                    $ch = ($v1 << 4) + $v2;
                    $outBuf .= chr($ch);
                    $v1 = $file->read();
                }
                if ($v1 < 0 || $v2 < 0)
                    throwError("Error reading string");
                break;
            }
            case ord('%'):
                $type = PRTokeniser::TK_COMMENT;
                do {
                    $ch = $file->read();
                } while ($ch != -1 && $ch != ord('\r') && $ch != ord('\n'));
                break;
            case ord('('):
            {
                $outBuf = "";
                $type = PRTokeniser::TK_STRING;
                $hexString = FALSE;
                $nesting = 0;
                while (TRUE) {
                    $ch = $file->read();
                    if ($ch == -1)
                        break;
                    if ($ch == ord('(')) {
                        ++$nesting;
                    }
                    else if ($ch == ord(')')) {
                        --$nesting;
                    }
                    else if ($ch == ord('\\')) {
                        $lineBreak = FALSE;
                        $ch = $file->read();
                        switch ($ch) {
                            case ord('n'):
                                $ch = '\n';
                                break;
                            case ord('r'):
                                $ch = '\r';
                                break;
                            case ord('t'):
                                $ch = '\t';
                                break;
                            case ord('b'):
                                $ch = '\b';
                                break;
                            case ord('f'):
                                $ch = '\f';
                                break;
                            case ord('('):
                            case ord(')'):
                            case ord('\\'):
                                break;
                            case ord('\r'):
                                $lineBreak = TRUE;
                                $ch = $file->read();
                                if ($ch != ord('\n'))
                                    backOnePosition($ch);
                                break;
                            case ord('\n'):
                                $lineBreak = TRUE;
                                break;
                            default:
                            {
                                if ($ch < ord('0') || $ch > ord('7')) {
                                    break;
                                }
                                $octal = $ch - ord('0');
                                $ch = $file->read();
                                if ($ch < ord('0') || $ch > ord('7')) {
                                    backOnePosition($ch);
                                    $ch = $octal;
                                    break;
                                }
                                $octal = ($octal << 3) + $ch - ord('0');
                                $ch = $file->read();
                                if ($ch < ord('0') || $ch > ord('7')) {
                                    backOnePosition($ch);
                                    $ch = $octal;
                                    break;
                                }
                                $octal = ($octal << 3) + $ch - ord('0');
                                $ch = $octal & 0xff;
                                break;
                            }
                        }
                        if ($lineBreak == TRUE)
                            continue;
                        if ($ch < 0)
                            break;
                    }
                    else if ($ch == ord('\r')) {
                        $ch = $file->read();
                        if ($ch < 0)
                            break;
                        if ($ch != ord('\n')) {
                            backOnePosition($ch);
                            $ch = ord('\n');
                        }
                    }
                    if ($nesting == -1)
                        break;
                    $outBuf .= chr($ch);
                }
                if ($ch == -1)
                    throwError("Error reading string");
                break;
            }
            default:
            {
                $outBuf = "";
                if ($ch == ord('-') || $ch == ord('+') || $ch == ord('.') || ($ch >= ord('0') && $ch <= ord('9'))) {
                    $type = PRTokeniser::TK_NUMBER;
                    do {
                        $outBuf .= chr($ch);
                        $ch = $file->read();
                    } while ($ch != -1 && (($ch >= ord('0') && $ch <= ord('9')) || $ch == ord('.')));
                }
                else {
                    $type = PRTokeniser::TK_OTHER;
                    do {
                        $outBuf .= chr($ch);
                        $ch = $file->read();
                    } while (PRTokeniser::$delims[$ch + 1] == FALSE);
                }
                backOnePosition($ch);
                break;
            }
        }
        if ($outBuf != NULL)
            $stringValue = $outBuf;
        return TRUE;
    }

    public function intValue() {
        return (integer)$stringValue;
    }

    public function readLineSegment($input)
    {
        $c = -1;
        $eol = FALSE;
        $ptr = 0;
        $len = itextphp_bytes_getSize($input);
	// ssteward, pdftk-1.10, 040922: 
	// skip initial whitespace; added this because PdfReader.rebuildXref()
	// assumes that line provided by readLineSegment does not have init. whitespace;
	if ( $ptr < $len ) {
	    while ( isWhitespace( ($c = read()) )  );
	}
	while ( $eol == FALSE && $ptr < $len ) {
	    switch ($c) {
                case -1:
                case ord('\n'):
                    $eol = TRUE;
                    break;
                case ord('\r'):
                    $eol = TRUE;
                    $cur = getFilePointer();
                    if ((read()) != ord('\n')) {
                        $seek($cur);
                    }
                    break;
                default:
                    $tmpByte = itextphp_bytes_create($c);
                    itextphp_updateByteWithByte($input, $ptr++, $tmpByte, 0);
                    break;
            }

	    // break loop? do it before we read() again
	    if( $eol == TRUE || $len <= $ptr ) {
		break;
	    }
	    else {
		$c = read();
	    }
        }
        if ($ptr >= $len) {
            $eol = FALSE;
            while ($eol == FALSE) {
                switch ($c = read()) {
                    case -1:
                    case ord('\n'):
                        $eol = TRUE;
                        break;
                    case ord('\r'):
                        $eol = TRUE;
                        $cur = getFilePointer();
                        if ((read()) != ord('\n')) {
                            seek($cur);
                        }
                        break;
                }
            }
        }

        if (($c == -1) && ($ptr == 0)) {
            return FALSE;
        }
        if ($ptr + 2 <= $len) {
            $tmpByteSpace = itextphp_bytes_createfromRaw(' ');
            $tmpByteX = itextphp_bytes_createfromRaw('X');
            itextphp_updateByteWithByte($input, $ptr++, $tmpByteSpace, 0);
            itextphp_updateByteWithByte($input, $ptr, $tmpByteX, 0);
        }
        return TRUE;
    }

     public static function checkObjectStart($line) {
        try {
            PRTokeniser tk = new PRTokeniser($line);
            $num = 0;
            $gen = 0;
            if ($tk->nextToken() == FALSE || $tk->getTokenType() != PRTokeniser::TK_NUMBER)
                return null;
            $num = $tk->intValue();
            if ($tk->nextToken() == FALSE || $tk->getTokenType() != PRTokeniser::TK_NUMBER)
                return NULL;
            $gen = $tk->intValue();
            if ($tk->nextToken() == FALSE)
                return NULL;
            if (strcmp($tk->getStringValue(), "obj") != 0)
                return NULL;
            return new array($num, $gen);
        }
        catch (Exception $ioe) {
            // empty on purpose
        }
        return NULL;
    }

    public function isHexString() {
        return $this->hexString;
    }

}
?>