<?PHP
/*
 * $Id: PdfName.php,v 1.2 2005/10/25 19:13:48 mstaylor Exp $
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

require_once("PdfObject.php");
require_once("PdfByteBuffer.php");
require_once("PRTokeniser.php");
require_once("../../exceptions/IllegalArgumentException.php");

class PdfName extends PdfObject
{

    // static membervariables (a variety of standard names used in PDF)

    /** A name */
    public static $A = NULL; 
    /** A name */
    public static $AA = NULL;
    /** A name */
    public static $ABSOLUTECALORIMETRIC = NULL;
    /** A name */
    public static $AC = NULL;
    /** A name */
    public static $ACROFORM = NULL;
    /** A name */
    public static $ACTION = NULL;
    /** A name */
    public static $ADBE_PKCS7_DETACHED = NULL;
    /** A name */
    public static $ADBE_PKCS7_SHA1 = NULL;
    /** A name */
    public static $ADBE_X509_RSA_SHA1 = NULL;
    /** A name */
    public static $ADOBE_PPKLITE = NULL;
    /** A name */
    public static $ADOBE_PPKMS = NULL;
    /** A name */
    public static $AIS = NULL;
    /** A name */
    public static $ALLPAGES = NULL;
    /** A name */
    public static $ALTERNATE = NULL;
    /** A name */
    public static $ANNOT = NULL;
    /** A name */
    public static $ANTIALIAS = NULL;
    /** A name */
    public static $ANNOTS = NULL;
    /** A name */
    public static $AP = NULL;
    /** A name */
    public static $ARTBOX = NULL;
    /** A name */
    public static $ASCENT = NULL;
    /** A name */
    public static $AS = NULL;
    /** A name */
    public static $ASCII85DECODE = NULL;
    /** A name */
    public static $ASCIIHEXDECODE = NULL;
    /** A name */
    public static $AUTHOR = NULL;
    /** A name */
    public static $B = NULL;
    /** A name */
    public static $BASEENCODING = NULL;
    /** A name */
    public static $BASEFONT = NULL;
    /** A name */
    public static $BBOX = NULL;
    /** A name */
    public static $BC = NULL;
    /** A name */
    public static $BG = NULL;
    /** A name */
    public static $BIGFIVE = NULL;
    /** A name */
    public static $BITSPERCOMPONENT = NULL;
    /** A name */
    public static $BITSPERSAMPLE = NULL;
    /** A name */
    public static $BL = NULL;
    /** A name */
    public static $BLACKIS1 = NULL;
    /** A name */
    public static $BLACKPOINT = NULL;
    /** A name */
    public static $BLEEDBOX = NULL;
    /** A name */
    public static $BLINDS = NULL;
    /** A name */
    public static $BM = NULL;
    /** A name */
    public static $BORDER = NULL;
    /** A name */
    public static $BOUNDS = NULL;
    /** A name */
    public static $BOX = NULL;
    /** A name */
    public static $BS = NULL;
    /** A name */
    public static $BTN = NULL;
    /** A name */
    public static $BYTERANGE = NULL;
    /** A name */
    public static $C = NULL;
    /** A name */
    public static $C0 = NULL;
    /** A name */
    public static $C1 = NULL;
    /** A name */
    public static $CA = NULL;
    /** A name */
    public static $ca = NULL;
    /** A name */
    public static $CALGRAY = NULL;
    /** A name */
    public static $CALRGB = NULL;
    /** A name */
    public static $CAPHEIGHT = NULL;
    /** A name */
    public static $CATALOG = NULL;
    /** A name */
    public static $CATEGORY = NULL;
    /** A name */
    public static $CCITTFAXDECODE = NULL;
    /** A name */
    public static $CENTERWINDOW = NULL;
    /** A name */
    public static $CERT = NULL;
    /** A name */
    public static $CH = NULL;
    /** A name */
    public static $CIDFONTTYPE0 = NULL;
    /** A name */
    public static $CIDFONTTYPE2 = NULL;
    /** A name */
    public static $CIDSYSTEMINFO = NULL;
    /** A name */
    public static $CIDTOGIDMAP = NULL;
    /** A name */
    public static $CIRCLE = NULL;
    /** A name */
    public static $CO = NULL;
    /** A name */
    public static $COLORS = NULL;
    /** A name */
    public static $COLORSPACE = NULL;
    /** A name */
    public static $COLUMNS = NULL;
    /** A name */
    public static $CONTACTINFO = NULL;
    /** A name */
    public static $CONTENT = NULL;
    /** A name */
    public static $CONTENTS = NULL;
    /** A name */
    public static $COORDS = NULL;
    /** A name */
    public static $COUNT = NULL;
    /** A name of a base 14 type 1 font */
    public static $COURIER = NULL;
    /** A name of a base 14 type 1 font */
    public static $COURIER_BOLD = NULL;
    /** A name of a base 14 type 1 font */
    public static $COURIER_OBLIQUE = NULL;
    /** A name of a base 14 type 1 font */
    public static $COURIER_BOLDOBLIQUE = NULL;
    /** A name */
    public static $CREATIONDATE = NULL;
    /** A name */
    public static $CREATOR = NULL;
    /** A name */
    public static $CREATORINFO = NULL;
    /** A name */
    public static $CROPBOX = NULL;
    /** A name */
    public static $CS = NULL;
    /** A name */
    public static $D = NULL;
    /** A name */
    public static $DA = NULL;
    /** A name */
    public static $DC = NULL;
    /** A name */
    public static $DCTDECODE = NULL;
    /** A name */
    public static $DECODE = NULL;
    /** A name */
    public static $DECODEPARMS = NULL;
    /** A name */
    public static $DEFAULTCMYK = NULL;
    /** A name */
    public static $DEFAULTGRAY = NULL;
    /** A name */
    public static $DEFAULTRGB = NULL;
    /** A name */
    public static $DESCENDANTFONTS = NULL;
    /** A name */
    public static $DESCENT = NULL;
    /** A name */
    public static $DEST = NULL;
    /** A name */
    public static $DESTOUTPUTPROFILE = NULL;
    /** A name */
    public static $DESTS = NULL;
    /** A name */
    public static $DEVICEGRAY = NULL;
    /** A name */
    public static $DEVICERGB = NULL;
    /** A name */
    public static $DEVICECMYK = NULL;
    /** A name */
    public static $DI = NULL;
    /** A name */
    public static $DIFFERENCES = NULL;
    /** A name */
    public static $DISSOLVE = NULL;
    /** A name */
    public static $DIRECTION = NULL;
    /** A name */
    public static $DISPLAYDOCTITLE = NULL;
    /** A name */
    public static $DM = NULL;
    /** A name */
    public static $DOMAIN = NULL;
    /** A name */
    public static $DP = NULL;
    /** A name */
    public static $DR = NULL;
    /** A name */
    public static $DS = NULL;
    /** A name */
    public static $DUR = NULL;
    /** A name */
    public static $DV = NULL;
    /** A name */
    public static $DW = NULL;
    /** A name */
    public static $E = NULL;
    /** A name */
    public static $EARLYCHANGE = NULL;
    /** A name */
    public static $EF = NULL;
    /** A name */
    public static $EMBEDDEDFILE = NULL;
    /** A name */
    public static $ENCODE = NULL;
    /** A name */
    public static $ENCODEDBYTEALIGN = NULL;
    /** A name */
    public static $ENCODING = NULL;
    /** A name */
    public static $ENCRYPT = NULL;
    /** A name */
    public static $ENDOFBLOCK = NULL;
    /** A name */
    public static $ENDOFLINE = NULL;
    /** A name */
    public static $EXTEND = NULL;
    /** A name */
    public static $EXTGSTATE = NULL;
    /** A name */
    public static $EXPORT = NULL;
    /** A name */
    public static $EXPORTSTATE = NULL;
    /** A name */
    public static $EVENT = NULL;
    /** A name */
    public static $F = NULL;
    /** A name */
    public static $FB = NULL;
    /** A name */
    public static $FDECODEPARMS = NULL;
    /** A name */
    public static $FDF = NULL;
    /** A name */
    public static $FF = NULL;
    /** A name */
    public static $FFILTER = NULL;
    /** A name */
    public static $FIELDS = NULL;
    /** A name */
    public static $FILEATTACHMENT = NULL;
    /** A name */
    public static $FILESPEC = NULL;
    /** A name */
    public static $FILTER = NULL;
    /** A name */
    public static $FIRST = NULL;
    /** A name */
    public static $FIRSTCHAR = NULL;
    /** A name */
    public static $FIRSTPAGE = NULL;
    /** A name */
    public static $FIT = NULL;
    /** A name */
    public static $FITH = NULL;
    /** A name */
    public static $FITV = NULL;
    /** A name */
    public static $FITR = NULL;
    /** A name */
    public static $FITB = NULL;
    /** A name */
    public static $FITBH = NULL;
    /** A name */
    public static $FITBV = NULL;
    /** A name */
    public static $FITWINDOW = NULL;
    /** A name */
    public static $FLAGS = NULL;
    /** A name */
    public static $FLATEDECODE = NULL;
    /** A name */
    public static $FO = NULL;
    /** A name */
    public static $FONT = NULL;
    /** A name */
    public static $FONTBBOX = NULL;
    /** A name */
    public static $FONTDESCRIPTOR = NULL;
    /** A name */
    public static $FONTFILE = NULL;
    /** A name */
    public static $FONTFILE2 = NULL;
    /** A name */
    public static $FONTFILE3 = NULL;
    /** A name */
    public static $FONTNAME = NULL;
    /** A name */
    public static $FORM = NULL;
    /** A name */
    public static $FORMTYPE = NULL;
    /** A name */
    public static $FREETEXT = NULL;
    /** A name */
    public static $FRM = NULL;
    /** A name */
    public static $FS = NULL;
    /** A name */
    public static $FT = NULL;
    /** A name */
    public static $FULLSCREEN = NULL;
    /** A name */
    public static $FUNCTION = NULL;
    /** A name */
    public static $FUNCTIONS = NULL;
    /** A name */
    public static $FUNCTIONTYPE = NULL;
    /** A name of an attribute. */
    public static $GAMMA = NULL;
    /** A name of an attribute. */
    public static $GBK = NULL;
    /** A name of an attribute. */
    public static $GLITTER = NULL;
    /** A name of an attribute. */
    public static $GOTO = NULL;
    /** A name of an attribute. */
    public static $GOTOR = NULL;
    /** A name of an attribute. */
    public static $GROUP = NULL;
    /** A name of an attribute. */
    public static $GTS_PDFX = NULL;
    /** A name of an attribute. */
    public static $GTS_PDFXVERSION = NULL;
    /** A name of an attribute. */
    public static $H = NULL;
    /** A name of an attribute. */
    public static $HEIGHT = NULL;
    /** A name of a base 14 type 1 font */
    public static $HELVETICA = NULL;
    /** A name of a base 14 type 1 font */
    public static $HELVETICA_BOLD = NULL;
    /** This is a static PdfName PdfName of a base 14 type 1 font */
    public static $HELVETICA_OBLIQUE = NULL;
    /** This is a static PdfName PdfName of a base 14 type 1 font */
    public static $HELVETICA_BOLDOBLIQUE = NULL;
    /** A name */
    public static $HID = NULL;
    /** A name */
    public static $HIDE = NULL;
    /** A name */
    public static $HIDEMENUBAR = NULL;
    /** A name */
    public static $HIDETOOLBAR = NULL;
    /** A name */
    public static $HIDEWINDOWUI = NULL;
    /** A name */
    public static $HIGHLIGHT = NULL;
    /** A name */
    public static $I = NULL;
    /** A name */
    public static $ICCBASED = NULL;
    /** A name */
    public static $ID = NULL;
    /** A name */
    public static $IDENTITY = NULL;
    /** A name */
    public static $IF = NULL;
    /** A name */
    public static $IMAGE = NULL;
    /** A name */
    public static $IMAGEB = NULL;
    /** A name */
    public static $IMAGEC = NULL;
    /** A name */
    public static $IMAGEI = NULL;
    /** A name */
    public static $IMAGEMASK = NULL;
    /** A name */
    public static $INDEX = NULL;
    /** A name */
    public static $INDEXED = NULL;
    /** A name */
    public static $INFO = NULL;
    /** A name */
    public static $INK = NULL;
    /** A name */
    public static $INKLIST = NULL;
    /** A name */
    public static $IMPORTDATA = NULL;
    /** A name */
    public static $INTENT = NULL;
    /** A name */
    public static $INTERPOLATE = NULL;
    /** A name */
    public static $ISMAP = NULL;
    /** A name */
    public static $IRT = NULL;
    /** A name */
    public static $ITALICANGLE = NULL;
    /** A name */
    public static $IX = NULL;
    /** A name */
    public static $JAVASCRIPT = NULL;
    /** A name */
    public static $JS = NULL;
    /** A name */
    public static $K = NULL;
    /** A name */
    public static $KEYWORDS = NULL;
    /** A name */
    public static $KIDS = NULL;
    /** A name */
    public static $L = NULL;
    /** A name */
    public static $L2R = NULL;
    /** A name */
    public static $LANG = NULL;
    /** A name */
    public static $LANGUAGE = NULL;
    /** A name */
    public static $LAST = NULL;
    /** A name */
    public static $LASTCHAR = NULL;
    /** A name */
    public static $LASTPAGE = NULL;
    /** A name */
    public static $LAUNCH = NULL;
    /** A name */
    public static $LENGTH = NULL;
    /** A name */
    public static $LENGTH1 = NULL;
    /** A name */
    public static $LIMITS = NULL;
    /** A name */
    public static $LINE = NULL;
    /** A name */
    public static $LINK = NULL;
    /** A name */
    public static $LISTMODE = NULL;
    /** A name */
    public static $LOCATION = NULL;
    /** A name */
    public static $LOCK = NULL;
    /** A name */
    public static $LZWDECODE = NULL;
    /** A name */
    public static $M = NULL;
    /** A name */
    public static $MATRIX = NULL;
    /** A name of an encoding */
    public static $MAC_EXPERT_ENCODING = NULL;
    /** A name of an encoding */
    public static $MAC_ROMAN_ENCODING = NULL;
    /** A name */
    public static $MASK = NULL;
    /** A name */
    public static $MAX = NULL;
    /** A name */
    public static $MAXLEN = NULL;
    /** A name */
    public static $MEDIABOX = NULL;
    /** A name */
    public static $METADATA = NULL;
    /** A name */
    public static $MIN = NULL;
    /** A name */
    public static $MK = NULL;
    /** A name */
    public static $MMTYPE1 = NULL;
    /** A name */
    public static $MODDATE = NULL;
    /** A name */
    public static $N = NULL;
    /** A name */
    public static $N0 = NULL;
    /** A name */
    public static $N1 = NULL;
    /** A name */
    public static $N2 = NULL;
    /** A name */
    public static $N3 = NULL;
    /** A name */
    public static $N4 = NULL;
    /** A name */
    public static $NAME = NULL;
    /** A name */
    public static $NAMED = NULL;
    /** A name */
    public static $NAMES = NULL;
    /** A name */
    public static $NEEDAPPEARANCES = NULL;
    /** A name */
    public static $NEWWINDOW = NULL;
    /** A name */
    public static $NEXT = NULL;
    /** A name */
    public static $NEXTPAGE = NULL;
    /** A name */
    public static $NM = NULL;
    /** A name */
    public static $NONFULLSCREENPAGEMODE = NULL;
    /** A name */
    public static $NUMS = NULL;
    /** A name */
    public static $O = NULL;
    /** A name */
    public static $OBJSTM = NULL;
    /** A name */
    public static $OC = NULL;
    /** A name */
    public static $OCG = NULL;
    /** A name */
    public static $OCGS = NULL;
    /** A name */
    public static $OCMD = NULL;
    /** A name */
    public static $OCPROPERTIES = NULL;
    /** A name */
    public static $Off = NULL;
    /** A name */
    public static $OFF = NULL;
    /** A name */
    public static $ON = NULL;
    /** A name */
    public static $ONECOLUMN = NULL;
    /** A name */
    public static $OPEN = NULL;
    /** A name */
    public static $OPENACTION = NULL;
    /** A name */
    public static $OP = NULL;
    /** A name */
    public static $op = NULL;
    /** A name */
    public static $OPM = NULL;
    /** A name */
    public static $OPT = NULL;
    /** A name */
    public static $ORDER = NULL;
    /** A name */
    public static $ORDERING = NULL;
    /** A name */
    public static $OUTLINES = NULL;
    /** A name */
    public static $OUTPUTCONDITION = NULL;
    /** A name */
    public static $OUTPUTCONDITIONIDENTIFIER = NULL;
    /** A name */
    public static $OUTPUTINTENT = NULL;
    /** A name */
    public static $OUTPUTINTENTS = NULL;
    /** A name */
    public static $P = NULL;
    /** A name */
    public static $PAGE = NULL;
    /** A name */
    public static $PAGELABELS = NULL;
    /** A name */
    public static $PAGELAYOUT = NULL;
    /** A name */
    public static $PAGEMODE = NULL;
    /** A name */
    public static $PAGES = NULL;
    /** A name */
    public static $PAINTTYPE = NULL;
    /** A name */
    public static $PANOSE = NULL;
    /** A name */
    public static $PARENT = NULL;
    /** A name */
    public static $PATTERN = NULL;
    /** A name */
    public static $PATTERNTYPE = NULL;
    /** A name */
    public static $PDF = NULL;
    /** A name */
    public static $PERCEPTUAL = NULL;
    /** A name */
    public static $POPUP = NULL;
    /** A name */
    public static $PREDICTOR = NULL;
    /** A name */
    public static $PREFERRED = NULL;
    /** A name */
    public static $PRESERVERB = NULL;
    /** A name */
    public static $PREV = NULL;
    /** A name */
    public static $PREVPAGE = NULL;
    /** A name */
    public static $PRINT = NULL;
    /** A name */
    public static $PRINTSTATE = NULL;
    /** A name */
    public static $PROCSET = NULL;
    /** A name */
    public static $PRODUCER = NULL;
    /** A name */
    public static $PROPERTIES = NULL;
    /** A name */
    public static $PS = NULL;
    /** A name */
    public static $Q = NULL;
    /** A name */
    public static $QUADPOINTS = NULL;
    /** A name */
    public static $R = NULL;
    /** A name */
    public static $R2L = NULL;
    /** A name */
    public static $RANGE = NULL;
    /** A name */
    public static $RC = NULL;
    /** A name */
    public static $RBGROUPS = NULL;
    /** A name */
    public static $REASON = NULL;
    /** A name */
    public static $RECT = NULL;
    /** A name */
    public static $REGISTRY = NULL;
    /** A name */
    public static $REGISTRYNAME = NULL;
    /** A name */
    public static $RELATIVECALORIMETRIC = NULL;
    /** A name */
    public static $RENDITION = NULL;
    /** A name */
    public static $RESETFORM = NULL;
    /** A name */
    public static $RESOURCES = NULL;
    /** A name */
    public static $RI = NULL;
    /** A name */
    public static $ROOT = NULL;
    /** A name */
    public static $ROTATE = NULL;
    /** A name */
    public static $ROWS = NULL;
    /** A name */
    public static $RUNLENGTHDECODE = NULL;
    /** A name */
    public static $RV = NULL;
    /** A name */
    public static $S = NULL;
    /** A name */
    public static $SATURATION = NULL;
    /** A name */
    public static $SCREEN = NULL;
    /** A name */
    public static $SEPARATION = NULL;
    /** A name */
    public static $SETOCGSTATE = NULL;
    /** A name */
    public static $SHADING = NULL;
    /** A name */
    public static $SHADINGTYPE = NULL;
    /** A name */
    public static $SHIFT_JIS = NULL;
    /** A name */
    public static $SIG = NULL;
    /** A name */
    public static $SIGFLAGS = NULL;
    /** A name */
    public static $SINGLEPAGE = NULL;
    /** A name */
    public static $SIZE = NULL;
    /** A name */
    public static $SMASK = NULL;
    /** A name */
    public static $SPLIT = NULL;
    /** A name */
    public static $SQUARE = NULL;
    /** A name */
    public static $ST = NULL;
    /** A name */
    public static $STAMP = NULL;
    /** A name */
    public static $STANDARD = NULL;
    /** A name */
    public static $STATE = NULL;
    /** A name */
    public static $STRIKEOUT = NULL;
    /** A name */
    public static $STRUCTPARENT = NULL;
    /** A name */
    public static $STYLE = NULL;
    /** A name */
    public static $STEMV = NULL;
    /** A name */
    public static $SUBFILTER = NULL;
    /** A name */
    public static $SUBJECT = NULL;
    /** A name */
    public static $SUBMITFORM = NULL;
    /** A name */
    public static $SUBTYPE = NULL;
    /** A name */
    public static $SUPPLEMENT = NULL;
    /** A name */
    public static $SV = NULL;
    /** A name */
    public static $SW = NULL;
    /** A name of a base 14 type 1 font */
    public static $SYMBOL = NULL;
    /** A name */
    public static $T = NULL;
    /** A name */
    public static $TEXT = NULL;
    /** A name */
    public static $THUMB = NULL;
    /** A name */
    public static $THREADS = NULL;
    /** A name */
    public static $TI = NULL;
    /** A name */
    public static $TILINGTYPE = NULL;
    /** A name of a base 14 type 1 font */
    public static $TIMES_ROMAN = NULL;
    /** A name of a base 14 type 1 font */
    public static $TIMES_BOLD = NULL;
    /** A name of a base 14 type 1 font */
    public static $TIMES_ITALIC = NULL;
    /** A name of a base 14 type 1 font */
    public static $TIMES_BOLDITALIC = NULL;
    /** A name */
    public static $TITLE = NULL;
    /** A name */
    public static $TK = NULL;
    /** A name */
    public static $TM = NULL;
    /** A name */
    public static $TOGGLE = NULL;
    /** A name */
    public static $TOUNICODE = NULL;
    /** A name */
    public static $TP = NULL;
    /** A name */
    public static $TRANS = NULL;
    /** A name */
    public static $TRANSPARENCY = NULL;
    /** A name */
    public static $TRAPPED = NULL;
    /** A name */
    public static $TRIMBOX = NULL;
    /** A name */
    public static $TRUETYPE = NULL;
    /** A name */
    public static $TU = NULL;
    /** A name */
    public static $TWOCOLUMNLEFT = NULL;
    /** A name */
    public static $TWOCOLUMNRIGHT = NULL;
    /** A name */
    public static $TX = NULL;
    /** A name */
    public static $TYPE = NULL;
    /** A name */
    public static $TYPE0 = NULL;
    /** A name */
    public static $TYPE1 = NULL;
    /** A name of an attribute. */
    public static $U = NULL;
    /** A name of an attribute. */
    public static $UHC = NULL;
    /** A name of an attribute. */
    public static $UNDERLINE = NULL;
    /** A name */
    public static $URI = NULL;
    /** A name */
    public static $URL = NULL;
    /** A name */
    public static $USAGE = NULL;
    /** A name */
    public static $USENONE = NULL;
    /** A name */
    public static $USEOC = NULL;
    /** A name */
    public static $USEOUTLINES = NULL;
    /** A name */
    public static $USER = NULL;
    /** A name */
    public static $USETHUMBS = NULL;
    /** A name */
    public static $V = NULL;
    /** A name */
    public static $VERISIGN_PPKVS = NULL;
    /** A name */
    public static $VIEW = NULL;
    /** A name */
    public static $VIEWERPREFERENCES = NULL;
    /** A name */
    public static $VIEWSTATE = NULL;
    /** A name */
    public static $VISIBLEPAGES = NULL;
    /** A name of an attribute. */
    public static $W = NULL;
    /** A name of an attribute. */
    public static $W2 = NULL;
    /** A name of an attribute. */
    public static $WC = NULL;
    /** A name of an attribute. */
    public static $WIDGET = NULL;
    /** A name of an attribute. */
    public static $WIDTH = NULL;
    /** A name */
    public static $WIDTHS = NULL;
    /** A name of an encoding */
    public static $WIN = NULL;
    /** A name of an encoding */
    public static $WIN_ANSI_ENCODING = NULL;
    /** A name of an encoding */
    public static $WIPE = NULL;
    /** A name */
    public static $WHITEPOINT = NULL;
    /** A name */
    public static $WP = NULL;
    /** A name of an encoding */
    public static $WS = NULL;
    /** A name */
    public static $X = NULL;
    /** A name */
    public static $XOBJECT = NULL;
    /** A name */
    public static $XSTEP = NULL;
    /** A name */
    public static $XREF = NULL;
    /** A name */
    public static $XREFSTM = NULL;
    /** A name */
    public static $XYZ = NULL;
    /** A name */
    public static $YSTEP = NULL;
    /** A name of a base 14 type 1 font */
    public static $ZAPFDINGBATS = NULL;
    /** A name */
    public static $ZOOM = NULL;

    public static $initialized = FALSE;


    private $hash = 0;


    public static function initializeStatics()
    {
        if(PdfName::$initialized == FALSE)
        {
            PdfName::$A = new PdfName("A");
            PdfName::$AA = new PdfName("AA");
            PdfName::$ABSOLUTECALORIMETRIC = new PdfName("AbsoluteColorimetric");
            PdfName::$AC = new PdfName("AC");
            PdfName::$ACROFORM = new PdfName("AcroForm");
            PdfName::$ACTION = new PdfName("Action");
            PdfName::$ADBE_PKCS7_DETACHED = new PdfName("adbe.pkcs7.detached");
            PdfName::$ADBE_X509_RSA_SHA1 = new PdfName("adbe.x509.rsa_sha1");
            PdfName::$ADOBE_PPKLITE = new PdfName("Adobe.PPKLite");
            PdfName::$ADOBE_PPKMS = new PdfName("Adobe.PPKMS");
            PdfName::$AIS = new PdfName("AIS");
            PdfName::$ALLPAGES = new PdfName("AllPages");
            PdfName::$ALTERNATE = new PdfName("Alternate");
            PdfName::$ANNOT = new PdfName("Annot");
            PdfName::$ANTIALIAS = new PdfName("AntiAlias");
            PdfName::$ANNOTS = new PdfName("Annots");
            PdfName::$AP = new PdfName("AP");
            PdfName::$ARTBOX = new PdfName("ArtBox");
            PdfName::$ASCENT = new PdfName("Ascent");
            PdfName::$AS = new PdfName("AS");
            PdfName::$ASCII85DECODE = new PdfName("ASCII85Decode");
            PdfName::$ASCIIHEXDECODE = new PdfName("ASCIIHexDecode");
            PdfName::$AUTHOR = new PdfName("Author");
            PdfName::$B = new PdfName("B");
            PdfName::$BASEENCODING = new PdfName("BaseEncoding");
            PdfName::$BASEFONT = new PdfName("BaseFont");
            PdfName::$BBOX = new PdfName("BBox");
            PdfName::$BC = new PdfName("BC");
            PdfName::$BG = new PdfName("BG");
            PdfName::$BIGFIVE = new PdfName("BigFive");
            PdfName::$BITSPERCOMPONENT = new PdfName("BitsPerComponent");
            PdfName::$BITSPERSAMPLE = new PdfName("BitsPerSample");
            PdfName::$BL = new PdfName("Bl");
            PdfName::$BLACKIS1 = new PdfName("BlackIs1");
            PdfName::$BLACKPOINT = new PdfName("BlackPoint");
            PdfName::$BLEEDBOX = new PdfName("BleedBox");
            PdfName::$BLINDS = new PdfName("Blinds");
            PdfName::$BM = new PdfName("BM");
            PdfName::$BORDER = new PdfName("Border");
            PdfName::$BOUNDS = new PdfName("Bounds");
            PdfName::$BOX = new PdfName("Box");
            PdfName::$BS = new PdfName("BS");
            PdfName::$BTN = new PdfName("Btn");
            PdfName::$BYTERANGE = new PdfName("ByteRange");
            PdfName::$C = new PdfName("C");
            PdfName::$C0 = new PdfName("C0");
            PdfName::$C1 = new PdfName("C1");
            PdfName::$CA = new PdfName("CA");
            PdfName::$ca = new PdfName("ca");
            PdfName::$CALGRAY = new PdfName("CalGray");
            PdfName::$CALRGB = new PdfName("CalRGB");
            PdfName::$CAPHEIGHT = new PdfName("CapHeight");
            PdfName::$CATALOG = new PdfName("Catalog");
            PdfName::$CATEGORY = new PdfName("Category");
            PdfName::$CCITTFAXDECODE = new PdfName("CCITTFaxDecode");
            PdfName::$CENTERWINDOW = new PdfName("CenterWindow");
            PdfName::$CERT = new PdfName("Cert");
            PdfName::$CH = new PdfName("Ch");
            PdfName::$CIDFONTTYPE0 = new PdfName("CIDFontType0");
            PdfName::$CIDFONTTYPE2 = new PdfName("CIDFontType2");
            PdfName::$CIDSYSTEMINFO = new PdfName("CIDSystemInfo");
            PdfName::$CIDTOGIDMAP = new PdfName("CIDToGIDMap");
            PdfName::$CIRCLE = new PdfName("Circle");
            PdfName::$CO = new PdfName("CO")
            PdfName::$COLORS = new PdfName("Colors");
            PdfName::$COLORSPACE = new PdfName("ColorSpace");
            PdfName::$COLUMNS = new PdfName("Columns");
            PdfName::$CONTACTINFO = new PdfName("ContactInfo");
            PdfName::$CONTENT = new PdfName("Content");
            PdfName::$CONTENTS = new PdfName("Contents");
            PdfName::$COORDS = new PdfName("Coords");
            PdfName::$COUNT = new PdfName("Count");
            PdfName::$COURIER = new PdfName("Courier");
            PdfName::$COURIER_BOLD = new PdfName("Courier-Bold");
            PdfName::$COURIER_OBLIQUE = new PdfName("Courier-Oblique");
            PdfName::$COURIER_BOLDOBLIQUE = new PdfName("Courier-BoldOblique");
            PdfName::$CREATIONDATE = new PdfName("CreationDate");
            PdfName::$CREATOR = new PdfName("Creator");
            PdfName::$CREATORINFO = new PdfName("CreatorInfo");
            PdfName::$CROPBOX = new PdfName("CropBox");
            PdfName::$CS = new PdfName("CS");
            PdfName::$D = new PdfName("D");
            PdfName::$DA = new PdfName("DA");
            PdfName::$DC = new PdfName("DC");
            PdfName::$DCTDECODE = new PdfName("DCTDecode");
            PdfName::$DECODE = new PdfName("Decode");
            PdfName::$DECODEPARMS = new PdfName("DecodeParms");
            PdfName::$DEFAULTCMYK = new PdfName("DefaultCMYK");
            PdfName::$DEFAULTGRAY = new PdfName("DefaultGray");
            PdfName::$DEFAULTRGB = new PdfName("DefaultRGB");
            PdfName::$DESCENDANTFONTS = new PdfName("DescendantFonts");
            PdfName::$DESCENT = new PdfName("Descent");
            PdfName::$DEST = new PdfName("Dest");
            PdfName::$DESTOUTPUTPROFILE = new PdfName("DestOutputProfile");
            PdfName::$DESTS = new PdfName("Dests");
            PdfName::$DEVICEGRAY = new PdfName("DeviceGray");
            PdfName::$DEVICERGB = new PdfName("DeviceRGB");
            PdfName::$DEVICECMYK = new PdfName("DeviceCMYK");
            PdfName::$DI = new PdfName("Di");
            PdfName::$DIFFERENCES = new PdfName("Differences");
            PdfName::$DISSOLVE = new PdfName("Dissolve");
            PdfName::$DIRECTION = new PdfName("Direction");
            PdfName::$DISPLAYDOCTITLE = new PdfName("DisplayDocTitle");
            PdfName::$DM = new PdfName("Dm");
            PdfName::$DOMAIN = new PdfName("Domain");
            PdfName::$DP = new PdfName("DP");
            PdfName::$DR = new PdfName("DR");
            PdfName::$DS = new PdfName("DS");
            PdfName::$DUR = new PdfName("Dur");
            PdfName::$DV = new PdfName("DV");
            PdfName::$DW = new PdfName("DW");
            PdfName::$E = new PdfName("E");
            PdfName::$EARLYCHANGE = new PdfName("EarlyChange");
            PdfName::$EF = new PdfName("EF");
            PdfName::$EMBEDDEDFILE = new PdfName("EmbeddedFile");
            PdfName::$ENCODE = new PdfName("Encode");
            PdfName::$ENCODEDBYTEALIGN = new PdfName("EncodedByteAlign");
            PdfName::$ENCODING = new PdfName("Encoding");
            PdfName::$ENCRYPT = new PdfName("Encrypt");
            PdfName::$ENDOFBLOCK = new PdfName("EndOfBlock");
            PdfName::$ENDOFLINE = new PdfName("EndOfLine");
            PdfName::$EXTEND = new PdfName("Extend");
            PdfName::$EXTGSTATE = new PdfName("ExtGState");
            PdfName::$EXPORT = new PdfName("Export");
            PdfName::$EXPORTSTATE = new PdfName("ExportState");
            PdfName::$EVENT = new PdfName("Event");
            PdfName::$F = new PdfName("F");
            PdfName::$FB = new PdfName("FB");
            PdfName::$FDECODEPARMS = new PdfName("FDecodeParms");
            PdfName::$FDF = new PdfName("FDF");
            PdfName::$FF = new PdfName("Ff");
            PdfName::$FFILTER = new PdfName("FFilter");
            PdfName::$FIELDS = new PdfName("Fields");
            PdfName::$FILEATTACHMENT = new PdfName("FileAttachment");
            PdfName::$FILESPEC = new PdfName("Filespec");
            PdfName::$FILTER = new PdfName("Filter");
            PdfName::$FIRST = new PdfName("First");
            PdfName::$FIRSTCHAR = new PdfName("FirstChar");
            PdfName::$FIRSTPAGE = new PdfName("FirstPage");
            PdfName::$FIT = new PdfName("Fit");
            PdfName::$FITH = new PdfName("FitH");
            PdfName::$FITV = new PdfName("FitV");
            PdfName::$FITR = new PdfName("FitR");
            PdfName::$FITB = new PdfName("FitB");
            PdfName::$FITBH = new PdfName("FitBH");
            PdfName::$FITBV = new PdfName("FitBV");
            PdfName::$FITWINDOW = new PdfName("FitWindow");
            PdfName::$FLAGS = new PdfName("Flags");
            PdfName::$FLATEDECODE = new PdfName("FlateDecode");
            PdfName::$FO = new PdfName("Fo");
            PdfName::$FONT = new PdfName("Font");
            PdfName::$FONTBBOX = new PdfName("FontBBox");
            PdfName::$FONTDESCRIPTOR = new PdfName("FontDescriptor");
            PdfName::$FONTFILE = new PdfName("FontFile");
            PdfName::$FONTFILE2 = new PdfName("FontFile2");
            PdfName::$FONTFILE3 = new PdfName("FontFile3");
            PdfName::$FONTNAME = new PdfName("FontName");
            PdfName::$FORM = new PdfName("Form");
            PdfName::$FORMTYPE = new PdfName("FormType");
            PdfName::$FREETEXT = new PdfName("FreeText");
            PdfName::$FRM = new PdfName("FRM");
            PdfName::$FS = new PdfName("FS");
            PdfName::$FT = new PdfName("FT");
            PdfName::$FULLSCREEN = new PdfName("FullScreen");
            PdfName::$FUNCTION = new PdfName("Function");
            PdfName::$FUNCTIONS = new PdfName("Functions");
            PdfName::$FUNCTIONTYPE = new PdfName("FunctionType");
            PdfName::$GAMMA = new PdfName("Gamma");
            PdfName::$GBK = new PdfName("GBK");
            PdfName::$GLITTER = new PdfName("Glitter");
            PdfName::$GOTO = new PdfName("GoTo");
            PdfName::$GOTOR = new PdfName("GoToR");
            PdfName::$GROUP = new PdfName("Group");
            PdfName::$GTS_PDFX = new PdfName("GTS_PDFX");
            PdfName::$GTS_PDFXVERSION = new PdfName("GTS_PDFXVersion");
            PdfName::$H = new PdfName("H");
            PdfName::$HEIGHT = new PdfName("Height");
            PdfName::$HELVETICA = new PdfName("Helvetica");
            PdfName::$HELVETICA_BOLD = new PdfName("Helvetica-Bold");
            PdfName::$HELVETICA_OBLIQUE = new PdfName("Helvetica-Oblique");
            PdfName::$HELVETICA_BOLDOBLIQUE = new PdfName("Helvetica-BoldOblique");
            PdfName::$HID = new PdfName("Hid");
            PdfName::$HIDE = new PdfName("Hide");
            PdfName::$HIDEMENUBAR = new PdfName("HideMenubar");
            PdfName::$HIDETOOLBAR = new PdfName("HideToolbar");
            PdfName::$HIDEWINDOWUI = new PdfName("HideWindowUI");
            PdfName::$HIGHLIGHT = new PdfName("Highlight");
            PdfName::$I = new PdfName("I");
            PdfName::$ICCBASED = new PdfName("ICCBased");
            PdfName::$ID = new PdfName("ID");
            PdfName::$IDENTITY = new PdfName("Identity");
            PdfName::$IF = new PdfName("IF");
            PdfName::$IMAGE = new PdfName("Image");
            PdfName::$IMAGEB = new PdfName("ImageB");
            PdfName::$IMAGEC = new PdfName("ImageC");
            PdfName::$IMAGEI = new PdfName("ImageI");
            PdfName::$IMAGEMASK = new PdfName("ImageMask");
            PdfName::$INDEX = new PdfName("Index");
            PdfName::$INDEXED = new PdfName("Indexed");
            PdfName::$INFO = new PdfName("Info");
            PdfName::$INK = new PdfName("Ink");
            PdfName::$INKLIST = new PdfName("InkList");
            PdfName::$IMPORTDATA = new PdfName("ImportData");
            PdfName::$INTENT = new PdfName("Intent");
            PdfName::$INTERPOLATE = new PdfName("Interpolate");
            PdfName::$ISMAP = new PdfName("IsMap");
            PdfName::$IRT = new PdfName("IRT");
            PdfName::$ITALICANGLE = new PdfName("ItalicAngle");
            PdfName::$IX = new PdfName("IX");
            PdfName::$JAVASCRIPT = new PdfName("JavaScript");
            PdfName::$JS = new PdfName("JS");
            PdfName::$K = new PdfName("K");
            PdfName::$KEYWORDS = new PdfName("Keywords");
            PdfName::$KIDS = new PdfName("Kids");
            PdfName::$L = new PdfName("L");
            PdfName::$L2R = new PdfName("L2R");
            PdfName::$LANG = new PdfName("Lang");
            PdfName::$LANGUAGE = new PdfName("Language");
            PdfName::$LAST = new PdfName("Last");
            PdfName::$LASTCHAR = new PdfName("LastChar");
            PdfName::$LASTPAGE = new PdfName("LastPage");
            PdfName::$LAUNCH = new PdfName("Launch");
            PdfName::$LENGTH = new PdfName("Length");
            PdfName::$LENGTH1 = new PdfName("Length1");
            PdfName::$LIMITS = new PdfName("Limits");
            PdfName::$LINE = new PdfName("Line");
            PdfName::$LINK = new PdfName("Link");
            PdfName::$LISTMODE = new PdfName("ListMode");
            PdfName::$LOCATION = new PdfName("Location");
            PdfName::$LOCK = new PdfName("Lock");
            PdfName::$LZWDECODE = new PdfName("LZWDecode");
            PdfName::$M = new PdfName("M");
            PdfName::$MATRIX = new PdfName("Matrix");
            PdfName::$MAC_EXPERT_ENCODING = new PdfName("MacExpertEncoding");
            PdfName::$MAC_ROMAN_ENCODING = new PdfName("MacRomanEncoding");
            PdfName::$MASK = new PdfName("Mask");
            PdfName::$MAX = new PdfName("max");
            PdfName::$MAXLEN = new PdfName("MaxLen");
            PdfName::$MEDIABOX = new PdfName("MediaBox");
            PdfName::$METADATA = new PdfName("Metadata");
            PdfName::$MIN = new PdfName("min");
            PdfName::$MK = new PdfName("MK");
            PdfName::$MMTYPE1 = new PdfName("MMType1");
            PdfName::$MODDATE = new PdfName("ModDate");
            PdfName::$N = new PdfName("N");
            PdfName::$N0 = new PdfName("n0");
            PdfName::$N1 = new PdfName("n1");
            PdfName::$N2 = new PdfName("n2");
            PdfName::$N3 = new PdfName("n3");
            PdfName::$N4 = new PdfName("n4");
            PdfName::$NAME = new PdfName("Name");
            PdfName::$NAMED = new PdfName("Named");
            PdfName::$NAMES = new PdfName("Names");
            PdfName::$NEEDAPPEARANCES = new PdfName("NeedAppearances");
            PdfName::$NEWWINDOW = new PdfName("NewWindow");
            PdfName::$NEXT = new PdfName("Next");
            PdfName::$NEXTPAGE = new PdfName("NextPage");
            PdfName::$NM = new PdfName("NM");
            PdfName::$NONFULLSCREENPAGEMODE = new PdfName("NonFullScreenPageMode");
            PdfName::$NUMS = new PdfName("Nums");
            PdfName::$O = new PdfName("O");
            PdfName::$OBJSTM = new PdfName("ObjStm");
            PdfName::$OC = new PdfName("OC");
            PdfName::$OCG = new PdfName("OCG");
            PdfName::$OCGS = new PdfName("OCGs");
            PdfName::$OCMD = new PdfName("OCMD");
            PdfName::$OCPROPERTIES = new PdfName("OCProperties");
            PdfName::$Off = new PdfName("Off");
            PdfName::$OFF = new PdfName("OFF");
            PdfName::$ON = new PdfName("ON");
            PdfName::$ONECOLUMN = new PdfName("OneColumn");
            PdfName::$OPEN = new PdfName("Open");
            PdfName::$OPENACTION = new PdfName("OpenAction");
            PdfName::$OP = new PdfName("OP");
            PdfName::$op = new PdfName("op");
            PdfName::$OPM = new PdfName("OPM");
            PdfName::$OPT = new PdfName("Opt");
            PdfName::$ORDER = new PdfName("Order");
            PdfName::$ORDERING = new PdfName("Ordering");
            PdfName::$OUTLINES = new PdfName("Outlines");
            PdfName::$OUTPUTCONDITION = new PdfName("OutputCondition");
            PdfName::$OUTPUTCONDITIONIDENTIFIER = new PdfName("OutputConditionIdentifier");
            PdfName::$OUTPUTINTENT = new PdfName("OutputIntent");
            PdfName::$OUTPUTINTENTS = new PdfName("OutputIntents");
            PdfName::$P = new PdfName("P");
            PdfName::$PAGE = new PdfName("Page");
            PdfName::$PAGELABELS = new PdfName("PageLabels");
            PdfName::$PAGELAYOUT = new PdfName("PageLayout");
            PdfName::$PAGEMODE = new PdfName("PageMode");
            PdfName::$PAGES = new PdfName("Pages");
            PdfName::$PAINTTYPE = new PdfName("PaintType");
            PdfName::$PANOSE = new PdfName("Panose");
            PdfName::$PARENT = new PdfName("Parent");
            PdfName::$PATTERN = new PdfName("Pattern");
            PdfName::$PATTERNTYPE = new PdfName("PatternType");
            PdfName::$PDF = new PdfName("PDF");
            PdfName::$PERCEPTUAL = new PdfName("Perceptual");
            PdfName::$POPUP = new PdfName("Popup");
            PdfName::$PREDICTOR = new PdfName("Predictor");
            PdfName::$PREFERRED = new PdfName("Preferred");
            PdfName::$PRESERVERB = new PdfName("PreserveRB");
            PdfName::$PREV = new PdfName("Prev");
            PdfName::$PREVPAGE = new PdfName("PrevPage");
            PdfName::$PRINT = new PdfName("Print");
            PdfName::$PRINTSTATE = new PdfName("PrintState");
            PdfName::$PROCSET = new PdfName("ProcSet");
            PdfName::$PRODUCER = new PdfName("Producer");
            PdfName::$PROPERTIES = new PdfName("Properties");
            PdfName::$PS = new PdfName("PS");
            PdfName::$Q = new PdfName("Q");
            PdfName::$QUADPOINTS = new PdfName("QuadPoints");
            PdfName::$R = new PdfName("R");
            PdfName::$R2L = new PdfName("R2L");
            PdfName::$RANGE = new PdfName("Range");
            PdfName::$RC = new PdfName("RC");
            PdfName::$RBGROUPS = new PdfName("RBGroups");
            PdfName::$REASON = new PdfName("Reason");
            PdfName::$RECT = new PdfName("Rect");
            PdfName::$REGISTRY = new PdfName("Registry");
            PdfName::$REGISTRYNAME = new PdfName("RegistryName");
            PdfName::$RELATIVECALORIMETRIC = new PdfName("RelativeColorimetric");
            PdfName::$RENDITION = new PdfName("Rendition");
            PdfName::$RESETFORM = new PdfName("ResetForm");
            PdfName::$RESOURCES = new PdfName("Resources");
            PdfName::$RI = new PdfName("RI");
            PdfName::$ROOT = new PdfName("Root");
            PdfName::$ROTATE = new PdfName("Rotate");
            PdfName::$ROWS = new PdfName("Rows");
            PdfName::$RUNLENGTHDECODE = new PdfName("RunLengthDecode");
            PdfName::$RV = new PdfName("RV");
            PdfName::$S = new PdfName("S");
            PdfName::$SATURATION = new PdfName("Saturation");
            PdfName::$SCREEN = new PdfName("Screen");
            PdfName::$SEPARATION = new PdfName("Separation");
            PdfName::$SETOCGSTATE = new PdfName("SetOCGState");
            PdfName::$SHADING = new PdfName("Shading");
            PdfName::$SHADINGTYPE = new PdfName("ShadingType");
            PdfName::$SHIFT_JIS = new PdfName("Shift?JIS");
            PdfName::$SIG = new PdfName("Sig");
            PdfName::$SIGFLAGS = new PdfName("SigFlags");
            PdfName::$SINGLEPAGE = new PdfName("SinglePage");
            PdfName::$SIZE = new PdfName("Size");
            PdfName::$SMASK = new PdfName("SMask");
            PdfName::$SPLIT = new PdfName("Split");
            PdfName::$SQUARE = new PdfName("Square");
            PdfName::$ST = new PdfName("St");
            PdfName::$STAMP = new PdfName("Stamp");
            PdfName::$STANDARD = new PdfName("Standard");
            PdfName::$STATE = new PdfName("State");
            PdfName::$STRIKEOUT = new PdfName("StrikeOut");
            PdfName::$STRUCTPARENT = new PdfName("StructParent");
            PdfName::$STYLE = new PdfName("Style");
            PdfName::$STEMV = new PdfName("StemV");
            PdfName::$SUBFILTER = new PdfName("SubFilter");
            PdfName::$SUBJECT = new PdfName("Subject");
            PdfName::$SUBMITFORM = new PdfName("SubmitForm");
            PdfName::$SUBTYPE = new PdfName("Subtype");
            PdfName::$SUPPLEMENT = new PdfName("Supplement");
            PdfName::$SV = new PdfName("SV");
            PdfName::$SW = new PdfName("SW");
            PdfName::$SYMBOL = new PdfName("Symbol");
            PdfName::$T = new PdfName("T");
            PdfName::$TEXT = new PdfName("Text");
            PdfName::$THUMB = new PdfName("Thumb");
            PdfName::$THREADS = new PdfName("Threads");
            PdfName::$TI = new PdfName("TI");
            PdfName::$TILINGTYPE = new PdfName("TilingType");
            PdfName::$TIMES_ROMAN = new PdfName("Times-Roman");
            PdfName::$TIMES_BOLD = new PdfName("Times-Bold");
            PdfName::$TIMES_ITALIC = new PdfName("Times-Italic");
            PdfName::$TIMES_BOLDITALIC = new PdfName("Times-BoldItalic");
            PdfName::$TITLE = new PdfName("Title");
            PdfName::$TK = new PdfName("TK");
            PdfName::$TM = new PdfName("TM");
            PdfName::$TOGGLE = new PdfName("Toggle");
            PdfName::$TOUNICODE = new PdfName("ToUnicode");
            PdfName::$TP = new PdfName("TP");
            PdfName::$TRANS = new PdfName("Trans");
            PdfName::$TRANSPARENCY = new PdfName("Transparency");
            PdfName::$TRAPPED = new PdfName("Trapped");
            PdfName::$TRIMBOX = new PdfName("TrimBox");
            PdfName::$TRUETYPE = new PdfName("TrueType");
            PdfName::$TU = new PdfName("TU");
            PdfName::$TWOCOLUMNLEFT = new PdfName("TwoColumnLeft");
            PdfName::$TWOCOLUMNRIGHT = new PdfName("TwoColumnRight");
            PdfName::$TX = new PdfName("Tx");
            PdfName::$TYPE = new PdfName("Type");
            PdfName::$TYPE0 = new PdfName("Type0");
            PdfName::$TYPE1 = new PdfName("Type1");
            PdfName::$U = new PdfName("U");
            PdfName::$UHC = new PdfName("UHC");
            PdfName::$UNDERLINE = new PdfName("Underline");
            PdfName::$URI = new PdfName("URI");
            PdfName::$URL = new PdfName("URL");
            PdfName::$USAGE = new PdfName("Usage");
            PdfName::$USENONE = new PdfName("UseNone");
            PdfName::$USEOC = new PdfName("UseOC");
            PdfName::$USEOUTLINES = new PdfName("UseOutlines");
            PdfName::$USER = new PdfName("User");
            PdfName::$USETHUMBS = new PdfName("UseThumbs");
            PdfName::$V = new PdfName("V");
            PdfName::$VERISIGN_PPKVS = new PdfName("VeriSign.PPKVS");
            PdfName::$VIEW = new PdfName("View");
            PdfName::$VIEWERPREFERENCES = new PdfName("ViewerPreferences");
            PdfName::$VIEWSTATE = new PdfName("ViewState");
            PdfName::$VISIBLEPAGES = new PdfName("VisiblePages");
            PdfName::$W = new PdfName("W");
            PdfName::$W2 = new PdfName("W2");
            PdfName::$WC = new PdfName("WC");
            PdfName::$WIDGET = new PdfName("Widget");
            PdfName::$WIDTH = new PdfName("Width");
            PdfName::$WIDTHS = new PdfName("Widths");
            PdfName::$WIN = new PdfName("Win");
            PdfName::$WIN_ANSI_ENCODING = new PdfName("WinAnsiEncoding");
            PdfName::$WIPE = new PdfName("Wipe");
            PdfName::$WHITEPOINT = new PdfName("WhitePoint");
            PdfName::$WP = new PdfName("WP");
            PdfName::$WS = new PdfName("WS");
            PdfName::$X = new PdfName("X");
            PdfName::$XOBJECT = new PdfName("XObject");
            PdfName::$XSTEP = new PdfName("XStep");
            PdfName::$XREF = new PdfName("XRef");
            PdfName::$XREFSTM = new PdfName("XRefStm");
            PdfName::$XYZ = new PdfName("XYZ");
            PdfName::$YSTEP = new PdfName("YStep");
            PdfName::$ZAPFDINGBATS = new PdfName("ZapfDingbats");
            PdfName::$ZOOM = new PdfName("Zoom");
            PdfName::$initialized = TRUE;
        }


    }

    // constructors

    /**
     * Constructs a <CODE>PdfName</CODE>-object.
     *
     * @param		name		the new Name.
     */

    public function __construct() {

        $num_args=func_num_args();
        switch ($num_args)
        {
            case 1:
            {
                $param = func_get_arg(0);
                if (strcmp(gettype($param),"resource")==0)
                {
                   construct1argbytes($arg1);
                }
                else
                {

                   // parent::__construct(PdfObject::NAME);
        // The minimum number of characters in a name is 0, the maximum is 127 (the '/' not included)
                    construct1argString($arg1);
                }
                break;
            }
        }


    }

    private function construct1argString($name)
    {
        parent::__construct(PdfObject::NAME);
        // The minimum number of characters in a name is 0, the maximum is 127 (the '/' not included)
        $length = strlen($name);
        if ($length > 127) {
            throw new IllegalArgumentException("The name is too long (" + bytes.length + " characters).");
        }
        // The name has to be checked for illegal characters
        // every special character has to be substituted
        $pdfName = new ByteBuffer(length + 20);
        $pdfName->append('/');
        $character;
        $chars = $name;
        // loop over all the characters
        for ($index = 0; $index < $length; $index++) {
            $character = ($chars[$index] & 0xff);
            // special characters are escaped (reference manual p.39)
            switch ($character) {
                case ' ':
                case '%':
                case '(':
                case ')':
                case '<':
                case '>':
                case '[':
                case ']':
                case '{':
                case '}':
                case '/':
                case '#':
                    $pdfName->append('#');
                    $pdfName->append(intval($character, 16));
                    break;
                default:
                    $tmpchar = itextphp_char_create($character);
                    $characteri = itextphp_char_getIntRep($tmpchar, 0);
                    if ($characteri > 126 || $characteri < 32) {
                        $pdfName->append('#');
                        if ($characteri < 16)
                            $pdfName->append('0');
                        $pdfName->append(intval($character, 16));
                    }
                    else
                        $pdfName->append($character);
                    break;
            }
        }
        $bytes = $pdfName->toByteArray();
    }
    private function construct1argbytes($bytes)
    {
        parent::__construct(PdfObject::NAME, $bytes);
    }

    // methods

    /**
    * Compares this object with the specified object for order.  Returns a
    * negative integer, zero, or a positive integer as this object is less
    * than, equal to, or greater than the specified object.<p>
    *
    *
    * @param   object the Object to be compared.
    * @return  a negative integer, zero, or a positive integer as this object
    *		is less than, equal to, or greater than the specified object.
    *
    * @throws ClassCastException if the specified object's type prevents it
    *         from being compared to this Object.
    */
    public function compareTo($object) {
        $name = $object;

        $myBytes = $bytes;
        $objBytes = $name->bytes;
        $len = min(itextphp_bytes_getSize($myBytes), itextphp_bytes_getSize($objBytes));
        for($i=0; $i<$len; $i++) {
            if(itextphp_bytes_greaterthanoperator($myBytes, $objBytes, $i, $i) == TRUE)
                return 1;

            if(itextphp_bytes_lessthanoperator($myBytes, $objBytes, $i, $i) == TRUE)
                return -1;
        }
        if (itextphp_bytes_getSize($myBytes) < itextphp_bytes_getSize($objBytes))
            return -1;
        if (itextphp_bytes_getSize($myBytes) > itextphp_bytes_getSize($objBytes))
            return 1;
        return 0;
    }


    /**
    * Indicates whether some other object is "equal to" this one.
    *
    * @param   obj   the reference object with which to compare.
    * @return  <code>true</code> if this object is the same as the obj
    *          argument; <code>false</code> otherwise.
    */
    public function equals($obj) {
        if ($this == $obj)
            return TRUE;
        if ($obj instanceof PdfName)
            return compareTo($obj) == 0;
        return FALSE;
    }

    /**
    * Returns a hash code value for the object. This method is
    * supported for the benefit of hashtables such as those provided by
    * <code>java.util.Hashtable</code>.
    *
    * @return  a hash code value for this object.
    */
    public function hashCode() {
        $h = $hash;
        if ($h == 0) {
            $ptr = 0;
            $len = itextphp_bytes_getSize($bytes);

            for ($i = 0; $i < $len; $i++)
                $h = 31*$h + (itextphp_bytes_getIntValue($bytes, $ptr++, 0xff));
            $hash = $h;
        }
        return $h;
    }

    /** Decodes an escaped name in the form "/AB#20CD" into "AB CD".
    * @param name the name to decode
    * @return the decoded name
    */
    public static function decodeName($name) {
        $buf = "";
        //try {
            $len = strlen($name);
            
            for ($k = 1; $k < $len; ++$k) {
                //$c = name.charAt(k);
                $c = $name[$k];
                if ($name[$k] == '#') {
                    $newchar1 = itextphp_char_create($name[$k+1]);
                    $newchar2 = itextphp_char_create($name[$k+2]);
                    $c = (PRTokeniser::getHex(itextphp_char_getIntRep($newchar1, 0) << 4)) + (PRTokeniser::getHex(itextphp_char_getIntRep($newchar2, 0)));
                    $k = $k + 2;
                }
                $buf  = $buf . $c;
            }
        //}
        //catch (IndexOutOfBoundsException e) {
            // empty on purpose
        //}
        return $buf;
    }

}


?>