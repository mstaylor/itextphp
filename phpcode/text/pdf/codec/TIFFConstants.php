<?PHP
/*
 * $Id: TIFFConstants.php,v 1.1 2005/11/10 23:08:24 mstaylor Exp $
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
* A baseline TIFF reader. The reader has some functionality in addition to
* the baseline specifications for Bilevel images, for which the group 3 and
* group 4 decompression schemes have been implemented. Support for LZW
* decompression has also been added. Support for Horizontal differencing
* predictor decoding is also included, when used with LZW compression.
* However, this support is limited to data with bitsPerSample value of 8.
* When reading in RGB images, support for alpha and extraSamples being
* present has been added. Support for reading in images with 16 bit samples
* has been added. Support for the SampleFormat tag (signed samples as well
* as floating-point samples) has also been added. In all other cases, support
* is limited to Baseline specifications.
*
*
*/


class TIFFConstants
{

    /*
    * TIFF Tag Definitions (from tifflib).
    */
    const TIFFTAG_SUBFILETYPE = 254;	/* subfile data descriptor */
    const FILETYPE_REDUCEDIMAGE = 0x1;	/* reduced resolution version */
    const FILETYPE_PAGE = 0x2;	/* one page of many */
    const FILETYPE_MASK = 0x4;	/* transparency mask */
    const TIFFTAG_OSUBFILETYPE = 255; /* +kind of data in subfile */
    const OFILETYPE_IMAGE = 1; /* full resolution image data */
    const OFILETYPE_REDUCEDIMAGE = 2; /* reduced size image data */
    const OFILETYPE_PAGE = 3; /* one page of many */
    const TIFFTAG_IMAGEWIDTH = 256; /* image width in pixels */
    const TIFFTAG_IMAGELENGTH = 257; /* image height in pixels */
    const TIFFTAG_BITSPERSAMPLE = 258; /* bits per channel (sample) */
    const TIFFTAG_COMPRESSION = 259; /* data compression technique */
    const COMPRESSION_NONE = 1; /* dump mode */
    const COMPRESSION_CCITTRLE = 2; /* CCITT modified Huffman RLE */
    const COMPRESSION_CCITTFAX3 = 3;	/* CCITT Group 3 fax encoding */
    const COMPRESSION_CCITTFAX4 = 4;	/* CCITT Group 4 fax encoding */
    const COMPRESSION_LZW = 5;       /* Lempel-Ziv  & Welch */
    const COMPRESSION_OJPEG = 6; /* !6.0 JPEG */
    const COMPRESSION_JPEG = 7; /* %JPEG DCT compression */
    const COMPRESSION_NEXT = 32766; /* NeXT 2-bit RLE */
    const COMPRESSION_CCITTRLEW = 32771; /* #1 w/ word alignment */
    const COMPRESSION_PACKBITS = 32773; /* Macintosh RLE */
    const COMPRESSION_THUNDERSCAN = 32809; /* ThunderScan RLE */
    /* codes 32895-32898 are reserved for ANSI IT8 TIFF/IT <dkelly@etsinc.com) */
    const COMPRESSION_IT8CTPAD = 32895;   /* IT8 CT w/padding */
    const COMPRESSION_IT8LW = 32896;   /* IT8 Linework RLE */
    const COMPRESSION_IT8MP = 32897;   /* IT8 Monochrome picture */
    const COMPRESSION_IT8BL = 32898;   /* IT8 Binary line art */
    /* compression codes 32908-32911 are reserved for Pixar */
    const COMPRESSION_PIXARFILM = 32908;   /* Pixar companded 10bit LZW */
    const COMPRESSION_PIXARLOG = 32909;   /* Pixar companded 11bit ZIP */
    const COMPRESSION_DEFLATE = 32946; /* Deflate compression */
    const COMPRESSION_ADOBE_DEFLATE = 8;       /* Deflate compression, as recognized by Adobe */
    /* compression code 32947 is reserved for Oceana Matrix <dev@oceana.com> */
    const COMPRESSION_DCS = 32947;   /* Kodak DCS encoding */
    const COMPRESSION_JBIG = 34661; /* ISO JBIG */
    const COMPRESSION_SGILOG = 34676; /* SGI Log Luminance RLE */
    const COMPRESSION_SGILOG24 = 34677;	/* SGI Log 24-bit packed */
    const TIFFTAG_PHOTOMETRIC = 262; /* photometric interpretation */
    const PHOTOMETRIC_MINISWHITE = 0; /* min value is white */
    const PHOTOMETRIC_MINISBLACK = 1; /* min value is black */
    const PHOTOMETRIC_RGB = 2; /* RGB color model */
    const PHOTOMETRIC_PALETTE = 3; /* color map indexed */
    const PHOTOMETRIC_MASK = 4; /* $holdout mask */
    const PHOTOMETRIC_SEPARATED = 5; /* !color separations */
    const PHOTOMETRIC_YCBCR = 6; /* !CCIR 601 */
    const PHOTOMETRIC_CIELAB = 8; /* !1976 CIE L*a*b* */
    const PHOTOMETRIC_LOGL = 32844; /* CIE Log2(L) */
    const PHOTOMETRIC_LOGLUV = 32845; /* CIE Log2(L) (u',v') */
    const TIFFTAG_THRESHHOLDING = 263; /* +thresholding used on data */
    const THRESHHOLD_BILEVEL = 1; /* b&w art scan */
    const THRESHHOLD_HALFTONE = 2; /* or dithered scan */
    const THRESHHOLD_ERRORDIFFUSE = 3; /* usually floyd-steinberg */
    const TIFFTAG_CELLWIDTH = 264; /* +dithering matrix width */
    const TIFFTAG_CELLLENGTH = 265; /* +dithering matrix height */
    const TIFFTAG_FILLORDER = 266; /* data order within a byte */
    const FILLORDER_MSB2LSB = 1; /* most significant -> least */
    const FILLORDER_LSB2MSB = 2; /* least significant -> most */
    const TIFFTAG_DOCUMENTNAME = 269; /* name of doc. image is from */
    const TIFFTAG_IMAGEDESCRIPTION = 270; /* info about image */
    const TIFFTAG_MAKE = 271; /* scanner manufacturer name */
    const TIFFTAG_MODEL = 272; /* scanner model name/number */
    const TIFFTAG_STRIPOFFSETS = 273; /* offsets to data strips */
    const TIFFTAG_ORIENTATION = 274; /* +image orientation */
    const ORIENTATION_TOPLEFT = 1; /* row 0 top, col 0 lhs */
    const ORIENTATION_TOPRIGHT = 2; /* row 0 top, col 0 rhs */
    const ORIENTATION_BOTRIGHT = 3; /* row 0 bottom, col 0 rhs */
    const ORIENTATION_BOTLEFT = 4; /* row 0 bottom, col 0 lhs */
    const ORIENTATION_LEFTTOP = 5; /* row 0 lhs, col 0 top */
    const ORIENTATION_RIGHTTOP = 6; /* row 0 rhs, col 0 top */
    const ORIENTATION_RIGHTBOT = 7; /* row 0 rhs, col 0 bottom */
    const ORIENTATION_LEFTBOT = 8; /* row 0 lhs, col 0 bottom */
    const TIFFTAG_SAMPLESPERPIXEL = 277; /* samples per pixel */
    const TIFFTAG_ROWSPERSTRIP = 278; /* rows per strip of data */
    const TIFFTAG_STRIPBYTECOUNTS = 279; /* bytes counts for strips */
    const TIFFTAG_MINSAMPLEVALUE = 280; /* +minimum sample value */
    const TIFFTAG_MAXSAMPLEVALUE = 281; /* +maximum sample value */
    const TIFFTAG_XRESOLUTION = 282; /* pixels/resolution in x */
    const TIFFTAG_YRESOLUTION = 283; /* pixels/resolution in y */
    const TIFFTAG_PLANARCONFIG = 284; /* storage organization */
    const PLANARCONFIG_CONTIG = 1; /* single image plane */
    const PLANARCONFIG_SEPARATE = 2; /* separate planes of data */
    const TIFFTAG_PAGENAME = 285; /* page name image is from */
    const TIFFTAG_XPOSITION = 286; /* x page offset of image lhs */
    const TIFFTAG_YPOSITION = 287; /* y page offset of image lhs */
    const TIFFTAG_FREEOFFSETS = 288; /* +byte offset to free block */
    const TIFFTAG_FREEBYTECOUNTS = 289; /* +sizes of free blocks */
    const TIFFTAG_GRAYRESPONSEUNIT = 290; /* $gray scale curve accuracy */
    const GRAYRESPONSEUNIT_10S = 1; /* tenths of a unit */
    const GRAYRESPONSEUNIT_100S = 2; /* hundredths of a unit */
    const GRAYRESPONSEUNIT_1000S = 3; /* thousandths of a unit */
    const GRAYRESPONSEUNIT_10000S = 4; /* ten-thousandths of a unit */
    const GRAYRESPONSEUNIT_100000S = 5; /* hundred-thousandths */
    const TIFFTAG_GRAYRESPONSECURVE = 291; /* $gray scale response curve */
    const TIFFTAG_GROUP3OPTIONS = 292; /* 32 flag bits */
    const GROUP3OPT_2DENCODING = 0x1;	/* 2-dimensional coding */
    const GROUP3OPT_UNCOMPRESSED = 0x2;	/* data not compressed */
    const GROUP3OPT_FILLBITS = 0x4;	/* fill to byte boundary */
    const TIFFTAG_GROUP4OPTIONS = 293; /* 32 flag bits */
    const GROUP4OPT_UNCOMPRESSED = 0x2;	/* data not compressed */
    const TIFFTAG_RESOLUTIONUNIT = 296; /* units of resolutions */
    const RESUNIT_NONE = 1; /* no meaningful units */
    const RESUNIT_INCH = 2; /* english */
    const RESUNIT_CENTIMETER = 3;	/* metric */
    const TIFFTAG_PAGENUMBER = 297;	/* page numbers of multi-page */
    const TIFFTAG_COLORRESPONSEUNIT = 300;	/* $color curve accuracy */
    const COLORRESPONSEUNIT_10S = 1;	/* tenths of a unit */
    const COLORRESPONSEUNIT_100S = 2;	/* hundredths of a unit */
    const COLORRESPONSEUNIT_1000S = 3;	/* thousandths of a unit */
    const COLORRESPONSEUNIT_10000S = 4;	/* ten-thousandths of a unit */
    const COLORRESPONSEUNIT_100000S = 5;	/* hundred-thousandths */
    const TIFFTAG_TRANSFERFUNCTION = 301;	/* !colorimetry info */
    const TIFFTAG_SOFTWARE = 305;	/* name & release */
    const TIFFTAG_DATETIME = 306;	/* creation date and time */
    const TIFFTAG_ARTIST = 315;	/* creator of image */
    const TIFFTAG_HOSTCOMPUTER = 316;	/* machine where created */
    const TIFFTAG_PREDICTOR = 317;	/* prediction scheme w/ LZW */
    const TIFFTAG_WHITEPOINT = 318;	/* image white point */
    const TIFFTAG_PRIMARYCHROMATICITIES = 319;	/* !primary chromaticities */
    const TIFFTAG_COLORMAP = 320;	/* RGB map for pallette image */
    const TIFFTAG_HALFTONEHINTS = 321;	/* !highlight+shadow info */
    const TIFFTAG_TILEWIDTH = 322;	/* !rows/data tile */
    const TIFFTAG_TILELENGTH = 323;	/* !cols/data tile */
    const TIFFTAG_TILEOFFSETS = 324;	/* !offsets to data tiles */
    const TIFFTAG_TILEBYTECOUNTS = 325;	/* !byte counts for tiles */
    const TIFFTAG_BADFAXLINES = 326;	/* lines w/ wrong pixel count */
    const TIFFTAG_CLEANFAXDATA = 327;	/* regenerated line info */
    const CLEANFAXDATA_CLEAN = 0;	/* no errors detected */
    const CLEANFAXDATA_REGENERATED = 1;	/* receiver regenerated lines */
    const CLEANFAXDATA_UNCLEAN = 2;	/* uncorrected errors exist */
    const TIFFTAG_CONSECUTIVEBADFAXLINES = 328;	/* max consecutive bad lines */
    const TIFFTAG_SUBIFD = 330;	/* subimage descriptors */
    const TIFFTAG_INKSET = 332;	/* !inks in separated image */
    const INKSET_CMYK = 1;	/* !cyan-magenta-yellow-black */
    const TIFFTAG_INKNAMES = 333;	/* !ascii names of inks */
    const TIFFTAG_NUMBEROFINKS = 334;	/* !number of inks */
    const TIFFTAG_DOTRANGE = 336;	/* !0% and 100% dot codes */
    const TIFFTAG_TARGETPRINTER = 337;	/* !separation target */
    const TIFFTAG_EXTRASAMPLES = 338;	/* !info about extra samples */
    const EXTRASAMPLE_UNSPECIFIED = 0;	/* !unspecified data */
    const EXTRASAMPLE_ASSOCALPHA = 1;	/* !associated alpha data */
    const EXTRASAMPLE_UNASSALPHA = 2;	/* !unassociated alpha data */
    const TIFFTAG_SAMPLEFORMAT = 339;	/* !data sample format */
    const SAMPLEFORMAT_UINT = 1;	/* !unsigned integer data */
    const SAMPLEFORMAT_INT = 2;	/* !signed integer data */
    const SAMPLEFORMAT_IEEEFP = 3;	/* !IEEE floating point data */
    const SAMPLEFORMAT_VOID = 4;	/* !untyped data */
    const SAMPLEFORMAT_COMPLEXINT = 5;	/* !complex signed int */
    const SAMPLEFORMAT_COMPLEXIEEEFP = 6;	/* !complex ieee floating */
    const TIFFTAG_SMINSAMPLEVALUE = 340;	/* !variable MinSampleValue */
    const TIFFTAG_SMAXSAMPLEVALUE = 341;	/* !variable MaxSampleValue */
    const TIFFTAG_JPEGTABLES = 347;	/* %JPEG table stream */
    /*
    * Tags 512-521 are obsoleted by Technical Note #2
    * which specifies a revised JPEG-in-TIFF scheme.
    */
    const TIFFTAG_JPEGPROC = 512;	/* !JPEG processing algorithm */
    const JPEGPROC_BASELINE = 1;	/* !baseline sequential */
    const JPEGPROC_LOSSLESS = 14;	/* !Huffman coded lossless */
    const TIFFTAG_JPEGIFOFFSET = 513;	/* !pointer to SOI marker */
    const TIFFTAG_JPEGIFBYTECOUNT = 514;	/* !JFIF stream length */
    const TIFFTAG_JPEGRESTARTINTERVAL = 515;	/* !restart interval length */
    const TIFFTAG_JPEGLOSSLESSPREDICTORS = 517;	/* !lossless proc predictor */
    const TIFFTAG_JPEGPOINTTRANSFORM = 518;	/* !lossless point transform */
    const TIFFTAG_JPEGQTABLES = 519;	/* !Q matrice offsets */
    const TIFFTAG_JPEGDCTABLES = 520;	/* !DCT table offsets */
    const TIFFTAG_JPEGACTABLES = 521;	/* !AC coefficient offsets */
    const TIFFTAG_YCBCRCOEFFICIENTS = 529;	/* !RGB -> YCbCr transform */
    const TIFFTAG_YCBCRSUBSAMPLING = 530;	/* !YCbCr subsampling factors */
    const TIFFTAG_YCBCRPOSITIONING = 531;	/* !subsample positioning */
    const YCBCRPOSITION_CENTERED = 1;	/* !as in PostScript Level 2 */
    const YCBCRPOSITION_COSITED = 2;	/* !as in CCIR 601-1 */
    const TIFFTAG_REFERENCEBLACKWHITE = 532;	/* !colorimetry info */
    /* tags 32952-32956 are private tags registered to Island Graphics */
    const TIFFTAG_REFPTS = 32953;	/* image reference points */
    const TIFFTAG_REGIONTACKPOINT = 32954;	/* region-xform tack point */
    const TIFFTAG_REGIONWARPCORNERS = 32955;	/* warp quadrilateral */
    const TIFFTAG_REGIONAFFINE = 32956;	/* affine transformation mat */
    /* tags 32995-32999 are private tags registered to SGI */
    const TIFFTAG_MATTEING = 32995;	/* $use ExtraSamples */
    const TIFFTAG_DATATYPE = 32996;	/* $use SampleFormat */
    const TIFFTAG_IMAGEDEPTH = 32997;	/* z depth of image */
    const TIFFTAG_TILEDEPTH = 32998;	/* z depth/data tile */
    /* tags 33300-33309 are private tags registered to Pixar */
    /*
    * TIFFTAG_PIXAR_IMAGEFULLWIDTH and TIFFTAG_PIXAR_IMAGEFULLLENGTH
    * are set when an image has been cropped out of a larger image.
    * They reflect the size of the original uncropped image.
    * The TIFFTAG_XPOSITION and TIFFTAG_YPOSITION can be used
    * to determine the position of the smaller image in the larger one.
    */
    const TIFFTAG_PIXAR_IMAGEFULLWIDTH = 33300;   /* full image size in x */
    const TIFFTAG_PIXAR_IMAGEFULLLENGTH = 33301;   /* full image size in y */
    /* Tags 33302-33306 are used to identify special image modes and data
    * used by Pixar's texture formats.
    */
    const TIFFTAG_PIXAR_TEXTUREFORMAT = 33302;	/* texture map format */
    const TIFFTAG_PIXAR_WRAPMODES = 33303;	/* s & t wrap modes */
    const TIFFTAG_PIXAR_FOVCOT = 33304;	/* cotan(fov) for env. maps */
    const TIFFTAG_PIXAR_MATRIX_WORLDTOSCREEN = 33305;
    const TIFFTAG_PIXAR_MATRIX_WORLDTOCAMERA = 33306;
    /* tag 33405 is a private tag registered to Eastman Kodak */
    const TIFFTAG_WRITERSERIALNUMBER = 33405;   /* device serial number */
    /* tag 33432 is listed in the 6.0 spec w/ unknown ownership */
    const TIFFTAG_COPYRIGHT = 33432;	/* copyright string */
    /* IPTC TAG from RichTIFF specifications */
    const TIFFTAG_RICHTIFFIPTC = 33723;
    /* 34016-34029 are reserved for ANSI IT8 TIFF/IT <dkelly@etsinc.com) */
    const TIFFTAG_IT8SITE = 34016;	/* site name */
    const TIFFTAG_IT8COLORSEQUENCE = 34017;	/* color seq. [RGB,CMYK,etc] */
    const TIFFTAG_IT8HEADER = 34018;	/* DDES Header */
    const TIFFTAG_IT8RASTERPADDING = 34019;	/* raster scanline padding */
    const TIFFTAG_IT8BITSPERRUNLENGTH = 34020;	/* # of bits in short run */
    const TIFFTAG_IT8BITSPEREXTENDEDRUNLENGTH = 34021;/* # of bits in long run */
    const TIFFTAG_IT8COLORTABLE = 34022;	/* LW colortable */
    const TIFFTAG_IT8IMAGECOLORINDICATOR = 34023;	/* BP/BL image color switch */
    const TIFFTAG_IT8BKGCOLORINDICATOR = 34024;	/* BP/BL bg color switch */
    const TIFFTAG_IT8IMAGECOLORVALUE = 34025;	/* BP/BL image color value */
    const TIFFTAG_IT8BKGCOLORVALUE = 34026;	/* BP/BL bg color value */
    const TIFFTAG_IT8PIXELINTENSITYRANGE = 34027;	/* MP pixel intensity value */
    const TIFFTAG_IT8TRANSPARENCYINDICATOR = 34028;	/* HC transparency switch */
    const TIFFTAG_IT8COLORCHARACTERIZATION = 34029;	/* color character. table */
    /* tags 34232-34236 are private tags registered to Texas Instruments */
    const TIFFTAG_FRAMECOUNT = 34232;   /* Sequence Frame Count */
    /* tag 34750 is a private tag registered to Adobe? */
    const TIFFTAG_ICCPROFILE = 34675;	/* ICC profile data */
    /* tag 34377 is private tag registered to Adobe for PhotoShop */
    const TIFFTAG_PHOTOSHOP = 34377;
    /* tag 34750 is a private tag registered to Pixel Magic */
    const TIFFTAG_JBIGOPTIONS = 34750;	/* JBIG options */
    /* tags 34908-34914 are private tags registered to SGI */
    const TIFFTAG_FAXRECVPARAMS = 34908;	/* encoded Class 2 ses. parms */
    const TIFFTAG_FAXSUBADDRESS = 34909;	/* received SubAddr string */
    const TIFFTAG_FAXRECVTIME = 34910;	/* receive time (secs) */
    /* tags 37439-37443 are registered to SGI <gregl@sgi.com> */
    const TIFFTAG_STONITS = 37439;	/* Sample value to Nits */
    /* tag 34929 is a private tag registered to FedEx */
    const TIFFTAG_FEDEX_EDR = 34929;	/* unknown use */
    /* tag 65535 is an undefined tag used by Eastman Kodak */
    const TIFFTAG_DCSHUESHIFTVALUES = 65535;   /* hue shift correction data */

}


?>