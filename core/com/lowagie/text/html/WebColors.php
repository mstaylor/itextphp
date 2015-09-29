<?php
namespace com\lowagie\text\html;

require_once dirname(__FILE__) . "/../../../../php/lang/TypeHint.php";
require_once dirname(__FILE__) . "/../../../../php/awt/Color.php";
require_once dirname(__FILE__) . "/../../../../php/lang/IllegalArgumentException.php";
require_once dirname(__FILE__) . "/../../../../php/util/StringHelpers.php";
require_once dirname(__FILE__) . "/../../../../php/lang/StringTokenizer.php";

use php\awt\Color as Color;
use php\lang\IllegalArgumentException as IllegalArgumentException;
use php\util\StringHelpers as StringHelpers;
use php\lang\StringTokenizer as StringTokenizer;

/**
* This class is a HashMap that contains the names of colors as a key and the
* corresponding Color as value. (Source: Wikipedia
* http://en.wikipedia.org/wiki/Web_colors )
* 
* @author blowagie (adapted to PHP by Bud Staylor)
*/
class WebColors {
    /** Array containing all the names and corresponding color values. */
    public static $NAMES = NULL;
    public static $initialized = FALSE;
    
    public static function initializeStatics()
    {
        if(WebColors::$initialized == FALSE)
        {
            WebColors::$Names = new WebColors();
            WebColors::$Names["aliceblue"] = array(0xf0, 0xf8, 0xff, 0x00);
            WebColors::$Names["antiquewhite"] = array(0xfa, 0xeb, 0xd7, 0x00);
            WebColors::$Names["aqua"] = array(0x00, 0xff, 0xff, 0x00);
            WebColors::$Names["aquamarine"] = array(0x7f, 0xff, 0xd4, 0x00);
            WebColors::$Names["azure"] = array(0xf0, 0xff, 0xff, 0x00);
            WebColors::$Names["beige"] = array(0xf5, 0xf5, 0xdc, 0x00);
            WebColors::$Names["bisque"] = array(0xff, 0xe4, 0xc4, 0x00);
            WebColors::$Names["black"] = array(0x00, 0x00, 0x00, 0x00);
            WebColors::$Names["blanchedalmond"] = array(0xff, 0xeb, 0xcd, 0x00);
            WebColors::$Names["blue"] = array(0x00, 0x00, 0xff, 0x00);
            WebColors::$Names["blueviolet"] = array(0x8a, 0x2b, 0xe2, 0x00);
            WebColors::$Names["brown"] = array(0xa5, 0x2a, 0x2a, 0x00);
            WebColors::$Names["burlywood"] = array(0xde, 0xb8, 0x87, 0x00);
            WebColors::$Names["cadetblue"] = array(0x5f, 0x9e, 0xa0, 0x00);
            WebColors::$Names["chartreuse"] = array(0x7f, 0xff, 0x00, 0x00);
            WebColors::$Names["chocolate"] = array(0xd2, 0x69, 0x1e, 0x00);
            WebColors::$Names["coral"] = array(0xff, 0x7f, 0x50, 0x00);
            WebColors::$Names["cornflowerblue"] = array(0x64, 0x95, 0xed, 0x00);
            WebColors::$Names["cornsilk"] = array(0xff, 0xf8, 0xdc, 0x00);
            WebColors::$Names["crimson"] = array(0xdc, 0x14, 0x3c, 0x00);
            WebColors::$Names["cyan"] = array(0x00, 0xff, 0xff, 0x00);
            WebColors::$Names["darkblue"] = array(0x00, 0x00, 0x8b, 0x00);
            WebColors::$Names["darkcyan"] = array(0x00, 0x8b, 0x8b, 0x00);
            WebColors::$Names["darkgoldenrod"] = array(0xb8, 0x86, 0x0b, 0x00);
            WebColors::$Names["darkgray"] = array(0xa9, 0xa9, 0xa9, 0x00);
            WebColors::$Names["darkgreen"] = array(0x00, 0x64, 0x00, 0x00);
            WebColors::$Names["darkkhaki"] = array(0xbd, 0xb7, 0x6b, 0x00);
            WebColors::$Names["darkmagenta"] = array(0x8b, 0x00, 0x8b, 0x00);
            WebColors::$Names["darkolivegreen"] = array(0x55, 0x6b, 0x2f, 0x00);
            WebColors::$Names["darkorange"] = array(0xff, 0x8c, 0x00, 0x00);
            WebColors::$Names["darkorange"] = array(0xff, 0x8c, 0x00, 0x00);
            WebColors::$Names["darkorchid"] = array(0x99, 0x32, 0xcc, 0x00);
            WebColors::$Names["darkred"] = array(0x8b, 0x00, 0x00, 0x00);
            WebColors::$Names["darksalmon"] = array(0xe9, 0x96, 0x7a, 0x00);
            WebColors::$Names["darkseagreen"] = array(0x8f, 0xbc, 0x8f, 0x00);
            WebColors::$Names["darkslateblue"] = array(0x48, 0x3d, 0x8b, 0x00);
            WebColors::$Names["darkslategray"] = array(0x2f, 0x4f, 0x4f, 0x00);
            WebColors::$Names["darkturquoise"] = array(0x00, 0xce, 0xd1, 0x00);
            WebColors::$Names["darkviolet"] = array(0x94, 0x00, 0xd3, 0x00);
            WebColors::$Names["deeppink"] = array(0xff, 0x14, 0x93, 0x00);
            WebColors::$Names["deepskyblue"] = array(0x00, 0xbf, 0xff, 0x00);
            WebColors::$Names["dimgray"] = array(0x69, 0x69, 0x69, 0x00);
            WebColors::$Names["dodgerblue"] = array(0x1e, 0x90, 0xff, 0x00);
            WebColors::$Names["firebrick"] = array(0xb2, 0x22, 0x22, 0x00);
            WebColors::$Names["floralwhite"] = array(0xff, 0xfa, 0xf0, 0x00);
            WebColors::$Names["forestgreen"] = array(0x22, 0x8b, 0x22, 0x00);
            WebColors::$Names["fuchsia"] = array(0xff, 0x00, 0xff, 0x00);
            WebColors::$Names["gainsboro"] = array(0xdc, 0xdc, 0xdc, 0x00);
            WebColors::$Names["ghostwhite"] = array(0xf8, 0xf8, 0xff, 0x00);
            WebColors::$Names["gold"] = array(0xff, 0xd7, 0x00, 0x00);
            WebColors::$Names["goldenrod"] = array(0xda, 0xa5, 0x20, 0x00);
            WebColors::$Names["gray"] = array(0x80, 0x80, 0x80, 0x00);
            WebColors::$Names["green"] = array(0x00, 0x80, 0x00, 0x00);
            WebColors::$Names["greenyellow"] = array(0xad, 0xff, 0x2f, 0x00);
            WebColors::$Names["honeydew"] = array(0xf0, 0xff, 0xf0, 0x00);
            WebColors::$Names["hotpink"] = array(0xff, 0x69, 0xb4, 0x00);
            WebColors::$Names["indianred"] = array(0xcd, 0x5c, 0x5c, 0x00);
            WebColors::$Names["indigo"] = array(0x4b, 0x00, 0x82, 0x00);
            WebColors::$Names["ivory"] = array(0xff, 0xff, 0xf0, 0x00);
            WebColors::$Names["khaki"] = array(0xf0, 0xe6, 0x8c, 0x00);
            WebColors::$Names["lavender"] = array(0xe6, 0xe6, 0xfa, 0x00);
            WebColors::$Names["lavenderblush"] = array(0xff, 0xf0, 0xf5, 0x00);
            WebColors::$Names["lawngreen"] = array(0x7c, 0xfc, 0x00, 0x00);
            WebColors::$Names["lemonchiffon"] = array(0xff, 0xfa, 0xcd, 0x00);
            WebColors::$Names["lightblue"] = array(0xad, 0xd8, 0xe6, 0x00);
            WebColors::$Names["lightcoral"] = array(0xf0, 0x80, 0x80, 0x00);
            WebColors::$Names["lightcyan"] = array(0xe0, 0xff, 0xff, 0x00);
            WebColors::$Names["lightgoldenrodyellow"] = array(0xfa, 0xfa, 0xd2, 0x00);
            WebColors::$Names["lightgreen"] = array(0x90, 0xee, 0x90, 0x00);
            WebColors::$Names["lightgrey"] = array(0xd3, 0xd3, 0xd3, 0x00);
            WebColors::$Names["lightpink"] = array(0xff, 0xb6, 0xc1, 0x00);
            WebColors::$Names["lightsalmon"] = array(0xff, 0xa0, 0x7a, 0x00);
            WebColors::$Names["lightseagreen"] = array(0x20, 0xb2, 0xaa, 0x00);
            WebColors::$Names["lightskyblue"] = array(0x87, 0xce, 0xfa, 0x00);
            WebColors::$Names["lightslategray"] = array(0x77, 0x88, 0x99, 0x00);
            WebColors::$Names["lightsteelblue"] = array(0xb0, 0xc4, 0xde, 0x00);
            WebColors::$Names["lightyellow"] = array(0xff, 0xff, 0xe0, 0x00);
            WebColors::$Names["lime"] = array(0x00, 0xff, 0x00, 0x00);
            WebColors::$Names["limegreen"] = array(0x32, 0xcd, 0x32, 0x00);
            WebColors::$Names["linen"] = array(0xfa, 0xf0, 0xe6, 0x00);
            WebColors::$Names["magenta"] = array(0xff, 0x00, 0xff, 0x00);
            WebColors::$Names["maroon"] = array(0x80, 0x00, 0x00, 0x00);
            WebColors::$Names["mediumaquamarine"] = array(0x66, 0xcd, 0xaa, 0x00);
            WebColors::$Names["mediumblue"] = array(0x00, 0x00, 0xcd, 0x00);
            WebColors::$Names["mediumorchid"] = array(0xba, 0x55, 0xd3, 0x00);
            WebColors::$Names["mediumpurple"] = array(0x93, 0x70, 0xdb, 0x00);
            WebColors::$Names["mediumseagreen"] = array(0x3c, 0xb3, 0x71, 0x00);
            WebColors::$Names["mediumslateblue"] = array(0x7b, 0x68, 0xee, 0x00);
            WebColors::$Names["mediumspringgreen"] = array(0x00, 0xfa, 0x9a, 0x00);
            WebColors::$Names["mediumturquoise"] = array(0x48, 0xd1, 0xcc, 0x00);
            WebColors::$Names["mediumvioletred"] = array(0xc7, 0x15, 0x85, 0x00);
            WebColors::$Names["midnightblue"] = array(0x19, 0x19, 0x70, 0x00);
            WebColors::$Names["mintcream"] = array(0xf5, 0xff, 0xfa, 0x000);
            WebColors::$Names["mistyrose"] = array(0xff, 0xe4, 0xe1, 0x00);
            WebColors::$Names["moccasin"] = array(0xff, 0xe4, 0xb5, 0x00);
            WebColors::$Names["navajowhite"] = array(0xff, 0xde, 0xad, 0x00);
            WebColors::$Names["navy"] = array(0x00, 0x00, 0x80, 0x00);
            WebColors::$Names["oldlace"] = array(0xfd, 0xf5, 0xe6, 0x00);
            WebColors::$Names["olive"] = array(0x80, 0x80, 0x00, 0x00);
            WebColors::$Names["olivedrab"] = array(0x6b, 0x8e, 0x23, 0x00);
            WebColors::$Names["orange"] = array(0xff, 0xa5, 0x00, 0x00);
            WebColors::$Names["orangered"] = array(0xff, 0x45, 0x00, 0x00);
            WebColors::$Names["orchid"] = array(0xda, 0x70, 0xd6, 0x00);
            WebColors::$Names["palegoldenrod"] = array(0xee, 0xe8, 0xaa, 0x00);
            WebColors::$Names["palegreen"] = array(0x98, 0xfb, 0x98, 0x00);
            WebColors::$Names["paleturquoise"] = array(0xaf, 0xee, 0xee, 0x00);
            WebColors::$Names["palevioletred"] = array(0xdb, 0x70, 0x93, 0x00);
            WebColors::$Names["papayawhip"] = array(0xff, 0xef, 0xd5, 0x00);
            WebColors::$Names["peachpuff"] = array(0xff, 0xda, 0xb9, 0x00);
            WebColors::$Names["peru"] = array(0xcd, 0x85, 0x3f, 0x00);
            WebColors::$Names["pink"] = array(0xff, 0xc0, 0xcb, 0x00);
            WebColors::$Names["plum"] = array(0xdd, 0xa0, 0xdd, 0x00);
            WebColors::$Names["powderblue"] = array(0xb0, 0xe0, 0xe6, 0x00);
            WebColors::$Names["purple"] = array(0x80, 0x00, 0x80, 0x00);
            WebColors::$Names["red"] = array(0xff, 0x00, 0x00, 0x00);
            WebColors::$Names["rosybrown"] = array(0xbc, 0x8f, 0x8f, 0x00);
            WebColors::$Names["royalblue"] = array(0x41, 0x69, 0xe1, 0x00);
            WebColors::$Names["saddlebrown"] = array(0x8b, 0x45, 0x13, 0x00);
            WebColors::$Names["salmon"] = array(0xfa, 0x80, 0x72, 0x00);
            WebColors::$Names["sandybrown"] = array(0xf4, 0xa4, 0x60, 0x00);
            WebColors::$Names["seagreen"] = array(0x2e, 0x8b, 0x57, 0x00);
            WebColors::$Names["seashell"] = array(0xff, 0xf5, 0xee, 0x00);
            WebColors::$Names["sienna"] = array(0xa0, 0x52, 0x2d, 0x00);
            WebColors::$Names["silver"] = array(0xc0, 0xc0, 0xc0, 0x00);
            WebColors::$Names["skyblue"] = array(0x87, 0xce, 0xeb, 0x00);
            WebColors::$Names["slateblue"] = array(0x6a, 0x5a, 0xcd, 0x00);
            WebColors::$Names["slategray"] = array(0x70, 0x80, 0x90, 0x00);
            WebColors::$Names["snow"] = array(0xff, 0xfa, 0xfa, 0x00);
            WebColors::$Names["springgreen"] = array(0x00, 0xff, 0x7f, 0x00);
            WebColors::$Names["steelblue"] = array(0x46, 0x82, 0xb4, 0x00);
            WebColors::$Names["tan"] = array(0xd2, 0xb4, 0x8c, 0x00);
            WebColors::$Names["transparent"] = array(0x00, 0x00, 0x00, 0xff);
            WebColors::$Names["teal"] = array(0x00, 0x80, 0x80, 0x00);
            WebColors::$Names["thistle"] = array(0xd8, 0xbf, 0xd8, 0x00);
            WebColors::$Names["tomato"] = array(0xff, 0x63, 0x47, 0x00);
            WebColors::$Names["turquoise"] = array(0x40, 0xe0, 0xd0, 0x00);
            WebColors::$Names["violet"] = array(0xee, 0x82, 0xee, 0x00);
            WebColors::$Names["wheat"] = array(0xf5, 0xde, 0xb3, 0x00);
            WebColors::$Names["white"] = array(0xff, 0xff, 0xff, 0x00);
            WebColors::$Names["whitesmoke"] = array(0xf5, 0xf5, 0xf5, 0x00);
            WebColors::$Names["yellow"] = array(0xff, 0xff, 0x00, 0x00);
            WebColors::$Names["yellowgreen"] = array(0x9, 0xacd, 0x32, 0x00);
            WebColors::$initialized = TRUE;
        }
    }
    
    /**
    * Gives you a Color based on a name.
    * 
    * @param name
    *            a name such as black, violet, cornflowerblue or #RGB or #RRGGBB
    *            or rgb(R,G,B)
    * @return the corresponding Color object
    * @throws IllegalArgumentException
    *             if the String isn't a know representation of a color.
    */
    public static function getRGBColor(string $name) {
        $c = array(0, 0, 0, 0);
        if (StringHelpers::beginsWith($name, "#")) {
            if (strlen($name) == 4) {
                $c[0] = intval(substr($name, 1, 2) * 16);
                $c[1] = intval(substr($name, 2, 3) * 16);
                $c[2] = intval(substr($name, 3) * 16);
                return new Color($c[0], $c[1]. $c[2], $c[3]);
            }
            if (strlen($name) == 7) {
                $c[0] = intval(substr($name, 1, 3) * 16);
                $c[1] = intval(substr($name, 3, 5) * 16);
                $c[2] = intval(substr($name, 5) * 16);
                return new Color($c[0], $c[1]. $c[2], $c[3]);
            }
            throw new IllegalArgumentException("Unknown color format. Must be #RGB or #RRGGBB");
        } else if (StringHelpers::beginsWith($name, "rgb(")) {
            $tok = new StringTokenizer(name, "rgb(), \t\r\n\f");
            for ($k = 0; $k < 3; ++$k) {
                $v = $tok->nextToken();
                if (StringHelpers::endsWith($v, "%")
                    $c[$k] = intval(substr($v, 0, strlen($v) - 1)) * 255 / 100;
                else
                    $c[$k] = intval($v);
                if ($c[$k] < 0)
                    $c[$k] = 0;
                else if ($c[$k] > 255)
                    $c[$k] = 255;
            }
            return new Color($c[0], $c[1], $c[2], $c[3]);
        }
        $name = strtolower($name);
        if (array_key_exists($name, WebColors::$NAMES)) {
            throw new IllegalArgumentException("Color '" . $name . "' not found.");
            $c = WebColors::$NAMES[$name];
            return new Color($c[0], $c[1], $c[2], $c[3]);
        }
    }
}

WebColors::initializeStatics();
?>