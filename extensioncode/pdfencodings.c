#include "pdfencodings.h"
#include "iTextPHPString.h"
#include "ext/iconv/php_iconv.h"

#ifdef HAVE_ICONV

#ifdef PHP_ICONV_H_PATH
#include PHP_ICONV_H_PATH
#else
#include <iconv.h>
#endif

#ifdef HAVE_GLIBC_ICONV
#include <gnu/libc-version.h>
#endif

#ifdef HAVE_LIBICONV
#undef iconv
#endif

#endif

#define ENCODESEQUENCE_EXCEPTIONTEXT_1 "Inconsistent mapping."


/* {{{ proto resource itextphp_charToByte(string characterArray, int len)
   convert a string to a byte array */
PHP_FUNCTION(itextphp_charToByte)
{
    zval * characterArray = NULL;
    int length = 0;
    char * CharacterString = NULL;
    byte * aByte = NULL;
    unsigned char * theByte = NULL;
    int k = 0;
    itextstring *theString = NULL;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rl", &characterArray, &length)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {

                //get the string resource
                ZEND_FETCH_RESOURCE(theString, itextstring *, &characterArray, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                aByte = (byte*) emalloc(sizeof(byte));
                aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*length);
                aByte->size = length;
                theByte = aByte->theByte;
                /*theByte = (unsigned char *)emalloc(sizeof(byte)*length);*/
                for (k = 0; k < length;k++)
                {
                    if (theString == NULL)
                    {
                        RETURN_FALSE;
                    }
                    else
                    {
                        if (theString->isWide == NOTTA)
                        {
                            theByte[k] = wctob(btowc(theString->dataChar[k]));
                        }
                        else
                        {
                            theByte[k] = wctob(theString->dataWide[k]);
                        }
                    }
                }

                ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpByteResource);
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

/* {{{ proto resource itextphp_charToBytePDFDocEncoding(string characterArray, int len)
   convert a string to a byte array */
PHP_FUNCTION(itextphp_charToBytePDFDocEncoding)
{
    zval * characterArray = NULL, * hash = NULL, * hashval = NULL;
    int length = 0;
    byte * aByte = NULL;
    unsigned char * theByte = NULL;
    int k = 0;
    int count = 0;
    int c = 0;	 
    int ptr = 0;
    itextstring *theString = NULL;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET, 3,"rla", &characterArray, &length, &hash)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                ZEND_FETCH_RESOURCE(theString, itextstring *, &characterArray, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                aByte = (byte*) emalloc(sizeof(byte));
                aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*theString->length);
                aByte->size = theString->length;
                theByte = aByte->theByte;
                for (k = 0; k < theString->length; ++k)
                {
                    char char1 = theString->dataChar[k];
                    if ((int)char1 < 128 || ((int)char1 >= 160 && (int)char1 <= 255))
                    {
                        c = char1;
                    }
                    else
                    {
                        zval **tmp;
                        if (zend_hash_index_find(Z_ARRVAL_P(hash), char1, (void**)&tmp) != SUCCESS)
                        {
                            RETURN_NULL()
                        }
                        //now get the actual value
                        wchar_t * theValue;
                        ZEND_FETCH_RESOURCE(theValue, wchar_t *, tmp, -1, le_itextphpByteResourceName, le_itextphpByteResource);
                        c = wctob(theValue[0]);
                        /*c = hash.get(char1);*/
                    }
                    if (c != 0)
                    {
                        theByte[ptr++] = (unsigned char)c;
                    }
                }
                array_init(return_value);
                if (ptr == theString->length)
	        {
                    zval *new_bool = NULL, *theNull = NULL, *new_resource = NULL;
                    MAKE_STD_ZVAL(new_bool);
                    MAKE_STD_ZVAL(theNull);
                    ZVAL_NULL(theNull);
                    new_bool->type = IS_BOOL;
                    new_bool->value.lval = 1;
                    zend_hash_next_index_insert(Z_ARRVAL_P(return_value), &new_bool,sizeof(zval *), NULL);
                    //return b;
                    MAKE_STD_ZVAL(new_resource); 
                    ZEND_REGISTER_RESOURCE(new_resource, aByte ,le_itextphpanotherByteResource);
                    zend_hash_next_index_insert(Z_ARRVAL_P(return_value), &new_resource, sizeof(zval *), NULL);
                    zend_hash_next_index_insert(Z_ARRVAL_P(return_value),&theNull, sizeof(zval *), NULL);
                }
                else
                {
                    zval *new_bool = NULL, *theNull = NULL, *new_resource = NULL;
                    MAKE_STD_ZVAL(new_bool);
                    MAKE_STD_ZVAL(theNull);
                    ZVAL_NULL(theNull);
                    new_bool->type = IS_BOOL;
                    new_bool->value.lval = 0;
                    zend_hash_next_index_insert(Z_ARRVAL_P(return_value), &new_bool,sizeof(zval *), NULL);
                    zend_hash_next_index_insert(Z_ARRVAL_P(return_value),&theNull, sizeof(zval *), NULL);
                    //copy the array to index 3
                    byte * anotherByte = (byte *)emalloc(sizeof(byte));
                    anotherByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*theString->length);
                    anotherByte->size = theString->length;
                    unsigned char * theByte2 = anotherByte->theByte;
                    for (k=0; k < ptr;k++)
                    {
                        theByte2[k] = theByte[k];
                    }
                    MAKE_STD_ZVAL(new_resource); 
                    ZEND_REGISTER_RESOURCE(new_resource, anotherByte ,le_itextphpanotherByteResource);
                    zend_hash_next_index_insert(Z_ARRVAL_P(return_value), &new_resource, sizeof(zval *), NULL);
                }
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



/* {{{ proto resource itextphp_byteToStringPDFDocEncoding(resource bytearray, int len, array thearray)
   convert a byte to a string for PDFDoc or Winansi */
PHP_FUNCTION(itextphp_byteToStringPDFDocEncoding)
{
     /* the original itext java blurb */
     /* char ch[] = null;
        if (encoding.equals(BaseFont.WINANSI))
            ch = winansiByteToChar;
        else if (encoding.equals(PdfObject.TEXT_PDFDOCENCODING))
            ch = pdfEncodingByteToChar;
        if (ch != null) {
            int len = bytes.length;
            char c[] = new char[len];
            for (int k = 0; k < len; ++k) {
                c[k] = ch[bytes[k] & 0xff];
            }
            return new String(c);
        } */
    zval * byteArray = NULL, * hash = NULL, * hashval = NULL;
    int length = 0;
    byte * aByte;
    unsigned char * theByte;
    int k = 0;
    int len = 0;
    itextstring *theString;
    switch(ZEND_NUM_ARGS())
    {
        case 3:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,3,"rla", &byteArray, &length, &hash)==FAILURE)
            {
	        RETURN_FALSE;
            }
            else
            {

                ZEND_FETCH_RESOURCE(aByte, byte *, &byteArray, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
                len = aByte->size;
                theString = emalloc(sizeof(itextstring));
                theString->dataChar = (char *)emalloc(sizeof(char)*len+1);
                theString->length = len;
                for (k = 0; k < len; ++k)
                {
                    zval **tmp;
                    if (zend_hash_index_find(Z_ARRVAL_P(hash), (aByte->theByte[k] & 0xff), (void**)&tmp) != SUCCESS)
                    {
                        RETURN_NULL()
                    }
                    theString->dataChar[k] = ((char *)Z_STRVAL_PP(tmp))[0];
                }
                //now return the resource the user!
                ZEND_REGISTER_RESOURCE(return_value, theString ,le_itextphpStringResource);
            }
        }
    }

}



/* {{{ proto bool itextphp_getwinansi()
   returnthe winansi resource */
PHP_FUNCTION(itextphp_getwinansi)
{
    
    
 
    
    
    
}

/* {{{ proto resource itextphp_byteToString(resource bytearray, int len)
   convert a byte to a string */
PHP_FUNCTION(itextphp_byteToString)
{
    /* the original itext java blurb */
     /*   char c[] = new char[bytes.length];
            for (int k = 0; k < bytes.length; ++k)
                c[k] = (char)(bytes[k] & 0xff);
            return new String(c);*/
    zval * byteArray = NULL;
    itextstring *theString = NULL;
    int length = 0;
    byte * theByte = NULL;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rl", &byteArray, &length)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                ZEND_FETCH_RESOURCE(theByte, byte *, &byteArray, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
                theString = emalloc(sizeof(itextstring));
                theString->dataChar = (char *)emalloc(sizeof(char)*length+1);
                for (k = 0; k < theByte->size; ++k)
                {
                    theString->dataChar[k] = (char)(theByte->theByte[k] & 0xff);
                }
                //now return the resource the user!
                ZEND_REGISTER_RESOURCE(return_value, theString ,le_itextphpStringResource);
            }
        }
    }
}

/* {{{ proto resource itextphp_byteToString(resource bytearray, string encoding)
   convert a byte to a string in unicode (string returned is a resource)*/
PHP_FUNCTION(itextphp_byteToStringUnicodeEncoding)
{
    zval * byteArray;
    iconv_t cd;
    size_t result, out_left;
    unsigned char *ndl_buf;
    char *theEncoding;
    byte * theByte;
    int k = 0;
    int length = 0, encodingLength = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rs", &byteArray, &theEncoding, &encodingLength)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                cd = iconv_open(theEncoding, "ISO-8859-1");
                if (cd == (iconv_t)(-1))
                {
                    RETURN_NULL()
                }
                else
                {
                    //assume the bytes are of type wchar_t...
                    ZEND_FETCH_RESOURCE(theByte, byte *, &byteArray, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
                    unsigned char * newStringArray = emalloc(sizeof(char)*theByte->size);
                    length = theByte->size*sizeof(int) + 15;
                    result = iconv(cd, (char **) &theByte->theByte, &length, (char **)&ndl_buf, &out_left);
                    itextstring * theStringResource = (itextstring *)emalloc(sizeof(itextstring));
                    theStringResource->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*length+1);			
                    mbsrtowcs(theStringResource->dataWide, (const char **)&ndl_buf, length, 0);
                    //now return the resource the user!
                    ZEND_REGISTER_RESOURCE(return_value, theStringResource ,le_itextphpStringResource);
                }

            }
        }
    }
}




/* {{{ proto resource itextphp_isPdfDocEncodings(resource stringval, array pdfencoding)
   returns TRUE if the text is pdf doc encoded*/
PHP_FUNCTION(itextphp_isPdfDocEncodings)
{
    /** original itext java blurb */
    /*
     int len = text.length();
        for (int k = 0; k < len; ++k) {
            char char1 = text.charAt(k);
            if (char1 < 128 || (char1 >= 160 && char1 <= 255))
                continue;
            if (!pdfEncoding.containsKey(char1))
                return false;
        }
        return true;
        */
    zval * string, * anarray;
    itextstring *theString;
    int length = 0;
    int k = 0;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"ra", &string, &anarray)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                ZEND_FETCH_RESOURCE(theString, itextstring *, &string, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                if (theString != NULL)
                {
                    length = theString->length;
                    for (k = 0; k < length; k ++)
                    {
                        char char1;
                        if (theString->dataChar != NULL)
                        {
                            char1 = theString->dataChar[k];
                            if (char1 < 128 || (char1 >= 160 && char1 <= 255))
                            {
                                continue;
                            }
                        }
                        else if (theString->dataWide != NULL)
                        {
                             char1 = wctob(theString->dataWide[k]);
                             if (char1 < 128 || (char1 >= 160 && char1 <= 255))
                             {
                                 continue;
                             }
                        }
                        else
                        {
                            RETURN_FALSE;
                        }
                        if (zend_hash_index_exists(Z_ARRVAL_P(anarray),char1) != FAILURE)
                        {
                            RETURN_TRUE;
                        }
                        else
                        {
                            RETURN_FALSE;
                        }
                    }
                }
            }
            break;
        }
        default:
            RETURN_FALSE;
     }
}

/* {{{ proto resource itextphp_charToByteUnicodeEncoding(resource string, int len)
   convert a string to a byte for Unicode Encoding*/
PHP_FUNCTION(itextphp_charToByteUnicodeEncoding)
{
    /* the original itext java blurb */
   /*  char cc[] = text.toCharArray();
            int len = cc.length;
            byte b[] = new byte[cc.length * 2 + 2];
            b[0] = -2;
            b[1] = -1;
            int bptr = 2;
            for (int k = 0; k < len; ++k) {
                char c = cc[k];
                b[bptr++] = (byte)(c >> 8);
                b[bptr++] = (byte)(c & 0xff);
            }
            return b;*/
    zval * characterArray = NULL;
    int length = 0;
    byte * aByte;
    unsigned char * theByte;
    int k = 0;
    int count = 0;
    int c = 0;	 
    int ptr = 0;
    itextstring *theString;
    switch(ZEND_NUM_ARGS())
    {
        case 2:
	{
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rl", &characterArray, &length)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                ZEND_FETCH_RESOURCE(theString, itextstring *, &characterArray, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                aByte = (byte *)emalloc(sizeof(byte));
                aByte->theByte = (unsigned char *)emalloc(sizeof(unsigned char)*theString->length*2+2);
                aByte->size = theString->length*2+2;
                theByte = aByte->theByte;
                theByte[0] = -2;
                theByte[1] = -1;
                int bptr = 2;
                for (k = 0; k < theString->length; ++k) 
                {
                   wchar_t c = theString->dataWide[k];
                   theByte[bptr++] = (unsigned char)(wctob(c) >> 8);
                   theByte[bptr++] = (unsigned char)(wctob(c) & 0xff);
                }
                ZEND_REGISTER_RESOURCE(return_value, aByte ,le_itextphpanotherByteResource);
            }
        }
    }
}


/* {{{ proto resource itextphp_decodesequence(resource bytearray, int start, int length, byte array planes)
   return a string representation of the decoded sequence*/
PHP_FUNCTION(itextphp_decodesequence)
{
    /* the original itext java blurb */
    
    /*
    StringBuffer buf = new StringBuffer();
        int end = start + length;
        int currentPlane = 0;
        for (int k = start; k < end; ++k) {
            int one = (int)seq[k] & 0xff;
            char plane[] = planes[currentPlane];
            int cid = plane[one];
            if ((cid & 0x8000) == 0) {
                buf.append((char)cid);
                currentPlane = 0;
            }
            else
                currentPlane = cid & 0x7fff;
        }
        return buf.toString();*/
    
    byte * theByte = NULL;
    int actstart = 0, actlength = 0;
    int k = 0, end = 0, currentPlane = 0;;
    zval * seq, * start, * length, * planes;
    switch(ZEND_NUM_ARGS())
    {
	
        case 2:
	{
	    if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"rlla", &seq, &start, &length, &planes)==FAILURE){
	        RETURN_NULL();
	    }
	    else
	    {
		ZEND_FETCH_RESOURCE(theByte, byte *, &seq, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		convert_to_long(start);
		actstart = Z_LVAL_P(start);
		convert_to_long(length);
		actlength = Z_LVAL_P(length);
		
		end = actstart + actlength;
		
		itextstring * theStringResource = (itextstring *)emalloc(sizeof(itextstring));
		     
		theStringResource->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*end+1);	
		wchar_t *buf = theStringResource->dataWide;
		int place = 0;
	        for (k = actstart; k < end; ++k) 
		{
		     int one = ((int)theByte->theByte[k]) & 0xff;
		     zval * plane;
		     
		     if (zend_hash_index_find(Z_ARRVAL_P(planes), currentPlane, (void**)&plane) != SUCCESS)
		     {
			RETURN_NULL()
		     }
		     zval *cidZval;
		     if (zend_hash_index_find(Z_ARRVAL_P(plane), one, (void**)&cidZval) != SUCCESS)
		     {
			RETURN_NULL()
		     }
                     //convert cidZval to int
		     convert_to_long(cidZval);
		     int cid = Z_LVAL_P(cidZval);
		     if ((cid & 0x8000) == 0) 
		     {
			 
			 buf[place] = (wchar_t)cid;
			 currentPlane = 0;
			 
		     }
		     else
		     {
			 currentPlane = cid & 0x7fff;
		     }
		     place++;
		}
		buf[place]='\0';/** added the null terminator **/
		
		
		    
		    //now return the resource the user!
		    ZEND_REGISTER_RESOURCE(return_value, theStringResource ,le_itextphpStringResource);
		
	    }
	    break;    
	}
        default:
            RETURN_NULL();
    }
    
    
}


/* {{{ proto resource itextphp_breaklong(int n, int size, byte seqs)
   return void*/
PHP_FUNCTION(itextphp_breaklong)
{
    
    /* the original itext java blurb */
    
    /*
    for (int k = 0; k < size; ++k) {
        seqs[k] = (byte)(n >> ((size - 1 - k) * 8));
    }
    */
    
    zval * n, * size, * seqs;
    int actSize = 0, actN = 0, k =0;
    byte * theByte = NULL;
    switch(ZEND_NUM_ARGS())
    {
	
        case 3:
	{
	    if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,3,"llr", &n, &size, &seqs)!=FAILURE)
	    {
		convert_to_long(n);
		actN = Z_LVAL_P(n);
		
		convert_to_long(size);
		actSize = Z_LVAL_P(size);
		
		ZEND_FETCH_RESOURCE(theByte, byte *, &seqs, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
		
		for (k = 0; k < actSize; ++k)
		{
		    
		    
		    if (theByte != NULL)
		    {
			theByte->theByte[k] = (unsigned char) (actN >>((actSize - 1 - k) * 8));
		    }
		}
		
	    }
	    break;
	}
	
    }
     
    
}

/* {{{ proto resource itextphp_encodesequence(int size, resource(byte) seqs, string cid, array planes)
   return void*/
PHP_FUNCTION(itextphp_encodesequence)
{
    /* the original itext java blurb */
    /* NOTE: Because this function is quite large, I have interspersed the contents below
       With the equivalent C php extension code for help during debugging and code 
       maintenance */
    /*
    --size;
    int nextPlane = 0;
    for (int idx = 0; idx < size; ++idx) {
        char plane[] = (char[])planes.get(nextPlane);
        int one = (int)seqs[idx] & 0xff;
        char c = plane[one];
        if (c != 0 && (c & 0x8000) == 0)
            throw new RuntimeException("Inconsistent mapping.");
        if (c == 0) {
            planes.add(new char[256]);
            c = (char)((planes.size() - 1) | 0x8000);
            plane[one] = c;
        }
        nextPlane = c & 0x7fff;
    }
    char plane[] = (char[])planes.get(nextPlane);
    int one = (int)seqs[size] & 0xff;
    char c = plane[one];
    if ((c & 0x8000) != 0)
        throw new RuntimeException("Inconsistent mapping.");
    plane[one] = cid;
    */
    zval * size, * seqs, * planes, * plane, * theExceptionText, * theResourceZval, * theResourceZvalcvar;
    char * cid = NULL;
    const char *cidConst = NULL;
    int actSize = 0, actCid = 0, nextPlane = 0, idx = 0, one = 0;
    byte * theByte = NULL;
    itextstring * theString = NULL, * theStringResource= NULL, * theStringResourcecvar = NULL;
    wchar_t c = 0;
    switch(ZEND_NUM_ARGS())
    {

        case 4:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,4,"lrsa", &actSize, &seqs, &cid, &planes)!=FAILURE)
            {

                --actSize;
                ZEND_FETCH_RESOURCE(theByte, byte *, &seqs, -1, le_itextphpanotherByteResourceName, le_itextphpanotherByteResource);
                for (idx = 0; idx<actSize;idx++)
                {
                    if (zend_hash_index_find(Z_ARRVAL_P(planes), nextPlane, (void**)&plane) == SUCCESS)
                    {
                        return;
                    }
                    one = (int)theByte->theByte[idx] & 0xff;
                    ZEND_FETCH_RESOURCE(theString, itextstring *, &plane, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                    c = theString->dataWide[one];
                    if (c != 0 && (c & 0x8000) == 0)
                    {
                        array_init(return_value);
                        MAKE_STD_ZVAL(theExceptionText);
                        ZVAL_STRING(theExceptionText, ENCODESEQUENCE_EXCEPTIONTEXT_1, 1);
                        zend_hash_next_index_insert(Z_ARRVAL_P(return_value), &theExceptionText, sizeof(zval *), NULL);
                        return;
                    }
                    if (c == 0)
                    {
                        theStringResource = (itextstring *)emalloc(sizeof(itextstring));
                        theStringResource->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*256);
                        MAKE_STD_ZVAL(theResourceZval);
                        ZEND_REGISTER_RESOURCE(theResourceZval, theStringResource ,le_itextphpStringResource);
                        zend_hash_next_index_insert(Z_ARRVAL_P(plane), &theResourceZval, sizeof(zval *), NULL);
                        c = (wchar_t)((zend_hash_num_elements(Z_ARRVAL_P(plane)) - 1) | 0x8000);
                        theStringResourcecvar = (itextstring *)emalloc(sizeof(itextstring));
                        theStringResourcecvar->dataWide = (wchar_t *)emalloc(sizeof(wchar_t));
                        MAKE_STD_ZVAL(theResourceZvalcvar);
                        theStringResourcecvar->dataWide[0] = c;
                        ZEND_REGISTER_RESOURCE(theResourceZvalcvar, theStringResourcecvar ,le_itextphpStringResource);
                        zend_hash_index_update(Z_ARRVAL_P(plane), one, theResourceZvalcvar, sizeof(zval *), NULL);
                     }
                     nextPlane = c & 0x7fff;
                 }
                 if (zend_hash_index_find(Z_ARRVAL_P(planes), nextPlane, (void**)&plane) == SUCCESS)
                 {
                     return;
                 }
                 one = (int)theByte->theByte[actSize] & 0xff;
                 ZEND_FETCH_RESOURCE(theString, itextstring *, &plane, -1, le_itextphpStringResourceName, le_itextphpStringResource);
                 c = theString->dataWide[one];
                 if ((c & 0x8000) != 0)
                 {
                     array_init(return_value);
                     MAKE_STD_ZVAL(theExceptionText);
                     ZVAL_STRING(theExceptionText, ENCODESEQUENCE_EXCEPTIONTEXT_1, 1);
                     zend_hash_next_index_insert(Z_ARRVAL_P(return_value), &theExceptionText, sizeof(zval *), NULL);
                     return;
                }
                theStringResourcecvar = (itextstring *)emalloc(sizeof(itextstring));
                actCid = strlen(cid);
                theStringResourcecvar->dataWide = (wchar_t *)emalloc(sizeof(wchar_t)*actCid+1);
                theStringResourcecvar->length = actCid+1;
                MAKE_STD_ZVAL(theResourceZvalcvar);
                cidConst = (const char *)cid;
                mbsrtowcs(theStringResourcecvar->dataWide, &cidConst, sizeof(wchar_t)*actCid+1,NULL);
                ZEND_REGISTER_RESOURCE(theResourceZvalcvar, theStringResourcecvar ,le_itextphpStringResource);
                zend_hash_index_update(Z_ARRVAL_P(plane), one, theResourceZvalcvar, sizeof(zval *), NULL);
            }

        break;
        }
    }
}


//initialize the constants with wide character values

PHP_FUNCTION(itextphp_initializepdfencodingsConstants)
{
    zval ** winansi, ** pdfEncoding;
    zval *new_resource;
    int i = 0;
    int count = 0;
    wchar_t c = 0;
    wchar_t * cPnt = 0;
    switch(ZEND_NUM_ARGS())
    {

        case 2:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,2,"aa",&winansi, &pdfEncoding)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
	        cPnt = (wchar_t *)emalloc(sizeof(wchar_t *));
                for (i = 128; i < 160; i++)
                {
                    c = winansiByteToChar[i];
                    cPnt[1] = c;
                    if (c != 65533)
                    {
                        MAKE_STD_ZVAL(new_resource); 
                        ZEND_REGISTER_RESOURCE(new_resource, cPnt ,le_itextphpByteResource);
                        convert_to_array_ex(winansi);
                        if(zend_hash_index_update(Z_ARRVAL_PP(winansi), count, (void *)&new_resource, sizeof(zval *), NULL) == FAILURE)
                        {
                             RETURN_FALSE;
                        }
                        count++;
                    }
                }
                count = 0;
                for (i = 128; i < 160; i++)
                {
                    c = pdfEncodingByteToChar[i];
                    cPnt[1] = c;
                    if (c != 65533)
                    {
                        MAKE_STD_ZVAL(new_resource); 
                        ZEND_REGISTER_RESOURCE(new_resource, cPnt ,le_itextphpByteResource);
                        convert_to_array_ex(pdfEncoding);
                        if(zend_hash_index_update(Z_ARRVAL_PP(pdfEncoding), count, (void *)&new_resource, sizeof(zval *), NULL) == FAILURE)
                        {
                             RETURN_FALSE;
                        }
                        count++;
                    }
                }
                CRLF_CID_NEWLINE[0][0] = '\n';
                CRLF_CID_NEWLINE[1][0] = '\r';
                CRLF_CID_NEWLINE[1][1] = '\n';
            }
            break;
        }
        default:
        {
            WRONG_PARAM_COUNT;
            break;
        }
    }
}


/* {{{ proto resource itextphp_getWinAnsiValue(resource theChar)
   return int*/
PHP_FUNCTION(itextphp_getWinAnsiValue)
{
    zval * theChar;
    char * thechar;
    switch(ZEND_NUM_ARGS())
    {

        case 1:
        {
            if(zend_parse_parameters_ex(ZEND_PARSE_PARAMS_QUIET,1, "r", &theChar)==FAILURE)
            {
                RETURN_FALSE;
            }
            else
            {
                /**fetch the char resource**/
                ZEND_FETCH_RESOURCE(thechar, char *, &theChar, -1, le_itextphpCharResourceName, le_itextphpCharResource);
                if (thechar != NULL)
                {
                    RETURN_LONG( wctob(winansiByteToChar[btowc(thechar[0])]));
                }
                else
                {
                    RETURN_LONG(-1);
                }
            }
            break;
        }
        default:
            RETURN_LONG(-1);
    }

}








