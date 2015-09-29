<?PHP
/*
 * $Id: TIFFDirectory.php,v 1.1 2005/11/10 23:08:24 mstaylor Exp $
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



require_once("../../../exceptions/IOException.php");
require_once("../../../exceptions/IllegalArgumentException.php");
require_once("../../../exceptions/ArrayIndexOutOfBoundsException.php");
require_once("../../../exceptions/EOFException.php");
require_once("../RandomAccessFileOrArray.php");
require_once("TIFFField.php");


/**
* A class representing an Image File Directory (IFD) from a TIFF 6.0
* stream.  The TIFF file format is described in more detail in the
* comments for the TIFFDescriptor class.
*
* <p> A TIFF IFD consists of a set of TIFFField tags.  Methods are
* provided to query the set of tags and to obtain the raw field
* array.  In addition, convenience methods are provided for acquiring
* the values of tags that contain a single value that fits into a
* byte, int, long, float, or double.
*
* <p> Every TIFF file is made up of one or more public IFDs that are
* joined in a linked list, rooted in the file header.  A file may
* also contain so-called private IFDs that are referenced from
* tag data and do not appear in the main list.
*
* <p><b> This class is not a committed part of the JAI API.  It may
* be removed or changed in future releases of JAI.</b>
*
* @see TIFFField
*/


class TIFFDirectory
{

    /** A boolean storing the endianness of the stream. */
    protected $isBigEndian = FALSE;

    /** The number of entries in the IFD. */
    protected $numEntries = 0;

    /** An array of TIFFFields. */
    protected $fields = array();

    /** A Hashtable indexing the fields by tag number. */
    protected $fieldIndex = array();

    /** The offset of this IFD. */
    protected $IFDOffset = 8;

    /** The offset of the next IFD. */
    protected $nextIFDOffset = 0;


    private static $sizeOfType = array(
        0, //  0 = n/a
        1, //  1 = byte
        1, //  2 = ascii
        2, //  3 = short
        4, //  4 = long
        8, //  5 = rational
        1, //  6 = sbyte
        1, //  7 = undefined
        2, //  8 = sshort
        4, //  9 = slong
        8, // 10 = srational
        4, // 11 = float
        8  // 12 = double
    );



    public function __construct()
    {
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
                if ($arg1 instanceof RandomAccessFileOrArray && is_integer($arg2) == TRUE)
                    construct2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                f ($arg1 instanceof RandomAccessFileOrArray && is_integer($arg2) == TRUE && is_integer($arg3) == TRUE)
                    construct3args($arg1, $arg2, $arg3);
                break;
            }
        }

    }

    /** The default constructor. */
    private function construct0args() {}





    private static function isValidEndianTag($endian) {
        return (($endian == 0x4949) || ($endian == 0x4d4d));
    }


    /**
    * Constructs a TIFFDirectory from a SeekableStream.
    * The directory parameter specifies which directory to read from
    * the linked list present in the stream; directory 0 is normally
    * read but it is possible to store multiple images in a single
    * TIFF file by maintaing multiple directories.
    *
    * @param stream a SeekableStream to read from.
    * @param directory the index of the directory to read.
    */
    private function construct2args(RandomAccessFileOrArray $stream, $directory)
    {

        $global_save_offset = $stream->getFilePointer();
        $ifd_offset = 0;

        // Read the TIFF header
        $stream->seek(0);
        $endian = $stream->readUnsignedShort();
        if (TIFFDirectory::isValidEndianTag($endian) == FALSE) {
            throw new IllegalArgumentException("Bad endianness tag (not 0x4949 or 0x4d4d).");
        }
        $isBigEndian = ($endian == 0x4d4d);

        $magic = readUnsignedShort($stream);
        if ($magic != 42) {
            throw new IllegalArgumentException("Bad magic number, should be 42.");
        }

        // Get the initial ifd offset as an unsigned int (using a long)
        $ifd_offset = readUnsignedInt($stream);

        for ($i = 0; $i < $directory; $i++) {
            if ($ifd_offset == 0) {
                throw new IllegalArgumentException("Directory number too large.");
            }

            $stream->seek($ifd_offset);
            $entries = readUnsignedShort($stream);
            $stream->skip(12*$entries);

            $ifd_offset = readUnsignedInt($stream);
        }

        $stream->seek($ifd_offset);
        initialize($stream);
        $stream->seek($global_save_offset);
    }

    /**
    * Constructs a TIFFDirectory by reading a SeekableStream.
    * The ifd_offset parameter specifies the stream offset from which
    * to begin reading; this mechanism is sometimes used to store
    * private IFDs within a TIFF file that are not part of the normal
    * sequence of IFDs.
    *
    * @param stream a SeekableStream to read from.
    * @param ifd_offset the long byte offset of the directory.
    * @param directory the index of the directory to read beyond the
    *        one at the current stream offset; zero indicates the IFD
    *        at the current offset.
    */
    private function construct3args(RandomAccessFileOrArray $stream, $ifd_offset, $directory)
    {

        $global_save_offset = $stream->getFilePointer();
        $stream->seek(0);
        $endian = $stream->readUnsignedShort();
        if (isValidEndianTag($endian) == FALSE) {
            throw new IllegalArgumentException("Bad endianness tag (not 0x4949 or 0x4d4d).");
        }
        $isBigEndian = ($endian == 0x4d4d);

        // Seek to the first IFD.
        $stream->seek($ifd_offset);

        // Seek to desired IFD if necessary.
        $dirNum = 0;
        while($dirNum < $directory) {
            // Get the number of fields in the current IFD.
            $numEntries = readUnsignedShort($stream);

            // Skip to the next IFD offset value field.
            $stream->seek($ifd_offset + 12*$numEntries);

            // Read the offset to the next IFD beyond this one.
            $ifd_offset = readUnsignedInt($stream);

            // Seek to the next IFD.
            $stream->seek($ifd_offset);

            // Increment the directory.
            $dirNum++;
        }

        initialize($stream);
        $stream->seek($global_save_offset);
    }


    private function initialize(RandomAccessFileOrArray $stream) 
    {
        $nextTagOffset = 0;
        $maxOffset = (integer) $stream->length();
        $i = 0;
        $j = 0;

        $IFDOffset = $stream->getFilePointer();

        $numEntries = readUnsignedShort($stream);
        $fields = array();

        for ($i = 0; ($i < $numEntries) && ($nextTagOffset < $maxOffset); $i++) {
            $tag = readUnsignedShort($stream);
            $type = readUnsignedShort($stream);
            $count = (integer)(readUnsignedInt($stream));
            $value = 0;
            $processTag = TRUE;

            // The place to return to to read the next tag
            $nextTagOffset = $stream->getFilePointer() + 4;

            try {
                // If the tag data can't fit in 4 bytes, the next 4 bytes
                // contain the starting offset of the data
                if ($count*$sizeOfType[$type] > 4) {
                    $valueOffset = readUnsignedInt($stream);

                    // bounds check offset for EOF
                    if ($valueOffset < $maxOffset) {
                        $stream->seek($valueOffset);
                    }
                    else {
                        // bad offset pointer .. skip tag
                        $processTag = FALSE;
                    }
                }
            } catch (ArrayIndexOutOfBoundsException $ae) {
                // if the data type is unknown we should skip this TIFF Field
                $processTag = FALSE;
            }

            if ($processTag == TRUE) {
                $fieldIndex[$tag] = $i;
                $obj = NULL;

                switch ($type) {
                    case TIFFField::TIFF_BYTE:
                    case TIFFField::TIFF_SBYTE:
                    case TIFFField::TIFF_UNDEFINED:
                    case TIFFField::TIFF_ASCII:
                        $bvalues = itextphp_bytes_create($count);
                        $stream->readFully($bvalues, 0, $count);

                        if ($type == TIFFField::TIFF_ASCII) {

                        // Can be multiple strings
                            $index = 0;
                            $prevIndex = 0;
                            $v = array();

                            while ($index < $count) {

                                while (($index < $count) && (itextphp_bytes_getIntValue($bvalues, $index++) != 0));

                                // When we encountered zero, means one string has ended
                                $tmpstring = itextphp_getAnsiString($bvalues);
                                array_push($v, substr($tmpstring, $prevIndex,($index - $prevIndex)) );
                                $prevIndex = $index;
                            }

                            $count = count($v);
                            $strings = array();
                            for ($c = 0 ; $c < $count; $c++) {
                                $strings[$c] = (String)$v[$c];
                            }

                            $obj = $strings;
                        } else {
                            $obj = $bvalues;
                        }

                        break;

                    case TIFFField::TIFF_SHORT:
                        $cvalues = array();
                        for ($j = 0; $j < $count; $j++) {
                            $cvalues[$j] = (readUnsignedShort($stream));
                        }
                        $obj = $cvalues;
                        break;

                    case TIFFField::TIFF_LONG:
                        $lvalues = array();
                        for ($j = 0; $j < $count; $j++) {
                            $lvalues[$j] = readUnsignedInt($stream);
                        }
                        $obj = $lvalues;
                        break;

                    case TIFFField::TIFF_RATIONAL:
                        $llvalues = array();
                        for ($j = 0; $j < $count; $j++) {
                            $llvalues[$j][0] = readUnsignedInt($stream);
                            $llvalues[$j][1] = readUnsignedInt($stream);
                        }
                        $obj = $llvalues;
                        break;

                    case TIFFField::TIFF_SSHORT:
                        $svalues = array();
                        for ($j = 0; j < $count; $j++) {
                            $svalues[$j] = readShort($stream);
                        }
                        $obj = $svalues;
                        break;

                    case TIFFField::TIFF_SLONG:
                        $ivalues = array();
                        for ($j = 0; $j < $count; $j++) {
                            $ivalues[$j] = readInt($stream);
                        }
                        $obj = $ivalues;
                        break;

                    case TIFFField::TIFF_SRATIONAL:
                        $iivalues = array();
                        for ($j = 0; $j < $count; $j++) {
                            $iivalues[$j][0] = readInt($stream);
                            $iivalues[$j][1] = readInt($stream);
                        }
                        $obj = $iivalues;
                        break;

                    case TIFFField::TIFF_FLOAT:
                        $fvalues = array();
                        for ($j = 0; $j < $count; $j++) {
                            $fvalues[$j] = readFloat($stream);
                        }
                        $obj = $fvalues;
                        break;

                    case TIFFField::TIFF_DOUBLE:
                        $dvalues = array();
                        for ($j = 0; j < $count; $j++) {
                            $dvalues[$j] = readDouble($stream);
                        }
                        $obj = $dvalues;
                        break;

                    default:
                        break;
                }

                $fields[$i] = new TIFFField($tag, $type, $count, $obj);
                }

                $stream->seek($nextTagOffset);
            }

        // Read the offset of the next IFD.
        $nextIFDOffset = readUnsignedInt($stream);
    }

    /** Returns the number of directory entries. */
    public function getNumEntries() {
        return $numEntries;
    }

    /**
    * Returns the value of a given tag as a TIFFField,
    * or null if the tag is not present.
    */
    public function getField($tag) {
        $i = fieldIndex[$tag];
        if ($i == NULL) {
            return NULL;
        } else {
            return $fields[$i];
        }
    }


    /**
    * Returns true if a tag appears in the directory.
    */
    public function isTagPresent($tag) {
        return array_key_exists($tag, $fieldIndex);
    }

    /**
    * Returns an ordered array of ints indicating the tag
    * values.
    */
    public function getTags() {
        $tags = array();
        $i = 0;


        foreach (array_keys($fieldIndex) as &$e) {
            $tags[$i++] = (integer)$e;
        }

        return $tags;
    }


    /**
    * Returns an array of TIFFFields containing all the fields
    * in this directory.
    */
    public function getFields() {
        return $fields;
    }



    public function getFieldAsByte()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE )
                    return getFieldAsByte1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE)
                    return getFieldAsByte2args($arg1, $arg2);
                break;
            }
    }



    /**
    * Returns the value of a particular index of a given tag as a
    * byte.  The caller is responsible for ensuring that the tag is
    * present and has type TIFFField.TIFF_SBYTE, TIFF_BYTE, or
    * TIFF_UNDEFINED.
    */
    private function getFieldAsByte2args($tag, $index) {
        $i = (integer)$fieldIndex[$tag];
        $b = itextphp_bytes_createfromRaw($fields[$i]);
        return itextphp_bytes_getIntValue($b, $index);
    }


    /**
    * Returns the value of index 0 of a given tag as a
    * byte.  The caller is responsible for ensuring that the tag is
    * present and has  type TIFFField.TIFF_SBYTE, TIFF_BYTE, or
    * TIFF_UNDEFINED.
    */
    private function getFieldAsByte1arg($tag) {
        return getFieldAsByte($tag, 0);
    }



    public function getFieldAsLong()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE )
                    return getFieldAsLong1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE)
                    return getFieldAsLong2args($arg1, $arg2);
                break;
            }
    }


    /**
    * Returns the value of a particular index of a given tag as a
    * long.  The caller is responsible for ensuring that the tag is
    * present and has type TIFF_BYTE, TIFF_SBYTE, TIFF_UNDEFINED,
    * TIFF_SHORT, TIFF_SSHORT, TIFF_SLONG or TIFF_LONG.
    */
    private function getFieldAsLong2args($tag, $index) {
        $i = (integer)$fieldIndex[$tag];
        return $fields[$i]->getAsLong($index);
    }

    /**
    * Returns the value of index 0 of a given tag as a
    * long.  The caller is responsible for ensuring that the tag is
    * present and has type TIFF_BYTE, TIFF_SBYTE, TIFF_UNDEFINED,
    * TIFF_SHORT, TIFF_SSHORT, TIFF_SLONG or TIFF_LONG.
    */
    private function getFieldAsLong1arg($tag) {
        return getFieldAsLong($tag, 0);
    }


    public function getFieldAsFloat()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE )
                    return getFieldAsFloat1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE)
                    return getFieldAsFloat2args($arg1, $arg2);
                break;
            }
    }


    /**
    * Returns the value of a particular index of a given tag as a
    * float.  The caller is responsible for ensuring that the tag is
    * present and has numeric type (all but TIFF_UNDEFINED and
    * TIFF_ASCII).
    */
    private function getFieldAsFloat2args($tag, $index) {
        $i = (integer)$fieldIndex[$tag];
        return fields[$i]->getAsFloat($index);
    }

    /**
    * Returns the value of index 0 of a given tag as a float.  The
    * caller is responsible for ensuring that the tag is present and
    * has numeric type (all but TIFF_UNDEFINED and TIFF_ASCII).
    */
    private function getFieldAsFloat1arg($tag) {
        return getFieldAsFloat($tag, 0);
    }

    public function getFieldAsDouble()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if (is_integer($arg1) == TRUE )
                    return getFieldAsDouble1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE)
                    return getFieldAsDouble2args($arg1, $arg2);
                break;
            }
    }

    /**
    * Returns the value of a particular index of a given tag as a
    * double.  The caller is responsible for ensuring that the tag is
    * present and has numeric type (all but TIFF_UNDEFINED and
    * TIFF_ASCII).
    */
    private function getFieldAsDouble2args($tag, $index) {
        $i = (integer)$fieldIndex[$tag];
        return $fields[$i]->getAsDouble($index);
    }

    /**
    * Returns the value of index 0 of a given tag as a double.  The
    * caller is responsible for ensuring that the tag is present and
    * has numeric type (all but TIFF_UNDEFINED and TIFF_ASCII).
    */
    private function getFieldAsDouble1arg($tag) {
        return getFieldAsDouble($tag, 0);
    }


    // Methods to read primitive data types from the stream

    private function readShort(RandomAccessFileOrArray $stream)
    {
        if ($isBigEndian == TRUE) {
            return $stream->readShort();
        } else {
            return $stream->readShortLE();
        }
    }

    private function readUnsignedShort(RandomAccessFileOrArray $stream)
    {
        if ($isBigEndian == TRUE) {
            return $stream->readUnsignedShort();
        } else {
            return $stream->readUnsignedShortLE();
        }
    }


    private function readInt(RandomAccessFileOrArray $stream)
    {
        if ($isBigEndian == TRUE) {
            return $stream->readInt();
        } else {
            return $stream->readIntLE();
        }
    }

    private function readUnsignedInt(RandomAccessFileOrArray $stream)
    {
        if ($isBigEndian == TRUE) {
            return $stream->readUnsignedInt();
        } else {
            return $stream->readUnsignedIntLE();
        }
    }

    private function readLong(RandomAccessFileOrArray $stream)
    {
        if ($isBigEndian == TRUE) {
            return $stream->readLong();
        } else {
            return $stream->readLongLE();
        }
    }

    private function readFloat(RandomAccessFileOrArray $stream)
    {
        if ($isBigEndian == TRUE) {
            return $stream->readFloat();
        } else {
            return $stream->readFloatLE();
        }
    }

    private function readDouble(RandomAccessFileOrArray $stream)
    {
        if ($isBigEndian == TRUE) {
            return $stream->readDouble();
        } else {
            return $stream->readDoubleLE();
        }
    }

    private static function readUnsignedShortStatic(RandomAccessFileOrArray $stream, $isBigEndian)
    {
        if ($isBigEndian == TRUE) {
            return $stream->readUnsignedShort();
        } else {
            return $stream->readUnsignedShortLE();
        }
    }


    private static function readUnsignedIntStatic(RandomAccessFileOrArray $stream,
    $isBigEndian)
    {
        if ($isBigEndian == TRUE) {
            return $stream->readUnsignedInt();
        } else {
            return $stream->readUnsignedIntLE();
        }
    }

    // Utilities

    /**
    * Returns the number of image directories (subimages) stored in a
    * given TIFF file, represented by a <code>SeekableStream</code>.
    */
    public static function getNumDirectories(RandomAccessFileOrArray $stream)
    {
        $pointer = $stream->getFilePointer(); // Save stream pointer

        $stream->seek(0);
        $endian = $stream->readUnsignedShort();
        if (isValidEndianTag($endian) == FALSE) {
            throw new IllegalArgumentException("Bad endianness tag (not 0x4949 or 0x4d4d).");
        }
        $isBigEndian = ($endian == 0x4d4d);
        $magic = TIFFDirectory::readUnsignedShortStatic($stream, $isBigEndian);
        if ($magic != 42) {
            throw new IllegalArgumentException("Bad magic number, should be 42.");
        }

        $stream->seek(4);
        $offset = TIFFDirectory::readUnsignedIntStatic($stream, $isBigEndian);

        $numDirectories = 0;
        while ($offset != 0) {
            ++$numDirectories;

            // EOFException means IFD was probably not properly terminated.
            try {
                $stream->seek($offset);
                $entries = TIFFDirectory::readUnsignedShortStatic($stream, $isBigEndian);
                $stream->skip(12*$entries);
                $offset = TIFFDirectory::readUnsignedIntStatic($stream, $isBigEndian);
            } catch(EOFException $eof) {
                $numDirectories--;
                break;
            }
        }

        $stream->seek($pointer); // Reset stream pointer
        return $numDirectories;
    }

    /**
    * Returns a boolean indicating whether the byte order used in the
    * the TIFF file is big-endian (i.e. whether the byte order is from
    * the most significant to the least significant)
    */
    public function isBigEndian() {
        return $isBigEndian;
    }

    /**
    * Returns the offset of the IFD corresponding to this
    * <code>TIFFDirectory</code>.
    */
    public function getIFDOffset() {
        return $IFDOffset;
    }

    /**
    * Returns the offset of the next IFD after the IFD corresponding to this
    * <code>TIFFDirectory</code>.
    */
    public function getNextIFDOffset() {
        return $nextIFDOffset;
    }

}
?>