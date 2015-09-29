#include "symbolconversion.h"
#include "iTextPHPString.h"

/* {{{ proto resource itextphp_symbolconversion_initialize(resource Array1, resource Array2)
   initialize the class */
PHP_FUNCTION(itextphp_symbolconversion_initialize)
{

     /* the original itext java blurb */
    /**
    for (int k = 0; k < table1.length; ++k) {
        int v = (int)table1[k];
        if (v != 0)
            t1.put(v, k + 32);
    }
    for (int k = 0; k < table2.length; ++k) {
        int v = (int)table2[k];
        if (v != 0)
            t2.put(v, k + 32);
    }

    **/

    zval * array1 = NULL;
    zval * array2 = NULL;
    int k = 0;

    switch(ZEND_NUM_ARGS())
    {

        case 2:
        {
            if(zend_parse_parameters(2,"aa", &array1, &array2)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
	    {
                for (k = 0; k < wcslen(symboltable1); ++k)
                {
                    int v = (int)symboltable1[k];
                    if (v != 0)
                    {
                        zval *new_value = NULL;
                        MAKE_STD_ZVAL(new_value); 
                        ZVAL_LONG(new_value, (k + 32));
                        zend_hash_index_update(Z_ARRVAL_P(array1), v, (void *)&new_value, sizeof(zval *), NULL);	
                    }
                }
                for (k = 0; k < wcslen(symboltable2); ++k)
                {
                    int v = (int)symboltable2[k];
                    if (v != 0)
                    {
                        zval *new_value = NULL;
                        MAKE_STD_ZVAL(new_value);
                        ZVAL_LONG(new_value, (k + 32));
                        zend_hash_index_update(Z_ARRVAL_P(array2), v, (void *)&new_value, sizeof(zval *), NULL);	
                    }
                }
            }
        }
    }
}

/* {{{ proto resource itextphp_cp437_chartobyte(resource characterArray, string encoding, array translation)
   convert a string to a byte array */
PHP_FUNCTION(itextphp_symbolconversion_chartobyte)
{
     /* the original itext java blurb */
    /*char cc[] = text.toCharArray();
    byte b[] = new byte[cc.length];
    int ptr = 0;
    int len = cc.length;
    for (int k = 0; k < len; ++k) {
        char c = cc[k];
        byte v = (byte)translation.get((int)c);
        if (v != 0)
            b[ptr++] = v;
    }
    if (ptr == len)
        return b;
    byte b2[] = new byte[ptr];
    System.arraycopy(b, 0, b2, 0, ptr);
    return b2;
    */
    zval * text = NULL, * encoding = NULL, * translation = NULL;
    int ptr = 0;
    int len = 0;
    byte * aByte = NULL;
    unsigned char * theByte = NULL;
    itextstring *theString = NULL;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 3:
        {
            if(zend_parse_parameters(3,"rsa", &text, &encoding, &translation)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                //get the string resource
                ZEND_FETCH_RESOURCE(theString, itextstring *, &text, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                aByte = (byte*) emalloc(sizeof(byte));
                aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*theString->length);
                aByte->size = theString->length;
                theByte = aByte->theByte;
                for (k = 0; k < theString->length;k++)
                {
                    if (theString == NULL)
                    {
                        return;
                    }

                        wchar_t c;
                        if (theString->isWide == NOTTA)
                        {
                            //theByte[k] = wctob(btowc(theString->dataChar[k]));
                            // char c = cc[k];
                            c = btowc(theString->dataChar[k]); 
                        }
                        else
                        {
                            //theByte[k] = wctob(theString->dataWide[k]);
                            // char c = cc[k];
                            c = theString->dataWide[k];
                        }
                        zval * arrayval = NULL;
                        if (zend_hash_index_find(Z_ARRVAL_P(translation),wctob(c) , (void**)&arrayval) != SUCCESS)
                        {
                            continue;
                        }
                        else
                        {
                            convert_to_string(arrayval);
                            unsigned char v = Z_STRVAL_P(arrayval)[0];
                            if (v != 0)
                            {
                                    aByte->theByte[ptr++] = v;
                            }
                        }
                }
                if (ptr == theString->length)
                {
                    ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpByteResource);
                }
                byte* b2 = (byte*) emalloc(sizeof(byte));
                b2->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*ptr);
                b2->size = ptr;
                for (k=0; k < aByte->size; k++)
                {
                    b2->theByte[k] = aByte->theByte[k];
                }
                ZEND_REGISTER_RESOURCE(return_value, b2 ,le_itextphpByteResource);
            }
         }
     }
}



