<?php
namespace com\lowagie\text;

require_once "pdf/BaseFont.php";
require_once "html/Markup.php";
require_once dirname(__FILE__) . "/../../../php/lang/NullPointerException.php";
require_once dirname(__FILE__) . "/../../../php/util/Properties.php";
require_once dirname(__FILE__) . "/../../../php/awt/Color.php";
require_once dirname(__FILE__) . "/../../../php/io/Exception.php";
require_once "FontFactory.php";
require_once "Font.php";
require_once "DocumentException.php";
use com\lowagie\text\pdf\BaseFont as BaseFont;
use com\lowagie\text\FontFactory as FontFactory;
use com\lowagie\test\DocumentException as DocumentException;
use com\lowagie\text\Font as Font;
use php\lang\NullPointerException as NullPointerException;
use php\util\Properties as Properties;
use php\awt\Color as Color;
use php\io\IOException as IOException;
use com\lowagie\text\html\Markup as Markup;

/**
 * If you are using True Type fonts, you can declare the paths of the different ttf- and ttc-files
 * to this class first and then create fonts in your code using one of the getFont method
 * without having to enter a path as parameter.
 *
 * @author  Bruno Lowagie (ported by Mills W. Staylor)
 */
 
class FontFactoryImp {
    /** This is a map of postscriptfontnames of True Type fonts and the path of their ttf- or ttc-file. */
    private $trueTypeFonts;//Properties
    
    private static $TTFamilyOrder = array("3", "1", "1033", "3", "0", "1033", "1", "0", "0", "0", "3", "0");
    /** This is a map of fontfamilies. */
    private $fontFamilies = array();
    /** This is the default encoding to use. */
    public $defaultEncoding = BaseFont::WINANSI;
    
    public $defaultEmbedding = BaseFont::NOT_EMBEDDED;
    
    
    public function __construct() {
        $this->trueTypeFonts = new Properties();
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::COURIER), FontFactory::COURIER);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::COURIER_BOLD), FontFactory::COURIER_BOLD);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::COURIER_OBLIQUE), FontFactory::COURIER_OBLIQUE);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::COURIER_BOLDOBLIQUE), FontFactory::COURIER_BOLDOBLIQUE);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::HELVETICA), FontFactory::HELVETICA);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::HELVETICA_BOLD), FontFactory::HELVETICA_BOLD);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::HELVETICA_OBLIQUE), FontFactory::HELVETICA_OBLIQUE);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::HELVETICA_BOLDOBLIQUE), FontFactory::HELVETICA_BOLDOBLIQUE);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::SYMBOL), FontFactory::SYMBOL);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::TIMES_ROMAN), FontFactory::TIMES_ROMAN);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::TIMES_BOLD), FontFactory::TIMES_BOLD);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::TIMES_ITALIC), FontFactory::TIMES_ITALIC);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::TIMES_BOLDITALIC), FontFactory::TIMES_BOLDITALIC);
        $this->trueTypeFonts->setProperty(strtolower(FontFactory::ZAPFDINGBATS), FontFactory::ZAPFDINGBATS);
        
        $tmp = array();
        array_push($tmp, FontFactory::COURIER, FontFactory::COURIER_BOLD, FontFactory::COURIER_OBLIQUE, FontFactory::COURIER_BOLDOBLIQUE);
        $this->fontFamilies[strtolower(FontFactory::COURIER)] = $tmp;
        $tmp = array();
        array_push($tmp, FontFactory::HELVETICA, FontFactory::HELVETICA_BOLD, FontFactory::HELVETICA_OBLIQUE, FontFactory::HELVETICA_BOLDOBLIQUE);
        $this->fontFamilies[strtolower(FontFactory::HELVETICA)] = $tmp;
        $tmp = array();
        array_push($tmp, FontFactory::SYMBOL);
        $this->fontFamilies[strtolower(FontFactory::SYMBOL)] = $tmp;
        $tmp = array();
        array_push($tmp, FontFactory::TIMES_ROMAN, FontFactory::TIMES_BOLD, FontFactory::TIMES_ITALIC, FontFactory::TIMES_BOLDITALIC);
        $this->fontFamilies[strtolower(FontFactory::TIMES)] = $tmp;
        $this->fontFamilies[strtolower(FontFactory::TIMES_ROMAN)] = $tmp;
        $tmp = array();
        array_push($tmp, FontFactory::ZAPFDINGBATS);
        $this->fontFamilies[strtolower(FontFactory::ZAPFDINGBATS)] = $tmp;
    }
    
    public function getFont() {
        $argCount = func_num_args();
        switch($argCount) {
            case 1: {
                $arg1 = func_get_arg(0);
                if (($arg1 instanceof Properties) == TRUE) {
                    $this->getFont1argProperties($arg1);
                }
                break;
            }
            case 6: {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                if (is_string($arg1) && is_string($arg2) && is_bool($arg3) && is_float($arg4) && is_integer($arg5) && ($arg6 instanceof Color) == TRUE) {
                    $this->getFont6argsStringStringBoolFloatIntColor($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
                }
                break;
            }
            case 7: {
                $arg1 = func_get_arg(0);
                $arg2 = func_get_arg(1);
                $arg3 = func_get_arg(2);
                $arg4 = func_get_arg(3);
                $arg5 = func_get_arg(4);
                $arg6 = func_get_arg(5);
                $arg7 = func_get_arg(6);
                if (is_string($arg1) && is_string($arg2) && is_bool($arg3) && is_float($arg4) && is_integer($arg5) && ($arg6 instanceof Color) == TRUE && is_bool($arg7)) {
                    $this->getFont7argsStringStringBoolFloatIntColorBool($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
            }
        }
    }
    
    /**
    * Constructs a <CODE>Font</CODE>-object.
    *
    * @param    fontname    the name of the font
    * @param    encoding    the encoding of the font
    * @param    embedded    true if the font is to be embedded in the PDF
    * @param    size        the size of this font
    * @param    style       the style of this font
    * @param    color       the <CODE>Color</CODE> of this font.
    * @return the Font constructed based on the parameters
    */
    private function getFont6argsStringStringBoolFloatIntColor(string $fontname, string $encoding, boolean $embedded, float $size, integer $style, Color $color) {
        $this->getFont7argsStringStringBoolFloatIntColorBool($fontname, $encoding, $embedded, $size, $style, $color, TRUE);
    }
    
    /**
    * Constructs a <CODE>Font</CODE>-object.
    *
    * @param    fontname    the name of the font
    * @param    encoding    the encoding of the font
    * @param    embedded    true if the font is to be embedded in the PDF
    * @param    size        the size of this font
    * @param    style       the style of this font
    * @param    color       the <CODE>Color</CODE> of this font.
    * @param    cached      true if the font comes from the cache or is added to
    *                       the cache if new, false if the font is always created new
    * @return the Font constructed based on the parameters
    */
    private function getFont7argsStringStringBoolFloatIntColorBool(string $fontname, string $encoding, boolean $embedded, float $size, integer $style, Color $color, boolean $cached) {
        if ($fontname == null)
            return new Font(Font::UNDEFINED, $size, $style, $color);
        $lowercasefontname = strtolower($fontname);
        $tmp = $this->fontFamilies[$lowercasefontname];
        if ($tmp != NULL) {
            $s = $style == Font::UNDEFINED ? Font::NORMAL : $style;
            $fs = Font::NORMAL;
            $found= FALSE;
            foreach ($tmp as $f) {
                $lcf = strtolower($f);
                $fs = Font::NORMAL;
                if (stripos($lcf, "bold") != FALSE)
                    $fs |= Font::BOLD;
                if (stripos($lcf, "italic") != FALSE || stripos($lcf, "oblique") != FALSE)
                    $fs |= Font:ITALIC;
                if (($s & Font::BOLDITALIC) == $fs) {
                    $fontname = $f;
                    $found = TRUE;
                    break;
                }
            }
            if ($style != Font::UNDEFINED && $found) {
                $style &= ~$fs;
            }
        }
            
        $baseFont = NULL;
        try {
            // the font is a type 1 font or CJK font
            BaseFont::createFont($fontname, $encoding, $embedded, $cached, NULL, NULL, TRUE);
        }
        catch (DocumentException $de) {
            if ($baseFont == NULL) {
                // the font is a true type font or an unknown font
                $fontname = $this->trueTypeFonts->getProperty(strtolower($fontname));
                // the font is not registered as truetype font
                if ($fontname == null)
                    return new Font(Font::UNDEFINED, $size, $style, $color);
                // the font is registered as truetype font
                $basefont = BaseFont::createFont($fontname, $encoding, $embedded, $cached, NULL, NULL);
            }
        }
        catch (IOException $ioe) {
            // the font is registered as a true type font, but the path was wrong
            return new Font(Font::UNDEFINED, $size, $style, $color);
        }
        
        return new Font($basefont, $size, $style, $color);
        
    }
    
    /**
    * Constructs a <CODE>Font</CODE>-object.
    *
    * @param   attributes  the attributes of a <CODE>Font</CODE> object.
    * @return the Font constructed based on the attributes
    */
    private function getFont1argProperties(Properties $attributes) {
        $fontname = NULL;
        $encoding = $this->defaultEncoding;
        $embedded = $this->defaultEmbedding;
        $size = Font::UNDEFINED;
        $style = Font::NORMAL;
        $color = NULL;
        $value = $attributes->getProperty(Markup::HTML_ATTR_STYLE);
        if ($value != NULL && strlen($value) > 0) {
            $styleAttributes = Markup::parseAttributes($value);
            if ($styleAttributes->isEmpty()) {
                $attributes->put(Markup::HTML_ATTR_STYLE, $value);
            } else {
                $fontname = $styleAttributes->getProperty(Markup::CSS_KEY_FONTFAMILY);
                if ($fontname != NULL) {
                $tmp = NULL;
                    while(strpos($fontname, ",") != FALSE) {
                        $tmp = substr($fontname, 0, strpos($fontname, ","));
                        if ($this->isRegistered($tmp)) {
                            $fontname = $tmp;
                        } else {
                            $fontname = substr($fontname, strpos($fontname, ",") + 1);
                        }
                    }
                }
                if (($value = $styleAttributes->getProperty(Markup::CSS_KEY_FONTSIZE)) != NULL) {
                    $size = Markup::parseLength($value);
                }
                if (($value = $styleAttributes->getProperty(Markup::CSS_KEY_FONTWEIGHT)) != NULL) {
                    $style |= Font::getStyleValue($value);
                }
                if (($value = $styleAttributes->getProperty(Markup::CSS_KEY_FONTSTYLE)) != NULL) {
                    $style |= Font::getStyleValue($value);
                }
                if (($value = $styleAttributes->getProperty(Markup::CSS_KEY_COLOR)) != NULL) {
                    $color = Markup::decodeColor($value);
                }
                
            }
        }
    }
    
    /**
    * Checks if a certain font is registered.
    *
    * @param   fontname    the name of the font that has to be checked.
    * @return  true if the font is found
    */
    public function isRegistered(string $fontname) {
        return $this->trueTypeFonts->containsKey(strtolower($fontname));
    }
}
?>