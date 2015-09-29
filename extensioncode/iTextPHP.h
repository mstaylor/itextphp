#ifndef PHP_ITEXTPHP_H
#define PHP_ITEXTPHP_H

#include "php.h"

#define PHP_ITEXTPHP_VERSION "1.0"
#define PHP_ITEXTPHP_EXTNAME "iTextPHP"
#define le_itextphpByteResourceName  "aByte"
#define le_itextphpanotherByteResourceName  "anotherByte"
#define le_itextphpStringResourceName  "aString"
#define le_itextphpCharResourceName  "aChar"

#define STRINGANSI 1
#define STRINGWIDE 2
#define ALREADYWIDE 3
#define NOTTA  0


extern int le_itextphpanotherByteResource;
extern int le_itextphpByteResource;
extern int le_itextphpStringResource;
extern int le_itextphpCharResource;



typedef struct byte
{
    unsigned char * theByte;
    int pointer;
    int size;
} byte;




PHP_FUNCTION(iTextPHP_hello_world);
PHP_FUNCTION(iTextPHP_floatToIntBits);


PHP_FUNCTION(itextphp_bytes_getSize);
PHP_FUNCTION(itextphp_bytes_create);
PHP_FUNCTION(itextphp_bytes_getIntValue);
PHP_FUNCTION(itextphp_bytes_createfromRaw);
PHP_FUNCTION(itextphp_bytes_createfromInt);
PHP_FUNCTION(itextphp_bytes_createfromChar);


/*** string functions ***/

PHP_FUNCTION(itextphp_newString);
PHP_FUNCTION(itextphp_StringLength);
PHP_FUNCTION(itextphp_newStringfromChar);
PHP_FUNCTION(itextphp_string_startswith);
PHP_FUNCTION(itextphp_string_append);
PHP_FUNCTION(itextphp_string_getIntFromIndex);
PHP_FUNCTION(itextphp_string_substring);
PHP_FUNCTION(itextphp_string_lastIndexOf);
PHP_FUNCTION(itextphp_string_equals);
PHP_FUNCTION(itextphp_string_toPHPString);
PHP_FUNCTION(itextphp_string_toInteger);

/*** wingdings conversion functions ***/

PHP_FUNCTION(itextphp_wingdings_chartobyte);

/*** cp437 conversion functions ***/
PHP_FUNCTION(itextphp_cp437_initialize);
PHP_FUNCTION(itextphp_cp437_chartobyte);
PHP_FUNCTION(itextphp_cp437_bytetochar);

/** symbolconversion functions **/
PHP_FUNCTION(itextphp_symbolconversion_initialize);
PHP_FUNCTION(itextphp_symbolconversion_chartobyte);

/*** pdfencodings Functions ****/
PHP_FUNCTION(itextphp_charToByte);
PHP_FUNCTION(itextphp_charToBytePDFDocEncoding);
PHP_FUNCTION(itextphp_charToByteUnicodeEncoding);
PHP_FUNCTION(itextphp_byteToString);
PHP_FUNCTION(itextphp_byteToStringPDFDocEncoding);
PHP_FUNCTION(itextphp_byteToStringUnicodeEncoding);
PHP_FUNCTION(itextphp_initializepdfencodingsConstants);
PHP_FUNCTION(itextphp_isPdfDocEncodings);
PHP_FUNCTION(itextphp_decodesequence);
PHP_FUNCTION(itextphp_breaklong);
PHP_FUNCTION(itextphp_encodesequence);
PHP_FUNCTION(itextphp_getWinAnsiValue);






void itextphpByteDestructionHandler(zend_rsrc_list_entry *rsrc TSRMLS_DC);
PHP_MINIT_FUNCTION(iTextPHP);


extern zend_module_entry iTextPHP_module_entry;
#define phpext_iTextPHP_ptr &iTextPHP_module_entry

#endif
