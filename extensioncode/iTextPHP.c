#ifdef HAVE_CONFIG_H
#include "config.h"
#endif



#include "iTextPHP.h"

int le_itextphpByteResource;
int le_itextphpanotherByteResource;
int le_itextphpStringResource;
int le_itextphpCharResource;

static function_entry iTextPHP_functions[] = {
    PHP_FE(iTextPHP_hello_world, NULL)
    PHP_FE(iTextPHP_floatToIntBits, NULL)
    PHP_FE(itextphp_bytes_getSize, NULL)
    PHP_FE(itextphp_bytes_create, NULL)
    PHP_FE(itextphp_bytes_createfromRaw, NULL)
    PHP_FE(itextphp_bytes_createfromInt, NULL)
    PHP_FE(itextphp_bytes_createfromChar, NULL)
    PHP_FE(itextphp_bytes_getIntValue, NULL)
    PHP_FE(itextphp_newString, NULL)
    PHP_FE(itextphp_StringLength, NULL)
    PHP_FE(itextphp_newStringfromChar, NULL)
    PHP_FE(itextphp_string_startswith, NULL)
    PHP_FE(itextphp_string_append, NULL)
    PHP_FE(itextphp_string_getIntFromIndex, NULL)
    PHP_FE(itextphp_string_substring, NULL)
    PHP_FE(itextphp_string_lastIndexOf, NULL)
    PHP_FE(itextphp_string_equals, NULL)
    PHP_FE(itextphp_string_toPHPString, NULL)
    PHP_FE(itextphp_string_toInteger, NULL)
    PHP_FE(itextphp_wingdings_chartobyte, NULL)
    PHP_FE(itextphp_cp437_initialize, NULL)
    PHP_FE(itextphp_cp437_chartobyte, NULL)
    PHP_FE(itextphp_cp437_bytetochar, NULL)
    PHP_FE(itextphp_symbolconversion_initialize, NULL)
    PHP_FE(itextphp_symbolconversion_chartobyte, NULL)
    PHP_FE(itextphp_charToByte, NULL)
    PHP_FE(itextphp_charToBytePDFDocEncoding, NULL)
    PHP_FE(itextphp_charToByteUnicodeEncoding, NULL)
    PHP_FE(itextphp_byteToString, NULL)
    PHP_FE(itextphp_byteToStringPDFDocEncoding, NULL)
    PHP_FE(itextphp_byteToStringUnicodeEncoding, NULL)
    PHP_FE(itextphp_initializepdfencodingsConstants, NULL)
    PHP_FE(itextphp_isPdfDocEncodings, NULL)
    PHP_FE(itextphp_decodesequence, NULL)
    PHP_FE(itextphp_breaklong, NULL)
    PHP_FE(itextphp_encodesequence, NULL)
    PHP_FE(itextphp_getWinAnsiValue, NULL)
    {NULL, NULL, NULL}
};

zend_module_entry iTextPHP_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
    STANDARD_MODULE_HEADER,
#endif
    PHP_ITEXTPHP_EXTNAME,
    iTextPHP_functions,
    PHP_MINIT(iTextPHP),
    NULL,
    NULL,
    NULL,
    NULL,
#if ZEND_MODULE_API_NO >= 20010901
    PHP_ITEXTPHP_VERSION,
#endif
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_ITEXTPHP
ZEND_GET_MODULE(iTextPHP)
#endif

PHP_FUNCTION(iTextPHP_hello_world)
{
    RETURN_STRING("Hello World from iTextPHP blah", 1);
}

/**
Note:  adapted from Classpath's implementation of Java's floatToIntBits
**/
PHP_FUNCTION(iTextPHP_floatToIntBits)
{
    double value;
    int e,f;
    union
    {
        int i;
        float f;
    } u;
    int argCount = ZEND_NUM_ARGS();
    if (argCount != 1)
    {
        WRONG_PARAM_COUNT;
    }

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC,
                          "d", &value) == FAILURE)
    {
        RETURN_STRING("NAN", 1);
    }

    u.f = value;

    e = u.i & 0x7f800000;
    f = u.i & 0x007fffff;
    if (e == 0x7f800000 && f != 0)
    {
        u.i = 0x7fc00000;

    }

    RETURN_LONG(u.i);
}

void itextphpByteDestructionHandler(zend_rsrc_list_entry *rsrc TSRMLS_DC)
{
    //to do: handle destruction
}

void itextphpanotherByteDestructionHandler(zend_rsrc_list_entry *rsrc TSRMLS_DC)
{
    //to do: handle destruction
}

void itextphpStringDestructionHandler(zend_rsrc_list_entry *rsrc TSRMLS_DC)
{
    //to do: handle destruction
}

void itextphpCharDestructionHandler(zend_rsrc_list_entry *rsrc TSRMLS_DC)
{
    //to do: handle destruction
}



PHP_MINIT_FUNCTION(iTextPHP)
{
    le_itextphpByteResource = zend_register_list_destructors_ex(itextphpByteDestructionHandler, NULL, le_itextphpByteResourceName, module_number);
    le_itextphpanotherByteResource = zend_register_list_destructors_ex(itextphpanotherByteDestructionHandler, NULL, le_itextphpanotherByteResourceName, module_number);
    le_itextphpStringResource = zend_register_list_destructors_ex(itextphpStringDestructionHandler, NULL, le_itextphpStringResourceName, module_number);
    le_itextphpCharResource = zend_register_list_destructors_ex(itextphpCharDestructionHandler, NULL, le_itextphpCharResourceName, module_number);


}





