<?php
namespace com\lowagie\text\pdf;

class BaseFont
{
    const COURIER = "Courier";
    const COURIER_BOLD = "Courier-Bold";
    const COURIER_OBLIQUE = "Courier-Oblique";
    const COURIER_BOLDOBLIQUE = "Courier-BoldOblique";
    const HELVETICA = "Helvetica";
    const HELVETICA_BOLD = "Helvetica-Bold";
    const HELVETICA_OBLIQUE = "Helvetica-Oblique";
    const HELVETICA_BOLDOBLIQUE = "Helvetica-BoldOblique";
    const SYMBOL = "Symbol";
    const TIMES_ROMAN = "Times-Roman";
    const TIMES_BOLD = "Times-Bold";
    const TIMES_ITALIC = "Times-Italic";
    const TIMES_BOLDITALIC = "Times-BoldItalic";
    const ZAPFDINGBATS = "ZapfDingbats";

    const ASCENT = 1;
    const CAPHEIGHT = 2;
    const DESCENT = 3;
    const ITALICANGLE = 4;
    const BBOXLLX = 5;
    const BBOXLLY = 6;
    const BBOXURX = 7;
    const BBOXURY = 8;
    const AWT_ASCENT = 9;
    const AWT_DESCENT = 10;
    const AWT_LEADING = 11;
    const AWT_MAXADVANCE = 12;
    const UNDERLINE_POSITION = 13;
    const UNDERLINE_THICKNESS = 14;
    const STRIKETHROUGH_POSITION = 15;
    const STRIKETHROUGH_THICKNESS = 16;
    const SUBSCRIPT_SIZE = 17;
    const SUBSCRIPT_OFFSET = 18;
    const SUPERSCRIPT_SIZE = 19;
    const SUPERSCRIPT_OFFSET = 20;
    const FONT_TYPE_T1 = 0;
    const FONT_TYPE_TT = 1;
    const FONT_TYPE_CJK = 2;
    const FONT_TYPE_TTUNI = 3;
    const FONT_TYPE_DOCUMENT = 4;
    const FONT_TYPE_T3 = 5;
    const IDENTITY_H = "Identity-H";
    const IDENTITY_V = "Identity-V";
    const CP1250 = "Cp1250";
    const CP1252 = "Cp1252";
    const CP1257 = "Cp1257";
    const WINANSI = "Cp1252";
    const MACROMAN = "MacRoman";
    public static $CHAR_RANGE_LATIN = NULL;
    public static $CHAR_RANGE_ARABIC = NULL;
    public static $CHAR_RANGE_HEBREW = NULL;
    public static $CHAR_RANGE_CYRILLIC = NULL;

    const EMBEDDED = TRUE;
    const NOT_EMBEDDED = FALSE;
    const CACHED = TRUE;
    const NOT_CACHED = FALSE;
    const RESOURCE_PATH = "com/lowagie/text/pdf/fonts/";
    const CID_NEWLINE = '\u7fff';

    public $subsetRanges = NULL;//array
    const notdef = ".notdef";
    protected $widths = array();
    protected $differences = array();
    protected $unicodeDifferences = array();
    protected $charBBoxes = array();//multi-dimensional array
    protected $encoding = NULL;
    protected $embedded = FALSE;
    protected $fontSpecific = TRUE;
    protected static $fontCache = array();
    protected static $BuiltinFonts14 = array();
    protected $forceWidthsOutput = FALSE;
    protected $directTextToByte = FALSE;
    protected $subset = TRUE;
    protected $fastWinansi = FALSE;


    public static $initialized = FALSE;



    public static function initializeStatics()
    {
        if(BaseFont::$initialized == FALSE)
        {
            BaseFont::$CHAR_RANGE_LATIN = array(0, 0x17f, 0x2000, 0x206f, 0x20a0, 0x20cf, 0xfb00, 0xfb06);
            BaseFont::$CHAR_RANGE_ARABIC = array(0, 0x7f, 0x0600, 0x067f, 0x20a0, 0x20cf, 0xfb50, 0xfbff, 0xfe70, 0xfeff);
            BaseFont::$CHAR_RANGE_HEBREW = array(0, 0x7f, 0x0590, 0x05ff, 0x20a0, 0x20cf, 0xfb1d, 0xfb4f);
            BaseFont::$CHAR_RANGE_CYRILLIC = array(0, 0x7f, 0x0400, 0x052f, 0x2000, 0x206f, 0x20a0, 0x20cf);
            BaseFont::$initialized = TRUE;
        }
    }

}

BaseFont::initializeStatics();

?>