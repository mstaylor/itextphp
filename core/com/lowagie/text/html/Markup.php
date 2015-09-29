<?php
namespace com\lowagie\text\html;

require_once dirname(__FILE__) . "/../../../../php/lang/TypeHint.php";
require_once dirname(__FILE__) . "/../../../../php/lang/StringTokenizer.php";
require_once dirname(__FILE__) . "/../../../../php/util/Properties.php";
require_once dirname(__FILE__) . "/../../../../php/util/StringHelpers.php";

use php\util\Properties as Properties;
use php\lang\StringTokenizer as StringTokenizer;
use php\util\StringHelpers as StringHelpers;

class Markup {

    // iText specific
    
    /** the key for any tag */
    const ITEXT_TAG = "tag";
    
    // HTML tags
    
    /** the markup for the body part of a file */
    const HTML_TAG_BODY = "body";
    /** The DIV tag. */
    const HTML_TAG_DIV = "div";
    /** This is a possible HTML-tag. */
    const HTML_TAG_LINK = "link";
    /** The SPAN tag. */
    const HTML_TAG_SPAN = "span";
    
    // HTML attributes
    
    /** the height attribute. */
    const HTML_ATTR_HEIGHT = "height";
    /** the hyperlink reference attribute. */
    const HTML_ATTR_HREF = "href";
    /** This is a possible HTML attribute for the LINK tag. */
    const HTML_ATTR_REL = "rel";
    /** This is used for inline css style information */
    const HTML_ATTR_STYLE = "style";
    /** This is a possible HTML attribute for the LINK tag. */
    const HTML_ATTR_TYPE = "type";
    /** This is a possible HTML attribute. */
    const HTML_ATTR_STYLESHEET = "stylesheet";
    /** the width attribute. */
    const HTML_ATTR_WIDTH = "width";
    /** attribute for specifying externally defined CSS class */
    const HTML_ATTR_CSS_CLASS = "class";
    /** The ID attribute. */
    const HTML_ATTR_CSS_ID = "id";
    
    // HTML values
    
    /** This is a possible value for the language attribute (SCRIPT tag). */
    const HTML_VALUE_JAVASCRIPT = "text/javascript";
    /** This is a possible HTML attribute for the LINK tag. */
    const HTML_VALUE_CSS = "text/css";
    
    // CSS keys
    
    /** the CSS tag for background color */
    const CSS_KEY_BGCOLOR = "background-color";
    /** the CSS tag for text color */
    const CSS_KEY_COLOR = "color";
    /** CSS key that indicate the way something has to be displayed */
    const CSS_KEY_DISPLAY = "display";
    /** the CSS tag for the font family */
    const CSS_KEY_FONTFAMILY = "font-family";
    /** the CSS tag for the font size */
    const CSS_KEY_FONTSIZE = "font-size";
    /** the CSS tag for the font style */
    const CSS_KEY_FONTSTYLE = "font-style";
    /** the CSS tag for the font weight */
    const CSS_KEY_FONTWEIGHT = "font-weight";
    /** the CSS tag for text decorations */
    const CSS_KEY_LINEHEIGHT = "line-height";
    /** the CSS tag for the margin of an object */
    const CSS_KEY_MARGIN = "margin";
    /** the CSS tag for the left margin of an object */
    const CSS_KEY_MARGINLEFT = "margin-left";
    /** the CSS tag for the right margin of an object */
    const CSS_KEY_MARGINRIGHT = "margin-right";
    /** the CSS tag for the top margin of an object */
    const CSS_KEY_MARGINTOP = "margin-top";
    /** the CSS tag for the bottom margin of an object */
    const CSS_KEY_MARGINBOTTOM = "margin-bottom";
    /** the CSS tag for the padding of an object */
    const CSS_KEY_PADDING = "padding";
    /** the CSS tag for the left padding of an object */
    const CSS_KEY_PADDINGLEFT = "padding-left";
    /** the CSS tag for the right padding of an object */
    const CSS_KEY_PADDINGRIGHT = "padding-right";
    /** the CSS tag for the top padding of an object */
    const CSS_KEY_PADDINGTOP = "padding-top";
    /** the CSS tag for the bottom padding of an object */
    const CSS_KEY_PADDINGBOTTOM = "padding-bottom";
    /** the CSS tag for the border color of an object */
    const CSS_KEY_BORDERCOLOR = "border-color";
    /** the CSS tag for the border width of an object */
    const CSS_KEY_BORDERWIDTH = "border-width";
    /** the CSS tag for the left border width of an object */
    const CSS_KEY_BORDERWIDTHLEFT = "border-left-width";
    /** the CSS tag for the right border width of an object */
    const CSS_KEY_BORDERWIDTHRIGHT = "border-right-width";
    /** the CSS tag for the top border width of an object */
    const CSS_KEY_BORDERWIDTHTOP = "border-top-width";
    /** the CSS tag for the bottom border width of an object */
    const CSS_KEY_BORDERWIDTHBOTTOM = "border-bottom-width";
    /** the CSS tag for adding a page break (after) when the document is printed */
    const CSS_KEY_PAGE_BREAK_AFTER = "page-break-after";
    /** the CSS tag for adding a page break (before) when the document is printed */
    const CSS_KEY_PAGE_BREAK_BEFORE = "page-break-before";
    /** the CSS tag for the horizontal alignment of an object */
    const CSS_KEY_TEXTALIGN = "text-align";
    /** the CSS tag for text decorations */
    const CSS_KEY_TEXTDECORATION = "text-decoration";
    /** the CSS tag for the vertical alignment of an object */
    const CSS_KEY_VERTICALALIGN = "vertical-align";
    /** the CSS tag for the visibility of objects */
    const CSS_KEY_VISIBILITY = "visibility";
    
    // CSS values
    
    /** value for the CSS tag for adding a page break when the document is printed */
    const CSS_VALUE_ALWAYS = "always";
    /** A possible value for the DISPLAY key */
    const CSS_VALUE_BLOCK = "block";
    /** a CSS value for text font weight */
    const CSS_VALUE_BOLD = "bold";
    /** the value if you want to hide objects. */
    const CSS_VALUE_HIDDEN = "hidden";
    /** A possible value for the DISPLAY key */
    const CSS_VALUE_INLINE = "inline";
    /** a CSS value for text font style */
    const CSS_VALUE_ITALIC = "italic";
    /** a CSS value for text decoration */
    const CSS_VALUE_LINETHROUGH = "line-through";
    /** A possible value for the DISPLAY key */
    const CSS_VALUE_LISTITEM = "list-item";
    /** a CSS value */
    const CSS_VALUE_NONE = "none";
    /** a CSS value */
    const CSS_VALUE_NORMAL = "normal";
    /** a CSS value for text font style */
    const CSS_VALUE_OBLIQUE = "oblique";
    /** A possible value for the DISPLAY key */
    const CSS_VALUE_TABLE = "table";
    /** A possible value for the DISPLAY key */
    const CSS_VALUE_TABLEROW = "table-row";
    /** A possible value for the DISPLAY key */
    const CSS_VALUE_TABLECELL = "table-cell";
    /** the CSS value for a horizontal alignment of an object */
    const CSS_VALUE_TEXTALIGNLEFT = "left";
    /** the CSS value for a horizontal alignment of an object */
    const CSS_VALUE_TEXTALIGNRIGHT = "right";
    /** the CSS value for a horizontal alignment of an object */
    const CSS_VALUE_TEXTALIGNCENTER = "center";
    /** the CSS value for a horizontal alignment of an object */
    const CSS_VALUE_TEXTALIGNJUSTIFY = "justify";
    /** a CSS value for text decoration */
    const CSS_VALUE_UNDERLINE = "underline";
    
    
    
    /**
    * Parses a length.
    * 
    * @param string
    *            a length in the form of an optional + or -, followed by a
    *            number and a unit.
    * @return a float
    */
    public static function parseLength(string $string) {
        $pos = 0;
        $length = strlen($string);
        $ok = TRUE;
        while($ok && $pos < $length) {
            switch($string[$pos]) {
                case '+':
                case '-':
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                case '.':
                    $pos++;
                    break;
                default:
                    $ok = FALSE;
            }
        }
        if ($pos == 0)
            return (float)0;
        if ($pos == $length)
            return floatval($string);
        $f = floatval(substr($str, 0, $pos));
        $string = substr($str, $pos);
        //inches
        if (StringHelpers::beginsWith($string, "in")) {
            return (float)($f * 72);
        }
        // centimeters
        if (StringHelpers::beginsWith($string, "cm")) {
            return (float)(($f/2.54) * 72);
        }
        // millimeters
        if (StringHelpers::beginsWith($string, "mm")) {
            return (float)(($f/25.4) * 72);
        }
        // picas
        if (StringHelpers::beginsWith($string, "pc")) {
            return (float)($f * 12);
        }
        return $f;
    }
    
    /**
    * Converts a <CODE>Color</CODE> into a HTML representation of this <CODE>
    * Color</CODE>.
    * 
    * @param s
    *            the <CODE>Color</CODE> that has to be converted.
    * @return the HTML representation of this <COLOR>Color </COLOR>
    */

    public static function decodeColor(string $s) {
        if ($s == NULL)
            return NULL;
        $s = trim(strtolower($s));
        
    }
    
    /**
    * This method parses a String with attributes and returns a Properties
    * object.
    * 
    * @param string
    *            a String of this form: 'key1="value1"; key2="value2";...
    *            keyN="valueN" '
    * @return a Properties object
    */
    public static function parseAttributes(string $string) {
        $result = new Properties();
        if ($string == NULL)
            return $result;
        $keyValuePairs = new StringTokenizer($string, ";");
        $keyValuePair = NULL;//StringTokenizer
        $key = NULL;//string
        $value = NULL;//string
        while($keyValuePairs->hasMoreTokens()) {
            $keyValuePair = new StringTokenizer($keyValuePairs->nextToken(), ":");
            if ($keyValuePair->hasMoreTokens()) {
                $key = trim($keyValuePair->nextToken());
            } else {
                continue;
            }
            if ($keyValuePair->hasMoreTokens()) {
                $value = trim($keyValuePair->nextToken());
            } else {
                continue;
            }
            if (StringHelpers::beginsWith($value, "\"")) {
                $value = substr($value, 1);
            }
            if (StringHelpers::endsWith($value, "\"")) {
                $value = substr($value, strlen($value)-1);
            }
            $result->setProperty(strtolower($key), $value);
        }
        return $result;
    }
}
?>