#include "cp437conversion.h"
#include "iTextPHPString.h"


/* {{{ proto resource itextphp_cp437_initialize(resource characterArray)
   convert a initialize the class */
PHP_FUNCTION(itextphp_cp437_initialize)
{

    /* the original itext java blurb */
    /**
    for (int k = 0; k < table.length; ++k)
        c2b.put(table[k], k + 128);
    **/
    int k = 0;
    zval * anArray;


    switch(ZEND_NUM_ARGS())
    {

        case 1:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET, 1,"a", &anArray)==FAILURE)
            {
                RETURN_FALSE;
	    }
            else
	    {
                for (k = 0; k < wcslen(cp437table); ++k)
                {

                    zval *dta = NULL;
                    MAKE_STD_ZVAL(dta);
                    ZVAL_LONG(dta, (k + 128));
                    zend_hash_index_update(Z_ARRVAL_P(anArray), cp437table[k],(void *)&dta, sizeof(zval *), NULL);
                }
            }
        }
    }
}


/* {{{ proto resource itextphp_cp437_chartobyte(resource characterArray, string encoding, array c2b)
   convert a string to a byte array */
PHP_FUNCTION(itextphp_cp437_chartobyte)
{

    /* the original itext java blurb */
    /*char cc[] = text.toCharArray();
            byte b[] = new byte[cc.length];
            int ptr = 0;
            int len = cc.length;
            for (int k = 0; k < len; ++k) {
                char c = cc[k];
                if (c < ' ')
                    continue;
                if (c < 128)
                    b[ptr++] = (byte)c;
                else {
                    byte v = (byte)c2b.get(c);
                    if (v != 0)
                        b[ptr++] = v;
                }
            }
            if (ptr == len)
                return b;
            byte b2[] = new byte[ptr];
            System.arraycopy(b, 0, b2, 0, ptr);
            return b2;
    */

    zval * characterArray = NULL, * c2b = NULL;
    char * encoding = NULL;
    byte * aByte = NULL;
    unsigned char * theByte = NULL;
    int k = 0, length = 0;
    itextstring *theString = NULL;
    switch(ZEND_NUM_ARGS())
    {
        case 3:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,3,"rsa", &characterArray, &encoding, &length, &c2b)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                //get the string resource
                ZEND_FETCH_RESOURCE(theString, itextstring *, &characterArray, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                aByte = (byte*) emalloc(sizeof(byte));
                aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*theString->length);
                aByte->size = theString->length;
                theByte = aByte->theByte;
                /*theByte = (unsigned char *)emalloc(sizeof(byte)*length);*/
                int ptr = 0;
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
                    if (c == ' ')
                    {
                        aByte->theByte[ptr++] = (unsigned char)wctob(c);
                    }
                    if (c < 128)
                    {
                        unsigned char v = wctob(c);
                        aByte->theByte[ptr++] = v;
                    }
                    else
                    {
                        zval * arrayval;
                        if (zend_hash_index_find(Z_ARRVAL_P(c2b),wctob(c) , (void**)&arrayval) != SUCCESS)
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
        break;
        }
        default:
        {
            RETURN_FALSE;
            break;
        }
    }

}


/* {{{ proto resource itextphp_cp437_chartobyte(resource characterArray, string encoding, array c2b)
   convert a string to a byte array */
PHP_FUNCTION(itextphp_cp437_bytetochar)
{

    /* the original itext java bits */
    /*int len = b.length;
    char cc[] = new char[len];
    int ptr = 0;
    for (int k = 0; k < len; ++k) {
        int c = b[k] & 0xff;
        if (c < ' ')
            continue;
        if (c < 128)
            cc[ptr++] = (char)c;
        else {
            char v = table[c - 128];
            cc[ptr++] = v;
        }
    }
    return new String(cc, 0, ptr);*/
    zval * byteArray, * len;
    itextstring *theString;
    int length = 0;
    byte * theByte;
    int k = 0;
    int ptr = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rl", &byteArray, &len)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                convert_to_long(len);
                length = Z_LVAL_P(len);
                ZEND_FETCH_RESOURCE(theByte, byte *, &byteArray, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
                theString = emalloc(sizeof(itextstring));
                theString->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*length+1);
                for (k = 0; k < theByte->size; k ++)
                {
                    int c =  (int)(theByte->theByte[k] & 0xff); 	
                    //theString->dataChar[k] = (char)(theByte->theByte[k] & 0xff); 
                    if (c < ' ')
                    {
                        continue;
                    }
                    if (c < 128)
                    {
                        theString->dataWide[ptr++] = cp437table[k-128];  
                    }
                }
                //now return the resource the user!
                theString->length = wcslen(theString->dataWide); 
                theString->isWide = STRINGWIDE;
                ZEND_REGISTER_RESOURCE(return_value, theString ,le_itextphpStringResource);
            }
	}
    }
}
