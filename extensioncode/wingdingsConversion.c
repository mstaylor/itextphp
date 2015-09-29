#include "wingdingsConversion.h"
#include "iTextPHPString.h"




/* {{{ proto resource itextphp_wingdings_chartobyte(resource characterArray, string encoding)
   convert a string to a byte array */
PHP_FUNCTION(itextphp_wingdings_chartobyte)
{
    
     /* the original itext java blurb */
     /**
     char cc[] = text.toCharArray();
     byte b[] = new byte[cc.length];
     int ptr = 0;
     int len = cc.length;
     for (int k = 0; k < len; ++k) {
         char c = cc[k];
         if (c == ' ')
             b[ptr++] = (byte)c;
         else if (c >= '\u2701' && c <= '\u27BE') {
             byte v = table[c - 0x2700];
             if (v != 0)
                 b[ptr++] = v;
	 }
     }
     if (ptr == len)
         return b;
     byte b2[] = new byte[ptr];
     System.arraycopy(b, 0, b2, 0, ptr);
     return b2;
     **/

    zval * characterArray = NULL;
    char * encoding = NULL;
    byte * aByte;
    unsigned char * theByte = NULL;
    int k = 0;
    itextstring *theString = NULL;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters(2,"rs", &characterArray, &encoding)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                //get the string resource
                ZEND_FETCH_RESOURCE(theString, itextstring *, &characterArray, -1, le_itextphpStringResourceName, le_itextphpStringResource);

                if (theString == NULL)
                {
                    RETURN_FALSE;
                }

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
                        RETURN_FALSE;
                    }
                    else
                    {
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
                        else if (c >= u2701 && c <= u27BE) 
                        {
                            unsigned char v = wctob(wingdingstable[c - 0x2700]);
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


