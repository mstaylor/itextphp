<?PHP
/*
 * $Id: Point2D.php,v 1.1 2005/11/09 15:38:24 mstaylor Exp $
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

/**
 * This class implements a generic point in 2D Cartesian space. The storage
 * representation is left up to the subclass. Point includes two useful
 * nested classes, for float and double storage respectively.
 *
 * @author Per Bothner (bothner@cygnus.com)
 * @author Eric Blake (ebb9@email.byu.edu)
 * @author Mills Staylor (updated to PHP framework only)
 * @since 1.2
 * @status updated to 1.4
 */


public abstract class Point2D
{
    /**
    * The default constructor.
    *
    * @see java.awt.Point
    * @see Point2D.Float
    * @see Point2D.Double
    */
    protected __construct Point2D()
    {
    }




    /**
    * Get the X coordinate, in double precision.
    *
    * @return the x coordinate
    */
    abstract public function getX();

    /**
    * Get the Y coordinate, in double precision.
    *
    * @return the y coordinate
    */
    abstract public function getY();

    public function setLocation()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Point2D)
                    setLocation1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_double($arg1) == TRUE && is_double($arg2) == TRUE)
                    setLocation2args($arg1, $arg2);
                break;
            }
        }
    }



    /**
    * Set the location of this point to the new coordinates. There may be a
    * loss of precision.
    *
    * @param x the new x coordinate
    * @param y the new y coordinate
    */

    abstract private function setLocation2args($x, $y);

    /**
    * Set the location of this point to the new coordinates. There may be a
    * loss of precision.
    *
    * @param p the point to copy
    * @throws NullPointerException if p is null
    */
    private function setLocation1arg(Point2D $p)
    {
        setLocation($p->getX(), $p->getY());
    }

    public static function distanceSq()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if ($arg1 instanceof Point2D && $arg2 instanceof Point2D)
                    return Point2D::distanceSq2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if ($arg1 instanceof Point2D && is_double($arg2) == TRUE && is_double($arg3) == TRUE)
                    return Point2D::distanceSq3args($arg1, $arg2, $arg3);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (is_double($arg1) == TRUE && is_double($arg2) == TRUE && is_double($arg3) == TRUE && is_double($arg4) == TRUE)
                    return Point2D::distanceSq4args($arg1, $arg2, $arg3, $arg4);
                break;
            }
        }
    }

    /**
    * Return the square of the distance between two points.
    *
    * @param x1 the x coordinate of point 1
    * @param y1 the y coordinate of point 1
    * @param x2 the x coordinate of point 2
    * @param y2 the y coordinate of point 2
    * @return (x2 - x1)^2 + (y2 - y1)^2
    */
    private static function distanceSq4args($x1, $y1, $x2, $y2)
    {
        $x2 -= $x1;
        $y2 -= $y1;
        return $x2 * $x2 + $y2 * $y2;
    }


    /**
    * Return the square of the distance from this point to the given one.
    *
    * @param x the x coordinate of the other point
    * @param y the y coordinate of the other point
    * @return the square of the distance
    */
    private static function distanceSq3args(Point2D $a, $x, $y)
    {
        return Point2D::distanceSq($a->getX(), $x, $a->getY(), $y);
    }

    /**
    * Return the square of the distance from this point to the given one.
    *
    * @param p the other point
    * @return the square of the distance
    * @throws NullPointerException if p is null
    */
    private static function distanceSq2args(Point2D $p, Point2D $p2)
    {
        return Point2D::distanceSq($p2->getX(), $p->getX(), $p2->getY(), $p->getY());
    }

    public static function distance()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if ($arg1 instanceof Point2D && $arg2 instanceof Point2D)
                    return Point2D::distance2args($arg1, $arg2);
                break;
            }
            case 3:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                if ($arg1 instanceof Point2D && is_double($arg2) == TRUE && is_double($arg3) == TRUE)
                    return Point2D::distance3args($arg1, $arg2, $arg3);
                break;
            }
            case 4:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                if (is_double($arg1) == TRUE && is_double($arg2) == TRUE && is_double($arg3) == TRUE && is_double($arg4) == TRUE)
                    return Point2D::distance4args($arg1, $arg2, $arg3, $arg4);
                break;
            }
        }
    }

    /**
    * Return the distance between two points.
    *
    * @param x1 the x coordinate of point 1
    * @param y1 the y coordinate of point 1
    * @param x2 the x coordinate of point 2
    * @param y2 the y coordinate of point 2
    * @return the distance from (x1,y1) to (x2,y2)
    */
    private static function distance4args($x1, $y1, $x2, $y2)
    {
        return sqrt(Point2D::distanceSq($x1, $y1, $x2, $y2));
    }

    /**
    * Return the distance from this point to the given one.
    *
    * @param x the x coordinate of the other point
    * @param y the y coordinate of the other point
    * @return the distance
    */
    private static function distance3args(Point2D $a, $x, $y)
    {
        return Point2D::distance($a->getX(), $x, $a->getY(), $y);
    }

    /**
    * Return the distance from this point to the given one.
    *
    * @param p the other point
    * @return the distance
    * @throws NullPointerException if p is null
    */
    private static function distance2args(Point2D $p, Point2D $p2)
    {
        return Point2D::distance($p2->getX(), $p->getX(), $p2->getY(), $p->getY());
    }

    /**
    * Create a new point of the same run-time type with the same contents as
    * this one.
    *
    * @return the clone
    */
    public function __clone()
    {
        return parent::__clone();
    }

    /**
    * Return the hashcode for this point. The formula is not documented, but
    * appears to be the same as:
    * <pre>
    * long l = Double.doubleToLongBits(getY());
    * l = l * 31 ^ Double.doubleToLongBits(getX());
    * return (int) ((l >> 32) ^ l);
    * </pre>
    *
    * @return the hashcode
    */
    public function hashCode()
    {
        // Talk about a fun time reverse engineering this one!
        // this may need some work...
        $l = getY();
        $l = $l * 31 ^ getX();
        return (integer) (($l >> 32) ^ $l);
    }

    /**
    * Compares two points for equality. This returns true if they have the
    * same coordinates.
    *
    * @param o the point to compare
    * @return true if it is equal
    */
    public function equals($o)
    {
        if (! ($o instanceof Point2D))
            return FALSE;
        $p = $o;
        return getX() == $p->getX() && getY() == $p->getY();
    }




} // class Point2D

/**
* This class defines a point in <code>double</code> precision.
*
* @author Eric Blake (ebb9@email.byu.edu)
* @since 1.2
* @status updated to 1.4
*/
class Double extends Point2D
{
    /** The X coordinate. */
    public $x = 0.0;

    /** The Y coordinate. */
    public $y = 0.0;


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
                construct2args($arg1, $arg2);
                break;
            }
        }
    }

    /**
    * Create a new point at (0,0).
    */
    private function construct0args()
    {
    }

    /**
    * Create a new point at (x,y).
    *
    * @param x the x coordinate
    * @param y the y coordinate
    */
    private function construct2args($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
    * Return the x coordinate.
    *
    * @return the x coordinate
    */
    public function getX()
    {
        return $x;
    }

    /**
    * Return the y coordinate.
    *
    * @return the y coordinate
    */
    public function getY()
    {
        return $y;
    }

    /**
    * Sets the location of this point.
    *
    * @param x the new x coordinate
    * @param y the new y coordinate
    */
    private function setLocation2args($x, $y)
    {
      $this->x = $x;
      $this->y = $y;
    }

    /**
    * Returns a string representation of this object. The format is:
    * <code>"Point2D.Double[" + x + ", " + y + ']'</code>.
    *
    * @return a string representation of this object
    */
    public function toString()
    {
      return "Point2D.Double[" . $x . ", " . $y . ']';
    }
} // class Double



/**
* This class defines a point in <code>float</code> precision.
*
* @author Eric Blake (ebb9@email.byu.edu)
* @since 1.2
* @status updated to 1.4
*/
class Float extends Point2D
{
    /** The X coordinate. */
    public $x = 0.0;

    /** The Y coordinate. */
    public $y = 0.0;


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
                construct2args($arg1, $arg2);
                break;
            }
        }
    }

    /**
    * Create a new point at (0,0).
    */
    private function construct0args()
    {
    }

    /**
    * Create a new point at (x,y).
    *
    * @param x the x coordinate
    * @param y the y coordinate
    */
    private function construct2args($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
    * Return the x coordinate.
    *
    * @return the x coordinate
    */
    public function getX()
    {
        return $x;
    }

    /**
    * Return the y coordinate.
    *
    * @return the y coordinate
    */
    public function getY()
    {
        return $y;
    }

    /**
    * Sets the location of this point.
    *
    * @param x the new x coordinate
    * @param y the new y coordinate
    */
    private function setLocation2args($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
    * Returns a string representation of this object. The format is:
    * <code>"Point2D.Float[" + x + ", " + y + ']'</code>.
    *
    * @return a string representation of this object
    */
    public function toString()
    {
      return "Point2D.Float[" . $x . ", " . $y . ']';
    }
  } // class Float





?>