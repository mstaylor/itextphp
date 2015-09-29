<?PHP
/*
 * $Id: Properties.php,v 1.1.1.1 2010/04/27 15:42:39 mstaylor Exp $
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
 
namespace php\util;

class Properties {
     /**
     * The property list that contains default values for any keys not
     * in this property list.
     *
     * @serial the default properties
     */
    protected $defaults;
    protected $the_array = array();
    /**
    * Creates a new empty property list with no default values.
    */
    public function __construct() {
        $num_args=func_num_args();
        switch ($num_args) {
            case 0: {
                break;
            }
            case 1: {
                $theDefaults = func_get_arg(0); 
                $this->defaults = $theDefaults;
                break;
            }
        }
    }
    
    /**
    * Tests if the specified object is a key in this hashtable.
    *
    * @param   key   possible key
    * @return  <code>true</code> if and only if the specified object
    *          is a key in this hashtable, as determined by the
    *          <tt>equals</tt> method; <code>false</code> otherwise.
    * @throws  NullPointerException  if the key is <code>null</code>
    * @see     #contains(Object)
    */
    public function containsKey($key){
        return array_key_exists($key, $this->the_array);
    }
    
    
    /**
    * Maps the specified key to the specified value in this hashtable. Neither the key nor the value
    * can be null. The value can be retrieved by calling the get method with a key that is 
    * equal to the original key. 
    *
    * @return the previous value of the specified key in this hashtable, or null if it did not have one. 
    */
    public function isEmpty() {
       return (0 === count($this->the_array));
    }

    /**
    * Adds the given key/value pair to this properties.  This calls
    * the hashtable method put.
    *
    * @param key the key for this property
    * @param value the value for this property
    * @return The old value for the given key
    * @see #getProperty(String)
    * @since 1.2
    */
    public function setProperty($key, $value) {
        $oldValue = $this->the_array[$key];
        $this->the_array[$key] = $value;
        return $oldValue;
    }
    
    public function put($key, $value) {
       return $this->setProperty($key, $value);
    }

    /**
    * Gets the property with the specified key in this property list.
    * If the key is not found, the default property list is searched.
    * If the property is not found in the default, null is returned.
    *
    * @param key The key for this property
    * @return the value for the given key, or null if not found
    * @throws ClassCastException if this property contains any key or
    *         value that isn't a string
    * @see #defaults
    * @see #setProperty(String, String)
    * @see #getProperty(String, String)
    */
    public function getProperty() {
        $num_args=func_num_args();
        switch ($num_args) {
            case 1: {
                $key = func_get_arg(0); 
                return getProperty($key,NULL);
                break;
            }
            case 2: {
                $prop = $this;
                $key = func_get_arg(0); 
                $value = func_get_arg(1); 
                do {
                    $thevalue = $prop->the_array[key];
                    if ($thevalue != null)
                        return $thevalue;
                    $prop = $prop->defaults;
                } while ($prop != NULL);
                break;
            }

        }

    }

    public function remove($key) {
        if ($key_exists($key, $the_array) == TRUE) {
            $theOldKey = $this->the_array[$key];
            unset($this->the_array[$key]);
            return $theOldKey;
        }
    }

    public function size() {
        return count($this->the_array);
    }

    public function load($inStream) {
    // The spec says that the file must be encoded using ISO-8859-1.
    //BufferedReader reader =
      //new BufferedReader(new InputStreamReader(inStream, "ISO-8859-1"));
        $line = NULL;

        while (!feof($inStrean)) {
            $line = fgets($inStream, 4096);
            $c = 0;
            $pos = 0;
        // Leading whitespaces must be deleted first.
        while ($pos < strlen($line)
               && ($c = $line[$pos]) == ' ')
            $pos++;

        // If empty line or begins with a comment character, skip this line.
        if ((strlen($line) - $pos) == 0 || $line[$pos] == '#' || $line[$pos] == '!')
          continue;

        // The characters up to the next Whitespace, ':', or '='
        // describe the key.  But look for escape sequences.
        $key = "";
        while ($pos < strlen($line)
               && ! ($c = $line[$pos++])==' '
               && $c != '=' && $c != ':') {
            if ($c == '\\') {
                if ($pos == strlen($line)) {
                    // The line continues on the next line.
                    $line = fgets($inStream, 4096);
                    $pos = 0;
                    while ($pos < strlen($line)
                           && ($c = $line[$pos]) == ' ')
                        $pos++;
                }
                else {
                    $c = $line[$pos++];
                    switch ($c) {
                        case 'n':
                            $key .= '\n';
                            break;
                        case 't':
                            $key .= '\t';
                            break;
                        case 'r':
                            $key .= '\r';
                            break;
                        case 'u':
                            if ($pos + 4 <= strlen($line)) {
                                $uni = intval(substr($line, $pos, $pos + 4), 16);
                                $key .= $uni;
                                $pos += 4;
                            }        // else throw exception?
                            break;
                      default:
                            $key .= $c;
                            break;
                      }
                  }
            }
            else
                $key .= $c;
            }

        $isDelim = ($c == ':' || $c == '=');
        while ($pos < strlen($line)
               && ($c = $line[$pos]) == ' ')
          $pos++;

        if ($isDelim == FALSE && ($c == ':' || $c == '=')) {
            $pos++;
            while ($pos < strlen($line)
                   && ($c = $line[$pos]) == ' ')
              $pos++;
        }

        $element = "";
        while ($pos < strlen($line)) {
            $c = $line[$pos++];
            if ($c == '\\') {
                if ($pos == strlen($line)) {
                    // The line continues on the next line.
                    $line = fgets($inStream, 4096);

            // We might have seen a backslash at the end of
            // the file.  The JDK ignores the backslash in
            // this case, so we follow for compatibility.
            if ($line == NULL || feof($inStrean) == TRUE)
                break;

                    $pos = 0;
                    while ($pos < strlen($line)
                           && ($c = $line[$pos]) == ' ')
                        $pos++;

                }
                else {
                    $c = $line[$pos++];
                    switch ($c) {
                        case 'n':
                            $element .= '\n';
                            break;
                      case 't':
                            $element .= '\t';
                            break;
                      case 'r':
                            $element .= '\r';
                            break;
                      case 'u':
                            if ($pos + 4 <= strlen(line)) {
                            $uni = intval(substr($line, $pos, $pos + 4), 16);
                            $element .= $uni;
                            $pos += 4;
                            }        // else throw exception?
                            break;
                      default:
                            $element .= $c;
                            break;
                      }
                  }
            }
            else
                $element .= $c;
          }
          $this->the_array[$key] = $element;
      }
  }

}
?>