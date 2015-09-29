<?php
namespace com\lowagie\text;
require_once "ElementListener.php";
use com\lowagie\text\ElementListener as ElementListener;

interface Element
{
    const HEADER = 0;
    const TITLE = 1;
    const SUBJECT = 2;
    const KEYWORDS = 3;
    const AUTHOR = 4;
    const PRODUCER = 5;
    const CREATIONDATE = 6;
    const CREATOR = 7;
    const CHUNK = 10;
    const PHRASE = 11;
    const PARAGRAPH = 12;
    const SECTION = 13;
    const LISTITEM = 15;
    const CHAPTER = 16;
    const ANCHOR = 17;
    const CELL = 20;
    const ROW = 21;
    const TABLE = 22;
    const PTABLE = 23;
    const ANNOTATION = 29;
    const RECTANGLE = 30;
    const JPEG = 32;
    const JPEG2000 = 33;
    const IMGRAW = 34;
    const IMGTEMPLATE = 35;
    const MULTI_COLUMN_TEXT = 40;
    const MARKED = 50;
    const ALIGN_UNDEFINED = -1;
    const ALIGN_LEFT = 0;
    const ALIGN_CENTER = 1;
    const ALIGN_RIGHT = 2;
    const ALIGN_JUSTIFIED = 3;
    const ALIGN_TOP = 4;
    const ALIGN_MIDDLE = 5;
    const ALIGN_BOTTOM = 6;
    const ALIGN_BASELINE = 7;
    const ALIGN_JUSTIFIED_ALL = 8;
    const CCITTG4 = 0x100;
    const CCITTG3_1D = 0x101;
    const CCITTG3_2D = 0x102;
    const CCITT_BLACKIS1 = 1;
    const CCITT_ENCODEDBYTEALIGN = 2;
    const CCITT_ENDOFLINE = 4;
    const CCITT_ENDOFBLOCK = 8;

    public function process(ElementListener $listener);
    public function type();
    public function isContent();
    public function isNestable();
    public function getChunks();
    public function toString();
}
?>