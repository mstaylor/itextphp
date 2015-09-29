<?PHP
/*
 * $Id: TIFFField.php,v 1.1 2005/11/10 23:08:24 mstaylor Exp $
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


require_once("../../../exceptions/ClassCastException.php");
require_once("../../../exceptions/IllegalArgumentException.php");


/**
* A class representing a field in a TIFF 6.0 Image File Directory.
*
* <p> The TIFF file format is described in more detail in the
* comments for the TIFFDescriptor class.
*
* <p> A field in a TIFF Image File Directory (IFD).  A field is defined
* as a sequence of values of identical data type.  TIFF 6.0 defines
* 12 data types, which are mapped internally onto the Java datatypes
* byte, int, long, float, and double.
*
* <p><b> This class is not a committed part of the JAI API.  It may
* be removed or changed in future releases of JAI.</b>
*
* @see TIFFDirectory
*/


class TIFFField
{


    /** Flag for 8 bit unsigned integers. */
    const TIFF_BYTE      =  1;

    /** Flag for null-terminated ASCII strings. */
    const TIFF_ASCII     =  2;

    /** Flag for 16 bit unsigned integers. */
    const TIFF_SHORT     =  3;

    /** Flag for 32 bit unsigned integers. */
    const TIFF_LONG      =  4;

    /** Flag for pairs of 32 bit unsigned integers. */
    const TIFF_RATIONAL  =  5;

    /** Flag for 8 bit signed integers. */
    const TIFF_SBYTE     =  6;

    /** Flag for 8 bit uninterpreted bytes. */
    const TIFF_UNDEFINED =  7;

    /** Flag for 16 bit signed integers. */
    const TIFF_SSHORT    =  8;

    /** Flag for 32 bit signed integers. */
    const TIFF_SLONG     =  9;

    /** Flag for pairs of 32 bit signed integers. */
    const TIFF_SRATIONAL = 10;

    /** Flag for 32 bit IEEE floats. */
    const TIFF_FLOAT     = 11;

    /** Flag for 64 bit IEEE doubles. */
    const TIFF_DOUBLE    = 12;

    /** The tag number. */
    protected $tag = 0;

    /** The tag type. */
    protected $type = 0;

    /** The number of data items present in the field. */
    protected $count = 0;

    /** The field data. */
    $data = NULL;


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
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE && is_integer($arg3) == TRUE)
                    construct4args($arg1);
                break;
            }
        }
    }


    /** The default constructor. */
    private function construct0args() {}


    /**
    * Constructs a TIFFField with arbitrary data.  The data
    * parameter must be an array of a Java type appropriate for the
    * type of the TIFF field.  Since there is no available 32-bit
    * unsigned datatype, long is used. The mapping between types is
    * as follows:
    *
    * <table border=1>
    * <tr>
    * <th> TIFF type </th> <th> Java type </th>
    * <tr>
    * <td><tt>TIFF_BYTE</tt></td>      <td><tt>byte</tt></td>
    * <tr>
    * <td><tt>TIFF_ASCII</tt></td>     <td><tt>String</tt></td>
    * <tr>
    * <td><tt>TIFF_SHORT</tt></td>     <td><tt>char</tt></td>
    * <tr>
    * <td><tt>TIFF_LONG</tt></td>      <td><tt>long</tt></td>
    * <tr>
    * <td><tt>TIFF_RATIONAL</tt></td>  <td><tt>long[2]</tt></td>
    * <tr>
    * <td><tt>TIFF_SBYTE</tt></td>     <td><tt>byte</tt></td>
    * <tr>
    * <td><tt>TIFF_UNDEFINED</tt></td> <td><tt>byte</tt></td>
    * <tr>
    * <td><tt>TIFF_SSHORT</tt></td>    <td><tt>short</tt></td>
    * <tr>
    * <td><tt>TIFF_SLONG</tt></td>     <td><tt>int</tt></td>
    * <tr>
    * <td><tt>TIFF_SRATIONAL</tt></td> <td><tt>int[2]</tt></td>
    * <tr>
    * <td><tt>TIFF_FLOAT</tt></td>     <td><tt>float</tt></td>
    * <tr>
    * <td><tt>TIFF_DOUBLE</tt></td>    <td><tt>double</tt></td>
    * </table>
    */
    private function construct4args($tag, $type, $count, $data) {
        $this->tag = $tag;
        $this->type = $type;
        $this->count = $count;
        $this->data = $data;
    }


    /**
    * Returns the tag number, between 0 and 65535.
    */
    public function getTag() {
        return $tag;
    }

    /**
    * Returns the type of the data stored in the IFD.
    * For a TIFF6.0 file, the value will equal one of the
    * TIFF_ constants defined in this class.  For future
    * revisions of TIFF, higher values are possible.
    *
    */
    public function getType() {
        return $type;
    }

    /**
    * Returns the number of elements in the IFD.
    */
    public function getCount() {
        return $count;
    }



    /**
    * Returns the data as an uninterpreted array of bytes.
    * The type of the field must be one of TIFF_BYTE, TIFF_SBYTE,
    * or TIFF_UNDEFINED;
    *
    * <p> For data in TIFF_BYTE format, the application must take
    * care when promoting the data to longer integral types
    * to avoid sign extension.
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_BYTE, TIFF_SBYTE, or TIFF_UNDEFINED.
    */
    public function getAsBytes() {
        return /*(byte[])*/$data;
    }

    /**
    * Returns TIFF_SHORT data as an array of chars (unsigned 16-bit
    * integers).
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_SHORT.
    */
    public function getAsChars() {
        return /*(char[])*/$data;
    }

    /**
    * Returns TIFF_SSHORT data as an array of shorts (signed 16-bit
    * integers).
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_SSHORT.
    */
    public function getAsShorts() {
        return /*(short[])*/$data;
    }

    /**
    * Returns TIFF_SLONG data as an array of ints (signed 32-bit
    * integers).
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_SLONG.
    */
    public function getAsInts() {
        return /*(int[])*/$data;
    }

    /**
    * Returns TIFF_LONG data as an array of longs (signed 64-bit
    * integers).
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_LONG.
    */
    public function getAsLongs() {
        return /*(long[])*/$data;
    }

    /**
    * Returns TIFF_FLOAT data as an array of floats. 
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_FLOAT.
    */
    public function getAsFloats() {
        return /*(float[])*/$data;
    }

    /**
    * Returns TIFF_DOUBLE data as an array of doubles. 
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_DOUBLE.
    */
    public function getAsDoubles() {
        return /*(double[])*/$data;
    }

    /**
    * Returns TIFF_SRATIONAL data as an array of 2-element arrays of ints.
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_SRATIONAL.
    */
    public function getAsSRationals() {
        return /*(int[][])*/$data;
    }

    /**
    * Returns TIFF_RATIONAL data as an array of 2-element arrays of longs.
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_RATTIONAL.
    */
    public function getAsRationals() {
        return /*(long[][])*/$data;
    }


    /**
    * Returns data in TIFF_BYTE, TIFF_SBYTE, TIFF_UNDEFINED, TIFF_SHORT,
    * TIFF_SSHORT, or TIFF_SLONG format as an int.
    *
    * <p> TIFF_BYTE and TIFF_UNDEFINED data are treated as unsigned;
    * that is, no sign extension will take place and the returned
    * value will be in the range [0, 255].  TIFF_SBYTE data will
    * be returned in the range [-128, 127].
    *
    * <p> A ClassCastException will be thrown if the field is not of
    * type TIFF_BYTE, TIFF_SBYTE, TIFF_UNDEFINED, TIFF_SHORT,
    * TIFF_SSHORT, or TIFF_SLONG.
    */
    public function getAsInt($index) {
        switch ($type) {
        case TIFFField::TIFF_BYTE: 
        case TIFFField::TIFF_UNDEFINED:
            return itextphp_bytes_getIntValue($data, $index) & 0xff;
        case TIFFField::TIFF_SBYTE:
            return itextphp_bytes_getIntValue($data, $index);
        case TIFFField::TIFF_SHORT:
            return ord($data[$index]) & 0xffff;
        case TIFFField::TIFF_SSHORT:
            return $data[$index];
        case TIFFField::TIFF_SLONG:
            return $data[$index];
        default:
            throw new ClassCastException();
        }
    }

    /**
    * Returns data in TIFF_BYTE, TIFF_SBYTE, TIFF_UNDEFINED, TIFF_SHORT,
    * TIFF_SSHORT, TIFF_SLONG, or TIFF_LONG format as a long.
    *
    * <p> TIFF_BYTE and TIFF_UNDEFINED data are treated as unsigned;
    * that is, no sign extension will take place and the returned
    * value will be in the range [0, 255].  TIFF_SBYTE data will
    * be returned in the range [-128, 127].
    *
    * <p> A ClassCastException will be thrown if the field is not of
    * type TIFF_BYTE, TIFF_SBYTE, TIFF_UNDEFINED, TIFF_SHORT,
    * TIFF_SSHORT, TIFF_SLONG, or TIFF_LONG.
    */
    public function getAsLong($index) {
        switch ($type) {
        case TIFFField::TIFF_BYTE: 
        case TIFFField::TIFF_UNDEFINED:
            return itextphp_bytes_getIntValue($data, $index) & 0xff;
        case TIFFField::TIFF_SBYTE:
            return itextphp_bytes_getIntValue($data, $index);
        case TIFFField::TIFF_SHORT:
            return ord($data[$index]) & 0xffff;
        case TIFFField::TIFF_SSHORT:
            return $data[$index];
        case TIFFField::TIFF_SLONG:
            return $data[$index];
        case TIFFField::TIFF_LONG:
            return $data[$index];
        default:
            throw new ClassCastException();
        }
    }

    /**
    * Returns data in any numerical format as a float.  Data in
    * TIFF_SRATIONAL or TIFF_RATIONAL format are evaluated by
    * dividing the numerator into the denominator using
    * double-precision arithmetic and then truncating to single
    * precision.  Data in TIFF_SLONG, TIFF_LONG, or TIFF_DOUBLE
    * format may suffer from truncation.
    *
    * <p> A ClassCastException will be thrown if the field is
    * of type TIFF_UNDEFINED or TIFF_ASCII.
    */
    public function getAsFloat($index) {
        switch ($type) {
        case TIFFField::TIFF_BYTE:
            return itextphp_bytes_getIntValue($data, $index) & 0xff;
        case TIFFField::TIFF_SBYTE:
            return itextphp_bytes_getIntValue($data, $index);
        case TIFFField::TIFF_SHORT:
            return ord($data[$index]) & 0xffff;
        case TIFFField::TIFF_SSHORT:
            return $data[$index];
        case TIFFField::TIFF_SLONG:
            return $data[$index];
        case TIFFField::TIFF_LONG:
            return $data[$index];
        case TIFFField::TIFF_FLOAT:
            return $data[$index];
        case TIFFField::TIFF_DOUBLE:
            return (float)$data[$index];
        case TIFFField::TIFF_SRATIONAL:
            $ivalue = getAsSRational($index);
            return (float)((double)$ivalue[0]/$ivalue[1]);
        case TIFFField::TIFF_RATIONAL:
            $lvalue = getAsRational($index);
            return (float)((double)$lvalue[0]/$lvalue[1]);
        default:
            throw new ClassCastException();
        }
    }

    /**
    * Returns data in any numerical format as a float.  Data in
    * TIFF_SRATIONAL or TIFF_RATIONAL format are evaluated by
    * dividing the numerator into the denominator using
    * double-precision arithmetic.
    *
    * <p> A ClassCastException will be thrown if the field is of
    * type TIFF_UNDEFINED or TIFF_ASCII.
    */
    public function getAsDouble($index) {
        switch ($type) {
        case TIFFField::TIFF_BYTE:
            return itextphp_bytes_getIntValue($data, $index) & 0xff;
        case TIFFField::TIFF_SBYTE:
            return itextphp_bytes_getIntValue($data, $index);
        case TIFFField::TIFF_SHORT:
            return ord($data[$index]) & 0xffff;
        case TIFFField::TIFF_SSHORT:
            return $data[$index];
        case TIFFField::TIFF_SLONG:
            return $data[$index];
        case TIFFField::TIFF_LONG:
            return $data[$index];
        case TIFFField::TIFF_FLOAT:
            return $data[$index];
        case TIFFField::TIFF_DOUBLE:
            return $data[$index];
        case TIFFField::TIFF_SRATIONAL:
            $ivalue = getAsSRational($index);
            return (double)$ivalue[0]/$ivalue[1];
        case TIFFField::TIFF_RATIONAL:
            $lvalue = getAsRational($index);
            return (double)$lvalue[0]/$lvalue[1];
        default:
            throw new ClassCastException();
        }
    }

    /**
    * Returns a TIFF_ASCII data item as a String.
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_ASCII.
    */
    public function getAsString($index) {
        return $data[$index];
    }

    /**
    * Returns a TIFF_SRATIONAL data item as a two-element array
    * of ints.
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_SRATIONAL.
    */
    public function getAsSRational($index) {
        return $data[$index];
    }

    /**
    * Returns a TIFF_RATIONAL data item as a two-element array
    * of ints.
    *
    * <p> A ClassCastException will be thrown if the field is not
    * of type TIFF_RATIONAL.
    */
    public function getAsRational($index) {
        return $data[$index];
    }

    /**
    * Compares this <code>TIFFField</code> with another
    * <code>TIFFField</code> by comparing the tags.
    *
    * <p><b>Note: this class has a natural ordering that is inconsistent
    * with <code>equals()</code>.</b>
    *
    * @throws IllegalArgumentException if the parameter is <code>null</code>.
    * @throws ClassCastException if the parameter is not a
    *         <code>TIFFField</code>.
    */
    public function compareTo($o) {
        if($o == NULL) {
            throw new IllegalArgumentException();
        }

        $oTag = $o->getTag();

        if($tag < $oTag) {
            return -1;
        } else if($tag > $oTag) {
            return 1;
        } else {
            return 0;
        }
    }



}




?>