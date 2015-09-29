<?PHP
/*
 * $Id: Point.php,v 1.1 2005/11/09 15:38:08 mstaylor Exp $
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


require_once("geom/Point2D.php");

/**
* This class represents a point on the screen using cartesian coordinates.
* Remember that in screen coordinates, increasing x values go from left to
* right, and increasing y values go from top to bottom.
*
* <p>There are some public fields; if you mess with them in an inconsistent
* manner, it is your own fault when you get invalid results. Also, this
* class is not threadsafe.
*
* @author Per Bothner (bothner@cygnus.com)
* @author Aaron M. Renn (arenn@urbanophile.com)
* @author Eric Blake (ebb9@email.byu.edu)
* @since 1.0
* @status updated to 1.4
*/


class Point extends Point2D
{

    /**
    * The x coordinate.
    *
    * @see #getLocation()
    * @see #move(int, int)
    * @serial the X coordinate of the point
    */
    public $x = 0;

    /**
    * The y coordinate.
    *
    * @see #getLocation()
    * @see #move(int, int)
    * @serial The Y coordinate of the point
    */
    public $y = 0;

    public function __construct()
    {
        $num_args=func_num_args();
        switch ($num_args)
        {
            case 0;
            {
                construct0args();
                break;
            }
            case 1:
            {
                $arg1 = func_get_arg(0);
                if ($arg1 instanceof Point)
                    construct1arg($arg1);
                break;
            }
            case 2:
            {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                if (is_integer($arg1) == TRUE && is_integer($arg2) == TRUE)
                    construct2args($arg1, $arg2);
                break;
            }
        }
    }


    /**
    * Initializes a new instance of <code>Point</code> representing the
    * coordiates (0,0).
    *
    * @since 1.1
    */
    private function construct0args()
    {
    }

    /**
    * Initializes a new instance of <code>Point</code> with coordinates
    * identical to the coordinates of the specified points.
    *
    * @param p the point to copy the coordinates from
    * @throws NullPointerException if p is null
    */
    private function construct1arg(Point $p)
    {
        $x = $p->x;
        $y = $p->y;
    }

    /**
    * Initializes a new instance of <code>Point</code> with the specified
    * coordinates.
    *
    * @param x the X coordinate
    * @param y the Y coordinate
    */
    private function construct2args($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
    * Get the x coordinate.
    *
    * @return the value of x, as a double
    */
    public function getX()
    {
        return $x;
    }

    /**
    * Get the y coordinate.
    *
    * @return the value of y, as a double
    */
    public function getY()
    {
        return $y;
    }

    /**
    * Returns the location of this point. A pretty useless method, as this
    * is already a point.
    *
    * @return a copy of this point
    * @see #setLocation(Point)
    * @since 1.1
    */
    public function getLocation()
    {
    return new Point($x, $y);
    }

    /**
    * Sets this object's coordinates to match those of the specified point.
    *
    * @param p the point to copy the coordinates from
    * @throws NullPointerException if p is null
    * @since 1.1
    */
    private function setLocation(Point $p)
    {
        $x = $p->x;
        $y = $p->y;
    }

    /**
    * Sets this object's coordinates to the specified values.  This method
    * is identical to the <code>move()</code> method.
    *
    * @param x the new X coordinate
    * @param y the new Y coordinate
    */
    abstract private function setLocation($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }


    /**
    * Sets this object's coordinates to the specified values.  This method
    * is identical to the <code>setLocation(int, int)</code> method.
    *
    * @param x the new X coordinate
    * @param y the new Y coordinate
    */
    public function move($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
    * Changes the coordinates of this point such that the specified
    * <code>dx</code> parameter is added to the existing X coordinate and
    * <code>dy</code> is added to the existing Y coordinate.
    *
    * @param dx the amount to add to the X coordinate
    * @param dy the amount to add to the Y coordinate
    */
    public function translate($dx, $dy)
    {
        $x += $dx;
        $y += $dy;
    }

    /**
    * Tests whether or not this object is equal to the specified object.
    * This will be true if and only if the specified object is an instance
    * of Point2D and has the same X and Y coordinates.
    *
    * @param obj the object to test against for equality
    * @return true if the specified object is equal
    */
    public function equals($obj)
    {
        if (! ($obj instanceof Point2D))
            return FALSE;
        $p = $obj;
        return $x == $p->getX() && $y == $p->getY();
    }

    /**
    * Returns a string representation of this object. The format is:
    * <code>getClass().getName() + "[x=" + x + ",y=" + y + ']'</code>.
    *
    * @return a string representation of this object
    */
    public function toString()
    {
        return this->__toString() . "[x=" . $x . ",y=" . $y . ']';
    }
} // class Point



?>